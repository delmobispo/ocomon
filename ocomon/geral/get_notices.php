<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}

$conn = ConnectPDO::getInstance();

$exceptions = "";
$user = $_SESSION['s_uid'];
$uareas = $_SESSION['s_uareas'];
$today = date('Y-m-d');


$terms = " AND ( area IN (" . $uareas . ") OR area = -1 OR area IS NULL) ";
if ($_SESSION['s_nivel'] == 1) {
    /* só filtra para as áreas caso não seja administrador do sistema */
    $terms = "";
}

$sql = "SELECT * 
        FROM 
            avisos 
        WHERE 
            expire_date >= '{$today}' 
            AND is_active = 1 
            {$terms} 
            ORDER BY data";
$res = $conn->query($sql);

$data = array();

foreach ($res->fetchAll() as $row) {
    
    $sql = "SELECT notice_id FROM user_notices WHERE
            notice_id = '" . $row['aviso_id'] . "' 
            AND
            user_id = '" . $user . "' 
    ";
    try {
        $result = $conn->query($sql);
        /* Só enviará a notificação caso já não tenha sido mostrada para o user */
        if (!$result->rowCount()) {
            $data[] = $row;
        }
    }
    catch (Exception $e) {
        $exceptions .= "<br/>" . $e->getMessage . "<br/>" . $sql;
        echo $exceptions;
    }
}
// $data[]['novo'] = ""
// echo $sql;

echo json_encode($data);

?>
