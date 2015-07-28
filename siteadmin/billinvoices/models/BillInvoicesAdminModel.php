<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/bill/models/BillInvoicesModel.php');

class BillInvoicesAdminModel extends BillInvoicesModel
{
    /**
     * Массив фильтра
     * @var array
     */
    protected $filter = array();

    
    /**
     * Установить массив фильтра
     * 
     * @param type $filter
     * @return \BillInvoicesAdminModel
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Собирает SQL запрос по фильтру
     * 
     * @param type $filter
     * @return type
     */
    protected function whereFilter()
    {
        $login = @$this->filter['login'];
        $date = @$this->filter['date'];
        $from_date = $to_date = null;
        
        if ($date) {
            $date = explode(' - ', $date);
            $from_date = strtotime($date[0]);
            $to_date = strtotime($date[1]);
            if ($from_date && $to_date) {
                $from_date = date('Y-m-d', $from_date);
                $to_date = date('Y-m-d', $to_date);
            }
        }
        
        return $this->db()->parse("
            WHERE TRUE 
            ".($login?$this->db()->parse(" AND (lower(u.login) = ? OR bi.id = ?i OR fa.original_name LIKE ? )", strtolower($login), intval($login), '%'.$login.'%'):"")." 
            ".(($from_date && $to_date)?" AND bi.date BETWEEN '{$from_date}'::date AND '{$to_date}'::date + '1 day'::interval":"")."
        ");
    }
    
    
    /**
     * Получить список счетов с учетом фильтра
     * 
     * @param type $filter
     * @return type
     */
    public function getInvoices()
    {
        $sql = $this->db()->parse("
            SELECT 
                bi.price,
                bi.id AS invoice_id,
                bi.date,
                (fa.path || fa.fname) AS file,
                fa.original_name AS name,
                u.uid,
                u.login,
                bi.acc_op_id,
                
                bi.file_factura_id,
                (fa2.path || fa2.fname) AS file_factura,
                fa2.original_name AS name_factura
                
            FROM ".self::$_TABLE." AS bi
            INNER JOIN ".self::$_TABLE_FILE." AS fa ON fa.id = bi.file_id
            LEFT JOIN ".self::$_TABLE_FILE." AS fa2 ON fa2.id = bi.file_factura_id
            INNER JOIN ".self::$_TABLE_USER." AS u ON u.uid = bi.user_id
            {$this->whereFilter()}
            ORDER BY bi.last DESC, bi.date DESC
        ");
        $sql = $this->_limit($sql);
        return $this->db()->rows($sql);
    }
    
    
    /**
     * Кол-во счетов с учетом фильтра
     * 
     * @return type
     */
    public function getInvoicesCnt()
    {
        $sql = $this->db()->parse("
            SELECT 
                COUNT(*)
            FROM ".self::$_TABLE." AS bi 
            INNER JOIN ".self::$_TABLE_USER." AS u ON u.uid = bi.user_id
            INNER JOIN ".self::$_TABLE_FILE." AS fa ON fa.id = bi.file_id
            {$this->whereFilter()}
        ");

        return $this->db()->val($sql);
    }
    
    
    
    
    public function deleteFactura($nums)
    {
        if($nums) {
            $file = new CFile();
            $file->table = self::$_TABLE_FILE;
            foreach($nums as $user_id => $invoices) {
                foreach($invoices as $invoice_id => $file_factura_id) {
                    $file->Delete($file_factura_id);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Обновить файл счет-фактуры
     * @param type $invoice_id
     * @param type $file
     */
    public function updateFactura($invoice_id, $uploaded_file)
    {
        if (!$uploaded_file || !is_array($uploaded_file) || !$uploaded_file['size']) {
            return false;
        }
        
        $data = $this->getInvoice($invoice_id);
        
        if (!$data) {
            return false;
        }

        $old_file = new CFile();
        $old_file->table = self::$_TABLE_FILE;
        $old_file->GetInfoById($data['file_factura_id']);        

        $file = new CFile($uploaded_file);
        $file->table = self::$_TABLE_FILE;
        $file->src_id = $old_file->src_id;
        $file->original_name = $old_file->original_name;
        $file->server_root = 1;
        $file->MoveUploadedFile($old_file->path);
        
        $old_file->Delete($data['file_factura_id']);
        
        $this->update($invoice_id, array(
            'file_factura_id' => $file->id
        ));
        
        return true;
    }
    


    /**
     * Сченерировать счет-фактуру
     * 
     * @param type $nums
     * @param type $dates
     */
    public function addFactura($nums, $dates)
    {
        require_once(ABS_PATH . '/classes/DocGen/DocGenBill.php');
        
        if($nums) {
            foreach($nums as $user_id => $invoices) {
                foreach($invoices as $invoice_id => $num) {
                    try {
                        
                        $date = @$dates[$user_id][$invoice_id];
                        
                        $doc = new DocGenBill();
                        $doc->generateFactura($invoice_id, $num, $date);
                    } catch (Exception $e) {
                        continue;
                    }                    
                }                        
            }
        }
    }
    
    
    
    
    
}