<?php

namespace YandexMoney3\Response;

require_once(__DIR__ . '/BaseResponse.php');


class DepositionResponse extends BaseResponse
{
    const TECH_MESSAGE = 'techMessage';
    
    /**
     * @return string|null
     */
    public function getTechMessage()
    {
        return $this->checkAndReturn(self::TECH_MESSAGE);
    }
    
}