<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/CModel.php');
require_once('GuestConst.php');
require_once('GuestInviteModel.php');


class GuestActivationModel extends CModel
{
    const SOLT = 'XPmyTlNrneYgoe3';
    
    static public $TABLE = 'activate_service';
    
    const REDIRECT_AUTH_FAIL = '/registration/';
    
    protected $data = array();

    
    
    
    /**
     * јктиваци€ и публикаци€ сразу по данным из мастера
     * 
     * @param type $uid
     * @param type $email
     * @param type $type
     * @return boolean
     */
    public function published($uid, $email, $type = GuestConst::TYPE_PROJECT)
    {
        if (!isset($_SESSION['customer_wizard'])) {
            return false;
        }
        
        unset($_SESSION['customer_wizard']['filled']);
        
        $data['user_id'] = $uid;
        $data['email'] = $email;
        $data['type'] = $type;   
        $data['dataForm'] = $_SESSION['customer_wizard'];

        $code = GuestActivationModel::model()->newActivation($data);

        if ($code) {
            $redirect_to = GuestActivationModel::model()->doActivation($code);
        }
        
        unset($_SESSION['customer_wizard']);
        
        $_SESSION['was_customer_wizard'] = true;
        
        return $redirect_to;
    }
    



    public function newActivation($data)
    {
        $data['code'] = md5( self::SOLT . serialize($data) . uniqid(mt_rand(), TRUE) );
        
        //≈сли есть ссылка то фискируем приглашение
        if (isset($data['dataForm']['link'])) {
            $guestInviteModel = new GuestInviteModel();
            $data['invite_id'] = $guestInviteModel->addInvite(
                    $data['type'], 
                    $data['email'], 
                    $data['dataForm']['link']);
            
            unset($data['dataForm']['link']);
        }
        
        if (isset($data['dataForm'])) {
            $data['data'] = serialize($data['dataForm']);
            unset($data['dataForm']);
        }
        
        $code = $this->db()->insert(self::$TABLE, $data, 'code');
        
        if ($code) {
            $this->data = $data;
        }
        
        return $code; 
    }
    
    
    
    public function doActivation($code)
    {
        $activation_data = $this->getActivation($code);
        if (!$activation_data) {
            return false;
        }
        $this->deleteActivation($code);
        
        $current_uid = get_uid(false);
        if ($current_uid) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
            $user = new employer();
            $user->GetUserByUID($current_uid);
            $status = 1;
        } else {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
            $registration = new registration();
            $user_data = $registration->autoRegistationAndLogin(array(
                //≈сли есть можно просто авторизовать юзера
                'uid' => $activation_data['user_id'],
                'role' => 1,//заказчик!
                //об€зательное поле это email
                'email' => $activation_data['email'],
                'uname' => $activation_data['uname'],
                'usurname' => $activation_data['usurname']
            )); 
            
            
            if (!$user_data || !$user_data['ret']) {
                return self::REDIRECT_AUTH_FAIL;
            }

            $status = $user_data['ret'];
            $user = $user_data['user'];
        }        
        
        $uid = $user->uid;
        
        $redirect = false;
        $data = $activation_data['data'];
        
        switch ($activation_data['type']) {
            
            case GuestConst::TYPE_PERSONAL_ORDER:
                
                $data['emp_id'] = $uid;
                
                require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
                $orderModel = TServiceOrderModel::model();
                
                if ($order = $orderModel->createPersonal($data)) {
                    $tservices_smail = new tservices_smail();
                    $tservices_smail->newOrder($order);
                    $redirect = sprintf(tservices_helper::url('order_card_url'), $order['id']);
                }
                break;
                
                
            case GuestConst::TYPE_VACANCY:    
                
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                
                $data['state'] = projects::STATE_MOVED_TO_VACANCY;
                $date_public = null;
                
                $redirect_layout = "/public/?step=1&kind=4&public=%s&popup=1";
               
                
            case GuestConst::TYPE_PROJECT:
                
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                
                $key = md5(uniqid($uid));
                $tmpPrj = new tmp_project($key);
                $tmpPrj->initForUser($user);
                
                if (isset($data['IDResource']) && !empty($data['IDResource'])) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
                    
                    $uploader = new uploader($data['IDResource']);
                    $attachedfiles_files = $uploader->getFiles();
                    $tmpPrj->clearAttaches();
                    $tmpPrj->addAttachedFiles($attachedfiles_files, false);
                    $uploader->clear();
                }
                
                if($prj = $tmpPrj->addSimpleProject($data)) {
                    $_SESSION['new_public'] = 1;
                    $redirect = getFriendlyURL('project', $prj);
                    
                    if (isset($redirect_layout)) {
                        $redirect = sprintf($redirect_layout, $prj['id']);
                    }
                    
                    $src_id = $prj['id'];
                    
                    //≈сли проект был создан при переходе с лендинга 
                    //то прив€зываем его дл€ статистики
                    if (isset($data['landingProjectId'])) {
                        require_once(ABS_PATH . '/classes/LandingProjects.php');
                        LandingProjects::model()->linkWithProject(
                                $data['landingProjectId'], 
                                $src_id,
                                !$activation_data['user_id']);
                    }
                }
                
                break;
        }
        
        
        //ќбновл€ем приглашение
        if (isset($activation_data['invite_id']) && 
            $activation_data['invite_id'] > 0) {

            $guestInviteModel = new GuestInviteModel();
            $guestInviteModel->updateDateComeInvite($activation_data['invite_id'], array(
                'src_id' => isset($src_id)?$src_id:null,
                'date_public' => isset($date_public)?$date_public:null
            ));
        }
        
        
        //ћессага с парол€ми дл€ новеньких
        if (!$activation_data['user_id'] && !$current_uid) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/SubBarNotificationHelper.php");
            SubBarNotificationHelper::getInstance()->setMessage(
                $activation_data['type'],
                array('login' => $user->login,'password' => $user->passwd), 
                $user->uid);
        } elseif ($status == users::AUTH_STATUS_2FA) {
            $_SESSION['ref_uri'] = $redirect;
            $redirect = '/auth/second/';
        }
        
        
        return $redirect;
    }

    


    public function deleteActivation($code)
    {
        $is_done = $this->db()->val("
            DELETE FROM ".self::$TABLE." WHERE code = ?",$code);
        
        if ($is_done) {
            $this->data = array();
        }
        
        return $is_done;
    }



    public function getActivation($code)
    {
        if(empty($code)) return false;

        $row = $this->db()->row("
            SELECT *
            FROM ".self::$TABLE." 
            WHERE code = ? 
            LIMIT 1
        ", $code);

        if ($row) {
            $row['data'] = ($row['data'])?mb_unserialize($row['data']):array();
            $this->data = $row;
        }

        return $row;         
    }
    
    
    
}
