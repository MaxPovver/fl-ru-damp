<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/globals.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");

/**
 * Кеширование произвольной информации через MemCache
 *
 */
class memBuff extends Memcached
{
    const SERVERS_VARKEY = 'MEMCACHED_SERVERS_VARKEY';
	
	/**
	 * Есть ли соединение с сервером мем-кеша
	 *
	 * @var boolean
	 */
	private $bIsConnected = false;
	
	/**
	 * Имя сервера. Задается в config.php как SERVER
	 * 
	 * @var string
	 */
	private $server = '';
	
	/**
	 * Данные пришли из кеша или из базы. Только для ф-ции getSql()
	 * true - из кеша
	 *
	 * @var boolean
	 */
	private $bWasMqUsed = true;
	
    /**
     * Количество ожиданий (с промежутком в одну секунду) появления запрашиваемых данных в мемкеше
     * в случает если их там нет, но их запрашивает сразу несколько скриптов. Для смягчения dogpile эфекта
     * Единый цикл для всех запущенных копий объекта
     * 
     * @var integer
     */
    private static $__edwCycles = 2;
    
    /**
     * Не использовать блокировки при запросе данных
     * 
     * @var boolean 
     */
    public $noLock = FALSE;
    
	private $_log;
	
	/**
	 * Конструктор. Подключается к серваку мемкэша
	 */
	public function __construct($noLock=FALSE) {
   		parent::__construct();
		$this->_log = new log('memcached/error-%d%m%Y.log', 'a');
        $this->setOption(Memcached::OPT_NO_BLOCK, true);
        $this->setOption(Memcached::OPT_COMPRESSION, false);
        
        $svk = $GLOBALS[memBuff::SERVERS_VARKEY];
        if(!$svk || !$GLOBALS[$svk]) {
            $svk = 'memcachedServers';
        }
        $servers = $GLOBALS[$svk] OR die('Server error: 121');
        foreach ($servers as $s){
            $this->bIsConnected = $this->addServer($s, 11211);
        }
	    
	    if (!$this->bIsConnected) {
	        $this->_error('connect');
	    }
	    $this->server = (defined('SERVER')?SERVER:'');
        $this->noLock = $noLock || (SERVER==='local' || defined('IS_LOCAL') && IS_LOCAL);
	}
	
	/**
	 * Деструктор. Отключается от сервака мемкэша
	 */
	public function __destruct() {
		//if ($this->bIsConnected) $this->close();
	}
	
	/**
	 * Возвращает false если данные пришли из базы
	 * 
	 * @return boolean		false - из базы, true - из кеша
	 */
	public function getBWasMqUsed() {
		return $this->bWasMqUsed;
	}
	
    /**
     * Запрашивает данные из мемкеша.
     *
     * @param string $key		ключ для поиска
     * @return array			результат. false, если не найдено
     */
    public function get($key) {
        $output = FALSE;
        $chLock = FALSE;
        
        if ($this->bIsConnected) {
            $fKey = $key . $this->server;            
            $output = parent::get($fKey);
            
            if ( is_array($output) && isset($output['__expire']) ) {
                if ( $output['__expire'] < time() && ($this->noLock || (parent::get($fKey . '_lock') === FALSE)) ) {
                    $chLock = TRUE;
                    $output = FALSE;
                }
            }
                
            if ( is_array($output) && array_key_exists('__data', $output) && array_key_exists('__tags', $output) ) {
                if ( count($output['__tags']) ) {
                    $tags = parent::getMulti(array_keys($output['__tags']));
                    if ( !$tags ) {
                        $tags = array();
                    }
                    if ( array_sum(array_values($output['__tags'])) != array_sum(array_values($tags)) ) {
                        if ( $this->noLock || $chLock || (parent::get($fKey . '_lock') === FALSE) ) {
                            $chLock = TRUE;
                            $output = FALSE;
                        }
                    }
                }
            }
                
            if ( isset($output['__data']) ) {
                $output = $output['__data'];
            }
                
            if ( !$this->noLock && $output === FALSE ) {
                if ( $chLock || (parent::get($fKey . '_lock') === FALSE) ) {
                    parent::set($fKey . '_lock', 1, 30);
                } else {
                    if ( self::$__edwCycles > 0 ) {
                        self::$__edwCycles--;
                        sleep(1);
                        $output = $this->get($key);
                    }
                }
            }
                
        }
        
        if ($output === FALSE) {
            $this->_error('get', $key);
        }
        
        return $output;
    }

    
    /**
     * Запрашивает набор данных из мемкеша
     *
     * @param  array  $keys  массив со списком ключей для получения
     * @return array         массив с результатом
     */
    public function gets($keys) {
        if ( $this->bIsConnected ) {
            $this->_error('get', implode(',', $keys));
        }
            
        $output   = array();
        $fullKeys = array();
        $locks    = array();
        $keyLocks = array();
        $setLocks = array();
        $waiting  = FALSE;
            
        foreach ( $keys as $k => $v ) {
            $fullKeys[$k] = $v . $this->server;
        }
            
        $res = parent::getMulti($fullKeys);
        if ( !is_array($res) ) {
            $this->_error('get', implode(',', $keys));
        }
        
        foreach ( $keys as $k ) {
            if ( isset($res[$k . $this->server]) ) {
                $output[$k] = &$res[$k . $this->server];
                if ( is_array($output[$k]) && isset($output[$k]['__expire']) && ($output[$k]['__expire'] < time()) ) {
                    $keyLocks[] = $k . $this->server . '_lock';
                }
            } else {
                $keyLocks[] = $k . $this->server . '_lock';
            }
        }
                
        if ( !$this->noLock && $keyLocks ) {
            $locks = parent::getMulti($keyLocks);
        }
                
        foreach ( $keys as $key ) {
                    
            $out = isset($output[$key])? $output[$key]: FALSE;
                    
            if ( is_array($out) && isset($out['__expire']) ) {
                if ( ($out['__expire'] < time()) && !isset($locks[$key . $this->server . '_lock']) ) {
                    $out = FALSE;
                }
            }
                    
            if ( is_array($out) && isset($out['__data']) && isset($out['_tags']) ) {
                if ( count($out['__tags']) ) {
                    $tags = parent::getMulti(array_keys($out['__tags']));
                    if ( !$tags ) {
                        $tags = array();
                    }
                    if ( array_sum(array_values($out['__tags'])) != array_sum(array_values($tags)) ) {
                        if ( !isset($locks[$key . $this->server . '_lock']) ) {
                            $out = FALSE;
                        }
                    }
                }
            }
                    
            if ( !$this->noLock && $out === FALSE ) {
                if ( !isset($locks[$key . $this->server . '_lock']) ) {
                    $setLocks[$key . $this->server . '_lock'] = 1;
                } else {
                    $waiting = TRUE;
                }
            }
                    
            if ( isset($out['__data']) ) {
                $output[$key] = $out['__data'];
            } else {
                $output[$key] = $out;
            }
                    
        }
        
        if ( $waiting && self::$__edwCycles > 0 ) {
            self::$__edwCycles--;
            if(SERVER!=='local') sleep(1);
            $output = $this->gets($keys);
        }
                
        if ( $setLocks ) {
            parent::setMulti($setLocks, 30);
        }
                
        return $output;
    }
        
	
	/**
	 * Пропихивает данные в мемкеш.
	 *
	 * @param string $key			ключ для кеширования
	 * @param string $data			данные
	 * @param integer $data			время жизни данных
	 * @param string|array $tags			Строка с именем тега или массив, если нужно добавить несколько тегов
	 * @return boolean				true - если все ок			
	 */
	public function set($key, $data, $expire = 600, $tags = '') {
        $output = FALSE;
        
        if ( $this->bIsConnected ) {
            
            $key = $key . $this->server;
            $this->_initSetData($data, $expire, $tags);
            $output = parent::set($key, $data, $expire > 0 ? $expire + 900 : 0);
            
        }
            
        if ( $output === FALSE ) {
            $this->_error('set', $key);
        }
            
        return $output;

	}
    
	
    /**
	 * Пропихивает группу данных в мемкеш.
	 *
	 * @param string $datas	        массив с данным
	 * @param integer $data	        время жизни данных
	 * @param string|array $tags    Строка с именем тега или массив, если нужно добавить несколько тегов
	 * @return boolean              true - если все ок			
	 */
    public function sets($datas, $expire = 600, $tags = '') {
        $output = FALSE;
        
        if ( $this->bIsConnected ) {
            
            foreach ( $datas as $key => $data ) {

                $key = $key . $this->server;
                $this->_initSetData($data, $expire, $tags);
                $datas[$key] = $data;
            }
            
            $output = parent::setMulti($datas, $expire > 0 ? $expire + 900 : 0);
            
        }
        
        if ( $output === FALSE ) {
            $this->_error('set', $key);
        }
            
        return $output;
        
    }
    
    
	/**
	 * Инициализирует данные при set-операциях.
	 *
	 * @param array $data			массив с данными
	 * @param integer $expire		время жизни данных
	 * @param string|array $tags			Строка с именем тега или массив, если нужно добавить несколько тегов
	 */
    private function _initSetData(&$data, $expire, $tags) {
        $data = array(
            '__data'   => $data
        );
        
        if($expire) {
            $data['__expire'] = time() + $expire;
        }
    
        if ( $tags ) {
        
            $data['__tags'] = array();
            if ( is_array($tags) ) {
                $_tags = array();
                foreach ( $tags as $tag ) {
                    $_tags[] = '__tag_version_' . $tag . $this->server;
                }
            } else {
                $_tags[] = '__tag_version_' . $tags . $this->server;
            }
        
            $_versions = parent::getMulti($_tags);
        
            foreach ( $_tags as $tag_name ) {
                $tag_version = isset($_versions[$tag_name])? floatval($_versions[$tag_name]): 0;
                $data['__tags'][$tag_name] = $tag_version;
            }
        
        }
    }
	
	/**
	 * Пихает данные в мемкеш. Не пихает, если ключ занят.
	 *
	 * @param string $key			ключ для кеширования
	 * @param array $data			данные
	 * @param integer $expire		время жизни данных
	 * @param string|array $tags	Строка с именем тега или массив, если нужно добавить несколько тегов
	 * @return boolean				true - если все ок			
	 */
	public function add($key, $data, $expire = 600, $tags = '') {
        $output = FALSE;
        if ( $this->bIsConnected ) {
            $key = $key . $this->server;
            $this->_initSetData($data, $expire, $tags);
            $output = parent::add($key, $data, $expire); // + 900);
        }
            
        if ( $output === FALSE ) {
            $this->_error('add', $key);
        }
            
        return $output;

	}
	
	/**
	 * Удаляет запись из кеша по ее коду
	 *
	 * @param string $key	ключ записи
	 * @return boolean				true - если все ок	
	 */
	function delete($key, $time = 0) {
		if ($this->bIsConnected) {
			$output = parent::delete($key.$this->server);
			if(!$output) {
			    $this->_error('delete', $key);
			}
	    }
		return $output;
	}
	
    /**
     * Удаляет группу записей из мемкеша
     * 
     * @deprecated
     * @param string $group		группа записей
     * @return boolean			true - если все ок	
     */
	function _flushGroup($group){
		$items = parent::get($group.$this->server);
		if ($items)
			foreach ($items as $item){
				if (parent::get($item.$this->server)!==false){ $this->delete($item); }
			}
		 if(parent::set($group.$this->server, false, 0) === false) {
		     $this->_error('flushGroup', $group);
		 }
	}
	
	/**
	 * Вычищает весь кеш
	 *
	 */
	function flush($delay = 0){
		parent::flush();
	}

    
	/**
	 * Запрашивает данные из мемкеша. Если не находит ключ, то кеширует результат запроса в
	 * формате pg_fetch_all()
	 *
	 * @param string $error			возвращает сообщение об ошибке при запросе к Постгресу
	 * @param string $sql			запрос к Постгресу
	 * @param integer $expire		время жизни кэша (в секундах)
	 * @param boolean $read_only		запрос только на чтение?
	 * @return array			результат запроса из кэша или базы в формате массива pg_fetch_all()
	 */
	function getSql(&$error, $sql, $expire = 600, $read_only = false, $group = false){
	    $key = md5($sql);
		$output = $this->get($key);
		//print "Buffer";
		if (!$output){
			//print "NoBuffer!";
			$res = pg_query_Ex($sql, $read_only);
			$output = pg_fetch_all($res);
			$this->bWasMqUsed = false;
			$error = pg_errormessage();
			if (!$error){
				$this->set($key,$output, $expire, $group);
			}
		}
		return $output;
	}
    
    
    /**
     * Алиас к Memcached::touchTag(), для совместимости
     * 
     * @see Memcached::touchTag()
     * 
     * @param type $tag_name    Имя тега
     * @return type 
     */
    public function flushGroup($tag_name) {
        return $this->touchTag($tag_name);
    }
 
    /**
     * Обновляет версию тега
     * Связанные с тегом данные перестают быть актуальными.
     * 
     * @param string $tag_name  Имя тега
     * @return type 
     */
    public function touchTag ($tag_name) {
        $new_version = microtime(1);
        return parent::set('__tag_version_' . $tag_name . $this->server, $new_version, 0);
    }
	
    /**
     * Обновляет кеш по ключу и добавляет указанный тег.
     * 
     * @param type $key
     * @param string $tag_name
     * @param type $expire
     * @return type 
     */
    public function addKeyTag ($key, $tag_name, $expire = 600) {
        $tag_name = '__tag_version_' . $tag_name . $this->server;
        
        $items = parent::getMulti(array($key, $tag_name));
        
        if (!$items) {
            $this->_error('addKeyTag');
            return false;
        }
        
        if (!isset($items[$key])) {
            $this->_error('addKeyTag');
            return false;
        }
        
        $item = $items[$key];
        
        if (!is_array($item) || !isset($item['__data']) || !isset($item['__tags'])) {
            $data = array(
                '__data' => $item,
                '__tags' => array(),
            );

            $tag_version = floatval(parent::get($tag_name));
            $data['__tags'][] = array($tag_name => $tag_version);
        } else {
            if (isset($item['__tags'][$tag_name])) {
                $this->_error('addKeyTag');
                return false;
            }
            
            $item['__tags'][] = $tag_name;
            $data = $item;
        }
        
        if (!$data) {
            $this->_error('addKeyTag');
            return false;
        }
        
        $output = parent::set($key, $data, $expire);
        
        if ($output === false) {
            $this->_error('set');
        }
        return $output;
    }
    
    /**
     * Обновляет кеш по ключу и удаляет указанный тег.
     * 
     * @param type $key
     * @param string $tag_name
     * @param type $expire
     * @return type 
     */
    public function dropKeyTag ($key, $tag_name, $expire = 600) {
        $tag_name = '__tag_version_' . $tag_name . $this->server;
        
        $item = parent::get($key);
        
        if (!$item) {
            $this->_error('dropKeyTag');
            return false;
        }
        
        if (!is_array($item) || !isset($item['__data']) || !isset($item['__tags'])) {
            $this->_error('dropKeyTag');
            return false;
        }
        
        if (isset($item['__tags'][$tag_name])) {
            unset($item['__tags'][$tag_name]);
        } else {
            $this->_error('dropKeyTag');
            return false;
        }
        
        $output = parent::set($key, $data, $expire);
        
        if ($output === false) {
            $this->_error('set');
        }
        return $output;
    }


    private function _error($optype = NULL, $key = NULL) {
	    if(!$this->_log->linePrefix) {
    		$this->_log->linePrefix = '%d.%m.%Y %H:%M:%S - ' . getRemoteIP()
    		                        . ' - "'
    		                        . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']
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
	    $this->_log->writeln("[error: {$rcode}, method: {$optype}, key: {$key}, time: {$ttime}] {$rmsg}");
	}

}
?>