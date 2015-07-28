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
require_once('classes/class.browser.php');
require_once('classes/class.thread.php');
require_once('classes/class.operator.php');
require_once('classes/class.visitor.php');
require_once('classes/class.threadprocessor.php');
require_once('classes/class.smartyclass.php');
require_once('classes/class.visitsession.php');

define('AUTOBAN_PERIOD', 20*60);
// 20 minutes

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
$captcha = new captcha();

$theme = Browser::getCurrentTheme();

$thread = tryToGetExistingThread();

if(!isset($_REQUEST['captcha'])) {
    $captcha->setNumber();    
}

$isBanned = MapperFactory::getMapper("Ban")->isBanned(Browser::GetExtAddr());

$shouldBeBanned = !$isBanned && empty($thread) && is_numeric(Settings::Get("max_sessions")) && Settings::Get("max_sessions") > 0 && MapperFactory::getMapper("Thread")->countOpenThreadsForIP(Browser::GetExtAddr()) + 1 > Settings::Get("max_sessions");

if ($shouldBeBanned) {
    $utime = getCurrentTime() + AUTOBAN_PERIOD;
    // BAN 24*60*60
    $hashTable = array(
    'till' => date("Y-m-d H:i:s", $utime),
    'address' => Browser::GetExtAddr(),
    'comment' => Resources::Get('ban.autoban.message', Settings::Get("max_sessions")),
    'created' => null 
    );
    MapperFactory::getMapper("Ban")->save($hashTable);
    $isBanned = true;
}

$departmentkey = verify_param("departmentkey", "/^\w+$/");


$numberOfOnline = Operator::getInstance()->countOnlineOperators(null, $departmentkey, Resources::getCurrentLocale());
//TODO: write this form departments $departmentkey, Resources::getCurrentLocale());

if ($numberOfOnline == 0 && empty($thread)) {
    showLeaveMessagePage();
} elseif ($isBanned) {
    displayBanPage();
    exit();
} elseif (!empty($thread) && !hasThreadIdParam()) {

    sendLocation($thread);
    exit();
} elseif (!empty($thread) && hasThreadIdParam()) {
    switch ($_GET['action']) {
    	case "feedback":
    		$thread['state'] = STATE_CLOSED;
            showFeedbackPage();
            break;
        case "leave":
            showLeaveMessageSentPage();
            break;
        default:
            openChatPage($thread);
    }
    exit();
} elseif (isInvite($thread) && !empty($thread)) {

    visitorAcceptedInvite($thread);
    openChatPage($thread);
    exit();
} else {
    switch ($_GET['action']) {
        case "feedback":
            showFeedbackPage();
            break;
        case "leave":
            showLeaveMessageSentPage();
            break;
    }
}



if (shouldChooseDepartment()) {
    exit();
}

if (shouldChooseOpeartor($numberOfOnline)) {
    exit();
}

if (shouldEnterFirstMessage()) {
    exit();
}


$thread = createNewThread();
sendLocation($thread);

function shouldChooseOpeartor($numberOfOnline) {	
    $chooseoperator = verify_param("chooseoperator", "/^\w+$/", "");
    $operatorid = verify_param("operatorid", "/^(\d)$/");
    
    // #0017905
    /*switch ($chooseoperator) {
    case null:
    case '':
    case 'N':
        return false;
      case 'optional':
        if (isset($_REQUEST['operatorid']) || $numberOfOnline <= 1) {
          return false;
        }
        break;
      case 'mandatory':
        if (!empty($operatorid) || $numberOfOnline <= 1) {
          return false;
        }
        break;
    }
    displayChooseOperator($chooseoperator);
    return true;*/
    switch ($chooseoperator) {
        case null:
        case '':
        case 'N':
            return false;
            break;
        default:
            header_location_exit('/403.php');
            return true;
    }
}

function shouldChooseDepartment() {
    $choosedepartment = verify_param("choosedepartment", "/^\d{1}$/", "") == 1;
    if ($choosedepartment && empty($_REQUEST['departmentkey']) && count(MapperFactory::getMapper("Department")->enumDepartments(Resources::getCurrentLocale())) > 1) {
        displayChooseDepartment();
        return true;
    }
    return false;
}

function shouldEnterFirstMessage() {
    global $captcha;
    $chatimmediatly = verify_param("chatimmediately", "/^\d{1}$/", "") == 1;
    if ($chatimmediatly) {
        return false;
    }
    if (!isset($_REQUEST['submitted'])) {
        displayStartChat();
        return true;
    } else {
        $TML = new SmartyClass();
        setupStartChat($TML);

        $_SESSION['webim_uname'] = $visitor_name = getSecureText($_REQUEST['visitorname']);
        $_SESSION['webim_email'] = $email = getSecureText($_REQUEST['email']);
        $_SESSION['webim_phone'] = $phone= getSecureText($_REQUEST['phone']);
        $message  = getSecureText($_REQUEST['message']);
        $captcha_num = getSecureText($_REQUEST['captcha']);
        
        $has_errors = false;
        if(!$captcha->checkNumber($captcha_num)) {
            $TML->assign('errorcaptcha', true);
            $has_errors = true;
        } elseif (empty($visitor_name) && Visitor::getInstance()->canVisitorChangeName()) {
            $TML->assign('errorname', true);
            $has_errors = true;
        } elseif ( !is_valid_name($visitor_name) && Visitor::getInstance()->canVisitorChangeName() ) {
            $TML->assign('errornameformat', true);
            $has_errors = true;
        } elseif (empty($message)) {
            $TML->assign('errormessage', true);
            $has_errors = true;
        } else {
            if ( !is_valid_email($email) && !intval($_SESSION["uid"]) ) {
                $TML->assign('erroremailformat', true);
                $has_errors = true;
            }
        }
        $captcha->setNumber();
        if ($has_errors) {
            $TML->assign('visitorname', $visitor_name);
            $TML->assign('email', $email);
            $TML->assign('phone', $phone);
            $TML->assign('captcha_num', "");
            
            $TML->display('start-chat.tpl');
            return true;
        }


        return false;
    }

}

function setupStartChat($TML) {
    $TML->assign('to_url', getParametersToPassThru('client.php'));
    $TML->assign('theme', Browser::getCurrentTheme());
    $TML->assign('canChangeName', Visitor::getInstance()->canVisitorChangeName());
    $TML->assignCompanyInfoAndTheme();
}

function displayStartChat() {
    $canChangeName = Visitor::getInstance()->canVisitorChangeName();
    $TML = new SmartyClass();
    setupStartChat($TML);

    $TML->assign('email', Visitor::getInstance()->getEmail());
    $TML->assign('phone', Visitor::getInstance()->getPhone());


    $v = GetVisitorFromRequestAndSetCookie();
    $visitorName = $v['name'];

    if ($canChangeName) {
        $TML->assign('visitorname', $visitorName != Resources::Get("chat.default.visitorname") ? $visitorName : '');
    } else {
        $TML->assign('visitorname', $visitorName);
    }
    
    if ( get_uid(false) ) { 
    	$TML->assign('u_token_key', $_SESSION['rand']);
    }
    
    $TML->display('start-chat.tpl');
}

function displayChooseOperator($chooseoperator) {
    $departmentkey = verify_param("departmentkey", "/^\w+$/");
    $onlineOperators = Operator::getInstance()->getOnlineOperators(NULL, $departmentkey, Resources::getCurrentLocale());

    $TML = new SmartyClass();
//    $TML->assign('ismandatory', $isMandatory);
    if ($chooseoperator == 'optional') {
      $onlineOperators = array_merge(array(array('operatorid' => '', 'fullname' => Resources::Get('chooseoperator.any'))), $onlineOperators);
    } else {
      $onlineOperators = array_merge(array(array('operatorid' => 0, 'fullname' => Resources::Get('chooseoperator.select'))), $onlineOperators);
    }
    $TML->assign('mode', $chooseoperator);
    $TML->assign('onlineOperators', $onlineOperators);
    showChoose($TML, "operatorid", 'choose-operator.tpl');
}

function displayChooseDepartment() {
    $departments = MapperFactory::getMapper("Department")->enumDepartments(Resources::getCurrentLocale());
    $TML = new SmartyClass();
    $TML->assign('departments', $departments);

    showChoose($TML, "departmentkey", 'choose-department.tpl');
}

function getParametersToPassThru($url = null) {
    $params = array('theme', 'lang', 'opener', 'openertitle', 'locale',
    'choosedepartment', 'chooseoperator', 'operatorid', 'chatimmediately', 'departmentkey',
    'message', 'visitorname', 'email', 'phone');
    $str = '';

    foreach ($params as $param) {
        if (isset($_REQUEST[$param])) {
            if (!empty($str)) {
                $str .= "&";
            }
            $str .= $param."=".urlencode($_REQUEST[$param]);
        }
    }

    if (!empty($str)) {
        if (empty($url)) {
            $str = '&'.$str;
        } else {
            $str = $url.'?'.$str;
        }
    }


    if (isset($_REQUEST['openertitle'])) {

    }

    return $str;
}

function showChoose($TML, $param, $template) {
    $toUrl = getParametersToPassThru('client.php')."&".$param."=";

    $TML->assign('to_url', $toUrl);
    $TML->assign('theme', Browser::getCurrentTheme());
    $TML->assignCompanyInfoAndTheme();
    $TML->display($template);
}


function visitorAcceptedInvite($thread) {
    ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], 'visitor_accept_invite');
}

function isInvite($thread) {
    return $thread['state'] == STATE_INVITE;
}



function hasThreadIdParam() {
    return isset($_REQUEST['thread']);
}

function openChatPage($thread) {
    $TML = new SmartyClass();
    $TML->assign('theme', Browser::getCurrentTheme());

    $token = verify_param("token", "/^\d{1,8}$/");
    $level = verify_param("level", "/^(ajaxed|simple|old)$/");

    $page_settings = setupChatViewForVisitor($thread, $level);

    $TML->assign('canChangeName', Visitor::getInstance()->canVisitorChangeName());

    $TML->assign('page_settings', $page_settings);
    if (!empty($page_settings['rateList'])) {
        $TML->assign('rateList', $page_settings['rateList'], false);
    }

    $TML->assignCompanyInfoAndTheme();
    $pparam = verify_param("act", "/^(mailthread)$/", "default");
    if ($pparam == "mailthread") {
        $TML->assign('threadid', $thread['threadid']);
        $TML->assign('token', $thread['token']);
        $TML->assign('level', $_REQUEST['level']);
        $TML->assign('email', Visitor::getInstance()->getEmail($thread['threadid']));
        $TML->display('send-history.tpl');
    } else  {
        $visitSessionId = VisitSession::GetInstance()->updateCurrentOrCreateSession();
        
        $TML->assign('threadid', $thread['threadid']);
        $TML->assign('token', $thread['token']);
        $TML->assign('level', $_REQUEST['level']);
        $TML->assign('userAgent', $_SERVER['HTTP_USER_AGENT']);

        $v = GetVisitorFromRequestAndSetCookie();
        $TML->assign('name', $v['name']);
        $TML->assign('visitorid', $v['id']);

        $TML->assign('fl_name', ($_SESSION['uid'] ? iconv('CP1251','UTF-8',$_SESSION['name'].' '.$_SESSION['surname']) : ''));
        $TML->assign('fl_email', Visitor::getInstance()->getEmail($thread['threadid']));
        
        
        $TML->display('chat-window.tpl');
    } 
}

function sendLocation($thread) {
    $threadid = $thread['threadid'];
    $token = $thread['token'];
    $level = Browser::GetRemoteLevel($_SERVER['HTTP_USER_AGENT']);


    $departmentkey = verify_param("deparmentkey", "/^\w+$/");
    $departmentParam = !empty($departmentkey) ? "&deparmentkey=".$departmentkey : '';

    $url = WEBIM_ROOT . "/client.php?thread=$threadid&token=$token&level=$level".$departmentParam.getParametersToPassThru();



    header("Location: " . $url);
    exit();
}

function displayBanPage() {
    $TML = new SmartyClass();
    $TML->assignCompanyInfoAndTheme();
    $TML->display('ban-message.tpl');
}

function threadIsClosed($thread) {
    return $thread['state'] == STATE_CLOSED;
}


function showFeedbackPage() {
    $canChangeName = Visitor::getInstance()->canVisitorChangeName();
    $TML = new SmartyClass();
    $args=array();
    foreach ($_GET as $key=>$item) {
        if ($key != 'action' && $key != 'lastid') {
            $args[] = "$key=$item";
        }
    }
    $TML->assign('chaturi', "/webim/client.php?".join('&', $args));
    $TML->assign('MAX_FILES', 10);
    $TML->assign('u_token_key', $_SESSION['rand']);
    $page['message'] = "";

    $v = GetVisitorFromRequestAndSetCookie();
    $visitor_name = $v['name'];
    $TML->assignCompanyInfoAndTheme();
    if ($canChangeName) {
        $TML->assign('name', $visitor_name != Resources::Get("chat.default.visitorname") ? $visitor_name : '');
    } else {
        $TML->assign('name', $visitor_name);
    }
    if ( $_GET["hidebacklink"] == 1) {
        $TML->assign('hidebacklink', 1);
    }
    $TML->display('feedback.tpl');
    exit();
}

function showLeaveMessagePage() {
    $canChangeName = Visitor::getInstance()->canVisitorChangeName();
    $TML = new SmartyClass();
    $TML->assign('theme', Browser::getCurrentTheme());
    $TML->assign('email', Visitor::getInstance()->getEmail());
    $TML->assign('phone', Visitor::getInstance()->getPhone());
    $page['message'] = "";

    $v = GetVisitorFromRequestAndSetCookie();
    $visitor_name = $v['name'];

    $TML->assign('page_settings', $page);
    $TML->assign('opener', htmlspecialchars(Browser::getOpener(), ENT_QUOTES));
    $TML->assign('openertitle', isset($_REQUEST['openertitle']) ? htmlspecialchars($_REQUEST['openertitle'], ENT_QUOTES) : '');
    $TML->assign('canChangeName', $canChangeName);
    $TML->assignCompanyInfoAndTheme();
    if ($canChangeName) {
        $TML->assign('name', $visitor_name != Resources::Get("chat.default.visitorname") ? $visitor_name : '');
    } else {
        $TML->assign('name', $visitor_name);

    }
    $TML->display('leave-message.tpl');
    exit();
}

function showLeaveMessageSentPage() {
    $TML = new SmartyClass();
    $TML->assignCompanyInfoAndTheme();
    $TML->assign('theme', Browser::getCurrentTheme());
    $TML->display('leave-message-sent.tpl');
    exit();
}

function createNewThread() {
    $extAddr = Browser::GetExtAddr();
    $remoteHost = !empty($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : $extAddr;

    $visitSessionId = VisitSession::GetInstance()->updateCurrentOrCreateSession();

    $params = array();
    $params['visitsessionid'] = $visitSessionId;
    $params['lastpingvisitor'] = null ;

//    $canChangeName = Visitor::getInstance()->canVisitorChangeName();
//    if (!empty($_REQUEST['visitorname']) && $canChangeName) {
//      Visitor::getInstance()->setVisitorNameCookie($_REQUEST['visitorname']);
//      $params['visitorname'] = $_REQUEST['visitorname'];
//    }



    
    $operatorid = verify_param("operatorid", "/^(\d)$/");
    $departmentkey = verify_param("departmentkey", "/^\w+$/");
    $autoinviteid = verify_param("autoinviteid", "/^\d+$/");


    if (!empty($departmentkey)) {
        $department = MapperFactory::getMapper("Department")->getByDepartmentKey($departmentkey);
        $params['departmentid'] = $department['departmentid'];
    }
    //  else {
    //    $departments = MapperFactory::getMapper("Department")->enumDepartments(Resources::getCurrentLocale());
    //
    //    if (count($departments) == 1) {
    //      $params['departmentid'] = $departments[0]['departmentid'];
    //    }
    //  }
    //
    $startThreadState = null;

    if (!empty($operatorid) && ($operator = Operator::GetOperatorById($operatorid))) {
        $params['nextoperatorid'] = $operator['operatorid'];
        $startThreadState = STATE_LOADING_FOR_EXACT_OPERATOR;
    } else {
        $startThreadState = STATE_LOADING;
    }
    

    $thread = Thread::getInstance()->CreateThread(Resources::getCurrentLocale(), $startThreadState, $params);
    VisitSession::GetInstance()->UpdateVisitSession($visitSessionId, array('hasthread' => 1));

    set_has_threads(HAS_THREADS_FILE);
    
    // если пользователь не ввел email, то по умолчанию сообщаем email с основной базы
    if ( empty($_REQUEST['email']) && !empty($_SESSION['uid']) ) {
        $_REQUEST['email'] = $GLOBALS['DB']->val("SELECT email FROM users WHERE uid = ?", $_SESSION['uid']);
    }
    
    Thread::getInstance()->sendFirstMessageWithVisitorInfo($thread, $_REQUEST);

    if(!empty($autoinviteid)) {
        Thread::getInstance()->sendAutoIniviteTextToOperator($thread, $autoinviteid);
    }

    $visitor = GetVisitorFromRequestAndSetCookie();
    $opener = Thread::getInstance()->getOpenerWithTitle();
    VisitSession::GetInstance()->setVisitSessionCurrentPage($visitor['id'], $opener[0], $opener[1]);

    if (!empty($_REQUEST['message'])) {
        $v = GetVisitorFromRequestAndSetCookie();
        $hash = array();
        $hash['sendername'] = $v['name'];
        $hash['message'] = $_REQUEST['message'];
        ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], 'visitor_message', $hash);
    }

    if (!empty($email) || !empty($phone)) {
        Thread::getInstance()->PostMessage($thread['threadid']
            , KIND_FOR_AGENT
            , Resources::get('start.chat.info', array($email, $phone)));
    }


    //  MapperFactory::getMapper("Thread")->incrementVisitorMessageCount($threadid);
    return $thread;
}

function setupChatViewForVisitor($thread, $level) {
    $visitSession = VisitSession::GetInstance()->GetVisitSessionById($thread['visitsessionid']);

    $page['agent'] = false;
    $page['visitor'] = true;
    $page['canpost'] = true;
    $nameisset = Resources::Get("chat.default.visitorname") != $visitSession['visitorname'];
    $page['displ1'] = $nameisset ? "none" : "inline";
    $page['displ2'] = $nameisset ? "inline" : "none";
    $page['level'] = $level;
    $page['ct_chatThreadId'] = $thread['threadid'];
    $page['ct_token'] = $thread['token'];
    $page['ct_visitor_name'] = $visitSession['visitorname'];
    $page['canChangeName'] = Visitor::getInstance()->canVisitorChangeName();

    $page['ct_company_name'] = Settings::Get('company_name');
    $page['ct_company_chatLogoURL'] = Settings::Get('logo');
    $page['webimHost'] = Settings::Get('hosturl');
    $page['send_shortcut'] = "Enter";

    $params = "thread=".$thread['threadid']."&token=".$thread['token'];
    $page['selfLink'] = WEBIM_ROOT."/client.php?".$params."&level=".$level;
    $page['isOpera95'] = is_agent_opera95();

    $page['displayrate'] = !empty($thread['rate']) ? "none" : "inline";
    $page['rateList'] = explode("|", Resources::Get('chat.operator_rates'));
    if (!empty($res)) {
        foreach ($res as $k => $v) {
            $page[$k] = $v;
        }
    }

    $page['viewonly'] = "0";
    return $page;
}

function tryToGetExistingThread() {
    $threadid = verify_param("thread", "/^\d{1,8}$/", "");
    $thread = null;



    if (empty($threadid)) {
        $visitor = GetVisitorFromRequestAndSetCookie();

        $thread = MapperFactory::getMapper("Thread")->getActiveThreadForVisitor($visitor['id']);


    } else {
        $thread = Thread::getInstance()->GetThreadById($threadid);

        $token = verify_param("token", "/^\d{1,8}$/");
        if ($token != $thread['token'] || $thread['state'] == STATE_CLOSED) {
            $thread = null;
        }

    }

    if (!empty($thread) && (visitorHasAccess($thread) || empty($threadid))) {
        ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], 'ping_visitor');
        $thread = Thread::getInstance()->GetThreadById($thread['threadid']);

        if (empty($thread) || $thread['state'] == STATE_CLOSED) {
            $thread = null;
        }
    } else {
        $thread = null;
    }


    return $thread;
}

?>
