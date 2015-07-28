<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/BaseModel.php');

/**
 * Коммисия для заказчика по резерву средств
 */
class ReservesTaxes extends BaseModel
{
    const CACHE_TTL     = 604800;//1 неделя
    
    private $TABLE = 'reserves_taxes';
    
    private $tax_list = array();

    
    public function __construct() 
    {
        $this->tax_list = $this->getList();
    }
    
    
    /**
     * Получить все варианты коммисии
     * 
     * @return array
     */
    public function getList()
    {
        return $this->db()->cache(self::CACHE_TTL)->rows("
            SELECT min,max,tax FROM {$this->TABLE}
        ");
    }
    
    
    /**
     * Получить значение коммисии
     * для указанной суммы резерва
     * 
     * @param init $price
     * @return double
     */
    public function getTax($price, $persent = false)
    {
        $tax = 0;
        
        if(!empty($this->tax_list))
        {
            foreach($this->tax_list as $el)
            {
                if($price >= $el['min']  && $price <= $el['max'])
                {
                    $tax = $el['tax'];
                    break;
                }
            }
        }

        return ($persent)?$tax*100:$tax;
    }
    
    
    /**
     * Получить сумму с коммисией
     * 
     * @param type $price
     * @return type
     */
    public function calcWithTax($price)
    {
        $tax = $this->getTax($price);
        return $price + floor($price * $tax);
    }
    
    
    
}