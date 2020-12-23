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

$post = $_POST;



$erro = false;
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";

$data['format_bar'] = $_SESSION['s_formatBarOco'];
$data['problema'] = (isset($post['problema']) ? noHtml($post['problema']) : "");
$data['area'] = (isset($post['area']) ? noHtml($post['area']) : "");
$data['sla'] = (isset($post['sla']) ? noHtml($post['sla']) : "");
$data['tipo_1'] = (isset($post['tipo_1']) ? noHtml($post['tipo_1']) : "");
$data['tipo_2'] = (isset($post['tipo_2']) ? noHtml($post['tipo_2']) : "");
$data['tipo_3'] = (isset($post['tipo_3']) ? noHtml($post['tipo_3']) : "");

$data['descricao'] = (isset($post['descricao']) ? $post['descricao'] : "");
$data['descricao'] = ($data['format_bar'] ? $data['descricao'] : noHtml($data['descricao']));


/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['problema'])) {
        $data['success'] = false; 
        $data['field_id'] = "problema";
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }
}



if ($data['action'] == 'new') {


    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "INSERT INTO problemas 
        (
            problema, prob_area, prob_sla, prob_tipo_1, prob_tipo_2, prob_tipo_3, prob_descricao
        ) 
        VALUES 
        (
            '" . $data['problema'] . "', 
            " . dbField($data['area']) . ", 
            " . dbField($data['sla']) . ", 
            " . dbField($data['tipo_1']) . ", 
            " . dbField($data['tipo_2']) . ", 
            " . dbField($data['tipo_3']) . ", 
            '" . $data['descricao'] . "' 
        )";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'edit') {

    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }


    $sql = "UPDATE problemas SET 
				problema = '" . $data['problema'] . "', 
                prob_area = " . dbField($data['area']) . ", 
                prob_sla = " . dbField($data['sla']) . ",  
                prob_tipo_1 = " . dbField($data['tipo_1']) . ", 
                prob_tipo_2 = " . dbField($data['tipo_2']) . ", 
                prob_tipo_3 = " . dbField($data['tipo_3']) . ",  
				prob_descricao = '" . $data['descricao'] . "' 
            WHERE prob_id='" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'delete') {


    $sqlFindPrevention = "SELECT numero FROM ocorrencias WHERE problema = '" . $data['cod'] . "'";
    $resFindPrevention = $conn->query($sqlFindPrevention);
    $foundPrevention = $resFindPrevention->rowCount();

    if ($foundPrevention) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }


    $sql = "DELETE FROM problemas WHERE prob_id = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_REMOVE');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }
    
}

echo json_encode($data);