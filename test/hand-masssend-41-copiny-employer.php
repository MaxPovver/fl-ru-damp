<?php
/**
 * Уведомление работодателям
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';


/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
$sender = 'admin';

// Только работодателям, активированным и неактивированным, незабаненным (is_banned = B'0'), с включенными рассылками

$sql = "SELECT uid FROM employer AS u INNER JOIN (SELECT DISTINCT emp_id FROM sbr) AS dsbr ON dsbr.emp_id = u.uid
        WHERE is_banned = B'0'
        AND (substring(subscr from 8 for 1)::integer = 1) LIMIT 3000 OFFSET ?"; //employer

//$sql = "SELECT uid FROM users WHERE login IN ('land_e2', 'land_f')"; 

$pHost = str_replace("https://", "", $GLOBALS['host']);
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}

$pMessage = "Уважаемые пользователи!

Спасибо, что воспользовались \"Сделкой Без Риска\" при заказе услуг фрилансера на сайте Free-lance.ru.

Приглашаем вас делиться отзывами, идеями и предложениями {$pHttp}:/{здесь}/feedback.free-lance.ru/topics?category=5268

Наша служба поддержки круглосуточно готова помочь вам в оформлении и сопровождении сделок, в выборе фрилансеров, определении стоимости сделки и в других возникающих вопросах.

Благодарим за сотрудничество.

Приятной работы с http:/{Free-lance}/{$pHost}/<i></i>!
";
// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('plproxy');
$M  = new DB('master');
 
// подготавливаем рассылку
$msgid = $DB->val("SELECT masssend(103, '$pMessage', '{}', '')");  
$i = 0;
while ( $users = $M->col($sql, $i) ) {
    $DB->query("SELECT masssend_bind(?, 103, ?a)", $msgid, $users);
    $i = $i + 3000;
}
$DB->query("SELECT masssend_commit(?, 103)", $msgid);
echo "OK";
