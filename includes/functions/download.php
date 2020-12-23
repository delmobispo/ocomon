<?php 
session_start();

	require_once (__DIR__ . "/../include_basics_only.php");
	require_once __DIR__ . "/" . "../classes/ConnectPDO.php";
	use includes\classes\ConnectPDO;
	$conn = ConnectPDO::getInstance();

	//Secure the user data by escaping characters
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

	if(isset($_GET['cod']))
	{
		// if id is set then get the file with the id from database

		$query = "SELECT img_nome, img_tipo, img_size, img_bin FROM imagens WHERE  img_cod=".$_GET['cod']."";
		try {
			$result = $conn->query($query);
		}
		catch (Exception $e) {
			$erro = true;
			echo TRANS('MSG_ERR_GET_DATA');
			exit();
		}
	

		// list($img_nome, $img_tipo, $img_size, $img_bin) = mysqli_fetch_array($result);
		// list($img_nome, $img_tipo, $img_size, $img_bin) = $result->fetch();

		$row = $result->fetch();

		header("Content-length: ".$row['img_size']);
		header("Content-type: ".$row['img_tipo']);
		header("Content-Disposition: attachment; filename=".$row['img_nome']);
		echo $row['img_bin'];

		exit;
	}
?>