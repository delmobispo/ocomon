<?php session_start();
/*      Copyright 2020 Flávio Ribeiro

        This file is part of OCOMON.

        OCOMON is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation; either version 3 of the License, or
        (at your option) any later version.
        OCOMON is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with Foobar; if not, write to the Free Software
        Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

$post = $_POST;


$config = getConfig($conn);
$rowLogado = getUserInfo($conn, $_SESSION['s_uid']);

$recordFile = false;
$erro = false;
$exception = "";
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['numero'] = (isset($post['numero']) ? intval($post['numero']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";


$data['model_name'] = (isset($post['model_name']) ? noHtml($post['model_name']) : "");
$data['type'] = (isset($post['type']) ? noHtml($post['type']) : "");
$data['total_files_to_deal'] = (isset($post['cont']) ? noHtml($post['cont']) : 0);



/* Checagem de preenchimento dos campos obrigatórios*/
if ($data['action'] == "new" || $data['action'] == "edit") {

    if ($data['model_name'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "model_name";
    } elseif ($data['type'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "type";
    } 

    if ($data['success'] == false) {
        $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
        echo json_encode($data);
        return false;
    }
}


/* Checagens para upload de arquivos - vale para todos os actions */
$totalFiles = ($_FILES && !empty($_FILES['anexo']['name'][0]) ? count($_FILES['anexo']['name']) : 0);
if ($totalFiles > $config['conf_qtd_max_anexos']) {

    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', 'Too many files','');
    echo json_encode($data);
    return false;
}

$uploadMessage = "";
/* Testa os arquivos enviados para montar os índices do recordFile*/
if ($totalFiles) {
    foreach ($_FILES as $anexo) {
        $file = array();
        for ($i = 0; $i < $totalFiles; $i++) {
            /* fazer o que precisar com cada arquivo */
            /* acessa:  $anexo['name'][$i] $anexo['type'][$i] $anexo['tmp_name'][$i] $anexo['size'][$i]*/
            $file['name'] =  $anexo['name'][$i];
            $file['type'] =  $anexo['type'][$i];
            $file['tmp_name'] =  $anexo['tmp_name'][$i];
            $file['error'] =  $anexo['error'][$i];
            $file['size'] =  $anexo['size'][$i];

            $upld = upload('anexo', $config, $config['conf_upld_file_types'], $file);
            if ($upld == "OK") {
                $recordFile[$i] = true;
            } else {
                $recordFile[$i] = false;
                $uploadMessage .= $upld;
            }
        }
    }
    if (strlen($uploadMessage) > 0) {
        $data['success'] = false; 
        $data['field_id'] = "idInputFile";
        $data['message'] = message('warning', 'Ooops!', $uploadMessage, '');
        echo json_encode($data);
        return false;                
    }
}


/* Processamento */
if ($data['action'] == "new") {

    $sql = "SELECT * FROM marcas_comp WHERE marc_nome = '" . $data['model_name'] . "'";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['field_id'] = "model_name";
        $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS'), '');
        echo json_encode($data);
        return false;
    }

    /* Verificação de CSRF */
    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
        echo json_encode($data);
        return false;
    }

	$sql = "INSERT INTO marcas_comp 
        (
            marc_nome, marc_tipo
        )
		VALUES 
        (
            '" . $data['model_name'] . "', '" . $data['type'] . "'
        )";
		
    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT') . $uploadMessage;
        
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD') . "<br/>" . $sql;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'edit') {

    $sql = "SELECT * FROM marcas_comp WHERE marc_nome = '" . $data['model_name'] . "' AND marc_cod <> '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['field_id'] = "model_name";
        $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS'), '');
        echo json_encode($data);
        return false;
    }

    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "UPDATE marcas_comp SET 
    
                marc_nome = '" . $data['model_name'] . "', 
                marc_tipo = '" . $data['type'] . "'
            WHERE 
                marc_cod = '" . $data['cod'] . "'";
            
    try {
        $conn->exec($sql);

        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');

    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE') . "<br />". $sql . "<br />" . $e->getMessage();
        $_SESSION['flash'] = message('danger', 'Ooops!', $data['message'], '');
        echo json_encode($data);
        return false;
    }
} elseif ($data['action'] == 'delete') {


    /* Confere se há impedimentos para excluir o registro */
    $sql = "SELECT * FROM equipamentos WHERE comp_marca = '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

    /* Sem restrições para excluir o registro */
    $sql = "DELETE FROM marcas_comp WHERE marc_cod = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');

        /* Remove também os arquivos relacionados na tabela de imagens */
        $sql = "DELETE FROM imagens WHERE img_model = '" . $data['cod'] . "'";
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }

        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage() . "<hr>";
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_REMOVE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
}



/* Upload de arquivos - Todos os actions */
if ($totalFiles) {
    foreach ($_FILES as $anexo) {
        $file = array();
        for ($i = 0; $i < $totalFiles; $i++) {
            /* fazer o que precisar com cada arquivo */
            /* acessa:  $anexo['name'][$i] $anexo['type'][$i] $anexo['tmp_name'][$i] $anexo['size'][$i]*/

            /* Apenas os arquivos já validados */
            if ($recordFile && $recordFile[$i]) {
                //INSERSAO DO ARQUIVO NO BANCO
                $fileinput = $anexo['tmp_name'][$i];
                // $tamanho = getimagesize($fileinput);
                $tamanho = getimagesize($fileinput);
                $tamanho2 = filesize($fileinput);

                if (!$tamanho) {
                    /* Nâo é imagem */
                    unset ($tamanho);
                    $tamanho = [];
                    $tamanho[0] = "";
                    $tamanho[1] = "";
                }

                if (chop($fileinput) != "") {
                    // $fileinput should point to a temp file on the server
                    // which contains the uploaded file. so we will prepare
                    // the file for upload with addslashes and form an sql
                    // statement to do the load into the database.
                    $file = addslashes(fread(fopen($fileinput, "r"), 1000000));
                    $sqlFile = "INSERT INTO imagens (img_nome, img_model, img_tipo, img_bin, img_largura, img_altura, img_size) values " .
                        "('" . noSpace($anexo['name'][$i]) . "'," . $data['cod'] . ", '" . $anexo['type'][$i] . "', " .
                        "'" . $file . "', " . dbField($tamanho[0]) . ", " . dbField($tamanho[1]) . ", " . dbField($tamanho2) . ")";
                    // now we can delete the temp file
                    unlink($fileinput);
                }
                try {
                    $exec = $conn->exec($sqlFile);
                }
                catch (Exception $e) {
                    $data['message'] = $data['message'] . "<hr>" . TRANS('MSG_ERR_NOT_ATTACH_FILE');
                    $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sqlFile;
                }
            }
        }
    }
}



//Exclui os anexos marcados - Action edit || close
if ( $data['total_files_to_deal'] > 0 ) {
    for ($j = 1; $j <= $data['total_files_to_deal']; $j++) {
        if (isset($post['delImg'][$j])) {
            $qryDel = "DELETE FROM imagens WHERE img_cod = " . $post['delImg'][$j] . "";

            try {
                $conn->exec($qryDel);
            } catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
        }
    }
}




$_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
echo json_encode($data);
return false;