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
$rowconfmail = getMailConfig($conn);
$rowLogado = getUserInfo($conn, $_SESSION['s_uid']);

$qry_profile_screen = $QRY["useropencall_custom"];
$qry_profile_screen .= " AND  c.conf_cod = '" . $_SESSION['s_screen'] . "'";
$res_screen = $conn->query($qry_profile_screen);
$screen = $res_screen->fetch(PDO::FETCH_ASSOC);


$sqlProfileScreenGlobal = $QRY["useropencall"];
$resScreenGlobal = $conn->query($sqlProfileScreenGlobal);
$screenGlobal = $resScreenGlobal->fetch(PDO::FETCH_ASSOC);


$recordFile = false;
$erro = false;
$exception = "";
$screenNotification = "";
$mailSent = false;
$mailNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['numero'] = (isset($post['numero']) ? intval($post['numero']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";


$data['format_bar'] = $_SESSION['s_formatBarOco'];
$data['_sistema'] = (isset($post['_sistema']) ? $post['_sistema'] : "-1");
$data['sistema'] = (isset($post['sistema']) ? noHtml($post['sistema']) : noHtml($data['_sistema']));
$data['area_destino'] = $screen['conf_opentoarea'];
$data['problema'] = (isset($post['problema']) ? noHtml($post['problema']) : "-1");
$data['radio_prob'] = (isset($post['radio_prob']) ? noHtml($post['radio_prob']) : $data['problema']);

$data['descricao'] = (isset($post['descricao']) ? $post['descricao'] : "");
$data['descricao'] = ($data['format_bar'] ? $data['descricao'] : noHtml($data['descricao']));

$data['unidade'] = (isset($post['instituicao']) ? noHtml($post['instituicao']) : "-1");
$data['etiqueta'] = (isset($post['equipamento']) ? noHtml($post['equipamento']) : "");
$data['department'] = (isset($post['local']) && !empty($post['local']) ? noHtml($post['local']) : "-1");

$data['aberto_por'] = (isset($_SESSION['s_uid']) ? intval($_SESSION['s_uid']) : "");

$data['logado'] = (isset($_SESSION['s_uid']) ? intval($_SESSION['s_uid']) : "");

$data['forward'] = (isset($post['foward']) ? noHtml($post['foward']) : "-1");
$data['operator'] = ($data['forward'] != "-1" ? $data['forward'] : $data['aberto_por']);
// $data['replicar'] = (isset($post['replicar']) ? intval($post['replicar']) : 0);
$data['contato'] = (isset($post['contato']) ? noHtml($post['contato']) : "");
$data['contato_email'] = (isset($post['contato_email']) ? noHtml($post['contato_email']) : "");
$data['telefone'] = (isset($post['telefone']) ? noHtml($post['telefone']) : "");
$data['prioridade'] = (isset($post['prioridade']) ? intval($post['prioridade']) : "-1");
$data['father'] = ((isset($post['pai']) ? intval($post['pai']) : ""));
$data['date_schedule_typed'] = (isset($post['date_schedule']) ? noHtml($post['date_schedule']) : "");
$data['date_schedule_part'] = (isset($post['date_schedule']) ? noHtml($post['date_schedule']) : "");
$data['time_schedule_part'] = (isset($post['time_schedule']) && !empty($post['time_schedule']) ? $post['time_schedule'] : date("H:i"));
$data['time_schedule_part'] = ($data['date_schedule_part'] == "" ? "" : $data['time_schedule_part']);
$data['schedule_to'] = "";
$data['is_scheduled'] = 0;
if ($data['date_schedule_part'] != "") {
    $data['schedule_to'] = dateDB($data['date_schedule_part'] . " " . $data['time_schedule_part']);
    $data['is_scheduled'] = 1;
}
$data['mail_area'] = (isset($post['mailAR']) ? $post['mailAR'] : "");
$data['mail_operador'] = (isset($post['mailOP']) ? $post['mailOP'] : "");
$data['mail_usuario'] = (isset($post['mailUS']) ? $post['mailUS'] : "");

$data['sla_out'] = (isset($post['sla_out']) ? $post['sla_out'] : 0); /* action = close */
$data['justificativa'] = (isset($post['justificativa']) ? $post['justificativa'] : "");
$data['justificativa'] = ($data['format_bar'] ? $data['justificativa'] : noHtml($data['justificativa']));
$data['script_solution'] = (isset($post['script_sol']) ? noHtml($post['script_sol']) : "");
$data['technical_description'] = (isset($post['descProblema']) ? $post['descProblema'] : "");
$data['technical_description'] = ($data['format_bar'] ? $data['technical_description'] : noHtml($data['technical_description']));


$data['technical_solution'] = (isset($post['descSolucao']) ? $post['descSolucao'] : "");
$data['technical_solution'] = ($data['format_bar'] ? $data['technical_solution'] : noHtml($data['technical_solution']));

$data['global_uri'] = "";

$data['entry_privated'] = (isset($post['check_asset_privated']) ? noHtml($post['check_asset_privated']) : 0);
$data['data_atendimento'] = (isset($post['data_atend']) ? noHtml($post['data_atend']) : "");
// $data['old_status'] = (isset($post['oldStatus']) ? noHtml($post['oldStatus']) : noHtml($post['status']));

$data['entry'] = (isset($post['assentamento']) ? $post['assentamento'] : "");
$data['entry'] = ($data['format_bar'] ? $data['entry'] : noHtml($data['entry']));

$data['first_response'] = (isset($post['resposta']) ? noHtml($post['resposta']) : "");
$data['total_files_to_deal'] = (isset($post['cont']) ? noHtml($post['cont']) : 0);
$data['total_relatives_to_deal'] = (isset($post['contSub']) ? noHtml($post['contSub']) : 0);
$data['total_entries_to_deal'] = (isset($post['total_asset']) ? noHtml($post['total_asset']) : 0);


/* Informações sobre a área destino */
$rowAreaTo = getAreaInfo($conn, $data['sistema']);

/* Para pegar o estado da ocorrência antes da atualização e permitir a gravação do log de modificações */
$arrayBeforePost = "";
if (!empty($data['numero'])) {
    $qryfull = $QRY["ocorrencias_full_ini"]." WHERE o.numero = '" . $data['numero'] . "' ";
    $execfull = $conn->query($qryfull);
    $arrayBeforePost = $execfull->fetch();
}

/* Tratando de acordo com os actions */
if ($data['action'] == "open") {
    $data['status'] = 1; /* Aguardando atendimento */
    if ($data['forward'] != "-1") {
        $data['status'] = $config['conf_foward_when_open'];
    }

    if ($data['is_scheduled']) {
        $data['status'] =  $config['conf_schedule_status'];
    }

    $data['aberto_por'] = (isset($_SESSION['s_uid']) ? intval($_SESSION['s_uid']) : "");

} elseif ($data['action'] == "edit") {
    
    $data['operator'] = $post['operador'];

    $data['status'] = (isset($post['status']) ? noHtml($post['status']) : "-1");
    $data['old_status'] = (isset($post['oldStatus']) ? noHtml($post['oldStatus']) : $data['status']);

    /* Se o chamado estiver encerrado não permito que o status seja alterado */
    $data['status'] = ($data['old_status'] == 4 ? 4 : $data['status']);


    $data['aberto_por'] = $arrayBeforePost['aberto_por_cod'];


    $qryGlobalUri = "SELECT * FROM global_tickets WHERE gt_ticket = '" . $data['numero'] . "' ";
    $resGlobalUri = $conn->query($qryGlobalUri);
    $rowGlobalUri = $resGlobalUri->fetch();
    $data['global_uri'] = (!empty($rowGlobalUri['gt_id']) ? $rowGlobalUri['gt_id'] : "");

} elseif ($data['action'] == "close") {
    $data['status'] = 4; /* Encerrado */

    $data['aberto_por'] = $arrayBeforePost['aberto_por_cod'];

    $qryGlobalUri = "SELECT * FROM global_tickets WHERE gt_ticket = '" . $data['numero'] . "' ";
    $resGlobalUri = $conn->query($qryGlobalUri);
    $rowGlobalUri = $resGlobalUri->fetch();
    $data['global_uri'] = (!empty($rowGlobalUri['gt_id']) ? $rowGlobalUri['gt_id'] : "");
}



/* Checagem de preenchimento dos campos obrigatórios*/
if ($data['action'] == "open") {

    if ($screen['conf_scr_area'] == '1' && $data['sistema'] == "-1") {
        $data['success'] = false; 
        $data['field_id'] = "idArea";
    } elseif ($screen['conf_scr_prob'] == '1' && $data['problema'] == "-1") {
        $data['success'] = false; 
        $data['field_id'] = "idProblema";
    } elseif ($screen['conf_scr_desc'] == '1' && $data['descricao'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "idDescricao";
    } /* elseif ($screen['conf_scr_unit'] && $data['unidade'] == "-1") {
        $data['success'] = false; 
        $data['field_id'] = "idUnidade";
    } elseif ($screen['conf_scr_tag'] && $data['etiqueta'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "idEtiqueta";
    } */ elseif ($screen['conf_scr_contact'] == '1' && $data['contato'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "contato";
    } elseif ($screen['conf_scr_contact_email'] == '1' && $data['contato_email'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "contato_email";
    } elseif ($screen['conf_scr_fone'] == '1' && $data['telefone'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "idTelefone";
    } elseif ($screen['conf_scr_local'] == '1' && $data['department'] == "-1") {
        $data['success'] = false; 
        $data['field_id'] = "idLocal";
    }


    if ($data['success'] == false) {
        $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
        echo json_encode($data);
        return false;
    }


    if ($data['contato_email'] != "" && !filter_var($data['contato_email'], FILTER_VALIDATE_EMAIL)) {
        $data['success'] = false; 
        $data['field_id'] = "contato_email";
        $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
        echo json_encode($data);
        return false;
    }


    if ($data['is_scheduled']) {
        if (!valida(TRANS('DATE_TO_SCHEDULE'), $data['date_schedule_typed'], 'DATA', 1, $ERR)) {
            $data['success'] = false; 
            $data['field_id'] = "idDate_schedule";
            $data['message'] = message('warning', '', $ERR, '');
            echo json_encode($data);
            return false;
        }
    
        $today = new DateTime();
        $schedule_to = new DateTime($data['schedule_to']);
        if ($today > $schedule_to) {
            $data['success'] = false; 
            $data['field_id'] = "idDate_schedule";
            $data['message'] = message('warning', '', TRANS('DATE_NEEDS_TO_BE_IN_FUTURE'), '');
            echo json_encode($data);
            return false;
        }
    }
}




/* Validações na edição e encerramento */
if ($data['action'] == "edit" || $data['action'] == "close") {

    if ($data['sistema'] == "-1") {
        $data['success'] = false; 
        $data['field_id'] = "idArea";
    } elseif ($data['problema'] == "-1") {
        $data['success'] = false; 
        $data['field_id'] = "idProblema";
    } /* elseif ($data['unidade'] == "-1") {
        $data['success'] = false; 
        $data['field_id'] = "idUnidade";
    } elseif ($data['etiqueta'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "idEtiqueta";
    } */ elseif ($data['contato'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "contato";
    } elseif ($data['contato_email'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "contato_email";
    } elseif ($data['telefone'] == "") {
        $data['success'] = false; 
        $data['field_id'] = "idTelefone";
    } elseif ($data['department'] == "-1") {
        $data['success'] = false; 
        $data['field_id'] = "idLocal";
    } elseif ($data['entry'] == "" && $data['action'] == "edit") {
        $data['success'] = false; 
        $data['field_id'] = "idAssentamento";
    } elseif ($data['technical_description'] == "" && $data['action'] == "close") {
        $data['success'] = false; 
        $data['field_id'] = "idDescProblema";
    } elseif ($data['technical_solution'] == "" && $data['action'] == "close") {
        $data['success'] = false; 
        $data['field_id'] = "idDescSolucao";
    } elseif ($data['justificativa'] == "" && $data['sla_out'] == 1 && $config['conf_desc_sla_out'] && $data['action'] == "close") {
        $data['success'] = false; 
        $data['field_id'] = "idJustificativa";
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
        echo json_encode($data);
        return false;
    }

    if ($data['contato_email'] != "" && !filter_var($data['contato_email'], FILTER_VALIDATE_EMAIL)) {
        $data['success'] = false; 
        $data['field_id'] = "contato_email";
        $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
        echo json_encode($data);
        return false;
    }
}




/* Checagens para upload de arquivos - vale para todos os actions */
$totalFiles = ($_FILES && !empty($_FILES['anexo']['name'][0]) ? count($_FILES['anexo']['name']) : 0);
if ($totalFiles > $config['conf_qtd_max_anexos']) {

    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', 'Too many files','');
    echo json_encode($data);
    return false;
}

$uploadMessage = "";
/* Testa os arquivos enviados para montar os índices do recordFile*/
if ($totalFiles) {
    foreach ($_FILES as $anexo) {
        $file = array();
        for ($i = 0; $i < $totalFiles; $i++) {
            /* fazer o que precisar com cada arquivo */
            /* acessa:  $anexo['name'][$i] $anexo['type'][$i] $anexo['tmp_name'][$i] $anexo['size'][$i]*/
            $file['name'] =  $anexo['name'][$i];
            $file['type'] =  $anexo['type'][$i];
            $file['tmp_name'] =  $anexo['tmp_name'][$i];
            $file['error'] =  $anexo['error'][$i];
            $file['size'] =  $anexo['size'][$i];

            $upld = upload('anexo', $config, $config['conf_upld_file_types'], $file);
            if ($upld == "OK") {
                $recordFile[$i] = true;
            } else {
                $recordFile[$i] = false;
                $uploadMessage .= $upld;

                // $data['success'] = false; 
                // $data['field_id'] = "idInputFile";
                // $data['message'] = message('warning', 'Ooops!', $uploadMessage, '');
                // echo json_encode($data);
                // return false;                
            }
        }
    }
    if (strlen($uploadMessage) > 0) {
        $data['success'] = false; 
        $data['field_id'] = "idInputFile";
        $data['message'] = message('warning', 'Ooops!', $uploadMessage, '');
        echo json_encode($data);
        return false;                
    }
}


/* Processamento */
if ($data['action'] == "open") {

    /* Verificação de CSRF */
    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
        echo json_encode($data);
        return false;
    }
    
	$sql = "INSERT INTO ocorrencias 
        (
            problema, descricao, instituicao, equipamento, 
            sistema, contato, contato_email, telefone, local, 
            operador, data_abertura, data_fechamento, status, data_atendimento, 
            aberto_por, oco_scheduled, oco_scheduled_to, 
            oco_real_open_date, date_first_queued, oco_prior 
        )
		VALUES 
        (
            '" . $data['radio_prob'] . "', '" . $data['descricao'] . "', '" . $data['unidade'] . "', '" . $data['etiqueta'] . "',
            '" . $data['sistema'] . "', '" . $data['contato'] . "', '" . $data['contato_email'] . "', '" . $data['telefone'] . "', '" . $data['department'] . "',
            '" . $data['operator'] . "', '" . date("Y-m-d H:i:s"). "', null, '" . $data['status'] . "', null,
            '" . $data['aberto_por'] . "', '" . $data['is_scheduled'] . "', " . dbField($data['schedule_to'],'date') . ", 
            '" . date("Y-m-d H:i:s"). "', null, '" . $data['prioridade'] . "'
        )";
		
    try {
        $conn->exec($sql);
        $data['numero'] = $conn->lastInsertId();
        $data['global_uri'] = random64();

        /* Gravação da data na tabela tickets_stages */
        $timeStage = insert_ticket_stage($conn, $data['numero'], 'start', $data['status']);
        
        /* Grava a uri global */
        $qryGlobalUri = "INSERT INTO global_tickets (gt_ticket, gt_id) values (" . $data['numero'] . ", '" . $data['global_uri'] . "')";
        $conn->exec($qryGlobalUri);

        /* Primeiro registro do log de modificações da ocorrência */
        $firstLog = firstLog($conn, $data['numero'], 0); 

        if (!empty($data['father'])) {
            $sqlDep = "INSERT INTO ocodeps (dep_pai, dep_filho) values (" . $data['father'] . ", " . $data['numero'] . ")";
            $conn->exec($sqlDep);
        }

        $data['success'] = true; 
        // $data['message'] = TRANS('MSG_OPEN_CALL_OK') . ": " . $data['numero'];



        if (!empty($uploadMessage)) {
            $data['message'] = $data['message'] . "<br />" . $uploadMessage;
        }
        
        
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD') . "<br/>" . $sql;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
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

    /* Insere o primeiro registro de log caso não exista - chamados anteriores a versao 3.0 */
    $firstLog = firstLog($conn, $data['numero']);

    $terms = "";
    $newStatus = false;
    if ($data['status'] != $data['old_status'] && $data['old_status'] != 4) {
        /* Status alterado - relevante em função do registro de mudança na tabela tickets_stages e para tirar de agendamento*/
        $newStatus = true;
        $terms .= " oco_scheduled = 0, ";
    }
    
    if (!empty($data['first_response'])) {
        $terms .= " data_atendimento = '" . date("Y-m-d H:i:s") . "', ";
    }

    $sql = "UPDATE ocorrencias SET 
    
                operador = " . dbField($data['operator']) . ", 
                problema = '" . $data['radio_prob'] . "', 
                instituicao = " . dbField($data['unidade']) . ", 
                equipamento = " . dbField($data['etiqueta'],'text') . ", 
                sistema = '" . $data['sistema'] . "', 
                local = '" . $data['department'] . "', 
                status = '" . $data['status'] . "', 
                {$terms} 
                contato = '" . noHtml($data['contato']) . "', 
                contato_email = '" . noHtml($data['contato_email']) . "', 
                telefone = '" . noHtml($data['telefone']) . "', 
                oco_prior = '" . $data['prioridade'] . "' 
            WHERE 
                numero = '" . $data['numero'] . "'";
            
    try {
        $conn->exec($sql);

        if ($newStatus) {
            /* Gravação da data na tabela tickets_stages */
            $stopTimeStage = insert_ticket_stage($conn, $data['numero'], 'stop', $data['status']);
            $startTimeStage = insert_ticket_stage($conn, $data['numero'], 'start', $data['status']);
        }



        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');

        
        /* Array para a função recordLog */
        $afterPost = [];
        $afterPost['prioridade'] = $data['prioridade'];
        $afterPost['area'] = $data['sistema'];
        $afterPost['problema'] = $data['radio_prob'];
        $afterPost['unidade'] = $data['unidade'];
        $afterPost['etiqueta'] = $data['etiqueta'];
        $afterPost['contato'] = $data['contato'];
        $afterPost['contato_email'] = $data['contato_email'];
        $afterPost['telefone'] = $data['telefone'];
        $afterPost['departamento'] = $data['department'];
        $afterPost['operador'] = $data['operator'];
        $afterPost['status'] = $data['status'];
        
        /* Função que grava o registro de alterações do chamado */
        $recordLog = recordLog($conn, $data['numero'], $arrayBeforePost, $afterPost, 1);
        
        
        
        // $_SESSION['flash'] = message('success', '', $data['message'], '');
        // echo json_encode($data);
        // return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE') . "<br />". $sql . "<br />" . $e->getMessage();
        $_SESSION['flash'] = message('danger', 'Ooops!', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'close') {

    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    /* Insere o primeiro registro de log caso não exista - chamados anteriores a versao 3.0 */
    $firstLog = firstLog($conn, $data['numero']);

    $terms = "";
    if (empty($data['data_atendimento'])) {
        $terms .= " data_atendimento = '" . date("Y-m-d H:i:s") . "', ";
    }

    $sql = "UPDATE ocorrencias SET 
    
                operador = " . dbField($data['operator']) . ", 
                problema = '" . $data['radio_prob'] . "', 
                instituicao = " . dbField($data['unidade']) . ", 
                equipamento = " . dbField($data['etiqueta'],'text') . ", 
                sistema = '" . $data['sistema'] . "', 
                local = '" . $data['department'] . "', 
                data_fechamento = '" . date('Y-m-d H:i:s') . "', 
                status = 4, 
                oco_scheduled = 0, 
                {$terms} 
                contato = '" . noHtml($data['contato']) . "', 
                contato_email = '" . noHtml($data['contato_email']) . "', 
                telefone = '" . noHtml($data['telefone']) . "', 
                oco_prior = '" . $data['prioridade'] . "', 
                oco_script_sol = " . dbField($data['script_solution']) . " 
            WHERE 
                numero = '" . $data['numero'] . "'";

    try {
        $conn->exec($sql);

        /* Gravação da data na tabela tickets_stages */
        /* A primeira entrada serve apenas para gravar a conclusão do status anterior ao encerramento */
        $stopTimeStage = insert_ticket_stage($conn, $data['numero'], 'stop', 4);
        /* As duas próximas entradas servem para lançar o status de encerramento - o tempo nao será contabilizado */
        $stopTimeStage = insert_ticket_stage($conn, $data['numero'], 'start', 4);
        $stopTimeStage = insert_ticket_stage($conn, $data['numero'], 'stop', 4);



        $data['success'] = true; 
        $data['message'] = TRANS('MSG_OCCO_FINISH_SUCESS');

        
        /* Array para a função recordLog */
        $afterPost = [];
        $afterPost['prioridade'] = $data['prioridade'];
        $afterPost['area'] = $data['sistema'];
        $afterPost['problema'] = $data['radio_prob'];
        $afterPost['unidade'] = $data['unidade'];
        $afterPost['etiqueta'] = $data['etiqueta'];
        $afterPost['contato'] = $data['contato'];
        $afterPost['contato_email'] = $data['contato_email'];
        $afterPost['telefone'] = $data['telefone'];
        $afterPost['departamento'] = $data['department'];
        $afterPost['operador'] = $data['operator'];
        $afterPost['status'] = $data['status'];
        
        /* Função que grava o registro de alterações do chamado */
        $recordLog = recordLog($conn, $data['numero'], $arrayBeforePost, $afterPost, 4);
        


        
        
        // $_SESSION['flash'] = message('success', '', $data['message'], '');
        // echo json_encode($data);
        // return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE') . "<br />". $sql;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

}




if (!empty($data['entry']) || !empty($data['technical_description'])) {

    /* Trata a visibilidade dos assentamentos */
    $queryCleanAssets = "UPDATE assentamentos SET asset_privated = 0 WHERE ocorrencia = " . $data['numero'] . "";
    try {
        $conn->exec($queryCleanAssets);
    } catch (Exception $e) {
        // echo 'Erro: ', $e->getMessage(), "<br/>";
        // $erro = true;
    }
    for ($i = 1; $i <= $post['total_asset']; $i++) {
        if (isset($post['asset' . $i])) {
            $queryUpdateAsset = "UPDATE assentamentos SET asset_privated = 1 WHERE numero = " . $post['asset' . $i] . "";

            try {
                $conn->exec($queryUpdateAsset);
            } catch (Exception $e) {
                // echo 'Erro: ', $e->getMessage(), "<br/>";
                // $erro = true;
            }
        }
    }

    /* action edit */
    if (!empty($data['entry'])) {
        /* Adiciona o assentamento */
        $sqlEntry = "INSERT INTO assentamentos 
        (
            ocorrencia, assentamento, data, responsavel, asset_privated, tipo_assentamento
        )
        VALUES 
        (
            " . $data['numero'] . ", 
            '" . $data['entry'] . "',
            '" . date('Y-m-d H:i:s') . "', 
            '" . $data['logado'] . "', 
            " . $data['entry_privated'] . ",
            1
        )";

        try {
            $conn->exec($sqlEntry);
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }
    }

    /* action close */
    if (!empty($data['technical_description'])) {
        /* Adiciona a descrição técnica como assentamento */
        $sqlEntry = "INSERT INTO assentamentos 
        (
            ocorrencia, assentamento, data, responsavel, asset_privated, tipo_assentamento
        )
        VALUES 
        (
            " . $data['numero'] . ", 
            '" . $data['technical_description'] . "',
            '" . date('Y-m-d H:i:s') . "', 
            '" . $data['logado'] . "', 
            0, 
            4
        )";

        try {
            $conn->exec($sqlEntry);
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }

        $sqlEntry = "INSERT INTO assentamentos 
        (
            ocorrencia, assentamento, data, responsavel, asset_privated, tipo_assentamento
        )
        VALUES 
        (
            " . $data['numero'] . ", 
            '" . $data['technical_solution'] . "',
            '" . date('Y-m-d H:i:s') . "', 
            '" . $data['logado'] . "', 
            0, 
            5
        )";

        try {
            $conn->exec($sqlEntry);
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }


        $sqlSolution = "INSERT INTO solucoes 
        (
            numero, problema, solucao, data, responsavel
        ) 
        VALUES 
        (
            " . $data['numero'] . ", '" . $data['technical_description'] . "', 
            '" . $data['technical_solution'] . "', '" . date('Y-m-d H:i:s'). "',
            '" . $data['logado'] . "'
        )";

        try {
            $conn->exec($sqlSolution);
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }
    }

    if (!empty($data['justificativa'])) {

        
        $sqlJustify = "INSERT INTO assentamentos 
        (
            ocorrencia, assentamento, data, responsavel, asset_privated, tipo_assentamento
        )
        VALUES 
        (
            " . $data['numero'] . ", 
            '" . $data['justificativa'] . "',
            '" . date('Y-m-d H:i:s') . "', 
            '" . $data['logado'] . "', 
            0, 
            3
        )";

        try {
            $conn->exec($sqlJustify);
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }
    }
}



/* Upload de arquivos - Todos os actions */
if ($totalFiles) {
    foreach ($_FILES as $anexo) {
        $file = array();
        for ($i = 0; $i < $totalFiles; $i++) {
            /* fazer o que precisar com cada arquivo */
            /* acessa:  $anexo['name'][$i] $anexo['type'][$i] $anexo['tmp_name'][$i] $anexo['size'][$i]*/

            /* Apenas os arquivos já validados */
            if ($recordFile && $recordFile[$i]) {
                //INSERSAO DO ARQUIVO NO BANCO
                $fileinput = $anexo['tmp_name'][$i];
                // $tamanho = getimagesize($fileinput);
                $tamanho = getimagesize($fileinput);
                $tamanho2 = filesize($fileinput);

                if (!$tamanho) {
                    /* Nâo é imagem */
                    unset ($tamanho);
                    $tamanho = [];
                    $tamanho[0] = "";
                    $tamanho[1] = "";
                }

                if (chop($fileinput) != "") {
                    // $fileinput should point to a temp file on the server
                    // which contains the uploaded file. so we will prepare
                    // the file for upload with addslashes and form an sql
                    // statement to do the load into the database.
                    $file = addslashes(fread(fopen($fileinput, "r"), 1000000));
                    $sqlFile = "INSERT INTO imagens (img_nome, img_oco, img_tipo, img_bin, img_largura, img_altura, img_size) values " .
                        "('" . noSpace($anexo['name'][$i]) . "'," . $data['numero'] . ", '" . $anexo['type'][$i] . "', " .
                        "'" . $file . "', " . dbField($tamanho[0]) . ", " . dbField($tamanho[1]) . ", " . dbField($tamanho2) . ")";
                    // now we can delete the temp file
                    unlink($fileinput);
                }
                try {
                    $exec = $conn->exec($sqlFile);
                }
                catch (Exception $e) {
                    $data['message'] = $data['message'] . "<hr>" . TRANS('MSG_ERR_NOT_ATTACH_FILE');
                    $exception .= "<hr>" . $e->getMessage();
                }
            }
        }
    }
}



//Exclui os anexos marcados - Action edit || close
if ( $data['total_files_to_deal'] > 0 ) {
    for ($j = 1; $j <= $data['total_files_to_deal']; $j++) {
        if (isset($post['delImg'][$j])) {
            $qryDel = "DELETE FROM imagens WHERE img_cod = " . $post['delImg'][$j] . "";

            try {
                $conn->exec($qryDel);
            } catch (Exception $e) {
                // echo 'Erro: ', $e->getMessage(), "<br/>";
                // $erro = true;
                $exception .= "<hr>" . $e->getMessage();
            }
        }
    }
}



$isPai = 0;

if ( $data['total_relatives_to_deal'] > 0 ) {
    /* Checa se um dos vínculos é chamado pai */
    for ($j = 1; $j <= $data['total_relatives_to_deal']; $j++) {

        if (!empty($post['delSub'][$j])) {
            $sql = "SELECT * FROM ocodeps WHERE dep_pai = " . $post['delSub'][$j] . " AND dep_filho = " . $data['numero'] . " ";
            try {
                $result = $conn->query($sql);
            } catch (Exception $e) {
                // echo 'Erro: ', $e->getMessage(), "<br/>", $sql;
                // exit();
                $exception .= "<hr>" . $e->getMessage();
            }
            $isPai = $result->rowCount();
        }
    }

    /* Remove chamado pai */
    if ($isPai) {
        $rowPai = $result->fetch();
        $qryDel = "DELETE FROM ocodeps WHERE dep_filho = " . $data['numero'] . " and dep_pai = " . $rowPai['dep_pai'] . "";
        try {
            $conn->exec($qryDel);
        }
        catch (Exception $e) {
            // echo 'Erro: ', $e->getMessage(), "<br/>";
            // $erro = true;
            $exception .= "<hr>" . $e->getMessage();
        }
    }

    // Remove subchamados
    for ($j = 1; $j <= $data['total_relatives_to_deal']; $j++) {
        if (isset($post['delSub'][$j])) {

            $qryDel = "DELETE FROM ocodeps WHERE dep_pai = " . $data['numero'] . " and dep_filho = " . $post['delSub'][$j] . "";
            try {
                $conn->exec($qryDel);
            } catch (Exception $e) {
                // echo 'Erro: ', $e->getMessage(), "<br/>";
                // $erro = true;
                $exception .= "<hr>" . $e->getMessage();
            }
        }
    }
}



/* Variáveis de ambiente para envio de e-mail: todos os actions */
$VARS = getEnvVarsValues($conn, $data['numero']);


/* envio de e-mails */
if ($data['action'] == "open") {

    if (!empty($data['mail_area'])) {
        $event = "abertura-para-area";
        $eventTemplate = getEventMailConfig($conn, $event);

        $mailSent = send_mail($event, $rowAreaTo['email'], $rowconfmail, $eventTemplate, $VARS);

        if (!$mailSent) {
            $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
        }
    }

    

    if (!empty($data['abertura-para-operador'])) {
        $event = "abertura-para-area";
        $eventTemplate = getEventMailConfig($conn, $event);

        if ($data['forward'] != "-1") {
            $rowMailOper = getUserInfo($conn, $data['forward']);
        } else {
            $rowMailOper = $rowLogado;
        }

        $VARS['%operador%'] = $rowMailOper['nome'];
        $mailSent = send_mail($event, $rowMailOper['email'], $rowconfmail, $eventTemplate, $VARS);

        if (!$mailSent) {
            $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
        }
    }


    // if ($rowLogado['area_atende'] == 0) {
    //     /* Usuário somente-abertura - recebe o email sobre o chamado aberto */
    //     $event = 'abertura-para-usuario';
	// 	$eventTemplate = getEventMailConfig($conn, $event);
        
    //     $mailSent = send_mail($event, $rowLogado['email'], $rowconfmail, $eventTemplate, $VARS);

    //     if (!$mailSent) {
    //         $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
    //     }
    // }

    if (!empty($data['mail_usuario']) || $rowLogado['area_atende'] == 0) {
        
        $event = 'abertura-para-usuario';
        $eventTemplate = getEventMailConfig($conn, $event);

        $rowMailUser = getUserInfo($conn, $data['aberto_por']);
        
        $recipient = "";
        if (!empty($data['contato_email'])) {
            $recipient = $data['contato_email'];
        } else {
            $recipient = $rowMailUser['email'];
        }
        
        $mailSent = send_mail($event, $recipient, $rowconfmail, $eventTemplate, $VARS);

        if (!$mailSent) {
            $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
        }
    }







    if (!empty($screen['conf_scr_msg'])) {
        $mensagem = str_replace("%numero%", $data['numero'], $screen['conf_scr_msg']);
    } else
        $mensagem = str_replace("%numero%", $data['numero'], $screenGlobal['conf_scr_msg']);

    $data['message'] = $mensagem;
    // $data['message'] = $data['message'] . "<br />" . $mensagem;
}

/* envio de e-mails */
if ($data['action'] == "edit") {

    if (!empty($data['mail_area'])) {
        $event = "edita-para-area";
        $eventTemplate = getEventMailConfig($conn, $event);

        $mailSent = send_mail($event, $rowAreaTo['email'], $rowconfmail, $eventTemplate, $VARS);

        if (!$mailSent) {
            $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
        }
    }

    if (!empty($data['mail_operador'])) {
        $event = "edita-para-operador";
        $eventTemplate = getEventMailConfig($conn, $event);

        $sqlMailOper = "SELECT nome, email FROM usuarios WHERE user_id ='" . $data['operator'] . "'";
        $execMailOper = $conn->query($sqlMailOper);
        $rowMailOper = $execMailOper->fetch();

        $VARS['%operador%'] = $rowMailOper['nome'];
        $mailSent = send_mail($event, $rowMailOper['email'], $rowconfmail, $eventTemplate, $VARS);

        if (!$mailSent) {
            $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
        }
    }

    if (!empty($data['mail_usuario'])) {
        
        $event = 'edita-para-usuario';
        $eventTemplate = getEventMailConfig($conn, $event);

        $rowMailUser = getUserInfo($conn, $data['aberto_por']);
        
        $recipient = "";
        if (!empty($data['contato_email'])) {
            $recipient = $data['contato_email'];
        } else {
            $recipient = $rowMailUser['email'];
        }
        
        $mailSent = send_mail($event, $recipient, $rowconfmail, $eventTemplate, $VARS);

        if (!$mailSent) {
            $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
        }
    }
    
}

/* envio de e-mails */
if ($data['action'] == "close") {

    if (!empty($data['mail_area'])) {
        $event = "encerra-para-area";
        $eventTemplate = getEventMailConfig($conn, $event);

        $mailSent = send_mail($event, $rowAreaTo['email'], $rowconfmail, $eventTemplate, $VARS);

        if (!$mailSent) {
            $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
        }
    }

    if (!empty($data['mail_usuario'])) {
        
        $event = 'encerra-para-usuario';
		$eventTemplate = getEventMailConfig($conn, $event);

        $rowMailUser = getUserInfo($conn, $data['aberto_por']);

        $recipient = "";
        if (!empty($data['contato_email'])) {
            $recipient = $data['contato_email'];
        } else {
            $recipient = $rowMailUser['email'];
        }
        
        $mailSent = send_mail($event, $recipient, $rowconfmail, $eventTemplate, $VARS);

        if (!$mailSent) {
            $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
        }
    }
    
}


$_SESSION['flash'] = message('success', '', $data['message'] . $exception . $mailNotification, '');
echo json_encode($data);
return false;
