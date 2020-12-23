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
	<!-- <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" /> -->
	<link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2.min.css" />

	<title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>
	<?= $auth->showHeader(); ?>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>


	<div class="container-fluid">
		<h5 class="my-4"><i class="fas fa-database text-secondary"></i>&nbsp;<?= TRANS('TLT_CONS_SOLUT_PROB'); ?></h5>
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
		?>


		<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form" onSubmit="return false;">
			<!-- onSubmit="return false;" -->
			<div class="form-group row my-4">

				<label for="data_inicial" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('START_DATE'); ?></label>
				<div class="form-group col-md-4">
					<input type="text" class="form-control " id="data_inicial" name="data_inicial" placeholder="<?= TRANS('PLACEHOLDER_START_DATE_PERIOD_SEARCH'); ?>" autocomplete="off" />
				</div>

				<label for="data_final" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('END_DATE'); ?></label>
				<div class="form-group col-md-4">
					<input type="text" class="form-control " id="data_final" name="data_final" placeholder="<?= TRANS('PLACEHOLDER_END_DATE_PERIOD_SEARCH'); ?>" autocomplete="off" />
				</div>
				<label for="problema" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SEARCH_TERMS'); ?></label>
				<div class="form-group col-md-10">
					<textarea class="form-control " id="problema" name="problema" rows="4" required></textarea>
					<small class="form-text text-muted">
						<?= TRANS('SEARCH_HELPER'); ?>.
					</small>
				</div>


				<label for="operador" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TECHNICIAN'); ?></label>
				<div class="form-group col-md-4">
					<select class="form-control sel2" id="operador" name="operador">
						<option value="-1" selected><?= TRANS('OCO_SEL_OPERATOR'); ?></option>
						<?php
						$sql = "SELECT * FROM usuarios WHERE nivel in (1,2) ORDER BY nome";
						$resultado = $conn->query($sql);
						foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
							print "<option value='" . $row['user_id'] . "'";
							print ">" . $row['nome'] . "</option>";
						}
						?>
					</select>
				</div>


				<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CONSIDER'); ?></label>
				<div class="form-group col-md-4">
					<div class="form-check form-check-inline">
						<input class="form-check-input " type="checkbox" name="anyword">
						<legend class="col-form-label col-form-label-sm"><?= TRANS('AT_LEAST_ONE_OF_THE_WORDS'); ?></legend>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input " type="checkbox" name="onlyImgs">
						<legend class="col-form-label col-form-label-sm"><?= TRANS('ONLY_TICKETS_WITH_ATTACHMENTS'); ?></legend>
					</div>

				</div>


				<div class="row w-100">
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">
						<button type="submit" id="idSubmit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
					</div>
					<div class="form-group col-12 col-md-2">
						<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
					</div>
				</div>


			</div>
		</form>

	</div>

	<div class="container-fluid">
		<div id="divResult">
		</div>
	</div>


	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
	<!-- <script src="../../includes/components/bootstrap/js/bootstrap.bundle.min.js"></script> -->
	<script src="../../includes/components/select2/dist-2/js/select2.full.min.js"></script>
	<script>
		$(function() {
			$("#data_inicial").datepicker({
				dateFormat: 'dd/mm/yy',
				changeMonth: true,
				dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
				dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
				dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
				monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
				monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
			});
			//idDataFinal
			$("#data_final").datepicker({
				dateFormat: 'dd/mm/yy',
				changeMonth: true,
				dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
				dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
				dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
				monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
				monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
			});

			$('.sel2').select2();

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
					url: 'get_solutions_result.php',
					method: 'POST',
					data: $('#form').serialize(),
				}).done(function(response) {
					$('#divResult').html(response);
				});
				return false;
			});


		});

		function openTicketInfo(ticket) {

			let location = 'ticket_show.php?numero=' + ticket;
			$("#divDetails").load(location);
			$('#modal').modal();
		}

		function valida() {
			var ok = validaForm('idDataInicial', 'DATA', 'Data inicial', 0);
			if (ok) var ok = validaForm('idDataFinal', 'DATA', 'Data final', 0);
			if (ok) var ok = validaForm('idDescricao', '', 'Problema', 1);
			return ok;
		}
	</script>
</body>

</html>