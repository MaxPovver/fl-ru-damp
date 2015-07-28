<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/buttons/buttons.php");

/**
 * Класс для работы с набором кнопок
 */
class multi_buttons extends buttons 
{
     /**
     * Шаблон набора кнопок
     * 
     * @var string 
     */
    public $TEMPLATE = 'tpl.button-multi.php';
    
    /**
     * Набор кнопок
     * @var array 
     */
    public $buttons  = array();
    
    /**
     * Отрисовка кнопок, если кнопка 1 то выводим как одну, 
     * иначе определяем главную кнопку (первую в списке), 
     * и выводим как мульти кнопку
     * 
     * @return string HTML-код 
     */
    public function draw() {
        if(count($this->buttons) == 0) return;
        if(count($this->buttons) == 1) {
            reset($this->buttons);
            $button = current($this->buttons);
            if(!is_object($button)) return false;
            return $button->draw();
        }
        $this->setMainButton();
        return parent::draw();
    }
    
    /**
     * Инициируем главную кнопку 
     */
    public function setMainButton() {
        $this->main = array_shift($this->buttons);
    }
    
    /**
     * Добавление кнопок в набор
     * 
     * @param buttons $button кнопка
     */
    public function addButton(buttons $button) {
        array_push($this->buttons, $button);
    }
    
    /**
     * Возвращает класс для кнопки по названию цвета кнопки
     * 
     * @param string $color   Цвет кнопки
     * @return string 
     */
    public function getColorMain($color = null) {
        if(!$color) $color = $this->main->getColor();
        switch($color) {
            case 'red':
                return 'b-button-multi__item_red';
                break;
            case 'green':
            default:    
                return 'b-button-multi__item_green';
                break;
        }
    }
    
    /**
     * Возвращает класс для доп кнопок по названию цвета кнопки (кнопки в выпадающем меню)
     * 
     * @param string $color   Цвет кнопки
     * @return string 
     */
    public function getColorLink($color = null) {
        switch($color) {
            case 'red':
                return 'b-layout__link_dot_c7271e';
                break;
            case 'green':
            default:    
                return 'b-layout__link_bordbot_dot_0f71c8';
                break;
        }
    }
    
    /**
     * Определяем есть ли в наборе кнопка с абревиатурой
     * 
     * @param string $abbr 
     */
    public function isButton($abbr) {
        if($this->main) {
            if($this->main->getAbbr() == $abbr) return true;
        }
        foreach($this->buttons as $button) {
            if($button->getAbbr() == $abbr) return true;
        }
        
        return false;
    }
    
    public function removeButton(buttons $button) {
        foreach($this->buttons as $k=>$btn) {
            if($button == $btn) {
                unset($this->buttons[$k]); 
                break;
            }
        }
    }
}

?>