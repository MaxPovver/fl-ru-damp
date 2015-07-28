<?php

namespace YandexMoney3\Request;

require_once(__DIR__ . '/../Presets/MWSApiKey.php');
require_once(__DIR__ . '/BaseXmlRequest.php');

use YandexMoney3\Presets\MWSApiKey;

class ReturnPaymentRequest extends BaseXmlRequest
{

    public function setInvoiceId($invoiceId)
    {
        $this->setAttr(MWSApiKey::INVOICE_ID, $invoiceId);
    }
    
    public function setShopId($shopId)
    {
        $this->setAttr(MWSApiKey::SHOP_ID, $shopId);
    }
    
    public function setAmount($amount)
    {
        $this->setAttr(MWSApiKey::AMOUNT, $amount);
    }
    
    public function setCurrency($currency)
    {
        $this->setAttr(MWSApiKey::CURRENCY, $currency);
    }
    
    public function setCause($ñause)
    {
        $this->setAttr(MWSApiKey::CAUSE, $ñause);
    }
    
}