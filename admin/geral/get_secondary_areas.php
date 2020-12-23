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

$areaAdmin = 0;
if (isset($_SESSION['s_area_admin']) && $_SESSION['s_area_admin'] == '1' && $_SESSION['s_nivel'] != '1') {
    $areaAdmin = 1;
}

$primary_area = (isset($post['primary_area']) ? $post['primary_area'] : "");

// && isset($post['action']) && $post['action'] == 'new'
if (empty($primary_area) ) {
    return false;
}

if (isset($post['level']) && (($post['level'] == 3) || ($post['level'] == 5))) {
    return false;
}

$terms .= (!empty($primary_area) ? "AND sis_id NOT IN ({$primary_area}) " : "");


$sql = "SELECT sis_id, sistema FROM sistemas WHERE sis_atende = 1 AND sis_status = 1 {$terms}";

$sql .= "ORDER BY sistema";

try {
    $res = $conn->query($sql);
}
catch (Exception $e) {
    return false;
}

$uareas = [];
?>
<div class="h6 w-100 my-4 border-top p-4"><?= TRANS('SECONDARY_AREAS'); ?></div>
<?php
$noChecked = "checked";
$yesChecked = "";
foreach ($res->fetchall() as $row) {
    ?>
    <label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= $row['sistema']; ?></label>
        <div class="form-group col-md-4 switch-field">
            <?php
            if (!empty($cod)) {
                $sqlUarea = "SELECT * FROM usuarios_areas WHERE uarea_uid=" . $cod . " AND uarea_sid = " . $row['sis_id'] . " ";
                $resArea = $conn->query($sqlUarea);
                $rowArea = $resArea->fetch();

                $yesChecked = ($rowArea && $rowArea['uarea_sid'] == $row['sis_id'] ? "checked" : "");
                $noChecked = ($yesChecked == "" ? "checked" : "");
            }
            ?>
            <input type="radio" id="secondary_area[<?= $row['sis_id']; ?>]" name="secondary_area[<?= $row['sis_id']; ?>]" value="yes" <?= $yesChecked; ?>/>
            <label for="secondary_area[<?= $row['sis_id']; ?>]"><?= TRANS('YES'); ?></label>
            <input type="radio" id="secondary_area_no[<?= $row['sis_id']; ?>]" name="secondary_area[<?= $row['sis_id']; ?>]" value="no" <?= $noChecked; ?> />
            <label for="secondary_area_no[<?= $row['sis_id']; ?>]"><?= TRANS('NOT'); ?></label>
        </div>
    <?php
}




// echo json_encode($data);