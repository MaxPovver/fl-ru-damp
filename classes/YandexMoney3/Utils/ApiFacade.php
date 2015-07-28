<?php

namespace YandexMoney3\Utils;

require_once(__DIR__ . '/../Exception/Exception.php');
require_once(__DIR__ . '/../Presets/Uri.php');
require_once(__DIR__ . '/../Presets/ApiKey.php');
require_once(__DIR__ . '/../Request/DepositionRequest.php');
require_once(__DIR__ . '/../Request/IdentificationDepositionRequest.php');
require_once(__DIR__ . '/../Request/BalanceRequest.php');
require_once(__DIR__ . '/../Response/DepositionResponse.php');
require_once(__DIR__ . '/../Response/BalanceResponse.php');


use YandexMoney3\Exception as Exceptions;
use YandexMoney3\Presets\Uri;
use YandexMoney3\Presets\ApiKey;
use YandexMoney3\Request\DepositionRequest;
use YandexMoney3\Request\IdentificationDepositionRequest;
use YandexMoney3\Request\BalanceRequest;
use YandexMoney3\Response as Responses;


/**
 * Class ApiFacade
 * @package YandexMoney3\Utils
 */
class ApiFacade
{
    /**
     * @var string
     */
    //private $agentId;    
    
    
    
    //private $paramArray = array();



    /**
     * @param string $agentId
     */
    /*
    public function setAgentId($agentId)
    {
        self::validateAgentId($agentId);
        $this->agentId = $agentId;
    }
    */
    
    /**
     * @return string
     */
    /*
    public function getAgentId()
    {
        return $this->agentId;
    }    
    */
    
    /*
    public function __construct() 
    {
        $this->paramArray[ApiKey::AGENT_ID] = $this->agentId;
    }
    */
    
    
    
    private $options;


    public function setOptions($options)
    {
        $this->options = $options;
    }

    




    public function testDeposition(DepositionRequest $depositionRequest)
    {
        $paramArray = $depositionRequest->getDefinedParams(Uri::TEST_DEPOSITION);
        $apiNetworkClient = new ApiNetworkClient($this->options);
        $response = $apiNetworkClient->request($this->getApiUri(Uri::TEST_DEPOSITION), $paramArray);
        return new Responses\DepositionResponse($response->getBodyXmlDecoded());
    }

    
    public function makeDeposition(DepositionRequest $depositionRequest)
    {
        $paramArray = $depositionRequest->getDefinedParams(Uri::MAKE_DEPOSITION);
        $apiNetworkClient = new ApiNetworkClient($this->options);
        $response = $apiNetworkClient->request($this->getApiUri(Uri::MAKE_DEPOSITION), $paramArray);
        return new Responses\DepositionResponse($response->getBodyXmlDecoded());
    }
    
    

    public function balance(BalanceRequest $balanceRequest)
    {
        $paramArray = $balanceRequest->getDefinedParams(Uri::BALANCE);
        $apiNetworkClient = new ApiNetworkClient($this->options);
        $response = $apiNetworkClient->request($this->getApiUri(Uri::BALANCE), $paramArray);
        return new Responses\BalanceResponse($response->getBodyXmlDecoded());
    }

    
    
    
    
    /**
     * Prepare full api request Uri
     * @param $uri
     * @return string
     */
    private function getApiUri($uri)
    {
        $url = $this->isTest()?$this->getTestUrl():Uri::API;
        return sprintf($url, $uri);
    }    
    
    
    private function getTestUrl()
    {
        return isset($this->options['test_url'])?$this->options['test_url']:null;
    }


    private function isTest()
    {
        return isset($this->options['is_test']) && $this->options['is_test'] === true;
    }












    /**
     * @param string $agentId
     * @throws \YandexMoney3\Exception\Exception
     */
    
    /*
    private static function validateAgentId($agentId)
    {
        if (($agentId == null) || ($agentId === '')) {
            throw new Exceptions\Exception('You must pass a valid application agentId');
        }
    }
    */
    
}