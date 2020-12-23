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
				
$selProb = 0;
				
if (isset($_GET['prob']) && $_GET['prob'] != '-1' && !isset($_GET['radio_prob'])) {
	$selProb = $_GET['prob'];
} elseif (isset($_GET['prob']) && $_GET['prob'] != '-1' && isset($_GET['radio_prob'])) {
	$selProb = $_GET['radio_prob'];
} elseif ($_GET['prob'] == '-1') {
	echo ""; return;
}			

$qry_problema = "SELECT p.prob_descricao, sc.*, script.* FROM problemas as p ".
			"LEFT JOIN prob_x_script as sc on sc.prscpt_prob_id = p.prob_id ".
			"LEFT JOIN scripts as script on script.scpt_id = sc.prscpt_scpt_id ". 
		"WHERE p.prob_id = ".$selProb." ";

$exec_problema = $conn->query($qry_problema);
$row_problema = $exec_problema->fetch(PDO::FETCH_ASSOC);
	
if ($exec_problema->rowCount() == 0) {
	print "<div></div>";
} else {
	
	if (!empty($row_problema['prscpt_prob_id']) && ($_SESSION['s_nivel']!=3 || $row_problema['scpt_enduser']==1)) {
		// $script = "<hr><p class='text-success'><a onClick=\"popup('../../admin/geral/scripts.php?action=popup&prob=".$selProb."')\"><br /><i class='far fa-hand-point-right'></i>&nbsp;".TRANS('TIPS')."</a></p>";
		$script = "<hr><p class='text-success'><a onClick=\"popup('../../admin/geral/scripts_documentation.php?action=endview&prob=".$selProb."')\"><br /><i class='far fa-hand-point-right'></i>&nbsp;".TRANS('TIPS')."</a></p>";
	} else
		$script = "";
	
	if(!empty($row_problema['prob_descricao']) OR !empty($script)) {
		$texto = $row_problema['prob_descricao'] . $script;

		echo message('info', TRANS('TYPE_OF_ISSUE_INDICATED_TO'), $texto, '', '', true, 'far fa-lightbulb');
	}
}
?>