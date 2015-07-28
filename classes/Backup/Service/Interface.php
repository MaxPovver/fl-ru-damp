<?php


interface Backup_Service_Interface 
{
    
    public function create($filepath);
    
    public function delete($filepath);
    
    public function copy($from, $to);
    
}