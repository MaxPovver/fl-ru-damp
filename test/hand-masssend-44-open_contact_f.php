<?php
/**
 * ”ведомление у которых еще не было никогда про, даже тестового
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp2.php';
require_once '../classes/users.php';

/**
 * Ћогин пользовател€ от кого осуществл€етс€ рассылка
 * 
 */
$sender = 'admin';

// –аботодател€м
$sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, u.subscr, usk.key AS ukey
FROM freelancer AS u
LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND is_pro = true";

//$sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, u.subscr, usk.key AS ukey FROM users AS u LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid WHERE login IN ('land_f', 'NIGGAtiff', 'DOWNshifter')"; // TEST!!

$eHost = $GLOBALS['host'];

$eSubject = "Free-lance.ru: у нас можно обмениватьс€ контактами";

$mail = new smtp2;

$img1  = $mail->cid();
$img2  = $mail->cid();
$img3  = $mail->cid();
$img4  = $mail->cid();
$img5  = $mail->cid();
$img30  = $mail->cid();

$mail->attach(ABS_PATH . '/images/letter/1.png', $img1);
$mail->attach(ABS_PATH . '/images/letter/2.png', $img2);
$mail->attach(ABS_PATH . '/images/letter/3.png', $img3);
$mail->attach(ABS_PATH . '/images/letter/4.png', $img4);
$mail->attach(ABS_PATH . '/images/letter/5.png', $img5);
$mail->attach(ABS_PATH . '/images/letter/30.png', $img30);

$link = "$eHost/gift_pro.php?utm_source=newsletter4&utm_medium=email&utm_campaign=podarok_emp&uid=%%%UID%%%";
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
        <td class="pad_null" height="20" width="20"></td>
        <td class="pad_null" height="20" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody><tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" ><font color="#000000" size="6" face="tahoma,sans-serif">«дравствуйте!</font></td>
        <td class="pad_null"></td>
        <td class="pad_null"width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody><tr>
        <td class="pad_null" bgcolor="#ffffff" height="15" width="20"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null"><font color="#000000" size="2" face="tahoma,sans-serif">Ќапоминаем, что с 5 декабр€ 2012 года обладатели аккаунта PRO могут оставл€ть свои контакты (e-mail, ICQ, Skype) в профиле, личных сообщени€х, проектах, &laquo;Ѕлогах&raquo; и других разделах сайта. </font></td>
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
</tbody>
</table>


<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
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
        <td class="pad_null" style="background:url(http://free-lance.ru/css/block/b-promo/b-promo_main-fon.gif); border-left:1px solid #e6e8e9; border-right:1px solid #e6e8e9;">
        <table style="margin-top: 0pt; text-align:left"  border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td class="pad_null" colspan="3" height="1" style="background:#d0d4d7;"  ></td>
                </tr>
                <tr>
                    <td class="pad_null" colspan="3" height="1" style="background:#e5e6e7;"  ></td>
                </tr>
                <tr>
                    <td class="pad_null"  width="20" height="20"></td>
                    <td class="pad_null"  ></td>
                    <td class="pad_null"  width="20"></td>
                </tr>
                <tr>
                    <td class="pad_null"  width="60"></td>
                    <td class="pad_null"  >
                        <b><font color="#000000" size="3" face="tahoma,sans-serif">Ќе забывайте, что контакты на Free-lance.ru открыты</font></b>
                    </td>
                    <td class="pad_null"  width="60"></td>
                </tr>
                <tr>
                    <td class="pad_null" width="20" height="30"></td>
                    <td class="pad_null"  ></td>
                    <td class="pad_null" width="20"></td>
                </tr>
                <tr>
                    <td class="pad_null"  width="20" ></td>
                    <td class="pad_null"  >
                      <table style="margin-top: 0pt; text-align:left"  border="0" cellpadding="0" cellspacing="0" width="100%">
                          <tbody>
                              <tr>
                                  <td class="pad_null" width="50" align="center"><img src="cid:<?= $img1; ?>"  border="0"></td>
                                  <td class="pad_null" width="50"></td>
                                  <td class="pad_null" width="50" align="center"><img src="cid:<?= $img2; ?>"  border="0"></td>
                                  <td class="pad_null" width="50"></td>
                                  <td class="pad_null" width="50" align="center"><img src="cid:<?= $img3; ?>"  border="0"> </td>
                                  <td class="pad_null" width="50"></td>
                                  <td class="pad_null" width="50" align="center"><img src="cid:<?= $img4; ?>"  border="0"></td>
                                  <td class="pad_null" width="40"></td>
                                  <td class="pad_null" width="80" align="center"><img src="cid:<?= $img5; ?>"  border="0"></td>
                                  <td class="pad_null"  ></td>
                              </tr>
                              <tr>
                                  <td class="pad_null" align="center">
                                    <font color="#444444" size="1" face="tahoma,sans-serif">“елефон</font>
                                  </td>
                                  <td class="pad_null" ></td>
                                  <td class="pad_null" align="center">
                                    <font color="#444444" size="1" face="tahoma,sans-serif">E-mail</font>
                                  </td>
                                  <td class="pad_null" ></td>
                                  <td class="pad_null" align="center">
                                    <font color="#444444" size="1" face="tahoma,sans-serif">ICQ</font>
                                  </td>
                                  <td class="pad_null" ></td>
                                  <td class="pad_null" align="center">
                                    <font color="#444444" size="1" face="tahoma,sans-serif">Skype</font>
                                  </td>
                                  <td class="pad_null" ></td>
                                  <td class="pad_null" align="center">
                                    <font color="#444444" size="1" face="tahoma,sans-serif">» все остальное</font>
                                  </td>
                                  <td class="pad_null" ></td>
                              </tr>
                          </tbody>
                      </table>
                    </td>
                    <td class="pad_null"  width="20"></td>
                </tr>
                <tr>
                    <td class="pad_null" width="20" height="30"></td>
                    <td class="pad_null"  ></td>
                    <td class="pad_null" width="20"></td>
                </tr>
            </tbody>
        </table>
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
</tbody>
</table>



<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
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
        <td class="pad_null"><font color="#000000" size="2" face="tahoma,sans-serif">¬ы можете размещать свои контактные данные в открытом доступе и обмениватьс€ ими с другими пользовател€ми. </font></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20" height="40"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null"><a href="<?= $eHost ?>/payed?utm_source=newsletter4&utm_medium=email&utm_campaign=open_contacts_free" target="_blank"><img src="cid:<?= $img30; ?>" width="328" height="36" border="0"></a></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20" height="40"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null"></td>
        <td class="pad_null" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff;" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff">
            <font color="#4d4d4d" size="1" face="tahoma,sans-serif">ѕо всем возникающим вопросам вы можете обращатьс€ в нашу <a target="_blank" style="color:#0f71c8;" href="https://feedback.free-lance.ru?utm_source=newsletter4&utm_medium=email&utm_campaign=open_contacts_free">службу поддержки</a>.<br>
¬ы можете отключить уведомлени€ на <a target="_blank" style="color:#0f71c8;" href="<?= $eHost ?>/unsubscribe?ukey=%%%UNSUBSCRIBE_KEY%%%&utm_source=newsletter4&utm_medium=email&utm_campaign=open_contacts_free">этой странице</a>.</font>
        </td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" height="20" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff">
            <font color="#4d4d4d" size="1" face="tahoma,sans-serif">ѕри€тной работы!<br> оманда Free-lance.ru</font>
        </td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td class="pad_null" bgcolor="#ffffff" width="20"></td>
        <td class="pad_null" bgcolor="#ffffff" height="20" width="20"></td>
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
</html>
<? $eMessage = ob_get_clean();
// ----------------------------------------------------------------------------------------------------------------
// -- –ассылка ----------------------------------------------------------------------------------------------------
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
$mail->recipient = ''; // свойство 'получатель' оставл€ем пустым
$spamid = $mail->masssend();
//if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!
// допустим нам нужно получить список получателей с какого-либо запроса
$i = 0;
$mail->recipient = array();
$DB->query("DELETE FROM week_pro_action WHERE is_emp = 't'"); //очистить таблицу логировани€ обращений за подарком (по идее только на бете нужно, но кто его знает)
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
    $DB->insert("week_pro_action", array("uid"=>$row['uid'], "is_emp"=>'t'));
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}

echo "OK. Total: {$cnt} users\n";
