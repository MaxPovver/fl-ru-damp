<?php
/**
 * Класс обработки файлов.
 * Библиотека должна работать с WebDav от nginx, а он поддерживает только PUT, DELETE, MKCOL, COPY и MOVE
 */
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/webdav_proxy.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/CFileCache.php');

// !!! найти тикет и добавить сюда '.jpeg': if($exp != 'jpg') $this->name = preg_replace('/\.[^.]+$/', '.jpg', $this->name);

class CFile
{
    /**
     * id файла
     *
     * @var integer
     */
    public $id;
    
    /**
     * текущая таблица файлов (file, file_projects, file_blogs и т.п.)
     * Если используется таблица по умолчанию (т.е. родительский шаблон -- file_template), то все инсерты пойдут в таблицу file (см. триггер), т.к.
     * шаблон _должен_ быть пустым всегда. Все остальные операции будут через него.
     * Желательно всегда указывать нужную таблицу.
     * (note: по умолчанию file_template, а не file, т.к., например, при удалении не всегда известно из какой таблицы удаляем.)
     *
     * @var string
     */
    public $table = 'file_template';

    
    /**
     * имя файла во временной директории
     *
     * @var string
     */
    public $tmp_name;
    
    /**
     * имя файла
     *
     * @var string
     */
    public $name;
    
    /**
     * Дата последнего изменения файла
     *
     * @var string
     */
    public $modified;
    
	/**
     * размер файла в байтах
     *
     * @var integer
     */
    public $size = 0;
    
    /**
     * Параметры графического файла
     *
     * @var array
     */
    public $image_size = array('width'=>0,'height'=>0, 'type' => 0);
    
    /**
     * Путь до файла
     *
     * @var string
     */
    public $path = '';
    
    /**
     * Массив ошибок при работе с файлом
     *
     * @var array
     */
    public $error = array();
    
    /**
     * максимальный размер файла для аплоада
     *
     * @var integer
     */
    public $max_size = 1048576;
    
    /**
     * дополнительное возможное расширения для файла 
     *
     * @var unknown_type
     */
    public $file_ext = "";

    /**
     * Массив с допустимыми расширениями
     *
     * @var array
     */
    public $allowed_ext = array();
    
    /**
     * максимальные размеры картинки для аплоада. Если resize = 0, 'less' = 0 и размеры картинки
     * больше указанных, то выдаст ошибку.
     * prevent_less = 1 - запретить загрузку картинки, если ее размер меньше указанных
     *
     * @var array
     */
    public $max_image_size = array('width'=>0,'height'=>0, 'less' => 0);

    /**
     * путь будет указан относительно корня веб-сервера (если 0, то относительно /upload/users)
     *
     * @var integer
     */
    public $server_root = 0;
    
    /**
     * Надо ли ресайзить картинку(0-нет,1-да)
     *
     * @var integer
     */
    public $resize=0;
    
    /**
     * Если надо ресайзить, то надо ли ресайзить пропорционально или просто сделать картинку $max_image_size (0-нет, 1-да)
     *
     * @var integer
     */
    public $proportional = 0;
    
    /**
     * Если ресайзить пропорционально, то задает цвет бекграунда
     *
     * @var integer:16
     */
    public $background = 0xFFFFFF;
    
    /**
     * Качество картинки при ресайзе (%)
     * Note: 100 от 90 внешне почти не отличается, но при этом часто уменьшенная картинка весит намного больше оригинала,
     *       поэтому 90 по умолчанию.
     *
     * @var integer
     */
    public $quality = 90; 

	/**
     * Флаг проверки антивирусом (4 бита)
     * 1 - файл заражен
     * 2 - невозможно проверить (напр.зашифрованный архив)
     * 3 - ошибка при проверке (нет доступа к файлу и т.д.)
     * 4 - файл проверять не нужно (см. self::$antivirusSkip и self::MoveUploadedFile)
     * NULL - файл не проверялся
     * 0000 - файл не заражен
     *
     * var integer
     */
    public $virus = NULL;

    /**
     * Имя вируса, если файл заражен
     *
     * var string
     */
    public $virusName = '';

    /**
     * Оригинальное имя файла
     *
     * var string
     */
    public $original_name = '';

    /**
     * Идентификатор источника, например, в блогах -- ид. сообщения, содержащего данный файл.
     *
     * @var string
     */
    public $src_id;

    /**
     * Признак того, что файл имеет уменьшенную копию (используется не во всех таблицах).
     *
     * @var mixed
     */
    public $small = 0;

    /**
     * Кол-во ссылок на файл. Если при удалении 0, то файл визически удаляется, иначе уменьшается на 1 это поле и файл не удаляется
     *
     * @var integer
     */
    public $count_links = 0;
    
    /**
     * Признак того, что файл (gif) не должен иметь анимацию, по умолчанию анимация разрешается
     *
     * @var mixed
     */
    public $disable_animate = false;

    /**
     * Расширенний файлов, которые не нужно проверять антивирусом
     *
     * @var array
     */
    public $antivirusSkip = array();

    /**
     * @var boolean $exclude_reserved_wdc   исключить ли резервные сервера (для каких-то временных данных, чтоб не нагружать лишний раз).
     */
    public $exclude_reserved_wdc = false;
    
    /**
     * WebDAV-прокси
     * @var webdav_proxy
     */
    private $_wdp;
    
    /**
     * Если true не удалять временный файл при вызове деструктора
     * @var unlinkOff
     */
    public $unlinkOff;
    
    /**
     * Конструктор. Инициализирует переменные класса по массиву $_FILES, пути до файла или id файла.
     * Путь до файла относительно корня, без первого слеша. Например: users/te/temp/upload/new.jpg
     *
     * @param mixed $file_arr	- элемент массива $_FILES, путь до файла или id файла из таблицы file
     */
    function __construct($file_arr = 0, $table = NULL) {
        if ($table)
            $this->table = $table;
        $this->_wdp = webdav_proxy::getInst($GLOBALS['WDCS']);
        if (is_array($file_arr)) { 
            $this->tmp_name = $file_arr['tmp_name'];
            $this->size = $file_arr['size'];
            $this->name = change_q_x($file_arr['name'], true);
            $this->original_name = change_q_x($file_arr['name'], true);
            if($file_arr['error'] != UPLOAD_ERR_OK) 
            {
              switch($file_arr['error']) {
                case UPLOAD_ERR_FORM_SIZE:
                case UPLOAD_ERR_INI_SIZE:
                  //$this->error[] = "Слишком большой файл ({$file_arr['error']})";
                  $this->error[] = "Слишком большой файл. ";
                  break;
                case UPLOAD_ERR_NO_FILE:
                  $this->error[] = "Выберите файл для загрузки";
                  break;
                default:
                  //$this->error[] = "Невозможно загрузить файл ({$file_arr['error']})";
                  $this->error[] = "Невозможно загрузить файл";
              }
            }
        } elseif ($file_arr) {
            if (strcmp($file_arr,intval($file_arr)) == 0){
                $this->GetInfoById($file_arr);
            }
            else 
                $this->GetInfo($file_arr);
        }
        $this->unlinkOff = false;
    }
    
    /**
     * Деструктор. Уничтожает временный файл ($this->tmp_name), если была загрузка файла.
     *
     */
    function __destruct() {
        if ($this->tmp_name && !$this->unlinkOff) @unlink($this->tmp_name);
    }
    
    /**
     * Инициализирует переменные класса данными из базы по имени файла
     *
     * @param string $file - путь до файла (относительно директории upload)
     */
    function GetInfo($file) {
        if ( !($row = $GLOBALS['CFileCache']->get($file)) ) {
            $rows = CFile::selectFilesByFullName($this->table, $file);
            $row = $rows[0];
        }
        $this->initByRow($row);
    }
    
    /**
     * Инициализирует переменные класса данными из базы по id файла в таблице file
     *
     * @param integer $id - id файла в таблице file
     */
    function GetInfoById($id) {
        if ($id = (int)$id) {
            if ( !($row = $GLOBALS['CFileCache']->get($id)) ) {
                $rows = CFile::selectFilesById($this->table, $id);
                $row = $rows[0];
            }
        }
        $this->initByRow($row);
    }
    
    static function selectFilesBySrc($t_name, $values, $order_by = NULL, $add_where = NULL, $limit = NULL) {
        if(is_array($values)) {
            foreach($values as $k=>$v) { $values[$k] = intval($v); }
        } else {
            $values = intval($values);
        }
        $rows = DB::londiste('INKEYS')->select($t_name, 'src_id', $values, $order_by, $add_where, $limit);
        $GLOBALS['CFileCache']->put($rows);
        return $rows;
    }

    static function selectFilesById($t_name, $values, $order_by = NULL, $add_where = NULL, $limit = NULL) {
        if(is_array($values)) {
            foreach($values as $k=>$v) { $values[$k] = intval($v); }
        } else {
            $values = intval($values);
        }
        $rows = DB::londiste('INKEYS')->select($t_name, 'id', $values, $order_by, $add_where, $limit);
        $GLOBALS['CFileCache']->put($rows);
        return $rows;
    }
    
    static function selectFilesByFullName($t_name, $values, $order_by = NULL, $add_where = NULL, $limit = NULL) {
        $rows = DB::londiste('INKEYS')->select($t_name, '(path||fname)', $values, $order_by, $add_where, $limit);
        $GLOBALS['CFileCache']->put($rows);
        return $rows;
    }
    
    /**
     * Инициализирует переменные класса данными строки полученной из базы
     *
     * @param array $row   запись из таблицы файлов.
     */
    function initByRow($row) {
        if (!$row) return;
        $this->size = $row['size'];
        $this->image_size = array('width'=>$row['width'],'height'=>$row['height'], 'type' => $row['ftype']);
        $this->name = $row['fname'];
        $this->original_name = $row['original_name'] ? $row['original_name'] : $row['fname'];
        $this->path = $row['path'];
        $this->id = $row['id'];
        $this->virus = is_null($row['virus']) ? $row['virus'] : bindec($row['virus']);
        $this->virusName = $row['virus_name'];
        $this->modified = $row['modified'];
        $this->count_links = $row['count_links'];
        $this->src_id = @$row['src_id'];
    }
    
    /**
     * Возвращает расширение файла
     *
     * @param string $fname		имя файла. Если не задано, используется $this->name
     * @return string			Расширение файла. Например jpg 
     */
    function getext($fname = ''){
        if ($fname == '' && $this) $fname = $this->name;
        $filename = preg_split("/[.]+/",$fname);
        if(count($filename)==1) {
            $ext = 'dat';
        } else {
            $ext = strtolower(array_pop($filename));
        }
        return $ext;
    }
    /**
     * проверяет, не содержит ли расширение файла кириллические символы.
     * если содержит - то расширение заменяется на .dat
     */
    function cyrillicExtension ($ext) {
        return preg_match('/[а-яА-Я]/', $ext) ? "dat" : $ext;
    }
    
    
    /**
     * Проверка названия файла (чтобы название файла не совпало)
     *
     * @param string $dir      Директория проверки
     * @param string $postfix  Постфик (добавляется в конце названия файла)
     * @param string $prefix   Префикс (добавляется в начале названия файла)
     * @return string Новое имя файла
     */
    function secure_tmpname($dir = null, $postfix = '.temp', $prefix = 'f_') {
        // validate arguments
        if (! (isset($postfix) && is_string($postfix))) {
            return false;
        }
        if (! (isset($prefix) && is_string($prefix))) {
            return false;
        }
        if (! isset($dir)) {
            return false;
        }
        
        $new_name = $dir . uniqid($prefix . str_pad(mt_rand(0,999), 3, '0', STR_PAD_LEFT)) . $postfix;
        return $new_name;
    }
    /**
     * Перемещает загруженный файл в новое место
     *
     * @param string  $dir Папка закачки
     * @param boolean $virusScan Если TRUE, то проверять на вирусы
     * @param string  $destFileName Если не пусто, то сохранить файл с этим именем
     * @return string Название закаченного файла
     */
    function MoveUploadedFile($dir, $virusScan=TRUE, $destFileName = ''){
        if (@$this->error[0]) return NULL;
        $this->path = ($this->server_root) ? $dir : "users/".substr($dir, 0, 2)."/".$dir."/";
        $dir = $this->path;

        if ( !$virusScan ) {
            $this->virus = 16;
        }

        /*if (!file_exists($dir)) {
        	mkdir($dir, 0777,1);
        }*/
        
        if ($this->size > 0){
            $ext = strtolower($this->getext($this->name));
            if ( strlen($destFileName) > 0) {
                $ext = strtolower($this->getext($destFileName));
            }
            if ($this->size > $this->max_size){
                $this->error = "Cлишком большой файл. ";
                return NULL;
            }
            
            if ( in_array($ext, $GLOBALS['disallowed_array']) && $ext != $this->file_ext ) {
                $this->error = "Недопустимый тип файла. ";
                return NULL;
            }

            if(count($this->allowed_ext) && !in_array($ext, $this->allowed_ext)) {
                $this->error = "Недопустимый тип файла. ";
                return NULL;
            }

            if (!$this->CheckPath($this->path, true)) { // проверяем директорию, если надо - создаем.
            	$this->MakeDir($this->path);
            }

            if ($this->CheckPath($dir, true))
            {
                $tmp = $this->secure_tmpname($dir,".".$ext);
                if (!$tmp) {
                    $this->error = "Директория задана неверно. ";
                    return false;
                }
                if ( strlen($destFileName) == 0) {
                    $this->name = substr_replace($tmp,"",0,strlen($dir));
                } else {
                    $this->name = $destFileName;
                }
                if(in_array($ext, $GLOBALS['graf_array']) && $this->disable_animate) {
                    $this->getDisabledAnimateGIF();
                }
                if (!isNulArray($this->max_image_size) && in_array($ext, $GLOBALS['graf_array'])) {
    
                    $this->_getImageSize($this->tmp_name);
                    $this->validExtensionFile($this->image_size['type']);
                    $ext = strtolower($this->getext($this->name));
                    if ( in_array($ext, $GLOBALS['disallowed_array']) && $ext != $this->file_ext ) {
                        $this->error = "Недопустимый тип файла. ";
                        return NULL;
                    }
                    if(count($this->allowed_ext) && !in_array($ext, $this->allowed_ext)) {
                        $this->error = "Недопустимый тип файла. ";
                        return NULL;
                    }
                    $prevent_less = ($this->max_image_size['prevent_less'] &&
                                ($this->image_size['width'] < $this->max_image_size['width']
                                    || $this->image_size['height'] < $this->max_image_size['height']));
                    
                    if ($this->resize && ($this->image_size['width'] > $this->max_image_size['width']
                            || $this->image_size['height'] > $this->max_image_size['height']) && !$prevent_less) {

                        $src = $this->tmp_name;
                        $dest = $this->tmp_name;
                        
                        $format = strtolower(substr($this->image_size['mime'], strpos($this->image_size['mime'], '/')+1));
                        $icfunc = "imagecreatefrom" . $format;
                        $imfunc = "image" . $format;
                        if (!function_exists($icfunc) || !function_exists($imfunc)) {
                            $this->error = "Недопустимый формат файла. ". $imfunc;
                            $this->name = "";
                        } else {

                            $x_ratio = $this->max_image_size['width'] / $this->image_size['width'];
                            $y_ratio = $this->max_image_size['height'] / $this->image_size['height'];
    
                            $ratio       = min($x_ratio, $y_ratio);
                            if ($ratio == 0) $ratio = max($x_ratio, $y_ratio);
                            $use_x_ratio = ($x_ratio == $ratio);
    
                            $new_width   = $use_x_ratio  ? $this->max_image_size['width']  : floor($this->image_size['width'] * $ratio);
                            $new_height  = !$use_x_ratio ? $this->max_image_size['height'] : floor($this->image_size['height'] * $ratio);
                            $new_left    = $use_x_ratio  ? 0 : floor(($this->max_image_size['width'] - $new_width) / 2);
                            $new_top     = !$use_x_ratio ? 0 : floor(($this->max_image_size['height'] - $new_height) / 2);
    
                            $isrc = $icfunc($src);
    						
                            if ($isrc) {
                            	
                                if ($this->proportional){
                                    if ($this->topfill) {
                                    	if($this->image_size['type']==3) {
                                            $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
                                        } else {
	                                        $idest = imagecreatetruecolor($this->max_image_size['width'], $this->max_image_size['height']);
	                                        imagefill($idest, 0, 0, $this->background);
	                                        imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
                                        				   $new_width, $new_height, $this->image_size['width'], $this->image_size['height']);
                                        }
                                    } elseif($this->crop) {
                                        if($this->image_size['type']==3) {
                                            $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
                                        } else {
                                            $newWidth      = $this->max_image_size['width'];
                                            $newHeight     = $this->max_image_size['height'];
                                            $optionArray   = $this->_getImageOptimalCrop($newWidth, $newHeight);
                                            $optimalWidth  = $optionArray['optimalWidth'];
                                            $optimalHeight = $optionArray['optimalHeight'];
                                            $imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
                                            imagecopyresampled($imageResized, $isrc, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->image_size['width'], $this->image_size['height']);
                                            $isrc = $imageResized;
                                            $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
                                            $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
                                            $idest = imagecreatetruecolor($newWidth , $newHeight);
                                            imagecopyresampled($idest, $isrc, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
                                        }
                                    } else {
                                    	if($this->image_size['type']==3) {
                                            $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
                                    	} else { 
                                    		$idest = imagecreatetruecolor($new_width, $new_height);
	                                        imagefill($idest, 0, 0, $this->background);
	                                        imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
	                                        $new_width, $new_height, $this->image_size['width'], $this->image_size['height']);
                                    	}
                                    }
                                } else {
                                    $idest = imagecreatetruecolor($this->max_image_size['width'], $this->max_image_size['height']);
                                    imagefill($idest, 0, 0, $this->background);
                                    imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
                                    $this->max_image_size['width'], $this->max_image_size['height'],
                                    $this->image_size['width'], $this->image_size['height']);
                                }
                                if ($this->image_size['type'] == 2) imagejpeg($idest, $dest, $this->quality);
                                else {
                                    if($this->image_size['type']!=1) {
                                        $imfunc($idest, $dest);
                                    }
                                }
                                $this->_getImageSize($dest);
                                $this->size = filesize($dest);
                                imagedestroy($isrc);
                                imagedestroy($idest);
                                unset($isrc);
                                unset($idest);
                            } else {
                                $this->error[] = "Не смог изменить размер файла. ";
                                $this->name = "";
                            }
     
                        }
    
                    }
                    elseif ((!$this->resize && ((!$this->max_image_size['less'] && 
                            ($this->image_size['width'] != $this->max_image_size['width'] 
                            || $this->image_size['height'] != $this->max_image_size['height']))
                            || ($this->max_image_size['less'] && ($this->image_size['width'] > $this->max_image_size['width']
                            || $this->image_size['height'] > $this->max_image_size['height']))))
                                ||
                            $prevent_less
                                ) {
                        $this->error[] = "Недопустимые размеры файла. ";
                        $this->name = "";
                    }
                }
                if (isNulArray($this->error)){
                    $this->_upload($this->tmp_name);
                }
            }
            else
            {
                $this->error[] = "Невозможно загрузить файл. ";
            }
        } else $this->name = "";
    
        return ($this->name);
    }
    
    /**
     * Записать данные в файл
     *
     * @param string $path     Путь к файлу
     * @param string $content  Данные
     * @return boolean true - если запись прошла удачно, false - ошибка
     */
    public function putContent($path, $content) {
        if(!$this->CheckPath(dirname($path), true)) {
            $this->MakeDir(dirname($path));
        }
        if($this->_wdp->put('/'.$path, $content, $this->exclude_reserved_wdc)) {
            if($this->name == '') {
                $this->_autoFileParams($path, strlen($content));
            }
            $this->_addFileParams();
            return true;
        }
        return false;
    }
    /**
     * Выясняем данные для записи в таблицу по данным (путь к файлу, размер файла)
     *
     * @param string  $path    Путь к файлу
     * @param integer $size    Размер файла
     */
    public function _autoFileParams($path, $size) {
        $this->name = $this->original_name = basename($path);
        $this->size = $size;
        $this->path = dirname($path)."/";
        $this->virus = null;
        $this->image_size = array('type'=>0, 'width'=>0, 'height'=>0);     
    }
    
    /**
     * Загрузка файла через WebDav. Имя файла и путь назначения задается в $this->name, $this->path.
     *
     * @param string $from Что загружать
     * @return boolean true - все прошло удачно, false - не получилось загрузить
     */
    private function _upload($from) {
        $ext = $this->getext();
        if (in_array($ext, $GLOBALS['graf_array']))
            $this->_getImageSize($from);
        $this->validExtensionFile($this->image_size['type']);
        if($this->prefix_file_name != "") 
            $this->name = $this->prefix_file_name . $this->name;
            
        if(!$this->CheckPath($this->path, true)) {
            $this->MakeDir($this->path);
        }
        
        if ($this->_wdp->put_file('/'.$this->path.$this->name, $from, $this->exclude_reserved_wdc)) {
            $this->_addFileParams();
            $this->fireEvent('create');
            return true;
        }
        return false;
    }
    
    /**
     * Проверка на совпадение типа файла с названием
     *
     * @param integer $type Тип файла (1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(orden de bytes intel), 8 = TIFF(orden de bytes motorola), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM)
     */
    public function validExtensionFile($type) {
        $exp = $this->getext();
        switch($type) {
            case 1:
                if($exp != 'gif') $this->name = preg_replace('/\.[^.]+$/', '.gif', $this->name);
                break;
            case 2:
                if($exp != 'jpg') $this->name = preg_replace('/\.[^.]+$/', '.jpg', $this->name);
                break;
            case 3:
                if($exp != 'png') $this->name = preg_replace('/\.[^.]+$/', '.png', $this->name);
                break;
            case 4:
            case 13:
                if($exp != 'swf') $this->name = preg_replace('/\.[^.]+$/', '.swf', $this->name);
                break;
            case 6:
                if($exp != 'bmp') $this->name = preg_replace('/\.[^.]+$/', '.bmp', $this->name);
                break;   
                
        }    
    }
    
    /**
     * Добавление параметров к загруженному файлу
     *
     */
    protected function _addFileParams(){
        if (!$this->modified) {
			$this->modified = date("Y-m-d H:i:s");
		}
        // На file_template все равно триггер, которые отправляет в file, но он не вернет id.
        $table = ($this->table == 'file_template' ? 'file' : $this->table);
        $this->id = $GLOBALS['DB']->insert($table, array(
			'fname'      => $this->name,
			'original_name'      => $this->original_name,
			'modified'   => $this->modified,
			'size'       => $this->size,
			'path'       => $this->path,
			'ftype'      => $this->image_size['type'],
			'width'      => $this->image_size['width'],
			'height'     => $this->image_size['height'],
            'virus'      => is_null($this->virus) ? $this->virus : sprintf("%04b", $this->virus),
            'virus_name' => $this->virusName,
            'src_id' => $this->src_id
		), 'id');
    }
    
    /**
     * Обновление параметров загруженного файла
     * Если не известно, из какой таблицы файл источника и в какую таблицу нужно его перекинуть,
     * то надежнее задать перед вызовом $file->table = 'file_template'.
     *
     * @param array $params Параметры обновления [size=>1, path=>,...] (см. таблицу file)
     */
    function updateFileParams($params, $nomod = false) {
        global $DB;
        if (!$this->id || !$params || !is_array($params)) {
			return;
		}
        if (!$this->modified) {
			$this->modified = date("Y-m-d H:i:s");
		}
        $params['modified'] = $this->modified;
        $DB->update($this->table, $params, 'id = ?', $this->id);
		return $DB->error;
    }

    /**
     * Импорт файла юзеров в БД. Используется только для первоначального импорта файлов юзеров в таблицу file
     *
     * @param string $from	имя файла с путем относительно users/ (например te/temp/upload/test.jpg)
     * @return integer|boolean		Возвращает id файла в случае успеха или false, если файла не существует.
     */
    function DBImport($from){
        if (!file_exists(ABS_PATH.'upload/'.$from)) return false;
        $this->size = filesize(ABS_PATH.'upload/'.$from);
        if ($this->size){
            $this->_getImageSize(ABS_PATH.'upload/'.$from);
            $this->path = dirname($from)."/";
            $this->name = basename($from);
            $this->_addFileParams();
            return $this->id;
        }
        return false;
    }
    
    /**
     * Разбираем файл SWF упакованный 7z, чтобы выяснить его данные
     * 
     * @param string $file
     * @return boolean
     * @todo Это костыль, как только в PHP сделают поддержку через getimagesize -- можно данную функцию убрать
     */
    private function _lzmaSWF($file) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/LZMA_SWF.php';
        $lzma = new LZMA_SWF($file);
        if( $lzma->isLZMACompress() ) {
            $lzma->getInformationSWF();
            return $lzma->info;
        }
        return false;
    }
    
    /**
     * Взять размеры загружаемого изображения
     *
     * @param resource $file  Загружаемое изображение
     */
    public function _getImageSize($file){
        $img = getimagesize($file);
        if(!$img) { // если не смог распознать данные проверим не SWF ли это запакованный 7z
            $img = $this->_lzmaSWF($file);
        }
        $ret['width'] = ($img[0])?$img[0]:0;
        $ret['height'] = ($img[1])?$img[1]:0;
        $ret['type'] = ($img[2])?$img[2]:0;
        $ret['mime'] = ($img['mime'])?$img['mime']:0;
        $this->image_size = $ret;
    }
    
    /**
     * Создание копии
     * Перед вызовом важно убедиться, что $file->table указана верная.
     *
     * @param string $dest      Имя копии
     * @param boolean $new_name Задать новое имя файлу или нет(true - да, false - нет)
     * @return boolean true - если создание прошло супешно, false - не успешно
     */
    public function _remoteCopy($dest, $new_name = true){
        if(!$this->CheckPath(dirname($dest), false)) {
            $this->MakeDir(dirname($dest));
        }
        if ($this->_wdp->copy_file('/'.$this->path.$this->name, '/'.$dest, true)) {
            $this->fireEvent('copy', $dest);
            $tmp_name = $this->name;
            $tmp_path = $this->path; 
            $this->path = dirname($dest)."/";
            $this->name = basename($dest);
            $this->_addFileParams();
            if (!$new_name){
                $this->name = $tmp_name;
                $this->path = $tmp_path;
            }
            return true;
        }
        return false;
    }
    
    
    
    
    
    /**
     * Создание копии файла без фиксации в БД
     */
    public function copyFileTo($dest, $create_dir = false)
    {
        if ($create_dir) {
            if(!$this->CheckPath(dirname($dest), false)) {
                $this->MakeDir(dirname($dest));
            }
        }
        
        if ($this->_wdp->copy_file(
                "/{$this->path}{$this->name}", 
                "{$dest}", true)) {
                    
            return true; 
        }
        
        return false;
    }






    /**
     * Удаляет файл по его id или пути до файла
     *
     * @param string $id		id файла в таблице file
     * @param string $dir		директория файла (относительно корня, без первого слеша). (опционально, если id = 0) Например: users/te/temp/
     * @param string $fname		имя файла. (опционально, если id = 0)
     */
    function Delete($id, $dir = "", $fname = ""){
        if ($id = intval($id)) {
            $where = 'id = ' . $id;
            if( !($rows = $GLOBALS['CFileCache']->get($id)) )
                $rows = self::selectFilesById($this->table, $id);
            if(isset($rows[0])) {
                $dir = $rows[0]['path'];
                $fname = $rows[0]['fname'];
                $count_links = intval($rows[0]['count_links']);
            } else {
                $dir = $rows['path'];
                $fname = $rows['fname'];    
                $count_links = intval($rows['count_links']);
            }
        } else if ($dir && $fname) {
            if ( !($rows = $GLOBALS['CFileCache']->get($dir.$fname)) ) {
                $rows = CFile::selectFilesByFullName($this->table, $dir.$fname);
            }
            if(isset($rows[0])) {
                $count_links = intval($rows[0]['count_links']);
            } else {
                $count_links = intval($rows['count_links']);
            }
            $where = "fname = '".pg_escape_string($fname)."' AND path = '".pg_escape_string($dir)."'";
		}

        if ($count_links) {
            $GLOBALS['DB']->query("UPDATE {$this->table} SET count_links = count_links-1 WHERE {$where}");
            if ($id) { 
                $GLOBALS['CFileCache']->del($id); 
            } else {
                $GLOBALS['CFileCache']->del($dir.$fname);
            }
        } else {
            if ($fname && $this->_wdp->delete('/'.$dir.$fname, $this->exclude_reserved_wdc)){
                $GLOBALS['DB']->query("DELETE FROM {$this->table} WHERE {$where}");
                
                if ($id) { 
                    $GLOBALS['CFileCache']->del($id); 
                } else {
                    $GLOBALS['CFileCache']->del($dir.$fname);
                }
                
                $this->fireEvent('delete', $dir . $fname);
            }
        }
    }
    
    /**
     * Переименовывает файл на стороне сервера
     * Если не известно, из какой таблицы файл источника и в какую таблицу нужно его перекинуть,
     * то надежнее задать перед вызовом $file->table = 'file_template'.
     *
     * @param string $to	имя файла вместе с новой директорией (относительно корня, без первого слеша) Например: users/te/temp/upload/new.jpg
     * @return boolean		Возвращает true, если переименование прошло успешно
     */
    function Rename($to) {
        if ($to && $this->name) {
            if(!$this->CheckPath(dirname($to), true)) {
                $this->MakeDir(dirname($to));
            }
            if ($this->_wdp->move('/'.$this->path.$this->name, '/'.$to, true)) {
                $this->name = basename($to);
                $this->path = dirname($to).'/';
                $this->updateFileParams(array( 'path'=>$this->path, 'fname'=>$this->name ));
                $GLOBALS['CFileCache']->del($this->id);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Создает директорию на сервере
     *
     * @param string $path		путь к директории (например users/te/temp).
     * @return boolean Возвращает true, если создание прошло успешно
     */
    function MakeDir($path) {
        $parent_dir = dirname($path);
        if (!($ok = $this->CheckPath($parent_dir, false))) {
            $ok = $this->MakeDir($parent_dir);
        }
        if($ok) {
            $ok = $this->_wdp->mkcol('/'.$path.'/');
        }
        return $ok;
    }
    
    /**
     * Проверяет директорию на существование 
     *
     * @param string $path	           путь до директории (относительно корня, без первого слеша) Например: users/te/temp/
     * @param boolean $dont_check_put    true: не проверять, если webdav сидит на nginx и включен create_full_put_path, просто вернуть true.
     * @return boolean			Возвращает true, если директория существует
     */
    function CheckPath($path, $dont_check_put = false) {
        if (!$path) $path = $this->path;
        return $this->_wdp->check_file($path, $dont_check_put);
    }
    
    /**
     * Удаляет директорию с поддиректориями и файлами в ней
     *
     * @param string $path		путь до директории (относительно корня, без первого слеша) Например: users/te/temp/
     * @param boolean $check	использовать ли проверку, чтобы случайно не потереть вообще все (разрешает удалять только в пределах директории users/??/)
     * @return boolean			true, если удалено. false - если не удалено.
     */
    function DeleteDir($path = '', $check = true){
        if (!$path) $path = $this->path;
        if ($check){
            $path_arr = explode("/",$path);
            if ($path_arr[0] != 'users') return false;
            if ($path_arr[1] == '.' || $path_arr[1] == '..') return false;
            if (!$path_arr[2]) return false;
        }
        if ($path && $this->_wdp->delete('/'.$path)) {
            $ppath = str_escape($path, '%_', '!');
            $GLOBALS['DB']->query("DELETE FROM {$this->table} WHERE path LIKE '{$ppath}%' ESCAPE '!'");
        }
        return true;
    }
    
    
    
    /**
     * Удалить директорию из временной директории хранилища
     * в БД никак не фиксируется
     * 
     * @param type $path
     * @return boolean
     */
    public function deleteFromTempDir($path)
    {
        $path = str_replace('.', '', $path);
        $path = str_replace('//', '', $path);
        $path = trim($path, '/');
        $path_arr = explode('/', $path);
        
        if (empty($path_arr) || 
            (count($path_arr) == 1 && 
             $path_arr[0] == 'temp')) {
            
            return false;
        }
        
        if ($path_arr[0] != 'temp') {
            $path = "temp/{$path}";
        }
        
        return $this->_wdp->delete("/{$path}/");
    }




    /**
     * Переименовывает директорию с поддиректориями и файлами в ней. Только для смены логина!!!
     *
     * @param string $new_login	новый логин
     * @param string $old_login	старый логин
     * @return boolean			true, если успешно. false - если не успешно.
     */
    function MoveDir($new_login, $old_login){
        $udir = 'users/';
        $old_path  = $udir.substr($old_login, 0, 2).'/'.$old_login.'/';
        $new_ppath = $udir.substr($new_login, 0, 2).'/';
        $new_path  = $new_ppath.$new_login.'/';
        if(!$this->CheckPath($old_path, false)) {
            return true;
        }
        if (!$this->CheckPath($new_ppath, false)) {
        	$this->MakeDir($new_ppath);
        }
        if ($this->_wdp->move('/'.$old_path, '/'.$new_path, 0)) {
            $pold_path = str_escape($old_path, '%_', '!');
            $GLOBALS['DB']->query(
              "UPDATE {$this->table}
                  SET path = ?::text||substring (path, '/([^/]*)/$')||'/'
                WHERE path LIKE '{$pold_path}%' ESCAPE '!'",
              $new_path
            );
            return true;
        }
        return false;
    }
    
    /**
     * Возвращает строку, содержащую все ошибки обработки файла ($this)
     *
     * @param string $glue		Разделитель ошибок в строке
     * @return string			Строка ошибок, разделенных $glue
     */
    function StrError($glue = " "){
        if(is_array($this->error))
            return implode($glue,$this->error);
        return $this->error;
    }
   
   /**
    * Уменьшение файла до нужных размеров
    * NB! для существующих файлов не подходит 
    *
    * @param string $destanation   имя генерируемого файла
    * @param array  $tn_image_size Размер файла
    * @param boolean  $allow_less Разрешить или нет размер картинок меньше указанного
    * @return boolean false, если не сработало
    */
    function img_to_small($destanation, $tn_image_size, $allow_less = false){
             
        $src = $this->tmp_name;
        $dest = $this->tmp_name."_sm";

        if (!file_exists($src)) {
            $this->error[] = "Загруженный файл не найден!";
            return false;
        }
       
        /*if (file_exists($dest)) {  Это проверку потом дописать
            $size_sm = getimagesize($dest);
            if ((!$width || $size_sm[0] <= $width) && (!$height || $size_sm[1] <= $height)){
                return true;
            }

        }*/

        if ($this->image_size === false) return false;
        $width = $tn_image_size['width'];
        $height = $tn_image_size['height'];
        // не уменьшать, если необходимая ширина меньше исходной, просто сделать копию

        if($allow_less && ($this->image_size['width'] < $width || $this->image_size['height'] < $height)) {
            $this->error[] = "Файл не удовлетворяет условиям загрузки!";
            return false;
        }
        
        if ((!$width || $this->image_size['width'] <= $width) && (!$height || $this->image_size['height'] <= $height)){
            $tmp_name = $this->name;
            $ret = $this->_remoteCopy($this->path.$destanation, false);
            $this->name = $tmp_name;
            return $ret;
        }

        // Определяем исходный формат по MIME-информации, предоставленной
        // функцией getimagesize, и выбираем соответствующую формату
        // imagecreatefrom-функцию.
        $format = strtolower(substr($this->image_size['mime'], strpos($this->image_size['mime'], '/')+1));
        $icfunc = "imagecreatefrom" . $format;
        $imfunc = "image" . $format;
        if (!function_exists($icfunc) || !function_exists($imfunc)) return false;

        $x_ratio = $width / $this->image_size['width'];
        $y_ratio = $height / $this->image_size['height'];

        $ratio       = min($x_ratio, $y_ratio);
        if ($ratio == 0) $ratio = max($x_ratio, $y_ratio);
        $use_x_ratio = ($x_ratio == $ratio);

        $new_width   = $use_x_ratio  ? $width  : floor($this->image_size['width'] * $ratio);
        $new_height  = !$use_x_ratio ? $height : floor($this->image_size['height'] * $ratio);

        $isrc = $icfunc($src);
		
        if ($isrc)
        {
			
        	
        	if ($this->proportional){
            	if($this->image_size['type']==3) {
                    $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
            	} else {                            
	                $idest = imagecreatetruecolor($new_width, $new_height);
	                imagefill($idest, 0, 0, $this->background);
	                imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
	                $new_width, $new_height, $this->image_size['width'], $this->image_size['height']);
            	}
            } else {
            	if($this->image_size['type']==3) {
                    $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
            	} else {   
	                $idest = imagecreatetruecolor($width, $height);
	                imagefill($idest, 0, 0, $this->background);
	                imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
	                $width, $height, $this->image_size['width'], $this->image_size['height']);
            	}    
            }

            if ($this->image_size['type'] == 2) imagejpeg($idest, $dest, $this->quality);
            else $imfunc($idest, $dest);
            imagedestroy($isrc);
            imagedestroy($idest);
            unset($isrc);
            unset($idest);
            $tmp_name = $this->name;
            $tmp_size = $this->size;
            $this->name = $destanation;
            $this->size = filesize($dest);
            $ret = $this->_upload($dest);
            $this->name = $tmp_name;
            $this->size = $tmp_size;
            unlink($dest);
            return $ret;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Функция для работы с изображениями PNG, для сохранения Alpha - канала при уменьшении картинки
     *
     * @param resource $src   Ресурс картинки
     * @param resource $ovr   Возвращает работу функции imageResizeAlpha()
     * @param integer  $ovr_x Ширина начального файла
     * @param integer  $ovr_y Высота начального файла
     * @param integer  $ovr_w Ширина конечного файла (если false не учитывается)
     * @param integer  $ovr_h Высота конечного файла (если fakse не учитывается)
     */
    function imageComposeAlpha(&$src, &$ovr, $ovr_x, $ovr_y, $ovr_w = false, $ovr_h = false)
	{
		if( $ovr_w && $ovr_h )
		$ovr = imageResizeAlpha( $ovr, $ovr_w, $ovr_h );
		
		/* Noew compose the 2 images */
		imagecopy($src, $ovr, $ovr_x, $ovr_y, 0, 0, imagesx($ovr), imagesy($ovr) );
	}
    /**
	* Resize a PNG file with transparency to given dimensions
	* and still retain the alpha channel information
	*/
	function imageResizeAlpha(&$src, $w, $h)
	{
		/* create a new image with the new width and height */
		$temp = imagecreatetruecolor($w, $h);
		
		/* making the new image transparent */
		//$background = imagecolorallocate($temp, 0, 0, 0);
		//ImageColorTransparent($temp, $background); // make the new temp image all transparent
		//ImageSaveAlpha($temp, false);
		
		imagealphablending($temp, true);
		imagesavealpha($temp,true);
		$transparent = imagecolorallocatealpha($temp, 255, 255, 255, 127);
		
		imagefilledrectangle($temp, 0, 0, $w, $h, $transparent);
        imagefill($temp, 0, 0, $transparent); 
		//imagealphablending($temp, false); // turn off the alpha blending to keep the alpha channel
		
		/* Resize the PNG file */
		/* use imagecopyresized to gain some performance but loose some quality */
		//imagecopyresized($temp, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
		/* use imagecopyresampled if you concern more about the quality */
		imagecopyresampled($temp, $src, 0, 0, 0, 0, $w, $h, $this->image_size['width'], $this->image_size['height']);
		return $temp;
	}
    
    /**
     * Запрашивает у сервера mime-тип файла.
     * 
     * @param string $fname   полное имя файла (включая путь, относительно корня, без первого слеша) Например: users/te/temp/xoxo.zip
     * @return boolean|string false - если не удалось получить mime-тип, или вощзвращает mime-тип файла
     */
    public function getContentType($fname = '') {
        if(!$fname) $fname = $this->path.$this->name;
        if(!$fname) return false;
        return $this->_wdp->get_content_type($fname);
    }
    
    /**
     * Удаляет файлы с дисков, помеченные в базе как удаленные
     * 
     */
    public function removeDeleted(){
        $ret = $GLOBALS['DB']->rows("SELECT id FROM {$this->table} WHERE deleted = true");
        if ($ret) {
			foreach ($ret as $row) {
				$this->Delete($row['id']);
			}
        }
    }

	/**
     * Проверяет файл антивирусом (drweb)
     * 
     * @param  boolean  $delete  Если TRUE, то удалит зараженный файл
     * @return integer           Код проверки (см. self::$virus) или FALSE в случае ошибки
     */
    public function antivirus($delete = TRUE) {
        global $DB;
        if ( !defined('DRWEB_DEAMON') && !defined('DRWEB_DUMMY') ) {
            return FALSE;
        }
        $path = pathinfo($this->name);
        if ( in_array(strtolower($path['extension']), $this->antivirusSkip) ) {
            $DB->update('file', array( 'virus' => '1000' ), 'id = ?', $this->id);
            return 8;
        }
        $name = '';
        if ( defined('DRWEB_DEAMON') ) {
            $file = DRWEB_STORE . '/' . $this->path . $this->name;
            exec(DRWEB_DEAMON . ' -n' . DRWEB_HOST . ' -p' . DRWEB_PORT . ' -rv -f"'.$file.'"', $shellText, $res);
            if ( $res > 0 ) {
                $code  = 0;
                $code += ( $res & 1 );
                $code += ( $res & 6 ) ? 2 : 0;
                $code += ( $res > 7 ) ? 4 : 0;
                if ( $code == 1 ) {
                    if (preg_match('/Known virus/', $shellText[1]))	{
                        $name = trim($shellText[2]);
                    }
                    if ( $delete ) {
                        $r = $DB->row("SELECT fname, path FROM {$this->table} WHERE id = ?", $this->id);
                        if ($r['fname']) {
                            $this->_wdp->delete('/'.$r['path'].$r['fname']);
                        }
                    }
                }
            }
        }
        $this->virus = $code;
        $this->virusName = $name;
        $DB->update($this->table, array ( 'virus' => sprintf("%04b", $code), 'virus_name' => $name ), 'id = ?', $this->id);
        return $code;
    }

    /**
     * Ресайз картинки уже добавленной в webdav
     *
     * @param    string     $to               Куда сохранить и с каким именем
     * @param    integer    $newWidth         Ширина в пикселах
     * @param    integer    $newHeight        Высота в пикселах
     * @param    string     $option           Тип ресайза: portrait - приоритет высоты
     *                                                     landscape - приоритет ширины
     *                                                     crop - вырезание из центра картинки
     *                                                     auto - автоматический выбор
     *                                                     cropthumbnail - уменьшить и верезать по центру
     * @param    boolean    $savePngAlpha     Сохранить альфа канал для PNG файлов
     * @return    boolean                     true - рейсайз сделан, false - не сделан
     */
    public function resizeImage($to, $newWidth, $newHeight, $option="auto", $savePngAlpha = false, $table = null) {
        if(!in_array($this->image_size['type'], array(1,2,3))) {
            // Недопустимый формат файла
            return false;
        }

        $tmp_name = uniqid();
        $file = "/tmp/{$tmp_name}";
        file_put_contents($file, file_get_contents(WDCPREFIX_LOCAL.'/'.$this->path.$this->name));
        
        /*switch($this->image_size['type']) {
            case 1:
                $img = imagecreatefromgif($file);
                break;
            case 2:
                $img = imagecreatefromjpeg($file);
                break;
            case 3:
                $img = imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }

        if(!$img) {
            // Ошибка открытия файла
            return false;
        }

        // Расчитываем новые значения ширины и высоты картинки
        switch ($option) {
            case 'portrait':
                $optimalWidth = $this->_getImageSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight= $this->_getImageSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->_getImageSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->_getImageOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }

        if ($this->image_size['type'] && $savePngAlpha) {
            $imageResized = $this->imageResizeAlpha($img, $optimalWidth, $optimalHeight);
        } else {
            $imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
            imagecopyresampled($imageResized, $img, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->image_size['width'], $this->image_size['height']);
        }

        if ($option == 'crop') {
            $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
            $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
            $crop = $imageResized;
            $imageResized = imagecreatetruecolor($newWidth , $newHeight);
            imagecopyresampled($imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
        }

        // Сохраняем измененную картинку
        $file = $file.'_resized';
        switch($this->image_size['type']) {
            case 1:
                $ext = 'gif';
                imagegif($imageResized, $file);
                break;
            case 2:
                $ext = 'jpg';
                imagejpeg($imageResized, $file, $this->quality);
                break;
            case 3:
                $ext = 'png';
                $scaleQuality = round(($this->quality/100) * 9);
                $invertScaleQuality = 9 - $scaleQuality;
                imagepng($imageResized, $file, $invertScaleQuality);
                break;
        }*/
        
        switch ($option) {
            case 'portrait':
                $optimalWidth = $this->_getImageSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight= $this->_getImageSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->_getImageSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->_getImageOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }
        
        $imagick = new Imagick($file);
        
        if ($option == 'cropthumbnail'){
            
            $imagick->cropThumbnailImage($newWidth, $newHeight);
            
        } else if ($option == 'crop') {
            $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
            $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
            $imagick->cropImage($newWidth, $newHeight, $cropStartX, $cropStartY);
        } else {
            if($this->image_size['type'] == 1) { // GIF пытаемся сохранить анимацию при уменьшении изображения
                $imagick = $imagick->coalesceImages();
                do {
                    $imagick->scaleImage($optimalWidth, $optimalHeight, true);
                } while ($imagick->nextImage());

                //$imagick->optimizeImageLayers();
            } else {
                $imagick->scaleImage($optimalWidth, $optimalHeight, true);
            }
        }
        //освобождаем память и сохраняем
        $imagick = $imagick->deconstructImages();
        $imagick->writeImages($file, true);
        
        $tFile = new CFile();
        if($table) $tFile->table = $table;
        else $tFile->table = $this->table;
        $tFile->tmp_name = $file;
        $tFile->original_name = $this->original_name;
        $tFile->size = filesize($file);
        $tFile->path = dirname($to) . '/';
        $tFile->name = basename($to);
        $tFile->_upload($file);
        return $tFile;
    }

    /**
     * Расчет оптимальной ширины при фиксированной высоте
     *
     * @param    integer    $newHeight    Высота в пикселах
     * @return   integer                  Расчитанная ширина
     */
    private function _getImageSizeByFixedHeight($newHeight) {
        $ratio = $this->image_size['width'] / $this->image_size['height'];
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    /**
     * Расчет оптимальной высоты при фиксированной ширене
     *
     * @param    integer    $newWidth     Ширина в пикселах
     * @return   integer                  Расчитанная высота
     */
    private function _getImageSizeByFixedWidth($newWidth) {
        $ratio = $this->image_size['height'] / $this->image_size['width'];
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }

    /**
     * Расчет оптимальной высоты и ширины
     *
     * @param    integer    $newWidth     Ширина в пикселах
     * @param    integer    $newHeight    Высота в пикселах
     * @return   array                    Расчитанная ширина и высота
     */
    public function _getImageSizeByAuto($newWidth, $newHeight) {
        if ($this->image_size['height'] < $this->image_size['width']) {
            $optimalWidth = $newWidth;
            $optimalHeight= $this->_getImageSizeByFixedWidth($newWidth);
        } elseif ($this->image_size['height'] > $this->image_size['width']) {
            $optimalWidth = $this->_getImageSizeByFixedHeight($newHeight);
            $optimalHeight= $newHeight;
        } else {
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight= $this->_getImageSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->_getImageSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
            } else {
                $optimalWidth = $newWidth;
                $optimalHeight= $newHeight;
            }
        }
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
    public function getImageSizeByAuto ($newWidth, $newHeight) {
        return $this->_getImageSizeByAuto($newWidth, $newHeight);
    }

    /**
     * Расчет оптимальной высоты и ширины при кропе
     *
     * @param    integer    $newWidth     Ширина в пикселах
     * @param    integer    $newHeight    Высота в пикселах
     * @return   array                    Расчитанная ширина и высота
     */
    private function _getImageOptimalCrop($newWidth, $newHeight) {
        $heightRatio = $this->image_size['height'] / $newHeight;
        $widthRatio  = $this->image_size['width'] /  $newWidth;
        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }
        $optimalHeight = $this->image_size['height'] / $optimalRatio;
        $optimalWidth  = $this->image_size['width']  / $optimalRatio;
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
    /**
     * Функция режет анимационные gif файлы сохраняя 1 кадр
     * 
     * @param string $file  Путь до файла
     * @return boolean 
     */
    public function getDisabledAnimateGIF($file = false) {
        if(!$file && $this->tmp_name) {
            $file = $this->tmp_name;
        } else {
            return false;
        }
        $orig_content = @file_get_contents($file);
        if(($unan_content = unanimate_gif($orig_content)) !== false) {
            file_put_contents($file, $unan_content);
        }
    }

    /**
     * Создает ссылку на файл
     *
     * @return boolean  treu - удачно, false - не удачно
     */
    public function makeLink() {
        if($this->id) {
            $this->table = 'file_template';
            $this->count_links++;
            $this->updateFileParams(array( 'count_links'=>$this->count_links ));
            $GLOBALS['CFileCache']->del($this->id);
        } else {
            return false;
        }
    }
    
    /**
     * укорачивает имя файла до заданного количества символов, оставляя расширение
     * @param string $name имя файла которое надо укоротить
     * @param integer $maxLength максимальная длина имени файла с расширением и точкой
     * 
     * @return возвращает новое укороченное имя файла
     */
    public function shortenName ($name, $maxLength) {
        $nameLength = strlen($name);
        if ($nameLength <= $maxLength) {
            return $name;
        }
        $ext = $this->getext($name);
        $maxLength_ = $maxLength - strlen($ext) - 1; // максимальная длина без расширения и точки
        $name_ = substr($name, 0, $maxLength_);
        $newName = $name_ . '.' . $ext;
        return $newName;
    }
 
    
    
    /**
     * @deprecated пока не использовать
     * @todo: на бое проблемы - не полностью возвращается тело запроса
     * 
     * Копирует файл из WebDav 
     * в локальную файловую систему
     * 
     * @param type $localpath
     */
    /*
    public function copyToLocalPath($localpath) 
    {
        return $this->_wdp->get_file("/{$this->path}{$this->name}", $localpath);
    }
    */

    /**
     * @deprecated пока не использовать
     * @todo: на бое проблемы - не полностью возвращается тело запроса
     * 
     * Копирует массив файлов из WebDav в локальную файловую систему
     * Формат входных данных array("remotepath" => "localpath")
     * 
     */
    /*
    public function copyFilesToLocalPath($filelist) 
    {
        return $this->_wdp->get_files($filelist);
    }
    */

    
    public function copyToLocalPathFromDav($remotepath, $localpath) 
    {
        $data = file_get_contents(WDCPREFIX_LOCAL . $remotepath);
        
        if ($data) {
            return (bool)file_put_contents($localpath, $data);
        }
        
        return false;
    }   

    


    /**
     * Сбор событий действий с файлом
     * Пока используется только под бекап
     * 
     * @global type $DB
     * @param type $event
     * @param type $params
     * @return boolean
     */
    public function fireEvent($event, $params = null)
    {
        global $DB, $BACKUP_SERVICE;

        //Если бекап отключен
        if(!isset($BACKUP_SERVICE['active']) || 
           $BACKUP_SERVICE['active'] === false) {
            return false;
        }
        
        switch ($event) {
            
            case 'delete':   
                $filepath = $params;
                
            case 'create': 
                if(!isset($filepath)){
                    $filepath = $this->path . $this->name;
                }
                
                $DB->query("SELECT pgq.insert_event('backup', '{$event}', 'file={$filepath}');");
                
                break;
                
            case 'copy':
                $from_filepath = $this->path . $this->name;
                $to_filepath = $params;
                
                $DB->query("SELECT pgq.insert_event('backup', '{$event}', 'file={$from_filepath}&to={$to_filepath}');");
                
                break;
                
        }
        
        return true;
    }
    
    
    
    
    public function getTableName($id = null)
    {
        global $DB;
        
        if (!$id)  {
            $id = $this->id;
        }
        
        return $DB->val("
            SELECT p.relname
            FROM file_template AS f, pg_class AS p
            WHERE f.id = ?i AND f.tableoid = p.oid            
        ", $id);
    }
    
    
    
    /**
     * Ссылка на файл
     * 
     * @return type
     */
    public function getUrl()
    {
        return WDCPREFIX . $this->path . $this->name;
    }
    
    /**
     * Дата создания/модификации файла
     * 
     * @param type $format
     * @return type
     */
    public function getModified($format = null)
    {
        return ($format)?date($format, strtotime($this->modified)):$this->modified;
    }
    
    /**
     * Название файла для UI
     * 
     * @return type
     */
    public function getOriginalName()
    {
        return $this->original_name;
    }
    
}
