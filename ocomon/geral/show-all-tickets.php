<?php session_start();
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
  */

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);
$_SESSION['s_page_ocomon'] = $_SERVER['PHP_SELF'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.css" />
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

        div.dt-searchPane div.dataTables_length {
            float: none;
            text-align: center;
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
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>
    <div class="container-fluid">

        <div class="display-panel"></div>
        <div class="display-buttons"></div>
        <table id="table_tickets_queue" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
            <thead>
                <tr class="header">
                    <td class='line'></td>
                    <td class='line'><?= TRANS('NUMBER_ABBREVIATE'); ?> / <?= TRANS('AREA'); ?></td>
                    <td class='line'><?= TRANS('ISSUE_TYPE'); ?></td>
                    <td class='line'><?= TRANS('FIELD_CONTACT'); ?> / <?= TRANS('COL_PHONE'); ?></td>
                    <td class='line truncate_flag truncate'><?= TRANS('DEPARTMENT'); ?> / <?= TRANS('DESCRIPTION'); ?></td>
                    <td class='line'><?= TRANS('COL_STATUS'); ?></td>
                    <td class='line'><?= TRANS('FILTERED_TIME'); ?></td>
                    <td class='line'><?= TRANS('OCO_PRIORITY'); ?></td>
                    <td class='line'><?= TRANS('COL_SLA'); ?></td>
                </tr>
            </thead>
        </table>








    </div>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script>
        $(function() {

            var dataTableQueue = $('#table_tickets_queue').DataTable({

                columnDefs: [{
                        className: 'control',
                        orderable: false,
                        targets: 0
                    },
                    {
                        targets: [6, 7, 8],
                        orderable: false
                    },

                ],

                order: [1, 'desc'],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "get_my_queued_tickets.php", // json datasource
                    type: "post",


                    error: function() { // error handling
                        $(".users-grid-error").html("");
                        $("#users-grid").append('<tbody class="users-grid-error"><tr><th colspan="3">Informações indisponíveis no momento</th></tr></tbody>');
                        $("#users-grid_processing").css("display", "none");
                    }
                },
                // rowId: 'id',

                colReorder: true,
                deferRender: true,

                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });

            new $.fn.dataTable.Buttons(dataTableQueue, {
                buttons: [{
                        extend: 'print',
                        text: 'Imprimir',
                        customize: function(win) {
                            $(win.document.body)
                                .css('font-size', '10pt')
                                /* .prepend(
                                    '<img src="http://datatables.net/media/images/logo-fade.png" style="position:absolute; top:0; left:0;" />'
                                ); */

                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                        },
                        
                    },
                    {
                        extend: 'copyHtml5',
                        text: 'Copiar'
                    },
                    'excel', 'csvHtml5', 'pdfHtml5',
                ],
                
            });
            dataTableQueue.buttons().container()
                .appendTo($('.display-buttons:eq(0)', dataTableQueue.table().container()));




        });
    </script>
</body>

</html>