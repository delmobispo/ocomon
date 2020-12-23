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

include "../../includes/include_geral_new.inc.php";

require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";
use includes\classes\ConnectPDO;
$conn = ConnectPDO::getInstance();

$post = $_POST;

$screenNotification = "";
$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['field_id'] = "";

$data['user'] = (isset($post['user']) ? noHtml($post['user']) : "");
$data['pass'] = (isset($post['pass']) ? $post['pass'] : "");

/* Validações */
if (empty($data['user']) || empty($data['pass'])) {
    $data['success'] = false; 
    $data['field_id'] = (empty($data['user']) ? 'user' : 'pass');
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
    echo json_encode($data);
    return false;
}

if (!valida(TRANS('FIELD_USER'), $data['user'], 'USUARIO', 1, $ERRO)) {
    $data['success'] = false; 
    $data['field_id'] = "user";
    $data['message'] = message('warning', '', $ERRO, '');
    echo json_encode($data);
    return false;
}

/* Validação de user e pass */
if (!(pass($conn, $data['user'], $data['pass']))) {
    $data['success'] = false; 
    $data['field_id'] = (empty($data['user']) ? 'user' : 'pass');
    $data['message'] = message('danger', 'Ooops!', TRANS('ERR_LOGON'),'');
    echo json_encode($data);
    return false;
}

$userInfo = getUserInfo($conn, 0, $data['user']);
$firstLogon = ($userInfo['last_logon'] == "" ? true : false);
updateLastLogon($conn, $userInfo['user_id']);

$area = $userInfo['area_id'];
$secondaryAreas = "";
$secondaryAreas .= getUserAreas($conn, $userInfo['user_id']); /* apenas secundárias */
$allAreas = (!empty($secondaryAreas) ? $area . "," . $secondaryAreas : $area);

$mod_tickets = getModuleAccess($conn, 1, $allAreas);
$mod_inventory = getModuleAccess($conn, 2, $allAreas);

$modulos = "";
if ($mod_tickets)
    $modulos = '1';
if ($mod_inventory) {
    if (strlen($modulos))
        $modulos .= ",";
    $modulos = '2';
}
$_SESSION['s_permissoes'] = $modulos;

$config = getConfig($conn);

$_SESSION['s_logado'] = 1;
$_SESSION['csrf_token'] = "";
$_SESSION['s_usuario'] = $data['user'];
$_SESSION['s_usuario_nome'] = $userInfo['nome'];
$_SESSION['s_uid'] = $userInfo['user_id'];
$_SESSION['s_nivel'] = $userInfo['nivel'];
$_SESSION['s_nivel_desc'] = $userInfo['nivel'];
$_SESSION['s_area'] = $userInfo['area_id'];
$_SESSION['s_uareas'] = $allAreas;

$_SESSION['s_area_admin'] = $userInfo['user_admin'];
$_SESSION['s_ocomon'] = $mod_tickets;
$_SESSION['s_invmon'] = $mod_inventory;
$_SESSION['s_screen'] = $userInfo['sis_screen'] ?? 2;
// $_SESSION['s_screen'] = $userInfo['sis_screen'];
$_SESSION['s_wt_areas'] = $config['conf_wt_areas']; //1: origem , 2: destino

$_SESSION['s_formatBarOco'] = 0;
$_SESSION['s_formatBarMural'] = 0;

if (strpos($config['conf_formatBar'], '%oco%')) {
    $_SESSION['s_formatBarOco'] = 1;
}
if (strpos($config['conf_formatBar'], '%mural%')) {
    $_SESSION['s_formatBarMural'] = 1;
}

$_SESSION['s_language'] = (!empty($userInfo['language']) ? $userInfo['language'] : $config['conf_language']);
$_SESSION['s_date_format'] = $config['conf_date_format'];
$_SESSION['s_paging_full'] = 0;
$_SESSION['s_page_size'] = $config['conf_page_size'];
$_SESSION['s_allow_reopen'] = $config['conf_allow_reopen'];
$_SESSION['s_allow_date_edit'] = $config['conf_allow_date_edit'];
$_SESSION['s_ocomon_site'] = $config['conf_ocomon_site'];

$_SESSION['s_colorDestaca'] = "#CCCCCC";
$_SESSION['s_colorMarca'] = "#FFFFCC";
$_SESSION['s_colorLinPar'] = "#E3E1E1";
$_SESSION['s_colorLinImpar'] = "#F6F6F6";


$data['success'] = true; 
$message = ($firstLogon ? TRANS('MSG_WELCOME') : TRANS('MSG_WELCOME_BACK'));
$_SESSION['flash'] = message('success', TRANS('MSG_HELLO') . " " . firstLetterUp(firstWord($userInfo['nome'])) . "!", $message, '');
echo json_encode($data);
