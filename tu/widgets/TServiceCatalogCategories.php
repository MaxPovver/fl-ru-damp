<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_categories.php');

/**
 * Class TServiceCatalogCategories
 *
 * Виджет - список категорий типовых услуг
 */
class TServiceCatalogCategories extends CWidget {

    protected $category_group;
        
    protected $filter_get_params;
        
	public function run() {
		$tservicesCategoriesModel = new tservices_categories();

        $categories = array();

        if($this->category_group) {
            $categories = $tservicesCategoriesModel->getCategoriesByParent($this->category_group);
        } else {
            $categories = $tservicesCategoriesModel->getParents();
        }

		$this->render('t-service-catalog-categories', array(
			'categories' => $categories,
            'get_params' => $this->filter_get_params
		));
	}
}