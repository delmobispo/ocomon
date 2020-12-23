<?php /*                        Copyright 2020 Flávio Ribeiro

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
  */session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
	header("Location: ../../index.php");
	exit;
}
require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new auth($_SESSION['s_logado']);
$auth->testa_user_hidden($_SESSION['s_usuario'],$_SESSION['s_nivel'],$_SESSION['s_nivel_desc'],4);


if (isset($_POST['onlyOpen']) && $_POST['onlyOpen'] == 1) {


	// dump($_POST); exit();

	$numero = noHtml($_POST['numero']);
	$comment = noHtml($_POST['add_comment']);
	
	$qry = "INSERT INTO assentamentos (ocorrencia, assentamento, data, responsavel, asset_privated, tipo_assentamento) values ".
			"(".$numero.", '".$comment."', '".date("Y-m-d H:i:s")."', ".$_SESSION['s_uid'].", 0, 8 ) ";
	$exec = $conn->exec($qry) or die ($qry);
	
	
	$qryfull = $QRY["ocorrencias_full_ini"]." WHERE o.numero = ".$numero."";
	// $execfull = mysql_query($qryfull) or die(TRANS('MSG_ERR_RESCUE_VARIA_SURROU').$qryfull);
	$execfull = $conn->query($qryfull);
	$rowfull = $execfull->fetch();

	$VARS = array();
	$VARS['%numero%'] = $rowfull['numero'];
	$VARS['%usuario%'] = $rowfull['contato'];
	$VARS['%contato%'] = $rowfull['contato'];
	$VARS['%descricao%'] = $rowfull['descricao'];
	$VARS['%departamento%'] = $rowfull['setor'];
	$VARS['%telefone%'] = $rowfull['telefone'];
	$VARS['%assentamento%'] = $comment;
	//$VARS['%site%'] = "<a href='".$row_config['conf_ocomon_site']."'>".$row_config['conf_ocomon_site']."</a>";
	$VARS['%area%'] = $rowfull['area'];
	$VARS['%operador%'] = $rowfull['nome'];
	//$VARS['%editor%'] = $rowMailLogado['nome'];
	$VARS['%aberto_por%'] = $rowfull['aberto_por'];
	$VARS['%problema%'] = $rowfull['problema'];
	// $VARS['%versao%'] = VERSAO;		
	
	$sqlMailArea = "select * from sistemas where sis_id = ".$rowfull['area_cod']."";
	$execMailArea = $conn->query($sqlMailArea);
	$rowMailArea = $execMailArea->fetch();	
	
	
	$qryconfmail = "SELECT * FROM mailconfig";
	$execconfmail = $conn->query($qryconfmail);
	$rowconfmail = $execconfmail->fetch();	


	$event = 'edita-para-area';
	$qrymsg = "SELECT * FROM msgconfig WHERE msg_event like ('".$event."')";
	$execmsg = $conn->query($qrymsg);
	$rowmsg = $execmsg->fetch();

	send_mail($event, $rowMailArea['sis_email'], $rowconfmail, $rowmsg, $VARS);
	
	// print "<script>redirect('ticket_show.php?numero=".$numero."&id=".$_POST['urlid']."');</script>";
	// print "<script>redirect('ticket_show.php?numero=".$numero."');</script>";

	$_SESSION['flash'] = message('success', 'Pronto!', TRANS('TICKET_ENTRY_SUCCESS_ADDED'), '');
	// echo TRANS('TICKET_ENTRY_SUCCESS_ADDED');
	echo message('success', 'Pronto!', TRANS('TICKET_ENTRY_SUCCESS_ADDED'), '');

}
