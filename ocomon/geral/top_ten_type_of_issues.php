<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}

$conn = ConnectPDO::getInstance();

$sql = "SELECT p.problema, count(*) as total FROM ocorrencias o, problemas p WHERE p.prob_id = o.problema  ";
$sql.= " GROUP BY problema ORDER by total desc LIMIT 10";
            

$sql = $conn->query($sql);

$data = array();

foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $data[] = $row;
}
$data[]['chart_title'] = TRANS('TOP_TEN_TYPE_OF_ISSUES', '', 1);
// IMPORTANT, output to json
echo json_encode($data);

?>
