<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');

class TServiceTiles extends CWidget 
{ 
    public function init() 
    {
        parent::init();
        
        global $js_file;
        $js_file['tservices/tservices_catalog'] = "tservices/tservices_catalog.js";
    }    
    
    public function run($tservices) 
    {
        //собираем шаблон
        $this->render('t-service-tiles', array(
            'tservices' => $tservices
        ));
    }
}