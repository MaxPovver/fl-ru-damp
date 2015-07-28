<?php

/**
 * Class buffer
 *
 */
class buffer {
    
    private $TABLE = 'buffer_sum';
    
    public function getSum()
    {
        global $DB;
        
        $uid = get_uid(false);
        
        $sql = "SELECT sum FROM {$this->TABLE} WHERE user_id = ?i";
        
        return (int)$DB->val($sql, $uid);
    }
    
    public function setUsedSum($price)
    {
        global $DB;
        
        $uid = get_uid(false);
        
        $sql = "UPDATE {$this->TABLE} SET sum = sum - ?i WHERE user_id = ?i;";
        
        $DB->query($sql, (int)$price, (int)$uid);
    }
}
