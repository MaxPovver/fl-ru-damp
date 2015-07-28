<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");









//print_r(date('d', strtotime('9 days 05:32:11.897864')));

$_interval = '6 month';

$diff = abs(strtotime('now') - strtotime("+ {$_interval}"));
//$days = date('j', $diff);


$date = $diff/(60*60*24);

print_r($date);
exit;


exit;

$days = $diff/(60*60*24);

$hours = floor(($diff - $days * 60 * 60 * 24)/(60 * 60));


print_r($days . ' days ' . $hours . ' hours');



exit;

$uid = 78734;

$payed = new payed();
$data = $payed->ProLastById($uid, array(164));

print_r($data);

exit;


//print_r(payed::getAvailablePayedList(false));


/*
var_dump(isAllowProfi());
var_dump(isAllowProfi());
var_dump(isAllowProfi());
var_dump(isAllowProfi());
var_dump(isAllowProfi());
var_dump(isAllowProfi());
*/


//print_r(strtotime('- 2 years'));



$uid = 78730;

payed::freezePro($uid, '2014-10-25 00:00:00', '2014-11-10 00:00:00');
exit;




exit;

/*
$data = payed::getProfiDaysFromPro(78730);
print_r($data);
exit;
*/



/*
$data = $DB->rows("
    SELECT
    *
    FROM orders
    WHERE
        from_id = ?i
        AND from_date < NOW() AND (from_date + to_date) > NOW();
", 78701);

print_r($data);
*/


$sess = new session();
$sess->UpdateProEndingDate('freelancer78712');


exit;




$uid = 78706;

/*
$data = payed::getProfiDaysFromPro($uid);
print_r($data);

exit;
*/

/*
payed::freezePro($uid, '2014-10-11 00:00:00', '2014-10-18 00:00:00');
exit;
*/



$payed = new payed();
$data = $payed->ProLastById($uid, array(164));

print_r($data);

exit;


$is_pro = $payed->checkProByUid($uid);
assert($is_pro == true);


//$ok = $payed->freezeProDeactivate($uid);
//assert($ok == true);

exit;





$last_freeze = payed::getLastFreeze($uid);

/*
if($last_freeze) {
    
    $from_time = strtotime($last_freeze['from_time_date']);
    $to_time = strtotime($last_freeze['to_time_date']);
     
    if ($from_time <= time() && strtotime($last_freeze['to_time']) > time()) {
            $freezed_now = true;
            $freezed_alert = false;
            $freeze_act = 'freeze_stop';
        }
    
    
}
*/

print_r($last_freeze);
exit;


/*
$data = payed::getProfiDaysFromPro(78701);
print_r($data);
exit;
*/


payed::freezePro($uid, '2014-10-20 00:00:00', '2014-10-27 00:00:00');
exit;


$data = payed::ProLast('freelancer78701');
print_r($data);
exit;