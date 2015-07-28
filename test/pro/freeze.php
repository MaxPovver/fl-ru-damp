<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


//------------------------------------------------------------------------------

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");


//------------------------------------------------------------------------------


if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);


//------------------------------------------------------------------------------

$login = $_GET['login'];
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];



$objUser = new users();
$objUser->GetUser($login);

if ($objUser->uid > 0) {

    $uid = $objUser->uid;

    //$fstart = mktime(0, 0, 0, date('m'), (date('d') + 1), date('Y'));

    if ($from_date && $to_date /*&& strtotime($from_date) >= $fstart*/) {

        if ($to_date != 1 && $to_date != 2 && $to_date != 3 && $to_date != 4) {
            $to_date = 1;
        }
        
        //if ($to_date == 2 && ceil($last_freeze['freezed_days'] / 7) == 1) {
        //    $to_date = 1;
        //}
        
        $ft = strtotime($from_date);
        $freeze_days = (int)$to_date*7;
        $to_date = date('Y-m-d', mktime(0, 0, 0, date('m', $ft), (date('d', $ft) + (intval($to_date) * 7)), date('Y', $ft)));

        payed::freezePro($uid, $from_date, $to_date);

        $from_time = $from_date;
        $to_time = $to_date;

        $freeze_set = true;
        $freeze_act = 'freeze_cancel';

        $pro_last = payed::ProLast($_SESSION['login']);

        echo "Ваш аккаунт будет заморожен с <b>" . date('d.m.Y', strtotime($from_date)) . "</b> на <b>{$freeze_days} дней</b>" ;
        exit;
        
    //} elseif (strtotime($from_date) > strtotime($_SESSION['pro_last']) || strtotime($from_date) < $fstart) {
    //    echo 'Неверная дата начала заморозки.';
    } else {
        echo 'Ошибка, не указана одна из дат.';
    }

} else {
    echo 'Пользователь не найден.';
}


echo '<br/>?login=kazakov&from_date=2015-06-16&to_date=1';


exit;