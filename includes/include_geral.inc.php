<?php 
##INCLUDES GERAIS

	include ("../../includes/classes/headers.class.php");
	include ("../../includes/classes/conecta.class.php");
	include ("../../includes/classes/auth.class.php");

	include ("../../includes/classes/AuthNew.class.php");
	// include ("../../includes/classes/dateOpers.class.php");
	include ("../../includes/functions/functions.php");

	include ("../../includes/javascript/funcoes.js");
 	include ("../../includes/config.inc.php");
	include ("../../includes/versao.php");

 	include ("../../includes/languages/".LANGUAGE.""); //TEMPORARIAMENTE

 	include ("../../includes/queries/queries.php");

	print "<style>"; //type='text/css'
	?>
	<!--
		@import url('../../includes/css/estilos.css');
		
	//-->
	<?php 
	print "</style>";


	print "<link rel='shortcut icon' href='../../includes/icons/favicon.ico'>";

	$conec = new conexao;
	$conec->conecta('MYSQL');

	define ( "BODY_COLOR", "#F6F6F6");
	define ( "TD_COLOR", "#DBDBDB");

?>