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

$config = getConfig($conn);



$erro = false;
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['numero'] = (isset($post['numero']) ? intval($post['numero']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";


$data['lang_file'] = (isset($post['lang_file']) ? noHtml($post['lang_file']) : "");
$data['date_format'] = (isset($post['date_format']) ? noHtml($post['date_format']) : "");
$data['site'] = (isset($post['site']) ? noHtml($post['site']) : "");
$data['allow_self_register'] = (isset($post['allow_self_register']) ? ($post['allow_self_register'] == "yes" ? 1 : 0) : 0);
$data['self_register_area'] = (isset($post['self_register_area']) ? noHtml($post['self_register_area']) : "");
$data['worktime_area_reference'] = (isset($post['worktime_area_reference']) ? noHtml($post['worktime_area_reference']) : "");
$data['msg'] = (isset($post['msg']) ? noHtml($post['msg']) : "");
$data['open_scheduling_status'] = (isset($post['open_scheduling_status']) ? noHtml($post['open_scheduling_status']) : "");
$data['edit_scheduling_status'] = (isset($post['edit_scheduling_status']) ? noHtml($post['edit_scheduling_status']) : "");
$data['forward_status'] = (isset($post['forward_status']) ? noHtml($post['forward_status']) : "");
$data['justificativa'] = (isset($post['justificativa']) ? ($post['justificativa'] == "yes" ? 1 : 0) : 0);
$data['allow_reopen'] = (isset($post['allow_reopen']) ? ($post['allow_reopen'] == "yes" ? 1 : 0) : 0);

$data['sla_tolerance'] = (isset($post['sla_tolerance']) ? noHtml($post['sla_tolerance']) : "");
// $data['sla_tolerance'] = (!empty($data['sla_tolerance']) ? intval($data['sla_tolerance']) : "");

$data['img_max_size'] = (isset($post['img_max_size']) ? intval($post['img_max_size'])*1024 : "");
$data['img_max_width'] = (isset($post['img_max_width']) ? noHtml($post['img_max_width']) : "");
$data['img_max_height'] = (isset($post['img_max_height']) ? noHtml($post['img_max_height']) : "");
$data['max_number_attachs'] = (isset($post['max_number_attachs']) ? intval($post['max_number_attachs']) : 0);

$data['days_before_expire'] = (isset($post['days_before_expire']) ? intval($post['days_before_expire']) : 0);
$data['area_to_alert'] = (isset($post['area_to_alert']) ? noHtml($post['area_to_alert']) : "");
$data['label_prob_tipo_1'] = (isset($post['label_prob_tipo_1']) ? noHtml($post['label_prob_tipo_1']) : "");
$data['label_prob_tipo_2'] = (isset($post['label_prob_tipo_2']) ? noHtml($post['label_prob_tipo_2']) : "");
$data['label_prob_tipo_3'] = (isset($post['label_prob_tipo_3']) ? noHtml($post['label_prob_tipo_3']) : "");


// $fileTypes = "%%IMG%";
$fileTypes = "%%";
$fileTypes .= (isset($post['upld_img']) ? ($post['upld_img'] == "yes" ? "IMG%" : "") : "");
$fileTypes .= (isset($post['upld_txt']) ? ($post['upld_txt'] == "yes" ? "TXT%" : "") : "");
$fileTypes .= (isset($post['upld_odf']) ? ($post['upld_odf'] == "yes" ? "ODF%" : "") : "");
$fileTypes .= (isset($post['upld_ooo']) ? ($post['upld_ooo'] == "yes" ? "OOO%" : "") : "");
$fileTypes .= (isset($post['upld_pdf']) ? ($post['upld_pdf'] == "yes" ? "PDF%" : "") : "");
$fileTypes .= (isset($post['upld_mso']) ? ($post['upld_mso'] == "yes" ? "MSO%" : "") : "");
$fileTypes .= (isset($post['upld_nmso']) ? ($post['upld_nmso'] == "yes" ? "NMSO%" : "") : "");
$fileTypes .= (isset($post['upld_rtf']) ? ($post['upld_rtf'] == "yes" ? "RTF%" : "") : "");
$fileTypes .= (isset($post['upld_html']) ? ($post['upld_html'] == "yes" ? "HTML%" : "") : "");
$data['fileTypes'] = $fileTypes;

$formatBar = "%%";
$formatBar .= (isset($post['formatMural']) ? ($post['formatMural'] == "yes" ? "mural%" : "") : "");
$formatBar .= (isset($post['formatOco']) ? ($post['formatOco'] == "yes" ? "oco%" : "") : "");
$data['formatBar'] = $formatBar;

// var_dump($data); exit();

/* Checagem de preenchimento dos campos obrigatórios*/
if ($data['action'] == "edit") {

    if ($data['lang_file'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "lang_file";
    } elseif ($data['date_format'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "date_format";
    } elseif ($data['site'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "site";
    } elseif ($data['self_register_area'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "self_register_area";
    } elseif ($data['msg'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "msg";
    } elseif ($data['open_scheduling_status'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "open_scheduling_status";
    } elseif ($data['edit_scheduling_status'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "edit_scheduling_status";
    } elseif ($data['forward_status'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "forward_status";
    } elseif ($data['sla_tolerance'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "sla_tolerance";
    } elseif ($data['img_max_size'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "img_max_size";
    } elseif ($data['img_max_width'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "img_max_width";
    } elseif ($data['img_max_height'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "img_max_height";
    } /* elseif ($data['max_number_attachs'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "max_number_attachs";
    } */ /* elseif ($data['days_before_expire'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "days_before_expire";
    } */ elseif ($data['area_to_alert'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "area_to_alert";
    } elseif ($data['label_prob_tipo_1'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "label_prob_tipo_1";
    }  elseif ($data['label_prob_tipo_2'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "label_prob_tipo_2";
    } elseif ($data['label_prob_tipo_3'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "label_prob_tipo_3";
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
        echo json_encode($data);
        return false;
    }

    if (!filter_var($data['site'], FILTER_VALIDATE_URL)) {
        $data['success'] = false; 
        $data['field_id'] = "site";
        $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
        echo json_encode($data);
        return false;
    }

    if (!is_numeric($data['sla_tolerance'])) {
        $data['success'] = false; 
        $data['field_id'] = "sla_tolerance";
        $data['message'] = message('warning', '', TRANS('MSG_ERROR_WRONG_FORMATTED'), '');
        echo json_encode($data);
        return false;
    }

    if ($data['sla_tolerance'] < 0 || $data['sla_tolerance'] > 99) {
        $data['success'] = false; 
        $data['field_id'] = "sla_tolerance";
        $data['message'] = message('warning', '', TRANS('MSG_INVALID_SLA_TOLERANCE'), '');
        echo json_encode($data);
        return false;
    }


    /* Confere o tamanho máximo da imagem */
    if ($data['img_max_size'] > (1024 * 1024 * 3)) {
        /* 3mb */
        $data['success'] = false; 
        $data['field_id'] = "img_max_size";
        $data['message'] = message('warning', '', TRANS('FILE_TOO_HEAVY_IN_CONFIG'), '');
        echo json_encode($data);
        return false;
    }
    
    if ($data['max_number_attachs'] > 10) {
        /* 3mb */
        $data['success'] = false; 
        $data['field_id'] = "max_number_attachs";
        $data['message'] = message('warning', '', TRANS('TOO_MANY_FILES'), '');
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

    /* ATUALIZAR TAMBÉM AS VARIÁVEIS DE SESSÃO */

    $exception = "";

    $sql = "UPDATE config SET 
                conf_language = '" . $data['lang_file'] . "', 
                conf_date_format = '" . $data['date_format'] . "', 
                conf_ocomon_site = '" . $data['site'] . "', 
                conf_schedule_status = '" . $data['open_scheduling_status'] . "', 
                conf_schedule_status_2 = '" . $data['edit_scheduling_status'] . "', 
                conf_foward_when_open = '" . $data['forward_status'] . "', 
                conf_desc_sla_out = '" . $data['justificativa'] . "', 
                conf_allow_reopen = '" . $data['allow_reopen'] . "', 
                conf_upld_size = '" . $data['img_max_size'] . "', 
                conf_upld_width = '" . $data['img_max_width'] . "', 
                conf_upld_height = '" . $data['img_max_height'] . "', 
                conf_qtd_max_anexos = '" . $data['max_number_attachs'] . "', 
                conf_formatBar = '" . $data['formatBar'] . "', 
                conf_days_bf = '" . $data['days_before_expire'] . "', 
                conf_wrty_area = '" . $data['area_to_alert'] . "', 
                conf_prob_tipo_1 = '" . $data['label_prob_tipo_1'] . "', 
                conf_prob_tipo_2 = '" . $data['label_prob_tipo_2'] . "', 
                conf_prob_tipo_3 = '" . $data['label_prob_tipo_3'] . "', 
                conf_upld_file_types = '" . $data['fileTypes'] . "', 
                conf_wt_areas = '" . $data['worktime_area_reference'] . "', 
                conf_sla_tolerance = '" . $data['sla_tolerance'] . "' 
                ";
		
    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_CONFIG');

        $_SESSION['s_formatBarMural'] = ((strpos($data['formatBar'], '%mural%')) ? 1 : 0);
        $_SESSION['s_formatBarOco'] = ((strpos($data['formatBar'], '%oco%')) ? 1 : 0);
        $_SESSION['s_date_format'] = $data['date_format'];
        $_SESSION['s_allow_reopen'] = $data['allow_reopen'];
        $_SESSION['s_language'] = $data['lang_file'];
        $_SESSION['s_wt_areas'] = $data['worktime_area_reference'];

        $sql = "UPDATE configusercall SET 
				conf_user_opencall = '" . $data['allow_self_register'] . "', 
				conf_ownarea = '" . $data['self_register_area'] . "', 
                conf_scr_msg = '" . $data['msg'] . "' WHERE conf_cod = 1 ";
        
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            $exception .= $e->getMessage();
        }
        

        
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD') . "<hr>" . $sql;
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