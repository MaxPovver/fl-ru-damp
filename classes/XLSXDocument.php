<?php

/**
 * Класс для генерации отчетности документов ИТО в формате XLSX
 */
class XLSXDocument_ITO extends odt2pdf {
    
    /**
     * Название файла с текстовыми данными
     * 
     * @var string
     */
    private $_sharedStrings = 'xl/sharedStrings.xml';
    
    /**
     * Название файла с данными рабочего листа
     * 
     * @var string
     */
    private $_workSheet     = 'xl/worksheets/sheet1.xml';
    
    /**
     * Название файла с данными о формалах используемых в документе
     * 
     * @var string
     */
    private $_calcChain     = 'xl/calcChain.xml';
    
    /**
     * Стили отображения
     * 
     * @var string
     */
    private $_styles        = 'xl/styles.xml';
    
    /**
     * Шаблон документа
     * 
     * @var string
     */
    private $_template;
    
    /**
     * Путь к шаблонам ODT
     * 
     * @var string 
     */
    protected $_folder = SBR_FOLDER_DOCS;
    
    /**
     * Путь к временной папке
     * 
     * @var string
     */
    protected $_tmp    = SBR_FOLDER_TMP;
    
    /**
     * Индекс файла текстов в архиве
     * 
     * @var integer
     */
    protected $_sharedIndex;
    
    /**
     * Индекс файла каркаса в архиве
     * 
     * @var integer
     */
    protected $_sheetIndex;
    
    /**
     * Индекс файла формул в архиве
     * 
     * @var integer
     */
    protected $_calcIndex;
    
    /**
     * Индекс файла стилей в архиве
     * 
     * @var integer
     */
    protected $_styleIndex;
    
    /**
     * Номер строки с которой начинаем вставлять данные
     * 
     * @var integer
     */
    protected $_startPosition = 25; // Строка с которой начинаем вставлять данные
    
    /**
     * Хранятся инициализированные DOMDocument
     * 
     * @var array
     */
    public $dom = array();
    
    /**
     * Хранятся инициализированные DOMXPath
     * 
     * @var array
     */
    public $xpath = array();
     
    public $debug = false;
    
    /**
     * Конструктор класса
     * 
     * @param string $template      Название шаблона ск оторым будем работать  
     */
    public function __construct($template = 'tpl_ito.xlsx') {
        $this->setTemplate($template);
    }
    
    /**
     * Задаем шаблон документа
     * 
     * @param string $template  Название шаблона ск оторым будем работать  
     * @throws Exception
     */
    public function setTemplate($template) {
        try {
            if( !file_exists($this->_folder . DIRECTORY_SEPARATOR . $template) ) {
                throw new Exception('Template file does not exists.');
            }
            $this->_template = $template;
        } catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     * Возвращает название шаблона
     * 
     * @return string
     */
    public function getTemplate() {
        return $this->_template;
    }
    
    /**
     * Кодируем текст в UTF8
     * 
     * @param string $val   Текст для кодирования
     * @return type
     */
    private function _enc($val) {
        return iconv('cp1251', 'utf8', $val);
    }
    
    /**
     * Подготовка к обработке файла шаблона
     * Создаем новый фыйл посредством копирования шаблона
     * 
     * @return boolean 
     */
    public function prepareFile() {
        $fname = $this->getFolder() . DIRECTORY_SEPARATOR . $this->getTemplate();
        $this->convert_file = $this->generateNameFile();
        $this->file_path = $this->getTmpFolder() . $this->convert_file . ".xlsx";
        
        return copy($fname, $this->file_path);
    }
    
    /**
     * Вносим изменения данных в шаблон
     * 
     * @param string $file_path   Путь файла в архиве который меняем
     * @param string $content     Измененные данные
     * @return boolean 
     */
    public function setContentFile($file_path, $content) {
        if($file_path == null) return false;
        if($this->opened) {
            return $this->zip->addFromString($file_path, $content);
        }
        return false;
    }
    
    /**
     * Задаем период поиска данных для генерации документа
     * 
     * @param array $period (01-01-2012,30-01-2012)
     */
    public function setPeriod($period) {
        $this->period = $period;
    }
    
    /**
     * Генерируем новый документ
     * 
     * @param boolean $save    Сохарнить документ на сайте или нет
     * @return boolean
     */
    public function generateDocument($save = false) {
        set_time_limit(0);
        if($this->prepareFile()) {
            
            if($this->initZipOpenFile()) {
                $this->parseContent();
                
                if($save) {
                    return $this->saveFile();
                }
            }
        }
    }
    
    /**
     * Возвращает данные обработканного файла
     * 
     * @return string
     */
    public function getOutput() {
        $content = file_get_contents($this->file_path);
        return $content;
    }
    
    /**
     * Сохраняем обработанный файл в системе
     * 
     * @return \CFile
     */
    public function saveFile() {
        $login = 'admin';
        $content = $this->getOutput();

        $file = new CFile();
        $file->path = "users/" . substr($login, 0, 2) . "/{$login}/upload/";
        $file->name = basename($file->secure_tmpname($file->path, '.xlsx'));
        $file->size = strlen($content);
        if ($file->putContent($file->path . $file->name, $content)) {
            return $file;
        }
    }
    
    /**
     * Обрабатываем данные документа и изменяем их если нужно
     * 
     */
    public function parseContent() {
        if ($this->opened) {
            $this->_sharedIndex = $this->zip->locateName($this->_sharedStrings, ZIPARCHIVE::FL_NOCASE);
            $this->_sheetIndex  = $this->zip->locateName($this->_workSheet, ZIPARCHIVE::FL_NOCASE);
            $this->_calcIndex   = $this->zip->locateName($this->_calcChain, ZIPARCHIVE::FL_NOCASE);
//            $this->_styleIndex  = $this->zip->locateName($this->_styles, ZIPARCHIVE::FL_NOCASE);
            
            $this->initDOMDocument('shared', $this->_sharedIndex);
            $this->initDOMDocument('sheet',  $this->_sheetIndex);
//            $this->initDOMDocument('style',  $this->_styleIndex);
            $this->initDOMDocument('calc',  null); // По формулам создадим новый документ
           
            $pskb = sbr_meta::getReservedSbr($this->period);
            $count_rows = count($pskb);
            
            $from_date = date('d.m.Y', strtotime($this->period[0]));
            $to_date   = date('d.m.Y', strtotime($this->period[1]));
            
            $period = "за период с {$from_date} по {$to_date}";
            $this->replaceSharedString(4, $period);
            $this->moveFooter($count_rows);
            foreach($pskb as $i=>$data) {
                $this->setOneRowTable($i, $data);
            }
            $this->generateFormulaData();
            
            $this->setContentFile($this->_sharedStrings, $this->dom['shared']->saveXML());
            $this->setContentFile($this->_workSheet, $this->dom['sheet']->saveXML());
            $this->setContentFile($this->_calcChain, $this->dom['calc']->saveXML());
            
            // Все гуд закрываемся
            $this->zip->close();
        }
    }
    
    /**
     * Сдвигает футер на определенное количество строк
     * 
     * @param integer $rows Количество строк
     */
    public function moveFooter($rows = 1) {
        // Позиция от которой пойдут отсупы (строка ИТОГО)
        $position = $this->_startPosition;
        $row = $this->xpath['sheet']->query('//p:row[@r= "' . $position . '"]', $this->dom['sheet']->documentElement)->item(0);
        
        do {
            $now_position = (int) $row->getAttribute('r');
            $replace[$now_position] = ($now_position + $rows); // Заменяем позицию

            $row->setAttribute('r', $replace[$now_position]);
            for ($i = 0; $i < $row->childNodes->length; $i++) {
                $c  = $row->childNodes->item($i);
                $rc = $c->getAttribute('r');
                
                if ($rc == "H{$this->_startPosition}") {
                    $fv = ($position - 1) + $rows;
                    $f = $this->dom['sheet']->createElement('f', $this->_enc("SUM(H{$this->_startPosition}:I{$fv})"));
                    $c->appendChild($f);
                }
                $rc = str_replace($now_position, $replace[$now_position], $rc);
                $c->setAttribute('r', $rc);
            }
        } while ( ($row = $row->nextSibling) );
        
        // Смещаем позиции в объединениях
        $mergeCells   = $this->dom['sheet']->getElementsByTagName('mergeCells')->item(0);
        $find_replace = array_keys($replace);

        for($i=0; $i < $mergeCells->childNodes->length; $i++) {
            $node = $mergeCells->childNodes->item($i);
            $ref  = $node->getAttribute('ref');
            list($from, $to) = explode(":", $ref);
            $from = preg_replace("/\D+/", "", $from);
            if( in_array($from, $find_replace) ) {
                $ref = str_replace($from, $replace[$from], $ref);
                $node->setAttribute('ref', $ref);
            }
        }
    }
    
    /**
     * Добавляем строку в таблицу
     * 
     * @param integer $n       Номер строки
     * @param array   $data    Данные добавления
     */
    public function setOneRowTable($n, $data) {
        $pos = $this->_startPosition + $n;
        // Ищем предыдущий элемент
        if(!$this->prevRow) {
            $this->prevRow   = $this->xpath['sheet']->query('//p:row[@r= "' . ($pos - 1) . '"]', $this->dom['sheet']->documentElement)->item(0);
            // Стили таблицы
            for($i = 0;$i<$this->prevRow->childNodes->length;$i++) {
                $snode = $this->prevRow->childNodes->item($i);
                $this->style_table[str_replace($pos - 1, '', $snode->getAttribute('r'))] = $snode->getAttribute('s');
            }
        }
        $row  = $this->dom['sheet']->createElement('row');
        $row->setAttribute('r', $pos);
        $row->setAttribute('spans', "1:9");
        $row->setAttribute('customHeight', "1");
        $row->setAttribute('x14ac:dyDescent', "0.2");
        
        $c = $this->dom['sheet']->createElement('c');
        $v = $this->dom['sheet']->createElement('v');
        $f = $this->dom['sheet']->createElement('f'); // формула
        
        $name_emp = $this->_enc($data['nameCust']);
        $sbr_id   = $this->_enc("№ {$data['sbr_id']}, ".date('d.m.Y H:i', strtotime($data['covered'])));
        $lc_id    = $this->_enc("№ {$data['lc_id']}");
        $cost     = $this->_enc($data['cost']);
        
        $len_name = strlen($data['nameCust']);
        $height   = ceil($len_name / 33) * 18;
        $row->setAttribute('ht', $height);
        
        // Столбец "п/п"
        if($pos == $this->_startPosition) {
            $cell['A'] = $this->createOneCell($c, $v, array('r' => "A{$pos}", 's' => $this->style_table['A']), "1");
        } else {
            $R = $pos-1;
            $cell['A'] = $this->createOneCell($c, $f, array('r' => "A{$pos}", 's' => $this->style_table['A']), "A{$R}+1");
        }
        
        // Столбец "Наименование заказчика"
        $cell['B'] = $this->createOneCell($c, $v, array('r' => "B{$pos}", 's' => $this->style_table['B'], 't' => 's'), $this->createSharedTextItem($name_emp));
        $cell['C'] = $this->createOneCell($c, $v, array('r' => "C{$pos}", 's' => $this->style_table['C']));
        
        // Столбец "Соглашение № дата"
        $cell['D'] = $this->createOneCell($c, $v, array('r' => "D{$pos}", 's' => $this->style_table['D'], 't' => 's'), $this->createSharedTextItem($sbr_id));
        $cell['E'] = $this->createOneCell($c, $v, array('r' => "E{$pos}", 's' => $this->style_table['E']));
        
        // Столбец "Идентификатор аккредитива"
        $cell['F'] = $this->createOneCell($c, $v, array('r' => "F{$pos}", 's' => $this->style_table['F'], 't' => 's'), $this->createSharedTextItem($lc_id));
        $cell['G'] = $this->createOneCell($c, $v, array('r' => "G{$pos}", 's' => $this->style_table['G']));
        
        // Столбец "Сумма перевода денежных средств"
        $cell['H'] = $this->createOneCell($c, $v, array('r' => "H{$pos}", 's' => $this->style_table['H']), $cost);
        $cell['I'] = $this->createOneCell($c, $v, array('r' => "I{$pos}", 's' => $this->style_table['I']));
        
        foreach($cell as $node) {
            $row->appendChild($node);
        }
        
        $this->prevRow = $this->dom['sheet']->getElementsByTagName('sheetData')->item(0)->insertBefore($row, $this->prevRow->nextSibling);
        $this->generateMergeForNewRow($pos);
    }
    
    /**
     * Генерирует ячейку, вставляет данные
     * 
     * @param DOMElement $c     Ячейка таблицы
     * @param DOMElement $v     Данные ячейки (может быть как просто данные так и формула)
     * @param array $attributes Аттрибуты ячейки
     * @param string $value     Значение ячейки    
     * @return DOMNode
     */
    public function createOneCell($c, $v, $attributes, $value = null) {
        if($value != null) {
            $cell_value = $v->cloneNode(true);
            $cell_value->nodeValue = $value;
        }
        $cell       = $c->cloneNode(true);
        foreach($attributes as $name=>$attr) {
            $cell->setAttribute($name, $attr);
        }
        if($value != null) {
            $cell->appendChild($cell_value);
        }
        return $cell;
    }
    
    /**
     * Создает переменную текста в таблице, весь текст в каркасе помечается номерами позиций данных элементов
     * 
     * @param string $text    Текст
     * @return integer  Номер позиции сгенерированного элемента
     */
    public function createSharedTextItem($text) {
        $si = $this->dom['shared']->createElement('si');
        $t  = $this->dom['shared']->createElement('t', $text);
        $t->setAttribute('xml:space', 'preserve');
        $si->appendChild($t);
        
        $this->dom['shared']->documentElement->appendChild($si);
        $sst = $this->dom['shared']->getElementsByTagName('sst')->item(0);
        $position   = $sst->childNodes->length;
        
        $sst->setAttribute('count', $position);
        $sst->setAttribute('uniqueCount', $position-2);
        return ($position - 1); // Начинается с нуля
    }
    
    /**
     * Генерируем данные для формул (данные о формулах храняться в отдельном файле)
     */
    public function generateFormulaData() {
        $formula = $this->xpath['sheet']->query('//p:f', $this->dom['sheet']->documentElement);
        
        $calcChain = $this->dom['calc']->createElement('calcChain');
        
        for($i=0; $i < $formula->length; $i++) {
            $node = $formula->item($i);
            $r    = $node->parentNode->getAttribute('r');
            $c    = $this->dom['calc']->createElement('c');
            $c->setAttribute('r', $r);
            $c->setAttribute('i', 1);
            if($r{0} == 'H') {
                $c->setAttribute('l', 1);
            }
            $calcChain->appendChild($c);
        }

        $this->dom['calc']->appendChild($calcChain);
    }
    
    /**
     * Заменяем текст в определенной переменной
     * 
     * @param integer $index   Индекс текста (позиция элемента)
     * @param string  $text    Заменяемый текст    
     */
    public function replaceSharedString($index, $text) {
        $period = $this->xpath['shared']->query('//p:si[' . $index . ']/p:t', $this->dom['shared']->documentElement)->item(0);
        $period->nodeValue = $this->_enc($text);
    }
    
    /**
     * Генерируем данные объединений ячеек (для таблицы)
     * 
     * @param integer $new_position    Позиция строки которую вставили
     */
    public function generateMergeForNewRow($new_position) {
        $m0 = $this->dom['sheet']->createElement('mergeCell');
        $m0->setAttribute('ref', "B{$new_position}:C{$new_position}");
        $m1 = $m0->cloneNode();
        $m1->setAttribute('ref', "D{$new_position}:E{$new_position}");
        $m2 = $m0->cloneNode();
        $m2->setAttribute('ref', "F{$new_position}:G{$new_position}");
        $m3 = $m0->cloneNode();
        $m3->setAttribute('ref', "H{$new_position}:I{$new_position}");

        $mergeCells = $this->dom['sheet']->getElementsByTagName('mergeCells')->item(0);
        $mergeCells->appendChild($m0);
        $mergeCells->appendChild($m1);
        $mergeCells->appendChild($m2);
        $mergeCells->appendChild($m3);
        $mergeCells->setAttribute('count', $mergeCells->childNodes->length);
    }
    
    /**
     * Инициализация DOMDocument для работы с файлами
     * 
     * @param string  $name     Название ключа
     * @param integer $index    Индекс документа в архиве 
     * @return type
     */
    public function initDOMDocument($name, $index = null) {
        $this->dom[$name] = new DOMDocument('1.0', 'UTF-8');
        $this->dom[$name]->standalone = true;
        if($index !== null) {
            $content = $this->zip->getFromIndex($index);
            $this->dom[$name]->loadXML($content);
        }
        
        $this->xpath[$name] = new DOMXPath($this->dom[$name]);
        $this->xpath[$name]->registerNamespace("p", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
        
        return $this->dom[$name];
    }
}
?>