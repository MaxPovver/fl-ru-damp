<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sms_gate_a1.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
			
/**
 * Класс для работы с привязкой телефона пользователем
 */

class user_phone {
	
	/**
     * @var cached reference to singleton instance 
     */
    protected static $instance;
	
	protected $_allow = null;
	
	public $_place;
	
	/**
	 * Есть ли на странице кнопка в шапке сайта. Используется для отступа контента
	 * 
	 * @var boolean
	 */
	public $_use_header = false;


	const PLACE_HEADER = 'header';
	const PLACE_TSERVICE = 'tservice';
	const PLACE_PROJECTS = 'projects';

	/**
     * gets the instance via lazy initialization (created on first usage)
     *
     * @return self
     */
    public static function getInstance()
    {
        
        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * is not allowed to call from outside: private!
     *
     */
    private function __construct() 
    {
		
	}

    /**
     * prevent the instance from being cloned
     *
     * @return void
     */
    private function __clone()
    {

    }

    /**
     * prevent from being unserialized
     *
     * @return void
     */
    private function __wakeup()
    {

    }
	
	/**
	 * Определяет, нужно ли показывать привязку телефона
	 * Показываем уведомление только авторизованным фрилансерам, у которых не привязан номер телефона
	 * 
	 * @return true/false
	 */
	public function checkAllow() {
		if ($this->_allow === null) {
			$uid = get_uid(false);
			if ($uid && !is_emp()) {
				$reqv = sbr_meta::getUserReqvs($uid);
				if ($reqv['is_activate_mob'] != 't') {
					$this->_allow = true;
					return $this->_allow;
				}
			}
			$this->_allow = false;
		}		
		return $this->_allow;
	}
	
	public function render($place = self::PLACE_HEADER) {
		
		$this->_place = $place;
		if ($place == self::PLACE_HEADER) {
			$this->_use_header = true;
		}
		
		if ($this->checkAllow()) {
			$this->addJS();
			
			$inlineTemplate = 'place_'.$place;
			$inlineHtml = Template::render(ABS_PATH . '/templates/user_phone/'.$inlineTemplate.'.php');
			
			return $inlineHtml;
		}
	}
	
	public function renderPopup() {
		if ($this->checkAllow()) {
			$popupHtml = Template::render(ABS_PATH . '/templates/user_phone/popup.php');
			return $popupHtml;
		}
	}
	
	protected function addJS() {
		global $js_file;
		$js_file[] = '/scripts/b-combo/b-combo-phonecodes.js';
		$js_file[] = '/scripts/user-phone.js';
	}
	
}

