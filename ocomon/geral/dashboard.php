<?php session_start();
/*  Copyright 2020 Flávio Ribeiro

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
*/

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

$imgsPath = "../../includes/imgs/";

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
        canvas {
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
        }

        .chart-container {
            position: relative;
            height: 100%;
            width: 100%;
            margin-left: 10px;
            margin-right: 10px;
        }

        .side-cards {
            max-width: calc(16.7%);
            height: 180%;
            float: right;
        }

        .icon-toogle {
            position: absolute;
            top: 0;
            right: 5px;
            z-index: 1;
            cursor: pointer;
        }

        .icon-show-graph {
            position: absolute;
            top: 0;
            left: 5px;
            z-index: 1;
            cursor: pointer;
        }

        .icon-expand:before {
            font-family: "Font Awesome\ 5 Free";
            /* content: "\f065"; */
            content: "\f30b";
            font-weight: 900;
            font-size: 16px;
        }

        .icon-collapse:before {
            font-family: "Font Awesome\ 5 Free";
            /* content: "\f066"; */
            content: "\f30a";
            font-weight: 900;
            font-size: 16px;
        }

        .icon-view-graph:before {
            font-family: "Font Awesome\ 5 Free";
            /* content: "\f065"; */
            content: "\f06e";
            font-weight: 900;
            font-size: 12px;
        }

        .icon-toogle-card:before {
            font-family: "Font Awesome\ 5 Free";
            content: "\f362";
            font-weight: 900;
            font-size: 12px;
        }

        .flex-container {
            display: flex;
            position: relative;
        }

        .flex-child {
            display: flex;
            max-width: calc(100%);
            flex: 1;
            position: relative;
        }

        .flex-child-child {
            max-width: calc(100%/2);
            flex: 1;
            padding-right: 5px;
            padding-bottom: 5px;
            position: relative;
        }



        @media only screen and (max-width: 768px) {

            .flex-container,
            .flex-child,
            .flex-child-child,
            .side-cards {
                display: block;
                max-width: 100%;
            }

            .icon-toogle {
                display: none;
            }
        }
    </style>
</head>

<body>
    <?= $auth->showHeader(); ?>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    <div class="container-fluid">


        <div class="modal" tabindex="-1" id="modalDefault">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div id="divShowGraph" class="p-3">
                        <!-- <canvas id="canvasModal"></canvas> -->
                    </div>
                </div>
            </div>
        </div>


        <div id="top-cards" class="mt-2">

            <div class="row no-gutters">
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_TODAY'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-plus-square"></i>&nbsp;<?= TRANS('CARDS_OPENED'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeAbertos" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-success">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_TODAY'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-check"></i>&nbsp;<?= TRANS('CARDS_CLOSED'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeFechados" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-info">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-user-check"></i>&nbsp;<?= TRANS('CARDS_IN_PROGRESS'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeEmProgresso" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-danger">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-clock"></i>&nbsp;<?= TRANS('CARDS_WAITING_RESPONSE'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeAguardandoResposta" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-plus-square"></i>&nbsp;<?= TRANS('CARDS_OPENED'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeAbertosMes" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-success">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-check"></i>&nbsp;<?= TRANS('CARDS_CLOSED'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeFechadosMes" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="side-cards" id="side-cards">
            <div class="row">

                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-oc-wine">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-list-ul"></i>&nbsp;<?= TRANS('CARDS_OPENED_QUEUE'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeFilaGeral" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-oc-teal">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-calendar-alt"></i>&nbsp;<?= TRANS('QUEUE_SCHEDULED'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeAgendados" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card flip frente">
                        <div class="card-header front bg-info">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?>: <?= TRANS('CARDS_NOT_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-handshake"></i>&nbsp;<span class='sla-response' id="span-sla-open"><?= TRANS('CARDS_RESPONSE_SLA'); ?></span></h6>
                            <h5 class="text-center text-white"><span id="badgeResponseGreen" class="badge badge-light">0</span></h5>
                        </div>

                        <div class="card-header back bg-info">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?>: <?= TRANS('CARDS_NOT_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-handshake"></i>&nbsp;<span class='sla-solution'><?= TRANS('CARDS_SOLUTION_SLA'); ?></span></h6>
                            <h5 class="text-center text-white"><span id="badgeSolutionGreen" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-12 ">
                    <div class="card flip frente">

                        <div class="card-header bg-oc-wine front">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?>: <?= TRANS('CARDS_NOT_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-clock"></i>&nbsp;<?= TRANS('CARDS_RESPONSE_AVG'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeAvgFilteredResponseTime" class="badge badge-light">0</span></h5>
                        </div>

                        <div class="card-header bg-oc-wine back">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?>: <?= TRANS('CARDS_NOT_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-clock"></i>&nbsp;<?= TRANS('CARDS_RESPONSE_AVG'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeAvgAbsResponseTime" class="badge badge-light">0</span></h5>
                        </div>

                    </div>
                </div>

                <div class="col-12 ">
                    <div class="card flip frente">
                        <div class="card-header bg-oc-wine front">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?>: <?= TRANS('CARDS_NOT_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-clock"></i>&nbsp;<?= TRANS('CARDS_LIFESPAN_AVG'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeAvgFilteredSolutionTime" class="badge badge-light">0</span></h5>
                        </div>
                        <div class="card-header bg-oc-wine back">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?>: <?= TRANS('CARDS_NOT_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-clock"></i>&nbsp;<?= TRANS('CARDS_LIFESPAN_AVG'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeAvgAbsSolutionTime" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>



                <div class="col-12">
                    <div class="card flip frente">
                        <div class="card-header front bg-oc-wine">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-pause"></i>&nbsp;<?= TRANS('CARDS_PAUSED'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeFrozenByStatus" class="badge badge-light">0</span></h5>
                        </div>

                        <div class="card-header back bg-oc-wine">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_NOW'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-pause"></i>&nbsp;<?= TRANS('CARDS_PAUSED'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeFrozenByWorktime" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card flip frente">
                        <div class="card-header front bg-info">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_GENERAL'); ?>: <?= TRANS('CARDS_NOT_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-ticket-alt"></i>&nbsp;<?= TRANS('CARDS_OLDER'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeOlderTicket" class="badge badge-light">0</span></h5>
                        </div>

                        <div class="card-header back bg-info">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_GENERAL'); ?>: <?= TRANS('CARDS_NOT_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-ticket-alt"></i>&nbsp;<?= TRANS('CARDS_NEWER'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeNewerTicket" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card flip frente">
                        <div class="card-header front bg-info">
                            <!-- bg-danger -->
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?>: <?= TRANS('CARDS_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-handshake"></i>&nbsp;<?= TRANS('CARDS_SOLUTION_SLA'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeDoneSolutionGreen" class="badge badge-light">0</span></h5>
                        </div>

                        <div class="card-header back bg-info">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?>: <?= TRANS('CARDS_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-handshake"></i>&nbsp;<?= TRANS('CARDS_RESPONSE_SLA'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeDoneResponseGreen" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card flip frente">
                        <div class="card-header front bg-info">
                            <!-- bg-danger -->
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_TODAY'); ?>: <?= TRANS('CARDS_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-handshake"></i>&nbsp;<?= TRANS('CARDS_SOLUTION_SLA'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeDoneTodaySolutionGreen" class="badge badge-light">0</span></h5>
                        </div>

                        <div class="card-header back bg-info">
                            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('CARDS_TURN'); ?>">
                                <span class="icon-toogle-card text-white toogle-sla-open"></span>
                            </div>
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_TODAY'); ?>: <?= TRANS('CARDS_CLOSED'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-handshake"></i>&nbsp;<?= TRANS('CARDS_RESPONSE_SLA'); ?></h6>
                            <h5 class="text-center text-white"><span id="badgeDoneTodayResponseGreen" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>

            </div>
        </div>



        <div class="flex-container">

            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('INCREASE_OR_DECREASE_VIEW_PANEL'); ?>">
                <span class="icon-expand text-secondary" id="toogle-side-cards"></span>
            </div>

            <div class="flex-child">

                <div class="flex-child-child">
                    <div class="icon-show-graph" id="first_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container">
                                <canvas id="graph_01"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-child-child">
                    <div class="icon-show-graph" id="second_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <!-- <div class="icon-show-graph" id="second_graph" title="<?= TRANS('SHOW_CHART'); ?>"> -->
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container">
                                <canvas id="graph_02"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-container">
            <div class="flex-child">
                <div class="flex-child-child">
                    <div class="icon-show-graph" id="third_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container">
                                <canvas id="graph_03"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-child-child">
                    <div class="icon-show-graph" id="fourth_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container">
                                <canvas id="graph_04"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-container">
            <div class="flex-child">
                <div class="flex-child-child">
                    <div class="icon-show-graph" id="fifth_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container">
                                <canvas id="graph_05"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-child-child">
                    <div class="icon-show-graph" id="sixth_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container">
                                <canvas id="graph_06"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-container">
            <div class="flex-child">
                <div class="flex-child-child">
                    <div class="icon-show-graph" id="seventh_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container">
                                <canvas id="graph_07"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex-child-child">
                    <div class="icon-show-graph" id="eightth_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container">
                                <canvas id="graph_08"></canvas>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>



        <!-- <script src="../../includes/javascript/funcoes-3.0.js"></script> -->
        <script src="../../includes/components/jquery/jquery.js"></script>
        <script src="../../includes/components/jquery/jquery-flip/dist/jquery.flip.js"></script>
        <script src="../../includes/components/bootstrap/js/popper.min.js"></script>
        <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="../../includes/components/chartjs/dist/Chart.min.js"></script>
        <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-colorschemes/dist/chartjs-plugin-colorschemes.js"></script>
        <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-datalabels/chartjs-plugin-datalabels.min.js"></script>

        <script src="ajax/tickets_x_status.js"></script>
        <script src="ajax/tickets_x_area_months.js"></script>
        <script src="ajax/top_ten_type_of_issues.js"></script>
        <script src="ajax/tickets_x_area_curr_month.js"></script>
        <script src="ajax/tickets_x_area.js"></script>
        <script src="ajax/tickets_area_close_months.js"></script>
        <script src="ajax/tickets_open_close_months.js"></script>
        <script src="ajax/tickets_operadores_close_months.js"></script>
        <script>
            $(function() {

                tickets_x_status('graph_01');
                tickets_x_area_months('graph_02');
                top_ten_type_of_issues('graph_03');
                tickets_x_area_curr_month('graph_04');
                tickets_x_area('graph_05');
                tickets_area_close_months('graph_06');
                tickets_open_close_months('graph_07');
                tickets_operadores_close_months('graph_08');


                $(".flip").flip({
                    trigger: 'manual'
                });


                $(function() {
                    $('[data-toggle="popover"]').popover({
                        html: true
                    })
                });
                $('.popover-dismiss').popover({
                    trigger: 'focus'
                });

                $('#first_graph').on('click', function() {
                    showGraphInModal(tickets_x_status);
                });
                $('#second_graph').on('click', function() {
                    showGraphInModal(tickets_x_area_months);
                });
                $('#third_graph').on('click', function() {
                    showGraphInModal(top_ten_type_of_issues);
                });
                $('#fourth_graph').on('click', function() {
                    showGraphInModal(tickets_x_area_curr_month);
                });
                $('#fifth_graph').on('click', function() {
                    showGraphInModal(tickets_x_area);
                });
                $('#sixth_graph').on('click', function() {
                    showGraphInModal(tickets_area_close_months);
                });
                $('#seventh_graph').on('click', function() {
                    showGraphInModal(tickets_open_close_months);
                });
                $('#eightth_graph').on('click', function() {
                    showGraphInModal(tickets_operadores_close_months);
                });




                /*  $('.icon-show-graph').on('click', function() {
                     console.log($(this).data('graph'));
                     showGraphInModal($(this).data('graph'));
                 }); */


                $('#toogle-side-cards').on('click', function() {
                    $('#side-cards').toggle('slow');
                    if ($('#toogle-side-cards').hasClass('icon-collapse')) {
                        $('#toogle-side-cards').addClass('icon-expand');
                        $('#toogle-side-cards').removeClass('icon-collapse');
                    } else {
                        $('#toogle-side-cards').addClass('icon-collapse');
                        $('#toogle-side-cards').removeClass('icon-expand');
                    }
                });

                $('.icon-toogle-card').on('click', function() {
                    if ($(this).parents().eq(2).hasClass('frente')) {
                        $(this).parents().eq(2).addClass('costas');
                        $(this).parents().eq(2).removeClass('frente');
                        $(this).parents().eq(2).flip(true);
                    } else {
                        $(this).parents().eq(2).addClass('frente');
                        $(this).parents().eq(2).removeClass('costas');
                        $(this).parents().eq(2).flip(false);
                    }
                });


                updateScheduled();
                getCardsData();

                setInterval(function() {
                    updateScheduled();
                    getCardsData();
                }, 60000); //a cada 1 minuto


            });

            function showGraphInModal(funcao) {
                $('.canvas-modal').remove();

                var fieldHTML = '<canvas class="canvas-modal" id="canvasModal"></canvas>';
                $('#divShowGraph').append(fieldHTML);

                funcao('canvasModal');
                $('#modalDefault').modal();
            }

            function getCardsData() {
                $.ajax({
                    url: 'get_cards_data.php',
                    method: 'POST',
                    // data: {
                    //     'solution': solution
                    // },
                    dataType: 'json',

                }).done(function(data) {

                    // console.log(data);

                    $('#badgeAbertos').html(data.abertosHoje);
                    $('#badgeFechados').html(data.fechadosHoje);
                    $('#badgeEmProgresso').html(data.emProgresso + ' <small><mark>(' + data.percEmProgresso + '%)</mark></small>');
                    $('#badgeAguardandoResposta').html(data.semResposta + ' <small><mark>(' + data.percSemResposta + '%)</mark></small>');
                    $('#badgeAbertosMes').html(data.abertosMes);
                    $('#badgeFechadosMes').html(data.fechadosMes);
                    $('#badgeAgendados').html(data.agendados);
                    $('#badgeFilaGeral').html(data.filaGeral + ' <small><mark>(' + data.percFilaGeral + '%)</mark></small>');


                    if (data.percResponseGreen >= 80) {
                        $('#badgeResponseGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeResponseGreen').parents().eq(1).addClass('bg-success');
                    } else if (data.percResponseGreen >= 70) {
                        $('#badgeResponseGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeResponseGreen').parents().eq(1).addClass('bg-oc-orange');
                    } else {
                        $('#badgeResponseGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeResponseGreen').parents().eq(1).addClass('bg-danger');
                    }
                    $('#badgeResponseGreen').html(data.percResponseGreen + '%');

                    if (data.percSolutionGreen >= 80) {
                        $('#badgeSolutionGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeSolutionGreen').parents().eq(1).addClass('bg-success');
                    } else if (data.percSolutionGreen >= 70) {
                        $('#badgeSolutionGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeSolutionGreen').parents().eq(1).addClass('bg-oc-orange');
                    } else {
                        $('#badgeSolutionGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeSolutionGreen').parents().eq(1).addClass('bg-danger');
                    }
                    $('#badgeSolutionGreen').html(data.percSolutionGreen + '%');

                    $('#badgeAvgFilteredResponseTime').html(data.openAvgFilteredResponseTime + ' <small><mark>(<?= TRANS("CARDS_FILTERED_TIME",'',1);?>)</mark></small>');
                    $('#badgeAvgFilteredSolutionTime').html(data.openAvgFilteredSolutionTime + ' <small><mark>(<?= TRANS("CARDS_FILTERED_TIME",'',1);?>)</mark></small>');
                    $('#badgeAvgAbsResponseTime').html(data.openAvgAbsResponseTime + ' <small><mark>(<?= TRANS("CARDS_ABSOLUTE_TIME",'',1);?>)</mark></small>');
                    $('#badgeAvgAbsSolutionTime').html(data.openAvgAbsSolutionTime + ' <small><mark>(<?= TRANS("CARDS_ABSOLUTE_TIME",'',1);?>)</mark></small>');
                    $('#badgeFrozenByStatus').html(data.frozenByStatus + ' <small><mark>(<?= TRANS("CARDS_DUE_STATUS",'',1);?>)</mark></small>');
                    $('#badgeFrozenByWorktime').html(data.frozenByWorktime + ' <small><mark>(<?= TRANS("CARDS_DUE_WORKTIME",'',1);?>)</mark></small>');
                    $('#badgeOlderTicket').html('<?= TRANS("NUMBER_ABBREVIATE",'',1);?> ' + data.olderTicket + ' <small><mark>( ' + data.olderAge + ' )</mark></small>');
                    $('#badgeNewerTicket').html('<?= TRANS("NUMBER_ABBREVIATE",'',1);?> ' + data.newerTicket + ' <small><mark>( ' + data.newerAge + ' )</mark></small>');

                    if (data.percDoneSolutionGreen >= 80) {
                        $('#badgeDoneSolutionGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneSolutionGreen').parents().eq(1).addClass('bg-success');
                    } else if (data.percDoneSolutionGreen >= 70) {
                        $('#badgeDoneSolutionGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneSolutionGreen').parents().eq(1).addClass('bg-oc-orange');
                    } else {
                        $('#badgeDoneSolutionGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneSolutionGreen').parents().eq(1).addClass('bg-danger');
                    }
                    $('#badgeDoneSolutionGreen').html(data.percDoneSolutionGreen + '%');

                    if (data.percDoneResponseGreen >= 80) {
                        $('#badgeDoneResponseGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneResponseGreen').parents().eq(1).addClass('bg-success');
                    } else if (data.percDoneResponseGreen >= 70) {
                        $('#badgeDoneResponseGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneResponseGreen').parents().eq(1).addClass('bg-oc-orange');
                    } else {
                        $('#badgeDoneResponseGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneResponseGreen').parents().eq(1).addClass('bg-danger');
                    }
                    $('#badgeDoneResponseGreen').html(data.percDoneResponseGreen + '%');

                    if (data.percDoneTodayResponseGreen >= 80) {
                        $('#badgeDoneTodayResponseGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneTodayResponseGreen').parents().eq(1).addClass('bg-success');
                    } else if (data.percDoneTodayResponseGreen >= 70) {
                        $('#badgeDoneTodayResponseGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneTodayResponseGreen').parents().eq(1).addClass('bg-oc-orange');
                    } else {
                        $('#badgeDoneTodayResponseGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneTodayResponseGreen').parents().eq(1).addClass('bg-danger');
                    }
                    $('#badgeDoneTodayResponseGreen').html(data.percDoneTodayResponseGreen + '%');

                    if (data.percDoneTodaySolutionGreen >= 80) {
                        $('#badgeDoneTodaySolutionGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneTodaySolutionGreen').parents().eq(1).addClass('bg-success');
                    } else if (data.percDoneTodaySolutionGreen >= 70) {
                        $('#badgeDoneTodaySolutionGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneTodaySolutionGreen').parents().eq(1).addClass('bg-oc-orange');
                    } else {
                        $('#badgeDoneTodaySolutionGreen').parents().eq(1).removeClass('bg-info bg-oc-orange bg-success bg-danger');
                        $('#badgeDoneTodaySolutionGreen').parents().eq(1).addClass('bg-danger');
                    }
                    $('#badgeDoneTodaySolutionGreen').html(data.percDoneTodaySolutionGreen + '%');




                }).fail(function() {
                    // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
                    console.log(data);
                });
                return false;
            }

            /* $('html').removeClass(function(index, css) {
                return (css.match(/\bpage-\S+/g) || []).join(' '); // removes anything that starts with "page-"
            }); */


            /* Roda a checagem de data para chamados agendados entrarem na fila geral de atendimento */
            function updateScheduled() {
                $.ajax({
                    url: 'update_scheduled_tickets.php',
                    method: 'POST',
                    data: {
                        'numero': 1
                    },
                });
                return false;
            }
        </script>
</body>

</html>