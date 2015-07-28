<?php
/**
 * Class TServiceOrderBreadcrumbs
 *
 * Виджет - Блок хлебных крошек
 */
class TServiceOrderBreadcrumbs extends CWidget 
{
    const ORDER_TEXT = 'Заказ №%d %s(%s)';
    const TEXT_TSERVICE = '<a class="b-layout__link_no-decorat" href="%s">услуги</a> ';
    const TEXT_PROJECT = '<a class="b-layout__link_no-decorat" href="/projects/%d">по проекту</a> ';
    
    const PAY_DIRECT = 'с прямой оплатой';
    const PAY_RESERVE = 'с оплатой через Безопасную сделку';
    
    protected $order;
    protected $is_emp;

    public function run() 
    {
        //собираем шаблон
        $this->render('t-service-order-breadcrumbs', array(
            'url_all' => $this->is_emp ? $this->getEmpUrlAll() : '/tu-orders/',
            'order_text' => $this->getOrderText()
        ));
	}
    
    public function setOrder($order)
    {
        $this->order = $order;
    }
    
    private function getEmpUrlAll()
    {
        return '/users/' . $this->order['employer']['login'] . '/tu-orders/';
    }
    
    private function getOrderText()
    {
        switch ($this->order['type']) {
            case TServiceOrderModel::TYPE_TSERVICE:
                $service = sprintf(self::TEXT_TSERVICE, 
                        tservices_helper::card_link($this->order['tu_id'], $this->order['title']));
                break;

            case TServiceOrderModel::TYPE_PROJECT:
                $service = sprintf(self::TEXT_PROJECT, $this->order['tu_id']);
                break;
            
            case TServiceOrderModel::TYPE_PERSONAL:
                $service = '';
                break;
        }

        $pay_text = isset($this->order['reserve']) ? self::PAY_RESERVE : self::PAY_DIRECT;
        
        return sprintf(self::ORDER_TEXT, $this->order['id'], $service, $pay_text);
    }
}