<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/smtp.php';
/**
 * Вторая версия класса для отправки сообщений
 * ВНИМАНИЕ!!! БЕТА-ВЕРСИЯ!
 * 
 */
class SMTP2 extends SMTP {
    
    /**
     * Обратный адрес сервиса.
     *
     * @var string
     */
	public $sender = 'Free-lance.ru <no_reply@free-lance.ru>';
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
     * Кодировка отправляемых писем.
	 * Если потребуется изменить, то на свой страх и риск :)
     *
     * @var string
     */
	protected $charset = 'windows-1251';
    /**
     * MIME тип письма. На данный момент поддерживается text/plain и text/html.
     * При необходимости может измениться на multipart/mixed или multipart/related
     * 
     * @var string
     */
    public $contentType = 'text/html';
    /**
     * Хост SMTP сервера.
     *
     * @var string
     */
	protected $_server = 'localhost';
    /**
     * Порт SMTP сервера.
     *
     * @var string
     */
	protected $_port = 25;
    /**
     * Массив с данными о прикрепленных файлах
     * @see self::attach();
     * 
     * @var array
     */
    protected $_attaches = array();
    /**
     * Сокет к SMTP серверу
	 * Один для всех создаваемых объектов.
     *
	 * @var resource
     */
	//private static $socket = NULL;
    /**
     * Количество созданных объектов класса.
     *
	 * @var integer
     */
	//private static $objects = 0;
    /**
     * Лог сообщений между сервером и скриптом для текущего объекта.
	 * Сохраняются только системные сообщения без тела письма.
     *
     * @var string
     */
	public $log = '';
   /**
     * Лог сообщений между сервером и скриптом для всех объектов этого класса.
	 * Сохраняются только системные сообщения без тела письма.
     *
     * @var string
     */
	public static $flog = '';
    
    
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
		if ( --self::$objects <= 0 ) {
			$this->_close();
			// лог
			//file_put_contents('/tmp/smtp.log', $this->log);
            //echo "<pre>" . $this->log . "</pre>";
		}
	}
    

    /**
     * Отсылает письмо одному адресату. Является оберткой для self::_send() и проверяет наличие
     * одного получателя, таким образом с помощью этого метода нельзя делать массовую рассылку.
     * 
     * @return boolean  успех функции
     */
    public function send() {
        if ( preg_match('/^.+\@.+$/', $this->sender) && preg_match('/^.+\@.+$/', $this->recipient) ) {
            return $this->_send();
        }
        return false;
    }

    
    /**
     * Подготавливает очередь к массовой рассылке. Является оберткой для self::_send(). Через нее
     * невозможно послать письмо конкретному одному получателю, т.к. self::$recipient сбрасывается.
     * 
     * @return boolean  успех функции
     */
    public function masssend() {
        if ( preg_match('/^.+\@.+$/', $this->sender) ) {
            $this->recipient = '';
            return $this->_send();
        }
        return false;
    }
    
    
    /**
     * Добавить получателей для массовой рассылки
     * 
     * @param  integer $spamid  id письма для которой добавляем адресатов
     * @return integer          id письма (0 - ошибка) 
     */
    public function bind($spamid) {
        $db = new DB('spam');
        $i  = 0;
        if ( is_array($this->recipient) ) {
            $recipients = array();
            foreach ( $this->recipient as $r ) {
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
                    $db->query("SELECT mail.bind(?, ?a)", $spamid, $recipients);
                    unset($recipients);
                    $recipients = array();
                    $i = 0;
                }
            }
            if ( $i ) {
                $db->query("SELECT mail.bind(?, ?a)", $spamid, $recipients);
                unset($recipients);
            }
        } else if ( is_string($this->recipient) && $this->recipient != '' ) {
            $spamid = $db->query("SELECT mail.bind(?, ?a)", $spamid, array($this->recipient));
        }
        return $spamid;
    }

    
    /**
	 * Проверяет, создано ли в данный момент соединение с SMTP сервером.
	 *
	 * @return   boolean   TRUE если соединение создано; FALSE если нет
	 */
	public function connected() {
		return is_resource(self::$socket);
	}
    
    
    /**
     * Создает уникальное имя для изображений, которые должны отображаться внутри HTML (inline attached).
     * Использование данного метода для генерации уникального имени не обязательно, можно
     * использовать свои имена.
     * 
     * @param  string $prefix  префикс для уникального имени, по желанию
     * @return string          уникальное имя
     */
    public function cid($prefix='') {
        $host   = preg_replace("/^https?\:\/\//", '', $GLOBALS['host']);
        $uniqid = uniqid(($prefix != '')? $prefix . '.': '', true);
        return $uniqid . '@' . $host;
    }

    
    /**
     * Добавляет аттач в письмо
     * 
     * @param mixed $file  файл в виде локального имени или объекта класса CFile
     * @param type  $cid   cid файла, который будет использоваться для внутренней ссылки на него.
     *                     Если cid не указан, то файл воспринимается как простой прикрепленный файл.
     */
    public function attach($file, $cid='') {
        $this->_attaches[] = array(
            'file' => $file,
            'cid'  => $cid
        );
    }

    
    /**
     * Обертка для метода self::_open()
	 * Устанавливает соединение с SMTP сервером, если оно еще не было установлено.
	 * Метод вызывается в self::_send() поэтому вызывать его явно обычно не требуется. Его может быть 
	 * удобно вызывать перед отправкой пачки сообщений, чтобы заранее убедится что соединение установилось.
     *
	 * @return  boolean   TRUE если соединение установлено; FALSE если установить соединение не удалось
     */
    public function connect() {
        return $this->_open();
    }

    
    /**
     * Отправляет сообщение на smtp сервер. Вызывать его вручную, без необходимости, не нужно.
     * Вызовом этого метода занимается консьюмер spam.php. Для отправки почты см. @see self::send(),
     * @see self::masssend(), @see self::bind
     * 
     * @param  string $sender     отправитель
     * @param  string $recipient  получатель
     * @param  string $body       подготовленное к рассылке сообщение, со всеми заголовками
     * @return boolean            успех
     */
    public function mail($sender, $recipient, $body) {
		if ( preg_match('/\<(.+@.+)\>$/', $sender, $o) ) {
            $sender = $o[1];
        } else {
            $sender = $sender;
        }
		if ( preg_match('/\<(.+@.+)\>$/', $recipient, $o) ) {
            $recipient = $o[1];
        } else {
            $recipient = $recipient;
        }
		// если установлена константа SERVER == beta или IS_LOCAL, то почта будет отправлятся только адресатам из $TESTERS_MAIL
		if  ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === true) ) {
			if ( !is_array($GLOBALS['TESTERS_MAIL']) || !in_array($recipient, $GLOBALS['TESTERS_MAIL']) ) {
				$this->sended++;
				return true;
			}
		}
        // подключение и отправка
		if ( !self::$socket && !$this->_open() ) {
            return false;
        }
        //
		if ( $this->_cmd("MAIL FROM: {$sender}") != 250
			|| $this->_cmd("RCPT TO: {$recipient}") != 250
			|| $this->_cmd("DATA") != 354
		) {
			$this->_cmd("RSET");
			return false;
		}
        //
        fwrite(self::$socket, $body);
        // 
		if ($this->_cmd(".") != 250) {
			$this->_cmd("RSET");
			return false;
		}
		$this->sended++;
		return true;
    }
    
    
    /**
	 * Устанавливает соединение с SMTP сервером, если оно еще не было установлено.
     * @see self::connect()
	 *
	 * @return   boolean   TRUE если соединение установлено; FALSE если установить соединение не удалось
	 */
	protected function _open() {
		if ( self::$socket ) {
            return true;
        }
		if ( !(self::$socket = fsockopen($this->_server, $this->_port, $errno, $errstr, 60)) ) {
			return false;
		}
		if ( $this->_cmd(null) != 220 || $this->_cmd("HELO {$this->_server}") != 250 ) {
			fclose(self::$socket);
			self::$socket = null;
			return false;
		}
		return true;
	}
	

    /**
	 * Закрывает соединение с SMTP сервером.
	 * Метод вызывается в деструкторе, при уничтожении последнего созданного объекта класса,
	 * поэтому вызывать его явно не требуется.
	 * Если его вызвать до того, как будет уничтожен последний объект и какой-либо объект в дальнейшем
	 * еще будет отсылать письма, то соединение создастся заново (переподключится).
	 *
	 * @return   boolean   TRUE если соединение закрыто; FALSE если закрыть не удалось
	 */
    public function _close(){
		if ( self::$socket ) {
			$this->_cmd("QUIT");
			fclose(self::$socket);
			self::$socket = null;
		}
		return true;
	}

    
    // временно
    public function close() {
        return $this->_close();
    }
    
	
	/**
	 * Отсылает команду SMTP серверу
	 *
	 * @param   string   команда
	 * @return  integer  код ответа SMTP сервера
	 */
	protected function _cmd($comm) {
		if ( $comm ) {
			fwrite(self::$socket, "{$comm}\r\n", strlen($comm)+2);
			$this->log  .= "{$comm}\r\n";
			self::$flog .= "{$comm}\r\n";
		}
		$line = '';
		$out  = '';
		$c = 0;
		while ( (strpos($out, "\r\n") === FALSE || substr($line, 3, 1) !== ' ') && $c < 100 ) {
			$line = fgets(self::$socket, 1024);
			$out .= $line;
			$c++;
		}
		$this->log  .= $out;
		self::$flog .= $out;
		if (preg_match("/^([0-9]{1,3}) (.+)$/", $out, $o)) {
			return (int) $o[1];
		}
		return 0;
	}
    
    
    /**
     * Полностью родгатавливает тело сообщения и ставит его в очередь на отправку
     * См. также @see self::send(), self::masssend(0
     * 
     * @return boolean  успех
     */
    protected function _send() {
		// работа с получателем, отправителем и темой
		$sender    = $this->_encodeEmail($this->sender);
        $recipient = ($this->recipient == '')? '%%%recipient%%%': $this->_encodeEmail($this->recipient);
		$subject   = $this->_encode(htmlspecialchars_decode($this->subject, ENT_QUOTES));
        // обработка текста письма
        $message = $this->message;
        $message = str_replace(array("\\'", '\\"', "\\\\"), array("'", '"', "\\"), $message);
        $message = preg_replace("'[\r\n]+\.[ \r\n]+'", ".\r\n", $message);
        // разбиваем сообщения на строки примерно в 80 символов
        // иначе почтовик может вставлять пробелы прямо внутри слов
        $len  = strlen($message);
        $prev = '';
        $res  = '';
        for ( $i=0, $j=0; $i<$len; $i++, $j++ ) {
            if ( ($j > 80) && ($message{$i} == ' ') ) {
                $res .= "\r\n";
                $j = 0;
            } else if ( $message{$i} == "\n" ) {
                if ( $prev != "\r" ) {
                    $res .= "\r\n";
                } else {
                    $res .= "\n";
                }
                $j = 0;
            } else {
                $res .= $message{$i};
            }
            $prev = $message{$i};
        }
        $message = $res;
        // определяем есть ли вложенные изобращения и прикрепленные файлы
        $mixed   = false;
        $related = false;
        for ( $i=0; $i<count($this->_attaches); $i++ ) {
            if ( $this->_attaches[$i]['cid'] == '' || $this->contentType == 'text/plain' ) {
                $mixed = true;
            } else {
                $related = true;
            }
        }
        // определяем базовый тип контента
        $baseContentType = $this->contentType;
        if ( $related ) {
            switch ( $this->contentType ) {
                // если есть вложенные изображения в text/plain -> пересылаем как вложения
                case 'text/plain': {
                    $baseContentType = 'multipart/mixed';
                    break;
                }
                // для html формата используем related (связный) формат
                case 'text/html': {
                    $baseContentType = 'multipart/related';
                    break;
                }
                default: {
                    $baseContentType = $this->contentType;
                    break;
                }
            }
        }
        if ( $mixed ) {
            $baseContentType = 'multipart/mixed';
        }
        // состовное или простое письмо
        if ( preg_match('/^multipart\//', $baseContentType) ) {
            $multipart = true;
        } else {
            $multipart = false;
        }
        $alternate = false;
        // строим тело сообщения
        $body  = "MIME-Version: 1.0\r\n";
        $body .= "To: {$recipient}\r\n";
        $body .= "From: {$sender}\r\n";
        $body .= "Subject: {$subject}\r\n";
// Рассылается она долго, а получается прописывается дата на момент рассылки. если закоментить то postfix автоматом будет время рассылки в каждое письмо проставлять
//        $body .= "Date: " . date("r") . "\r\n";
        if ( $multipart ) {
            $boundaries = array();
            $boundary = '------------' . md5(uniqid(time()));
            array_push($boundaries, $boundary);
            $body .= "Content-Type: {$baseContentType};\n boundary=\"{$boundary}\"\r\n\r\n";
            $body .= "This is a multi-part message in MIME format.\r\n";
            if ( $related && $mixed ) {
                $body .= "--{$boundary}\r\n";
                $boundary = '------------' . md5(uniqid(time()));
                array_push($boundaries, $boundary);
                $body .= "Content-Type: multipart/related;\n boundary=\"{$boundary}\"\r\n\r\n";
                $boundary = array_pop($boundaries);
            }
            if ( $this->contentType == 'text/plain' || $alternate ) {
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: text/plain; charset={$this->charset}; format=flowed\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $message;
                $body .= "\r\n\r\n";
            }
            if ( $this->contentType == 'text/html' || $alternate ) {
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: text/html; charset={$this->charset}\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $message;
                $body .= "\r\n\r\n";
            }
            if ( $related ) {
                foreach ( $this->_attaches as $attach ) {
                    if ( $attach['cid'] == '' ) {
                        continue;
                    }
                    $file = &$attach['file'];
                    $cid  = $attach['cid'];
                    if ( $file instanceof CFile ) {
                        if ( !empty($file->size) ) {
                            $fc = @file_get_contents(WDCPREFIX_LOCAL."/{$file->path}{$file->name}");
                            if ( !empty($fc) ) {
                                $type = $file->getContentType();
                                $name = $this->_encode(htmlspecialchars_decode($file->original_name, ENT_QUOTES));
                            }
                        }
                    } else if ( is_string($file) && $file != '' ) {
                        $fc = @file_get_contents($file);
                        if ( !empty($fc) ) {
                            $name = basename($file);
                            $out  = exec("file -i '{$file}'");
                            $type = preg_replace('/^[^:]+:\s*([^\s;]+).*$/', '$1', $out);
                        }
                    }
                    if ( !empty($fc) ) {
                        $body .= "--{$boundary}\r\n";
                        $body .= "Content-Type: {$type};\n name=\"{$name}\"\r\n";
                        $body .= "Content-Transfer-Encoding: base64\r\n";
                        $body .= "Content-ID: <{$cid}>\r\n";
                        $body .= "Content-Disposition: inline;\n filename=\"{$name}\"\r\n\r\n";
                        $body .= chunk_split(base64_encode($fc));
                    }
                }
                if ( $mixed ) {
                    $body .= "--{$boundary}--\r\n\r\n";
                    $boundary = array_pop($boundaries);
                }
            }
            if ( $mixed ) {
                foreach ( $this->_attaches as $attach ) {
                    if ( $attach['cid'] != '' && $this->contentType != 'text/plain' ) {
                        continue;
                    }
                    $file = &$attach['file'];
                    if ( $file instanceof CFile ) {
                        if ( !empty($file->size) ) {
                            $fc = @file_get_contents(WDCPREFIX_LOCAL."/{$file->path}{$file->name}");
                            if ( !empty($fc) ) {
                                $type = $file->getContentType();
                                $name = $this->_encode(htmlspecialchars_decode($file->original_name, ENT_QUOTES));
                            }
                        }
                    } else if ( is_string($file) && $file != '' ) {
                        $fc = @file_get_contents($file);
                        if ( !empty($fc) ) {
                            $name = basename($file);
                            $out  = exec("file -i '{$file}'");
                            $type = preg_replace('/^[^:]+:\s*([^\s;]+).*$/', '$1', $out);
                        }
                    }
                    if ( !empty($fc) ) {
                        $body .= "--{$boundary}\r\n";
                        $body .= "Content-Type: {$type};\n name=\"{$name}\"\r\n";
                        $body .= "Content-Transfer-Encoding: base64\r\n";
                        $body .= "Content-Disposition: attachment;\n filename=\"{$name}\"\r\n\r\n";
                        $body .= chunk_split(base64_encode($fc));
                    }
                }
            }
            $body .= "--{$boundary}--\r\n";
        } else {
            if ( $this->contentType == 'text/html' ) {
                $body .= "Content-Type: text/html; charset={$this->charset}\r\n";
            } else {
                $body .= "Content-Type: text/plain; charset={$this->charset}; format=flowed\r\n";
            }
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $message;
            $body .= "\r\n\r\n";
        }
        // постановка сообщения в очередь
        $db = new DB('spam');
        if ( $this->recipient == '' ) {
            $spamid = $db->val(
                "SELECT mail.send(?, ?, ?, ?, ?, ?a)",
                $this->sender,
                NULL,
                'SMTP2',
                $this->subject,
                $body,
                array()
            );
        } else {
            $spamid = $db->val(
                "SELECT mail.send(?, ?, ?, ?, ?, ?a)",
                $this->sender,
                $this->recipient,
                'SMTP2',
                $this->subject,
                $body,
                array()
            );
        }
        unset($body);
        return $spamid;
    }
    
    
    /**
     * Кодирует текст в BASE64 для нормального прохождения через почтовые сервера не поддерживающие кириллицу
     * 
     * @param  string  $text  текст, который необходимо закодировать
     * @return string         закодированный текст
     */
    protected function _encode($text) {
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
	
    // временно!!!
    public function encodeEmail($text) {
        return $this->_encodeEmail($text);
    }

    /**
     * Подготавливает строку с получателями сообщений, кодирует везде имя получателя в соотвествии с требованиями.
     * @see smail::encode()
     * 
     * @param   string   $in_str   имя получателя вместе с мылом, например "Федя <fedya@mail.ru>".
     * @param   string   $charset  кодировка (та же, что используется в исходном сообщении).
     * @return  string             готовая строка для использования в протоколе.
     */
    protected function _encodeEmail($text) {
        $subj = preg_match_all("'([^<]*)<([^>]*)>[/s,]*'", $text, $matches);
        $out  = array();
        foreach ($matches[1] as $ikey => $sting)
            $out[] = $this->_encode(trim($sting))." <".$matches[2][$ikey].">";
        if (count($out)) {
            $out_str = implode(", ", $out);
        }
        else {
            $out_str = $text;
        }
        unset($out);
        return $out_str;
    }
    
    
}

