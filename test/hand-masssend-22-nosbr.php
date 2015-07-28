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

//$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE login = 'jb_work'";
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND uid NOT IN (SELECT frl_id FROM sbr) AND uid NOT IN (SELECT emp_id FROM sbr)";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "
Здравствуйте!

На нашем сайте есть сервис http:/{«Сделка без риска»}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR<span>,</span> который может защитить вас от недобросовестных заказчиков в ходе сотрудничества. Работая через данный сервис, вы гарантированно получите оплату за выполненный проект и другие приятные бонусы.

Кроме того, вы можете получить гораздо больший гонорар, чем обычно. По нашей статистике, самые дорогие проекты работодатели проводят именно через «Сделку без риска»: 90% проектов с бюджетом более 100&nbsp;000&nbsp;рублей проходят именно так, а средняя стоимость проекта, работа по которому ведется через данный сервис, составляет 25&nbsp;000&nbsp;рублей. 

http:/{«Сделка без риска»}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR &ndash; это не только надежность и безопасность, но и высокий доход для фрилансера. Убедитесь сами!     

Вы можете http:/{узнать больше о сервисе}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR и почитать мнения пользователей в http:/{сообществе «Фри-ланс без риска»}/{$pHost}/commune/obuchenie/1562/fri-lans-bez-riska/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR<span>.</span>

По всем возникающим вопросам вы можете обращаться в нашу http:/{службу поддержки}/{$pHost}/help/?all<i>.</i>
Вы можете отключить уведомления на http:/{странице &laquo;Уведомления/Рассылка&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> вашего</span> аккаунта.

Приятной работы,
Команда http:/{Free-lance.ru}/{$pHost}/";


$eSubject = "А вы хотите зарабатывать много?";

$eMessage = "<p>Здравствуйте!</p>

<p>
На нашем сайте есть сервис <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR' target='_blank'>«Сделка без риска»</a>, который может защитить вас от недобросовестных заказчиков в ходе сотрудничества. Работая через данный сервис, вы гарантированно получите оплату за выполненный проект и другие приятные бонусы.
</p>

<p>
Кроме того, вы можете получить гораздо больший гонорар, чем обычно. По нашей статистике, самые дорогие проекты работодатели проводят именно через «Сделку без риска»: 90% проектов с бюджетом более 100&nbsp;000&nbsp;рублей проходят именно так, а средняя стоимость проекта, работа по которому ведется через данный сервис, составляет 25&nbsp;000&nbsp;рублей. 
</p>

<p>
<a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR' target='_blank'>«Сделка без риска»</a> &ndash; это не только надежность и безопасность, но и высокий доход для фрилансера. Убедитесь сами!     
</p>

<p>
Вы можете <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR' target='_blank'>узнать больше о сервисе</a> и почитать мнения пользователей в <a href='{$eHost}/commune/obuchenie/1562/fri-lans-bez-riska/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR' target='_blank'>сообществе «Фри-ланс без риска»</a>.  
</p>

<p>
По всем возникающим вопросам вы можете обращаться в нашу <a href='{$eHost}/help/?all' target='_blank'>службу поддержки</a>.<br/>
Вы можете отключить уведомления на <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/' target='_blank'>странице «Уведомления/Рассылка»</a> вашего аккаунта.
</p>

Приятной работы!<br/>
Команда <a href='{$eHost}' target='_blank'>Free-lance.ru</a>";


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


if ( $pMessage != '' ) {
    echo "Send personal messages\n";
    $msgid = $DB->val("SELECT masssend(?, ?, '{}', '')", $sender['uid'], $pMessage);
    if (!$msgid) {
        die("Failed!\n");
    }
    $i = 0;
    while ( $users = $master->col("{$sql} LIMIT 30000 OFFSET ?", $i) ) {
        $DB->query("SELECT masssend_bind(?, {$sender['uid']}, ?a)", $msgid, $users);
        $i = $i + 30000;
        echo "{$i} users\n";
    }
    $DB->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 
    echo "Send email messages\n";
}


if ( $eMessage != '' ) {
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