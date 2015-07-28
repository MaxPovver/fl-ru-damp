<?php

require_once('Interface.php');

abstract class Backup_Service_Abstract implements Backup_Service_Interface
{
    protected $filepath = null;
    
    public function setFilePath($filepath)
    {
        $this->filepath = ltrim($filepath, '/');
    }
    
}