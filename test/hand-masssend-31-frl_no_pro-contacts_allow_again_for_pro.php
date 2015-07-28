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

// Только не pro фрилансерам, активированным (active = true), незабаненным (is_banned = B'0'), с включенными рассылками

$sql = "SELECT DISTINCT(uid), email, login, uname, usurname, subscr FROM freelancer AS u LEFT JOIN
                orders o ON o.from_id = u.uid WHERE is_banned = B'0'
        AND (substring(subscr from 8 for 1)::integer = 1) 
        AND                 
                (o.payed IS NULL                
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )
        "; //freelancer


//$sql = "SELECT uid, email, login, uname, usurname FROM users WHERE email IN('jusoft@yandex.ru', 'lamzin80@mail.ru')"; // !! 

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$pMessage = "Здравствуйте!

С 5 декабря работодатели и исполнители с профессиональным аккаунтом могут делиться своими контактами (e-mail, ICQ, Skype и т.д.), поэтому многие фрилансеры приобретают для себя аккаунт PRO.
Без аккаунта PRO ваши контакты не видны работодателям, вы теряете позиции в каталоге фрилансеров, не отвечаете на проекты с пометкой «Только для PRO», количество которых после 5 декабря резко увеличилось и составляет 40% от общего количества проектов. Это значит, что вы теряете потенциальных заказчиков и деньги.

Подумайте о приобретении аккаунта PRO – с ним вы получите:
<ul><li>открытые контакты: информация о вас видна всем пользователям – работодатели смогут связаться с вами, даже если вы долгое время не заходите на сайт;</li><li>возможность оставлять свои контакты в проектах и сообщениях;</li><li>безлимитные ответы: вы сможете отвечать на все опубликованные проекты;</li><li>доступ к проектам «Только для PRO»: по статистике, их бюджет выше, чем бюджет обычных проектов, на 30%;</li><li>улучшенное портфолио: яркое оформление работ с указанием ваших контактов;</li><li>дополнительные специализации: отображение вашего профиля сразу в нескольких разделах каталога фрилансеров;</li><li>бонусную рекламу;</li></ul> и другие преимущества. 

{$pHttp}:/{Приобрести аккаунт PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO
По всем возникающим вопросам вы можете обращаться в нашу http:/{службу поддержки}/{$pHost}/help/?all<i>.</i>
Вы можете отключить уведомления на http:/{странице &laquo;Уведомления/Рассылка&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> вашего</span> аккаунта.

Приятной работы,
Команда http:/{Free-lance.ru}/{$pHost}/
";

$eSubject = "Free-lance.ru: не сдавайте свои позиции";

$eMessage = "<p>Здравствуйте!</p>
<p>С 5 декабря работодатели и исполнители с профессиональным аккаунтом могут делиться своими контактами (e-mail, ICQ, Skype и т.д.), поэтому многие фрилансеры приобретают для себя аккаунт PRO. </p>

<p>Без аккаунта PRO ваши контакты не видны работодателям, вы теряете позиции в каталоге фрилансеров, не отвечаете на проекты с пометкой «Только для PRO», количество которых после 5 декабря резко увеличилось и составляет 40% от общего количества проектов. Это значит, что вы теряете потенциальных заказчиков и деньги.</p>

<p>Подумайте о приобретении аккаунта PRO – с ним вы получите:</p>
<ul>
<li>открытые контакты: информация о вас видна всем пользователям – работодатели смогут связаться с вами, даже если вы долгое время не заходите на сайт;</li>
<li>возможность оставлять свои контакты в проектах и сообщениях;</li>
<li>безлимитные ответы: вы сможете отвечать на все опубликованные проекты;</li>
<li>доступ к проектам «Только для PRO»: по статистике, их бюджет выше, чем бюджет обычных проектов, на 30%;</li>
<li>улучшенное портфолио: яркое оформление работ с указанием ваших контактов;</li>
<li>дополнительные специализации: отображение вашего профиля сразу в нескольких разделах каталога фрилансеров;</li>
<li>бонусную рекламу;</li>
</ul>
<p>и другие преимущества. </p>

<p><a href=\"{$eHost}/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO\" target=\"_blank\">Приобрести аккаунт PRO</a></p>
<br/>
<p>Подробная информация находится в соответствующем <a href=\"{$eHost}/help/?q=1037&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=rating_freelancer\" target=\"_blank\">разделе «Помощи»</a>.</p>

<p>По всем возникающим вопросам вы можете обращаться в нашу <a href=\"{$eHost}/help/?all\" target=\"_blank\">службу поддержки</a>.</p>
<br/><p>Вы можете отключить уведомления на <a href=\"{$eHost}/users/%USER_LOGIN%/setup/mailer/\" target=\"_blank\">странице «Уведомления/Рассылка»</a> вашего аккаунта.</p>
<br/>
Приятной работы!<br/>
Команда <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>";
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
