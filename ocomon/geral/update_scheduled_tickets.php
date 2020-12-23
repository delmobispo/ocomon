<?php session_start();
 /*                        Copyright 2020 Flávio Ribeiro

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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);

if (!isset($_POST['numero'])) {
  exit();
}

$erro = false;
$mensagem = "";
$assent = TRANS('TICKET_AUTO_GET_IN_QUEUE');

$sqlTicket = "SELECT numero, oco_scheduled_to FROM ocorrencias WHERE oco_scheduled = 1 AND oco_scheduled_to <= '". date('Y-m-d H:i:s') ."' ";


try {
  $resultTicket = $conn->query($sqlTicket);
}
catch (Exception $e) {
  // echo 'Erro: ', $e->getMessage(), "<br/>";
  return false;
}

foreach ($resultTicket->fetchAll() as $row) {
  $sqlUpdTicket = "UPDATE ocorrencias SET status = 1, oco_scheduled = 0 WHERE numero = " . $row['numero'] . " ";
  try {
    $resultUpd = $conn->exec($sqlUpdTicket);
  }
  catch (Exception $e) {
    // echo 'Erro: ', $e->getMessage(), "<br/>";
    $erro = true;
  }

  if (!$erro) {
    /* Tipo de assentamento: 6 - Caiu na fila de atendimento de forma automática após atingir a data de agendamento */
    $sqlAssent = "INSERT INTO assentamentos (ocorrencia, assentamento, `data`, responsavel, tipo_assentamento) values (".$row['numero'].", '{$assent}', '". $row['oco_scheduled_to'] ."', 1 , 6 )"; //tratar usuario 0 (zero) para ser do sistema

    $resultAssent = $conn->exec($sqlAssent);
  }

  if (!$erro) {
    /* Gravação da data na tabela tickets_stages */
    $stopTimeStage = insert_ticket_stage($conn, $row['numero'], 'stop', 1, $row['oco_scheduled_to']);
    $startTimeStage = insert_ticket_stage($conn, $row['numero'], 'start', 1, $row['oco_scheduled_to']);
  }


}

/* $dataHoje = new DateTime();
$schedule_to = new DateTime(dateDB($_POST['date_schedule']));
 */

return true;