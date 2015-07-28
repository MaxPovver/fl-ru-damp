<?php
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';

// ----------------------------------------------------------------------------------------------------------------
// -- Блок настроек -----------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------

/**
 * Если TRUE - рассылка массовая (to_id = 0), если FALSE - адресная (каждому пользователю свое сообщение)
 * 
 */
$mass = TRUE;

/**
 * Рассылка только для pro ( только для $mass = TRUE )
 * TRUE - Pro, FALSE - Не Pro, NULL - всем
 * 
 */
$pro = NULL;

/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
$sender = 'admin';

/**
 * Кому рассылать
 * all - всем, employers - работодателям, freelancers - фрилансерам ( <- только для $mass = TRUE )
 * пустая строка - свой массив ( <- для любой $mass )
 * Важно! для $mass == FALSE всегда должна быть пустая строка
 */
$recipients = 'freelancers';

/**
 * Запрос для получения данных пользователей, если $recipients == ''
 * Обязательные колонки: uid, login, uname, usurname, email, subscr
 * Важно! uid должен быть первым столбцом
 */
$sql = "SELECT uid, login, uname, usurname, email, subscr FROM users WHERE login = 'jb_admin'";

/**
 * Текст для личного сообщения (формат HTML)
 * Если пустая строка, то в личку не шлем
 * {{name}} заменяются на колонки из $sql (если $mass == FALSE)
 * для $mass == TRUE можно использовать спец.переменные, см. http://www.free-lance.ru/siteadmin/admin/
 */
$pMessage = "<p>Привет, друзья!</p>

<p>У нас есть очень хорошая новость для вас.</p>

<p>Мы начали интеграцию с ведущим российским сайтом по поиску работы hh.ru. Теперь работодатели на hh.ru в выдаче поиска видят всех фрилансеров в соответствии с указанными в портфолио ключевыми словами.</p>

<p>Кроме того, ссылка на Free-lance.ru теперь размещена на главной странице hh.ru.</p>

<p>Чтобы привлечь клиентов hh.ru и получать интересные и высокооплачиваемые заказы, вам необходимо:</p>

<p>1. Подробно заполнить <a href='{$GLOBALS['host']}/help/?q=850?&utm_source=newsletter4&utm_medium=rassillka&utm_campaign=integration_hh.ru'>портфолио и профиль</a></p>

<p>2. Обязательно добавить <a href='{$GLOBALS['host']}/help/?q=948?&utm_source=newsletter4&utm_medium=rassillka&utm_campaign=integration_hh.ru'>ключевые слова (теги)</a>, 
по которым работодатели hh.ru смогут находить вас (например, &laquo;дизайнер&raquo;, &laquo;PHP-программист&raquo; и т.д.) 
Теперь эта функция доступна для всех, а не только для фрилансеров с 
<a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassillka&utm_campaign=integration_hh.ru'>аккаунтом PRO</a>.</p>

<p><a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassillka&utm_campaign=integration_hh.ru'>Перейти на сайт</a></p>

<p>Если у вас возникли вопросы, обратитесь <a href='{$GLOBALS['host']}/help/?all'>в службу поддержки</a> Free-lance.ru</p>

<p>Вы можете отключить уведомления на странице «Уведомления/Рассылка» вашего аккаунта.</p>

<p>Команда Free-lance.ru благодарит вас за участие в жизни нашего портала.</p>

<p>
Удачной работы!<br/>
Команда Free-lance.ru
</p>
";

/**
 * Массив с id прикрепляемых файлов из таблицы file для лички. Должны быть уже залиты на webdav.
 * NULL - без файлов
 */
$pFiles = NULL;

/**
 * Заголовок для уведомления на почту
 * Если пустая строка, то на почту не шлем
 * {{name}} заменяются на колонки из $sql (для любых $mass)
 */
$eSubject = "Интеграция с hh.ru – будьте готовы к крупным клиентам!";

/**
 * Текст для уведомления на почту (формат HTML)
 * Если пустая строка, то на почту не шлем
 * {{name}} заменяются на колонки из $sql (для любых $mass)
 */
$eMessage = $pMessage;

/**
 * Флаг подписки (номер байта) колонки subscr из таблицы users, который следует проверять для рассылки почты
 * Если NULL - слать всем
 * Для "Новости от команды Free-lance.ru" флаг == 7
 * 
 */
$eSubscr = 7;

/**
 * Массив с id прикрепляемых файлов из таблицы file для почты. Должны быть уже залиты на webdav.
 * NULL - без файлов
 */
$eFiles = NULL;

/**
 * Через какое количество отосланных сообщений выводить статистику о них
 * (для адресной рассылки и email рассылки)
 * 
 */
$printStatus = 200;


// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------

$master  = new DB('master');
$plproxy = new DB('plproxy');
$count   = NULL;

$sender = $master->row("SELECT * FROM users WHERE login = ?", $sender);
if ( empty($sender) ) {
    die("Unknown Sender\n");
}

echo "Send personal messages\n";

if ( $mass ) {
    
    $count = 0;
    
    switch ( $recipients ) {
        case 'all': {
            $message_id = $plproxy->val("SELECT messages_masssend_all(?, ?, ?, ?a)", $sender['uid'], $pro, $pMessage, $pFiles);
            break;
        }
        case 'freelancers': {
            $message_id = $plproxy->val("SELECT messages_masssend_freelancers(?, ?, ?, ?a)", $sender['uid'], $pro, $pMessage, $pFiles);
            break;
        }
        case 'employers': {
            $message_id = $plproxy->val("SELECT messages_masssend_employers(?, ?, ?, ?a)", $sender['uid'], $pro, $pMessage, $pFiles);
            break;
        }
        case '': {
            $users = $master->col($sql);
            if ( empty($users) ) {
                die("No users\n");
            }
            $count = count($users);
            $message_id = $plproxy->val("SELECT messages_masssend(?, ?a, ?, ?a)", $sender['uid'], $users, $pMessage, $pFiles);
            unset($users);
            break;
        }
        default: {
            die("Unknown mode\n");
        }
    }
        
} else {
    
    $count = 0;
    
    $res = $master->query($sql);
    while ( $user = pg_fetch_assoc($res) ) {
        
        $msg = preg_replace("/\{\{([-_A-Za-z0-9]+)\}\}/e", "\$user['\\1']", $pMessage);
        $plproxy->query("SELECT messages_add(?, ?, ?, ?, ?a)", $sender['uid'], $user['uid'], $msg, TRUE, $pFiles);
        
        if ( ($count > 0) && ($count % $printStatus == 0) ) {
            echo "Working... {$count} emails sended\n";
        }
        
        $count++;
        
    }
    
}

$memBuff = new memBuff();
$memBuff->set("msgsCnt_updated", time());

if ( is_null($count) ) {
    die("Settings error\n");
} else if ( $count ) {
    echo "OK. Total: {$count} users\n";
} else {
    echo "OK.\n";
}



if ( $mass ) {
    while ( !$plproxy->val("SELECT COUNT(*) FROM messages(?) WHERE id = ?", $sender['uid'], $message_id) ) {
        echo "Wait PGQ (10 seconds)...\n";
        sleep(10);
    }
    $res = $plproxy->query("SELECT * FROM messages_zeros_userdata(?, ?)", $sender['uid'], $message_id);
} else {
    $res = $master->query($sql);
}

echo "Send email messages\n";

$count = 0;
$smtp  = new SMTP;
if ( !$smtp->Connect() ) {
    die("Don't connect to SMTP\n");
}
    
while ( $user = pg_fetch_assoc($res) ) {
        
    if ( empty($user['email']) || (!is_null($eSubscr) && substr($user['subscr'], $eSubscr, 1) == '0') ) {
        continue;
    }
        
    $smtp->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
    $smtp->subject   = preg_replace("/\{\{([-_A-Za-z0-9]+)\}\}/e", "\$user['\\1']", $eSubject);
    $smtp->message   = preg_replace("/\{\{([-_A-Za-z0-9]+)\}\}/e", "\$user['\\1']", $eMessage);
    
    if ( ($count > 0) && ($count % $printStatus == 0) ) {
        echo "Working... {$count} emails sended\n";
    }
    
    $smtp->SmtpMail('text/html');
    
    $count++;
        
}

echo "OK. Total: {$count} users\n";
