<?php 
session_start();

	require_once (__DIR__ . "/../include_basics_only.php");
	require_once __DIR__ . "/" . "../classes/ConnectPDO.php";
	use includes\classes\ConnectPDO;
	$conn = ConnectPDO::getInstance();
	
	// Secure the user data by escaping characters 
	// and shortening the input string
	function clean($input, $maxlength) {
		$input = substr($input, 0, $maxlength);
		$input = EscapeShellCmd($input);
		return ($input);
	}

	$file = "";
	$file = clean($_GET['file'], 4);

	if (empty($file))
	exit;

	$query = "SELECT * FROM imagens WHERE  img_cod=".$_GET['cod']."";
	
	try {
		$result = $conn->query($query);
	}
	catch (Exception $e) {
		$erro = true;
		message('danger', 'Ooops!', TRANS('MSG_ERR_GET_DATA'), '', '', 1);
		return;
	}
	
	// $data = @ mysql_fetch_array($result);
	$data = $result->fetch();

	if (!empty($data["img_bin"])) {
		// Saída MIME header
		header("Content-Type: {$data["img_tipo"]}");
		// Saída da imagen
		echo $data["img_bin"];
	}
?>