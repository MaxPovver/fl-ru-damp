<?php
/**
 * Уведомление работодателям
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';
require_once '../classes/users.php';

/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
$sender = 'admin';

// Только подписаным пользователям, незабаненным (is_banned = B'0'), с включенными рассылками

$sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, usk.key AS ukey  FROM users AS u
LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
WHERE  u.is_banned = B'0' AND (substring(subscr from 8 for 1)::integer = 1)
ORDER BY u.uid"; //все незабаненые подписаные с учетом флага "Получать рассылку от администрации" в users

//$sql = "SELECT u.uid, email, login, uname, usurname, usk.key AS ukey FROM users AS u LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid WHERE login IN('land_f', 'land_e2')"; // !! 

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$eSubject = "Открываем проекты для верифицированных пользователей";
//<a href=\"{$eHost}/promo/verification?utm_source=newsletter4&utm_medium=email&utm_campaign=verification\" target=\"_blank\">Начать верификацию</a>
$eMessage = "<p>Здравствуйте!</p>
<p>В апреле мы запустили верификацию – процедуру подтверждения паспортных данных, которая доступна для фрилансеров и работодателей. Также появилась возможность публиковать проекты для верифицированных пользователей. 
</p><p>К сожалению, сделано это было слишком рано: на тот момент процедуру верификации прошло небольшое количество фрилансеров, поэтому мы приостановили публикацию таких проектов. Совсем скоро работодатели снова смогут публиковать проекты для верифицированных пользователей.</p>
<p>Напоминаем, что верифицированный пользователь получает дополнительные возможности на сайте и большее доверие со стороны как работодателей, так и фрилансеров. </p>
<p><a href=\"http://feedback.free-lance.ru/article/details/id/1270?utm_source=newsletter4&utm_medium=email&utm_campaign=prverific\" target=\"_blank\">Узнать подробнее про верификацию</a></p>
<p>По всем возникающим вопросам вы можете обращаться в нашу <a href=\"http://feedback.free-lance.ru?utm_source=newsletter4&utm_medium=email&utm_campaign=prverific\" target=\"_blank\">службу поддержки</a>.</p>
<br/><p>Вы можете отключить уведомления на <a href=\"{$eHost}/unsubscribe/?ukey=%UNSUBSCRIBE_KEY%&utm_source=newsletter4&utm_medium=email&utm_campaign=prverific\" target=\"_blank\">этой странице</a>.</p>

Приятной работы!<br/>
Команда <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>";

// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$proxy = new DB('plproxy');
$DB = new DB('master');
$cnt = 0;

$mail = new smtp;
$mail->subject = $eSubject;  // заголовок письма
$mail->message = $eMessage; // текст письма
$mail->recipient = ''; // свойство 'получатель' оставляем пустым
$spamid = $mail->send('text/html');
if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!
// допустим нам нужно получить список получателей с какого-либо запроса
$i = 0;
$mail->recipient = array();
$res = $DB->query($sql);
while ($row = pg_fetch_assoc($res)) {
    if ( strlen($row['ukey']) == 0 ) {
        $row['ukey'] = users::writeUnsubscribeKey($row["uid"]);
    }
    if ( is_email($row['email']) ) {
        $mail->recipient[] = array(
            'email' => $row['email'],
            'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'UNSUBSCRIBE_KEY' => $row['ukey'])
        );
        if (++$i >= 30000) {
            $mail->bind($spamid);
            $mail->recipient = array();
            $i = 0;
        }
    }
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}

echo "OK. Total: {$cnt} users\n";
