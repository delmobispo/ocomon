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
    0 => 'nome',
    1 => 'login',
    2 => 'sistema',
    3 => 'email',
    4 => 'user_admin',
    5 => 'nivel_nome',
    6 => 'last_logon',
    7 => 'user_id',
    8 => 'user_id'
);

$terms = "";
if (isset($requestData['areaAdmin']) && $requestData['areaAdmin'] == "1") {
    $terms .= " AND s.sis_id = '" . $_SESSION['s_area'] . "' ";
}

// getting total number records without any search
$sql = "SELECT u.*, n.*,s.* 
        \n FROM usuarios u 
        \n LEFT JOIN sistemas as s on u.AREA = s.sis_id 
        \n LEFT JOIN nivel as n on n.nivel_cod = u.nivel

        WHERE 1 = 1 {$terms};
";


// var_dump($sql);


$sqlResult = $conn->query($sql);
$totalData = $sqlResult->rowCount();
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

$sql = "SELECT u.*, n.*,s.* 
		\n FROM usuarios u 
		\n LEFT JOIN sistemas as s on u.AREA = s.sis_id 
		\n LEFT JOIN nivel as n on n.nivel_cod = u.nivel 
";

$sql.=" WHERE 1 = 1 {$terms} ";


if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter

    $sql.=" AND ( nome LIKE '%".$requestData['search']['value']."%' ";  
	$sql.=" OR login LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR sistema LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR nivel_nome LIKE '%".$requestData['search']['value']."%' ";
	$sql.=" OR email LIKE '%".$requestData['search']['value']."%' )";
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

	$nestedData[] = $row['nome'];
    $nestedData[] = $row['login'];
    $nestedData[] = $row['sistema'];
    $nestedData[] = $row['email'];
    $nestedData[] = transbool($row['user_admin']);
    $nestedData[] = $row['nivel_nome'];
    $nestedData[] = dateScreen($row['last_logon']);
    $nestedData[] = "<button type='button' class='btn btn-secondary btn-sm' onclick=\"redirect('". $origin ."?action=edit&cod=". $row['user_id'] ."')\">" . TRANS('BT_EDIT') . "</button>";
    $nestedData[] = "<button type='button' class='btn btn-danger btn-sm' onclick=\"confirmDeleteModal('" . $row['user_id'] . "')\">" . TRANS('BT_REMOVE') . "</button>";
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