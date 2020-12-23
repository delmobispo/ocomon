<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}

$conn = ConnectPDO::getInstance();

$sql = "SELECT sistemas.sistema AS area, count(ocorrencias.sistema) AS quantidade 
            FROM sistemas, ocorrencias 
            WHERE sistemas.sis_id = ocorrencias.sistema ";

if (isset($_POST['area']) && ! empty($_POST['area'])) {
    $sql.= "AND sistemas.sis_id = {$_POST['area']}";
}

$sql.= " GROUP BY area";
            

$sql = $conn->query($sql);

$data = array();

foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $data[] = $row;
}
$data[]['chart_title'] = TRANS('TICKETS_BY_AREAS', '', 1);

// IMPORTANT, output to json
echo json_encode($data);

?>
