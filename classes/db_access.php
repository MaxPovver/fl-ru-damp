<?
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с БД
 *
 */
class db_access 
{ 
	
	/**
	* 
	* Обновление данных в таблице
	* 
	* @desc Перед вызовом проверить переменные класса!
	* Должны быть определены только те, которые надо изменять!
	* 
	* @param integer $fid      Идентификатор ключевого поля (числовое значение)
	* @param string  $eddition Дополнительные условия выборки для редактирования
	* @return string $error Сообщение об ошибке
	
	*/
	function Update($fid, $eddition = ""){
		$current = get_class($this);
		$class_vars = get_class_vars(get_class($this));
		$fields = array();
		foreach ($class_vars as $name => $value) {
    		if (isset($this->$name) && $name != "pr_key"){
				if ($this->$name == 'null') $fields[] = $name."= ".$this->$name."";
				else $fields[] = $name."= '".str_replace("'","&#039;",$this->$name)."'";
    		}
		}
		$fld = implode(", ",$fields);
		if ($fld){
			$sql .= "UPDATE $current SET $fld WHERE (".$this->pr_key." = '$fid' ".$eddition.")";
			if (!$GLOBALS['DB']->squery($sql)) {
				$error = 'DB error';
			}
		}
		return ($error);
	}
	
	/**
	 * Взять данные определенного поля по ключу
	 *
	 * @param integer $uid       ИД поля
	 * @param string  $error     Возвращает сообщение об ошибке
	 * @param string  $fieldname Поле выборки
	 * @return string данные поля
	 */
	function GetField($uid, &$error, $fieldname){
		$current = get_class($this);
		return $GLOBALS['DB']->val("SELECT {$fieldname} FROM {$current} WHERE {$this->pr_key} = ?", $uid);
	}
	
	/**
	 * Орабатывает ряд результата запроса и возвращает неассоциативный массив, 
	 * который записывается в переменны класса как $this->[ключ из БД] = [Значение БД]
	 *
	 * @param integer $id    Идентификатор ключевого поля
	 * @param string  $addit Условие выборки
	 * @param string  $order Сортировка
	 * @return integer всегда возвращает 1
	 */
	function GetRow($id = "", $addit = "", $order = ""){
		$current = get_class($this);
		if ($id) $addit = $this->pr_key."='$id'" . $addit;
		if ($order) $order = " ORDER BY ".$order;
		$out = $GLOBALS['DB']->row("SELECT * FROM $current WHERE ($addit)".$order);
		foreach ($out as $key => $value){
			$this->$key = $value;
		}
		return 1;
	}
	
	/**
	 * Взять все поля определенной таблицы (по условию или без, с сортировкой или без)
	 *
	 * @param string $orderby Сортировка, по умолчанию не стоит
	 * @param string $filter  Условие выборки, по умолчанию не стоит
	 * @return array Данные выборки
	 */
	function GetAll($orderby = "", $filter=""){
		$current = get_class($this);
		$sql = "SELECT * FROM $current";
		if ($filter) $sql .= " WHERE ( $filter )";
		if ($orderby) $sql .= " ORDER BY $orderby";
		else $sql .= " ORDER BY ".$this->pr_key;
		return $GLOBALS['DB']->rows($sql);
	}
	
	/**
	 * Добавить новую информацию в таблицу
	 *
	 * @param mixed   $error     Вовзращает сообщение об ошибоке
	 * @param integer $return_id Возвращать или нет ИД созданной записи (0 - не возвращать, 1 - возвращать)
	 * @return integer -1 - если возникла ошибка, 0 - если стоит параметр $return_id = 0 и все прошло успешно, Ид созданной записи
	 */
	function Add(&$error, $return_id = 0){
		$current = get_class($this);
		$class_vars = get_class_vars(get_class($this));
		$fields = array();
		$vals = array();
		foreach ($class_vars as $name => $value) {
    		if (isset($this->$name) && $name != "pr_key"){
    			$fields[] = $name;
				$vals[] = $this->$name;
    		}
		}
		$fld = implode(", ",$fields);
		$vls = "'".implode("', '",$vals)."'";
		$sql = "INSERT INTO $current($fld) VALUES ($vls)";
		if ($return_id) $sql .= " RETURNING $this->pr_key";
		$res = $GLOBALS['DB']->query($sql);
		if ($GLOBALS['DB']->error)
			return -1;
		else{
			if ($return_id) {
				list($out) = pg_fetch_row($res);
				return $out;
			}
		 return 0;
		}
	}
	
	/**
	 * Удалить запись из таблицы
	 *
	 * @param integer $id     ИД ключевого поля
	 * @param string  $addit  Условие удаления (по умолчанию его нет, но оно обязательно)
	 * @return string Сообщение об ошибке
	 */
	function Del($id, $addit = ""){
		$current = get_class($this);
		if ($id) $addit = $this->pr_key."='$id'" . $addit;
		if ($GLOBALS['DB']->query("DELETE FROM $current WHERE $addit")) {
			return '';
		} else {
			return 'DB Error';
		}
	}
	
	/**
	 * Инициализировать члены класса параметрами из массива
	 * Массив должен содержать переменные с такими же именами, как и члены класса
	 *
	 * @param array $arr  массив переменных
	 * @return integer Всегда возвращает 0
	 */
	function BindRequest($arr, $force = false){
		$class_vars = get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value) {
				if ($force || isset($arr[$name])){
	   				$this->$name = ($force && !isset($arr[$name])) ? '' : $arr[$name];
				}
		}
		return 0;
	}
	
	/**
	 * Проверка проинициализированы ли необходимые поля
	 *
	 * @param array $reqvs 	массив с именами необходимых полей
	 * @return array 		сообщения об ошибках
	 */
	function check_required($reqvs){
		foreach($reqvs as $varname){
			if (!isset($this->$varname) || !$this->$varname)
				$error[$varname] = "Поле заполнено некорректно";
		}
		return $error;
	}
	
}
?>