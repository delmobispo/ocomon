<?php /*                        Copyright 2005 Flávio Ribeiro

         This file is part of OCOMON.

         OCOMON is free software; you can redistribute it and/or modify
         it under the terms of the GNU General Public License as published by
         the Free Software Foundation; either version 2 of the License, or
         (at your option) any later version.

         OCOMON is distributed in the hope that it will be useful,
         but WITHOUT ANY WARRANTY; without even the implied warranty of
         MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
         GNU General Public License for more details.

         You should have received a copy of the GNU General Public License
         along with Foobar; if not, write to the Free Software
         Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  */ session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <title>OcoMon&nbsp;<?= VERSAO; ?></title>
    <style>
        #spanTktNumber {
            cursor: pointer;
        }
        hr.thick {
			border: 1px solid;
			border-radius: 5px;
		}
    </style>
</head>

<body>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>
    <div class="container-fluid">
        <?php

        if (isset($_GET['numero'])) {


            //CHECA SE O PRIMEIRO REGISTRO DE LOG JA EXISTE - SE NAO EXISTIR GRAVA O ESTADO ATUAL
            $firstLog = firstLog($conn, $_GET['numero'], 'NULL', 1);

            $sql_log = "SELECT * FROM ocorrencias_log WHERE log_numero = '" . $_GET['numero'] . "' ORDER BY log_id";
            $commit_log = $conn->query($sql_log);
            $existe_log = $commit_log->rowCount();

            $tipoAtendimento = array();

            $log = array();
            $i = 0;

            if ($existe_log) {

                foreach ($commit_log->fetchall() as $row_log) {

                    $log[$i]['IDX_DATA'] = dateScreen($row_log['log_data']);

                    $sql = "SELECT nome FROM usuarios WHERE user_id = '" . $row_log['log_quem'] . "'";
                    $commit = $conn->query($sql);
                    $row = $commit->fetch();

                    $log[$i]['IDX_QUEM'] = $row['nome'];
                    $log[$i]['FLAGGED'] = 0;


                    if ($row_log['log_descricao'] != "") {
                        $log[$i]['IDX_DESCRICAO'] = noHtml($row_log['log_descricao']);
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_prioridade'] != "" && $row_log['log_prioridade'] != "0" && $row_log['log_prioridade'] != "-1") {

                        $sql = "SELECT pr_desc FROM prior_atend WHERE pr_cod = '" . $row_log['log_prioridade'] . "'";
                        $commit = $conn->query($sql);
                        $row = $commit->fetch();

                        $log[$i]['IDX_PRIORIDADE'] = $row['pr_desc'];
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_area'] != "" && $row_log['log_area'] != "0" && $row_log['log_area'] != "-1") {

                        $sql = "SELECT sistema FROM sistemas WHERE sis_id = '" . $row_log['log_area'] . "'";
                        $commit = $conn->query($sql);
                        $row = $commit->fetch();

                        $log[$i]['IDX_AREA'] = $row['sistema'];
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_problema'] != "" && $row_log['log_problema'] != "0" && $row_log['log_problema'] != "-1") {

                        $sql = "SELECT problema FROM problemas WHERE prob_id = '" . $row_log['log_problema'] . "'";
                        $commit = $conn->query($sql);
                        $row = $commit->fetch();

                        //$log[$i]['SOLICITACAO'] = $row['problema'];
                        $log[$i]['IDX_PROB'] = $row['problema'];
                        $log[$i]['FLAGGED'] = 1;
                    }


                    if ($row_log['log_unidade'] != "" && $row_log['log_unidade'] != "0" && $row_log['log_unidade'] != "-1") {

                        $sql = "SELECT inst_nome FROM instituicao WHERE inst_cod = '" . $row_log['log_unidade'] . "'";
                        $commit = $conn->query($sql);
                        $row = $commit->fetch();

                        $log[$i]['IDX_UNIDADE'] = $row['inst_nome'];
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_etiqueta'] != "") {
                        $log[$i]['IDX_ETIQUETA'] = $row_log['log_etiqueta'];
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_contato'] != "") {
                        $log[$i]['IDX_CONTATO'] = $row_log['log_contato'];
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_contato_email'] != "") {
                        $log[$i]['IDX_CONTATO_EMAIL'] = $row_log['log_contato_email'];
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_telefone'] != "") {
                        $log[$i]['IDX_TELEFONE'] = $row_log['log_telefone'];
                        $log[$i]['FLAGGED'] = 1;
                    }


                    if ($row_log['log_departamento'] != "" && $row_log['log_departamento'] != "0" && $row_log['log_departamento'] != "-1") {

                        $sql = "SELECT local FROM localizacao WHERE loc_id = '" . $row_log['log_departamento'] . "'";
                        $commit = $conn->query($sql);
                        $row = $commit->fetch();

                        $log[$i]['IDX_DEPARTMENT'] = $row['local'];
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_responsavel'] != "") {

                        $sql = "SELECT nome FROM usuarios WHERE user_id = '" . $row_log['log_responsavel'] . "'";
                        $commit = $conn->query($sql);
                        $row = $commit->fetch();

                        $log[$i]['IDX_RESPONSAVEL'] = $row['nome'];
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_data_agendamento'] != "") {
                        $log[$i]['IDX_AGENDAMENTO'] = dateScreen($row_log['log_data_agendamento']);
                        $log[$i]['FLAGGED'] = 1;
                    }


                    if ($row_log['log_status'] != "") {

                        $sql = "SELECT `status` FROM status WHERE stat_id = '" . $row_log['log_status'] . "'";
                        $commit = $conn->query($sql);
                        $row = $commit->fetch();

                        $log[$i]['IDX_STATUS'] = $row['status'];
                        $log[$i]['FLAGGED'] = 1;
                    }

                    if ($row_log['log_tipo_edicao'] != "") {

                        // if ($row_log['log_tipo_edicao'] == 0) $operation_type = TRANS('OPT_OPERATION_TYPE_OPEN');
                        // elseif ($row_log['log_tipo_edicao'] == 1) $operation_type = TRANS('OPT_OPERATION_TYPE_EDIT');
                        // elseif ($row_log['log_tipo_edicao'] == 2) $operation_type = TRANS('OPT_OPERATION_TYPE_ATTEND');
                        // elseif ($row_log['log_tipo_edicao'] == 3) $operation_type = TRANS('OPT_OPERATION_TYPE_REOPEN');
                        // elseif ($row_log['log_tipo_edicao'] == 4) $operation_type = TRANS('OPT_OPERATION_TYPE_CLOSE');
                        // elseif ($row_log['log_tipo_edicao'] == 5) $operation_type = TRANS('OPT_OPERATION_TYPE_ATTRIB');
                        // elseif ($row_log['log_tipo_edicao'] == 6) $operation_type = TRANS('OPT_OPERATION_SCHEDULE');
                        // else
                        //     $operation_type = TRANS('OPT_OPERATION_NOT_LABELED');

                        // $log[$i]['IDX_OPERACAO'] = $operation_type;
                        
                        
                        $log[$i]['IDX_OPERACAO'] = getOperationType($row_log['log_tipo_edicao']);
                        $log[$i]['FLAGGED'] = 1;
                    }
                    $i++;
                }
                ?>

                <h5 class="my-4" id="spanTktNumber" data-link="<?= $_GET['numero']; ?>"><i class="fas fa-file-signature text-secondary"></i>&nbsp;<?= TRANS('IDX_TICKET_LOGS'); ?>&nbsp;<span class="badge badge-secondary pt-2">N.º <?= $_GET['numero']; ?></span></h5> <!-- fa-exchange-alt  -->

                <div class="table-responsive">
                    <!-- <div class="card "> -->
                    <table class="table" width="100%">
                        <?php
                        $x = 0;
                        foreach ($log as $l) {
                            $j = 0;
                            $x++;

                            foreach ($l as $k => $valor) {

                                $CONT = count($l) - 1; //MENOS A QUANTIDADE DE INDICES QUE ESTOU OMITINDO - NO CASO, O INDICE = 'FLAGGED'

                                if ($l['FLAGGED'] == 1) {

                                    if ($k != "FLAGGED") {
                                        ?>
                                        <tr>
                                            <?php

                                            if ($j == 0) /* primeira iteração do registro */
                                                // print "<td class='table-secondary' rowspan='" . $CONT . "' width='15%'><p class='font-weight-bold'>" . TRANS('IDX_EDIT') . "&nbsp;" . $x . "</p></td>"; 
                                                print "<td rowspan='" . $CONT . "' width='20%'><h5 class='font-weight-bold'><span class='badge badge-secondary p-2'>" . ($k == 'IDX_DATA' ? $valor : '') . "</span></h5></td>"; 

                                            if ($k != 'IDX_DATA')
                                                print "<td class='font-weight-bold' width='20%'>" . TRANS($k) . "</td><td>" . $valor . "</td>";

                                            ?>
                                        </tr>
                                        <?php
                                        $j++;
                                    }
                                }
                            }
                            ?>
                            <tr class='table-borderless'>
                                <td colspan="3"><hr class="thick"></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </div>
                <div class="container-fluid">
                    <div class="row justify-content-end">
                        <div class="col-2"><button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_RETURN'); ?></button></div>
                    </div>
				</div>
            <?php
            }
        }
        ?>
    </div>
    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
    <script>
        $(function() {

            $('#spanTktNumber').on('click', function() {
                var ticket = $(this).attr('data-link');
                redirect('ticket_show.php?numero=' + ticket);
            });

        });
    </script>
</body>

</html>