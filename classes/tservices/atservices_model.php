<?php

/**
 * Базовый класс модели
 *
 */
abstract class atservices_model 
{
    /**
     * Параметры пагинации
     * @var int
     */
    protected $limit = 0;
    protected $offset;
    
    
    /**
     * Установить параметры пагинации
     * 
     * @param int $limit
     * @param int $page
     * @return $this
     */
    public function setPage($limit, $page = 1) 
    {
        $page = ($page > 0) ? $page : 1;
        $this->limit = $limit;
        $this->offset = ($page - 1) * $limit;

        return $this;
    }
    
    
    /**
     * Достроить SQL запрос ограничением на кол-во и смещение
     * 
     * @todo Перенести настройки пагинации в абстрактный класс модели?
     * 
     * @param string $sql
     * @return string
     */
    protected function _limit($sql)
    {
        if ( $this->limit ) 
        {
            $sql .= ' LIMIT ' . $this->limit . ($this->offset? ' OFFSET ' . $this->offset: '');
        }        
        
        return $sql;
    } 
    
    
    /**
     * @return DB
     */
    public function db()
    {
        return $GLOBALS['DB'];
    }
    
}