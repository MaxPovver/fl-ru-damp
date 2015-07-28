<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
setlocale(LC_TIME, "ru_RU");
/**
 * SMTP Mail API. Класс для отправки сообщение SMTP серверу.
 * Родитель для классов smail и pmail.
 *
 */
class SMTP {

    /**
     * Обратный адрес сервиса.
     *
     * @var string
     */
	public $from = 'FL.ru <no_reply@free-lance.ru>';

    /**
     * Получатель письма, например Федя <fedya@mail.ru>.
     *
     * @var string
     */
	public $recipient = '';

    /**
     * Тема письма.
     *
     * @var string
     */
	public $subject = '';

    /**
     * Текст письма.
     *
     * @var string
     */
	public $message = '';
	
    /**
     * Лог сообщений между сервером и скриптом для текущего объекта.
	 * Сохраняются только системные сообщения без тела письма.
     *
     * @var string
     */
	public $log = '';

    /**
     * Количество успешно отправленных сообщений.
     *
     * @var integer
     */
	public $sended = 0;

    /**
     * Хост SMTP сервера.
     *
     * @var string
     */
	protected $server = 'localhost';

    /**
     * Порт SMTP сервера.
     *
     * @var string
     */
	protected $port = 25;

    /**
     * Кодировка отправляемых писем.
	 * Если потребуется изменить, необходимо еще поправить self::charset_convert().
     *
     * @var string
     */
	protected $charset = 'windows-1251';
	
    /**
     * Массив с прикрепленными файлами
	 * @see self::AttachFile()
     *
     * @var array
     */
	public $attaches = array();

    /**
     * Количество прикрепленных файлов
     *
     * @var integer
     */
	private $attaches_nums = 0;

    /**
     * Сокет к SMTP серверу
	 * Один для всех создаваемых объектов.
     *
	 * @var resource
     */
	protected static $socket = NULL;

    /**
     * Количество созданных объектов класса.
     *
	 * @var integer
     */
	protected static $objects = 0;
	
    /**
     * Лог сообщений между сервером и скриптом для всех объектов этого класса.
	 * Сохраняются только системные сообщения без тела письма.
     *
     * @var string
     */
	public static $flog = '';

    public function splitMessage($content) {
        $len  = strlen($content);
        $prev = '';
        $message = '';
        for ( $i = 0, $j = 0; $i < $len; $i++, $j++ ) {
            if ( ($j > 80) && ($content{$i} == ' ') ) {
                $message .= "\r\n";
                $j = 0;
            } else if ( $content{$i} == "\n" ) {
                if ( $prev != "\r" ) {
                    $message .= "\r\n";
                } else {
                    $message .= "\n";
                }
                $j = 0;
            } else {
                $message .= $content{$i};
            }
            $prev = $content{$i};
        }
        
        return $message;
    }
    
    /**
     * Обработка текста сообщения
     */
    public function prepareMessage() {
        $this->message = preg_replace("~(http|https):/\{(.*?)\}?/([^<|^\s]*)~mix", '<a href="$1://$3" target="_blank">$2</a>', $this->message);
    }
    
    /**
     * Отправка email сообщения
     * 
     * @param  string $content_type  mime type сообщения
     * @param  array  $files         массив с id прикрепленных файлов (file_template)
     * @return integer               id письма (0 - ошибка)
     */
    public function send($content_type='text/plain', $files = array()) {
        $DB   = new DB('spam');
        if($this->prepare) $this->prepareMessage();
        $message = $this->splitMessage($this->message);
        if ( is_array($this->recipient) || empty($this->recipient) ) {
            $spamid = $DB->val(
                "SELECT mail.send(?, ?, ?, ?, ?, ?a)", 
                $this->from, 
                NULL, 
                $content_type, 
                $this->subject, 
                $message,
                $files
            );
        } else {
            $spamid = $DB->val(
                "SELECT mail.send(?, ?, ?, ?, ?, ?a)", 
                $this->from, 
                $this->recipient, 
                $content_type, 
                $this->subject, 
                $message,
                $files
            );
        }
        if ( $spamid && is_array($this->recipient) ) {
            $this->bind($spamid);
        }
        return $spamid;
    }
    
    
    /**
     * Добавить адресатов к рассылке
     * 
     * @param  integer  $spamid  id письма для которой добавляем адресатов
     * @param  boolean  $unset_recipient  можно ли уничтожать данные $this->recipient после использования?
     * @return integer           id письма (0 - ошибка) 
     */
    public function bind($spamid, $unset_recipient = false) {
        $DB = new DB('spam');
        $i  = 0;
        if ( is_array($this->recipient) ) {
            $recipients = array();
            foreach ( $this->recipient as $j=>$r ) {
                if ( is_array($r) && !empty($r['email']) ) {
                    $extra = array();
                    if ( $r['extra'] ) {
                        foreach ( $r['extra'] as $k => $v ) {
                            $extra[] = $k . '=' . str_replace('&', '&&', $v);
                        }
                    }
                    $recipients[] = array($r['email'], implode('&', $extra));
                } else {
                    $recipients[] = $r;
                }
                $i++;
                if ( $i % 5000 == 0 ) {
                    $DB->query("SELECT mail.bind(?, ?a)", $spamid, $recipients);
                    unset($recipients);
                    $recipients = array();
                    $i = 0;
                }
                if($unset_recipient) {
                    unset($this->recipient[$j]);
                }
            }
            if ( $i ) {
                $DB->query("SELECT mail.bind(?, ?a)", $spamid, $recipients);
                unset($recipients);
            }
        } else if ( is_string($this->recipient) && $this->recipient != '' ) {
            $spamid = $DB->query("SELECT mail.bind(?, ?a)", $spamid, array($this->recipient));
            if($unset_recipient) {
                unset($this->recipient);
            }
        }
        return $spamid;
    }
    
    
    /**
	 * Отправляет сообщение.
	 * При первом вызове открывает сокет к SMTP серверу (см. self::Connect()) и закрывает его в деструкторе.
	 * 
	 * @param   string   mime тип сообщения
	 * @return  boolean  TRUE если сообщение отправлено; FALSE если не удалось отправить или установить соединение
	 */
	public function SmtpMail($content_type='text/plain', &$files = array()) {
		// если установлена константа SERVER == beta или IS_LOCAL, то почта будет отправлятся только адресатам из $TESTERS_MAIL
		if ((defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE)) {
			if (preg_match("/<([^>]+)>$/", $this->recipient, $o)) {
				$test = $o[1];
			} else {
				$test = $this->recipient;
			}
			if (!is_array($GLOBALS['TESTERS_MAIL']) || !in_array($test, $GLOBALS['TESTERS_MAIL'])) {
				$this->sended++;
				return TRUE;
			}
		}
		
		$from      = $this->encode_email($this->from);
		$recipient = $this->encode_email($this->recipient);
		$subject   = $this->encode(htmlspecialchars_decode($this->subject, ENT_QUOTES));
		$mail_from = $from;
		$rcpt_to   = $recipient;
		if($brk = strpos($mail_from, '<')) $mail_from = substr($mail_from, $brk);
		if($brk = strpos($rcpt_to, '<'))   $rcpt_to = substr($rcpt_to, $brk);
    $message   = str_replace(array("\\'", '\\"', "\\\\"), array("'", '"', "\\"), $this->message);
    $message   = preg_replace("'[\r\n]+\.[ \r\n]+'", ".\r\n", $message);
		if (!self::$socket && !$this->Connect()) return FALSE;

		if ($this->cmd("MAIL FROM: $mail_from") != 250
			|| $this->cmd("RCPT TO: $rcpt_to") != 250
			|| $this->cmd("DATA") != 354
		) {
			$this->cmd("RSET");
			return FALSE;
		}

		// формирование и отправка тела письма
		$body = "Mime-Version: 1.0\r\n";
		if (!empty($files)) {
			$boundary = md5(uniqid(time()));
			$body .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
			$body .= "To: {$recipient}\r\n";
			$body .= "From: {$from}\r\n";
			$body .= "Subject: {$subject}\r\n";
			$body .= "--{$boundary}\r\n";
		}
		$body .= "Content-Type: {$content_type}; charset={$this->charset}\r\n";
		$body .= "Content-Transfer-Encoding: 8bit\r\n";
		if (empty($files)) {
			$body .= "To: {$recipient}\r\n";
			$body .= "From: {$from}\r\n";
			$body .= "Subject: {$subject}\r\n";
		}
		$body .= "\r\n";
		$body .= "{$message}\r\n\r\n";
		fwrite(self::$socket, $body);
		// атачи
		if (!empty($files)) {
			for ($i=0; $i<count($files); $i++) {
				if (!empty($files[$i])) {
					fputs(self::$socket, "--{$boundary}\r\n".$files[$i]);
				}
			}
		}

		if ($this->cmd(".") != 250) {
			$this->cmd("RSET");
			return FALSE;
		}

		$this->sended++;
		return TRUE;
    }

	
    /**
	 * Устанавливает соединение с SMTP сервером, если оно еще не было установлено.
	 * Метод вызывается в self::Send() поэтому вызывать его явно обычно не требуется. Его может быть 
	 * удобно вызывать перед отправкой пачки сообщений, чтобы заранее убедится что соединение установилось
	 *
	 * @return   boolean   TRUE если соединение установлено; FALSE если установить соединение не удалось
	 */
	public function Connect() {
		if (self::$socket) return TRUE;
		if (!(self::$socket = fsockopen($this->server, $this->port, $errno, $errstr, 5))) {
			return FALSE;
		}
		if ($this->cmd(NULL) != 220 || $this->cmd("HELO {$this->server}") != 250) {
			fclose(self::$socket);
			self::$socket = NULL;
			return FALSE;
		}
		return TRUE;
	}
	

    /**
	 * Закрывает соединение с SMTP сервером.
	 * Метод вызывается в деструкторе, при уничтожении последнего созданного объекта класса,
	 * поэтому вызывать его явно обычно не требуется.
	 * Если его вызвать до того, как будет уничтожен последний объект и какой-либо объект в дальнейшем
	 * еще будет отсылать письма, то соединение создастся заново (переподключится).
	 *
	 * @return   boolean   TRUE если соединение закрыто; FALSE если закрыть не удалось
	 */
    public function Close(){
		if (self::$socket) {
			$this->cmd("QUIT");
			fclose(self::$socket);
			self::$socket = NULL;
		}
                //чистим логи
                $this->log = self::$flog = '';
		return TRUE;
	}

	
    /**
	 * Проверяет, создано ли в данный момент соединение с SMTP сервером.
	 *
	 * @return   boolean   TRUE если соединение создано; FALSE если нет
	 */
	public function Сonnected() {
		return is_resource(self::$socket);
	}

	
    /**
	 * Конструктор. Наращивает счетчик созданных объектов класса.
	 *
	 */
	public function __construct() {
		++self::$objects;
	}

	
    /**
	 * Деструктор. Закрывает соединение с SMTP сервером, если больше нет объектов класса
	 *
	 */
	public function __destruct() {
		if (--self::$objects <= 0) {
			$this->Close();
			// лог
			//file_put_contents('/tmp/smtp.log', self::$flog);
		}
	}
	
	
    /**
     * Обрабатывает файл(ы) для пересылки по почте
	 * Именно отработанные через этот метод файлы нужно использовать в качестве параметра для self::SmtpMail
     * 
     * @param   mixed   $files    файл в виде объекта класса CFile или массив таких объектов
     * @return  array             обработанные файлы
     */
	public function CreateAttach($files) {
		if (!is_array($files)) $files = array($files);
		$i = 0;
		$res = array();
		foreach ($files as $file) {
			if (($file instanceof CFile) && ($file->name != '') && ($fcnt = @file_get_contents(WDCPREFIX_LOCAL."/{$file->path}{$file->name}"))) {
				$original_name = $this->encode( htmlspecialchars_decode($file->original_name, ENT_QUOTES) );
				$res[$i]  = "Content-Type: ".$file->getContentType()."; name=\"{$original_name}\"\r\n";
				$res[$i] .= "Content-Transfer-Encoding: base64\r\n";
				$res[$i] .= "Content-Disposition: attachment; filename=\"{$original_name}\"\r\n\r\n";
				$res[$i] .= chunk_split(base64_encode($fcnt))."\r\n";
				$i++;
			}
		}
		return $res;
	}
	
    /**
     * Обрабатывает локальные файл(ы) для пересылки по почте
	 * Именно отработанные через этот метод файлы нужно использовать в качестве параметра для self::SmtpMail
     * 
     * @param   mixed   $files    путь до файла или массив путей
     * @return  array             обработанные файлы
     */
	public function CreateLocalAttach($files) {
		if (!is_array($files)) $files = array($files);
		$i = 0;
		$res = array();
		foreach ($files as $file) {
			if (($file != '') && ($fcnt = @file_get_contents($file))) {
				$filename = basename($file);
                $out = exec("file -i '{$file}'");
				$contentType = preg_replace('/^[^:]+:\s*([^\s;]+).*$/', '$1', $out);
				$res[$i]  = "Content-Type: ".$contentType."; name=\"{$filename}\"\r\n";
				$res[$i] .= "Content-Transfer-Encoding: base64\r\n";
				$res[$i] .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n";
				$res[$i] .= chunk_split(base64_encode($fcnt))."\r\n";
				$i++;
			}
		}
		return $res;
	}
	

    /**
     * Конвертирует plain text в HTML
     * 
     * @param   string   $text    исходный текст
     * @param   boolean  $body    если 1, то ссылки обрабатываться не будут
     * @return  string            html-сообщение.
     */	
	public function ToHtml($text, $nolink=0) {
		$text = str_replace(array("<", ">", "'", "\"", ' - ', ' -- ', "\n"), array("&lt;", "&gt;", "&#039;", "&quot;", ' &#150; ', ' &mdash; ', ' <br/>'), $text);
        $text = preg_replace('~(https?:/){[^}]+}/~', '$1/', $text); // чистим шаблоны гиперссылок.
		if (!$nolink) {
			$text = preg_replace_callback("/((https?\:\/\/|www\.)[-a-zA-Zа-яА-ЯёЁ0-9\.]+\.[a-zA-Zа-яА-ЯёЁ]{2,30}(?:\/[^\s]+)?)/", array($this, 'ToHtml_callback'), $text);
			$text = preg_replace("/([-_a-zA-Z0-9\.]+?\@[-a-zA-Zа-яА-ЯёЁ0-9\.]+\.[a-zA-Zа-яА-ЯёЁ]{2,30})/", "<a href='mailto:\$1'>\$1</a>", $text);
		}
		$text = textWrap($text, 76);
		return $text;
	}
	
	/**
	 * Вспомогательная callback функция @see smtp::ToHtml
	 *
	 * @param  array $m
	 * @return string
	 */
	private function ToHtml_callback($m) {
		if ($m[2] == 'http://' || $m[2] == 'https://') {
			$link = $m[1];
		} else {
			$link = 'http://'.$m[1]; 
		}
		return "<a href='$link'>$link</a>";
	}

    /**
     * Формирует тело сообщения в формате HTML, в стиле сообщений от FL.ru.
     * 
     * @param   string  $uname    имя пользователя-получателя сообщения.
     * @param   string  $body     исходный текст сообщения.
     * @param   array   $format   можно расширять и использовать как угодно, например, задать так, чтобы не было приветствия.
     * @return  string           html-сообщение.
     */
    public function GetHtml($uname, $body, $format=array('header'=>'default', 'footer'=>'default'), $params = null) {
        if (!empty($format) && !is_array($format)) $format = array('header'=>$format, 'footer'=>$format);
        $body = preg_replace('~(https?:/){[^}]+}/~', '$1/', $body); // чистим шаблоны гиперссылок.
        $html_header = '';
        $html_footer = '';
        if($format['footer'] == 'frl_subscr_projects' || $format['footer'] == 'frl_simple_projects') {
            $format['footer'] = str_replace("frl_", "", $format['footer']);
            $role = 'заказчиком';
        } else {
            $role = 'фрилансерами';
        }
        
        if($format['footer'] == 'sub_emp_projects') {
            $subscr = true;
            $format['footer'] = str_replace("sub_", "", $format['footer']);
        } else {
            $subscr = false;
        }
        
        if ($format['header']=='simple_with_add') {
            
            $html_header = '
                <div style="font-size:10px; color:#7e7e7e;">
                    Чтобы не пропустить ни одного письма от команды <a style="font-size:10px; color:#006ed6" target="_blank" href="'.$GLOBALS['host'].'">FL.ru</a>, добавьте наш адрес <a style="font-size:10px; color:#006ed6" target="_blank" href="mailto:no_reply@free-lance.ru">no_reply@free-lance.ru</a> в вашу адресную книгу. 
                    <a style="font-size:10px; color:#006ed6" target="_blank" href="https://feedback.fl.ru/topic/532678-instruktsiya-po-dobavleniyu-email-adresa-flru-v-spisok-kontaktov/">Инструкция</a>
                </div>
                <br/>
                <br/>
            ';
            
            $html_header .= "Здравствуйте".($uname ? ", {$uname}." : "!");
            
        } elseif ($format['header']=='default' || $format['header']=='simple' || $format['header']=='info') {
            $html_header .= "Здравствуйте".($uname ? ", {$uname}." : "!");
        
        } elseif ($format['header']=='noname') {
            $html_header .= "Здравствуйте!";
        } elseif ($format['header']=='default_new') {
            $html_header .= "Здравствуйте, %USER_NAME%!";
        } elseif ($format['header'] == 'subscribe') {
            $html_header .= "Здравствуйте".($uname ? ", {$uname}." : "!")." ";
            $html_header .= "В ".($params['type']==1 ? 'блоге' : 'топике '.($params['topic_title'] ? ' &laquo;'.$params['topic_title'].'&raquo;' : '').' сообщества').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '').", "; 
            $html_header .= "на котор".($params['type']==1 ? 'ый' : 'ое')." вы подписаны, появился новый комментарий.";
        } elseif ($format['header'] == 'subscribe_edit_comment') {
            $html_header .= "Здравствуйте".($uname ? ", {$uname}." : "!")." <br/><br/>";
            $html_header .= "В ".($params['type']==1 ? 'блоге' : 'сообществе').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '').", "; 
            $html_header .= "на котор".($params['type']==1 ? 'ый' : 'ое')." вы подписаны, отредактирован комментарий.";
        } elseif ($format['header'] == 'subscribe_edit_post') {
            $html_header .= "Здравствуйте".($uname ? ", {$uname}." : "!")." <br/><br/>";
            $html_header .= ($params['type']==1 ? 'В  блоге' : 'Топик '.($params['topic_name'] ? ' &laquo;'.$params['topic_name'].'&raquo;' : '').' сообщества').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '');
            $html_header .= ($params["to_subscriber"] ? ", на который вы подписаны," : "")." отредактирован.";
        }  elseif ($format['header'] == 'subscribe_delete_comment') {
            $html_header .= "Здравствуйте".($uname ? ", {$uname}." : "!")." <br/><br/>";
            $html_header .= "В ".($params['type']==1 ? 'блоге' : 'сообществе').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '').", "; 
            $html_header .= "на котор".($params['type']==1 ? 'ый' : 'ое')." вы подписаны, удален комментарий.";
        } elseif ($format['header'] == 'subscribe_delete_post') {
            $html_header .= "Здравствуйте".($uname ? ", {$uname}." : "!")." <br/><br/>";
            $html_header .= ($params['type']==1 ? 'Блог' : ($params['is_comment']? 'Комментарий в топике  ': 'Топик ').($params['topic_name'] ? ' &laquo;'.$params['topic_name'].'&raquo;' : '').' сообщества ').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '').", "; 
            if ( !$params['to_topicstarter'] ) {
                $html_header .= "на который вы подписаны, удален.";
            } else {
                $html_header .= "удален " . ($params["is_author"] ? "автором темы" : "модератором сообщества") .".";
            }
        }

        if(!empty($params['login'])) {
            $lnk_setup_mail = "<a href='{$GLOBALS['host']}/unsubscribe?ukey=".users::GetUnsubscribeKey($params['login'])."'>на этой странице</a>";
        } else {
            if ( empty($params['target_footer']) ) {
                $lnk_setup_mail = "на странице &laquo;Уведомления/Рассылка&raquo;";
            } else {
                $lnk_setup_mail = "<a href='{$GLOBALS['host']}/unsubscribe?ukey=%UNSUBSCRIBE_KEY%'>на этой странице</a>";
            }
        }
        
        if ( !empty($params['utm_campaign']) ) {
            $lnk_team = "<a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign={$params['utm_campaign']}{$this->_addUrlParams('b', '&')}'>FL.ru</a>";
        } 
        else {
            $lnk_team = "<a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        }

        if ($format['footer']=='default') {
            //$html_footer .= "Вы можете отключить уведомления {$lnk_setup_mail}.";
            //$html_footer .= "<br><br>";
            //$html_footer .= "Команда FL.ru благодарит вас за участие в жизни нашего портала.";
            $html_footer .= "<br><br>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда {$lnk_team}";
        } else if ($format['footer']=='feedback_default') {
            $html_footer .= "По всем возникающим вопросам вы можете обращаться в нашу <a href='https://feedback.fl.ru/' taraget='_blank'>службу поддержки</a>. ";
            //$html_footer .= "<br/>Вы можете отключить уведомления {$lnk_setup_mail}.";
            //$html_footer .= "<br><br>";
            //$html_footer .= "Команда FL.ru благодарит вас за участие в жизни нашего портала.";
            $html_footer .= "<br><br>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда {$lnk_team}";
        } else if ($format['footer'] == 'simple') {
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if ($format['footer'] == 'info_robot') {
            $html_footer .= "Данное письмо отправлено почтовым роботом сервера FL.ru и не требует ответа.";
            $html_footer .= "<br>";
            $html_footer .= "По всем возникающим вопросам вы можете обращаться в нашу <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>службу поддержки</a>.";
            $html_footer .= "<br><br>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if ($format['footer'] == 'norisk_robot') {
            $html_footer .= "Данное письмо отправлено почтовым роботом FL.ru и не требует ответа.";
            $html_footer .= "<br>";
            $html_footer .= "По всем возникающим вопросам вы можете обращаться в нашу <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>службу поддержки</a>.";
            $html_footer .= "<br><br>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if ($format['footer'] == 'info') {
            $html_footer .= "Команда FL.ru";
            $html_footer .= "<br>";
            $html_footer .= "<a href='mailto:info@fl.ru'>info@fl.ru</a>";
            $html_footer .= "<br>";
            $html_footer .= "<a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>{$GLOBALS['host']}</a>";
        } else if ($format['footer'] == 'subscribe') {
            $html_footer .= "Чтобы отключить подобные уведомления, зайдите в ".($params['type']==1 ? 'блог' : 'сообщество')." и нажмите кнопку \"Отписаться\".";
            //$html_footer .= "<br><br>";
            //$html_footer .= "Команда FL.ru благодарит вас за участие в жизни нашего портала.";
            $html_footer .= "<br><br>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if($format['footer'] == 'simple_norisk') {
            $html_footer .= "Если у вас есть какие-то вопросы по «Безопасной Сделке», обращайтесь к нашему менеджеру <a href='mailto:norisk@fl.ru'>norisk@fl.ru</a>";
            $html_footer .= "<br><br>";
            $html_footer .= "Команда FL.ru благодарит вас за участие в жизни нашего портала.";
            $html_footer .= "<br><br>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";     
        } else if($format['footer'] == 'simple_projects') {
            $html_footer .= "В проекте вы выбрали способ оплаты - <strong>Прямая оплата исполнителю.</strong><br>";
            $html_footer .= "В этом случае вы несете все риски, связанные с несвоевременным и/или некачественным выполнением работы. И не имеете возможности обратиться в Арбитраж.";
            $html_footer .= "<br><br>";
            $html_footer .= "Предлагаем вам <strong><a href='{$GLOBALS['host']}/public/?step=1&public={$params['project']['id']}&choose_bs=1'>изменить способ оплаты на Безопасную сделку</a>.</strong><br>";
            $html_footer .= "<a href='{$GLOBALS['host']}/promo/bezopasnaya-sdelka/'>Промо-страница Безопасной сделки</a>.";
                    
            $html_footer .= "<br><br>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>"; 
        } else if($format['footer'] == 'subscr_projects') {
            $html_footer .= "Чтобы минимизировать риски, возникающие в ходе сотрудничества с {$role}, рекомендуем воспользоваться «<a href='{$GLOBALS['host']}/promo/sbr/{$this->_addUrlParams('b')}'>Безопасной Сделкой</a>».";
            $html_footer .= "<br><br>";
            //$html_footer .= "Вы можете отключить уведомления {$lnk_setup_mail}.";
            //$html_footer .= "<br><br>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>"; 
        } else if($format['footer'] == 'emp_projects') {
            if($subscr) {
                //$html_footer .= "<br><br>";
                //$html_footer .= "Вы можете отключить уведомления {$lnk_setup_mail}.";
            }
            $html_footer .= "<br><br>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br>";
            $html_footer .= "команда <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if($format['footer'] == 'simple_adv') {
            $html_footer .= "Владельцы «Аккаунта PRO» - это наиболее активная и профессиональная аудитория сайта. 
                            Хотим напомнить, что на каждого обладателя профессионального аккаунта приходится по 2 опубликованных проекта, 
                            а средний бюджет проекта для PRO составляет более 10 300 рублей. Продлить действие «Аккаунта PRO», 
                            а также ознакомиться с другими возможностями продвижения своего аккаунта на FL.ru можно <a href='{$GLOBALS['host']}/payed/{$this->_addUrlParams('b')}'>здесь</a>.";
            $html_footer .= "<br/><br/>";
            $html_footer .= "Приятной работы,";
            $html_footer .= "<br/><br/>";
            $html_footer .= "команда <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        }
        ob_start(null, 0, true);
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" /> 
</head>
<style type="text/css">
a { color: #003399; text-decoration:underline; font-size:10pt; font-family:Tahoma; }
body { margin:10px; background:#ffffff; color:#000000; font-size:10pt; font-family:Tahoma; }
td { font-size:10pt; font-family:Tahoma; }
</style>
<body>
<table cellspacing="0" border="0" cellpadding="0" width="100%">
<tbody>
<tr>
<td>
<?=($html_header? "{$html_header}<br><br>": "")?>
<?=$body?>
<br><br><?=$html_footer?>
</td>
</tr>
</tbody>
</table>
</body>
</html>
<?
    $ret = ob_get_clean();
        return $ret;
    }
	
	
	/**
	 * Отсылает команду SMTP серверу
	 *
	 * @param   string   команда
	 * @return  integer  код ответа SMTP сервера
	 */
	protected function cmd($comm) {
		if ($comm) {
			fwrite(self::$socket, "{$comm}\r\n", strlen($comm)+2);
			$this->log .= "{$comm}\r\n";
			self::$flog .= "{$comm}\r\n";
		}
		$line = '';
		$out  = '';
		$c = 0;
		while ((strpos($out, "\r\n") === FALSE || substr($line, 3, 1) !== ' ') && $c < 100) {
			$line = fgets(self::$socket, 1024);
			$out .= $line;
			$c++;
		}
		$this->log .= $out;
		self::$flog .= $out;
		if (preg_match("/^([0-9]{1,3}) (.+)$/", $out, $o)) {
			return (int) $o[1];
		}
		return 0;
	}

	
    /**
     * Кодирует текст в BASE64 для нормального прохождения через почтовые сервера не поддерживающие кириллицу
     * 
     * @param  string  $text  текст, который необходимо закодировать
     * @return string         закодированный текст
     */
    private function encode($text) {
        if ($text && $this->charset) {
            // define start delimimter, end delimiter and spacer
            $end = "?=";
            $start = "=?" . $this->charset . "?B?";
            $spacer = $end . " " . $start;
            // determine length of encoded text within chunks
            // and ensure length is even
            // Здесь можно увеличить длину > 128, если будут проблемы с отправкой юзерам с длинющими именами.
            $length = 128 - strlen($start) - strlen($end);
            $length = $length - ($length % 4);
            // encode the string and split it into chunks
            // with spacers after each chunk
            $text = base64_encode($text);
            $text = chunk_split($text, $length, $spacer);
            // remove trailing spacer and
            // add start and end delimiters
            $spacer = preg_quote($spacer);
            $text = preg_replace("/" . $spacer . "$/", "", $text);
            $text = $start . $text . $end;
        }
        return $text;
    }

	

    /**
     * Подготавливает строку с получателями сообщений, кодирует везде имя получателя в соотвествии с требованиями.
     * @see smail::encode()
     * 
     * @param   string   $in_str   имя получателя вместе с мылом, например "Федя <fedya@mail.ru>".
     * @param   string   $charset  кодировка (та же, что используется в исходном сообщении).
     * @return  string             готовая строка для использования в протоколе.
     */
    private function encode_email($text) {
        $subj = preg_match_all("'([^<]*)<([^>]*)>[/s,]*'", $text, $matches);
        $out  = array();
        foreach ($matches[1] as $ikey => $sting)
            $out[] = $this->encode(trim($sting))." <".$matches[2][$ikey].">";
        if (count($out)) {
            $out_str = implode(", ", $out);
        }
        else {
            $out_str = $text;
        }
        unset($out);
        return $out_str;
    }
	
	/**
     * отдает окончание для ссылки
     * @param mixed $role 'f'(фрилансер) или 'e'(работодатель) или 'b'(оба)
     * @param string $firstChar первый символ в результате, ? или &
     * @return type string
     */
    protected function _addUrlParams($role, $firstChar = '?') {
        if ($role == 'f') { 
            $params = 'utm_source=newsletter4&utm_medium=uvedomlenie&utm_campaign=free-lancer'; // окончание для фрилансера
        } elseif ($role == 'e') {
            $params = 'utm_source=newsletter4&utm_medium=uvedomlenie&utm_campaign=rabotodatel'; // для работодателя
        } elseif ($role == 'b') {
            $params = 'utm_source=newsletter4&utm_medium=uvedomlenie&utm_campaign=polzovatel'; // для обоих
        } else return '';
        return $firstChar.$params;
    }
    
    
    /**
     * UTM-метки для аналитики
     * 
     * @param type $utm_medium
     * @param type $utm_content
     * @param type $utm_campaign
     * @param type $firstChar
     * @return type
     */
    public static function _addUtmUrlParams($utm_medium, $utm_content, $utm_campaign, $firstChar = '?'){
        return "{$firstChar}utm_source=newsletter4&utm_medium={$utm_medium}&utm_content={$utm_content}&utm_campaign={$utm_campaign}";
    }

}

?>
