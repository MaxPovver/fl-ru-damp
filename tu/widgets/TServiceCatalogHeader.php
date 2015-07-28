<?php
/**
 * Class TServiceCatalogHeader
 *
 * Виджет - Блок c заголовком
 */
class TServiceCatalogHeader extends CWidget 
{
        public function run() 
        {
            //собираем шаблон
            $this->render('t-service-catalog-header', array(
                'page_title' => SeoTags::getInstance()->getH1()
            ));
	}
}