<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}

$conn = ConnectPDO::getInstance();

//Todas as áreas que o usuário percente
$uareas = $_SESSION['s_uareas'];

$sql = "SELECT contato FROM ocorrencias WHERE sistema in (" . $uareas . ") GROUP BY contato ORDER BY contato";
$sql = $conn->query($sql);

$data = array();

foreach ($sql->fetchAll() as $row) {
    $data[] = $row;
}
// $data[]['novo'] = ""

echo json_encode($data);

?>
