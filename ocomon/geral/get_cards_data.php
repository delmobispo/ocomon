<?php session_start();
/*                        Copyright 2020 Flávio Ribeiro

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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

/* if (!isset($_POST['numero'])) {
    exit();
} */

if (isset($_POST)){
    $post = $_POST;
}


$hoje = date('Y-m-d 00:00:00');
$mes = date('Y-m-01 00:00:00');

$config = getConfig($conn);
$percLimit = $config['conf_sla_tolerance']; 

$totalEmAberto = 0;

/* Total de chamados em aberto no sistema */
$sqlTotalEmAberto = "SELECT count(*) AS total FROM ocorrencias, status WHERE status.stat_painel not in (3) AND ocorrencias.status = status.stat_id AND ocorrencias.oco_scheduled = 0 ";
try {
    $res = $conn->query($sqlTotalEmAberto);
}
catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    $erro = true;
}
$totalEmAberto = $res->fetch()['total'];
/* final do total em aberto */



/* Chamado mais antigo em aberto */
$sql = "SELECT oco_real_open_date as data, numero FROM ocorrencias, status WHERE status.stat_painel not in (3) AND ocorrencias.status = status.stat_id AND oco_real_open_date = (SELECT min(oco_real_open_date) FROM ocorrencias, status WHERE status.stat_painel not in (3) AND ocorrencias.status = status.stat_id)";
try {
    $res = $conn->query($sql);
}
catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    $erro = true;
}
$rowOlder = $res->fetch();
$olderTicket = $rowOlder['numero'];
$olderAge = absoluteTime($rowOlder['data'], date('Y-m-d H:i:s'))['inTime'];
/* Final do chamado mais antigo em aberto */

/* Chamado mais recente em aberto */
$sql = "SELECT oco_real_open_date as data, numero FROM ocorrencias, status WHERE status.stat_painel not in (3) AND ocorrencias.status = status.stat_id AND oco_real_open_date = (SELECT max(oco_real_open_date) FROM ocorrencias, status WHERE status.stat_painel not in (3) AND ocorrencias.status = status.stat_id) ";
try {
    $res = $conn->query($sql);
}
catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    $erro = true;
}
$rowNewer = $res->fetch();
$newerTicket = $rowNewer['numero'];
$newerAge = absoluteTime($rowNewer['data'], date('Y-m-d H:i:s'))['inTime'];
/* Final do chamado mais recente em aberto */



/* Abertos na data corrente */
$sqlOpenToday = "SELECT count(*) AS total FROM ocorrencias WHERE oco_real_open_date >= '". $hoje ."' ";
try {
    $resultOpenToday = $conn->query($sqlOpenToday);
} catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    return false;
}
$abertosHoje = $resultOpenToday->fetch()['total'];

/* Abertos no mês corrente */
$sqlOpenMonth = "SELECT count(*) AS total FROM ocorrencias WHERE oco_real_open_date >= '". $mes ."' ";
try {
    $resultOpenMonth = $conn->query($sqlOpenMonth);
} catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    return false;
}
$abertosMes = $resultOpenMonth->fetch()['total'];

/* Fechados na data corrente */
$sqlCloseToday = "SELECT count(*) AS total FROM ocorrencias WHERE data_fechamento >= '". $hoje ."' ";
try {
    $resultCloseToday = $conn->query($sqlCloseToday);
} catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    return false;
}
$fechadosHoje = $resultCloseToday->fetch()['total'];

/* Fechados no mês corrente */
$sqlCloseMonth = "SELECT count(*) AS total FROM ocorrencias WHERE data_fechamento >= '". $mes ."' ";
try {
    $resultCloseMonth = $conn->query($sqlCloseMonth);
} catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    return false;
}
$fechadosMes = $resultCloseMonth->fetch()['total'];


/* Modificar para pegar todas as ocorrências em status vinculados aos operadores - painel superior */
$sqlEmProgresso = "SELECT count(*) AS total FROM ocorrencias, status WHERE ocorrencias.status NOT IN (1, 4, 12) AND status.stat_painel in (1) AND ocorrencias.status = status.stat_id AND ocorrencias.oco_scheduled = 0";
try {
    $resultEmProgresso = $conn->query($sqlEmProgresso);
} catch (Exception $e) {
    echo 'Erro: ', $e->getMessage(), "<br/>";
    // return false;
}
$emProgresso = $resultEmProgresso->fetch()['total'];

$percEmProgresso = 0;
if ($totalEmAberto) {
    $percEmProgresso = round($emProgresso * 100 / $totalEmAberto, 2);
}


/* Chamados sem resposta */
$sqlSemResposta = "SELECT count(*) AS total FROM ocorrencias WHERE data_atendimento IS NULL ";
try {
    $resultSemResposta = $conn->query($sqlSemResposta);
} catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    return false;
}
$semResposta = $resultSemResposta->fetch()['total'];
$percSemResposta = 0;
if ($totalEmAberto) {
    $percSemResposta = round($semResposta * 100 / $totalEmAberto, 2);
}


/* Agendados */
$sqlAgendados = "SELECT count(*) as total FROM ocorrencias WHERE oco_scheduled = 1 ";
$resultAgendados = $conn->query($sqlAgendados);
$agendados = $resultAgendados->fetch()['total'];

/* Fila aberta */
$sqlFilaGeral = "SELECT count(*) as total FROM ocorrencias o, status s WHERE o.status = s.stat_id AND s.stat_painel in (2) AND o.oco_scheduled = 0";
$resultFilaGeral = $conn->query($sqlFilaGeral);
$filaGeral = $resultFilaGeral->fetch()['total'];
$percFilaGeral = 0;
if ($totalEmAberto) {
    $percFilaGeral = round($filaGeral * 100 / $totalEmAberto, 2);
}



/* Busca geral de ocorrencias em aberto para os cálculos de tempos de resposta  */
$countResponseUndefined = 0;
$countResponseGreen = 0;
$countResponseYellow = 0;
$countResponseRed = 0;

$countSolutionUndefined = 0;
$countSolutionGreen = 0;
$countSolutionYellow = 0;
$countSolutionRed = 0;

$absoluteReponseTime = 0;
$absoluteSolutionTime = 0;
$filteredResponseTime = 0;
$filteredSolutionTime = 0;

$frozenByStatus = 0;
$frozenByWorktime = 0;

$percResponseUndefined = 0;
$percResponseGreen = 0;
$percResponseYellow = 0;
$percResponseRed = 0;
$percSolutionUndefined = 0;
$percSolutionGreen = 0;
$percSolutionYellow = 0;
$percSolutionRed = 0;
$avgAbsoluteResponseTime = 0;
$avgAbsoluteSolutionTime = 0;
$avgFilteredResponseTime = 0;
$avgFilteredSolutionTime = 0;

$sql = $QRY["ocorrencias_full_ini"] . " WHERE  s.stat_painel not in (3)"; /* apenas abertos */
$res = $conn->query($sql);
$countRecords = $res->rowCount();
foreach ($res->fetchAll() as $row) {

    $referenceDate = (!empty($row['oco_real_open_date']) ? $row['oco_real_open_date'] : $row['data_abertura']);
    $dataAtendimento = $row['data_atendimento']; //data da primeira resposta ao chamado
    $dataFechamento = $row['data_fechamento'];

    /* MÉTODOS PARA O CÁLCULO DE TEMPO VÁLIDO DE RESPOSTA E SOLUÇÃO */
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
    if (isTicketFrozen($conn, $row['numero'])) {
        /* Pausado em função do status */
        $frozenByStatus ++;
    } elseif (!$isRunning) {
        /* Pausado em função da jornada de trabalho */
        $frozenByWorktime ++;
    }

    
    if (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 1) {
        $countResponseUndefined ++;
    } elseif (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 2) {
        $countResponseGreen ++;
    } elseif (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 3) {
        $countResponseYellow ++;
    } elseif (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 4) {
        $countResponseRed ++;
    }

    if (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 1) {
        $countSolutionUndefined ++;
    } elseif (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 2) {
        $countSolutionGreen ++;
    } elseif (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 3) {
        $countSolutionYellow ++;
    } elseif (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 4) {
        $countSolutionRed ++;
    }

    $absoluteReponseTime += absoluteTime($referenceDate, (!empty($dataAtendimento) ? $dataAtendimento : date('Y-m-d H:i:s')))['inSeconds'];
    $absoluteSolutionTime += absoluteTime($referenceDate, date('Y-m-d H:i:s'))['inSeconds'];

    $filteredResponseTime += $ticketTimeInfo['response']['seconds'];
    $filteredSolutionTime += $ticketTimeInfo['solution']['seconds'];

}
/* Variáveis sobre os chamados em aberto no sistema */
if ($countRecords) {
    $percResponseUndefined = round($countResponseUndefined * 100 / $countRecords,2);
    $percResponseGreen = round($countResponseGreen * 100 / $countRecords,2);
    $percResponseYellow = round($countResponseYellow * 100 / $countRecords,2);
    $percResponseRed = round($countResponseRed * 100 / $countRecords,2);
    $percSolutionUndefined = round($countSolutionUndefined * 100 / $countRecords,2);
    $percSolutionGreen = round($countSolutionGreen * 100 / $countRecords,2);
    $percSolutionYellow = round($countSolutionYellow * 100 / $countRecords,2);
    $percSolutionRed = round($countSolutionRed * 100 / $countRecords,2);
    $avgAbsoluteResponseTime = secToTime(floor($absoluteReponseTime/$countRecords))['verbose'];
    $avgAbsoluteSolutionTime = secToTime(floor($absoluteSolutionTime/$countRecords))['verbose'];
    $avgFilteredResponseTime = secToTime(floor($filteredResponseTime/$countRecords))['verbose'];
    $avgFilteredSolutionTime = secToTime(floor($filteredSolutionTime/$countRecords))['verbose'];
}

/* final das variáveis sobre os chamados em aberto no sistema */


$data = array();

/* info dos chamados em aberto */
$data['abertosHoje'] = $abertosHoje;
$data['fechadosHoje'] = $fechadosHoje;
$data['abertosMes'] = $abertosMes;
$data['fechadosMes'] = $fechadosMes;
$data['emProgresso'] = $emProgresso;
$data['percEmProgresso'] = $percEmProgresso;
$data['olderTicket'] = $olderTicket;
$data['olderAge'] = truncateTime($olderAge, 2);
$data['newerTicket'] = $newerTicket;
$data['newerAge'] = truncateTime($newerAge, 2);

$data['semResposta'] = $semResposta;
$data['percSemResposta'] = $percSemResposta;
$data['agendados'] = $agendados;
$data['filaGeral'] = $filaGeral;
$data['percFilaGeral'] = $percFilaGeral;
$data['frozenByStatus'] = $frozenByStatus;
$data['frozenByWorktime'] = $frozenByWorktime;

/* SLA de Resposta */
$data['percResponseUndefined'] = $percResponseUndefined;
$data['percResponseGreen'] = $percResponseGreen;
$data['percResponseYellow'] = $percResponseYellow;
$data['percResponseRed'] = $percResponseRed;
/* SLA de Solução */
$data['percSolutionUndefined'] = $percSolutionUndefined;
$data['percSolutionGreen'] = $percSolutionGreen;
$data['percSolutionYellow'] = $percSolutionYellow;
$data['percSolutionRed'] = $percSolutionRed;
/* Média absoluta de resposta e solução para chamados em aberto */
$data['openAvgAbsResponseTime'] = truncateTime($avgAbsoluteResponseTime, 2);
$data['openAvgAbsSolutionTime'] = truncateTime($avgAbsoluteSolutionTime, 2);
/* Média filtrada (considera o tempo filtrado) de resposta e solução para chamados em aberto */
$data['openAvgFilteredResponseTime'] = truncateTime($avgFilteredResponseTime, 2);
$data['openAvgFilteredSolutionTime'] = truncateTime($avgFilteredSolutionTime, 2);
/* Final das info dos chamados em aberto */







/* Busca geral de ocorrencias concluúdas para os cálculos de tempos de solução  */
$countResponseUndefined = 0;
$countResponseGreen = 0;
$countResponseYellow = 0;
$countResponseRed = 0;

$countSolutionUndefined = 0;
$countSolutionGreen = 0;
$countSolutionYellow = 0;
$countSolutionRed = 0;

$absoluteReponseTime = 0;
$absoluteSolutionTime = 0;

$percResponseUndefined = 0;
$percResponseGreen = 0;
$percResponseYellow = 0;
$percResponseRed = 0;
$percSolutionUndefined = 0;
$percSolutionGreen = 0;
$percSolutionYellow = 0;
$percSolutionRed = 0;
$avgAbsoluteResponseTime = 0;
$avgAbsoluteSolutionTime = 0;


$sql = $QRY["ocorrencias_full_ini"] . " WHERE  o.status = 4 AND o.data_fechamento >= '" . date('Y-m-01 00:00:00') . "' "; /* apenas encerrados no mês corrente */
$res = $conn->query($sql);
$countRecords = $res->rowCount();
foreach ($res->fetchAll() as $row) {

    $referenceDate = (!empty($row['oco_real_open_date']) ? $row['oco_real_open_date'] : $row['data_abertura']);
    $dataAtendimento = $row['data_atendimento']; //data da primeira resposta ao chamado
    $dataFechamento = $row['data_fechamento'];

    /* MÉTODOS PARA O CÁLCULO DE TEMPO VÁLIDO DE RESPOSTA E SOLUÇÃO */
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

    
    if (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 1) {
        $countResponseUndefined ++;
    } elseif (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 2) {
        $countResponseGreen ++;
    } elseif (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 3) {
        $countResponseYellow ++;
    } elseif (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 4) {
        $countResponseRed ++;
    }

    if (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 1) {
        $countSolutionUndefined ++;
    } elseif (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 2) {
        $countSolutionGreen ++;
    } elseif (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 3) {
        $countSolutionYellow ++;
    } elseif (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 4) {
        $countSolutionRed ++;
    }

    $absoluteReponseTime += absoluteTime($referenceDate, (!empty($dataAtendimento) ? $dataAtendimento : date('Y-m-d H:i:s')))['inSeconds'];

    $absoluteSolutionTime += absoluteTime($referenceDate, date('Y-m-d H:i:s'))['inSeconds'];

}
/* Variáveis sobre os chamados encerrados no mês corrente */
if ($countRecords) {
    $percResponseUndefined = round($countResponseUndefined * 100 / $countRecords,2);
    $percResponseGreen = round($countResponseGreen * 100 / $countRecords,2);
    $percResponseYellow = round($countResponseYellow * 100 / $countRecords,2);
    $percResponseRed = round($countResponseRed * 100 / $countRecords,2);
    $percSolutionUndefined = round($countSolutionUndefined * 100 / $countRecords,2);
    $percSolutionGreen = round($countSolutionGreen * 100 / $countRecords,2);
    $percSolutionYellow = round($countSolutionYellow * 100 / $countRecords,2);
    $percSolutionRed = round($countSolutionRed * 100 / $countRecords,2);
    $avgAbsoluteResponseTime = secToTime(floor($absoluteReponseTime/$countRecords))['verbose'];
    $avgAbsoluteSolutionTime = secToTime(floor($absoluteSolutionTime/$countRecords))['verbose'];
}

/* final das variáveis sobre os chamados encerrados no mês corrente */



/* SLA de Resposta - chamados encerrados no mês corrente */
$data['percDoneResponseUndefined'] = $percResponseUndefined;
$data['percDoneResponseGreen'] = $percResponseGreen;
$data['percDoneResponseYellow'] = $percResponseYellow;
$data['percDoneResponseRed'] = $percResponseRed;
/* SLA de Solução - encerrados */
$data['percDoneSolutionUndefined'] = $percSolutionUndefined;
$data['percDoneSolutionGreen'] = $percSolutionGreen;
$data['percDoneSolutionYellow'] = $percSolutionYellow;
$data['percDoneSolutionRed'] = $percSolutionRed;
/* Média absoluta de resposta e solução para chamados encerrados */
$data['doneAvgAbsResponseTime'] = $avgAbsoluteResponseTime;
$data['doneAvgAbsSolutionTime'] = $avgAbsoluteSolutionTime;
/* Final das info dos chamados encerrados no mês corrente */








/* Busca geral de ocorrencias concluídas na data atual para os cálculos de tempos de solução  */
$countResponseUndefined = 0;
$countResponseGreen = 0;
$countResponseYellow = 0;
$countResponseRed = 0;

$countSolutionUndefined = 0;
$countSolutionGreen = 0;
$countSolutionYellow = 0;
$countSolutionRed = 0;

$absoluteReponseTime = 0;
$absoluteSolutionTime = 0;

$percResponseUndefined = 0;
$percResponseGreen = 0;
$percResponseYellow = 0;
$percResponseRed = 0;
$percSolutionUndefined = 0;
$percSolutionGreen = 0;
$percSolutionYellow = 0;
$percSolutionRed = 0;
$avgAbsoluteResponseTime = 0;
$avgAbsoluteSolutionTime = 0;


$sql = $QRY["ocorrencias_full_ini"] . " WHERE  o.status = 4 AND o.data_fechamento >= '" . date('Y-m-d 00:00:00') . "' "; /* apenas encerrados na data corrente corrente */
$res = $conn->query($sql);
$countRecords = $res->rowCount();
foreach ($res->fetchAll() as $row) {

    $referenceDate = (!empty($row['oco_real_open_date']) ? $row['oco_real_open_date'] : $row['data_abertura']);
    $dataAtendimento = $row['data_atendimento']; //data da primeira resposta ao chamado
    $dataFechamento = $row['data_fechamento'];

    /* MÉTODOS PARA O CÁLCULO DE TEMPO VÁLIDO DE RESPOSTA E SOLUÇÃO */
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

    
    if (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 1) {
        $countResponseUndefined ++;
    } elseif (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 2) {
        $countResponseGreen ++;
    } elseif (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 3) {
        $countResponseYellow ++;
    } elseif (getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']) == 4) {
        $countResponseRed ++;
    }

    if (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 1) {
        $countSolutionUndefined ++;
    } elseif (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 2) {
        $countSolutionGreen ++;
    } elseif (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 3) {
        $countSolutionYellow ++;
    } elseif (getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']) == 4) {
        $countSolutionRed ++;
    }

    $absoluteReponseTime += absoluteTime($referenceDate, (!empty($dataAtendimento) ? $dataAtendimento : date('Y-m-d H:i:s')))['inSeconds'];

    $absoluteSolutionTime += absoluteTime($referenceDate, date('Y-m-d H:i:s'))['inSeconds'];

}
/* Variáveis sobre os chamados encerrados na data corrente */
if ($countRecords) {
    $percResponseUndefined = round($countResponseUndefined * 100 / $countRecords,2);
    $percResponseGreen = round($countResponseGreen * 100 / $countRecords,2);
    $percResponseYellow = round($countResponseYellow * 100 / $countRecords,2);
    $percResponseRed = round($countResponseRed * 100 / $countRecords,2);
    $percSolutionUndefined = round($countSolutionUndefined * 100 / $countRecords,2);
    $percSolutionGreen = round($countSolutionGreen * 100 / $countRecords,2);
    $percSolutionYellow = round($countSolutionYellow * 100 / $countRecords,2);
    $percSolutionRed = round($countSolutionRed * 100 / $countRecords,2);
    $avgAbsoluteResponseTime = secToTime(floor($absoluteReponseTime/$countRecords))['verbose'];
    $avgAbsoluteSolutionTime = secToTime(floor($absoluteSolutionTime/$countRecords))['verbose'];
}

/* final das variáveis sobre os chamados encerrados na data corrente */



/* SLA de Resposta - chamados encerrados na data corrente */
$data['percDoneTodayResponseUndefined'] = $percResponseUndefined;
$data['percDoneTodayResponseGreen'] = $percResponseGreen;
$data['percDoneTodayResponseYellow'] = $percResponseYellow;
$data['percDoneTodayResponseRed'] = $percResponseRed;
/* SLA de Solução - encerrados */
$data['percDoneTodaySolutionUndefined'] = $percSolutionUndefined;
$data['percDoneTodaySolutionGreen'] = $percSolutionGreen;
$data['percDoneTodaySolutionYellow'] = $percSolutionYellow;
$data['percDoneTodaySolutionRed'] = $percSolutionRed;
/* Média absoluta de resposta e solução para chamados encerrados */
$data['doneTodayAvgAbsResponseTime'] = $avgAbsoluteResponseTime;
$data['doneTodayAvgAbsSolutionTime'] = $avgAbsoluteSolutionTime;
/* Final das info dos chamados encerrados na data corrente */







echo json_encode($data);
