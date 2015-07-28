<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_order_history.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reserves/ReservesSmail.php");

/**
 * Class ReservesTServiceModel
 * Модель резерва оплаты для заказов ТУ
 */

class ReservesTServiceOrderModel extends ReservesModel
{
    const TYPE  = 10;
    const NUM_FORMAT = "БС#%07d";
    const BILL_COMM = "За заказ БС#%07d";
    
    protected $TABLE_TSERVICE_ORDER = 'tservices_orders';
    static public $_TABLE_SRC = 'tservices_orders';
    static public $_TABLE_RESERVES_FILES = 'file_reserves_order';


    protected $reserves_bank_options = array(
        'table_files' => 'file_tservices_order',
        'bill_num_format' => self::NUM_FORMAT
    );
    
    
    const ReserveOrderStatus_New         = NULL;
    const ReserveOrderStatus_Negotiation = 1;
    const ReserveOrderStatus_Cancel      = 2;
    const ReserveOrderStatus_Reserve     = 3;
    const ReserveOrderStatus_InWork      = 4;
    const ReserveOrderStatus_Arbitrage   = 5;
    const ReserveOrderStatus_Pay         = 6;
    const ReserveOrderStatus_Closed      = 7;
    const ReserveOrderStatus_Done        = 8;
    
   
    static $_reserve_order_status_txt = array(
        self::ReserveOrderStatus_New            => 'новый',
        self::ReserveOrderStatus_Negotiation    => 'согласование',
        self::ReserveOrderStatus_Cancel         => 'отменен',
        self::ReserveOrderStatus_Reserve        => 'pезервирование',
        self::ReserveOrderStatus_InWork         => 'в работе',
        self::ReserveOrderStatus_Done           => 'выполнен',
        self::ReserveOrderStatus_Arbitrage      => 'арбитраж',
        self::ReserveOrderStatus_Pay            => 'выплата',
        self::ReserveOrderStatus_Closed         => 'закрыт'
    );




    protected $orderHistory = null;

    
    protected $reserveMail = null;



    
    public function getReserveMail()
    {
        if (!$this->reserveMail) {
           $this->reserveMail = new ReservesSmail(); 
        }
        
        return $this->reserveMail;
    }

    


    public function getFiles($order_id = null) 
    {
        if(!$order_id) {
            $order_id = $this->getSrcId();
        }
        
        $sql = "
            SELECT 
                f.path || f.fname AS url,
                f.*
            FROM ".self::$_TABLE_RESERVES_FILES." AS f
            WHERE f.src_id = ?i
            ORDER BY id DESC
        ";

        $files = $this->db()->rows($sql, $order_id);
        return $files;
    }    
    

    public function getOrderHistory()
    {
        if (!$this->orderHistory) {
            $this->orderHistory = new tservices_order_history(
                    $this->reserve_data['src_id']);
        }
        
        return $this->orderHistory;
    }


    
    
    /**
     * Переопределяем вызов формирования счета
     * для инициализации событий и записи их в историю
     * 
     * @param type $options
     * @return type
     */
    public function getReservesBank($options = array()) 
    {
        $this->getOrderHistory();
        return parent::getReservesBank($options);
    }

    
    
    /**
     * Обрабатываем действия после согласия на БС
     */
    public function afterNewReserve()
    {
        $order = $this->getSrcObject()->getOrderData();
        
        //Генерируем документы
        require_once(ABS_PATH . '/classes/DocGen/DocGenReserves.php');
        $doc = new DocGenReserves($order);
        //Ставим в очередь на генерацию
        //договора и соглашения
        $doc->generateOffers();
    }



    /**
     * Переопределяем события перед сменой статуса
     * 
     * @param type $new_status
     * @return boolean
     */
    public function beforeChangeStatus($new_status) 
    {
        $data = parent::beforeChangeStatus($new_status);
        if($data === null) return $data;
        
        switch($new_status)
        {
            case self::STATUS_RESERVE:
            case self::STATUS_ERR:
                
                if ($this->isStatusCancel()) {
                    if (!$this->isReserveByService()) {
                        if ($new_status == self::STATUS_ERR) {
                            $this->db()->update($this->TABLE_TSERVICE_ORDER, array(
                                'status' => TServiceOrderModel::STATUS_NEW
                            ), 'id = ?i', $this->getSrcId());
                        }
                    } else {
                        $data = false;
                    }
                }
                
                break;
        }
        
        return $data;
    }

    



    /**
     * Переопределяем события после изменения статуса
     * резерва оплаты для заказов ТУ
     * 
     * @param type $new_status
     */
    public function afterChangeStatus($new_status)
    {
        $success = false;
        $time = time();
        switch($new_status)
        {
            case self::STATUS_CANCEL:
                
                //Отмена заказа заказчиком то отменяем заказ если была попытка зарезервировать
                if ($_SESSION['uid'] == $this->reserve_data['emp_id']) {
                    require_once(ABS_PATH . "/classes/billing.php");
                    $billing = new billing($this->reserve_data['emp_id']);
                    $billing->cancelReserveByOrder('reserves', $this->reserve_data['id']);
                }
                
                $success = true;
                
                break;
            
            case self::STATUS_ERR:
                
                $this->getOrderHistory()->reservePriceErr();
                
                break;
            
            
            case self::STATUS_RESERVE:
                
                //После успешного резервирования средств 
                //переводим заказ в статус начала работы
                $src_id = @$this->reserve_data['src_id'];
                $success = $this->db()->update($this->TABLE_TSERVICE_ORDER, array(
                    'status' => TServiceOrderModel::STATUS_ACCEPT,
                    'accept_date' => date('Y-m-d H:i:s', $time)
                ), 'id = ?i', $src_id);
                
                
                if ($success) {
                     $orderModel = TServiceOrderModel::model();
                     $order = $orderModel->getShortCard($src_id);
                   
                     if ($order) {
                         
                        //@todo: можно передать просто $this ?
                        $reserveInstance = ReservesModelFactory::getInstance(ReservesModelFactory::TYPE_TSERVICE_ORDER);
                        if($reserveInstance) {
                            $reserveInstance->setSrcObject($orderModel);
                            $reserveInstance->setReserveData($this->reserve_data);
                            $order['reserve'] = $reserveInstance;
                        }
                        
                         $this->getOrderHistory()->reservePriceSuccess($this->reserve_data['price']);
                         
                         $order['reserve_data'] = $this->reserve_data;
                         $reservesSmail = new ReservesSmail();
                         $reservesSmail->attributes(array('order' => $order));
                         $reservesSmail->onReserveOrder();
                         
                         //Генерируем документы
                         require_once(ABS_PATH . '/classes/DocGen/DocGenReserves.php');
                         $doc = new DocGenReserves($order);
                         $doc->generateSpecification();
                    }
                }
                
                break;

            
            case self::STATUS_PAYED:
            case self::STATUS_ARBITRAGE:
                
                //@todo: генерируем документ когда резерв закрыт после всех выплат
                $src_id = @$this->reserve_data['src_id'];
                $orderModel = TServiceOrderModel::model();
                $order = $orderModel->getShortCard($src_id);
                
                if ($order) {
                    $this->getOrderHistory();
                    require_once(ABS_PATH . '/classes/DocGen/DocGenReserves.php');
                    $order['reserve_data'] = $this->reserve_data;
                    $order['reserve'] = $this;
                    $doc = new DocGenReserves($order);
                    $doc->generateActServiceEmp();
                    $doc->generateAgentReport();                    
                }

            
            default:
                $success = true;
        }
        
        return $success;
    }    
    
    
    /**
     * Переопределяем обработчик событий после
     * выплаты исполнителю
     * 
     * @param type $new_status
     * @return type
     */
    public function afterChangePayStatus($new_status)
    {
        $success = parent::afterChangePayStatus($new_status);
        if(!$success) return $success;
        
        switch($new_status)
        {
            case self::SUBSTATUS_NEW:
                if (!$this->isArbitrage()) {
                    $this->getOrderHistory()->reserveDone($this->getPriceWithOutNDFL());
                }
                break;
                
            case self::SUBSTATUS_INPROGRESS:
                $this->getOrderHistory()->reservePayoutReq($this->getPayoutTypeText(true));
                break;
            
            case self::SUBSTATUS_PAYED:
                $this->getOrderHistory()->reservePayout(
                        $this->getPayoutSum(), 
                        $this->getPayoutNDFL(),
                        $this->getPayoutTypeText(true));
                
                //Если после успешной выплаты сделка помечена 
                //как подозрительная то уведомляем
                if ($this->isFrod()) {
                    $this->getReserveMail()->frodPosible($this->getSrcObject());
                }
                
                break;
            
            case self::SUBSTATUS_ERR:
                $this->getOrderHistory()->reservePayoutErr();
                break;
        }
        
        return $success;
    }
    
    
    /**
     * Переопределяем обработчик событий после
     * возврата средств заказчику
     * 
     * @param type $new_status
     * @return type
     */
    public function afterChangeBackStatus($new_status)
    {
        $success = parent::afterChangeBackStatus($new_status);
        if(!$success) return $success;
        
        switch($new_status)
        {
            case self::SUBSTATUS_INPROGRESS:
                $this->getOrderHistory()->reservePaybackReq();
                break;
            
            case self::SUBSTATUS_PAYED:
                $this->getOrderHistory()->reservePayback($this->getPayback());
                break;
            
            case self::SUBSTATUS_ERR:
                $this->getOrderHistory()->reservePaybackErr();
                break;
        }
        
        return $success;
    }
    
    
    /*
    public function beforeChangePayStatus($new_status) 
    {
        $data = array();
        
        switch ($new_status) 
        {
            case self::SUBSTATUS_PAYED:
                break;
            case self::SUBSTATUS_NEW:
                break;
        }
        
        return $data;
    }*/
    
    
    public function getReserveNum()
    {
        return sprintf(static::NUM_FORMAT, @$this->reserve_data['src_id']);
    }
    

    public function getTypeUrl()
    {
        $src_id = @$this->reserve_data['src_id'];
        if(!$src_id) return false;
        return tservices_helper::getOrderCardUrl($src_id);
    }
    
    
    

    public function getReserveOrderStatus()
    {
        $status = self::ReserveOrderStatus_New;
        
        if( @$this->reserve_data['src_status'] >= TServiceOrderModel::STATUS_NEW) {
            if ($this->isReserveData()) {
                if ($this->isStatusNew() || $this->isStatusError()) {
                    $status = self::ReserveOrderStatus_Reserve;
                } elseif ($this->isClosed()) {
                    $status = self::ReserveOrderStatus_Closed;
                } elseif ($this->isArbitrageOpen()) {
                    $status = self::ReserveOrderStatus_Arbitrage;
                } elseif(!($this->isStatusPayNone() || $this->isStatusPayPayed()) || 
                         !($this->isStatusBackNone() || $this->isStatusBackPayed())) {
                    $status = self::ReserveOrderStatus_Pay;
                } elseif(@$this->reserve_data['src_status'] == TServiceOrderModel::STATUS_FRLCLOSE) {
                    $status = self::ReserveOrderStatus_Done;
                } else {
                    $status = self::ReserveOrderStatus_InWork;
                }                
            }else {
                $status = self::ReserveOrderStatus_Negotiation;
            }
        } else {
            $status = self::ReserveOrderStatus_Cancel;
        }
        
        return $status;
    }  
    
    /**
     * Сообщения в заказе до резервирования
     * @param type $order_id ИД заказа
     * @param type $date Дата
     */
    public function getReserveMessages($order_id, $date)
    {
        $result = "";
        $msgModel = new TServiceMsgModel();
        $messages = $msgModel->getMessagesBeforeDate($order_id, $date);
        
        $template_user = "%s %s [%s]";
        $template = "%s - %s\n%s\n\n";
        
        foreach ($messages as $message) {
            $user = sprintf($template_user, $message['uname'], $message['usurname'], $message['login']);
            
            $result .= sprintf($template, 
                    trim($user), 
                    dateFormat("d.m.Y, H:i", $message['sent']),
                    strip_tags(str_replace('<br />', "\n", $message['message']))
                );
        }
        
        return $result;
    }
    
    
    
}
