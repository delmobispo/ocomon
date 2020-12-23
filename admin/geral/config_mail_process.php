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


$config = getMailConfig($conn);



$erro = false;
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['numero'] = (isset($post['numero']) ? intval($post['numero']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";


$data['mail_is_smtp'] = (isset($post['mail_is_smtp']) ? ($post['mail_is_smtp'] == "yes" ? 1 : 0) : 0);
$data['mail_host'] = (isset($post['mail_host']) ? noHtml($post['mail_host']) : "");
$data['smtp_port'] = (isset($post['smtp_port']) ? $post['smtp_port'] : "");
$data['smtp_secure'] = (isset($post['smtp_secure']) ? noHtml($post['smtp_secure']) : "");
$data['need_authentication'] = (isset($post['need_authentication']) ? ($post['need_authentication'] == "yes" ? 1 : 0) : 0);
$data['smtp_user'] = (isset($post['smtp_user']) ? noHtml($post['smtp_user']) : "");
$data['smtp_pass'] = (isset($post['smtp_pass']) ? $post['smtp_pass'] : "");
$data['address_from'] = (isset($post['address_from']) ? $post['address_from'] : "");
$data['address_from_name'] = (isset($post['address_from_name']) ? $post['address_from_name'] : "");
$data['html_content'] = (isset($post['html_content']) ? ($post['html_content'] == "yes" ? 1 : 0) : 0);


// var_dump($data); exit();

/* Checagem de preenchimento dos campos obrigatórios*/
if ($data['action'] == "edit") {

    if ($data['mail_host'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "mail_host";
    } elseif ($data['smtp_port'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "smtp_port";
    } elseif ($data['smtp_secure'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "smtp_secure";
    } elseif ($data['need_authentication'] == 1 && $data['smtp_user'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "smtp_user";
    } elseif ($data['need_authentication'] == 1 && empty($data['smtp_pass'])) {
        $data['success'] = false; 
        $data['field_id'] = "smtp_pass";
    } elseif ($data['address_from'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "address_from";
    } elseif ($data['address_from_name'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "address_from_name";
    }

    
    if ($data['success'] == false) {
        $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
        echo json_encode($data);
        return false;
    }

    if (!filter_var($data['mail_host'], FILTER_VALIDATE_DOMAIN)) {
        /* FILTER_VALIDATE_DOMAIN */
        $data['success'] = false; 
        $data['field_id'] = "mail_host";
        $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
        echo json_encode($data);
        return false;
    }
    
    if (!filter_var($data['smtp_port'], FILTER_VALIDATE_INT)) {
        /* FILTER_VALIDATE_DOMAIN */
        $data['success'] = false; 
        $data['field_id'] = "smtp_port";
        $data['message'] = message('warning', '', TRANS('MSG_ERROR_WRONG_FORMATTED'), '');
        echo json_encode($data);
        return false;
    }

    if (!filter_var($data['address_from'], FILTER_VALIDATE_EMAIL)) {
        $data['success'] = false; 
        $data['field_id'] = "address_from";
        $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
        echo json_encode($data);
        return false;
    }

}


/* Processamento */
if ($data['action'] == "edit") {

    /* Verificação de CSRF */
    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
        echo json_encode($data);
        return false;
    }

    $exception = "";

    $sql = "UPDATE mailconfig SET 
                mail_issmtp = '" . $data['mail_is_smtp'] . "', 
                mail_host = '" . $data['mail_host'] . "', 
                mail_port = '" . $data['smtp_port'] . "', 
                mail_secure = '" . $data['smtp_secure'] . "', 
                mail_isauth = '" . $data['need_authentication'] . "', 
                mail_user = '" . $data['smtp_user'] . "', 
                mail_pass = '" . $data['smtp_pass'] . "', 
                mail_from = '" . $data['address_from'] . "', 
                mail_from_name = '" . $data['address_from_name'] . "', 
                mail_ishtml = '" . $data['html_content'] . "'
                ";
		
    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');

        
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD') . "<hr>" . $sql . "<hr>" . $e->getMessage();
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} 

if (!empty($exception)) {
    $data['message'] = $data['message'] . "<hr>" . $exception;
}

$_SESSION['flash'] = message('success', '', $data['message'], '');
echo json_encode($data);
return false;

echo json_encode($data);