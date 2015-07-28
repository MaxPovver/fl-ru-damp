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

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$eSubject = "Кому скидки от ActiveCloud?";
$eMessage = "<p>Здравствуйте!</p>
<p>Мы объявляем о начале партнерства с компанией ActiveCloud, ведущим облачным хостинг-провайдером. В честь этого события ActiveCloud дарит всем пользователям сайта Free-lance.ru скидку на хостинг в размере 40% и скидку на Облачный сервер в размере 10%.</p>
<p>Немного о компании: ActiveCloud предоставляет надежный хостинг и профессиональные облачные решения начиная с 2003 года для более чем 25 000 своих клиентов в РФ и пяти других государствах СНГ. ActiveCloud входит в группу Softline – ведущую международную компанию, специализирующуюся на лицензировании ПО и оказывающую широкий спектр различных IT-услуг.</p>
<p>Для активации скидки введите при заказе промо-код Freelance и нажмите кнопку «Применить». Надеемся, вам понравится! Более подробная информация об акции размещена на сайте 
<a href='http://www.activecloud.ru/ru/freelance/?utm_source=newsletter4&utm_medium=email&utm_campaign=activcloud' target='_blank'>ActiveCloud</a>
</p>
<p>По всем возникающим вопросам обращайтесь в нашу <a href=\"http://feedback.free-lance.ru?utm_source=newsletter4&utm_medium=email&utm_campaign=activcloud\" target=\"_blank\">службу поддержки</a>.</p>
<br/><p>Вы можете отключить уведомления на <a href=\"{$eHost}/unsubscribe/?ukey=%UNSUBSCRIBE_KEY%&utm_source=newsletter4&utm_medium=email&utm_campaign=activcloud\" target=\"_blank\">этой странице</a>.</p>

<p>Команда <a href=\"{$eHost}/?utm_source=newsletter4&utm_medium=email&utm_campaign=activcloud\" target=\"_blank\">Free-lance.ru</a>.</p>";

//Всем подписаным и незабаненым
$sql = "SELECT u.uid, email, login, uname, usurname, usk.key AS ukey 
        FROM users AS u
        LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid 
        WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' 
        LIMIT 3000 OFFSET ?";
//die($sql);
$sql = "SELECT u.uid, email, login, uname, usurname, usk.key AS ukey 
        FROM users AS u
        LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid 
        WHERE login = 'land_f'";
// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$M  = new DB('master');
$cnt = 0;

$mail = new smtp;
$mail->subject = $eSubject;  // заголовок письма
$mail->message = $eMessage; // текст письма
$mail->recipient = ''; // свойство 'получатель' оставляем пустым
$spamid = $mail->send('text/html');
if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!

$mail->recipient = array();
$i = 0;
//Отправить сообщения
while ($rows = $M->rows($sql, $i)) {
    $pm_users = array();
	foreach ($rows as $row) {
		unset( $csv_users[ $row["fromuser_id"] ] );
	    if ( strlen($row['ukey']) == 0 ) {
	        $row['ukey'] = users::writeUnsubscribeKey($row["uid"]);
	    }
	    if ( is_email($row['email']) ) {
	        $mail->recipient[] = array(
	            'email' => $row['email'],
	            'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'UNSUBSCRIBE_KEY' => $row['ukey'])
	        );
	   
	        $mail->bind($spamid);
	         $mail->recipient = array();
	   
	        $cnt++;
	    }
	    $pm_users[] = $row["uid"];
	    $i++;
	}
}
echo "OK. Total: {$cnt} users\n";
