<?php

require_once(__DIR__ . '/DocGen.php');
require_once(__DIR__ . '/Exception/DocGenReservesException.php');
require_once(__DIR__ . '/Formatter/DocGenReservesFormatter.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_order_history.php');

class DocGenReserves extends DocGen
{
    const FILE_PATH                 = 'users/%s/%s/reserves/%d/';
    const TEMPLATE_PATH             = '/templates/reserves/docs/';
    
    const BANK_INVOICE              = 'bank_invoice.odt';
    const BANK_INVOICE_TEXT         = 'Счет №%s';
    const BANK_INVOICE_TYPE         = 5;
    
    const ACT_COMPLETED_FRL         = 'act_completed_frl.odt';
    const ACT_COMPLETED_FRL_TEXT    = 'Акт о выполнении работы Исполнителем';
    const ACT_COMPLETED_FRL_TYPE    = 10;
    
    const ACT_SERVICE_EMP           = 'act_service_emp.odt';
    const ACT_SERVICE_EMP_TEXT      = 'Акт об оказании услуг Заказчику';
    const ACT_SERVICE_EMP_TYPE      = 20;
    
    const AGENT_REPORT              = 'agent_report.odt';
    const AGENT_REPORT_TEXT         = 'Отчет агента по Договору';
    const AGENT_REPORT_TYPE         = 30;
    
    const RESERVE_OFFER_CONTRACT        = 'contract.odt';
    const RESERVE_OFFER_CONTRACT_TEXT   = 'Договор %s';
    const RESERVE_OFFER_CONTRACT_TYPE   = 40;
    
    const RESERVE_OFFER_AGREEMENT       = 'agreement.odt';
    const RESERVE_OFFER_AGREEMENT_TEXT  = 'Соглашение %s';
    const RESERVE_OFFER_AGREEMENT_TYPE  = 50;

    const LETTER_FRL                = 'letter_frl.odt';
    const LETTER_FRL_TEXT           = 'Информационное письмо Исполнителю';
    const LETTER_FRL_TYPE           = 60;
    
    
    const ARBITRAGE_REPORT          = 'arbitrage_report.odt';
    const ARBITRAGE_REPORT_TEXT     = 'Отчет об арбитражном рассмотрении';
    const ARBITRAGE_REPORT_TYPE     = 70;
    
    
    const RESERVE_FACTURA           = 'factura.xls';
    const RESERVE_FACTURA_TEXT      = 'Счет-фактура';
    const RESERVE_FACTURA_TYPE      = 80;
    
    
    
    const RESERVE_SPECIFICATION         = 'specification.odt';
    const RESERVE_SPECIFICATION_TEXT    = 'Техническое задание';
    const RESERVE_SPECIFICATION_TYPE    = 90;
    
    
    
    protected $template_path        = self::TEMPLATE_PATH;
    protected $file_table           = 'file_reserves_order';
    
    protected $tmp_path = '/var/tmp/reserves/';
    
    protected $docs = array(
        self::RESERVE_OFFER_AGREEMENT_TYPE => array(
            'template'  => self::RESERVE_OFFER_AGREEMENT,
            'name'      => self::RESERVE_OFFER_AGREEMENT_TEXT,
            'error'     => DocGenReservesException::RESERVE_OFFER_AGREEMENT_ERROR_MSG
        ),
        
        self::RESERVE_OFFER_CONTRACT_TYPE => array(
            'template'  => self::RESERVE_OFFER_CONTRACT,
            'name'      => self::RESERVE_OFFER_CONTRACT_TEXT,
            'error'     => DocGenReservesException::RESERVE_OFFER_CONTRACT_ERROR_MSG
        ),        
        
        self::RESERVE_FACTURA_TYPE => array(
            'template' => self::RESERVE_FACTURA,
            'cellnames' => array('B2','B9','B10','B11','B12','B14','K18','M18','M19','S18','S19','W18','W19'),
            'name' => self::RESERVE_FACTURA_TEXT,
            'error' => DocGenReservesException::RESERVE_FACTURA_ERROR_MSG
        ),
        
        self::ACT_COMPLETED_FRL_TYPE => array(
            'template'  => self::ACT_COMPLETED_FRL,
            'name'      => self::ACT_COMPLETED_FRL_TEXT,
            'error'     => DocGenReservesException::ACT_COMPLETED_FRL_ERROR_MSG
        ),
        
        self::ACT_SERVICE_EMP_TYPE => array(
            'template'  => self::ACT_SERVICE_EMP,
            'name'      => self::ACT_SERVICE_EMP_TEXT,
            'error'     => DocGenReservesException::ACT_SERVICE_EMP_ERROR_MSG
        ),
        
        self::AGENT_REPORT_TYPE => array(
            'template'  => self::AGENT_REPORT,
            'name'      => self::AGENT_REPORT_TEXT,
            'error'     => DocGenReservesException::AGENT_REPORT_ERROR_MSG
        ),
        
        self::BANK_INVOICE_TYPE => array(
            'template'  => self::BANK_INVOICE,
            'name'      => self::BANK_INVOICE_TEXT,
            'error'     => DocGenReservesException::BANK_INVOICE_ERROR_MSG
        ),
        
        self::LETTER_FRL_TYPE => array(
            'template'  => self::LETTER_FRL,
            'name'      => self::LETTER_FRL_TEXT,
            'error'     => DocGenReservesException::LETTER_FRL_ERROR_MSG
        ),
      
        self::ARBITRAGE_REPORT_TYPE => array(
            'template'  => self::ARBITRAGE_REPORT,
            'name'      => self::ARBITRAGE_REPORT_TEXT,
            'error'     => DocGenReservesException::ARBITRAGE_REPORT_ERROR_MSG
        ),
        
        self::RESERVE_SPECIFICATION_TYPE => array(
            'template'  => self::RESERVE_SPECIFICATION,
            'name'      => self::RESERVE_SPECIFICATION_TEXT,
            'error'     => DocGenReservesException::RESERVE_SPECIFICATION_ERROR_MSG
        )
    );


    
    public $order;

    /**
     * Использовать ли очередь
     * @var type 
     */
    private $use_queue = true;

    
    public function __construct(Array $order) 
    {
        $this->setFormetter(new DocGenReservesFormatter());
        $this->order = $order;
        $this->setFileSrcId($order['id']);
        $this->setFilePath($order['id'], $order['employer']['login']);
    }
    
    
    public function setFilePath($order_id, $login) 
    {
        $this->file_path = sprintf(self::FILE_PATH, 
                substr($login, 0, 2), 
                $login, 
                $order_id);
    }

    
    public function beforeGenerate()
    {
        $history = new tservices_order_history($this->order['id']);
    }
    

    public function generate($type) 
    {
        if(!isset($this->docs[$type])) return false;
        extract($this->docs[$type]);
        
        $this->setTemplate($template);
        $this->setFileOriginalName($name);
        $file = parent::generate();
        if(!$file) throw new DocGenReservesException($error);
        
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
            throw new DocGenReservesException($error);
        }
        
        $file->updateFileParams(array('doc_type' => $type));
        return $file;
    }


    public function generateBankInvoice()
    {
        $original_name = $this->docs[self::BANK_INVOICE_TYPE]['name'];
        $this->docs[self::BANK_INVOICE_TYPE]['name'] = sprintf($original_name, $this->data['$num_bs']);
        
        if ($this->use_queue) {
            return $this->addToQueue(self::BANK_INVOICE_TYPE);
        }
        return $this->generate(self::BANK_INVOICE_TYPE);
    }

    


    /**
     * Акт о выполнении работы Исполнителем
     */
    public function generateActCompletedFrl()
    {
        $this->setField('datetext_1', $this->order['close_date']);
        $this->setField('fio_emp', $this->order['reserve']->getEmpReqv());
        $this->setField('fio_frl', $this->order['reserve']->getFrlReqv());
        $this->setField('date_confirm', $this->order['reserve_data']['date']);
        $this->setField('num_bs', $this->order['reserve_data']['src_id']);
        $this->setField('date_reserve', $this->order['reserve_data']['date_reserve']);
        $this->setField('text3_info', array(
            'order_id' => $this->order['reserve_data']['src_id'],
            'reserve_data' => $this->order['reserve_data'],
            'reserve' => $this->order['reserve']
        ));
        $this->setField('details_emp', array(
            'reqv' => $this->order['reserve']->getEmpReqv(),
            'email' => $this->order['employer']['email']
        ));
        $this->setField('details_frl', array(
            'reqv' => $this->order['reserve']->getFrlReqv(),
            'email' => $this->order['freelancer']['email']
        ));
        
        return $this->addToQueue(self::ACT_COMPLETED_FRL_TYPE);
    }

    
    /**
     * Акт об оказании услуг Заказчику
     */
    public function generateActServiceEmp()
    {
        $this->setField('datereqv_complete', $this->order['reserve']);
        $this->setField('date_reserve', $this->order['reserve_data']['date_reserve']);
        $this->setField('num_bs', $this->order['reserve_data']['src_id']);
        $this->setField('fio_emp', $this->order['reserve']->getEmpReqv());
        $this->setField('fio_frl', $this->order['reserve']->getFrlReqv());
        $this->setField('details_emp', array(
            'reqv' => $this->order['reserve']->getEmpReqv(),
            'email' => $this->order['employer']['email']
        ));
        $this->setField('text5_info', $this->order['reserve_data']);
        $this->setField('text6_info', $this->order['reserve_data']);
        $this->setField('pricelong_commision', $this->order['reserve_data']['tax_price']);
        $this->setField('nds_commision', $this->order['reserve_data']['tax_price']);
        
        return $this->addToQueue(self::ACT_SERVICE_EMP_TYPE);
    }
    
    
    /**
     * Отчет агента по Договору
     */
    public function generateAgentReport()
    {
        $this->setField('num_bs', $this->order['id']);
        $this->setField('date_confirm', $this->order['reserve_data']['date']);
        $this->setField('date_reserve', $this->order['reserve_data']['date_reserve']);
        $this->setField('datereqv_complete', $this->order['reserve']);
        $this->setField('fio_emp', $this->order['reserve']->getEmpReqv());
        $this->setField('fio_frl', $this->order['reserve']->getFrlReqv());
        $this->setField('pricelong_reserve_price', $this->order['reserve_data']['reserve_price']);
        $this->setField('pricelong_price', $this->order['reserve_data']['price']);
        $this->setField('pricelong_commision', $this->order['reserve_data']['tax_price']);
        $this->setField('nds_commision', $this->order['reserve_data']['tax_price']);
        $this->setField('text4_info', $this->order['reserve']);
        
        $original_name = $this->docs[self::AGENT_REPORT_TYPE]['name'];
        $this->docs[self::AGENT_REPORT_TYPE]['name'] =  $original_name . ' ' . $this->data['$num_bs'];
        
        return $this->addToQueue(self::AGENT_REPORT_TYPE);
    }
    
    
    
    
    /**
     * Договор и Соглашение
     * 
     * @throws DocGenReservesException
     */
    public function generateOffers()
    {
        $this->setField('num_bs', $this->order['id']);
        $this->setField('date_reserve', $this->order['reserve']->getDate());       
        
        //AGREEMENT
        $original_name = $this->docs[self::RESERVE_OFFER_AGREEMENT_TYPE]['name'];
        $this->docs[self::RESERVE_OFFER_AGREEMENT_TYPE]['name'] = 
                sprintf($original_name, $this->docFormatter->num($this->order['id']));
        $ok_1 = $this->addToQueue(self::RESERVE_OFFER_AGREEMENT_TYPE);
        
        //CONTRACT
        $original_name = $this->docs[self::RESERVE_OFFER_CONTRACT_TYPE]['name'];
        $this->docs[self::RESERVE_OFFER_CONTRACT_TYPE]['name'] = 
                sprintf($original_name, $this->docFormatter->num($this->order['id']));
        $ok_2 = $this->addToQueue(self::RESERVE_OFFER_CONTRACT_TYPE);
        
        return $ok_1 && $ok_2;
    }
    
    
    
    /**
     * Информационное письмо Исполнителю
     */
    public function generateInformLetterFRL() 
    {
        //Удаляем старый файл
        $this->deleteFiles($this->order['id'], self::LETTER_FRL_TYPE);
        
        $price = $this->order['reserve']->getPayoutSum();
        
        $this->setField('num_bs', $this->order['id']);
        $this->setField('date_reserve', $this->order['reserve_data']['date_reserve']);
        $this->setField('fio_frl', $this->order['reserve']->getFrlReqv());
        $this->setField('orderurl', $this->order['id']);
        $this->setField('date_act', $this->order['close_date']);
        $this->setField('pricelong_frl', $price);
        $this->setField('ndflprice_frl', $this->order['reserve']);
        $this->setField('ndsprice_frl', array(
            'reqv' => $this->order['reserve']->getFrlReqv(), 
            'price' => $price
        ));
        $this->setField('dettitle_frl', $this->order['reserve']->getFrlReqv()); 
        $this->setField('details_frl', array(
            'reqv' => $this->order['reserve']->getFrlReqv(),
            'email' => $this->order['freelancer']['email']
        ));
        
        $this->setField('text7_info', $this->order['reserve']->getFrlReqv());

        return $this->addToQueue(self::LETTER_FRL_TYPE);
    }
    
    
    /**
     * Отчет об арбитражном рассмотрении
     */
    public function generateArbitrageReport()
    {
        $this->setField('num_bs', $this->order['id']);
        $this->setField('date_arbclose', $this->order['reserve_data']['arbitrage_date_close']);
        $this->setField('date_reserve', $this->order['reserve_data']['date_reserve']);
        $this->setField('fio_emp', $this->order['reserve']->getEmpReqv());
        $this->setField('fio_frl', $this->order['reserve']->getFrlReqv());
        $this->setField('date_confirm', $this->order['reserve_data']['date']);
        $is_emp = $this->order['reserve_data']['arbitrage_is_emp'] == 't';
        $this->setField('empfrl', ($is_emp)?'Заказчик':'Исполнитель');
        $this->setField('pricelong_price', $this->order['reserve_data']['price']);
        $this->setField('title', $this->order['title']);
        
        $description = $this->order['description'];
        
        if($this->order['type'] == 0)
        {
            
            $this->setField('description', sprintf("Что вы получите \n%s", $description));
            
            $requirement = $this->order['requirement'];
            $this->setField('requirement', sprintf("Что нужно, чтобы начать \n%s", $requirement)); 
            
            if($this->order['order_extra'])
            {
                $this->setField('tuextra_info',array(
                    'order_extra' => $this->order['order_extra'],
                    'extra' => $this->order['extra']
                ));
            }
        }
        else
        {
            $this->setField('description', $description);
        }
        
        $this->setField('daytext_orderdays', $this->order['order_days']);
        
        //$this->setField('text2_info', $this->order['reserve_data']);
        $this->setField('text2top1_info', $this->order['reserve_data']);
        $this->setField('text2top2_info', $this->order['reserve_data']);
        $this->setField('text2mid_info', $this->order['reserve_data']);
        $this->setField('text2bot_info', $this->order['reserve_data']);
        
        return $this->addToQueue(self::ARBITRAGE_REPORT_TYPE);
    }
    
    
    
    
    
    /**
     * Генерация счет-фактуры
     * 
     * @return type
     */
    public function generateFactura()
    {
        $order = $this->order;
        
        //Удаляем старую фактуру
        $this->deleteFiles($order['id'], self::RESERVE_FACTURA_TYPE);
        
        $this->setField('name_emp', $order['employer']['reqv']);
        $this->setField('address_emp', $order['employer']['reqv']);
        $this->setField('inn_emp', $order['employer']['reqv']);
        $this->setField('kpp_emp', $order['employer']['reqv']);
        
        $this->setField('price_sf_summa', $order['sf_summa']);
        $this->setField('nonds_sf_summa', $order['sf_summa']);
        $this->setField('pricends_sf_summa', $order['sf_summa']);
        
        $this->setField('num_bs', $order['id']);
        unset($order['id'], $order['employer']);

        foreach ($order as $key => $value) {
            $this->setField($key, $value);
        }

        return $this->addToQueue(self::RESERVE_FACTURA_TYPE);
    }



    
    /**
     * Техническое задание
     */
    public function generateSpecification() 
    {
        $this->setField('num_bs', $this->order['id']);
        $this->setField('date_reserve', $this->order['reserve_data']['date_reserve']);
        $this->setField('fio_emp', $this->order['reserve']->getEmpReqv());
        $this->setField('fio_frl', $this->order['reserve']->getFrlReqv());
        $this->setField('description', $this->order['description']);
        $this->setField('pricelong_price', $this->order['reserve_data']['price']);
        $this->setField('worktime_order', array(
            'days' => $this->order['order_days'],
            'date' => $this->order['reserve_data']['date_reserve']
        ));
        $this->setField('country_frl', array(
            'reqv' => $this->order['reserve']->getFrlReqv(),
            'user_country_id' => $this->order['freelancer']['country']
        ));
        $this->setField('messages', $this->order['reserve']->getReserveMessages(
                $this->order['id'], 
                $this->order['reserve_data']['date_reserve']
            ));
        
        return $this->addToQueue(self::RESERVE_SPECIFICATION_TYPE);
    }
    
    

    
    /**
     * Удаление файлов указанных типов
     * @todo Удалять из очереди тоже нужно наверно
     * 
     * @param type $srcId
     * @param type $types
     * @return boolean
     */
    public function deleteFiles($srcId, $types)
    {
        $types = !is_array($types)?array($types):$types;
        
        $this->clearQueue($srcId, $types);
        
        $rows = CFile::selectFilesBySrc($this->file_table, $srcId);
        
        if (!$rows) {
            return false;
        }
        
        foreach ($rows as $row)
        {
            if(!in_array($row['doc_type'], $types)) {
                continue;
            }
        
            $file = new CFile();
            $file->Delete($row['id']);            
        }
        
        return true;
    }
    
    /**
     * Возвращает данные, необходимые для воссоздания экземпляра класса
     * @return type
     */
    protected function getConstructorParams()
    {
        return array(array(
            'id' => $this->order['id'],
            'employer' => array(
                'login' => $this->order['employer']['login']
            )
        ));
    }
    
    public function disableQueue()
    {
        $this->use_queue = false;
    }
    
    
    
    
}