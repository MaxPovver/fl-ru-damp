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
  require_once('../classes/class.thread.php');
  require_once('../classes/class.smartyclass.php');
  

//  if (isset($_GET['threadid'])) {
//    $threadid = verify_param("threadid", "/^(\d{1,9})?$/", "");
//    $lastid = -1;
//    $TML->assign('messages', Thread::getInstance()->GetMessages($threadid, "html", false, $lastid));
//  }
  $threadid = verify_param("threadid",  "/^(\d{1,9})?$/");
  if (empty($threadid)) {
    die('threadid should be provided for history');
  }

  $operator = Operator::getInstance()->GetLoggedOperator();
  $canAccess = Thread::getInstance()->hasThreadAccess($operator['operatorid'], $threadid);
  if (!$canAccess) {
    die('access denied');
  }

  $TML = new SmartyClass();
  $lastid = '-1';
  $TML->assign('messages', Thread::getInstance()->GetMessages($threadid, "html", false, $lastid));
  $TML->assign('threadid', $threadid);
  $TML->assign('is_admin', Operator::getInstance()->isCurrentUserAdmin());
  
  
  if (isset($_REQUEST['act'])) {
    switch ($_REQUEST['act']) {
      case 'removerate':
        Operator::getInstance()->IsCurrentUserAdminOrRedirect();
        $rateid = verify_param("rateid",  "/^(\d{1,9})?$/");
        $url = WEBIM_ROOT."/operator/threadprocessor.php?threadid=".$threadid;

        Thread::getInstance()->removeRate($rateid);
        header("Location: ".$url);
        exit();
      break;
      case 'removethread':
        Operator::getInstance()->IsCurrentUserAdminOrRedirect();
        $threadid = verify_param("threadid",  "/^(\d{1,9})?$/");
        $url = WEBIM_ROOT."/operator/threadprocessor.php?threadid=".$threadid;
        $TML->assign("removed_thread", true);
        MapperFactory::getMapper("Thread")->delete($threadid);
        //Thread::getInstance()->removeRate($rateid);
        //header("Location: ".$url);
        //exit();
      break;
      case 'removehistory':
        Operator::getInstance()->IsCurrentUserAdminOrRedirect();
        $url = WEBIM_ROOT."/operator/history.php"; // TODO history

        Thread::getInstance()->removeHistory($threadid);
        header("Location: ".$url);
        exit();
      break;
    }
  }
  

  $TML->display('thread_log.tpl');
 
?>