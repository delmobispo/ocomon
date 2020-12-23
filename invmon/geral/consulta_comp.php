<?php
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
 */ session_start();

include "../../includes/include_geral.inc.php";
include "../../includes/include_geral_II.inc.php";

$_SESSION['s_page_invmon'] = $_SERVER['PHP_SELF'];

$auth = new auth($_SESSION['s_logado']);
$auth->testa_user($_SESSION['s_usuario'], $_SESSION['s_nivel'], $_SESSION['s_nivel_desc'], 2);

$cab = new headers;
$cab->set_title(TRANS('TTL_OCOMON'));

$hoje = date("Y-m-d H:i:s");

$cor1 = TD_COLOR;

?>
<link href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" rel="stylesheet" />
<link href="../../includes/components/select2/dist-2/css/select2.min.css" rel="stylesheet" />

<?php

print "<BR><B>" . TRANS('TTL_CONS_PERSONAL') . ":</B><BR>";


// print "<form method='post' name='form1' action='equipments_list.php'>";
print "<form method='post' name='form1' action='equipments_list.php'>";

print "<TABLE border='0'   width='100%'>";

print "<tr><td colspan='4'>&nbsp;</td></tr>";
print "<tr><td colspan='4'><b>" . TRANS('SUBTTL_DATA_COMP_GENERAL') . ":</b></td></tr>";
print "<tr><td colspan='4'>&nbsp;</td></tr>";

print "<tr>";
print "<TD bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_TYPE_EQUIP') . ": </b></TD>";
print "<TD bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_tipo_equip' size='1'>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from tipo_equip  order by tipo_nome";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['tipo_cod'] . "'>" . $row['tipo_nome'] . "</option>";
}
print "</SELECT>";
print "</TD>";

print "<TD  bgcolor='" . TD_COLOR . "'><b>" . TRANS('COL_MANUFACTURE') . ":</b></TD>";
print "<TD  bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_fab' size='1'>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from fabricantes  order by fab_nome";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['fab_cod'] . "'>" . $row['fab_nome'] . "</option>";
}
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<tr>";

print "<TD  bgcolor='" . TD_COLOR . "'><b>" . TRANS('ASSET_TAG') . "(s):</b></TD>";
print "<TD  bgcolor='" . BODY_COLOR . "'><INPUT type='text' class='text2' name='comp_inv'></TD>";

print "<TD  bgcolor='" . TD_COLOR . "'><b>" . TRANS('SERIAL_NUMBER') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'><INPUT type='text' class='text2' name='comp_sn'></TD>";
print "</tr>";

print "<tr>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('COL_MODEL') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_marca' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from marcas_comp order by marc_nome";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['marc_cod'] . "'>" . $row['marc_nome'] . "</option>";
}
print "</SELECT>";
print "</TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('LOCALIZATION') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select' name='comp_local' size='1'>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from localizacao  order by local";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['loc_id'] . "'>" . $row['local'] . "</option>";
}
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<TR>";
print "<TD  bgcolor='" . TD_COLOR . "'><b>" . TRANS('STATE') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select' name='comp_situac' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from situacao order by situac_nome";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['situac_cod'] . "'>" . $row['situac_nome'] . "</option>";
}
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<tr><td colspan='4'>&nbsp;</td></tr>";
print "<tr><td colspan='4'><b>" . TRANS('SUBTTL_DATA_COMP_COMP') . ":</b></td></tr>";
print "<tr><td colspan='4'>&nbsp;</td></tr>";

print "<tr>";
print "<TD  bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_NAME_COMPUTER') . ":</b></TD>";
print "<TD  bgcolor='" . BODY_COLOR . "'><INPUT type='text' class='text2' name='comp_nome'></TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('MOTHERBOARD') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_mb' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 10 order by mdit_fabricante, mdit_desc";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<tr>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('PROCESSOR') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_proc' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 11 order by mdit_fabricante,mdit_desc,mdit_desc_capacidade";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "</SELECT>";
print "</TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('CARD_MEMORY') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_memo' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 7 order by mdit_fabricante, mdit_desc, mdit_desc_capacidade";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "<option value=-2>Não nulo</option>";
print "<option value=-3>Nulo</option>";
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<TR>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('CARD_VIDEO') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_video' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 2 order by mdit_fabricante, mdit_desc";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "</SELECT>";
print "</TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('CARD_SOUND') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_som' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 4 order by mdit_fabricante, mdit_desc";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<TR>";
print "<TD  bgcolor='" . TD_COLOR . "'><b>" . TRANS('CARD_NETWORK') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_rede' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 3 order by mdit_fabricante, mdit_desc";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "</SELECT>";
print "</TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('CARD_MODEN') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_modem' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 6 order by mdit_fabricante, mdit_desc";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "<option value=-2>" . TRANS('OPT_NOT_POSSESS') . "</option>";
print "<option value=-3>Possui qualquer</option>";
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<TR>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('MNL_HD') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_modelohd' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 1 order by mdit_fabricante, mdit_desc_capacidade";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "</SELECT>";
print "</TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_RECORD_CD') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_grav' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 9 order by mdit_fabricante, mdit_desc";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "<option value=-2>" . TRANS('OPT_NOT_POSSESS') . "</option>";
print "<option value=-3>" . TRANS('OPT_IT_POSSESS_ANY') . "</option>";
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<TR>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_CDROM') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_cdrom' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 5 order by mdit_fabricante, mdit_desc";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "<option value=-2>" . TRANS('OPT_NOT_POSSESS') . "</option>";
print "<option value=-3>" . TRANS('OPT_IT_POSSESS_ANY') . "</option>";
print "</SELECT>";
print "</TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('DVD') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_dvd' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from modelos_itens where mdit_tipo = 8 order by mdit_fabricante, mdit_desc";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['mdit_cod'] . ">" . $row['mdit_fabricante'] . " " . $row['mdit_desc'] . " " . $row['mdit_desc_capacidade'] . "" . $row['mdit_sufixo'] . "</option>";
} // while
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<TR>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_WITH_SOFTWARE') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='software' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "select * from softwares s, fabricantes f where s.soft_fab = f.fab_cod order by f.fab_nome, s.soft_desc";
$commit = mysql_query($query);
while ($row = mysql_fetch_array($commit)) {
	print "<option value=" . $row['soft_cod'] . ">" . $row['fab_nome'] . " " . $row['soft_desc'] . " " . $row['soft_versao'] . "</option>";
} // while
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<tr><td colspan='4'>&nbsp;</td></tr>";
print "<tr><td colspan='4'><b>" . TRANS('SUBTTL_DATA_COMP_OTHERS') . ":</b></td></tr>";
print "<tr><td colspan='4'>&nbsp;</td></tr>";

print "<TR>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_TYPE_PRINTER') . ": </b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_tipo_imp'>";
print "<option value=-1>" . TRANS('ALL') . " </option>";
$query = "SELECT * from tipo_imp  order by tipo_imp_nome";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['tipo_imp_cod'] . "'>" . $row['tipo_imp_nome'] . "</option>";
}
print "</SELECT>";
print "</TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_MONITOR') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_polegada' size=1>";
print "<option value =-1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from polegada  order by pole_nome";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['pole_cod'] . "'>" . $row['pole_nome'] . "</option>";
}
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<tr>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_SCANNER') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select' name='comp_resolucao' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from resolucao  order by resol_nome";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['resol_cod'] . "'>" . $row['resol_nome'] . "</option>";
}
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<tr><td colspan='4'>&nbsp;</td></tr>";
print "<tr><td colspan='4'><b>" . TRANS('SUBTTL_DATA_COMP_CONT') . ":</b></td></tr>";
print "<tr><td colspan='4'>&nbsp;</td></tr>";

print "<TR>";
print "<TD   bgcolor='" . TD_COLOR . "'><b><a title='" . TRANS('MSG_POSSIBLE_SEL_UNIT_CTRL') . "'>" . TRANS('COL_UNIT') . ":</a></b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select-multiple-lin js-example-basic-multiple' name='comp_inst[]' multiple='multiple'>";
print "<option value=-1 title='" . TRANS('MSG_CTRL_SELECT_MULTIPLE') . "'>" . TRANS('ALL') . "</option>";
$query = "SELECT * from instituicao  order by inst_nome";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['inst_cod'] . "'>" . $row['inst_nome'] . "</option>";
}
print "</SELECT>";
print "</TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('COST_CENTER') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_ccusto' size=1>";
print "<option value = -1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from " . DB_CCUSTO . "." . TB_CCUSTO . " order by " . CCUSTO_DESC . "";
$resultado = mysql_query($query);
while ($rowCcusto = mysql_fetch_array($resultado)) {
	print "<option value='" . $rowCcusto[CCUSTO_ID] . "'>" . $rowCcusto[CCUSTO_DESC] . " - " . $rowCcusto[CCUSTO_COD] . "</option>";
}
print "</SELECT>";
print "</TD>";
print "</tr>";

print "<TR>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('COL_VENDOR') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select' name='comp_fornecedor' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from fornecedores  order by forn_nome";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['forn_cod'] . "'>" . $row['forn_nome'] . "</option>";
}
print "</SELECT>";
print "</TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_FISCAL_NOTES') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'><INPUT type='text' class='text2' name='comp_nf'></TD>";
print "</tr>";

print "<TR>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('FIELD_VALUE') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'><INPUT type='text' class='text2' name='comp_valor'></TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('PURCHASE_DATE') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'><INPUT type='text' class='text2' name='comp_data_compra' id='data_compra' autocomplete='off'></TD>";

print "</tr>";

print "<tr>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('COL_COMMENT') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'><INPUT type='text' class='text2' name='comp_coment'></TD>";

print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('ASSISTENCE') . ":</b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='comp_assist' size=1>";
print "<option value=-1 selected>" . TRANS('ALL') . "</option>";
$query = "SELECT * from assistencia order by assist_desc";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);
while ($row = mysql_fetch_array($resultado)) {
	print "<option value='" . $row['assist_cod'] . "'>" . $row['assist_desc'] . "</option>";
}
print "<option value=-2>" . TRANS('MSG_NOT_DEFINED') . "</option>";
print "</SELECT>";
print "</TD>";
print "</TR>";

print "<tr>";
print "<TD   bgcolor='" . TD_COLOR . "'><b>" . TRANS('COL_SUBSCRIBE_DATE') . ":</b>&nbsp;" .
	"<input type='checkbox' name='fromDateRegister'>" . TRANS('INV_FROM_DATE_REGISTER', 'A partir') . "</TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'><INPUT type='text' class='text2' name='comp_data' id='data_cadastro' autocomplete='off'></TD>";
print "<TD   bgcolor='" . TD_COLOR . "'><b><a title='" . TRANS('MSG_SEL_EQUIP_STATUS_GUARANTEE') . "'>" . TRANS('LINK_GUARANT') . ":</a></b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='garantia' size=1>";
print "<option value='-1' selected>" . TRANS('ALL') . "</option>";
print "<option value='1'>" . TRANS('UNDER_WARRANTY') . "</option>";
print "<option value='2'>" . TRANS('SEL_GUARANTEE_EXPIRED') . "</option>";
print "</selected>";
print "</td>";
print "</TR>";

print "<TR>";
print "<TD   bgcolor='" . TD_COLOR . "'><b><a title='" . TRANS('MSG_OPT_EQUIP_STATUS_GUARANTEE') . "'>" . TRANS('OCO_ORDER_BY') . ":</a></b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='ordena' size=1>";
print "<option value='etiqueta' selected>" . TRANS('ASSET_TAG') . "</option>";
print "<option value='instituicao,etiqueta'>" . TRANS('COL_UNIT') . "</option>";
print "<option value='equipamento,modelo'>" . TRANS('COL_TYPE') . "</option>";
print "<option value='fab_nome,modelo'>" . TRANS('COL_MODEL') . "</option>";
print "<option value='local'>" . TRANS('LOCALIZATION') . "</option>";
print "</selected>";
print "</TD>";
print "<TD   bgcolor='" . TD_COLOR . "'><b><a title='" . TRANS('MSG_OPT_EXIT_CONS') . "'>" . TRANS('FIELD_FORMAT_OF_EXIT') . ":</a></b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'>";
print "<SELECT class='select'  name='visualiza' size=1>";
print "<option value='tela' selected>" . TRANS('SEL_PRIORITY_NORMAL') . "</option>";
print "<option value='impressora'>" . TRANS('SEL_REPORT_FIVE_LINE') . "</option>";
print "<option value='relatorio'>" . TRANS('SEL_REPORT_ONE_LINE') . "</option>";
print "<option value='mantenedora1'>" . TRANS('SEL_CENTRAL_ONE_LINE') . "</option>";
print "<option value='texto'>" . TRANS('SEL_TXT_DELIMIT') . "</option>";
print "<option value='config'>" . TRANS('TTL_CONFIG') . "</option>";
print "<option value='termo'>" . TRANS('SEL_COMMITMENT_TERM') . "</option>";
print "<option value='transito'>" . TRANS('SEL_TRANSIT_FORM') . "</option>";
print "</selected>";

print "</td>";
print "</tr>";

print "<tr>";
print "<TD   bgcolor='" . TD_COLOR . "'><b><a title='" . TRANS('MSG_HEADING_EXIT_REPORT') . "'>" . TRANS('FIELD_HEADING') . ":</a></b></TD>";
print "<TD   bgcolor='" . BODY_COLOR . "'><INPUT type='text' class='text2' name='header'></TD>";
print "<td class='line'><input type='checkbox' name='novaJanela' title='" . TRANS('MSG_SEL_EXIT_WINDOW') . "'>" . TRANS('OPT_NEW_WINDOW') . "<td class='line'>";
print "</TR>";

print "<TR>";
print "<TD colspan='2' align='right'  bgcolor='" . BODY_COLOR . "'><input type='button' value='  " . TRANS('BT_OK') . "  ' class='button' name='ok' onClick='javascript:submitForm()'></td>";
print "<TD colspan='2' align='right'  bgcolor='" . BODY_COLOR . "'><INPUT type='reset' value='" . TRANS('BT_CANCEL') . "'  class='button' onClick='javascript:history.back()'></TD>";
print "</TR>";

print "</TABLE>";
print "</FORM>";

?>
<script src="../../includes/components/jquery/jquery.js"></script>
<script src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>
<script src="../../includes/components/select2/dist-2/js/select2.min.js"></script>
<script src="../../includes/components/select2/dist-2/js/i18n/pt-BR.js"></script>


<script LANGUAGE="JAVASCRIPT">
	// $(document).ready(function() {
	// 	$('.js-example-basic-single').select2({
	// 		placeholder: {
	// 			text: 'Todos'
	// 		},
	// 		minimumResultsForSearch: 10,
	// 		language: 'pt-BR',
	// 	});	
	// 	$('.js-example-basic-multiple').select2({
	// 		scrollAfterSelect: true,
	// 		closeOnSelect: false,
	// 		placeholder: {
	// 			id: '-1',
	// 			text: 'Seleção Múltipla (vazio para todos)'
	// 		},
	// 		allowClear: true,
	// 		language: 'pt-BR'
	// 	});
	// });

	$(window).on('load', function() {

		$('.select').addClass('js-example-basic-single');

		$('.js-example-basic-single').select2({
			placeholder: {
				text: 'Todos'
			},
			minimumResultsForSearch: 10,
			language: 'pt-BR',
		});
		$('.js-example-basic-multiple').select2({
			scrollAfterSelect: true,
			closeOnSelect: false,
			placeholder: {
				id: '-1',
				text: 'Seleção Múltipla (vazio para todos)'
			},
			allowClear: true,
			language: 'pt-BR'
		});


		$("#data_compra").datepicker({
			dateFormat: 'dd/mm/yy',
			changeMonth: true,
			dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
			dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
			dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
			monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
			monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
		});

		$("#data_cadastro").datepicker({
			dateFormat: 'dd/mm/yy',
			changeMonth: true,
			dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
			dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
			dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
			monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
			monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
		});



	});



	function checar() {
		var checado = false;
		if (document.form1.novaJanela.checked) {
			checado = true;
			//document.form1.target = "_blank";
		} else {
			checado = false;
		}
		return checado;
	}

	//window.setInterval("checar()",3000);

	function submitForm() {
		//document.form1.action = "equipments_list.php";


		if (checar() == true) {
			document.form1.target = "_blank";
			document.form1.submit();
		} else {
			document.form1.target = "";
			document.form1.submit();
		}

	}

	function invertView(id) {
		var element = document.getElementById(id);
		var elementImg = document.getElementById('img' + id);
		var address = '../../includes/icons/';

		if (element.style.display == 'none') {
			element.style.display = '';
			elementImg.src = address + 'close.png';
		} else {
			element.style.display = 'none';
			elementImg.src = address + 'open.png';
		}
	}

	desabilitaLinks(<?php print $_SESSION['s_invmon']; ?>);
</script>
<?php


print "</body>";
print "</html>";
?>