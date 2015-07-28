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
require_once('../classes/class.visitsession.php');


$operator = Operator::getInstance()->GetLoggedOperator();

$TML    = new SmartyClass();

$items_per_page = verify_param("items", "/^\d{1,3}$/", DEFAULT_ITEMS_PER_PAGE);
$visitsessionid = verify_param("visitsessionid", "/^\d{0,63}\.?\d{0,63}$/", "");
$threadid = verify_param("threadid", "/^\d{1,8}$/", "");

$found = Thread::getInstance()->GetThreadsByVisitSessionID($visitsessionid);

$tmlPage = array();
if ($found) {
  $pagination = setup_pagination($found);
  $tmlPage['pagination'] = $pagination['pagination'];
  $tmlPage['pagination_items'] = $pagination['pagination_items'];
  $TML->assign('pagination', generate_pagination($tmlPage['pagination']));
  for ($i=0; $i < count($tmlPage['pagination_items']); $i++) {
    $tmlPage['pagination_items'][$i]['diff'] =
    webim_date_diff($tmlPage['pagination_items'][$i]['modified'] - $tmlPage['pagination_items'][$i]['created']);
  }
}

 

$TML->assign('page_settings', $tmlPage);
$TML->display('visit_info.tpl');
?>