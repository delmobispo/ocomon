<?php session_start();
/*      Copyright 2020 Flávio Ribeiro

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
*/

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$post = $_POST;
$terms = "";

$cod = (isset($post['cod']) ? $post['cod'] : "");

$areaInfo =[];
if (!empty($cod)) {
    $areaInfo = getAreaInfo($conn, $cod);
}

// if (empty($cod)) {
//     return false;
// }

/* Para áreas que enviam chamados, todas são consideradas (exceto as desabilitadas) */
$sql = "SELECT sis_id, sistema, sis_atende FROM sistemas WHERE sis_status = 1  ";
$sql .= "ORDER BY sistema";

try {
    $res = $conn->query($sql);
}
catch (Exception $e) {
    return false;
}

/* Para manter a compatibilidade com versões antigas */
$table = "areaxarea_abrechamado";
$sqlTest = "SELECT * FROM {$table}";
try {
    $conn->query($sqlTest);
}
catch (Exception $e) {
    $table = "areaXarea_abrechamado";
}




/* Se for área de nível somente abertura então não exibo essa seção */
// if ((isset($areaInfo['atende']) && $areaInfo['atende'] == 1) OR empty($cod)) {
    ?>
    
    <div class="h6 w-100 my-4 border-top p-4"><i class="fas fa-angle-double-left text-secondary"></i>&nbsp;<?= TRANS('RECEIVE_TICKETS_FROM'); ?>:</div>
    <?php
    $noChecked = "checked";
    $yesChecked = "";
    foreach ($res->fetchall() as $row) {
        ?>
        <label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= $row['sistema']; ?></label>
            <div class="form-group col-md-4">
                <div class="switch-field">
                    <?php
                        /* Recebe chamados das áreas.. */
                        $sqlAreaFrom = "SELECT * FROM {$table} WHERE area = '" . $cod . "'  AND area_abrechamado = '" . $row['sis_id'] . "' ";
                        $resAreaFrom = $conn->query($sqlAreaFrom);
                        if ($resAreaFrom->rowCount()) {
                            $yesChecked = "checked";
                            $noChecked = "";
                        } else {
                            $yesChecked = "";
                            $noChecked = "checked";
                        }
                    ?>
                    <input class="areaFrom_yes" type="radio" id="areaFrom[<?= $row['sis_id']; ?>]" name="areaFrom[<?= $row['sis_id']; ?>]" value="yes" <?= $yesChecked;  ?> <?= (isset($areaInfo['atende']) && $areaInfo['atende'] == 0 ? 'disabled' : ''); ?>/>
                    <label for="areaFrom[<?= $row['sis_id']; ?>]"><?= TRANS('YES'); ?></label>
                    <input class="areaFrom_no" type="radio" id="areaFrom_no[<?= $row['sis_id']; ?>]" name="areaFrom[<?= $row['sis_id']; ?>]" value="no" <?= $noChecked; ?> <?= (isset($areaInfo['atende']) && $areaInfo['atende'] == 0 ? 'disabled' : ''); ?>/>
                    <label for="areaFrom_no[<?= $row['sis_id']; ?>]"><?= TRANS('NOT'); ?></label>
                </div>
            </div>
        <?php
    }
// }


/* Para áreas que recebem chamados, apenas áreas que prestam atendimento e estão ativas são consideradas */
$sql = "SELECT sis_id, sistema FROM sistemas WHERE sis_status = 1 AND sis_atende = 1 ";
$sql .= "ORDER BY sistema";

try {
    $res = $conn->query($sql);
}
catch (Exception $e) {
    return false;
}

?>
<div class="h6 w-100 my-4 border-top p-4"><i class="fas fa-angle-double-right text-secondary"></i>&nbsp;<?= TRANS('OPEN_TICKETS_TO'); ?>:</div>
<?php
$noChecked = "checked";
$yesChecked = "";
foreach ($res->fetchall() as $row) {
    ?>
    <label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= $row['sistema']; ?></label>
        <div class="form-group col-md-4">
            <div class="switch-field">
                <?php
                    /* Recebe chamados das áreas.. */
                    $sqlAreaTo = "SELECT * FROM {$table} WHERE area = '" . $row['sis_id'] . "'  AND area_abrechamado = '" . $cod . "' ";
                    $resAreaTo = $conn->query($sqlAreaTo);
                    if ($resAreaTo->rowCount()) {
                        $yesChecked = "checked";
                        $noChecked = "";
                    } else {
                        $yesChecked = "";
                        $noChecked = "checked";
                    }
                ?>
                <input type="radio" id="areaTo[<?= $row['sis_id']; ?>]" name="areaTo[<?= $row['sis_id']; ?>]" value="yes" <?= $yesChecked; ?>/>
                <label for="areaTo[<?= $row['sis_id']; ?>]"><?= TRANS('YES'); ?></label>
                <input type="radio" id="areaTo_no[<?= $row['sis_id']; ?>]" name="areaTo[<?= $row['sis_id']; ?>]" value="no" <?= $noChecked; ?> />
                <label for="areaTo_no[<?= $row['sis_id']; ?>]"><?= TRANS('NOT'); ?></label>
            </div>
        </div>
    <?php
}



// echo json_encode($data);