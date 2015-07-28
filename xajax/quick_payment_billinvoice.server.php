<?php

/*
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');
*/

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/forms/BillInvoiceForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenBill.php');

//------------------------------------------------------------------------------


/**
 * Выставяем счет оплаты по безналу
 * 
 * @param string $type - тип платежа (тут всегда bank)
 * @param array $data
 * @return \xajaxResponse
 */
function quickPaymentBillinvoiceBank($type, $data)
{
    $objResponse = &new xajaxResponse();
    
    $uid = get_uid(false);
    if ($uid <= 0) {
        return $objResponse;
    }
    
    $form = new BillInvoiceForm();
    
    if (!$form->isValid($data)) {
        $params = addslashes(urldecode(http_build_query($form->getAllMessages('<br/>'))));
        $objResponse->script("
            var qp = window.quick_ext_payment_factory.getQuickPayment('billinvoice');
            if(qp) qp.showElementsError('{$params}');
        ");
        return $objResponse;
    }

    $sum = $form->getValue('sum');
    
    try {
        
        $doc = new DocGenBill();
        $file = $doc->generateBankInvoice($uid, @$_SESSION['login'], $sum);
        
    } catch (Exception $e) {
         $objResponse->script("
            var qp = quick_ext_payment_factory.getQuickPayment('billinvoice');
            if(qp) qp.show_error('{$e->getMessage()} Попробуйте еще раз.');
        ");
            
        return $objResponse;
    }
    
    
    $link = WDCPREFIX . '/' . $file->path . $file->name;
    
    $objResponse->script(" 
        
        var template = $('bill_invoice_template').get('html');
        if(template) {
            template = template.replace('{link}','{$link}');
            template = template.replace('{name}','{$file->original_name}');  
            template = template.replace('{num}','{$doc->getField('id')}'); 
            $('bill_invoice_create').addClass('b-layout_hide');    
            $('bill_invoice_remove').set('html', template).removeClass('b-layout_hide'); 
        }
        
        var qp = quick_ext_payment_factory.getQuickPayment('billinvoice');
        if(qp) qp.close_popup();
    ");
    
    return $objResponse;
}