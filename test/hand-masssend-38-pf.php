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
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM employer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'";

$eHost = $GLOBALS['host'];

$eSubject = "Free-lance.ru: Отдохните, а мы поработаем за вас";

$mail = new smtp2;

$img17  = $mail->cid();
$img16  = $mail->cid();

$mail->attach(ABS_PATH . '/images/letter/17.png', $img17);
$mail->attach(ABS_PATH . '/images/letter/16.png', $img16);

ob_start(); ?>
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
                            <td  ></td>
                            <td width="140" align="right"></td>
                            <td  width="20"></td>
                            <td  bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td  bgcolor="#ffffff" width="20"></td>
                            <td  width="20"></td>
                            <td  align="left" ><font color="#444444" size="2" face="tahoma,sans-serif">Для удобства работодателей мы создали сервис &laquo;<strong>Подбор фрилансеров</strong>&raquo;. Если у вас нет лишнего времени или возникли трудности с поиском исполнителей, наши квалифицированные менеджеры возьмут все ваши заботы на себя. А вы сможете заняться другими, более важными делами. </font></td>
                            <td width="140" rowspan="3" align="right" valign="top"><img src="cid:<?= $img17; ?>" width="121" height="102" border="0"></td>
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
                            <td  align="left"><font color="#444444" size="2" face="tahoma,sans-serif">Нам требуется только подробно заполненная вами <strong>заявка</strong> с указанием всех требований к кандидату. Наши менеджеры обладают большим опытом в подборе фрилансеров – мы находим профессиональных исполнителей даже на самые сложные проекты.</font></td>
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
                            <td  align="left"><font color="#444444" size="2" face="tahoma,sans-serif">&laquo;Подбор фрилансеров&raquo; – это залог успешного сотрудничества.</font></td>
                            <td  width="20"></td>
                            <td  bgcolor="#ffffff" width="20"></td>
                        </tr>
                    </tbody>
                </table>

                <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
                    <tbody>
                        <tr>
                            <td  bgcolor="#ffffff" width="20" height="30"></td>
                            <td  width="20"></td>
                            <td colspan="2"  ></td>
                            <td width="20"></td>
                            <td  bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td  bgcolor="#ffffff" width="20"></td>
                            <td  width="20"></td>
                            <td  colspan="2" align="left"><a href="<?= $eHost ?>/manager/?utm_source=newsletter4&utm_medium=email&utm_campaign=PF" target="_blank"><img src="cid:<?= $img16; ?>" width="255" height="36" border="0" alt="Заказать подбор фрилансеров" title="Заказать подбор фрилансеров"></a></td>
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
                
                <table style="text-align:left; margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff;" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
                    <tbody>
                        <tr>
                            <td  bgcolor="#ffffff" width="20"></td>
                            <td  bgcolor="#ffffff" width="20"></td>
                            <td bgcolor="#ffffff">
                                <font color="#4d4d4d" size="1" face="tahoma,sans-serif">По всем возникающим вопросам вы можете обращаться в нашу <a target="_blank" style="color:#0f71c8;" href="<?= $eHost ?>/about/feedback/?utm_source=newsletter4&utm_medium=email&utm_campaign=PF">службу поддержки</a>.<br>
                    Вы можете отключить уведомления на странице «<a target="_blank" style="color:#0f71c8;" href="<?= $eHost ?>/users/%%%USER_LOGIN%%%/setup/mailer/?utm_source=newsletter4&utm_medium=email&utm_campaign=PF">Уведомления/Рассылка</a>» вашего аккаунта.</font>
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
                                <font color="#4d4d4d" size="1" face="tahoma,sans-serif">Приятной работы!<br>Команда Free-lance.ru</font>
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
