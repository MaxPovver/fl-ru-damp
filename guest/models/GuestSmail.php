<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/template.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smail.php';
require_once('GuestConst.php');


/**
 * Директория шаблонов писем
 */
define('GUEST_TPL_MAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/guest/');
define('GUEST_TPL_BASE_LAYOUT', '/../layout.tpl.php');

class GuestSmail extends smail
{
    protected $template_format = '%s.tpl.php';
    protected $is_local = FALSE; 
    protected $base_suffix = '';


    public function __construct() 
    {
        parent::__construct();

        $server = defined('SERVER')?strtolower(SERVER):'local';
        $this->is_local = ($server == 'local');
    }    
    
    
    
    /**
     * Скрываем вызов некоторых методов чтобы при их вызове проверить 
     * в каком окружении запускается рассылка и если на локале то игнорим ее
     * 
     * @todo: Если мешает достаточно закоментить проверку на лакальность ;)
     * 
     * @param string $method
     * @param type $arguments
     * @return boolean
     */
    public function __call($method, $arguments) 
    {
        if($this->is_local) return FALSE;
        
        $method = '_' . $method;
        if(method_exists($this, $method)) 
        {
            call_user_func_array(array($this, $method), $arguments);
        }
        
        return TRUE;
    }
    
    
    /**
     * Инициализация или получение аттрибутов класса
     * 
     * @param array $attributes
     * @return type
     */
    public function attributes($attributes = null) 
    {
        if (is_null($attributes)) {
            return get_object_vars($this);
        }

        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }     
    
    
    protected function sendFromTemplate($template, $data, $user)
    {
        $template = sprintf($this->template_format, $template);
        
        $this->recipient = $this->_formatFullname($user, true);
        $content = Template::render(
                GUEST_TPL_MAIL_PATH . $template, 
                array('smail' => $this) + $data
        );       

        $message = Template::render(
                GUEST_TPL_MAIL_PATH . GUEST_TPL_BASE_LAYOUT, 
                array('content' => $content)
        ); 
        
        $this->message = nl2br($message);
        
        return array('ret' => $this->send('text/html'), 'message' => $message);         
    }

    

    public function _sendActivation($email, $code, $user, $type, $link = '')
    {
        $suffix = '';
        
        switch($type){
            case GuestConst::TYPE_PERSONAL_ORDER: $suffix = 'order';break;
            case GuestConst::TYPE_PROJECT: $suffix = 'project';break;
            case GuestConst::TYPE_VACANCY: $suffix = 'vacancy';break;
        }
        
        if(empty($suffix)) {
            return false;
        }
        
        $template = 'activate';
        $recepient = array('email' => $email);
        $is_pro = false;
        
        if ($user->uid > 0) {
            $is_pro = $user->is_pro == 't';
            $recepient = $user;
            $template = 'accept';
        }
        
        $ext_vars = array();
        
        if (!empty($link)) {
            $ext_vars = array(
                'link' => $link,
                'unsubscribe_uri' => GuestInviteUnsubscribeModel::getUri($email)                
            );
            
            $this->setBaseSuffix('_adm');
        }
        
        $res = $this->sendFromTemplate(
                $template . $this->base_suffix . '_' . $suffix, 
                array('code' => $code, 'is_pro' => $is_pro) + $ext_vars, 
                $recepient
        );
        
        return $res['ret'];
    }
    
    
    /**
     * Форматтер имени юзера
     * @todo Не лучшее место для этого?
     * 
     * @param type $user
     * @param type $with_email
     * @return type
     */
    protected function _formatFullname(&$user, $with_email = false)
    {
        $u = (is_object($user))?array(
            'uname' => $user->uname,
            'usurname' => $user->usurname,
            'login' => $user->login,
            'email' => $user->email
            ):$user;
        
        $fullname = "{$u['uname']}";
        $fullname .= ((empty($fullname))?"":" ") . "{$u['usurname']}";
        $fullname .= (empty($fullname))?"{$u['login']}":" [{$u['login']}]";
        if($with_email) $fullname .= " <{$u['email']}>";
        return $fullname;
    }
    
    public function setBaseSuffix($suffix)
    {
        $this->base_suffix = $suffix;
    }
    
}