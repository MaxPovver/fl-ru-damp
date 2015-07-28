<?php
/**
 * Выводит заглушки и форму авторизации
 *
 */

if(!defined('IN_STDF')) {
    include_once($_SERVER['DOCUMENT_ROOT'] . "/404.php");
    exit();
}


if(defined('IS_CLOSED') && IS_CLOSED) {
    
    if(defined('IS_OPENED')) return; // если нужно оставить работающим отдельный скрипт.
    if(defined('IS_EXTERNAL')) return;
    if(defined('IS_PGQ')) return;
    
    if(preg_match('~^/(?:webim/|income|classes/mail_cons\.php|image\.php)~', $_SERVER['PHP_SELF']))
        return;

    if(!session_id()) {
        session_start();
    }

    $sessid = 'maintenance_'.session_id();
    $is_auth = $session->read($sessid);

    if($is_auth !== 'ok') {
        header('Content-Type: text/html');
        /**
         * Заглушки
         */
        $pages = array(
            'content_closed.php',
            'content_tv.php',
            'content_tetris.php'
        );

        $auth_allow = (isset($_GET['x']) && $_GET['x'] == IS_CLOSED);

        if($auth_allow && isset($_POST['login']) && isset($_POST['passw'])
            && trim($_POST['login']) != '' && trim($_POST['passw']) != '' ) {

            $login = trim($_POST['login']);
            $passw = md5(trim($_POST['passw']));
            $DB = new DB('master');
            $sql = "SELECT * FROM team_people WHERE (lower(login) = ? AND passw = ?)";
            $res = $DB->query($sql, strtolower($login), $passw);

            if(pg_numrows($res) && pg_fetch_result($res, 0, 0)) {
                $session->write($sessid, 'ok');
                header('Location: /');
            } else {
                $error = 'Нет такого!';
            }
        }

        $page = $pages[mt_rand(0, (count($pages)-1))];

        include_once($_SERVER['DOCUMENT_ROOT'] . "/siteclosed/{$page}");
        exit();
    }
}
