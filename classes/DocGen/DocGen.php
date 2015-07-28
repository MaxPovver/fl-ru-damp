<?php

//Временная директория для odt2pdf
define("SBR_FOLDER_TMP", '/var/tmp/docgen/');

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/odt2pdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/CFile.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/events.php');
require_once('Formatter/DocGenFormatter.php');
require_once('DocGenQueue.php');

class DocGen 
{
    protected $template;
    protected $template_path;

    protected $file_table = 'file';
    protected $file_path;
    protected $file_original_name;
    protected $file_src_id = 0;


    protected $data;
    
    protected $docFormatter;

    protected $tmp_path = '/var/tmp/';

    protected $docs = array();
    
    /**
     * Массив с данными для принудительного перехвата 
     * и замены от оригинального источника
     * 
     * @var array 
     */
    protected $override_data = array();


    public function __construct() 
    {
        $this->setFormetter(new DocGenFormatter());
    }
    
    
    public function setDocName($type, $name)
    {
        $this->docs[$type]['name'] = $name;
    }


    public function setFilePath($path)
    {
        $this->file_path = $path;
    }

    public function setFileOriginalName($name)
    {
        $this->file_original_name = $name;
    }

    public function setFileSrcId($src_id)
    {
        $this->file_src_id = $src_id;
    }
    

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    

    public function setFormetter(DocGenFormatter $docFormatter)
    {
        $this->docFormatter = $docFormatter;
    }
    
    
    public function getField($key)
    {
        return @$this->data['$'.$key];
    }
    

    public function setField($key, $value, $formatterName = null)
    {
        //Если был перехват то получаем данные от туда
        if($_override_field = $this->getOverrideField($key)) {
            $this->data['$'.$key] = $_override_field;
            return true;
        }
        
        //Иначе обычный способ
        if(!$formatterName && 
            ($part = explode('_', $key)) && 
            (count($part) > 1)) $formatterName = strtolower($part[0]);
        $is_format = method_exists($this->docFormatter, $formatterName);
        $this->data['$'.$key] = ($is_format)?$this->docFormatter->$formatterName($value):$value;
        return true;
    }
    
    
    public function setFields($data, $options = array())
    {
        if(count($data))
        {
            foreach($data as $key => $value)
            {
                $formatterName = (isset($options[$key]))?$options[$key]:null;
                $this->setFieldValue($key, $value, $formatterName);
            }
        }       
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    

    public function getFilePath()
    {
        return $this->file_path;
    }
    
    public function beforeGenerate()
    {
        
    }
    
    /**
     * Генерация файла по шаблону ODT
     * и затем конвертация в PDF
     * 
     * @return boolean|\CFile
     */
    public function generate()
    {
        $pdf = new odt2pdf($this->template);
        $pdf->setFolder(ABS_PATH . $this->template_path);
        $pdf->convert($this->data);
        $content = $pdf->output(NULL, 'S');
        $len = strlen($content);
        if(!$len) return false;
        
        $file = new CFile();
        $file->path = $this->getFilePath();
        $file->table = $this->file_table;
        $file->size = $len;
        $file->src_id = $this->file_src_id;
        $file->name = basename($file->secure_tmpname($file->path,'.pdf'));
        $file->original_name = change_q_x($this->file_original_name);
        if(!$file->putContent($file->path . $file->name, $content)) return false;
        
        Events::trigger('generate_file', $file);
        
        return $file;
    }
    
    
    /**
     * Генерация Exсel таблицы на основе шаблона 
     * 
     * @param type $cellnames - массив ячеек где искать шаблоны подстановки
     * @return boolean|\CFile
     */
    public function generateExcel($cellnames = array('A1'))
    {
        if (empty($cellnames) || empty($this->data)) {
            return false;
        }
        
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objPHPExcel = $objReader->load(ABS_PATH . $this->template_path . $this->template);

        $data = array();
        foreach ($this->data as $key => $value) {
            $value = iconv("windows-1251", "utf-8", $value);
            $data['{' . $key . '}'] = $value;
        }
        
        $keys = array_keys($data);
        $vals = array_values($data);        
        
        foreach ($cellnames as $cellname) {
            $content = (string)$objPHPExcel->getActiveSheet()->getCell($cellname);
            if(!empty($content)) {
                $content = str_replace($keys, $vals, $content);
                $objPHPExcel->getActiveSheet()->setCellValue($cellname, $content);
            }
        }
        
        if (!file_exists($this->tmp_path)) {
            mkdir($this->tmp_path, 0777);
        }
        
        $tmpFilename = tempnam($this->tmp_path, 'tmp');
        //$tmpFilename = $this->tmp_path . substr(md5(microtime()), 0, 6) . '.xls';
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save($tmpFilename);
        $content = file_get_contents($tmpFilename);
        unlink($tmpFilename);
        
        
        $file = new CFile();
        $file->path = $this->getFilePath();
        $file->table = $this->file_table;
        $file->size = strlen($content);
        $file->src_id = $this->file_src_id;
        $file->name = basename($file->secure_tmpname($file->path,'.xls'));
        $file->original_name = change_q_x($this->file_original_name);
        
        if (!$file->putContent($file->path . $file->name, $content))  {
            return false;
        }

        Events::trigger('generate_file', $file);
        
        return $file;
    }
    
    
    
    /**
     * Проверяет нужный метод генерации документа
     * @param type $type
     * @return boolean
     */
    public function isExcel($type)
    {
        if (!isset($this->docs[$type]['cellnames'])) { 
            return false;
        }
        
        return true;
    }
    


    /**
     * Установить перехват и замену данных для ключа
     * 
     * @param type $key
     * @param type $value
     */
    public function setOverrideField($key, $value)
    {
        $this->override_data[$key] = $value;
    }
    
    
    /**
     * Получить существующий перехват и замену по ключу
     * 
     * @param type $key
     * @return type
     */
    public function getOverrideField($key)
    {
        return (isset($this->override_data[$key]))?
                    $this->override_data[$key]:
                    false;
    }
    
    /**
     * Возвращает данные, необходимые для воссоздания экземпляра класса
     * @return type
     */
    protected function getConstructorParams()
    {
        return array();
    }
    
    
    /**
     * Добавляет данные в очередь
     */
    public function addToQueue($type)
    {
        $original_name = isset($this->docs[$type]['name'])?
                $this->docs[$type]['name']:'';
        
        return $this->getQueue()->addItem(array(
                'class_name' => get_class($this),
                'class_params' => $this->getConstructorParams(),
                'type' => $type,
                'src_id' => $this->file_src_id,
                'file_path' => $this->file_path,
                'original_name' => $original_name,
                'fields' => $this->data,
            ));
    }
    
    public function clearQueue($srcId, $types)
    {
        return $this->getQueue()->clear(
                get_class($this), 
                $srcId,
                $types
            );
    }
    
    protected function getQueue()
    {
        if ($this->queue === null) {
            $this->queue = new DocGenQueue();
        }
        
        return $this->queue;
    }
    
}