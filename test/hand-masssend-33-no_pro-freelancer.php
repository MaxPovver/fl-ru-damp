<?php
/**
 * Уведомление работодателям
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp2.php';


/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
$sender = 'admin';

// Только не pro фрилансерам, активированным (active = true), незабаненным (is_banned = B'0'), с включенными рассылками

$sql = "SELECT uid, email, login, uname, usurname, subscr FROM freelancer 
        WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND is_pro = false"; //freelancer

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$pMessage = "Здравствуйте!

С 5 декабря работодатели и исполнители с профессиональным аккаунтом могут делиться своими контактами (e-mail, ICQ, Skype и т.д.), поэтому многие фрилансеры приобретают для себя аккаунт PRO.

Без аккаунта PRO ваша контактная информация не видна работодателям, вы теряете позиции в каталоге фрилансеров и не можете отвечать на проекты с пометкой «Только для PRO». Это значит, что вы теряете потенциальных заказчиков и деньги.

Подумайте о приобретении аккаунта PRO: ваши данные будут в открытом доступе, и работодатели всегда смогут связаться с вами, даже если вы долго не заходите на сайт. Не пропустите интересные и высокооплачиваемые проекты!

{$pHttp}:/{Подробнее обо всех преимуществах аккаунта PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO

По всем возникающим вопросам вы можете обращаться в нашу {$pHttp}:/{службу поддержки}/{$pHost}/help/?all<i>.</i>
Вы можете отключить уведомления на {$pHttp}:/{странице &laquo;Уведомления/Рассылка&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> вашего</span> аккаунта.

Приятной работы,
Команда {$pHttp}:/{Free-lance.ru}/{$pHost}/
";

$eSubject = "Free-lance.ru: не сдавайте свои позиции";

$mail = new smtp2;

$cid1  = $mail->cid();
$cid2  = $mail->cid();
$cid3  = $mail->cid();

$mail->attach(ABS_PATH . '/images/letter/12.png', $cid1);
$mail->attach(ABS_PATH . '/images/letter/13.png', $cid2);
$mail->attach(ABS_PATH . '/images/letter/15.png', $cid3);

$eMessage = '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title></title>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" link="#396ea9"  bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0" style="margin:0">

<table bgcolor="#ffffff" width="100%">
<tbody><tr>
<td bgcolor="#ffffff">
<center>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody><tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  height="20" width="20"></td>
        <td  height="20" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody><tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td  align="left" ><font color="#000000" size="6" face="tahoma,sans-serif">Здравствуйте!</font></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="10"></td>
        <td  width="20"></td>
        <td colspan="2" ></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td width="602" align="left" ><font color="#444444" size="3" face="tahoma,sans-serif">С 5 декабря работодатели и исполнители с профессиональным аккаунтом могут делиться своими контактами (e-mail, icq, skype и т.д.), поэтому многие фрилансеры приобретают для себя аккаунт PRO. </font></td>
        <td width="18" rowspan="3" valign="top"  align="left"><a href="'.$eHost.'/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO" target="_blank"><img src="cid:'.$cid1.'" width="104" height="45" alt="PRO" title="PRO" border="0"></a></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="10"></td>
        <td  width="20"></td>
        <td ></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td  align="left"><font color="#444444" size="3" face="tahoma,sans-serif">Без аккаунта PRO ваша контактная информация не видна работодателям, вы теряете позиции в каталоге фрилансеров и не можете отвечать на проекты с пометкой &laquo;Только для PRO&raquo;. Это значит, что вы теряете потенциальных заказчиков и деньги.</font></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="30"></td>
        <td  width="20"></td>
        <td colspan="2" ></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td  align="left" ><img src="cid:'.$cid2.'" width="631" height="182" border="0"></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="30"></td>
        <td  width="20"></td>
        <td  ></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td  align="left" ><font color="#444444" size="3" face="tahoma,sans-serif">Подумайте о приобретении аккаунта PRO: ваши данные будут в открытом доступе, и работодатели всегда смогут связаться с вами, даже если вы долго не заходите на сайт. Не пропустите интересные и высокооплачиваемые проекты!</font></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="30"></td>
        <td  width="20"></td>
        <td  ></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td align="left"><a href="'.$eHost.'/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO" target="_blank"><img src="cid:'.$cid3.'" width="177" height="36" border="0" alt="Купить аккаунт PRO" title="Купить аккаунт PRO"></a></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="30"></td>
        <td  width="20"></td>
        <td  ></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff;" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff">
            <font color="#4d4d4d" size="1" face="tahoma,sans-serif">По всем возникающим вопросам вы можете обращаться в нашу <a target="_blank" style="color:#0f71c8;" href="'.$eHost.'/help/?all">службу поддержки</a>.<br>
Вы можете отключить уведомления на странице «<a target="_blank" style="color:#0f71c8;" href="'.$eHost.'/users/%%%USER_LOGIN%%%/setup/mailer/">Уведомления/Рассылка</a>» вашего аккаунта.</font>
        </td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" height="20" width="20"></td>
        <td  bgcolor="#ffffff"></td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff">
            <font color="#4d4d4d" size="1" face="tahoma,sans-serif">Приятной работы!<br>Команда <a target="_blank" style="color:#0f71c8;" href="'.$eHost.'">Free-lance.ru</a></font>
        </td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" height="20" width="20"></td>
        <td  bgcolor="#ffffff"></td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
</tbody>
</table>

</center>
</td>
</tr>
</tbody></table>

</body>
</html>';

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

$mail->subject = $eSubject;  // заголовок письма
$mail->message = $eMessage; // текст письма
$mail->recipient = ''; // свойство 'получатель' оставляем пустым
$spamid = $mail->masssend();
if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!
// допустим нам нужно получить список получателей с какого-либо запроса
$i = 0;
$mail->recipient = array();
$res = $master->query($sql);
while ($row = pg_fetch_assoc($res)) {
    $mail->recipient[] = array(
        'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
        'extra' => array('USER_LOGIN' => $row['login'])
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
