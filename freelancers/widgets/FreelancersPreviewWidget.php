<?php

/**
 * Виджет выводит превью работ/услуг пользователя
 */

require_once(ABS_PATH . '/classes/template.php');

class FreelancersPreviewWidget
{
    const MAX_ITEMS = 3;
    
    protected $list = array();
    
    protected $is_owner = false;
    
    protected $is_ajax = false;


    public function __construct($params = array()) 
    {
        $this->is_owner = isset($params['is_owner'])?$params['is_owner']:false;
        $this->is_ajax = isset($params['is_ajax'])?$params['is_ajax']:false;
        
        if (!$this->is_ajax) {
            $this->init();
        }
    }

    
    public function init() 
    {
        global $js_file;
        $js_file['tservices/tservices_catalog'] = "tservices/tservices_catalog.js";
    }    
    
    
    public function addItem($item)
    {
        if (count($this->list) < self::MAX_ITEMS) {
            $this->list[] = $item;
            return true;
        }
        
        return false;
    }

    
    public function render()
    {
        $suffix = $this->is_ajax? '-ajax':'';
        return Template::render(__DIR__ . "/views/freelancers-preview{$suffix}.php", array(
            'list' => $this->list,
            'max' => self::MAX_ITEMS,
            'is_owner' => $this->is_owner
        ));
    }
}