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

if (!isset($_POST['numero'])) {
    exit();
}

$numero = (int)$_POST['numero'];


/* $sqlStatusNow = "SELECT s.stat_id, s.status FROM ocorrencias o, status s WHERE o.numero = {$numero} AND o.status = s.stat_id ";
$resultStatusNow = $conn->query($sqlStatusNow);
$rowStatusNow = $resultStatusNow->fetch();
$statusNow = $rowStatusNow['status'];
$idStatusNow = $rowStatusNow['stat_id']; */



// $sql = $QRY["ocorrencias_full_ini"]. "WHERE o.numero = {$numero}";
// $sql = "SELECT ts.* , s.* FROM tickets_stages ts, status s WHERE ts.ticket = " . $numero . " AND ts.status_id = s.stat_id ORDER BY ts.id";
$sql = "SELECT * FROM tickets_stages WHERE ticket = " . $numero . " ORDER BY id";
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

        $status = 'Indeterminado';
        $freeze = 0;

        if ($row['status_id'] != 0) { /* Status Zero reservado para os casos de chamados existentes antes do ticket_stage */
            $sqlInner = "SELECT status, stat_time_freeze FROM status WHERE stat_id = " . $row['status_id'] . " ";
            $resultInner = $conn->query($sqlInner);
            $rowInner = $resultInner->fetch();
            $status = $rowInner['status'];
            $freeze = $rowInner['stat_time_freeze'];
        }
        
        $loopData = array();
        $loopData['date_start'] = dateScreen($row['date_start']);
        $loopData['date_stop'] = dateScreen($row['date_stop']);
        // $loopData['status'] = $row['status'];
        $loopData['status'] = $status;
        $loopData['freeze'] = transbool($freeze);

        $data[] = $loopData;
    }
} else {
    /* Nesse caso, o chamado é anterior a implementação do ticket_stages - não tenho as informações */
    $loopData['date_start'] = '';
    $loopData['date_stop'] = '';
    $loopData['status'] = 'Indisponível';
    $loopData['freeze'] = '';
    $data[] = $loopData;
}


echo json_encode($data);
