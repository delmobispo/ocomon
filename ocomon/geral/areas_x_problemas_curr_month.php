<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || $_SESSION['s_nivel'] != 1) {
    exit;
}

$conn = ConnectPDO::getInstance();


/* 
select count(*) qtd, sistemas.sistema, problemas.problema from ocorrencias, sistemas, problemas 
where ocorrencias.sistema = sistemas.sis_id AND ocorrencias.problema = problemas.prob_id AND ocorrencias.status = 4 
GROUP BY sistemas.sistema, problemas.problema ORDER BY sistemas.sistema, qtd desc, problemas.problema 
*/


$d_ini_completa = date("Y-m-01 00:00:00");
$d_fim_completa = date("Y-m-d H:i:s");
$totalAbertos = 0;
$totalFechados = 0;
$totalCancelados = 0;
$i = 0;
$j = 0;


$data = array();

$query_areas = "SELECT sis_id, sistema FROM sistemas WHERE sis_status not IN (0) AND sis_atende = 1 ORDER by sistema";
$query_areas = $conn->query($query_areas);

foreach ($query_areas->fetchAll(PDO::FETCH_ASSOC) as $row) {
    

    $query_all_problems = "SELECT * FROM problemas ORDER BY problema";
    $query_all_problems = $conn->query($query_all_problems);

    foreach ($query_all_problems->fetchAll(PDO::FETCH_ASSOC) as $row_prob) {

        $query_area_prob = "SELECT s.sistema AS area, p.problema AS problema, count(*) AS quantidade 
                    FROM ocorrencias as o, sistemas as s, problemas as p 
                    WHERE o.sistema = s.sis_id AND o.problema = p.prob_id 
                            AND s.sis_id IN (" . $row['sis_id'] . ") 
                            AND p.prob_id = " . $row_prob['prob_id'] . "
                    GROUP BY area, problema ORDER BY area, problema";
        $query_area_prob = $conn->query($query_area_prob);

        if ($query_area_prob->rowCount()){
            $rowQTD = $query_area_prob->fetch(PDO::FETCH_ASSOC);

            if (!in_array($row['sistema'], $data)) {

                $data[$j]['area'] = $rowQTD['area'];
                $data[$j][$rowQTD['problema']] = (int)$rowQTD['quantidade'];
            } else {

                $data[$j][$rowQTD['problema']] = (int)$rowQTD['quantidade'];
            }
            
        } else {

            $data[$j]['area'] = $row['sistema'];
            $data[$j][$row_prob['problema']] = 0;
            
        }

        

        $j++;
    }



    // $query_problemas = "SELECT s.sistema AS area, p.problema AS problema, count(*) AS quantidade 
    //                 FROM ocorrencias as o, sistemas as s, problemas as p 
    //                 WHERE o.sistema = s.sis_id AND o.problema = p.prob_id AND s.sis_id IN (" . $row['sis_id'] . ")
    //                 GROUP BY area, problema ORDER BY area, problema";
    
    // $query_problemas = $conn->query($query_problemas);

    // foreach ($query_problemas->fetchAll(PDO::FETCH_ASSOC) as $rowProblems) {

    //     $data[$j]['area'] = $row['sistema'];
    //     $data[$j]['problema'] = $rowProblems['problema'];
    //     $data[$j]['quantidade'] = $rowProblems['quantidade'];
        
    //     $j++;
    // }
    
    

    // $data[$i]['area'] = $row['sistema'];
    // $data[$i]['abertos'] = $totalAbertos;
    // $data[$i]['fechados'] = $totalFechados;
    // $data[$i]['cancelados'] = $totalCancelados;

    // $totalAbertos = 0;
    // $totalFechados = 0;
    // $totalCancelados = 0;

    $i++;
}

// IMPORTANT, output to json
echo json_encode($data);

?>
