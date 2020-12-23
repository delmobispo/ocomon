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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);


if (!isset($_POST['numero'])) {
  exit();
}

$erro = false;
$mensagem = "";
$numero = (int) $_POST['numero'];

//Checa se já existe algum registro de log - caso não existir grava o estado atual
$firstLog = firstLog($conn, $numero,'NULL', 1);


$sqlTicket = "SELECT * FROM ocorrencias WHERE numero = {$numero} ";
$resultTicket = $conn->query($sqlTicket);
$row = $resultTicket->fetch();


/* Array para a funcao recordLog */
$arrayBeforePost = [];
$arrayBeforePost['operador_cod'] = $row['operador'];
$arrayBeforePost['status_cod'] = $row['status'];
$arrayBeforePost['oco_scheduled_to'] = $row['oco_scheduled_to'];
/* Para pegar o estado da ocorrência antes da atualização e permitir a gravação do log de modificações com recordLog() */
// $qryfull = $QRY["ocorrencias_full_ini"]." WHERE o.numero = " . $numero ;
// $execfull = $conn->query($qryfull);
// $arrayBeforePost = $execfull->fetch();


$user = (int)$_SESSION['s_uid'];
$assent = TRANS('TXTAREA_IN_ATTEND_BY') . ' ' . $_SESSION['s_usuario'];

/* Tipo de assentamento: 2 - Edição para atendimento */
$sql = "INSERT INTO assentamentos (ocorrencia, assentamento, `data`, responsavel, tipo_assentamento) values (".$numero.", '{$assent}', '".date('Y-m-d H:i:s')."', {$user}, 2 )";

try {
  $result = $conn->exec($sql);
}
catch (Exception $e) {
  echo 'Erro: ', $e->getMessage(), "<br/>";
  $mensagem .= $e->getMessage()."<br/>".$sql;
  $erro = true;
}


if (!empty($row['data_atendimento'])) {
  $sql = "UPDATE ocorrencias SET status = 2, operador= ".$user.", oco_scheduled = 0, oco_scheduled_to = null WHERE numero = '".$numero."'";
} else {
  $sql = "UPDATE ocorrencias SET status = 2, operador= ".$user.", data_atendimento='".date("Y-m-d H:i:s")."', oco_scheduled = 0, oco_scheduled_to = null WHERE numero='".$numero."'";
}

try {
  $result = $conn->exec($sql);
}
catch (Exception $e) {
  echo 'Erro: ', $e->getMessage(), "<br/>";
  $mensagem .= $e->getMessage()."<br/>".$sql;
  $erro = true;
}

/* Gravação da data na tabela tickets_stages */
$stopTimeStage = insert_ticket_stage($conn, $numero, 'stop', 2);
$startTimeStage = insert_ticket_stage($conn, $numero, 'start', 2);


if (!$erro) {

    $_SESSION['flash'] = message('success', '', TRANS('TICKET_GOTTEN_IN'), '', '');

    /* Array para a função recordLog */
    $afterPost = [];
    $afterPost['operador'] = $user;
    $afterPost['status'] = 2;
    $afterPost['agendadoPara'] = "";

    /* Função que grava o registro de alterações do chamado */
    $recordLog = recordLog($conn, $numero, $arrayBeforePost, $afterPost, 2);
    return $recordLog;

} else {
  $_SESSION['flash'] = message('danger', '', $mensagem, '', '');
}

return true;