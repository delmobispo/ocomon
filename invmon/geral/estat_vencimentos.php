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

$query = $QRY["vencimentos"];
$result = mysql_query($query);

//----------------TABELA  -----------------//
print "<br/><br/><p class='center'>" . TRANS('TTL_PREVIEWS_EXP_GUARANTEE') . ": <a href='estat_vencimentos_full.php'>" . TRANS('SHOW_FULL_5_YEARS') . "</a></p><br/>";
print "<table cellspacing='0' border='1' class='center'>";
print "<tr class='header'><td class='line' ><b>" . TRANS('DATE') . "</b></td><td class='line'><b>" . TRANS('COL_AMOUNT') . "</b></td><td class='line' ><b>" . TRANS('COL_TYPE') . "</b></td><td  class='line' ><b>" . TRANS('COL_MODEL_2') . "</b></td></tr>";
//-----------------FINAL DA TABELA  -----------------------//

$tt_garant = 0;
while ($row = mysql_fetch_array($result)) {
    $temp1 = explode(" ", $row['vencimento']);
    $temp = explode(" ", datab($row['vencimento']));
    $vencimento1 = $temp1[0];
    $vencimento = $temp[0];
    $tt_garant += $row['quantidade'];
    print "<tr><td class='line'><a onClick=\"popup('equipments_list.php?VENCIMENTO=" . $vencimento1 . "')\">" . $vencimento . "</a></td>" .
        "<td class='line'><a onClick=\"popup('equipments_list.php?VENCIMENTO=" . $vencimento1 . "')\">" . $row['quantidade'] . "</a></td>" .
        "<td class='line'>" . $row['tipo'] . "</td><td class='line'>" . $row['fabricante'] . " " . $row['modelo'] . "</td></tr>";
} // while
print "<tr><td class='line'><b>" . TRANS('TOTAL') . "</b></td><td class='line' colspan='3'><b>" . $tt_garant . "</b></td></tr>";
print "</table><br><br>";



print "</BODY>";
print "</HTML>";
