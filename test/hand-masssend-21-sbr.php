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
$sql = "
SELECT 
	u.uid, u.email, u.login, u.uname, u.usurname, u.subscr
FROM
	sbr s
INNER JOIN
	employer u ON s.emp_id = u.uid AND substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'

UNION

SELECT 
	u.uid, u.email, u.login, u.uname, u.usurname, u.subscr
FROM
	sbr s
INNER JOIN
	freelancer u ON s.frl_id = u.uid AND substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'
";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "
Здравствуйте!

Команда Free-lance.ru непрерывно работает над улучшением существующих сервисов и услуг. Мы стремимся сделать работу наших пользователей на сайте более простой и комфортной. А для того чтобы знать, в каком направлении двигаться, нам просто необходимо ваше мнение. 

Вы уже пользовались сервисом http:/{«Сделка без риска»}/{$pHost}/norisk2/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_community_yes%2B<span>,</span> и наверняка у вас остались какие-то впечатления о подобном виде сотрудничества. Мы просим вас уделить несколько минут и рассказать о своем опыте работы через безопасную сделку в сообществе http:/{«Фри-ланс без риска»}/{$pHost}/commune/obuchenie/1562/fri-lans-bez-riska/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_community_yes%2B<span>.</span> Заранее благодарны!

По всем возникающим вопросам вы можете обращаться в нашу http:/{службу поддержки}/{$pHost}/help/?all<i>.</i>
Вы можете отключить уведомления на http:/{странице &laquo;Уведомления/Рассылка&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> вашего</span> аккаунта.

Приятной работы,
Команда http:/{Free-lance.ru}/{$pHost}/";


$eSubject = "Нам важно ваше мнение!";

$eMessage = "<p>Здравствуйте!</p>

<p>
Команда Free-lance.ru непрерывно работает над улучшением существующих сервисов и услуг. Мы стремимся сделать работу наших пользователей на сайте более простой и комфортной. А для того чтобы знать, в каком направлении двигаться, нам просто необходимо ваше мнение. 
</p>

<p>
Вы уже пользовались сервисом <a href='{$eHost}/norisk2/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_community_yes%2B' target='_blank'>«Сделка без риска»</a>, и наверняка у вас остались какие-то впечатления о подобном виде сотрудничества. Мы просим вас уделить несколько минут и рассказать о своем опыте работы через безопасную сделку в сообществе <a href='{$eHost}/commune/obuchenie/1562/fri-lans-bez-riska/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_community_yes%2B' target='_blank'>«Фри-ланс без риска»</a>. Заранее благодарны!
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