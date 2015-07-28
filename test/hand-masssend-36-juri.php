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

// Рассылаем всем юрлицам (исполнителям и заказчикам) с аккредитивами и не заполненным юр адресом
$sql = "
SELECT u.uid, u.email, u.login, u.uname, u.usurname
FROM sbr_reqv r 
INNER JOIN users u ON u.uid = r.user_id
WHERE 
u.is_banned = B'0'
AND r.form_type = 2 
AND (r._2_address_jry IS NULL OR r._2_address_jry = '')
AND ( 
  (SELECT id FROM sbr s WHERE s.emp_id = r.user_id AND s.scheme_type = 4 LIMIT 1) IS NOT NULL 
    OR
  (SELECT id FROM sbr s WHERE s.frl_id = r.user_id AND s.scheme_type = 4 LIMIT 1) IS NOT NULL 
)"; 

$eHost = $GLOBALS['host'];

$eSubject = "Free-lance.ru: Вы не указали обязательную информацию на странице «Финансы»";

$eMessage = "<p>Здравствуйте!</p>

<p>Мы заметили, что вы не указали свой юридический адрес в профиле. Эта информация является очень важной и используется при формировании закрывающих документов для бухгалтерии. Убедительно просим вас заполнить поле «Юридический адрес» на странице «Финансы» до 20 января 2013 года.</p>

<p>Напоминаем вам, что для полноценной работы через сервис «Сделка Без Риска» необходимо заполнить все обязательные поля на странице «Финансы». Подробная инструкция находится в <a href=\"{$eHost}/help/?q=1034\" target=\"_blank\">этом</a> разделе «Помощи».</p><br/>

<p>По всем возникающим вопросам вы можете обращаться в нашу <a href=\"{$eHost}/help/?all\" target=\"_blank\">службу поддержки</a>.</p>
<p>Вы можете отключить уведомления на <a href=\"{$eHost}/users/%USER_LOGIN%/setup/mailer/\" target=\"_blank\">странице «Уведомления/Рассылка»</a> вашего аккаунта.</p>
<br/>
Приятной работы!<br/>
Команда <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>";

// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$master = new DB('master');
$cnt = 0;

$sender = $master->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}

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
