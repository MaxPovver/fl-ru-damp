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
 


require_once('classes/common.php');
require_once('classes/functions.php');
require_once('classes/class.thread.php'); 
require_once('classes/class.operator.php');
require_once('classes/class.browser.php');
require_once('classes/class.button.php');
require_once('classes/class.mailstatistics.php');




  set_error_handler("button_output_anyway", E_ALL & ~E_NOTICE & ~E_WARNING);
  $OLD_DISPLAY_ERRORS = ini_get('display_errors');
  ini_set('display_errors', 0);


$button_sent = false;



$lang = verify_param(isset($_GET['language']) ? "language" : "lang", "/^[\w-]{2,5}$/", "");

 
$documentRoot = $_SERVER["DOCUMENT_ROOT"];
$image = verify_param("bim", "/^[\w\.]+$/");

if (empty($image)) {
  $image = verify_param("image", "/^[\w\.]+$/", "webim");
}

if (empty($image)) {
  $image = 'webim';
}

$departmentKey = verify_param("departmentkey", "/^\w+$/");


@MailStatistics::sendStatsIfNeeded(MAIL_STATISTICS_FILE, MAIL_STATISTICS_HOUR);


$button_sent = Button::sendButton($image, $departmentKey, $lang, $_SERVER['DOCUMENT_ROOT'].WEBIM_ROOT);


  restore_error_handler();
  ini_set('display_errors', $OLD_DISPLAY_ERRORS);

exit;

function button_output_anyway($errno, $errstr, $errfile, $errline, $errcontext) {
  global $button_sent, $image, $lang, $departmentKey;







  if ($button_sent) {
    return;
  }
  Button::sendButton($image, $departmentKey, $lang, $_SERVER['DOCUMENT_ROOT'].WEBIM_ROOT, "off");
  die(1);
}
?>