<?php
/**
 * Виджет - Тизер закрепления услуг (для скрытой части)
 */
class TServiceBindTeaserShort extends CWidget {
    
    const TPL_MAIN_PATH = '/tu/widgets/views/';
    
    const TPL_DEFAULT = 't-service-bind-teaser-short.php';
   
    public function run() 
    {
        echo Template::render(ABS_PATH . self::TPL_MAIN_PATH . self::TPL_DEFAULT, array());
    }
}