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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);

$_SESSION['s_page_home'] = $_SERVER['PHP_SELF'];

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

        <?php
            if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
                echo $_SESSION['flash'];
                $_SESSION['flash'] = '';
            }
        ?>


        <div id="divMyInactiveTickets" class="mt-2">
            <div class="accordion" id="accordionMyInactiveTickets">
                <div class="card">
                    <div class="card-header bg-oc-teal" id="showMyInactiveTickets">
                        <button id="idBtnMyInactiveTickets" class="btn btn-block text-center text-white" type="button" data-toggle="collapse" data-target="#listagemMyInactive" aria-expanded="true" aria-controls="listagemMyInactive" onclick="this.blur();">
                            <h4><i class="fas fa-archive"></i>&nbsp;<?= TRANS('QUEUE_INACTIVE_OPEN_BY_ME'); ?>&nbsp;<span id="idTotalInactive" class="badge badge-light"></span></h4>
                        </button>
                    </div>

                    <div id="listagemMyInactive" class="collapse " aria-labelledby="showMyInactiveTickets" data-parent="#accordionMyInactiveTickets">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 ">
                                    <table id="table_my_inactive" class="table stripe hover order-column row-border" width="100%">
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

        
        <?php
        if ($_SESSION['s_nivel'] < 3) {
            ?>
            <div id="divMyClosure">
                <div class="accordion" id="accordionMyClosure">
                    <div class="card">
                        <div class="card-header bg-success" id="showMyClosure"> <!-- #3D9970; -->
                            <button id="idBtnMyClosure" class="btn btn-block text-center text-white" type="button" data-toggle="collapse" data-target="#listagemMyClosure" aria-expanded="true" aria-controls="listagemMyClosure" onclick="this.blur();">
                                <h4><i class="fas fa-check"></i>&nbsp;<?= TRANS('QUEUE_MY_CLOSURES'); ?>&nbsp;<span id="idTotalMyClosure" class="badge badge-light"></span></h4>
                            </button>
                        </div>

                        <div id="listagemMyClosure" class="collapse" aria-labelledby="showMyClosure" data-parent="#accordionMyClosure">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 ">
                                        <table id="table_my_closure" class="table stripe hover order-column row-border" width="100%">
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
            <?php
        }
        ?>
        
        

        <div id="divMyOpenTickets">
            <div class="accordion" id="accordionMyTickets">
                <div class="card">
                    <div class="card-header bg-oc-wine" id="showMyTickets">
                        <button id="idBtnMyTickets" class="btn btn-block text-center text-white" type="button" data-toggle="collapse" data-target="#listagemMyTickets" aria-expanded="true" aria-controls="listagemMyTickets" onclick="this.blur();">
                            <h4><i class="fas fa-list-alt"></i>&nbsp;<?= TRANS('QUEUE_ACTIVE_OPEN_BY_ME'); ?>&nbsp;<span id="idTotalMyTickets" class="badge badge-light"></span></h4>
                        </button>
                    </div>

                    <div id="listagemMyTickets" class="collapse show" aria-labelledby="showMyTickets" data-parent="#accordionMyTickets">
                        <div class="card-body" id="idCardMyTickets">
                            <div class="row">
                                <div class="col-12 ">
                                    <table id="table_my_tickets" class="table stripe hover order-column row-border" width="100%">
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

    </div>

    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/Responsive-2.2.5/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/Scroller-2.0.2/js/dataTables.scroller.js"></script>
    <script src="../../includes/components/bootstrap/js/popper.min.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../includes/components/jquery/toast-bootstrap-notify/dist/js/notify.js"></script>
    <script>
        $(function() {

            $(function() {
                $('[data-toggle="popover"]').popover()
            });

            $('.popover-dismiss').popover({
                trigger: 'focus'
            });

            var dataTableMyTickets = $('#table_my_tickets').DataTable({
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
                    url: "get_my_open_tickets.php", // json datasource
                    type: "post", // method  , by default get
                    /* data: {
                        user_id: 1 
                    }, */

                    "dataSrc": function(json) { //aqui consigo trabalhar no response

                        if (json.recordsTotal == 0) {
                            $("#listagemMyTickets").collapse('hide');
                            $("#idBtnMyTickets").attr('data-toggle', '');
                        } else {
                            $("#idBtnMyTickets").attr('data-toggle', 'collapse');
                        }
                        $('#idTotalMyTickets').html(json.recordsTotal);
                        // You can also modify `json.data` if required
                        return json.data;

                    },

                    error: function() { // error handling
                        $(".users-grid-error").html("");
                        $("#users-grid").append('<tbody class="users-grid-error"><tr><th colspan="3">Informações indisponíveis no momento</th></tr></tbody>');
                        $("#users-grid_processing").css("display", "none");
                    }
                },
                // scrollY: 200,
                deferRender: true,
                // scroller: {
                //     loadingIndicator: true
                // },
                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });

            var dataTableMyInactive = $('#table_my_inactive').DataTable({
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
                    url: "get_my_inactive_tickets.php", // json datasource
                    type: "post", // method  , by default get

                    "dataSrc": function(json) { //aqui consigo trabalhar no response

                        if (json.recordsTotal == 0) {
                            $("#listagemMyInactive").collapse('hide');
                            $("#idBtnMyInactiveTickets").attr('data-toggle', '');
                        } else {
                            $("#idBtnMyInactiveTickets").attr('data-toggle', 'collapse');
                        }
                        $('#idTotalInactive').html(json.recordsTotal);
                        // You can also modify `json.data` if required
                        return json.data;

                    },
                    error: function() { // error handling
                        $(".users-grid-error").html("");
                        $("#users-grid").append('<tbody class="users-grid-error"><tr><th colspan="3">Informações indisponíveis no momento</th></tr></tbody>');
                        $("#users-grid_processing").css("display", "none");
                    }
                },
                
                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });


            /* INICIALIZANDO A TABELA COM DIVERSOS ATRIBUTOS - MUITO UTIL*/
            /* $('#table_my_closure').on('init.dt', function() {
                $('.truncate_flag')
                    .attr('data-toggle', 'popover')
                    .attr('data-content', '')
                    .attr('data-placement', 'top')
                    .attr('data-trigger', 'focus');
            }); */

            /* ENCERRADOS PELO OPERADOR LOGADO */
            if ($('#table_my_closure').length > 0) {
                var dataTableMyClosure = $('#table_my_closure').DataTable({
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
                        url: "get_my_closure_tickets.php", // json datasource
                        type: "post",

                        "dataSrc": function(json) { //aqui consigo trabalhar no response
                            if (json.recordsTotal == 0) {
                                $("#listagemMyClosure").collapse('hide');
                                $("#idBtnMyClosure").attr('data-toggle', '');
                            } else {
                                $("#idBtnMyClosure").attr('data-toggle', 'collapse');
                            }
                            $('#idTotalMyClosure').html(json.recordsTotal);
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
                    // scrollY: 200,
                    deferRender: true,
                    // scroller: {
                    //     loadingIndicator: true
                    // },
                    "language": {
                        "url": "../../includes/components/datatables/datatables.pt-br.json"
                    }
                });
            }
            


            $('#table_my_tickets').on('click', 'td', function() {

                var idFull = dataTableMyTickets.row(this).id();
                var ticket = formatRowId(idFull, 'id_');

                var colIndex = $(this).index() + 1; /* coluna */

                //QUANDO FOR A COLUNA DO RESPOSIVO OU A COLUNA DA DESCRIÇÃO TRUNCADA NAO REDIRECIONA
                if (colIndex != 1 && colIndex != 5) {
                    redirect('ticket_show.php?numero=' + ticket);
                } else if (colIndex == 5) { //coluna da descricao do chamado

                    //SE EU QUISESSE MOSTRAR EM POPOVER
                    /* $(this).attr('data-content', dataTableMyTickets.cell(this).data());
                    $(this).popover('show'); */

                    //mostrar o conteúdo da celula clicada na modal
                    $("#divPageDetails").html(dataTableMyTickets.cell(this).data());

                    $("#divPageDetails").prepend('<h4>Chamado ' + ticket + '</h4><hr>');
                    $("#divPageDetails").append("<hr><button class='btn btn-secondary' onClick=\"redirect('ticket_show.php?numero=" + ticket + "')\">Ir para o chamado</button>");
                    $('#modalDefault').modal();

                }
                // loadPageInModal('ticket_show.php?numero='+chamado);
            });


            $('#table_my_inactive').on('click', 'td', function() {

                var idFull = dataTableMyInactive.row(this).id();
                var ticket = formatRowId(idFull, 'id_');

                var colIndex = $(this).index() + 1; /* coluna */

                //QUANDO FOR A COLUNA DO RESPOSIVO OU A COLUNA DA DESCRIÇÃO TRUNCADA NAO REDIRECIONA
                if (colIndex != 1 && colIndex != 5) {
                    redirect('ticket_show.php?numero=' + ticket);
                } else if (colIndex == 5) { //coluna da descricao do chamado

                    //SE EU QUISESSE MOSTRAR EM POPOVER
                    /* $(this).attr('data-content', dataTableMyInactive.cell(this).data());
                    $(this).popover('show'); */

                    //mostrar o conteúdo da celula clicada na modal
                    $("#divPageDetails").html(dataTableMyInactive.cell(this).data());

                    $("#divPageDetails").prepend('<h4>Chamado ' + ticket + '</h4><hr>');
                    $("#divPageDetails").append("<hr><button class='btn btn-secondary' onClick=\"redirect('ticket_show.php?numero=" + ticket + "')\">Ir para o chamado</button>");
                    $('#modalDefault').modal();

                }
                // loadPageInModal('ticket_show.php?numero='+chamado);
            });




            /* $("#table_my_closure tbody").on("click", ".truncate_flag", function() {
                var index = $(this).index() + 1; //coluna
                $('table tr td:nth-child(' + index  + ')').toggleClass("truncate");
            }); */


            if ($('#table_my_closure').length > 0) {
                $('#table_my_closure').on('click', 'td', function() {
                    var idFull = dataTableMyClosure.row(this).id();
                    var ticket = formatRowId(idFull, 'id_');

                    var colIndex = $(this).index() + 1; /* coluna */

                    //QUANDO FOR A COLUNA DO RESPOSIVO OU A COLUNA DA DESCRIÇÃO TRUNCADA NAO REDIRECIONA
                    if (colIndex != 1 && colIndex != 5) {
                        redirect('ticket_show.php?numero=' + ticket);
                    } else if (colIndex == 5) { //coluna da descricao do chamado

                        //SE EU QUISESSE MOSTRAR EM POPOVER
                        /* $(this).attr('data-content', dataTableMyClosure.cell(this).data());
                        $(this).popover('show'); */

                        //mostrar o conteúdo da celula clicada na modal
                        $("#divPageDetails").html(dataTableMyClosure.cell(this).data());

                        $("#divPageDetails").prepend('<h4>Chamado ' + ticket + '</h4><hr>');
                        $("#divPageDetails").append("<hr><button class='btn btn-secondary' onClick=\"redirect('ticket_show.php?numero=" + ticket + "')\">Ir para o chamado</button>");
                        $('#modalDefault').modal();

                    }
                    // loadPageInModal('ticket_show.php?numero='+chamado);
                });
            }

            

            updateScheduled();
            getNotices();

            setInterval(function() {
                dataTableMyTickets.ajax.reload(null, false); // user paging is not reset on reload
                dataTableMyInactive.ajax.reload(null, false); // user paging is not reset on reload

                if ($('#table_my_closure').length > 0) {
                    dataTableMyClosure.ajax.reload(null, false); // user paging is not reset on reload
                }
                
                updateScheduled();
                getNotices();
            }, 60000); //a cada 1 minuto




        });


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