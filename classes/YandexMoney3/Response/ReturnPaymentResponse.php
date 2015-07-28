<?php

namespace YandexMoney3\Response;

require_once(__DIR__ . '/MWSBaseResponse.php');

class ReturnPaymentResponse extends MWSBaseResponse
{
    const CLIENT_ORDER_ID = 'clientOrderId';

    /**
     * @return string/null
     */
    public function getClientOrderId()
    {
        return $this->checkAndReturn(self::CLIENT_ORDER_ID);
    }
    
}