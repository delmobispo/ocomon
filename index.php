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

is_file("./includes/config.inc.php")
    or die("Você precisa configurar o arquivo config.inc.php em OCOMON/INCLUDES/para iniciar o uso do OCOMON!<br>Leia o arquivo <a href='LEIAME.md'>LEIAME.md</a> para obter as principais informações sobre a instalação do OCOMON!" .
        "<br><br>You have to configure the config.inc.php file in OCOMON/INCLUDES/ to start using Ocomon!<br>Read the file <a href='LEIAME.md'>LEIAME.md</a>to get the main informations about the Ocomon Installation!");

session_start();

include "PATHS.php";
require_once "includes/functions/functions.php";
include_once "includes/queries/queries.php";
require_once "" . $includesPath . "config.inc.php";
include_once "" . $includesPath . "versao.php";

require_once __DIR__ . "/" . "includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();


if (!isset($_SESSION['s_language'])) {
    $_SESSION['s_language'] = "pt_BR.php";
}

if (!isset($_SESSION['s_usuario'])) {
    $_SESSION['s_usuario'] = "";
}
if (!isset($_SESSION['s_usuario_nome'])) {
    $_SESSION['s_usuario_nome'] = "";
}

if (!isset($_SESSION['s_logado'])) {
    $_SESSION['s_logado'] = "";
}

if (!isset($_SESSION['s_nivel'])) {
    $_SESSION['s_nivel'] = "";
}

$uName = $_SESSION['s_usuario_nome'];
if (!empty($uName)) {
    $logInfo = TRANS('MNS_LOGOFF');
    $hnt = TRANS('HNT_LOGOFF');
}


$screen = getScreenInfo($conn, 1);


$marca = "HOME";

$rootPath = "./";
$ocomonPath = "./ocomon/geral/";
$invmonPath = "./invmon/geral/";
$adminPath = "./admin/geral/";

/* Páginas que serão carregadas por padrão em cada aba */
$simplesHome = (isset($_SESSION['s_page_simples']) ? $_SESSION['s_page_simples'] : $ocomonPath . "tickets_main_user.php?action=listall");
$homeHome = (isset($_SESSION['s_page_home']) ? $_SESSION['s_page_home'] : "home.php");
$ocoHome = (isset($_SESSION['s_page_ocomon']) ? $_SESSION['s_page_ocomon'] : $ocomonPath . "tickets_main.php");
$invHome = (isset($_SESSION['s_page_invmon']) ? $_SESSION['s_page_invmon'] : $invmonPath . "inventory_main.php");
$admHome = (isset($_SESSION['s_page_admin']) ? $_SESSION['s_page_admin'] : $adminPath . "main_settings.php");
$admAreaHome = (isset($_SESSION['s_page_admin']) ? $_SESSION['s_page_admin'] : $adminPath . "types_of_issues.php");


?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= TRANS('TTL_OCOMON'); ?>">
    <title>OcoMon&nbsp;<?= VERSAO; ?></title>

    <!-- using local links -->
    <link rel="stylesheet" type="text/css" href="./includes/components/w3/w3.css" />
    <link rel="stylesheet" href="./includes/components/bootstrap/custom.css">
    <link rel="stylesheet" href="./includes/components/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="./includes/components/malihu-custom-scrollbar/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="./includes/components/sidebar/css/main.css">
    <link rel="stylesheet" href="./includes/components/sidebar/css/sidebar-themes.css">
    <link rel="stylesheet" type="text/css" href="./includes/css/estilos.css" />
    <link rel="shortcut icon" href="./includes/icons/favicon.ico">

    <style>
        header {
            padding: 0px, 5px, 20px, 20px;
            background: #756c75;
            /* #5f585e */
        }

        header h1 {
            font-size: 24px;
        }

        .logo {
            max-height: 46.3px;
            min-height: 46.3px;
            padding: 4px;
        }

        #iframeMain {
            position: relative;
            height: 99%;
            width: calc(100% - 40px);
            margin-left: 20px;
            margin-right: 20px;
        }

        @media only screen and (max-width: 768px) {
            #iframeMain {
                display: block;
                width: 100%;
                margin-left: 0px;
                margin-right: 0px;
            }
        }


        ::placeholder {
            /* Chrome, Firefox, Opera, Safari 10.1+ */
            color: #181818;
            opacity: 0.5;
            /* Firefox */
        }
    </style>
</head>

<body>

    <?php
    if (isPHPOlder()) {
        echo message('danger', 'Ooops!', TRANS('ERROR_PHP_VERSION'), '', '', 1);
        session_destroy();
        return;
    }
    ?>
    <input type="hidden" name="s_logado" id="s_logado" value="<?= $_SESSION['s_logado']; ?>">
    <?php
    if ($_SESSION['s_logado']) {
    ?>
        <header>
            <div class="topo w3-top w3-left-align" style="z-index:4;">

                <div id="header_logo">
                    <span class="logo"><img src="MAIN_LOGO.svg" width="240" class="w3-image"></span>
                </div>
                <div id="header_elements" class="w3-hide-small">
                    <span class="w3-small"> <?= $uName . "&nbsp;&nbsp;|&nbsp;&nbsp;"; ?>
                        <a class="w3-text-red " href="<?= $commonPath; ?>logout.php" title="<?= $hnt ?>" data-toggle="popover" data-content="" data-placement="left" data-trigger="hover"><i class="fas fa-sign-out-alt w3-large"></i></a></span>
                </div>

                <div class="barra">
                    <input type="hidden" name="s_nivel" id="s_nivel" value="<?= $_SESSION['s_nivel']; ?>">
                    <?php

                    if (empty($_SESSION['s_permissoes']) && $_SESSION['s_nivel'] != 1) {
                        print "&nbsp;";
                        print "&nbsp;";
                        print "&nbsp;";
                        print "&nbsp;";
                        print "&nbsp;";
                    } else {

                        print "<a class='barra td-barra' id='HOME' onMouseOver=\"destaca('HOME')\" onMouseOut=\"libera('HOME')\" onclick=\"loadPage('menu-sidebar.php?menu=hom #sidebar-loaded',loadMenu()); loadPageContent('hom');\" >&nbsp;" . TRANS('MNS_HOME') . "&nbsp;</a>";

                        if ($_SESSION['s_nivel'] < 3) {

                            if (($_SESSION['s_ocomon'] == 1) && !isIn($_SESSION['s_area'], $screen['conf_ownarea_2'])) {
                                print "<a class='barra td-barra' id='OCOMON' onMouseOver=\"destaca('OCOMON')\" onMouseOut=\"libera('OCOMON')\" onclick=\"loadPage('menu-sidebar.php?menu=oco #sidebar-loaded',loadMenu()); loadPageContent('oco'); \">&nbsp;" . TRANS('TICKETS') . "&nbsp;</a>";
                            } elseif (($_SESSION['s_ocomon'] == 1) && isIn($_SESSION['s_area'], $screen['conf_ownarea_2'])) {
                                    print "<a class='barra td-barra' id='OCOMON' onMouseOver=\"destaca('OCOMON')\" onMouseOut=\"libera('OCOMON')\" onclick=\"loadPage('menu-sidebar.php?menu=oco #sidebar-loaded',loadMenu()); loadPageContent('hom'); \">&nbsp;" . TRANS('TICKETS') . "&nbsp;</a>";
                            } else {
                                    print "&nbsp;" . TRANS('TICKETS') . "&nbsp;";
                            }
                        }

                        if ($_SESSION['s_invmon'] == 1) {
                            print "<a class='barra td-barra' id='INVMON' onMouseOver=\"destaca('INVMON')\" onMouseOut=\"libera('INVMON')\" onclick=\"loadPage('menu-sidebar.php?menu=inv #sidebar-loaded',loadMenu()); loadPageContent('inv'); \">&nbsp;" . TRANS('MNS_INVENTARIO') . "&nbsp;</a>";

                        } else {
                            print "&nbsp;";
                        }

                        if ($_SESSION['s_nivel'] == 1 || (isset($_SESSION['s_area_admin']) && $_SESSION['s_area_admin'] == '1')) {
                            print "<a class='barra td-barra' id='ADMIN' onMouseOver=\"destaca('ADMIN')\" onMouseOut=\"libera('ADMIN')\" onclick=\"loadPage('menu-sidebar.php?menu=adm #sidebar-loaded',loadMenu()); loadPageContent('admin'); \">&nbsp;" . TRANS('ADMIN') . "&nbsp;</a>";
                        } else {
                            print "&nbsp;";
                        }

                        ?>
                        <span data-toggle="popover" data-content="<?= TRANS('MENU_SHOW_HIDE'); ?>" data-trigger="hover" data-placement="right">
                            <a href="#" class="w3-text-white toggle-sidebar"><i class="fas fa-bars"></i></a>
                        </span>
                        <span class="w3-hide-medium w3-hide-large">
                            <a href="<?= $commonPath; ?>logout.php" title="<?= $hnt ?>"><i class="fas fa-sign-out-alt w3-medium w3-text-red w3-right"></i></a>
                        </span>
                        <?php
                    }
                    ?>
                </div> <!-- barra -->
            </div> <!-- topo -->
        </header>

        <!-- <div class="page-wrapper default-theme sidebar-bg bg1 toggled"> -->
        <div class="page-wrapper theme ocomon-theme toggled border-radius-on">
            <!-- default-theme legacy-theme chiller-theme ice-theme cool-theme light-theme -->
            <nav id="sidebar" class="sidebar-wrapper">
                <!-- the menu will be loaded dynamicaly -->
                <input type="hidden" name="defaultPageHome" id="defaultPageHome" value="<?= $homeHome; ?>">
                <input type="hidden" name="defaultPageOcomon" id="defaultPageOcomon" value="<?= $ocoHome; ?>">
                <input type="hidden" name="defaultPageInvmon" id="defaultPageInvmon" value="<?= $invHome; ?>">
                <input type="hidden" name="defaultPageAdmin" id="defaultPageAdmin" value="<?= $admHome; ?>">
                <input type="hidden" name="defaultPageAdminArea" id="defaultPageAdminArea" value="<?= $admAreaHome; ?>">
            </nav>

            <main class="page-content pt-2">
                <div id="overlay" class="overlay"></div>
                <!-- <div id="divMain" class="container-fluid p-2"> -->
                <iframe id="iframeMain" class="iframeMain" frameborder="0"></iframe><!-- scrolling="no" -->
                <!-- </div> -->
            </main>
            <!-- page-content" -->
        </div>

        <!-- FOOTER -->
        <div class="w3-bar" style="margin-top:50px;">
            <div class="w3-bar w3-card w3-bottom w3-light-grey w3-border-top w3-center w3-padding" style="z-index:4; height: 50px;">
                <div class="w3-left">
                    <a class="toggle-sidebar w3-left w3-tooltip" href="#">
                        <span data-toggle="popover" data-content="<?= TRANS('MENU_SHOW_HIDE'); ?>" data-trigger="hover" data-placement="right"><i class="fa fa-bars"></i></span>
                    </a>
                </div>
                <a href="https://ocomonphp.sourceforge.io/" target="_blank">
                    OcoMon
                </a>&nbsp;-&nbsp;
                <?= TRANS('MNS_MSG_OCOMON'); ?><br />
                <?= TRANS('COL_VERSION') . ": " . VERSAO . " - " . TRANS('MNS_MSG_LIC') . " GPL"; ?>
            </div>
        </div>
    <?php
    } else {
        /* Área de login */
        ?>
        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div id="divDetails">
                        <p><?= TRANS('USER_SELF_REGISTER'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div id="idLoad" class="loading" style="display:none"></div>
        </div>

        <div class="w3-modal" style="display:block; background-color: rgba(0,0,0,0.1);">
            <div class="w3-modal-content w3-card-4  w3-round" style="max-width:600px; ">

                <div class=" w3-padding w3-border-bottom w3-round " style="background: #3a4d56; margin-top: -40px"><br />
                    <!-- <img src="./MAIN_LOGO.png" alt="OcoMon" style="width:50%; padding-bottom: 18px;"> -->
                    <img src="./MAIN_LOGO.svg" alt="OcoMon" style="width:50%; padding-bottom: 18px;">
                </div>

                <form name="form" id="form" method="post" class="w3-container" action="<?= $_SERVER['PHP_SELF']; ?>">
                    <div class="w3-section w3-large">
                        <div id="divResult"></div>
                        <?php
                        if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
                            echo $_SESSION['flash'];
                            $_SESSION['flash'] = '';
                        }
                        ?>
                        <label><b><?= TRANS('FIELD_USER'); ?></b></label>
                        <input class="w3-input w3-border w3-round w3-margin-bottom" type="text" placeholder="<?= TRANS('FIELD_USER'); ?>" name="user" id="user" required autocomplete="off" value="">
                        <label><b><?= TRANS('PASSWORD'); ?></b></label>
                        <input class="w3-input w3-border w3-round" type="password" placeholder="<?= TRANS('PASSWORD'); ?>" name="pass" id="pass" required>
                        <button class="w3-button w3-block w3-padding w3-section w3-light-grey w3-round w3-border" id="bt_login" type="submit"><?= TRANS('ENTER_IN'); ?></button>
                        <!-- <input class="w3-check w3-margin-top" type="checkbox" checked="checked"> Remember me -->
                    </div>
                </form>

                <div class="w3-container w3-padding-16 w3-round w3-center">
                    <?php
                    if ($screen['conf_user_opencall']) {
                        ?>
                        <span class="w3-padding w3-medium"><?= TRANS('MNS_MSG_CAD_ABERTURA_1'); ?><a href="#" onclick="autosubscribeform();"><?= TRANS('TLT_HERE'); ?>!</a></span><br />
                        <?php
                    }
                    ?>
                    <!-- <span class="w3-right w3-padding w3-hide-small">Esqueceu a <a href="#">senha?</a></span> -->
                </div>

                <div class="w3-container w3-border-top w3-padding-16 w3-light-grey w3-round w3-center">
                    <span class="w3-padding">
                        <a href="https://ocomonphp.sourceforge.io/" target="_blank">OcoMon</a> - <?= TRANS('MNS_MSG_OCOMON'); ?><br /><?= TRANS('COL_VERSION'); ?>:&nbsp;<?= VERSAO; ?>&nbsp;-&nbsp;<?= TRANS('MNS_MSG_LIC'); ?>GPL
                    </span>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <!-- page-wrapper -->
    <script src="./includes/components/jquery/jquery.js"></script>
    <script src="./includes/components/jquery/jquery.initialize.min.js"></script>
    <script src="./includes/components/jquery/MHS/jquery.md5.min.js"></script>
    <script src="./includes/components/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="./includes/components/malihu-custom-scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="./includes/javascript/funcoes-3.0.js"></script>
    <script src="./includes/components/sidebar/js/main.js"></script>

    <script>
        $(function() {
            // $("#sidebar").load('menu-sidebar.php');
            $('input, select, textarea').on('change', function() {
                $(this).removeClass('is-invalid');
            });

            $('#bt_login').on('click', function(e) {
                e.preventDefault();
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });

                let user = $('#user').val();
                let pass = ($('#pass').val() != "" ? $.MD5($('#pass').val()) : "");

                $("#bt_login").prop("disabled", true);
                $.ajax({
                    url: '<?= $commonPath ?>auth_process.php',
                    method: 'POST',
                    data : {
                            "user" : user,
                            "pass" : pass
                    },
                    dataType: 'json',
                }).done(function(response) {

                    if (!response.success) {
                        $('#divResult').html(response.message);
                        $('input, select, textarea').removeClass('is-invalid');
                        if (response.field_id != "") {
                            $('#' + response.field_id).focus().addClass('is-invalid');
                        }
                        $("#bt_login").prop("disabled", false);
                    } else {
                        $('#divResult').html('');
                        $('input, select, textarea').removeClass('is-invalid');
                        $("#bt_login").prop("disabled", false);
                        var url = '<?= $_SERVER['PHP_SELF'] ?>';
                        $(location).prop('href', url);
                        return false;
                    }
                });
                return false;
            });

        });

        function autosubscribeform() {
            let location = 'newUser.php';
            $("#divDetails").load(location);
            $('#modal').modal();
        }

        var GLArray = new Array();

        function destaca(id) {
            var obj = document.getElementById(id);
            var valor = '#3a4d56'; /* cor fixa */
            if (valor != '') {
                if (obj != null) {
                    obj.style.background = valor;
                }
            }
        }

        function libera(id) {
            if (verificaArray('', id) == false) {
                var obj = document.getElementById(id);
                if (obj != null) {
                    obj.style.background = ''; //#675E66
                    obj.style.borderTop = '';
                    //obj.className = "released";
                }
            }
        }


        function verificaArray(acao, id) {
            var i;
            var tamArray = GLArray.length;
            var existe = false;

            for (i = 0; i < tamArray; i++) {
                if (GLArray[i] == id) {
                    existe = true;
                    break;
                }
            }

            if ((acao == 'guarda') && (existe == false)) { //
                GLArray[tamArray] = id;
            } else if ((acao == 'libera')) {
                var temp = new Array(tamArray - 1); //-1
                var pos = 0;
                for (i = 0; i < tamArray; i++) {
                    if (GLArray[i] == id) {
                        temp[pos] = GLArray[i];
                        pos++;
                    }
                }

                GLArray = new Array();
                var pos = temp.length;
                for (i = 0; i < pos; i++) {
                    GLArray[i] = temp[i];
                }
            }

            return existe;
        }
        
    </script>

</body>

</html>