<?php

require_once('FormElement.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");

class Form_Element_GuestProjectUploader extends Form_Element
{
    protected $_isArray = true;
    
    public function init()
    {
        global $js_file;
        $js_file['uploader'] = 'uploader.js';
        
        //Всегда фильтруем ResourceID
        $this->addFilter('PregReplace',array('match' => '/[^0-9a-z_-]/', 'replace' => ''));
    }
    
}