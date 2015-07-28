<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayout.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayoutPopup.php');

class ReservesHelper 
{
    protected static $instance;
    
    protected $reqs_list;
    protected $is_valids;
    

    protected $payout = null;
    protected $payout_reqv;


    /**
    * —оздаем синглтон
    * @return object
    */
    public static function getInstance() 
    {

        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }
    
    
    
    public function getPayout()
    {
        if(!$this->payout) {
            $this->payout = new ReservesPayout();
        }
        
        return $this->payout;
    }

    
    public function getPayoutReqv($reserve_id)
    {
        if(!isset($this->payout_reqv[$reserve_id])) {
            $this->payout_reqv[$reserve_id] = $this->getPayout()->getPayoutReqv($reserve_id);
        }
        
        return $this->payout_reqv[$reserve_id];
    }

    
    
    public function getPayoutType($reserve_id, $short = false)
    {
        $payout_reqv = $this->getPayoutReqv($reserve_id);
        if(!$payout_reqv) return '';
        
        $text = ($short)?ReservesPayoutPopup::$payments_short_text[$payout_reqv['pay_type']]:
                         ReservesPayoutPopup::$payments_text[$payout_reqv['pay_type']];
        
        return $text;
    }

    


    /**
     * ѕолучить реквизиты юзера
     * даже если их нет
     * 
     * @param int $uid
     * @return array
     */
    public function getUserReqvs($uid = null, $rewrite = false)
    {
        if(!$uid) $uid = get_uid(false);
        
        if(!isset($this->reqs_list[$uid]) || $rewrite) 
        {
            $this->reqs_list[$uid] = sbr_meta::getUserReqvs($uid);
        }
        
        return $this->reqs_list[$uid];
    }
   
    
    
    /**
     * ёзер физик и резидент или беженец?
     * 
     * @param type $uid
     * @return boolean
     */
    public function isPhisRT($uid)
    {
        $reqvs = $this->getUserReqvs($uid);
        if(!$reqvs || !$reqvs['form_type']) return false;
        
        return ($reqvs['form_type'] == sbr::FT_PHYS && 
                in_array($reqvs['rez_type'], array(sbr::RT_RU, sbr::RT_REFUGEE, sbr::RT_RESIDENCE)));
    }

    
    /**
     * ёзер юрик?
     * 
     * @param type $uid
     * @return boolean
     */
    public function isJuri($uid)
    {
        $reqvs = $this->getUserReqvs($uid);
        if (!$reqvs || !$reqvs['form_type']) { 
            return false; 
        }
        
        return $reqvs['form_type'] == sbr::FT_JURI;
    }

    

    public function getNDFL($sum, $uid)
    {
        if (!$this->isPhisRT($uid)) {
            return 0;
        }
        
        return round($sum * 0.13);
    }

    


    /**
     * ѕолучить статус заполнени€ финансов
     * 
     * @param type $uid
     * @return int
     */
    public function getFinStatus($uid = null)
    {
        $reqvs = $this->getUserReqvs($uid);
        if(!$reqvs || !$reqvs['form_type']) return false;
        return (int)$reqvs['validate_status'];
    }

    
    /**
     * ‘инансы отклонены модератором?
     * 
     * @param type $uid
     * @return type
     */
    public function finStatusIsDecline($uid = null)
    {
        return $this->getFinStatus($uid) == sbr_meta::VALIDATE_STATUS_DECLINE;
    }    
    
    
    /**
     * ‘инансы удалены пользователем?
     * 
     * @param type $uid
     * @return type
     */
    public function finStatusIsDeleted($uid = null)
    {
        return $this->getFinStatus($uid) == sbr_meta::VALIDATE_STATUS_DELETED;
    }

    

    /**
     * ‘инансы заблокированы на использоание / редактирование?
     * 
     * @param type $uid
     * @return type
     */
    public function finStatusIsBlocked($uid = null)
    {
        return $this->getFinStatus($uid) == sbr_meta::VALIDATE_STATUS_BLOCKED;
    }

    



    /**
     * ¬ернуть причину блокировки финансов
     * 
     * @param type $uid
     * @return type
     */
    public function getFinBlockedReason($uid)
    {
        //≈сли это отклонение модератором или блокировка 
        //то показываем текст причины
        if ($this->finStatusIsDecline($uid) || 
            $this->finStatusIsBlocked($uid)) {
            
            return sbr_meta::getReqvBlockedReason($uid);
        }
        
        return false;
    }

    
    
    /**
     * —охран€ем текущий URL дл€ возврата из финансов
     * 
     * @param type $is_valid
     */
    public function saveCurrentUrlForFinance($is_valid = false)
    {
        unset($_SESSION['redirect_from_finance']);
        if(!$is_valid && isset($_SESSION['ref_uri'])) {
            $_SESSION['redirect_from_finance'] = $_SESSION['ref_uri'];
        }
    }


    /**
     * ѕроверить наличие финансовой информации
     * 
     * @param type $uid
     * @return boolean
     */
    public function isValidUserReqvs($uid, $is_emp = false)
    {
        if(isset($this->is_valids[$uid])) return $this->is_valids[$uid];
        
        $reqvs = $this->getUserReqvs($uid);
        if(!$reqvs || !$reqvs['form_type']) return false;
        
        $reqv = $reqvs[$reqvs['form_type']];
        
        //@todo: использую существующий метод вместо своего
        $errors = sbr::checkRequired($reqvs['form_type'], $reqvs['rez_type'], $reqv, $is_emp);
        $is_valid = empty($errors);
        
        //≈сли фрилансер физик и не резидент 
        //то провер€ем есть ли скан паспорта
        
        //@todo: это доп.проверка так как на странице финансов это поле об€зательно
        //то возможно данные были заполнены еще до ввода скана в об€заловку
        
        //ѕозже после того как пометим всех нерезидентов у которых нет сканов
        //как не корректные финансы - проверку можно убрать
        if ($is_valid && !$is_emp && 
            $reqvs['form_type'] == sbr::FT_PHYS) {
            
            require_once(ABS_PATH . "/classes/account.php");
            
            $account = new account();
            $account->GetInfo($uid, true);
            $is_valid = $account->isExistAttach();
            
            if (!$is_valid) {
                session::setFlashMessage(account::MSG_UPLOAD_REQ, 'isValidUserReqvs');
            }
        }
        
        
        //если исполнитель беженец то провер€ем действительны ли еще у него документы
        if ($is_valid && !$is_emp && 
            in_array($reqvs['rez_type'], array(sbr::RT_REFUGEE, sbr::RT_RESIDENCE))) {
            
                $is_valid = isset($reqv['idcard_to']) && !empty($reqv['idcard_to'])? 
                        strtotime($reqv['idcard_to']) > strtotime('+ 1 day') : false;
                
                if (!$is_valid) {
                    session::setFlashMessage(account::MSG_UPLOAD_OLD, 'isValidUserReqvs');
                }                
        }
        

        
        /*
        $reqv = array_filter($reqv, function($value){ 
            return $value !== null && !empty($value); 
        });
        
        $valid_keys = array_keys($reqv);
        
        $req_keys_more = array();
        $req_keys = array(
            'fio',
            'birthday',
            'mob_phone'
        );
        
        switch($reqvs['form_type'])
        {
            case sbr::FT_PHYS:
                $req_keys_more = array(
                    'idcard_ser',
                    'idcard',
                    'idcard_by',
                    'address_reg',
                    'address'
                );
            break;
        
            case sbr::FT_JURI:
                $req_keys_more = array(
                    'full_name',
                    'address_jry'
                );
                
                if($reqvs['rez_type'] == sbr::RT_RU)
                {
                    $req_keys_more[] = 'type';
                }
                
            break;
        }
        
        $req_keys = array_merge($req_keys, $req_keys_more);
        $is_valid = (count(array_intersect($req_keys, $valid_keys)) == count($req_keys));
         */
        
        
        $this->is_valids[$uid] = $is_valid;
        return $is_valid;
    }
    
    
    

    public function getAllowedPayoutTypes($form, $rez, $price)
    {
        $payments = array_flip(ReservesPayoutPopup::$payment_list);
        
        $code = $form . $rez;
        
        switch($code)
        {
            case sbr::FT_PHYS . sbr::RT_REFUGEE:
            case sbr::FT_PHYS . sbr::RT_RESIDENCE:
                unset($payments[ReservesPayoutPopup::PAYMENT_TYPE_CARD]);
                
                
            case sbr::FT_PHYS . sbr::RT_RU:

                
                unset($payments[ReservesPayoutPopup::PAYMENT_TYPE_BANK]);
                
                if($price <= ReservesPayout::MAX_SUM)
                {
                   unset($payments[ReservesPayoutPopup::PAYMENT_TYPE_CARD],
                         $payments[ReservesPayoutPopup::PAYMENT_TYPE_RS]); 
                }

                break;
                
                
            case sbr::FT_PHYS . sbr::RT_UABYKZ:
                
                unset($payments[ReservesPayoutPopup::PAYMENT_TYPE_RS], 
                      $payments[ReservesPayoutPopup::PAYMENT_TYPE_CARD]);
                
                if($price <= ReservesPayout::MAX_SUM)
                {
                    unset($payments[ReservesPayoutPopup::PAYMENT_TYPE_BANK]);
                }
                
                break;
            
                
            default:
                unset($payments[ReservesPayoutPopup::PAYMENT_TYPE_RS],
                      $payments[ReservesPayoutPopup::PAYMENT_TYPE_CARD],
                      $payments[ReservesPayoutPopup::PAYMENT_TYPE_YA]);
        }
        
        //@todo: выключаем выплату на счет, пока яƒ решает проблему
        unset($payments[ReservesPayoutPopup::PAYMENT_TYPE_RS]);
        
        return $payments;
    }
    
    
    
    
}