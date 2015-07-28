<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/template.php';

/**
 * Class SubBarNotificationHelper
 * 
 * Хелпер уведомлений под панелью меню
 */
class SubBarNotificationHelper 
{
    
    protected static $instance;
    
    protected $template = '/templates/helpers/subbar-notification.tpl.php';

    
    const MEMCACHE_TTL = 86400;
    const MEMCACHE_KEY_PREFIX = "SubBarNotificationHelperUserID%d";
    
    //Храним сообщение в мемкеше
    protected $_membuff = NULL;
    
    //Храним с переменной на время работы скрипта
    protected $_message = NULL;


    protected $_is_show = false;

    


    /**
     * Виды уведомлений
     */
    const TYPE_TEXT                 = 0;
    const TYPE_GUEST_NEW_ORDER      = 10;
    const TYPE_GUEST_NEW_PROJECT    = 20;
    const TYPE_GUEST_NEW_VACANCY    = 25;
    const TYPE_RESERVE_PROMO        = 30;
    const TYPE_USER_ACTIVATED       = 50;
    
    protected $types = array(
        self::TYPE_TEXT,
        self::TYPE_GUEST_NEW_ORDER,
        self::TYPE_GUEST_NEW_PROJECT,
        self::TYPE_GUEST_NEW_VACANCY,
        self::TYPE_RESERVE_PROMO,
        self::TYPE_USER_ACTIVATED
    );


    
    public function __construct() 
    {
        if (!$this->_membuff) {
            $this->_membuff = new memBuff();
        }
    }
    

    public function isShow()
    {
        return $this->_is_show;
    }

    
    public function setIsShow($set = true)
    {
        $this->_is_show = $set;
    }
    
    
    public function setNowMessage($type, $data = array())
    {
        $data['type'] = $type;
        $this->_message = $data;
        return true;
    }


    public function setMessageText($text, $data = array(), $user_id = null)
    {
        $_data['text'] = vsprintf($text, $data);
        return $this->setMessage(self::TYPE_TEXT, $_data, $user_id);
    }


    public function setMessage($type, $data, $user_id = null)
    {
        if (!in_array($type, $this->types)) {
            return false;
        }
        
        $data['type'] = $type;
        $key = $this->getMemcacheKeyPrefix($user_id);
        
        if ($key) {
            $result = $this->_membuff->get($key);
            $result = (!empty($result))?$result:array();
            $result[] = $data;
            $this->_membuff->set($key, $result, self::MEMCACHE_TTL);
            return true;
        }
        
        return false;
    }
    


    public function showMessage($user_id = null)
    {
        $html = '';
        $key = $this->getMemcacheKeyPrefix($user_id);
        
        //Показать отложенное сообщение из мемкеша
        if ($key) {
            $result = $this->_membuff->get($key);
            if (!empty($result)) {
                $this->_is_show = true;
                $idx = key($result);
                $data = $result[$idx];
                $html = Template::render(ABS_PATH . $this->template, $data);
                unset($result[$idx]);
                if (empty($result)) {
                    $this->_membuff->delete($key);
                } else {
                    $this->_membuff->set($key, $result, self::MEMCACHE_TTL);
                }
            }
        }
        
        //Показать сообщение в течении жизни скрипта
        if (empty($html) && $this->_message) {
            $this->_is_show = true;
            $html = Template::render(ABS_PATH . $this->template, $this->_message);
        }
        
        return $html;
    }

    

    protected function getMemcacheKeyPrefix($user_id = null)
    {
        $user_id = ($user_id)?$user_id:get_uid(false);
        if (!$user_id) {
            return false;
        }
        return sprintf(self::MEMCACHE_KEY_PREFIX, $user_id);
    }

    
    /**
    * Создаем синглтон
    * @return object
    */
    public static function getInstance() 
    {

        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }
}