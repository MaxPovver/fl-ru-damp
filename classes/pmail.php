<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smtp.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';

/**
 * Класс для отправки писем. Обрабатывается PgQ
 *
 * После изменения необходимо перезапустить консьюмеры /classes/pgq/mail_cons.php и /classes/pgq/plproxy-mail.php на сервере 
 * Если нет возможности, то сообщить админу.
 * @see PGQMailSimpleConsumer::finish_batch()
 */
class pmail extends SMTP {
    
    
    /**
     * Общая функция для рассылки сообщений из косюмера nsync
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @param  string  $subject     тема письма
     * @param  string  $message     сообщение (или NULL чтобы использовать сообщение из таблицы messages)
     * @param  boolean $useVars     использование переменных
     * @return integer              id сообщения, 0 - ошибка
     */
    protected function _nsyncMasssend( $msgid, $spamid, &$recipients, $subject, $message=NULL, $useVars=TRUE ) {
        $DB = new DB('master');
        $messages = new messages;
        if ( empty($spamid) && empty($recipients) ) {
            if ( is_null($message) ) {
                if ( !($message = $messages->GetMessage($msgid)) ) {
                    return 0;
                }
                $message = trim($message['msg_text']);
            } 
            $message = str_replace("\n", "\r\n", $message); // это потом доработать и перенести в smtp::SendSmtp
            $text = reformat($message, 100, 0, -1);
            $this->subject   = $subject;
            $this->message   = $this->GetHtml('', $text, array('header'=>'none', 'footer'=>'none'));
            $this->recipient = '';
            return $this->send('text/html',  array());
        } else {
            if ( empty($recipients) ) {
                return 0;
            }
            $this->recipient = array();
            $res = $DB->query("SELECT * FROM users WHERE uid IN (?l)", $recipients);
            if ( $useVars ) {
                while ( $row = pg_fetch_assoc($res) ) {
                    $this->recipient[] = array(
                        'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
                        'extra' => array(
                            'USER_NAME'    => $row['uname'],
                            'USER_SURNAME' => $row['usurname'],
                            'USER_LOGIN'   => $row['login']
                        )
                    );
                }
            } else {
                while ( $row = pg_fetch_assoc($res) ) {
                    $this->recipient[] = "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>";
                }
            }
            return $this->bind($spamid);
        }
    }
    

	/**
     * Рассылка от администранции /siteadmin/admin
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              0 -> ошибка
     */
    public function SpamFromAdmin($msgid, $spamid, $recipients) {
        $DB = new DB('master');
        $messages = new messages;
        if ( empty($spamid) && empty($recipients) ) {
            if ( !($message = $messages->GetMessage($msgid)) ) {
                return 0;
            }
            $text = reformat2($message['msg_text'], 100);
            $this->subject   = "Новое сообщение от Команды FL.ru";
            $this->message   = $this->GetHtml('', $text, array('header'=>'none', 'footer'=>'none'));
            $this->recipient = '';
            return $this->send('text/html', ($message['files'] == '{}'? array(): $DB->array_to_php($message['files'])));
        } else {
            if ( empty($recipients) ) {
                return 0;
            }
            $this->recipient = array();
            $res = $DB->query("SELECT * FROM users WHERE uid IN (?l)", $recipients);
            while ( $row = pg_fetch_assoc($res) ) {
                $this->recipient[] = array(
                    'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
                    'extra' => array(
                        'USER_NAME'    => $row['uname'],
                        'USER_SURNAME' => $row['usurname'],
                        'USER_LOGIN'   => $row['login']
                    )
                );
            }
            return $this->bind($spamid);
        }
	}
    
    
	public function SpamFromMasssending($msgid, $spamid, $recipients) {
        $DB = new DB('master');
        $messages = new messages;
        if ( empty($spamid) && empty($recipients) ) {
            if ( !($message = $messages->GetMessage($msgid)) ) {
                return 0;
            }
            // рассылка пользователям (подготовка)
            $this->recipient = '';
            $this->subject   = "Новое сообщение на FL.ru";
            $msg_text = "
<a href='{$GLOBALS['host']}/users/{$message['from_login']}{$this->_addUrlParams('b')}'>{$message['from_uname']} {$message['from_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$message['from_login']}{$this->_addUrlParams('b')}'>{$message['from_login']}</a>]
написал(а) вам новое сообщение на сайте FL.ru.<br />
<br />
---------- 
<br />
".$this->ToHtml(LenghtFormatEx(strip_tags($message['msg_text']), 300))."
<br />
<br />
<br />
<a href='{$GLOBALS['host']}/contacts/?from={$message['from_login']}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/contacts/?from={$message['from_login']}</a>
<br />
<br />
------------
";
            $this->message = $this->GetHtml('%USER_NAME%', $msg_text, array('header'=>'default', 'footer'=>'simple'));
            return $this->send('text/html', ($message['files'] == '{}'? array(): $DB->array_to_php($message['files'])));
        } else {
            if ( empty($recipients) ) {
                return 0;
            }
            $this->recipient = array();
            $res = $DB->query("SELECT u.*, usk.key AS unsubscribe_key FROM users AS u LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid WHERE u.uid IN (?l)", $recipients);
            while ( $row = pg_fetch_assoc($res) ) {
            	if (!$row['unsubscribe_key']) {
            		$row['unsubscribe_key'] = users::writeUnsubscribeKey($row["uid"]);
            	}
                $this->recipient[] = array(
                    'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
                    'extra' => array(
                        'USER_NAME'       => $row['uname'],
                        'USER_SURNAME'    => $row['usurname'],
                        'USER_LOGIN'      => $row['login'],
                        'UNSUBSCRIBE_KEY' => $row['unsubscribe_key']
                    )
                );
            }
            return $this->bind($spamid);
        }

	}
    
    
    /**
     * Отправляет уведомления о новых сообщениях в личке ("Мои контакты").
	 * Консьюмер plproxy-mail
     * 
     * @param   array      $params    Данные от PgQ, TO-адрес получателя; FROM-адрес отправителя
     * @param   string     $msg       Текст сообщения
     *
     * @return  integer    количество отправленных уведомлений.
     */
	function NewMessage($from_uid, $to_uid, $msg) {

		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';

		$to = new users;
		$to->GetUserByUID($to_uid);

		if (substr($to->subscr, 0, 1) != '1' || !$to->email || $to->is_banned == '1') {
			return 0;
		}

		$from = new users;
		$from->GetUserByUID($from_uid);
                $msg = preg_replace("/\/\{\W+\}\//", "//", $msg); // Удаляем умные ссылки которые идут в сообщения
		$this->message = $this->GetHtml($to->uname, "
<a href='{$GLOBALS['host']}/users/{$from->login}{$this->_addUrlParams('b')}'>{$from->uname} {$from->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$from->login}{$this->_addUrlParams('b')}'>{$from->login}</a>]
написал(а) вам новое сообщение на сайте FL.ru.<br />
<br />
---------- 
<br />
".$this->ToHtml(LenghtFormatEx(strip_tags($msg), 300))."
<br />
<br />
<br />
<a href='{$GLOBALS['host']}/contacts/?from={$from->login}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/contacts/?from={$from->login}</a>
<br />
------------
", array('header' => 'default', 'footer' => 'default'), array('login'=>$to->login));
	
		$this->recipient = "{$to->uname} {$to->usurname} [{$to->login}] <{$to->email}>";
		$this->subject = "Новое сообщение на FL.ru";
		$this->send('text/html');
		
		return $this->sended;
	
	}

        
    /**
     * Отправляет уведомления о новых сообщениях в заказах типовых услуг.
     * Консьюмер plproxy-mail
     * 
     * @param   array      $params    Данные от PgQ, TO-адрес получателя; FROM-адрес отправителя
     * @param   string     $order     Заказ
     * @param   string     $msg       Текст сообщения
     *
     * @return  integer    количество отправленных уведомлений.
     */
    function NewTserviceMessage($from_uid, $to_uid, $order, $msg) {

        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';

        $to = new users;
        $to->GetUserByUID($to_uid);

        if (substr($to->subscr, 0, 1) != '1' || !$to->email || $to->is_banned == '1') {
            return 0;
        }

        $from = new users;
        $from->GetUserByUID($from_uid);
        $msg = preg_replace("/\/\{\W+\}\//", "//", $msg); // Удаляем умные ссылки которые идут в сообщения
        $role = in_array($from_uid, array($order['frl_id'], $order['emp_id'])) 
                ? (is_emp() ? 'Заказчик' : 'Исполнитель')
                : 'Арбитр';
        $this->message = $this->GetHtml($to->uname, "
            {$role} {$from->uname} {$from->usurname} [{$from->login}] оставил вам новое сообщение в заказе <br />
            «<a href='{$GLOBALS['host']}/tu/order/{$order['id']}/'>{$order['title']}</a>»:<br /><br />
            <em>" . $this->ToHtml(LenghtFormatEx(strip_tags($msg), 300)) . "</em><br /><br />"
            . "<a href='{$GLOBALS['host']}/tu/order/{$order['id']}/#messages'>Перейти к сообщению</a> /
                <a href='{$GLOBALS['host']}/tu/order/{$order['id']}/#messages'>Ответить на него</a>

", array('header' => 'default', 'footer' => 'default'), array('login' => $to->login));

        $this->recipient = "{$to->uname} {$to->usurname} [{$to->login}] <{$to->email}>";
        $this->subject = "Новое сообщение в заказе на FL.ru";
        $this->send('text/html');

        return $this->sended;
    }
    

    /**
     * Отправляет уведомления о новых комментариях в сообществе.
     * 
     * @param   string|array   $message_ids  идентификаторы комментариев.
     * @param   resource       $connect      соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                      количество отправленных уведомлений.
     */
    function CommuneNewComment($message_ids, $connect = NULL)
    {
        require_once($_SERVER['DOCUMENT_ROOT'].'/classes/commune.php');
        $commune = new commune();
        if(!($comments = $commune->GetComments4Sending($message_ids, $connect)))
            return NULL;

        $top_ids = array();
        foreach($comments as $cm) {
            $top_ids[] = $cm['top_id'];
        }

        $subscribers = array();
        if(count($top_ids)) {
            $top_ids = array_unique($top_ids);
            $subscr = $commune->getThemeSubscribers($top_ids);
            
            foreach($subscr as $row) {
                $subscribers[$row['message_id']][] = $row;
            }
        }

        foreach($comments as $comment) {
            $this->subject = 'Новый комментарий в топике «'.$comment['top_title'].'» сообщества «'.$comment['commune_name'].'»';
            $userlink = $GLOBALS["host"]."/users/".$comment['login'];
            $friendly_url_topic = getFriendlyURL('commune', $comment["top_id"]); 
            $body_start = "
<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] оставил(а) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">комментарий</a> к вашему ".($comment["parent_id"] != $comment["top_id"] ? "сообщению/комментарию" : "посту" )." в топике «<a href=\"{$GLOBALS['host']}{$friendly_url_topic}?{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['top_title']}</a>» сообщества «<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>».
<br/><br/>
--------
";

            $body_subscr =
"<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] оставил(а) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">комментарий</a> к ".($comment["parent_id"] != $comment["top_id"] ? "сообщению/комментарию" : "посту" ).".
<br/><br/>
--------
";

            $body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".reformat2($comment['msgtext'],100,0,1)."
<br/><br/>
--------
";
$p_body = 
reformat2($comment['title'],100)."
".str_replace(array("\r", "\n", "<br>", "<br/>"), array("__NEWLINE__", "__NEWLINE__", "__NEWLINE__", "__NEWLINE__" ), $comment['msgtext'])."
--------";

            $p_body = str_replace("__NEWLINE__", "\n", $p_body);
            $p_body = str_replace("<br/>", "\n", "\n--------\n".$p_body);
            $skip_users = array();
            $skip_users[] = $comment['user_id'];
            $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$comment['commune_id']}' target='_blank'>{$comment['commune_name']}</a>";
            $link_topic = "<a href='{$GLOBALS['host']}{$friendly_url_topic}' target='_blank'>{$comment['top_title']}</a>";
                    
            if($comment['p_user_id'] != $comment['user_id']
                 && $comment['p_email']
                 && substr($comment['p_subscr'],5,1)=='1'
                 && $comment['p_banned'] == '0')
            {
                // отправляем родителю.
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->message = $this->GetHtml($comment['p_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['p_user_id'];
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
                $msg = "Здравствуйте, {$comment['p_uname']}.
<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] оставил(а) коментарий к вашему ".($comment["parent_id"] == $comment["top_id"] ? "посту" : "комментарию к посту")." {$link_topic} в сообществе {$link_commune}. $p_body
Это сообщение было отправлено автоматически и не требует ответа.
Команда FL.ru.";
                //messages::Add( users::GetUid($err, 'admin'), $comment['p_login'], $msg, '', 1 );
            }

            if($comment['t_user_id']!=$comment['user_id']
                 && $comment['t_user_id']!=$comment['p_user_id']
                 && $comment['t_email']
                 && substr($comment['t_subscr'],5,1)=='1'
                 && $comment['t_banned'] == '0')
            {
                // отправляем автору топика.
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->message = $this->GetHtml($comment['t_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['t_user_id'];
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
                $msg = "Здравствуйте, {$comment['t_uname']}.
<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] оставил коментарий ".($comment["parent_id"] == $comment["top_id"] ? "к вашему посту" : "в ветке топика")." {$link_topic} в сообществе {$link_commune}. $p_body
Это сообщение было отправлено автоматически и не требует ответа.
Команда FL.ru.";
                //messages::Add( users::GetUid($err, 'admin'), $comment['t_login'], $msg, '', 1 );
            }

            if(isset($subscribers[$comment['top_id']])) {
                // отправка всем подписчикам топика
                foreach($subscribers[$comment['top_id']] as $user) {
                    // кроме родителя и автора
                    if(in_array($user['user_id'], $skip_users)) continue;
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    $this->message = $this->GetHtml($user['uname'], 
                        $body_subscr . $body,
                        array('header' => 'subscribe', 'footer' => 'subscribe'),
                        array('type' => 0, 'title' => $link_commune, 'topic_title' => $link_topic, 'login' => $user['login'], 'is_comment' => $user['parent_id']));
                    $this->SmtpMail('text/html');
                    $msg = "Здравствуйте, {$user['uname']}.
<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] оставил(а) коментарий в ".($comment["parent_id"] == $comment["top_id"] ? "топике" : "ветке топика")." {$link_topic} сообщества {$link_commune} на которое вы подписаны. $p_body";
                    require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
                    //messages::Add( users::GetUid($err, 'admin'), $user['login'], $msg, '', 1 );
                }
            }
        }

        return $this->sended;
    }
    
    /**
     * Отправляет уведомления о новых комментариях в сообществе.
     * 
     * @param   string|array   $message_ids  идентификаторы комментариев.
     * @param   resource       $connect      соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                      количество отправленных уведомлений.
     */
    function CommuneUpdateComment($message_ids, $connect = NULL)
    {
        require_once($_SERVER['DOCUMENT_ROOT'].'/classes/commune.php');
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
        $commune = new commune();
        if(!($comments = $commune->GetComments4Sending($message_ids, $connect))) {
        	$subscr = $commune->getThemeSubscribers(implode(',', $message_ids));
        	$this->CommuneUpdateTopic($subscr, (is_array($message_ids)? $message_ids[0] : $message_ids) );
           return NULL;
        }
        $top_ids = array();
        foreach($comments as $cm) {
            $top_ids[] = $cm['top_id'];
        }

        $subscribers = array();
        if(count($top_ids)) {
            $top_ids = array_unique($top_ids);
            $subscr = $commune->getThemeSubscribers(implode(',', $top_ids));
            
            foreach($subscr as $row) {
                $subscribers[$row['message_id']][] = $row;
            }
        }

        foreach($comments as $comment) {
            $this->subject = 'Комментарий в сообществе «'.$comment['commune_name'].'» отредактирован';
            $userlink = $GLOBALS["host"]."/users/".$comment['login'];
            $skip_users = array();
            $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$comment['commune_id']}' target='_blank'>{$comment['commune_name']}</a>";
            $friendly_url_topic = getFriendlyURL('commune', $comment["top_id"]); 
            $link_topic = ($comment["top_title"]? "<a href='{$GLOBALS['host']}{$friendly_url_topic}' target='_blank'>{$comment['top_title']}</a>" : '');
        
            $admin_userlink = $GLOBALS["host"]."/users/".$comment['admin_login'];
            if($comment['commune_id'] == commune::COMMUNE_BLOGS_ID && $comment['p_user_id'] != $comment['user_id']) {
                $admin_user = "Модератор сообщества";
            } else {
                $admin_user = "<a href='{$admin_userlink}'>{$comment['admin_uname']} {$comment['admin_usurname']}</a> [<a href='{$admin_userlink}'>{$comment['admin_login']}</a>]";
            }
            
            if($comment['p_user_id'] != $comment['user_id']
                 && $comment['p_email']
                 && substr($comment['p_subscr'],5,1)=='1'
                 && $comment['p_banned'] == '0')
            {
                // отправляем родителю.
                $body_start = "
{$admin_user} отредактировал(а) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">комментарий</a> к вашему сообщению/комментарию в топике «<a href=\"{$GLOBALS['host']}{$friendly_url_topic}{$this->_addUrlParams('b', '?')}\" target=\"_blank\">{$comment['top_title']}</a>» сообщества «<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>».
<br/><br/>
--------
";

            $body_subscr =
"<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] отредактировал(а) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">комментарий</a> к сообщению/комментарию.
<br/><br/>
--------
";

            $body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".reformat2($comment['msgtext'],100,0,1)."
<br/><br/>
--------
";
$p_body = 
reformat2($comment['title'],100)."
".str_replace(array("\r", "\n", "<br>", "<br/>"), array("__NEWLINE__", "__NEWLINE__", "__NEWLINE__", "__NEWLINE__" ), $comment['msgtext'])."
--------";
                $p_body = str_replace("__NEWLINE__", "\n", $p_body);
                $p_body = str_replace("<br/>", "\n", "\n--------\n".$p_body);
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->message = $this->GetHtml($comment['p_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['p_user_id'];
            }

            if(  $comment['t_email']
                 && substr($comment['t_subscr'],5,1)=='1'
                 && $comment['t_banned'] == '0'
                 && ! in_array($comment["t_user_id"], $skip_users) )
            {
                // отправляем автору топика.
                $body_start = "
Модератор сообщества отредактировал <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">". ($comment["t_login"] == $comment["admin_login"] ? 'ваше' : '') ." сообщение/комментарий</a> в топике «<a href=\"{$GLOBALS['host']}{$friendly_url_topic}{$this->_addUrlParams('b', '?')}\" target=\"_blank\">{$comment['top_title']}</a>» сообщества «<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>».
<br/><br/>
--------
";
                $body_subscr =
"Модератор отредактировал <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\"> ". ($comment["t_login"] == $comment["admin_login"] ? 'ваше' : '') ." сообщение / комментарий</a>.
<br/><br/>
--------
";
            
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->message = $this->GetHtml($comment['t_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['t_user_id'];
                $admin_userlink = $GLOBALS["host"]."/users/".$comment['admin_login'];
                $msg = "Здравствуйте, {$comment['t_uname']} {$comment['t_usurname']}.<br/>
<a href=\"{$admin_userlink}\">{$comment['admin_uname']}</a> <a href=\"{$admin_userlink}\">{$comment['admin_usurname']}</a> [<a href=\"{$admin_userlink}\">{$comment['admin_login']}</a>] отредактировал коментарий к вашему сообщению / комментарию {$link_topic} в сообществе {$link_commune}. $p_body";
                //messages::Add( users::GetUid($err, 'admin'), $comment['t_login'], $msg, '', 1 );
            }
            if ( ! in_array($comment["user_id"], $skip_users) ) {
	                // отправляем автору комментария.
	                $body_start = ($comment["admin_login"] == $comment["t_login"] ? "Автор темы " : "Модератор сообщества ")."
	отредактировал <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\"> ваше сообщение/комментарий</a> в топике «<a href=\"{$GLOBALS['host']}{$friendly_url_topic}{$this->_addUrlParams('b', '?')}\" target=\"_blank\">{$comment['top_title']}</a>» сообщества «<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>».
	<br/><br/>
	--------
	";
	                $body_subscr = ($comment["admin_login"] == $comment["t_login"] ? "Автор темы " : "Модератор сообщества ")."
	отредактировал <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\"> ваше сообщение / комментарий</a>.
	<br/><br/>
	--------
	";
	                $this->recipient = $comment['uname']." ".$comment['usurname']." [".$comment['login']."] <".$comment['email'].">";
	                $this->message = $this->GetHtml($comment['uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['login']));
	                $this->SmtpMail('text/html');
	                $msg = "Здравствуйте, {$comment['uname']}.<br/>
<a href=\"{$admin_userlink}\">{$comment['admin_uname']}</a> <a href=\"{$admin_userlink}\">{$comment['admin_usurname']}</a> [<a href=\"{$admin_userlink}\">{$comment['admin_login']}</a>] отредактировал ваш коментарий в топике {$link_topic} в сообществе {$link_commune}. $p_body";
                    //messages::Add( users::GetUid($err, 'admin'), $comment['login'], $msg, '', 1 );
	                $skip_users[] = $comment['user_id'];
           }
//письмо подписаным
$body_start = "
{$admin_user} отредактировал(а) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">комментарий</a> к вашему сообщению/комментарию в топике «<a href=\"{$GLOBALS['host']}/commune/".translit($comment['commune_name'])."/{$comment['top_id']}/".translit($comment['group_name'])."/".translit($comment['top_title'])."/{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['top_title']}</a>» сообщества «<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>».
<br/><br/>
--------
";

            $body_subscr =
"{$admin_user} отредактировал(а) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">комментарий</a> к сообщению/комментарию.
<br/><br/>
--------
";

            $body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".reformat2($comment['msgtext'],100,0,1)."
<br/><br/>
--------
";
$p_body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".str_replace(array("\r", "\n", "<br>", "<br/>"), array("__NEWLINE__", "__NEWLINE__", "__NEWLINE__", "__NEWLINE__" ), $comment['msgtext'])."
--------";

            $p_body = str_replace("__NEWLINE__", "<br/>", $p_body);
            $p_body = str_replace("<br/>", "\n", "\n--------\n".$p_body);
            if(isset($subscribers[$comment['top_id']])) {
                // отправка всем подписчикам топика
                foreach($subscribers[$comment['top_id']] as $user) {
                    // кроме родителя и автора
                    if(in_array($user['user_id'], $skip_users)) continue;
                    
                    $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$comment['commune_id']}' target='_blank'>{$comment['commune_name']}</a>";
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    $this->message = $this->GetHtml($user['uname'], 
                        $body_subscr . $body,
                        array('header' => 'subscribe_edit_comment', 'footer' => 'subscribe_edit_comment'),
                        array('type' => 0, 'title' => $link_commune, 'login' => $user['login']));
                    $this->SmtpMail('text/html'); 
                }
            }
        }

        return $this->sended;
    }
    /**
     * Отправка уведомления о редактировании топика в сообществе
     * Вызывается в тех случаях, когда commune::GetComments4Sending вернула FALSE
     * @param $subscr - массив подписчиков, возвращаемый commune::getThemeSubscribers
     * @param $msg_id - идентификатор темы сообщества или комментария сообщества
     */
    function CommuneUpdateTopic($subscr, $msg_id) {
        $subscribers = array();
        $info = commune::getMessageInfoByMsgID( $msg_id );
        $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$info['commune_id']}' target='_blank'>{$info['commune_name']}</a>";
        $friendly_url_topic = getFriendlyURL('commune', $info["top_id"]); 
        $link_topic = ($info["title"]? "<a href='{$GLOBALS['host']}{$friendly_url_topic}' target='_blank'>{$info['title']}</a>" : '');
        $skip_users = array();
        $admin_userlink = $GLOBALS["host"]."/users/".$info['editor_login'];
        if($info['commune_id'] == commune::COMMUNE_BLOGS_ID && $info["commentator_uid"] != $info["editor_id"]) {
            $admin_user = "Модератор сообщества";
        } else {
            $admin_user = "<a href='{$admin_userlink}'>{$info['editor_uname']} {$info['editor_usurname']}</a> [<a href='{$admin_userlink}'>{$info['editor_login']}</a>]";
        }
        //отправка автору комментария
        if ($info["commentator_uid"] != $info["editor_id"] && $info["parent_id"]) {
        	$this->subject = ($info['parent_id']?'Ваш комментарий':'Ваш пост').($info['title'] ? ' «'.$info['title'].'» в сообществе':' в сообществе').' «'.$info['commune_name'].'» отредактирован.';

            $body_start = "
    {$admin_user} отредактировал(а) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$info['top_id']}.{$msg_id}{$this->_addUrlParams('b', '&')}#c_{$msg_id}\">ваше сообщение/комментарий</a> в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>».
    <br/><br/>
    --------
    ";
            $body_subscr =
    "{$admin_user} отредактировал(а) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$info['top_id']}.{$msg_id}{$this->_addUrlParams('b', '&')}#c_{$msg_id}\">комментарий</a> к сообщению/комментарию.
    <br/><br/>
    --------
    ";
            $body = 
    "<br/>".reformat2($info['msgtext'],100,0,1)."
    <br/><br/>
    --------
    ";
            $this->recipient = $info['commentator_uname']." ".$info['commentator_usurname']." [".$info['commentator_login']."] <".$info['commentator_email'].">";
            $this->message = $this->GetHtml($info['commentator_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$info['commentator_login']));
            $this->SmtpMail('text/html');
            $skip_users[] = $info['commentator_uid'];
        }
        
        //отправка автору топика
        if ( $info && $info['topicstarter_uid'] && $info['topicstarter_uid'] != $info['editor_id'] && ! in_array($info['topicstarter_uid'], $skip_users) ) {
   /*2*/         $this->subject = ($info['parent_id']?'Комментарий к вашему посту':'Ваш пост').($info['title'] ? ' «'.$info['title'].'» в сообществе':' сообщества').' «'.$info['commune_name'].'» отредактирован.';
            $body_start = "
	        <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">Комментарий</a> к вашему посту в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>» отредактирован модератором сообщества.
	        <br/><br/>
	        --------
	        ";
            if (!$info["parent_id"]) {
                $body_start = "
	            <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">Ваш пост</a> в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>» отредактирован модератором сообщества.
	            <br/><br/>
	            --------
	            ";  
            }
            $body = 
	        "<br/>".reformat2($info['msgtext'],100,0,1)."
	        <br/><br/>
	        --------
	        ";
            $this->recipient = $info['topicstarter_uname']." ".$info['topicstarter_usurname']." [".$info['topicstarter_login']."] <".$info['topicstarter_email'].">";
            $this->message = $this->GetHtml($info['topicstarter_uname'], 
                        $body_start . $body,
                        array('header' => 'subscribe_edit_post', 'footer' => 'default'),
                        array('type' => 0, 'title' => $link_commune, 'topic_name' => $link_topic, 'is_comment' => $info['parent_id'], 'to_topicstarter' => true, 'login' => $info['topicstarter_login'],  'is_author' => ($info['deleter_uid'] == $info['topicstarter_uid']) ));
            $this->SmtpMail('text/html');
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
            $msg = "Здравствуйте, {$info['topicstarter_uname']}.
Модератор сообщества отредактировал ".( $info["parent_id"] ? "комментарий к Вашему посту" : "Ваш пост" )." {$link_topic} в сообществе {$link_commune}.";
            //messages::Add( users::GetUid($err, 'admin'), $info['topicstarter_login'], $msg, '', 1 );
            $skip_users[] = $info['topicstarter_uid'];
        }
        foreach($subscr as $user) {
            if ( in_array($user["user_id"], $skip_users) ) continue;
            $this->subject = ($info['parent_id']?'В топике':'Топик').($info['title'] ? ' «'.$info['title'].'» в сообществе':' сообщества').' «'.$info['commune_name'].'» отредактирован'.($info['parent_id']?' комментарий':'');
            $userlink = $GLOBALS["host"]."/users/".$info['editor_login'];
            $body_start = "
            {$admin_user} отредактировал(а) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">сообщение</a> в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>».
            <br/><br/>
            --------
            ";
            
            $body_subscr =
            "{$admin_user} отредактировал(а) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$this->_addUrlParams('b', '&')}\">сообщение</a>.
            <br/><br/>
            --------
            ";
            
            $body = 
            "<br/>".reformat2($info['msgtext'],100,0,1)."
            <br/><br/>
            --------
            ";
            $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
            $this->message = $this->GetHtml($user['uname'], 
                            $body_start . $body,
                            array('header' => ($info['parent_id'] ? 'subscribe_edit_comment' : 'subscribe_edit_post'), 'footer' =>  'default' ),
                            array('type' => 0, 'title' => $link_commune, 'topic_name' => $link_topic, 'login' => $user['login'], 'is_admin' => ($info['editor_id'] == $info['topicstarter_uid']), 'to_subscriber' => true ));
            $this->SmtpMail('text/html');
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
            $msg = "Здравствуйте, {$user['uname']}.
    Отредактирован ".($info["parent_id"] ? "комментарий к посту {$link_topic}" : 'пост &laquo;'.$info["title"]."&raquo;"). " сообщества {$link_commune} на который вы подписаны.";
            //messages::Add( users::GetUid($err, 'admin'), $user['login'], $msg, '', 1 );
        }
    }
    /**
     * Отправляет уведомления о новых комментариях в сообществе.
     * 
     * @param   string|array   $message_ids  идентификаторы комментариев.
     * @param   resource       $connect      соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                      количество отправленных уведомлений.
     */
    function CommuneDeleteComment($message_ids, $connect = NULL)
    {
        require_once($_SERVER['DOCUMENT_ROOT'].'/classes/commune.php');
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
        $commune = new commune();
        if(!($comments = $commune->GetComments4Sending($message_ids, $connect))) {
            $subscr = $commune->getThemeSubscribers(implode(',', $message_ids));
            $this->CommuneDeleteTopic($subscr, (is_array($message_ids)? $message_ids[0] : $message_ids) );
            return NULL;
        }
        $top_ids = array();
        foreach($comments as $cm) {
            $top_ids[] = $cm['top_id'];
        }

        $subscribers = array();
        if(count($top_ids)) {
            $top_ids = array_unique($top_ids);
            $subscr = $commune->getThemeSubscribers(implode(',', $top_ids));
            
            foreach($subscr as $row) {
                $subscribers[$row['message_id']][] = $row;
            }
        }

        foreach($comments as $comment) {
            $this->subject = 'Комментарий в сообществе «'.$comment['commune_name'].'» удален';
            $skip_users = array();
            $skip_users[] = $comment['user_id'];
            $userlink = $GLOBALS["host"]."/users/".$comment['login'];
            if($comment['commune_id'] == commune::COMMUNE_BLOGS_ID && $comment['p_user_id'] != $comment['user_id']) {
                $admin_user = "Модератор сообщества";
            } else {
                $admin_user = "<a href='{$userlink}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$userlink}'>{$comment['login']}</a>]";
            }
            $body_start = "
{$admin_user} удалил(а) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">комментарий</a> к вашему сообщению/комментарию в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>».
<br/><br/>
--------
";
            $body_subscr =
"{$admin_user} удалил(а) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">комментарий</a> к сообщению/комментарию.
<br/><br/>
--------
";
            $body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".reformat2($comment['msgtext'],100,0,1)."
<br/><br/>
--------
";
$p_body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".str_replace(array("\r", "\n", "<br>", "<br/>"), array("__NEWLINE__", "__NEWLINE__", "__NEWLINE__", "__NEWLINE__" ), $comment['msgtext'])."
--------";

            $p_body = str_replace("__NEWLINE__", "<br/>", $p_body);
            $p_body = str_replace("<br/>", "\n", "\n--------\n".$p_body);
            if($comment['p_user_id'] != $comment['user_id']
                 && $comment['p_email']
                 && substr($comment['p_subscr'],5,1)=='1'
                 && $comment['p_banned'] == '0')
            {
                // отправляем родителю.
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->message = $this->GetHtml($comment['p_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['p_user_id'];
            }

            if($comment['t_user_id']!=$comment['user_id']
                 && $comment['t_user_id']!=$comment['p_user_id']
                 && $comment['t_email']
                 && substr($comment['t_subscr'],5,1)=='1'
                 && $comment['t_banned'] == '0')
            {
                // отправляем автору топика.
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->message = $this->GetHtml($comment['t_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['t_user_id'];
            }

            if(isset($subscribers[$comment['top_id']])) {
                // отправка всем подписчикам топика
                foreach($subscribers[$comment['top_id']] as $user) {
                    // кроме родителя и автора
                    if(in_array($user['user_id'], $skip_users)) continue;
                    
                    $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$comment['commune_id']}' target='_blank'>{$comment['commune_name']}</a>";
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    $this->message = $this->GetHtml($user['uname'], 
                        $body_subscr . $body,
                        array('header' => 'subscribe', 'footer' => 'subscribe'),
                        array('type' => 0, 'title' => $link_commune, 'login' => $user['login']));
                    $this->SmtpMail('text/html');
                }
            }
        }

        return $this->sended;
    }

    /**
     * Отправка уведомления об удалении топика в сообществе
     * Вызывается в тех случаях, когда commune::GetComments4Sending вернула FALSE
     * @param $subscr - массив подписчиков, возвращаемый commune::getThemeSubscribers
     * @param $msg_id - идентификатор темы сообщества или комментария сообщества
     */
    function CommuneDeleteTopic($subscr, $msg_id) {
        $subscribers = array();
        $info = commune::getMessageInfoByMsgID( $msg_id );
        $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$info['commune_id']}' target='_blank'>{$info['commune_name']}</a>";
        $friendly_url_topic = getFriendlyURL('commune', $info["top_id"]); 
        $link_topic = ($info["title"]? "<a href='{$GLOBALS['host']}{$friendly_url_topic}' target='_blank'>{$info['title']}</a>" : '');
        $skip_users = array();
        
        $admin_userlink = $GLOBALS["host"]."/users/".$info['deleter_login'];
        if($info['commune_id'] == commune::COMMUNE_BLOGS_ID && $info["commentator_uid"] != $info["deleter_uid"]) {
            $admin_user = "Модератор сообщества";
        } else {
            $admin_user = "<a href='{$admin_userlink}'>{$info['deleter_uname']} {$info['deleter_usurname']}</a> [<a href='{$admin_userlink}'>{$info['deleter_login']}</a>]";
        }
        
        //отправка автору комментария
        if ($info["commentator_uid"] != $info["deleter_uid"]) {
        	$skip_users[] = $info['commentator_uid'];
	        $this->subject = 'Вашe сообщение в сообществе удалено.';
	        $body_start = "
	{$admin_user} удалил(а) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$info['top_id']}.{$msg_id}{$this->_addUrlParams('b', '&')}#c_{$msg_id}\">ваше сообщение/комментарий</a> в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>».
	<br/><br/>
	--------
	";
	        $body_subscr =
	"{$admin_user} удалил(а) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$info['top_id']}.{$msg_id}{$this->_addUrlParams('b', '&')}#c_{$msg_id}\">сообщение</a> в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>».
	<br/><br/>
	--------
	";
	        $body = 
	"<br/>".reformat2($info['msgtext'],100,0,1)."
	<br/><br/>
	--------
	";
	        $this->recipient = $info['commentator_uname']." ".$info['commentator_usurname']." [".$info['commentator_login']."] <".$info['commentator_email'].">";
	        $this->message = $this->GetHtml($info['commentator_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$info['commentator_login']));
	        $this->SmtpMail('text/html');
        }
        
        //отправка автору топика
        if ( $info && $info['topicstarter_uid'] && $info['topicstarter_uid'] != $info['deleted_id'] && ! in_array($info['topicstarter_uid'], $skip_users))  {
            $this->subject = ($info['parent_id']?'Комментарий к вашему посту':'Ваш пост').($info['title'] ? ' «'.$info['title'].'» в сообществе':' сообщества').' «'.$info['commune_name'].'» удален.';
            $body_start = "
        <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">Комментарий</a> к вашему посту(1) в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>» удален модератором сообщества.
        <br/><br/>
        --------
        ";

            $body = 
        "<br/>".reformat2($info['msgtext'],100,0,1)."
        <br/><br/>
        --------
        ";
            $this->recipient = $info['topicstarter_uname']." ".$info['topicstarter_usurname']." [".$info['topicstarter_login']."] <".$info['topicstarter_email'].">";
            $this->message = $this->GetHtml($info['topicstarter_uname'], 
                        $body_start . $body,
                        array('header' => 'subscribe_delete_post', 'footer' => 'subscribe_delete_post'),
                        array('type' => 0, 'title' => $link_commune, 'topic_name' => $link_topic, 'is_comment' => $info['parent_id'], 'to_topicstarter' => true, 'login' => $info['topicstarter_login'],  'is_author' => ($info['deleter_uid'] == $info['topicstarter_uid']) ));
            $this->SmtpMail('text/html');
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
            $msg = "Здравствуйте, {$info['topicstarter_uname']}.
Модератор сообщества удалил комметарий к Вашему посту {$link_topic} в сообществе {$link_commune}.";
            //messages::Add( users::GetUid($err, 'admin'), $info['topicstarter_login'], $msg, '', 1 );
            $skip_users[] = $info['topicstarter_uid'];
        }
        foreach($subscr as $user) {
            if ( !in_array($user["user_id"], $skip_users) ) {
				$this->subject = ($info['parent_id']?'В топике':'Топик').($info['title'] ? ' «'.$info['title'].'» в сообществе':' сообщества').' «'.$info['commune_name'].'» удален'.($info['parent_id']?' комментарий':'');
				$userlink = $GLOBALS["host"]."/users/".$info['deleter_login'];
				$body_start = "
				{$admin_user} удалил(-а) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">сообщение</a> в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>».
				<br/><br/>
				--------
				";
				
				$body_subscr =
				"{$admin_user} удалил(а) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$this->_addUrlParams('b', '&')}\">сообщение</a>.
				<br/><br/>
				--------
				";
				
				$body = 
				"<br/>".reformat2($info['msgtext'],100,0,1)."
				<br/><br/>
				--------
				";
				$this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
				$this->message = $this->GetHtml($user['uname'], 
								$body_start . $body,
								array('header' => 'subscribe_delete_post', 'footer' => 'subscribe_delete_post'),
								array('type' => 0, 'title' => $link_commune, 'topic_name' => $link_topic, 'is_comment' => $info['parent_id'], 'login' => $user['login'], 'is_admin' => ($info['deleter_id'] == $info['topicstarter_uid']) ));
				$this->SmtpMail('text/html');
				require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
			}
		}
	}
    
    /**
     * Отправляет уведомление при разблокировке в блогах.
     *
     * @param  string|array $ids идентификаторы пользователей
     * @param  resource $connect соединение к БД (необходимо в PgQ) или NULL -- создать новое
     * @return integer количество отправленных уведомлений
     */
    function UserRazban($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $user          = new users();
        $this->subject = 'Доступ в Сообщества на FL.ru разблокирован';
        $message       = 'Команда FL.ru разблокировала Вам доступ в сервис Сообщества.';
        
        foreach ( $ids as $id ) {
            $user->GetUserByUID( $id );
            $to_user = array(
                'usurname' => $user->usurname, 
                'uname'    => $user->uname, 
                'login'    => $user->login, 
                'photo'    => $user->photo,
                'email'    => $user->email
            ); 
            
            $this->message   = $this->GetHtml( $to_user['uname'], $message, 'info' );
            $this->recipient = $to_user['uname'].' '.$to_user['usurname'].' ['.$to_user['login'].'] <'.$to_user['email'].'>';
            
            $this->SmtpMail('text/html');
        }
        
        return $this->sended; 
    }

    
    /**
     * Уведомление о пополнении счета
     * 
     * @param  string|array $operation_ids идентификаторы операций с пользовательскими счетами
     * @param  resource $connect соединение к БД (необходимо в PgQ) или NULL -- создать новое
     * @return integer количество отправленных уведомлений
     */
    function DepositMail( $operation_ids, $connect = NULL ) {
        return; //##0027187
        global $host;
        if ( !empty($operation_ids) ) {
            $operation_ids = is_array($operation_ids) ? array_unique($operation_ids) : array($operation_ids);
        	
        	$sQuery = "SELECT ao.ammount, ao.trs_sum, ao.balance, u.uname, u.usurname, u.login, u.email FROM account_operations ao
        	   INNER JOIN account a ON a.id = ao.billing_id 
        	   INNER JOIN users u ON u.uid = a.uid 
        	   WHERE ao.id IN (?l) AND u.is_banned = '0' AND substr(u.subscr::text,16,1) = '1' AND u.is_active = true";
        	
        	$mRes = $GLOBALS['DB']->query( $sQuery, $operation_ids );
        	
        	if ( !$GLOBALS['DB']->error && pg_num_rows($mRes) ) {
                    while ( $aOne = pg_fetch_assoc($mRes) ) {
                        $this->subject   = 'Пополнение вашего счета на FL.ru';
                        $this->recipient = $aOne['uname']." ".$aOne['usurname']." [".$aOne['login']."] <".$aOne['email'].">";;

                        $message =
'На ваш личный счет была зачислена сумма ' . number_format($aOne['trs_sum'], 2, ',', ' ') . ' руб.<br />
<br />
С подробной информацией по управлению услугами и личным счетом на FL.ru вы можете ознакомиться в нашем <a href="https://feedback.fl.ru/'.$this->_addUrlParams('b', '?').'">сообществе поддержки</a>.<br />
<br />
По всем возникающим вопросам обращайтесь в нашу <a href="https://feedback.fl.ru/' . $this->_addUrlParams('b', '?') . '">службу поддержки</a>.';
                        $this->message = $this->GetHtml(($aOne['uname'] ? $aOne['uname'] : $aOne['login']), $message, array('header' => 'default', 'footer' => 'default'), array('login' => $aOne['login']));
                        $this->message = str_replace('%USER_NAME%', ($aOne['uname'] ? $aOne['uname'] : $aOne['login']), $this->message);
                        $this->send( 'text/html' );
                    }
                }
         }
    }
    
    /**
     * Отправляет уведомления о новых комментариях к действиям модераторов.
     * 
     * @param  string|array $message_ids идентификаторы комментариев
     * @param  resource $connect соединение к БД (необходимо в PgQ) или NULL -- создать новое
     * @return integer количество отправленных уведомлений
     */
    function AdminLogCommentsMail( $message_ids, $connect = NULL ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
        
        $admin_log = new admin_log();
        $noSend    = array();
        
        if ( !$comments = $admin_log->GetComments4Sending($message_ids, $connect) ) {
            return NULL;
        }
        
        $this->subject = 'Комментарии к действию модератора на сайте FL.ru';
        
        foreach( $comments as $comment ) {
            $sObjEntity = admin_log::$aObj[$comment['obj_code']]['name'];
            $sObjName   = $comment['object_name'] ? $comment['object_name'] : '<без названия>';
            setlocale(LC_ALL, 'ru_RU.CP1251');
            $sObjName   = str_replace(array('<','>'), array('&lt;', '&gt;'), $sObjName );
            setlocale(LC_ALL, "en_US.UTF-8");
            $sObjLink   = $comment['object_link'] ? '<a href="'.$comment['object_link'].$this->_addUrlParams('b').'">'.$sObjName.'</a>' : $sObjName;
            
            // отправляем автору родительского коментария
            if ( 
                $comment['s_uid'] != $comment['uid']
                && $comment['s_email']
                && $comment['s_banned'] == '0'
            ) {
                $this->message = $this->GetHtml($comment['s_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
оставил(а) вам комментарии к действию модератора на сайте FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
$sObjEntity: $sObjLink<br />
<br />
<a href='{$GLOBALS['host']}/siteadmin/admin_log/?view={$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['comment_id']}'>{$GLOBALS['host']}/siteadmin/admin_log/?view={$comment['id']}#c_{$comment['comment_id']}</a>
<br />
<br />
", array('header'=>'simple', 'footer'=>'simple') );
                $this->recipient = $comment['s_uname']." ".$comment['s_usurname']." [".$comment['s_login']."] <".$comment['s_email'].">";
                $this->SmtpMail( 'text/html' );
                $noSend[ $comment['s_uid'] ] = $comment['s_uid'];
            }
            
            // отправляем автору действия
            if ( 
                $comment['a_uid'] != $comment['uid']
                && $comment['a_uid'] != $comment['s_uid']
                && $comment['a_email']
                && $comment['a_banned'] == '0' 
            ) {
                $this->message = $this->GetHtml($comment['s_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
оставил(а) вам комментарии к действию модератора на сайте FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
$sObjEntity: $sObjLink<br />
<br />
<a href='{$GLOBALS['host']}/siteadmin/admin_log/?view={$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['comment_id']}'>{$GLOBALS['host']}/siteadmin/admin_log/?view={$comment['id']}#c_{$comment['comment_id']}</a>
<br />
<br />
", array('header'=>'simple', 'footer'=>'simple') );
                $this->recipient = $comment['a_uname']." ".$comment['a_usurname']." [".$comment['a_login']."] <".$comment['a_email'].">";
                $this->SmtpMail( 'text/html' );
                $noSend[ $comment['a_uid'] ] = $comment['a_uid'];
            }
            
            // подписка пока не реализована
        }
        
        return $this->sended;
    }
    
    /**
     * Отправляет уведомления о новых действиях модераторов
     * 
     * @param  string|array $message_ids идентификаторы комментариев
     * @param  resource $connect соединение к БД (необходимо в PgQ) или NULL -- создать новое
     * @return integer количество отправленных уведомлений
     */
    function AdminLogNotice( $log_ids, $connect = NULL ) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/admin_log.php";
        
        $admin_log = new admin_log();
        
        if ( !$comments = $admin_log->GetNotices4Sending($log_ids, $connect) ) {
            return NULL;
        }
        
        $this->subject = 'Новое действие модератора на сайте FL.ru';
        
        foreach( $comments as $aOne ) {
            if ( 
                hasPermissions($aOne['rights'], $aOne['notice_uid']) 
                && $aOne['notice_uid'] != $aOne['a_uid'] 
            ) {
                $sObjEntity = admin_log::$aObj[$aOne['obj_code']]['name'];
                $sObjName   = $aOne['object_name'] ? $aOne['object_name'] : '<без названия>';
                setlocale(LC_ALL, 'ru_RU.CP1251');
                $sObjName   = str_replace(array('<','>'), array('&lt;', '&gt;'), $sObjName );
                setlocale(LC_ALL, "en_US.UTF-8");
                
                if ( $aOne['object_link'] ) {
                	$sObjLink = '<a href="' . getAbsUrl( $aOne['object_link'] ) . '">' . $sObjName . '</a>';
                }
                else {
                    $sObjLink = $sObjName;
                }
                
            	$this->message = $this->GetHtml( $aOne['uname'], "
Новое действие модератора:<br/>
<a href='{$GLOBALS['host']}/users/{$aOne['a_login']}{$this->_addUrlParams('b')}'>{$aOne['a_uname']} {$aOne['a_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$aOne['a_login']}{$this->_addUrlParams('b')}'>{$aOne['a_login']}</a>]
<br/>
$sObjEntity: $sObjLink<br />
Действие: {$aOne['act_name']}<br />
<br />
<a href='{$GLOBALS['host']}/siteadmin/admin_log/?view={$aOne['id']}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/siteadmin/admin_log/?view={$aOne['id']}</a>
<br />
<br />
            	", array('header'=>'simple', 'footer'=>'simple') );
                
            	$this->recipient = $aOne['uname']." ".$aOne['usurname']." [".$aOne['login']."] <".$aOne['email'].">";
                $this->SmtpMail( 'text/html' );
            }
        }
        
        return $this->sended;
    }
    
    /**
     * Отправляет уведомления о новых комментариях в блоге.
     * 
     * @param   string|array   $message_ids  идентификаторы комментариев.
     * @param   resource       $connect      соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                      количество отправленных уведомлений.
     */
    function BlogNewComment($message_ids, $connect = NULL)
    {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";

        $blogs = new blogs();
        if(!($comments = $blogs->GetComments4Sending($message_ids, $connect))){
            return NULL;
        }
        
        $this->subject = "Комментарии в сообществе на сайте FL.ru";
        
        $userSubscribe = $blogs->getUsersSubscribe($message_ids, $connect);
        foreach($comments as $comment)
        {
            // Отправляем родителю.
            if( substr($comment['p_subscr'], 2, 1) == '1' 
                && $comment['p_uid'] != $comment['uid']
                && $comment['p_email']
                && $comment['p_banned'] == '0')
            {
                $this->message = $this->GetHtml($comment['p_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}/{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
оставил(-а) <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>комментарий</a> к вашим сообщениям/комментариям в сообществе на сайте FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
", array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->SmtpMail('text/html');
                $notSend[$comment['p_uid']] = $comment['p_uid'];
            }
            // Отправляем автору топика.
            if( substr($comment['t_subscr'], 2, 1) == '1' 
                    && $comment['t_uid'] != $comment['uid']
                    && $comment['t_uid'] != $comment['p_uid']
                    && $comment['t_email']
                    && $comment['t_banned'] == '0' )
            {
                $this->message = $this->GetHtml($comment['t_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
оставил(-а) <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>комментарий</a> к вашим сообщениям/комментариям в сообществе на сайте FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
", array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->SmtpMail('text/html');
                $notSend[$comment['t_uid']] = $comment['t_uid'];
            }
        }

        // Посылаем подписавшимся на темы  
        if($userSubscribe)
        foreach($userSubscribe as $comment) {
            $this->subject = "Комментарии в блогах на сайте FL.ru";
           
            if( substr($comment['s_subscr'], 2, 1) == '1' 
                && !$notSend[$comment['s_uid']] 
                && $comment['s_uid'] != $comment['uid'] 
                && $comment['s_email'])
            {
                $link_title = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}{$this->_addUrlParams('b', '&')}' target='_blank'>" . ( $comment['blog_title'] == ''? 'Без названия' : $comment['blog_title'] )  ."</a>";  
                $this->message = $this->GetHtml($comment['s_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}/{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
оставил(-а) <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>новый комментарий</a> к сообщениям/комментариям в сообществе на сайте FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(input_ref(LenghtFormatEx($comment['title'], 300), 1))."<br />---<br />"): "")
.$this->ToHtml(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
<br /> --------
<br />
", array('header' => 'subscribe', 'footer' => 'subscribe'), array('type' => 1, 'title' => $link_title));
                $this->recipient = $comment['s_uname']." ".$comment['s_usurname']." [".$comment['s_login']."] <".$comment['s_email'].">";
                $this->SmtpMail('text/html');  
            }
        }
          
        return $this->sended;
    }

/**
     * Отправляет уведомления о редактировании комментария в блоге.
     * 
     * @param   string|array   $message_ids  идентификаторы комментариев.
     * @param   resource       $connect      соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                      количество отправленных уведомлений.
     */
    function BlogUpdateComment($message_ids, $connect = NULL)
    {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        $blogs = new blogs();
        if(!($comments = $blogs->GetComments4Sending($message_ids, $connect))) {
            return NULL;
        }
        $this->subject = "Комментарии в сообществе на сайте FL.ru";
        
        $userSubscribe = $blogs->getUsersSubscribe($message_ids, $connect, true);
        foreach($comments as $comment)
        {
            // Отправляем родителю.
            if( substr($comment['p_subscr'], 2, 1) == '1' 
                && ( $comment['p_uid'] != $comment['uid'] || $comment['uid'] != $comment['modified_id'] )
                && $comment['p_email']
                && $comment['p_banned'] == '0')
            {
                $this->message = $this->GetHtml($comment['p_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['m_login']}/{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
отредактировал(а) <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>комментарий</a> к вашим сообщениям/комментариям в сообществе на сайте FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
", array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->SmtpMail('text/html');
                $notSend[$comment['p_uid']] = $comment['p_uid'];
            }
            // Отправляем автору топика.
            if( substr($comment['t_subscr'], 2, 1) == '1' 
                    && ( $comment['t_uid'] != $comment['uid'] || $comment['t_uid'] != $comment['modified_id'] )
                    && ( $comment['t_uid'] != $comment['p_uid'] || $comment['t_uid'] != $comment['modified_id'] )
                    && $comment['t_email']
                    && !$notSend[$comment['t_uid']]
                    && $comment['t_banned'] == '0' )
            {
                $post_type = "<a target='_blank' href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>комментарий</a> к вашим сообщениям/комментариям";
                if ( $comment['reply_to'] == '' ) {
                    $post_type = "<a target='_blank' href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>ваше сообщение</a> ";
                    $this->subject = "Блоги FL.ru";
                }
                $this->message = $this->GetHtml($comment['t_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
отредактировал(а) {$post_type} в блогах на сайте FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
", array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->SmtpMail('text/html');
                $notSend[$comment['t_uid']] = $comment['t_uid'];
                $message = "<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
отредактировал(а) {$post_type} в сообществе на сайте FL.ru.
 --------

"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."
---
"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
 --------";
                messages::Add( users::GetUid($err, 'admin'), $comment['t_login'], $message, '', 1 );
            }
        }
        // Посылаем подписавшимся на темы  
        if($userSubscribe)
        foreach($userSubscribe as $comment) {
            $this->subject = "Комментарии в сообществе на сайте FL.ru";
            if( substr($comment['s_subscr'], 2, 1) == '1' 
                && !$notSend[$comment['s_uid']] 
                && $comment['s_email'])
            {
                $post_type = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>комментарий</a> к сообщениям/комментариям в сообществе";
                $message_template = "subscribe_edit_comment";
                if ( $comment['reply_to'] == '' ) {
                    $post_type = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>пост в сообществе</a> на который вы подписаны";
                    $message_template = "subscribe_edit_post";
                }
                $link_title = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}{$this->_addUrlParams('b', '&')}' target='_blank'>" . ( $comment['blog_title'] == ''? 'Без названия' : $comment['blog_title'] )  ."</a>";  
                $this->message = $this->GetHtml($comment['s_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['m_login']}/{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
отредактровал(а) {$post_type} на сайте FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(input_ref(LenghtFormatEx($comment['title'], 300), 1))."<br />---<br />"): "")
.$this->ToHtml(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
<br /> --------
<br />
", array('header' => $message_template, 'footer' => 'subscribe'), array('type' => 1, 'title' => $link_title));
                $this->recipient = $comment['s_uname']." ".$comment['s_usurname']." [".$comment['s_login']."] <".$comment['s_email'].">";
                $this->SmtpMail('text/html');
                $message = "Здравствуйте, ".$comment['s_uname'].".                
<a href='{$GLOBALS['host']}/users/{$comment['m_login']}/{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
отредактровал(а) {$post_type} на сайте FL.ru.
--------"
.($comment['title']? ($this->ToHtml(input_ref(LenghtFormatEx($comment['title'], 300), 1))."
---
"): "")
.$this->ToHtml(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
 --------
 ";
                messages::Add( users::GetUid($err, 'admin'), $comment['s_login'], $message, '', 1 );
            }
        }
          
        return $this->sended;
    }
	
    /**
     * Отправляет уведомления о смене сроков в конкурсах
     * 
     * @param   string|array    $ids        идентификаторы конкурсов
     * @param   resource        $connect    соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                     количество отправленных уведомлений.
     */
	function ContestChangeDates($ids, $connect = NULL) {
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';

		$contest = new contest(0, 0);
		if (!($prjs = $contest->GetContests4Sending($ids))) return NULL;

		$emp = new employer();
		$emp->GetUserByUID($prjs[0]['user_id']);
		
		foreach ($prjs as $prj) {
			if ($prj['email'] && substr($prj['subscr'], 8, 1) == '1' && $prj['is_banned'] == '0') {
                $prj['name'] = htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false);
				$userlink = HTTP_PREFIX."{$GLOBALS['host']}/users/{$emp->uname}";
				$this->message = $this->GetHtml($prj['uname'], "
					Заказчик <a href=\"{$userlink}\">{$emp->uname} {$emp->usurname}</a> [<a href=\"{$userlink}\">{$emp->login}</a>] изменил(a) сроки конкурса
					«<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."\">".$prj['name']."</a>».
                    Вы можете перейти к своей <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."?offer={$prj['offer_id']}{$this->_addUrlParams('f', '&')}#offer-{$prj['offer_id']}\">работе</a>.
					<br /><br/>
					Дата завершения конкурса: ".dateFormat("d.m.Y", $prj['end_date'])."<br />
					Дата объявления победителей: ".dateFormat("d.m.Y", $prj['win_date'])."<br />
                    ", array('header'=>'simple', 'footer'=>'frl_subscr_projects'), array('login'=>$prj['login']));
				$this->recipient = "{$prj['uname']} {$prj['usurname']} [{$prj['login']}] <{$prj['email']}>";
				$this->subject = 'Сроки конкурса «'.htmlspecialchars_decode($prj['name'], ENT_QUOTES).'» были изменены';
				$this->send('text/html');
				++$count;
			}
		}
		
		return $this->sended;

	}
	
	/**
     * Отправляет уведомление автору проекта о новом отклике.
     *
     * @param   string|array    $ids        идентификаторы ответов к проекту
     * @param   resource        $connect    соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                     количество отправленных уведомлений.
     */
    function NewPrjOffer($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php';
        $offers = new projects_offers();
        
        if (!($ofs = $offers->getNewProjectOffers($ids, $connect))) return NULL;
        
        //$this->subject = "Фрилансер ответил на проект";
        foreach($ofs as $offer) {            
            $offer['project_name'] = html_entity_decode($offer['project_name'], ENT_QUOTES);
            
            if($offer['kind'] == 7 OR $offer['kind'] == 2) {
                if (!$offer['to_email'] || substr($offer['to_subscr'], 8, 1) != '1') continue; // если не нужны уведомления пропускаем отсылку
                $this->subject = "Новая работа добавлена в конкурс «{$offer['project_name']}»";
        		$this->recipient = " {$offer['to_uname']} {$offer['to_usurname']} [{$offer['to_login']}] <".$offer['to_email'].">";		
        		$userlink = $GLOBALS["host"]."/users/".$offer['from_login'];
        		$this->message = $this->GetHtml($offer['to_uname'], "
        		    <a href=\"{$userlink}\">{$offer['from_uname']} {$offer['from_usurname']}<a/> [<a href=\"{$userlink}\">{$offer['from_login']}</a>] добавил(a) новую работу
        			в&nbsp;конкурс «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $offer['project_id'])."?offer={$offer['id']}{$this->_addUrlParams('e', '&')}\">" . $offer['project_name'] . "</a>».
        			<br />", array('header' => 'default', 'footer' => 'default'), array('login'=>$offer['to_login']));
        		$this->SmtpMail('text/html');  
                //++$count;   
            } else {
                
                $_blocked_txt = '';
                
                //Если это перенесенная вакансия и она не оплачена
                //то скрываем уведомление об ответе
                if($offer['kind'] == 4 && 
                   $offer['state'] == 1 && 
                   $offer['payed'] == 0) {
                    
                    $url_vacancy = sprintf('%s/public/?step=1&kind=4&public=%s&popup=1', $GLOBALS['host'], $offer['project_id']);
                    
                    $_blocked_txt = '
                        Фрилансер ответил на опубликованный вами проект «<a href="'
                            . $GLOBALS['host'] 
                            . getFriendlyURL("project", $offer['project_id']) 
                            . $this->_addUrlParams('e') . '">'
                            . $offer['project_name'] . '</a>».
                        <br/>
                        <br/>
                        ------------
                        <br/>
                        Текст ответа временно скрыт.
                        <br/>
                        ------------
                        <br/>
                        <br/>
                        Для того, чтобы видеть ответы фрилансеров и иметь возможность выбрать исполнителя, пожалуйста, 
                        перейдите в вакансию и оплатите ее размещение.
                        <br/>
                        <br/>
                        <a href="'.$url_vacancy.'">Оплатить размещение вакансии</a>
                    ';
                }
                
                
                $userlink = $GLOBALS["host"]."/users/".$offer['from_login'];
                if (!$offer['to_email'] || substr($offer['to_subscr'], 1, 1) != '1') continue; // если не нужны уведомления пропускаем отсылку
                $this->subject = "Фрилансер ответил на проект «".html_entity_decode($offer['project_name'], ENT_QUOTES)."»";
                
                $body = empty($_blocked_txt)?"Фрилансер <a href=\"{$userlink}\">{$offer['from_uname']}</a> <a href=\"{$userlink}\">{$offer['from_usurname']}</a> [<a href=\"{$userlink}\">{$offer['from_login']}</a>] "."<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $offer['project_id']).$this->_addUrlParams('e')."#freelancer_".$offer['user_id']."\">"."ответил</a> на опубликованный вами проект
                «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $offer['project_id']).$this->_addUrlParams('e')."\">" . $offer['project_name'] . "</a>».
                <br/>
                <br/>
                ------------
                <br/>
                ".html_entity_decode(strip_tags(input_ref(LenghtFormatEx($offer['description'], 300), 1)), ENT_COMPAT, "CP1251")."
                <br/>
                ------------":$_blocked_txt;
                $this->recipient = "{$offer['to_uname']} {$offer['to_usurname']} [{$offer['to_login']}] <{$offer['to_email']}>";
                $this->message   = $this->GetHtml($offer['to_uname'], $body, array('header' => 'default', 'footer' => 'sub_emp_projects'), array('login'=>$offer['to_login']));
                $this->SmtpMail('text/html');  
                //++$count;
            }
        }
        
        return $this->sended;
    }

	/**
     * Отправляет уведомление автору проекта о новом сообщении от юзера, ранее ответившего на данный проект.
     *
     * @param   string|array    $ids        идентификаторы ответов автору проекта
     * @param   resource        $connect    соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                     количество отправленных уведомлений.
     */
    function NewPrjMessageOnOffer($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php';
        $offers = new projects_offers();
        
        if (!($dialog = $offers->getNewPrjMessageOnOffer($ids, $connect))) return NULL;
        
        foreach($dialog as $offer) { 
            $project_name = $offer['project_name'];
            $project_id   = $offer['project_id'];
            $msg          = $offer['msg'];
            
            if($offer['usr_dialog'] == $offer['emp_uid']) {
				if (!$offer['frl_email'] || substr($offer['frl_subscr'], 4, 1) != '1') continue; // если не нужны уведомления пропускаем отсылку
                $this->subject = "Новое сообщение по проекту «" . html_entity_decode($project_name) . "»";
                
                //Если не исполнитель и не ПРО то скрываем контакты заказчика в уведомлении
                $emp_contact = '';
                if (isset($offer['is_view_contacts']) && $offer['is_view_contacts'] == 't') {
                    $userlink = $GLOBALS["host"]."/users/".$offer['emp_login'];
                    $emp_contact = "<a href=\"{$userlink}\">{$offer['emp_name']}</a> <a href=\"{$userlink}\">{$offer['emp_uname']}</a> [<a href=\"{$userlink}\">{$offer['emp_login']}</a>] ";
                }
                
                $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
                
                $body = "Заказчик {$emp_contact}оставил(а) вам новое сообщение по проекту «<a href='{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('f')."#freelancer_".$offer['frl_uid']."'>{$project_name}</a> ».
                        <br/><br/>
                        ------
                        <br/>
                        ".(html_entity_decode(strip_tags(input_ref(LenghtFormatEx($msg, 300), 1)), ENT_COMPAT, "CP1251")."\n")."
                        <br/>
                        ------";
                $this->recipient = "{$offer['frl_name']} {$offer['frl_uname']} [{$offer['frl_login']}] <".$offer['frl_email'].">";
                $this->message   = $this->GetHtml($offer['frl_name'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$offer['frl_login']));
                
				$this->SmtpMail('text/html');
                //++$count;   
            } else {
				if (!$offer['emp_email'] || substr($offer['emp_subscr'], 4, 1) != '1') continue; // если не нужны уведомления пропускаем отсылку
				$this->subject = "Новое сообщение по проекту «" . html_entity_decode($project_name) . "»";
				$userlink = $GLOBALS["host"]."/users/".$offer['frl_login'];
                
                $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
                
                $body = "Фрилансер <a href=\"{$userlink}\">{$offer['frl_name']}</a> <a href=\"{$userlink}\">{$offer['frl_uname']}</a> [<a href=\"{$userlink}\">{$offer['frl_login']}</a>] оставил(а) вам <a href='{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('e')."#comment".$offer['spoiler_id']."'>" . "новое сообщение </a> по опубликованному вами проекту «<a href='{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('e')."'>{$project_name}</a>».
                        <br/><br/>
                        ------
                        <br/>
                        ".(html_entity_decode(strip_tags(input_ref(LenghtFormatEx($msg, 300), 1)), ENT_COMPAT, "CP1251")."\n")."
                        <br/>
                        ------";
                $this->recipient = "{$offer['emp_name']} {$offer['emp_uname']} [{$offer['emp_login']}] <".$offer['emp_email'].">";
                $this->message = $this->GetHtml($offer['emp_name'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$offer['emp_login']));
				$this->SmtpMail('text/html');
                //++$count;
            }
        }
        
        return $this->sended;
    }
    
    /**
     * Отправляет Уведомления о добавлении в избранные.
     *
     * @param   integer    $from_id        ID пользователя кто добавляет
     * @param   integer    $target_id      ID пользователя кого добавляют
     * @return  integer                    количество отправленных уведомлений
     */
    function addTeamPeople($from_id, $target_id) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $f_user = new users();
        $t_user = new users();
        
        $f_user->GetUserByUID($from_id);
        $t_user->GetUserByUID($target_id);

        if (!$t_user->email || substr($t_user->subscr, 9, 1) != '1' || $t_user->is_banned == '1') return 0; // если не нужны уведомления пропускаем отсылку
        $this->subject = "Вас добавили в «Избранные» на FL.ru";
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";		
        		
        if(is_emp($f_user->role)) $name = "Работодатель"; 
        else $name = "Фрилансер";
            
        $message = $name." <a href='{$GLOBALS['host']}/users/{$f_user->login}/{$this->_addUrlParams('b')}' target='_blank'>{$f_user->uname} {$f_user->usurname} [{$f_user->login}]</a>  добавил вас в «Избранные» на своей личной странице на <a href=\"{$GLOBALS['host']}/{$this->_addUrlParams('b')}\">FL.ru</a>. 
        <br/><br/>
        --------
        <br/>
        <a href=\"{$GLOBALS['host']}/users/{$f_user->login}/info/{$this->_addUrlParams('b')}\">Посмотреть</a><br/>
        --------
        <br/><br/>
        ";
     	$this->message = $this->GetHtml($t_user->uname, $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$t_user->login));  
        $this->send('text/html');
        
        return $this->sended;
    }
    
    /**
     * Отправляет Уведомления о удалении из избранные.
     *
     * @param   integer    $from_id         ID пользователя кто удаляет
     * @param   integer    $target_id       ID пользователя кого удаляют
     * @return  integer                     количество отправленных уведомлений.
     */
    function delTeamPeople($from_id, $target_id) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $f_user = new users();
        $t_user = new users();
        
        $f_user->GetUserByUID($from_id);
        $t_user->GetUserByUID($target_id);
            
        if (!$t_user->email || substr($t_user->subscr, 9, 1) != '1' || $t_user->is_banned == '1') return; // если не нужны уведомления пропускаем отсылку
        $this->subject = "Вас удалили из «Избранных» на FL.ru";
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";		
        		
        if(is_emp($f_user->role)) $name = "Работодатель"; 
        else $name = "Фрилансер";
            
        $message = $name." <a href='{$GLOBALS['host']}/users/{$f_user->login}/{$this->_addUrlParams('b')}' target='_blank'>{$f_user->uname} {$f_user->usurname} [{$f_user->login}]</a>  удалил(а) вас из «Избранных» на своей личной странице на сайте <a href=\"{$GLOBALS['host']}/{$this->_addUrlParams('b')}\">FL.ru</a><br/><br/>";
            
        $this->message = $this->GetHtml($t_user->uname, $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$t_user->login));  
        $this->send('text/html');  
        
        return $this->sended; 
    }
    
    /**
     * Отсылает сообщение фрилансеру о добавлении комментария к его предложению в конкурсе
     *
     * @param   string|array    $ids        идентификаторы новых комментариев
     * @param   resource        $connect    соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                     количество отправленных уведомлений.
     */
    function ContestNewComment($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
        
        $contest = new contest(0, 0);
		if (!($comments = $contest->getContestNewComment($ids, $connect))) return NULL;
        
        foreach($comments as $comment) {
            // Письмо организатору конкурса, если подписан и не он оставил коммент 
            if ( substr($comment['p_subscr'], 8, 1) == '1' 
                && $comment['p_uid'] != $comment['uid'] 
                && $comment['p_email'] 
                && $comment['p_banned'] == '0' 
            ) {
                $this->subject = 'Комментарии в вашем конкурсе «'.htmlspecialchars_decode($comment['project_name'], ENT_QUOTES).'» на сайте FL.ru';
                
                $comment['project_name'] = htmlspecialchars($comment['project_name'], ENT_QUOTES, 'CP1251', false);
                
                $body = '<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('e').'">'.$comment['uname'].' '.$comment['usurname'].'</a> [<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('e').'">'.$comment['login'].'</a>] 
                оставил(а) комментарий на <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?offer='.$comment['offer_id'].$this->_addUrlParams('e', '&').'#offer-'.$comment['offer_id'].'">работу</a> 
                в вашем конкурсе «<a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).$this->_addUrlParams('e').'">'.$comment['project_name'].'</a>». 
                Ознакомиться с данным <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?comm='.$comment['comment_id'].$this->_addUrlParams('e', '&').'#comment-'.$comment['comment_id'].'">комментарием</a> можно на странице конкурса.';
                
                $this->message   = $this->GetHtml( $comment['p_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']) );
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                
                $this->SmtpMail( 'text/html' );
            }
            
            // Письмо автору предложения, если подписан и не он оставил коммент 
            if ( substr($comment['o_subscr'], 8, 1) == '1' 
                && $comment['o_uid'] != $comment['uid'] 
                && $comment['o_email'] 
                && $comment['o_banned'] == '0' 
            ) {
            	$this->subject = 'Вашу работу в конкурсе «'.htmlspecialchars_decode($comment['project_name'], ENT_QUOTES).'» прокомментировали';
            	
                $comment['project_name'] = htmlspecialchars($comment['project_name'], ENT_QUOTES, 'CP1251', false);
                
                $body = '<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('f').'">'.$comment['uname'].' '.$comment['usurname'].'</a> [<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('f').'">'.$comment['login'].'</a>] 
                прокомментировал(a) вашу <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?offer='.$comment['offer_id'].$this->_addUrlParams('f', '&').'#offer-'.$comment['offer_id'].'">работу</a> 
                в&nbsp;конкурсе «<a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).$this->_addUrlParams('f').'">' . $comment['project_name'] . '</a>».
                Ознакомиться с данным <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?comm='.$comment['comment_id'].$this->_addUrlParams('f', '&').'#comment-'.$comment['comment_id'].'">комментарием</a> можно на странице конкурса.';
                
                $this->message   = $this->GetHtml( $comment['o_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['o_login']) );
                $this->recipient = $comment['o_uname']." ".$comment['o_usurname']." [".$comment['o_login']."] <".$comment['o_email'].">";
                
                $this->SmtpMail( 'text/html' );
            }
            
            // Письмо автору родительского комментария, если нужно 
            if ( substr($comment['m_subscr'], 8, 1) == '1' 
                && $comment['m_uid'] != $comment['uid'] 
                && $comment['m_uid'] != $comment['p_uid'] 
                && $comment['m_uid'] != $comment['o_uid'] 
                && $comment['m_email'] 
                && $comment['m_banned'] == '0' 
            ) {
            	$this->subject = 'Комментарии в конкурсе "'.htmlspecialchars_decode($comment['project_name'], ENT_QUOTES).'" на сайте FL.ru';
            	
                $comment['project_name'] = htmlspecialchars($comment['project_name'], ENT_QUOTES, 'CP1251', false);
                
            	$body = '<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('b').'">'.$comment['uname'].' '.$comment['usurname'].'</a> [<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('b').'">'.$comment['login'].'</a>] 
                оставил(а) вам комментарий в конкурсе <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).$this->_addUrlParams('b').'">"'.$comment['project_name'].'"</a>. 
                <br/>Вы можете прочитать данный <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?comm='.$comment['comment_id'].$this->_addUrlParams('b').'#comment-'.$comment['comment_id'].'">комментарий</a>.';
                
                $this->message   = $this->GetHtml( $comment['m_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['m_login']) );
                $this->recipient = $comment['m_uname']." ".$comment['m_usurname']." [".$comment['m_login']."] <".$comment['m_email'].">";
                
                $this->SmtpMail( 'text/html' );
            }
        }
        
        return $this->sended;    
    }
    
    /**
     * Отправляет уведомление о новом отзыве.
     *
     * @param   string|array    $ids        идентификаторы новых отзывов
     * @param   resource        $connect    соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                     количество отправленных уведомлений.
     */
    function NewOpinion($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/opinions.php';
        
        $opin = new opinions(0, 0);
		if (!($opinions = $opin->getNewOpinion($ids, $connect))) return NULL;
        
        foreach($opinions as $opinion) {
            if (!$opinion['t_email'] || substr($opinion['t_subscr'],3,1) != '1' || $opinion['t_banned'] == '1') continue; // если не нужны уведомления пропускаем отсылку
            
            switch ($opinion['rating']) {
                case 0:
                    $type_text = "нейтральный";
                    break;
                case 1:
                    $type_text = "положительный";
                    break;
                case -1:
                    $type_text = "отрицательный";
                    break;
            }
            
            if (substr($opinion['f_role'],0,1)=='1') { $path= "/users/".$opinion["t_login"]."/opinions/"; }
            else { $path= "/users/".$opinion["t_login"]."/opinions/?from=frl"; }

            $body = "Пользователь <a href='{$GLOBALS['host']}/users/{$opinion['f_login']}{$this->_addUrlParams('b')}'>".$opinion["f_uname"]." ".$opinion["f_usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$opinion['f_login']}{$this->_addUrlParams('b')}'>".$opinion["f_login"]."</a>]
оставил(а) $type_text отзыв о вас.<br />
Вы можете ознакомиться с <a href='{$GLOBALS['host']}{$path}{$this->_addUrlParams('b', '&')}'>новым отзывом</a> на странице вашего аккаунта.";
            
            $this->message = $this->GetHtml($opinion["t_uname"], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$opinion['t_login']));
            $this->from = "FL.ru <administration@fl.ru>";
            $this->subject = "Новый отзыв на FL.ru";
            $this->recipient = "{$opinion['t_uname']} {$opinion['t_usurname']} [{$opinion['t_login']}] <".$opinion['t_email'].">";
            
            $this->SmtpMail('text/html');
        }
        
        return $this->sended;    
    }
    
   /**
     * Отправляет уведомление о редактировании отзыва.
     *
     * @param   string|array    $ids        идентификаторы  отзывов
     * @param   resource        $connect    соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                     количество отправленных уведомлений.
     */
    function EditOpinion($ids, $connect = NULL) {
         require_once $_SERVER['DOCUMENT_ROOT'].'/classes/opinions.php';
         
         $opin = new opinions(0, 0);
         if (!($opinions = $opin->getNewOpinion($ids, $connect))) return NULL;
         
         foreach($opinions as $opinion) {
            if (!$opinion['t_email'] || substr($opinion['t_subscr'],3,1) != '1' || $opinion['t_banned'] == '1') continue; // если не нужны уведомления пропускаем отсылку
            
            $path= "/users/{$opinion['t_login']}/opinions/?from=" . ( substr($opinion['f_role'],0,1)=='1' ? 'emp' : 'frl' ); 
            
            if ( !$opinion['modified_id'] || $opinion['modified_id'] == $opinion['f_uid'] ) { // отзыв редактирует автор
                switch ($opinion['rating']) {
                    case 0:
                        $type_text = "нейтральный";
                        break;
                    case 1:
                        $type_text = "положительный";
                        break;
                    case -1:
                        $type_text = "отрицательный";
                        break;
                }
    
                $body = "Пользователь <a href='{$GLOBALS['host']}/users/{$opinion['f_login']}{$this->_addUrlParams('b')}'>".$opinion["f_uname"]." ".$opinion["f_usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$opinion['f_login']}{$this->_addUrlParams('b')}'>".$opinion["f_login"]."</a>]
оставил(а) $type_text отзыв о вас.<br />
Вы можете прочитать его на странице вашего аккаунта - <a href='{$GLOBALS['host']}{$path}{$this->_addUrlParams('b', '&')}'>".$GLOBALS["host"].$path."</a>";
            }
            else { // отзыв редактирует админ
                $body = "Модератор отредактировал отзыв по Безопасной Сделке.
<br />
<br />
Вы можете прочитать его на странице вашего аккаунта - <a href='{$GLOBALS['host']}{$path}{$this->_addUrlParams('b', '&')}'>".$GLOBALS["host"].$path."</a>";
            }
            
            $this->message   = $this->GetHtml($opinion["t_uname"], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$opinion['t_login']));
            $this->from      = "FL.ru <administration@fl.ru>";
            $this->subject   = "Редактирование отзыва на FL.ru";
            $this->recipient = "{$opinion['t_uname']} {$opinion['t_usurname']} [{$opinion['t_login']}] <".$opinion['t_email'].">";
            
            $this->send( 'text/html' );
        }
        
        return $this->sended;  
    }

    /**
     * Отправляет фрилансеру сообщение об отказе
     *
     * @param string|array $ids
     * @param resource $connect
     * @return  integer количество отправленных уведомлений.
     */
    function ProjectsOfferRefused($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php';

        $offers = new projects_offers();
        if (!($data = $offers->getRefusedProjectOffers($ids, $connect))) return NULL;

        foreach($data as $offer) {
            if (substr($offer['subscr'], 4, 1) != '1' || $offer['is_banned'] == '1') continue; // если не нужны уведомления пропускаем отсылку

            $uname = $offer['uname'];
            $usurname = $offer['usurname'];
            $login = $offer['login'];
            $email = $offer['email'];
            $project_name = $offer['project_name'];

            $this->subject = "По проекту «"  . html_entity_decode($project_name) . "» был получен отказ";
            
            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            
            $body = "К сожалению, вы получили отказ от заказчика по проекту «<a href=\"".$GLOBALS['host'] . getFriendlyURL("project", $offer['project_id']).$this->_addUrlParams('f')."\">".$project_name."</a>».";
            
            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->message = $this->GetHtml($uname, $body, array('header'=>'default', 'footer'=>"default"), array('login' => $login));
            $this->SmtpMail('text/html');

        }
        return $this->sended;
    }

    /**
     * Уведомления заказчику, если кандидат/победитель/исполнитель заблокирован на сайте
     * 
     * проект/конкурс/СБР - всего 5 видов уведомлений @see pmail::_ExecutorCandidateBanMail
     * 
     * @param  string|array $ids идентификаторы заблокированных пользователей
     * @param  resource $connect соединение к БД (необходимо в PgQ) или NULL -- создать новое
     * @return bool true - успех, false - провал
     */
    function ExecutorCandidateBanMail( $ids, $connect = NULL ) {
        if ( empty($ids) ) {
            return false;
        }
        $mRes = employer::GetEmployersBlockedCandidates($ids);
        if ( !$GLOBALS['DB']->error && pg_num_rows($mRes) ) {
            $nCurCnf    = 1;
            $aRecipient = array();
            
            while ( $aOne = pg_fetch_assoc($mRes) ) {
                
                $aOne['name'] = htmlspecialchars($aOne['name'], ENT_QUOTES, 'CP1251', false);
                
                if ( $nCurCnf != $aOne['cnf'] ) {
                    if ( $aRecipient ) {
                        $this->_ExecutorCandidateBanMail( $nCurCnf, $aRecipient );
                        $aRecipient = array();
                    }
                    
                    $nCurCnf = $aOne['cnf'];
                }
                
                if ( $aOne['lnk'] == 'project' ) {
                    $sLink = $GLOBALS['host'] . getFriendlyURL( 'project', $aOne['id'] );
                }
                elseif ( $aOne['lnk'] == 'sbr' ) {
                    $sLink = $GLOBALS['host'] . '/' . sbr::NEW_TEMPLATE_SBR . '/?id=' . $aOne['id'];
                }
                
                $sUlink = $GLOBALS['host'] . '/users/' . $aOne['login'];
                $sUname = $aOne['uname'] . ' ' . $aOne['usurname'] . ' [' . $aOne['login'] . ']';
                
                $aRecipient[] = array(
                    'email' => $aOne['e_name']." ".$aOne['e_surname']." <".$aOne['email'].">",
                    'extra' => array( 'name' => $aOne['name'], 'link' => $sLink, 'u_link' => $sUlink, 'u_name' => $sUname, 'USER_LOGIN' => $aOne['e_login'] )
                );
            }
            
            if ( $aRecipient ) {
                $this->_ExecutorCandidateBanMail( $nCurCnf, $aRecipient );
            }
        }
    }

    /**
     * Уведомления заказчику, если кандидат/победитель/исполнитель заблокирован на сайте
     * 
     * вспомагательная функция @see pmail::ExecutorCandidateBanMail
     * 
     * @param int $nCnf номер уведомления от 1 до 5 
     * @param array $aRecipient массив данных для получателей
     */
    function _ExecutorCandidateBanMail( $nCnf = 0, $aRecipient = array() ) {
        if ( !$nCnf || !$aRecipient ) return false;
        
        $aCnf = array(
            // исполнитель в проекте 
            1 => array('sujb' => 'Исполнитель вашего проекта заблокирован на FL.ru', 'msg' => 'В проекте <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> вы выбрали в качестве исполнителя пользователя <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. Сообщаем вам, что данный пользователь был заблокирован на FL.ru.<br />'),
            // кандидат в проекте
            2 => array('sujb' => 'Исполнитель, определенный как кандидат в вашем проекте на FL.ru, заблокирован', 'msg' => 'В проекте <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> вы выбрали в качестве кандидата пользователя <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. Сообщаем вам, что данный пользователь был заблокирован на FL.ru.<br />'),
            // кандидат в конкурсе, в котром еще нет победителей
            3 => array('sujb' => 'Исполнитель, определенный как кандидат в вашем конкурсе на FL.ru, заблокирован', 'msg' => 'В конкурсе <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> вы выбрали в качестве кандидата пользователя <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. Сообщаем вам, что данный пользователь был заблокирован на FL.ru.<br />'),
            // победитель в конкурсе (тут кандидаты уже не важны)
            4 => array('sujb' => 'Победитель конкурса, опубликованого вами на FL.ru, заблокирован', 'msg' => 'В конкурсе <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> вы выбрали в качестве победителя пользователя <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. Сообщаем вам, что данный пользователь был заблокирован на FL.ru.<br />'),
            // исполнитель в сделке без риска
            5 => array('sujb' => 'Исполнитель в Безопасной Сделке заблокирован на FL.ru', 'msg' => 'Вы заключили Безопасную Сделку <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> с пользователем <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. Сообщаем вам, что данный пользователь был заблокирован на FL.ru.<br />')
        );
        
        $this->subject   = $aCnf[ $nCnf ]['sujb'];
    	$this->recipient = array();
    	$this->message   = $this->GetHtml( 
    	   "%USER_LOGIN%", 
    	   $aCnf[ $nCnf ]['msg'] . '<br />По всем возникающим вопросам вы можете обращаться в нашу <a href="https://feedback.fl.ru/'.$this->_addUrlParams('e', '?').'">службу поддержки</a>.', 
    	   array( 'header' => 'default', 'footer' => 'simple' ), 
    	   array( 'target_footer' => true ) 
        );
	    
        $sMsgId = $this->send( 'text/html' );
        
        $this->recipient = $aRecipient;
        
        $this->bind( $sMsgId );
        $this->recipient = array();
    }

    /**
     * Отправляет фрилансеру сообщение о том, что его выбрали кандидатом
     *
     * @param string|array $ids
     * @param resource $connect
     * @return  integer количество отправленных уведомлений.
     */
    function ProjectsOfferSelected($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php';

        $offers = new projects_offers();
        if (!($data = $offers->getSelectedProjectOffers($ids, $connect))) return NULL;

        foreach($data as $offer) {
            //if (substr($offer['subscr'], 4, 1) != '1' || $offer['is_banned'] == '1') continue; // если не нужны уведомления пропускаем отсылку

            $uname = $offer['uname'];
            $usurname = $offer['usurname'];
            $login = $offer['login'];
            $email = $offer['email'];
            $project_name = $offer['project_name'];
            $project_id = $offer['project_id'];

            $this->subject = "Вас выбрали кандидатом в проекте «" . html_entity_decode($project_name)."»";

            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            
            $body  = "Вас выбрали кандидатом в проекте «<a href=\"".$GLOBALS['host'] . getFriendlyURL("project", $project_id) . $this->_addUrlParams('f') . "\">".$project_name."</a>».";
            $body .= "<br/><br/>Желаем вам удачи!<br/>";

            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->message = $this->GetHtml($uname, $body, array('header'=>'simple', 'footer'=>'frl_simple_projects'), array('login' => $offer['login']));
            $this->SmtpMail('text/html');

        }
        return $this->sended;
    }

    /**
     * Отправляет фрилансеру сообщение о том, что его выбрали исполнителем
     *
     * @param string|array $ids
     * @param resource $connect
     * @return  integer количество отправленных уведомлений.
     */
    function ProjectsExecSelected($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';

        $projects = new projects();
        if (!($data = $projects->GetExecProjects($ids, $connect))) return NULL;

        foreach($data as $proj) {
            //if (substr($proj['subscr'], 4, 1) != '1' || $proj['is_banned'] == '1') continue; // если не нужны уведомления пропускаем отсылку

            $uname = $proj['uname'];
            $usurname = $proj['usurname'];
            $login = $proj['login'];
            $email = $proj['email'];
            $project_name = $proj['project_name'];
            $project_id = $proj['project_id'];

            $this->subject = "Вас выбрали исполнителем в проекте «" . html_entity_decode($project_name)."»";
            
            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            
            $body = "Вас выбрали исполнителем в проекте «<a href=\"".$GLOBALS['host'] . getFriendlyURL("project", $project_id) . $this->_addUrlParams('f') . "\">".$project_name."</a>».";

            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->message = $this->GetHtml($uname, $body, array('header'=>'simple', 'footer' => 'frl_simple_projects'), array('login'=>$login));
            echo $this->message;
            $this->SmtpMail('text/html');

        }
        return $this->sended;
    }



    /**
     * Отсылает сообщения заблокированным в конкурсе пользователям
     *
     * @param string|array $ids пользователи
     * @param resource $connect
     * @return  integer количество отправленных уведомлений.
     */
    function ContestUserBlocked($ids, $connect = NULL) {

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';

        if (!($data = contest::getContestsBlockedUsers($ids, $connect))) return NULL;

        foreach($data as $proj) {
            if (!$proj['email'] || substr($proj['subscr'], 8, 1) != '1') continue;

            $uname = $proj['uname'];
            $usurname = $proj['usurname'];
            $login = $proj['login'];
            $email = $proj['email'];
            $project_name = $proj['project_name'];
            $project_id = $proj['project_id'];
            $userlink = $GLOBALS["host"]."/users/".$proj['emp_login'];
            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->subject = 'Вас заблокировали в конкурсе «'.htmlspecialchars_decode($project_name, ENT_QUOTES).'»';
            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            $this->message = $this->GetHtml($uname, "
       Заказчик <a href=\"{$userlink}\">{$proj['emp_name']} {$proj['emp_uname']}</a> [<a href=\"{$userlink}\">{$proj['emp_login']}</a>] заблокировал(а) вас
       в&nbsp;конкурсе «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('f')."\">".$project_name."</a>».
       К сожалению, теперь вы не можете продолжать свое участие в этом конкурсе.<br />
            ", array('header' => 'default', 'footer' => 'default'), array('login'=>$login));
            $this->SmtpMail('text/html');
        }

        return $this->sended;
    }

    /**
     * Отсылает сообщения разблокированным в конкурсе пользователям
     *
     * @param string|array $ids пользователи
     * @param resource $connect
     * @return  integer количество отправленных уведомлений.
     */
    function ContestUserUnblocked($ids, $connect = NULL) {

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';

        if (!($data = contest::getContestsUnblocked($ids, $connect))) return NULL;

        foreach($data as $row) {
            if (!$row['user']['email'] || substr($row['user']['subscr'], 8, 1) != '1'
                || !isset($row['projects'])) continue;
            
            $user = $row['user'];

            $uname = $user['uname'];
            $usurname = $user['usurname'];
            $login = $user['login'];
            $email = $user['email'];

            $this->recipient = "$uname $usurname [$login] <".$email.">";

            foreach($row['projects'] as $proj) {
                $project_name = $proj['project_name'];
                $project_id = $proj['project_id'];

                $this->subject = 'Вас разблокировали в конкурсе «'.htmlspecialchars_decode($project_name, ENT_QUOTES).'»';
                $userlink = $GLOBALS["host"]."/users/".$proj['emp_login'];
                $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
                $this->message = $this->GetHtml($uname, "
                   Заказчик <a href=\"{$userlink}\">{$proj['emp_name']} {$proj['emp_uname']}</a> [<a href=\"{$userlink}\">{$proj['emp_login']}</a>] разблокировал(а) вас
                   в&nbsp;конкурсе «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('f')."\">".$project_name."</a>».
                   Теперь вы можете продолжить свое участие в этом конкурсе.
                   <br /><br />
                   Желаем удачи!
                   <br/>", 
                   array('header' => 'default', 'footer' => 'frl_subscr_projects'), array('login'=>$login));
                $this->SmtpMail('text/html');
            }
        }

        return $this->sended;
    }

    /**
     * Отсылает сообщение пользователям, которых определили кандидатами в конкурсах
     *
     * @param string|array $ids ид предложений пользователей
     * @param resource $connect
     * @return  integer количество отправленных уведомлений.
     */
    function ContestAddCandidate($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';

        if (!($data = contest::getSelectedOffers($ids, $connect))) return NULL;

        foreach($data as $proj) {
            if (!$proj['email'] || substr($proj['subscr'], 8, 1) != '1' || $proj['is_banned'] == '1') continue;

            $uname = $proj['uname'];
            $usurname = $proj['usurname'];
            $login = $proj['login'];
            $email = $proj['email'];
            $project_name = $proj['project_name'];
            $project_id = $proj['project_id'];

            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->subject = 'Вас добавили в кандидаты в победители в конкурсе «'.htmlspecialchars_decode($project_name, ENT_QUOTES).'»';
            $userlink = $GLOBALS["host"]."/users/".$proj['emp_login'];
            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            $this->message = $this->GetHtml($uname, "Заказчик <a href=\"{$userlink}\">{$proj['emp_name']} {$proj['emp_uname']}</a> [<a href=\"{$userlink}\">{$proj['emp_login']}</a>] добавил(а) вас в кандидаты в победители в&nbsp;конкурсе «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('f')."\">".$project_name."</a>».
               Вы можете перейти к своей <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $project_id)."?offer={$proj['id']}{$this->_addUrlParams('f', '&')}#offer-{$proj['id']}\">работе</a>.
               <br /><br />
               Желаем вам удачи!
               <br/>
              ", array('header' => 'default', 'footer' => 'frl_subscr_projects'), array('login'=>$login));
            $this->SmtpMail('text/html');
        }

        return $this->sended;
    }

    /**
     * Отсылает сообщения победителям конкурса
     *
     * @param string|array $ids ид предложений пользователей
     * @param resource $connect
     * @return  integer количество отправленных уведомлений.
     */
    function ContestWinners($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';

        if (!($data = contest::getWinnerOffers($ids, $connect))) return NULL;

        foreach($data as $proj) {
            if (!$proj['email'] || substr($proj['subscr'], 8, 1) != '1' || $proj['is_banned'] == '1') continue;

            $str = array(1 => 'первое', 2 => 'второе', 3 => 'третье');

            $this->recipient = "{$proj['uname']} {$proj['usurname']} [{$proj['login']}] <{$proj['email']}>";
            $userlink = $GLOBALS["host"]."/users/".$proj['emp_login'];
            $this->subject = 'Вас объявили одним из победителей конкурса «'.htmlspecialchars_decode($proj['project_name'], ENT_QUOTES).'»';
            
            $proj['project_name'] = htmlspecialchars($proj['project_name'], ENT_QUOTES, 'CP1251', false);
            
            $this->message = $this->GetHtml($proj['uname'], "Поздравляем вас!<br/><br/>
                Заказчик <a href=\"{$userlink}\">{$proj['emp_name']}</a> <a href=\"{$userlink}\">{$proj['emp_uname']}</a> [<a href=\"{$userlink}\">{$proj['emp_login']}</a>] объявил(a) вас одним из победителей в&nbsp;конкурсе «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $proj['project_id']).$this->_addUrlParams('f')."\">".$proj['project_name']."</a>». 
                Вы заняли ".($str[$proj['position']]? $str[$proj['position']]: $position)." место. Поздравляем!
                <br /><br/>
                Вы можете перейти к своей <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $proj['project_id'])."?offer={$proj['id']}{$this->_addUrlParams('f', '&')}#offer-{$proj['id']}\">работе</a>.
                <br />
                ", array('header' => 'default', 'footer' => 'frl_subscr_projects'), array('login'=>$proj['login']));
            $this->SmtpMail('text/html');
        }
        
        return $this->sended;
    }


    /**
     * Отсылает сообщение о том, что проект/конкурс опубликован
     *
     * @param string|array $ids ид проектов
     * @param resource $connect
     * @return  integer количество отправленных уведомлений.
     */
    function ProjectPosted($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";

        if (!($data = projects::getProjects4Sending($ids, $connect))) return NULL;

        foreach($data as $prj) {

            $prj['name'] = htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false);
            
            if($prj['kind'] == 7) {
                //конкурс
                $this->message = $this->GetHtml($prj['uname'],
                "Ваш конкурс «<a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('e')."'>{$prj['name']}</a>» был опубликован на сайте FL.ru.
                Напоминаем вам, что пользователи с <a href='{$GLOBALS['host']}/payed-emp/{$this->_addUrlParams('e')}'>аккаунтом PRO</a> экономят на платных услугах сайта.",
                array('header'=>'simple', 'footer'=>'simple'));
            } else {
                //проект
                $this->message = $this->GetHtml($prj['uname'],
                "Ваш проект «<a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('e')."'>{$prj['name']}</a>» был опубликован на сайте FL.ru.", 
                array('header'=>'simple', 'footer'=>($prj['prefer_sbr']=='t' ? 'simple' : 'simple_projects')), array('project' => $prj));
            }

            $this->recipient = "{$prj['uname']} {$prj['usurname']} [{$prj['login']}] <". $prj['email'] .">";
            $item_name = ($prj['kind'] == 7 ? 'конкурс' : 'проект' ) . " «" . html_entity_decode($prj['name'], ENT_QUOTES)."»"; 
            $this->subject = "Ваш $item_name опубликован на FL.ru";
            $this->SmtpMail('text/html');
        }


        
        return $this->sended;
    }
    
    /**
     * Отправляет уведомление об удалении отзыва.
     *
     * @param   string|array    $ids        идентификаторы новых отзывов
     * @param   resource        $connect    соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                     количество отправленных уведомлений.
     */
    function DeleteOpinion($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $f_user = new users();
        $t_user = new users();
        
        foreach($ids as $id) {
            list($from_id, $to_id, $type) = explode("|", $id);
            $f_user->GetUserByUID($from_id);
            $t_user->GetUserByUID($to_id);
            switch ($type) {
                case "0":
                    $type_text = "нейтральный";
                    break;
                case "1":
                    $type_text = "положительный";
                    break;
                case "-1":
                    $type_text = "отрицательный";
                    break;
            }
            
            
            $from_user = array("usurname"=>$f_user->usurname, "uname"=>$f_user->uname, "login"=>$f_user->login, "photo"=>$f_user->photo); 
            $to_user   = array("usurname"=>$t_user->usurname, "uname"=>$t_user->uname, "login"=>$t_user->login, "photo"=>$t_user->photo); 
            $email     = $t_user->email; 
            $role      = $f_user->role;  
            $subscr    = $t_user->subscr; 
            
            if (substr($subscr, 3, 1) != '1' || $t_user->is_banned == '1') continue; // если не нужны уведомления пропускаем отсылку
            
            if (substr($role,0,1)=='1') { $path= "/users/".$to_user["login"]."/opinions/"; }
            else { $path= "/users/".$to_user["login"]."/opinions/?from=frl"; }
    
            /*
            $message = "Пользователь <a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["uname"]." ".$from_user["usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["login"]."</a>]
    удалил(a) свой $type_text отзыв из вашего аккаунта или он был скрыт из-за блокировки или удаления аккаунта пользователя.";
             */
            
            $message = "Пользователь <a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["uname"]." ".$from_user["usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["login"]."</a>]
    был заблокирован за нарушение правил сайта FL.ru, и его отзыв скрыт.";

            
            $this->message = $this->GetHtml($to_user['uname'], $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$to_user['login']));
    
            $this->recipient = $to_user["uname"]." ".$to_user["usurname"]." [".$to_user["login"]."] <".$email.">";
            $this->subject = "Отзыв скрыт на FL.ru";
    
            $this->SmtpMail('text/html');
        }
        
        return $this->sended; 
    }

    /**
     * Отправляет уведомление о востановлении отзыва.
     *
     * @param   string|array    $ids        идентификаторы новых отзывов
     * @param   resource        $connect    соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return  integer                     количество отправленных уведомлений.
     */
    function RestoreOpinion($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $f_user = new users();
        $t_user = new users();
        
        foreach($ids as $id) {
            list($from_id, $to_id) = explode("|", $id);
            $f_user->GetUserByUID($from_id);
            $t_user->GetUserByUID($to_id);
            
            $from_user = array("usurname"=>$f_user->usurname, "uname"=>$f_user->uname, "login"=>$f_user->login, "photo"=>$f_user->photo); 
            $to_user   = array("usurname"=>$t_user->usurname, "uname"=>$t_user->uname, "login"=>$t_user->login, "photo"=>$t_user->photo); 
            $email     = $t_user->email; 
            $role      = $f_user->role;  
            $subscr    = $t_user->subscr; 
            
            if (substr($subscr, 3, 1) != '1' || $t_user->is_banned == '1') continue; // если не нужны уведомления пропускаем отсылку
            
            if (substr($role,0,1)=='1') { $path= "/users/".$to_user["login"]."/opinions/"; }

            else { $path= "/users/".$to_user["login"]."/opinions/?from=frl"; }

            $message = "Отзыв пользователя  <a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["uname"]." ".$from_user["usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["login"]."</a>] 
                        восстановлен на вашей странице на FL.ru в связи с тем, что данный пользователь был разблокирован модератором сайта.";
    
            $this->message = $this->GetHtml($to_user['uname'], $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$to_user['login']));
    
            $this->recipient = $to_user["uname"]." ".$to_user["usurname"]." [".$to_user["login"]."] <".$email.">";
            $this->subject = "Отзыв восcтановлен на FL.ru";
    
            $this->SmtpMail('text/html');
        }
        
        return $this->sended; 
    }

    /**
     * Отправляет пользователю уведомление об ответе на его сообщение
     *
     * @param string|array $ids
     * @param resource $connect
     * @return  integer количество отправленных уведомлений.
     */
    function ArticleNewComment($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/articles_comments.php';

        $c = new articles_comments();
        if (!($data = $c->getComments4Sending($ids, $connect))) return NULL;

        foreach($data as $comment) {
            $this->subject = "Комментарии в разделе «Статьи и интервью» на сайте FL.ru";

            if(substr($comment['s_subscr'], 11, 1) == '1' && $comment['s_uid'] != $comment['uid']
                && $comment['s_email'] && $comment['parent_id'] && $comment['s_banned'] == '0')
            {
                $body = 
                "<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
                оставил(-а) <a href='{$GLOBALS['host']}/articles/?id={$comment['article_id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}'>новый комментарий</a> 
                к вашим сообщениям/комментариям в разделе <a href='{$GLOBALS['host']}/articles/{$this->_addUrlParams('b')}'>«Статьи и интервью»</a> на сайте FL.ru. 
                <br /> --------
                <br />"
                .repair_html(LenghtFormatEx($comment['msgtext'], 300))."
                <br /> --------<br />";
                
                $this->message = $this->GetHtml($comment['s_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['s_login']));

                $this->recipient = $comment['s_uname']." ".$comment['s_usurname']." [".$comment['s_login']."] <".$comment['s_email'].">";
                $this->SmtpMail('text/html');
            }

            if(substr($comment['a_subscr'], 11, 1) == '1' && !$comment['parent_id']
                && $comment['a_uid'] != $comment['from_id']
                && $comment['a_email'] && $comment['a_banned'] == '0') {
                $body = 
                "<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
                оставил(-а) <a href='{$GLOBALS['host']}/articles/?id={$comment['article_id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}'>новый комментарий</a> 
                к вашим сообщениям/комментариям в разделе <a href='{$GLOBALS['host']}/articles/{$this->_addUrlParams('b')}'>«Статьи и интервью»</a> на сайте FL.ru. 
                <br /> --------
                <br />"
                .repair_html(LenghtFormatEx($comment['msgtext'], 300))."
                <br /> --------<br />";
                
                $this->message = $this->GetHtml($comment['a_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login' => $comment['a_login']));

                $this->recipient = $comment['a_uname']." ".$comment['a_usurname']." [".$comment['a_login']."] <".$comment['a_email'].">";
                $this->SmtpMail('text/html');
            }
        }

        return $this->sended;
    }
    
    /**
     * По идентификаторм транзакций выбирает события для отправки по ним уведомлений.
     * @see sbr_meta::getEventsInfo4Sending()
     *
     * @param array $xids   ид. транзакций.
     * @param resource $connect   текущий коннект к БД.
     * @return integer   количество отправленных уведомлений.
     */
    function SbrNewEvents($xids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        if($info = sbr_meta::getEventsInfo4Sending($xids, $connect)) {
            foreach($info as $xacts) {
                foreach($xacts as $func=>$events) {
                    $this->$func($events);
                }   
            }
        }
        return $this->sended;
    }

    /**
     * Отправляет уведоление об открытии СБР.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrOpened($events) {
        $ev0 = $events[0];
        $this->subject = "Предложение о заключении новой Безопасной Сделки по проекту  «{$ev0['sbr_name']}»";
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $msg = "
          Заказчик <a href=\"{$userlink}\">{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>] предлагает вам заключить с ним Безопасную Сделку по проекту «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f')}'>{$sbr_name}</a>». 
          Вы можете получить подробную информацию по Безопасной Сделке в <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>соответствующем разделе</a> «Помощи». 
        ";
        $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * Уведомления обоим участникам о том, что деньги зарезервированы.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrReserved($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        include_once(dirname(__FILE__).'/account.php');
        include_once(dirname(__FILE__).'/bank_payments.php');
        $reserved = account::getOperationInfo($ev0['reserved_id']);

        for($e=0;$e<2;$e++) {
            $r = $e ? 'e_' : 'f_';
            $rcls = $e ? 'sbr_emp' : 'sbr_frl';
            $sbr = new $rcls($ev0[$r.'uid'], $ev0[$r.'login']);
                $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
                $cnum = $sbr->getContractNum($ev0['sbr_id'], $ev0['scheme_type'], $ev0['posted']);
                $num = in_array((int)$reserved['payment_sys'], array(4,5))
                            ? ((int)$reserved['payment_sys'] == 4 ? '№ Б-'.$cnum : '№ '.  bank_payments::GetBillNum($ev0['reserved_id']))
                            : '';
                $num_str = in_array((int)$reserved['payment_sys'], array(4,5))
                            ? 'по счету '.$num : '';
                if($r == 'e_'){
                    $fuserlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
                    $msg_e = "Информируем Вас о том, что деньги в Сделке «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$sbr_name}</a>» успешно зарезервированы. Исполнителю <a href='{$fuserlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$fuserlink}'>{$ev0['f_login']}</a>] отправлено уведомление о том, что ему необходимо начать выполнение работы по заданию.";
                    
                    $this->subject = "Денежные средства для $cnum зарезервированы";
                    $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg_e, array('header'=>'simple', 'footer'=>'norisk_robot')));
                    $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
                    $this->SmtpMail('text/html');
                }else{
                    
                    $msg_f  = "Информируем Вас о том, что  деньги в Сделке «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>» успешно зарезервированы.<br/><br/>";
                    $msg_f .= "Пожалуйста, приступите к выполнению задания.";
                   
                    $this->subject = "Резервирование денег в Безопасной Сделке (проект «{$ev0['sbr_name']}»)";
                    $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg_f, array('header'=>'simple', 'footer'=>'norisk_robot')));
                    $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
                    $this->SmtpMail('text/html');
                }
            /**
             * @deprecated 
             */
            /*
            if(!$sbr->checkUserReqvs()) {
                $msg =  "
                  Пожалуйста, внесите все необходимые данные на вкладке «<a href='{$GLOBALS['host']}/users/{$ev0[$r.'login']}/setup/finance/{$this->_addUrlParams($e ? 'e' : 'f')}'>Финансы</a>». Указанные во вкладке реквизиты требуются для составления договора
                  на оказание услуг и являются необходимым условием для работы через сервис «Сделка Без Риска».
                ";//по проекту «<a href='{$url}?id={$ev0['sbr_id']}'>{$ev0['sbr_name']}</a>»
                $this->subject = "Заполнение вкладки «Финансы»";
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }*/
        }
    }

    /**
     * Уведомление, что фрилансер согласился приступить к проекту по СБР.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrAgreed($events) {
        $this->subject = "Фрилансер согласился с условиями Безопасной Сделки";
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $msg  = "Исполнитель <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] согласился с предложенными вами условиями Сделки «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$sbr_name}</a>». Вам необходимо зарезервировать деньги.<br/><br/>";
        $msg .= "Пожалуйста, перейдите в <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>Сделку</a> и зарезервируйте деньги, следуя подсказкам интерфейса. С подробной инструкцией по резервированию средств можно ознакомиться <a href='https://feedback.fl.ru/{$this->_addUrlParams('e', '?')}'>здесь</a>.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * Уведомление работодателю об отказе фрилансера от СБР.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrRefused($events) {
    	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_meta.php'; 	
        $this->subject = "Фрилансер отказался от Безопасной Сделки";
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $ev0['new_val'] = str_replace("\\", "", $ev0['new_val']);
        $msg  = "Исполнитель <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] отказался от Сделки «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$sbr_name}</a>»";
        $msg .= $ev0['new_val'] ? " по причине:<br/><br/> «{$ev0['new_val']}». " : ' без указания причины. ';
        $msg .= "<br>Вы можете перейти в <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>Сделку</a>, изменить задание и повторно отправить на утверждение Исполнителю.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * Уведомление работодателю о принятии изменений в СБР фрилансером.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrChangesAgreed($events) {
        $this->subject = "Фрилансер согласился с изменением условий Безопасной Сделки";
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['e_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        $stage_name = sbr_meta::getNameForMail($ev0);
        $msg  = "Исполнитель <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] согласился с предложенными Вами изменениями условий в Сделке «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>».<br/><br/>";
        if(!$sbr->reserved_id) {
            $msg .= "Вам необходимо зарезервировать деньги. <br/>";
            $msg .= "Пожалуйста, перейдите в <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>проект</a> и зарезервируйте деньги, следуя подсказкам интерфейса. С подробной инструкцией по резервированию средств можно ознакомиться <a href='https://feedback.fl.ru/{$this->_addUrlParams('e', '?')}'>здесь</a>.";
        }
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * Уведомление работодателю об отказе фрилансера от изменений в СБР.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrChangesRefused($events) {
        $this->subject = "Фрилансер не согласился с изменением условий Безопасной Сделки";
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
        $stage_name = sbr_meta::getNameForMail($ev0);
        $msg = "Исполнитель <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] отказался от предложенных Вами изменений условий в Сделке «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>».<br/><br/>";
        $msg .= "Вы можете перейти в <a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('e', '&')}'>сделку</a>, изменить задание и повторно отправить на утверждение Исполнителю или вернуться к предыдущей версии условий.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * Уведомление одному из участников СБР о том, что другая строна обратилась в арбитраж.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrArb($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr   = sbr_meta::getInstanceLocal($ev0['e_uid']);
        $sbr->initFromId($ev0['sbr_id']);
        $stage = new sbr_stages($sbr, array('id'=>$ev0['own_id']));
        $arb = $stage->getArbitrage(false, false);
        $stage_name = sbr_meta::getNameForMail($ev0);
        $sbr_num    = $stage->sbr->getContractNum();
        if($arb['user_id'] == $ev0['f_uid']) {
            $r = 'e_';
            $arb = 'f_';
            $this->subject = "Фрилансер обратился в Арбитраж сервиса Безопасная Сделка";
            $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
            //$msg = "Информируем вас о том, что по проекту «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>» Исполнитель <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] обратился в Арбитраж по причине:<br/><br/>";
        } else {
            $r = 'f_';
            $arb = 'e_';
            $this->subject = "Заказчик обратился в Арбитраж сервиса Безопасная Сделка";
            $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
            //$msg = "Информируем вас о том, что по проекту «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>» Заказчик <a href='{$userlink}'>{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href='{$userlink}'>{$ev0['e_login']}</a>] обратился в Арбитраж по причине:<br/><br/>";
        }
        $msg = "Информируем вас о том, что пользователь <a href='{$userlink}'>{$ev0[$arb.'uname']} {$ev0[$arb.'usurname']}</a> [<a href='{$userlink}'>{$ev0[$arb.'login']}</a>] обратился в Арбитраж по причине:<br/><br/>";
        $msg .= "«" . reformat($arb['descr']) . "»<br/><br/>";
        $msg .= "Работа по этапу <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$ev0['stage_name']}</a> «Безопасной Сделки» <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$sbr_num}</a> приостановлена. Срок вынесения решения – до " . sbr_stages::MAX_ARBITRAGE_DAYS . " рабочих дней с момента обращения в арбитраж.<br/><br/>";
        $msg .= "Вы можете оставить свой комментарий по поводу сложившейся ситуации в разделе «Мои Сделки», в системе комментариев к сделке «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>».";
        //$msg .= "«" . reformat($arb['descr']) . "»<br/><br/>";
        //$msg .= "Пожалуйста, перейдите в <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>сделку</a> и прокомментируйте ситуацию.";
            
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * Уведомление обоим участникам СБР о вынесении решения арбитража.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrArbResolved($events) {
        $ev0 = $events[0];
        $this->subject = "Арбитраж вынес решение по Безопасной Сделке (проект «{$ev0['sbr_name']}»)";
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr = new sbr(NULL);
        $stage = $sbr->getStage($ev0['own_id']);
        $arb = $stage->getArbitrage(false, false);
        for($e=0;$e<2;$e++) {
            $r = $e ? 'e_' : 'f_';
            
            $stage_name = sbr_meta::getNameForMail($ev0);
            
            if($r == 'f_') {
                $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
                $usr = "Заказчику <a href='{$userlink}'>{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href='{$userlink}'>{$ev0['e_login']}</a>]";
            } else {
                $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
                $usr = "Исполнителю <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>]";
            }
            
            $msg  = "Информируем Вас о том, что Арбитраж вынес решение в Сделке «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>» и закрыл ее. ";
            $msg .= "Пожалуйста, перейдите в <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>сделку</a>, чтобы ознакомиться с решением Арбитража и оставить отзыв {$usr}, а также отзыв сервису Безопасная Сделка.";
             
            //$msg =  "«Арбитраж» сервиса «Сделка без риска» вынес решение по задаче «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$ev0['stage_name']}</a>» проекта <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$ev0['sbr_name']}</a>:<br/><br/>";
            //$msg .= "----<br/>";
            //$msg .= "«{$stage->arbitrage['descr_arb']}»";
            //$msg .= "<br/>----<br/>";
            //$msg .= '<br/><br/>Пройдите по ссылке, чтобы получить более подробную информацию.';
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }

    /**
     * Отправляет уведомление после изменений условий сделки работодателем.
     * Формируется список изменений со старым и новым значением.
     * Также отправляет уведомление об откате изменений (с тем же списком) в случае, если фрилансер отказался от них.
     * @see sbr_meta::parseEvents()
     *
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrTzChanged($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr_link = " «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('b', '&')}'>{$ev0['sbr_name']}</a>»";
        $changes = '';
        $parse = sbr_meta::parseEvents($events);
        foreach($parse['events'] as $id=>$ev) {
            $changes .= '<br/>'.(++$i).'. '.$ev['ev_name'].($ev['note'] ? ' (<strong>'.trim($ev['note']).'</strong>)' : '') . ' &mdash; '
                     . ($ev['stage_name'] ? 'задача «' : '')
                     . '<a href="' . $url . ($ev['stage_name'] ? "?site=Stage&id={$ev['own_id']}" : "?id={$ev['sbr_id']}") . $this->_addUrlParams('b', '&') . '">'
                     . ($ev['stage_name'] ? reformat($ev['stage_name'],40,0,1) : 'Весь проект')
                     . '</a>'
                     . ($ev['stage_name'] ? '»' : '')
                     . '.'
                     ;
        }

        if(!$changes) return;

        if($ev0['xtype'] == sbr::XTYPE_RLBK) { 
            $this->subject = "Изменения в Безопасной Сделке отменены (проект «{$ev0['sbr_name']}»)";
            $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
            for($e=0;$e<2;$e++) {
                $r = $e ? 'e_' : 'f_';
                $msg = $e ? "В связи с отказом исполнителя <a href=\"{$userlink}\">{$ev0['f_uname']}</a> <a href=\"{$userlink}\">{$ev0['f_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['f_login']}</a>] от изменений, система произвела возврат условий {$sbr_link} к предыдущей версии:<br/>"
                          : "В связи с тем, что вы отказались от изменений в Безопасной Сделке, система произвела возврат условий {$sbr_link} к предыдущей версии:<br/>";
                $msg .= "---<br/>";
                $msg .= $changes.'<br/>';
                $msg .= "---<br/><br/>";
                $msg .= $e ? "Вы можете отредактировать условия и повторно отправить их исполнителю на утверждение или отказаться от изменений, продолжив работу с прежними условиями.".
                             " Более подробная информация по согласованию Безопасной Сделки с фрилансером размещена <a href='https://feedback.fl.ru/{$this->_addUrlParams('e', '?')}'>здесь</a>."
                           : "Вы можете продолжить работу с прежними условиями. Вы можете ознакомиться с общей информацией <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>по порядку проведения Безопасной Сделки</a>.";
                $msg .= ' Пройдите по ссылке, чтобы получить более подробную информацию.';
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
        else {
            $this->subject = "Заказчик внес изменения в условия Безопасной Сделки по проекту «{$ev0['sbr_name']}»";
            $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
            
            $msg  = "Заказчик <a href='{$userlink}'>{$ev0['e_uname']} {$ev0['e_usurname']} [{$ev0['e_login']}]</a> предлагает Вам изменить условия Сделки {$sbr_link}.<br/><br/>";
            $msg .= "Вам необходимо перейти в <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('b', '&')}'>сделку</a> и ознакомиться с предложенными изменениями. Вы можете согласиться на изменения условий или отказаться от них, указав причину.";
            
            /*$msg = "Работодатель <a href=\"{$userlink}\">{$ev0['e_uname']}</a> <a href=\"{$userlink}\">{$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>] внес(-ла) поправки в «Сделку без риска» по проекту";
            $msg .= $sbr_link.':<br/>';
            $msg .= '----<br/>';
            $msg .= $changes.'<br/><br/>';
            $msg .= '----<br/><br/>';
            $msg .= "Вам необходимо подтвердить или отклонить данные изменения.<br/> Вы можете ознакомиться с общей информацией по <a href='{$GLOBALS['host']}/help/?q=891{$this->_addUrlParams('f', '&')}'>порядку проведения «Сделки без риска»</a>.";
            */
            $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
            $this->SmtpMail('text/html');
        }
    }
    
    /**
     * Уведомление об изменении статуса этапа СБР.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrStatusChanged($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr_link_e = "задачи «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>{$ev0['stage_name']}</a>» «Безопасной Сделки» в проекте «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$ev0['sbr_name']}</a>»";
        $sbr_link_f = "задачи «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('f', '&')}'>{$ev0['stage_name']}</a>» «Безопасной Сделки» в проекте «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$ev0['sbr_name']}</a>»";
        setlocale(LC_ALL, "ru_RU.CP1251");
        $changes = "c «" . ucfirst(sbr_stages::$nss_classes[$ev0['old_val']][1]) . "» на «" . ucfirst(sbr_stages::$nss_classes[$ev0['new_val']][1]) . "».";
        setlocale(LC_ALL, "en_US.UTF-8");
        if($ev0['xtype'] == sbr::XTYPE_RLBK) {
            $this->subject = "Статус Безопасной Сделки возвращен к предыдущему состоянию";
            for($e=0;$e<2;$e++) {
                $r = $e ? 'e_' : 'f_';
                $userlink = $GLOBALS['host']."/users/{$ev0['f_login']}";
                $msg = $e ? "В связи с отказом исполнителя <a href=\"{$userlink}\">{$ev0['f_uname']} {$ev0['f_usurname']}</a> <a href=\"{$userlink}\">[{$ev0['f_login']}]</a> от изменений, система произвела возврат {$sbr_link_e} к предыдущей версии:<br/>"
                          : "В связи с тем, что вы отказались от изменений, система произвела возврат {$sbr_link_f} к предыдущей версии:<br/>";
                $msg .= "<br>Изменился статус Сделки: {$changes}<br/><br/>";
                $msg .= $e ? ""
                           : "";
                $msg .= 'Пройдите по ссылке, чтобы получить более подробную информацию.';
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        } else {
            $this->subject = "Изменился статус задачи в Безопасной Сделке по проекту «{$ev0['sbr_name']}»";
            $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
            $fuserlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
            $stage_name = sbr_meta::getNameForMail($ev0);
            if($ev0['new_val'] == sbr_stages::STATUS_COMPLETED) {
                $msg  = "Информируем вас о том, что проект «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>»  успешно завершен Заказчиком <a href=\"{$userlink}\">{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>].<br/><br/>";
                $msg .= "Пожалуйста, перейдите в <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('f', '&')}'>сделку</a>, чтобы оставить отзыв Заказчику, отзыв сервису Безопасная Сделка и выбрать способ получения денег.";
                
                // Для работодателя
                $e_msg  = "Вы успешно завершили проект «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>».<br/>";
                $e_msg .= "Теперь Вам необходимо оставить отзыв Исполнителю <a href=\"{$fuserlink}\">{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href=\"{$fuserlink}\">{$ev0['f_login']}</a>] и отзыв сервису Безопасная Сделка в интерфейсе вашей <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>сделки</a>.";
            
                $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $e_msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
                $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
                $this->SmtpMail('text/html');
            } else {
                $msg  = "Заказчик <a href=\"{$userlink}\">{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>] хочет изменить статус Сделки «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>» {$changes}<br/><br/>";
                $msg .= "Пожалуйста, перейдите в <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('f', '&')}'>сделку</a>, чтобы принять или отклонить предложенные Заказчиком изменения.";  
            }
            //$msg .= "Вы можете ознакомиться с общей информацией по <a href='{$GLOBALS['host']}/help/?q=891{$this->_addUrlParams('f', '&')}'>порядку проведения «Сделки без риска»</a>.";
            $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
            $this->SmtpMail('text/html');
            
        }
    }


    /**
     * Уведомление фрилансеру об отмене СБР.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrCanceled($events) {
        $ev0 = $events[0];
        $this->subject = "Заказчик отменил Безопасную Сделку по проекту «{$ev0['sbr_name']}»";
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        
        $msg = "Сделка «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>» была отменена Заказчиком. Сожалеем, что ваше сотрудничество не состоялось. Перейти к открытым <a href='{$url}'>Безопасным Сделкам</a>."; 
        
        //$msg  = "Информируем Вас о том, что проект «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>» отменен Заказчиком <a href=\"{$userlink}\">{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>].<br/>";
        //$msg .= "Причину отмены сделки вы можете узнать у работодателя.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * Уведомление обоим участникам об отмене арбитража (заявки в арбитраж).
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrArbCanceled($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $this->subject = "Арбитраж по Безопасной Сделке отменен";
        for($e=0;$e<2;$e++) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            $stage_name = sbr_meta::getNameForMail($ev0);
            $sbr_link = "задаче «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($e ? 'e' : 'f', '&')}'>{$stage_name}</a>» в проекте «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($e ? 'e' : 'f', '&')}'>{$sbr_name}</a>»";
            $r = $e ? 'e_' : 'f_';
            $msg = "Арбитраж по {$sbr_link} был отменен.<br/><br/>
              Статус задачи автоматически изменился на «В разработке». 
              Вы можете узнать более подробно о статусах Безопасной Сделки в <a href='https://feedback.fl.ru/{$this->_addUrlParams($e ? 'e' : 'f', '?')}'>соответствующем разделе «Помощи»</a>.";
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }

    /**
     * Уведомление обоим участникам о завершении всей СБР.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrCompleted($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $this->subject = "Безопасная Сделка по проекту «{$ev0['sbr_name']}» завершена";
        for($e=0;$e<2;$e++) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            $sbr_link = " «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($e ? 'e' : 'f', '&')}'>{$sbr_name}</a>»";
            $r = $e ? 'e_' : 'f_';
            $f = $e ? 'simple' : 'norisk_robot';
            $w = $e ? 'фрилансеру' : 'заказчику';
            $msg = "
              Безопасная Сделка по проекту {$sbr_link} " .($e?"":"полностью")." завершена.<br/><br/>
              Пожалуйста, не забудьте оставить отзывы {$w}. 
              Вы можете получить подробную информацию по <a href='https://feedback.fl.ru/{$this->_addUrlParams($e ? 'e' : 'f', '?')}'>завершению Безопасной Сделки</a>.
              <br/><br/>              
            ";
            $msg .= $e? "Благодарим вас за использование сервиса Безопасная Сделка. Надеемся, что вы остались довольны!" : "";  
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>$f)));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }

    /**
     * Уведомление для подряда о подписанных документах
     * 
     */
    function SbrStageCompleted($events) {
        $ev0 = $events[0];
        
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        
        if( ( $stage->sbr->scheme_type == sbr::SCHEME_PDRD || $stage->sbr->scheme_type == sbr::SCHEME_PDRD2 ) && $ev0['own_role'] == sbr::EVROLE_FRL && $stage->status == sbr_stages::STATUS_COMPLETED) {
            $sbr->getDocs();
            $r   = 'f_';
            
            $this->subject = "Необходимо прислать подписанные документы (проект «{$ev0['sbr_name']}»)";
            foreach($stage->sbr->docs as $hdoc) {
                if( $hdoc['type'] == sbr::DOCS_TYPE_ACT || $hdoc['type'] == sbr::DOCS_TYPE_FM_APPL || 
                    $hdoc['type'] == sbr::DOCS_TYPE_WM_APPL || $hdoc['type'] == sbr::DOCS_TYPE_YM_APPL ||
                    $hdoc['type'] == sbr::DOCS_TYPE_TZ_PDRD ) {
                    $head_docs[] = $hdoc;
                }
            }
            $hdoc_cnt = count($head_docs);    
            $stage_name = sbr_meta::getNameForMail($ev0);
            $msg  = "Вы успешно завершили Сделку «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a>».<br/><br/>";
            $msg .= "Для получения оплаты Вам необходимо скачать, распечатать в двух экземплярах и подписать данные " . ending($hdoc_cnt, 'документ', 'документы', 'документы') . ":<br/>";

            foreach($head_docs as $hdoc) {
                $msg .= "<a href='". WDCPREFIX . "/{$hdoc['file_path']} {$hdoc['file_name']}' class='b-layout__link'> {$hdoc['name']}</a>, " . ConvertBtoMB($hdoc['file_size']);
            }
            $msg .= "<br/><br/>";
            $msg .= "Подписанные документы необходимо в оригиналах отправить на любой удобный вам адрес из списка:<br/>";
            $msg .= "- 129223, Москва, а/я 33;<br/>"; 
            $msg .= "- 190031, Санкт-Петербург, Сенная пл., д.13/52, а/я 427;<br/>";
            $msg .= "- 420032, Казань, а/я 624;<br/>";
            $msg .= "- 454014, Челябинск - 14, а/я 2710.<br/><br/>";
            $msg .= "Обязательно укажите наименование организации-получателя в поле «Кому» на конверте - ООО \"ВААН\".<br/><br/>"; 
            $msg .= "Время доставки документов зависит от работы выбранной Вами почтовой службы и Вашей удаленности от адреса доставки. При значительной задержке в доставки рекомендуем Вам обратиться в <a href='https://feedback.fl.ru/'>службу поддержки</a>.";

            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        } else if(( $stage->sbr->scheme_type == sbr::SCHEME_PDRD || $stage->sbr->scheme_type == sbr::SCHEME_PDRD2 ) && $ev0['own_role'] == sbr::EVROLE_FRL && $stage->status == sbr_stages::STATUS_ARBITRAGED) {
            $sbr->getDocs();
            $r   = 'f_';
            $this->subject = "Необходимо прислать подписанные документы (проект «{$ev0['sbr_name']}»)";
            foreach($stage->sbr->docs as $hdoc) {
                if( $hdoc['type'] == sbr::DOCS_TYPE_ACT || $hdoc['type'] == sbr::DOCS_TYPE_FM_APPL || 
                    $hdoc['type'] == sbr::DOCS_TYPE_WM_APPL || $hdoc['type'] == sbr::DOCS_TYPE_YM_APPL ||
                    $hdoc['type'] == sbr::DOCS_TYPE_TZ_PDRD ) {
                    $head_docs[] = $hdoc;
                }
            }
            $stage_name = sbr_meta::getNameForMail($ev0);
            $msg .= "Информируем вас о том, что для получения вашего гонорара по Сделке «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a>»  в соответствии с решением Арбитража, вам необходимо скачать, распечатать в двух экземплярах и подписать данные документы:<br/>";
            foreach($head_docs as $hdoc) {
                $msg .= "<a href='". WDCPREFIX . "/{$hdoc['file_path']} {$hdoc['file_name']}' class='b-layout__link'> {$hdoc['name']}</a>, " . ConvertBtoMB($hdoc['file_size']);
            }
            $msg .= "<br/><br/>";
            $msg .= "Подписанные документы необходимо в оригиналах отправить на любой удобный вам адрес из списка:<br/>";
            $msg .= "- 129223, Москва, а/я 33;<br/>"; 
            $msg .= "- 190031, Санкт-Петербург, Сенная пл., д.13/52, а/я 427;<br/>";
            $msg .= "- 420032, Казань, а/я 624;<br/>";
            $msg .= "- 454014, Челябинск - 14, а/я 2710.<br/><br/>";
            $msg .= "Обязательно укажите наименование организации-получателя в поле «Кому» на конверте - ООО \"ВААН\".<br/><br/>";
            
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        } elseif($stage->sbr->scheme_type == sbr::SCHEME_LC) {
            $stage_name = sbr_meta::getNameForMail($ev0);
            $this->subject = "Безопасная Сделка по проекту «{$ev0['sbr_name']}» завершена";
            $endDate = date('d.m.Y', strtotime($sbr->data['dateEndLC']));
            $msg  = "Безопасная Сделка <a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a> завершена. Для того, чтобы получить заработанные деньги, вам необходимо отправить документы в банк путем нажатия на кнопку «Завершить этап».<br/><br/>";
            $msg .= "Обратите внимание, что завершить этап необходимо до того, как истечет срок действия аккредитива (до {$endDate}). В противном случае денежные средства будут возвращены Заказчику.";
            $r = 'f_';
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }
    
    /**
     * Уведомление о получении подпсанных документов
     * 
     * @param type $events 
     */
    function SbrDocReceived($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $r   = 'f_';
         
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        
        if($stage->sbr->scheme_type == sbr::SCHEME_PDRD || $stage->sbr->scheme_type == sbr::SCHEME_PDRD2) {
            $r == 'f_';
            
            $this->subject = "Мы получили подписанные вами документы (проект «{$ev0['sbr_name']}»)";
             
            $msg  = "Мы получили подписанные Вами документы.<br/><br/>";
            $msg .= "Деньги будут переведены вам в течение 1-2 рабочих дней. В случае задержки рекомендуем Вам обратиться в <a href='https://feedback.fl.ru/'>службу поддержки</a>.";
            
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }
    
    /**
     * Отсылаем уведомление фрилансеру о том что сделка просрочена
     * 
     * @param type $events 
     */
    function SbrOvertime($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $r   = 'f_';
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $this->subject = "Время на этап сделки {$sbr_name} истекло. Посетите раздел «Мои Сделки» ";// Этап «Сделки без риска» {$sbr_name} завершен. Посетите раздел «Мои СБР» на FL.ru.)";
        
        $fmsg  = "{$ev0[$r.'uname']}, время, отведенное заказчиком на выполнение <a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>этапа</a> Безопасной Сделки <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>, истекло. Вам необходимо передать заказчику выполненную работу (если необходимо передать файлы, опубликуйте их в комментариях к сделке). <br/><br/>";
        $fmsg .= "<i>Обратите внимание</i>: если за 2 рабочих дня заказчик не примет работу и не выйдет на связь, вам необходимо обратиться в арбитраж в срок  не позднее чем  5 рабочих дней с даты завершения этапа. В противном случае зарезервированные под сделку деньги будут возвращены заказчику, и вы не получите заработанные денежные средства.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $fmsg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
        
        $r = 'e_';
        $emsg  = "{$ev0[$r.'uname']}, время, отведенное вами на выполнение <a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>этапа</a> Безопасной Сделки <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>, истекло. Вам необходимо принять результат работы, предоставленный исполнителем. Если вы не удовлетворены результатом работы и нашли несоответствия работы поставленному вами техническому заданию, а также в том случае, если работа исполнителем не была предоставлена, обратитесь в арбитраж. <br/><br/>";
        $emsg .= "<i>Обратите внимание</i>: если возникла спорная ситуация, вам необходимо подать жалобу в арбитраж в течение 5 рабочих дней с момента завершения этапа. По истечении этого срока возможности обратиться в арбитраж уже не будет.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $emsg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * Уведомление о том что пауза была удалена тк исполнител ее не подтвердил
     * 
     * @param array $events
     */
    function SbrPauseReset($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr_name   = sbr_meta::getNameForMail($ev0, 'sbr');
        $stage_name = sbr_meta::getNameForMail($ev0);
        
        $this->subject = "Срок паузы в сделке «{$sbr_name}» истек";
        $msg = "Истек срок подтверждения паузы, ранее предложенной в этапе «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a>» проекта «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>». Пауза отменена, вы можете продолжить работу в сделке в обычном режиме.";
        $r = 'f_';
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
        
        $r = 'e_';
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * Уведомление о том что пауза была завершена
     * 
     * @param array $events
     */
    function SbrPauseOver($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr_name   = sbr_meta::getNameForMail($ev0, 'sbr');
        $stage_name = sbr_meta::getNameForMail($ev0);
        
        $this->subject = "Срок паузы в сделке «{$sbr_name}» завершен";
        $msg = "Истек срок паузы, ранее установленной в этапе «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a>» проекта «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>». Данный этап автоматически возвращен в рабочий режим, статус этапа изменился на «В работе».";
        $r = 'f_';
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
        
        $r = 'e_';
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * Произведена выплата гонорара.
     * @param type $events 
     */
    function SbrMoneyPaidFrl($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $r   = 'f_';
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        $arb   = $stage->getArbitrage();
        $type_payment = exrates::getNameExrates($stage->type_payment);
        
        $this->subject = "Произведена выплата гонорара (проект «{$ev0['sbr_name']}»)";
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        if($stage->status == sbr_stages::STATUS_ARBITRAGED && (int) $arb['frl_percent'] != 1) {
            $msg  = "Информируем вас о том, что частичная выплата гонорара в Сделке «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>» произведена (в соответствии с решением Арбитража).<br/><br/>";
        } else {
            $msg  = "Информируем вас о том, что в Сделке «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>» вам был произведен перевод гонорара в сумме " . sbr_meta::view_cost($stage->getPayoutSum(sbr::FRL), $stage->sbr->cost_sys) . " на указанные вами реквизиты.<br/><br/>";
        }
        $msg .= "Зачисление средств на ваш личный счет может занять некоторое время (от нескольких минут до нескольких дней в зависимости от способа выплаты).";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
        
        if($stage->sbr->scheme_type == sbr::SCHEME_LC && $stage->sbr->ps_frl == pskb::WW) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/single_send.php';
            $user = new users();
            $user->GetUserByUID($stage->sbr->frl_id);
            
            $single_send = new single_send($user);
            
            if( !$single_send->is_send(single_send::NOTICE_WEBM) ) {
                $msg  = "Немного о том, для чего нужен Веб-кошелек: <a href='http://webpay.pscb.ru/login/auth' target='_blank'>Веб-кошелек Петербургского Социального  Коммерческого Банка (ПСКБ)</a> – это платежная система для мгновенной оплаты различных услуг и осуществления банковских переводов через интернет. При работе через «Безопасную Сделку» Веб-кошелек используется для резервирования и возврата денег заказчику, а также для выплаты гонорара исполнителю.<br/><br/>";
                $msg .= "Веб-кошелек заводится для вас в момент принятия вами <a href='https://www.fl.ru/offer_lc.pdf' target='_blank'>Оферты на заключение договора с аккредитивной формой расчетов</a>. Мы рекомендуем вам идентифицироваться в Веб-кошельке: в этом случае для вас не будет ограничений по выводу денежных средств, а также вы всегда сможете получить деньги в случае непредвиденных ситуаций (потери телефона с мобильным номером, к которому привязывается каждый Веб-кошелек).<br/><br/>";
                $msg .= "С более подробной информацией по Веб-кошельку можно ознакомиться в <a href='https://feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/{$this->_addUrlParams('f', '?')}'>соответствующем разделе помощи</a>.";
                
                $this->subject = "Что такое Веб-кошелек";
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
                $single_send->setUpdateBit(single_send::NOTICE_WEBM);
            }
        }
    }
    
    /**
     * Произведена выплата гонорара.
     * @param type $events 
     */
    function SbrMoneyPaidEmp($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $r   = 'f_';
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        $arb   = $stage->getArbitrage();
        $type_payment = exrates::getNameExrates($stage->type_payment);
        
        $this->subject = "Произведена выплата гонорара (проект «{$ev0['sbr_name']}»)";
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $msg  = "Информируем вас о том, что возврат денег в Сделке «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>» произведен (в соответствии с решением Арбитража).<br/><br/>";
        $msg .= "Зачисление средств на ваш личный счет может занять некоторое время (от нескольких минут до нескольких дней в зависимости от способа выплаты).";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * Уведомление одному из участников СБР о том что другой оставил ему отзыв.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrFeedback($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        // Если оставляет мнение исполнитель, этап считается завершенным отправляем уведомление
        if($ev0['abbr'] == 'FRL_FEEDBACK') {
            $this->SbrStageCompleted($events);
        }
        $this->subject = "Вам оставили отзыв по Безопасной Сделке (проект «{$ev0['sbr_name']}»)";
        $stage_name = sbr_meta::getNameForMail($ev0);
        if($ev0['own_role'] == sbr::EVROLE_FRL && $ev0['frl_feedback_id']) {
            $r = 'e_';
            $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
            $feedback = sbr_meta::getFeedback($ev0['frl_feedback_id']);
            $uniq_id = $feedback['id'] * 2 + 1;
            $link_feedback = "{$GLOBALS["host"]}/users/{$ev0['e_login']}/opinions/#p_{$uniq_id}";
            
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
            $sbr = sbr_meta::getInstanceLocal($ev0['e_uid']);
            $stage = $sbr->initFromStage($ev0['stage_id']);
            
            if($stage->status == sbr_stages::STATUS_ARBITRAGED) {
                $msg = "Информируем вас о том, что Исполнитель <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] оставил вам <a href='{$link_feedback}'>отзыв</a> по Сделке «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>»:<br/></br>";
            } else {    
                $msg = "Исполнитель <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] завершил Сделку «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>» со своей стороны и оставил вам <a href='{$link_feedback}'>отзыв</a>:<br/></br>";
            }
        } else if($ev0['emp_feedback_id']) {
            $r = 'f_';
            $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
            $feedback = sbr_meta::getFeedback($ev0['emp_feedback_id']);
            $uniq_id = $feedback['id'] * 2 + 1;
            $link_feedback = "{$GLOBALS["host"]}/users/{$ev0['f_login']}/opinions/#p_{$uniq_id}";
            $msg = "Заказчик <a href='{$userlink}'>{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href='{$userlink}'>{$ev0['e_login']}</a>] завершил Сделку «<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>» со своей стороны и оставил вам <a href='{$link_feedback}'>отзыв</a>:<br/><br/>";
            //$msg = "Сообщаем вам о том, что работодатель <a href=\"{$userlink}\">{$ev0['e_uname']}</a> <a href=\"{$userlink}\">{$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>]";
        }
        $sbr_link = "задаче «<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>» (проект «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$ev0['sbr_name']}</a>»)";
        if(!$feedback) return;
        $opi_path = $GLOBALS['host'].'/users/'.$ev0[$r.'login'].'/opinions/?from=norisk';
        
        $msg .= "«{$feedback['descr']}».";
        
        //$msg .= " оставил(-a) вам рекомендацию по «Сделке без риска» в {$sbr_link}:<br/><br/>---<br/>«{$feedback['descr']}»<br/>---<br/>";
        //$msg .= "<br/>Вы можете просмотреть рекомендацию на вкладке <a href='{$opi_path}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>«Отзывы»</a> в вашем аккаунте.";
        //if($ev0['emp_feedback_id']) {
        //    $msg .= "<br/><br/>Напоминаем, что вы можете воспользоваться услугой «Рекомендация» - <a href='{$GLOBALS['host']}/service/{$this->_addUrlParams($r == 'e_' ? 'e' : 'f')}'>приобрести рекомендации</a> от работодателей по сервису «Сделка без риска».";
        //}
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * Уведомление о том, что загружен новый документ в СБР.
     * Отправляется участнику СБР, если он заинтересован в этом документе (см. статусы и доступы к документам)
     *
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrAddDoc($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $sbr_link_e = " «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$sbr_name}</a>»";
        $sbr_link_f = " «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>»";
        if(!($doc = sbr_meta::getDoc($ev0['new_val'], false))) return 0;
        if($doc['owner_role']!=0) return 0; // только если админ загружал.
        $doc_link_e = " «<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['new_val']}{$this->_addUrlParams('e', '&')}'>{$doc['name']}</a>»";
        $doc_link_f = " «<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['new_val']}{$this->_addUrlParams('f', '&')}'>{$doc['name']}</a>»";
        $this->subject = "Загружен новый документ по Безопасной Сделке (проект {$ev0['sbr_name']})";
        $e = 'e_';
        $f = 'f_';
        //$msg[$e] = "Менеджер сервиса «Безопасная Сделка» загрузил документ {$doc_link_e} в проект {$sbr_link_e}.
        $msg[$e] = "В Сделку {$sbr_link_e} загружен документ {$doc_link_e}.
        Вы можете ознакомиться с общим <a href='https://feedback.fl.ru/{$this->_addUrlParams('e', '?')}'>порядком проведения Безопасной Сделки</a>.";
        //$msg[$f] = "Менеджер сервиса «Безопасная Сделка» загрузил документ {$doc_link_f} в проект {$sbr_link_f}.
        $msg[$f] = "В Сделку {$sbr_link_f} загружен документ {$doc_link_f}.
        Вы можете ознакомиться с общим <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>порядком проведения Безопасной Сделки</a>.";
        $footer = 'norisk_robot';

        if($doc['type'] == sbr::DOCS_TYPE_ACT) {
            $sbr = sbr_meta::getInstanceLocal($ev0['e_uid']);
            $sbr->initFromId($ev0['sbr_id']);
            
            if($sbr->isNewVersionSbr()) {
                $this->subject = "Завершается Безопасная Сделка {$sbr_name}";
                
                if($sbr->scheme_type == sbr::SCHEME_LC) {
                    $message  = "Безопасная Сделка {$sbr_link_e} находится на завершающем этапе. В систему комментариев к сделке и подраздел «Файлы по этапу» был загружен {$doc_link_e}.<br/><br/>";
                    $message .= "<i>Обратите внимание</i>: отправлять документы по почте не требуется. Все налоги и сборы вам необходимо оплатить самостоятельно.<br/><br/>";
                    $message .= "Подробная информация по порядку завершения Безопасной Сделки находится в соответствующем <a href='https://feedback.fl.ru/' target='_blank'>разделе помощи</a>.";
                    
                    $msg[$e]  = $message;
                    $msg[$f]  = $message;
                } elseif($sbr->scheme_type == sbr::SCHEME_PDRD2){
                    //@todo: был не корректный вызов метода sbr::getContractNum($ev0['sbr_id'], $ev0['scheme_type'], $ev0['posted']) который валил всю функцию и возможно консьюмер
                    $doc_tz = " «<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc=" . ($ev0['new_val']+1 ) . "{$this->_addUrlParams('e', '&')}'>Техническое задание по договору {$sbr_name}</a>»";
                    $message  = "Безопасная Сделка {$sbr_link_e} находится на завершающем этапе. В комментарии к сделке и подраздел «Файлы по этапу» был загружен {$doc_link_e} и {$doc_tz}.<br/><br/>";
                    $message .= "Для того чтобы получить гонорар за выполненную работу, вам необходимо распечатать данные документы в 2-х экземплярах, подписать и отправить на адрес компании FL.ru: 129223, г. Москва, а/я 33, ООО «Ваан».";
                    
                    $msg[$e]  = $message;
                    $msg[$f]  = $message;
                }
                
            } else {
                $this->subject = "Документы для завершения Безопасной Сделки по проекту «{$ev0['sbr_name']}»";
                $msg[$e] = "
                Ваша Безопасная Сделка по проекту {$sbr_link_e} находится на завершающем этапе. В раздел «Документы проекта» был загружен {$doc_link_e}.
                <br/><br/>
                Для того чтобы деньги были переведены исполнителю, вам необходимо распечатать данный документ в 2-х экземплярах,
                подписать и отправить на адрес компании FL.ru: 129223, г. Москва, а/я 33, ООО «Ваан».
                <br/><br/>
                Пожалуйста, обратите внимание на то, что деньги будут переведены исполнителю только после получения нами оригиналов документов. Выплаты производятся еженедельно в среду и четверг.
                ";

                if(!empty($events[1])) {
                    $ev1 = $events[1];
                    $_doc = sbr_meta::getDoc($ev1['new_val'], false);
                    if($_doc['type'] == sbr::DOCS_TYPE_WM_APPL || $_doc['type'] == sbr::DOCS_TYPE_YM_APPL) {
                        $_doc_link_f = " «<a href='{$url}?site=Stage&id={$ev1['stage_id']}&doc={$ev1['new_val']}{$this->_addUrlParams('f', '&')}'>{$_doc['name']}</a>»";
                    }
                }
                if($doc_link_f && $_doc_link_f) {
                    $doc_string_f = "были загружены {$doc_link_f} и {$_doc_link_f}";
                    $print_info_f = "Заявление в одном экземпляре, Акт – в двух";
                } else {
                    $doc_string_f = "был загружен {$doc_link_f}";
                    $print_info_f = "данный документ в 2-х экземплярах";
                }

                $msg[$f] = "
                Безопасная Сделка по проекту {$sbr_link_f} находится на завершающем этапе. В раздел «Документы проекта» {$doc_string_f}.
                <br/><br/>
                Для того чтобы вам были перечислены ваши деньги, вам необходимо распечатать {$print_info_f},
                подписать и отправить на адрес компании FL.ru: 129223, г. Москва, а/я 33, ООО «Ваан».
                <br/><br/>
                Пожалуйста, обратите внимание на то, что деньги будут переведены вам только после получения нами оригиналов документов. Выплаты производятся еженедельно в среду и четверг.
                ";
                $footer = 'norisk_robot';
            }
        }

        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = $f;
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = $e;
        if($rs) {
            foreach($rs as $r) {
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg[$r], array('header'=>'simple', 'footer'=>$footer)));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * Уведомление о том, что документ удален из СБР.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrDelDoc($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($doc = sbr_meta::getDoc($ev0['old_val'], false))) return 0;
        $doc_link = " «{$doc['name']}»";
        $this->subject = "Удален документ из Безопасной Сделки";
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = 'f_';
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = 'e_';
        if($rs) {
            foreach($rs as $r) {
                $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
                $sbr_link = " «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$sbr_name}</a>»";
                $msg = "Администратор удалил документ {$doc_link} из Сделки {$sbr_link}";
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * Уведомление об изменении статуса документа.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrDocStatusChanged($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($doc = sbr_meta::getDoc($ev0['own_id'], false))) return 0;
        $this->subject = "Изменился статус документа в Безопасной Сделки";
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = 'f_';
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = 'e_';
        if($rs) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            foreach($rs as $r) {
                $sbr_link = " «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$sbr_name}</a>»";
                $doc_link = " «<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['own_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$doc['name']}</a>»";
                $msg = "Администратор Безопасной Сделки изменил статус документа {$doc_link} в Сделке {$sbr_link}: ";
                $msg .= '<br/><br/><strong>' . sbr::$docs_ss[$ev0['old_val']][0] . ' &mdash; ' . sbr::$docs_ss[$ev0['new_val']][0] . '</strong>';
                $msg .= "<br/><br/>Свяжитесь с <a href=\"{$GLOBALS['host']}/contacts/?from=norisk{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}\">менеджером Безопасной Сделки</a>, чтобы уточнить подробности.";
                
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * Уведомление о изменении доступа к документу.
     *
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrDocAccessChanged($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($doc = sbr_meta::getDoc($ev0['own_id'], false))) return 0;
        $this->subject = "Изменилась видимость документа в Безопасной Сделки";
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = 'f_';
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = 'e_';
        if($rs) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            foreach($rs as $r) {
                $sbr_link = " «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$sbr_name}</a>»";
                $doc_link = " «<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['own_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$doc['name']}</a>»";
                $msg = "Администратор Безопасной Сделки изменил уровень доступа к просмотру документа {$doc_link} в Сделке {$sbr_link}: ";
                $msg .= '<br/><br/><strong>' . sbr::$docs_access[$ev0['old_val']][0] . ' &mdash; ' . sbr::$docs_access[$ev0['new_val']][0] . '</strong>';
                $msg .= "<br/><br/>Свяжитесь с <a href=\"{$GLOBALS['host']}/contacts/?from=norisk{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}\">менеджером Безопасной сделки</a>, чтобы уточнить подробности.";
                
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * Уведомление о том, что файл документа перезагружен.
     * @param array $events   информация по событиям (если событий нескольлко, то содержит несколько элементов).
     */
    function SbrDocReload($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($doc = sbr_meta::getDoc($ev0['own_id'], false))) return 0;
        if($doc['owner_role']!=0) return 0; // только если админ загружал.
        $this->subject = "Перезагружен файл документа в проекте «Безопасной Сделки»";
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = 'f_';
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = 'e_';
        if($rs) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            foreach($rs as $r) {
                $sbr_link = " «<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$sbr_name}</a>»";
                $doc_link = " «<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['own_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$doc['name']}</a>»";
                $msg = "Администратор Безопасной Сделки перезагрузил файл документа {$doc_link} в Сделке {$sbr_link}.";
                $msg .= "<br/><br/>Свяжитесь с <a href=\"{$GLOBALS['host']}/contacts/?from=norisk{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}\">менеджером</a>, чтобы уточнить подробности.";
                
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * Уведомление о новом комментарии в диалоге к этапу СБР.
     *
     * @param array $ids   идентификаторы новых комментов.
     * @param resource $connect   текущее соединение с БД.
     * @return integer количество отправленных уведомлений.
     */
    function SbrNewComment($ids, $connect = NULL) {
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/sbr.php");
        if(!($comments = sbr_meta::getComments4Sending($ids, $connect)))
            return NULL;

        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        foreach($comments as $comment) {
            $this->subject = "Новый комментарий в Безопасной Сделке по проекту «{$comment['sbr_name']}»";
            $rs = array();
            $msg = '';
            
            //$sbr_num = sbr::getContractNum($comment['sbr_id'], $comment['scheme_type']);
            $stage_name = sbr_meta::getNameForMail($comment);
            if($comment['is_admin']=='t') {
                $this->subject = "Арбитраж оставил комментарий в Безопасной Сделке «{$comment['sbr_name']}»";
                $msg = "Информируем вас о том, что в Сделке «<a href='{$url}?site=Stage&id={$comment['stage_id']}' target='_blank'>{$stage_name}</a>» Арбитраж оставил новый <a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}#c_{$comment['id']}'>комментарий</a>:<br/>";
                
                $rs[] = 'f_';
                $rs[] = 'e_';
            } else if($comment['user_id'] == $comment['e_uid']) {
                $userlink = $GLOBALS["host"]."/users/".$comment['e_login'];
                $msg = "Информируем вас о том, что в Сделке «<a href='{$url}?site=Stage&id={$comment['stage_id']}' target='_blank'>{$stage_name}</a>» Заказчик <a href=\"{$userlink}\">{$comment['e_uname']} {$comment['e_usurname']}</a> [<a href=\"{$userlink}\">{$comment['e_login']}</a>] оставил новый <a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}#c_{$comment['id']}'>комментарий</a>:<br/>";
                
                $rs[] = 'f_';
            } else if($comment['user_id'] == $comment['f_uid']) {
                $userlink = $GLOBALS["host"]."/users/".$comment['f_login'];
                $msg = "Информируем вас о том, что в Сделке «<a href='{$url}?site=Stage&id={$comment['stage_id']}' target='_blank'>{$stage_name}</a>» Исполнитель <a href=\"{$userlink}\">{$comment['f_uname']} {$comment['f_usurname']}</a> [<a href=\"{$userlink}\">{$comment['f_login']}</a>] оставил новый <a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}#c_{$comment['id']}'>комментарий</a>:<br/>";
                
                $rs[] = 'e_';
            }
            if($rs) {
                foreach($rs as $r) {
                    /*$sbr_link = "задаче «<a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}'>{$comment['stage_name']}</a>» проекта «<a href='{$url}?id={$comment['sbr_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}'>{$comment['sbr_name']}</a>»";
                    $msg .= "
                    <a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}#c_{$comment['id']}'>новый комментарий</a> в {$sbr_link}:
                    <br/>-----<br/>
                    «" . reformat($comment['msgtext'], 0, 0, 0, 1) . "»
                    <br/>-----<br/>
                    ";*/
                    $msg_send = $msg . "<br/>«".reformat($comment['msgtext'], 0, 0, 0, 1)."».<br/>";
                    
                    $this->message = $this->splitMessage($this->GetHtml($comment[$r.'uname'], $msg_send, array('header'=>'simple', 'footer'=>'norisk_robot')));
                    $this->recipient = $comment[$r.'uname']." ".$comment[$r.'usurname']." [".$comment[$r.'login']."] <".$comment[$r.'email'].">";
                    $this->send('text/html');
                }
            }
        }

        return $this->sended;
    }

    /**
     * Отправляет уведомления о новых сообщениях в личке при платной рассылке.
	 * Консьюмер plproxy-mail
     * 
     * @param   array      $params    Данные от PgQ, TO-адреса получателей; FROM-адрес отправителя
     * @param   string     $msg       Текст сообщения
     */
	function SendMasssending($params, $from, $to, $msg)
	{
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
	    
        $uid_from = intval($from);
        $uids_to = explode(",",preg_replace("/[\{\}]/","",$to));

        if(!$uid_from || !is_array($uids_to)) return;

		$from = new users;
		$from->GetUserByUID($uid_from);

        $this->subject = "Новое сообщение на FL.ru";
  		$msg_text = "
<a href='{$GLOBALS['host']}/users/{$from->login}'>{$from->uname} {$from->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$from->login}{$this->_addUrlParams('b')}'>{$from->login}</a>]
написал(а) вам новое сообщение на сайте FL.ru.<br />
<br />
---------- 
<br />
".$this->ToHtml(LenghtFormatEx(strip_tags($msg), 300))."
<br />
<br />
<br />
<a href='{$GLOBALS['host']}/contacts/?from={$from->login}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/contacts/?from={$from->login}</a>
<br />
<br />
------------
";
        foreach($uids_to as $uid_to) {
    		$to = new users;
    		$to->GetUserByUID($uid_to);
		
    		if (substr($to->subscr, 0, 1) != '1' || !$to->email || $to->is_banned == '1') {
    			continue;
    		}

	    	if (!$this->Connect())
    			return "Невозможно соеденится с SMTP сервером";
            if ($to->email && (substr($to->subscr, 12, 1) == '1')) {
    			$this->recipient = $to->uname." ".$to->usurname." [".$to->login."] <".$to->email.">";
    			$this->message = $this->GetHtml($to->uname, $msg_text, array('header' => 'default', 'footer' => 'default'), array('login'=>$to->login));
    			$this->SmtpMail('text/html');
            }
        }

        $this->subject = "Ваша рассылка на FL.ru прошла модерацию";
   		$this->recipient = $from->uname." ".$from->usurname." [".$from->login."] <".$from->email.">";
   		$msg_text = $this->ToHtml($msg);
   		
        $body = 
        "Ваша заявка на рассылку была рассмотрена и одобрена модераторами сайта FL.ru. 
         Фрилансерам выбранных вами специализаций будет отправлено сообщение следующего содержания:</br>
         ---<br/>
         {$msg_text}<br/>
         ---<br/>";
        
   		$this->message = $this->GetHtml($from->uname, $body, array('header'=>'simple', 'footer' => 'simple'));
        
   		$this->SmtpMail('text/html');

	}


    /**
     * Отправляет уведомления о новых сообщениях в личке при рассылке администрации.
	 * Консьюмер plproxy-mail
     *
     * @param   array      $params    Данные от PgQ, TO-адреса получателей; FROM-адрес отправителя
     * @param   string     $msg       Текст сообщения
     */
	function SendAdminMessage($params)
	{
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/messages.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        $messObj = new messages;
        
        $message_id = $params;
		if (!($message = $messObj->GetMessage($message_id)))
			return "Тело сообщения отсутствует.";

		$this->subject = "Новое сообщение от Команды FL.ru";

		$msg_text = reformat2($message['msg_text'], 100);
		$attaches = array();
		if ($message['attach']) {
			foreach($message['attach'] as $a) {
				$attaches[] = new CFile($a['path'].$a['fname']);
			}
			$attaches = $this->CreateAttach($attaches);
		}

		if (!$this->Connect())
			return "Невозможно соеденится с SMTP сервером";
			
        $from = new users;
		$from->GetUserByUID( $message['from_id'] );
		$parse  = $from->login == 'admin';
		$header = $parse ? 'none' : 'default';

		for ($i=0; $users = $messObj->GetZeroMessageUsers($message['from_id'], $message_id, 1000, $i * 1000); $i++) {
			foreach ($users as $ikey=>$user) {
			    if ( $parse ) {
			    	$msg_text = reformat2($message['msg_text'], 100);
			    	$msg_text = preg_replace( "/%USER_NAME%/", $user['uname'], $msg_text );
                    $msg_text = preg_replace( "/%USER_SURNAME%/", $user['usurname'], $msg_text );
                    $msg_text = preg_replace( "/%USER_LOGIN%/", $user['login'], $msg_text );
			    }
			    
				if (!$user['email'] || substr($user['subscr'], 7, 1) == '0') continue;
				$this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
				$this->message = $this->GetHtml( $user['uname'], $msg_text, array('header' => 'none', 'footer' => 'none') );
				$this->SmtpMail('text/html', $attaches);
			}
		}
		return '';
	}
	
	/**
	 * Новая платная рекомендация
	 *
	 * @param array $events
	 */
	function newPaidAdvice($ids, $connect = NULL) {
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        $f_user = new users();
        $t_user = new users();
        
        $this->subject = "Вам оставили отзыв";
        
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach ( $ids as $id ) {
            list($user_from, $user_to) = explode("-", $id);
            $f_user->GetUserByUID($user_from);
            $t_user->GetUserByUID($user_to);
            if (!$t_user->email || substr($t_user->subscr, 14, 1) != '1') continue;
            
            $to_user = get_object_vars($t_user);
            $from_user = get_object_vars($f_user);
                
            $message  = (is_emp($from_user['role'])?"Заказчик":"Фрилансер") . " {$from_user['uname']} {$from_user['usurname']} [{$from_user['login']}] оставил вам отзыв. ";
            $message .= "Вы можете ознакомиться с ним, а затем принять или отказаться от данного отзыва на вкладке «Отзывы» в вашем аккаунте.";
                
            $this->message   = $this->GetHtml( $to_user['uname'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $to_user['login']));
            $this->recipient = $to_user['uname'].' '.$to_user['usurname'].' ['.$to_user['login'].'] <'.$to_user['email'].'>';
            $this->SmtpMail('text/html');
        }
	}
	
	/**
	 * Изменение статуса платной рекомендации
	 *
	 * @param array $events
	 */
	function changePaidAdvice($ids, $connect = NULL) {
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/paid_advices.php';
        $f_user = new users();
        $t_user = new users();
        
        foreach ( $ids as $id ) {
            list($user_from, $user_to, $mod_status, $id_advice, $status) = explode("-", $id);
            $f_user->GetUserByUID($user_from);
            $t_user->GetUserByUID($user_to);
            if (!$t_user->email || substr($t_user->subscr, 14, 1) != '1') continue;
            
            $to_user = get_object_vars($t_user);
            $from_user = get_object_vars($f_user);
            
            if($mod_status == paid_advices::MOD_STATUS_ACCEPTED ) {
                $this->subject = "Ваш отзыв прошел модерацию";  
                $message  = "Отзыв от ". (is_emp($from_user['role'])?"Заказчика":"Фрилансера") . " {$from_user['uname']} {$from_user['usurname']} [{$from_user['login']}], отправленный вами на проверку, одобрен модератором.";
                $message .= " Для того чтобы отзыв появился на вкладке «Отзывы» вашего аккаунта и стал видна всем пользователям сайта, вам необходимо его <a href='{$GLOBALS['host']}/users/{$to_user['login']}/opinions/{$this->_addUrlParams('b')}#n_{$id_advice}'>оплатить</a>.";        
            } else if($mod_status == paid_advices::MOD_STATUS_DECLINED && $status == paid_advices::STATUS_BLOCKED) {
                $this->subject = "Отзыв удален модератором";
                $paid_advice = new paid_advices();
                $advice = $paid_advice->getAdviceById($id_advice);
                $message = 
                "Отзыв, отправленный вами на модерацию, был удален по причине: 
                <br/>-----<br/>
                ".nl2br($advice['mod_msg'])."
                <br/>-----<br/><br/>
                Благодарим за понимание!<br/><br/>
                По всем возникающим вопросам вы можете обращаться в нашу <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>службу поддержки</a>.";
            } else if($mod_status == paid_advices::MOD_STATUS_DECLINED ) {
                $this->subject = "Ваш отзыв не прошел модерацию";
                $message  = "Отзыв от ". (is_emp($from_user['role'])?"Заказчика":"Фрилансера") . " {$from_user['uname']} {$from_user['usurname']} [{$from_user['login']}], отправленный вами на проверку модераторам, не одобрен.";
                $message .= " Вам необходимо устранить причину, указанную модераторами в качестве основания отказа для принятия отзыва. После этого вы можете отправить отзыв на повторную модерацию.";
            }
            
            $this->message   = $this->GetHtml( $to_user['uname'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $to_user['login']));
            $this->recipient = $to_user['uname'].' '.$to_user['usurname'].' ['.$to_user['login'].'] <'.$to_user['email'].'>';
            $this->SmtpMail('text/html');
        }
	}
    
    
    /**
     * Рассылка для неактивных фрилансеров, рассылается из hourly.php
     * 
     * @return integer  количество пользователей получивших рассылку
     */
    function noActiveFreelancers() {
        $DB = new DB('master');
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';
        
        $message = "<p>
&nbsp;&nbsp;&nbsp;&nbsp;Мы заметили, что вы давно не заходили на <a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>FL.ru</a>.<br/> 
&nbsp;&nbsp;&nbsp;&nbsp;<br/>Если вы не можете часто посещать наш сайт, рекомендуем приобрести <a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>аккаунт PRO</a>: ваша контактная информация будет видна всем работодателям, и они смогут связаться с вами в любое время.
&nbsp;&nbsp;&nbsp;&nbsp;<br/>Тем временем, на сайте публикуется около 40 000 проектов в месяц, а средняя стоимость проекта составляет 25000 рублей. Наверняка, многие из этих проектов будут вам интересны.
&nbsp;&nbsp;&nbsp;&nbsp;Напоминаем, что искать работу на <a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>FL.ru</a> очень просто. Вы можете подписаться на ежедневные рассылки предложений работодателей или установить на свой компьютер программу <a href='{$GLOBALS['host']}/promo/freetray/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>Free Tray</a>.<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>Перейти на FL.ru</a>
</p>";
        $this->subject   = "Приглашаем вас вновь посетить FL.ru";
        $this->recipient = '';
        $this->message   = $this->GetHtml( 
            '', 
            $message,
            array( 'header' => 'default', 'footer' => 'feedback_default' ),
            array( 'login' => '', 'utm_campaign' => 'freelancers_comeback', 'target_footer' => 1 )
        );
        $msgid = $this->send("text/html");
        if ( !$msgid ) {
            return 0;
        }
        $i = 0;
        $this->recipient = array();
        $res = $DB->query("SELECT * FROM freelancer WHERE is_active = FALSE AND is_banned = B'0'");
        while ( $user = pg_fetch_assoc($res) ) {
            if ( !$user['subscr'][7] ) {
                continue;
            }
            $this->recipient[] = array(
                'email' => "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>",
                'extra' => array(
                    'USER_NAME'    => $user['uname'],
                    'USER_SURNAME' => $user['usurname'],
                    'USER_LOGIN'   => $user['login']
                )
            );
            if ( ++$i >= 10000 ) {
                $this->bind($msgid);
                unset($this->recipients);
                $this->recipient = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->bind($msgid);
        }
        unset($this->recipients);
        return $i;
    }
    
    
    /**
     * @todo: отключено в hourly, при использовании исправить текст
     * 
     * Рассылка для неактивных работодателей, рассылается из hourly.php
     * 
     * @return integer  количество пользователей получивших рассылку
     */
    function noActiveEmployers() {
        $DB = new DB('master');
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';
        $eHost = $GLOBALS['host'];        
        $message = "<p>Мы заметили, что вы давно не заходили на <a href=\"{$eHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=clients_comeback\" target=\"_blank\">FL.ru</a>. Тем временем, ваших заказов ждут более 1 миллиона профессиональных исполнителей – дизайнеров, веб-мастеров, программистов, разработчиков, копирайтеров, переводчиков, менеджеров и консультантов. </p>
<p>Напоминаем, что работать на FL.ru легко и удобно. Всегда к вашим услугам <a href=\"{$eHost}/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_content=manager&utm_campaign=clients_comeback\" target=\"_blank\">менеджеры</a>, которые возьмут на себя все обязанности по подбору нужного вам специалиста, а сервис «<a href=\"{$eHost}/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_content=manager&utm_campaign=clients_comeback\" target=\"_blank\">Безопасная Сделка</a>» обеспечит полную безопасность вашего сотрудничества с фрилансерами на всех этапах выполнения ваших проектов.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"{$eHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=clients_comeback\" target=\"_blank\">Перейти на FL.ru</a></p>";
        $this->subject   = "Приглашаем вас вновь посетить FL.ru";
        $this->recipient = '';
        $this->message   = $this->GetHtml( 
            $user['uname'], 
            $message,
            array( 'header' => 'default', 'footer' => 'feedback_default' ),
            array( 'login' => '', 'utm_campaign' => 'clients_comeback', 'target_footer' => 1 )
        );
        $msgid = $this->send("text/html");
        if ( !$msgid ) {
            return 0;
        }
        $i = 0;
        $this->recipient = array();
        $res = $DB->query("SELECT * FROM employer WHERE is_active = FALSE AND is_banned = B'0'");
        while ( $user = pg_fetch_assoc($res) ) {
            if ( !$user['subscr'][7] ) {
                continue;
            }
            $this->recipient[] = array(
                'email' => "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>",
                'extra' => array(
                    'USER_NAME'    => $user['uname'],
                    'USER_SURNAME' => $user['usurname'],
                    'USER_LOGIN'   => $user['login']
                )
            );
            if ( ++$i >= 10000 ) {
                $this->bind($msgid);
                unset($this->recipients);
                $this->recipient = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->bind($msgid);
        }
        unset($this->recipients);
        return $i;
    }
    
    
    /**
     * Рассылка для фрилансеров с незаполненным профилем. Рассылается с hourly.php
     * 
     * @return integer  количество пользователей получивших рассылку
     */
    function withoutProfileFrelancers() {
        $DB = new DB('master');
        $this->recipient = '';
        $this->subject   = "Как получить больше заказов на FL.ru";//"Напоминание от FL.ru: вы теряете заказчиков!";
        $message = "<p>
&nbsp;&nbsp;&nbsp;&nbsp;Мы заметили, что у вас не заполнен раздел «Портфолио». По статистике, 95% работодателей обращают внимание на фрилансеров с выбранной специализацией, заполненным портфолио и опытом работы. Когда вы не предоставляете полную информацию о себе как о специалисте, не демонстрируете уровень своих работ, заказчики не могут в полной мере оценить ваш профессионализм и в таком случае нередко отказываются от сотрудничества.<br/><br/>            
&nbsp;&nbsp;&nbsp;&nbsp;Мы рекомендуем вам заполнить портфолио примерами выполненных вами работ. Это поможет вам быстрее найти интересный и выгодный проект.<br/><br/>
&nbsp;&nbsp;&nbsp;&nbsp;Полная инструкция по заполнению портфолио находится <a href='https://feedback.fl.ru/'>здесь</a>. Вы можете ознакомиться с ней в любое время<br/><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<a href='{$GLOBALS['host']}'>Перейти на FL.ru</a>";
        $this->message = $this->GetHtml( 
            '', 
            $message,
            array( 'header' => 'default', 'footer' => 'default' ),
            array( 'login' => '', 'utm_campaign' => 'freelancers_profile', 'target_footer' => 1 )
        );
        $msgid = $this->send("text/html");
        if ( !$msgid ) {
            return 0;
        }
        $i = 0;
        $this->recipient = array();
        $res = $DB->query("
            SELECT 
                uid, login, uname, usurname, email, subscr 
            FROM 
                freelancer u
            LEFT JOIN 
                portfolio p ON u.uid = p.user_id
            WHERE
                p.id IS NULL
                AND is_active = TRUE
                AND is_banned = B'0'
        ");
        while ( $user = pg_fetch_assoc($res) ) {
            if ( !$user['subscr'][7] ) {
                continue;
            }
            $this->recipient[] = array(
                'email' => "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>",
                'extra' => array(
                    'USER_NAME'    => $user['uname'],
                    'USER_SURNAME' => $user['usurname'],
                    'USER_LOGIN'   => $user['login']
                )
            );
            if ( ++$i >= 20000 ) {
                $this->bind($msgid);
                $this->recipient = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->bind($msgid);
        }
        $this->recipient = array();
        return $i;
    }
       
    
    /**
     * Рассылка для работодателей с незаполненным профилем. Рассылается с hourly.php
     * 
     * @return integer  количество пользователей получивших рассылку
     */
    function withoutProfileEmployers() {
        $DB = new DB('master');
        $this->recipient = '';
        $this->subject   = "Напоминание от FL.ru: пожалуйста, заполните свой профиль";
        $message = "<p>
&nbsp;&nbsp;&nbsp;&nbsp;Мы заметили, что у вас не полностью заполнен профиль. Однако именно на заполненность профиля работодателя обращают внимание ответственные и профессиональные фрилансеры. Мы рекомендуем вам добавить больше информации о себе. Это поможет вам быстрее найти нужного исполнителя на ваши проекты.<br />
&nbsp;&nbsp;&nbsp;&nbsp;Полная инструкция по заполнению профиля находится <a href='https://feedback.fl.ru/'>здесь</a>. Вы можете ознакомиться с ней в любое время.<br />
&nbsp;&nbsp;&nbsp;&nbsp;<a href='{$GLOBALS['host']}'>Будем рады видеть вас на FL.ru</a>!
</p>";
        $this->message = $this->GetHtml( 
            '', 
            $message,
            array( 'header' => 'default', 'footer' => 'default' ),
            array( 'login' => $user['login'], 'utm_campaign' => 'clients_profile', 'target_footer' => 1 )
        );
        $msgid = $this->send("text/html");
        if ( !$msgid ) {
            return 0;
        }
        $i = 0;
        $this->recipient = array();
        $res = $DB->query("
            SELECT 
                uid, login, uname, usurname, email, subscr 
            FROM 
                employer u
            WHERE 
                is_active = TRUE
                AND is_banned = B'0'
                AND (birthday IS NULL OR birthday = '1910-01-01')
                AND (country IS NULL OR country = 0)
                AND (site IS NULL OR site = '')
                AND (icq IS NULL OR icq = '')
                AND (jabber IS NULL OR jabber = '')
                AND (phone IS NULL OR phone = '')
                AND (ljuser IS NULL OR ljuser = '')
                AND (skype IS NULL OR skype = '')
                AND (second_email IS NULL OR second_email = '')
                AND (resume IS NULL OR resume = '')
                AND (compname IS NULL OR compname = '')
                AND (logo IS NULL OR logo = '')
                AND (company IS NULL OR company = '')
        ");
        while ( $user = pg_fetch_assoc($res) ) {
            if ( !$user['subscr'][7] ) {
                continue;
            }
            $this->recipient[] = array(
                'email' => "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>",
                'extra' => array(
                    'USER_NAME'    => $user['uname'],
                    'USER_SURNAME' => $user['usurname'],
                    'USER_LOGIN'   => $user['login']
                )
            );
            if ( ++$i >= 20000 ) {
                $this->bind($msgid);
                $this->recipient = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->bind($msgid);
        }
        $this->recipient = array();
        return $i;
    }
    
    
    /**
     * Рассылка работодателям, которые зарегистрировались менее 30 дней назад
     * (вызывается из nsync)
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              id сообщения или 0 в случае ошибки
     */
    public function empRegLess30($msgid, $spamid, $recipients ) {
       $subject = 'Как быстро найти исполнителя на FL.ru';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }

 
    /**
     * Рассылка фрилансерам, которые зарегистрировались на сайте менее 30 дней назад и не купили никакой ПРО
     * (вызывается из nsync)
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              id сообщения или 0 в случае ошибки
     */
    public function frlNotBuyPro($msgid, $spamid, $recipients) {
       $subject = 'Пора зарабатывать на FL.ru!';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    

    /**
     * Рассылка фрилансерам, которые купили тестовый ПРО и не купили обычный ПРО в течение месяца
     * (вызывается из nsync)
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              id сообщения или 0 в случае ошибки
     */
    public function frlBuyTestPro($msgid, $spamid, $recipients) {
       $subject = 'Зарабатывайте больше на FL.ru!';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * Рассылка фрилансерам, которые купили тестовый ПРО и после него только однажды купили обычный
     * (вызывается из nsync)
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              id сообщения или 0 в случае ошибки
     */
    public function frlBuyProOnce($msgid, $spamid, $recipients) {
       $subject = 'Зарабатывайте больше на FL.ru!';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * Рассылка фрилансерам, у которых через 2 недели заканчивается про на 6 или 12 месяцев.
     * (вызывается из nsync)
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              id сообщения или 0 в случае ошибки
     */
    public function frlEndingPro($msgid, $spamid, $recipients) {
       $subject = 'FL.ru: последние дни с аккаунтом PRO';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * Рассылка работодателям опубликовавшим платный проект или конкурс в течение 30 дней
     * (вызывается из nsync)
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              id сообщения или 0 в случае ошибки
     */
    public function empPubPrj30Days($msgid, $spamid, $recipients) {
       $subject = 'Как найти подходящего исполнителя на FL.ru';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
 
    
    /**
     * Рассылка работодателям купившим рассылку в течение 30 дней
     * (вызывается из nsync)
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              id сообщения или 0 в случае ошибки
     */
    public function empBuyMass30Days($msgid, $spamid, $recipients) {
       $subject = 'Как найти подходящего исполнителя на FL.ru';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * Рассылка работодателям активным за 30 дней, но не публиковавшим проектов.
     * (вызывается из nsync)
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              id сообщения или 0 в случае ошибки
     */
    public function empNotPubPrj($msgid, $spamid, $recipients) {
       $subject = 'Публикация проекта – простой способ найти исполнителя';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * Рассылка работодателям  у которых на счету есть 35+ бонусных FM.
     * 
     * @param  integer $msgid       id личного сообщения
     * @param  integer $spamid      id рассылки или NULL, если рассылку нужно создать
     * @param  array   $recipients  массив с uid пользователей для рассылки
     * @return integer              id сообщения или 0 в случае ошибки
     */
    public function empBonusFm($msgid, $spamid, $recipients) {
        $subject = 'Бонусы от FL.ru';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    /**
     * Уведомление об удалении поста или комментария в блогах
     * 
     * @param mixed $mId ID блога / массив ID блогов
     */
    function blogDeleteNotification( $mId = 0 ) {
        $sId    = !is_array($mId) ? array($mId) : $mId;
        $sQuery = 'SELECT u.uname, u.usurname, u.login, b.title, b.post_time 
            FROM blogs_msgs b
            INNER JOIN blogs_themes t ON t.thread_id = b.thread_id
            INNER JOIN users u ON u.uid = b.fromuser_id
            WHERE b.thread_id IN (?l) AND b.reply_to IS NULL';
        
        $aBlogs = $GLOBALS['DB']->rows( $sQuery, $sId );
        if ( $aBlogs ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            
            foreach ( $aBlogs as $blog ) {
                $message = 'Здравствуйте, '. $blog['uname'] .' '. $blog['usurname'] .'

Сожалеем, но модераторы сайта вынуждены были удалить ваш пост в сообществе'. ( trim($blog["title"]) ? ' &laquo;' . ($blog["title"]) . '&raquo;' : '' ) . ' от ' . date( 'd.m.Y', strtotimeEx($blog['post_time']) ) .'

Просим вас впредь быть внимательнее при публикации и соблюдать Правила сайта. 

Это сообщение было отправлено автоматически и не требует ответа. 

Надеемся на понимание, Команда FL.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $blog['login'], $message, '', 1 );
            }
        }
    }
    
    /**
     * Уведомление об удалении предложения в конкурсе
     * 
     * @param mixed $mId ID предложения / массив ID предложений
     */
    function contestOfferDeleteNotification( $mId ) {
        $sId    = !is_array($mId) ? array($mId) : $mId;
        $sQuery = 'SELECT po.id, po.project_id, f.uid, f.login, f.uname, f.usurname, p.name 
            FROM projects_offers po 
            INNER JOIN projects p ON p.id = po.project_id 
            INNER JOIN freelancer f ON f.uid = po.user_id 
            WHERE po.id IN (?l)';
        
        $aOffers = $GLOBALS['DB']->rows( $sQuery, $sId );
        
        if ( $aOffers ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            
            foreach ( $aOffers as $aOne ) {
                
                $aOne['name'] = htmlspecialchars($aOne['name'], ENT_QUOTES, 'CP1251', false);
                
                $sMessage = 'Здравствуйте, '. $aOne['uname'] .' '. $aOne['usurname'] .'

Сожалеем, но из-за нарушения Правил модераторы сайта вынуждены были удалить вашу работу в конкурсе &laquo;'. $aOne['name'] .'&raquo;
'. $GLOBALS['host'] . getFriendlyURL('project', $aOne['project_id']) .'?offer='. $aOne['id'] .'#offer-'. $aOne['id'] .'

Просим вас впредь быть внимательнее при публикации работ и соблюдать Правила сайта. 

Это сообщение было отправлено автоматически и не требует ответа. 

Надеемся на понимание, Команда FL.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $aOne['login'], $sMessage, '', 1 );
            }
        }
    }
    
    /**
     * Уведомление об удалении комментария к работе в конкурсе
     * 
     * @param mixed $mId ID комментария / массив ID комментариев
     */
    function contestMessageDeleteNotification( $mId ) {
        $sId    = !is_array($mId) ? array($mId) : $mId;
        $sQuery = 'SELECT o.id, o.project_id, u.uid, u.login, u.uname, u.usurname, p.name 
            FROM projects_contest_msgs m 
            INNER JOIN projects_contest_offers o ON o.id = m.offer_id 
            INNER JOIN projects p ON p.id = o.project_id 
            INNER JOIN users u ON u.uid = m.user_id 
            WHERE m.id IN (?l)';
        
        $aMessages = $GLOBALS['DB']->rows( $sQuery, $sId );
        
        if ( $aMessages ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            
            foreach ( $aMessages as $aOne ) {
                
                $aOne['name'] = htmlspecialchars($aOne['name'], ENT_QUOTES, 'CP1251', false);
                
                $sMessage = 'Здравствуйте, '. $aOne['uname'] .' '. $aOne['usurname'] .'

Сожалеем, но из-за нарушения Правил модераторы сайта вынуждены были удалить ваш комментарий к работе в конкурсе &laquo;'. $aOne['name'] .'&raquo;
'. $GLOBALS['host'] . getFriendlyURL('project', $aOne['project_id']) .'?offer='. $aOne['id'] .'#offer-'. $aOne['id'] .'

Просим вас впредь быть внимательнее при публикации комментариев и соблюдать Правила сайта. 

Это сообщение было отправлено автоматически и не требует ответа. 

Надеемся на понимание, Команда FL.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $aOne['login'], $sMessage, '', 1 );
            }
        }
    }
    
    /**
     * Уведомление об удалении предложений фрилансеров
     * 
     * @param mixed $mId ID предложения фрилансера / массив ID предложений фрилансеров
     */
    function freelancerOfferBlockedNotification( $mId ) {
        $sId    = !is_array($mId) ? array($mId) : $mId;
        $sQuery = 'SELECT o.title, o.post_date, o.reason, f.uid, f.login, f.uname, f.usurname
            FROM freelance_offers o 
            INNER JOIN freelancer f ON f.uid = o.user_id 
            WHERE o.id IN (?l)';
        
        $aOffers = $GLOBALS['DB']->rows( $sQuery, $sId );
        
        if ( $aOffers ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            
            foreach ( $aOffers as $aOne ) {
                $sMessage = 'Здравствуйте, '. $aOne['uname'] .' '. $aOne['usurname'] .'

Сожалеем, но из-за нарушения Правил модераторы сайта вынуждены были заблокировать вашу услугу &laquo;'. $aOne['title'] .'&raquo;  от '. date('d.m.Y', strtotimeEx($aOne['post_date'])) .' в разделе &laquo;Сделаю&raquo;

Причина блокировки: '. $aOne['reason'] .'

Просим вас впредь быть внимательнее при публикации услуг и соблюдать Правила сайта. 

Это сообщение было отправлено автоматически и не требует ответа. 

Надеемся на понимание, Команда FL.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $aOne['login'], $sMessage, '', 1 );
            }
        }
    }
        
    /**
	 * Посылаем уведомление пользователю о его некорректном проекте исходя из жалоб пользователей
	 *
	 * @param array $ids имеет вид array('1-2') где 1 - ИД проекта, 2 - Тип жалобы
	 */
	function ProjectComplainsSend($ids, $connect = NULL) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        if(!is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach ( $ids as $id ) {
            list($project_id, $type) = explode("-", $id);
            $complains[$project_id][] = $type;
        }
        
        // Рассылаем
        foreach($complains as $project_id => $types) {
            $project = new projects();
            $prj     = $project->GetPrj(0, $project_id, 1);
            $emp     = new users();
            $emp->GetUserByUID($prj['user_id']);
            if (!$emp->email || substr($emp->subscr, 4, 1) != '1' || $emp->is_banned == '1') continue;
            
            $prj['name'] = htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false);
            
            $text_type = "";
            foreach($types as $type) {
                switch($type) {
                    case '6':
                        $this->subject = "Отредактируйте свой проект на FL.ru";
                        
                        $message  = "Пожалуйста, измените раздел/подраздел, в котором опубликован ваш проект «<a href='{$GLOBALS['host']}" . getFriendlyURL("project", $project_id) . $this->_addUrlParams('e')."'>{$prj['name']}</a>». По сообщениям пользователей, проект размещен неверно: задание не соответствует специализации фрилансеров, которую вы указали.<br/><br/>"; 
                        $message .= "<a href='{$GLOBALS['host']}/public/?step=1&public={$project_id}" . $this->_addUrlParams('e') . "'>Перейти к редактированию проекта</a><br/><br/>";
                        $message .= "Шансы найти подходящего исполнителя выше, если ваш проект опубликован правильно. Вы можете ознакомиться с инструкцией по <a href='http://feedback.fl.ru/" . $this->_addUrlParams('e') ."'>редактированию</a> проектов в нашем сообществе поддержки.";
                        break;
                    case '7':
                        $this->subject = "Укажите дополнительную информацию по вашему проекту на FL.ru";
                        
                        $message  = "По сообщениям пользователей, вы указали недостаточно информации при публикации проекта «<a href='{$GLOBALS['host']}" . getFriendlyURL("project", $project_id) . $this->_addUrlParams('e')."'>{$prj['name']}</a>». Возможно, вам стоит описать подробнее суть задачи, дополнить техническое задание, указать сроки выполнения работы.<br/><br/>";
                        $message .= "<a href='{$GLOBALS['host']}/public/?step=1&public={$project_id}" . $this->_addUrlParams('e') . "'>Перейти к редактированию проекта</a><br/><br/>";
                        $message .= "Вы можете ознакомиться с инструкцией по <a href='http://feedback.fl.ru/" . $this->_addUrlParams('e') ."'>редактированию</a> проектов в нашем сообществе поддержки. ";
                        break;
                    case '8':
                        $this->subject = "Укажите бюджет вашего проекта на FL.ru";
                        
                        $message  = "По сообщениям пользователей, вы не указали размер гонорара исполнителя в вашем проекте «<a href='{$GLOBALS['host']}" . getFriendlyURL("project", $project_id) . $this->_addUrlParams('e')."'>{$prj['name']}</a>».<br/><br/>";
                        $message .= "Для того чтобы фрилансеры могли оценить соотношение «объем работы/оплата» и принять решение о подаче заявки на выполнение проекта, им необходимо знать бюджет. Пожалуйста, заполните поле «Бюджет» в форме редактирования проекта.<br/><br/>";
                        $message .= "<a href='{$GLOBALS['host']}/public/?step=1&public={$project_id}" . $this->_addUrlParams('e') . "'>Перейти к редактированию проекта</a><br/><br/>";
                        $message .= "Вы можете ознакомиться с инструкцией по <a href='http://feedback.fl.ru/" . $this->_addUrlParams('e') ."'>редактированию</a> проектов в нашем сообществе поддержки. ";
                        break;
                    default:
                        continue;
                        break;
                }
                
                $this->message   = $this->GetHtml( $emp->uname, $message, array('header'=>'default', 'footer'=>'feedback_default'), array('login' => $emp->login));
                $this->recipient = $emp->uname.' '.$emp->usurname.' ['.$emp->login.'] <'.$emp->email.'>';
                $this->send('text/html');
                //$this->SmtpMail('text/html');
                projects::updateComplainCounters(array('is_send' => true), $project_id, "AND is_send = false AND type = {$type}");
            }
        }
    }
    
    /**
     * Отсылаем уведомление о бане пользователя в сообществе
     * @see trigger "aIU commune_members/mail"
     * 
     * @param array $ids     Список заблокированных
     * @param type $connect
     */
    public function CommuneMemberBan($ids, $connect = NULL) {
        if(!is_array($ids)) return;
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/commune.php';
        
        foreach($ids as $id) {
            list($user_id, $commune_id) = explode("-", $id);
            $user = new users();
            $user->GetUserByUID($user_id);
            if(!$user->email || substr($user->subscr, 5, 1) != '1' || $user->is_banned == '1') {
                continue;
            }
            $comm_link = $GLOBALS['host'].'/commune/?id='.$commune_id;
            
            $comm = commune::getCommuneInfoForFriendlyURL($commune_id);
            $this->subject  = "Вас заблокировали в сообществе ";
            $body = $this->subject . ' «<a href="'.$comm_link.$this->_addUrlParams('b', '&').'">'.$this->ToHtml($comm['name'], 1).'</a>». ';
            $this->subject .= "«{$comm['name']}»";
            $body .= "К сожалению, теперь вы не можете создавать новые темы и оставлять комментарии в сообществе.";
            
            $this->recipient = $user->uname.' '.$user->usurname.' ['.$user->login.'] <'.$user->email.'>';
            $this->message = $this->GetHtml($user->uname, $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$user->login));
            $this->send('text/html');
        }
        
        return $this->sended;
    }

    /**
     * Посылает уведомление о том что включено автооплата с перечислением того что будет автоплачивать
     * Включение одного или нескольких автопродлений вместе с активацией способа оплаты
     *
     * @param $uids         Список ИД пользователей
     * @param null $connect
     * @return int
     */
    public function activateWallet($uids, $connect = NULL) {
        if(!is_array($uids)) return;

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/billing.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wallet/wallet.php';

        foreach($uids as $uid) {
            if((int)$uid <= 0) continue; // Мало ли
            $bill   = new billing((int)$uid);
            if( substr($bill->user['subscr'],15,1) !='1' ) continue;
            $autopay = billing::getAllAutoPayed($uid);
            if(empty($autopay)) continue; // Автопродление не включено

            $wallet     = walletTypes::initWalletByType($uid);
            if(!walletTypes::checkWallet($wallet)) continue;  // Метод оплаты уже не действителен
            $walletName = str_replace("%WALLET%", $wallet->getWalletBySecure(), walletTypes::getNameWallet($wallet->data['type'], 2));

            $message  = "Вы подключили {$walletName} в качестве средства оплаты при автопродлении следующих услуг:<br/><br/>";
            foreach($autopay as $payed) {
                $message .= "-&nbsp;{$payed['name']} ({$payed['cost']} руб.)<br/>";
            }
            $message .= "<br/>";
            $message .= "Информацию о способах оплаты и автопродлении услуг, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='http://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>сообществе поддержки</a>.";

            $this->subject   = "FL.ru: Подключение нового способа оплаты";
            $this->recipient = "{$bill->user['uname']} {$bill->user['usurname']} [{$bill->user['login']}] <{$bill->user['email']}>";
            $this->message   = $this->GetHtml($bill->user['uname'], $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$bill->user['login']));

            $this->send('text/html');
        }

        return $this->sended;
    }
}