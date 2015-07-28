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

require_once('common.php');
require_once('class.thread.php');

class ThreadProcessor  {

  private static $instance = NULL;

  static function getInstance() {
    if (self::$instance == NULL) {
      self::$instance = new ThreadProcessor();
    }
    return self::$instance;
  }

  private function __construct() {
    
  }

  private function __clone() {
  }

  function ProcessThread($threadid, $action, $params = array()) {










    $res = null;
    $newState = null;
    $forExactOperatorMode = false;

    $thread = Thread::getInstance()->GetThreadById($threadid);












    // without changing state
    switch($action) {
      case 'visitor_message':
        $res = Thread::getInstance()->PostMessage($threadid, KIND_USER, $params['message'], $params['sendername']);
        MapperFactory::getMapper("Thread")->incrementVisitorMessageCount($threadid);
      case 'visitor_ping':
        $this->ping($thread, $params, 'lastpingvisitor', 'visitortyping');
        $thread = Thread::getInstance()->GetThreadById($threadid);
	break;
      case 'operator_message':
        $res = Thread::getInstance()->PostMessage($threadid, KIND_AGENT, $params['message'], $params['sendername'], null, $params['operatorid']);
      case 'operator_ping':
      case 'operator_force_join':
        $this->ping($thread, $params, 'lastpingagent', 'agenttyping');
        $thread = Thread::getInstance()->GetThreadById($threadid);
        break;
    }


    switch ($thread["state"]) {
      case STATE_INVITE:
        switch($action) {
          case 'visitor_ping':
          case 'visitor_message':
            Thread::getInstance()->PostMessage($threadid, KIND_FOR_AGENT, Resources::Get('client.joined.thread', array(), $thread['locale']));
            $newState = STATE_CHATTING;
            break;
          case "operator_close":
            $this->sendOperatorLeft($thread);
            $newState = STATE_CLOSED;
            break;
          
          case 'operator_ping':
            $session = VisitSession::getInstance()->GetVisitSessionById($thread['visitsessionid']);
            if (isset($session) && isset($session['updated']) && (getCurrentTime() - $session['updated']) > VISITED_PAGE_TIMEOUT) {
              Thread::getInstance()->PostMessage($threadid, KIND_FOR_AGENT, Resources::Get('invite.window.closed', array(), $thread['locale']));
              $newState = STATE_CLOSED;
            }
            break;
          
          case 'visitor_invite_close':
            Thread::getInstance()->PostMessage($threadid, KIND_FOR_AGENT, $params['message']);
            $newState = STATE_CLOSED;
          default:
            if ($this->isCreatedTimeout($thread, INVITE_ANIMATION_DURATION + 30)) { // TODO correct timeout
              $newState = STATE_CLOSED;
            }
        }

        break;
        
      case STATE_LOADING_FOR_EXACT_OPERATOR:
            $forExactOperatorMode = true;
      case STATE_LOADING:
        switch($action) {
          case 'visitor_ping':
          case 'visitor_message':
            VisitSession::getInstance()->UpdateVisitSession($thread['visitsessionid'], array('waitmess'=>0));
            Thread::getInstance()->PostMessage($threadid, KIND_INFO, Resources::Get('chat.wait', array(), $thread['locale']));
            $newState = $forExactOperatorMode ? STATE_QUEUE_EXACT_OPERATOR : STATE_QUEUE;
            break;
          case "visitor_browser_unload":
            $newState = STATE_CLOSED;
            break;
          case 'operator_join':
          case 'operator_force_join':
          case 'operator_ping';
          case 'operator_message';
            $this->joinThreadAndSendMessage($thread, $params['operatorid']);
            
            $newState = STATE_CHATTING;
            break;
          default:
            if ($this->isCreatedTimeout($thread, TIMEOUT_VISITOR_PING)) {
              $this->sendVisitorLeft($thread);
              $newState = STATE_CLOSED;
            }
        }

        break;

      case STATE_QUEUE_EXACT_OPERATOR:
        if ($this->isOperatorTimeout($thread,  TIMEOUT_EXACT_OPERATOR)
          || $this->isCreatedTimeout($thread,  TIMEOUT_EXACT_OPERATOR)) {
          $newState = STATE_QUEUE;
          break;
        }
      case STATE_REDIRECTED:
        if ($this->isOperatorTimeout($thread,  TIMEOUT_EXACT_OPERATOR)) {
          $newState = STATE_QUEUE;
          break;
        }
      case STATE_QUEUE:
        switch($action) {
          case 'operator_join':
          case 'operator_force_join':
          case 'operator_ping';
          case 'operator_message';
          $this->joinThreadAndSendMessage($thread, $params['operatorid']);
          
          $operators = Operator::getInstance()->getOnlineOperators($params['operatorid']);
          $lvm = MapperFactory::getMapper("LostVisitor");
          foreach ($operators as $operator) {
            $lvm->addLostVisitor($threadid, $operator['operatorid'], $params['operatorid']);        
          }
          
          $newState = STATE_CHATTING;
          break;
          case 'visitor_close':
            $this->sendVisitorLeft($thread);
            $newState = STATE_CLOSED;
            break;
          default:
            if ($this->isVisitorTimeout($thread,  TIMEOUT_VISITOR_PING)) {
              $this->sendVisitorLeft($thread);
              $newState = STATE_CLOSED;
            }
            if ($this->isCreatedTimeout($thread, TIMEOUT_OPERATOR_NOANSWER)) {
                $session = VisitSession::getInstance()->GetVisitSessionById($thread['visitsessionid']);
                if (!$session['waitmess']) {
                    VisitSession::getInstance()->UpdateVisitSession($thread['visitsessionid'], array('waitmess'=>1));
                    Thread::getInstance()->PostMessage($threadid, KIND_NOANSWER, Resources::Get('chat.noanswer', array(), $thread['locale']), null, null, null, 1);
                }
            }
            break;
        }
        break;

      case STATE_CHATTING:
        switch($action) {
          case "redirect":
            $hash = array(
              "operatorid" => null,
              "operatorfullname" => null,
            );
            if (!empty($params['nextoperatorid'])) {
              $hash["nextoperatorid"] = $params['nextoperatorid'];
            } 
            
            if (!empty($params['nextdepartmentid'])) {
              $hash["departmentid"] = $params['nextdepartmentid'];
            } else {
              $hash["departmentid"] = null;
            }
            
            Thread::getInstance()->CommitThread($threadid, $hash);

            Thread::getInstance()->PostMessage(
              $thread['threadid'],
              KIND_EVENTS,
              Resources::Get(
                "chat.status.operator.redirect",
                array($params['operator']['fullname']),
                $thread['locale']
              )
            );
            Thread::getInstance()->PostMessage($threadid, KIND_AVATAR, "");
            $newState = STATE_REDIRECTED;
            break;
          case "visitor_close":
            $this->sendVisitorLeft($thread);
            $newState = STATE_CLOSED;
            break;
          case "operator_close":
            $this->sendOperatorLeft($thread);
            $newState = STATE_CLOSED;
            break;
          case 'operator_force_join':
            $this->joinThreadAndSendMessage($thread, $params['operatorid']);
            break;
          case "visitor_browser_unload":
            $newState = STATE_CHAT_VISITOR_BROWSER_CLOSED_REFRESHED;
            break;
          default:
            if ($this->isVisitorTimeout($thread, TIMEOUT_VISITOR_PING)) {
              $this->sendVisitorLeft($thread);
              $newState = STATE_CLOSED;
            } elseif ($this->isOperatorTimeout($thread, TIMEOUT_OPERATOR_PING)) {
              $message_to_post = Resources::Get("chat.status.operator.dead", array(), $thread['locale']);
              Thread::getInstance()->PostMessage($threadid, KIND_EVENTS, $message_to_post);
              $newState = STATE_CHATTING_OPERATOR_BROWSER_CLOSED_REFRESHED;
            }
        }

        break;

      case STATE_CHAT_VISITOR_BROWSER_CLOSED_REFRESHED:
        switch($action) {
          case "visitor_ping":
          case "visitor_message":
            $newState = STATE_CHATTING;
            break;
          default:
            if ($this->isVisitorTimeout($thread, TIMEOUT_REFRESH)) {
              $this->sendVisitorLeft($thread);
              $newState = STATE_CLOSED;
            }
            break;
        }

        break;

      case STATE_CHATTING_CLOSED_REFRESHED:
        switch($action) {
          case "visitor_ping":
          case "visitor_message":
            $newState = STATE_CHATTING;
            break;
          default:
            if ($this->isVisitorTimeout($thread, TIMEOUT_REFRESH)) {
              $this->sendVisitorLeft($thread);
              $newState = STATE_CLOSED;
            }
            break;
        }

        break;


      case STATE_CHATTING_OPERATOR_BROWSER_CLOSED_REFRESHED:
        switch($action) {
          case "operator_ping":
          case "operator_message":
            $newState = STATE_CHATTING;
            break;
          case 'operator_force_join':
            $this->joinThreadAndSendMessage($thread, $params['operatorid']);
            $newState = STATE_CHATTING;
            break;
          case 'visitor_close':
            $this->sendVisitorLeft($thread);
            $newState = STATE_CLOSED;
            break;
          default:
            if ($this->isVisitorTimeout($thread,  TIMEOUT_VISITOR_PING)) {
              $this->sendVisitorLeft($thread);
              $newState = STATE_CLOSED;
            } elseif ($this->isOperatorTimeout($thread, TIMEOUT_EXACT_OPERATOR)) {
              $newState = STATE_QUEUE;
            }

            break;
        }

        break;

      case STATE_CLOSED:
        switch($action) {
          //          case "visitor_ping":
          case "visitor_message":
            $this->resetOperatorPing($thread);
            if (isset($thread['operatorid'])) {
              $newState = STATE_CHATTING_OPERATOR_BROWSER_CLOSED_REFRESHED;
            } else {
              $newState = STATE_QUEUE;
            }
            break;
          case "operator_message":
            $newState = STATE_CHATTING;
            break;
        }
        
        break;
        
    } 

    if (isset($newState)) {


      if ($newState == STATE_QUEUE) {


      }

      if($newState == STATE_CLOSED) {
        if($thread['operatorid'] === null) {
          $operators = Operator::getInstance()->getOnlineOperators();
          $lvm = MapperFactory::getMapper("LostVisitor");
          foreach ($operators as $operator) {
            $lvm->addLostVisitor($threadid, $operator['operatorid']);
          }
        }
        
        // папка online в мэмкэш --------------------
        //@unlink(Thread::getOperatorHasMesagesFilename($threadid));
        //@unlink(Thread::getVisitorHasMesagesFilename($threadid));
        $GLOBALS['mem_buff']->delete( Thread::getOperatorHasMesagesFilename($threadid) );
        $GLOBALS['mem_buff']->delete( Thread::getVisitorHasMesagesFilename($threadid) );
      }
      
      $this->updateThreadState($threadid, $newState);
    }

    return $res;
  }

  private function sendVisitorLeft($thread) { 
    $visitSession = VisitSession::GetInstance()->GetVisitSessionById($thread['visitsessionid']);
    $message = Resources::Get("chat.status.visitor.left", 
                              array($visitSession['visitorname']),
                              $thread['locale']);
    Thread::getInstance()->PostMessage($thread['threadid'], KIND_FOR_AGENT, $message);
  }
  
  private function sendOperatorLeft($thread) {
    $message = Resources::Get('chat.status.operator.left', $thread['operatorfullname'], $thread['locale']);
    Thread::getInstance()->PostMessage($thread['threadid'], KIND_EVENTS, $message,  null, null, null, 2);
  }

  private function joinThread($thread, $operatorid) {
    $operator = Operator::getInstance()->GetOperatorById($operatorid);
    $hash = array(
    
    "nextoperatorid" => null,
    
    "operatorid" => $operator['operatorid'],
    "operatorfullname" => $operator['fullname'],
    "nextoperatorid" => null
    );

    Thread::getInstance()->CommitThread($thread['threadid'], $hash);
  }

  private function joinThreadAndSendMessage($thread, $operatorId) {
    $threadid = $thread['threadid'];

    $operator = Operator::getInstance()->GetOperatorById($operatorId);
    if ($operatorId != $thread['operatorid']) {
      $message = Resources::Get(
      "chat.status.operator.joined",
      array($operator['fullname']),
      $thread['locale']
      );
      Thread::getInstance()->PostMessage($threadid, KIND_EVENTS, $message);
      
      // should explicitly send empty string for unset avatar
      $avatar = isset($operator['avatar']) && !empty($operator['avatar']) ? $operator['avatar'] : "";
      Thread::getInstance()->PostMessage($threadid, KIND_AVATAR, $avatar);
      
    }

    $this->joinThread($thread, $operatorId);

  }


  private function isVisitorTimeout($thread, $timeout) {
    return $this->isTimeoutOrEmpty($thread, $timeout, 'lpvisitor');
  }

  private function isOperatorTimeout($thread, $timeout) {
    return $this->isTimeout($thread, $timeout, 'lpoperator');
  }
  
  private function isCreatedTimeout($thread, $timeout) {
    return $this->isTimeout($thread, $timeout, 'tscreated');
  }

  private function isTimeoutOrEmpty($thread, $timeout, $field) {
    return empty($thread[$field]) || $this->isTimeout($thread, $timeout, $field);
  }
  
  private function isTimeout($thread, $timeout, $field) {
    $res = false;



                              
    if (empty($thread[$field])) {
      $res = false;
    } else {
      $res = $thread['current'] - $thread[$field] > $timeout;
    }

    return $res;
  }

  function ProcessOpenThreads() {
    $threads = MapperFactory::getMapper("Thread")->enumOpenWithTimeout(PROCESS_THREADS_DELAY);
    foreach ($threads as $thread) {
      $this->ProcessThread($thread['threadid'], 'idle');
    }
  }
  
  private function resetOperatorPing($thread) {
    Thread::getInstance()->CommitThread($thread['threadid'], array('lastpingagent'=>null, 'agenttyping'=>null));
  }


  private function ping($thread, $params, $pingfield, $typingfield) {
    $hash[$pingfield] = null; 
    $hash[$typingfield] = !empty($params['istyping'])  && $params['istyping'] ? '1' : '0';
    Thread::getInstance()->CommitThread($thread['threadid'], $hash);
  }

  private function updateThreadState($threadid, $state) {
    $hash = array('state' => $state);
    Thread::getInstance()->CommitThread($threadid, $hash);
  }


}
?>
