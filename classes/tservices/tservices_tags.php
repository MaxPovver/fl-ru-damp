<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/atservices_model.php");

/**
 * Теги ТУ
 *
 */
class tservices_tags extends atservices_model
{
    private $TABLE              = 'tservices_tags';
    private $TABLE_TAG_REF      = 'tservices_tags_reference';

    
    /**
     * Вернуть все пользовательские теги определенной ТУ
     * 
     * @param int $service_id
     * @return array()
     */
    public function getsByTServiceId($service_id) 
    {
        return $this->db()->col("
            SELECT t.name 
            FROM {$this->TABLE} AS t 
            INNER JOIN {$this->TABLE_TAG_REF} AS tr ON (tr.tag_id = t.id AND t.category_id = 0) 
            WHERE tr.service_id = ?i", 
           $service_id);
    }
    
 
    /**
     * Обновляем/добавляем теги ТУ 
     * с учетом пользователских 
     * и админских привязанных к категории
     * 
     * @todo это можно перенести в БД ввиде функции и вызывать ее тут
     * @todo возможно отдельная привязка к админским тегам это лишнее ведь их связывает категория?
     * 
     * @param int $service_id
     * @param int $category_id
     * @param array() $tags
     * @return boolean
     */
    public function updateByTServiceId($service_id, $category_id = 0, $tags = array())
    {
        if(!count($tags)) return false;
        
        $this->db()->query("DELETE FROM {$this->TABLE_TAG_REF} WHERE service_id = ?i",$service_id);
        
        $tag_ids = array();
        if($category_id > 0)
        {
            $tag_ids = $this->db()->col("SELECT id FROM {$this->TABLE} WHERE category_id = ?i",$category_id);
        }

        foreach($tags as $tag)
        {
            $tag_id = $this->db()->val("SELECT id FROM {$this->TABLE} WHERE (lower(name) = lower(?))", $tag);

            if($tag_id && !in_array($tag_id, $tag_ids)) $tag_ids[] = $tag_id; 
            
            if(!$tag_id)
            {
                $tag_id = $this->db()->insert($this->TABLE,array('name' => $tag),'id');
                $tag_ids[] = $tag_id;
            }
        }

        $data = array();
        foreach($tag_ids as $tag_id)
        {
            $data[] = array('tag_id' => $tag_id,'service_id' => $service_id);
        }
        
        $this->db()->insert($this->TABLE_TAG_REF,$data);
        
        return true;
    }
    
}

