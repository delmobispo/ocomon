<?php

/*                        Copyright 2020 Flávio Ribeiro

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
 */session_start();

if ($_SESSION['s_logado'] != 1 || $_SESSION['s_nivel'] != 1) {
    exit;
}

include_once ("../../includes/include_basics_only.php");
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();
/* Database connection end */

// storing  request (ie, get/post) global array to a variable  
// $requestData = $_REQUEST;
$requestData = $_POST;

// var_dump($requestData); exit();
$origin = "users.php";

$columns = array(
	// datatable column index  => database column name
    0 => 'utmp_nome',
    1 => 'utmp_login',
    2 => 'utmp_email',
    3 => 'utmp_date'
    
    // 8 => 'user_id'
);

$terms = "";
if (isset($requestData['areaAdmin']) && $requestData['areaAdmin'] == "1") {
    // $terms .= " AND s.sis_id = '" . $_SESSION['s_area'] . "' ";
}

// getting total number records without any search
$sql = "SELECT * FROM utmp_usuarios 
        WHERE 1 = 1 {$terms};
";


// var_dump($sql);


$sqlResult = $conn->query($sql);
$totalData = $sqlResult->rowCount();
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

$sql = "SELECT * FROM utmp_usuarios 
";

$sql.=" WHERE 1 = 1 {$terms} ";


if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter

    $sql.=" AND ( utmp_nome LIKE '%".$requestData['search']['value']."%' ";  
	$sql.=" OR utmp_email LIKE '%".$requestData['search']['value']."%' )";
	
}


$sqlResult = $conn->query($sql);
$totalFiltered = $sqlResult->rowCount();
// echo($columns[$requestData['order'][0]['column']]);

$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
//  dump($sql);
$sqlResult = $conn->query($sql);

$data = array();
foreach ($sqlResult->fetchall() as $row) {
	$nestedData=array(); 

	$nestedData[] = $row['utmp_nome'];
    $nestedData[] = $row['utmp_login'];
    $nestedData[] = $row['utmp_email'];
    $nestedData[] = dateScreen($row['utmp_date']);
    $nestedData[] = "<button type='button' class='btn btn-success btn-sm' onclick=\"confirmUser('" . $row['utmp_cod'] . "')\">" . TRANS('BT_OK') . "</button>";
    $nestedData[] = "<button type='button' class='btn btn-danger btn-sm' onclick=\"confirmDeleteModalTmp('" . $row['utmp_cod'] . "')\">" . TRANS('BT_REMOVE') . "</button>";
	$data[] = $nestedData;
}


$json_data = array(
    "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
    "recordsTotal"    => intval( $totalData ),  // total number of records
    "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
    "data"            => $data   // total data array
    );

echo json_encode($json_data);  // send data as json format

?>