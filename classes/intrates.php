<?
/**
 * Класс для работы с процентами за перевод из одной платежной системы в другую в СБР.
 * 
 */
class intrates {
	/**
	 * ID обмена.
	 *
	 * @var integer
	 */
	public $id;
	/**
	 * Процент при обмена.
	 *
	 * @var float
	 */
	public $val;
	/**
	 * Primary key таблицы intrates.
	 *
	 * @var string
	 */
	public $pr_key = "id";

	
	/**
	 * Обновить все проценты
	 * @param   array   $arr    массив с новыми данными, в котором:
	 *                          индекс - id обмена; значение - процент
	 * @return  integer         1 - в случае успеха, иначе 0
	 */
	function BatchUpdate( $arr ) {
		foreach ($arr as $ikey => $val){
			$vals[] = "INSERT INTO intrates (id, val) VALUES ('".$ikey."','".$val."')";
		}
		
		global $DB;
		$sql = "DELETE FROM intrates; ".implode('; ', $vals).";";
		$DB->squery( $sql );
		return 0;
	}
	
	/**
	 * Возвращает текущие процента по переводам
	 * @return  array         массив с текущими процентами за перевод, в котором:
	 *                        индекс - id обмена; значение - процент
	 */
	function GetAll() {
	    global $DB;
		$res = $DB->squery( "SELECT * FROM intrates" );
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
		return $GLOBALS['DB']->val( "SELECT {$fieldname} FROM {$current} WHERE {$this->pr_key} = ?", $uid );
	}
}

?>