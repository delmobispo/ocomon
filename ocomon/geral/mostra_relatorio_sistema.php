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
  */


	include ("../../includes/include_geral.inc.php");
	include ("../../includes/include_geral_II.inc.php");

?>

<HTML>
<BODY>

<?php 

        $query = "SELECT * FROM ocorrencias WHERE (";

        if (!empty($sistema) and $sistema != -1)
        {
                if (strlen($query)>34)
                        $query.="AND ";
                $query.="sistema=$sistema ";
        }
        else
        {
                $aviso = "Escolha_um_dos_sistemas.";
                $origem = "relatorio_sistema.php";
                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0;URL=mensagem.php?aviso=$aviso&origem=$origem\">";
        }

        if (!empty($data_inicial) and !empty($data_final))
        {
                if (strlen($query)>34)
                        $query.="AND ";
                $data_inicial = datam($data_inicial);
                $data_final = datam($data_final);
                $query.="data_abertura>='$data_inicial' AND data_abertura<='$data_final'";
        }

        if (!empty($data_inicial) and empty($data_final))
        {
                if (strlen($query)>34)
                        $query.="AND ";
                $data_inicial = datam($data_inicial);
                $query.="data_abertura>='$data_inicial'";
        }

        if (empty($data_inicial) and !empty($data_final))
        {
                if (strlen($query)>34)
                        $query.="AND ";
                $data_final = datam($data_final);
                $query.="data_abertura<='$data_final'";
        }


        $query = $query." ) ORDER BY numero";
        $resultado = mysql_query($query);
        $linhas = mysql_num_rows($resultado);

        $query_total = "SELECT * FROM ocorrencias";
        $resultado_total = mysql_query($query_total);
        $linhas_total = mysql_num_rows($resultado_total);

        $query_sis = "SELECT sistema FROM sistemas WHERE (sis_id=$sistema)";
        $resultado_sis = mysql_query($query_sis);
        $linhas_sis = mysql_num_rows($resultado_sis);

        if ($linhas == 0)
        {
                $aviso = "Nenhuma_ocorrencia_localizada.";
                $origem = "relatorio_sistema.php";
                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0;URL=mensagem.php?aviso=$aviso&origem=$origem\">";
        }

        print "<BR><B>OcoMon - Relatório de ocorrências por sistema.</B> - <a href=relatorio_sistema.php>Voltar</a><BR>";
        print "<HR>";
?>
<TABLE border="0"  align="center" width="100%">

        <TR>
        <TABLE border="0"  align="center" width="100%">
                <TD width="20%" align="left">Período de:</TD>
                <TD width="30%" align="left"><?php print datab($data_inicial);?> a <?php print datab($data_final);?></TD>
                <TD width="40%" align="left">Número total de ocorrências no período:</TD>
                <TD width="10%" align="left"><?php print $linhas_total;?></TD>
        </TABLE>
        </TR>

        <TR>
        <TABLE border="0"  align="center" width="100%">
                <TD width="34%" align="left">Sistema:</TD>
                <TD width="33%" align="left">Quantidade:</TD>
                <TD width="33%" align="left">Porcentagem</TD>
        </TABLE>
        </TR>

        <TR>
        <TABLE border="0"  align="center" width="100%">
                <TD width="34%" align="left"><?php print mysql_result($resultado_sis,0,0);?></TD>
                <TD width="33%" align="left"><?php print $linhas;?></TD>
                <TD width="33%" align="left"><?php print round(($linhas*100)/$linhas_total)?>%</TD>
        </TABLE>
        </TR>

</TABLE>
<HR>

</BODY>
</HTML>


