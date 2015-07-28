<?php

require_once('DocGen.php');
require_once('Exception/DocGenBillException.php');
require_once('Formatter/DocGenBillFormatter.php');

class DocGenBill extends DocGen
{
    const FILE_PATH                 = 'users/%s/%s/attach/';
    const TEMPLATE_PATH             = '/templates/bill/docs/';
    
    const BANK_INVOICE              = 'bank_invoice.odt';
    const BANK_INVOICE_TEXT         = 'Счет %s';
    const BANK_INVOICE_TYPE         = 1;
    
    
    const BILL_FACTURA              = 'factura.xls';
    const BILL_FACTURA_TEXT         = 'Счет-фактура СФ-%s';
    const BILL_FACTURA_TYPE         = 2;

    const BANK_INVOICE_TABLE        = 'bill_invoices';
    
    protected $template_path        = self::TEMPLATE_PATH;
    protected $file_table           = 'file_account';
    
  
    
    protected $docs = array(
        self::BANK_INVOICE_TYPE => array(
            'template'  => self::BANK_INVOICE,
            'name'      => self::BANK_INVOICE_TEXT,
            'error'     => DocGenBillException::BANK_INVOICE_ERROR_MSG
        ),
        
        self::BILL_FACTURA_TYPE => array(
            'template'  => self::BILL_FACTURA,
            'name'      => self::BILL_FACTURA_TEXT,
            'cellnames' => array('B2','B9','B10','B11','B12','B14','K18','M18','M19','S18','S19','W18','W19'),
            'error'     => DocGenBillException::BILL_FACTURA_ERROR_MSG
        )
    );


    public function __construct() 
    {
        $this->setFormetter(new DocGenBillFormatter());
    }
    
    
    public function setFilePath($login) 
    {
        $this->file_path = sprintf(self::FILE_PATH, 
                substr($login, 0, 2), 
                $login);
    }

    

    public function generate($type) 
    {
        if(!isset($this->docs[$type])) return false;
        extract($this->docs[$type]);
        
        $this->setTemplate($template);
        $this->setFileOriginalName($name);
        $file = parent::generate();
        if(!$file) {
            throw new DocGenBillException($error);
        }
        
        $file->updateFileParams(array('doc_type' => $type));
        return $file;
    }

    
    
    public function generateExcel($type) 
    {
        if (!isset($this->docs[$type])) { 
            return false;
        }
        
        extract($this->docs[$type]);       
        
        $this->setTemplate($template);
        $this->setFileOriginalName($name);

        $file = parent::generateExcel($cellnames);
        if (!$file) { 
            throw new DocGenBillException($error);
        }
        
        $file->updateFileParams(array('doc_type' => $type));
        return $file;
    }
    
    
    
    /**
     * Генерация счет-фактуры
     * 
     * @return type
     */
    public function generateFactura($invoice_id, $num, $date = null)
    {
        require_once(ABS_PATH . '/bill/models/BillInvoicesModel.php');
        require_once(ABS_PATH . '/classes/sbr.php');
        require_once(ABS_PATH . '/classes/sbr_meta.php');
        
        $model = BillInvoicesModel::model();
        $data = $model->getInvoice($invoice_id);
        
        if (!$data) {
            throw new DocGenBillException(DocGenBillException::BILL_FACTURA_ERROR_MSG);
        }
        
        $this->setFilePath($data['login']);
        $this->setFileSrcId($data['uid']);
              
        $this->setField('sf_num', $num);
        $this->setField('sf_date', (!empty($date))?$date:date('j.m.Y'));
        $this->setField('name_user', $data['fields']);
        $this->setField('address', $data['fields']['address']);
        $this->setField('inn', $data['fields']['inn']);
        $this->setField('kpp', $data['fields']['kpp']);
        $this->setField('price_sf_summa', $data['price']);
        $this->setField('nonds_sf_summa', $data['price']);
        $this->setField('pricends_sf_summa', $data['price']);
        $this->setField('num_invoice', $invoice_id);

        $original_name = $this->docs[self::BILL_FACTURA_TYPE]['name'];
        $this->docs[self::BILL_FACTURA_TYPE]['name'] = sprintf($original_name, $this->data['$sf_num']);
        
        $file = $this->generateExcel(self::BILL_FACTURA_TYPE);
        
        if (!$file) {
            throw new DocGenBillException(DocGenBillException::BILL_FACTURA_ERROR_MSG);
        }        
        
        $is_done = $model->update($invoice_id, array(
            'file_factura_id' => $file->id
        ));      
        
        if (!$is_done) {
            throw new DocGenBillException(DocGenBillException::BILL_FACTURA_ERROR_MSG);
        }        
        
        return $file;
    }    
    
    
    
    public function generateBankInvoice($uid, $login, $sum)
    {
        global $DB;
        
        require_once(ABS_PATH . '/classes/sbr.php');
        require_once(ABS_PATH . '/classes/sbr_meta.php');
        
        $reqvs = sbr_meta::getUserReqvs($uid);
        $form_type = @$reqvs['form_type'];
        
        if(!$form_type) {
            throw new DocGenBillException(DocGenBillException::BANK_INVOICE_ERROR_MSG);
        }
        
        $reqv = $reqvs[$form_type];
        
        $num_id = $DB->insert(self::BANK_INVOICE_TABLE, array(
            'user_id' => $uid,
            'price' => $sum,
            'form_type' => $form_type,
            'rez_type' => $reqvs['rez_type'],
            'fields' => serialize($reqv)
        ),'id');
        
        if(!$num_id) {
            throw new DocGenBillException(DocGenBillException::BANK_INVOICE_ERROR_MSG);
        }
        
        $this->setFilePath($login);
        $this->setFileSrcId($uid);

        $this->setField('id', $num_id);
        $this->setField('login', $login);
        $this->setField('num_id', $num_id);
        $this->setField('datetext_1', date('Y-m-d H:i:s'));
        $this->setField('fio_emp', $reqvs);
        $phone = empty($reqv['phone'])?$reqv['phone']:$reqv['mob_phone'];
        $this->setField('phone', $phone);
        $this->setField('nonds_sum', $sum);
        $this->setField('nds_sum', $sum);
        $this->setField('price_sum', $sum);
        $this->setField('pricelong_sum', $sum);
        
        $original_name = $this->docs[self::BANK_INVOICE_TYPE]['name'];
        $this->docs[self::BANK_INVOICE_TYPE]['name'] = sprintf($original_name, $this->data['$num_id']);

        $file = $this->generate(self::BANK_INVOICE_TYPE);

        if(!$file) {
            throw new DocGenBillException(DocGenBillException::BANK_INVOICE_ERROR_MSG);
        }
        
        $is_done = $DB->update(self::BANK_INVOICE_TABLE, array(
            'file_id' => $file->id
        ), 'id = ?i', $num_id);

        if(!$is_done) {
            throw new DocGenBillException(DocGenBillException::BANK_INVOICE_ERROR_MSG);
        }
        
        return $file;
    }

}