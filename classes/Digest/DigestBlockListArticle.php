<?php

require_once 'DigestBlockList.php';

/**
 * Класс для работы с блоком "Статьи"
 */
class DigestBlockListArticle extends DigestBlockList {
    
    /**
     * @see parent::ADD_FIELD
     */
    const ADD_FIELD = true;
    
    /**
     * @see parent::MASK_LINK
     */
    const MASK_LINK = '~articles\/(\d+)\/~mix';
    
    /**
     * @see parent::$title
     */
    public $title = '<a class="b-layout__link" href="/articles/" target="_blank">Статьи</a>';
    
    /**
     * @see parent::$hint
     */
    public $hint  = 'Например: https://www.free-lance.ru/articles/100/example.html';
    
    /**
     * @see parent::$title_field
     */
    public $title_field = 'Ссылки на статьи:';
    
    /**
     * @see parent::initHtmlData
     */
    public function initHtmlData() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/articles.php';
        
        $ids = $this->parseLinks();
        if($ids) {
            $this->html_data = articles::getArticleByIds(array_map('intval', $ids));
        }
    }
}