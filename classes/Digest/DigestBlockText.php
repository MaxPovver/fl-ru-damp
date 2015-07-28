<?php

require_once 'DigestBlock.php';

/**
 * Класс для работы с блоком "Гланый блок"
 */
class DigestBlockText extends DigestBlock {
    
    /**
     * @see parent::$title
     */
    public $title   = 'Главный блок';
    
    /**
     * @see parent::$created
     */
    const IS_CREATED = true;
    
    /**
     * Использовать визивиг или нет
     */
    const IS_WYSIWYG = true;
    
    /**
     * Название
     * @var string 
     */
    public $name;
    
    /**
     * Ссылка
     * 
     * @var string 
     */
    public $link;
    
    /**
     * Описание
     * 
     * @var string
     */
    public $text;
    
    /**
     * Конструктор класса
     * 
     * @param string $name    @see self::$name
     * @param string $link    @see self::$link
     * @param string $text    @see self::$text
     */
    public function __construct($name = null, $link = null, $text = null) {
        if($name !== null && $link !== null && $text !== null) {
            $this->initBlock($name, $link, $text);
        }
    }
    
    /**
     * Инициализация блока
     * 
     * @param string $name    @see self::$name
     * @param string $link    @see self::$name
     * @param string $text    @see self::$name
     */
    public function initBlock($name = null, $link = null, $text = null) {
        $this->name = stripcslashes(__paramValue('string', $name));
        $this->link = stripslashes(__paramValue('string', $link));
        $this->text = stripcslashes(__paramValue($this->isWysiwyg() ? 'ckeditor' : 'html', $text));
        
        if(!$this->validateLink()) {
            $this->_error['link'] = true;
        }
    }
    
    /**
     * Проверяем введенную ссылку на валидность
     * 
     * @return boolean
     */
    public function validateLink() {
        if($this->link == '') return true;
        return url_validate($this->link, true);
    }
    
    /**
     * Отображение блока
     */
    public function displayBlock() {
        include ($_SERVER['DOCUMENT_ROOT'] . self::TEMPLATE_PATH . "/tpl.digest_text.php");
    }
    
    /**
     * Если блоком может быть несколько, имена input должны быть массивами
     * Выдает модификатор имени input
     * 
     * @return string
     */
    public function isMore() {
        return $this->isCreated() ? "[]" : "";
    }
    
    /**
     * Инициализация блока
     * 
     * @param array $data
     */
    public function initialize($data) {
        $class = $this->__toString();
        
        $this->setMain( $data[$class.'Main'][$this->getNum()] == 1 );
        $this->setPosition( $data['position'][$class][$this->getNum()] );
        $this->setCheck( isset($data[$class.'Check']) ? ($data[$class.'Check'][$this->getNum()] == 1) : false );
        $this->initBlock( $data[$class.'Name'][$this->getNum()], $data[$class.'Link'][$this->getNum()], $data[$class.'Descr'][$this->getNum()]);
    }
    
    /**
     * Выдает HTML блок
     * 
     * @return string
     */
    public function htmlBlock() {
        $this->host = $GLOBALS['host'];
        $this->html_data = $this->name . $this->text;
        if(!$this->html_data) return ''; 
        include ($_SERVER['DOCUMENT_ROOT'] . self::TEMPLATE_PATH . "/tpl." . __CLASS__ . ".php");
    }
    
    /**
     * Проверка на использователие визивиг редактора при создании блока
     * 
     * @return boolean
     */
    public function isWysiwyg() {
        return constant(get_class($this) . '::IS_WYSIWYG');
        //return $this::IS_WYSIWYG;//Начиная с версии 5.3.0
    }
}