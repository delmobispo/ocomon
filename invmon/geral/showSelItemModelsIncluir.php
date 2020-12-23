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

include "../../includes/include_geral.inc.php";
include "../../includes/include_geral_II.inc.php";

// require_once ("../../includes/include_basics_only.php");


// $auth = new auth($_SESSION['s_logado']);
// $auth->testa_user_hidden($_SESSION['s_usuario'], $_SESSION['s_nivel'], $_SESSION['s_nivel_desc'], 4);

$tipo = "";
$cod = "";


if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
}

if (isset($_GET['cod'])) {
    $cod = $_GET['cod'];
}

// if (empty($cod) && empty($tipo)) {
//     exit;
// }

print "<select class='select' name='comp_marca' id='idModelo'>";

$select = "SELECT * from marcas_comp ";

$select.= "WHERE 1 = 1 ";

if (!empty($tipo)) {
    $select .= "AND marc_tipo = '" . $tipo . "' ";
}

if (!empty($cod)) {
    $select .= "AND marc_cod = '" . $cod . "' ";
}

$select .= "order by marc_nome";

$exec = mysql_query($select);
print "<option value=-1>" . TRANS('SEL_MODEL') . "</option>";
while ($desc = mysql_fetch_array($exec)) {
    print "<option value=" . $desc['marc_cod'] . "";
    if ($desc['marc_cod'] == (!empty($cod)) ? $cod : '') {
        print " selected";
    }

    print ">" . $desc['marc_nome'] . "</option>";
} // while
print "</select>";
print "<input type='button' name='modelo' value='" . TRANS('NEW') . "' class='minibutton' onClick=\"javascript:popup_alerta('modelos.php?popup=true')\">";
