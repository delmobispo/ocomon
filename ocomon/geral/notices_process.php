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


$data['title'] = (isset($post['title']) ? noHtml($post['title']) : "");

$data['format_bar'] = $_SESSION['s_formatBarMural'];
$data['notice'] = (isset($post['notice']) ? trim($post['notice']) : "");
$data['notice'] = ($data['format_bar'] ? $data['notice'] : noHtml($data['notice']));

$data['type'] = (isset($post['type']) ? noHtml($post['type']) : "");
// $data['area'] = (isset($post['area']) ? noHtml($post['area']) : "");

$data['area'] = "";
if (isset($post['area']) && !empty($post['area'])) {
    $areaIN = "";
    foreach ($post['area'] as $area) {
        if (strlen($areaIN)) $areaIN .= ",";
        $areaIN .= $area;
    }
    // $terms .= " AND o.sistema IN ({$areaIN}) ";
    $data['area'] = $areaIN;
}

$data['expire_date'] = (isset($post['expire_date']) ? noHtml($post['expire_date']) : "");
$data['expire_date'] = (!empty($data['expire_date']) ? dateDB($data['expire_date'],1) : "");

$data['expire_date_copy'] = (!empty($post['expire_date_copy']) ? dateDB($post['expire_date_copy'],1) : "");

$data['active_status'] = (isset($post['active_status']) ? ($post['active_status'] == "yes" ? 1 : 0) : 0);
$data['resend'] = (isset($post['resend']) ? ($post['resend'] == "yes" ? 1 : 0) : 0);
$data['author'] = $_SESSION['s_uid'];


/* Checa avisos expirados */
$sql = "UPDATE avisos SET 
            is_active = 0 
        WHERE expire_date < '" . date("Y-m-d") . "'
";
$conn->exec($sql);



/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['title'])) {
        $data['success'] = false; 
        $data['field_id'] = "title";
    } elseif (empty($data['notice'])) {
        $data['success'] = false; 
        $data['field_id'] = "notice";
    } elseif (empty($data['type'])) {
        $data['success'] = false; 
        $data['field_id'] = "type";
    } elseif (empty($data['expire_date']) && $data['action'] == "new") {
        $data['success'] = false; 
        $data['field_id'] = "expire_date";
    } elseif (empty($data['expire_date']) && $data['active_status'] == 1 ) {
        $data['success'] = false; 
        $data['field_id'] = "expire_date";
    }  elseif (empty($data['expire_date']) && $data['active_status'] == 0 && !empty($data['expire_date_copy'])) {
        $data['expire_date'] = $data['expire_date_copy'];
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }

    if (($data['resend'] == 1) && $data['expire_date'] < date('Y-m-d')) {
        $data['success'] = false; 
        $data['field_id'] = "expire_date";
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', 'Ooops!', TRANS('RESEND_NEEDS_FUTURE_EXPIRE_DATE'),'');
        echo json_encode($data);
        return false;
    }

}


if ($data['action'] == 'new') {

    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $data['active_status'] = 1;

    $sql = "INSERT INTO 
                avisos 
                (
                    title, 
                    avisos, 
                    data, 
                    origem, 
                    status, 
                    area, 
                    expire_date,
                    is_active 
                ) 
                VALUES 
                (
                    '" . $data['title'] . "', 
                    '" . $data['notice'] . "', 
                    '" . date("Y-m-d H:i:s") . "', 
                    '" . $data['author'] . "', 
                    '" . $data['type'] . "', 
                    " . dbField($data['area'],'text') . ", 
                    " . dbField($data['expire_date'],'date') . ",
                    '" . $data['active_status'] . "'
                )";
    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');

        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
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

    $sql = "UPDATE avisos SET 
                title = '" . $data['title'] . "', 
                avisos = '" . $data['notice'] . "', 
                status = '" . $data['type'] . "',  
                area = " . dbField($data['area'],'text') . ", 
                expire_date = " . dbField($data['expire_date'],'date') . ",
                is_active = '" . $data['active_status'] . "'
            WHERE aviso_id = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');

        if ($data['resend'] == 1) {
            $sql = "DELETE FROM user_notices WHERE notice_id = '" . $data['cod'] . "' ";
            $conn->exec($sql);
        }

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

} elseif ($data['action'] == 'delete') {
    
    $sql = "DELETE FROM avisos WHERE aviso_id = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');

        /* Excluir da user_notices também */
        $sql = "DELETE FROM user_notices WHERE notice_id = '" . $data['cod'] . "'";
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        }

        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_REMOVE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
    
} elseif ($data['action'] == 'shown_notices') {

    if ($post && isset($post['notice_ids'])) {
        foreach ($post['notice_ids'] as $notice_id) {
            $sql = "INSERT INTO user_notices 
                    (
                        user_id, 
                        notice_id
                    ) 
                    VALUES 
                    (
                        '" . $data['author'] ."', 
                        '{$notice_id}'
                    )
            ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                echo $exception;
            }
        }
    }
    

    return false;
}

echo json_encode($data);