<?php

/**
 * Класс для регистрации пользователей
 *  
 */
class registration
{
    /**
     * Название действия регистрации 
     */
    const ACTION_REGISTRATION = 'registration';
    
    const ACTION_SEND_MAIL    = 'resend_mail';
    
    const ACTION_SAVE_INFO    = 'save_info';
    
    const ACTION_ACTIVATE     = 'activate_account';
    
    const ACTION_GETSMS       = 'getsms';
    
    const ACTION_STEP1        = 'step1';
    
    
    
    
    
    public $error = array();
    
    public $errno = array();
    
    /**
     * Роль фрилансера 
     */
    const ROLE_FREELANCER = 1;
    /**
     * Роль работодателя 
     */
    const ROLE_EMPLOYER   = 2;
    
    
    public $access_action_page = array (
        //'public' => array('confirm' => 'Чтобы создать проект, укажите личную информацию.',
        //                  'confirm_frl' => 'Чтобы опубликовать предложение, укажите личную информацию.'),
        'norisk2_create' => array('confirm' => 'Чтобы работать через Безопасную Сделку, укажите личную информацию.',
                          'confirm_frl' => 'Чтобы работать через Безопасную Сделку, укажите личную информацию.')
    );
    
    public $access_action = array (
        //'action'         => array('page'    => 'public', 
        //                          'confirm' => 'Чтобы создать проект, укажите личную информацию.'),
        //'new_tr'         => array('page'    => 'blogs',
        //                          'confirm' => 'Чтобы написать сообщение в блогах, укажите личную информацию.'),
        //'post_msg'       => array('page'    => 'blogs',
        //                          'confirm' => 'Чтобы написать сообщение в блогах, укажите личную информацию.'),
        //'add'            => array('page'    => 'projects',
        //                          'confirm' => 'Чтобы ответить на проект, укажите личную информацию.'),
        //'do.Create.post' => array('page'    => 'commune',
        //                          'confirm' => 'Чтобы написать сообщение в сообществах, укажите личную информацию.'),
        //'add_comment'    => array('page'    => true,
        //                          'confirm' => 'Чтобы написать комментарий, укажите личную информацию.'),
        'agree'          => array('page'    => 'norisk2',
                                  'confirm' => 'Чтобы работать через Безопасную Сделку, укажите личную информацию.'),
        //'look-contacts'  => array('page'    => 'contacts',
        //                          'confirm' => 'Чтобы просматривать сообщения, заполните личную информацию.'),
    );
    
    
    
    protected $_fields;
    
    
    /**
     * Следующий шаг регистрации
     * 
     * @var type 
     */
    protected $_next_action = self::ACTION_STEP1;


    
    /**
     * Редиректить на страницу если емыло уже используется
     * иначе выводим сообщение об этом.
     * 
     * @var type 
     */
    protected $_disable_email_redirect = false;



    
    public function setDisableEmailRedirect($value = true) 
    {
        $this->_disable_email_redirect = $value;
    }


    public function getNextAction()
    {
        return $this->_next_action;
    }

    
    public function saveFields()
    {
        if ($this->is_validate) {
            $_SESSION['registration_fields'] = $this->_fields;
            return true;
        }
        
        return false;
    }


    public function restoreFields()
    {
        if (isset($_SESSION['registration_fields']) && 
            is_array($_SESSION['registration_fields'])) {
            
            $this->_fields = $_SESSION['registration_fields'];
            return true;
        }
        
        return false;
    }
    



    public function setFieldInfo($name, $value) {
        $this->_fields[$name] = $value;
    }
    
    
    public function unsetField($name, $clear = FALSE)
    {
        if($clear) $this->_fields[$name] = NULL; 
        else unset($this->_fields[$name]);
    }

        /**
     * Метод доступа к переменным 
     *  
     * @param string $name    Имя переменной
     * @return mixed Данные переменной 
     */
    public function __get($name) {
        if(!isset($this->_fields)) return $this->{$name};
        if (array_key_exists($name, $this->_fields)) {
            return $this->_fields[$name];
        } else {
            return $this->{$name};
        }
    }
    
    public function listenerAction($action) 
    {
        switch($action) {
            case self::ACTION_STEP1:
                $this->actionStep1();
                break;

            case self::ACTION_REGISTRATION:
                $this->actionRegistrationFromWizard();
                //$this->actionRegistration();
                break;
            
            case self::ACTION_ACTIVATE:
                $this->actionActivate();
                break;
            
            case self::ACTION_SEND_MAIL:
                $this->actionSendMail();
                break;
            
            case self::ACTION_SAVE_INFO:
                $this->actionSaveInfo();
                break;
            
            case self::ACTION_GETSMS:
                $this->actionSendSms();
                break;
            
            default:
                break;
        }
    }
    
    
    
    
    
    /**
     * Подготовка и сохранение данных для последующей регистрации
     */
    public function actionStep1()
    {
        $role = __paramInit('int', null, 'role', self::ROLE_FREELANCER);

        if($role == self::ROLE_EMPLOYER) {
            require_once(ABS_PATH . "/classes/employer.php");
        } else {
            require_once(ABS_PATH . "/classes/freelancer.php");
            $role = self::ROLE_FREELANCER;
        }
        $this->setFieldInfo('role', $role);
        
        $email = trim(__paramInit('string', null, 'email'));
        $this->fillData(array('email' => $email), true);
        
        $this->setFieldInfo('email', $email);
        $this->setFieldInfo('subscr_news', trim(__paramInit('bool', null, 'subscribe')));
        // пароль берем напрямую из $_POST, а то __paramInit режет спецсимволы (пароль хешируется - SQL инъекция невозможна)
        $this->setFieldInfo('password', stripslashes($_POST['password']));
        
        //Выключаем редирект если мыло уже существует
        $this->setDisableEmailRedirect(true);
        
        //Проверка полей
        $this->checkedFields();

        //Проверка каптчи
        $this->setFieldInfo('captchanum', __paramInit('string', null, 'captchanum'));
        $num = __paramInit('string', null, 'rndnum');
        if ( !$_SESSION["regform_captcha_entered"] ) {
            $_SESSION['reg_captcha_num'] = $this->captchanum;
            $captcha = new captcha($this->captchanum);
            if (!$captcha->checkNumber($num)) {
                $this->error['captcha'] = 'Неверный код. Попробуйте еще раз';
                $this->is_validate = false;            
                unset($_SESSION['reg_captcha_num']);
            }
        }        
        

        if($this->is_validate) {
            $this->_next_action = self::ACTION_REGISTRATION;
            return $this->saveFields();
        }
        
        return $this->error;
    }

    
    
    
    public function actionRegistrationFromWizard()
    {
        $is_preset = $this->restoreFields();
        $this->setFieldInfo('login', trim(__paramInit('string', null, 'login')));
        $this->checkedFields();
        
        $result = $this->actionRegistration($is_preset);
        if (isset($result['success']) && 
            $result['success'] === true) {
            
            unset($_SESSION['registration_fields']);
            $redirect_to = $result['redirect'];

            header("Location: {$redirect_to}");
            exit;
        }
        
        $this->_next_action = $is_preset? self::ACTION_REGISTRATION:self::ACTION_STEP1;
    }

    



    public function actionActivate() {
        define('IS_USER_ACTION', 1);
        $redirect = '/';
        $subscr_news = isset($_SESSION['subscr_news']) ? (int)$_SESSION['subscr_news'] : 1;
        logout();
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/activate_code.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/SubBarNotificationHelper.php");
        $act = new activate_code;
        $activated = $act->Activate($this->code, $login, $pass);
        if ($activated === 1) {
            $uid = login($login, $pass, 0, true);
            
            if (!is_emp()) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
                $freelancer = new freelancer();
                $freelancer->UpdateSubscr($uid, 1, array(), 1, 1, 1, 1, 1, $subscr_news, 1, 1, 1, 1, 1, 1, 1, true, 1);
                if ($redirect == '/') $redirect = '/projects/';
            }
            
            $_SESSION['link_back'] = "/registration/wellcome/" . (is_emp() ? "employer.php" : "freelancer.php");
            // факт того, что пользователь только что зарегестрировался (сбрасывается на страницах wellcome)
            $_SESSION['is_new_user'] = 1;
            unset($_SESSION['activate_resend_attempts']);
            
            SubBarNotificationHelper::getInstance()->setMessage(
                SubBarNotificationHelper::TYPE_USER_ACTIVATED,
                array(), 
                $uid
            );

            header("Location: ".$redirect);
            exit();
        } else {
            $this->error['rndnum'] = 'Ошибка активации. Попробуйте еще раз';
        }

    }
    
    /**
     * Заполнение обязательных полей после регистрации через API мобильного приложения.
     * 
     * @param  array $aParams массив входящих данных
     * @return bool true - успех, false - провал
     */
    public function actionSaveInfoMobile( $aParams = array() ) {
        $this->setFieldInfo( 'uname', __paramValue('string', iconv('utf-8', 'cp1251', $aParams['first_name'])) );
        $this->setFieldInfo( 'usurname', __paramValue('string', iconv('utf-8', 'cp1251', $aParams['last_name'])) );
        $this->setFieldInfo( 'birthday', __paramValue('string', $aParams['birthday']) );
        $this->setFieldInfo( 'country', __paramValue('int', $aParams['country_id']) );
        $this->setFieldInfo( 'city', __paramValue('int', $aParams['city_id']) );
        $this->setFieldInfo('info_for_reg', array('birthday' => 0, 'sex' => 0, 'country' => 0, 'city'=> 0) );
        $gender = __paramValue('int', $aParams['gender']);
        $this->setFieldInfo( 'sex', ( $gender == 1 ? 't' : ( $gender == 2 ? 'f' : NULL) ) );
        $this->checkedFields();
        
        if ( date('Y-m-d', strtotime($aParams['birthday'])) != $aParams['birthday'] ) {
            $this->error['birthday'] = 'Укажите некорректную дату дня рождения'; 
            $this->errno['birthday'] = 2;
        }
        
        if ( empty($this->error) ) {
            if ( !is_emp() ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php' );
                $user = new freelancer();
            } else {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/employer.php' );
                $user = new employer();
            }
            
            $user->info_for_reg = serialize( $this->info_for_reg );
            $user->uname        = $this->uname;
            $user->usurname     = $this->usurname;
            $user->sex          = $this->sex;
            $user->birthday     = $this->birthday;
            $user->country      = $this->country;
            $user->city         = $this->city;
            
            if ( !is_emp() ) {
                $spec = intvalPgSql( $aParams['prof_id'] );
                
                if ( $spec ) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
                    
                    $or_spec  = professions::GetProfessionOrigin( $spec );
                    $spec_mod = professions::getLastModifiedSpec( $_SESSION['uid'] );
                    
                    if ( !is_pro() &&  $spec_mod['days'] > 0 ) {
                        $this->error['spec'] = 'Не прошло 30 дней с момента последней смены специализации';
                        $this->errno['spec'] = 2;
                    }
                    else {
                        $user->spec = $spec;
                        $user->spec_orig = $or_spec;
                        
                        professions::setLastModifiedSpec( $_SESSION['uid'], $spec );
                    }
                }
                else {
                    $this->error['spec'] = 'Не указан параметр ID профессии';
                    $this->errno['spec'] = 1;
                }
            }
            
            if ( empty($this->error) ) {
                if ( $sError = $user->Update($_SESSION['uid'], $res) ) {
                    $this->error['save'] = $sError;
                }
                else {
                    if ( !is_emp() ) {
                        $_SESSION['specs'] = $user->GetAllSpecs( $_SESSION['uid'] );
                    }
                }
            }
            
            if ( empty($this->error['save']) ) {
                $_SESSION['check_user_access'] = true;
            }
        }
        
        return empty( $this->error );
    }
    
    /*
	 *Вызывается при сохранении данных со страницы registration/info.php
	 * 
	 * */
    public function actionSaveInfo() {
        $bday = __paramInit('int', null, 'bday', null);
        $bmonth = __paramInit('int', null, 'bmonth_db_id', 1);
        $byear  = __paramInit('int', null, 'byear', null);
        $ukey   = __paramInit('string', 'ukey', null);

        if($bday != null && $byear != null) {
            if (!is_numeric($bday) || !is_numeric($byear) || !checkdate($bmonth, $bday, $byear) || $byear < 1945 || $byear > date('Y')) {
                $this->error['birthday'] = "Поле заполнено некорректно";
            } else {
                $birthday = dateFormat("Y-m-d", $byear."-".$bmonth."-".$bday);
            }
        } else {
            $birthday = "1910-01-01";
        }
        
        $info_for_reg = $_POST['info_for_reg'];
        $info_for_reg = array_map('intval', $info_for_reg);
        $this->setFieldInfo('uname',    __paramInit('string', null, 'uname'));
        $this->setFieldInfo('usurname', __paramInit('string', null, 'usurname'));
        $this->setFieldInfo('birthday', $birthday);
        $this->setFieldInfo('country', __paramInit('string', null, 'country_db_id'));
        $this->setFieldInfo('country_name', __paramInit('string', null, 'country'));
        $this->setFieldInfo('city', __paramInit('string', null, 'city_db_id'));
        $this->setFieldInfo('city_name', __paramInit('string', null, 'city'));
        $this->setFieldInfo('sex', __paramInit('string', null, 'sex', 1)); // по умолчанию мужской пол
        $this->setFieldInfo('info_for_reg', $info_for_reg);
        $this->checkedFields();
        
        if($this->is_validate) {
            if (!is_emp()) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
                $user = new freelancer();
            } else {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
                $user = new employer();
            }
            
            $user->info_for_reg = serialize($this->info_for_reg);
            $user->uname        = $this->uname;
            $user->usurname     = $this->usurname;
            $user->sex          = $this->sex;
            $user->birthday     = $this->birthday;
            $user->country      = $this->country;
            $user->city         = $this->city;
            
            $this->error['save'] = $user->Update($_SESSION['uid'], $res);
            
            if (!$ukey) {
                if (get_uid(0) && is_emp()) {
                    header("Location: /registration/welcome_employer.php");
                } elseif (get_uid(0) && !is_emp()) {
                    header("Location: /registration/welcome_free-lancer.php");
                }
                exit;
            } elseif (!$this->error['save']) {
                $_SESSION['check_user_access'] = true;
                $this->action_form = $_SESSION['action_form'][$ukey];
                
                if(count($_SESSION['post_cache'][$ukey]) > 0) {
                    $this->is_post = true;
                    $this->generate_post = $_SESSION['post_cache'][$ukey];
                    if(count($_SESSION['files_cache'][$ukey])) {
                        $this->generate_files = $_SESSION['files_cache'][$ukey];
                    }
                } else {
                    header("Location: {$this->action_form}");
                    exit;
                    
                }    
            }
        }
    }
    /**
     * Отправить пользователю код для подтверждения телефона
    */
    public function actionSendSms( $echo = true, $fields_are_set = false ) {
        if($_SESSION['send_sms_time'] > time()) {
            return;
        }
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/captcha.php";
        session_start();
        $this->setFieldInfo('captchanum', __paramInit('string', null, 'captchanum'));
        $num = __paramInit('string', null, 'rndnum');
        $_SESSION['reg_captcha_num'] = $this->captchanum;
        $captcha = new captcha($this->captchanum);
        if (!$captcha->checkNumber($num)) {
            unset($_SESSION['reg_captcha_num']);
            unset($_SESSION["regform_captcha_entered"]);
            echo json_encode(array("err_msg" => iconv("WINDOWS-1251", "UTF-8//IGNORE", "Вы ввели неверный код."), "target"=>"captchanum"));
            exit;
        } else {
             $_SESSION["regform_captcha_entered"] = true;
        }
        
    	$code = rand(1000, 9999);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate_a1.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/sbr_meta.php";
        $phone = $fields_are_set ? $this->phone : trim(__paramInit('string', null, 'phone'));
        $phone = $_SESSION["reg_phone"] = preg_replace("#[\D]#", "", $phone);
        $sms = new sms_gate_a1($phone);
        $sRole = $fields_are_set ? ( $this->role == 2 ? 'emp' : 'frl' ) : __paramInit('string', null, 'role');
        
        if ( (is_release() || $phone != 71111112222) && sbr_meta::findSafetyPhone($phone, $sRole) ) {
            if ( $echo ) {
                echo json_encode(array("err_msg" => iconv("WINDOWS-1251", "UTF-8//IGNORE", "Пользователь с таким номером уже зарегистрирован")));
                exit;
            }
            else {
                $this->error['phone'] = 'Пользователь с таким номером уже зарегистрирован';
                $this->errno['phone'] = 3;
            }
        }
        $msg = "Ваш код активации на free-lance.ru \n{$code}";
        $count = "5";
        if (is_release() || $phone != 71111112222) {
            $id = $sms->sendSMS($msg, "text", null, true); //$code = 7777; // !!
            $message = $sms->getLimitMessage($count);
        } else {
        	$code = 7777;
        	$id = 123;
        }
        if ((int)$id > 0) {
            $_SESSION['send_sms_time'] = strtotime('+1 min');
            $_SESSION["reg_sms_isnn"] = $sms->getISNN();
            $_SESSION["reg_sms_data"] = $msg;
            $_SESSION["reg_sms_date_send"] = date('Y-m-d H:i:s');
            $_SESSION['smsIsRequested'] = 1;
            $_SESSION['smsCode'] = $code;
            
            if ( $echo ) {
                echo json_encode(array("mid" => $id, "count" => $count, "message" => iconv("WINDOWS-1251", "UTF-8//IGNORE", $message), "LIMIT" => sms_gate::SMS_ON_NUMBER_PER_24_HOURS) );
                exit;
            }
        } else {
        	$error_text = "Не удалось отправить сообщение. Попробуйте через несколько минут.";
        	$target = "";
        	if ( $sms->limitIsExceed() ) {
                $error_text = sms_gate::LIMIT_EXCEED_LINK_TEXT;
                $target = "limit_exceed";
        	}
            if ( $echo ) {
                echo json_encode(array("err_msg" => iconv("WINDOWS-1251", "UTF-8//IGNORE", $error_text), "target"=>$target) );
                exit;
            } else {
                $this->error['actionSendSms'] = $error_text;
            }
        }
        
        return $id;
    }
    
    /**
     * Генерируем форму для отправки
     * 
     * @param string $ukey    Ключ данных @see self::genUkeyPost();
     * @return string 
     */
    public function generateFormPost($ukey) {
        $form = "<form action='{$this->action_form}' method='POST' id='form_$ukey'>\r\n";
        foreach($this->generate_post as $name => $value) {
            $form .= $this->generateInput($name, $value);
        }
        $form .= "</form>\r\n";
        
        return $form;
    }
    
    /**
     * Генерируем инпуты
     * 
     * @param type $name    Название
     * @param type $value   Значение
     * @return type 
     */
    public function generateInput($name, $value) {
        if(is_array($value)) {
            $result = "";
            foreach($value as $nm=>$val) {
                $nm = $name ."[$nm]";
                $result .= $this->generateInput($nm, $val);
            }
            return $result;
        } else {
            if(is_string($value) && $value != '') {
                $value = stripslashes($value);
                $value = htmlspecialchars($value, ENT_QUOTES);
            }
            return "<input type='hidden' name='{$name}' value='{$value}'>\r\n";
        }
    }
    
    /**
     * Очищаем данные которые будем посылать
     * 
     * @param type $ukey ключ данных
     */
    public function clearPostForm($ukey) {
        unset($_SESSION['post_cache'][$ukey], $_SESSION['files_cache'][$ukey], $_SESSION['action_form'][$ukey]);
        
    }
    
    public function actionSendMail($redirect = true) {
        global $DB;
        if(empty($_SESSION['email']) && $_SESSION['suspect'] == true) return false;
        
        $attemps = isset($_SESSION['activate_resend_attempts']) ? $_SESSION['activate_resend_attempts'] : 5;
        $password = isset($_SESSION['activate_password']) ? $_SESSION['activate_password'] : false;
        if ($attemps == 0) {
            unset($_SESSION['activate_password']);
            return false;
        }
        
        $user = $DB->row("SELECT login, uid FROM users WHERE lower(email) = ?", $_SESSION['email']);
        
        if($user['uid'] > 0) {
            $_SESSION['activate_resend_attempts'] = $attemps - 1;
            
            require_once( $_SERVER['DOCUMENT_ROOT'] . "/classes/activate_code.php");
            require_once( $_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
            $smail    = new smail();
            $code     = activate_code::getActivateCodeByUID($user['uid']);
            $smail->NewUser($user['login'], $password, $code);

            if($redirect && !defined("NEO")) {
                header("Location: /registration/complete.php");
                exit;
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Регистрация через API мобильного приложения. Выслать SMS еще раз.
     * 
     * @param  array $aParams массив входящих данных
     * @return bool true - успех, false - провал
     */
    public function actionResendSmsMobile( $aParams = array() ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php' );
        
        $this->setFieldInfo( 'login',    trim(__paramValue('string', $aParams['username'])) );
        $this->setFieldInfo( 'phone',    trim(preg_replace("#[\D]#", "", __paramValue('string', $aParams['phone']))) );
        $this->checkedFields( true );
        
        if ( (is_release() || $this->phone != 71111112222) && sbr_meta::findSafetyPhone($this->phone, $this->role == 2 ? 'emp' : 'frl') ) {
            $this->error['phone'] = 'Пользователь с таким номером уже зарегистрирован';
            $this->errno['phone'] = 3;
        }
        
        if ( $this->login != $_SESSION['api_reg_login'] ) {
            $this->error['login'] = 'Вы указали не этот логин при регистрации';
            $this->errno['login'] = 6;
        }
        
        if ( empty($this->error) ) {
            $_SESSION['send_sms_time']           = 0;
            $_SESSION['regform_captcha_entered'] = true;

            $nCode = $this->actionSendSms( false, true );
        }
        
        return ( empty($this->error) && !empty($nCode) );
    }
    
    /**
     * Регистрация через API мобильного приложения. Начало.
     * 
     * @param  array $aParams массив входящих данных
     * @return bool true - успех, false - провал
     */
    public function actionRegistrationMobile( $aParams = array() ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php' );
        
        $this->setFieldInfo( 'role',     __paramValue('int', $aParams['role']) );
        $this->setFieldInfo( 'login',    trim(__paramValue('string', $aParams['username'])) );
        $this->setFieldInfo( 'email',    trim(__paramValue('string', $aParams['email'])) );
        $this->setFieldInfo( 'phone',    trim(preg_replace("#[\D]#", "", __paramValue('string', $aParams['phone']))) );
        $this->setFieldInfo( 'password', stripslashes($aParams['password']) );
        $this->checkedFields( true );
        
        if ( !$this->role ) {
            $this->error['role'] = 'Не указан параметр - Роль пользователя';
            $this->errno['role'] = 1;
        }
        elseif ( $this->role > 2 ) {
            $this->error['role'] = 'Ошибочный параметр - Роль пользователя';
            $this->errno['role'] = 2;
        }
        
        if ( (is_release() || $this->phone != 71111112222) && sbr_meta::findSafetyPhone($this->phone, $this->role == 2 ? 'emp' : 'frl') ) {
            $this->error['phone'] = 'Пользователь с таким номером уже зарегистрирован';
            $this->errno['phone'] = 3;
        }
        
        if ( empty($this->error) ) {
            $_SESSION['api_reg_role']   = $this->role;
            $_SESSION['api_reg_login']  = substr($this->login, 0, 15);
            $_SESSION['api_reg_email']  = substr($this->email, 0, 64);
            $_SESSION['api_reg_passwd'] = substr($this->password, 0, 24);
            $_SESSION['api_reg_phone']  = $this->phone;
            
            // отправляем смс для подтверждения телефона
            $_SESSION['send_sms_time'] = 0;
            $_SESSION['regform_captcha_entered'] = true;

            $nCode = $this->actionSendSms( false, true );
        }
        
        return ( empty($this->error) && !empty($nCode) );
    }
    
    /* Регистрация через API мобильного приложения. Подтверждение.
     * 
     * @param  array $aParams массив входящих данных
     * @return users объект или null в случае провала
     */
    public function actionRegistrationMobileComplete( $aParams = array() ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/wizard/wizard.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php' );
        
        $this->setFieldInfo( 'login',    trim(__paramValue('string', $aParams['username'])) );
        $this->setFieldInfo( 'phone',    trim(preg_replace("#[\D]#", "", __paramValue('string', $aParams['phone']))) );
        $this->setFieldInfo( 'smscode',  __paramValue('int', $aParams['code']) );
        $this->setFieldInfo( 'role',     $_SESSION['api_reg_role'] );
        $this->setFieldInfo( 'email',    $_SESSION['api_reg_email'] );
        $this->setFieldInfo( 'password', $_SESSION['api_reg_passwd'] );
        $this->checkedFields( false );
        
        if ( $this->login != $_SESSION['api_reg_login'] ) {
            $this->error['login'] = 'Вы указали не этот логин при регистрации';
            $this->errno['login'] = 6;
        }
        
        if ( empty($this->error) ) {
            $sClassName = $this->role == self::ROLE_EMPLOYER ? 'employer' : 'freelancer';
            
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/'. $sClassName .'.php' );
            
            $newuser         = new $sClassName();
            $newuser->role   = $this->role == self::ROLE_EMPLOYER ? 1 : 0;
            $newuser->login  = substr($this->login, 0, 15);
            $newuser->email  = substr($this->email, 0, 64);
            $newuser->passwd = substr($this->password, 0, 24);
            $newuser->uid    = $newuser->Create( $rerror, $error );
            
            if ( $newuser->uid && !$error ) {
                users::isSuspiciousUser($newuser->uid, $newuser->login, '', '', '', '', '');
                $this->checkGrayIp( $newuser );
                
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate.php";
                $phone = '+' . preg_replace("#^\+#", "", $_SESSION["reg_phone"]);
                unset($_SESSION["regform_captcha_entered"]);
                unset($_SESSION["reg_phone"]);
                unset($_SESSION['send_sms_time']);
                sms_gate::saveSmsInfo($phone, $_SESSION["reg_sms_isnn"], $_SESSION["smsCode"], $_SESION["reg_sms_date_send"], $newuser->uid);
                $_SESSION['email'] = $newuser->email;
                $_SESSION['rrole'] = $_SESSION['api_reg_role'];
                // Если пришли сюда регистрироватся то после нажатия кнопки регистрации удаляем куки регистрации иначе после активации нас перекинет на мастер
                $wizard = new wizard();
                $wizard->clearCookiesById($newuser->role == 1 ? 1 : 2); // В зависимоти от того кого регистрируем
                // На всякий случай при новой регистрации удаляем переменную проверки
                self::resetCheckAccess();

                //Обработать действия по событию успешной регистрации
                $this->afterSuccessRegistation($newuser);
                
                return $newuser;
            }
            else {
                if ( $error['exceed_max_reg_ip'] == 1 ) {
                    $this->error['exceed_max_reg_ip'] = 'Превышено количество регистраций с одного IP';
                }
            }
        }
        
        return null;
    }

    public function actionRegistrationOpauth($data)
    {
        $this->setFieldInfo('role', (int)$data['role']);
        $this->setFieldInfo('login', trim($data['login']));
        $this->setFieldInfo('email', trim($data['email']));
        
        //Кастомная валидация для этого способа регистрации
        $this->is_validate = true;
        
        $users = new users();
        $users->GetUser($data['email'], true, true);
        
        if ($users->uid) {
            $this->error['email'] = "Пользователь с таким email-адресом существует.";
            $this->is_validate = false;
        }
        
        $users = new users();
        $users->GetUser($data['login'], true, false);
        if ($users->uid) {
            $this->error['login'] = "Логин {$data['login']} занят. Введите другой логин для регистрации на сайте.";
            $this->is_validate = false;
        }
        
        //Если кастомную прошли, проводим стандартную
        if ($this->is_validate) {
            $this->checkedFields();
        }
        
        if ($this->is_validate) {
            $this->setFieldInfo('password', substr(md5(uniqid(mt_rand(), true)), 10, 10));
            $this->setFieldInfo('subscr_news', 1);
        }
        
        return $this->actionRegistration(true);
    }
    
    /**
     * Основной метод регистрации пользователей
     * @param bool $is_preset Флаг, показывающий наличие подготовленных данных
     * @return type
     */
    public function actionRegistration($is_preset = false) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_meta.php");
        
        if (!$is_preset) {
            $this->setFieldInfo('role', __paramInit('int', null, 'role'));
            $this->setFieldInfo('login', trim(__paramInit('string', null, 'login')));
            $this->setFieldInfo('email', trim(__paramInit('string', null, 'email')));
            $this->setFieldInfo('subscr_news', trim(__paramInit('bool', null, 'subscribe')));
            //$this->setFieldInfo('smscode', trim(__paramInit('string', null, 'smscode')));
            //$this->setFieldInfo('phone', $_SESSION["reg_phone"]);
            // пароль берем напрямую из $_POST, а то __paramInit режет спецсимволы (пароль хешируется - SQL инъекция невозможна)
            $this->setFieldInfo('password', stripslashes($_POST['password']));
            $this->checkedFields();

            session_start();   
            $this->setFieldInfo('captchanum', __paramInit('string', null, 'captchanum'));
            $num = __paramInit('string', null, 'rndnum');
            if ( !$_SESSION["regform_captcha_entered"] ) {
                $_SESSION['reg_captcha_num'] = $this->captchanum;
                $captcha = new captcha($this->captchanum);
                if (!$captcha->checkNumber($num)) {
                    $this->error['captcha'] = 'Неверный код. Попробуйте еще раз';
                    $this->is_validate = false;            
                    unset($_SESSION['reg_captcha_num']);
                }
            }
        }
        //if ( (is_release() || $_SESSION["reg_phone"] != 71111112222) && sbr_meta::findSafetyPhone($_SESSION["reg_phone"], __paramInit('string', null, 'role') == 2 ? 'emp' : 'frl') ) {
        //    $this->error['phone'] = 'Пользователь с таким номером уже зарегистрирован';
        //    $this->is_validate = false;
        //    unset($_SESSION['reg_captcha_num']);
        //}
        
        if($this->is_validate) {
            //unset($_SESSION['smsIsRequested']);
            if ($this->role == self::ROLE_FREELANCER) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
                $newuser = new freelancer();
                $newuser->role = 0;
            } else if($this->role == self::ROLE_EMPLOYER) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
                $newuser = new employer();
                $newuser->role = 1;
            }
            $newuser->login  = substr($this->login, 0, 15);
            $newuser->email  = substr($this->email, 0, 64);
            $newuser->passwd = substr($this->password, 0, 24);
            $newuser->subscr = '1111111' . (int)$this->subscr_news . '11111111';
            
            $newuser->uid    = $newuser->Create($rerror, $error);
            if ($newuser->uid && !$error) {
                $ok = $this->completedRegistration($newuser);
                
                if($ok) {
                    //require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate.php";
                    //$phone = '+' . preg_replace("#^\+#", "", $_SESSION["reg_phone"]);
                    unset($_SESSION["regform_captcha_entered"]);
                    unset($_SESSION["login_generated"]);
                    $tu_ref_uri = @$_SESSION['tu_ref_uri'];
                    //unset($_SESSION["reg_phone"]);
                    //unset($_SESSION['send_sms_time']);
                    //sms_gate::saveSmsInfo($phone, $_SESSION["reg_sms_isnn"], $_SESSION["smsCode"], $_SESION["reg_sms_date_send"], $newuser->uid);
                    $_SESSION['email'] = $newuser->email;
                    $_SESSION['rrole'] = $this->role;

                    // Если пришли сюда регистрироватся то после нажатия кнопки регистрации удаляем куки регистрации иначе после активации нас перекинет на мастер
                    $wizard = new wizard();
                    $wizard->clearCookiesById($newuser->role == 1 ? 1 : 2); // В зависимоти от того кого регистрируем
                    // На всякий случай при новой регистрации удаляем переменную проверки
                    self::resetCheckAccess();
                    
					$_user_action = (isset($_REQUEST['user_action']) && $_REQUEST['user_action'])?substr(htmlspecialchars($_REQUEST['user_action']), 0, 25):'';
                    $_user_action = trim($_user_action);
					
                    login($newuser->login, users::hashPasswd(trim(stripslashes($newuser->passwd))), 1, false);
                    
                    if (is_emp($newuser->role)) {
                        $_SESSION['reg_role'] = 'Employer';

                        $ref_uri = isset($_SESSION['ref_uri'], $_SESSION['was_customer_wizard'])?
                                urldecode($_SESSION['ref_uri']):null;
                        unset($_SESSION['was_customer_wizard']);
                        
                        $redirect_to = $ref_uri?$ref_uri:'/public/?step=1&kind=1';
                        
                        //По умолчанию, при регистрации заказчика, перенаправляем его на публикацию проекта
						if (strpos($_user_action, 'project_to_')) {
							$login = str_replace('add_project_to_', '', $_user_action);
							$redirect_to = '/public/?step=1&kind=9&exec='.$login;
						}
                        
                        $redirect = __paramInit('link', NULL, 'redirect');
                        if ($redirect && !$ref_uri) {
                            $redirect_to = urldecode($redirect);
                        }

                    } else {
                        $_SESSION['reg_role'] = 'Freelancer';
                        
                        $redirect_to = //"/registration/wellcome/freelancer.php";
                        $redirect_to = "/registration/profession.php" . (!empty($user_action)?"?user_action={$user_action}":'');
                        
                        //Очищаем чтобы далее небыло редиректа
                        //@todo: согласно https://beta.free-lance.ru/mantis/view.php?id=28862
                        $_user_action = '';
                    }
					
                    switch($_user_action) {
                        case 'tu':
                            if($tu_ref_uri) {
                                $redirect_to = HTTP_PFX.$_SERVER["HTTP_HOST"].urldecode($tu_ref_uri);
                            }
                            break;
                        case 'new_tu':
                            if(!is_emp($newuser->role)) {
                                $redirect_to = HTTP_PFX.$_SERVER["HTTP_HOST"].'/users/'.$newuser->login.'/tu/new/';
                            } else $redirect_to = HTTP_PFX.$_SERVER["HTTP_HOST"].'/tu/';
                            break;
                        case 'promo_verification':
                            $redirect_to = '/promo/verification/';
                            break;
                        case 'buypro':
                            if(is_emp($newuser->role)) {
                                $redirect_to = '/payed-emp/';
                            } else {
                                $redirect_to = '/payed/';
                            }
                            break;
                        case 'add_order':
                            $url = __paramInit('link', NULL, 'redirect');
                            $redirect_to = HTTP_PFX.$_SERVER["HTTP_HOST"].urldecode($url);
                            break;
                    }
                    
                    if (!is_emp($newuser->role)) {
                        $_SESSION['activate_password'] = $newuser->passwd;
                        $_SESSION['subscr_news'] = (int)$this->subscr_news;

                        //Создаем новый экземпляр, т.к. нужно обновить только подписки
                        //Отписываем от всего, кроме личных сообщений
                        $freelancer = new freelancer();
                        $freelancer->UpdateSubscr($newuser->uid, 1, array(), 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, false, 0);
                    }
                    
                    
                    //Обработать действия по событию успешной регистрации
                    $this->afterSuccessRegistation($newuser);
                   
                    
                    if ($is_preset) {
                        return array(
                            'success' => true,
                            'user_id' => $newuser->uid,
                            'redirect' => $redirect_to
                        );
                    } else {
                        header("Location: ".$redirect_to);
                        exit;
                    }
                }
            }
        } else {
            return $this->error;
        }
    }
    
    public function completedRegistration($user) { 
        require_once( $_SERVER['DOCUMENT_ROOT'] . "/classes/activate_code.php");
        require_once( $_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");

        $smail    = new smail();
        $bSuspect = users::isSuspiciousUser($user->uid, $user->login, '', $user->uname, '', $user->usurname, '');
        $sPasswd  = ( $bSuspect ) ? $user->passwd : ''; // чтобы из админки можно было выслать $smail->NewUser
        $code     = activate_code::Create($user->uid, $user->login, $sPasswd, $error);

        if (!$bSuspect) {
            // юзер не подозрительный - сразу отпавляем юзеру письмо с кодом активации
            $_SESSION['suspect'] = false;
            $smail->NewUser($user->login, $this->_fields['password'], $code);
        } else {
            // отправляем уведомление админу о том, что зарегистрировался подозрительный юзер
            // если админ его одобрит - то письмо с кодом активации уйдет из админки
            $_SESSION['suspect'] = true;
            $smail->adminNewSuspectUser($user->login, $user->uname, $user->usurname);
        }
        
        // Серый список IP
        $this->checkGrayIp( $user );
        
        // Фиксация трафика, учет статистики 
        $GLOBALS['traffic_stat']->checkRegistration( $user->uid, $user->role );
        
        return true;
    }
    
    /**
     * Серый список IP
     * 
     * @param users $user
     */
    function checkGrayIp( $user ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/gray_ip.php' );

        $aGrayList = gray_ip::getGrayListByRegIp(getRemoteIP());
        if ($aGrayList) { // есть записи в списке первичных IP
            gray_ip::addSecondaryIp($user->uid, $user->login, $user->role, $aGrayList);
        }
    }
    
    public function checkedFields( $phone_is_set = false ) {
        $this->is_validate = true;
        foreach($this->_fields as $name=>$value) {
            $this->validate( $name, $value, $phone_is_set );
            if(!empty($this->error[$name])) {
                $this->is_validate = false;
            }
        }
    }
    
    public function validate( $name, $value, $phone_is_set = false ) {
        global $DB;
        
        switch($name) {
            case 'agree':
                if($value!=1) {
                    $this->error[$name] = 'Прочтите и согласитесь с правилами';
                }
                break;
            case 'country':
                if($value <= 0) {
                    $this->error[$name] = 'Выберите страну';
                }
                break;
            case 'city':
                if($value <= 0) {
                    $this->error[$name] = 'Выберите город';
                }
                break;
            case 'birthday':
                if(!$value) {
                    $this->error[$name] = "Заполните дату дня рождения"; 
                    $this->errno[$name] = 1;
                }
                break;
            case 'sex':
                if($value === null) {
                   // $this->error[$name] = 'Выберите пол';
                }
                break;
            case 'uname':
            case 'usurname':
                if(!$value) {
                    $this->error[$name] = "Поле заполнено некорректно"; 
                    $this->errno[$name] = 1;
                }
                if (!preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/i", $value)) {
                    $this->error[$name] = "Поле заполнено некорректно";
                    $this->errno[$name] = 2;
                }
                break;
            case 'password':
                if($value == '') {
                    $this->error[$name] = 'Введите пароль';
                    $this->errno[$name] = 1;
                }else if ( strlen($value) > 24) {
                    $this->error[$name] = 'Максимальная длина пароля 24 символа';
                    $this->errno[$name] = 2;
                }else if ( strlen($value) < 6) {
                    $this->error[$name] = 'Минимальная длина пароля 6 символов';
                    $this->errno[$name] = 3;
                }else if ( strlen( preg_replace("#[a-zA-Z\d\!\@\#\$\%\^\&\*\(\)\_\+\-\=\;\,\.\/\?\[\]\{\}]#", "", $value) ) != 0) {
                    $this->error[$name] = 'Поле заполнено некорректно';
                    $this->errno[$name] = 4;
                }
                break;
            case 'login':
                if (!preg_match("/^[a-zA-Z0-9]+[-a-zA-Z0-9_]{2,}$/", $value)) {
                    $this->error[$name] = 'От 3 до 15 символов. Может содержать латинские буквы, цифры, подчёркивание (_) и дефис (-)';
                    $this->errno[$name] = 1;
                }
                if (in_array(strtolower($value), $GLOBALS['disallowUserLogins'])) {
                    $this->error[$name] = 'Извините, такой логин использовать нельзя';
                    $this->errno[$name] = 2;
                }
                if ( empty($this->error[$name]) ) {
                    $sql = "SELECT uid FROM users WHERE lower(login) = ?";
                    if ($DB->val($sql, strtolower($value))) {
                        $this->error[$name] = 'Извините, этот логин занят. Придумайте другой.';
                        $this->errno[$name] = 3;
                    }
                }
                break;
            case 'email':
                if (!is_email($value)) {
                    $this->error[$name] = 'Поле заполнено некорректно';
                    $this->errno[$name] = 1;
                }
                
                if (empty($this->error[$name])) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/temp_email.php");

                    if (temp_email::isTempEmail($value)) {
                        $this->error[$name] = 'К сожалению, регистрация аккаунта на указанный адрес электронной почты невозможна. Пожалуйста, для регистрации воспользуйтесь почтовым адресом другого домена';
                        $this->errno[$name] = 2;
                    } else {
                        if ($DB->val("SELECT uid FROM users WHERE lower(email) = ?", strtolower($value))) {
                            if ($this->_disable_email_redirect) {
                                $this->error[$name] = 'Email занят';
                                $this->errno[$name] = 3;
                            } else {
                                require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php";
                                $smail = new smail();
                                $smail->reRegisterToYourMail(strtolower($value));
                                unset($_SESSION["regform_captcha_entered"]);
                                unset($_SESSION["reg_phone"]);
                                unset($_SESSION['send_sms_time']);
                                header_location_exit("/reg_complete.php");
                            }
                        }
                    }
                }
                break;
            case 'smscode':
                if ( $_SESSION['smsCode'] != $value  && !($value == 7777 && $_SESSION["reg_phone"] == 71111112222 && !is_release()) ) {
                    $this->error[$name] = 'Неверный код';
                }
                break;
            case 'phone':
                if ( !$phone_is_set && $_SESSION["reg_phone"] != $value ) {
                    $this->error[$name] = 'Вы подтвердили не этот номер';
                    $this->errno[$name] = 1;
                }
                
                $sPhone = $phone_is_set ? $value : $_SESSION['reg_phone'];
                
                if ( trim( preg_replace("#[\D]#", "", $sPhone) ) == '' ) {
                    $this->error[$name] = 'Необходимо ввести номер';
                    $this->errno[$name] = 2;
                }
                break;
        }
    }
    
    public function validActivateCode($code) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/activate_code.php");
        $act = new activate_code;
        $this->setFieldInfo('uid', $act->isActivateCode($code));
        $this->setFieldInfo('code', $code);
        return ($this->uid > 0);
    }
    
    public function resetCheckAccess() {
        unset($_SESSION['check_user_access']);
    }
    
    /**
     * Вставлять в любое дествие в котором необходима заполненность данных со страницы registration/info.php
     * @example registration::access_action_site(); 
     */
    public function access_action_site($confirm = '') {
        if(!$this->checkUserAccess()) {
            $key = $this->genUkeyPost();
            if(isset($_SESSION['link_back'])) {
                $_SESSION['action_form'][$key] = $_SESSION['link_back'];
            } elseif(isset($_SESSION['ref_uri'])) {
                $_SESSION['action_form'][$key] = urldecode($_SESSION['ref_uri']);
            } else {
                $_SESSION['action_form'][$key] = "/registration/wellcome/" . ( is_emp() ? "employer.php" : "freelancer.php" );
            }
            $_SESSION['confirm_info'] = $confirm;
            $_SESSION['cache_request']['post'] = $_POST; 
            $_SESSION['cache_request']['files'] = $_FILES; 
            if(count($_POST) > 0) {
                $_SESSION['post_cache'][$key] = $_POST;
            }
            if(count($_FILES) > 0) {
                $_SESSION['files_cache'][$key] = $_FILES;
            }
            header("Location: /registration/info.php?ukey=$key");
            exit;
        }
    }
    
    /**
     * Генерирует ключ для записи данных
     * @return type 
     */
    public function genUKeyPost() {
        return substr(md5(microtime()), 0, 5);
    }
    
    /**
     * Будем проверять заполнил ли пользователь фамилию и имя 
     */
    public function checkUserAccess($uid = false, $force = false) {
        if(isset($_SESSION['check_user_access']) && !$force) {
            return $_SESSION['check_user_access'];
        }
        
        $this->getInformationUser($uid);
        $this->checkedFields();
        $this->error = array(); // Ошибки убираем они нас не интересуют тут

        $_SESSION['check_user_access'] = $this->is_validate;
        return $_SESSION['check_user_access'];
    }
    
    public function getInformationUser($uid = false) {
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
            
        if(!$uid) $uid = $_SESSION['uid'];
        if(is_emp()) {
            $user = new employer();
        } else {
            $user = new freelancer();
        }
        $user->GetUserByUID($uid);
        $this->setFieldInfo('uname',    $user->uname);
        $this->setFieldInfo('usurname', $user->usurname);
        $this->setFieldInfo('birthday', $user->birthday);
        $this->setFieldInfo('country', $user->country);
        $this->setFieldInfo('country_name', country::GetCountryName($user->country));
        $this->setFieldInfo('city', $user->city);
        $this->setFieldInfo('city_name', city::GetCityName($user->city));
        $this->setFieldInfo('sex', $user->sex == 't' ? 1 : ($user->sex == 'f'?0:-1));
        $this->setFieldInfo('info_for_reg', unserialize($user->info_for_reg));
    }
    
    public function getNamePageUri($uri) {
        $url = @parse_url($uri);
        if($url['path'] == '/') return '';
        $page = explode('/', $url['path']);
        $page = $page[0] != '' ? $page[0] : $page[1];
        return __paramValue('string', $page);
    }
    
    public function listenerAccess($request) {
        $uri = self::getNamePageUri(isset($_SERVER['HTTP_ORIGINAL_URI']) ? $_SERVER['HTTP_ORIGINAL_URI'] : $_SERVER['REQUEST_URI']);
        if (is_array($request) && isset($request['action'])) {
            $action = $request['action'];
            if($this->access_action[$action] && ( $this->access_action[$action]['page'] == $uri || $this->access_action[$action]['page'] === true ) ) {
                $_SESSION['link_back'] = $_SERVER['REQUEST_URI'];
                $this->access_action_site($this->access_action[$action]['confirm']);
            }
        } elseif($uri != '') {
            // учитываем параметры в адресной строке
            switch ($uri) {
                case 'norisk2':
                    if ($_GET['site'] === 'create') {
                        $uri .= '_create';
                    }
                    break;
                default:
                    break;
            }
            // Данную страницу мы проверяем на заполненность данных
            if(isset($this->access_action_page[$uri])) {
                $_SESSION['link_back'] = $_SERVER['REQUEST_URI'];
                $confirm = $this->access_action_page[$uri]['confirm'];
                if(!is_emp() && isset($this->access_action_page[$uri]['confirm_frl'])) $confirm = $this->access_action_page[$uri]['confirm_frl'];
                $this->access_action_site($confirm);
            } 
        } 
    }
    
    
    
    
    
    
    public function autoRegistation($data = array())
    {
        $newuser = $this->fillData($data);
        
        //Пробуем создавать
        $rerror = 0;
        $error = array(); 
        $newuser->uid = $newuser->Create($rerror, $error);
        if(!$newuser->uid) return FALSE;

        //Высылаем приглашение без активации но с паролем
        $smail = new smail();
        $smail->NewUser($newuser->login, $this->_fields['password']);
        
        //Обработать действия по событию успешной регистрации
        $this->afterSuccessRegistation($newuser);
        
        return $newuser;   
    }
    
    
    
    public function autoRegistationAndLogin($data = array())
    {
        $uid = $data['uid']?intval($data['uid']):0;
        $role = $data['role'];
        
        if ($uid > 0) {
            $class_name = ($role == 0)?'freelancer':'employer';
            $user = new $class_name();
            $user->GetUserByUID($uid);
        } else {
            unset($data['uid']);
            $user = $this->autoRegistation($data);
        }
        
        if (!$user || !$user->uid)  {
            return FALSE;
        }
        
        $pwd = ($uid > 0)?@current(users::GetUserSoltCookie($user->uid)):
                          users::hashPasswd(trim(stripslashes($user->passwd)));
        
        $ret = login($user->login, $pwd, 1, ($uid > 0));
        
        return array(
            'ret' => $ret,
            'user' => $user
        );
    }
    
    /**
     * Наполняем пользователя параметрами
     * 
     * @param array $data Параметры
     * @return type Получившийся юзер
     */
    public function fillData($data = array(), $only_generate = false) 
    {
        //Мыло обязательно должно быть не свое же нам втыкать
        if(empty($data) || !isset($data['email']) || !strlen($data['email'])) return FALSE;
        //Идем в обход меcтного свойства email так как оно при проверке редиректит
        $email = $data['tmp_email'] = $data['email'];
        unset($data['email']);
        //Обязательные поля которые можно сгенерировать
        $_requred = array('login' => '', 'password' => '');
        $data = array_merge($_requred,$data);
        
        foreach($data as $key => &$value)
        {
            //Генерируем пустые поля
            if(empty($value))
            {
                switch($key)
                {
                    case 'login':
                        //По умолчанию логин генерируем из мыла
                        $value = @current(explode("@",$email));
                        $value = preg_replace('/[^-a-zA-Z0-9_]/', '', $value);
                        //Если совсем все плохо то генерируем случайно
                        if(!strlen($value)) $value = substr(md5(uniqid(mt_rand(), true)), 12, 8);
                        break;
                
                    case 'password':
                        $value = substr(md5(uniqid(mt_rand(), true)), 10, 10);
                        break;
                    //Тут много еще чего можно генерировать но мне лень :)
                }
            }
            
            $this->setFieldInfo($key, $value);
        }
        
        
        $try_cnt = 1;

        do
        {
            //Проверяем поля
            $this->error = array();
            $this->checkedFields();
            
            foreach($data as $key => $value)
            {
                //Если у поля нет ошибки то збс
                if(!isset($this->error[$key])) continue;
                //Иначе пробуем исправить
                switch($key)
                {
                    //Пробуем добавлять дату и верямя в конец
                    /*
                    case 'login':
                        $len = 8;
                        $frm = 'dmy';
                        
                        if($try_cnt > 0)
                        {
                            $len = 2;
                            $frm = 'dmyHis';
                        }
                        
                        $new_login = substr($value, 0, $len);
                        $new_login = sprintf('%s_%s', $new_login, date($frm));
                        $this->setFieldInfo($key, $new_login);
                        break;
                    */
                    
                    //Добавляем циферки попорядку
                    case 'login':
                        $suffix = $try_cnt <=15 ? rand(1, 999) : rand(1000, 99999);
                        $len = 15 - strlen($suffix);
                        $new_login = substr($value, 0, $len);
                        $new_login = sprintf('%s%s', $new_login, $suffix);
                        $this->setFieldInfo($key, $new_login);
                        break;
                    
                    //Если ошибки в именах то убиваем поля и без них прокатит
                    case 'uname':
                    case 'usurname':
                        $this->unsetField($key);
                        break;
                }
            }
            
            $try_cnt++;
        }
        while(!$this->is_validate && $try_cnt < 20);
        
        //Мы перепробывали все аж столько раз 
        //ничего не помогат, это не исправить, выходим)
        if ($try_cnt >= 20 || $only_generate) {
            return FALSE;
        }
        
        
        
        //@todo: здесь некорректно понимается параметр роли в этом классе значения 1/2 а в users уже 0/1
        //@todo: поэтому необходимо внести правки начиная с функции анонимной публикации чего-либо
        $class_name = (isset($this->role) && $this->role == 0)?'freelancer':'employer';
        $newuser = new $class_name();
        $keys = array_keys($data);
        
        //Последнии приготовления
        foreach($keys as $key)
        {
            $class_prop = $key;
            
            switch($key)
            {
                case 'uname':
                case 'usurname':
                    if(!isset($this->{$key})) $newuser->checked_name = FALSE; 
                    break;
                case 'password':
                    $class_prop = 'passwd';
                    break;
                //email проходит без проверок до самого класса юзера
                case 'tmp_email':
                    $class_prop = 'email';
                    break;
            }
            
            $newuser->{$class_prop} = $this->{$key};
        }
        
        return $newuser;
    } 
    
    
    
    /**
     * Метод вызывается после успешной регистации
     */
    protected function afterSuccessRegistation($newuser)
    {
        if (!$newuser || $newuser->uid <= 0) {
            return false;
        }

        $_SESSION['reg_role'] = (is_emp($newuser->role))?'customer':'freelancer';
        
        //Отправить в очередь события регистрации для GA
        require_once(ABS_PATH . '/classes/statistic/StatisticFactory.php');
        require_once(ABS_PATH . '/classes/users.php');
        
        $ga = StatisticFactory::getInstance('GA');
        $ga->queue('event', array(
            'uid' => $newuser->uid,
            'cid' => users::getCid(),
            'category' => $_SESSION['reg_role'],
            'action' => 'registration_finished'
        ));
        
        
        return true;
    }
    
    
}