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
require_once('classes/class.settings.php');
require_once('classes/class.visitor.php');

require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/captcha.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/feedback.php' );


$captcha = new captcha();
    
$TML = new SmartyClass();
$TML->assignCompanyInfoAndTheme();
$errors = array();
$page = array();


$email = get_mandatory_param('email');
$phone = get_mandatory_param('phone');
// отправке диалогов
$dept = 1; // пока не выбирается

$canChangeName = Visitor::getInstance()->canVisitorChangeName();
$v = GetVisitorFromRequestAndSetCookie();
$visitorid = $v['id'];
$captcha_num  = $v['captcha'];

if ($canChangeName) {
  $visitor_name = get_mandatory_param('name');
} else {
  $visitor_name = $v['name'];
}

$message = get_mandatory_param('message');

$has_errors = false;

if (empty($email)) {
    $TML->assign('erroremail', true);
    $has_errors = true;
} elseif(!$captcha->checkNumber($captcha_num)) {
    $TML->assign('errorcaptcha', true);
    $has_errors = true;
} elseif (empty($visitor_name) && $canChangeName) {
    $TML->assign('errorname', true);
    $has_errors = true;
} elseif (empty($message)) {
    $TML->assign('errormessage', true);
    $has_errors = true;
} else {
  if (!is_valid_email($email)) {
    $TML->assign('erroremailformat', true);
    $has_errors = true;
  }
}

$captcha->setNumber();

if ($has_errors) {
  $TML->assign('name', getSecureText($visitor_name));
  $TML->assign('email', getSecureText($email));
  $TML->assign('phone', getSecureText($phone));
  $TML->assign('message', getSecureText($message));
  $TML->assign('canChangeName', getSecureText($canChangeName));
  $TML->assign('captcha_num', "");

  $TML->display('leave-message.tpl');
  exit();
}

$visitSessionId = VisitSession::GetInstance()->updateCurrentOrCreateSession();

$params = array();
$params['visitsessionid'] = $visitSessionId;
$params['lastpingvisitor'] = null ;
$params['offline'] = 1;

$threads_count = MapperFactory::getMapper("Thread")->getNonEmptyThreadsCountByVisitorId($visitorid);

$thread = Thread::getInstance()->CreateThread(WEBIM_CURRENT_LOCALE, STATE_CLOSED, $params);
VisitSession::GetInstance()->UpdateVisitSession($visitSessionId, array('hasthread' => 1));
Thread::getInstance()->sendFirstMessageWithVisitorInfo($thread);

Thread::getInstance()->PostMessage($thread['threadid'], KIND_USER, Resources::Get('chat.window.offline_message', array($message)));
MapperFactory::getMapper("Thread")->incrementVisitorMessageCount($thread['threadid']);

$first_message = MapperFactory::getMapper("Message")->getFirstMessage($thread['threadid']);

Visitor::getInstance()->setVisitorNameCookie($visitor_name);

// отправке диалогов из мессенджера
$subject = ( $dept && isset($aDko[$dept]['subject']) ) ? $subject = $aDko[$dept]['subject'] : Resources::Get("leavemail.subject", array($visitor_name), WEBIM_CURRENT_LOCALE);

$body = Resources::Get(
  "leavemail.body", 
  array(
    $visitor_name, 
    $email, 
    $message, 
    $phone, 
    Thread::getInstance()->formatOpenerWithTitle(), 
    HTTP_PREFIX.$_SERVER['HTTP_HOST'].WEBIM_ROOT."/operator/threadprocessor.php?threadid=".$thread['threadid'],
    str_replace("\n", "\n\n", $first_message['message'])
  ), 
  WEBIM_CURRENT_LOCALE
);

// отправке диалогов из мессенджера
if ( $dept && ($feedback = feedbackAdd($dept, $visitor_name, $email, $body, get_uid(false))) ) {
    $body .= "\n" . '[[UCODE::{' . $feedback['uc'] . '},FID::{' . $feedback['id']  .'}]]';
}

// отправке диалогов из мессенджера
$inbox_mail = ( $dept && isset($aDko[$dept]['email']) ) ? $aDko[$dept]['email'] : Settings::Get('offline_email');

webim_mail($inbox_mail, $visitor_name.'<'.$email.'>', $subject, $body);

$TML->display('leave-message-sent.tpl');
?>
