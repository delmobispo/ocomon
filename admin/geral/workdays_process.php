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

require_once __DIR__ . "/" . "../../includes/classes/worktime/Worktime.php";
require_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";

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

$data['name'] = (isset($post['name']) ? noHtml($post['name']) : "");

$is_default = (isset($post['is_default']) ? ($post['is_default'] == "yes" ? 1 : 'null') : 'null');
$fullTime = (isset($post['247']) ? ($post['247'] == "yes" ? 1 : 0) : 0);
$offsats = (isset($post['off-saturdays']) ? ($post['off-saturdays'] == "yes" ? 1 : 0) : 0);
$offsuns = (isset($post['off-sundays']) ? ($post['off-sundays'] == "yes" ? 1 : 0) : 0);
$offholies = (isset($post['off-holidays']) ? ($post['off-holidays'] == "yes" ? 1 : 0) : 0);




/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['name'])) {
        $data['success'] = false; 
        $data['field_id'] = 'name';
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }
    
    if ($fullTime == 1) {
        $week_ini_time_hour = "00";
        $week_ini_time_minute = "00";
        $week_end_time_hour = "23";
        $week_end_time_minute = "59";
        $week_day_full_worktime = 1440;

        $sat_ini_time_hour = "00";
        $sat_ini_time_minute = "00";
        $sat_end_time_hour = "23";
        $sat_end_time_minute = "59";
        $sat_day_full_worktime = 1440;

        $sun_ini_time_hour = "00";
        $sun_ini_time_minute = "00";
        $sun_end_time_hour = "23";
        $sun_end_time_minute = "59";
        $sun_day_full_worktime = 1440;

        $off_ini_time_hour = "00";
        $off_ini_time_minute = "00";
        $off_end_time_hour = "23";
        $off_end_time_minute = "59";
        $off_day_full_worktime = 1440;
    } else {

        $week_ini_time_hour = noHtml($post['week_ini_time_hour']);
        $week_ini_time_minute = noHtml($post['week_ini_time_minute']);

        if (($post['week_end_time_hour'] == "0" || $post['week_end_time_hour'] == "00") && ($post['week_end_time_minute'] == "0" || $post['week_end_time_minute'] == "00") && (($week_ini_time_hour != "0" && $week_ini_time_hour != "00") || ($week_ini_time_minute != "0" && $week_ini_time_minute != "00"))) {
            $week_end_time_hour = "23";
            $week_end_time_minute = "59";
        } else {
            $week_end_time_hour = noHtml($post['week_end_time_hour']);
            $week_end_time_minute = noHtml($post['week_end_time_minute']);
        }
        
        $week_day_full_worktime = daysFullWorkTime($week_ini_time_hour . ':' . $week_ini_time_minute, $week_end_time_hour . ':' . $week_end_time_minute);

        if ($offsats != 1) {
            $sat_ini_time_hour = "00";
            $sat_ini_time_minute = "00";
            $sat_end_time_hour = "00";
            $sat_end_time_minute = "00";
            $sat_day_full_worktime = 0;
        } else {
            $sat_ini_time_hour = noHtml($post['sat_ini_time_hour']);
            $sat_ini_time_minute = noHtml($post['sat_ini_time_minute']);

            if (($post['sat_end_time_hour'] == "0" || $post['sat_end_time_hour'] == "00") && ($post['sat_end_time_minute'] == "0" || $post['sat_end_time_minute'] == "00") && (($sat_ini_time_hour != "0" && $sat_ini_time_hour != "00") || ($sat_ini_time_minute != "0" && $sat_ini_time_minute != "00"))) {
                $sat_end_time_hour = "23";
                $sat_end_time_minute = "59";
            } else {
                $sat_end_time_hour = noHtml($post['sat_end_time_hour']);
                $sat_end_time_minute = noHtml($post['sat_end_time_minute']);
            }

            // $sat_end_time_hour = noHtml($post['sat_end_time_hour']);
            // $sat_end_time_minute = noHtml($post['sat_end_time_minute']);
            $sat_day_full_worktime = daysFullWorkTime($sat_ini_time_hour . ':' . $sat_ini_time_minute, $sat_end_time_hour . ':' . $sat_end_time_minute);
        }

        if ($offsuns != 1) {
            $sun_ini_time_hour = "00";
            $sun_ini_time_minute = "00";
            $sun_end_time_hour = "00";
            $sun_end_time_minute = "00";
            $sun_day_full_worktime = 0;
        } else {
            $sun_ini_time_hour = noHtml($post['sun_ini_time_hour']);
            $sun_ini_time_minute = noHtml($post['sun_ini_time_minute']);

            if (($post['sun_end_time_hour'] == "0" || $post['sun_end_time_hour'] == "00") && ($post['sun_end_time_minute'] == "0" || $post['sun_end_time_minute'] == "00") && (($sun_ini_time_hour != "0" && $sun_ini_time_hour != "00") || ($sun_ini_time_minute != "0" && $sun_ini_time_minute != "00"))) {
                $sun_end_time_hour = "23";
                $sun_end_time_minute = "59";
            } else {
                $sun_end_time_hour = noHtml($post['sun_end_time_hour']);
                $sun_end_time_minute = noHtml($post['sun_end_time_minute']);
            }

            // $sun_end_time_hour = noHtml($post['sun_end_time_hour']);
            // $sun_end_time_minute = noHtml($post['sun_end_time_minute']);
            $sun_day_full_worktime = daysFullWorkTime($sun_ini_time_hour . ':' . $sun_ini_time_minute, $sun_end_time_hour . ':' . $sun_end_time_minute);
        }

        if ($offholies != 1) {
            $off_ini_time_hour = "00";
            $off_ini_time_minute = "00";
            $off_end_time_hour = "00";
            $off_end_time_minute = "00";
            $off_day_full_worktime = 0;
        } else {
            $off_ini_time_hour = noHtml($post['off_ini_time_hour']);
            $off_ini_time_minute = noHtml($post['off_ini_time_minute']);

            if (($post['off_end_time_hour'] == "0" || $post['off_end_time_hour'] == "00") && ($post['off_end_time_minute'] == "0" || $post['off_end_time_minute'] == "00") && (($off_ini_time_hour != "0" && $off_ini_time_hour != "00") || ($off_ini_time_minute != "0" && $off_ini_time_minute != "00"))) {
                $off_end_time_hour = "23";
                $off_end_time_minute = "59";
            } else {
                $off_end_time_hour = noHtml($post['off_end_time_hour']);
                $off_end_time_minute = noHtml($post['off_end_time_minute']);
            }

            // $off_end_time_hour = noHtml($post['off_end_time_hour']);
            // $off_end_time_minute = noHtml($post['off_end_time_minute']);
            $off_day_full_worktime = daysFullWorkTime($off_ini_time_hour . ':' . $off_ini_time_minute, $off_end_time_hour . ':' . $off_end_time_minute);
        }
    }

    if ($week_day_full_worktime == 0 && $sat_day_full_worktime == 0 && $sun_day_full_worktime == 0 && $off_day_full_worktime == 0) {
        $data['success'] = false; 
        $data['field_id'] = 'week_ini_time_hour';
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', 'Ooops!', TRANS('AT_LEAST_ONE_WORKDAY_IS_NEEDED'),'');
        echo json_encode($data);
        return false;
    }
    

}


if ($data['action'] == 'new') {


    $sql = "SELECT id FROM worktime_profiles WHERE name = '" . $data['name'] . "' ";
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

    if ($is_default == 1) {

        $sql = "UPDATE worktime_profiles SET `is_default` = NULL ";
        $erro = 0;
        try {
            $sqldo = $conn->exec($sql);
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }
    }


    $sql = "INSERT INTO worktime_profiles (
        name, `is_default`,
        week_ini_time_hour, week_ini_time_minute, week_end_time_hour, week_end_time_minute, week_day_full_worktime,
        sat_ini_time_hour, sat_ini_time_minute, sat_end_time_hour, sat_end_time_minute, sat_day_full_worktime,
        sun_ini_time_hour, sun_ini_time_minute, sun_end_time_hour, sun_end_time_minute, sun_day_full_worktime,
        off_ini_time_hour, off_ini_time_minute, off_end_time_hour, off_end_time_minute, off_day_full_worktime,
        `247`)
        values (
            '" . $data['name'] . "', {$is_default},
            '{$week_ini_time_hour}', '{$week_ini_time_minute}', '{$week_end_time_hour}', '{$week_end_time_minute}', '{$week_day_full_worktime}',
            '{$sat_ini_time_hour}', '{$sat_ini_time_minute}', '{$sat_end_time_hour}', '{$sat_end_time_minute}', $sat_day_full_worktime,
            '{$sun_ini_time_hour}', '{$sun_ini_time_minute}', '{$sun_end_time_hour}', '{$sun_end_time_minute}', $sun_day_full_worktime,
            '{$off_ini_time_hour}', '{$off_ini_time_minute}', '{$off_end_time_hour}', '{$off_end_time_minute}', '{$off_day_full_worktime}',
            '{$fullTime}'
        )   
        ";


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


    // var_dump([
    //     'post' => $post,
    //     'data' => $data,
    // ]); exit();

    $sql = "SELECT id FROM worktime_profiles WHERE name = '" . $data['name'] . "' AND id <> '" . $data['cod'] . "' ";
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

    if ($is_default == 1) {

        $sql = "UPDATE worktime_profiles SET `is_default` = NULL ";
        try {
            $sqldo = $conn->exec($sql);
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }
    } else {
        /* Se não estiver marcado como perfil padrão, preciso checar se há algum outro perfil já marcado como padrão */
        $sql = "SELECT id FROM worktime_profiles WHERE is_default IS NOT NULL AND id <> '" . $data['cod'] . "' ";
        $res = $conn->query($sql);
        if (!$res->rowCount()) { /* nao encontrou nenhum outro perfil como padrao */
            $sql = "SELECT MIN(id) as id FROM worktime_profiles";
            $res = $conn->query($sql);
            $rowMinId = $res->fetch();
            if ($rowMinId['id'] == $data['cod']) { /* significa que a edição está ocorrendo no menor id - nesse caso ignoro a opção do usuário */
                $is_default = 1;
            } else {
                $sqlNullfy = "UPDATE worktime_profiles SET is_default = null ";
                $conn->exec($sqlNullfy);
                /* Defino como padrao o perfil de menor id */
                $sql = "UPDATE worktime_profiles SET is_default = 1 WHERE id = " . $rowMinId['id'] . "";
                $conn->exec($sql);
            }
        }
    }




    $sql = "UPDATE worktime_profiles SET 
            name = '" . $data['name'] . "', is_default = {$is_default},
            week_ini_time_hour = '" . $week_ini_time_hour . "',
            week_ini_time_minute = '" . $week_ini_time_minute . "',
            week_end_time_hour = '" . $week_end_time_hour . "',
            week_end_time_minute = '" . $week_end_time_minute . "',
            week_day_full_worktime = " . $week_day_full_worktime . ",

            sat_ini_time_hour = '" . $sat_ini_time_hour . "',
            sat_ini_time_minute = '" . $sat_ini_time_minute . "',
            sat_end_time_hour = '" . $sat_end_time_hour . "',
            sat_end_time_minute = '" . $sat_end_time_minute . "',
            sat_day_full_worktime = " . $sat_day_full_worktime . ",

            sun_ini_time_hour = '" . $sun_ini_time_hour . "',
            sun_ini_time_minute = '" . $sun_ini_time_minute . "',
            sun_end_time_hour = '" . $sun_end_time_hour . "',
            sun_end_time_minute = '" . $sun_end_time_minute . "',
            sun_day_full_worktime = " . $sun_day_full_worktime . ",

            off_ini_time_hour = '" . $off_ini_time_hour . "',
            off_ini_time_minute = '" . $off_ini_time_minute . "',
            off_end_time_hour = '" . $off_end_time_hour . "',
            off_end_time_minute = '" . $off_end_time_minute . "',
            off_day_full_worktime = " . $off_day_full_worktime . ",
            `247` = " . $fullTime . "

            WHERE id = '" . $data['cod'] . "'
    ";


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

} elseif ($data['action'] == 'delete') {

    // var_dump([
    //     'post' => $post,
    //     'data' => $data,
    // ]); exit();

    $sql_find = "SELECT * FROM worktime_profiles WHERE id = '" . $data['cod'] . "' AND is_default IS NOT NULL";
    $res = $conn->query($sql_find);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('CANT_REMOVE_DEFAULT_PROFILE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
    /** Ver quais serão os requisitos a serem checados para permitir ou não a exclusão do perfil */
    $sql = "SELECT sis_wt_profile FROM sistemas WHERE sis_wt_profile = '" . $data['cod'] . "'";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('CANT_REMOVE_IN_USE_PROFILE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }


    /* Sem restrições para excluir o perfil */
    $sql = "DELETE FROM worktime_profiles WHERE id = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');

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
    
}

echo json_encode($data);