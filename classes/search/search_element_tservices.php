<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";

/**
 * Класс для поиска по ТУ
 * @todo: класс не реализован полностью и был использован для тестирования Sphinx
 */
class searchElementTservices extends searchElement
{
    protected $_sort = SPH_SORT_EXTENDED;
    protected $_sortby = '@weight DESC';
    
    /*
    public function isAllowed() 
    {
        return false;
    }
     */
    
}