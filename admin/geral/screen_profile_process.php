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
$mensagem = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";


$data['profile_name'] = (isset($post['profile_name']) ? noHtml($post['profile_name']) : "");
$data['allow_user_open'] =  (isset($post['allow_user_open']) ? ($post['allow_user_open'] == "yes" ? 1 : 0) : "");
$data['area_to'] = (isset($post['area_to']) ? $post['area_to'] : "");

$data['field_area'] = (isset($post['field_area']) ? ($post['field_area'] == "yes" ? 1 : 0) : 0);
$data['field_issue'] = (isset($post['field_issue']) ? ($post['field_issue'] == "yes" ? 1 : 0) : 0);
$data['field_description'] = (isset($post['field_description']) ? ($post['field_description'] == "yes" ? 1 : 0) : 0);
$data['field_unit'] = (isset($post['field_unit']) ? ($post['field_unit'] == "yes" ? 1 : 0) : 0);
$data['field_tag_number'] = (isset($post['field_tag_number']) ? ($post['field_tag_number'] == "yes" ? 1 : 0) : 0);
$data['field_tag_check'] = (isset($post['field_tag_check']) ? ($post['field_tag_check'] == "yes" ? 1 : 0) : 0);
$data['field_tag_tickets'] = (isset($post['field_tag_tickets']) ? ($post['field_tag_tickets'] == "yes" ? 1 : 0) : 0);
$data['field_contact'] = (isset($post['field_contact']) ? ($post['field_contact'] == "yes" ? 1 : 0) : 0);
$data['field_contact_email'] = (isset($post['field_contact_email']) ? ($post['field_contact_email'] == "yes" ? 1 : 0) : 0);
$data['field_phone'] = (isset($post['field_phone']) ? ($post['field_phone'] == "yes" ? 1 : 0) : 0);
$data['field_department'] = (isset($post['field_department']) ? ($post['field_department'] == "yes" ? 1 : 0) : 0);
$data['field_load_department'] = (isset($post['field_load_department']) ? ($post['field_load_department'] == "yes" ? 1 : 0) : 0);
$data['field_search_dep_tags'] = (isset($post['field_search_dep_tags']) ? ($post['field_search_dep_tags'] == "yes" ? 1 : 0) : 0);
$data['field_operator'] = (isset($post['field_operator']) ? ($post['field_operator'] == "yes" ? 1 : 0) : 0);
$data['field_date'] = (isset($post['field_date']) ? ($post['field_date'] == "yes" ? 1 : 0) : 0);
$data['field_schedule'] = (isset($post['field_schedule']) ? ($post['field_schedule'] == "yes" ? 1 : 0) : 0);
$data['field_forward'] = (isset($post['field_forward']) ? ($post['field_forward'] == "yes" ? 1 : 0) : 0);
$data['field_status'] = (isset($post['field_status']) ? ($post['field_status'] == "yes" ? 1 : 0) : 0);
$data['field_replicate'] = (isset($post['field_replicate']) ? ($post['field_replicate'] == "yes" ? 1 : 0) : 0);
$data['field_attach_file'] = (isset($post['field_attach_file']) ? ($post['field_attach_file'] == "yes" ? 1 : 0) : 0);
$data['field_priority'] = (isset($post['field_priority']) ? ($post['field_priority'] == "yes" ? 1 : 0) : 0);
$data['field_send_mail'] = (isset($post['field_send_mail']) ? ($post['field_send_mail'] == "yes" ? 1 : 0) : 0);

$data['opening_message'] = (isset($post['opening_message']) ? noHtml($post['opening_message']) : "");


$screenNotification = "";


/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['profile_name']) || empty($data['opening_message'])) {
        $data['success'] = false; 
        $data['field_id'] = (empty($data['profile_name']) ? 'profile_name' : 'opening_message');
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
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

    $sql = "UPDATE configusercall SET 
				conf_name= '" . $data['profile_name'] . "', 
				conf_user_opencall= " . $data['allow_user_open'] . ", 
				conf_opentoarea = " . $data['area_to'] . ", 
				conf_scr_area = " . $data['field_area'] . ", conf_scr_prob = " . $data['field_issue'] . ", 
				conf_scr_desc = " . $data['field_description'] . ", conf_scr_unit = " . $data['field_unit'] . ", 
				conf_scr_tag = " . $data['field_tag_number'] . ", conf_scr_chktag = " . $data['field_tag_check'] . ", 
                conf_scr_chkhist = " . $data['field_tag_tickets'] . ", conf_scr_contact = " . $data['field_contact'] . ", 
                conf_scr_contact_email = " . $data['field_contact_email'] . ", 
				conf_scr_fone = " . $data['field_phone'] . ", conf_scr_local = " . $data['field_department'] . ", 
				conf_scr_btloadlocal = " . $data['field_load_department'] . ", conf_scr_searchbylocal = " . $data['field_search_dep_tags'] . " ,
				conf_scr_operator = " . $data['field_operator'] . ", conf_scr_date = " . $data['field_date'] . ", 
				conf_scr_schedule = " . $data['field_schedule'] . ", 
				conf_scr_foward = " . $data['field_forward'] . ", 
				conf_scr_status = " . $data['field_status'] . ", conf_scr_replicate = " . $data['field_replicate'] . " ,
				conf_scr_upload = " . $data['field_attach_file'] . " ,
				conf_scr_mail = " . $data['field_send_mail'] . ", conf_scr_msg = '" . $data['opening_message'] . "' ,
				conf_scr_prior = " . $data['field_priority'] . " 
				
				WHERE conf_cod=" . $data['cod'] . " ";
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

    $sql = "INSERT INTO configusercall 
            (
                conf_name, conf_user_opencall, conf_opentoarea, conf_scr_area, conf_scr_prob, conf_scr_desc, 
                conf_scr_unit, conf_scr_tag, conf_scr_chktag, conf_scr_chkhist, conf_scr_contact, conf_scr_contact_email, 
                conf_scr_fone, 
                conf_scr_local, conf_scr_btloadlocal, conf_scr_searchbylocal, conf_scr_operator, conf_scr_date, 
                conf_scr_schedule, conf_scr_foward, conf_scr_status, conf_scr_replicate, conf_scr_upload, 
                conf_scr_mail, conf_scr_msg, conf_scr_prior
            ) 
            VALUES 
            (
                '" . $data['profile_name'] . "', " . $data['allow_user_open'] . ", " . $data['area_to'] . ", " . $data['field_area'] . ", 
                " . $data['field_issue'] . ", " . $data['field_description'] . ", " . $data['field_unit'] . ", " . $data['field_tag_number'] . ", 
                " . $data['field_tag_check'] . ", " . $data['field_tag_tickets'] . ", " . $data['field_contact'] . ", 
                " . $data['field_contact_email'] . ", " . $data['field_phone'] . ", 
                " . $data['field_department'] . ", " . $data['field_load_department'] . ", " . $data['field_search_dep_tags'] . " , " . $data['field_operator'] . ", 
                " . $data['field_date'] . ", " . $data['field_schedule'] . ", " . $data['field_forward'] . ", " . $data['field_status'] . ", 
                " . $data['field_replicate'] . " ," . $data['field_attach_file'] . " , " . $data['field_send_mail'] . ", '" . $data['opening_message'] . "', 
                " . $data['field_priority'] . " 
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

} elseif ($data['action'] == 'delete') {

    $sql = "SELECT * FROM sistemas where sis_screen='" . $data['cod'] . "'";
    $res = $conn->query($sql);
    $achou = $res->rowCount();

    if ($achou) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }
    
    $sql =  "DELETE FROM configusercall WHERE conf_cod='".$data['cod']."'";

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