<?
/**
 * Обработка и хранение сессий (memcached)
 * НЕ УЧИТЫВАЕТ FREETRAY
 *
 */
class session extends Memcached
{

	/** 
	 * Содержит строку с последней активностью (см. view_online_status())
	 * @var string
	 */
    public $ago;
	
	/** 
	 * Дата последней активности (см. view_online_status())
	 * @var string
	 */
    public $last_ref;
    
	/** 
	 * Активен ли щас пользователь (см. view_online_status())
	 * @var boolean
	 */
    public $is_active;
    
	/** 
	 * Есть ли соединение с сервером мем-кеша
	 *
	 * @var boolean
	 */
	private $bIsConnected = false;
    
    /**
     * Класс для записи лога
     * 
     * @var object 
     */
    private $_log = NULL;
    
    /**
     * Была ли ошибка при работе с memcache
     * 
     * @var type 
     */
    public $err = FALSE;
	
	/**
	 * Конструктор. Подключается к серваку мемкэша
	 * 
	 */
	function __construct() {
		parent::__construct();
        if ( !($server = $GLOBALS['memcachedSessionServer']) ) {
            // В /classes/config.php добавляем:
            // $memcachedSessionServer = 'localhost';
            if ( !($server = $GLOBALS['memcachedServers'][0]) )
                die('Не найдены сервера Memcached');
        }
        $this->bIsConnected = $this->addServer($server, 11211);
        
        $this->setOption (self::OPT_PREFIX_KEY , SERVER);
        
        $this->_log = new log('sessions/error-%d%m%Y.log', 'a');
	}
	
	/**
	 * Открыть соединение
	 *
	 * @return boolean
	 */
   function open() {
       return $this->bIsConnected;
   }
   
   /**
    * Закрыть соединение
    *
    * @return boolean
    */
   function close() {
       return true;//$this->close();
   }
   
   /**
    * Читает данные из кеша
    *
    * @param string $sessID		идентификатор сессии
    * @return array				массив с данными по сессии или пустая строка (если сессия не найдена)
    */
   function read($sessID) {
       // fetch session-data
	   $results = "";
       $res = $this->get($sessID);
       // return data or an empty string at failure
       $this->_error('get', $sessID);
       if ( $res ) {
           return $res;
       } else  {
           $this->_error('get', $sessID);
       }
       return settype($results, 'string');
   }
   
   /**
    * Сохраняет данные сессии в кеш
    *
    * @param string $sessID		идентификатор сессии
    * @param array $sessData	данные сессии
    * @return boolean			true в случае удачной записи
    */
   function write($sessID, $sessData) {
       $ret = $this->set($sessID, $sessData, 7200);
       if ( $ret === FALSE ) {
           $this->_error('set', $sessID);
       }
       if ( !empty($_SESSION['login']) ) {
           $last_ref['date'] = $_SESSION['last_refresh']; // см. users::regVisit()
           $last_ref['sid'] = $sessID;
           $ret = $this->set($_SESSION['login'], $last_ref, 7200);
            if ( $ret === FALSE ) {
                $this->_error('set', $sessID);
            }
       }
       return $ret;
   }
   
   /**
    * Убивает сессию по ее ID
    *
    * @param string $sessID		идентификатор сессии
    * @return boolean			true в случае удачи
    */
   function destroy($sessID) {
       // delete session-data
       $ret = $this->delete($sessID);
       return $ret;
   }
   
   /**
    * Сборщик мусора - используется внутренний для memcache см. write()
    *
    * @return boolean	всегда возвращает true	
    */
   function gc() {

       return true;
   }
   
   /**
    * Есть ли юзер на сайте
    *
    * @param string $login		логин юзера
    * @return string			дата последней активности юзера в формате ISO 8601 или 0 если сессия не найдена
    */
   function getActivityByLogin($login){
   		if (!$login) return 0;
   		$last_ref = $this->get($login);

        if (!isset($last_ref['sid']) && isset($last_ref['sess_id'])) {
            $last_ref['sid'] = $last_ref['sess_id'];
            
            if (isset($last_ref['data']['date'])) {
                $last_ref['date'] = $last_ref['data']['date'];
            }
        }

   		if ($last_ref)
   			$sessData = $this->get($last_ref['sid']);
		if($sessData)
           return $last_ref['date'];
        else $this->destroy($login);
		return 0;
   }
   
    /**
     * Сбрасывает активность юзера на сайте
     * 
     * @param  string $login логин юзера
     * @return bool true - успех, false - провал
     */
    function nullActivityByLogin( $login ) {
        $bRet = false;
        
        if ( $login ) {
            $last_ref = $this->get( $login );
            
            if ( $last_ref) {
                $last_ref['date'] = null;
                $ret = $this->set( $login, $last_ref, 7200);
                $bRet = true;
            }
        }
        
        return $bRet;
    }

   /**
   * Обновляет дату окончания PRO в сессии пользователя
   * 
   * @param string $login   логин пользователя
   */
   function UpdateProEndingDate($login) {
        if(!$login) return;
        $s = $this->get($login);
        if($s) {
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/payed.php");
            $pro_last = payed::ProLast($login);
            $pro_last = $pro_last['freeze_to'] ? false : $pro_last['cnt'];
            $session_data = $this->read($s['sid']);
            
            $session_data = preg_replace(
                    "/;pro_last\|(?:s:0:\"\"|s:[0-9]{2}:\".*\"|b\:0|N)/U",
                    ";pro_last|s:".strlen($pro_last).":\"$pro_last\"",
                    $session_data);
            
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/account.php";
    		$user = new users();
            $user->GetUser($login);
            $account = new account();
            $account->GetInfo($user->uid);
            $session_data = preg_replace("/ac_sum\|s:\d{1,}:\".*\"/U","ac_sum|s:".strlen($account->sum).":\"$account->sum\"",$session_data);
            $session_data = preg_replace("/is_profi\|b:[0-1]/U","is_profi|b:".(($user->isProfi())?'1':'0'), $session_data);
            
            $this->set($s['sid'],$session_data,7200);
        }
   }
   
   /**
   * Обновляет статус верификации в сессии пользователя
   * 
   * @param string $login   логин пользователя
   */
   function UpdateVerification($login) {
        if(!$login) return;
        $s = $this->get($login);
        if($s) {
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
            $user = new users();
            $user->GetUser($login);
            
            $session_data = $this->read($s['sid']);
            
            $session_data = preg_replace("/is_verify\|s:1:\"[ft]\"/U", "is_verify|s:1:\"".$user->is_verify."\"", $session_data);

            $this->set($s['sid'],$session_data,7200);
        }
   }
   
   /**
    * Обновляем деньги у пользователя по его логину
    * 
    * @param string $login    Логин пользователя
    * @return type 
    */
   function UpdateAccountSum($login) {
        if(!$login) return;
        $s = $this->get($login);
        if($s) {
            $session_data = $this->read($s['sid']);
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/account.php";
    		$user = new users();
            $user->GetUser($login);
            $account = new account();
            $account->GetInfo($user->uid);
            $session_data = preg_replace("/ac_sum\|s:\d{1,}:\".*\"/U","ac_sum|s:".strlen($account->sum).":\"$account->sum\"",$session_data);
            $session_data = preg_replace("/ac_sum\|d:\d+?;/U", "ac_sum|s:".strlen($account->sum).":\"$account->sum\";", $session_data);
            $session_data = preg_replace("/bn_sum\|s:\d{1,}:\".*\"/U","bn_sum|s:".strlen($account->bonus_sum).":\"$account->bonus_sum\"",$session_data);
            $this->set($s['sid'],$session_data,7200);
        }
   }
   
    /**
    * Обновляет данные об антиюзере
    * 
    * $login - у кого обновляем сессию
    * $antiUser - объект класса users с нужными данными
    */
    /*public function UpdateAntiuser ($login, $antiUser) {
        if (!$login) return;
        $s = $this->get($login);
        if (!$s) return;
        $s['anti_uid'] = $antiUser->uid;
        $s['anti_login'] = $antiUser->login;
        $s['anti_surname'] = $antiUser->surname;
        $s['anti_name'] = $antiUser->name;
        $set = $this->set($login, $s, 7200);
    }*/

   /**
    * Отображает значек активности юзера на сайте
    *
    * @param string $login		логин юзера
    * @param boolean $full		отображать ли строковую информацию ("Нет на сайте")
    * @return string			HTML-код значка активности
    */
   function view_online_status($login, $full=false, $nbsp='&nbsp;', &$activity = NULL){
        $this->is_active = false;
        $this->ago = 0;
        $this->last_ref = NULL;
		if ($login) {
			$this->last_ref = $this->getActivityByLogin($login);
			$activity = $this->last_ref;
			$last_ref_unixtime = strtotime($this->last_ref);
		}
		if ($this->last_ref && (time() - $last_ref_unixtime <= 30*60)) {	      
            $this->ago = ago_pub($last_ref_unixtime);
			$this->ago = ago_pub(strtotime($this->last_ref));
            $this->is_active = true;
			if (intval($this->ago) == 0) $this->ago = 'менее минуты';
		/*	return  ($full ? "<span class='u-act' title=\"Последняя активность была ".$this->ago." назад\">На сайте</span>" : "{$nbsp}<img src=\"/images/dot_active.png\" class=\"u-inact\" alt=\"Последняя активность была ".$this->ago." назад\" title=\"Последняя активность была ".$this->ago." назад\" />$nbsp");*/
		/*	return  "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335 b-layouyt__txt_weight_normal'>На сайте.</span>"; */
        return  ($full ? "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335 b-layouyt__txt_weight_normal'>На сайте.</span>" : "<span class=\"b-icon b-icon__lamp\" title='На сайте'></span>$nbsp");
		}
	/*	return ($full ? "<span class='u-inact'>Нет на сайте</span>" : "{$nbsp}<img src=\"/images/dot_inactive.png\" class=\"u-inact\" alt=\"Нет на сайте\" title=\"Нет на сайте\" />$nbsp");*/
	/*	return "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080 b-layouyt__txt_weight_normal'>Нет на сайте.</span>"; */
      return ($full ? "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080 b-layouyt__txt_weight_normal'>Нет на сайте.</span>" : '');
	}

   /**
    * Отображает значек активности юзера на сайте (новая версия view_online_status)
    *
    * @param string $login		логин юзера
    * @param boolean $full		отображать ли строковую информацию ("Нет на сайте")
    * @return string			HTML-код значка активности
    */
    function view_online_status_new($login, $full=false, $nbsp='&nbsp;', &$activity = NULL){
        if ($login)
            $last_ref = $this -> getActivityByLogin($login);
        $activity = $last_ref;
        $last_ref_unixtime = strtotime($last_ref);
        if ($last_ref && (time() - $last_ref_unixtime <= 30*60)){
            $ago = ago_pub(strtotimeEx($last_ref));
            if (intval($ago) == 0) $ago = "менее минуты";
          /*  return  ($full ? "<span class='u-act' title=\"Последняя активность была ".$ago." назад\">На сайте</span>" : "{$nbsp}<img src=\"/images/dot_active.png\" class=\"u-act\" alt=\"Последняя активность была ".$ago." назад\" title=\"Последняя активность была ".$ago." назад\" />$nbsp");*/
            return  "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335 b-layouyt__txt_weight_normal'>На сайте.</span>";
        }
        /*return  ($full ? "<span class='u-inact'>Нет на сайте</span>" : "{$nbsp}<img src=\"/images/dot_inactive.png\" width=\"8\" height=\"9\" alt=\"Нет сайте\" class=\"u-inact\" title=\"Нет на сайте\" />$nbsp");*/
        return  "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080 b-layouyt__txt_weight_normal'>Нет на сайте.</span>";
    }
	
	/**
	 * Уничтожает сессию пользователя по его логину
	 *
	 * @param string $login		логин юзера
	 * @return boolean			true если сессия была уничтожена, false - если не найдена
	 */
	function logout($login){
		if (!$login) return 0;
   		$last_ref = $this->get($login);
   		if ($last_ref['sid'])
   		    $this->destroy($last_ref['sid']);
   		$ret = $this->destroy($login);
       return $ret;
	}

    /**
     * Запись в лог при возникновении ошибки
     * 
     * @param  string  $optype  тип операции (get, set, add)
     * @param  string  $key     ключ в memcache
     * @return void
     */
    private function _error($optype = NULL, $key = NULL) {
	    if(!$this->_log->linePrefix) {
    		$this->_log->linePrefix = '%d.%m.%Y %H:%M:%S - ' . getRemoteIP()
    		                        . ' - "'
    		                        . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']
    		                        . ($_SERVER['REQUEST_METHOD']=='POST' && $_POST ? '?'.http_build_query($_POST) : '')
    		                        . '" : ';
		}
	    $rcode = $this->getResultCode();
	    $rmsg = $this->getResultMessage();
	    $ttime = $this->_log->getTotalTime('%H:%M:%S', 3);
	    if($rcode == Memcached::RES_NOTFOUND
	       || $rcode == Memcached::RES_SUCCESS
	       || ($optype == 'add' && $rcode == Memcached::RES_NOTSTORED)
	      )
	    {
	        return;
	    }
        $this->err = TRUE;
	    $this->_log->writeln("[error: {$rcode}, method: {$optype}, key: {$key}, time: {$ttime}] {$rmsg}");
	}
    
    
    
    /**
     * Сохранить сообщения до следующего обращения к странице
     * 
     * @param type $value - тест сообщения
     * @param string $key - ключ для специфического сообщения
     * @param type $type - тип (пока не используется)
     * @return boolean
     */
    public static function setFlashMessage($value, $key = 'default', $type = 'success')
    {
        if (empty($value))  {
            return false;
        }
        
        $_SESSION['flash_message'][$key] = array(
            'type' => $type, 
            'value' => $value);
        return true;
    }
    
    
    /**
     * Показать текущее сообщение
     * 
     * @return string
     */
    public static function getFlashMessages($key = 'default')
    {   
        if (!isset($_SESSION['flash_message'][$key])) {
            return '';
        }
        
        $message = $_SESSION['flash_message'][$key]['value'];
        unset($_SESSION['flash_message'][$key]);
        return $message;
    }  
    
}