<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');

/**
 * Class TServiceOrderMessages
 *
 * Виджет - Переписка в заказе
 */
class TServiceOrderMessages extends CWidget 
{
        protected $uid; 
        protected $order_id;
        protected $is_owner;
        protected $frl_id;
        
        private $messages;

        public function init() 
        {
            parent::init();
            
            $msg_model = TServiceMsgModel::model();
            $this->messages = $msg_model->getList($this->order_id);
            
            if($this->is_owner)
            {
                //Помечаем сообщения в заказе как прочтенные
                $msg_model->markAsRead($this->order_id, $this->uid);
            }

        }
    
        public function run() 
        {
            //собираем шаблон
            $this->render('t-service-order-messages', array(
                'messages' => $this->messages,
                'frl_id' => $this->frl_id
            ));
	}
}