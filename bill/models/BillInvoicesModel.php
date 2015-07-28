<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/CModel.php');

class BillInvoicesModel extends CModel
{
    public static $_TABLE           = 'bill_invoices';
    public static $_TABLE_FILE      = 'file_account';
    public static $_TABLE_USER      = 'users';

    
    
    
    
    
    public function getInvoice($id)
    {
        $row = $this->db()->row("
            SELECT 
                bi.*,
                u.uid,
                u.login
            FROM ".self::$_TABLE." AS bi 
            INNER JOIN ".self::$_TABLE_USER." AS u ON u.uid = bi.user_id
            WHERE bi.id = ?i", $id);
        
        if ($row) {
            $row['fields'] = mb_unserialize($row['fields']);
        }
        
        return $row;
    }


    /**
     * Получить ID файла неоплаченного счета
     * 
     * @param type $id
     * @param type $uid
     * @return type
     */
    public function getInvoiceFileId($id, $uid)
    {
        return $this->db()->val("
            SELECT file_id FROM ".self::$_TABLE." 
            WHERE id = ?i AND user_id = ?i AND acc_op_id IS NULL 
        ", $id, $uid);
    }
    
    /**
     * Получить последний неоплаченный за 5 дней счет пользователя
     * 
     * @param type $uid
     * @return type
     */
    public function getLastActiveInvoice($uid)
    {
        return $this->db()->row("
            SELECT 
                bi.id AS invoice_id,
                (fa.path || fa.fname) AS file,
                fa.original_name AS name
            FROM ".self::$_TABLE." AS bi
            INNER JOIN ".self::$_TABLE_FILE." AS fa ON fa.id = bi.file_id
            WHERE 
                bi.acc_op_id IS NULL 
                AND bi.date > NOW() - interval '5 days'
                AND bi.user_id = ?i
            ORDER BY bi.id DESC
            LIMIT 1
        ", $uid);
    }
    
    
    public function update($id, $data = array())
    {
        $data['last'] = 'NOW()';
        return $this->db()->update(self::$_TABLE, $data, 'id = ?i', $id);
    }
    
}