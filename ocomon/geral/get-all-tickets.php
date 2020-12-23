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
$config = getConfig($conn);
$percLimit = $config['conf_sla_tolerance']; 

$post = $_POST;
$dataSearch = (array)json_decode($post['data']);

// dump($dataSearch); exit;


$terms = "";
$postArea = (isset($dataSearch['area']) && !empty($dataSearch['area']) ? $dataSearch['area'] : '');
if (!empty($dataSearch['area'])) {
    $terms .= " AND o.sistema = " . $dataSearch['area'];
}

if (!empty($dataSearch['problema'])) {
    $terms .= " AND o.problema = " . $dataSearch['problema'];
}
// dump($dataSearch);

// echo $terms; exit();
echo $terms;

$sql = $QRY["ocorrencias_full_ini"] . " WHERE 1 = 1 {$terms} ORDER BY numero";

    /* $sql.=" AND ( p.problema LIKE '%".$post['search']['value']."%' ";  
	$sql.=" OR a.sistema LIKE '%".$post['search']['value']."%' ";
	$sql.=" OR o.contato LIKE '%".$post['search']['value']."%' ";
	$sql.=" OR l.local LIKE '%".$post['search']['value']."%' ";
	$sql.=" OR s.status LIKE '%".$post['search']['value']."%' ";
	$sql.=" OR o.numero LIKE '%".$post['search']['value']."%' )"; */


$sqlResult = $conn->query($sql);
$totalFiltered = $sqlResult->rowCount();


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
            $imgSub = "<a onClick=\"javascript:popup('../../includes/help/help_depends.php')\"><img src='" . $imgsPath . "sub-ticket-red.svg' class='mb-1' height='10' title='Chamado com restrições para encerramento'></a>";
        } else {
            $imgSub = "<a onClick=\"javascript:popup('../../includes/help/help_depends.php')\"><img src='" . $imgsPath . "sub-ticket-green.svg' class='mb-1' height='10' title='Chamado com vínculos mas sem restrições de encerramento'></a>";
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

    $nestedData['numero'] = "{$imgSub}&nbsp;<b>" . $row['numero'] . "</b><br/>" . $row['area'];
	$nestedData['problema'] = $linkImg."&nbsp;".$row['problema'];
    $nestedData['contato'] = "<b>" . $row['contato'] . "</b><br/>" . $row['telefone'];
    $nestedData['departamento'] = "<b>" . $row['setor'] . "</b><br/>" . $texto;
    $nestedData['status'] = "<b>" . $row['chamado_status'] . "</b>";
    $nestedData['tempo'] = $colTVNew;
    $nestedData['prioridade'] = "<span class='badge text-gray' style='background-color: " . $COR . "'>" . $row['pr_descricao'] . "</span>";
    $nestedData['leds'] = "<img height='20' src='" . $imgsPath . "" . $ledSlaResposta . "' title='" . TRANS('HNT_RESPONSE_LED') . "'>&nbsp;<img height='20' src='" . $imgsPath . "" . $ledSlaSolucao . "' title='" . TRANS('HNT_SOLUTION_LED') . "'>";
    
	$data[] = $nestedData;
}

/* $json_data = array(
    // "recordsTotal"    => intval( $totalData ),  // total number of records
    // "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
    "data"            => $data   // total data array
    ); */

echo json_encode($data);  // send data as json format

?>