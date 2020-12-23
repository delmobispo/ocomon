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

// var_dump([
//     'post' => $post,
// ]); exit();

$screenNotification = "";
$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";

$data['area'] = (isset($post['area']) ? noHtml($post['area']) : "");
$data['process_tickets'] = (isset($post['process_tickets']) ? ($post['process_tickets'] == "yes" ? 1 : 0) : 0);
$data['email'] = (isset($post['email']) ? noHtml($post['email']) : "");
$data['screen_profile'] = (isset($post['screen_profile']) ? noHtml($post['screen_profile']) : "");
$data['status'] = (isset($post['status']) ? noHtml($post['status']) : "");
$data['wt_profile'] = (isset($post['wt_profile']) ? noHtml($post['wt_profile']) : "");
$data['mod_tickets'] = (isset($post['mod_tickets']) ? ($post['mod_tickets'] == "yes" ? 1 : 0) : 0);
$data['mod_inventory'] = (isset($post['mod_inventory']) ? ($post['mod_inventory'] == "yes" ? 1 : 0) : 0);

$modules = [];
if ($data['mod_tickets'] == 1) $modules[] = 1; /* ocorrencias */
if ($data['mod_inventory'] == 1) $modules[] = 2; /* inventário */




/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['area']) || empty($data['email'])) {
        $data['success'] = false; 
        $data['field_id'] = (empty($data['area']) ? 'area' : 'email');
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $data['success'] = false; 
        $data['field_id'] = "email";
        $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
        echo json_encode($data);
        return false;
    }
}

if ($data['action'] == 'new') {


    $sql = "SELECT sis_id FROM sistemas WHERE sistema = '" . $data['area'] . "' ";
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
                sistemas 
                (
                    sistema, 
                    sis_status, 
                    sis_email, 
                    sis_atende, 
                    sis_screen, 
                    sis_wt_profile 
                ) 
                VALUES 
                (
                    '" . $data['area'] . "', 
                    '" . $data['status'] . "', 
                    '" . $data['email'] . "', 
                    '" . $data['process_tickets'] . "', 
                    " . dbField($data['screen_profile']) . ", 
                    '" . $data['wt_profile'] . "'
                )";


    try {
        $conn->exec($sql);
        $areaId = $conn->lastInsertId();
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');

        /* VER */
        // $sql = "UPDATE configusercall SET conf_ownarea_2 = CONCAT(conf_ownarea_2,'".$newArea."') ";
        /* Além disso: A área deve poder abrir chamado para ela mesma (se for do tipo que presta atendimento) */

        /* Módulos de acesso */
        foreach ($modules as $mod) {
            $sql = "INSERT INTO permissoes 
                (
                    perm_area, 
                    perm_modulo
                )
                VALUES 
                (
                    {$areaId},
                    {$mod}
                )
                ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
            }
        }

        /* Se for do tipo que presta atendimento, deve poder abrir chamado para ela mesma */
        if ($data['process_tickets'] == 1) {
            $sql = "INSERT INTO areaxarea_abrechamado 
                        (
                            area, area_abrechamado
                        )
                        VALUES
                        (
                            {$areaId}, {$areaId}
                        )
            ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
            }
        }


        /* atualizar as configurações de áreas que podem enviar chamados para a área recém criada */
        if (isset($post['areaFrom'])) {
            foreach ($post['areaFrom'] as $key => $value) {
                if ($value == 'yes') {
                    $sql = "INSERT INTO areaxarea_abrechamado 
                            (
                                area, area_abrechamado
                            )
                            VALUES
                            (
                                {$areaId}, {$key}
                            )
                    ";
                    try {
                        $conn->exec($sql);
                    }
                    catch (Exception $e) {
                        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                    }
                }
            }
        }

        /* atualizar as configurações de áreas que podem receber chamados da área recém criada */
        if (isset($post['areaTo'])) {
            foreach ($post['areaTo'] as $key => $value) {
                if ($value == 'yes') {
                    $sql = "INSERT INTO areaxarea_abrechamado 
                            (
                                area, area_abrechamado
                            )
                            VALUES
                            (
                                {$key}, {$areaId}
                            )
                    ";
                    try {
                        $conn->exec($sql);
                    }
                    catch (Exception $e) {
                        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                    }
                }
            }
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


    $sql = "SELECT sis_id FROM sistemas WHERE sistema = '" . $data['area'] . "' AND sis_id <> '" . $data['cod'] . "' ";
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

    $sql = "UPDATE sistemas SET 
                sistema = '" . $data['area'] . "', 
                sis_status = " . $data['status'] . ", 
                sis_email = '" . $data['email'] . "', 
                sis_screen = " . dbField($data['screen_profile']) . ",  
                sis_atende = '" . $data['process_tickets'] . "', 
                sis_wt_profile = '" . $data['wt_profile'] . "' 
            WHERE sis_id = '" . $data['cod'] . "'";


    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');

        $sql = "DELETE FROM permissoes WHERE perm_area = " . $data['cod'] . " ";
        try {
            $conn->exec($sql);

            /* Módulos de acesso */
            foreach ($modules as $mod) {
                $sql = "INSERT INTO permissoes 
                    (
                        perm_area, 
                        perm_modulo
                    )
                    VALUES 
                    (
                        " . $data['cod'] . ", 
                        {$mod}
                    )
                    ";
                try {
                    $conn->exec($sql);
                }
                catch (Exception $e) {
                    $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                }
            }
        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        }

        /* Remove todas as configurações sobre que áreas podem abrir ou receber chamados da área editada */
        $sql = "DELETE FROM 
                    areaxarea_abrechamado 
                WHERE 
                    area = '" . $data['cod'] . "' 
                OR 
                    area_abrechamado = '" . $data['cod'] . "'";
        try {
            $conn->exec($sql);
        
            $duplicateKey = false;

            /* atualizar as configurações de áreas que podem enviar chamados para a área editada */
            if (isset($post['areaFrom']) && $data['process_tickets'] == 1) {
                foreach ($post['areaFrom'] as $key => $value) {
                    if ($value == 'yes') {
                        if ($key == $data['cod']) $duplicateKey = true;
                        $sql = "INSERT INTO areaxarea_abrechamado 
                                (
                                    area, area_abrechamado
                                )
                                VALUES
                                (
                                    " . $data['cod'] . ", {$key}
                                )
                        ";
                        try {
                            $conn->exec($sql);
                        }
                        catch (Exception $e) {
                            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                        }
                    }
                }
            }

            /* atualizar as configurações de áreas que podem receber chamados da área editada */
            if (isset($post['areaTo'])) {
                foreach ($post['areaTo'] as $key => $value) {
                    if ($value == 'yes' && ($key != $data['cod'] || !$duplicateKey)) {
                        $sql = "INSERT INTO areaxarea_abrechamado 
                                (
                                    area, area_abrechamado
                                )
                                VALUES
                                (
                                    {$key}, " . $data['cod'] . "
                                )
                        ";
                        try {
                            $conn->exec($sql);
                        }
                        catch (Exception $e) {
                            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                        }
                    }
                }
            }            
        
        
        
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
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


    /* Confere na tabela de usuários se a área está associada */
    $sql = "SELECT user_id FROM usuarios WHERE AREA = '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
    /* Confere na tabela de ocorrências se a área está associada */
    $sql = "SELECT numero FROM ocorrencias WHERE sistema = '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
    /* Confere na tabela de tipos de problemas se a área está associada */
    $sql = "SELECT prob_id FROM problemas WHERE prob_area = '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }


    /* Sem restrições para excluir a área */
    $sql = "DELETE FROM sistemas WHERE sis_id = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');

        /* Remove as permissões associadas */
        $sql = "DELETE FROM permissoes WHERE perm_area = '" . $data['cod'] . "'";
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        }

        $sql = "DELETE FROM 
                    areaxarea_abrechamado 
                WHERE 
                    area = '" . $data['cod'] . "' 
                OR 
                    area_abrechamado = '" . $data['cod'] . "'";
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