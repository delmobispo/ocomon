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

		.select-checkbox {
			max-width: 30px !important;
		}

		.selected {
			background-color: #0074D9 !important;
			color: white !important;
		}
	</style>

	<title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>
	<?= $auth->showHeader(); ?>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>
	<div class="container" id="divResult"></div>

	<div class="container">
		<h5 class="my-4"><i class="fas fa-envelope text-secondary"></i>&nbsp;<?= TRANS('MAIL_SENDING'); ?>&nbsp;<span class="badge badge-secondary pt-2"><?= TRANS('NUMBER_ABBREVIATE') . "&nbsp;" . $_GET['numero']; ?></span></h5>
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

		if (!isset($_GET['numero']) && !isset($_POST)) {
			echo message('info', 'Ooops!', TRANS('MSG_ERR_NOT_EXECUTE'), '');
			exit();
		}

		if (!isset($_POST['numero'])) {
			$numero = intval($_GET['numero']);
		} else {
			$numero = intval($_POST['numero']);
		}

		if (isset($_POST['action']) && $_POST['action'] == "send") {

			// var_dump($_POST); exit;
			//AJAX

		}

		$colLabel = "col-sm-2 text-md-right font-weight-bold p-2";
		$colsDefault = " text-break p-2 bg-white"; /* border-secondary */
		$colContent = $colsDefault . " col-sm-3 col-md-3";
		$colContentLine = $colsDefault . " col-sm-9";

		$qryMailList = "SELECT * FROM mail_list ORDER BY ml_sigla";
		$resMailList = $conn->query($qryMailList);
		$hasMailList = $resMailList->rowCount();

		$qryTpl = "SELECT * FROM mail_templates ORDER BY tpl_sigla";
		$resTpl = $conn->query($qryTpl);
		$hasTpl = $resTpl->rowCount();

		$classDisabledMailList = ($hasMailList > 0 ? '' : ' disabled');
		$ariaDisabledMailList = ($hasMailList > 0 ? '' : ' true');
		$classDisabledTpl = ($hasTpl > 0 ? '' : ' disabled');
		$ariaDisabledTpl = ($hasTpl > 0 ? '' : ' true');

		// var_dump([
		// 	'classDisabledMailList' => $classDisabledMailList,
		// 	'classDisabledTpl' => $classDisabledTpl,
		// 	'$hasMailList' => $hasMailList,
		// 	'$hasTpl' => $hasTpl,
		// ]);



		?>
		<!-- ABAS -->
		<div class="row my-2 w-100">
			<!-- data-toggle="collapse"  -->
			<div class="<?= $colLabel; ?> my-auto"><span class="badge badge-success oc-cursor " data-toggle="collapse" data-target="#divListagens" data-pop="popover" data-placement="top" data-content="<?= TRANS('SHOW_HIDE_LISTS'); ?>" data-trigger="hover" id="oc_plus_minus"><i class="fas fa-plus"></i></span>
			</div>
			<div class="<?= $colContentLine; ?>">
				<ul class="nav nav-pills " id="pills-tab" role="tablist">
					<li class="nav-item" role="mailLists">
						<a class="nav-link active <?= $classDisabledMailList; ?>" id="divMailLists-tab" data-toggle="pill" href="#divMailLists" role="tab" aria-controls="divMailLists" aria-selected="true" aria-disabled="<?= $ariaDisabledMailList; ?>"><i class="fas fa-list-alt"></i>&nbsp;<?= TRANS('MNL_DIST_LISTS'); ?>&nbsp;<span class="badge badge-light"><?= $hasMailList; ?></span></a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?= $classDisabledTpl; ?>" id="divTpl-tab" data-toggle="pill" href="#divTpl" role="tab" aria-controls="divTpl" aria-selected="true" aria-disabled="<?= $ariaDisabledTpl; ?>"><i class="fas fa-clone"></i>&nbsp;<?= TRANS('MNL_MAIL_TEMPLATES'); ?>&nbsp;<span class="badge badge-light"><?= $hasTpl; ?></span></a><!-- <i class="fas fa-object-ungroup"></i> -->
					</li>
				</ul>
			</div>
		</div>
		<!-- FINAL DAS ABAS -->

		<div class="container collapse" id="divListagens">
			<!-- collapse -->
			<div class="tab-content" id="pills-tabContent">
				<?php
				/* LISTAGEM DAS MAIL LISTS */
				$mailListIdx = 0;
				if ($hasMailList) {
				?>
					<!-- show  active-->
					<div class="tab-pane active show fade " id="divMailLists" role="tabpanel" aria-labelledby="divMailLists-tab">
						<div class="row ">

							<div class="col-sm-12 border-bottom rounded p-0 bg-white " id="mailLists">
								<!-- collapse -->
								<table id="tableMailLists" class="table table-hover table-striped rounded" width="100%">
									<!-- table-responsive -->
									<thead class="text-white" style="background-color: #48606b;">
										<tr class="header">
											<th class="col_select" scope="col"><?= TRANS('SEL_SELECT'); ?></th>
											<th class="col_sigla" scope="col"><?= TRANS('COL_NAME'); ?></th>
											<th class="col_para" scope="col"><?= TRANS('MAIL_FIELD_TO'); ?></th>
											<th class="col_cc" scope="col"><?= TRANS('MAIL_FIELD_CC'); ?></th>
											<th class="col_id" scope="col"></th> <!-- hidden -->
										</tr>
									</thead>
									<tbody>
										<?php
										// $mailListIdx = 0;
										$i = 1;
										foreach ($resMailList->fetchAll() as $rowMailList) {
											$mailListIdx++;

										?>
											<tr id="mailList<?= $rowMailList['ml_cod']; ?>">
												<td class="line"></td>
												<!-- <td><input type="radio" class="rowMailList" id="mailList<?= $rowMailList['ml_cod']; ?>" name="mailList" value="<?= $rowMailList['ml_cod']; ?>"></td> -->
												<td class="line"><?= $rowMailList['ml_sigla']; ?></td>
												<td class="line"><?= $rowMailList['ml_addr_to']; ?></td>
												<td class="line"><?= $rowMailList['ml_addr_cc']; ?></td>
												<td class="line">mailList<?= $rowMailList['ml_cod']; ?></td>
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
				/* FINAL DA LISTAGEM DAS MAIL LISTS */


				/* TRECHO PARA EXIBIÇÃO DA LISTAGEM DE TEMPLATES DE E-MAILS */
				$tplIdx = 0;
				if ($hasTpl) {
				?>
					<div class="tab-pane  fade" id="divTpl" role="tabpanel" aria-labelledby="divTpl-tab">
						<div class="row my-2">

							<div class="col-sm-12 border-bottom rounded p-0 bg-white " id="files">
								<!-- collapse -->
								<table id="tableTpl" class="table  table-hover table-striped rounded" width="100%">
									<!-- table-responsive -->
									<!-- <thead class="bg-secondary text-white"> -->
									<thead class=" text-white" style="background-color: #48606b;">
										<tr>
											<th scope="col"><?= TRANS('SEL_SELECT'); ?></th>
											<th scope="col"><?= TRANS('COL_NAME'); ?></th>
											<th scope="col"><?= TRANS('SUBJECT'); ?></th>
											<th scope="col"><?= TRANS('COL_TEMPLATE'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 1;
										// $tplIdx = 0;
										foreach ($resTpl->fetchAll() as $rowTpl) {
											$tplIdx++;

										?>
											<tr>
												<td></td>
												<td><?= $rowTpl['tpl_sigla']; ?></td>
												<td><?= $rowTpl['tpl_subject']; ?></td>
												<td><?= nl2br($rowTpl['tpl_msg_html']); ?></td>
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
				/* FINAL DO TRECHO DE LISTAGEM DE TEMPLATES DE E-MAILS*/
				?>
			</div>
		</div>

		<form name="form" id="form" method="post" action="<?= $_SERVER['PHP_SELF']; ?>">

			<?= csrf_input(); ?>

			<div class="form-group row my-4">
				<label for="mailTo" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('MAIL_FIELD_TO'); ?></label>
				<div class="form-group col-md-10">
					<textarea class="form-control " id="mailTo" name="mailTo" rows="2" required></textarea>
				</div>


				<label for="mailToOthers" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('MAIL_FIELD_TO_OTHERS'); ?></label>
				<div class="form-group col-md-10">
					<textarea class="form-control " id="mailToOthers" name="mailToOthers" rows="2"></textarea>
				</div>

				<label for="mailToCopy" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('MAIL_FIELD_CC'); ?></label>
				<div class="form-group col-md-10">
					<textarea class="form-control " id="mailToCopy" name="mailToCopy" rows="2"></textarea>
				</div>

				<label for="subject" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SUBJECT'); ?></label>
				<div class="form-group col-md-10">
					<input type="text" class="form-control " id="subject" name="subject" placeholder="<?= TRANS('SUBJECT'); ?>" autocomplete="off" required />
				</div>

				<label for="message" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('MAIL_BODY_CONTENT'); ?></label>
				<div class="form-group col-md-10">
					<textarea class="form-control " id="message" name="message" rows="4" required></textarea>
				</div>

				<!-- <div class="container" id="divResult"></div> -->
				<div class="row w-100"></div>
				<div class="form-group col-md-8 d-none d-md-block">
				</div>
				<div class="form-group col-12 col-md-2 ">

					<input type="hidden" name="numero" value="<?= $numero; ?>" />
					<input type="hidden" name="action" value="send" />
					<button type="submit" id="idSend" name="submit" value="send" class="btn btn-primary btn-block"><?= TRANS('BT_SEND'); ?></button>
				</div>
				<div class="form-group col-12 col-md-2">
					<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
				</div>

			</div>
		</form>



	</div>
	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<!-- <script src="../../includes/components/jquery/jquery.initialize.min.js"></script> -->
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
	<script>
		$(function() {

			$('#idSend').on('click', function(e) {
				e.preventDefault();
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});

				$(document).ajaxStop(function() {
					loading.hide();
				});

				$("#idSend").prop("disabled", true);
				$.ajax({
					url: 'send_mail.php',
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
						$("#idSend").prop("disabled", false);
					} else {
						$('#divResult').html('');
						$('input, select, textarea').removeClass('is-invalid');
						$("#idSend").prop("disabled", false);
						var url = 'ticket_show.php?numero=<?= $numero ?>';
						$(location).attr('href', url);
					}

				});
				return false;
			});

			var tableMailLists = $('#tableMailLists').DataTable({
				paging: true,
				deferRender: true,
				columnDefs: [{
						orderable: false,
						className: 'select-checkbox',
						targets: 0
					},
					{
						targets: 'col_id',
						searchable: false,
						visible: false
					}
				],
				select: {
					style: 'os',
					selector: 'td:first-child'
				},
				order: [
					[1, 'asc']
				],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});

			var tableTpl = $('#tableTpl').DataTable({
				paging: true,
				deferRender: true,
				columnDefs: [{
					orderable: false,
					className: 'select-checkbox',
					targets: 0
				}],
				select: {
					style: 'os',
					selector: 'td:first-child'
				},
				order: [
					[1, 'asc']
				],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});


			$('#tableMailLists').on('click', 'td', function() {

				var row = tableMailLists.row(this.closest('tr')).data(); /* linha */
				// console.log('row[0]: ' + row[0]);
				/* 
				row[0]: checkbox
				row[1]: sigla
				row[2]: para
				row[3]: cópia
				row[4]: id (hidden)
				*/

				/* VER */
				// table.rows('.selected').data();

				var colIndex = $(this).index(); /* coluna */
				if (colIndex == 0) {
					$('#mailTo').val(row['2']);
					$('#mailToCopy').val(row['3']);
				}

			});

			$('#tableTpl').on('click', 'td', function() {

				var row = tableTpl.row(this.closest('tr')).data(); /* linha */

				var colIndex = $(this).index(); /* coluna */
				if (colIndex == 0) {
					$('#subject').val(row['2']);
					$('#message').val(row['3']);
				}
			});


			$('#oc_plus_minus').on('click', function() {
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

			$(function() {
				$('[data-pop="popover"]').popover()
			});

			$('.popover-dismiss').popover({
				trigger: 'focus'
			});


		});
	</script>
</body>

</html>