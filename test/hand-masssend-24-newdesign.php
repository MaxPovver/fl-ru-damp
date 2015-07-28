<?php

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

// Всем пользователям, активированным (active = true), незабаненным (is_banned = B'0'), с включенными рассылками (substring(subscr from 8 for 1)::integer = 1)
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND active = true"; 

$pHost = str_replace("http://", "", $GLOBALS['host']);
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$eHost = $GLOBALS['host'];


$eSubject = "Обновленный дизайн и дополнительные сервисы Free-lance.ru";

$eMessage = "<p>Здравствуйте!</p>

<p>
Спешим рассказать о том, что мы обновили дизайн главной страницы сайта и верхнего личного поля пользователя. Кроме того, у нас появился обучающий мастер, который поможет новым пользователям зарегистрироваться и проведет небольшую «экскурсию» по основным сервисам сайта. Более подробно обо всех обновлениях читайте в «<a href='{$pHttp}://www.free-lance.ru/blogs/free-lanceru/704501/obnovleniya-na-sayte.html?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_desigh' target='_blank'>Блогах</a>».
</p>

<p>
<a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_desig' target='_blank'>Перейти на сайт и все увидеть своими глазами!</a>
</p>

<p>
По всем возникающим вопросам вы можете обращаться в нашу <a href='{$eHost}/help/?all&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_design' target='_blank'>службу поддержки</a>.<br/>
Вы можете отключить уведомления на <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_design' target='_blank'>странице «Уведомления/Рассылка»</a> вашего аккаунта.
</p>

Приятной работы!<br/>
Команда <a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_design' target='_blank'>Free-lance.ru</a>";

// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('plproxy');
$master = new DB('master');

if ( $eMessage != '' ) {
    echo "Send email messages\n";
    $mail = new smtp;
    $mail->subject   = $eSubject;
    $mail->message   = $eMessage;
    $mail->recipient = '';
    $spamid = $mail->send('text/html');
    if ( !$spamid ) {
        die("Failed!\n");
    }

    $i = 0;
    $c = 0;
    $mail->recipient = array();
    $res = $master->query($sql);
    while ($row = pg_fetch_assoc($res)) {
        $mail->recipient[] = array(
            'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
            'extra' => array('USER_NAME' => $row['uname'], 'USER_SURNAME' => $row['usurname'], 'USER_LOGIN' => $row['login'])
        );
        if (++$i >= 30000) {
            $mail->bind($spamid);
            $mail->recipient = array();
            $i = 0;
            echo "{$c} users\n";
        }
        $c++;
    }
    if ($i) {
        $mail->bind($spamid);
        $mail->recipient = array();
    }
}

echo "OK. Total: {$c} users\n";