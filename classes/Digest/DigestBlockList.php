<?php

require_once 'DigestBlock.php';

/**
 * Класс для работы с блоком со ссылками
 */
class DigestBlockList extends DigestBlock {
    
    /**
     * Доступна ли кнопка автозаполнения ссылок
     * 
     * @var boolean 
     */
    const AUTO_COMPLETE = false;
    
    /**
     * Маска валидации и проверки ссылки
     * 
     */
    const MASK_LINK = '';
    
    /**
     * Количество ссылок
     * 
     * @var integer 
     */
    protected $_list_size = 3;
    
    /**
     * При создании ссылки больше чем инициированное количество, последняя ссылка замещается
     * @var type 
     */
    protected $_is_replace    = true;
    
    /**
     * Название заголовка полей блока
     * 
     * @var string
     */
    public $title_field = "Ссылки";
    
    /**
     * Подсказка к полю
     * 
     * @var string 
     */
    public $hint  = '';
    
    /**
     * Инициализированный ссылки блока
     * 
     * @var array
     */
    public $links = array();
    
    /**
     * Связь ссылок с распарсенными данными
     * 
     * @var array 
     */
    public $linked = array();
    
    /**
     * Конструктор позволяет задать ссылки и количество ссылок блока
     * 
     * @param integer $size    Количество ссылок блока
     * @param mixed   $link    Ссылка(и)
     */
    public function __construct($size = null, $link = null) {
        if($size !== null) {
            $this->setListSize($size);
        }
        if($link !== null) {
            $this->initBlock($link);
        }
    }
    
    /**
     * Задает количество ссылок в блоке
     * 
     * @param integer $size
     */
    public function setListSize($size) {
        $this->_list_size = (int) $size;
    }
    
    /**
     * Возвращает допустимое количество ссылок в блоке
     * 
     * @return integer
     */
    public function getListSize() {
        return $this->_list_size;
    }
    
    /**
     * Проверка на разрешение замешать последнюю ссылку если блок уже заполнен
     * 
     * @return boolean
     */
    public function isReplace() {
        return $this->_is_replace;
    }
    
    /**
     * Инициализация данных блока
     * 
     * @param mixed $link       Ссылки
     */
    public function initBlock($link = null) {
        if(is_array($link)) {
            $link = array_map('stripslashes', $link);
            if(count($link) > $this->getListSize()) {
                $link = current(array_chunk($link, $this->getListSize()));
            }
            $this->links = $link;
        } elseif(count($this->links) < $this->getListSize()) {
            $link = stripslashes($link);
            array_push($this->links, $link);
        } elseif($this->isReplace()) {
            $link = stripslashes($link);
            array_pop($this->links); // Выталкиваем последний
            array_push($this->links, $link);
        }
    }
    
    /**
     * Отображение блока
     */
    public function displayBlock() {
        include ($_SERVER['DOCUMENT_ROOT'] . self::TEMPLATE_PATH . "/tpl.digest_list.php");
    }
    
    /**
     * Проверка на возможность автозаполения блока
     * 
     * @return boolean
     */
    public function isAutoComplete() {
        return constant(get_class($this) . '::AUTO_COMPLETE');
        //return $this::AUTO_COMPLETE;
    }
    
    /**
     * Задаем название заголовка полей блока
     * 
     * @param string $title
     */
    public function setTitleField($title) {
        if(func_num_args() > 1) {
            $args = func_get_args();
            array_shift($args);
            $this->title_field = vsprintf($title, $args);
        } else {
            $this->title_field = $title;
        }
    }
    
    /**
     * Возвращает название заголовка полей блока
     * 
     * @return string
     */
    public function getTitleField() {
        return $this->title_field;
    }
    
    /**
     * Инициализация блока
     * 
     * @param array $data
     */
    public function initialize($data) {
        $class = $this->__toString();
        
        $this->setPosition($data['position'][$class]);
        $this->setListSize(count($data[$class.'Link']));
        $this->_check = ($data[$class.'Check'] == 1);
        $this->initBlock($data[$class.'Link']);
        $this->parseLinks();
    }
    
    /**
     * Функция для автозаполнения полей
     * 
     * @return boolean
     */
    public function setFieldAutoComplete() {
        return false;
    }
    
    /**
     * Разбираем введенные ссылки
     * 
     * @return array
     */
    public function parseLinks() {
        $parse = array();
        foreach($this->links as $i=>$link) {
            if($link == '') continue;
            if(preg_match(constant(get_class($this) . '::MASK_LINK'), $link, $match)) {
                $parse[] = stripslashes(__paramValue('string', $match[1]));
                $this->linked[$match[1]] = $i;
            } else {
                $this->_error[$i] = 'Ссылка не валидна';
            }
        }
        
        return $parse;
    }
    
    /**
     * Выдаем ссылку по разобранным данным
     * 
     * @param mixed $id  Ид данных
     * @return string Связанная ссылка
     */
    public function getLinkById($id) {
        return $this->links[$this->linked[$id]];
    }
}