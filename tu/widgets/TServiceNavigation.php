<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_categories.php');

/**
 * Class TServiceNavigation
 *
 * Виджет - Блок c заголовком и навигацией
 */
class TServiceNavigation extends CWidget 
{
        protected $category_group;
        
        protected $category;
        
        protected $filter_get_params;

        public function run() 
        {
            $cur_category_group = $cur_category = $categories = array();

            $model = new tservices_categories;
            
            if($this->category_group) {
                $cur_category_group = $model->getCategoryById($this->category_group);
                if ($this->category) {
                    $cur_category = $model->getCategoryById($this->category);
                }
            }

            //собираем шаблон
            $this->render('t-service-navigation', array(
                'is_crumbs' => $this->category_group,
                'cur_cat' => $cur_category,
                'cur_cat_group' => $cur_category_group,
                'get_params' => $this->filter_get_params
            ));
	}
}