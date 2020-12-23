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
$query = "select count(l.local)as qtd, count(*)/" . $total . "*100 as porcento,
		l.local as local, l.loc_id as tipo_local, t.tipo_nome as equipamento, t.tipo_cod as tipo
		from equipamentos as c,
		tipo_equip as t, localizacao as l where c.comp_tipo_equip = t.tipo_cod
		and c.comp_local = l.loc_id  group by local, equipamento order by qtd desc ,
		local asc";

$resultado = mysql_query($query);
$linhas = mysql_num_rows($resultado);



print "<br/><br/><p class='center bold'>" . TRANS('TTL_TOTAL_EQUIP_CAD_FOR_LOCAL') . "</p>";

print "<fieldset class='center'><legend>" . TRANS('TTL_EQUIP_X_SECTOR') . "</legend>";
print "<TABLE border='0' cellpadding='5' cellspacing='0' class='center' width='100%' >";
print "<TR><TD class='line'><b>" . TRANS('DEPARTMENT') . "</TD><TD class='line'><b>" . TRANS('MNL_CAD_EQUIP') . "</TD><TD class='line'><b>" . TRANS('COL_QTD') . "</TD><TD class='line'><b>" . TRANS('PERCENTAGE') . "</TD></tr>";
$i = 0;
$j = 2;

while ($row = mysql_fetch_array($resultado)) {
    $color = BODY_COLOR;
    $j++;
    print "<TR>";
    print "<TD bgcolor='" . $color . "'><a href='equipments_list.php?comp_tipo_equip=" . $row['tipo'] . "&comp_local=" . $row['tipo_local'] . "&ordena=modelo,etiqueta' title='" . TRANS('HNT_SHOW_LIST_EQUIP_CAD_LOCAL') . "'>" . $row['local'] . "</TD>";
    print "<TD bgcolor='" . $color . "'>" . $row['equipamento'] . "</TD>";
    print "<TD bgcolor='" . $color . "'>" . $row['qtd'] . "</TD>";
    print "<TD bgcolor='" . $color . "'>" . round($row['porcento'], 2) . "%</TD>";
    print "</TR>";
    $i++;
}
print "<TR><TD class='line'><b></TD><TD class='line'><b></TD><TD class='line'><b>" . TRANS('TOTAL') . ": $total</TD><TD class='line'></TD></tr>";
print "</TABLE>";
print "</fieldset>";



//print "<tr><td class='line' align='center'><a href='" . $_SERVER['PHP_SELF'] . "' target='_blank')\">" . TRANS('NEW_SCREEN') . "</a></TD></tr>";


print "</BODY>";
print "</HTML>";
