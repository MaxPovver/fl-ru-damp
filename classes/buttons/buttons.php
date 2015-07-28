<?php

/**
 * Класс для работы с кнопками
 *  
 */
class buttons
{
    /**
     * Шаблон одной кнопки
     * 
     * @var string 
     */
    public $TEMPLATE       = 'tpl.button.php';
    
    /**
     * Название кнопки по умолчанию
     * 
     * @var string 
     */
    public $name           = 'Button';
    
    /**
     * Аббревиатура кнопки
     * 
     * @var string 
     */
    public $abbr           = 'button';
    
    /**
     * Настройки кнопки по умолчанию
     * 
     * @var array 
     */
    public $options = array('link'  => 'javascript:void(0)',     
                            'color' => '',
                            'css'   => '', 
                            'event' => array());
    
    /**
     * Конструктор класса
     * 
     * @param string $name Название кнопки 
     */
    public function __construct($name = null, $color = null, $abbr = null) {
        $this->setColor($color);
        if($name) $this->setName($name);
        if($abbr) $this->setAbbr($abbr);
    }
    
    /**
     * Обрисовка кнопки
     * 
     * @return string HTML-код кнопки 
     */
    public function draw() {
        ob_start();
        include $this->TEMPLATE;
        $result = ob_get_clean();
        return $result;
    }
    
    /**
     * Показываем кнопку 
     */
    public function view() {
        print $this->draw();
    }
    
    /**
     * Задать CSS для кнопки
     * 
     * @param string  $css        Название класса/классов
     * @param boolean $rewrite    Переписать опцию или нет
     */
    public function setCss($css, $rewrite = false) {
        if($rewrite) {
            $this->options['css'] = $css;
        } else {
            $this->options['css'] .= ' '.$css;
        }
    }
    
    /**
     * Возвращает CSS кнопки
     * 
     * @return string
     */
    public function getCss() {
        return $this->options['css'];
    }
    
    /**
     * Задать цвет кнопки
     * 
     * @param string $color Цвет кнопки @see self::getColorMain(); 
     */
    public function setColor($color = '') {
        $this->options['color'] = $color;
    }
    
    /**
     * Возвращает цвет кнопки
     * 
     * @return string 
     */
    public function getColor() {
        return $this->options['color'];
    }
    
    /**
     * Возвращает класс для кнопки по названию цвета кнопки
     * 
     * @param string $color   Цвет кнопки
     * @return string 
     */
    public function getColorMain($color = null) {
        if(!$color) $color = $this->getColor();
        switch($color) {
            case 'red':
                return 'b-button_flat_red';
                break;
            case 'green':
            default:    
                return 'b-button_flat_green';
                break;
        }
    }
    
    /**
     * Добавляет событие нажатия кнопки
     * 
     * @param string $event Событие нажатия кнопки (js - onclick)
     */
    public function addEvent($name, $event) {
        $this->options['event'][$name] = $event;
    }
    
    /**
     * Возвращает событие нажатия кнопки
     * 
     * @return string
     */
    public function getEvent($name) {
        return $this->options['event'][$name];
    }
    
    /**
     * Добавляет ссылку кнопки 
     * 
     * @param string $link    Ссылка кнопки
     */
    public function setLink($link) {
        $this->options['link'] = $link;
    }
    
    /**
     * Возвращает ссылку кнопки
     * 
     * @return string 
     */
    public function getLink() {
        return $this->options['link'];
    }
    
    /**
     * Задает название пноки
     * 
     * @param string $name Название кнопки 
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * Возвращает название кнопки
     * 
     * @return string 
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Генерирует все события на кнопку
     * 
     * @return string 
     */
    public function getEvents() {
        $string = '';
        foreach($this->options['event'] as $event_name => $event) {
            $string .= $event_name."=\"{$event}\" ";
        }
        return $string;
    }
    
    /**
     * Задаем абревиатуру кнопки (идентификатор кнопки)
     * 
     * @param string $abbr 
     */
    public function setAbbr($abbr) {
        $this->abbr = $abbr;
    }
    
    /**
     * Возвращаем абревиатуру кнопки
     * 
     * @return string 
     */
    public function getAbbr() {
        return $this->abbr;
    }
    
    /**
     * Возвращает название кнопки
     * 
     * @return string
     */
    public function __toString() {
        return $this->name;
    }  
}


?>