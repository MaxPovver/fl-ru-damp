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
 

require_once ('classes/functions.php');
require_once ('classes/class.browser.php');
require_once ('classes/class.thread.php');
require_once ('classes/class.threadprocessor.php');
require_once ('classes/class.operator.php');
require_once ('classes/class.invitation.php');
require_once ('classes/class.eventcontroller.php');
require_once ('classes/class.visitor.php');
require_once ('classes/events_register.php');

session_start();

ThreadProcessor::getInstance()->ProcessOpenThreads();

$act = verify_param("act", "/^(refresh|post|rename|close|ping"."|contacts|rate".")$/");
$token = verify_param("token", "/^\d{1,9}$/");
$threadid = verify_param("thread", "/^\d{1,9}$/");
$isvisitor = verify_param("visitor", "/^true$/", "false") == 'true';
$outformat = ((verify_param("html", "/^on$/", "off") == 'on') ? "html" : "xml");
$istyping = verify_param("typed", "/^1$/", "") == '1';
 
$viewonly = verify_param("viewonly", "/^true$/", "false") == 'true';


if (!$isvisitor) {
  $o = Operator::getInstance();
  $operator = $o->GetLoggedOperator(false);
  $f = "i"."s"."O"."p"."er"."a"."to"."rsL"."im"."it"."E"."x"."ce"."ed"."ed";
  if ($o->$f()) {
    die();
  }
}

$thread = Thread::getInstance()->GetThreadById($threadid);
if(empty($thread) || ! isset($thread['token']) || $token != $thread['token']) {
  die("wrong thread in thread.php");
}

if($isvisitor && !visitorHasAccess($thread)) {
  show_error("server: visitor has no access to the thread");
}

if($isvisitor) {
  ThreadProcessor::getInstance()->ProcessThread($threadid, 'visitor_ping', array(
    'istyping' => !empty($istyping)
  ));
}
if(! $isvisitor && ! $viewonly) {
  EventController::getInstance()->dispatchEvent(
  	EventController::EVENT_OPERATOR_PING,
  	array($threadid, 
  		'operator_ping', 
  		array(
    		'istyping' => ! empty($istyping),
        'operatorid' => $operator['operatorid']
  		)
  	)
  );
}


$operator = null;

if (!$isvisitor && !$viewonly) {

  $operator = Operator::getInstance()->GetLoggedOperator();




  
  $viewonly = $thread['operatorid'] != $operator['operatorid'] && !empty($thread['operatorid']);
  







}




//$visitSession = VisitSession::GetInstance()->GetVisitSessionById($thread['visitsessionid']);
$v = GetVisitorFromRequestAndSetCookie();

if($act == "refresh") {
  $lastid = verify_param("lastid", "/^\d{1,9}$/", - 1);

  if ($isvisitor &&!empty($thread['operatorid'])) {
    setcookie('WEBIM_LAST_OPERATOR_ID', $thread['operatorid'], time()+60*60*24*365, '/');
  }
  Thread::getInstance()->PrintThreadMessages($thread, $token, $lastid, $isvisitor, $outformat, $viewonly);
  exit();
} elseif($act == "post") {
  $lastid = verify_param("lastid", "/^\d{1,9}$/", - 1);
  if(isset($_REQUEST['message'])) {
    $message = trim(smarticonv("UTF-8", WEBIM_ENCODING, $_REQUEST['message']));
  }
  


    
  $kind = $isvisitor ? KIND_USER : KIND_AGENT;
  
  
  $lastSentMessage  = isset($_SESSION['LAST_SENT_MESSAGE']) ? $_SESSION['LAST_SENT_MESSAGE'] : '';
  $lastMessageTime  = isset($_SESSION['LAST_MESSAGE_TIME']) ? $_SESSION['LAST_MESSAGE_TIME'] : 0;
  $lastSentThreadId = isset($_SESSION['LAST_SENT_THREAD_ID']) ? $_SESSION['LAST_SENT_THREAD_ID'] : 0;
  $lastSentWasVisitor = isset($_SESSION['LAST_SENT_WAS_VISITOR']) ? $_SESSION['LAST_SENT_WAS_VISITOR'] : false;

  $isSendingDuplicatedMessage = $lastSentMessage == $message 
                                && (time() - $lastMessageTime) <= 60 
                                && $threadid == $lastSentThreadId 
                                && $lastSentWasVisitor == $isvisitor;






  if (!$viewonly && !$isSendingDuplicatedMessage) {
    
    $hash = array(
      'threadid' => $threadid, 'kind' => $kind, 'message' => $message, 'created' => null 
    );
    if(! $isvisitor) {
      $hash['operatorid'] = $operator['operatorid'];
      $hash['sendername'] = $operator['fullname'];
    } else {
      //$hash['sendername'] = $visitSession['visitorname'];
      $hash['sendername'] = $v['name'];
    }
    $postedid = ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], $isvisitor ? 'visitor_message' : 'operator_message', $hash);
    
    
    $_SESSION['LAST_SENT_MESSAGE'] = $message;
    $_SESSION['LAST_MESSAGE_TIME'] = time();
    $_SESSION['LAST_SENT_THREAD_ID'] = $threadid;
    $_SESSION['LAST_SENT_WAS_VISITOR'] = $isvisitor;

    if($isvisitor && empty($thread["shownmessageid"])) {
      Thread::getInstance()->CommitThread($thread['threadid'], array(
        'shownmessageid' => $postedid
      ));
    }

    if (!$isvisitor && $thread['state'] == STATE_INVITE) {
      Invitation::GetInstance()->UpdateInvitationMessage($thread['threadid'], $postedid);
    }
  }
  
  
  Thread::getInstance()->PrintThreadMessages($thread, $token, $lastid, $isvisitor, $outformat, $viewonly);
  exit();

} elseif($act == "rename") {
  
  if(! Visitor::getInstance()->canVisitorChangeName()) {
    show_error("server: forbidden to change name");
  }
  
   
  
  if(! empty($_REQUEST['name'])) {
    $newname = smarticonv('UTF-8', WEBIM_ENCODING, $_REQUEST['name']);
    Thread::getInstance()->RenameVisitor($thread, $newname);
    Visitor::getInstance()->setVisitorNameCookie($newname);
  }
  
  //        $data = strtr(base64_encode($newname), '+/=', '-_, ');
  show_ok_result("rename");

} elseif($act == "ping") {
  show_ok_result("ping");

} elseif($act == "close") {
  if($isvisitor) { 
    ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], 'visitor_close');
    $visitor = GetVisitorFromRequestAndSetCookie();
    
    $threads = MapperFactory::getMapper("Thread")->getOpenThreadsForVisitor($visitor['id']);
    if(count($threads) < 1) {
      VisitSession::GetInstance()->deleteVisitSessionCurrentPageFile($visitor['id']);
    }
    
  } elseif($thread['operatorid'] == $operator['operatorid']) {
    ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], 'operator_close');
  }
  
  show_ok_result("closed");

} elseif($act == "browser_unload") {
  
  //        if ($isvisitor || $thread['operatorid'] == $operator['operatorid']) {
  //            $obj->CloseThread($thread, $isvisitor);
  //        }
  if($isvisitor) {
    ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], 'visitor_browser_unload');
  }
  
  show_ok_result("closed");

} elseif($act == 'rate') {

  if(! $isvisitor) {

    show_error("visitor-only operation");
  }
  
  $rate = verify_param("rate", "/^-?\d{1,9}$/", "0");
  


  Thread::getInstance()->RateOperator($thread, $rate);  
  

  show_ok_result("rate");
} elseif($act == "contacts") {
  
  $name = ! empty($_REQUEST['name']) ? smarticonv("UTF-8", WEBIM_ENCODING, $_REQUEST['name']) : "";
  $email = ! empty($_REQUEST['email']) ? smarticonv("UTF-8", WEBIM_ENCODING, $_REQUEST['email']) : "";
  
  Thread::getInstance()->PostMessage($thread['threadid'], KIND_INFO, Resources::Get('contacts.submitted', array(
    $name, $email
  )));
  
  updateContacts($name, $email, $phone, $threadid, $thread['visitsessionid']);
  show_ok_result("contacts");
}



function show_ok_result($resid) {
  Browser::SendXmlHeaders();
  echo "<$resid></$resid>";
  exit();
}

function show_error($message) {
  Browser::SendXmlHeaders();
  echo "<error><descr>$message</descr></error>";
  exit();
}

?>