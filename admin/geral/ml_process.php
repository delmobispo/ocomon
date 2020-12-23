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
$mensagem = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['sigla'] = (isset($post['sigla']) ? noHtml($post['sigla']) : "");
$data['recipient'] = (isset($post['recipient']) ? noHtml($post['recipient']) : "");
$data['copy_to'] = (isset($post['copy_to']) ? noHtml($post['copy_to']) : "");
$data['field_id'] = "";

$screenNotification = "";


/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['sigla']) || empty($data['recipient'])) {
        $data['success'] = false; 
        $data['field_id'] = (empty($data['sigla']) ? 'sigla' : 'recipient');
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }

    if (!valida('E-mail', $data['recipient'], 'MAILMULTI', 1, $screenNotification)) {
        $data['success'] = false; 
        $data['field_id'] = "recipient";
        $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
        echo json_encode($data);
        return false;
    }
    if (!valida('E-mail', $data['copy_to'], 'MAILMULTI', 0, $screenNotification)) {
        $data['success'] = false; 
        $data['field_id'] = "copy_to";
        $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
        echo json_encode($data);
        return false;
    }
}


if ($data['action'] == 'edit') {

    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "UPDATE mail_list SET ml_sigla='" . $data['sigla'] . "', ml_addr_to ='" . $data['recipient'] . "', 
    ml_addr_cc = " . dbField($data['copy_to'], 'text') . "  WHERE ml_cod = '" . $data['cod'] . "'";

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

} elseif ($data['action'] == 'new') {


    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "INSERT INTO mail_list (ml_cod, ml_sigla, ml_desc, ml_addr_to, ml_addr_cc) 
            VALUES (null, '" . $data['sigla'] . "', '','" . $data['recipient'] . "'," . dbField($data['copy_to'], 'text') . ")";

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

} elseif ($data['action'] == 'delete') {
    $sql = "DELETE FROM mail_list WHERE ml_cod ='" . $data['cod'] . "'";

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