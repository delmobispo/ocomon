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


include ("includes/functions/functions.php");
include ("includes/config.inc.php");
include ("includes/versao.php");
include ("includes/languages/".LANGUAGE.""); //TEMPORARIAMENTE
require_once ("includes/classes/ConnectPDO.php");

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$post = $_POST;

$erro = false;
$exception = "";
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";

$data['login_name'] = (isset($post['login_name']) ? noHtml($post['login_name']) : "");
$data['password'] = (isset($post['password']) && !empty($post['password']) ? $post['password'] : "");
$data['password2'] = (isset($post['password2']) && !empty($post['password2']) ? $post['password2'] : "");
$data['fullname'] = (isset($post['fullname']) ? noHtml($post['fullname']) : "");
$data['email'] = (isset($post['email']) ? noHtml($post['email']) : "");
$data['phone'] = (isset($post['phone']) ? noHtml($post['phone']) : "");


/* Validações */
if (empty($data['login_name']) || empty($data['fullname']) || 
        empty($data['password']) || empty($data['password2']) || 
        empty($data['email']) || empty($data['phone']) ) {

    $data['success'] = false; 

    if (empty($data['login_name'])) {
        $data['field_id'] = 'login_name';
    } elseif (empty($data['fullname'])) {
        $data['field_id'] = 'fullname';
    } elseif (empty($data['email'])) {
        $data['field_id'] = 'email';
    } elseif (empty($data['phone'])) {
        $data['field_id'] = 'phone';
    } elseif (empty($data['password'])) {
        $data['field_id'] = 'password';
    } elseif (empty($data['password2'])) {
        $data['field_id'] = 'password2';
    }
    
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
    echo json_encode($data);
    return false;

}

if (!valida('Usuário', $data['login_name'], 'USUARIO', 1, $screenNotification)) {
    $data['success'] = false; 
    $data['field_id'] = "login_name";
    $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
    echo json_encode($data);
    return false;
}

if (!valida('E-mail', $data['email'], 'MAIL', 1, $screenNotification)) {
    $data['success'] = false; 
    $data['field_id'] = "recipient";
    $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
    echo json_encode($data);
    return false;
}

if ($data['password'] !== $data['password2']) {
    $data['success'] = false; 
    $data['field_id'] = "password";
    $screenNotification .= TRANS('PASSWORDS_DOESNT_MATCH');
    $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
    echo json_encode($data);
    return false;
}


$sql = "SELECT login FROM usuarios WHERE login = '" . $data['login_name'] . "'";
$res = $conn->query($sql);
$found = $res->rowCount();

if ($found) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('USERNAME_ALREADY_EXISTS'),'');

    echo json_encode($data);
    return false;
}


$sql = "SELECT utmp_login FROM utmp_usuarios WHERE utmp_login = '" . $data['login_name'] . "'";
$res = $conn->query($sql);
$found = $res->rowCount();

if ($found) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('USERNAME_ALREADY_TAKEN'),'');

    echo json_encode($data);
    return false;
}


if (!csrf_verify($post)) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

    echo json_encode($data);
    return false;
}


$row_config = getConfig($conn);

$random = random64();
$sql= "INSERT INTO utmp_usuarios 
        (
            utmp_cod, utmp_login, utmp_nome, utmp_email, utmp_phone, utmp_passwd, utmp_rand
        ) 
        VALUES 
		(
            null, '" . $data['login_name'] . "', '" . $data['fullname'] . "','" . $data['email'] . "', 
        '" . $data['phone'] . "' , '" . $data['password'] . "', '".$random."'
        )";

try {
    $conn->exec($sql);

    $VARS = array();
    $VARS['%login%'] = $data['login_name'];
    $VARS['%usuario%'] = $data['fullname'];
    $VARS['%site%'] = "<a href='".$row_config['conf_ocomon_site']."'>".$row_config['conf_ocomon_site']."</a>";
    $VARS['%linkconfirma%'] = "<a href='".$row_config['conf_ocomon_site']."/ocomon/geral/confirm_subscription.php?rand=".urlencode($random)."'>".TRANS('MSG_LINK_CONFIRM_SUBSCRIBE')."</a>";

    $rowconf = getMailConfig($conn);
    $event = 'cadastro-usuario';
    $rowmsg = getEventMailConfig($conn, $event);

    send_mail($event, $data['email'], $rowconf, $rowmsg, $VARS);


    $data['success'] = true; 
    $data['message'] = TRANS('USER_SELF_REGISTER_SUCCESS');
    $_SESSION['flash'] = message('success', '', $data['message'], '');
    echo json_encode($data);
    return false;
} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['success'] = false; 
    $data['message'] = TRANS('MSG_ERR_SAVE_RECORD');
    $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
    echo json_encode($data);
    return false;
}



echo json_encode($data);