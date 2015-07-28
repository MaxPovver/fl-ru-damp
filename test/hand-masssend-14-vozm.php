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


$sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, u.subscr FROM (
            SELECT DISTINCT uid as user_id FROM __tmp_PRO_vozm20120523
            UNION
            SELECT DISTINCT user_id FROM __tmp_FPAGE_vozm20120523
            UNION
            SELECT DISTINCT user_id FROM __tmp_PROJECTS_vozm20120523) q
            INNER JOIN users u ON u.uid = q.user_id AND u.is_banned = '0' 
                AND substr(u.subscr::text,8,1) = '1' AND u.uid != 103";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "Здравствуйте.

В связи с техническими работами 22-23 мая, сайт временно был недоступен.

Мы приносим свои извинения за временное неудобство и продлеваем платные услуги на 10 часов (действие аккаунта PRO для работодателей и фрилансеров, размещение на главной странице и в каталоге, закрепление проекта). Данное продление актуально в случае, если вышеперечисленные услуги были активны в момент проведения технических работ.

По всем возникающим вопросам вы можете обращаться в нашу http:/{службу поддержки}/{$pHost}/help/?all.
Вы можете отключить уведомления на http:/{странице «Уведомления/Рассылка»}/{$pHost}/users/%USER_LOGIN%/setup/mailer/ вашего аккаунта.

С уважением, http:/{Free-lance.ru}/{$pHost}/";

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
    $cnt++;
}
// Стартуем рассылку в личку
$DB->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 


echo "OK. Total: {$cnt} users\n";