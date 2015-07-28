<?php
/**
 * Уведомление у которых еще не было никогда про, даже тестового
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

// Работодателям
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM employer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND ( NOW() - last_time ) > '1 year'";

//$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE login = 'land_f'"; // TEST!!

$eHost = $GLOBALS['host'];

$eSubject = "Free-lance: мы дарим вам профессиональный аккаунт";

$mail = new smtp2;

$img17  = $mail->cid();
$img6  = $mail->cid();
$img29  = $mail->cid();

$mail->attach(ABS_PATH . '/images/letter/28.png', $img17);
$mail->attach(ABS_PATH . '/images/letter/6.png', $img6);
$mail->attach(ABS_PATH . '/images/letter/29.png', $img29);

$link = "$eHost/gift_pro.php?utm_source=newsletter4&utm_medium=email&utm_campaign=podarok_emp&uid=%%%UID%%%";
ob_start(); ?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title></title>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" link="#396ea9"  bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0" style="margin:0">

<table bgcolor="#ffffff" width="100%">
<tr>
<td bgcolor="#ffffff">
<center>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody><tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td height="20" width="20"></td>
        <td height="20" width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td width="20"></td>
        <td ><font color="#000000" size="6" face="tahoma,sans-serif">Здравствуйте!</font></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="20"></td>
        <td width="20"></td>
        <td ></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td width="20"></td>
        <td ><font color="#000000" size="2" face="tahoma,sans-serif">Мы заметили, что вы давно не заходили на Free-lance.ru. Тем временем наш сайт растет и обновляется. Аудитория ресурса превышает 1,3 млн. зарегистрированных пользователей, а количество публикуемых проектов в месяц достигло 36 000.</font></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="10"></td>
        <td width="20"></td>
        <td ></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td width="20"></td>
        <td ><font color="#000000" size="2" face="tahoma,sans-serif">Воспользуйтесь нашим подарком &mdash; аккаунтом PRO на месяц.</font></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="20"></td>
        <td width="20"></td>
        <td ></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody><tr>
        <td bgcolor="#ffffff" height="20" width="20"></td>
        <td width="20"></td>
        <td></td>
        <td valign="middle"></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td width="20"></td>
        <td valign="middle" width="370"><a href="<?=$link ?>" target="_blank"><img src="cid:<?= $img17; ?>" width="312" height="132" align="middle" border="0"></a></td>
        <td valign="middle"><font color="#65ac2b" size="6" face="tahoma,sans-serif">Бесплатно</font><br> <font color="#000000" size="6" face="tahoma,sans-serif">&#160;на месяц</font></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40"></td>
        <td width="20"></td>
        <td valign="middle"></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
  </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td width="20"></td>
        <td><font color="#000000" size="2" face="tahoma,sans-serif">Чтобы получить подарок, перейдите по <a href="<?=$link ?>" target="_blank" style="color:#0f71c8">этой ссылке</a>. Срок активации &mdash; 7 дней. Срок действия подарка &mdash; <br>месяц.</font></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40"></td>
        <td width="20"></td>
        <td ></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td width="20"></td>
        <td ><b><font color="#000000" size="3" face="tahoma,sans-serif">Профессиональный аккаунт для работодателя позволяет:</font></b></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>


<table width="740" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left">
    <tbody>
    <tr>
        <td width="40" height="10" bgcolor="#ffffff"></td>
        <td width="20"></td>
        <td></td>
        <td width="20"></td>
        <td width="20" bgcolor="#ffffff"></td>
  </tr>
    <tr>
        <td width="40" bgcolor="#ffffff"></td>
        <td width="25" valign="middle"><img width="15" height="15" border="0" src="cid:<?=$img6 ?>"></td>
        <td valign="top"><font size="2" face="tahoma,sans-serif" color="#000000">Напрямую связаться с интересующими исполнителями.</font></td>
        <td width="20"></td>
        <td width="20" bgcolor="#ffffff"></td>
  </tr>
    <tr>
        <td width="40" height="10" bgcolor="#ffffff"></td>
        <td width="20"></td>
        <td></td>
        <td width="20"></td>
        <td width="20" bgcolor="#ffffff"></td>
  </tr>
    <tr>
        <td width="40" bgcolor="#ffffff"></td>
        <td width="25" valign="middle"><img width="15" height="15" border="0" src="cid:<?=$img6 ?>"></td>
        <td valign="top"><font size="2" face="tahoma,sans-serif" color="#000000">Публиковать развернутую информацию о компании.</font></td>
        <td width="20"></td>
        <td width="20" bgcolor="#ffffff"></td>
  </tr>
    <tr>
        <td width="40" height="10" bgcolor="#ffffff"></td>
        <td width="20"></td>
        <td></td>
        <td width="20"></td>
        <td width="20" bgcolor="#ffffff"></td>
  </tr>
    <tr>
        <td width="40" bgcolor="#ffffff"></td>
        <td width="25" valign="middle"><img width="15" height="15" border="0" src="cid:<?=$img6 ?>"></td>
        <td valign="top"><font size="2" face="tahoma,sans-serif" color="#000000">Размещать рекламу и конкурсы на льготных условиях.</font></td>
        <td width="20"></td>
        <td width="20" bgcolor="#ffffff"></td>
  </tr>
    <tr>
        <td width="40" height="10" bgcolor="#ffffff"></td>
        <td width="20"></td>
        <td></td>
        <td width="20"></td>
        <td width="20" bgcolor="#ffffff"></td>
  </tr>
    <tr>
        <td width="40" bgcolor="#ffffff"></td>
        <td width="25" valign="middle">&#160;</td>
        <td valign="top"><font size="2" face="tahoma,sans-serif" color="#000000">И многое другое.</font></td>
        <td width="20"></td>
        <td width="20" bgcolor="#ffffff"></td>
  </tr>
    <tr>
        <td width="40" height="30" bgcolor="#ffffff"></td>
        <td width="20"></td>
        <td></td>
        <td width="20"></td>
        <td width="20" bgcolor="#ffffff"></td>
  </tr>
</tbody>
</table>


<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td width="20"></td>
        <td ><font color="#000000" size="2" face="tahoma,sans-serif">Воспользуйтесь бесплатно возможностями профессионального аккаунта. Более 15 000 работодателей уже используют аккаунт PRO.</font></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40"></td>
        <td width="20"></td>
        <td ></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td width="20"></td>
        <td ><a href="<?=$link ?>" target="_blank"><img src="cid:<?=$img29 ?>" width="198" height="36" border="0"></a></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40"></td>
        <td width="20"></td>
        <td ></td>
        <td></td>
        <td width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left;" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff">
            <font color="#4d4d4d" size="1" face="tahoma,sans-serif">По всем возникающим вопросам вы можете обращаться в нашу <a target="_blank" style="color:#0f71c8;" href="<?= $eHost ?>/about/feedback/?utm_source=newsletter4&utm_medium=email&utm_campaign=podarok_emp">службу поддержки</a>.<br>
Вы можете отключить уведомления на странице «<a target="_blank" style="color:#0f71c8;" href="<?= $eHost ?>/users/%%%USER_LOGIN%%%/setup/mailer/?utm_source=newsletter4&utm_medium=email&utm_campaign=podarok_emp">Уведомления/Рассылка</a>» вашего аккаунта.</font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff" height="20" width="20"></td>
        <td bgcolor="#ffffff"></td>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff">
            <font color="#4d4d4d" size="1" face="tahoma,sans-serif">Приятной работы!<br>Команда <a target="_blank" style="color:#0f71c8;" href="<?= $eHost ?>/?utm_source=newsletter4&utm_medium=email&utm_campaign=podarok_emp">Free-lance.ru</a></font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff" height="20" width="20"></td>
        <td bgcolor="#ffffff"></td>
        <td bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
  </tr>
</tbody>
</table>
</center>
</td>
</tr>
</table>

</body>
</html>
<? $eMessage = ob_get_clean();
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

$mail->subject = $eSubject;  // заголовок письма
$mail->message = $eMessage; // текст письма
$mail->recipient = ''; // свойство 'получатель' оставляем пустым
$spamid = $mail->masssend();
//if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!
// допустим нам нужно получить список получателей с какого-либо запроса
$i = 0;
$mail->recipient = array();
$master->query("DELETE FROM week_pro_action WHERE is_emp = 't'"); //очистить таблицу логирования обращений за подарком (по идее только на бете нужно, но кто его знает)
$res = $master->query($sql);
while ($row = pg_fetch_assoc($res)) {
    $mail->recipient[] = array(
        'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
        'extra' => array('USER_LOGIN' => $row['login'], 'UID' => $row['uid'])
    );
    if (++$i >= 30000) {
        $mail->bind($spamid);
        $mail->recipient = array();
        $i = 0;
    }
    $master->insert("week_pro_action", array("uid"=>$row['uid'], "is_emp"=>'t'));
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}

echo "OK. Total: {$cnt} users\n";
