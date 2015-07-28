<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/classes/template.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_status.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_feedback.php';

define('PORJECTS_TPL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/projects/');

class projects_helper 
{
    private static $_urls = array(
        'change_status' => '/project/%d/status/%s/%s/'
    );
    
    
    /**
     * Отображает форму отзыва
     * 
     * @global type $js_file
     * @param int $status
     * @return string
     */
    public static function renderFeedback($status)
    {
        global $js_file;
        $js_file[] = '/scripts/projects/projects_feedback.js';
        $js_file[] = '/scripts/mootools-form-validator.js';
        
        return Template::render(PORJECTS_TPL_PATH . 'projects_feedback.tpl.php',array());
    }

    
    //--------------------------------------------------------------------------
    
    
    /**
     * Отображает статус по умолчанию для всех остальных
     * 
     * @param array $exec_info
     * @return boolean
     */
    public static function renderGuestStatus($exec_info)
    {
        if(!$exec_info) return FALSE;
        return Template::render(PORJECTS_TPL_PATH . 'projects_guest_status.tpl.php',array(
            'exec_info' => $exec_info
        ));        
    }
    

    //--------------------------------------------------------------------------
    
    /**
     * Отображает текущий статус проекта
     * 
     * @param array $project
     * @param array $offer
     * @return boolean
     */
    public static function renderStatus($project, $offer)
    {
        $uid = get_uid(FALSE);
        $is_project_owner = ($project['user_id'] == $uid);
        $is_offer_owner = ($offer)?($offer['user_id'] == $uid):FALSE;
        $is_adm = hasPermissions('projects') && !$is_project_owner && !$is_offer_owner;
        $is_exec = ($offer)?$project['exec_id'] == $offer['user_id']:FALSE;
        //Если не владелец проекта или предложения или не админ то показываем статус по умолчанию 
        $is_guest = (!($uid > 0) || (!$is_project_owner && !$is_offer_owner));
        //Если фрилансер не исполнитель и нет движухи по статусу то ничего не показываем
        $is_frl_status_new = ($is_offer_owner && !$is_exec && $offer['status'] == projects_status::STATUS_NEW);
        if(($is_guest || $is_frl_status_new) && !$is_adm) return FALSE;
        
        if(!isset($project['emp_feedback']) || !isset($project['frl_feedback']))
        {
            $obj_feedback = new projects_feedback();
            $project += $obj_feedback->getFeedbackByProjectID($project['id']);
        }
        
        $is_allow_feedback = (!$project['close_date'] || projects_feedback::isAllowFeedback($project['close_date']));

        $fullname = ($is_project_owner && $offer)?
                "{$offer['uname']} {$offer['usurname']} [{$offer['login']}]":
                "{$project['uname']} {$project['usurname']} [{$project['login']}]";
    
                
        $date_feedback = ($project['close_date'] ? strtotime($project['close_date']) : time()) + projects_feedback::LIFETIME;
        $date_feedback_formatted = date("d.m.Y H:i", $date_feedback);
                
        return Template::render(PORJECTS_TPL_PATH . 'projects_status.tpl.php',array(
            'fullname' => $fullname,
            
            'project' => $project,
            'offer' => $offer,
            'is_exec' => $is_exec,
            'is_adm' => (!$is_project_owner && !$is_offer_owner),
            'date_feedback' => $date_feedback_formatted,
            'is_allow_feedback' => $is_allow_feedback
        ));
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Генерируем хеш параметров
     * 
     * @param array $params
     * @param int $uid
     * @return string - md5
     */
    public static function getStatusHash($params, $uid = NULL)
    {
        if(!$uid) $uid = @$_SESSION['uid'];
        return md5(projects_status::SOLT . serialize(array_values($params)) . $uid);
    }    
    
    
    //--------------------------------------------------------------------------    
    
    
    /**
     * Генерирует строку с параметрами для JS-функции смены статуса проекта
     * 
     * @param int $project_id
     * @param string $status
     * @return string
     */
    public static function getJsParams($project_id, $status)
    {
        $params = array(
            'project_id' => (string)$project_id,
            'status' => $status
        );
        
        $hash = self::getStatusHash($params);
        return sprintf("%d,'%s','%s'", $project_id, $status, $hash);
    }
    
    
    //--------------------------------------------------------------------------
    
    
    /**
     * Генерирует строку с параметрами для JS-функции для закрытия и отправки отзыва
     * 
     * @param int $project_id
     * @param boolean $is_close
     * @param int $rating
     * @return string
     */
    public static function getJsCloseParams($project_id, $is_close = false, $rating = 0)
    {
        $params = array(
            'project_id' => (string)$project_id,
            'status' => 'close'
        );
        
        $is_close = $is_close?'true':'false';
        $hash = self::getStatusHash($params);
        return sprintf("%d,'%s',%s,%d", $project_id, $hash, $is_close, $rating);
    }
    
    
    //--------------------------------------------------------------------------
    
    
    /**
     * Ссылка смены статуса проекта
     * 
     * @param int $project_id
     * @param string $status
     * @param int $uid
     * @return string
     */
    public static function getStatusUrl($project_id, $status, $uid = NULL)
    {
        $params = array('project_id' => $project_id,'status' => $status);
        $hash = static::getStatusHash($params, $uid);
        return sprintf(static::url("change_status"),$project_id,$status,$hash);
    }
    
    
    //--------------------------------------------------------------------------
    
    
    
    /**
     * Вернуть URI
     * 
     * @param type $key
     * @return type
     */
    public static function url($key)
    {
        return @self::$_urls[$key];
    }
    
    
    
    
    //--------------------------------------------------------------------------
    
    
    
    /**
     * Выводим несколько вариантов попапов в зависимости от параметров
     * 
     * @param type $params
     * @return type
     */
    public static function renderAnswerPopup($params = array())
    {
        return Template::render(PORJECTS_TPL_PATH . 'project_answer_popup.tpl.php', $params);
    }
    
    
    
    
}