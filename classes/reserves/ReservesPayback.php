<?php

require_once(__DIR__ . '/BaseModel.php');
require_once(__DIR__ . '/Exception/ReservesPaybackException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/YandexMoney3/YandexMoney3.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');


use YandexMoney3\Request\ReturnPaymentRequest;
use YandexMoney3\YandexMoney3;


class ReservesPayback extends BaseModel
{
    
    protected static $instance;
    
    const PAYBACK_CAUSE     = '¬озврат средств по %s решением арбитража.';
    
            
    const STATUS_NEW        = -1;
    const STATUS_SUCCESS    = 0;
    const STATUS_INPROGRESS = 1;
    const STATUS_FAIL       = 3;
    
    const CURRENCY_TEST     = 10643;
    const CURRENCY          = 643;
    
    private $currency;    
    
    private $isTest         = false;
    
    private $apiFacade      = null;

    private $TABLE          = 'reserves_payback';
    
    
    /**
     * ѕриведение статуса сервиса к статусу системы
     * 
     * @var array 
     */
    private $to_reserve_substatus = array(
        self::STATUS_SUCCESS    => ReservesModel::SUBSTATUS_PAYED,
        self::STATUS_INPROGRESS => ReservesModel::SUBSTATUS_INPROGRESS,
        self::STATUS_FAIL       => ReservesModel::SUBSTATUS_ERR
    );


    public function __construct() 
    {
        $this->isTest = !is_release() || is_local();
    }

    
    public function getSubStatus($status)
    {
        if(!isset($this->to_reserve_substatus[$status])) return false;
        return $this->to_reserve_substatus[$status];
    }
    

    public function getCurrency()
    {
        return $this->currency = ($this->isTest)?
                self::CURRENCY_TEST:
                self::CURRENCY;
    }


    public function setTest($value = true)
    {
        $this->isTest = $value;
    }


    
    public function getApiFacade()
    {
        if(!$this->apiFacade) 
        {
            $this->apiFacade = YandexMoney3::getMwsApiFacade(array(
                'crypt' => array(
                    'encrypt_cert_path' => __DIR__ . '/data_mws/certnew_vaan.cer',
                    'private_key_path'  => __DIR__ . '/data_mws/private_mws.key',
                    'passphrase'        => 'swirls53.quarks'
                ),
                'uri_test' => 'https://penelope-demo.yamoney.ru:8083',
                'uri_main' => 'https://penelope.yamoney.ru',
                'is_test'  => $this->isTest
            )); 
        }
        
        return $this->apiFacade;
    }

    
    
    
    /**
     * ¬ызвать запрос к сервису на возврат средств
     * 
     * @param int $reserve_id
     * @return boolean
     * @throws ReservesPaybackException
     */
    public function doPayback($reserve_id)
    {
        $paybackData = $this->db()->row("
            SELECT *
            FROM {$this->TABLE}
            WHERE reserve_id = ?i
        ",$reserve_id);
        
        //≈сли запроса не существует    
        if(!$paybackData) 
            throw new ReservesPaybackException(ReservesPaybackException::PAYBACK_NOTFOUND);
        
        //≈сли запрос уже успешно обработан
        //(урал так как если вдруг неполучитс€ сменить статус резерва возврата а возврат пройдет то можно повторить)
        //if($paybackData['status'] == self::STATUS_SUCCESS)
        //    throw new ReservesPaybackException(ReservesPaybackException::ALREADY_PAYBACK_MSG);
        
        $is_timeout = $this->isTimeout($paybackData['cnt'], $paybackData['last']);
        //“аймаут еще не вышел нужно поставить в очередь
        if(!$is_timeout) return false;
        
        //ѕревышен лимит прерываем цикл дл€ этого запроса
        if($is_timeout === -1) 
            throw new ReservesPaybackException(ReservesPaybackException::REQUEST_LIMIT);
        
        //≈сли не существует самого резерва
        $reserveInstance = ReservesModelFactory::getInstanceById($paybackData['reserve_id']);
        if(!$reserveInstance)
            throw new ReservesPaybackException(ReservesPaybackException::PAYBACK_NOTFOUND);

        
        $data['cnt'] = $paybackData['cnt'] + 1;
        $data['last'] = 'NOW()';
        
        try
        {
            //√отовим запрос
            $returnPaymentRequest = new ReturnPaymentRequest();
            $returnPaymentRequest->setShopId(yandex_kassa::SHOPID_SBR);
            $returnPaymentRequest->setClientOrderId($paybackData['id']);
            $returnPaymentRequest->setInvoiceId($paybackData['invoice_id']);
            $returnPaymentRequest->setCurrency($this->getCurrency());
            $returnPaymentRequest->setCause(sprintf(self::PAYBACK_CAUSE, $reserveInstance->getNUM()));
            $returnPaymentRequest->setAmount(number_format($paybackData['price'], 2, '.', ''));
            //ƒелаем запрос к API сервиса
            $result = $this->getApiFacade()->returnPayment($returnPaymentRequest);
            
            //¬ыставл€ем статус и код ошибки
            $data['status'] = $result->getStatus();
            $data['error'] = (!$result->isSuccess())?$result->getError():0;
        }
        catch(Exception $e)
        {
            //¬ случае аварии при транспорте API 
            //пишем в лог и просим поставить задачу в очередь
            $data['status'] = self::STATUS_FAIL;
            $data['error'] = 10000 + intval($e->getCode());
            $this->db()->update($this->TABLE, $data,'id = ?i', $paybackData['id']);
            throw new ReservesPaybackException($e->getMessage(),true);
        }
        
        $this->db()->update($this->TABLE, $data, 'id = ?i', $paybackData['id']);
        
        $new_status = $this->getSubStatus($result->getStatus());
        //Ќет смысла мен€ть статус так как система уже в таком же статусе
        //например долгий процесс ожидани€
        if($reserveInstance->getStatusBack() != $new_status)
        {
            //Ќе удалост сменить статус
            if(!$reserveInstance->changeBackStatus($new_status))
                throw new ReservesPaybackException(ReservesPaybackException::CANT_CHANGE_SUBSTATUS, true); 
        }
        
        //ќшибки при которых ставить в очередь нет смысла
        if(!$result->isSuccess() && in_array($result->getError(), array(403, 404, 405, 412, 413, 414, 417)))
            throw new ReservesPaybackException(ReservesPaybackException::API_CRITICAL_FAIL, $result->getError()); 
        
        
        //≈сли статус еще не оплачен то нужно 
        //повторить и поставить задачу в очередь
        return $reserveInstance->isStatusBackPayed();
    }






    /**
     * «апрос на возврат средств
     * или обновить по возможности существующий запрос
     * “ак же ставит в очередь
     * 
     * @param type $reserve_id
     * @param type $invoice_id
     * @param type $sum
     * @throws ReservesPaybackException
     */
    public function requestPayback($reserve_id, $invoice_id, $sum)
    {
        $paybackData = $this->db()->row("
            SELECT *
            FROM {$this->TABLE}
            WHERE reserve_id = ?i
        ",$reserve_id);
        
        $id = null;
            
        if($paybackData)
        {
            if($paybackData['status'] == self::STATUS_SUCCESS)
                throw new ReservesPaybackException(ReservesPaybackException::ALREADY_PAYBACK_MSG, $paybackData['id']);
            
            if($paybackData['status'] == self::STATUS_INPROGRESS)
                throw new ReservesPaybackException(ReservesPaybackException::PAYBACK_INPROGRESS,  $paybackData['id']);

            $is_ok = $this->db()->update($this->TABLE, array(
                'invoice_id' => $invoice_id,
                'price' => $sum
            ), 'id = ?i', $paybackData['id']);
            
            if($is_ok) $id = $paybackData['id'];
        }
        else
        {
            $id = $this->db()->insert($this->TABLE, array(
                'reserve_id' => $reserve_id,
                'invoice_id' => $invoice_id,
                'price' => $sum
            ),'id');
        }
            
        if(!$id) throw new ReservesPaybackException(ReservesPaybackException::INSERT_FAIL_MSG);
        
        //запускаем в очередь на обработку
        $this->db()->query("SELECT pgq.insert_event('reserves', 'payback', ?)", 
                http_build_query(array('reserve_id' => $reserve_id)));
    }

    

    /**
     * –екомендуетс€ следующий режим повтора: первый повтор через 1 минуту, 
     * следующие три с промежутком в 5 минут, далее не чаще чем раз в 30 минут.
     */
    private function isTimeout($cnt, $timeString)
    {
        if($cnt == 0) return true;
        if($cnt >= 999) return -1;
        
        $timeout = 1800;
        if($cnt == 1) $timeout = 60;
        elseif($cnt >= 2 && $cnt <= 4) $timeout = 300;
        
        $time = strtotime($timeString) + $timeout;
        return (time() < $time)?false:true;
    }

    

    /**
     * ѕолучить запросы на возврат дл€ указанного резерва
     * 
     * @param type $reserve_id
     * @param type $status
     * @return type
     */
    public function getPayback($reserve_id, $status = null)
    {
        $where_sql = ($status)?$this->db()->parse(' AND status = ?i',$status):'';
        
        return $this->db()->rows("
            SELECT *
            FROM {$this->TABLE}
            WHERE reserve_id = ?i 
            {$where_sql}
        ",$reserve_id);
    }




    /**
     * —оздаем синглтон
     * @return object
     */
    public static function getInstance() 
    {

        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }
    
}