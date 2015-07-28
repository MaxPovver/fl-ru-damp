<?
/**
 * Подключаем класс для работы с аккаунтом пользователя
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
/**
 * Подключаем файл с ключами оплаты
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");

/**
 * Класс для пополнения счета через WebMoney
 *
 * @see /income/wm.php
 */
class wmpay extends account
{
	
	/**
	 * Кошельки на которые поступает счет
	 *
	 * @var array
	 */
	public $wmzr = array('Z801604194058','R199396491834','R109922555324');
	
	/**
	 * Ключ оплаты
	 * 
	 * @link /classes/payment_keys.php
	 * @var string
	 */
	public $key  = WM_KEY;
	
	/**
	 * Валюта оплаты на Р кошелек
	 *
	 * @link /classes/payment_keys.php
	 * @var string
	 */
	public $exchR = EXCH_WMR;
	
	/**
	 * Валюта оплаты на Z кошелек
	 *
	 * @link /classes/payment_keys.php
	 * @var string
	 */
	public $exchZ = EXCH_WMZ;
	
	/**
	 * Обработка запроса на оплату
	 *
	 * @param string  $wmzr           Номер кошелька
	 * @param integer $billing_no     Номер биллинга
	 * @param integer $ammount        Сумма перевода
	 * @param integer $operation_type Тип операции (тбл. op_codes)
	 * @param integer $operation_id   ИД операции 
	 * @return string Сообщение об ошибке
	 */
	function prepare($wmzr, $billing_no, $ammount, $operation_type, $operation_id){
		if (!in_array($wmzr, $this->wmzr)) $error = "Неверный кошелек!";
		if (!$this->is_dep_exists($billing_no)) $error = "Неверный счет на сайте!";
		switch ($operation_type){
			case "1":		//Резерв денег по СбР
				
				break;
			default:		//Перевод денег на личный счет
				
		}
		return $error;
	}
	
	/**
	 * Проверка депозита
	 *
	 * @see /income/wm.php 
	 * @param string  $wmzr        Номер кошелька 
	 * @param inetger $ammount     Сумма депозита
	 * @param inetger $payment_num Сумма оплаты
	 * @param inetger $wm_invs_no  Номер ВМЗ 
	 * @param inetger $wm_trans_no Номер транзакции ВМЗ
	 * @param inetger $payer_wmzr  Номер кошелька оплатившего
	 * @param inetger $payer_wmid  номер ВМИД оплатившего 
	 * @param date    $wm_date     Дата оплаты
	 * @param char    $hash        Хэш
	 * @param inetger $mode        Режим оплаты
	 * @param inetger $billing_no  Номер биллинга
	 * @param inetger $operation_type Тип операции (см пояснения в функции)
	 * @param inetger $operation_id   ИД Операции
	 * @return string Сообщение об ошибке
	 */
	function checkdeposit($wmzr, $ammount, $payment_num,
			$wm_invs_no, $wm_trans_no, $payer_wmzr, $payer_wmid, $wm_date,
			$hash, $mode, $billing_no, $operation_type, $operation_id){
		
		if (!in_array($wmzr, $this->wmzr)) return "Неверный кошелек продавца!";
		
		if (floatval($ammount) <= 0) return "Неверная сумма!";
		
		$hash_str = $wmzr . $ammount . $payment_num . $mode . $wm_invs_no . $wm_trans_no . $wm_date
					 . $this->key . $payer_wmzr . $payer_wmid;
		if (strtoupper(md5($hash_str)) != $hash) return "Неверный хэш!";
		
		$descr = "WM #$payment_num на кошелек $wmzr с кошелька $payer_wmzr (wmid:$payer_wmid) сумма - $ammount,";
		$descr .= " обработан $wm_date, номер покупки - $payment_num, номер платежа - $wm_trans_no";
		
		$op_id = 0;
		switch ($operation_type){
			case "1":		//Резерв денег по СбР
				$op_code = 36;
				$amm = 0;
				$descr .= " СбР #".$operation_id;
				break;
			case sbr::OP_RESERVE: // Резерв денег по СбР (новая)
				$op_code = sbr::OP_RESERVE;
				$amm = 0;
				$descr .= " СбР #".$operation_id;
				break;
			default:		//Перевод денег на личный счет
				$op_code = 12;
				if (substr($wmzr,0,1) == "R") {$amm = $ammount;}
				if (substr($wmzr,0,1) == "Z") {$amm = $ammount * $this->exchZ;}
		}

		if (substr($wmzr,0,1) == "R") {
			if ($wmzr == "R109922555324") $ps = 10; else $ps = 2;}
		if (substr($wmzr,0,1) == "Z") {$ps = 1;}
		
		$error = $this->deposit($op_id, $billing_no, $amm, $descr, $ps, $ammount, $op_code, $operation_id);
		return $error;
	}
	
}
?>