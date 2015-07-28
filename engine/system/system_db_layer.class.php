<?
/**
 * Класс для работы с базой данных
 *
 */
class system_db_layer {
    /**
     * Переменная инициализации класса
     *
     * @var object
     */
	static $instanse = false;
	/**
	 * Переменная подключения
	 *
	 * @var resource
	 */
	public $connection = false;
	/**
	 * Конструктор класса
	 *
	 * @param string $host Адресс БД
	 * @param string $user Пользователь БД
	 * @param string $pass Пароль к БД
	 * @param string $db   Название БД
	 * @param mixed $sett Дополнительные настройки [port=>5432, presistent=>true]
	 */
    static function connect($host, $user, $pass, $db, $sett = array()) {
        $port = 5432; // Порт по умолчанию
        if(isset($sett['port'])) $port = $sett['port'];
        
        $persistent = false;
        if(isset($sett['persistent'])) $persistent = $sett['persistent'];
        
        self::$instanse = new system_db_layer();
        
        try {
            if(!$persistent)
                self::$instanse->connection = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass"); // Подключение к БД
            else
                self::$instanse->connection = pg_pconnect("host=$host port=$port dbname=$db user=$user password=$pass");     
        } catch(Exception $e) {
            self::$instanse = false; 
            trigger_error("Connect db($db) error ", E_USER_ERROR);
        }
        
        return self::$instanse;
    }
    /**
     * Установка переменной подключения. Возвращает указатель на соединение c БД
     *
     * @param resource $conn Указатель на соединение с БД
     * @return Указатель на соединение с БД
     */
    static function setConnection($conn) {
        self::$instanse = new system_db_layer();
        
        self::$instanse->connection = $conn;    
        
        return self::$instanse;
    }
    /**
     * Инициализация класса
     *
     * @return unknown
     */
    static function getInstance() {
        if(self::$instanse === false) {
             trigger_error("Instance not found, plese create connect to db!", E_USER_ERROR);
        }
        return self::$instanse;    
    }
    public function system_db_layer() {
        
        
    }
    /**
     * Функция выборки через SELECT
     *
     * // ?s - подстановка массива [[name=>'nane'],[kod=>'kod']] 
     * где ключ это имя поля, значение массива это данные для обновления
     * // ?a - подстановка массива [1,3,4,5,6,7] Используется в случае 'id IN (?a)'
     * // ?f - подстановка FLOAT
     * 
     * Достать одну запись:
     * // ?n - постановка INT
	 * $db->select("SELECT * FROM test WHERE id = ?n", 1)->fetchRow();
	 * 
	 * Достать все записи:
	 * $db->select("SELECT * FROM test;")->fetchAll();
	 * 
	 * Достать одно значения:
	 * $db->select("SELECT name FROM test WHERE id = ?n", 1)->fetchOne();
     * 
     * @return возврашает класс db_layer_statement
     */
    public function select() {
        $args = func_get_args();
        
        if(sizeof($args) == 0) {
            trigger_error("Invalid count of args", E_USER_ERROR);
        }
          
        $statement = new db_layer_statement($this->connection, array_shift($args), $args);
        return $statement->select();
    }
    /**
     * Добавить запись в БД
     *
     * @param string $table_name Название таблицы // test
     * @param array $array Данные для записи [[name=>'nane'],[kod=>'kod']]
     * @return ID записи если ID записи добавляется инкрементно
     */
    public function insert($table_name, $array, $pkey = "id") {
        $values = array();
        $keys = array();
        
        foreach($array as $key=>$value) {
            $keys[] = pg_escape_string($key);
            if(!is_integer($value)) {
                $value = "'" . pg_escape_string($value) . "'";
            }
            $values[] = $value;
        }
        
        if ($pkey) $returning = " RETURNING ".$pkey;
        
        $query = "INSERT INTO $table_name (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $values) . ")".$returning;    
        
        $result = pg_query($this->connection, $query);
        
        list($insertedId) = pg_fetch_row($result);     
        if($insertedId) {
            return $insertedId;
        }
        //vardump($insertedId);
        //return $insertedId[0]['ins_id'];
        
        return pg_last_oid($result); 
    }
    /**
     * Выдать SQL запрос
     *
     * @return SQL запрос
     */
    public function sql() {
        $args = func_get_args();
        
        if(sizeof($args) == 0) {
            trigger_error("Invalid count of args", E_USER_ERROR);
        }
          
        $statement = new db_layer_statement($this->connection, array_shift($args), $args);
        return $statement->sql();
    }
    /**
     * Обновить запись в БД
     * 
     * // ?n - постановка INT
     * // ?s - подстановка массива 
     * $save = [[name=>'nane'],[kod=>'kod']] 
     * где ключ это имя поля, значение массива это данные для обновления
     * 
	 * $db->update("UPDATE test SET name ?s WHERE id = ?n", $save, 1);
     * 
     * @return unknown
     */
    public function update() {
        $args = func_get_args();
        
        if(sizeof($args) == 0) {
            trigger_error("Invalid count of args", E_USER_ERROR);
        }
          
        $statement = new db_layer_statement($this->connection, array_shift($args), $args);
        return $statement->update();
    }
    /**
     * Удалить запись из БД
     *
     * // ?n - постановка INT
	 * $db->update("DELETE FROM test WHERE id = ?n", 1);
     * 
     * @return unknown
     */
    public function delete() {
        $args = func_get_args();
        
        if(sizeof($args) == 0) {
            trigger_error("Invalid count of args", E_USER_ERROR);
        }
          
        $statement = new db_layer_statement($this->connection, array_shift($args), $args);
        return $statement->delete();
    }
}
/**
 * Класс для обработки и выполнения SQL запросов
 *
 */
class db_layer_statement {
	/**
	 * Тип подключения
	 *
	 * @var unknown_type
	 */
    private $statement = false;
    /**
     * Параметры
     *
     * @var array
     */
    private $params = array();
    /**
     * Выполнение запроса
     *
     * @var unknown_type
     */
    private $executed = false;
    /**
     * Запрос к БД
     *
     * @var unknown_type
     */
    private $query = false;
    const REGEX_SELECT = "{(\?[a|n|f|v]?)}sx";
    const REGEX_UPDATE = "{(\?[a|n|f|s|v]?)}sx";
    /**
     * Указатель на соединение c БД
     *
     * @var resource
     */
    private $connection = false;
    /**
     * Конструктор класса
     *
     * @param resource $connection Указатель на соединение
     * @param string   $query Запрос
     * @param mixed    $params Параметры
     * @return unknown
     */
    function __construct(&$connection, $query, $params) {
        $this->connection = $connection;
        $this->query = $query;
        $this->params = $params;
        
        $this->temp_params = $this->params;
        return $this;
    }
    /**
     * Обновить запись
     *
     * @return unknown
     */
    public function update() {
        if(sizeof($this->temp_params) > 0)  {
            $this->query = preg_replace_callback(
                  self::REGEX_UPDATE,
                  array(&$this, '_callback_replace_placeholder'),
                  $this->query);
        }
        $result = pg_query($this->connection, $this->query); 
        if (!$result) {
            trigger_error("Update error: ". pg_last_error($this->connection)." SQL ".$this->query, E_USER_ERROR);
        }
        return pg_affected_rows($result);
    }
    /**
     * Удаление записи
     *
     * @return unknown
     */
    public function delete() {
        if(sizeof($this->temp_params) > 0)  {
            $this->query = preg_replace_callback(
                  self::REGEX_SELECT,
                  array(&$this, '_callback_replace_placeholder'),
                  $this->query);
        }
        $result = pg_query($this->connection, $this->query); 
        if (!$result) {
            trigger_error("Insert error: ". pg_last_error($this->connection)." SQL ".$this->query, E_USER_ERROR);
        }
        return pg_affected_rows($result);
    }
    /**
     * Выборка записи
     *
     * @return self
     */
    public function select() {
        return $this;
    }
    /**
     * Обработка SELECT запроса
     *
     */
    private function select_prepare() {
        if(sizeof($this->temp_params) > 0)  {
            $this->query = preg_replace_callback(
                  self::REGEX_SELECT,
                  array(&$this, '_callback_replace_placeholder'),
                  $this->query);
        }
        
    }
    /**
     * Возвращает запрос к БД
     *
     * @return unknown
     */
    public function sql() {
        $this->select_prepare();
        return $this->query;
    }
    /**
     * Выбрать все записи, по запросу
     *
     * @param unknown_type $map_id
     * @return unknown
     */
    public function fetchAll($map_id=false) {
        $this->select_prepare();
        $result = pg_query($this->connection, $this->query);
        if (!$result) {
            debug_print_backtrace();
            trigger_error("Select error: ". pg_last_error($this->connection), E_USER_ERROR);
        }

        $result = pg_fetch_all($result);
        if($map_id) {
            if(!is_array($map_id)) {
                if(sizeof($result)) {
                    $result_maped = array();
                    foreach($result as $row) {
                        if(!($mapedval = $row[$map_id])) {
                            trigger_error("Map id column not found", E_USER_ERROR);
                        }
                        $result_maped[$mapedval] = $row;
                    }
                    return $result_maped;
                }
            } else {
                if(sizeof($result)) {
                    $result_maped = array();
                    $key = key($map_id);
                    $val = array_pop($map_id);
                    
                    foreach($result as $row) {
                        if(!($mapedval = $row[$key]) || ($mapedval2 && !($mapedval2 = $row[$val]))) {
                            trigger_error("Map id column not found (colomn '".$key."' in sql ".$this->query.")", E_USER_ERROR);
                        }
                        if($mapedval2)
                            $result_maped[$mapedval][$mapedval2] = $row;
                        else {
                            $result_maped[$mapedval][] = $row;
                        }
                    }
                   
                    return $result_maped;
                }
            }
        }
        return $result;
    }
    /**
     * Выборка одной строки записи из БД
     *
     * @return unknown
     */
    public function fetchRow() {
        $this->select_prepare();
        $result = pg_query($this->connection, $this->query);
        if (!$result) {
            trigger_error("Select error: ". pg_last_error($this->connection), E_USER_ERROR);
        }

        return pg_fetch_assoc($result);
    }
    /**
     * Выбрать одно поле по запросу из БД
     *
     * @return unknown
     */
    public function fetchOne() {
        return array_shift($this->fetchColumn());
    }
    /**
     * Выбрать колонку из БД
     *
     * @return unknown
     */
    public function fetchColumn() {
        $this->select_prepare();
        $result = pg_query($this->connection, $this->query);
        if (!$result) {
            trigger_error("Select error: ". pg_last_error($this->connection), E_USER_ERROR);
        }

        return pg_fetch_all_columns($result);
    }
    /**
     * Обработка дополнительных переменных
     *
     * @param array $matches
     * @return unknown
     */
    private function _callback_replace_placeholder($matches) {
        if(($value = array_shift($this->temp_params)) === null) {
            trigger_error("Placeholder arg not found!", E_USER_ERROR);
        }
        
        switch($matches[1]{1}) {
            case "a": // Тип переменной массив, ("SELECT ... WHERE id IN (?a)", array(1,2,3,4,5));
                if(is_array($value)) {
                    foreach($value as &$val) {
                        if(!is_integer($val))
                            $val = "'" . $this->_prepare_value($val) . "'"; 
                    }
                    return implode(", ", $value);
                } else {
                    trigger_error("?a value is not Array!", E_USER_ERROR);    
                }
            break;
            case "n": // Тип переменной INT, ("SELECT ... WHERE id = ?n", 2);
                return intval($value);
            break;
            case "s": // Тип перменной массив, ("UPDATE ... SET ?s WHERE ...", array('name'=>'Name1', 'time'=>'Time1'));
                $update_arr = array();
                foreach($value as $update_key=>&$update_param) {
                    if(!is_int($update_param)) {
                        $update_param = "'" . $this->_prepare_value($update_param) . "'";
                    }
                    
                    $update_arr[] = $update_key . " = " . $update_param;
                }
                return implode(", ", $update_arr);
            break;
            case "f": // Тип переменной FLOAT, ("SELECT ... WHERE id = ?f", 10.15);
                return floatval($value);
            break;
            case "v": // Тип переменной - значение,  ("SELECT ... WHERE id = ?v", 'tbl1.id');
                return $this->_prepare_value($value);
            break;
            default: // Тип переменной - текст, ("SELECT ... WHERE name = ?", 'Name1');
                return "'" . $this->_prepare_value($value) . "'";
            break;
        }
    }
    /**
     * Обработка переменной
     *
     * @param mixed $value
     * @return unknown
     */
    private function _prepare_value($value) {
        if(get_magic_quotes_gpc() || defined('NEO')) { // Creaker проставляет слеши для общей совместимости, но get_magic_quotes_gpc отключен.
            $value = stripslashes($value);
        }
        return pg_escape_string($value);
    }
    /**
     * Функция для защиты от SQL-Injection
     *
     * @param mixed $param_to_clean
     * @return mixed
     */
    public static function clean_up($param_to_clean) {
        //if(!isset($param_to_clean)) return $param_to_clean;
        //$param_to_clean = preg_replace('/^(\s|\t|\n)+|(\s|\t|\n)+$/', '', $param_to_clean);
        //$param_to_clean = stripslashes($param_to_clean);
        
        //$param_to_clean = preg_replace('/\;/', 'xX;Yy', $param_to_clean);
        //$param_to_clean = htmlspecialchars($param_to_clean, ENT_QUOTES);
        
        $param_to_clean = preg_replace('/\`/', '&#039;', $param_to_clean);
        $param_to_clean = preg_replace('/\"/', '&#039;', $param_to_clean);
        $param_to_clean = preg_replace('/\|/', '&#124;', $param_to_clean);
        $param_to_clean = preg_replace('/\\\/', '&#092;', $param_to_clean);
        $param_to_clean = preg_replace('/\%/', '&#037;', $param_to_clean);
        $param_to_clean = preg_replace('/\?/', '&#063;', $param_to_clean);
        $param_to_clean = preg_replace('/\//', '&#047;', $param_to_clean);
        $param_to_clean = preg_replace('/\(/', '&#040;', $param_to_clean);
        $param_to_clean = preg_replace('/\)/', '&#041;', $param_to_clean);
        $param_to_clean = preg_replace('/\:/', '&#058;', $param_to_clean);
        //$param_to_clean = preg_replace('/xX\;Yy/', '&#059;', $param_to_clean);
        
        //if (strlen($param_to_clean) > $max_len) {
         //   $param_to_clean = substr($param_to_clean, 0, $max_len);
        //}
        
        $param_to_clean = preg_replace('/(select|union|update|delete|create)\s+/i', '$1&nbsp;', $param_to_clean);
        
        return $param_to_clean;
    }
    
}

?>