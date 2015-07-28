<?php

define('NO_CSRF', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/yandex_kassa.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");

$yandex_kassa = new yandex_kassa();
$log = new log('ykassa/%d%m%Y.log');
$log->writeln('----- ' . date('d.m.Y H:i:s'));
$log->writevar($_POST);

$action = __paramInit('string', null, 'action');

//@todo: выставл€ем тестовый режим дл€ валидации по IP
//после тестировани€ резерва Ѕ— убрать
/*
$scid = __paramInit('string', null, 'scid');
$is_test = $scid == yandex_kassa::SCID_SBR_TEST;
if($is_test) $yandex_kassa->setTest($is_test);
*/


$result = array();
if ($action == 'checkOrder') {
    $result = $yandex_kassa->order(false);
}

if ($action == 'paymentAviso') {
    $result = $yandex_kassa->order(true);
}

$log->writeln('----- ' . (isset($result['message']) ? $result['message'] : 'YES'));
$log->writeln();

echo "<?xml version=\"1.0\" encoding=\"windows-1251\"?>"
?>

<<?=$action?>Response <?php foreach ($result as $param => $value) { 
    echo "{$param}='{$value}' ";
} ?>/>