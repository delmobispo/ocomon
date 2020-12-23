<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || $_SESSION['s_nivel'] != 1) {
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


$query = "SELECT s.sistema AS area,  p.problema as problema, count(*)  AS quantidade
            FROM ocorrencias AS o, localizacao AS l, sistemas AS s, problemas as p
            WHERE o.sistema = s.sis_id AND o.local = l.loc_id AND o.problema = p.prob_id";
                    
$query .= " AND o.data_fechamento >= '" . $d_ini_completa . "' AND o.data_fechamento <= '" . $d_fim_completa . "' AND
            o.data_atendimento is not null
                GROUP  BY p.problema, s.sistema order by area, quantidade desc";


$query = $conn->query($query);

foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {

    // $data[] = $row;
    $data[$i]['area'] = $row['area'];
    $data[$i][$row['problema']] = $row['quantidade'];

    // $data[$i]['area'] = $row['sistema'];
    // $data[$i]['abertos'] = $totalAbertos;
    // $data[$i]['fechados'] = $totalFechados;
    // $data[$i]['cancelados'] = $totalCancelados;


    $i++;
}

// IMPORTANT, output to json
echo json_encode($data);

?>
