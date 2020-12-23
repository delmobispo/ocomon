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
$auth->showHeader();

//Todas as áreas que o usuário percente
$uareas = $_SESSION['s_area'];
if ($_SESSION['s_uareas']) {
    $uareas .= "," . $_SESSION['s_uareas'];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />

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

        .icon-expand:before {
            font-family: "Font Awesome\ 5 Free";
            /* content: "\f0fe"; */
            content: "\f105";
            font-weight: 900;
            font-size: 16px;
        }

        .icon-collapse:before {
            font-family: "Font Awesome\ 5 Free";
            /* content: "\f146"; */
            content: "\f107";
            font-weight: 900;
            font-size: 16px;
        }


        .just-padding {
            padding: 15px;
        }

        .list-group.list-group-root {
            padding: 0;
            overflow: hidden;
        }

        .list-group>a {
            color: #111111 !important;
        }

        .list-group>a:hover {
            text-decoration: none !important;
            color: #111111 !important;
        }

        .list-group.list-group-root .list-group {
            margin-bottom: 0;
        }

        .list-group.list-group-root .list-group-item {
            border-radius: 0;
            border-width: 0 0 0 0;
        }

        .list-group.list-group-root>.list-group-item:first-child {
            border-top-width: 0;
        }

        .list-group.list-group-root>.list-group>.list-group-item {
            padding-left: 30px;
        }

        .list-group.list-group-root>.list-group>.list-group>.list-group-item {
            padding-left: 45px;
        }

        .list-group-item .glyphicon,
        .list-group-item .icon-expand {
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    <?php
    if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
        echo $_SESSION['flash'];
        $_SESSION['flash'] = '';
    }
    ?>

    <div class="container-fluid">
        <h5 class="my-4"><i class="fas fa-stream text-secondary"></i>&nbsp;<?= TRANS('YOUR_AREAS_TICKETS_TREE'); ?></h5>
        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetails">
                    </div>
                </div>
            </div>
        </div>

        <?php





        $qryTotal = "select a.sistema area, a.sis_id area_cod from ocorrencias o left join sistemas a on o.sistema = a.sis_id" .
            " left join `status` s on s.stat_id = o.status where o.sistema in (" . $uareas . ") and s.stat_painel in (1,2) ";
        // $execTotal = mysql_query($qryTotal) or die (TRANS('MSG_ERR_TOTAL_OCCO'). $qryTotal);
        $execTotal = $conn->query($qryTotal);
        $regTotal = $execTotal->rowcount();

        //Todas as áreas que o usuário percente
        $qryAreas = "select count(*) total, a.sistema area, a.sis_id area_cod from ocorrencias o left join sistemas a on o.sistema = a.sis_id" .
            " left join `status` s on s.stat_id = o.status where o.sistema in (" . $uareas . ") and s.stat_painel in (1,2) " .
            "group by a.sistema";
        $execAreas =  $conn->query($qryAreas);
        $regAreas = $execAreas->rowcount();


        ?>

        <div class="just-padding">

            <p><?= TRANS('THEREARE'); ?>&nbsp;<span class="font-weight-bold text-danger"><?= $regTotal; ?></span>&nbsp;<?= TRANS('HOME_OPENED_CALLS'); ?>:</p>

            <div class="list-group list-group-root well">
                <?php

                //$a = 0;
                //$b = 0;
                foreach ($execAreas->fetchAll() as $rowAreas) {
                ?>
                    <a href="#area<?= $rowAreas['area_cod'] ?>" class="list-group-item" data-toggle="collapse">
                        <span class="glyphicon icon-expand"></span>
                        <?= TRANS('THEREARE'); ?>&nbsp;
                        <span class="text-danger font-weight-bold"><?= $rowAreas['total']; ?></span>&nbsp;
                        <?= TRANS('HOME_OPENED_CALLS_TO_AREA'); ?>:&nbsp;
                        <span class="text-success font-weight-bold"><?= $rowAreas['area']; ?></span>
                    </a>
                    <?php

                    //TOTAL DE NÍVEIS DE STATUS
                    $qryStatus = "select count(*) total, o.*, s.* from ocorrencias o left join `status` s on o.status = s.stat_id where " .
                        "o.sistema = " . $rowAreas['area_cod'] . " and s.stat_painel in (1,2) group by s.status";
                    $execStatus = $conn->query($qryStatus);
                    ////$a = 0;

                    ?>
                    <div class="list-group collapse" id="area<?= $rowAreas['area_cod'] ?>">
                        <?php
                        foreach ($execStatus->fetchall() as $rowStatus) {
                        ?>
                            <a href="#status<?= $rowStatus['stat_id'] ?>-<?= $rowAreas['area_cod'] ?>" class="list-group-item" data-toggle="collapse">
                                <span class="glyphicon icon-expand"></span><span class="font-weight-bold"><?= $rowStatus['status'] . " : "; ?></span><span class="text-danger font-weight-bold"><?= $rowStatus['total'] . " ocorrências "; ?></span>
                            </a>

                            <!-- Listagem dos chamados -->
                            <div class="list-group-item collapse" id="status<?= $rowStatus['stat_id'] ?>-<?= $rowAreas['area_cod'] ?>">
                                <table id="table<?= $rowStatus['stat_id'] ?>-<?= $rowAreas['area_cod'] ?>" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
                                    <?php

                                    $qryDetail = $QRY["ocorrencias_full_ini"] . " WHERE o.sistema = " . $rowAreas['area_cod'] . " and s.stat_painel in (1,2) and " .
                                        " o.status = " . $rowStatus['stat_id'] . "";
                                    $execDetail = $conn->query($qryDetail);
                                    ?>
                                    <thead>
                                        <tr class="header">
                                            <td class="line"><?= TRANS('TICKET_NUMBER'); ?></td>
                                            <td class="line"><?= TRANS('ISSUE_TYPE'); ?></td>
                                            <td class="line"><?= TRANS('FIELD_CONTACT'); ?><br /><?= TRANS('COL_PHONE'); ?></td>
                                            <td class="line"><?= TRANS('DEPARTMENT'); ?><br /><?= TRANS('DESCRIPTION'); ?></td>
                                            <td class="line"><?= TRANS('FIELD_LAST_OPERATOR'); ?></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        $j = 2;

                                        foreach ($execDetail->fetchall() as $rowDetail) { /* registros */

                                            $j++;

                                            print "<tr>";

                                            $qryImg = "select * from imagens where img_oco = " . $rowDetail['numero'] . "";
                                            $execImg = $conn->query($qryImg);
                                            $rowTela = $execImg->fetch();
                                            $regImg = $execImg->rowCount();
                                            if ($regImg != 0) {
                                                $linkImg = "<a onClick=\"javascript:popup_wide('./ocomon/geral/listFiles.php?COD=" . $rowDetail['numero'] . "')\"><img src='../../includes/icons/attach2.png'></a>";
                                            } else $linkImg = "";

                                            $sqlSubCall = "select * from ocodeps where dep_pai = " . $rowDetail['numero'] . " or dep_filho=" . $rowDetail['numero'] . "";
                                            $execSubCall = $conn->query($sqlSubCall);
                                            $regSub = $execSubCall->rowCount();
                                            if ($regSub > 0) {
                                                #É CHAMADO PAI?
                                                $_sqlSubCall = "select * from ocodeps where dep_pai = " . $rowDetail['numero'] . "";
                                                $_execSubCall = $conn->query($_sqlSubCall);
                                                $_regSub = $_execSubCall->rowCount();
                                                $comDeps = false;
                                                foreach ($_execSubCall->fetchall() as $rowSubPai) {
                                                    $_sqlStatus = "select o.*, s.* from ocorrencias o, `status` s  where o.numero=" . $rowSubPai['dep_filho'] . " and o.`status`=s.stat_id and s.stat_painel not in (3) ";
                                                    $_execStatus = $conn->query($_sqlStatus);
                                                    $_regStatus = $_execStatus->rowCount();
                                                    if ($_regStatus > 0) {
                                                        $comDeps = true;
                                                    }
                                                }
                                                if ($comDeps) {
                                                    $imgSub = "<img src='../../includes/icons/view_tree_red.png' width='16' height='16' title='" . TRANS('FIELD_CALL_BOND_HANG') . "'>";
                                                } else
                                                    $imgSub =  "<img src='../../includes/icons/view_tree_green.png' width='16' height='16' title='" . TRANS('FIELD_CALL_BOND_NOT_HANG') . "'>";
                                            } else
                                                $imgSub = "";

                                            print "<td class='line'><a onClick=\"openTicketInfo('" . $rowDetail['numero'] . "')\">" . $rowDetail['numero'] . "</a> " . $imgSub . "</TD>";

                                            //print "<td class='line'>".$rowDetail['numero']."</TD>";
                                            print "<td class='line'>" . $linkImg . "&nbsp;" . $rowDetail['problema'] . "</TD>";
                                            print "<td class='line'><b>" . $rowDetail['contato'] . "</b><br>" . $rowDetail['telefone'] . "</TD>";
                                            $texto = trim($rowDetail['descricao']);
                                            if (strlen($texto) > 200) {
                                                $texto = substr($texto, 0, 195) . " ..... ";
                                            };
                                            print "<td class='line'><b>" . $rowDetail['setor'] . "</b><br>" . $texto . "</TD>";
                                            print "<td class='line'>" . $rowDetail['nome'] . "</TD>";
                                            print "</TR>";
                                        }
                                        //$a++;
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                <?php
                    //$a++;
                    //$b++;
                }
                ?>
            </div>
        </div>


        <script src="../../includes/components/jquery/jquery.js"></script>
        <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
        <script src="../../includes/javascript/funcoes-3.0.js"></script>
        <SCRIPT LANGUAGE="javaScript">
            $(function() {

                $('.list-group-item').on('click', function() {
                    $('.glyphicon', this)
                        .toggleClass('icon-expand')
                        .toggleClass('icon-collapse');

                    /* Solução de contorno para conseguir esconder a div após abrir carregar o ajax load na modal */
                    var target = $(this).attr('href');
                    if ($('.glyphicon', this).hasClass('icon-expand')) {
                        // $(target).removeClass('show');
                        $(target).hide();
                    } else {
                        $(target).show();
                    }
                });

                $('table').each(function() {
                    $(this).DataTable({
                        paging: true,
                        deferRender: true,

                        "language": {
                            "url": "../../includes/components/datatables/datatables.pt-br.json"
                        }
                    });
                });

                $('#modal').on('hidden.bs.modal', function(e) {
                    // do something...
                    console.log('modal fechada');
                    // $('#modal').modal('dispose');
                })
            });

            function openTicketInfo(ticket) {
                let location = 'ticket_show.php?numero=' + ticket;
                $("#divDetails").load(location);
                $('#modal').modal();
            }
        </script>
    </div>
</body>

</html>