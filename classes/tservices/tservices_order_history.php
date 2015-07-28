<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/events.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

/**
 * Модель истории изменений заказа типовых услуг
 */
class tservices_order_history extends atservices_model 
{
    
    private $TABLE = 'tservices_order_history';
    
    /**
     * Id заказа ТУ
     * 
     * @var int
     */
    private $order_id;
    
    /*
     * Статусы резервирования
     */
    const TEXT_RESERVE = "Оплата через Безопасную сделку";
    const TEXT_NORESERVE = "Прямая оплата";
    
    const MES_CREATE = "Создан заказ \"%s\", срок %s, бюджет %s (%s).";
    const MES_UPD_PRICE = "Бюджет заказа: %s >> %s.";
    const MES_UPD_DAYS = "Срок выполнения заказа: %s >> %s.";
    const MES_UPD_RESERVE = "Способ оплаты заказа: %s >> %s.";
    
    const MES_UPD_STATUS_ACCEPT = "Исполнитель подтвердил заказ.";
    const MES_UPD_STATUS_CANCEL = "Заказчик отменил заказ.";
    const MES_UPD_STATUS_DECLINE = "Исполнитель отказался от заказа.";
    
    const MES_RESERVE = "Сумма %s зарезервирована (удержана комиссия %s%).";//???

    const MES_ARB_START = "%s обратился в Арбитраж.";
    const MES_ARB_CANCEL = "Арбитраж отменен.";
    const MES_ARB_DESIDE = "Арбитр вынес решение: %s.";
    const MES_ARB_DESIDE_FRL = "%s выплатить Исполнителю";
    const MES_ARB_DESIDE_EMP = "%s вернуть Заказчику";
    
    const MES_DOCUMENT = "Загружен документ \"%s\".";
    
    //const MES_DONE = "Заказчик принял работу и подтвердил выплату 100% бюджета Исполнителю";
    const MES_FEEDBACK = "%s оставил %s отзыв о сотрудничестве.";
    const MES_CLOSE = "Заказ закрыт.";
    const MES_DONE = "Исполнитель уведомил о выполненной работе.";
    const MES_FIX = "Заказчик отправил на доработку.";
    
    const MES_RESERVE_SUCCESS = "Заказчик успешно зарезервировал сумму %s.";
    const MES_RESERVE_INPORGRESS = "Заказчик подал запрос на резервирование суммы.";
    const MES_RESERVE_ERR = "Резервирование суммы Заказчиком временно приостановлено.";
    const MES_RESERVE_DONE = "Заказчик завершил сотрудничество, подтвердив: %s выплатить Исполнителю.";
    
    const MES_RESERVE_PAYOUT_REQ = "Исполнитель подал запрос на выплату суммы%s.";
    const MES_RESERVE_PAYOUT = "Сумма %s перечислена Исполнителю%s%s";
    const MES_RESERVE_PAYOUT_NDFL = ", НДФЛ %s удержан и перечислен в бюджет РФ.";
    const MES_RESERVE_PAYOUT_ERR = "Выплата суммы Исполнителю временно приостановлена.";
    const MES_RESERVE_PAYBACK_REQ = "Заказчик подал запрос на возврат суммы.";
    const MES_RESERVE_PAYBACK = "Сумма %s перечислена Заказчику.";
    const MES_RESERVE_PAYBACK_ERR = "Возврат суммы Заказчику временно приостановлен.";
    
    const MES_ADMIN_CHANGE_STATUS = "Статус заказа изменен.";
    
    
    
    public function __construct($order_id) 
    {
        $this->order_id = (int)$order_id;
        
        //При генериации счета
        Events::register('generateInvoice2', array($this, 'reservePriceInprogress'));
        //Вешаем обработку события при генерации файла
        Events::register('generate_file', array($this, 'saveFileForEvents'));
    }
    
    

    
    /*
     * Возвращает массив событий изменения указанного заказа
     */
    public function getHistory() 
    {
        return $this->db()->rows("SELECT * FROM {$this->TABLE} WHERE order_id = ?i ORDER BY date DESC", $this->order_id);
    }
    
    
    
    /**
     * Сохранить историю заказа
     * 
     * @param type $new_order
     * @param type $old_order
     */
    public function save($new_order, $old_order = null) 
    {
        if (!$old_order) 
        {
            $this->addEvent(sprintf(self::MES_CREATE, 
                    htmlspecialchars($new_order['title']), 
                    tservices_helper::days_format($new_order['order_days']), 
                    tservices_helper::cost_format($new_order['order_price'], true), 
                    (tservices_helper::isOrderReserve($new_order['pay_type']))?self::TEXT_RESERVE:self::TEXT_NORESERVE
            ));
        } 
        else 
        {
            if ($old_order['order_price'] != $new_order['order_price']) 
            {
                $this->addEvent(sprintf(self::MES_UPD_PRICE, 
                        tservices_helper::cost_format($old_order['order_price'], true), 
                        tservices_helper::cost_format($new_order['order_price'], true)
                ));
            }

            if ($old_order['order_days'] != $new_order['order_days']) 
            {
                $this->addEvent(sprintf(self::MES_UPD_DAYS, 
                        tservices_helper::days_format($old_order['order_days']), 
                        tservices_helper::days_format($new_order['order_days'])
                ));
            }
            
            if ($old_order['pay_type'] != $new_order['pay_type']) 
            {
                $is_reserve = tservices_helper::isOrderReserve($new_order['pay_type']);
                $str_from = ($is_reserve)?self::TEXT_NORESERVE:self::TEXT_RESERVE;
                $str_to = (!$is_reserve)?self::TEXT_NORESERVE:self::TEXT_RESERVE;
                $this->addEvent(sprintf(self::MES_UPD_RESERVE, $str_from, $str_to));
            }
        }
    }
    
    
    
    public function saveFeedback($is_emp, $fbtype) {
        $message = sprintf(self::MES_FEEDBACK, 
            ($is_emp?'Заказчик':'Исполнитель'), 
            ($fbtype>0?'положительный':'отрицательный')
        );
        $this->addEvent($message);
    }
    
    public function saveStatus($status) {
        switch ($status) {
            case TServiceOrderModel::STATUS_ACCEPT:
                $message = self::MES_UPD_STATUS_ACCEPT;
                break;
            
            case TServiceOrderModel::STATUS_CANCEL:
                $message = self::MES_UPD_STATUS_CANCEL;
                break;
            
            case TServiceOrderModel::STATUS_DECLINE:
                $message = self::MES_UPD_STATUS_DECLINE;
                break;
            
            case TServiceOrderModel::STATUS_FRLCLOSE:
                $message = self::MES_DONE;
                break;
            
            case TServiceOrderModel::STATUS_EMPCLOSE:
                $message = self::MES_CLOSE;
                break;
            
            case TServiceOrderModel::STATUS_FIX:
                $message = self::MES_FIX;
                break;
        }
        
        if($message) {
            $this->addEvent($message);
        }        
    }
    
    
    public function saveFile($fname) 
    {
        $message = sprintf(self::MES_DOCUMENT, $fname);
        $this->addEvent($message);
    }
    
    
    /**
     * При обработке события генерации файла
     * сработает это событие и мы фиксируем в иторию
     * имя сгенерированного файла 
     * 
     * @param CFile $file
     */
    public function saveFileForEvents(CFile $file)
    {
        $this->saveFile($file->original_name);
    }
    
    
    /**
     * Завершение БС с 100% выплатой исполнителю
     * 
     * @param type $price
     */
    public function reserveDone($price)
    {
        $message = sprintf(self::MES_RESERVE_DONE, tservices_helper::cost_format($price, true, false, false));
        $this->addEvent($message);
    }
    

    /**
     * Фиксации в истории успешного резервирования суммы заказчиком
     * 
     * @param type $price
     */
    public function reservePriceSuccess($price)
    {
        $message = sprintf(self::MES_RESERVE_SUCCESS, tservices_helper::cost_format($price, true, false, false));
        $this->addEvent($message);
    }

    /**
     * Фиксация в истории запроса на резервирование средств заказчиком
     */
    public function reservePriceInprogress()
    {
        $this->addEvent(self::MES_RESERVE_INPORGRESS);
    }

    /**
     * Фиксация в истории ошибки в процессе резервирования
     */
    public function reservePriceErr()
    {
        $this->addEvent(self::MES_RESERVE_ERR);
    }

    /**
     * Обращение в арбитраж
     * 
     * @param type $is_emp
     */
    public function reserveArbitrageNew($is_emp)
    {
        $this->addEvent(sprintf(self::MES_ARB_START,($is_emp)?'Заказчик':'Исполнитель'));
    }

    /**
     * Арбитраж отменен
     */
    public function reserveArbitrageCancel()
    {
        $this->addEvent(self::MES_ARB_CANCEL);
    }

    /**
     * Решение арбитра
     * 
     * @param type $frl_price
     * @param type $emp_price
     */
    public function reserveArbitrageDecide($frl_price, $emp_price)
    {
        $frl_pay = ($frl_price > 0)?sprintf(self::MES_ARB_DESIDE_FRL, tservices_helper::cost_format($frl_price, true, false, false)):'';
        $emp_back = ($emp_price > 0)?sprintf(self::MES_ARB_DESIDE_EMP, tservices_helper::cost_format($emp_price, true, false, false)):'';
        $str = (empty($frl_pay) || empty($emp_back))?$frl_pay . $emp_back:$frl_pay . ', ' . $emp_back;
        $this->addEvent(sprintf(self::MES_ARB_DESIDE, $str));
    }

    /**
     * Запрос на выплату
     */
    public function reservePayoutReq($type_text = null)
    {
        $this->addEvent(sprintf(self::MES_RESERVE_PAYOUT_REQ,($type_text)?' на ' . $type_text:''));
    }

    /**
     * Ошибка при выплате
     */
    public function reservePayoutErr()
    {
        $this->addEvent(self::MES_RESERVE_PAYOUT_ERR);
    }
    

    /**
     * Сумма выплачена исполнителю
     * 
     * @param type $price
     * @param type $ndfl
     */
    public function reservePayout($price, $ndfl = 0, $type_text = null)
    {
        $type_text = ($type_text)?" на {$type_text}":'';
        $str = ($ndfl > 0)?sprintf(self::MES_RESERVE_PAYOUT_NDFL, 
                tservices_helper::cost_format($ndfl, true, false, false)):'.';
        $this->addEvent(sprintf(self::MES_RESERVE_PAYOUT,
                tservices_helper::cost_format($price, true, false, false),
                $type_text,
                $str));
    }


    /**
     * Запрос на возврат суммы
     */
    public function reservePaybackReq()
    {
        $this->addEvent(self::MES_RESERVE_PAYBACK_REQ);
    }

    
    /**
     * Ошибка при возврате
     */
    public function reservePaybackErr()
    {
        $this->addEvent(self::MES_RESERVE_PAYBACK_ERR);
    }
    
    
    /**
     * Сумма возвращена заказчику
     * 
     * @param type $price
     */
    public function reservePayback($price)
    {
        $this->addEvent(sprintf(self::MES_RESERVE_PAYBACK,
                tservices_helper::cost_format($price, true, false, false)));
    }
    

    /**
     * Сообщение о смене статуса админом
     */
    public function adminChangeStatus()
    {
        $this->addEvent(self::MES_ADMIN_CHANGE_STATUS);
    }

    
    /**
     * Добавить сообщение в историю
     * 
     * @param string $message
     */
    private function addEvent($message) 
    {
        $this->db()->insert($this->TABLE, array(
            'order_id' => $this->order_id,
            'date' => 'NOW()',
            'description' => $message
        ));
    }
}


/*
 * В истории изменений отслеживать изменения публикаций/блокировок карточки, изменений срока, цены, названия, специализации карточки.
 */

/*
 * История изменений услуги
 * 
30.10.2014 - Резервирование - Сумма 100 000 рублей зарезервирована (удержана комиссия 10%)
30.10.2014 - Обращения в арбитраж - Заказчик/Исполнитель обратился в Арбитраж
30.10.2014 - Отмену арбитража - Арбитраж отменен
30.10.2014 - Вынесение решения - Арбитр вынес решение: разделение бюджета, 50% Исполнителю, 50% Заказчику / Арбитр вынес решение: возврат 100% бюджета Заказчику / Арбитр вынес решение: выплата 100% бюджета Исполнителю
30.10.2014 - Выплаты сумм - Сумма 50 000 рублей выплачена Исполнителю (удержана комиссия 10%) / Сумма 50 000 рублей возвращена Заказчику
30.10.2014 - Загрузки документов - Загружен документ "Название документа"
30.10.2014 - Принятие работы - Заказчик принял работу и подтвердил выплату 100% бюджета Исполнителю
 */