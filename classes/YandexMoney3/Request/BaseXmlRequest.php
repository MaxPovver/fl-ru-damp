<?php

namespace YandexMoney3\Request;

require_once(__DIR__ . '/../Presets/BaseApiKey.php');

use YandexMoney3\Presets\BaseApiKey;

class BaseXmlRequest 
{
    /**
     * @var array string
     */
    protected $paramsArray = array();


    protected $rootParam = null;



    public function __construct() 
    {
        $this->setRequestDt(date('c'));
    }
    
    
    
    /**
     * @return array of params
     */
    public function getDefinedParams($rootParam = null)
    {
        $rootParam = ($rootParam)?$rootParam:$this->rootParam;
        return ($rootParam)?array($rootParam . 'Request' => $this->paramsArray):
                                  $this->paramsArray;
    }
    
    
    public function setClientOrderId($clientOrderId)
    {
        $this->setAttr(BaseApiKey::CLIENT_ORDER_ID, $clientOrderId);
    }

    public function setRequestDt($requestDT)
    {
        $this->setAttr(BaseApiKey::REQUEST_DT, $requestDT);
    }


    protected function setAttr($key, $value)
    {
        $this->paramsArray['@attributes'][$key] = $value;
    }   
    
}