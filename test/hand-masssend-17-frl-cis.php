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

$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE login = 'jb_work'";
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM freelancer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND country IN (2, 10, 38)";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "
Здравствуйте!

Мы проанализировали жалобы в обратную связь Free-lance.ru за последние 3 года и увидели, что 30% обращений звучат примерно так: &laquo;Заказчик пропал и не заплатил гонорар&raquo;. Единственный способ избежать такой ситуации &ndash; работать по договору через сервис http:/{&laquo;Сделка без риска&raquo;}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF.

Теперь сервис доступен для всех пользователей вне зависимости от страны проживания. Денежные средства, полученные по http:/{&laquo;Сделкам без риска&raquo;}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF<i>,</i> можно выводить не только на счет в банке, но и на счета в электронных платежных системах Яндекс.Деньги и WebMoney.

Работая через http:/{&laquo;Сделку без риска&raquo;}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF, вы не попадете в неприятные ситуации, связанные с неоплатой выполненной вами работы и внезапной &laquo;пропажей&raquo; заказчиков, необоснованными требованиями с их стороны доделать и переделать работу. Все условия сотрудничества и ТЗ будут прописаны в договоре.

После сотрудничества через сервис http:/{&laquo;Сделка без риска&raquo;}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF вы получите рекомендации от работодателей, которые повышают доверие к вам как к профессионалу. Вам больше не нужно беспокоиться о том, что вы нелегально работаете и не платите налогов, &ndash; можно смело браться за выполнение любых заказов. Кроме того, проекты, выполненные через данный сервис, значительно повышают ваш рейтинг на Free-lance.ru.

http:/{Узнать больше о сервисе &laquo;Сделка без риска&raquo;.}/{$pHost}/help/?c=41&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF

По всем возникающим вопросам вы можете обращаться в нашу http:/{службу поддержки}/{$pHost}/help/?all<i>.</i>
Вы можете отключить уведомления на http:/{странице &laquo;Уведомления/Рассылка&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> вашего</span> аккаунта.

Приятной работы,
Команда http:/{Free-lance.ru}/{$pHost}/";


$eSubject = "Работайте без риска!";

$eMessage = "<p>Здравствуйте!</p>

<p>
Мы проанализировали жалобы в обратную связь Free-lance.ru за последние 3 года и увидели, что 30% обращений звучат примерно так: &laquo;Заказчик пропал и не заплатил гонорар&raquo;. Единственный способ избежать такой ситуации &ndash; работать по договору через сервис <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>&laquo;Сделка без риска&raquo;</a>. 
</p>

<p>
Теперь сервис доступен для всех пользователей вне зависимости от страны проживания. Денежные средства, полученные по <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>&laquo;Сделкам без риска&raquo;</a>, можно выводить не только на счет в банке, но и на счета в электронных платежных системах Яндекс.Деньги и WebMoney.
</p>

<p>
Работая через <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>&laquo;Сделку без риска&raquo;</a>, вы не попадете в неприятные ситуации, связанные с неоплатой выполненной вами работы и внезапной &laquo;пропажей&raquo; заказчиков, необоснованными требованиями с их стороны доделать и переделать работу. Все условия сотрудничества и ТЗ будут прописаны в договоре.
</p>

<p>
После сотрудничества через сервис <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>&laquo;Сделка без риска&raquo;</a> вы получите рекомендации от работодателей, которые повышают доверие к вам как к профессионалу. Вам больше не нужно беспокоиться о том, что вы нелегально работаете и не платите налогов, &ndash; можно смело браться за выполнение любых заказов. Кроме того, проекты, выполненные через данный сервис, значительно повышают ваш рейтинг на Free-lance.ru.
</p>

<p>
<a href='{$eHost}/help/?c=41&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>Узнать больше о сервисе &laquo;Сделка без риска&raquo;.</a>
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