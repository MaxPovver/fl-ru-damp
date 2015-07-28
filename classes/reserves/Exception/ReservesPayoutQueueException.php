<?php

require_once(__DIR__ . '/ReservesPayException.php');


class ReservesPayoutQueueException extends ReservesPayException
{
    const NOTFOUND                  = 'Запрос на выплату средств не найден.';
    const PAYED                     = 'Запрос уже был выплачен ранее.';
    const CANT_CHANGE_SUBSTATUS     = 'Не удалось сменить статус выплаты.';
}