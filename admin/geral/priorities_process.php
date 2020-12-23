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



$exception = "";
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";

$data['level_name'] = (isset($post['level_name']) ? noHtml($post['level_name']) : "");
$data['level'] = (isset($post['level']) ? noHtml($post['level']) : "");
$data['priority_color'] = (isset($post['priority_color']) ? noHtml($post['priority_color']) : "");
$data['is_default'] = (isset($post['is_default']) ? ($post['is_default'] == "yes" ? 1 : 0) : 0);



/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['level_name'])) {
        $data['success'] = false; 
        $data['field_id'] = 'level_name';
    } elseif (empty($data['level'])) {
        $data['success'] = false; 
        $data['field_id'] = 'level';
    } elseif (empty($data['priority_color'])) {
        $data['success'] = false; 
        $data['field_id'] = 'priority_color';
    }

    if ($data['success'] == false ) {
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }

    if ($data['is_default'] == 0) {
        /* Confere se existe alguma prioridade marcada como padrão */
        $sql = "SELECT * FROM prior_atend WHERE pr_default = 1 AND pr_cod <> '" . $data['cod'] . "' ";
        $res = $conn->query($sql);
        if (!$res->rowCount()) {
            /* Se não existe nenhuma prioridade padrão, então essa será a padrão */
            $data['is_default'] = 1;
            $exception .= "<hr>" . TRANS('MSG_ONE_MUST_BE_DEFAULT');
        }
    }
}


if ($data['action'] == 'new') {


    $sql = "SELECT * FROM prior_atend WHERE pr_desc = '" . $data['level_name'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['field_id'] = "area";
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

    $sqlUpdDefault = "UPDATE prior_atend SET pr_default = 0 ";
    try {
        $conn->exec($sqlUpdDefault);
    }
    catch (Exception $e) {
       $exception .= "<hr>" . $e->getMessage();
    }

    $sql = "INSERT INTO prior_atend 
                (
                    pr_nivel, pr_default, pr_desc, pr_color
                ) 
                VALUES 
                (
                    '" . $data['level'] . "', 
                    '" . $data['is_default'] . "', 
                    '" . $data['level_name'] . "', 
                    '" . $data['priority_color'] . "'
                )";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');
        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'edit') {

    $sql = "SELECT pr_cod FROM prior_atend WHERE pr_desc = '" . $data['level_name'] . "' AND pr_cod <> '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['field_id'] = "area";
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


    $sqlUpdDefault = "UPDATE prior_atend SET pr_default = 0 ";
    try {
        $conn->exec($sqlUpdDefault);
    }
    catch (Exception $e) {
       $exception .= "<hr>" . $e->getMessage();
    }


    $sql = "UPDATE prior_atend SET 
                pr_nivel = '" . $data['level'] . "', pr_default = '" . $data['is_default'] . "', 
                pr_desc = '" . $data['level_name'] . "', pr_color = '" . $data['priority_color'] . "'
            WHERE pr_cod = ' " . $data['cod'] . " ' ";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');
        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'delete') {


    $sql = "SELECT * FROM ocorrencias WHERE oco_prior ='".$data['cod']."'";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

    $sql = "SELECT * FROM prior_atend WHERE pr_default = 1 AND pr_cod = '" . $data['cod'] . "'";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL_DEFAULT');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

    $sql = "DELETE FROM prior_atend WHERE pr_cod = '" . $data['cod'] . "'";

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