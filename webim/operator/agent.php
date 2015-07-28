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
 


require_once('../classes/functions.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/class.thread.php');
require_once('../classes/class.threadprocessor.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.settings.php');
require_once('../classes/class.visitsession.php');
require_once('../classes/class.browser.php');


require_once('../classes/class.pagination.php');
require_once('../classes/class.geoiplookup.php');


$operator = Operator::getInstance()->GetLoggedOperator();

$threadid = verify_param("thread", "/^\d{1,8}$/");

$viewonly = false;

$viewonly = verify_param("viewonly", "/^true$/", false);


if (!isset($_REQUEST['token'])) {

  handleWithoutToken($threadid, $viewonly);
}

$token = verify_param("token", "/^\d{1,8}$/");

$thread = Thread::getInstance()->GetThreadById($threadid);
$visitSession = VisitSession::GetInstance()->GetVisitSessionById($thread['visitsessionid']);



if (!$thread || !isset($thread['token']) || $token != $thread['token']) {
  die("wrong thread");
}

$TML = new SmartyClass();

setupChatViewForOperator($thread, $visitSession, $operator, $viewonly, $TML);

 
Browser::SendHtmlHeaders();
$pparam = verify_param("act", "/^(mailthread_c)$/", "default");
    if ($pparam == "mailthread_c") {
        $TML->assignCompanyInfoAndTheme();
        $TML->assign('threadid', $thread['threadid']);
        $TML->assign('token', $thread['token']);
        $TML->assign('level', $_REQUEST['level']);
        $theme = Browser::getCurrentTheme();
        $TML->assign('theme', $theme);
        $TML->assign('email_from', Visitor::getInstance()->getEmail($threadid));
        $TML->assign('email', 'info@free-lance.ru');
        
        // отделы службы поддержки free-lance ---
        $aDetps = array();
        foreach ( $aDkoOrder as $nOrder ) {
            $aDetps[] = array( 'value'=> $nOrder, 'title' => $aDko[$nOrder]['option'] );
        }
        
        $TML->assign('depts', $aDetps);
        //---------------------------------------
        
        $TML->display('send-history-c.tpl');
    } else {
        $snd_uri = '/webim/operator/agent.php?thread='.$threadid.'&token='.$token.'&act=mailthread_c';
        $TML->assign('snd_uri', $snd_uri);
        $TML->display('chat_ajaxed.tpl');
    }


function preparePredefinedAnswers($locale) {
  return explode("\n", Settings::Get("answers_".$locale));
}


function setupChatViewForOperator($thread, $visitSession, $operator, $viewonly, &$TML) {
   
  $TML->assign('thread', $thread);
	
  $TML->assign('visit_session', $visitSession);
  $TML->assign('first_page', VisitSession::GetInstance()->getFirstPage($visitSession['visitsessionid']));
  $chats = Thread::getInstance()->CountNonEmptyThreads($visitSession['visitorid']);
  $TML->assign('chats_count', max($chats - 1, 0));
  $TML->assign('browser', get_user_agent($visitSession['useragent']));
  $TML->assign('visitor_name', preg_replace('/</', '&lt', $visitSession['visitorname']));

  if ($viewonly) {
    $TML->assign('mode', 'viewonly');
  }

  $historyParams = array('q' => $visitSession['visitorid']);  
   
  
  $root = WEBIM_ROOT;
  $history = '/operator/history.php';
  
  
  $TML->assign('servlet_root', $root);
  $TML->assign('history_servlet', $history);
  $TML->assign('history_params', $historyParams);

  $TML->assign('send_shortcut', "Enter");
  $TML->assign('isOpera95', is_agent_opera95());
  $TML->assign('userAgent', $_SERVER['HTTP_USER_AGENT']);
  $TML->assign('visitor_geodata', GeoIPLookup::getGeoDataByIP($visitSession['ip']));
  $TML->assign('predefined_answers', preparePredefinedAnswers($thread['locale']));
}

function handleWithoutToken($threadid, $viewonly) {
  $TML = new SmartyClass();
  $operator = Operator::getInstance()->GetLoggedOperator();

  $remote_level = Browser::GetRemoteLevel($_SERVER['HTTP_USER_AGENT']);
  if ($remote_level != "ajaxed") {
    die("old browser is used, please update it");
  }

  $thread = Thread::getInstance()->GetThreadById($threadid);

  if (!$thread || !isset($thread['token'])) {
    die("wrong thread");
  }

  if ($viewonly && $operator['operatorid'] != $thread['operatorid']) {
    redirectToPageWithToken($thread, $viewonly, $remote_level);
  }

  $forcetake = verify_param("force", "/^true$/", false);

  if ($forcetake) {
    ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], 'operator_force_join', array("operatorid"=>$operator["operatorid"]));
    redirectToPageWithToken($thread, null, $remote_level);
  } else {
    // is taken by another
    $visitSession = VisitSession::GetInstance()->GetVisitSessionById($thread['visitsessionid']);
    if ($thread['state'] != STATE_CLOSED
        && !empty($thread['operatorid'])
        && $operator['operatorid'] != $thread['operatorid']        
        && $operator['operatorid'] != $thread['nextoperatorid']) {
      $page = array(
        'visitor'  => $visitSession['visitorname'],
        'agent' => $thread['operatorfullname'],
        'force' => true,
        'takelink' => $_SERVER['PHP_SELF']."?thread=$threadid&amp;force=true",
        'viewlink' => $_SERVER['PHP_SELF']."?thread=$threadid&amp;viewonly=true",
        'priority' => $thread['state'] == STATE_QUEUE_EXACT_OPERATOR || $thread['state'] == STATE_CHATTING_OPERATOR_BROWSER_CLOSED_REFRESHED
      ); // TODO get rid of STATE_WAIT_ANOTHER_OPERATOR 
      $TML->assign('link_arguments', "&thread=$threadid");  
      $TML->assign('page', $page);
      $TML->display('confirm.tpl');
      exit;
    }

    // is closed
    if ($thread['state'] == STATE_CLOSED) {
      $page = array(
      'viewlink' => $_SERVER['PHP_SELF']."?thread=".$threadid."&amp;viewonly=true",
      'force'    => false,
      'thread_id'    => $threadid,
      'closed' => true
      );
      $TML->assign('link_arguments', "&thread=$threadid");
      $TML->assign('page', $page);
      $TML->display('confirm.tpl');
      exit;
    }

  }

  ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], 'operator_join', array('operatorid'=>$operator['operatorid']));
  redirectToPageWithToken($thread, $viewonly, $remote_level);

}

function redirectToPageWithToken($thread, $viewonly, $remote_level) {
  $token = $thread['token'];
  $lang = verify_param("lang", "/^[\w-]{2,5}$/", "");
  $lang_param = !empty($lang) ? "&lang=$lang" : "";
  $viewonly_param = !empty($viewonly) ? "&viewonly=true" : "";
  $url = WEBIM_ROOT."/operator/agent.php?thread=".$thread['threadid']."&token=".$token."&level=".$remote_level.$viewonly_param.$lang_param;

  header("Location: ".$url);
  exit;
}

?>
