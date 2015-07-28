<?php

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';

 
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$pMessage = "
Здравствуйте!

Мы представляем вам очередные изменения и надеемся, что все они – только к лучшему. 

Как мы сообщали ранее, отменена платная разблокировка аккаунтов и покупка рейтинга, убраны всплывающие рекламные предложения аккаунта PRO и «Сделки Без Риска». 

Теперь у вас есть возможность отписаться от уведомлений, которые приходят на вашу электронную почту. Для этого достаточно перейти по ссылке из нашего письма и ввести проверочный код – отправка сообщений от Free-lance.ru будет автоматически отключена.

С 14 по 21 февраля мы проводим акцию {$pHttp}:/{&laquo;Тестовый PRO за 1 FM&raquo;}/{$pHost}/promo/testpro/?utm_source=newsletter4&utm_medium=email&utm_campaign=ottepel<i>.</i> Фрилансеры, которые никогда не покупали PRO, смогут воспользоваться нашим специальным предложением и познакомиться с преимуществами профессионального аккаунта.

{$pHttp}:/{Напоминаем}/{$pHost}/blogs/free-lanceru/725656/identifikatsiya-veb-koshelka-cherez-sistemu-contact.html?utm_source=newsletter4&utm_medium=email&utm_campaign=ottepel, что для резидентов РФ с сегодняшнего дня возможна идентификация в Веб-кошельке через систему Contact.

Более подробную информацию о нововведениях на сайте и планах команды на ближайшее будущее можно узнать в {$pHttp}:/{&laquo;Блогах&raquo;}/{$pHost}/blogs/free-lanceru/726198/ottepel-prodoljaetsya.html?utm_source=newsletter4&utm_medium=email&utm_campaign=ottepel<i>.</i>

Приятной работы с {$pHttp}:/{Free-lance.ru}/{$pHost}/!
";

$DB = new DB('plproxy');
$M  = new DB('master');
 
// подготавливаем рассылку
$msgid = $DB->val("SELECT masssend(103, '$pMessage', '{}', '')");  
$i = 0;
// Только всем незабаненным (is_banned = B'0') 
//$testloginlist = " AND login IN ('land_f', 'bolvan1', 'vg_rabot1') ";
$testloginlist = "";
$sql = "SELECT uid FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' {$testloginlist} LIMIT 3000 OFFSET ?";
while ( $users = $M->col($sql, $i) ) {
    $DB->query("SELECT masssend_bind(?, 103, ?a)", $msgid, $users);
    $i = $i + 3000;
}
$DB->query("SELECT masssend_commit(?, 103)", $msgid);
echo "OK";