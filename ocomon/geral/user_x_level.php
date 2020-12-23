<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || $_SESSION['s_nivel'] != 1) {
    exit;
}

$conn = ConnectPDO::getInstance();

$sql = "SELECT n.nivel_nome nivel, count(*) quantidade
        FROM usuarios u, nivel n WHERE u.nivel = n.nivel_cod 
        GROUP by nivel";

$sql = $conn->query($sql);
// $num_rows = $sql->rowCount();

$data = array();

// while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
//     $data[] = $row;
// }

foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $data[] = $row;
}

// IMPORTANT, output to json
echo json_encode($data);

?>
