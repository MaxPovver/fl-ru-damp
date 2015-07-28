<?php
/**
 * Уведомление у которых еще не было никогда про, даже тестового
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp2.php';
require_once '../classes/users.php';

/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
$sender = 'admin';

// Работодателям
$sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, u.subscr, usk.key AS ukey
FROM users AS u
LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND (NOW() - last_time < '1 year')";

if ($_GET['test'] == 1) {
    $sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, u.subscr, usk.key AS ukey FROM users AS u LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid WHERE login IN ('land_f', 'land_e2', 'bolvan')"; // TEST!!
}


if ($_GET['to'] != null) {
    $sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, u.subscr, usk.key AS ukey FROM users AS u LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid WHERE email = '{$_GET['to']}'"; // TEST!!
}

$eHost = $GLOBALS['host'];

$eSubject = "Безопасная Сделка: теперь еще лучше";

$mail = new smtp2;

$img1  = $mail->cid();
$img2  = $mail->cid();
$img3  = $mail->cid();
$img4  = $mail->cid();
$img5  = $mail->cid();
$img6  = $mail->cid();

$mail->attach(ABS_PATH . '/images/letter/160720131.png', $img1);
$mail->attach(ABS_PATH . '/images/letter/170720131.gif', $img2);
$mail->attach(ABS_PATH . '/images/letter/160720133.png', $img3);
$mail->attach(ABS_PATH . '/images/letter/160720134.png', $img4);
$mail->attach(ABS_PATH . '/images/letter/160720135.png', $img5);
$mail->attach(ABS_PATH . '/images/letter/160720136.png', $img6);

ob_start(); ?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title></title>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" link="#396ea9"  bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0" style="margin:0">

<table bgcolor="#ffffff" width="100%">
<tbody><tr>
<td bgcolor="#ffffff">
<center>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody><tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" height="90" width="20"></td>
        <td class="pad_null" height="90" valign="top"><a href="https://free-lance.ru?utm_source=newsletter4&utm_medium=email&utm_campaign=bs_upd1" target="_blank"><img width="136" height="20" src="cid:<?= $img1; ?>" alt="" border="0" style="margin-top:27px;"></a></td>
        <td class="pad_null" height="90"></td>
        <td class="pad_null" height="90" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody><tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" >
        	<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%"><tr>
        	<td width="100"><img width="85" height="91" src="cid:<?= $img2; ?>" alt="" border="0" style="position:relative;top:7px;left:-2px;"></td>
        	<td>
			<span style="color:#000000;font-family:Arial, Tahoma, Sans-serif;font-size:34px;">Безопасная сделка</span><br />
        	<span style="color:#74bb54;font-family:Arial, Tahoma, Sans-serif;font-size:22px;">становится лучше</span>        				
        	</td>
        	</tr>
        	</table>
        </td>
        <td class="pad_null"></td>
        <td class="pad_null"width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>

<table style="margin-top: 20px; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody><tr>
        <td class="pad_null" bgcolor="#ffffff" height="10" width="20"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;">
        
            <p><b>Здравствуйте!</b></p>
            <p style="line-height:140%;">Наша команда непрерывно работает над улучшением ключевых сервисов сайта для того, чтобы вам было проще и удобнее ими пользоваться. Хотим представить вам позитивные изменения по Безопасной Сделке, которые были сделаны в последнее время.</p>
            
<table width="100%" border="0" style="margin-top:36px;">
    <tr valign="top">
        <td width="140"><img width="134" height="133" src="cid:<?= $img3; ?>" alt="" border="0" align="left"></td>
        <td>
            <p style="font-size:15px;color:#000000;font-family:Arial, Tahoma, Sans-serif;padding-left:20px;"><b>Начало сотрудничества</b></p>
			<table cellpadding="0" cellspacing="0" style="width:100%;margin-top:5px;text-align:left">
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif; vertical-align:top">Новая промо-страница сервиса Безопасная Сделка с кратким описанием возможностей.</span></td>
			</tr>
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Обязательное ознакомление с Договором аккредитива перед началом активной работы.</span></td>
			</tr>
			</table>
        </td>
    </tr>
    <tr><td colspan="2" height="55">&nbsp;</td></tr>
    <tr valign="top">
        <td width="140"><img width="134" height="133" src="cid:<?= $img4; ?>" alt="" border="0" align="left"></td>
        <td>
            <p style="font-size:15px;color:#000000;font-family:Arial, Tahoma, Sans-serif;padding-left:20px;"><b>Этап согласования</b></p>
			<table cellpadding="0" cellspacing="0" style="width:100%;margin-top:5px;text-align:left">
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Повышение минимальной суммы сделки до 300 рублей в целях уменьшения демпинга.</span></td>
			</tr>
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Привязка банковской карты к Веб-кошельку непосредственно на нашем сайте, без перехода на сторонние ресурсы.</span></td>
			</tr>
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Изменения в интерфейсе окна «Идет оплата…»: стало более информативным с подробными инструкциями по действиям, которые нужно совершить заказчику.</span></td>
			</tr>
			</table>
        </td>
    </tr>
    <tr><td colspan="2" height="55">&nbsp;</td></tr>
    <tr valign="top">
        <td width="140"><img width="134" height="133" src="cid:<?= $img5; ?>" alt="" border="0" align="left"></td>
        <td>
            <p style="font-size:15px;color:#000000;font-family:Arial, Tahoma, Sans-serif;padding-left:20px;"><b>Сделка в работе</b></p>
			<table cellpadding="0" cellspacing="0" style="width:100%;margin-top:5px;text-align:left">
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Добавили возможность настраивать расположение формы комментирования так, как это удобно пользователю &mdash; сверху или снизу в Безопасной Сделке, а также менять порядок отображения сообщений/комментариев.</span></td>
			</tr>
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Упорядочили уведомления при изменениях: теперь можно посмотреть, на какие именно коррективы по сделке соглашается вторая сторона.</span></td>
			</tr>
			</table>
        </td>
    </tr>
    <tr><td colspan="2" height="55">&nbsp;</td></tr>
    <tr valign="top">
        <td width="140"><img width="134" height="133" src="cid:<?= $img6; ?>" alt="" border="0" align="left"></td>
        <td>
            <p style="font-size:15px;color:#000000;font-family:Arial, Tahoma, Sans-serif;padding-left:20px;"><b>Завершение Безопасной Сделки </b></p>
			<table cellpadding="0" cellspacing="0" style="width:100%;margin-top:5px;text-align:left">
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Появилась возможность писать ответ на оставленный вам отзыв по результатам сотрудничества через БС.</span></td>
			</tr>
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Юридическим лицам отправляется информация о том, когда и каким образом будут предоставлены закрывающие документы.</span></td>
			</tr>
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Срок Арбитража теперь рассчитывается в рабочих днях, а обращение в Арбитраж можно подавать не позднее чем за 2 рабочих дня до закрытия аккредитива.</span></td>
			</tr>
			<tr valign="top">
				<td width="20" style="color:#74bb54;font-size:25px;font-family:Arial, Tahoma, Sans-serif;line-height:80%;">&#8226;</td>
				<td style="padding-bottom:11px;"><span style="font-family:Arial, Tahoma, Sans-serif;font-size:15px;color:#000000;line-height:130%;font-family:Arial, Tahoma, Sans-serif;">Сделаны уведомления для заказчиков о сроках возврата средств после Арбитража (с указанием точной даты).</span></td>
			</tr>
			</table>
        </td>
    </tr>
    <tr><td colspan="2" height="50">&nbsp;</td></tr>
</table>

<p style="font-size:15px;line-height:140%;color:#000000;font-family:Arial, Tahoma, Sans-serif;">Надеемся, эти улучшения сделают ваше сотрудничество в Безопасной Сделке проще и удобнее. Будем рады услышать ваши отзывы и предложения в нашем <a href="https://feedback.free-lance.ru/?utm_source=newsletter4&utm_medium=email&utm_campaign=bs_upd1" target="_blank" style="color:#0f71c8;">сообществе поддержки</a>.</p>

<p style="padding-top:25px;font-size:15px;color:#000000;font-family:Arial, Tahoma, Sans-serif;font-style: italic;">Приятной работы с Free-lance.ru!</p>

        </td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20" height="20"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>

    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20" height="1"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null"><hr size="1" color="#f1f1f1"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
</tbody>
</table><table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff;text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" style="text-align:left">
            <font color="#999999" size="1" face="tahoma,sans-serif">Вы можете отписаться от рассылки новостей на <a target="_blank" style="color:#0f71c8;" href="<?= $eHost ?>/unsubscribe?ukey=%%%UNSUBSCRIBE_KEY%%%&utm_source=newsletter4&utm_medium=email&utm_campaign=bs_upd1">этой странице</a>.</font>
        </td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" height="30" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
</tbody>
</table>

</center>
</td>
</tr>
</tbody></table>

</body>
</html><? $eMessage = ob_get_clean();
// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('master');
$cnt = 0;

$sender = $DB->row("SELECT * FROM users WHERE login = ?", $sender);
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
$res = $DB->query($sql);
while ($row = pg_fetch_assoc($res)) {
    if ( strlen($row['ukey']) == 0 ) {
        $row['ukey'] = users :: writeUnsubscribeKey($row['uid']);
    }
    $mail->recipient[] = array(
        'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
        'extra' => array('USER_LOGIN' => $row['login'], 'UID' => $row['uid'], 'UNSUBSCRIBE_KEY' => $row["ukey"])
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
