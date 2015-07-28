<?php

/**
 * Класс для работы с блоком дайджеста
 * 
 */
class DigestBlock {
    
    
    /**
     * Путь к шаблонам
     */
    const TEMPLATE_PATH = '/siteadmin/mailer/digest';
    
    /**
     * Возможность добавлять дополнительный блок
     * 
     * @var boolean 
     */
    const IS_CREATED = false;
    
    /**
     * Возможность добавлять дополнительные поля
     * 
     * @var boolean 
     */
    const ADD_FIELD = false;
    
    /**
     * Номер блока (необходимо для идентификации если их несколько)
     * 
     * @var integer
     */
    protected $num = 0;
    
    /**
     * Позиция блока
     * 
     * @var integer 
     */
    protected $_position = 0;
    
    /**
     * Выбран блок для отображения или нет
     * 
     * @var boolean
     */
    protected $_check    = false;
    
    /**
     * Главный блок или нет
     * 
     * @var boolean 
     */
    protected $is_main   = true;
    
    /**
     * Ошибка при записи данных в блок
     * 
     * @var mixed 
     */
    protected $_error    = false;
    
    /**
     * Данные для отображения блока в HTML
     * 
     * @var mixed
     */
    public $html_data = false;
    
    /**
     * Заголовок блока
     * 
     * @var string
     */
    public $title = "Блок";
    
    /**
     * Инициализация данных блока
     */
    public function initBlock() {}
    
    /**
     * Отображения блока
     */
    public function displayBlock() {}
    
    /**
     * Инициализация блока
     */
    public function initialize() {}
    
    /**
     * Название класса
     * 
     * @return string
     */
    public function __toString() {
        return get_class($this);
    }
    
    /**
     * Задать номер блока
     * 
     * @param integer $num
     */
    public function setNum($num) {
        $this->num = $num;
    }
    
    /**
     * Возвращает номер блока
     * 
     * @return integer
     */
    public function getNum() {
        return $this->num;
    }
    
    /**
     * Увеличить позицию блока
     */
    public function setUpPosition() {
        $this->_position++;
    }
    
    /**
     * Уменьшить позицию блока
     */
    public function setDownPosition() {
        if($this->_position > 0) {
            $this->_position--;
        }
    }
    
    /**
     * Задаем позицию блоку
     * 
     * @param integer $pos
     */
    public function setPosition($pos) {
        $this->_position = $pos;
    }
    
    /**
     * Текущая позиция блока
     * @return type
     */
    public function getPosition() {
        return $this->_position;
    }
    
    /**
     * Есть ли возможность создавать новые блоки
     * 
     * @return boolean
     */
    public function isCreated() {
        return constant(get_class($this) . '::IS_CREATED');
        //return $this::IS_CREATED; // начиная с версии 5.3.0
    }
    
    /**
     * Задаем отображение блока в HTML
     * 
     * @param boolean $bool
     */
    public function setCheck($bool) {
        $this->_check = $bool;
    }
    
    /**
     * Отображать или нет блок в HTMl
     * 
     * @return boolean
     */
    public function isCheck() {
        return $this->_check;
    }
    
    /**
     * Есть ли возможность создавать новые поля
     * 
     * @return boolean
     */
    public function isAdditionFields() {
        return constant(get_class($this) . '::ADD_FIELD');
        //return $this::ADD_FIELD; // начиная с версии 5.3.0
    }
    
    /**
     * Блок является главным или нет
     * 
     * @return boolean
     */
    public function isMain() {
        return $this->is_main;
    }
    
    /**
     * Задаем параметр блока, главный блок или нет
     * 
     * @param boolean $bool
     */
    public function setMain($bool) {
        $this->is_main = $bool;
    }
    
    /**
     * Задаем заголовок блока
     * 
     * @param string $title
     * @param ...
     */
    public function setTitle($title) {
        if(func_num_args() > 1) {
            $args = func_get_args();
            array_shift($args);
            $this->title = vsprintf($title, $args);
        } else {
            $this->title = $title;
        }
    }
    
    /**
     * Заголовок блока
     * 
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * Ошибка при инициализации блока или нет
     * 
     * @return boolean
     */
    public function isError() {
        return ($this->_error != false);
    }
    
    /**
     * Инициализирует данные для HTML 
     */
    public function initHtmlData() { }
    
    /**
     * Выдает HTML блок
     * 
     * @return string
     */
    public function htmlBlock() {
        $this->host = $GLOBALS['host'];
        $this->initHtmlData();
        if(!$this->html_data) return ''; // Данных для блока нет
        include ($_SERVER['DOCUMENT_ROOT'] . self::TEMPLATE_PATH . "/tpl.{$this->__toString()}.php");
    }
    
    public function isWysiwyg() {
        return false;
    }
}