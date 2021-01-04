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

        .oc-cursor {
            cursor: pointer;
        }
    </style>

</head>

<body class="bg-light">

    <?php

    // var_dump($_REQUEST);

    if (isset($_POST['tag']) && !empty($_POST['tag'])) {
        $tag = $_POST['tag'];
    } else
	if (isset($_GET['tag']) && !empty($_GET['tag'])) {
        $tag = $_GET['tag'];
    } else {
        echo message('warning', 'Ooops!', TRANS('MSG_ERR_NOT_EXECUTE'), '', '', 1);
        return;
    }

    if (isset($_POST['unit']) && !empty($_POST['unit'])) {
        $unit = $_POST['unit'];
    } else
	if (isset($_GET['unit']) && !empty($_GET['unit'])) {
        $unit = $_GET['unit'];
    } else {
        echo message('warning', 'Ooops!', TRANS('MSG_ERR_NOT_EXECUTE'), '', '', 1);
        return;
    }

    $query = $QRY["full_detail_ini"];
    $query .= " AND (c.comp_inv in ('" . $tag . "'))";

    if ($unit != '-1') {
        $query .= " AND (inst.inst_cod IN (" . $unit . "))";
    }
    $query .= $QRY["full_detail_fim"];

    $resultado = $conn->query($query);
    $row = $resultado->fetch();

    if (!$row) {
        echo message ('warning','Ooops!', TRANS('NO_RECORDS_FOUND'), '', '', 1);
        return;
    }

    // dump($query);
    // var_dump([
    //     'row' => $row,
    // ]); exit();


    /* Para manter a compatibilidade com versões antigas */
    $qryPieces = $QRY["componentexequip_ini"];
    $table = "equipxpieces";
    $sqlTest = "SELECT * FROM {$table}";
    try {
        $conn->query($sqlTest);
    }
    catch (Exception $e) {
        $table = "equipXpieces";
        $qryPieces = $QRY["componenteXequip_ini"];
    }



    /* Componentes avulsos */
    // $qryPieces = $QRY["componenteXequip_ini"];
    $qryPieces .= " and eqp.eqp_equip_inv in ('" . $tag . "') and eqp.eqp_equip_inst=" . $unit . "";
    $qryPieces .= $QRY["componenteXequip_fim"];

    // dump($qryPieces);

    $resultPieces = $conn->query($qryPieces);

    $pieces = $resultPieces->rowCount();


    /* Arquivos associados ao modelo*/
    $sqlFilesModel = "SELECT  i.* FROM imagens i  WHERE i.img_model ='" . $row['modelo_cod'] . "'  order by i.img_inv ";
    $resFilesModel = $conn->query($sqlFilesModel);
    $hasFilesFromModel = $resFilesModel->rowCount();

    /* Arquivos associados diretamente ao equipamento */
    $sqlFilesEquipment = "SELECT  i.* FROM imagens i  WHERE i.img_inst ='".$row['cod_inst']."' AND i.img_inv ='".$row['etiqueta']."'  ORDER BY i.img_inv ";
    $resFilesEquipment = $conn->query($sqlFilesEquipment);
    $hasFilesFromEquipment = $resFilesEquipment->rowCount();


    /* Arquivos nos chamados relacionados */
    $sqlFiles = "SELECT o.*, i.* FROM ocorrencias o , imagens i
				WHERE (i.img_oco = o.numero) AND (o.equipamento ='" . $tag . "' AND o.instituicao ='" . $unit . "')  ORDER BY o.numero ";
    $resultFiles = $conn->query($sqlFiles);
    $hasFilesFromTickets = $resultFiles->rowCount();

    /* Definições do grid */
    $colLabel = "col-sm-3 text-md-right font-weight-bold p-2";
    $colsDefault = "small text-break border-bottom rounded p-2 bg-white"; /* border-secondary */
    $colContent = $colsDefault . " col-sm-3 col-md-3";
    $colContentLine = $colsDefault . " col-sm-9";
    ?>

    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    <!-- MENU DE OPÇÕES -->
    <nav class="navbar navbar-expand-md navbar-light  p-0 rounded" style="background-color: #48606b;">
        <!-- bg-secondary -->
        <!-- style="background-color: #dbdbdb; -->
        <div class="ml-2 font-weight-bold text-white"><?= TRANS('COL_UNIT'); ?> <?= $row['instituicao']; ?> - <?= TRANS('ASSET_TAG'); ?> <?= $row['etiqueta']; ?></div> <!-- navbar-brand -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#idMenuOcorrencia" aria-controls="idMenuOcorrencia" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="idMenuOcorrencia">
            <div class="navbar-nav ml-2 mr-2">

                <a class="nav-link small text-white" href="../../invmon/geral/altera_dados_computador.php?comp_inv=<?= $tag ?>&comp_inst=<?= $unit; ?>"><i class="fas fa-edit"></i><?= TRANS('BT_EDIT'); ?></a>

                <a class="nav-link small text-white" onclick="getTickets('<?= $unit;?>','<?= $tag;?>')"><i class="fas fa-bars"></i><?= TRANS('TICKETS'); ?></a>

                <a class="nav-link small text-white" onclick="popup_alerta('../../invmon/geral/comp_soft.php?popup=true&comp_inv=<?= $tag;?>&comp_inst=<?= $unit;?>')"><i class="fas fa-photo-video"></i><?= TRANS('MNL_SW'); ?></a>

                <a class="nav-link small text-white" onclick="popup_alerta('../../invmon/geral/hw_historico.php?inv=<?= $tag;?>&inst=<?= $unit;?>')"><i class="fas fa-edit"></i><?= TRANS('LINK_HISTORY_EXCHANGE'); ?></a>

                <a class="nav-link small text-white" onclick="popup_alerta('../../invmon/geral/mostra_historico.php?popup=true&comp_inv=<?= $tag;?>&comp_inst=<?= $unit;?>')"><i class="fas fa-address-card"></i><?= TRANS('DEPARTMENTS'); ?></a>

                <a class="nav-link small text-white" onclick="popup_alerta('../../invmon/geral/consulta_garantia.php?popup=true&comp_inv=<?= $tag;?>&comp_inst=<?= $unit;?>')"><i class="fas fa-business-time"></i><?= TRANS('LINK_GUARANT'); ?></a>

                <a class="nav-link small text-white" onclick="popup_alerta('../../invmon/geral/docs_assoc_model.php?popup=true&model=<?= $row['modelo_cod'];?>')"><i class="fas fa-book"></i><?= TRANS('LINK_DOCUMENTS'); ?></a>

            </div>
        </div>
    </nav>
    <!-- FINAL DO MENU DE OPÇÕES-->


    <div class="modal" tabindex="-1" style="z-index:9001!important" id="modalEquipment">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div id="divModalEquipment" class="p-3"></div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" id="modalDefault">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div id="divModalDetails" class="p-3"></div>
            </div>
        </div>
    </div>


    <div class="container-fluid bg-light">

        <?php

        /* MENSAGEM DE RETORNO PARA ABERTURA, EDIÇÃO E ENCERRAMENTO DO CHAMADO */
        if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
            echo $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
        ?>

        <div class="accordion"  id="accordionBasicInfo">

            <div class="card">
                <div class="card-header" id="cardBasicInfo">
                    <h2 class="mb-0">
                        <button class="btn btn-block text-left" type="button" data-toggle="collapse" data-target="#basicInfo" aria-expanded="true" aria-controls="basicInfo" onclick="this.blur();">
                            <h6 class="font-weight-bold"><i class="fas fa-info-circle text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('BASIC_INFORMATIONS')); ?></h6>
                        </button>
                    </h2>
                </div>
                
                <div id="basicInfo" class="collapse show" aria-labelledby="cardBasicInfo" data-parent="#accordionBasicInfo">
                    <div class="card-body">
                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_TYPE_EQUIP'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['equipamento']; ?></div>
                            <div class="<?= $colLabel; ?>"><?= TRANS('COL_MANUFACTURE'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['fab_nome']; ?></div>
                        </div>

                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('COL_MODEL'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['modelo']; ?></div>
                            <div class="<?= $colLabel; ?>"><?= TRANS('SERIAL_NUMBER'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['serial']; ?></div>
                        </div>

                        <div class="row my-2">

                            <div class="<?= $colLabel; ?>"><?= TRANS('DEPARTMENT'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['local']; ?></div>
                            <div class="<?= $colLabel; ?>"><?= TRANS('STATE'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['situac_nome']; ?></div>
                        </div>

                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_NAME_COMPUTER'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['nome']; ?></div>
                        </div>
                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('ENTRY_TYPE_ADDITIONAL_INFO'); ?></div>
                            <div class="<?= $colContentLine; ?>"><?= $row['comentario']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            


        <div class="accordion"  id="accordionInventoryDetails">
            <div class="card">
                <div class="card-header" id="cardConfigurations">
                    <h2 class="mb-0">
                        <button class="btn btn-block text-left" type="button" data-toggle="collapse" data-target="#configuration" aria-expanded="true" aria-controls="configuration" onclick="this.blur();">
                            <h6 class="font-weight-bold"><i class="fas fa-hdd text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('SUBTTL_DATA_COMPLE_CONFIG')); ?></h6>
                        </button>
                    </h2>
                </div>

                <div id="configuration" class="collapse " aria-labelledby="cardConfigurations" data-parent="#accordionInventoryDetails">
                <!-- <div class="card-body"> -->
                    <div class="row my-2">
                        <div class="<?= $colLabel; ?>"><?= TRANS('MOTHERBOARD'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['fabricante_mb'] . " " . $row['mb']; ?></div>
                        <div class="<?= $colLabel; ?>"><?= TRANS('PROCESSOR'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['processador'] . " " . $row['clock'] . " " . $row['proc_sufixo']; ?></div>
                    </div>

                    <div class="row my-2">
                        <div class="<?= $colLabel; ?>"><?= TRANS('CARD_MEMORY'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['memoria'] . " " . $row['memo_sufixo']; ?></div>
                        <div class="<?= $colLabel; ?>"><?= TRANS('CARD_VIDEO'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['fabricante_video'] . " " . $row['video']; ?></div>
                    </div>

                    <div class="row my-2">
                        <div class="<?= $colLabel; ?>"><?= TRANS('CARD_SOUND'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['fabricante_som'] . " " . $row['som']; ?></div>
                        <div class="<?= $colLabel; ?>"><?= TRANS('CARD_NETWORK'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['rede_fabricante'] . " " . $row['rede']; ?></div>
                    </div>

                    <div class="row my-2">
                        <div class="<?= $colLabel; ?>"><?= TRANS('CARD_MODEN'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['fabricante_modem'] . " " . $row['modem']; ?></div>
                        <div class="<?= $colLabel; ?>"><?= TRANS('MNL_HD'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['fabricante_hd'] . " " . $row['hd'] . " " . $row['hd_capacidade'] . " " . $row['hd_sufixo']; ?></div>
                    </div>

                    <div class="row my-2">
                        <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_CDROM'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['fabricante_cdrom'] . " " . $row['cdrom']; ?></div>
                        <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_RECORD_CD'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['fabricante_gravador'] . " " . $row['gravador']; ?></div>
                    </div>

                    <div class="row my-2">
                        <div class="<?= $colLabel; ?>"><?= TRANS('DVD'); ?></div>
                        <div class="<?= $colContent; ?>"><?= $row['fabricante_dvd'] . " " . $row['dvd']; ?></div>
                    </div>
                </div>
                <!-- </div> -->
            </div>

            <div class="card">
                <div class="card-header" id="cardConfigurationOthers">
                    <h2 class="mb-0">
                        <button class="btn btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#configurationOthers" aria-expanded="false" aria-controls="configurationOthers" onclick="this.blur();">
                            <h6 class="font-weight-bold"><i class="fas fa-print text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('SUBTTL_DATA_COMP_OTHERS')); ?></h6>
                        </button>
                    </h2>
                </div>
                <div id="configurationOthers" class="collapse" aria-labelledby="cardConfigurationOthers" data-parent="#accordionInventoryDetails">
                    <div class="card-body">

                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_TYPE_PRINTER'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['impressora']; ?></div>
                            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_MONITOR'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['polegada_nome']; ?></div>
                        </div>
                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_SCANNER'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['resol_nome']; ?></div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card">
                <div class="card-header" id="cardInvoice">
                    <h2 class="mb-0">
                        <button class="btn btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#configurationInvoice" aria-expanded="false" aria-controls="configurationInvoice" onclick="this.blur();">
                            <h6 class="font-weight-bold"><i class="fas fa-file-invoice-dollar text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('TXT_OBS_DATA_COMPLEM_2')); ?></h6>
                        </button>
                    </h2>
                </div>
                <div id="configurationInvoice" class="collapse" aria-labelledby="cardInvoice" data-parent="#accordionInventoryDetails">
                    <div class="card-body">

                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_FISCAL_NOTES'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['nota']; ?></div>
                            <div class="<?= $colLabel; ?>"><?= TRANS('COST_CENTER'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['ccusto']; ?></div>
                        </div>

                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('FIELD_VALUE'); ?></div>
                            <div class="<?= $colContent; ?>"><?= priceScreen($row['valor']); ?></div>
                            <div class="<?= $colLabel; ?>"><?= TRANS('PURCHASE_DATE'); ?></div>
                            <div class="<?= $colContent; ?>"><?= dateScreen($row['data_compra'], 1); ?></div>
                        </div>

                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('COL_RECTORY'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['reitoria']; ?></div>
                            <div class="<?= $colLabel; ?>"><?= TRANS('COL_SUBSCRIBE_DATE'); ?></div>
                            <div class="<?= $colContent; ?>"><?= dateScreen($row['data_cadastro'], 1); ?></div>
                        </div>
                        <div class="row my-2">
                            <div class="<?= $colLabel; ?>"><?= TRANS('COL_VENDOR'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['fornecedor_nome']; ?></div>
                            <div class="<?= $colLabel; ?>"><?= TRANS('TECHNICAL_ASSISTANCE'); ?></div>
                            <div class="<?= $colContent; ?>"><?= $row['assistencia']; ?></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>



        <?php
        /* ABAS */
        $classDisabledPieces = ($pieces > 0 ? '' : ' disabled');
        $ariaDisabledPieces = ($pieces > 0 ? '' : ' true');
        $classDisabledFilesFromEquipment = ($hasFilesFromEquipment > 0 ? '' : ' disabled');
        $ariaDisabledFilesFromEquipment = ($hasFilesFromEquipment > 0 ? '' : ' true');
        $classDisabledFilesFromModel = ($hasFilesFromModel > 0 ? '' : ' disabled');
        $ariaDisabledFilesFromModel = ($hasFilesFromModel > 0 ? '' : ' true');
        $classDisabledFilesFromTickets = ($hasFilesFromTickets > 0 ? '' : ' disabled');
        $ariaDisabledFilesFromTickets = ($hasFilesFromTickets > 0 ? '' : ' true');


        ?>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"></div>
            <div class="<?= $colContentLine; ?>">
                <!-- <div class="<?= $colsDefault; ?> col-sm-12 d-flex justify-content-md-center"> -->
                <ul class="nav nav-pills " id="pills-tab-inventory" role="tablist">
                    <li class="nav-item" role="pieces">
                        <a class="nav-link active <?= $classDisabledPieces; ?>" id="divPieces-tab" data-toggle="pill" href="#divPieces" role="tab" aria-controls="divPieces" aria-selected="true" aria-disabled="<?= $ariaDisabledPieces; ?>"><i class="fas fa-comment-alt"></i>&nbsp;<?= TRANS('DETACHED_COMPONENTS'); ?>&nbsp;<span class="badge badge-light p-1"><?= $pieces; ?></span></a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledFilesFromEquipment; ?>" id="divFilesFromEquipment-tab" data-toggle="pill" href="#divFilesFromEquipment" role="tab" aria-controls="divFilesFromEquipment" aria-selected="true" aria-disabled="<?= $ariaDisabledFilesFromEquipment; ?>"><i class="fas fa-paperclip"></i>&nbsp;<?= TRANS('FILES_FROM_EQUIPMENT'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $hasFilesFromEquipment; ?></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledFilesFromModel; ?>" id="divFilesFromModel-tab" data-toggle="pill" href="#divFilesFromModel" role="tab" aria-controls="divFilesFromModel" aria-selected="true" aria-disabled="<?= $ariaDisabledFilesFromModel; ?>"><i class="fas fa-paperclip"></i>&nbsp;<?= TRANS('FILES_FROM_MODEL'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $hasFilesFromModel; ?></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledFilesFromTickets; ?>" id="divFilesFromTickets-tab" data-toggle="pill" href="#divFilesFromTickets" role="tab" aria-controls="divFilesFromTickets" aria-selected="true" aria-disabled="<?= $ariaDisabledFilesFromTickets; ?>"><i class="fas fa-paperclip"></i>&nbsp;<?= TRANS('FILES_FROM_RELATED_TICKETS'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $hasFilesFromTickets; ?></span></a>
                    </li>

                </ul>
            </div>
        </div>
        <!-- FINAL DAS ABAS -->



        <!-- LISTAGEM DE COMPONENTES AVULSOS -->

        <div class="tab-content" id="pills-tabInventoryContent">
            <?php
            if ($pieces) {
            ?>

                <div class="tab-pane fade show active" id="divPieces" role="tabpanel" aria-labelledby="divPieces-tab">

                    <div class="row my-2">

                        <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="pieces">
                            <!-- collapse -->
                            <table class="table  table-hover table-striped rounded">
                                <!-- table-responsive -->
                                <thead class="text-white" style="background-color: #48606b;">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col"><?= TRANS('SUBTTL_DATA_COMPLE_PIECES'); ?></th>
                                        <th scope="col"><?= TRANS('COMPONENT'); ?></th>
                                        <th scope="col"><?= TRANS('SERIAL_NUMBER'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($resultPieces->fetchAll() as $rowPiece) {

                                    ?>
                                        <tr>
                                            <!-- <th scope="row"><?= $i; ?></th> -->
                                            <td class="line"><a onclick="popupS('peripherals_tagged.php?action=view&cod=<?= $rowPiece['estoq_cod']; ?>')"><?= $i; ?></a></td>
                                            <td><?= $rowPiece['item_nome']; ?></td>
                                            <td><?= $rowPiece['fabricante'] . " " . $rowPiece['modelo'] . " " . $rowPiece['capacidade'] . " " . $rowPiece['sufixo']; ?></td>
                                            <td><?= $rowPiece['estoq_sn']; ?></td>
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
            /* FINAL DA LISTAGEM DE COMPONENTES AVULSOS */


            /* TRECHO PARA EXIBIÇÃO DA LISTAGEM DE ARQUIVOS ANEXOS DO EQUIPAMENTO */
            if ($hasFilesFromEquipment) {
                ?>
                    <div class="tab-pane fade" id="divFilesFromEquipment" role="tabpanel" aria-labelledby="divFilesFromEquipment-tab">
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
                                        foreach ($resFilesEquipment->fetchAll() as $rowFiles) {

                                            $size = round($rowFiles['img_size'] / 1024, 1);
                                            $rowFiles['img_tipo'] . "](" . $size . "k)";

                                            if (isImage($rowFiles["img_tipo"])) {
                                                $viewImage = "&nbsp;<a onClick=\"javascript:popupWH('../../includes/functions/showImg.php?" .
                                                    "file=" . $rowFiles['img_oco'] . "&cod=" . $rowFiles['img_cod'] . "'," . $rowFiles['img_largura'] . "," . $rowFiles['img_altura'] . ")\" " .
                                                    "title='view'><i class='fa fa-search'></i></a>";
                                            } else {
                                                $viewImage = "";
                                            }
                                        ?>
                                            <tr>
                                                <th scope="row"><?= $i; ?></th>
                                                <td><?= $rowFiles['img_tipo']; ?></td>
                                                <td><?= $size; ?>k</td>
                                                <td><a onClick="redirect('../../includes/functions/download.php?file=<?= $tag; ?>&cod=<?= $rowFiles['img_cod']; ?>')" title="Download the file"><?= $rowFiles['img_nome']; ?></a><?= $viewImage; ?></i></td>
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
            /* FINAL DO TRECHO DE LISTAGEM DE ARQUIVOS ANEXOS DO EQUIPAMENTO*/


             /* TRECHO PARA EXIBIÇÃO DA LISTAGEM DE ARQUIVOS ANEXOS DO EQUIPAMENTO */
             if ($hasFilesFromModel) {
                ?>
                    <div class="tab-pane fade" id="divFilesFromModel" role="tabpanel" aria-labelledby="divFilesFromModel-tab">
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
                                        foreach ($resFilesModel->fetchAll() as $rowFiles) {

                                            $size = round($rowFiles['img_size'] / 1024, 1);
                                            $rowFiles['img_tipo'] . "](" . $size . "k)";

                                            if (isImage($rowFiles["img_tipo"])) {
                                                $viewImage = "&nbsp;<a onClick=\"javascript:popupWH('../../includes/functions/showImg.php?" .
                                                    "file=" . $rowFiles['img_cod'] . "&cod=" . $rowFiles['img_cod'] . "'," . $rowFiles['img_largura'] . "," . $rowFiles['img_altura'] . ")\" " .
                                                    "title='view'><i class='fa fa-search'></i></a>";
                                            } else {
                                                $viewImage = "";
                                            }
                                        ?>
                                            <tr>
                                                <th scope="row"><?= $i; ?></th>
                                                <td><?= $rowFiles['img_tipo']; ?></td>
                                                <td><?= $size; ?>k</td>
                                                <td><a onClick="redirect('../../includes/functions/download.php?file=<?= $tag; ?>&cod=<?= $rowFiles['img_cod']; ?>')" title="Download the file"><?= $rowFiles['img_nome']; ?></a><?= $viewImage; ?></i></td>
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
            /* FINAL DO TRECHO DE LISTAGEM DE ARQUIVOS ANEXOS DO EQUIPAMENTO*/


            /* TRECHO PARA EXIBIÇÃO DA LISTAGEM DE ARQUIVOS ANEXOS */
            if ($hasFilesFromTickets) {
            ?>
                <div class="tab-pane fade" id="divFilesFromTickets" role="tabpanel" aria-labelledby="divFilesFromTickets-tab">
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
                                            $viewImage = "&nbsp;<a onClick=\"javascript:popupWH('../../includes/functions/showImg.php?" .
                                                "file=" . $rowFiles['img_cod'] . "&cod=" . $rowFiles['img_cod'] . "'," . $rowFiles['img_largura'] . "," . $rowFiles['img_altura'] . ")\" " .
                                                "title='view'><i class='fa fa-search'></i></a>";
                                        } else {
                                            $viewImage = "";
                                        }
                                    ?>
                                        <tr>
                                            <th scope="row"><?= $i; ?></th>
                                            <td><?= $rowFiles['img_tipo']; ?></td>
                                            <td><?= $size; ?>k</td>
                                            <td><a onClick="redirect('../../includes/functions/download.php?file=<?= $tag; ?>&cod=<?= $rowFiles['img_cod']; ?>')" title="Download the file"><?= $rowFiles['img_nome']; ?></a><?= $viewImage; ?></i></td>
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



        });


        function loadPageInModal(page) {
            $("#divModalEquipment").load(page);
            $('#modalEquipment').modal();
        }

        function getTickets(unit, tag) {
		
            $("#divModalEquipment").load('../../ocomon/geral/get_tickets_by_unit_and_tag.php?unit=' + unit + '&tag=' + tag);
            $('#modalEquipment').modal();
            return false;
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