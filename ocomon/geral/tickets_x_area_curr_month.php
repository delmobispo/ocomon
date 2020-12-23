<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}

$conn = ConnectPDO::getInstance();

$d_ini_completa = date("Y-m-01 00:00:00");
$d_fim_completa = date("Y-m-d H:i:s");
$totalAbertos = 0;
$totalFechados = 0;
$totalCancelados = 0;
$i = 0;

$data = array();

$query_areas = "SELECT sis_id, sistema FROM sistemas WHERE sis_status not in (0) AND sis_atende = 1 ORDER by sistema";
$query_areas = $conn->query($query_areas);

foreach ($query_areas->fetchAll(PDO::FETCH_ASSOC) as $row) {
    

    $query_ab_sw = "SELECT count(*) AS abertos, s.sistema AS area
                        FROM ocorrencias AS o, sistemas AS s
                        WHERE o.sistema = s.sis_id AND o.oco_real_open_date >= '" . $d_ini_completa . "' AND
                        o.oco_real_open_date <= '" . $d_fim_completa . "' and s.sis_id in (" . $row['sis_id'] . ") 
                        GROUP by area";
    $query_ab_sw = $conn->query($query_ab_sw);
    $totalAbertos += $query_ab_sw->fetch(PDO::FETCH_ASSOC)['abertos'];
    

    $query_fe_sw = "SELECT count(*) AS fechados, s.sistema AS area, s.sis_id
                        FROM ocorrencias AS o, sistemas AS s
                        WHERE o.sistema = s.sis_id AND o.data_fechamento >= '" . $d_ini_completa . "' AND
                        o.data_fechamento <= '" . $d_fim_completa . "' and s.sis_id in (" . $row['sis_id'] . ")  
                        GROUP by area";
    $query_fe_sw = $conn->query($query_fe_sw);
    $totalFechados += $query_fe_sw->fetch(PDO::FETCH_ASSOC)['fechados'];

    $query_ca_sw = "SELECT count(*) AS cancelados, s.sistema AS area
                        FROM ocorrencias AS o, sistemas AS s
                        WHERE o.sistema = s.sis_id AND o.oco_real_open_date >= '" . $d_ini_completa . "' AND
                        o.oco_real_open_date <= '" . $d_fim_completa . "' and s.sis_id in (" . $row['sis_id'] . ") and
                        o.status in (12) 
                        GROUP by area";
    $query_ca_sw = $conn->query($query_ca_sw);
    $totalCancelados += $query_ca_sw->fetch(PDO::FETCH_ASSOC)['cancelados'];


    $data[$i]['area'] = $row['sistema'];
    $data[$i]['abertos'] = $totalAbertos;
    $data[$i]['fechados'] = $totalFechados;
    $data[$i]['cancelados'] = $totalCancelados;

    $totalAbertos = 0;
    $totalFechados = 0;
    $totalCancelados = 0;

    $i++;
}

$data[]['chart_title'] = TRANS('TICKETS_BY_AREA_CURRENT_MONTH', '', 1);

// IMPORTANT, output to json
echo json_encode($data);

?>
