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

class auth
{
    public $saida;
	public $texto;
    
    public function __construct($logged)
    {
        if (!isset($logged) || $logged == 0) {
            header("Location: ../../index.php");
            return;
        }
    }


	public function testa_user_bootstrap($s_usuario, $s_nivel, $s_nivel_desc = '', $permissao, $help = '')
    {

        if ($help != '') {
            $help = "&nbsp;<a onClick=\"return popupS('" . HELP_PATH . "" . $help . "')\"><i class='fas fa-question-circle text-success'></i></a>";
        }

        if ($s_nivel > $permissao) //se o nível do usuário for maior do que a permissão necessária para o script..
        {
            header("Location: ../../index.php");
            return;
        } 
        
        $this->texto = TRANS('MENU_TTL_MOD_OCCO');
        if (is_file("./.invmon_dir")) {
            $this->texto = TRANS('MENU_TTL_MOD_INV');
        } elseif (is_file("./.admin_dir")) {
            $this->texto = TRANS('MENU_TTL_MOD_ADMIN');
        }

        $this->saida = "<div class='container-fluid '>"; /* bg-light */
            $this->saida .= "<div class='row border-bottom rounded' style='border-width: 3px !important;'>";
                $this->saida .= "<div class='col-sm-4 small text-nowrap  text-sm-left '>"; /* text-muted */
                    $this->saida .= $this->texto;
                $this->saida .= "</div>";
                $this->saida .= "<div class='col-4'>";
                $this->saida .= "</div>";
                $this->saida .= "<div class='col-sm-4 small text-nowrap text-md-right text-sm-left '>"; /* text-muted */
                    $this->saida .= TRANS(date("l")) . ",&nbsp;" . (dateScreen(date("Y/m/d H:i:s"))) . "</b>" . $help;
                $this->saida .= "</div>";

            $this->saida .= "</div>";
        $this->saida .= "</div>";
        
        echo $this->saida;

        return;
        
    }

    public function testa_user($s_usuario, $s_nivel, $s_nivel_desc = '', $permissao, $help = '')
    {

        if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
            header("Location: ../../index.php");
            exit;
        } else {
            //print "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'/>";
            if ($help != '') {
                $help = "&nbsp;<a><img align='absmiddle' src='" . ICONS_PATH . "help-16.png' width='16' height='16' border='0' onClick=\"return popupS('" . HELP_PATH . "" . $help . "')\"></a>";
            }

            if ($s_nivel > $permissao) //se o nível do usuário for maior do que a permissão necessária para o script..
            {
                $this->saida = "<script>window.open('../../index.php','_parent','')</script>";
                exit;
            } else {
                if (is_file("./.invmon_dir")) {
                    $this->texto = TRANS('MENU_TTL_MOD_INV');
                } else
                if (is_file("./.admin_dir")) {
                    $this->texto = TRANS('MENU_TTL_MOD_ADMIN');
                } else {
                    $this->texto = TRANS('MENU_TTL_MOD_OCCO');
                }

                $this->saida = "<TABLE class='header_centro' cellspacing='1' border='0' cellpadding='1' align='center' width='100%' class='center'>" . //#5E515B
                "<TR>" .
                "<TD nowrap width='60%'><b>" . $this->texto . "</b></td>" .
                "<td width='40%' nowrap><p class='parag' style='text-align:right'><b>" . TRANS(date("l")) . ",&nbsp;" . (dateScreen(date("Y/m/d H:i:s"))) . "</b>" . $help . "</p></TD>";
                $this->saida .= "</TR>" .
                    "</TABLE>";
            }
            print $this->saida;
        }
    }

    public function testa_user_hidden($s_usuario, $s_nivel, $s_nivel_desc = '', $permissao, $help = '')
    {

        if ($s_nivel > $permissao) //se o nível do usuário for maior do que a permissão necessária para o script..
        {
            $this->saida = "<script>window.open('../../index.php','_parent','')</script>";
            return;
        }
        
    }
}
