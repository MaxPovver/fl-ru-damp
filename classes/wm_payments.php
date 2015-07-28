<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/payment_keys.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXI.php');

define('WMXI_LOCALE', 'ru_RU');        

class WMXIPayouts extends WMXI {

    public $basic_auth;
    public $debug_url;
    private $_log;
    
    function _request($url, $xml, $scope = '') {
        if(!$this->_log) {
            $this->_log = new log('wm_payments/wm_payments-%d%m%Y.log');
        }
        $this->_log->linePrefix = '%d.%m.%Y %H:%M:%S : ' . getRemoteIP() . " : {$scope} : ";
        
        $res = parent::_request($url, $xml, $scope);
        $this->_log->writeln('REQUEST:');
        $this->_log->writeln($res->GetRequest());
        $this->_log->writeln('RESPONSE:');
        $this->_log->writeln($res->GetResponse());
        
        return $res;
    }

    public function UKG1($name, $passport_serie, $passport_number, $passport_date, $purse, $price) {
        $req = new SimpleXMLElement('<w3s.request/>');

        if ($this->classic) {
            $req->wmid = $this->wmid;
            $req->sign = $this->_sign($this->wmid.$name.$passport_serie.$passport_number.$passport_date.$purse.$price);
            $req->sign->addAttribute('type', 1);
        }
        $group = 'payment';
        $req->$group->name = iconv('CP1251', 'UTF-8', $name);
        $req->$group->passport_serie = iconv('CP1251', 'UTF-8', $passport_serie);
        $req->$group->passport_number = iconv('CP1251', 'UTF-8', $passport_number);
        $req->$group->passport_date = date('Ymd', strtotime($passport_date));
        $req->$group->purse = $purse;
        $req->$group->price = $price;
        $url = $this->debug_url ? $this->debug_url : 'https://transfer.guarantee.ru/AgentInPreXml.aspx';

        return $this->_request($url, $req->asXML(), __FUNCTION__);
    }

    public function UKG2($payment_id, $payment_test, $name, $passport_serie, $passport_number, $passport_date, $purse, $price, $cheque, $date, $kiosk_id, $phone) {
        $req = new SimpleXMLElement('<w3s.request/>');

        if ($this->classic) {
            $req->wmid = $this->wmid;
            $req->sign = $this->_sign($this->wmid.$payment_id.$payment_test.$name.$passport_serie.$passport_number.$passport_date.$phone.$purse.$price.$cheque.$date.$kiosk_id);
            $req->sign->addAttribute('type', 1);
        }
        $group = 'payment';
        $req->$group = NULL;
        $req->$group->addAttribute('id', $payment_id);
        $req->$group->addAttribute('test', $payment_test);
        $req->$group->name = iconv('CP1251', 'UTF-8', $name);
        $req->$group->passport_serie = iconv('CP1251', 'UTF-8', $passport_serie);
        $req->$group->passport_number = iconv('CP1251', 'UTF-8', $passport_number);
        $req->$group->passport_date = date('Ymd', strtotime($passport_date));
        $req->$group->purse = $purse;
        $req->$group->price = $price;
        $req->$group->date = date('Ymd H:i:s', strtotime($date));
        $req->$group->cheque = $cheque;
        $req->$group->kiosk_id = $kiosk_id;
        $req->$group->phone = $phone;
        $url = $this->debug_url ? $this->debug_url : 'https://transfer.guarantee.ru/AgentInXml.aspx';

        return $this->_request($url, $req->asXML(), __FUNCTION__);
    }

    public function UKG3($startdate, $enddate, $test = NULL) {
        $req = new SimpleXMLElement('<w3s.request/>');

        if ($this->classic) {
            $req->wmid = $this->wmid;
            $req->sign = $this->_sign($this->wmid.$startdate.$enddate);
            $req->sign->addAttribute('type', 1);
        }
        $group = 'payment';
        $req->startdate = $startdate;
        $req->enddate= $enddate;
        if($test !== NULL) {
            $req->test= (int)$test;
        }
        $url = $this->debug_url ? $this->debug_url : 'https://transfer.guarantee.ru/AgentInHistory.aspx';

        return $this->_request($url, $req->asXML(), __FUNCTION__);
    }
}

class wm_payments {
    
    // Тип исходного платежа
    const SRC_SBR = 1; // Сделка без риска
    
    // Результаты выполнения операции
    const RES_OK = 0;  // Успех. Обработка завершена. Операция совершена успешно
    
    const WMID = '200477354071';
    const TEST_MODE = 0;
    
    private $_wmkey = array('nkey'=>WM_NKEY, 'ekey'=>WM_EKEY);

    private $_src = array();
    private $_pmt = array();
    private $_tr = array();
    private $_ptry = 0;
    private $_pdata;
    private $_answer;
    private $_maxAmt = 0;

    private $_performedAmt      = 0;
    private $_performedAmtFixed = 0;
    
    private $_isPmtLocked = false;
    
    private $_wmxi;

    public $ignoreLimit = false;
    public $reqConfirm = false;

    // для внешних нужд.
    public $pmt;
    public $tr;

    public $DEBUG;

    public $logOn = false;

    
    /**
     * Конструктор класса
     *
     * @param int $src_id ID исходного платежа
     * @param int $src_type тип исходного платежа
     */
    function __construct($src_id = NULL, $src_type = NULL) {
        $this->DB = new DB('master');
        $this->setSrc($src_id, $src_type);
    }
    
    /**
     * Устанавливает исходный платеж
     * 
     * @param int $src_id ID исходного платежа
     * @param int $src_type тип исходного платежа
     */
    function setSrc($src_id, $src_type) {
        $this->_src = array('id'=>(int)$src_id, 'type'=>(int)$src_type);
    }
    
    /**
     * Возвращает отформатированное число. Если целое, то без точки, иначе после точки минимум два знака.
     * (В описании у них ошибка, пишет, что "10.50 - не верно". Но неверно как раз "10.5".)
     * 
     * @param  float $amt число
     * @return string
     */
    private function _amtFmt($amt) {
        $amt = (string)$amt;
        $amti = (string)(int)$amt;
        if($amt == $amti) {
            return $amti;
        }
        return sprintf('%01.2f', $amt);
    }
    
    
    private function _initData() {
        $ok = true;
        foreach ($this->_pdata as $field=>&$val) {
            switch ($field) {
                case 'cheque' :
                case 'kiosk_id' :
                    break;
                case 'name'            : $ok = $val ? $ok : $this->error('Необходимо указать ФИО получателя.'); break;
                case 'passport_serie'  : $ok = $val ? $ok : $this->error('Необходимо указать серию паспорта получателя.'); break;
                case 'passport_number' : $ok = $val ? $ok : $this->error('Необходимо указать номер паспорта получателя.'); break;
                case 'passport_date' :
                    if($time = strtotime($val)) {
                        $val = date('Ymd', $time);
                    } else {
                        $ok = $this->error('Необходимо указать дату выдачи паспорта получателя.');
                    }
                    break;
                case 'purse' : $ok = $val ? $ok : $this->error('Необходимо указать номер WMR-кошелька получателя.'); break;
                case 'price' :
                    $ok = $this->_safeAmt($val) && $ok;
                    $ok = $val ? $ok : $this->error('Не задана сумма выплаты.');
                    break;
                case 'phone' : 
                    $val = preg_replace('/\D/', '', $val);
                    $ok = $val ? $ok : $this->error('Необходимо указать номер телефона получателя.');
                    break;
            }
        }
        return $ok;
    }
    
    /**
     * Создает новую запись платежа
     * 
     * @return array данные платежа (см. таблицу wm_payments), или bool false - ошибка
     */
    private function _createPayment() {
        if(!$this->_src['id'] || !$this->_src['type'])
            return false;
        $this->_pmt = $this->DB->row(
          'INSERT INTO wm_payments (src_type, src_id, in_amt, is_locked) VALUES (?i, ?i, ?f, true) RETURNING *',
           $this->_src['type'], $this->_src['id'], (float)$this->_pdata['price']);
        $this->_isPmtLocked = ($this->_pmt['is_locked'] == 't');
        return $this->_pmt;
    }
    
    /**
     * Блокирует запись платежа
     * 
     * @param  bool $lock значение флага блокировки
     * @return array данные платежа (см. таблицу wm_payments), или bool false - ошибка
     */
    private function _lockPayment($lock = true) {
        if(!$this->_src['id'] || !$this->_src['type'])
            return false;
        $lw = $lock ? 'заблокировать' : 'разблокировать';
        $pmt = $this->DB->row('UPDATE wm_payments SET is_locked = ?b WHERE src_type = ?i AND src_id = ?i RETURNING *', $lock, $this->_src['type'], $this->_src['id']);
        if(!$pmt)
            return $this->error("Не удалось {$lw} платеж #{$pmt['id']}! Обратитесь в тех. отдел.");
        $this->_isPmtLocked = $lock;
        return ($this->_pmt = $pmt);
    }
    
    /**
     * Возвращает данные платежа по исходному платежу
     * @see wm_payments::setSrc
     * 
     * @param  bool $lock нужно ли физически блокировать запись в постгресе
     * @return array данные платежа (см. таблицу wm_payments), или NULL если запись не найдена
     */
    function getPayment($lock = false) {
        if(!$this->_src['id'] || !$this->_src['type'])
            return NULL;
        $pmt = $this->DB->row('SELECT * FROM wm_payments WHERE src_type = ?i AND src_id = ?i'.($lock ? ' FOR UPDATE' : ''), $this->_src['type'], $this->_src['id']);
        $this->_isPmtLocked = ( $lock && $this->DB->error || $pmt['is_locked'] == 't' );
        return $pmt;
    }
    
    /**
     * Проверяет заблокирована ли запись
     *
     * @return bool
     */
    function isPmtLocked() {
        return $this->_isPmtLocked;
    }
    
    
    /**
     * Устанавливает лимит для данной выплаты. Может устанавливаться бухгалтером, а может автоматически при получении поля limit в ответе.
     *
     * @param  int $user_id UID пользователя
     * @param  int $limit    лимит.
     * @return boolean 
     */
    function setLimit($limit) {
        $limit = (int)$limit;
        if($limit < 0) {
            return false;
        }
        
        $this->_initPayment(false);
        
        if($limit != $this->_pmt['amt_limit']) {
            $sql = 'UPDATE wm_payments SET amt_limit = ?i WHERE id = ?i';
            if($this->DB->query($sql, $limit, $this->_pmt['id'])) {
                $this->_pmt['limit'] = $limit;
            }
        }
        
        $this->_maxAmt = $this->_pmt['amt_limit'];
        if($this->_tr) {
            $this->_safeAmt($this->_tr['price']);
        }
        
        return true;
    }
    
    /**
     * Инициализирует операцию
     * 
     * @return array данные платежа
     */
    private function _initPayment($lock = true) {
        if($this->_pmt)
            return $this->_pmt;

        if($this->DB->start()) {
            $this->_pmt = $this->getPayment(TRUE);
            if($this->isPmtLocked()) {
                $this->DB->rollback();
                return $this->error('Операция по данной выплате уже выполняется...');
            }

            if(!$this->_pmt) {
                $this->_createPayment();
            } else if($lock) {
                $this->_lockPayment(TRUE);
            }
            
            
            if($ok = ($this->_pmt && !$this->errors)) {
                $this->setLimit($this->_pmt['amt_limit']);
                $ok = $this->DB->commit();
            }
            if(!$ok) {
                $this->DB->rollback();
                return false;
            }
        }

        return $this->_pmt;
    }
    
    /**
     * Завершает операцию
     */
    private function _commitPayment() {
        $this->_lockPayment(FALSE);
    }
    
    /**
     * Инициализирует объект WMXI
     */
    private function _initWMXI() {
        if(!$this->_wmxi) {
            $this->_wmxi = new WMXIPayouts($this->DEBUG ? '' : $_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXI.crt', 'windows-1251');
            if($this->DEBUG) {
                $this->_wmxi->debug_url = $this->DEBUG['address'];
                if(defined('BASIC_AUTH')) {
                    $this->_wmxi->basic_auth = BASIC_AUTH;
                }
            }
            $this->_wmxi->Classic(self::WMID, $this->_wmkey);
        }
    }
    
    /**
     * Осуществляет операцию Зачисление
     *
     * @param  float $price сумма платежа
     * @param  string $purse номер счета (кошелька)
     * @param  string $cont основание для зачисления платежа
     * @return float зачисленная сумма
     */
    function pay($name, $passport_serie, $passport_number, $passport_date, $purse, $price, $cheque, $kiosk_id, $phone) {
        $this->_initWMXI();
        $this->_pdata = compact(
          'name', 'passport_serie', 'passport_number', 'passport_date', 'purse', 'price', 'cheque', 'kiosk_id', 'phone'
        );
        if($this->_initPayment()) {
            if($this->_initData() && $this->_initTr()) {
                $this->_analyzeTr();
            }
            $this->_commitPayment();
        }
        $this->log("Зачислено:\t{$this->_performedAmt}");
        $this->pmt = $this->_pmt;
        $this->tr = $this->_tr ? $this->_tr : $this->_pdata;
        return $this->_performedAmt;
    }
    
    /**
     * Осуществляет операцию Запрос баланса Агента
     *
     * @return string ответ сервера
     */
    function balance() {
        // $this->_initWMXI();
        // return $this->_getLstBalance();
    }

    
    /**
     * Не пашет.
     */
    function history($from_date, $to_date, $test = NULL) {
        $this->_initWMXI();
        $from_date = date('Ymd', strtotime($from_date));
        $to_date = date('Ymd', strtotime($to_date));
        $res = $this->_wmxi->UKG3($from_date, $to_date, $test);
    }
    
    /**
     * Возвращает последний зафиксированный баланс
     * 
     * @return float
     */
    function _getLstBalance() {
        if($this->_tr && $this->_tr['rest'])
            return $this->_tr['rest'];
       return $this->DB->val('SELECT rest FROM wm_trs ORDER BY id DESC WHERE rest IS NOT NULL LIMIT 1');
    }
    
    /**
     * Проверяет является ли текущая транзакция выполненной
     * 
     * @return bool
     */
    private function _isTrPerformed() {
        return ($this->_tr['dateupd'] && $this->_tr['retval'] == self::RES_OK);
    }
    
    /**
     * Возвращает разницу между суммой перевода и зачисленными суммами.
     * 
     * @return float
     */
    private function _remAmt() {
        return $this->_amtFmt($this->_pmt['in_amt'] - ($this->_pmt['out_amt'] + $this->_performedAmt));
    }
    
    /**
     * Проверка допустимости суммы транзакции.
     * 
     * @param  float $amt суммы транзакции
     * @return bool true - допустима, false - не допустима
     */
    private function _safeAmt(&$amt) {
        if( $this->_performedAmtFixed != $this->_performedAmt )
            return $this->error('Опасность неверной суммы (сумма выполенных транзакций != сумма зафиксированных транзакций).');
        if( ($amt = $this->_amtFmt(min($amt, $this->_maxAmt > 0 ? $this->_maxAmt : 99999999999, $this->_remAmt()))) <= 0 ) {
            return $this->error('Попытка использовать в транзакции нулевую сумму.');
        }
        return true;
    }

    /**
     * Возвращает транзакцию
     * 
     * @param  int $tr_id ID транзакции
     * @return array
     */
    function getTr($tr_id) {
        if($tr_id)
            return $this->DB->row('SELECT * FROM wm_trs WHERE id = ?i', $tr_id);
        return NULL;
    }
    
    /**
     * Инициализирует транзакцию
     * 
     * @return array
     */
    private function _initTr() {
        if($this->_pmt['ltr_id']) {
            $this->_tr = $this->getTr($this->_pmt['ltr_id']);
            foreach($this->_pdata as $f=>$v) {
                $this->_tr[$f] = $v;
            }
        } else {
            $this->_tr = $this->_createTr();
        }
        if($this->_tr) {
            $this->_tr['req_date'] = date('Ymd H:i:s', strtotime($this->_tr['req_date']));
            $this->_tr['passport_date'] = date('Ymd', strtotime($this->_tr['passport_date']));
        }
        return $this->_tr;
    }
    
    /**
     * Создает транзакцию
     *
     * @return array транзакция или false в случае ошибки
     */
    private function _createTr() {
        if( !$this->_pdata )
            return false;
        $this->_tr = $this->DB->row(
          'INSERT INTO wm_trs (payment_id, name, passport_serie, passport_number, passport_date, purse, price, cheque, kiosk_id, phone)
           VALUES (?i, ?, ?, ?, ?, ?, ?f, ?, ?, ?) RETURNING *',
           $this->_pmt['id'], $this->_pdata['name'], $this->_pdata['passport_serie'], $this->_pdata['passport_number'],
           $this->_pdata['passport_date'], $this->_pdata['purse'], $this->_pdata['price'], $this->_pdata['cheque'], $this->_pdata['kiosk_id'],
           $this->_pdata['phone']
        );
        if($this->_tr) {
            $this->_pmt['ltr_id'] = $this->_tr['id'];
            return $this->_tr;
        }
        return $this->error('Ошибка при создании транзакции.');
    }
    
    /**
     * Проверка возможности зачисления
     * 
     * @param  array $answer возвращает ответ сервера
     * @return bool true - успех, false - провал
     */
    private function _checkPayTr(&$answer) {
        $res = $this->_wmxi->UKG1($this->_tr['name'], $this->_tr['passport_serie'], $this->_tr['passport_number'], $this->_tr['passport_date'], $this->_tr['purse'], $this->_tr['price']);
        $answer = $res->toArray();
        if($res->ErrorCode()) {
            return $this->error($res);
        }
        return !!$answer;
    }
    
    /**
     * Зачисление
     * 
     * @param  bool $new_tr нужно ли создавать новую транзакцию
     * @return bool false
     */
    private function _payTr($new_tr = false) {
        if( $new_tr && !$this->_createTr() )
            return false;
        if( !$this->_tr )
            return false;
        if( $this->_isTrPerformed() )
            return $this->error("Попытка повторить обработанную транзакцию {$this->_tr['id']}. Обартитесь в тех. отдел.");
        if( ! $this->_safeAmt($this->_tr['price']) )
            return false;

        if($this->_ptry > 10 && $this->DEBUG) {
            return $this->error('Кольцо. Повторите запрос.');
        }

        $this->_ptry++;
        $pay_checked = $this->_checkPayTr($answer);
        $limit = $answer['payment']['limit'];
        if($limit && $limit < $this->_tr['price']) {
            $this->setLimit($limit);
            if(!$this->ignoreLimit) {
                $this->reqConfirm = true;
                return $this->error("Выплачиваемая сумма превышает текущий лимит для данного кошелька: <b>{$limit} WMR</b>. Можно выплатить сумму частями.<br />" . 
                                    'Лимиты можно посмотреть здесь: <a href="http://www.guarantee.ru/services/users/addfunds" target="_blank">http://www.guarantee.ru/services/users/addfunds</a><br />'
                                    );
            }
        }
        
        if($pay_checked) {
            $this->_tr['cheque'] = $this->_tr['cheque'] ? $this->_tr['cheque'] : $this->_tr['id'];
            $this->_tr['kiosk_id'] = $this->_tr['kiosk_id'] ? $this->_tr['kiosk_id'] : $this->_tr['id'];
            $res = $this->_wmxi->UKG2(
              $this->_tr['id'], self::TEST_MODE, $this->_tr['name'], $this->_tr['passport_serie'],
              $this->_tr['passport_number'], $this->_tr['passport_date'], $this->_tr['purse'], $this->_tr['price'],
              $this->_tr['cheque'], $this->_tr['req_date'], $this->_tr['kiosk_id'], $this->_tr['phone']
            );
            if($res->ErrorCode()) {
                $this->error($res);
            }
            $answer = $res->toArray();
        }
        if($answer) {
            if($answer['payment']) {
                foreach($answer['payment'] as $f=>$v) {
                    $this->_tr[$f] = iconv('UTF-8', 'CP1251//TRANSLIT', $v);
                }
            }
            $this->_tr['retval'] = $answer['retval'];
            if($this->_commitTr()) {
                // $this->_analyzeTr();
            }
        }

        return false;
    }
    
    /**
     * Делает разные проверки и вызывает процедуру платежа.
     * Может служить как в ЯД: для автоматики повторных попыток после частичной выплаты, серверных и др. проблем. Тогда дополнительно вызвается после commitTr().
     * Но пока вроде не требуется -- на одно действие одна попытка.
     * 
     * @return bool true - успех, false - провал
     */
    private function _analyzeTr() {
        if(!$this->_tr)
            return false;
        
        // если все выплачено, то выходим.
        if($this->_remAmt() <= 0)
            return true;

        // Если уже что-то выплачено по старому кошельку, но на входе новый кошелек, то выходим.
        if($this->_remAmt() < $this->_pmt['in_amt'] && $this->_pdata['purse'] != $this->_tr['purse'])
            return $this->error("Текущий номер кошелька {$this->_pdata['purse']} пользователя не совпадает с тем, на который уже были поступления по данной выплате: {$this->_tr['purse']}.<br/>Необходимо прояснить ситуацию и обратиться в тех. отдел.");

        if(!$this->_ptry) {
            // Платим пока не выплатим всю сумму. Случаи сюда попадания:
            // а) если это самый первый запрос на выплату;
            // б) последняя транзакция не была зафиксирована (dateupd is null), пытаемся ее повторить;
            // в) зафиксировали, но еще не все выплачено. Тогда создаем новую и доплачиваем.
            return $this->_payTr( $this->_isTrPerformed() );
        }
        
    }
    
        
    /**
     * Фиксирует транзакцию
     * 
     * @return bool true - успех, false - провал
     */
    private function _commitTr() {
        if($this->_isTrPerformed()) {
            $this->_performedAmt += $this->_tr['price'];
        }
        
        $this->_tr = $this->DB->row('
           UPDATE wm_trs
              SET retval = ?i, wmtranid = ?, dateupd = ?::timestamp without time zone, rest = ?f,
                  req_cnt = req_cnt + 1, req_date = now(), price = ?f, purse = ?,
                  name = ?, passport_serie = ?, passport_number = ?, passport_date = ?::date,
                  cheque = ?, kiosk_id = ?, phone = ?
            WHERE id = ?i
              AND dateupd IS NULL
           RETURNING *
          ',
          $this->_tr['retval'], $this->_tr['wmtranid'], $this->_tr['dateupd'], $this->_tr['rest'],
          $this->_tr['price'], $this->_tr['purse'],
          $this->_tr['name'], $this->_tr['passport_serie'], $this->_tr['passport_number'], $this->_tr['passport_date'],
          $this->_tr['cheque'], $this->_tr['kiosk_id'], $this->_tr['phone'],
          $this->_tr['id']
        );

        if($this->_tr) {
            if($this->_isTrPerformed()) {
                $this->_performedAmtFixed += $this->_tr['price'];
            }
            return true;
        }

        return $this->error('Не удалось зафиксировать транзакцию! Обратитесь в тех. отдел.');
    }

    /**
     * Добавляет сообщение в лог
     *
     * @param string $str сообщение
     * @param string $pfx префикс
     */
    function log($str, $pfx = '') {
        if($this->logOn)
            echo $pfx . date('c') . "\t{$str}\r\n";
    }
    
    /**
     * Обработка ошибки.
     * Добавляет сообщение об ошибке в массив $this->errors и в лог
     * 
     * @param  string $err сообщение об ошибке
     * @return bool false
     */
    function error($err, $encoding = NULL) {
        if($err instanceof WMXIResult) {
            $r = $err->toObject();
            $this->error("Ошибка: {$r->retval}");
            $this->error($r->retdesc, 'UTF-8');
            if($r->description) {
                $this->error($r->description, 'UTF-8');
            }
            return FALSE;
        }
    
        if($encoding) {
            $err = iconv($encoding, 'CP1251//IGNORE', $err);
        }
        
        $this->errors[] = $err;
        $this->log("Ошибка:\t{$err}");
        return FALSE;
    }
}
