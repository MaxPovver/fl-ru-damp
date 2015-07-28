<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');

class yd_payments {
    
    const VERSION = '2.02';
    const AGENT_ID = 159202;
    const AGENT_NAME = 'ООО "Ваан"';
    const CONTRACT_NUM = 'Б.11111.03';
    
    // Тип исходного платежа
    const SRC_SBR = 1; // Сделка без риска
    
    // Коды операций
    const ACT_PAY       = 1;    // Зачисление
    const ACT_STATUS    = 1001; // Запрос о состоянии зачисления
    const ACT_CHECKPAY  = 1002; // Проверка возможности зачисления
    const ACT_BALANCE   = 1003; // Запрос баланса Агента
    
    // Результаты выполнения операции
    const RES_OK      = 0;  // Успех. Обработка завершена. Операция совершена успешно
    const RES_WAIT    = 1;  // В обработке. Запрос в процессе обработки
    const RES_FAIL    = 3;  // Отвергнуто. Обработка завершена
    const RES_UNKNOWN = 30; // Технические проблемы на стороне Системы
    
    // Код валюты
    const CUR_CD  = 643; // рубли

    const REGISTRY_NOENC_SFX = '_vaan.txt';
    const REGISTRY_PATH   = '/var/tmp/';
    const REGISTRY_DIR    = 'yd_payments';
    const REGISTRY_FROM   = 'payments@free-lance.ru';
    const REGISTRY_YDTO   = 'onlinegate@yamoney.ru';
    
    const PGP_SIGN    = 'gpg --clearsign --always-trust --batch --no-secmem-warning --homedir=/var/www/.gnupg';
    const PGP_ENCRIPT = 'gpg -r ACE74CE2 -a --always-trust --sign --pgp6 --homedir=/var/www/.gnupg --encrypt';
    const PGP_CHECK   = 'gpg --verify --batch --no-secmem-warning --homedir=/var/www/.gnupg';
    
    const BALANCE_MEM_KEY = 'yd_payments.balance()';
    
    private $_address = "https://calypso.yamoney.ru/onlinegates/vaan.aspx";

    private $_src = array();
    private $_pmt = array();
    private $_tr = array();
    private $_ptry = 0;
    private $_pdata;
    private $_answer;
    private $_maxAmt = 15000;

    private $_performedAmt      = 0;
    private $_performedAmtFixed = 0;
    
    private $_isPmtLocked = false;
    
    private $_log;


    // для внешних нужд.
    public $pmt;
    public $tr;

    public $DEBUG;

    static $REGISTRY_VAANTO = array('ey@free-lance.ru', 'kotova@free-lance.ru', 'abbram@mail.ru', 'payments@free-lance.ru');
    
    /**
     * Коды операций
     * 
     * @var array
     */
    static $act_nm = array (
        self::ACT_PAY       => 'Зачисление',
        self::ACT_STATUS    => 'Запрос о состоянии зачисления',
        self::ACT_CHECKPAY  => 'Проверка возможности зачисления',
        self::ACT_BALANCE   => 'Запрос баланса Агента'
    );

    /**
     * Ошибки, возвращаемые при обработке запросов.
     * 
     * @var array
     */
    static $yd_errs = array (
        14 => 'Неверно задана валюта (CUR_CD).',
        16 => 'Неверно задан счет получателя средств (DSTACNT_NR). Ошибка в контрольной сумме или формате номера счета',
        17 => 'Неверно задана сумма (TR_AMT).',
        18 => 'Неверно задан номер транзакции  (TR_NR).',
        20 => 'Неверно задан код операции (ACT_CD).',
        22 => 'Отсутствуют необходимые параметры (кроме SIGN).',
        24 => 'Валюта не соответствует счету.',
        25 => 'Слишком длинный текст контракта.',
        26 => 'Операция с таким номером транзакции (TR_NR), но другими параметрами уже выполнялась.',
        50 => 'Отсутствует подпись (SIGN).',
        51 => 'Подпись не подтверждена (данные подписи не совпадают с данными запроса).',
        53 => 'Запрос подписан неизвестным Системе PGP-ключом.',
        55 => 'Истек срок действия публичного PGP-ключа ИС Агента.',
        56 => 'Неверный формат подписи(SIGN). Подпись не распознана как PGP-sign.',
        40 => 'Счет закрыт.',
        41 => 'Счет в Системе заблокирован. Зачисления на него запрещены.',
        42 => 'Счета с таким номером не существует.',
        43 => 'Превышено ограничение на единовременно зачисляемую сумму.',
        44 => 'Превышено ограничение на максимальную сумму зачислений за период времени. Дождитесь наступления следующих суток и повторите запрос.',
        45 => 'Недостаточно средств для проведения операции. Необходимо перечислить принятые платежи на расчетный счет ПС Яндекс.Деньги.',
        30 => 'Технический сбой на стороне ЯД. Состояние обработки запроса неизвестно.',
        19 => 'В запросе о состоянии зачисления задан номер транзакции (TR_NR), который не обрабатывался.'
    );
    
    /**
     * Конструктор класса
     *
     * @param int $src_id ID исходного платежа
     * @param int $src_type тип исходного платежа
     */
    function __construct($src_id = NULL, $src_type = NULL) {
        $this->DB = new DB('master');
        $this->setSrc($src_id, $src_type);
        $this->_log = new log('yd_payments/%d%m%Y.log');
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
     * Возвращает отформатированное число
     * 
     * @param  float $amt число
     * @return string число с 2 знаками после точки
     */
    private function _amtFmt($amt) {
        return number_format($amt, 2, '.', '');
    }
    
    /**
     * Проверяет сумму платежа ЯД
     * 
     * @return string отформатированное значение суммы платежа, или bool false - ошибка
     */
    private function _initAmt() {
        $tr_amt = $this->_pdata['tr_amt'];
        if(!((float)$tr_amt)) 
            return $this->error('Неверная сумма');
        return ($this->_pdata['tr_amt'] = $this->_amtFmt($tr_amt));
    }
    
    /**
     * Проверяет номер счета (кошелька) ЯД
     *
     * @return string номер кошелька ЯД, или bool false - ошибка
     */
    private function _initAcntNr() {
        $dstacnt_nr = trim($this->_pdata['dstacnt_nr']);
        if(!account::isValidYd($dstacnt_nr))
            return $this->error('Неверный номер кошелька');
        return ($this->_pdata['dstacnt_nr'] = $dstacnt_nr);
    }
    
    /**
     * Проверяет основание для зачисления платежа ЯД
     *
     * @return string основание для зачисления платежа, или bool false - ошибка
     */
    private function _initCont() {
        $cont = trim($this->_pdata['cont']);
        if(!$cont)
            return $this->error('Текст контракта обязателен');
        if(strlen($cont > 128))
            return $this->error('Слишком длинный текст контракта');
        return ($this->_pdata['cont'] = $cont);
    }
    
    /**
     * Создает новую запись платежа ЯД
     * 
     * @return array данные платежа (см. таблицу yd_payments), или bool false - ошибка
     */
    private function _createPayment() {
        if(!$this->_src['id'] || !$this->_src['type'])
            return false;
        if(!$this->_initAmt())
            return false;
        $this->_pmt = $this->DB->row('INSERT INTO yd_payments (src_type, src_id, in_amt, is_locked) VALUES (?i, ?i, ?f, true) RETURNING *', $this->_src['type'], $this->_src['id'], $this->_pdata['tr_amt']);
        $this->_isPmtLocked = ($this->_pmt['is_locked'] == 't');
        return $this->_pmt;
    }
    
    /**
     * Блокирует запись платежа ЯД
     * 
     * @param  bool $lock значение флага блокировки
     * @return array данные платежа (см. таблицу yd_payments), или bool false - ошибка
     */
    private function _lockPayment($lock = true) {
        if(!$this->_src['id'] || !$this->_src['type'])
            return false;
        $lw = $lock ? 'заблокировать' : 'разблокировать';
        $pmt = $this->DB->row('UPDATE yd_payments SET is_locked = ?b WHERE src_type = ?i AND src_id = ?i RETURNING *', $lock, $this->_src['type'], $this->_src['id']);
        if(!$pmt)
            return $this->error("Не удалось {$lw} платеж #{$pmt['id']}! Обратитесь в тех. отдел.");
        $this->_isPmtLocked = $lock;
        return ($this->_pmt = $pmt);
    }
    
    /**
     * Возвращает данные платежа ЯД по исходному платежу
     * @see yd_payments::setSrc
     * 
     * @param  bool $lock нужно ли физически блокировать запись в постгресе
     * @return array данные платежа (см. таблицу yd_payments), или NULL если запись не найдена
     */
    function getPayment($lock = false) {
        if(!$this->_src['id'] || !$this->_src['type'])
            return NULL;
        $pmt = $this->DB->row('SELECT * FROM yd_payments WHERE src_type = ?i AND src_id = ?i'.($lock ? ' FOR UPDATE' : ''), $this->_src['type'], $this->_src['id']);
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
     * Инициализирует операцию
     * 
     * @return array данные платежа ЯД
     */
    private function _initPayment() {
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
            } else {
                $this->_lockPayment(TRUE);
            }
            
            if($ok = ($this->_pmt && !$this->errors))
                $ok = $this->DB->commit();
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
     * Осуществляет операцию Зачисление ЯД
     *
     * @param  float $tr_amt сумма платежа
     * @param  string $dstacnt_nr номер счета (кошелька) ЯД
     * @param  string $cont основание для зачисления платежа
     * @return float зачисленная сумма
     */
    function pay($tr_amt = NULL, $dstacnt_nr = NULL, $cont = NULL) {
        $this->_pdata = array('tr_amt' => $tr_amt, 'dstacnt_nr' => $dstacnt_nr, 'cont' => $cont);
        if($this->_initPayment()) {
            if($this->_initTr())
                $this->_analyzeTr();
            $this->_commitPayment();
        }
        $this->log("Зачислено:\t{$this->_performedAmt}");
        $this->pmt = $this->_pmt;
        $this->tr = $this->_tr ? $this->_tr : $this->_pdata;
        $memBuff = new memBuff();
        $memBuff->delete(self::BALANCE_MEM_KEY);
        return $this->_performedAmt;
    }
    
    /**
     * Осуществляет операцию Запрос о состоянии зачисления
     * 
     * @return string ответ сервера
     */
    function status() {
        if($this->_initPayment()) {
            if($this->_pmt['ltr_id'] && $this->_initTr())
                $this->_send( array('TR_NR'=>$this->_tr['id'], 'ACT_CD'=>self::ACT_STATUS) );
            $this->_commitPayment();
        }
        return $this->_answer;
    }
    
    /**
     * Осуществляет операцию Запрос баланса Агента
     *
     * @param  boolean $nocache   запрещает брать данные из кэша.
     * @return string ответ сервера
     */
    function balance($nocache = false) {
        $memBuff = new memBuff();
        $balance = $nocache ? false : $memBuff->get(self::BALANCE_MEM_KEY);
        if($balance === false) {
            if($answer = $this->_send(array('ACT_CD'=>self::ACT_BALANCE))) {
                $balance = $answer['balance'];
                $memBuff->set(self::BALANCE_MEM_KEY, $balance, 180);
            }
        }
        return $balance;
    }
    
    /**
     * Возвращает последний зафиксированный баланс
     * 
     * @return float
     */
    function _getLstBalance() {
        if($this->_tr && $this->_tr['balance'])
            return $this->_tr['balance'];
       return $this->DB->val('SELECT balance FROM yd_trs ORDER BY id DESC WHERE balance IS NOT NULL LIMIT 1');
    }
    
    /**
     * Проверяет является ли текущая транзакция выполненной
     * 
     * @return bool
     */
    private function _isTrPerformed() {
        return ($this->_tr['performed_dt'] && $this->_tr['act_cd'] == self::ACT_PAY);
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
        if( ($amt = $this->_amtFmt(min($amt, $this->_maxAmt, $this->_remAmt()))) <= 0 )
            return $this->error('Попытка использовать в транзакции нулевую сумму.');
        return true;
    }

    /**
     * Возвращает транзакцию ЯД
     * 
     * @param  int $tr_id ID транзакции
     * @return array
     */
    function getTr($tr_id) {
        if($tr_id)
            return $this->DB->row('SELECT * FROM yd_trs WHERE id = ?i', $tr_id);
        return NULL;
    }
    
    /**
     * Инициализирует транзакцию ЯД
     * 
     * @return array
     */
    private function _initTr() {
        if($this->_pmt['ltr_id'])
            return ($this->_tr = $this->getTr($this->_pmt['ltr_id']));
        if( ! ($this->_initAmt() && $this->_initAcntNr() && $this->_initCont()) )
            return false;
        if( ! $this->_safeAmt($this->_pdata['tr_amt']) )
            return false;
        $this->_tr = $this->_pdata;
        return $this->_createTr();
    }
    
    /**
     * Создает транзакцию ЯД
     *
     * @return array транзакция ЯД или false в случае ошибки
     */
    private function _createTr() {
        if( !$this->_tr )
            return false;
        $this->_tr = $this->DB->row('INSERT INTO yd_trs (payment_id, dstacnt_nr, tr_amt, cont) VALUES (?i, ?, ?f, ?) RETURNING *',
                             $this->_pmt['id'], $this->_tr['dstacnt_nr'], $this->_tr['tr_amt'], $this->_tr['cont']);
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
        $this->_tr['act_cd'] = self::ACT_CHECKPAY;
        $answer = $this->_send( array('TR_NR'=>$this->_tr['id'], 'ACT_CD'=>$this->_tr['act_cd'], 'DSTACNT_NR'=>$this->_tr['dstacnt_nr'], 'TR_AMT'=>$this->_tr['tr_amt'], 'CUR_CD'=>self::CUR_CD) );
        return ! ($this->errors || $answer['err_cd'] || $answer['res_cd']);
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
        if( ! $this->_safeAmt($this->_tr['tr_amt']) )
            return false;

        if($this->_ptry > 10 && $this->DEBUG) {
            return $this->error('Кольцо. Повторите запрос.');
        }

        $this->_ptry++;
        if($this->_checkPayTr($answer)) {
            $this->_tr['act_cd'] = self::ACT_PAY;
            $answer = $this->_send( array('TR_NR'=>$this->_tr['id'], 'ACT_CD'=>$this->_tr['act_cd'], 'DSTACNT_NR'=>$this->_tr['dstacnt_nr'],
                                   'TR_AMT'=>$this->_tr['tr_amt'], 'CUR_CD'=>self::CUR_CD, 'CONT'=>$this->_tr['cont']) );
        }
        if($answer) {
            foreach($answer as $f=>$v)
                $this->_tr[$f] = $v;
            if($this->_commitTr()) {
                $this->_analyzeTr();
            }
        }

        return false;
    }
    
    /**
     * Зачисление ЯД
     * 
     * @return bool true - успех, false - провал
     */
    private function _analyzeTr()
    {
        if(!$this->_tr)
            return false;
        
        // если все выплачено, то выходим.
        if($this->_remAmt() <= 0)
            return true;

        // Если уже что-то выплачено по старому кошельку, но на входе новый кошелек, то выходим.
        // !!! Если убрать эту проверку, то выплаты будут идти на прежний кошелек до тех пор, пока не вылезут ошибки 16, 40-42. В таком
        //     случае будет попытка кинуть деньги на новый кошелек.
        //     Если такая логика подойдет, то нужно в терминальном окне соответствующие предупреждалки выдавать и номера всех кошельков, на которые шли выплаты.
        //     Но как на деле поступать пока неизвестно, поэтому просто выкидываем ошибку, смотрим.
        if($this->_remAmt() < $this->_pmt['in_amt'] && $this->_pdata['dstacnt_nr'] != $this->_tr['dstacnt_nr'])
            return $this->error("Текущий номер кошелька {$this->_pdata['dstacnt_nr']} пользователя не совпадает с тем, на который уже были поступления по данной выплате: {$this->_tr['dstacnt_nr']}.<br/>Необходимо прояснить ситуацию и обратиться в тех. отдел.");

        if($this->_tr['res_cd'] == self::RES_OK) {
            // Платим пока не выплатим всю сумму. Случаи сюда попадания:
            // а) если это самый первый запрос на выплату;
            // б) последняя транзакция не была зафиксирована (performed_dt is null), пытаемся ее повторить;
            // в) зафиксировали, но еще не все выплачено. Тогда создаем новую и доплачиваем.
            return $this->_payTr( $this->_isTrPerformed() );
        }

        if($this->_tr['res_cd'] == self::RES_WAIT) {
            // три раза пытаемся проверить состояние и выходим.
            if($this->_ptry < 3) {
                sleep(1);
                return $this->_payTr();
            }
            return $this->error('Платеж в обработке. Повторите запрос.');
        }

        if($this->_tr['res_cd'] == self::RES_UNKNOWN || $this->_tr['err_cd'] == self::RES_UNKNOWN) {
            $ii = floor((time() - strtotime($this->_tr['req_date'])) / 60);
            $mi = $this->_tr['req_cnt'] <= 4 ? 5 : 30;
            if($ii >= $mi) // переждали рекомендуемый интервал, повторяем запрос.
                return $this->_payTr();
            return $this->error(self::$yd_errs[self::RES_UNKNOWN] . ' Повторите попытку через '.($mi-$ii).' мин.');
        }

        switch( $err_cd = $this->_tr['err_cd'] ) {
            
            ///////// Ошибки, которые не фиксируются в ЯД. Можно повторять в тех же транзакциях.
            
            case 16 :
                if($this->_initAcntNr() && !$this->_ptry) { // делаем одну попытку, на случай если изменили кошелек.
                    $this->_tr['dstacnt_nr'] = $this->_pdata['dstacnt_nr'];
                    return $this->_payTr();
                }
                break;

            case 25 :
                if($this->_initCont() && !$this->_ptry) { // одну попытку.
                    $this->_tr['cont'] = $this->_pdata['cont'];
                    return $this->_payTr();
                }
                break;

            case 26 : // просто выдаем ошибку. Нужно в базе разбираться.
                break;

            case 14 : case 17 : case 18 : case 20 : case 22 :
            case 24 : case 50 : case 51 : case 53 : case 55 :
            case 56 :
                if(!$this->_ptry) // один раз пробуем и выходим.
                    return $this->_payTr();
                break;


            ///////// Для этих кодов нужно НОВЫЕ транзакции создать.

            case 40 :
            case 41 :
            case 42 :
                if(!$this->_ptry || $this->_tr['dstacnt_nr'] != $this->_pdata['dstacnt_nr']) { // пробуем с новым кошельком.
                    $this->_tr['dstacnt_nr'] = $this->_pdata['dstacnt_nr'];
                    return $this->_payTr(true);
                }
                break;

            case 43 :
                $this->_maxAmt = $this->_tr['tr_amt'] * 0.618;  // потом смотрим логи, корректируем значение по умолчанию.
                return $this->_payTr(true);

            case 44 :
                if(date('Ymd') != date('Ymd', strtotime($this->_tr['req_date']))) { // только если наступил след. день.
                    return $this->_payTr(true);
                } 
                break;

            case 45 :
                if( !$this->_ptry && ($cbal = (float)$this->balance(true)) > (float)$this->_getLstBalance() ) { // проверяем, если баланс изменился, то пытаемся повторить (считаем, что счет пополнили).
                    $this->_tr['balance'] = $cbal;
                    return $this->_payTr(true);
                } 
                break;

            default :
                self::$yd_errs[$err_cd] = "Неизвестная ошибка (код: {$err_cd})";
                break;
                
        }

        $this->error( self::$yd_errs[$err_cd] );
    }
    
    /**
     * Отправляет запрос на сервер
     * 
     * @param  array $sign_fields поля для формирования подписи
     * @return array ответ сервера
     */
    private function _send($sign_fields) {
        if(!($sign = $this->_sign($sign_fields)))
            return false;
        $query            = $sign_fields;
        $query['SIGN']    = $sign;
        $query['VERSION'] = self::VERSION;

        $context = array (
            'http' => array (
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($query, '', '&')
        ) );

        foreach($sign_fields as $f=>$v) $prms .= ($i++?', ':'')."{$f}={$v}";
        $this->log("Действие:\t" . self::$act_nm[$query['ACT_CD']] . " [{$prms}]", "\r\n\r\n");
        $this->log("Подпись:\t{$sign}");

        if($this->DEBUG) {
            $context['http']['header'] .= $this->DEBUG['headers'];
            $this->_answer = @file_get_contents($this->DEBUG['address'], false, stream_context_create($context));
        } else {
            $this->_answer = @file_get_contents($this->_address, false, stream_context_create($context));
        }

        $this->log("Ответ:\t{$this->_answer}");

        return $this->_parseAnswer();
    }
        
    /**
     * Формирует подпись для запроса
     *
     * @param  array $fields поля для формирования подписи
     * @return string
     */
    private function _sign($fields) {
        if($fields) {
            $prms = implode('&', $fields);
            if($this->DEBUG)
                return $prms;
            @exec("echo '{$prms}' | ".self::PGP_SIGN, $encrypted, $errorcode);
            if($errorcode == 0)
                return implode(PHP_EOL, $encrypted);
        }
        return $this->error('Ошибка подписи запроса.');
    }
    
    /**
     * Преобразует текст ответа сервера в ассоциативный массив
     * 
     * @return array
     */
    private function _parseAnswer() {
        $answer = NULL;
        if($this->_checkAnswerSign()) {
            preg_match('/^RES_CD=([^\r\n]*)/m', $this->_answer, $m);
            $answer['res_cd'] = $m[1];
            preg_match('/^ERR_CD=([^\r\n]*)/m', $this->_answer, $m);
            $answer['err_cd'] = $m[1];
            preg_match('/^PERFORMED_DT=([^\r\n]*)/m', $this->_answer, $m);
            $answer['performed_dt'] = $m[1] ? $m[1] : NULL;
            preg_match('/^BALANCE=([^\r\n]*)/m', $this->_answer, $m);
            $answer['balance'] = $m[1] ? $m[1] : NULL;
        }
        return $answer;
    }
    
    /**
     * Проверяет подпись ответа сервера
     * 
     * @return bool true - подпись верна, false - провал
     */
    private function _checkAnswerSign() {
        if(!$this->_answer)
            return $this->error('Некорректный ответ от сервера.');
        if($this->DEBUG) {
            if($this->DEBUG['wrong_sign'])
                $errorcode = 1;
        } else {
            @exec("echo '{$this->_answer}' | ".self::PGP_CHECK, $message, $errorcode);
        }
        if ($errorcode > 0)
            return $this->error('PGP-подпись не верна!');
        return true;
    }
    
    /**
     * Фиксирует транзакцию
     * 
     * @return bool true - успех, false - провал
     */
    private function _commitTr() {
        
        if($this->_isTrPerformed()) {
            $this->_performedAmt += $this->_tr['tr_amt'];
        }
        $this->_tr = $this->DB->row('
           UPDATE yd_trs
              SET res_cd = ?i, err_cd = ?i, performed_dt = ?::timestamp without time zone, balance = ?f, act_cd = ?i, tr_amt = ?f, dstacnt_nr = ?,
                  req_cnt = req_cnt + 1, req_date = now()
            WHERE id = ?i
              AND performed_dt IS NULL
           RETURNING *
          ',
          $this->_tr['res_cd'], $this->_tr['err_cd'], $this->_tr['performed_dt'], $this->_tr['balance'], $this->_tr['act_cd'],
          $this->_tr['tr_amt'], $this->_tr['dstacnt_nr'], $this->_tr['id']
        );

        if($this->_tr) {
            if($this->_isTrPerformed()) {
                $this->_performedAmtFixed += $this->_tr['tr_amt'];
            }
            return true;
        }

        return $this->error('Не удалось зафиксировать транзакцию! Обратитесь в тех. отдел.');
    }

    /**
     * Генерирет Реестр принятых платежей
     *
     * @param  string $from_dt начальная дата 
     * @param  string $to_dt конечная дата
     * @return string путь к сгенерированному файлу, или bool false - ошибка
     */
    function createRegistry($from_dt = NULL, $to_dt = NULL) {
        $from_dt = date('Y-m-d', strtotime($from_dt === NULL ? '-1 day' : $from_dt));
        $to_dt   = date('Y-m-d', strtotime($to_dt === NULL   ? $from_dt.' +1 day' : $to_dt));

        $content = 'Agent_ID:'.self::AGENT_ID."\r\n"
                 . 'Agent_name:'.self::AGENT_NAME."\r\n"
                 . 'Contract_number:'.self::CONTRACT_NUM."\r\n";
        $tcnt = 0;
        $tsum = 0;
        $table = '';
        $trs = $this->DB->rows('SELECT * FROM yd_trs WHERE performed_dt >= ?::date AND performed_dt < ?::date AND act_cd = ?i ORDER BY performed_dt', $from_dt, $to_dt, self::ACT_PAY);
        if($trs) {
            foreach($trs as $tr) {
                $dtt = strtotime($tr['performed_dt']);
                $sum = $this->_amtFmt($tr['tr_amt']);
                $tcnt++;
                $tsum += $sum;
                $table .= date('d.m.Y', $dtt)."\t"
                       . date('H:i:s', $dtt)."\t"
                       . $tr['id']."\t"
                       . $tr['dstacnt_nr']."\t"
                       . $sum."\t"
                       . "\r\n";
            }
        }
        $content .= 'Total:'.$tcnt."\t".$this->_amtFmt($tsum)."\r\n";
        if($table) {
            $content .= "Table:Date\tTime\tTransaction\tAccount\tAmount\r\n"
                     .  $table;
        }
        $fname = self::AGENT_ID.'_'.date('Ymd', strtotime($from_dt));
        $fpath = self::REGISTRY_PATH.self::REGISTRY_DIR;
        if(!file_exists($fpath))
            mkdir($fpath);
        $rname = $fpath.'/'.$fname;
        $ne_name = $rname.self::REGISTRY_NOENC_SFX;

        if($fp = fopen($ne_name, 'w')) {
            fwrite($fp, $content);
            fclose($fp);
            @unlink($rname);
            $errorcode = 1;
            if($this->DEBUG) {
                if($ft = fopen($rname, 'w')) {
                    fwrite($ft, $content);
                    fclose($ft);
                    $errorcode = 0;
                }
            } else {
                @exec(self::PGP_ENCRIPT." -o {$rname} {$ne_name}", $encrypted, $errorcode);
            }
            if($errorcode == 0)
                return $rname;
            $this->error('Ошибка шифрования реестра.');
        }

        $this->error('Не удалось сформировать реестр.');
        return false;
    }

    /**
     * Добавляет сообщение в лог
     *
     * @param string $str сообщение
     * @param string $pfx префикс
     */
    function log($str, $pfx = '') {
        $this->_log->writeln($pfx . date('c') . "\t{$str}");
    }
    
    /**
     * Обработка ошибки.
     * Добавляет сообщение об ошибке в массив $this->errors и в лог
     * 
     * @param  string $err сообщение об ошибке
     * @return bool false
     */
    function error($err) {
        $this->errors[] = $err;
        $this->log("Ошибка:\t{$err}");
        return FALSE;
    }
}

