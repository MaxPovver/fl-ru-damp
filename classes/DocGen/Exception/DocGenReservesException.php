<?php

require_once('DocGenException.php');

class DocGenReservesException extends DocGenException
{
    const BANK_INVOICE_ERROR_MSG       = 'счета на оплату';
    
    const ACT_COMPLETED_FRL_ERROR_MSG  = 'акта о выполнении работы Исполнителем';
    const ACT_SERVICE_EMP_ERROR_MSG    = 'aктa об оказании услуг Заказчику';
    const AGENT_REPORT_ERROR_MSG       = 'отчета агента по Договору';
    
    const RESERVE_OFFER_CONTRACT_ERROR_MSG  = 'документа договора';
    const RESERVE_OFFER_AGREEMENT_ERROR_MSG = 'документа соглашения';
    
    const LETTER_FRL_ERROR_MSG       = 'информационного письма Исполнителю';
    
    const ARBITRAGE_REPORT_ERROR_MSG    = 'отчета об арбитражном рассмотрении';
    
    const RESERVE_FACTURA_ERROR_MSG     = 'счет-фактуры';
    
    const RESERVE_SPECIFICATION_ERROR_MSG     = 'технического задания';
    
    /**
     * @todo: А если сделать проще и писать Ошибка при формировании документа: "Бла бля".?
     * Тогда тут нужно перебить конструктор и собирать там полную строку а в модели передавать
     * просто название документа.
     */
}