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
$data['model_nanufacturer'] = (isset($post['model_nanufacturer']) ? noHtml($post['model_nanufacturer']) : "");
$data['type'] = (isset($post['type']) ? noHtml($post['type']) : "");

$data['model_capacity'] = (isset($post['model_capacity']) ? noHtml($post['model_capacity']) : "");
$data['model_capacity'] = (!empty($data['model_capacity']) ? str_replace(",", ".", $data['model_capacity']) : "");

$data['capacity_sufix'] = (isset($post['capacity_sufix']) ? noHtml($post['capacity_sufix']) : "");


/* Checagem de preenchimento dos campos obrigatórios*/
if ($data['action'] == "new" || $data['action'] == "edit") {

    if ($data['type'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "type";
    } elseif ($data['model_nanufacturer'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "model_nanufacturer";
    } elseif ($data['model_name'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "model_name";
    } 

    if ($data['success'] == false) {
        $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
        echo json_encode($data);
        return false;
    }

    if ($data['model_capacity'] != "" && !filter_var($data['model_capacity'], FILTER_VALIDATE_FLOAT)) {
        $data['success'] = false; 
        $data['field_id'] = "model_capacity";
        $data['message'] = message('warning', '', TRANS('MSG_ERROR_WRONG_FORMATTED'), '');
        echo json_encode($data);
        return false;
    }
}


/* Processamento */
if ($data['action'] == "new") {

    $sql = "SELECT * FROM modelos_itens WHERE mdit_desc = '".$data['model_name']."' AND ".
					"mdit_desc_capacidade = '".$data['model_capacity']."' ";
    
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

	$sql = "INSERT INTO modelos_itens 
        (
            mdit_tipo, mdit_fabricante, mdit_desc, mdit_desc_capacidade, mdit_sufixo
        )
		VALUES 
        (
            '" . $data['type'] . "', '" . $data['model_nanufacturer'] . "',
            '" . $data['model_name'] . "', " . dbField($data['model_capacity'],'float') . ", 
            " . dbField($data['capacity_sufix'], 'text') . "
        )";
		
    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');
        
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD') . "<br/>" . $sql;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'edit') {

    $sql = "SELECT * FROM modelos_itens WHERE mdit_desc = '".$data['model_name']."' AND ".
					"mdit_desc_capacidade = '".$data['model_capacity']."' AND mdit_cod <> '" . $data['cod'] . "'";
    
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

    $sql = "UPDATE modelos_itens SET 
    
                mdit_fabricante = '" . $data['model_nanufacturer'] . "', 
                mdit_desc = '" . $data['model_name'] . "',
                mdit_desc_capacidade = " . dbField($data['model_capacity'], 'float') . ",
                mdit_sufixo = " . dbField($data['capacity_sufix'],'text') . ",
                mdit_tipo = '" . $data['type'] . "'
            WHERE 
                mdit_cod = '" . $data['cod'] . "'";
            
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

    /* Preciso verificar os impedimentos */
    // $sql = "SELECT * FROM equipamentos WHERE comp_marca = '" . $data['cod'] . "' ";
    // $res = $conn->query($sql);
    // if ($res->rowCount()) {
    //     $data['success'] = false; 
    //     $data['message'] = TRANS('MSG_CANT_DEL');
    //     $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
    //     echo json_encode($data);
    //     return false;
    // }

    /* Por enquanto nenhum registro está sendo excluido - falta realizar todas as consistências antes e remover também os registros de histórico */
    $data['success'] = false; 
    $data['message'] = TRANS('MSG_DATA_REMOVE_NOT_IMPLEMENTED');
    $_SESSION['flash'] = message('danger', '', $data['message'], '');
    echo json_encode($data);
    return false;
    /* Sem restrições para excluir o registro */
    $sql = "DELETE FROM modelos_itens WHERE mdit_cod = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');

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



$_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
echo json_encode($data);
return false;