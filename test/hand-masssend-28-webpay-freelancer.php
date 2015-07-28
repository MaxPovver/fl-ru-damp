<?php
/**
 * Уведомление фриленсерам
 * */
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

// Только фрилансерам, активированным (active = true), незабаненным (is_banned = B'0'), с включенными рассылками

$sql = "SELECT uid, email, login, uname, usurname, subscr FROM freelancer WHERE is_banned = B'0'
        AND (substring(subscr from 8 for 1)::integer = 1)"; //freelancer

//$sql = "SELECT uid, email, login, uname, usurname FROM users WHERE login = 'land_e2'"; 

$pHost = str_replace("http://", "", $GLOBALS['host']);
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$eHost = $GLOBALS['host'];

$pMessage = "
Здравствуйте!

В сообщениях для службы поддержки и в блогах мы видим много вопросов про Веб-кошелек и хотели бы рассказать о нем поподробнее. {$pHttp}:/{Веб-кошелек ПСКБ}/webpay.pscb.ru/login/auth – это платежная система, необходимая для работы с сервисом «Сделка Без Риска» при сотрудничестве по аккредитиву. Для того чтобы у вас не было лимита на вывод денежных средств, нужно идентифицировать свой Веб-кошелек (<a href=\"{$pHttp}://{$pHost}/help/?q=1035&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=web-wallet\" target=\"_blank\">инструкция</a>).

Веб-кошелек автоматически создается в момент заключения «Сделки Без Риска» и привязывается к вашему номеру мобильного телефона.  Если вы работаете как физическое лицо, гонорар за выполненный проект в сумме до 15 000 руб. выводится только на Веб-кошелек. 

<b><i>Получение гонорара</i></b>
Вывести деньги из Веб-кошелька можно следующими способами: 
<ul>
<li>платежным поручением на расчетный счет любого банка либо банковскую карту (без комиссии);</li>
<li>на кошельки платежных систем Яндекс.Деньги, 2pay, RBK Money и т.д. (без комиссии) и на кошельки WebMoney (комиссия 0,5%);</li>
<li>на виртуальную карту Master Card в рублях либо USD;</li>
<li>воспользоваться услугами поставщиков (оплата мобильной связи, интернета, товаров в интернет-магазинах и т.д.).</li></ul>

Подробная информация о Веб-кошельке находится в соответствующем разделе {$pHttp}:/{«Помощи»}/{$pHost}/help/?q=1035&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=web-wallet.

По всем возникающим вопросам вы можете обращаться в нашу {$pHttp}:/{службу поддержки}/{$pHost}/help/?all.
Вы можете отключить уведомления на {$pHttp}:/{странице «Уведомления/Рассылка»}/{$pHost}/users/%USER_LOGIN%/setup/mailer/ вашего аккаунта.

Приятной работы!
Команда {$pHttp}:/{Free-lance.ru}/{$pHost}/
";

$eSubject = "Важная информация о Веб-кошельке";

$eMessage = "<p>Здравствуйте!</p>
<p>В сообщениях для службы поддержки и в блогах мы видим много вопросов про Веб-кошелек и хотели бы рассказать о нем поподробнее. 
<a href='http://webpay.pscb.ru/login/auth' target='_blank'>Веб-кошелек ПСКБ</a> – это платежная система, необходимая для работы с сервисом «Сделка Без Риска» при сотрудничестве по аккредитиву. Для того чтобы у вас не было лимита на вывод денежных средств, нужно идентифицировать свой Веб-кошелек (<a href='{$eHost}/help/?q=1035&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=web-wallet' target='_blank'>инструкция</a>).</p>

<p>Веб-кошелек автоматически создается в момент заключения «Сделки Без Риска» и привязывается к вашему номеру мобильного телефона.  Если вы работаете как физическое лицо, гонорар за выполненный проект в сумме до 15 000 руб. выводится только на Веб-кошелек. </p>
<br/>
<b><i>Получение гонорара</i></b>
<p>Вывести деньги из Веб-кошелька можно следующими способами:</p>
<ul>
<li>платежным поручением на расчетный счет любого банка либо банковскую карту (без комиссии);</li>
<li>на кошельки платежных систем Яндекс.Деньги, 2pay, RBK Money и т.д. (без комиссии) и на кошельки WebMoney (комиссия 0,5%);</li>
<li>на виртуальную карту Master Card в рублях либо USD;</li>
<li>воспользоваться услугами поставщиков (оплата мобильной связи, интернета, товаров в интернет-магазинах и т.д.).</li></ul>
<br/>
<p>Подробная информация о Веб-кошельке находится в соответствующем разделе <a href='{$eHost}/help/?q=1035&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=web-wallet' target='_blank'>«Помощи»</a>.</p>
<br/>
<p>По всем возникающим вопросам вы можете обращаться в нашу <a href='{$eHost}/help/?all' target='_blank'>службу поддержки</a>.</p>
<p>Вы можете отключить уведомления на <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/' target='_blank'>странице «Уведомления/Рассылка»</a> вашего аккаунта.</p>
<br/>
Приятной работы!<br/>
Команда <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>";

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

echo "Send personal messages\n";

// подготавливаем рассылку
$msgid = $DB->val("SELECT masssend(?, ?, '{}', '')", $sender['uid'], $pMessage);
if (!$msgid) die('Failed!');

// допустим, мы получаем адресатов с какого-то запроса
$i = 0;
while ($users = $master->col("{$sql} LIMIT 30000 OFFSET ?", $i)) {
    $DB->query("SELECT masssend_bind(?, {$sender['uid']}, ?a)", $msgid, $users);
    $i = $i + 30000;
}
// Стартуем рассылку в личку
$DB->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 
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
$res = $master->query($sql);
while ($row = pg_fetch_assoc($res)) {
    $mail->recipient[] = array(
        'email' => $row['email'],
        'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'USER_LOGIN' => $row['login'])
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
