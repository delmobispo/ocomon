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
		<h4 class="my-4"><i class="fas fa-chalkboard-teacher text-secondary"></i>&nbsp;<?= TRANS('MNL_SCREEN_PROFILE'); ?></h4>
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

		$qrymsgdefault = $QRY["useropencall"];
		$execqrydefault = $conn->query($qrymsgdefault);
		$rowmsgdefault = $execqrydefault->fetch();


		$query = "SELECT c.*, a.*, b.sistema as ownarea, b.sis_id as ownarea_cod 
					FROM configusercall as c, sistemas as a, sistemas as b 
					WHERE c.conf_opentoarea = a.sis_id and c.conf_ownarea = b.sis_id and c.conf_cod <> 1"; //codigo 1 é reservado para as definicoes globais

		if (isset($_GET['cod'])) {
			$query .= " AND c.conf_cod = '" . $_GET['cod'] . "' ";
		}
		$query .= " ORDER BY conf_name";
		$resultado = $conn->query($query);
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
							<td class="line sigla"><?= TRANS('SCREEN_PROFILE_NAME'); ?></td>
							<td class="line sigla"><?= TRANS('DESTINY_AREA'); ?></td>
							<td class="line sigla"><?= TRANS('APLIED_TO'); ?></td>
							<td class="line editar"><?= TRANS('BT_EDIT'); ?></td>
							<td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php

						foreach ($resultado->fetchall() as $row) {

							$listAreasIn = "";
							$sqlIn = "SELECT * FROM sistemas where sis_screen='" . $row['conf_cod'] . "'";
							$resIn = $conn->query($sqlIn);
							foreach ($resIn->fetchall() as $rowIn) {
								if (strlen($listAreasIn) > 0)
									$listAreasIn .= ", ";
								$listAreasIn .= $rowIn['sistema'];
							}

						?>
							<tr>
								<td class="line"><?= $row['conf_name']; ?></td>
								<td class="line"><?= $row['sistema']; ?></td>
								<td class="line"><?= $listAreasIn; ?></td>
								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['conf_cod']; ?>&cellStyle=true')"><?= TRANS('BT_EDIT'); ?></button></td>
								<td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['conf_cod']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
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

					<label for="profile_name" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('SCREEN_PROFILE_NAME'); ?></label>
					<div class="form-group col-md-9">
						<input type="text" class="form-control " id="profile_name" name="profile_name" required />
					</div>

					<label for="area_to" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_AREA_USER_OPENTO'); ?></label>
					<div class="form-group col-md-9">
						<select class="form-control" name="area_to" id="area_to">
							<?php
							$sql = "SELECT * FROM sistemas where sis_atende = 1 AND sis_status = 1 ORDER BY sistema";
							$res = $conn->query($sql);
							foreach ($res->fetchall() as $row) {
							?>
								<option value='<?= $row['sis_id']; ?>'><?= $row['sistema']; ?></option>
							<?php
							}
							?>
						</select>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_ALLOW_USER_OPEN'); ?></label>
					<div class="form-group col-md-9 switch-field">
						<input type="radio" id="allow_user_open" name="allow_user_open" value="yes" checked />
						<label for="allow_user_open"><?= TRANS('YES'); ?></label>
						<input type="radio" id="allow_user_open_no" name="allow_user_open" value="no" />
						<label for="allow_user_open_no"><?= TRANS('NOT'); ?></label>
					</div>


					<div class="form-group col-md-12">
						<h6 class="my-4"><?= TRANS('AVAILABLE_FIELDS_IN_OPENING_SCREEN'); ?></h6>
					</div>


					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_AREA'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_area" name="field_area" value="yes" checked />
						<label for="field_area"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_area_no" name="field_area" value="no" />
						<label for="field_area_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_TYPE_OF_PROBLEM'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_issue" name="field_issue" value="yes" checked />
						<label for="field_issue"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_issue_no" name="field_issue" value="no" />
						<label for="field_issue_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_DESCRIPTION'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_description" name="field_description" value="yes" checked />
						<label for="field_description"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_description_no" name="field_description" value="no" disabled />
						<label for="field_description_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_UNIT'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_unit" name="field_unit" value="yes" checked />
						<label for="field_unit"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_unit_no" name="field_unit" value="no" />
						<label for="field_unit_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_ASSET_TAG'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_tag_number" name="field_tag_number" value="yes" checked />
						<label for="field_tag_number"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_tag_number_no" name="field_tag_number" value="no" />
						<label for="field_tag_number_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_LNK_TAG'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_tag_check" name="field_tag_check" value="yes" checked />
						<label for="field_tag_check"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_tag_check_no" name="field_tag_check" value="no" />
						<label for="field_tag_check_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_LNK_HIST'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_tag_tickets" name="field_tag_tickets" value="yes" checked />
						<label for="field_tag_tickets"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_tag_tickets_no" name="field_tag_tickets" value="no" />
						<label for="field_tag_tickets_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_CONTACT'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_contact" name="field_contact" value="yes" checked />
						<label for="field_contact"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_contact_no" name="field_contact" value="no" />
						<label for="field_contact_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_CONTACT_EMAIL'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_contact_email" name="field_contact_email" value="yes" checked />
						<label for="field_contact_email"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_contact_email_no" name="field_contact_email" value="no" />
						<label for="field_contact_email_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_PHONE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_phone" name="field_phone" value="yes" checked />
						<label for="field_phone"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_phone_no" name="field_phone" value="no" />
						<label for="field_phone_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_DEPARTMENT'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_department" name="field_department" value="yes" checked />
						<label for="field_department"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_department_no" name="field_department" value="no" />
						<label for="field_department_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('BUTTON_LOAD_DEPARTMENT'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_load_department" name="field_load_department" value="yes" checked />
						<label for="field_load_department"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_load_department_no" name="field_load_department" value="no" />
						<label for="field_load_department_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_SCH_LOCAL'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_search_dep_tags" name="field_search_dep_tags" value="yes" checked />
						<label for="field_search_dep_tags"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_search_dep_tags_no" name="field_search_dep_tags" value="no" />
						<label for="field_search_dep_tags_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_OPERATOR'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_operator" name="field_operator" value="yes" checked />
						<label for="field_operator"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_operator_no" name="field_operator" value="no" />
						<label for="field_operator_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_DATE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_date" name="field_date" value="yes" checked />
						<label for="field_date"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_date_no" name="field_date" value="no" />
						<label for="field_date_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_SCHEDULE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_schedule" name="field_schedule" value="yes" checked />
						<label for="field_schedule"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_schedule_no" name="field_schedule" value="no" />
						<label for="field_schedule_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_FORWARD'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_forward" name="field_forward" value="yes" checked />
						<label for="field_forward"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_forward_no" name="field_forward" value="no" />
						<label for="field_forward_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_STATUS'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_status" name="field_status" value="yes" checked />
						<label for="field_status"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_status_no" name="field_status" value="no" />
						<label for="field_status_no"><?= TRANS('NOT'); ?></label>
					</div>

					<!-- <label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_REPLICATE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_replicate" name="field_replicate" value="yes" checked />
						<label for="field_replicate"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_replicate_no" name="field_replicate" value="no" />
						<label for="field_replicate_no"><?= TRANS('NOT'); ?></label>	
					</div> -->

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_ATTACH_FILE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_attach_file" name="field_attach_file" value="yes" checked />
						<label for="field_attach_file"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_attach_file_no" name="field_attach_file" value="no" />
						<label for="field_attach_file_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_PRIORITY'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_priority" name="field_priority" value="yes" checked />
						<label for="field_priority"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_priority_no" name="field_priority" value="no" />
						<label for="field_priority_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_SEND_EMAIL'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<input type="radio" id="field_send_mail" name="field_send_mail" value="yes" checked />
						<label for="field_send_mail"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_send_mail_no" name="field_send_mail" value="no" />
						<label for="field_send_mail_no"><?= TRANS('NOT'); ?></label>
					</div>

					<div class="w-100"></div>
					<label for="opening_message" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_FIELD_MSG'); ?></label>
					<div class="form-group col-md-9">
						<textarea class="form-control" id="opening_message" name="opening_message"><?= $rowmsgdefault['conf_scr_msg']; ?></textarea>
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
		?>
			<h6><?= TRANS('BT_EDIT'); ?></h6>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>
				<div class="form-group row my-4">


					<label for="profile_name" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('SCREEN_PROFILE_NAME'); ?></label>
					<div class="form-group col-md-9">
						<input type="text" class="form-control " id="profile_name" name="profile_name" required value="<?= $row['conf_name']; ?>" />
					</div>

					<label for="area_to" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_AREA_USER_OPENTO'); ?></label>
					<div class="form-group col-md-9">
						<select class="form-control" name="area_to" id="area_to">
							<?php
							$sql = "SELECT * FROM sistemas where sis_atende = 1 AND sis_status = 1 ORDER BY sistema";
							$res = $conn->query($sql);
							foreach ($res->fetchall() as $rowArea) {
							?>
								<option value='<?= $rowArea['sis_id']; ?>' <?= ($rowArea['sis_id'] == $row['sis_id'] ? 'selected' : ''); ?>>
									<?= $rowArea['sistema']; ?></option>
							<?php
							}
							?>
						</select>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_ALLOW_USER_OPEN'); ?></label>
					<div class="form-group col-md-9 switch-field">
						<?php
						$yesChecked = ($row['conf_user_opencall'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_user_opencall'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="allow_user_open" name="allow_user_open" value="yes" <?= $yesChecked; ?> />
						<label for="allow_user_open"><?= TRANS('YES'); ?></label>
						<input type="radio" id="allow_user_open_no" name="allow_user_open" value="no" <?= $noChecked; ?> />
						<label for="allow_user_open_no"><?= TRANS('NOT'); ?></label>
					</div>


					<div class="form-group col-md-12">
						<h6 class="my-4"><?= TRANS('AVAILABLE_FIELDS_IN_OPENING_SCREEN'); ?></h6>
					</div>


					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_AREA'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_area'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_area'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_area" name="field_area" value="yes" <?= $yesChecked; ?> />
						<label for="field_area"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_area_no" name="field_area" value="no" <?= $noChecked; ?> />
						<label for="field_area_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_TYPE_OF_PROBLEM'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_prob'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_prob'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_issue" name="field_issue" value="yes" <?= $yesChecked; ?> />
						<label for="field_issue"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_issue_no" name="field_issue" value="no" <?= $noChecked; ?> />
						<label for="field_issue_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_DESCRIPTION'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_desc'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_desc'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_description" name="field_description" value="yes" <?= $yesChecked; ?> />
						<label for="field_description"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_description_no" name="field_description" value="no" <?= $noChecked; ?>disabled />
						<label for="field_description_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_UNIT'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_unit'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_unit'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_unit" name="field_unit" value="yes" <?= $yesChecked; ?> />
						<label for="field_unit"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_unit_no" name="field_unit" value="no" <?= $noChecked; ?> />
						<label for="field_unit_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_ASSET_TAG'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_tag'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_tag'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_tag_number" name="field_tag_number" value="yes" <?= $yesChecked; ?> />
						<label for="field_tag_number"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_tag_number_no" name="field_tag_number" value="no" <?= $noChecked; ?> />
						<label for="field_tag_number_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_LNK_TAG'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_chktag'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_chktag'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_tag_check" name="field_tag_check" value="yes" <?= $yesChecked; ?> />
						<label for="field_tag_check"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_tag_check_no" name="field_tag_check" value="no" <?= $noChecked; ?> />
						<label for="field_tag_check_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_LNK_HIST'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_chkhist'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_chkhist'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_tag_tickets" name="field_tag_tickets" value="yes" <?= $yesChecked; ?> />
						<label for="field_tag_tickets"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_tag_tickets_no" name="field_tag_tickets" value="no" <?= $noChecked; ?> />
						<label for="field_tag_tickets_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_CONTACT'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_contact'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_contact'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_contact" name="field_contact" value="yes" <?= $yesChecked; ?> />
						<label for="field_contact"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_contact_no" name="field_contact" value="no" <?= $noChecked; ?> />
						<label for="field_contact_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_CONTACT_EMAIL'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_contact_email'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_contact_email'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_contact_email" name="field_contact_email" value="yes" <?= $yesChecked; ?> />
						<label for="field_contact_email"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_contact_email_no" name="field_contact_email" value="no" <?= $noChecked; ?> />
						<label for="field_contact_email_no"><?= TRANS('NOT'); ?></label>
					</div>


					

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_PHONE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_fone'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_fone'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_phone" name="field_phone" value="yes" <?= $yesChecked; ?> />
						<label for="field_phone"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_phone_no" name="field_phone" value="no" <?= $noChecked; ?> />
						<label for="field_phone_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_DEPARTMENT'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_local'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_local'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_department" name="field_department" value="yes" <?= $yesChecked; ?> />
						<label for="field_department"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_department_no" name="field_department" value="no" <?= $noChecked; ?> />
						<label for="field_department_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('BUTTON_LOAD_DEPARTMENT'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_btloadlocal'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_btloadlocal'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_load_department" name="field_load_department" value="yes" <?= $yesChecked; ?> />
						<label for="field_load_department"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_load_department_no" name="field_load_department" value="no" <?= $noChecked; ?> />
						<label for="field_load_department_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_SCH_LOCAL'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_searchbylocal'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_searchbylocal'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_search_dep_tags" name="field_search_dep_tags" value="yes" <?= $yesChecked; ?> />
						<label for="field_search_dep_tags"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_search_dep_tags_no" name="field_search_dep_tags" value="no" <?= $noChecked; ?> />
						<label for="field_search_dep_tags_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_OPERATOR'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_operator'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_operator'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_operator" name="field_operator" value="yes" <?= $yesChecked; ?> />
						<label for="field_operator"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_operator_no" name="field_operator" value="no" <?= $noChecked; ?> />
						<label for="field_operator_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_DATE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_date'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_date'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_date" name="field_date" value="yes" <?= $yesChecked; ?> />
						<label for="field_date"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_date_no" name="field_date" value="no" <?= $noChecked; ?> />
						<label for="field_date_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_SCHEDULE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_schedule'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_schedule'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_schedule" name="field_schedule" value="yes" <?= $yesChecked; ?> />
						<label for="field_schedule"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_schedule_no" name="field_schedule" value="no" <?= $noChecked; ?> />
						<label for="field_schedule_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_FORWARD'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_foward'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_foward'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_forward" name="field_forward" value="yes" <?= $yesChecked; ?> />
						<label for="field_forward"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_forward_no" name="field_forward" value="no" <?= $noChecked; ?> />
						<label for="field_forward_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_STATUS'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_status'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_status'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_status" name="field_status" value="yes" <?= $yesChecked; ?> />
						<label for="field_status"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_status_no" name="field_status" value="no" <?= $noChecked; ?> />
						<label for="field_status_no"><?= TRANS('NOT'); ?></label>
					</div>

					<!-- <label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_REPLICATE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						// $yesChecked = ($row['conf_scr_replicate'] == 1 ? "checked" : "");
						// $noChecked = ($row['conf_scr_replicate'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_replicate" name="field_replicate" value="yes" <?= $yesChecked; ?> />
						<label for="field_replicate"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_replicate_no" name="field_replicate" value="no" <?= $noChecked; ?>/>
						<label for="field_replicate_no"><?= TRANS('NOT'); ?></label>	
					</div> -->

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_ATTACH_FILE'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_upload'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_upload'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_attach_file" name="field_attach_file" value="yes" <?= $yesChecked; ?> />
						<label for="field_attach_file"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_attach_file_no" name="field_attach_file" value="no" <?= $noChecked; ?> />
						<label for="field_attach_file_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_PRIORITY'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_prior'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_prior'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_priority" name="field_priority" value="yes" <?= $yesChecked; ?> />
						<label for="field_priority"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_priority_no" name="field_priority" value="no" <?= $noChecked; ?> />
						<label for="field_priority_no"><?= TRANS('NOT'); ?></label>
					</div>

					<label class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FIELD_SEND_EMAIL'); ?></label>
					<div class="form-group col-md-3 switch-field">
						<?php
						$yesChecked = ($row['conf_scr_mail'] == 1 ? "checked" : "");
						$noChecked = ($row['conf_scr_mail'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="field_send_mail" name="field_send_mail" value="yes" checked />
						<label for="field_send_mail"><?= TRANS('YES'); ?></label>
						<input type="radio" id="field_send_mail_no" name="field_send_mail" value="no" <?= $noChecked; ?> />
						<label for="field_send_mail_no"><?= TRANS('NOT'); ?></label>
					</div>

					<div class="w-100"></div>
					<label for="opening_message" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_FIELD_MSG'); ?></label>
					<div class="form-group col-md-9">
						<textarea class="form-control" id="opening_message" name="opening_message"><?= $row['conf_scr_msg']; ?></textarea>
					</div>



					<input type="hidden" name="cod" value="<?= $_GET['cod']; ?>">
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
	<script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
	<script type="text/javascript">
		$(function() {

			$('#table_lists').DataTable({
				paging: true,
				deferRender: true,
				columnDefs: [{
					searchable: false,
					orderable: false,
					targets: ['editar', 'remover']
				}],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});

			if (!$('#field_tag_number').is(':checked')) {
				$('#field_tag_check').prop('disabled', true).prop('checked', false);
				$('#field_tag_check_no').prop('disabled', true).prop('checked', true);
				$('#field_tag_tickets').prop('disabled', true).prop('checked', false);
				$('#field_tag_tickets_no').prop('disabled', true).prop('checked', true);
			}

			if (!$('#field_department').is(':checked')) {
				$('#field_load_department').prop('disabled', true).prop('checked', false);
				$('#field_load_department_no').prop('disabled', true).prop('checked', true);
				$('#field_search_dep_tags').prop('disabled', true).prop('checked', false);
				$('#field_search_dep_tags_no').prop('disabled', true).prop('checked', true);
			}


			$('[name="field_tag_number"]').on('change', function() {
				if ($(this).val() == "no") {
					$('#field_tag_check').prop('checked', false).prop('disabled', true);
					$('#field_tag_check_no').prop('checked', true).prop('disabled', true);
					$('#field_tag_tickets').prop('checked', false).prop('disabled', true);
					$('#field_tag_tickets_no').prop('checked', true).prop('disabled', true);
				} else {
					$('#field_tag_check').prop('disabled', false);
					$('#field_tag_check_no').prop('disabled', false);
					$('#field_tag_tickets').prop('disabled', false);
					$('#field_tag_tickets_no').prop('disabled', false);
				}
			});

			$('[name="field_department"]').on('change', function() {
				if ($(this).val() == "no") {
					$('#field_load_department').prop('checked', false).prop('disabled', true);
					$('#field_load_department_no').prop('checked', true).prop('disabled', true);
					$('#field_search_dep_tags').prop('checked', false).prop('disabled', true);
					$('#field_search_dep_tags_no').prop('checked', true).prop('disabled', true);
				} else {
					$('#field_load_department').prop('disabled', false);
					$('#field_load_department_no').prop('disabled', false);
					$('#field_search_dep_tags').prop('disabled', false);
					$('#field_search_dep_tags_no').prop('disabled', false);
				}
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

				$.ajax({
					url: './screen_profile_process.php',
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
				url: './screen_profile_process.php',
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