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

print "<div id='Layer2' style='position:absolute; left:80%; top:100px; width:15%; height:40%; z-index:2; '>"; //  <!-- Ver: overflow: auto    não funciona para o Mozilla-->
print "<b>" . TRANS('COL_UNIT') . ":</font></font></b>";
print "<FORM name='form1' method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
$sizeLin = $linhasInst + 1;
print "<select class='select-multiple' name='instituicao[]' size='" . $sizeLin . "' multiple='yes'>";

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
    $clausula2 = " and comp_inst in (" . $saida . ") ";
}

$queryB = "SELECT count(*) from equipamentos  where comp_tipo_equip in (1,2) " . $clausula . "";
$resultadoB = mysql_query($queryB);
$total = mysql_result($resultadoB, 0);

// Select para retornar a quantidade e percentual de equipamentos cadastrados no sistema
$query = "select count(*) as qtd, t.tipo_nome as equipamento, t.tipo_cod as tipo, p.mdit_cod as tipo_proc,
				concat(count(*)/" . $total . "*100,'%')
			as porcento ,concat(p.mdit_desc,' ', p.mdit_desc_capacidade,'MHZ') as processador
				from equipamentos, tipo_equip as t, modelos_itens as p where
				comp_proc=p.mdit_cod and p.mdit_tipo=11 and comp_tipo_equip=t.tipo_cod " . $clausula2 . "
				group by processador,equipamento order by qtd desc,processador";
//and (comp_tipo_equip=1 or comp_tipo_equip=2)
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);



print "<br/><b><p align='center'>" . TRANS('TTL_QTD_COMP_PROCESSOR') . "</p></b><br/>";
print "<p align='center'>" . TRANS('COL_UNIT') . ": {$msgInst}</p><br/>";

print "<fieldset style='max-width:55%; padding:10px; align:center; margin:auto;'><legend>" . TRANS('TTL_COMP_X_PROCESSOR') . "</legend>";
print "<TABLE border='0' cellpadding='5' cellspacing='0' align='center' width='80%' style='margin:auto;'>";
print "<TR><TD class='line'><b>" . TRANS('MNL_CAD_EQUIP') . "</TD><TD class='line'><b>" . TRANS('PROCESSOR') . "</TD><TD class='line'><b>" . TRANS('COL_QTD') . "</TD><TD class='line'><b>" . TRANS('PERCENTAGE') . "</TD></tr>";

$i = 0;
$j = 2;
$totalFull = 0;
while ($row = mysql_fetch_array($resultado)) {
    $totalFull += $row['qtd'];

    $color = BODY_COLOR;
    $j++;
    print "<TR>";
    print "<TD bgcolor='" . $color . "'>" . $row['equipamento'] . "</TD>";
    print "<TD bgcolor='" . $color . "'><a href='equipments_list.php?comp_tipo_equip=" . $row['tipo'] . "&comp_proc=" . $row['tipo_proc'] . "&ordena=local,etiqueta' title='Exibe a listagem de computadores cadastrados com esse tipo de processador.'>" . $row['processador'] . "</a></TD>";
    print "<TD bgcolor='" . $color . "'>" . $row['qtd'] . "</TD>";
    print "<TD bgcolor='" . $color . "'>" . round($row['porcento'],2) . "%</TD>";
    print "</TR>";
    $i++;
}
print "<TR><TD class='line'><b></TD><TD class='line'><b></TD><TD class='line'><b>" . TRANS('TOTAL') . ": <font color='red'>" . $totalFull . "</font></TD><TD class='line'><b>100%</b></TD></tr>";
print "</TABLE>";
print "</fieldset>";


print "</BODY>";
print "</HTML>";
