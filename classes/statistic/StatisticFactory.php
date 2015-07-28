<?php

//Соль для MD5
define('STAT_URL_PREFIX','yPXEUFyDqh');

require_once(ABS_PATH . "/classes/statistic/StatisticConfig.php");
require_once(ABS_PATH . "/classes/statistic/StatisticHelper.php");

/**
 * Класс фабрика для работы 
 * с сервисами статистики
 */
class StatisticFactory 
{
    const TYPE_GA = 'GA';
    
    protected static $models = array(
        self::TYPE_GA => 'GA'
    );    
    
    /**
     * Создаем и конфигурируем 
     * адаптер связи с сервисом статистики
     * 
     * @param string $name
     * @param array $options
     * @return \class
     * @throws Exception
     */
    public static function getInstance($name, $options = array()) 
    {
        if (!isset(self::$models[$name])) {
            throw new Exception("The type not found.");
        }   
        
        $class = 'StatisticAdapter'.ucfirst($name);
        
        if (!class_exists($class, false)) {
            
            $filename = sprintf('%s/Adapters/%s.php', __DIR__, $class);
            
            if (!file_exists($filename)) {
                throw new Exception("The class name $class could not be instantiated.");
            }
            
            require_once $filename;
        }
        
        $config = new StatisticConfig($name);
        $instance = new $class($options, $config);
        
        return $instance;
    }   
}