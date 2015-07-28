<?php

/**
 * Класс работы с диф.ценами на различные услуги
 */
class op_codes_price 
{
    /**
     * Кеш на данных на период работы скрипта
     * исключает многократное обращение к БД в период одной сесии работы скрипта
     * 
     * @var type 
     */
    static protected $_cache_data = array();


    const OP_CODES_PRICE_MEMCACHE_TAG     = 'getAllOpCodesPrice';
    //на вечно, но моет быть очищет если другим нехватит места см. доку
    const OP_CODES_PRICE_MEMCACHE_LIFE    = 0; 
    
 
    
    /**
     * Чистим в мемкеше
     * 
     * @return type
     */
    public static function clearCache()
    {
        $memBuff = new memBuff;
        return $memBuff->delete(self::OP_CODES_PRICE_MEMCACHE_TAG);
    }
    
    
    
    /**
     * Обновить кеш цен
     * 
     * @global type $DB
     * @return boolean
     */
    public static function updateCache()
    {
        global $DB;
        
        self::$_cache_data = null;
        $data = $DB->rows("SELECT * FROM ". get_class($this));
        
        if ($data) {
            
            //Преобразуем в удобную форму пользования
            foreach($data as $el) {
                self::$_cache_data[$el['op_code']][$el['param']] = $el['sum'];
            }
            
            $memBuff = new memBuff;
            return $memBuff->set(
                    self::OP_CODES_PRICE_MEMCACHE_TAG, 
                    self::$_cache_data, 
                    self::OP_CODES_PRICE_MEMCACHE_LIFE);
        }
        
        return false;
    }


    /**
     * Загрузить весь список цен
     * 
     * @return boolean / array
     */
    public static function getAllOpCodesPrice($refresh = false)
    {
        if (!empty(self::$_cache_data) && !$refresh) {
            return self::$_cache_data;
        }
        
        self::updateCache();
        
        return self::$_cache_data;
    }
    
    
    /**
     * Получить диф.цену услуги по опкоду и параметрам
     * 
     * @param type $op_code - Опкод
     * @param type $param - параметр или массив параметров
     * @return type
     */
    public static function getOpCodePrice($op_code, $param = array())
    {
        $param = !is_array($param)?array($param):$param;
        $param_key = (!$param || empty($param))?'0':implode('_', $param);
        
        $data = self::getAllOpCodesPrice();
        
        if (!isset($data[$op_code][$param_key])) {
            //Пробуем получить стоимость поумолчанию для указанного опкода
            $price = @$data[$op_code]['0'];
        } else {
            //Получаем диф.цену
            $price = $data[$op_code][$param_key];
        }
        
        return $price;
    }
    
    
}