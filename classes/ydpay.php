<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");

/**
 * Подключаем файл для работы с ключами оплаты
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");

/**
 * Класс для работы с оплатой через Яндекс деньги
 *
 */
class ydpay extends account
{
	
    const SHOP_DEPOSIT     = 4551;
    const SHOP_SBR_RESERVE = 12445;
    
    // IP, с которых разрешено приходить запросам Яндекса.
    protected $_allowed_ips = array('77.75.157.168', '77.75.157.169', '77.75.159.166', '77.75.159.170', '77.75.159.196');
    
	/**
	 * ИД магазина 
	 *
	 * @var integer
	 */
	public $shopid = array(self::SHOP_DEPOSIT, self::SHOP_SBR_RESERVE);
	
	/**
	 * Ключ оплаты
	 *
	 * @var integer
	 */
	public $key = YD_KEY;
	
	/**
	 * Валюта оплаты
	 *
	 * @var string
	 */
	public $exchR = EXCH_YM;
	
	/**
	 * ИД курса для оплаты
	 *
	 * @var integer
	 */
	public $curid = 643;
	
	/**
	 * ИД банка
	 *
	 * @var integer
	 */
	public $bank = 1001;
	
	/**
	 * Проверка данных на дублирование
	 *
	 * @param string $str	строка описания операции
	 * @return integer		id предыдущей операции, false если операция не найдена
	 */
	function checkDups($str){
	    global $DB;
	    
		$sql = "SELECT id FROM account_operations WHERE descr = ?";
		$out = $DB->val($sql, $str);
		if($out !== null) return $out;
		return false;
	}
	
	/**
	 * Проверка входящих данных
	 *
	 * @param integer $shopid     		ИД магазина
	 * @param integer $billing_no 		Номер биллинга
	 * @param integer $ammount    		Сумма оплаты
	 * @param string  $operation_type 	Тип операции
	 * @param integer $operation_id   	Ид операции  (op_codes)
	 * @return string Сообщение об ошибке
	 */
	function prepare($shopid, $billing_no, $ammount, $operation_type, $operation_id){
		if (!in_array($shopid, $this->shopid)) $error = 'Неверный магазин!';
		if (!$this->is_dep_exists($billing_no)) $error = 'Неверный счет на сайте!';
		return $error;
	}
	
	/**
	 * Проверка и внесение депозита
	 *
	 * @param integer $shopid       	Ид магазина
	 * @param integer $ammount      	Сумма депозита 
	 * @param integer $orderIsPaid  	Оплачено или нет
	 * @param integer $orderNumber  	Номер оплаты
	 * @param integer $billing_no   	Номер биллинга
	 * @param integer $hash         	Хэш оплаты (то что мы должны послать в запросе на Яндекс деньги должно совпадать с этим хешем)
	 * @param integer $fromcode     	Кошелек с которого происхдит оплата
	 * @param integer $paymentDateTime  Дата оплаты
	 * @param string  $operation_type   Тип операции (см. в функции)
	 * @param integer $operation_id     Ид операции (op_codes)
	 * @return string Сообщение об ошибке
	 */
	function checkdeposit($shopid, $ammount, $orderIsPaid,
        $orderNumber, $billing_no, $hash, $fromcode, $paymentDateTime, $operation_type, $operation_id)
    {
        if (floatval($ammount) <= 0)
            return 'Неверная сумма!';
            
        $hash_str = $orderIsPaid . ';' . $ammount . ';' . $this->curid . ';'
                  . $this->bank . ';' . $shopid . ';' . $orderNumber . ';' . $billing_no . ';' . $this->key;
                  
        if (strtoupper(md5($hash_str)) != $hash)
            return 'Неверный хэш!';
        
        $op_id = 0;
        $descr = "ЯД с кошелька $fromcode сумма - $ammount, обработан $paymentDateTime, номер покупки - $orderNumber";
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        
        if($operation_type == sbr::OP_RESERVE) { // убрать проверку после тестирования.
            $shopid = ydpay::SHOP_SBR_RESERVE;
        }
        
        switch ($shopid) {
            case ydpay::SHOP_SBR_RESERVE : // Резерв денег по СбР (новая)
                $op_code = sbr::OP_RESERVE;
                $amm = 0;
                $descr .= " СбР #".$operation_id;
                break;
            case ydpay::SHOP_DEPOSIT : // Перевод денег на личный счет
                $op_code = 12;
                $amm = $ammount/$this->exchR;
                break;
            default :
                return 'Неверный магазин!';
        }
        
        $dups = $this->checkDups($descr);
        if (!$dups) {
            $error = $this->deposit($op_id, $billing_no, $amm, $descr, 3, $ammount, $op_code, $operation_id);
        }
        
        return $error;
    }
    
    
    /**
     * Проверка и подтверждение платежа, внесение депозита
     * 
     * @global DB $DB
     * @param type $request     Массив с данными запроса (можно весь $_POST)
     * @return type             Строка, если ошибка, иначе NULL
     */
    function process_payment($request) {
        global $DB;
        
        $action = $request['action'];
        $ip = getRemoteIp();
        
        if (!in_array($ip, $this->_allowed_ips)) {
            return "Неразрешенный IP: {$ip}";
        }
        
        if (!in_array($action, array('Check', 'PaymentSuccess'))) {
            return 'Некорректный запрос';
        }

        $shopid = $request['shopId'];
        $ammount = $request['orderSumAmount'];
        $orderIsPaid = $request['orderIsPaid'];
        $orderNumber = $request['invoiceId'];
        $billing_no = $request['customerNumber'];
        $hash = $request['md5'];
        $fromcode = $request['paymentPayerCode'];
        $paymentDateTime = $request['paymentDateTime'];
        $orderCreatedDatetime = $request['orderCreatedDatetime'];
        $operation_type = $request['OPERATION_TYPE'];
        $operation_id = $request['OPERATION_ID'];

        if (floatval($ammount) <= 0)
            return 'Неверная сумма!';

        $hash_str = $orderIsPaid . ';' . $ammount . ';' . $this->curid . ';'
            . $this->bank . ';' . $shopid . ';' . $orderNumber . ';' . $billing_no . ';' . $this->key;

        var_dump(strtoupper(md5($hash_str)));
        if (strtoupper(md5($hash_str)) != $hash)
            return 'Неверный хэш!';
        
        $op_id = 0; 
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        if($operation_type == sbr::OP_RESERVE) { // убрать проверку после тестирования.
            $shopid = ydpay::SHOP_SBR_RESERVE;
        }
        $op_descr = '';
        switch ($shopid) {
            case ydpay::SHOP_SBR_RESERVE : // Резерв денег по СбР (новая)
                $op_code = sbr::OP_RESERVE;
                $amm = 0;
                $op_descr = " СбР #".$operation_id;
                break;
            case ydpay::SHOP_DEPOSIT : // Перевод денег на личный счет
                $op_code = 12;
                $amm = $ammount;
                break;
            default :
                return 'Неверный магазин!';
        }
        
        if ($action == 'Check') {
            
            $descr = "ЯД с кошелька $fromcode сумма - $ammount, номер покупки - $orderNumber";
            $descr .= $op_descr;
            
            $dups = $DB->val('SELECT id FROM account_operations_yd WHERE descr = ?', $descr);
            if (!$dups) {
                $op_id = $DB->insert('account_operations_yd', array(
                    'billing_id'  => $billing_no,
                    'op_date'     => $orderCreatedDatetime,
                    'op_code'     => $op_code,
                    'ammount'     => $amm,
                    'trs_sum'     => $ammount,
                    'descr'       => $descr,
                    'invoice_id'  => $orderNumber,
                    ), 'id');

                $error = $DB->error;
            }
        } elseif ($action == 'PaymentSuccess') {
            $descr = "ЯД с кошелька $fromcode сумма - $ammount, обработан $paymentDateTime, номер покупки - $orderNumber";
            $descr .= $op_descr;
        
            $tmp_payment = $DB->row('SELECT * FROM account_operations_yd WHERE invoice_id = ?', $orderNumber);
            if (!$tmp_payment) {
                return 'Платеж не найден';
            }
            
            $dups = $this->checkDups($descr);
            if ($dups) {
                return;
            }
            
            $error = $this->deposit($op_id, $billing_no, $amm, $descr, 3, $ammount, $op_code, $operation_id);

            if (!$error) {
                $DB->query('DELETE FROM account_operations_yd WHERE invoice_id = ?', $orderNumber);
            }
        } else {
            $error = 'Некорректный запрос';
        }

        return $error;
    }

    

}
