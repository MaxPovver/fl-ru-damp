<?php

require_once 'DigestBlockList.php';

/**
 * Класс для работы с блоком "Обсуждают в сообществах"
 */
class DigestBlockListCommune extends DigestBlockList {
    
    /**
     * @see parent::ADD_FIELD
     */
    const ADD_FIELD = true;
    
    /**
     * @see parent::MASK_LINK
     */
    const MASK_LINK = '~commune\/.*?/\d+?\/.*?\/(\d+)?\/~mix';
    
    /**
     * @see parent::$title
     * @var string 
     */
    public $title = 'Обсуждают в <a class="b-layout__link" href="/commune/" target="_blank">сообществах</a>';
    
    /**
     * @see parent::$hint
     * @var string 
     */
    public $hint  = 'Например: https://www.free-lance.ru/commune/drugoe/411/example/31837/example.html';
    
    /**
     * @see parent::$title_field
     * @var string 
     */
    public $title_field = 'Ссылки на сообщества:';
    
    /**
     * @see parent::initHtmlData
     */
    public function initHtmlData() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/commune.php';
        $ids = $this->parseLinks();
        if($ids) {
            $this->html_data = commune::getCommunePostByIds(array_map("intval", $ids));
        }
    }
}