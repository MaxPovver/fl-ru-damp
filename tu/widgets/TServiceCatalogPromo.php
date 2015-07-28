<?php

/**
 * Class TServiceCatalogPromo
 *
 * Виджет - блочная реклама всего раздела
 */
class TServiceCatalogPromo extends CWidget {

	public function run() {
		$uid = get_uid(false);
		if ($uid = get_uid(false))
		{
			$user = new users();
			$user->GetUser($_SESSION['login']);
		} else
		{
			$user = null;
		}

		$this->render('t-service-catalog-promo', array(
			'user' => $user,
		));
	}
}