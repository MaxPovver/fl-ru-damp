<?php

namespace YandexMoney3\Request;

require_once(__DIR__ . '/../Presets/ApiKey.php');

use YandexMoney3\Presets\ApiKey;

class BaseRequest
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

    
    /*
    protected function checkAndReturn($key)
    {
        if (array_key_exists($key, $this->paramsArray)) {
            return $this->paramsArray[$key];
        } else {
            throw new \ErrorException("Key " . $key . " doesn't exists in defined params!");
        }
    }
    */
    
    
    public function setAgentId($agentId)
    {
        $this->setAttr(ApiKey::AGENT_ID, $agentId);
    }

    public function setClientOrderId($clientOrderId)
    {
        $this->setAttr(ApiKey::CLIENT_ORDER_ID, $clientOrderId);
    }

    public function setRequestDt($requestDT)
    {
        $this->setAttr(ApiKey::REQUEST_DT, $requestDT);
    }



    protected function setAttr($key, $value)
    {
        $this->paramsArray['@attributes'][$key] = $value;
    }

} 