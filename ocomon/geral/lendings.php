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
$_SESSION['s_page_ocomon'] = $_SERVER['PHP_SELF'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" />
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


	<div class="container-fluid">
		<h5 class="my-4"><i class="fas fa-book text-secondary"></i>&nbsp;<?= TRANS('TLT_ADMIN_LOAN'); ?></h5>
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

		$query = "SELECT e.* , u.* , l.loc_id, l.local as local_nome FROM emprestimos AS e, usuarios AS u, localizacao AS l " .
			"WHERE e.responsavel = u.user_id AND e.local = l.loc_id ";

		if (isset($_GET['cod'])) {
			$query .= " AND e.empr_id= " . $_GET['cod'] . " ";
		}
		$query .= " ORDER  BY data_devol";
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
				echo message('info', '', TRANS('MSG_NO_LOAN'), '', '', true);
			} else {

			?>
				<table id="emprestimos" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">

					<thead>
						<tr class="header">
							<td class="line material"><?= TRANS('COL_MAT'); ?></td>
							<td class="line responsavel"><?= TRANS('OCO_RESP'); ?></td>
							<td class="line data_emprestimo"><?= TRANS('LENDING_DATE'); ?></td>
							<td class="line data_devolucao"><?= TRANS('COL_DATE_DEV'); ?></td>
							<td class="line quem"><?= TRANS('COL_WHO'); ?></td>
							<td class="line departamento"><?= TRANS('DEPARTMENT'); ?></td>
							<td class="line telefone"><?= TRANS('COL_PHONE'); ?></td>
							<td class="line editar"><?= TRANS('BT_ALTER'); ?></td>
							<td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php

						foreach ($resultado->fetchall() as $row) {

						?>
							<tr>
								<td class="line"><?= $row['material']; ?></td>
								<td class="line"><?= $row['nome']; ?></td>
								<td class="line" data-sort="<?= dateDB($row['data_empr']); ?>"><?= dateScreen($row['data_empr'], 1); ?></td>
								<td class="line" data-sort="<?= dateDB($row['data_devol'], 1); ?>"><?= dateScreen($row['data_devol'], 1); ?></td>
								<td class="line"><?= $row['quem']; ?></td>
								<td class="line"><?= $row['local_nome']; ?></td>
								<td class="line"><?= $row['ramal']; ?></td>
								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=alter&cod=<?= $row['empr_id']; ?>&cellStyle=true')"><?= TRANS('BT_EDIT'); ?></button></td>
								<td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['empr_id']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
							</tr>

						<?php
						}
						?>
					</tbody>
				</table>
			<?php
			}
		} else
		if ((isset($_GET['action'])  && ($_GET['action'] == "incluir")) && !isset($_POST['submit'])) {

			?>
			<h6><?= TRANS('NEW_RECORD'); ?></h6>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form" onSubmit="return valida();">
				<div class="form-group row my-4">
					<label for="idMaterial" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MAT'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control " id="idMaterial" name="material" rows="4" required></textarea>
						<small class="form-text text-muted">
							<?= TRANS('DESCRIPTION_LENDING_HELPER'); ?>.
						</small>
					</div>

					<label for="idQuem" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_FIELD_FOR_WHO'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="idQuem" name="quem" placeholder="<?= TRANS('OCO_FIELD_FOR_WHO'); ?>" autocomplete="off" required />
					</div>

					<label for="idRamal" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
					<div class="form-group col-md-4">
						<input type="tel" class="form-control " id="idRamal" name="ramal" placeholder="<?= TRANS('COL_PHONE'); ?>" autocomplete="off" required />
					</div>

					<label for="idLocal" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control sel2" id="idLocal" name="local" required>
							<option value="" selected><?= TRANS('OCO_SEL_LOCAL'); ?></option>
							<?php
							$sql = "SELECT loc_id, local FROM localizacao ORDER BY local";
							$resultado = $conn->query($sql);
							foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
								print "<option value='" . $row['loc_id'] . "'";
								print ">" . $row['local'] . "</option>";
							}
							?>
						</select>
					</div>

					<label for="idResponsabel" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_RESP'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control sel2" id="idResponsabel" name="responsavel" required>
							<?php
							$sql = "SELECT user_id, nome FROM usuarios WHERE nivel in (1,2) ORDER BY nome";
							$resultado = $conn->query($sql);
							foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $rowUser) {
								print "<option value='" . $rowUser['user_id'] . "'";
								echo ($rowUser['user_id'] == $_SESSION['s_uid'] ? ' selected' : '');
								print ">" . $rowUser['nome'] . "</option>";
							}
							?>
						</select>
					</div>

					<label for="idDataSaida" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LENDING_DATE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="idDataSaida" name="saida" value="<?= date("d/m/Y"); ?>" autocomplete="off" required />
					</div>

					<label for="idDataDevolucao" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_DATE_DEV'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="idDataDevolucao" name="volta" placeholder="<?= TRANS('COL_DATE_DEV'); ?>" autocomplete="off" />
					</div>

					<div class="row w-100">
						<div class="form-group col-md-8 d-none d-md-block">
						</div>
						<div class="form-group col-12 col-md-2 ">

							<input type="hidden" name="action" value="new">
							<button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
						</div>
						<div class="form-group col-12 col-md-2">
							<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
						</div>
					</div>

				</div>
			</form>
		<?php
		} else

		if ((isset($_GET['action']) && $_GET['action'] == "alter") && empty($_POST['submit'])) {

			$row = $resultado->fetch();
		?>
			<h6><?= TRANS('COL_EDIT_LOAN'); ?></h6>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form" onSubmit="return valida();">
				<div class="form-group row my-4">
					<label for="idMaterial" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MAT'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control " id="idMaterial" name="material" rows="4" required><?= $row['material']; ?></textarea>
						<small class="form-text text-muted">
							<?= TRANS('DESCRIPTION_LENDING_HELPER'); ?>.
						</small>
					</div>

					<label for="idQuem" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_FIELD_FOR_WHO'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="idQuem" name="quem" value="<?= $row['quem']; ?>" placeholder="<?= TRANS('OCO_FIELD_FOR_WHO'); ?>" autocomplete="off" required />
					</div>

					<label for="idRamal" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
					<div class="form-group col-md-4">
						<input type="tel" class="form-control " id="idRamal" name="ramal" value="<?= $row['ramal']; ?>" placeholder="<?= TRANS('COL_PHONE'); ?>" autocomplete="off" required />
					</div>

					<label for="idLocal" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control sel2" id="idLocal" name="local" required>
							<option value=""><?= TRANS('OCO_SEL_LOCAL'); ?></option>
							<?php
							$sql = "SELECT loc_id, local FROM localizacao ORDER BY local";
							$resultado = $conn->query($sql);
							foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $rowLoc) {
								print "<option value='" . $rowLoc['loc_id'] . "'";
								echo ($rowLoc['loc_id'] == $row['loc_id'] ? ' selected' : '');
								print ">" . $rowLoc['local'] . "</option>";
							}
							?>
						</select>
					</div>


					<label for="idResponsabel" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_RESP'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control sel2" id="idResponsabel" name="responsavel" required>
							<!-- <option value="-1"><?= TRANS('OCO_SEL_OPERATOR'); ?></option> -->
							<?php
							$sql = "SELECT user_id, nome FROM usuarios WHERE nivel in (1,2) ORDER BY nome";
							$resultado = $conn->query($sql);
							foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $rowUser) {
								print "<option value='" . $rowUser['user_id'] . "'";
								echo ($rowUser['user_id'] == $row['responsavel'] ? ' selected' : '');
								print ">" . $rowUser['nome'] . "</option>";
							}
							?>
						</select>
					</div>


					<label for="idDataSaida" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LENDING_DATE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="idDataSaida" name="saida" value="<?= dateScreen($row['data_empr'], 1); ?>" autocomplete="off" required />
					</div>

					<label for="idDataDevolucao" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_DATE_DEV'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="idDataDevolucao" name="volta" value="<?= dateScreen($row['data_devol'], 1); ?>" placeholder="<?= TRANS('COL_DATE_DEV'); ?>" autocomplete="off" />
					</div>


					<input type="hidden" name="cod" value="<?= $_GET['cod']; ?>">
					<input type="hidden" name="action" value="alter">

					<div class="row w-100"></div>
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">
						<button type="submit" id="idSubmit" name="submit" value="alter" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
					</div>
					<div class="form-group col-12 col-md-2">
						<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
					</div>


				</div>
			</form>
		<?php
		} else

		if (isset($_GET['action']) && $_GET['action'] == "excluir") {

			$erro = false;

			$query2 = "DELETE FROM emprestimos WHERE empr_id='" . $_GET['cod'] . "'";

			try {
				$resultado2 = $conn->query($query2);
				$aviso = TRANS('OK_DEL');
			} catch (Exception $e) {
				$erro = true;
				$aviso = TRANS('MSG_ERR_DATA_REMOVE');
			}

			if (!$erro) {
				$_SESSION['flash'] = message('success', '', $aviso, '');
			} else {
				$_SESSION['flash'] = message('danger', '', $aviso, '');
			}
			// print "<script>redirect('" . $_SERVER['PHP_SELF'] . "');</script>";
			redirect($_SERVER['PHP_SELF']);
		} else

			if (isset($_POST['action']) && $_POST['action'] == 'new') {

			$erro = false;
			$aviso = "";

			if (!empty($_POST['material'])) {
				$material = noHtml($_POST['material']);
			} else {
				$erro = true;
				$aviso .= "O Campo [Material] precisa ser preenchido<br/>";
			}

			if (!empty($_POST['quem'])) {
				$quem = noHtml($_POST['quem']);
			} else {
				$erro = true;
				$aviso .= "O Campo [Para quem] precisa ser preenchido<br/>";
			}

			if (!empty($_POST['ramal'])) {
				$ramal = noHtml($_POST['ramal']);
			} else {
				$erro = true;
				$aviso .= "O Campo [Telefone] precisa ser preenchido<br/>";
			}

			if (!empty($_POST['local']) && $_POST['local'] != '-1') {
				$local = noHtml($_POST['local']);
			} else {
				$erro = true;
				$aviso .= "O Campo [Departamento] precisa ser preenchido<br/>";
			}

			if (empty($_POST['saida'])) {
				$erro = true;
				$aviso .= "O Campo [Data do empréstimo] precisa ser preenchido<br/>";
			}


			if (!$erro) {

				$query = "INSERT INTO emprestimos (material, responsavel, data_empr, data_devol, quem, local, ramal) values" .
					" ('" . $material . "', '" . $_SESSION['s_uid'] . "','" . dateDB($_POST['saida']) . "'," . dbField(dateDB($_POST['volta'], 1), 'date') . "," .
					"'" . $quem . "', '" . $local . "', '" . $ramal . "')";

				try {
					$resultado = $conn->query($query);
					$aviso .= TRANS('MSG_SUCCESS_INSERT');
				} catch (Exception $e) {
					$erro = true;
					$aviso .= "" . TRANS('MSG_ERR_SAVE_RECORD') . "<br>" . $query;
				}
			}

			if (!$erro) {
				$_SESSION['flash'] = message('success', '', $aviso, '');
				// print "<script>redirect('" . $_SERVER['PHP_SELF'] . "');</script>";

			} else {
				$_SESSION['flash'] = message('danger', '', $aviso, '');
				// print "<script>redirect('" . $_SERVER['PHP_SELF'] . "');</script>";
			}
			redirect($_SERVER['PHP_SELF']);
		} elseif (isset($_POST['action']) && $_POST['action'] == 'alter') {

			// var_dump([
			// 	'POST' => $_POST,
			// 	'dateDB nulable' => dateDB($_POST['volta'], 1),
			// 	'dateDB not nulable' => dateDB($_POST['volta'], 0),
			// 	'Na clausula' => dbField(dateDB($_POST['volta'], 1), 'date'),
			// ]); exit();

			$erro = false;
			$aviso = "";

			if (!empty($_POST['material'])) {
				$material = noHtml($_POST['material']);
			} else {
				$erro = true;
				$aviso .= "O Campo [Material] precisa ser preenchido<br/>";
			}

			if (!empty($_POST['quem'])) {
				$quem = noHtml($_POST['quem']);
			} else {
				$erro = true;
				$aviso .= "O Campo [Para quem] precisa ser preenchido<br/>";
			}

			if (!empty($_POST['ramal'])) {
				$ramal = noHtml($_POST['ramal']);
			} else {
				$erro = true;
				$aviso .= "O Campo [Telefone] precisa ser preenchido<br/>";
			}

			if (!empty($_POST['local']) && $_POST['local'] != '-1') {
				$local = noHtml($_POST['local']);
			} else {
				$erro = true;
				$aviso .= "O Campo [Departamento] precisa ser preenchido<br/>";
			}

			if (empty($_POST['saida'])) {
				$erro = true;
				$aviso .= "O Campo [Data do empréstimo] precisa ser preenchido<br/>";
			}

			$query2 = "UPDATE emprestimos SET material='" . $material . "', responsavel='" . noHtml($_POST['responsavel']) . "', " .
				"ramal = '" . $ramal . "', local = " . $local . ", data_empr='" . dateDB($_POST['saida']) . "', data_devol=" . dbField(dateDB($_POST['volta'], 1), 'date') . ", " .
				"quem='" . $quem . "' WHERE empr_id='" . $_POST['cod'] . "'";

			try {
				$resultado2 = $conn->query($query2);
				$aviso .=  TRANS('MSG_SUCCESS_EDIT');
			} catch (Exception $e) {
				$erro = true;
				$aviso .=  TRANS('MSG_ERR_DATA_UPDATE');
			}

			if (!$erro) {
				$_SESSION['flash'] = message('success', '', $aviso, '');
			} else {
				$_SESSION['flash'] = message('danger', '', $aviso, '');
			}
			// print "<script>redirect('" . $_SERVER['PHP_SELF'] . "');</script>";
			redirect($_SERVER['PHP_SELF']);
		}

		// print "</table>";
		// print "</form>";


		?>
	</div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
	<script type="text/javascript">
		$(function() {

			$('#emprestimos').DataTable({
				paging: true,
				deferRender: true,
				columnDefs: [{
					orderable: false,
					targets: ['editar', 'remover']
				}],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});

			$("#idDataSaida").datepicker({
				dateFormat: 'dd/mm/yy',
				changeMonth: true,
				dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
				dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
				dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
				monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
				monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
			});
			//idDataFinal
			$("#idDataDevolucao").datepicker({
				dateFormat: 'dd/mm/yy',
				changeMonth: true,
				dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
				dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
				dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
				monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
				monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
			});

			$('#idBtIncluir').on("click", function() {
				$('#idLoad').css('display', 'block');
				var url = '<?= $_SERVER['PHP_SELF'] ?>?action=incluir';
				$(location).prop('href', url);
			});

			$('#bt-cancel').on('click', function() {
				var url = '<?= $_SERVER['PHP_SELF'] ?>';
				$(location).prop('href', url);
			});


		});




		function valida() {
			var ok = validaForm('idMaterial', '', 'Material', 1);
			if (ok) var ok = validaForm('idQuem', '', 'Para quem', 1);

			if (ok) var ok = validaForm('idLocal', 'COMBO', 'Local', 1);

			if (ok) var ok = validaForm('idRamal', 'ALFAFULLESPACO', 'Ramal', 1);
			if (ok) var ok = validaForm('idDataSaida', 'DATAFULL', 'Data Saída', 1);
			if (ok) var ok = validaForm('idDataDevolucao', 'DATAFULL', 'Data Devolução', 0);
			return ok;
		}

		function confirmDeleteModal(id) {
			$('#deleteModal').modal();
			$('#deleteButton').html('<a class="btn btn-danger" onclick="deleteData(' + id + ')">Remover</a>');
		}

		function deleteData(id) {
			// do your stuffs with id
			var url = "<?= $_SERVER['PHP_SELF'] ?>?action=excluir&cod=" + id + "&successRemove=true";
			$(location).prop('href', url);

			// $("#successMessage").html("Registro com  " + id + " Removido com sucesso!");
			$('#deleteModal').modal('hide'); // now close modal
		}
	</script>
</body>

</html>