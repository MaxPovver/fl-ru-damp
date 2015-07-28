<?
// так же в /projects/index.php
define('MAX_WORKS_IN_LIST', 30);

$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects_ci.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_status.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_helper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_sms.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');

/**
 * Установка исполнителя проекта
 *
 * @param integer $po_id           id предложения к проекту
 * @param unknown_type $prj_id     id проекта
 * @param unknown_type $user_id    id юзера-исполнителя
 * @return                         xajax response
 */
function SelectProjectExecutor($po_id, $prj_id, $user_id, $type, $exec_po_id = 0)
{
    session_start();

    $user      = new users();
    $prj       = new projects;
    $prj_offer = new projects_offers;

    $po_id      = intval($po_id);
    $prj_id     = intval($prj_id);
    $exec_po_id = intval($exec_po_id);
    $user_id    = intval($user_id);
    $user_name  = $user->GetName($user_id, $error);

    $emp_id     = get_uid(false);
    $emp_name   = $user->GetName($emp_id, $error);

    $objResponse = new xajaxResponse();
    
    $pod = new projects_offers_dialogue();
    $pod->markReadEmp(array($po_id), $emp_id);
    //Не позволяем производить действия с заблокированным проектом
    if (projects::CheckBlocked(intval($prj_id))) {
        $objResponse->script("document.location.href='/projects/index.php?pid=".intval($prj_id)."'");
        return $objResponse;
    }

    $project = $prj->GetPrj($emp_id, $prj_id, 1);
    
    if(tservices_helper::isAllowOrderReserve())
    {
        //@todo: отправляем на форму нового заказа на базе проекта для текущего предложения фрилансера
        $objResponse->script("document.location.href='/new-project-order/{$po_id}/'");
        return $objResponse;
    }

    if($error = $prj->SetExecutor($prj_id, $user_id, $emp_id)) {
        $objResponse->alert($error);
        return $objResponse;
    }
    $project['exec_id'] = $user_id;
    
    
    //Отправляем уведомления участникам сделки
    $smail = new projects_smail();
    $smail->onSetExecutorFrl($project);
    $smail->onSetExecutorEmp($project);
    
    //Отправляем СМС уведомление
    ProjectsSms::model($project['exec_id'])->sendStatus($project['status'], $project['id'], $project['kind']);
    
    
    require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/base.php");
    require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/api.php");
    require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/mobile.php");
    externalApi_Mobile::addPushMsg($user_id, 'prj_select_performer', array('from_user_id'=>$project['user_id'], 'name'=>$project['name'], 'project_id'=>$project['id']));


    // Если находимся в "Не определен", тогда предыдущий исполнитель (если он был) сам попадает в "Не определен".
    // Перезагружаем в этом случае страницу.
    if($type == 'o' && $project['exec_id']) {
        $objResponse->script("document.location.href=document.location.href.replace(/(&v=\d*)?#offers$/,'&v=".mt_rand(1,99999)."#offers')");
        return $objResponse;
    }

    list($po_offers_count, $msg_offers_count) = $prj_offer->CountPrjOffers($prj_id, 'offers');
    $objResponse->assign("po_offers_count", "innerHTML", $po_offers_count);
    if ($msg_offers_count > 0) {
        $objResponse->assign("op_count_offers_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_offers_count .' '. ending($msg_offers_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
    } else {
        $objResponse->assign("op_count_offers_new_msgs", "innerHTML", '');
        if ($type == 'o') {
    		    $objResponse->remove('sort_box');
        }
    }

    list($po_executor_count, $msg_executor_count) = $prj_offer->CountPrjOffers($prj_id, 'executor');
    $objResponse->assign("po_executor_count", "innerHTML", $po_executor_count);
    if ($msg_executor_count > 0) {
        $objResponse->assign("op_count_executor_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_executor_count .' '. ending($msg_executor_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
    } else {
        $objResponse->assign("op_count_executor_new_msgs", "innerHTML", '');
        if ($type == 'i') {
    		    $objResponse->remove('sort_box');
        }
    }

    list($po_candidate_count, $msg_candidate_count) = $prj_offer->CountPrjOffers($prj_id, 'candidate');
    $objResponse->assign("po_candidate_count", "innerHTML", $po_candidate_count);
    if ($msg_candidate_count > 0) {
        $objResponse->assign("op_count_candidate_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_candidate_count .' '. ending($msg_candidate_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
    } else {
        $objResponse->assign("op_count_candidate_new_msgs", "innerHTML", '');
        if ($type == 'c') {
    		    $objResponse->remove('sort_box');
        }
    }

    list($po_refuse_count, $msg_refuse_count) = $prj_offer->CountPrjOffers($prj_id, 'refuse');
    $objResponse->assign("po_refuse_count", "innerHTML", $po_refuse_count);
    if ($msg_refuse_count > 0) {
        $objResponse->assign("op_count_refuse_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_refuse_count .' '. ending($msg_refuse_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
    } else {
        $objResponse->assign("op_count_refuse_new_msgs", "innerHTML", '');
        if ($type == 'r') {
    		    $objResponse->remove('sort_box');
        }
    }

    if ($exec_po_id > 0) {
        //$objResponse->assign("po_b_exec_" . $exec_po_id, "innerHTML", '<a id="po_img_exec_' . $exec_po_id . '" class="b-button-multi__link" onclick="xajax_SelectProjectExecutor(' . $exec_po_id . ', ' . $prj_id . ', ' . $user_id . ', ' . "'" . $type . "'" . ', ' . $po_id . ');" href="javascript:void(0)" title="Буду работать с этим человеком."><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_green"></span><span class="b-button-multi__txt">Исполнитель</span></span></a>');
    }
    //$objResponse->assign("po_b_exec_" . $po_id, "innerHTML", '<a id="po_img_exec_' . $po_id . '" class="b-button-multi__link" href="javascript:void(0)" title="Буду работать с этим человеком."><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_green"></span><span class="b-button-multi__txt">Исполнитель</span></span></a>');
    //$objResponse->assign("po_b_select_" . $po_id, "innerHTML", '<a id="po_img_select_' . $po_id . '" class="b-button-multi__link" onclick="xajax_SelectProjectOffer(' . $po_id . ', ' . $prj_id . ', ' . $user_id . ', ' . "'" . $type . "'" . ');" href="javascript:void(0)" title="Прошел предварительный отбор. Может быть исполнителем"><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_blue"></span><span class="b-button-multi__txt">Кандидат</span></span></a>');
   // $objResponse->assign("po_b_refuse_" . $po_id, "innerHTML", '<a id="po_img_refuse_' . $po_id . '" class="b-button-multi__link" onclick="show_fpopup(' . "'po_b_refuse_" . $po_id . "', 'po_m_refuse_" . $po_id . "'" . ');" href="javascript:void(0)" title="Этот человек мне не подходит. Может быть в следующий раз."><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_red"></span><span class="b-button-multi__txt">Отказать</span></span></a>');

    $objResponse->script("removeNoteBar('{$user_name['login']}');");
    $objResponse->remove("po_" . $po_id);
    $objResponse->remove("po_u_" . $po_id);
    $objResponse->remove("po_bar_" . $po_id);
    
    
    $offer = $prj_offer->GetPrjOffer($project['id'], $project['exec_id']);
    
    $sHtml = projects_helper::renderStatus($project, $offer);
    $objResponse->assign('project_status_'.$prj_id,'innerHTML',$sHtml);

    $_SESSION['offers_on_page']--;
    if ($_SESSION['offers_on_page'] == 0)
    {
        $objResponse->script("document.location.href='/projects/index.php?pid=".intval($prj_id)."&type=".$type."'");
    }

    return $objResponse;
}

/**
* Добавляет жалобу на проект
*
* @param    integer     $project_id     ID проекта
* @param    integer     $user_id        ID пользователя
* @param    integer     $type           тип жалобы
* @param    string      $msg            текст жалобы
* @param    string      $files          имена загруженных скриншотов
* @return                               xajax responce
*/
function SendComplain($project_id, $type, $msg, $files) {
	global $session;
	session_start();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_complains.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/userecho.php");

    $objResponse = new xajaxResponse();
    
    $project_id = (int)$project_id;
    $user_id = get_uid(false);
    $type = (int)$type;
    $msg = __paramValue('htmltext', $msg);
    $error = false;
    
    $project = new projects();
    $prj = $project->GetPrj(0, $project_id, 1);
    
    $file_list = array();
    if($files != '') {
        $files   = uploader::sgetFiles($files);
        if(!empty($files)) {
            $emp = new users();
            $emp->GetUser($emp->GetField($prj['user_id'],$ee,'login')); 
            $dir = 'users/' . substr($emp->login, 0, 2). '/'. $emp->login . "/upload/";
            foreach($files as $file) {
                $copy = uploader::remoteCopy($file['id'], 'file_projects', $dir);
                $rfiles[] = $copy->name;
                $file_list[] = array(
                    'name' => $copy->original_name,
                    'link' => WDCPREFIX . '/' . $copy->path . $copy->name
                );
            }
            $files = implode(",", $rfiles);
        } else {
            $files = "";
        }
    }
    if(!$files) $files = "";
    
    if(projects::IsHaveComplainType($project_id, $user_id, $type)) { // Уже жаловался
        return $objResponse;
    }
    
    $projects_complains = new projects_complains();
    $type_name = $projects_complains->GetComplainType($type);
    $project_url = getAbsUrl(getFriendlyURL('project', $project_id));
    
    $is_moder = $projects_complains->isComplainTypeModer($type);
    if ($is_moder) {
        $userEcho = new UserEcho();
        $topic_message = $userEcho->constructMessage($project_url, $prj['name'], $msg, $file_list);
        $topicUrl = $userEcho->newTopicComplain($type_name, $topic_message, $file_list);
        if ($topicUrl) {
            messages::sendProjectComplain($user_id, $project_url, $prj['name'], $msg, $topicUrl);
        } else {
            $error = true;
        }
    }
    
    if (!$error) {
        $error = projects::AddComplain($project_id, $user_id, $type, $msg, $files, $is_moder && $topicUrl);
    }
    
    if ($error) {
        
        $objResponse->script("$('abuse_project_popup').toggleClass('b-shadow_hide');");
        
        if ($is_moder) {
            $objResponse->script("$('abuse-cause-error').removeClass('b-layout__txt_hide'); abuseResetSelection();");
        } else {
            $objResponse->script("$$('.abuse-btn-send').removeClass('b-button_rectangle_color_disable')");
        }
        
    } else {
        $upl = array(
            'umask' => uploader::umask('prj_abuse'),
            'validation' => array('allowedExtensions' => array('jpg', 'gif', 'png', 'jpeg'), 'restrictedExtensions' => array()),
            'text' => array('uploadButton' => iconv('cp1251', 'utf8', 'Прикрепить файлы'))
        );
        
        $objResponse->script("
            $('abuse{$type}').addClass('abuse-checked');
            $('abuse{$type}').getChildren().each(function(el) { $(el).addClass('abuse-checked'); });
        ");
        $objResponse->script("uploader.create('abuse_uploader', ".json_encode($upl).");");
        $objResponse->script("$('prj_abuse_msg').set('value', '')");
        $objResponse->script("$$('.abuse-btn-send').removeClass('b-button_disabled')");
        $objResponse->script("$('abuse_project_popup').toggleClass('b-shadow_hide');");
        $objResponse->script("$('project_abuse_success').removeClass('b-layout__txt_hide');");
        $objResponse->script("$('form_abuse').hide();");
        $objResponse->script("setTimeout(\"$('project_abuse_success').addClass('b-layout__txt_hide')\", 5000);");
        
        if ($is_moder) {
            $objResponse->script("$('abuse-cause-error').addClass('b-layout__txt_hide');");
        }
    }
    return $objResponse;
}

function SelectProjectOffer($po_id, $prj_id, $user_id, $type)
{
	global $session;
	session_start();

	$user      = new users();
	$prj       = new projects;
	$prj_offer = new projects_offers;

	$po_id      = intval($po_id);
	$prj_id     = intval($prj_id);
	$user_id    = intval($user_id);
	$user_name  = $user->GetName($user_id, $error);

	$emp_id     = get_uid(false);
	$emp_name   = $user->GetName($emp_id, $error);
    
    $pod = new projects_offers_dialogue();
    $pod->markReadEmp(array($po_id), $emp_id);
    
	$objResponse = new xajaxResponse();

    //Не позволяем производить действия с заблокированным проектом
    if (projects::CheckBlocked(intval($prj_id)))
    {
        $objResponse->script("document.location.href='/projects/index.php?pid=".intval($prj_id)."'");
    }
    else
    {

    $error = '';
    $project = $prj->GetPrjCust($prj_id);
    if ($project['exec_id'] == $user_id) {
        $error  = $prj->ClearExecutor($prj_id, $emp_id);
    }
	if (!$error)
	{
  	$error .= ($error?' ':'') . $prj_offer->SetSelected($po_id, $prj_id, $user_id, true);

        require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/base.php");
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/api.php");
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/mobile.php");
        externalApi_Mobile::addPushMsg($user_id, 'prj_select_candidate', array('from_user_id'=>$project['user_id'], 'name'=>$project['name'], 'project_id'=>$project['id']));

		list($po_offers_count, $msg_offers_count) = $prj_offer->CountPrjOffers($prj_id, 'offers');
		$objResponse->assign("po_offers_count", "innerHTML", $po_offers_count);
		if ($msg_offers_count > 0) {
		    $objResponse->assign("op_count_offers_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_offers_count .' '. ending($msg_offers_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
		} else {
		    $objResponse->assign("op_count_offers_new_msgs", "innerHTML", '');
		    if ($type == 'o') {
    		    $objResponse->remove('sort_box');
		    }
		}

		list($po_executor_count, $msg_executor_count) = $prj_offer->CountPrjOffers($prj_id, 'executor');
		$objResponse->assign("po_executor_count", "innerHTML", $po_executor_count);
		if ($msg_executor_count > 0) {
		    $objResponse->assign("op_count_executor_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_executor_count .' '. ending($msg_executor_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
		} else {
		    $objResponse->assign("op_count_executor_new_msgs", "innerHTML", '');
		    if ($type == 'i') {
    		    $objResponse->remove('sort_box');
		    }
		}

	    list($po_candidate_count, $msg_candidate_count) = $prj_offer->CountPrjOffers($prj_id, 'candidate');
		$objResponse->assign("po_candidate_count", "innerHTML", $po_candidate_count);
		if ($msg_candidate_count > 0) {
		    $objResponse->assign("op_count_candidate_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_candidate_count .' '. ending($msg_candidate_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
		} else {
		    $objResponse->assign("op_count_candidate_new_msgs", "innerHTML", '');
		    if ($type == 'c') {
    		    $objResponse->remove('sort_box');
		    }
		}

	    list($po_refuse_count, $msg_refuse_count) = $prj_offer->CountPrjOffers($prj_id, 'refuse');
		$objResponse->assign("po_refuse_count", "innerHTML", $po_refuse_count);
		if ($msg_refuse_count > 0) {
		    $objResponse->assign("op_count_refuse_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_refuse_count .' '. ending($msg_refuse_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
		} else {
		    $objResponse->assign("op_count_refuse_new_msgs", "innerHTML", '');
		    if ($type == 'r') {
    		    $objResponse->remove('sort_box');
		    }
		}

		//$objResponse->assign("po_b_exec_" . $po_id, "innerHTML", '<a id="po_img_exec_' . $po_id . '" class="b-button-multi__link" onclick="xajax_SelectProjectExecutor(' . $po_id . ', ' . $prj_id . ', ' . $user_id . ', ' . "'" . $type . "'" . ', ' . 0 . ');" href="javascript:void(0)" title="Буду работать с этим человеком."><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_green"></span><span class="b-button-multi__txt">Исполнитель</span></span></a>');
		//$objResponse->assign("po_b_select_" . $po_id, "innerHTML", '<a id="po_img_select_' . $po_id . '" class="b-button-multi__link"  href="javascript:void(0)" title="Прошел предварительный отбор. Может быть исполнителем"><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_blue"></span><span class="b-button-multi__txt">Кандидат</span></span></a>');
		//$objResponse->assign("po_b_refuse_" . $po_id, "innerHTML", '<a id="po_img_refuse_' . $po_id . '" class="b-button-multi__link" onclick="show_fpopup(\'po_b_refuse_' . $po_id . '\', \'po_m_refuse_' . $po_id . '\');" href="javascript:void(0)" title="Этот человек мне не подходит.  Может быть в следующий раз."><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_red"></span><span class="b-button-multi__txt">Отказать</span></span></a>');

  $objResponse->script("removeNoteBar('{$user_name['login']}');");
		$objResponse->remove("po_" . $po_id);
        $objResponse->remove("po_u_" . $po_id);
        $objResponse->remove("po_bar_" . $po_id);


	}

        $_SESSION['offers_on_page']--;
        if ($_SESSION['offers_on_page'] == 0)
        {
            $objResponse->script("document.location.href='/projects/index.php?pid=".intval($prj_id)."&type=".$type."'");
        }
    }

    return $objResponse;
}

function RefuseProjectOffer($po_id, $prj_id, $user_id, $type, $po_reason = 0)
{
	global $session;
	session_start();

	$user      = new users();
	$prj       = new projects;
	$prj_offer = new projects_offers;

	$po_id     = intval($po_id);
	$prj_id    = intval($prj_id);
	$po_reason = intval($po_reason);
	$user_id   = intval($user_id);
	$user_name = $user->GetName($user_id, $error);

	$emp_id   = get_uid(false);
	$emp_name = $user->GetName($emp_id, $error);
    
    $pod = new projects_offers_dialogue();
    $pod->markReadEmp(array($po_id), $emp_id);
    
	$objResponse = new xajaxResponse();

    //Не позволяем производить действия с заблокированным проектом
    if (projects::CheckBlocked(intval($prj_id)))
    {
        $objResponse->script("document.location.href='/projects/index.php?pid=".intval($prj_id)."'");
    }
    else
    {

    $error = '';
    $project = $prj->GetPrjCust($prj_id);
    if ($project['exec_id'] == $user_id) {
        $error  = $prj->ClearExecutor($prj_id, $emp_id);
    }
	if (!$error)
	{
	  $error .= ($error?' ':'') . $prj_offer->SetRefused($po_id, $prj_id, $user_id, $po_reason, true);

        $project = $prj->GetPrjCust($prj_id);
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/base.php");
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/api.php");
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/mobile.php");
        externalApi_Mobile::addPushMsg($user_id, 'prj_select_reject', array('from_user_id'=>$project['user_id'], 'name'=>$project['name'], 'project_id'=>$project['id']));

		list($po_offers_count, $msg_offers_count) = $prj_offer->CountPrjOffers($prj_id, 'offers');
		$objResponse->assign("po_offers_count", "innerHTML", $po_offers_count);
		if ($msg_offers_count > 0) {
		    $objResponse->assign("op_count_offers_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_offers_count .' '. ending($msg_offers_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
		} else {
		    $objResponse->assign("op_count_offers_new_msgs", "innerHTML", '');
		    if ($type == 'o') {
    		    $objResponse->remove('sort_box');
		    }
		}

	    list($po_executor_count, $msg_executor_count) = $prj_offer->CountPrjOffers($prj_id, 'executor');
		$objResponse->assign("po_executor_count", "innerHTML", $po_executor_count);
		if ($msg_executor_count > 0) {
		    $objResponse->assign("op_count_executor_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_executor_count .' '. ending($msg_executor_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
		} else {
		    $objResponse->assign("op_count_executor_new_msgs", "innerHTML", '');
		    if ($type == 'i') {
    		    $objResponse->remove('sort_box');
		    }
		}

		list($po_candidate_count, $msg_candidate_count) = $prj_offer->CountPrjOffers($prj_id, 'candidate');
		$objResponse->assign("po_candidate_count", "innerHTML", $po_candidate_count);
		if ($msg_candidate_count > 0) {
		    $objResponse->assign("op_count_candidate_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_candidate_count .' '. ending($msg_candidate_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
		} else {
		    $objResponse->assign("op_count_candidate_new_msgs", "innerHTML", '');
		    if ($type == 'c') {
    		    $objResponse->remove('sort_box');
		    }
		}

		list($po_refuse_count, $msg_refuse_count) = $prj_offer->CountPrjOffers($prj_id, 'refuse');
		$objResponse->assign("po_refuse_count", "innerHTML", $po_refuse_count);
		if ($msg_refuse_count > 0) {
		    $objResponse->assign("op_count_refuse_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_refuse_count .' '. ending($msg_refuse_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
		} else {
		    if ($type == 'r') {
    		    $objResponse->remove('sort_box');
		    }
		    $objResponse->assign("op_count_refuse_new_msgs", "innerHTML", '');
		}

		//$objResponse->assign("po_b_exec_" . $po_id, "innerHTML", '<a id="po_img_exec_' . $po_id . '" class="b-button-multi__link" onclick="xajax_SelectProjectExecutor(' . $po_id . ', ' . $prj_id . ', ' . $user_id . ', ' . "'" . $type . "'" . ', ' . 0 . ');" href="javascript:void(0)" title="Буду работать с этим человеком."><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_green"></span><span class="b-button-multi__txt">Исполнитель</span></span></a>');
		//$objResponse->assign("po_b_select_" . $po_id, "innerHTML", '<a id="po_img_select_' . $po_id . '" class="b-button-multi__link" onclick="xajax_SelectProjectOffer(' . $po_id . ', ' . $prj_id . ', ' . $user_id  . ', ' . "'" . $type . "'" . ');" href="javascript:void(0)" title="Прошел предварительный отбор. Может быть исполнителем"><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_blue"></span><span class="b-button-multi__txt">Кандидат</span></span></a>');
		//$objResponse->assign("po_b_refuse_" . $po_id, "innerHTML", '<a id="po_img_refuse_' . $po_id . '" class="b-button-multi__link"  href="javascript:void(0)" title="Этот человек мне не подходит.  Может быть в следующий раз."><span class="b-button-multi__inner"><span class="b-button-multi__icon b-button-multi__icon_red"></span><span class="b-button-multi__txt">Отказано</span></span></a>');

  $objResponse->script("removeNoteBar('{$user_name['login']}');");
		$objResponse->remove("po_" . $po_id);
        $objResponse->remove("po_u_" . $po_id);
        $objResponse->remove("po_bar_" . $po_id);

}

        $_SESSION['offers_on_page']--;
        if ($_SESSION['offers_on_page'] == 0)
        {
            $objResponse->script("document.location.href='/projects/index.php?pid=".intval($prj_id)."&type=".$type."'");
        }
    }

    return $objResponse;
}

function AddDialogueMessage($form)
{
    global $session;
    session_start();

    
    $objResponse = new xajaxResponse();
    $offerIsBlocked = projects_offers::isOfferBlocked(false, get_uid(), $form['prj_id']);
    if ($offerIsBlocked) {
        $objResponse->alert("Ваше предложение заблокировано, вы не можете отправить это сообщение");        
        return $objResponse;
    }
    $prj = new projects();    
    $project = $prj->GetPrjCust(intval($form['prj_id']));    

    $is_pro = is_pro();
    if($project['pro_only']=='t' && !$is_pro && !is_emp() && !hasPermissions('projects')) {
        if($project['kind']==7) {
            if(contest::IsContestOfferExists($project['id'], get_uid(false))) { $is_pro = true; }
        } else {
            if(projects_offers::IsPrjOfferExists($project['id'], get_uid(false))) { $is_pro = true; }
        }
    }

    if($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=get_uid() && !hasPermissions('projects')) {
        $objResponse->alert("Данная функция доступна только пользователям с аккаунтом PRO.");
        $objResponse->script("$('savebtn').set('disabled', false);");
        return $objResponse;
    } elseif ($project['verify_only'] == 't' && !($_SESSION['is_verify'] == 't') && $project['user_id']!=get_uid() && !hasPermissions('projects')) {
        $objResponse->alert("Данная функция доступна только верифицированным пользователям.");
        $objResponse->script("$('savebtn').set('disabled', false);");
        return $objResponse;
    }

    if(!trim($form['po_text'])) {
        $objResponse->alert("Невозможно отправить пустое сообщение.");
        $objResponse->script("
            $('savebtn').set('disabled', false);
        ");
        return $objResponse;
    }
    if (!is_emp() && $form['from'] == 'emp') {
        $objResponse->script("
            $('savebtn').set('disabled', false);
        ");
        $objResponse->alert("Невозможно отправить сообщение. Вы вышли из аккаунта работодателя.");
        return $objResponse;
    } elseif (is_emp() && $form['from'] == 'frl') {
        $objResponse->script("
            $('savebtn').set('disabled', false);
        ");
        $objResponse->alert("Невозможно отправить сообщение. Вы вышли из аккаунта фрилансера.");
        return $objResponse;
    }
    
    //Не позволяем производить действия с заблокированным проектом
    if (projects::CheckBlocked(intval($form['prj_id'])))
    {
        $objResponse->script("document.location.href='/projects/index.php?pid=".intval($form['prj_id'])."'");
    }
    elseif (intval($_SESSION['uid']))
    {
	$po_id = intval($form['po_id']);
	
	//$po_text = substr(change_q_x($form['po_text'], false), 0, 1000);
	$po_text = antispam(trim($form['po_text']));
	$po_text = preg_replace("/(\r\n|\r|\n){3,100}/i", "\r\n\r\n", $po_text);
	$po_commentid = intval($form['po_commentid']);
	$user_id = get_uid(false);
	$user = new users();
	$user_name = $user->GetName($user_id, $error);

	$pod = new projects_offers_dialogue;
	$project_dialogue = $pod->GetDialogueForOffer($po_id);
	$project = $pod->GetProjectFromDialogue($po_id);

	if (count($project_dialogue)) {
		for ($i=count($project_dialogue)-1; $i>=0; $i--) {
			if ($project_dialogue[$i]['user_id'] != $user_id) {
				$to_user_name = $project_dialogue[$i]['login'];
				break;

			}
		}
	}

	if (is_emp()) {
	    $emp_read = true;
	    $frl_read = false;
	}
	else {
	    $emp_read = false;
	    $frl_read = true;
	}

	if (!$po_commentid)
	{
		$error = $pod->AddDialogueMessage($po_id, $user_id, $po_text, $frl_read, $emp_read);
		$last_comment = $pod->GetLastDialogueMessage($user_id, $po_id);
		$objResponse->script("last_commentid={$last_comment};");
		$objResponse->script("edit_block[$po_id] = '&nbsp;&nbsp;<span><a href=\"javascript:void(null)\" onClick=\"answer($po_id, $last_comment);markRead(\'$po_id\');\" class=\"internal\">Редактировать</a></span>';");
//		$objResponse->script("alert(last_commentid);");
//		$objResponse->script("alert(edit_block);");
	}
	else
	{
		$error = $pod->SaveDialogueMessage($user_id, $po_text, $po_commentid, $po_id, false);

		if ($error == 1)
		{
			$objResponse->alert("Вы не можете редактировать комментарий, так как на него уже ответили.");
			return $objResponse;
		}
	}

	$po_text = rtrim(ltrim($po_text, "\r\n"));
	$po_text = substr(change_q_x($po_text, false, true, '', false, false), 0, 1000);
	$po_text = stripslashes($po_text);

	if ($error == '')
	{
        $sPostText = $po_text;
        
        if ( $project['kind'] != 4 ) {
            $sId      = $po_commentid ? $po_commentid : $last_comment;
            $aComment = $pod->getDialogueMessageById( $sId );
            
            if ( $aComment['moderator_status'] === '0' ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

                $stop_words = new stop_words( hasPermissions('projects') );
                $sPostText  = $stop_words->replace( $sPostText );
            }
        }
        
		if (!$po_commentid)
		{
			$objResponse->append("po_dialogue_talk_" . $po_id, "innerHTML", "<div style=\"margin-bottom:8px;font-size:100%;\"><span class=\"" . (is_emp()?'emp':'frl') . "name11\"><a href=\"/users/" . get_login($user_id) . "/\" class=\"" . (is_emp()?'emp':'frl') . "name11\" title=\"" . $user_name['uname'] . " ". $user_name['usurname'] . "\">" . $user_name['uname'] . " " . $user_name['usurname'] . "</a> [<a href=\"/users/" . $user_name['login'] . "/\" class=\"" . (is_emp()?'emp':'frl') . "name11\" title=\"" . $user_name['login'] . "\">" . $user_name['login'] . "</a>]</span> <span id=\"po_date_".$last_comment."\">[" . strftime("%d.%m.%Y | %H:%M", time()) . "]</span><br /><div id=\"po_comment_".$last_comment."\">" . reformat($sPostText, 50, 0, 0, 1) . "</div><div id=\"po_comment_original_".$last_comment."\" style=\"display:none;\">" . str_replace(' ', '&nbsp;', reformat($po_text, 1000, 0, 1)) . "</div></div>");
//			$objResponse->call('resetfld', $po_id);
			$objResponse->script("dialogue_count[" . $po_id . "] = " . (count($project_dialogue) + 1));

		}
		else
		{
			$objResponse->assign("po_comment_" . $po_commentid, "innerHTML", reformat($sPostText, 50, 0, 0, 1));
			$objResponse->assign("po_comment_original_" . $po_commentid, "innerHTML", str_replace(' ', '&nbsp;', reformat($po_text, 1000, 0, 1)));
			$objResponse->assign("po_date_" . $po_commentid, "innerHTML", dateFormat("[d.m.Y | H:i]", date("Y-m-d H:i:s")));
		}

		$objResponse->call('answer', $po_id);
		

		if ($to_user_name && $project['id'] && $project['name'] && !$po_commentid) {

			/*require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
			$smail = new smail();
			if ($project['user_id'] == $user_id) {
                $error = $smail->NewPrjMessageOnOfferFrl($user_name['login'], $project['id'], $project['name'], $to_user_name, $po_text);
			} else {
                $error = $smail->NewPrjMessageOnOfferEmp($user_name['login'], $project['id'], $project['name'], $to_user_name, $po_text);
			}*/
		}
	}
	else
	{
		$objResponse->alert("Ошибка сохранения.");
	}

    }
    else
    {
	$objResponse->alert("Ошибка сохранения. Авторизируйтесь на сайте.");
    }
    
    return $objResponse;
}

function ChangePortfByProf($curr_prof_id, $prof_id, $id1 = 0, $id2 = 0, $id3 = 0)
{
	global $session;
	session_start();

	$objResponse = new xajaxResponse();

	$prof_id = intval($prof_id);
	$curr_prof_id = intval($curr_prof_id);
	$user_id = get_uid(false);

	// Работы.
	$obj_portfolio = new portfolio();
	$portf_works = $obj_portfolio->GetPortfProf(get_uid(false), $prof_id);
	if (!$portf_works)
	{
		$portf_works = array();
	}
	// Признак того, что работ > 30
	$portf_more = (count($portf_works) > 30);

	$html_works = '';
	$js_works = "cur_prof = " . $prof_id . ";\n";
	$js_works .= "works_ids = new Array();\n";
	$js_works .= "works_names = new Array();\n";
	$js_works .= "works_prevs = new Array();\n";
	$js_works .= "works_picts = new Array();\n";
	$js_works .= "works_links = new Array();\n";
	$i = 0;
	foreach ($portf_works as $key => $value)
	{
		$i++;
		if ($i == MAX_WORKS_IN_LIST + 1)
		{
			$html_works .= '<div id="more_works" style="font-size:11px;visibility:visible;display:none;">';
		}
		$html_works .= '<div id="portfolio_work_' . $value['id'] . '" style="font-size:100%">';
		$html_works .= '<input type="checkbox" class="checkbox" id="ps_portfolio_work_' . $value['id'] . '" name="ps_portfolio_work_' . $value['id'] . '" value="0" ';
		//if (in_array($value['id'], array($id1, $id2, $id3)))
		//{
		//	$html_works .= ' checked="checked" disabled="disabled"> ' . $value['name'];
        if ($value['id'] == $id1) {
            $html_works .= ' checked="checked" onclick="clear_work(1, ' . $value['id'] . ');">' . $value['name'];
        } else if ($value['id'] == $id2) {
            $html_works .= ' checked="checked" onclick="clear_work(2, ' . $value['id'] . ');">' . $value['name'];
        } else if ($value['id'] == $id3) {
            $html_works .= ' checked="checked" onclick="clear_work(3, ' . $value['id'] . ');">' . $value['name'];
        } else {
    		$html_works .= ' onClick="add_work(' . $value['id'] . ');"> <a href="javascript:void(null);" onClick="add_work(' . $value['id'] . ');" class="blue">' . $value['name'] . '</a>';
		}
		$html_works .= '</div>';

		$js_works .= "works_ids[" . $value['id'] . "] = '" . $value['id'] . "';\n";
		$js_works .= "works_names[" . $value['id'] . "] = '" . (trim(addslashes($value['name']))) . "';\n";
		$js_works .= "works_prevs[" . $value['id'] . "] = '" . $value['prev_pict'] . "';\n";
		$js_works .= "works_picts[" . $value['id'] . "] = '" . $value['pict'] . "';\n";
		$js_works .= "works_links[" . $value['id'] . "] = '" . $value['link'] . "';\n";
	}

	if ($i >= MAX_WORKS_IN_LIST + 1)
	{
		$html_works .= '</div>';
		$html_works .= '<div id="show_more_works" style="font-size:100%;margin-top:12px;"><a href="javascript:void(null)" onClick="document.getElementById(\'show_more_works\').style.display=\'none\';document.getElementById(\'more_works\').style.display=\'block\';" class="blue" style="font-weight:bold;"><img src="/images/triangle_grey.gif" alt="" width="4" height="11" border="0" style="margin-right:4px;" />Остальные работы</a>';
	}

	// Профессии
	$obj_profession = new professions();
	$prof_names = $obj_profession->GetChangeProfNames($curr_prof_id, $prof_id);

	$objResponse->script($js_works);
	$objResponse->assign("portfolio_works", "innerHTML", $html_works);
	$objResponse->assign("profession_" . $prof_id, "innerHTML", $prof_names['new_name']);
	$objResponse->assign("profession_" . $curr_prof_id, "innerHTML", '<a href="javascript:void(null);" onClick="if (ajaxFlag){ ajaxFlag=0; xajax_ChangePortfByProf(cur_prof, ' . $curr_prof_id . ', $(' ."'" . 'ps_work_1_id' ."'" . ').value, $(' ."'" . 'ps_work_2_id' ."'" . ').value, $(' ."'" . 'ps_work_3_id' ."'" . ').value);}" class="blue">' . $prof_names['old_name'] . '</a>');
	$objResponse->script("ajaxFlag=1;");

	return $objResponse;
}


function ReadAllOffers($prj_id)
{
	global $session;
	session_start();
	
	$objResponse = new xajaxResponse();
	$offers = new projects_offers();
	
	$prj_id = intval($prj_id);
	$user_id = get_uid(false);
	
	if (is_emp()) {
		$offlist = $offers->OffersEmpNewMessages($prj_id);
		if (!empty($offlist)) {
		
			projects_offers_dialogue::markAllReadEmp($prj_id, $user_id);
			
			$script = '';
		
			for ($i=0; $i<count($offlist); $i++) {
			
				$script .= 'if($chk($("po_comments_'.$offlist[$i]['id'].'"))) { '
                . "$('po_comments_{$offlist[$i]['id']}').removeClass('po_comments_new_hide');"
                . "$('po_comments_{$offlist[$i]['id']}').addClass('po_comments'); }";
				
				$objResponse->assign("new_msgs_{$offlist[$i]['id']}", "innerHTML", '');
			
			}
			
			$objResponse->script($script);
			$objResponse->assign("op_count_offers_new_msgs", "innerHTML", '');
			$objResponse->assign("op_count_executor_new_msgs", "innerHTML", '');
			$objResponse->assign("op_count_candidate_new_msgs", "innerHTML", '');
			$objResponse->assign("op_count_refuse_new_msgs", "innerHTML", '');
			$objResponse->assign("prj_chk_all", "innerHTML", '');
			
			// Обновляем количество новых сообщений в заголовке.
			$cnt_emp_new_messages = projects_offers_dialogue::CountMessagesForEmp($_SESSION['uid'], true);
			if ($cnt_emp_new_messages > 0) {
				$last_emp_new_messages_pid = projects_offers_dialogue::FindLastMessageProjectForEmp($_SESSION['uid']);
			} else {
				$last_emp_new_messages_pid = false;
			}
			$ndm_html = '';
			$sScript  = "$$('.b-userbar__prjic').addClass('b-userbar__prjic_hide');$$('.b-userbar__icprj').removeClass('b-userbar__icprj_hide');";
			if ($last_emp_new_messages_pid) {
				$ndm_html = '(<a class="b-userbar__toplink" href="/projects/?pid='.$last_emp_new_messages_pid.'" title="Есть новые сообщения">'.$cnt_emp_new_messages.'</a>)';
				$sScript  = "$$('.b-userbar__prjic').removeClass('b-userbar__prjic_hide');$$('.b-userbar__icprj').addClass('b-userbar__icprj_hide');";
			}
			$objResponse->assign("new_dialogue_messages", "innerHTML", $ndm_html);
			$objResponse->script($sScript);
			
		}
		
		return $objResponse;
	}


}

function ReadOfferDialogue($po_id, $prj_id = 0, $fldr = '')
{
    global $session;
    session_start();

    $objResponse = new xajaxResponse();
	$prj_offer = new projects_offers;

    $po_id = intval($po_id);
    $prj_id = intval($prj_id);
    $user_id = get_uid(false);

    $pod = new projects_offers_dialogue;

    if(hasPermissions('projects')) {
        $pod->markReadMod(array($po_id), $user_id);
        $objResponse->script('if($("new_msgs_'.$po_id.'").get("need_change")==1) { $("po_comments_'.$po_id.'").setStyle("background-color","#fff"); }');
    }

        if (is_emp()) {
            $pod->markReadEmp(array($po_id), $user_id);

            $script = 'if($chk($("po_comments_' . $po_id . '"))) { '
                    . "if ($('new_msgs_$po_id').get('need_change') == 1) {"
                    . "$('po_comments_$po_id').removeClass('po_comments_new_hide');"
                    . "$('po_comments_$po_id').addClass('po_comments'); } }";
            $objResponse->script($script);
            switch ($fldr) {
                case 'o':
            		list($po_offers_count, $msg_offers_count) = $prj_offer->CountPrjOffers($prj_id, 'offers');
            		if ($msg_offers_count > 0) {
            		    $objResponse->assign("op_count_offers_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_offers_count . ending($msg_offers_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
            		} else {
    //              $objResponse->script($script);
            		    $objResponse->assign("op_count_offers_new_msgs", "innerHTML", '');
            		}
                	break;
                case 'i':
            		list($po_executor_count, $msg_executor_count) = $prj_offer->CountPrjOffers($prj_id, 'executor');
            		if ($msg_executor_count > 0) {
            		    $objResponse->assign("op_count_executor_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_executor_count . ending($msg_executor_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
            		} else {
    //              $objResponse->script($script);
            		    $objResponse->assign("op_count_executor_new_msgs", "innerHTML", '');
            		}
                	break;
                case 'c':
                    list($po_candidate_count, $msg_candidate_count) = $prj_offer->CountPrjOffers($prj_id, 'candidate');
                	if ($msg_candidate_count > 0) {
                	    $objResponse->assign("op_count_candidate_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_candidate_count . ending($msg_candidate_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
                	} else {
    //                 $objResponse->script($script);
                	    $objResponse->assign("op_count_candidate_new_msgs", "innerHTML", '');
                	}
                	break;
                case 'r':
            	    list($po_refuse_count, $msg_refuse_count) = $prj_offer->CountPrjOffers($prj_id, 'refuse');
            		if ($msg_refuse_count > 0) {
            		    $objResponse->assign("op_count_refuse_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_refuse_count . ending($msg_refuse_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
            		} else {
    //              $objResponse->script($script);
            		    $objResponse->assign("op_count_refuse_new_msgs", "innerHTML", '');
            		}
    
                	break;
                case 'fr':
            	    list($po_refuse_count, $msg_refuse_count) = $prj_offer->CountPrjOffers($prj_id, 'frl_refuse');
            		if ($msg_refuse_count > 0) {
            		    $objResponse->assign("op_count_frl_refuse_new_msgs", "innerHTML", '<img src="/images/ico_envelop.gif" alt="" width="10" height="8" border="0"> ' . $msg_refuse_count . ending($msg_refuse_count, 'новое сообщение', 'новых сообщения', 'новых сообщений'));
            		} else {
            		    $objResponse->assign("op_count_frl_refuse_new_msgs", "innerHTML", '');
            		}
    
                	break;
            }
    
            // Обновляем количество новых сообщений в заголовке.
            $cnt_emp_new_messages = projects_offers_dialogue::CountMessagesForEmp($_SESSION['uid'], true);
            if ($cnt_emp_new_messages > 0) {
                $last_emp_new_messages_pid = projects_offers_dialogue::FindLastMessageProjectForEmp($_SESSION['uid']);
            } else {
                $last_emp_new_messages_pid = false;
            }
            $ndm_html = '';
            $sScript  = "$$('.b-userbar__prjic').addClass('b-userbar__prjic_hide');$$('.b-userbar__icprj').removeClass('b-userbar__icprj_hide');";
            if ($last_emp_new_messages_pid) {
                $ndm_html = '(<a class="b-userbar__toplink" href="/projects/?pid='.$last_emp_new_messages_pid.'" title="Есть новые сообщения">'.$cnt_emp_new_messages.'</a>)';
                $sScript  = "$$('.b-userbar__prjic').removeClass('b-userbar__prjic_hide');$$('.b-userbar__icprj').addClass('b-userbar__icprj_hide');";
            }
            $objResponse->assign("new_dialogue_messages", "innerHTML", $ndm_html);
            $objResponse->script($sScript);
            
        }
        else {
            $pod->markReadFrl($po_id, $user_id);
            // обновляем мигающий значек проекта
            if (!projects_offers::CheckNewFrlEvents($user_id, false) && !projects_offers_dialogue::CountMessagesForFrl($user_id, true, false)) {
                $objResponse->script("$('new_offers_messages').getElement('img').addClass('b-userbar__prjic_hide'); 
                                      $('new_offers_messages').getElement('i').removeClass('b-userbar__icprj_hide'); ");
            }
            if(hasPermissions('projects')) {
                $script = '$("po_comments_'.$po_id.'").setStyle("background-color","#fff"); if($chk($("po_comments_' . $po_id . '"))) { '
                        . "if ($('new_msgs_$po_id').get('need_change') == 1) {"
                        . "$('new_msgs_$po_id').set('need_change', 0); dialogue_toggle({$po_id}); } }";
                $objResponse->script($script);
            }
        }
        
    if(defined('NEO')) {
        $objResponse->script('Page.checkNotifications(true)');
    } else {
        $objResponse->script("Notification()");
    }

	return $objResponse;
}

function FrlRefuse($pid){
    global $session;
    session_start();

    $objResponse = new xajaxResponse();

    $res = freelancer::Refuse(get_uid(), $pid);
    if($res == 't') {
        $objResponse->assign ("frl_edit_bar", 'innerHTML', '');
        $objResponse->assign ("add_dialog_{$uid}", 'innerHTML', '&nbsp;');
        $objResponse->assign ("add_dialog_{$pid}", 'innerHTML', '&nbsp;');
        $objResponse->script("$$('.add_dialog_user').set('html', '&nbsp;');");
        $objResponse->script("$$('.opinions1_{$uid}').set('html', 'Вы отказались от проекта').addClass('refusal-prj'); $$('.opinions2_{$uid}').destroy();");
    }
    return $objResponse;
}

function getStatProject($id, $payed_to, $now, $payed, $post_date, $kind, $comm_count, $offers_count) {
    $objResponse = new xajaxResponse();
    $payed = (($payed_to && $payed_to > $now) ? 1 : 0);
    
    $counte = projects::CountProjectByID($id);
    $page = floor($counte / $GLOBALS["prjspp"]) + 1;
    $counte_page = $counte % $GLOBALS["prjspp"];
    
    $html  = 'Ваше объявление &ndash; <a class="public_blue" href="/projects/?kind='.$kind.'&page='.$page.'#prj'.$id.'">'.$counte_page.'-е по счету ('.$page.'-я страница)</a><br />';
    
    if(hasPermissions('projects')) {
        $aWatch = projects::getProjectWatch($id);
        $html .= "Просмотров " . (int)$aWatch['view_cnt'] . " (" . (int)$aWatch['today_view_cnt'] . " за сегодня)<br />";
    }
    
    if(is_new_prj($post_date) && $comm_count>0) {
        $html .= "{$comm_count} ".ending($comm_count, "предложение", "предложения", "предложений");
    } elseif($offers_count>0) {
        $html .= "{$offers_count} ".ending($offers_count, "предложение", "предложения", "предложений");
    }
    
    $objResponse->assign("prj_pos_{$id}", "innerHTML", $html);
    $objResponse->script("$('pos_link_{$id}').destroy();");
    
    return $objResponse;
}


function mass_Calc($frm) 
{
    global $DB; 

    $objResponse = new xajaxResponse();

    $uid = get_uid(false);
    
    if ($uid <= 0 || !is_emp()) {
        return $objResponse;
    }

    require_once $_SERVER['DOCUMENT_ROOT']."/classes/masssending.php";
    $masssending = new masssending;

    $params['savetime'] = mktime();
    $params['msg'] = stripslashes($frm['msg']);
    $params['is_pro'] = stripslashes($frm['pro']);
    $params['favorites'] = stripslashes($frm['favorites']);
    $params['free'] = stripslashes($frm['free']);
    $params['sbr'] = stripslashes($frm['bs']);
    $params['portfolio'] = stripslashes($frm['withworks']);
    $params['inoffice'] = stripslashes($frm['office']);
    $params['opi_is_verify'] = stripslashes($frm['ver']);
    $tmp = array();
    if($frm['mass_location_columns'][0]!='0' || $frm['mass_location_columns'][1]!='0') {
        $tmp[] = intval($frm['mass_location_columns'][0]).':'.intval($frm['mass_location_columns'][1]);
        $params['locations'] = $tmp;
    }
    if($frm['f_cats']) {
        $frm['f_cats'] = preg_replace("/,$/", "", $frm['f_cats']);
        $acats = explode(",", $frm['f_cats']);
        $cats_data = array();
        foreach($acats as $v) {
            $v = preg_replace("/^mass_cat_span_/", "", $v);
            $c = explode("_", $v);
            if($c[1]==0) {
                $sql = "SELECT prof_group FROM professions WHERE id=?i";
                $p = $DB->val($sql, $c[0]);
                $cats_data[] = $p.":".$c[0];
            } else {
                $cats_data[] = $c[0].":0";
            }
        }
    }
    $params['professions'] = $cats_data;

    //Помимо основного общего расчета нам отдельно нужно кол-во ПРО остальные способы расчета отключаются
    $calc = $masssending->setCalcMethods('pro')->Calculate($uid, $params);

    $objResponse->assign("mass_find_count", "innerHTML", $calc['count']);
    $objResponse->assign("mass_f_users", "value", $calc['count']);
    $objResponse->assign("mass_f_cost", "value", $calc['cost']);

    $objResponse->assign("mass_max_users", "value", $calc['count']);
    $objResponse->assign("mass_max_cost", "value", $calc['cost']);

    $objResponse->assign("mass_find_cost", "innerHTML", $calc['cost']);
    $objResponse->script("$('mass_sendit').removeClass('b-button_disable');");
    $objResponse->script("mass_spam.busy = 0;");

    $objResponse->script("try { $('quickmas_f_mas_u_count_pro').set('html', '".$calc['pro']['count']."'); } catch(e) { }");

    return $objResponse;
}


$xajax->processRequest();