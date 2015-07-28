<?php

namespace YandexMoney3\Utils;

require_once(__DIR__ . '/../Exception/Exception.php');
require_once(__DIR__ . '/../Exception/ApiException.php');

use YandexMoney3\Exception as Exceptions;

class BaseApiFacade 
{
    const URI_TEST_KEY  = 'uri_test';
    const URI_MAIN_KEY  = 'uri_main';
    
    protected $options;
    protected $base_uri;

    protected $isTest = false;


    public function __construct($options) 
    {
        $this->setOptions($options);
    }
    
    
    
    /**
     * Options 
     * 
     * @param type $options
     * @throws Exception\ApiException
     */
    public function setOptions($options)
    {
        $this->isTest = (isset($options['is_test']) && $options['is_test'] == true);
        $reqField = $this->isTest?self::URI_TEST_KEY:self::URI_MAIN_KEY;
        if(!array_key_exists($reqField, $options)) 
                throw new Exceptions\ApiException('URI options is required.');
        
        $this->base_uri = $options[$reqField];
        $this->options = $options;
    }
    
    
    
    
    /**
     * Prepare full api request Uri
     * @param $uri
     * @return string
     */
    protected function getApiUri($uri, $api)
    {
        return sprintf($api, $this->base_uri, $uri);
    }  
}
