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
require_once ('class.settings.php');
require_once ('class.threadprocessor.php');
require_once ('class.browser.php');
require_once ('class.visitsession.php');
require_once ('class.operator.php');
require_once ('class.department.php');
require_once('models/generic/class.mapperfactory.php');
require_once ('class.visitedpage.php'); 


require_once 'class.geoiplookup.php';


class Thread  {
  protected $tableName = 'chatthread';
  protected $uniqueTableKey = 'threadid';
  
  private static $kindToString = array(
    KIND_USER => "visitor", 
    KIND_AGENT => "agent", 
    KIND_FOR_AGENT => "hidden", 
    KIND_INFO => "inf", 
    KIND_CONN => "conn", 
    KIND_EVENTS => "event", 
    KIND_RATE => "rate",
    KIND_NOANSWER => "rate",
  
  KIND_AVATAR => "avatar",
  
  );
  private static $instance = NULL;

  static function getInstance() { // TODO: mind the casing
    if(self::$instance == NULL) {
      self::$instance = new Thread();
    }
    return self::$instance;
  }

  private function __construct() {
    
  }

  private function __clone() {}

    function GetListThreads( $operatorid, $q = null, $showEmpty = true, $nLimit = 15, $nOffset = 0 ) {
        $departmentsExist = MapperFactory::getMapper("Department")->departmentsExist();
        return MapperFactory::getMapper("Thread")->getListThreads( $operatorid, $q, $showEmpty, $departmentsExist, $nLimit, $nOffset );
    }
    
    function GetListThreadsCount( $operatorid, $q = null, $showEmpty = true ) {
        $departmentsExist = MapperFactory::getMapper("Department")->departmentsExist();
        return MapperFactory::getMapper("Thread")->getListThreadsCount( $operatorid, $q, $showEmpty, $departmentsExist );
    }
    
    function GetListThreadsAdv( $operatorid, $q, $start, $end, $operator, $showEmpty = true, $departmentid, $locale, $rate, $offline, $nLimit = 15, $nOffset = 0 ) {
        $departmentsExist = MapperFactory::getMapper("Department")->departmentsExist();
        return MapperFactory::getMapper("Thread")->getListThreads( $operatorid, $q, $showEmpty, $departmentsExist, $nLimit, $nOffset, $start, $end, $operator, $departmentid, $locale, $rate, $offline );
    }
    
    function GetListThreadsAdvCount( $operatorid, $q, $start, $end, $operator, $showEmpty = true, $departmentid, $locale, $rate, $offline ) {
        $departmentsExist = MapperFactory::getMapper("Department")->departmentsExist();
        return MapperFactory::getMapper("Thread")->getListThreadsCount( $operatorid, $q, $showEmpty, $departmentsExist, $start, $end, $operator, $departmentid, $locale, $rate, $offline );
    }

  function CommitThread($threadid, $params) {
    $params['revision'] = MapperFactory::getMapper("Thread")->getNextRevision();
    $params['modified'] = null ;
    $params['threadid'] = $threadid;
    MapperFactory::getMapper("Thread")->save($params);
  }

  function PostMessage($threadid, $kind, $message, $sendername = null, $utime = null, $operatorId = null, $feedbackButon = null) {


    $hash = array(
      'threadid' => $threadid, 
      'kind' => $kind, 
      'message' => $message, 
      'sendername' => $sendername, 
      'created' => ! empty($utime) ? date('Y-m-d H:i:s', $utime) : null 
    );
    if ($feedbackButon) {
        $hash['message_additional_info'] = $feedbackButon;
    }
    if(isset($operatorId)) {
      $hash['operatorid'] = $operatorId;
    }
    $this->setThreadHasMessagesForOperator($threadid);
    $this->setThreadHasMessagesForVisitor($threadid);
    return MapperFactory::getMapper("Message")->save($hash);
  }

  function isHasMessagesForOperator($threadid) {
  	return is_has_threads(self::getOperatorHasMesagesFilename($threadid));	
  }
	
  function isHasMessagesForVisitor($threadid) {
  	return is_has_threads(self::getVisitorHasMesagesFilename($threadid));	
  }
  
  function setThreadHasMessagesForOperator($threadid) {
   	set_has_threads(self::getOperatorHasMesagesFilename($threadid));
  }
  
  function setThreadHasMessagesForVisitor($threadid) {
   	set_has_threads(self::getVisitorHasMesagesFilename($threadid));
  }
  
  function unsetThreadHasMessagesForOperator($threadid) {
   	unset_has_threads(self::getOperatorHasMesagesFilename($threadid));
  }
  
  function unsetThreadHasMessagesForVisitor($threadid) {
   	unset_has_threads(self::getVisitorHasMesagesFilename($threadid));
  }
  
  static function getOperatorHasMesagesFilename($threadid) {
  	$filename = $threadid.HAS_MESSAGES_OPERATOR_FILE_POSTFIX;
  	
  	return ONLINE_FILES_DIR . DIRECTORY_SEPARATOR . 
  		substr(md5($filename), 0, 1) . DIRECTORY_SEPARATOR . $filename;	
  }
  
  static function getVisitorHasMesagesFilename($threadid) {
  	$filename = $threadid.HAS_MESSAGES_VISITOR_FILE_POSTFIX;
  	
  	return ONLINE_FILES_DIR . DIRECTORY_SEPARATOR . 
  		substr(md5($filename), 0, 1) . DIRECTORY_SEPARATOR . $filename;	
  }
  
  function GetMessages($threadid, $meth, $isvisitor, &$lastid, $forceShowingRates = false) {
    
    if($forceShowingRates || ($isvisitor == false && Operator::getInstance()->isCurrentUserAdmin())) {
      $crm = MapperFactory::getMapper('Rate');
      $rates = $crm->getByThreadidWithOperator($threadid);
      $current_rate = array_shift($rates);
    } else {
      $current_rate = null;
    }
    
    $res = MapperFactory::getMapper("Message")->getListMessages($threadid, $lastid, $isvisitor);
    
    $messages = array();
    foreach($res as $msg) {
      $message = "";
      switch($meth) {
        case 'xml' :
          if($msg['kind'] == KIND_AVATAR) {
            $message = "<avatar>" . Browser::AddCdata($msg['message']) . "</avatar>";
          } else {
            $message = "<message>" . Browser::AddCdata($this->messageToHtml($msg)) . "</message>\n";
          }
        break;
        case 'text' :
          $message = $this->messageToText($msg);
        break;
        case 'html' :
          if($current_rate && $current_rate['date'] < $msg['created']) {
            $messages[] = $this->rateToHtml($current_rate);
            $current_rate = array_shift($rates);
          }
          
          $isvisitor = verify_param("visitor", "/^true$/", "false") == 'true';
          $cleanup_special_tags = ! $isvisitor;
          
          $message = $this->messageToHtml($msg, $cleanup_special_tags);
        break;
      }
      
      if(! empty($message)) {
        $messages[] = $message;
      }
      
      if($msg['messageid'] > $lastid) {
        $lastid = $msg['messageid'];
      }
    }
    
    return $messages;
  }

  
  function CreateThread($lang, $stateid = STATE_LOADING, $additional = array()) {
    $hash = array(
      'token' => MapperFactory::getMapper("Thread")->getNextToken(), 
      'revision' => MapperFactory::getMapper("Thread")->getNextRevision(), 
      'locale' => $lang, 'created' => null , 
      'modified' => null , 
      'state' => $stateid
    );
    


    
    $hash = array_merge($hash, $additional);

    
    //$id = $this->Insert($hash);
    $id = MapperFactory::getMapper("Thread")->save($hash);

    $thread = MapperFactory::getMapper("Thread")->getById($id);

    return $thread;
  }

 
  
  
  
  public function buildPendingThreadsXml($since, $operator) {
    $xml = array();
    $params = array(
      'revision' => $since, 'time' => getCurrentTime() . '000', 'product' => strtolower(Settings::GetProduct()), 'version' => WEBIM_VERSION
    );
    

    $prio = array_fill(0, 10, array());
    $queue = array_fill(0, 10, array());
    $info = array_fill(0, 10, array());
    $closed = array_fill(0, 10, array());

    $res = array();

    $departmentsExist = MapperFactory::getMapper("Department")->departmentsExist();
    
    $operator_data = MapperFactory::getMapper("OperatorLastAccess")->getById($operator['operatorid']);
    $locales = empty($operator_data['locales']) ? null : split(',', $operator_data['locales']);
   



	$threads = array();
    if(is_has_threads(HAS_THREADS_FILE)) {
    	$threads = MapperFactory::getMapper("Thread")->getPendingThreads($since, $includeClosed = $since != 0, $operator['operatorid'], $departmentsExist, $locales);


	    foreach($threads as $thread) {
	      $state = $thread['state'];
	      $isForMe = $thread['nextoperatorid'] == $operator['operatorid'] || empty($thread['nextoperatorid']);
	      $isMine = $thread['operatorid'] == $operator['operatorid'];
      
	      switch ($state) {
	        case STATE_CHATTING_OPERATOR_BROWSER_CLOSED_REFRESHED: 
	          if ($isForMe || $isMine) {
	             $prio[0][] = $thread;
	          } else {
	            $info[1][] = $thread;
	          }
	          break;
	        case STATE_REDIRECTED:
	          if ($isForMe) {
	            $prio[1][] = $thread;
	          } else {
	            $info[2][] = $thread;
	          }
	          break;
	        case STATE_QUEUE_EXACT_OPERATOR:
	        case STATE_LOADING_FOR_EXACT_OPERATOR:
	          if ($isForMe) {
	            $prio[3][] = $thread;
	          } else {
	            $info[1][] = $thread;
	          }
	          break;
	        case STATE_LOADING:
	        case STATE_QUEUE:
	          $queue[0][] = $thread;
	          break;
	        case STATE_CHATTING:
	          $info[0][] = $thread;
	          break;
	        case STATE_CLOSED:
	          $closed[0][] = $thread;
	          break;
        
	        default: 
	          $info[2][] = $thread;
	          break;
	      }

	      if($thread['revision'] > $params['revision']) {
	        $params['revision'] = $thread['revision'];
	      }
	  
		  if(MapperFactory::getMapper("Thread")->countActiveThreads() < 1 && count($threads) < 1) {
		   	unset_has_threads(HAS_THREADS_FILE);
		  }
		}
	}

    $res = array();
    self::appendThreads($res, $prio, $operator, 'prio');
    self::appendThreads($res, $queue, $operator, 'wait');
    self::appendThreads($res, $info, $operator, 'chat');
    self::appendThreads($res, $closed, $operator, 'closed');
    
    $xml[] = '<threads ' . self::xmlParamsToProperties($params) . '>';


    $xml[] = join("\n", $res);
    $xml[] = '</threads>';
    
    return join("\n", $xml);
  }
  
  private static function appendThreads(&$res, $arr, $operator, $segment) {
    $sorted = array_keys($arr);
    sort($sorted);
    foreach ($sorted as $key) {
      foreach ($arr[$key] as $thread) {
        $toAdd = self::buildThreadXml($thread, $operator, $segment);
        if ($toAdd !== null) {
          $res[] = $toAdd;
        }
      }
    }
  }
  
  private function buildThreadXml($thread, $operator, $segment) {
    $xml = array(); 
    
    $visitSession = MapperFactory::getMapper("VisitSession")->getById($thread['visitsessionid']);
//    $stateid = self::$threadStateStrings[$thread['state']];
    $state = Resources::GetStateName($thread['state']);
    
    $msg = "";
    if(!empty($thread["shownmessageid"])) {
    	$msg = MapperFactory::getMapper("Message")->getById($thread["shownmessageid"]);
    } 
    
    if (empty($msg)) {
      $message = '';
    } else {
      $message = preg_replace("/[\r\n\t]+/", " ", $msg["message"]);
      $message = removeSpecialSymbols($message);
    }
    
    $props = array(
      'id' => $thread['threadid'], 'stateid' => $segment
    );
    
    if($thread['state'] == STATE_CLOSED) {
      return '<thread ' . self::xmlParamsToProperties($props) . ' />';
    }
    
    $props['state'] = $state;
    $props['typing'] = $thread['visitortyping'];
    $props['canopen'] = 'true';
    
    if(isset($thread['operatorid']) && $thread['operatorid'] != $operator['operatorid'] && $thread['nextoperatorid'] != $operator['operatorid']) {
      $props['canview'] = 'true';
    }
    
    $xml[] = '<thread ' . self::xmlParamsToProperties($props) . '>';
    
    
    $geodata = GeoIPLookup::getGeoDataByIP($visitSession['ip']);
    //for testing purpose
    //$geodata = GeoIPLookup::getGeoDataByIP('89.113.218.99');
    if($geodata == NULL) {
      $geodata = array(
        'city' => null, 'country' => null, 'lat' => null, 'lng' => null
      );
    }
        
    
    $visitorName = empty($visitSession['visitorname']) ? Resources::Get("chat.default.visitorname") : 
                     removeSpecialSymbols($visitSession['visitorname']);
    $departmentname = "";
   
    if($thread['departmentid']) {
    	$department = MapperFactory::getMapper("DepartmentLocale")->getDepartmentLocale($thread['departmentid'], WEBIM_CURRENT_LOCALE);
    	if(!empty($department)) {
    		$departmentname = $department['departmentname'];
    	}
    }
    
    if(empty($departmentname))
    	$departmentname = Resources::Get('pending.table.no_department');
    
    $nodes = array(
      'name' => $visitorName, 'host' => $visitSession['remotehost'], 'addr' => $visitSession['ip'], 'agent' => self::getOperatorFullNameToShow($thread), 'time' => $thread['created'] . '000', 'other' => get_user_agent($visitSession['useragent']), 'message' => $message,
      
      'city' => $geodata['city'], 'country' => $geodata['country'], 'lat' => $geodata['lat'], 'lng' => $geodata['lng'],
      
      'locale' => $thread['locale'], 'department' => $departmentname
    );
    $current_page = VisitSession::GetInstance()->getVisitSessionCurrentPage($visitSession['visitorid']);

    if(!empty($current_page)) {
      $nodes['current_page_url']= $current_page[0];
      $nodes['current_page_title']= $current_page[1];  
      if(empty($nodes['current_page_title'])) {
        $nodes['current_page_title'] = !empty($nodes['current_page_url']) ? $nodes['current_page_url'] : Resources::Get('chat.visited_page.no_title');
      }
    }
    
    if(! empty($ban)) {
      $nodes['reason'] = $ban['comment'];
    }
    
    $xml[] = self::xmlParamsToNodes($nodes);
    $xml[] = "</thread>";
    
    return join("\n", $xml);
  }

  

  private static function xmlParamsToProperties($params) {
    $res = array();
    foreach ($params as $key => $value) {
      $res[] = $key . '="' . $value .'"';
    }
    return join(' ', $res);
  }
  
  private static function xmlParamsToNodes($params) {
    $res = array();
    foreach ($params as $key => $value) {
        $res[] = '<' . $key . '>' . htmlspecialchars(htmlspecialchars($value)) . '</' . $key . '>';
    }
    return join("\n", $res);
  }



  
  function BuildVisitorsXml() {
    $xml = array();
    
    // папка online в мэмкэш --------------------
    //VisitedPage::GetInstance()->retrieveVisitors();
    VisitedPage::GetInstance()->retrieveVisitorsFromMemBuff();
    
    $xml[] = "\n<visitors time=\"" . getCurrentTime() . "000\">";
    
    $alive_visitors = VisitedPage::GetInstance()->getAliveVisitors();
    foreach($alive_visitors as $visitor) {
      if (!isset($visitor)) {
        continue;
      }
      $visitor['alive'] = true;
      $xml[] = $this->BuildVisitorXml($visitor);
    }
    
    $dead_visitors = VisitedPage::GetInstance()->getDeadVisitors();
    foreach($dead_visitors as $visitor) {
      if (!isset($visitor)) {
        continue;
      }
      $visitor['alive'] = false;
      $xml[] = $this->BuildVisitorXml($visitor);
    }
    $xml[] = '</visitors>';
    
    return join("\n", $xml);
  }

  function BuildVisitorXml($visitor) {
    $xml = array();
    $session = VisitSession::GetInstance()->GetVisitSessionById($visitor['visitsessionid']);

    
    $visitedPages = VisitedPage::GetInstance()->enumVisitedPagesByVisitSessionId($visitor['visitsessionid']);
	$landingPage = end($visitedPages);
    
    $props = array(
      'visitedpageid' => $visitor['visitedpageid'], 'alive' => $visitor['alive']
    );
    
    
    $geodata = GeoIPLookup::getGeoDataByIP($session['ip']);
    //for testing purpose
    //$geodata = GeoIPLookup::getGeoDataByIP('89.113.218.99');
    if($geodata == NULL) {
      $geodata = array(
        'city' => null, 'country' => null, 'lat' => null, 'lng' => null
      );
    }
    
    $visitor['visitorname']  = $session['visitorname'];
    $visitor['visitorid']  = $session['visitorid'];
    
    $nodes = array(
      'uri'                => isset($visitor['uri']) ? $visitor['uri'] : '', 
      'title'              => empty($visitor['title']) ? (isset($visitor['uri']) ? $visitor['uri'] : Resources::Get('visitors_list.no_title')) : $visitor['title'],  
      'name'               => removeSpecialSymbols($session['visitorname']), 
      'host'               => $session['remotehost'], 
      'addr'               => $session['ip'], 
      'stime'              => (isset($visitor['opened']) ? $visitor['opened'] : '') . '000',
       
          
      'visitinfoid'        => $visitor['visitedpageid'],   
      'city'               => $geodata['city'], 
      'country'            => $geodata['country'], 
      'lat'                => $geodata['lat'], 
      'lng'                => $geodata['lng'],
      
      'browser'            => get_user_agent($session['useragent']), 
      'landingpage'        => $landingPage['uri'], 
      'landingpagetitle'   => empty($landingPage['title']) ? (isset($visitor['uri']) ? $visitor['uri'] : Resources::Get('visitors_list.no_title')) : $landingPage['title'], 
      'landingpageref'     => isset($visitor['referrer']) ? $visitor['referrer'] : '', 
      'landingpagereflink' => parseReferrer(isset($visitor['referrer']) ? $visitor['referrer'] : '', 'visitors_list.referrer'), 
      'other'              => '',
      'locale'             => MapperFactory::getMapper("Thread")->getLocaleByVisitSessionId($session['visitsessionid'])
    );
    $xml[] = '<visitor ' . self::xmlParamsToProperties($props) . '>';
    $xml[] = self::xmlParamsToNodes($nodes);
    $xml[] = '</visitor>';
    
    return join("\n", $xml);
  }

  private static function getOperatorFullNameToShow($thread) {
    if(empty($thread['nextoperatorid'])) {
      return $thread['operatorfullname'];
    } else {
      $op = Operator::getInstance()->getOperatorById($thread['nextoperatorid']);
      return $op['fullname'];
    }
  }

  
  
  function RenameVisitor($thread, $newname) {
    $visitSession = MapperFactory::getMapper("VisitSession")->getById($thread['visitsessionid']);        
    
    MapperFactory::getMapper("VisitSession")->save(array(
      'visitsessionid' => $thread['visitsessionid'],
      'visitorname' => $newname
    ));
    
    if($visitSession['visitorname'] != $newname) {
      $message = Resources::Get("chat.status.visitor.changedname", array(
        $visitSession['visitorname'], $newname
      ), $thread['locale']);
      
      $this->PostMessage($thread['threadid'], KIND_EVENTS, $message);
    }
  }

  function GetThreadById($threadid) {
    return MapperFactory::getMapper("Thread")->getById($threadid);
  }


  function SendVisitedPageForOpenThreads($visitorid, $page, $title) {


    
    if(empty($page)) {
      return;
    }
    
    $threads = MapperFactory::getMapper("Thread")->getOpenThreadsForVisitor($visitorid);

    
    foreach($threads as $thread) {
      $message = Resources::Get("chat.client.visited.page", array(
        $title, $page
      ), $thread['locale']);

      $this->PostMessage($thread['threadid'], KIND_FOR_AGENT, $message);
      ThreadProcessor::getInstance()->ProcessThread($thread['threadid'], 'idle');
    }
  }

  function PrintThreadMessages($thread, $token, $lastid, $isvisitor, $format, $viewonly = false) {
    $threadid = $thread['threadid'];
    $istyping = abs($thread['current'] - $thread[$isvisitor ? "lpoperator" : "lpvisitor"]) < WEBIM_CONNECTION_TIMEOUT && $thread[$isvisitor ? "agenttyping" : "visitortyping"] == "1" ? "1" : "0";
    $israted = empty($thread['ratedoperatorid']) ? 'false' : 'true';
    
    $visitSession = $isvisitor ? GetVisitorFromRequestAndSetCookie() : MapperFactory::getMapper("VisitSession")->getById($thread['visitsessionid']);
    $visitorname  = $isvisitor ? removeSpecialSymbols($visitSession['name']) : removeSpecialSymbols(htmlspecialchars($visitSession['visitorname']));
    
    $rate = $thread['rate'];
    
    if($format == "xml") { 
      $visitorname = "visitorname=\"$visitorname\"";
      $operatorfullname = "operatorfullname=\"" . $thread['operatorfullname'] . "\"";      
      $fl_login = "fl_login=\"" . ( $isvisitor || empty($visitSession['fl_login']) ? '' : $visitSession['fl_login'] ) . "\"";
      $threadstate = "state=\"" . $thread['state'] . "\"";
    
      $isHasMessages = true;
      if ($lastid != 0 && !$viewonly) {
        $isHasMessages = $isvisitor ? $this->isHasMessagesForVisitor($thread['threadid']) : $this->isHasMessagesForOperator($thread['threadid']);
      }
      
      $haveMessegesToAlert = "needtoalert=\"" . ( $isHasMessages ? "true" : "false") . "\"";
      $output = array();
      if($isHasMessages) {
      	$output = $this->GetMessages($threadid, "xml", $isvisitor, $lastid);
		if($thread['state'] !== STATE_CLOSED) {
	      	if($isvisitor) {
				$this->unsetThreadHasMessagesForVisitor($thread['threadid']);
			} else if (!$viewonly) {
				$this->unsetThreadHasMessagesForOperator($thread['threadid']);
			}
		}
      }
      
      
      Browser::SendXmlHeaders();
      print("<thread lastid=\"$lastid\" typing=\"" . $istyping."\" viewonly=\"" . $viewonly."\" $visitorname $operatorfullname $fl_login $haveMessegesToAlert $threadstate israted=\"$israted\" rate=\"$rate\">");
      
      foreach($output as $msg) {
        if (!$isvisitor && strpos($msg, "webimFeedbackBtn") !== false) {
            continue;
        }
        print $msg;
      }
      print("</thread>");
    } elseif($format == "html") {
      $output = $this->GetMessages($threadid, "html", $isvisitor, $lastid);
      
      Browser::SendHtmlHeaders();
      $url = WEBIM_ROOT . "/thread.php?act=refresh&thread=" . $threadid . "&token=" . $token . "&html=on&visitor=" . ($isvisitor ? "true" : "false");
      
      print("<html><head>\n" . "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"" . WEBIM_ROOT . "/css/admin_chat.css?".WEBIM_VERSION."\" />\n" . "<meta http-equiv=\"Refresh\" content=\"7; URL=$url&sn=11\">\n" . "<meta http-equiv=\"Pragma\" content=\"no-cache\">\n" . "</head>" . "<body bgcolor='#FFFFFF' text='#000000' link='#C28400' vlink='#C28400' alink='#C28400' marginwidth='0' marginheight='0' leftmargin='0' rightmargin='0' topmargin='0' bottommargin='0' onload=\"if (location.hash != '#aend') {location.hash='#aend';}\">" . "<table width='100%' cellspacing='0' cellpadding='0' border='0'><tr><td valign='top' class='message'>");
      
      foreach($output as $msg) {
        if (!$isvisitor && strpos($msg, "webimFeedbackBtn") !== false) {
            continue;
        }
        print $msg;
      }
      
      print("</td></tr></table><a name='aend'>" . "</body></html>");
    }
  }

  function GetReportByAgent($start, $end, $departmentid = null, $locale = null) {
    
     return MapperFactory::getMapper("Operator")->getAdvancedReport($start, $end, $departmentid, $locale);
    
    
     
  }

  
  function removeRate($rateid) {
    MapperFactory::getMapper("Rate")->removeRate($rateid);
  }
  
  function RateOperator($thread, $rate) {
    
    $crm = MapperFactory::getMapper("Rate");
    $crate = array(
      	'threadid' => $thread['threadid'], 
      	'operatorid' => $thread['operatorid'], 
      	'rate' => $rate, 
		'date' => null 
    );
    $crm->save($crate);
    
    // Send an email in case of negative rate
    if($rate < 0) { // TODO i would send all the negative feedback was == -2
      $history = "";
      $lastid = - 1;
      $output = $this->GetMessages($thread['threadid'], "text", true, $lastid);
      foreach($output as $msg) {
        $history .= $msg;
      }
      
      $visitSession = VisitSession::GetInstance()->GetVisitSessionById($thread['visitsessionid']);
      
      $subject = Resources::Get("mail.visitor.negative.rate.history.subject");
      $body = Resources::Get("mail.visitor.negative.rate.history.body", array(
        $visitSession['visitorname'], $history
      ));
      
      webim_mail(Settings::Get('superviser_email'), Settings::Get('from_email'), $subject, $body);
    }
  }

  
  
  
  
  function GetThreadsByVisitSessionId($visitsessionid) {
    return MapperFactory::getMapper("Thread")->getByVisitSessionId($visitsessionid);
  }
  
  
  public function CountNonEmptyThreads($visitorid) {
    return MapperFactory::getMapper("Thread")->getNonEmptyThreadsCountByVisitorId($visitorid);
  }

  
  private function rateToHtml($rate) {
    $message = "<span>" . date("H:i:s", $rate['date']) . "</span> ";
    $message .=  "<span class='m".self::$kindToString[KIND_RATE]."'>" . 
      Resources::Get(
      	'chat.window.admin.history.user_rate', 
        array(
          $rate['operator'],
          Resources::Get('rate.'.$rate['rate'])
        )
    );
    
    $message .= " <a onclick=\"return confirm('".Resources::Get('chat.window.admin.history.remove_rate.confirm')."');\" 
    	href=\"".WEBIM_ROOT."/operator/threadprocessor.php?act=removerate&threadid={$rate['threadid']}&rateid={$rate['rateid']}\">" .
      Resources::Get('chat.window.admin.history.remove_rate') .
    "</a>";
      
    $message .= "</span><br />";
    return $message;
  }
  
  private function messageToHtml($msg, $cleanup_special_tags = false) {
    if($msg['kind'] == KIND_AVATAR)
      return "";
    
    if ($msg['kind'] == KIND_FOR_AGENT) {
        $message = "<span>" . date("Y-m-d H:i:s", $msg['created']) . "</span> ";
    } else {
        $message = "<span>" . date("H:i:s", $msg['created']) . "</span> ";
    }
    $kind = self::$kindToString[$msg['kind']];
    



    

    if($msg['sendername']) {
      $message .= "<span class='n$kind'>" . htmlspecialchars(removeSpecialSymbols($msg['sendername'])) . "</span>: ";
    }
    
    if ($msg['kind'] == KIND_NOANSWER) {
        $messageHTML = self::processSpecialTags($msg['message'], "text", false);
    } else {
        $messageHTML = $this->PrepareHtmlMessage($msg['message'], $cleanup_special_tags);
    }
    $message .= "<span class='m$kind'>" . $messageHTML . "</span><br/>";
    if ($msg['message_additional_info'] > 0 && $msg['message_additional_info'] < 3) {
        $iestyle = '';
        $ua = strtolower($_SERVER["HTTP_USER_AGENT"]);
        if ( strpos( $ua, "msie") !== false ) {
            $iestyle = ' style="width:125px"';
            if ( strpos( $ua, "msie 10.") !== false ) {
                $iestyle = ' style="width:120px"';
            }
        }
        $noBackToChatLink = '';
        if ($msg['message_additional_info'] == 2) {
            $noBackToChatLink = ', 1';
        }
    	$message .= '<a class="b-button b-button_rectangle_color_transparent b-button_margleft_45 webimFeedbackBtn" href="#" onclick="return showFeedback(event'.$noBackToChatLink.');" '.$iestyle.'>
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">Задать вопрос</span>
                            </span>
                        </span>
                    </a><br/>
        ';
    }
    return $message;
  }
  
  private function messageToText($msg) {
    if($msg['kind'] == KIND_AVATAR)
      return "";
    $messageText = self::processSpecialTags($msg['message'], "text", true );
    $message_time = date("H:i:s ", $msg['created']);
    if($msg['kind'] == KIND_USER || $msg['kind'] == KIND_AGENT) {
      if($msg['sendername'])
        return $message_time . $msg['sendername'] . ": " . $messageText . "\n";
      else
        return $message_time . $messageText . "\n";
    } elseif($msg['kind'] == KIND_INFO) {
      return $message_time . $messageText . "\n";
    } else {
      return $message_time . "[" . $messageText . "]\n";
    }
  }

  private static function processSpecialTags($message, $format, $hideSpecialTags) {
    $protocol = '(http|ftp|https):\/\/';
    $domain = '[\w]+(.[\w]+)';
    $subdir = '([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&\/~\+#])?';
    //$pattern = $protocol . $domain . $subdir;
    $pattern = '((http|ftp|https):\/\/|www\.)(([\da-z-_а-яёА-ЯЁ]+\.)*([\da-z-_]+|рф|РФ)(:\d+)?([\/?#][^"\s<]*)*)';

    switch ($format) {
      case "text":
        if(preg_match("/\!($pattern)/i", $message)) {
          $result = preg_replace("/\!($pattern)/i", Resources::Get('push.page.invitation')." $1", $message);
        } else {
          if(preg_match('/\?\@/', $message)) {
            $result = preg_replace('/\?\@/', Resources::Get('visitor.contacts.message'), $message);
          } else {
            $result = $message;
          }
        }
        break;
      case "html":
        if(preg_match("/\!($pattern)/i", $message)) {
          $link = Resources::Get('push.page.invitation') . 
            ' <a href=' . ($hideSpecialTags ? '"$1"' : '"!$1"') . ' target="_blank">$1</a>';
          $result = preg_replace("/\!($pattern)/i", $link, $message);
        } else {
          if(preg_match('/\?\@/', $message)) {
            $contactRequest = $hideSpecialTags ? Resources::Get('visitor.contacts.message') : 
              '?@' . Resources::Get('visitor.contacts.message');
            $result = preg_replace('/\?\@/', $contactRequest, $message);
          } else {
            $result = preg_replace("/$pattern/i", '<a href="$0" target="_blank">$0</a>', $message);
          }
        }
        break;
    }
   
    return $result;
  }

  private function PrepareHtmlMessage($text, $cleanup_special_tags = false) {    
    $message = removeSpecialSymbols(htmlspecialchars($text));    
    return str_replace("\n", "<br/>", self::processSpecialTags($message, "html", $cleanup_special_tags));
  }

  public function removeHistory($threadid) {
    MapperFactory::getMapper("Message")->removeHistory($threadid);
    MapperFactory::getMapper("Thread")->removeHistory($threadid);
  }
  
  public function hasThreadAccess($operatorid,  $threadid) {
    if (empty($operatorid)) {
      return false;
    }
    
    if (!MapperFactory::getMapper("Department")->departmentsExist()) {
      return true;
    }
    
    $thread = $this->GetThreadById($threadid);
    
    if (empty($thread)) {
      return false;
    }
    
    if ($thread['departmentid'] === null) {
      return true;
    }
    
    return MapperFactory::getMapper("OperatorDepartment")->isOperatorInDepartment($operatorid, $thread['departmentid']);
  }

  public function getOpenerWithTitle() {
    $opener = Browser::getOpener();

    $openerText = '';
    if (!empty($_REQUEST['openertitle'])) { // TODO remove from here
      $openerTitle = $_REQUEST['openertitle'];
      if (strtoupper(WEBIM_ENCODING) != 'UTF-8') {
        $openerTitle = smarticonv('UTF-8', WEBIM_ENCODING, $openerTitle);
      }
      return array($opener, $openerTitle);
    } 
    
    return array($opener);
  }
  
  public function formatOpenerWithTitle() {
    $opener = $this->getOpenerWithTitle();
    $openerText = implode(":", array_reverse($opener));
    

    return $openerText;
  }

  public function sendFirstMessageWithVisitorInfo($thread, $params = array()) {
    $visitSession = VisitSession::GetInstance()->GetVisitSessionById($thread['visitsessionid']);

    $firstPage = null;
    $visted_pages = "";
  	if(!session_id()) {
   		session_start();
   	}
    	
   	if(isset($_SESSION['user_stats'])) {
   	  $stats = $_SESSION['user_stats'];
   	}
   	else{
   	  $stats = getUsersStatsFromCookie();
   	}

   	if(isset($stats['visited_pages']) && is_array($stats['visited_pages'])) {
    	if(count($stats['visited_pages']) > 0) {
    		$firstPage = $stats['visited_pages'][0];
    	}
    	
    	$visted_pages = "\n".Resources::Get("chat.visited_pages");
    	foreach ($stats['visited_pages'] as $vp) {
    	   
    		$title = isset($_SESSION['titles'], $_SESSION['titles'][$vp['url']]) ? $_SESSION['titles'][$vp['url']] : "";
    	    
    		if(WEBIM_ENCODING != 'UTF-8') {
	  			$title = smarticonv('utf-8', WEBIM_ENCODING, $title);
	  		}

	  		if(empty($title)) {
    		  $title = Resources::Get("chat.visited_page.no_title");
	  		}
    		
    		$visted_pages .= "\n".Resources::Get("chat.visited_page", array($title, HTTP_PREFIX.$_SERVER['HTTP_HOST'].$vp['url'], $vp['time']));
    	}
    } 
    	 


    
    $openerText = self::formatOpenerWithTitle();
    $message = Resources::Get(empty($openerText) ? 'chat.came.from.unknown' : 'chat.came.from', $openerText);

    
    $simple = "\n%PARAM%: %VALUE%";
    $link = "\n%PARAM%: %VALUE% %URL%";  
    
    if (!empty($thread['departmentid'])) {
      $departmentid = $thread['departmentid'];
      $dep = MapperFactory::getMapper("DepartmentLocale")->getDepartmentLocale($departmentid, Resources::getCurrentLocale());

      $message .= str_replace(array('%PARAM%', '%VALUE%'), 
        array(
          Resources::Get('pending.table.head.department'), 
            $dep['departmentname']), $simple);
    }
	
    if ($firstPage !== null)  {
      if (!empty($firstPage['referrer'])) {
      	$message .= str_replace(
    		array('%PARAM%', '%VALUE%'), 
    		array(
    			Resources::Get('chat.window.referrer'), 
    			parseReferrer($firstPage['referrer'], 'chat.window.referrer')
    		), 
    		$simple
    	);
      }
      if (!empty($firstPage['url'])) {
        $message .= str_replace(array('%PARAM%', '%VALUE%'), array(Resources::Get('chat.window.landingpage'), $firstPage['url']), $simple);
      	
      }
    }
    
    $visitor_geodata = GeoIPLookup::getGeoDataByIP($visitSession['ip']);
    if (!empty($visitor_geodata)) {
      $message .= str_replace(array('%PARAM%', '%URL%', '%VALUE%'), 
        array(
          Resources::Get('chat.window.geolocation'), 
            "http://maps.google.com/maps?q=".$visitor_geodata['lat'].",".$visitor_geodata['lng'], 
            $visitor_geodata['city'] . ' ' . $visitor_geodata['country']), $link);
    }

    $message .= str_replace(array('%PARAM%','%VALUE%'), 
      array(
        Resources::Get('chat.window.browser'), 
        get_user_agent($visitSession['useragent'])), $simple);
          
    $message .= str_replace(array('%PARAM%', '%VALUE%'), 
      array(
        Resources::Get('chat.window.ip'), 
        WEBIM_WHOIS_LINK.urlencode($visitSession['ip'])), $simple);

    if (!empty($visitSession['remotehost'])) {
      $message .= str_replace(array('%PARAM%', '%VALUE%'), 
        array(
          Resources::Get('chat.window.remotehost'), 
          $visitSession['remotehost']), $simple);
    }

    if (!empty($params['email'])) {
      $message .= str_replace(array('%PARAM%', '%VALUE%'),
        array(
          Resources::Get('chat.window.email'),
          $params['email']), $simple);
    }
    
	$message .= str_replace(
        array('%PARAM%', '%VALUE%'), 
        array( Resources::Get('chat.window.fl_login'), $visitSession['fl_login']), 
        $simple 
    );


    $chats = Thread::getInstance()->CountNonEmptyThreads($visitSession['visitorid']);
    if ($chats > 0) {
      $message .= str_replace(array('%PARAM%', '%URL%', '%VALUE%'), 
        array(Resources::Get('chat.window.chats'), 
  
            HTTP_PREFIX.$_SERVER['HTTP_HOST'].WEBIM_ROOT.'/operator/history.php?q='.$visitSession['visitorid'], 
  
   
            $chats), $link);
    }
	
    $message .= $visted_pages;

    Thread::getInstance()->PostMessage($thread['threadid']
    , KIND_FOR_AGENT
    , $message);
  }
  
  public function sendAutoIniviteTextToOperator($thread, $autoinviteid) {
    $ainvite = MapperFactory::getMapper("AutoInvite")->getById($autoinviteid);

    if(empty($ainvite)) {
      return;
    }
        
    $message = Resources::Get("chat.window.auto_invite_text", array($ainvite['text']));
    Thread::getInstance()->PostMessage($thread['threadid'], KIND_FOR_AGENT, $message);
    
  }
}

?>
