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
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<!-- <link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" /> -->
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<!-- <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" /> -->
	<!-- <link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2.min.css" /> -->

	<title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>
	<?= $auth->showHeader(); ?>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>

    <div id="divResult"></div>

	<?php
		if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
            echo $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
	?>

	<div class="container">
		<h5 class="my-4"><i class="fas fa-key text-secondary"></i>&nbsp;<?= TRANS('TTL_ALTER_PASS'); ?></h5>
		<div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div id="divDetails">
					</div>
				</div>
			</div>
		</div>

		<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">

            <?= csrf_input(); ?>
			<div class="form-group row my-4">
				<label for="current_pass" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TTL_CURRENT_PASS'); ?></label>
				<div class="form-group col-md-10">
					<input type="password" class="form-control " id="current_pass" name="current_pass" placeholder="<?= TRANS('TTL_CURRENT_PASS'); ?>" autocomplete="off" required/>
                    <div class="invalid-feedback">
						<?= TRANS('MANDATORY_FIELD'); ?>
					</div>
				</div>
				<div class="w-100"></div>
				<label for="new_pass_1" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TTL_NEWS_PASS'); ?></label>
				<div class="form-group col-md-10">
					<input type="password" class="form-control " id="new_pass_1" name="new_pass_1" placeholder="<?= TRANS('TTL_NEWS_PASS'); ?>" autocomplete="off" required/>
                    <div class="invalid-feedback">
                        <?= TRANS('MANDATORY_FIELD'); ?>
                    </div>
				</div>
				<div class="w-100"></div>
				<label for="new_pass_2" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('REPEAT_NEW_PASSWORD'); ?></label>
				<div class="form-group col-md-10">
					<input type="password" class="form-control " id="new_pass_2" name="new_pass_2" placeholder="<?= TRANS('REPEAT_NEW_PASSWORD'); ?>" autocomplete="off" required/>
                    <div class="invalid-feedback">
                        <?= TRANS('MANDATORY_FIELD'); ?>
                    </div>
				</div>

				<div class="w-100"></div>
				<div class="form-group col-md-8 d-none d-md-block">
				</div>
				<div class="form-group col-12 col-md-2  ">
                    <input type="hidden" name="action" id="action" value="edit"/>
                    <input type="hidden" name="cod" id="cod" value="<?= $_SESSION['s_uid']; ?>"/>
					<button type="submit" id="idSubmit" name="submit" value="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
				</div>
				<div class="form-group col-12 col-md-2">
					<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
				</div>

			</div>
		</form>
	</div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="../../includes/components/jquery/MHS/jquery.md5.min.js"></script>
	<script type="text/javascript">
        
            $('input').on('change', function() {
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

                let csrf = $('#csrf').val();
                let current_pass = ($('#current_pass').val() != "" ? $.MD5($('#current_pass').val()) : "");
				let new_pass_1 = ($('#new_pass_1').val() != "" ? $.MD5($('#new_pass_1').val()) : "");
				let new_pass_2 = ($('#new_pass_2').val() != "" ? $.MD5($('#new_pass_2').val()) : "");
				let action = $('#action').val();
				let cod = $('#cod').val();
				$("#idSubmit").prop("disabled", true);
				$.ajax({
					url: './change_pass_process.php',
					method: 'POST',
					data : {
                            "csrf" : csrf,
                            "current_pass" : current_pass,
                            "new_pass_1" : new_pass_1,
                            "new_pass_2" : new_pass_2,
                            "action" : action,
                            "cod" : cod
                    },
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
	</script>

</body>

</html>