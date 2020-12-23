<?php session_start();
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
*/

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

use includes\classes\ConnectPDO;
$conn = ConnectPDO::getInstance();


$post = $_POST;

$config = getConfig($conn);
$exception = "";
$terms = "";
$data = [];
$data['script_id'] = $post['cod'];
$data['action'] = (isset($post['action']) ? noHtml($post['action']) : "");
$data['disabled'] = ($data['action'] == "" || $data['action'] == 'details' ? " disabled" : "");


$sql = "SELECT * FROM prob_x_script 
					LEFT JOIN problemas on prob_id = prscpt_prob_id 
					WHERE prscpt_scpt_id = '" . $data['script_id'] . "' 
					AND prscpt_prob_id = prob_id 
					GROUP BY problema 
                    ORDER BY problema ";

try {
    $res = $conn->query($sql);

    ?>
    <table id="table_script_type_of_issues" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
        <thead>
            <tr class="header">
                <td class='line area'><?= TRANS('AREA'); ?></td>
                <td class='line problema'><?= TRANS('ISSUE_TYPE'); ?></td>
                <td class='line aberto_por'><?= $config['conf_prob_tipo_1']; ?></td>
                <td class='line aberto_por'><?= $config['conf_prob_tipo_2']; ?></td>
                <td class='line aberto_por'><?= $config['conf_prob_tipo_3']; ?></td>
            </tr>
        </thead>
    <?php
    foreach ($res->fetchall() as $row) {

        $sql = "SELECT * FROM problemas AS p 
                    LEFT JOIN sistemas AS s ON p.prob_area = s.sis_id 
                    LEFT JOIN sla_solucao AS sl ON sl.slas_cod = p.prob_sla 
                    LEFT JOIN prob_tipo_1 AS pt1 ON pt1.probt1_cod = p.prob_tipo_1 
                    LEFT JOIN prob_tipo_2 AS pt2 ON pt2.probt2_cod = p.prob_tipo_2 
                    LEFT JOIN prob_tipo_3 AS pt3 ON pt3.probt3_cod = p.prob_tipo_3 
                WHERE prob_id = '".$row['prob_id']."'  
                ORDER BY s.sistema, p.problema";
        
        $resCat = $conn->query($sql);
        $rowCat = $resCat->fetch();

        $area = TRANS('OCO_SEL_ANY');
        if (!empty($rowCat['sistema'])){
            $area = $rowCat['sistema'];
        }
        ?>
        <tr>
            <td class="line"><?= $area; ?></td>
            <?php
            if (!empty($data['disabled'])) {
                ?>
                <td class="line"><?= $rowCat['problema']; ?></td>
                <?php
            } else {
                ?>
                <td class="line"><span class="align-top"><i class="fas fa-trash-alt text-danger"></i></span><input type="checkbox" name="delProb[<?= $row['prscpt_id']; ?>]" value="<?= $row['prscpt_id']; ?>"/>&nbsp;<?= $rowCat['problema']; ?></td>
                <?php
            }
            ?>
            <td class="line"><?= $rowCat['probt1_desc']; ?></td>
            <td class="line"><?= $rowCat['probt2_desc']; ?></td>
            <td class="line"><?= $rowCat['probt3_desc']; ?></td>
        </tr>
        <?php
    }
    ?>
    </table>
    <?php
}
catch (Exception $e) {
    $exception .= $e->getMessage();
    echo $exception;
}
?>

