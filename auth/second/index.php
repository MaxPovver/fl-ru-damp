<?php

/**
 * 2ой этап 2х-этапной авторизации
 */

define('IS_AUTH_SECOND', true);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OpauthModel.php");

$uid = get_uid(false);

if ($uid > 0) {
    //Если уже авторизован то отправляем 
    //на раздел безопасность в профиле    
    header("Location: /users/{$_SESSION['login']}/setup/safety/");
    exit;
} elseif (!isset($_SESSION['2fa_provider'])) {
    //Если это не 2ой этап то на регистрацию
    header("Location: /registration/");
    exit;    
}

//Передаем во вьюшку тип 2ого этапа
//0 - обычная
//1... - по типу соцсети
$_2fa_provider = $_SESSION['2fa_provider']['type'];
$_2fa_login = $_SESSION['2fa_provider']['login'];

//Пытаемся установить нужные поля чтобы 
//после авторизации пользователя направило куда нужно
if (isset($_SESSION['2fa_redirect'])) {
    $redirectUri = $_SESSION['2fa_redirect']['redirectUri'];
    $_user_action = $_SESSION['2fa_redirect']['_user_action'];
}

//Сообщение об ошибке
$alert_message = session::getFlashMessages('/auth/second/');

$hide_banner_top = true;
//Скрыть форму авторизации в меню
$registration_page = true;
$js_file[] = "/css/block/b-eye/b-eye.js";
$js_file[] = 'registration/login.js';
$content = "content.php";
include ("../../template3.php");