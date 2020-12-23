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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);



$qry_config = "SELECT * FROM config ";
$exec_config = $conn->query($qry_config);
$row_config = $exec_config->fetch(PDO::FETCH_ASSOC);

$selProb = 0;
if (isset($_GET['prob'])) {
	$selProb = $_GET['prob'];
	$qry_id = "SELECT * FROM problemas WHERE prob_id = ".$selProb."";
	$exec_qry_id = $conn->query($qry_id);
	$rowId = $exec_qry_id->fetch(PDO::FETCH_ASSOC);
}

$query = "SELECT * FROM problemas as p ".
			"LEFT JOIN sistemas as s on p.prob_area = s.sis_id ".
			"LEFT JOIN sla_solucao as sl on sl.slas_cod = p.prob_sla ".
			"LEFT JOIN prob_tipo_1 as pt1 on pt1.probt1_cod = p.prob_tipo_1 ".
			"LEFT JOIN prob_tipo_2 as pt2 on pt2.probt2_cod = p.prob_tipo_2 ".
			"LEFT JOIN prob_tipo_3 as pt3 on pt3.probt3_cod = p.prob_tipo_3 ";

if (isset($_GET['area_cod']) && $_GET['area_cod'] != -1){
	$clausula = " and (p.prob_area = '".$_GET['area_cod']."' OR (p.prob_area is null OR p.prob_area = -1)) ";
} else
	$clausula = "";

if (isset($_GET['prob']) && $_GET['prob'] != -1 )  { //&& $_POST['problema'])
	$query.= " WHERE lower(p.problema) like lower(('%".$rowId['problema']."%')) ".$clausula."";
} else
	$query.= " WHERE p.problema = '-1' ".$clausula."";

$query .=" ORDER  BY s.sistema, p.problema";

// dump($query);
	
$resultado = $conn->query($query);
$registros = $resultado->rowCount();

if ($registros == 0)
{
	//print "<tr><td align='center'>";
	//echo mensagem(TRANS('NO_CAT_TIL_SEL_PROB'));
	//print "</tr></td>";
}
else
{
	// print "<TABLE border='0' cellpadding='2' cellspacing='0' width='90%'>";
	print "<table class='table'>";
	print "<tr class='header'><td class='line'>".TRANS('ISSUE_TYPE')."<td class='line'>".TRANS('COL_SLA')."</TD>".
		"<td class='line'>".$row_config['conf_prob_tipo_1']."</TD><td class='line'>".$row_config['conf_prob_tipo_2']."</TD>".
		"<td class='line'>".$row_config['conf_prob_tipo_3']."</TD></tr>";

	$j=2;
	foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row)
	{
		if ($j % 2)
		{
			$trClass = "lin_par";
		}
		else
		{
			$trClass = "lin_impar";
		}
		$j++;
		print "<tr class=".$trClass." id='linhaxx".$j."' onMouseOver=\"destaca('linhaxx".$j."','".$_SESSION['s_colorDestaca']."');\" onMouseOut=\"libera('linhaxx".$j."','".$_SESSION['s_colorLinPar']."','".$_SESSION['s_colorLinImpar']."');\"  onMouseDown=\"marca('linhaxx".$j."','".$_SESSION['s_colorMarca']."');\">";

		//------------------------------------------------------------- INICIO ALTERACAO --------------------------------------------------------------
		print "<td class='line'><input type='radio' id='idRadioProb".$row['prob_id']."' name='radio_prob' value='".$row['prob_id']."'";
		//------------------------------------------------------------- FIM ALTERACAO --------------------------------------------------------------


			if (isset($_GET['radio_prob']) && $_GET['radio_prob'] == $row['prob_id']) print " checked"; else
			if (isset($_GET['prob']) && $_GET['prob'] == $row['prob_id']) print " checked"; //else

		//------------------------------------------------------------- INICIO ALTERACAO --------------------------------------------------------------
		if (isset($_GET['pathAdmin'])) //se o script estiver sendo chamado da path do módulo de administração
		{
			// print " onClick=\"ajaxFunction('divInformacaoProblema', '../../ocomon/geral/showInformacaoProb.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea' , 'radio_prob=idRadioProb".$row['prob_id']."');\"";
		}
			
		else	
			print " onClick=\"ajaxFunction('divInformacaoProblema', 'showInformacaoProb.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea' , 'radio_prob=idRadioProb".$row['prob_id']."');\"";
		//------------------------------------------------------------- FIM ALTERACAO --------------------------------------------------------------
		print ">".$row['problema']."</td>";

		print "<td class='line'>".NVL($row['slas_desc'])."</td>";
		print "<td class='line'>".NVL($row['probt1_desc'])."</td>";
		print "<td class='line'>".NVL($row['probt2_desc'])."</td>";
		print "<td class='line'>".NVL($row['probt3_desc'])."</td>";

		print "</TR>";
	}
}
print "</table>";

?>