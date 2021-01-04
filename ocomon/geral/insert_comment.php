<?php /*                        Copyright 2020 Flávio Ribeiro

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

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
	header("Location: ../../index.php");
	exit;
}
require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);


$config = getConfig($conn);
$rowconfmail = getMailConfig($conn);


if (isset($_POST['onlyOpen']) && $_POST['onlyOpen'] == 1) {

	// dump($_POST); exit();
	$exception = "";
	$mailNotification = "";
	$numero = noHtml($_POST['numero']);
	$data = [];
	$data['numero'] = $numero;
	$comment = (isset($_POST['add_comment']) ? noHtml($_POST['add_comment']) : "");

	
	if (empty($comment)) {
		$_SESSION['flash'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'), '');
		return false;
	}

	
	$qry = "INSERT INTO assentamentos (ocorrencia, assentamento, data, responsavel, asset_privated, tipo_assentamento) values ".
			"(".$numero.", '".$comment."', '".date("Y-m-d H:i:s")."', ".$_SESSION['s_uid'].", 0, 8 ) ";

	try {
		$exec = $conn->exec($qry);
		$data['message'] = TRANS('TICKET_ENTRY_SUCCESS_ADDED');
	}
	catch (Exception $e) {
		$exception .= "<hr>" . $e->getMessage();
		$data['message'] = TRANS('MSG_SOMETHING_GOT_WRONG') . $exception;
	}
			

	/* Checagens para upload de arquivos - vale para todos os actions */
	$totalFiles = ($_FILES && !empty($_FILES['anexo']['name'][0]) ? count($_FILES['anexo']['name']) : 0);
	if ($totalFiles > $config['conf_qtd_max_anexos']) {

		$data['success'] = false; 
		$data['message'] .= '<hr>Too many files';
		echo json_encode($data);
		$_SESSION['flash'] = message('warning', 'Ooops!', $data['message'], '');
		return false;
	}

	$uploadMessage = "";
	/* Testa os arquivos enviados para montar os índices do recordFile*/
	if ($totalFiles) {
		foreach ($_FILES as $anexo) {
			$file = array();
			for ($i = 0; $i < $totalFiles; $i++) {
				/* fazer o que precisar com cada arquivo */
				/* acessa:  $anexo['name'][$i] $anexo['type'][$i] $anexo['tmp_name'][$i] $anexo['size'][$i]*/
				$file['name'] =  $anexo['name'][$i];
				$file['type'] =  $anexo['type'][$i];
				$file['tmp_name'] =  $anexo['tmp_name'][$i];
				$file['error'] =  $anexo['error'][$i];
				$file['size'] =  $anexo['size'][$i];

				$upld = upload('anexo', $config, $config['conf_upld_file_types'], $file);
				if ($upld == "OK") {
					$recordFile[$i] = true;
				} else {
					$recordFile[$i] = false;
					$uploadMessage .= $upld;
				}
			}
		}
		if (strlen($uploadMessage) > 0) {
			$data['success'] = false; 
			$data['field_id'] = "idInputFile";
			$data['message'] = message('warning', 'Ooops!', $uploadMessage, '');
			echo json_encode($data);
			$_SESSION['flash'] = $data['message'];
			return false;                
		}
	}

	/* Upload de arquivos - Todos os actions */
	if ($totalFiles) {
		foreach ($_FILES as $anexo) {
			$file = array();
			for ($i = 0; $i < $totalFiles; $i++) {
				/* fazer o que precisar com cada arquivo */
				/* acessa:  $anexo['name'][$i] $anexo['type'][$i] $anexo['tmp_name'][$i] $anexo['size'][$i]*/

				/* Apenas os arquivos já validados */
				if ($recordFile && $recordFile[$i]) {
					//INSERSAO DO ARQUIVO NO BANCO
					$fileinput = $anexo['tmp_name'][$i];
					// $tamanho = getimagesize($fileinput);
					$tamanho = getimagesize($fileinput);
					$tamanho2 = filesize($fileinput);

					if (!$tamanho) {
						/* Nâo é imagem */
						unset ($tamanho);
						$tamanho = [];
						$tamanho[0] = "";
						$tamanho[1] = "";
					}

					if (chop($fileinput) != "") {
						// $fileinput should point to a temp file on the server
						// which contains the uploaded file. so we will prepare
						// the file for upload with addslashes and form an sql
						// statement to do the load into the database.
						$file = addslashes(fread(fopen($fileinput, "r"), 1000000));
						$sqlFile = "INSERT INTO imagens (img_nome, img_oco, img_tipo, img_bin, img_largura, img_altura, img_size) values " .
							"('" . noSpace($anexo['name'][$i]) . "'," . $data['numero'] . ", '" . $anexo['type'][$i] . "', " .
							"'" . $file . "', " . dbField($tamanho[0]) . ", " . dbField($tamanho[1]) . ", " . dbField($tamanho2) . ")";
						// now we can delete the temp file
						unlink($fileinput);
					}
					try {
						$exec = $conn->exec($sqlFile);
					}
					catch (Exception $e) {
						$data['message'] = $data['message'] . "<hr>" . TRANS('MSG_ERR_NOT_ATTACH_FILE');
						$exception .= "<hr>" . $e->getMessage();
					}
				}
			}
		}
	}


	/* Variáveis de ambiente para envio de e-mail: todos os actions */
	$VARS = getEnvVarsValues($conn, $data['numero']);

	$event = 'edita-para-area';
	$eventTemplate = getEventMailConfig($conn, $event);

	$mailSent = send_mail($event, $VARS['%area_email%'], $rowconfmail, $eventTemplate, $VARS);
	if (!$mailSent) {
		$mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT');
	}
	
	$_SESSION['flash'] = message('success', '', TRANS('TICKET_ENTRY_SUCCESS_ADDED') . $mailNotification, '');

	return false;
	// echo TRANS('TICKET_ENTRY_SUCCESS_ADDED');
	// echo message('success', 'Pronto!', TRANS('TICKET_ENTRY_SUCCESS_ADDED'), '');

}
