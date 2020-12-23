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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/invoice-print.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />

	<style>
		
	</style>

	<title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>

	<div class="container-fluid">
		<h5 class="my-4"><i class="fas fa-print text-secondary"></i>&nbsp;<?= TRANS('PRINT_TO_TREATING'); ?></h5>
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

        
        $sql = $QRY["ocorrencias_full_ini"]." WHERE numero = '" . $_GET['numero'] . "' ORDER BY numero";
        try {
            $res = $conn->query($sql);
        }
        catch (Exception $e) {
            exit();
        }
        
        $row = $res->fetch();

        $sqlPriorityDesc = "SELECT * FROM prior_atend WHERE pr_cod = '" . $row['oco_prior'] . "'";
        $resPriority = $conn->query($sqlPriorityDesc);
        $rowPriority = $resPriority->fetch();

        $qryCatProb = "SELECT * FROM problemas as p " .
        "LEFT JOIN sistemas as s on p.prob_area = s.sis_id " .
        "LEFT JOIN sla_solucao as sl on sl.slas_cod = p.prob_sla " .
        "LEFT JOIN prob_tipo_1 as pt1 on pt1.probt1_cod = p.prob_tipo_1 " .
        "LEFT JOIN prob_tipo_2 as pt2 on pt2.probt2_cod = p.prob_tipo_2 " .
        "LEFT JOIN prob_tipo_3 as pt3 on pt3.probt3_cod = p.prob_tipo_3 " .
        " WHERE p.prob_id = " . $row['prob_cod'] . " ";
        $execCatprob = $conn->query($qryCatProb);
        $rowCatProb = $execCatprob->fetch();
        
        $sqlAssentamentos = "SELECT a.*, u.* 
                            FROM assentamentos a, usuarios u 
                            WHERE a.responsavel = u.user_id AND a.ocorrencia='" . $_GET['numero'] . "' 
                                AND a.asset_privated = 0 ORDER BY numero";
        $resAssentamentos = $conn->query($sqlAssentamentos);
        $countAssentamentos = $resAssentamentos->rowCount();

        ?>

        <div class="invoice">
			<header class="clearfix">
				<div class="row">
					<div class="col-sm-6 mt-md">
						<h2 class="h2 mt-none mb-sm text-dark text-bold"><?= TRANS('TICKET_NUMBER'); ?></h2>
						<h4 class="h4 m-none text-dark text-bold"><?= $row['numero']; ?></h4>
					</div>
					<div class="col-sm-6 text-right mt-md mb-md"> <!-- mt-md mb-md -->
						<address class="ib mr-xlg">
                            <?= TRANS('RESPONSIBLE_AREA'); ?>:&nbsp;<span class="text-dark"><?= $row['area']; ?></span>
                            <br/>
                            <?= TRANS('FIELD_LAST_OPERATOR'); ?>:&nbsp;<span class="text-dark"><?= $row['nome']; ?></span>
                            <br/>
                            <?= TRANS('COL_STATUS'); ?>:&nbsp;<span class="text-dark"><?= $row['chamado_status']; ?></span>
                            <br/>
                            <?= TRANS('PRINTING_DATE'); ?>:&nbsp;<span class="text-dark"><?= dateScreen(date("Y-m-d H:i:s")); ?></span>
						</address>
					</div>
				</div>
            </header>
            
            <div class="bill-to ">
				<div class="row">
					<div class="col-md-6">
						<div class="bill-to">
							<!-- <p class="h5 mb-xs text-dark text-semibold">To:</p> -->
							<address>
                                <?= TRANS('OPENED_BY'); ?>:&nbsp;<span class="text-dark"><?= $row['aberto_por']; ?></span>
                                <br/>
                                <?= TRANS('FIELD_CONTACT'); ?>:&nbsp;<span class="text-dark"><?= $row['contato']; ?></span>
                                <br/>
                                <?= TRANS('COL_PHONE'); ?>:&nbsp;<span class="text-dark"><?= $row['telefone']; ?></span>
                                <br/>
                                <?= TRANS('DEPARTMENT'); ?>:&nbsp;<span class="text-dark"><?= $row['setor']; ?></span>
                                <br/>
                                <?= TRANS('OCO_PRIORITY'); ?>:&nbsp;<span class="text-dark"><?= $rowPriority['pr_desc']; ?></span>
							</address>
						</div>
					</div>
					<div class="col-md-6">
						<!-- <div class="bill-data text-right"> -->
                        <div class="bill-to text-muted text-right">
							<p class="mb-none">
								<span ><?= TRANS('OPENING_DATE'); ?>:</span>
								<span class="value text-dark"><?= dateScreen($row['data_abertura']); ?></span>
                            </p>
						</div>
					</div>
				</div>
            </div>
            <hr>

			<div class="bill-to ">
				<div class="row">
					<div class="col-md-6">
						<div class="bill-to">
							<!-- <p class="h5 mb-xs text-dark text-semibold">To:</p> -->
							<address>
                                <?= TRANS('DESCRIPTION'); ?>:&nbsp;<span class="text-dark"><?= $row['descricao']; ?></span>
                                <br/>
                                <?= TRANS('ISSUE_TYPE'); ?>:&nbsp;<span class="text-dark"><?= $row['problema']; ?></span>
                                <br/>
                                <?= TRANS('COL_CAT_PROB'); ?>:&nbsp;<span class="text-dark"><?= $rowCatProb['probt1_desc'] . " | " . $rowCatProb['probt2_desc'] . " | " . $rowCatProb['probt3_desc']; ?></span>
                                <br/>
                                
							</address>
						</div>
					</div>
					<div class="col-md-6">
						<!-- <div class="bill-data text-right"> -->
                        <div class="bill-to text-right text-muted">
							<p class="mb-none">
                                <?= TRANS('COL_UNIT'); ?>:&nbsp;<span class="text-dark"><?= $row['unidade']; ?></span>
                                <br/>
                                <?= TRANS('FIELD_TAG_EQUIP'); ?>:&nbsp;<span class="text-dark"><?= $row['etiqueta']; ?></span>
                            </p>
						</div>
					</div>
				</div>
            </div>
            
		
			<div class="table-responsive">
				<table class="table invoice-items">
					<thead>
						<tr class="h4 text-dark">
							<th id="cell-desc"     class="text-semibold"><?= TRANS('TICKET_ENTRY'); ?></th>
							<th id="cell-type"   class="text-semibold"><?= TRANS('COL_TYPE'); ?></th>
							<th id="cell-id"   class="text-semibold"><?= TRANS('DATE'); ?></th>
							<th id="cell-author"  class=" text-semibold"><?= TRANS('AUTHOR'); ?></th>
						</tr>
					</thead>
					<tbody>

                    <?php
                    foreach ($resAssentamentos->fetchall() as $rowEntries) {
                        ?>
                        <tr>
							<td><?= $rowEntries['assentamento']; ?></td>
							<td><?= getEntryType($rowEntries['tipo_assentamento']); ?></td>
							<td><?= dateScreen($rowEntries['data']); ?></td>
							<td><?= $rowEntries['nome']; ?></td>
						</tr>
                        <?php
                    }
                    ?>
					</tbody>
				</table>
			</div>
        </div>
        <div class="container-fluid d-print-none">
            <div class="row justify-content-end">
                <div class="col-2"><button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_RETURN'); ?></button></div>
            </div>
        </div>

	</div>

	<script src="../../includes/components/jquery/jquery.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		$(function() {
        });
        window.print();

	</script>
</body>

</html>