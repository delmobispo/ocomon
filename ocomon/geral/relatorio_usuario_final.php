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
        <h5 class="my-4"><i class="fas fa-user text-secondary"></i>&nbsp;<?= TRANS('TTL_REP_CALL_OPEN_USER_FINISH'); ?></h5>
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


                    <label for="state" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('STATE'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control sel2" id="state" name="state">
                            <option value="1" selected><?= TRANS('STATE_OPEN_CLOSE_IN_SEARCH_RANGE'); ?></option>
                            <option value="2"><?= TRANS('STATE_OPEN_IN_SEARCH_RANGE'); ?></option>
                            <option value="3"><?= TRANS('STATE_OPEN_IN_SEARCH_RANGE_CLOSE_ANY_TIME'); ?></option>
                            <option value="4"><?= TRANS('STATE_OPEN_ANY_TIME_CLOSE_IN_SEARCH_RANGE'); ?></option>
                            <option value="5"><?= TRANS('STATE_JUST_OPEN_IN_SEARCH_RANGE'); ?></option>
                        </select>
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

            $criterio = "";
            $query = "SELECT count(*) AS qtd, o.*, u.*, a.*, n.* FROM ocorrencias AS o 
                        LEFT JOIN usuarios AS u ON o.aberto_por = u.user_id 
                        LEFT JOIN sistemas AS a ON a.sis_id = u.AREA 
                        LEFT JOIN nivel AS n ON nivel_cod = u.nivel 
                        WHERE a.sis_atende=0 AND n.nivel_cod=3 ";

            if (isset($_POST['area']) and ($_POST['area'] != -1) and (($_POST['area'] == $_SESSION['s_area']) || ($_SESSION['s_nivel'] == 1))) {
                $query .= " AND o.sistema = " . $_POST['area'] . "";
                $getAreaName = "SELECT * from sistemas where sis_id = " . $_POST['area'] . "";
                $exec = $conn->query($getAreaName);
                $rowAreaName = $exec->fetch();
                $nomeArea = $rowAreaName['sistema'];

                $criterio .= "Área filtrada: {$nomeArea}";
            } else
            if ($_SESSION['s_nivel'] != 1) {
                $_SESSION['flash'] = message('info', '', TRANS('MSG_CONSULT_FOR_YOU_AREA'), '');
                // echo "<script>redirect('" . $_SERVER['PHP_SELF'] . "')</script>";
                redirect($_SERVER['PHP_SELF']);
            } else {
                $criterio .= TRANS('FILTERED_AREA') . ": " . TRANS('NONE_FILTER');
            }


            if ((!isset($_POST['d_ini'])) || (!isset($_POST['d_fim']))) {
                $_SESSION['flash'] = message('info', '', TRANS('MSG_ALERT_PERIOD'), '');
                // echo "<script>redirect('" . $_SERVER['PHP_SELF'] . "')</script>";
                redirect($_SERVER['PHP_SELF']);
            } else {

                $d_ini = $_POST['d_ini'] . $hora_inicio;
                $d_ini = dateDB($d_ini);

                $d_fim = $_POST['d_fim'] . $hora_fim;
                $d_fim = dateDB($d_fim);

                if ($d_ini <= $d_fim) {

                    //Padrão: abertos e concluídos no range de pesquisa
                    $extraTerms = " AND oco_real_open_date >= '{$d_ini}' AND oco_real_open_date <= '{$d_fim}' 
                                    AND data_fechamento >= '{$d_ini}' AND data_fechamento <= '{$d_fim}' ";
                    $newTerms = TRANS('STATE') . ": " . TRANS('STATE_OPEN_CLOSE_IN_SEARCH_RANGE');
                    
                    if (isset($_POST['state']) && $_POST['state'] == 2) { // Não foram encerrados no período pesquisado
                        $extraTerms = " AND oco_real_open_date >= '{$d_ini}' AND oco_real_open_date <= '{$d_fim}' 
                                    AND (data_fechamento > '{$d_fim}' OR data_fechamento IS NULL) ";
                        $newTerms = TRANS('STATE') . ": " . TRANS('STATE_OPEN_IN_SEARCH_RANGE');
                    } elseif (isset($_POST['state']) && $_POST['state'] == 3) { // Abertos no período e concluídos em qualquer tempo
                        $extraTerms = " AND oco_real_open_date >= '{$d_ini}' AND oco_real_open_date <= '{$d_fim}' 
                                    AND data_fechamento IS NOT NULL ";
                        $newTerms = TRANS('STATE') . ": " . TRANS('STATE_OPEN_IN_SEARCH_RANGE_CLOSE_ANY_TIME');
                    } elseif (isset($_POST['state']) && $_POST['state'] == 4) { // Abertos em qualquer termpo e concluídos no período pesquisado
                        $extraTerms = " AND data_fechamento >= '{$d_ini}' AND data_fechamento <= '{$d_fim}' ";
                        $newTerms = TRANS('STATE') . ": " . TRANS('STATE_OPEN_ANY_TIME_CLOSE_IN_SEARCH_RANGE');
                    } elseif (isset($_POST['state']) && $_POST['state'] == 5) { // Abertos no período e não checa se foram concluídos
                        $extraTerms = " AND oco_real_open_date >= '{$d_ini}' AND oco_real_open_date <= '{$d_fim}' ";
                        $newTerms = TRANS('STATE') . ": " . TRANS('STATE_JUST_OPEN_IN_SEARCH_RANGE');
                    } 

                    if (strlen($criterio)) $criterio .= ", ";
                    $criterio .= $newTerms;

                    if (strlen($criterio) == 0) {
                        $criterio = TRANS('NONE_FILTER');
                    }

                    $query .= " {$extraTerms}
                            GROUP BY u.nome ORDER BY qtd desc,nome";

                    // $query .= " AND o.data_abertura >= '" . $d_ini . "' AND o.data_abertura <= '" . $d_fim . "' 
                    //             GROUP BY u.nome ORDER BY qtd desc,nome";
                    $resultado = $conn->query($query);
                    $linhas = $resultado->rowCount();

                    if ($linhas == 0) {
                        $_SESSION['flash'] = message('info', '', TRANS('MSG_NO_DATA_IN_PERIOD'), '');
                        // echo "<script>redirect('" . $_SERVER['PHP_SELF'] . "')</script>";
                        redirect($_SERVER['PHP_SELF']);
                    } else {

                        ?>
                        <p><?= TRANS('TTL_PERIOD_FROM') . " " . dateScreen($d_ini, 1) . " a " . dateScreen($d_fim, 1); ?></p>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <!-- table-hover -->
                                <caption><?= $criterio; ?></caption>
                                <thead>
                                    <tr class="header table-borderless">
                                        <td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>
                                        <td class="line"><?= mb_strtoupper(TRANS('FIELD_USER')); ?></td>
                                        <td class="line"><?= mb_strtoupper(TRANS('ENDUSER_AREA')); ?></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $data = [];
                                    $total = 0;
                                    foreach ($resultado->fetchall() as $row) {
                                        $data[] = $row;
                                    ?>
                                        <tr>
                                            <td class="line"><?= $row['qtd']; ?></td>
                                            <td class="line"><?= $row['nome']; ?></td>
                                            <td class="line"><?= $row['sistema']; ?></td>
                                        </tr>
                                    <?php
                                        $total += $row['qtd'];
                                    }
                                    $json = json_encode($data);
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="header table-borderless">
                                        <td><?= $total; ?></td>
                                        <td colspan="2"><?= TRANS('TOTAL'); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="chart-container">
                            <canvas id="canvasChart1"></canvas>
                        </div>
                        <?php
                        // var_dump($json);
                    }
                } else {
                    $_SESSION['flash'] = message('info', '', TRANS('MSG_COMPARE_DATE'), '');
                    // echo "<script>redirect('" . $_SERVER['PHP_SELF'] . "')</script>";
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
            var dataFromPHP = <?= $json; ?>

            var labels = []; // X Axis Label
            var total = []; // Value and Y Axis basis

            for (var i in dataFromPHP) {
                // console.log(dataFromPHP[i]);
                // labels.push(dataFromPHP[i].operador);
                labels.push(dataFromPHP[i].nome + '(' + dataFromPHP[i].sistema + ')');
                total.push(dataFromPHP[i].qtd);
            }

            var myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?= TRANS('total','',1); ?>',
                        data: total,
                        // backgroundColor: [
                        //     'rgba(255, 99, 132, 0.2)',
                        //     'rgba(54, 162, 235, 0.2)',
                        //     'rgba(255, 206, 86, 0.2)',
                        //     'rgba(75, 192, 192, 0.2)',
                        //     'rgba(153, 102, 255, 0.2)',
                        //     'rgba(255, 159, 64, 0.2)'
                        // ],
                        // borderColor: [
                        //     'rgba(255, 99, 132, 1)',
                        //     'rgba(54, 162, 235, 1)',
                        //     'rgba(255, 206, 86, 1)',
                        //     'rgba(75, 192, 192, 1)',
                        //     'rgba(153, 102, 255, 1)',
                        //     'rgba(255, 159, 64, 1)'
                        // ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: '<?= TRANS('TTL_REP_CALL_OPEN_USER_FINISH','',1); ?>',
                    },
                    scales: {
                        yAxes: [{
                            display: false,
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
                            formatter: (value, ctx) => {
                                let sum = ctx.dataset._meta[0].total;
                                let percentage = (value * 100 / sum).toFixed(2) + "%";
                                return percentage;
                            }
                        },
                    },
                }
            });
        }
    </script>
</body>

</html>