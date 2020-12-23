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
$data['action'] = (isset($post['action']) ? $post['action'] : "");
$data['fromAdmin'] = (isset($post['fromAdmin']) && $post['fromAdmin'] == 1 ? $post['fromAdmin'] : "");
$data['field_id'] = "";

$data['random'] = (isset($post['random']) ? $post['random'] : ""); /* Apenas a confirmação pelo próprio usuário utiliza o random */



if (!empty($data['action']) && $data['action'] == "delete") {

    $sql = "DELETE FROM utmp_usuarios WHERE utmp_cod = '".$data['cod']."'";
    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }
    catch (Exception $e) {
        $erro = true;
    }
} elseif (!empty($data['action']) && ($data['action'] == 'autoconfirm' || $data['action'] == 'adminconfirm')) {

    $term = " utmp_rand = '" . $data['random'] . "' ";
    if ($data['action'] == 'adminconfirm') {
        $term = " utmp_cod = '" . $data['cod'] . "' ";
    }

    $sql = "SELECT * FROM utmp_usuarios WHERE {$term} ";
    try {
        $res = $conn->query($sql);
        $found = $res->rowCount();
    
        if ($found) {
    
            $qryconfig = $QRY["useropencall"];
            $execconfig = $conn->query($qryconfig);
            $rowconfig = $execconfig->fetch();
    
            $row = $res->fetch();
    
            $sqlchk = "SELECT user_id FROM usuarios WHERE login = '" . $row['utmp_login'] . "' ";
            $reschk = $conn->query($sqlchk);
    
            if ($reschk->rowCount()) {
                $data['success'] = false; 
                $data['message'] = TRANS('USERNAME_ALREADY_EXISTS');
                $_SESSION['flash'] = message('danger', '', $data['message'], '');
                echo json_encode($data);
                return false;
            }
    
            $sql = "INSERT INTO usuarios 
                (
                    user_id, login, nome, password, data_inc, data_admis, email, fone, nivel, AREA
                ) 
                VALUES 
                (
                    null, '" . $row['utmp_login'] . "', '" . $row['utmp_nome'] . "', '" . $row['utmp_passwd'] . "', 
                            '" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "','" . $row['utmp_email'] . "', 
                            '" . $row['utmp_phone'] . "', 3, 
                            '" . $rowconfig['conf_ownarea'] . "'
                ) ";
    
            try {
                $conn->exec($sql);
    
                // $sqlDel = "DELETE FROM utmp_usuarios WHERE utmp_rand = '".$data['random']."'";
                $sqlDel = "DELETE FROM utmp_usuarios WHERE {$term} ";
                $conn->exec($sqlDel);
    
                $data['success'] = true; 
                $data['message'] = TRANS('SUBSCRIPTION_CONFIRMED');
                $_SESSION['flash'] = message('success', '', $data['message'], '');
                echo json_encode($data);
                return false;
            }
            catch (Exception $e) {
                $erro = true;
            }
    
        } else {
            $data['success'] = false; 
            $data['message'] = TRANS('EMAIL_CONFIRMATION_EXPIRED');
            $_SESSION['flash'] = message('danger', '', $data['message'], '');
            echo json_encode($data);
            return false;
        }
    
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }


}








echo json_encode($data);