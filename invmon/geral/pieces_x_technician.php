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

if (!isset($_POST['ok']) && !isset($_GET['action'])) {
    print "<html>";
    print "<head>"; 

    print "<link rel='stylesheet' href='../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css' />";
    print "<script type='text/javascript' src='../../includes/components/jquery/jquery.js'></script>";
    print "<script type='text/javascript' src='../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js'></script>";
    
    ?>
    <script>
    $( function() {
        $( "#d_ini" ).datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
            dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
            dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
            monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro','Janeiro'],
            monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez','Jan'],
        });
        //idDataFinal
        $( "#d_fim" ).datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
            dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
            dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
            monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro','Janeiro'],
            monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez','Jan'],
        });

    } );
    </script>
    
    <?php    
    
    print "</head>";
    print "	<BR><BR>";
    print "	<h2 class='center'>::: " . TRANS('TTL_PIECES_BY_TECHNICIAN') . " :::</h2><BR><BR>";
    print "		<FORM action='" . $_SERVER['PHP_SELF'] . "' method='post' name='form1' onSubmit=\"return valida();\">";
    print "		<TABLE border='0' class='center' cellspacing='2' >";

    print "				<tr>";
    print "					<td class='line' align='right'>" . TRANS('START_DATE') . ":</td>";
    print "					<td><INPUT type='text' name='d_ini' class='data' id='d_ini' value='01/" . date("m/Y") . "' autocomplete='off'></td>";
    print "				</tr>";
    print "				<tr>";
    print "					<td class='line' align='right'>" . TRANS('END_DATE') . ":</td>";
    print "					<td><INPUT type='text' name='d_fim' class='data' id='d_fim' value='" . date("d/m/Y") . "' autocomplete='off'></td>";
    print "				</tr>";
    print "<tr><td colspan='2'><input type='checkbox' name='checkprint'>&nbsp;" . TRANS('OPT_NEW_WINDOW') . "</td></tr>";
    print "		</TABLE><br>";
    print "		<TABLE class='center'>";
    print "			<tr>";
    print "	            <td class='line'>";
    print "					<input type='submit' class='button' value='" . TRANS('BT_SEARCH') . "' name='ok' >"; //onclick='ok=sim'
    print "	            </TD>";
    print "	            <td class='line'>";
    print "					<INPUT type='reset'  class='button' value='" . TRANS('BT_CLEAR') . "' name='cancelar'>";
    print "				</TD>";
    print "			</tr>";
    print "	    </TABLE>";
    print " </form>";
    ?>
				<script language="JavaScript">

					function valida(){
						var ok = validaForm('d_ini','DATA','<?php print TRANS('START_DATE');?>',0);
						if (ok) var ok = validaForm('d_fim','DATA','<?php print TRANS('END_DATE');?>',0);

						if (ok) newTarget();

						return ok;
					}


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
				</script>
		<?php
//if $ok!=Pesquisar
} else
if (isset($_POST['ok'])) {

    $hora_inicio = ' 00:00:00';
    $hora_fim = ' 23:59:59';

    $query = "SELECT " .
        "*, count(*) as total, t.nome as tecnico, t.user_id as tecnico_cod " .
        "FROM " .

        "hist_pieces h " .
        "left join instituicao inst on inst.inst_cod = h.hp_comp_inst " .

        "left join usuarios t on t.user_id = h.hp_technician, " .

        "estoque e, itens i, modelos_itens m, localizacao l, usuarios u " .
        "WHERE " .
        "h.hp_piece_id = e.estoq_cod and " .
        "e.estoq_tipo = i.item_cod and " .
        "m.mdit_cod = e.estoq_desc and " .
        "m.mdit_tipo = i.item_cod and " .
        "h.hp_piece_local = l.loc_id and " .
        "h.hp_uid = u.user_id ";

    if ((!isset($_POST['d_ini'])) || ((!isset($_POST['d_fim'])))) {

        print "<script>window.alert('" . TRANS('MSG_ALERT_PERIOD') . "'); history.back();</script>";
    } else {
        $d_ini = str_replace("-", "/", $_POST['d_ini']);
        $d_fim = str_replace("-", "/", $_POST['d_fim']);
        $d_ini_nova = converte_dma_para_amd($d_ini);
        $d_fim_nova = converte_dma_para_amd($d_fim);

        $d_ini_completa = $d_ini_nova . $hora_inicio;
        $d_fim_completa = $d_fim_nova . $hora_fim;

        if ($d_ini_completa <= $d_fim_completa) {

			echo "<br/><br/><p class='bold center'>" . TRANS('TTL_PERIOD_FROM') . " " . $d_ini . " a " . $d_fim . "</p>";

            $query .= " and h.hp_date between '" . $d_ini_completa . "' and '" . $d_fim_completa . "' ";

            $query .= "GROUP BY h.hp_technician " .
                "ORDER BY h.hp_date DESC";

            // print $query;
            $resultado = mysql_query($query) or die(TRANS('MSG_ERR_GET_DATA'));
            $linhas = mysql_num_rows($resultado);

            if ($linhas == 0) {
                $aviso = TRANS('MSG_NO_DATA_IN_PERIOD');
                echo "<script>mensagem('" . $aviso . "'); redirect('" . $_SERVER['PHP_SELF'] . "');</script>";
            } else { //if($linhas==0)
                echo "<br><br>";
                $background = '#CDE5FF';

                print "<table class='centro' cellspacing='0' border='1' width='30%'>";

                print "<tr class='header'><td class='line'><B>" . TRANS('COL_AMOUNT') . "</td>
								<td class='line' ><B>" . TRANS('TECHNICIAN') . "</td>
							</tr>";
                $total = 0;
                while ($row = mysql_fetch_array($resultado)) {

                    print "<tr>";
                    print "<td class='line'>" . $row['total'] . "</td>";
                    print "<td class='line'><a onClick=\"javascript: popup_alerta('" . $_SERVER['PHP_SELF'] . "?action=list&tech=" . $row['tecnico_cod'] . "&date1=" . $d_ini_completa . "&date2=" . $d_fim_completa . "')\">" . NVL($row['tecnico']) . "</a></td>";

                    print "</tr>";
                    $total += $row['total'];
                }

                print "<tr><td class='line'><b>" . TRANS('TOTAL') . "</b></td><td class='line'><b>" . $total . "</b></td></tr>";

            } //if($linhas==0)
        } else {

            $aviso = TRANS('MSG_COMPARE_DATE');
            print "<script>mensagem('" . $aviso . "'); redirect('" . $_SERVER['PHP_SELF'] . "');</script>";
        }
    } //if ((empty($d_ini)) and (empty($d_fim)))
} else
if (isset($_GET['action']) && $_GET['action'] == 'list') {

    $query = "SELECT " .
        "*, t.nome as tecnico, t.user_id as tecnico_cod " .
        "FROM " .

        "hist_pieces h " .
        "left join instituicao inst on inst.inst_cod = h.hp_comp_inst " .

        "left join usuarios t on t.user_id = h.hp_technician, " .

        "estoque e, itens i, modelos_itens m, localizacao l, usuarios u " .
        "WHERE " .
        "h.hp_piece_id = e.estoq_cod and " .
        "e.estoq_tipo = i.item_cod and " .
        "m.mdit_cod = e.estoq_desc and " .
        "m.mdit_tipo = i.item_cod and " .
        "h.hp_piece_local = l.loc_id and " .
        "h.hp_uid = u.user_id and " .
        "t.user_id = " . $_GET['tech'] . " and " .
        "h.hp_date between  '" . $_GET['date1'] . "' and '" . $_GET['date2'] . "' " .
        "";
    $exec = mysql_query($query) or die($query);

    $linhas = mysql_num_rows($exec);

    print "<table border='0' cellspacing='1' summary=''";
    print "<TR>";
    print "<TD colspan='3' align='left' ><B>" . TRANS('WERE_FOUND') . " " . $linhas . " " . TRANS('TXT_REG_OF_PIECE_BY_TECH') . ":</B></TD>";
    print "</tr>";
    print "<tr><td>&nbsp;</td></tr>";
    print "</table>";

    print "<TABLE border='0' cellpadding='5' cellspacing='0' align='left' width='100%'>";

    print "<TR class='header'>" .
    "<TD class='line'>" . TRANS('COMPONENT') . "</TD>" .
    "<TD class='line'>" . TRANS('DEPARTMENT') . "</TD>" .
    "<td class='line'><b>" . TRANS('DATE') . "</TD>" .
    "<td class='line'>" . TRANS('TECHNICIAN') . "</td> ";
    $i = 0;
    $j = 2;
    while ($rowlist = mysql_fetch_array($exec)) {
        if ($j % 2) {
            $trClass = "lin_par";
        } else {
            $trClass = "lin_impar";
        }
        $j++;
        print "<tr class=" . $trClass . " id='linhax" . $j . "' onMouseOver=\"destaca('linhax" . $j . "','" . $_SESSION['s_colorDestaca'] . "');\" onMouseOut=\"libera('linhax" . $j . "','" . $_SESSION['s_colorLinPar'] . "','" . $_SESSION['s_colorLinImpar'] . "');\"  onMouseDown=\"marca('linhax" . $j . "','" . $_SESSION['s_colorMarca'] . "');\">";
        print "<td class='line'>" . $rowlist['item_nome'] . "&nbsp;" . $rowlist['mdit_desc'] . "</td>";
        print "<td class='line'>" . NVL($rowlist['local']) . "</td>";
        print "<td class='line'>" . NVL(formatDate($rowlist['hp_date'])) . "</td>";
        print "<td class='line'>" . NVL($rowlist['tecnico']) . "</td>";

        print "</TR>";
    }
    print "</table>";
}

?>
			<script type='text/javascript'>
			<!--
				function popup(pagina)	{ //Exibe uma janela popUP
					x = window.open(pagina,'popup','width=400,height=200,scrollbars=yes,statusbar=no,resizable=yes');
					//x.moveTo(100,100);
					x.moveTo(window.parent.screenX+100, window.parent.screenY+100);
					return false
				}

				function popup_alerta(pagina)	{ //Exibe uma janela popUP
					x = window.open(pagina,'_blank','width=700,height=470,scrollbars=yes,statusbar=no,resizable=yes');
					//x.moveTo(100,100);
					x.moveTo(window.parent.screenX+50, window.parent.screenY+50);
					return false
				}


			-->
			</script>

		<?php

print "</BODY>";
print "</html>";
?>