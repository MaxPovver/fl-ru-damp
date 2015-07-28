<?
/**
 * Класс для хранения, обработки и генерации кодов uuid 
 *
 */
class codes
{
	/**
	 * ID Пользователя
	 *
	 * @var integer
	 */
	public $user_id;
	/**
	 * Сгенерированный код
	 *
	 * @var string
	 */
	public $code;
	
	/**
	 * Тип кода (1)
	 *
	 * @var integer
	 */
	public $type;
	
	/**
	 * Дата создания
	 *
	 * @var string
	 */
	public $cdate;
	
	
	public $pr_key = "code";
	
	/**
	 * Создать код
	 *
	 * @param stting  $error	  возвращает ошибку
	 * @param integer $return_id  Возвращать ли ИД созданной записи (0 - нет, 1 - да)
	 * @return string Сгенерированный код
	 */
	function Add( $error, $return_id = 0 ) {
	    if( !$this->cdate ) {
			$this->cdate = date("c");
		}
		
		if ( !$this->code ){
            mt_srand();
			$this->code = md5( $this->user_id.$this->cdate.uniqid(mt_rand(), true) );
		}
		
		$aData = array( 'user_id' => $this->user_id, 'type' => $this->type, 'code' => $this->code, 'cdate' => $this->cdate );
		
		$GLOBALS['DB']->insert( get_class($this), $aData );
		
		return $this->code;
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
	 * Удалить код
	 *
	 * @param integer $uid  ИД юзера
	 * @param integer $type Тип кода (в базе найдено только одно значение и оно принимает значение 1)
	 * @return string Сообщение об ошибке
	 */
    function DelByUT( $uid, $type ) {
        global $DB;
        $DB->query( 'DELETE FROM codes WHERE type = ? AND user_id = ?', $type, $uid );
		return $DB->error;
    }
}
?>