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

		.cke_textarea_inline {
			width: 100%;
			padding: 10px;
			height: 200px;
			overflow: auto;

			border: 1px solid lightgrey;
			border-radius: 4px;
			-webkit-appearance: textfield;
		}
	</style>

	<title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>
	<?= $auth->showHeader('helpconfigmsg.php'); ?>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>

	<div id="divResult"></div>


	<div class="container-fluid">
		<h4 class="my-4"><i class="fas fa-envelope-open-text text-secondary"></i>&nbsp;<?= TRANS('TTL_CONFIG_SEND_MAIL'); ?></h4>
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

		$query = "SELECT * FROM msgconfig ";
		if (isset($_GET['cod'])) {
			$query .= "WHERE msg_cod=" . $_GET['cod'] . "";
		}
		$query .= " ORDER BY msg_event";
		$resultado = $conn->query($query);
		$registros = $resultado->rowCount();

		if ((!isset($_GET['action'])) && !isset($_POST['submit'])) {

			if ($registros == 0) {
				echo message('info', '', TRANS('NO_RECORDS_FOUND'), '', '', true);
			} else {

		?>
				<table id="table_lists" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">

					<thead>
						<tr class="header">
							<td class="line event"><?= TRANS('OPT_EVENT'); ?></td>
							<td class="line from"><?= TRANS('OPT_FROM'); ?></td>
							<td class="line reply_to"><?= TRANS('OPT_REPLY_TO'); ?></td>
							<td class="line subject"><?= TRANS('SUBJECT'); ?></td>
							<td class="line msg_html"><?= TRANS('OPT_HTML_MSG'); ?></td>
							<td class="line msg_alternate"><?= TRANS('OPT_ALTERNATE_MSG'); ?></td>
							<td class="line editar"><?= TRANS('BT_EDIT'); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php

						foreach ($resultado->fetchall() as $row) {

							$texto1 = str_replace("\r", "\n", $row['msg_body']);
							$texto1 = str_replace("\n", "", $texto1);
						?>
							<tr>
								<td class="line"><?= $row['msg_event']; ?></td>
								<td class="line"><?= $row['msg_fromname']; ?></td>
								<td class="line"><?= $row['msg_replyto']; ?></td>
								<td class="line"><?= $row['msg_subject']; ?></td>
								<td class="line"><?= $texto1; ?></td>
								<td class="line"><?= $row['msg_altbody']; ?></td>
								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['msg_cod']; ?>')"><?= TRANS('BT_EDIT'); ?></button></td>
							</tr>

						<?php
						}
						?>
					</tbody>
				</table>
			<?php
			}
		} else

		if ((isset($_GET['action']) && $_GET['action'] == "edit") && empty($_POST['submit'])) {

			$row = $resultado->fetch();
			?>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>
				<div class="form-group row my-4">
					<label for="msg_from" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_EVENT'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="msg_from" name="msg_from" readonly value="<?= $row['msg_event']; ?>" required />
					</div>

					<label for="msg_from" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_FROM'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="msg_from" name="msg_from" value="<?= $row['msg_fromname']; ?>" required />
					</div>

					<label for="reply_to" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_REPLY_TO'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="reply_to" name="reply_to" value="<?= $row['msg_replyto']; ?>" required />
					</div>

					<label for="subject" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SUBJECT'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="subject" name="subject" value="<?= $row['msg_subject']; ?>" required />
					</div>

					<label for="variables" class="col-md-2 col-form-label col-form-label-sm text-md-right"></label>
					<div class="form-group col-md-10">
						<div class="accordion" id="accordionVariables">
							<div class="card">
								<div class="card-header" id="headingOne">
									<h2 class="mb-0">
										<button class="btn btn-block text-left font-weight-bold" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" onClick="blur();">
											<i class="fas fa-closed-captioning text-secondary"></i>&nbsp;<?= TRANS('OPT_ENVIRON_AVAIL'); ?>
										</button>
									</h2>
								</div>

								<div id="collapseOne" class="collapse " aria-labelledby="headingOne" data-parent="#accordionVariables">
									<div class="card-body bg-light">
										<?= nl2br(getEnvVars($conn)); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="w-100"></div>


					<label for="body_content" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_HTML_MSG'); ?></label>
					<div class="form-group col-md-10">
						<textarea name="body_content" id="body_content" class="form-control" rows="4"><?= $row['msg_body']; ?></textarea>

					</div>

					<label for="alternative_content" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_ALTERNATE_MSG'); ?></label>
					<div class="form-group col-md-10">
						<textarea name="alternative_content" id="alternative_content" class="form-control" rows="4"><?= $row['msg_altbody']; ?></textarea>
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
	<script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/ckeditor/ckeditor.js"></script>
	<script type="text/javascript">
		$(function() {

			$('#table_lists').DataTable({
				paging: true,
				deferRender: true,
				columnDefs: [{
						searchable: false,
						orderable: false,
						targets: ['editar']
					},
					{
						width: '35%',
						targets: ['msg_html']
					}
				],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});


			if (typeof($('#body_content').val()) !== 'undefined') {
				var formatBar = CKEDITOR.inline('body_content', {
					uiColor: '#CCCCCC',
					language: 'pt-br'
				});

				formatBar.on('change', function() {
					formatBar.updateElement();
				});
			}


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

				$.ajax({
					url: './configmsgs_process.php',
					method: 'POST',
					// data: $('#form').serialize() + "&htmlHiddenContent=" + formatBar.getData(),
					data: $('#form').serialize(),
					dataType: 'json',
				}).done(function(response) {

					if (!response.success) {
						$('#divResult').html(response.message);
						if (response.field_id != "")
							$('#' + response.field_id).focus();
					} else {
						var url = '<?= $_SERVER['PHP_SELF'] ?>';
						$(location).prop('href', url);
						return false;
					}
				});
				return false;
			});


			$('#bt-cancel').on('click', function() {
				var url = '<?= $_SERVER['PHP_SELF'] ?>';
				$(location).prop('href', url);
			});
		});
	</script>
</body>

</html>