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

$_SESSION['s_page_invmon'] = $_SERVER['PHP_SELF'];

$config = getConfig($conn);
$hasFiles = 0;

$type = "";


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
	<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2.min.css" />
	<!-- <link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2-themebt4.css" /> -->
	<link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2-bootstrap4.min.css" />

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
		<h4 class="my-4"><i class="fas fa-clone text-secondary"></i>&nbsp;<?= TRANS('MNL_COMPONENTES_MODEL'); ?></h4>
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

        $query = "SELECT * FROM modelos_itens, itens WHERE mdit_tipo = item_cod 
                ";

        if (isset($_GET['type'])) {
            $query .= " AND mdit_tipo = '".$_GET['type']."' ";
            $type = $_GET['type'];
        }


		if (isset($_GET['cod'])) {
            $query .= " AND mdit_cod = ".$_GET['cod']." ";
		}
        $query .=" ORDER BY item_nome, mdit_fabricante, mdit_desc, mdit_desc_capacidade";
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
							<td class="line col_type"><?= TRANS('COL_TYPE'); ?></td>
							<td class="line col_model"><?= TRANS('COL_MODEL'); ?></td>
							<td class="line editar" width="10%"><?= TRANS('BT_EDIT'); ?></td>
							<td class="line remover" width="10%"><?= TRANS('BT_REMOVE'); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php

						foreach ($resultado->fetchall() as $row) {

						?>
							<tr>
								<td class="line"><?= $row['item_nome']; ?></td>
								<td class="line"><?= $row['mdit_fabricante']." ".$row['mdit_desc']." ".$row['mdit_desc_capacidade']." ".$row['mdit_sufixo']; ?></td>
								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['mdit_cod']; ?>')"><?= TRANS('BT_EDIT'); ?></button></td>
								<td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['mdit_cod']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
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
			<form name="form" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form" >
				<?= csrf_input(); ?>
				<div class="form-group row my-4">
                    
                    <label for="type" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_TYPE'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control sel2" id="type" name="type" required>
                            <option value=""><?= TRANS('SEL_TYPE_ITEM'); ?></option>
                            <?php
                            $sql = "SELECT * FROM itens ORDER BY item_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
                                <option value="<?= $rowType['item_cod']; ?>"
                                    <?= ($rowType['item_cod'] == $type ? ' selected' : ''); ?>
                                ><?= $rowType['item_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                
                    <label for="model_nanufacturer" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MANUFACTURE'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="model_nanufacturer" list="manufacturers" name="model_nanufacturer" required />
                    </div>
                    <datalist id="manufacturers"></datalist>

                    <label for="model_name" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MODEL'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="model_name" name="model_name" required />
                    </div>

                    <label for="model_capacity" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CAPACITY'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="model_capacity" name="model_capacity"  />
                    </div>
                    
                    <label for="capacity_sufix" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_SUFIX'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="capacity_sufix" name="capacity_sufix"  />
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
			<form name="form" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form" >
				<?= csrf_input(); ?>
				<div class="form-group row my-4">
                    
                    <label for="type" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_TYPE'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control sel2" id="type" name="type" required>
                            <option value=""><?= TRANS('SEL_TYPE_ITEM'); ?></option>
                            <?php
                            $sql = "SELECT * FROM itens ORDER BY item_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
                                <option value="<?= $rowType['item_cod']; ?>"
                                    <?= ($rowType['item_cod'] == $row['item_cod'] ? ' selected' : ''); ?>
                                ><?= $rowType['item_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                
                    <label for="model_nanufacturer" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MANUFACTURE'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="model_nanufacturer" list="manufacturers" name="model_nanufacturer" value="<?= $row['mdit_fabricante']; ?>" required />
                    </div>
                    <datalist id="manufacturers"></datalist>

                    <label for="model_name" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MODEL'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="model_name" name="model_name" value="<?= $row['mdit_desc']; ?>" required />
                    </div>

                    <label for="model_capacity" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CAPACITY'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="model_capacity" name="model_capacity" value="<?= $row['mdit_desc_capacidade']; ?>"  />
                    </div>
                    
                    <label for="capacity_sufix" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_SUFIX'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="capacity_sufix" name="capacity_sufix" value="<?= $row['mdit_sufixo']; ?>"  />
                    </div>

					<div class="row w-100"></div>
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">
                        <input type="hidden" name="cod" value="<?= $_GET['cod']; ?>">
                        <input type="hidden" name="action" id="action" value="edit">
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
    <script src="../../includes/components/jquery/jquery.initialize.min.js"></script>
    <script src="../../includes/components/select2/dist-2/js/select2.min.js"></script>
    <script src="../../includes/components/select2/dist-2/js/i18n/pt-BR.js"></script>
	<!-- <script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script> -->
	<script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
	<script type="text/javascript">
		$(function() {

            $('.sel2').addClass('new-select2');

            $('.new-select2').select2({
                // placeholder: {
                //     text: 'Todos'
                // },
                theme: 'bootstrap4',
                minimumResultsForSearch: 10,
                language: 'pt-BR',
            });

            $(window).resize(function() {
                $('.new-select2').select2({
                    theme: 'bootstrap4',
                    minimumResultsForSearch: 10,
                    language: 'pt-BR',
                });
            });


            if ($('#table_lists').length > 0) {
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
            }
            

            /* Autocompletar os fabricantes */
            if ($('#manufacturers').length > 0) {
                $.ajax({
                    url: './get_peripherals_manufacturers.php',
                    method: 'POST',
                    dataType: 'json',
                }).done(function(response) {
                    for (var i in response) {
                        var option = '<option value="' + response[i].manufacturer + '"/>';
                        $('#manufacturers').append(option);
                    }
                });
            }


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

                // var form = $('form').get(0);
				$("#idSubmit").prop("disabled", true);
				$.ajax({
					url: './peripherals_models_process.php',
					method: 'POST',
                    data: $('#form').serialize(),
                    // data: new FormData(form),
                    dataType: 'json',
                    
                    // cache: false,
				    // processData: false,
				    // contentType: false,
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
				url: './peripherals_models_process.php',
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