<?php


require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesTServiceOrderModel.php');


class OrderStatusIndicator extends CWidget
{
    public $is_ajax = false;
    protected $stages_list = array();
    protected $active_status = NULL;
    public $order = NULL;
    protected $paytype = TServiceOrderModel::PAYTYPE_RESERVE;

    public function init() 
    {
        $_arbitrage = false;
        
        if($this->order){
            $this->paytype = $this->order['pay_type'];
            switch($this->paytype){
                case TServiceOrderModel::PAYTYPE_DEFAULT:
                    $this->active_status = $this->order['status'];
                    break;
                
                case TServiceOrderModel::PAYTYPE_RESERVE:
                    $reserve = clone $this->order['reserve'];
                    $reserve->setReserveDataByKey('src_status', $this->order['status']);
                    $this->active_status = $reserve->getReserveOrderStatus();
                    $_arbitrage = $reserve->isArbitrage();
                    break;
            }
        }

        $this->active_status = ($this->active_status !== NULL)?
                intval($this->active_status):
                $this->active_status;
        
        //Прямая оплата
        if ($this->active_status === NULL || 
            $this->paytype == TServiceOrderModel::PAYTYPE_DEFAULT) {
        
            $_default_stages = array(
                array(
                    'status' => NULL,
                    'title' => 'Создание заказа',
                    'texts' => 'Формирование исходного задания, бюджета, сроков выполнения работы и выбора типа оплаты.'
                ),

                array(
                    'status' => NULL,
                    'adv' => true,
                    'title' => 'Обратите внимание',
                    'texts' => array(
                        'Тип оплаты в вашем заказе — <b>Прямая оплата</b>, 
                         при которой вы самостоятельно регулируете все претензии 
                         по качеству, срокам и оплате выполненой работы.',

                        'Для безопасной работы в заказе рекомендуем оплату через 
                         «<a href="/promo/bezopasnaya-sdelka/" target="_blank"><b>Безопасную сделку</b></a>»'
                    )
                ),

                array(
                    'status' => TServiceOrderModel::STATUS_NEW,
                    'title' => 'Согласование условий',
                    'texts' => 'Обсуждение задания и условий выполнения работы. Подтверждение заказа Исполнителем'
                ),

                array(
                    'status' => TServiceOrderModel::STATUS_NEW,
                    'adv' => true,
                    'title' => 'Обратите внимание',
                    'texts' => array(
                        'Тип оплаты в вашем заказе — <b>Прямая оплата</b>, 
                         при которой вы самостоятельно регулируете все претензии 
                         по качеству, срокам и оплате выполненой работы.',

                        'Для безопасной работы в заказе рекомендуем оплату через 
                         «<a href="/promo/bezopasnaya-sdelka/" target="_blank"><b>Безопасную сделку</b></a>»'
                    )
                ),

                array(
                    'status' => array(
                        TServiceOrderModel::STATUS_DECLINE,
                        TServiceOrderModel::STATUS_CANCEL
                     ),
                    'title' => 'Отмена заказа',
                    'break' => true
                ),

                array(
                    'status' => array(
                        TServiceOrderModel::STATUS_ACCEPT,
                        TServiceOrderModel::STATUS_FIX
                    ),
                    'title' => 'Выполнение работы',
                    'texts' => 'Процесс выполнения задания в заказе до получения Заказчиком итогового результата работы.'
                ),

                array(
                    'status' => TServiceOrderModel::STATUS_FRLCLOSE,
                    'title' => 'Завершение заказа',
                    'texts' => 'Получение заказчиком результата работы, закрытие заказа с отзывом о сотрудничестве.'
                )
            );
        
            $this->stages_list[TServiceOrderModel::PAYTYPE_DEFAULT] = $_default_stages;
        }    
            
        
        //Через Безопасную сделку
        if ($this->active_status === NULL || 
            $this->paytype == TServiceOrderModel::PAYTYPE_RESERVE) {        
        
            $_reserve_stages = array(            
                array(
                    'status' => NULL,
                    'title' => 'Создание заказа',
                    'texts' => 'Формирование исходного задания, бюджета, сроков выполнения работы и выбора типа оплаты.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Negotiation,
                    'title' => 'Согласование условий',
                    'texts' => 'Обсуждение задания и условий выполнения работы. Подтверждение заказа Исполнителем'
                ),            

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Cancel,
                    'title' => 'Отмена заказа',
                    'break' => true
                ),            

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Reserve,
                    'title' => 'Резервирование',
                    'texts' => 'Резервирование Заказчиком суммы оплаты по заказу. Деньги перечисляются и хранятся на сайте.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_InWork,
                    'title' => 'Выполнение работы',
                    'texts' => 'Процесс выполнения задания в заказе до получения Заказчиком итогового результата работы.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Arbitrage,
                    'exclude' => !$_arbitrage,
                    'title' => 'Арбитраж',
                    'texts' => 'Рассмотрение заказа Арбитром, вынесение решения о выплате или возврате суммы.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Done,
                    'exclude' => $_arbitrage,
                    'title' => 'Завершение заказа',
                    'texts' => 'Получение заказчиком результата работы, закрытие заказа с отзывом о сотрудничестве.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Pay,
                    'title' => 'Выплата суммы',
                    'texts' => 'Перечисление суммы оплаты Исполнителю (и/или ее возврат Заказчику по решению Арбитра).'
                )
            );
        
            $this->stages_list[TServiceOrderModel::PAYTYPE_RESERVE] = $_reserve_stages;
        }    
    }
    
    
    
    public function run($ret = false) 
    {
        return $this->render('order-status-indicator', array(
            'stages_list' => $this->stages_list,
            'active_status' => $this->active_status,
            'active_paytype' => $this->paytype,
            'is_ajax' => $this->is_ajax
        ), $ret);
    }
    
    
    public function getAjaxRender()
    {
        $this->is_ajax = true;
        $this->init();
        return $this->run(true);
    }
    
}
