<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_helper.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/employer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/template.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_sms.php");


/**
 * Директория шаблонов писем
 */
define('PROJECTS_TPL_MAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/projects/');

/**
 * Class tservices_smail
 * Класс для работы с отправкой писем для ТУ
 */
class projects_smail extends smail {

    protected $is_local = FALSE;

    public function __construct() {
        parent::__construct();

        $server = defined('SERVER') ? strtolower(SERVER) : 'local';
        $this->is_local = ($server == 'local');
    }

    /**
     * Скрываем вызов некоторых методов чтобы при их вызове проверить 
     * в каком окружении запускается рассылка и если на локале то игнорим ее
     * 
     * @todo: Если мешает достаточно закоментить проверку на локальность ;)
     * 
     * @param string $method
     * @param type $arguments
     * @return boolean
     */
    public function __call($method, $arguments) {
        if ($this->is_local) return FALSE;

        $method = '_' . $method;
        if (method_exists($this, $method)) {
            call_user_func_array(array($this, $method), $arguments);
        }

        return TRUE;
    }

    /**
     * Уведомление фрилансера о выборе его исполнителем
     * 
     * @param type $project
     */
    public function _onSetExecutorFrl($project) {
        $frl = new freelancer();
        $frl->GetUserByUID($project['exec_id']);

        $emp = new employer();
        $emp->GetUserByUID($project['user_id']);

        $this->subject = "Вам предложили стать исполнителем по проекту на сайте FL.ru";
        $this->recipient = $this->_formatFullname($frl, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'set_executor_frl.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'accept_url' => $GLOBALS['host'] . projects_helper::getStatusUrl($project['id'], 'accept', $frl->uid),
                'decline_url' => $GLOBALS['host'] . projects_helper::getStatusUrl($project['id'], 'decline', $frl->uid),
                'emp_fullname' => $this->_formatFullname($emp)
            )
        );

        return $this->send('text/html');
    }
    
    /**
     * Уведомление заказчику о создании заказа услуги
     * 
     * @param type $project
     */
    public function _onSetExecutorEmp($project) {
        $frl = new freelancer();
        $frl->GetUserByUID($project['exec_id']);

        $emp = new employer();
        $emp->GetUserByUID($project['user_id']);

        $this->subject = "Ожидается ответ от выбранного вами исполнителя в проекте";
        $this->recipient = $this->_formatFullname($emp, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'set_executor_emp.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'cancel_url' => $GLOBALS['host'] . projects_helper::getStatusUrl($project['id'], 'cancel', $emp->uid),
                'frl_fullname' => $this->_formatFullname($frl)
            )
        );

        return $this->send('text/html');
    }
    
    /**
     * Уведомление исполнителю о старте работ
     * 
     * @param type $project
     */
    public function _onStartWorkingFrl($project, $offer) {
        
        //$frl = new freelancer();
        //$frl->GetUserByUID($project['exec_id']);

        //$emp = new employer();
        //$emp->GetUserByUID($project['user_id']);

        $this->subject = "Начало работ по проекту";
        $this->recipient = $this->_formatFullname(&$offer, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'start_working_frl.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'emp_login' => $project['login'],
                'emp_fullname' => $this->_formatFullname(&$project)
            )
        );
        return $this->send('text/html');
    }
    
    /**
     * Уведомление заказчику о подтверждении проекта и старте работ
     * 
     * @param type $project
     */
    public function _onStartWorkingEmp($project, $offer) {
        
        //$frl = new freelancer();
        //$frl->GetUserByUID($project['exec_id']);

        //$emp = new employer();
        //$emp->GetUserByUID($project['user_id']);

        $this->subject = "Исполнитель начал выполнение работ по проекту";
        $this->recipient = $this->_formatFullname(&$project, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'start_working_emp.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'frl_login' => $offer['login'],
                'frl_fullname' => $this->_formatFullname(&$offer)
            )
        );
        return $this->send('text/html');
    }
    
    
    
    /**
     * Групируем уведомления о начале работы над проектом
     * 
     * @param type $project
     * @param type $offer
     */
    public function onStartWorking($project, $offer)
    {
        $ret_frl = $this->onStartWorkingFrl($project, $offer);
        $ret_emp = $this->onStartWorkingEmp($project, $offer);
        
        //Отправить СМС заказчику о подтверждении и начале работ исполнителем
        ProjectsSms::model($project['user_id'])->sendStatus($project['status'], $project['id'], $project['kind']);
        
        return $ret_frl && $ret_emp;
    }

    

    /**
     * Уведомление заказчику об отказе от проекта со стороны исполнителя
     * 
     * @param type $project
     */
    public function _onRefuseEmp($project, $offer) {
        
        //$frl = new freelancer();
        //$frl->GetUserByUID($project['exec_id']);

        //$emp = new employer();
        //$emp->GetUserByUID($project['user_id']);

        $this->subject = "Исполнитель отказался от выполнения вашего проекта";
        $this->recipient = $this->_formatFullname(&$project, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'refuse_emp.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'frl_login' => $offer['login'],
                'frl_fullname' => $this->_formatFullname(&$offer)
            )
        );
        
        $ret = $this->send('text/html');
        
        //Отправить СМС заказчику
        ProjectsSms::model($project['user_id'])->sendStatus($offer['status'], $project['id'], $project['kind']);
        
        return $ret;
    }
    
    /**
     * Уведомление исполнителю об отмене проекта со стороны заказчика
     * 
     * @param type $project
     */
    public function _onRefuseFrl($project, $offer) {
        
        //@todo: нет необходимости тк инфо о заказчике передается в project а о исполнителе в $offer
        
        //$frl = new freelancer();
        //$frl->GetUserByUID($project['exec_id']);
        
        //$emp = new employer();
        //$emp->GetUserByUID($project['user_id']);
        
        $this->subject = "Заказчик отменил свое предложение по проекту";
        $this->recipient = $this->_formatFullname(&$offer, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'refuse_frl.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'emp_login' => $project['login'],
                'emp_fullname' => $this->_formatFullname(&$project)
            )
        );
                
        $ret = $this->send('text/html');
        
        //Отправить СМС фрилансеру
        ProjectsSms::model($offer['user_id'])->sendStatus($offer['status'], $project['id'], $project['kind']);
        
        return $ret;
    }
    
    /**
     * Уведомление второй стороны о завершении проекта
     * 
     * @param type $project
     */
    public function _onFinish($project, $to_frl = true) {
        $params = array(
            'project_title' => $project['name'],
            'project_url' => $GLOBALS['host'].'/projects/' . $project['id']
        );
        
        $frl = new freelancer();
        $frl->GetUserByUID($project['exec_id']);

        $emp = new employer();
        $emp->GetUserByUID($project['user_id']);
        
        if ($to_frl) { //Письмо отправляем исполнителю
            $recipient = $this->_formatFullname($frl, true);
            $params['emp_login'] = $emp->login;
            $params['emp_fullname'] = $this->_formatFullname($emp);
            $params['opinions_url'] = $GLOBALS['host'].'/users/'.$emp->login.'/opinions/';
            
            $subject = "Заказчик завершил сотрудничество по проекту";
            $template = 'finish_no_fb_frl.tpl.php'; //Без отзыва
            if (isset($project['emp_feedback']) && isset($project['emp_rating'])) {
                $params['rating'] = $project['emp_rating'];
                $params['opinions_url'] = $GLOBALS['host'].'/users/'.$frl->login.'/opinions/';
                $params['text'] = $project['emp_feedback'];
                $subject = "Заказчик завершил сотрудничество по проекту и оставил вам отзыв";
                $template = 'finish_fb_frl.tpl.php'; //С отзывом
                if ($project['emp_rating'] == 1 && $frl->is_pro != 't') {
                    $template = 'finish_pos_fb_frl.tpl.php'; //Не-ПРО с положительным отзывом
                }
            }
        } else {
            $recipient = $this->_formatFullname($emp, true);
            $params['frl_login'] = $frl->login;
            $params['frl_fullname'] = $this->_formatFullname($frl);
            $params['opinions_url'] = $GLOBALS['host'].'/users/'.$frl->login.'/opinions/';
            
            $subject = "Исполнитель завершил работу по вашему проекту";
            $template = 'finish_no_fb_emp.tpl.php'; //Без отзыва
            if (isset($project['frl_feedback']) && isset($project['frl_rating'])) {//С отзывом
                $params['rating'] = $project['frl_rating'];
                $params['opinions_url'] = $GLOBALS['host'].'/users/'.$emp->login.'/opinions/';
                $params['text'] = $project['frl_feedback'];
                $subject = "Исполнитель завершил работу по вашему проекту и оставил вам отзыв";
                $template = 'finish_fb_emp.tpl.php'; 
            }
        }
        
        $this->subject = $subject;
        $this->recipient = $recipient;
        $this->message = Template::render(PROJECTS_TPL_MAIL_PATH.$template, $params);
        $ret = $this->send('text/html');
        
        
        //Отправляем СМС
        $status = $project['status'];
        $user_id = ($to_frl) ? $project['exec_id'] : $project['user_id'];

        if ($to_frl && !empty($project['emp_feedback'])) 
        {
            if (($frl->is_pro == 't' && $project['emp_rating'] > 0) || $project['emp_rating'] < 0) $status = 100;
            elseif ($frl->is_pro == 'f' && $project['emp_rating'] > 0) $status = 101;
        }

        ProjectsSms::model($user_id)->sendStatus($status, $project['id']);
        
        
        return $ret;
    }
    
    /**
     * Уведомление второй стороны о новом отзыве
     * 
     * @param type $project
     */
    public function _onFeedback($project, $to_frl = true) {
        $params = array(
            'project_title' => $project['name'],
            'project_url' => $GLOBALS['host'].'/projects/' . $project['id']
        );
        
        $frl = new freelancer();
        $frl->GetUserByUID($project['exec_id']);

        $emp = new employer();
        $emp->GetUserByUID($project['user_id']);
        
        if ($to_frl) { //Письмо отправляем исполнителю
            $recipient = $this->_formatFullname($frl, true);
            $subject = "Заказчик оставил вам отзыв о сотрудничестве в проекте";

            $params['emp_login'] = $emp->login;
            $params['emp_fullname'] = $this->_formatFullname($emp);
            $params['rating'] = $project['emp_rating'];
            $params['opinions_url'] = $GLOBALS['host'].'/users/'.$frl->login.'/opinions/';
            $params['text'] = $project['emp_feedback'];
            $template = 'fb_frl.tpl.php'; //С отзывом
            if ($project['emp_rating'] == 1 && $frl->is_pro != 't') {
                $template = 'pos_fb_frl.tpl.php'; //Не-ПРО с положительным отзывом
            }
        } else {
            $recipient = $this->_formatFullname($emp, true);
            $subject = "Исполнитель оставил вам отзыв о сотрудничестве в проекте";
            
            $params['frl_login'] = $frl->login;
            $params['frl_fullname'] = $this->_formatFullname($frl);
            $params['rating'] = $project['frl_rating'];
            $params['opinions_url'] = $GLOBALS['host'].'/users/'.$emp->login.'/opinions/';
            $params['text'] = $project['frl_feedback'];

            $template = 'fb_emp.tpl.php'; 
        }
        
        $this->subject = $subject;
        $this->recipient = $recipient;
        $this->message = Template::render(PROJECTS_TPL_MAIL_PATH.$template, $params);
        $ret = $this->send('text/html');
        
        
        
        //Отправляем СМС
        if($to_frl && isset($project['emp_feedback']))
        {
            $status = null;
            
            if(($frl->is_pro == 't' && $project['emp_rating'] > 0) || $project['emp_rating'] < 0) 
            {
                $status = (@$project['frl_feedback_id'] > 0)?102:100;
            }
            elseif($frl->is_pro == 'f' && $project['emp_rating'] > 0) 
            {
                $status = 101;
            }
            
            if($status) ProjectsSms::model($frl->uid)->sendStatus($status,$project['id']);
        }
        

        return $ret;
    }
    
    /**
     * Уведомление исполнителю об успешной публикации ранее скрытых отзывов
     * 
     * @param type $frl_id
     */
    public function _onPublicFrl($frl_id) {
        $frl = new freelancer();
        $frl->GetUserByUID($frl_id);

        $this->subject = "Успешно опубликованы ранее скрытые отзывы о сотрудничестве в проектах";
        $this->recipient = $this->_formatFullname($frl, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'public_frl.tpl.php', array(
                'opinions_url' => $GLOBALS['host'].'/users/'.$frl->login.'/opinions/'
            )
        );
        return $this->send('text/html');
    }

    /**
     * Форматтер имени юзера
     * @todo Не лучшее место для этого?
     * 
     * @param type $user
     * @param type $with_email
     * @return type
     */
    protected function _formatFullname(&$user, $with_email = false) {
        $u = (is_object($user)) ? array(
            'uname' => $user->uname,
            'usurname' => $user->usurname,
            'login' => $user->login,
            'email' => $user->email
                ) : $user;

        $fullname = "{$u['uname']}";
        $fullname .= ((empty($fullname)) ? "" : " ") . "{$u['usurname']}";
        $fullname .= (empty($fullname)) ? "{$u['login']}" : " [{$u['login']}]";
        if ($with_email)
            $fullname .= " <{$u['email']}>";
        return $fullname;
    }

}
