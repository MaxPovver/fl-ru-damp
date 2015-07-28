<?
/**
 * Класс для работы с курсами обмена между платежными системами
 * 
 */
class exrates {
	
	/**
	 * ID обмена.
	 *
	 * @var integer
	 */
	public $id;
	/**
	 * Курс обмена.
	 *
	 * @var float
	 */
	public $val;
	/**
	 * Primary key таблицы exrates.
	 *
	 * @var string
	 */
	public $pr_key = "id";	


	/**
	 * ID для FM
	 */
	const FM   = 1; 
	/**
	 * ID для WMZ
	 */
	const WMZ  = 2; 
	/**
	 * ID для WMR
	 */
	const WMR  = 3;
	/**
	 * ID для YandexMoney
	 */
	const YM   = 4;
	/**
	 * ID для банковских операций (рубли)
	 */
	const BANK = 5;
    /**
     * Веб-кошелек
     */
    const WEBM = 6;
    /**
     * Банковские карты
     */
    const CARD = 7;
    
    /**
     * Киви
     */
    const QIWIPURSE = 8;
    
     /**
     * Терминалы
     */
    const OSMP = 9;

     /**
     * OKPay
     */
    const OKPAY = 14;

     /**
     * QiwiMobile
     */
    const MOBILE = 15;
	
	
	/**
	 * Обновить все курсы обмена
	 * @param   array   $arr   массив с новыми курсами, в котором:
	 *                         индексы - id обмена, значение - курс обмена
	 * @return  integer        1 - в случае успеха, иначе - 0
	 */
	function BatchUpdate( $arr ) {
		foreach ($arr as $ikey => $val){
			$vals[] = "INSERT INTO exrates (id, val) VALUES ('".$ikey."','".$val."')";
		}
		
		global $DB;
		$sql = "DELETE FROM exrates; ".implode('; ', $vals).";";
		
		if( $DB->squery($sql) ) return 1;
        
		return 0;
	}
	
	/**
	 * Возвращает текущие обменные курсы
	 * @return  array    массив с текущими курсами, в котором:
	 *                   индексы - id обмена, значение - курс обмена
	 */
	function GetAll() {
	    global $DB;
		$res = $DB->squery( "SELECT * FROM exrates" );
		$ret = pg_fetch_all($res);
		if ($ret)
			foreach($ret as $val){
				$out[$val['id']] = $val['val'];
			}
		return $out;
	}
	
	/**
	 * Взять данные определенного поля по ключу
	 * 
	 * @param  integer $uid ИД поля
	 * @param  string $error Возвращает сообщение об ошибке
	 * @param  string $fieldname Поле выборки
	 * @return string данные поля
	 */
	function GetField( $uid, &$error, $fieldname ) {
		$current = get_class($this);
		return $GLOBALS['DB']->val("SELECT {$fieldname} FROM {$current} WHERE {$this->pr_key} = ?", $uid);
	}
    
    static function getNameExratesForHistory($exr) {
        switch($exr) {
            case self::WMR:
                return 'WebMoney';
            case self::YM:
                return 'Яндекс.Деньгами';
            case self::BANK:
                return 'через банковский счет';  
            case self::CARD:
                return 'пластиковой картой';
            case self::WEBM:
                return 'Веб-кошельком';
            case self::QIWIPURSE:
                return "QIWI-кошельком";
            case self::OSMP:
                return 'через терминал';
        }
    }
    
    function getNameExrates($exr) {
        switch($exr) {
            case self::FM:
                return 'счет на сайте';
            case self::WMR:
                return 'кошелек WMR';
            case self::YM:
                return 'кошелек Яндекс.Деньги';
            case self::BANK:
                return 'банковский счет';   
        }
    }
}

?>