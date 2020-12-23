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

$_SESSION['s_page_home'] = $_SERVER['PHP_SELF'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/switch_radio.css" />
    <link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2.min.css" />

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

        /* Style the CKEditor element to look like a textfield */
        .cke_textarea_inline {
            width: 100%;
            padding: 10px;
            height: 100px;
            overflow: auto;
            border: 1px solid #ced4da;
            border-radius: 4px;
            -webkit-appearance: textfield;
        }
    </style>

    <title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>
    <?= $auth->showHeader(); ?>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    <div id="divResult"></div>


    <div class="container-fluid">
        <h4 class="my-4"><i class="fas fa-bell text-secondary"></i>&nbsp;<?= TRANS('TLT_BOARD_NOTICE'); ?></h4>
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

        $types = [];
        $types['warning'] = TRANS('TOAST_WARNING');
        $types['error'] = TRANS('TOAST_ERROR');
        $types['info'] = TRANS('TOAST_INFO');
        $types['success'] = TRANS('TOAST_SUCCESS');

        /* Checa avisos expirados */
        $sql = "UPDATE avisos SET 
                    is_active = 0 
                WHERE expire_date < '" . date("Y-m-d") . "'
        ";
        $conn->exec($sql);


        $terms = "( a.area IN (" . $_SESSION['s_uareas'] . ") OR a.area = -1 OR a.area IS NULL) AND ";
        if ($_SESSION['s_nivel'] == 1) {
            /* só filtra para as áreas caso não seja administrador do sistema */
            $terms = "";
        }
        $query = "SELECT a.*, u.*, ar.* 
                    FROM 
                        usuarios u, avisos a 
                    LEFT JOIN sistemas ar ON a.area = ar.sis_id 
                    WHERE 
                        {$terms} a.origem = u.user_id ";

        if (isset($_GET['cod'])) {
            $query .= " AND a.aviso_id = '" . $_GET['cod'] . "'";
        }
        $query .= " ORDER BY u.nome";


        try {
            $resultado = $conn->query($query);
        } catch (Exception $e) {
            echo message('danger', 'Ooops!', $e->getMessage(), '');
            return false;
        }

        $registros = $resultado->rowCount();

        if ((!isset($_GET['action'])) && !isset($_POST['submit'])) {

        ?>
            <!-- Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-triangle text-secondary"></i>&nbsp;<?= TRANS('REMOVE'); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?= TRANS('confirm_exclui'); ?> <span class="j_param_id"></span>?
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
                            <button type="button" id="deleteButton" class="btn"><?= TRANS('BT_OK'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn btn-sm btn-primary" id="idBtIncluir" name="new"><?= TRANS("ACT_NEW"); ?></button><br /><br />
            <?php
            if ($registros == 0) {
                echo message('info', '', TRANS('NO_RECORDS_FOUND'), '', '', true);
            } else {

            ?>
                <table id="table_lists" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">

                    <thead>
                        <tr class="header">
                            <td class="line title"><?= TRANS('TITLE'); ?></td>
                            <td class="line area" width="30%"><?= TRANS('NOTICE'); ?></td>
                            <td class="line subject"><?= TRANS('AUTHOR'); ?></td>
                            <td class="line subject"><?= TRANS('DESTINY_AREA'); ?></td>
                            <td class="line email"><?= TRANS('COL_TYPE'); ?></td>
                            <td class="line screen_profile"><?= TRANS('DATE'); ?></td>
                            <td class="line wc_profile"><?= TRANS('COL_STATUS'); ?></td>
                            <td class="line status"><?= TRANS('ACTIVE_UNTIL'); ?></td>
                            <td class="line editar"><?= TRANS('BT_EDIT'); ?></td>
                            <td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        foreach ($resultado->fetchall() as $row) {

                            // $lstatus = ($row['is_active'] == 0 ? TRANS('INACTIVE') : TRANS('ACTIVE'));
                            $lstatus = ($row['is_active'] == 0 ? "<span class='badge badge-danger'>" . TRANS('INACTIVE_O') . "</span>" : "<span class='badge badge-success'>" . TRANS('ACTIVE_O') . "</span>");
                            if ($row['is_active'] == "") {
                                // $lstatus = TRANS('MSG_NOT_DEFINED');
                                $lstatus = "<span class='badge badge secondary'>" . TRANS('MSG_NOT_DEFINED') . "</span>";
                            }
                            
                            $areaDestiny = (($row['area'] == "-1" OR $row['area'] == "") ? TRANS('ALL_TREATERS_AREAS') : "");

                            if (empty($areaDestiny)) {
                                $sql = "SELECT sistema FROM sistemas WHERE sis_id IN (" . $row['area']. ") ORDER BY sistema";
                                try {
                                    $res = $conn->query($sql);
                                    foreach ($res->fetchall() as $rowArea) {
                                        if (strlen($areaDestiny) > 0)
                                            $areaDestiny .= ", ";
                                        $areaDestiny .= $rowArea['sistema'];
                                    }
                                }
                                catch (Exception $e) {
                                    echo 'Erro: ', $e->getMessage(), "<br/>";
                                }
                            }
                            
                            $noticeType = TRANS('MSG_NOT_DEFINED');
                            foreach ($types as $key => $type) {
                                if ($row['status'] == $key)
                                    $noticeType = $type;
                            }

                        ?>
                            <tr>
                                <td class="line"><?= trim($row['title']); ?></td>
                                <td class="line"><?= trim($row['avisos']); ?></td>
                                <td class="line"><?= $row['nome']; ?></td>
                                <td class="line"><?= $areaDestiny; ?></td>
                                <td class="line"><?= $noticeType; ?></td>
                                <td class="line"><?= dateScreen($row['data']); ?></td>
                                <td class="line"><?= $lstatus; ?></td>
                                <td class="line"><?= dateScreen($row['expire_date'],1); ?></td>
                                <td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['aviso_id']; ?>')"><?= TRANS('BT_EDIT'); ?></button></td>
                                <td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['aviso_id']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
                            </tr>

                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            <?php
            }
        } else
		if ((isset($_GET['action'])  && ($_GET['action'] == "new")) && !isset($_POST['submit'])) {

            ?>
            <h6><?= TRANS('NEW_RECORD'); ?></h6>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <?= csrf_input(); ?>
                <div class="form-group row my-4">

                    <label for="title" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('TITLE'); ?></label>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control" id="title" name="title" required />
                        <div class="invalid-feedback">
                            <?= TRANS('MANDATORY_FIELD'); ?>
                        </div>
                    </div>


                    <label for="notice" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('NOTICE'); ?></label>
                    <div class="form-group col-md-10">
                        <textarea class="form-control" id="notice" name="notice" required></textarea>
                        <div class="invalid-feedback">
                            <?= TRANS('MANDATORY_FIELD'); ?>
                        </div>
                        <small class="text-mute"><?= TRANS('HELPER_NOTICE'); ?></small>
                    </div>


                    <label for="type" class="col-md-2 col-form-label text-md-right"><?= TRANS('COL_TYPE'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control " id="type" name="type">

                            <?php
                            foreach ($types as $key => $type) {
                            ?>
                                <option value="<?= $key; ?>" <?= ($key == 'info' ? ' selected' : ''); ?>><?= $type; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <label for="area" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('DESTINY_AREA'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="area" name="area[]" required multiple="multiple">
                            <!-- <option value="-1"><?= TRANS('ALL_TREATERS_AREAS'); ?></option> -->
                            <?php
                            $areas = getAreas($conn, 0, 1, 1);
                            foreach ($areas as $area) {
                            ?>
                                <option value="<?= $area['sis_id']; ?>"><?= $area['sistema']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <label for="expire_date" class="col-md-2 col-form-label text-md-right"><?= TRANS('ACTIVE_UNTIL'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control" name="expire_date" id="expire_date" />
                    </div>

                    <div class="row w-100"></div>
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2 ">

                        <input type="hidden" name="action" id="action" value="new">
                        <button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>


                </div>
            </form>
        <?php
        } else

		if ((isset($_GET['action']) && $_GET['action'] == "edit") && empty($_POST['submit'])) {

            $row = $resultado->fetch();
        ?>
            <h6><?= TRANS('BT_EDIT'); ?></h6>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <?= csrf_input(); ?>

                <div class="form-group row my-4">

                    <label for="author" class="col-md-2 col-form-label text-md-right"><?= TRANS('AUTHOR'); ?></label>
                    <div class="form-group col-md-4">

                        <input type="text" class="form-control" name="author" id="author" value="<?= getUserInfo($conn, $row['origem'])['nome']; ?>" disabled />
                    </div>

                    <label for="date_record" class="col-md-2 col-form-label text-md-right"><?= TRANS('DATE'); ?></label>
                    <div class="form-group col-md-4">

                        <input type="text" class="form-control" name="date_record" id="date_record" value="<?= dateScreen($row['data'], 1); ?>" disabled />
                    </div>

                    <label for="title" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('TITLE'); ?></label>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control" id="title" name="title" value="<?= $row['title']; ?>" required />
                        <div class="invalid-feedback">
                            <?= TRANS('MANDATORY_FIELD'); ?>
                        </div>
                    </div>

                    <label for="notice" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('NOTICE'); ?></label>
                    <div class="form-group col-md-10">
                        <textarea class="form-control" id="notice" name="notice" required><?= $row['avisos']; ?></textarea>
                        <div class="invalid-feedback">
                            <?= TRANS('MANDATORY_FIELD'); ?>
                        </div>
                    </div>

                    <label for="type" class="col-md-2 col-form-label text-md-right"><?= TRANS('COL_TYPE'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control " id="type" name="type">
                            <option value=""><?= TRANS('SEL_TYPE'); ?></option>
                            <?php
                            foreach ($types as $key => $type) {
                            ?>
                                <option value="<?= $key; ?>" <?= ($key == $row['status'] ? ' selected' : ''); ?>><?= $type; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>



                    <label for="area" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('DESTINY_AREA'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="area" name="area[]" required multiple="multiple">
                            <!-- <option value="-1"><?= TRANS('ALL_TREATERS_AREAS'); ?></option> -->
                            <?php
                            $areas = getAreas($conn, 0, 1, 1);
                            foreach ($areas as $area) {
                            ?>
                                <option value="<?= $area['sis_id']; ?>" <?= (isIn($area['sis_id'], $row['area']) ? ' selected' : ''); ?>><?= $area['sistema']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <label for="expire_date" class="col-md-2 col-form-label text-md-right"><?= TRANS('ACTIVE_UNTIL'); ?></label>
                    <div class="form-group col-md-4">
                        <?php
                        $expire_date = (!empty($row['expire_date']) ? dateScreen($row['expire_date'], 1) : "");
                        ?>
                        <input type="text" class="form-control" name="expire_date" id="expire_date" value="<?= $expire_date; ?>" />
                        <input type="hidden" name="expire_date_copy" id="expire_date_copy" value="<?= $expire_date; ?>" />
                    </div>
                    


                    <label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('COL_STATUS'); ?>"><?= firstLetterUp(TRANS('COL_STATUS')); ?></label>
                    <div class="form-group col-md-4 ">
                        <div class="switch-field">
                            <?php
                            $yesChecked = ($row['is_active'] == 1 ? "checked" : "");
                            $noChecked = (!($row['is_active'] == 1) ? "checked" : "");
                            ?>
                            <input type="radio" id="active_status" name="active_status" value="yes" <?= $yesChecked; ?> />
                            <label for="active_status"><?= TRANS('ACTIVE_O'); ?></label>
                            <input type="radio" id="active_status_no" name="active_status" value="no" <?= $noChecked; ?> />
                            <label for="active_status_no"><?= TRANS('INACTIVE_O'); ?></label>
                        </div>
                    </div>

                    <label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('RE_SEND'); ?>"><?= firstLetterUp(TRANS('RE_SEND')); ?></label>
                    <div class="form-group col-md-4 ">
                        <div class="switch-field">
                            <?php
                            $yesChecked = "";
                            $noChecked = "checked";
                            ?>
                            <input type="radio" id="resend" name="resend" value="yes" <?= $yesChecked; ?> />
                            <label for="resend"><?= TRANS('YES'); ?></label>
                            <input type="radio" id="resend_no" name="resend" value="no" <?= $noChecked; ?> />
                            <label for="resend_no"><?= TRANS('NOT'); ?></label>
                        </div>
                    </div>




                    <input type="hidden" name="cod" value="<?= $_GET['cod']; ?>">
                    <input type="hidden" name="action" id="action" value="edit">

                    <div class="row w-100"></div>
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2 ">
                        <button type="submit" id="idSubmit" name="submit" value="edit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>

                </div>
            </form>
        <?php
        }
        ?>
    </div>

    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/ckeditor/ckeditor.js"></script>
    <script src="../../includes/components/select2/dist-2/js/select2.full.min.js"></script>
    <script type="text/javascript">
        $(function() {

            $('#table_lists').DataTable({
                paging: true,
                deferRender: true,
                // order: [0, 'DESC'],
                columnDefs: [{
                    searchable: false,
                    orderable: false,
                    targets: ['editar', 'remover']
                }],
                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });

            $(function() {
                $('[data-toggle="popover"]').popover()
            });

            $('.popover-dismiss').popover({
                trigger: 'focus'
            });

            var bar = '<?php print $_SESSION['s_formatBarMural']; ?>';
            if ((typeof($('#notice').val()) !== 'undefined') && bar == 1) {
                var formatBar = CKEDITOR.inline('notice', {
                    uiColor: '#CCCCCC',
                    language: 'pt-br'
                });

                formatBar.on('change', function() {
                    formatBar.updateElement();
                });
            }

            
            $("#expire_date").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
                minDate: '+1d'
            });


            if ($('#active_status').length > 0) {
                if (!$('#active_status').is(':checked')) {
                    $('#expire_date').prop('disabled', true);
                }
            }
            
            
            $('[name="active_status"]').on('change', function () {
                if (!$('#active_status').is(':checked')) {
                    $('#expire_date').prop('disabled', true);
                } else {
                    $('#expire_date').prop('disabled', false);
                }
            });

            $('.sel2').select2({
                // theme: 'bootstrap4',
                placeholder: {
                    text: '<?= TRANS('ALL_TREATERS_AREAS', '', 1); ?>'
                },
                allowClear: true,
                maximumSelectionLength: 5,
                closeOnSelect: false,
                minimumResultsForSearch: 10,
            });

            $('input, select, textarea').on('change', function() {
                $(this).removeClass('is-invalid');
            });

            $('#idSubmit').on('click', function(e) {
                e.preventDefault();
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });

                $("#idSubmit").prop("disabled", true);
                $.ajax({
                    url: './notices_process.php',
                    method: 'POST',
                    data: $('#form').serialize(),
                    dataType: 'json',
                }).done(function(response) {

                    if (!response.success) {
                        $('#divResult').html(response.message);
                        $('input, select, textarea').removeClass('is-invalid');
                        if (response.field_id != "") {
                            $('#' + response.field_id).focus().addClass('is-invalid');
                        }
                        $("#idSubmit").prop("disabled", false);
                    } else {
                        $('#divResult').html('');
                        $('input, select, textarea').removeClass('is-invalid');
                        $("#idSubmit").prop("disabled", false);
                        var url = '<?= $_SERVER['PHP_SELF'] ?>';
                        $(location).prop('href', url);
                        return false;
                    }
                });
                return false;
            });

            $('#idBtIncluir').on("click", function() {
                $('#idLoad').css('display', 'block');
                var url = '<?= $_SERVER['PHP_SELF'] ?>?action=new';
                $(location).prop('href', url);
            });

            $('#bt-cancel').on('click', function() {
                var url = '<?= $_SERVER['PHP_SELF'] ?>';
                $(location).prop('href', url);
            });
        });


        function dateBrToDate (dateBr) {
            var pieces = dateBr.split("/");
            return pieces[2] + '-' + pieces[1] + '-' + pieces[0];
            // let date = new Date(pieces[2] + '-' + pieces[1] + '-' + pieces[0]);
            // return date;
        }

        function today() {
            var date = new Date();

            var year = date.getFullYear().toString();
            var month = (date.getMonth() + 101).toString().substring(1);
            var day = (date.getDate() + 100).toString().substring(1);

            return year + '-' + month + '-' + day;
        }




        function confirmDeleteModal(id) {
            $('#deleteModal').modal();
            $('#deleteButton').html('<a class="btn btn-danger" onclick="deleteData(' + id + ')"><?= TRANS('REMOVE'); ?></a>');
        }

        function deleteData(id) {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './notices_process.php',
                method: 'POST',
                data: {
                    cod: id,
                    action: 'delete'
                },
                dataType: 'json',
            }).done(function(response) {
                var url = '<?= $_SERVER['PHP_SELF'] ?>';
                $(location).prop('href', url);
                return false;
            });
            return false;
            // $('#deleteModal').modal('hide'); // now close modal
        }
    </script>
</body>

</html>