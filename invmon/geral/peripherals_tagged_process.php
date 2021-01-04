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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

$post = $_POST;


$config = getConfig($conn);
$rowLogado = getUserInfo($conn, $_SESSION['s_uid']);

/* Para manter a compatibilidade com versões antigas */
$table = "equipxpieces";
$sqlTest = "SELECT * FROM {$table}";
try {
    $conn->query($sqlTest);
}
catch (Exception $e) {
    $table = "equipXpieces";
}


$exception = "";
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";


$data['type'] = (isset($post['type']) ? noHtml($post['type']) : "");
$data['model_full'] = (isset($post['model_full']) ? noHtml($post['model_full']) : "");
$data['serial_number'] = (isset($post['serial_number']) ? noHtml($post['serial_number']) : "");
$data['part_number'] = (isset($post['part_number']) ? noHtml($post['part_number']) : "");
$data['asset_unit'] = (isset($post['asset_unit']) ? noHtml($post['asset_unit']) : "");
$data['asset_tag'] = (isset($post['asset_tag']) ? noHtml($post['asset_tag']) : "");

$data['department'] = (isset($post['department']) ? noHtml($post['department']) : "");
$data['old_department'] = (isset($post['old_department']) ? noHtml($post['old_department']) : "");


$data['cost_center'] = (isset($post['cost_center']) ? noHtml($post['cost_center']) : "");

$data['purchase_date'] = (isset($post['purchase_date']) ? noHtml($post['purchase_date']) : "");
$data['purchase_date'] = (!empty($data['purchase_date']) ? dateDB($data['purchase_date']) : "");


$data['supplier'] = (isset($post['supplier']) ? noHtml($post['supplier']) : "");
$data['invoice_number'] = (isset($post['invoice_number']) ? noHtml($post['invoice_number']) : "");

$data['price'] = (isset($post['price']) && $post['price'] != "0,00" ? noHtml($post['price']) : "");
$data['price'] = (!empty($data['price']) ? priceDB($data['price']) : $data['price']);

$data['time_of_warranty'] = (isset($post['time_of_warranty']) ? noHtml($post['time_of_warranty']) : "");
$data['condition'] = (isset($post['condition']) ? noHtml($post['condition']) : "");
$data['additional_info'] = (isset($post['additional_info']) ? noHtml($post['additional_info']) : "");
$data['in_equipment'] = (isset($post['in_equipment']) ? ($post['in_equipment'] == "yes" ? 1 : 0) : 0);
$data['equipment_unit'] = (isset($post['equipment_unit']) ? noHtml($post['equipment_unit']) : "");
$data['old_equipment_unit'] = (isset($post['old_equipment_unit']) ? noHtml($post['old_equipment_unit']) : "");
$data['equipment_tag'] = (isset($post['equipment_tag']) ? noHtml($post['equipment_tag']) : "");
$data['old_equipment_tag'] = (isset($post['old_equipment_tag']) ? noHtml($post['old_equipment_tag']) : "");


$equipment_info = [];
$equipment_department = "";
/* Se estiver vinculado a algum equipamento então altero a localizaçao para a localização do equipamento */
if ($data['equipment_tag'] != "" && $data['equipment_unit'] != "") {
    $equipment_info = getEquipmentInfo($conn, $data['equipment_unit'], $data['equipment_tag']);
    $equipment_department = $equipment_info['comp_local'];
    $data['department'] = $equipment_department;
}

$data['record_change'] = ($data['old_equipment_tag'] != $data['equipment_tag'] || $data['old_equipment_unit'] != $data['equipment_unit'] || $data['old_department'] != $data['department'] ? true : false);


/* Checagem de preenchimento dos campos obrigatórios*/
if ($data['action'] == "new" || $data['action'] == "edit") {

    if ($data['type'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "type";
    } elseif ($data['model_full'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "model_full";
    } elseif ($data['department'] == "" && $data['in_equipment'] == 0) {
        $data['success'] = false; 
        $data['field_id'] = "department";
    } elseif ($data['condition'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "condition";
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
        echo json_encode($data);
        return false;
    }


    if ($data['price'] != "" && !filter_var($data['price'], FILTER_VALIDATE_FLOAT)) {
        $data['success'] = false; 
        $data['field_id'] = "price";
        $data['message'] = message('warning', '', TRANS('MSG_ERROR_WRONG_FORMATTED'), '');
        echo json_encode($data);
        return false;
    }

    if ($data['in_equipment'] == 1) {
        if ($data['equipment_unit'] == "") {
            $data['success'] = false; 
            $data['field_id'] = "equipment_unit";
        } elseif ($data['equipment_tag'] == "") {
            $data['success'] = false; 
            $data['field_id'] = "equipment_tag";
        }
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
        echo json_encode($data);
        return false;
    }
}


/* Processamento */
if ($data['action'] == "new") {

    if (!empty($data['asset_tag']) && !empty($data['asset_unit'])) {
        $sql = "SELECT * FROM estoque WHERE estoq_tag_inv = '".$data['asset_tag']."' AND 
					estoq_tag_inst = '".$data['asset_unit']."' ";
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            $data['success'] = false; 
            $data['field_id'] = "asset_tag";
            $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS_WITH_THIS_TAG'), '');
            echo json_encode($data);
            return false;
        }
    }

    if (!empty($data['asset_tag']) && !empty($data['asset_unit'])) {
        $sql = "SELECT * FROM equipamentos WHERE comp_inv = '".$data['asset_tag']."' AND 
					comp_inst = '".$data['asset_unit']."' ";
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            $data['success'] = false; 
            $data['field_id'] = "asset_tag";
            $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS_WITH_THIS_TAG'), '');
            echo json_encode($data);
            return false;
        }
    }
    
    /* Verificação de CSRF */
    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
        echo json_encode($data);
        return false;
    }

	$sql = "INSERT INTO estoque 
        (
            estoq_tipo, estoq_desc, estoq_local, estoq_sn, estoq_tag_inv, estoq_tag_inst, 
            estoq_nf, estoq_warranty, estoq_value, estoq_situac, estoq_data_compra, 
            estoq_ccusto, estoq_vendor, estoq_partnumber, estoq_comentario
        )
		VALUES 
        (
            '" . $data['type'] . "', 
            '" . $data['model_full'] . "', 
            '" . $data['department'] . "', 
            " . dbField($data['serial_number'], 'text') . ", 
            " . dbField($data['asset_tag'], 'text') . ", 
            " . dbField($data['asset_unit'], 'text') . ",  
            " . dbField($data['invoice_number'], 'text') . ",  
            " . dbField($data['time_of_warranty'], 'int') . ",  
            " . dbField($data['price'], 'float') . ",  
            " . dbField($data['condition'], 'int') . ",  
            " . dbField($data['purchase_date'], 'date') . ",  
            " . dbField($data['cost_center'], 'int') . ",  
            " . dbField($data['supplier'], 'int') . ",  
            " . dbField($data['part_number'], 'text') . ",  
            " . dbField($data['additional_info'], 'text') . "  
        )";
		
    try {
        $conn->exec($sql);
        $data['success'] = true; 

        $data['estoque_id'] = $conn->lastInsertId();

        if ($data['in_equipment'] == 1) {

            $sql = "INSERT INTO {$table} (eqp_piece_id, eqp_equip_inv, eqp_equip_inst)
                    VALUES 
                    ( 
                        '" . $data['estoque_id'] . "', 
                        " . dbField($data['equipment_tag'],'text') . ", 
                        " . dbField($data['equipment_unit'],'int') . "
                    )";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
        } 

        $sql = "INSERT INTO hist_pieces 
                (
                    hp_piece_id, hp_piece_local, hp_comp_inv, 
                    hp_comp_inst, hp_uid, hp_date
                )
                VALUES 
                (
                    '" . $data['estoque_id'] . "', 
                    " . dbField($data['department'],'int') . ", 
                    " . dbField($data['equipment_tag'],'text') . ", 
                    " . dbField($data['equipment_unit'],'int') . ", 
                    '" . $_SESSION['s_uid'] . "', 
                    '" . date("Y-m-d H:i:s") . "'
                )";
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }

        $data['message'] = TRANS('MSG_SUCCESS_INSERT') . $exception;
        
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD') . $exception;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'edit') {

    if (!empty($data['asset_tag']) && !empty($data['asset_unit'])) {
        $sql = "SELECT * FROM estoque WHERE estoq_tag_inv = '".$data['asset_tag']."' AND 
					estoq_tag_inst = '".$data['asset_unit']."' AND estoq_cod <> '" . $data['cod'] . "' ";
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            $data['success'] = false; 
            $data['field_id'] = "asset_tag";
            $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS_WITH_THIS_TAG'), '');
            echo json_encode($data);
            return false;
        }
    }

    if (!empty($data['asset_tag']) && !empty($data['asset_unit'])) {
        $sql = "SELECT * FROM equipamentos WHERE comp_inv = '".$data['asset_tag']."' AND 
					comp_inst = '".$data['asset_unit']."' ";
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            $data['success'] = false; 
            $data['field_id'] = "asset_tag";
            $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS_WITH_THIS_TAG'), '');
            echo json_encode($data);
            return false;
        }
    }

    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "UPDATE estoque SET 
                estoq_tipo = '" . $data['type'] . "', 
                estoq_desc = '" . $data['model_full'] . "', 
                estoq_sn = " . dbField($data['serial_number'], 'text') . ", 
                estoq_local = " . dbField($data['department'], 'int') . ", 
                estoq_tag_inst = " . dbField($data['asset_unit'], 'int') . ", 
                estoq_tag_inv = " . dbField($data['asset_tag'], 'text') . ", 
                estoq_partnumber = " . dbField($data['part_number'], 'text') . ", 
                estoq_vendor = " . dbField($data['supplier'], 'int') . ", 
                estoq_nf = " . dbField($data['invoice_number'], 'text') . ", 
                estoq_value = " . dbField($data['price'], 'float') . ", 
                estoq_data_compra = " . dbField($data['purchase_date'], 'date') . ", 
                estoq_warranty = " . dbField($data['time_of_warranty'], 'int') . ", 
                estoq_ccusto = " . dbField($data['cost_center'], 'int') . ", 
                estoq_situac = " . dbField($data['condition'], 'int') . ", 
                estoq_comentario = " . dbField($data['additional_info'], 'text') . " 
                
            WHERE 
                estoq_cod = '" . $data['cod'] . "'";
            
    try {
        $conn->exec($sql);

        $data['success'] = true; 



        if ($data['in_equipment'] == 1) {

            $sqlExists = "SELECT * FROM {$table} WHERE eqp_piece_id = '" . $data['cod'] . "' ";
            $resExists = $conn->query($sqlExists);
            $achou = $resExists->rowCount();
            
            if (!$achou) {
                $sql = "INSERT INTO {$table} (eqp_piece_id, eqp_equip_inv, eqp_equip_inst)
                    VALUES 
                    ( 
                        '" . $data['cod'] . "', 
                        " . dbField($data['equipment_tag'],'text') . ", 
                        " . dbField($data['equipment_unit'],'int') . "
                    )";
            } else {
                $sql = "UPDATE {$table} SET 
                            eqp_equip_inv = '" . $data['equipment_tag'] . "', 
                            eqp_equip_inst = '" . $data['equipment_unit'] . "' 
						WHERE eqp_piece_id = '" . $data['cod'] . "' ";
            }
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
            
        } else {
            /* Se existir, exclui o vinculo com o equipamento */
            $sql = "DELETE FROM {$table} WHERE eqp_piece_id = '" . $data['cod'] . "' ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                    $exception .= "<hr>" . $e->getMessage();
            }
        }

        if ($data['record_change']) {
            $sql = "INSERT INTO hist_pieces 
                (
                    hp_piece_id, hp_piece_local, hp_comp_inv, 
                    hp_comp_inst, hp_uid, hp_date
                )
                VALUES 
                (
                    '" . $data['cod'] . "', 
                    " . dbField($data['department'],'int') . ", 
                    " . dbField($data['equipment_tag'],'text') . ", 
                    " . dbField($data['equipment_unit'],'int') . ", 
                    '" . $_SESSION['s_uid'] . "', 
                    '" . date("Y-m-d H:i:s") . "'
                )";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
        }


        $data['message'] = TRANS('MSG_SUCCESS_EDIT') . $exception;

    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE') . "<br />". $sql . "<br />" . $e->getMessage();
        $_SESSION['flash'] = message('danger', 'Ooops!', $data['message'], '');
        echo json_encode($data);
        return false;
    }
} elseif ($data['action'] == 'delete') {


    /* Confere se há impedimentos para excluir o registro */

    /* Preciso verificar os impedimentos */
    $sql = "SELECT * FROM {$table} WHERE eqp_piece_id = '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }


    /* Por enquanto nenhum registro está sendo excluido - falta realizar todas as consistências antes e remover também os registros de histórico */
    $data['success'] = false; 
    $data['message'] = TRANS('MSG_DATA_REMOVE_NOT_IMPLEMENTED');
    $_SESSION['flash'] = message('danger', '', $data['message'], '');

    echo json_encode($data);
    return false;

    /* Sem restrições para excluir o registro */
    $sql = "DELETE FROM estoque WHERE estoq_cod = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');

        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage() . "<hr>";
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_REMOVE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
}



$_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
echo json_encode($data);
return false;