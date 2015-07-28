<?php

require_once 'DigestBlockList.php';

/**
 * Класс для работы с блоком "Обсуждают в блогах"
 */
class DigestBlockListBlog extends DigestBlockList {
    
    /**
     * @see parent::ADD_FIELD
     */
    const ADD_FIELD = true;
    
    /**
     * @see parent::MASK_LINK
     */
    const MASK_LINK = '~blogs\/\S+?\/(\d+?)\/~mix';
    
    /**
     * @see parent::$title
     * @var string 
     */
    public $title = 'Обсуждают в <a class="b-layout__link" href="/blogs/" target="_blank">блогах</a>';
    
    /**
     * @see parent::hint
     * @var string 
     */
    public $hint  = 'Например: https://www.free-lance.ru/blogs/obschenie/268587/example.html';
    
    /**
     * @see parent::$tirle_field
     * @var string 
     */
    public $title_field = 'Ссылки на блоги:';
    
    
    /**
     * @see parent::initHtmlData
     */
    public function initHtmlData() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php';
        $ids = $this->parseLinks();
        if($ids) {
            $this->html_data = blogs::getBlogsByIds(array_map("intval", $ids));
        }
    }
}