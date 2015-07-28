<?php

require_once 'DigestBlockList.php';

/**
 * Класс для работы с блоком "Интервью"
 */
class DigestBlockListInterview extends DigestBlockList {
    
    /**
     * Возможность добавлять дополнительные поля
     * 
     * @var boolean 
     */
    const ADD_FIELD = true;
    
    /**
     * Маска валидации и проверки ссылки
     * 
     * @var string 
     */
    const MASK_LINK = '~interview\/(\d+)\/~mix';
    
    /**
     * @see parent::$title
     * @var string 
     */
    public $title = '<a class="b-layout__link" href="/interview/" target="_blank">Интервью</a>';
    
    /**
     * @see parent::$hint
     * @var string 
     */
    public $hint  = 'Например: https://www.free-lance.ru/interview/100/example.html';
    
    /**
     * @see parent::$title_field
     * @var string 
     */
    public $title_field = 'Ссылки на интервью:';
    
    
    /**
     * @see parent::initHtmlData
     */
    public function initHtmlData() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/interview.php';
        
        $ids = $this->parseLinks();
        if($ids) {
            $this->html_data = interview::getInterviewById(array_map("intval", $ids));
        }
    }
}