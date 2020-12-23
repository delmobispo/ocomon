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
require_once __DIR__ . "/" . "../../includes/classes/worktime/Worktime.php";
include_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

$_SESSION['s_page_ocomon'] = $_SERVER['PHP_SELF'];

$imgsPath = "../../includes/imgs/";
$config = getConfig($conn);
$percLimit = $config['conf_sla_tolerance']; 
$ledGreen = "<img width='20' src='{$imgsPath}green-circle.svg'>";
$ledYellow = "<img width='20' src='{$imgsPath}yellow-circle.svg'>";
$ledRed = "<img width='20' src='{$imgsPath}red-circle.svg'>";
$ledGray = "<img width='20' src='{$imgsPath}gray-circle.svg'>";
$slaIndicatorLabel = [];
$slaIndicatorLabel[1] = TRANS('SMART_NOT_IDENTIFIED');
$slaIndicatorLabel[2] = TRANS('SMART_IN_SLA');
$slaIndicatorLabel[3] = TRANS('SMART_IN_SLA_TOLERANCE');
$slaIndicatorLabel[4] = TRANS('SMART_OUT_SLA');

$json = 0;
$json2 = 0;
$json3 = 0;

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
        <h5 class="my-4"><i class="fas fa-handshake text-secondary"></i>&nbsp;<?= TRANS('REPORT_BY_SLA'); ?></h5>
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

            $terms = "";
            $query = $QRY["ocorrencias_full_ini"] . " WHERE 1 = 1 ";


            if (isset($_POST['area']) && !empty($_POST['area']) && ($_POST['area'] != -1)) {
                $query .= " AND o.sistema = '" . $_POST['area'] . "'";
                $qry_criterio = "SELECT sistema FROM sistemas WHERE sis_id = " . $_POST['area'] . " ";
                $exec_criterio = $conn->query($qry_criterio);
                $row_criterio = $exec_criterio->fetch();
                $criterio .= TRANS('FILTERED_AREA') . ": " . $row_criterio['sistema'];
            }


            if ((!isset($_POST['d_ini'])) || (!isset($_POST['d_fim']))) {
                $_SESSION['flash'] = message('info', '', TRANS('MSG_ALERT_PERIOD'), '');
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
                                ORDER BY o.numero ";
                    $resultado = $conn->query($query);
                    $linhas = $resultado->rowCount();

                    if ($linhas == 0) {
                        $_SESSION['flash'] = message('info', '', TRANS('MSG_NO_DATA_IN_PERIOD'), '');
                        redirect($_SERVER['PHP_SELF']);
                    } else {

                        ?>
                        <p><?= TRANS('TTL_PERIOD_FROM') . "&nbsp;" . dateScreen($d_ini, 1) . "&nbsp;" . TRANS('DATE_TO') . "&nbsp;" . dateScreen($d_fim, 1); ?></p>
                        
                        <?php
                        $data = [];
                        $data2 = [];
                        $data3 = [];

                        $respGreen = 0;
                        $respYellow = 0;
                        $respRed = 0;
                        $respUndentified = 0;
                        $respGreenPerc = 0;
                        $respYellowPerc = 0;
                        $respRedPerc = 0;
                        $respUndentifiedPerc = 0;

                        $solGreen = 0;
                        $solYellow = 0;
                        $solRed = 0;
                        $solUndentified = 0;
                        $solGreenPerc = 0;
                        $solYellowPerc = 0;
                        $solRedPerc = 0;
                        $solUndentifiedPerc = 0;

                        
                        $total = 0;
                        foreach ($resultado->fetchall() as $row) {
                            
                            

                            $referenceDate = (!empty($row['oco_real_open_date']) ? $row['oco_real_open_date'] : $row['data_abertura']);
                            $dataAtendimento = $row['data_atendimento']; //data da primeira resposta ao chamado
                            $dataFechamento = $row['data_fechamento'];
                        
                            /* MÉTODOS PARA O CÁLCULO DE TEMPO VÁLIDO DE RESPOSTA E SOLUÇÃO */
                            $holidays = getHolidays($conn);
                            $profileCod = getProfileCod($conn, $_SESSION['s_wt_areas'], $row['numero']);
                            $worktimeProfile = getWorktimeProfile($conn, $profileCod);
                        
                            /* Objeto para o cálculo de Tempo válido de SOLUÇÃO - baseado no perfil de jornada de trabalho e nas etapas em cada status */
                            $newWT = new WorkTime( $worktimeProfile, $holidays );
                            
                            /* Objeto para o cálculo de Tempo válido de RESPOSTA baseado no perfil de jornada de trabalho e nas etapas em cada status */
                            $newWTResponse = new WorkTime( $worktimeProfile, $holidays );
                        
                            /* Objeto para checagem se o momento atual está coberto pelo perfil de jornada associado */
                            $objWT = new Worktime( $worktimeProfile, $holidays );
                        
                            /* Realiza todas as checagens necessárias para retornar os tempos de resposta e solução para o chamado */
                            $ticketTimeInfo = getTicketTimeInfo($conn, $newWT, $newWTResponse, $row['numero'], $referenceDate, $dataAtendimento, $dataFechamento, $row['status_cod'], $objWT);
                        
                            // $solutionTime = $ticketTimeInfo['solution']['time'];
                            
                            /* Checagem sobre o filtro de SLAs */
                            $responseResult = getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']);
                            $solutionResult = getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']);
                            $absoluteTime = absoluteTime($referenceDate, (!empty($dataFechamento) ? $dataFechamento : date('Y-m-d H:i:s')))['inTime'];

                            $respUndentified += ($responseResult == 1 ? 1 : 0);
                            $respGreen += ($responseResult == 2 ? 1 : 0);
                            $respYellow += ($responseResult == 3 ? 1 : 0);
                            $respRed += ($responseResult == 4 ? 1 : 0);

                            $solUndentified += ($solutionResult == 1 ? 1 : 0);
                            $solGreen += ($solutionResult == 2 ? 1 : 0);
                            $solYellow += ($solutionResult == 3 ? 1 : 0);
                            $solRed += ($solutionResult == 4 ? 1 : 0);
                            
                        }
                        $data[0]['label'] = $slaIndicatorLabel[2];
                        $data[1]['label'] = $slaIndicatorLabel[3];
                        $data[2]['label'] = $slaIndicatorLabel[4];
                        $data[3]['label'] = $slaIndicatorLabel[1];

                        $data[0]['total'] = $respGreen;
                        $data[1]['total'] = $respYellow;
                        $data[2]['total'] = $respRed;
                        $data[3]['total'] = $respUndentified;

                        $data2[0]['label'] = $slaIndicatorLabel[2];
                        $data2[1]['label'] = $slaIndicatorLabel[3];
                        $data2[2]['label'] = $slaIndicatorLabel[4];
                        $data2[3]['label'] = $slaIndicatorLabel[1];

                        $data2[0]['total'] = $solGreen;
                        $data2[1]['total'] = $solYellow;
                        $data2[2]['total'] = $solRed;
                        $data2[3]['total'] = $solUndentified;

                        $respUndentifiedPerc = ($linhas ? round($respUndentified * 100 / $linhas,2) : 0);
                        $respGreenPerc = ($linhas ? round($respGreen * 100 / $linhas,2) : 0);
                        $respYellowPerc = ($linhas ? round($respYellow * 100 / $linhas,2) : 0);
                        $respRedPerc = ($linhas ? round($respRed * 100 / $linhas,2) : 0);
                        
                        $solUndentifiedPerc = ($linhas ? round($solUndentified * 100 / $linhas,2) : 0);
                        $solGreenPerc = ($linhas ? round($solGreen * 100 / $linhas,2) : 0);
                        $solYellowPerc = ($linhas ? round($solYellow * 100 / $linhas,2) : 0);
                        $solRedPerc = ($linhas ? round($solRed * 100 / $linhas,2) : 0);

                        $json = json_encode($data);
                        $json2 = json_encode($data2);
                        ?>
                                
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <caption><?= $criterio; ?></caption>
                                <thead>
                                    <tr class="header table-borderless">
                                        <td class="line"></td>
                                        <td class="line"><?= $ledGreen; ?>&nbsp;<?= mb_strtoupper($slaIndicatorLabel[2]); ?></td>
                                        <td class="line"><?= $ledYellow; ?>&nbsp;<?= mb_strtoupper($slaIndicatorLabel[3]); ?></td>
                                        <td class="line"><?= $ledRed; ?>&nbsp;<?= mb_strtoupper($slaIndicatorLabel[4]); ?></td>
                                        <td class="line"><?= $ledGray; ?>&nbsp;<?= mb_strtoupper($slaIndicatorLabel[1]); ?></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class=" table-borderless">
                                        <td class="line font-weight-bold"><?= TRANS('RESPONSE'); ?></td>
                                        <td class="line"><?= $respGreen . " (" . $respGreenPerc . "%)";?></td>
                                        <td class="line"><?= $respYellow . " (" . $respYellowPerc . "%)";?></td>
                                        <td class="line"><?= $respRed . " (" . $respRedPerc . "%)";?></td>
                                        <td class="line"><?= $respUndentified . " (" . $respUndentifiedPerc . "%)";?></td>
                                    </tr>
                                    <tr class=" table-borderless">
                                        <td class="line font-weight-bold"><?= TRANS('SOLUTION'); ?></td>
                                        <td class="line"><?= $solGreen . " (" . $solGreenPerc . "%)";?></td>
                                        <td class="line"><?= $solYellow . " (" . $solYellowPerc . "%)";?></td>
                                        <td class="line"><?= $solRed . " (" . $solRedPerc . "%)";?></td>
                                        <td class="line"><?= $solUndentified . " (" . $solUndentifiedPerc . "%)";?></td>
                                    </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="header table-borderless">
                                            <td colspan="4"><?= TRANS('TOTAL_OF_TICKETS_IN_PERIOD'); ?></td>
                                            <td ><?= $linhas; ?></td>
                                        </tr>
                                    </tfoot>
                                </tbody>
                            </table>
                        </div>



                        <div class="chart-container">
                            <canvas id="canvasChart1"></canvas>
                        </div>
                        <div class="chart-container">
                            <canvas id="canvasChart2"></canvas>
                        </div>
                        <!-- <div class="chart-container">
                            <canvas id="canvasChart3"></canvas>
                        </div> -->
                        <?php
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
                showChart2('canvasChart2');
            }

        });


        function showChart(canvasID) {
            var ctx = $('#' + canvasID);
            var dataFromPHP = <?= $json; ?>

            var labels = []; // X Axis Label
            var total = []; // Value and Y Axis basis

            for (var i in dataFromPHP) {
                labels.push(dataFromPHP[i].label);
                total.push(dataFromPHP[i].total);
            }

            var myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        // label: 'SLA de Resposta',
                        data: total,
                        backgroundColor: [
                            'rgba(0, 128, 0, 0.8)',
                            'rgba(255, 255, 0, 0.8)',
                            'rgba(255, 0, 0, 0.8)',
                            'rgba(128, 128, 128, 0.8)',
                        ],
                        borderColor: [
                            'rgba(0, 128, 0, 1)',
                            'rgba(255, 255, 0, 1)',
                            'rgba(255, 0, 0, 1)',
                            'rgba(128, 128, 128, 0.1)',
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: '<?= TRANS('RESPONSE_SLA','',1)?>',
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
                            color: "#FFFFFF", 
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

        function showChart2(canvasID) {
            var ctx2 = $('#' + canvasID);
            var dataFromPHP2 = <?= $json2; ?>

            var labels = []; // X Axis Label
            var total = []; // Value and Y Axis basis

            for (var i in dataFromPHP2) {
                labels.push(dataFromPHP2[i].label);
                total.push(dataFromPHP2[i].total);
            }

            var myChart2 = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        // label: 'SLA de Resposta',
                        data: total,
                        backgroundColor: [
                            'rgba(0, 128, 0, 0.8)',
                            'rgba(255, 255, 0, 0.8)',
                            'rgba(255, 0, 0, 0.8)',
                            'rgba(128, 128, 128, 0.8)',
                        ],
                        borderColor: [
                            'rgba(0, 128, 0, 1)',
                            'rgba(255, 255, 0, 1)',
                            'rgba(255, 0, 0, 1)',
                            'rgba(128, 128, 128, 0.1)',
                        ],
                        borderWidth: 2,
                        
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: '<?= TRANS('SOLUTION_SLA','',1)?>',
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
                            color: "#FFFFFF", 
                            formatter: (value, ctx2) => {
                                let sum = ctx2.dataset._meta[1].total;
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