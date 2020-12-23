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



if ($data['action'] == 'edit') {

    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $terms = "";
    if (!empty($data['password'])) {

        if ($data['password'] !== $data['password2']) {
            $data['success'] = false; 
            $data['field_id'] = "password";
            $screenNotification .= TRANS('PASSWORDS_DOESNT_MATCH');
            $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
            echo json_encode($data);
            return false;
        }
        $terms = " password = '" . $data['password'] . "', ";
    }

    $sql = "UPDATE usuarios SET 
				nome= '" . $data['fullname'] . "', 
                {$terms}
				data_admis = " . dbField($data['hire_date'],'date') . ", 
				email = '" . $data['email'] . "', 
				fone = '" . $data['phone'] . "', 
				nivel = '" . $data['level'] . "', 
				AREA = '" . $data['primary_area'] . "', 
				user_admin = " . $data['area_admin'] . "
				
				WHERE user_id = " . $data['cod'] . " ";
    try {
        $conn->exec($sql);

        $sqlDel = "DELETE FROM usuarios_areas WHERE uarea_uid = " . $data['cod'] . " ";
        try {
            $conn->exec($sqlDel);
            foreach ($secondary_areas as $area) {
                $sql = "INSERT INTO usuarios_areas (uarea_cod, uarea_uid, uarea_sid) VALUES (null, " . $data['cod'] . ", {$area})";
                $conn->exec($sql);
            }
        }
        catch (Exception $e) {
            echo 'Erro: ', $e->getMessage(), "<br/>";
            $erro = true;
        }

        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE') . "<br />". $sql;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'new') {

    $sql = "SELECT login FROM usuarios WHERE login = '" . $data['login_name'] . "'";
    $res = $conn->query($sql);
    $found = $res->rowCount();

    if ($found) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('USERNAME_ALREADY_EXISTS'),'');
    
        echo json_encode($data);
        return false;
    }

    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }
    

    $sql = "INSERT INTO usuarios 
            (
                login, nome, password, data_inc, data_admis, email, 
                fone, nivel, AREA, user_admin
            ) 
            VALUES 
            (
                '" . $data['login_name'] . "', '" . $data['fullname'] . "', '" . $data['password'] . "', 
                '" . $data['subscribe_date'] . "', 
                " . dbField($data['hire_date'],'date') . ", '" . $data['email'] . "', '" . $data['phone'] . "', '" . $data['level'] . "', 
                '" . $data['primary_area'] . "', " . $data['area_admin'] . " 
            )";


    try {
        $conn->exec($sql);
        $uid = $conn->lastInsertId();

        foreach ($secondary_areas as $area) {
            $sql = "INSERT INTO usuarios_areas (uarea_cod, uarea_uid, uarea_sid) VALUES (null, {$uid}, {$area})";
            $conn->exec($sql);
        }

        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD') . "<br/>" . $sql;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'delete') {

    $sql = "SELECT * FROM ocorrencias WHERE aberto_por = '" . $data['cod'] . "' OR operador='" . $data['cod'] . "'";
    $res = $conn->query($sql);
    $achou = $res->rowCount();

    if ($achou) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }
    
    $sql =  "DELETE FROM usuarios WHERE user_id = '".$data['cod']."'";

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