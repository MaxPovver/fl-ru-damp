<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/template.php';


/**
 * Class PopupAfterPageLoaded
 * 
 * Показывает попап после загрузки страницы
 */
class PopupAfterPageLoaded 
{
    protected static $instance;
    
    protected $template = '/templates/helpers/popup-after-page-loaded.tpl.php';
    
    const KEY_PREFIX = 'PopupAfterPageLoaded';
    
    
    
    public function showAfterLoad(Array $data)
    {
        $_SESSION[self::KEY_PREFIX] = $data;
    }



    public function render()
    {
        $html = '';
        
        if (isset($_SESSION[self::KEY_PREFIX])) {
            $data = $_SESSION[self::KEY_PREFIX];
            $html = Template::render(ABS_PATH . $this->template, $data);
            unset($_SESSION[self::KEY_PREFIX]);
        }
        
        return $html;
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