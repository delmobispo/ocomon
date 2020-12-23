<?php session_start();
/*  Copyright 2020 Flávio Ribeiro

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

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);

if (!isset($_POST['numero'])) {
    exit();
}

$numero = (int)$_POST['numero'];

$imgsPath = "../../includes/imgs/";
$iconFrozen = "<span class='text-oc-teal' title='" . TRANS('HNT_TIMER_STOPPED') . "'><i class='fas fa-pause fa-lg'></i></span>";
$iconOutOfWorktime = "<span class='text-oc-teal' title='" . TRANS('HNT_TIMER_OUT_OF_WORKTIME') . "'><i class='fas fa-pause fa-lg'></i></i></span>";
$iconClosed = "<span class='text-oc-teal' title='" . TRANS('HNT_TICKET_CLOSED') . "'><i class='fas fa-check fa-lg'></i></span>";
$config = getConfig($conn);
$percLimit = $config['conf_sla_tolerance']; 
$holidays = getHolidays($conn);

$erro = false;

$sql = $QRY["ocorrencias_full_ini"]. "WHERE o.numero = {$numero}";
try {
    $resultSQL = $conn->query($sql);
}
catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    $erro = true;
    return false;
}
$row = $resultSQL->fetch();


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

    $responseResult = getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']);
    $solutionResult = getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']);
    
    $isRunning = $ticketTimeInfo['running'];

    $colTVNew = $ticketTimeInfo['solution']['time'];
    if (!empty($dataFechamento)) {
        $colTVNew = $iconClosed . "&nbsp;" . $colTVNew;
    } elseif (isTicketFrozen($conn, $row['numero'])) {
        $colTVNew = $iconFrozen . "&nbsp;" . $colTVNew;
    } elseif (!$isRunning) {
        $colTVNew = $iconOutOfWorktime . "&nbsp;" . $colTVNew;
    }
    /* FINAL DO TRECHO SOBRE O TEMPO FILTRADO */

$data = array();

$data['sla_resposta'] = $row['sla_resposta'] ?? TRANS('MSG_NOT_DEFINED'); 
$data['sla_solucao'] = $row['sla_solucao'] ?? TRANS('MSG_NOT_DEFINED'); 

$responseInHours = "";
if (!empty($row['sla_resposta_tempo']) && $row['sla_resposta_tempo'] > 60)
    $responseInHours = round($row['sla_resposta_tempo'] / 60, 2) . " " . TRANS('FILTERED_HOURS'); else
    $responseInHours = TRANS('FILTERED_TIME');

$solutionInHours = "";
if (!empty($row['sla_solucao_tempo']) && $row['sla_solucao_tempo'] > 60)
    $solutionInHours = round($row['sla_solucao_tempo'] / 60, 2) . " " . TRANS('FILTERED_HOURS'); else
    $solutionInHours = TRANS('FILTERED_TIME');

$data['sla_resposta_in_hours'] = $responseInHours;
$data['sla_solucao_in_hours'] = $solutionInHours; 

$data['sla_response_result'] = $responseResult;
$data['sla_solution_result'] = $solutionResult;
$data['setor'] = $row['setor'];
$data['problema'] = $row['problema'];

$data['filter_time'] = "<img height='20' src='" . $imgsPath . "" . $ledSlaSolucao . "' title='" . TRANS('HNT_SOLUTION_LED') . "'>&nbsp;" . $colTVNew;
$data['response_time'] = "<img height='20' src='" . $imgsPath . "" . $ledSlaResposta . "' title='" . TRANS('HNT_RESPONSE_LED') . "'>&nbsp;" . $ticketTimeInfo['response']['time'];



$data['abs_response_time'] = absoluteTime($referenceDate, (!empty($dataAtendimento) ? $dataAtendimento : date('Y-m-d H:i:s')))['inTime'];
$data['abs_solution_time'] = absoluteTime($referenceDate, (!empty($dataFechamento) ? $dataFechamento : date('Y-m-d H:i:s')))['inTime'];

echo json_encode($data);
