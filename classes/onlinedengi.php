<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/multi_log.php");

/**
 * @todo Убрать лишний код, в связи с изменениями в схеме работы с ДОЛ
 */
class onlinedengi extends account {
    
    const TEST_MODE = 1;
    
    const PAYMENT_SYS = 12;
    
    /**
     * ИД проекта в системе ДеньгиОнлайн
     */
    
    const PROJECT_ID = 1963;

    /**
     * Идентификатор владельца внешней формы
     */
    const SOURCE_ID = 1963;

    /**
     * секретный код
     */
    const SECRET = 'qV$9;u7pjMrtB$spVbL*Z2XI[Nm83c4$0';

    /**
     * Адрес внешнего обработчика
     */
    const REQUEST_URL = 'http://www.onlinedengi.ru/wmpaycheck.php';
    
    const REQUEST_TEST_URL = '/sbr/dengionline_server.php';
    
    
    const PAYOUT_API_URL = 'http://paygate.dengionline.com/apiv2';


    /**
     * Код Webmoney в системе ДеньгиОнлайн
     */
    const WMR = 338;

    /**
     * Код Яндекс.Деньги в системе ДеньгиОнлайн
     */
    const YD = 7;

    /**
     * Код Банковского перевода для физлиц  в системе ДеньгиОнлайн
     */
    const BANK_FL = 115;

    /**
     * Код Банковского перевода  для юрлиц в системе ДеньгиОнлайн
     */
    const BANK_YL = 116;

    /**
     * Код Пластика  в системе ДеньгиОнлайн
     */
    const CARD = 263;


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
     * Внешняя форма. 
     * Пользователь перенаправляется на страницу оплаты системы ДеньгиОнлайн
     */
    const REQUEST_FORM_EXT = 'external';

    /**
     * Тип интерфейса оплаты - фрейм
     * Форма оплаты будет показана внутри фрейма.
     * Ссылка на страницу для этого фрейма генерится удаленно
     */
    const REQUEST_FORM_IFRAME = 'inline';

    /**
     * Коды доступных платежных методов
     * @var array 
     */
    public static $paysystems = array(
        self::BANK_FL => 'Банковский перевод',
        self::BANK_YL => 'Банковский перевод',
        self::WMR => 'Webmoney, рубли',
        self::YD => 'Яндекс.Деньги',
        self::CARD => 'Банковская карта',
    );
    
    public static $form_types = array(
        self::BANK_FL => self::REQUEST_FORM_EXT,
        self::BANK_YL => self::REQUEST_FORM_EXT,
        self::WMR => self::REQUEST_FORM_EXT,
        self::YD => self::REQUEST_FORM_EXT,
        self::CARD => self::REQUEST_FORM_EXT,
    );


    /**
     * @var DB 
     */
    private $_db;
    
    /**
     *
     * @var integer 
     */
    private $_action;
    
    /**
     *
     * @var array 
     */
    private $_request;
    
    /**
     *
     * @var log 
     */
    private $_log;

    /**
     * @var Dengionline 
     */
    private static $_instance;
    
    public static $req_descr = array(
        self::DO_REQUEST_PAYMENT => 'payment',
        self::DO_REQUEST_CHECKIN => 'checkin',
        self::DO_REQUEST_SUCCESS => 'success',
        self::DO_REQUEST_FAILURE => 'failure',
    );

    public static function init($src = null, $debug = FALSE, $request = array()) {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c($src, $debug, $request);
        }

        return self::$_instance;
    }

    private function __construct($src, $debug, $request) {
        $this->_db = new DB('master');
        $this->_action = $src;
        $this->_request = $request;
        
        foreach ($this->_request as $k => $v) {
            switch ($k) {
                case 'userid':
                case 'nickname':
                case 'paymentid':
                case 'paymode':
                    $v = __paramValue('int', $v);
                    break;
                case 'amount':
                    $v = __paramValue('money', $v);
                    break;
                default:
                    $v = __paramValue('string', $v);
            }
            $this->_request[$k] = $v;
        }
        
//        if ($debug) {
            $this->_log = new log('onlinedengi/income-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ' . self::$req_descr[$this->_action] . ' : ');
            $this->_log->addAlternativeMethodSave(new log_pskb(), true);
            $alt_out = array(
                'param'       => self::$req_descr[$this->_action],
                'response'    => $this->_request
            );
            $this->_log->setAlternativeWrite(serialize($alt_out), 'log_pskb');
//            $dbg = var_export($this->_request, true);
            $this->_log->writevar($this->_request);
//        }
    }

    /**
     * Уникальный номер платежа, передаваемый в деньгионлайн
     * 
     * @return type 
     */
    public static function getPaymentId() {
        global $DB;
        return $DB->val("SELECT nextval('do_payments_id_seq')");
    }
    
    /**
     * Создает новый платеж во временной таблице
     * 
     * @param type $id
     * @param type $account_id
     * @param type $op_code
     * @return type
     */
    public function preparePayment($account_id, $op_code, $order_id) {
        
        $id = $this->_db->insert('do_payments', array(
            'account_id' => $account_id,
            'op_code' =>    $op_code,
            'order_id' => $order_id,
        ), 'id');
        
        $res['nickname'] = $id;
        
        return $res;
    }

    public function signRequest($params) {
        return md5(
                self::SECRET .
                $params['project'] .
                $params['amount'] .
                $params['mode_type'] .
                $params['source'] .
                $params['order_id']
        );
    }
    
    
    public function handleRequest() {
        
        switch ($this->_action) {
            case self::DO_REQUEST_PAYMENT:
//                return $this->_processPayment();
                break;
            case self::DO_REQUEST_CHECKIN:
//                return $this->_processCheck();
                break;
            case self::DO_REQUEST_SUCCESS:
                $pmt = $this->_getPayment($this->_request['nickname']);
                if (!$pmt) {
                    header_location_exit('/404.php');
                }
                $pskb = new pskb();
                $pskb->upLC(array('refund' => 1, 'dol_paymentid' => $this->_request['paymentid']), $pmt['lc_id']);
                header_location_exit('/'.sbr::NEW_TEMPLATE_SBR.'/?site=reserve&id=' . $pmt['sbr_id']);
                break;
            case self::DO_REQUEST_FAILURE:
                $pmt = $this->_getPayment($this->_request['nickname']);
                if (!$pmt || ( $pmt['state'] != 'new' && $pmt['state'] != 'err' ) ) {
                    header_location_exit('/404.php');
                }
                if($this->_request['duplicate'] == 1) {
                    $pskb = new pskb();
                    $pskb->upLC(array('sended' => 1, 'dol_paymentid' => $this->_request['paymentid']), $pmt['lc_id']);
                } else {
                    if($this->_request['refund'] == 1) {
                        $pskb = new pskb();
                        $pskb->upLC(array('refund' => 1, 'dol_paymentid' => $this->_request['paymentid']), $pmt['lc_id']);
                    } else {
                        $pskb = new pskb();
                        $pskb->upLC(array('dol_paymentid' => $this->_request['paymentid']), $pmt['lc_id']);
                    }
                    $pskb = new pskb();
                    $pskb_lc = $pskb->checkLC($pmt['lc_id']);
                    if($pskb_lc->state != pskb::STATE_COVER) {
                        $reason = __paramValue('string', $this->_request['process_message']);
                        $this->_clearPayment($pmt['lc_id'], $reason);
                    }
                }
                header_location_exit('/'.sbr::NEW_TEMPLATE_SBR.'/?site=reserve&id=' . $pmt['sbr_id']);
                break;
            default:
                
                break;
        }
    }
    

    /**
     * Проверяет, был ли ранее выставлен счет с указанными реквизитами.
     * если да, то обновляет запись
     * 
     * @return type
     */
    private function _processCheck() {
        $this->_log('Проверка счета', $this->_request);
        
        if (!$this->_validate()) {
            return $this->_response('NO', 'Запрос не подписан');
        }
        
        $res = $this->_db->query('UPDATE pskb_lc SET dol_lastcheck = NOW() WHERE lc_id = ? AND dol_paymentid IS NULL', 
            $this->_request['userid']);
        
        if (!$res || !pg_affected_rows($res)) {
            return $this->_response('NO', 'Счет не найден.');
        }
        
        return $this->_response('YES');
    }
    
    
    private function _getPayment($id) {
        $pmt = $this->_db->row('SELECT * FROM pskb_lc WHERE lc_id = ?i', $id);        
        return $pmt;
    }
    
    
    private function _clearPayment($id, $reason = '') {
        $pmt = $this->_db->update('pskb_lc', array(
//            'lc_id' => null,
            'state' => 'err',
            'stateReason' => $reason,
            'dol_is_failed' => true,
        ), 'lc_id = ?i AND state IN(?l)', $id, array('new', 'err'));        
        return $pmt;
    }
    
    /**
     * Зачисление средств.
     *
     * @return string Ответ
     */
    private function _processPayment() {
        $this->_log('Извещение о зачислении средств', $this->_request);
        
        if (!$this->_validate()) {
            return $this->_response('NO', 'Запрос не подписан, либо передан неверный ключ');
        }
        
        $time = time();
        $date = date('c', $time);
        
        $descr = "ДеньгиОнлайн #{$this->_request['userid']} (paymentid: {$this->_request['paymentid']});"
               . " платежная cистема пользователя #{$this->_request['paymode']}: сумма оплаты {$this->_request['amount']} руб.;"
               . " обработан {$date}";
        
        $pmnt = $this->_getPayment($this->_request['userid']);
        if (!$pmnt) {
            $err = "Платеж {$this->_request['userid']} не зарегистрирован.";
            $this->_log("ERROR: {$err}");
            return $this->_response('NO', $err);
        } elseif ($pmnt['dol_is_failed'] == 't') {
            $err = "Платеж {$this->_request['userid']} был отменен, деньги зачислены не будут.";
            $this->_log("WARNING: {$err}");
            return $this->_response('NO', $err);
        } elseif ($pmnt['dol_paymentid']) {
            $this->_log("WARNING: Дубль. По платежу {$this->_request['userid']} деньги уже зачислены: идентификатор платежа {$pmnt['dol_paymentid']}.");
            return $this->_response('YES');
        }
        
//        $op_id = 0;
//        $ammount = $this->_request['amount'];
//        switch ($pmnt['op_code']) {
//            case sbr::OP_RESERVE: // Резерв денег по СбР
//                $op_code = sbr::OP_RESERVE;
//                $amm = 0;
//                $descr .= ' СбР #'.$this->_request['userid_extra'];
//                break;
//            default:        // Перевод денег на личный счет
//                $op_code = 12;
//                $amm = $ammount / EXCH_OD;
//                break;
//        }
        
//        if (self::TEST_MODE == 1) {
//            $var['deposit_data'] = array($op_id, $pmnt['account_id'], $amm, $descr, onlinedengi::PAYMENT_SYS, $ammount, $op_code, $this->_request['userid_extra']);
//            $var['query'] = "UPDATE do_payments SET billing_id = ?i, paymentid = ?, paymode = ?, raw_data = ?, complete_time = now() WHERE id = ?i";
//            $var['query_data'] = array("UPDATE do_payments SET billing_id = ?i, paymentid = ?, paymode = ?, raw_data = ?, complete_time = now() WHERE id = ?i", 
//                    $op_id, $this->_request['paymentid'], $this->_request['paymode'], json_encode($this->_request), $this->_request['userid']);
//            
//            $this->_log(null, $var);
//            
//            return $this->_response('YES');
//        }

//        $error = $this->deposit($op_id, $pmnt['account_id'], $amm, $descr, onlinedengi::PAYMENT_SYS, $ammount, $op_code, $this->_request['userid_extra']);
        
        $sql = "UPDATE pskb_lc SET dol_paymentid = ?, dol_raw_resp = ?, dol_completed = now() WHERE lc_id = ?i";
        $res = $this->_db->query($sql, $this->_request['paymentid'], json_encode($this->_request), $this->_request['orderid']);
//        if(!$res) {
//            return $this->_response('NO', 'Ошибка. Платеж не проведен.');
//        }
        
        return $this->_response('YES');
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
        $out = '';
        $out .= '<?xml version="1.0" encoding="UTF-8"?>';
        $out .= '<code>' . $code . '</code>';
        
        if ($comment) {
            $out .= '<comment>' . $comment . '</comment>';
        }
        
        $out .= '<result>';
        $out .= '</result>';
        
        return $out;
    }
    
    private function _log($message = '', $var = null) {
        if (!$this->_log) {
            return;
        }
        if ($message) {
            $this->_log->writeln($message);
        }
        if ($var) {
            $this->_log->writevar($var);
        }
    }
    
}
