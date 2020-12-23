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
    <link rel="stylesheet" type="text/css" href="../../includes/components/jquery/toast-bootstrap-notify/dist/css/notify.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/Responsive-2.2.5/css/responsive.dataTables-custom.css" />
    <title>OcoMon&nbsp;<?= VERSAO; ?></title>

    <style>
        .dataTables_filter input,
        .dataTables_length select {
            border: 1px solid gray;
            border-radius: 4px;
            background-color: white;
            height: 25px;
        }

        .dataTables_filter {
            float: left !important;
        }

        .dataTables_length {
            float: right !important;
        }

        .truncate {
            max-width: 100px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }

        .table_lines {
            cursor: pointer;
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
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divPageDetails" class="p-3"></div>
                </div>
            </div>
        </div>

        <div id="divCards" class="mt-2">

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



        <div id="divAgendados">
            <!-- class="mt-2" -->
            <div class="accordion" id="accordionAgendados">
                <div class="card">
                    <div class="card-header bg-oc-teal" id="showAgendados">
                        <!-- style="background-color: teal;" -->
                        <button id="idBtnAgendados" class="btn btn-block text-center text-white" type="button" data-toggle="collapse" data-target="#listagemAgendados" aria-expanded="true" aria-controls="listagemAgendados" onclick="this.blur();">
                            <h4><i class="fas fa-calendar-alt"></i>&nbsp;<?= TRANS('QUEUE_SCHEDULED'); ?>&nbsp;<span id="idTotalAgendados" class="badge badge-light"></span></h4>
                        </button>
                    </div>

                    <div id="listagemAgendados" class="collapse" aria-labelledby="showAgendados" data-parent="#accordionAgendados">
                        <div class="card-body" id="idCardAgendados">
                            <div class="row">
                                <div class="col-12 ">
                                    <table id="table_scheduled" class="table stripe hover order-column row-border" width="100%">
                                        <thead>
                                            <tr class="header">
                                                <td class='line'></td>
                                                <td class='line'><?= TRANS('NUMBER_ABBREVIATE'); ?> / <?= TRANS('AREA'); ?></td>
                                                <td class='line'><?= TRANS('ISSUE_TYPE'); ?></td>
                                                <td class='line'><?= TRANS('CONTACT'); ?> / <?= TRANS('COL_PHONE'); ?></td>
                                                <td class='line truncate_flag truncate'><?= TRANS('DEPARTMENT'); ?> / <?= TRANS('DESCRIPTION'); ?></td>
                                                <td class='line'><?= TRANS('COL_STATUS'); ?></td>
                                                <td class='line'><?= TRANS('FILTERED_TIME'); ?></td>
                                                <td class='line'><?= TRANS('FIELD_SCHEDULE_TO'); ?></td>
                                                <td class='line'><?= TRANS('COL_SLA'); ?></td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="divVinculados">
            <div class="accordion" id="accordionMeusChamados">
                <div class="card">
                    <div class="card-header bg-success" id="showMeusChamados">
                        <!-- style="background-color: #3D9970;" -->
                        <button id="idBtnMeusChamados" class="btn btn-block text-center text-white" type="button" data-toggle="collapse" data-target="#listagemMeusChamados" aria-expanded="true" aria-controls="listagemMeusChamados" onclick="this.blur();">
                            <h4><i class="fas fa-user-check"></i>&nbsp;<?= TRANS('QUEUE_PENDING_FOR_YOU'); ?>&nbsp;<span id="idTotalVinculados" class="badge badge-light"></span></h4>
                        </button>
                    </div>

                    <div id="listagemMeusChamados" class="collapse " aria-labelledby="showMeusChamados" data-parent="#accordionMeusChamados">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 ">
                                    <table id="table_my_linked" class="table stripe hover order-column row-border" width="100%">
                                        <thead>
                                            <tr class="header">
                                                <td class='line'></td>
                                                <td class='line'><?= TRANS('NUMBER_ABBREVIATE'); ?> / <?= TRANS('AREA'); ?></td>
                                                <td class='line'><?= TRANS('ISSUE_TYPE'); ?></td>
                                                <td class='line'><?= TRANS('CONTACT'); ?> / <?= TRANS('COL_PHONE'); ?></td>
                                                <td class='line truncate_flag truncate'><?= TRANS('DEPARTMENT'); ?> / <?= TRANS('DESCRIPTION'); ?></td>
                                                <td class='line'><?= TRANS('COL_STATUS'); ?></td>
                                                <td class='line'><?= TRANS('FILTERED_TIME'); ?></td>
                                                <td class='line'><?= TRANS('OCO_PRIORITY'); ?></td>
                                                <td class='line'><?= TRANS('COL_SLA'); ?></td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="divFilaGeral">
            <div class="accordion" id="accordionFilaGeral">
                <div class="card">
                    <div class="card-header bg-oc-wine" id="showFilaGeral">
                        <!-- style="background-color: #85144b;" -->
                        <button id="idBtnFilaGeral" class="btn btn-block text-center text-white" type="button" data-toggle="collapse" data-target="#listagemFilaGeral" aria-expanded="true" aria-controls="listagemFilaGeral" onclick="this.blur();">
                            <h4><i class="fas fa-list-ul"></i>&nbsp;<?= TRANS('QUEUE_OPEN_FOR_TREAT'); ?>&nbsp;<span id="idTotalFila" class="badge badge-light"></span></h4>
                        </button>
                    </div>

                    <div id="listagemFilaGeral" class="collapse show" aria-labelledby="showFilaGeral" data-parent="#accordionFilaGeral">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 ">
                                    <table id="table_my_queued" class="table stripe hover order-column row-border" width="100%">
                                        <thead>
                                            <tr class="header">
                                                <td class='line'></td>
                                                <td class='line numero'><?= TRANS('NUMBER_ABBREVIATE'); ?> / <?= TRANS('AREA'); ?></td>
                                                <td class='line problema'><?= TRANS('ISSUE_TYPE'); ?></td>
                                                <td class='line contato'><?= TRANS('CONTACT'); ?> / <?= TRANS('COL_PHONE'); ?></td>
                                                <td class='line descricao truncate_flag truncate'><?= TRANS('DEPARTMENT'); ?> / <?= TRANS('DESCRIPTION'); ?></td>
                                                <td class='line status'><?= TRANS('COL_STATUS'); ?></td>
                                                <td class='line tempo'><?= TRANS('FILTERED_TIME'); ?></td>
                                                <td class='line prioridade'><?= TRANS('OCO_PRIORITY'); ?></td>
                                                <td class='line sla' id="idColSLAFilaGeral" data-toggle="popover" data-content="" data-placement="left" data-trigger="hover" title="<?= TRANS('HNT_LEDS'); ?>"><?= TRANS('COL_SLA'); ?></td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/Responsive-2.2.5/js/dataTables.responsive.min.js"></script>
    <!-- <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/Scroller-2.0.2/js/dataTables.scroller.js"></script> -->
    <script src="../../includes/components/bootstrap/js/popper.min.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../includes/components/jquery/toast-bootstrap-notify/dist/js/notify.js"></script>
    <script>
        $(function() {

            $(function() {
                $('[data-toggle="popover"]').popover({
                    html: true
                })
            });

            $('.popover-dismiss').popover({
                trigger: 'focus'
            });

            
            

            // $('#idColSLAFilaGeral').attr('data-content', hintSLALeds());

            var dataTableScheduled = $('#table_scheduled').DataTable({
                "responsive": {
                    details: {
                        type: 'column',
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
                                return col.hidden ?
                                    '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                    '<td>' + col.title + ':' + '</td> ' +
                                    '<td>' + col.data + '</td>' +
                                    '</tr>' :
                                    '';
                            }).join('');
                            return data ? $('<table/>').append(data) : false;
                        }
                        // renderer: $.fn.dataTable.Responsive.renderer.tableAll()
                    }
                },
                columnDefs: [{
                        className: 'control',
                        orderable: false,
                        targets: 0
                    },
                    {
                        className: 'truncate truncate_flag',
                        targets: [1, 4],
                        /* render: function ( targets, type, row ) {
                            return '$'+ targets;
                        } */
                        // render: $.fn.dataTable.render.text() //buidin helper
                    },
                    {
                        className: 'table_lines',
                        targets: '_all'
                    },
                    {
                        targets: [6, 7, 8],
                        orderable: false
                    }
                ],

                order: [1, 'desc'],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "get_scheduled_tickets.php", // json datasource
                    type: "post", // method  , by default get
                    /* data: {
                        user_id: 1 
                    }, */

                    "dataSrc": function(json) { //aqui consigo trabalhar no response

                        if (json.recordsTotal == 0) {
                            $("#listagemAgendados").collapse('hide');
                            $("#idBtnAgendados").attr('data-toggle', '');
                        } else {
                            $("#idBtnAgendados").attr('data-toggle', 'collapse');
                        }
                        $('#idTotalAgendados').html(json.recordsTotal);
                        // You can also modify `json.data` if required
                        return json.data;

                    },

                    error: function() { // error handling
                        $(".users-grid-error").html("");
                        $("#users-grid").append('<tbody class="users-grid-error"><tr><th colspan="3">Informações indisponíveis no momento</th></tr></tbody>');
                        $("#users-grid_processing").css("display", "none");
                    }
                },
                deferRender: true,
                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });

            var dataTableMyLinked = $('#table_my_linked').DataTable({
                "responsive": {
                    details: {
                        type: 'column',
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
                                return col.hidden ?
                                    '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                    '<td>' + col.title + ':' + '</td> ' +
                                    '<td>' + col.data + '</td>' +
                                    '</tr>' :
                                    '';
                            }).join('');
                            return data ? $('<table/>').append(data) : false;
                        }
                        // renderer: $.fn.dataTable.Responsive.renderer.tableAll()
                    }
                },
                columnDefs: [{
                        className: 'control',
                        orderable: false,
                        targets: 0
                    },
                    {
                        className: 'truncate truncate_flag',
                        targets: [1, 4],
                        /* render: function ( targets, type, row ) {
                            return '$'+ targets;
                        } */
                        // render: $.fn.dataTable.render.text() //buidin helper
                    },
                    {
                        className: 'table_lines',
                        targets: '_all'
                    },
                    {
                        targets: [6, 7, 8],
                        orderable: false
                    }
                ],

                order: [1, 'desc'],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "get_my_linked_tickets.php", // json datasource
                    type: "post", // method  , by default get

                    "dataSrc": function(json) { //aqui consigo trabalhar no response

                        if (json.recordsTotal == 0) {
                            $("#listagemMeusChamados").collapse('hide');
                            $("#idBtnMeusChamados").attr('data-toggle', '');
                        } else {
                            $("#idBtnMeusChamados").attr('data-toggle', 'collapse');
                        }
                        $('#idTotalVinculados').html(json.recordsTotal);
                        // You can also modify `json.data` if required
                        return json.data;

                    },
                    error: function() { // error handling
                        $(".users-grid-error").html("");
                        $("#users-grid").append('<tbody class="users-grid-error"><tr><th colspan="3">Informações indisponíveis no momento</th></tr></tbody>');
                        $("#users-grid_processing").css("display", "none");
                    }
                },
                deferRender: true,
                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });


            /* INICIALIZANDO A TABELA COM DIVERSOS ATRIBUTOS - MUITO UTIL*/
            /* $('#table_my_queued').on('init.dt', function() {
                $('.popover_tip')
                    .attr('data-toggle', 'popover')
                    .attr('data-content', '')
                    .attr('data-placement', 'top')
                    .attr('data-trigger', 'focus');
            }); */

            /* CARREGA A FILA GERAL DE CHAMADOS */
            var dataTableMyQueued = $('#table_my_queued').DataTable({
                "responsive": {
                    details: {
                        type: 'column',
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
                                return col.hidden ?
                                    '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                    '<td>' + col.title + ':' + '</td> ' +
                                    '<td>' + col.data + '</td>' +
                                    '</tr>' :
                                    '';
                            }).join('');
                            return data ? $('<table/>').append(data) : false;
                        }
                        // renderer: $.fn.dataTable.Responsive.renderer.tableAll()
                    }
                },

                columnDefs: [{
                        className: 'control',
                        orderable: false,
                        targets: 0
                    },
                    {
                        className: 'truncate truncate_flag',
                        // targets: [1, 4],
                        targets: [
                            'numero',
                            'descricao'
                        ],
                        /* render: function ( targets, type, row ) {
                            return '$'+ targets;
                        } */
                        // render: $.fn.dataTable.render.text() //buidin helper
                    },

                    {
                        className: 'table_lines',
                        targets: '_all',
                        // "createdCell": function(td){
                        //     td.setAttribute('data-toggle','popover');
                        //     td.setAttribute('data-placement','top');
                        //     td.setAttribute('data-trigger','hover');
                        //     td.setAttribute('data-content','');
                        // }
                    },

                    {
                        targets: 'tempo', //6
                        "createdCell": function(td, targets) {
                            td.setAttribute('data-sort', targets[0]);
                        },
                        "render": function(targets, type, row) {
                            return targets[1]
                        },
                        orderable: false,
                    },
                    {
                        targets: 'prioridade',
                        "createdCell": function(td, targets) {
                            td.setAttribute('data-sort', targets[0]);
                        },
                        "render": function(targets, type, row) {
                            return targets[1]
                        }
                    },

                    {
                        targets: ['sla'],
                        orderable: false
                    },


                    /* {
                        targets: 6,
                        "render": function (targets, type, row) { return targets[0]+"&nbsp;<i class='far " + targets[1] + "'></i>" }
                    } */
                ],

                order: [1, 'desc'],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "get_my_queued_tickets.php", // json datasource
                    type: "post",

                    "dataSrc": function(json) { //aqui consigo trabalhar no response
                        if (json.recordsTotal == 0) {
                            $("#listagemFilaGeral").collapse('hide');
                            $("#idBtnFilaGeral").attr('data-toggle', '');
                        } else {
                            $("#idBtnFilaGeral").attr('data-toggle', 'collapse');
                        }
                        $('#idTotalFila').html(json.recordsTotal);
                        // You can also modify `json.data` if required
                        return json.data;
                    },
                    error: function() { // error handling
                        $(".users-grid-error").html("");
                        $("#users-grid").append('<tbody class="users-grid-error"><tr><th colspan="3">Informações indisponíveis no momento</th></tr></tbody>');
                        $("#users-grid_processing").css("display", "none");
                    }
                },
                // rowId: 'id',

                // dom: "frtiS",
                scrollY: 400,
                deferRender: true,
                scroller: {
                    loadingIndicator: true
                },
                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });


            $('#table_scheduled').on('click', 'td', function() {

                var idFull = dataTableScheduled.row(this).id();
                var ticket = formatRowId(idFull, 'id_');

                var colIndex = $(this).index() + 1; /* coluna */

                //QUANDO FOR A COLUNA DO RESPOSIVO OU A COLUNA DA DESCRIÇÃO TRUNCADA NAO REDIRECIONA
                if (colIndex != 1 && colIndex != 5) {
                    redirect('ticket_show.php?numero=' + ticket);
                } else if (colIndex == 5) { //coluna da descricao do chamado

                    //SE EU QUISESSE MOSTRAR EM POPOVER
                    /* $(this).attr('data-content', dataTableScheduled.cell(this).data());
                    $(this).popover('show'); */

                    //mostrar o conteúdo da celula clicada na modal
                    $("#divPageDetails").html(dataTableScheduled.cell(this).data());

                    $("#divPageDetails").prepend('<h4>Chamado ' + ticket + '</h4><hr>');
                    $("#divPageDetails").append("<hr><button class='btn btn-secondary' onClick=\"redirect('ticket_show.php?numero=" + ticket + "')\">Ir para o chamado</button>");
                    $('#modalDefault').modal();

                }
                // loadPageInModal('ticket_show.php?numero='+chamado);
            });


            $('#table_my_linked').on('click', 'td', function() {

                var idFull = dataTableMyLinked.row(this).id();
                var ticket = formatRowId(idFull, 'id_');

                var colIndex = $(this).index() + 1; /* coluna */

                //QUANDO FOR A COLUNA DO RESPOSIVO OU A COLUNA DA DESCRIÇÃO TRUNCADA NAO REDIRECIONA
                if (colIndex != 1 && colIndex != 5) {
                    redirect('ticket_show.php?numero=' + ticket);
                } else if (colIndex == 5) { //coluna da descricao do chamado

                    //SE EU QUISESSE MOSTRAR EM POPOVER
                    /* $(this).attr('data-content', dataTableMyLinked.cell(this).data());
                    $(this).popover('show'); */

                    //mostrar o conteúdo da celula clicada na modal
                    $("#divPageDetails").html(dataTableMyLinked.cell(this).data());

                    $("#divPageDetails").prepend('<h4>Chamado ' + ticket + '</h4><hr>');
                    $("#divPageDetails").append("<hr><button class='btn btn-secondary' onClick=\"redirect('ticket_show.php?numero=" + ticket + "')\">Ir para o chamado</button>");
                    $('#modalDefault').modal();

                }
                // loadPageInModal('ticket_show.php?numero='+chamado);
            });




            /* $("#table_my_queued tbody").on("click", ".truncate_flag", function() {
                var index = $(this).index() + 1; //coluna
                $('table tr td:nth-child(' + index  + ')').toggleClass("truncate");
            }); */

            $('#table_my_queued').on('click', 'td', function() {

                var idFull = dataTableMyQueued.row(this).id();
                var ticket = formatRowId(idFull, 'id_');

                var colIndex = $(this).index() + 1; /* coluna */

                //QUANDO FOR A COLUNA DO RESPOSIVO OU A COLUNA DA DESCRIÇÃO TRUNCADA NAO REDIRECIONA
                if (colIndex != 1 && colIndex != 5) {
                    redirect('ticket_show.php?numero=' + ticket);
                } else if (colIndex == 5) { //coluna da descricao do chamado

                    //SE EU QUISESSE MOSTRAR EM POPOVER
                    /* $(this).attr('data-content', dataTableMyQueued.cell(this).data());
                    $(this).popover('show'); */

                    //mostrar o conteúdo da celula clicada na modal
                    $("#divPageDetails").html(dataTableMyQueued.cell(this).data());

                    $("#divPageDetails").prepend('<h4>Chamado ' + ticket + '</h4><hr>');
                    $("#divPageDetails").append("<hr><button class='btn btn-secondary' onClick=\"redirect('ticket_show.php?numero=" + ticket + "')\">Ir para o chamado</button>");
                    $('#modalDefault').modal();

                }
                // loadPageInModal('ticket_show.php?numero='+chamado);
            });

            updateScheduled();
            getCardsData();
            getNotices();
            check_warranties();

            setInterval(function() {
                dataTableScheduled.ajax.reload(null, false); // user paging is not reset on reload
                dataTableMyLinked.ajax.reload(null, false); // user paging is not reset on reload
                dataTableMyQueued.ajax.reload(null, false); // user paging is not reset on reload
                updateScheduled();
                getCardsData();
                getNotices();
            }, 60000); //a cada 1 minuto


        });

        function hintSLALeds() {
            var imgsPath = '<?= $imgsPath; ?>';
            var output = '<div class="row"><div class="col">Tempo de Resposta (primeira coluna)</div></div><div class="row"><div class="col-1"><img src="' + imgsPath + 'green-circle.svg"></div><div class="col-11"><p>Indica que o tempo de resposta ainda está dentro do previsto para o departamento origem do chamado</p></div></div>';

            return output;
        }

        function getCardsData() {
            $.ajax({
                url: 'get_cards_data.php',
                method: 'POST',
                // data: {
                //     'cod': cod
                // },
                dataType: 'json',

            }).done(function(data) {

                $('#badgeAbertos').html(data.abertosHoje);
                $('#badgeFechados').html(data.fechadosHoje);
                $('#badgeEmProgresso').html(data.emProgresso);
                $('#badgeAguardandoResposta').html(data.semResposta);
                $('#badgeAbertosMes').html(data.abertosMes);
                $('#badgeFechadosMes').html(data.fechadosMes);

            }).fail(function() {
                // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
            });
            return false;
        }


        function getNotices() {
            $.ajax({
                url: 'get_notices.php',
                method: 'POST',
                dataType: 'json',

            }).done(function(data) {
                
                let notice_ids = [];
                for (var i in data) {
                    let title = '<?= TRANS('NOTIFICATION_FROM_NOTICES_BOARD'); ?>';
                    notice_ids.push(data[i].aviso_id);
                    if ((data[i].title != null)) {
                        title = data[i].title;
                    }
                    notify(data[i].status, title, data[i].avisos);
                }
                
                $.ajax({
                    url: 'notices_process.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {'notice_ids' : notice_ids, 'action' : 'shown_notices'}
                }).done(function(data) {
                    console.log(data);
                });
                return false;

            }).fail(function() {
                // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
            });
            return false;
        }


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


        /* Roda a checagem de vencimentos das garantias de equipamentos */
        function check_warranties() {
            $.ajax({
                url: 'check_expiring_warranties.php',
                method: 'POST'
            });
            return false;
        }


        /* Ajusta o valor de ID das linhas para inteiro = numero do chamado */
        function formatRowId(fullId, prefix) {
            var id = fullId.split(prefix)[1];
            return parseInt(id);
        }

        function loadPageInModal(page) {
            $("#divPageDetails").load(page);
            $('#modalDefault').modal();
        }
    </script>
</body>

</html>