<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesTServiceOrderModel.php');

class ReservesAdminOrderModel extends ReservesTServiceOrderModel
{
    protected $filters = array();
    protected $filter_data = array();
    protected $filter_type = 'all';
    protected $filter_query;

    /**
     * Расширение запроса выборки
     * 
     * @var type 
     */
    protected $query_ext = array();


    static $_substatus_txt = array(
        self::SUBSTATUS_NONE => 'нет',
        self::SUBSTATUS_NEW => 'назначен',
        self::SUBSTATUS_INPROGRESS => 'в процессе',
        self::SUBSTATUS_PAYED => 'выполнено',
        self::SUBSTATUS_ERR => 'ошибка'
    );

    static $_payout_status_txt = array(
        -1 => 'новый',
        0 => 'успех',
        1 => 'в обработке',
        2 => 'отвергнут'
    );

    const ReserveOrderStatus_ArbitrageEmp = 51;
    const ReserveOrderStatus_ArbitrageFrl = 52;
    
    
    public function isStatusBackAllowChange()
    {
        return in_array($this->reserve_data['status_back'], array(
            self::SUBSTATUS_NEW,
            self::SUBSTATUS_INPROGRESS,
            self::SUBSTATUS_ERR
        ));
    }

    
    public function getStatusPayTxt()
    {
        $idx = intval($this->getStatusPay());
        return @self::$_substatus_txt[$idx];
    }

    
    public function getStatusBackTxt()
    {
        $idx = intval($this->getStatusBack());
        return @self::$_substatus_txt[$idx];
    }

    public function setFilterType($filter_type)
    {
        $this->filter_type = $filter_type;
    }
    
    public function setFilterData($filter_data)
    {
        $this->filter_data[$this->filter_type] = $filter_data;
    }

    public function getFilterData()
    {
        return @$this->filter_data[$this->filter_type];
    }

    public function getFilter()
    {
       if (empty($this->filters)) {

           $this->query_ext = array(
               'frod' => array(
                   'select' => $this->db()->parse('
                        f.login AS frl_login,
                        e.login AS emp_login,
                        rpr.date AS date_payout
                        '), 
                   'join' => $this->db()->parse('
                       LEFT JOIN freelancer AS f ON f.uid = r.frl_id 
                       LEFT JOIN employer AS e ON e.uid = r.emp_id
                       LEFT JOIN reserves_payout_reqv AS rpr ON rpr.reserve_id = r.id
                   ')
                ),
               
               'all' => array(
                   'select' => $this->db()->parse('
                       rb.id::boolean AS is_invoice,
                       rp.id::boolean AS is_pay_req
                    '),
                   'join' => $this->db()->parse('
                       LEFT JOIN reserves_payout AS rp ON rp.reserve_id = r.id
                       LEFT JOIN ' . ReservesBank::$_TABLE . ' AS rb ON rb.reserve_id = r.id
                    ')
               )
           );
           
           $this->filters = array(
               'frod' => array(
                    'is_frod' => array(
                        'where' => 'r.is_frod = ?',
                        'order' => array('asc' => 'src.id', 'desc' => 'src.id DESC')
                    )
               ),
               'all' => array(
                    'date' => array(
                        'type' => 'Date',
                        'label' => 'Создано',
                        'where' => 'src.date::date = ?',
                        'order' => array('asc' => 'src.date', 'desc' => 'src.date DESC')
                    ),
                    'date_reserve' => array(
                        'type' => 'Date',
                        'label' => 'Резерв',
                        'where' => 'r.date_reserve::date = ?',
                        'order' => array('asc' => 'r.date_reserve', 'desc' => 'r.date_reserve DESC')
                    ),
                    'date_complete' => array(
                        'type' => 'Date',
                        'label' => 'Закрыт',
                        'where' => 'r.date_complete::date = ?',
                        'order' => array('asc' => 'r.date_complete', 'desc' => 'r.date_complete DESC')
                    ),
                    'id' => array(
                        'label' => '№ дог./плат.',
                        'where' => 'src.id = ?i OR r.invoice_id = ?i OR rp.id = ?i',
                        'order' => array('asc' => 'src.id', 'desc' => 'src.id DESC'),
                        'filters' => array('Digits')
                    ),
                    'title' => array(
                        'label' => 'Название',
                        'where' => 'src.title LIKE ?',
                        'filter' => function($value){ return "%{$value}%"; },
                        'order' => array('asc' => 'src.title', 'desc' => 'src.title DESC')
                    ),
                    'order_price' => array(
                        'label' => 'Бюджет',
                        'where' => 'src.order_price = ?i',
                        'order' => array('asc' => 'src.order_price', 'desc' => 'src.order_price DESC'),
                        'filters' => array('Digits')
                    ),
                    'reserve_type' => array(
                        'type' => 'Select',
                        'label' => 'Резерв',
                        'multioptions' => array(
                            NULL => 'Все',
                            1 => 'БН',
                            2 => 'ЯК'                         
                        ),
                        'where' => array(
                            1 => $this->db()->parse('(((rb.id > 0) OR (r.status >= ?i)) AND r.invoice_id IS NULL)', self::STATUS_RESERVE),
                            2 => $this->db()->parse('r.status >= ?i AND r.invoice_id > 0', self::STATUS_RESERVE)
                        )
                    ),
                    'payout_type' => array(
                        'type' => 'Select',
                        'label' => 'Выплата',
                        'multioptions' => array(
                            NULL => 'Все',
                            1 => 'БН',
                            2 => 'ЯК'                         
                        ),
                        'where' => array(
                            1 => $this->db()->parse('r.status_pay = ?i AND rp.id IS NULL', self::SUBSTATUS_PAYED),
                            2 => $this->db()->parse('r.status_pay = ?i AND rp.id > 0', self::SUBSTATUS_PAYED)
                        )
                    ),    
                    'payback_type' => array(
                        'type' => 'Select',
                        'label' => 'Возврат',
                        'multioptions' => array(
                            NULL => 'Все',
                            1 => 'БН',
                            2 => 'ЯК'                         
                        ),
                        'where' => array(
                            1 => $this->db()->parse('r.status_back = ?i AND rb.id > 0 AND r.invoice_id IS NULL', self::SUBSTATUS_PAYED),
                            2 => $this->db()->parse('r.status_back = ?i AND r.invoice_id > 0', self::SUBSTATUS_PAYED)
                        )
                    ),                                
                    'order_days' => array(
                        'label' => 'Срок',
                        'where' => 'src.order_days = ?i',
                        'order' => array('asc' => 'src.order_days', 'desc' => 'src.order_days DESC'),
                        'filters' => array('Digits')
                    ),
                    'status' => array(
                        'type' => 'Select',
                        'label' => 'Статус',
                        'where' => array(
                            1 => 'r.id IS NULL AND src.status >= ' . TServiceOrderModel::STATUS_NEW,
                            2 => 'src.status < ' . TServiceOrderModel::STATUS_NEW,
                            3 => $this->db()->parse('r.id IS NOT NULL AND r.status = ?i AND src.status = ?i', 
                                    self::STATUS_NEW, 
                                    TServiceOrderModel::STATUS_NEW),
                            4 => $this->db()->parse('ra.id IS NULL AND r.status = ?i AND src.status IN(?l)',
                                    self::STATUS_RESERVE,
                                    array(TServiceOrderModel::STATUS_ACCEPT,TServiceOrderModel::STATUS_FIX)
                                    ),
                            5 => $this->db()->parse('ra.id IS NOT NULL AND ra.status = ?i',
                                    ReservesArbitrage::STATUS_OPEN
                                    ),
                            6 => $this->db()->parse('r.status_pay IN(?l) OR r.status_back IN(?l)',
                                    array(self::SUBSTATUS_NEW,self::SUBSTATUS_INPROGRESS, self::SUBSTATUS_ERR),
                                    array(self::SUBSTATUS_NEW,self::SUBSTATUS_INPROGRESS, self::SUBSTATUS_ERR)
                                    ),
                            7 => $this->db()->parse('r.status IN(?l)',array(self::STATUS_PAYED, self::STATUS_ARBITRAGE)),
                            8 => $this->db()->parse('r.status = ?i AND src.status = ?i AND ra.id IS NULL',
                                    self::STATUS_RESERVE,
                                    TServiceOrderModel::STATUS_FRLCLOSE
                                    )
                        ),
                        'multioptions' => array(
                            NULL => 'Все',
                            1 => 'Согласование',
                            2 => 'Отменен',
                            3 => 'Резервирование',
                            4 => 'В работе',
                            8 => 'Выполнен',
                            5 => 'Арбитраж',
                            6 => 'Выплата',
                            7 => 'Закрыт'                            
                        )
                    )
               )
           );

       }

       return (isset($this->filters[$this->filter_type]))?$this->filters[$this->filter_type]:array();
    }

   

    protected function _getFilterQuery($what = 'where')
    {
        if (!isset($this->filter_query[$this->filter_type])) {
            
            $_where_sql = '';
            $_order_sql = '';
            
            $data = $this->getFilterData();
            $dir = isset($data['dir'])?$data['dir']:'asc';
            $dir_col = isset($data['dir_col'])?$data['dir_col']:'';
            
            if ($data) {
                $filter = $this->getFilter();
                
                foreach ($data as $key => $value){
                    if(empty($value) || !isset($filter[$key]['where'])) continue;
                    $_where = is_array($filter[$key]['where'])?$filter[$key]['where'][$value]:$filter[$key]['where'];
                    $value = (isset($filter[$key]['filter']))?$filter[$key]['filter']($value):$value;
                    $_where_sql .= $this->db()->parse(" AND {$_where}", $value, $value, $value);
                }
                
                if (isset($filter[$dir_col]['order'])) {
                    $_order_sql = $filter[$dir_col]['order'][$dir];
                }
            }
            
            $this->filter_query[$this->filter_type] = array(
                'where' => $_where_sql,
                'order' => $_order_sql
            );
        }
        
        return $this->filter_query[$this->filter_type][$what];
    }

    

    protected function _getSrcWhere()
    { 
        $sql = $this->_getFilterQuery('where');
        return $this->db()->parse("WHERE src.pay_type = ?i {$sql}",
                TServiceOrderModel::PAYTYPE_RESERVE);
    }

    
    protected function _getSrcOrder()
    {
        $sql = $this->_getFilterQuery('order');
        return (empty($sql))?parent::_getSrcOrder():"ORDER BY {$sql}";
    }
    
    
    protected function _getSrcSelect()
    {
        $_select = $this->db()->parse("
            src.date AS src_date,
            src.title,
            src.order_price,
            src.order_days,
            src.status AS src_status
        ");
        
        if (isset($this->query_ext[$this->filter_type]['select'])) {
            $_select .= ',' . $this->query_ext[$this->filter_type]['select'];
        }
        
        return $_select;
    }
    
    
    protected function _getSrcJoin()
    {
        if (isset($this->query_ext[$this->filter_type]['join'])) {
            return $this->query_ext[$this->filter_type]['join']; 
        }
        
        return '';
    } 
    
    
    public function getSrcTitle()
    {
        return @$this->reserve_data['title'];
    }
    
    
    /**
     * Есть ли выставленный заказчиком счет
     * @return boolean
     */
    public function isInvoice()
    {
        return @$this->reserve_data['is_invoice'] === 't';
    }

    /**
     * Резерв БС был оплачен
     * 
     * @return type
     */
    public function reserveIsPayed()
    {
        return $this->isReserveByService() > 0 || 
               ($this->isInvoice() && in_array(@$this->reserve_data['status'], array(
                   self::STATUS_RESERVE,
                   self::STATUS_PAYED,
                   self::STATUS_ARBITRAGE
               )));
    }

    
    /**
     * Заказ готов к резервированию средств по безналу?
     * 
     * @return type
     */
    public function isAllowBankReserve()
    {
        return !$this->isReserveByService() && 
                $this->isInvoice() && 
                in_array(@$this->reserve_data['status'], array(
                   self::STATUS_ERR,
                   self::STATUS_INPROGRESS,
                   self::STATUS_NEW
               ));
    }

    
    
    public function isPayoutByService()
    {
        return $this->reserve_data['is_pay_req'] == 't';
    }    
    

    public function getSrcDate()
    {
        return date('d.m.Y H:i',strtotime(@$this->reserve_data['src_date']));
    }
    
    
    public function getCompleteDate()
    {
        if (!@$this->reserve_data['date_complete']) {
            return '';
        }
        
        return date('d.m.Y H:i',strtotime(@$this->reserve_data['date_complete']));
    }    
    
    
    public function getReserveDate()
    {
        if (!@$this->reserve_data['date_reserve']) {
            return '';
        }
        
        return date('d.m.Y H:i',strtotime(@$this->reserve_data['date_reserve']));
    }
     
    
    public function getSrcPrice()
    {
        return tservices_helper::cost_format(@$this->reserve_data['order_price'], false);
    }
    
    
    public function getSrcDays()
    {
        return tservices_helper::days_format(@$this->reserve_data['order_days']);
    }
   
    
    public function getSrcStatus()
    {
        return $this->getReserveDataByKey('src_status');
    }


    
    public function getStatusText()
    {
        return self::$_reserve_order_status_txt[$this->getReserveOrderStatus()];
    }
    
    
    public function _getStatusText()
    {
        $text = '&mdash;';
        
        if( @$this->reserve_data['src_status'] >= TServiceOrderModel::STATUS_NEW) {
            if ($this->isReserveData()) {
                if ($this->isStatusNew()) {
                    $text = 'pезервирование';
                } elseif ($this->isClosed()) {
                    $text = 'закрыт';
                } elseif ($this->isArbitrageOpen()) {
                    $text = 'арбитраж';
                } elseif(!($this->isStatusPayNone() || $this->isStatusPayPayed()) || 
                         !($this->isStatusBackNone() || $this->isStatusBackPayed())) {
                    $text = 'выплата';
                } else {
                    $text = 'в работе';
                }                
            }else {
                $text = 'cогласование';
            }
        } else {
            $text = 'отменен';
        }
        
        
        return $text;
    }
    
    
    /**
     * Карточка резерва БС
     * 
     * @param type $src_id
     * @return type
     */
    public function getReserveAdmin($src_id) 
    {
        $src_table = static::$_TABLE_SRC;
        
        $sql = $this->db()->parse("
            SELECT 
                r.*,
                ra.id AS arbitrage_id,
                ra.status AS arbitrage_status, 
                ra.is_emp AS arbitrage_is_emp,
                ra.date AS arbitrage_date,
                ra.date_close AS arbitrage_date_close,
                ra.message AS arbitrage_message,
                ra.price AS arbitrage_price,
                (r.price - ra.price) AS arbitrage_payback,
                ra.allow_fb_frl AS arbitrage_allow_fb_frl,
                ra.allow_fb_emp AS arbitrage_allow_fb_emp,
                src.id AS src_id,
                {$this->_getSrcSelect()},
                rb.id::boolean AS is_invoice
            FROM {$src_table} AS src
            LEFT JOIN {$this->TABLE} AS r ON r.src_id = src.id 
            LEFT JOIN " . ReservesArbitrage::$_TABLE . " AS ra ON ra.reserve_id = r.id 
            LEFT JOIN " . ReservesBank::$_TABLE . " AS rb ON rb.reserve_id = r.id
            WHERE 
                src.id = ?i
            LIMIT 1
        ", $src_id);        
        
        $row = $this->db()->row($sql);
            
        if($row) {
            $this->setReserveData($row);
        }
        
        return $row;
    }

    
    public function getDateByKey($key)
    {
        $date = @$this->reserve_data[$key];
        if(!$date) return ' — ';
        return date('d.m.Y H:i',strtotime($date));
    }      
    
    public function getPriceByKey($key, $currency = false)
    {
        $price = @$this->reserve_data[$key];
        if(!$price) return ' — ';
        return tservices_helper::cost_format($price, $currency);
    }     
    
    
    public function getPayoutsInfo()
    {
       require_once(ABS_PATH . '/classes/reserves/ReservesPayout.php');
       $reservesPayout = new ReservesPayout();
       $payoutList = $reservesPayout->getPayouts($this->getID());
       if($payoutList) {
           foreach($payoutList as $key => $payoutItem) {
               $payoutList[$key]['price'] = tservices_helper::cost_format($payoutItem['price'], true);
               $payoutList[$key]['status_txt'] = self::$_payout_status_txt[$payoutItem['status']];
               $payoutList[$key]['error'] = ($payoutItem['error'] == 0)?'нет':$payoutItem['error'];
               $payoutList[$key]['date'] = date('d.m.Y H:i',strtotime($payoutItem['date']));
               $payoutList[$key]['last'] = ($payoutItem['last'])?date('d.m.Y H:i',strtotime($payoutItem['last'])):' — ';
               $payoutList[$key]['techmessage'] = ($payoutList[$key]['techmessage'])?$payoutList[$key]['techmessage']:' — ';
           } 
       }

       $payoutReqv = $reservesPayout->getPayoutReqv($this->getID());
       if($payoutReqv) {
           require_once(ABS_PATH . '/classes/reserves/ReservesPayoutPopup.php');
           $payoutReqv['pay_type_txt'] = ReservesPayoutPopup::$payments_short_text[$payoutReqv['pay_type']];
           $payoutReqv['date'] = date('d.m.Y H:i',strtotime($payoutReqv['date']));
           $payoutReqv['last'] = ($payoutReqv['last'])?date('d.m.Y H:i',strtotime($payoutReqv['last'])):' — ';
           $payoutReqv['fields'] = mb_unserialize($payoutReqv['fields']);
           $payoutReqv['is_bank'] = $payoutReqv['pay_type'] == ReservesPayoutPopup::PAYMENT_TYPE_BANK;
           $payoutReqv['is_allow_change_status'] = $payoutReqv['is_bank'] && !$this->isStatusPayPayed();
       }

       $payoutLog = $reservesPayout->getErrorLog($this->getID());

       $payouts = array(
           'list' => $payoutList,
           'reqv' => $payoutReqv,
           'log' => $payoutLog
       );
       
       return $payouts;
    }
    
    
    
    public function getPaybackInfo() 
    {
        require_once(ABS_PATH . '/classes/reserves/ReservesPayback.php');
        
        $reservesPayback = new ReservesPayback();
        $paybackList = $reservesPayback->getPayback($this->getID());

        if ($paybackList) {
           foreach ($paybackList as $key => $paybackItem) {
               $paybackList[$key]['price'] = tservices_helper::cost_format($paybackItem['price'], true);
               $paybackList[$key]['status_txt'] = self::$_payout_status_txt[$paybackItem['status']];
               $paybackList[$key]['error'] = ($paybackItem['error'] == 0)?'нет':$paybackItem['error'];
               $paybackList[$key]['date'] = date('d.m.Y H:i',strtotime($paybackItem['date']));
               $paybackList[$key]['last'] = ($paybackItem['last'])?date('d.m.Y H:i',strtotime($paybackItem['last'])):' — ';
           } 
       }
        
       return $paybackList;
    }
    
    
    
    public function switchStatus($to_status)
    {
        $current_status = $this->getReserveOrderStatus();
        
        //Если статус совпадает с текущим то не меняем
        if($current_status == $to_status || 
           !$this->isReserveData() ||
           ($this->isArbitrage() && in_array($to_status, array(
               self::ReserveOrderStatus_ArbitrageEmp, 
               self::ReserveOrderStatus_ArbitrageFrl)))) {
            return false;
        }
        
        switch($to_status) {
            
            case self::ReserveOrderStatus_ArbitrageEmp:
                $is_emp = true;
            case self::ReserveOrderStatus_ArbitrageFrl:
                if(!isset($is_emp)) {
                    $is_emp = false;
                }
                
                $this->db()->start();

                $this->db()->update(self::$_TABLE_SRC, array(
                    'close_date' => NULL,
                    'status' => 1
                ), 'id = ?i', $this->getSrcId());                  
                
                $this->db()->update(self::$_TABLE, array(
                    'status' => self::STATUS_RESERVE,
                    'date_complete' => NULL,
                    'status_pay' => NULL,
                    'status_back' => NULL
                ), 'id = ?i', $this->getID());                  
                
                $this->db()->query("
                    DELETE FROM ".ReservesArbitrage::$_TABLE." 
                    WHERE reserve_id = ?i;                    

                    DELETE FROM reserves_payback 
                    WHERE reserve_id = ?i;

                    DELETE FROM reserves_payout 
                    WHERE reserve_id = ?i;

                    DELETE FROM reserves_payout_reqv 
                    WHERE reserve_id = ?i;", 
                $this->getID(),
                $this->getID(), 
                $this->getID(), 
                $this->getID());

                $this->db()->insert(ReservesArbitrage::$_TABLE, array(
                    'reserve_id' => $this->getID(),
                    'is_emp' => $is_emp,
                    'frl_id' => $this->getFrlId(),
                    'emp_id' => $this->getEmpId(),
                    'message' => 'Разрешение конфликтной ситуации по заказу.'
                ));
                
                if(!$this->db()->commit()) {
                    $this->db()->rollback();
                }                 
                
                break;
                

            case self::ReserveOrderStatus_InWork:
                
                $this->db()->start();

                $this->db()->update(self::$_TABLE_SRC, array(
                    'accept_date' => 'NOW()',
                    'close_date' => NULL,
                    'status' => 1
                ), 'id = ?i', $this->getSrcId());            
                
                $this->db()->update(self::$_TABLE, array(
                    'status' => self::STATUS_RESERVE,
                    //'date_reserve' => 'NOW()',
                    'date_complete' => NULL,
                    'status_pay' => NULL,
                    'status_back' => NULL
                ), 'id = ?i', $this->getID());                
                
                $this->db()->query("
                    DELETE FROM ".ReservesArbitrage::$_TABLE." 
                    WHERE reserve_id = ?i;

                    DELETE FROM reserves_payback 
                    WHERE reserve_id = ?i;

                    DELETE FROM reserves_payout 
                    WHERE reserve_id = ?i;

                    DELETE FROM reserves_payout_reqv 
                    WHERE reserve_id = ?i;",
                $this->getID(), 
                $this->getID(), 
                $this->getID(), 
                $this->getID());

                if(!$this->db()->commit()) {
                    $this->db()->rollback();
                }                 
                
            break;
            
            
            //@todo: Можно предусмотреть удаление существующего резерва 
            //и возврата суммы на ЛС
            case self::ReserveOrderStatus_Reserve:

                $this->db()->start();

                $this->db()->update(self::$_TABLE_SRC, array(
                    'accept_date' => NULL,
                    'close_date' => NULL,
                    'status' => 0
                ), 'id = ?i', $this->getSrcId());

                $this->db()->update(self::$_TABLE, array(
                    'status' => self::STATUS_NEW,
                    'date_reserve' => NULL,
                    'date_complete' => NULL,
                    'acc_op_id' => 0,
                    'invoice_id' => NULL,
                    'status_pay' => NULL,
                    'status_back' => NULL
                ), 'id = ?i', $this->getID());

                $this->db()->query("
                    DELETE FROM ".ReservesBank::$_TABLE." 
                    WHERE reserve_id = ?i;

                    DELETE FROM ".ReservesArbitrage::$_TABLE." 
                    WHERE reserve_id = ?i;

                    DELETE FROM reserves_payback 
                    WHERE reserve_id = ?i;

                    DELETE FROM reserves_payout 
                    WHERE reserve_id = ?i;

                    DELETE FROM reserves_payout_reqv 
                    WHERE reserve_id = ?i;",
                $this->getID(), 
                $this->getID(), 
                $this->getID(), 
                $this->getID(), 
                $this->getID());

                if(!$this->db()->commit()) {
                    $this->db()->rollback();
                }     
                
                break;
        }
        
        $orderHistoryModel = $this->getOrderHistory();
        $orderHistoryModel->adminChangeStatus();
        
        return true;
    }
    
    
    
    public function updateDocs($types = array(), $is_create = false, $override_data = array())
    {
        ini_set('max_execution_time', 300);
        //ini_set('memory_limit', '512M');
        
        if (empty($types)) {
            $is_create = true;
        }
        
        if (!$this->isReserveData()) {
            return false;
        }
        
        $orderModel = TServiceOrderModel::model();
        $orderModel->attributes(array('is_adm' => true));
        $orderData = $orderModel->getCard($this->getSrcId(), 0);
        
        if (!$orderData || 
           !$orderModel->isReserve()) {
            
            return false;
        }
        
        $reserveInstance = $orderModel->getReserve();
        
        if ($is_create) {
            
            //Если зарезервировали
            if ($reserveInstance->isStatusReserved()) {
                $base_doc_types[] = DocGenReserves::RESERVE_SPECIFICATION_TYPE;
                $base_doc_types[] = DocGenReserves::RESERVE_OFFER_CONTRACT_TYPE;
                $base_doc_types[] = DocGenReserves::RESERVE_OFFER_AGREEMENT_TYPE;
            }
            
            //Если резерв по безналу то нужен счет
            if (!$reserveInstance->isReserveByService()) {
                $base_doc_types[] = DocGenReserves::BANK_INVOICE_TYPE;
            }
            
            //Если сделка закрыта и исполнителю полагается выплата либо выплата в процессе
            if ($reserveInstance->isStatusPayInprogress() || 
                    ($reserveInstance->isClosed() && 
                    (!$reserveInstance->isArbitrage() || $reserveInstance->isStatusPayPayed()))
                ) {
                $base_doc_types[] = DocGenReserves::ACT_COMPLETED_FRL_TYPE;
            }
            
            //Если сделка закрыта либо выплата в процессе
            if ($reserveInstance->isClosed() || $reserveInstance->isStatusPayInprogress()) {
                $base_doc_types[] = DocGenReserves::LETTER_FRL_TYPE;
            }
            
            //Если сделка закрыта
            if ($reserveInstance->isClosed()) {
                $base_doc_types[] = DocGenReserves::ACT_SERVICE_EMP_TYPE;
                $base_doc_types[] = DocGenReserves::AGENT_REPORT_TYPE;
            }
            
            //Если сделка закрыта по арбитражу
            if ($reserveInstance->isArbitrage() && $reserveInstance->isArbitrageClosed()) {
                $base_doc_types[] = DocGenReserves::ARBITRAGE_REPORT_TYPE;
            }
            
            $types = array_merge($types, $base_doc_types);
        }

        $types = array_unique($types);

        require_once(ABS_PATH . '/classes/DocGen/DocGenReserves.php');
        $this->getOrderHistory();
        $doc = new DocGenReserves($orderData);
        $doc->deleteFiles($this->getSrcId(), $types);    
        
        if (!empty($override_data)) {
            foreach($override_data as $key => $value) {
                $doc->setOverrideField($key, $value);
            }
        }
            
        foreach ($types as $type) {
            switch ($type) {
                case DocGenReserves::BANK_INVOICE_TYPE:
                    
                $reserveBank = $reserveInstance->getReservesBank();   
                if ($reserveBank) {
                    $reqv = $reserveBank->getCheckByReserveId($reserveInstance->getID());
                    if ($reqv) {
                        $reserveInstance->getReservesBank()->generateInvoice2($reqv);
                    }
                }

                break;
                
                case DocGenReserves::ACT_COMPLETED_FRL_TYPE:
                    $doc->generateActCompletedFrl();
                break;

                case DocGenReserves::ACT_SERVICE_EMP_TYPE:
                    $doc->generateActServiceEmp();
                break;

                case DocGenReserves::AGENT_REPORT_TYPE:
                    $doc->generateAgentReport();
                break;

                case DocGenReserves::RESERVE_OFFER_CONTRACT_TYPE:
                //case DocGenReserves::RESERVE_OFFER_AGREEMENT_TYPE:
                    $doc->generateOffers();
                    break;

                case DocGenReserves::RESERVE_SPECIFICATION_TYPE:
                    $doc->generateSpecification();
                    break;
                
                case DocGenReserves::LETTER_FRL_TYPE:
                    $doc->generateInformLetterFRL();
                break;

                case DocGenReserves::ARBITRAGE_REPORT_TYPE:
                    $doc->generateArbitrageReport();
                break;
            }
        }
        
        
        return true;
    }
    
    
    /**
     * Возвращает общую сумму резерва за указанную дату
     * 
     * @return boolean
     */
    public function getSummary()
    {
        $filterData = $this->getFilterData();
        
        if (!isset($filterData['date_reserve']) || !$filterData['date_reserve']) {
            return false;
        }        
        
        return tservices_helper::cost_format($this->getReservesListPrice(), false);
    }
    
    
}