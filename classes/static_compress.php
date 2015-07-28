<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/pgq/api/PGQConsumer.php');

if(!defined('COMPRESS_STATIC')) {
    define('COMPRESS_STATIC', false);
}

define('JAVA_PATH', $_SERVER['DOCUMENT_ROOT'] . '/classes/java');

/**
 * Класс для сжатия и кеширования .css и .js файлов
 * 		  
 */
class static_compress {
	
	/**
	 * Максимальное время жизни данных в мемкеше.
	 */
    const GC_LIFE = 14400;
    
    const MEM_LOCK_LIFE = 25; // время блокировки процесса формирования пакета.
    const MEM_BATCHES_VERSION_KEY = 'static_batch_version';
    const STATIC_PATH = '/static'; // (deprecated) папка для хранения сжатых .js и.css на локальном сервере 
    const STATIC_WDPATH = 'wdstatic'; // папка для хранения сжатых .js и.css на WebDAV
    const BEM_SRC_PATH = '/css/block/style.css'; // точка входа в исходники БЭМ.
    const BEM_SRC_PATH_MAIN = '/css/block/style-main.css';
    const BEM_DEST_PATH = '/css/block/_compressed.css'; // конечный (собранный в один) файл БЕМ-стилей.
    const MAX_CSSSIZE_IE = 240; // максимальный размер .css файла для IE в килобайтах.

    /**
     * Разделитель имен файлов в $seed (раскодированное base64 содержимое параметра ?t).
     */
    const SEED_SEP = ':';

    /**
     * Индекс типа файла CSS.
     */
    const TYPE_CSS = 0;

    /**
     * Индекс типа файла JS.
     */
    const TYPE_JS  = 1;
    
    
    /**
     * Индекс файла JS обернутого в php
     */
    const TYPE_PHP_JS = 2;
    
    /**
     * Индекс типа файла JS который необходимо выдать в кодировке UTF-8.
     */
    const TYPE_JS_UTF8  = 3;
    
    /**
     * Служит для проверки, был ли добавлен файл в любом из экземпляров класса, чтобы исключить дубли.
     * @var array
     */
    private static $_allAddedFiles = array();
    
    /**
     * Типы файлов.
     * 
     * @var array
     */
    public $types = array( self::TYPE_CSS => "css",
                           self::TYPE_JS => "js",
                           self::TYPE_PHP_JS => "js",
                           self::TYPE_JS_UTF8 => "js"
                          );

    public $isMSIE = false;


    /**
     *  Массивы имен файлов (от корня), индексированные типом файлов.
     * 
     * @var array
     */
    private $files;
	
	/**
	 * Переменная для подключения мемкеша
	 * 
	 * @var memBuff
	 */
	private $memBuff;
	
	/**
	 * Надо ли жать файлы
	 * 
	 * @var memBuff
	 */
	private $enabled;

    /**
     *
     * @var log 
     */
    private $_log;

    private $_cssSize = 0;
    private $_addWorker;

    private $_batches;
 
 /**
  * Массивы с временем изменения файлов, индексированные типом.
  * 
  * @var array 
  */
 private $mtime;

 private $_root;
	
	/**
	 * Конструктор. Инициализация переменных
	 */
	function static_compress($enabled = COMPRESS_STATIC, $options = array()){
        if (isset($options['bem']) && $options['bem']) {
            $this->bem_src_path = $options['bem'];
        } else {
            $this->bem_src_path = self::BEM_SRC_PATH;
        }
        $this->_root = $options['root'];
		$this->memBuff = new memBuff();
		$this->enabled = $enabled;
        $this->_log = new log('static/'.SERVER.'-%d.log');
        $this->_log->linePrefix = '%d.%m.%Y %H:%M:%S : ' . str_pad(getRemoteIP(),15) . ' ';
        $this->isMSIE = stripos($_SERVER['HTTP_USER_AGENT'], 'msie ') !== false;
	}

	function root($file) {
	    if($file=trim($file)) {
  	        if(strpos($file, '/static') === 0) {
  	            $root = $this->_root;
            } else {
      	        $root = ABS_PATH;
      	    }
  	    }
  	    return $root.$file;
	}
	
	/**
     * Добавление нового файла (стилей, ява-скрипта, др) перед вызовом static_compress::send().
     * @see static_compress::send()
	 * 
     * @param string $fname   путь к файлу.
	 */
    function add($fname, $utf8 = false) {
        if($this->isAdded($fname)) {
            return;
        }

        if (strstr($fname, '.php') !== false && preg_match('/(kword_js|kword_search_js|professions_js|cities_js|tservices_categories_js)\.php/', $fname)) {
            $this->_add(self::TYPE_PHP_JS, $fname);
        }
        
        else if (strstr($fname, '.js') !== false) {
            $this->_add($utf8 ? self::TYPE_JS_UTF8 : self::TYPE_JS, $fname);
        }

        else if (strstr($fname, '.css') !== false) {
            if($this->isMSIE && COMPRESS_STATIC) {
                if(!$this->_addWorker) {
                    $fsize = @filesize($this->root($fname));
                    $maxsize = self::MAX_CSSSIZE_IE * 1024;
                    if($fsize > $maxsize) {
                        $this->_log->writeln("ERROR! {$fname} size is {$fsize} bytes (limit is $maxsize)");
                        if(!is_release()) {
                            die("static_compress: ERROR! {$fname} size is {$fsize} bytes (limit is $maxsize). Необходимо разбить файл на более мелкие.");
                        }
                    }
                    if($this->_cssSize && $this->_cssSize + $fsize > $maxsize) {
                        $this->_addWorker = new static_compress($this->enabled);
                    }
                    $this->_cssSize += $fsize;
                }
                if($this->_addWorker) {
                    return $this->_addWorker->add($fname);
                }
            }
            $this->_add(self::TYPE_CSS, $fname);
        }
    }

    /**
     * Регистрирует файл в массивах класса.
     *
     * @param int $type   тип файла.
     * @param string $fname   путь к файлу.
     */
    private function _add($type, $fname) {
        $this->files[$type][] = $fname;
        static_compress::$_allAddedFiles[$fname] = 1;
    }

    /**
     * Проверяет, был ли добавлен файл в любом из экземпляров класса.
     * @param string $fname   путь к файлу.
     * @return boolean
     */
    function isAdded($fname) {
        return isset(static_compress::$_allAddedFiles[$fname]);
    }

    /**
     * Формирует параметр ?t по типу файлов и их именам.
     * 
     * @param int $type   тип файла.
     * @return string   закодированная строка.
     */
    private function _encodeSeed($type) {
        return base64_encode($type . self::SEED_SEP . $this->_root . self::SEED_SEP . implode(self::SEED_SEP, $this->files[$type]));
    }

    /**
     * Раскодирует $seed (параметр ?t). Инициализирует $this->files.
     * @see static_compress::_encodeSeed()
     * 
     * @param string $seed   закодировнная строка.
     * @param boolean $seed_expired   флаг устаревшего $seed (например, запрашивается уже удаленный файл).
     * @return integer|boolean $type   тип файла или FALSE.
     */
    private function _decodeSeed($seed, &$seed_expired = false) {
        $seed_expired = false;
        $arr = explode(self::SEED_SEP, base64_decode($seed));
        $type = array_shift($arr);
        $this->_root = array_shift($arr);
        $parse = array();
        if($this->types[$type]) {
            $this->files[$type] = array();
            foreach($arr as $file) {
                if($type == self::TYPE_PHP_JS) {
                    $parse = parse_url($file);
                    $file = $parse['path'];
                }

                if (strpos($file, '../') !== false
                   || !(preg_match('/\.(css|js)$/', $file) || 
                        preg_match('/^\/(kword_js|professions_js|cities_js|kword_search_js|tservices_categories_js)\.php$/',$file))
                   || !file_exists($this->root($file))
                  )
                {
                	$this->_log->writeln("Error. File not exists or wrong path: " . $this->root($file));
                    $seed_expired = true;
                    continue;
                }
                $this->files[$type][] = $file . (isset($parse['query']) && $parse['query'] ? '?'.$parse['query'] : "");
            }
            if($this->files[$type])
                return $type;
        }
        return false;
    }
    
    /**
     * Получаем инфо о пакетах из кэша.
     * Что делать если кэш отвалился? 
     * @todo сделать альтернативу в БД.
     *
     * @return array
     */ 
    function getBatchesInfo() {
        if (!$this->_batches) {
            if (!$this->_batches = $this->memBuff->get(self::MEM_BATCHES_VERSION_KEY)) {
                $this->_batches = array(
                    'version'   => time(),                  // версия кэша
                    'batches'   => array(),                 // массив с версиями сборок (id сборки => версия кэша)
                    'bem_count' => 0                        // кол-во получившихся БЭМ-файлов (для IE).
                );
            }
        }
        return $this->_batches;
    }

    /**
     * Сохраняет инфо о пакетах в мемкэш.
     * @param array $batches   см. getBatchesInfo()
     */ 
    function setBatchesInfo($batches) {
        $this->_batches = NULL;
        if($this->memBuff->set(self::MEM_BATCHES_VERSION_KEY, $batches, 0)) {
            $this->_batches = $batches;
        }
        return !!$this->_batches;
    }
    
    
    /**
     * Получает версию конкретного пакета. При необходимости кэширует в общий массив, чтобы не делать лишних обращений в мемкэш.
     *
     * @param integer $batch_id   ид. пакета.
     * @return integer   версия.
     */ 
    function getBatchVersion($batch_id) {
        $batch_version = (int)$this->_batches[$batch_id];
        if($batch_version < $this->_batches['version']) {
            $batch_version = (int)$this->memBuff->get(self::MEM_BATCHES_VERSION_KEY.$batch_id);
            if($batch_version >= $this->_batches['version']) { // пакет сформирован.
                $this->_batches[$batch_id] = $batch_version;
                $this->setBatchesInfo($this->_batches);
            }
        }
        return $batch_version;
    }

    /**
     * Сохраняет версию конкретного пакета. Дополнительно кэширует в общий массив,
     * чтобы не делать лишних обращений в мемкэш.
     *
     * @param integer $batch_id   ид. пакета.
     * @param integer $batch_version   версия.
     * @return boolean   успешно?
     */ 
    function setBatchVersion($batch_id, $batch_version) {
        if($this->memBuff->set(self::MEM_BATCHES_VERSION_KEY.$batch_id, $batch_version, 0)) {
            $batches = $this->getBatchesInfo();
            $batches[$batch_id] = $batch_version;
            $this->setBatchesInfo($batches); // тут есть вероятность того, что параллельный скрипт затрет данные, тогда запись будет в getBatchVersion()
            return true;
        }
        return false;
    }

    
    /**
     * Обновляет версию статики глобально.
     * После чего файлы формируются заново.
     * Запускается автоматом (при коммите на бете/альфе, при синхе на релизе)
     * @todo сделать чистку от старых файлов.
     *
     * @param integer $version   версия (timestamp), если NULL, то текущее время.
     */
    function updateBatchesVersion($version = NULL) {
        $log = $this->_log;
        $ret = 0;
        $version = $version === NULL ? time() : $version;
        $log->writeln("update batches version to {$version}");
        $batches = $this->getBatchesInfo();
        $batches['version'] = $version;
        $bcnt = $this->createBemBatchFiles();
        $ret |= !$bcnt;
        $batches['bem_count'] = $bcnt;
        if(!$this->setBatchesInfo($batches)) {
            $log->writeln("ERROR: failed to save batch info!\n");
            $ret |= 2;
        } else {
            $log->writeln("ok\n");
        }
        return $ret;
    }

    /**
     * Формирует имя лок-файла
     * @see static_compressor::_lock()
     * @deprecated
     *
     * @param string $batch_id    ид. пакета (md5 имен файлов, см. send()) 
     * @param string $type        тип файлов в пакете
     * @return boolean   ок?
     */ 
    private function _lfname($batch_id, $type) {
        return "/{$batch_id}.{$this->types[$type]}.lock";
    }

    /**
     * Блокирует пакет от других процессов.
     * info: LOCK_EX не катит на боевой, т.к. все процессы один и тот же экземпляр используют, поэтому такой способ.
     *       (есть еще метод с мемкэшем.)
     * @see static_compressor::send()
     * @deprecated
     *
     * @param string $batch_id    ид. пакета (md5 имен файлов, см. send()) 
     * @param string $type        тип файлов в пакете
     * @return boolean   ок?
     */ 
    private function _lock($batch_id, $type) {
        // info: LOCK_EX не катит на боевой, т.к. все процессы один и тот же экземпляр используют.
        $this->_lock = NULL;
        $lfname = $this->_lfname($batch_id, $type);
        if($f = @fopen($lfname, 'x')) {
            $this->_lock = array($f, $lfname);
        }
        return !!$this->_lock;
    }

    /**
     * Проверяет, заблокирован ли пакет. Если процесс натыкается на блок, то вместо пакетного файла получает sendUncompress().
     * @see static_compressor::send()
     * @deprecated
     *
     * @param string $batch_id    ид. пакета (md5 имен файлов, см. send()) 
     * @param string $type        тип файлов в пакете
     * @return boolean   ок?
     */ 
    function _islock($batch_id, $type) {
        return file_exists($this->_lfname($batch_id, $type));
    }

    /**
     * Снимает блок с последнего заблокированного пакета.
     * @see static_compressor::_lock()
     * @deprecated
     * @return boolean   ок?
     */ 
    private function _unlock() {
        if($this->_lock) {
            fclose($this->_lock[0]);
            if($ok = unlink($this->_lock[1]))
                $this->_lock = NULL;
        }
        return !$this->_lock;
    }
    
    /**
     * Задает имя ключа для блокировки процесса генерации пакета.
     * @param string $batch_id   ид (хеш) пакета
     * @param string $batch_version   текущая версия статики, блок действует только в этой версии.
     * @return string
     */ 
    private function _createBatchLockKey($batch_id, $batch_version) {
        return md5($batch_id.$batch_version.'.lock');
    }

    /**
     * Печатает html-блоки (<script ...>, <link ...>, др.) для обращения к статическому контенту.
     * Собирает статику каждого типа (js|css) в один файл, вида:
     * %batchid%_%version%.%type%
     * который сохраняет на сервере. При следующем обращении, если версия не устарела, то просто печатаем тег с адресом этого файла.
     * Если версия устарела, то сначала генерируем файл заново. При этом блокируем запись файла от других процессов.
     * Если процесс натыкается на блок, то выдаем ему статику "online" -- через static_compressor::output().
     */
    function send() {
        global $DB;
        
        if (!$this->enabled)
            return $this->sendUncomressed();

        $log = $this->_log;
        $this->getBatchesInfo();

        foreach($this->types as $type=>$name) {
            if(!$this->files[$type]) continue;

            $batch_id = md5(implode(self::SEED_SEP, $this->files[$type]));
            $batch_version = $this->getBatchVersion($batch_id);
                
            $ext = $this->types[$type];
            $filename = self::STATIC_WDPATH . '/' . $this->createFileName($batch_id, $batch_version, $ext);
            $fileurl = WDCPREFIX . '/';
            $expired = false;
            $file_not_exists = false;
            $batch_locked = 0;
            
            if ( $expired = $batch_version < $this->_batches['version'] ) {
                if(isset($_SERVER['REQUEST_URI'])) {
                    // $log->writeln("ref: {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}");
                }
                
                $old_filename = $filename;
                $old_batch_version = $batch_version;
                
                // $log->writeln("batch file {$filename} expired");

                $lock_key = $this->_createBatchLockKey($batch_id, $this->_batches['version']);
                // $this->memBuff->delete($lock_key);
                if( !$batch_locked && !($batch_locked = $this->memBuff->get($lock_key)) ) {
                    $log->writeln('lock not exist, try set it...');
                    if($batch_locked = !$this->memBuff->add($lock_key, 1, self::MEM_LOCK_LIFE)) {
                        $log->writeln('lock already added');
                    } else if($batch_locked = !$this->memBuff->set($lock_key, 1, self::MEM_LOCK_LIFE)) {  // какая-то фигня с add(), но зато блокирует другие add().
                        $log->writeln('lock setting failed');
                    }
                }

                if( !$batch_locked ) { // т.е. именно этот процесс будет формировать пакет.
                    $lock_cnt = (int)$this->memBuff->get($lock_key.'.counter');
                    if( $lock_cnt > 0
                        || !$DB->query("SELECT pgq.insert_event('share', 'static_compress.createBatchBySeed', ?)",
                                       'seed='.$this->_encodeSeed($type)) )
                    {
                        // Сжимаем рантайм (но лайт):
                        // а) если прошло уже достаточно времени для формирования пакета, но его так и нет -- считаем, что отвалился pgq
                        //    (один $lock_cnt значит, что прошло self::MEM_LOCK_LIFE секунд);
                        // б) в случае, если pgq сработал, но возникла ошибка в createBatch() (например, при сжатии);
                        // в) в случае неудачи инсерта в очередь.
                        $batch_locked = $this->_createBatch($type, $batch_id, $this->_batches['version'], $filename, true);
                    } else {
                        $batch_locked = 100; // просто отдадим пока старую версию.
                    }
                    
                    $this->memBuff->set($lock_key.'.counter', $lock_cnt + 1, self::MEM_LOCK_LIFE * 10);
                }
                
                if( $batch_locked ) {
                    if($old_batch_version) { // старый файл точно есть.
                        // 1. Отдаем старую версию.
                        // $log->writeln("sending old version: batch file {$filename} is locked/failed ($batch_locked)\n");
                        $filename = $old_filename;
                    } else {
                        // 2. Если использовать только этот вариант, то он жутко грузит апачи при перегенерации.
                        // Поэтому только в случае отсутствия старого файла.
                        // $log->writeln("sending uncompressed: batch file {$filename} is locked/failed ($batch_locked)\n");
                        $filename = '/static.php?t='.$this->_encodeSeed($type);
                        $fileurl = '';
                    }
                    
                    // 3. Либо такой вариант. Но в таком случае рискуем выдать юзеру серверный устаревший кэш. 
                    // 08.2012: уже не катит совсем из-за IE+БЭМ.
                    // $this->sendUncomressed($type, $this->_batches['version']);
                    // continue;
                }
                // $log->write("\n");
            }

            $this->printTags($fileurl.$filename, $type);
        }

        if($this->_addWorker) {
            $this->_addWorker->send();
        }
    }
    
    /**
     * Определяем кодировку по типам
     * 
     * @param integer $type    Тип файла
     * @return string
     */
    public static function getCharsetType($type) {
        switch($type) {
            case self::TYPE_JS:
            case self::TYPE_CSS:
            case self::TYPE_PHP_JS:
                return 'windows-1251';
                break;
            case self::TYPE_JS_UTF8:
                return 'utf-8';
                break;
        }
    }

    /**
     * Печатает html-блоки для запроса несжатого контента.
     */
    function sendUncomressed($onlytype = -1, $version = NULL) {
        if(!$version) {
            $version = $GLOBALS['RELEASE_VERSION'];
        }
        foreach($this->types as $type=>$name) {
            if($this->files[$type] && ($onlytype == -1 || $type == $onlytype)) {
                foreach ($this->files[$type] as $file)
                    $this->printTags($file, $type, $version);
            }
        }
    }

    /**
     * Печатает содержимое файлов, при запросе /static.php?t=$seed.
     * Сжимает содержимое, контролирует кэширование на сервере и клиенте.
     *
     * @param string $seed   закодировнная строка (параметр ?t).
     */
    function output($seed) {
        $log = $this->_log;
        if(($type = $this->_decodeSeed($seed, $seed_expired)) === false) {
            $log->writeln("\n\nstatic_compressor::output()\n");
            $log->writeln("Error _decodeSeed - seed:{$seed}\n\n");
            exit;
        }
        if($seed_expired)
            $seed = $this->_encodeSeed($type);
        $last_mod = $this->getLastModified($type);
        $mem_key = md5('static_compress.output'.$seed);
        $mem_data = $this->memBuff->get($mem_key);
        if(!$mem_data || $last_mod != $mem_data['last_mod']) {
            $mem_data['body'] = $this->_compress($type, true);
            $mem_data['etag'] = '"' . md5($mem_data['body']) . '"';
            $mem_data['last_mod'] = $last_mod;
            $mem_data['length'] = strlen($mem_data['body']);
            $this->memBuff->set($mem_key, $mem_data, self::GC_LIFE);
        }
        header('Content-Type: text/' . ($this->types[$type]=='js' ? 'javascript' : 'css') . '; charset=' . self::getCharsetType($type));
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('ETag: ' . $mem_data['etag']);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mem_data['last_mod']). ' GMT');
        if( isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $mem_data['etag']
            && (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $mem_data['last_mod']) )
        {
            header('HTTP/1.1 304 Not Modified');
            $mem_data['length'] = 0;
            $mem_data['body'] = NULL;
        }
        header('Content-Length: ' . $mem_data['length']);
        exit($mem_data['body']);
    }

    /**
     * Получает время последнего изменения текущей группы файлов заданного типа.
     *
     * @param int $type   тип файлов.
     * @return int   время изменения.
     */
    function getLastModified($type) {
        $lastmod = 0;
        if($this->files[$type]) {
            foreach ($this->files[$type] as $file) {
                $lastmod = max($lastmod, @filemtime($this->root($file))); // @ - #0015499 stat failed
            }
        }
        return $lastmod;
    }
    
    /**
     * Создает пакет по $seed (список имен файлов). Вызывается асинхронно через PgQ.
     *
     * @param string $seed   закодированный список имен файлов (см. self::_encodeSeed()).
     * @param boolean $light   true, если сжимаем по облегченному варианту (сжатие быстрое, но не полное).
     * @return int   код ошибки или 0.
     */
    function createBatchBySeed($seed, $light = false) {
        $type = $this->_decodeSeed($seed, $seed_expired);
        $this->getBatchesInfo();
        $batch_version = $this->_batches['version'];
        $batch_id = md5(implode(self::SEED_SEP, $this->files[$type]));
        return $this->_createBatch($type, $batch_id, $batch_version, $filename, $light);
    }
    
    /**
     * Создает пакет.
     *
     * @param integer $type   тип пакета (TYPE_CSS|TYPE_JS|TYPE_PHP_JS|TYPE_JS_UTF8)
     * @param string $batch_id   ид. (хеш) пакета
     * @param string $batch_version   устанавливаемая версия пакета (текущая версия всей статики).
     * @param string $filename   вернется имя файла пакета
     * @param boolean $light   true, если сжимаем по облегченному варианту (сжатие быстрое, но не полное).
     * @return int   код ошибки или 0.
     */
    private function _createBatch($type, $batch_id, $batch_version, &$filename, $light = false) {
        $log = $this->_log;
        $err = 0;
        $cfile = new CFile();
        $filename = self::STATIC_WDPATH . '/' . $this->createFileName($batch_id, $batch_version, $this->types[$type]);
        $lock_key = $this->_createBatchLockKey($batch_id, $batch_version);

        if( !$light || !$cfile->CheckPath($filename, false) ) { // проверка на случай, если pgq переполнится.
            $log->writeln("creating new batch file {$filename}, compressing content...");
            if($content = $this->_compress($type, $light)) {
                $cfile->exclude_reserved_wdc = true;
                if($cfile->putContent($filename, $content)) {
                    $log->writeln('saving batch info to memcached...');
                    if(!$this->setBatchVersion($batch_id, $batch_version)) {
                        $err = 3;
                    }
                } else {
                    $err = 2;
                }
            } else {
                $err = 1;
            }
        }
        
        if($err) {
            $log->writeln("failed ({$err})");
            $filename = NULL;
        }
        
        $log->writeln('unset lock...');
        $ok = $this->memBuff->delete($lock_key);
        $log->writeln($ok ? 'ok' : 'failed');
        
        return $err;
    }
    

    /**
     * Сжимает содержимое файлов по заданному типу.
     *
     * @param int $type   тип файлов.
     * @param boolean $light   true, если сжимаем по облегченному варианту (сжатие быстрое, но не полное).
     * @return string   сжатый и объединенный контент.
     */
    private function _compress($type, $light = false) {
        if($func = $this->_getTypeFunc('_compress', $type))
            return $this->$func($light, self::getCharsetType($type));
    }
	
	/**
	 * Сжимает ява-скрипт
	 * 
     * @param boolean $light   true, если сжимаем по облегченному варианту (сжатие быстрое, но не полное).
	 * @return string	сжатый и объединенный файл скриптов
	 */
    private function _compressJs($light = false, $charset = 'windows-1251') {
        if($charset == 'windows-1251') {
            return $this->compressJsFiles($this->files[self::TYPE_JS], NULL, $light, $charset);
        } else {
            return $this->compressJsFiles($this->files[self::TYPE_JS_UTF8], NULL, $light, $charset);
        }
	}
	

    /**
     * Возвращает сжатое содержимое переданных файлов.
     * 
     * @param array $files   массив имен файлов (с путями от корня).
     * @param string $root   путь до корня.
     * @param boolean $light   true, если сжимаем по облегченному варианту (сжатие быстрое, но не полное).
     * @return string   сжатый js.
     */
    function compressJsFiles($files, $root = NULL, $light = false, $charset='windows-1251') {
        $out = '';
        if($light) {
            foreach($files as $file) {
                $contents = @file_get_contents($root ? $root.$file : $this->root($file));
                $out .= preg_replace('/([\r\n]){2,}/', '$1', $contents)."\n";
            }
        }
        else {
            $js = '';
            foreach($files as $file) {
                $js .= ' --js '.escapeshellarg($root ? $root.$file : $this->root($file));
            }
            $cmd = 'java -jar ' . JAVA_PATH . "/compiler.jar --language_in ECMASCRIPT5 --charset {$charset} {$js}";
            ob_start();
            system($cmd);
            $out = ob_get_clean();
            if(!$out) {
                $this->_log->writeln("compress failed: {$cmd}");
            }
        }
        return trim($out);
	}

	/**
     * Сжимает ява-скрипт
     * 
     * @return string	сжатый и объединенный файл скриптов
     */
    private function _compressPHP($light = false) {
        $out = '';
        $files = array();
        $tmp_path = '/var/tmp/static/';
        foreach ($this->files[self::TYPE_PHP_JS] as $file) {
            $parse = parse_url($file);
            $exp = explode('=', $parse['query']);
            $_GET[$exp[0]] = $exp[1];
            ob_start();
            //@todo: уязвимость при подключении через static.php
            //необходимо в первую очередь избавиться от его использования
            //include($this->root($parse['path']));
            $contents = ob_get_clean();
            if($light) {
                $out .= preg_replace('/([\r\n]){2,}/', '$1', $contents)."\n";
            } else {
                $tmp_js = basename($parse['path']).'.'.md5($parse['query']).'.tmp.js';
                file_put_contents($tmp_path.$tmp_js, $contents);
                $files[] = $tmp_js;
            }
        }
        return $light ? trim($out) : $this->compressJsFiles($files, $tmp_path, $light);
    }
	
	/**
	 * Сжимает файлы стилей
	 * 
	 * @return string	сжатый и объединенный файл стилей	
	 */
    private function _compressCss() {
        $out='';
        foreach($this->files[self::TYPE_CSS] as $file){
            //$out .= exec('java -jar ' . JAVA_PATH . '/yuicompressor-2.4.7.jar --charset=windows-1251 ' . $this->root($file));
            //continue;
			$contents = file_get_contents($this->root($file));
            $this->compressCssContent($contents);
			$out .= $contents;
		}
        return trim($out);
	}


    function compressCssContent(&$contents) {
        $contents = preg_replace('~/\*.*\*/~Uis', '', $contents);
        $contents = preg_replace('/[\r\n]+/', '', $contents);
        $contents = preg_replace('/\s{2,}/', ' ', $contents);
        $contents = preg_replace('~\s([}{;:+=/,])~', '$1', $contents);
        $contents = preg_replace('~([}{;:+=/,])\s~', '$1', $contents);
        return $contents;
    }
	

    /**
     * Печатает html-блок, запрашивающий контент файла по заданному типу.
     *
     * @param string $file   имя файла.
     * @param string $type   тип файла.
     */
    function printTags($file, $type, $version = NULL) {
        if($func = $this->_getTypeFunc('printTags', $type)) {
            $file = $file . ($version ? "?v={$version}" : '');
            $this->$func($file, self::getCharsetType($type));
        }
    }
	
    /**
     * Выводит тэг HTML подключения CSS файла
     * 
     * @param string $file путь к файлу
     */
    function printTagsCss($file, $charset) {
        print "<link type=\"text/css\" href=\"{$file}\" rel=\"stylesheet\" charset=\"{$charset}\"/>\n";
	}
	
	/**
     * Выводит тэг HTML подключения JS файла
     * 
     * @param string $file путь к файлу
     */
    function printTagsJs($file, $charset){
        print "<script type=\"text/javascript\" src=\"{$file}\" charset=\"{$charset}\"></script>\n";
	}
	
	/**
     * Выводит тэг HTML подключения JS файла обернутого через PHP
     * 
     * @param string $file путь к файлу
     */
    function printTagsPHP($file, $charset){
        print "<script type=\"text/javascript\" src=\"{$file}\" charset=\"{$charset}\"></script>\n";
	}


    /**
     * Возвращает имя метода данного класса, для заданного типа файлов.
     * 
     * @param string $pfx   префикс метода.
     * @param int $type   тип файла.
     * @return string   имя метода.
     */
    private function _getTypeFunc($pfx, $type) {
        $func = $pfx . ucwords($this->types[$type]);
        if($type == self::TYPE_PHP_JS ) $func = $pfx."PHP";
        if(method_exists($this, $func))
            return $func;
        return NULL;
    }
    
    /**
     * Функция собирает из всех CSS иденое целое (заменяет все импорты на содержимое файла)
     *
     * @param string $path_style Путь до файла стилей (вида - "/css/block/style.css" - Обязательный слеш в начале пути)
     * @param boolean $unique Выдать сразу уникальный контент (удаляются повторяющиеся классы) 
     * @return strine Чистый CSS без @import 
     */
    public function collectBem($path_style, $unique = false) {
        $glob_dir =  dirname($path_style);
        $path = $_SERVER['DOCUMENT_ROOT'].$path_style;
        $css  = file_get_contents($path);
        $exp = explode("/", $path_style);
        foreach($exp as $i=>$k) if($k == ".." && isset($exp[$i-1])) unset($exp[$i-1], $exp[$i]);
        $dir = dirname(implode("/", $exp));
        $css = preg_replace("/url\(((?!(data:))[^\"|^\/].*?)\)/mix", "url(\"{$dir}/$1\")", $css);
        $css = preg_replace("/@import\s*url\(\"(.*?)\"\);/mix", "@import url(\"$glob_dir/$1\");", $css); // заменяем все пути на полные
        $css = preg_replace_callback("/(@import\s*url\(\"(.*?)\"\);)/mix", create_function('$matches','return static_compress::collectBem($matches[2]);'), $css); // заменяем импорт на файл
        return $unique?self::getUniqueBemSource($css):$css;
    }
    
    /**
     * Чистим содержимое CSS от дублирубщих классов
     *
     * @param string $css_source    Содержимое CSS  
     * @return string Результат чистки 
     */
    public function getUniqueBemSource($css_source) {
        // Удалеям из текста все лишнее, что будет мешать при поиске в регулярке
        $css_source = str_replace(array("\r", "\n"), "", $css_source);
        $css_source = str_replace(array("\t"), " ", $css_source);
        $css_source = trim(preg_replace("/\/\*.*?\*\//", "", $css_source));
        if($css_source == "") return false;
        return $css_source; // 0024809

        if(preg_match_all("/(\s*(@media)?.+?)\{(.*?)(?(2)\}\s*?\}|\})/mix", $css_source, $matches)) {
            foreach($matches[1] as $u=>$value) {
                $value = trim($value);
                if(isset($result[$value])) {
                    if(strpos($value, '@media') !== false) {
                        if($result[$value] != trim($matches[3][$u])) {
                            $result[$value][] = trim($matches[3][$u]);
                        }
                    } else {
                        $style  = implode(";", array_map("trim", $result[$value]));
                        $rstyle = implode(";", array_map("trim", explode(";", trim($matches[3][$u]))));
                        if($style != $rstyle) {
                            $result[trim($value)] = explode(";", trim($style.$rstyle));
                        }
                    }
                } else {
                    if(strpos($value, '@media') !== false) {
                        $result[$value][] = trim($matches[3][$u]);
                    } else {
                        $result[$value] = explode(";", trim($matches[3][$u]));
                    }
                }
            }
            $css = "";
            foreach($result as $name=>$style) {
                if(strpos($name, '@media') !== false) {
                    $style = $name . "{\r". implode("}\r", $style) . "}\r}\r";
                    $css  .= $style;
                } else {
                    $style = $name . "{\r". implode(";\r", $style)."}\r";
                    $css  .= $style;
                }
            }
            return $css; 
        }
        return false;   
    }

    /**
     * Сохраняет весь БЭМ-контент в файл(ы). Это не конечные файлы, они будут подключены через метод Add() и сжаты в один (или несколько для IE).
     * @see static_compress::collectBem()
     *
     * @param boolean $unique   для collectBem(): Выдать сразу уникальный контент (удаляются повторяющиеся классы) 
     * @return integer   возвращает кол-во получившихся файлов.
     */
    function createBemBatchFiles($unique = true) {
        $this->_log->writeln('generating BEM batch file...');
        $cnt = 0;
        if($content = $this->collectBem($this->bem_src_path, $unique)) {
            $this->compressCssContent($content);
            $parts = array();
            $maxsize = self::MAX_CSSSIZE_IE * 1024;
            while( ($lpos = @strpos($content, '}', $maxsize)) !== false ) {
                $rpos = strrpos(substr($content, 0, $lpos), '}');
                $parts[] = substr($content, 0, $rpos + 1);
                $content = substr($content, $rpos + 1);
            }
            $parts[] = $content;
            ob_start();
            foreach($parts as $part) {
                $pname = $this->bemFilePath($cnt);
                if(!file_put_contents($pname, $part, LOCK_EX)) {
                    break;
                } else if(!$_SERVER['REQUEST_METHOD']) {
                    @chmod($pname, 0666);
                }
                $cnt++;
            }
            // Удаляем старые файлы, если остались, чтобы потом не подключить лишнее (см. addBem())
            $i = $cnt;
            while(file_exists($this->bemFilePath($i))) {
                unlink($this->bemFilePath($i++));
            }
            $err = ob_get_clean();
        } else {
            $err = 'content is empty';
        }
        
        if(!$cnt && $err) {
            $this->_log->writeln("WARNING: bem failed: {$err}");
        } else {
            $this->_log->writeln('ok');
        }
        
        return $cnt;
    }

    /**
     * Добавляет суффикс к имени бэм-файла в зависимости от номера файла в пакете.
     *
     * @param intger $num   номер файла
     * @return string   готовое имя файла
     */
    function bemFilePath($num = 0, $abs = true) {
        $sfx = $num ? '-'.$num : '';
        $pfx = $abs ? preg_replace('~[\\\/]$~', '', ABS_PATH) : '';
        return $pfx.str_replace('.css', $sfx.'.css', self::BEM_DEST_PATH);
    }

    /**
     * Метод add() для БЭМ. Специфика в том, что бэм может быть разбит автоматически на несколько файлов.
     * @see static_compress::createBemBatchFiles()
     * @see static_compress::add()
     */
    function addBem() {
        if(!$this->enabled) {
            return $this->add($this->bem_src_path);
        }
        $i = 0;
        $this->getBatchesInfo();
        $bcnt = (int)$this->_batches['bem_count'];
        if(!$bcnt && !file_exists($this->bemFilePath())) { // ни разу не выполнился updateBatchesVersion()
            $this->createBemBatchFiles();
        }
        do {
            $this->add($this->bemFilePath($i++, FALSE));
        } while( $i < $bcnt || !$bcnt && file_exists($this->bemFilePath($i)) );
    }
    
    /**
     * Сборщик мусора, удаляем все кроме последней и предыдущей версии 
     * запускается в 6 часов утра каждый день @see hourly.php 
     *
     * @return boolean
     */
    public function cleaner() {
        global $DB;
        
        $path_static = self::STATIC_WDPATH;
        $this->_log->writeln('garbage collecting...');
        
        $dcnt = 0;
        $batches = $this->getBatchesInfo();
        $cur_version = $batches['version'];
        if(!$cur_version) {
            $this->_log->writeln('failed: batch version is undefined');
            return false;
        }
        
        $sql = "SELECT id, fname FROM file WHERE path = '{$path_static}/'";
        if($result = $DB->rows($sql)) { 
            $prev_version = 0;
            foreach($result as $key=>$value) {
                $file_version = $this->parseFileName($value['fname'], 1);
                $result[$key]['version'] = $file_version;
                if($file_version > $prev_version && $file_version < $cur_version) {
                    $prev_version = $file_version;
                }
            }
            $cfile = new CFile();
            foreach($result as $value) {
                if($value['version'] < $prev_version) {
                    $dcnt++;
                    $cfile->Delete($value['id']);
                }
            }
        }
        $this->_log->writeln("ok: {$dcnt} file(s) deleted");
    }
    
    
    /**
     * Разбирает имя пакета на части
     * 
     * @param string $filename    имя файла пакета
     * @param integer $ret_mode   какую составляющую вернуть: -1:все в массиве, 0:ид. пакета (md5), 1:версия пакета, 2:расширение
     * @return mixed
     */
    function parseFileName($filename, $ret_mode = -1) {
        $ret = preg_split('/[_.]/', $filename);
        return $ret_mode < 0 ? $ret : $ret[$ret_mode];
    }
    
    function createFileName($batch_id, $version, $ext) {
        return $batch_id . '_' . $version . '.' . $ext;
    }

}
