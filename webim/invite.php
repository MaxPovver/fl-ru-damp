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


require_once('classes/common.php');
require_once('classes/class.thread.php');
require_once('classes/class.visitsession.php');
require_once('classes/class.visitedpage.php');
require_once('classes/class.invitation.php');
require_once('classes/class.operator.php');
require_once('classes/class.smartyclass.php');


 

$pageId = verify_param("pageid", "/^[a-z0-9]{32}$/");
$isSecure = verify_param("issecure", "/^\d+$/", 0) == 1;

$visitSession = VisitSession::GetInstance()->GetVisitSessionByPageId($pageId);
$invitation = Invitation::GetInstance()->GetInvitationByVisitedPageId($pageId);
$thread = Thread::getInstance()->GetThreadById($invitation['threadid']);

$message = getInvitationMessage($invitation);

// set invitation parameters
$p_location = get_app_location(true, $isSecure);
$p_theme = Browser::getCurrentTheme();
$p_message  = $message." <img src=\"$p_location/themes/$p_theme/images/invite/bullet5.gif\"/>";
$p_sound    = $p_location."/sounds/default_invite.wav";
$p_hideanim = $p_location."/track.php?issecure=$isSecure&";
$p_level    = Browser::GetRemoteLevel($visitSession['useragent']);
$p_threadid = $thread['threadid'];
$p_token    = $thread['token'];
$p_pageid   = $pageId;
$p_lang     = WEBIM_CURRENT_LOCALE;
$p_invitation = getInvitationContent(getAvatar($thread['operatorid']), $message, $isSecure);
$p_amination_duration = INVITE_ANIMATION_DURATION;

header('Content-type: text/javascript; charset='.BROWSER_CHARSET);
require('js/invite/invitation.js');

function getAvatar($operatorId) {
  $operator = Operator::getInstance()->GetOperatorById($operatorId);
  $avatar = $operator['avatar'];
  return $avatar;
}

function getInvitationMessage($invitation) {
  $search  = array("\n", "\r");
  $replace = array("<br/>", "");
  $messageObj = Invitation::GetInstance()->GetInvitationMessageById($invitation['invitemessageid']);
  return str_replace($search, $replace, $messageObj['message']);
}

function getInvitationContent($avatar, $message, $isSecure) {
  $host = ($isSecure ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
  $TML = new SmartyClass();
  $TML->assign('message', empty($message) ? Resources::Get('invite.default.message') : $message);
  $TML->assign('operatorimage', empty($avatar) ? null : $host.$avatar);    
  $TML->assign('theme', Browser::getCurrentTheme());
  $TML->assign('addressprefix', $host);  
  
  $invitation = $TML->fetch('invite.tpl');

  $invitation = addslashes($invitation);
  $invitation = str_replace("\n", "' + \n'", $invitation);
  $invitation = str_replace("\r", '', $invitation);
  
  return $invitation;
}
?>