<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Кеширование произвольной информации через MemCache
 *
 */
class memBuff extends Memcache
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
	 * Конструктор. Подключается к серваку мемкэша
	 */
	function __construct() {
		if (sizeof($GLOBALS['memcachedServers']) == 1){
		    $this->bIsConnected = $this->connect($GLOBALS['memcachedServers'][0], 11211);
	    } elseif (sizeof($GLOBALS['memcachedServers']) > 1) {
	        foreach ($GLOBALS['memcachedServers'] as $server){
	            $this->bIsConnected = $this->addServer($server);
	        }
	    }
	    else die("Не найдены сервера Memcache");
	    $this->server = (defined('SERVER')?SERVER:'');
        $this->setOption(Memcached::OPT_COMPRESSION, false);
	}
	
	/**
	 * Деструктор. Отключается от сервака мемкэша
	 */
	function __destruct() {
		if ($this->bIsConnected) $this->close();
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
	 * @param string $key			ключ для поиска
	 * @return array			результат. false, если не найдено
	 */
	function get($key){
		if ($this->bIsConnected)
			$output = parent::get($key.$this->server);
		return $output;
	}
	
	/**
	 * Запрашивает данные из мемкеша. Если не находит ключ, то кеширует результат запроса в
	 * формате pg_fetch_all()
	 *
	 * @param string $error			возвращает сообщение об ошибке при запросе к Постгресу
	 * @param string $sql			запрос к Постгресу
	 * @param integer $expire		время жизни кэша (в секундах)
	 * @param boolean $read_only	запрос только на чтение?
	 * @param mixed $group			имя группы, false если без группы
	 * @return array			результат запроса из кэша или базы в формате массива pg_fetch_all()
	 */
	function getSql(&$error, $sql, $expire = 600, $read_only = false, $group = false){
		$output = $this->get(md5($sql));
		//print "Buffer";
		if (!$output){
			//print "NoBuffer!";
			$res = pg_query_Ex($sql, $read_only);
			$output = pg_fetch_all($res);
			$this->bWasMqUsed = false;
			$error = pg_errormessage();
			if (!$error){
				$this->set(md5($sql),$output, $expire, $group);
			}
		}
		return $output;
	}
	
	/**
	 * Пихает данные в мемкеш.
	 *
	 * @param string $key			ключ для кеширования
	 * @param string $data			данные
	 * @param integer $data			время жизни данных
	 * @param string $data			группа для данных (false - не заморачиваться)
	 * @return boolean				true - если все ок			
	 */
	function set($key, $data, $expire = 600, $group = false){
//print "Buffer_SET!";
		if ($this->bIsConnected)
			$output = parent::set($key.$this->server,$data, false, $expire);
		if ($group){
			$items = $this->get($group);
			$items[] = $key;
			parent::set($group.$this->server,$items, false, 0);
		}
		return $output;
	}
	
	/**
	 * Удаляет запись из кеша по ее коду
	 *
	 * @param string $key	ключ записи
	 * @return boolean				true - если все ок	
	 */
	function delete($key) {
		if ($this->bIsConnected)
			$output = parent::delete($key.$this->server);
		return $output;
	}
	
	/**
	 * Удаляет группу записей из мемкеша
	 *
	 * @param string $group		группа записей
	 * @return boolean			true - если все ок	
	 */
	function flushGroup($group){
		$items = $this->get($group);
		if ($items)
			foreach ($items as $item){
				if ($this->get($item)!==false)	$this->delete($item);
			}
		 parent::set($group.$this->server, false, 0);
	}
	
	function touchTag() {}
	
	/**
	 * Вычищает весь кеш
	 *
	 */
	function flush(){
		parent::flush();
	}

}
