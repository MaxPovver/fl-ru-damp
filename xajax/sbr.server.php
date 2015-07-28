<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");

session_start();
get_uid(false);

function addFrl($login){
    $objResponse = new xajaxResponse();
    $frl = new freelancer();
    $frl->GetUser($login);
    $err = NULL;
    $frl_ftype = sbr::FT_PHYS;
    if(!$frl->uid)
        $err = 'Фрилансер не найден';
    else {
        if($frl_reqvs = sbr_meta::getUserReqvs($frl->uid)) {
            $frl_ftype = (int)$frl_reqvs['form_type'];
            $frl_rtype = (int)$frl_reqvs['rez_type'];
        }
        $sbr = sbr_meta::getInstance();
        $sbr->frl_id = $frl->uid;
        $js_schemes = sbr_meta::jsSchemeTaxes($sbr->getSchemes(), $frl_reqvs, $sbr->getUserReqvs());
        $objResponse->script("SBR.SCHEMES = {$js_schemes};");
    }
    $objResponse->call('SBR.addFrl', $err ? NULL : sbr_meta::view_frl($frl), $frl_ftype, $frl_rtype, $err);
    return $objResponse;
}

function sendFeedbackSMSCode() {
    $objResponse = new xajaxResponse();
    $uid = get_uid(false);
    $reqv = sbr_meta::getUserReqvs($uid);
    $phone = $reqv[$reqv['form_type']]['mob_phone'];

    $code = rand(1000, 9999);
    $_SESSION['close_sbr_smscode'] = $code;

    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    $sms_gate = new sms_gate_a1($phone);
    $sms_gate->sendSMS($sms_gate->getTextMessage(sms_gate::TYPE_CLOSE_SBR, $code));

    $objResponse->script("$('sbr_sms_code').set('value', '');");
    $objResponse->script("$('sbr_sms_code').removeClass('b-input__text_error');");

    $objResponse->script("$('sbr_send_sms_link').addClass('b-layout_hide'); $('sbr_send_sms_link_disabled').removeClass('b-layout_hide');");
    $objResponse->script("setTimeout(function() { $('sbr_send_sms_link').removeClass('b-layout_hide'); $('sbr_send_sms_link_disabled').addClass('b-layout_hide'); }, 60000);");
    return $objResponse;
}

function changeEmpRezType($frl_login, $rez_type){
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance();
    if($frl_login) {
        $frl = new freelancer();
        $frl->GetUser($frl_login);
        if($frl->uid)
            $frl_reqvs = sbr_meta::getUserReqvs($frl->uid);
    }
    $sbr->emp_reqvs = $sbr->getUserReqvs();
    $sbr->emp_reqvs['rez_type'] = $rez_type;
    $js_schemes = sbr_meta::jsSchemeTaxes($sbr->getSchemes(), $frl_reqvs, $sbr->emp_reqvs);
    $objResponse->script("SBR.SCHEMES = {$js_schemes};");
    $objResponse->call('SBR.changeEmpRezType', $rez_type, 1);
    return $objResponse;
}

function getMsgForm($stage_id, $msg_id, $to_edit) 
{
    $stage_id = intval($stage_id);
    $msg_id = intval($msg_id);
    
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance();
    $stage = $sbr->initFromStage($stage_id, false);
    if($stage->error)
        return $objResponse;
    if($to_edit)
        $msg = $stage->getMsgs($msg_id);
    else
        $msg = array('stage_id'=>$stage_id, 'parent_id'=>$msg_id); // !!! ид.
    $objResponse->call("SBR_STAGE.getMsgForm", $msg_id, $to_edit, $stage->msg_form($msg), count($msg['attach']), (int)(!!$msg['attach']), (int)(!!$msg['yt_link']));
    return $objResponse;
}

function delMsg($msg_id, $stage_id) 
{
    $stage_id = intval($stage_id);
    $msg_id = intval($msg_id);
    
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance();
    $stage = $sbr->initFromStage($stage_id, false);
    if($stage->error)
        return $objResponse;
    if($msg = $stage->delMsg($msg_id)) // !!!
        $objResponse->call("SBR_STAGE.delMsg", $msg['id'], $stage->msg_node_content($msg));
    return $objResponse;
}

function getDocForm($sbr_id, $doc_id) 
{
    $sbr_id = intval($sbr_id);
    $doc_id = intval($doc_id);
    
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance();
    $sbr->initFromId($sbr_id, true, false, false);
    if($sbr->error)
        return $objResponse;
    if($sbr->getDocs($doc_id)) {
        $doc = current($sbr->docs);
        $objResponse->call("SBR.initDocForm", NULL, $sbr_id, $doc_id, $sbr->doc_form($doc, $doc['stage_id'], TRUE));
    }
    return $objResponse;
}

function delDoc($sbr_id, $doc_id, $anc = NULL) 
{
    $sbr_id = intval($sbr_id);
    $doc_id = intval($doc_id);
    
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance(hasPermissions('sbr') ? sbr_meta::ADMIN_ACCESS : NULL);
    $sbr->initFromId($sbr_id, false, false, false);
    if($sbr->error)
        return $objResponse;
    if($sbr->delDocs($doc_id)) {
        if(!$anc)
            $objResponse->call("SBR.delDoc", NULL, $sbr_id, $doc_id);
        else {
            $doc = $sbr->getDoc($doc_id);
            $objResponse->call("SBR.delDoc", NULL, $sbr_id, $doc_id, $anc, sbr_adm::view_doc_field(NULL, $anc, $doc['stage_id'], $doc['type'], $doc['access_role']));
        }
    }
    return $objResponse;
}

function setDocsReceived($suid, $mode) 
{
    $suid = intval($suid);
    
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance(hasPermissions('sbr') ? sbr_meta::ADMIN_ACCESS : NULL);
    if($sbr->error)
        return $objResponse;
    if($su = $sbr->setDocsReceived($suid))
        $objResponse->call("SBR.setRecvDocs", NULL, $suid, $su['docs_received']=='t', true); // $su['docs_ready']=='t');
    return $objResponse;
}

function getArbDescr($stage_id) 
{
    $stage_id = intval($stage_id);
    
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance();
    $stage = $sbr->initFromStage($stage_id, false);
    if($stage->error)
        return $objResponse;
    if($stage->getArbitrage(TRUE)) 
        $objResponse->call("SBR.getArbDescr", NULL, $stage_id, $stage->arb_descr());
    return $objResponse;
}

/**
 * Получение отзыва по СБР
 */
function getFeedback($stage_id, $feedback_id, $login) {
    $objResponse = new xajaxResponse();
    $stage_id = (int)$stage_id;
    $feedback_id = (int)$feedback_id;
    if(($sbr = sbr_meta::getInstance()) && ($stage = $sbr->getStage($stage_id))) {
        $feedback = $stage->getFeedback($feedback_id);
        $objResponse->call("feedbackEditForm", $stage_id, $feedback_id, $login, htmlspecialchars_decode($feedback['descr'], ENT_QUOTES), $feedback['p_rate'], $feedback['n_rate'], $feedback['a_rate']);
    }
    return $objResponse;
}

/**
 * Простое обновление отзыва  таблице sbr_feedbacks
 * @param type $id
 * @param type $descr
 * @param string  $login
 * @param integer $stage_id
 * @param type $vote
 * @return xajaxResponse 
 */
function editFeedbackNew($id, $descr, $login, $stage_id, $vote = null, $sbr_name = null, $stage_name = null)
{
    $stage_id = intval($stage_id);
    
    $objResponse = new xajaxResponse();
    $request = array (
      'id' => intval($id),
      'descr' => $descr
    );
    if(hasPermissions('sbr')) {
        $request['sbr_name']   = htmlspecialchars($sbr_name);
        $request['stage_name'] = htmlspecialchars($stage_name);
    }

    if($vote !== NULL) {
        $vote = (int)$vote < 0 ? -1 : ((int)$vote > 0 ? 1 : 0);
        $request += array (
          'ops_type' => $vote
        );
    }
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    if($login && hasPermissions('sbr')) {
        $user = new users();
        $user->GetUser($login);
        if(!$user->uid) $error = 'Ошибка';
        $sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS, $user, is_emp($user->role));
    } else {
        $sbr = sbr_meta::getInstance();
    }
    
    $ele_id = 'form_container_'.$id;
    $link_id = 'ops_edit_link_'.$id;
    $text_id = 'op_message_'.$id;
    
    if($sbr) {
        $stage = $sbr->initFromStage($stage_id, false);
    }
    if($stage) {
        $old_fbk = $stage->getFeedback(intval($id));
        if (strtotime($old_fbk['posted_time'])+3600*24 < time() && !hasPermissions('users')) {
            $error = "'Ошибка'";
            $objResponse->script("$('$link_id').setStyle('display', 'block');");
            $objResponse->script("$('$text_id').setStyle('display', 'block');");
            $objResponse->script("$('$ele_id').setStyle('display', 'none');");
        } else {
            $feedback = $stage->feedback($request);
            if(hasPermissions('sbr')) {
                sbr_meta::setNamesSBR($request['sbr_name'], $request['stage_name'], $stage->data['sbr_id'], $stage_id);
                $objResponse->script("$('sbr_name_{$id}').set('text', '{$sbr_name}');");
                $objResponse->script("$('stage_name_{$id}').set('text', '{$stage_name}');");
            }
            if($stage->error['feedback'])  {
                $error = 'Ошибка';
                if($stage->error['feedback']['descr'])
                    $error = $stage->error['feedback']['descr'];
            }
        }
    }
    
    if($error) {
        $objResponse->script("alert({$error});"); 
        return $objResponse;
    }
    
    $userId  = ($sbr->uid == $sbr->emp_id ? $sbr->frl_id : $sbr->emp_id);
    $moderId = get_uid(FALSE);
    if ( $userId != $moderId ) {
        $mail = new smail;
        $mail->sbrFeedbackEdit($old_fbk['id'], $userId, $moderId, $sbr);
    }

    if ($login) {
        $user = new users();
        $user->GetUserByUID($sbr->uid == $sbr->emp_id ? $sbr->frl_id : $sbr->emp_id);
        $rating = round($user->rating, 2);
        $objResponse->script("$$('.pp-rate span.form-in').set('html', {$rating});");
    }
    
    $cont_id = 'cont_'.$id;
    if($vote !== null){
        $cls = '';
        switch ((int)$vote){
            case -1:
                $cls = 'b-button_poll_minus';
                break;
            case 0;
                $cls = 'b-button_poll_multi';
                break;
            case 1:
                $cls = 'b-button_poll_plus';
                break;
            default:
                $cls = 'b-button_poll_multi';
                break;
        }
        //$objResponse->script("$('$cont_id').removeClass('b-post__voice_negative'); $('$cont_id').removeClass('b-post__voice_neutral');$('$cont_id').removeClass('b-post__voice_plus');$('$cont_id').addClass('$cls');");
        $objResponse->script("$('$cont_id').removeClass('b-button_poll_minus').removeClass('b-button_poll_plus').removeClass('b-button_poll_multi');$('$cont_id').addClass('$cls');");
    }
    $ot = $old_fbk['rating'] == 1 ? 'plus' : ($old_fbk['rating'] == -1 ? 'minus' : 'neitral');
    $nt = $vote == 1 ? 'plus' : ($vote == -1 ? 'minus' : 'neitral');
    
    $objResponse->call('opinionChConuters', 'ops-norisk'.$ot, 'ops-norisk'.$nt);
    $objResponse->script("$('$link_id').setStyle('display', 'block');");
    $objResponse->script("$('$text_id').setStyle('display', 'block');");
    $objResponse->script("$('$ele_id').setStyle('display', 'none');");
    $objResponse->script("$$('.sbrmsgblock').setStyle('display', 'block');");
    $objResponse->assign($text_id, "innerHTML",  '<p>'.stripslashes(reformat(htmlspecialchars($descr), 30, 0, 1, 1)).'</p>');
    $objResponse->assign($ele_id, "innerHTML",  '');
    return $objResponse;
}
/**
 * Сохраняет изменения рейтинга СБР
 */
function editFeedback($stage_id, $feedback_id, $ops_type, $mesg, $login) 
{
    $stage_id = intval($stage_id);
    
    $objResponse = new xajaxResponse();
    $request = array (
      'id' => intval($feedback_id),
      'descr' => $mesg
    );

    if($ops_type !== NULL) {
        $request += array (
          'ops_type' => intval($ops_type)
        );
    }

    if($login && hasPermissions('sbr')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $user = new users();
        $user->GetUser($login);
        if(!$user->uid) $err = 'Ошибка';
        $sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS, $user, is_emp($user->role));
    }
    else {
        $sbr = sbr_meta::getInstance();
    }

    if($sbr)
        $stage = $sbr->getStage($stage_id);

    if($stage) {
        if($feedback = $stage->feedback($request))
            $objResponse->call("saveRating", NULL, $stage->id, $feedback['id'], reformat($feedback['descr'], 30, 0, 1, 1), $feedback['p_rate'], $feedback['n_rate'], $feedback['a_rate']);
        if($stage->error['feedback'])  {
            $err = 'Ошибка';
            if($stage->error['feedback']['descr'])
                $err = $stage->error['feedback']['descr'];
        }
    }
    if($err)
        $objResponse->call("saveRating", $err, $stage_id);
    return $objResponse;
}

function getInvoiceForm($stage_id, $reqv_mode) 
{
    $stage_id = intval($stage_id);
    
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance();
    $stage = $sbr->initFromStage($stage_id, false);
    if($stage->error)
        return $objResponse;
    $sbr->getInvoiceReqv($form_type, $reqv_mode);
    $objResponse->call('SBR.switchReqvMode', $stage_id, $reqv_mode, $sbr->view_invoice_form($stage_id, $form_type, $reqv_mode));
    return $objResponse;
}

function setArbPercent($stage_id, $arb_percent) 
{
    $stage_id = intval($stage_id);
    
    $objResponse = new xajaxResponse();
    if(!hasPermissions('sbr'))
        return $objResponse;
    $sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS);
    $stage = $sbr->initFromStage($stage_id, false);
    if($stage->error)
        return $objResponse;
    $objResponse->call("SBR.setArbPercent", NULL, $stage_id, $stage->getPayoutSum(sbr::EMP, NULL, 1-$arb_percent), $stage->getPayoutSum(sbr::FRL, NULL, $arb_percent));
    return $objResponse;
}

function setRemoved($suid) 
{
    if(!$suid) return;

    $suid = intval($suid);
    
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance(hasPermissions('sbr') ? sbr_meta::ADMIN_ACCESS : NULL);
    if($sbr->error)
        return $objResponse;
    if($su = $sbr->setRemoved($suid)) {
        $resp['is_removed'] = $su['is_removed'];
        $objResponse->call("SBR.setRemoved", $suid, $resp);
    }
    return $objResponse;
}

function changeRezTypeFrl($sbr_id, $rez_type) 
{
    $sbr_id = intval($sbr_id);
    
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance();
    $sbr->getUserReqvs();
    $sbr->user_reqvs['rez_type'] = $rez_type;
    $sbr->frl_reqvs = $sbr->user_reqvs;
    $sbr->initFromId($sbr_id, true, false, false, true);
    $sbr->view_scheme_info();
    $objResponse->call("SBR.changeRezTypeFrl", $sbr_id, $rez_type, $sbr->view_scheme_info());
    return $objResponse;
}

/**
 * @deprecated
 */
/*
function rezDocChange($login, $comment, $status = NULL) {
    $objResponse = new xajaxResponse();
    if(!hasPermissions('sbr'))
        return $objResponse;
    $user = new users();
    $user->GetUser($login);
    if(!$user->uid)
        return $objResponse;

    $sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS, $user, is_emp($user->role));
    $sbr->setRezDoc($user->uid, substr(change_q_x($comment, TRUE),0,1000), $status);
    $reqvs = $sbr->getUserReqvs();
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'].'/user/setup/tpl.finance_rezdoc.php');
    $html = ob_get_clean();
    $objResponse->call("SBR.rezDocChange", $html);
    return $objResponse;
}
*/

function setNotNp($user_id, $stage_id, $np) 
{
    $user_id = intval($user_id);
    $stage_id = intval($stage_id);
    
    $objResponse = new xajaxResponse();
    if(!hasPermissions('sbr'))
        return $objResponse;
    $user = new freelancer();
    $user->GetUserByUID($user_id);
    if(!$user->uid)
        return $objResponse;

    $sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS, $user, false);
    $stage = $sbr->initFromStage($stage_id, false);
    if(!$stage->error && $stage->setNotNp($np))
        $objResponse->call("SBR.setNotNp", NULL, $user_id, $stage_id, $np);
    return $objResponse;
}


function elPayout($type, $stage_id, $user_id, $confirmed = false) 
{
    $stage_id = intval($stage_id);
    $user_id = intval($user_id);
    
    $objResponse = new xajaxResponse();
    if(!hasPermissions('sbr'))
        return $objResponse;
    $sbr = new sbr_adm($_SESSION['uid'], $_SESSION['login']);
    $stage = $sbr->initFromStage($stage_id, false);
    $fn = $type == exrates::YM ? 'ydPayout' : 'wmPayout';
    if($pmt = $stage->$fn($user_id, $confirmed)) {
        $objResponse->call("SBR.openPayoutPopup", $type, $stage_id, $user_id, $stage->view_payout_popup($pmt, $user_id),
                           $pmt['in_amt'] <= $pmt['out_amt'], $stage->payouts[$user_id]['completed'] ? date('d.m.Y H:i', strtotime($stage->payouts[$user_id]['completed'])) : NULL);
    }
    return $objResponse;
}

function openPayoutPopup($type, $stage_id, $user_id) 
{
    $stage_id = intval($stage_id);
    $user_id = intval($user_id);
    
    $objResponse = new xajaxResponse();
    if(!hasPermissions('sbr'))
        return $objResponse;

    $sbr = new sbr_adm($_SESSION['uid'], $_SESSION['login']);
    $stage = $sbr->initFromStage($stage_id, false);
    $fn = $type == exrates::YM ? 'getYdPaymentInfo' : 'getWmPaymentInfo';
    if($pmt = $stage->$fn($user_id)) {
        $objResponse->call("SBR.openPayoutPopup", $type, $stage_id, $user_id, $stage->view_payout_popup($pmt, $user_id));
    }
    return $objResponse;
}

function saveLimit($type, $stage_id, $user_id, $limit) 
{
    $stage_id = intval($stage_id);
    $user_id = intval($user_id);
    $limit = intval($limit);
    
    $objResponse = new xajaxResponse();
    if(!hasPermissions('sbr'))
        return $objResponse;

    $sbr = new sbr_adm($_SESSION['uid'], $_SESSION['login']);
    $stage = $sbr->initFromStage($stage_id, false);
    $fn = $type == exrates::YM ? 'saveYdPaymentLimit' : 'saveWmPaymentLimit';
    if($stage->$fn($user_id, $limit)) {
        $fn = $type == exrates::YM ? 'getYdPaymentInfo' : 'getWmPaymentInfo';
        if($pmt = $stage->$fn($user_id)) {
            $objResponse->call("SBR.openPayoutPopup", $type, $stage_id, $user_id, $stage->view_payout_popup($pmt, $user_id));
        }
    }
    return $objResponse;
}


function EditSBROpForm($op_id, $login)
{
    $op_id = intval($op_id);
    
    session_start();
    include_once $_SERVER['DOCUMENT_ROOT'].'/classes/opinions.php';
	$objResponse = new xajaxResponse();
        $ele_id = 'form_container_'.$op_id;
        
        $objResponse->script("$$('.editFormSbr').set('html', '&nbsp;').setStyle('display', 'none');");
        $objResponse->script("$$('.sbrmsgblock').setStyle('display', 'block');");
        $objResponse->script("$('form_container_to_{$op_id}').setStyle('display', 'none');");
        $objResponse->script("$('$ele_id').setStyle('display', 'block');");
        $objResponse->assign($ele_id, "innerHTML",  opinions::getEditSBREditForm($op_id, $login));
        return $objResponse;
}


function sbrCalc($frl_type, $rez_type, $scheme_type, $currency, $sbr_cost, $emp_cost, $frl_cost, $usr_type) {
    session_start();
    
    $frl_type = intval($_POST['xjxargs']['frl_type']);
    $rez_type = intval($_POST['xjxargs']['residency']);
    $scheme_type = intval($_POST['xjxargs']['scheme_type']);
    $currency = intval($_POST['xjxargs']['currency']);
    $sbr_cost = $_POST['xjxargs']['sbr_cost'];
    $emp_cost = $_POST['xjxargs']['emp_cost'];
    $frl_cost = $_POST['xjxargs']['frl_cost'];
    $usr_type = $_POST['xjxargs']['usr_type'];
    $_POST['xjxargs'] = array("frl_type" => $frl_type, "rez_type" => $rez_type, "scheme_type" => $scheme_type, "currency" => $currency, "sbr_cost" => $sbr_cost, "emp_cost" => $emp_cost, "frl_cost" => $frl_cost, "usr_type" => $usr_type); // Заплатка для того чтобы работала CSRF xajax
    $hash = "";
    sbr::setSbrCalc($_POST['xjxargs'], $hash);
    $_POST = $_POST['xjxargs'];
    
    foreach ($_POST as $k=>$v) {
        if (!in_array($k, array('sbr_cost', 'frl_cost', 'emp_cost'))) {
            continue;
        }
        $_POST[$k] = str_replace(',', '.', $v);
    }
    
    $sbr_cost = __paramInit('money', null, 'sbr_cost', 0.00, 10);
    $emp_cost = __paramInit('money', null, 'emp_cost', 0.00, 10);
    $frl_cost = __paramInit('money', null, 'frl_cost', 0.00, 10);
    
    $err = 0;
    
    if (!$frl_type || !$frl_type || !$scheme_type || !$currency) {
        $err = 1;
    }
    
    if (($sbr_cost + $emp_cost + $frl_cost) <= 0) {
        $err = 1;
    }
    
    if ($sbr_cost && $sbr_cost < sbr_stages::MIN_COST_RUR) {
        $err = 1;
        $res['msg'] = iconv('CP1251', 'UTF8', 'Минимальный бюджет проекта - ' .sbr_stages::MIN_COST_RUR. ' руб.');
    }
    
    if ($sbr_cost && $sbr_cost < sbr_stages::MIN_COST_RUR_PDRD && ( $scheme_type == sbr::SCHEME_PDRD || $scheme_type == sbr::SCHEME_PDRD2 ) ) {
        $err = 1;
        $res['msg'] = iconv('CP1251', 'UTF8', 'Минимальный бюджет проекта - ' .sbr_stages::MIN_COST_RUR_PDRD. ' руб.');
    }
    
    if (!get_uid(false) || $err) {
        $res['success'] = false;
        echo json_encode($res);
        
        return;
    }
    
    $sbr_meta = sbr_meta::getInstance();
    $sbr_meta->scheme_type = $scheme_type;
    $schemes = $sbr_meta->getSchemes();
    
    $emp_total = $frl_total = 0;
    $emp_tax = $frl_tax = 0;
    $tcost = $sbr_cost;
    
    $sch = null;
    
    foreach($schemes as $id => $scheme) {
        if ($scheme['type'] != $scheme_type) continue;
        
        $sch = $scheme;
    }
    
    $_taxes = $taxes = array();
    
    $rrq = array('U'=>0, 'Ff'=>$frl_type, 'P' => $currency, 'Rf'=>$rez_type);
    $pct = 0;
    foreach ($sch['taxes'][0] as $id => $tax) {
        $cost = sbr_meta::calcAnyTax($tax['id'], $tax['scheme_id'], $tcost, $rrq);
        $pct = $cost/$tcost;
        if (!$pct) continue;
        $tax['pct'] = $pct;
        $_taxes['frl'][] = $tax;
        $frl_tax += $pct*100;
    }
    
    $rrq = array('U'=>1, 'Ff'=>$frl_type, 'P' => $currency, 'Rf'=>$rez_type, 'C' => $tcost);
    foreach ($sch['taxes'][1] as $id => $tax) {
        $cost = sbr_meta::calcAnyTax($tax['id'], $tax['scheme_id'], $tcost, $rrq);
        $pct = $cost/$tcost;
        if (!$pct) continue;
        $tax['pct'] = $pct;
        $_taxes['emp'][] = $tax;
        $emp_tax += $pct*100;
    }
    
    
    if ($sbr_cost) {
        $emp_cost = $sbr_cost + $sbr_cost*($emp_tax/100);
        $frl_cost = $sbr_cost - $sbr_cost*($frl_tax/100);
    } elseif ($emp_cost) {
        $sbr_cost = $emp_cost - ($emp_cost/(100+$emp_tax))*$emp_tax;
        $frl_cost = $sbr_cost - $sbr_cost*$frl_tax/100;
    } elseif ($frl_cost) {
        $sbr_cost = $frl_cost + ($frl_cost/(100-$frl_tax))*$frl_tax;
        $emp_cost = $sbr_cost + $sbr_cost*($emp_tax/100);
    }
    
    
    if ($sbr_cost && $sbr_cost < sbr_stages::MIN_COST_RUR) {
        $err = 1;
        $res['msg'] = iconv('CP1251', 'UTF8', 'Минимальный бюджет проекта - ' .sbr_stages::MIN_COST_RUR. ' руб.');
        $res['success'] = false;
        echo json_encode($res);
        
        return;
    }
    
    
    if ($rez_type == sbr::RT_UABYKZ && $sbr_cost && $sbr_cost > sbr::usd2rur(sbr::MAX_COST_USD)) {
        $err = 1;
        $sum = sbr_meta::view_cost(sbr::usd2rur(sbr::MAX_COST_USD), exrates::BANK);
        $sum = html_entity_decode($sum);
        $res['msg'] = iconv('CP1251', 'UTF8', 'Максимальный бюджет ' . $sum . ', поскольку исполнитель не является резидентом Российской Федерации');
        $res['success'] = false;
        echo json_encode($res);
        
        return;
    }
    if($_taxes['frl']) {
        foreach ($_taxes['frl'] as $k => $tax) {
            $cost = $sbr_cost * $tax['pct'];
            if ($tax['tax_id'] == $sbr_meta->getTaxByCode('TAX_NDFL') || $tax['tax_id'] == $sbr_meta->getTaxByCode('TAX_NDFL_NR') ) {
                $cost = sbr_meta::ndfl_round($cost);
            }
            $taxes['frl'][] = array(
                'id' => $tax['id'],
                'scheme' => $scheme_type,
                'cost' => sbr_meta::view_cost($cost, null, false)
            );
            $frl_total += $cost;
        }
    }
    
   
    foreach ($_taxes['emp'] as $k => $tax) {
        $cost = $sbr_cost * $tax['pct'];
        if ($tax['tax_id'] == $sbr_meta->getTaxByCode('TAX_NDFL') || $tax['tax_id'] == $sbr_meta->getTaxByCode('TAX_NDFL_NR') ) {
            $cost = sbr_meta::ndfl_round($cost);
        }
        $taxes['emp'][] = array(
            'id' => $tax['id'],
            'scheme' => $scheme_type,
            'cost' => sbr_meta::view_cost($cost, null, false)
        );
        $emp_total += $cost;
    }
    
    $res['usr_type'] = intval($_POST['usr_type']);
    $res['taxes'] = $taxes;
    $res['emp_total'] = sbr_meta::view_cost($emp_total, null, false);
    $res['frl_total'] = sbr_meta::view_cost($frl_total, null, false);
    $res['emp_cost'] = round($sbr_cost+$emp_total,2);
    $res['frl_cost'] = round($sbr_cost-$frl_total,2);
    $res['sbr_cost'] = round($sbr_cost,2);
    $res['rating_get'] = sbr_meta::getSBRRating($res['sbr_cost']);
    $res['hash'] = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/?site=calc&hash='.$hash;
    
    $res['success'] = true;
    echo json_encode($res);
    return;
}

/**
 * Удаляет рекомендацию
 * @param type $stage_id 
 * @param type $feedback_id
 * @param type $deleteOpinion удалить мнение
 * @param type $reloadPage перезагрузить страницу
 * @return \xajaxResponse
 */
function DeleteFeedback ($stage_id, $feedback_id, $reloadPage = false) {
    session_start();
    $objResponse = new xajaxResponse();
    
    $stage_id = intval($stage_id);
    $feedback_id = intval($feedback_id);
    
    $uid = get_uid(false);
    
    if (!hasPermissions('sbr')) {
        return;
    }
    
    $feedback = sbr_meta::getFeedback($feedback_id);
    if (!$feedback) {
        return;
    }
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    $user = new users();
    $user->GetUserByUID($uid);
    if(!$user->uid) $err = 'Ошибка';
    $sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS, $user, is_emp($user->role));
    if (!$sbr) {
        return;
    }
    
    $stage = $sbr->getStage($stage_id);
    if (!$stage) {
        return;
    }
    
    //$res = sbr_meta::deleteFeedback($feedback_id);
    $res = sbr_meta::setDeletedFeedback($feedback_id);
    if (!$res) {
        return;
    }
    
    $ot = $feedback['rating'] == 0 ? 'neitral' : ($feedback['rating'] == 1 ? 'plus' : 'minus');
    
    $objResponse->call('opinionChConuters', 'ops-norisk'.$ot);
    $objResponse->script("$('cont_{$feedback_id}').getParent('div.ops-one').dispose();");
    $objResponse->script("if($$('.page-ops div.ops-one').length == 0) $('no_messages').show();");
    
    if ($reloadPage) {
        $objResponse->script("window.location.reload()");
    }
    
    return $objResponse;
}

// Проверяем заполненость полей при выплате через WM
function checkWMDoc() {
    session_start();
    $objResponse = new xajaxResponse();
    
    $uid  = $_SESSION['uid'];
    $reqv = sbr_meta::getUserReqvs($uid);
    // Поля не заполнены
    if(sbr_meta::checkWMDoc($reqv)) {
        
        $html  = '<div class="">';
        $html .= '<b class="b1"></b><b class="b2"></b>';
        $html .= '<div class="form-in">';
        $html .= 'Для выбора Webmoney в качестве валюты выбора требуется заполнить поля "<a href="/users/' . $_SESSION['login'] . '/setup/finance/#WMDOC">Паспортные данные</a>" в блоке "Электронные кошельки" на странице "<a href="/users/' . $_SESSION['login'] . '/setup/finance/">Финансы</a>"';
        $html .= '</div><b class="b2"></b><b class="b1"></b></div>';
        $objResponse->script("
            if($('wmdoc_alert')) $('wmdoc_alert').dispose();
            var block = $$('.nr-block-imp')[$$('.nr-block-imp').length-1];
            var html  = new Element('div', {'class':'nr-block-imp', 'html':'{$html}', 'id':'wmdoc_alert'});
            block.grab(html, 'after');
            $('submit_btn').addClass('btnr-disabled');
        ");
    } else {
        $objResponse->script("if($('wmdoc_alert')) $('wmdoc_alert').dispose();");
    }
    return $objResponse;
}

function loadCurrents($filter, $limit) {
    global $session;
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
    session_start();

    $objResponse = new xajaxResponse();
     
    $filter = __paramValue('string', $filter);
    $limit  = __paramValue('int', $limit);
    
    $sbr = sbr_meta::getInstance();
    $sbr_currents = $sbr->getOldSbrCompleted();
    if(!$sbr_currents) {
        return $objResponse;
    }
    $html = "";
    $fpath = $sbr->isEmp() ? 'employer/' : 'freelancer/';
    
    ob_start();
    include $_SERVER['DOCUMENT_ROOT']."/sbr/tpl.sbr-content.php";
    $html = ob_get_clean();
    
    $objResponse->script("var block = new Element('span', {'id' : 'loads_currents_sbr'});
                          $('load_link').dispose();
                          $('show_link').removeClass('b-layout_hide');
                          $('button_load_currents').grab(block, 'after');");
    
    $objResponse->assign('loads_currents_sbr', "innerHTML",  $html);
    
    return $objResponse;
}

function loadSbr($sbr_id) {
    global $session;
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
    session_start();
    
    $objResponse = new xajaxResponse();
    
    $sbr_id = intval($sbr_id);
    
    $sbr = sbr_meta::getInstance();
    $sbr_array = $sbr->getSbrForId($sbr_id);
    $curr_sbr = $sbr_array ? current($sbr_array) : false;
    if(!$curr_sbr) {
        return $objResponse;
    }
    $fpath = $sbr->isEmp() ? 'employer/' : 'freelancer/';
    
    ob_start();
    include $_SERVER['DOCUMENT_ROOT']."/sbr/tpl.sbr.php";
    $html = ob_get_clean();
    
    $objResponse->script("var block = new Element('span', {'id' : 'loads_currents_sbr'});
                          $('button_load_currents').grab(block, 'after');");
    
    $objResponse->assign('loads_currents_sbr', "innerHTML",  $html);
    $objResponse->script("new Fx.Scroll(window, {duration : 0}).toElement($('sbrList{$sbr_id}'));");
    
    return $objResponse;
}

function agreeStage($stage_id, $next_stage) {
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance();
    
    $stage_id   = __paramValue('int', $stage_id);
    $next_stage = __paramValue('int', $next_stage);
    
    $stage = $sbr->initFromStage($stage_id);
    if(!$stage) return $objResponse;
    
    $agree = $stage->agreeStage($stage_id);
    
    if($agree) {
        $objResponse->script("var ms = new MStage();
                            ms.completeStage('{$stage_id}');" .
                            ( ($next_stage > 0) ? "ms.redrawStage('{$next_stage}', true);"
                                                : ($next_stage == 0 ? "ms.redrawStage('last', true);" : "" )));
    }
                         
    return $objResponse;
}

function checkSbr($stage_id) {
    $objResponse = new xajaxResponse();
    $sbr = sbr_meta::getInstance();
    
    $stage_id   = __paramValue('int', $stage_id);
    
    $stage = $sbr->initFromStage($stage_id);
    if($stage->sbr->status == sbr::STATUS_CANCELED) {
        ob_start();
        include ($_SERVER['DOCUMENT_ROOT']. "/sbr/freelancer/tpl.sbr_refuse.php");
        $html = ob_get_clean();
        
        $objResponse->assign('master_content', 'innerHTML', $html);
        $objResponse->script('$$(".b-master").destroy();');
    }
    
    return $objResponse;
}

function checkState($sbr_id) {
    $objResponse = new xajaxResponse();
    $sbr_id = __paramValue('int', $sbr_id);
    
    if(!$sbr_id) {
        return $objResponse;
    }
    
    $sbr = sbr_meta::getInstance();
    $sbr->initFromId($sbr_id);
    if ($sbr->data['emp_id'] != get_uid(false)) {
        return $objResponse;
    }
    
    $pskb = new pskb($sbr);
    $lc = $pskb->getLC();
    
    if (in_array($lc['state'], array(pskb::STATE_COVER, pskb::STATE_ERR))) {
        $objResponse->script('document.location.reload();');
    } elseif (time() - strtotime($lc['created']) > 600) {
        $objResponse->script("$('reservation').setStyle('display', 'none'); $('reservation-after10min').setStyle('display', '')");
    } else {
        $objResponse->script("setTimeout(function() { xajax_checkState({$sbr_id}); }, 29000);");
    }
    
    return $objResponse;
}

function deleteDraftSbr($sbr_id) {
    $objResponse = new xajaxResponse();
    $sbr_id      = __paramValue('int', $sbr_id);
    if(!$sbr_id) return $objResponse;
    $sbr         = sbr_meta::getInstance();
    
    $sbr->initFromId($sbr_id);
    if(!$sbr) return $objResponse;
    
    $delete = $sbr->delete($sbr_id);
    
    if($delete) {
        $objResponse->script("removeDraftSbr('{$sbr_id}');");
    }
    
    return $objResponse;
}

function checkFrlRezType($frl_id, $emp_rez_type){
    $frl_id = intval($frl_id);
    $objResponse = new xajaxResponse();
    $objResponse->script("$('taxes_alert').hide();");
    $objResponse->script("$('unknown_frl_rez').addClass('b-fon_hide');");
    $objResponse->script("if(!$('nerez_frl_rez').hasClass('b-fon_nohide')) $('nerez_frl_rez').addClass('b-fon_hide');");
    $objResponse->script("$('frl_ban').addClass('b-fon_hide');");
    $objResponse->script("$('frl').getParent().removeClass('b-combo__input_error');");
    if(!$frl_id) {
        return $objResponse;
    }
    $frl = new freelancer();
    $frl->GetUserByUID($frl_id);
    if(!$frl->uid) {
        return $objResponse;
    } else {
        
        if($frl->is_banned == 1) {
            $objResponse->script("$('frl_ban').removeClass('b-fon_hide');");
            $objResponse->script("$('frl').getParent().addClass('b-combo__input_error');");
        }
        
        if($frl_reqvs = sbr_meta::getUserReqvs($frl->uid)) {
            if( (int) $frl_reqvs['rez_type'] <= 0) {
                $objResponse->script("$('unknown_frl_rez').removeClass('b-fon_hide');");
                $objResponse->script("if($('scheme_type".sbr::SCHEME_PDRD2."').checked) $('taxes_alert').show();");
            }
            
            if((int) $frl_reqvs['rez_type'] == sbr::RT_UABYKZ) {
                $objResponse->script("$('nerez_frl_rez').removeClass('b-fon_hide');");
            }
            
            if($frl_reqvs['rez_type'] == sbr::RT_UABYKZ) {
                if ($frl_reqvs['form_type'] == sbr::FT_PHYS) {
                    $objResponse->script("$('alert_frl_is_fiz').removeClass('b-layout__txt_hide');");
                    $objResponse->script("$('alert_frl_is_jur').addClass('b-layout__txt_hide');");
                    $objResponse->script("sbr.options.reztype = 'UABYKZ_FIZ';");
                } else {
                    $objResponse->script("$('alert_frl_is_jur').removeClass('b-layout__txt_hide');");
                    $objResponse->script("$('alert_frl_is_fiz').addClass('b-layout__txt_hide');");
                    $objResponse->script("sbr.options.reztype = 'UABYKZ';");
                }
            } elseif($emp_rez_type == 0) {
                $objResponse->script("$('alert_frl_is_fiz').addClass('b-layout__txt_hide');");
                $objResponse->script("$('alert_frl_is_jur').addClass('b-layout__txt_hide');");
                $objResponse->script("sbr.options.reztype = 'RU'; ");
            }
            $objResponse->script("$$('input[tmpname=\"cost\"]')[0].fireEvent('change')");

            $sbr = sbr_meta::getInstance();
            $sbr->frl_id = $frl->uid;
            $sbr_schemes = $sbr->getSchemes();
            $taxes = sbr_meta::jsSchemeTaxes($sbr_schemes, $frl_reqvs, $sbr->getUserReqvs(), sbr::EMP, exrates::BANK);
            $objResponse->script("sbr.options.schemes = {$taxes};");
            $objResponse->script("sbr.form.recalcTotal()");
        } else {
            $objResponse->script("$('unknown_frl_rez').removeClass('b-fon_hide');");
            $objResponse->script("if($('scheme_type".sbr::SCHEME_PDRD2."').checked) $('taxes_alert').show();");
        }
    }
    
    return $objResponse;
}

function setReqvs ($sbr_id, $params) {
    
    $objResponse = new xajaxResponse();
    //@todo: запрещаем изменять финансы в старой СБР #29196
    $objResponse->alert("Прекращена поддержка СБР.");
    return $objResponse;
    
    $objResponse->script("$('finance-update-btn').removeClass('b-button_disabled');");
    $sbr_id = intval($sbr_id);
    if (!$sbr_id) {
        return $objResponse;
    }
    
    $uid = get_uid(0);
    if (!$uid) {
        $objResponse->redirect('/');
        return $objResponse;
    }

    $reqvs = sbr_meta::getUserReqvs($uid);
    $sbr = sbr_meta::getInstance();
    $sbr->initFromId($sbr_id);
    if($sbr->status == sbr::STATUS_CANCELED) {
        ob_start();
        include ($_SERVER['DOCUMENT_ROOT']. "/sbr/freelancer/tpl.sbr_refuse.php");
        $html = ob_get_clean();
        
        $objResponse->assign('master_content', 'innerHTML', $html);
        $objResponse->script('$$(".b-master").destroy();');
    }
    
    $form_type = intval($params['form_type']);
    $rez_type = intval($params['rez_type']);
    if (!$rez_type) {
        $rez_type = $reqvs['rez_type'];
    }
    
    if (!$form_type || !$rez_type) {
        return $objResponse;
    }
    $oreqvs = $reqvs;
    $error = array();
    $reqvs1 = array();
    foreach ($params as $k => $v) {
        if (!array_key_exists($k, $reqvs[$form_type])) continue;
        $reqvs1[$k] = $v;
        $reqvs[$form_type][$k] = $v;
    }
    
    
    //if($err = sbr_meta::setUserReqv($uid, $rez_type, $form_type, $reqvs1)) {
    //    $error = $err;
    //}
    
    //@todo: запрещаем изменять финансы в старой СБР #29196
    $error = 'Прекращена поддержка СБР.';
    
    if( empty($error) && 
          ( 
               ( $reqvs1['mob_phone'] != $oreqvs[$form_type]['mob_phone'] && !($_SESSION['is_verify'] == 't') ) 
            || ($oreqvs['is_activate_mob'] == 'f') 
          )
    ) {
        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
        
        if($params['mob_phone'] != '') {
            $ureqv = $reqvs1;
            $sms_gate = new sms_gate_a1($ureqv['mob_phone']);
            $info     = $sms_gate->getInfoSend();
            $success = false;  
            if (!$sms_gate->isNextSend()) {
                $success = $sms_gate->sendAuthCellCode();
                if (SMS_GATE_DEBUG) {
                    $code_debug = $sms_gate->getAuthCode();
                }
            }
            
            if(SMS_GATE_DEBUG && !$code_debug) {
                $code_debug = $info['data'];
            }
            $callback_js = 'a_sms_act';
            $limitIsExceed = $sms_gate->limitSmsOnNumberIsExceed($params['mob_phone'], $recordId, $count, $message);
            $linkText = "СМС не пришло";
            $linkStyle = sms_gate_a1::$enable_link_css;
            if ($message) {
                $linkText = "СМС не пришло ({$message})";
            }
            if ($limitIsExceed) {
                $linkText = sms_gate_a1::LIMIT_EXCEED_LINK_TEXT;
                $linkStyle = sms_gate_a1::$disable_link_css;
            }
            if ($success) {
                $limitIsExceed = false;
            }
            ob_start();
            include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.auth_sms_popup.php');
            $out = ob_get_clean();
            $objResponse->assign("auth_popup", "innerHTML", $out); 
            $objResponse->script("$('auth_popup').show(); $('auth_popup').removeClass('b-shadow_hide'); shadow_popup();");
            $objResponse->script("$('send_btn').removeClass('b-button_rectangle_color_disable');
                                  $('send_btn').getElement('.b-button__txt').removeClass('b-button__txt_hide');
                                  $('send_btn').getElement('.b-button__load').hide();");
            return $objResponse;
        }
    } else if ( $oreqvs['is_activate_mob'] == 't' && $reqvs1['mob_phone'] != $oreqvs[$form_type]['mob_phone'] ) {
    	$error["mob_phone"] = "У вас привязка к другому номеру";
    }
    
    
    
    //Проверка наличия и идентификации веб-кошелька только для физиков!
    if(!$error && !is_emp())
    {
        $phone = $oreqvs[$form_type]['mob_phone'];

        $pskb  = new pskb;
        $res   = $pskb->checkOrCreateWallet($phone);
        
        if ( empty($res) ) 
        {
           $error["mob_phone"] = 'Ошибка соединения с Веб-кошельком.';
        }
        else
        {
            $res = json_decode($res, 1);

            if ( empty($res['state']) || in_array($res['state'], array('COMPLETE')) ) //'EXIST'
            {
                $error["mob_phone"] = '
                    Веб-кошелек с указанным номером отсутствует. Для проведения сделки 
                    зарегистрируйте и идентифицируйте Веб-кошелек на указанный номер.';
            }
            elseif ( !$res['identified'] ) 
            {
                $error["mob_phone"] = '
                    Ваш Веб-кошелек не идентифицирован. Для проведения сделки укажите 
                    другой номер телефона и кошелька или идентифицируйте текущий Веб-кошелек.';
            }
        }
    }
    
    

    if (!$error) {
        //$_SESSION['users.setup.fin_success'] = 1;
        if(!hasPermissions('users')) {
            $smail = new smail();
            $smail->FinanceChanged($_SESSION['login']);
        }
    }
    
    if ($error) {
        $objResponse->call('finance_err_set', $error, $form_type);
        return $objResponse;
    }
    
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
    $pskb_frl = new pskb_user($reqvs, intval(is_emp()), $sbr->getTotalCost());
    $psystems = $pskb_frl->getPsystems();
    
    
    $objResponse->script("psysDisabled = " . json_encode($psystems['disabled']) . ";");
    $objResponse->script("psysHidden = " . json_encode($psystems['hidden']) . ";");
    $objResponse->script('if(finance_check(null, true)) finance_prepare();');
//    $objResponse->call('finance_spinn_hide');
    
    return $objResponse;
}


function preparePayment($sbr_id, $mode_type) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
    $objResponse = new xajaxResponse();
//    $objResponse->call('finance_spinn_hide');
    
    $sbr_id = __paramValue('int', $sbr_id);
    $mode_type = __paramValue('int', $mode_type);
    if (!$sbr_id || !$mode_type) {
        $objResponse->call('finance_raise_err', 'Запрос не может быть обработан.');
        return $objResponse;
    }
    
    $uid = get_uid(0);
    if (!$uid) {
        $objResponse->redirect('/');
        return $objResponse;
    }
    
    $sbr = sbr_meta::getInstance();
    if(!$sbr->initFromId($sbr_id)) {
        $objResponse->call('finance_raise_err', 'Запрос не может быть обработан.');
        return $objResponse;
    }
    if($sbr->status == sbr::STATUS_REFUSED) {
        $objResponse->call('finance_raise_err', 'Запрос не может быть обработан. Исполнитель отказался от сделки.');
        return $objResponse;
    }
    if ($sbr->data['scheme_type'] != sbr::SCHEME_LC) {
        $objResponse->call('finance_raise_err', 'Указанная схема не поддерживается.');
        return $objResponse;
    }
    
    $pskb = new pskb($sbr);
    $lc = $pskb->getLC();
    
    if ($lc['state'] == pskb::STATE_COVER) {
        $objResponse->script('document.location.reload();');
        return $objResponse;
    }

    if($lc['lc_id'] > 0 && $lc['state'] == pskb::STATE_ERR) {
        $resp  = $pskb->_checks(json_encode(array('id' => array($lc['lc_id']))));
        $lc_ch = $resp[$lc['lc_id']];
        
        if($lc_ch->state == pskb::STATE_NEW) {
            $user = $pskb->initPskbUser($mode_type);
            $checkReqvNew = $pskb->getMd5Reqvs($user->getParams());
            $checkReqvOld = $pskb->getMd5Reqvs($lc, 'Cust');
            $pskb->diffUserReqvs($checkReqvNew, $checkReqvOld);
        } elseif($lc_ch->state == pskb::STATE_COVER) {
            $pskb->upLC(array('state' => 'new'), $lc['lc_id']);
            pskb::checkStatus(array($lc['lc_id']), $in, $out);
            $objResponse->script('document.location.reload();');
            return $objResponse;
        }
    }
    
    if ($pskb->prepareLC($mode_type) && $pskb_lc = $pskb->reserve()) {
        if(is_object($pskb_lc)) { // Все идет по плану
            $objResponse->call('finance_add_fld', 'source', onlinedengi::SOURCE_ID);
            $objResponse->call('finance_add_fld', 'order_id', $pskb_lc->id);
            $objResponse->call('finance_add_fld', 'nickname', $pskb_lc->id);
            $objResponse->call('finance_add_fld', 'amount', $sbr->getReserveSum(true, pskb::$exrates_map[$mode_type]));
            $objResponse->call('xajax_checkPayment', $sbr_id);
        } elseif($pskb_lc == 'no_different') {
            $pskb->upLC(array('state' => 'new'), $lc['lc_id']);
            if ($mode_type == onlinedengi::CARD) {
                $objResponse->call('pskb_frame', $lc['lc_id'], pskb::getNonceSign($lc['lc_id']));
            } else {
                $objResponse->call('finance_add_fld', 'source', onlinedengi::SOURCE_ID);
                $objResponse->call('finance_add_fld', 'order_id', $lc['lc_id']);
                $objResponse->call('finance_add_fld', 'nickname', $lc['lc_id']);
                $objResponse->call('finance_add_fld', 'amount', $sbr->getReserveSum(true, pskb::$exrates_map[$mode_type]));
                $objResponse->call('finance_send_frm');
            }
        }
    } else {
        $objResponse->call('finance_raise_err', $pskb->getError());
    }
    
    return $objResponse;
}

function checkPayment ($sbr_id, $delay = null) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi.php");
    $objResponse = new xajaxResponse();
    
    $sbr_id = __paramValue('int', $sbr_id);
    if (!$sbr_id) {
        $objResponse->call('finance_raise_err', 'Запрос не может быть обработан.');
        return $objResponse;
    }
    
    $uid = get_uid(0);
    if (!$uid) {
        $objResponse->redirect('/');
        return $objResponse;
    }
    
    $sbr = sbr_meta::getInstance();
    if(!$sbr->initFromId($sbr_id)) {
        $objResponse->call('finance_raise_err', 'Запрос не может быть обработан.');
        return $objResponse;
    }
    
    if($sbr->status == sbr::STATUS_REFUSED) {
        $objResponse->call('finance_raise_err', 'Запрос не может быть обработан. Исполнитель отказался от сделки.');
        return $objResponse;
    }
    
    if ($sbr->data['scheme_type'] != sbr::SCHEME_LC) {
        $objResponse->call('finance_raise_err', 'Указанная схема не поддерживается.');
        return $objResponse;
    }
    
    $pskb = new pskb($sbr);
    $state = $pskb->checkNew();
    if ($state == 'err') {
        $objResponse->call('finance_raise_err', $pskb->getError());
        return $objResponse;
    }
    
    if ($state == 'form') {
        $objResponse->script("setTimeout(function() { xajax_checkPayment({$sbr_id});}, 2000);");
        return $objResponse;
    }
    
    if ($state == 'new') {
        $lc = $pskb->getLC();
        if ($lc['ps_emp'] == onlinedengi::BANK_YL) {
            $sbr->getDocs();
            if($sbr->docs) {
                foreach($sbr->docs as $doc) {
                    if($doc['type'] == sbr::DOCS_TYPE_STATEMENT) {
                        $doc_file = new CFile($doc['file_id']);
                        //$doc_file->original_name = $doc['name'];
                        $doc_file->delete($doc['file_id']);
                        $sbr->removeEvent(24);
                    }
                }
            }
            $stage = current($sbr->stages);
            if($lc['ps_emp'] == onlinedengi::BANK_YL) {
                if ($doc_file = $stage->generateStatement($doc_err, $lc)) { // формируем заявление на аккредитив
                    $doc = array ('file_id'     => $doc_file->id, 
                                  'status'      => sbr::DOCS_STATUS_SIGN, 
                                  'access_role' => sbr::DOCS_ACCESS_EMP, 
                                  'owner_role'  => 0, 
                                  'type'        => sbr::DOCS_TYPE_STATEMENT, 
                                  'subtype'     => 1);
                    $sbr->addDocR($doc);
                    $doc_file->original_name = $sbr->post_doc['name'];
                }
            }
            $objResponse->script('document.location.reload();');
            return $objResponse;
        }
        
        if ($lc['ps_emp'] == onlinedengi::CARD) {
            $objResponse->call('pskb_frame', $lc['lc_id'], pskb::getNonceSign($lc['lc_id']));
        } else {
            $objResponse->call('finance_send_frm');
        }
        return $objResponse;
    }
    
    if ($state == 'cover') {
        $objResponse->script('document.location.reload();');
        return $objResponse;
    }
    
//    $objResponse->call('finance_raise_err', 'Указанная схема не поддерживается.');
    
    return $objResponse;
}

/**
 * Генерирует заявление асинхронно только в случае если его нет 
 */
function generateStatement($sbr_id) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
    $objResponse = new xajaxResponse();
    
    $sbr_id = __paramValue('int', $sbr_id);
    
    $sbr = sbr_meta::getInstance();
    if(!$sbr->initFromId($sbr_id)) {
        return $objResponse;
    }
    $pskb = new pskb($sbr);
    $lc   = $pskb->getLC();
    if($lc['ps_emp'] != onlinedengi::BANK_YL) {
        return $objResponse;
    }
    $sbr->getDocs();
    
    foreach($sbr->docs as $doc) {
        if($doc['type'] == sbr::DOCS_TYPE_STATEMENT) {
            $doc_file = new CFile($doc['file_id']);
            $doc_file->original_name = $doc['name'];
            
            $content_file = '<div class="b-layout__txt"><i class="b-icon b-icon_attach_pdf"></i> <a class="b-layout__link" href="' . WDCPREFIX . '/' . $doc_file->path . $doc_file->name . ' ">' . $doc_file->original_name . '</a>, ' . ConvertBtoMB($doc_file->size) . '</div>';
            $info_file    = '<div class="b-layout__txt"><a class="b-layout__link" href="' . WDCPREFIX . '/' . $doc_file->path . $doc_file->name . '">Скачать файл</a></div>';

            $objResponse->assign('content_statement_doc', "innerHTML",  $content_file);
            $objResponse->assign('info_statement_doc', "innerHTML",  $info_file);
            
            return $objResponse;
        }
    }
    
    if ($doc_file = $sbr->stages[0]->generateStatement($doc_err, $lc)) { // формируем заявление на аккредитив
        $doc = array ('file_id'     => $doc_file->id, 
                      'status'      => sbr::DOCS_STATUS_SIGN, 
                      'access_role' => sbr::DOCS_ACCESS_EMP, 
                      'owner_role'  => 0, 
                      'type'        => sbr::DOCS_TYPE_STATEMENT, 
                      'subtype'     => 1);
        $sbr->addDocR($doc);
        $doc_file->original_name = $sbr->post_doc['name'];
        
        $content_file = '<div class="b-layout__txt"><i class="b-icon b-icon_attach_pdf"></i> <a class="b-layout__link" href="' . WDCPREFIX . '/' . $doc_file->path . $doc_file->name . ' ">' . $doc_file->original_name . '</a>, ' . ConvertBtoMB($doc_file->size) . '</div>';
        $info_file    = '<div class="b-layout__txt"><a class="b-layout__link" href="' . WDCPREFIX . '/' . $doc_file->path . $doc_file->name . '">Скачать файл</a></div>';
        
        $objResponse->assign('content_statement_doc', "innerHTML",  $content_file);
        $objResponse->assign('info_statement_doc', "innerHTML",  $info_file);
    }
    
    return $objResponse;
}

function subOpen($sbr_id, $code, $stage_id) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
    $objResponse = new xajaxResponse();
    
    $stage_id = __paramValue('int', $stage_id);
    $code = __paramValue('string', $code);
    
    $sbr_id = __paramValue('int', $sbr_id);
    if (!$sbr_id || !$code) {
        $objResponse->call('_raise_err', 'Запрос не может быть обработан.');
        return $objResponse;
    }
    
    $uid = get_uid(0);
    if (!$uid) {
        $objResponse->redirect('/');
        return $objResponse;
    }
    
    $sbr = sbr_meta::getInstance();
    if(!$sbr->initFromId($sbr_id)) {
        $objResponse->call('_raise_err', 'Запрос не может быть обработан.');
        return $objResponse;
    }
    if ($sbr->data['scheme_type'] != sbr::SCHEME_LC) {
        $objResponse->call('_raise_err', 'Указанная схема не поддерживается.');
        return $objResponse;
    }
    
    $pskb = new pskb($sbr);
    if ($pskb->payoutConfirm($code, $stage_id)) {
        $objResponse->script('document.location.reload();');
    } else {
        $objResponse->call('_raise_err', $pskb->getError());
    }
    
    return $objResponse;
}

function resendCode ($sbr_id, $stage_id) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
    
    $sbr_id = __paramValue('int', $sbr_id);
    $stage_id = __paramValue('int', $stage_id);
    
    $objResponse = new xajaxResponse();
    
    $objResponse->script("$('alert_sms').removeClass('b-layout__txt_color_56bd06').removeClass('b-layout__txt_color_c7271e'); ");
    
    if (!$sbr_id) {
        $objResponse->script("$('alert_sms').addClass('b-layout__txt_color_c7271e'); 
                              $('alert_sms').set('html', 'Запрос не может быть обработан.');");
        //$objResponse->alert('Запрос не может быть обработан.');
        return $objResponse;
    }
    
    $m = new memBuff();
    $lasttime = $m->get(pskb::SMS_RESEND_KEY . $sbr_id);
    if ($lasttime) {
        $mins = ceil(($lasttime+300 - time())/60);
        $objResponse->script("$('alert_sms').addClass('b-layout__txt_color_c7271e'); 
                              $('alert_sms').set('html', 'Еще раз выслать код можно будет через {$mins} минут" . ending($mins, 'у', 'ы', '') . ".');");
        //$objResponse->alert("Повторный запрос можно будет сделать примерно через {$mins} минут" . ending($mins, 'у', 'ы', '') );
        return $objResponse;
    }
    
    $uid = get_uid(0);
    if (!$uid) {
        $objResponse->redirect('/');
        return $objResponse;
    }
    
    $sbr = sbr_meta::getInstance();
    if(!$sbr->initFromId($sbr_id)) {
        $objResponse->script("$('alert_sms').addClass('b-layout__txt_color_c7271e'); 
                              $('alert_sms').set('html', 'Запрос не может быть обработан.');");
        return $objResponse;
    }
    
    $stage = $sbr->initFromStage($stage_id, false);
    if(!$stage) {
        $objResponse->script("$('alert_sms').addClass('b-layout__txt_color_c7271e'); 
                              $('alert_sms').set('html', 'Запрос не может быть обработан.');");
        return $objResponse;
    }
    
    if ($uid != $sbr->data['frl_id']) {
        $objResponse->script("$('alert_sms').addClass('b-layout__txt_color_c7271e'); 
                              $('alert_sms').set('html', 'Запрос не может быть обработан.');");
        //$objResponse->alert('Запрос не может быть обработан.');
        return $objResponse;
    }
    
    $pskb = new pskb($sbr);
    if (!$pskb->resendCode($stage)) {
        $objResponse->script("$('alert_sms').addClass('b-layout__txt_color_c7271e'); 
                              $('alert_sms').set('html', 'Не удалось выслать код.');");
        //$objResponse->alert('Ошибка запроса. Попробуйте еще раз.');
        return $objResponse;
    }
    
    $m->set(pskb::SMS_RESEND_KEY . $sbr_id, time(), 300);
    
    $objResponse->script("$('alert_sms').addClass('b-layout__txt_color_56bd06'); 
                          $('alert_sms').set('html', 'Код отправлен повторно.');
                          $('send_sms').destroy();
                          $('resend_sms').set('html', 'Еще раз выслать код можно будет через 5 минут.')");
    //$objResponse->alert('Код отправлен повторно.');
    
    if (defined('PSKB_TEST_MODE')) {
        $objResponse->script('document.location.reload();');
    }
    
    return $objResponse;
}

function updCostSys($sbr_id, $cost_sys) {
    $objResponse = new xajaxResponse();
    
    $cost_sys   = __paramValue('int', $cost_sys);
    $sbr_id     = __paramValue('int', $sbr_id);
    
    $sbr = sbr_meta::getInstance();
    if(!$sbr->initFromId($sbr_id)) {
        return $objResponse;
    }
    
    if ($sbr->data['scheme_type'] == sbr::SCHEME_PDRD2) {
        if($sbr->setCostSys($cost_sys)) {
            $objResponse->script('$("cost_sys_set").set("value", "'.$cost_sys.'");');
            $objResponse->call('submitReservePdrd');
        } 
    }
    
    return $objResponse;
}

function aCompleteEvent($xact_id) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_notification.php');
    
    $objResponse = new xajaxResponse();
    if( !(hasPermissions('sbr')  && $_SESSION['access']=='A') ) {
        return $objResponse;
    }
    $xact_id = intval($xact_id);
    $compl = sbr_notification::setNotificationCompletedAdmin($xact_id);
    
    if($compl) {
        $objResponse->script("$('event_react_{$xact_id}').removeClass('b-fon__body_bg_f0ffdf').removeClass('b-fon__body');");
        $objResponse->script("$('adm_react_link_{$xact_id}').dispose()");
    }
    
    return $objResponse;
}

function aGetLCInfo($sbr_id) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) {
        return $objResponse;
    }
    
    $sbr_id = __paramValue('int', $sbr_id);
    if (!$sbr_id) {
        $objResponse->alert('Ошибка запроса.');
        return $objResponse;
    }
    
    $sbr = sbr_meta::getInstance( sbr_meta::ADMIN_ACCESS );
    if(!$sbr->initFromId($sbr_id)) {
        $objResponse->alert('Сделка не найдена.');
        return $objResponse;
    }
    
    $pskb = new pskb($sbr);
    $data = $pskb->getLCInfo($id);
    
    if (!$data) {
        $objResponse->alert($pskb->getError());
        return $objResponse;
    }
    
    $lc = $data['lc'];
    $pskb_lc = $data['pskb_lc'];
    $payouts = $data['payouts'];
    
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'].'/sbr/admin/tpl.lc-info.php');
    $out = ob_get_clean();
    
//    $objResponse->script("$$('#lc-info-popup').inject(document.body.getElement('div.main'),'top');"); 
    $objResponse->script("$$('#lc-info-popup').inject($('pp-place-{$lc['sbr_id']}'));"); 
    $objResponse->script("$$('#lc-info-popup, #lc-info-popup .b-shadow').removeClass('b-shadow_hide');"); 
    $objResponse->assign('lc-info-popup-body', 'innerHTML', $out);  
    
    return $objResponse;
}


/**
 * Функция для пересоздания документа
 * 
 * @param integer $doc_id  ИД текущего документа
 * @param integer $uid     ИД Пользователя
 * @return \xajaxResponse
 */
function aRecreateDocLC($doc_id, $uid, $stage_id, $action = 'create', $interface = 'admin') {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) {
        return $objResponse;
    }
    
    $doc_id   = intval($doc_id);
    $uid      = intval($uid);
    $stage_id = intval($stage_id);
    
    if(!$doc_id) {
        $objResponse->alert('Не корректный документ.');
        return $objResponse;
    }
    
    if(!$uid) {
        $objResponse->alert('Не корректный пользователь.');
        return $objResponse;
    }
    
    $sbr   = sbr_meta::getInstanceLocal($uid);
    
    if (!$sbr) {
        $objResponse->alert('Ошибка запроса.');
        return $objResponse;
    }
    
    $stage = $sbr->initFromStage($stage_id);
    
    if(!$stage) {
        $objResponse->alert('Этап не найден.');
        return $objResponse;
    }
    if($stage->status == sbr_stages::STATUS_ARBITRAGED) {
        $stage->getArbitrage(false, false);
    }
    $doc = $sbr->getDoc($doc_id, true, true);
    $gen = $stage->recreateDoc($doc, $action);
    
    
    if($gen) {
        
        if($interface == 'admin') {
            $doc_act = $sbr->getDocs(NULL, NULL, true, $stage_id, true);

            ob_start();
            include($_SERVER['DOCUMENT_ROOT'].'/sbr/admin/tpl.lc-docinfo.php');
            $out = ob_get_clean();

            $objResponse->assign("doc_content_{$stage_id}", "innerHTML", $out);  
        } else {
            $stage->getAllFiles();
            
            ob_start();
            include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.stage-files.php');
            $out = ob_get_clean();
            
            $objResponse->assign("doc_content", "innerHTML", $out); 
        }
    } else {
        $objResponse->alert('Ошибка генерации документа.');
    }
    
    return $objResponse;
}

function aGetHistoryLC($id, $uid = null, $target = null) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
    
    $id = __paramValue('int', $id);
    
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) { 
        return $objResponse;
    }
    
    $pskb = new pskb();
    $history = $pskb->getHistoryLC($id, $uid, $target);
    
    if(!is_array($history)) {
        if($history->id > 0) {
            $history = array($history);
        } else {
            $history = false;
        }
    }
    
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'].'/sbr/admin/tpl.history-lc.php');
    $out = ob_get_clean();
    
    if($target !== null) {
        $content_name = "user{$target}_history_lc_{$uid}";
    } else {
        $content_name = "history_lc_{$uid}";
    }
    $objResponse->assign("{$content_name}-body", "innerHTML", $out); 
    $objResponse->script("$('{$content_name}').removeClass('b-shadow_hide')");
    return $objResponse;
}

function aCreateDocITO($date, $doc = 'odt') {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) { 
        return $objResponse;
    }
    
    $period = array(
        0 => date('Y-m-01', strtotime($date)),
        1 => date('Y-m-t', strtotime($date))
    );
    
    $doc_ito = sbr_meta::generateDocITO($period, true, $doc);
    
    $date_create_id = "date_create_" . date('Yn', strtotime($date));
    $file_name_id = "file_name_" . date('Yn', strtotime($date));
    $link = WDCPREFIX."/{$doc_ito->path}{$doc_ito->name}";
    
    $objResponse->assign($date_create_id, "innerHTML", date('d.m.Y H:i'));
    $objResponse->script("$('{$file_name_id}').setProperty('href', '{$link}')");
    
    return $objResponse;
}

function authSMS($uid, $action = 'send', $phone = null) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
    
    $objResponse = new xajaxResponse();
    //@todo: запрещаем изменять финансы в старой СБР #29196
    $objResponse->alert("Прекращена поддержка СБР.");
    return $objResponse;
    
    if(!$uid) {
        $uid = get_uid();
    } 
    
    if($uid != get_uid() || get_uid() <= 0) {
        return $objResponse;
    }
    
    $reqv = sbr_meta::getUserReqvs($uid);
    if($reqv['user_id'] == NULL) { // Еще не создано
        $reqv['from_type']       = 1;
        $reqv['rez_type']        = sbr::RT_RU;
        $reqv['is_activate_mob'] = 'f';
    }
    $ureqv = $reqv[$reqv['form_type']];
    
    if($phone !== null && $reqv['is_activate_mob'] == 'f' && $phone != $ureqv['mob_phone'] && $_SESSION['is_verify'] != 't') {
        $ureqv['mob_phone'] = $phone;
        $nreqv['mob_phone'] = $phone;
        
        //@todo: запрещаем изменять финансы в старой СБР #29196
        //$error = sbr_meta::setUserReqv($uid, $reqv['rez_type'], $reqv['from_type'], $nreqv);
        
        if($error['mob_phone']) {
            $objResponse->call('alert', $error['mob_phone']);
            return $objResponse;
        }
    }
    
    // Если уже активировано
    if($reqv['is_activate_mob'] == 't') {
        $html = '<div class="b-layout__txt b-layout__txt_padtop_7 b-layout__txt_nowrap b-layout__txt_inline-block"><span class="b-icon b-icon_sbr_gok b-icon_top_2"></span>Активирован</div>';
        $objResponse->script("$('auth_popup').set('html', '');$('auth_popup').hide(); $$('.c_sms_main').set('html', '{$html}');");
        return $objResponse;
    }
    
    $sms_gate = new sms_gate_a1($ureqv['mob_phone']);
    $info     = $sms_gate->getInfoSend();
    if(!$sms_gate->isNextSend() && ( (in_array($action, array('send', 'safety'))) || $action == 'resend' ) ) {
        $sms_gate->sendAuthCellCode();
        if(SMS_GATE_DEBUG) {
            $code_debug = $sms_gate->getAuthCode();
        }
    } elseif($action == 'resend') {
        $timer = $sms_gate->next_time_send - time();
        $objResponse->alert("Следующее сообщение можно будет послать через {$timer} ". ending($timer, 'секунду', 'секунды', 'секунд'));
        return $objResponse;
    } else {
        $timer = $sms_gate->next_time_send - time();
        if($timer > 0) {
            $objResponse->alert("Следующее сообщение можно будет послать через {$timer} ". ending($timer, 'секунду', 'секунды', 'секунд'));
            return $objResponse;
        }
    }
    
    if(SMS_GATE_DEBUG && !$code_debug) {
        $code_debug = $info['data'];
    }
    
    if($action == 'send') {
        $callback_js = 'a_sms_act';
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.auth_sms_popup.php');
        $out = ob_get_clean();
        $objResponse->assign("auth_popup", "innerHTML", $out); 
        $objResponse->script("$('auth_popup').show(); $('auth_popup').removeClass('b-shadow_hide'); shadow_popup();");
    } elseif($action == 'safety') {
        $callback_js = 'a_sms_act_safety';
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.auth_sms_popup.php');
        $out = ob_get_clean();
        $objResponse->assign("auth_popup", "innerHTML", $out); 
        $objResponse->script("$('auth_popup').show();$('auth_popup').removeClass('b-shadow_hide'); shadow_popup();");
    } else {
    	$text = "СМС не пришло";
    	$isExceed = $sms_gate->limitSmsOnNumberIsExceed($ureqv['mob_phone'], $recId, $count, $message);
    	$css1 = sms_gate_a1::$disable_link_css;
    	$css2 = sms_gate_a1::$enable_link_css;
    	if (!$isExceed) {
            $text .= " ($message)";
    	} else {
    		$text = $message;
    		$buf = $css1;
    		$css1 = $css2;
    		$css2 = $buf;
    	}
        $objResponse->script("$('a_sms_act').removeClass('b-button_rectangle_color_disable');
                              $('sms_error').addClass('b-layout__txt_hide');
                              $('i_sms_code').getParent().removeClass('b-combo__input_error');
                              $('a_sms_resend').set('text', '{$text}');
                              $('a_sms_resend').removeClass('{$css1}').addClass('{$css2}');");
        if($code_debug) {
            $objResponse->script("$('i_sms_code').set('value', '{$code_debug}');");
        }
    }
    
    return $objResponse;
}

function authCodeSMS($code, $page = 'finance') {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    $objResponse = new xajaxResponse();
    
    $uid = get_uid();
    
    $reqv = sbr_meta::getUserReqvs($uid);
    $ureqv = $reqv[$reqv['form_type']];
    
    $sms_gate = new sms_gate_a1($ureqv['mob_phone']);
    $info = $sms_gate->getInfoSend();
    
    if($info['data'] == $code && $info['data'] != null) {
        sbr_meta::authMobPhone($uid);
        $sms_gate->setIsAuth($info['id'], true); // Обновляем флаг
        switch($page) {
            case 'finance':
                $html = '<div class="b-layout__txt b-layout__txt_padtop_7 b-layout__txt_nowrap b-layout__txt_inline-block"><span class="b-icon b-icon_sbr_gok b-icon_top_2"></span>Активирован</div>';
                $objResponse->script("$('auth_popup').set('html', ''); $('auth_popup').hide(); $$('.c_sms_main').set('html', '{$html}');");
                break;
            case 'safety':
                $html = '&#160;&#160;<div class="b-layout__txt b-layout__txt_inline-block"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)">Отвязать</a></div>';
                $objResponse->script("$('auth_popup').set('html', ''); 
                                      $('auth_popup').hide(); 
                                      $$('.c_sms_main').set('html', '{$html}');
                                      $$('.safety_phone_checks').each( function(el) {
                                          $(el).getElements('input[type=checkbox]').set('disabled', false);
                                      });
                                      $('safety_mob_phone').addClass('b-combo__input_disabled');
                                      $('safety_mob_phone').getElement('input').set('disabled', true);
                                      $('safety_status').set('html', 'включена');
                                      $('safety_status').removeClass('b-layout__txt_color_c10600').addClass('b-layout__txt_color_6bb336');
                                      bindLinkUnativateAuth('{$uid}');
                                      ");
                break;
        }
        
    } else {
        $objResponse->script("$('a_sms_act').getElement('.b-button__txt').removeClass('b-button__txt_hide');
                              $('a_sms_act').getElement('.b-button__load').hide();
                              $('a_sms_act').addClass('b-button_rectangle_color_disable');
                              $('a_sms_resend').set('text', 'Выслать СМС еще раз');");
        $objResponse->script("$('sms_error').removeClass('b-layout__txt_hide'); $('i_sms_code').getParent().addClass('b-combo__input_error');");
    }
    
    return $objResponse;
}

function unactivateAuth($uid, $action = 'safety') {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    
    $objResponse = new xajaxResponse();
    if($uid != get_uid()) {
        return $objResponse;
    }
    
    $reqv = sbr_meta::getUserReqvs($uid);
    $ureqv = $reqv[$reqv['form_type']];
    
    $sms_gate = new sms_gate_a1($ureqv['mob_phone']);
    $info     = $sms_gate->getInfoSend();
            
    if(!$sms_gate->isNextSend() && ( $action == 'safety' || $action == 'resend' ) ) {
        $sms_gate->sendAuthCellCode();
        if(SMS_GATE_DEBUG) {
            $code_debug = $sms_gate->getAuthCode();
        }
    } elseif($action == 'resend') {
        $timer = $sms_gate->next_time_send - time();
        $objResponse->alert("Следующее сообщение можно будет послать через {$timer} ". ending($timer, 'секунду', 'секунды', 'секунд'));
        return $objResponse;
    }
    
    if(SMS_GATE_DEBUG && !$code_debug) {
        $code_debug = $info['data'];
    }
    
    if($action == 'safety' || $action == 'resend') {
        $callback_js = 'a_sms_unact_safety';
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.unauth_sms_popup.php');
        $out = ob_get_clean();
        $objResponse->assign("auth_popup", "innerHTML", $out); 
        $objResponse->script("$('auth_popup').show().removeClass('b-shadow_hide'); shadow_popup();");
    }
    
    return $objResponse;
}

function unauthCodeSMS($code) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    $objResponse = new xajaxResponse();
    
    $uid = get_uid();
    
    $reqv = sbr_meta::getUserReqvs($uid);
    $ureqv = $reqv[$reqv['form_type']];
    
    $sms_gate = new sms_gate_a1($ureqv['mob_phone']);
    $info = $sms_gate->getInfoSend();
    
    if($info['data'] == $code) {
        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php');
        $user = new users();
        sbr_meta::authMobPhone($uid, false);
        sbr_meta::safetyMobPhone($uid, false);
        $user->updateSafetyPhone($uid, false);
        
        $html = '<a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent b-button_margtop_-2" data-send="safety"><span class="b-button__b1"><span class="b-button__b2"><span class="b-button__txt">Активировать</span></span></span></a>';
        if ( $_SESSION['is_verify'] != 't' ) {
            $mobphone = "$('safety_mob_phone').removeClass('b-combo__input_disabled'); $('safety_mob_phone').getElement('input').set('disabled', false);";
        } else {
            $mobphone = "";
        }

        $objResponse->script("$('auth_popup').set('html', '').hide();
                              $$('.c_sms_main').set('html', '{$html}');
                              $$('.safety_phone_checks').each( function(el) {
                                  $(el).getElements('input[type=checkbox]').set('disabled', true).set('checked', false);
                              });
                              {$mobphone}
                              $('safety_status').set('html', 'выключена');
                              $('safety_status').addClass('b-layout__txt_color_c10600').removeClass('b-layout__txt_color_6bb336');
                              bindLinkActivateAuth();
                              ");
        return $objResponse;
    }
}

function resendAuthCode() {
    $objResponse = new xajaxResponse();
    
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    
    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }
    
    $reqv = sbr_meta::getUserReqvs($uid);
    $ureqv = $reqv[$reqv['form_type']];
    
    $sms_gate = new sms_gate_a1($ureqv['mob_phone']);
    if(!$sms_gate->isNextSend()) {
        $sms_gate->sendAuthCellCode();
        $_SESSION['sms_access_code'] = $sms_gate->getAuthCode();
    } else {
        $timer = $sms_gate->next_time_send - time();
        $objResponse->alert("Следующее сообщение можно будет послать через {$timer} ". ending($timer, 'секунду', 'секунды', 'секунд'));
        return $objResponse;
    }
    
    if(SMS_GATE_DEBUG) {
        $objResponse->script("$('auth_sms_code').set('value', '{$_SESSION['sms_access_code']}')");
    }
    $objResponse->alert('Код выслан повторно');
    return $objResponse;
}

function sendCode() {
    $objResponse = new xajaxResponse();
    
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    
    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }
    
    $reqv = sbr_meta::getUserReqvs($uid);
    $ureqv = $reqv[$reqv['form_type']];
    
    $sms_gate = new sms_gate_a1($ureqv['mob_phone']);
    $info     = $sms_gate->getInfoSend();
    
    if(!$sms_gate->isNextSend()) {
        $sms_gate->sendAuthCellCode();
        $_SESSION['sms_auth_code_now'] = $sms_gate->getAuthCode();
        if(SMS_GATE_DEBUG) {
            $code_debug = $sms_gate->getAuthCode();
        }
    } else {
        
        $timer = $sms_gate->next_time_send - time();
        if($timer > 0) {
            $objResponse->alert("Следующее сообщение можно будет послать через {$timer} ". ending($timer, 'секунду', 'секунды', 'секунд'));
        }
    }
    
    if(SMS_GATE_DEBUG && !$code_debug) {
        $code_debug = $info['data'];
    }
    
    $callback_js = 'a_sms_disabled_safety';
    $callback_resend = 'sendCode';
    $sms_title   = 'Подтверждение действий';
    $sms_btn     = 'Отправить';
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.auth_sms_popup.php');
    $out = ob_get_clean();
    $objResponse->assign("auth_popup", "innerHTML", $out); 
    $objResponse->script("$('auth_popup').show(); $('auth_popup').removeClass('b-shadow_hide'); shadow_popup();");
    
    return $objResponse;
}

function authCode($code) {
    $objResponse = new xajaxResponse();
    
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sms_gate_a1.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    
    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }
    
    if($code == $_SESSION['sms_access_code']) {
        $_SESSION['is_finance_access'] = true;
        $objResponse->script("window.location = window.location + '?auth';");
    } else {
        $objResponse->script("$('auth_sms_error').removeClass('b-layout__txt_hide'); 
                              $('auth_sms_code').getParent().addClass('b-combo__input_error');");
    }
    
    return $objResponse;
}

function aAddDocument($role, $type_document, $file_session) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) { 
        return $objResponse;
    }
}

function aDelDocument($stage_id, $doc_id) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    
    $stage_id = __paramValue('int', $stage_id);
    $doc_id = __paramValue('int', $doc_id);
    
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) { 
        return $objResponse;
    }
    
    $sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS);
    $sbr_id = $sbr->getSbrIdFromStage($stage_id);
    $sbr->initFromId($sbr_id, false, false, false);
    if($sbr->error) {
        return $objResponse;
    }
    $doc = $sbr->getDoc($doc_id);
    $stage = $sbr->initFromStage($stage_id, false);
    if($doc['is_deleted'] == 't') { // Восстанавливаем
        if($sbr->recoveryDocs($doc_id)) {
            $stage->removeEvent(29, true);
            $objResponse->script("$('doc_{$doc_id}').removeClass('b-layout__tr_bg_ffdfdf')");
        }
    } else {
        if($sbr->delDocs($doc_id)) {
            $stage->removeEvent(30, true);
            $objResponse->script("$('doc_{$doc_id}').addClass('b-layout__tr_bg_ffdfdf')");
        }
    }
    return $objResponse;
}

function aEditDocument($stage_id, $doc_id) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    
    $stage_id = __paramValue('int', $stage_id);
    $doc_id = __paramValue('int', $doc_id);
    
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) { 
        return $objResponse;
    }
    
    $sbr = sbr_meta::getInstance();
    $sbr_id = $sbr->getSbrIdFromStage($stage_id);
    $sbr->initFromId($sbr_id, true, false, false);
    if($sbr->error) {
        return $objResponse;
    }
    if($sbr->getDocs($doc_id)) {
        $doc   = current($sbr->docs);
        $stage = $sbr->initFromStage($stage_id, false);
        $doc_info = '<i class="b-icon b-icon_attach_' . getICOFile(CFile::getext($doc['file_name'])) . '"></i> <a class="b-layout__link" href="' . WDCPREFIX . '/' . $doc['file_path'] . $doc['file_name'] . '">' . $doc['name'] . '</a>, ' . ConvertBtoMB($doc['file_size']);
        
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/sbr/admin/tpl.popup-doc.php');
        $html = ob_get_clean();
    
        //$objResponse->script("$('popup_admin_files').addClass('b-shadow_hide');");
        $objResponse->assign("popup_admin_files_edit", 'innerHTML', $html);
        $objResponse->script("
        new attachedFiles2( $('popup_admin_files{$doc['id']}').getElement('.attachedfiles_admin_sbr{$doc['id']}'), {
            'hiddenName':   'attaches[]',
            'files':        '',
            'selectors': {'template' : '.attachedfiles_admin_sbr-tpl'}
        });");
        $objResponse->call("shadow_popup");
    }
    
    return $objResponse;
}

function aSaveDocument($stage_id, $doc_id, $name, $type, $access, $session) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    
    $stage_id = __paramValue('int', $stage_id);
    $doc_id = __paramValue('int', $doc_id);
    
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) { 
        $objResponse->script('window.sended = false');
        return $objResponse;
    }
    
    $sbr = sbr_meta::getInstance();
    $sbr_id = $sbr->getSbrIdFromStage($stage_id);
    $sbr->initFromId($sbr_id, true, false, false);
    if($sbr->error) {
        $objResponse->script('window.sended = false');
        return $objResponse;
    }
    
    $stage = $sbr->initFromStage($stage_id, false);
    
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php');
    $attachedfiles = new attachedfiles($session);
    $attach = current($attachedfiles->getFiles());
    if($attach['id']) {
        $file = new CFile($attach['id']);
        $file->table = 'file_sbr';
        $file->_remoteCopy($sbr->getUploadDir().$file->name);
    } else {
        $file = false;
    }
    
    if($doc_id) {
        $old_doc = $sbr->getDoc($doc_id);
        $doc = array(
            'name'        => $name,
            'type'        => $type,
            'access_role' => $access,
            'status'      => $old_doc['status'],
            'stage_id'    => $stage_id,
            'id'          => $doc_id
        );
        if($file) {
            $doc['file_id'] = $file->id;
        }
        $sbr->editDoc($doc, $old_doc);
        if($old_doc['access_role'] == 0 && $doc['access_role'] > 0) {
            $stage->removeEvent(29, true);
        } else if($old_doc['access_role'] > 0 && $doc['access_role'] == 0 ) {
            $stage->removeEvent(30, true);
        }
    } else {
        if(!$file) {
            $objResponse->script('window.sended = false');
            $objResponse->call('alert', 'Загрузите файл');
            return $objResponse;
        }
        $doc = array(
            'stage_id'    => $stage_id, 
            'file_id'     => $file->id, 
            'status'      => sbr::DOCS_STATUS_PUBL,
            'access_role' => $access,
            'owner_role'  => 0, 
            'type'        => $type);

        $add_doc = $sbr->addDocR($doc);
        
        if(!$add_doc) {
            $objResponse->script('window.sended = false;');
            $objResponse->call('alert', 'Ошибка запроса');
            return $objResponse;
        }
    }
    $objResponse->script("window.location.reload()");
    return $objResponse;
}

function aGetLogPSKBInfo($lc_id) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    
    $lc_id = __paramValue('int', $lc_id);
    
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) { 
        return $objResponse;
    }
    $log_pskb = new log_pskb();

    ob_start();
    include($_SERVER['DOCUMENT_ROOT'].'/sbr/admin/tpl.log_pskb.php');
    $out = ob_get_clean();
    
    $objResponse->assign("log_pskb_{$lc_id}-body", "innerHTML", $out); 
    $objResponse->script("$('log_pskb_{$lc_id}').removeClass('b-shadow_hide')");
    return $objResponse;
}

function aFindLogPSKB($lc_id, $query, $logname) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    
    $lc_id = __paramValue('int', $lc_id);
    
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) { 
        return $objResponse;
    }
    
    $param = array(
        'query'   => $query,
        'link_id' => $lc_id,
        'logname' => $logname
    );
    
    $log_pskb = new log_pskb();
    $content  = $log_pskb->findLogs($param);
    
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'].'/sbr/admin/tpl.log_pskb.content.php');
    $out = ob_get_clean();
    
    $objResponse->assign("log_content_{$lc_id}", "innerHTML", $out); 
    return $objResponse;
}

function aClearCloneLogPSKB($lc_id, $query, $logname) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    
    $lc_id = __paramValue('int', $lc_id);
    
    $objResponse = new xajaxResponse();
    
    if( !hasPermissions('sbr') ) { 
        return $objResponse;
    }
    
    $log_pskb = new log_pskb();
    $clear    = $log_pskb->clearCloneData($lc_id);
    
    if($clear) {
        $objResponse->call("alert", "Дублирующие записи удалены"); 
    } else {
        $objResponse->call("alert", "Ошибка удаления дублирующих записей"); 
    }
    
    return aFindLogPSKB($lc_id, $query, $logname);
}

/**
 * помечает отзыв о сервисе для показа в промоблоке
 * @param integer $feedbackID ID отзыва
 * @param bool $check если true - то отзыв разрешено показывать в промо-блоке
 * 
 */
function addFeedbackToPromo ($feedbackID, $check = true) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
    $objResponse = new xajaxResponse();
    
    sbr_meta::feedbackToPromo($feedbackID, $check);
    if (is_array($feedbackID)) {
        $objResponse->script("$('all_feedbacks_to_promo').set('disabled', false)");
        $objResponse->script("$$('.feedback_in_promo').set('checked', " . (int)$check . ")");
    } else {
        $objResponse->script("$('feedback_id_$feedbackID').set('disabled', false)");
    }
    return $objResponse;
}

/**
 * устанавливает арбитра
 */
function setArbitr ($arbitrageID, $arbitrID) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_stages.php');
    $objResponse = new xajaxResponse();
    
    if (!hasPermissions('sbr')) { 
        return $objResponse;
    }
    
    sbr_stages::setArbitr($arbitrageID, $arbitrID);

    return $objResponse;
}

$xajax->processRequest();