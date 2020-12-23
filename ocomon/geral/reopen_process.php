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
    return false;
}

$config = getConfig($conn);

if (!$config['conf_allow_reopen']) {
    return false;
}


$exception = "";
$erro = false;
$mensagem = "";
$numero = (int) $_POST['numero'];

//Checa se já existe algum registro de log - caso não existir grava o estado atual
$firstLog = firstLog($conn, $numero,'NULL', 1);


$sqlTicket = "SELECT * FROM ocorrencias WHERE numero = {$numero} AND status = 4 ";
$resultTicket = $conn->query($sqlTicket);
if ($resultTicket->rowCount() == 0) {
    $_SESSION['flash'] = message('danger', '', TRANS('MSG_TICKET_CANT_REOPEN'), '', '');
    return false;
}
$row = $resultTicket->fetch();


/* Array para a funcao recordLog */
$arrayBeforePost = [];
$arrayBeforePost['operador_cod'] = $row['operador'];
$arrayBeforePost['status_cod'] = $row['status'];
/* Para pegar o estado da ocorrência antes da atualização e permitir a gravação do log de modificações com recordLog() */
// $qryfull = $QRY["ocorrencias_full_ini"]." WHERE o.numero = " . $numero ;
// $execfull = $conn->query($qryfull);
// $arrayBeforePost = $execfull->fetch();
$user = (int)$_SESSION['s_uid'];
$entry = TRANS('TICKET_REOPENED_BY') . ' ' . $_SESSION['s_usuario'];

$sql = "UPDATE ocorrencias SET `status`= 1, data_fechamento = NULL WHERE numero = " . $numero . "";
try {
    $conn->exec($sql);
    $qryDelSolution = "DELETE FROM solucoes WHERE numero = " . $numero . "";
    $conn->exec($qryDelSolution);


    /* Tipo de assentamento: 9 - reabertura */
    $sql = "INSERT INTO assentamentos (ocorrencia, assentamento, `data`, responsavel, tipo_assentamento) values (".$numero.", '{$entry}', '".date('Y-m-d H:i:s')."', {$user}, 9 )";

    try {
        $result = $conn->exec($sql);
    }
    catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
    }


    /* Gravação da data na tabela tickets_stages */
    $stopTimeStage = insert_ticket_stage($conn, $numero, 'stop', 1);
    $startTimeStage = insert_ticket_stage($conn, $numero, 'start', 1);


}
catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
}



if (strlen ($exception) > 0) {
    $_SESSION['flash'] = message('warning', '', TRANS('MSG_SOMETHING_GOT_WRONG') . $exception, '', '');
    return false;
}


$_SESSION['flash'] = message('success', '', TRANS('MSG_TICKET_REOPENED_SUCCESSFULY'), '', '');

/* Array para a função recordLog */
$afterPost = [];
$afterPost['operador'] = $user;
$afterPost['status'] = 1;

/* Função que grava o registro de alterações do chamado */
$recordLog = recordLog($conn, $numero, $arrayBeforePost, $afterPost, 3);
return $recordLog;