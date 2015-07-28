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
require_once('../classes/class.pagination.php');
require_once('../classes/class.smartyclass.php');


 

$operator = Operator::getInstance()->GetLoggedOperator();

$TML = new SmartyClass();

$action = $_REQUEST['action'];
$TML->assign('action', $action);


$threadid = verify_param( "thread", "/^\d{1,8}$/");
$token = verify_param( "token", "/^\d{1,8}$/");

$TML->assign('threadid', $threadid);
$TML->assign('token', $token);

if ($action == 'operators') {
  $found = Operator::getInstance()->getOnlineOperatorsWithDepartments($operator['operatorid'], Resources::getCurrentLocale());
  $TML->assign('operators', $found);
  
//  $out = setupPage($found, $action, 'operatorid', 'fullname');
//  $TML->assign('out', $out);
} elseif ($action == 'visitor_redirected') {
  $TML->Assign('link', WEBIM_ROOT.'/operator/agent.php?thread='.$threadid.'&token='.$token.'&level=ajaxed&viewonly=true');
} elseif ($action == 'chat_closed') {
  $TML->Assign('link', WEBIM_ROOT.'/operator/agent.php?thread='.$threadid.'&token='.$token.'&level=ajaxed&viewonly=true&history=true');
} 


$TML->display('popup.tpl');


function setupPage($list, $action, $idfield, $valuefield) {
  global $token, $threadid, $TML;
  $pagination = setup_pagination($list);
  if (!empty($pagination)) {
    $page = array();
    $page['pagination'] = $pagination['pagination'];
    $page['pagination_items'] = $pagination['pagination_items'];
    $page['params'] = array('thread' => $threadid, 'token' => $token);
    $TML->assign('pagination', generate_pagination($page['pagination']));
  }

  $out = array();
  if(!empty($page['pagination_items'])) {
    foreach($page['pagination_items'] as $v) {
      $page['params']['nextoperatorid'] = $v[$idfield];
      $params = array(
        'servlet_root' => WEBIM_ROOT,
        'servlet' => '/operator/redirect.php',
        'path_vars' => $page['params'],
      );

      $href = generate_get($params);
      $value = $v[$valuefield];

      $out[] = '<li><a href="'.$href.'" title="'.$value.'">'.$value.'</a></li>';
    }
  }
  return $out;
}
?>