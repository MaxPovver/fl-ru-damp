<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php
class EventController {
	const EVENT_OPERATOR_STATUS = "status";
	const EVENT_OPERATOR_PING = "operator_ping";
	
	protected $listeners;
	protected static $instance;
	
	protected function __construct() {
		$this->listeners = array();
	}
	
	public function getInstance() {
		if(self::$instance === null) {
			$class_name = __CLASS__;
			self::$instance = new $class_name();	
		}
		
		return self::$instance;
	}
	
	public function addEventListener($event, $listener) {

		
		if(!is_callable($listener)) {

			return false;
		}
		
		if(!isset($this->listeners[$event])) {
			$this->listeners[$event] = array();
		}
		
		$this->listeners[$event][] = $listener;

		return true;
	}
	
	public function dispatchEvent($event, $params = array()) {
		if(!isset($this->listeners[$event])) {

			return;
		}
		
		
		foreach($this->listeners[$event] as $listener) {

	
			call_user_func_array($listener, $params);
		}
	}
}
?>