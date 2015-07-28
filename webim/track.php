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
require_once('classes/class.visitsession.php');
require_once('classes/class.visitedpage.php');
require_once('classes/class.invitation.php');


 

$trackStateStrings = array(
  INVITATION_UNINITIALIZED  => "uninitialized",
  INVITATION_CAN_BE_SENT    => "can-be-sent",
  INVITATION_SENT           => "sent",
  INVITATION_ACCEPTED       => "accepted",
  INVITATION_REJECTED       => "rejected",
  INVITATION_TIMEOUT        => "timeout",
  INVITATION_MISSED         => "missed"
);

if(!Operator::getInstance()->hasViewTrackerOperators())
	die();

$event = verify_param("event", "/^(init|poll|accept|reject|timeout|left)$/");

$visitsessionid = VisitSession::GetInstance()->updateCurrentOrCreateSession();


if ($event == "init") {
  initVisitedPage($visitsessionid, Browser::getCurrentTheme());
  exit;
}

$pageid = verify_param("pageid", "/^[a-z0-9]{32}$/");


// FIXME: do we really need this udpate?
VisitSession::GetInstance()->UpdateVisitSession($visitsessionid);
VisitedPage::GetInstance()->UpdateVisitedPage($pageid);

$visitedpage = VisitedPage::GetInstance()->GetVisitedPageById($pageid);

$state = Invitation::GetInstance()->GetInvitationState($pageid);


$showInvitation = NULL;
$nextState = $state;

switch($state) {
  case INVITATION_UNINITIALIZED:
    switch($event) {
      case "poll":
        if (VisitedPage::GetInstance()->HasPendingInvitation($pageid)) {
          $showInvitation = true;
          $nextState = INVITATION_SENT;
        } else {
          $showInvitation = false;
        }
        break;
      case "left":
        setVisitedPageClosed($pageid);
        break;
    }
    break;
  case INVITATION_CAN_BE_SENT:
     switch($event) {
       case "left":
         setVisitedPageClosed($pageid);
         notifyOperatorOnHideInvitation($pageid, $event);
         VisitSession::GetInstance()->UnsetVisitSession();
         $nextState = INVITATION_MISSED;
         break;
       case "poll":
         if (VisitedPage::GetInstance()->HasPendingInvitation($pageid)) {
           $showInvitation = true;
           $nextState = INVITATION_SENT;
         } else {
           $showInvitation = false;
         }
         break;
     }
     break;
  case INVITATION_SENT:
    switch($event) {
      case "poll":
        if (VisitedPage::GetInstance()->HasPendingInvitation($pageid)) {
          $showInvitation = true;
        } else {
          $showInvitation = false;
          // Operator closed the window
          $nextState = INVITATION_CAN_BE_SENT;
        }
        break;
      case "accept":
        $nextState = INVITATION_ACCEPTED;
        break;
      case "reject":
        notifyOperatorOnHideInvitation($pageid, $event);
        $nextState = INVITATION_REJECTED;
        break;
      case "timeout":
        notifyOperatorOnHideInvitation($pageid, $event);
        $nextState = INVITATION_TIMEOUT;
        break;
      case "left":
        setVisitedPageClosed($pageid);
        notifyOperatorOnHideInvitation($pageid, $event);
        $nextState = INVITATION_MISSED;
    }
    break;
  case INVITATION_ACCEPTED:
    switch($event) {
      case "left":
        setVisitedPageClosed($pageid);
        VisitSession::GetInstance()->UnsetVisitSession();
        $nextState = INVITATION_MISSED;
        break;
      case "poll":
        $showInvitation = false;
      default:
        // FIXME: Visited page can not be in chat
        if (!VisitedPage::GetInstance()->IsInChat($pageid)) {
           $nextState = INVITATION_CAN_BE_SENT;
        }
        break;
    }
    break;
  case INVITATION_REJECTED:
  case INVITATION_TIMEOUT:
    switch($event) {
      case "left":
        setVisitedPageClosed($pageid);
        VisitSession::GetInstance()->UnsetVisitSession();
        $nextState = INVITATION_MISSED;
        break;
      case "poll":
        $showInvitation = false;
      default:
        $nextState = INVITATION_CAN_BE_SENT;
    }
    break;
  case INVITATION_MISSED:
    break;
}




setNextState($nextState);
if (isset($showInvitation)) {
  sendShowInvitation($showInvitation);
}
exit;

function notifyOperatorOnHideInvitation($pageid, $event) {
  $invitation = Invitation::GetInstance()->GetInvitationByVisitedPageId($pageid);
  $threadid = $invitation['threadid'];





  
  if (!empty($threadid)) {
    $reasonText = null;
    if ($event == "reject") {
      $reasonText =  Resources::Get('invite.visitor.closed.invitation', array(), WEBIM_CURRENT_LOCALE);
    } elseif ($event == "timeout") {
      $reasonText =  Resources::Get('invite.invitation.timout', array(), WEBIM_CURRENT_LOCALE);
    } elseif ($event == "left") {
      $reasonText = Resources::Get('invite.window.closed', array(), WEBIM_CURRENT_LOCALE);
    }


    if (!empty($reasonText)) {
      ThreadProcessor::GetInstance()->ProcessThread($threadid, 'visitor_invite_close', array('message' => $reasonText));
    }
  }
}

function initVisitedPage($visitsessionid, $theme) {
  $url = $_GET['url']; // TODO why we use _GET?
  $referrer = $_GET['from']; // TODO let's call referer 'referer' buy not from?
  $isSecure = isset($_GET['issecure']) ? $_GET['issecure'] : FALSE; // TODO, can we use false? hope so ...
  $title = isset($_GET['title']) ? $_GET['title'] : null;
  if(WEBIM_ENCODING != 'UTF-8') {
  	$title = smarticonv('utf-8', WEBIM_ENCODING, $title);
  }
  $title = removeSpecialSymbols($title);

  $p_pageid = VisitedPage::GetInstance()->CreateVisitedPage($visitsessionid, $url, $referrer, $title);
  $p_location = get_app_location(true, $isSecure);
  $p_invitescript = $p_location."/invite.php?pageid=".$p_pageid."&theme=".$theme."&issecure=$isSecure";
  $p_issecure = $isSecure;

  require('js/invite/tracking.js');
}

function sendShowInvitation($showInvitation) {
  $filename = $showInvitation ? 'images/4.gif' : 'images/free.gif';
  $fp = fopen($filename, 'rb') or die("no image");
  header("Content-Type: image/gif");
  header("Content-Length: ".filesize($filename));
  fpassthru($fp);
}

function setNextState($state) {
  global $pageid;

  if ($state == INVITATION_UNINITIALIZED) {
    return;
  }




  VisitedPage::GetInstance()->SetInvitationState($pageid, $state);
}

function setVisitedPageClosed($pageid) {
  VisitedPage::GetInstance()->UpdateVisitedPage($pageid, array("state" => VISITED_PAGE_CLOSED));
}

?>