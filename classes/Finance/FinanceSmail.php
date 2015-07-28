<?php

/**
 *  Класс для работы с уведомлениями при работе с реквизитами финансов
 *
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/template.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smail.php';


/**
 * Директория шаблонов писем
 */
define('FINANCE_TPL_MAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/finance/');
define('FINANCE_TPL_BASE_LAYOUT', 'layout.tpl.php');

class FinanceSmail extends smail
{
    protected $data = array();
    
    protected $template_format = '%s_%s.tpl.php';
    protected $is_local = FALSE;
    
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
    
    
    
    
    protected function sendFromTemplateByUserId($template, $uid)
    {
        $user = new users();
        $user->GetUserByUID($uid);
        if($user->uid <= 0) return false;
        
        $is_emp = is_emp($user->role);

        $sufix = ($is_emp)?'emp':'frl';
        $template = sprintf($this->template_format,$template, $sufix);
        
        $this->recipient = $this->_formatFullname($user,true);
        $content = Template::render(
                FINANCE_TPL_MAIL_PATH . $template, 
                array(
                    'smail' => &$this,
                    'data' => $this->data,
                    'params' => $this->_addUrlParams($is_emp?'e':'f'),
                    'user' => (array)$user
                )
        );       

        $message = Template::render(
                FINANCE_TPL_MAIL_PATH . FINANCE_TPL_BASE_LAYOUT, 
                array('content' => $content)
        ); 
        
        $this->message = nl2br($message);
        
        return array('ret' => $this->send('text/html'), 'message' => $message); 
    }
    
    
    
    /**
     * Отправить уведомление о успешной проверки реквизитов
     * 
     * @param int $uid
     * @return boolean
     */
    public function _financeUnBlocked($uid)
    {
        $result = $this->sendFromTemplateByUserId('success_check', $uid);
        return $result['ret'];
    }

    
    /**
     * Отправить уведомление о недействительности реквизитов
     * 
     * @param int $uid
     * @return boolean
     */
    public function _financeBlocked($uid, $reason)
    {
        $this->data['reason'] = $reason;
        $result = $this->sendFromTemplateByUserId('fail_check', $uid);
        return $result['ret'];        
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
    
}
