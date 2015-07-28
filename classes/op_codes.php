<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes_price.php");

/**
 * Класс для работы с Типами денежных операция
 *
 */
class op_codes
{
	/**
	 * Ид операции
	 *
	 * @var integer
	 */
	public $id;
	
	/**
	 * Название операции
	 *
	 * @var integer
	 */
	public $op_name;
	
	/**
	 * Стоимость операции
	 *
	 * @var integer
	 */
	public $sum;
	
	/**
	 * Ключевое поле таблицы
	 *
	 * @var string
	 */
	public $pr_key="id";
	
    
    
    /**
     * Кеш на данных опкодов на период работы скрипта
     * исключает многократное обращение к БД в период одной сесии работы скрипта
     * 
     * @var type 
     */
    static protected $_cache_data = array();


    const OP_CODES_MEMCACHE_TAG     = 'getAllOpCodes';
    const OP_CODES_MEMCACHE_LIFE    = 86400; //на сутки


    /**
	 * Возвращает типы денежных операций
	 * 
	 * @param  string|array $codes один или несколько кодов денежных операций
	 * @return array индексированный кодами операций
	 */
	function getCodes($codes) {
	    if(is_array($codes)) $codes = implode(',', $codes);
        if(!$codes) $codes = '0';
        $ret = array();
        if ( $rows = $GLOBALS['DB']->rows("SELECT * FROM op_codes WHERE id IN ({$codes})") ) {
            foreach($rows as $row)
                $ret[$row['id']] = $row;
        }
	    return $ret;
	}

	/**
	 * Взять данные определенного поля по ключу
	 *
	 * @param  integer $uid ИД поля
	 * @param  string $error Возвращает сообщение об ошибке
	 * @param  string $fieldname Поле выборки
	 * @return string данные поля
	 */
	function GetField($uid, &$error, $fieldname){
		$current = get_class($this);
		return $GLOBALS['DB']->val("SELECT {$fieldname} FROM {$current} WHERE {$this->pr_key} = ?", $uid);
	}
	
	/**
	 * Выбирает запись базы и устанавливает переменные класса.
	 * 
	 * @param  integer $id идентификатор ключевого поля
	 * @return bool true - успех, false - провал
	 */
	function GetRow( $id = '' ) {
	    global $DB;
	    
	    $bRet = true;
	    $aRow = $DB->row( 'SELECT * FROM '. get_class($this) .' WHERE '. $this->pr_key .' = ?', $id );
	    
	    if ( is_array($aRow) && count($aRow) ) {
    	    foreach ( $aRow as $key => $val ) {
    			$this->$key = $val;
    		}
	    }
	    else {
	        $bRet = false;
	    }
	    
	    return $bRet;
	}
	
    
    
    /**
     * Чистим все опкоды в мемкеше
     * 
     * @return type
     */
    public static function clearCache()
    {
        $memBuff = new memBuff;
        return $memBuff->flushGroup(self::OP_CODES_MEMCACHE_TAG);
    }
    
    
    /**
     * Загрузить весь список опкодов
     * 
     * @return boolean
     */
    static function getAllOpCodes($refresh = false)
    {
        if (!empty(self::$_cache_data) && !$refresh) {
            return self::$_cache_data;
        }
        
        $error = null;
        
        $memBuff = new memBuff();
        $data = $memBuff->getSql($error, " 
                SELECT * FROM ". get_class($this) ."
            ", 
            self::OP_CODES_MEMCACHE_LIFE, 
            true, 
            self::OP_CODES_MEMCACHE_TAG);
        
        if($data && !$error) {
            
            foreach ($data as $el) {
                self::$_cache_data[$el['id']] = $el;
            }
            
            return self::$_cache_data;
        }
        
        return false;
    }
   


    /**
     * Получить данные указанного опкода
     * 
     * @param type $opCode
     * @return boolean
     */
    static function getDataByOpCode()
    {
        $args = func_get_args();
        $opCode = $args[0];
        unset($args[0]);
        $param = @$args;
        
        //Проверяем есть ли диф.цена для услуги
        $price = op_codes_price::getOpCodePrice($opCode, $param);

        if ($price) {
            return array('sum' => $price);
        }
        
        //Если нет то отрабатываем обычную цену из op_codes
        self::getAllOpCodes();
        
        if (isset(self::$_cache_data[$opCode])) {
           return self::$_cache_data[$opCode]; 
        }
        
        return false;
    }
    
    
    
    /**
     * Получить базовую цену указанного опкода
     * 
     * @param type $opCode
     * @return boolean
     */
    public static function getPriceByOpCode($opCode)
    {
        $opCodeData = self::getDataByOpCode($opCode);
        return $opCodeData['sum'];
    }
    
    
    
    
    /**
     * Получить цену по опкоду без использования диф.цен
     * 
     * @param type $opCode
     * @return boolean
     */
    public static function getPriceByOpCodeWithoutDiscount($opCode)
    {
        self::getAllOpCodes();
        
        if (isset(self::$_cache_data[$opCode])) {
           return self::$_cache_data[$opCode]['sum']; 
        }
        
        return false;
    }

        
    
    
    /**
     * Возвращает метку услуги для аналитики
     * @param type $opCode
     * @return type
     */
    public static function getLabel($opCode)
    {
        self::getAllOpCodes();
        
        if (isset(self::$_cache_data[$opCode])) {
            $opCodeData = self::$_cache_data[$opCode];
            return isset($opCodeData['ga_label']) ? $opCodeData['ga_label'] : '';
        }
    }
        
    
       
}