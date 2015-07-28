<?php

require_once(ABS_PATH . "/classes/Form/View.php");


class LoginForm extends Form_View
{
    const FAIL_LOGIN_MSG = 'Неверный логин/пароль. Попробуйте ещё раз или восстановите пароль.';
    
    const UID_FAIL      = 0;
    const UID_BANNED    = -1;
    const UID_INNACTIVE = -2;
    const UID_DENYIP    = -3;
    
    const REDIRECT_URL_BANNED   = "/banned.php?login=%s&rnd=%s";
    const REDIRECT_URL_INACTIVE = "/inactive.php"; 
    const REDIRECT_URL_DENYIP   = "/denyip.php?login=%s";
    const REDIRECT_URL_2FA      = "/auth/second/";
    
    
    
    protected $viewScriptFormPrefixPath = 'login/views/forms';
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';
    

    protected $redirect_to = '';


    public $filters = array(
        'StringTrim',
        'StripSlashes'
    ); 
    
    
    /*
    public function __construct($options = null) 
    {
        parent::__construct($options);
    }    
    */
    
    /**
     * Общая вьюшка для форм
     */
    
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 
                $this->viewScriptFormPrefixPath . 
                '/login-default-form.phtml'))
        ));
    }
    
    
    
    
    /**
     * Инициализация формы
     */    
    public function init()
    {
        global $js_file;
        
        $js_file['mootools-form-validator'] = 'mootools-form-validator.js';
        $js_file['registration/LoginForm'] = 'registration/LoginForm.js';
        
        $this->setAttrib('id', 'login-form');
        
        $this->addElement(
           new Zend_Form_Element_Text('login', array(
               'data-validators' => 'required',
               'td_class' => 'b-layout__td_relative b-layout__td_width_full_ipad',
               'error_class' => 'b-layout__txt_error b-layout__txt_error_right_desktop',
               'class' => 'b-combo_large',
               'padbot' => 30,
               'size' => 80,
               'hide_label' => true,
               'label' => 'Логин, телефон или почта',
               //'width' => 250,
               'placeholder' => 'Логин, телефон или почта',
               'required' => true,
               'filters' => $this->filters + array('StripTags'),
               //'validators' => $validators
        )));        
        
        $this->addElement(
           new Zend_Form_Element_Password('passwd', array(
               'data-validators' => 'required',
               'td_class' => 'b-layout__td_relative b-layout__td_width_full_ipad',
               'error_class' => 'b-layout__txt_error b-layout__txt_error_right_desktop',               
               'class' => 'b-combo_large',
               'padbot' => 15,
               'size' => 80,
               'hide_label' => true,
               'label' => 'Пароль',
               //'width' => 250,
               'placeholder' => 'Пароль',
               'required' => true,
               'filters' => $this->filters,
               //'validators' => $validators
        )));
        
        $this->addElement(
           new Zend_Form_Element_Checkbox('autologin', array(
               'class' => 'b-check_large',
               'padbot' => 60,
               'label' => 'Запомнить меня',
               'td_class' => 'b-layout__td_width_full_ipad'
        )));
        
        $this->addElement(
           new Zend_Form_Element_Submit('singin', array(
               'td_class' => 'b-layout__td_width_full_ipad',
               'class' => 'b-button_flat_large b-button_flat_width_full',
               'padbot' => 1,
               'label' => 'Войти',
               'data-ga-event' => "{ec: 'user', ea: 'authorization_started',el: 'email'}"
        )));
    }
    
    
    /**
     * Проверка авторизации и установка ошибки или URL для перехода
     * 
     * @param type $data
     * @return boolean
     */
    public function isValid($data) 
    {
        if ($valid = parent::isValid($data)) {
            
            $data = $this->getValues();

            $autologin = isset($data['autologin']) && $data['autologin'] == 1;
            $pwd = users::hashPasswd($data['passwd']);
            $uid = login($data['login'], $pwd, $autologin);

            switch ($uid) {
                case self::UID_FAIL:
                    $valid = false;
                    $this->getElement('login')->addError(self::FAIL_LOGIN_MSG);
                    break;
                
                case self::UID_BANNED:
                    $_SESSION['rand'] = csrf_token();
                    $this->redirect_to = sprintf(self::REDIRECT_URL_BANNED, 
                            $data['login'], 
                            $_SESSION['rand']);
                    break;
                
                case self::UID_INNACTIVE:
                    $this->redirect_to = self::REDIRECT_URL_INACTIVE;
                    break;
                
                case self::UID_DENYIP:
                    $this->redirect_to = sprintf(self::REDIRECT_URL_DENYIP, 
                            $data['login']);
                    break;
                
                case users::AUTH_STATUS_2FA:
                    $this->redirect_to = self::REDIRECT_URL_2FA;
                    break;
                
                default:
                    
                    //Успешная авторизация
                    if ($uid > 0) {
                        
                        $default_location = is_emp() ? '/tu/' : '/projects/';
                        
                        $ref_uri = isset($_SESSION['ref_uri']) ? urldecode($_SESSION['ref_uri']) : null;
                        $ref_uri = !$ref_uri ? $default_location : $ref_uri;
                        $location = HTTP_PFX . $_SERVER['HTTP_HOST'] . $ref_uri;
                        
                        // #0012501
                        $location = preg_replace("/\/router\.php\?pg=/", "", $location);
                        
                        // #0011589
                        if (strpos($location, '/remind/') || 
                            strpos($location, 'inactive.php') || 
                            strpos($location, 'checkpass.php') || 
                            strpos($location, '/registration/') || 
                            strpos($location, 'fbd.php')) {
                            
                            $location  = $default_location;                        
                        }
                        
                        session_write_close();

                        //Отправляем в очередь событие об успешной авторизации
                        require_once(ABS_PATH . '/classes/statistic/StatisticFactory.php');
                        require_once(ABS_PATH . '/classes/users.php');
                        
                        $ga = StatisticFactory::getInstance('GA');
                        $ga->queue('event', array(
                            'uid' => $uid,
                            'cid' => users::getCid(),
                            'category' => is_emp()?'customer':'freelancer',
                            'action' => 'authorization_passed',
                            'label' => 'email'
                        ));
                        
                        
                        $this->redirect_to = $location;
                    }
            }
        }
        
        return $valid;
    }
    
    
    /**
     * Вернуть URL
     * 
     * @return type
     */
    public function getRedirect()
    {
        return $this->redirect_to;
    }
}