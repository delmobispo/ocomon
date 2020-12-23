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


$areaAdmin = 0;
if (isset($_SESSION['s_area_admin']) && $_SESSION['s_area_admin'] == '1' && $_SESSION['s_nivel'] != '1') {
    $areaAdmin = 1;
}

if ($areaAdmin) {
    $auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);
} else {
    $auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);
}

$_SESSION['s_page_admin'] = $_SERVER['PHP_SELF'];


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
    <!-- <link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2.min.css" /> -->

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

    <div id="divResult"></div>


    <div class="container-fluid">
        <h5 class="my-4"><i class="fas fa-user-friends text-secondary"></i>&nbsp;<?= TRANS('MNL_USUARIOS'); ?></h5>
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

        $query = "SELECT u.*, n.*,s.* FROM usuarios u 
                    LEFT JOIN sistemas AS s ON u.AREA = s.sis_id
                    LEFT JOIN nivel AS n ON n.nivel_cod = u.nivel 
                WHERE 1 = 1 ";


        if ($areaAdmin) {
            $query .= " AND s.sis_id = " . $_SESSION['s_area'] . " ";
        }

        if (isset($_GET['cod'])) {
            $query .= " AND u.user_id = '" . $_GET['cod'] . "' ";
        }
        $query .= "ORDER BY u.nome";
        $resultado = $conn->query($query);
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

            <div class="modal fade" id="deleteTmpModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title" id="modalTitle"><i class="fas fa-exclamation-triangle text-secondary"></i>&nbsp;<?= TRANS('REMOVE'); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?= TRANS('confirm_exclui'); ?> <span class="j_param_id"></span>?
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
                            <button type="button" id="deleteTmpButton" class="btn"><?= TRANS('BT_OK'); ?></button>
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
                <table id="table_users" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
                    <thead>
                        <tr class="header">
                            <td class="line name"><?= TRANS('OPT_NAME'); ?></td>
                            <td class="line login"><?= TRANS('OPT_LOGIN_NAME'); ?></td>
                            <td class="line area"><?= TRANS('RESPONSIBLE_AREA'); ?></td>
                            <td class="line email"><?= TRANS('COL_EMAIL'); ?></td>
                            <td class="line admin"><?= TRANS('OCO_FIELD_AREA_ADMIN'); ?></td>
                            <td class="line level"><?= TRANS('LEVEL'); ?></td>
                            <td class="line last_logon"><?= TRANS('LAST_LOGON'); ?></td>
                            <td class="line editar"><?= TRANS('BT_EDIT'); ?></td>
                            <td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>
                        </tr>
                    </thead>
                </table>
                <div class="chart-container">
                    <canvas id="canvasChart1"></canvas>
                </div>


                <?php
                if (!$areaAdmin) {
                ?>
                    <h6 class="my-4"><?= TRANS('WAITING_CONFIRMATION'); ?></h6>
                    <table id="table_users_tmp" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
                        <thead>
                            <tr class="header">
                                <td class="line name"><?= TRANS('COL_NAME'); ?></td>
                                <td class="line login"><?= TRANS('COL_LOGIN'); ?></td>
                                <td class="line email"><?= TRANS('COL_EMAIL'); ?></td>
                                <td class="line email"><?= TRANS('DATE'); ?></td>
                                <td class="line editar"><?= TRANS('BT_OK'); ?></td>
                                <td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>
                            </tr>
                        </thead>
                    </table>
            <?php
                }
            }
        } else
		if ((isset($_GET['action'])  && ($_GET['action'] == "new")) && !isset($_POST['submit'])) {

            ?>
            <h6><?= TRANS('NEW_RECORD'); ?></h6>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <?= csrf_input(); ?>
                <div class="form-group row my-4">

                    <label for="login_name" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_LOGIN'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control" id="login_name" name="login_name" required />
                    </div>

                    <div class="w-100"></div>

                    <label for="password" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PASSWORD'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="password" class="form-control " id="password" name="password" required />
                    </div>

                    <label for="password2" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RETYPE_PASS'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="password" class="form-control " id="password2" name="password2" required />
                    </div>

                    <label for="fullname" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('FULLNAME'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="fullname" name="fullname" required />
                    </div>

                    <label for="level" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LEVEL'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control" name="level" id="level" required>
                            <option value=""><?= TRANS('SEL_LEVEL'); ?></option>
                            <?php
                            if ($areaAdmin) {
                                $sql = "SELECT * FROM nivel WHERE nivel_cod = '" . $_SESSION['s_nivel'] . "' ORDER BY nivel_nome ";
                            } else {
                                $sql = "SELECT * FROM nivel WHERE nivel_cod <> 5 ORDER BY nivel_nome";
                            }
                            $res = $conn->query($sql);
                            foreach ($res->fetchall() as $row) {
                            ?>
                                <option value='<?= $row['nivel_cod']; ?>'><?= $row['nivel_nome']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>


                    <label for="subscribe_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_SUBSCRIBE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="subscribe_date" name="subscribe_date" value="<?= date("d/m/Y H:i:s"); ?>" required readonly />
                    </div>

                    <label for="hire_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('HIRE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="hire_date" name="hire_date" value="" />
                    </div>

                    <label for="email" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_EMAIL'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="email" class="form-control " id="email" name="email" required />
                    </div>

                    <label for="phone" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="tel" class="form-control " id="phone" name="phone" required />
                    </div>

                    <label for="primary_area" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PRIMARY_AREA'); ?></label>
                    <div class="form-group col-md-4 ">
                        <select class="form-control" id="primary_area" name="primary_area" required>
                            <option id="sel_areas" value=""><?= TRANS('LOADING'); ?></option>
                        </select>
                    </div>

                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_AREA_ADMIN'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <input type="radio" id="area_admin" name="area_admin" value="yes" />
                        <label for="area_admin"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="area_admin_no" name="area_admin" value="no" checked />
                        <label for="area_admin_no"><?= TRANS('NOT'); ?></label>
                    </div>


                </div>

                <div class="form-group row my-4" id="div_secondary_areas"></div>

                <div class="form-group row my-4">
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


                    <label for="login_name" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_LOGIN'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control " id="login_name" name="login_name" value="<?= (isset($row['login']) ? $row['login'] : ""); ?>" required readonly />
                    </div>

                    <div class="w-100"></div>

                    <label for="password" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PASSWORD'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="password" class="form-control " id="password" name="password" placeholder="<?= TRANS('PASSWORD_EDIT_PLACEHOLDER'); ?>" />
                    </div>

                    <label for="password2" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RETYPE_PASS'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="password" class="form-control " id="password2" name="password2" placeholder="<?= TRANS('PASSWORD_EDIT_PLACEHOLDER'); ?>" />
                    </div>

                    <label for="fullname" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('FULLNAME'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="fullname" name="fullname" value="<?= (isset($row['nome']) ? $row['nome'] : ""); ?>" required />
                    </div>

                    <label for="level" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LEVEL'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control" name="level" id="level" required>
                            <option value=""><?= TRANS('SEL_LEVEL'); ?></option>
                            <?php
                            if ($areaAdmin) {
                                $sql = "SELECT * FROM nivel WHERE nivel_cod in (" . $_SESSION['s_nivel'] . " , 5) ORDER BY nivel_nome ";
                            } else {
                                $sql = "SELECT * FROM nivel WHERE nivel_cod NOT IN (4) ORDER BY nivel_nome";
                            }
                            $res = $conn->query($sql);
                            foreach ($res->fetchall() as $rowLevel) {
                            ?>
                                <option value='<?= $rowLevel['nivel_cod']; ?>' <?= ($rowLevel['nivel_cod'] == $row['nivel'] ? 'selected' : ''); ?>><?= $rowLevel['nivel_nome']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>


                    <label for="subscribe_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_SUBSCRIBE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="subscribe_date" name="subscribe_date" value="<?= (isset($row['data_inc']) ? dateScreen($row['data_inc'],1) : ""); ?>" required readonly />
                    </div>

                    <label for="hire_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('HIRE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="hire_date" name="hire_date" value="<?= (isset($row['data_admis']) ? dateScreen($row['data_admis'], 1) : ""); ?>" />
                    </div>

                    <label for="email" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_EMAIL'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="email" class="form-control " id="email" name="email" value="<?= (isset($row['email']) ? $row['email'] : ""); ?>" required />
                    </div>

                    <label for="phone" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="tel" class="form-control " id="phone" name="phone" value="<?= (isset($row['fone']) ? $row['fone'] : ""); ?>" required />
                    </div>

                    <label for="primary_area" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PRIMARY_AREA'); ?></label>
                    <div class="form-group col-md-4 ">
                        <select class="form-control" id="primary_area" name="primary_area" required>
                            <option id="sel_areas" value=""><?= TRANS('LOADING'); ?></option>

                        </select>
                    </div>

                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_AREA_ADMIN'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <?php
                        $yesChecked = ($row['user_admin'] == 1 ? "checked" : "");
                        $noChecked = ($row['user_admin'] == 0 ? "checked" : "");
                        ?>
                        <input type="radio" id="area_admin" name="area_admin" value="yes" <?= $yesChecked; ?> />
                        <label for="area_admin"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="area_admin_no" name="area_admin" value="no" <?= $noChecked; ?> />
                        <label for="area_admin_no"><?= TRANS('NOT'); ?></label>
                    </div>
                </div>


                <div class="form-group row my-4" id="div_secondary_areas"></div>

                <div class="form-group row my-4">

                    <input type="hidden" name="cod" id="cod" value="<?= $_GET['cod']; ?>">
                    <input type="hidden" name="area" id="idArea" value="<?= $row['sis_id']; ?>">
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
    <script src="../../includes/components/jquery/MHS/jquery.md5.min.js"></script>
    <!-- <script src="../../includes/components/jquery/jquery.initialize.min.js"></script> -->
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/dist/Chart.min.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-colorschemes/dist/chartjs-plugin-colorschemes.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-datalabels/chartjs-plugin-datalabels.min.js"></script>

    <script src="./ajax/user_x_level.js"></script>
    <script type="text/javascript">
        $(function() {

            if ($('#canvasChart1').length)
                showTotalGraph();

            $("#hire_date").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
            });


            $.ajax({
                url: 'get_possible_areas.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    'level': $('#level').val()
                },
                success: function(data) {

                    var area = (typeof($('#idArea') !== 'undefined') ? $('#idArea').val() : "");
                    $('#sel_areas').text('<?= TRANS('SEL_AREA'); ?>');
                    if ($('#level').val() == "") {
                        $('#sel_areas').text('<?= TRANS('SEL_LEVEL_FIRST'); ?>');
                    } else {
                        $.each(data, function(key, data) {
                            $('#primary_area').append('<option value="' + data.sis_id + '"' + (data.sis_id == area ? 'selected' : '') + '>' + data.sistema + '</option>');
                        });
                    }
                }
            });

            $.ajax({
                url: 'get_secondary_areas.php',
                type: 'POST',
                data: {
                    // 'primary_area': $('#primary_area').val(), 
                    'primary_area': (typeof($('#idArea') !== 'undefined') ? $('#idArea').val() : ""),
                    'level': $('#level').val(),
                    'cod': (typeof $('#cod') !== 'undefined' ? $('#cod').val() : ""),
                    // 'action': $('#action').val()
                },
                success: function(data) {
                    $('#div_secondary_areas').html(data);
                }
            });

            $('#level').on("change", function() {
                $.ajax({
                    url: 'get_possible_areas.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'level': $(this).val()
                    },
                    success: function(data) {

                        $('#primary_area').empty();

                        if ($('#level').val() == "") {
                            $('#primary_area').append('<option value="" selected id="sel_areas">' + '<?= TRANS('SEL_LEVEL_FIRST'); ?>' + '</option>');
                        } else {
                            $('#primary_area').append('<option value="" selected id="sel_areas">' + '<?= TRANS('SEL_AREA'); ?>' + '</option>');
                            $.each(data, function(key, data) {
                                $('#primary_area').append('<option value="' + data.sis_id + '">' + data.sistema + '</option>');
                            });
                        }
                    }
                });
            });


            $('#primary_area, #level').on("change", function() {
                $.ajax({
                    url: 'get_secondary_areas.php',
                    type: 'POST',
                    data: {
                        'primary_area': $('#primary_area').val(),
                        'level': $('#level').val(),
                        'cod': (typeof $('#cod') !== 'undefined' ? $('#cod').val() : "")
                    },
                    success: function(data) {
                        $('#div_secondary_areas').html(data);
                    }
                });
            });


            var dataTable = $('#table_users').DataTable({
                "processing": true,
                "serverSide": true,
                deferRender: true,
                columnDefs: [{
                    searchable: false,
                    orderable: false,
                    targets: ['editar', 'remover']
                }, ],
                "ajax": {
                    url: "users-grid-data.php", // json datasource
                    type: "post", // method  , by default get
                    data: {
                        "areaAdmin": '<?= $areaAdmin ?>'
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


            if ($('#table_users_tmp').length) {
                var dataTableTmp = $('#table_users_tmp').DataTable({
                    "processing": true,
                    "serverSide": true,
                    deferRender: true,
                    columnDefs: [{
                        searchable: false,
                        orderable: false,
                        targets: ['editar', 'remover']
                    }, ],
                    "ajax": {
                        url: "userstmp_grid_data.php", // json datasource
                        type: "post", // method  , by default get
                        data: {
                            "areaAdmin": '<?= $areaAdmin ?>'
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
            }

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

                let password = ($('#password').val() != "" ? $.MD5($('#password').val()) : "");
                let password2 = ($('#password2').val() != "" ? $.MD5($('#password2').val()) : "");
                
                let form = $('#form').serialize();
                form = removeParam('password', form);
                form = removeParam('password2', form);
                form += "&password=" + password + "&password2=" + password2;
                
                $("#idSubmit").prop("disabled", true);
                $.ajax({
                    url: './users_process.php',
                    method: 'POST',
                    // data: $('#form').serialize(),
                    data: form,
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
                url: './users_process.php',
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


        function confirmDeleteModalTmp(id) {
            $('#deleteTmpModal').modal();
            $('#deleteTmpButton').html('<a class="btn btn-danger" onclick="deleteTmpData(' + id + ')"><?= TRANS('REMOVE'); ?></a>');
        }

        function deleteTmpData(id) {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './new_user_confirm_process.php',
                method: 'POST',
                data: {
                    cod: id,
                    action: 'delete',
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

        function confirmUser(id) {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './new_user_confirm_process.php',
                method: 'POST',
                data: {
                    cod: id,
                    action: 'adminconfirm',
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