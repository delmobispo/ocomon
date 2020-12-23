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

		<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form" onSubmit="return valida();">
			<div class="form-group row my-4">
				<label for="passwordAtual" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TTL_CURRENT_PASS'); ?></label>
				<div class="form-group col-md-10">
					<input type="password" class="form-control " id="passwordAtual" name="passwordAtual" placeholder="<?= TRANS('TTL_CURRENT_PASS'); ?>" autocomplete="off" required/>
				</div>
				<div class="w-100"></div>
				<label for="password" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TTL_NEWS_PASS'); ?></label>
				<div class="form-group col-md-10">
					<input type="password" class="form-control " id="password" name="password" placeholder="<?= TRANS('TTL_NEWS_PASS'); ?>" autocomplete="off" required/>
				</div>
				<div class="w-100"></div>
				<label for="password2" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('REPEAT_NEW_PASSWORD'); ?></label>
				<div class="form-group col-md-10">
					<input type="password" class="form-control " id="password2" name="password2" placeholder="<?= TRANS('REPEAT_NEW_PASSWORD'); ?>" autocomplete="off" required/>
				</div>

				<div class="w-100"></div>
				<div class="form-group col-md-8 d-none d-md-block">
				</div>
				<div class="form-group col-12 col-md-2  ">
					<button type="submit" id="idSubmit" name="submit" value="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
				</div>
				<div class="form-group col-12 col-md-2">
					<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
				</div>

			</div>
		</form>
	</div>



	<?php

	if (isset($_POST['submit']) && $_POST['submit'] == 'submit') {
		$erro = false;

		if (($_POST['password'] != $_POST['password2']) or (!isset($_POST['password']))) {
			$erro = true;
			$aviso = TRANS('PASSWORDS_DOESNT_MATCH');
		}

		if (!$erro) {
			$newPassword = md5($_POST['password']);
			$oldPassword = md5($_POST['passwordAtual']);

			$userId = $_SESSION['s_uid'];
			$sql = "SELECT user_id FROM usuarios WHERE user_id = {$userId} AND password = '{$oldPassword}'";
			$res = $conn->query($sql);

			if (!$res->rowCount()) {
				$erro = true;
				$aviso = TRANS('ERR_LOGON');
			}
		}

		if (!isset($_POST['password']) or !isset($_POST['password2'])) {
			$aviso = TRANS('MSG_INCOMPLETE_DATA_CONS_FIELDS');
			$erro = true;
		}

		if (!$erro) {
			$query = "UPDATE usuarios SET password = '" . $newPassword . "' WHERE user_id = '{$userId}' ";
			try {
				$conn->exec($query);
				$aviso = TRANS('MSG_SUCCESS_EDIT');
			} catch (Exception $e) {
				$erro = true;
				$aviso = TRANS('MSG_ERR_UPDATE_DATA_SYSTEM');
			}
		}

		if (!$erro) {
			$_SESSION['flash'] = message('success', 'Pronto!', $aviso, '');
			// echo "<script>redirect('" . $_SERVER['PHP_SELF'] . "')</script>";
			redirect($_SERVER['PHP_SELF']);
		} else {
			$_SESSION['flash'] = message('danger', 'Ooops!', $aviso, '');
			// echo "<script>redirect('" . $_SERVER['PHP_SELF'] . "')</script>";
			redirect($_SERVER['PHP_SELF']);
		}
	}
	?>
	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/MHS/jquery.md5.min.js"></script>
	<script type="text/javascript">
		function compPass() {
			var obj = document.getElementById('password');
			var obj2 = document.getElementById('password2');
			if (obj.value != obj2.value) {
				alert('<?= TRANS('PASSWORDS_DOESNT_MATCH');?>');
				return false;
			} else
				return true;
		}

		function valida() {
			var ok = validaForm('passwordAtual', '', '<?= TRANS('PASSWORD');?>', 1);
			if (ok) var ok = validaForm('password', 'ALFANUM', '<?= TRANS('PASSWORD');?>', 1);
			if (ok) var ok = validaForm('password2', 'ALFANUM', '<?= TRANS('PASSWORD');?>', 1);
			if (ok) var ok = compPass();

			return ok;
		}
	</script>

</body>

</html>