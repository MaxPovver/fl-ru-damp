<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php


require_once('classes/functions.php');
require_once('classes/class.thread.php');
require_once('classes/class.smartyclass.php');
require_once('classes/class.visitsession.php');


$errors = array();
$page = array();

$token = verify_param("token", "/^\d{1,8}$/");
$threadid = verify_param("threadid", "/^\d{1,8}$/");

$thread = Thread::getInstance()->GetThreadById($threadid);

if (!$thread || !isset($thread['token']) || $token != $thread['token']) {
  die("wrong thread");
}

$email = !empty($_POST['email']) ? trim($_POST['email']) : false;
$email_from = !empty($_POST['email_from']) ? trim($_POST['email_from']) : false;
$mode = !empty($_POST['mode']) ? trim($_POST['mode']) : false;
$dept = !empty($_POST['dept']) ? trim($_POST['dept']) : false;

// отправке диалогов из мессенджера ----------
if ( $dept && isset($aDko[$dept]['email']) ) {
	$email = $aDko[$dept]['email'];
}

$TML = new SmartyClass();
$TML->assignCompanyInfoAndTheme();

$has_errors = false;
if ( $mode != 'cons' && empty($email) ) {
  $TML->assign('erroremail', true);
  $has_errors = true;
} 
elseif ( $mode != 'cons' && !is_valid_email($email) ) {
  $TML->assign('erroremailformat', true);
  $has_errors = true;
}

if ($mode == 'cons' && empty($email_from)) {
  $TML->assign('erroremail_from', true);
  $has_errors = true;
} elseif ($mode == 'cons' &&  !is_valid_email($email_from)) {
  $TML->assign('erroremailformat_from', true);
  $has_errors = true;
}

if ($has_errors) {
  $TML->assign('threadid', $_REQUEST['threadid']);
  $TML->assign('token', $_REQUEST['token']);
  $TML->assign('level', $_REQUEST['level']);
  if($mode != 'cons') $TML->display('send-history.tpl');
  else {
      // отделы службы поддержки free-lance ---
        $aDetps = array();
        foreach ( $aDkoOrder as $nOrder ) {
            $aDetps[] = array( 'value'=> $nOrder, 'title' => $aDko[$nOrder]['option'] );
        }
        
        $TML->assign('depts', $aDetps);
        //---------------------------------------
        
      $TML->display('send-history-c.tpl');
  }
  exit;
}

$eHistory = $history = "";
$lastid = -1;

$output = Thread::getInstance()->GetMessages($threadid, "text", true, $lastid);

foreach ($output as $msg) {
  $history .= $msg;
  $eHistory .= $msg;
}

$visitSession = VisitSession::GetInstance()->GetVisitSessionById($thread['visitsessionid']);

$systemInfo = 
"Системная информация
имя: " . $visitSession['visitorname'] . "
создан: " . date('Y-m-d', $visitSession['created']) . "
ip: {$visitSession['ip']}
браузер: " . get_user_agent($visitSession['useragent']) . "

";

$history = $systemInfo . $history;

$subject = Resources::Get("mail.visitor.history.subject");

// отправке диалогов из мессенджера ------------
if ( $dept && isset($aDko[$dept]['subject']) ) {
	$subject = $aDko[$dept]['subject'];
}

$visitor_name = $visitSession['visitorname'];
$body = Resources::Get("mail.visitor.history.body", array($visitor_name, $history));

// отправке диалогов из мессенджера
if ( $dept && ($feedback = feedbackAdd($dept, $visitor_name, $email_from, $body, 0)) ) {
    $body .= "\n" . '[[UCODE::{' . $feedback['uc'] . '},FID::{' . $feedback['id']  .'}]]';
}

$webim_from_email = $email_from ? $email_from : Settings::Get("from_email");

$body = Resources::Get("mail.visitor.history.body", array( $visitor_name, $history ));
webim_mail($email, $webim_from_email, $subject, $body, 0);

// отправке диалогов из мессенджера ------------
if ( $dept && isset($aDko[$dept]['option']) ) {
    $sMsg = 'Диалог был отправлен в раздел: ' . $aDko[$dept]['option'];
    Thread::getInstance()->PostMessage( $threadid, KIND_EVENTS, $sMsg );
}

$TML->display('send-history-sent.tpl');
?>