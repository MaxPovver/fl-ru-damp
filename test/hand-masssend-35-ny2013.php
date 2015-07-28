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

// Всем пользователям, активированным (active = true), незабаненным (is_banned = B'0'), с включенными рассылками (substring(subscr from 8 for 1)::integer = 1)

$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND active = true"; 

$eHost = $GLOBALS['host'];

$eSubject = "Поздравляем с Новым годом!";

$mail = new smtp2;

$cid1  = $mail->cid();
$cid2  = $mail->cid();

$mail->attach(ABS_PATH . '/images/letter/sneg.gif', $cid1);
//$mail->attach(ABS_PATH . '/images/letter/ng13.png', $cid2);

$eMessage = '
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <title></title>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" link="#396ea9"  bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0" style="margin:0">

<table bgcolor="#ffffff" width="100%">
    <tbody>
        <tr>
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
                    <tbody>
                        <tr>
                            <td  bgcolor="#ffffff" width="20"></td>
                            <td  width="20"></td>
                            <td width="250" align="left" ><img src="cid:'.$cid1.'" width="246" height="150"></td>
                            <td  align="left" >
                                <table style="margin-top: 0pt; margin-left: 0; margin-right: 0; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td align="left">
                                                <font color="#444444" size="2" face="tahoma,sans-serif">&mdash; Кто это там, в кустах? &mdash; поинтересовалась Алиса.</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="5"></td>
                                        </tr>
                                        <tr>
                                            <td align="left">
                                                <font color="#444444" size="2" face="tahoma,sans-serif">&mdash; Чудеса – ответил Чеширский Кот.</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="5"></td>
                                        </tr>
                                        <tr>
                                            <td align="left">
                                                <font color="#444444" size="2" face="tahoma,sans-serif">&mdash; А что они там делают? &mdash; спросила она, слегка покраснев.</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="5"></td>
                                        </tr>
                                        <tr>
                                            <td align="left">
                                                <font color="#444444" size="2" face="tahoma,sans-serif">&mdash; Как и положено чудесам &mdash; случаются.</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="5"></td>
                                        </tr>
                                        <tr>
                                            <td align="left">
                                                <font color="#444444" size="1" face="tahoma,sans-serif">&#160;&#160;&#160;&#160;<i>«Приключения Алисы в Стране чудес», Льюис Кэрролл</i></font>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td width="20"></td>
                            <td  bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td  bgcolor="#ffffff" width="20" height="10"></td>
                            <td  width="20"></td>
                            <td width="250" align="left" ></td>
                            <td  ></td>
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
                            <td  align="left" ><font color="#444444" size="3" face="tahoma,sans-serif">Вы задумывались, что для нас значит Новый год? Это всегда ожидание чуда, чего-то нового и светлого. В самом укромном уголочке сердца каждого из нас теплится надежда на перемены. И это самое главное. Надежда и вера в лучшее – вот то, что помогает нам идти вперед, двигаться, жить, любить и творить.</font></td>
                            <td width="20"></td>
                            <td  bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td  bgcolor="#ffffff" width="20" height="20"></td>
                            <td  width="20"></td>
                            <td  ></td>
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
                            <td  align="left"><a target="_blank" href="https://www.free-lance.ru/blogs/free-lanceru/722117/s-nastupayuschim.html"><img src="'.WDCPREFIX.'/images/letter/ng13.png" width="639" height="639" border="0" alt="" title=""></a></td>
                            <td width="20"></td>
                            <td  bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td  bgcolor="#ffffff" width="20" height="20"></td>
                            <td  width="20"></td>
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
                            <td  align="left" ><font color="#444444" size="3" face="tahoma,sans-serif">Команда Free-lance.ru хочет пожелать вам всего самого доброго и хорошего. Пусть в жизни будет больше приятных моментов и радости! Делайте то, что вам нравится и приносит удовольствие, повышайте свой профессиональный уровень и увеличивайте заработки. Будем расти вместе в 2013 году. Пусть он будет удачным для каждого из нас и для сообщества фри-ланса в целом! С Новым 2013 Годом!</font></td>
                            <td width="20"></td>
                            <td  bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td  bgcolor="#ffffff" width="20" height="10"></td>
                            <td  width="20"></td>
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
                            <td  align="left" ><font color="#444444" size="3" face="tahoma,sans-serif">Еще больше поздравлений &mdash; в «<a target="_blank" style="color:#0f71c8;" href="https://www.free-lance.ru/blogs/free-lanceru/722117/s-nastupayuschim.html">Блогах</a>». С наступающим!</font></td>
                            <td width="20"></td>
                            <td  bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td  bgcolor="#ffffff" width="20" height="30"></td>
                            <td  width="20"></td>
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
</tbody>
</table>
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
