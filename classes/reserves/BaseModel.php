<?php

//@todo: пока использую абстрактный общий класс из ТУ 
//потом его можно выделить в общую библиотеку
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');


/**
 *  Базовый класс модели
 */
abstract class BaseModel extends atservices_model
{
    
    /**
     * Создаем сами себя
     * @return ReservesModel
     */
    public static function model(array $options = array()) 
    {
        $class = get_called_class();
        return new $class($options);
    }
    
}
