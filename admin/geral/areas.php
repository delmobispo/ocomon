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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);

$_SESSION['s_page_admin'] = $_SERVER['PHP_SELF'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/switch_radio.css" />
	<!-- <link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" /> -->
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
		<h4 class="my-4"><i class="fas fa-headset text-secondary"></i>&nbsp;<?= TRANS('SERVICE_AREAS'); ?></h4>
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

		$query = "SELECT s.*, c.*, w.* FROM sistemas AS s 
					LEFT JOIN configusercall as c on s.sis_screen = c.conf_cod 
					LEFT JOIN worktime_profiles as w on w.id = s.sis_wt_profile ";
		if (isset($_GET['cod'])) {
			$query .= "WHERE sis_id = " . $_GET['cod'] . " ";
		}
		$query .= " ORDER  BY sistema";
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
							<td class="line area"><?= TRANS('AREA'); ?></td>
							<td class="line subject"><?= TRANS('PROCESS_TICKETS'); ?></td>
							<td class="line subject"><?= TRANS('ACCESS_MODULES'); ?></td>
							<td class="line email"><?= TRANS('COL_EMAIL'); ?></td>
							<td class="line screen_profile"><?= TRANS('COL_SCREEN_PROFILE'); ?></td>
							<td class="line status"><?= TRANS('COL_STATUS'); ?></td>
							<td class="line wc_profile"><?= TRANS('WORKTIME_PROFILE'); ?></td>
							<td class="line editar"><?= TRANS('BT_EDIT'); ?></td>
							<td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php
						
						foreach ($resultado->fetchall() as $row) {
							$lstatus = ($row['sis_status'] == 0 ? TRANS('INACTIVE') : TRANS('ACTIVE'));
							$textScreen = ($row['conf_name'] == "" ? TRANS('ALL_FIELDS_SCREEN') : $row['conf_name']);
							$modulesText = "";
							$modules = [];
							$modules[] = (getModuleAccess($conn, 1, $row['sis_id']) ? TRANS('MOD_TICKETS') : "");
							$modules[] = (getModuleAccess($conn, 2, $row['sis_id']) ? TRANS('MOD_INVENTORY') : "");
							foreach ($modules as $mod) {
								if (strlen($modulesText) > 0 && strlen($mod) > 0) $modulesText .= ", ";
								$modulesText .= $mod;
							}

							?>
							<tr>
								<td class="line"><?= $row['sistema']; ?></td>
								<td class="line"><?= transbool($row['sis_atende']); ?></td>
								<td class="line"><?= $modulesText; ?></td>
								<td class="line"><?= $row['sis_email']; ?></td>
								<td class="line"><?= NVL($textScreen); ?></td>
								<td class="line"><?= $lstatus; ?></td>
								<td class="line"><?= $row['name']; ?></td>
								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['sis_id']; ?>&cellStyle=true')"><?= TRANS('BT_EDIT'); ?></button></td>
								<td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['sis_id']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
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


					<label for="area" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('AREA'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control" id="area" name="area" required placeholder="<?= TRANS('PLACEHOLDER_AREA_NAME'); ?>" />
						<div class="invalid-feedback">
							<?= TRANS('MANDATORY_FIELD'); ?>
						</div>
					</div>

					<label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('UNCHECK_IF_ENDUSER_AREA'); ?>"><?= firstLetterUp(TRANS('PROCESS_TICKETS')); ?></label>
					<div class="form-group col-md-4 ">
						<div class="switch-field">
							<?php
							$yesChecked = "checked";
							$noChecked = "";
							?>
							<input type="radio" id="process_tickets" name="process_tickets" value="yes" <?= $yesChecked; ?> />
							<label for="process_tickets"><?= TRANS('YES'); ?></label>
							<input type="radio" id="process_tickets_no" name="process_tickets" value="no" <?= $noChecked; ?> />
							<label for="process_tickets_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>


					<label for="email" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_EMAIL'); ?></label>
					<div class="form-group col-md-4">
						<input type="email" class="form-control" id="email" name="email" required placeholder="<?= TRANS('PLACEHOLDER_AREA_EMAIL'); ?>" />
						<div class="invalid-feedback">
							<?= TRANS('MANDATORY_FIELD'); ?>
						</div>
					</div>

					<label for="screen_profile" class="col-md-2 col-form-label text-md-right"><?= TRANS('SCREEN_NAME'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control " id="screen_profile" name="screen_profile" >
							<option value=""><?= TRANS('SEL_SCREEN'); ?></option>
							<?php
							$sql = "select * from configusercall order by conf_name";
							$commit = $conn->query($sql);
							foreach ($commit->fetchAll() as $row) {
								print "<option value=" . $row['conf_cod'] . ">" . $row["conf_name"] . "</option>";
							}
							?>
						</select>
					</div>

					<label for="status" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_STATUS'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control " id="status" name="status" required>
							<option value="1"><?= TRANS('ACTIVE'); ?></option>
							<?php
							print "<option value=0>" . TRANS('INACTIVE') . "</option>";
							?>
						</select>
					</div>

					<label for="wt_profile" class="col-md-2 col-form-label text-md-right"><?= TRANS('WORKTIME_PROFILE'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control " id="wt_profile" name="wt_profile" required>
							<?php
							$sql = "SELECT id, name, is_default FROM worktime_profiles ORDER BY name ";
							$result = $conn->query($sql);
							foreach ($result->fetchAll() as $rowWT) {
								print "<option value = '" . $rowWT['id'] . "' ";
								if ($rowWT['is_default'] == 1) print " selected";
								print ">" . $rowWT['name'] . "</option>";
							}
							?>
						</select>
					</div>

					<h6 class="w-100 mt-5 mb-4 ml-5 border-top p-4"><i class="fas fa-project-diagram text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('ACCESS_MODULES')); ?></h6>
					<label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('MOD_TICKETS'); ?>"><?= TRANS('MOD_TICKETS'); ?></label>
					<div class="form-group col-md-4 ">
						<div class="switch-field">
							<?php
							$yesChecked = "checked";
							$noChecked = "";
							?>
							<input type="radio" id="mod_tickets" name="mod_tickets" value="yes" <?= $yesChecked; ?> />
							<label for="mod_tickets"><?= TRANS('YES'); ?></label>
							<input type="radio" id="mod_tickets_no" name="mod_tickets" value="no" <?= $noChecked; ?> />
							<label for="mod_tickets_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('MOD_INVENTORY'); ?>"><?= TRANS('MOD_INVENTORY'); ?></label>
					<div class="form-group col-md-4 ">
						<div class="switch-field">
							<?php
							$yesChecked = "checked";
							$noChecked = "";
							?>
							<input type="radio" id="mod_inventory" name="mod_inventory" value="yes" <?= $yesChecked; ?> />
							<label for="mod_inventory"><?= TRANS('YES'); ?></label>
							<input type="radio" id="mod_inventory_no" name="mod_inventory" value="no" <?= $noChecked; ?> />
							<label for="mod_inventory_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>


					</div>
					<div class="form-group row my-4 " id="div_send_receive_areas"></div>
					<div class="form-group row my-4 ">


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


					<label for="area" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('AREA'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control" id="area" name="area" value="<?= $row['sistema']; ?>" placeholder="Informe o nome da área" />
						<div class="invalid-feedback">
							<?= TRANS('MANDATORY_FIELD'); ?>
						</div>
					</div>


					<label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('UNCHECK_IF_ENDUSER_AREA'); ?>"><?= firstLetterUp(TRANS('PROCESS_TICKETS')); ?></label>
					<div class="form-group col-md-4 ">
						<div class="switch-field">
							<?php
							$yesChecked = ($row['sis_atende'] == 1 ? "checked" : "");
							$noChecked = (!($row['sis_atende'] == 1) ? "checked" : "");
							?>
							<input type="radio" id="process_tickets" name="process_tickets" value="yes" <?= $yesChecked; ?> />
							<label for="process_tickets"><?= TRANS('YES'); ?></label>
							<input type="radio" id="process_tickets_no" name="process_tickets" value="no" <?= $noChecked; ?> />
							<label for="process_tickets_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>


					<label for="email" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_EMAIL'); ?></label>
					<div class="form-group col-md-4">
						<input type="email" class="form-control" id="email" name="email" value="<?= $row['sis_email']; ?>" placeholder="Informe o e-mail da área" />
						<div class="invalid-feedback">
							<?= TRANS('MANDATORY_FIELD'); ?>
						</div>
					</div>

					<label for="screen_profile" class="col-md-2 col-form-label text-md-right"><?= TRANS('SCREEN_NAME'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control " id="screen_profile" name="screen_profile" >
							<?php

							print "<option value=''>" . TRANS('SEL_SCREEN') . "</option>";
							$sql2 = "select * from configusercall order by conf_name";
							$commit2 = $conn->query($sql2);
							foreach ($commit2->fetchall() as $rowB) {
								print "<option value=" . $rowB["conf_cod"] . "";

								/* Para não passar valor nulo para o getScreenInfo */
								if (isset($row['sis_screen'])) {
									if ($rowB['conf_cod'] == getScreenInfo($conn, $row['sis_screen'])['conf_cod']) {
										print " selected";
									}
								}
								print ">" . $rowB["conf_name"] . "</option>";
							} 
							?>
						</select>

					</div>

					<label for="status" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_STATUS'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control " id="status" name="status" required>
							<option value="1" <?= ($row['sis_status'] == 1) ? ' selected' : ''; ?>><?= TRANS('ACTIVE'); ?></option>
							<option value="0" <?= ($row['sis_status'] == 0) ? ' selected' : ''; ?>><?= TRANS('INACTIVE'); ?></option>
						</select>
					</div>

					<label for="wt_profile" class="col-md-2 col-form-label text-md-right"><?= TRANS('WORKTIME_PROFILE'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control " id="wt_profile" name="wt_profile" required>
							<?php
							$sql = "SELECT id, name, is_default FROM worktime_profiles ORDER BY name ";
							$result = $conn->query($sql);
							foreach ($result->fetchall() as $rowWT) {
								?>
								<option value="<?= $rowWT['id']; ?>" <?= ($rowWT['id'] == $row['sis_wt_profile'] ? 'selected' : ''); ?>><?= $rowWT['name']; ?></option>
								<?php
							}
							?>
						</select>
					</div>


					<h6 class="w-100 mt-5 mb-4 ml-5 border-top p-4"><i class="fa fa-project-diagram text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('ACCESS_MODULES')); ?></h6>
					<label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('MOD_TICKETS'); ?>"><?= TRANS('MOD_TICKETS'); ?></label>
					<div class="form-group col-md-4 ">
						<div class="switch-field">
							<?php
							$yesChecked = (getModuleAccess($conn, 1, $_GET['cod']) ? "checked" : "");
							$noChecked = (!(getModuleAccess($conn, 1, $_GET['cod'])) ? "checked" : "");
							?>
							<input type="radio" id="mod_tickets" name="mod_tickets" value="yes" <?= $yesChecked; ?> />
							<label for="mod_tickets"><?= TRANS('YES'); ?></label>
							<input type="radio" id="mod_tickets_no" name="mod_tickets" value="no" <?= $noChecked; ?> />
							<label for="mod_tickets_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('MOD_INVENTORY'); ?>"><?= TRANS('MOD_INVENTORY'); ?></label>
					<div class="form-group col-md-4 ">
						<div class="switch-field">
							<?php
							$yesChecked = (getModuleAccess($conn, 2, $_GET['cod']) ? "checked" : "");
							$noChecked = (!(getModuleAccess($conn, 2, $_GET['cod'])) ? "checked" : "");
							?>
							<input type="radio" id="mod_inventory" name="mod_inventory" value="yes" <?= $yesChecked; ?> />
							<label for="mod_inventory"><?= TRANS('YES'); ?></label>
							<input type="radio" id="mod_inventory_no" name="mod_inventory" value="no" <?= $noChecked; ?> />
							<label for="mod_inventory_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>
				
				</div>
				<div class="form-group row my-4 " id="div_send_receive_areas"></div>
				<div class="form-group row my-4 ">


					<input type="hidden" name="cod" id="cod" value="<?= $_GET['cod']; ?>">
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
	<!-- <script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script> -->
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
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

			if (!$('#process_tickets').is(':checked')) {
				$('#mod_inventory').prop('disabled', true).prop('checked', false);
				$('#mod_inventory_no').prop('disabled', true).prop('checked', true);
			}

			$('[name="process_tickets"]').on('change', function() {
				if ($(this).val() == "no") {
					$('#mod_inventory').prop('checked', false).prop('disabled', true);
					$('#mod_inventory_no').prop('checked', true).prop('disabled', true);

					$('.areaFrom_yes').prop('checked', false).prop('disabled', true);
					$('.areaFrom_no').prop('checked', true).prop('disabled', true);
				} else {
					$('#mod_inventory').prop('disabled', false);
					$('#mod_inventory_no').prop('disabled', false);

					$('.areaFrom_yes').prop('disabled', false);
					$('.areaFrom_no').prop('disabled', false);
				}
			});



			$.ajax({
                url: 'get_send_receive_areas.php',
                type: 'POST',
                data: {
					'cod': (typeof $('#cod') !== 'undefined' ? $('#cod').val() : ""),
					'action': $('#action').val()
                },
                success: function(data) {
                    $('#div_send_receive_areas').html(data);
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
					url: './areas_process.php',
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
				url: './areas_process.php',
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