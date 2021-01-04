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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);

$config = getConfig($conn);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" />
    <link rel="stylesheet" href="../../includes/components/jquery/timePicker/jquery.timepicker.css" />
    <!--     <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/css/bootstrap.min.css" /> -->
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" /> <!-- custom bootstrap v4.5 -->
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <title>OcoMon&nbsp;<?= VERSAO; ?></title>
    <style>
        .navbar-nav>.nav-link:hover {
            background-color: #3a4d56 !important;
        }

        .nav-pills>li>a.active {
            /* background-color: #6c757d !important; */
            background-color: #48606b !important;
        }

        .navbar-nav i {
            margin-right: 3px;
            font-size: 12px;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            -ms-flex-negative: 0;
            flex-shrink: 0;
            /* background-color: #3a4d56; */
            border-radius: 4px;
        }

    </style>

</head>

<body class="bg-light">

    <?php

    if (isset($_POST['numero']) && !empty($_POST['numero'])) {
        $COD = $_POST['numero'];
    } else
	if (isset($_GET['numero']) && !empty($_GET['numero'])) {
        $COD = $_GET['numero'];
    } else {
        echo message('warning', 'Ooops!', TRANS('MSG_ERR_NOT_EXECUTE'), '', '', 1);
        return;
    }

    $query = $QRY["ocorrencias_full_ini"] . " where numero in (" . $COD . ") order by numero";
    $resultado = $conn->query($query);
    $row = $resultado->fetch();

    $GLOBALACCESS = false;

    if (isset($_GET['id'])) {
        // if (!strcmp($_GET['id'], $rowID['gt_id'])) $GLOBALACCESS = true;
        // else $GLOBALACCESS = false;
        $GLOBALACCESS = true;
    }

    if ($_SESSION['s_nivel'] == 3 && !$GLOBALACCESS) { //SOMENTE ABERTURA
        if ($row['aberto_por_cod'] != $_SESSION['s_uid']) {
            print "" . TRANS('MSG_NOT_ALLOWED') . "";
            exit;
        }
    }

    /* ASSENTAMENTOS */
    if ($_SESSION['s_nivel'] < 3) {
        $query2 = "select a.*, u.* from assentamentos a, usuarios u where a.responsavel=u.user_id and a.ocorrencia=" . $COD . "";
    } else
        $query2 = "select a.*, u.* from assentamentos a, usuarios u where a.responsavel=u.user_id and a.ocorrencia=" . $COD . " and a.asset_privated = 0";

    $resultAssets = $conn->query($query2);
    $assentamentos = $resultAssets->rowCount();


    /* CHECA SE A OCORRÊNCIA É SUB CHAMADO */
    $sqlPai = "select * from ocodeps where dep_filho = " . $COD . " ";
    // $execpai = mysql_query($sqlPai) or die (TRANS('ERR_QUERY'));
    $execpai = $conn->query($sqlPai);
    $rowPai = $execpai->fetch();
    if ($rowPai && $rowPai['dep_pai'] != "") {
        /* $msgPai = "<img src='" . ICONS_PATH . "view_tree.png' width='16' height='16' title='" . TRANS('FIELD_CALL_BOND') . "'><u><a onClick=\"javascript: popup_alerta('ticket_show.php?popup=true&numero=" . $rowPai['dep_pai'] . "')\">" . TRANS('FIELD_OCCO_SUB_CALL') . "" . $rowPai['dep_pai'] . "</a></u>"; */

        // $msgPai = message("info", "", TRANS('FIELD_OCCO_SUB_CALL') . "" . $rowPai['dep_pai'], "divMsgSubCall", "", true);
        $msgPai = "
            <div class='d-flex justify-content-center'>
                <div class='alert alert-info fade show w-100' role='alert' id='divMsgSubCall' style=' z-index:1030 !important;'>
                    <i class='fas fa-info-circle'></i>&nbsp;" . TRANS('FIELD_OCCO_SUB_CALL') . "<strong onClick=\"location.href = '" . $_SERVER['PHP_SELF'] . "?numero=" . $rowPai['dep_pai'] . "'\" style='cursor: pointer'>" . $rowPai['dep_pai'] . "</strong>
                    
                </div>
            </div>
        ";
    } else
        $msgPai = "";


    /* CHECAGEM PARA VER SE É CHAMADO PAI */
    $qrySubCall = "select * from ocodeps where dep_pai = " . $COD . "";
    $execSubCall = $conn->query($qrySubCall);
    $existeSub = $execSubCall->rowCount();


    /* INÍCIO DAS CHECAGENS PARA A MONTAGEM DO MENU DE OPÇÕES */
    $showItemClosure = false;
    $showItemEdit = false;
    $showItemAttend = false;
    $showItemOpenSubcall = false;
    $showItemReopen = false;
    $showItemPrint = true;
    $showItemSla = true;
    $showItemDocTime = false; /* Essa função será removida - pouca utilidade e muito onerosa */
    $showItemSendMail = false;
    $showItemHistory = true;

    $showItemSchedule = false;

    $itemClosure = "";
    $itemEdit = "";
    $itemAttend = "";
    $itemOpenSubcall = "";
    $itemReopen = "";

    $itemPrint = "print_ticket.php?numero=" . $row['numero']; /* TRANS('FIELD_PRINT_OCCO') */
    $itemSla = "mostra_sla_definido.php?popup=true&numero=" . $row['numero']; /* TRANS('COL_SLA') */
    $itemDocTime = "tempo_doc.php?popup=true&cod=" . $row['numero']; /* TRANS('FIELD_TIME_DOCUMENTATION') */
    $itemSendMail = "";
    $itemHistory = "ticket_history.php?numero=".$row['numero'];

    if ($row['status_cod'] != 4 && $_SESSION['s_nivel'] < 3) {
        $showItemClosure = true;
        $itemClosure = "ticket_close.php?numero=" . $row['numero']; //TRANS('CLOSE_TICKET')

        $showItemSchedule = true;
    }

    if ($_SESSION['s_nivel'] < 3) {
        $showItemEdit = true;
        $itemEdit = "ticket_edit.php?numero=" . $row['numero']; /* TRANS('BT_EDIT') */
    }

    if (($row['status_cod'] != 2) && ($row['status_cod'] != 4) && ($_SESSION['s_nivel'] < 3)) {
        $showItemAttend = true;
    }

    if ($row['status_cod'] != 4 && $_SESSION['s_nivel'] < 3) {
        $showItemOpenSubcall = true;
        $itemOpenSubcall = "ticket_add.php?popup=true&pai=" . $row['numero'] . "&invTag=" . $row['etiqueta'] . "&invInst=" . $row['unidade_cod'] . "&invLoc=" . $row['setor_cod'] . "&contato=" . $row['contato'] . "&telefone=" . $row['telefone'];
        /* TRANS('TO_OPEN_SUBTICKET') */
    }

    if ($row['status_cod'] == 4 && $_SESSION['s_allow_reopen']) { //CHECAGEM PARA PERMITIR QUE O CHAMADO SEJA REABERTO NO SISTEMA.
        $showItemReopen = true;
    }

    if ($_SESSION['s_nivel'] < 3) {
        $showItemSendMail = true;
        $itemSendMail = "form_send_mail.php?popup=true&numero=" . $row['numero']; /* TRANS('SEND_EMAIL') */
    }
    /* final das chegagens para a montagem do menu de opções */


    /* INÍCIO DAS CONSULTAS REFERENTES À OCORRÊNCIA */

    $isClosed = ($row['status_cod'] == 4 ? true : false);
    $isScheduled = ($row['oco_scheduled'] == 1 ? true : false);

    $getPriorityDesc = "SELECT * FROM prior_atend WHERE pr_cod = '" . $row['oco_prior'] . "'";
    $execGetPrior = $conn->query($getPriorityDesc);
    $rowGet = $execGetPrior->fetch();

    $qryCatProb = "SELECT * FROM problemas as p " .
        "LEFT JOIN sistemas as s on p.prob_area = s.sis_id " .
        "LEFT JOIN sla_solucao as sl on sl.slas_cod = p.prob_sla " .
        "LEFT JOIN prob_tipo_1 as pt1 on pt1.probt1_cod = p.prob_tipo_1 " .
        "LEFT JOIN prob_tipo_2 as pt2 on pt2.probt2_cod = p.prob_tipo_2 " .
        "LEFT JOIN prob_tipo_3 as pt3 on pt3.probt3_cod = p.prob_tipo_3 " .
        " WHERE p.prob_id = " . $row['prob_cod'] . " ";
    $execCatprob = $conn->query($qryCatProb);
    $rowCatProb = $execCatprob->fetch();

    $descricao = "";
    if (isset($_GET['destaca'])) {
        $descricao = destaca($_GET['destaca'], toHtml(nl2br($row['descricao'])));
    } else {
        $descricao = toHtml(nl2br($row['descricao']));
    }

    $ShowlinkScript = "";
    $qryScript = "SELECT * FROM prob_x_script WHERE prscpt_prob_id = " . $row['prob_cod'] . "";
    $execQryScript = $conn->query($qryScript);
    if ($execQryScript->rowCount() > 0)
        // $ShowlinkScript = "<a onClick=\"popup_alerta('../../admin/geral/scripts.php?action=popup&prob=" . $row['prob_cod'] . "')\"><img height='16' width='16' src='" . ICONS_PATH . "help-16.png' title='" . TRANS('HNT_SCRIPT_PROB') . "'></a>";
        $ShowlinkScript = "<a onClick=\"popup_alerta('../../admin/geral/scripts_documentation.php?action=endview&prob=" . $row['prob_cod'] . "')\" title='" . TRANS('HNT_SCRIPT_PROB') . "'><i class='far fa-lightbulb text-success'></i></a>";


    $global_link = getGlobalUri($conn, $_GET['numero']);


    // $dateOpen = formatDate($row['data_abertura']);
    $dateOpen = (!empty($row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') ? formatDate($row['oco_real_open_date']): formatDate($row['data_abertura']));
    $dateClose = formatDate($row['data_fechamento']);
    $dateLastSchedule = formatDate($row['oco_scheduled_to']);
    $dateScheduleTo = "";
    $timeScheduleTo = "";
    $dateRealOpen = formatDate($row['oco_real_open_date']);
    $scriptSolution = "";

    if ($isClosed) {
        if ($row['data_abertura'] != $row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') {
            $dateLastSchedule = formatDate($row['data_abertura']);
            $dateClose = formatDate($row['data_fechamento']);
            $dateRealOpen = formatDate($row['oco_real_open_date']);
        } else {
            // $dateOpen = formatDate($row['data_abertura']);
            $dateOpen = (!empty($row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') ? formatDate($row['oco_real_open_date']): formatDate($row['data_abertura']));
            $dateClose = formatDate($row['data_fechamento']);
        }

        $scriptSolution = $row['script_desc'];
    } else {
        if ($isScheduled) {
            // $dateOpen = formatDate($row['data_abertura']);
            $dateOpen = (!empty($row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') ? formatDate($row['oco_real_open_date']): formatDate($row['data_abertura']));

            // $dateScheduleTo = formatDate($row['oco_scheduled_to']);
            $dateScheduleTo = dateScreen($row['oco_scheduled_to'],1);

            $timeScheduleTo = explode(" ", $row['oco_scheduled_to'])[1];
            // $timeScheduleTo = $dateTimeArray[1];

        } else {
            // $dateOpen = formatDate($row['data_abertura']);
            $dateOpen = (!empty($row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00') ? formatDate($row['oco_real_open_date']): formatDate($row['data_abertura']));
            // $dateScheduleTo = formatDate($row['oco_scheduled_to']);
        }

        if ($row['data_abertura'] != $row['oco_real_open_date'] && $row['oco_real_open_date'] != '0000-00-00 00:00:00' && !empty($row['oco_real_open_date'])) {
            $dateLastSchedule = formatDate($row['data_abertura']);
            $dateRealOpen = formatDate($row['oco_real_open_date']);
        }
    }

    $qryMail = "SELECT * FROM mail_hist m, usuarios u WHERE m.mhist_technician=u.user_id AND
    m.mhist_oco=" . $COD . " ORDER BY m.mhist_date";
    $execMail = $conn->query($qryMail);
    $emails = $execMail->rowCount();


    $sqlFiles = "select * from imagens where img_oco = " . $COD . "";
    $resultFiles = $conn->query($sqlFiles);
    $hasFiles = $resultFiles->rowCount();


    /* FINAL DAS CONSULTAS REFERENTES À OCORRÊNCIA */



    $colLabel = "col-sm-3 text-md-right font-weight-bold p-2";
    $colsDefault = "small text-break border-bottom rounded p-2 bg-white"; /* border-secondary */
    $colContent = $colsDefault . " col-sm-3 col-md-3";
    $colContentLine = $colsDefault . " col-sm-9";
    $colContentLineFile = " text-break border-bottom rounded p-2 bg-white col-sm-9"; 
    ?>

    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    <!-- MENU DE OPÇÕES -->
    <nav class="navbar navbar-expand-md navbar-light  p-0 rounded" style="background-color: #48606b;">
        <!-- bg-secondary -->
        <!-- style="background-color: #dbdbdb; -->
        <div class="ml-2 font-weight-bold text-white">N.º <?= $row['numero']; ?></div> <!-- navbar-brand -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#idMenuOcorrencia" aria-controls="idMenuOcorrencia" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="idMenuOcorrencia">
            <div class="navbar-nav ml-2 mr-2">

                <?php
                if ($showItemSchedule) {
                ?>
                    <a class="nav-link small text-white" onclick="scheduleTicket('<?= $row['numero']; ?>')"><i class="fas fa-calendar-alt"></i><?= TRANS('TO_SCHEDULE'); ?></a>
                <?php
                }
                if ($showItemAttend) {
                    ?>
                    <a class="nav-link small text-white" onclick="confirmAttendModal('<?= $row['numero']; ?>')"><i class="fas fa-thumbtack"></i><?= TRANS('GET_THE_TICKET_TO_TREAT'); ?></a>
                    <?php
                }
                if ($showItemReopen) {
                    ?>
                    <a class="nav-link small text-white" onclick="confirmReopen('<?= $row['numero']; ?>')"><i class="fas fa-external-link-alt"></i><?= TRANS('REOPEN'); ?></a>
                    <?php
                }
                if ($showItemEdit) {
                    ?>
                    <a class="nav-link small text-white" href="<?= $itemEdit; ?>"><i class="fas fa-edit"></i><?= TRANS('BT_EDIT'); ?></a>
                    <?php
                }
                if ($showItemClosure) {
                ?>
                    <a class="nav-link small text-white" href="<?= $itemClosure; ?>"><i class="fas fa-check"></i><?= TRANS('CLOSE_TICKET'); ?></a>
                <?php
                }
                if ($showItemPrint) {
                ?>
                    <a class="nav-link small text-white" href="<?= $itemPrint; ?>"><i class="fas fa-print"></i><?= TRANS('FIELD_PRINT_OCCO'); ?></a>
                <?php
                }


                if ($showItemSla) {
                ?>
                    <a class="nav-link small text-white" href="#" onclick="showSlaDetails('<?= $row['numero']; ?>')"><i class="fas fa-handshake"></i><?= TRANS('COL_SLA'); ?></a>
                <?php
                }
                if ($showItemOpenSubcall) {
                ?>
                    <a class="nav-link small text-white" href="<?= $itemOpenSubcall; ?>"><i class="fas fa-stream"></i><?= TRANS('TO_OPEN_SUBTICKET'); ?></a>
                <?php
                }
                if ($showItemSendMail) {
                ?>
                    <a class="nav-link small text-white" href="<?= $itemSendMail; ?>"><i class="fas fa-envelope"></i><?= TRANS('SEND_EMAIL'); ?></a>
                <?php
                }
                if ($showItemHistory) {
                ?>
                    <a class="nav-link small text-white" href="<?= $itemHistory; ?>"><i class="fas fa-file-signature"></i><?= TRANS('MNL_CHANGES_HISTORY'); ?></a>
                <?php
                }
                ?>

            </div>
        </div>
    </nav>
    <!-- FINAL DO MENU DE OPÇÕES-->


    <div class="modal" tabindex="-1" style="z-index:9001!important" id="modalSubs">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div id="divSubDetails"></div>
            </div>
        </div>
    </div>


    <!-- Modal de confirmação de atendimento-->
    <div class="modal fade" id="modalGetTicket" tabindex="-1" style="z-index:9001!important" role="dialog" aria-labelledby="getit" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="getit"><i class="fas fa-thumbtack"></i>&nbsp;<?= TRANS('GET_THE_TICKET_TO_TREAT'); ?>&nbsp;<span id="j_param_id"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= TRANS('CONFIRM_ATTEND'); ?>?
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" id="getItButton" class="btn "><?= TRANS('BT_OK'); ?></button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de reabertura do chamado-->
    <div class="modal fade" id="modalReopenTicket" tabindex="-1" style="z-index:9001!important" role="dialog" aria-labelledby="reopenIt" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="reopenIt"><i class="fas fa-external-link-alt"></i>&nbsp;<?= TRANS('REOPEN_THE_TICKET'); ?>&nbsp;<span id="j_param_id"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= TRANS('CONFIRM_REOPEN'); ?>?
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" id="reopenItButton" class="btn "><?= TRANS('BT_OK'); ?></button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de agendamento --> <!-- style="z-index:9001!important" -->
    <div class="modal fade" id="modalSchedule" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="myModalSchedule" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header text-center bg-light">
                    
                    <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fas fa-calendar-alt"></i>&nbsp;<?= TRANS('SCHEDULE_TICKET'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- <label for="idDate_schedule" class="col-md-12 col-form-label col-form-label">Agendamento atual: <?= $dateScheduleTo; ?></label> -->
                <div class="row p-3">
                    <div class="col"><p ><?= TRANS('SCHEDULE_TICKET_HELPER'); ?>.</p></div>
                </div>
                
                <div class="row mx-2">
                    
                    <div class="form-group col-md-6">
                    <!-- <div class="col-md-6"> -->
                        <input type="text" class="form-control " id="idDate_schedule" name="date_schedule"  placeholder="<?= TRANS('DATE_TO_SCHEDULE'); ?>" value="<?= $dateScheduleTo; ?>" autocomplete="off" />
                            
                    </div>
                    
                    <!-- <div class="col-md-6"> -->
                    <div class="form-group col-md-6">
                        <input type="text" class="form-control " id="idTime_schedule" name="time_schedule" placeholder="<?= TRANS('PLACEHOLDER_SCHEDULE_TIME'); ?>" value="<?= $timeScheduleTo; ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="row mx-2">
                    <div class="col"><i class="fas fa-envelope text-secondary"></i>&nbsp;<?= TRANS('OCO_FIELD_SEND_MAIL_TO'); ?>:</div>
                </div>
                <div class="row mx-2">
                    <div class="form-group col-md-12">
						<div class="form-check form-check-inline">
							<input class="form-check-input " type="checkbox" name="mailAR" value="ok" id="idMailToArea" checked>
							<legend class="col-form-label col-form-label-sm"><?= TRANS('RESPONSIBLE_AREA'); ?></legend>
						</div>
                        
                        <?php
                            if (getOpenerLevel($conn, $row['numero']) == 3 || !empty($row['contato_email'])) { /* Se foi aberto pelo usuário final ou se tem e-mail de contato */
                                ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input " type="checkbox" name="mailUS" value="ok" id="idMailToUser">
                                    <legend class="col-form-label col-form-label-sm"><?= TRANS('CONTACT'); ?></legend>
                                </div>
                                <?php
                            }
                        ?>
						
					</div>
                </div>
                <div class="modal-footer d-flex justify-content-end bg-light">
                    <button id="confirmSchedule" class="btn "><?= TRANS('BT_SCHEDULE'); ?></button>
                    <button id="cancelSchedule" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?= TRANS('BT_CANCEL'); ?></button>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal de SLAs --> 
    <div class="modal fade" id="modalSla" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="mymodalSla" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header text-center bg-light">
                    
                    <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fas fa-handshake"></i>&nbsp;<?= TRANS('MENU_SLAS'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="row p-3">
                    <div class="col"><p ><?= TRANS('SLAS_HELPER'); ?>:</p></div>
                </div>
                
                <div class="row mx-2">
                    <div class="col-7"><?= TRANS('RESPONSE_SLA'); ?> <span class="badge badge-secondary" id="idRespostaLocal"></span></div>
                    <div id="idResposta" class="col-5"></div>
                </div>
                <div class="row mx-2 mb-4">
                    <div class="col-7"><?= TRANS('SOLUTION_SLA'); ?> <span class="badge badge-secondary" id="idSolucaoProblema"></span></div>
                    <div id="idSolucao" class="col-5"></div>
                </div>
                
                <div class="row mx-2">
                    <div class="col-7"><?= TRANS('HNT_RESPONSE_TIME'); ?> (<?= TRANS('FILTERED'); ?>)</div>
                    <div id="idResponseTime" class="col"></div>
                </div>
                <div class="row mx-2 mb-4">
                    <div class="col-7"><?= TRANS('TICKET_LIFESPAN'); ?> (<?= TRANS('FILTERED'); ?>)</div>
                    <div id="idFilterTime" class="col"></div>
                </div>

                <div class="row mx-2">
                    <div class="col-7"><?= TRANS('HNT_RESPONSE_TIME'); ?> <?= TRANS('ABSOLUTE'); ?></div>
                    <div id="idAbsResponseTime" class="col"></div>
                </div>
                <div class="row mx-2 mb-4">
                    <div class="col-7"><?= TRANS('TICKET_ABSOLUTE_LIFESPAN'); ?></div>
                    <div id="idAbsSolutionTime" class="col"></div>
                </div>
                
                <div class="row mx-2 mb-4">
                    <div class="col-7"></div>
                    <div class="col"><a href="#" onclick="showStages('<?= $row['numero']; ?>')"><i class="fab fa-stack-exchange"></i>&nbsp;<?= TRANS('STATUS_STACK'); ?></a></div>
                </div>


                <div class="modal-footer d-flex justify-content-end bg-light">
                    <button id="cancelSchedule" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?= TRANS('BT_CLOSE'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <!-- FINAL DA MODAL DE SLAS -->


    <!-- Modal de PILHA de status --> 
    <div class="modal fade" id="modalStages" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="mymodalStages" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header text-center bg-light">
                    
                    <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fab fa-stack-exchange"></i>&nbsp;<?= TRANS('STATUS_STACK'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="row p-3">
                    <div class="col"><p ><?= TRANS('STATUS_STACK_HELPER'); ?>:</p></div>
                </div>
                <div class="row header px-3 bold">
                    <div class="col-5"><?= TRANS('COL_STATUS'); ?></div>
                    <div class="col-5"><?= TRANS('DATE'); ?></div>
                    <div class="col"><?= TRANS('IS_PAUSE_STATUS'); ?></div>
                </div>
                <div class="row p-3" id="idStages">
                    <!-- Conteúdo carregado via ajax -->
                </div>
                

                <div class="modal-footer d-flex justify-content-end bg-light">
                    <button id="cancelSchedule" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?= TRANS('BT_CLOSE'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <!-- FINAL DA MODAL DE PILHA DE STATUS -->




    <div class="container bg-light">

        <?php
        /* MENSAGEM SE FOR SUBCHAMADO */
        if (!empty($msgPai)) {
        ?>
            <div class="row my-2 mb-0">
                <div class="<?= $colsDefault . " col-sm-12"; ?>">
                    <?= $msgPai; ?>
                </div>
            </div>
        <?php
        }

        /* MENSAGEM DE RETORNO PARA ABERTURA, EDIÇÃO E ENCERRAMENTO DO CHAMADO */
        if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
            echo $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
        ?>

        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('OPENED_BY'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['aberto_por']; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('DEPARTMENT'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['setor']; ?></div>
        </div>

        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('RESPONSIBLE_AREA'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['area']; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('OCO_PRIORITY'); ?></div>
            <div class="<?= $colContent; ?>"><?= $rowGet['pr_desc']; ?></div>
        </div>

        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('ISSUE_TYPE'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['problema'] . "&nbsp;" . $ShowlinkScript; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('COL_CAT_PROB'); ?></div>
            <?php
                if ($rowCatProb) {
                    ?>
                    <div class="<?= $colContent; ?>"><?= $rowCatProb['probt1_desc'] . " | " . $rowCatProb['probt2_desc'] . " | " . $rowCatProb['probt3_desc']; ?></div>
                    <?php
                } else {
                    ?>
                    <div class="<?= $colContent; ?>"></div>
                    <?php
                }
            ?>
            
        </div>

        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('DESCRIPTION'); ?></div>
            <div class="<?= $colContentLine; ?>"><?= $descricao; ?></div>
        </div>
        <div class="row my-2">

            <div class="<?= $colLabel; ?>"><?= TRANS('COL_UNIT'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['unidade']; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_TAG_EQUIP'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['etiqueta']; ?></div>
            <!-- <a onClick="showTagConfig(<?= $row['unidade_cod']; ?>, <?= $row['etiqueta']; ?>)"> -->
        </div>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('CONTACT'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['contato']; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('COL_PHONE'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['telefone']; ?></div>
        </div>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('CONTACT_EMAIL'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['contato_email']; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('OPENING_DATE'); ?></div>
            <div class="<?= $colContent; ?>"><?= $dateOpen; ?></div>
            
            <?php
            if ($isScheduled) {
                ?>
                    <!-- <div class="row my-2"> -->
                        <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_SCHEDULE_TO'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $dateScheduleTo; ?></div>
                    <!-- </div> -->
                <?php
                }
            ?>
            
        </div>
        <?php
        if ($isClosed) {
        ?>
            <div class="row my-2">
                <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_DATE_CLOSING'); ?></div>
                <div class="<?= $colContent; ?>"><?= $dateClose; ?></div>
                <div class="<?= $colLabel; ?>"><?= TRANS('COL_SCRIPT_SOLUTION'); ?></div>
                <div class="<?= $colContent; ?>"><?= $scriptSolution; ?></div>
            </div>
        <?php
        }

        
        ?>

        <!-- <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('OCO_FIELD_REAL_DATE_OPEN'); ?></div>
            <div class="<?= $colContent; ?>"><?= $dateRealOpen; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('OCO_FIELD_LAST_SCHEDULE'); ?></div>
            <div class="<?= $colContent; ?>"><?= $dateLastSchedule; ?></div>
        </div> -->

        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('COL_STATUS'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['chamado_status']; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_LAST_OPERATOR'); ?></div>
            <div class="<?= $colContent; ?>"><?= $row['nome']; ?></div>
        </div>

        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('GLOBAL_LINK'); ?></div>
            <div class="<?= $colContentLine; ?>"><?= $global_link; ?></div>

        </div>

        <!--         <div class="modal" tabindex="-1" id="modalSubs">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divSubDetails"></div>
                </div>
            </div>
        </div> -->

        <?php
        /* Usuário final - pode inserir comentário e arquivos ao chamado */
        if ($_SESSION['s_nivel'] == 3) {
            ?>
            <form name="form" id="form" method="post" enctype="multipart/form-data" action="./insert_comment.php">

                <input type="hidden" name="onlyOpen" id="onlyOpen" value="1" />
                <input type="hidden" name="numero" id="idNumero" value="<?= $COD; ?>" /> <!-- id="idUrl" -->

                <div class="row my-2">
                    <div class="col-sm-12 d-none" id="server-response"></div>
                </div>

                <div class="row my-2">
                    <div class="<?= $colLabel; ?>">
                        <?= TRANS('ATTACH_FILE'); ?>
                    </div>
                    <div class="<?= $colContentLineFile; ?>">
                        <div class="field_wrapper" id="field_wrapper">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <a href="javascript:void(0);" class="add_button" title="<?= TRANS('TO_ATTACH_ANOTHER'); ?>"><i class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="anexo[]" id="idInputFile" aria-describedby="inputGroupFileAddon01" lang="br">
                                    <label class="custom-file-label text-truncate" for="inputGroupFile01"><?= TRANS('CHOOSE_FILE'); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row my-2">
                    <div class="<?= $colLabel; ?>">

                        <button id="bt_new_comment" class="btn btn-secondary" type="button" value="<?= TRANS('INSERT_COMMENT_FILE'); ?>"><?= TRANS('INSERT_COMMENT_FILE'); ?>
                        </button>

                    </div>
                    <div class="<?= $colContentLine; ?>">
                        <div class="form-group col-md-12 p-0">
                            <textarea class="form-control form-control-sm" id="add_comment" name="add_comment" rows="4" placeholder="<?= TRANS('AT_LEAST_5_CHARS'); ?>" required></textarea>
                            <small class="form-text text-muted">
                                <?= TRANS('COMMENT_DESC'); ?>
                            </small>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="MAX_FILE_SIZE" value="<?= $config['conf_upld_size']; ?>" />

            </form>

            <?php
        }
        /* Final do trecho para inserção de arquivos e comentários */


        /* ABAS */

        $classDisabledAssent = ($assentamentos > 0 ? '' : ' disabled');
        $ariaDisabledAssent = ($assentamentos > 0 ? '' : ' true');
        $classDisabledEmails = ($emails > 0 ? '' : ' disabled');
        $ariaDisabledEmails = ($emails > 0 ? '' : ' true');
        $classDisabledFiles = ($hasFiles > 0 ? '' : ' disabled');
        $ariaDisabledFiles = ($hasFiles > 0 ? '' : ' true');
        $classDisabledSubs = ($existeSub > 0 ? '' : ' disabled');
        $ariaDisabledSubs = ($existeSub > 0 ? '' : ' true');

        ?>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"></div>
            <div class="<?= $colContentLine; ?>">
                <!-- <div class="<?= $colsDefault; ?> col-sm-12 d-flex justify-content-md-center"> -->
                <ul class="nav nav-pills " id="pills-tab" role="tablist">
                    <li class="nav-item" role="assentamentos">
                        <a class="nav-link active <?= $classDisabledAssent; ?>" id="divAssentamentos-tab" data-toggle="pill" href="#divAssentamentos" role="tab" aria-controls="divAssentamentos" aria-selected="true" aria-disabled="<?= $ariaDisabledAssent; ?>"><i class="fas fa-comment-alt"></i>&nbsp;<?= TRANS('TICKET_ENTRIES'); ?>&nbsp;<span class="badge badge-light p-1"><?= $assentamentos; ?></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledEmails; ?>" id="divEmails-tab" data-toggle="pill" href="#divEmails" role="tab" aria-controls="divEmails" aria-selected="true" aria-disabled="<?= $ariaDisabledEmails; ?>"><i class="fas fa-envelope"></i>&nbsp;<?= TRANS('EMAILS'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $emails; ?></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledFiles; ?>" id="divFiles-tab" data-toggle="pill" href="#divFiles" role="tab" aria-controls="divFiles" aria-selected="true" aria-disabled="<?= $ariaDisabledFiles; ?>"><i class="fas fa-paperclip"></i>&nbsp;<?= TRANS('FILES'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $hasFiles; ?></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledSubs; ?>" id="divSubs-tab" data-toggle="pill" href="#divSubs" role="tab" aria-controls="divSubs" aria-selected="true" aria-disabled="<?= $ariaDisabledSubs; ?>"><i class="fas fa-stream"></i>&nbsp;<?= TRANS('SUBTICKETS'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $existeSub; ?></span></a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- FINAL DAS ABAS -->



        <!-- LISTAGEM DE ASSENTAMENTOS -->

        <div class="tab-content" id="pills-tabContent">
            <?php
            if ($assentamentos) {
            ?>

                <div class="tab-pane fade show active" id="divAssentamentos" role="tabpanel" aria-labelledby="divAssentamentos-tab">

                    <div class="row my-2">

                        <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="assentamentos">
                            <!-- collapse -->
                            <table class="table  table-hover table-striped rounded">
                                <!-- table-responsive -->
                                <thead class="text-white" style="background-color: #48606b;">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col"><?= TRANS('PRIVACY'); ?></th>
                                        <th scope="col"><?= TRANS('AUTHOR'); ?></th>
                                        <th scope="col"><?= TRANS('DATE'); ?></th>
                                        <th scope="col"><?= TRANS('COL_TYPE'); ?></th>
                                        <th scope="col"><?= TRANS('TICKET_ENTRY'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($resultAssets->fetchAll() as $rowAsset) {
                                        $transAssetText = "";
                                        if ($rowAsset['asset_privated'] == 1) {
                                            $transAssetText = "<span class='badge badge-danger'>" . TRANS('CHECK_ASSET_PRIVATED') . "</span>";
                                        } else {
                                            $transAssetText = "<span class='badge badge-success'>" . TRANS('CHECK_ASSET_PUBLIC') . "</span>";
                                        }
                                    ?>
                                        <tr>
                                            <th scope="row"><?= $i; ?></th>
                                            <td><?= $transAssetText; ?></td>
                                            <td><?= $rowAsset['nome']; ?></td>
                                            <td><?= formatDate($rowAsset['data']); ?></td>
                                            <td><?= getEntryType($rowAsset['tipo_assentamento']); ?></td>
                                            <td><?= nl2br($rowAsset['assentamento']); ?></td>
                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php
            }
            /* FINAL DA LISTAGEM DE ASSENTAMENTOS */




            /* INÍCIO DO TRECHO PARA E-MAILS ENVIADOS */
            if ($emails) {
            ?>
                <div class="tab-pane fade" id="divEmails" role="tabpanel" aria-labelledby="divEmails-tab">
                    <div class="row my-2">
                        <div class="col-12" id="divError">
                        </div>
                    </div>

                    <div class="row my-2">

                        <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="emails">
                            <!-- collapse -->
                            <table class="table  table-hover table-striped rounded">
                                <!-- table-responsive -->
                                <!-- <thead class="bg-secondary text-white"> -->
                                <thead class="text-white" style="background-color: #48606b;">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col"><?= TRANS('SUBJECT'); ?></th>
                                        <th scope="col"><?= TRANS('MHIST_LISTS'); ?></th>
                                        <th scope="col"><?= TRANS('MAIL_BODY_CONTENT'); ?></th>
                                        <th scope="col"><?= TRANS('DATE'); ?></th>
                                        <th scope="col"><?= TRANS('AUTHOR'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($execMail->fetchAll() as $rowMail) {
                                        $limite = 30;
                                        $shortBody = trim($rowMail['mhist_body']);
                                        if (strlen($shortBody) > $limite) {
                                            $shortBody = substr($shortBody, 0, ($limite - 4)) . "...";
                                        }
                                    ?>
                                        <tr onClick="showEmailDetails(<?= $rowMail['mhist_cod']; ?>)" style="cursor: pointer;">
                                            <!-- <tr data-toggle="modal" data-target="#myModal"> -->
                                            <th scope="row"><?= $i; ?></th>
                                            <td><?= $rowMail['mhist_subject']; ?></td>
                                            <td><?= NVL($rowMail['mhist_listname']); ?></td>
                                            <td><?= $shortBody; ?></td>
                                            <td><?= formatDate($rowMail['mhist_date']); ?></td>
                                            <td><?= $rowMail['nome']; ?></td>
                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                    ?>

                                    <div class="modal" tabindex="-1" id="modalEmails">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modal_title"></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-12" id="para"></div>
                                                        <div class="col-12" id="copia"></div>
                                                        <div class="col-12" id="subject"></div>
                                                        <div class="col-12">
                                                            <hr>
                                                        </div>
                                                        <div class="col-12" id="mensagem"></div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('LINK_CLOSE'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php
            }
            /* FINAL DO TRECHO PARA OS EMAILS ENVIADOS */



            /* TRECHO PARA EXIBIÇÃO DA LISTAGEM DE ARQUIVOS ANEXOS */
            if ($hasFiles) {
            ?>
                <div class="tab-pane fade" id="divFiles" role="tabpanel" aria-labelledby="divFiles-tab">
                    <div class="row my-2">

                        <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="files">
                            <!-- collapse -->
                            <table class="table  table-hover table-striped rounded">
                                <!-- table-responsive -->
                                <!-- <thead class="bg-secondary text-white"> -->
                                <thead class=" text-white" style="background-color: #48606b;">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col"><?= TRANS('COL_TYPE'); ?></th>
                                        <th scope="col"><?= TRANS('SIZE'); ?></th>
                                        <th scope="col"><?= TRANS('FILE'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($resultFiles->fetchAll() as $rowFiles) {

                                        $size = round($rowFiles['img_size'] / 1024, 1);
                                        $rowFiles['img_tipo'] . "](" . $size . "k)";

                                        if (isImage($rowFiles["img_tipo"])) {
                                            /* $viewImage = "&nbsp;<a onClick=\"javascript:popupWH('../../includes/functions/showImg.php?" .
                                                "file=" . $row['numero'] . "&cod=" . $rowFiles['img_cod'] . "'," . $rowFiles['img_largura'] . "," . $rowFiles['img_altura'] . ")\" " .
                                                "title='Visualize o arquivo'><i class='fa fa-search'></i></a>"; */

                                            $viewImage = "&nbsp;<a onClick=\"javascript:popupWH('../../includes/functions/showImg.php?" .
                                                "file=" . $row['numero'] . "&cod=" . $rowFiles['img_cod'] . "'," . $rowFiles['img_largura'] . "," . $rowFiles['img_altura'] . ")\" " .
                                                "title='view'><i class='fa fa-search'></i></a>";

                                            /* $page = "../../includes/functions/showImg.php?file=" . $row['numero'] . "&cod=" . $rowFiles['img_cod'];
                                                
                                                
                                            $viewImage = "&nbsp;<a onClick=\"loadPageInModal('$page')\" title='Visualize o arquivo'><i class='fa fa-search'></i></a>"; */
                                        } else {
                                            $viewImage = "";
                                        }
                                        ?>
                                        <tr>
                                            <th scope="row"><?= $i; ?></th>
                                            <td><?= $rowFiles['img_tipo']; ?></td>
                                            <td><?= $size; ?>k</td>
                                            <td><a onClick="redirect('../../includes/functions/download.php?file=<?= $COD; ?>&cod=<?= $rowFiles['img_cod']; ?>')" title="Download the file"><?= $rowFiles['img_nome']; ?></a><?= $viewImage; ?></i></td>
                                        </tr>
                                        <?php
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php
            }

            /* FINAL DO TRECHO DE LISTAGEM DE ARQUIVOS ANEXOS*/
            ?>


            <!-- LISTAGEM DE SUBCHAMADOS -->
            <?php
            if ($existeSub) {
            ?>
                <div class="tab-pane fade" id="divSubs" role="tabpanel" aria-labelledby="divSubs-tab">
                    <div class="row my-2">

                        <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="subs">
                            <!-- collapse -->
                            <table class="table  table-hover table-striped rounded">
                                <!-- table-responsive -->
                                <!-- <thead class="bg-secondary text-white"> -->
                                <thead class=" text-white" style="background-color: #48606b;">
                                    <tr>
                                        <th scope="col"><?= TRANS('TICKET_NUMBER'); ?></th>
                                        <th scope="col"><?= TRANS('AREA'); ?></th>
                                        <th scope="col"><?= TRANS('ISSUE_TYPE'); ?></th>
                                        <th scope="col"><?= TRANS('CONTACT') . "<br />" . TRANS('COL_PHONE'); ?></th>
                                        <th scope="col"><?= TRANS('DEPARTMENT') . "<br />" . TRANS('DESCRIPTION'); ?></th>
                                        <th scope="col"><?= TRANS('FIELD_LAST_OPERATOR') . "<br />" . TRANS('COL_STATUS'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $comDeps = false;
                                    $i = 1;
                                    foreach ($execSubCall->fetchAll() as $rowSubPai) {

                                        // $comDeps = false;

                                        $sqlStatus = "select o.*, s.* from ocorrencias o, `status` s  where o.numero=" . $rowSubPai['dep_filho'] . " and o.`status`=s.stat_id and s.stat_painel not in (3) ";
                                        $execStatus = $conn->query($sqlStatus);
                                        $regStatus = $execStatus->rowCount();
                                        if ($regStatus > 0) {
                                            $comDeps = true;
                                        }
                                        if ($comDeps) {
                                            $imgSub = ICONS_PATH . "view_tree_red.png";
                                        } else {
                                            $imgSub = ICONS_PATH . "view_tree_green.png";
                                        }


                                        $qryDetail = $QRY["ocorrencias_full_ini"] . " WHERE  o.numero = " . $rowSubPai['dep_filho'] . " ";
                                        $execDetail = $conn->query($qryDetail);
                                        $rowDetail = $execDetail->fetch();

                                        $texto = trim($rowDetail['descricao']);
                                        if (strlen($texto) > 200) {
                                            $texto = substr($texto, 0, 195) . " ..... ";
                                        };

                                    ?>
                                        <tr onClick="showSubsDetails(<?= $rowDetail['numero']; ?>)" style="cursor: pointer;">
                                            <th scope="row"><?= $rowDetail['numero']; ?></th>
                                            <td><?= $rowDetail['area']; ?></td>
                                            <td><?= $rowDetail['problema']; ?></td>
                                            <td><?= $rowDetail['contato'] . "<br/>" . $rowDetail['telefone']; ?></td>
                                            <td><?= $rowDetail['setor'] . "<br/>" . $texto; ?></td>
                                            <td><?= $rowDetail['nome'] . "<br/>" . $rowDetail['chamado_status']; ?></td>

                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                    ?>
                                    <!-- <div class="modal" tabindex="-1" id="modalSubs">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div id="divSubDetails"></div>
                                            </div>
                                        </div>
                                    </div> -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            <!-- FINAL DA LISTAGEM DE SUBCHAMADOS -->



        </div> <!-- tab-content -->






    </div>




    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/jquery/jquery.initialize.min.js"></script>
    <script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>
    <script type="text/javascript" src="../../includes/components/jquery/timePicker/jquery.timepicker.min.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
    <script>
        $(function() {

            //APENAS PARA USUÁRIOS DE ABERTURA
            if ($('#onlyOpen').val() == 1) {

                /* Permitir a replicação do campo de input file */
                var maxField = <?= $config['conf_qtd_max_anexos']; ?>;
                var addButton = $('.add_button'); //Add button selector
                var wrapper = $('.field_wrapper'); //Input field wrapper

                var fieldHTML = '<div class="input-group my-1 d-block"><div class="input-group-prepend"><div class="input-group-text"><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus"></i></a></div><div class="custom-file"><input type="file" class="custom-file-input" name="anexo[]"  aria-describedby="inputGroupFileAddon01" lang="br"><label class="custom-file-label text-truncate" for="inputGroupFile01"><?= TRANS('CHOOSE_FILE', '', 1); ?></label></div></div></div></div>';

                var x = 1; //Initial field counter is 1

                //Once add button is clicked
                $(addButton).click(function() {
                    //Check maximum number of input fields
                    if (x < maxField) {
                        x++; //Increment field counter
                        $(wrapper).append(fieldHTML); //Add field html
                    }
                });

                //Once remove button is clicked
                $(wrapper).on('click', '.remove_button', function(e) {
                    e.preventDefault();
                    $(this).parent('div').parent('div').parent('div').remove(); //Remove field html
                    x--; //Decrement field counter
                });


                $('#bt_new_comment').on('click', function() {
                    $('#add_comment').focus();
                });

                $('#add_comment').on('keyup', function() {
                    if ($.trim($(this).val()).length > 4) {

                        $('#bt_new_comment').removeClass('btn-secondary').addClass('btn-primary').text('<?= TRANS('BT_OK'); ?>').prop('id', 'bt_submit');

                    } else {
                        if ($('#bt_submit').length) {
                            $('#bt_submit').removeClass('btn-primary').addClass('btn-secondary').text('<?= TRANS('INSERT_COMMENT_FILE'); ?>').prop('id', 'bt_new_comment');
                        }
                    }
                });
                new_submit();
            }


            if ($('#idInputFile').length > 0) {
			/* Adicionei o mutation observer em função dos elementos que são adicionados após o carregamento do DOM */
			var obs = $.initialize(".custom-file-input", function() {
				$('.custom-file-input').on('change', function() {
					let fileName = $(this).val().split('\\').pop();
					$(this).next('.custom-file-label').addClass("selected").html(fileName);
				});

			}, {
				target: document.getElementById('field_wrapper')
			}); /* o target limita o scopo do observer */
		}


            $( "#idDate_schedule" ).datepicker({
			dateFormat: 'dd/mm/yy',
			changeMonth: true,
			dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
			dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
			dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
			monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro','Janeiro'],
			monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez','Jan'],
			minDate: '+1d'
		});

		$('#idTime_schedule').timepicker({
			timeFormat: 'H:mm',
			interval: 30,
			minTime: '08',
			maxTime: '11:30pm',
			// defaultTime: '11',
			startTime: '8:00',
			dynamic: false,
			dropdown: true,
			scrollbar: false
		});

            /* if ($('#divMsgSubCall').length > 0) {
                $(this).on('click', showSubsDetails(
                    $('#father').val()
                ));
            } */

        });


        function confirmAttendModal(id) {
            $('#modalGetTicket').modal();
            $('#j_param_id').html(id);
            $('#getItButton').html('<a class="btn btn-primary" onclick="getTicket(' + id + ')"><?= TRANS('GET_THE_TICKET_TO_TREAT'); ?></a>');
        }

        function getTicket(numero) {
            $.ajax({
                url: 'get_ticket_in.php',
                method: 'POST',
                data: {
                    'numero': numero
                },
            }).done(function() {
                $('#modalGetTicket').modal('hide');
                location.reload();
            });
            return false;
        }

        function confirmReopen(id) {
            $('#modalReopenTicket').modal();
            $('#j_param_id').html(id);
            $('#reopenItButton').html('<a class="btn btn-primary" onclick="reopenTicket(' + id + ')"><?= TRANS('REOPEN'); ?></a>');
        }

        function reopenTicket(numero) {
            $.ajax({
                url: 'reopen_process.php',
                method: 'POST',
                data: {
                    'numero': numero
                },
            }).done(function() {
                $('#modalReopenTicket').modal('hide');
                location.reload();
            });
            return false;
        }
        

        function scheduleTicket(id) {
            $('#modalSchedule').modal();
            $('#j_param_id').html(id);
            
            $('#confirmSchedule').html('<a class="btn btn-primary" onclick="getScheduleData(' + id + ')"><?= TRANS('TO_SCHEDULE'); ?></a>');
        }

        function getScheduleData(numero) {

            var sendEmailToArea = ($('#idMailToArea').is(':checked') ? true : false);
            
            if ($('#idMailToUser').length > 0) {
                var sendEmailToUser = ($('#idMailToUser').is(':checked') ? true : false);
            } else {
                var sendEmailToUser = false;
            }

            $(".loading").show();

            $.ajax({
                url: 'schedule_ticket.php',
                method: 'POST',
                data: {
                    'numero': numero,
                    'scheduleDate': $('#idDate_schedule').val(),
                    'scheduleTime': $('#idTime_schedule').val(),
                    'sendEmailToArea': sendEmailToArea,
                    'sendEmailToUser': sendEmailToUser
                },
            }).done(function() {
                $('#modalSchedule').modal('hide');
                location.reload();
            });
            return false;
        }

        function new_submit() {
            var obs = $.initialize("#bt_submit", function() {

                $('#bt_submit').on('click', function(e) {
                    e.preventDefault();
                    if (!$('#add_comment').val()) {
                        $('#add_comment').focus();
                    } else {
                        $('#idLoad').show();

                        var form = $('form').get(0);
                        $("#bt_submit").prop("disabled", true);

                        $.ajax({
                            url: './insert_comment.php',
                            method: 'POST',
                            // data: $('#form').serialize(),
                            data: new FormData(form),
                            // dataType: 'json',

                            cache: false,
                            processData: false,
                            contentType: false,
                        }).done(function(response) {
                            // $("#server-response").html(response);
                            location.reload();
                        });
                    }
                });
            }); /* , { target: document.getElementById('sidebar') } */
        }

        function showEmailDetails(cod) {
            $.ajax({
                url: 'showEmailDetails.php',
                method: 'POST',
                data: {
                    'cod': cod
                },
                dataType: 'json',

            }).done(function(data) {

                $('#modal_title').html('<b><?= TRANS('SENT_DATE'); ?>:</b> ' + dateToBR(data.mhist_date));
                $('#para').html('<b><?= TRANS('MAIL_FIELD_TO'); ?>:</b> ' + data.mhist_address);
                $('#copia').html('<b><?= TRANS('MAIL_FIELD_CC'); ?>:</b> ' + data.mhist_address_cc);
                $('#subject').html('<b><?= TRANS('SUBJECT'); ?>:</b> ' + data.mhist_subject);

                var bodyMessage = data.mhist_body;
                bodyMessage = bodyMessage.replace(new RegExp('\r?\n','g'), '<br />');

                $('#mensagem').html('<b><?= TRANS('MAIL_BODY_CONTENT'); ?>:</b><br/>' + bodyMessage);
                $('#modalEmails').modal();
            }).fail(function() {
                $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
            });
            return false;
        }


        function showSlaDetails(cod) {
            $.ajax({
                url: 'getTicketSlaInfo.php',
                method: 'POST',
                data: {
                    'numero': cod
                },
                dataType: 'json',

            }).done(function(data) {
                
                $('#idResposta').html(data.sla_resposta + '&nbsp;<span class="badge badge-secondary">' + data.sla_resposta_in_hours + '</span>');
                $('#idRespostaLocal').html(data.setor);
                $('#idSolucao').html(data.sla_solucao + '&nbsp;<span class="badge badge-secondary">' + data.sla_solucao_in_hours + '</span>');
                $('#idSolucaoProblema').html(data.problema);
                $('#idResponseTime').html(data.response_time);
                $('#idFilterTime').html(data.filter_time);
                $('#idAbsResponseTime').html(data.abs_response_time);
                $('#idAbsSolutionTime').html(data.abs_solution_time);
                // $('#idLeds').html(data.slas_leds);
                $('#modalSla').modal();
            }).fail(function() {
                $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
            });
            return false;
        }

        function showStages(cod) {
            $.ajax({
                url: 'getTicketStages.php',
                method: 'POST',
                data: {
                    'numero': cod
                },
                dataType: 'json',

            }).done(function(data) {
                
                $('.classDynRow').remove();
                for (var i in data) {
                    //data[i].status | data[i].date_start | data[i].date_stop | data[i].freeze

                    var fieldHTML = '<div class="col-5 classDynRow">' + data[i].status + '</div><div class="col-5 classDynRow">' + data[i].date_start + '</div><div class="col classDynRow">' + data[i].freeze + '</div><div class="w-100 classDynRow"></div>';
                    $(idStages).append(fieldHTML);
                }
                $('#modalStages').modal();
            }).fail(function() {
                $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
            });
            return false;
        }


        function showSubsDetails(cod) {
            $("#divSubDetails").load('<?= $_SERVER['PHP_SELF']; ?>?numero=' + cod);
            $('#modalSubs').modal();
        }

        function loadPageInModal(page) {
            $("#divSubDetails").load(page);
            $('#modalSubs').modal();
        }

        function showTagConfig(unit, tag) {

            if (unit != '' && tag != '') {
                $("#divSubDetails").load('../../invmon/geral/equipment_show.php?comp_inst=' + unit + '&comp_inv=' + tag);
                $('#modalSubs').modal();
            }
            return false;
        }


        function dateToBR(date) {
            var date = new Date(date);

            var year = date.getFullYear().toString();
            var month = (date.getMonth() + 101).toString().substring(1);
            var day = (date.getDate() + 100).toString().substring(1);
            var hour = ('0' + date.getHours()).slice(-2);
            var minute = ('0' + date.getMinutes()).slice(-2);
            var second = ('0' + date.getSeconds()).slice(-2);
            return day + '/' + month + '/' + year + ' ' + hour + ':' + minute + ':' + second;
        }



        function popup_alerta(pagina) { //Exibe uma janela popUP
            x = window.open(pagina, '_blank', 'dependent=yes,width=700,height=470,scrollbars=yes,statusbar=no,resizable=yes');
            x.moveTo(window.parent.screenX + 50, window.parent.screenY + 50);
            return false
        }

        function popup_alerta_mini(pagina) { //Exibe uma janela popUP
            x = window.open(pagina, '_blank', 'dependent=yes,width=400,height=250,scrollbars=yes,statusbar=no,resizable=yes');
            x.moveTo(100, 100);
            x.moveTo(window.parent.screenX + 50, window.parent.screenY + 50);
            return false
        }

        function popup(pagina) { //Exibe uma janela popUP
            x = window.open(pagina, 'popup', 'dependent=yes,width=400,height=200,scrollbars=yes,statusbar=no,resizable=yes');
            x.moveTo(window.parent.screenX + 100, window.parent.screenY + 100);
            return false
        }
    </script>
</body>

</html>