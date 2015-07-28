<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exchrates.php");

/**
 * Класс для работы с оплатой через банк
 *
 * @todo Код в читабельный вид(по стандартам) привести
 */
class bank_payments
{
  /**
   * код сбербанка
   *
   */
  const BC_SB = 1;
  const BC_SB_SBR = 2;

  /**
   * Ид оплаты
   *
   * @var integer
   */
  public $id;
  
  /**
   * Идентификатор пользователя
   *
   * @var integer
   */
  public $user_id;
  
  /**
   * Код банка
   *
   * @var integer
   */
  public $bank_code;
  
  /**
   * Номер биллинга
   *
   * @var integer
   */
  public $bill_num;
  
  /**
   * ФИО платильщика
   *
   * @var string
   */
  public $fio;
  
  /**
   * Адресс платильщика
   *
   * @var string
   */
  public $address;
  
  /**
   * Сумма оплаты
   *
   * @var integer
   */
  public $sum;
  
  /**
   * Сумма в ФМ
   *
   * @var integer
   */
  public $fm_sum;
  
  /**
   * Идентификатор биллинга
   *
   * @var integer
   */
  public $billing_id;
  
  /**
   * Код операции
   *
   * @var integer
   */
  public $op_code;
  
  /**
   * Время платежа
   *
   * @var string
   */
  public $invoiced_time;
  
  /**
   * Время поступления на счет
   *
   * @var string
   */
  public $accepted_time;
  
  /**
   * Получить подарок при пополнении счета или нет
   * @var boolean 
   */
  public $is_gift;
  
  public $sbr_id;

  /**
   * Ключевое поле таблицы
   *
   * @var string
   */
  protected $pr_key = 'id';

    /**
     * Валидация данных
     * 
     * @return string сообщение об ошибке
     */
	function CheckInput() {
    	$this->address = substr(change_q($this->address), 0, 128);
    	$this->fio = substr(change_q($this->fio), 0, 64);
	    $this->bank_code = $this->bank_code ? $this->bank_code : self::BC_SB;
  	  $this->sum     = (float)$this->sum;
      setlocale(LC_ALL, 'en_US.UTF-8');
  	  $this->fm_sum  = $bp->sum / EXCH_TR;
  //	  if(isset($this->id))
//      	  $this->id = (int)$this->id;
  	  if(!$this->fio) $alert['fio'] = 'Поле заполненно некорректно.';
  	  if(!$this->address) $alert['address'] = 'Поле заполненно некорректно.';
  	  if(!$this->sum || $this->sum < 0.01) $alert['sum'] = 'Поле заполненно некорректно.';
  	  return $alert;
	}
  
  
 	/**
	 * Возвращает информацию о банке по его коду. Типа таблицы (может быть лучше в БД ее сделать).
	 * Сюда можно добавлять разные банки (только константу-код банка соответсвующую сделать)
	 *
	 * @param integer $bank_code код банка (коды задаются в этом классе)
	 * @return array информация по банку.
	 */
  function GetBank($bank_code)
  {
    $bank;
    switch($bank_code) {

      case self::BC_SB:
         $bank = array('name'=>'Сбербанк', // во всяких титлах используется.
                       'prefix'=>'СБ',     // используется при формировании номера счета.
                       'payment_sys'=>5);  // для таблицы account_operations
    }

    return $bank;
  }

	/**
	 * Возвражает номер биллинга (кода оплаты для банка имеет вид XXX-XXX-XXXX)
	 *
	 * @param integer $bank_code  Код банка
	 * @param integer $user_id    Ид пользователя
	 * @param integer $account_id Ид аккаунта
	 * @return string
	 */
  function GenBillNum($bank_code, $user_id, $account_id=NULL)
  {
    if(!($bank = self::GetBank($bank_code)))
        return NULL;
        
    $lst = self::GetLastReqv($bank_code, $user_id, 12);
    $ord = (int)preg_replace('/^'.$bank['prefix'].'-\d+-(\d+)/','$1',$lst['bill_num']) + 1;
    if(!$account_id) {
      global $DB;
      
      $account_id = $DB->val( "SELECT id FROM account WHERE uid = ?", $user_id );
    }

    if($account_id && $ord)
      return $bank['prefix'].'-'.$account_id.'-'.$ord;

    return NULL;
  }
  
  /**
   * Получить номер выписанного счета
   * 
   * @param  int $id ID операции с пользовательскими счетами
   * @return string
   */
  public static function GetBillNum($id){
        $sql = "SELECT bill_num FROM bank_payments WHERE billing_id = ?i LIMIT 1";
        global $DB;
        return $DB->val($sql,$id);
  }

  /**
   * Взять последнюю регистрацию оплаты через банк
   *
   * @param integer $bank_code Код банка
   * @param integer $user_id   Ид юзера
   * @return array
   */
  function GetLastReqv($bank_code, $user_id, $op_code = NULL)
  {
    global $DB;
    
    $where = 'WHERE user_id = ? AND bank_code = ?';
    if($op_code)
        $where .= ' AND op_code = ?';
    $sql = "SELECT * FROM bank_payments {$where} ORDER BY invoiced_time DESC LIMIT 1";
    $res = $DB->row( $sql, $user_id, $bank_code, $op_code );

    return count($res) ? $res : null;
  }


 	/**
	 * Возвращает все счета, выписанные за данный период
	 *
	 * @param string $fdate			с какого числа получить счета
	 * @param string $tdate			по какое число
	 * @param string $search        Поисковое слово
	 * @param array  $sort          Тип сортировки [login=> DESC, fio=>ASC, ...]
	 * @return array				инфа по счетам
	 */
	function GetOrders($fdate, $tdate, $search = NULL, $sort){
	  $tdate = preg_replace("#\-0+#", "-0", $tdate);
	  $fdate = preg_replace("#\-0+#", "-0", $fdate);
	  $sort_fld = array_keys($sort);
	  $sort_fld = $sort_fld[0];
 	  $dir = $sort[$sort_fld];
	  switch($sort_fld) {
	    case 'login': $orderby = "lower(u.login) {$dir}, bp.id"; break;
	    case 'fio': $orderby = "lower(bp.fio) {$dir}, bp.id"; break;
	    case 'sum': $orderby = "bp.sum {$dir}, bp.id"; break;
	    case 'status': $orderby = "COALESCE(bp.accepted_time, 'epoch') {$dir}, bp.id"; break;
 	    case 'date': $orderby = "bp.id {$dir}"; break;
 	    default: $orderby = "bp.id"; break;
	  }
	  
	    global $DB;
		$sql = 
		"SELECT bp.*, u.login, u.photo, u.uname, u.usurname, u.role
		   FROM bank_payments bp
		 INNER JOIN
		   users u
		     ON u.uid = bp.user_id
		  WHERE bp.invoiced_time >= '$fdate' AND bp.invoiced_time < '$tdate'::date + 1".
		  ($search ? " AND (bp.fio ilike '%{$search}%'
		                    OR bp.bill_num ilike '%{$search}%'
		                    OR u.login ilike '%{$search}%') "
		           : ''
		  )."
		  ORDER BY {$orderby}";
		  
        $res = $DB->rows( $sql );
        
        return count($res) ? $res : null;
	}
    
    /**
	 * Орабатывает ряд результата запроса и возвращает неассоциативный массив, 
	 * который записывается в переменны класса как $this->[ключ из БД] = [Значение БД]
	 *
	 * @param  integer $id Идентификатор ключевого поля
	 * @param  string  $addit Условие выборки
	 * @param  string  $order Сортировка
	 * @return integer всегда возвращает 1
	 */
	function GetRow( $id = "", $addit = "", $order = "" ) {
		$current = get_class($this);
  $id = intval($id);
		if ( $id ) $addit = $this->pr_key."='$id'" . $addit;
		if ( $order ) $order = " ORDER BY ".$order;
		$out = $GLOBALS['DB']->row("SELECT * FROM $current WHERE ($addit)".$order);
		foreach ( $out as $key => $value ) {
			$this->$key = $value;
		}
		return 1;
	}
	
	/**
	 * Инициализировать члены класса параметрами из массива
	 * Массив должен содержать переменные с такими же именами, как и члены класса
	 *
	 * @param  array $arr массив переменных
	 * @return integer Всегда возвращает 0
	 */
	function BindRequest( $arr, $force = false ) {
		$class_vars = get_class_vars(get_class($this));
		foreach ( $class_vars as $name => $value ) {
			if ( $force || isset($arr[$name]) ) {
   				$this->$name = ($force && !isset($arr[$name])) ? '' : $arr[$name];
			}
		}
		return 0;
	}
	
	/**
	 * Удалить запись из таблицы
	 *
	 * @param  integer $id ИД ключевого поля
	 * @param  string  $addit Условие удаления (по умолчанию его нет, но оно обязательно)
	 * @return string Сообщение об ошибке
	 */
	function Del( $id, $addit = "" ) {
		$current = get_class($this);
		if ( $id ) $addit = $this->pr_key."='$id'" . $addit;
		if ( $GLOBALS['DB']->query("DELETE FROM $current WHERE $addit") ) {
			return '';
		} else {
			return 'DB Error';
		}
	}
	
	/**
	 * Добавить запись из переменныех класса.
	 * 
	 * @param string $error возвращает сообщение об ошибке если есть.
	 */
	function Add( &$sError, $bReturnId = false ) {
	    global $DB;
	    
		$aData = $this->_getDataArray();
        
		if ( $aData ) {
		    $sReturn = ( $bReturnId ) ? $this->pr_key : '';
		    $mRes    = $DB->insert(get_class($this), $aData, $sReturn );
		    
            if ( $DB->error ) {
                $sError = $DB->error;
                return -1;
            }
            elseif ( $bReturnId ) {
                return $mRes;
            }
            else {
                return 0;
            }
		}
		
		return -1;
	}
	
	/**
	 * Обновить запись из переменныех класса.
	 *
	 * @param  integer $id идентификатор ключевого поля
	 * @param  string $add дополнительные условия
	 * @return возвращает сообщение об ошибке если есть
	 */
	function Update( $id = '', $add = '' ) {
	    global $DB;
	    
	    $sError = '';
	    $aData  = $this->_getDataArray();
	    
	    if ( $aData ) {
	        if ( !$DB->update(get_class($this), $aData, $this->pr_key.' = ?' . $add, $id) ) {
	            $sError = $DB->error;
	        }
	    }
	    
	    return $sError;
	}
	
	/**
	 * Вспомогательная функция. собирает переменные класса в массив для Add и Update.
	 * 
	 * @return array
	 */
	function _getDataArray() {
	    $aData = array();
	    $vars  = get_class_vars( get_class($this) );
	    
	    foreach ( $vars as $name => $value) {
	        if ( isset($this->$name) && $name != "pr_key" ) {
	            if ( strtolower($this->$name) == 'null' ) {
	                $sVal = null;
	            }
	            elseif ( strtolower($this->$name) == 'false' ) {
	                $sVal = false;
	            }
	            elseif ( strtolower($this->$name) == 'true' ) {
	                $sVal = true;
	            }
	            else {
	                $sVal = $this->$name;
	            }
                $aData[$name] = $sVal;
	        }
        }
        
        return $aData;
	}
}
?>