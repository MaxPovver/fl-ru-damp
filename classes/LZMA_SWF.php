<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

if(!defined('LZMA_FOLDER_TMP')) {
    define("LZMA_FOLDER_TMP", "/var/tmp/lzma");
}

if(!defined('LZMA_EXEC')) {
    define("LZMA_EXEC", "lzma -d -f -c -S .lz");
}
/**
 * Класс для работы в SWF которые запакованы через 7z алгоритмом LZMA, 
 * в таком случае getimagesize -- не может разобрать исходный файл
 * Это костыль что бы разобрать файл
 * 
 * Для работы класса требуется библиотека LZMA SDK v 9.*
 * 
 * Ниже пример заголовка файла SWF запакованного 7x
 * A LZMA compressed SWF looks like this (example):
 *
 * 0000 5A 57 53 0F   // ZWS + Version 15
 * 0004 DF 52 00 00   // Uncompressed size: 21215
 * // ZWS-specific:
 * 0008 94 3B 00 00   // Compressed size: 15252
 * 000C 5D 00 00 00 01   // LZMA Properties
 * 0011 00 3B FF FC A6 14 16 5A ...   // LZMA Compressed Data (until EOF)
 * 
 * LZMA headers example
 * 0000 5D 00 00 00 01   // LZMA Properties
 * 0005 D7 52 00 00 00 00 00 00   // Uncompressed size: 21207 (64 bit)
 * 000D 00 3B FF FC A6 14 16 5A ...   // LZMA Compressed Data (until EOF)
 * 
 */
class LZMA_SWF
{
    
    /**
     * Размер несжатых данных
     * 
     * @var integer
     */
    public $uncompress_length;
    
    /**
     * Файлы которые необходимо почистить после работы скрипта
     * 
     * @var array
     */
    public $clear_files = array();
    
    /**
     * Путь главного файла который разбираем
     * 
     * @var string
     */
    public $filename    = '';
    
    /**
     * Текущий путь файла этапа работы скрипта
     * @var string 
     */
    public $curr_file   = '';
    
    /**
     * Версия flash
     * 
     * @var integer
     */
    public $version     = 0x0F;
    
    /**
     * Конструктор класса
     * 
     * @param string $filename Путь к файлу
     */
    public function __construct($filename) {
        $this->filename = $filename;
        $this->log = new log('lzma/lzma-'.SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }
    
    /**
     * Проверяем зависимости скрипта ( зависит от пакета LZMA SDK ) если он не установлен в системе скрипт не будет работать
     * 
     * @return boolean true - если все ок
     */
    public function isRequirement() {
        return ( strpos(shell_exec("lzma -V"), 'command not found') === false );
    }
    
    /**
     * Переводим размер в байты
     * 
     * @param integer $size    Размер     
     * @param integer $length  Количество ячеек памяти
     * @return array (1 элемент массива ячейка памяти)
     */
    public function getByteForSize($size, $length = 4) {
       $bytes = array();
       for($i=0, $t=0; $i<$length; $i++, $t+=8) {
           $bytes[$i] = ($size >> $t) & 0xFF;  
       }
       return $bytes;
    }
    
    /**
     * Переводим байты в размер
     * 
     * @param array   $byte    байты
     * @param integer $offset   С какой ячейки памяти читать
     * @param integer $length   Количество ячеек памяти
     * @return integer
     */
    public function getSizeForByte($byte, $offset=0, $length=4) {
        $size = 0;
        for($i=0, $t=0; $i<$length; $i++, $t+=8) {
            $size += ($byte[$offset+$i] << $t); 
        }
        return $size;
    }
    
    /**
     * Записываем файл на удаление
     * 
     * @param string $file  Путь файла
     */
    public function setClear($file) {
        $this->clear_files[basename($file)] = $file;
    }
    
    /**
     * Очищаем временные файлы созданные скриптом
     */
    public function clearTmpFiles() {
        foreach($this->clear_files as $files) {
            unlink($files);
        }   
    }
    
    /**
     * Получаем информацию по SWF
     */
    public function getInformationSWF() {
        if($this->isRequirement()) {
            try {
                $this->replaceHeaderSWF($this->filename)
                     ->decompressLZMA($this->getCurrentFileName())
                     ->injectHeaderSWF($this->getCurrentFileName())
                     ->getInfoSWF($this->getCurrentFileName())
                     ->clearTmpFiles();
            } catch (Exception $e) {
                $this->log->writeln("Exception: " . $e->getMessage());
            }
        } else {
            $this->log->writeln("LZMA SDK does not install your system");
        }
    }
    
    /**
     * Проверяем файл SWF сжат он через 7z или нет
     * 
     * @param string $filename
     * @return boolean
     */
    public function isLZMACompress($filename = null) {
        if($filename == null) $filename = $this->filename;
        if(!file_exists($filename)) {
            $this->log->writeln("{$filename} does not exists");
            return false;
        }
        $fp = fopen($filename, 'rb');
        $pack = "";
        // Читаем первые 3 байта смотрит точно это ZWS или нет
        for($i=0;$i<3;$i++) {
            $pack .= fread($fp, 1);
        }
        fclose($fp);
        return ($pack == 'ZWS');
    }
    
    /**
     * Переводим все байты в символы
     * @param array $bytes  байты
     * @return type
     */
    public function byte2Char($bytes) {
        if(!is_array($bytes)) return;
        return implode("", array_map('chr', $bytes));
    }
    
    /**
     * Получаем корректный заголовок файла LZMA
     * 
     * @param array $SWFheader  байты заголовка SWF сжатого через 7z
     * 
     * @return int
     * @throws Exception
     */
    public function getHeaderLZMA($SWFheader) {
        if(!is_array($SWFheader)) {
            throw new Exception('Argument $SWFheader does not array!');
        }
        $header = array();
        $this->version = $SWFheader[4];
        for ($i=0;$i<5;$i++) {
            $header [$i]= $SWFheader[13+$i];     
        }
        $this->uncompress_length = $this->getSizeForByte($SWFheader, 5) - 8;
        
        $byteSize = $this->getByteForSize($this->uncompress_length);
        array_splice($header, 5, 0, $byteSize);
        for ($i=0;$i<4;$i++) {
            $header[9+$i]= 0;    
        }
        return $header;
    }
    
    /**
     * Получаем заголовок файла несжатого SWF
     * 
     * @param integer $size Размер несжатого SWF
     * @return array
     */
    public function getHeaderFWS($size) {
        $header = array();
        $header[] = 0x46; // F
        $header[] = 0x57; // W
        $header[] = 0x53; // S
        $header[] = $this->version; // Version
        array_splice($header, 5, 0, $this->getByteForSize($size));
        return $header;
    }
    
    /**
     * Получаем байты в массив
     * 
     * @param string $filename
     * @return type
     * @throws Exception
     */
    
    /**
     * Получаем байты в массив
     * 
     * @param string  $filename     Путь к файлу
     * @param boolean $header       Взять только заголовок файла
     * @return array
     * @throws Exception
     */
    public function getFileByteSWF($filename, $header = true) {
        if(!file_exists($filename)) {
            throw new Exception("{$filename} does not exists");
        }
        if($header) { // Взять только шапку
            $fp = fopen($filename, 'rb');
            $content_swf = fread($fp, 17);
            fclose($fp);
        } else {
            $content_swf    = file_get_contents($filename);
        }
        
        if($content_swf == '') {
            throw new Exception("{$filename} data is NULL");
        }
        
        return unpack('C*', $content_swf);
    }
    
    /**
     * Текущий файл с которым работаем
     * 
     * @param string $filename  Путь к файлу 
     * @param string $ext       Расширение файла
     * @return string
     */
    public function getCurrentFileName($filename = null, $ext = "txt") {
        if($filename !== null) {
            $this->curr_file = $this->getTmpFolder() . "/". basename($filename) . ".{$ext}";
            $this->setClear($this->curr_file);
        }
        return $this->curr_file;
    }
    
    /**
     * Меняем заголовок файла упакованного в 7z
     * 
     * @param string $filename  Путь к файлу 
     * @return \LZMA_SWF
     */
    public function replaceHeaderSWF($filename) {
        if(!file_exists($filename)) {
            throw new Exception("{$filename} does not exists");
        }
        $LZMAHeaderChar = self::byte2Char(self::getHeaderLZMA(self::getFileByteSWF($filename)));
        
        $fp = fopen($filename, 'rb');
        // Удаляем первые 17 байт
        $delete = fread($fp, 17); 

        $contents = $LZMAHeaderChar;
        while (!feof($fp)) {
            $contents .= fread($fp, 8192);
        }
        fclose($fp);
        file_put_contents($this->getCurrentFileName($filename, "lz"), $contents);
        return $this;
    }
    
    /**
     * Декомпрессим файл
     * 
     * @param string $filename  Путь к файлу 
     * @return \LZMA_SWF
     * @throws Exception
     */
    public function decompressLZMA($filename) {
        if(!file_exists($filename)) {
            throw new Exception("{$filename} does not exists");
        }
        exec(LZMA_EXEC . " {$filename} >>" . $this->getCurrentFileName($filename, "de"));
        return $this;
    }
    
    /**
     * Добавляем корректный заголовок для несжатого SWF
     * 
     * @param string $filename  Путь к файлу 
     * @return \LZMA_SWF
     * @throws Exception
     */
    public function injectHeaderSWF($filename) {
        if(!file_exists($filename)) {
            throw new Exception("{$filename} does not exists");
        }
        $headerFWS = self::getHeaderFWS($this->uncompress_length);
        $content   = file_get_contents($filename);
        $content   = self::byte2Char($headerFWS) . $content;
        file_put_contents($this->getCurrentFileName($filename, "swf"), $content);
        return $this;
    }
    
    /**
     * Берем инфу у обрабтанного файла SWF 
     * 
     * @param string $filename  Путь к файлу 
     * @return \LZMA_SWF
     */
    public function getInfoSWF($filename) {
        $this->info = getimagesize($filename);
        return $this;
    }
    
    /**
     * Выдаем папку для махинаций с временными файлами,
     * если ее нет создаем
     * 
     * @return string
     */
    public function getTmpFolder() {
        if(!file_exists(LZMA_FOLDER_TMP)) {
            $m = mkdir(LZMA_FOLDER_TMP, 0777);
        }
        return LZMA_FOLDER_TMP;
    }
}