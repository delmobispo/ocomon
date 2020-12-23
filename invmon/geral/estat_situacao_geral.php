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
include "../../includes/components/jpgraph/src/jpgraph.php";
include "../../includes/components/jpgraph/src/jpgraph_pie.php";
include "../../includes/components/jpgraph/src/jpgraph_pie3d.php";

$_SESSION['s_page_invmon'] = $_SERVER['PHP_SELF'];

$cab = new headers;
$cab->set_title(TRANS('TTL_OCOMON'));

$auth = new auth($_SESSION['s_logado']);
$auth->testa_user($_SESSION['s_usuario'], $_SESSION['s_nivel'], $_SESSION['s_nivel_desc'], 2);

$hoje = date("Y-m-d H:i:s");

$cor = TD_COLOR;
$cor1 = TD_COLOR;
$cor3 = BODY_COLOR;

$dados = array(); //Array que irá guardar os valores para montar o gráfico
$legenda = array();

$queryInst = "SELECT * from instituicao order by inst_nome";
$resultadoInst = mysql_query($queryInst);
$linhasInst = mysql_num_rows($resultadoInst);

if (isset($_POST['checkprint'])) {
    $div_hide = "display: none;";
    $hide_buttom = true;
} else {
    $div_hide = "";
    $hide_buttom = false;
}

print "<div id='Layer2' style='position:absolute; left:80%; top:70px; width:15%; height:40%; z-index:2; " . $div_hide . " '>"; //  <!-- Ver: overflow: auto    não funciona para o Mozilla-->
print "<b>" . TRANS('COL_UNIT') . ":</font></font></b>";
print "<FORM name='form1' method='post' action='" . $_SERVER['PHP_SELF'] . "' onSubmit=\"newTarget();\">";
$sizeLin = $linhasInst + 1;
print "<select class='select-multiple' name='instituicao[]' size='" . $sizeLin . "' multiple='yes'>";

print "<option value='-1' selected>" . TRANS('ALL') . "</option>";
while ($rowInst = mysql_fetch_array($resultadoInst)) {
    print "<option value='" . $rowInst['inst_cod'] . "'>" . $rowInst['inst_nome'] . "</option>";
}
print "</select>";
print "<br/><br/><input type='submit' class='button' value='" . TRANS('BT_APPLY') . "' name='OK'>";
print "<input type='checkbox' name='checkprint'>" . TRANS('PRINT') . "";

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

    $clausula = " where comp_inst in (" . $saida . ")";
    $clausula2 = " and c.comp_inst in (" . $saida . ") ";
}

$queryB = "SELECT count(*) from equipamentos " . $clausula . "";
$resultadoB = mysql_query($queryB);
$total = mysql_result($resultadoB, 0);

// Select para retornar a quantidade e percentual de equipamentos cadastrados no sistema
$query = "SELECT s.situac_nome AS situacao, count( s.situac_nome )  AS qtd_situac,
				concat(count( * ) / " . $total . " * 100,'%') AS porcento, s.situac_cod as situac_cod
				FROM equipamentos AS c, situacao AS s
				WHERE c.comp_situac = s.situac_cod " . $clausula2 . "
				GROUP  BY s.situac_nome order  by qtd_situac desc";
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);


echo "<br/><br/><p class='center bold'>" . TRANS('TTL_ESTAT_GENERAL_SIT_EQUIP') . "</p>";
echo "<br/><p class='center bold'>" . TRANS('COL_UNIT') . ": " . $msgInst . "</p>";

print "<fieldset class='center'><legend>" . TRANS('SUBTTL_SITUAC_BOARD') . "</legend>";
print "<TABLE border='0' cellpadding='5' cellspacing='0' class='center' width='100%'>";
print "<TR><TD class='line'><b>" . TRANS('STATE') . "</TD><TD class='line'><b>" . TRANS('COL_QTD') . "</TD><TD class='line'><b>" . TRANS('PERCENTAGE') . "</TD></tr>";
$i = 0;
$j = 2;
$totalFull = 0;
while ($row = mysql_fetch_array($resultado)) {
    $color = BODY_COLOR;
    $j++;
    $totalFull += $row['qtd_situac'];
    print "<TR>";
    print "<TD bgcolor='" . $color . "'><a href='equipments_list.php?comp_situac=" . $row['situac_cod'] . "&ordena=local,etiqueta' title='" . TRANS('HNT_LIST_EQUIP_CAD_FOR_SITUAC') . "'>" . $row['situacao'] . "</a></TD>";
    print "<TD bgcolor='" . $color . "'>" . $row['qtd_situac'] . "</TD>";
    print "<TD bgcolor='" . $color . "'>" . round($row['porcento'], 2) . "</TD>";

    $dados[] = $row['qtd_situac']; //Dados para o gráfico.
    $legenda[] = $row['situacao'];
    print "</TR>";
    $i++;
}
print "<TR><TD class='line'><b></TD><TD class='line'><b>" . TRANS('TOTAL') . ": <font color='red'>" . $totalFull . "</font></TD><TD class='line'><b>" . TRANS('100_PERCENT') . "</b></TD></tr>";
print "</TABLE>";

$valores = "";
for ($i = 0; $i < count($dados); $i++) {
    $valores .= "data%5B%5D=" . $dados[$i] . "&";
}
for ($i = 0; $i < count($legenda); $i++) {
    $valores .= "legenda%5B%5D=" . $legenda[$i] . "&";
}
$valores = substr($valores, 0, -1);

print "</fieldset>";


$nome = "titulo=Gráfico de equipamentos por situação.";
$msgInst = "Unidade: " . $msgInst;

if (!$hide_buttom) {
	
	echo "<br/><p class='center'><input type='button' class='button' value='" . TRANS('BT_GRAPH') . "' onClick=\"return popup('graph_geral_pizza.php?" . $valores . "&" . $nome . "&instituicao=" . $msgInst . "')\"></p>";
    
}


print "</BODY>";
print "</HTML>";
?>
<script type='text/javascript'>
<!--
	function newTarget()
	{
		if (document.form1.checkprint.checked) {
			document.form1.target = "_blank";
			document.form1.submit();
		} else {
			document.form1.target = "";
			document.form1.submit();
		}
	}
	-->
</script>
