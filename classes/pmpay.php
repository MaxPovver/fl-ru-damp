<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");

/**
 * Класс для пополнения счета через WebMoney по системе Paymaster
 *
 * @see /income/pm.php
 */
class pmpay extends account
{
    const OP_CODE = 10;
    
    const MERCHANT_BILL = 0;
    const MERCHANT_SBR  = 1;
    const MERCHANT_BETA = 2;

    // Сколько минимум секунд ждать подтверждения оплаты после "Invoice Confirmation", чтоб начать проверку статуса.
    const CHECK_INVOICED_LAG = 300;
    
    /**
     * Идентификаторы учетной записи,
     *
     * @var string
     */
    public $merchants = array(
        self::MERCHANT_BILL => 'R109922555324',
        self::MERCHANT_SBR  => 'R977176597701',
        self::MERCHANT_BETA => '8e9d6b16-4f21-4a1c-af24-659827ffaa87'
    );
    
    /**
     * Ключ оплаты
     * 
     * @link /classes/payment_keys.php
     * @var string
     */
    public $key = PM_KEY;
    
    /**
     * Лог
     * @var log
     */
    public $log;
    
    
    /**
     * Учетные данные для автоматических запросов (на проверку статусов и т.п.).
     */
    private $_restUrl      = 'https://paymaster.ru/partners/rest/';
    private $_debugUrl     = '';
    private $_restLogin    = PM_REST_LOGIN;
    private $_restPassword = PM_REST_PASSWORD;
    
    
    /**
     * Генерирует LMI_PAYMENT_NO для передачи в Paymaster.
     *
     * Схема обработки платежей:
     * 1. Не делая новую запись в таблицу pm_payments, получаем через nextval() номер счета.
     * 2. Отправляем юзера в Paymaster с этим номером (параметр LMI_PAYMENT_NO).
     * 3. От Paymaster приходит извещение "Invoice Confirmation": сохраняем его в таблице по тому же LMI_PAYMENT_NO.
     * 4. Делаем периодические проверки статусов.
     */
    function genPaymentNo() {
        global $DB;
        return $DB->val("SELECT nextval('pm_payments_id_seq')");
    }

    /**
     * Костыль для того чтобы забрать PAYMENT_BILL_NO в автоплатежах
     *
     * @param $descr
     */
    static function getPaymentBillNO($descr) {
        return (int) str_replace('PAYMENT_BILL_NO=', '', strstr($descr, 'PAYMENT_BILL_NO='));
    }
    
    /**
     * Обработка запроса "Invoice Confirmation". Проверяем и регистрируем платеж в БД.
     *
     * @param array  $req   параметры запроса.  
     * @return string Сообщение об ошибке
     */
    function prepare($req) {
        global $DB;
        $merchant_type = array_search($req['LMI_MERCHANT_ID'], $this->merchants);
        
        if ( $merchant_type === false ) {
            $error = 'Bad MERCHANT_ID';
        }
        if ( !$this->is_dep_exists($req['PAYMENT_BILL_NO']) ) {
            $error = 'PAYMENT_BILL_NO not exists';
        }
        
        if (!$error) {
            // Регистрируем платеж.
            $sreq = base64_encode(serialize($req));
            $payment_no = (int)$req['LMI_PAYMENT_NO'];
            $max_no = $this->genPaymentNo() - 1; // глянуть получше способ: надо просто узнать текущее значение, увеличивать не обязательно.
            if ($payment_no <= 0 || ($more = $payment_no > $max_no)) {
                $error = 'Bad LMI_PAYMENT_NO' . ($more ? " (more {$max_no})" : '');
            } else {
                $sql = 'INSERT INTO pm_payments (id, account_id, merchant_type, invoice_req) VALUES (?i, ?i, ?, ?)';
                if ( !$DB->query($sql, $req['LMI_PAYMENT_NO'], $req['PAYMENT_BILL_NO'], $merchant_type, $sreq)) {
                    $error = 'Bad LMI_PAYMENT_NO (dup?)';
                }
            }
        }
        
        return $error;
    }
    

    /**
     * Проверяет стаусы платежей, прошедших pmpay::prepared(), но еще не подтвержденных.
     * Если платеж успешно завершен, зачисляем деньги.
     * 
     * @return integer   кол-во обработанных платежей.
     */
    function checkInvoiced() {
        global $DB;
        $this->log = new log('pmpay/checkinvoiced-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        
        $sql = "
          SELECT *
            FROM pm_payments
           WHERE is_canceled = false
             AND billing_id IS NULL
             AND invoiced_time < now() - interval '" . pmpay::CHECK_INVOICED_LAG . " seconds'
           ORDER BY invoiced_time
        ";
        
        if( !($payments = $DB->rows($sql)) ) {
            $payments = array();
        }
        
        foreach ($payments as $pmt) {
            $this->_log("Запрашиваем платеж: {$pmt['id']} (account_id: {$pmt['account_id']}, invoiced_time: {$pmt['invoiced_time']})...");
            if ($pinf = $this->getPaymentByInvoiceID($pmt['id'], $this->merchants[$pmt['merchant_type']])) {
                $this->_log('Получен ответ: ' . http_build_query($pinf, '', '&'));
                switch ($pinf['State']) {
                    case 'COMPLETE' :
                        $this->_log('Зачисляем деньги...');
                        $req = unserialize(base64_decode($pmt['invoice_req']));
                        $req['LMI_SYS_PAYMENT_DATE'] = $pinf['LastUpdateTime'];
                        $req['LMI_SYS_PAYMENT_ID']   = $pinf['PaymentID'];
                        if ($error = $this->_setDeposit($req)) {
                            $this->_log("ERROR: {$error}");
                        }
                        $this->_log('ОК.');
                        break;
                    case 'CANCELLED' :
                        $this->_log('Платеж отменен, ставим флаг...');
                        $sql = "UPDATE pm_payments SET is_canceled = true WHERE id = ?i";
                        $res = $DB->query($sql, $pmt['id']) ? 'ОК.' : 'ERROR: '.pg_last_error();
                        $this->_log($res);
                        break;
                    default :
                        $this->_log("Платеж в статусе {$pinf['State']}, игнорируем.");
                        break;
                }
            }
        }
        
        return count($payment);
    }
    
    /**
     * Проверяем статус возврата денег
     * 
     * @global type $DB 
     */
    function checkRefund() {
        global $DB;
        $this->log = new log('pmpay/checkrefund-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        
        $sql = "
          SELECT pp.payment_id, sp.* FROM sbr_stages_payouts sp
          INNER JOIN sbr_stages ss ON ss.id = sp.stage_id
          INNER JOIN sbr s ON s.id = ss.sbr_id
          INNER JOIN pm_payments pp ON pp.billing_id = s.reserved_id 
          WHERE sp.is_refund = false AND sp.refund_id IS NOT NULL AND sp.completed IS NULL
        ";
        
        if( !($payments = $DB->rows($sql)) ) {
            $payments = array();
        }
        
        foreach ($payments as $pmt) {
            $this->_log("Запрашиваем платеж: {$pmt['id']} (payment_id: {$pmt['payment_id']}, stage_id: {$pmt['stage_id']})...");
            
            if ($pinf = $this->getRefundByPaymentID($pmt['payment_id'])) {
                $this->_log('Получен ответ: ' . http_build_query($pinf, '', '&'));
                $refund = $this->findRefundById($pinf, $pmt['refund_id']); // Ищем из выдачи наш возврат
                if(!$refund) {
                    $this->_log('Ошибка: Платеж не найден в выдаче ' . $pmt['refund_id']);
                    return;
                }
                switch ($refund['Status']) {
                    case 'SUCCESS' :
                        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/pmpay.php';
                        $this->_log('Возврат денег выполнен успешно, обновляем статус');
                        $update = array('is_refund' => true, 'completed' => 'NOW()');
                        sbr_adm::refundStatusUpdate($update, $pmt['id']);
                        $this->_log('ОК.');
                        break;
                    case 'FAILURE' :
                        $this->_log('Платеж отменен, отменяем возврат, ставим соттветствующий флаг');
                        $this->_log('Код ошибки: '. $refund['ErrorCode']);
                        $update = array('is_refund' => null, 'completed' => null);
                        $res = sbr_adm::refundStatusUpdate($update, $pmt['id']) ? 'ОК.' : 'ERROR: '.pg_last_error();;
                        $this->_log($res);
                        break;
                    default :
                        $this->_log("Платеж в статусе {$refund['Status']}, игнорируем.");
                        break;
                }
            }
        }
    }
    
    function findRefundById($refunds, $refund_id) {
        foreach($refunds as $refund) {
            if($refund->RefundID == $refund_id) 
                return (array) $refund;
        }
        return false;
    }
    
    /**
     * Проверяет платеж на дубли и прочее перед зачислением.
     *
     * @param integer $invoice_id   ид. счета (pm_payments.id или LMI_PAYMENT_NO).
     * @return boolean   true: все ок, можно зачилять деньги.
     */
    private function _checkInvoice4Deposit($invoice_id) {
        global $DB;
        
        $err = NULL;
        $pmt = $DB->row('SELECT * FROM pm_payments WHERE id = ?i', $invoice_id);
        
        if (!$pmt) {
            $err = "ERROR: Платеж {$invoice_id} не зарегистрирован.";
        } else if ($pmt['is_canceled'] == 't') {
            $err = "WARNING: Платеж {$invoice_id} был отменен, деньги зачислены не будут.";
        } else if ($pmt['billing_id']) {
            $err = "WARNING: Дубль. По платежу {$invoice_id} деньги уже зачислены: операция {$pmt['billing_id']}.";
        }
        
        if ($err) {
            $this->_log($err);
            return false;
        }
        
        return true;
    }
    
    /**
     * Запрашивает у paymaster информацию о платеже по нашему номеру счета (LMI_PAYMENT_NO).
     *
     * @param integer $invoice_id   ид. счета.
     * @param integer $merchant_id  ид. магазина (мерчанта).
     * @return array
     */
    function getPaymentByInvoiceID($invoice_id, $merchant_id) {
        $res = $this->_send( 'getPaymentByInvoiceID', array('invoiceID' => $invoice_id, 'siteAlias' => $merchant_id) );
        return (array)$res->Payment;
    }
    
    /**
     * Отправляет запрос в Paymaster и возвращает результат.
     *
     * @param string $opname   название операции по системе Paymaster.
     * @param array $aprms   параметры операции (в дополнение к трем обязательным, см. внутрь), по ним же строится хэш.
     * @return object   декодированный json ответ. (note: все структурные элементы -- объекты, а не ассоциативные массивы.)
     */
    private function _send($opname, $aprms) {
        $prms = $aprms;
        $prms['login'] = $this->_restLogin;
        $prms['nonce'] = md5(uniqid(mt_rand(), true));
        $prms['hash']  = base64_encode(sha1($prms['login'].';'.$this->_restPassword.';'.$prms['nonce'].';'.implode(';', $aprms), true));
        
        $query = http_build_query($prms, '', '&');
        if(DEBUG && $this->_debugUrl) {
            $res = $this->getContentSite($this->_debugUrl.'?opname='.$opname.'&'.$query);
        } else {  
            $res = file_get_contents($this->_restUrl.$opname.'?'.$query);
        }
        if (!$res) {
            $this->_log('ERROR: Не удалось получить информацию по платежу.');
            return NULL;
        }
        
        $res = json_decode($res);
        
        switch ($ec = $res->ErrorCode) {
            case 0   : return $res;
            case -1  : $this->_log("ERROR: {$ec}: Неизвестная ошибка. Сбой в системе PayMaster. Если ошибка повторяется, обратитесь в техподдержку."); break;
            case -2  : $this->_log("ERROR: {$ec}: Сетевая ошибка. Сбой в системе PayMaster. Если ошибка повторяется, обратитесь в техподдержку."); break;
            case -6  : $this->_log("ERROR: {$ec}: Нет доступа. Неверно указан логин, или у данного логина нет прав на запрошенную информацию."); break;
            case -7  : $this->_log("ERROR: {$ec}: Неверная подпись запроса. Неверно сформирован хеш запроса."); break;
            case -14 : $this->_log("ERROR: {$ec}: Повторный запрос с тем же nonce."); break;
            default  : $this->_log("ERROR: {$ec}: Неизвестная ошибка."); break;
        }
        
        return NULL;
    }
    
    function getContentSite($url) {
        $header = array('Authorization: Basic '.base64_encode(BASIC_AUTH));
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        ob_start();
        curl_exec($ch);
        $content = ob_get_clean();
        curl_close($ch);
        return $content;
    }
    
    /**
     * Зачисление средств при поступлении "Payment Confirmation" от Paymaster.
     *
     * @see /income/pm.php 
     * @param array  $req   параметры запроса.  
     * @return string Сообщение об ошибке
     */
    function checkdeposit($req) {
        $this->log = new log('pmpay/checkdeposit-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        $this->log->writevar($req);
        
        $hash_str = $req['LMI_MERCHANT_ID'] .';'. $req['LMI_PAYMENT_NO'] .';'. $req['LMI_SYS_PAYMENT_ID']
                  .';'. $req['LMI_SYS_PAYMENT_DATE'] .';'. $req['LMI_PAYMENT_AMOUNT'] .';'. $req['LMI_CURRENCY']
                  .';'. $req['LMI_PAID_AMOUNT'] .';'. $req['LMI_PAID_CURRENCY'] .';'. $req['LMI_PAYMENT_SYSTEM']
                  .';'. $req['LMI_SIM_MODE']
                  .';'. $this->key;
                  
        $hash_str = base64_encode(md5($hash_str, true));
        
        if ($req['LMI_HASH'] == $hash_str) {
            $error = $this->_setDeposit($req);
        } else {
            $error = 'Bad LMI_HASH';
        }
        
        $this->log->writeln($error ? $error : 'YES');
        $this->log->writeln();
        
        return $error;
    }
    
    
    /**
     * Зачисление средств.
     *
     * @param array  $req   параметры платежа.
     * @return string Сообщение об ошибке
     */
    private function _setDeposit($req) {
        global $DB;
        
        if ( !in_array($req['LMI_MERCHANT_ID'], $this->merchants) ) {
            return 'Bad MERCHANT_ID';
        }
        
        if (floatval($req['LMI_PAYMENT_AMOUNT']) <= 0) {
            return 'Bad LMI_PAYMENT_AMOUNT';
        }
        
        $descr = "WM #{$req['LMI_PAYMENT_NO']} (pmnum: {$req['LMI_SYS_PAYMENT_ID']}) на кошелек {$req['LMI_MERCHANT_ID']};"
               . " платежная cистема пользователя #{$req['LMI_PAYMENT_SYSTEM']}: сумма оплаты {$req['LMI_PAID_AMOUNT']} {$req['LMI_PAID_CURRENCY']};"
               . " сумма поступления: {$req['LMI_PAYMENT_AMOUNT']} {$req['LMI_CURRENCY']};"
               . " обработан {$req['LMI_SYS_PAYMENT_DATE']}";
         
        if (!$this->_checkInvoice4Deposit($req['LMI_PAYMENT_NO'])) {
            return NULL;
        }
        
        $op_id = 0;
        $ammount = $req['LMI_PAYMENT_AMOUNT'];
        switch ($req['OPERATION_TYPE']) {
            case sbr::OP_RESERVE: // Резерв денег по СбР
                $op_code = sbr::OP_RESERVE;
                $amm = 0;
                $descr .= ' СбР #'.$req['OPERATION_ID'];
                break;
            default:        // Перевод денег на личный счет
                $op_code = 12;
                $amm = $ammount;
                break;
        }

        $error = $this->deposit($op_id, $req['PAYMENT_BILL_NO'], $amm, $descr, pmpay::OP_CODE, $ammount, $op_code, $req['OPERATION_ID']);
        if($op_id) {
            $sql = "UPDATE pm_payments SET billing_id = ?i, payment_id = ?i WHERE id = ?i";
            $DB->query($sql, $op_id, $req['LMI_SYS_PAYMENT_ID'], $req['LMI_PAYMENT_NO']);
        }
        
        return $error;
    }
    
    
    /**
     * Пишет сообщение в лог.
     * @param string $msg   сообщение.
     */
    private function _log($msg) {
        if(!$this->log) return;
        $this->log->writeln($msg);
    }
    
    /**
     * Возврат денежных средств после резерва
     * 
     * @param type $payment_id  ИД операции в paymaster
     * @param type $ammount     Сумма возврата
     * @return type 
     */
    public function refundPayments($payment_id, $ammount) {
        $this->log = new log('pmpay/refundPayments-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        
        $res = $this->_send('refundPayment', array('paymentID' => $payment_id, 'ammount' => $ammount) );
        return (array)$res->Refund;
    }
    
    /**
     * Запрашиваем операции
     * 
     * @param type $payment_id  ИД операции в paymaster
     * @return type 
     */
    public function getRefundByPaymentID($payment_id) {
        $res = $this->_send('listRefunds', array('paymentID' => $payment_id) );
        return (array)$res->Refunds;
    }
    
    public function setDebugUrl($url) {
        return $this->_debugUrl = $url;
    }
}
