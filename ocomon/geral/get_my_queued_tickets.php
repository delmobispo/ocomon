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
require_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

use includes\classes\ConnectPDO;
$conn = ConnectPDO::getInstance();


$config = getConfig($conn);

//Todas as áreas que o usuário percente
$uareas = $_SESSION['s_uareas'];

$imgsPath = "../../includes/imgs/";
$iconFrozen = "<span class='text-oc-teal' title='" . TRANS('HNT_TIMER_STOPPED') . "'><i class='fas fa-pause fa-lg'></i></span>";
$iconOutOfWorktime = "<span class='text-oc-teal' title='" . TRANS('HNT_TIMER_OUT_OF_WORKTIME') . "'><i class='fas fa-pause fa-lg'></i></i></span>";
$percLimit = $config['conf_sla_tolerance']; //Tolerância em percentual - jogar para o menu de administração

// storing  request (ie, get/post) global array to a variable  
$requestData = $_POST;

//FOR ORDER BY
$columns = array(
	// datatable column index  => database column name
    0 => '',
    1 => 'numero',
    2 => 'problema',
    3 => 'contato',
    4 => 'setor',
    5 => 'chamado_status',
    6 => 'numero',
    7 => 'pr_atendimento',
);

// getting total number records without any search
$sql = $QRY["ocorrencias_full_ini"] . " WHERE s.stat_painel in (2) and ( o.sistema in (" . $uareas . ") OR o.sistema = '-1' ) and o.oco_scheduled=0 ORDER BY numero";
$sqlResult = $conn->query($sql);
$totalData = $sqlResult->rowCount();
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

$sql = $QRY["ocorrencias_full_ini"] . " WHERE s.stat_painel in (2) and ( o.sistema in (" . $uareas . ") OR o.sistema = '-1' ) and o.oco_scheduled=0 ";

if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter

    $sql.=" AND ( p.problema LIKE '%".$requestData['search']['value']."%' ";  
	$sql.=" OR a.sistema LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR o.contato LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR l.local LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR s.status LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR o.numero LIKE '%".$requestData['search']['value']."%' )";
}

$sqlResult = $conn->query($sql);
$totalFiltered = $sqlResult->rowCount();


$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

// dump($sql);
$sqlResult = $conn->query($sql);

$data = array();

foreach ($sqlResult->fetchAll() as $row){
    $nestedData = array(); 
    
    /* CHECAGEM DE SUB-CHAMADOS */
    $sqlSubCall = "select * from ocodeps where dep_pai = " . $row['numero'] . " or dep_filho = " . $row['numero'] . "";
    $execSubCall = $conn->query($sqlSubCall);
    $regSub = $execSubCall->rowCount();
    if ($regSub > 0) {
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
            $imgSub = "<a onClick=\"javascript:popup('../../includes/help/help_depends.php')\"><img src='" . $imgsPath . "sub-ticket-red.svg' class='mb-1' height='10' title='" . TRANS('TICKET_WITH_RESTRICTIVE_RELATIONS') . "'></a>";
        } else {
            $imgSub = "<a onClick=\"javascript:popup('../../includes/help/help_depends.php')\"><img src='" . $imgsPath . "sub-ticket-green.svg' class='mb-1' height='10' title='" . TRANS('TICKET_WITH_OPEN_RELATIONS') . "'></a>";
        }
    } else {
        $imgSub = "";
    }
    /* FINAL DA CHEGAGEM DE SUB-CHAMADOS */

    /* CHECAGEM DE ANEXOS */
    $qryImg = "select * from imagens where img_oco = " . $row['numero'] . "";
    $execImg = $conn->query($qryImg);
    $regImg =  $execImg->rowCount();
    if ($regImg != 0) {
        $linkImg = "<a onClick=\"javascript:popup_wide('listFiles.php?COD=" . $row['numero'] . "')\"><img src='../../includes/icons/attach2.png'></a>";
        // $linkImg = "<a onClick=\"javascript:popup_wide('listFiles.php?COD=" . $row['numero'] . "')\"><i class='fas fa-paperclip'></i></a>";
    } else {
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

    /* NOVOS MÉTODOS PARA O CÁLCULO DE TEMPO VÁLIDP DE RESPOSTA E SOLUÇÃO */
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
    if (isTicketFrozen($conn, $row['numero'])) {
        $colTVNew = $iconFrozen . "&nbsp;" . $colTVNew;
    } elseif (!$isRunning) {
        $colTVNew = $iconOutOfWorktime . "&nbsp;" . $colTVNew;
    }
    /* FINAL DO TRECHO SOBRE O TEMPO FILTRADO */

    $colTime = array();
    $colTime[] = $ticketTimeInfo['solution']['seconds'];
    $colTime[] = $colTVNew;

    $colOrderPrior = array();
    $colOrderPrior[] = $row['pr_atendimento'];
    $colOrderPrior[] = "<span class='badge text-gray' style='background-color: " . $COR . "'>" . $row['pr_descricao'] . "</span>";


    $nestedData[] = ""; /* reservado para o botao de expandir e esconder quando responsivo */
    $nestedData[] = "{$imgSub}&nbsp;<b>" . $row['numero'] . "</b><br/>" . $row['area'];
	$nestedData[] = $linkImg."&nbsp;".$row['problema'];
    $nestedData[] = "<b>" . $row['contato'] . "</b><br/>" . $row['telefone'];
    $nestedData[] = "<b>" . $row['setor'] . "</b><br/>" . $texto;
    $nestedData[] = "<b>" . $row['chamado_status'] . "</b>";
    // $nestedData[] = $colTVNew;
    $nestedData[] = $colTime; /* enviando um array para ordenar pelo indice 0 */
    $nestedData[] = $colOrderPrior; /* enviando um array para ordenar pelo indice 0 */
    // $nestedData[] = "<span class='badge text-gray' style='background-color: " . $COR . "'>" . $row['pr_descricao'] . "</span>";
    $nestedData[] = "<img height='20' src='" . $imgsPath . "" . $ledSlaResposta . "' title='" . TRANS('HNT_RESPONSE_LED') . "'>&nbsp;<img height='20' src='" . $imgsPath . "" . $ledSlaSolucao . "' title='" . TRANS('HNT_SOLUTION_LED') . "'>";
    $nestedData['DT_RowId'] = 'id_' . $row['numero']; //DT_RowId é reservado
    
	$data[] = $nestedData;
}

/* MENSAGEM QUE SERÁ EXIBIDA EM CASO DE NENHUM REGISTRO ENCONTRADO */
// $customMessage = message('secondary','', TRANS('OCO_NOT_PENDING_TO_USER'),'','',true);

$json_data = array(
    "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
    "recordsTotal"    => intval( $totalData ),  // total number of records
    "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
    // "customMessage"   => $customMessage,
    "data"            => $data   // total data array
    );

echo json_encode($json_data);  // send data as json format

?>