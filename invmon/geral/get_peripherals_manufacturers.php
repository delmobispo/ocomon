<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}

$conn = ConnectPDO::getInstance();


$sql = "SELECT TRIM(mdit_fabricante) as manufacturer FROM modelos_itens GROUP BY TRIM(mdit_fabricante) ORDER BY TRIM(mdit_fabricante)";
$sql = $conn->query($sql);

$data = array();

foreach ($sql->fetchAll() as $row) {
    $data[] = $row;
}
// $data[]['novo'] = ""

echo json_encode($data);

?>
