<?php

$last_freeze = payed::getLastFreeze($uid);

$last_freeze_id = null;

$freeze_allow = false;
$freeze_set = false;
$freezed_now = false;
$freezed_alert = false;

$freeze_act = 'freeze';

$from_time = $to_time = time();

$location = ( substr($_SESSION['role'], 0, 1) == 1 ) ? '/payed-emp/' : '/payed/';

if($last_freeze) {
    $last_freeze_id = $last_freeze['id'];

    $from_time = strtotime($last_freeze['from_time_date']);
    $to_time = strtotime($last_freeze['to_time_date']);

    if(ceil($last_freeze['freezed_days']/7) < 4 && $last_freeze['freezed_cnt'] < 4) {
        $freeze_allow = true;
    } else {
        $freezed_alert = true;
    }

    if($from_time > time()) {
        $freeze_set = true;
        $freezed_alert = false;
        $freeze_act = 'freeze_cancel';
    }

    if($from_time <= time() && strtotime($last_freeze['to_time']) > time()){
        $freezed_now = true;
        $freezed_alert = false;
        $freeze_act = 'freeze_stop';
    }

} elseif(is_pro()) {
    $freeze_allow = true;
    $from_time += 24*3600;
    $to_time += 24*3600;
}
$from_time = date('Y-m-d', $from_time);
$to_time = date('Y-m-d', $to_time);


//if(date('Ymd', strtotime($_SESSION['pro_last'])) == date('Ymd')) $freeze_allow = false;
//@todo: выключаем возможность заморозки
//https://beta.free-lance.ru/mantis/view.php?id=29292
$freeze_allow = false;



$freeze_disabled = !( (ceil($last_freeze['freezed_days']/7) <= 3 || ($freeze_set || $freezed_now)) && !$freezed_alert );

$action = __paramInit('string', null, 'action');
$from_date = __paramInit('string', null, 'from_date');
$to_date = __paramInit('string', null, 'to_date');

//var_dump($from_time);
//$from_date = '2011-05-10';

if ($action == 'freeze' && $freeze_allow) {
    $fstart = mktime(0,0,0, date('m'), (date('d')+1), date('Y'));
    
    if ($from_date && $to_date && strtotime($from_date) >= $fstart) {

        if ($to_date != 1 && $to_date != 2 && $to_date != 3 && $to_date != 4) {
            $to_date = 1;
        }
        //if ($to_date == 2 && ceil($last_freeze['freezed_days']/7) == 1) {
        //    $to_date = 1;
        //}
        $ft = strtotime($from_date);
        $to_date = date('Y-m-d', mktime(0, 0, 0, date('m', $ft), (date('d', $ft)+(intval($to_date)*7)), date('Y', $ft)));
        
        payed::freezePro($uid, $from_date, $to_date);

        $from_time = $from_date;
        $to_time = $to_date;

        $freeze_set = true;
        $freeze_act = 'freeze_cancel';

        $pro_last = payed::ProLast($_SESSION['login']);
        if($pro_last['freeze_to']) {
            $_SESSION['freeze_from'] = $pro_last['freeze_from'];
            $_SESSION['freeze_to'] = $pro_last['freeze_to'];
            $_SESSION['is_freezed'] = $pro_last['is_freezed'];
            $_SESSION['payed_to'] = $pro_last['cnt'];
        }
        $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];

        header_location_exit($location);

    } elseif (strtotime($from_date) > strtotime($_SESSION['pro_last']) || strtotime($from_date) < $fstart ) {
        $freeze_error = 'Неверная дата начала заморозки.';
    } else {
        $freeze_error = 'Ошибка, не указана одна из дат.';
    }
}

if ($action == 'freeze_cancel' && $freeze_set) {
    if (!payed::freezeProCancel($uid, $last_freeze_id)) {
        $freeze_error = 'Невозможно отменить заморозку.';
    } else {
        $freeze_set = false;
        $freeze_allow = true;
        $from_time = $to_time = date('Y-m-d', time()+24*3600);

        $_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);
        if(isset($_SESSION['freeze_from'])) unset($_SESSION['freeze_from']);
        if(isset($_SESSION['freeze_to'])) unset($_SESSION['freeze_to']);
        if(isset($_SESSION['is_freezed'])) unset($_SESSION['is_freezed']);
        $_SESSION['pro_last'] = $_SESSION['pro_last']['is_freezed'] ? false : $_SESSION['pro_last']['cnt'];

        header_location_exit($location);
    }
}

if ($action == 'freeze_stop' && $freezed_now) {
    if (!payed::freezeProStop($uid, $last_freeze_id)) {
        $freeze_error = 'Невозможно разморозить аккаунт.';
    } else {
        $pro_last = payed::ProLast($_SESSION['login']);
        if(!$pro_last['freeze_to']) {
            if(isset($_SESSION['freeze_from'])) unset($_SESSION['freeze_from']);
            if(isset($_SESSION['freeze_to'])) unset($_SESSION['freeze_to']);
            if(isset($_SESSION['is_freezed'])) unset($_SESSION['is_freezed']);
        } else {
            $_SESSION['freeze_from'] = $pro_last['freeze_from'];
            $_SESSION['freeze_to'] = $pro_last['freeze_to'];
            $_SESSION['is_freezed'] = $pro_last['is_freezed'];
            $_SESSION['payed_to'] = $pro_last['cnt'];
        }
        $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];

        $freezed_now = $freeze_allow = false;

        header_location_exit($location);
    }
}

