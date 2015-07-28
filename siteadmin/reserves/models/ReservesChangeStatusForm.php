<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Select.php");
require_once('ReservesAdminOrderModel.php');

class ReservesChangeStatusForm extends Form_View
{
    
    public function setDefaultStatus($status, $is_emp)
    {
        if($status == ReservesAdminOrderModel::ReserveOrderStatus_Arbitrage) {
            $status = ($is_emp === 't')?
                    ReservesAdminOrderModel::ReserveOrderStatus_ArbitrageEmp:
                    ReservesAdminOrderModel::ReserveOrderStatus_ArbitrageFrl;
        }
        
        $this->setDefault('status', $status);
    }

    
    public function init()
    {
        $default = ReservesAdminOrderModel::$_reserve_order_status_txt;
        
        $options = array(
            '' => 'Выберите статус',
            ReservesAdminOrderModel::ReserveOrderStatus_Reserve => 
                $default[ReservesAdminOrderModel::ReserveOrderStatus_Reserve],
            ReservesAdminOrderModel::ReserveOrderStatus_InWork => 
                $default[ReservesAdminOrderModel::ReserveOrderStatus_InWork],
            ReservesAdminOrderModel::ReserveOrderStatus_ArbitrageEmp => 'арбитраж как заказчик',
            ReservesAdminOrderModel::ReserveOrderStatus_ArbitrageFrl => 'арбитраж как исполнитель'
        );
        
        $this->addElement(
           new Form_Element_Select('status', array(
               'label' => 'Статус',
               'multioptions' => $options
        ))); 
        
        $this->addElementByName('submit', 'submit', array(
            'label' => 'Изменить',
            'disableLoadDefaultDecorators' => true,
            'decorators' => array('ViewHelper')
        ));
    }
    
}