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
		<h4 class="my-4"><i class="fas fa-percentage text-secondary"></i>&nbsp;<?= TRANS('ADM_STATUS'); ?></h4>
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

		$query = "SELECT S.*, STC.*  FROM `status`  as S left join status_categ as STC on S.stat_cat = STC.stc_cod ";
		if (isset($_GET['cod'])) {
			$query .= " WHERE S.stat_id = " . $_GET['cod'] . " ";
		}
		$query .= " ORDER  BY S.status";
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
							<td class="line status"><?= TRANS("COL_STATUS"); ?></td>
							<td class="line dependencia"><?= TRANS("COL_DEPS"); ?></td>
							<td class="line painel"><?= TRANS("COL_PANEL"); ?></td>
							<td class="line freeze"><?= TRANS("COL_TIME_FREEZE"); ?></td>
							<td class="line editar"><?= TRANS("BT_ALTER"); ?></td>
							<td class="line remover"><?= TRANS("BT_REMOVE"); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php
						
						foreach ($resultado->fetchall() as $row) {
							$painel = "";
							if ($row['stat_painel'] == 1) $painel = TRANS('PANEL_UPPER');
							elseif ($row['stat_painel'] == 2) $painel = TRANS('PANEL_MAIN');
							elseif ($row['stat_painel'] == 3) $painel = TRANS('HIDDEN_PANEL');

							?>
							<tr>
								<td class="line"><?= $row['status']; ?></td>
								<td class="line"><?= $row['stc_desc']; ?></td>
								<td class="line"><?= $painel; ?></td>
								<td class="line"><?= transbool($row['stat_time_freeze']); ?></td>
								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['stat_id']; ?>')"><?= TRANS('BT_EDIT'); ?></button></td>
								<td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['stat_id']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
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

					<label for="status" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_STATUS'); ?></label>
						<div class="form-group col-md-10">
							<input type="text" class="form-control" id="status" name="status" required placeholder="Informe o nome do status" />
							<div class="invalid-feedback">
								<?= TRANS('MANDATORY_FIELD'); ?>
							</div>
						</div>

						<label for="categoria" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_DEPS'); ?></label>
						<div class="form-group col-md-10">
							<select class="form-control" id="categoria" name="categoria" required>
								<?php
								$sql = "select * from status_categ order by stc_desc";
								$exec_sql = $conn->query($sql);
								foreach ($exec_sql->fetchAll() as $rowCateg) {
									print "<option value=" . $rowCateg['stc_cod'] . ">" . $rowCateg['stc_desc'] . "</option>";
								}
								?>
							</select>
						</div>

						<label for="painel" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_PANEL'); ?></label>
						<div class="form-group col-md-10">
							<select class="form-control" id="painel" name="painel" required>
								<?php
								print "<option value='' selected>" . TRANS('SEL_PANEL') . "</option>";
								print "<option value='1'>" . TRANS('PANEL_UPPER') . "</option>";
								print "<option value='2'>" . TRANS('PANEL_MAIN') . "</option>";
								print "<option value='3'>" . TRANS('HIDDEN_PANEL') . "</option>";
								?>
							</select>
							<div class="invalid-feedback">
								<?= TRANS('MANDATORY_FIELD'); ?>
							</div>
						</div>

						

						<label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('COL_TIME_FREEZE'); ?>"><?= TRANS('COL_TIME_FREEZE'); ?></label>
						<div class="form-group col-md-4 ">
							<div class="switch-field">
								<?php
								$yesChecked = "";
								$noChecked = "checked";
								?>
								<input type="radio" id="time_freeze" name="time_freeze" value="yes" <?= $yesChecked; ?> />
								<label for="time_freeze"><?= TRANS('YES'); ?></label>
								<input type="radio" id="time_freeze_no" name="time_freeze" value="no" <?= $noChecked; ?> />
								<label for="time_freeze_no"><?= TRANS('NOT'); ?></label>
							</div>
						</div>



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
			//These are hard status, it should not have its codes changed
			if ($row['stat_id'] == 1 || $row['stat_id'] == 2 || $row['stat_id'] == 4) { 
				$STATUS = "disabled";
			} else {
				$STATUS = "";
			}
		?>
			<h6><?= TRANS('BT_EDIT'); ?></h6>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>

				<div class="form-group row my-4">



					<label for="status" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_STATUS'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control" id="status" name="status" value="<?= $row['status']; ?>" required />
						<div class="invalid-feedback">
							<?= TRANS('PROFILE_NAME_IS_MANDATORY'); ?>
						</div>
					</div>

					<label for="categoria" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_DEPS'); ?></label>
					<div class="form-group col-md-10">
						<select class="form-control" id="categoria" name="categoria" <?= $STATUS; ?>>
							<?php
							$sql = "select * from status_categ order by stc_desc";
							$exec_sql = $conn->query($sql);
							// print "<option value=''>" . TRANS('SEL_DEPS') . "</option>";
							foreach ($exec_sql->fetchAll() as $rowCateg) {
								print "<option value=" . $rowCateg['stc_cod'] . " ";
								if ($rowCateg['stc_cod'] == $row['stat_cat']) {
									print " selected";
								}
								print ">" . $rowCateg['stc_desc'] . "</option>";
							}
							?>
						</select>
					</div>

					<label for="painel" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_PANEL'); ?></label>
					<div class="form-group col-md-10">
						<select class="form-control" id="painel" name="painel" <?= $STATUS; ?>>
							<?php
							// print "<option value=''>" . TRANS('SEL_PANEL') . "</option>";
							print "<option value='1' ";
							if ($row['stat_painel'] == 1) print "selected";
							print ">" . TRANS('PANEL_UPPER') . "</option>";
							print "<option value='2' ";
							if ($row['stat_painel'] == 2) print "selected";
							print ">" . TRANS('PANEL_MAIN') . "</option>";
							print "<option value='3' ";
							if ($row['stat_painel'] == 3) print "selected";
							print ">" . TRANS('HIDDEN_PANEL') . "</option>";
							?>
						</select>
					</div>

					<label class="col-md-2 col-form-label text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('COL_TIME_FREEZE'); ?>"><?= firstLetterUp(TRANS('COL_TIME_FREEZE')); ?></label>
					<div class="form-group col-md-4 ">
						<div class="switch-field">
							<?php
							$yesChecked = ($row['stat_time_freeze'] == 1 ? "checked" : "");
							$noChecked = (!($row['stat_time_freeze'] == 1) ? "checked" : "");
							?>
							<input type="radio" id="time_freeze" name="time_freeze" value="yes" <?= $yesChecked; ?> />
							<label for="time_freeze"><?= TRANS('YES'); ?></label>
							<input type="radio" id="time_freeze_no" name="time_freeze" value="no" <?= $noChecked; ?> />
							<label for="time_freeze_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>


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
					url: './status_process.php',
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
				url: './status_process.php',
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