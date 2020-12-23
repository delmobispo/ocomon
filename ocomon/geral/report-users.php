<?php session_start();
/*  Copyright 2020 Flávio Ribeiro

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
require_once __DIR__ . "/" . "../../includes/classes/worktime/Worktime.php";
include_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);



$sql = $QRY["ocorrencias_full_ini"] . "ORDER BY numero";
try {
    $resultSQL = $conn->query($sql);
}
catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    $erro = true;
    return false;
}

$data = array();
if ($resultSQL->rowCount()) {
    foreach ($resultSQL->fetchAll() as $row) {

        $loopData = array();
        $loopData['numero'] = $row['numero'];
        $loopData['data_abertura'] = dateScreen($row['data_abertura']);
        $loopData['status'] = $row['status'];

        $data[] = $loopData;
    }
} else {
    /* Nesse caso, o chamado é anterior a implementação do ticket_stages - não tenho as informações */
    
    $data[] = $loopData;
}


echo json_encode($data);
