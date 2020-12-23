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


$sqlUserLang = "SELECT upref_lang FROM uprefs WHERE upref_uid = ".$_SESSION['s_uid']."";
$execUserLang = $conn->query($sqlUserLang);
$rowUL = $execUserLang->fetch();
$hasUL = $execUserLang->rowcount();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- <link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" /> -->
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
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

	<?php
		if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
            echo $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
	?>

	<div class="container">

		<h5 class="my-4"><i class="fas fa-globe text-secondary"></i>&nbsp;<?= TRANS('MNL_LANG'); ?></h5>
		<div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div id="divDetails">
					</div>
				</div>
			</div>
		</div>

		<form method="post" name="form1" action="<?= $_SERVER['PHP_SELF']; ?>" id="form1" > <!-- onSubmit="return valida();" -->
		<?php
		if (!isset($_POST['submit'])) {


			$files = array();
			$files = getDirFileNames('../../includes/languages/');
			
			?>
			<div class="form-group row my-4">
				<label for="lang" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LANGUAGE_FILE'); ?></label>
				<div class="form-group col-md-10">
					<select class="form-control" id="lang" name="lang">
						<?php
						for ($i=0; $i<count($files); $i++){
							print "<option value='".$files[$i]."' ";
							if ($rowUL && $files[$i] == $rowUL['upref_lang'])
								print " selected";
							print ">".str_replace('.php', '', $files[$i]) ."</option>";
						}
						?>
					</select>
				</div>
				<div class="w-100"></div>
				<div class="form-group col-md-8 d-none d-md-block">
				</div>
				<div class="form-group col-12 col-md-2  ">
					<button type="submit" id="idSubmit" name="submit" value="submit" class="btn btn-primary btn-block"><?= TRANS('BT_LOAD'); ?></button>
				</div>
				<div class="form-group col-12 col-md-2">
					<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
				</div>
			</div>
			<?php
		} else
		if (isset($_POST['submit']) ) {

			if (!empty($hasUL)){
				$qry = "UPDATE uprefs SET upref_lang = '".$_POST['lang']."' WHERE upref_uid = ".$_SESSION['s_uid']."";
			} else {
				$qry = "INSERT INTO uprefs (upref_uid, upref_lang) values (".$_SESSION['s_uid'].", '".$_POST['lang']."')";
			}
			$execQry = $conn->exec($qry);

			$_SESSION['s_language'] = $_POST['lang'];

			$_SESSION['flash'] = message('success', '', TRANS('MSG_LANG_FILE_SUCCESS_LOADED'), '');
			// echo "<script>redirect('" . $_SERVER['PHP_SELF'] . "')</script>";
			// header("Location: ". $_SERVER['PHP_SELF']);
			redirect($_SERVER['PHP_SELF']);
		}			
		?>
		</form>
		<script src="../../includes/javascript/funcoes-3.0.js"></script>
<script type="text/javascript">
</script>
</div>
</body>
</html>