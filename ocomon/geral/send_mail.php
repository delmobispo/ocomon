<?php session_start();
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

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);


if (!isset($_POST['numero'])) {
  exit();
}

$config = getConfig($conn);
$rowconfmail = getMailConfig($conn);

$erro = false;
$mensagem = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['field_id'] = "";

$screenNotification = "";
$mailTo = noHtml($_POST['mailTo']);
$mailToOthers = noHtml($_POST['mailToOthers']);
$mailToCopy = noHtml($_POST['mailToCopy']);
$subject = noHtml($_POST['subject']);
$message = noHtml($_POST['message']);
$numero = intval($_POST['numero']);




if (empty($mailTo) || empty($subject) || empty($message)) {

    $data['success'] = false; 
    $data['field_id'] = (empty($mailTo) ? 'mailTo' : (empty($subject) ? 'subject' : 'message'));
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');

    echo json_encode($data);
    return false;

}

if (!valida('E-mail', $mailTo, 'MAILMULTI', 1, $screenNotification)) {
    $data['success'] = false; 
    $data['field_id'] = "mailTo";
    $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
    echo json_encode($data);
    return false;
}
if (!valida('E-mail', $mailToOthers, 'MAILMULTI', 0, $screenNotification)) {
    $data['success'] = false; 
    $data['field_id'] = "mailToOthers";
    $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
    echo json_encode($data);
    return false;
}
if (!valida('E-mail', $mailToCopy, 'MAILMULTI', 0, $screenNotification)) {
    $data['success'] = false; 
    $data['field_id'] = "mailToCopy";
    $data['message'] = message('warning', 'Ooops!', $screenNotification,'');
    echo json_encode($data);
    return false;
}


if (!csrf_verify($_POST)) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

    echo json_encode($data);
    return false;
}

$qryId = "SELECT * FROM global_tickets WHERE gt_ticket = ".$numero."";
$execId = $conn->query($qryId);
$rowID = $execId->fetch();		


/* Variáveis de ambiente para os e-mails */
$VARS = array();
$VARS = getEnvVarsValues($conn, $numero);


$bodyTranslated = "";
$subjectTranslated = "";
$subjectTranslated = noHtml(transvars($subject,$VARS));
$bodyTranslated = noHtml(transvars($message,$VARS));	
    
$qryMailArea = "SELECT u.nome, s.sistema, s.sis_email as replyto FROM usuarios u, sistemas s ".
                "WHERE u.user_id='".$_SESSION['s_uid']."' and s.sis_id = u.AREA";
$execMailArea = $conn->query($qryMailArea);;
$rowMailArea = $execMailArea->fetch();


$msg = "";


    if (mail_send($rowconfmail,$mailTo,$mailToCopy,$subject,$message, $rowMailArea['replyto'], $VARS)) {
        //transvars($body,$envVars)
        $sqlHist = "INSERT INTO mail_hist (mhist_oco, mhist_listname, mhist_address, mhist_address_cc, mhist_subject, mhist_body, mhist_date, mhist_technician) ".
                    " values ('".$numero."', 'Sem gravação do nome da lista', '".$mailTo."', '".$mailToCopy."', '".$subjectTranslated."', '".$bodyTranslated."', '".date("Y-m-d H:i:s")."', '".$_SESSION['s_uid']."')";
        
        try {
            $execHist = $conn->query($sqlHist);
        }
        catch (Exception $e) {
            $screenNotification .= $e->getMessage() . "<br/>";

            $erro = true;
        }
        $msg.=TRANS('MAIL_SENT_TO').": Sem gravação do nome da lista ";

    } else {
        $erro = true;
        $screenNotification .= "A mensagem não pôde ser enviada <br/>";
        $screenNotification .= $mail->ErrorInfo . "<br/>";
        
        $msg.=TRANS('MAIL_NOT_SENT_TO').": Sem gravação do nome da lista ";
    }


    if (!empty($mailToOthers)){

        if (mail_send($rowconfmail,$mailToOthers,'',$subject,$message, $rowMailArea['replyto'], $VARS)) {

            $sqlHist = "INSERT INTO mail_hist (mhist_oco, mhist_listname, mhist_address, mhist_address_cc, mhist_subject, mhist_body, mhist_date, mhist_technician) ".
                        " values ('".$numero."', '', '".$mailToOthers."', '', '".$subjectTranslated."', '".$bodyTranslated."', '".date("Y-m-d H:i:s")."', '".$_SESSION['s_uid']."')";

            try {
                $execHist = $conn->query($sqlHist);
            }
            catch (Exception $e) {
                $screenNotification .= $e->getMessage() . "<br/>";
                $erro = true;
            }

            $msg.=TRANS('MAIL_SENT_TO').": ".$_POST['field_to_editable']." ";
        } else {
            $screenNotification .= "A mensagem não pôde ser enviada <br/>";
            $screenNotification .= $mail->ErrorInfo . "<br/>";
            $msg.=TRANS('MAIL_NOT_SENT_TO').": ".$_POST['field_to_editable']." ";
        }
    }
    if (!$erro) { 
        $_SESSION['flash'] = message('success', 'Enviado!', TRANS('EMAIL_SUCCESS_SENT'), '');
    } else {
        $_SESSION['flash'] = message('danger', 'Ooops!', $screenNotification, '');
    }


    
    echo json_encode($data);