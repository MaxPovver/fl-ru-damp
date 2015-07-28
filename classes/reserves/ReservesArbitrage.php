<?php

require_once(__DIR__ . '/Exception/ReservesArbitrageException.php');
require_once(__DIR__ . '/ReservesModel.php');
require_once(__DIR__ . '/ReservesPayback.php');


/**
 * Класс для работы с арбитражем
 */
class ReservesArbitrage extends BaseModel {
    
    protected $TABLE      = 'reserves_arbitrage';
    static public $_TABLE = 'reserves_arbitrage';
    
    const STATUS_OPEN = 0;
    const STATUS_CLOSED = 1;
    
    /**
     * Создание новой заявки на арбитраж.
     * @param type $data Массив необходимых данных по столбцам таблицы reserves_arbitrage
     * @return array Массив арбитража при успехе, либо пустой массив
     */
    public function createArbitrage($data) 
    {
        $id = $this->db()->insert($this->TABLE, $data,'id');
        
        if ($id) {
            $data['id'] = $id;
            return $data;
        }
        
        return false;
    }
    
    /**
     * Удаляет открытый арбитраж
     * @param type $reserve_id ИД резерва
     * @return bool true если успешно
     */
    public function removeArbitrage($reserve_id) {
        if ($this->is_allowed()) {
            $sql = "DELETE FROM {$this->TABLE} WHERE reserve_id = ?i AND status = ".self::STATUS_OPEN.";";
            $ok = $this->db()->query($sql, (int)$reserve_id);
            return $ok;
        }
        return false;
    }
    
    /**
     * Закрывает арбитраж
     * @param array $reserve_data данные резерва
     * @param array data Массив данных
     * @return bool true если успешно
     */
    public function closeArbitrage($reserve_data, $data) 
    {
        if(!$this->is_allowed()) 
            throw new ReservesArbitrageException(ReservesArbitrageException::NOT_ALLOWED);
        
        $ok = $this->db()->update(
            $this->TABLE, 
            array(
                'price' => (int)$data['price_pay'],
                'allow_fb_frl' => $data['allow_fb_frl'],
                'allow_fb_emp' => $data['allow_fb_emp'],
                'status' => self::STATUS_CLOSED,
                'date_close' => date('Y-m-d H:i:s')
            ), 
            "reserve_id = ?i AND status = ".self::STATUS_OPEN, (int)$reserve_data['id']
        );

        if($ok)
        {
            $reserveInstance = ReservesModelFactory::getInstance($reserve_data['type']);
            $reserveInstance->setReserveData($reserve_data);
            
            $payStatusDone = true;
            $backStatusDone = true;

            if($data['price_pay'] > 0) 
            {
                $payStatusDone = $reserveInstance->changePayStatus(ReservesModel::SUBSTATUS_NEW);
            }

            if($data['price_back'] > 0) 
            {
                $backStatusDone = $reserveInstance->changeBackStatus(ReservesModel::SUBSTATUS_NEW);
                //Ставим задачу на возврат средств в очередь
                if($backStatusDone && $reserve_data['invoice_id'] > 0)
                {
                    ReservesPayback::getInstance()->requestPayback(
                        $reserve_data['id'],
                        $reserve_data['invoice_id'],
                        $data['price_back']);
                }
            }

            if($payStatusDone && $backStatusDone)
            {
                return true;
            }
        }
        
        throw new ReservesArbitrageException(ReservesArbitrageException::CLOSE_FAIL);
    }
    
    
    
    /**
     * Проверяет доступность пользователю управления арбитражами
     */
    private function is_allowed() 
    {
        return hasPermissions('tservices');
    }
    
}
