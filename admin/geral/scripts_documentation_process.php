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

$data['script_name'] = (isset($post['script_name']) ? noHtml($post['script_name']) : "");
$data['enduser'] = (isset($post['enduser']) ? ($post['enduser'] == "yes" ? 1 : 0) : 0);
$data['description'] = (isset($post['description']) ? noHtml($post['description']) : "");
$data['script_content'] = (isset($post['script_content']) ? $post['script_content'] : "");
$data['area'] = (isset($post['area']) && $post['area'] != '-1' ? noHtml($post['area']) : "");
$data['problema'] = (isset($post['problema']) && $post['problema'] != '-1' ? noHtml($post['problema']) : "");
$data['radio_prob'] = (isset($post['radio_prob']) && $post['radio_prob'] != '-1' ? noHtml($post['radio_prob']) : $data['problema']);

$data['delProb'] = (isset($post['delProb']) ? $post['delProb'] : "");





/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['script_name'])) {
        $data['success'] = false; 
        $data['field_id'] = 'script_name';
    } elseif (empty($data['description'])) {
        $data['success'] = false; 
        $data['field_id'] = 'description';
    } elseif (empty($data['script_content'])) {
        $data['success'] = false; 
        $data['field_id'] = 'script_content';
    } elseif (empty($data['radio_prob']) && $data['action'] == 'new') {
        $data['success'] = false; 
        $data['field_id'] = 'idProblema';
    } 


    if ($data['success'] == false) {
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }

}




if ($data['action'] == 'new') {


    $sql = "SELECT scpt_id FROM scripts WHERE scpt_nome = '" . $data['script_name'] . "' ";
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

    $sql = "INSERT INTO 
                scripts 
                (
                    scpt_nome, 
                    scpt_desc, 
                    scpt_script, 
                    scpt_enduser 
                ) 
                VALUES 
                (
                    '" . $data['script_name'] . "', 
                    '" . $data['description'] . "', 
                    '" . $data['script_content'] . "', 
                    '" . $data['enduser'] . "' 
                )
    ";

    try {
        $conn->exec($sql);
        $script_id = $conn->lastInsertId();
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');


        $sql = "INSERT INTO prob_x_script 
                    (
                        prscpt_prob_id, prscpt_scpt_id
                    )
                    VALUES
                    (
                        '" . $data['radio_prob'] . "', {$script_id}
                    )
        ";
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
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'edit') {


    $sql = "SELECT scpt_id FROM scripts WHERE scpt_nome = '" . $data['script_name'] . "' AND scpt_id <> '" . $data['cod'] . "' ";
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


    $sql = "UPDATE scripts SET 
                scpt_nome = '" . $data['script_name'] . "', 
                scpt_desc = '" . $data['description'] . "', 
                scpt_script = '" . $data['script_content'] . "', 
                scpt_enduser = '" . $data['enduser'] . "'  
            WHERE scpt_id = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');

        if (!empty($data['radio_prob'])) {
            $sql = "INSERT INTO 
                        prob_x_script 
                        (
                            prscpt_prob_id, prscpt_scpt_id
                        ) VALUES 
                        (
                            '" . $data['radio_prob'] . "', '" . $data['cod'] . "'
                        ) 
            ";

            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
            }
        }

        
        if (!empty($data['delProb'])) {
            foreach ($data['delProb'] as $delProb) {
                $sqlDel = "DELETE FROM prob_x_script WHERE prscpt_id = '" . $delProb . "' ";
                try {
                    $conn->exec($sqlDel);
                }
                catch (Exception $e) {
                    $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                }
            }
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


    $sql = "DELETE FROM scripts WHERE scpt_id = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');

        
        $sql = "DELETE FROM prob_x_script WHERE prscpt_scpt_id = '" . $data['cod'] . "' ";
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
    
}

echo json_encode($data);