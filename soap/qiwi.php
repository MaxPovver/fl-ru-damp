<?php
/**
 * Ќа этот скрипт приход€т уведомлени€ от QIWI  ошелька.
 * SoapServer парсит вход€щий SOAP-запрос, извлекает значени€ тегов login, password, txn, status,
 * помещает их в объект класса Param и вызывает функцию updateBill объекта класса TestServer.
 *
 * Ћогика обработки магазином уведомлени€ должна быть в updateBill.
 */

require_once($_SERVER['DOCUMENT_ROOT']."/classes/qiwi_soap.php");

$soap = new SoapServer('IShopClientWS.wsdl', array('classmap' => array('tns:updateBill' => 'Param', 'tns:updateBillResponse' => 'Response')));

$soap->setClass('QiwiServer');
$soap->handle();

class Response {
    public $updateBillResult;
}

class Param {
    public $login;
    public $password;
    public $txn;      
    public $status;
}

class QiwiServer 
{
    function updateBill($param) {
    	// ¬ зависимости от статуса счета $param->status мен€ем статус заказа в магазине
    	if ($param->status = 60) {
    		// заказ оплачен
    		// найти заказ по номеру счета ($param->txn), пометить как оплаченный
    	} else if ($param->status > 100) {
    		// заказ не оплачен (отменен пользователем, недостаточно средств на балансе и т.п.)
    		// найти заказ по номеру счета ($param->txn), пометить как неоплаченный
    	} else if ($param->status >= 50 && $param->status < 60) {
    		// счет в процессе проведени€
    	} else {
    		// неизвестный статус заказа
    	}

    	// формируем ответ на уведомление
    	// если все операции по обновлению статуса заказа в магазине прошли успешно, отвечаем кодом 0
    	// $temp->updateBillResult = 0
    	// если произошли временные ошибки (например, недоступность Ѕƒ), отвечаем ненулевым кодом
    	// в этом случае QIWI  ошелЄк будет периодически посылать повторные уведомлени€ пока не получит код 0
    	// или не пройдет 24 часа
    	$temp = new Response();
    	$temp->updateBillResult = 0;
    	return $temp;
    }
}
?>
