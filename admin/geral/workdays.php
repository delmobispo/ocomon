<?php /*                        Copyright 2020 Flávio Ribeiro

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

// require_once __DIR__ . "/" . "../../includes/classes/worktime/Worktime.php";
// require_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= TRANS('WORKDAYS_PROFILES'); ?></title>

    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/switch_radio.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.css" />

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
    </style>
</head>

<body>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    <div id="divResult"></div>
    <?php

    $auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);
    $auth->showHeader();
    $_SESSION['s_page_admin'] = $_SERVER['PHP_SELF'];

    ?>
    <div class="container-fluid">
        <h4 class="my-4"><i class="fas fa-clock text-secondary"></i>&nbsp;<?= TRANS('WORKDAYS_PROFILES'); ?></h4>
    <?php


    /* Mensagem de retorno */
    if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
        echo $_SESSION['flash'];
        $_SESSION['flash'] = '';
    }

    $workOnSats = false;
    $workOnSuns = false;
    $workOnHolies = false;

    if (!isset($_GET['action']) && !isset($_POST['action'])) {
        ?>
        <!-- Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-triangle text-secondary"></i>&nbsp;<?= TRANS('WORKDAYS_PROFILES_REMOVE'); ?></h5>
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
        <button class="btn btn-sm btn-primary" id="new" name="new"><?= TRANS("ACT_NEW"); ?></button><br/><br/>
        <?php

        // list all workdays profiles
        $sql = "SELECT * FROM worktime_profiles ORDER BY name";
        $sql = $conn->query($sql);

        echo "<table id='table_profiles' class='stripe hover order-column row-border' border='0' cellspacing='0' width='100%'>";
        echo "<thead>";
        echo "<tr class='header'>";
        // echo "<td class='line'>#ID</td>";
        echo "<td class='line'>" . TRANS("PROFILE_NAME") . "</td>";
        echo "<td class='line'>" . TRANS("FROM_MON_TO_FRI") . "</td>";
        echo "<td class='line'>" . TRANS("SATS") . "</td>";
        echo "<td class='line'>" . TRANS("SUNS") . "</td>";
        echo "<td class='line'>" . TRANS("MNL_FERIADOS") . "</td>";
        echo "<td class='line'>" . TRANS("BT_ALTER") . "</td>";
        echo "<td class='line'>" . TRANS("BT_REMOVE") . "</td>";
        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";

        foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {

            echo "<tr>";
            echo "<td class='line'>" . $row['name'];
            if ($row['is_default']) {
                echo "&nbsp;<span class='badge badge-info'>" . TRANS('DEFAULT_PROFILE') . "</span>";
            }
            echo "</td>";

            echo "<td class='line'>";
            if ($row['week_ini_time_hour'] == "00" && $row['week_ini_time_minute'] == "00" && $row['week_end_time_hour'] == "00" && $row['week_end_time_minute'] == "00") {
                echo TRANS('OFF_TIME');
            } else {
                echo TRANS('TIME_FROM') . " " . $row['week_ini_time_hour'] . ":" . $row['week_ini_time_minute'] . " " . TRANS('TIME_TO') . " " . $row['week_end_time_hour'] . ":" . $row['week_end_time_minute'];
            }
            echo "</td>";
            echo "<td class='line'>";
            if ($row['sat_ini_time_hour'] == "00" && $row['sat_ini_time_minute'] == "00" && $row['sat_end_time_hour'] == "00" && $row['sat_end_time_minute'] == "00") {
                echo TRANS('OFF_TIME');
            } else {
                echo TRANS('TIME_FROM') . " " . $row['sat_ini_time_hour'] . ":" . $row['sat_ini_time_minute'] . " " . TRANS('TIME_TO') . " " . $row['sat_end_time_hour'] . ":" . $row['sat_end_time_minute'];
            }
            echo "</td>";

            echo "<td class='line'>";
            if ($row['sun_ini_time_hour'] == "00" && $row['sun_ini_time_minute'] == "00" && $row['sun_end_time_hour'] == "00" && $row['sun_end_time_minute'] == "00") {
                echo TRANS('OFF_TIME');
            } else {
                echo TRANS('TIME_FROM') . " " . $row['sun_ini_time_hour'] . ":" . $row['sun_ini_time_minute'] . " " . TRANS('TIME_TO') . " " . $row['sun_end_time_hour'] . ":" . $row['sun_end_time_minute'];
            }
            echo "</td>";

            echo "<td class='line'>";
            if ($row['off_ini_time_hour'] == "00" && $row['off_ini_time_minute'] == "00" && $row['off_end_time_hour'] == "00" && $row['off_end_time_minute'] == "00") {
                echo TRANS('OFF_TIME');
            } else {
                echo TRANS('TIME_FROM') . " " . $row['off_ini_time_hour'] . ":" . $row['off_ini_time_minute'] . " " . TRANS('TIME_TO') . " " . $row['off_end_time_hour'] . ":" . $row['off_end_time_minute'];
            }
            echo "</td>";
            ?>
            <td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['id']; ?>')"><?= TRANS('BT_EDIT'); ?></button></td>
            <td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['id']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
            <?php
            echo "</tr>";
        }


        echo "</tbody>";

        echo "</table>";

        ?>
        </div>
        <?php

    } elseif (isset($_GET['action']) && $_GET['action'] == "new") {

        echo "<h6>" . TRANS('NEW_RECORD') . "</h6>";

        ?>
        <div class="container">

            <form name="form_new" id="form" method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">

                <?= csrf_input(); ?>
                <input type="hidden" name="action" value="new" />

                <div class="form-group row align-items-top">
                    <div class="form-group col-md-4">
                        <label for="name"><?= TRANS('OPT_NAME'); ?></label>
                        <input type="text" id="name" name="name" class="form-control " required>
                        <div class="invalid-feedback">
                            <?= TRANS('PROFILE_NAME_IS_MANDATORY'); ?>
                        </div>
                    </div>

                    <div class="form-group col-md-2">
                        <label>24/7</label>
                        <div class="switch-field">
                            <input type="radio" id="247" name="247" value="yes" />
                            <label for="247"><?= TRANS('YES'); ?></label>
                            <input type="radio" id="247_no" name="247" value="no" checked />
                            <label for="247_no"><?= TRANS('NOT'); ?></label>
                        </div>
                    </div>

                    <div class="form-group col-md-4">
                        <label><?= TRANS('SET_AS_DEFAULT_PROFILE'); ?></label>
                        <div class="switch-field">
                            <input type="radio" id="is_default" name="is_default" value="yes" />
                            <label for="is_default"><?= TRANS('YES'); ?></label>
                            <input type="radio" id="is_default_no" name="is_default" value="no" checked />
                            <label for="is_default_no"><?= TRANS('NOT'); ?></label>
                        </div>
                    </div>


                </div>

                <fieldset id="set-247">
                    <h4><?= TRANS('FROM_MON_TO_FRI'); ?></h4>
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label for "week_ini_time_hour"><?= TRANS('HOUR_INI'); ?></label>
                            <input type="number" id="week_ini_time_hour" name="week_ini_time_hour" class="form-control " min="0" max="23" value="00" required />
                        </div>

                        <div class="form-group col-md-2">
                            <label for "week_ini_time_minute"><?= TRANS('MINUTE_INI'); ?></label>
                            <input type="number" id="week_ini_time_minute" name="week_ini_time_minute" class="form-control " min="0" max="59" value="00" required>
                        </div>

                        <div class="form-group col-md-2">
                            <label for "week_end_time_hour"><?= TRANS('HOUR_END'); ?></label>
                            <input type="number" id="week_end_time_hour" name="week_end_time_hour" class="form-control " min="0" max="23" value="00" required>
                        </div>

                        <div class="form-group col-md-2">
                            <label for "week_end_time_minute"><?= TRANS('MINUTE_END'); ?></label>
                            <input type="number" id="week_end_time_minute" name="week_end_time_minute" class="form-control " min="0" max="59" value="00" required>
                        </div>
                    </div>


                    <h4><?= TRANS('SATS'); ?></h4>
                    <fieldset id="set_saturdays">
                        <div class="form-row">

                            <div class="form-group col-md-2">
                                <label for "sat_ini_time_hour"><?= TRANS('HOUR_INI'); ?></label>
                                <input type="number" id="sat_ini_time_hour" name="sat_ini_time_hour" class="form-control " min="0" max="23" value="00" required>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sat_ini_time_minute"><?= TRANS('MINUTE_INI'); ?></label>
                                <input type="number" id="sat_ini_time_minute" name="sat_ini_time_minute" class="form-control " min="0" max="59" value="00" required>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sat_end_time_hour"><?= TRANS('HOUR_END'); ?></label>
                                <input type="number" id="sat_end_time_hour" name="sat_end_time_hour" class="form-control " min="0" max="23" value="00" required>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sat_end_time_minute"><?= TRANS('MINUTE_END'); ?></label>
                                <input type="number" id="sat_end_time_minute" name="sat_end_time_minute" class="form-control " min="0" max="59" value="00" required>
                            </div>

                            <div class="form-group col-auto">
                                <label><?= TRANS('ON_SATURDAYS'); ?></label>
                                <div class="switch-field">
                                    <input type="radio" id="off-saturdays" name="off-saturdays" value="yes" />
                                    <label for="off-saturdays"><?= TRANS('YES'); ?></label>
                                    <input type="radio" id="off-saturdays_no" name="off-saturdays" value="no" checked />
                                    <label for="off-saturdays_no"><?= TRANS('NOT'); ?></label>
                                </div>
                            </div>

                        </div>
                    </fieldset>


                    <h4><?= TRANS('SUNS'); ?></h4>
                    <fieldset id="set_sundays">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for "sun_ini_time_hour"><?= TRANS('HOUR_INI'); ?></label>
                                <input type="number" id="sun_ini_time_hour" name="sun_ini_time_hour" class="form-control " min="0" max="23" value="00" required>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sun_ini_time_minute"><?= TRANS('MINUTE_INI'); ?></label>
                                <input type="number" id="sun_ini_time_minute" name="sun_ini_time_minute" class="form-control " min="0" max="59" value="00" required>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sun_end_time_hour"><?= TRANS('HOUR_END'); ?></label>
                                <input type="number" id="sun_end_time_hour" name="sun_end_time_hour" class="form-control " min="0" max="23" value="00" required>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sun_end_time_minute"><?= TRANS('MINUTE_END'); ?></label>
                                <input type="number" id="sun_end_time_minute" name="sun_end_time_minute" class="form-control " min="0" max="59" value="00" required>
                            </div>

                            
                            <div class="form-group col-auto">
                                <label><?= TRANS('ON_SUNDAYS'); ?></label>
                                <div class="switch-field">
                                    <input type="radio" id="off-sundays" name="off-sundays" value="yes" />
                                    <label for="off-sundays"><?= TRANS('YES'); ?></label>
                                    <input type="radio" id="off-sundays_no" name="off-sundays" value="no" checked />
                                    <label for="off-sundays_no"><?= TRANS('NOT'); ?></label>
                                </div>
                            </div>

                        </div>
                    </fieldset>


                    <h4><?= TRANS('MNL_FERIADOS'); ?></h4>
                    <fieldset id="set_holidays">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for "off_ini_time_hour"><?= TRANS('HOUR_INI'); ?></label>
                                <input type="number" id="off_ini_time_hour" name="off_ini_time_hour" class="form-control " min="0" max="23" value="00" required>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "off_ini_time_minute"><?= TRANS('MINUTE_INI'); ?></label>
                                <input type="number" id="off_ini_time_minute" name="off_ini_time_minute" class="form-control " min="0" max="59" value="00" required>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "off_end_time_hour"><?= TRANS('HOUR_END'); ?></label>
                                <input type="number" id="off_end_time_hour" name="off_end_time_hour" class="form-control " min="0" max="23" value="00" required>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "off_end_time_minute"><?= TRANS('MINUTE_END'); ?></label>
                                <input type="number" id="off_end_time_minute" name="off_end_time_minute" class="form-control " min="0" max="59" value="00" required>
                            </div>

                            
                            <div class="form-group col-auto">
                                <label><?= TRANS('ON_HOLIDAYS'); ?></label>
                                <div class="switch-field">
                                    <input type="radio" id="off-holidays" name="off-holidays" value="yes" />
                                    <label for="off-holidays"><?= TRANS('YES'); ?></label>
                                    <input type="radio" id="off-holidays_no" name="off-holidays" value="no" checked/>
                                    <label for="off-holidays_no"><?= TRANS('NOT'); ?></label>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                </fieldset>

                <div class="row w-100">
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2 ">
                        <button type="submit" id="idSubmit" class="btn btn-primary mb-2" name="new_profile"><?= TRANS('BT_OK') ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="button" id="bt-cancel" class="btn btn-secondary mb-2"><?= TRANS('BT_CANCEL') ?></button>
                    </div>
                </div>


            </form>

        </div>

    <?php

    } elseif (isset($_GET['action']) && $_GET['action'] == "edit") {

        echo "<h6>" . TRANS('WORKDAYS_PROFILES_EDIT') . "</h6>";

        $id = intval($_GET['cod']);
        $sql = "SELECT * FROM worktime_profiles WHERE id = {$id}";
        $sql = $conn->query($sql);
        if (!$sql->rowCount()) {
            echo "<h1>" . TRANS('NO_RECORDS_FOUND') . "</h1>";
            exit;
        }
        $row = $sql->fetch(PDO::FETCH_ASSOC);

        ?>
        <div class="container">

            <form name="form_edit" id="form" method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                <!-- novalidate -->
                <?= csrf_input(); ?>

                <input type="hidden" name="action" value="edit" />
                <input type="hidden" name="cod" value="<?= $id; ?>" />

                <div class="form-row align-items-top">
                    <div class="form-group col-md-4">
                        <label for="name"><?= TRANS('OPT_NAME'); ?></label>
                        <input type="text" id="name" name="name" class="form-control " value="<?= $row['name'] ?>" required>
                        <div class="invalid-feedback">
                            <?= TRANS('PROFILE_NAME_IS_MANDATORY'); ?>
                        </div>
                    </div>

                    <?php
                        $checked = ($row['247'] == 1 ? "checked" : "");
                        $checked_no = ($row['247'] == 0 ? "checked" : "");
                    ?>
                    <div class="form-group col-md-2">
                        <label>24/7</label>
                        <div class="switch-field">
                            <input type="radio" id="247" name="247" value="yes" <?= $checked; ?> />
                            <label for="247"><?= TRANS('YES'); ?></label>
                            <input type="radio" id="247_no" name="247" value="no" <?= $checked_no; ?> />
                            <label for="247_no"><?= TRANS('NOT'); ?></label>
                        </div>
                    </div>

                    <?php
                        $checked = ($row['is_default'] == 1 ? "checked" : "");
                        $checked_no = ($row['is_default'] == 0 ? "checked" : "");
                    ?>
                    <div class="form-group col-md-4">
                        <label><?= TRANS('SET_AS_DEFAULT_PROFILE'); ?></label>
                        <div class="switch-field">
                            <input type="radio" id="is_default" name="is_default" value="yes" <?= $checked; ?> />
                            <label for="is_default"><?= TRANS('YES'); ?></label>
                            <input type="radio" id="is_default_no" name="is_default" value="no" <?= $checked_no; ?> />
                            <label for="is_default_no"><?= TRANS('NOT'); ?></label>
                        </div>
                    </div>


                </div>

                <fieldset id="set-247">
                    <h4><?= TRANS('FROM_MON_TO_FRI'); ?></h4>
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label for "week_ini_time_hour"><?= TRANS('HOUR_INI'); ?></label>
                            <input type="number" id="week_ini_time_hour" name="week_ini_time_hour" class="form-control " min="0" max="23" value="<?= $row['week_ini_time_hour'] ?>" required />
                        </div>

                        <div class="form-group col-md-2">
                            <label for "week_ini_time_minute"><?= TRANS('MINUTE_INI'); ?></label>
                            <input type="number" id="week_ini_time_minute" name="week_ini_time_minute" class="form-control " min="0" max="59" value="<?= $row['week_ini_time_minute'] ?>" required>
                        </div>

                        <div class="form-group col-md-2">
                            <label for "week_end_time_hour"><?= TRANS('HOUR_END'); ?></label>
                            <input type="number" id="week_end_time_hour" name="week_end_time_hour" class="form-control " min="0" max="23" value="<?= $row['week_end_time_hour'] ?>" required>
                        </div>

                        <div class="form-group col-md-2">
                            <label for "week_end_time_minute"><?= TRANS('MINUTE_END'); ?></label>
                            <input type="number" id="week_end_time_minute" name="week_end_time_minute" class="form-control " min="0" max="59" value="<?= $row['week_end_time_minute'] ?>" required>
                        </div>
                    </div>


                    <h4><?= TRANS('SATS'); ?></h4>
                    <fieldset id="set_saturdays">
                        <div class="form-row">

                            <div class="form-group col-md-2">
                                <label for "sat_ini_time_hour"><?= TRANS('HOUR_INI'); ?></label>
                                <input type="number" id="sat_ini_time_hour" name="sat_ini_time_hour" class="form-control " min="0" max="23" value="<?= $row['sat_ini_time_hour'] ?>" required>
                                <?php
                                    if ($row['sat_ini_time_hour'] != "00") $workOnSats = true;
                                ?>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sat_ini_time_minute"><?= TRANS('MINUTE_INI'); ?></label>
                                <input type="number" id="sat_ini_time_minute" name="sat_ini_time_minute" class="form-control " min="0" max="59" value="<?= $row['sat_ini_time_minute'] ?>" required>
                                <?php
                                    if ($row['sat_ini_time_minute'] != "00") $workOnSats = true;
                                ?>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sat_end_time_hour"><?= TRANS('HOUR_END'); ?></label>
                                <input type="number" id="sat_end_time_hour" name="sat_end_time_hour" class="form-control " min="0" max="23" value="<?= $row['sat_end_time_hour'] ?>" required>
                                <?php
                                    if ($row['sat_end_time_hour'] != "00") $workOnSats = true;
                                ?>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sat_end_time_minute"><?= TRANS('MINUTE_END'); ?></label>
                                <input type="number" id="sat_end_time_minute" name="sat_end_time_minute" class="form-control " min="0" max="59" value="<?= $row['sat_end_time_minute'] ?>" required>
                                <?php
                                    if ($row['sat_end_time_minute'] != "00") $workOnSats = true;
                                ?>
                            </div>

                            <?php
                                $checked = ($workOnSats ? "checked" : "");
                                $checked_no = (!$workOnSats ? "checked" : "");
                            ?>
                            <div class="form-group col-auto">
                                <label><?= TRANS('ON_SATURDAYS'); ?></label>
                                <div class="switch-field">
                                    <input type="radio" id="off-saturdays" name="off-saturdays" value="yes" <?= $checked; ?>/>
                                    <label for="off-saturdays"><?= TRANS('YES'); ?></label>
                                    <input type="radio" id="off-saturdays_no" name="off-saturdays" value="no"  <?= $checked_no; ?>/>
                                    <label for="off-saturdays_no"><?= TRANS('NOT'); ?></label>
                                </div>
                            </div>
                        </div>
                    </fieldset>


                    <h4><?= TRANS('SUNS'); ?></h4>
                    <fieldset id="set_sundays">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for "sun_ini_time_hour"><?= TRANS('HOUR_INI'); ?></label>
                                <input type="number" id="sun_ini_time_hour" name="sun_ini_time_hour" class="form-control " min="0" max="23" value="<?= $row['sun_ini_time_hour'] ?>" required>
                                <?php
                                    if ($row['sun_ini_time_hour'] != "00") $workOnSuns = true;
                                ?>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sun_ini_time_minute"><?= TRANS('MINUTE_INI'); ?></label>
                                <input type="number" id="sun_ini_time_minute" name="sun_ini_time_minute" class="form-control " min="0" max="59" value="<?= $row['sun_ini_time_minute'] ?>" required>
                                <?php
                                    if ($row['sun_ini_time_minute'] != "00") $workOnSuns = true;
                                ?>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sun_end_time_hour"><?= TRANS('HOUR_END'); ?></label>
                                <input type="number" id="sun_end_time_hour" name="sun_end_time_hour" class="form-control " min="0" max="23" value="<?= $row['sun_end_time_hour'] ?>" required>
                                <?php
                                    if ($row['sun_end_time_hour'] != "00") $workOnSuns = true;
                                ?>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "sun_end_time_minute"><?= TRANS('MINUTE_END'); ?></label>
                                <input type="number" id="sun_end_time_minute" name="sun_end_time_minute" class="form-control " min="0" max="59" value="<?= $row['sun_end_time_minute'] ?>" required>
                                <?php
                                    if ($row['sun_end_time_minute'] != "00") $workOnSuns = true;
                                ?>
                            </div>

                            
                            <?php
                                $checked = ($workOnSuns ? "checked" : "");
                                $checked_no = (!$workOnSuns ? "checked" : "");
                            ?>
                            <div class="form-group col-auto">
                                <label><?= TRANS('ON_SUNDAYS'); ?></label>
                                <div class="switch-field">
                                    <input type="radio" id="off-sundays" name="off-sundays" value="yes" <?= $checked; ?>/>
                                    <label for="off-sundays"><?= TRANS('YES'); ?></label>
                                    <input type="radio" id="off-sundays_no" name="off-sundays" value="no"  <?= $checked_no; ?>/>
                                    <label for="off-sundays_no"><?= TRANS('NOT'); ?></label>
                                </div>
                            </div>

                        </div>
                    </fieldset>


                    <h4><?= TRANS('MNL_FERIADOS'); ?></h4>
                    <fieldset id="set_holidays">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for "off_ini_time_hour"><?= TRANS('HOUR_INI'); ?></label>
                                <input type="number" id="off_ini_time_hour" name="off_ini_time_hour" class="form-control " min="0" max="23" value="<?= $row['off_ini_time_hour'] ?>" required>
                                <?php
                                    if ($row['off_ini_time_hour'] != "00") $workOnHolies = true;
                                ?>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "off_ini_time_minute"><?= TRANS('MINUTE_INI'); ?></label>
                                <input type="number" id="off_ini_time_minute" name="off_ini_time_minute" class="form-control " min="0" max="59" value="<?= $row['off_ini_time_minute'] ?>" required>
                                <?php
                                    if ($row['off_ini_time_minute'] != "00") $workOnHolies = true;
                                ?>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "off_end_time_hour"><?= TRANS('HOUR_END'); ?></label>
                                <input type="number" id="off_end_time_hour" name="off_end_time_hour" class="form-control " min="0" max="23" value="<?= $row['off_end_time_hour'] ?>" required>
                                <?php
                                    if ($row['off_end_time_hour'] != "00") $workOnHolies = true;
                                ?>
                            </div>

                            <div class="form-group col-md-2">
                                <label for "off_end_time_minute"><?= TRANS('MINUTE_END'); ?></label>
                                <input type="number" id="off_end_time_minute" name="off_end_time_minute" class="form-control " min="0" max="59" value="<?= $row['off_end_time_minute'] ?>" required>
                                <?php
                                    if ($row['off_end_time_minute'] != "00") $workOnHolies = true;
                                ?>
                            </div>

                            <?php
                                $checked = ($workOnHolies ? "checked" : "");
                                $checked_no = (!$workOnHolies ? "checked" : "");
                            ?>
                            <div class="form-group col-auto">
                                <label><?= TRANS('ON_HOLIDAYS'); ?></label>
                                <div class="switch-field">
                                    <input type="radio" id="off-holidays" name="off-holidays" value="yes" <?= $checked; ?>/>
                                    <label for="off-holidays"><?= TRANS('YES'); ?></label>
                                    <input type="radio" id="off-holidays_no" name="off-holidays" value="no" <?= $checked_no; ?> />
                                    <label for="off-holidays_no"><?= TRANS('NOT'); ?></label>
                                </div>
                            </div>

                        </div>
                    </fieldset>

                </fieldset>
                <div class="w-100"></div>
                <!-- <div class="row w-100"> -->
                <div class="form-row">
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="submit" id="idSubmit" class="btn btn-primary mb-2" name="edit"><?= TRANS('BT_OK') ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="button" id="bt-cancel" class="btn btn-secondary mb-2"><?= TRANS('BT_CANCEL') ?></button>
                    </div>
                </div>
                <!-- </div> -->

            </form>

        </div>
        <?php

    }



    ?>
    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script type="text/javascript">
        $(document).ready(function() { //ESCOPO DE MONITORAMENTO DA PÁGINA

            $('#table_profiles').DataTable({
                paging: true,
                deferRender: true,
                columnDefs: [{
                    orderable: false,
                    targets: [5, 6]
                }],
                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });

            $(".close").click(function() {
                $("#msgSuccessNew").alert("close");
                $("#msgSuccessEdit").alert("close");
            });
            $("#msgSuccessNew").on('closed.bs.alert', function() {
                var url = "workdays.php";
                $(location).prop('href', url);
            });
            $("#msgSuccessEdit").on('closed.bs.alert', function() {
                var url = "workdays.php";
                $(location).prop('href', url);
            });


            // $('.j_modal_btn').on('click', function() {
            //     var id = $(this).data('id');
            //     $('.j_param_id').text(id);
            //     $('#modalRemove').modal();
            // });

            $('#new').on("click", function() {
                $('#idLoad').css('display', 'block');
                var url = "workdays.php?action=new";
                $(location).prop('href', url);
            });

            $('#bt-cancel').on('click', function() {
                var url = "workdays.php";
                $(location).prop('href', url);
            });


            if ($('#247').is(":checked")) {
                $('#set-247').prop("disabled", true);
            }


            $('.form-control').on('change', function(){

                if (isWorkOnSats()) {
                    $('#off-saturdays').prop('checked', true);
                    $('#off-saturdays_no').prop('checked', false);
                } else {
                    $('#off-saturdays').prop('checked', false);
                    $('#off-saturdays_no').prop('checked', true);
                }
                if (isWorkOnSuns()) {
                    $('#off-sundays').prop('checked', true);
                    $('#off-sundays_no').prop('checked', false);
                } else {
                    $('#off-sundays').prop('checked', false);
                    $('#off-sundays_no').prop('checked', true);
                }
                if (isWorkOnHolies()) {
                    $('#off-holidays').prop('checked', true);
                    $('#off-holidays_no').prop('checked', false);
                } else {
                    $('#off-holidays').prop('checked', false);
                    $('#off-holidays_no').prop('checked', true);
                }
            });


            $('[name="247"]').on('change', function(){
				if ($(this).val() == "no") {
                    $('#set-247').prop("disabled", false);
                    $('#off-saturdays').prop('disabled', false).prop('checked', true);
                    $('#off-saturdays_no').prop('disabled', false).prop('checked', false);
                    $('#off-sundays').prop('disabled', false).prop('checked', true);
                    $('#off-sundays_no').prop('disabled', false).prop('checked', false);
                    $('#off-holidays').prop('disabled', false).prop('checked', true);
                    $('#off-holidays_no').prop('disabled', false).prop('checked', false);
                    enableSaturdays();
                    enableSundays();
                    enableOffdays();
					
				} else {
                    $('#set-247').prop("disabled", true);
                    $('#off-saturdays').prop('checked', true);
                    $('#off-saturdays_no').prop('checked', false);
                    $('#off-sundays').prop('checked', true);
                    $('#off-sundays_no').prop('checked', false);
                    $('#off-holidays').prop('checked', true);
                    $('#off-holidays_no').prop('checked', false);
                    setWeekdaysFull();
                    setSaturdaysFull();
                    setSundaysFull();
                    setOffdaysFull();
                    // uncheckOffdays();
				}
			});


            $('[name="off-saturdays"]').on('change', function(){
				if ($(this).val() == "no") {
					disableSaturdays();
                    setSaturdaysOff();
				} else {
					enableSaturdays();
				}
			});



            $('[name="off-sundays"]').on('change', function(){
				if ($(this).val() == "no") {
					disableSundays();
                    setSundaysOff();
				} else {
					enableSundays();
				}
			});

            $('[name="off-holidays"]').on('change', function(){
				if ($(this).val() == "no") {
                    disableOffdays();
                    setOffdaysOff();
                } else {
                    enableOffdays();
                }
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
					url: './workdays_process.php',
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
            




        }); // final do escopo de monitoramento


        // function confirmDeleteModal(id) {
        //     $('#deleteModal').modal();
        //     $('#deleteButton').html('<a class="btn btn-danger" onclick="deleteData(' + id + ')">Delete</a>');
        // }

        // function deleteData(id) {
        //     // do your stuffs with id
        //     var url = "workdays.php?action=remove&id=" + id + "&successRemove=true";
        //     $(location).prop('href', url);

        //     // $("#successMessage").html("Registro com  " + id + " Removido com sucesso!");
        //     $('#deleteModal').modal('hide'); // now close modal
        // }


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
				url: './workdays_process.php',
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

        function setWeekdaysFull() {
            $('#week_ini_time_hour').val("00");
            $('#week_ini_time_minute').val("00");
            $('#week_end_time_hour').val("23");
            $('#week_end_time_minute').val("59");
        }

        function setWeekdaysOff() {
            $('#week_ini_time_hour').val("00");
            $('#week_ini_time_minute').val("00");
            $('#week_end_time_hour').val("00");
            $('#week_end_time_minute').val("00");
        }

        function isWorkOnSats() {
            let iniHour = parseInt($('#sat_ini_time_hour').val());
            let iniMinute = parseInt($('#sat_ini_time_minute').val());
            let endHour = parseInt($('#sat_end_time_hour').val());
            let endMinute = parseInt($('#sat_end_time_minute').val());

            if (iniHour >= 1 || iniMinute >= 1 || endHour >= 1 || endMinute >= 1  ) {
                return true;
            }
            return false;
        }

        function isWorkOnSuns() {
            let iniHour = parseInt($('#sun_ini_time_hour').val());
            let iniMinute = parseInt($('#sun_ini_time_minute').val());
            let endHour = parseInt($('#sun_end_time_hour').val());
            let endMinute = parseInt($('#sun_end_time_minute').val());

            if (iniHour >= 1 || iniMinute >= 1 || endHour >= 1 || endMinute >= 1  ) {
                return true;
            }
            return false;
        }

        function isWorkOnHolies() {
            let iniHour = parseInt($('#off_ini_time_hour').val());
            let iniMinute = parseInt($('#off_ini_time_minute').val());
            let endHour = parseInt($('#off_end_time_hour').val());
            let endMinute = parseInt($('#off_end_time_minute').val());

            if (iniHour >= 1 || iniMinute >= 1 || endHour >= 1 || endMinute >= 1  ) {
                return true;
            }
            return false;
        }
        

        function setSaturdaysFull() {
            $('#sat_ini_time_hour').val("00");
            $('#sat_ini_time_minute').val("00");
            $('#sat_end_time_hour').val("23");
            $('#sat_end_time_minute').val("59");
        }

        function setSaturdaysOff() {
            $('#sat_ini_time_hour').val("00");
            $('#sat_ini_time_minute').val("00");
            $('#sat_end_time_hour').val("00");
            $('#sat_end_time_minute').val("00");
        }

        function setSundaysFull() {
            $('#sun_ini_time_hour').val("00");
            $('#sun_ini_time_minute').val("00");
            $('#sun_end_time_hour').val("23");
            $('#sun_end_time_minute').val("59");
        }

        function setSundaysOff() {
            $('#sun_ini_time_hour').val("00");
            $('#sun_ini_time_minute').val("00");
            $('#sun_end_time_hour').val("00");
            $('#sun_end_time_minute').val("00");
        }

        function setOffdaysFull() {
            $('#off_ini_time_hour').val("00");
            $('#off_ini_time_minute').val("00");
            $('#off_end_time_hour').val("23");
            $('#off_end_time_minute').val("59");
        }

        function setOffdaysOff() {
            $('#off_ini_time_hour').val("00");
            $('#off_ini_time_minute').val("00");
            $('#off_end_time_hour').val("00");
            $('#off_end_time_minute').val("00");
        }

        function disableSaturdays() {
            $('#sat_ini_time_hour').prop("disabled", true);
            $('#sat_ini_time_minute').prop("disabled", true);
            $('#sat_end_time_hour').prop("disabled", true);
            $('#sat_end_time_minute').prop("disabled", true);
        }

        function enableSaturdays() {
            $('#sat_ini_time_hour').prop("disabled", false);
            $('#sat_ini_time_minute').prop("disabled", false);
            $('#sat_end_time_hour').prop("disabled", false);
            $('#sat_end_time_minute').prop("disabled", false);
        }

        function disableSundays() {
            $('#sun_ini_time_hour').prop("disabled", true);
            $('#sun_ini_time_minute').prop("disabled", true);
            $('#sun_end_time_hour').prop("disabled", true);
            $('#sun_end_time_minute').prop("disabled", true);
        }

        function enableSundays() {
            $('#sun_ini_time_hour').prop("disabled", false);
            $('#sun_ini_time_minute').prop("disabled", false);
            $('#sun_end_time_hour').prop("disabled", false);
            $('#sun_end_time_minute').prop("disabled", false);
        }

        function disableOffdays() {
            $('#off_ini_time_hour').prop("disabled", true);
            $('#off_ini_time_minute').prop("disabled", true);
            $('#off_end_time_hour').prop("disabled", true);
            $('#off_end_time_minute').prop("disabled", true);
        }

        function enableOffdays() {
            $('#off_ini_time_hour').prop("disabled", false);
            $('#off_ini_time_minute').prop("disabled", false);
            $('#off_end_time_hour').prop("disabled", false);
            $('#off_end_time_minute').prop("disabled", false);
        }

        function uncheckOffdays() {
            $('#off-saturdays').prop("checked", false);
            $('#off-sundays').prop("checked", false);
            $('#off-holidays').prop("checked", false);
        }

        function checkOffdays() {
            $('#off-saturdays').prop("checked", true);
            $('#off-sundays').prop("checked", true);
            $('#off-holidays').prop("checked", true);
        }
    </script>

</body>

</html>