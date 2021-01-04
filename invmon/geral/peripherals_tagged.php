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

/* Para manter a compatibilidade com versões antigas */
$table = "equipxpieces";
$clausule = $QRY['componentexequip_ini'];
$sqlTest = "SELECT * FROM {$table}";
try {
    $conn->query($sqlTest);
}
catch (Exception $e) {
    $table = "equipXpieces";
    $clausule = $QRY['componenteXequip_ini'];
}

$type = "";


?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/switch_radio.css" />
	<link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" />
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
		<h4 class="my-4"><i class="fas fa-hdd text-secondary"></i>&nbsp;<?= TRANS('DETACHED_COMPONENTS'); ?></h4>
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

		$query = $clausule;
		$filtro = ""; //Variável que irá retornar qual é o filtro que está sendo aplicado na consulta.

		if (isset($_GET['cod'])) {
            $query .= " AND e.estoq_cod = ".$_GET['cod']." ";
		}

		if (isset($_POST['estoque_tipo'])  && $_POST['estoque_tipo']!=-1) {
			$query.= " AND e.estoq_tipo = ".$_POST['estoque_tipo']." ";
		}
		if (isset($_POST['estoque_sn']) && !empty($_POST['estoque_sn'])) {

			$query.= " AND lower(e.estoq_sn) = lower('".$_POST['estoque_sn']."') ";
		}
		if (isset($_POST['estoque_partnumber'])  && !empty($_POST['estoque_partnumber'])) {
			$query.= " AND lower(e.estoq_partnumber) = lower('".$_POST['estoque_partnumber']."') ";
		}
		if (isset($_POST['estoque_local'])  && $_POST['estoque_local']!=-1) {
			$query.= " AND e.estoq_local = ".$_POST['estoque_local']." ";
		}
		if (isset($_POST['estoque_tag']) && !empty($_POST['estoque_tag'])) {
			$query.= " AND e.estoq_tag_inv = '".$_POST['estoque_tag']."' ";
		}

		if (isset($_POST['estoque_unidade'])) {
			if ($_POST['estoque_unidade'] !='null')
				$query.= " AND e.estoq_tag_inst = ".$_POST['estoque_unidade']." ";
		}

		$query .=" ORDER BY i.item_nome, e.estoq_desc";

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
							<td class="line col_sequence">#</td>
							<td class="line col_model"><?= TRANS('COL_TYPE'); ?></td>
							<td class="line col_model"><?= TRANS('COL_MODEL'); ?></td>
							<td class="line col_type"><?= TRANS('SERIAL_NUMBER'); ?></td>
							<td class="line col_type"><?= TRANS('COL_PARTNUMBER'); ?></td>
							<td class="line col_type"><?= TRANS('DEPARTMENT'); ?></td>
							<td class="line col_type"><?= TRANS('COL_EQUIP'); ?></td>
							<td class="line editar" width="10%"><?= TRANS('BT_EDIT'); ?></td>
							<td class="line remover" width="10%"><?= TRANS('BT_REMOVE'); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						foreach ($resultado->fetchall() as $row) {

							?>
							<tr>
								<td class="line"><a onclick="redirect('<?= $_SERVER['PHP_SELF'] ?>?action=view&cod=<?= $row['estoq_cod']; ?>')"><?= $i; ?></a></td>
								<td class="line"><?= $row['item_nome']; ?></td>
								<td class="line"><?= $row['fabricante']." ".$row['modelo']." ".$row['capacidade']." ".$row['sufixo']; ?></td>
								<td class="line"><?= $row['estoq_sn']; ?></td>
								<td class="line"><?= $row['estoq_partnumber']; ?></td>
								<td class="line"><?= $row['local']; ?></td>

								<?php
								$link = "";
								if (!empty($row['eqp_equip_inv']) && $row['instEquipamento'] != '-1' && $row['instEquipamento'] != "") {
									$link = "<a onClick=\"popup('equipment_show.php?tag=".$row['eqp_equip_inv']."&unit=".$row['eqp_equip_inst']."')\">".NVL($row['instEquipamento']." - ".$row['eqp_equip_inv'])."</a>";
								}
								?>
								<td class="line"><?= $link; ?></td>


								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['estoq_cod']; ?>')"><?= TRANS('BT_EDIT'); ?></button></td>
								<td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['estoq_cod']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
							</tr>

							<?php
							$i++;
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
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="type" name="type" required>
                            <option value=""><?= TRANS('SEL_TYPE_ITEM'); ?></option>
                            <?php
                            $sql = "SELECT * FROM itens ORDER BY item_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['item_cod']; ?>"

                                ><?= $rowType['item_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>


					<label for="model_full" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MODEL'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="model_full" name="model_full" required>
                            <option value=""><?= TRANS('SEL_TYPE_ITEM'); ?></option>
                            
                        </select>
					</div>
                
                    <label for="serial_number" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SERIAL_NUMBER'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="serial_number" name="serial_number" required />
                    </div>
					
					<label for="part_number" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PARTNUMBER'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="part_number" name="part_number"  />
                    </div>

					<label for="asset_unit" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="asset_unit" name="asset_unit" required>
                            <option value=""><?= TRANS('OCO_SEL_UNIT'); ?></option>
                            <?php
                            $sql = "SELECT * FROM instituicao ORDER BY inst_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['inst_cod']; ?>"

                                ><?= $rowType['inst_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>


                    <label for="asset_tag" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ASSET_TAG'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="asset_tag" name="asset_tag"  />
					</div>

                    <label for="department" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="department" name="department" required>
                            <option value=""><?= TRANS('OCO_SEL_LOCAL'); ?></option>
                            <?php
                            $sql = "SELECT * FROM localizacao ORDER BY local";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['loc_id']; ?>"

                                ><?= $rowType['local']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					<label for="cost_center" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COST_CENTER'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="cost_center" name="cost_center" required>
                            <option value=""><?= TRANS('COST_CENTER'); ?></option>
                            <?php
                            $sql = "SELECT * FROM " . TB_CCUSTO . " ORDER BY descricao";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['codigo']; ?>"

                                ><?= $rowType['descricao']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					
                    <label for="purchase_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PURCHASE_DATE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="purchase_date" name="purchase_date" autocomplete="off"  />
					</div>
					
					<label for="supplier" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_SEL_VENDOR'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="supplier" name="supplier" required>
                            <option value=""><?= TRANS('OCO_SEL_VENDOR'); ?></option>
                            <?php
                            $sql = "SELECT * FROM fornecedores ORDER BY forn_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['forn_cod']; ?>"

                                ><?= $rowType['forn_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					

					<label for="invoice_number" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_NF'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="invoice_number" name="invoice_number"  />
					</div>
					
					<label for="price" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_VALUE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="price" name="price"  />
					</div>
					
					
					
					<label for="time_of_warranty" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_SEL_WARRANTY'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="time_of_warranty" name="time_of_warranty" required>
                            <option value=""><?= TRANS('FIELD_TIME_MONTH'); ?></option>
                            <?php
                            $sql = "SELECT * FROM tempo_garantia ORDER BY tempo_meses";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['tempo_cod']; ?>"

                                ><?= $rowType['tempo_meses'] . ' ' . TRANS('MONTHS'); ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					

					<label for="condition" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('STATE'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="condition" name="condition" required>
                            <option value=""><?= TRANS('STATE'); ?></option>
                            <?php
                            $sql = "SELECT * FROM situacao ORDER BY situac_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['situac_cod']; ?>"

                                ><?= $rowType['situac_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					<div class="w-100"></div>
					<label for="additional_info" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ENTRY_TYPE_ADDITIONAL_INFO'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control " id="additional_info" name="additional_info"></textarea>
					</div>

					<h6 class="w-100 mt-4 ml-5 border-top p-4"><i class="fas fa-laptop text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('ASSOC_EQUIP_PIECES')); ?></h6>

					<label class="col-md-2 col-form-label text-md-right"><?= TRANS('IN_EQUIPMENT'); ?></label>
						<div class="form-group col-md-10 ">
							<div class="switch-field">
								<?php
								$yesChecked = "checked";
								$noChecked = "";
								?>
								<input type="radio" id="in_equipment" name="in_equipment" value="yes" <?= $yesChecked; ?> />
								<label for="in_equipment"><?= TRANS('YES'); ?></label>
								<input type="radio" id="in_equipment_no" name="in_equipment" value="no" <?= $noChecked; ?> />
								<label for="in_equipment_no"><?= TRANS('NOT'); ?></label>
							</div>
						</div>

					<label for="equipment_unit" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="equipment_unit" name="equipment_unit" required>
                            <option value=""><?= TRANS('OCO_SEL_UNIT'); ?></option>
                            <?php
                            $sql = "SELECT * FROM instituicao ORDER BY inst_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['inst_cod']; ?>"

                                ><?= $rowType['inst_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>


                    <label for="equipment_tag" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ASSET_TAG'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="equipment_tag" name="equipment_tag"  />
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
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="type" name="type" required>
                            <option value=""><?= TRANS('SEL_TYPE_ITEM'); ?></option>
                            <?php
                            $sql = "SELECT * FROM itens ORDER BY item_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['item_cod']; ?>"
									<?= ($rowType['item_cod'] == $row['estoq_tipo'] ? ' selected' : ''); ?>
                                ><?= $rowType['item_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>


					<label for="model_full" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MODEL'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="model_full" name="model_full" required>
							<option value=""><?= TRANS('SEL_TYPE_ITEM'); ?></option>
                            
                        </select>
					</div>
                
                    <label for="serial_number" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SERIAL_NUMBER'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="serial_number" name="serial_number" value="<?= $row['estoq_sn']; ?>" required />
                    </div>
					
					<label for="part_number" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PARTNUMBER'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="part_number" name="part_number" value="<?= $row['estoq_partnumber']; ?>" />
                    </div>

					<label for="asset_unit" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="asset_unit" name="asset_unit" required>
                            <option value=""><?= TRANS('OCO_SEL_UNIT'); ?></option>
                            <?php
                            $sql = "SELECT * FROM instituicao ORDER BY inst_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['inst_cod']; ?>"
									<?= ($row['estoq_tag_inst'] == $rowType['inst_cod'] ? ' selected' : '' ); ?>
                                ><?= $rowType['inst_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>


                    <label for="asset_tag" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ASSET_TAG'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="asset_tag" name="asset_tag" value="<?= $row['estoq_tag_inv']; ?>" />
					</div>

                    <label for="department" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="department" name="department" required>
                            <option value=""><?= TRANS('OCO_SEL_LOCAL'); ?></option>
                            <?php
                            $sql = "SELECT * FROM localizacao ORDER BY local";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['loc_id']; ?>"
									<?= ($row['loc_id'] == $rowType['loc_id'] ? ' selected' : ''); ?>
                                ><?= $rowType['local']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					<label for="cost_center" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COST_CENTER'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="cost_center" name="cost_center" required>
                            <option value=""><?= TRANS('COST_CENTER'); ?></option>
                            <?php
                            $sql = "SELECT * FROM " . TB_CCUSTO . " ORDER BY descricao";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['codigo']; ?>"
								<?= ($row['codigo'] == $rowType['codigo'] ? ' selected' : ''); ?>
                                ><?= $rowType['descricao']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					
                    <label for="purchase_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PURCHASE_DATE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="purchase_date" name="purchase_date" autocomplete="off" value="<?= dateScreen($row['estoq_data_compra'],1); ?>" />
					</div>
					
					<label for="supplier" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_SEL_VENDOR'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="supplier" name="supplier" required>
                            <option value=""><?= TRANS('OCO_SEL_VENDOR'); ?></option>
                            <?php
                            $sql = "SELECT * FROM fornecedores ORDER BY forn_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['forn_cod']; ?>"
									<?= ($row['forn_cod'] == $rowType['forn_cod'] ? ' selected' : ''); ?>
                                ><?= $rowType['forn_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					

					<label for="invoice_number" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_NF'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="invoice_number" name="invoice_number" value="<?= $row['estoq_nf']; ?>" />
					</div>
					
					<label for="price" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_VALUE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="price" name="price" value="<?= priceScreen($row['estoq_value']); ?>" />
					</div>
					
					
					
					<label for="time_of_warranty" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_SEL_WARRANTY'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="time_of_warranty" name="time_of_warranty" required>
                            <option value=""><?= TRANS('FIELD_TIME_MONTH'); ?></option>
                            <?php
                            $sql = "SELECT * FROM tempo_garantia ORDER BY tempo_meses";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['tempo_cod']; ?>"
									<?= ($row['tempo_cod'] == $rowType['tempo_cod'] ? ' selected' : ''); ?>
                                ><?= $rowType['tempo_meses'] . ' ' . TRANS('MONTHS'); ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					

					<label for="condition" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('STATE'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="condition" name="condition" required>
                            <option value=""><?= TRANS('STATE'); ?></option>
                            <?php
                            $sql = "SELECT * FROM situacao ORDER BY situac_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['situac_cod']; ?>"
									<?= ($row['situac_cod'] == $rowType['situac_cod'] ? ' selected' : ''); ?>
                                ><?= $rowType['situac_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					<div class="w-100"></div>
					<label for="additional_info" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ENTRY_TYPE_ADDITIONAL_INFO'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control " id="additional_info" name="additional_info"><?= $row['estoq_comentario']; ?></textarea>
					</div>

					<h6 class="w-100 mt-4 ml-5 border-top p-4"><i class="fas fa-laptop text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('ASSOC_EQUIP_PIECES')); ?></h6>

					<label class="col-md-2 col-form-label text-md-right"><?= TRANS('IN_EQUIPMENT'); ?></label>
						<div class="form-group col-md-10 ">
							<div class="switch-field">
								<?php
								$yesChecked = ($row['eqp_equip_inst'] != '' && $row['eqp_equip_inv'] != '' ? 'checked' : '');
								$noChecked = ($row['eqp_equip_inst'] == '' || $row['eqp_equip_inv'] == '' ? 'checked' : '');
								?>
								<input type="radio" id="in_equipment" name="in_equipment" value="yes" <?= $yesChecked; ?> />
								<label for="in_equipment"><?= TRANS('YES'); ?></label>
								<input type="radio" id="in_equipment_no" name="in_equipment" value="no" <?= $noChecked; ?> />
								<label for="in_equipment_no"><?= TRANS('NOT'); ?></label>
							</div>
						</div>

					<label for="equipment_unit" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="equipment_unit" name="equipment_unit" required>
                            <option value=""><?= TRANS('OCO_SEL_UNIT'); ?></option>
                            <?php
                            $sql = "SELECT * FROM instituicao ORDER BY inst_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['inst_cod']; ?>"
									<?= ($row['eqp_equip_inst'] == $rowType['inst_cod'] ? ' selected' : ''); ?>
                                ><?= $rowType['inst_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>


                    <label for="equipment_tag" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ASSET_TAG'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="equipment_tag" name="equipment_tag" value="<?= $row['eqp_equip_inv']; ?>" />
					</div>
					
                
                    
                    
                    

					<div class="row w-100"></div>
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">

						<input type="hidden" name="old_department" value="<?= $row['loc_id']; ?>" id="old_department"/>
						<input type="hidden" name="old_equipment_unit" value="<?= $row['eqp_equip_inst']; ?>" id="old_equipment_unit"/>
						<input type="hidden" name="old_equipment_tag" value="<?= $row['eqp_equip_inv']; ?>" id="old_equipment_tag"/>
						<input type="hidden" name="model_selected" value="<?= $row['estoq_desc']; ?>" id="model_selected"/>
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
		} else

		if ((isset($_GET['action']) && $_GET['action'] == "view") && empty($_POST['submit'])) {

			$row = $resultado->fetch();
			?>
			<button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['estoq_cod']; ?>')"><?= TRANS('BT_EDIT'); ?></button>&nbsp;
			
			<button type="button" class="btn btn-info btn-sm" onclick="popup_alerta('piece_hist.php?popup=true&piece_id=<?= $row['estoq_cod']; ?>')"><?= TRANS('MNL_CON_HIST'); ?></button>&nbsp;

			<button type="button" class="btn btn-info btn-sm" onclick="popup_alerta('consulta_garantia_piece.php?popup=true&piece_id=<?= $row['estoq_cod']; ?>')"><?= TRANS('LINK_GUARANT'); ?></button><br /><br />

			

			<form name="form_view" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form_view" >
				<?= csrf_input(); ?>
				<div class="form-group row my-4">
                    
					
					<label for="type_view" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_TYPE'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="type_view" name="type_view" disabled>
                            <option value=""><?= TRANS('SEL_TYPE_ITEM'); ?></option>
                            <?php
                            $sql = "SELECT * FROM itens ORDER BY item_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['item_cod']; ?>"
									<?= ($rowType['item_cod'] == $row['estoq_tipo'] ? ' selected' : ''); ?>
                                ><?= $rowType['item_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>


					<label for="model_full_view" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MODEL'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="model_full_view" name="model_full_view" disabled>
							<?php
							$sql = "SELECT * FROM modelos_itens WHERE mdit_cod = '" . $row['estoq_desc']. "' ";
							dump($sql);
							$res = $conn->query($sql);
							$rowType = $res->fetch();
							?>
						
							<option value="<?= $rowType['mdit_cod']; ?>"><?= $rowType['mdit_fabricante'] ." ". $rowType['mdit_desc'] . " " . $rowType['mdit_desc_capacidade'] . " " . $rowType['mdit_sufixo'];?></option>
                            
                        </select>
					</div>
                
                    <label for="serial_number_view" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SERIAL_NUMBER'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="serial_number_view" name="serial_number_view" value="<?= $row['estoq_sn']; ?>" disabled />
                    </div>
					
					<label for="part_number_view" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PARTNUMBER'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="part_number_view" name="part_number_view" value="<?= $row['estoq_partnumber']; ?>" disabled />
                    </div>

					<label for="asset_unit_view" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="asset_unit_view" name="asset_unit_view" disabled>
                            <option value=""><?= TRANS('OCO_SEL_UNIT'); ?></option>
                            <?php
                            $sql = "SELECT * FROM instituicao ORDER BY inst_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['inst_cod']; ?>"
									<?= ($row['estoq_tag_inst'] == $rowType['inst_cod'] ? ' selected' : '' ); ?>
                                ><?= $rowType['inst_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>


                    <label for="asset_tag_view" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ASSET_TAG'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="asset_tag_view" name="asset_tag_view" value="<?= $row['estoq_tag_inv']; ?>" disabled/>
					</div>

                    <label for="department_view" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="department_view" name="department_view" disabled>
                            <option value=""><?= TRANS('OCO_SEL_LOCAL'); ?></option>
                            <?php
                            $sql = "SELECT * FROM localizacao ORDER BY local";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['loc_id']; ?>"
									<?= ($row['loc_id'] == $rowType['loc_id'] ? ' selected' : ''); ?>
                                ><?= $rowType['local']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					<label for="cost_center_view" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COST_CENTER'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="cost_center_view" name="cost_center_view" disabled>
                            <option value=""><?= TRANS('COST_CENTER'); ?></option>
                            <?php
                            $sql = "SELECT * FROM " . TB_CCUSTO . " ORDER BY descricao";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['codigo']; ?>"
								<?= ($row['codigo'] == $rowType['codigo'] ? ' selected' : ''); ?>
                                ><?= $rowType['descricao']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					
                    <label for="purchase_date_view" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PURCHASE_DATE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="purchase_date_view" name="purchase_date_view" autocomplete="off" value="<?= dateScreen($row['estoq_data_compra'],1); ?>" disabled />
					</div>
					
					<label for="supplier_view" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_SEL_VENDOR'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="supplier_view" name="supplier_view" disabled>
                            <option value=""><?= TRANS('OCO_SEL_VENDOR'); ?></option>
                            <?php
                            $sql = "SELECT * FROM fornecedores ORDER BY forn_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['forn_cod']; ?>"
									<?= ($row['forn_cod'] == $rowType['forn_cod'] ? ' selected' : ''); ?>
                                ><?= $rowType['forn_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					

					<label for="invoice_number_view" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_NF'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="invoice_number_view" name="invoice_number_view" value="<?= $row['estoq_nf']; ?>" disabled/>
					</div>
					
					<label for="price_view" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_VALUE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="price_view" name="price_view" value="<?= priceScreen($row['estoq_value']); ?>" disabled/>
					</div>
					
					
					
					<label for="time_of_warranty_view" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_SEL_WARRANTY'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="time_of_warranty_view" name="time_of_warranty_view" disabled>
                            <option value=""><?= TRANS('FIELD_TIME_MONTH'); ?></option>
                            <?php
                            $sql = "SELECT * FROM tempo_garantia ORDER BY tempo_meses";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['tempo_cod']; ?>"
									<?= ($row['tempo_cod'] == $rowType['tempo_cod'] ? ' selected' : ''); ?>
                                ><?= $rowType['tempo_meses'] . ' ' . TRANS('MONTHS'); ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					

					<label for="condition_view" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('STATE'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="condition_view" name="condition_view" disabled>
                            <option value=""><?= TRANS('STATE'); ?></option>
                            <?php
                            $sql = "SELECT * FROM situacao ORDER BY situac_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['situac_cod']; ?>"
									<?= ($row['situac_cod'] == $rowType['situac_cod'] ? ' selected' : ''); ?>
                                ><?= $rowType['situac_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>

					<div class="w-100"></div>
					<label for="additional_info_view" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ENTRY_TYPE_ADDITIONAL_INFO'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control " id="additional_info_view" name="additional_info_view" disabled><?= $row['estoq_comentario']; ?></textarea>
					</div>

					<h6 class="w-100 mt-4 ml-5 border-top p-4"><i class="fas fa-laptop text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('ASSOC_EQUIP_PIECES')); ?></h6>

					<label class="col-md-2 col-form-label text-md-right"><?= TRANS('IN_EQUIPMENT'); ?></label>
						<div class="form-group col-md-10 ">
							<div class="switch-field">
								<?php
								$yesChecked = ($row['eqp_equip_inst'] != '' && $row['eqp_equip_inv'] != '' ? 'checked' : '');
								$noChecked = ($row['eqp_equip_inst'] == '' || $row['eqp_equip_inv'] == '' ? 'checked' : '');
								?>
								<input type="radio" id="in_equipment_view" name="in_equipment_view" value="yes" <?= $yesChecked; ?> disabled />
								<label for="in_equipment_view"><?= TRANS('YES'); ?></label>
								<input type="radio" id="in_equipment_view_no" name="in_equipment_view" value="no" <?= $noChecked; ?> disabled />
								<label for="in_equipment_view_no"><?= TRANS('NOT'); ?></label>
							</div>
						</div>

					<label for="equipment_unit_view" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control sel2" id="equipment_unit_view" name="equipment_unit_view" disabled>
                            <option value=""><?= TRANS('OCO_SEL_UNIT'); ?></option>
                            <?php
                            $sql = "SELECT * FROM instituicao ORDER BY inst_nome";
                            $exec_sql = $conn->query($sql);
                            foreach ($exec_sql->fetchAll() as $rowType) {
                                ?>
								<option value="<?= $rowType['inst_cod']; ?>"
									<?= ($row['eqp_equip_inst'] == $rowType['inst_cod'] ? ' selected' : ''); ?>
                                ><?= $rowType['inst_nome']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</div>


                    <label for="equipment_tag_view" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ASSET_TAG'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="equipment_tag_view" name="equipment_tag_view" value="<?= $row['eqp_equip_inv']; ?>" disabled/>
					</div>
					

					<div class="row w-100"></div>
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">

						
					</div>
					<div class="form-group col-12 col-md-2">
						<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_RETURN'); ?></button>
					</div>

				</div>
			</form>
			<?php
		}
		?>
	</div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/jquery/plentz-jquery-maskmoney/dist/jquery.maskMoney.min.js"></script>
    <script src="../../includes/components/jquery/jquery.initialize.min.js"></script>
    <script src="../../includes/components/select2/dist-2/js/select2.min.js"></script>
    <script src="../../includes/components/select2/dist-2/js/i18n/pt-BR.js"></script>
	<script type="text/javascript" src="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.js"></script>
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
			
			/* Carregamento dos modelos com base na seleção de tipo */
			showModelsByType($('#model_selected').val() ?? '');
			$('#type').on('change', function() {
				showModelsByType();
			});
			/* Final do carregamento dos modelos */


			$("#purchase_date").datepicker({
				dateFormat: 'dd/mm/yy',
				changeMonth: true,
				dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
				dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
				dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
				monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro', 'Janeiro'],
				monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
			});

			/* Trazer os parâmetros do banco a partir da opção que será criada para internacionaliação */
			$('#price').maskMoney({
                prefix:'R$ ',
                thousands:'.', 
                decimal:',', 
                allowZero: false, 
                affixesStay: false
            });


			/* Controle para liberar ou não os campos de identificação de equipamento associado */
			if (!$('#in_equipment').is(":checked")) {
				$("#equipment_unit").val("").change().prop('disabled',true);
				$('#equipment_tag').prop('disabled',true).val('');
				$('#department').prop('disabled',false);
			} else {
				$('#equipment_unit').prop('disabled',false);
				$('#equipment_tag').prop('disabled',false);
				$('#department').prop('disabled',true);
			}

			$('[name="in_equipment"]').on('change', function(){
				if ($(this).val() == "no") {
					$("#equipment_unit").val("").change().prop('disabled',true);
					$('#equipment_tag').prop('disabled',true).val('');
					$('#department').prop('disabled',false);
				} else {
					$('#equipment_unit').prop('disabled',false);
					$('#equipment_tag').prop('disabled',false);
					$('#department').prop('disabled',true);
				}
			});
			/* Final do controle para liberar ou não os campos de identificação de equipamento associado */




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
					url: './peripherals_tagged_process.php',
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


		function showModelsByType (selected_id = '') {
			/* Popular os modelos de acordo com o tipo selecionado */
			if ($('#model_full').length > 0) {
				
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});
				
				$.ajax({
					url: './get_models_by_type.php',
					method: 'POST',
					dataType: 'json',
					data: {
						type: $('#type').val(),
						model_selected: $('#model_selected').val() ?? '',
					},
				}).done(function(response) {
					$('#model_full').empty().append('<option value=""><?= TRANS('SEL_MODEL'); ?></option>');
					for (var i in response) {
						var option = '<option value="' + response[i].mdit_cod + '">' + response[i].mdit_fabricante + ' ' + response[i].mdit_desc + ' ' + response[i].mdit_desc_capacidade + ' ' + response[i].mdit_sufixo + '</option>';
						$('#model_full').append(option);

						if (selected_id !== '') {
							$('#model_full').val(selected_id).change();
						}
					}
				});
			}
		}



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
				url: './peripherals_tagged_process.php',
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