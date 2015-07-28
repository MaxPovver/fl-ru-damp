<?php

namespace YandexMoney3\Presets;

require_once(__DIR__ . '/BaseApiKey.php');

class MWSApiKey extends BaseApiKey
{
    //returnPayment
    const INVOICE_ID        = 'invoiceId';
    const SHOP_ID           = 'shopId';
    const AMOUNT            = 'amount';
    const CURRENCY          = 'currency';
    const CAUSE             = 'cause';
}