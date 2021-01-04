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
*/ session_start();

include("PATHS.php");
require_once("./includes/config.inc.php");
include("./includes/languages/" . LANGUAGE . "");
require_once("./includes/functions/functions.php");


$rootPath = "./";
$ocomonPath = "./ocomon/geral/";
$invmonPath = "./invmon/geral/";
$adminPath = "./admin/geral/";
$commonPath = "./includes/common/";


$OPERADOR_AREA = false;
if (isset($_SESSION['s_area_admin']) && $_SESSION['s_area_admin'] == '1' && $_SESSION['s_nivel'] != '1')
	$OPERADOR_AREA = true;


/* Páginas que serão carregadas por padrão em cada aba */
$simplesHome = (isset($_SESSION['s_page_simples']) ? $_SESSION['s_page_simples'] : $ocomonPath . "tickets_main_user.php");
$homeHome = (isset($_SESSION['s_page_home']) ? $_SESSION['s_page_home'] : $ocomonPath . "tickets_main_user.php");
$ocoHome = (isset($_SESSION['s_page_ocomon']) ? $_SESSION['s_page_ocomon'] : $ocomonPath . "tickets_main.php");
$invHome = (isset($_SESSION['s_page_invmon']) ? $_SESSION['s_page_invmon'] : $invmonPath . "inventory_main.php");
$admHome = (isset($_SESSION['s_page_admin']) ? $_SESSION['s_page_admin'] : $adminPath . "main_settings.php");
$admAreaHome = (isset($_SESSION['s_page_admin']) ? $_SESSION['s_page_admin'] : $adminPath . "users.php");


$menuHome = "";
$menuHomeSimples = "";
$menuOcomon = "";
$menuInvmon = "";
$menuAdmin = "";
$menuAdminArea = "";

/* SET THE CHOSEN MENU */
if (isset($_GET['menu']) && !empty($_GET['menu'])) {

	$menuHomeSimples = (($_GET['menu'] == 'hom' && $_SESSION['s_nivel'] == 3) ? "sidebar-loaded" : "");

	$menuHome = (($_GET['menu'] == 'hom' && empty($menuHomeSimples)) ? "sidebar-loaded" : "");

	$menuOcomon = ($_GET['menu'] == 'oco' ? "sidebar-loaded" : "");
	$menuInvmon = ($_GET['menu'] == 'inv' ? "sidebar-loaded" : "");
	// $menuAdmin = ($_GET['menu'] == 'adm' ? "sidebar-loaded" : "");

	$menuAdmin = ($_GET['menu'] == 'adm' && $OPERADOR_AREA ? "" : ($menuAdmin = ($_GET['menu'] == 'adm' ? "sidebar-loaded" : "")));

	$menuAdminArea = ($_GET['menu'] == 'adm' && $OPERADOR_AREA ? "sidebar-loaded" : "");
} elseif ($_SESSION['s_nivel'] == 3) {
	$menuHomeSimples = "sidebar-loaded";
} else
	$menuHome = "sidebar-loaded";

?>

<!-- MENU HOME SOMENTE ABERTURA -->
<div class="sidebar-content" id="<?= $menuHomeSimples; ?>">

	<input type="hidden" name="defaultPageHome" id="defaultPageHome" value="<?= $homeHome; ?>">
	<input type="hidden" name="defaultPageOcomon" id="defaultPageOcomon" value="<?= $ocoHome; ?>">
	<input type="hidden" name="defaultPageInvmon" id="defaultPageInvmon" value="<?= $invHome; ?>">
	<input type="hidden" name="defaultPageAdmin" id="defaultPageAdmin" value="<?= $admHome; ?>">
	<input type="hidden" name="defaultPageAdminArea" id="defaultPageAdminArea" value="<?= $admAreaHome; ?>">

	<div class="sidebar-item sidebar-menu ">
		<ul>
			<li class="header-menu">
				<!-- <span>Home</span> -->
				<div class="row">
					<div class="col"><span>Home</span></div>
					<div class="col text-right"><span class="pin-sidebar" data-toggle="popover" data-content="<?= TRANS('MENU_SHRINK'); ?>" data-placement="top" data-trigger="hover" style="cursor:pointer;"><i class="fa fa-compress-arrows-alt small"></i></span></div>
				</div>
			</li>
			<li class='li-link'>
				<a href="#" data-app="ticket_add" data-params="" data-path="<?= $ocomonPath ?>">
					<!-- <i class="fa fa-phone-volume"></i> -->
					<i class="fa fa-plus-square"></i>
					<span class="menu-text"><?= TRANS('TO_OPEN_TICKET'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="tickets_main_user" data-params="action=listall" data-path="<?= $ocomonPath ?>">
					<i class="fas fa-user-check"></i>
					<span class="menu-text"><?= TRANS('MNL_MEUS'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="change_pass" data-params="" data-path="<?= $commonPath ?>">
					<i class="fa fa-key"></i>
					<span class="menu-text"><?= TRANS('PASSWORD'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="user_lang" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fa fa-globe"></i>
					<span class="menu-text"><?= TRANS('MNL_LANG'); ?></span>
				</a>
			</li>
		</ul>
	</div>
</div>

<!-- MENU HOME -->
<div class="sidebar-content" id="<?= $menuHome; ?>">

	<input type="hidden" name="defaultPageHome" id="defaultPageHome" value="<?= $homeHome; ?>">
	<input type="hidden" name="defaultPageOcomon" id="defaultPageOcomon" value="<?= $ocoHome; ?>">
	<input type="hidden" name="defaultPageInvmon" id="defaultPageInvmon" value="<?= $invHome; ?>">
	<input type="hidden" name="defaultPageAdmin" id="defaultPageAdmin" value="<?= $admHome; ?>">
	<input type="hidden" name="defaultPageAdminArea" id="defaultPageAdminArea" value="<?= $admAreaHome; ?>">

	<div class="sidebar-item sidebar-menu ">
		<ul>
			<li class="header-menu">
				<!-- <span>Home</span> -->
				<div class="row">
					<div class="col"><span>Home</span></div>
					<div class="col text-right"><span class="pin-sidebar" data-toggle="popover" data-content="<?= TRANS('MENU_SHRINK'); ?>" data-placement="top" data-trigger="hover" style="cursor:pointer;"><i class="fa fa-compress-arrows-alt small"></i></span></div>
				</div>
			</li>
			<li class='li-link'>
				<a href="#" data-app="tickets_main_user" data-params="" data-path="<?= $ocomonPath ?>">
					<!-- action=listall -->
					<!-- <i class="fa fa-book"></i> -->
					<i class="fas fa-user-check"></i>
					<span class="menu-text"><?= TRANS('MNL_MEUS'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="home" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fas fa-stream"></i>
					<span class="menu-text"><?= TRANS('TICKETS_TREE'); ?></span>
				</a>
			</li>

			<li class='li-link'>
				<a href="#" data-app="notices_board" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fas fa-bell"></i>
					<span class="menu-text"><?= TRANS('TLT_BOARD_NOTICE'); ?></span>
				</a>
			</li>

			<li class='li-link'>
				<a href="#" data-app="change_pass" data-params="" data-path="<?= $commonPath ?>">
					<i class="fa fa-key"></i>
					<span class="menu-text"><?= TRANS('PASSWORD'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="user_lang" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fa fa-globe"></i>
					<span class="menu-text"><?= TRANS('MNL_LANG'); ?></span>
				</a>
			</li>
		</ul>
	</div>
</div>


<!-- MENU OCORRÊNCIAS -->
<div class="sidebar-content" id="<?= $menuOcomon; ?>">

	<input type="hidden" name="defaultPageHome" id="defaultPageHome" value="<?= $homeHome; ?>">
	<input type="hidden" name="defaultPageOcomon" id="defaultPageOcomon" value="<?= $ocoHome; ?>">
	<input type="hidden" name="defaultPageInvmon" id="defaultPageInvmon" value="<?= $invHome; ?>">
	<input type="hidden" name="defaultPageAdmin" id="defaultPageAdmin" value="<?= $admHome; ?>">
	<input type="hidden" name="defaultPageAdminArea" id="defaultPageAdminArea" value="<?= $admAreaHome; ?>">

	<div class="sidebar-item sidebar-menu">
		<ul>
			<li class="header-menu">
				<div class="row">
					<div class="col"><span><?= TRANS('TICKETS'); ?></span></div>
					<div class="col text-right"><span class="pin-sidebar" data-toggle="popover" data-content="<?= TRANS('MENU_SHRINK'); ?>" data-placement="top" data-trigger="hover" style="cursor:pointer;"><i class="fa fa-compress-arrows-alt small"></i></span></div>
				</div>
			</li>
			<li class='li-link'>
				<a href="#" data-app="dashboard" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fas fa-tachometer-alt"></i>
					<span class="menu-text"><?= TRANS('DASHBOARD'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="tickets_main" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fa fa-align-justify"></i>
					<span class="menu-text"><?= TRANS('QUEUE_OF_TICKETS'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="ticket_add" data-params="" data-path="<?= $ocomonPath ?>">
					<!-- <i class="fas fa-phone-volume"></i> -->
					<i class="fa fa-plus-square"></i>
					<span class="menu-text"><?= TRANS('TO_OPEN_TICKET'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="smart_search_to_report" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fa fa-filter"></i>
					<span class="menu-text"><?= TRANS('TTL_SMART_SEARCH'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="simple_search_to_report" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fa fa-search"></i>
					<span class="menu-text"><?= TRANS('SEARCH_FOR_TICKETS_BY_NUMBER'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="search_for_solutions" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fas fa-database"></i>
					<span class="menu-text"><?= TRANS('MNL_SOLUCOES'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="lendings" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fa fa-book"></i>
					<span class="menu-text"><?= TRANS('MNL_EMPRESTIMOS'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="tickets_reports" data-params="" data-path="<?= $ocomonPath ?>">
					<i class="fa fa-chart-bar"></i>
					<span class="menu-text"><?= TRANS('GENERAL_REPORTS'); ?></span>
				</a>
			</li>
		</ul>
	</div>
</div>


<!-- MENU INVENTÁRIO -->
<div class="sidebar-content" id="<?= $menuInvmon; ?>">

	<input type="hidden" name="defaultPageHome" id="defaultPageHome" value="<?= $homeHome; ?>">
	<input type="hidden" name="defaultPageOcomon" id="defaultPageOcomon" value="<?= $ocoHome; ?>">
	<input type="hidden" name="defaultPageInvmon" id="defaultPageInvmon" value="<?= $invHome; ?>">
	<input type="hidden" name="defaultPageAdmin" id="defaultPageAdmin" value="<?= $admHome; ?>">
	<input type="hidden" name="defaultPageAdminArea" id="defaultPageAdminArea" value="<?= $admAreaHome; ?>">

	<div class="sidebar-item sidebar-menu ">
		<ul>
			<li class="header-menu">
				<div class="row">
					<div class="col"><span><?= TRANS('MNS_INVENTARIO'); ?></span></div>
					<div class="col text-right"><span class="pin-sidebar" data-toggle="popover" data-content="<?= TRANS('MENU_SHRINK'); ?>" data-placement="top" data-trigger="hover" style="cursor:pointer;"><i class="fa fa-compress-arrows-alt small"></i></span></div>
				</div>
			</li>
			<li class='li-link'>
				<a href="#" data-app="inventory_main" data-params="" data-path="<?= $invmonPath ?>">
					<i class="fa fa-home"></i>
					<span class="menu-text"><?= TRANS('MNL_INICIO'); ?></span>
				</a>
			</li>

			<li class="sidebar-dropdown">
				<a href="#">
					<i class="fa fa-shopping-cart"></i>
					<span class="menu-text"><?= TRANS('BT_RECORD'); ?></span>
					<!-- <span class="badge badge-pill badge-danger">3</span> -->
				</a>
				<div class="sidebar-submenu">
					<ul>
						<li>
							<a href="#" data-app="incluir_computador" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('MNL_CAD_EQUIP'); ?></a>
						</li>
						<li>
							<a href="#" data-app="documentos" data-params="action=incluir&cellStyle=true" data-path="<?= $invmonPath ?>"><?= TRANS('DOCUMENT'); ?></a>
						</li>
						<li>
							<a href="#" data-app="equipments_models" data-params="action=new&cellStyle=true" data-path="<?= $invmonPath ?>"><?= TRANS('EQUIPMENTS_MODELS'); ?></a>
						</li>
						<li>
							<a href="#" data-app="peripherals" data-params="action=new&cellStyle=true" data-path="<?= $invmonPath ?>"><?= TRANS('MNL_COMPONENTES_MODEL'); ?></a>
						</li>
						<li>
							<a href="#" data-app="peripherals_tagged" data-params="action=new&cellStyle=true" data-path="<?= $invmonPath ?>"><?= TRANS('DETACHED_COMPONENT'); ?></a>
						</li>
					</ul>
				</div>
			</li>
			<li class="sidebar-dropdown">
				<a href="#">
					<i class="fa fa-binoculars"></i>
					<span class="menu-text"><?= TRANS('VIEW'); ?></span>
					<!-- <span class="badge badge-pill badge-danger">3</span> -->
				</a>
				<div class="sidebar-submenu">
					<ul>
						<li>
							<a href="#" data-app="equipments_list" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('MNL_VIS_EQUIP'); ?></a>
						</li>
						<li>
							<a href="#" data-app="documentos" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('DOCUMENT'); ?></a>
						</li>
						<li>
							<a href="#" data-app="equipments_models" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('EQUIPMENTS_MODELS'); ?></a>
						</li>
						<li>
							<a href="#" data-app="peripherals" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('MNL_COMPONENTES_MODEL'); ?></a>
						</li>
						<li>
							<a href="#" data-app="peripherals_tagged" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('DETACHED_COMPONENTS'); ?></a>
						</li>
					</ul>
				</div>
			</li>
			<li class="sidebar-dropdown">
				<a href="#">
					<i class="fa fa-search"></i>
					<span class="menu-text"><?= TRANS('SEARCH'); ?></span>
					<!-- <span class="badge badge-pill badge-danger">3</span> -->
				</a>
				<div class="sidebar-submenu">
					<ul>
						<li>
							<a href="#" data-app="consulta_inv" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('ASSETS_SIMPLE_SEARCH'); ?></a>
						</li>
						<li>
							<a href="#" data-app="consulta_comp" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('ASSETS_FULL_SEARCH'); ?></a>
						</li>
						<li>
							<a href="#" data-app="peripherals_tagged" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('DETACHED_COMPONENTS'); ?></a>
						</li>
					</ul>
				</div>
			</li>
			<li class="sidebar-dropdown">
				<a href="#">
					<i class="fa fa-history"></i>
					<span class="menu-text"><?= TRANS('MNL_CON_HIST'); ?></span>
					<!-- <span class="badge badge-pill badge-danger">3</span> -->
				</a>
				<div class="sidebar-submenu">
					<ul>
						<li>
							<a href="#" data-app="consulta_hist_inv" data-params="from_menu=1" data-path="<?= $invmonPath ?>"><?= TRANS('MNL_CON_HIST_TAG'); ?></a>
						</li>
						<li>
							<a href="#" data-app="consulta_hist_local" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('MNL_CON_HIST_LOCAL'); ?></a>
						</li>
					</ul>
				</div>
			</li>
			<li class='li-link'>
				<a href="#" data-app="softwares" data-params="" data-path="<?= $invmonPath ?>">
					<i class="fas fa-photo-video"></i>
					<span class="menu-text"><?= TRANS('MNL_SW'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="relatorios" data-params="" data-path="<?= $invmonPath ?>">
					<i class="fa fa-chart-pie"></i>
					<span class="menu-text"><?= TRANS('MNL_STAT_RELAT'); ?></span>
				</a>
			</li>

		</ul>
	</div>
</div>


<!-- MENU ADMINISTRAÇÃO -->
<div class="sidebar-content" id="<?= $menuAdmin; ?>">

	<input type="hidden" name="defaultPageHome" id="defaultPageHome" value="<?= $homeHome; ?>">
	<input type="hidden" name="defaultPageOcomon" id="defaultPageOcomon" value="<?= $ocoHome; ?>">
	<input type="hidden" name="defaultPageInvmon" id="defaultPageInvmon" value="<?= $invHome; ?>">
	<input type="hidden" name="defaultPageAdmin" id="defaultPageAdmin" value="<?= $admHome; ?>">
	<input type="hidden" name="defaultPageAdminArea" id="defaultPageAdminArea" value="<?= $admAreaHome; ?>">

	<div class="sidebar-item sidebar-menu ">
		<ul>
			<li class="header-menu">
				<div class="row">
					<div class="col"><span><?= TRANS('MANAGEMENT'); ?></span></div>
					<div class="col text-right"><span class="pin-sidebar" data-toggle="popover" data-content="<?= TRANS('MENU_SHRINK'); ?>" data-placement="top" data-trigger="hover" style="cursor:pointer;"><i class="fa fa-compress-arrows-alt small"></i></span></div>
				</div>
			</li>

			<li class="sidebar-dropdown">
				<a href="#">
					<i class="fas fa-cogs"></i>
					<span class="menu-text"><?= TRANS('MNL_CONF_GERAL'); ?></span>
					<!-- <span class="badge badge-pill badge-danger">3</span> -->
				</a>
				<div class="sidebar-submenu">
					<ul>
						<li>
							<a href="#" data-app="main_settings" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_CONF_BASIC'); ?></a>
						</li>
						<li>
							<a href="#" data-app="mail_settings" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_CONF_SMTP'); ?></a>
						</li>
						<li>
							<a href="#" data-app="messages_settings" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_CONF_MSG'); ?></a>
						</li>

						<li>
							<a href="#" data-app="workdays" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MENU_WORKDAYS_PROFILES'); ?></a>
						</li>

						<li>
							<a href="#" data-app="areas" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('SERVICE_AREAS'); ?></a>
						</li>


					</ul>
				</div>
			</li>

			<li class="sidebar-dropdown">
				<a href="#">
					<i class="fa fa-align-justify"></i>
					<span class="menu-text"><?= TRANS('TICKETS'); ?></span>
					<!-- <span class="badge badge-pill badge-danger">3</span> -->
				</a>
				<div class="sidebar-submenu">
					<ul>
						<li>
							<a href="#" data-app="screenprofiles" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_SCREEN_PROFILE'); ?></a>
						</li>
						
						<!-- <li>
							<a href="#" data-app="sistemas_conf_abertura" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('AREAS_CONFIG'); ?></a>
						</li> -->
						<li>
							<a href="#" data-app="types_of_issues" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('PROBLEM_TYPES'); ?></a>
						</li>
						<li>
							<a href="#" data-app="status" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('ADM_STATUS'); ?></a>
						</li>
						<li>
							<a href="#" data-app="response_levels" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_PRIORIDADES'); ?></a>
						</li>
						<li>
							<a href="#" data-app="priorities" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_PRIORIDADES_ATEND'); ?></a>
						</li>
						<li>
							<a href="#" data-app="holidays" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_FERIADOS'); ?></a>
						</li>
						<li>
							<a href="#" data-app="default_solutions" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('ADM_SCRIPT_SOLUTION'); ?></a>
						</li>
						<li>
							<a href="#" data-app="scripts_documentation" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_SCRIPTS'); ?></a>
						</li>
						
						<li>
							<a href="#" data-app="mail_templates" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_MAIL_TEMPLATES'); ?></a>
						</li>
						<li>
							<a href="#" data-app="mail_distribution_lists" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_DIST_LISTS'); ?></a>
						</li>
					</ul>
				</div>
			</li>

			<li class="sidebar-dropdown">
				<a href="#">
					<i class="fa fa-warehouse"></i>
					<span class="menu-text"><?= TRANS('MNL_INVENTARIO'); ?></span>
					<!-- <span class="badge badge-pill badge-danger">3</span> -->
				</a>
				<div class="sidebar-submenu">
					<ul>
						<li>
							<a href="#" data-app="type_of_equipments" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('ADM_EQUIP_TYPE'); ?></a>
						</li>
						<li>
							<a href="#" data-app="type_of_components" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('ADM_COMPONENT_TYPE'); ?></a>
						</li>
						<li>
							<a href="#" data-app="manufacturers" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_FABRICANTES'); ?></a>
						</li>
						<li>
							<a href="#" data-app="suppliers" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_FORNECEDORES'); ?></a>
						</li>
						<li>
							<a href="#" data-app="conditions" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_SITUACOES'); ?></a>
						</li>
						<li>
							<a href="#" data-app="warranty_times" data-params="" data-path="<?= $adminPath ?>"><?= TRANS('MNL_GARANTIA'); ?></a>
						</li>
						<!-- <li>
							<a href="#" data-app="softwares" data-params="" data-path="<?= $invmonPath ?>"><?= TRANS('MNL_SW'); ?></a>
						</li> -->
					</ul>
				</div>
			</li>


			<li class='li-link'>
				<a href="#" data-app="users" data-params="" data-path="<?= $adminPath ?>">
					<i class="fa fa-user-friends"></i>
					<span class="menu-text"><?= TRANS('MNL_USUARIOS'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="departments" data-params="" data-path="<?= $adminPath ?>">
					<i class="fas fa-address-card"></i>
					<span class="menu-text"><?= TRANS('DEPARTMENTS'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="units" data-params="" data-path="<?= $adminPath ?>">
					<i class="fa fa-city"></i>
					<span class="menu-text"><?= TRANS('MNL_UNIDADES'); ?></span>
				</a>
			</li>
			<li class='li-link'>
				<a href="#" data-app="cost_centers" data-params="" data-path="<?= $adminPath ?>">
					<i class="fa fa-file-invoice-dollar"></i>
					<span class="menu-text"><?= TRANS('COST_CENTERS'); ?></span>
				</a>
			</li>
			

		</ul>
	</div>
</div>


<!-- MENU ADMINISTRADOR DA ÁREA -->
<div class="sidebar-content" id="<?= $menuAdminArea; ?>">

	<input type="hidden" name="defaultPageHome" id="defaultPageHome" value="<?= $homeHome; ?>">
	<input type="hidden" name="defaultPageOcomon" id="defaultPageOcomon" value="<?= $ocoHome; ?>">
	<input type="hidden" name="defaultPageInvmon" id="defaultPageInvmon" value="<?= $invHome; ?>">
	<input type="hidden" name="defaultPageAdmin" id="defaultPageAdmin" value="<?= $admAreaHome; ?>">
	<input type="hidden" name="defaultPageAdminArea" id="defaultPageAdminArea" value="<?= $admAreaHome; ?>">

	<div class="sidebar-item sidebar-menu ">
		<ul>
			<li class="header-menu">
				<div class="row">
					<div class="col"><span><?= TRANS('MANAGEMENT'); ?></span></div>
					<div class="col text-right"><span class="pin-sidebar" data-toggle="popover" data-content="<?= TRANS('MENU_SHRINK'); ?>" data-placement="top" data-trigger="hover" style="cursor:pointer;"><i class="fa fa-compress-arrows-alt small"></i></span></div>
				</div>
			</li>


			<!-- <li class='li-link'>
				<a href="#" data-app="problemas" data-params="" data-path="<?= $adminPath ?>">
					<i class="fa fa-user-friends"></i>
					<span class="menu-text"><?= TRANS('PROBLEM_TYPES'); ?></span>
				</a>
			</li> -->
			<!-- <li class='li-link'>
				<a href="#" data-app="scripts_documentation" data-params="" data-path="<?= $adminPath ?>">
					<i class="fa fa-user-friends"></i>
					<span class="menu-text"><?= TRANS('MNL_SCRIPTS'); ?></span>
				</a>
			</li> -->
			<li class='li-link'>
				<a href="#" data-app="users" data-params="" data-path="<?= $adminPath ?>">
					<i class="fa fa-user-friends"></i>
					<span class="menu-text"><?= TRANS('MNL_USUARIOS'); ?></span>
				</a>
			</li>


		</ul>
	</div>
</div>