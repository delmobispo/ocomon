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

$screenNotification = "";
$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";

$data['current_pass'] = (isset($post['current_pass']) ? noHtml($post['current_pass']) : "");
$data['new_pass_1'] = (isset($post['new_pass_1']) ? noHtml($post['new_pass_1']) : "");
$data['new_pass_2'] = (isset($post['new_pass_2']) ? noHtml($post['new_pass_2']) : "");



/* Validações */
if ($data['action'] == "edit") {

    if (empty($data['current_pass']) || empty($data['new_pass_1']) || empty($data['new_pass_2'])) {
        $data['success'] = false; 
        $data['field_id'] = (empty($data['current_pass']) ? 'current_pass' : (empty($data['new_pass_1']) ? 'new_pass_1' : 'new_pass_2'));
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }

    if ($data['new_pass_1'] !== $data['new_pass_2']) {
        $data['success'] = false; 
        $data['field_id'] = "new_pass_1";
        $data['message'] = message('warning', 'Ooops!', TRANS('PASSWORDS_DOESNT_MATCH'),'');
        echo json_encode($data);
        return false;
    }

    
    $sql = "SELECT user_id FROM usuarios WHERE user_id = '" . $data['cod'] . "' AND password = '" . $data['current_pass'] . "'";
    $res = $conn->query($sql);
    if (!$res->rowCount()) {
        $data['success'] = false; 
        $data['field_id'] = "new_pass_1";
        $data['message'] = message('warning', 'Ooops!', TRANS('ERR_LOGON'),'');
        echo json_encode($data);
        return false;
    }

    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "UPDATE usuarios SET password = '" . $data['new_pass_1'] . "' WHERE user_id = '" . $data['cod'] . "' ";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');

        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
}

echo json_encode($data);