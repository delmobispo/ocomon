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

$config = getConfig($conn);

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

		/* Style the CKEditor element to look like a textfield */
        .cke_textarea_inline {
            width: 100%;
            padding: 10px;
            height: 300px;
            overflow: auto;
            border: 1px solid #ced4da;
            border-radius: 4px;
            -webkit-appearance: textfield;
        }
	</style>

	<title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>

	<?php
		if (isset($_GET['action']) && $_GET['action']=='endview') {
			//
		} else {
			$auth->showHeader();
		}
	?>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>

	<div id="divResult"></div>


	<div class="container-fluid">
		<h4 class="my-4"><i class="fas fa-tasks text-secondary"></i>&nbsp;<?= TRANS('ADM_SCRIPTS'); ?></h4>
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

		$query = "SELECT 
					sr.*, prsc.*, pr.*, a.* 
					FROM scripts AS sr 
						LEFT JOIN prob_x_script as prsc on prsc.prscpt_scpt_id = sr.scpt_id 
						LEFT JOIN problemas as pr on pr.prob_id = prsc.prscpt_prob_id 
						LEFT JOIN sistemas as a on a.sis_id = pr.prob_area 
					WHERE 1 = 1
		";
		if (isset($_GET['prob'])) {
			$query .= " AND pr.prob_id = '" . $_GET['prob'] . "' ";
		}

		if (isset($_GET['cod'])) {
			$query .= " AND sr.scpt_id = '" . $_GET['cod'] . "' ";
		}

		if (isset($_GET['action']) && $_GET['action'] == 'details') {
			$query .= " AND sr.scpt_id = '" . $_GET['cod'] . "' ";
		}

		if ((!isset($_GET['action']) || $_GET['action']=='endview')) {
			$query .= " GROUP BY sr.scpt_id ";
		}

		$query .= " ORDER BY sr.scpt_nome";
		$resultado = $conn->query($query);
		$registros = $resultado->rowCount();


		/* Classes para o grid */
		$colLabel = "col-sm-2 text-md-right font-weight-bold p-2 mb-4";
		$colsDefault = "small text-break border-bottom rounded p-2 bg-white"; /* border-secondary */
		$colContent = $colsDefault . " col-sm-10 col-md-10 ";
		$colContentLine = $colsDefault . " col-sm-10";
		/* Duas colunas */
		$colLabel2 = "col-sm-2 text-md-right font-weight-bold p-2 mb-4";
		$colContent2 = $colsDefault . " col-sm-3 col-md-3";

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
							<td class="line sigla"><?= TRANS('COL_NAME'); ?></td>
							<td class="line description"><?= TRANS('DESCRIPTION'); ?></td>
							<td class="line end_user"><?= TRANS('COL_SCRIPT_ENDUSER'); ?></td>
							<td class="line issue_type"><?= TRANS('ISSUE_TYPE'); ?></td>
							<td class="line editar"><?= TRANS('BT_EDIT'); ?></td>
							<td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php

						foreach ($resultado->fetchall() as $row) {
							$qryProb = "SELECT * FROM prob_x_script 
									LEFT JOIN problemas on prob_id = prscpt_prob_id 
									WHERE 
										prscpt_scpt_id = ".$row['scpt_id']." 
										AND prscpt_prob_id = prob_id 
										GROUP BY problema 
										ORDER BY problema 
									";
							$resProb = $conn->query($qryProb);
							
							$allProbs = "";
							foreach ($resProb->fetchall() as $rowProb) {
								!empty($allProbs)?$allProbs.=",<br>":$allProbs.="";
								$allProbs.= $rowProb['problema'];
							}
							?>
							<tr>
								<td class="line"><a onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=details&cod=<?= $row['scpt_id']; ?>')"><?= $row['scpt_nome']; ?></a></td>
								<td class="line"><?= $row['scpt_desc']; ?></td>
								<td class="line"><?= transbool($row['scpt_enduser']); ?></td>
								<td class="line"><?= $allProbs; ?></td>
								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['scpt_id']; ?>')"><?= TRANS('BT_EDIT'); ?></button></td>
								<td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['scpt_id']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
							</tr>

							<?php
						}
						?>
					</tbody>
				</table>
			<?php
			}
		} elseif ((isset($_GET['action'])  && ($_GET['action'] == "details")) && !isset($_POST['submit'])) {
			$row = $resultado->fetch();
			?>
			<div class="row my-2">
				
				<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('COL_NAME')); ?></div>
				<div class="<?= $colContent2; ?>">
					<?= $row['scpt_nome']; ?>
				</div>
			
				<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('COL_SCRIPT_ENDUSER')); ?></div>
				<div class="<?= $colContent2; ?>">
					<?php
					$yesChecked = ($row['scpt_enduser'] == 1 ? "checked" : "");
					$noChecked = ($row['scpt_enduser'] == 0 ? "checked" : "");
					?>
					<div class="switch-field">
						<input type="radio" id="enduser" name="enduser" value="yes" <?= $yesChecked; ?> disabled />
						<label for="enduser"><?= TRANS('YES'); ?></label>
						<input type="radio" id="enduser_no" name="enduser" value="no" <?= $noChecked; ?> disabled />
						<label for="enduser_no"><?= TRANS('NOT'); ?></label>
					</div>
				</div>
			</div>

			<div class="row my-2">
				<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('DESCRIPTION')); ?></div>
				<div class="<?= $colContent; ?>"><?= $row['scpt_desc']; ?></div>
			</div>
			<div class="row my-2">
				<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('COL_SCRIPT')); ?></div>
				<div class="<?= $colContent; ?>"><?= $row['scpt_script']; ?></div>
			</div>

			<input type="hidden" name="cod" id="cod" value="<?= $_GET['cod']; ?>" />
			<input type="hidden" name="action" id="action" value="<?= $_GET['action']; ?>" />
			<div class="row my-2" id="related_issues">
			</div>
			


			<div class="row w-100">
				<div class="col-md-8 d-none d-md-block">
				</div>
				<div class="col-12 col-md-2 ">
					<button class="btn btn-primary bt-edit" data-id="<?= $_GET['cod']; ?>" name="edit"><?= TRANS("BT_EDIT"); ?></button>
				</div>
				<div class="col-12 col-md-2 ">
					<button class="btn btn-secondary " name="return" onClick="javascript:history.back()"><?= TRANS("TXT_RETURN"); ?></button>
				</div>
			</div>
			<?php
		
		
		} elseif ((isset($_GET['action'])  && ($_GET['action'] == "endview")) && !isset($_POST['submit'])) {

			if ($registros == 1) {
				$row = $resultado->fetch();
				?>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('DESCRIPTION')); ?></div>
					<div class="<?= $colContent; ?>"><?= $row['scpt_desc']; ?></div>
				</div>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('COL_SCRIPT')); ?></div>
					<div class="<?= $colContent; ?>"><?= $row['scpt_script']; ?></div>
				</div>
				<?php
			} else {
				?>
				<table id="table_lists" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">

				<thead>
					<tr class="header">
						<td class="line sigla"><?= TRANS('COL_NAME'); ?></td>
						<td class="line description"><?= TRANS('DESCRIPTION'); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($resultado->fetchall() as $row) {
							if ($_SESSION['s_nivel']!=3 || $row['scpt_enduser']==1) {
								?>
								<tr>
									<td class="line"><a onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=endview&cod=<?= $row['scpt_id']; ?>')"><?= $row['scpt_nome']; ?></a></td>
									<td class="line"><?= $row['scpt_desc']; ?></td>
								</tr>
								<?php
							}
						}
					?>
				</tbody>
				</table>
				<?php
			}
			
		} elseif ((isset($_GET['action'])  && ($_GET['action'] == "new")) && !isset($_POST['submit'])) {

			?>
			<h6><?= TRANS('NEW_RECORD'); ?></h6>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>

				<input type="hidden" name="areaHabilitada" id="idAreaHabilitada" value="needless_area"/>


				<div class="form-group row my-4">
					<label for="script_name" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_NAME'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="script_name" name="script_name" rows="4" required />
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('COL_SCRIPT_ENDUSER'); ?>"><?= TRANS('COL_SCRIPT_ENDUSER'); ?></label>
					<div class="form-group col-md-10 ">
						<div class="switch-field">
							<?php
							$yesChecked = "";
							$noChecked = "checked";
							?>
							<input type="radio" id="enduser" name="enduser" value="yes" <?= $yesChecked; ?> />
							<label for="enduser"><?= TRANS('YES'); ?></label>
							<input type="radio" id="enduser_no" name="enduser" value="no" <?= $noChecked; ?> />
							<label for="enduser_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label for="description" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DESCRIPTION'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control" id="description" name="description" rows="2"></textarea>
					</div>

					<label for="script_content" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_SCRIPT'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control" id="script_content" name="script_content" rows="4"></textarea>
						<div class="invalid-feedback">
							<?= TRANS('MANDATORY_FIELD'); ?>
						</div>
					</div>

					<label for="idArea" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RESPONSIBLE_AREA'); ?></label>
					<div class="form-group col-md-10">
						<select class="form-control" id="idArea" name="area">
							<option value="-1"><?= TRANS('FILTER_BY_AREA'); ?></option>
							<?php
							$areas = getAreas($conn, 0, 1, 1);
							foreach ($areas as $area) {
								?>
								<option value="<?= $area['sis_id']; ?>"><?= $area['sistema']; ?></option>
								<?php
							}
							?>
						</select>
					</div>

					<label for="idProblema" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ISSUE_TYPE'); ?></label>
					<div class="form-group col-md-10">
						<div id="Problema">
							<select class="form-control" id="idProblema" name="problema">
								<option value="-1"><?= TRANS('FILTER_BY_AREA'); ?></option>
							</select>
							<!-- <input type="hidden" name="problema" id="idProblema" value="-1" /> -->
						</div>
					</div>

					<div class="form-group col-md-12">
						<div id="divProblema">
							<input type="hidden" name="radio_prob" id="idRadioProb" value="-1"/>
						</div>
					</div>
					<div class="form-group col-md-12">
						<div id="divInformacaoProblema">
						</div>
					</div>
					<input type="hidden" name="pathAdmin" id="idPathAdmin" value="fromPathAdmin"/>



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
					<input type="hidden" name="areaHabilitada" id="idAreaHabilitada" value="needless_area"/>

					<label for="script_name" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_NAME'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="script_name" name="script_name" rows="4" required value="<?= $row['scpt_nome']; ?>"/>
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('COL_SCRIPT_ENDUSER'); ?>"><?= TRANS('COL_SCRIPT_ENDUSER'); ?></label>
					<div class="form-group col-md-10 ">
						<div class="switch-field">
							<?php
							$yesChecked = ($row['scpt_enduser'] == 1 ? "checked" : "");
							$noChecked = (!($row['scpt_enduser'] == 1) ? "checked" : "");
							?>
							<input type="radio" id="enduser" name="enduser" value="yes" <?= $yesChecked; ?> />
							<label for="enduser"><?= TRANS('YES'); ?></label>
							<input type="radio" id="enduser_no" name="enduser" value="no" <?= $noChecked; ?> />
							<label for="enduser_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label for="description" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DESCRIPTION'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control" id="description" name="description" rows="2"><?= $row['scpt_desc']; ?></textarea>
					</div>

					<label for="script_content" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_SCRIPT'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control" id="script_content" name="script_content" rows="4"><?= $row['scpt_script']; ?></textarea>
						<div class="invalid-feedback">
							<?= TRANS('MANDATORY_FIELD'); ?>
						</div>
					</div>


					
					<div class="form-group col-md-12" id="related_issues">
					</div>






					<label for="idArea" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RESPONSIBLE_AREA'); ?></label>
					<div class="form-group col-md-10">
						<select class="form-control" id="idArea" name="area">
							<option value="-1"><?= TRANS('FILTER_BY_AREA'); ?></option>
							<?php
							$areas = getAreas($conn, 0, 1, 1);
							foreach ($areas as $area) {
								?>
								<option value="<?= $area['sis_id']; ?>"><?= $area['sistema']; ?></option>
								<?php
							}
							?>
						</select>
					</div>

					<label for="idProblema" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ISSUE_TYPE'); ?></label>
					<div class="form-group col-md-10">
						<div id="Problema">
							<select class="form-control" id="idProblema" name="problema">
								<option value="-1"><?= TRANS('FILTER_BY_AREA'); ?></option>
							</select>
							<!-- <input type="hidden" name="problema" id="idProblema" value="-1" /> -->
						</div>
					</div>

					<div class="form-group col-md-12">
						<div id="divProblema">
							<input type="hidden" name="radio_prob" id="idRadioProb" value="-1"/>
						</div>
					</div>
					<div class="form-group col-md-12">
						<div id="divInformacaoProblema">
						</div>
					</div>
					<input type="hidden" name="pathAdmin" id="idPathAdmin" value="fromPathAdmin"/>



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
					targets: ['editar', 'remover']
				}],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});

			if (typeof($('#script_content').val()) !== 'undefined') {
                var formatBar = CKEDITOR.inline('script_content', {
                    uiColor: '#CCCCCC',
                    language: 'pt-br'
                });

                formatBar.on('change', function() {
                    formatBar.updateElement();
                });
            }
			
			if ($('#action').val() == 'edit' || $('#action').val() == 'details') {
				$.ajax({
					url: './get_script_type_of_issues_table.php',
					method: 'POST',
					data: {
						cod: $('#cod').val(),
						action: $('#action').val(),
					}
					// dataType: 'json',
				}).done(function(response) {
					$('#related_issues').html(response);
				});
			}

			$('.bt-edit').on("click", function() {

				$('#idLoad').css('display', 'block');
				var cod = $(this).attr("data-id");

				var url = '<?= $_SERVER['PHP_SELF'] ?>?action=edit&cod=' + cod;
				$(location).prop('href', url);
			});


			if ($('#idProblema').length > 0) {
				ajaxFunction('Problema', '../../ocomon/geral/showSelProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea', 'pathAdmin=idPathAdmin', 'area_habilitada=idAreaHabilitada'); 
				ajaxFunction('divProblema', '../../ocomon/geral/showProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea', 'pathAdmin=idPathAdmin');
			}

			

			$('#idArea').on('change', function() {
				ajaxFunction('Problema', '../../ocomon/geral/showSelProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea', 'pathAdmin=idPathAdmin', 'area_habilitada=idAreaHabilitada');
				ajaxFunction('divProblema', '../../ocomon/geral/showProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea');
				// ajaxFunction('divInformacaoProblema', '../../ocomon/geral/showInformacaoProb.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea');
			});

			$('#idProblema').on('change', function() {
				// ajaxFunction('Problema', '../../ocomon/geral/showSelProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea', 'pathAdmin=idPathAdmin', 'area_habilitada=idAreaHabilitada');
				// ajaxFunction('divProblema', '../../ocomon/geral/showProbs.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea');
				// ajaxFunction('divInformacaoProblema', '../../ocomon/geral/showInformacaoProb.php', 'idLoad', 'prob=idProblema', 'area_cod=idArea');
			});







			// console.log('Area: ' + $('#idArea').val());
			// console.log('Prob: ' + $('#idProblema').val());
			// console.log('pathAdmin: ' + $('#idPathAdmin').val());
			// console.log('idAreaHabilitada: ' + $('#idAreaHabilitada').val());
			// console.log('Problema: ' + $('#Problema').html());
			// console.log('divProblema: ' + $('#divProblema').html());
			// console.log('divInformacaoProblema: ' + $('#divInformacaoProblema').html());


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
					url: './scripts_documentation_process.php',
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
				url: './scripts_documentation_process.php',
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