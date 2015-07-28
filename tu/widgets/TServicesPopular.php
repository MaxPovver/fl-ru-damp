<?php

require_once(ABS_PATH . '/classes/tservices/tservices_helper.php');
require_once(ABS_PATH . '/classes/tservices/tservices_catalog.php');
require_once(ABS_PATH . '/tu/models/TServiceModel.php');

class TServicesPopular extends CWidget 
{ 
    public $tservices = array();
    public $limit = 8;
    public $ttl_cache = 300;
    protected $options = array();

    public function init($category_id = null, $current_tuid = null) 
    {
        parent::init();
        
        global $js_file;
        $js_file['tservices/tservices_catalog'] = "tservices/tservices_catalog.js";
        
        if (isset($this->options['limit'])) {
            $this->limit = $this->options['limit'];
        }
        
        
        if (isset($this->options['prof_group_id'], $this->options['prof_id']) && 
            !$category_id) {
            
            require_once(ABS_PATH . '/classes/tservices/tservices_categories.php');
            $tservices_categories = new tservices_categories();
            $category_data = $tservices_categories->getCategoryByFreelancersCatalog(
                    $this->options['prof_group_id'], 
                    $this->options['prof_id']);
            if ($category_data) {
                $category_id = $category_data['id'];
                $this->options['category_title'] = $category_data['title'];
                $this->options['category_stitle'] = $category_data['link'];
            }
        }
        
        $tservicesCatalogModel = new tservices_catalog();
        $tservicesCatalogModel->setPage($this->limit, 1);

        if (isset($this->options['user_id']) && $this->options['user_id'] > 0) {
            $tservicesCatalogModel->user_id = $this->options['user_id'];
        } elseif ($category_id) {
            $tservicesCatalogModel->category_id = $category_id;
        }
        
        $exclude_ids = ($current_tuid)?array($current_tuid):array();
        $list = $tservicesCatalogModel->cache($this->ttl_cache)->getList($exclude_ids);
        $this->tservices = $list['list'];
        
        if ($this->tservices) {
            //расширение сведений о типовых услугах
            $tserviceModel = new TServiceModel();
            $tserviceModel->addOwnerInfo()->extend($this->tservices, 'id');
        }
    }

    
    
    
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    
    
    
    public function run($category_title = null, $category_stitle = null) 
    {
        if(empty($this->tservices)) {
            return false;
        }
        
        if (isset($this->options['category_title'])) {
            $category_title = $this->options['category_title'];
        }
        
        if (isset($this->options['category_stitle'])) {
            $category_stitle = $this->options['category_stitle'];
        }
        
        //собираем шаблон
        $this->render('t-service-popular', array(
            'tservices' => $this->tservices,
            'category_title' => $category_title,
            'category_stitle' => $category_stitle,
            'options' => $this->options
        ));
    }
}