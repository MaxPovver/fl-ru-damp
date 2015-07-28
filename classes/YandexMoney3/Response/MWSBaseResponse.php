<?php

namespace YandexMoney3\Response;

require_once(__DIR__ . '/../Domain/Base.php');
require_once(__DIR__ . '/ResponseInterface.php');


use YandexMoney3\Domain\Base;

class MWSBaseResponse extends Base implements ResponseInterface
{
    const ERROR           = 'error';
    const STATUS          = 'status';
    const PROCESSED_DT    = 'processedDT';
    const TECH_MESSAGE    = 'techMessage';
    
    /**
     * @var OriginalServerResponse
     */
    private $originalServerResponse;

    
    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->checkAndReturn(self::STATUS);
    }
    
    
    /**
     * @return string/null
     */
    public function getError()
    {
        return $this->checkAndReturn(self::ERROR);
    }    
    
    
    /**
     * @return string/null
     */
    public function getTechMessage()
    {
        return $this->checkAndReturn(self::TECH_MESSAGE);
    }
   
    
    /**
     * @return string/null
     */
    public function getProcessedDT()
    {
        return $this->checkAndReturn(self::PROCESSED_DT);
    }
    
    
    /**
     * @return bool
     */
    
    public function isSuccess()
    {
        return $this->checkAndReturn(self::ERROR) === null;
    }
    

    /**
     * @return \YandexMoney3\Response\OriginalServerResponse
     */
    public function getOriginalServerResponse()
    {
        return $this->originalServerResponse;
    }
    
    
    /**
     * @param \YandexMoney3\Response\OriginalServerResponse $originalServerResponse
     */
    public function setOriginalServerResponse($originalServerResponse)
    {
        $this->originalServerResponse = $originalServerResponse;
    }

    
    public function getDefinedParams()
    {
        return $this->params;
    }    
}
