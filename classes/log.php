<?php

if(!defined('LOG_DIR'))
    define('LOG_DIR', '/var/tmp/');
    
/**
 * Класс для ведения логов
 */
class log {

    private $_files = array();
    private $_fp = false;
    private $_beginTime;
    private $_logname;
    private $_mode;
    
    /**
     * Альтернативные методы записи 
     * 
     * @see LogSave
     * @var array
     */
    private $_alternative_methods = array(); // Альтернативные методы записи лога
    
    /**
     * Включение/отключение альтернативных методов записи
     * 
     * @var array
     */
    private $_is_use_alternative_methods = false;
    public  $linePrefix = ''; // добавляется в начале каждой строки, можно в формате strftime.
    
    /**
     * Конструктор класса
     * 
     * @param string $logname имя открываемого файла
     * @param string $mode способ доступа к файлу (см. fopen)
     */
    function __construct($logname, $mode = 'a', $line_prefix = '') {
        if(strpos($logname, '../') !== false) {
            return;
        }
        date_default_timezone_set('Europe/Moscow');
        $this->_logname = $logname;
        $this->_mode = $mode;
        $this->_beginTime = microtime(true);
        $this->linePrefix = $line_prefix;
    }
    
    /**
     * Деструктор класса
     */
    function __destruct() {
        if($this->_fp) {
            fclose($this->_fp);
        }
    }
    
    /**
     * Добавляет альтернативный способ записи данных в логи
     * 
     * @param LogSave $obj  Обьект реализующий запись в БД
     * @param boolean $use  Включить альтернативную запись или нет
     */
    function addAlternativeMethodSave($obj, $use = null) {
        $obj->setLogName($this->_logname);
        $this->_alternative_methods[$obj->__toString()] = $obj;
        if($use !== null) $this->setUseAlternativeMethod($use);
    }
    
    /**
     * Включение альтернативного способа записи данных, 
     * если параметр false - альтернативные способы использоваться не будут
     * 
     * @param boolean $use
     */
    function setUseAlternativeMethod($use) {
        $this->_is_use_alternative_methods = $use;
    }
    
    /**
     * Пре-инициализация данных для записи через альтернативный способ
     * 
     * @param string $str             Данные для записи
     * @param string $method_alias    Название метода
     */
    function setAlternativeWrite($str, $method_alias) {
        $this->_alternative_methods[$method_alias]->setStr($str);
    }
    
    /**
     * Возвращает имя файла лога.
     * @param boolean $root   вернет с абсолютным путем.
     * @return string
     */
    function getLogname($root = false) {
        if($this->_logname) {
            return ($root ? LOG_DIR : '') . strftime($this->_logname);
        }
        return NULL;
    }
    
    /**
     * Возвращает время выполнения
     *
     * @param  string $fmt опционально. формат времени или NULL чтобы получить время выполнения в секундах
     * @return mixed
     */
    function getTotalTime($fmt = '%H:%M:%S', $msecs = 0) {
        $diff = microtime(true) - $this->_beginTime;
        $s = floor($diff);
        if($msecs) {
            $ms = '.' . round(pow(10,$msecs)*($diff - $s));
        }
        return $fmt === NULL ? $diff : gmstrftime($fmt, $s).$ms;
    }
    
    /**
     * Открывает файл лога.
     *
     */
    private function _open() {
        if (!$this->_fp) {
            $logname = $this->getLogname();
            if( $logname && !($this->_fp = @fopen(LOG_DIR.$logname, $this->_mode)) ) {
                $dirs = explode('/', dirname($logname));
                $pth = LOG_DIR;
                foreach($dirs as $d) {
                    $pth .= $d.'/';
                    if(!file_exists($pth))
                        mkdir($pth, 0777);
                }
                $this->_fp = fopen(LOG_DIR.$logname, $this->_mode);
            }
            if($this->_fp && !isset($_SERVER['REQUEST_METHOD'])) {
                chmod(LOG_DIR.$logname, 0666);
            }
        }
        return !!$this->_fp;
    }
    
    /**
     * Возвращает содержимое файла в виде массива строк
     * 
     * @param  string $name путь к файлу
     * @return array
     */
    function _getFile($name) {
        if(!$this->_files[$name])
            $this->_files[$name] = file($name);
        return $this->_files[$name];
    }
    
    /**
     * Записывает данные в текущий лог
     * 
     * @param string $str данные для записи
     */
    function write($str) {
        if($this->_open()) {
            fwrite($this->_fp, $str);
        }
        
        if($this->_is_use_alternative_methods) { // Если существует альтернативный метод записи тогда пишем
            foreach($this->_alternative_methods as $method) {
                if(!$method->isStr()) { // Если данные не готовы, берем то что дают
                    $method->write($str);
                } else{
                    $method->write($method->getStr());
                }
            }
        }
    }
    
    /**
     * Записывает отформатированную строку в текущий лог
     *
     * @param string $str данные для записи
     */
    function writeln($str = '') {
        $this->write(strftime($this->linePrefix).$str."\n");
    }
    
    /**
     * Записывает значение переменной в лог
     *
     * @param string $var данные для записи
     */
    function writevar($var) {
        $this->writeln(var_export($var, true));
    }
    
    /**
     * Записывает в лог отладочную информацию выполненного действия.
     * 
     * @param mixed $res результат какого либо действия (функции например).
     */
    function trace($res) {
        $tre = '/^\$\w+\s*->\s*trace\s*\((.*?)(?:,[^\)]+)*\)\s*;\s*$/i';
        $bt = current(debug_backtrace());
        $ln = $bt['line'];
        $file = $this->_getFile($bt['file']);
        $fn = trim(preg_replace($tre, '$1',trim($file[$ln-1])));
        $dt = date('d.m.Y H:i:s');
        $ln = str_pad($ln, 4, '0', STR_PAD_LEFT);
        ob_start();
        var_dump($res);
        $res = trim(ob_get_clean());
        $this->writeln("{$dt}, ln: {$ln}, {$fn} = {$res}");
    }
    
    function writedump($param, $title = '') {
        ob_start();
        var_dump($param);
        $out = ob_get_clean();
        if($title != '') {
            $this->write($title."\r\n");
        }
        $this->writeln($out);
    }
} 


