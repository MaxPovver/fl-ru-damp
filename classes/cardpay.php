<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/card_account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
/**
 *
 * Класс для покупки FM с помощью банковских карт.
 *
 */
class cardpay extends account {
	
	const MERCHANT_ID = '691486';
	const TESTMODE = 0;
	
	// Адреса сервисов.
	const URL_RESULTBYDATE = 'https://payments.paysecure.ru/resultbydate/resultbydate.cfm'; // для запроса операций за период.
	const URL_ORDER        = 'https://payments100.paysecure.ru/pay/order.cfm'; // для запросо операций по номеру заказа.
	
	// Коды ошибок (для нас).
    const ERR_MERCHANT_ID = 1;
    const ERR_HASH        = 2;
    const ERR_ORDERNUM    = 3;
    const ERR_DEPOSIT     = 4;
    
	/**
	 * Логин для авторизации в ASSIST
	 *
	 * @var string
	 */
	private $_login = 'freelance_sale';

	/**
	 * Пароль для авторизации в ASSIST
	 * @var string
	 */
	private $_password = ASSIST_PASSWD;
	
	/**
	 * Ключ для проверки хеша входящих запросов от assist.
	 * @var string
	 */
	private $_secret = ASSIST_SECRET;
	
	/**
	 * Лог
	 * @var log
	 */
	public $log;
	
	
	function __construct() {
	    $this->log = new log('assist/assist-%d%m%Y.log');
	    $this->log->linePrefix = '%d.%m.%Y %H:%M:%S : ';
	}

	/**
	 * Принимает от assist результаты платежа, производит зачисление.
	 *
	 * @param array $req   массив $_POST с данными.
	 */
	function checkdeposit($req) {
	    $this->log->writeln('Поступление платежа.');
	    $this->log->writevar($req);
        if($req['merchant_id'] != self::MERCHANT_ID) {
            $this->fail(self::ERR_MERCHANT_ID);
        }
        $hash_x = $req['merchant_id'].$req['ordernumber'].$req['amount'].$req['currency'].$req['orderstate'];
        $hash = strtoupper(md5(strtoupper(md5($this->_secret).md5($hash_x))));
        if($hash != $req['checkvalue']) {
            $this->fail(self::ERR_HASH);
        }
	    
	    if($req['responsecode'] == 'AS000' && $req['orderstate'] == 'Approved') {
            $card_account = new card_account();
            $billing_no = $card_account->checkPayment($req['ordernumber']);
            if(!$billing_no) {
                $this->fail(self::ERR_ORDERNUM);
            }

            $amm   = $req['orderamount'];
            $descr = "CARD номер счета в ассисте {$req['billnumber']} с карты {$req['meantypename']} {$req['meannumber']} "
                   . "сумма - {$req['orderamount']} {$req['ordercurrency']}, "
                   . "обработан {$req['packetdate']}, номер покупки - {$req['ordernumber']}";
            if($error = $this->deposit($op_id, $billing_no, $amm, $descr, 6, $req['orderamount'])) {
                $this->fail(self::ERR_DEPOSIT, $error);
            }
	    }
	    $this->success($req['billnumber'], $req['packetdate']);
	}
	
	/**
	 * Набросок функции для запроса у assist операций за определенный период.
	 * @todo реализовать парамтеры для более гибких периодов, разных статусов и т.п.
	 *
	 * @return string
	 */
	function checkResultsByDate() {
	    $this->log->writeln('Проверка результатов операций.');
        $card_account = new card_account();
        $req['Merchant_ID'] = self::MERCHANT_ID;
        $req['Login'] = $this->_login;
        $req['Password'] = $this->_password;
        $req['TestMode'] = self::TESTMODE;
        $req['Operationstate'] = 'S';
        echo $this->request($req, self::URL_RESULTBYDATE);
	}
	
	/**
	 * Выполняет запрос в assist
	 *
	 * @param array $req   параметры запроса [ключ:значение]
	 * @param string $url   адрес сервиса.
	 * @return string   ответ.
	 */
	function request($req, $url) {
	    echo http_build_query($req, '', '&');
        $context = array (
            'http' => array (
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($req, '', '&')
        ) );
        return file_get_contents($url, false, stream_context_create($context));
	}
	
	/**
	 * Формирует ошибочный ответ на неудавшийся платеж.
	 *
	 * @param integer $code   внутренний (наш) код ошибки.
	 * @param string $msg   описание ошибки (тоже для нас).
	 */
	function fail($code, $msg = NULL) {
	    switch($code) {
	        case self::ERR_MERCHANT_ID : $fc = 5; $sc = 100; break;
	        case self::ERR_HASH        : $fc = 9; $sc = 0; break;
	        case self::ERR_ORDERNUM    : $fc = 5; $sc = 107; break;
	        case self::ERR_DEPOSIT     : $fc = 2; $sc = 1; break;
	    }
	    
        $ret = '<?xml version="1.0" encoding="UTF-8"?>'
             . '<pushpaymentresult firstcode="' . $fc . '" secondcode="' . $sc . '" />';
        $this->log->writeln("ОШИБКА: code={$code} firstcode={$fc} secondcode={$sc} msg={$msg}");
        die($ret);
	}
	
	/**
	 * Формирует успешный ответ на платеж.
	 *
	 * @param integer $billnumber   номер операции в системе Assist.
	 * @param string $packetdate   дата операции по Assist.
	 */
	function success($billnumber, $packetdate) {
        $ret = '<?xml version="1.0" encoding="UTF-8"?>'
             . '<pushpaymentresult firstcode="0" secondcode="0">'
             . '<order>'
             . '<billnumber>'.$billnumber.'</billnumber>'
             . '<packetdate>'.$packetdate.'</packetdate>'		
             . '</order>'
             . '</pushpaymentresult>';
	    $this->log->writeln('ОК');
        die($ret);
	}
	
    function getSecret() {
        if(is_release()) return false;
        return $this->_secret;
    }
}
