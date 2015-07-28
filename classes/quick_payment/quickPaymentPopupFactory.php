<?php

/**
 * Class quickPaymentPopupFactory
 * Фабрика для создания обьектов "быстрой" оплаты
 */
class quickPaymentPopupFactory 
{
    const QPP_PROCESS_SESSION = 'quickPaymentPopupProcess';
    
    /**
     * Сущности использующие 
     * быстрые способы оплаты
     * 
     * @var type 
     */
    protected static $models = array(
        'reserve'           => 'reserve',
        'autoresponse'      => 'autoresponse',
        'frlbind'           => 'frlbind',
        'frlbindup'         => 'frlbindup',
        'carusel'           => 'carusel',
        'tservicebind'      => 'tservicebind',
        'tservicebindup'    => 'tservicebindup',
        'billinvoice'       => 'billInvoice',
        'account'           => 'account',
        'masssending'       => 'masssending',
        'pro'               => 'pro'
    );

    
    /**
     * Получить список существующих папов быстрой оплаты
     * для создания через фабрику
     * 
     * @return array
     */
    public static function getModelsList()
    {
        return array_keys(static::$models);
    }

    
    /**
     * Есть ли ключ в сессии
     * 
     * @return type
     */
    public static function isExistProcess()
    {
        return isset($_SESSION[self::QPP_PROCESS_SESSION]);
    }

    

    /**
     * Фабрика подключает и инициализирует объект нужной нам сущности по ее типу
     * 
     * @param type $type - тип оплаты
     * @return object
     * @throws Exception
     */
    public static function getInstance($type = null) 
    {
        $type = (!$type)?@$_SESSION[self::QPP_PROCESS_SESSION]:$type;
        if (!$type || !in_array($type, array_keys(self::$models))) {
            throw new Exception("The type not found.");
        }
        
        $class = 'quickPaymentPopup' . ucfirst(self::$models[$type]);
        
        if (!class_exists($class, false)) {
            $filename = sprintf('%s/%s.php', __DIR__, $class);
            
            if (!file_exists($filename)) {
                throw new Exception("The class name $class could not be instantiated.");
            }
            
            require_once $filename;
        }
        
        return $class::getInstance();
    }  
    
}
