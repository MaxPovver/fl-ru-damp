<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesTaxes.php');

/**
 * Class TServiceOrderPopup
 * Âèäæåò ïîêàçûâàåò ïîïàï ïğè çàêàçå ÒÓ
 */

class TServiceOrderPopup extends CWidget 
{
        public $data = array();
        public $is_emp;
        public $is_auth;

        
        /**
         * Èíèöèàëèçàöèÿ ïîïàïà äàííûìè èç êàğòî÷êè ÒÓ
         * 
         * @param type $data
         */
        public function init($data = array()) 
        {
            parent::init();
            if(!empty($data)) $this->data = $data;
            $this->is_emp = is_emp();
            $this->is_auth = (get_uid(false) > 0);
        }

        
        /**
         * Ìåòîä ñğàçó ïå÷àòàåò â ïîòîê îêîøêî ïîïàïà
         * ñì render
         * 
         * @return boolean
         */
        public function run() 
        {
            //Äëÿ ôğèëàíñåğà íåíóæåí ïîïàï
            if($this->is_auth && !$this->is_emp) return false;
            
            $is_emp = $this->is_emp && $this->is_auth;
            $is_allowOrderReserve = tservices_helper::isAllowOrderReserve($this->data['category_id']);
            
            //Äëÿ àíîíèìóñà è çàêàç÷èêà ïîêàçûâàåì ñîîòâåòñòâóşùèé ïîïàï ñ ó÷åòîì äîñòóïà
            $sufix = ($is_emp)?'emp':'reg';
            //Çàäåéñòâóåì äëÿ ıòîãî şçåğà è êàòåãîğèè ÒÓ íîâóş ÁÑ ñ ğåçåğâîì èëè íåò
            $sufix .= (($is_allowOrderReserve)?'-reserve':'');
            
            if($is_emp && $is_allowOrderReserve)
            {
                $reservesTaxes = ReservesTaxes::model();
                $this->data['reserveTax'] = $reservesTaxes->getTax($this->data['price'], true);
                $this->data['priceWithTax'] = $reservesTaxes->calcWithTax($this->data['price']);
                $this->data['reserveAllTaxJSON'] = json_encode($reservesTaxes->getList());
            }
            
            $this->render("t-service-order-popup-{$sufix}", $this->data);
	}
}