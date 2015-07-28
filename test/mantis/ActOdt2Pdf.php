<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/odt2pdf.php');

class ActOdt2Pdf extends odt2pdf
{
   protected $_stop_remove = false;
   
   
   public function setStopRemove($stop = true)
   {
       $this->_stop_remove = $stop;
   }

   public function getOdtContent() 
   {
        return file_get_contents($this->file_path);
   }
   
   public function convert($replace = false, $filename = "") 
   {
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
                
                if(!$this->_stop_remove){
                    $this->remove();
                }
                
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
    
}
