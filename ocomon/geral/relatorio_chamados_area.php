<?php
/* Copyright 2020 Flávio Ribeiro

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
 */ session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    // header("Location: ../../index.php");
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

$_SESSION['s_page_ocomon'] = $_SERVER['PHP_SELF'];

$json = 0;
$json2 = 0;

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <!-- <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" /> -->
    <!-- <link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2.min.css" /> -->

    <style>
        .chart-container {
            position: relative;
            /* height: 100%; */
            max-width: 100%;
            margin-left: 10px;
            margin-right: 10px;
            margin-bottom: 30px;
        }
    </style>

    <title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>
    <?= $auth->showHeader(); ?>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>


    <div class="container">
        <h5 class="my-4"><i class="fas fa-headset text-secondary"></i>&nbsp;<?= TRANS('TTL_BOARD_CALL_AREA_ATTEND'); ?></h5>
        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetails">
                    </div>
                </div>
            </div>
        </div>

        <?php
        if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
            echo $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }


        if (!isset($_POST['action'])) {

        ?>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <div class="form-group row my-4">
                    <label for="area" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RESPONSIBLE_AREA'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control sel2" id="area" name="area">
                            <option value="-1"><?= TRANS('ALL'); ?></option>
                            <?php
                            $sql = "SELECT * FROM sistemas WHERE sis_atende = 1 AND sis_status NOT IN (0) ORDER BY sistema";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll() as $rowArea) {
                                print "<option value='" . $rowArea['sis_id'] . "'";
                                echo ($rowArea['sis_id'] == $_SESSION['s_area'] ? ' selected' : '');
                                print ">" . $rowArea['sistema'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <label for="d_ini" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('START_DATE'); ?></label>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control " id="d_ini" name="d_ini" value="<?= date("01/m/Y"); ?>" autocomplete="off" required />
                    </div>

                    <label for="d_fim" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('END_DATE'); ?></label>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control " id="d_fim" name="d_fim" value="<?= date("d/m/Y"); ?>" autocomplete="off" required />
                    </div>


                    <div class="row w-100"></div>
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2 ">

                        <input type="hidden" name="action" value="search">
                        <button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_SEARCH'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>
                    

                </div>
            </form>
            <?php
        } else {

            $hora_inicio = ' 00:00:00';
            $hora_fim = ' 23:59:59';

            $terms = "";

            if ((!isset($_POST['d_ini'])) || (!isset($_POST['d_fim']))) {
                $_SESSION['flash'] = message('info', '', 'O período deve ser informado', '');
                redirect($_SERVER['PHP_SELF']);
            } else {

                $d_ini = $_POST['d_ini'] . $hora_inicio;
                $d_ini = dateDB($d_ini);

                $d_fim = $_POST['d_fim'] . $hora_fim;
                $d_fim = dateDB($d_fim);

                if ($d_ini <= $d_fim) {

                    if (isset($_POST['area']) && ($_POST['area'] == -1)) {
                        $query_areas = "SELECT  * FROM sistemas WHERE sis_status NOT IN (0) AND sis_atende = 1 ORDER BY sistema";
                        $terms = TRANS('NONE_FILTER');
                    } else
                    if (isset($_POST['area']) && $_POST['area'] != -1) {
                        $query_areas = "SELECT  * FROM sistemas WHERE sis_id IN (" . $_POST['area'] . ") ORDER BY sistema";

                        $getAreaName = "SELECT sistema FROM sistemas WHERE sis_id = " . $_POST['area'] . " ";
                        $resGetArea = $conn->query($getAreaName);
                        $terms = TRANS('FILTERED_AREA') . ": ". $resGetArea->fetch()['sistema'];
                    }
                    $exec_qry_areas = $conn->query($query_areas);
                    $linhas = $exec_qry_areas->rowCount();
        
                    if ($linhas == 0) {
                        $_SESSION['flash'] = message('info', '', TRANS('MSG_NO_DATA_IN_PERIOD'), '');
                        redirect($_SERVER['PHP_SELF']);
                    } else {

                        ?>
                        <p><?= TRANS('TTL_PERIOD_FROM') . "&nbsp;" . dateScreen($d_ini, 1) . "&nbsp;" . TRANS('DATE_TO') . "&nbsp;" . dateScreen($d_fim, 1); ?></p>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <!-- table-hover -->
                                <caption><?= $terms; ?></caption>
                                <thead>
                                    <tr class="header table-borderless">
                                        <td class="line"><?= mb_strtoupper(TRANS('SERVICE_AREA')); ?></td>
                                        <td class="line"><?= mb_strtoupper(TRANS('OPENED')); ?></td>
                                        <td class="line"><?= mb_strtoupper(TRANS('COL_CLOSED')); ?></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $data = [];
                                    $total = 0;

                                    $totalAbertos = 0;
                                    $totalFechados = 0;
                                    $tAbertos = 0;
                                    $tFechados = 0;
                                    $i = 0;
                                    foreach ($exec_qry_areas->fetchall() as $row) {
                                        
                                        $query_ab_sw = "SELECT count(*) AS abertos, s.sistema AS area
                                                                FROM ocorrencias AS o, sistemas AS s
                                                                WHERE o.sistema = s.sis_id AND o.data_abertura >= '" . $d_ini . "' AND
                                                                o.data_abertura <= '" . $d_fim . "' and s.sis_id in (" . $row['sis_id'] . ") GROUP BY area";
                                        $exec_ab_sw = $conn->query($query_ab_sw);
                                        $row_ab_sw = $exec_ab_sw->fetch();
                        
                                        $query_fe_sw = "SELECT count(*) AS fechados, s.sistema AS area, s.sis_id
                                                                FROM ocorrencias AS o, sistemas AS s
                                                                WHERE o.sistema = s.sis_id AND o.data_fechamento >= '" . $d_ini . "' AND
                                                                o.data_fechamento <= '" . $d_fim . "' and s.sis_id in (" . $row['sis_id'] . ")  GROUP BY area";
                                        $exec_fe_sw = $conn->query($query_fe_sw);
                                        $row_fe_sw = $exec_fe_sw->fetch();
                        
                                        $tAbertos += $totalAbertos += $tt_ab = $row_ab_sw['abertos'];
                                        $tFechados += $totalFechados += $tt_fe = $row_fe_sw['fechados'];
                        
                                        $data[$i]['area'] = $row['sistema'];
                                        $data[$i]['abertos'] = $totalAbertos;
                                        $data[$i]['fechados'] = $totalFechados;

                                        $totalAbertos = 0;
                                        $totalFechados = 0;
                                        ?>
                                        <tr>
                                            <td class="line"><?= $data[$i]['area']; ?></td>
                                            <td class="line"><?= $data[$i]['abertos']; ?></td>
                                            <td class="line"><?= $data[$i]['fechados']; ?></td>
                                        </tr>
                                        <?php
                                        $i++;
                                    }


                                    $json = json_encode($data);
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="header table-borderless">
                                        <td><?= TRANS('TOTAL'); ?></td>
                                        <td ><?= $tAbertos; ?></td>
                                        <td ><?= $tFechados; ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="chart-container">
                            <canvas id="canvasChart1"></canvas>
                        </div>
                        
                        <?php
                        // var_dump([
                        //     'Query' => $query,
                        //     'Data' => $data,
                        //     'Json normal' => $json,
                        // ]);
                    }
                } else {
                    $_SESSION['flash'] = message('info', '', TRANS('MSG_COMPARE_DATE'), '');
                    redirect($_SERVER['PHP_SELF']);
                }
            }
        }
        ?>
    </div>
    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/dist/Chart.min.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-colorschemes/dist/chartjs-plugin-colorschemes.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-datalabels/chartjs-plugin-datalabels.min.js"></script>
    <script type='text/javascript'>
        $(function() {
            $("#d_ini").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
            });
            //idDataFinal
            $("#d_fim").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
            });

            $('#idSubmit').on('click', function() {
                $('.loading').show();
            });

            if (<?= $json ?> != 0) {
                showChart('canvasChart1');
            }

        });


        function showChart(canvasID) {
            var ctx = $('#' + canvasID);
            var dataFromPHP = <?= $json; ?>;

            var areasVar = []; // X Axis Label
            var abertosVar = []; // Value and Y Axis basis
            var fechadosVar = []; // Value and Y Axis basis

            for (var i in dataFromPHP) {
                areasVar.push(dataFromPHP[i].area);
                abertosVar.push(dataFromPHP[i].abertos);
                fechadosVar.push(dataFromPHP[i].fechados);
            }

            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: areasVar,
                    datasets: [{
                        label: '<?= TRANS('OPENED','',1);?>',
                        data: abertosVar,
                        borderWidth: 2
                    }, {
                        label: '<?= TRANS('COL_CLOSED','',1)?>',
                        data: fechadosVar,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: '<?= TRANS('TTL_BOARD_GENERAL_CALL_PERIOD','',1);?>',
                    },
                    scales: {
                        xAxes: [
                        {
                            display: true,
                            stacked: true,
                        },
                        ],
                        yAxes: [{
                            stacked: true,
                            display: true,
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    plugins: {
                        colorschemes: {
                            scheme: 'brewer.Paired12'
                        },
                        datalabels: {
                            display: function(context) {
                                return context.dataset.data[context.dataIndex] >= 1; // or !== 0 or ...
                            },
                            // formatter: (value, ctx) => {
                            //     let sum = ctx.dataset._meta[0].abertosVar;
                            //     let percentage = (value * 100 / sum).toFixed(2) + "%";
                            //     return percentage;
                            // }
                        },
                    },
                }
            });
        }

   </script>
</body>

</html>