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
$cab->set_title(TRANS("ttl_ocomon"));
$auth = new auth($_SESSION['s_logado']);
$auth->testa_user($_SESSION['s_usuario'], $_SESSION['s_nivel'], $_SESSION['s_nivel_desc'], 4);

print "<BODY bgcolor=" . BODY_COLOR . ">";
$hoje = date("d-m-Y H:i:s");

//     $cor  = TD_COLOR;
//     $cor1 = TD_COLOR;
//     $cor3 = BODY_COLOR;


// $teste = "teste";
// dbField(-1);
// exit;



$dados = array(); //Array que irá guardar os valores para montar o gráfico
$legenda = array();

$queryB = $QRY["total_equip"] . " where comp_inst not in (" . INST_TERCEIRA . ")";
$resultadoB = mysql_query($queryB);
$row = mysql_fetch_array($resultadoB);
//$total = mysql_result($resultadoB,0);
$total = $row["total"];

// Select para retornar a quantidade e percentual de equipamentos cadastrados no sistema
$query = "SELECT count(*) as Quantidade, count(*)*100/" . $total . " as Percentual, " .
    "T.tipo_nome as Equipamento, T.tipo_cod as tipo " .
    "FROM equipamentos as C, tipo_equip as T " .
    "WHERE C.comp_tipo_equip = T.tipo_cod and C.comp_inst not in (" . INST_TERCEIRA . ") " .
    "GROUP by C.comp_tipo_equip ORDER BY Quantidade desc,Equipamento";

$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);

print "<br/><p class='bold center'>" . TRANS("abert_titulo") . ": <font color='red'>" . $total . "</font></p><br/>";

print "<fieldset class='center'><legend>" . TRANS("quadro") . "</legend>";
print "<TABLE border='0' cellpadding='5' cellspacing='0' width='60%' class='center'>";
print "<TR><td class='line'><b>" . TRANS("COL_EQUIP") . "</TD><td class='line'><b>" . TRANS("qtd") . "</TD><td class='line'><b>" . TRANS("PERCENTAGE") . "</TD></tr>";
$i = 0;
$j = 2;

while ($row = mysql_fetch_array($resultado)) {
    $color = BODY_COLOR;
    $j++;
    print "<tr id='linha" . $j . "' onMouseOver=\"destaca('linha" . $j . "');\" onMouseOut=\"libera('linha" . $j . "');\"  " .
        "onMouseDown=\"marca('linha" . $j . "');\">";
    //print "<tr class=".$trClass." id='linhax".$j."' onMouseOver=\"destaca('linhax".$j."','".$_SESSION['s_colorDestaca']."');\" onMouseOut=\"libera('linhax".$j."','".$_SESSION['s_colorLinPar']."','".$_SESSION['s_colorLinImpar']."');\"  onMouseDown=\"marca('linhax".$j."','".$_SESSION['s_colorMarca']."');\">";
    print "<td class='line'><a href='equipments_list.php?comp_tipo_equip=" . $row['tipo'] . "' title='" . TRANS('list_all_assets_of_this_type', '', 0) . "'>" . $row['Equipamento'] . "</a></TD>";
    print "<td class='line'>" . $row['Quantidade'] . "</TD>";
    print "<td class='line'>" . round($row['Percentual'], 2) . "%</TD>";
    print "</TR>";
    $dados[] = $row['Quantidade'];
    $legenda[] = $row['Equipamento'];
    $i++;
}

print "<TR><td class='line'><b>" . TRANS('total', 'Total') . "</TD><td class='line'><b>" . $total . "</TD><td><b>100%</TD></tr>";

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

if ($linhas > 0) {

    print "<br/><TABLE align='center' style='margin:auto'>";

    print "<tr><td width=60% align=center><b><a href=equipments_list.php?visualiza=relatorio&ordena=equipamento," .
    "modelo,etiqueta title='" . TRANS('hint_relat_geral') . "'>" . TRANS('relat_geral') . "</a>.</b></td></tr>";
    print "</TABLE>";

    print "<TABLE align='center' style='margin:auto'>";

    $msgInst = "";
    $nome = "titulo=" . TRANS('tit_graf_geral') . "";
    print "<tr><td width=60% align=center><input type='button' class='button' value='" . TRANS('grafico', 'Gráfico', 0) . "' " .
        "onClick=\"return popup('graph_geral_barras.php?" . $valores . "&" . $nome . "&instituicao=" . $msgInst . "')\"></td></tr>";

    print "</TABLE>";
}

$cab->set_foot();
