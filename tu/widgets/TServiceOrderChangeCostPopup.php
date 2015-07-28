<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');

/**
 * Class TServiceOrderChangeCostPopup
 * Виджет показывает попап в заказе ТУ при изменении стоимости, сроков и вида расчета для заказчика
 */

class TServiceOrderChangeCostPopup extends CWidget 
{
        public $order;
        
        /**
         * Метод сразу печатает в поток окошко попапа
         * см render
         * 
         * @return boolean
         */
        public function run() 
        {            
            //Задействуем для этого юзера и категории ТУ новую БС с резервом или нет
            $sufix = ((tservices_helper::isAllowOrderReserve($this->order['category_id']))?'-reserve':'');
            $this->render("t-service-order-change-cost{$sufix}-popup", array('order' => $this->order));
	}
}