<?php

require_once(__DIR__ . '/ReservesPayException.php');


class ReservesPaybackException extends ReservesPayException
{
    const INSERT_FAIL_MSG       = 'Ќе удаетс€ добавить запрос за возврат средств.';
    const ALREADY_PAYBACK_MSG   = '—редства запроса уже были возвращены.';
    const PAYBACK_INPROGRESS    = '«апрос в процессе возврата средств.';
    const PAYBACK_NOTFOUND      = '«апрос на возврат средств не найден.';
    const UNDEFINED_STATUS      = 'Ќа запрос возврата средств был получен не известный статус.';
    const CANT_CHANGE_SUBSTATUS = 'Ќа запрос возврата средств не удалось сменить подстатус резерва.';
}