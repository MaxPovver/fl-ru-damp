<?php

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

$eHost = $GLOBALS['host'];

$eSubject = "Мы нашли для вас простой способ верификации";

$eMessage = "<p>Здравствуйте!</p>

<p>
Появился новый бесплатный способ верификации за 10 минут через Okpay.com. Верификация доступна для резидентов всех стран, без участия нотариуса, посещения офисов компаний – проходит в режиме онлайн.
</p>

<p>
Подтвердите личность и получите все преимущества верифицированного пользователя:
</p>
<ul>
<li>безлимитные ответы на проекты (по вашей специализации);</li>
<li>рейтинг  +20%;</li>
<li>доступ к проектам «Только для верифицированных»;</li>
<li>доверие серьезных заказчиков.</li>
</ul>
<p>
<a href='{$eHost}/promo/verification/?service=okpay&utm_source=newsletter4&utm_medium=email&utm_campaign=unverif_okpay' target='_blank'>Перейти к инструкции по верификации</a>
</p>

<p>
По всем возникающим вопросам обращайтесь в нашу <a href='http://feedback.free-lance.ru/' target='_blank'>службу поддержки</a>.<br/>
Вы можете отключить уведомления <a href='{$eHost}/unsubscribe?ukey=%UNSUBSCRIBE_KEY%' target='_blank'>на этой странице</a>.
</p>

Приятной работы с <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>!";

// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('master');
$cnt = 0;


// Только всем, незабаненным (is_banned = B'0'), с включенными рассылками (substring(subscr from 8 for 1)::integer = 1)
//sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'";
//всем незабаненым, кто пытался верифицироваться всеми способами кроме okpay и не смог
$limit = 8000;  
$sql = "
(SELECT DISTINCT ff.user_id AS uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey FROM verify_ff AS ff 
   INNER JOIN users AS u ON ff.user_id = u.uid AND u.is_verify = false
   LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
   WHERE result = false AND is_banned = B'0'
)

UNION 

(SELECT DISTINCT wm.user_id AS uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey FROM verify_webmoney AS wm 
   INNER JOIN users AS u ON wm.user_id = u.uid AND u.is_verify = false
   LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
   WHERE result = false AND is_banned = B'0'
)

UNION 

(SELECT DISTINCT pskb.user_id AS uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey FROM verify_pskb AS pskb 
   INNER JOIN users AS u ON pskb.user_id = u.uid AND u.is_verify = false
   LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
   WHERE result = false AND is_banned = B'0'
)

UNION 

(SELECT DISTINCT yd.user_id AS uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey FROM verify_yd AS yd 
   INNER JOIN users AS u ON yd.user_id = u.uid AND u.is_verify = false
   LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
   WHERE result = false AND is_banned = B'0'
)
";

$sender = $DB->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}

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
    if($row['email'] == '') continue;
    if ( strlen($row['ukey']) == 0 ) {
        $row['ukey'] = users::writeUnsubscribeKey($row["uid"], true);
    }
    $mail->recipient[] = array(
        'email' => $row['email'],
        'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 
                         'USER_LOGIN' => $row['login'],
                         'UNSUBSCRIBE_KEY' => $row['ukey'])
    );
    if (++$i >= 30000) {
        $mail->bind($spamid);
        $mail->recipient = array();
        $i = 0;
    }
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}

echo "OK. Total: {$cnt} users\n";