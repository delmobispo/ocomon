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

$imgsPath = "../../includes/imgs/";

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

/* Para manter a compatibilidade com versões antigas */
$table = "areaxarea_abrechamado";
$sqlTest = "SELECT * FROM {$table}";
try {
    $conn->query($sqlTest);
}
catch (Exception $e) {
    $table = "areaXarea_abrechamado";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OcoMon&nbsp;<?= VERSAO; ?></title>

    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />

    <style>
        .oc-cursor {
            cursor: pointer;
        }

    </style>


</head>
<?php

print "<body onLoad=\"
        ajaxFunction('divSelProblema', 'showSelProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea', 'area_habilitada=idAreaHabilitada'); 
        ajaxFunction('divProblema', 'showProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea'); 
        
        ajaxFunction('divInformacaoProblema', 'showInformacaoProb.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea'); 
    \">";
/* checarSchedule('');  */

$auth->showHeader();

$hoje = date("Y-m-d H:i:s");
$hoje2 = date("d/m/Y");

$qry_config = "SELECT * FROM config ";
$exec_config = $conn->query($qry_config);
$row_config = $exec_config->fetch(PDO::FETCH_ASSOC);


if (!isset($_POST['submit'])) {


    if (!isset($_GET['numero'])) {
        exit();
    }
    $numero = (int)$_GET['numero'];

    $query = "select o.*, u.* from ocorrencias as o, usuarios as u where o.operador = u.user_id and numero=" . $numero . "";

    $resultado = $conn->query($query);
    $row = $resultado->fetch();
    $linhas = $resultado->rowCount();

    $data_atend = $row['data_atendimento']; //Data de atendimento!!!

    $query2 = "select a.*, u.* from assentamentos a, usuarios u where a.responsavel=u.user_id and ocorrencia=" . $numero . "";
    $resultado2 = $conn->query($query2);
    $linhas2 = $resultado2->rowCount();


    /* ASSENTAMENTOS */
    if ($_SESSION['s_nivel'] < 3) {
        $query2 = "select a.*, u.* from assentamentos a, usuarios u where a.responsavel=u.user_id and a.ocorrencia=" . $numero . "";
    } else
        $query2 = "select a.*, u.* from assentamentos a, usuarios u where a.responsavel=u.user_id and a.ocorrencia=" . $numero . " and a.asset_privated = 0";

    $resultAssets = $conn->query($query2);
    $assentamentos = $resultAssets->rowCount();

    /* ARQUIVOS */
    $sqlFiles = "select * from imagens where img_oco = " . $numero . "";
    $resultFiles = $conn->query($sqlFiles);
    $hasFiles = $resultFiles->rowCount();


    /* CHECAGEM PARA VER SE É CHAMADO PAI */
    // $qrySubCall = "select * from ocodeps where dep_pai = " . $numero . "";
    $qrySubCall = "select * from ocodeps where dep_pai = " . $numero . " OR dep_filho = " . $numero . "";
    $execSubCall = $conn->query($qrySubCall);
    $existeSub = $execSubCall->rowCount();



    $closed = false;
    $scheduled = false;
    $hadFirstResponse = false;
    if ($row['status'] == 4) {
        $closed = true;
    }

    if ($row['oco_scheduled'] == 1) {
        $scheduled = true;
    }

    if (!empty($row['data_atendimento'])) {
        $hadFirstResponse = true;
    }


?>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
        <div id="loading" class="loading" style="display:none"></div>
    </div>

    <!-- Mensagens de retorno -->
    <div id="divResult"></div>

    <div class="container-fluid">


        <div class="modal" tabindex="-1" id="modalDefault">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divModalDetails" class="p-3"></div>
                </div>
            </div>
        </div>


        <h5 class="my-2"><i class="fas fa-edit text-secondary"></i>&nbsp;<?= TRANS('TICKET_EDIT_TITLE') . "&nbsp;<span class='badge badge-secondary pt-2'>" . $numero . "</span>"; ?></h5>

        <form name="form" id="form" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" > <!-- onSubmit="return valida();" -->

            <?= csrf_input(); ?>

            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $row_config['conf_upld_size']; ?>" />


            <div class="form-group row my-4">
                <label for="idArea" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RESPONSIBLE_AREA'); ?></label>
                <div class="form-group col-md-4">
                    <select class="form-control " id="idArea" name="sistema" required onChange="ajaxFunction('divSelProblema', 'showSelProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea', 'area_habilitada=idAreaHabilitada'); ajaxFunction('divProblema', 'showProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea');">
                        <!-- ajaxFunction('divInformacaoProblema', 'showInformacaoProb.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea'); ajaxFunction('divOperator', 'showOperators.php', 'idLoad', 'area_cod=idArea'); -->

                        <option value="-1"><?= TRANS('SEL_AREA'); ?></option>
                        <?php
                        $query = "SELECT s.* from sistemas s, {$table} a WHERE s.sis_status NOT IN (0) " .
                            "AND s.sis_atende = 1 AND s.sis_id = a.area AND a.area_abrechamado IN (" . $_SESSION['s_uareas'] . ") " .
                            "GROUP BY sistema ORDER BY sistema";
                        $exec_sis = $conn->query($query);
                        foreach ($exec_sis->fetchAll() as $row_sis) {
                            print "<option value='" . $row_sis['sis_id'] . "'";
                            if ($row_sis['sis_id'] == $row['sistema']) {
                                print " selected";
                            }
                            print " >" . $row_sis['sistema'] . " </option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="areaHabilitada" id="idAreaHabilitada" value="sim">
                </div>
                <label for="idProblema" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ISSUE_TYPE'); ?></label>
                <div class="form-group col-md-4">
                    <div id="divSelProblema">
                        <input type="hidden" name="problema" id="idProblema" value="<?= $row['problema']; ?>">
                    </div>
                </div>
                <div class=" col-md-12 ">
                    <div id="divProblema">
                        <input type="hidden" name="radio_prob" id="idRadioProb" value="-1" />
                    </div>
                </div>
                <div class=" col-md-12">
                    <div id="divInformacaoProblema"></div>
                </div>



                <label for="idDescricao" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DESCRIPTION'); ?></label>
                <div class="form-group col-md-10">
                    <textarea class="form-control " id="idDescricao" name="descricao" rows="4" disabled><?= trim(noHtml($row['descricao'])); ?></textarea>
                </div>



                <label for="idUnidade" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                <div class="form-group col-md-4">
                    <select class="form-control " id="idUnidade" name="instituicao">
                        <option value="-1"><?= TRANS('OCO_SEL_UNIT'); ?></option>
                        <?php

                        $sqlUnidades = "SELECT * FROM instituicao ORDER BY inst_nome";
                        $resultUnidades = $conn->query($sqlUnidades);
                        foreach ($resultUnidades->fetchAll() as $rowUnidade) {
                        ?>
                            <option value="<?= $rowUnidade['inst_cod']; ?>" <?= ($rowUnidade['inst_cod'] == $row['instituicao'] ?  " selected" : ""); ?>><?= $rowUnidade['inst_nome']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>

                <label for="idEtiqueta" class="col-md-2 col-form-label col-form-label-sm text-md-right text-nowrap"><?= TRANS('FIELD_TAG_EQUIP'); ?></label>


                <div class="form-group col-md-4">
                    <div class="input-group">

                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <a href="javascript:void(0);" data-pop="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('BT_GET_TAG_INFO_HELPER'); ?>." onClick="checa_etiqueta()"><i class="fa fa-sliders-h"></i></a>
                            </div>
                        </div>
                        <input type="text" class="form-control " id="idEtiqueta" name="equipamento" value="<?= $row['equipamento']; ?>" placeholder="<?= TRANS('FIELD_TAG_EQUIP'); ?>" />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <a href="javascript:void(0);" data-pop="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('BT_GET_TICKETS_FROM_TAG_HELPER'); ?>." onClick="checa_chamados()"><i class="fa fa-history"></i></a>
                            </div>
                        </div>

                    </div>
                </div>
                <label for="idContato" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CONTACT') ?></label>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control " id="idContato" name="contato" list="contatos" autocomplete="off" required value="<?= $row['contato']; ?>" placeholder="<?= TRANS('CONTACT') ?>" />
                </div>
                <datalist id="contatos"></datalist>

                <label for="contato_email" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CONTACT_EMAIL') ?></label>
                <div class="form-group col-md-4">
                    <input type="email" class="form-control " id="contato_email" name="contato_email" list="contatos_emails" required value="<?= $row['contato_email']; ?>" autocomplete="off" placeholder="<?= TRANS('CONTACT_EMAIL_PLACEHOLDER') ?>" />
                </div>
                <datalist id="contatos_emails"></datalist>


                <label for="idTelefone" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
                <div class="form-group col-md-4">
                    <input type="tel" class="form-control " id="idTelefone" name="telefone" required value="<?= $row['telefone']; ?>" placeholder="<?= TRANS('COL_PHONE'); ?>" />
                </div>
                <label for="departamento" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>

                <div class="form-group col-md-4">
                    <div id="idDivSelLocal">
                        <select class="form-control " name="local" required id="idLocal">
                            <option value=""><?= TRANS('SEL_SECTOR'); ?></option>
                            <?php
                            $query = "SELECT * from localizacao order by local";
                            $exec_loc = $conn->query($query);
                            foreach ($exec_loc->fetchAll() as $row_loc) {
                                print "<option value=" . $row_loc['loc_id'] . "";
                                if ($row_loc['loc_id'] == $row['local']) {
                                    print " selected";
                                }
                                print " >" . $row_loc['local'] . " </option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <label for="operador" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TECHNICIAN'); ?></label>
                <div class="form-group col-md-4">
                    <select class="form-control  " id="operador" name="operador">
                        <?php
                        $query = "SELECT u.*, a.* from usuarios u, sistemas a where u.AREA = a.sis_id and a.sis_atende='1' and u.nivel not in (3,4,5) order by login";
                        $exec_oper = $conn->query($query);
                        foreach ($exec_oper->fetchAll() as $row_oper) {
                            print "<option value=" . $row_oper['user_id'] . " ";
                            if ($row_oper['user_id'] == $_SESSION['s_uid']) {
                                print " selected";
                            }
                            print ">" . $row_oper['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </div>


                <!-- <label for="idDate_schedule" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TO_SCHEDULE'); ?></label>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control " id="idDate_schedule" name="date_schedule" value="" disabled />
                </div> -->


                <label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ATTACH_FILE'); ?></label>

                <div class="form-group col-md-4">
                    <div class="field_wrapper" id="field_wrapper">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <a href="javascript:void(0);" class="add_button" title="<?= TRANS('TO_ATTACH_ANOTHER'); ?>"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                            <!-- <input type="file" class="form-control  " name="anexo[]" /> -->
                            <div class="custom-file">
                                <input type="file" class="custom-file-input custom-file-input-sm" id="idInputFile" name="anexo[]" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01" lang="br">
                                <label class="custom-file-label text-truncate" for="inputGroupFile01"><?= TRANS('CHOOSE_FILE'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>



                <label for="idPrioridade" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_PRIORITY'); ?></label>
                <div class="form-group col-md-4">
                    <select class="form-control " id="idPrioridade" name="prioridade">
                        <?php
                        print "<option value='-1'>" . TRANS('OCO_PRIORITY') . "</option>";
                        $sql2 = "select * from prior_atend order by pr_nivel";
                        $commit2 = $conn->query($sql2);
                        foreach ($commit2->fetchAll() as $rowB) {
                            print "<option value=" . $rowB["pr_cod"] . "";
                            if ($rowB['pr_cod'] == $row['oco_prior']) {
                                print " selected";
                            }
                            print ">" . $rowB["pr_desc"] . "</option>";
                        }
                        ?>
                    </select>
                </div>


                <?php
                $qrymail = "SELECT u.*, a.*,o.* from usuarios u, sistemas a, ocorrencias o where " .
                    "u.AREA = a.sis_id and o.aberto_por = u.user_id and o.numero = " . $numero . "";
                $execmail = $conn->query($qrymail);
                $rowmail = $execmail->fetch();
                if ($rowmail['sis_atende'] == 0) {
                    $habilita = "";
                } else {
                    $habilita = "disabled";
                }

                if ($row['contato_email'] && !empty($row['contato_email'])) {
                    $habilita = "";
                }


                ?>
                <label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_FIELD_SEND_MAIL_TO'); ?></label>
                <div class="form-group col-md-4">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input " type="checkbox" name="mailAR" value="ok" id="defaultCheck1" checked>
                        <legend class="col-form-label col-form-label-sm"><?= TRANS('RESPONSIBLE_AREA'); ?></legend>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input " type="checkbox" name="mailOP" value="ok" id="defaultCheck2">
                        <legend class="col-form-label col-form-label-sm"><?= TRANS('TECHNICIAN'); ?></legend>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input " type="checkbox" name="mailUS" value="ok" <?= $habilita; ?> id="mailUS">
                        <legend class="col-form-label col-form-label-sm"><?= TRANS('CONTACT'); ?></legend>
                    </div>
                </div>

                <?php


                if ($closed) {
                    $oldStatus = 4;
                    //TRANS('FIELD_DATE_CLOSING')
                    $data_fechamento = formatDate($row['data_fechamento']);
                    ?>
                    <label for="data_fechamento" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_DATE_CLOSING'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control  " readonly id="data_fechamento" name="data_fechamento" value="<?= $data_fechamento; ?>" />
                    </div>
                    <?php
                }



                $os_DataAbertura = dateScreen($row['data_abertura']);
                ?>
                <label for="data_abertura" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPENING_DATE'); ?></label>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control  " readonly id="data_abertura" name="data_abertura" value="<?= $os_DataAbertura; ?>" />
                </div>

                <div class="w-100"></div>

                <label for="idAssentamento" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TICKET_ENTRY'); ?><br /><span><input type="checkbox" name="check_asset_privated" value="1">&nbsp;<?= TRANS('CHECK_ASSET_PRIVATED'); ?></span></label>
                <div class="form-group col-md-10">
                    <textarea class="form-control " id="idAssentamento" name="assentamento" required rows="4" placeholder="<?= TRANS('PLACEHOLDER_ASSENT'); ?>"></textarea>
                    <small class="form-text text-muted">
                        <?= TRANS('ENTRY_HELPER'); ?>.
                    </small>
                </div>

                <?php
                if (!$hadFirstResponse) {
                ?>
                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIRST_RESPONSE'); ?></label>
                    <div class="form-group col-md-10">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input " type="checkbox" name="resposta" value="ok" id="idResposta" checked>
                            <legend class="col-form-label col-form-label-sm"><?= TRANS('HNT_NOT_MARK_OPT_FIRST_REPLY_CALL'); ?></legend>
                        </div>
                    </div>
                <?php
                }

                $able = "";
                if ($closed) {
                    $able = " disabled";
                }
                ?>

                <label for="idStatus" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_STATUS'); ?></label>
                <div class="form-group col-md-4">
                    <select class="form-control  " id="idStatus" name="status" <?= $able; ?>>
                        <!-- <option value="-1"><?= TRANS('SEL_STATUS'); ?></option> -->
                        <?php
                        if ($row['status'] == 4) {
                            $stat_flag = "";
                        } else {
                            $stat_flag = " where stat_id <> 4 ";
                        }
                        $query_stat = "SELECT * from status {$stat_flag} order by status";
                        $exec_stat = $conn->query($query_stat);
                        foreach ($exec_stat->fetchAll() as $row_stat) {
                            print "<option value=" . $row_stat['stat_id'] . "";
                            if ($row_stat['stat_id'] == $row['status']) {
                                print " selected";
                            }
                            print " >" . $row_stat['status'] . " </option>";
                        }
                        ?>
                    </select>
                </div>





                <?php
                /* $colLabel = "col-sm-2 text-md-right font-weight-bold p-2";
                $colsDefault = "small text-break border-bottom rounded p-2 bg-white"; */ /* border-secondary */
                $colLabel = "col-sm-2 text-md-right font-weight-bold p-2";
                $colsDefault = " text-break p-2 bg-white"; /* border-secondary */
                $colContent = $colsDefault . " col-sm-3 col-md-3";
                $colContentLine = $colsDefault . " col-sm-9";

                /* ABAS */

                $classDisabledAssent = ($assentamentos > 0 ? '' : ' disabled');
                $ariaDisabledAssent = ($assentamentos > 0 ? '' : ' true');
                $classDisabledFiles = ($hasFiles > 0 ? '' : ' disabled');
                $ariaDisabledFiles = ($hasFiles > 0 ? '' : ' true');
                $classDisabledSubs = ($existeSub > 0 ? '' : ' disabled');
                $ariaDisabledSubs = ($existeSub > 0 ? '' : ' true');

                ?>
                <div class="row my-2 w-100">
                    <div class="<?= $colLabel; ?> my-auto"><span class="badge badge-danger oc-cursor " data-toggle="collapse" data-target="#divListagens" data-pop="popover" data-placement="top" data-content="<?= TRANS('SHOW_HIDE_LISTS'); ?>" data-trigger="hover" id="oc_plus_minus"><i class="fas fa-minus"></i></span><!-- <button class="btn btn-oc-wine" type="button" data-toggle="collapse" data-target="#divListagens">Teste de Collapse</button> -->
                    </div>
                    <div class="<?= $colContentLine; ?>">
                        <ul class="nav nav-pills " id="pills-tab" role="tablist">
                            <li class="nav-item" role="assentamentos">
                                <a class="nav-link active <?= $classDisabledAssent; ?>" id="divAssentamentos-tab" data-toggle="pill" href="#divAssentamentos" role="tab" aria-controls="divAssentamentos" aria-selected="true" aria-disabled="<?= $ariaDisabledAssent; ?>"><i class="fas fa-comment-alt"></i>&nbsp;<?= TRANS('TICKET_ENTRIES'); ?>&nbsp;<span class="badge badge-light"><?= $assentamentos; ?></span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $classDisabledFiles; ?>" id="divFiles-tab" data-toggle="pill" href="#divFiles" role="tab" aria-controls="divFiles" aria-selected="true" aria-disabled="<?= $ariaDisabledFiles; ?>"><i class="fas fa-paperclip"></i>&nbsp;<?= TRANS('FILES'); ?>&nbsp;<span class="badge badge-light"><?= $hasFiles; ?></span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $classDisabledSubs; ?>" id="divSubs-tab" data-toggle="pill" href="#divSubs" role="tab" aria-controls="divSubs" aria-selected="true" aria-disabled="<?= $ariaDisabledSubs; ?>"><i class="fas fa-stream"></i>&nbsp;<?= TRANS('TICKETS_REFERENCED'); ?>&nbsp;<span class="badge badge-light"><?= $existeSub; ?></span></a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- FINAL DAS ABAS -->



                <div class="container collapse show" id="divListagens">
                    <div class="tab-content" id="pills-tabContent">
                        <?php
                        /* LISTAGEM DE ASSENTAMENTOS */
                        $printCont = 0;
                        if ($assentamentos) {
                        ?>
                            <div class="tab-pane fade show active" id="divAssentamentos" role="tabpanel" aria-labelledby="divAssentamentos-tab">
                                <div class="row ">

                                    <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="assentamentos">
                                        <!-- collapse -->
                                        <table class="table  table-hover table-striped rounded">
                                            <!-- table-responsive -->
                                            <thead class="text-white" style="background-color: #48606b;">
                                                <tr>
                                                    <th scope="col"><?= TRANS('CHECK_ASSET_PRIVATED'); ?></th>
                                                    <th scope="col"><?= TRANS('AUTHOR'); ?></th>
                                                    <th scope="col"><?= TRANS('DATE'); ?></th>
                                                    <th scope="col"><?= TRANS('COL_TYPE'); ?></th>
                                                    <th scope="col"><?= TRANS('TICKET_ENTRY'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // $printCont = 0;
                                                $i = 1;
                                                foreach ($resultAssets->fetchAll() as $rowAsset) {
                                                    $printCont++;
                                                    $transAssetText = "";
                                                    $checked = "";
                                                    if ($rowAsset['asset_privated'] == 1) {
                                                        $transAssetText = TRANS('CHECK_ASSET_PRIVATED');
                                                        $checked = " checked";
                                                    } else $transAssetText = "";
                                                ?>
                                                    <tr>
                                                        <!-- <th scope="row"><?= $i; ?></th> -->
                                                        <th><input type="checkbox" name="asset<?= $printCont; ?>" value="<?= $rowAsset['numero']; ?>" <?= $checked; ?>></th>
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


                        /* TRECHO PARA EXIBIÇÃO DA LISTAGEM DE ARQUIVOS ANEXOS */
                        $cont = 0;
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
                                                    <th scope="col"><?= TRANS('REMOVE'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 1;
                                                // $cont = 0;
                                                foreach ($resultFiles->fetchAll() as $rowFiles) {
                                                    $cont++;
                                                    $size = round($rowFiles['img_size'] / 1024, 1);
                                                    $rowFiles['img_tipo'] . "](" . $size . "k)";

                                                    if (isImage($rowFiles["img_tipo"])) {

                                                        $viewImage = "&nbsp;<a onClick=\"javascript:popupWH('../../includes/functions/showImg.php?" .
                                                            "file=" . $row['numero'] . "&cod=" . $rowFiles['img_cod'] . "'," . $rowFiles['img_largura'] . "," . $rowFiles['img_altura'] . ")\" " .
                                                            "title='" . TRANS('VIEW') . "'><i class='fa fa-search'></i></a>";
                                                    } else {
                                                        $viewImage = "";
                                                    }
                                                ?>
                                                    <tr>
                                                        <th scope="row"><?= $i; ?></th>
                                                        <td><?= $rowFiles['img_tipo']; ?></td>
                                                        <td><?= $size; ?>k</td>
                                                        <td><a onClick="redirect('../../includes/functions/download.php?file=<?= $numero; ?>&cod=<?= $rowFiles['img_cod']; ?>')" title="Download the file"><?= $rowFiles['img_nome']; ?></a><?= $viewImage; ?></i></td>
                                                        <td><input type="checkbox" name="delImg[<?= $cont; ?>]" value="<?= $rowFiles['img_cod']; ?>">&nbsp;<span class="align-top"><i class="fas fa-trash-alt text-danger"></i></span></td>

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

                        // LISTAGEM DE CHAMADOS VINCULADOS (PAI OU FILHOS)
                        $contSub = 0;
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
                                                    <th scope="col"><?= TRANS('REMOVE_RELATIONSHIP'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $comDeps = false;
                                                $i = 1;
                                                $key = "";
                                                $label = "";
                                                // $contSub = 0;
                                                foreach ($execSubCall->fetchAll() as $rowSubPai) {
                                                    $contSub++;
                                                    $key = $rowSubPai['dep_filho'];
                                                    $label = "<span class='badge badge-oc-wine'>filho</span>";
                                                    // $comDeps = false;
                                                    if ($rowSubPai['dep_pai'] != $numero) {
                                                        $key = $rowSubPai['dep_pai'];
                                                        $label = "<span class='badge badge-oc-teal'>pai</span>";
                                                    }

                                                    $sqlStatus = "select o.*, s.* from ocorrencias o, `status` s  where o.numero=" . $key . " and o.`status`=s.stat_id and s.stat_painel not in (3) ";
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

                                                    $qryDetail = $QRY["ocorrencias_full_ini"] . " WHERE  o.numero = " . $key . " ";
                                                    $execDetail = $conn->query($qryDetail);
                                                    $rowDetail = $execDetail->fetch();

                                                    $texto = trim($rowDetail['descricao']);
                                                    if (strlen($texto) > 200) {
                                                        $texto = substr($texto, 0, 195) . " ..... ";
                                                    };

                                                ?>
                                                    <!-- <tr onClick="showSubsDetails(<?= $rowDetail['numero']; ?>)" style="cursor: pointer;"> -->
                                                    <tr>
                                                        <th scope="row"><a href="ticket_show.php?numero=<?= $rowDetail['numero']; ?>"><?= $rowDetail['numero']; ?></a>&nbsp;<?= $label; ?></th>
                                                        <td><?= $rowDetail['area']; ?></td>
                                                        <td><?= $rowDetail['problema']; ?></td>
                                                        <td><?= $rowDetail['contato'] . "<br/>" . $rowDetail['telefone']; ?></td>
                                                        <td><?= $rowDetail['setor'] . "<br/>" . $texto; ?></td>
                                                        <td><?= $rowDetail['nome'] . "<br/>" . $rowDetail['chamado_status']; ?></td>
                                                        <td><input type="checkbox" name="delSub[<?= $contSub; ?>]" value="<?= $key ?>" />&nbsp;<span class="align-top"><i class="fas fa-trash-alt text-danger"></i></span></td>
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
                        ?>
                        <!-- FINAL DA LISTAGEM DE CHAMADOS VINCULADOS -->

                    </div>
                </div>


                <input type='hidden' name='data_gravada' value='<?= date("Y-m-d H:i:s"); ?>' />
                <input type='hidden' name='numero' value='<?= $numero; ?>' />
                <input type='hidden' name='cont' value='<?= $cont; ?>' />
                <!-- arquivos -->
                <input type='hidden' name='contSub' value='<?= $contSub; ?>' />
                <input type='hidden' name='oldStatus' value='<?= $row['status']; ?>' />
                <input type='hidden' name='data_atend' value='<?= $row['data_atendimento']; ?>' />
                <input type='hidden' name='abertopor' value='<?= $rowmail['user_id']; ?>' />
                <input type='hidden' name='total_asset' value='<?= $printCont; ?>' />
                <input type='hidden' name='data_abertura_hidden' value='<?= $row['data_abertura']; ?>' />
                <input type="hidden" name="submit" value="" />

                <input type="hidden" name="action" value="edit"/>



                <div class="w-100"></div>
                <div class="form-group col-md-8 d-none d-md-block">
                </div>
                <div class="form-group col-12 col-md-2 ">
                    <button type="button" id="idSubmit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
                </div>
                <div class="form-group col-12 col-md-2">
                    <button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
                </div>
            </div>
        </form>
    </div>

    <?php
} 


?>
<script src="../../includes/javascript/funcoes-3.0.js"></script>
<script src="../../includes/components/jquery/jquery.js"></script>
<script src="../../includes/components/jquery/jquery.initialize.min.js"></script>
<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
<script type="text/javascript">
    $(function() {

        // var maxField = 5; //Input fields increment limitation
        var maxField = <?= $row_config['conf_qtd_max_anexos']; ?>; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.field_wrapper'); //Input field wrapper


        /* var fieldHTML = '<div class="input-group my-1"><div class="input-group-prepend"><div class="input-group-text"><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus"></i></a></div><div class="custom-file"><input type="file" class="custom-file-input custom-file-input-sm" name="anexo[]" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01" lang="br"><label class="custom-file-label " for="inputGroupFile01"><?= TRANS('CHOOSE_FILE'); ?></label></div></div></div></div>'; */

        var fieldHTML = '<div class="input-group my-1"><div class="input-group-prepend"><div class="input-group-text"><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus"></i></a></div><div class="custom-file"><input type="file" class="custom-file-input" name="anexo[]"  aria-describedby="inputGroupFileAddon01" lang="br"><label class="custom-file-label text-truncate" for="inputGroupFile01"><?= TRANS('CHOOSE_FILE'); ?></label></div></div></div></div>';

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

        /* Autocompletar os nomes dos contatos */
        if ($('#contatos').length > 0) {
			$.ajax({
				url: './get_contacts_names.php',
				method: 'POST',
				dataType: 'json',
			}).done(function(response) {
				for (var i in response) {
					var option = '<option value="' + response[i].contato + '"/>';
					$('#contatos').append(option);
				}
			});
        }
        
        /* Autocompletar os emails dos contatos */
		if ($('#contatos_emails').length > 0) {
			$.ajax({
				url: './get_contacts_emails.php',
				method: 'POST',
				dataType: 'json',
			}).done(function(response) {
				for (var i in response) {
					var option = '<option value="' + response[i].contato_email + '"/>';
					$('#contatos_emails').append(option);
				}
			});
		}

        if ($('#contato_email').length > 0) {
			$('#contato_email').on('blur', function() {
				if ($('#contato_email').val() != '') {
					$('#mailUS').prop('disabled', false);
				} else {
					$('#mailUS').prop('disabled', true).prop('checked', false);
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

			// for (instance in CKEDITOR.instances) {
			// 	CKEDITOR.instances[instance].updateElement();
			// }

			var form = $('form').get(0);
			// disabled the submit button
			$("#idSubmit").prop("disabled", true);

			$.ajax({
				url: './tickets_process.php',
				method: 'POST',
				
				data: new FormData(form),
				dataType: 'json',

				cache: false,
				processData: false,
				contentType: false,
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
					var url = 'ticket_show.php?numero=' + response.numero;
					$(location).prop('href', url);
					return false;
				}
			});
			return false;
		});


        $('#oc_plus_minus').on('click', function() {
            //  console.log($(this).children().prop('class'));
            if ($(this).children().hasClass("fa-minus")) {
                $(this).children().removeClass('fa-minus');
                $(this).children().addClass('fa-plus');

                $(this).removeClass('badge-danger');
                $(this).addClass('badge-success');
            } else {
                $(this).children().removeClass('fa-plus');
                $(this).children().addClass('fa-minus');
                $(this).removeClass('badge-success');
                $(this).addClass('badge-danger');
            }
        });

        /* Adicionei o mutation observer em função dos elementos que são adicionados após o carregamento do DOM */
        var obs = $.initialize(".custom-file-input", function() {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

        }, {
            target: document.getElementById('field_wrapper')
        }); /* o target limita o scopo do observer */


        $(function() {
            $('[data-pop="popover"]').popover()
        });

        $('.popover-dismiss').popover({
            trigger: 'focus'
        });

    });

    
    function checa_etiqueta() {
		var inst = document.getElementById('idUnidade');
		var inv = document.getElementById('idEtiqueta');
		if (inst != null && inv != null) {
			if (inst.value == 'null' || !inv.value) {
				/* var msg = '<?php print TRANS('MSG_UNIT_TAG'); ?>!'
				window.alert(msg); */
				$("#divModalDetails").html('<div class="modal-header bg-light"><h5 class="modal-title"><?php print TRANS('WARNING'); ?></h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><p><?php print TRANS('FILL_UNIT_TAG'); ?></p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal"><?php print TRANS('LINK_CLOSE'); ?></button></div>');
				$('#modalDefault').modal();
			} else
				// popup_alerta('../../invmon/geral/equipment_show.php?comp_inst=' + inst.value + '&comp_inv=' + inv.value + '&popup=' + true);
				// popup_alerta('../../invmon/geral/equipment_show.php?unit=' + inst.value + '&tag=' + inv.value);
				$("#divModalDetails").load('../../invmon/geral/equipment_show.php?unit=' + inst.value + '&tag=' + inv.value);
                $('#modalDefault').modal();
		}
		return false;
	}

    function checa_chamados() {
		var inst = document.getElementById('idUnidade');
		var inv = document.getElementById('idEtiqueta');
		if (inst != null && inv != null) {
			if (inst.value == 'null' || !inv.value) {
				$("#divModalDetails").html('<div class="modal-header bg-light"><h5 class="modal-title"><?php print TRANS('WARNING'); ?></h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><p><?php print TRANS('FILL_UNIT_TAG'); ?></p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal"><?php print TRANS('LINK_CLOSE'); ?></button></div>');
				$('#modalDefault').modal();
			} else
				// popup_alerta('../../invmon/geral/ocorrencias.php?comp_inst=' + inst.value + '&comp_inv=' + inv.value + '&popup=' + true);
				// popup_alerta('./get_tickets_by_unit_and_tag.php?unit=' + inst.value + '&tag=' + inv.value);
				// $("#divModalDetails").html('');
				$("#divModalDetails").load('./get_tickets_by_unit_and_tag.php?unit=' + inst.value + '&tag=' + inv.value);
                $('#modalDefault').modal();
		}
		return false;
	}


	function checa_por_local() {
		//var local = document.form.local.value;
		var local = document.getElementById('idLocal');
		if (local != null) {
			if (local.value == -1) {

				$("#divModalDetails").html('<div class="modal-header bg-light"><h5 class="modal-title"><?php print TRANS('WARNING'); ?></h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><p><?php print TRANS('FILL_LOCATION'); ?></p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal"><?php print TRANS('LINK_CLOSE'); ?></button></div>');
				$('#modalDefault').modal();
			} else
				popup_alerta('../../invmon/geral/equipments_list.php?comp_local=' + local.value + '&popup=' + true);
		}
		return false;
	}

    function showSubsDetails(cod) {
        $("#divModalDetails").load('ticket_show.php?numero=' + cod);
        $('#modalDefault').modal();
    }
</script>
</body>

</html>