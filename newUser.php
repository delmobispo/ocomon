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

include("includes/functions/functions.php");
include("includes/config.inc.php");
include("includes/versao.php");
include("includes/languages/" . LANGUAGE . ""); //TEMPORARIAMENTE
include("includes/queries/queries.php");
require_once "includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();


$qry = $QRY["useropencall"];
$exec = $conn->query($qry);
$rowconf = $exec->fetch();
if (!$rowconf['conf_user_opencall']) {
	exit();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" /> -->

	<style>

	</style>

	<title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>

	<div id="divResult"></div>


	<div class="container">
		<h5 class="my-4"><i class="fas fa-user-plus text-secondary"></i>&nbsp;<?= TRANS('USER_SELF_REGISTER'); ?></h5>
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
		<h6><?= TRANS('NEW_RECORD'); ?></h6>
		<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
			<?= csrf_input(); ?>
			<div class="form-group row my-4">
				<label for="login_name" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_LOGIN'); ?></label>
				<div class="form-group col-md-9">
					<input type="text" class="form-control " id="login_name" name="login_name" required placeholder="<?= TRANS('LOGIN_NAME_PLACEHOLDER'); ?>" />
				</div>

				<label for="fullname" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('FULLNAME'); ?></label>
				<div class="form-group col-md-9">
					<input type="text" class="form-control " id="fullname" name="fullname" required />
				</div>

				<label for="email" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_EMAIL'); ?></label>
				<div class="form-group col-md-9">
					<input type="email" class="form-control " id="email" name="email" required />
				</div>

				<label for="phone" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
				<div class="form-group col-md-9">
					<input type="tel" class="form-control " id="phone" name="phone" required />
				</div>

				<label for="password" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('PASSWORD'); ?></label>
				<div class="form-group col-md-9">
					<input type="password" class="form-control " id="password" name="password" required />
				</div>

				<label for="password2" class="col-md-3 col-form-label col-form-label-sm text-md-right"><?= TRANS('RETYPE_PASS'); ?></label>
				<div class="form-group col-md-9">
					<input type="password" class="form-control " id="password2" name="password2" required />
				</div>

				<div class="row w-100"></div>
				<div class="form-group col-md-8 d-none d-md-block">
				</div>
				<div class="form-group col-12 col-md-6 ">

					<input type="hidden" name="action" id="action" value="new">
					<button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
				</div>
				<div class="form-group col-12 col-md-6">
					<button type="reset" class="btn btn-secondary btn-block" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
				</div>

			</div>
		</form>
	</div>

	<!-- <script src="../../includes/javascript/funcoes-3.0.js"></script> -->
	<!-- <script src="../../includes/components/jquery/jquery.js"></script> -->
	<!-- <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script> -->
	<!-- <script src="../../includes/components/jquery/MHS/jquery.md5.min.js"></script> -->
	<script type="text/javascript">
		$(function() {

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

				let csrf = $('#csrf').val();
				let login_name = $('#login_name').val();
				let fullname = $('#fullname').val();
				let email = $('#email').val();
				let phone = $('#phone').val();
                let password = ($('#password').val() != "" ? $.MD5($('#password').val()) : "");
				let password2 = ($('#password2').val() != "" ? $.MD5($('#password2').val()) : "");
				let action = $('#action').val();

				$.ajax({
					url: './new_user_process.php',
					method: 'POST',
					// data: $('#form').serialize(),
					data : {
                            "csrf" : csrf,
                            "login_name" : login_name,
                            "fullname" : fullname,
                            "email" : email,
                            "phone" : phone,
                            "password" : password,
                            "password2" : password2,
                            "action" : action
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
						var url = 'index.php';
						$(location).prop('href', url);
						return false;
					}
				});
				return false;
			});

		});
	</script>
</body>

</html>