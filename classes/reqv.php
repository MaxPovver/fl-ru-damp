<?
/**
 * Класс для работы с оплатой по безналичному расчету (через банк)
 *
 */
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/country.php' );

class reqv {
	
	/**
	 * ИД данных
	 *
	 * @var integer
	 */
	var $id;
	
	/**
	 * ИД Пользователя
	 *
	 * @var integer
	 */
	var $user_id;
	
	/**
	 * Название организации
	 *
	 * @var string
	 */
  	var $org_name;
  	
  	/**
  	 * Контактный телефон
  	 *
  	 * @var string
  	 */
  	var $phone;
  	
  	/**
  	 * Факс
  	 *
  	 * @var string
  	 */
  	var $fax;
  	
  	/**
  	 * Емейл адресс
  	 *
  	 * @var string
  	 */
  	var $email;
  	
  	/**
  	 * Страна. ID из country
  	 *
  	 * @var string
  	 */
  	var $country_id;
  	
  	/**
  	 * Город. ID из city
  	 *
  	 * @var string
  	 */
  	var $city_id;
  	
  	/**
  	 * Страна. Словами
  	 *
  	 * @var string
  	 */
  	var $country;
  	
  	/**
  	 * Город. Словами
  	 *
  	 * @var string
  	 */
  	var $city;
  	
  	/**
  	 * Почтовый Индекс
  	 *
  	 * @var string
  	 */
  	var $index;
  	
  	/**
  	 * Адресс
  	 *
  	 * @var string
  	 */
  	var $address;
  	
  	/**
  	 * Адресс доставки
  	 *
  	 * @var string
  	 */
  	var $address_grz;
  	
  	/**
  	 * ИНН
  	 *
  	 * @var integer
  	 */
  	var $inn;
  	
  	/**
  	 * КПП
  	 *
  	 * @var integer
  	 */
 	var $kpp;
 	
  	/**
  	 * ОКПО
  	 *
  	 * @var integer
  	 */
 	var $okpo;
 	
 	/**
 	 * Полное имя платильщика
 	 *
 	 * @var string
 	 */
  	var $full_name;
  	
  	/**
  	 * Фамилия имя отчество
  	 *
  	 * @var string
  	 */
  	var $fio;
  	
  	/**
  	 * Юридический адресс
  	 *
  	 * @var string
  	 */
  	var $address_jry;
  	
  	/**
  	 * название банка
  	 *
  	 * @var string
  	 */
  	var $bank_name;
  	
  	/**
  	 * Город банка
  	 *
  	 * @var string
  	 */
  	var $bank_city;
  	
  	/**
  	 * Расчетный счет
  	 *
  	 * @var string
  	 */
	var $acc;
	
  	/**
  	 * Расчетный счет

  	 *
  	 * @var string
  	 */
	var $bank_rs;

	/**
	 * Корреспонденсткий счет
	 *
	 * @var string
	 */
  	var $bank_ks;
  	
	/**
	 * БИК
	 *
	 * @var string
	 */
  	var $bank_bik;
  	
	/**
	 * Ид. сделки без риска (новой).
	 *
	 * @var integer
	 */
  	var $sbr_id;
  	
  	/**
  	 * Название первичного ключа таблицы
  	 *
  	 * @var string
  	 */
	var $pr_key = 'id';
        
        /**
         * Получить подарок при пополнении счета или нет
         * @var boolean 
         */
        public $is_gift;
	
	/**
	 * Проверяем введенные данные записываем их в класс и приводим их к нормальной форме
	 *
	 * @return string Сообщение об ошибке
	 */
	function CheckInput($sbr = false) {
		$this->org_name    = $this->org_name ? change_q(substr($this->org_name, 0, 128)) : '';
		$this->phone       = $this->phone ? substr(change_q($this->phone), 0, 24) : '';
		$this->fax         = $this->fax? substr(change_q($this->fax), 0, 24) : '';
		$this->email       = $this->email ? substr(change_q($this->email), 0, 64) : '';
		$this->country     = $this->country ? substr(change_q($this->country), 0, 64) : '';
		$this->country_id  = intval($this->country_id);
		$this->city        = $this->city ? substr(change_q($this->city), 0, 64) : '';
		$this->city_id     = intval($this->city_id);
		$this->index       = $this->index ? substr(change_q($this->index), 0, 7) : '';
		$this->address     = $this->address ? substr(change_q($this->address), 0, 128) : '';
		$this->address_grz = $this->address_grz ? substr(change_q($this->address_grz), 0, 128) : '';
		$this->inn         = $this->inn ? substr(change_q($this->inn), 0, 32) : '';
		$this->kpp         = $this->kpp ? substr(change_q($this->kpp), 0, 32) : '';
		$this->okpo        = $this->okpo ? substr(change_q($this->okpo), 0, 10) : '';
		$this->full_name   = $this->full_name ? change_q(substr($this->full_name, 0, 128)) : '';
		$this->fio         = $this->fio ? substr(change_q($this->fio), 0, 64) : '';
		$this->address_jry = $this->address_jry ? substr(change_q($this->address_jry), 0, 128) : '';
		$this->bank_name   = $this->bank_name ? substr(change_q($this->bank_name), 0, 64) : '';
		$this->bank_city   = $this->bank_city ? substr(change_q($this->bank_city), 0, 32) : '';
		$this->bank_ks     = $this->bank_ks ? substr(change_q($this->bank_ks), 0, 64) : '';
		$this->bank_rs     = $this->bank_rs ? substr(change_q($this->bank_rs), 0, 64) : '';
		$country_id        = country::getCountryId( $this->country );
		$aRequired         = array("org_name","phone","email","country_id","city_id","index","address","inn","full_name","address_jry");
		
		// убираем не обязательные поля в зависимости от ситуации
		if ( $this->country_id != 1 ) {
			unset( $aRequired[7] ); // inn
		}
		
		if ( $sbr ) {
		    unset( $aRequired[0] ); // org_name
		}
		
		$error = $this->check_required( $aRequired );
		
		if ( isset($error['country']) ) $error['country'] = 'Пожалуйста, выберите страну';
		if ( isset($error['city']) )    $error['city']    = 'Пожалуйста, выберите город';
		
		if (!is_email($this->email)) { $error['email'] = "Поле заполнено некорректно";}
		if ($this->kpp && !preg_match( "/^\d{9}$/", $this->kpp )) { $error['kpp'] = "Поле заполнено некорректно";}
		if ( $country_id == 1 ) {
            if (!preg_match("/^\d{10,12}$/", $this->inn) || strlen($this->inn)==11) { $error['inn'] = "Поле заполнено некорректно";}
		}
		if (!preg_match( "/^([0-9]+)$/", $this->index )) { $error['index'] = "Поле заполнено некорректно";}
		if($this->okpo && !preg_match('/^(?:\d{8}|\d{10})$/', $this->okpo)) { $error['okpo'] = "Поле заполнено некорректно";}
		if(!preg_match("/^[A-Za-z\d\-\(\)+\s]+$/", $this->phone)) { $error['phone'] = "Поле заполнено некорректно"; }
		return $error;
	}
	
	/**
	 * Взять данные по ИД пользователя
	 *
	 * @param integer $uid Ид пользователя
	 * @return arrya|integer Данные по выборке, либо 0
	 */
	function GetByUid($uid){
	    $current = get_class($this);
		$sql = "SELECT * FROM $current WHERE user_id = ? ORDER BY {$this->pr_key}";
	    
		if ( $ret = $GLOBALS['DB']->rows($sql, $uid) ) {
			if ( sizeof($ret) > 0 ) {
				return $ret;
			}
		}
		return 0;
	}
	
	/**
	 * Добавить запись из переменныех класса.
	 * 
	 * @param  string $error возвращает сообщение об ошибке если есть.
	 * @param  bool $bReturnId возвращать ли идентификатор записи.
	 * @return integer -1 - если возникла ошибка, 0 - если стоит параметр $return_id = 0 и все прошло успешно, Ид созданной записи
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
	 * @return возвращает сообщение об ошибке если есть.
	 */
	function Update( $id = '' ) {
	    global $DB;
	    
	    $sError = '';
	    $aData  = $this->_getDataArray();
	    
	    if ( $aData ) {
	        if ( !$DB->update(get_class($this), $aData, $this->pr_key.' = ?', $id) ) {
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
	
	/**
	 * Удалить запись из таблицы
	 *
	 * @param integer $id ИД ключевого поля
	 * @param string  $addit Условие удаления (по умолчанию его нет, но оно обязательно)
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
	 * @param  array $arr массив переменных
	 * @return integer Всегда возвращает 0
	 */
	function BindRequest($arr, $force = false){
        // из формы ID страны берется из country_db_id, в базе хранится в country_id (тоже самое и с городом)
        if ($arr['country_db_id'] && $arr['country_id'] === null) {
            $arr['country_id'] = $arr['country_db_id'];
        }
        if ($arr['city_db_id'] && $arr['city_id'] === null) {
            $arr['city_id'] = $arr['city_db_id'];
        }
		$class_vars = get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value) {
			if ($force || isset($arr[$name])){
   				$this->$name = ($force && !isset($arr[$name])) ? '' : $arr[$name];
			}
		}
		return 0;
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
  $id = intval($id);
		if ($id) $addit = $this->pr_key."='$id'" . $addit;
		if ($order) $order = " ORDER BY ".$order;
		$out = $GLOBALS['DB']->row("SELECT * FROM $current WHERE ($addit)".$order);
		foreach ($out as $key => $value){
			$this->$key = $value;
		}
		return 1;
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
	 * Проверка проинициализированы ли необходимые поля
	 * 
	 * @param array $reqvs массив с именами необходимых полей
	 * @return array сообщения об ошибках
	 */
	function check_required($reqvs){
		foreach ( $reqvs as $varname ) {
			if ( !isset($this->$varname) || !$this->$varname ) {
				$error[$varname] = "Поле заполнено некорректно";
			}
		}
		return $error;
	}
}
?>
