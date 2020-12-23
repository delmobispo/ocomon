<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || $_SESSION['s_nivel'] != 1) {
    exit;
}

$conn = ConnectPDO::getInstance();

$areaAdmin = 0;
if (isset($_SESSION['s_area_admin']) && $_SESSION['s_area_admin'] == '1' && $_SESSION['s_nivel'] != '1') {
    $areaAdmin = 1;
}

$terms = "";
if ($areaAdmin) {
    $terms = " AND u.AREA = " . $_SESSION['s_area'] . " ";
}

$sql = "SELECT n.nivel_nome nivel, count(*) quantidade
        FROM usuarios u, nivel n WHERE u.nivel = n.nivel_cod {$terms}
        GROUP by nivel";

$sql = $conn->query($sql);
// $num_rows = $sql->rowCount();

$data = array();


foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $data[] = $row;
}
$data[]['chart_title'] = TRANS('USERS_X_LEVELS');
// IMPORTANT, output to json
echo json_encode($data);

?>