<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/BaseModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesTaxes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesBank.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesArbitrage.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesServiceReqvModel.php');


/**
 * Class ReservesModel
 * Модель резерва оплаты
 * 
 * Базовый класс. 
 * Использование на прямую только по необходимости.
 * Обычно необходимо наследовать дочерний класс для каждой сущности.
 * 
 */

class ReservesModel extends BaseModel
{
    //Код операции в ЛС
    const OPCODE_RESERVE = 136;
    
    
    //Отмена резерва
    const STATUS_CANCEL     = -1;
    
    //Заказчику предлагается 
    //зарезервировать сумму
    const STATUS_NEW        = 0;
    
    //Заказчик зарезервировал сумму
    const STATUS_RESERVE    = 10; 
    
    //Суммы выплачена исполнителю 
    //в полном обьеме
    const STATUS_PAYED      = 20;
    
    //БС завершена
    //по решению арбитража
    const STATUS_ARBITRAGE  = 30;
    
    //Ошибка при выплате.
    //Исполнителю нужно проверить реквизиты 
    //и повторить операцию.
    const STATUS_ERR        = 40;
    
    //В ожидании выплаты
    //сервис в процессе выплаты 
    //и поэтому позже нужно повторно запросить состояние
    const STATUS_INPROGRESS = 50;
    
    
    //В какое состояние статус может переходить
    protected $STATUS_NEXT = array(
        self::STATUS_NEW => array(
            self::STATUS_RESERVE,
            self::STATUS_ERR,
            self::STATUS_CANCEL
        ),
        self::STATUS_RESERVE => array(
            self::STATUS_PAYED,
            self::STATUS_ARBITRAGE,
            self::STATUS_ERR,
            self::STATUS_INPROGRESS
        ),
        self::STATUS_ERR => array(
            self::STATUS_PAYED,
            self::STATUS_ARBITRAGE,
            self::STATUS_RESERVE,
            self::STATUS_CANCEL,
            self::STATUS_ERR
        ),
        self::STATUS_INPROGRESS => array(
            self::STATUS_ERR,
            self::STATUS_PAYED,
            self::STATUS_ARBITRAGE
        ),
        self::STATUS_CANCEL => array(
            self::STATUS_RESERVE,
            self::STATUS_ERR            
        )
    );
    
    
    
    
    //Выплата не назначена
    const SUBSTATUS_NONE     = 0;
    
    //Выплата назначена
    const SUBSTATUS_NEW     = 1;
    
    //Ожидание выплаты
    const SUBSTATUS_INPROGRESS    = 10;
    
    //Выплата прошла успешно
    const SUBSTATUS_PAYED   = 20;
    
    //Попытка завершилась ошибкой
    const SUBSTATUS_ERR     = 30;
    
    
    //В какое состояние подстатус может переходить
    protected $SUBSTATUS_NEXT = array(
        self::SUBSTATUS_NONE => array(
            self::SUBSTATUS_NEW
        ),
        self::SUBSTATUS_NEW => array(
            self::SUBSTATUS_INPROGRESS,
            self::SUBSTATUS_PAYED,
            self::SUBSTATUS_ERR
        ),
        self::SUBSTATUS_INPROGRESS => array(
            self::SUBSTATUS_PAYED,
            self::SUBSTATUS_ERR
        ),
        self::SUBSTATUS_ERR => array(
            self::SUBSTATUS_PAYED,
            self::SUBSTATUS_INPROGRESS,
            self::SUBSTATUS_ERR
        )
    );
    
    
    const TYPE = 0; //Тип резерва    
    const NUM_FORMAT = "%d";
    const BILL_COMM = "";
    
    const NDFL = 0.13;
    
    
    protected $TABLE                = 'reserves';
    protected $TABLE_SERVICE_REQV   = 'reserves_service_reqv';
    static public $_TABLE           = 'reserves';
    static public $_TABLE_META      = 'reserves_meta';
    static public $_TABLE_SRC;
    static public $_TABLE_EMPLOYER  = 'employer';


    protected $reserve_data = array();
    

    const RESERVES_BANK_CLASS = 'ReservesBank';
    protected $reserves_bank = null;
    protected $reserves_bank_options = array();


    protected $reserves_taxes = null;


    /**
     * Обьект для которого осуществляется резерв средств
     * (например заказ)
     * @var type 
     */
    protected $src_object = null;


    /**
     * Добалнительные данные которые можно обновить при смене статуса
     * 
     * @var type 
     */
    protected $updated_data = array();
    
    
    
    /**
     * Сумма резерва после которой следим за сделкой, руб.
     */
    const FROD_MONEY_LIMIT = 15000;
    
    /**
     * Минимальное время между резервом и выплатой
     */
    const FROD_TIME_LIMIT = 86400;


    
    
    public function setUpdatedData($key, $value)
    {
        $this->updated_data[$key] = $value;
    }


    /**
     * Получить ссылку на обьект резерва
     */
    public function getSrcObject()
    {
        return $this->src_object;
    }    
    
    /**
     * Установить ссылку на обьект резерва
     * 
     * @param type $obj
     */
    public function setSrcObject($obj)
    {
        $this->src_object = $obj;
    }


    /**
     * Вернуть обьект коммисий резерва
     * 
     * @return type
     */
    public function getReservesTaxes()
    {
        if(!$this->reserves_taxes) 
            $this->reserves_taxes = new ReservesTaxes();
        
        return $this->reserves_taxes;
    }

    

    /**
     * Вернуть комментарий о списании средств за резерв
     * 
     * @param type $src_id
     * @return type
     */
    public function getBillComment($src_id)
    {
        return sprintf(static::BILL_COMM, $src_id);
    }


    /**
     * Установить текущий резерв с которым работает объект
     * 
     * @param type $data
     * @return \ReservesModel
     */
    public function setReserveData($data)
    {
        $this->reserve_data = $data;
        return $this;
    }

    
    /**
     * Установить значение по ключу текущего резерва 
     * с которым работает объект
     * 
     * @param type $key
     * @param type $value
     * @return \ReservesModel
     */
    public function setReserveDataByKey($key, $value)
    {
        $this->reserve_data[$key] = $value;
        return $this;
    }
    

    /**
     * Получить текущий резерв
     * 
     * @return array
     */
    public function getReserveData()
    {
        return $this->reserve_data;
    }

    /**
     * Получить значение по ключу
     * 
     * @param type $key
     * @return type
     */
    public function getReserveDataByKey($key)
    {
        return @$this->reserve_data[$key];
    }

    
    /**
     * Проверяем возможность перехода в следующий статус
     * 
     * @param type $new_status
     * @return boolean
     */
    public function allowChangeStatus($new_status)
    {
        if (!$this->isReserveData()) { 
            return false;
        }
        
        $old_status = $this->reserve_data['status'];
        $next = $this->STATUS_NEXT;
        
        return isset($next[$old_status]) && 
               in_array($new_status, $next[$old_status]);
    }

    
    /**
     * Переход в следующий возможный статус
     * 
     * @param int $id - ID резерва
     * @param int $new_status - новый статус
     * @param int $old_status - текущий статус
     * @return boolean - Результат операции
     */
    public function changeStatus($new_status)
    {
        if (!$this->allowChangeStatus($new_status)) {
            return false;
        }

        $data = array('status' => $new_status);
        
        //Обработка событий до обновления статуса
        $bdata = $this->beforeChangeStatus($new_status);
        //@todo: это плохо нужно переходить на бросание исключений (throw new Exception)
        if ($bdata === false)  {
            return false;
        }
        
        $data = array_merge($data, $bdata);
        
        $ok = $this->db()->update($this->TABLE, $data, 'id = ?i', $this->getID());

        if ($ok) {
            $this->reserve_data = array_merge($this->reserve_data, $data);
            $ok = $this->afterChangeStatus($new_status);
        }

        return $ok;
    }

    
    /**
     * Обработка событий после успешного обновления статуса
     * 
     * @param type $new_status
     */
    public function afterChangeStatus($new_status)
    {
        //TODO
        return true;
    }

    

    /**
     * Обработка событий до обновления статуса.
     * Допустима модификация только для общих случаев!
     * Для специвики требуется переопределение в дочернем классе.
     * 
     * @param int $new_status - Переход в этот статус
     * @return array - Обновляем данные
     */
    public function beforeChangeStatus($new_status)
    {
        $time = date('Y-m-d H:i:s', time());
        $data = $this->updated_data;
        
        switch($new_status)
        {
            case self::STATUS_RESERVE:
                
                $data['date_reserve'] = $time;
                //при успешном резервировании средств сохраняем номер транзакции в ЯД
                //и номер операции в account_operation
                if(isset($this->reserve_data['invoice_id']))
                {
                    $data['invoice_id'] = $this->reserve_data['invoice_id'];
                    $this->getReservesServiceReqvs()->captureEmpReqv($this->getEmpId());
                }
                
                if(isset($this->reserve_data['acc_op_id']))
                {
                    $data['acc_op_id'] = $this->reserve_data['acc_op_id'];
                }
                
                break;
            
            case self::STATUS_PAYED:
            case self::STATUS_ARBITRAGE:
                $data['date_complete'] = $time;
                break;
        }
        
        return $data;
    }
    
    /**
     * Переход в следующий возможный статус выплаты исполнителю
     * 
     * @param int $new_status - новый статус
     * @return boolean - Результат операции
     */
    public function changePayStatus($new_status)
    {
        if(empty($this->reserve_data)) return false;
        
        $old_status = (int)$this->reserve_data['status_pay'];
        $id = $this->reserve_data['id'];
        
        //Проверяем возможность перехода в следующий статус
        $next = $this->SUBSTATUS_NEXT;
        if(!isset($next[$old_status]) || 
           !in_array($new_status, $next[$old_status])) return FALSE;
        
        $data = array(
            'status_pay' => $new_status,
            'last_status_pay' => date('Y-m-d H:i:s')
        );
        
        //Обработка событий до обновления статуса
        $bdata = $this->beforeChangePayStatus($new_status);
        $data = array_merge($data, $bdata);
        
        $ok = $this->db()->update($this->TABLE, $data, 'id = ?i', $id);
        
        if ($ok)  {
            $this->reserve_data = array_merge($this->reserve_data, $data);
            $ok = $this->afterChangePayStatus($new_status);
        }
        
        return $ok;
    }
    
    /**
     * Обработка событий после успешного обновления статуса выплаты
     * 
     * @param type $new_status
     */
    public function afterChangePayStatus($new_status)
    {
        $success = true;
        
        switch($new_status)
        {
            case self::SUBSTATUS_PAYED:
                
                //Если выплата успешна и нет арбитража 
                //то закрываем резерв
                if(!$this->isArbitrage()) 
                {
                    $success = $this->changeStatus(self::STATUS_PAYED);
                }
                //Если часть средств были уже возвращены заказчику
                //или даже не назначались арбитражем, то
                //закрываем резерв
                elseif($this->isStatusBackPayed() || 
                       $this->isStatusBackNone())
                {
                    $success = $this->changeStatus(self::STATUS_ARBITRAGE);
                }
                
                break;
        }        

        return $success;
    }

    

    /**
     * Обработка событий до обновления статуса выплаты.
     * 
     * @param int $new_status - Переход в этот статус
     */
    public function beforeChangePayStatus($new_status)
    {
        $data = $this->updated_data;
        
        switch ($new_status) {
            case self::SUBSTATUS_PAYED:
                if ($this->frodCheck()) {
                    $data['is_frod'] = true;
                }
                break;
        }
        
        
        return $data;
    }
    
    
    /**
     * Переход в следующий возможный статус возврата заказчику
     * 
     * @param int $new_status - новый статус
     * @return boolean - Результат операции
     */
    public function changeBackStatus($new_status)
    {
        if(empty($this->reserve_data)) return false;
        
        $old_status = (int)$this->reserve_data['status_back'];
        $id = $this->reserve_data['id'];
        
        //Проверяем возможность перехода в следующий статус
        $next = $this->SUBSTATUS_NEXT;
        if(!isset($next[$old_status]) || 
           !in_array($new_status, $next[$old_status])) return FALSE;
        
        $data = array(
            'status_back' => $new_status,
            'last_status_back' => date('Y-m-d H:i:s')
        );
        
        //Обработка событий до обновления статуса
        $bdata = $this->beforeChangeBackStatus($new_status);
        $data = array_merge($data, $bdata);
        
        $ok = $this->db()->update($this->TABLE, $data, 'id = ?i', $id);
        
        if ($ok)  {
            $this->reserve_data = array_merge($this->reserve_data, $data);
            $ok = $this->afterChangeBackStatus($new_status);
        }

        return $ok;
    }
   
    
    
    /**
     * Обработка событий после успешного обновления статуса возврата
     * 
     * @param type $new_status
     */
    public function afterChangeBackStatus($new_status)
    {
        $success = true;
        
        switch($new_status)
        {
            case self::SUBSTATUS_PAYED:
                
                //Если возврат средств успешно завершен
                //и уже была выплата или без нее (решение арбитража)
                //то закрываем резерв арбитражем
                if($this->isStatusPayPayed() || 
                   $this->isStatusPayNone())
                {
                    $success = $this->changeStatus(self::STATUS_ARBITRAGE);
                }
                
                break;
        }

        return $success;
    }

    

    /**
     * Обработка событий до обновления статуса возврата.
     * 
     * @param int $new_status - Переход в этот статус
     */
    public function beforeChangeBackStatus($new_status)
    {
        $data = $this->updated_data;
        return $data;
    }

    
    
    /**
     * Создание резерва
     * Вызывается из дочернего класса где указан
     * тип резерва $TYPE
     * 
     * @param type $src_id - ID сущности для резерва в зависимости от $TYPE типа
     * @param type $price - Стоимость сущности
     * @param type $emp_id - ID заказчика
     * @param type $frl_id - ID фрилансера
     * @return boolean | array - вернуть данные для добавления
     */
    public function newReserve($srcObject = null)
    {
        if ($srcObject) {
            $this->setSrcObject($srcObject);
        }
        
        $srcObject = $this->getSrcObject();
        if (!$srcObject) {
            return false;
        }
        
        $src_id = $srcObject->getId();
        $price = $srcObject->getPrice();
        $emp_id = $srcObject->getEmpId();
        $frl_id = $srcObject->getFrlId();
        
        $tax = $this->getReservesTaxes()->getTax($price);
        $tax_price = floor($tax * $price);
        $reserve_price = $price + $tax_price;
        
        $data = array(
            'tax' => $tax,
            'tax_price' => $tax_price,
            'price' => $price,
            'reserve_price' => $reserve_price,
            'type' => static::TYPE,
            'src_id' => $src_id,
            'emp_id' => $emp_id,
            'frl_id' => $frl_id,
            'status' => static::STATUS_NEW,
            'date' => date('Y-m-d H:i:s', time())
        );
        
        $id = $this->db()->insert($this->TABLE, $data,'id');
        if(!$id) return false;
        
        $data['id'] = $id;
        $this->reserve_data = $data;
        
        $this->afterNewReserve();
        
        return $data;
    }
    
    
    /**
     * Событие после создания резерва
     * 
     * @param type $data
     */
    public function afterNewReserve()
    {
        //@todo: событие после создания резерва
    }

    

    /**
     * Уточнение запроса для таблицы сущности
     * переопределяется в наследнике
     * 
     * @return string
     */
    protected function _getSrcWhere()
    {
        return '';
    }

    
    protected function _getSrcSelect()
    {
        return '';
    }

    protected function _getSrcJoin()
    {
        return '';
    }    
    
    protected function _getSrcOrder()
    {
        return 'ORDER BY src.id DESC';
    }
    
    /**
     * Получить список обьектов резервов 
     * средств конкретной сущности
     * 
     * @return array of objects
     */
    public function getReservesList()
    {
        $src_table = static::$_TABLE_SRC;
        $_where = $this->_getSrcWhere();
        $_order = $this->_getSrcOrder();
        
        $sql = $this->db()->parse("
            SELECT DISTINCT src.id,
                r.*,
                ra.id AS arbitrage_id,
                ra.status AS arbitrage_status, 
                src.id AS src_id, /* Если вдруг небудет связи то id сущности заменит */
                {$this->_getSrcSelect()}
            FROM {$src_table} AS src
            LEFT JOIN {$this->TABLE} AS r ON r.src_id = src.id 
            LEFT JOIN " . ReservesArbitrage::$_TABLE . " AS ra ON ra.reserve_id = r.id
            {$this->_getSrcJoin()}
            {$_where}
            {$_order}
        ");
        
        $sql = $this->_limit($sql);
        $rows = $this->db()->rows($sql);
        
        $objs = array();
        if(count($rows)) {
            foreach($rows as $row){
                $reserveInstance = clone $this;
                $reserveInstance->setReserveData($row);
                $objs[] = $reserveInstance;
            }
        }
        
        return $objs;
    }

    
    /**
     * Кол-во сделок при выводе списка выше
     * нужно для пагинации
     * 
     * @return type
     */
    public function getReservesListCount()
    {
        $src_table = static::$_TABLE_SRC;
        $_where = $this->_getSrcWhere();      
        
        return $this->db()->cache(300)->val("
            SELECT COUNT(DISTINCT r.id)
            FROM {$src_table} AS src
            LEFT JOIN {$this->TABLE} AS r ON r.src_id = src.id 
            LEFT JOIN " . ReservesArbitrage::$_TABLE . " AS ra ON ra.reserve_id = r.id
            {$this->_getSrcJoin()}    
            {$_where}
        ");
    }
    
    
    /**
     * Общая сумма резерва при выводе списка
     * 
     * @return type
     */
    public function getReservesListPrice()
    {
        $src_table = static::$_TABLE_SRC;
        $_where = $this->_getSrcWhere();      
        
        return $this->db()->cache(300)->val("
            SELECT SUM(s.price)
            FROM (
                SELECT r.price
                FROM {$src_table} AS src
                LEFT JOIN {$this->TABLE} AS r ON r.src_id = src.id 
                LEFT JOIN " . ReservesArbitrage::$_TABLE . " AS ra ON ra.reserve_id = r.id
                {$this->_getSrcJoin()}    
                {$_where}
                GROUP BY r.id
            ) as s
        ");
    }

    
    /**
     * Получить зерезв по ID сущности
     * Вызывается из дочерней таблицы где указан тип $TYPE сущности
     * 
     * @param int $src_id - ID сущности
     * @return array - данные резерва
     */
    public function getReserve($src_id)
    {
        $this->reserve_data = $this->db()->row("
            {$this->getBaseSql()}
            WHERE 
                r.type = ?i AND
                r.src_id = ?i
            LIMIT 1    
        ",static::TYPE, $src_id);
            
        return $this->reserve_data;
    }
    
    
    
    /**
     * Получить резерв по его ID
     * 
     * @param int $id - ID резерва
     * @return array - данные резерва
     */
    public function getReserveById($id)
    {
        $this->reserve_data = $this->db()->row("
            {$this->getBaseSql()}
            WHERE 
                r.id = ?i
            LIMIT 1    
        ",$id);
        
        return $this->reserve_data;
    }
    
    
    
    
    /**
     * Получить логин заказчика по ID БС
     * 
     * @param type $id
     * @return type
     */
    public function getEmpByReserveIds($src_ids = array())
    {
        $src_ids = is_array($src_ids)?$src_ids:array($src_ids);
        
        $rows = $this->db()->rows("
            SELECT
                r.src_id,
                e.login,
                e.uid
            FROM ".static::$_TABLE_EMPLOYER." AS e
            INNER JOIN ".static::$_TABLE." AS r ON r.emp_id = e.uid
            WHERE
                r.type = ?i AND
                r.src_id IN(?l)
        ", static::TYPE, $src_ids);

        $result = array();
        
        if ($rows) {
            foreach($rows as $row) {
                $result[$row['src_id']] = $row;
            }
        }
        
        return $result;
    }

    



    /**
     * Получить список резервов с указанным статусом выплаты по сервису ЯД
     * 
     * @param type $status_pay
     * @return type
     */
    public function getReservesWithStatusPayByService($status_pay, $limit = 10)
    {
        require_once('ReservesPayout.php');
        
        return $this->db()->rows("
            {$this->getBaseSql("
                rpr.pay_type,
                e.login AS emp_login,
                e.email AS emp_email
            ")} 
            INNER JOIN " . ReservesPayout::$_TABLE_REQV . " AS rpr ON rpr.reserve_id = r.id
            INNER JOIN " . static::$_TABLE_EMPLOYER . " AS e ON e.uid = r.emp_id 
            WHERE 
                r.status = ?i 
                AND r.status_pay = ?i 
                AND rpr.pay_type <> ?
            LIMIT ?i
        ", static::STATUS_RESERVE, $status_pay, ReservesPayoutPopup::PAYMENT_TYPE_BANK, $limit);
    }

    



    protected function getBaseSql($select = '')
    {
        $select = (empty($select))?$select:", {$select}";
        
        $sql = "
            SELECT 
                r.*, 
                ra.id as arbitrage_id,
                ra.status as arbitrage_status,
                ra.message as arbitrage_message,
                ra.is_emp as arbitrage_is_emp,
                ra.price as arbitrage_price,
                ra.date_close as arbitrage_date_close,
                ra.allow_fb_frl as arbitrage_allow_fb_frl,
                ra.allow_fb_emp as arbitrage_allow_fb_emp
                {$select}
            FROM {$this->TABLE} AS r 
            LEFT JOIN " . ReservesArbitrage::$_TABLE . " AS ra ON ra.reserve_id = r.id            
        ";
        
        return $sql;
    }

    

    /**
     * Проверить существует ли резерв по ID сущности
     * и вернуть ID зерезва в случае успеха
     * 
     * @param int $src_id - ID сущности
     * @return int - ID зерезва
     */
    public function isExistReserve($src_id)
    {
        return $this->db()->val("
            SELECT id 
            FROM {$this->TABLE}
            WHERE 
                type = ?i AND
                src_id = ?i
            LIMIT 1    
        ",static::TYPE, $src_id);        
    }
    
    
    
    
    /**
     * Есть ли у пользователя хоть одна сделка с резервом средств
     * 
     * @param int $uid
     * @return int
     */
    public function hasReserveByUserId($uid)
    {   
        return $this->db()->val("
            SELECT id
            FROM {$this->TABLE}
            WHERE 
                type = ?i AND
                (frl_id = ?i OR emp_id = ?i)
            LIMIT 1    
       ",  static::TYPE, $uid, $uid);        
    }

    
    
    /**
     * Есть ли у заказчика резерв в котором:
     * - был выставлен счет на резерв или
     * - средства уже зарезервированы и выше 
     * - но не статус ошибка
     * 
     * @param int $emp_id
     * @return int
     */
    public function hasAfterReserveForEmpId($emp_id)
    {
        $table_bank = ReservesBank::$_TABLE;
        
        return $this->db()->val("
            SELECT r.id
            FROM {$this->TABLE} AS r
            LEFT JOIN {$table_bank} AS rb ON rb.reserve_id = r.id
            WHERE 
                r.type = ?i AND 
                r.emp_id = ?i AND 
                (rb.id IS NOT NULL OR r.status IN(?l)) AND
                r.status <> ?i
            LIMIT 1    
       ",  static::TYPE, $emp_id, array(
           self::STATUS_RESERVE,
           self::STATUS_INPROGRESS,
           self::STATUS_PAYED,
           self::STATUS_ARBITRAGE
       ),
           self::STATUS_ERR
       );
    }


    /**
     * Есть ли у фрилансера резерв в котором
     * выплата в процессе или уже выплачена
     * 
     * @param int $frl_id
     * @return int
     */
    public function hasReserveForFrlId($frl_id)
    {
        return $this->db()->val("
            SELECT id
            FROM {$this->TABLE}
            WHERE 
                type = ?i AND 
                frl_id = ?i AND 
                status_pay IN (?l)
            LIMIT 1    
       ",  static::TYPE, $frl_id, array(
            self::SUBSTATUS_INPROGRESS,
            self::SUBSTATUS_PAYED   
       ));
    } 

    
    
    /**
     * Можно редактировать реквизиты финансов?
     * 
     * @param type $uid
     * @param type $role
     * @return type
     */
    public function isAllowEditFinance($uid, $role)
    {
        $is_emp = is_bool($role)?$role:is_emp($role);
        $is_exist = (bool)(($is_emp)?
                $this->hasAfterReserveForEmpId($uid):
                $this->hasReserveForFrlId($uid));
        
        return !$is_exist;
    }



    /**
     * Новый резерв?
     * 
     * @return boolean
     */
    public function isStatusNew()
    {
        return (isset($this->reserve_data['status']))?
            $this->reserve_data['status'] == static::STATUS_NEW:
            false;
    }
    
    
    public function isStatusError()
    {
        return $this->reserve_data['status'] == self::STATUS_ERR; 
    }
    
    
    /**
     * Есть данные о текущем резерве?
     * 
     * @return boolean
     */
    public function isExistReserveData()
    {
        return !empty($this->reserve_data);
    }

    


    /**
     * Инициализировать и получить обьект для работы с резервом по безналу
     * 
     * @param type $options
     * @return type
     */
    public function getReservesBank($options = array()) 
    {
        if (!empty($this->reserve_data)) 
        {
            $this->reserves_bank_options['src_id'] = $this->reserve_data['src_id'];
        }

        $options = array_merge($this->reserves_bank_options, $options);
        $class = static::RESERVES_BANK_CLASS;
        if (!$this->reserves_bank) $this->reserves_bank = $class::model($options);

        if (!empty($this->reserve_data)) 
        {
            $this->reserves_bank->setData(array(
                'user_id' => $this->reserve_data['emp_id'],
                'reserve_id' => $this->reserve_data['id'],
                'price' => $this->reserve_data['price'],
                'reserve_price' => $this->reserve_data['reserve_price'],
                'tax_price' => $this->reserve_data['tax_price'],
                'date_offer' => $this->reserve_data['date']
            ));
        }

        return $this->reserves_bank;
    }
    

    
    public function getTypeUrl()
    {
        return false;
    }
    
    
    public function isReserveData()
    {
        return isset($this->reserve_data['id']) && 
               @$this->reserve_data['id'] > 0;        
    }

    
    public function isReserveByService()
    {
        return $this->reserve_data['invoice_id'] > 0;
    }
    
    
    public function getInvoiceId()
    {
        return @$this->reserve_data['invoice_id'];
    }
    
    public function getAccountOperationId()
    {
        return @$this->reserve_data['acc_op_id'];
    }
    
    public function getFrlId()
    {
        return $this->reserve_data['frl_id'];
    }

    public function getEmpId()
    {
        return $this->reserve_data['emp_id'];
    }    
    
    public function getArbitrageDateClose()
    {
        return @$this->reserve_data['arbitrage_date_close'];
    }

    public function isArbitrage()
    {
        return isset($this->reserve_data['arbitrage_id']) && 
               @$this->reserve_data['arbitrage_id'] > 0;
    }

    public function isArbitrageOpen()
    {
        return isset($this->reserve_data['arbitrage_status']) && 
               @$this->reserve_data['arbitrage_status'] == ReservesArbitrage::STATUS_OPEN;
    }
    
    public function isArbitrageClosed()
    {
        return @$this->reserve_data['arbitrage_status'] == ReservesArbitrage::STATUS_CLOSED;
    }

    
    public function isClosed()
    {
        return in_array($this->reserve_data['status'], array(
                    self::STATUS_PAYED, 
                    self::STATUS_ARBITRAGE));
    }
    

    public function isAllowPayout($uid)
    {
        return $this->isStatusReserve() && 
               ($this->isStatusPayNew() || $this->isStatusPayError()) && 
               $this->reserve_data['frl_id'] == $uid;
    }


    public function isStatusPayAllowPayout()
    {
        return $this->isStatusPayNew() || $this->isStatusPayError();
    }
    

    public function isAllowPayoutForQueue()
    {
        return $this->isStatusReserve() && 
               ($this->isStatusPayNew() || 
                $this->isStatusPayError() || 
                $this->isStatusPayInprogress());
    }
    

    public function isStatusReserve()
    {
        return $this->reserve_data['status'] == self::STATUS_RESERVE; 
    }

    /**
     * Проверка резерва БС на возможность обращения арбитража
     * когда обращения еще небыло и статусы позволяют
     * 
     * @return type
     */
    public function isAllowArbitrageNew()
    {
        return !$this->isArbitrage() && 
               $this->isStatusReserve() && 
               $this->isStatusBackNone() &&
               $this->isStatusPayNone();
    }

    

    public function isStatusReserved()
    {
        return in_array($this->reserve_data['status'], array(
            self::STATUS_RESERVE,
            self::STATUS_PAYED,
            self::STATUS_ARBITRAGE)); 
    }

    
    public function isStatusCancel()
    {
        return $this->reserve_data['status'] == self::STATUS_CANCEL; 
    }
    
    public function getStatus()
    {
        return $this->reserve_data['status'];
    }    
    
    
    public function isSubStatusError()
    {
        return $this->isStatusBackError() || $this->isStatusPayError();
    }
    

    public function isStatusPayError()
    {
        return $this->reserve_data['status_pay'] == self::SUBSTATUS_ERR;
    }

    public function isStatusPayInprogress()
    {
        return $this->reserve_data['status_pay'] == self::SUBSTATUS_INPROGRESS;
    }

    public function isStatusPayPayed()
    {
        return $this->reserve_data['status_pay'] == self::SUBSTATUS_PAYED;
    }
    
    public function isStatusPayNone()
    {
        return $this->reserve_data['status_pay'] == self::SUBSTATUS_NONE;
    }

    public function isStatusPayNew()
    {
        return $this->reserve_data['status_pay'] == self::SUBSTATUS_NEW;
    }

    public function getStatusPay()
    {
        return $this->reserve_data['status_pay'];
    }
    
    public function isStatusBackNew()
    {
        return $this->reserve_data['status_back'] == self::SUBSTATUS_NEW;
    }
    
    public function isStatusBackPayed()
    {
        return $this->reserve_data['status_back'] == self::SUBSTATUS_PAYED;
    }

    public function isStatusBackError()
    {
        return $this->reserve_data['status_back'] == self::SUBSTATUS_ERR;
    }

    public function isStatusBackNone()
    {
        return $this->reserve_data['status_back'] == self::SUBSTATUS_NONE;
    }    
    
    public function getStatusBack()
    {
        return $this->reserve_data['status_back'];
    }

    public function getReservePrice()
    {
        return $this->reserve_data['reserve_price'];
    }

    public function getPrice()
    {
        return $this->reserve_data['price'] - $this->getNDFL();
    }
    
    public function getNDFL()
    {
        return round($this->reserve_data['price'] * ($this->isFrlPhis()?0.13:0));
    }


    public function getPriceWithOutNDFL()
    {
        return $this->reserve_data['price'];
    }
    
    
    public function getPayback()
    {
        if(!$this->isArbitrage()) return 0;
        
        $payout = ($this->reserve_data['arbitrage_price'] > $this->reserve_data['price'])?
                $this->reserve_data['price']:
                $this->reserve_data['arbitrage_price'];
            
        return $this->reserve_data['price'] - $payout;
    }
    

    public function getPayoutSumWithOutNDFL()
    {
        $sum = 0;
        
        if ($this->isArbitrage()) {
            $sum = ($this->reserve_data['arbitrage_price'] > $this->reserve_data['price'])?
                    $this->reserve_data['price']:
                    $this->reserve_data['arbitrage_price'];
        } else {
            $sum = $this->reserve_data['price'];
        }
        
        return $sum;
    }
    
    public function getPayoutSum()
    {
        $sum = $this->getPayoutSumWithOutNDFL();
        $NDFL = round($sum * ($this->isFrlPhis()?0.13:0));
        return $sum - $NDFL;
    }

    
    public function getPayoutNDFL()
    {
        $sum = $this->getPayoutSumWithOutNDFL();
        $NDFL = round($sum * ($this->isFrlPhis()?0.13:0));
        return $NDFL;
    }
    

    public function getID()
    {
        return intval($this->reserve_data['id']);
    }

    public function getSrcId()
    {
        return intval($this->reserve_data['src_id']);
    }
    
    public function isFrlPhis()
    {
        return ReservesHelper::getInstance()->
               isPhisRT($this->reserve_data['frl_id']);
    }
    
    public function isEmpJuri()
    {
        return ReservesHelper::getInstance()->
                isJuri($this->reserve_data['emp_id']);
    }
    
    public function getNUM()
    {
        return sprintf(static::NUM_FORMAT, $this->reserve_data['src_id']);
    }
    

    public function getTable() 
    {
        return $this->TABLE;
    }
    
   
    public function getArbitrageNDFL()
    {
        return round($this->reserve_data['arbitrage_price'] * ($this->isFrlPhis()?0.13:0));
    }
    
    
    public function getArbitragePricePay() 
    {
        return $this->reserve_data['arbitrage_price'] - $this->getArbitrageNDFL();
    }
    
    
    public function getArbitragePriceBack() 
    {
        return $this->reserve_data['price'] - $this->reserve_data['arbitrage_price'];
    }
    
    
    public function getArbitragePriceWithOutNDFL()
    {
        return $this->reserve_data['arbitrage_price'];
    }

    
    public function isAllowFeedback($is_emp) 
    {
        if ($this->isArbitrage()) 
        {
            $field = 'allow_fb_'.($is_emp ? 'emp' : 'frl');
            return $this->reserve_data['arbitrage_'.$field] == 't';
        }
        
        return true;
    }


    public function isEmpAllowFinance()
    {
        return $this->isAllowFinance($this->reserve_data['emp_id'], true);
    }

    public function isFrlAllowFinance()
    {
        return $this->isAllowFinance($this->reserve_data['frl_id'], false);
    }    
    
    public function isAllowFinance($uid, $is_emp = false)
    {
        $is_valid = ReservesHelper::getInstance()->isValidUserReqvs($uid, $is_emp);
        $fn_status = ReservesHelper::getInstance()->getFinStatus($uid);
        
        if($is_valid && $fn_status === 0)
        {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            user_content::sendToModeration($uid, user_content::MODER_SBR_REQV);
        }
        
        return $is_valid && ($fn_status == 2);            
    }
    
    public function isEmpFinanceReqvsValid()
    {
        return ReservesHelper::getInstance()->isValidUserReqvs($this->reserve_data['emp_id'], true);
    }
    
    public function isEmpFinanceValid()
    {
        $is_valid = $this->isEmpFinanceReqvsValid() && !$this->isEmpFinanceFailStatus();
        ReservesHelper::getInstance()->saveCurrentUrlForFinance($is_valid);
        
        return $is_valid;
    }
    
    public function isFrlFinanceValid()
    {
        $is_valid = $this->isFrlFinanceReqvsValid() && !$this->isFrlFinanceFailStatus();
        ReservesHelper::getInstance()->saveCurrentUrlForFinance($is_valid);
        
        return $is_valid;
    }
    
    public function isFrlFinanceReqvsValid()
    {
        return ReservesHelper::getInstance()->isValidUserReqvs($this->reserve_data['frl_id'], false);
    }
    
    public function isEmpFinanceFailStatus()
    {
        return $this->isFinanceFailStatus($this->reserve_data['emp_id']);
    }
    
    public function isEmpFinanceDeclineStatus()
    {
        return ReservesHelper::getInstance()->finStatusIsDecline($this->reserve_data['emp_id']);
    }    
    
    public function isFrlFinanceFailStatus()
    {
        return $this->isFinanceFailStatus($this->reserve_data['frl_id']);
    }
    
    public function isFinanceFailStatus($uid)
    {
        $fn_status = ReservesHelper::getInstance()->getFinStatus($uid);
        return $fn_status < 0;        
    }

    public function getEmpFinanceBlockedReason()
    {
        return ReservesHelper::getInstance()->getFinBlockedReason($this->reserve_data['emp_id']);
    }
    
    public function getFrlFinanceBlockedReason()
    {
        return ReservesHelper::getInstance()->getFinBlockedReason($this->reserve_data['frl_id']);
    }    
    
    public function getPayoutTypeText($is_short = false)
    {
        return ReservesHelper::getInstance()->getPayoutType($this->getID(), $is_short);
    }
    
    
    public function getTax($persent = true)
    {
        return @$this->reserve_data['tax'] * (($persent)?100:1);
    }
    
    
    
    /**
     * Подозрительная сделка?
     * 
     * @return boolean
     */
    public function isFrod()
    {
        return @$this->reserve_data['is_frod'] == 't';
    }

    

    /**
     * Проверка параметров сделки на подозрительность
     * 
     * @return type
     */
    public function frodCheck()
    {
        return @$this->reserve_data['date_reserve'] && 
                $this->getReservePrice() > self::FROD_MONEY_LIMIT &&
                ((time() - strtotime($this->reserve_data['date_reserve'])) < self::FROD_TIME_LIMIT);
    }
    
    
    
    /**
     * Вернуть причину отказа в резерве средств
     * 
     * @todo: само решение задачи - ненравится тк уже есть блокировка по финансам и это еще нагромождение сверху
     * по тикету https://beta.free-lance.ru/mantis/view.php?id=28571
     */
    public function getReasonReserve()
    {
        return @$this->reserve_data['reason_reserve'];
    }

    /**
     * Вернуть причину отказа в возврате средств
     * 
     * @return type
     */ 	
    public function getReasonPayback()
    {
        return @$this->reserve_data['reason_payback'];
    }

    
    /**
     * Вернуть причину отказа в выплате
     * 
     * @return type
     */
    public function getReasonPayout()
    {
        return @$this->reserve_data['reason_payout'];
    }
    

    public function getDate()
    {
        return $this->reserve_data['date'];
    }

    



    /**
     * Вернуть дату завершения резерва после всех выплат/возврата.
     * Вычисляем самую поздню.
     * 
     * $with_correct - корректировка даты при сделке по безналу
     * 
     * @return type
     */
    public function getLastCompleteDate($with_correct = true)
    {
        $timestamp = null;
        $timestamp_reserve = strtotime($this->reserve_data['date_reserve']);
        $is_bank = false;
        
        if ($this->reserve_data['last_status_pay']) {
            $timestamp = strtotime($this->reserve_data['last_status_pay']);
            
            //Если выплата по безналу то - 1 рабочий день.
            if($with_correct && !ReservesHelper::getInstance()->getPayout()->isPayoutByService($this->getID())) {
                $timestamp_tmp = strtotime('- 1 day', $timestamp);
                $is_bank = true;
                if ($timestamp_tmp > $timestamp_reserve) {
                    $timestamp = $timestamp_tmp;
                }
            }
        }

        
        if ($this->reserve_data['last_status_back']) {
            $timestamp_tmp = strtotime($this->reserve_data['last_status_back']);
            
            //Если возврат по безналу то - 1 рабочий день.
            if($with_correct && !$this->isReserveByService()) {
                $timestamp_back_fix = strtotime('- 1 day', $timestamp_tmp);
                $is_bank = true;
                if ($timestamp_back_fix > $timestamp_reserve) {
                    $timestamp_tmp = $timestamp_back_fix;
                }
            }
            
            if (!$timestamp || $timestamp_tmp > $timestamp) {
               $timestamp =  $timestamp_tmp;
            }
        }

        
        //Если нет дат обновления статусов выплаты/возврата то возвращаем дату закрытия
        if($this->reserve_data['date_complete'] && !$timestamp) {
            $timestamp = strtotime($this->reserve_data['date_complete']);           
        }

        //Если по безналу то закрываем ближайшим рабочим днем
        if($is_bank) {
            //если выходной то берем следующий день
            if(in_array(idate('w', $timestamp),array(0,6))) {
                $timestamp = strtotime('+ 1 day', $timestamp);
            }

            //если опять выходной то еще раз следующий день
            if(in_array(idate('w', $timestamp),array(0,6))) {
                $timestamp = strtotime('+ 1 day', $timestamp);
            }            
        }
        
        return $timestamp;
    }
    
    
    public function getReservesServiceReqvs()
    {
        return new ReservesServiceReqvModel($this->getID());
    }
    
    
    /**
     * Возвращает реквизиты заказчика из слепков
     * @return type
     */
    public function getEmpReqv()
    {
        if (!$this->reserve_data['date_reserve']) {
            $reqvs = ReservesHelper::getInstance()->getUserReqvs($this->getEmpId());
            if ($reqvs && $reqvs['form_type']) {
                $reqv = $reqvs[$reqvs['form_type']];
                $reqv['form_type'] = $reqvs['form_type'];
                $reqv['rez_type'] = $reqvs['rez_type'];
            }
        } elseif ($this->isReserveByService()) {
            $reqv = $this->getReservesServiceReqvs()->getReqv($this->getEmpId());
        } else {
            $reqv = $this->getReservesBank()->getCheckByReserveId($this->getID());
            $reqv['form_type'] = sbr::FT_JURI;
            
            //Определяем резидентство
            $reqv['rez_type'] = sbr::RT_RU;
            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/country.php';
            $country = new country();
            $country_iso = !empty($reqv['country_iso']) 
                    ? $reqv['country_iso'] 
                    : $country->getCountryISO($reqv['country']);
            if ($country_iso && $country_iso != country::ISO_RUSSIA) {
                $reqv['rez_type'] = sbr::RT_UABYKZ;
            }
        }
        return $reqv;
    }
    
    
    /**
     * Возвращает реквизиты фрилансера из слепков
     * @return type
     */
    public function getFrlReqv()
    {
        $payout_reqv = ReservesHelper::getInstance()->getPayoutReqv($this->getID());
        
        if ($payout_reqv) {
            $reqv = mb_unserialize($payout_reqv['fields']);
        } else {
            $reqvs = ReservesHelper::getInstance()->getUserReqvs($this->getFrlId());
            if ($reqvs && $reqvs['form_type']) {
                $reqv = $reqvs[$reqvs['form_type']];
                $reqv['form_type'] = $reqvs['form_type'];
                $reqv['rez_type'] = $reqvs['rez_type'];
            }
        }
        return $reqv;
    }
    
    
    
    public function getReservesBankReqvByIds($ids = array())
    {
        return $this->db()->rows("
                SELECT 
                    r.src_id, 
                    e.uid, 
                    rb.fio, 
                    rb.type,
                    rb.full_name AS name,
                    rb.address,
                    rb.index,
                    rb.city,
                    rb.country,
                    rb.country_iso,
                    e.city AS city_id,
                    e.country AS country_id
                FROM " . self::$_TABLE . " AS r
                INNER JOIN employer AS e ON e.uid = r.emp_id
                INNER JOIN " . ReservesBank::$_TABLE . " AS rb ON rb.reserve_id = r.id
                WHERE r.src_id IN (?l)", $ids);
    }
    
}