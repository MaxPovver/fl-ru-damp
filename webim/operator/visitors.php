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
$TITLE_KEY = 'topMenu.visitors';

require_once(dirname(__FILE__).'/inc/admin_prolog.php');


require_once('../classes/functions.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.smartyclass.php');


$TML = new SmartyClass($TITLE_KEY);

$o = Operator::getInstance(); 


$operator = $o->GetLoggedOperator();

if ($o->isOperatorsLimitExceeded()) {
  $TML->display('operators_limit.tpl');
  require_once(dirname(__FILE__).'/inc/admin_epilog.php');
  die();
}

$o->UpdateOperatorStatus(
	$operator/*, 
	OPERATOR_STATUS_ONLINE, 
	$o->getLoggedOperatorDepartmentsKeys(), 
	$o->getLoggedOperatorLocales()*/
);


$lang = verify_param("lang", "/^[\w-]{2,5}$/", "");
if (!empty($lang)) {
    $TML->assign('lang_param', "?lang=$lang");
    $TML->assign('lang_and_is_operator_param', "?isoperator=true&lang=$lang");
} else {
    $TML->assign('lang_and_is_operator_param', "?isoperator=true");
}


$TML->display('pending_visitors.tpl');

require_once(dirname(__FILE__).'/inc/admin_epilog.php');
?>