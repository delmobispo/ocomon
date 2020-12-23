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
$_SESSION['s_page_invmon'] = $_SERVER['PHP_SELF'];

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
    <!-- <link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" /> -->
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
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>


    <div class="container-fluid">
        <h5 class="my-4"><i class="fas fa-laptop text-secondary"></i>&nbsp;<?= TRANS('TTL_ESTAT_CAD_EQUIP'); ?></h5>
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

        $sqlTotalCount = $QRY["total_equip"] . " WHERE comp_inst NOT IN (" . INST_TERCEIRA . ")";
        $resTotalCount = $conn->query($sqlTotalCount);
        
        $total = $resTotalCount->fetch()['total'];
        if ($total == 0) {
            echo message('info', 'Ooops!', TRANS('NO_RECORDS_FOUND'), '', '', 1);
            return;
        }

        $terms = "";
        $query = "SELECT count(*) as Quantidade, count(*)*100/" . $total . " as Percentual, 
                    T.tipo_nome as Equipamento, T.tipo_cod as tipo 
                FROM equipamentos as C, tipo_equip as T 
                WHERE C.comp_tipo_equip = T.tipo_cod and C.comp_inst not in (" . INST_TERCEIRA . ") 
                GROUP by C.comp_tipo_equip 
                ORDER BY Quantidade desc, Equipamento";

        $resultado = $conn->query($query);
        $linhas = $resultado->rowCount();

        if ($linhas == 0) {
            echo message('info', '', TRANS('MSG_NO_DATA_IN_PERIOD'), '');
            return;
        } 

        $data = [];
        // $data2 = [];
        // $data3 = [];

        ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <caption><?= TRANS('TTL_GENERAL_BOARD'); ?></caption>
                <thead>
                    <tr class="header table-borderless">
                        <td class="line"><?= TRANS('COL_EQUIP'); ?></td>
                        <td class="line"><?= TRANS('qtd'); ?></td>
                        <td class="line"><?= TRANS('PERCENTAGE'); ?></td>
                    </tr>
                </thead>
                <tbody>
        <?php


        // $total = 0;
        foreach ($resultado->fetchall() as $row) {
            
            $data[] = $row;
            ?>
            <tr class=" table-borderless">
                <td class="line"><a href="equipments_list.php?comp_tipo_equip=<?= $row['tipo']; ?>"><?= $row['Equipamento'];?></a></td>
                <!-- <td class="line"><?= $row['Equipamento'];?></td> -->
                <td class="line"><?= $row['Quantidade'];?></td>
                <td class="line"><?= round($row['Percentual'], 2);?>%</td>
            </tr>
            
            <?php
            
        }
        

        $json = json_encode($data);
        // $json2 = json_encode($data2);
        ?>
                
                    </tbody>
                    <tfoot>
                        <tr class="header table-borderless">
                            <td ><?= TRANS('total'); ?></td>
                            <td colspan="2"><?= $total; ?></td>
                        </tr>
                    </tfoot>
                </tbody>
            </table>
        </div>



        <div class="chart-container">
            <canvas id="canvasChart1"></canvas>
        </div>
        <!-- <div class="chart-container">
            <canvas id="canvasChart2"></canvas>
        </div> -->
        <!-- <div class="chart-container">
            <canvas id="canvasChart3"></canvas>
        </div> -->
        <?php
        
        ?>
    </div>
    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <!-- <script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script> -->
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/dist/Chart.min.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-colorschemes/dist/chartjs-plugin-colorschemes.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-datalabels/chartjs-plugin-datalabels.min.js"></script>
    <script type='text/javascript'>
        $(function() {
            

            if (<?= $json ?> != 0) {
                showChart('canvasChart1');
                // showChart2('canvasChart2');
            }

        });


        function showChart(canvasID) {
            var ctx = $('#' + canvasID);
            var dataFromPHP = <?= $json; ?>

            var labels = []; // X Axis Label
            var total = []; // Value and Y Axis basis

            for (var i in dataFromPHP) {
                labels.push(dataFromPHP[i].Equipamento);
                total.push(dataFromPHP[i].Quantidade);
            }

            var myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        // label: 'SLA de Resposta',
                        data: total,
                        
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: '<?= TRANS('TTL_ESTAT_CAD_EQUIP','',1)?>',
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

        // function showChart2(canvasID) {
        //     var ctx2 = $('#' + canvasID);
        //     var dataFromPHP2 = <?= $json2; ?>

        //     var labels = []; // X Axis Label
        //     var total = []; // Value and Y Axis basis

        //     for (var i in dataFromPHP2) {
        //         labels.push(dataFromPHP2[i].label);
        //         total.push(dataFromPHP2[i].total);
        //     }

        //     var myChart2 = new Chart(ctx2, {
        //         type: 'doughnut',
        //         data: {
        //             labels: labels,
        //             datasets: [{
        //                 // label: 'SLA de Resposta',
        //                 data: total,
        //                 backgroundColor: [
        //                     'rgba(0, 128, 0, 0.8)',
        //                     'rgba(255, 255, 0, 0.8)',
        //                     'rgba(255, 0, 0, 0.8)',
        //                     'rgba(128, 128, 128, 0.8)',
        //                 ],
        //                 borderColor: [
        //                     'rgba(0, 128, 0, 1)',
        //                     'rgba(255, 255, 0, 1)',
        //                     'rgba(255, 0, 0, 1)',
        //                     'rgba(128, 128, 128, 0.1)',
        //                 ],
        //                 borderWidth: 2,
                        
        //             }]
        //         },
        //         options: {
        //             responsive: true,
        //             title: {
        //                 display: true,
        //                 text: '<?= TRANS('SOLUTION_SLA','',1)?>',
        //             },
        //             scales: {
        //                 yAxes: [{
        //                     display: false,
        //                     ticks: {
        //                         beginAtZero: true
        //                     }
        //                 }]
        //             },
        //             plugins: {
        //                 colorschemes: {
        //                     scheme: 'brewer.Paired12'
        //                 },
        //                 datalabels: {
        //                     display: function(context) {
        //                         return context.dataset.data[context.dataIndex] >= 1; // or !== 0 or ...
        //                     },
        //                     color: "#FFFFFF", 
        //                     formatter: (value, ctx2) => {
        //                         let sum = ctx2.dataset._meta[1].total;
        //                         let percentage = (value * 100 / sum).toFixed(2) + "%";
        //                         return percentage;
        //                     }
        //                 },
        //             },
        //         }
        //     });
        // }


    </script>
</body>

</html>