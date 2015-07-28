<?php

/**
 * подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Путь до шаблонов
 */
if(!defined('SBR_FOLDER_DOCS')) {
    define("SBR_FOLDER_DOCS", $_SERVER['DOCUMENT_ROOT'] . "/norisk2/docs/");
}

if(!defined('SBR_FOLDER_TMP')) {
    define("SBR_FOLDER_TMP", '/var/tmp/sbr_docs/');
}

if(!defined('ODT2PDF_OPTIONS_EXEC')) {
    define("ODT2PDF_OPTIONS_EXEC", '--format=pdf --timeout=30 --stdout');
}

if(!defined('ODT2PDF_UNOCONV_EXEC')) {
    define("ODT2PDF_UNOCONV_EXEC", 'unoconv -v ');
}
/**
 * Класс для генерации из формата ODT в PDF с обработкой дополнительных переменных 
 */
class odt2pdf
{
    /**
     * Данные файла ODT
     * 
     * @var string 
     */
    protected $_content = "";
    
    /**
     * Путь к шаблонам ODT
     * 
     * @var string 
     */
    protected $_folder = SBR_FOLDER_DOCS;
    
    
    protected $_tmp = SBR_FOLDER_TMP;
    
    /**
     * Шаблон документа который необходимо обрабатывать
     * 
     * @var string 
     */
    public $doc    = "test.odt";
    
    /**
     * Строка запуска unoconv 
     * 
     * @var string
     */
    public $programm_exec = ODT2PDF_UNOCONV_EXEC;
    
    /**
     * Опции запуска ковертации @see unoconv --help
     * 
     * @var string
     */
    public $option_exec = ODT2PDF_OPTIONS_EXEC;
    
    /**
     * Название файла с данными который мы обрабатываем и берем из формата ODT
     * 
     * @var string 
     */
    public $content_file = "content.xml";
    
    /**
     * Название обработанного сконвертированного файла
     * 
     * @var string 
     */
    public $convert_file = "";
    
    /**
     * Путь до папки с сгенерированными PDF
     * 
     * @var string 
     */
    public $outputpath   = SBR_FOLDER_TMP;
    
    /**
     * Маска переменных в шаблоне
     * 
     * @var string 
     */
    public $mask_vars   = "{%s}";
    
    /**
     * Перенос строки в формате ODT 
     */
    const LINE_BREAK     = "<text:line-break/>";
    
    /**
     * @deprecated
     * Маска условия в шаблоне ODT
     */
    const MASK_CONDITION = "IFSHOW";
    
    /**
     * Архив открыт или нет
     * 
     * @var boolean
     */
    public $opened = false;
    
    /**
     * Работа с ZIP архивами
     * 
     * @var object ZipArchive
     */
    public $zip;
    
    public $log_unoconv = '/var/tmp/unoconv.log';
    
    /**
     * Конструктор класса
     * 
     * @param string $doc Шаблон документа 
     */
    public function __construct($doc = false) {
        if($doc) $this->doc = $doc;
        $this->log = new log('odt2pdf/odt2pdf-'.SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }
    
    /**
     * Подготовка к обработке файла шаблона
     * Создаем новый фыйл посредством копирования шаблона
     * 
     * @return boolean 
     */
    public function prepareFile() {
        $fname = $this->getFolder() . $this->doc;
        if(!file_exists($fname)) return false;
        $this->convert_file = $this->generateNameFile();
        $this->file_path = $this->getTmpFolder() . $this->convert_file . ".odt";
        
        return copy($fname, $this->file_path);
    }
    
    /**
     * Генерируем название нового файла
     * 
     * @return string 
     */
    public function generateNameFile() {
        return substr(md5(microtime()), 0, 6);
    }
    
    /**
     * Инифиализация открытия архива
     * 
     * @return boolean 
     */
    public function initZipOpenFile() {
        $this->zip = new ZipArchive;
        if ($this->zip->open($this->file_path)) {
            $this->opened = true;
            return true;
        }
        return false;
    }
    
    /**
     * Вносим изменения данных в шаблон
     * 
     * @param string $content     Измененные данные
     * @return boolean 
     */
    public function setContentFile($content) {
        if($this->opened) {
            return $this->zip->addFromString($this->content_file, $content);
        }
        return false;
    }
    
    /**
     * Берем данные из шаблона 
     * 
     * Данные находятся в self::$content_file файле 
     */
    public function getContentFile() {
        if ($this->opened) {
            if (($index = $this->zip->locateName($this->content_file, ZIPARCHIVE::FL_NOCASE)) !== false) {
                $this->setContent($this->zip->getFromIndex($index));
            }
        }
    }
    
    /**
     * Обрабатываем дополнительные переменные в шаблоне
     * 
     * @param array $variables   Переменные формата array('$name' => 'Название')
     * @return array(key, $var)  Возвращает массив с обработанными ключами и значениями к ним 
     */
    public function prepareVariables($variables) {
        $keys = array_keys($variables);
        $vals = array_values($variables);
        
        foreach($vals as $k=>$val) {
            // Борьба с значениями приходящими не как сущности (например -- &)
            $val = iconv("windows-1251", "utf-8", $val);
            $val = html_entity_decode($val, ENT_QUOTES, 'UTF-8');
            $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8', false); // Теперь все НЕ сущности переводим в сущности
            $val = str_replace("\r", "", $val);
            $val = str_replace("\n", self::LINE_BREAK, $val);
            $val = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $val);
            if($val == '') continue; // Такие удалим способом clearEmptyVariables();
            $vals[$k] = $val;
        }
        
        foreach($keys as $i=>$key) {
            $keys[$i] = sprintf($this->mask_vars, $key);
        }
        
        return array($keys, $vals);
    }
    
    /**
     * Функция обрабатывает переменные для нахождения и удаления стиля если его не надо отображать 
     * 
     * В OpenOffice стиль задается в соответствии с переменной например $emp_init_arb - и больше ничего делать не надо
     * Любая строка обозначенная даным стилем в документе - если переменная $variables['$emp_init_arb'] = false -- удалится из документа
     * 
     * @param type $variables
     * @return type 
     */
    public function prepareConditionsVar($variables) {
        $keys = array_keys($variables);
        foreach($keys as $i=>$key) {
            $keys[$i] = str_replace(array('_', '$'), array('_5f_', '_24_'), $key);
        }
        
        return $keys;
    }
    
    /**
     * Чистим данные в шаблоне
     * Бывает так что конструкция вида {$name} -- в шаблоне идет не в одну строку и имеет вид <text:p><text:p>{</text:p>$name<text:p>}</text:p></text:p>
     * Чтобы исключить такие вероятности чистим шаблон
     * 
     * @param string $content Данные из шаблона
     * @return type 
     */
    public function clearDataVar($content) {
        $content = preg_replace("/<text([^>])+>\}<\/text([^>])+>/mix", "}", $content);
        $content = preg_replace("/<text([^>])+>\{<\/text([^>])+>/mix", "{", $content);
        
        return $content;
    }
     
    /**
     * Парсим структуру данных шаблона, обрабатываем условия, заменяем все переменные на значения
     * 
     * @param string $content     Данные из шаблона (файл self::$content_file)
     * @param array  $variables   Переменные формата array('$name' => 'Название')
     * @return string
     */
    public function parseStructure($content, $variables) {
        list($keys, $vals) = $this->prepareVariables($variables);
        $content = $this->clearDataVar($content);
        $condition_keys = $this->prepareConditionsVar($variables);
        
        $dom = new DOMDocument('1.0');
        $dom->loadXML($content);
        $xpath = new DOMXPath($dom);
        
        foreach($condition_keys as $key=>$condition) {
            if(!is_bool($vals[$key])) continue;
            
            if($vals[$key] == false) {
                $find = '//text:p[@text:style-name= "'.$condition.'"]';
                
                $element = $xpath->query($find, $dom->documentElement);
                if($element->length > 0) {
                    for($i=0;$i<$element->length;$i++) {
                        $remove_element[] = $element->item($i);
                    }
                }
            }
        }
        if($remove_element) {
            foreach($remove_element as $element) {
                $parent = $element->parentNode;
                $parent->removeChild($element);
            }
            $content = $dom->saveXML();
        }
        
        $content = str_replace($keys, $vals, $content);
        return $this->clearEmptyVariables($content);
    }
    
    /**
     * Чистим не успользуемые переменные
     * 
     * @param string $content Данные из шаблона (файл self::$content_file)
     * @return string 
     */
    public function clearEmptyVariables($content) {
        if(preg_match_all('/({\$.*?})/mix', $content, $matches)) {
            $vars = array_map('trim', $matches[1]);
            foreach($vars as $var) {
                $var = str_replace('$', '\$', $var);
                $content = preg_replace('/<text([^>])+>'. $var . '.*?<\/text([^>])+>/mix', "", $content);
            }
            return str_replace($vars, '', $content);
        }
        return $content;
    }
    
    /**
     * Поиск условий в шаблоне
     * 
     * @deprecated
     * @param string $content Данные из шаблона
     * @return array 
     */
    public function conditions($content) {
        if(preg_match_all("#{".self::MASK_CONDITION."=(.*?)}#", $content, $matches)) {
            return array_map('trim', $matches[1]);
        }
        return array();
    }
    
    /**
     * Конвертация из ODT в PDF
     * 
     * @param array  $replace     Переменные для замены формата array('$name' => 'Название'). если false, то парсинг шаблона не будет инициирован
     * @param string $filename    Название файла, если нет, то файл pdf не будет сохранен
     */
    public function convert($replace = false, $filename = "") {
        if($this->prepareFile()) {
            
            if($this->initZipOpenFile()) {

                $this->getContentFile();
                
                if(is_array($replace)) {
                    $toContent = $this->parseStructure($this->getContent(), $replace);
                    $this->setContentFile($toContent);
                }
                
                $this->zip->close();
                $this->execConvert();
//                if($exec != '') {
//                    $this->log->writeln("unoconv закончил работу -- {$exec}");
//                }
//                if(!file_exists($this->outputpath . $this->convert_file . ".pdf")) {
//                    $this->log->writeln("Template: {$this->doc}");
//                    $this->log->writeln("Ошибка конвертации unoconv (проверте работоспособность unoconv или soffice) (file not exists -- {$this->outputpath}{$this->convert_file}.pdf)");
//                    unlink($this->file_path);
//                    return false;
//                }
//                $this->output = $this->getOutput();
                if($this->output == '') {
                    $this->log->writeln("Ошибка конвертации шаблона (проверте шаблон на повержденные участки (не валидный xml внутри шаблона) -- {$this->doc})");
                }
                $this->remove();
                
                if($filename != "" && $this->output != "") {
                    file_put_contents($this->getTmpFolder() . $filename, $this->output);
                }
            } else {
                $this->log->writeln("Template: {$this->doc}");
                $this->log->writeln("Ошибка открытия архива -- {$this->file_path}");
            }
        } else {
            $fname = $this->getFolder() . $this->doc;
            $this->log->writeln("Ошибка клонирования файла шаблона -- {$fname}");
        }
    }
    
    /**
     * Данные на выходе после конвертации
     * 
     * @return string
     */
    public function getOutput() {
        $content = file_get_contents($this->outputpath . $this->convert_file . ".pdf");
        return $content;
    }
    
    /**
     * Функция заглушка для повторения интерфейса функции класса FPDF::Output($name='', $dest='')
     * Всегда возвращает данные конвертации то есть работает как вызов FPDF::Output(NULL, 'S')
     * 
     * @param type $a
     * @param type $b
     * @return type 
     */
    public function output($a = null, $b = 'S') {
        return $this->output;
    }
    
    /**
     * Удаление временных файлов необходимых для конвертации 
     */
    public function remove() {
        if(file_exists($this->outputpath . $this->convert_file . ".pdf")) unlink($this->outputpath . $this->convert_file . ".pdf");
        if(file_exists($this->file_path)) unlink($this->file_path);
    }
    
    /**
     * Запуск процесса конвертации
     * 
     * @return string
     */
    public function execConvert() {
        $this->output = shell_exec("{$this->programm_exec} {$this->option_exec} -o {$this->outputpath} {$this->file_path} ");
//        $this->log->writeln($out);
        return $this->output;
    }
    
    /**
     * Задаем данные из шаблона
     * 
     * @param string $content Данные
     */
    public function setContent($content) {
        $this->_content = $content;
    }
    
    /**
     * Берем данные из шаблона
     * 
     * @return string
     */
    public function getContent() {
        return $this->_content;
    }
    
    /**
     * Задаем папку с шаблонами
     * 
     * @param string $folder Путь до папки с шаблонами
     */
    public function setFolder($folder) {
        $this->_folder = $folder;
    }
    
    /**
     * Возвращаем путь до папки с шаблонами
     * 
     * @return string
     */
    public function getFolder() {
        return $this->_folder;
    }
    
    public function getTmpFolder() {
        if(!file_exists($this->_tmp)) {
            mkdir($this->_tmp, 0777);
        }
        return $this->_tmp;
    }
}


?>
