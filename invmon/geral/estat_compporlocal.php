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
 */session_start();
include "../../includes/include_geral.inc.php";
include "../../includes/include_geral_II.inc.php";

$_SESSION['s_page_invmon'] = $_SERVER['PHP_SELF'];

$cab = new headers;
$cab->set_title(TRANS('TTL_OCOMON'));

$auth = new auth($_SESSION['s_logado']);
$auth->testa_user($_SESSION['s_usuario'], $_SESSION['s_nivel'], $_SESSION['s_nivel_desc'], 2);

$hoje = date("Y-m-d H:i:s");

$cor = TD_COLOR;
$cor1 = TD_COLOR;
$cor3 = BODY_COLOR;

$queryInst = "SELECT * from instituicao order by inst_nome";
$resultadoInst = mysql_query($queryInst);
$linhasInst = mysql_num_rows($resultadoInst);

print "<div id='Layer2' style='position:absolute; left:81%; top:70px; width:15%; height:40%; z-index:2; '>"; //  <!-- Ver: overflow: auto    não funciona para o Mozilla-->
print "<b>" . TRANS('COL_UNIT') . ":</b>";
print "<FORM name='form1' method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
$sizeLin = $linhasInst + 1;
print "<select class='select-multiple' font-size:11px;' name='instituicao[]' size='" . $sizeLin . "' multiple='yes'>";

print "<option value='-1' selected>" . TRANS('ALL') . "</option>";
while ($rowInst = mysql_fetch_array($resultadoInst)) {
    print "<option value='" . $rowInst['inst_cod'] . "'>" . $rowInst['inst_nome'] . "</option>";
}
print "</select>";
print "<br/><br/><input type='submit' class='button' value='" . TRANS('BT_APPLY') . "' name='OK'>";

print "</form>";
print "</div>";

$saida = "";
if (isset($_POST['instituicao'])) {
    for ($i = 0; $i < count($_POST['instituicao']); $i++) {
        $saida .= $_POST['instituicao'][$i] . ",";
    }
}
if (strlen($saida) > 1) {
    $saida = substr($saida, 0, -1);
}

$msgInst = "";
if (($saida == "") || ($saida == "-1")) {
    $clausula = "";
    $clausula2 = "";
    $msgInst = TRANS('ALL');
} else {
    $sqlA = "select inst_nome as inst from instituicao where inst_cod in (" . $saida . ")";
    $resultadoA = mysql_query($sqlA);
    while ($rowA = mysql_fetch_array($resultadoA)) {
        $msgInst .= $rowA['inst'] . ', ';
    }
    $msgInst = substr($msgInst, 0, -2);

    $clausula = " and comp_inst in (" . $saida . ")";
    $clausula2 = " and c.comp_inst in (" . $saida . ") ";
}

$queryB = "SELECT count(*) from equipamentos where comp_tipo_equip in (1,2) " . $clausula . "";

$resultadoB = mysql_query($queryB);
$total = mysql_result($resultadoB, 0);

// Select para retornar a quantidade e percentual de equipamentos cadastrados no sistema
$query = "select count(l.local)as qtd, count(*)/" . $total . "*100 as porcento,
			l.local as local, l.loc_id as tipo_local, t.tipo_nome as equipamento, t.tipo_cod as tipo
			from equipamentos as c,
			tipo_equip as t, localizacao as l where ((c.comp_tipo_equip = t.tipo_cod)
			and (c.comp_local = l.loc_id) and (t.tipo_cod in (1,2)) " . $clausula2 . ") group by local,tipo order by qtd desc ,
			local asc";

$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);

print "<br/><TABLE border='0' cellpadding='5' cellspacing='0' align='left' width='80%'>";


print "<tr><td width='80%' align='center'><b>" . TRANS('TTL_TOTAL_COMP_CAD_SECTOR') . " <p>" . TRANS('COL_UNIT') . ": " . $msgInst . ".</p></b></td></tr>";

print "<td class='line'>";
print "<fieldset class='center'><legend>" . TRANS('TTL_COMP_X_SECTOR') . "</legend>";
print "<TABLE border='0' cellpadding='5' cellspacing='0' align='center' width='100%' style='margin:auto;'>";
print "<TR class='header'><TD class='line'><b>" . TRANS('DEPARTMENT') . "</TD><TD class='line'><b>" . TRANS('MNL_CAD_EQUIP') . "</TD><TD class='line'><b>" . TRANS('COL_QTD') . "</TD><TD class='line'><b>" . TRANS('PERCENTAGE') . "</TD></tr>";
$i = 0;
$j = 2;
while ($row = mysql_fetch_array($resultado)) {
    $color = BODY_COLOR;
    $j++;
    print "<TR>";
    print "<TD bgcolor='" . $color . "'><a href='equipments_list.php?comp_tipo_equip=" . $row['tipo'] . "&comp_local=" . $row['tipo_local'] . "&ordena=modelo,etiqueta' title='" . TRANS('HNT_LIST_EQUIP_CAD_TYPE_LOCAL') . "'>" . $row['local'] . "</TD>";
    print "<TD bgcolor='" . $color . "'>" . $row['equipamento'] . "</TD>";
    print "<TD bgcolor='" . $color . "'>" . $row['qtd'] . "</TD>";
    print "<TD bgcolor='" . $color . "'>" . round($row['porcento'],2) . "%</TD>";
    print "</TR>";
    $i++;
}
print "<TR><TD class='line'><b></TD><TD class='line'><b></TD><TD class='line'><b>" . TRANS('TOTAL') . ": <font color='red'>" . $total . "</font></TD><TD class='line'></TD></tr>";
print "</TABLE>";
print "</fieldset>";



print "</BODY>";
print "</HTML>";
