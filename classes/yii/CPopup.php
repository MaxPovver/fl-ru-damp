<?php

require_once(ABS_PATH . '/classes/template.php');

abstract class CPopup
{
    /**
     * Обший шаблон попапа
     */
    const DEFAULT_LAYOUT  = '/templates/popup/default_layout.tpl.php';
    
    
    /**
     * Уникальный иднтификатор попапа
     * 
     * @var type 
     */
    protected $id = '';
    
    
    /**
     * Текущий шаблон попапа
     * 
     * @var type 
     */
    protected $layout_tpl;
    
    
    /**
     * Шаблон контентной области попапа
     * 
     * @var type 
     */
    //protected $popup_tpl = null;



    protected $is_ajax = false;


    /**
     * Отключить обший шаблон попапа 
     * и выводить только контентную область
     * 
     * @var type 
     */
    protected $disableLayout = false;





    /**
     * Настройки попапа
     * 
     * @var type 
     */
    protected $options = array();
    
    
    
    public function __construct($params = array()) 
    {
        $this->is_ajax = isset($params['is_ajax']) && $params['is_ajax'];
        
        if ($this->is_ajax) {
            $this->setDisableLayout(true);
        } else {
            $this->initMainJs();            
        }
        
        $this->layout_tpl = static::DEFAULT_LAYOUT;
        
        $class_name = get_called_class();
        $this->id = lcfirst($class_name);
        
        $this->options['popup_title_class_bg']      = 'b-fon_bg_po';
        $this->options['popup_title_class_icon']    = 'b-icon__po';
        $this->options['popup_id'] = $this->id;
        $this->options['popup_width'] = 520;
        
        $options = $this->init($params);
        $this->options = array_merge($this->options, $options);
    }
    
    
    
    /**
     * Пользоватеьская инициализация
     */
    abstract protected function init($params);

    

    /**
     * Базовые скрипты попапа
     * 
     * @global array $js_file
     */
    protected function initMainJs()
    {
        global $js_file;
        $js_file['popup/cpopup'] = 'popup/cpopup.js';
        $js_file = array_merge($js_file, $this->initJS());
    }
    

    /**
     * Список пользовательских скриптов 
     * в наследуемом классе
     * 
     * @return type
     */
    protected function initJS()
    {
        return array();
    }
    
    
    public function setContent($html)
    {
        $this->options['content'] = $html;
    }    
    
    
    public function setDisableLayout($disable = true)
    {
        $this->disableLayout = $disable;
    }
    

    public function render($options = array())
    {
        $this->options = array_merge($this->options, $options);
        
        if ($this->disableLayout) {
            $html = $this->options['content'];
        } else {
            $html = Template::render(ABS_PATH . $this->layout_tpl, $this->options);
        }
        
        return $html;
    }
    
    
    
    public function getPopupId()
    {
        return $this->id;
    }
    
    
    /**
     * Создаем синглтон
     * @return object
     */
    final public static function getInstance($options = array())
    {
        static $instances = array();

        $calledClass = get_called_class();

        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass($options);
        }

        return $instances[$calledClass];
    }  
    
}