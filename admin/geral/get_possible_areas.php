<?php session_start();
/*      Copyright 2020 Flávio Ribeiro

        This file is part of OCOMON.

        OCOMON is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation; either version 3 of the License, or
        (at your option) any later version.
        OCOMON is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with Foobar; if not, write to the Free Software
        Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$post = $_POST;


$areaAdmin = 0;
if (isset($_SESSION['s_area_admin']) && $_SESSION['s_area_admin'] == '1' && $_SESSION['s_nivel'] != '1') {
    $areaAdmin = 1;
}


$terms = "";
if ($post['level'] == 3) {
    $terms .= "AND sis_atende = 0 ";
} elseif ($post['level'] == 5) {
    $terms = "";
} else {
    $terms .= "AND sis_atende = 1 ";
}

if ($post['level'] == "") {
    $data['']['sistema'] = TRANS('SEL_LEVEL_FIRST');
    echo json_encode($data);
    return false;
}


$erro = false;
$mensagem = "";


$sql = "SELECT sis_id, sistema FROM sistemas WHERE 1 = 1 {$terms} "; 
if ($areaAdmin) {
    $sql .= " AND sis_id = " . $_SESSION['s_area'] . " ";
}
$sql .= "ORDER BY sistema";
try {
    $res = $conn->query($sql);
}
catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    $erro = true;
    $data['']['sistema'] = TRANS('NO_DATA_AVAILABLE');

    echo json_encode($data);
    return false;
}

foreach ($res->fetchall() as $row) {
    $data[] = $row;
}

echo json_encode($data);