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
	require_once('../classes/class.settings.php');
	require_once('../classes/class.operator.php');
	require_once('../classes/class.browser.php');
	require_once('../classes/common.php');
	require_once('../classes/models/generic/class.mapperfactory.php');
	require_once('../classes/class.json.php');
	require_once('../classes/class.thread.php');
	
	
	 
    
	if(!session_id()) {
		session_start();
	}
	
	$theme = verify_param("theme", "/^\w+$/", "default");
	$isSecure = verify_param("issecure", "/^\d+$/", 0) == 1;
	$location = get_app_location(true, $isSecure);
	
	$lang = Resources::getCurrentLocale();
	
	$link = $location."/client.php?theme=$theme".(!empty($lang) ? "&lang=".$lang : "");
	$temp = get_popup_onclick($link, "webim_".getWindowNameSuffix(), "toolbar=0, scrollbars=0, location=0, menubar=0, width=528, height=456, resizable=1", true);
	// папка online в мэмкэш --------------------
	//$operators = Operator::getInstance()->getOnlineOperatorsFromFiles();
	$operators = Operator::getInstance()->getOnlineOperatorsFromMemBuff();
	$operators_count = count($operators);
	
	$no_operators = false;
	if($operators_count == 0) {
	  $no_operators = true;
	}
	
	if(!isset($_COOKIE[WEBIM_COOKIE_VISITOR_IN_CHAT])) {
	   $visitor = GetVisitorFromRequestAndSetCookie();
       $thread = MapperFactory::getMapper("Thread")->getActiveThreadForVisitor($visitor['id']);
       if(!empty($thread)) {
         $res = true;
       } else {
         $res = false;
       }
       $_COOKIE[WEBIM_COOKIE_VISITOR_IN_CHAT] = $res;
       setcookie(WEBIM_COOKIE_VISITOR_IN_CHAT, $res, time() + 30, '/');
	}
	
	$user_in_chat = false;
	if($_COOKIE[WEBIM_COOKIE_VISITOR_IN_CHAT]) {
	  $user_in_chat = true;
	}
	
	$invite_image = $location . "/themes/" . Browser::getCurrentTheme() . '/images/default-auto-invite-operator.gif';
	if($operators_count == 1) {
	  $operator = Operator::getInstance()->GetOperatorById(array_shift($operators));
	  if(!empty($operator) && !empty($operator['avatar'])) {
	    $invite_image = $operator['avatar'];
	  }
	}
	
	$json = new Json(SERVICES_JSON_LOOSE_TYPE);

	$statistics = getUsersStatsFromCookie();//isset($_COOKIE[WEBIM_COOKIE_AUTOINVITE_STATS]) ? $json->decode($_COOKIE[WEBIM_COOKIE_AUTOINVITE_STATS]) : null;
	$total_time_on_site = isset($_COOKIE[WEBIM_COOKIE_TOTAL_TIME_ON_SITE]) ? intval($_COOKIE[WEBIM_COOKIE_TOTAL_TIME_ON_SITE]) : 0;
	
	$visited_pages = array();
	
	if(isset($statistics['visited_pages']) && is_array($statistics['visited_pages'])) {
		foreach ($statistics['visited_pages'] as $p) {
			if(isset($p['url'], $p['time'], $p['referrer'])) {
				$p = array_map("htmlspecialchars", $p);
				$visited_pages[] = $p;
			}
		}
	}
	
	if(count($visited_pages) > 0 && $total_time_on_site > 0) {
		$_SESSION['user_stats'] = array();
		$_SESSION['user_stats']['visited_pages'] = $visited_pages;
		$_SESSION['user_stats']['total_time_on_site'] = htmlspecialchars($total_time_on_site);	
	}

	$title = verify_param("title", "/^.+$/");
	$tokens = explode(":", $title);
	$tokens = array_map("htmlspecialchars", $tokens);
	$referer_title = "";
	
	if(count($tokens) >= 2) {
	  if(!isset($_SESSION['titles']))	{
	    $_SESSION['titles'] = array();
	  }
	  
	  $referer = array_shift($tokens);
	  $referer_title = implode(":", $tokens);
	  $_SESSION['titles'][$referer] = $referer_title;

	  
	  $_SESSION['current_page'] = array($referer, $referer_title);
	} else {
      unset($_SESSION['current_page']);
	}
	
	$visitor = GetVisitorFromRequestAndSetCookie();
	
	if(WEBIM_ENCODING != 'UTF-8') {
	  $referer_title = smarticonv('utf-8', WEBIM_ENCODING, $referer_title);
	}

	VisitSession::GetInstance()->setVisitSessionCurrentPage($visitor['id'], $referer, $referer_title);
	
	if($user_in_chat) { 
	  if(empty($referer_title)) {
    	  $referer_title = Resources::Get("chat.visited_page.no_title");
  	  }
  	  
	  $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
	 
	  if (empty($visitor)) {
	    return;
	  }
		
	  Thread::getInstance()->SendVisitedPageForOpenThreads($visitor['id'], $referer, $referer_title);
	}
	
	header('Content-type: text/javascript; charset='.BROWSER_CHARSET);
?>

var rules = [
	<?php 
	  $rules = MapperFactory::getMapper("AutoInvite")->getAll();

	  foreach ($rules as $rule):
	  	if(WEBIM_ENCODING != 'UTF-8') {
	  		$rule['text'] = smarticonv('cp1251', 'utf-8', $rule['text']);
	  	}
	?>
		{
			id: <?php echo $rule['autoinviteid']; ?>,
			text: <?php echo $json->encode($rule['text'])?>,
			conditions: <?php echo $rule['conditions']?>
		},
	<?endforeach;?>
];
<?php require_once('./ainvite.js'); ?>