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

// var_dump($post); exit();

$erro = false;
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";


$data['msg_from'] = (isset($post['msg_from']) ? noHtml($post['msg_from']) : "");
$data['reply_to'] = (isset($post['reply_to']) ? noHtml($post['reply_to']) : "");
$data['subject'] = (isset($post['subject']) ? noHtml($post['subject']) : "");
$data['body_content'] = (isset($post['body_content']) ? $post['body_content'] : "");
// $data['body_content'] = (isset($post['body_content_copy']) ? $post['body_content_copy'] : "");
$data['alternative_content'] = (isset($post['alternative_content']) ? noHtml($post['alternative_content']) : "");





/* Validações */
if ($data['action'] == "edit") {

    if (empty($data['msg_from']) || empty($data['subject']) || empty($data['body_content']) || empty($data['alternative_content'])) {
        $data['success'] = false; 

        if (empty($data['msg_from'])) {
            $data['field_id'] = 'msg_from';
        } elseif (empty($data['reply_to'])) {
            $data['field_id'] = 'reply_to';
        } elseif (empty($data['subject'])) {
            $data['field_id'] = 'subject';
        } elseif (empty($data['body_content'])) {
            $data['field_id'] = 'body_content';
        } elseif (empty($data['alternative_content'])) {
            $data['field_id'] = 'alternative_content';
        }

        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }

    if (!valida('E-mail', $data['reply_to'], 'MAIL', 1, $screenNotification)) {
        $data['success'] = false; 
        $data['field_id'] = "reply_to";
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

    $sql = "UPDATE msgconfig SET 
                msg_fromname = '" . $data['msg_from'] . "', msg_replyto = '" . $data['reply_to'] . "', 
                msg_subject = '" . $data['subject'] . "', msg_body = '" . $data['body_content'] . "', 
                msg_altbody = '" . $data['alternative_content'] . "' WHERE msg_cod = " . $data['cod'] . "";

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

} 

echo json_encode($data);