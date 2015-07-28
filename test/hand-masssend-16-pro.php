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
//http://www.free-lance.ru/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended
// Только фрилансерам
$sql = "SELECT f.uid, f.email, f.login, f.uname, f.usurname, f.subscr
            FROM freelancer f 
            WHERE is_banned = B'0' AND substr(f.subscr::text,8,1) = '1'
            AND is_pro = false";

$pHost = str_replace("https://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "Здравствуйте!

Фри-ланс – это не только свободный график и возможность выбора, но и постоянная атмосфера конкуренции. На нашем ресурсе зарегистрировано большое количество фрилансеров, которые ежедневно ищут для себя работу. Для того чтобы вы могли выгодно отличаться от конкурентов, на сайте существует https:/{аккаунт PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended.

С https:/{аккаунтом PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended вы сможете безлимитно отвечать на проекты, получите возможность отвечать на проекты только для PRO, увеличенный рейтинг, полноценное портфолио и многое другое. В дополнение к этому теперь вы станете еще заметнее для заказчиков: обладатели профессионального аккаунта показываются работодателям в проектах в качестве рекомендованных исполнителей. Фрилансеры выбираются в случайном порядке из того раздела https:/{каталога}/{$pHost}/freelancers/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended, в котором опубликован проект.

Теперь выгодные и интересные заказы найдут вас сами – вам нужно лишь следить за своевременным обновлением срока действия https:/{своего аккаунта PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended!

Как https:/{получить аккаунт PRO}/{$pHost}/help/?q=789&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended<span>?</span>

По всем возникающим вопросам вы можете обращаться в нашу https:/{службу поддержки}/{$pHost}/help/?all.
Вы можете отключить уведомления на https:/{странице «Уведомления/Рассылка»}/{$pHost}/users/%USER_LOGIN%/setup/mailer/ вашего аккаунта.

Приятной работы,
Команда https:/{Free-lance.ru}/{$pHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended";


$eSubject = "Теперь вы станете еще заметнее для работодателей на Free-lance.ru";

$eMessage = "<p>Здравствуйте!</p>

<p>
Фри-ланс – это не только свободный график и возможность выбора, но и постоянная атмосфера конкуренции. На нашем ресурсе зарегистрировано большое количество фрилансеров, которые ежедневно ищут для себя работу. Для того чтобы вы могли выгодно отличаться от конкурентов, на сайте существует <a href='{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>аккаунт PRO</a>.
</p>

<p>
С <a href='{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>аккаунтом PRO</a> вы сможете безлимитно отвечать на проекты, получите возможность отвечать на проекты только для PRO, увеличенный рейтинг, полноценное портфолио и многое другое. В дополнение к этому теперь вы станете еще заметнее для заказчиков: обладатели профессионального аккаунта показываются работодателям в проектах в качестве рекомендованных исполнителей. Фрилансеры выбираются в случайном порядке из того раздела <a href='{$eHost}/freelancers/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>каталога</a>, в котором опубликован проект.
</p>

<p>
Теперь выгодные и интересные заказы найдут вас сами – вам нужно лишь следить за своевременным обновлением срока действия <a href='{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>своего аккаунта PRO</a>!
</p>

<p>
Как <a href='{$eHost}/help/?q=789&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>получить аккаунт PRO</a>?
</p>

<p>
По всем возникающим вопросам вы можете обращаться в нашу <a href='{$eHost}/help/' target='_blank'>службу поддержки</a>.<br/>
Вы можете отключить уведомления <a href='{$eHost}/unsubscribe?ukey=%UNSUBSCRIBE_KEY%' target='_blank'>на этой странице</a> вашего аккаунта.
</p>

Приятной работы!<br/>
Команда <a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>Free-lance.ru</a>";

// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$plproxy = new DB('plproxy');
$DB = new DB('master');
$cnt = 0;


$sender = $DB->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}

echo "Send personal messages\n";

// подготавливаем рассылку
$msgid = $plproxy->val("SELECT masssend(?, ?, '{}', '')", $sender['uid'], $pMessage);
if (!$msgid) die('Failed!');

// допустим, мы получаем адресатов с какого-то запроса
$i = 0;
while ($users = $DB->col("{$sql} LIMIT 5000 OFFSET ?", $i)) {
    $plproxy->query("SELECT masssend_bind(?, {$sender['uid']}, ?a)", $msgid, $users);
    $i = $i + 5000;
}
// Стартуем рассылку в личку
$plproxy->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 
echo "Send email messages\n";

$mail = new smtp;
$mail->subject = $eSubject;  // заголовок письма
$mail->message = $eMessage; // текст письма
$mail->recipient = ''; // свойство 'получатель' оставляем пустым
$spamid = $mail->send('text/html');
if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!
// допустим нам нужно получить список получателей с какого-либо запроса
$i = 0;
$mail->recipient = array();
$res = $DB->query($sql);
while ($row = pg_fetch_assoc($res)) {
    $mail->recipient[] = array(
        'email' => $row['email'],
        'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'USER_LOGIN' => $row['login'], 'UNSUBSCRIBE_KEY' => users::GetUnsubscribeKey($row["login"]) )
    );
    if (++$i >= 5000) {
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