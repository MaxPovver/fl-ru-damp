<?php

require_once 'DigestBlockListProject.php';

class DigestBlockListContest extends DigestBlockListProject {
    
    /**
     * Тип проекта
     */
    const PROJECT_KIND = 7;
    
    /**
     * @see parent::$title
     * @var string 
     */
    public $title = '<a class="b-layout__link" href="/konkurs/" target="_blank">Топ %s конкурсов</a> с наиболее большим бюджетом за неделю';
    
    /**
     * @see parent::$hint
     * @var string 
     */
    public $hint  = 'Например: http://www.free-lance.ru/projects/5/example.html';
    
    /**
     * @see parent::$title_field
     * @var string 
     */
    public $title_field = 'Ссылки на конкурсы:';
}
