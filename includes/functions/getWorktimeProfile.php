<?php /*                        Copyright 2020 Flávio Ribeiro

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

// require_once (__DIR__ . "/" . "../include_basics_only.php");
// require_once (__DIR__ . "/" . "../classes/ConnectPDO.php");
// use includes\classes\ConnectPDO;

// $conn = ConnectPDO::getInstance();

function getWorktimeProfile($conn, $profile_id) {
        
    if (empty($profile_id)) {
        //NESSE CASO UTILIZO O CODIGO DO PERFIL MARCADO COMO PADRAO
        $sql = "SELECT id FROM worktime_profiles WHERE is_default = 1";
        $result = $conn->query($sql);
        $profile_id = $result->fetch()['id'];
    } 

    $data = array();

    $sql = "SELECT * FROM worktime_profiles WHERE id = {$profile_id} ";
    $sql = $conn->query($sql);

    foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {

        $data['247'] = ($row['247'] == 1 ? (bool)true : (bool)false);

        $data['week']['iniTimeHour'] = $row['week_ini_time_hour'];
        $data['week']['iniTimeMinute'] = $row['week_ini_time_minute'];
        $data['week']['endTimeHour'] = $row['week_end_time_hour'];
        $data['week']['endTimeMinute'] = $row['week_end_time_minute'];
        $data['week']['dayFullWorkTime'] = (int)$row['week_day_full_worktime'];
        $data['week']['dayFullWorkTimeInSecs'] = $row['week_day_full_worktime'] * 60;

        $data['sat']['iniTimeHour'] = $row['sat_ini_time_hour'];
        $data['sat']['iniTimeMinute'] = $row['sat_ini_time_minute'];
        $data['sat']['endTimeHour'] = $row['sat_end_time_hour'];
        $data['sat']['endTimeMinute'] = $row['sat_end_time_minute'];
        $data['sat']['dayFullWorkTime'] = (int)$row['sat_day_full_worktime'];
        $data['sat']['dayFullWorkTimeInSecs'] = $row['sat_day_full_worktime'] * 60;

        $data['sun']['iniTimeHour'] = $row['sun_ini_time_hour'];
        $data['sun']['iniTimeMinute'] = $row['sun_ini_time_minute'];
        $data['sun']['endTimeHour'] = $row['sun_end_time_hour'];
        $data['sun']['endTimeMinute'] = $row['sun_end_time_minute'];
        $data['sun']['dayFullWorkTime'] = (int)$row['sun_day_full_worktime'];
        $data['sun']['dayFullWorkTimeInSecs'] = $row['sun_day_full_worktime'] * 60;
        

        $data['off']['iniTimeHour'] = $row['off_ini_time_hour'];
        $data['off']['iniTimeMinute'] = $row['off_ini_time_minute'];
        $data['off']['endTimeHour'] = $row['off_end_time_hour'];
        $data['off']['endTimeMinute'] = $row['off_end_time_minute'];
        $data['off']['dayFullWorkTime'] = (int)$row['off_day_full_worktime'];
        $data['off']['dayFullWorkTimeInSecs'] = $row['off_day_full_worktime'] * 60;

        // $data['workHolidays'] = ($row['work_holidays'] == 1 ? (bool)true : (bool)false);
    }

    return (array)$data;

}

/**
 * RETORNA O ARRAY DE FERIADOS QUE SERÁ UTILIZADO PARA O CÁLCULO DO TEMPO FILTRADO
 * $conn: conexão
 */
function getHolidays($conn) {
        
    $data = array();
    $year = date('Y');
    $holidayYear = (int) $year;
    /* Para os casos de feriados fixos, o array adiciona as datas para os 5 anos anteriores do ano atual e tamem para os 5 anos seguintes */
    $yearBase = $holidayYear - 5; 
    $yearLimit = $holidayYear + 5;

    $sql = "SELECT 
                date_format(data_feriado, '%Y') ano, 
                date_format(data_feriado, '%m' ) mes, 
                date_format(data_feriado, '%d' ) dia, 
                fixo_feriado fixo
            FROM feriados ORDER BY data_feriado ";
    $sql = $conn->query($sql);

    foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {

        if ($row['fixo']) {
            for ($i = $yearBase; $i <= $yearLimit; $i++) {
                $data[] = $i . '-' . $row['mes'] . '-' . $row['dia'];
            }
        } else 
            $data[] = $row['ano'] . '-' . $row['mes'] . '-' . $row['dia'];
        
    }
    return (array)$data;
}



function daysFullWorkTime($startTime, $endTime, $fullTime = false){
    
    //PRESEATS - 24/7
    if ($fullTime === true){
        return 1440;
    }

    $startTime = new DateTime($startTime);
    $endTime = new DateTime($endTime);

    $diff = $startTime->diff($endTime);

    $min = $diff->i;
    $hour = $diff->h * 60;

    /** The only case when the result should return 1439 is from 00:00 to 23h59, but this represents a full day,
     * so its changed to 1440 for the best representation of a full day
    */
    return ((($min + $hour == 1439) ? 1440 : $min + $hour)); //in minutes
}



/**
 * Retorna o tempo absoluto entre duas datas
 * Formato do retorno: x anos x meses x dias x horas x minutos x segundos
 * $startTime: data de início do período
 * $endTime: data de fim do feríodo
 */
function absoluteTime (string $startTime, string $endTime) {
    
    $time1 = strtotime($startTime);
    $time2 = strtotime($endTime);
    $inSeconds = $time2 - $time1;


    $startTime = new DateTime($startTime);
    $endTime = new DateTime($endTime);

    $diff = $startTime->diff($endTime);
    
    $years = ($diff->y ? $diff->y : '');
    $months = ($diff->m ? $diff->m : '');
    $days = ($diff->d ? $diff->d : '');
    $hours = ($diff->h ? $diff->h : '');
    $minutes = ($diff->i ? $diff->i : '');
    $seconds = ($diff->s ? $diff->s : '');

    $inTime = "";
    
    /* $inTime = (!empty($years) && (int)$years > 1 ? $years . " anos " : (!empty($years) ? $years . " ano " : ''));
    $inTime .= (!empty($months) && (int)$months > 1 ? $months . " meses " : (!empty($months) ? $months . " mês " : ''));
    $inTime .= (!empty($days) && (int)$days > 1 ? $days . " dias " : (!empty($days) ? $days . " dia " : ''));
    $inTime .= (!empty($hours) && (int)$hours > 1 ? $hours . " horas " : (!empty($hours) ? $hours . " hora " : ''));
    $inTime .= (!empty($minutes) && (int)$minutes > 1 ? $minutes . " minutos " : (!empty($minutes) ? $minutes . " minuto " : ''));
    $inTime .= (!empty($seconds) && (int)$seconds > 1 ? $seconds . " segundos " : (!empty($seconds) ? $seconds . " segundo " : '')); */

    $inTime = (!empty($years) ? $years . "a " : '');
    $inTime .= (!empty($months) ? $months . "m " : '');
    $inTime .= (!empty($days) ? $days . "d " : '');
    $inTime .= (!empty($hours) ? $hours . "h " : '');
    $inTime .= (!empty($minutes) ? $minutes . "m " : '');
    $inTime .= (!empty($seconds) ? $seconds . "s " : '');

    $output = [];
    $output['inTime'] = trim($inTime);
    $output['inSeconds'] = $inSeconds;

    // return trim($inTime);
    return $output;
}

/** 
 * Retorna o código do perfil de jornada de trabalho
 * $conn: Conexão
 * $conf: valor da chave conf_wt_areas na tabela config - sessão-> $_SESSION['s_wt_areas']
 * $ticket: número do chamado
 * $specific: ignora os demais parãmetros e retorna diretamente o codigo de perfil passado
 *              para os casos de determinar o perfil de jornada diretamente no chamado
*/
function getProfileCod ($conn, $conf, $ticket, $specific = '') {

    
    if (empty($specific)) {
        /* pegar o usuário que abriu o chamado */
        $sql = "SELECT sistema, aberto_por FROM ocorrencias WHERE numero = {$ticket} ";

        try {
            $result = $conn->query($sql);
        }
        catch (Exception $e) {
            // echo 'Erro: ', $e->getMessage(), "<br/>";
            return false;
        }
        $row = $result->fetch();

        if ($conf == 1) { //ÁREA DE ORIGEM DO CHAMADO - NESSE CASO, BASEADO NO USUÁRIO QUE ABRIU O CHAMADO
            $sql = "SELECT u.AREA, s.sis_id, w.id  FROM usuarios u, sistemas s, worktime_profiles w 
                WHERE user_id = ".$row['aberto_por']." AND u.AREA = s.sis_id AND s.sis_wt_profile = w.id ";
            try {
                $result = $conn->query($sql);
            }
            catch (Exception $e) {
                // echo 'Erro: ', $e->getMessage(), "<br/>";
                return false;
                
            }
            
            $rowConf1 = $result->fetch();
            return $rowConf1['id'];
            
        } elseif ($conf == 2) { //ÁREA DE ATENDIMENTO DO CHAMADO
            
            if (empty($row['sistema'])) {
                /* Se o chamado não possuir área, então utilizo o perfil definido como padrão */
                $sql = "SELECT id FROM worktime_profiles WHERE is_default = 1 ";
                $result = $conn->query($sql);
                $rowProfile = $result->fetch();
                return $rowProfile['id'];
            }
            
            $sql = "SELECT sis_wt_profile FROM sistemas WHERE sis_id = " . $row['sistema'] . " ";
            try {
                $result = $conn->query($sql);
            }
            catch (Exception $e) {
                // echo 'Erro: ', $e->getMessage(), "<br/>";
                return false;
            }
            
            $rowConf2 = $result->fetch();
            return $rowConf2['sis_wt_profile'];
        }
    }
    return (int)$specific;
}


/**
 * Retorna se o código de status informado está configurado para parada de relógio
 * $conn: conexão
 * $statusId: código do status do chamado
 */
function isStatusFreeze ($conn, $statusId) {
    if (empty($statusId))
        return 0;
    $sql = "SELECT stat_time_freeze FROM status WHERE stat_id = {$statusId} ";
    $result = $conn->query($sql);
    $row = $result->fetch();
    return $row['stat_time_freeze'];
}

/**
 * Retorna se o chamado está com o relógio parado
 */
function isTicketFrozen ($conn, $ticket) {
    $sqlTkt = "SELECT * FROM `tickets_stages` 
                WHERE ticket = {$ticket} AND id = (SELECT max(id) FROM tickets_stages WHERE ticket = {$ticket}) ";
    $resultTkt = $conn->query($sqlTkt);
    
    if ($resultTkt->rowCount()) {
        $row = $resultTkt->fetch();
        return isStatusFreeze($conn, $row['status_id']);
    }

    return false;
}


/**
 * Retorna codigos de identificacao sobre o resultado de SLA para o chamado
 * 1: sem definicao | 2: Dentro do SLA | 3: Dentro da tolerância excendente | 4: Excedeu
 * $definedSLA: sla definido para o chamado (em minutos)
 * $ticketLifetime: tempo de vida do chamado em segundos (retornado pela classe Worktime)
 * $tolerance: percentual de tolerância sobre os tempos definidos para o SLA
 */
function getSlaResult ($ticketLifetime, $tolerance, $definedSLA = '') {

    if (empty($definedSLA)) {
        return 1; /* Não identificado */
    }

    if ($ticketLifetime <= (($definedSLA * 60))) {
        return 2; /* Dentro do SLA */
    }

    if ($ticketLifetime <= (($definedSLA * 60) + (($definedSLA * 60) * $tolerance / 100))) {
        return 3; /* Dentro da tolerância excendente */
    }

    return 4; /* Excedeu o SLA */
}

/**
 * Retorna os indicadores de SLA
 * $definedSLA: sla definido para o chamado (em minutos)
 * $ticketLifetime: tempo de vida do chamado em segundos (retornado pela classe Worktime)
 * $tolerance: percentual de tolerância sobre os tempos definidos para o SLA
 */
function showLedSLA ($ticketLifetime, $tolerance, $definedSLA = '') {

    if (empty($definedSLA)) {
        return 'gray-circle.svg';
    }

    if ($ticketLifetime <= (($definedSLA * 60))) {
        return 'green-circle.svg';
    }

    if ($ticketLifetime <= (($definedSLA * 60) + (($definedSLA * 60) * $tolerance / 100))) {
        return 'yellow-circle.svg';
    }

    return 'red-circle.svg';
}


/** 
 * Retorna as informações necessárias para a exibição dos tempos válidos de Resposta e Solução
 * $conn: conexão
 * $newWT: objeto Worktime para o cálculo de tempo de Solução
 * $newWTResponse: objeto Worktime para o cálculo de tempo de Resposta
 * $ticket: número do chamado
 * $openDate: data de abertura do chamado
 * $responseDate: data da responsta para o chamado
 * $closureDate: data de encerramento do chamado
 * $ticketStatus: status do chamado
 * $objWT: Objeto Worktime para checagem se o momento atual está coberto pela jornada de trabalho associada
*/
function getTicketTimeInfo ($conn, Worktime $newWT, Worktime $newWTResponse, $ticket, $openDate, $responseDate, $closureDate = '', $ticketStatus = '', Worktime $objWT = null): array
{
    $output = [];

    $sqlStages = "SELECT * FROM tickets_stages WHERE ticket = " . $ticket . " ";
    $resultStage = $conn->query($sqlStages);
    if ($resultStage->rowCount()) {
        $hasValidStage = false;
        foreach ($resultStage->fetchAll() as $rowStage) {
            /* Só considera os status que não param o relógio */
            if (!isStatusFreeze($conn, $rowStage['status_id'])) {
                $hasValidStage = true;
                $newWT->startTimer($rowStage['date_start']);
                if (!empty($rowStage['date_stop'])) {
                    $newWT->stopTimer($rowStage['date_stop']);
                } else {
                    $newWT->stopTimer(date("Y-m-d H:i:s"));
                }
            }
        }
        if (!$hasValidStage) {
            //Há registro no tickets_stages mas nenhum dispara a contagem de tempo
            $newWT->startTimer(date("Y-m-d H:i:s"));
            $newWT->stopTimer(date("Y-m-d H:i:s"));
        }
    } else {
        /* Se não encontra nenhum registro em tickets_stages então considero apenas a data de abertura
         e a data de fechamento caso exista, caso contrário, a data atual */
        $newWT->startTimer($openDate);
        if ($closureDate != '') {
            $newWT->stopTimer($closureDate);
        } elseif (isStatusFreeze($conn, $ticketStatus)) {
            if (!empty($responseDate))
                $newWT->stopTimer($responseDate); else
                $newWT->stopTimer($openDate);
        } else 
            $newWT->stopTimer(date("Y-m-d H:i:s"));
    }
    

    /* Tempo válido de RESPOSTA baseado no perfil de jornada de trabalho e nas etapas em cada status */
    // $newWTResponse = new Worktime(getWorktimeProfile($conn, getProfileCod($conn, $_SESSION['s_wt_areas'], $row['numero'])), $holidays);
    $sqlStages = "SELECT * FROM tickets_stages WHERE ticket = " . $ticket . " ORDER BY id";
    $resultStage = $conn->query($sqlStages);
    if ($resultStage->rowCount()) {
        $hasValidStage = false;
        foreach ($resultStage->fetchAll() as $rowStage) {
            /* Só considera os status que não param o relógio */
            /* Só busca registro do primeiro estágio para efeitos de tempo de resposta */
            if (!$hasValidStage) {
                if (!isStatusFreeze($conn, $rowStage['status_id'])) {
                    $hasValidStage = true;
                    $newWTResponse->startTimer($rowStage['date_start']);
                    
                    if (!empty($responseDate)) {
                        $newWTResponse->stopTimer($responseDate);
                    } else {
                        $newWTResponse->stopTimer(date("Y-m-d H:i:s"));
                    }
                }
            }
            
        }
        if (!$hasValidStage) {
            //Há registro no tickets_stages mas nenhum dispara a contagem de tempo
            $newWTResponse->startTimer(date("Y-m-d H:i:s"));
            $newWTResponse->stopTimer(date("Y-m-d H:i:s"));
        }
    } else {
        /* Se não encontra nenhum registro em tickets_stages então considero apenas a data de abertura e a data atual */
        $newWTResponse->startTimer($openDate);
        // if (!empty($row['data_atendimento'])) {
        if (!empty($responseDate)) {
            $newWTResponse->stopTimer($responseDate);
        } else {
            $newWTResponse->stopTimer(date("Y-m-d H:i:s"));
        }
    }


    /* Checar se o momento atual está dentro da cobertura da jornada de trabalho associada */
    $output['running'] = 0;
    if ($objWT != null) {

        $now = (array) new DateTime( date("Y-m-d H:i:s") );
        $now = explode(".", $now['date']); //now[0] = date part

        $before = new DateTime( date("Y-m-d H:i:s"));
        $before = $before->modify( '-1 second' ); 
		$before = (array)$before;
		$before = explode(".", $before['date']);
        
        $later = new DateTime( date("Y-m-d H:i:s"));
        $later = (array)$later->modify( '+1 second' ); 
        $later = explode(".", $later['date']);
        
        $objWT->startTimer($now[0]);
        $objWT->stopTimer($later[0]);

        if ($objWT->getSeconds() > 0) {
            $output['running'] = $objWT->getSeconds();
        } else {
            $objWT->startTimer($before[0]);
            $objWT->stopTimer($now[0]);

            if ($objWT->getSeconds() > 0) {
                $output['running'] = $objWT->getSeconds();
            }
        }
    }
    
    $output['response']['time'] = $newWTResponse->getTime();
    $output['response']['seconds'] = $newWTResponse->getSeconds();
    $output['solution']['time'] = $newWT->getTime();
    $output['solution']['seconds'] = $newWT->getSeconds();

    return $output;

}


