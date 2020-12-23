<?php session_start();
 /* Copyright 2020 Flávio Ribeiro

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
include_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

use includes\classes\ConnectPDO;
$conn = ConnectPDO::getInstance();


$imgsPath = "../../includes/imgs/";
$iconFrozen = "<span class='text-oc-teal' title='" . TRANS('HNT_TIMER_STOPPED') . "'><i class='fas fa-pause fa-lg'></i></span>";
$iconOutOfWorktime = "<span class='text-oc-teal' title='" . TRANS('HNT_TIMER_OUT_OF_WORKTIME') . "'><i class='fas fa-pause fa-lg'></i></i></span>";
$iconTicketClosed = "<span class='text-oc-teal' title='" . TRANS('HNT_TICKET_CLOSED') . "'><i class='fas fa-check fa-lg'></i></i></span>";
$config = getConfig($conn);
$percLimit = $config['conf_sla_tolerance']; 

$post = $_POST;
$terms = "";
$criteria = array();
$criterText = "";
$badgeClass = "badge badge-success p-2 mb-1";
$badgeClassEmptySearch = "badge badge-danger p-2 mb-1";

$slaIndicatorLabel = [];
$slaIndicatorLabel[1] = TRANS('SMART_NOT_IDENTIFIED');
$slaIndicatorLabel[2] = TRANS('SMART_IN_SLA');
$slaIndicatorLabel[3] = TRANS('SMART_IN_SLA_TOLERANCE');
$slaIndicatorLabel[4] = TRANS('SMART_OUT_SLA');

// dump($post);
if (isset($post['simpleSearch']) && $post['simpleSearch'] == 1 && empty($post['ticket'])) {
    $_SESSION['flash'] = message('warning', '', TRANS('MSG_FILL_AT_LEAST_ONE_TICKET_NUMBER'), '');
    print "<script>redirect('simple_search_to_report.php');</script>";
    exit;
}

/* Para os casos da consulta simples por número do chamado */
if (isset($post['ticket']) && !empty($post['ticket'])) {
    
    $maxNumberOfTickets = 30; /* número máximo de ocorrências para a consulta */
    $tmp = explode(',', $post['ticket']);
    
    $treatValues = array_map('intval', $tmp);
    $ticketIN = "";
    $i = 0;
    foreach ($treatValues as $ticketNumber) {
        if ($i < $maxNumberOfTickets) { /* Limitando a quantidade de chamados da consulta */
            if (strlen($ticketIN)) $ticketIN .= ", ";
            $ticketIN .= $ticketNumber;
        }
        $i++;
    }
    $terms .= " AND o.numero IN ({$ticketIN}) ";
    
    $criterText = TRANS('TICKET_NUMBER') . ": {$ticketIN}<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}




if (isset($post['current_month']) && !empty($post['current_month'])) {
    $date_no_time = date('01/m/Y');
    $data_abertura_from = date('Y-m-01') . " 00:00:00";
    $terms .= " AND o.oco_real_open_date >= '" . $data_abertura_from . "' ";
    $criterText = TRANS('SMART_MIN_DATE_OPENING') . ": " . $date_no_time . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['data_abertura_from']) && !empty($post['data_abertura_from'])) {
    $data_abertura_from = "";

    $data_abertura_from = $post['data_abertura_from'] . " 00:00:00";
    $data_abertura_from = dateDB($data_abertura_from);

    $terms .= " AND o.oco_real_open_date >= '" . $data_abertura_from . "' ";
    $criterText = TRANS('SMART_MIN_DATE_OPENING') . ": " . $post['data_abertura_from'] . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}

if (isset($post['data_abertura_to']) && !empty($post['data_abertura_to'])) {
    $data_abertura_to = "";

    $data_abertura_to = $post['data_abertura_to'] . " 23:59:59";
    $data_abertura_to = dateDB($data_abertura_to);

    $terms .= " AND o.oco_real_open_date <= '" . $data_abertura_to . "' ";
    $criterText = TRANS('SMART_MAX_DATE_OPENING') . ": " . $post['data_abertura_to'] . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}

if (isset($post['no_empty_response']) && $post['no_empty_response'] == 1) {
    $terms .= " AND o.data_atendimento IS NOT null ";
    $criterText = TRANS('SMART_HAS_FIRST_RESPONSE') ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['empty_response']) && $post['empty_response'] == 1) {
    $terms .= " AND o.data_atendimento IS null ";
    $criterText = TRANS('SMART_HASNT_FIRST_RESPONSE') ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['data_atendimento_from']) && !empty($post['data_atendimento_from'])) {
    $data_atendimento_from = "";

    $data_atendimento_from = $post['data_atendimento_from'] . " 00:00:00";
    $data_atendimento_from = dateDB($data_atendimento_from);

    $terms .= " AND o.data_atendimento >= '" . $data_atendimento_from . "' ";
    $criterText = TRANS('SMART_MIN_DATE_FIRST_RESPONSE') . ": " . $post['data_atendimento_from'] . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}
if (isset($post['data_atendimento_to']) && !empty($post['data_atendimento_to'])) {
    $data_atendimento_to = "";

    $data_atendimento_to = $post['data_atendimento_to'] . " 23:59:59";
    $data_atendimento_to = dateDB($data_atendimento_to);

    $terms .= " AND o.data_atendimento <= '" . $data_atendimento_to . "' ";
    $criterText = TRANS('SMART_MAX_DATE_FIRST_RESPONSE') . ": " . $post['data_atendimento_to'] . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}

if (isset($post['closed']) && $post['closed'] == 1) {
    $terms .= " AND o.data_fechamento IS NOT null ";
    $criterText = TRANS('CARDS_CLOSED') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['not_closed']) && $post['not_closed'] == 1) {
    $terms .= " AND o.data_fechamento IS null ";
    $criterText = TRANS('CARDS_NOT_CLOSED') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['data_fechamento_from']) && !empty($post['data_fechamento_from'])) {
    $data_fechamento_from = "";

    $data_fechamento_from = $post['data_fechamento_from'] . " 00:00:00";
    $data_fechamento_from = dateDB($data_fechamento_from);

    $terms .= " AND o.data_fechamento >= '" . $data_fechamento_from . "' ";
    $criterText = TRANS('SMART_MIN_DATE_CLOSURE') . ": " . $post['data_fechamento_from'] . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}
$data_fechamento_to = "";
if (isset($post['data_fechamento_to']) && !empty($post['data_fechamento_to'])) {

    $data_fechamento_to = $post['data_fechamento_to'] . " 23:59:59";
    $data_fechamento_to = dateDB($data_fechamento_to);

    $terms .= " AND o.data_fechamento <= '" . $data_fechamento_to . "' ";
    $criterText = TRANS('SMART_MAX_DATE_CLOSURE') . ": " . $post['data_fechamento_to'] . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}




if (isset($post['no_empty_contact_email']) && $post['no_empty_contact_email'] == 1) {
    $terms .= " AND ( o.contato_email != '' AND o.contato_email IS NOT NULL  ) ";
    $criterText = TRANS('CONTACT_EMAIL') . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_contact_email']) && $post['no_contact_email'] == 1) {
    $terms .= " AND ( o.contato_email = '' OR o.contato_email IS NULL ) ";
    $criterText = TRANS('CONTACT_EMAIL') . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['contact_email']) && !empty($post['contact_email'])) {
    
    
    $terms .= " AND o.contato_email = '" . noHtml($post['contact_email']) . "' ";
    
    $criterText = TRANS('CONTACT_EMAIL') . ": " . noHtml($post['contact_email']) . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}









if (isset($post['no_empty_area']) && $post['no_empty_area'] == 1) {
    $terms .= " AND ( o.sistema != '-1' AND o.sistema != '0' ) ";
    $criterText = TRANS('SERVICE_AREA') . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_area']) && $post['no_area'] == 1) {
    $terms .= " AND ( o.sistema = '-1' OR o.sistema = '0') ";
    $criterText = TRANS('SERVICE_AREA') . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['area']) && !empty($post['area'])) {
    $areaIN = "";
    foreach ($post['area'] as $area) {
        if (strlen($areaIN)) $areaIN .= ",";
        $areaIN .= $area;
    }
    $terms .= " AND o.sistema IN ({$areaIN}) ";

    $criterText = "";
    $sqlCriter = "SELECT sistema FROM sistemas WHERE sis_id in ({$areaIN}) ORDER BY sistema";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter['sistema'];
    }
    $criterText = TRANS('SERVICE_AREA') . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


if (isset($post['no_empty_problema']) && $post['no_empty_problema'] == 1) {
    $terms .= " AND ( o.problema != '-1' AND o.problema != '0' ) ";
    $criterText = TRANS('ISSUE_TYPE') . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_problema']) && $post['no_problema'] == 1) {
    $terms .= " AND ( o.problema = '-1' OR o.problema = '0') ";
    $criterText = TRANS('ISSUE_TYPE') . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['problema']) && !empty($post['problema'])) {
    $probIN = "";
    foreach ($post['problema'] as $problema) {
        if (strlen($probIN)) $probIN .= ",";
        $probIN .= $problema;
    }
    $terms .= " AND o.problema IN ({$probIN}) ";

    $criterText = "";
    $sqlCriter = "SELECT problema FROM problemas WHERE prob_id in ({$probIN}) ORDER BY problema";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter['problema'];
    }
    $criterText = TRANS('ISSUE_TYPE') . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}



if (isset($post['no_empty_unidade']) && $post['no_empty_unidade'] == 1) {
    $terms .= " AND ( o.instituicao != '-1' AND o.instituicao != '0' AND o.instituicao IS NOT NULL ) ";
    $criterText = TRANS('COL_UNIT') . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_unidade']) && $post['no_unidade'] == 1) {
    $terms .= " AND ( o.instituicao = '-1' OR o.instituicao = '0' OR o.instituicao IS NULL ) ";
    $criterText = TRANS('COL_UNIT') . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['unidade']) && !empty($post['unidade'])) {
    $unitIN = "";
    
    if (is_array($post['unidade'])) {
        foreach ($post['unidade'] as $unidade) {
            if (strlen($unitIN)) $unitIN .= ",";
            $unitIN .= $unidade;
        }
    } else {
        $unitIN = $post['unidade'];
    }
    
    
    $terms .= " AND o.instituicao IN ({$unitIN}) ";

    $criterText = "";
    $sqlCriter = "SELECT inst_nome FROM instituicao WHERE inst_cod in ({$unitIN}) ORDER BY inst_nome";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter['inst_nome'];
    }
    $criterText = TRANS('COL_UNIT') . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}



if (isset($post['no_empty_etiqueta']) && $post['no_empty_etiqueta'] == 1) {
    $terms .= " AND ( o.equipamento != '-1' AND o.equipamento != '0' AND o.equipamento IS NOT NULL AND o.equipamento != '' ) ";
    $criterText = TRANS('ASSET_TAG') . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_etiqueta']) && $post['no_etiqueta'] == 1) {
    $terms .= " AND ( o.equipamento = '-1' OR o.equipamento = '0' OR o.equipamento IS NULL OR o.equipamento = '' ) ";
    $criterText = TRANS('ASSET_TAG') . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['etiqueta']) && !empty($post['etiqueta'])) {
    
    $tmp = explode(',', $post['etiqueta']);
    // $treatValues = array_map('intval', $tmp);
    $treatValues = array_map('noHtml', $tmp);
    $tagIN = "";
    foreach ($treatValues as $tag) {
        if (strlen($tagIN)) $tagIN .= ", ";
        $tag = trim($tag);
        $tagIN .= "'{$tag}'";
    }
    $terms .= " AND o.equipamento IN ({$tagIN}) ";
    
    $criterText = TRANS('ASSET_TAG') . ": {$tagIN}<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


if (isset($post['no_empty_departamento']) && $post['no_empty_departamento'] == 1) {
    $terms .= " AND ( o.local != '-1' AND o.local != '0' AND o.local IS NOT NULL AND o.local != '') ";
    $criterText = TRANS('DEPARTMENT') . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_departamento']) && $post['no_departamento'] == 1) {
    $terms .= " AND ( o.local = '-1' OR o.local = '0' OR o.local IS NULL OR o.local = '' ) ";
    $criterText = TRANS('DEPARTMENT') . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['departamento']) && !empty($post['departamento'])) {
    $localIN = "";
    foreach ($post['departamento'] as $departamento) {
        if (strlen($localIN)) $localIN .= ",";
        $localIN .= $departamento;
    }
    $terms .= " AND o.local IN ({$localIN}) ";

    $criterText = "";
    $sqlCriter = "SELECT local FROM localizacao WHERE loc_id in ({$localIN}) ORDER BY local";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter['local'];
    }
    $criterText = TRANS('DEPARTMENT') . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}





if (isset($post['end_user_only']) && $post['end_user_only'] == 1) {
    $terms .= " AND ua.nivel = '3' ";
    $criterText = TRANS('SMART_OPENING_USER_TYPE') . ": " . TRANS('SMART_ONLY_BY_ENDUSER') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_end_user']) && $post['no_end_user'] == 1) {
    $terms .= " AND ua.nivel in (1,2) ";
    $criterText = TRANS('SMART_OPENING_USER_TYPE') . ": " . TRANS('SMART_ONLY_BY_TECHNITIANS') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['aberto_por']) && !empty($post['aberto_por'])) {
    $abertoPorIN = "";
    foreach ($post['aberto_por'] as $aberto_por) {
        if (strlen($abertoPorIN)) $abertoPorIN .= ",";
        $abertoPorIN .= $aberto_por;
    }
    $terms .= " AND o.aberto_por IN ({$abertoPorIN}) ";

    $criterText = "";
    $sqlCriter = "SELECT nome FROM usuarios WHERE user_id in ({$abertoPorIN}) ORDER BY nome";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter['nome'];
    }
    $criterText = TRANS('OPENED_BY') . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


if (isset($post['last_editor']) && !empty($post['last_editor'])) {
    $lastEditorIN = "";
    foreach ($post['last_editor'] as $last_editor) {
        if (strlen($lastEditorIN)) $lastEditorIN .= ",";
        $lastEditorIN .= $last_editor;
    }
    $terms .= " AND o.operador IN ({$lastEditorIN}) ";

    $criterText = "";
    $sqlCriter = "SELECT nome FROM usuarios WHERE user_id in ({$lastEditorIN}) ORDER BY nome";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter['nome'];
    }
    $criterText = TRANS('SMART_LAST_EDITOR') . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


if (isset($post['prioridade']) && !empty($post['prioridade'])) {
    $prioridadeIN = "";
    foreach ($post['prioridade'] as $prioridade) {
        if (strlen($prioridadeIN)) $prioridadeIN .= ",";
        $prioridadeIN .= $prioridade;
    }
    $terms .= " AND o.oco_prior IN ({$prioridadeIN}) ";

    $criterText = "";
    $sqlCriter = "SELECT pr_desc FROM prior_atend WHERE pr_cod in ({$prioridadeIN}) ORDER BY pr_desc";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter['pr_desc'];
    }
    $criterText = TRANS('COL_PRIORITY') . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


if (isset($post['time_freeze_status_only']) && $post['time_freeze_status_only'] == 1) {
    $terms .= " AND s.stat_time_freeze = 1 AND s.stat_id NOT IN (4,12) "; /* desconsidera os status fixos de encerramento e cancelamento */
    $criterText = TRANS('SMART_NOT_CLOSED_PAUSED_STATUS') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_time_freeze_status']) && $post['no_time_freeze_status'] == 1) {
    $terms .= " AND s.stat_time_freeze = 0 AND s.stat_id NOT IN (4,12) ";
    $criterText = TRANS('SMART_NOT_CLOSED_RUNNING_STATUS') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['status']) && !empty($post['status'])) {
    $statusIN = "";
    foreach ($post['status'] as $status) {
        if (strlen($statusIN)) $statusIN .= ",";
        $statusIN .= $status;
    }
    $terms .= " AND o.status IN ({$statusIN}) ";

    $criterText = "";
    $sqlCriter = "SELECT status FROM status WHERE stat_id in ({$statusIN}) ORDER BY status";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter['status'];
    }
    $criterText = TRANS('COL_STATUS') . "Status: " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}

if (isset($post['response_sla']) && !empty($post['response_sla'])) {
    $criterText = "";
    foreach ($post['response_sla'] as $res) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $slaIndicatorLabel[$res];
    }

    $criterText = TRANS('RESPONSE_SLA') . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
    $terms .= " ";
} 
if (isset($post['solution_sla']) && !empty($post['solution_sla'])) {
    $criterText = "";
    foreach ($post['solution_sla'] as $res) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $slaIndicatorLabel[$res];
    }

    $criterText = TRANS('SOLUTION_SLA') . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
    $terms .= " ";
} 

if (isset($post['only_relatives']) && !empty($post['only_relatives'])) {

    $criterText = TRANS('SMART_ONLY_WITH_TICKETS_REFERENCED') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
    $terms .= " ";
} elseif (isset($post['no_relatives']) && !empty($post['no_relatives'])) {

    $criterText = TRANS('SMART_ONLY_WITHOUT_TICKETS_REFERENCED') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
    $terms .= " ";
} 

if (isset($post['only_attachments']) && !empty($post['only_attachments'])) {

    $criterText = TRANS('ONLY_TICKETS_WITH_ATTACHMENTS') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
    $terms .= " ";
} elseif (isset($post['no_attachments']) && !empty($post['no_attachments'])) {

    $criterText = TRANS('ONLY_TICKETS_WITHOUT_ATTACHMENTS') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
    $terms .= " ";
} 





if (empty($terms)) {
    $criterText = TRANS('SMART_WITHOUT_SEARCH_CRITERIA') . "<br />";
    $criteria[] = "<span class='{$badgeClassEmptySearch}'>{$criterText}</span>";
}

// echo $terms;

$sql = $QRY["ocorrencias_full_ini"] . " WHERE 1 = 1 {$terms} ORDER BY numero";

$sqlResult = $conn->query($sql);
$totalFiltered = $sqlResult->rowCount();

$criterios = "";

?>
    <!-- <div class="row">
        <div class="col-12">Foram encontrados <span class="bold"><?= $totalFiltered; ?></span> registros de acordo com os seguintes <span class="bold">critérios de pesquisa:</span></div>
    </div> -->
    <div id="table_info"></div>
    <div id="div_criterios" class="row p-4">
        <div class="col-10">
            <?php
            foreach ($criteria as $badge) {
                // echo $badge . "&nbsp;";
                $criterios .= $badge . "&nbsp;";
            }
            ?> 
        </div>
        
    </div>
    <div class="display-buttons"></div>

    <div class="double-scroll">
        <table id="table_tickets_queue" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
            <thead>
                <tr class="header">
                    <td class='line'><?= TRANS('NUMBER_ABBREVIATE'); ?></td>
                    <td class='line area'><?= TRANS('AREA'); ?></td>
                    <td class='line problema'><?= TRANS('ISSUE_TYPE'); ?></td>
                    <td class='line aberto_por'><?= TRANS('OPENED_BY'); ?></td>
                    <td class='line contato'><?= TRANS('CONTACT'); ?></td>
                    <td class='line contato_email'><?= TRANS('CONTACT_EMAIL'); ?></td>
                    <td class='line telefone'><?= TRANS('COL_PHONE'); ?></td>
                    <td class='line departamento'><?= TRANS('DEPARTMENT'); ?></td>
                    <td class='line descricao truncate_flag truncate'><?= TRANS('DESCRIPTION'); ?></td>
                    <td class='line data_abertura'><?= TRANS('OPENING_DATE'); ?></td>
                    <td class='line agendado'><?= TRANS('IS_SCHEDULED'); ?></td>
                    <td class='line agendado_para'><?= TRANS('FIELD_SCHEDULE_TO'); ?></td>
                    <td class='line data_atendimento'><?= TRANS('FIRST_RESPONSE'); ?></td>
                    <td class='line data_fechamento'><?= TRANS('FIELD_DATE_CLOSING'); ?></td>
                    <td class='line unidade'><?= TRANS('COL_UNIT'); ?></td>
                    <td class='line etiqueta'><?= TRANS('ASSET_TAG'); ?></td>
                    <td class='line status'><?= TRANS('COL_STATUS'); ?></td>
                    <td class='line tempo_absoluto'><?= TRANS('ABSOLUTE_TIME'); ?></td>
                    <td class='line tempo'><?= TRANS('FILTERED_TIME'); ?></td>
                    <td class='line prioridade'><?= TRANS('OCO_PRIORITY'); ?></td>
                    <td class='line sla'><?= TRANS('COL_SLA'); ?></td>
                </tr>
            </thead>
       
<?php



foreach ($sqlResult->fetchAll() as $row){
    $nestedData = array(); 
    $showRecord = true;
    
    

    /* CHECAGEM DE SUB-CHAMADOS */
    $sqlSubCall = "select * from ocodeps where dep_pai = " . $row['numero'] . " or dep_filho = " . $row['numero'] . "";
    $execSubCall = $conn->query($sqlSubCall);
    $regSub = $execSubCall->rowCount();
    if ($regSub > 0) {

        if (isset($post['no_relatives']) && $post['no_relatives'] == 1) {
            $showRecord = false;
        }

        #É CHAMADO PAI?
        $sqlSubCall = "select * from ocodeps where dep_pai = " . $row['numero'] . "";
        $execSubCall = $conn->query($sqlSubCall);
        $regSub = $execSubCall->rowCount();


        $comDeps = false;
        foreach ($execSubCall->fetchAll() as $rowSubPai) {
            $sqlStatus = "select o.*, s.* from ocorrencias o, `status` s  where o.numero=" . $rowSubPai['dep_filho'] . " and o.`status`=s.stat_id and s.stat_painel not in (3) ";
            $execStatus = $conn->query($sqlStatus);
            $regStatus = $execStatus->rowCount();
            if ($regStatus > 0) {
                $comDeps = true;
            }
        }
        if ($comDeps) {
            $imgSub = "<a onClick=\"javascript:popup('../../includes/help/help_depends.php')\"><img src='" . $imgsPath . "sub-ticket-red.svg' class='mb-1' height='10' data-title='" . TRANS('TICKET_WITH_RESTRICTIVE_RELATIONS') . "'></a>";
        } else {
            $imgSub = "<a onClick=\"javascript:popup('../../includes/help/help_depends.php')\"><img src='" . $imgsPath . "sub-ticket-green.svg' class='mb-1' height='10' data-title='" . TRANS('TICKET_WITH_OPEN_RELATIONS') . "'></a>";
        }
    } else {
        if (isset($post['only_relatives']) && $post['only_relatives'] == 1) {
            $showRecord = false;
        }
        $imgSub = "";
    }
    /* FINAL DA CHEGAGEM DE SUB-CHAMADOS */

    
    /* CHECAGEM DE ANEXOS */
    $qryImg = "select * from imagens where img_oco = " . $row['numero'] . "";
    $execImg = $conn->query($qryImg);
    $regImg =  $execImg->rowCount();
    
    if ($regImg != 0) {
        
        if ($showRecord) {
            if (isset($post['no_attachments']) && !empty($post['no_attachments'])) {
                $showRecord = false;
            }
        }
        
        $linkImg = "<a onClick=\"javascript:popup_wide('listFiles.php?COD=" . $row['numero'] . "')\"><img src='../../includes/icons/attach2.png'></a>";
        // $linkImg = "<a onClick=\"javascript:popup_wide('listFiles.php?COD=" . $row['numero'] . "')\"><i class='fas fa-paperclip'></i></a>";
    } else {

        if ($showRecord) {
            if (isset($post['only_attachments']) && !empty($post['only_attachments'])) {
                $showRecord = false;
            }
        }

        $linkImg = "";
    }
    /* FINAL DA CHECAGEM DE ANEXOS */


    /* DESCRIÇÃO DO CHAMADO */
    $texto = trim($row['descricao']);

    /* COR DO BADGE DA PRIORIDADE */
    if (!isset($row['cor'])) {
        $COR = '#CCCCCC';
    } else {
        $COR = $row['cor'];
    }


    $referenceDate = (!empty($row['oco_real_open_date']) ? $row['oco_real_open_date'] : $row['data_abertura']);
    $dataAtendimento = $row['data_atendimento']; //data da primeira resposta ao chamado
    $dataFechamento = $row['data_fechamento'];

    /* NOVOS MÉTODOS PARA O CÁLCULO DE TEMPO VÁLIDO DE RESPOSTA E SOLUÇÃO */
    $holidays = getHolidays($conn);
    $profileCod = getProfileCod($conn, $_SESSION['s_wt_areas'], $row['numero']);
    $worktimeProfile = getWorktimeProfile($conn, $profileCod);

    /* Objeto para o cálculo de Tempo válido de SOLUÇÃO - baseado no perfil de jornada de trabalho e nas etapas em cada status */
    $newWT = new WorkTime( $worktimeProfile, $holidays );
    
    /* Objeto para o cálculo de Tempo válido de RESPOSTA baseado no perfil de jornada de trabalho e nas etapas em cada status */
    $newWTResponse = new WorkTime( $worktimeProfile, $holidays );

    /* Objeto para checagem se o momento atual está coberto pelo perfil de jornada associado */
    $objWT = new Worktime( $worktimeProfile, $holidays );

    /* Realiza todas as checagens necessárias para retornar os tempos de resposta e solução para o chamado */
    $ticketTimeInfo = getTicketTimeInfo($conn, $newWT, $newWTResponse, $row['numero'], $referenceDate, $dataAtendimento, $dataFechamento, $row['status_cod'], $objWT);

    /* Retorna os leds indicativos (bolinhas) para os tempos de resposta e solução */
    $ledSlaResposta = showLedSLA($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']);
    $ledSlaSolucao = showLedSLA($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']);

    $isRunning = $ticketTimeInfo['running'];

    $colTVNew = $ticketTimeInfo['solution']['time'];
    if ($row['status_cod'] == 4) {
        $colTVNew = $iconTicketClosed . "&nbsp;" . $colTVNew;
    } elseif (isTicketFrozen($conn, $row['numero'])) {
        $colTVNew = $iconFrozen . "&nbsp;" . $colTVNew;
    } elseif (!$isRunning) {
        $colTVNew = $iconOutOfWorktime . "&nbsp;" . $colTVNew;
    }

    
    /* Checagem sobre o filtro de SLAs */
    // $showRecord = true;
    $responseResult = getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']);
    $solutionResult = getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']);
    $absoluteTime = absoluteTime($referenceDate, (!empty($dataFechamento) ? $dataFechamento : date('Y-m-d H:i:s')))['inTime'];

    if ($showRecord) {
        if (isset($post['response_sla']) && !empty($post['response_sla'])) {
            $showRecord = false;
            foreach ($post['response_sla'] as $res) {

                if ($res == $responseResult )
                    $showRecord = true;
            }
        }
    }
    

    if ($showRecord) {
        if (isset($post['solution_sla']) && !empty($post['solution_sla'])) {
            $showRecord = false;
            foreach ($post['solution_sla'] as $res) {
                if ($res == $solutionResult )
                    $showRecord = true;
            }
        } 
    }
    

    if ($showRecord) {

        ?>
        <tr>
            <td class="line" data-sort="<?= $row['numero']; ?>"><span class="pointer" onClick="openTicketInfo('<?= $row['numero']; ?>')"><?= "{$imgSub}&nbsp;<b>" . $row['numero'] . "</b>"; ?></span></td>
            <td class="line"><?= "<b>" . $row['area'] . "</>"; ?></td>
            <td class="line"><?= $linkImg."&nbsp;".$row['problema']; ?></td>
            <td class="line"><?= "<b>" . $row['aberto_por'] . "</b>"; ?></td>
            <td class="line"><?= "<b>" . $row['contato'] . "</b>"; ?></td>
            <td class="line"><?= "<b>" . $row['contato_email'] . "</b>"; ?></td>
            <td class="line"><?= "<b>" . $row['telefone'] . "</b>"; ?></td>
            <td class="line"><?= "<b>" . $row['setor'] . "</b>"; ?></td>
            <td class="line"><?= $texto; ?></td>
            <?php
                $mydate = strtotime($row['oco_real_open_date']);
            ?>
            <td class="line" data-sort="<?= $mydate; ?>"><?= "<b>" . dateScreen($row['oco_real_open_date']) . "</>"; ?></td>
            <td class="line"><?= "<b>" . transbool($row['oco_scheduled']) . "</>"; ?></td>
            <td class="line" data-sort="<?= $row['oco_scheduled_to']; ?>"><?= "<b>" . dateScreen($row['oco_scheduled_to']) . "</>"; ?></td>
            <td class="line" data-sort="<?= $row['data_atendimento']; ?>"><?= "<b>" . dateScreen($row['data_atendimento']) . "</>"; ?></td>
            <td class="line" data-sort="<?= $row['data_fechamento']; ?>"><?= "<b>" . dateScreen($row['data_fechamento']) . "</>"; ?></td>
            <td class="line"><?= "<b>" . $row['unidade'] . "</>"; ?></td>
            <td class="line"><?= "<b>" . $row['etiqueta'] . "</>"; ?></td>
            <td class="line"><?= "<b>" . $row['chamado_status'] . "</>"; ?></td>
            <td class="line"><?= $absoluteTime; ?></td>
            <td class="line" data-sort="<?= $ticketTimeInfo['solution']['seconds']; ?>"><?= $colTVNew; ?></td>
            <td class="line" data-sort="<?= $row['pr_atendimento']; ?>"><?= "<span class='badge text-gray' style='background-color: " . $COR . "'>" . $row['pr_descricao'] . "</span>"; ?></td>
            <td class="line"><?= "<img height='20' src='" . $imgsPath . "" . $ledSlaResposta . "' title='" . TRANS('HNT_RESPONSE_LED') . "'>&nbsp;<img height='20' src='" . $imgsPath . "" . $ledSlaSolucao . "' title='" . TRANS('HNT_SOLUTION_LED') . "'>"; ?></td>
            
        </tr>
        <?php
    } else {
        $totalFiltered--;
    }
}
?>
        </table>
        <div class="d-none" id="table_info_hidden">
            <div class="row"> <!-- d-none -->
                <div class="col-12"><?= TRANS('WERE_FOUND'); ?> <span class="bold"><?= $totalFiltered; ?></span> <?= TRANS('POSSIBLE_RECORDS_ACORDING_TO_FOLLOW'); ?> <span class="bold"><?= TRANS('SMART_SEARCH_CRITERIA'); ?>:</span></div>
            </div>
            <div class="row p-2 mt-2" id="divCriterios">
                <div class="col-10">
                    <?= $criterios; ?>
                </div>
            </div>

        </div>

    </div>
