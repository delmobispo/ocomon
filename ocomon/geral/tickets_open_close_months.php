<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}
$conn = ConnectPDO::getInstance();

$dates = [];
$datesBegin = [];
$datesEnd = [];
$months = [];
$tickets = [];
$areas = [];
$open = [];
$close = [];
$data = [];

// Meses anteriores
$dates = getMonthRangesUpToNOw('P3M');
$datesBegin = $dates['ini'];
$datesEnd = $dates['end'];
$months = $dates['mLabel'];

/* PRIMEIRO BUSCO AS AREAS ENVOLVIDAS NA CONSULTA */
// $sql = "SELECT sis_id, sistema FROM sistemas WHERE sis_atende = 1 ";
// $result = $conn->query($sql);
// foreach ($result->fetchAll() as $row) {
    $i = 0;
    foreach ($datesBegin as $dateStart) {
        /* Em cada intervalo de tempo busco os totais de cada área */

        // $sqlEach = "SELECT count(*) AS total, s.sistema FROM ocorrencias o, sistemas s WHERE s.sis_id = o.sistema AND s.sis_id = " . $row['sis_id'] . " AND o.oco_real_open_date >= '" .  $dateStart  . "' AND o.oco_real_open_date <= '" .  $datesEnd[$i]  . "' ";
        // $resultEach = $conn->query($sqlEach);


        $sqlEachOpen = "SELECT count(*) AS abertos
                        FROM ocorrencias AS o
                        WHERE o.oco_real_open_date >= '" . $dateStart . "' AND
                        o.oco_real_open_date <= '" . $datesEnd[$i] . "' ";
        $resultEachOpen = $conn->query($sqlEachOpen);
        $rowEachOpen = $resultEachOpen->fetch();

        $tickets['abertos'][] = $rowEachOpen['abertos'];

        $sqlEachClose = "SELECT count(*) AS fechados 
                        FROM ocorrencias AS o
                        WHERE o.data_fechamento >= '" . $dateStart . "' AND
                        o.data_fechamento <= '" . $datesEnd[$i] . "' ";
        $resultEachClose = $conn->query($sqlEachClose);
        $rowEachClose = $resultEachClose->fetch();

        $tickets['fechados'][] = $rowEachClose['fechados'];

        $meses[] = $months[$i];

        // }
        $i++;
    }
// }

/* Ajusto os arrays de labels para não ter repetidos */
$meses = array_unique($meses);
// $areas = array_unique($areas);

/* Separo o conteúdo para organizar o JSON */
// $data['areas'] = $areas;
$data['months'] = $meses;
$data['totais'] = $tickets;
$data['chart_title'] = TRANS('TICKETS_LAST_MONTHS', '', 1);
// var_dump($data);

echo json_encode($data);

?>
