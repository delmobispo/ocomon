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

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$imgsPath = "../../includes/imgs/";

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

$_SESSION['s_page_ocomon'] = $_SERVER['PHP_SELF'];

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
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
    <!-- <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/DataTables-1.10.21/css/dataTables.bootstrap4.css" /> -->
    <!-- <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/Responsive-2.2.5/css/responsive.dataTables-custom.css" /> -->
    <link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2.min.css" />

    <title>OcoMon&nbsp;<?= VERSAO; ?></title>

    <style>
        .input-group>.input-group-prepend {
            /* flex: 0 0 60px; */
            max-width: 60px;
            min-width: 60px;
        }

        .input-group .input-group-text {
            width: 100%;
        }

        .input-group>.input-group-append {
            /* flex: 0 0 60px; */
            max-width: 60px;
            min-width: 60px;
        }
    </style>

</head>

<body>
    <?= $auth->showHeader(); ?>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    <div class="container-fluid">

        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important" id="modalSubs">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetails">
                    </div>
                </div>
            </div>
        </div>



        <h5 class="my-4"><i class="fas fa-filter text-secondary"></i>&nbsp;<?= TRANS('TTL_SMART_SEARCH_TO_REPORT'); ?></h5>
        <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form" onSubmit="return false;">
            <div class="form-group row my-4">
                <!-- form-row -->
                <label for="data_abertura_from" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SMART_MIN_DATE_OPENING'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_MIN_DATE_OPENING'); ?>" data-placeholder="<?= TRANS('SMART_MIN_DATE_OPENING'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-calendar-alt"></i>&nbsp;&nbsp;<i class="fas fa-plus-square"></i>&nbsp;
                            </div>
                        </div>
                        <input type="text" class="form-control " id="data_abertura_from" name="data_abertura_from" placeholder="<?= TRANS('FIELD_CURRENT_MONTH'); ?>" autocomplete="off" disabled />
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('FIELD_CURRENT_MONTH'); ?>" data-placeholder="<?= TRANS('FIELD_CURRENT_MONTH'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-calendar-check"></i>&nbsp;
                                <input type="checkbox" class="last-check-text" name="current_month" id="current_month" value="1" checked="checked">
                            </div>
                        </div>
                    </div>
                </div>

                <label for="data_abertura_to" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SMART_MAX_DATE_OPENING'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_MAX_DATE_OPENING'); ?>" data-placeholder="<?= TRANS('SMART_MAX_DATE_OPENING'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-calendar-alt"></i>&nbsp;&nbsp;<i class="fas fa-plus-square"></i>&nbsp;
                            </div>
                        </div>
                        <input type="text" class="form-control " id="data_abertura_to" name="data_abertura_to" placeholder="<?= TRANS('OCO_SEL_ANY'); ?>" autocomplete="off" />
                    </div>
                </div>

                <label for="data_atendimento_from" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SMART_MIN_DATE_FIRST_RESPONSE'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_HAS_FIRST_RESPONSE'); ?>" data-placeholder="<?= TRANS('SMART_HAS_FIRST_RESPONSE'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-comment"></i>&nbsp;
                                <input type="checkbox" name="no_empty_response" id="no_empty_response" class="first-check-text" value="1">
                            </div>
                        </div>
                        <input type="text" class="form-control " id="data_atendimento_from" name="data_atendimento_from" placeholder="<?= TRANS('OCO_SEL_ANY'); ?>" autocomplete="off" />
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_HASNT_FIRST_RESPONSE'); ?>" data-placeholder="<?= TRANS('SMART_HASNT_FIRST_RESPONSE'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-comment-slash"></i>&nbsp;
                                <input type="checkbox" class="last-check-text" name="empty_response" id="empty_response" value="1">
                            </div>
                        </div>
                    </div>
                </div>

                <label for="data_atendimento_to" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SMART_MAX_DATE_FIRST_RESPONSE'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_MAX_DATE_FIRST_RESPONSE'); ?>" data-placeholder="<?= TRANS('SMART_MAX_DATE_FIRST_RESPONSE'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-calendar-alt"></i>&nbsp;&nbsp;<i class="fas fa-comment"></i>
                            </div>
                        </div>
                        <input type="text" class="form-control " id="data_atendimento_to" name="data_atendimento_to" placeholder="<?= TRANS('OCO_SEL_ANY'); ?>" autocomplete="off" />
                    </div>
                </div>


                <label for="data_fechamento_from" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SMART_MIN_DATE_CLOSURE'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_ONLY_CLOSED'); ?>" data-placeholder="<?= TRANS('SMART_ONLY_CLOSED'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-check"></i>&nbsp;
                                <input type="checkbox" name="closed" id="closed" class="first-check-text" value="1">
                            </div>
                        </div>
                        <input type="text" class="form-control " id="data_fechamento_from" name="data_fechamento_from" placeholder="<?= TRANS('OCO_SEL_ANY'); ?>" autocomplete="off" />
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('CARDS_NOT_CLOSED'); ?>" data-placeholder="<?= TRANS('CARDS_NOT_CLOSED'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <!-- <i class="fas fa-folder-open"></i>&nbsp; -->
                                <!-- <i class="fas fa-ellipsis-h"></i>&nbsp; -->
                                <i class="fas fa-times"></i>&nbsp;
                                <input type="checkbox" class="last-check-text" name="not_closed" id="not_closed" value="1">
                            </div>
                        </div>
                    </div>
                </div>

                <label for="data_fechamento_to" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SMART_MAX_DATE_CLOSURE'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_MAX_DATE_CLOSURE'); ?>" data-placeholder="<?= TRANS('SMART_MAX_DATE_CLOSURE'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-calendar-alt"></i>&nbsp;&nbsp;<i class="fas fa-check"></i>
                            </div>
                        </div>
                        <input type="text" class="form-control " id="data_fechamento_to" name="data_fechamento_to" placeholder="<?= TRANS('OCO_SEL_ANY'); ?>" autocomplete="off" />
                    </div>
                </div>


                <label for="aberto_por" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPENED_BY'); ?></label>

                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_ONLY_BY_ENDUSER'); ?>" data-placeholder="<?= TRANS('SMART_ONLY_BY_ENDUSER'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-user"></i>&nbsp;
                                <input type="checkbox" class="first-check" name="end_user_only" id="end_user_only" value="1">
                            </div>
                        </div>
                        <select class="form-control sel2" id="aberto_por" name="aberto_por[]" multiple="multiple">
                            <?php
                            $sql = "SELECT * FROM usuarios ORDER BY nome";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                print "<option value='" . $row['user_id'] . "'";
                                print ">" . $row['nome'] . "</option>";
                            }
                            ?>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_ONLY_BY_TECHNITIANS'); ?>" data-placeholder="<?= TRANS('SMART_ONLY_BY_TECHNITIANS'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-user-md"></i>&nbsp;
                                <input type="checkbox" class="last-check" name="no_end_user" id="no_end_user" value="1">
                            </div>
                        </div>
                    </div>
                </div>



                <label for="contact_email" class="col-md-2 col-form-label col-form-label-sm text-md-right text-nowrap"><?= TRANS('CONTACT_EMAIL'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-at"></i>&nbsp;
                                <input type="checkbox" name="no_empty_contact_email" id="no_empty_contact_email" class="first-check-text" value="1">
                            </div>
                        </div>
                        <input type="text" class="form-control " id="contact_email" name="contact_email" placeholder="<?= TRANS('OCO_SEL_ANY'); ?>" />

                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-times"></i>&nbsp;
                                <input type="checkbox" name="no_contact_email" id="no_contact_email" class="last-check-text" value="1">
                            </div>
                        </div>
                    </div>
                </div>





                <label for="departamento" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>

                <div class="form-group col-md-4">
                    <div class="input-group" name="terceiro-parent">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <!-- <i class="fas fa-tag"></i>&nbsp; -->
                                <i class="fas fa-address-card"></i>&nbsp;
                                <input type="checkbox" class="first-check" name="no_empty_departamento" id="no_empty_departamento" value="1">
                            </div>
                        </div>
                        <select class="form-control sel2" id="departamento" name="departamento[]" multiple="multiple">
                            <?php
                            $sql = "SELECT * FROM localizacao ORDER BY local";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                print "<option value='" . $row['loc_id'] . "'";
                                print ">" . $row['local'] . "</option>";
                            }
                            ?>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-times"></i>&nbsp;
                                <input type="checkbox" class="last-check" name="no_departamento" id="no_departamento" value="1">
                            </div>
                        </div>
                    </div>
                </div>

                <label for="last_editor" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SMART_LAST_EDITOR'); ?></label>

                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_LAST_EDITOR'); ?>" data-placeholder="<?= TRANS('SMART_LAST_EDITOR'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-user-md"></i>&nbsp;&nbsp;<i class="fas fa-edit"></i>
                            </div>
                        </div>
                        <select class="form-control sel2" id="last_editor" name="last_editor[]" multiple="multiple">
                            <?php
                            $sql = "SELECT * FROM usuarios WHERE nivel in (1,2) ORDER BY nome";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                print "<option value='" . $row['user_id'] . "'";
                                print ">" . $row['nome'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>


                <label for="area" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RESPONSIBLE_AREA'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <!-- <i class="fas fa-circle"></i>&nbsp; -->
                                <i class="fas fa-headset"></i>&nbsp;
                                <input type="checkbox" class="first-check" name="no_empty_area" id="no_empty_area" value="1">
                            </div>
                        </div>
                        <select class="form-control sel2 " id="area" name="area[]" multiple="multiple">
                            <?php
                            $sql = "SELECT * FROM sistemas WHERE sis_status = 1 AND sis_atende = 1 ORDER BY sistema";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $rowArea) {
                                print "<option value='" . $rowArea['sis_id'] . "'";
                                print ">" . $rowArea['sistema'] . "</option>";
                            }
                            ?>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <!-- <i class="far fa-circle"></i>&nbsp; -->
                                <i class="fas fa-times"></i>&nbsp;
                                <input type="checkbox" class="last-check" name="no_area" id="no_area" value="1">
                            </div>
                        </div>
                    </div>
                </div>
                <label for="problema" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ISSUE_TYPE'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-exclamation-circle"></i>&nbsp;
                                <input type="checkbox" class="first-check" name="no_empty_problema" id="no_empty_problema" value="1">
                            </div>
                        </div>
                        <select class="form-control  sel2" id="problema" name="problema[]" multiple="multiple">
                            <?php
                            $sql = "SELECT * FROM problemas ORDER BY problema";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                print "<option value='" . $row['prob_id'] . "'";
                                print ">" . $row['problema'] . "</option>";
                            }
                            ?>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-times"></i>&nbsp;
                                <input type="checkbox" class="last-check" name="no_problema" id="no_problema" value="1">
                            </div>
                        </div>
                    </div>
                </div>


                <label for="unidade" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-city"></i>&nbsp;
                                <input type="checkbox" class="first-check" name="no_empty_unidade" id="no_empty_unidade" value="1">
                            </div>
                        </div>
                        <select class="form-control sel2 " id="unidade" name="unidade[]" multiple="multiple">
                            <?php
                            $sql = "SELECT * FROM instituicao ORDER BY inst_nome";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                print "<option value='" . $row['inst_cod'] . "'";
                                print ">" . $row['inst_nome'] . "</option>";
                            }
                            ?>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-times"></i>&nbsp;
                                <input type="checkbox" class="last-check" name="no_unidade" id="no_unidade" value="1">
                            </div>
                        </div>
                    </div>
                </div>
                <label for="etiqueta" class="col-md-2 col-form-label col-form-label-sm text-md-right text-nowrap"><?= TRANS('FIELD_TAG_EQUIP'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_NOT_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-barcode"></i>&nbsp;
                                <input type="checkbox" name="no_empty_etiqueta" id="no_empty_etiqueta" class="first-check-text" value="1">
                            </div>
                        </div>
                        <input type="text" class="form-control " id="etiqueta" name="etiqueta" placeholder="<?= TRANS('OCO_SEL_ANY'); ?>" />

                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_EMPTY'); ?>" data-placeholder="<?= TRANS('SMART_EMPTY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-times"></i>&nbsp;
                                <input type="checkbox" name="no_etiqueta" id="no_etiqueta" class="last-check-text" value="1">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <label for="contato" class="col-md-2 col-form-label col-form-label-sm text-md-right">Contato</label>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control " id="contato" name="contato" placeholder="Informe o nome do contato" />
                </div> -->



                <label for="status" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_STATUS'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_NOT_CLOSED_PAUSED_STATUS'); ?>" data-placeholder="<?= TRANS('SMART_NOT_CLOSED_PAUSED_STATUS'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-pause"></i>&nbsp;
                                <input type="checkbox" class="first-check" name="time_freeze_status_only" id="time_freeze_status_only" value="1">
                            </div>
                        </div>
                        <select class="form-control sel2 " id="status" name="status[]" multiple="multiple">
                            <?php
                            $sql = "SELECT * FROM status  ORDER BY status";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                print "<option value='" . $row['stat_id'] . "'";
                                print ">" . $row['status'] . "</option>";
                            }
                            ?>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_NOT_CLOSED_RUNNING_STATUS'); ?>" data-placeholder="<?= TRANS('SMART_NOT_CLOSED_PAUSED_STATUS'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <!-- <i class="fas fa-clock"></i>&nbsp; -->
                                <i class="fas fa-hourglass-half"></i>&nbsp;
                                <input type="checkbox" class="last-check" name="no_time_freeze_status" id="no_time_freeze_status" value="1">
                            </div>
                        </div>
                    </div>
                </div>




                <label for="relacionados" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TICKETS_REFERENCED'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SMART_ONLY_WITH_TICKETS_REFERENCED'); ?>" data-placeholder="<?= TRANS('SMART_ONLY_WITH_TICKETS_REFERENCED'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-stream"></i>&nbsp;
                                <input type="checkbox" class="first-check-text" name="only_relatives" id="only_relatives" value="1">
                            </div>
                        </div>

                        <input type="text" class="form-control " id="relacionados" name="relacionados" placeholder="<?= TRANS('OCO_SEL_ANY'); ?>" autocomplete="off" readonly />
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('SMART_ONLY_WITHOUT_TICKETS_REFERENCED'); ?>" data-placeholder="<?= TRANS('SMART_ONLY_WITHOUT_TICKETS_REFERENCED'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <!-- <i class="fas fa-clock"></i>&nbsp; -->
                                <i class="fas fa-times"></i>&nbsp;
                                <input type="checkbox" class="last-check-text" name="no_relatives" id="no_relatives" value="1">
                            </div>
                        </div>
                    </div>
                </div>



                <div class="w-100"></div>
                <!-- <i class="fas fa-handshake"></i> -->
                <label for="response_sla" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RESPONSE_SLA'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('RESPONSE_SLA'); ?>" data-placeholder="<?= TRANS('RESPONSE_SLA'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-handshake"></i>&nbsp;<i class="fas fa-comment"></i>
                            </div>
                        </div>
                        <select class="form-control sel2 " id="response_sla" name="response_sla[]" multiple="multiple">
                            <option value="1"><?= TRANS('SMART_NOT_IDENTIFIED'); ?></option>
                            <option value="2"><?= TRANS('SMART_IN_SLA'); ?></option>
                            <option value="3"><?= TRANS('SMART_IN_SLA_TOLERANCE'); ?></option>
                            <option value="4"><?= TRANS('SMART_OUT_SLA'); ?></option>

                        </select>

                    </div>
                </div>

                <label for="solution_sla" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SOLUTION_SLA'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('SOLUTION_SLA'); ?>" data-placeholder="<?= TRANS('SOLUTION_SLA'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-handshake"></i>&nbsp;<i class="fas fa-check"></i>
                            </div>
                        </div>

                        <select class="form-control sel2 " id="solution_sla" name="solution_sla[]" multiple="multiple">
                            <option value="1"><?= TRANS('SMART_NOT_IDENTIFIED'); ?></option>
                            <option value="2"><?= TRANS('SMART_IN_SLA'); ?></option>
                            <option value="3"><?= TRANS('SMART_IN_SLA_TOLERANCE'); ?></option>
                            <option value="4"><?= TRANS('SMART_OUT_SLA'); ?></option>

                        </select>

                    </div>
                </div>

                <label for="prioridade" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PRIORITY'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('COL_PRIORITY'); ?>" data-placeholder="<?= TRANS('COL_PRIORITY'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-angle-double-down"></i>
                            </div>
                        </div>
                        <select class="form-control sel2 " id="prioridade" name="prioridade[]" multiple="multiple">
                            <?php
                            $sql = "SELECT * FROM prior_atend ORDER BY pr_nivel";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                print "<option value='" . $row['pr_cod'] . "'";
                                print ">" . $row['pr_desc'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <label for="attachments" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ATTACHMENTS'); ?></label>
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" title="<?= TRANS('ONLY_TICKETS_WITH_ATTACHMENTS'); ?>" data-placeholder="<?= TRANS('ONLY_TICKETS_WITH_ATTACHMENTS'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <i class="fas fa-paperclip"></i>&nbsp;
                                <input type="checkbox" class="first-check-text" name="only_attachments" id="only_attachments" value="1">
                            </div>
                        </div>
                        <input type="text" class="form-control " id="attachments" name="attachments" placeholder="<?= TRANS('OCO_SEL_ANY'); ?>" autocomplete="off" readonly />
                        <div class="input-group-append">
                            <div class="input-group-text" title="<?= TRANS('ONLY_TICKETS_WITHOUT_ATTACHMENTS'); ?>" data-placeholder="<?= TRANS('ONLY_TICKETS_WITHOUT_ATTACHMENTS'); ?>" data-toggle="popover" data-placement="top" data-trigger="hover">
                                <!-- <i class="fas fa-clock"></i>&nbsp; -->
                                <i class="fas fa-times"></i>&nbsp;
                                <input type="checkbox" class="last-check-text" name="no_attachments" id="no_attachments" value="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group col-md-6 d-none d-md-block"></div>





                <!-- <div class="form-group col-md-6 col-sm-6 d-flex justify-content-center">
                    <button type="submit" id="idSearch" class="btn btn-primary">Confirmar Solicitação</button>
                </div>
                <div class="form-group col-md-6 col-sm-6 d-flex justify-content-center">
                    <button type="reset" class="btn btn-secondary">Cancelar</button>
                </div> -->


                <div class="row w-100"></div>
                <div class="form-group col-md-8 d-none d-md-block">
                </div>
                <div class="form-group col-12 col-md-2 ">
                    <button type="submit" id="idSearch" class="btn btn-primary btn-block"><?= TRANS('BT_SEARCH'); ?></button>
                </div>
                <div class="form-group col-12 col-md-2">
                    <button type="reset" id="idReset" class="btn btn-secondary btn-block text-nowrap"><?= TRANS('BT_CLEAR'); ?></button>
                </div>



            </div>

        </form>

    </div>


    <div id="print-info" class="d-none">&nbsp;</div>


    <div class="container-fluid">


        <div id="divResult">

        </div>


    </div>

    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <!-- <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/ColReorderWithResize/ColReorderWithResize.js"></script> -->
    <!-- <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/Responsive-2.2.5/js/dataTables.responsive.min.js"></script> -->
    <script src="../../includes/components/jquery/jquery.initialize.min.js"></script>
    <script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>
    <!-- <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script> -->
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../includes/components/select2/dist-2/js/select2.full.min.js"></script>
    <script>
        $(function() {

            // $('.modal').modal();

            $(window).resize(function() {
                $('.sel2').select2({
                    placeholder: {
                        text: '<?= TRANS('OCO_SEL_ANY', '', 1); ?>'
                    },
                    allowClear: true,
                    maximumSelectionLength: 5,
                    closeOnSelect: false,
                    minimumResultsForSearch: 10,
                });
            });

            $(function() {
                $('[data-toggle="popover"]').popover()
            });

            $('.popover-dismiss').popover({
                trigger: 'focus'
            });

            $("#data_abertura_from").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
            });
            $("#data_abertura_to").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
            });
            $("#data_atendimento_from").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
            });
            $("#data_atendimento_to").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
            });
            $("#data_fechamento_from").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
            });
            $("#data_fechamento_to").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
            });


            $('.first-check-text').on('click', function() {

                var group_parent = $(this).parents().eq(2); //object
                var select_input_id = group_parent.find(':text').attr('id');
                var last_checkbox_id = group_parent.find('input:last').attr('id');

                if ($(this).is(':checked')) {

                    $('#' + select_input_id).prop('disabled', true);
                    $('#' + last_checkbox_id).prop('checked', false);

                    $('#' + select_input_id).val('');
                    // $('#' + select_input_id).attr('placeholder', $(this).parent().attr('title'));
                    $('#' + select_input_id).attr('placeholder', $(this).parent().attr('data-placeholder'));

                } else {
                    $('#' + select_input_id).prop('disabled', false);
                    $('#' + select_input_id).attr('placeholder', '<?= TRANS('OCO_SEL_ANY', '', 1); ?>');
                }
            });

            $('.last-check-text').on('click', function() {

                var group_parent = $(this).parents().eq(2); //object
                var select_input_id = group_parent.find(':text').attr('id');
                var first_checkbox_id = group_parent.find('input:first').attr('id');

                if ($(this).is(':checked')) {
                    $('#' + select_input_id).prop('disabled', true);
                    $('#' + first_checkbox_id).prop('checked', false);

                    $('#' + select_input_id).val('');
                    // $('#' + select_input_id).attr('placeholder', $(this).parent().attr('title'));
                    $('#' + select_input_id).attr('placeholder', $(this).parent().attr('data-placeholder'));
                } else {
                    $('#' + select_input_id).prop('disabled', false);
                    $('#' + select_input_id).attr('placeholder', '<?= TRANS('OCO_SEL_ANY', '', 1); ?>');
                }
            });


            $('.first-check').on('click', function() {

                var group_parent = $(this).parents().eq(2); //object
                // var select_input_id = group_parent.find('select').attr('id');
                var select_input_id = group_parent.find('select').attr('id');
                var last_checkbox_id = group_parent.find('input:last').attr('id');

                if ($(this).is(':checked')) {

                    $('#' + select_input_id).prop('disabled', true);
                    $('#' + last_checkbox_id).prop('checked', false);
                    $('#' + select_input_id).val(null).trigger('change');
                    $('#' + select_input_id).select2({
                        // placeholder: $(this).parent().attr('title')
                        placeholder: $(this).parent().attr('data-placeholder')
                    });
                } else {
                    $('#' + select_input_id).prop('disabled', false);
                    $('#' + select_input_id).select2({
                        placeholder: "<?= TRANS('OCO_SEL_ANY', '', 1); ?>"
                    });
                }
            });

            $('.last-check').on('click', function() {

                var group_parent = $(this).parents().eq(2); //object
                var select_input_id = group_parent.find('select').attr('id');
                var first_checkbox_id = group_parent.find('input:first').attr('id');

                if ($(this).is(':checked')) {
                    $('#' + select_input_id).prop('disabled', true);
                    $('#' + first_checkbox_id).prop('checked', false);

                    $('#' + select_input_id).val(null).trigger('change');
                    $('#' + select_input_id).select2({
                        // placeholder: $(this).parent().attr('title')
                        placeholder: $(this).parent().attr('data-placeholder')
                    });
                } else {
                    $('#' + select_input_id).prop('disabled', false);
                    $('#' + select_input_id).select2({
                        placeholder: "<?= TRANS('OCO_SEL_ANY', '', 1); ?>"
                    });
                }
            });



            $('#idSearch').on('click', function(e) {
                e.preventDefault();
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });

                $(document).ajaxStop(function() {
                    loading.hide();
                });

                $.ajax({
                    url: 'get_full_tickets_table.php',
                    method: 'POST',
                    data: $('#form').serialize(),
                }).done(function(response) {
                    $('#divResult').html(response);
                });
                return false;
            });

            $("#idReset").click(function(e) {

                e.preventDefault();
                $("#form").trigger('reset');

                $(this).closest('form').find("input[type=text]").prop('disabled', false);
                $(this).closest('form').find("input[type=text]").attr('placeholder', '<?= TRANS('OCO_SEL_ANY', '', 1); ?>');
                $(this).closest('form').find("select").prop('disabled', false);

                $('.sel2').select2({
                    placeholder: {
                        text: '<?= TRANS('OCO_SEL_ANY', '', 1); ?>'
                    },
                    allowClear: true,
                    maximumSelectionLength: 5,
                    closeOnSelect: false,
                    minimumResultsForSearch: 10,
                });

                $('#data_abertura_from').prop('disabled', true);
                $('#data_abertura_from').attr('placeholder', '<?= TRANS('FIELD_CURRENT_MONTH', '', 1); ?>');

            });

            $('.sel2').select2({
                // theme: 'bootstrap4',
                placeholder: {
                    text: '<?= TRANS('OCO_SEL_ANY', '', 1); ?>'
                },
                allowClear: true,
                maximumSelectionLength: 5,
                closeOnSelect: false,
                minimumResultsForSearch: 10,
            });


            /* Adicionei o mutation observer em função dos elementos que são adicionados após o carregamento do DOM */
            var obs2 = $.initialize("#table_info", function() {
                $('#table_info').html($('#table_info_hidden').html());
                $('#print-info').html($('#table_info').html());

                /* Collumn resize */
                var pressed = false;
                var start = undefined;
                var startX, startWidth;

                $("table td").mousedown(function(e) {
                    start = $(this);
                    pressed = true;
                    startX = e.pageX;
                    startWidth = $(this).width();
                    $(start).addClass("resizing");
                });

                $(document).mousemove(function(e) {
                    if (pressed) {
                        $(start).width(startWidth + (e.pageX - startX));
                    }
                });

                $(document).mouseup(function() {
                    if (pressed) {
                        $(start).removeClass("resizing");
                        pressed = false;
                    }
                });
                /* end Collumn resize */

            }, {
                target: document.getElementById('divResult')
            }); /* o target limita o scopo do mutate observer */



            /* Adicionei o mutation observer em função dos elementos que são adicionados após o carregamento do DOM */
            var obs = $.initialize("#table_tickets_queue", function() {

                var criterios = $('#divCriterios').text();

                var table = $('#table_tickets_queue').DataTable({

                    paging: true,
                    pageLength: 25,
                    deferRender: true,
                    fixedHeader: true,
                    // scrollX: 300, /* para funcionar a coluna fixa */
                    // fixedColumns: true,
                    columnDefs: [{
                            targets: [
                                'aberto_por',
                                'contato_email',
                                'telefone',
                                'agendado',
                                'agendado_para',
                                'data_atendimento',
                                'data_fechamento',
                                'unidade',
                                'etiqueta',
                                'prioridade',
                                'tempo_absoluto'
                            ],
                            visible: false,
                        },
                        {
                            targets: ['sla', 'tempo_absoluto'],
                            orderable: false,
                            searchable: false,
                        },
                        {
                            targets: [
                                'telefone',
                                'descricao',
                                'data_abertura',
                                'agendado',
                                'agendado_para',
                                'data_atendimento',
                                'data_fechamento',
                                'tempo_absoluto',
                                'tempo'
                            ],
                            searchable: false,
                        },
                    ],

                    colReorder: {
                        iFixedColumns: 1
                    },

                    "language": {
                        "url": "../../includes/components/datatables/datatables.pt-br.json"
                    },

                });

                // new $.fn.dataTable.ColReorder(table);

                new $.fn.dataTable.Buttons(table, {

                    buttons: [{
                            extend: 'print',
                            text: '<?= TRANS('SMART_BUTTON_PRINT', '', 1) ?>',
                            title: '<?= TRANS('SMART_CUSTOM_REPORT_TITLE', '', 1) ?>',
                            // message: 'Relatório de Ocorrências',
                            message: $('#print-info').html(),
                            autoPrint: true,

                            // className: 'btn btn-primary',

                            /* customize: function(win) {
                                $(win.document.body)
                                    .css('font-size', '8pt')

                                $(win.document.body).find('table')
                                    .addClass('compact')
                                    .css('font-size', 'inherit');
                            }, */

                            customize: function(win) {
                                $(win.document.body).find('table').addClass('display').css('font-size', '10px');
                                $(win.document.body).find('tr:nth-child(odd) td').each(function(index) {
                                    $(this).css('background-color', '#f9f9f9');
                                });
                                $(win.document.body).find('h1').css('text-align', 'center');
                            },
                            exportOptions: {
                                columns: ':visible'
                            },
                        },
                        {
                            extend: 'copyHtml5',
                            text: '<?= TRANS('SMART_BUTTON_COPY', '', 1) ?>',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'excel',
                            text: "Excel",
                            exportOptions: {
                                columns: ':visible'
                            },
                            filename: '<?= TRANS('SMART_CUSTOM_REPORT_FILE_NAME', '', 1); ?>-<?= date('d-m-Y-H:i:s'); ?>',
                        },
                        {
                            extend: 'csvHtml5',
                            text: "CVS",
                            exportOptions: {
                                columns: ':visible'
                            },

                            filename: '<?= TRANS('SMART_CUSTOM_REPORT_FILE_NAME', '', 1); ?>-<?= date('d-m-Y-H:i:s'); ?>',
                        },
                        {
                            extend: 'pdfHtml5',
                            text: "PDF",

                            exportOptions: {
                                columns: ':visible',
                            },
                            title: '<?= TRANS('SMART_CUSTOM_REPORT_TITLE', '', 1); ?>',
                            filename: '<?= TRANS('SMART_CUSTOM_REPORT_FILE_NAME', '', 1); ?>-<?= date('d-m-Y-H:i:s'); ?>',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            customize: function(doc) {
                                var criterios = $('#divCriterios').text()
                                var rdoc = doc;
                                var rcout = doc.content[doc.content.length - 1].table.body.length - 1;
                                doc.content.splice(0, 1);
                                var now = new Date();
                                var jsDate = now.getDate() + '/' + (now.getMonth() + 1) + '/' + now.getFullYear() + ' ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();
                                doc.pageMargins = [30, 70, 30, 30];
                                doc.defaultStyle.fontSize = 8;
                                doc.styles.tableHeader.fontSize = 9;

                                /* O trecho abaixo insere uma primeira coluna com o número da linha */
                                // doc.content[doc.content.length - 1].table.headerRows = 2;
                                // doc.content[doc.content.length - 1].table.body[0].splice(0, 0, {
                                //     text: "SNo.",
                                //     style: "tableHeader"
                                // });
                                // var iPlus;
                                // for (var i = 0; i < rcout; i++) {
                                //     iPlus = (i + 1);
                                //     var obj = doc.content[doc.content.length - 1].table.body[i + 1];
                                //     doc.content[doc.content.length - 1].table.body[(i + 1)][0] = {
                                //         text: obj[0].text,
                                //         style: [obj[0].style],
                                //         bold: true
                                //     };
                                //     doc.content[doc.content.length - 1].table.body[(i + 1)][3] = {
                                //         text: obj[3].text,
                                //         style: [obj[3].style],
                                //         alignment: 'center',
                                //         bold: obj[3].text > 60 ? true : false,
                                //         fillColor: obj[3].text > 60 ? 'red' : null
                                //     };

                                //     doc.content[doc.content.length - 1].table.body[iPlus].splice(0, 0, {
                                //         text: iPlus,
                                //         style: obj[0].style
                                //     });
                                // }
                                /* Final do trecho de inserção da coluna com o número da linha */

                                doc['header'] = (function(page, pages) {
                                    return {
                                        table: {
                                            widths: ['100%'],
                                            headerRows: 0,
                                            body: [
                                                [{
                                                    text: '<?= TRANS('SMART_CUSTOM_REPORT_TITLE', '', 1); ?>',
                                                    alignment: 'center',
                                                    fontSize: 14,
                                                    bold: true,
                                                    margin: [0, 10, 0, 0]
                                                }],
                                                // [{
                                                //     text: [{
                                                //             text: criterios,
                                                //             bold: true
                                                //         }, 'Sub title details...1\n',
                                                //         {
                                                //             text: 'SubTitle2: ',
                                                //             bold: true
                                                //         }, 'Sub title details...2',
                                                //     ]
                                                // }]
                                            ]
                                        },
                                        layout: 'noBorders',
                                        margin: 10
                                    }
                                });

                                doc['footer'] = (function(page, pages) {
                                    return {
                                        columns: [{
                                                alignment: 'left',
                                                text: ['Criado em: ', {
                                                    text: jsDate.toString()
                                                }]
                                            },
                                            {
                                                alignment: 'center',
                                                text: 'Total ' + rcout.toString() + ' linhas'
                                            },
                                            {
                                                alignment: 'right',
                                                text: ['página ', {
                                                    text: page.toString()
                                                }, ' de ', {
                                                    text: pages.toString()
                                                }]
                                            }
                                        ],
                                        margin: 10
                                    }
                                });

                                var objLayout = {};
                                objLayout['hLineWidth'] = function(i) {
                                    return .8;
                                };
                                objLayout['vLineWidth'] = function(i) {
                                    return .5;
                                };
                                objLayout['hLineColor'] = function(i) {
                                    return '#aaa';
                                };
                                objLayout['vLineColor'] = function(i) {
                                    return '#aaa';
                                };
                                objLayout['paddingLeft'] = function(i) {
                                    return 5;
                                };
                                objLayout['paddingRight'] = function(i) {
                                    return 35;
                                };
                                doc.content[doc.content.length - 1].layout = objLayout;

                            }

                        },
                        {
                            extend: 'colvis',
                            text: '<?= TRANS('SMART_BUTTON_MANAGE_COLLUMNS', '', 1) ?>',
                            // className: 'btn btn-primary',
                            columns: ':gt(0)'
                        },
                    ]
                });

                table.buttons().container()
                    .appendTo($('.display-buttons:eq(0)', table.table().container()));

                /* $('.double-scroll').doubleScroll({
                    resetOnWindowResize: true
                }) */




            }, {
                target: document.getElementById('divResult')
            }); /* o target limita o scopo do mutate observer */

            // var obs2 = $.initialize("#table_info", function() {
            //     $('#table_info').html($('#table_info_hidden').html());
            //     $('#print-info').html($('#table_info').html());
            // }, {
            //     target: document.getElementById('divResult')
            // }); /* o target limita o scopo do mutate observer */



        });


        function openTicketInfo(ticket) {

            let location = 'ticket_show.php?numero=' + ticket;
            $("#divDetails").load(location);
            $('#modal').modal();

        }
    </script>
</body>

</html>