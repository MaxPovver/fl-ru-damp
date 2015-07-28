<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


/**
 * Класс для работы нового загрузчика
 * Все основные функции работают статически вызвать можно так uploader::s{nameMethod}()
 * 
 */
class uploader {
    
    /**
     * Статус файла, только что добавленный новый
     */
    const STATUS_CREATE = 1;
    
    /**
     * Статус файла, удаленный из статуса STATUS_CREATE
     */
    const STATUS_REMOVE = 2;
    
    /**
     * Добавленный ранее в систему
     */
    const STATUS_ADDED  = 3;
    
    /**
     * Удаленный из статуса STATUS_ADDED 
     */
    const STATUS_DELETE = 4;
    
    /**
     * Маска уникального ИД для сессии загрузки файлов не более 32 символов
     */
    const UMASK   = '00400-00600-1001001';
    
    /**
     * Максимальное количество загружаемых файлов
     * 
     */
    const MAX_FILES = 6;
    
    /**
     * Максимальный размер файла (10 МБ)
     * 
     */
    const MAX_FILE_SIZE = 10485760; 
    
    /**
     * Папка загрузки по умолчанию
     */
    const DEFAULT_TEMPLATE = 'uploader/';
    
    /**
     * Учитывать общий объем загружаемых файлов или только одного файла
     */
    const IS_TOTAL_SIZE = false;
    
    /**
     * Конструктору класса передаем идентификатор конкретного загрузчика
     * 
     * @param type $resource    Идентификатор загрузчика
     */
    function __construct($resource = null) {
        $this->resource = $resource;
    }
    
    /**
     * Создаем Ид сессии
     * 
     * @param type $type    тип сессии
     * @return type
     */
    static function createResource($type = null) {
        return self::resource(self::umask($type));
    }
    
    /**
     * Функция генерирует уникальный код
     * 
     * @param string $code  
     * @return hex
     */
    static function generate($code) {
        $r = floatval("0." . mt_rand())*16|0;  
        return dechex( current($code) == "0" ? $r : ($r&0x3|0x8) ); 
    }
    
    /**
     * Возвращает маску для генерации идентификатора
     * 
     * @param string $type  Тип загрузчика @see self::validate
     * @return string
     */
    static function umask($type = null) {
        return self::UMASK . ( $type !== null ? "-{$type}" : "-file" );
    }
    
    /**
     * Генерирует уникальный код ресурса загрузчика
     * 
     * @param string $mask    Маска для генерации 
     * @return string
     */
    static function resource($mask = null) {
          if($mask === null) $mask = self::umask();
          return preg_replace_callback('~[01]~', array('uploader', 'generate'), $mask);
    }
    
    /**
     * Возвращает количественные данные сессии загрузчика
     * 
     * @param array $status Статусы файлов
     * @return type
     */
    public function getCountResource($status = array(uploader::STATUS_CREATE, uploader::STATUS_ADDED)) {
        return self::sgetCountResource($this->resource, $status);
    }
    
    /**
     * Возвращает количественные данные сессии загрузчика
     * 
     * @global object $DB         Подключение к БД
     * @param string $resource    Сессия загрузчика
     * @param array  $status      Статусы файлов
     * @return array
     */
    static function sgetCountResource($resource, $status = array(uploader::STATUS_CREATE, uploader::STATUS_ADDED)) {
        global $DB;
        $sql = "SELECT COUNT(*) as count, SUM(size) as size FROM attachedfiles WHERE session = ? AND status IN (?l)";
        return $DB->row($sql, $resource, $status);
    }
    
    /**
     * Задаем файлу определенный статус
     * 
     * @param integer $status_before   Статус до
     * @param integer $status_after    Статус после
     * @return bolean
     */
    public function setStatusFiles($status_before, $status_after) {
        return self::ssetStatusFiles($this->resource, $status_before, $status_after);
    }
    
    /**
     * Задаем файлу определенный статус
     * 
     * @global object $DB   Подключение к БД
     * @param string  $resource    Сессия загрузчика
     * @param integer $status_before   Статус до
     * @param integer $status_after    Статус после
     * @return boolean
     */
    static function ssetStatusFiles($resource, $status_before, $status_after) {
        global $DB;
        $sql = "UPDATE attachedfiles SET status = ?i WHERE session = ? AND status = ?i";
        return $DB->query($sql, $status_after, $resource,  $status_before);
    }
    
    /**
     * Количество файлов в текущей сессии
     * 
     * @param array $status  Статусы файлов
     * @return integer
     */
    public function getCountFiles($status = array(uploader::STATUS_CREATE, uploader::STATUS_ADDED)) {
        return self::sgetCountFiles($this->resource, $status);
    }
    
    /**
     * Количество файлов в текущей сессии
     * 
     * @global object $DB   Подключение к БД
     * @param string  $resource    Сессия загрузчика
     * @param array   $status      Статусы файлов
     * @return integer
     */
    static function sgetCountFiles($resource, $status = array(uploader::STATUS_CREATE, uploader::STATUS_ADDED)) {
        global $DB;
        $sql = "SELECT COUNT(*) as count FROM attachedfiles WHERE session = ? AND status IN (?l)";
        return $DB->val($sql, $resource, $status);
    }
    
    /**
     * Возвращает файлы текущей сессии по определенным параметрам
     * 
     * @param array   $status      Статусы файлов
     * @return array
     */
    public function getFiles($status = array(uploader::STATUS_CREATE, uploader::STATUS_ADDED)) {
        return self::sgetFiles($this->resource, $status);
    }
     
    /**
     * Возвращает файлы текущей сессии по определенным параметрам
     * 
     * @global object $DB   Подключение к БД
     * @param string  $resource    Сессия загрузчика
     * @param array   $status      Статусы файлов
     * @return array
     */
    static function sgetFiles($resource, $status = array(uploader::STATUS_CREATE, uploader::STATUS_ADDED)) {
        global $DB;
        
        $sql = "SELECT a.file_id as id, f.original_name as orig_name, a.size, f.path, f.fname, a.status 
                FROM attachedfiles a
                INNER JOIN file_template f ON f.id = a.file_id
                WHERE session = ? AND status IN (?l)
                ORDER BY file_id ASC";
        
        return $DB->rows($sql, $resource, $status);
    }
    
    /**
     * Возвращает опции текущей сессии необходимые для инициализации загрузчка на стороне клиента
     * 
     * @return array
     */
    public function getLoaderOptions() {
        return self::sgetLoaderOptions($this->resource);
    } 
    
    /**
     * Возвращает опции текущей сессии необходимые для инициализации загрузчка на стороне клиента
     * 
     * @param string  $resource    Сессия загрузчика
     * @return array
     */
    static function sgetLoaderOptions($resource) {
        $option = array(
            'items'    => uploader::sgetFiles($resource),
            'resource' => $resource
        );
        $option = self::encodeCharset($option);
        $type  = self::sgetTypeUpload($resource);
        $valid = self::getValidationInfo('project');
        switch($type) {
            case 'project':
            case 'guest_prj':
                $setting = array(
                    'showGraph' => false,
                    'text' => array(
                        'uploadButton' => self::encodeCharset('+ Добавить файл'),
                        'sufSize'      => ''
                    ),
                    'validation' => array(
                        'sizeLimit' => $valid['max_file_size']
                    )
                );
                break;
            default:
                $setting = array();
                break;
        }
        
        $option = array_merge($option, $setting);
        
        return $option;
    }
    
    /**
     * Сохраняет файлы в сессию
     * 
     * @param array $files   Список файлов
     * @param integer $status  Статус файлов
     * @return boolean
     */
    public function setFiles($files, $status = uploader::STATUS_CREATE) {
        return self::ssetFiles($files, $this->resource, $status);
    }
    
    /**
     * Сохраняет(подгружает) файлы в сессию
     * 
     * @global object $DB           Подключение к БД
     * @param array   $files        Список файлов
     * @param string  $resource     Сессия загрузчика
     * @param integer $status       Статус файлов
     * @return boolean
     */
    static function ssetFiles($files, $resource, $status = uploader::STATUS_CREATE) {
        global $DB;
        
        if($files) {
            foreach($files as $file) {
                $CFile = new CFile($file);
                if($status == self::STATUS_CREATE) {
                    $CFile->table = 'file';
                    $CFile->makeLink();
                }
                $sql = "INSERT INTO attachedfiles(file_id, status, date, session, size) VALUES(?i, ?i, NOW(), ?u, ?i);";
                
                $DB->hold()->query($sql, $CFile->id, $status, $resource, $CFile->size);
            }
            return $DB->query();
        }
        
        return false;
    }
    
    /**
     * Создает файл в текущей сессии
     * 
     * @param object $CFile Данные файла
     * @return array    Возвращает массив необходимый для передачи клиентскому скрипту
     */
    public function createFile($CFile) {
        return self::screateFile($CFile, $this->resource);
    }
    
    /**
     * Создает файл в текущей сессии
     * 
     * @global object $DB
     * @param CFile $CFile Данные файла
     * @param string  $resource     Сессия загрузчика
     * @param integer $status       Статус создаваемых файлов
     * @return array    Возвращает массив необходимый для передачи клиентскому скрипту
     */
    static function screateFile(CFile $CFile, $resource, $status = uploader::STATUS_CREATE) {
        global $DB;
        
        $sql = "INSERT INTO attachedfiles(file_id, status, session, size) VALUES(?i, ?i, ?u, ?i)";
        $DB->query($sql, $CFile->id, $status, $resource, $CFile->size);
        
        return array(
            'id'         => $CFile->id,
            'fname'      => $CFile->name,
            'path'       => $CFile->path,
            'size'       => $CFile->size,
            'orig_name'  => $CFile->original_name,
            'status'     => $status
        );
    }
    
    /**
     * Очищает текущую сессию от загруженных в нее файлов
     * 
     * @return boolean
     */
    public function clear() {
        return self::sclear($this->resource);
    }
    
    /**
     * Очищает текущую сессию от загруженных в нее файлов
     * 
     * @global object $DB
     * @param string $resource        Сессия загрзчика
     * @return boolean
     */
    static function sclear($resource) {
        global $DB;
        
        $sql   = "SELECT file_id FROM attachedfiles WHERE session = ?  AND status IN (?l)";
        $files = $DB->rows($sql, $resource, array(uploader::STATUS_CREATE, uploader::STATUS_REMOVE));
        if($files) {
            foreach($files as $file) {
                $cFile = new CFile($file['file_id']);
                if($cFile->id) {
                    $cFile->delete($file['file_id']);
                }
            }
        }
        
        return $DB->query("DELETE FROM attachedfiles WHERE session = ?", $resource);
    }
    
    /**
     * Перевод статусов в удаленноу состояние в зависимости от текущего статуса
     *  
     * @param integer $status  текущий статус
     * @return int  
     */
    static function transStatus($status) {
        switch($status) {
            case self::STATUS_CREATE:
                return self::STATUS_REMOVE;
                break;
            case self::STATUS_ADDED:
                return self::STATUS_DELETE;
                break;
            default:
                return 0;
                break;
        }
    }
    
    /**
     * Удаляет определенные файлы из сессии
     * 
     * @param array $files   Список файлов (1,2,3)
     * @return boolean
     */
    public function removeFiles($files) {
        return self::sremoveFiles($this->resource, $files);
    }
    
    /**
     * Удаляет определенные файлы из сессии
     * @global object $DB
     * @param string $resource    Сессия загрузчика    
     * @param array  $files_id   Список файлов (1,2,3)
     * @return boolean
     */
    static function sremoveFiles($resource, $files_id) {
        global $DB;
        
        $sql = "SELECT * FROM attachedfiles WHERE session = ? AND file_id IN (?l)";
        $files = $DB->rows($sql, $resource, $files_id);
        
        foreach($files as $file) {
            $sql = "UPDATE attachedfiles SET status = ?i WHERE id = ?i";
            $DB->query($sql, self::transStatus($file['status']), $file['id']);
        }
        
        return true;
    }
    
    /**
     * Возвращает шаблоны загрузчика
     * 
     * @param string $name    Имя шаблона
     * @return type
     */
    static function getTemplate($name, $tpl = '') {
        ob_start();
        include ($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/{$tpl}tpl.{$name}.php");
        $html = ob_get_clean();
        return str_replace(array("\r", "\n", "'"), array("", "", "\'"), $html);
    }
    
    /**
     * Стандартная инициализация клиентовского скприта для загрузчика
     * 
     * @param string|array $uploader    Название элемента(ов) куда нужно инициализировать загрузчик
     * @param array $templates  Шаблоны для загрузчика (должен быть массив состоящий из 3-х шаблонов)
     */
    static function init($uploader, $templates = null, $type = '') {
        if($templates === null) {
            $template      = self::getTemplate('uploader');
            $fileTemplate  = self::getTemplate('uploader.file');
            $popupTemplate = self::getTemplate('uploader.popup');
        } else {
            list($template, $fileTemplate, $popupTemplate) = array_values($templates);
        }
        
        if(!is_array($uploader)) {
            $array[$uploader] = array();
            $uploader         = $array;
        }
        
        $elements = array();
        if ($uploader) {
            foreach ($uploader as $element => $upload) {
                $elements[] = "['" . $element . "', " . ($upload ? json_encode($upload) : 'null') . "]";        
            }
        }
        ?>
        <script>
            if (typeof window.uploaderSet == "undefined") {
                window.uploaderSet = [];
            }
            window.uploaderSet.push({
                template: '<?= $template; ?>',
                fileTemplate: '<?= $fileTemplate; ?>',
                popupTemplate: '<?= $popupTemplate;?>',
                WDCPREFIX: '<?= WDCPREFIX;?>',
                umask: '<?= self::umask($type); ?>',
                elements: [<?=implode(',', $elements)?>]
            });
        </script>    
        <? 
    }
    
    /**
     * Выдает тип загружаемого контента по его сессии
     * 
     * @return string
     */
    public function getTypeUpload() {
        return self::sgetTypeUpload($this->resource);
    }
    
    /**
     * Выдает тип загружаемого контента по его сессии
     * 
     * @param string $resource    Сессия загрузчика
     * @return string
     */
    static function sgetTypeUpload($resource) {
        return array_pop(explode('-', $resource));
    }
    
    /**
     * Кодируем все элементы массив в определенную кодировку
     * 
     * @param mixed $object      Элемент в котором будем кодировать
     * @param array $charset     Кодировка (0 - текущая, 1 - которую надо получить)
     * @return mixed
     */
    static function encodeCharset($object, $charset = array('cp1251', 'utf-8')) {
        if(is_array($object)) {
            return array_map(create_function('$a', 'if(is_array($a)) {
                                                        return uploader::encodeCharset($a, array("'.$charset[0].'", "'.$charset[1].'")); 
                                                    } elseif(is_string($a)) { 
                                                        return iconv("'.$charset[0].'", "'.$charset[1].'//IGNORE", $a); 
                                                    } else {
                                                        return $a; 
                                                    }'), $object);
        } else {
            return iconv($charset[0], $charset[1], $object);
        }
    }
    
    static function getRemoveCallback($type) {
        $callback = '';
        switch($type) {
            case 'portfolio':
                $button_text = 'Загрузить файл';
                $callback  = "if($('work_image')) $('work_image').dispose(); $('preview_overlay').hide();";
                $callback .= "$('file_upload_block_portf').removeClass('b-file_hover');";
                $callback .= "$$('#file_upload_block_portf .qq-upload-button .b-button__txt').set('text', '{$button_text}');";
                break;
            case 'pf_preview':
                $button_text = 'Загрузить картинку';
                $callback = "
                     if($('preview_image')) $('preview_image').dispose();
                     $('file_upload_block_preview').removeClass('b-file_hover');
                     $$('#file_upload_block_preview .qq-upload-delete').dispose();
                     $$('#file_upload_block_preview .qq-upload-button .b-button__txt').set('text', '{$button_text}');";
                break;
        }
        return str_replace(array("\r", "\n", "  "), " ", $callback);
    }
    
    static function getCallback($type, $CFile = null, $file_type = '') {
        $callback = '';
        switch($type) {
            case 'portfolio':
                $button_text = 'Заменить файл';
                if($CFile->image_size['type'] && $CFile->image_size['type'] != 13 && $CFile->image_size['type'] != 4) { // SWF сюда не входит
                    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php";
                    
                    if($CFile->image_size['height'] > portfolio::PREVIEW_MAX_HEIGHT || $CFile->image_size['width'] > portfolio::PREVIEW_MAX_WIDTH) {
                        $size = $CFile->_getImageSizeByAuto(portfolio::PREVIEW_MAX_HEIGHT, portfolio::PREVIEW_MAX_WIDTH);
                    } else {
                        $size = array(
                            'optimalWidth'  => $CFile->image_size['width'], 
                            'optimalHeight' => $CFile->image_size['height']
                        );
                    }
                    $callback = "
                        $('preview_overlay').show();
                        $('work_preview_file').hide();
                        $('file_upload_block_preview').removeClass('b-file_hover');
                        if($('work_image')) $('work_image').dispose();
                        var src = $$('#work_main_file .qq-upload-file').get('href');
                        var img = new Element('img', {'src' : src, 'class':'b-prev__pic', 'width':'{$size['optimalWidth']}', 'height':'{$size['optimalHeight']}', 'id': 'work_image' });
                        $('work_main_file').grab(img, 'before');
                        $('file_upload_block_portf').addClass('b-file_hover');
                        $$('#work_main_file .qq-upload-list .qq-upload-file-table').dispose();
                    ";
                } else {
                    $callback = "
                        if($('work_image')) $('work_image').dispose();
                        $('file_upload_block_portf').removeClass('b-file_hover');";
                }
                $callback .= "if($('remove_main_file')) $('remove_main_file').dispose();
                              var remove_link = $$('#file_upload_block_portf .qq-upload-delete')[0];
                              remove_link.set('id', 'remove_main_file');
                              remove_link.addEvent('click', function() {
                                    if($('work_image')) { $('work_image').dispose(); } 
                                    $('imain_file').set('value', ''); 
                                    $('file_upload_block_portf').removeClass('b-file_hover'); 
                                    $(this).dispose();
                              });
                              $('work_main_file').grab(remove_link, 'after');";
                $callback .= "$$('#file_upload_block_portf .qq-upload-button .b-button__txt').set('text', '{$button_text}');";
                if ( $file_type == "application/x-shockwave-flash" ) {
                    $callback .= "$('swf_params').setStyle('display', null).getParent('div#file_upload_block_portf').getElement('div.qq-upload-portfolio').setStyle('margin-top', null);";
                } else {
                	$callback .= "$('swf_params').setStyle('display', 'none').getParent('div#file_upload_block_portf').getElement('div.qq-upload-portfolio').setStyle('margin-top', null);";
                }
                break;
            case 'pf_preview':
                $button_text = 'Заменить картинку';
                $callback = "
                    if($('remove_preview_file')) $('remove_preview_file').dispose();
                    $('preview_overlay').hide();
                    $('work_preview_file').show();
                    if($('preview_image')) $('preview_image').dispose();
                    var src = $$('#work_preview_file .qq-upload-file').get('href');
                    var img = new Element('img', {'src' : src, 'class':'b-prev__pic', 'id': 'preview_image' });
                    $('work_preview_file').grab(img, 'before');
                    $$('#work_preview_file .qq-upload-list .qq-upload-file-table').dispose();
                    $('file_upload_block_preview').addClass('b-file_hover');
                    var remove_link = $$('#file_upload_block_preview .qq-upload-delete')[0];
                    remove_link.set('id', 'remove_preview_file');
                    remove_link.addEvent('click', function() {
                        if($('preview_image')) $('preview_image').dispose(); 
                        $('ipreview_file').set('value', ''); 
                        $('file_upload_block_preview').removeClass('b-file_hover'); 
                        $(this).dispose();
                    });
                    $('work_preview_file').grab(remove_link, 'after');
                    $$('#file_upload_block_preview .qq-upload-button .b-button__txt').set('text', '{$button_text}');
                ";
                break;
            case 'wisywig':
                $callback = "";
                break;
            default:
                $callback = "";
                break;
        }
        return str_replace(array("\r", "\n", "  "), " ", $callback);
    }
    
    /**
     * Возвращает валидационные данные по конкретным типам загрузки
     * 
     * @param string $type    Тип загрузки
     * @return array
     */
    static function getValidationInfo($type) {
        $validate = array(
            'is_total_size' => false,
            'is_auth'       => true,
            'fname_length'  => 25,
            'dir'           => $_SESSION['login'] . "/attach"
        );
        
        switch($type) {
            case 'portfolio':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
                $validate = array_merge($validate, 
                    array(
                        'max_files'      => 1,
                        'max_file_size'  => uploader::MAX_FILE_SIZE,
                        'is_total_size'  => uploader::IS_TOTAL_SIZE,
                        'fname_length'   => portfolio::FILE_NAME_LENGTH_EDIT,
                        'is_replace'     => true,
                    )
                );
                break;
            case 'pf_preview':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
                $validate = array_merge($validate, 
                    array(
                        'max_files'      => 1,
                        'max_file_size'  => uploader::MAX_FILE_SIZE,
                        'is_total_size'  => uploader::IS_TOTAL_SIZE,
                        'is_replace'     => true, // При очередной загрузке старый файл удалится новый заменит его доступно только для max_files == 1
//                        'maxImageHeight' => portfolio::PREVIEW_MAX_HEIGHT,
//                        'maxImageWidth'  => portfolio::PREVIEW_MAX_WIDTH,
                        'resize'         => true, // Изменяем размер изображения или нет
                        'imageWidth'     => portfolio::PREVIEW_MAX_WIDTH, // Размер конечного изображения которое должно получится
                        'imageHeight'    => portfolio::PREVIEW_MAX_HEIGHT, // Размер конечного изображения которое должно получится
                        'imageTypes'     => array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_ICO, IMAGETYPE_JPEG2000),
                        'imageOnly'      => true, // Загружать можно только картинки
                    )
                );
                break;
            case 'wysiwyg':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mailer.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => uploader::MAX_FILES,
                        'max_file_size' => uploader::MAX_FILE_SIZE,
                        'is_total_size' => uploader::IS_TOTAL_SIZE,
                        'maxImageHeight' => 2700,
                        'maxImageWidth'  => 840,
                        'imageTypes'     => array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_ICO, IMAGETYPE_JPEG2000),
                        'imageOnly'      => true, // Загружать можно только картинки
                        'is_admin'       => true, // Загрузка доступна только админам
                        'copy_table'     => 'file_mailer'  // Загрузить сразу на сервер
                    )
                );    
                break;
            case 'contacts':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => messages::MAX_FILES,
                        'max_file_size' => messages::MAX_FILE_SIZE
                    )
                );
                break;
            case 'blog':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => blogs::MAX_FILES,
                        'max_file_size' => blogs::MAX_FILE_SIZE
                    )
                );
                break;
            case 'prj_abuse':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => tmp_project::MAX_FILE_COUNT,
                        'max_file_size' => tmp_project::MAX_FILE_SIZE,
                        'fname_length'  => 18,
                        'imageOnly'     => true, // Загружать можно только картинки
                        'is_total_size' => true,
                    )
                );
                break;
            case 'project':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => tmp_project::MAX_FILE_COUNT,
                        'max_file_size' => tmp_project::MAX_FILE_SIZE,
                        'is_total_size' => true,
                    )
                );
                break;
            case 'guest_prj':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => tmp_project::MAX_FILE_COUNT,
                        'max_file_size' => tmp_project::MAX_FILE_SIZE,
                        'is_total_size' => true,
                        'is_auth'       => false,
                        'server_root'   => true,
                        'dir'           => uploader::DEFAULT_TEMPLATE                     
                    )
                );
                break;            
            
            case 'mailer':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mailer.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => mailer::MAX_FILE_COUNT,
                        'max_file_size' => mailer::MAX_FILE_SIZE
                    )
                );
                break;
            case 'commune':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => commune::MAX_FILE_COUNT,
                        'max_file_size' => commune::MAX_FILE_SIZE
                    )
                );
                break;
            case 'sbr_arb':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => 1,
                        'max_file_size' => sbr_stages::ARB_FILE_MAX_SIZE
                    )
                );
                break;
            case 'sbr':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => sbr::MAX_FILES,
                        'max_file_size' => sbr::MAX_FILE_SIZE
                    )
                );
                break;
            case 'finance_doc':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => account::MAX_FILE_COUNT,
                        'max_file_size' => account::MAX_FILE_SIZE
                    )
                );
                break;
            case 'finance_other':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
                
                $validate = array_merge($validate, 
                    array(
                        'max_files'     => account::MAX_FILE_COUNT,
                        'max_file_size' => account::MAX_FILE_SIZE,
                        'dir'           => $validate['dir'] . "/{$type}"
                    )
                );
                break;
            default:
                $validate = array(
                    'max_files'     => uploader::MAX_FILES,
                    'max_file_size' => uploader::MAX_FILE_SIZE,
                    'is_total_size' => uploader::IS_TOTAL_SIZE,
                    'is_auth'       => false,
                    'server_root'   => true,
                    'dir'           => uploader::DEFAULT_TEMPLATE
                );
                break;
        }
        return $validate;
    }
    
    /**
     * Слушатель который обрабатывает на серверной стороне поступление файла
     * 
     * @param string $resource  Сессия загрузчика
     * @return boolean
     */
    static function listener($resource) {
        $browser = '';
        $version = array();
        browserCompat($browser, $version);
        // Скрипт посылает в той кодировке в которой страница в IE, в остальных браузерах в UTF-8
        if($browser != 'msie' && ( (int) $version[1] != 8 || (int) $version[1] != 10) ) { 
            $_FILES   = self::encodeCharset($_FILES, array('utf-8' , 'cp1251'));
        }
        $validate = self::getValidationInfo(self::sgetTypeUpload($resource));
        
        if($validate['is_auth'] && !get_uid(false)) {
            return array('success' => false, 'error' => 'Ошибка загрузки файлов');
        }
        if($validate['is_admin'] && !hasPermissions('adm')) {
            return array('success' => false, 'error' => 'Ошибка загрузки файлов');
        }
        
        if($validate['max_files'] == 1 && $validate['is_replace']) { // Удаляем старые файлы для замены на новые
            self::sclear($resource);
        }
        
        $_FILES['qqfile']['name'] = stripslashes(__paramValue('string', $_FILES['qqfile']['name']));
        
        $CFile              = new CFile($_FILES['qqfile']);
        $CFile->table       = 'file';
        $CFile->server_root = $validate['server_root'] === true ? true : false;
        $CFile->max_size    = $validate['max_file_size'];
        
        $uploader      = new uploader($resource);
        $resourceInfo  = $uploader->getCountResource();
        
        if($validate['imageOnly'] && strpos($_FILES['qqfile']['type'], 'image') === false) {
            $file['error'] = "Недопустимый формат файла";
        }
        
        if( ($resourceInfo['count']+1) > $validate['max_files'] ) {
            $file['error'] = "Максимальное количество файлов: {$validate['max_files']}";
        }
        
        if( ($resourceInfo['size'] + $CFile->size) > $validate['max_file_size'] && $validate['is_total_size']) {
            $file['error'] = "Максимальный объем файлов: ".ConvertBtoMB($validate['max_file_size']);
        }

        if( in_array($CFile->getext(), $GLOBALS['disallowed_array']) ) {
            $file['error'] = "Недопустимый формат файла";
        }
        
        if($file['error']) {
            $file['success'] = false;
            return $file;
        } else {
            $CFile->MoveUploadedFile($validate['dir']);
            
            if($validate['resize'] && ( $CFile->image_size['height'] > $validate['imageHeight'] || $CFile->image_size['width'] > $validate['imageWidth']) ) {
                $CFile = $CFile->resizeImage($CFile->path . $CFile->name, $validate['imageWidth'], $validate['imageHeight'], 'auto', true);
            }
        }
        $skipEncoding = false;
        if($CFile->id) {
            // если заданы типы графических файлов
            if ($validate['imageTypes'] && ( !$CFile->image_size['type'] || !in_array($CFile->image_size['type'], $validate['imageTypes']) ) ) {
                $file['error'] = "Недопустимый формат файла";
            }                    

            // если задана максимальная высота
            if ($validate['maxImageHeight'] && $CFile->image_size['height'] > $validate['maxImageHeight']) {
                $file['error'] = "Превышена максимальная высота изображения";
            }
            // если задана максимальная ширина
            if ($validate['maxImageWidth'] && $CFile->imae_size['width'] > $validate['maxImageWidth']) {
                $file['error'] = "Превышена максимальная ширина изображения";
            }

            if($file['error']) {
                $CFile->Delete($CFile->id);
                $file['success'] = false;
            } else {
                $file = $uploader->createFile($CFile);
                
                if($validate['copy_table'] != '') {
                    $cremotefile = self::remoteCopy($CFile->id, $validate['copy_table'], mailer::FILE_DIR);
                    $file['path']  = $cremotefile->path;
                    $file['fname'] = $cremotefile->name;
                }
                
                $file['success'] = true;
            }
        } else {
            $file['success'] = false;
            $file['error']   = $CFile->error;
            if ( is_array($file['error']) && count( $file['error'] ) == 0 && $CFile->size == 0) {
                $file['error'] = "Пустой файл";
                $skipEncoding = true;
            }
        }
        
        $file['onComplete'] = self::getCallback(self::sgetTypeUpload($resource), $CFile, $_FILES['qqfile']['type']);
        $file['orig_name']  = self::cutNameFile($file['orig_name'], $validate['fname_length']);
        if (!$skipEncoding) {
           $file = self::encodeCharset($file);
        }
        
        return $file;
    }
    
    /**
     * Обрезаем название файла
     * 
     * @param string  $name    Название файла
     * @param integer $length  Размер который должен быть.
     * @return string
     */
    static public function cutNameFile($name, $length = 0) {
        if($length > 0) {
            $ext = strrchr($name, '.');
            if(strlen($ext) != 0) {
                $name = substr($name, 0, - (strlen($ext)));
            }
            if(strlen($name) > $length) {
                $name  = substr($name, 0, $length) . '..' . $ext;
            } else {
                $name .= $ext;
            }
        }
        return $name;    
    }
            
    /**
     * Перемещаем файл куда надо
     * 
     * @param integer $id_file     ИД файла который нужно переместить
     * @param string  $copy_table  Таблица в которую перемещаем
     * @param string  $dir         Директория в которую копируем
     * @return \CFile
     */
    function remoteCopy($id_file, $copy_table, $dir, $new_name = true, $prefix = 'f_') {
        $CFile        = new CFile($id_file);
        $CFile->table = $copy_table;
        if($new_name) {
            $tmp_name = $CFile->secure_tmpname($dir, '.'.$CFile->getext(), $prefix);
            $tmp_name = substr_replace($tmp_name, "", 0, strlen($dir));
        } else {
            $tmp_name = $prefix . str_replace(array("f_", "sm_"), "", $CFile->name);
        }
        $CFile->_remoteCopy($dir . $tmp_name);
        return $CFile;
    }
}
