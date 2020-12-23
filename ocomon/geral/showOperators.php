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
	

if (isset($_GET['op'])){
	$qryDesc = "SELECT * FROM usuarios WHERE user_id = ".$_GET['op']."";
	$execDesc = $conn->query($qryDesc);
	$rowDesc = $execDesc->fetch(PDO::FETCH_ASSOC);
}

print "<SELECT class='form-control ' name='foward' id='idFoward'>";
	print "<option value='-1' selected>".TRANS('OCO_SEL_OPERATOR')."</option>";
$query = "SELECT u.*, a.* from usuarios u, sistemas a where u.AREA = a.sis_id and a.sis_atende='1' and u.nivel not in (3,4,5)";
	
if(isset($_GET['area_cod'])){
	$query.=" and a.sis_id = '".$_GET['area_cod']."'";
}
$query.=" order by login";
$exec_oper = $conn->query($query);
foreach ($exec_oper->fetchAll(PDO::FETCH_ASSOC) as $row_oper)
{
	print "<option value=".$row_oper['user_id'].""; 
	
	if (isset($_GET['op'])){
		if ($_GET['op'] == $row_oper){
			print " selected";
		}
	}
	print ">".$row_oper['nome']."</option>";
}
print "</SELECT>";

?>