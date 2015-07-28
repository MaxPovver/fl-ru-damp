<?php

/**
 * Реализация обмена по основному протоколу ДОЛ
 * http://dengionline.com/dev/protocol/invoice#span-facePri-oplate-bankovskimi-kartamispan
 * 
 * Используется для пополнения счета банк. картами
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php';

class onlinedengi_cards extends account {
    /**
     * ИД проекта в системе ДеньгиОнлайн
     */
    const PROJECT_ID = 3097;

    /**
     * Идентификатор владельца внешней формы
     */
    const SOURCE_ID = 3097;

    /**
     * Код Пластика  в системе ДеньгиОнлайн
     */
    const MODE_TYPE = 263;
//    const MODE_TYPE = 108; // тестовые карты

    /**
     * секретный код
     */
    const SECRET = 'wihIH*OHhs@kjsdhf&LKADHdgfd13287*j';

    
    const REQUEST_URL = 'http://www.onlinedengi.ru/wmpaycheck.php';
    
    /**
     * Уведомление о платеже (код передается в параметре src)
     */
    const DO_REQUEST_PAYMENT = 1;

    /**
     * Проверка заказа (код передается в параметре src)
     */
    const DO_REQUEST_CHECKIN = 2;

    /**
     * Успешный платеж (код передается в параметре src)
     */
    const DO_REQUEST_SUCCESS = 3;

    /**
     * Ошибка платежа (код передается в параметре src)
     */
    const DO_REQUEST_FAILURE = 4;


    /**
     * @var DB 
     */
    private $_db;
    
    /**
     * лог
     * @var log 
     */
    private $_log;

    public function __construct() {
        $this->_db = new DB('master');
    }
    
    public function getRedirectUrl($order_id, $amount) {
        $url = "";
        $log = array();
        
        $this->GetInfo(get_uid(0));
        
        $params = array(
            'project' => self::PROJECT_ID,
            'source' => self::SOURCE_ID,
            'nickname' => get_uid(0),
            'order_id' => $order_id,
            'amount' => $amount,
            'mode_type' => self::MODE_TYPE,
            'comment' => 'Пополнение счета № ' . $this->id,
            'xml' => 1
        );
        
        $log['request'] = 'getRedirectUrl';
        $log['params'] = $params;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::REQUEST_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $res = curl_exec($ch);
        
        $log['response'] = iconv('UTF8', 'CP1251', $res);
        
        if (!$res) {
            $log['result'] = 'Ошибка запроса.';
            $this->_log('request')->writevar($log);
            return false;
        }
        
        $xml = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        if (!$res) {
            $log['result'] = 'Ошибка обработки ответа.';
            $log['response'] = $xml;
            $this->_log('request')->writevar($log);
            return false;
        }
        
        if (intval($xml->status) == 0) {
            $url = trim($xml->iframeUrl) . '?'
                . 'payment_id=' . trim($xml->paymentId)
                . '&amount_original=' . trim($xml->amountOriginal)
                . '&currency=' . trim($xml->currency)
                . '&lang=' . trim($xml->lang)
                . '&hash=' . trim($xml->hash);
        } else {
            $log['result'] = 'Ошибка запроса.';
            $this->_log('request')->writevar($log);
            return false;
        }
        
        $log['url'] = $url;
        $this->_log('request')->writevar($log);
        
        return $url;
    }

    public function handleRequest($src = null, $req = array()) {
        $this->_action = $src;
        $this->_request = $req;
        
        switch ($this->_action) {
            case self::DO_REQUEST_CHECKIN:
                $this->_log('response')->writeln('CHECKIN');
                $this->_log('response')->writevar($req);
                if (!$this->_validate()) {
                    echo $this->_response('NO', 'Ошибка проверки подлинности запроса.');
                    exit();
                }
                require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
                $uid = intval($req['userid']);
                $user = new users();
                $user->GetUserByUID($uid);
                
                if($user->uid > 0) {
                    echo $this->_response('YES', 'Пользователь существует.');
                    exit();
                } else {
                    echo $this->_response('NO', 'Пользователь не существует.');
                    exit();
                }   
                break;
            case self::DO_REQUEST_PAYMENT:
                $this->_log('response')->writeln('PAYMENT');
                $this->_log('response')->writevar($req);
                
                if (!$this->_validate()) {
                    echo $this->_response('NO', 'Ошибка проверки подлинности запроса.');
                    exit();
                }
	    
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/card_account.php");
                $card_account = new card_account();
                $billing_no = $card_account->checkPayment($req['orderid']);
                if(!$billing_no) {
                    $this->_log('response')->writeln('Платеж не найден.');
                    echo $this->_response('NO', 'Номер платежа не найден.');
                    exit();
                }
                
                $req['date'] = date('Y-m-d H:i:s');

                $amm   = $req['amount'];
                $descr = "CARD номер платежа в системе ДеньгиОнлайн {$req['paymentid']}  "
                       . "сумма - {$req['amount']} руб., "
                       . "обработан {$req['date']}";
                if($error = $this->deposit($op_id, $billing_no, $amm, $descr, 6, $req['amount'])) {
                    $this->_log('response')->writeln('Ошибка проведения платежа.');
                    echo $this->_response('NO', $error);
                    exit();
                }
                $this->_log('response')->writeln('Платеж принят.');
                echo $this->_response('YES');
                break;
            case self::DO_REQUEST_SUCCESS:
                $this->_log('response')->writeln('SUCCESS');
                header_location_exit('/bill/cardsuccess/');
                break;
            case self::DO_REQUEST_FAILURE:
                $this->_log('response')->writeln('FAILURE');
                $_SESSION['bill.GET']['error'] = '';
                header_location_exit('/bill/fail/');
                break;
            default:
                break;
        }
    }
    
    /**
     * 
     * @return log
     */
    private function _log($type = 'income') {
        if ($this->_log) {
            return $this->_log;
        }
        
        $this->_log = new log("onlinedengi_cards/cards-{$type}-%d%m%Y.log");
        $this->_log->linePrefix = '%d.%m.%Y %H:%M:%S : ' . $_SERVER['REMOTE_ADDR'] . ': ';
        
        return $this->_log;
    }


    private function _validate() {
        $arr = array();
        
        switch ($this->_action) {
            case self::DO_REQUEST_CHECKIN:
                $arr[] = '0';
                $arr[] = $this->_request['userid'];
                $arr[] = '0';
                $arr[] = self::SECRET;
                break;
            case self::DO_REQUEST_PAYMENT:
                $arr[] = $this->_request['amount'];
                $arr[] = $this->_request['userid'];
                $arr[] = $this->_request['paymentid'];
                $arr[] = self::SECRET;
                break;
        }
        
        $key = md5(implode('', $arr));
        
        if ($key != $this->_request['key']) {
            $this->_log('Запрос не подписан, либо передан неверный ключ');
            return false;
        }
        
        return true;
    }
    
    private function _response($code, $comment = '') {        
        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<result>' . "\n";
        $out .= '<code>' . $code . '</code>' . "\n";
        $out .= '<comment>' . $comment . '</comment>' . "\n";
        $out .= '</result>';
        return $out;
    }
}
