<?php /*Copyright 2020 Flávio Ribeiro

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

	/* A div "divProblema" deve ser utilizada para a exibição das categorias de tipos de problemas */
	
	if (isset($_GET['pathAdmin'])) {
		/* se o script estiver sendo chamado a partir do path do módulo de administração */
		print "<input type='hidden' name='pathAdmin' id='idPathAdmin' value='fromPathAdmin'>";
		print "<SELECT class='form-control' name='problema' id='idProblema' onChange=\"ajaxFunction('divProblema', '../../ocomon/geral/showProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea', 'pathAdmin=idPathAdmin');   \">";

		/* ajaxFunction('divInformacaoProblema', '../../ocomon/geral/showInformacaoProb.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea'); */

		/* ajaxFunction('divSla', 'sla_standalone.php', 'idLoad', 'numero=idSlaNumero', 'popup=idSlaNumero', 'SCHEDULED=idScheduled', 'new_prob=idProblema'); */
	} else {
		print "<SELECT class='form-control' name='problema' id='idProblema' 
		onChange=\"
		ajaxFunction('divProblema', 'showProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea'); 
		ajaxFunction('divInformacaoProblema', 'showInformacaoProb.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea'); 

		\">";
	}
	
	$query = "";
	if($_GET['area_habilitada'] == 'sim'){
		if($_GET['area_cod']=="" || $_GET['area_cod']==-1){
			print "<option value='-1'>".TRANS('SEL_AREA')."</option>";
		} else {
			$query = "
                SELECT * 
                FROM problemas 
                WHERE 
                	prob_area = '".$_GET['area_cod']."' OR 
                	prob_area IS NULL OR 
                	prob_area = -1
                GROUP BY problema 
                ORDER BY problema
				";
			$exec_prob = $conn->query($query);
			print "<option value='-1'>".TRANS('OCO_SEL_PROB')."</option>";
			foreach ($exec_prob->fetchAll(PDO::FETCH_ASSOC) as $row_prob) {
				print "<option value=".$row_prob['prob_id']."";
				if ($row_prob['prob_id'] == $_GET['prob']) {
					print " selected";
				}
				print " >".$row_prob['problema']." </option>";
			}
		}
	} elseif ($_GET['area_habilitada'] == 'needless_area'){
		
			$query = "
                SELECT * 
                FROM problemas 
                WHERE 
                	prob_area = '".$_GET['area_cod']."' OR 
                	prob_area IS NULL OR 
                	prob_area = -1
                GROUP BY problema 
                ORDER BY problema
				";
			$exec_prob = $conn->query($query);
			print "<option value='-1'>".TRANS('OCO_SEL_PROB')."</option>";
			foreach ($exec_prob->fetchAll(PDO::FETCH_ASSOC) as $row_prob) {
				print "<option value=".$row_prob['prob_id']."";
				if ($row_prob['prob_id'] == $_GET['prob']) {
					print " selected";
				}
				print " >".$row_prob['problema']." </option>";
			}
		
	} else {
		
		$terms = (isset($_GET['area_destino']) ? " WHERE prob_area = '" . $_GET['area_destino'] . "' OR prob_area = '-1' OR prob_area IS NULL" : "");

		$query = "
                SELECT * 
				FROM problemas 
				{$terms} 
                GROUP BY problema 
                ORDER BY problema
                ";
		$exec_prob = $conn->query($query);
		print "<option value='-1'>".TRANS('OCO_SEL_PROB')."</option>";
		foreach ($exec_prob->fetchAll(PDO::FETCH_ASSOC) as $row_prob) {
			print "<option value=".$row_prob['prob_id']."";
				if ($row_prob['prob_id'] == $_GET['prob']) {
					print " selected";
				}
			print " >".$row_prob['problema']." </option>";
		}
	}
	print "</select>";

?>