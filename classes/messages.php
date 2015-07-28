<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/account.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/QChat.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/template.php';

class messages {

    protected $DB;
    
    /**
     * uid пользователя работающего с личными сообщениями (передается в конструкторе)
     * Пока используется с методами Masssend*, далее будет использоваться везде
     * 
     * @var integer
     */
    public $uid = 0;

    /**
     * Максимальный размер файла в байтmsgsCntах
     *
     * @var integer
     */
    const MAX_FILE_SIZE = 5242880;

    /**
     * Максимальное количество прикрепленных файлов
     *
     * @var integer
     */
    const MAX_FILES = 10;

    /**
     * Максимальная длина личного сообщения
     *
     * @var integer
     */
    const MAX_MSG_LENGTH = 20000;
    
    /**
     * количество автоматических папок личных менеджеров на странице.
     */
    const PM_AUTOFOLDERS_PP = 10;

    /**
     * Кол-во сообщений разрешенных для отправки подряд без капчи в период времени SPAM_CAPTCHA_TIME_WITHOUT
     *
     * @var integer
     */
    const SPAM_CAPTCHA_MSG_COUNT = 10;

    /**
     * Кол-во сообщений разрешенных для отправки подряд без капчи в период времени SPAM_CAPTCHA_TIME_WITHOUT для пользователей, который что-то покупали на сайте
     *
     * @var integer
     */
    const SPAM_CAPTCHA_MSG_COUNT_PAY = 30;

    /**
     * Период времени(в секундах) в который можно отправлять подряд SPAM_CAPTCHA_MSG_COUNT без капчи
     *
     * @var integer
     */
    const SPAM_CAPTCHA_TIME_WITHOUT = 60;

    /**
     * Период времени(в секундах) в течении которого будет показываться капча
     *
     * @var integer
     */
    const SPAM_CAPTCHA_TIME_SHOW = 900;

    /**
     * Префикс для мемкеша счетчика новых сообщений в чате
     * 
     */
    const MEMBUFF_CHAT_PREFIX = 'QChatMsgsCnt';
    
    /**
     * Путь к шаблонам
     */
    const TPL_PATH = "/templates/messages/";
    
    

    
    /**
     * Имя таблицы где указано разрешение связаться к пользователем
     * 
     * @var type 
     */
    const TABLE_ALLOWED = 'messages_allowed';

    
    const CACHE_TAG_IS_ALLOWED = 'Messages_isAllowed_ForUserId%s';
    const KEY_CHECK_IS_ALLOWED = 'Messages_isAllowed__WasCheckForUserId%sTo%s';
    const KEY_CHECK_TAG_IS_ALLOWED = 'Messages_isAllowed__WasCheck';
    
    
    const MESSAGES_NOT_ALLOWED = '
        Возможность отправки личных сообщений работодателям закрыта из-за жалоб на спам, 
        кроме случаев, когда вы являетесь исполнителем в проекте данного работодателя, 
        сотрудничали с ним по заказу на сайте или раньше уже обменивались личными сообщениями.';




    /**
	 * Конструктор класса
	 */
	public function __construct( $uid = 0 ) {
		$this->uid = $uid;
        $this->DB = new DB('plproxy');
	}


    /**
     * Массовая рассылка указанным пользователям
     *
     * @param  string   $message      текст сообщения
     * @param  boolean  $recipients   массив с логинами получаетелей
     * @param  boolean  $mailFunc     имя метода класса pmail для рассылки email
     * @param  array    $attachments  прикрепленные файлы (массив объектов класса CFile)
     *
     * @return integer                0 в случае ошибки, id созданного сообщения в случае успеха
     */
    public function masssendTo($message, $recipients, $mailFunc = '', $attachments = array()) {
        $DB = new DB('plproxy');
        $files = array();
		foreach ( $attachments as $file ) {
			$files[] = $file->id;
		}
        $msgid = $DB->val("SELECT masssend(?, ?, ?a, ?)", $this->uid, $message, $files, $mailFunc);
        if ( $msgid && !empty($recipients) ) {
            $where = $DB->parse("login IN (?l) AND is_banned = B'0' AND substr(subscr::text,8,1) = '1'", $recipients);
            $sql = "SELECT uid FROM users WHERE {$where}";
            $DB->query("SELECT masssend_sql(?, ?, ?)", $msgid, $this->uid, $sql);
        }
        return $msgid;
    }
    
    
    /**
     * Массовая рассылка для всех пользователей
     *
     * @param  string   $message      текст сообщения
     * @param  boolean  $pro          TRUE - только для PRO, FALSE - только для НЕ PRO, NULL - для всех
     * @param  boolean  $mailFunc     имя метода класса pmail для рассылки email
     * @param  array    $attachments  прикрепленные файлы (массив объектов класса CFile)
     *
     * @return integer                0 в случае ошибки, id созданного сообщения в случае успеха
     */
    public function masssendToAll($message, $pro, $mailFunc = '', $attachments = array()) {
        $DB = new DB('plproxy');
        $files = array();
		foreach ( $attachments as $file ) {
			$files[] = $file->id;
		}
        $msgid = $DB->val("SELECT masssend(?, ?, ?a, ?)", $this->uid, $message, $files, $mailFunc);
        if ( $msgid ) {
            $where = $DB->parse("is_banned = B'0' AND substr(subscr::text,8,1) = '1' AND uid <> ?i", $this->uid);
            if ( !is_null($pro) ) {
                $where = $pro? ' AND is_pro = TRUE ': ' AND is_pro = FALSE ';
            }
            $sql = "SELECT uid FROM users WHERE {$where}";
            $DB->query("SELECT masssend_sql(?, ?, ?)", $msgid, $this->uid, $sql);
        }
        return $msgid;
    }
	
	
    /**
     * Массовая рассылка для всех работодателей
     *
     * @param  string   $message      текст сообщения
     * @param  boolean  $pro          TRUE - только для PRO, FALSE - только для НЕ PRO, NULL - для всех
     * @param  boolean  $mailFunc     имя метода класса pmail для рассылки email
     * @param  array    $attachments  прикрепленные файлы (массив объектов класса CFile)
     *
     * @return integer                0 в случае ошибки, id созданного сообщения в случае успеха
     */
    public function masssendToEmployers($message, $pro, $mailFunc = '', $attachments = array()) {
        $DB = new DB('plproxy');
        $files = array();
		foreach ( $attachments as $file ) {
			$files[] = $file->id;
		}
        $msgid = $DB->val("SELECT masssend(?, ?, ?a, ?)", $this->uid, $message, $files, $mailFunc);
        if ( $msgid ) {
            $where = $DB->parse("is_banned = B'0' AND substr(subscr::text,8,1) = '1' AND uid <> ?i", $this->uid);
            if ( !is_null($pro) ) {
                $where = $pro? ' AND is_pro = TRUE ': ' AND is_pro = FALSE ';
            }
            $sql = "SELECT uid FROM employer WHERE {$where}";
            $DB->query("SELECT masssend_sql(?, ?, ?)", $msgid, $this->uid, $sql);
        }
        return $msgid;
    }


    /**
     * Массовая рассылка для всех фрилансеров
     *
     * @param  string   $message      текст сообщения
     * @param  boolean  $pro          TRUE - только для PRO, FALSE - только для НЕ PRO, NULL - для всех
	 * @param  array    $profs        NULL - всем фрилансерам. Или массив с идентификаторами профессий или разделов
	 *                                Должен иметь члены:
	 *                                id - id. профессии или раздела
	 *                                is_group - раздел (true) / профессия (false)
     * @param  boolean  $mailFunc     имя метода класса pmail для рассылки email
     * @param  array    $attachments  прикрепленные файлы (массив объектов класса CFile)
     *
     * @return integer                0 в случае ошибки, id созданного сообщения в случае успеха
     */
    public function masssendToFreelancers($message, $pro, $profs, $mailFunc = '', $attachments = array()) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $dbMaster = new DB('master');
        $dbProxy  = new DB('plproxy');

        $files = array();
        foreach ($attachments as $file) {
            $files[] = $file->id;
        }
        
        $where = $dbMaster->parse("u.is_banned = B'0' AND substr(subscr::text,8,1) = '1' AND uid<>?i", $this->uid);
        
        if ( empty($profs) ) {
			
            if ( !is_null($pro) ) {
                $where .= $dbMaster->parse( $pro? ' AND u.is_pro = TRUE ': ' AND u.is_pro <> TRUE ' );
            }
            
        } else {
            
            $items  = array();
            $groups = array();
            
            foreach ( $profs as $prof ) {
                if ( $prof['is_group'] ) {
                    $groups[] = $prof['id'];
                } else {
                    $items[]  = $prof['id'];
                }
            }
            
            if ( $groups ) {
                $rows = $dbMaster->col("SELECT id FROM professions WHERE prof_group IN (?l)", $groups);
                $items = array_merge($items, $rows);
            }

            if ( empty($items) ) {
                return 0;
            }
            
            $unions = array_unique($items);
            foreach ( $unions as $prof ) {
                $mirrors = professions::GetMirroredProfs($prof);
                $items = array_merge($items, $mirrors);
            }
            $items = array_unique($items);

            $inner = $dbMaster->parse(' LEFT JOIN spec_paid_choise sp ON sp.user_id = u.uid ', $items);
            $wprof = $dbMaster->parse('u.spec IN (?l) OR (sp.prof_id IN (?l) AND sp.paid_to >= NOW())', $items, $items);
            if ( $pro === FALSE ) {
                $where .= $dbMaster->parse(' AND u.is_pro <> TRUE ');
            } else {
                $inner .= $dbMaster->parse(' LEFT JOIN spec_add_choise sa ON sa.user_id = u.uid ', $items);
                $wprof .= $dbMaster->parse(' OR (sa.prof_id IN (?l) AND u.is_pro = TRUE) ', $items);
                $where .= $pro? $dbMaster->parse(' AND u.is_pro = TRUE '): '';
            }
            $where = "{$where} AND ({$wprof})";
            
        }
        
        $sql = $dbMaster->parse("SELECT DISTINCT uid FROM freelancer u {$inner} WHERE {$where}");
            
        $msgid = $dbProxy->val("SELECT masssend(?, ?, ?a, ?)", $this->uid, $message, $files, $mailFunc);
        if ( $msgid ) {
            $dbProxy->query("SELECT masssend_sql(?, ?, ?)", $msgid, $this->uid, $sql);
        }

		return $msgid;
	}
    

	/**
	 * Рассылает сообщение фрилансерам заказанные в массовой рассылке (таблица mass_sending_users)
	 *
	 * @param   integer   $user_id         uid пользователя заказавшего рассылку
	 * @param   integer   $masssending_id  id рассылки
	 * @param   string    $text            текст рассылки
	 * @param   string    $posted_time     дата создания рассылки
         * @param   bool $skip_mail           Если TRUE - не отправлять уведомление о новом сообщении на емайл.
         * @return  bool true - успех, false - провал
	 */
	function Masssending($user_id, $masssending_id, $text, $posted_time, $skip_mail=false) {
		$master  = new DB('master');
		$plproxy = new DB('plproxy');
		$error = '';
		
		$files = $master->col("SELECT file.id FROM mass_sending_files m INNER JOIN file ON m.fid = file.id WHERE mass_sending_id = ? ORDER BY m.pos", $masssending_id);

        $ignors = $plproxy->col("SELECT user_id FROM ignor_me(?)", $user_id);
        array_push($ignors, $user_id);
        
        $sql = $master->parse("
            SELECT 
                m.uid 
            FROM 
                mass_sending_users m 
            INNER JOIN 
                users u ON m.uid = u.uid AND u.is_banned = B'0' 
            WHERE 
                mid = ?i AND m.uid NOT IN (?l)
        ", $masssending_id, $ignors);
        /*$msgid = $plproxy->val("SELECT masssend(?, ?, ?a, ?)", $user_id, $text, $files, ($skip_mail? '': 'SpamFromMasssending'));
        if ( $msgid ) {
            $plproxy->query("SELECT masssend_sql(?, ?, ?)", $msgid, $user_id, $sql);
        }*/
        
        $msgid = $plproxy->val( 'SELECT masssend(?, ?, ?a, ?, ?, ?, ?)', $user_id, $text, $files, $masssending_id, $posted_time, ($skip_mail? '': 'SpamFromMasssending'), $sql );
		
        // TODO: отдельным тикетом
		//$master->query("DELETE FROM mass_sending_users WHERE mid = ?", $masssending_id);
            return empty( $plproxy->error );
	}
	
	/**
	 * Возвращает данные о сообщении массовой рассылки
	 * 
	 * @param  int $message_id ID сообщения
	 * @return array
	 */
	function GetMessage($message_id) {
		$DBProxy  = new DB('plproxy');
        $DBMaster = new DB('master');
        $message = $DBProxy->row("SELECT * FROM messages_mass_userdata(?i)", $message_id);
		if ( !empty($message) ) {
			// !!! OLD !!!
			if ($message['files'] && $message['files']!='{}') {
				$res = $DBMaster->query("SELECT * FROM file WHERE id IN (".substr($message['files'], 1, strlen($message['files'])-2).")");
				while ($row = pg_fetch_assoc($res)) {
					$message['attach'][] = $row;
				}
			}
			// !!! OLD !!!
		} else {
			$message = NULL;
		}
		return $message;
	}
	
	/**
	 * Возвращает получателей массовой рассылки
	 * 
	 * @param  int $user_id  UID получателя. Не используется
	 * @param  int $message_id ID сообщения
	 * @param  int $limit количество получателей
	 * @param  int $offset с какого начинать
	 * @param  bool $only_subscr опционально. по умолчанию TRUE - возвращать только подписавшихся.
	 * @return array
	 */
	function GetZeroMessageUsers($user_id, $message_id, $limit='ALL', $offset=0, $only_subscr=TRUE) {
		$DB = new DB;
		if ($users = $DB->rows("SELECT * FROM messages_zeros_userdata(?i, ?i)".($only_subscr ? " WHERE substr(subscr::text,8,1) = '1'" : '')." LIMIT $limit OFFSET $offset", $user_id, $message_id)) {
			return $users;
		} else {
			return NULL;
		}
	}
	
    /**
     * Регистрирует новое личное сообщение
     *
     * @param integer $user_id              id пользователя-отправителя 
     * @param string $target_login          логин пользователя-получателя
     * @param string $text                  текст сообщения
     * @param array $files                  прикрепленные файлы
     * @param integer $force                разрешение/отказ ответа на письмо (1/0)
     * @param bool $skip_mail               Если TRUE - не отправлять уведомление о новом сообщении на емайл.
	 * @param string $attachedfiles_session   ID сессии загруженных файлов
     *
     * @return mixed                    сообщение об ошибке и флаг ошибки в случае ее возниконовения
     */
    function Add($user_id, $target_login, $text, $files, $force=0, $skip_mail=false, $attachedfiles_session=null, &$message_id=0){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/ignor.php";
        
		$users = new users;
        $login  = $users->GetName($user_id, $err);
        $tar_id = $users->GetUid($err,$target_login);
        if ((ignor::CheckIgnored($tar_id, $user_id) || in_array($target_login, array('admin', 'Anonymous'))) && !$force) {
			$error = "Пользователь запретил отправлять ему сообщения";
        } else {
            if ($files) {
                if (count($files) > messages::MAX_FILES) {
                    $alert[1] = "Вы не можете прикрепить больше " . messages::MAX_FILES . " файлов к сообщению.";
                } else {
                    $max_file_size = messages::MAX_FILE_SIZE;
                    foreach ($files as $file) {
                        $ext = $file->getext();
                        $file->max_size = $max_file_size;
                        $max_file_size -= $file->size;
                        if ( !in_array($ext, $GLOBALS['disallowed_array']) ) {
                            $f_name = $file->MoveUploadedFile($login['login'] . "/contacts");
                            if (!isNulArray($file->error)) {
                                if ($max_file_size < 0) {
                                    $alert[1] = "Вы превысили максимально допустимый размер файлов";
                                } else {
                                    $alert[1] = $GLOBALS['PDA'] ? 'Файл не удовлетворяет условиям загрузки'
                                                        : "Один или несколько файлов не удовлетворяют условиям загрузки.";
                                }
                                break;
                            }
                        } else {
                            $alert[1] = $GLOBALS['PDA'] ? 'Файл не удовлетворяет условиям загрузки'
                                                        : 'Один или несколько файлов имеют неправильный формат.';
                        }

                    }
                }
            }
            
			if (empty($alert) && empty($error)) {
			    $memBuff = new memBuff();
			    
			    // автоматические папки для массовых рассылок личных менеджеров
                global $aPmUserUids;
                
                if ( 
                    in_array($tar_id, $aPmUserUids) // пишут личному менеджеру 
                    || SERVER === 'local' || SERVER === 'beta' || SERVER === 'alpha' // или тестируется
                ) { 
                    $DBproxy = new DB;
                    
                    $nRecId = $DBproxy->val( 'SELECT mess_pm_ustf_add(?i, ?i)', $tar_id, $user_id );
    			    
    			    if ( $nRecId ) {
    			    	$memBuff->delete( 'pmAutoFolder'. $tar_id .'_'. $nRecId );
    			    }
                }
                //---------------------------------------------
			    
				$DB = new DB;
				$f = array();
				if ($files) {
					foreach ($files as $file) {
						$f[] = $file->id;
					}
				}

				require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
				$attachedfiles = new attachedfiles($attachedfiles_session);
				$attachedfiles_files = $attachedfiles->getFiles();
				if($attachedfiles_files) {
					foreach($attachedfiles_files as $attachedfiles_file) {
						$cFile = new CFile($attachedfiles_file['id']);
						$cFile->table = 'file';
                        $ext = $cFile->getext();

                        $tmp_dir = "users/".substr($login['login'], 0, 2)."/".$login['login']."/contacts/";
                        $tmp_name = $cFile->secure_tmpname($tmp_dir, '.'.$ext);
                        $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));

						$cFile->_remoteCopy($tmp_dir.$tmp_name, true);
						$f[] = $cFile->id;
					}
				}
				$attachedfiles->clear();
                
                $aNoMod = array_merge( $GLOBALS['aContactsNoMod'], $GLOBALS['aPmUserUids'] );
                //$bNoMod = hasPermissions('streamnomod', $user_id) || hasPermissions('streamnomod', $tar_id) || is_pro(true, $user_id) || is_pro(true, $tar_id) || in_array($user_id, $aNoMod);
                $bNoMod = true; // #0022344: Убрать из потоков личку
				$message_id = $DB->val("SELECT messages_add(?i, ?i, ?, ?b, ?a, ?b)", $user_id, $tar_id, $text, $skip_mail, $f, $bNoMod);
                
                if ( $user_id % 2 == $tar_id % 2 ) {
                    $memBuff->delete(self::MEMBUFF_CHAT_PREFIX . $tar_id);
                }
                
                if ( $message_id /*&& $bNoMod*/ && !$skip_mail && !QChat::active($tar_id) ) {
                    $mail = new pmail;
                    $mail->NewMessage($user_id, $tar_id, stripslashes($text));
                }

                if ($message_id) {
                    require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/base.php");
                    require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/api.php");
                    require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/mobile.php");
                    externalApi_Mobile::addPushMsg($tar_id, 'message', array('from_user_id'=>get_uid(false), 'text'=>stripslashes($text)));
                }
			}
			
        }
        
		return array($alert,$error);
    }
    
    /**
     * Редактирование личного сообщения
     * 
     * @param  int $from_id UID пользователя-отправителя 
     * @param  int $modified_id UID пользователя изменявшего сообщение
     * @param  int $id ID сообщения
     * @param  string $msg_text текст сообщения
     * @param  array $attachedfiles_file приаттаченные файлы
     * @param  string $modified_reason причина редактирования
     * @return bool true - успех, false - провал
     */
    function Update( $from_id = 0, $modified_id = 0, $id = 0, $msg_text = '', $attachedfiles_file = array(), $modified_reason = '' ) {
        $bRet = false;
        
        if ( $from_id && $id && $msg_text ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
            
            $users = new users;
            $login = $users->GetName( $from_id, $err );
            $files = array();
            
            if ( $login ) {
                if ( $attachedfiles_file ) {
                    foreach( $attachedfiles_file as $file ) {
                        switch ( $file['status'] ) {
                            case 1:
                                // добавляем файл
                                $cFile = new CFile($file['id']);
                                $cFile->table = 'file';
                                $ext = $cFile->getext();

                                $tmp_dir = "users/".substr($login['login'], 0, 2)."/".$login['login']."/contacts/";
                                $tmp_name = $cFile->secure_tmpname($tmp_dir, '.'.$ext);
                                $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));

                                $cFile->_remoteCopy($tmp_dir.$tmp_name, true);
                                $files[] = $cFile->id;
                                break;
                            case 3:
                                // ранее добавленный
                                $files[]  = $file['id'];
                            break;
                            case 4:
                                // удаляем файл
                                $cFile    = new CFile();
                                $cFile->Delete( $file['id'] );
                                break;
                        }
                    }
                }
                
                $DB = new DB;
                $DB->val( "SELECT message_update(?i, ?i, ?, ?a, ?)", $id, $modified_id, $msg_text, $files, $modified_reason );
                
                $bRet = empty( $DB->error );
            }
        }
        
        return $bRet;
    }
    
    /**
     * Получить информацию о конкретном сообщении
     * 
     * @param int $user_id UID пользователя-отправителя 
     * @param int $message_id ID сообщения
     */
    function Get( $user_id, $message_id ) {
        $DB     = new DB;
        $sQuery = 'SELECT * FROM message_get(?i, ?i);';
        $aRows  = $DB->rows( $sQuery, $user_id, $message_id );
        
        self::getMessagesAttaches( $aRows );
        
        return $aRows[0];
    }

    /**
     * Получает список приатаченных файлов к сообщению
     *
     * @return  array               Информация о файлах
     *
     */
    function getAttachedFiles() {
        global $DB;

        $fList = array();
        if($_SESSION['attachedfiles_contacts']['added']) {
            $login = $_SESSION['login'];
            $files = ($_SESSION['attachedfiles_contacts']['added'] ? preg_split("/ /", trim($_SESSION['attachedfiles_contacts']['added'])) : array());
            $dfiles = ($_SESSION['attachedfiles_contacts']['deleted'] ? preg_split("/ /", trim($_SESSION['attachedfiles_contacts']['deleted'])) : array());
            if(count($files)) {
                $sql = "SELECT * FROM file WHERE MD5(id::text || fname) IN (?l);";
                $aFiles = $DB->rows($sql, $files);
                foreach($aFiles as $f) {
                    $cFile = new CFile("users/".substr($login, 0, 2)."/".$login."/contacts/".$f['fname']);
                    if($cFile->id) {
                    	if(in_array(md5($cFile->id.$cFile->name), $dfiles)) {
                    		$is_deleted = 't';
                    	} else {
                    		$is_deleted = 'f';
                    	}
                        array_push($fList, array('file_id'=>$cFile->id, 'name'=>$cFile->name, 'path'=>$cFile->path, 'size'=>$cFile->size, 'ftype'=>$cFile->getext(), 'is_del'=>$is_deleted));
                    }
                }
            }
        }
        return $fList;
    }

	
    /**
     * Получает диалог в личных сообщениях между двумя пользователями
     *
     * @param integer $to_id          id пользователя-получателя
     * @param string $from_login      логин пользователя-отправителя
     * @param integer $num_msgs_from  вывод, начиная с какого-то сообщения
     * @param integer $msg_offset     количество получаемых сообщений (($msg_offset-1) * $GLOBALS['msgspp'])
     *
     * @return mixed                  массив диалога в случае успеха или текст ошибки
     */
    function GetMessages($to_id, $from_login, &$num_msgs_from, $offset=1, $limit=NULL){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";

		$limit = $limit? $limit: $GLOBALS['msgspp'];
        $offset = ((($offset < 1)? 1: $offset) - 1) * $limit;
        $user = new users();
        $user->login = $from_login;
        $from_id = $user->GetUid($error);
        $num_msgs_from = 0;

        if ($from_id) {

			$DB = new DB;
            $offset = intvalPgSql( (string) $offset );
			$rows = $DB->rows("SELECT m.*, array_length(m.files, 1) AS fcount FROM messages_dialog(?i, ?i) m LIMIT ?i OFFSET ?i", 
				$to_id, $from_id, $limit, $offset);
            
			self::getMessagesAttaches( $rows );	
            self::readDialog( $to_id, $from_id );
			$num_msgs_from = $this->num_msgs_from;
			return $rows;
			
		}
    }
    
    /**
     * Пометить переписку прочитанной
     * 
     * @param  int $to_id UID пользователя-получателя
     * @param  int $from_id UID пользователя-отправителя
     * @return bool true - успех, false - провал
     */
    function readDialog( $to_id = 0, $from_id = 0 ) {
        $DB = new DB;
        
        if ( $this->num_msgs_from = $DB->val("SELECT messages_dialog_count(?i, ?i)", $to_id, $from_id) ) {
            $DB->query( 'SELECT messages_dialog_read(?i, ?i)', $to_id, $from_id );

            if ( empty($DB->error) ) {
                $mem = new memBuff();
                $mem->delete("msgsNewSender{$to_id}");
            }
        }
        
        return empty( $DB->error );
    }
    
    /**
     * Возвращает историю диалога между двумя пользователями. В историю не входят непрочитанные сообщения
     * и сообщения массовой рассылки. Можно запросить историю сразу с несколькими пользователями
     * 
     * @param  string  $uid1           uid для кого запрашиваем историю
     * @param  integer $uid2           uid (или массив с uid'ами) с кем нужна история
     * @param  integer $limit          количество сообщений, которые нужно получить (с конца)
     *                                 только целое число. 'ALL' использовать нельзя
     * @param  integer $maxid          получить только сообщения с id < $maxid (0 = все)
     *                                 (актуально только если $uid2 скаляр)
     * @return array                   массив с сообщениями
     */
    function GetHistory($uid1, $uid2, $limit=4, $maxid=0) {
        if ( empty($uid1) || empty($uid2) ) {
            return;
        }
        $where = '';
        $DB    = new DB;
        if ( !is_array($uid2) ) {
            $where = ($maxid > 0)? $DB->parse("WHERE id < ?", $maxid): '';
            $uid2  = array($uid2);
        }
        $sql = "SELECT m.*, array_length(m.files, 1) AS fcount FROM messages_history(?i, ?a, ?i) m {$where}";
        $rows  = $DB->rows($sql, $uid1, $uid2, $limit);
        self::getMessagesAttaches($rows);
        return $rows;
    }
    
    
    /**
     * Получает диалог в личных сообщениях между двумя пользователями для модераторов
     * Сюда не входят сообщения которые не когда не будут проверенны модераторами
     *
     * @param integer $to_id          id пользователя-получателя
     * @param string $from_login      логин пользователя-отправителя
     * @param integer $num_msgs_from  вывод, начиная с какого-то сообщения
     * @param integer $msg_offset     количество получаемых сообщений (($msg_offset-1) * $GLOBALS['msgspp'])
     *
     * @return mixed                  массив диалога в случае успеха или текст ошибки
     */
    function GetMessagesForModers($to_id, $from_login, $offset=1, $limit=NULL){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        $limit         = $limit? $limit: $GLOBALS['msgspp'];
        $offset        = ((($offset < 1)? 1: $offset) - 1) * $limit;
        $user          = new users();
        $user->login   = $from_login;
        $from_id       = $user->GetUid($error);
        $num_msgs_from = 0;
        if ( $from_id ) {
            $DB     = new DB;
            $offset = intvalPgSql( (string) $offset );
            $rows   = $DB->rows("SELECT m.*, array_length(m.files, 1) AS fcount FROM messages_dialog(?i, ?i) m WHERE moderator_status IS NOT NULL LIMIT ?i OFFSET ?i", 
				$to_id, $from_id, $limit, $offset);
            self::getMessagesAttaches( $rows );
            return $rows;
        }
    }
    
    
    /**
     * Прикрепляет вложенные файлы к массиву сообщений
     * 
     * @param  array $rows массив сообщений
     * @param  string $pk индекс в массиве, содержащий ID сообщений
     * @return array
     */
    function getMessagesAttaches( &$rows, $pk = 'id' ) {
        if ( is_array($rows) && count($rows) ) {
            $DB = new DB;
            
            $files = '';
			$fids  = array();
			for ($i=0; $i<count($rows); $i++) {
				$f = $DB->array_to_php($rows[$i]['files']);
				for ($j=0; $j<count($f); $j++) {
				    if($f[$j]) {
					    $fids[$f[$j]] = $rows[$i][$pk];
    					$files .= $f[$j].',';
					}
				}
				$rows[$i]['files'] = array();
			}
			
			// !!! OLD !!!
			if ($files) {
				$res = pg_query(DBConnect(), "SELECT * FROM file LEFT JOIN mass_sending_files AS msf ON msf.fid = file.id WHERE id IN (".substr($files, 0, strlen($files)-1).") ORDER BY msf.pos, id");
				while ($row = pg_fetch_assoc($res)) {
					for ($i=0; $i<count($rows); $i++) {
						if ($rows[$i][$pk] == $fids[$row['id']]) {
							if (!is_array($rows[$i]['files'])) $rows[$i]['files'] = array();
							$rows[$i]['files'][] = $row;
							break;
						}
					}
				}
			}
			// !!! OLD !!!
        }
    }


    /**
     * Пометить сообщение удаленным
     * 
     * @param  int $msg_id 
     * @param  int $del_uid 
     * @return bool true - успех, false - провал
     */
    function deleteMessage( $msg_id = 0, $del_uid = 0 ) {
        $DB = new DB;
        
        $DB->query( 'SELECT message_delete(?i, ?i)', $msg_id, $del_uid );
        
        return empty( $DB->error );
    }
    
    /**
     * Получает контакты пользователей для которых есть заметки
     *
     * @param integer  $uid     id пользователя
     * @param string   $search  подстрока для поиска пользователя
     *
     * @return mixed            массив контактов
     */
    function GetContactsWithNote($uid, $search=''){
        $DB = new DB;
		return $DB->rows("SELECT * FROM notes_users_search(?i, ?)", $uid, $search);
    }
	
    
	/**
     * Получает контакты пользователей
     *
     * @param  integer  $uid     id пользователя
     * @param  string   $folder  папка, для которой получаем контакты
     * @param  string   $search  подстрока для поиска в логине пользователя
     *
     * @return array             массив контактов
     */
    public function GetContacts($uid, $folder=0, $search=NULL, $limit = 'ALL', $offset = 0, &$count = -1) {
		$rows = array();
		if ($search) {
			if ($folder > 0) {
                $func = $this->DB->parse("messages_search_folder(?i, ?i, ?)", $uid, $folder, $search);
			} else if ($folder == 0) {
                $func = $this->DB->parse("messages_search(?i, ?)", $uid, $search);
			} else if ($folder == -1) {
                $func = $this->DB->parse("messages_search_team(?i, ?)", $uid, $search);
			} else if ($folder == -2) {
                $func = $this->DB->parse("messages_search_ignor(?i, ?)", $uid, $search);
			} else if ($folder == -3) {
                $func = $this->DB->parse("messages_search_del(?i, ?)", $uid, $search);
			} else if ($folder == -4) {
                $func = $this->DB->parse("messages_search_notes(?i, ?)", $uid, $search);
			} else if ($folder == -6) {
                $func = $this->DB->parse("messages_search_mass(?i, ?)", $uid, $search);
			} else if ($folder == -7) {
                $func = $this->DB->parse("messages_search_unread(?i, ?)", $uid, $search);
			}
		} else {
			if ($folder > 0) {
                $func = $this->DB->parse("messages_contacts_folder(?i, ?i)", $uid, $folder);
			} else if ($folder == 0) {
                $func = $this->DB->parse("messages_contacts(?i)", $uid);
			} else if ($folder == -1) {
                $func = $this->DB->parse("messages_contacts_team(?i)", $uid);
			} else if ($folder == -2) {
                $func = $this->DB->parse("messages_contacts_ignor(?i)", $uid);
			} else if ($folder == -3) {
                $func = $this->DB->parse("messages_contacts_del(?i)", $uid);
			} else if ($folder == -4) {
                $func = $this->DB->parse("messages_contacts_notes(?i)", $uid);
			} else if ($folder == -6) {
                $func = $this->DB->parse("messages_contacts_mass(?i)", $uid);
			} else if ($folder == -7) {
                $func = $this->DB->parse("messages_contacts_unread(?i)", $uid);
			}
		}

        // Такой тупняк, но работает нормально -- только один проход по выборке. В идеале, нужно лимит перетащить в функцию,
        // кол-во контактов держать отдельно.
        $sql = "
          WITH w_contacts as (SELECT * FROM {$func})
          SELECT *, (SELECT COUNT(1) as count FROM w_contacts) as __count FROM w_contacts LIMIT {$limit} OFFSET {$offset}
        ";
        $rows = $this->DB->rows($sql);
        if($count !== -1) {
            $count = $rows[0]['__count'];
        }
         
		return $rows;
	}
    
    /**
     * возвращает массив с данными об отправителе последнего сообщения
     * 
     * @param int $uid - id пользователя (получателя сообщения)
     */
    public function GetLastMessageContact ($uid) {
        $user = $this->DB->row("SELECT * FROM messages_one_new(?i)", $uid);
        return $user;
    }
    
	/**
     * Список папок пользователя
     *
     * @param  integer  $uid     id пользователя
     *
     * @return array             массив контактов (индекс: uid собеседника)
     */	
	function GetUsersInFolders($uid) {
		$res = array();
		$rows = $this->DB->rows("SELECT * FROM messages_folders_users(?i)", $uid);
		foreach ($rows as $row) {
			$res[$row['to_id']][] = $row['folder'];
		}
		return $res;
	}
	

    /**
     * Возвращает папки, в которых содержится данный контакт
     *
     * @param integer $to_id          id пользователя-владельца контакта (со стороны которого рассматриваем контакт)
     * @param integer $from_id        id пользователя-контакта
     *
     * @return array                  массив папок
     */
    function GetContactFolders($uid1, $uid2, &$error) {
        $DB = new DB;
		$out = array();
		$res = $DB->query("SELECT * FROM messages_folders_user(?i, ?i)", $uid1, $uid2);
		while ($row = pg_fetch_assoc($res)) {
			$out[$row['to_id']][] = $row['folder'];
		}
		return $out;

    }
	
    
	/**
     * Удаляет выбранные контакты (делает их невидимыми для юзера-владельца)
     *
     * @param integer $user_id        id пользователя-владельца контакта
     * @param array $selected         id пользователей-контактов, участников диалогов
     *
     * @return string                 текст ошибки, в случае неуспеха
     */
    function DeleteFromUsers($from_id, $to_id){
		$DB = new DB;
		$DB->query("SELECT messages_dialog_delete(?i, ?i)", $from_id, $to_id);
		return '';
    }
	
	
    /**
     * Восстанавливает удаленный контакт
     *
     * @param integer $user_id        id пользователя-отправителя
     * @param array $selected         id пользователей, которых трубуется восстановить
     *
     * @return string                 текст ошибки, в случае неуспеха
     */
    function RestoreFromUsers($from_id, $to_id){
        $DB = new DB;
		$DB->query("SELECT messages_dialog_restore(?i, ?i)", $from_id, $to_id);
		return '';
    }
	
	
    /**
     * Получает количество новых личных сообщений
     *
     * @param integer $user_id        id пользователя-получателя
     * @param boolean $nocache        не читать из кеша, всегда делать запрос к бд
     *
     * @return integer                  количество новых личных сообщений или NULL в случае ошибки
     */
	public function GetNewMsgCount($uid, $nocache=FALSE) {
		$DB = new DB;
return (int)$DB->val("SELECT messages_newmsg_count(?)", $uid); // без согласования не изменять. Пока не кэшируем.

		$mem = new memBuff();
        if ( $nocache ) {
            $count = FALSE;
        } else {
            $count = $mem->get("msgsCnt{$uid}");
        }
        if ($count === FALSE) {
			$DB = new DB;
			$count = (int) $DB->val("SELECT messages_newmsg_count(?)", $uid);
			$mem->set("msgsCnt{$uid}", $count, 1800, 'msgsCnt');
		}
		return $count;
	}
	

    /**
     * Получает количество новых личных сообщений для быстрочата
     *
     * @param integer $user_id        id пользователя-получателя
     * @param boolean $nocache        не читать из кеша, всегда делать запрос к бд
     *
     * @return integer                  количество новых личных сообщений или NULL в случае ошибки
     */
	public function ChatNewMsgCount($uid, $nocache=FALSE) {
		$DB = new DB;
		$mem = new memBuff();
        if ( $nocache ) {
            $count = FALSE;
        } else {
            $count = $mem->get(self::MEMBUFF_CHAT_PREFIX . $uid);
        }
        if ($count === FALSE) {
			$DB = new DB;
			$count = (int) $DB->val("SELECT chat_newmsgs_count(?)", $uid);
			$mem->set(self::MEMBUFF_CHAT_PREFIX . $uid, $count, 1800, self::MEMBUFF_CHAT_PREFIX . $uid);
		}
		return $count;
	}
    
    
    /**
     * Получает все непрочитанные сообщения.
     * @see externalApi_Freetray
     *
     * @param integer $uid   ид. пользователя.
     * @return array    массив сообщений.
     */
    function getNewMessages($uid) {
        $DB = new DB;
        $rows = $DB->rows("SELECT * FROM messages_moder_newmsg(?i)", $uid);

        return count($rows) ? $rows : null;
    }
    
    /**
     * Возвращает все сообщения пользователя (входящие и исходящие), которые были добавлены, 
     * изменены или помечены удаленными после определенной даты.
     * (!) Массовые рассылки только входящие.
     * 
     * @param  int $uid UID пользователя
     * @param  string $time время, после которого нужно выбирать сообщения
     * @return array массив сообщений
     */
    function getMessagesAllSinceDate( $uid = 0, $time = '' ) {
        $DB = new DB;
        return $DB->rows("SELECT * FROM messages_get_all_since_date(?i, ?)", $uid, $time);
    }
    
    /**
     * Получает все непрочитанные сообщения для быстрочата
     *
     * @param integer $uid   ид. пользователя.
     * @param boolean $read  нужно ли омечать прочитанными эти сообщения
     * @return array    массив сообщений.
     */
    function ChatNewMessages($uid, $read=false) {
        $DB = new DB;
        $rows = $DB->rows("SELECT * FROM chat_newmsgs(?) ORDER BY post_time", $uid);
        if ( $read ) {
            $ids = array();
            foreach ( $rows as $row ) {
                if ( !in_array($row['uid'], $ids) ) {
                    $ids[] = $row['uid'];
                    $DB->query("SELECT chat_dialog_read(?i, ?i)", $uid, $row['uid']);
                }
            }
            $memBuff = new memBuff();
            $memBuff->delete(self::MEMBUFF_CHAT_PREFIX . $uid);
        }
        self::getMessagesAttaches( $rows );
        return $rows;
    }
    
    /**
     * получает данные об отправителе
     */
    function getMessageAuthorByUid($uid) {
        global $DB;
        $sql = "SELECT us.login, us.uname, us.usurname
                    FROM users us
                    WHERE us.uid = (?i)";
        $row = $DB->row($sql, $uid);
        return count($row) ? $row : null;
    }


    /**
     * Регистрирует сообщение-предупреждение о некорректном поведении в блогах
     *
     * @param integer $login          логин пользователя-получателя
     * @param integer $msgid          id сообщения
     * @param integer $thid           id ветки в блогах
     *
     * @return                        @see messages::Add()

     */
    function SendWarn ($login,$msgid=0,$thid=0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
        $f_user = new users();
        $f_user->GetUser($login);
        $msg=new blogs();
        if ($thid) {
            $w_msg=$msg->GetThreadMsgInfo($thid,$error,$perm);
            //print_r($w_msg);
           // exit;
        }
        else {
            $w_msg=$msg->GetMsgInfo($msgid,$error,$perm);
        }
        $message = "
$f_user->uname $f_user->usurname!

Модераторы нашего ресурса нашли ваш комментарий некорректным:

\"$f_user->uname $f_user->usurname. [$login] ".date("[d.m.Y | H:i]",strtotimeEx($w_msg["post_time"]))."
".reformat($w_msg["title"])."
".reformat($w_msg["msgtext"])."
\"

Мы призываем вас впредь не делать подобных комментариев, иначе модераторы лишат ваш аккаунт доступа к сайту.

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$login,$message,'',1);
    }



    /**
     * Регистрирует сообщение-предупреждение об удалении коммента в блогах
     *
     * @param integer $msgid          id сообщения
     * @param array $w_msg            Сообщение
     *
     * @return                        @see messages::Add()
     */
    function SendMsgDelWarn ($msgid, $w_msg) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");

        $f_user = new users();
        $f_user->GetUserByUID($w_msg['fromuser_id']);

        $message = "
Здравствуйте, $f_user->uname $f_user->usurname

Модераторы нашего ресурса удалили ваш комментарий:

{$GLOBALS['host']}/blogs/view.php?tr={$w_msg['thread_id']}&openlevel={$msgid}#o{$msgid}
\"
$f_user->uname $f_user->usurname. [$f_user->login] ".date("[d.m.Y | H:i]",strtotimeEx($w_msg["post_time"]))."
".reformat($w_msg["title"])."
".reformat($w_msg["msgtext"])."
\"
Мы призываем вас впредь не нарушать правила портала, иначе модераторы лишат ваш аккаунт доступа к сайту.

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru.";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Регистрирует сообщение-предупреждение об удалении стандартного комментария.
     * @see TComments::deleteComment() (classes/comments/Comments.php)
     *
     * @param array $w_msg сообщение
     *
     * @return @see messages::Add()
     */
    function sendCommentDeleteWarn( $w_msg ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

        $f_user = new users();
        $f_user->GetUserByUID($w_msg['user_id']);

        $message = "
Здравствуйте, $f_user->uname $f_user->usurname

Модераторы нашего ресурса удалили ваш комментарий:

{$w_msg['link']}

$f_user->uname $f_user->usurname. [$f_user->login] ".date("[d.m.Y | H:i]",strtotimeEx($w_msg["post_time"]))."
".reformat($w_msg["msg"])."


Мы призываем вас впредь не нарушать правила портала, иначе модераторы лишат ваш аккаунт доступа к сайту.

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru.";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Сообщение автору комментария в сообществе об удалении комментария
     * @param type $w_msg
     */
    function sendCommuneCommentDeleteWarn($w_msg) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

        $user_data = commune::GetUserCommuneRel($w_msg['commune_id'], get_uid(0));
        
        $f_user = new users();
        $f_user->GetUserByUID($w_msg['user_id']);
        if ($user_data['is_author']) {
            $deleter = 'создателем сообщества';
        } elseif ($user_data['is_moderator']) {
            $deleter = 'модератором сообщества';
        } else {
            $deleter = 'модератором сайта';
        }
        if (!$user_data['is_author'] && !$user_data['is_moderator']) {
            $attention = "Мы призываем вас впредь не нарушать правила портала, иначе модераторы лишат ваш аккаунт доступа к сайту.
    
";
        }
        
        $message = "
Здравствуйте, $f_user->uname $f_user->usurname

Ваш комментарий {$w_msg['link']} от " . date("d.m.Y", strtotime($w_msg["post_time"])) . " был удален $deleter.
    
" . $attention .
"Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Команда Free-lance.ru.";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Сообщение автору комментария в сообществе об изменении комментария
     * @param type $w_msg
     */
    function sendCommuneCommentEditedWarn($comment) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

        $user_data = commune::GetUserCommuneRel($comment['resource_id'], get_uid(0));
        
        $f_user = new users();
        $f_user->GetUserByUID($comment['author']);
        if ($user_data['is_author']) {
            $deleter = 'создателем сообщества';
        } elseif ($user_data['is_moderator']) {
            $deleter = 'модератором сообщества';
        } else {
            $deleter = 'модератором сайта';
        }
        if (!$user_data['is_author'] && !$user_data['is_moderator']) {
            $attention = "Мы призываем вас впредь не нарушать правила портала, иначе модераторы лишат ваш аккаунт доступа к сайту.
    
";
        }
        
        $message = "
Здравствуйте, $f_user->uname $f_user->usurname

Ваш комментарий {$comment['link']} от " . date("d.m.Y", strtotime($comment["created_time"])) . " был отредактирован $deleter.
    
" . $attention .
"Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Команда Free-lance.ru.";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }


    /**
     * Регистрирует сообщение-предупреждение о некорректном проекте
     *
     * @param integer $login          логин пользователя-получателя
     * @param integer $prjid          id проекта
     *
     * @return                        @see messages::Add()
     */
    function SendProjectWarn ($login, $prjid=0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
        $f_user = new users();
        $f_user->GetUser($login);
        $obj_project = new projects();
        $project = $obj_project->GetPrjCust($prjid);
        $message = "
$f_user->uname $f_user->usurname!

Модераторы нашего ресурса нашли ваш проект некорректным:

\"$f_user->uname $f_user->usurname. [$login] ".date("[d.m.Y | H:i]",strtotimeEx($project["post_date"]))."
".reformat($project["name"])."
".reformat($project["descr"])."
\"
Мы призываем вас впредь не публиковать подобных проектов, иначе модераторы лишат ваш аккаунт доступа к сайту.

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$login,$message,'',1);
    }

    
    /**
     * Сообщение о блокировке треда в блогах
     *
     * @param integer  $thread_id   id треда
     * @param string   $reason      причина
     *
     * @return                      @see messages::Add()
     */    
    function SendBlockedThread ($thread_id=0, $reason) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $msg=new blogs();
        $w_msg=$msg->GetThreadMsgInfo($thread_id,$error,$perm);
        $f_user = new users();
        $f_user->GetUserByUID($w_msg['fromuser_id']);
        $message = "
$f_user->uname $f_user->usurname!

Модераторы нашего ресурса нашли ваш блог".((trim($w_msg["title"])!="")?" &laquo;".($w_msg["title"])."&raquo;":"")." от ".date("d.m.Y",strtotimeEx($w_msg["post_time"]))." некорректным:

Причина: ".($reason)."

Мы призываем вас впредь не создавать подобных блогов, иначе модераторы лишат ваш аккаунт доступа к сайту.

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }

    /**
     * Сообщение о блокировке топика в сообществах
     * 
     * @param array $topic массив информации о топике @see commune::GetTopMessageByAnyOther
     * @param string $reason опционально. причина блокировки
     */
    function SendBlockedCommuneTheme( $topic = array(), $reason = '' ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
        
        //$topic = commune::GetTopMessageByAnyOther( $msg_id, null, commune::MOD_ADMIN );
        
        if ( $topic ) {
            $message = "
{$topic['user_uname']} {$topic['user_usurname']}!";
            
            if ( $topic['is_blocked_c'] == 't' ) {
            	// блокировали админы сообщества
                $message .= '
                
Модераторы сообщества &laquo;'.$topic['commune_name'].'&raquo; нашли ваше сообщение'.((trim($topic["title"])!="")?" &laquo;".($topic["title"])."&raquo;":"")." от ".date("d.m.Y",strtotimeEx($topic["created_time"])).' некорректным.

Мы призываем вас впредь не создавать подобных сообщений, иначе модераторы лишат ваш аккаунт доступа к сообществу.';
            }
            else {
                // блокировали админы сайта Free-lance.ru
            	$message .= '

Модераторы нашего ресурса нашли ваше сообщение'.((trim($topic["title"])!="")?" &laquo;".($topic["title"])." &raquo;":"")." от ".date("d.m.Y",strtotimeEx($topic["created_time"])).' в сообществе &laquo;'.$topic['commune_name'].'&raquo; некорректным:

Причина: '. $reason.'

Мы призываем вас впредь не создавать подобных сообщений, иначе модераторы лишат ваш аккаунт доступа к сайту.';
            }
            
            $message .= '

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Команда Free-lance.ru.';
            
        	messages::Add( users::GetUid($err, 'admin'), $topic['user_login'], $message, '', 1 );
        }
        
    }
    
    /**
     * Сообщение об удалении топика в сообществах
     * 
     * @param array $topic массив информации о топике @see commune::GetTopMessageByAnyOther
     * @param string $deleter кто удаляет 'admin' - админ сообщества, 'moder' - модератор
     */
    function SendDeletedCommuneTheme( $topic = array(), $deleter = 'admin' ) {
        if ( $topic ) {
            $whoDelete = $deleter === 'admin' ? 'создателем сообщества' : ($deleter === 'moder' ? 'модератором сообщества' : 'модератором');
            
            $message = 'Здравствуйте, '. $topic['user_uname'] . ' ' . $topic['user_usurname'] . '
            
Ваше сообщение '. ( trim($topic["title"]) ? '&laquo;' . ($topic["title"]) . '&raquo;' : '' ) . ' от ' . date( 'd.m.Y', strtotimeEx($topic['created_time']) ) .' в сообществе &laquo;'.$topic['commune_name'].'&raquo; было удалено ' . $whoDelete . '.
' . ($deleter === 'site-moder' ? 'Рекомендуем не нарушать <a href="https://feedback.fl.ru/article/details/id/161">правила сайта</a>, в противном случае мы будем вынуждены заблокировать ваш профиль.
    ' : '') . '

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться. 

Команда Free-lance.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $topic['user_login'], $message, '', 1 );
        }
    }
    
    
    
    
    
    
    /**
     * Сообщение о блокировки типовой услуги
     * 
     * @param array $service
     * @param string $reason
     * @return mix
     */
    function SendBlockedTServices ($service = array(), $reason)
    {
        if(!count($service)) return false;
        
        $sName   = $service["title"] ? ' &laquo;'. $service["title"] .'&raquo;' : '';
        $sUser   = $service["uname"] . ' ' . $service["usurname"];

        $message = "
$sUser!

Сожалеем, но модераторы сайта временно скрыли вашу типовую услугу$sName

Рекомендация: ".($reason)."


Пожалуйста, измените содержимое услуги в соответствии с рекомендацией. После этого вы можете вновь опубликовать услугу и начать ее продажи.

Это сообщение было выслано автоматически и не требует ответа.

Желаем выгодных заказов! Команда Free-lance.ru.";   
                            
        return messages::Add(users::GetUid($err,"admin"), $service['login'], $message,'',1);
    }






    /**
     * Сообщение о блокировке проекта
     *
     * @param integer  $project_id   id проекта
     * @param string   $reason       причина
     *
     * @return                       @see messages::Add()
     */    
    function SendBlockedProject ($project_id=0, $reason) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $pr=new projects();
        $prj = $pr->GetPrjCust($project_id);
        $prj_url = getFriendlyURL('project', $project_id);
        $f_user = new users();
        $f_user->GetUserByUID($prj['user_id']);
        $name = $f_user->uname || $f_user->usurname ? trim($f_user->uname . ' ' . $f_user->usurname) : $f_user->login;
        $message = "
Здравствуйте, $name!

Благодарим Вас за то, что воспользовались сайтом FL.ru для поиска исполнителя. 
К сожалению, ваш проект «<a href='$prj_url'>{$prj["name"]}</a>» был заблокирован. 
$reason

<a href='http://feedback.fl.ru/'>Обратиться в Службу поддержки</a> (если проект заблокирован ошибочно)
---
С уважением, команда FL.ru";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Сообщение о разблокировке проекта
     *
     * @param integer  $project_id   id проекта
     * @return                       @see messages::Add()
     */    
    function SendUnBlockedProject ($project_id=0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $pr=new projects();
        $prj = $pr->GetPrjCust($project_id);
        $f_user = new users();
        $f_user->GetUserByUID($prj['user_id']);
        
        $message = Template::render(
            $_SERVER['DOCUMENT_ROOT'] . self::TPL_PATH . 'send_unblocked_project.tpl.php', 
            array(
                'name'  => $f_user->uname,
                'surname' => $f_user->usurname,
                'project_name' => $prj["name"]
            )
        );

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Сообщение о блокировке предложения по проекту
     *
     * @param  integer $offer_id ID предложения
     * @param  integer $user_id UID пользователя
     * @param  integer $project_id id проекта
     * @param  string $reason причина
     * @return @see messages::Add()
     */    
    function SendBlockedProjectOffer( $offer_id = 0, $user_id = 0, $project_id = 0, $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $pr     = new projects();
        $prj    = $pr->GetPrjCust( $project_id );
        $f_user = new users();
        $f_user->GetUserByUID( $user_id );
        
        $message = "
$f_user->uname $f_user->usurname!

Модераторы нашего ресурса нашли ваше предложени по проекту &laquo;".($prj["name"])."&raquo; некорректным:

Причина: ".($reason)."

Мы призываем вас впредь не публиковать подобных предложений, иначе модераторы лишат ваш аккаунт доступа к сайту.

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Сообщение о блокировке проекта
     * 
     * @param  integer $portfolio_id ID работы в портфолио
     * @param  string $reason причина
     * @return @see messages::Add()
     */    
    function SendBlockedPortfolio( $portfolio_id = 0, $reason = '' ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/portfolio.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $portfolio = portfolio::GetPrj( $portfolio_id );
        $f_user    = new users();
        $f_user->GetUserByUID( $portfolio['user_id'] );
        
        $sName   = $portfolio["name"] ? ' &laquo;'. $portfolio["name"] .'&raquo;' : '';
        $link    = getAbsUrl( '/users/'. $f_user->login .'/viewproj.php?prjid='. $portfolio['id'] );
        $message = "
$f_user->uname $f_user->usurname!

Модераторы нашего ресурса нашли вашу работу$sName в портфолио некорректной:

Причина: ".($reason)."

$link

Мы призываем вас впредь не публиковать подобных проектов, иначе модераторы лишат ваш аккаунт доступа к сайту.

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Сообщение о блокировке комментария к предложению по проекту
     * 
     * @param  integer $dialogue_id ID сообщениея
     * @param  string $reason причина
     * @return @see messages::Add()
     */    
    function SendBlockedDialogue( $dialogue_id = 0, $reason = '' ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers_dialogue.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $dialogue    = projects_offers_dialogue::getDialogueMessageById( $dialogue_id );
        $f_user    = new users();
        $f_user->GetUserByUID( $dialogue['user_id'] );
        
        $sName   = $dialogue["project_name"] ? ' &laquo;'. $dialogue["project_name"] .'&raquo;' : '';
        $link    = getAbsUrl( getFriendlyURL('project', $dialogue['project_id']) );
        $message = "
$f_user->uname $f_user->usurname!

Модераторы нашего ресурса нашли ваш комментарий к предложеню по проекту$sName некорректным:

Причина: ".($reason)."

$link

Мы призываем вас впредь не публиковать подобных комментариев, иначе модераторы лишат ваш аккаунт доступа к сайту.

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Сообщение о выдаче предупреждения
     *
     * @param integer  $uid          кому
     * @param string   $reason       причина
     * @param string   $link         где
     *
     * @return                       @see messages::Add()
     */    
    function SendUserWarn ($uid=0, $reason, $link) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $f_user = new users();
        $f_user->GetUserByUID($uid);
        
        $rules = WDCPREFIX."/about/documents/appendix_2_regulations.pdf";
        
        $message = "
Здравствуйте, $f_user->uname $f_user->usurname!

".strip_tags($reason, "<BR>")."

Пожалуйста, впредь соблюдайте <a href='".$rules."'>Правила сайта</a>.

---
С уважением, команда FL.ru";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
	
    /**
     * Сообщение о получении жалобы
     *
     * @param integer  $uid          кому
     * @param integer  $project_url  ИД проекта
     * @param integer  $project_name Название проекта
     * @param string   $text         Текст жалобы
     * @param string   $link         Ссылка на тему в userEcho
     *
     * @return                       @see messages::Add()
     */    
    public static function sendProjectComplain($uid=0, $project_url, $project_name, $text, $link)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $f_user = new users();
        $f_user->GetUserByUID($uid);
        
        $message = "
Здравствуйте, $f_user->uname $f_user->usurname!
Мы получили от вас жалобу на проект <a href='".$project_url."'>{$project_name}</a> с комментарием: 
".strip_tags($text)."

По жалобе сформирована заявка {$link}
Пожалуйста, ведите дальнейший диалог по жалобе в сформированной заявке. 

---
С уважением, команда FL.ru";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Отправляет уведомления об удалении сообщения в личке ("Мои контакты").
     * 
     * @param  int $from_uid UID отправителя
     * @param  int $to_uid UID получателя
     * @param  string $msg Текст сообщения
     * @return bool true - успех, false - провал
     */
    function messageDeletedNotification( $from_uid = 0, $to_uid = 0, $msg = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $to = new users;
		$to->GetUserByUID( $to_uid );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $message = 'Здравствуйте, '. $from->uname .' '. $from->usurname .'

Модераторы нашего ресурса удалили ваше сообщение в разделе &laquo;Обсудить проект&raquo;

'. $to->uname .' '. $to->usurname .' ['. $to->login .']
'. $msg .'

Мы призываем вас впредь не нарушать правила портала, иначе модераторы лишат ваш аккаунт доступа к сайту. 

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться. 

Надеемся на понимание, Команда Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * Отправляет уведомления об изменении админом сообщения в личке ("Мои контакты").
     * 
     * @param  int $from_uid UID отправителя
     * @param  int $to_uid UID получателя
     * @param  string $msg Текст сообщения
     * @param  string $reason причина редактирования
     * @return bool true - успех, false - провал
     */
    function messageModifiedNotification( $from_uid = 0, $to_uid = 0, $msg = '', $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $to = new users;
		$to->GetUserByUID( $to_uid );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $sFeedback = "<a href=\"{$GLOBALS['host']}/about/feedback/\" target=\"_blank\">службу поддержки</a>";        
        $message   = 'Здравствуйте, '. $from->uname .' '. $from->usurname .'

Модераторы нашего ресурса отредактировали ваше сообщение в разделе &laquo;Обсудить проект&raquo;

'. $to->uname .' '. $to->usurname .' ['. $to->login .']
'. $msg . $sRason . '

Вы можете обратиться в '. $sFeedback .'.

Надеемся на понимание, Команда Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * Отправляет уведомления об изменении админом сообщения в личке ("Мои контакты").
     * 
     * @param  int $from_uid UID отправителя
     * @param  string $ucolumn название поля
     * @param  string $utable название редактируемой таблицы
     * @param  string $reason причина редактирования
     * @return bool true - успех, false - провал
     */
    function profileModifiedNotification( $from_uid = 0, $ucolumn = '', $utable = '', $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        switch ( $ucolumn ) {
            case 'uname': $sColumn = 'Имя'; break;
            case 'usurname': $sColumn = 'Фамилия'; break;
            case 'pname': $sColumn = 'Заголовок страницы'; break;
            case 'spec_text': $sColumn = 'Уточнения к услугам в портфолио'; break;
            case 'resume_file': $sColumn = 'Файл резюме'; break;
            case 'resume':
                if ( $utable == 'freelancer' ) {
                    $sColumn = 'Текст резюме';
                }
                else {
                    $sColumn = 'Дополнительная информация';
                }
                break;
            case 'konk': $sColumn = 'Участие в конкурсах и награды'; break;
            case 'company': $sColumn = 'О компании'; break;
            case 'status_text': $sColumn = 'Статус'; break;
            case 'compname': $sColumn = 'Компания'; break;
            default: $sColumn = ''; break;
        }
        
        $to = new users;
		$to->GetUserByUID( $to_uid );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = 'Здравствуйте, '. $from->uname .' '. $from->usurname .'

Модераторы нашего ресурса отредактировали поле'. ($sColumn ? ' &laquo;'.$sColumn.'&raquo;' : '') .' в вашем профиле.'. $sRason . '

Вы можете обратиться в '. $sFeedback .'.

Надеемся на понимание, Команда Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * Сообщение об изменении работы в портфолио
     * 
     * @param  array $portfolio работа в портфолио
     * @param  object $f_user работа в портфолио
     * @param  string $reason причина
     * @return @see messages::Add()
     */    
    function portfolioModifiedNotification( $portfolio = 0, $f_user = null, $reason = '' ) {
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $sName   = $portfolio["name"] ? ' &laquo;'. $portfolio["name"] .'&raquo;' : '';
        $link    = getAbsUrl( '/users/'. $f_user->login .'/viewproj.php?prjid='. $portfolio['id'] );
        $sRason  = $reason ? "\n\nПричина: ". $reason : '';
        $message = "
$f_user->uname $f_user->usurname!

Модераторы нашего ресурса отредактировали вашу работу$sName в портфолио:$sRason

$link

Вы можете обратиться в $sFeedback.

Надеемся на понимание, Команда Free-lance.ru";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * Сообщение об изменении блога (пост/комментарий)
     * 
     * @param  int $rec_type 1- пост, 2 - комментарий
     * @param  string $title заголовок блога
     * @param  string $post_time дата создания блога
     * @param  string $uname имя автора блога 
     * @param  string $usurname фамилия автора блога 
     * @param  string $login логин автора блога 
     * @param  string $reason причина изменений
     * @return @see messages::Add()
     */
    function blogModifiedNotification( $rec_type = 0, $title = '', $post_time = '', $uname = '', $usurname = '', $login = '', $reason = '' ) {
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $sKind   = $rec_type == 1 ? 'ваше сообщение в блоге' : 'ваш комментарий в блоге';
        $sRason  = $reason ? "\n\nПричина: ". $reason : '';
        $message = "
$uname $usurname!

Модераторы нашего ресурса отредактировали $sKind".((trim($title)!="")?" &laquo;".($title)."&raquo;":"")." от ".date("d.m.Y",strtotimeEx($post_time)).":" . $sRason . "

Вы можете обратиться в $sFeedback.

Надеемся на понимание, Команда Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$login,$message,'',1);
    }
    
    /**
     * Сообщение об изменении поста/комментария в сообществах
     * 
     * @param  int $rec_id id записи
     * @param  int $rec_type 1- пост, 2 - комментарий
     * @param  string $login логин автора блога 
     * @param  string $uname имя автора блога 
     * @param  string $usurname фамилия автора блога 
     * @param  string $reason причина изменений
     * @param  int $post_id id поста для комментария
     * @return @see messages::Add()
     */
    function communityModifiedNotification( $rec_id = 0, $rec_type = 0, $login = '', $uname = '', $usurname = '', $reason = '', $post_id = 0 ) {
        if ( $rec_type == 1 ) {
            $sLink = getAbsUrl( getFriendlyURL('commune', $rec_id) );
        }
        else {
            $sLink  = getAbsUrl( getFriendlyURL( 'commune', $post_id) ) . '#c_' . $rec_id;
        }
        
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $sKind     = $rec_type == 1 ? 'ваш пост в сообществах' : 'ваш комментарий в сообществах';
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $message   = "
$uname $usurname!

Модераторы нашего ресурса отредактировали $sKind:
$sLink $sRason

Вы можете обратиться в $sFeedback.

Команда Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $login, $message, '', 1 );
    }
    
    /**
     * отправляет личное сообщение о том что топик в сообществе отредактирован
     * @param array $comm массив полученный из commune::GetCommune
     * @param array $post массив полученный из commune::GetMessage
     * @param string $editor кто отредактировал топик ('comm-author', 'comm-moder', 'site-moder')
     */
    function communityPostModifiedNotification($comm, $post, $editor = 'site-moder') {
        $createDate = date('d.m.Y', strtotime($post['created_time']));
        switch ($editor) {
            case 'comm-author':
                $editorText = 'создателем сообщества';
                break;
            case 'comm-moder':
                $editorText = 'модератором сообщества';
                break;
            case 'site-moder':
                $editorText = 'модератором сайта';
                break;
        }        
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        
        $message   = "
Здравствуйте, {$post['user_uname']} {$post['user_usurname']} [{$post['user_login']}].

Ваше сообщение" . ($post['title'] ? " «" . $post['title'] . "»" : '') . " от {$createDate} в сообществе «{$comm['name']}» было отредактировано $editorText.

Это сообщение было выслано автоматически, отвечать на него не нужно.

Вы можете обратиться в $sFeedback.

Команда Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $post['user_login'], $message, '', 1 );
    }
    
    /**
     * Сообщение об изменении комментария в статьях
     * 
     * @param  int $rec_id id записи
     * @param  string $login логин автора блога 
     * @param  string $uname имя автора блога 
     * @param  string $usurname фамилия автора блога 
     * @param  string $reason причина изменений
     * @param  int $art_id id статьи
     * @return @see messages::Add()
     */
    function artComModifiedNotification( $rec_id = 0, $login = '', $uname = '', $usurname = '', $reason = '', $art_id = 0 ) {
        $sLink     = getAbsUrl( getFriendlyURL( 'article', $art_id) ) . '#c_' . $rec_id;
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $message   = "
$uname $usurname!

Модераторы нашего ресурса отредактировали ваш комментарий в статьях:
$sLink $sRason

Вы можете обратиться в $sFeedback.

Надеемся на понимание, Команда Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $login, $message, '', 1 );
    }
    
    /**
     * Сообщение об изменении проекта/конкурса
     * 
     * @param  int $rec_id id записи
     * @param  int $rec_type 7 - конкурс, не 7 - проект
     * @param  string $login логин автора блога 
     * @param  string $uname имя автора блога 
     * @param  string $usurname фамилия автора блога 
     * @param  string $reason причина изменений
     * @return @see messages::Add()
     */
    function projectsModifiedNotification( $rec_id = 0, $rec_type = 0, $login = '', $uname = '', $usurname = '', $reason = '' ) {
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $sKind     = $rec_type == 7 ? 'конкурс' : 'проект';
        $sLink     = getAbsUrl( getFriendlyURL('project', $rec_id) );
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $message   = "
$uname $usurname!

Модераторы нашего ресурса отредактировали ваш $sKind:
$sLink $sRason

Вы можете обратиться в $sFeedback.

Надеемся на понимание, Команда Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $login, $message, '', 1 );
    }
    
    /**
     * Отправляет уведомления об изменении админом предложения по проекту
     * 
     * @param  int $from_uid UID отправителя
     * @param  int $project_id ID проекта
     * @param  string $reason причина редактирования
     * @return bool true - успех, false - провал
     */
    function prjOfferModifiedNotification( $from_uid = 0, $project_id = 0, $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $pr        = new projects();
        $prj       = $pr->GetPrjCust( $project_id );
        $sName     = $prj['name'] ? ' &laquo;'. $prj['name'] .'&raquo;' : '';
        $sLink     = getAbsUrl( getFriendlyURL('project', $project_id) );
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = 'Здравствуйте, '. $from->uname .' '. $from->usurname .'

Модераторы нашего ресурса отредактировали ваше предложение в проекте'. $sName .'

'. $sLink . $sRason . '

Вы можете обратиться в '. $sFeedback .'.

Надеемся на понимание, Команда Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * Отправляет уведомления об изменении админом предложения по конкурсу
     * 
     * @param  int $rec_id id предложения
     * @param  int $prj_id id конкурса
     * @param  string $login логин автора блога 
     * @param  string $uname имя автора блога 
     * @param  string $usurname фамилия автора блога 
     * @param  string $reason причина изменений
     * @return @see messages::Add()
     */
    function contestOfferModifiedNotification( $rec_id = 0, $prj_id = 0, $login = '', $uname = '', $usurname = '', $reason = '' ) {
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $sLink     = getAbsUrl( getFriendlyURL('project', $prj_id) ) . '#c-offer-' . $rec_id;
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $message   = "
$uname $usurname!

Модераторы нашего ресурса отредактировали вашу работу в конкурсе:
$sLink $sRason

Вы можете обратиться в $sFeedback.

Надеемся на понимание, Команда Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $login, $message, '', 1 );
    }
    
    /**
     * Отправляет уведомления об изменении админом диалога в проекте
     * 
     * @param  int $from_uid UID отправителя
     * @param  int $project_id ID проекта
     * @param  string $msg Текст сообщения
     * @param  string $reason причина редактирования
     * @return bool true - успех, false - провал
     */
    function prjDialogModifiedNotification( $from_uid = 0, $project_id = 0, $msg = '', $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $pr        = new projects();
        $prj       = $pr->GetPrjCust( $project_id );
        $sName     = $prj['name'] ? ' &laquo;'. $prj['name'] .'&raquo;' : '';
        $sLink     = getAbsUrl( getFriendlyURL('project', $project_id) );
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = 'Здравствуйте, '. $from->uname .' '. $from->usurname .'

Модераторы нашего ресурса отредактировали ваш комментарий к предложеню по проекту'. $sName .'

'. $sLink . '
'. $msg . $sRason . '

Вы можете обратиться в '. $sFeedback .'.

Надеемся на понимание, Команда Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
        
    /**
     * Отправляет уведомления об изменении админом комментария к работе в конкурсе
     * 
     * @param  int $pid ID конкурса
     * @param  int $oid ID работы в конкурсе
     * @param  int $cid ID комментария
     * @param  string $login логин автора комментария 
     * @param  string $uname имя автора комментария 
     * @param  string $usurname фамилия автора комментария 
     * @param  string $reason причина редактирования
     * @return bool true - успех, false - провал
     */
    function contestComModifiedNotification( $pid = 0, $oid = 0, $cid = 0, $login = '', $uname = '', $usurname = '', $reason = '' ) {
        $sLink     = getAbsUrl( $GLOBALS['host'] . getFriendlyURL( 'project', $pid ) . '?comm='. $cid .'#comment-'. $cid );
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = 'Здравствуйте, '. $uname .' '. $usurname .'

Модераторы нашего ресурса отредактировали ваш комментарий к работе в конкурсе:

'. $sLink . $sRason . '

Вы можете обратиться в '. $sFeedback .'.

Надеемся на понимание, Команда Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $login, $message, '', 1 );
    }
    
    /**
     * Отправляет уведомления об изменении админом предложения фрилансеров Сделаю
     * 
     * @param  string $msg текст предложения
     * @param  string $login логин автора комментария 
     * @param  string $uname имя автора комментария 
     * @param  string $usurname фамилия автора комментария 
     * @param  string $reason причина редактирования
     * @return bool true - успех, false - провал
     */
    function sdelauModifiedNotification( $msg = '', $login = '', $uname = '', $usurname = '', $reason = '' ) {
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = 'Здравствуйте, '. $uname .' '. $usurname .'

Модераторы нашего ресурса отредактировали ваше объявление в предложениях фрилансеров:

'. $msg . $sRason . '

Вы можете обратиться в '. $sFeedback .'.

Надеемся на понимание, Команда Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $login, $message, '', 1 );
    }
    
   
    /**
     * Отправляет уведомления об изменении админом платных мест на главной или в каталоге
     * 
     * @param  string $login логин автора комментария 
     * @param  string $uname имя автора комментария 
     * @param  string $usurname фамилия автора комментария 
     * @param  string $reason причина редактирования
     * @return bool true - успех, false - провал
     */
    function carouselModifiedNotification( $login = '', $uname = '', $usurname = '', $reason = '' ) {       
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = 'Здравствуйте, '. $uname .' '. $usurname .'

Модераторы нашего ресурса отредактировали ваше платное объявление на &laquo;Карусели&raquo;:'. $sRason . '

Вы можете обратиться в '. $sFeedback .'.

Надеемся на понимание, Команда Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $login, $message, '', 1 );
    }
    
    /**
     * Отправляет уведомления об изменении админом сообщения в личке ("Мои контакты").
     * 
     * @param  int $from_uid UID отправителя
     * @param  int $prof_id ID профессии
     * @param  string $reason причина редактирования
     * @return bool true - успех, false - провал
     */
    function portfChoiceModifiedNotification( $from_uid = 0, $prof_id = 0, $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $prj       = professions::GetProfDesc( $from_uid, $prof_id );
        $sLink     = getAbsUrl( '/users/'. $from->login .'/setup/#prof'. $prof_id );
        $sRason    = $reason ? "\n\nПричина: ". $reason : '';
        $sFeedback = str_replace( '//', '/{службу поддержки}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = 'Здравствуйте, '. $from->uname .' '. $from->usurname .'

Модераторы нашего ресурса отредактировали ваши уточнения к разделу &laquo;'. $prj['profname'] .'&raquo;

'. $sLink . $sRason . '

Вы можете обратиться в '. $sFeedback .'.

Надеемся на понимание, Команда Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * Отправляет уведомления об удалении предложения в проекте
     * 
     * @param array $offer информация о предложении
     */
    function offerDeletedNotification( $offer = array() ) {
        if ( $offer ) {
            $message = 'Здравствуйте, '. $offer['uname'] .' '. $offer['usurname'] .'

Модераторы нашего ресурса удалили ваше предложение в проекте &laquo;'. $offer['name'] .'&raquo;
'. $GLOBALS['host'] . getFriendlyURL('project', $offer['project_id']) .'

Мы призываем вас впредь не нарушать правила портала, иначе модераторы лишат ваш аккаунт доступа к сайту. 

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться. 

Надеемся на понимание, Команда Free-lance.ru
';
            
            messages::Add( users::GetUid($err,"admin"), $offer['login'], $message, '', 1 );
        }
    }
    
    /**
     * Отправляет уведомления об удалении комментария в предложении в проекте
     * 
     * @param array $dialogue информация о комментарии
     */
    function dialogueMessageDeletedNotification( $dialogue = array() ) {
        if ( $dialogue ) {
            $message = 'Здравствуйте, '. $dialogue['uname'] .' '. $dialogue['usurname'] .'

Модераторы нашего ресурса удалили ваше сообщение в предложении по проекту &laquo;'. $dialogue['name'] .'&raquo;
'. $GLOBALS['host'] . getFriendlyURL('project', $dialogue['project_id']) .'

Мы призываем вас впредь не нарушать правила портала, иначе модераторы лишат ваш аккаунт доступа к сайту. 

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться. 

Надеемся на понимание, Команда Free-lance.ru
';
            
            messages::Add( users::GetUid($err,"admin"), $dialogue['login'], $message, '', 1 );
        }
    }
    
    function portfolioDeletedNotification( $name = '', $surname = '', $login = '' ) {
        if ( $login ) {
            $message = 'Здравствуйте, '. $name .' '. $surname .'

Модераторы нашего ресурса удалили вашу работу в портфолио.

Мы призываем вас впредь не нарушать правила портала, иначе модераторы лишат ваш аккаунт доступа к сайту. 

Это сообщение было выслано автоматически, и ответ на него не будет рассматриваться. 

Надеемся на понимание, Команда Free-lance.ru
';
            
            messages::Add( users::GetUid($err,"admin"), $login, $message, '', 1 );
        }
    }
    
    /**
     * отправляет сообщение что аккаунт партнера по СБР заблокирован
     * @param array $partnersLogins массив с логинами получателей уведомления
     * @param string $login логин заблокированного пользователя
     */
    function yourSbrPartnerIsBanned(array $partnersLogins, $login) {
        if (!is_array($partnersLogins)) {
            return;
        }
        $message = 
'Free-lance.ru: заблокирован пользователь, с которым вы заключили «Безопасную Сделку»

Здравствуйте!

Сообщаем вам, что пользователь [' . $login . '], с которым вы работаете через сервис «Безопасная Сделка», был заблокирован администрацией сайта. Для завершения текущих сделок с данным пользователем обратитесь в Арбитраж.

Подробная инструкция по завершению сотрудничества через арбитражную комиссию находится в соответствующей статье раздела «Помощь».

Приятной работы!
Команда Free-lance.ru';

        foreach ($partnersLogins as $targetLogin) {
            messages::Add(users::GetUid($err, "admin"), $targetLogin, $message, '', 1);
        }
        
    }


    /**
     * Возвращает сообщение от администрации по шаблону, для лички.
     *
     * @param string $message Текст сообщения
     */
    function AdminMsgFromTempl($message) {
        $tpl = "
Здравствуйте.

Команда \"Free-lance.ru\" благодарит Вас за Ваше желание участвовать в жизни нашего проекта.


{$message}

--
Команда \"Free-lance.ru\"
info@free-lance.ru
www.free-lance.ru
        ";
        return $tpl;
    }
	
    /**
     * Возвращает список годов, в которых есть массовые рассылки у личных менеджеров.
     * 
     * @param  string $sUid UID пользователя
     * @return array
     */
	function pmAutoFoldersGetYears( $sUid = '' ) {
	    $DBproxy = new DB;
	    return $DBproxy->col( 'SELECT * FROM mess_pm_folder_years(?i)', $sUid );
	}
	
	/**
	 * Возвращает автоматические папки для массовых рассылок личных менеджеров.
	 * 
	 * @param  string $sUid UID пользователя
	 * @param  string $sYear за какой год
	 * @param  int $nLimit
	 * @param  int $nOffset
	 * @return array
	 */
	function pmAutoFolders( $sUid = '', $sYear = '', $nLimit = 0, $nOffset = 0 ) {
	    $DBproxy = new DB;
	    return $DBproxy->rows( 'SELECT * FROM mess_pm_folders_get(?i, ?i, ?i, ?i)', $sUid, $sYear, $nLimit, $nOffset );
	}
	
	/**
	 * Возвращает количество автоматических папок для массовых рассылок личных менеджеров.
	 *
	 * @param  string $sUid UID пользователя
	 * @param  string $sYear за какой год
	 * @return int
	 */
	function pmAutoFoldersCount( $sUid = '', $sYear = '' ) {
	    $DBproxy = new DB;
	    return $DBproxy->val( 'SELECT mess_pm_folders_count(?i, ?i)', $sUid, $sYear );
	}
	
	/**
	 * Возвращает автоматическую папку пользователя по ID
	 * 
	 * @param  string $sUid UID пользователя
	 * @param  string $sFolderId ID папки
	 * @return array
	 */
	function pmAutoFolderGetById( $sUid = '', $sFolderId = '' ) {
	    $DBproxy = new DB;
	    return $DBproxy->row( 'SELECT * FROM mess_pm_folder_get(?i, ?i)', $sUid, $sFolderId );
	}
	
	/**
	 * Переименовывает автоматическую папку пользователя
	 * 
	 * @param  string $sUid UID пользователя
	 * @param  string $sFolderId ID папки
	 * @param  string $sName новое название папки
	 * @return string пустая строка - успех, сообщение об ошибке - провал.
	 */
    function pmAutoFolderRename( $sUid, $sFolderId, $sName = '' ) {
        $sError = '';
        
        if ( $sName ) {
            $DBproxy = new DB;
            $DBproxy->val('SELECT mess_pm_folder_rename(?i, ?i, ?)', $sUid, $sFolderId, $sName );
            $sError = $DBproxy->error;
        }
        
        return $sError;
    }
    
    /**
     * Удалить автоматическую папку пользователя
     * 
     * @param  string $sUid UID пользователя
     * @param  string $sFolderId ID папки
     * @return string пустая строка - успех, сообщение об ошибке sql - провал
     */
    function pmAutoFolderDelete( $sUid, $sFolderId ) {
        $DBproxy = new DB;
        $DBproxy->query( 'SELECT mess_pm_folder_delete(?i, ?i)', $sUid, $sFolderId );
        return $DBproxy->error;
    }
    
    /**
	 * Получить контакты из автоматической папки пользователя
	 *
	 * @param  string $sUid UID пользователя.
     * @param  string $sFolderId папка, для которой получаем контакты
     * @param  string $sSearch подстрока для поиска в логине пользователя
	 * @return array
	 */
	function pmAutoFolderGetContacts( $sUid = '', $sFolderId = '', $sSearch = '' ) {
	    $DBproxy = new DB;
	    
	    if ( $sSearch ) {
            $sQuery = $DBproxy->parse( 'SELECT * FROM messages_search_pm_folder(?, ?, ?)', $sUid, $sFolderId, $sSearch );
	    }
	    else {
            $sQuery = $DBproxy->parse( 'SELECT * FROM messages_contacts_pm_folder(?, ?)', $sUid, $sFolderId );
	    }
        
	    return $DBproxy->rows( $sQuery );
	}

    /**
     * Нужно ли использовать капчу для защиты от рассылки спама
     *
     * @param   integer $uid    ID пользователя 
     * @return  boolean         true - да, false - нет
     */
    function isNeedUseCaptcha($uid) {
        global $DB, $ourUserLogins;
        $ret = NULL;
        $user = new users();
        $login = $user->GetField($uid,$ee,'login');
        foreach($ourUserLogins as $ourUserLogin) {
            if(strtolower($login)==strtolower($ourUserLogin)) {
                $ret = false;
            }
        }
        if(hasGroupPermissions('administrator') || hasGroupPermissions('moderator')) { $ret = false; }
        if($ret===NULL) {
            $sql = "SELECT EXTRACT(EPOCH FROM date) as date, count FROM messages_sendlog WHERE uid=?i";
            $log = $DB->row($sql, $uid);
            if($log) {
                $spam_msg_count = (account::checkPayOperation($uid) ? self::SPAM_CAPTCHA_MSG_COUNT_PAY : self::SPAM_CAPTCHA_MSG_COUNT);
                if($log['count']>=$spam_msg_count && ($log['date']+self::SPAM_CAPTCHA_TIME_SHOW)>time()) {
                    $ret = true;
                } else {
                    $ret = false;
                }
            } else {
                $ret = false;
            }
        }
        return $ret;
    }

    function updateSendLog($uid) {
        global $DB;
        $sql = "SELECT EXTRACT(EPOCH FROM date) as date, count FROM messages_sendlog WHERE uid=?i";
        $log = $DB->row($sql, $uid);
        if($log) {
            $spam_msg_count = (account::checkPayOperation($uid) ? self::SPAM_CAPTCHA_MSG_COUNT_PAY : self::SPAM_CAPTCHA_MSG_COUNT);
            if(($log['count']<=$spam_msg_count && ($log['date']+self::SPAM_CAPTCHA_TIME_WITHOUT)>time()) || $log['count']>=$spam_msg_count) {
                if($log['count']>$spam_msg_count && ($log['date']+self::SPAM_CAPTCHA_TIME_SHOW)>time()) {
                    $sql = "UPDATE messages_sendlog SET count=count+1 WHERE uid=?i";
                } else {
                    if($log['count']<=$spam_msg_count) {
                        $sql = "UPDATE messages_sendlog SET count=count+1 WHERE uid=?i";
                    } else {
                        $sql = "UPDATE messages_sendlog SET count=1, date=NOW() WHERE uid=?i";
                    }
                }
            } else {
                $sql = "UPDATE messages_sendlog SET count=1, date=NOW() WHERE uid=?i";
            }
        } else {
            $sql = "INSERT INTO messages_sendlog(uid, date, count) VALUES(?i, NOW(), 1)";
        }
        $DB->query($sql, $uid);
    }
    
    /**
     * очищает имя отправителя, хранящееся в буфере, у всех получателей
     * @param type $sender_uid uid отправителя
     */
    public function clearMessageSender ($sender_uid) {
        $mem = new memBuff();
        $mem->touchTag("msgsNewSenderID{$sender_uid}");
    }
    
    
    /**
     * Прверить если ли файл в переписке пользователей
     * 
     * @param type $from_id
     * @param type $to_id
     * @param type $file_id
     * @return type
     */
    public function isFileExist($from_id, $to_id, $file_id)
    {
        $res = $this->DB->val("SELECT messages_file_exist(?i,?i,?i);", $from_id, $to_id, $file_id);
        return $res == 't';
    }

    
   
   /**
    * Разрешить отправку сообщений
    * 
    * @global type $DB
    * @param type $to_id
    * @param type $from_id
    * @return boolean
    */ 
   public static function setIsAllowed($to_id, $from_id, $stop_check = false)
   {
       global $DB;
       
       if (!$stop_check && 
            self::_isAllowed($to_id, $from_id)) {
           
           return true;
       }
       
       $DB->val("
           INSERT INTO " . self::TABLE_ALLOWED . " (to_id, from_id) 
           SELECT ?i, ?i WHERE NOT EXISTS(SELECT 1 FROM " . self::TABLE_ALLOWED . "
           WHERE to_id = ?i AND from_id = ?i LIMIT 1);
        ", 
           $to_id, $from_id,
           $to_id, $from_id   
       );
       
       $mem = new memBuff();
       $cache_tag_key = sprintf(self::CACHE_TAG_IS_ALLOWED, $from_id);
       $mem->delete($cache_tag_key);
       
       
       if (is_beta()) {                
            require_once(ABS_PATH . "/classes/log.php");                                                                                                                                                                                                                                  
            $log = new log('debug/0029319-%d%m%Y.log'); 
            $log->writeln('----- ' . date('d.m.Y H:i:s'));
            $log->writeln("to_id = {$to_id}, from_id = {$from_id}");
       }
   }

   
   
   /**
    * Проверка в БД возможнолсть отправки сообщения
    * 
    * @global type $DB
    * @param type $to_id
    * @param type $from_id
    * @return type
    */
   public static function _isAllowed($to_id, $from_id)
   {
       global $DB;
       static $exists_allowed = null;
       
       $cache_tag_key = sprintf(self::CACHE_TAG_IS_ALLOWED, $from_id);
       
       if (!$exists_allowed) {
           
           $mem = new memBuff(); 
           
           if (!$exists_allowed = $mem->get($cache_tag_key)) {
           
                $_exists_allowed = $DB->col('SELECT to_id FROM ' . self::TABLE_ALLOWED . ' 
                                             WHERE from_id = ?i', $from_id);
                
                if ($_exists_allowed) {
                    $exists_allowed = array_flip($_exists_allowed);
                    $mem->set($cache_tag_key, $exists_allowed, 604800);
                }
           }
           
       }
            
       return isset($exists_allowed[$to_id]);
   }

   


   /**
     * Проверка возможности отправить сообщение ползователю
     * 
     * @global type $DB
     * @staticvar null $exists_allowed
     * @param type $to_id
     * @param type $from_id
     * @return boolean
     */
    public static function isAllowed($to_id, $from_id = null) 
    {
        $is_auth = isset($_SESSION['uid']) && $_SESSION['uid'] > 0;

        if (!$from_id && !$is_auth) {
            return false;
        } 
        
        if (!$from_id) {
            $from_id = $_SESSION['uid'];
        }
               
        if ($is_auth && (currentUserHasPermissions('users') || is_emp())) {
            return true;
        }
        

        $is_allowed = self::_isAllowed($to_id, $from_id);
        
        
        if(!$is_allowed) {
            
            //Была ли уже проверка доступности
            //тогда пользователю запрещено писать
            $key_check_is_allowed = sprintf(self::KEY_CHECK_IS_ALLOWED, $from_id, $to_id);
            $mem = new memBuff();            

            if ($mem->get($key_check_is_allowed)) {
                return false;
            }

            
            //Иначе делаем проверку
            
            //Которые уже хотя бы раз общались с заказчиком через личку, 
            //например если заказчик инициировал общение или они ранее общались
            $proxy_db = new DB();
            $is_allowed = $proxy_db->val("SELECT messages_dialog_count(?i, ?i)", $to_id, $from_id) > 0;
            
            //Которых заказчик выбрал исполнителем в любом своем проекте
            if (!$is_allowed) {
                require_once(ABS_PATH . "/classes/projects.php");
                $is_allowed = (bool)projects::isExec($from_id, $to_id);
            }
            

            //В список условий я бы еще добавил проведение заказа на сайте - если есть завершенный заказ 
            //(с резервом или без, по ТУ, проекту или прямой) с данным заказчиком, то тоже разрешать 
            //исполнителю писать ему в личку, так как в заказе светится логин заказчика и они уже сотрудничали.
            if (!$is_allowed) {
                require_once(ABS_PATH . "/tu/models/TServiceOrderModel.php");
                $is_allowed = (bool)TServiceOrderModel::hasSuccessfulOrder($from_id, $to_id);
            }

            
            //Если фрилансер был выбран на любое призовое место в конкурсе то 
            //он может писать сообщения заказчику.
            if (!$is_allowed) {
                require_once(ABS_PATH . "/classes/contest.php");
                $is_allowed = (bool)contest::isPrizePlace($from_id, $to_id);
            }
            
            
            if ($is_allowed) {
                self::setIsAllowed($to_id, $from_id, true);
            }   
            
            
            $mem->set($key_check_is_allowed, 1, 0, self::KEY_CHECK_TAG_IS_ALLOWED);
        }
        
        return $is_allowed;
    }
    
    
    
    
}
