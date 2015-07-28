<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

/**
 * Class TServiceOrderDebtMessage
 *
 * Виджет - Показывает сообщение о возможной блокировки ТУ из-за не погашения долга ЛС
 */
class TServiceOrderDebtMessage extends CWidget 
{
        protected $user_id;
                
        public function init($user_id) 
        {
            parent::init();
            $this->user_id = $user_id;
        }

        public function run() 
        {
            $debt_info = TServiceOrderModel::model()->isDebt($this->user_id);
            if(!$debt_info) return;
            
            //собираем шаблон
            $this->render('t-service-order-debt-message', array(
                'debt_info' => $debt_info
            ));
	}
}