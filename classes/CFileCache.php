<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/memBuff.php');

/**
 * Кэширует информацию о файлах.
 * Кэш информация используется в функциях CFile::GetInfo() и CFile::GetInfoById(), 
 * т.е. при инициализации файла через конструктор CFile.
 * Это позволяет избежать лишних запросов в БД, когда даже после получения информации о файле,
 * мы снова вызываем CFile (например, см. stdf.php, viewAttachLeft())
 * Файлы кэшируются сначала в массив текущего процесса, а по завершению
 * переносятся в мемкэш.
 */
class CFileCache {
    
    const MEM_PREFIX = 'CFileCache.';
    const MEM_LIFE   = 1200; // Время жизни кэша.
    
    /**
     * @see CFileCache::$_memAllowedForceLag
     */
    const MEM_ALLOWED_FORCE_COEFF = 10;

    /**
     * @var CFileCache   экземпляр класса
     */
    private static $_inst = NULL;
    
    /**
     * Время в секундах с момента записи файла, по истечение которого уже не важно был ли проверен файл антивирусом
     * или нет. В любом случае кладем его в кэш.
     * @see CFileCache::MEM_ALLOWED_FORCE_COEFF
     * @see CFileCache::__construct()
     *
     * @var integer
     */
    private $_memAllowedForceLag;

    /**
     * Хэш, хранящий информацию о файлах.
     * Файлы доступны по ключам [path+fname] или [id].
     * 
     * @var array
     */
    private $_cache = array();
    
    /**
     * @var memBuff
     */
    private $_memBuff;

    /**
     * Метод для получения глобального экземпляра.
     * Запрещено создавать несколько иначе смысл кэширования теряется
     * @return CFileCache
     */
    static function getInstance() {
        if(!self::$_inst)
            self::$_inst = new CFileCache();
        return self::$_inst;
    }

    /**
     * Конструктор
     * @see CFileCache::getInstance()
     */
    private function __construct() {
        $this->_memBuff = new memBuff();
        $this->_memAllowedForceLag = self::MEM_LIFE * self::MEM_ALLOWED_FORCE_COEFF;
    }
    
    /**
     * Сообщает, можно ли кэшировать файл.
     * Запрещено, когда файл недавно загрузился и еще не был проверен антивирусом (иначе юзер получит ложную информацию).
     *
     * @return boolean   да|нет
     */
    private function _memAllowed(&$row) {
        return ( !is_null($row['virus']) || strtotime($row['modified']) < time() - $this->_memAllowedForceLag );
    }

    /**
     * Деструктор.
     * @see CFileCache::getInstance()
     */
    function __destruct() {
        foreach($this->_cache as $key=>$row) {
            if ($this->_memAllowed($row)) {
                $this->_memBuff->add($this->_memkey($key), $row, self::MEM_LIFE);
            }
        }
    }

    /**
     * Кладет файл(ы) в кэш.
     * @param array $rows   один или несколько файлов (массивов с полями).
     */
    function put($rows) {
        if (!$rows) return;
        if (isset($rows['fname'])) {
            $rows = array($rows);
        }
        foreach ($rows as $r) {
            $k1 = self::_k($r, 1);
            $this->_cache[$k1] = $r;
            $this->_cache[self::_k($r, 2)] = &$this->_cache[$k1];
        }
    }

    /**
     * Выдает файл из кэша. 
     *
     * @param string|integer $key   ключ [path+fname] или [id]
     * @return array   информация о файле.
     */
    function get($key) {
        if( !($row = $this->_cache[$key]) ) {
            $row = $this->_memBuff->get(self::_memkey($key));
        }
        return $row;
    }

    /**
     * Удаляет файл из кэша. 
     * @param string|integer $key   ключ [path+fname] или [id]
     */
    function del($key) {
        if($r = $this->_cache[$key]) {
            unset($this->_cache[self::_k($r, 1)]);
            unset($this->_cache[self::_k($r, 2)]);
        }
        if($r = $this->_memBuff->get(self::_memkey($key))) {
            $this->_memBuff->delete(self::_memkey(self::_k($r, 1)));
            $this->_memBuff->delete(self::_memkey(self::_k($r, 2)));
        }
    }

    /**
     * Возвращает ключ того или другого типа по данным файла для сохранения в хэш ($this->_cache).
     *
     * @param array $r   файл (массив с полями)
     * @param integer $t   требуемый тип ключа (1:ид; 2:path+fname).
     * @return string   ключ.
     */
    private static function _k(&$r, $t) {
        return $t == 2 ? $r['path'].$r['fname'] : $r['id'];
    }

    
    /**
     * Возвращает ключ для сохранения в мемкэш.
     *
     * @param string $key    ключ файла (см. функцию _k()).
     * @return string   ключ.
     */
    private static function _memkey($key) {
        return md5(self::MEM_PREFIX.$key);
    }
}

// Глобальность.
$GLOBALS['CFileCache'] = CFileCache::getInstance();
