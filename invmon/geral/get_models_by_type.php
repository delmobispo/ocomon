<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    return;
}

$conn = ConnectPDO::getInstance();

$post = $_POST;


$terms = "";

// if ($post['model_selected'] && !empty($post['model_seleted'])) {
//     $terms .= " AND mdit_cod = '" . $post['model_selected'] . "' ";
// }

if (isset($post['type']) && $post['type'] != '-1' && $post['type'] != '') {
    $terms .= " AND mdit_tipo = '" . $post['type'] . "' ";
}

$sql = "SELECT * FROM modelos_itens WHERE 1 = 1 {$terms}";
$sql .= " ORDER BY mdit_fabricante, mdit_desc, mdit_desc_capacidade";

$res = $conn->query($sql);

$data = array();

foreach ($res->fetchAll() as $row) {
    $data[] = $row;
}


/* mdit_cod,  mdit_fabricante, mdit_desc, mdit_desc_capacidade, mdit_sufixo*/

// $data[]['novo'] = ""

echo json_encode($data);

?>
