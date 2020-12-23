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

$queryB = "SELECT count(*) from equipamentos";
$resultadoB = mysql_query($queryB);
$total = mysql_result($resultadoB, 0);

// Select para retornar a quantidade e percentual de equipamentos cadastrados no sistema
$query = "SELECT count( i.inst_nome ) AS qtd_inst, i.inst_nome AS instituicao, i.inst_cod as inst_cod,
			concat( count( * ) / " . $total . " * 100, '%' ) AS porcento
			FROM instituicao AS i, equipamentos AS c
			WHERE c.comp_inst = i.inst_cod
			GROUP BY i.inst_nome order by qtd_inst desc";
//and (comp_tipo_equip=1 or comp_tipo_equip=2)
$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);

print "<br/><p class='bold center'>" . TRANS('TTL_ESTAT_EQUIP_FOR_UNIT') . "</p><br/>";


print "<fieldset style='max-width:55%; padding:10px; align:center; margin:auto;'><legend>" . TRANS('SUBTTL_INSTIT_BOARD') . "</legend>";
print "<TABLE class='center' border='0' cellpadding='5' cellspacing='0' width='60%' >";
print "<TR><TD class='line'><b>" . TRANS('COL_UNIT') . "</TD><TD class='line'><b>" . TRANS('COL_QTD') . "</TD><TD class='line'><b>" . TRANS('PERCENTAGE') . "</TD></tr>";

$i = 0;
$j = 2;

while ($row = mysql_fetch_array($resultado)) {
    $color = BODY_COLOR;
    $j++;
    print "<TR>";
    print "<TD bgcolor='" . $color . "'><a href='equipments_list.php?comp_inst[]=" . $row['inst_cod'] . "&ordena=local,etiqueta' title='" . TRANS('HNT_LIST_EQUIP_CAD_FOR_UNIT') . "'>" . $row['instituicao'] . "</a></TD>";
    print "<TD bgcolor='" . $color . "'>" . $row['qtd_inst'] . "</TD>";
    print "<TD bgcolor='" . $color . "'>" . round($row['porcento'], 2) . "</TD>";
    print "</TR>";
    $i++;
}

print "<TR><TD class='line'><b></TD><TD class='line'><b>" . TRANS('TOTAL') . ": " . $total . "</TD><TD class='line'><b>" . TRANS('100_PERCENT') . "</b></TD></tr>";
print "</TABLE>";
print "</fieldset>";



print "</BODY>";
print "</HTML>";
