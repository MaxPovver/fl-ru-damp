<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate_a1.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/sbr.php";

/**
 * СМС уведомления по резервированию и арбитражу
 */
class ReservesSms extends sms_gate_a1
{
    /**
     * Дополнительные статусы
     */
    const STATUS_NEW_ARBITRAGE_FRL = 9;
    const STATUS_NEW_ARBITRAGE_EMP = 10;
    const STATUS_CANCEL_ARBITRAGE_EMP = 12;
    const STATUS_CANCEL_ARBITRAGE_FRL = 13;
    const STATUS_APPLY_ARBITRAGE_EMP = 14;
    const STATUS_APPLY_ARBITRAGE_FRL = 15;
    
    const STATUS_RESERVE_DONE_EMP = 16;
    const STATUS_RESERVE_DONE_FRL = 17;
    
    
    protected $PAY_EMP = '%g руб. выплатить исполнителю и %g руб. вернуть вам';
    protected $PAY_FRL = '%g руб. выплатить вам и %g руб. вернуть заказчику';
    protected $PAY_ALL_EMP = 'всю сумму бюджета выплатить исполнителю';
    protected $PAY_ALL_FRL = 'всю сумму бюджета выплатить вам';
    protected $BACK_ALL_EMP = 'всю сумму бюджета вернуть вам';
    protected $BACK_ALL_FRL = 'всю сумму бюджета вернуть заказчику';
    
    /**
     * Сообщения состояния заказа ТУ
     * 
     * @var type 
     */
    public $text_templates = array(
        //self::STATUS_NEW_ARBITRAGE_FRL => 'Заказ #%d передан заказчиком в Арбитраж. В ближайшее время Арбитр рассмотрит ситуацию по заказу и вынесет решение о выплате, возврате или разделении суммы бюджета.',
        //self::STATUS_NEW_ARBITRAGE_EMP => 'Заказ #%d передан исполнителем в Арбитраж. В ближайшее время Арбитр рассмотрит ситуацию по заказу и вынесет решение о выплате, возврате или разделении суммы бюджета.',
        //self::STATUS_CANCEL_ARBITRAGE_EMP => 'Арбитраж по заказу #%d отменен, исполнитель продолжил выполнение работы.',
        //self::STATUS_CANCEL_ARBITRAGE_FRL => 'Арбитраж по заказу #%d отменен, вы можете продолжить выполнение работы.',
        //self::STATUS_APPLY_ARBITRAGE_EMP => 'По заказу #%d Арбитром вынесено решение: %s.',
        //self::STATUS_APPLY_ARBITRAGE_FRL => 'По заказу #%d Арбитром вынесено решение: %s.',
        
        //self::STATUS_RESERVE_DONE_EMP => 'Сумма %s руб. по заказу #%d зарезервирована, Исполнитель начал выполнение работы.',
        //self::STATUS_RESERVE_DONE_FRL => 'Сумма %s руб. по заказу #%d зарезервирована, вы можете начать выполнение работы.'
    );

    /**
     * Телефончик то есть?
     * 
     * @return type
     */
    public function isPhone()
    {
        return !empty($this->_msisdn);
    }

    
    
    /**
     * Отправить СМС по статусу арбитража
     * 
     * @param int $status
     * @param int $num
     * @return boolean
     */
    public function sendByStatus()
    {
        $args = func_get_args();
        $cnt = count($args);
        if(!$cnt) return FALSE;
        
        $status = $args[0];
        unset($args[0]);
        
        if (!isset($this->text_templates[$status]) || !$this->isPhone())  {
            
            return FALSE;
        }
        
        $message = vsprintf($this->text_templates[$status], $args);
        return $this->sendSMS($message);
    }
    
    /**
     * Отправить СМС по состоянию арбитража c информацией о выплатах
     * 
     * @param int $status
     * @param int $num
     * @return boolean
     */
    public function sendByStatusAndPrice($status, $pricePay, $priceBack, $id)
    {
        if (!isset($this->text_templates[$status]) ||
           !in_array($status, array(self::STATUS_APPLY_ARBITRAGE_EMP, self::STATUS_APPLY_ARBITRAGE_FRL)) || 
           !$this->isPhone())  {
            
            return FALSE;
        }
           
        $payBoth = $pricePay && $priceBack;
        $priceTemplateCode = $payBoth ? 'PAY' : ($priceBack ? 'BACK_ALL' : 'PAY_ALL');        
        $priceTemplateCode .= '_' . ($status == self::STATUS_APPLY_ARBITRAGE_EMP ? 'EMP' : 'FRL');
        $priceTemplate = $this->{$priceTemplateCode};
        $priceText = $payBoth ? sprintf($priceTemplate, $pricePay, $priceBack) : $priceTemplate;
        
        $message = sprintf($this->text_templates[$status], $id, $priceText);
        return $this->sendSMS($message);
    }

    



    /**
     * Создаем сами себя
     * @return TServiceModel
     */
    public static function model($uid) 
    {
        $phone = '';
        $reqv = sbr_meta::getUserReqvs($uid);
        
        if($reqv)
        {
            $ureqv = $reqv[$reqv['form_type']];
            $phone = $ureqv['mob_phone'];
        }

        $class = get_called_class();
        return new $class($phone);
    }
}