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

require_once __DIR__ . "/" . "../../includes/include_geral.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../../includes/components/jquery/jquery-ui-1.12.1/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<!-- <link rel="stylesheet" type="text/css" href="../../includes/components/select2/dist-2/css/select2.min.css" /> -->

	<title>OcoMon&nbsp;<?= VERSAO; ?></title>
	<style>
		hr.thick {
			border: 1px solid;
			border-radius: 5px;
		}
	</style>

</head>

<body>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>

	<div class="container bg-light">
		<!-- <h5 class="my-2"><i class="fas fa-database text-secondary"></i>&nbsp;<?= TRANS('TLT_CONS_SOLUT_PROB'); ?></h5> -->

		<!-- <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div id="divDetails">
					</div>
				</div>
			</div>
		</div> -->



		<?php

		$post = $_POST;
		// var_dump($post);

		$colLabel = "col-sm-2 text-md-right font-weight-bold p-2";
		$colsDefault = "small text-break border-bottom rounded p-2 bg-white"; /* border-secondary */
		$colContent = $colsDefault . " col-sm-2 col-md-2";
		$colContentLine = $colsDefault . " col-sm-10";


		if (empty($post['problema']) || strlen(trim($post['problema'])) < 5 ) {
			echo message('warning','', 'Informe pelo menos um termo de pesquisa com no mínimo 5 caracteres', '');
			exit;
		}

		$probB = str_replace(" ", "%", trim($post['problema'])); //SEM FORMATAÇÃO HTML
		$probA = htmlentities(str_replace(" ", "%", trim($post['problema']))); //FORMATADOS EM HTML

		//Quantidade de palavras digitadas
		$termos = explode("%", $probA);
		$termos = array_unique($termos);
		reIndexArray($termos);

		$destacaProb = $probA . "%" . $probB; //TODOS OS TERMOS COM OU SEM FORMATAÇÃO HTML
		$palavrasA = explode("%", $destacaProb);
		$palavrasA = array_unique($palavrasA); //RETIRA OS ELEMENTOS REPETIDOS (DISTINGUE AS FORMAÇÕES HTML)

		reIndexArray($palavrasA);

		// if (isset($post['anyword']) || (count($termos) == 1)) {
		if (isset($post['anyword'])) {
			$OPER = "<i>[" . TRANS('AT_LEAST_ONE_OF_THE_WORDS') . "";
		} else
			$OPER = "<i>[" . TRANS('TXT_ALL_WORDS') . "";

		if (isset($post['data_inicial']) && !empty($post['data_inicial'])) {
			$OPER .= " " . TRANS('TXT_AND_DATE_FROM') . " " . $post['data_inicial'] . "";
		}
		if (isset($post['data_final']) && !empty($post['data_final'])) {
			$OPER .= " " . TRANS('DATE_TO') . " " . $post['data_final'] . "";
		}

		if (isset($post['operador']) && !empty($post['operador']) && $post['operador'] != -1) {
			$sqlOper = "SELECT nome FROM usuarios WHERE user_id = " . $post['operador'] . "";
			$execOper = $conn->query($sqlOper);
			$rowOper = $execOper->fetch();

			$OPER .= " " . TRANS('TXT_FINISH_FROM') . " " . $rowOper['nome'] . "";
		}

		if (isset($post['onlyImgs'])) {
			$OPER .= " " . TRANS('TXT_ONLY_CALL_ATTACH') . "";
		}

		$OPER .= "].</i>";



		//----- TESTES PARA ENCONTRAR O BUG ---------//
		$todosTermos = "";
		$arrayTermos = array();
		$palavrasA = array_unique($palavrasA);
		for ($b = 0; $b < count($palavrasA); $b++) {
			$todosTermos .= $palavrasA[$b] . ", ";
			$arrayTermos[] = noHtml($palavrasA[$b]);
		}
		reIndexArray($arrayTermos);
		//dump($arrayTermos, 'ARRAY TERMOS:');

		$arrayTeste = array();
		for ($b = 0; $b < count($arrayTermos); $b++) {
			//$arrayTeste[] = $arrayTermos[$b];
			$arrayTeste[] = toHtml($arrayTermos[$b]);
		}

		array_unique($arrayTeste);
		reIndexArray($arrayTeste);
		//dump($arrayTeste, 'TESTE INVERTENDO');
		//----- TESTES PARA ENCONTRAR O BUG ---------//

		echo "<p>" . TRANS('TXT_TERM_SEARCH') . ": <i>\"" . trim($post['problema']) . "\".</i></p><p>" . TRANS('TXT_CRITERION') . ": " . $OPER . "</p>";


		$qrySolucao = "";
		$qryAssentamento = "";
		$qryProblema = "";
		$qryDesc = "";

		$destacaProb = str_replace("%", "|", $destacaProb);

		//SQL GLOBAL - RETORNA TODAS AS OCORRÊNCIAS QUE CONTENHAM PELO MENOS UM DOS TERMOS DE PESQUISA
		for ($i = 0; $i < count($palavrasA); $i++) {
			//Monta o SQL de forma dinâmica de acordo com a quantidade de palavras a serem pesquisadas
			if (isset($palavrasA[$i])) {
				if (strlen($qrySolucao) > 0)
					$qrySolucao .= " OR ";
				$qrySolucao .= "\n (lower( s.solucao ) LIKE lower(  '%" . $palavrasA[$i] . "%' ) OR  " .
					"\n lower( s.solucao ) LIKE lower(  '%" . noHtml($palavrasA[$i]) . "%' ) OR  " .
					"\n lower( s.problema ) LIKE lower(  '%" . $palavrasA[$i] . "%' ) OR " .
					"\n lower( s.problema ) LIKE lower(  '%" . noHtml($palavrasA[$i]) . "%' )) ";

				if (strlen($qryAssentamento) > 0)
					$qryAssentamento .= " OR ";
				$qryAssentamento .= "\n (lower( a.assentamento ) LIKE lower(  '%" . $palavrasA[$i] . "%' ) OR " .
					"\n lower( a.assentamento ) LIKE lower(  '%" . noHtml($palavrasA[$i]) . "%' )) ";

				if (strlen($qryDesc) > 0)
					$qryDesc .= " OR ";
				$qryDesc .= "\n (lower(o.descricao)  LIKE lower('%" . $palavrasA[$i] . "%') OR " .
					"\n lower(o.descricao)  LIKE lower('%" . noHtml($palavrasA[$i]) . "%')) ";
			}
		}

		$query = "";

		$query = "SELECT s.numero as numero, s.problema as problema, s.solucao as solucao, s.data as data, " .
			"\n s.responsavel as responsavel, a.assentamento as assentamento, o.descricao as descricao, u.* ";

		$queryFrom = "\nFROM solucoes s, assentamentos a, ocorrencias as o, usuarios as u ";

		if (isset($post['onlyImgs'])) {
			$queryFrom .= ", imagens i ";
		}

		//O SQL, em um primeiro momento, pesquisa por qualquer uma das palavras digitadas.
		$queryWhere = "\nWHERE ((" . $qrySolucao . ") OR (" . $qryAssentamento . ")  OR (" . $qryDesc . ") )" . //OR (".$qryProblema.")
			"\n AND (a.ocorrencia = s.numero AND o.numero = s.numero and o.operador = u.user_id ";


		if (isset($post['onlyImgs'])) {
			$queryWhere .= "\n and o.numero = i.img_oco ";
		}

		$queryWhere .= " ) ";

		$query .= $queryFrom . $queryWhere;

		if (isset($post['data_inicial']) && !empty($post['data_inicial'])) {
			$data_inicial = dateDB($post['data_inicial']);
			$query .= "and o.data_abertura >='" . $data_inicial . "'  ";
		}

		if (isset($post['data_final']) && !empty($post['data_final'])) {
			$data_final = dateDB($post['data_final']);
			$query .= " and o.data_fechamento <= '" . $data_final . "' ";
		}

		if (!empty($post['operador']) and $post['operador'] != -1) {
			$query .= "and s.responsavel=" . $post['operador'] . " ";
		}

		$query .= "\nGROUP BY numero ORDER BY numero"; // Retorna todos os registros onde pelo menos um dos termos existe.
		$query2 = $query;

		$resultado = $conn->query($query);
		$resultado2 = $conn->query($query2);

		$linhas = $resultado->rowCount();

		$qryChkOco = array();
		$qryChkAss = array();
		$qryChkSol = array();
		$achou = array();
		$totalE = 0; //quantidade de registros onde pelo menos uma das palavras não foi encontrada.

		if ($linhas == 0) {
			$aviso = TRANS('MSG_NONE_SOLUT_CRITE');
			// print "<script>mensagem('" . $aviso . "'); history.back();</script>";
			echo message('info', '', $aviso, '');
			exit();
		} elseif (!isset($post['anyword']) && count($termos) != 1) { //Condição  para checar se todos os termos existem
			//print "<br><b>Entrei na condição pra buscar chamados com todos os termos (AND)!</b><br>";
			//Esse laço serve apenas para contabilizar a quantidade de registros onde nem todas as palavras pesquisadas são encontradas
			foreach ($resultado2->fetchAll() as $rowA) {
				for ($i = 0; $i < count($palavrasA); $i++) {
					if (isset($palavrasA[$i])) {
						$qryChkOco[$i] = "SELECT * FROM ocorrencias WHERE numero = " . $rowA['numero'] . " AND " .
							"\n (lower(descricao) like lower('%" . $palavrasA[$i] . "%') " .
							"\n OR lower(descricao) like lower('%" . noHtml($palavrasA[$i]) . "%')) ";
						$execChkOco[$i] = $conn->query($qryChkOco[$i]);
						if ($execChkOco[$i]->rowCount()) {
							$achou[] = normaliza($palavrasA[$i]);
							$achou = array_unique($achou);
						}
						$qryChkAss[$i] = "SELECT * FROM assentamentos WHERE ocorrencia = " . $rowA['numero'] . " AND " .
							"\n (lower(assentamento) like lower('%" . $palavrasA[$i] . "%') " .
							"\n OR lower(assentamento) like lower('%" . noHtml($palavrasA[$i]) . "%') )";
						$execChkAss[$i] = $conn->query($qryChkAss[$i]);
						if ($execChkAss[$i]->rowcount()) {
							$achou[] = normaliza($palavrasA[$i]);
							$achou = array_unique($achou);
						}
						$qryChkSol[$i] = "SELECT * FROM solucoes WHERE numero = " . $rowA['numero'] . " AND (" .
							"\n (lower(solucao) like lower('%" . $palavrasA[$i] . "%')) OR " .
							"\n (lower(problema) like lower('%" . $palavrasA[$i] . "%')) OR " .
							"\n (lower(solucao) like lower('%" . noHtml($palavrasA[$i]) . "%')) OR " .
							"\n (lower(problema) like('%" . noHtml($palavrasA[$i]) . "%')) )";
						$execChkSol[$i] = $conn->query($qryChkSol[$i]);
						if ($execChkSol[$i]->rowcount()) {
							$achou[] = normaliza($palavrasA[$i]);
							$achou = array_unique($achou);
						}
					}
				}
				reIndexArray($achou);
				if (count($achou) < count($termos)) { //Não achou o termo
					$totalE++;
				}
				//ZERANDO O ARRAY ACHOU
				for ($j = 0; $j <= count($achou); $j++) {
					array_pop($achou);
				}
			}

			unset($qryChkOco);
			unset($qryChkAss);
			unset($qryChkSol);
		}

		$totalRegs = $linhas - $totalE;

		unset($achou);

		if ($totalRegs > 1)
			print "<p><B>" . TRANS('MSG_REGISTER_FIND') . " " . $totalRegs . " " . TRANS('POSSIBLE_RECORDS_ACORDING_TO_CRITERIA') . " </B></TD></p>";
		else
		if ($totalRegs == 1)
			print "<p><B>" . TRANS('TXT_ONLY_ONE_SOLUT_CRITE_LAST') . ".</B></p>";
		else {
			$aviso = "Nenhuma solução localizada com os critérios passados.";
			// print "<script>mensagem('" . $aviso . "'); history.back();</script>";
			echo message('info', '', $aviso, '');
			// echo "<script></script>";

		}


		foreach ($resultado->fetchAll() as $row) {
			for ($i = 0; $i < count($palavrasA); $i++) {
				$qryChkOco[$i] = "SELECT * FROM ocorrencias WHERE numero = " . $row['numero'] . " AND " .
					"\n ( lower(descricao) like lower('%" . $palavrasA[$i] . "%') " .
					"\n OR lower(descricao) like lower('%" . noHtml($palavrasA[$i]) . "%') )";
				$execChkOco[$i] = $conn->query($qryChkOco[$i]);
				if ($execChkOco[$i]->rowcount()) {
					$achou[] = normaliza($palavrasA[$i]);
					$achou = array_unique($achou);
				}
				$qryChkAss[$i] = "SELECT * FROM assentamentos WHERE ocorrencia = " . $row['numero'] . " AND " .
					"\n (lower(assentamento) like lower('%" . $palavrasA[$i] . "%') " .
					"\n OR lower(assentamento) like lower('%" . noHtml($palavrasA[$i]) . "%') )";
				$execChkAss[$i] = $conn->query($qryChkAss[$i]);
				if ($execChkAss[$i]->rowcount()) {
					$achou[] = normaliza($palavrasA[$i]);
					$achou = array_unique($achou);
				}
				$qryChkSol[$i] = "SELECT * FROM solucoes WHERE numero = " . $row['numero'] . " AND (" .
					"\n lower(solucao) like lower('%" . $palavrasA[$i] . "%') OR " .
					"\n lower(problema) like lower('%" . $palavrasA[$i] . "%') OR " .
					"\n lower(solucao) like lower('%" . noHtml($palavrasA[$i]) . "%') OR " .
					"\n lower(problema) like('%" . noHtml($palavrasA[$i]) . "%') )";
				$execChkSol[$i] = $conn->query($qryChkSol[$i]);
				if ($execChkSol[$i]->rowcount()) {
					$achou[] = normaliza($palavrasA[$i]);
					$achou = array_unique($achou);
				}
			}
			reIndexArray($achou);

			if ((isset($post['anyword'])) || (!isset($post['anyword']) && (count($achou) >= count($termos) && count($termos) == 1)) || (!isset($post['anyword']) && (count($achou) == count($termos) && count($termos) > 1))) {

		?>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= TRANS('TICKET_NUMBER'); ?></div>
					<div class="<?= $colContent; ?> font-weight-bold"><a onClick="openTicketInfo('<?= $row['numero']; ?>')"><?= $row['numero']; ?></a></div>
					<div class="<?= $colLabel; ?>"><?= TRANS('DATE'); ?></div>
					<div class="<?= $colContent; ?>"><?= dateScreen($row['data']); ?></div>
					<div class="<?= $colLabel; ?>"><?= TRANS('TECHNICIAN'); ?></div>
					<div class="<?= $colContent; ?>"><?= $row['nome']; ?></div>
				</div>

				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= TRANS('ISSUE_TYPE'); ?></div>
					<div class="<?= $colContentLine; ?>"><?= destaca($destacaProb, nl2br($row['problema'])); ?></div>
				</div>
				<div class="row my-2">
					<div class="<?= $colLabel; ?>"><?= TRANS('SOLUTION'); ?></div>
					<div class="<?= $colContentLine; ?>"><?= destaca($destacaProb, nl2br($row['solucao'])); ?></div>
				</div>
				<div class="w-100">
					<hr class='thick text-secondary'>
				</div>
		<?php
			}
			//ZERANDO O ARRAY ACHOU
			for ($j = 0; $j <= count($achou); $j++) {
				array_pop($achou);
			}
		} //while

		?>
	</div>
	<!-- <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script> -->
	<!-- <script>
		function openTicketInfo(ticket) {

			let location = 'ticket_show.php?numero=' + ticket;
			$("#divDetails").load(location);
			$('#modal').modal();
		}
	</script> -->
</body>

</html>