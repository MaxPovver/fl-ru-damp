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

$eSubject = "Вы не оставили отзыв в сделке";

$eMessage = "<p>Здравствуйте, %USER_LOGIN%!</p>

<p>
Напоминаем вам, что до %DATE_SBR% необходимо оставить отзыв в Безопасной сделке БС-%SBR_ID%. После указанной даты возможность публикации отзыва будет закрыта.
</p>

<p>
Информацию о публикации отзывов и проведении сделок, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='https://feedback.fl.ru/knowledgebase/category/id/31' target='_blank'>сообществе поддержки</a>.
</p>

<p>
Вы можете отключить уведомления <a href='{$eHost}/unsubscribe?ukey=%UNSUBSCRIBE_KEY%' target='_blank'>на этой странице</a>.
</p>

Приятной работы с <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>!";

// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('master');
$cnt = 0;



$limit = 8000;  
$sql = "
SELECT u.uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey, ss.closed_time as closed, ss.sbr_id, ss.closed_time + interval '10 days' as done_feedback
FROM  sbr s
INNER join sbr_stages ss ON ss.sbr_id = s.id
INNER join employer u on u.uid = s.emp_id
LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
WHERE ss.status IN(4,7) AND ss.emp_feedback_id IS NULL AND ss.closed_time + interval '10 days' > NOW();
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
                         'SBR_ID' => $row['sbr_id'],
                         'DATE_SBR' => date('d.m.Y', strtotime($row['closed'])),
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