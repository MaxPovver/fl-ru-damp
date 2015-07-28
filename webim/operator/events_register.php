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

EventController::getInstance()->addEventListener(
	EventController::EVENT_OPERATOR_STATUS, 
	array(Operator::getInstance(), "UpdateOperatorStatus")
);

EventController::getInstance()->addEventListener(
	EventController::EVENT_OPERATOR_STATUS, 
	array(Operator::getInstance(), "updateOperatorOnlineStats")
);





EventController::getInstance()->addEventListener(
	EventController::EVENT_OPERATOR_PING, 
	array(Operator::getInstance(), "updateOperatorOnlineStatsForThread")
);

EventController::getInstance()->addEventListener(
	EventController::EVENT_OPERATOR_PING, 
	array(ThreadProcessor::getInstance(), "ProcessThread")
);





?>