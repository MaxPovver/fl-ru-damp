<?php


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

// Только фрилансерам
$sql = "SELECT f.uid, f.email, f.login, f.uname, f.usurname, f.subscr
            FROM account ac 
        INNER JOIN freelancer f ON f.uid = ac.uid AND is_banned = B'0' AND substr(f.subscr::text,8,1) = '1'
        WHERE ac.sum >= 3 AND ac.sum <= 6.99
        AND id NOT IN (SELECT billing_id FROM account_operations WHERE op_date::date >= NOW()-'1 month'::interval
                        AND op_code NOT IN (12, 23)
                        AND  NOT (op_code IN (16, 52, 66, 67, 68, 17, 69, 83, 84, 85) AND ammount >= 0)  )";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "Здравствуйте!

Остаток средств на http:/{вашем личном счете}/{$pHost}/bill/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm позволяет вам прорекламировать свои услуги на сайте и получить еще больше заказов. Вы можете потратить оставшиеся FM следующим образом:

<ul>";
$pMessage .= "<li>приобрести http:/{дополнительную специализацию}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm, чтобы получать заказы по новым направлениям деятельности; </li>";
$pMessage .= "<li>прокатиться на «Карусели» http:/{на главной странице}/{$pHost}/pay_place/top_payed.php?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm – вы заявите о себе и сразу станете заметнее для потенциальных заказчиков и других фрилансеров, и на «Карусели» http:/{в каталоге фрилансеров}/{$pHost}/pay_place/top_payed.php?catalog&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm – вы сможете выделиться, когда работодатель смотрит на ваших прямых конкурентов в каталоге;  </li>";
$pMessage .= "<li>поместить объявление с предложением своих услуг в разделе http:/{«Сделаю»}/{$pHost}/public/offer/?kind=8&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm – заказчики найдут вас сами. </li>";
$pMessage .= "</ul>
Не упускайте возможность привлечь работодателей и увеличить свои доходы!

По всем возникающим вопросам вы можете обращаться в нашу http:/{службу поддержки}/{$pHost}/help/.
Вы можете отключить уведомления на http:/{странице «Уведомления/Рассылка»}/{$pHost}/users/%USER_LOGIN%/setup/mailer/ вашего аккаунта.

Приятной работы,
Команда http:/{Free-lance.ru}/{$pHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm";


$eSubject = "Найдите новых заказчиков на Free-lance.ru прямо сейчас";

$eMessage = "<p>Здравствуйте!</p>

<p>
Остаток средств на <a href='{$eHost}/bill/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>вашем личном счете</a> позволяет вам прорекламировать свои услуги на сайте и получить еще больше заказов. Вы можете потратить оставшиеся FM следующим образом:
</p>

<ul>
<li>приобрести <a href='{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>дополнительную специализацию</a>, чтобы получать заказы по новым направлениям деятельности; </li>
<li>прокатиться на «Карусели» <a href='{$eHost}/pay_place/top_payed.php?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>на главной странице</a> – вы заявите о себе и сразу станете заметнее для потенциальных заказчиков и других фрилансеров, и на «Карусели» <a href='{$eHost}/pay_place/top_payed.php?catalog&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>в каталоге фрилансеров</a> – вы сможете выделиться, когда работодатель смотрит на ваших прямых конкурентов в каталоге;  </li>
<li>поместить объявление с предложением своих услуг в разделе <a href='{$eHost}/public/offer/?kind=8&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>«Сделаю»</a> – заказчики найдут вас сами. </li>
</ul>

<p>
Не упускайте возможность привлечь работодателей и увеличить свои доходы!
</p>

<p>
По всем возникающим вопросам вы можете обращаться в нашу <a href='{$eHost}/help/' target='_blank'>службу поддержки</a>.<br/>
Вы можете отключить уведомления на <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/' target='_blank'>странице «Уведомления/Рассылка»</a> вашего аккаунта.
</p>

Приятной работы!<br/>
Команда <a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm' target='_blank'>Free-lance.ru</a>";

// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('plproxy');
$master = new DB('master');
$cnt = 0;


$sender = $master->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}

echo "Send personal messages\n";

// подготавливаем рассылку
$msgid = $DB->val("SELECT masssend(?, ?, '{}', '')", $sender['uid'], $pMessage);
if (!$msgid) die('Failed!');

// допустим, мы получаем адресатов с какого-то запроса
$i = 0;
while ($users = $master->col("{$sql} LIMIT 30000 OFFSET ?", $i)) {
    $DB->query("SELECT masssend_bind(?, {$sender['uid']}, ?a)", $msgid, $users);
    $i = $i + 30000;
}
// Стартуем рассылку в личку
$DB->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 
echo "Send email messages\n";

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
$res = $master->query($sql);
while ($row = pg_fetch_assoc($res)) {
    $mail->recipient[] = array(
        'email' => $row['email'],
        'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'USER_LOGIN' => $row['login'])
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