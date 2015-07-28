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

// ищем фрилансеров у которых не было никогда ПРО
$sql = 
   "SELECT f.uid, f.login, f.uname, f.usurname, f.email
    FROM freelancer f
    LEFT JOIN orders o
        ON o.from_id = f.uid
        AND o.ordered = TRUE
        AND o.tarif IN (1,2,3,4,5,6,15,16,28,35,42,47,48,49,50,51,52,76)
    WHERE f.active = TRUE
    AND f.is_banned = B'0'
    AND substring(subscr from 8 for 1)::integer = 1
    AND o.id IS NULL
    GROUP BY f.uid
    ";

$eHost = $GLOBALS['host'];

$eSubject = "Free-lance.ru: попробуйте аккаунт PRO";

$mail = new smtp2;

$imgPro  = $mail->cid();
$imgFPro  = $mail->cid();
$img7  = $mail->cid();
$img20  = $mail->cid();
$img21  = $mail->cid();
$img22  = $mail->cid();
$img18  = $mail->cid();

$mail->attach(ABS_PATH . '/images/letter/pro.png', $imgPro);
$mail->attach(ABS_PATH . '/images/pro/fpro.gif', $imgFPro);
$mail->attach(ABS_PATH . '/images/letter/7.png', $img7);
$mail->attach(ABS_PATH . '/images/letter/20.png', $img20);
$mail->attach(ABS_PATH . '/images/letter/21.png', $img21);
$mail->attach(ABS_PATH . '/images/icons/del.gif', $img22);
$mail->attach(ABS_PATH . '/images/promo-icons/big/18.png', $img18);

ob_start(); ?>
<html>
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
                            <tr>
                                <td bgcolor="#ffffff" width="20"></td>
                                <td height="20" width="20"></td>
                                <td height="20" width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                        </table>
                        <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                            <tr>
                                <td bgcolor="#ffffff" width="20"></td>
                                <td width="20"></td>
                                <td align="left" ><font color="#000000" size="6" face="tahoma,sans-serif">Тестовый аккаунт PRO за 1 FM</font></td>
                                <td></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                        </table>
                        <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                            <tr>
                                <td bgcolor="#ffffff" height="20" width="20"></td>
                                <td width="20"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" width="20"></td>
                                <td width="20"></td>
                                <td valign="middle"><img src="cid:<?= $imgPro ?>" width="313" height="133" align="middle" border="0"></td>
                                <td align="center" width="200">
                                	<font color="#6db335" size="6" face="tahoma,sans-serif">1 FM<br></font>
									<font color="#000000" size="6" face="tahoma,sans-serif">1 неделя</font></td>
                                <td valign="middle" align="center" width="140"><img src="cid:<?= $img18 ?>" alt="" width="62" height="62" border="0" align="middle" /><br>
                                	<font color="#fd6c30" size="3" face="tahoma,sans-serif">Акция продлится<br>до 21 февраля</font>
                                </td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" width="20" height="20"></td>
                                <td width="20"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                        </table>
                        <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                            <tr>
                                <td bgcolor="#ffffff" width="20"></td>
                                <td width="20"></td>
                                <td align="left"><font color="#444444" size="2" face="tahoma,sans-serif">Обладатели аккаунтна PRO &mdash; наиболее активные пользователи Free-lance.ru с неплохими заработками. Заказчики чаще доверяют PRO &mdash; большая часть всех опубликованных на сайте проектов выполняется фрилансерами с профессиональным аккаунтом.</font></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" width="20" height="40"></td>
                                <td width="20"></td>
                                <td></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" width="20"></td>
                                <td width="20"></td>
                                <td align="left"><b><font color="#444444" size="3" face="arial,sans-serif">Некоторые преимущества аккаунта PRO</font></b></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" width="20" height="20"></td>
                                <td width="20"></td>
                                <td></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" width="20" ></td>
                                <td width="20"></td>
                                <td>
                                    <table style=" background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                              <td height="30" style="border-bottom-color:#cccccc; border-bottom-style:double; border-bottom-width:3px;"><em><font color="#444444" size="2" face="tahoma,sans-serif">Возможности</font></em></td>
                                              <td width="100" height="30" align="center" style="border-bottom-color:#cccccc; border-bottom-style:double; border-bottom-width:3px;"><em><font color="#444444" size="2" face="tahoma,sans-serif">Аккаунт <img border="0" src="cid:<?= $imgFPro ?>" width="27" height="12"></font></em></td>
                                              <td width="130" height="30" align="center" style="border-bottom-color:#cccccc; border-bottom-style:double; border-bottom-width:3px;"><em><font color="#444444" size="2" face="tahoma,sans-serif">Базовый аккаунт</font></em></td>
                                        </tr>
                                        <tr>
                                              <td height="40" style="border-bottom-color:#cccccc; border-bottom-style: solid; border-bottom-width:1px;"><font color="#444444" size="2" face="tahoma,sans-serif">Ваши контакты видны работодателям</font></em></td>
                                              <td width="100" height="40" align="center" valign="middle" style="border-bottom-color:#cccccc; border-bottom-style: solid; border-bottom-width:1px;"><img src="cid:<?= $img20 ?>" width="15" height="15" border="0"></td>
                                              <td width="130" height="40" align="center" valign="middle" style="border-bottom-color:#cccccc; border-bottom-style: solid; border-bottom-width:1px;"><img src="cid:<?= $img22 ?>" width="15" height="15" border="0"></td>
                                        </tr>
                                        <tr>
                                              <td height="40" style="border-bottom-color:#cccccc; border-bottom-style: solid; border-bottom-width:1px;"><font color="#444444" size="2" face="tahoma,sans-serif">Количество ответов на проекты (в месяц)</font></em></td>
                                              <td width="100" height="40" align="center" valign="middle" style="border-bottom-color:#cccccc; border-bottom-style: solid; border-bottom-width:1px;"><img src="cid:<?= $img21 ?>" width="16" height="10" border="0"></td>
                                              <td width="130" height="40" align="center" valign="middle" style="border-bottom-color:#cccccc; border-bottom-style: solid; border-bottom-width:1px;"><b><font color="#cc1313" size="3" face="arial,sans-serif">3</font></b></td>
                                        </tr>
                                        <tr>
                                              <td height="40" style="border-bottom-color:#cccccc; border-bottom-style: solid; border-bottom-width:1px;"><font color="#444444" size="2" face="tahoma,sans-serif">Возможность отвечать на проекты с пометкой &laquo;Только для PRO&raquo;</font></em></td>
                                              <td width="100" height="40" align="center" valign="middle" style="border-bottom-color:#cccccc; border-bottom-style: solid; border-bottom-width:1px;"><img src="cid:<?= $img20 ?>" width="15" height="15" border="0"></td>
                                              <td width="130" height="40" align="center" valign="middle" style="border-bottom-color:#cccccc; border-bottom-style: solid; border-bottom-width:1px;"><img src="cid:<?= $img22 ?>" width="15" height="15" border="0"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" width="20" height="40"></td>
                                <td width="20"></td>
                                <td></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" width="20" ></td>
                                <td width="20"></td>
                                <td align="left"><a href="<?= $eHost ?>/promo/testpro" target="_blank"><img src="cid:<?= $img7 ?>" width="231" height="36" border="0"></a></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" width="20" height="40"></td>
                                <td width="20"></td>
                                <td></td>
                                <td width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                            </tr>
                        </table>
                        <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff;" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                            <tr>
                                <td bgcolor="#ffffff" width="20"></td>
                                <td bgcolor="#ffffff" width="20"></td>
                                <td bgcolor="#ffffff" align="left">
                                    <font color="#4d4d4d" size="1" face="tahoma,sans-serif">По всем возникающим вопросам вы можете обращаться в нашу <a target="_blank" style="color:#0f71c8;" href="<?= $eHost ?>/about/feedback">службу поддержки</a>.<br>
                        Вы можете отключить уведомления на странице «<a target="_blank" style="color:#0f71c8;" href="<?= $eHost ?>/users/%%%USER_LOGIN%%%/setup/mailer/">Уведомления/Рассылка</a>» вашего аккаунта.</font>
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
                                <td bgcolor="#ffffff" align="left">
                                    <font color="#4d4d4d" size="1" face="tahoma,sans-serif">Приятной работы!<br>Команда <a href="<?= $eHost ?>" target="_blank" style="color:#0f71c8;">Free-lance.ru</a></font>
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
