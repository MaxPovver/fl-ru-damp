<?php

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

define('IS_OPENED', true); 

if (!$_SERVER['DOCUMENT_ROOT']) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once("classes/log.php");

//$log = new log('minutly/minutly-sbr-'.SERVER.'-%d%m%Y[%H].log', 'w');

require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');

/**
 * проверка статуса новых аккредитивов и покрытия
 */
pskb::checkStatus(null, $in, $out);

/**
 * проверка аккредитивов в статусе trans (перечисление денег)
 */
if (date('i') % 5 == 0) {
    pskb::checkStagePayoputForSuperCheck(null, $in, $out);
}

if(pskb::PSKB_SUPERCHECK && date('i') % 2 == 0 ) { // сократим до раз в две минуты, раньше смысла нет. Ответ от ПСКБ формируется до 5 минут 
    pskb::checkStagePayouts(null, $in, $out);
} elseif(!pskb::PSKB_SUPERCHECK) { // Если отключен суперчек по старому
    pskb::checkStagePayouts(null, $in, $out);
}

if (date('i') % 2 == 0) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_adm.php');
    sbr_adm::processInvoiceData();
}