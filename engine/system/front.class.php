<?php
/**
 * Основной класс для функционирования движка
 */
final class front
{
    static private $_object = array();
    /**
     * Переменная для хранения данных REQUEST и доступа через статичускую переменную
     * @var
     */
	static public $_req = array();
    static $d = false;    
    static $map = false;    
    private function __construct(){}
    
	/**
	 * Вызов и вывод страницы ошибки
	 * @param string $str [optional] Строка тескта ошибки
	 * @return 
	 */
    public function error($str='') {
        self::exec_page(array("class"=>"error404", "after_uri"=>array($str)));   
        die();
    }
    
	/**
	 * Установка карты роутинга движка - служебная
	 * @param object $map Карта
	 * @return 
	 */
    public function setMap($map) {
        self::$map = $map;
    }
    
	/**
	 * Конвертировать в утф8
	 * @param string $in Строка win1251
	 * @return Строка
	 */
    public function toUtf($in) {
        if(is_array($in)) {
            foreach($in as &$i) {
                $i = self::toUtf($i);
            }
        } else {
            $in = iconv("windows-1251",'utf-8',  $in);
        }
        return $in;
    }
	/**
	 * Конвертация в win1251
	 * @param string $in Строка утф8
	 * @return Строка
	 */
    public function toWin($in) {
        if(is_array($in)) {
            foreach($in as &$i) {
                $i = self::toWin($i);
            }
        } else {
            $in = iconv('utf-8', "windows-1251",  $in);
        }
        return $in;
    }
	/**
	 * Создает хэш массив с разметкой по двум ключам из двухмерного массива 
	 * @param array $arr
	 * @param object $key_name [optional] имя меременной для ключа, если не задан задается от 0 ...
	 * @param object $val_name [optional] имя переменной для значения
	 * @return Результирующий массив
	 */
    public function get_hash($arr, $key_name=false, $val_name='id') {
        if (!is_array($arr)) return array();
        $ret = array();
        $i=0;
        foreach ($arr as $item) {
            if($key_name<>false) {
            $ret[$item[$key_name]] = $item[$val_name]; } else {
                $ret[$i] = $item[$val_name];
                $i++;
            }
        }
        return $ret;
    }
	/**
	 * Служебная функция вызова ф-ии и класса движка
	 * @param object $class Название класса
	 * @param object $die [optional] Вызвать и остановить скрипт
	 * @return 
	 */
    public function exec_page($class, $die=false) {
		//var_export($class);
        $class2 = "page_".$class['class'];
        self::$d = new $class2();
        self::$d->page = $class['class'];
        if(front::og("tpl")) front::og("tpl")->set("pageClass", self::$d->page);
		if(!$class['method']) {
			$afteruri_wothout_action = $class['after_uri'];
			$action = array_shift($afteruri_wothout_action);
		} else {
			$action = $class['method'];
		}
        if(!$action) {            
            $afteruri_wothout_action = $class['after_uri'];
            $action = "index";
        }

        $method = strtolower($action) . "Action";
        if(method_exists(self::$d, $method)) {
            self::$d->uri = $afteruri_wothout_action;
            self::$d->action = $method;
            //die(self::$d->action);
            if(front::og("tpl")) front::og("tpl")->set("action", self::$d->action);
            self::$d->$method();    
        } elseif((method_exists(self::$d, "indexAction") && $method == "")) {
            self::$d->uri = $class['after_uri'];
            self::$d->action = "indexAction";
            if(front::og("tpl")) front::og("tpl")->set("action", self::$d->action);
            self::$d->indexAction();     
        } else {
            self::error();
        }
       
        if($die) die();
    }
    
	/**
	 * Обработчик строки адреса через карту роутинга
	 * @param string $uri_ Строка запроса адреса
	 * @return 
	 */
    public function exec_uri($uri_) {
        $map = self::$map;
        $uri_input = $uri_;
        $uri_ = explode("?", $uri_);
        $uri_ = $uri_[0];
        $uri = explode("/", $uri_);
        
        $doc_root = getcwd();
        
        if($uri_ == '/') {
            if(isset($map["index"])) {
                self::exec_page(array("class"=>$map["index"]["class"], "after_uri"=>$uri)); 
            }
            else if(file_exists($doc_root . DIR_SEP . 'index.php')) {
                return;
            }
            else {
                self::error();
            }
            return 0;   
        }
        
        array_shift($uri);
        $end_slash = array_pop($uri);
        if($end_slash !== '') {
            header_location_exit($uri_input . '/');
            exit();
            //self::error();
        }

        $i = 0;
        
        $class = array();
        if(empty($class["class"]))
        while(sizeof($uri) && $i<6) {
            $i++;
            $dat = array_shift($uri);
            
            if($dat == "adminback") {
                if(!hasPermissions('adm')) {
                    self::error("Нет прав");    
                }
            }
            
            //vardump();
            $error = true;
            
            if(isset($map[$dat])) {
                $map = $map[$dat]; 
                $error = false;
            } else {
                break;
            }
            
            if(isset($map[(array_shift($temp_sub = $uri))])) {
                continue;
            }
            if(isset($map["class"])) {
					$class=array("class"=>$map["class"], "method"=>$map["method"], "after_uri"=>$uri);
                break;       
            }
        }
        
       // vardump($class);

        if(!isset($class["class"])) {
            self::error();
        } else {
            self::exec_page($class);
//            exit();
        }
    }
	
	/**
	 * Проверяет, если ли класс в статическом репозитории объектов
	 * @param string $name Метка класса
	 * @return 
	 */
    static public function oc($name) {
        return isset(self::$_object[$name]);
    }
    
	/**
	 * Возвращает класс из статического репозитория объектов, если существует
	 * @param string $name Метка класса
	 * @return 
	 */
    static public function og($name) {
        if (!is_string($name) || !array_key_exists($name, self::$_object)) {
            return false;
        }
        return self::$_object[$name];
    }
    /**
     * Добавляет класс в статический репозиторий объектов
     * @param string $name Метка класса
     * @param object $obj Класс
     * @return 
     */
    static public function os($name, &$obj) {
        if (!is_string($name) || array_key_exists($name, self::$_object) || !is_object($obj)) {
            return false;
        }
        
        self::$_object[$name] = $obj;
        return true;
    }
    /**
     * Авто лоадер классов
     * @param string $class Имя класса
     * @return 
     */
    static public function load_class($class) {
        if(class_exists($class)){
            return 1;
        }
        $class = strtolower($class);    
        if(($v5_substr = substr($class, 0,5)) && $v5_substr === 'page_' && file_exists(ROOT_DIR.'engine/page/'.$class.'.class.php')) {
            require_once(ROOT_DIR.'engine/page/'.$class.'.class.php');   
            if(class_exists($class)) { return true; }       
        } elseif(($v7_substr = substr($class, 0,7)) && $v7_substr === 'system_' && file_exists(ROOT_DIR.'engine/system/'.$class.'.class.php')) {
            require_once(ROOT_DIR.'engine/system/'.$class.'.class.php');   
            if(class_exists($class)) { return true; }    
        } elseif(file_exists(ROOT_DIR.'engine/'.$class.'.class.php')) {
            require_once(ROOT_DIR.'engine/'.$class.'.class.php');   
            if(class_exists($class)) { return true; }    
        } elseif(file_exists(ROOT_DIR.'engine/classes/'.$class.'.php')) {
            require_once(ROOT_DIR.'engine/classes/'.$class.'.php');   
            if(class_exists($class)) { return true; }    
        } elseif(file_exists(ROOT_DIR.'classes/'.$class.'.php')) {
            require_once(ROOT_DIR.'classes/'.$class.'.php');   
            if(class_exists($class)) { return true; }    
        }
        return false;
    }
    public function __destruct(){}
}
?>
