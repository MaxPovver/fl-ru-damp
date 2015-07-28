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


$operator = Operator::getInstance()->GetLoggedOperator(false);

// папка online в мэмкэш --------------------
//touch_online_file(OPERATOR_VIEW_TRACKER_FILE);
$mem_buff->set( 'OPERATOR_VIEW_TRACKER_FILE', time(), 1800 );

if (!$operator) {
  Browser::SendXmlHeaders();
  echo "<error><descr>".escape_with_cdata(getstring("agent.not_logged_in"))."</descr></error>";
  exit;
}

$xml = Thread::getInstance()->BuildVisitorsXml();
Browser::SendXmlHeaders();
echo $xml;

?>