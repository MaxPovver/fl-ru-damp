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
require_once('../classes/class.operator.php');
require_once('../classes/class.thread.php');
require_once('../classes/class.threadprocessor.php');
require_once('../classes/class.eventcontroller.php');
require_once('../classes/events_register.php');


ThreadProcessor::getInstance()->ProcessOpenThreads();

$o = Operator::getInstance();
$operator = $o->GetLoggedOperator(false); 

$f = "i"."s"."Op"."er"."a"."to"."rsL"."im"."it"."E"."x"."ce"."ed"."ed";
if ($o->$f()) {
  die();
}

$status = verify_param("status", "/^\d{1,9}$/", OPERATOR_STATUS_ONLINE);

EventController::getInstance()->dispatchEvent(
	EventController::EVENT_OPERATOR_STATUS, 
	array(
		$operator/*, 
		$status, 
		Operator::getInstance()->getLoggedOperatorDepartmentsKeys(), 
		Operator::getInstance()->getLoggedOperatorLocales()*/
	)
); 

if ($status != 0) {
  $since = verify_param("since", "/^\d{1,9}$/", 0);
  $xml = Thread::getInstance()->buildPendingThreadsXml($since, $operator);
  Browser::SendXmlHeaders();
  echo $xml; 
}

exit;
?>