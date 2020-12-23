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

$auth = new auth($_SESSION['s_logado']);
$auth->testa_user($_SESSION['s_usuario'], $_SESSION['s_nivel'], $_SESSION['s_nivel_desc'], 2);

function ordenaPorColunas($array, $col)
{
    ksort($array);
    $arrayIndexado = array();
    $i = 0;
    foreach ($array as $nome => $link) {
        $arrayIndexado[$i][$nome] = $link;
        $i++;
    }
    $tamanho = count($array); //quantidade de elementos do array
    $partlen = floor($tamanho / $col); //quantidade de elementos por coluna (ajustar o valor do resto da divisao)
    $resto = $tamanho % $col;
    $arrayLinha = array();
    $i = 0;
    $elemento = 1;
    $coluna_size = 0;
    $coluna_index = 0;
    $cont = 0;
    $colunaX = array();

    for ($j = 0; $j < $col; $j++) {
        if ($resto > 0) {
            $colunaX[$j] = $partlen + 1;
            $resto--;
        } else {
            $colunaX[$j] = $partlen;
        }
    }

    foreach ($arrayIndexado as $indice => $array2) {
        foreach ($array2 as $nome => $link) {
            $arrayLinha[$i][$nome] = $link;
            if ($elemento < $colunaX[$cont]) {
                $elemento++;
            } else {
                $elemento = 1;
                $coluna_index++;
                $cont++;
                $i = $coluna_index;
            }
            if ($elemento != 1) {
                $i += $col;
            }
        }
    }
    ksort($arrayLinha);
    return $arrayLinha;
}

$colunas = 3;
$REP = array();

$REP[TRANS('TTL_REP_EST_GENERAL_EQUIP')] = "estat_geral.php";
$REP[TRANS('SUBTTL_ALTER_HW_PERIOD')] = "hw_alteracoes.php";
$REP[TRANS('TOP_TEN_MODELS_RECORD')] = "estat_topten_modelo.php";
$REP[TRANS('PIECES_BY_TECHNICIAN')] = "pieces_x_technician.php";
$REP[TRANS('TTL_QTD_EQUIP_CAD_FOR_DAY')] = "estat_equippordia.php";
$REP[TRANS('TTL_QTD_EQUIP_CAD_FOR_LOCAL')] = "estat_equipporlocal.php";
$REP[TRANS('TTL_COMP_X_SECTOR')] = "estat_compporlocal.php";
$REP[TRANS('TTL_COMP_X_MEMORY')] = "estat_comppormemoria.php";
$REP[TRANS('TTL_MODEL_X_MEMORY')] = "estat_modelo_memoria.php";
$REP[TRANS('TTL_COMP_X_PROCESSOR')] = "estat_compporprocessador.php";
$REP[TRANS('TTL_COMP_X_HD')] = "estat_compporhd.php";
$REP[TRANS('TTL_SIT_GENERAL_EQUIP')] = "estat_situacao_geral.php";
$REP[TRANS('TTL_EQUIP_X_SITUAC')] = "estat_equipporsituacao.php";
$REP[TRANS('TTL_DIST_GENERAL_EQUIP_FOR_UNIT')] = "estat_instituicao.php";
$REP[TRANS('TTL_EQUIP_X_MAJOR')] = "estat_equipporreitoria_agrup.php";
$REP[TRANS('TTL_EQUIP_X_DOMAIN')] = "estat_equippordominio.php";
$REP[TRANS('TTL_EXPIRAT_GUARANTEE')] = "estat_vencimentos.php";

$cab = new headers;
$cab->set_title(TRANS('TTL_OCOMON'));

print "<br/><h1>" . TRANS('GENERAL_REPORTS') . "</h1><br/>";

print "<p><B>" . TRANS('TXT_REPORTS_OPTIONS_1') . " <a href='consulta_comp.php'>" . TRANS('TLT_HERE') . "</a> " . TRANS('TXT_REPORTS_OPTIONS_2') . "</B></p><br/>";
//print "<br><a href=relatorio_geral.php>Relatório geral.</a></br>";

print "<TABLE border='0' cellpadding='5' cellspacing='0' align='center' width='100%'>";
print "<TR class='header'><td class='line' colspan='" . $colunas . "'>" . TRANS('GENERAL_REPORTS') . "</TD></tr>";

// print "<TR class='lin_par'>" .
// "<td class='line'><a href='estat_geral.php'>" . TRANS('TTL_REP_EST_GENERAL_EQUIP') . "</a></TD>" .
// "<td class='line'><a href='hw_alteracoes.php'>" . TRANS('SUBTTL_ALTER_HW_PERIOD') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_impar'>" .
// "<td class='line'><a href='estat_topten_modelo.php'>" . TRANS('TOP_TEN_MODELS_RECORD') . "</a></TD>" .
// "<td class='line'><a href='pieces_x_technician.php'>" . TRANS('PIECES_BY_TECHNICIAN') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_par'>" .
// "<td class='line' colspan='2'><a href='estat_equippordia.php'>" . TRANS('TTL_QTD_EQUIP_CAD_FOR_DAY') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_impar'>" .
// "<td class='line' colspan='2'><a href='estat_equipporlocal.php'>" . TRANS('TTL_QTD_EQUIP_CAD_FOR_LOCAL') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_par'>" .
// "<td class='line' colspan='2'><a href='estat_compporlocal.php'>" . TRANS('TTL_COMP_X_SECTOR') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_impar'>" .
// "<td class='line' colspan='2'><a href='estat_comppormemoria.php'>" . TRANS('TTL_COMP_X_MEMORY') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_par'>" .
// "<td class='line' colspan='2'><a href='estat_modelo_memoria.php'>" . TRANS('TTL_MODEL_X_MEMORY') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_impar'>" .
// "<td class='line' colspan='2'><a href='estat_compporprocessador.php'>" . TRANS('TTL_COMP_X_PROCESSOR') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_par'>" .
// "<td class='line' colspan='2'><a href='estat_compporhd.php'>" . TRANS('TTL_COMP_X_HD') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_impar'>" .
// "<td class='line' colspan='2'><a href='estat_situacao_geral.php'>" . TRANS('TTL_SIT_GENERAL_EQUIP') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_par'>" .
// "<td class='line' colspan='2'><a href='estat_equipporsituacao.php'>" . TRANS('TTL_EQUIP_X_SITUAC') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_impar'>" .
// "<td class='line' colspan='2'><a href='estat_instituicao.php'>" . TRANS('TTL_DIST_GENERAL_EQUIP_FOR_UNIT') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_par'>" .
// "<td class='line' colspan='2'><a href='estat_equipporreitoria_agrup.php'>" . TRANS('TTL_EQUIP_X_MAJOR') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_impar'>" .
// "<td class='line' colspan='2'><a href='estat_equippordominio.php'>" . TRANS('TTL_EQUIP_X_DOMAIN') . "</a></TD>" .
//     "</TR>";

// print "<TR class='lin_par'>" .
// "<td class='line' colspan='2'><a href='estat_vencimentos.php'>" . TRANS('TTL_EXPIRAT_GUARANTEE') . "</a></TD>" .
//     "</TR>";

$ctdTD = 1;
$indCol = 0;
print "<tr>";
$checked = "";
$i = 0;
$j = 0;

$REP2 = array();
$REP2 = ordenaPorColunas($REP, $colunas);


//EXIBICAO COM ORDENACAO DINAMICA
foreach ($REP2 as $indice) {
    foreach ($indice as $key => $value) {

        $class = (isImpar($j) ? 'lin_par' : 'lin_impar');

        if ($ctdTD == 1) {
            print "<tr class='" . $class . "'>";
            $j++;
        }
        print "<td class='line' colspan = '" . (($i+1) == count($REP2) && ((count($REP2)-$j)!= 0 ) ? count($REP2)-$j : '') . "'><a href='" . $value . "'>" . $key . "</a></TD>";

        if ($ctdTD == $colunas) {
            print "</tr>";
            $ctdTD = 1;
        } else {
            $ctdTD++;
        }
        $i++;
    }
}
print "</TR>";



print "</table>";
print "</BODY>";
print "</HTML>";
