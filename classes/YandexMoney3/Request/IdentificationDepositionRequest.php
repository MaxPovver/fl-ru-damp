<?php

namespace YandexMoney3\Request;

require_once(__DIR__ . '/DepositionRequest.php');

use YandexMoney3\Presets\ApiKey;


class IdentificationDepositionRequest extends DepositionRequest
{

    
    
    public function setDocType($docType)
    {
        $this->setIdentificationAttr(ApiKey::DOC_TYPE, $docType);
    }

    
    public function setDocNumber($docNumber)
    {
        $this->setIdentificationAttr(ApiKey::DOC_NUMBER, $docNumber);
    }

    




    protected function setIdentificationAttr($key, $value)
    {
        $this->paramsArray['identification']['@attributes'][$key] = $value;
    }    
    
}