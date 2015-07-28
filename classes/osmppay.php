<?
/**
 * Подключаем файл для работы с аккаунтом
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
/**
 * Подключаем файл для работы с пользователем
 */
require_once ($_SERVER['DOCUMENT_ROOT']."/classes/users.php");

/**
 * Класс для работы с пополнение счета через ОСМП
 *
 */
class osmppay extends account
{
	/**
	 * Курс валюты по отношению к FM.
	 *
	 * @var string
	 */
	public $exch = EXCH_OSMP;
	
	/**
	 * Проверка данных оплаты
	 *
	 * @param integer $err_code      Возвращает Код ошибки 
	 * @param string  $login         Логин оплатившего
	 * @param integer $operation_id  Ид операции
	 * @param integer $ammount       Сумма оплаты
	 * @return string Сообщение об ошибке
	 */
	function prepare(&$err_code, $login, $operation_id, $ammount){
		if (floatval($ammount) <= 0) { $err_code = 241; return "Неверная сумма!";}
		if (!$operation_id) {$err_code = 300; return "Неверный идентификатор операции!";}
	    if (!preg_match("/^[a-zA-Z0-9]+[-a-zA-Z0-9_]{2,}$/", $login)) {$err_code = 4; return "Неверный логин на сайте!";}
		$user = new users();
		$uid = $user->GetUid($error, $login);
		if (!$uid) {$err_code = 5; $error = "Неверный логин на сайте!";}
		elseif (!$this->GetInfo($uid)) {$err_code = 79; $error = "Счет абонента не активен.";}
		return $error;
	}
	
	/**
	 * Проверка депозита и зачисление денег на счет.
	 *
	 * @param integer $op_id        Возвращает Код операции
	 * @param integer $err_code     Возвращает Код ошибки
	 * @param integer $ammount      Возвращает Сумма депозита
	 * @param string $login         Логин депозитчика
	 * @param integer $operation_id ИД Операции
	 * @param string $op_date       Дата операции
	 * @return string Сообщение об ошибке
	 */
	function checkdeposit(&$op_id, &$err_code, &$ammount, $login, $operation_id, $op_date){
		
		if (floatval($ammount) <= 0) { $err_code = 241; return "Неверная сумма!";}
		
		if (!$operation_id) return "Неверный идентификатор операции!";
		
		if (!$op_date) { $err_code = 300; return "Неверная дата операции!";}

		$date_arr=strptime($op_date,"%Y%m%d%H%M%S");
		$date = ($date_arr['tm_year']+1900)."-".($date_arr['tm_mon']+1)."-".$date_arr['tm_mday']." ".$date_arr['tm_hour'].
			":".$date_arr['tm_min'].":".$date_arr['tm_sec'];
		if (strtotime($date) == -1) { $err_code = 300; return "Неверная дата операции!";}
		
		$user = new users();
		$uid = $user->GetUid($error, $login);
		if (!$uid) {$err_code = 5; $error = "Неверный счет на сайте!";}
		elseif (!$this->GetInfo($uid)) {$err_code = 79; $error = "Счет абонента не активен.";}
		
		$descr = "ОСМП от $date сумма - $ammount, номер покупки ОСМП $operation_id";
		
		$op_id = 0;
		$op_code = 12;
		$amm = $ammount;
		
		$old_payment = $this->SearchPaymentByDescr("номер покупки ОСМП $operation_id");
		if ($old_payment){
			$op_id = $old_payment['id'];
			$ammount = $old_payment['trs_sum'];							
		} else {
			$error = $this->deposit($op_id, $this->id, $amm, $descr, 8, $ammount, $op_code, 0, $date);
			if ($error) {
			    $error = "Невозможно завершить оплату. Повторите позже";
			    $err_code = 1;
			}
		}
		return $error;
	}
	
}
?>