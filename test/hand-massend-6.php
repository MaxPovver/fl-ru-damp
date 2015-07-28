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
$pro = FALSE;

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
$recipients = 'employers';

/**
 * Запрос для получения данных пользователей, если $recipients == ''
 * Обязательные колонки: uid, login, uname, usurname, email, subscr
 * Важно! uid должен быть первым столбцом
 */
// флаг подписки - "Новости от команды Free-lance.ru"
$sw = $ew = str_repeat('0', $subscrsize);
$sw{7} = '1';
// ---
$sql = "SELECT u.* FROM freelancer u WHERE subscr & B'{$sw}' <> B'{$ew}' AND is_banned = B'0'";

/**
 * Текст для личного сообщения (формат функции reformat)
 * Если пустая строка, то в личку не шлем
 * {{name}} заменяются на колонки из $sql (если $mass == FALSE)
 * для $mass == TRUE можно использовать спец.переменные, см. http://www.free-lance.ru/siteadmin/admin/
 * Памятка: Ссылки пишутся в виде http:/{ссылке}/{$h}/quiz/form/ (работают только при рассылке от админа и менеджеров)
 */
$h = preg_replace("/http\:\/\//", "", $GLOBALS['host']);

$pMessage = "Здравствуйте!

Хотим рассказать вам о возможности сэкономить на услугах нашего сайта. Любые платные сервисы Free-lance.ru дешевле на 10 FM для клиентов с http:/{аккаунтом PRO}/{$h}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro<span>.</span> При регулярной работе на сайте вы можете сэкономить на оплате сервисов до 100 FM!

По нашей статистике, количество предложений от исполнителей на проекты, публикуемые владельцами http:/{аккаунта PRO}/{$h}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro<span>,</span> в разы больше. 
Аккаунт PRO – это выгодно и удобно. http:/{Убедитесь сами}/{$h}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro<span>!</span>

По всем возникающим вопросам вы можете обращаться в нашу http:/{службу поддержки}/{$h}/help/?all<span>.</span>
Вы можете отключить уведомления на http:/{странице &laquo;Уведомления/Рассылка&raquo;}/{$h}/users/%USER_LOGIN%/setup/mailer/<span> вашего</span> аккаунта.

Приятной работы,
Команда http:/{Free-lance.ru}/{$h}/
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
$eSubject = "Скидки на услуги Free-lance.ru";

/**
 * Текст для уведомления на почту (формат HTML)
 * Если пустая строка, то на почту не шлем
 * {{name}} заменяются на колонки из $sql (для любых $mass)
 */
$eMessage = "<p>Здравствуйте!</p>

<p>
Хотим рассказать вам о возможности сэкономить на услугах нашего сайта. Любые платные сервисы Free-lance.ru дешевле на 10 FM для клиентов с <a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro'>аккаунтом PRO</a>. При регулярной работе на сайте вы можете сэкономить на оплате сервисов до 100 FM!
</p>

<p>Судите сами!
    <table cellpadding='0' cellspacing='0' border='0' width='370'>
        <tbody>
            <tr>

                <td class='pad_null' colspan='4' height='40' valign='top'><font color='#000000' size='2' face='tahoma,sans-serif'><b>Стоимость услуг со скидками:</b></font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' colspan='2'><font color='#000000' size='2' face='tahoma,sans-serif'>При публикации проектов</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>без PRO, FM</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>с PRO, FM</font></td>
            </tr>

            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>закрепление наверху ленты</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>35</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>25</font></td>
            </tr>
            <tr>

                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>выделение цветом</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>0</font></td>
            </tr>
            <tr>
            <tr>

                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>выделение жирным</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>10</font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>

                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>загрузка логотипа</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>30</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>20</font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>поднятие проекта</font></td>

                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>10</font></td>
            </tr>
            <tr>
                <td class='pad_null' colspan='4' height='40'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>Экономия при публикации проекта до&nbsp;<font color='#fd6c30' size='3' face='tahoma,sans-serif'><b>60 FM</b></font></font></td>
            </tr>
            <tr>

                <td class='pad_null' colspan='4' height='20'>&#160;</td>
            </tr>
        </tbody>
        </table>
        <table cellpadding='0' cellspacing='0' border='0' width='370'>
        <tbody>
            <tr>
                <td class='pad_null' height='25' colspan='2'><font color='#000000' size='2' face='tahoma,sans-serif'>При публикации конкурсов</font></td>

                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>без PRO, FM</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>с PRO, FM</font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>публикация конкурса</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>110</font></td>

                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>100</font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>закрепление наверху ленты</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>45</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>35</font></td>

            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>выделение цветом</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>0</font></td>
            </tr>

            <tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>выделение жирным</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>10</font></td>
            </tr>

            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>загрузка логотипа</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>30</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>20</font></td>
            </tr>
            <tr>

                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>поднятие конкурса</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>35</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>25</font></td>
            </tr>
            <tr>
                <td class='pad_null' colspan='4' height='40'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>Экономия при публикации конкурса до&nbsp;<font color='#fd6c30' size='3' face='tahoma,sans-serif'><b>70 FM</b></font></font></td>

            </tr>
            <tr>
                <td class='pad_null' colspan='4' height='20'>&#160;</td>
            </tr>
            <tr>
                <td class='pad_null' colspan='4' height='40'><font color='#6DB335' size='2' face='tahoma,sans-serif'>И это при цене аккаунта PRO всего <font color='#6DB335' size='3' face='tahoma,sans-serif'><b>10 FM</b></font> в месяц.</font></td>
            </tr>

        </tbody>
    </table>
</p>
                
<p>
По нашей статистике, количество предложений от исполнителей на проекты, публикуемые владельцами <a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro'>аккаунта PRO</a>, в разы больше.<br/> 
Аккаунт PRO – это выгодно и удобно. <a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro'>Убедитесь сами</a>!
</p>
                             
<p>
По всем возникающим вопросам вы можете обращаться в нашу <a href='{$GLOBALS['host']}/help/?all'>службу поддержки</a>.<br />
Вы можете отключить уведомления <a href='{$GLOBALS['host']}/users/{{login}}/setup/mailer/'>на странице &laquo;Уведомления/Рассылка&raquo;</a> вашего аккаунта.
</p>

<p>
Приятной работы,<br/>
Команда <a href='{$GLOBALS['host']}/'>Free-lance.ru</a>
</p>
";


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

if ( $mass ) {
    while ( !$plproxy->val("SELECT COUNT(*) FROM messages(?) WHERE id = ?", $sender['uid'], $message_id) ) {
        echo "Wait PGQ (10 seconds)...\n";
        sleep(10);
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
