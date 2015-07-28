<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/classes/log.php');

/**
 * Интерфейс класса
 * @see log::addAlternativeMethodSave();
 */
interface LogSave 
{
    function setLogName($name);
    function write($str);
    function setStr($str);
    function __toString();
}

/**
 * Класс для записи логов в базу данных (stat)
 */
class log_pskb //implements LogSave
{
    private $_db; // Подключение к базе данных
    
    /**
     * Название лога @see log::$_logname;
     * @var string
     */
    private $_name;
    
    /**
     * Записываемое сообщение в лог, если определено то для записи берется именно он
     * 
     * @var string 
     */
    private $_message;
    
    /**
     * Наименование класса для доступа к нему из коллекции в классе log
     * @var string
     */
    public  $alias = 'log_pskb';
    
    /**
     * Интервал хранения логов в базе данных, все логи которые старше 2 месяцев удаляются
     */
    CONST INTERVAL_DATA = '2 month';
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        $this->_db = new DB('stat');
    }
    
    /**
     * Возвращает наименование класса
     * 
     * @return string
     */
    public function __toString() {
        return $this->alias;
    }
    
    /**
     * Задаем имя лога  @see log::$_logname;
     * 
     * @param string $name  Название лога
     */
    public function setLogName($name) {
        $this->_name = current(explode("-", $name));
    }
    
    /**
     * Возвращает имя лога
     * 
     * @return type
     */
    public function getLogName() {
        return $this->_name;
    }
    
    /**
     * Проверяет есть ли подготовленные данные для записи
     * 
     * @return boolean
     */
    public function isStr() {
        return (trim($this->_message) != '');
    }
    
    /**
     * Задает данные для записи в лог
     * 
     * @param string $str Сообщение для записи
     */
    public function setStr($str) {
        $this->_message = $str;
    }
    
    /**
     * Возвращает данные для записи в лог
     * 
     * @return string
     */
    public function getStr() {
        return $this->_message;
    }
    
    /**
     * Записывает данные в таблицу
     * 
     * @param string $str   Сообщение для записи
     * @return boolean
     */
    public function write($str = '') {
        if(is_array($str)) {
            $str = serialize($str);
        }
        
        if($this->isStr()) { // данные подготовлены
            switch(basename($this->getLogName())) {
                case 'income':
                    $content    = unserialize($str);
                    $logs       = $content['response'];
                    $logs['id'] = $logs['nickname'];
                    break;
                default:
                    $content = unserialize($str);
                    $logs    = json_decode(iconv('cp1251', 'utf8', $content['response']), 1);
                    break;
            }
        } else {
            $logs['id'] = 1;
        }
        
        if(!$logs['id']) {
            $sql = "INSERT INTO _log_pskb (date_created, link_id, logname, log) VALUES ";
            if(!$logs) return false;
            $cnt = $content;
            foreach($logs as $log) {
                $cnt['param']    = '{"id":[' . $log['id'] . ']}';
                $cnt['response'] = json_encode($log);
                $a_sql[] = $this->_db->parse("(NOW(), ?, ?, ?)", $log['id'], $this->getLogName(), iconv('utf8', 'cp1251', serialize($cnt)) );
            }
            $sql = $sql . implode(", ", $a_sql);
            $res = $this->_db->query($sql);
        } else {
            $sql = "INSERT INTO _log_pskb (date_created, link_id, logname, log) VALUES (NOW(), ?, ?, ?)";
            $res = $this->_db->query($sql, $logs['id'], $this->getLogName(), $str);
        }
        $this->getStr(null); // Удаляем данные
        return $res;
    }
    
    /**
     * Чистим от дублированных записей лога, сохраняем первую и последнюю запись
     * Чтобы знать когда первый раз записали параметр и когда последний
     * 
     * @todo Не знаю на сколько сложный будет запрос для сервера когда записей будет больше 100к, может быть стоит придумать что-то более рациональное
     * 
     * @return type
     */
    public function clearCloneData($lc_id = false) {
        if($lc_id) {
            $sWhere = $this->_db->parse('WHERE link_id = ?i', $lc_id);
        }
        
        $sql = "DELETE FROM _log_pskb
                USING (
                    SELECT MAX(id) as max_id, MIN(id) as min_id, link_id, logname, log
                    FROM _log_pskb 
                    {$sWhere}
                    GROUP BY link_id, logname, log
                ) as _tbl
                WHERE _log_pskb.link_id = _tbl.link_id
                AND _log_pskb.logname = _tbl.logname
                AND _log_pskb.log = _tbl.log
                AND _log_pskb.id <> _tbl.min_id
                AND _log_pskb.id < _tbl.max_id";
        
        return $this->_db->query($sql);
    }
    
    /**
     * Пакует старые данные в файл и если необходимо удаляет их из таблицы
     * 
     * @param boolean $is_delete    Удалять или нет из логов старые данные
     * @return boolean
     */
    public function packOldData($is_delete = false) {
        $sql  = "SELECT * FROM _log_pskb WHERE date_created < NOW() - interval ?";
        $rows = $this->_db->rows($sql, self::INTERVAL_DATA);
        if(!$rows) return false;
        $pack = serialize($rows); 
        
        $log = new log("stat_save/stat_{$this->alias}-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        $log->writeln($pack);
        
        if($is_delete) {
            return $this->clearOldData();
        }
        return true;
    }
    
    /**
     * Чистим старые данные
     */
    public function clearOldData() {
        $sql = "DELETE FROM _log_pskb WHERE date_created < NOW() - interval ?";
        return $this->_db->query($sql, self::INTERVAL_DATA);
    }
    
    /**
     * Берем все группы находящиеся в таблице логов
     * 
     * @param integer $link_id Если задан ИД, берем только те группы которые имеются для данного ИД
     * @return array
     */
    public function getNameGroupLog($link_id = false) {
        if(!$link_id) {
            $sql = "SELECT logname FROM _log_pskb WHERE link_id = ? GROUP BY logname";
        } else {
            $sql = "SELECT logname FROM _log_pskb GROUP BY logname";
        }
        return $this->_db->col($sql, $link_id); 
    }
    
    /**
     * Поиск по логам
     * 
     * @param type $param       Параметры поиска
     * @return type
     */
    public function findLogs($param, $limit = 100) {
        $limit = intval($limit);
        if(trim($param['query']) != '') { // Строка поиска
            $param['query'] = trim($param['query']);
            $aWhere[] = $this->_db->parse("log LIKE ?", "%{$param['query']}%");
        }
        if($param['link_id']) { // ИД поиска
            $aWhere[] = $this->_db->parse("link_id = ?i", $param['link_id']);
        }
        if(trim($param['logname']) != '') { // Группа поиска
            $aWhere[] = $this->_db->parse("logname = ?", trim($param['logname']));
        }
        
        if($aWhere) {
            $sWhere = "WHERE " . implode(" AND ", $aWhere);
        }
        
        $sql  = "SELECT * FROM _log_pskb {$sWhere} ORDER BY date_created DESC LIMIT {$limit};";
        return $this->_db->rows($sql);
    }
}