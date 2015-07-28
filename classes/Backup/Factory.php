<?php


class Backup_Factory 
{
    const CLASS_PREFIX = 'Backup_Service_%s';
    const SERVICE_PATH = '%s/Service/%s.php';
    
    /**
     * Создать сервис передаваемоего типа.
     * 
     * @param string $type
     * @return \class
     * @throws Exception
     */
    public static function getInstance($type, $options = array()) 
    {
        $type = ucfirst(preg_replace('/[^a-zA-Z0-9_]/', '', (string) $type));
        $class = sprintf(self::CLASS_PREFIX, $type);
        
        if (!class_exists($class, false)) {
            $filename = sprintf(self::SERVICE_PATH, __DIR__, $type);
            
            if (!file_exists($filename)) {
                throw new Exception("The class name $class could not be instantiated.");
            }
            
            require_once $filename;
        }
        
        return new $class($options);
    }  
    
}