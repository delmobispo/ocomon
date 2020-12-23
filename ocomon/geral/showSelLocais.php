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
  */ session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
	header("Location: ../../index.php");
	exit;
}
require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);


$qry = $QRY["useropencall_custom"];
$qry .= " AND  c.conf_cod = '" . $_SESSION['s_screen'] . "'";
$execqry = $conn->query($qry);
$rowconf = $execqry->fetch(PDO::FETCH_ASSOC);

$showLoadButton = false;
$showSearchButton = false;
if ((!empty($rowconf) && $rowconf['conf_scr_btloadlocal']) || empty($rowconf)) {
	$showLoadButton = true;
}
if ((!empty($rowconf) && $rowconf['conf_scr_searchbylocal']) || empty($rowconf)) {
	$showSearchButton = true;
}


if (isset($_GET['unidade']) && isset($_GET['etiqueta'])) {
	$qryDesc = "SELECT * FROM equipamentos where comp_inst = '" . $_GET['unidade'] . "' AND comp_inv ='" . $_GET['etiqueta'] . "' ";
	$execDesc = $conn->query($qryDesc);
	$rowDesc = $execDesc->fetch(PDO::FETCH_ASSOC);
}

?>
	<div class="input-group">
		
	<?php
		if ($showLoadButton) {
			?>
			<div class="input-group-prepend">
				<div class="input-group-text">
					<a href="javascript:void(0);" onClick="ajaxFunction('idDivSelLocal', 'showSelLocais.php', 'idLoad', 'unidade=idUnidade', 'etiqueta=idEtiqueta');" title="<?= TRANS('LOAD_DEPARTMENT_OF_THE_ASSET_TAG'); ?>"><i class="fa fa-sync-alt"></i></a>
				</div>
			</div>	
			<?php
		}

print "<SELECT class='form-control ' name='local' id='idLocal'>";
print "<option value=-1 selected>" . TRANS('OCO_SEL_LOCAL') . "</option>";
$query = "SELECT l .  * , r.reit_nome, pr.prior_nivel AS prioridade, d.dom_desc AS dominio, pred.pred_desc as predio
			FROM localizacao AS l
			LEFT  JOIN reitorias AS r ON r.reit_cod = l.loc_reitoria
			LEFT  JOIN prioridades AS pr ON pr.prior_cod = l.loc_prior
			LEFT  JOIN dominios AS d ON d.dom_cod = l.loc_dominio
			LEFT JOIN predios as pred on pred.pred_cod = l.loc_predio
			WHERE loc_status not in (0)
			ORDER  BY LOCAL ";
$resultado = $conn->query($query);
$linhas = $resultado->rowCount();
foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $rowi) {
	print "<option value='" . $rowi['loc_id'] . "'";
	if (isset($rowDesc) && $rowDesc && $rowDesc['comp_local'] == $rowi['loc_id']) print " selected";
	//------------------------------------------------------------- INICIO ALTERACAO --------------------------------------------------------------
	else if (isset($_REQUEST['invLoc']) && $rowi['loc_id'] == $_REQUEST['invLoc']) print " selected";
	//------------------------------------------------------------- FIM ALTERACAO --------------------------------------------------------------
	print ">" . $rowi['local'] . " - " . $rowi['predio'] . "</option>";
}
print "</SELECT>";

if ($showSearchButton) {
	?>
	<div class="input-group-append">
		<div class="input-group-text">
			<a href="javascript:void(0);" title="<?= TRANS('CONS_EQUIP_LOCAL'); ?>" onClick="checa_por_local()"><i class="fa fa-search"></i></a>
		</div>
	</div>
	<?php
}
?>
	
</div>