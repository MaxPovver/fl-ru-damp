<?php 

require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");

/**
 * 
 * Класс для обработки акции на масленицу
 */
class maslen extends account
{
    /**
     * Код акции в БД 
     */
    const OP_CODE = 108;
    
    /*
     * Сумма за акцию
     */
    const PAYED_SUM = 20; 
    
    /**
     *  Количество месяцев при покупке про
     */
    const MONTH_PRO = 1;
    
    const PRO_TARIF = 48;
    
    /**
     * Дата старта акции // для теста сделано на 15 февраля
     */
    public $start_date = '20120215';
    
    /**
     * Дата окончания акции 
     */
    public $stop_date  = '20120226';
    
    /**
     * Заголовок операции
     * 
     * @var string 
     */
    public $title      = 'Масленичная акция';
    
    /**
     * Описание операции 
     * 
     * @var string 
     */
    public $descr      = 'PRO-аккаунт на месяц + Размещение в карусели (в каталоге фрилансеров)';
    
    /**
     * Функция оплаты акции
     *  
     * @global object $DB база данных
     * @param integer $transaction_id  ИД транзакции сделки
     * @param integer $user_id         ИД пользователя
     * @return int 
     */
    function setPayed($transaction_id, $user_id) {
        global $DB;
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
  		$account = new account();
        if(is_emp($_SESSION['role'])) return 0;
        $bill_id = 0;
  		$error = $account->Buy($bill_id, $transaction_id, $this->getConst_OP_CODE(), $user_id, $this->title, $this->descr, $this->getConst_PAYED_SUM(), 0);
  		if ($error!==0) return 0;
  		
  		if ($bill_id) {
  		    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
            $pay_place = new pay_place(1);
            
            $account->commit_transaction($transaction_id, $user_id, $bill_id);
  		    $this->setUserPro($user_id, $bill_id);
            $pay_place->addUserTop($user_id);
  		    
  		    return $bill_id;
  		}
        
        return 0;
    }
    
    /**
     * Даем пользователою ПРО на месяц
     * 
     * @global object $DB     подключение к БД
     * @param  integer $user_id ИД пользователя
     * @param  integer $bill_id ИД операции
     * @return resource
     */
    function setUserPRO($user_id, $bill_id) {
        global $DB;
        
        $time = $this->getConst_MONTH_PRO() . " month";
        $sql = "INSERT INTO orders (from_id, to_date, tarif, ordered, billing_id, payed) VALUES (?, ?, ?, true, ?, true);
                    UPDATE users SET is_pro = true, is_pro_test = false WHERE uid=?;";
        $res = $DB->query($sql, $user_id, $time, $this->getConst_PRO_TARIF(), $bill_id, $user_id);
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
        $pro_last = payed::ProLast($_SESSION['login']);
        $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];
        return $res;
    }
    
    /**
     * Пользовался ли пользователь уже этой акцией
     *  
     * @global object $DB   
     * @param integer $user_id  ИД пользователя
     * @return boolean 
     */
    function isPayed($user_id) {
        global $DB;
        
        $sql = "SELECT a.id FROM account a INNER JOIN account_operations ao ON ao.billing_id = a.id WHERE a.uid = ?i AND ao.op_code = ?i";
        
        if($DB->cache(3600)->val($sql, $user_id, $this->getConst_OP_CODE()) > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверка даты проведения акции
     * 
     * @return boolean 
     */
    function isValidDate() {
        $now_date   = date('Ymd');
        
        if( !( $now_date >= $this->start_date && $now_date <= $this->stop_date ) )  {
            return false;
        }
        
        return true;
    }
    
    /**
     * Чистим по операции
     * 
     * @global object $DB      Подключение к БД 
     * @param integer $uid     ИД Пользователя
     * @param integer $opid    ИД операции (account_operations)
     * @return boolean 
     */
    function DelByOpid($uid, $opid) {
        global $DB;
        
        $sql = "DELETE FROM paid_places WHERE uid = ? AND type_place = 1";
        $DB->query($sql, $uid);
        
        $sql = "DELETE FROM orders WHERE from_id = ? AND billing_id = ?";
        $DB->query($sql, $uid, $opid);
        
        return true;
    }
    
    public function getConst_OP_CODE() {
        return self::OP_CODE;
    }
    
    public function getConst_PRO_TARIF() {
        return self::PRO_TARIF;
    }
    
    public function getConst_PAYED_SUM() {
        return self::PAYED_SUM;
    }
    
    public function getConst_MONTH_PRO() {
        return self::MONTH_PRO;
    }
}
?>