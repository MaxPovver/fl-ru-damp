<?php
define('LAST_REFRESH_DISABLE', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/notifications.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/bar_notify.php';
session_start();

$out = array(
    'success' => false,
    'delay'   => NOTIFICATION_DELAY
);

if (isset($_SESSION['rand'])) {
    $out['token'] = $_SESSION['rand'];
}

if (!empty($_SESSION['uid']) && !empty($_POST['op']) ) {
    $old_pro = is_pro(); // То что в сеcсии лежит
    $pro = is_pro(true); // То что на самом деле, проверяем про заного
    if($pro == 0 && $old_pro > 0 && !$_SESSION['is_freezed']) {
        unset($_SESSION['pro_last']);
        $out['pro']['action'] = 'done';
        $out['pro']['role']   = is_emp() ? 'EMP' : 'FRL';
    }
    $op  = explode('|', $_POST['op']);
    
    // Новые события в заказах ТУ
    if ( in_array('tu', $op) ) {
        $tip = notifications::getTServicesOrdersTip();

        if ($tip === null) {
            $out['tu']['success'] = false;
        } else {
            $out['tu']['success']  = true;
            $out['tu']['tip']      = iconv("CP1251", "UTF-8", $tip['tip']);
            $out['tu']['count']    = $tip['count'];
            $out['tu']['count_html'] = view_event_count_format($tip['count']);
            $out['tu']['link']    = $tip['link'];
        }
    }
    
    // новые события в проектах
    if ( in_array('prj', $op) ) {
        if(is_emp()) {
            $tip = notifications::getProjectsTipEmp();
        } else {
            $tip = notifications::getProjectsTipFrl();
        }

        if ($tip === null) {
            $out['prj']['success'] = false;
        } else {
            $out['prj']['success']  = true;
            $out['prj']['tip']      = iconv("CP1251", "UTF-8", $tip['tip']);
            $out['prj']['count']    = $tip['count'];
            $out['prj']['count_html'] = view_event_count_format($tip['count']);
            $out['prj']['link']    = $tip['link'];
        }
    }
    
    // новые сообщения
    if ( in_array('msg', $op) ) {
        $out['msg'] = array();
        $tip = notifications::getMessTip(true);

        if ($tip === null) {
            $out['msg']['success'] = false;
        } else {
            $out['msg']['success'] = true;
            $out['msg']['count'] = $tip['count'];
            $out['msg']['count_html'] = view_event_count_format($tip['count']);
            $out['msg']['tip']   = iconv("CP1251", "UTF-8", $tip['tip']);
        }
    }

    // события в сбр
    if ( in_array('sbr', $op) ) {
        $tip = notifications::getAllSbrTip();
        if ($tip === null) {
            $out['sbr']['success'] = false;
        } else {
            $out['sbr']['success'] = true;
            $out['sbr']['count'] = $tip['count'];
            $out['sbr']['count_html'] = view_event_count_format($tip['count']);
            $out['sbr']['alert'] = $tip['alert'];
            $out['sbr']['tip']   = iconv("CP1251", "UTF-8", $tip['tip']);
        }
    }

    // новые события через класс bar_notify
    $barNotify = new bar_notify(get_uid(0));
    $barNotifies = $barNotify->getNotifies();
    if (in_array('bill', $op)) {
        $out['bill']['success'] = true;
        $out['bill']['count'] = (int)$barNotifies['bill']['count'];
        $out['bill']['count_html'] = view_event_count_format($tip['count']);
        $out['bill']['tip']   = iconv("CP1251", "UTF-8", $barNotifies['bill']['message']);
    }

    $out['success'] = true;

}

echo json_encode( $out );