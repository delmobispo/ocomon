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

$data['sigla'] = (isset($post['sigla']) ? noHtml($post['sigla']) : "");
$data['subject'] = (isset($post['subject']) ? noHtml($post['subject']) : "");
$data['body_content'] = (isset($post['body_content']) ? noHtml($post['body_content']) : "");



/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['sigla']) || empty($data['subject']) || empty($data['body_content'])) {
        $data['success'] = false; 
        $data['field_id'] = (empty($data['sigla']) ? 'sigla' : (empty($data['subject']) ? 'subject' : 'body_content'));
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }

    // if (!valida('E-mail', $data['subject'], 'MAILMULTI', 1, $screenNotification)) {
    //     $data['success'] = false; 
    //     $data['field_id'] = "subject";
    //     $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
    //     echo json_encode($data);
    //     return false;
    // }
}


if ($data['action'] == 'new') {


    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "INSERT INTO mail_templates (tpl_sigla, tpl_subject, tpl_msg_html, tpl_msg_text) values 
                ('" . $data['sigla'] . "', '" . $data['subject'] . "', 
                '" . $data['body_content'] . "', '" . $data['body_content'] . "')";

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

    

    $sql = "UPDATE mail_templates SET 
                tpl_sigla = '" . $data['sigla'] . "', tpl_subject = '" . $data['subject'] . "',
                tpl_msg_html = '" . $data['body_content'] . "', tpl_msg_text = '" . $data['body_content'] . "' 
            WHERE tpl_cod = ' " . $data['cod'] . " ' ";


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
    $sql = "DELETE FROM mail_templates WHERE tpl_cod = '" . $data['cod'] . "'";

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