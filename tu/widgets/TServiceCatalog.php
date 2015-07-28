<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_categories.php');

/**
 * Class TServiceCatalog
 *
 * Виджет - список категорий типовых услуг
 */
class TServiceCatalog extends CWidget {

	public function run() {
		$tservicesCategoriesModel = new tservices_categories();
		$categoriesTree = $tservicesCategoriesModel->getAllCategories(true);

		$this->render('t-service-catalog', array(
			'categoriesTree' => $categoriesTree,
		));
	}
}