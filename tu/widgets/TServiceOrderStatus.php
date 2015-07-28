<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderFeedbackModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderStatusPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderFeedback.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupReserve.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayoutPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesArbitrageForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesArbitragePopup.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');


/**
 * Class TServiceOrderStatus
 *
 * Виджет - Блок информации о фрилансере в карточке заказа
 */
class TServiceOrderStatus extends CWidget 
{
        protected $order;
        protected $is_emp;
        protected $is_owner = TRUE;
        
        //Статус выводится в списке 
        //или карточке
        public $is_list = FALSE;

        public function run() 
        {
            
            $is_allow_feedback = (!$this->order['close_date'] || TServiceOrderFeedbackModel::isAllowFeedback($this->order['close_date']))
                    && (!isset($this->order['reserve']) || $this->order['reserve']->isAllowFeedback($this->is_emp));
            
            $date_feedback = ($this->order['close_date'] ? strtotime($this->order['close_date']) : time()) + TServiceOrderFeedbackModel::LIFETIME;
            $date_feedback_formatted = date("d.m.Y H:i", $date_feedback);
        
            //собираем шаблон
            $this->render('t-service-order-status', array(
                'is_adm' => !$this->is_owner,
                
                'user' => $this->order[(($this->is_emp)?'freelancer':'employer')],
                'order_id' => $this->order['id'],
                'order_status' => $this->order['status'],
                'is_emp' => $this->is_emp,
                'tax' => $this->order['tax'],
                'tax_price' => $this->order['tax_price'],
                
                'freelancer' => @$this->order['freelancer'],
                'employer' => @$this->order['employer'],
                
                'frl_feedback_id' => $this->order['frl_feedback_id'],
                'frl_feedback' => $this->order['frl_feedback'],
                'is_frl_feedback' => !empty($this->order['frl_feedback']),
                'frl_is_good' => ($this->order['frl_rating'] > 0),
                'frl_rating' => intval($this->order['frl_rating']),
                
                'emp_feedback_id' => $this->order['emp_feedback_id'],
                'emp_feedback' => $this->order['emp_feedback'],
                'is_emp_feedback' => !empty($this->order['emp_feedback']),
                'emp_is_good' => ($this->order['emp_rating'] > 0),                
                'emp_rating' => intval($this->order['emp_rating']),
                
                'is_allow_feedback' => $is_allow_feedback,
                'date_feedback' => $date_feedback_formatted,
                
                'order_title' => $this->order['title'],
                'order_price' => $this->order['order_price'],
                'order_days' => $this->order['order_days'],
                
                'pay_type' => $this->order['pay_type'],
                'is_reserve_accepted' => isset($this->order['reserve_data']),
                'reserve_data' => @$this->order['reserve_data'],
                'reserve' => @$this->order['reserve'],
                
                'is_list' => $this->is_list
            ));
        }
        
        public function setIsOwner($is_owner)
        {
            $this->is_owner = $is_owner;
        }

        public function setIsEmp($is_emp)
        {
            $this->is_emp = $is_emp;
        }
        
        public function setOrder($order)
        {
            $this->order = $order;
        }
        
        public function setUser($user)
        {
            $this->order[(($this->is_emp)?'freelancer':'employer')] = $user;
        }
        
        
        public function setEmployer($user)
        {
            $this->order['employer'] = $user;
        }
        
        public function setFreelancer($user)
        {
            $this->order['freelancer'] = $user;
        }

        private function isFrlPhis() 
        {
            return ReservesHelper::getInstance()->isPhisRT($this->order['frl_id']);
        }
        
        protected function getEmpAddress()
        {
            $reqv = ReservesHelper::getInstance()->getUserReqvs($this->order['emp_id']);
            return @$reqv[2]['address'];
        }
}