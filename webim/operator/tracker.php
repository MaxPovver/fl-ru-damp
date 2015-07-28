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
$TITLE_KEY = 'active.visits.queue';

require_once(dirname(__FILE__).'/inc/admin_prolog.php');

  

require_once('../classes/functions.php');
require_once('../classes/class.thread.php');
require_once('../classes/class.smartyclass.php');


$TML = new SmartyClass($TITLE_KEY);

$o = Operator::getInstance();
$operator = $o->GetLoggedOperator();

if ($o->isOperatorsLimitExceeded()) {
  $TML->display('operators_limit.tpl');
  require_once(dirname(__FILE__).'/inc/admin_epilog.php');
  die();
}

 


$TML->assign('visit_details', get_app_location(true, false).'/operator/visit.php?pageid=');



$TML->display('../templates/active_visitors.tpl');

require_once(dirname(__FILE__).'/inc/admin_epilog.php');
?>