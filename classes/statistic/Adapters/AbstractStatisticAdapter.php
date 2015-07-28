<?php

/**
 * Абтрактный класс адаптера
 */
abstract class AbstractStatisticAdapter 
{
    protected $service;
    protected $config;
    protected $options;

    protected $lastRequest;
    
    
    /**
     * Конструктор конфигурирует адаптер
     * 
     * @param array $options
     * @param object $config
     */
    public function __construct($options = array(), $config = NULL) 
    {
        $default_options = array();
        
        if($config){
            $this->setConfig($config);
            $default_options = $this->config->options();
        }

        $options = (count($options))? $options + $default_options : $default_options;
        
        if(count($options)){
            $this->setOptions($options);
        }
        
        //Вызов метода для инициализации сервиса статистики
        $this->initService();
    }
    
    
    /**
     * Указать обьект конфигурации
     * 
     * @param object $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    
    /**
     * Указать настройки
     * 
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    
    /**
     * Вернуть настройки
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    
    /**
     * Инициализация сервиса статистики
     * метод должен быть описан в реализации класса
     */
    protected abstract function initService();
    
    
    
    /**
     * Постановка события в очередь
     */
    public function queue($type, Array $data)
    {
        return $this->db()->query("SELECT pgq.insert_event('statistic', ?, ?)", 
                $type, 
                http_build_query($data));
    }
    
    
    /**
     * Вызов метода сервиса
     */
    public function call($type, Array $data)
    {
        if (method_exists($this, $type)) {
            return call_user_func_array(array($this, $type), $data);
        }
        
        return false;
    }

    
    
    public function getLastRequest()
    {
        return $this->lastRequest;
    }
    

    
    /**
     * @return DB
     */
    public function db()
    {
        return $GLOBALS['DB'];
    }
    
}
