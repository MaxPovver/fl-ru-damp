<?php

/**
 * Виджет выводит ТУ пользователя в каталоге фрилансеров
 */

require_once(ABS_PATH . '/classes/template.php');
require_once(ABS_PATH . '/classes/tservices/tservices_helper.php');

class FreelancersTServicesWidget
{
    public function __construct() 
    {
        $this->init();
    }

    public function init() 
    {
        global $js_file;
        $js_file['tservices/tservices_catalog'] = "tservices/tservices_catalog.js";
    }    
    
    public function run($tservices, $is_owner = false)
    {
        echo Template::render(__DIR__ . '/views/freelancers-tservices.php', array(
            'tservices' => $tservices,
            'is_owner' => $is_owner
        ));
    }
}