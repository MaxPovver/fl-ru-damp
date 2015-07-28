<?php

//$rpath = "../";

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/xajax/projects_status.common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_status.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_helper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_feedback.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_smail.php");

session_start();



//------------------------------------------------------------------------------


function projectDeleteFeedback($feedback_id)
{
    $uid = get_uid(false);
    $objResponse = &new xajaxResponse();

    $feedback_id = intval($feedback_id);
    
    $feedbackModel = new projects_feedback();
    $data = $feedbackModel->getFeedback($feedback_id);
    if(!$data) return $objResponse;      
    
    $is_adm = hasPermissions('projects');
    $is_owner = ($data['user_id'] == $uid);
    
    if(!$is_owner && !$is_adm) return $objResponse; 
    
    $feedbackModel->attributes(array('modified_id' => $uid));
    $ret = $feedbackModel->deleteFeedback($feedback_id);
    if(!$ret) return $objResponse;
    

    $objResponse->script("$('p_stage_{$feedback_id}-3').dispose();");
    $objResponse->script("$('feedback_comment_cont_{$feedback_id}-3').dispose();");;

    $objResponse->script("window.location.reload()");
    
    return $objResponse;
}


//------------------------------------------------------------------------------


function projectUpdateFeedback($params)
{
    $uid = get_uid(false);
    $objResponse = &new xajaxResponse();
    
    $feedback_id = @$params['feedback_id'];
    $feedback_id = intval($feedback_id);
    $feedback = @$params['feedback'];
    
    $feedbackModel = new projects_feedback();
    $data = $feedbackModel->getFeedback($feedback_id);
    if(!$data) return $objResponse;     
    
    $is_adm = hasPermissions('projects');
    $is_owner = ($data['user_id'] == $uid);
    $is_editable = (($data['rating'] < 0) || projects_feedback::isAllowFeedback($data['posted_time']));

    if(!($is_owner && $is_editable) && !$is_adm) return $objResponse; 

    $is_valid = $feedbackModel->attributes(array(
        'feedback' => $feedback,
        'modified_id' => $uid
    ));
    if(!$is_valid) return $objResponse;
    
    $ret = $feedbackModel->updateFeedback($feedback_id);
    if(!$ret) return $objResponse;
    
    $data = $feedbackModel->attributes();
    
    $ele_id = 'form_container_' . $feedback_id . '-3';
    $text_id = 'op_message_' . $feedback_id . '-3';
    
    $objResponse->script("$('$text_id').setStyle('display', 'block');");
    $objResponse->script("$('$ele_id').setStyle('display', 'none');");
    $objResponse->script("$$('.sbrmsgblock').setStyle('display', 'block');");
    $objResponse->assign($text_id, "innerHTML",  '<p>'.reformat($data['feedback'], 30).'</p>');
    $objResponse->assign($ele_id, "innerHTML",  '');

    return $objResponse;    
}


//------------------------------------------------------------------------------



function projectEditFeedback($feedback_id)
{   
    $uid = get_uid(false);
    
    $objResponse = &new xajaxResponse();

    $feedbackModel = new projects_feedback();
    $data = $feedbackModel->getFeedback($feedback_id);
    if(!$data) return $objResponse; 

    $is_adm = hasPermissions('projects');
    $is_owner = ($data['user_id'] == $uid);
    if(!$is_owner && !$is_adm) return $objResponse; 
    
    $content = Template::render(ABS_PATH . '/projects/tpl.feedback-form.php',$data);
    
    $ele_id = 'form_container_'.$feedback_id.'-3';
    $objResponse->script("$$('.editFormSbr').set('html', '&nbsp;').setStyle('display', 'none');");
    $objResponse->script("$$('.sbrmsgblock').setStyle('display', 'block');");
    $objResponse->script("$('form_container_to_{$feedback_id}-3').setStyle('display', 'none');");
    $objResponse->script("$('$ele_id').setStyle('display', 'block');");
    $objResponse->assign($ele_id, "innerHTML", $content);
    
    return $objResponse;      
}



//------------------------------------------------------------------------------


/**
 * Смена статуса проекта
 * 
 * @param array $params
 * @return \xajaxResponse
 */
function changeProjectStatus($params)
{
    $objResponse = &new xajaxResponse();
    
    $uid = get_uid(false);
    $project_id = intval(@$params['project_id']);
    $status = @$params['status'];
    $feedback = @$params['feedback'];
    $rating = @$params['rating'];
    $hash = @$params['hash'];
    unset($params['hash'], $params['u_token_key'], $params['feedback'], $params['rating']);
    $current_hash = projects_helper::getStatusHash($params);
    if(!($uid > 0) || ($hash !== $current_hash)) return $objResponse;
    
    // Проект.
    $obj_project = new projects();
    //$project = $obj_project->GetPrjCust($project_id);
    //Сделал отдельный метод получающий только то что нам нужно
    //лучше так делать а то далеко не всегда существующие методы оптимальны
    //в них выбирается много лишнего
    $project = $obj_project->getProjectWithFeedback($project_id);
    if(!$project || !in_array($project['kind'], array(1,5,9))) return $objResponse;

    $is_project_owner = ($project['user_id'] == $uid);
    $is_exec = ($project['exec_id'] == $uid);
    if(!($is_project_owner || $is_exec)) return $objResponse; 
    
    //Сохраним текущий статус
    $old_status = $project['status'];
    $is_emp = is_emp();
    $attr = array(
        'is_emp' => $is_emp,
        'project' => $project
    );
    $offer = array();
    
    if($project['exec_id'])
    {
        $obj_offer = new projects_offers();
        $offer = $obj_offer->GetPrjOffer($project['id'], $project['exec_id']);
        if(!$offer) return $objResponse;
        $attr['offer'] = $offer;    
    }

    $projectsStatus = new projects_status();
    $projectsStatus->attributes($attr);
    $projectsStatus->changeStatus($status);
    $project = $projectsStatus->getProject();
    $offer = $projectsStatus->getOffer();

    
    if($project['status'] > projects_status::STATUS_ACCEPT && !empty($feedback))
    {
        //Время вышло
        if(!projects_feedback::isAllowFeedback($project['close_date'])) return $objResponse;

        //Если отзыв от работодателя и положительный то в зависимости от статуса ПРО фрилансера отзыв скрывается либо публикуется
        //Если отзыв от фрилансера то всегда публикуется
        $is_show = ($is_emp && $rating > 0)?($offer['is_pro'] == 't'):TRUE;
        
        $obj_feedback = new projects_feedback();
        $is_valid = $obj_feedback->attributes(array(
            'feedback' => $feedback,
            'rating' => $rating,
            'is_emp' => $is_emp,
            'user_id' => $uid,
            'show' => $is_show,
            'touser_id' => ($is_emp)?$project['exec_id']:$project['user_id']
        ));
        
        if(!$is_valid || !$obj_feedback->addFeedback($project_id)) return $objResponse;
        
        $prefix = ($is_emp)?'emp':'frl';
        $attributes = $obj_feedback->attributes();   
        $project[$prefix . '_feedback'] = $attributes['feedback'];
        $project[$prefix . '_rating'] = $attributes['rating'];      
    }
    
    
    if($project['status'] > projects_status::STATUS_ACCEPT)
    {
        // Отправляем письма об отзывах
        $mes = new projects_smail();
        if($old_status != $project['status']) { //была смена статуса на закрытие
            $mes->onFinish($project, $is_emp);
        } else {
            $mes->onFeedback($project, $is_emp);
        } 
    }
    
    
    
    if($project['status'] == projects_status::STATUS_EMPCLOSE)
    {
        //$objResponse->remove('project_public_agane')->remove('project_edit');
        $objResponse->script("$$('.__project_close_hide').destroy();");
    }

    $sHtml = projects_helper::renderStatus($project, $offer);
    $objResponse->assign('project_status_'.$project_id,'innerHTML',$sHtml);
    
    
	if ($project['kind'] == 9 && $offer['status'] == projects_status::STATUS_DECLINE) {
		$objResponse->script("window.location.reload()");
	}

    return $objResponse;
}



$xajax->processRequest();