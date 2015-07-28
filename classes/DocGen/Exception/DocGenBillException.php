<?php

require_once('DocGenException.php');

class DocGenBillException extends DocGenException
{
    const BANK_INVOICE_ERROR_MSG = 'счета на оплату';
    const BILL_FACTURA_ERROR_MSG = 'счет-фактуры';
}