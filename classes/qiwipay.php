<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
/**
 *
 * Пополнение счета через QIWI.кошелек
 *
 */
class qiwipay
{
    /**
     * Минимальная сумма счета.
     */
    const MIN_SUM = 1;

    /**
     * Максимальная сумма счета.
     */
    const MAX_SUM = 15000;

    /**
     * Код платежной системы (см. account_operations.payment_sys).
     */
    const PAYMENT_SYS = 9;
    
    /**
     * Количество телефонных номеров с помощью которых можно пополнять счет (0 если не ограничено).
     */
    const MAX_PHONE_NUM = 5;

    
    const STATUS_ACCEPTED = 50;
    const STATUS_PROCESS  = 52;
    const STATUS_COMPLETED = 60;
    const STATUS_TERMINAL_ERROR = 150;
    const STATUS_CANCELED = 160;
    const STATUS_EXPIRED  = 161;
    
    /**
     * Регистрировать клиента, если он еще не был зарегистрирован?
     * @var integer
     */
    public $create_agt = 1;

    /**
     * Время жизни счета в часах (0 -- максимум=45 суток).
     * @var integer
     */
    public $ltime = 0;

    /**
     * Уведомлять клиента по SMS об успешно выставленном счете?
     * @var integer
     */
    public $alarm_sms = 0;

    /**
     * Уведомлять клиента звонком об успешно выставленном счете?
     * @var integer
     */
    public $accept_call = 0;

    /**
     * Массив с полями введенных данных (из html-формы)
     * @var array
     */
    public $form;
    
    /**
     * Ид. пользователя при выставлении счета.
     * @var integer
     */
    public $uid;


    public $login  = '7458';
    public $passwd = QIWI_PASSWD;
    
    public $encode = 'windows-1251'; 
    public $url = 'https://ishop.qiwi.ru/xmlcp';
    

    private $DB;
    private $_cookie_key;

    private $_errors = array(
      300 => 'Неизвестная ошибка',
      13  => 'Сервер занят. Повторите запрос позже',
      150 => 'Неверный логин или пароль',
      215 => 'Счёт с таким номером уже существует',
      278 => 'Превышение максимального интервала получения списка счетов',
      298 => 'Агент не существует в системе',
      330 => 'Ошибка шифрования',
      370 => 'Превышено макс. кол-во одновременно выполняемых запросов'
    );
    
    /**
     * Максимальное число платежей, совершаемых с одного номера абонента определенного оператора.
     * ID оператора => Максимальное число платежей в час 
     * Или пустой массив если нет ограничений.
     * 
     * @var array
     */
    private $aOperatorLimit = array(
        3 => 20
    );
    
    /**
     * Конструктор класса
     * 
     * @param int $uid Ид. пользователя при выставлении счета.
     */
    function __construct($uid = NULL) {
        $this->DB = $GLOBALS['DB'];
        $this->uid = $uid;
        if($this->uid) {
            $this->_cookie_key = 'QIWI' . $this->uid;
            $this->getBillForm();
        }
    }
    
    /**
     * Проверяет параметры запроса для выставления счета в системе.
     *
     * @param  array $request параметры ($_POST).
     * @param  int $account_id ID счета.
     * @return array $error массив ошибок.
     */
    function validate( $request, $account_id ) {
        $error = NULL;
        foreach($request as $f=>$v) {
            $err = NULL;
            $v = htmlspecialchars(stripslashes(trim($v)));
            switch($f) {
                case 'phone' :
                    if(!preg_match('/^\d{10}$/', $v))
                        $err = 'Неверный формат';
                    break;
                case 'sum' :
                    setlocale(LC_ALL, 'en_US.UTF-8');
                    $v = floatval($v);
                    if($v > self::MAX_SUM)
                        $err = 'Слишком большая сумма';
                    else if($v < self::MIN_SUM)
                        $err = 'Минимальная сумма &mdash; ' . self::MIN_SUM . ' руб.';
                    break;
                case 'comment' :
                    $v = substr($v, 0, 255);
                    break;
//                case 'rndnum':
//                    $cap = new captcha();
//                    if(!$cap->checkNumber($v)) $err = 'Код введен неверно';
//                    break;
            }
            if($err) $error[$f] = $err;
            $this->form[$f] = $v;
        }
        
        // различные ограничения по телефонному номеру
        if ( !$error['phone'] ) {
            $bFound = false;
        	$aPhone = $this->DB->rows( 'SELECT * FROM qiwi_phone WHERE account_id = ?i', $account_id );
    	    
    		foreach ( $aPhone as $aCurrPhone ) {
    			if ( $request['phone'] == $aCurrPhone['phone'] ) {
    				$bFound = true;
    				break;
    			}
    		}
        	
        	// 1. ограничиваем количество используемых телефонных номеров
        	if ( self::MAX_PHONE_NUM > 0 && count($aPhone) >= self::MAX_PHONE_NUM ) {
        		if ( !$bFound ) {
        			$error['max_phone_num'] = 1;
        		}
        	}
        	
        	// 2. ограничиваем количество платежей с одного номера
        	$memBuff = new memBuff();
        	$sKey    = 'qiwiPhone'.$account_id.'_'.$aCurrPhone['phone'];
        	$aData   = $memBuff->get( $sKey );
        	$nStamp  = time();
            
        	if ( !$error['max_phone_num'] && count($this->aOperatorLimit) && $aData && $bFound && $aCurrPhone['operator_id'] ) {
        	    if ( isset($aData['wait']) && $nStamp < $aData['wait'] ) {
        	    	$nLast = $aData['wait'] - $nStamp;
        	    	$sLast = '';
        	    	
        	    	if ( $nLast > 60 ) {
        	    		$nTime  = ceil( $nLast / 60 );
        	    		$nLast %= 60;
        	    		$sLast .= $nTime > 0 ? $nTime . ' ' . ending( $nTime, 'минуту', 'минуты', 'минут' ) : '';
        	    	}
        	    	
        	    	if ( $nLast >= 1 ) {
        	    		$sLast .= ($sLast ? ' и ' : '') . $nLast . ' ' . ending( $nLast, 'секунду', 'секунды', 'секунд' );
        	    	}
        	    	
        	    	$error['max_pay_num'] = 'Количество платежей с номера '. $aCurrPhone['phone'] .' за последний час<br/> превысило допустимое число. Повторите попытку через '.$sLast;
        	    }
        	    else {
                	foreach ($this->aOperatorLimit as $nOpID => $nMaxPay) {
                		if ( $aCurrPhone['operator_id'] == $nOpID && $nStamp - $aData['time'] <= 3600 && $aData['cnt'] >= $nMaxPay ) {
                		    $aData['wait'] = $nStamp + 3540;
                		    $memBuff->set( $sKey, $aData, 3600 );
                		    $error['max_pay_num'] = 'Количество платежей с номера '. $aCurrPhone['phone'] .' за последний час<br/> превысило допустимое число. Повторите попытку через 1 час';
                    	}
                    }
        	    }
        	}
        }
        //---------------------------------------
        
        return $error;
    }

    /**
     * Создает новый счет, отправляет на регистрацию в платежную систему.
     *
     * @param integer $uid    ид. пользователя.
     * @param array $request    параметры ($_POST).
     * @return array $error   массив ошибок.
     */
    function createBill($request) {
        if ( !$this->uid ) return 'Пользователь не определен';
        
        $account = new account();
        $account->GetInfo( $this->uid, true );
        
        if ( $error = $this->validate($request, $account->id) ) return $error;
        
		$this->DB->start();
		
		$aData = array(
			'account_id' => $account->id,
			'phone'      => $this->form['phone'],
			'sum'        => $this->form['sum']
		);
		
		$id = $this->DB->insert("qiwi_account", $aData, "id");
        $oper_xml = '';
        switch($request['oper_code']) {
            case 'megafon':
                $oper_xml = '<extra name="megafon2-acc">1</extra>';
                break;
            case 'mts':
                $oper_xml = '<extra name="mts-acc">1</extra>';
                break;
            case 'beeline':
                $oper_xml = '<extra name="beeline-acc">1</extra>';
                break;
        }
		
        if ($id) {
            $xml = '<?xml version="1.0" encoding="' . $this->encode . '"?>'
                 . '<request>'
                 . '<protocol-version>4.00</protocol-version>'
                 . '<request-type>30</request-type>'
                 . '<extra name="password">' . $this->passwd . '</extra>'
                 . '<terminal-id>' . $this->login . '</terminal-id>'
                 . '<extra name="txn-id">' . $id . '</extra>'
                 . '<extra name="to-account">' . $this->form['phone'] . '</extra>'
                 . '<extra name="amount">' . $this->form['sum'] . '</extra>'                 
                 . '<extra name="comment">' . $this->form['comment'] . '</extra>'
                 . '<extra name="create-agt">' . $this->create_agt . '</extra>'
                 . '<extra name="ltime">' . $this->ltime . '</extra>'
                 . '<extra name="ALARM_SMS">' . $this->alarm_sms . '</extra>'
                 . '<extra name="ACCEPT_CALL">' . $this->accept_call . '</extra>'
                 . $oper_xml
                 . '</request>';
            if($this->passwd=='debug') {
                $result = '<response><result-code fatal="false">0</result-code></response>';
            }
            else {
                $result = $this->_request($xml);
            }
            if($err = $this->_checkResultError($result)) {
                $error['qiwi'] = $err;
                $this->DB->rollback();
                die;
                return $error;
            }
            
            // различные ограничения по телефонному номеру
            unset( $aData['sum'] );
            
            $sCode = substr( $aData['phone'], 0, 3 );
    		$sNum  = substr( $aData['phone'], 3 );
    		$sOper = $this->DB->val( 'SELECT COALESCE(operator_id, 0) FROM mobile_operator_codes 
                WHERE code = ? AND ? >= start_num AND ? <= end_num', 
                $sCode, $sNum, $sNum 
    		);
            
    		$aData['operator_id'] = $sOper;
    		
            $this->DB->insert( 'qiwi_phone', $aData );
            
        	$memBuff = new memBuff();
        	$nStamp  = time();
        	$sKey    = 'qiwiPhone' . $account->id . '_' . $aData['phone'];
        	
        	if ( !$aData = $memBuff->get($sKey) ) {
        		$aData = array( 'time' => $nStamp, 'cnt' => 0 );
        	}
        	
        	$aData['time'] = ( $aData['time'] + 3600 > $nStamp ) ? $aData['time']    : $nStamp;
        	$aData['cnt']  = ( $aData['time'] + 3600 > $nStamp ) ? $aData['cnt'] + 1 : 1;
        	
        	$memBuff->set( $sKey, $aData, 3600 );
        	//-----------------------------------
        }
        $this->DB->commit();
        $this->saveBillForm();
        return 0;
    }


    /**
     * Сохраняет введенные данные в куки.
     */
    function saveBillForm() {
        foreach($this->form as $key=>$val)
            setcookie("{$this->_cookie_key}[{$key}]", $val, time()+60*60*24*60, "/", $GLOBALS['domain4cookie'], COOKIE_SECURE);
    }

    /**
     * Берет из куки данные для формы.
     */
    function getBillForm() {
        $this->form = $_COOKIE[$this->_cookie_key];
        $this->form['sum'] = '';
    }
    

    /**
     * Проверяет статусы выставленных счетов. Пополняет счет для оплаченных.
     *
     * @param string $error   сюда пишет ошибку.
     * @return integer   количество пополненных счетов; 
     */
    function checkBillsStatus(&$error = NULL) {
        $offset = 0;
        $limit = 300;
        $completed_cnt = 0;

        libxml_disable_entity_loader();
        $error = NULL;
        $sql = 'SELECT * FROM qiwi_account OFFSET '.$offset.' LIMIT '.$limit;
        if(!($res = $this->DB->query($sql)) || !pg_num_rows($res)) return 0;

        while(pg_num_rows($res)) {
            $curr_bills = array();
            $xml = '<?xml version="1.0" encoding="' . $this->encode . '"?>'
                 . '<request>'
                 . '<protocol-version>4.00</protocol-version>'
                 . '<request-type>33</request-type>'
                 . '<extra name="password">' . $this->passwd . '</extra>'
                 . '<terminal-id>' . $this->login . '</terminal-id>'
                 . '<bills-list>';


            while($row = pg_fetch_assoc($res)) {
                $xml .= '<bill txn-id="' . $row['id'] . '" />';
                $curr_bills[$row['id']] = $row;
            }

            $log = new log('qiwipay/qiwi-%d%m%Y.log');
            $log->writeln();
            $log->writeln(date('c'));
            $log->writeln('===============================');

            $xml .= '</bills-list></request>';
            
            $log->writeln($xml);
            
            $result = $this->_request($xml);
            
            $log->writeln($result);

            if(!$result || ($error = $this->_checkResultError($result)))
                return 0;

            $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>'.$result);
            $bills = array();

            foreach ($xml->{'bills-list'}->children() as $bill) {
                $status = (string)$bill['status'];
                $id = (string)$bill['id'];
                switch($status) {
                    case self::STATUS_ACCEPTED :
                    case self::STATUS_PROCESS :
                        continue 2;
                    case self::STATUS_TERMINAL_ERROR :
                    case self::STATUS_EXPIRED :
                    case self::STATUS_CANCELED :
                        $this->deleteBill($error, $id);
                        break;
                    case self::STATUS_COMPLETED :
                        $this->completeBill($error, $curr_bills[$id], $bill['sum']);
                        if(!$error) $completed_cnt++;
                        break;
                    default :
                        $this->updateBillStatus($error, $id, $status);
                        break;
                }
            }


            $offset = $offset+$limit;
            $sql = 'SELECT * FROM qiwi_account OFFSET '.$offset.' LIMIT '.$limit;    
            $res = $this->DB->query($sql);
        }


        return $completed_cnt;
    }

    /**
     * Обновляет статус счета согласно статусу, выданному платежной системой.
     *
     * @param string $error   сюда пишет ошибку.
     * @param integer $id   ид. счета (qiwi_account.id)
     * @param integer $status   статус
     * @return boolean   успех?
     */
    function updateBillStatus(&$error, $id, $status) {
        if (!$this->DB->query("UPDATE qiwi_account SET status = ? WHERE id = ?", $status, $id)) {
            $error = $this->DB->error;
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Пополняет FM-счет.
     *
     * @param string $error   сюда пишет ошибку.
     * @param array $bill   qiwi-счет
     * @param float $sum   сумма попоплнения
     * @return boolean   успех?
     */
    function completeBill(&$error, $bill, $sum) {
        $account = new account();
        $descr = "Пополнение через систему QIWI.кошелек -- сумма: {$sum} руб., телефон: {$bill['phone']}, счет #{$bill['id']}";
        $error = $account->deposit($op_id, $bill['account_id'], $sum, $descr, self::PAYMENT_SYS, $sum, 12);
        if($error) return false;
        return $this->deleteBill($error, $bill['id']);
    }

    /**
     * Удаляет запись со счетом.
     *
     * @param string $error   сюда пишет ошибку.
     * @param integer $id   ид. счета (qiwi_account.id)
     * @return boolean   успех?
     */
    function deleteBill(&$error, $id) {
        if (!$this->DB->query("DELETE FROM qiwi_account WHERE id = ?", $id)) {
            $error = $this->DB->error;
            return false;
        }
        return true;
    }

    /**
     * Если платежная система сообщает об ошибке, то возвращет текст ошибки
     *
     * @param string $result   ответ системы (xml-строка)
     * @return string   пусто или текст ошибки.
     */
    function _checkResultError($result) {
        libxml_disable_entity_loader();
    	if ($result){
            libxml_use_internal_errors(true);
            $rxml = simplexml_load_string('<?xml version="1.0" encoding="' . $this->encode . '"?>' . $result);
            
            if ($rxml === FALSE) {
                $this->_log_errors(libxml_get_errors(), $result);
                libxml_clear_errors();
                
                $rc = "";

            } else {
                $rc = $rxml->{'result-code'};
            }
            
    	} else $rc = "";
        return $this->_errors[(string)$rc];
    }

    /**
     * Отправляет запрос в платежную систему
     *
     * @param string $xml   тело запроса (xml-строка)
     * @return string   ответ системы (xml-строка)
     */
    function _request($xml) {
    	$body = $this->_encrypt($xml);

    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'text/xml; encoding=' . $this->encode));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
    
    /**
     * Возвращает зашифрованную строку запроса для передачи в платежную систему, согласно протоколу.
     *
     * @param string $xml   тело запроса (xml-строка)
     * @return string   готовая строка запроса.
     */
    function _encrypt($xml) { 
        $passwordMD5 = md5($this->passwd, TRUE);
    	$salt = md5($this->login . bin2hex($passwordMD5), TRUE);
    	$key = str_pad($passwordMD5, 24, '\0');

    	for ($i = 8; $i < 24; $i++) {
    		if ($i >= 16) {
    			$key[$i] = $salt[$i-8];
    		} else {
    			$key[$i] = $key[$i] ^ $salt[$i-8];
    		}
    	}

    	$n = 8 - strlen($xml) % 8;
    	$pad = str_pad($xml, strlen($xml) + $n, ' ');
    	$crypted = mcrypt_encrypt(MCRYPT_3DES, $key, $pad, MCRYPT_MODE_ECB, "\0\0\0\0\0\0\0\0");
    	$result = "qiwi" . str_pad($this->login, 10, "0", STR_PAD_LEFT) . "\n";
    	$result .= base64_encode($crypted);
    	
    	return $result;
    }
    
}
