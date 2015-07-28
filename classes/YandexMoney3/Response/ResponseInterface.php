<?php

namespace YandexMoney3\Response;



interface ResponseInterface
{
    /**
     * @return string
     */
    public function getError();

    /**
     * @return boolean
     */
    public function isSuccess();
}