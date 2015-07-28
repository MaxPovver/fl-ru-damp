<?
/**
 * подключаем файл с курсом обмена валют
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exchrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/PromoCodes.php");

/**
 * Класс обрабатывающий счета юзеров (таблицы account и account_operations)
 *
 */
class account
{
    /**
     * Пути для загрузки сканов документов
     * Например: kazakov/private/account/...
     */
    const DOC_UPLOAD_PATH   = '%s/private/account';
    const OTHER_UPLOAD_PATH = '$s/private/account/finance_other';
    
    
    const MAX_FILE_SIZE = 20971520;//2097152;
    const MAX_FILE_COUNT = 4;
    
    
    const MSG_UPLOAD_REQ = 'Требуется загрузить скан одной или нескольких страниц паспорта.';
    const MSG_UPLOAD_OLD = 'Проверьте актуальность сканов документов.';
    
    
	/**
	 * id счета пользователя. ОНО отлично от UID юзера из таблицы users!
	 *
	 * @var integer
	 */
	public $id;
	
	/**
	 * UID юзера из таблицы users
	 *
	 * @var integer
	 */
	public $uid;
	
	/**
	 * По идеи должно сохранять имя и фамилию юзера, если того удалят (чтобы сохранить историю и счета)
	 * (Похоже этого не делает) character varying(128)
	 *
	 * @var string
	 */
	public $username;
	
	/**
	 * Сумма на счету юзера (numeric(8,2))
	 *
	 * @var float
	 */
	public $sum;
	
	/**
	 * Сумма бонуса на счету юзера
	 *
	 * @var float
	 */
    public $bonus_sum;
	
	/**
	 * ФИО пользователя
	 *
	 * @var string
	 */
	public $fio;
	
	/**
	 * Номер аккаунта
	 *
	 * @var integer
	 */
	public $accnum;
	
	/**
	 * Название первичного ключа таблицы
	 *
	 * @var string
	 */
	public $pr_key="id";

        public static $db_connect = null;
        
        public $is_gift = false;
        
    /**
     * Массив UID юзеров которые не нужно учитывать в статистике
     * 
     * @var array 
     */
    private $aIgnoreInStats = array();
    
    /**
     * Получает массив UID юзеров которые не нужно учитывать в статистике и сохраняет в $this->aIgnoreInStats
     */
    private function _getIgnoreInStats() {
        if ( !$this->aIgnoreInStats ) {
            $this->aIgnoreInStats = $GLOBALS['DB']->col( 'SELECT uid FROM users WHERE ignore_in_stats = TRUE' );
        }
    }

    /**
     * Возвращает соединение с базой данных.
     * 
     * @return resource
     */
    
    public static function getDBConnect(){
        if(!self::$db_connect) self::$db_connect = DBConnect();
        return self::$db_connect;
    }

	/**
	 * Занести деньги на счет юзера (предполагается, что оплата прошла успешно)
	 *
	 * @param integer $op_id			возвращает идентификатор платежной операции
	 * @param integer $dep_id			номер счета
	 * @param float   $ammount			сумма перевода (numeric(8,2)) в FM
	 * @param string  $descr			описание перевода
	 * @param integer $payment_sys		тип системы через которую осуществлен перевод
	 * @param float   $trs_sum			кол-во денег в единицах исходной системы
	 * @param integer $op_code			номер операции (по дефолту - 12 занесение денег на счет)
	 * @param integer $op_add			Доп. инфа по операции (например id Сделки без Риска)
	 * @return string					возвращает сообщение об ошибке
	 */
	function deposit(&$op_id, $dep_id, $ammount, $descr, $payment_sys = 0, $trs_sum = 0, $op_code = 12, $op_add = 0, $date = 'now()') 
    {
		global $DB;
        
        //setlocale(LC_ALL, "en_US");
        
		//$ammount = (float)$ammount; // фикс (540.3/30) -- вместо 18.01 округление дает 18.00.
		//$ammount = round($ammount * 100) / 100;
				
        $row = $DB->row("
            SELECT u.uid, u.role, u.login 
            FROM account AS a 
            INNER JOIN users AS u ON (a.uid=u.uid) 
            WHERE a.id = ?", $dep_id);
        
        if ($DB->error) {
            return $DB->error;
        }

        if (!$row) {
            return 'Аккаунт не существует';
        }
        
        $this->uid = $row['uid'];
        $login = $row['login'];

		$this->GetInfo($this->uid);
		$memBuff = new memBuff();
        $memBuff->set("ac_sum_old_".$this->uid, $this->sum);
		
		$op_id = $DB->insert('account_operations', array(
			'billing_id'  => $dep_id,
			'op_code'     => $op_code,
			'ammount'     => $ammount,
			'descr'       => $descr,
			'payment_sys' => $payment_sys,
			'trs_sum'     => $trs_sum,
			'op_date'     => $date
		), 'id');
		
		if(!$op_id) {
			return "Ошибка сервера";
		}
        
		// количество операций
		$_SESSION['account_operations'] = intval($_SESSION['account_operations']) + 1;
        
        // Обновляем сессию пользователю сразу 
        // при поступлении денежных средств
        $session = new session();
        $session->UpdateAccountSum($login);
        
        
        
        
        
        //if (!$result) {
            
            //session_start();
            //$this->GetInfo($gid);
            //$_SESSION['ac_sum'] = $this->sum;
            
            // Обновляем сессию пользователю сразу 
            // при поступлении денежных средств
            //$session = new session();
            //$session->UpdateAccountSum($login);
        //}

        
		//if (!$result) {
            //$this->buyOrdersList($gid, $op_code, $login);
            
            // Обновляем сессию пользователю сразу при поступлении денежных средств
            //$session = new session();
            //$session->UpdateAccountSum($login);

            //@todo: по идее более не используется
            /*
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
			switch ($op_code) {
				case sbr::OP_RESERVE: // новая СБР.
					$sbr = new sbr_emp($gid);
                    $sbr->setGetterSchemes(1);// Новая СБР
					$sbr->initFromId($op_add, false, false);
					if (!$sbr->error) {
						$sbr->reserve($op_id);
						$sbr_stage = $sbr->getStages();
                        foreach($sbr_stage as $stage) {
                            $sbr->setUserReqvHistory($gid, intval($stage->data['id']), 0); // Сохраняем для всех этапов, Резервирование работодателя
                        }
					}
					break;
				default:  //Зачисление денег на счет
			}*/
            
		//}
        	
		return false;
	}

    /**
     * Покупка списка услуг ожидающих оплаты
     * 
     * @todo: рекомендуется не использовать данный метод
     * 
     * @param integer $gid     Ид пользователя
     * @param integer $op_code Ид услуги
     */
    public function buyOrdersList($gid, $op_code, $login = "") {
        $log =  new log("billing/deposit-".SERVER.'-%d%m%Y.log', 'a', "%d.%m.%Y %H:%M:%S:\r\n");
        $log->writeln("deposit: login:{$login}, uid:{$gid}, code:{$op_code}\r\n");
        if(!$gid) return false;
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
        
        session_start();
        $this->GetInfo($gid);
        $ac_sum = $this->sum;
        $log->write("start account_sum:{$ac_sum}\r\n");
        if(in_array($op_code, billing::$op_code_transfer_money)) {
            // Деньги поступают на счет смотрим что можно оплатить
            $bill= new billing($gid);
            $reserve_operations = $bill->getReserveOperationsByStatus();
            if( !empty($reserve_operations) ) {
                ob_start();
                var_dump($reserve_operations);
                $out = ob_get_clean();
                $log->write($out."\r\n");
                //$mail_reserved = array();
                $reserved_ids  = array_map(create_function('$array', 'return $array["id"];'), $reserve_operations);
                $bill->startReserved($reserved_ids); // Блокируем услуги для изменений
                foreach($reserve_operations as $reserve) {
                    //$ret[$reserve['id']] = $reserve;
                    $log->write("reserve {$reserve['id']} : {$ac_sum} >= {$reserve['ammount']}\r\n");
                    //if($ac_sum >= $reserve['ammount']) { // Пытаемся оплатить список услуг
                        //$mail_reserved[] = $reserve['id'];
                        $bill->transaction = $bill->account->start_transaction($bill->user['uid'], 0);
                        $success = $bill->completeOrders($reserve['id']);
                        $log->write("{$reserve['id']} ({$reserve['ammount']}) : {$success}\r\n");
                        if($success) {
                            $bill->account->commit_transaction($bill->transaction, $bill->user['uid'], NULL);
                            $ac_sum = $ac_sum - $reserve['ammount'];
                        }
                        $log->write("set account_sum: {$ac_sum}\r\n---\r\n");
                    //}
                }
                $bill->stopReserved($reserved_ids); // Разблокируем услуги для изменений
                $log->write("done\r\n---------------------------------\r\n\r\n");
                
                /*
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php');
                if (substr($bill->user['subscr'], 15, 1) == 1) {
                    //$smail = new smail();
                    //$smail->sendReservedOrders($ret, $mail_reserved);
                }
                */
            }
            
            $_SESSION['ac_sum'] = $ac_sum;
        }
    }
    
    
    
    
    /**
     * Аналог: depositEx
     * 
     * Занести деньги на счет юзера (используется в админке, когда деньги заносятся
     * вручную) По умолчанию падает в "Другие операции"
     *
     * @todo Это доработанная версия depositEx() чтобы оная вернула $op_id как делает это deposit()
     * @todo Но вот deposit() не принимает комментарий $ucoms
     * 
     * @param integer $op_id			возвращает идентификатор платежной операции 
     * @param integer $dep_id 			номер счета
     * @param float   $ammount			сумма перевода (numeric(8,2)) в FM
     * @param string  $descr			описание перевода для системы
     * @param string  $ucoms			описание перевода для "истории" в аккаунте юзера
     * @param integer $op_code			идентификатор типа платежа
     * @param float   $trs_sum			кол-во денег в единицах исходной системы
     * @param integer $payment_sys		тип системы через которую осуществлен перевод
     * @return string				возвращает сообщение об ошибке
     */
    public function depositEx2(&$op_id, $dep_id, $ammount, $descr, $ucoms, $op_code = 13, $trs_sum = NULL, $payment_sys = NULL, $date = NULL){
        global $DB;
        
        $op_id = $DB->insert('account_operations', array(
                        'billing_id'  => $dep_id,
			'op_code'     => $op_code,
			'ammount'     => $ammount,
			'descr'       => $descr,
                        'comments'    => $ucoms,
			'payment_sys' => $payment_sys,
			'trs_sum'     => $trs_sum,
			'op_date'     => $date
		), 'id');
        
         $result = $DB->error;   
        
         $row = $DB->row("
             SELECT u.uid, u.role, u.login, a.sum
             FROM account AS a 
             INNER JOIN users AS u ON (a.uid=u.uid) 
             WHERE a.id = ?", $dep_id);
         
        $this->sum = $row['sum']; 
        $gid  = $row['uid'];
         //$role  = $row['role'];
         $login = $row['login'];
        
         //@todo: в обновленной схеме биллинга каждый занос денег может быть привязан к услуге
         //и покупка всех услуг в очереди не допустима
         
         //if (!$result) {
         //   $this->buyOrdersList($gid, $op_code, $login);
         //}
         
         $session = new session();
         $session->UpdateAccountSum($login);

         // количество операций
        $_SESSION['account_operations'] = intval($_SESSION['account_operations']) + 1;
        
         return $result;     
    }
    
    
    
    
	/**
	 * Занести деньги на счет юзера (используется в админке, когда деньги заносятся
	 * вручную) По умолчанию падает в "Другие операции"
	 *
	 * @param integer $dep_id 			номер счета
	 * @param float   $ammount			сумма перевода (numeric(8,2)) в FM
	 * @param string  $descr			описание перевода для системы
	 * @param string  $ucoms			описание перевода для "истории" в аккаунте юзера
	 * @param integer $op_code			идентификатор типа платежа
	 * @param float   $trs_sum			кол-во денег в единицах исходной системы
	 * @param integer $payment_sys		тип системы через которую осуществлен перевод
	 * @return string					возвращает сообщение об ошибке
	 */
	function depositEx($dep_id, $ammount, $descr, $ucoms, $op_code = 13, $trs_sum = NULL, $payment_sys = NULL, $date = NULL){
	    global $DB;

		$data = array(
			'billing_id'  => $dep_id,
			'op_code'     => $op_code,
			'ammount'     => $ammount,
			'descr'       => $descr,
			'comments'    => $ucoms,
			'payment_sys' => $payment_sys,
			'trs_sum'     => $trs_sum
		);
		if ($date) {
			$data['op_date'] = $date;
		};
		$DB->insert('account_operations', $data);
        $result = $DB->error;
		$row = $DB->row("SELECT u.uid, u.role, u.login FROM account AS a INNER JOIN users AS u ON (a.uid=u.uid) WHERE a.id = ?", $dep_id);
		$gid   = $row['uid'];
        $role  = $row['role'];
        $login = $row['login'];
        
        $session = new session();
        $session->UpdateAccountSum($login);

		// количество операций
		$_SESSION['account_operations'] = intval($_SESSION['account_operations']) + 1;
  
		return $DB->error;
	}
	
	/**
	 * ВРЕМЕННАЯ
	 * 
	 * Акция Альфа-банк: первые 50 юзеров пополнившие на 1000 и более рублей счет, получают ПРО в подарок
	 * 
	 * @param float $nSummR
	 * @param integer $sUid
	 */
	function alphaBankGift( $nSummR = 0, $sDate = '', $sUid = 0, $sLogin = '' ) {
	    global $DB;
	    
	    if ( $nSummR >= 1000 && $sDate < date('c', strtotime('2011-06-06')) ) { // если сумма больше 1000
	        /*
        	$nCount = $DB->val('SELECT COUNT(DISTINCT u.uid) FROM users u 
                LEFT JOIN account a ON u.uid = a.uid 
                LEFT JOIN account_operations o ON a.id = o.billing_id 
                WHERE o.op_code = 89 AND o.trs_sum >= 1000');
        	
        	if ( $nCount < 50 ) { // если юзер из числа первых 50
        	    $nCount = $DB->val('SELECT COUNT(o.billing_id) FROM users u 
                    LEFT JOIN account a ON u.uid = a.uid 
                    LEFT JOIN account_operations o ON a.id = o.billing_id 
                    WHERE u.uid = ?i AND o.op_code = 89 AND o.trs_sum >= 1000', $sUid );
        	    
        	    if ( $nCount == 1 ) { // если юзер еще не получал этот бонус (первое пополнение на 1000+)
        	    */
            		require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/payed.php' );
				    
				    $payed   = new payed();
				    $bill_id = $gift_id = 0;
				    $tr_id   = $this->start_transaction( 103 );
				    
				    $payed->GiftOrderedTarif( $bill_id, $gift_id, $sUid, 103, $tr_id, '1', '', 90 );
                                                
                    // уведомление о подарке
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php' );
    				$sm = new smail();
    				$sm->NewGift( 'admin', $sLogin, '', $gift_id );
    				/*
        	    }
        	}
        	*/
        }
	}

	/**
	 * Занести деньги на бонусный счет юзера (используется в админке, когда деньги заносятся
	 * вручную) По умолчанию падает в "Другие операции"
	 *
	 * @param integer $dep_id 			номер счета
	 * @param float   $ammount			сумма перевода (numeric(8,2)) в FM
	 * @param string  $descr			описание перевода для системы
	 * @param string  $ucoms			описание перевода для "истории" в аккаунте юзера
	 * @param integer $op_code			идентификатор типа платежа
	 * @return string					возвращает сообщение об ошибке
	 */
	function depositBonusEx($dep_id, $ammount, $descr, $ucoms, $op_code = 13, $date=NULL)
	{
		global $DB;
		$data = array(
			'billing_id'    => $dep_id,
			'op_code'       => $op_code,
			'bonus_ammount' => $ammount,
			'descr'         => $descr,
			'comments'      => $ucoms
		);
		if ($date) {
			$data['op_date'] = $date;
		}
		$DB->insert('account_operations', $data);
		return $DB->error;
	}

	/**
	 * Проверяет существует ли счет с заданным номером
	 *
	 * @param integer $billing_no	номер счета
	 * @return integer				1 - да, 0 - нет :)
	 */
	function is_dep_exists($billing_no){
		return $GLOBALS['DB']->val("SELECT COUNT(id) FROM account WHERE id = ?", $billing_no);
	}

	/**
	 * Инициализирует члены класса значениями из таблицы счетов, соотв. данному юзеру
	 *
     * @param integer $uid
     * @param boolean $create   создать, если счет отсутсвует?
	 * @return integer		0 - счет не найден, 1 - иначе
	 */
    function GetInfo($uid, $create = false){
		global $DB;
		$row = $DB->row("SELECT *, round(sum,2) as sum FROM account WHERE uid = ?", $uid);
		if ($row){
			foreach ($row as $ikey => $value)
				$this->$ikey = trim($value);
			$ret = 1;
        } else {
            if($create) {
                $this->CreateNew($uid);
                return $this->GetInfo($uid, false);
            }
            $ret = 0;
        }
		return $ret;
	}

	/**
	 * Создать новый счет для пользователя с заданным UID
	 *
	 * @param integer $uid ИД Пользователя
	 * @return string	сообщение об ошибке
	 */
	function CreateNew($uid){
		global $DB;
		$DB->query("INSERT INTO account (uid) VALUES (?)", $uid);
		return $DB->error;
	}

	/**
	 * Получить историю операций юзера в виде многомерного массива по UID юзера
	 *
	 * @param integer $uid ИД Пользователя
	 * @param integer $mode Выборка для админов
	 * @return array  Данные выборки 
	 */
	function GetHistory($uid, $mode=0) {
		global $DB;
		if ($mode == 1) {
			$sql = "SELECT account_operations.balance, op_date, op_name, ammount, trs_sum, comments, op_code, account_operations.id, ob.id as is_blocked 
                FROM account_operations
				LEFT JOIN account ON account.id=account_operations.billing_id
				LEFT JOIN op_codes on op_code=op_codes.id 
                LEFT JOIN account_operations_blocked ob ON ob.operation_id = account_operations.id 
                WHERE uid = ? 
                ORDER BY account_operations.id DESC, op_date DESC";
		} else {
			$sql = "SELECT account_operations.balance, op_date, op_name, ammount, trs_sum, comments, op_code, account_operations.id, bonus_ammount 
                FROM account_operations
				LEFT JOIN account ON account.id=account_operations.billing_id
				LEFT JOIN op_codes on op_code=op_codes.id 
                LEFT JOIN account_operations_blocked ob ON ob.operation_id = account_operations.id 
                WHERE ob.id IS NULL AND uid = ? 
                ORDER BY account_operations.id DESC, op_date DESC";
		}
		return $DB->rows($sql, $uid);
	}
	
	/**
	 * Взять все операции по истории пользователя (надо для фильтра)
	 * 
	 * @param integer $uid    ИД Пользователя
	 * @return array
	 */
	function GetHistoryOpCodes($uid) {
	    global $DB;
	    
	    $sql = "SELECT DISTINCT op_code FROM account_operations
                LEFT JOIN account ON account.id=account_operations.billing_id
                LEFT JOIN op_codes on op_code=op_codes.id 
                LEFT JOIN account_operations_blocked ob ON ob.operation_id = account_operations.id 
                WHERE ob.id IS NULL AND uid = ?"; 
	    
	    return $DB->rows($sql, $uid);
	}
	
	/**
	 * Получить историю операций юзера в виде многомерного массива по UID юзера c применением фильтра
	 *
	 * @param integer $uid ИД Пользователя
	 * @param array   $filter Фильтр выборки
	 * @return array  Данные выборки 
	 */
	function GetHistoryByFilter($uid, $filter) {
	    global $DB;
	    
	    list($filter_sql, $order_sql, $limit_sql) = $this->getHistoryFilterSQL($filter);
	    
	    $sql = "SELECT 
                  account_operations.balance,
	              op_date, op_name, ammount, comments, op_code, account_operations.id, bonus_ammount 
	            FROM 
	              account_operations
				LEFT JOIN 
				  account ON account.id=account_operations.billing_id
				LEFT JOIN 
				  op_codes on op_code=op_codes.id 
				LEFT JOIN 
				  account_operations_blocked ob ON ob.operation_id = account_operations.id 
				WHERE 
				{$filter_sql}
				ob.id IS NULL AND uid = ?i 
				{$order_sql}
				{$limit_sql}";
	    return $DB->rows($sql, $uid);
	}
	
	/**
	 * Генерируем SQL запрос фильтра 
	 *
	 * @param array $filter Данные фильтра
	 * @return unknown
	 */
	public function getHistoryFilterSQL($filter) {
        $filter_sql = array();
	    if(strstr($filter['num_operation'], "*") === false && $filter['num_operation'] != "") {
	        $filter_sql[] = "account_operations.id = ".intval($filter['num_operation']);
	    } else if($filter['num_operation'] != "") {
	        $filter['num_operation'] = intval(str_replace("*", "", $filter['num_operation']));
	        $filter_sql[] = "account_operations.id::text SIMILAR TO '".intval($filter['num_operation'])."%'";
	    }
	    
	    if($filter['date_from'] !== false) {
         $filter['date_from'] = date('Y-m-d', strtotime($filter['date_from']));
	       $filter_date[] =  "op_date::date >= '{$filter['date_from']}'";    
	    }
	    if($filter['date_to'] !== false) {
         $filter['date_to'] = date('Y-m-d', strtotime($filter['date_to']));
	        $filter_date[] = "op_date::date <= '{$filter['date_to']}'";
	    }
	    if(count($filter_date)) {
	        $filter_sql[] = "(".implode(" AND ", $filter_date).")";
	    }
	    
	    if($filter['sum_from'] !== false) {
	       $filter_sum[] =  "ammount >= '{$filter['sum_from']}'";    
	    }
	    if($filter['sum_to'] !== false) {
	        $filter_sum[] = "ammount <= '{$filter['sum_to']}'";
	    }
	    if(count($filter_sum)) {
	        $filter_sql[] = "(".implode(" AND ", $filter_sum).") ";
	    }
	    
	    if($filter['op_code'] > 0) {
	        $filter_sql[] = "op_code = {$filter['op_code']}";
	    }
	    
	    if(count($filter_sql)) {
	       $filter_sql = implode(" AND ", $filter_sql). " AND ";
	    } else {
	       $filter_sql = "";
	    }
	    
	    switch($filter['sort']) {
	        default:
	        case 1: 
	           $order_sql = "ORDER BY op_date DESC";
	           break;
	        case 2:
	            $order_sql = "ORDER BY id DESC";
	            break;
	        case 3:
	            $order_sql = "ORDER BY ammount DESC";
	            break;
	    }
	    
	    $limit_sql = "";
	    if($filter['limit'] > 0) {
	       $limit_sql = "LIMIT {$filter['limit']} OFFSET 0";
	    }
	    
	    return array($filter_sql, $order_sql, $limit_sql);
    }
	
	/**
	 * Получить историю операций юзера в виде многомерного массива по UID юзера и типу 
	 *
	 * @param integer $uid  ИД Пользователя
	 * @param array   $type Тип истории (тбл. op_codes) Если тип равен false выборка берется через self::getHistory();
	 * @return array  Данные выборки
	 */
	function getHistoryType($uid, $type=false) {
		if(!$type) return self::GetHistory($uid);
		return $GLOBALS['DB']->rows("
			SELECT op_date, op_name, ammount, comments, op_code, account_operations.id FROM account_operations
			LEFT JOIN account ON account.id=account_operations.billing_id
			LEFT JOIN op_codes on op_code=op_codes.id WHERE uid = ? AND op_code IN(?l) ORDER BY op_date DESC
		", $uid, $type);
	}

	/**
	 * Проверяет пополнял ли пользователь счет любым способом, кроме карты
	 *
	 * @param    integer    $uid    ID пользователям
	 * @return   boolean            true - да, false - нет
	 */
	function checkDepositByNotCard($uid) {
		global $DB;
                $sql = "SELECT * FROM account_operations AS ap JOIN account ON account.id = ap.billing_id WHERE uid=?i AND ap.ammount > 0";
                $res = $DB->query($sql, $uid);
                $ret = TRUE;
                while ( $row = pg_fetch_assoc($res) ) {
                    if ( $row['payment_sys'] == 6 || $row['payment_sys'] == 0 ) {
                        $ret = FALSE;
                    } else {
                        $ret = TRUE;
                        break;
                    }
                }
                return $ret;
	}
	
	/**
	 * Проверяет, совершал ли пользователь хотя бы одну операцию указанного типа(ов)
	 * 
	 * @param    integer   $uid   uid пользователя
	 * @param    array     $type  массив с типами операций (op_code)
	 * @return   boolean          TRUE - операции были, FALSE - нет
	 */
	function checkHistory($uid, $type) {
		global $DB;
		return $DB->val("SELECT COUNT(ap.id) FROM account_operations AS ap JOIN account ON account.id = ap.billing_id WHERE uid=? AND op_code IN(?l) LIMIT 1", $uid, $type);
	}

	/**
	 * Проверяет, совершал ли пользователь хотя бы одну платную покупку
	 * 
	 * @param    integer   $uid   uid пользователя
	 * @return   boolean          TRUE - операции были, FALSE - нет
	 */
	function checkPayOperation($uid) {
		global $DB;
		return $DB->val("SELECT 1 FROM account_operations AS ap JOIN account ON account.id = ap.billing_id WHERE uid=? AND ammount<0 LIMIT 1", $uid);
	}
	
	/**
	 * Группирует историю пользователя по коду операции.
	 *
	 * @param integer $uid ИД пользователя
	 * @return array массив (код операции -> количество)
	 */
	function getCountHistoryType($uid) {
		$row = $DB->rows("SELECT COUNT(op_code) as count, op_code FROM account_operations
		LEFT JOIN account ON account.id=account_operations.billing_id
			LEFT JOIN op_codes on op_code=op_codes.id WHERE uid=? GROUP BY op_code", $uid);
		if($row) {
			foreach($row as $k=>$val) {
				$result[$val['op_code']] = $val['count'];
			}
			
			return $result;
		}
		
		return false;
	}

	/**
	 * Совершить покупку
	 *
	 * @param integer $id				возвращает id покупки
	 * @param integer $transaction_id	идентификатор текущей транзакции
	 * @param integer $op_code			идентификатор операции
	 * @param integer $uid				UID
	 * @param string  $descr			описание для системы
	 * @param string  $comments			описание для истории юзера
	 * @param integer $ammount			количество товара
     * @param integer $commit			завершать ли транзакцию?
     * @param integer $promo_code   	ИД промо-кода
     * 
	 * @return integer					0
	 */
    function Buy(&$id, $transaction_id, $op_code, $uid, $descr = "", $comments = "", $ammount = 1, $commit = 1, $promo_code = 0, $payment_sys = 0, $trs_sum = 0){
		global $DB;
                if (!$transaction_id || $transaction_id != $this -> check_transaction($transaction_id, $uid)) return "Невозможно завершить транзакцию";
		else {
			$res = $DB->query("SELECT op_codes.sum as op_sum, account.sum, account.id FROM op_codes, account WHERE op_codes.id=? AND account.uid=?", $op_code, $uid);
			if (pg_errormessage()) return "Ошибка при получении информации о счете!";

			list($op_sum, $ac_sum, $bill_id) = pg_fetch_row($res);
			$sum = $op_sum*$ammount;
            
            if ($promo_code) {
                $promoCodes = new PromoCodes();
                $sum = $sum - $promoCodes->getDiscount($promo_code, $sum);
            }

            $ac_sum = round($ac_sum, 2);
            $sum = round($sum, 2);
			
            //@todo: зачем мемкеш? если занос денег deposit и покупка в отдной сессии php
            //можно было старое значение передать глобальной переменной или реестром
			$memBuff = new memBuff();
			$ac_sum_old = round($memBuff->get("ac_sum_old_".$uid), 2);
			$memBuff->delete("ac_sum_old_".$uid);
			
			$new_ac_sum = $ac_sum_old < 0 ? $ac_sum - $ac_sum_old : $ac_sum;
            
			if ($sum > 0 && $sum > $new_ac_sum)  {
                return "Недостаточно средств на счету!";
            }
            
			if ($sum < 0) { 
                return "Покупка на отрицательную сумму!";
            }
            
            $id = $DB->insert('account_operations', array(
				'billing_id'  => $bill_id,
				'op_code'     => $op_code,
				'ammount'     => -$sum,
				'descr'       => $descr,
				'comments'    => $comments,
				'payment_sys' => $payment_sys,
                'trs_sum'     => $trs_sum
			), 'id');
			
			if ($DB->error) {
                            return "Ошибка при записи счета!";
                        } else {
                            if ($uid == get_uid(false)) {
                                $_SESSION['ac_sum'] = $_SESSION['ac_sum'] - $sum;
                            }
                        }
            
            if ($promo_code) {
                $promoCodes->markUsed($promo_code);
            }
                        
            // количество операций
            $_SESSION['account_operations'] = intval($_SESSION['account_operations']) + 1;
            
            // для счетчика everesttech.net (см. engine/templates/footer.tpl)
            if ( $sum > 0 ) {
                $_SESSION['everesttech_conter'] = 1;
            }
                        
			if ($commit) $this -> commit_transaction($transaction_id, $uid, $id);
		}
		return 0;
	}

	/**
	 * Совершить покупку через СМС
	 *
	 * @param integer $id				возвращает id покупки
	 * @param integer $transaction_id	идентификатор текущей транзакции
	 * @param integer $op_code			идентификатор операции
	 * @param integer $uid				UID
	 * @param string  $descr			описание для системы
	 * @param string  $comments			описание для истории юзера
	 * @param integer $sum			    сумма потраченная за смс
	 * @param integer $commit			завершать ли транзакцию?
	 * @return integer					0
	 */	
	function BuyFromSMS(&$id, $transaction_id, $op_code, $uid, $descr, $comments, $sum, $commit = 1, $payment_sys = 7) { 
		global $DB;
		if (!$transaction_id || $transaction_id != $this->check_transaction($transaction_id, $uid)) {
			$this->view_error("Невозможно завершить транзакцию");
		} else {
			if (!($bill_id = $DB->val("SELECT account.id FROM op_codes, account WHERE op_codes.id = ? AND account.uid = ?", $op_code, $uid))) {
				return "Ошибка при получении информации о счете!";
			}
			$id = $DB->insert('account_operations', array(
				'billing_id'  => $bill_id,
				'op_code'     => $op_code,
				'ammount'     => 0,
				'descr'       => $descr,
				'comments'    => $comments,
				'payment_sys' => 7,
				'trs_sum'     => $sum
			), 'id');
			if ($DB->error) {
				return 'Ошибка при записи счета!';
			}
			
            // количество операций
            $_SESSION['account_operations'] = intval($_SESSION['account_operations']) + 1;
			
			if ($commit) {
				$this->commit_transaction($transaction_id, $uid, $id);
			}
		}
		return 0;
	}

	
	/**
	 * Совершить покупку c бонусного счета
	 *
	 * @param integer $id				возвращает id покупки
	 * @param integer $transaction_id	идентификатор текущей транзакции
	 * @param integer $op_code			идентификатор операции
	 * @param integer $uid				UID
	 * @param string  $descr				описание для системы
	 * @param string  $comments			описание для истории юзера
	 * @param integer $ammount			количество товара
	 * @param integer $commit			завершать ли транзакцию?
	 * @return integer					0
	 */
	function BuyFromBonus(&$id, $transaction_id, $op_code, $uid, $descr = "", $comments = "", $ammount = 1, $commit = 1){
		global $DB;
		if (!$transaction_id || $transaction_id != $this -> check_transaction($transaction_id, $uid)) {
			$this->view_error("Невозможно завершить транзакцию");
		} else {
			$res = $DB->query("SELECT op_codes.sum as op_sum, account.bonus_sum, account.id FROM op_codes, account WHERE op_codes.id=? AND account.uid=?", $op_code, $uid);
			if ($DB->error) {
				return "Ошибка при получении информации о счете!";
			}
			list($op_sum, $ac_sum, $bill_id) = pg_fetch_row($res);
			$sum = $op_sum * $ammount;
			if ($sum > $ac_sum) {
				return "Недостаточно средств на бонусном счету!";
			}
			if ($sum < 0) {
				return "Покупка на отрицательную сумму!";
			}
			$id = $DB->insert('account_operations', array(
				'billing_id'    => $bill_id,
				'op_code'       => $op_code,
				'bonus_ammount' => -$sum,
				'descr'         => $descr,
				'comments'      => $comments
			), 'id');
			if ($DB->error) {
				return "Ошибка при записи счета!";
			} else {
				$_SESSION['bn_sum'] = $_SESSION['bn_sum'] - $sum;
				
				// количество операций
                //$_SESSION['account_operations'] = intval($_SESSION['account_operations']) + 1;
			}
			if ($commit) {
				$this->commit_transaction($transaction_id, $uid, $id);
			}
		}
		return 0;
	}

	/**
	 * Изменить код операции (op_codes)
	 *
	 * @param integer $bill_id			id покупки
	 * @param integer $new_opcode		новый код покупки(операции)
	 */
	function ChangeOpcodes ($bill_id, $new_opcode) {
		$GLOBALS['DB']->query("UPDATE account_operations SET op_code = ? WHERE id = ?", $new_opcode, $bill_id);
	    return 0;
	}

	/**
	 * Подарить что-нибудь пользователю
	 *
	 * @param integer $id				возвращает id покупки
	 * @param integer $gid				возвращает идентификатор подарка
	 * @param integer $transaction_id	идентификатор текущей транзакции
	 * @param integer $op_code			идентификатор операции
	 * @param integer $fid				UID дарителя
	 * @param integer $tid				UID получателя
	 * @param string  $descr			описание для системы
	 * @param string  $comments			описание для истории юзера
	 * @param integer $ammount			количество товара
	 * @return integer Возвращает всегда 0;
	 */
	function Gift(&$id, &$gid, $transaction_id, $op_code, $fid, $tid, $descr = "", $comments = "", $ammount = 1){
		global $DB;
		if (!$transaction_id || $transaction_id != $this->check_transaction($transaction_id, $fid)) {
			$this->view_error("Невозможно завершить транзакцию");
		} else {
			$res = $DB->query("SELECT op_codes.sum as op_sum, f.sum, f.id, t.id FROM op_codes, account f, account t WHERE op_codes.id = ? AND f.uid = ? AND t.uid = ?", $op_code, $fid, $tid);
			if ($DB->error) {
				return "Ошибка при получении информации о счете!";
			}
			list($op_sum, $ac_sum, $bill_id, $tbill_id) = pg_fetch_row($res);
			$sum = $op_sum*$ammount;
			if ($sum > $ac_sum) {
				return "Недостаточно средств на счету!";
			}
			if ($sum < 0) {
				return "Подарок на отрицательную сумму!";
			}
			$res = $DB->mquery(
				"SELECT gid, id FROM MakeGift(?, ?, ?, ?, ?, ?, ?, ?, ?) as (gid integer, id integer)",
				$bill_id, $tbill_id, $op_code, $sum, $descr, $comments, $fid, $tid, 0
			);
			list($gid, $id) = pg_fetch_row($res);
			if ($DB->error) {
				return "Ошибка при записи счета!";
			} else {
				$_SESSION['ac_sum'] = $_SESSION['ac_sum'] - $sum;
			}
			$this->commit_transaction($transaction_id, $fid, $id);
		}
                // для счетчика everesttech.net (см. engine/templates/footer.tpl)
                if ( $sum > 0 ) {
                    $_SESSION['everesttech_conter'] = 1;
                }
		return 0;
	}

	/**
	 * Подарить что-нибудь по бонусному счету
	 *
	 * @param integer $id				возвращает id покупки
	 * @param integer $gid				возвращает идентификатор подарка
	 * @param integer $transaction_id	идентификатор текущей транзакции
	 * @param integer $op_code			идентификатор операции
	 * @param integer $fid				UID дарителя
	 * @param integer $tid				UID получателя
	 * @param string  $descr				описание для системы
	 * @param string  $comments			описание для истории юзера
	 * @param integer $ammount			количество товара
	 * @return integer
	 */
	function GiftBonus(&$id, &$gid, $transaction_id, $op_code, $fid, $tid, $descr = "", $comments = "", $ammount = 1){
		global $DB;
		if (!$transaction_id || $transaction_id != $this->check_transaction($transaction_id, $fid)) {
			$this->view_error("Невозможно завершить транзакцию");
		} else {
			$res = $DB->query("SELECT op_codes.sum as op_sum, f.bonus_sum, f.id, t.id FROM op_codes, account f, account t WHERE op_codes.id=? AND f.uid=? AND t.uid=?", $op_code, $fid, $tid);
			if ($DB->error) {
				return "Ошибка при получении информации о счете!";
			}

			list($op_sum, $ac_sum, $bill_id, $tbill_id) = pg_fetch_row($res);
			$sum = $op_sum*$ammount;

			if ($sum > $ac_sum) {
				return "Недостаточно средств на счету!";
			}

			if ($sum < 0) {
				return "Подарок на отрицательную сумму!";
			}

			$res = $DB->query(
				"SELECT gid, id FROM MakeBonusGift(?, ?, ?, ?, ?, ?, ?, ?, ?) as (gid integer, id integer)",
				$bill_id, $tbill_id, $op_code, $sum, $descr, $comments, $fid, $tid, 0
			);
			list($gid, $id) = pg_fetch_row($res);
			if ($DB->error) {
				return "Ошибка при записи счета!";
			} else {
				$_SESSION['ac_sum'] = $_SESSION['ac_sum'] - $sum;
			}
			$this->commit_transaction($transaction_id, $fid, $id);
		}
		return 0;
	}

	/**
	 * Получить имя класса операции по ее коду (типу) или идентификатору в журнале операций.
	 *
	 * @param integer $bill_id		[опционально] идентификатор операции (ид биллинга)
	 * @param integer $opcode		[опционально] код операции (op_codes)
	 * @return string				имя класса
	 */
	function GetOperationClassName($bill_id = 0, $opcode = 0){
		global $DB;
		require_once ABS_PATH.'/classes/login_change.php';
		if (!$opcode && $bill_id){
			$opcode = $DB->val("SELECT op_code FROM account_operations LEFT JOIN op_codes on op_code=op_codes.id WHERE account_operations.id = ?i", $bill_id);
		}
        $payed_opcodes = array(15,16,26,28,35,42,47,48,49,50,51,52,63,66,67,68,76,91,92,114,118,119,120,131,132,163,164);
        $project_opcodes = array(9, 106, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 86, 53, 113, 192, 138, 139, 140, 141);
		if ($opcode && in_array($opcode, $payed_opcodes)){
			$class = "payed";
		} elseif (in_array($opcode, $project_opcodes) || $opcode && $opcode > 6 && $opcode < 10 || $opcode == 54 || $opcode == 88 || $opcode == 105 || $opcode == 104 || $opcode == 103){
			$class = "projects";
		} elseif ($opcode && $opcode > 9 && $opcode < 12 || $opcode == 17 || $opcode == 18 || $opcode == 19 || $opcode == 20 || $opcode == 24 || $opcode == 25 || $opcode == 27 || $opcode == 29 || $opcode == 30 || $opcode == 33 || $opcode == 34 || $opcode == 64 || $opcode == 84 || $opcode == 85 || $opcode == 93){
			$class = "firstpage";
		} elseif ($opcode == 13 || $opcode == 12 || $opcode == 40 || $opcode == 75 || $opcode == 89 || $opcode == 94){
			$class="account";
		} elseif ($opcode == 21){
			$class="firstpagepos";
		} elseif ($opcode == 31){
			$class="confa07";
		} elseif ($opcode == 23 || $opcode == 39 || $opcode==95 || $opcode==97 || $opcode==99 || $opcode==101 || $opcode==96 || $opcode==100){
			$class="present"; // если будут добавляться опкоды, то надо проверить правильность ф-ции DelByOpid в этом классе.
		} elseif ($opcode == 36 || $opcode == 38 || $opcode == 79 || $opcode == 38){
			$class="norisk"; //  @todo старый norisk, необходимо убрать
		} elseif ($opcode == 44){
			$class="birthday08";
	    } elseif ($opcode == 45 || $opcode == 46){
	      $class="masssending";
	    } elseif ($opcode == 61){
	      $class="projects_offers_answers";
	    } elseif ($opcode == 65 || $opcode == 73 || $opcode == 69 || $opcode == 83){
	      $class="pay_place";
	    } elseif ($opcode == login_change::OP_CODE){
	      $class="login_change";
	    } elseif ($opcode == 77 || $opcode == 78 || $opcode == 80 || $opcode == 12){
	      $class="account";
	    } elseif ($opcode == 82) {
                $class="lm";
        } elseif ($opcode == 134) {
            $class="tservices/tservices";
        } elseif (in_array($opcode, array(155, 156, 157, 158, 159, 160, 161, 162, 
            173, 174, 175, 176, 177, 178, 179, 180))) {
            $class="tservices/tservices_binds";
        } elseif (in_array($opcode, array(142, 143, 144, 148, 149, 150, 151, 152, 153, 
            181, 182, 183, 184, 185, 186, 187, 188, 189))) {
            $class="freelancer_binds";
        } else $class = "";
		return $class;
	}

	/**
	 * Получить информацию об операции по ее идентификатору
	 *
	 * @param integer $bill_id		идентификатор операции
	 * @param integer $uid			ID Пользователя
	 * @param integer $mode			1:история юзера; 2:история юзера для админа; 3:подарок
	 * @return string				текстовое описание операции
	 */
	function GetHistoryInfo($bill_id, $uid, $mode = 1){
		$class = $this->GetOperationClassName($bill_id);
                if($class){
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/' . $class.".php");
                    $rtf = new $class;
                    $out = $rtf->GetOrderInfo($bill_id, $uid, $mode);
                }else{
                    $out = 'Класс не найден '.$bill_id;
                }
                return $out. ($mode==1 ? ' &#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_80 b-layout__link_inline-block b-layout__link_lineheight_1" onclick="xajax_ShowBillText('.$bill_id.');" href="javascript:void(0);">Скрыть</a>' : '');
	}

    /**
	 * Получить наименование операции для вывода в таблице истории
	 *
	 * @param integer $bill_id		идентификатор операции
	 * @param integer $uid			ID Пользователя
	 * @return string				текстовое описание операции
	 */
	public static function GetHistoryText(&$val){
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
        
        //Входной опкод может быть для скидки поэтому пробуем получить его оригинал услуги
        $original_op_code = $val['op_code'];
        $val['op_code'] = billing::getOpCodeByDiscount($val['op_code']);
        
		$html = $val['op_name'];
        if($val['op_code'] == billing::RESERVE_OP_CODE) {
            $html .= ' &#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_80 b-layout__link_inline-block b-layout__link_lineheight_1" href="javascript:void(0);" onclick="xajax_ShowReserveOrders('.$val['id'].');">подробнее</a>';
        }
        if($val['op_code']==16) $html .= '(EMP)';
        if($val['op_code']==52) $html .= '(FL)';
        if($val['op_code'] < 7 || in_array($val['op_code'], array(
            8,
          //  10,
            11,
            15,
            16,
            17,
            18,
          //  19,
          //  20,
            23,
            24,
            25,
            33,
            52,
            63,
            64,
            66,
            67,
            68,
            12, // Зачисление денег
            //21, // Изменение позиции платного размещения
            38, // Перевод денег за "Сделку без риска"
            45, // Платная рассылка по разделам
            47, // Тестовый ПРО
            114,// Тестовый ПРО акция
            131, // Аккаунт PRO на неделю
            132, // Аккаунт PRO на день
            48, // Аккаунт PRO на месяц
            49, // Аккаунт PRO на 3 мес
            50, // Аккаунт PRO на 6 мес
            51, // Аккаунт PRO на 12 мес
            69, // Место наверху главной страницы в подарок
            76, // Аккаунт PRO на неделю
            80, // Платные специализации
            84, // Размещение на странице каталога в подарок
            83, // Место наверху каталога в подарок
            85, // Размещение на странице каталога (внутренние страницы) в подарок
            118, // ПРО на 3 месяца
            119, // ПРО на 6 месяцев
            120, // ПРО на 12 месяцев
            163, // Тестовый ПРО на 2 месяц
            164  // PROFI на 1 месяц
            ))){
            $html .= ' &#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_80 b-layout__link_inline-block b-layout__link_lineheight_1" href="javascript:void(0);" onclick="xajax_ShowBillComms('.$val['id'].', 0, 1);">подробнее</a>';
        }

        /**
         * Платные опции проектов
         * @TODO Конструкция монструозная, конечно. Хотя делает простую вещь. Вынести в метод?
         * @todo Почему она вообще здесь и отрабатывает на каждую итерацию?
         * @todo Зачем вообще нужен этот метод?
         */
        if (in_array($val['op_code'], array(9, 53, 86, 106, 138, 139, 140, 141, 113, 192))) {
            //Если нет ИД проекта, парсим его из строки
            //Старые операции - с пробелом после №
            if (!$val['project_id']) {
                preg_match('~№ (\d+)~', $val['comments'], $match);
                $val['project_id'] = $match[1];
            }
            //Не нашли, тогда новые - без пробела
            if (!$val['project_id']) {
                preg_match('~№(\d+)~', $val['comments'], $match);
                $val['project_id'] = $match[1];
            }

            $parts = explode(' & ', $val['comments']);
            if (count($parts) == 2) {
                $words = explode(' ', $parts[0]);
                $projectNumber = false;
                foreach ($words as $key=>&$word) {
                    if (strpos($word, '№') === false) {
                        continue;
                    }
                    $projectNumber = $key;
                    
                    //Номер мог не записался в операцию, если покупка была при создании
                    if ($word == '№') {
                        $word .= $val['project_id'];
                    }
                }
                $html = '';
                $words[$projectNumber - 1] = '<a class="b-layout__link" href="/projects/'.$val['project_id'].'">'.$words[$projectNumber - 1];
                $words[$projectNumber] .= '</a>';
                $html = implode(' ', $words);
                $val['comments'] = $parts[1];
            } else {
                $html = '<a class="b-layout__link" href="/projects/'.$val['project_id'].'">' . $val['op_name'] . '</a>';
            }
        }
        
        //Платные места в каталоге
        if (in_array($val['op_code'], array(142, 143, 144))) {
            $html = $val['descr'];
        }
        
        //Поднятие платного места
        if (in_array($val['op_code'], array(10, 20, 145, 146, 147, 148, 149, 150, 
            151, 152, 153, 154, 155, 156, 157, 158, 159, 160, 161, 162))) {
            $html = $val['descr'];
        }
        
        //Поднятие платного места
        if (in_array($val['op_code'], array(10, 19, 20))) {
            if ($val['descr']) {
                $html = $val['descr'];
            } else {
                //У старых платежей описание не заполнено
                $html = $val['op_name'].' &#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_80 b-layout__link_inline-block b-layout__link_lineheight_1" href="javascript:void(0);" onclick="xajax_ShowBillComms('.$val['id'].', 0, 1);">подробнее</a>';
            }
        }

        
        //Услуга по скидке, пока у нас только для PROFI
        //поэтому дописываем пометку
        if ($original_op_code != $val['op_code'] && 
            //В опкодах тоже есть пометка поэтому кое-где она не нужна
            !in_array($original_op_code, array(165, 166))) {
            $html .= '  (для profi)';
        }
        
        
        return $html;
}

	/**
	 * Удалить операцию
	 *
	 * @param integer $uid		ID Пользователя
	 * @param integer $opid		идентификатор операции
	 * @return string			сообщение об ошибке
	 */
	function Del($uid, $opid){
		global $DB;
		$class = $this->GetOperationClassName($opid);

		if ($class) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/' . $class.".php");
                    
                    $classElements = explode('/', $class);
                    $className = count($classElements) > 1 ? $classElements[1] : $class;

                    $rtf = new $className;
                    $rtf->DelByOpid($uid, $opid);
                    $DB->query("DELETE FROM account_operations WHERE id = ? AND billing_id=(SELECT id FROM account WHERE uid = ?)", $opid, $uid);
                    $user = new users();
                    $user->GetUserByUID($uid);
                    $session = new session();
                    $session->UpdateAccountSum($user->login);
                    
                    if ($class == 'tservices/tservices') {
                        //@todo: А сделать это в DelByOpid нельзя?
                        $rtf->updateTab($uid);
                    }
		}

		return $DB->error;
	}

	/**
	 * Заглушка для операции Del для этого класса
	 *
	 * @param integer $uid		ID Пользователя
	 * @param integer $opid		идентификатор операции
	 * @return integer			0
	 */
	function DelByOpid($uid, $opid){
		return 0;
	}

	/**
	 * Выдает статистику по приходу средств счет за указаный период
	 *
	 * @param integer $ps					идентификатор платежной системы
	 * @param string  $from_date			c какого дня
	 * @param string  $to_date				по какой день
	 * @return array					[сумма в валюте платежной системы, сумма в FM]
	 */
	function GetStatPS($ps, $from_date = '2000-01-01', $to_date = 'now()', $op_code = 12) {
		global $DB;
		$ret = $DB->row(
			"SELECT SUM(trs_sum) as trsum, SUM(ammount) as sum, COUNT(1) as count FROM account_operations
			WHERE op_date >= ? AND op_date < ?::date + 1 AND payment_sys = ? AND op_code = ?",
			$from_date, $to_date, $ps, $op_code
		);
		return $ret;
	}
	
	/**
	 * Выдает статистику по приходу средств счет за указаный период по всем платежным системам
	 *
	 * @param string  $from_date			c какого дня
	 * @param string  $to_date				по какой день
	 * @return array					[сумма в валюте платежной системы, сумма в FM]
	 */
	function GetStatPSEx($from_date = '2000-01-01', $to_date = 'now()', $op_codes = array(12)){
		global $DB;

        $this->_getIgnoreInStats();

		$out = array();
		$tmp = array();
		$ret = $DB->rows(
			"SELECT SUM(trs_sum)::integer as trsum, SUM(round(ammount,2)) as sum, op_code, payment_sys as ps FROM account_operations
            INNER JOIN account a ON a.id = billing_id AND NOT (op_date >= '2011-01-01' AND a.uid IN (" . implode(',', $this->aIgnoreInStats) . "))
			WHERE op_date >= ? AND op_date < ?::date+'1 day'::interval GROUP BY op_code, ps",
			$from_date, $to_date
		);
		$ret1 = $DB->rows(
			"SELECT COUNT(trs_sum) as count, op_code, payment_sys as ps FROM account_operations
            INNER JOIN account a ON a.id = billing_id AND NOT (op_date >= '2011-01-01' AND a.uid IN (" . implode(',', $this->aIgnoreInStats) . "))
			WHERE op_date >= ? AND op_date < ?::date+'1 day'::interval AND op_code IN(?l) GROUP BY op_code, ps",
			$from_date, $to_date, $op_codes
		);
		if ($ret1) foreach ($ret1 as $row1){
        	$tmp[$row1['op_code']][$row1['ps']] = $row1;
        }
        if ($ret) foreach ($ret as $ikey => $row){
        	$out[$row['op_code']][$row['ps']] = $row;
        	$out[$row['op_code']][$row['ps']]['sum'] = zin($out[$row['op_code']][$row['ps']]['sum']);
        	$out[$row['op_code']][$row['ps']]['trsum'] = zin($out[$row['op_code']][$row['ps']]['trsum']);
        	$out[$row['op_code']][$row['ps']]['count'] = zin($tmp[$row['op_code']][$row['ps']]['count']);
        }
		return $out;
	}

    /**
     * Получить остатки на счетах юзеров за указанный период (суммарный баланс)
     *
     * @param string $from_date			c какого дня
     * @param string $to_date			по какой день
     * @return real						сумма
     */
    function GetStatOst($from_date = '2000-01-01', $to_date = 'now()', $ignore_staff = false) {
        global $DB;

        $ignore_sql = "";
        if ($ignore_staff) {
            $ignore_sql = "INNER JOIN account a ON a.id = ac.billing_id 
               AND NOT (op_date >= '2011-01-01' AND a.uid IN (SELECT uid FROM users WHERE ignore_in_stats = TRUE))";
        }
        return $DB->val("SELECT SUM(round(ammount, 2)) FROM account_operations ac 
            {$ignore_sql}
            WHERE op_date >= ? AND op_date < ?::date+'1 day'::interval", $from_date, $to_date);
    }
    
    /**
     * Общая статистика (Остаток на начало, Остаток на конец, Всего зачислено, Всего потрачено)
     * 
     * @param  string $from_date дата начала периода подсчета статистики
     * @param  string $to_date дата окончания периода подсчета статистики
     * @param  bool $ignore_staff опцилнально. установить в true если нужно исключить сотрудников
     * @return array масив с данными 
     *     (
     *         'begin'   => 'остаток_на_начало', 
     *         'end'    => 'остаток_на_конец', 
     *         'income' => 'всего_зачислено', 
     *         'spent'  => 'всего_потрачено'
     *     )
     */
    function getStatOverall( $from_date = '2000-01-01', $to_date = '2000-01-01', $ignore_staff = false ) {
        $this->_getIgnoreInStats();
        
        $sJoin1 = ( $ignore_staff ) ? "INNER JOIN account a ON a.id = ao.billing_id AND NOT (op_date >= '2011-01-01' AND a.uid IN (". implode( ',', $this->aIgnoreInStats ) ."))" : '';
        $sJoin2 = ( $ignore_staff ) ? "INNER JOIN account a ON a.id = ao.billing_id AND a.uid NOT IN (". implode( ',', $this->aIgnoreInStats ) .")" : '';
        $sQuery = "SELECT
                (
                    SELECT SUM(round(ammount, 2)) FROM account_operations ao
                    $sJoin1 
                    WHERE date(op_date) >= '2000-12-12' AND date(op_date) < gs.from_date
                ) AS begin,
                (
                    SELECT SUM(round(ammount, 2)) FROM account_operations ao
                    $sJoin1 
                    WHERE date(op_date) >= '2000-12-12' AND date(op_date) < gs.to_date + interval '1 day'
                ) AS end, 
                (
                    SELECT ABS(SUM(round(ammount, 2)))
                    FROM account_operations ao
                    $sJoin2 
                    WHERE date(op_date) >= gs.from_date AND date(op_date) < gs.to_date + interval '1 day' 
                        AND ammount < 0 AND ao.op_code <> 23
                ) AS spent,
                (
                    SELECT ABS(SUM(round(ammount, 2))) FROM account_operations ao
                    $sJoin2 
                    WHERE date(op_date) >= gs.from_date AND date(op_date) < gs.to_date + interval '1 day' 
                        AND ammount > 0 AND ao.op_code NOT IN (13, 23, 46)
                ) AS income
            FROM
                (SELECT ?::date AS from_date, ?::date AS to_date) gs";
        
        return $GLOBALS['DB']->row( $sQuery, $from_date, $to_date );
    }
    
    /**
     * Статистика по переводам от "своих" к "чужим" и от "чужих" к "своим"
     * 
     * @param  string $from_date дата начала периода подсчета статистики
     * @param  string $to_date дата окончания периода подсчета статистики
     * @param  bool $direction true - от "своих" к "чужим", false от "чужих" к "своим"
     */
    function getStatTransferOursAlien( $from_date = '2000-01-01', $to_date = '2000-01-01', $direction = true ) {
        $this->_getIgnoreInStats();
        
        $sNot1  = $direction ? ''    : 'NOT';
        $sNot2  = $direction ? 'NOT' : '';
        $sQuery = "SELECT ABS(SUM(round(ao1.ammount, 2)))
            FROM account_operations ao1
            INNER JOIN account_operations ao2 ON 
                date_trunc('seconds', ao2.op_date) = date_trunc('seconds', ao1.op_date) 
                AND abs(ao1.ammount) = abs(ao2.ammount) and ao2.op_code = 23 and ao1.id <> ao2.id and ao2.ammount > 0 
            INNER JOIN account a1 ON 
                a1.id = ao1.billing_id AND a1.uid $sNot1 IN (". implode( ',', $this->aIgnoreInStats ) .")
            INNER JOIN account a2 ON 
                a2.id = ao2.billing_id AND a2.uid $sNot2 IN (". implode( ',', $this->aIgnoreInStats ) .")
            WHERE 
                date(ao1.op_date) between ?::date and ?::date and ao1.op_code = 23 and ao1.ammount < 0
                and date(ao2.op_date) between ?::date and ?::date";
        
        return zin( $GLOBALS['DB']->val($sQuery, $from_date, $to_date, $from_date, $to_date) );
    }

    /**
	 * Получить статистику по сделкам за указанный период
	 *
	 * @param string $from_date		c какого дня
	 * @param string $to_date		по какой день
	 * @return arrray				[
     *                                  selfrl - найден исполнитель
     *                                  t3acc - утвердили ТЗ
     *                                  reserved - переведены деньги
     *                                  final - работа завершена
     *                                  moneyout - сделать выплаты
     *                                  finished - закончены
     *                              ]
	 */
    function GetStatDeal($from_date = '2000-01-01', $to_date = 'now()') {
		global $DB;
		return $DB->row("SELECT COUNTBOOL(NOT is_accepted) as selfrl, COUNTBOOL(is_accepted AND NOT is_money_reserved) as t3acc,
			COUNTBOOL(is_money_reserved AND NOT is_closed) as reserved,COUNTBOOL(is_closed = true AND is_finalized = false AND is_current_nr = true) as final,
			COUNTBOOL(is_payment_commited = false AND 
			is_finalized AND need_arbiter=false) + COUNTBOOL(need_arbiter=true AND resolved = true AND receivefrl_id IS NULL AND frl_sum > 0)
			 + COUNTBOOL(need_arbiter=true AND resolved = true AND receiveemp_id IS NULL AND emp_sum > 0) as moneyout,
			COUNTBOOL(is_finalized AND is_payment_commited) as finished FROM norisk  LEFT JOIN norisk_arbitrage ON norisk_id=id WHERE is_current_nr = true AND norisk.posted >= '$from_date' AND norisk.posted < '$to_date'::date +'1 day'::interval",
			$from_date, $to_date
		);
    }
    
	/**
	 * Взять статистику по сделкам за деньги в определенный период времени
	 *
	 * @param string $from_date С какой даты
	 * @param string $to_date   По какую дату
	 * @return array [WMZ сумма, WMR сумма]
	 */
    function GetStatDealMoney($from_date = '2000-01-01', $to_date = 'now()') {
        global $DB;
		$wmz = 0;
        $wmr = 0;
        $sql = 'SELECT nrsk.id as nid, money_from_sum, money_from_type, money_to_sum, money_to_type, posted, project_until, projects.name, projects.id, frl.login as flogin, frl.usurname as fusurname, frl.uname as funame, is_t3_send, emp.login, emp.usurname, emp.uname, frl.photo, emp.photo FROM (SELECT norisk.* FROM norisk LEFT JOIN norisk_arbitrage ON norisk_id=norisk.id WHERE is_closed = true AND is_finalized = true AND is_payment_commited = false AND is_current_nr = true AND need_arbiter=false OR need_arbiter=true AND resolved = true AND receivefrl_id IS NULL AND frl_sum > 0 OR need_arbiter=true AND resolved = true AND receiveemp_id IS NULL AND emp_sum > 0) as nrsk INNER JOIN users as frl ON nrsk.frl_id = frl.uid INNER JOIN users as emp ON nrsk.emp_id = emp.uid INNER JOIN projects ON projects.id = project_id ORDER BY nid DESC';
        $res = $DB->rows($sql);
        foreach($res as $d) {
            switch($d['money_to_type']) {
                case 2:
                    $wmz += $d['money_to_sum'];
                    break;
                case 3:
                    $wmr += $d['money_to_sum'];
                    break;
            }
        }
        $out = array('wmz'=>$wmz,'wmr'=>$wmr);
        return $out;
    }
    
    /**
     * Разделенная статистика по платным проектам (платные проекты, платные проекты по бонусу, конкурсы, конкурсы по бонусу)
     *
     * @param string  $from_date	c какого дня
	 * @param string  $to_date		по какой день
     * @param integer $type         Тип платной услуги (0 - логотип, 1 - фон, 2 - выделение текста, 3 - поднятие проекта)
     * @param boolean $is_konkurs   Берем данные по конкурсу или нет
     * @param boolean $is_bonus     Берем данные по бонусам или нет
     * @return array [сумма в FM, кол-во операций]
     */
    function getStatOPProject($from_date = '2000-01-01', $to_date = 'now()', $type = '', $is_konkurs=false, $is_bonus=false) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        global $DB;
        $query = array();
        
        if($type !== '') {
            $query[] = "pay_type = {$type}";
            $select = "SUM(round(p.ammount, 2)) as sum, COUNT(p.*) as cnt ";
        } else {
            if($is_bonus) {
                $select = "SUM(round(ac.bonus_ammount,2)) as sum, COUNT(ac.*) as cnt ";
            } else {
                $select = "SUM(round(ac.ammount,2)) as sum, COUNT(ac.*) as cnt ";
            }
        }
        
        if($is_konkurs) {
            $contestOpCodes = new_projects::$contestTaxesCodes;
            $contestOpCodes[] = 9;
            $contestOpCodes[] = 86;
            $contestOpCodes[] = 106;
            $contestOpCodesSql = implode(',', $contestOpCodes);
            
            $query[] = "ac.op_code IN ($contestOpCodesSql) ";
            if($is_bonus) {
                $query[] = "ac.bonus_ammount <> 0";
            } else {
                $query[] = "ac.bonus_ammount = 0";
            }
        } else {
            if($is_bonus) {
                $query[] = "ac.op_code = 54";
            } else {
                $query[] = "ac.op_code IN (8,53,113,192)";
            }
            
        }
        $query_str = implode(" AND ", $query);
        
        $this->_getIgnoreInStats();
        
        if($type === '') {
            $sql = "SELECT 
                        {$select}
                    FROM
                        account_operations as ac 
                        INNER JOIN account a ON a.id = ac.billing_id AND NOT (op_date >= '2011-01-01' AND a.uid IN (". implode( ',', $this->aIgnoreInStats ) ."))
                    WHERE 
                        ac.op_date >= '$from_date'::date AND ac.op_date < '$to_date'::date+'1 day'::interval AND {$query_str}";   
        } else {
            $sql = "SELECT 
                        {$select}
                    FROM
                        projects_payments as p 
                        INNER JOIN projects prj ON prj.id = p.project_id
                        INNER JOIN account_operations as ac ON p.opid=ac.id 
                        INNER JOIN account a ON a.id = ac.billing_id AND NOT (op_date >= '2011-01-01' AND a.uid IN (". implode( ',', $this->aIgnoreInStats ) ."))
                    WHERE 
                        ac.op_date >= '$from_date'::date AND ac.op_date < '$to_date'::date+'1 day'::interval AND {$query_str}";
        }
        
        return $DB->row($sql);
    }

	/**
	 * Получить статистику по операциям за указанный период
	 *
	 * @param array  $op			массив кодов операций (или номер одной операции) (op_codes)
	 * @param string $from_date		c какого дня
	 * @param string $to_date		по какой день
	 * @param string $addit			доп. условия (в WHERE после AND)
	 * @return arrray				[сумма в FM, кол-во операций]
	 */
	function GetStatOP($op, $from_date = '2000-01-01', $to_date = 'now()', $addit="", $join = "", $app="") {
        global $DB;
        if (in_array($addit, array('0', '1', '2', '3'))) {
            //Услуги платных проектов по отдельности
            $sql = "SELECT SUM(round(p.ammount,2)) as sum, COUNT(p.*) as cnt 
                    FROM projects_payments as p 
                    INNER JOIN projects prj ON prj.id = p.project_id
                    INNER JOIN account_operations as ac ON p.opid=ac.id 
                    INNER JOIN account a ON a.id = ac.billing_id 
                           AND NOT (op_date >= '2011-01-01' AND a.uid IN (SELECT uid FROM users WHERE ignore_in_stats = TRUE))
                    WHERE ac.op_date >= '$from_date' AND ac.op_date < '$to_date'::date+'1 day'::interval AND pay_type=$addit ";
            
        } else {
            if ($op[0] == 23) {
                $cond = " AND ammount>=0";
            } else {
                $cond = "";
            }
            if ($addit) $addit .= " AND";
            if ($app) $app = ", " . $app;
            if ($op) {
                $op = (is_array($op)) ? "op_code IN ('" . implode("','", $op) . "')" : "op_code = '$op'";
                $op = " AND " . $op;
            } else {
                $op = "";
            }
            $sql = "SELECT SUM(trs_sum) as trsum, SUM(round(ammount,2)) as sum, COUNT(*) as cnt $app 
                    FROM account_operations 
                    INNER JOIN account a ON a.id = account_operations.billing_id 
                           AND NOT (op_date >= '2011-01-01' AND a.uid IN (SELECT uid FROM users WHERE ignore_in_stats = TRUE))
                    $join 
                    WHERE $addit op_date >= '$from_date' AND op_date < '$to_date'::date +'1 day'::interval " . $op;
            $sql .= $cond;
        }
        return $DB->row($sql);
    }

	
   /**
    * Получить массив статистики за указанный период
    *
    * @param Array $from_date		c какого дня
    * @param Array $to_date		по какой день
    * @param Bool $ignore_staff		игнорировать своих (users.ignore_in_stats)
    * @return arrray				[сумма в FM, кол-во операций]
    */
    function GetStatOPEx($from_date = '2000-01-01', $to_date = 'now()', $ignore_staff = false) {
        global $DB;
        
        $ignore_sql = "";
        if ($ignore_staff) {
            $this->_getIgnoreInStats();
            $ignore_sql = "INNER JOIN account a ON a.id = ac.billing_id 
               AND NOT (op_date >= '2011-01-01' AND a.uid IN (". implode( ',', $this->aIgnoreInStats ) ."))";
        }
        
        $ret = $DB->rows(
            "SELECT SUM(trs_sum) as trsum, SUM(round(ammount,2)) as sum, COUNT(*) as cnt, op_code 
                FROM account_operations ac
                {$ignore_sql}
                WHERE op_date >= ? AND op_date < ?::date +'1 day'::interval AND payment_sys != 7
                GROUP BY op_code", $from_date, $to_date
        );
        $out = array();
        if ($ret) {
            foreach ($ret as $row) {
                $out[$row['op_code']] = $row;
            }
        }
        return $out;
    }
	
    /**
     * Получить статистику по PRO
     *
     * @param string  $from_date	c какого дня
     * @param string  $to_date		по какой день
     * @param integer $emp			по работодательскому ПРО (чьи аккаунты про будем смотреть, работодателей либо фрилансеров, если 1 то работодателей)
     * @return array				[сумма в FM, кол-во операций]
     */
    function GetPROStat($from_date = '2000-01-01', $to_date = 'now()', $emp = 1) {
        global $DB;
        $utbl = $emp ? 'employer' : 'freelancer';
        $op = '1,2,3,4,5,6,15,48,49,50,51,76,131,132';
        $sql = "
            SELECT SUM(round(ao.ammount,2)) as sum, COUNT(ao.id) as cnt
              FROM account_operations ao
             INNER JOIN
               account a
                 ON a.id = ao.billing_id
             INNER JOIN
               {$utbl} u
                 ON u.uid = a.uid AND NOT (op_date >= '2011-01-01'::date AND ignore_in_stats = TRUE)
              WHERE op_date >= '{$from_date}'::date AND op_date < '{$to_date}'::date +'1 day'::interval
                AND ao.op_code IN ({$op})
          ";
        return $DB->row($sql);
    }

	/**
	 * Получить юзеров по номеру группы операции
	 *
	 * @param integer $akop			номер группы
	 * @param string $fdate			c какого дня
	 * @param string $tdate			по какой день
	 * @param string $sDomainId опуионально. ID платного источника трафика. Тогда берутся только юзеры пришедшие с него.
	 * @return array				[[логин, имя, фамилия, фото, регистрационный ip, последний ip, e-mail, кол-во посещений]]
	 */
	function GetUsersByAkOp( $akop, $fdate, $tdate, $sDomainId = '' ){
		global $DB;
  
  $ignore = true;
  $ignore_sql = "";
  $join = "";
  
  $inc = array(16,17,22,23,24,25,26,27,28,89,29);
  
		switch ($akop){
			case 1: $opcode = "op_code IN ('1','2','3','4','5','6','15','131','132', '48','49','50','51','52','76', 108) AND role&'100000'='000000' "; break; //PRO freelancer -- добавляем акцию "Масленица" 2012 года
			case 11: $opcode = "op_code IN ('1','2','3','4','5','6','15') AND role&'100000'='100000' "; break; //PRO employers
			case 2: $opcode = "op_code IN (10,11)"; break;
            case 3: $opcode = "op_code IN (8,53,54,113,192)"; break;
			case 4: $opcode = "op_code IN (16,17,18) AND ammount <> 0"; break;
			case 5: $opcode = "op_code IN (19) AND ammount <> 0"; break;
			case 6: $opcode = "op_code IN (20) AND ammount <> 0"; break;
			case 7: $opcode = "op_code IN (21) AND ammount <> 0"; break;
			case 8: $opcode = "op_code IN (9)"; break;
			case 9: $opcode = "op_code IN (7, 87, 103)"; break;
			case 10: $opcode = "op_code IN (23)"; break;
			case 14: $opcode = "op_code IN (27) AND account.id <> 134"; break;
			case 12: $opcode = "op_code IN (26) AND account.id <> 134"; break;
			case 13: $opcode = "op_code IN (39) AND account.id <> 134"; break;
			case 15: $opcode = "op_code IN (47, 114)"; break;
   
			case 16: $opcode = "payment_sys = 7"; break; //смс
			case 17: $opcode = "op_code IN (12) AND payment_sys = 8"; break; //осмп
			case 22: $opcode = "op_code IN (12) AND payment_sys = 3"; break; //яд

			case 23: $opcode = "op_code IN (12) AND payment_sys = 1"; break; //wmz
			case 24: $opcode = "op_code IN (12) AND payment_sys = 2"; break; //wmrч
			case 25: $opcode = "op_code IN (12) AND payment_sys = 4"; break; //бн
			case 26: $opcode = "op_code IN (12) AND payment_sys = 17"; break; //сб
			case 27: $opcode = "op_code IN (12) AND payment_sys = 6"; break; //карта
			case 28: $opcode = "op_code IN (12) AND payment_sys = 9"; break; //киви
			case 89: $opcode = "op_code IN (12) AND payment_sys = 16"; break; //альфа банк
			case 29: $opcode = "op_code IN (12) AND payment_sys = 10"; break; //wmrб
            case 117: $opcode = "op_code IN (12) AND payment_sys = 13"; break; //wmrб

            case 18: $opcode = "op_code IN (61)"; break;
            case 19: $opcode = "op_code IN (62) AND payment_sys = 7"; break;
            case 20: $opcode = "op_code IN (65)"; break;
			case 21: $opcode = "op_code IN (55) AND payment_sys = 7"; break;
			case 101: $opcode = "op_code IN (8,53,113,192)"; break; // Платные проекты
			case 102: $opcode = "op_code = 54"; break; // Платные проекты (бонус)
			case 103:
                $opCodes = implode(',', new_projects::getContestOpCodes());
                $opcode = "op_code IN ($opCodes) AND bonus_ammount = 0";
                break; // Конкурсы
			case 104: $opcode = "op_code IN (9, 86, 106) AND bonus_ammount <> 0"; break; // Конкурсы (бонус)
			case 107: $opcode = "op_code IN (107)"; break;
   

    case 45: 
        $opcode = "op_code IN (45) AND NOT ((role&'000010')='000010' OR (role&'000100')='000100') AND mass_sending.is_accepted='t'";
        $join .= " LEFT JOIN mass_sending ON account_operations.id=mass_sending.account_op_id ";
        break;

			case 70: $opcode = "op_code IN (70)"; break;
			case 74: $opcode = "op_code IN (74)"; break;
			case 71: $opcode = "op_code IN (71) AND payment_sys = 7"; break; //восстановление пароля
			case 72: $opcode = "op_code IN (72, 88, 104)"; break; //поднятие конкурса
			case 73: $opcode = "op_code IN (73, 108, 109, 111)"; break; // добавляем акцию "Масленица" 2012 года + акция первомай

            case 92: $opcode = "op_code IN (91) AND NOT ((role&'100000')='100000') "; break;
            case 75: $opcode = "op_code IN (93) AND NOT ((role&'100000')='100000') "; break;
            case 76: $opcode = "op_code IN (92) AND ((role&'100000')='100000')"; break;
            case 77: $opcode = "op_code IN (93) AND ((role&'100000')='100000')"; break;
            case 80: $opcode = "op_code IN (80)"; break; // личный менеджер
            case 82: $opcode = "op_code IN (82)"; break; // личный менеджер
            
            //акция - сбер
            case 83: $opcode = "op_code IN (95) AND NOT ((role&'100000')='100000') "; break;
            case 84: $opcode = "op_code IN (97) AND NOT ((role&'100000')='100000') "; break;
            case 85: $opcode = "op_code IN (96) AND ((role&'100000')='100000') "; break;
            case 86: $opcode = "op_code IN (97) AND ((role&'100000')='100000') "; break;
            //акция - безнал
            case 87: $opcode = "op_code IN (99) AND NOT ((role&'100000')='100000')  "; break;
            case 88: $opcode = "op_code IN (101) AND NOT ((role&'100000')='100000') "; break;
            case 90: $opcode = "op_code IN (100) AND ((role&'100000')='100000') "; break;
            case 91: $opcode = "op_code IN (101) AND ((role&'100000')='100000') "; break;
            case 94: $opcode = "op_code IN (94)"; break;    
            case 116: $opcode = "op_code IN(116)"; break;
            case 118: $opcode = "op_code IN(117)"; break; // верификация через FF
            // следующий номер использовать 93, 92 уже используется выше. Это чтобы не забыть

			default: $opcode = "op_code IN (12)";
       $ignore = false;
		}
  
  if (!$akop || in_array($akop, $inc)) {
      $ignore = false;
  }
  
  if ($ignore) {
      $ignore_sql = "AND NOT (op_date >= '2011-01-01' AND users.ignore_in_stats = TRUE)";
  }
        
        if ( !$sDomainId ) {
		return $DB->rows("SELECT DISTINCT users.uid, login, uname, usurname, photo, reg_ip, last_ip, email, role, hits, is_banned, account_operations.op_code, account_operations.ammount, account_operations.op_date, account_operations.comments, account_operations.descr FROM users
        LEFT JOIN account ON users.uid=account.uid 
        LEFT JOIN account_operations ON account.id=billing_id 
        $join
        WHERE $opcode AND op_date >= ? AND op_date < ?::date+'1 day'::interval {$ignore_sql}",
			$fdate, $tdate);
        }
        else {
            $sQuery = '
                SELECT DISTINCT users.uid, login, uname, usurname, photo, reg_ip, last_ip, email, role, 
                    hits, is_banned, account_operations.op_code, account_operations.ammount, 
                    account_operations.op_date, account_operations.comments, account_operations.descr 
                    FROM traffic_stat_uids t 
                    INNER JOIN users ON users.uid = t.uid 
                    INNER JOIN account ON users.uid = account.uid 
                    INNER JOIN account_operations ON account.id = billing_id '
                    . $join . '
                    WHERE t.domain_id = ?i AND ' . $opcode 
                    . " AND op_date >= ? AND op_date < ?::date+'1 day'::interval {$ignore_sql}";
            
            return $DB->rows( $sQuery, $sDomainId, $fdate, $tdate );
        }
	}
	
	/**
	 * Взять данные для платных услуг
	 *
	 * @param array $sql Запросы SQL для объединиеня их в один запрос
	 * @return array
	 */
	function getPayUsers($get, $ds, $de, $filter=false) {
	    global $DB;
		if($filter) {
            $filter = "lower(login) = '".strtolower($filter)."' AND ";
        }
	    
        $acc_op  = array();
        $user_fp = array();
        $orders  = array();
        $acc_specs = array();
        if ($get['pfl'])  {
        	$prefix = "freelancer";
        	$pro[$prefix] =  array(15,16,28,35,42,131,132,48,49,50,51,52,66,67,68, 76);
        }
        
        if ($get['prb'])  {
        	$prefix = "employer";
        	$pro[$prefix] =  array(15,16,28,35,42,48,49,50,51,52,66,67,68, 76);
        }
        
        if ($get['pt']) $orders = array_merge($orders, array(47));
        if ($get['pp']) $acc_op = array(8, 53, 113, 192);
        if ($get['gift']) {
        	$acc_op  = array_merge($acc_op, array(39,40,54));
        	$user_fp = array(17,18,24,25,34);
        	$orders  = array_merge($orders, array(16, 35, 52, 66, 67, 68));
        }
        if ($get['pf']) $user_fp = array_merge($user_fp, array(10, 11, 17, 18));
        if ($get['pca']) $user_fp = array_merge($user_fp, array(19,25,29));
        if ($get['pci']) $user_fp = array_merge($user_fp,  array(20,30));
        if ($get['tr']) $acc_op  = array_merge($acc_op, array(21,23));
        if ($get['con']) $acc_op  = array_merge($acc_op, array(9));
        if ($get['uprj']) $acc_op  = array_merge($acc_op, array(7));
        if ($get['oprj']) $acc_op  = array_merge($acc_op, array(61,62));
        if ($get['bonus']) {
            $acc_op = array_merge($acc_op, array(26,27));
            $orders = array_merge($orders, array(26,27));
        }
        if ($get['chlogin']) $acc_op  = array_merge($acc_op, array(70));
        if ($get['unlock']) $acc_op  = array_merge($acc_op, array(74));
        if ($get['askmanager']) $acc_op  = array_merge($acc_op, array(82));

        if ($get['specs']) $acc_specs  = array_merge($acc_specs, array(80));
        if ($get['rating']) $acc_op  = array_merge($acc_op, array(75));
        
        if ($get['alpha']) {
            $acc_op = array_merge($acc_op, array(12));
            $filter .= ' payment_sys = 11 AND ';
        }
        
        $orders  = array_unique($orders);
        $acc_op  = array_unique($acc_op);
        $user_fp = array_unique($user_fp);
        
        if(count($user_fp) > 0) {
            $sql[] = "SELECT f.uid,  f.login, f.role,  f.uname, f.usurname, a.id, a.billing_id, oc.op_name, a.ammount, a.trs_sum,
                      o.from_date, (o.from_date + o.to_date)::date as to_date, o.tarif, oc.sum as def_ammount
        			  FROM op_codes oc, users_first_page o JOIN users f ON f.uid = o.user_id 
        			  LEFT JOIN account_operations a ON a.id = o.billing_id  
        			  WHERE o.tarif IN(".implode(",", $user_fp).") AND ".($filter?$filter:'')." o.from_date+o.to_date >= '$ds' AND o.from_date < '$de'::date+'1 day'::interval AND oc.id = o.tarif";
        }
        
        if(count($acc_op) > 0) {
            $sql[] = "SELECT users.uid, login, users.role, uname, usurname, ao.id, ao.billing_id, oc.op_name, ao.ammount, ao.trs_sum, 
                      op_date as from_date, op_date::date as to_date, op_code as tarif, oc.sum as def_ammount  
                      FROM users LEFT JOIN account ON account.uid=users.uid LEFT JOIN account_operations ao ON ao.billing_id=account.id LEFT JOIN op_codes oc ON oc.id = op_code 
                      WHERE op_code IN(".implode(",", $acc_op).") AND op_date >= '$ds' AND ".($filter?$filter:'')." op_date < '$de'::date+'1 day'::interval ORDER BY op_date DESC";
        }
        
        if($get['pfl'] && $get['prb']) {
            $orders = array_merge($orders, $pro['freelancer']);
            $orders = array_merge($orders, $pro['employer']);
            $orders = array_unique($orders); 
            
            $sql[] = "SELECT f.uid, f.login, f.role, f.uname, f.usurname, a.id, a.billing_id, oc.op_name, a.ammount, a.trs_sum, 
                      o.from_date, (o.from_date + o.to_date + COALESCE(o.freeze_to, '0')::interval)::date as to_date, o.tarif, oc.sum as def_ammount
        			  FROM op_codes oc, orders o JOIN users f ON f.uid = o.from_id 
        			  LEFT JOIN account_operations a ON a.id = o.billing_id  
        			  WHERE o.tarif IN(".implode(",", $orders).") AND ".($filter?$filter:'')." o.from_date+o.to_date+COALESCE(o.freeze_to, '0')::interval >= '$ds' AND o.from_date < '$de'::date+'1 day'::interval AND oc.id = o.tarif";;
        	   
        } else {
            if(count($pro[$prefix]) > 0)
                $sql[] = "SELECT f.uid, f.login, f.role, f.uname, f.usurname, a.id, a.billing_id, oc.op_name, a.ammount, a.trs_sum, 
                          o.from_date, (o.from_date + o.to_date + COALESCE(o.freeze_to, '0')::interval)::date as to_date, o.tarif, oc.sum as def_ammount
            			  FROM op_codes oc, orders o JOIN $prefix f ON f.uid = o.from_id 
            			  LEFT JOIN account_operations a ON a.id = o.billing_id  
            			  WHERE o.tarif IN(".implode(",", $pro[$prefix]).") AND ".($filter?$filter:'')." o.from_date+o.to_date+COALESCE(o.freeze_to, '0')::interval >= '$ds' AND o.from_date < '$de'::date+'1 day'::interval AND oc.id = o.tarif";
            
            if(count($orders) > 0)
                $sql[] = "SELECT f.uid, f.login, f.role, f.uname, f.usurname, a.id, a.billing_id, oc.op_name, a.ammount, a.trs_sum, 
                          o.from_date, (o.from_date + o.to_date + COALESCE(o.freeze_to, '0')::interval)::date as to_date, o.tarif, oc.sum as def_ammount
            			  FROM op_codes oc, orders o JOIN users f ON f.uid = o.from_id 
            			  LEFT JOIN account_operations a ON a.id = o.billing_id  
            			  WHERE o.tarif IN(".implode(",", $orders).") AND ".($filter?$filter:'')." o.from_date+o.to_date+COALESCE(o.freeze_to, '0')::interval >= '$ds' AND o.from_date < '$de'::date+'1 day'::interval AND oc.id = o.tarif";
               
        }

        if(count($acc_specs)) {
            $sql[] = "SELECT DISTINCT ON (spo.billing_id, op_date) users.uid, login, users.role, uname, usurname, ao.id, ao.billing_id, oc.op_name, ao.ammount, ao.trs_sum,
                      COALESCE(spo.paid_from, op_date) as from_date, COALESCE(spo.paid_to, op_date)::date as to_date, op_code as tarif, oc.sum as def_ammount
                      FROM users LEFT JOIN account ON account.uid=users.uid LEFT JOIN account_operations ao ON ao.billing_id=account.id LEFT JOIN op_codes oc ON oc.id = op_code
                      LEFT JOIN spec_paid_acc_operations spo ON spo.billing_id = ao.id
                      WHERE op_code IN(".implode(",", $acc_specs).") AND op_date >= '$ds' AND ".($filter?$filter:'')." op_date < '$de'::date+'1 day'::interval ORDER BY op_date DESC";
        }
        
		if(count($sql) > 0) {
            $usql = "(".implode(") UNION ALL (", $sql).")"; 
            return $DB->rows($usql);
		}
		return false;
	}
	
	/**
	 * Считаем стоимость за период (Платные услуги)
	 * 
	 * @param integer $ammount  Сумма оплаты
	 * @param integer $days     Период в днях (по выбранному периоду)
	 * @param integer $period   Реальный период оплаченного тарифа 
	 * @return integer
	 */
	function ammountDay($ammount, $days, $period=1) {
	    if($period <= 1) return 0;
	    return round($ammount/$period * $days, 2);
	}
	
	/**
	 * Взять оплаты юзера в определенный период времени
	 *
	 * @param array  $ps     Системы оплаты
	 * @param string $fdate  С какой даты 
	 * @param string $tdate  По какую дату
	 * @param boolean $is_ret_data 		true - возвращать данные запроса, false - позвращать ссылку на результат запроса
	 * @return array Результат выборки
	 */
	function GetUsersPayments($ps, $fdate, $tdate, $is_ret_data=true){
		global $DB;
		if (!$ps) return 0;
		$ps =  (is_array($ps))? "ao.payment_sys IN ('" . implode("','", $ps) . "')" : "ao.payment_sys = '$ps'";
		$sql = "
			SELECT ao.*, u.uid, u.login, u.uname
			  FROM account_operations ao
			INNER JOIN account a ON a.id = ao.billing_id
			INNER JOIN users u ON u.uid = a.uid
			WHERE ao.op_code IN ('12')
			  AND ao.op_date >= ? AND ao.op_date < ?::date+'1 day'::interval
			  AND {$ps}
		";
		if($is_ret_data) {
			$ret = $DB->rows($sql, $fdate, $tdate);
		} else {
			$ret = $DB->query($sql, $fdate, $tdate);
		}
		return $ret;
	}

	/**
	 * Начинает транзакцию (должно быть выполнено перед любой покупкой и подарком) или проверяет существующую
	 *
	 * @param integer $user_id		UID
	 * @param integer $tr_id		идентификатор возможной текущей транзакции
	 * @param boolean $yd			является операцией вывода ЯД
	 * @return integer				идентификатор транзакции
	 */
	function start_transaction($user_id, $tr_id = 0, $yd = false){
		global $DB;
		if ($tr_id) {
			if ($tr_id == $this -> check_transaction($tr_id, $user_id)) return $tr_id;
		}
		if ($yd) {
			return $DB->val("INSERT INTO account_transaction (user_id, yd) VALUES (?, TRUE) RETURNING id", $user_id);
		} else {
			return $DB->val("INSERT INTO account_transaction (user_id) VALUES (?) RETURNING id", $user_id);
		}
	}

	/**
	 * Показывает ошибку
	 *
	 * @param string $error	текст ошибки
	 */
	function view_error($error){
	    $_SESSION['bill.GET']['error'] = $error;
	    header('Location: /bill/fail/');
		exit;
	}

	/**
	 * Проверяет идентификатор транзакции. Если транзакция с таким идентификатором завершена, то возвращает 0,
	 * иначе - идентификатор транзакции
	 *
	 * @param integer $tr_id		идентификатор транзакции
	 * @param integer $user_id		ID Пользователя
	 * @return integer				идентификатор транзакции или 0
	 */
	function check_transaction($tr_id, $user_id){
		global $DB;
		$commited = $DB->val("SELECT commited FROM account_transaction WHERE (user_id=? AND id=?)", $user_id, $tr_id);
		if ($commited == 'f') return $tr_id;
		return 0;
	}

	/**
	 * Завершает транзакцию
	 *
	 * @param integer $tr_id		идентификатор транзакции
	 * @param integer $user_id		ID Пользователя
	 * @param integer $op_id		идентификатор операции
	 * @return integer				Всегда возвращает 0
	 */
	function commit_transaction($tr_id, $user_id, $op_id){
		global $DB;
		if ($op_id == 'null') {
			$DB->query("UPDATE account_transaction SET commited = true, commit_date=now(), opid = null WHERE (user_id=? AND id=?)", $user_id, $tr_id);
		} else {
			$DB->query("UPDATE account_transaction SET commited = true, commit_date=now(), opid = ? WHERE (user_id=? AND id=?)", $op_id, $user_id, $tr_id);
		}
		return 0;
	}

	/**
	 * Перевод денег на другой аккаунт
	 *
	 * @param integer $uid					UID переводящего
	 * @param integer $gid					UID кому переводят
	 * @param integer $sum					сумма
	 * @param integer $transaction_id		идентификатор транзакции
	 * @param string  $comments				комментарии переводящего
	 * @return integer						1 - все ок, 0 - иначе
	 */
	function transfer($uid, $gid, $sum, $transaction_id, $comments, $commit = true, $trs_sum = 0){
		global $DB;
		$user_transfer = $gid;
        if (!$transaction_id || $transaction_id != $this -> check_transaction($transaction_id, $uid)) {
			$this -> view_error("Невозможно завершить транзакцию");
		} else {
			$res = $DB->query("SELECT account.sum, account.id FROM account WHERE account.uid=?", $uid);
			if ($DB->error) return "Ошибка при получении информации о счете!";

			list($ac_sum, $bill_id) = pg_fetch_row($res);

			if ($sum > $ac_sum) return "Недостаточно средств на счету!";

			$ok = $this->GetInfo($gid);
			if (!$ok) {
				$this->CreateNew($gid);
				$this->GetInfo($gid);
			}

			if ($sum < 0) return "Перевод на отрицательную сумму!";
			$descr = '';
			$res = $DB->query(
				"SELECT gid, id FROM MakeGift(?, ?, ?, ?, ?, ?, ?, ?, ?, ?) as (gid integer, id integer)",
				$bill_id, $this->id, 23, $sum, $descr, $comments, $uid, $gid, $sum, $trs_sum
			);
			if ($DB->error) return "Ошибка при записи счета!";
			else {
                $_SESSION['ac_sum'] = $_SESSION['ac_sum'] - $sum;
            }
			list($gid, $id) = pg_fetch_row($res);
			if ($commit) $this -> commit_transaction($transaction_id, $uid, $id);
                        // для счетчика everesttech.net (см. engine/templates/footer.tpl)
                        if ( $sum > 0 ) {
                            $_SESSION['everesttech_conter'] = 1;
                        }
            $user = new users();
            $user->GetUserByUID($user_transfer);
            // Обновляем сессию пользователю сразу при поступлении денежных средств
            $session = new session();
            $session->UpdateAccountSum($user->login);

            // уведомление в юзербаре
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");
            $bar_notify = new bar_notify($user->uid);
            $bar_notify->addNotify('bill', null, "На счет зачислено $sum руб.");

			return 1;
		}
		return 0;
	}

	/**
	 * Возвращает массив всех переводов за указанный период
	 *
	 * @param string $fdate		c какого дня
	 * @param string $tdate		по какой день
	 * @return array
	 */
	function GetTransfers($fdate, $tdate){
		global $DB;
		return $DB->rows("
			SELECT a.*, fuser.login as flogin, fuser.usurname as fusurname, fuser.uname as funame, fuser.role as frole, fuser.photo as fphoto,
			tuser.login as tlogin, tuser.usurname as tusurname, tuser.uname as tuname, tuser.role as trole, tuser.photo as tphoto,
			present.id
			FROM (SELECT id, ammount, op_date FROM account_operations WHERE (op_date >= ? AND op_date - '1 day'::interval <= ?) AND op_code = 23 AND ammount < 0) as a
			LEFT JOIN present ON billing_from_id = a.id LEFT JOIN account_operations ON billing_to_id = account_operations.id
			LEFT JOIN users fuser ON fuser.uid = from_uid LEFT JOIN users tuser ON tuser.uid=to_uid ORDER BY op_date
		", $fdate, $tdate);
	}

	/**
	 * Возвращает массив всех подарков за указанный период
	 *
	 * @param string $fdate		c какого дня
	 * @param string $tdate		по какой день
	 * @return array
	 */
	function GetGifts($fdate, $tdate){
		global $DB;
		return $DB->rows("
			SELECT a.*, op_codes.op_name as op_name, fuser.login as flogin, fuser.usurname as fusurname, fuser.uname as funame, fuser.role as frole, fuser.photo as fphoto,
			tuser.login as tlogin, tuser.usurname as tusurname, tuser.uname as tuname, tuser.role as trole, tuser.photo as tphoto,
			present.id
			FROM (SELECT id, ammount, op_date, op_code FROM account_operations WHERE (op_date >= ? AND op_date - '1 day'::interval <= ?) AND op_code IN (17, 16, 52, 69, 66, 67, 83, 84, 85) AND ammount < 0) as a
			LEFT JOIN present ON billing_from_id = a.id LEFT JOIN account_operations ON billing_to_id = account_operations.id
			LEFT JOIN op_codes op_codes ON op_codes.id=a.op_code  
			LEFT JOIN users fuser ON fuser.uid = from_uid LEFT JOIN users tuser ON tuser.uid=to_uid ORDER BY op_date
		", $fdate, $tdate);
	}

	/**
	 * Отменить перевод денег.
	 *
	 * @param integer $id		идентификатор перевода (из таблицы подарков!)
	 * @return integer			Всегда возвращает 0
	 */
	function DropTransfer($id){
		global $DB;
        $q = $DB->query("SELECT billing_from_id, billing_to_id FROM present WHERE id=?", $id);
		list($from, $to) = pg_fetch_row($q);
		$DB->query("DELETE FROM account_operations WHERE id = ?", $from);
		$DB->query("DELETE FROM account_operations WHERE id = ?", $to);
		return 0;
	}

	/**
	 * Возвращает название платежной системы
	 * 
	 * @param  int $payment_sys код платежной системы
	 * @return string
	 */
    public static function GetPSName( $payment_sys ) {
        $out = '';
        switch((int)$payment_sys) {
            case 7:
                $out .= 'СМС';
                break;
            case 8:
                $out .= 'ОСМП';
                break;
            case 3:
                $out .= 'Яндекс.Деньги';
                break;
            case 1:
                $out .= 'WMZ';
                break;
            case 2:
                $out .= 'WMR';
                break;
            case 4:
                $out .= 'безналичный расчет';
                break;
            case 5:
                $out .= 'квитанцию Сбербанка';
                break;
            case 6:
                $out .= 'пластиковую карту';
                break;
            case 9:
                $out .= 'QIWI кошелек';
                break;
            case 10:
                $out .= 'WMR';
                break;
            case 11:
                $out .= 'Альфа-Банк';
                break;
            case 13:
                $out .= 'Веб-кошелек';
                break;
            case 14:
                $out .= 'OKPAY';
                break;
            case 15:
                $out .= 'Плати потом';
                break;
            case 16: 
                $out .= "Альфа-Клик";
                break;
            case 17: 
                $out .= "Сбербанк Онлайн";
                break;
            default:
                $out = 'пополнение счета';
                break;
        }
        
        return $out;
    }

	/**
	 * Информация об операции
	 *
	 * @param integer $bill_id		идентификатор операции
	 * @return string				информация об операции				
	 */
	function GetOrderInfo($bill_id, $uid = NULL, $mode = 1){
		global $DB;
        $q = $DB->query("SELECT descr, payment_sys, billing_id, op_code FROM account_operations WHERE id = ?i", $bill_id);
        if(!$q) return NULL;
		list($out, $payment_sys, $billing_id, $op_code) = pg_fetch_row($q);
		if($mode!=2 && $op_code == 12){// пополнение счета
			$out = 'Пополнение счета через '.self::GetPSName($payment_sys);
			//return $out;
		}
                
        switch($payment_sys) {
            case 4:
                // Безнал юр. лица
				if($reqv = $DB->row("SELECT * FROM reqv_ordered WHERE billing_id = ?i", $bill_id)) {
                    $out = 'Перевод по безналу для юр. лиц, '.$reqv['full_name'].', счет Б-'.$billing_id.'-'.($reqv['bill_no']+1).', заказ '.$reqv['id'];
                }
                break;
            case 5:
                // Сбербанк физ. лица
                if($bank = $DB->row("SELECT * FROM bank_payments WHERE billing_id = ?i", $bill_id)) {
                    $out = 'Перевод по безналу для физ. лиц, '.$bank['bill_num'].', заказ '.$bank['id'];
                }
                break;
        }
		return $out;
	}
	
	/**
	 * Снять зарезервированные деньги со счета (СБР)
	 *
	 * @param integer $eid				uid работодателя
	 * @param integer $reserve_id		id операции резервирования денег работодателем
     * @param string  $descr            комменты к транзакции
     * @param string  $comments         комменты к операции для юзера.
	 * @return integer					идентификатор операции списания денег
	 */
    function CommitReserved($eid, $reserve_id, $descr, $op_code = 37, $sum = NULL, $comments = NULL){
		global $DB;
		$user_account = new account();
		$user_account->GetInfo($eid);
        $q = $DB->query("SELECT payment_sys, trs_sum FROM account_operations WHERE id = ?", $reserve_id);
		list($reserve_pay_sys, $reserve_sum) = pg_fetch_row($q);
        if($sum === NULL) $sum = $reserve_sum;
        return $DB->insert('account_operations', array(
			'billing_id'  => $user_account->id,
			'op_code'     => $op_code,
			'ammount'     => 0,
			'descr'       => $descr,
			'comments'    => ($comments? $comments: NULL),
			'payment_sys' => $reserve_pay_sys,
			'trs_sum'     => -$sum
		), 'id');
		
		// количество операций
		$_SESSION['account_operations'] = intval($_SESSION['account_operations']) + 1;
	}
	
	/**
	 * Зачислить деньги после списания с резерва (или арбитража) (СБР)
	 *
	 * @param integer $fid				uid кому зачисляем
	 * @param float   $sum				сумма
	 * @param integer $money_type		тип денег
     * @param string  $descr            комменты к операции
	 * @param string  $errors			возвращает массив ошибок
	 * @param integer $op_code			код операции
     * @param string  $comments         комменты к операции для юзера.
	 * @return integer					id операции в account_operations
	 */
    function TransferReserved($fid, $sum, $money_type, $descr, &$errors, $op_code = 38, $comments = NULL){
		global $DB;
		$user_account = new account();
		$user_account->GetInfo($fid);
		//Если в ФМ, то надо пополнять сразу
		$ammount =($money_type == 0)?$sum:0;
        $frl_accept_id = $DB->insert('account_operations', array(
            'billing_id'  => $user_account->id,
            'op_code'     => $op_code,
            'ammount'     => $ammount,
            'descr'       => $descr,
            'comments'    => ($comments? $comments: NULL),
            'payment_sys' => $money_type,
            'trs_sum'     => $sum
        ), 'id');
			
        // количество операций
        $_SESSION['account_operations'] = intval($_SESSION['account_operations']) + 1;
		return $frl_accept_id;
	}
	
	/**
	 * Получить TOP-100 пользователей, которые принесли больше всего денег
	 *
	 * @param  string $users_table  используемая таблица
	 * @param  date   $period_from	дата начала периода
	 * @param  date   $period_to	дата окончания периода
	 * @return array
	 */
	function GetMoneyTop100( $users_table = 'users', $period_from='1970-01-01', $period_to = 'NOW()' ) {
		global $DB;
		return $DB->rows("SELECT sum(ammount) as sum_ammount, account.uid, u.login, u.uname, u.usurname
			FROM account_operations
			LEFT JOIN account ON account.id=account_operations.billing_id
			INNER JOIN {$users_table} u ON account.uid = u.uid
			WHERE (ammount > 0) AND (account_operations.op_code = 12)
			AND (account_operations.op_date BETWEEN ? AND ?)
			GROUP BY account.uid, u.login, u.uname, u.usurname
			ORDER BY sum_ammount DESC
			LIMIT 100 OFFSET 0", $period_from, $period_to);
	}

	/**
	 * Получить сумму денег, которые принес пользователь за определенный период
	 *
	 * @param array $uarray		uid пользователей
	 * @param date $period_from	дата начала периода
	 * @param date $period_to	дата окончания периода
	 * @return array
	 */
	function GetMoneyTop100UsersByPeriod($uarray, $period_from = '2000-01-01', $period_to = 'NOW()'){
		global $DB;
		return $DB->rows("
			SELECT account.uid, sum(ammount) as sum_month
			FROM account_operations
			LEFT JOIN account ON account.id=account_operations.billing_id
			WHERE (ammount > 0) AND (account_operations.op_code = 12) 
			AND (account.uid IN (?l)) AND (account_operations.op_date BETWEEN ? AND ?)
			GROUP BY account.uid
		", $uarray, $period_from, $period_to);
	}
	
	/**
	 * Найти данные по операции по описанию
	 *
	 * @param string $text   текст описания
	 * @return array   информация по операции.
	 */
	function SearchPaymentByDescr($text){
		global $DB;
		return $DB->row("SELECT * FROM account_operations WHERE descr ILIKE ?", "%{$text}%");
	}
	
	/**
	 * Взять юзера по идентификатору его счета
	 *
	 * @param integer $id   ид счета.
	 * @return array   данные по юзеру
	 */
	function GetUserByAccID($id){
		global $DB;
		return $DB->row("SELECT * from users INNER JOIN account ON account.uid=users.uid WHERE account.id = ?", $id);
	}
	
	
	/**
	 * Взять данные для истории по периоду (новый личный счет)
	 *
	 * @see  class page_bill
	 * 
	 * @param string  $sdate 	Дата начало периода
	 * @param string  $edate 	Дата окончание периода
	 * @param integer $sort  	Тип сортировки (1,2 - сортировка по дате; 3,4 - сортировка по коду операции (op_codes); 5,6 - сортировка по сумме) по умолчанию сортировка по дате
	 * @param string  $type  	Доп переменная для фильтровки результатов, по умолчанию false @see page_bill::historyAction() 
	 * @param integer $page  	Страница выборки
	 * @param integer $pages 	Возвращает количество страниц 
	 * @param integer $total 	Возвращает количество данных выборки

	 * @param integer $count 	Число показа данных на странице
	 * @return array 			Данные выборки
	 */
	function searchBillHistory($sdate, $edate, $sort, $type=false, $page=1, &$pages, &$total, $count=10) {
		global $DB;
		$uid = get_uid(false);
		
		switch($sort) {
			case 1:
				$sort = "op_date DESC";
				break;
			case 2:
				$sort = "op_date ASC";
				break;
			case 3:
				$sort = "op_code DESC";
				break;
			case 4:	
				$sort = "op_code ASC";
			case 5:	
				$sort = "ammount DESC";
				break;
			case 6:
				$sort = "ammount ASC";
				break;
			default:
				$sort = "op_date DESC";
				break;			
		}

		$page--;
		if($page<0) $page = 0;
		$page_sql = $page*$count;
		$limit = $count ? "LIMIT $count OFFSET $page_sql" : '';
        
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
        
        $opCodes = new_projects::getContestOpCodes();
        $opCodesSql = implode(',', $opCodes);
        
		// op_code <> 77 СБР не выводим тк деньги через ЛС не проходят #0020554 
		$sql = "SELECT op_date, op_name, ammount, comments, op_code, account_operations.id, round(balance,2) as balance, trs_sum, payment_sys, p.id as project_id
                FROM account_operations
				LEFT JOIN account ON account.id=account_operations.billing_id
				LEFT JOIN op_codes on op_code=op_codes.id LEFT JOIN account_operations_blocked ob ON ob.operation_id = account_operations.id 
                LEFT JOIN projects p
                    ON op_code IN ($opCodesSql) AND account_operations.id = p.billing_id
                WHERE ( op_code IN (".sbr::OP_RESERVE.", ".sbr::OP_CREDIT.", ".sbr::OP_DEBIT.") AND ammount = 0 ) = false AND  ob.id IS NULL AND uid='$uid' AND op_date <= '".date('c', $edate)."' AND op_date >= '".date('c', $sdate)."' ".($type!=false?$type:'')." ORDER BY $sort $limit";
		
		
		$sql_total_page =  "SELECT COUNT(*) as total FROM account_operations LEFT JOIN account ON account.id=account_operations.billing_id LEFT JOIN op_codes on op_code=op_codes.id 
                            WHERE ( op_code IN (".sbr::OP_RESERVE.", ".sbr::OP_CREDIT.", ".sbr::OP_DEBIT.") AND ammount = 0 ) = false AND uid='$uid' AND op_date <= '".date('c', $edate)."' AND op_date >= '".date('c', $sdate)."' ".($type!=false?$type:'');
		$total  = $DB->val($sql_total_page);
	    $pages  = $count ? ceil($total/$count) : 1;
		
		/*} else {
			$sql = "SELECT op_date, op_name, ammount, comments, op_code, account_operations.id FROM account_operations
				LEFT JOIN account ON account.id=account_operations.billing_id
				LEFT JOIN op_codes on op_code=op_codes.id WHERE uid='$uid' AND op_date <= '".date('c', $edate)."' AND op_date >= '".date('c', $sdate)."' AND op_code IN(".implode(",", $type).") ORDER BY $sort";
		}*/
		
		
		//echo $sql;
		
		return $DB->rows($sql);
	}

    /**
     * возвращает историю платежей
     * @param int $page номер страницы
     * @param int $itemsPerPageсколько пунктов на одной странице
     * @param string $startTime дата начала периода
     * @param int $opCode назначение платежа
     */
    public static function getBillHistory ($page = 1, $itemsPerPage = null, $startTime = null, $opCode = null, $getCount = true) {
        global $DB;

        $uid = get_uid(false);

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
        $prjCodes = new_projects::$contestTaxesCodes;
        $prjCodes[] = new_projects::OPCODE_KON;
        $prjCodes[] = new_projects::OPCODE_PAYED_KON;
        $prjCodes[] = new_projects::OPCODE_KON_NOPRO;
        $prjCodes[] = new_projects::OPCODE_PRJ_OFFICE;
        $prjCodes[] = new_projects::OPCODE_PAYED;
        $prjCodes[] = new_projects::OPCODE_PAYED_BNS;
        $prjCodes[] = new_projects::OPCODE_TOP_NEW;
        $prjCodes[] = new_projects::OPCODE_LOGO;
        $prjCodes[] = new_projects::OPCODE_URGENT;
        $prjCodes[] = new_projects::OPCODE_HIDE;
        $prjCodes[] = new_projects::OPCODE_PRJ_OFFICE;
        $prjCodes[] = new_projects::OPCODE_PRJ_OFFICE_PRO;
        
        $prjCodesSql = implode(',', $prjCodes);

        $page = $page < 1 ? 1 : $page;
        $offset = ($page - 1) * $itemsPerPage;
        $limit = $itemsPerPage ? "LIMIT $itemsPerPage OFFSET $offset" : '';

        if ($startTime) {
            $whereDateSql = $DB->parse('AND ao.op_date >= ?', date('c', $startTime));
            $whereDataReserveSql = $DB->parse('AND create_time >= ?', date('c', $startTime));
        }

        if ($opCode) { // назначение платежа
            switch ($opCode) {
                case 77:
                    $whereOpCodeSql = 'AND ao.op_code IN (36, 77)';
                    break;
                case 78:
                    $whereOpCodeSql = 'AND ao.op_code IN (37, 78)';
                    break;
                case 79:
                    $whereOpCodeSql = 'AND ao.op_code IN (38, 79)';
                    break;
                default:
                    $whereOpCodeSql = $DB->parse('AND ao.op_code = ?i', $opCode);
                    break;
            }
        }

        $result                  = array();
        $reserve_operation       = "";
        $reserve_operation_count = "";
        
        if($opCode == NULL) {
            $reserve_operation = "UNION
            SELECT create_time as op_date, round(bq.ammount, 2) as ammount, null::text as descr, null::text as comments, ".billing::RESERVE_OP_CODE." as op_code, id,
                null::numeric as balance, null::integer as trs_sum, payment as payment_sys,
                'Список заказов №'|| id ||' на сумму ' || round(bq.ammount, 2) || ' руб' as op_name,
                null::integer as project_id, status, FALSE
            FROM bill_reserve
            INNER JOIN bill_queue_ammount as bq ON bq.reserve_id = bill_reserve .id
            WHERE uid = ?i AND complete_time IS NULL AND status = ?
            {$whereDataReserveSql}   
            ORDER BY op_date DESC, id DESC";
            
            $reserve_operation_count = "UNION
            SELECT COUNT(*) as cnt
            FROM bill_reserve 
            WHERE uid = ?i AND complete_time IS NULL AND status = ?
            {$whereDataReserveSql}";
        }
        
        $sql = "
            WITH bill_queue_ammount AS (
                SELECT SUM(CASE WHEN pro_ammount > 0 THEN pro_ammount ELSE ammount END) as ammount, reserve_id FROM bill_queue
                WHERE uid = ?i AND status = ? GROUP BY reserve_id
            )
            SELECT ao.op_date, round(ao.ammount,2) as ammount, ao.descr, ao.comments, ao.op_code, ao.id, round(ao.balance,2) as balance, ao.trs_sum, ao.payment_sys,
                oc.op_name,
                p.id as project_id, null::text as status, ao.op_code IN ($prjCodesSql) as is_project
            FROM account_operations ao
            INNER JOIN account a
                ON a.id = ao.billing_id
            INNER JOIN op_codes oc
                ON ao.op_code = oc.id
            LEFT JOIN account_operations_blocked aob
                ON aob.operation_id = ao.id
            LEFT JOIN projects p
                ON ao.op_code IN ($prjCodesSql) AND ao.id = p.billing_id
            WHERE ( ao.op_code IN (".sbr::OP_RESERVE.", ".sbr::OP_CREDIT.", ".sbr::OP_DEBIT.") AND ao.ammount = 0 ) = false AND aob.id IS NULL AND a.uid = ?i
            $whereDateSql
            $whereOpCodeSql
            $reserve_operation    
            $limit";
        $result['items'] = $DB->rows($sql, $uid, billing::STATUS_RESERVE, $uid, $uid, billing::STATUS_RESERVE);
        // считаем общее количество денежных операций
        if ($itemsPerPage && $getCount) {
            $sqlItemsCount =  "
                WITH count_history AS (
                    SELECT COUNT(*) as cnt
                    FROM account_operations ao
                    INNER JOIN account a
                        ON a.id = ao.billing_id
                    INNER JOIN op_codes oc
                        ON ao.op_code = oc.id
                    LEFT JOIN account_operations_blocked aob
                        ON aob.operation_id = ao.id
                    WHERE ( ao.op_code IN (".sbr::OP_RESERVE.", ".sbr::OP_CREDIT.", ".sbr::OP_DEBIT.") AND ao.ammount = 0 ) = false AND aob.id IS NULL AND a.uid = ?i
                    $whereDateSql
                    $whereOpCodeSql
                        UNION
                    SELECT COUNT(*) as cnt
                    FROM bill_reserve WHERE uid = ?i AND complete_time IS NULL AND status = ?
                )
                SELECT SUM(cnt) FROM count_history
            ";

            $itemsCount  = $DB->val($sqlItemsCount, $uid, $uid, billing::STATUS_RESERVE);
            $result['pagesCount']  = ceil($itemsCount / $itemsPerPage);
        }

        return $result;
    }
	
	/**
	 * Берем все события произошедшие за данный период (новый личный счет)
	 *
	 * @see  class page_bill 
	 * 
	 * @param string $sdate Дата начала периода
	 * @param string $edate Дата окочнация периода
	 * @return array Данные выборки
	 */
	function searchBillEvent($sdate, $edate) {
		global $DB;
		$uid = get_uid(false);
		
		$event = $DB->rows("
			SELECT op_name, op_code FROM account_operations
			LEFT JOIN account ON account.id=account_operations.billing_id
			LEFT JOIN op_codes on op_code=op_codes.id 
			WHERE uid=? AND op_date <= ? AND op_date >= ?
			    AND (op_code IN (".sbr::OP_RESERVE.", ".sbr::OP_CREDIT.", ".sbr::OP_DEBIT.") AND ammount = 0 ) = false
			GROUP BY op_code, op_name
		", $uid, date('c', $edate), date('c', $sdate));

		if(!$event) return false;
		foreach($event as $k=>$v) {
			if($v['op_code'] == 36) $v['op_code'] = 77;
			if($v['op_code'] == 37) $v['op_code'] = 78;
			if($v['op_code'] == 38) $v['op_code'] = 79;
			$result[$v['op_code']] = $v['op_name'];
		}
		
		return $result;
	}
	
	/**
	 * Выбираем дни в месяце где были операции (новый личный счет)
	 *
	 * @see  class page_bill 
	 * 
	 * @param integer  $month    Месяц (если false, берем текущий месяц)
	 * @param integer  $uid      ИД Пользовтаеля  (если false, берем юзера из сессии)
	 * @param array    $type     Типы операция (op_codes) 
	 * @param integer  $year     Год (если false, берем текущий год)
	 * @return array 			 Данные выборки
	 */
	function getDateBillOperation($month=false, $uid=false, $type=false, $year = false)  {
		global $DB;
		if(!$month) $month = date('m');
		if(!$year) $year = date('Y');
		if(!$uid) $uid = get_uid(false);
		
		$sdate = mktime(0,0,0,$month, 1, $year);
		$edate = mktime(23,59,58,$month, date('t'), $year);
		
		
		if(!$type) {
			$d = $DB->rows("
				SELECT op_date, op_name, ammount, comments, op_code, account_operations.id FROM account_operations
				LEFT JOIN account ON account.id=account_operations.billing_id
				LEFT JOIN op_codes on op_code=op_codes.id WHERE uid=? AND op_date <= ? AND op_date >= ? ORDER BY op_date DESC
			", $uid, date('c', $edate), date('c', $sdate));
		} else {
			$d = $DB->rows("
				SELECT op_date, op_name, ammount, comments, op_code, account_operations.id FROM account_operations
				LEFT JOIN account ON account.id=account_operations.billing_id
				LEFT JOIN op_codes on op_code=op_codes.id WHERE uid=? AND op_date <= ? AND op_date >= ? AND op_code IN(?l) 
				ORDER BY op_date DESC
			", $uid, date('c', $date), date('c', $sdate), $type);
		}
		
		if($d) {
			foreach($d as $k=>$v) {
				$op = date("d", strtotime($v['op_date']));
				$ret[(int)$op] = (int)$op;
			}
			
			return $ret;
		} 
		
		return false;
	}	

    /**
     * Выводит информацию по операциям SMS для админки.
     * Разбирает поле account_operations.descr (см. /classes/ifreepay.php) для получения данных.
     * @see ifreepay::processRequest()
     *
     * @param string $from_date   начало периода (включительно).
     * @param string $to_date     конец периода (включительно).
     * @return array
     */
    function getSmsInfo($from_date = '2009-01-01', $to_date = 'now()') {
        global $DB;
		return $DB->rows(
        "select substring(descr from E'\\\\(\\\\w{2}\\\\)') as country,
                replace(substring(descr from E'номер \\\\d{4}'),'номер ','') as phone,
                replace(substring(descr from E'оператор [^,]*'),'оператор ','') as oper,
                count(*) as count,
                sum(trs_sum)*0.5 as sum,
                sum(coalesce(so.profit, (trs_sum*30*0.5))) as profit
           from account_operations
           LEFT JOIN sms_operations so ON operation_id = account_operations.id
          where (so.id IS NULL OR so.profit IS NOT NULL) AND payment_sys = 7
            and substr(descr,1,4) <> 'ОСМП'
            and op_date >= ? and op_date < ?::date +'1 day'::interval
          group by substring(descr from E'\\\\(\\\\w{2}\\\\)'), 
                   replace(substring(descr from E'номер \\\\d{4}'),'номер ',''),
                   replace(substring(descr from E'оператор [^,]*'),'оператор ','')
          order by country, phone, oper, count
		", $from_date, $to_date);
        return NULL;
    }

    /**
     * Выводит информацию по операциям SMS в CSV для админки.
     * Разбирает поле account_operations.descr (см. /classes/ifreepay.php) для получения данных.
     * @see ifreepay::processRequest()
     *
     * @param string $from_date   начало периода (включительно).
     * @param string $to_date     конец периода (включительно).
     * @return array
     */
    function getSmsInfoInCSV($from_date = '2009-01-01', $to_date = 'now()') {
        global $DB;
        $from_date = pg_escape_string($from_date);
        $to_date   = pg_escape_string($to_date);
		return $DB->rows(
        "select regexp_replace(descr, E'^.*?#(\\\\d+).*$', E'\\\\1') as evtId,
                regexp_replace(descr, E'^.*?с номера (\\\\d+).*$', E'\\\\1') as MSISDN,
                regexp_replace(descr, E'^.*?текст: ([^,]+).*$', E'\\\\1') as SmsText,
                regexp_replace(descr, E'^.*?обработан (\\\\d+).*$', E'\\\\1') as Time--, descr
                from account_operations where op_date >= ? AND op_date < ?::date+1 AND payment_sys = 7
                order by evtId;
		", $from_date, $to_date);
    }
    
	/**
	 * Сортировка в истории 
	 *
	 * @deprecated эта функция нигде не используется вроде как...
	 * 
	 * @param integer $sort Тип сортировки
	 * @param integer $uid  ИД пользователя
	 * @return array
	 */
	function billHistorySort($sort, $uid) {
		global $DB;
		if(!$month) $month = date('m');
		if(!$uid) $uid = get_uid(false);
		
		$sdate = mktime(0,0,0,$month, 1, date('Y'));
		$edate = mktime(23,59,58,$month, date('t'), date('Y'));
		
		switch($sort) {
			case 1:
				$sort = "op_date DESC";
				break;
			case 2:
				$sort = "op_date ASC";
				break;
			case 3:
				$sort = "op_code DESC";
				break;
			case 4:	
				$sort = "op_code ASC";
			case 5:	
				$sort = "ammount DESC";
				break;
			case 6:
				$sort = "ammount ASC";
				break;
			default:
				$sort = "op_date DESC";
				break;			
		}
		
		return $DB->rows("
			SELECT op_date, op_name, ammount, comments, op_code, account_operations.id FROM account_operations
			LEFT JOIN account ON account.id=account_operations.billing_id
			LEFT JOIN op_codes on op_code=op_codes.id WHERE uid=? AND op_date <= ? AND op_date >= ? ORDER BY $sort
		", $uid, date('c', $edate), date('c', $sdate));
	}
	
	/**
	 * Получает кол-во пользователей которые были TESTPRO и купили PRO в указанном периоде
	 *
	 * @param string $fdate     Дата начала периода
	 * @param string $tdate     Дата окончания периода
	 * @return integer          кол-во пользователей
	 */
    function GetStatTestBuyPro($fdate,$tdate) {
        global $DB;
        
        $sql = "
          SELECT COUNT(1)
            FROM (
              SELECT x.from_id, MAX(x.posted) as posted
                FROM (
                  SELECT from_id, MIN(posted) as posted
                    FROM orders
                   WHERE tarif IN (48, 49, 50, 51, 76)
                     AND posted >= ?::date AND posted < ?::date + 1
                   GROUP BY from_id
                ) as o
              INNER JOIN
                orders x
                  ON x.from_id = o.from_id
                 AND x.posted < o.posted
               GROUP BY x.from_id
            ) as x
          INNER JOIN
            orders t
              ON t.from_id = x.from_id
             AND t.posted = x.posted
             AND t.tarif IN (47, 114)        
        ";
        
        return $DB->val($sql, $fdate, $tdate);
    }

	/**
	 * Получает кол-во бонусов полученных пользователями по акции пополнения счета через ЯД. Подробности на /bill/
	 *
	 * @param string $fdate     С даты
	 * @param string $tdate     По дату
	 * @return array            [frl_pro] - pro в подарок, [frl_main] - размещение на главной в подарок, [emp_pro] - pro в подарок, [emp_fm] - 85 FM на бонусный счет
	 */
    function GetStatBonuses($fdate,$tdate) {
        global $DB;
		$stat = array('frl_pro'=>0,'frl_main'=>0,'emp_pro'=>0,'emp_fm'=>0);
        // Фрилансер PRO в подарок
        $data = $DB->row("SELECT count(o.*) as count 
                FROM account_operations as o,
                     account_transaction as t,
                     freelancer as f 
                WHERE op_date>='$fdate 00:00:00.000000' AND 
                      op_date<'$tdate 23:59:59.999999' AND 
                      op_code=26 AND 
                      o.id=t.opid AND 
                      t.user_id=f.uid
                ");
        $stat['frl_pro'] = $data['count'];
        // Фрилансер размещение на главной в подарок
        $sql = "SELECT count(*) as count 
                FROM account_operations  
                WHERE op_date>='$fdate 00:00:00.000000' AND 
                      op_date<'$tdate 23:59:59.999999' AND 
                      op_code=27
                ";
        $data = $DB->row($sql);
        $stat['frl_main'] = $data['count'];
        // Работадатель PRO в подарок
        $sql = "SELECT count(o.*) as count 
                FROM account_operations as o,
                     account_transaction as t,
                     employer as e 
                WHERE op_date>='$fdate 00:00:00.000000' AND 
                      op_date<'$tdate 23:59:59.999999' AND 
                      op_code=26 AND 
                      o.id=t.opid AND 
                      t.user_id=e.uid
                ";
        $data = $DB->row($sql);
        $stat['emp_pro'] = $data['count'];
        // Работадатель 85 FM на бонусный счет
        $sql = "SELECT count(*) as count  
                FROM account_operations  
                WHERE op_date>='$fdate 00:00:00.000000' AND 
                      op_date<'$tdate 23:59:59.999999' AND 
                      op_code=40
                ";
        $data = $DB->row($sql);
        $stat['emp_fm'] = $data['count'];

        return $stat;
    }
    
    /**
	 * Заблокировать операцию
	 *
	 * @param integer $uid			ID Пользователя Который производит блокировку
	 * @param integer $opid			идентификатор типа операции
	 * @return string			сообщение об ошибке
	 */
	function Blocked($uid, $opid){
		$class = $this->GetOperationClassName($opid);
		if ($class) {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/' . $class.".php");
			$rtf = new $class;
			$res = $rtf->BlockedByOpid($uid, $opid);
		}
		return $res;
	}
	
	/**
	 * Разблокировать заблокированную операцию
	 *
	 * @param integer $uid			ИД заблокированной записи 
	 * @param integer $operation	ИД заблокированной операции
	 */
	function unBlocked($uid, $opid) {
		$class = $this->GetOperationClassName($opid);
		
		if($class) {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/' . $class.".php");
			$rtf = new $class;
			$rtf->unBlockedByOpid($uid, $opid);	
		}
	}
	
	
	/**
	 * Статистика про покупкам ПРО аккаунта
	 * 
	 * @param string  $from_date	c какого дня
	 * @param string  $to_date		по какой день
	 * @return array [данные по фрилансерам, данные по работодателям]
	 */
	function getStatsPRO($from_date = '2000-01-01', $to_date = 'now()') {
	    global $DB;
		$f_sql = "SELECT 
                    sum(x.test_pro) as tp, 
                    sum((x.full_pro > 0)::int * x.test_pro) as fpp_tp,
                    sum((x.full_pro > 1)::int * x.test_pro) as fpp2_tp,
                    sum((x.full_pro > 2)::int * x.test_pro) as fpp3_tp,
                    sum((x.full_pro > 3)::int * x.test_pro) as fpp4_tp,
                    sum((x.full_pro > 4)::int * x.test_pro) as fpp5_tp,
                    sum((x.full_pro > 0)::int) as fpp,
                    sum((x.full_pro > 1)::int) as fpp2,
                    sum((x.full_pro > 2)::int) as fpp3,
                    sum((x.full_pro > 3)::int) as fpp4,
                    sum((x.full_pro > 4)::int) as fpp5
                FROM ( SELECT billing_id,
                           sum((op_code <> 47)::int) as full_pro, 
                           sum((op_code = 47)::int) as test_pro
                       FROM account_operations
                       WHERE 
                       op_date >= ? AND op_date < ?::date +'1 day'::interval AND
                       op_code IN (15, 28, 48, 49, 50, 51, 76, 131, 132,    -- обычные покупки.
                                         16, 35, 42, 52, 66, 67, 68, -- подарки.
                                         47)                         -- пробник.
                       GROUP BY billing_id
                     ) as x
                INNER JOIN account a ON a.id = x.billing_id
                INNER JOIN freelancer f ON f.uid = a.uid;";
	   $frl   = $DB->row($f_sql, $from_date, $to_date);
	    
	   
	   $e_sql = "SELECT 
                       sum((x.full_pro > 0)::int) as epp,
                       sum((x.full_pro > 1)::int) as epp2,
                       sum((x.full_pro > 2)::int) as epp3,
                       sum((x.full_pro > 3)::int) as epp4,
                       sum((x.full_pro > 4)::int) as epp5
                FROM ( SELECT billing_id,
                     sum((op_code <> 47)::int) as full_pro 
                     FROM account_operations
                     WHERE 
                     op_date >= ? AND op_date < ?::date +'1 day'::interval AND
                     op_code IN (15, 28, 48, 49, 50, 51, 76,    
                                       16, 35, 42, 52, 66, 67, 68) 
                     GROUP BY billing_id ) as x
                     INNER JOIN account a ON a.id = x.billing_id
                     INNER JOIN employer e ON e.uid = a.uid;";
	   
	   $emp   = $DB->row($e_sql, $from_date, $to_date);
	   
	   return array($frl, $emp);
	}

	/**
	 * Возвращает одну или несколько (одновременных) самых последних операций пользователя.
	 * @param integer $uid   ид. пользователя.
	 * @return array   операции.
	 */
    function getLastOperations($uid) {	
        global $DB;
        $sql = "
            SELECT q.*, oc.op_name, q.is_pending FROM (
                SELECT ao.id, ao.op_code, ao.op_date, ao.payment_sys, ao.trs_sum, ao.ammount, 0 as is_pending
                FROM account a
                INNER JOIN account_operations ao ON ao.billing_id = a.id
                WHERE a.uid = ?

                UNION ALL

                SELECT ao.id, ao.op_code, ao.op_date, 3 as payment_sys, ao.trs_sum, ao.ammount, 1 as is_pending
                FROM account a
                INNER JOIN account_operations_yd ao ON ao.billing_id = a.id
                WHERE a.uid = ?
            ) q
            INNER JOIN op_codes oc ON oc.id = q.op_code
            ORDER BY q.op_date DESC
            LIMIT 1
        ";
		if ($rows = $DB->rows($sql, $uid, $uid)) {
            if( in_array($rows[0]['op_code'], array(12, 77)) ) // deposit()-коды, не проходят через account_transactions.
                return $rows;
        }
        
        $sql  = "
          SELECT ao.*, oc.op_name
            FROM (SELECT * FROM account_transaction WHERE user_id = {$uid} AND commited = true ORDER BY id DESC LIMIT 1) as at
          INNER JOIN
            account a
              ON a.uid = at.user_id
          INNER JOIN
            account_operations ao
              ON ao.billing_id = a.id
             AND ao.op_date BETWEEN at.start_date AND at.commit_date
          INNER JOIN
            op_codes oc
              ON oc.id = ao.op_code
           ORDER BY ao.id
        ";
           
        return $DB->rows($sql);
    }	
	
	/**
	 * Информация о успешно прошедшей операции
	 * 
	 * @param array $data - Информация об операции
	 * @return array информация
	 */
	function getSuccessInfo($data) {
	    if($data['op_code'] == 12) {
	        list($sys, $cur) = self::getPaymentSysName($data['payment_sys']);
	        $suc = array("date"  => $data['op_date'],
	                     "name"  => "Пополнение счета ({$sys})",
	                     "descr" => '',
	                     "sum"   => $data['trs_sum']." {$cur}",   
	                   );
	                   
	        return $suc;
	    }
        if($data['op_code'] == 77) {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
            $suc = array("date"  => $data['op_date'],
                         "name"  => "Резервирование денег («Безопасная Сделка»)",
                         "descr" => '',
                         "sum"   => sbr_meta::view_cost($data['trs_sum'], $data['payment_sys']+1)
                       );
                       
            return $suc;
        }
	    $sum = abs($data['ammount']);
	    return array("date" => $data['op_date'], "name" => $data['op_name'] , "descr" => $data['comments'], "sum"=> $sum." руб.");
	}
	
	/**
	 * Название системы по его ИД
	 *
	 * @param integer $sys ИД Системы
	 * @return string название
	 */
	function getPaymentSysName($sys) {
	    switch($sys) {
	        case 1: $s = "WebMoney WMZ"; $c = "USD"; break;
	        case 2: $s = "WebMoney WMR"; $c = "Руб."; break;
	        case 3: $s = "Яндекс.Деньги"; $c = "Руб."; break;
	        case 4: $s = "Безналичный расчет"; $c = "Руб."; break;
	        case 5: $s = "Сбербанк"; $c = "Руб."; break;
	        case 6: $s = "Пластиковые карты"; $c = "Руб."; break;
	        case 7: $s = "SMS"; $c = "Руб."; break;
	        case 8: $s = "ОСМП"; $c = "Руб."; break;
            case 10: $s = "WebMoney WMR"; $c = "Руб."; break;
            case 16: $s = "Альфа-Клик"; $c = "Руб."; break;
            case 17: $s = "Сбербанк Онлайн"; $c = "Руб."; break;
	        default: $s = ""; break;
	    }
	    
	    return array($s, $c);
	}
	
	/**
	 * Берем сумму на последний момент на странице истории
	 * 
	 * @param string $date - Тут дата
	 * @return integer сумма
	 */
	function getLastSumHistory($date) {
	   global $DB;
	   return $DB->val("SELECT SUM(ammount) as s FROM account_operations WHERE op_date <= ?", $date);
	}
	
	/**
	 * Заблокировать деньги у пользователя
	 *
	 * @param integer $uid    ИД Пользователя
	 * @param boolean $block  Заблокировать или разблокировать пользователя (true - заблокировать, false - разблокировать)
	 */
	function setBlockMoney($uid, $block=true) {
	    global $DB;
		if(!$uid) return false;
		return $DB->query("UPDATE account SET is_block = ?b WHERE uid = ?", $block, $uid);
	}

    /**
     * Прикрепляет файлы
     * 
     * @param  array $files массив файлов
     * @param  string $dir папка назначения
     * @param  bool $formatted true - файлы являются экземплярами CFile, иначе false
     * @return mixed сообщение об ошибке или 0
     */
    function addAttach($files, $dir, $formatted=false) {
        global $DB;
		if(!$files)
            return 0;

        $attach = NULL;
        if(!$formatted) {
            foreach($files['name'] as $idx=>$value) {
                foreach($files as $key=>$a) {
                    $att[$key] = $files[$key][$idx];
                }
                if($att['size']) {
                    $attach[] = new CFile($att);
                }
            }
        }
        else
            $attach = $files;

        if(!$attach) return 0;

        $i=0;
        $sql = '';
        foreach($attach as $file) {
            if(!$file->size) continue;
            if(++$i > self::MAX_FILE_COUNT) continue;
            $ext = $file->getext();
            $file->orig_name = change_q_x($file->name);
            if(!in_array($ext, $GLOBALS['graf_array']) || $ext == 'swf') return "Неверный формат файла: {$file->orig_name}";
            $file->max_size = self::MAX_FILE_SIZE;
            if(!$file->MoveUploadedFile($dir) || !isNulArray($file->error))
                return $file->StrError();
            $sql .= "INSERT INTO account_attach (account_id, file_id, name, orig_name) VALUES ({$this->id}, {$file->id}, '{$file->name}', '{$file->orig_name}');";
        }
        if($sql && !$DB->query($sql))
           return 'Ошибка';

        return 0;
    }
    
    /**
     * делает запись о новом файле или удаляет запись
     * @param $attach список файлов полученый из attachedfiles::getFiles()
     */
    function addAttach2 ($attach) {
        global $DB;
		if (!$attach) {
            return 0;
        }

        $sql = '';
        foreach ($attach as $file) {
            if ($file['status'] == 1) { // токо что загруженные файлы
                $sql .= "INSERT INTO account_attach (account_id, file_id, name, orig_name) VALUES (?i, ?i, ?, ?);";
                $sql = $DB->parse($sql, $this->id, $file['id'], $file['name'], $file['orig_name']);
            } elseif ($file['status'] == 4) { // удаленные, ранее загруженные файлы
                $sql .= "UPDATE account_attach SET deleted = TRUE WHERE file_id = {$file['id']};";
            }
        }
        if ($sql && !$DB->query($sql)) {
           return 'Ошибка';
        }
       
        return 0;        
    }

    /**
     * Возвращает все прикрепленные файлы или определенный файл
     * 
     * @param  int $attach_id опционально. ID файла
     * @return array
     */
    function getAllAttach($attach_id = NULL) {
        global $DB;
		$where = "WHERE aa.account_id = {$this->id} AND aa.deleted = FALSE";
        if($attach_id) $where .= " AND aa.id IN ($attach_id)";
        $ret = NULL;
        $sql = "
          SELECT aa.*, f.fname as name, f.path, f.size
            FROM account_attach aa
          INNER JOIN
            file f
              ON f.id = aa.file_id
           {$where}
        ";

        if(($res = $DB->query($sql)) && pg_num_rows($res)) {
            while($row = pg_fetch_assoc($res))
                $ret[$row['id']] = $row;
        }
        return $ret;
    }
    
    
    
    
    /**
     * Есть ли сканы?
     * 
     * @global object $DB
     * @return type
     */
    function isExistAttach()
    {
        global $DB;
        return (bool)$DB->val("SELECT 1 FROM account_attach WHERE account_id = ?i AND deleted = FALSE", $this->id);
    }




    /**
     * Удаляет определенный файл
     *
     * @param  int $attach_id ID файла
     * @return bool true - успех, false - провал
     */
    function delAttach($attach_id) {
        global $DB;
        if(!$attach_id) return false;
        if(!($aa = $this->getAllAttach($attach_id)))
            return false;
        $DB->query("UPDATE account_attach SET deleted = TRUE WHERE id = ?", $attach_id);
        // в док.файлы не удаляем. храним для истории
        /*$cfile = new CFile();
        foreach($aa as $a) {
            $cfile->Delete(0, $a['path'], $a['name']);
        }*/
        return true;
    }


    /**
     * Возвращает статистику по вводу/выводу СБР
     *
     * @param string $from_date     фильтр: начальная дата
     * @param string $to_date       фильтр: конечная дата
     * @param array $op_codes       массив с кодами операций
     * @return array                массив или NULL
     */
    function GetStatSbrInOut($from_date = '2000-01-01', $to_date = 'now()', $op_codes = array(36, 38, 77, 79)) {
		global $DB;
        $sql = "SELECT SUM(trs_sum) as trsum, SUM(ammount) as sum, COUNT(trs_sum) as count, 
                    CASE WHEN op_code = 36 THEN 77 WHEN op_code = 38 THEN 79 ELSE op_code END as op_code,
                    payment_sys as ps
                FROM account_operations
                WHERE op_date >= ? AND op_date < ?::date+'1 day'::interval
                    AND op_code IN (?l)
                GROUP BY op_code, ps
        ";
                
        $ret = null;

        if(($res = $DB->query($sql, $from_date, $to_date, $op_codes)) && pg_num_rows($res)) {
            while($row = pg_fetch_assoc($res)) {
                $row['trsum'] = zin(round($row['trsum'], 2));
                $ret[$row['op_code']][$row['ps']] = $row;
            }
        }

        return $ret;

    }

    /**
     * Возвращает статистику по комиссиям СБР
     *
     * @param string $from_date     фильтр: начальная дата
     * @param string $to_date       фильтр: конечная дата
     * @return array                массив или NULL
     */
    function GetStatSbrCommission($from_date = '2000-01-01', $to_date = 'now()') {
        global $DB;
        $sql = "SELECT SUM(act_lcomm) as summ, COALESCE(ao.payment_sys, cost_sys) as ps
                FROM sbr_stages_users s
                    INNER JOIN sbr_stages st ON st.id = s.stage_id
                    INNER JOIN sbr ON sbr.id = st.sbr_id
                    LEFT JOIN sbr_stages_payouts po ON po.stage_id = st.id
                    LEFT JOIN account_operations ao ON ao.id = po.credit_id
                WHERE st.closed_time::date >= ?::date
                AND st.closed_time::date < ?::date+'1 day'::interval
                GROUP BY ps
        ";

        $ret = null;

        if(($res = $DB->query($sql, $from_date, $to_date)) && pg_num_rows($res)) {
            while($row = pg_fetch_assoc($res)) {
                $row['summ'] = zin(round($row['summ'], 2));
                $ret[$row['ps']] = $row;
            }
        }
        
        return $ret;
        
    }
    
    /**
     * Получаем инфу по определенной операции
     * 
     * @param integer $operation_id - ID операции
     * @return mixed
     */
    public static function getOperationInfo($operation_id){
        global $DB;
        $sql = "SELECT * FROM account_operations WHERE id = ?i";
        return $DB->row($sql,$operation_id);
    }


    /**
     * Проверка номера счета ЯД
     *
     * @param string $input     Строка с номером счета
     * @return boolean          Результат проверки
     */
    public static function isValidYd($input) {
        $N = $X = $Y = $Z = null;
        
        // Если строка пуста или содержит символы, отличные от цифр,
        // то разбор невозможен и строка недействительна.
        // Максимально возможная длина номера — 32
        if (strlen($input) == 0 || preg_match("/[\D]+/", $input) || strlen($input) > 32)
            return false;

        // только рубли
        if (substr($input, 0, 5) != '41001')
            return false;

        // Если первая цифра равна 0, то разбор невозможен и
        // строка недействительна, в противном случае N= первая цифра.
        if (intval(substr($input, 0, 1)) == 0)
            return false;

        $N = intval(substr($input, 0, 1));

        // Если длина строки меньше N+4, то разбор невозможен и строка недействительна.
        if (strlen($input) < $N+4)
            return false;

        // Если две последние цифры строки равны "00", то строка недействительна.
        if (substr($input, (strlen($input)-2), 2) == "00")
            return false;

        // X = N цифр, начиная со второй;
        $X = substr($input, 1, $N);
        // Z = две последних цифры
        $Z = substr($input, (strlen($input)-2), 2);
        // Y =  оставшиеся цифры
        $Y = substr($input, $N+1, (strlen($input)-2-($N+1)));

        //Если длина Y больше 20, то строка недействительна
        if (strlen($Y) > 20)
            return false;


        /**
         * AccountNumberRedundancy
         *
         * X записывается как последовательность 10 десятичных цифр:
         * X = X9  X8  … X0
         * Пример: для X = 67458
         *      X0  = 8	X1 =  5	X2  = 4	X3  = 7	X4  = 6	X5  = 0 X6  = 0	X7  = 0	X8  = 0	X9  = 0
         *
         * Y записывается как последовательность 20 десятичных цифр:
         * Y = Y19  Y18  … Y0
         * Пример: для Y = 3285076
         *      Y0  = 6	Y1 =  7	Y2  = 0	Y3  = 5	Y4  = 8	Y5  = 2	Y6  = 3
         *            Y7  = 0	Y8  = 0	Y9  = 0	Y10  = 0	Y11  = 0	Y12  = 0	Y13  = 0
         *            Y14  = 0	Y15  = 0	Y16  = 0	Y17  = 0	Y18  = 0	Y19  = 0
         *
         * Важно!
         * Нули слева в X и Y, которые являются незначащими
         * с точки зрения десятичной записи X и Y, пропускать НЕЛЬЗЯ.
         */
        
        $Xs = str_pad($X, 10, "0", STR_PAD_LEFT);
        $Ys = str_pad($Y, 20, "0", STR_PAD_LEFT);
        
        $Xs = strrev($Xs);
        $Ys = strrev($Ys);

        $res = 0;
        $a = pow(13,2)%99;

        $str = $Ys.$Xs;

        for ($i = 0; $i < strlen($str); $i++) {
            $t = substr($str, $i, 1);
            if ($t == '0') $t = 10;

            $res = ($res + (($t*$a) % 99)) % 99;
            $a = ($a*13) % 99;
        }
        $res += 1;

        $res = str_pad($res, 2, "0", STR_PAD_LEFT);

        if($Z != $res)
            return false;

        return true;
        
    }
    
    /**
     * Количество денег, потраченных на сервис
     * 
     * @global object $DB Класс для работы с БД 
     * @param integer $uid ИД пользователя
     * @return float;
     */
    public function getSumAmmountSpentService($uid) {
        global $DB;
        
        $sql = "SELECT SUM(ao.ammount) * -1 FROM account a JOIN account_operations ao ON ao.billing_id = a.id WHERE a.uid = ?i AND ao.ammount < 0";
        $res = $DB->val($sql, $uid);
        return round($res, 2);
    }
  
    /**
     * Проверка, был ли новый возврат денег 
     * @param $uid идентификатор пользователя     
     * @param &$lastId    идентификатор последней операции возврата средств за рассылку, о которой пользователя оповестили
     * @param &$currentId идентификатор последней операции возврата средств за рассылку, о которой пользователя не оповестили
     * @return true если существует возврат денег, о котором пользователя не оповестили  
     */
    public static function GetNewMoneyBack ($uid, &$lastId, &$currentId) {
        global $DB;
        //получаем последний номер записи возврата денег, о котором было уведомление
        $uid = (int)$uid;
        $query   = "SELECT last_deny_subscribe_op_id FROM users_counters WHERE user_id = $uid";
        $lastId  = $DB->val($query);
        //получаем последний номер записи возврата денег из лога операций
        $query = "SELECT id FROM account_operations 
                  WHERE billing_id = (SELECT id FROM account WHERE uid = $uid)
                        AND op_code = 46 
        ORDER BY op_date DESC LIMIT 1";
        $currentId = $DB->val($query);
        //если они не равны вернем истину
        if ($currentId != $lastId) {           
            return true;
        }
        //иначе вернем ложь
        return false;
    }    
    
    /**
     * Запоминаем последний возврат денег, о котором пользователю маякнули 
     * @param $uid идентификатор пользователя
     * @param $currentId идентификатор последней операции возврата средств за рассылку, о которой пользователя не оповестили
     */
    public static function SetNewMoneyBack($uid, $currentId) {
        global $DB;
        //получаем последний номер записи возврата денег, о котором было уведомление
        $uid = (int)$uid;
        $query   = "SELECT last_deny_subscribe_op_id FROM users_counters WHERE user_id = $uid";
        $lastId  = $DB->val($query);
        $numRows = pg_num_rows($DB->res);
        //если они не равны запишем новое значение
        if ($currentId != $lastId) {
            if ($numRows == 0) {                
                $DB->insert("users_counters", array("user_id"=>$uid, "last_deny_subscribe_op_id"=>$currentId));
            } else {
                $DB->update("users_counters", array("last_deny_subscribe_op_id"=>$currentId), " user_id = $uid ");
            }            
        }
    }
    
    /**
	 * Изменить комментарий к операции
	 *
     * @param string $comment Комментарий
	 * @param integer $bill_id id покупки
     * @param array $op_codes Коды операций
	 */
	public function updateComment ($comment, $bill_id, $op_codes) {
		$GLOBALS['DB']->query("UPDATE account_operations SET comments = ? WHERE id = ? AND op_code IN (?l)", $comment, $bill_id, $op_codes);
	    return 0;
	}
    
    /**
     * вычисляем в каких месяцах была активность начиная с указанного года
     * @global object $DB
     * @param type $year
     * @return type
     */
    public static function getStatYears($year = 2006)
    {
        global $DB;
        $aData = array();
        $ids = array();

        for ($i = $year; $i <= date('Y'); $i++) {
            for ($j = 1; $j <= 12; $j++) {
                $aData[$i][$j]['data'] = 0;
                $aData[$i][$j]['date_m'] = 0;
                $aData[$i][$j]['date_y'] = 0;
            }
        }
        
        for ($i = $year; $i <= date('Y'); $i++) {
            $date_from = $i . '-01-01';
            $date_to = ($i + 1) . '-01-01';
            $sql = "SELECT to_char(op_date,'MM') as _day FROM
                    account_operations WHERE op_date >= ? AND op_date < ? GROUP BY to_char(op_date,'MM') ORDER BY to_char(op_date,'MM')";
            
            
            //Текущий год кешируем на час, прошедшие - на неделю
            $time = $i == date('Y') ? 3600 : 604800;
            $aTemp = $DB->cache($time)->rows($sql, $date_from, $date_to);

            $ids[] = $i . '_0';
            for ($j = 0; $j < count($aTemp); $j++) {
                $iMonth = intval($aTemp[$j]['_day']);
                $aData[$i][$iMonth]['data'] = true;
                $aData[$i][$iMonth]['date_m'] = $aTemp[$j]['_day'];
                $aData[$i][$iMonth]['date_y'] = $i;
                $ids[] = $i . '_' . $aTemp[$j]['_day'];
            }
        }

        return array(
            'data' => $aData,
            'ids' => $ids
        );
    }
    
    /**
     * Проверяет принадлежность операции указанному пользователю
     * @global object $DB
     * @param type $billId
     * @param type $uid
     * @return type
     */
    public function checkOperationOwner($billId, $uid)
    {
        global $DB;
        $sql = "SELECT 1
            FROM account_operations ao
            INNER JOIN account a ON a.id = ao.billing_id
            WHERE ao.id = ?i AND a.uid = ?i;";
        return $DB->val($sql, $billId, $uid) == 1;
    }

}
?>
