<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с датами, допиленый под наши потребности
 */
class LocalDateTime extends DateTime {
    
    private $_debug = false;
    
    /**
     * Стандартные выходные (сб, вс)
     * 
     * @var array
     */
    private $_holidays = array(6,7); 
    
    /**
     * Стандартные рабочие дни (пн, вт, ср, чт, пт, сб)
     * 
     * @var array
     */
    private $_workdays = array(1,2,3,4,5);
    
    /**
     * Не стандартные выходные дни (праздничные дни)
     * 
     * @var array array(20121230, 20121231, ...)
     */
    public $exc_holidays = array();
    
    /**
     * Не стандартные рабочие дни
     * 
     * @var array array(20121230, 20121231, ...)
     */
    public $exc_workdays = array();
    
    public $start_time = "now";
    
    public function __construct($time="now", $object = null, $init_default = true) {
        $this->start_time = $time;
        if($object === null) {
            parent::__construct($time);
        } else {
            parent::__construct($time, $object);
        }
        if($init_default) {
            $this->getExcDaysInit(false, true);
        }
    }
    
    /**
     * Не стандартные выходные дни (праздничные дни)
     * 
     * @param array $holidays   array(20121230, 20121231, ...)
     */
    public function setHolidays($holidays, $merge = false) {
        if($merge && !empty($this->exc_holidays)) {
            $this->exc_holidays = array_merge($this->exc_holidays, $holidays);
        } else {
            $this->exc_holidays = $holidays;
        }
    }
    
    /**
     * Не стандартные рабочие дни
     * 
     * @param array $workdays   array(20121230, 20121231, ...)
     */
    public function setWorkdays($workdays, $merge = false) {
        if($merge && !empty($this->exc_workdays)) {
            $this->exc_workdays = array_merge($this->exc_workdays, $workdays);
        } else {
            $this->exc_workdays = $workdays;
        }
    }
    
    /**
     * Не стандартные рабочие дни добавляем новые дни в уже инициализированные
     * 
     * @param array $workdays   array(20121230, 20121231, ...)
     */
    public function setMergeWorkdays($workdays) {
        $this->exc_workdays = array_merge($this->exc_workdays, $workdays);
    }
    
    /**
     * Берем данные за определенный год
     * 
     * @global type $DB
     * @param mixed $year        Год, если нет текущий
     * @param boolean $set       Записать данные сразу в переменные класса или нет
     * @param boolean $cache     Использовать кеш или нет
     * @return type
     */
    public function getExcDaysInit($year = false, $set = false, $cache = true) {
        global $DB;
        
        if($year == false) $year = $this->format('Y');
        
        $mem = new memBuff();
        $exc_days = $cache ? $mem->get( "exc_days_{$year}" ) : false;
        
        if($exc_days === false) {
            $sql = "SELECT * FROM exception_date WHERE year = ?";
            $exc_days = $DB->row($sql, $year);
            $mem->set( "exc_days_{$year}", $exc_days, 1800 );
        }
        if(!$exc_days) return array();
        $workdays = $this->initCollectionDate($exc_days['workdays']);
        $holidays = $this->initCollectionDate($exc_days['holidays']);
        
        if($set) {
            $this->setWorkdays($workdays, true);
            $this->setHolidays($holidays, true);
            return;
        }
        
        return $exc_days;
    }
    
    /**
     * Приводим данные по датам в корректный массив
     * 
     * @param string $days    Даты @example (20121001,20121030)
     * @return type
     */
    public function initCollectionDate($days) {
        $array = explode(",", $days);
        $array = array_map("trim", $array);
        $array = array_map("intval",  $array);
        $array = array_unique($array);
        return $array;
    }
    
    /**
     * Обновляем или записываем данные по конкретному дню
     * 
     * @global type $DB
     * @param array  $edate   Данные для обновления
     * @param string $act     Действие функции (обновить или записать новую запись)
     * @return type
     */
    public function updateExcDays($edate, $act = 'update') {
        global $DB;
        
        $mem = new memBuff();
        $mem->delete("exc_days_{$edate['year']}");
        
        if($act == 'update') {
            $sql = "UPDATE exception_date SET holidays = ?, workdays = ? WHERE id = ?i";
            $res = $DB->query($sql, $edate['holidays'], $edate['workdays'], $edate['id']);
        } else {
            $sql = "INSERT INTO exception_date (year, holidays, workdays) VALUES(?, ?, ?)";
            $res = $DB->query($sql, $edate['year'], $edate['holidays'], $edate['workdays']);
        }
        
        return $res;
    }
    
    /**
     * Проверяем в текущей дате стандартный выходной или нет
     * 
     * @return boolean      true - Текущий день выходной
     */
    public function isStandartHoliday() {
        return in_array($this->format('N'), $this->_holidays);
    }
    
    /**
     * Проверяем в текущей дате стандартный рабочий день или нет
     * 
     * @return boolean      true - Текущий день рабочий
     */
    public function isStandartWorkday() {
        return in_array($this->format('N'), $this->_workdays);
    }
    
    /**
     * Проверяем в текущей дате не стандартный рабочий день
     * 
     * @return boolean      true - Текущий день рабочий
     */
    public function isExceptionWorkday() {
        return in_array($this->format('Ymd'), $this->exc_workdays );
    }
    
    /**
     * Проверяем в текущей дате стандартный выходной или нет
     * 
     * @return boolean      true - Текущий день выходной (праздничный)
     */
    public function isExceptionHoliday() {
        return in_array($this->format('Ymd'), $this->exc_holidays );
    }
    
    /**
     * Текущее число считается рабочим или нет в зависимости от не стандартных рабочих дней
     * учитывает так же ситуацию когда день постандарту рабочий но по не стандарту праздничный
     * 
     * @return boolean  - true - Текущий день рабочий 
     */
    public function isWorkday() {
        if($this->isExceptionWorkday() || ( $this->isStandartWorkday() && !$this->isExceptionHoliday())) {
            return true;
        }
    }
    
    /**
     * Приводит дату к числу необходимому для получения $day - рабочих дней
     * 
     * @param integer $day  Сколько рабочих дней необходимо плюсануть
     */
    public function getWorkForDay($day = 5, $clear = false) {
        if($clear) {
            $this->setDate(date('Y'), date('m'), date('d'));
        }
        $cnt_work_day = 0;
        $safe_while   = 0; // На вяский случай от зацикливания, параноя...
        while($cnt_work_day < $day && $safe_while < 100) {
            if($this->isWorkday()) {
                $cnt_work_day++;
            }
            
            if($this->_debug) {
                if($this->isWorkday()) {
                    echo $this->format('d.m.Y') ." -- WORK\r\n";
                } else {
                    echo $this->format('d.m.Y') ." -- FREE\r\n";
                }
            }
            if($cnt_work_day < $day) {
                $Y1 = $this->format("Y");
                $this->modify('+1 day');
                if($Y1 != $this->format("Y")) {
                    // Подгружаем следующий год
                    $this->getExcDaysInit($this->format('Y'), true);
                }
            }
            $safe_while++;
        }   
    }
    
    /**
     * возвращает количество рабочих дней начиная с установленной даты и заканчивая заданной датой
     * ВНИМАНИЕ!!! увеличивает сохраненную внутреннюю дату
     * @param integer $endDate дата в формате UnixTime
     */
    public function howManyWorkDays ($endDate) {
        if (!$endDate || !$this->getTimestamp() || $this->getTimestamp() > $endDate) {
            return 0;
        }
        $workDays = 0;
        $calDays = ceil(($endDate - $this->getTimestamp()) / (3600 * 24)) + 1;
        if ($calDays < 0) {
            return 0;
        }
        for ($i = 0; $i < $calDays; $i++) {
            $workDays += $this->isWorkday() ? 1 : 0;
            $this->modify('+1 day');
        }
        
        return $workDays;
        
    }
    
    public function getTimestamp() {
        return $this->format('U');
    }
    // @todo костыль для весрии 5.2.17
    public function setTimestamp($u) {
        $this->setDate(date('Y', $u), date('m', $u), date('d', $u));
        $this->setTime(date('H', $u), date('i', $u), date('s', $s));
    }
    
    public function getCountDays($stime = null) {
        if($stime == null) $stime = strtotime($this->start_time);
        return ceil ( ( $this->format('U') - $stime ) / ( 3600 * 24 ) );
    }
}

?>