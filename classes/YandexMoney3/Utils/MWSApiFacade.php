<?php

namespace YandexMoney3\Utils;

require_once(__DIR__ . '/BaseApiFacade.php');
require_once(__DIR__ . '/../Presets/MWSUri.php');
//require_once(__DIR__ . '/../Presets/MWSApiKey.php');
require_once(__DIR__ . '/../Request/ReturnPaymentRequest.php');
require_once(__DIR__ . '/../Response/ReturnPaymentResponse.php');

use YandexMoney3\Presets\MWSUri;
//use YandexMoney3\Presets\MWSApiKey;
use YandexMoney3\Request\ReturnPaymentRequest;
use YandexMoney3\Response as Responses;


class MWSApiFacade extends BaseApiFacade
{
    
    public function returnPayment(ReturnPaymentRequest $returnPaymentRequest)
    {
        $paramArray = $returnPaymentRequest->getDefinedParams(MWSUri::RETURN_PAYMENT);
        $apiNetworkClient = new ApiNetworkClient($this->options);
        $response = $apiNetworkClient->request($this->getApiUri(MWSUri::RETURN_PAYMENT), $paramArray);
        return new Responses\ReturnPaymentResponse($response->getBodyXmlDecoded());
    }

    protected function getApiUri($uri)
    {
        return parent::getApiUri($uri, MWSUri::API);
    } 
}