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
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND country = 1";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "
Здравствуйте!

Всем нашим пользователям-резидентам РФ теперь доступно пополнение http:/{личного счета}/{$pHost}/help/?c=32&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=Evroset_svyaznoi в магазинах &laquo;Связной&raquo; и &laquo;Евросеть&raquo;: вам достаточно найти любую точку продаж или платежный терминал в своем городе и внести необходимое количество средств. Платежи проходят круглосуточно в режиме онлайн, служба поддержки клиентов также работает 24 часа в сутки и без выходных. 

Вы можете ознакомиться с подробными инструкциями по пополнению счета на Free-lance.ru через сети магазинов &laquo;http:/{Связной}/help/?q=1010&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=Evroset_svyaznoi<span>&raquo;</span> и &laquo;http:/{Евросеть}/help/?q=1009&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=Evroset_svyaznoi<span>&raquo;</span>.

По всем возникающим вопросам вы можете обращаться в нашу http:/{службу поддержки}/{$pHost}/help/?all<i>.</i>
Вы можете отключить уведомления на http:/{странице &laquo;Уведомления/Рассылка&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> вашего</span> аккаунта.

Приятной работы,
Команда http:/{Free-lance.ru}/{$pHost}/";


$eSubject = "Обновления на сайте";

$eMessage = "<p>Здравствуйте!</p>

<p>
Всем нашим пользователям-резидентам РФ теперь доступно пополнение <a href='{$eHost}/help/?c=32&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=Evroset_svyaznoi'>личного счета</a> в магазинах &laquo;Связной&raquo; и &laquo;Евросеть&raquo;: вам достаточно найти любую точку продаж или платежный терминал в своем городе и внести необходимое количество средств. Платежи проходят круглосуточно в режиме онлайн, служба поддержки клиентов также работает 24 часа в сутки и без выходных. 
</p>

<p>
Вы можете ознакомиться с подробными инструкциями по пополнению счета на Free-lance.ru через сети магазинов &laquo;<a href='{$eHost}/help/?q=1010&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=Evroset_svyaznoi'>Связной</a>&raquo; и &laquo;<a href='{$eHost}/help/?q=1009&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=Evroset_svyaznoi'>Евросеть</a>&raquo;.
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