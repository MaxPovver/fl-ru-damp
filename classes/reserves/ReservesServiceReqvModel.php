<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/BaseModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');

class ReservesServiceReqvModel extends BaseModel
{
    protected $TABLE = 'reserves_service_reqv';
    
    private $reserve_id;
    
    public function __construct($reserve_id)
    {
        $this->reserve_id = $reserve_id;
    }
    
    /**
     * Сохраняет слепок реквизитов заказчика, если они не были сохранены ранее 
     * @return boolean
     */
    public function captureEmpReqv($emp_id)
    {
        $isCaptured = $this->db()->val("
            SELECT 1 
            FROM {$this->TABLE} 
            WHERE reserve_id = ?i
        ", $this->reserve_id);
        
        if ($isCaptured) {
            return false;
        }
        
        $reqvs = ReservesHelper::getInstance()->getUserReqvs($emp_id);
        if (!$reqvs || !$reqvs['form_type']) {
            return false;
        }
        $reqv = $reqvs[$reqvs['form_type']];
        
        $reqv['form_type'] = $reqvs['form_type'];
        $reqv['rez_type'] = $reqvs['rez_type'];
        
        $data = array(
            'reserve_id' => $this->reserve_id,
            'fields' => serialize($reqv)
        );
        
        return $this->db()->insert($this->TABLE, $data, 'id');
    }
    
    /**
     * Возвращает реквизиты заказчика из слепков
     * @return type
     */
    public function getReqv($emp_id = 0)
    {
        $fields = $this->db()->val("SELECT fields FROM {$this->TABLE} WHERE reserve_id = ?i", $this->reserve_id);

        if ($fields) {
            $reqv = mb_unserialize($fields);
        } elseif ($emp_id) {
            $reqvs = ReservesHelper::getInstance()->getUserReqvs($emp_id);
            if ($reqvs && $reqvs['form_type']) {
                $reqv = $reqvs[$reqvs['form_type']];
                $reqv['form_type'] = $reqvs['form_type'];
                $reqv['rez_type'] = $reqvs['rez_type'];
            }
        }
        
        return $reqv;
    }
    
    
}