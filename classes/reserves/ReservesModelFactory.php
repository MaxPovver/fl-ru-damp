<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModel.php');

/**
 *  Class ReservesModelFactory
 *  Фабрика создает сущность по указанному типу.
 */
class ReservesModelFactory 
{
    const TYPE_TSERVICE_ORDER = 10;
    //const TYPE_PROJECT      = 20;
    
    protected static $models = array(
        self::TYPE_TSERVICE_ORDER => 'TServiceOrder',
        //self::TYPE_PROJECT        => 'Project'
    );
    
    
    
    /**
     * Фабрика подключает и инициализирует объект нужной нам сущности по ее типу
     * 
     * @param int $type - Тип сущности см поле reserves.type
     * @return \class - Синглтон сущности
     * @throws Exception - Если сущность не найдена то бросаем исключение 
     */
    public static function getInstance($type) 
    {
        if (!isset(self::$models[$type])) {
            throw new Exception("The type not found.");
        }
        
        $class = 'Reserves' . self::$models[$type] . 'Model';

        if (!class_exists($class, false)) {
            $filename = sprintf('%s/%s.php', __DIR__, ucfirst($class));
            if (!file_exists($filename)) {
                throw new Exception("The class name $class could not be instantiated.");
            }
            require_once $filename;
        }

        $instance = $class::model();
        return $instance;
    }  
    
    
    /**
     * Фабрика получает нужную сущность по ID резерва
     * 
     * @param type $id
     * @return boolean
     */
    public static function getInstanceById($id)
    {
        $reserve_data = ReservesModel::model()->getReserveById($id);
        if(!$reserve_data) return false;
        $instance = static::getInstance($reserve_data['type']);
        if(!$instance) return false;
        $instance->setReserveData($reserve_data);
        return $instance;
    }
    
}