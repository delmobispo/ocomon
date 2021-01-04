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

		hr.thick {
			border: 1px solid;
			color: #CCCCCC !important;
			/* border-radius: 5px; */

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


	<div class="container-fluid bg-light">
		<h4 class="my-4"><i class="fas fa-cogs text-secondary"></i>&nbsp;<?= TRANS('MNL_CONF_BASIC'); ?></h4>
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

		$registros = 1;
		/* Configuraçoes gerais */
		$config = getConfig($conn);
		/* Configuração para auto-cadastro */
		$screen = getScreenInfo($conn, 1);
		/* Listagem de status possíveis */
		$status = getStatus($conn, 0, '1,2');
		/* Base de referência de perfil de jornada - área origem do chamado ou a área de atendimento */
		$wt_areas = array();
		$wt_areas[1] = TRANS('ORIGIN_AREA');
		$wt_areas[2] = TRANS('SERVICE_AREA');


		/* Classes para o grid */
		$colLabel = "col-sm-3 text-md-right font-weight-bold p-2 mb-4";
		$colsDefault = "small text-break border-bottom rounded p-2 bg-white"; /* border-secondary */
		$colContent = $colsDefault . " col-sm-9 col-md-9 ";
		$colContentLine = $colsDefault . " col-sm-9";
		/* Duas colunas */
		$colLabel2 = "col-sm-3 text-md-right font-weight-bold p-2 mb-4";
		$colContent2 = $colsDefault . " col-sm-3 col-md-3";


		if ((!isset($_GET['action'])) && !isset($_POST['submit'])) {

		?>
			<button class="btn btn-sm btn-primary bt-edit" id="idBtEdit" name="edit"><?= TRANS("BT_EDIT"); ?></button><br /><br />
			<?php
			if ($registros == 0) {
				echo message('info', '', TRANS('NO_RECORDS_FOUND'), '', '', true);
			} else {
			?>
				<h6 class="w-100 mt-5 "><i class="fas fa-sliders-h text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('BASIC_CONFIGURATION')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('LANGUAGE_FILE')); ?></div>
					<div class="<?= $colContent; ?>"><?= str_replace('.php', '', $config['conf_language']); ?></div>
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('OPT_DATE_FORMAT')); ?></div>
					<div class="<?= $colContent; ?>"><?= $config['conf_date_format']; ?></div>
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('OPT_SITE')); ?></div>
					<div class="<?= $colContent; ?>"><?= $config['conf_ocomon_site']; ?></div>
				</div>


				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-user-plus text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('USER_SELF_REGISTER')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('ALLOW_SELF_REGISTER')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = ($screen['conf_user_opencall'] == 1 ? "checked" : "");
						$noChecked = ($screen['conf_user_opencall'] == 0 ? "checked" : "");
						?>
						<div class="switch-field">
							<input type="radio" id="allow_self_register" name="allow_self_register" value="yes" <?= $yesChecked; ?> disabled />
							<label for="allow_self_register"><?= TRANS('YES'); ?></label>
							<input type="radio" id="allow_self_register_no" name="allow_self_register" value="no" <?= $noChecked; ?> disabled />
							<label for="allow_self_register_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('SELF_REGISTER_AREA')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?= getAreaInfo($conn, $screen['conf_ownarea'])['area_name']; ?>
					</div>
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_FIELD_MSG')); ?></div>
					<div class="<?= $colContent; ?>">
						<?= $screen['conf_scr_msg']; ?>
					</div>
				</div>


				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-clock text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('WORKTIME_CALC')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('OPT_WT_PROFILE_AREAS')); ?></div>
					<div class="<?= $colContent; ?>"><?= $wt_areas[$config['conf_wt_areas']]; ?></div>
				</div>


				<!-- section -->

				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-calendar-alt text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('CFG_TICKET_SCHEDULING')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_SCHEDULE_STATUS')); ?></div>
					<div class="<?= $colContent2; ?>"><?= getStatusInfo($conn, $config['conf_schedule_status'])['status']; ?></div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_SCHEDULE_STATUS_2')); ?></div>
					<div class="<?= $colContent2; ?>"><?= getStatusInfo($conn, $config['conf_schedule_status_2'])['status']; ?></div>
				</div>

				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-angle-double-right text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPENING_FORWARD_STATUS')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('SEL_FOWARD_STATUS')); ?></div>
					<div class="<?= $colContent; ?>"><?= getStatusInfo($conn, $config['conf_foward_when_open'])['status']; ?></div>
				</div>


				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-handshake text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('SLA_TOLERANCE')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('PERCENTAGE')); ?></div>
					<div class="<?= $colContent; ?>"><?= $config['conf_sla_tolerance']; ?>%</div>
				</div>



				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-exclamation-circle text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_JUSTIFICATION_SLA_OUT')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('OPT_DESC_SLA_OUT')); ?></div>
					<div class="<?= $colContent; ?>">
						<?php
						$yesChecked = ($config['conf_desc_sla_out'] == 1 ? "checked" : "");
						$noChecked = ($config['conf_desc_sla_out'] == 0 ? "checked" : "");
						?>
						<div class="switch-field">
							<input type="radio" id="desc_sla_out" name="desc_sla_out" value="yes" <?= $yesChecked; ?> disabled />
							<label for="desc_sla_out"><?= TRANS('YES'); ?></label>
							<input type="radio" id="desc_sla_out_no" name="desc_sla_out" value="no" <?= $noChecked; ?> disabled />
							<label for="desc_sla_out_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>
				</div>

				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-external-link-alt text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_REOPEN')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= firstLetterUp(TRANS('OPT_ALLOW_REOPEN')); ?></div>
					<div class="<?= $colContent; ?>">
						<?php
						$yesChecked = ($config['conf_allow_reopen'] == 1 ? "checked" : "");
						$noChecked = ($config['conf_allow_reopen'] == 0 ? "checked" : "");
						?>
						<div class="switch-field">
							<input type="radio" id="allowReopen" name="allowReopen" value="yes" <?= $yesChecked; ?> disabled />
							<label for="allowReopen"><?= TRANS('YES'); ?></label>
							<input type="radio" id="allowReopen_no" name="allowReopen" value="no" <?= $noChecked; ?> disabled />
							<label for="allowReopen_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>
				</div>


				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-upload text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_UPLOAD')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_MAXSIZE')); ?></div>
					<?php
					$emKbytes = $config['conf_upld_size'] / 1024;
					?>
					<div class="<?= $colContent2; ?>"><?= $emKbytes; ?> (kbytes)</div>
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_QTD_MAX_ANEXOS')); ?></div>
					<div class="<?= $colContent2; ?>"><?= $config['conf_qtd_max_anexos']; ?></div>
				</div>

				<!-- Arquivos permitidos para upload -->
				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-file-import text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_IMG')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%IMG%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%IMG%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_img" name="upld_img" value="yes" <?= $yesChecked; ?> disabled />
							<label for="upld_img"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_img_no" name="upld_img" value="no" <?= $noChecked; ?> disabled />
							<label for="upld_img_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_TXT')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%TXT%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%TXT%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_txt" name="upld_txt" value="yes" <?= $yesChecked; ?> disabled />
							<label for="upld_txt"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_txt_no" name="upld_txt" value="no" <?= $noChecked; ?> disabled />
							<label for="upld_txt_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_PDF')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%PDF%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%PDF%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_pdf" name="upld_pdf" value="yes" <?= $yesChecked; ?> disabled />
							<label for="upld_pdf"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_pdf_no" name="upld_pdf" value="no" <?= $noChecked; ?> disabled />
							<label for="upld_pdf_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_ODF')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%ODF%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%ODF%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_odf" name="upld_odf" value="yes" <?= $yesChecked; ?> disabled />
							<label for="upld_odf"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_odf_no" name="upld_odf" value="no" <?= $noChecked; ?> disabled />
							<label for="upld_odf_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_OOO')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%OOO%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%OOO%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_ooo" name="upld_ooo" value="yes" <?= $yesChecked; ?> disabled />
							<label for="upld_ooo"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_ooo_no" name="upld_ooo" value="no" <?= $noChecked; ?> disabled />
							<label for="upld_ooo_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_MSO')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%MSO%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%MSO%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_mso" name="upld_mso" value="yes" <?= $yesChecked; ?> disabled />
							<label for="upld_mso"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_mso_no" name="upld_mso" value="no" <?= $noChecked; ?> disabled />
							<label for="upld_mso_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_NMSO')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%NMSO%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%NMSO%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_nmso" name="upld_nmso" value="yes" <?= $yesChecked; ?> disabled />
							<label for="upld_nmso"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_nmso_no" name="upld_nmso" value="no" <?= $noChecked; ?> disabled />
							<label for="upld_nmso_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_RTF')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%RTF%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%RTF%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_rtf" name="upld_rtf" value="yes" <?= $yesChecked; ?> disabled />
							<label for="upld_rtf"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_rtf_no" name="upld_rtf" value="no" <?= $noChecked; ?> disabled />
							<label for="upld_rtf_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_HTML')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%HTML%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%HTML%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_html" name="upld_html" value="yes" <?= $yesChecked; ?> disabled />
							<label for="upld_html"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_html_no" name="upld_html" value="no" <?= $noChecked; ?> disabled />
							<label for="upld_html_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>
				</div>

				<!-- section -->
				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-image text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_UPLOAD_IMG')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_MAXWIDTH')); ?></div>
					<div class="<?= $colContent2; ?>"><?= $config['conf_upld_width']; ?> px</div>

					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_MAXHEIGHT')); ?></div>
					<div class="<?= $colContent2; ?>"><?= $config['conf_upld_height']; ?> px</div>
				</div>


				<!-- section -->
				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-align-right text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('FORMATTING_BAR')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_MURAL')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_formatBar'], '%mural%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_formatBar'], '%mural%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="formatMural" name="formatMural" value="yes" <?= $yesChecked; ?> disabled />
							<label for="formatMural"><?= TRANS('YES'); ?></label>
							<input type="radio" id="formatMural_no" name="formatMural" value="no" <?= $noChecked; ?> disabled />
							<label for="formatMural_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_OCORRENCIAS')); ?></div>
					<div class="<?= $colContent2; ?>">
						<?php
						$yesChecked = (strpos($config['conf_formatBar'], '%oco%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_formatBar'], '%oco%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="formatOco" name="formatOco" value="yes" <?= $yesChecked; ?> disabled />
							<label for="formatOco"><?= TRANS('YES'); ?></label>
							<input type="radio" id="formatOco_no" name="formatOco" value="no" <?= $noChecked; ?> disabled />
							<label for="formatOco_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>
				</div>


				<!-- section -->
				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-bell text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_SEND_MAIL_WRTY')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_DAYS_BEFORE')); ?></div>
					<div class="<?= $colContent2; ?>"><?= $config['conf_days_bf']; ?></div>
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_SEL_AREA')); ?></div>
					<div class="<?= $colContent2; ?>"><?= getAreaInfo($conn, $config['conf_wrty_area'])['area_name']; ?></div>
				</div>

				<h6 class="w-100 mt-5 border-top p-4"><i class="fas fa-tags text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_PROB_CATEG')); ?></h6>
				<div class="row my-2">
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_PROB_LABEL1')); ?></div>
					<div class="<?= $colContent2; ?>"><?= $config['conf_prob_tipo_1']; ?></div>
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_PROB_LABEL2')); ?></div>
					<div class="<?= $colContent2; ?>"><?= $config['conf_prob_tipo_2']; ?></div>
					<div class="<?= $colLabel2; ?>"><?= firstLetterUp(TRANS('OPT_PROB_LABEL3')); ?></div>
					<div class="<?= $colContent2; ?>"><?= $config['conf_prob_tipo_3']; ?></div>
				</div>


				<div class="row w-100">
					<div class="col-md-10 d-none d-md-block">
					</div>
					<div class="col-12 col-md-2 ">
						<button class="btn btn-primary bt-edit " name="edit"><?= TRANS("BT_EDIT"); ?></button>
					</div>
				</div>

			<?php
			}
		} else
		if ((isset($_GET['action'])  && ($_GET['action'] == "edit")) && !isset($_POST['submit'])) {

			?>
			<h6><?= TRANS('EDITION'); ?></h6>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>
				<div class="form-group row my-4">


					<h6 class="w-100 mt-5 ml-5"><i class="fas fa-sliders-h text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('BASIC_CONFIGURATION')); ?></h6>
					<?php
					$files = array();
					$files = getDirFileNames('../../includes/languages/');
					?>
					<label for="lang_file" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('LANGUAGE_FILE')); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control " name="lang_file" required id="lang_file">
							<?php
							for ($i = 0; $i < count($files); $i++) {
								print "<option value='" . $files[$i] . "' ";
								if ($files[$i] == $config['conf_language'])
									print " selected";
								print ">" . str_replace('.php', '', $files[$i]) . "</option>";
							}
							?>
						</select>
					</div>
					<label for="date_format" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_DATA_FORMAT'); ?>"><?= firstLetterUp(TRANS('OPT_DATE_FORMAT')); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control" name="date_format" id="date_format" required value="<?= $config['conf_date_format']; ?>" placeholder="<?= TRANS('SUGGESTION'); ?>: d/m/Y H:i:s" />
					</div>
					<label for="site" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_SITE'); ?>"><?= firstLetterUp(TRANS('OPT_SITE')); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control" name="site" id="site" required value="<?= $config['conf_ocomon_site']; ?>" />
					</div>


					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-user-plus text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('USER_SELF_REGISTER')); ?></h6>
					<label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_SELF_REGISTER'); ?>"><?= firstLetterUp(TRANS('ALLOW_SELF_REGISTER')); ?></label>
					<div class="form-group col-md-4 ">
						<div class="switch-field">
							<?php
							$yesChecked = ($screen['conf_user_opencall'] == 1 ? "checked" : "");
							$noChecked = ($screen['conf_user_opencall'] == 0 ? "checked" : "");
							?>
							<input type="radio" id="allow_self_register" name="allow_self_register" value="yes" <?= $yesChecked; ?> />
							<label for="allow_self_register"><?= TRANS('YES'); ?></label>
							<input type="radio" id="allow_self_register_no" name="allow_self_register" value="no" <?= $noChecked; ?> />
							<label for="allow_self_register_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label for="self_register_area" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_SELF_REGISTER_AREA'); ?>"><?= firstLetterUp(TRANS('SELF_REGISTER_AREA')); ?></label>
					<div class="form-group col-md-4 ">
						<select class="form-control " name="self_register_area" required id="self_register_area">
							<?php
							$areas = getAreas($conn, 0, 1, 0);
							foreach ($areas as $area) {
							?>
								<option value="<?= $area['sis_id']; ?>" <?= ($area['sis_id'] == $screen['conf_ownarea']) ? 'selected' : ''; ?>><?= $area['sistema']; ?></option>
							<?php
							}
							?>
						</select>
					</div>

					<label for="msg" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_OPENING_MESSAGE'); ?>"><?= firstLetterUp(TRANS('OPT_FIELD_MSG')); ?></label>
					<div class="form-group col-md-10 ">
						<textarea class="form-control" name="msg" id="msg"><?= $screen['conf_scr_msg']; ?></textarea>
					</div>

					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-clock text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('WORKTIME_CALC')); ?></h6>
					<label for="worktime_area_reference" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_WT_PROFILE_AREAS'); ?>"><?= firstLetterUp(TRANS('OPT_WT_PROFILE_AREAS')); ?></label>
					<div class="form-group col-md-10 ">
						<select class="form-control " name="worktime_area_reference" required id="worktime_area_reference">
							<?php
							foreach ($wt_areas as $key => $value) {
							?>
								<option value="<?= $key; ?>" <?= ($key == $config['conf_wt_areas'] ? 'selected' : ''); ?>><?= $value; ?></option>
							<?php
							}
							?>
						</select>
					</div>

					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-calendar-alt text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('CFG_TICKET_SCHEDULING')); ?></h6>
					<label for="open_scheduling_status" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_OPENING_SCHEDULE_STATUS'); ?>"><?= firstLetterUp(TRANS('OPT_SCHEDULE_STATUS')); ?></label>
					<div class="form-group col-md-4 ">
						<select class="form-control " name="open_scheduling_status" required id="open_scheduling_status">
							<?php
							foreach ($status as $stat) {
							?>
								<option value="<?= $stat['stat_id']; ?>" <?= ($stat['stat_id'] == $config['conf_schedule_status']) ? 'selected' : ''; ?>><?= $stat['status']; ?></option>
							<?php
							}
							?>
						</select>
					</div>
					<label for="edit_scheduling_status" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_EDITING_SCHEDULE_STATUS'); ?>"><?= firstLetterUp(TRANS('OPT_SCHEDULE_STATUS_2')); ?></label>
					<div class="form-group col-md-4 ">
						<select class="form-control " name="edit_scheduling_status" required id="edit_scheduling_status">
							<?php
							// $status = getStatus($conn, 0, '1,2');
							foreach ($status as $stat) {
							?>
								<option value="<?= $stat['stat_id']; ?>" <?= ($stat['stat_id'] == $config['conf_schedule_status_2']) ? 'selected' : ''; ?>><?= $stat['status']; ?></option>
							<?php
							}
							?>
						</select>
					</div>


					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-angle-double-right text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPENING_FORWARD_STATUS')); ?></h6>
					<label for="forward_status" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_OPENING_FORWARD_STATUS'); ?>"><?= firstLetterUp(TRANS('SEL_FOWARD_STATUS')); ?></label>
					<div class="form-group col-md-10 ">
						<select class="form-control " name="forward_status" required id="forward_status">
							<?php
							foreach ($status as $stat) {
							?>
								<option value="<?= $stat['stat_id']; ?>" <?= ($stat['stat_id'] == $config['conf_foward_when_open']) ? 'selected' : ''; ?>><?= $stat['status']; ?></option>
							<?php
							}
							?>
						</select>
					</div>


					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-handshake text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('SLA_TOLERANCE')); ?></h6>
					<label for="sla_tolerance" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_SLA_TOLERANCE'); ?>"><?= firstLetterUp(TRANS('PERCENTAGE')); ?></label>
					<div class="form-group col-md-10">
						<input type="number" class="form-control" name="sla_tolerance" id="sla_tolerance" required value="<?= $config['conf_sla_tolerance']; ?>" />
					</div>


					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-exclamation-circle text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_JUSTIFICATION_SLA_OUT')); ?></h6>
					<label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_JUSTIFICATION_SLA_OUT'); ?>"><?= firstLetterUp(TRANS('OPT_DESC_SLA_OUT')); ?></label>
					<div class="form-group col-md-10 ">
						<div class="switch-field">
							<?php
							$yesChecked = ($config['conf_desc_sla_out'] == 1 ? "checked" : "");
							$noChecked = ($config['conf_desc_sla_out'] == 0 ? "checked" : "");
							?>
							<input type="radio" id="justificativa" name="justificativa" value="yes" <?= $yesChecked; ?> />
							<label for="justificativa"><?= TRANS('YES'); ?></label>
							<input type="radio" id="justificativa_no" name="justificativa" value="no" <?= $noChecked; ?> />
							<label for="justificativa_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-external-link-alt text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_REOPEN')); ?></h6>
					<label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_ALLOW_REOPEN'); ?>"><?= firstLetterUp(TRANS('OPT_ALLOW_REOPEN')); ?></label>
					<div class="form-group col-md-10 ">
						<div class="switch-field">
							<?php
							$yesChecked = ($config['conf_allow_reopen'] == 1 ? "checked" : "");
							$noChecked = ($config['conf_allow_reopen'] == 0 ? "checked" : "");
							?>
							<input type="radio" id="allow_reopen" name="allow_reopen" value="yes" <?= $yesChecked; ?> />
							<label for="allow_reopen"><?= TRANS('YES'); ?></label>
							<input type="radio" id="allow_reopen_no" name="allow_reopen" value="no" <?= $noChecked; ?> />
							<label for="allow_reopen_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-upload text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_UPLOAD')); ?></h6>
					<label for="img_max_size" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_MAX_FILE_SIZE'); ?>"><?= firstLetterUp(TRANS('OPT_MAXSIZE')); ?></label>
					<div class="form-group col-md-4">
						<input type="number" class="form-control" name="img_max_size" id="img_max_size" required value="<?= $config['conf_upld_size'] / 1024; ?>" />
					</div>
					<label for="max_number_attachs" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_QTD_MAX_ANEXOS')); ?></label>
					<div class="form-group col-md-4">
						<input type="number" class="form-control" name="max_number_attachs" id="max_number_attachs" required value="<?= $config['conf_qtd_max_anexos']; ?>" />
					</div>




					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-file-import text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE')); ?></h6>
					<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_IMG')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%IMG%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%IMG%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_img" name="upld_img" value="yes" <?= $yesChecked; ?> />
							<label for="upld_img"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_img_no" name="upld_img" value="no" <?= $noChecked; ?> />
							<label for="upld_img_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_TXT')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%TXT%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%TXT%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_txt" name="upld_txt" value="yes" <?= $yesChecked; ?> />
							<label for="upld_txt"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_txt_no" name="upld_txt" value="no" <?= $noChecked; ?> />
							<label for="upld_txt_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_PDF')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%PDF%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%PDF%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_pdf" name="upld_pdf" value="yes" <?= $yesChecked; ?> />
							<label for="upld_pdf"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_pdf_no" name="upld_pdf" value="no" <?= $noChecked; ?> />
							<label for="upld_pdf_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_ODF')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%ODF%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%ODF%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_odf" name="upld_odf" value="yes" <?= $yesChecked; ?> />
							<label for="upld_odf"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_odf_no" name="upld_odf" value="no" <?= $noChecked; ?> />
							<label for="upld_odf_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_OOO')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%OOO%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%OOO%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_ooo" name="upld_ooo" value="yes" <?= $yesChecked; ?> />
							<label for="upld_ooo"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_ooo_no" name="upld_ooo" value="no" <?= $noChecked; ?> />
							<label for="upld_ooo_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_MSO')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%MSO%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%MSO%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_mso" name="upld_mso" value="yes" <?= $yesChecked; ?> />
							<label for="upld_mso"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_mso_no" name="upld_mso" value="no" <?= $noChecked; ?> />
							<label for="upld_mso_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_NMSO')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%NMSO%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%NMSO%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_nmso" name="upld_nmso" value="yes" <?= $yesChecked; ?> />
							<label for="upld_nmso"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_nmso_no" name="upld_nmso" value="no" <?= $noChecked; ?> />
							<label for="upld_nmso_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_RTF')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%RTF%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%RTF%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_rtf" name="upld_rtf" value="yes" <?= $yesChecked; ?> />
							<label for="upld_rtf"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_rtf_no" name="upld_rtf" value="no" <?= $noChecked; ?> />
							<label for="upld_rtf_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_UPLOAD_TYPE_HTML')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_upld_file_types'], '%HTML%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_upld_file_types'], '%HTML%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="upld_html" name="upld_html" value="yes" <?= $yesChecked; ?> />
							<label for="upld_html"><?= TRANS('YES'); ?></label>
							<input type="radio" id="upld_html_no" name="upld_html" value="no" <?= $noChecked; ?> />
							<label for="upld_html_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-image text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_UPLOAD_IMG')); ?></h6>
					
					<label for="img_max_width" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_MAXWIDTH')); ?></label>
					<div class="form-group col-md-4">
						<input type="number" class="form-control" name="img_max_width" id="img_max_width" required value="<?= $config['conf_upld_width']; ?>" />
					</div>
					<label for="img_max_height" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_MAXHEIGHT')); ?></label>
					<div class="form-group col-md-4">
						<input type="number" class="form-control" name="img_max_height" id="img_max_height" required value="<?= $config['conf_upld_height']; ?>" />
					</div>
					

					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-align-right text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('FORMATTING_BAR')); ?></h6>
					<label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_FORMATBAR_MURAL'); ?>"><?= firstLetterUp(TRANS('OPT_MURAL')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_formatBar'], '%mural%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_formatBar'], '%mural%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="formatMural" name="formatMural" value="yes" <?= $yesChecked; ?> />
							<label for="formatMural"><?= TRANS('YES'); ?></label>
							<input type="radio" id="formatMural_no" name="formatMural" value="no" <?= $noChecked; ?> />
							<label for="formatMural_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>
					<label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_FORMATBAR_TICKETS'); ?>"><?= firstLetterUp(TRANS('OPT_OCORRENCIAS')); ?></label>
					<div class="form-group col-md-4 ">
						<?php
						$yesChecked = (strpos($config['conf_formatBar'], '%oco%')) ? " checked" : "";
						$noChecked = (!strpos($config['conf_formatBar'], '%oco%')) ? " checked" : "";
						?>
						<div class="switch-field">
							<input type="radio" id="formatOco" name="formatOco" value="yes" <?= $yesChecked; ?> />
							<label for="formatOco"><?= TRANS('YES'); ?></label>
							<input type="radio" id="formatOco_no" name="formatOco" value="no" <?= $noChecked; ?> />
							<label for="formatOco_no"><?= TRANS('NOT'); ?></label>
						</div>
					</div>

					<h6 class="w-100 mt-5 ml-5 border-top p-4"><i class="fas fa-bell text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_SEND_MAIL_WRTY')); ?></h6>
					<label for="days_before_expire" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_DAYS_BEFORE')); ?></label>
					<div class="form-group col-md-4">
						<input type="number" class="form-control" name="days_before_expire" id="days_before_expire" required value="<?= $config['conf_days_bf']; ?>" />
					</div>
					<label for="area_to_alert" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_SEL_AREA')); ?></label>
					<div class="form-group col-md-4 ">
						<select class="form-control " name="area_to_alert" required id="area_to_alert">
							<?php
							$areas = getAreas($conn, 0, 1, 1);
							foreach ($areas as $area) {
							?>
								<option value="<?= $area['sis_id']; ?>" <?= ($area['sis_id'] == $config['conf_wrty_area']) ? 'selected' : ''; ?>><?= $area['sistema']; ?></option>
							<?php
							}
							?>
						</select>
					</div>

					<!-- $config['conf_prob_tipo_1'] -->
					<h6 class="w-100 mt-5 ml-5 border-top p-4" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELP_LABELS_ISSUES_CATEGORIES'); ?>"><i class="fas fa-tags text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('OPT_PROB_CATEG')); ?></h6>
					<label for="label_prob_tipo_1" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_PROB_LABEL1')); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control" name="label_prob_tipo_1" id="label_prob_tipo_1" required value="<?= $config['conf_prob_tipo_1']; ?>" />
					</div>
					<label for="label_prob_tipo_2" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_PROB_LABEL2')); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control" name="label_prob_tipo_2" id="label_prob_tipo_2" required value="<?= $config['conf_prob_tipo_2']; ?>" />
					</div>
					<label for="label_prob_tipo_3" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= firstLetterUp(TRANS('OPT_PROB_LABEL3')); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control" name="label_prob_tipo_3" id="label_prob_tipo_3" required value="<?= $config['conf_prob_tipo_3']; ?>" />
					</div>

					<!-- ---------------------------------------- -->
					<div class="row w-100"></div>
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">

						<input type="hidden" name="action" id="action" value="edit">
						<button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
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
	<!-- <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script> -->
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
	<script type="text/javascript">
		$(function() {

			$(function() {
				$('[data-toggle="popover"]').popover()
			});

			$('.popover-dismiss').popover({
				trigger: 'focus'
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
					url: './config_geral_process.php',
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

			$('.bt-edit').on("click", function() {
				$('#idLoad').css('display', 'block');
				var url = '<?= $_SERVER['PHP_SELF'] ?>?action=edit';
				$(location).prop('href', url);
			});

			$('#bt-cancel').on('click', function() {
				var url = '<?= $_SERVER['PHP_SELF'] ?>';
				$(location).prop('href', url);
			});
		});
	</script>
</body>

</html>