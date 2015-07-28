<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/xajax/paid-advices.common.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/paid_advices.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

function NewAdvice($to_user, $msgtext) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
    $objResponse = new xajaxResponse();
    
    $to_user = intval($to_user);
    
    $sbr = new sbr(get_uid(false));
    $isReqvsFilled = !$sbr->checkUserReqvs();
    
    if($isReqvsFilled && !is_emp($_SESSION['role'])) {
        $objResponse->call('alert', 'Заполните раздел «Финансы»');
        $objResponse->script("$$('a.advice-new').store('lock', 0);");
        return $objResponse;    
    }
    
    if (!$to_user) {
        $objResponse->call('alert', 'Ошибка');
        $objResponse->script("$$('a.advice-new').store('lock', 0);");
        return $objResponse;
    }
    
    if(is_empty_html($msgtext)) {
        $objResponse->call('alert', 'Вы не заполнили форму.');
        $objResponse->script("$$('a.advice-new').store('lock', 0);");
        return $objResponse;    
    }
    
    if(strlen_real($msgtext) > paid_advices::MAX_DESCR_ADVICE) {
        $objResponse->call('alert', 'Рекомендация не должна быть больше ' . paid_advices::MAX_DESCR_ADVICE . ' символов');
        $objResponse->script("$$('a.advice-new').store('lock', 0);");
        return $objResponse; 
    }
    
    $advice = new paid_advices();
    $new = $advice->add($to_user, $msgtext);
    if($new === false) {
        $objResponse->call('alert', 'Ошибка отправки рекомендации.');
        $objResponse->script("$$('a.advice-new').store('lock', 0);");
        return $objResponse;  
    }
    $objResponse->call('newAdviceResp', $res);
    return $objResponse;
}

function AcceptedAdvice($id_advice) {
    $objResponse = new xajaxResponse();
    
    $id_advice = intval($id_advice);
    if (!$id_advice) {
        $objResponse->call('alert', 'Ошибка');
        return $objResponse;
    }
    
    $advice = new paid_advices(); 
    $advice->accepted($id_advice);
    
    $objResponse->script("window.location = '/users/{$_SESSION['login']}/opinions/?from=norisk&edit={$id_advice}#op_head'");
    
    return $objResponse;    
}

function DeclineAdvice($id_advice, $status) {
    $objResponse = new xajaxResponse();
    
    $status = intval($status);
    $id_advice = intval($id_advice);
    if (!$id_advice) {
        $objResponse->call('alert', 'Ошибка');
        return $objResponse;
    }
    
    $advice = new paid_advices(); 
    
    $advice->decline($id_advice);
    
    $html = 'Вы отказались от рекомендации. <a class="b-fon__link b-fon__link_fontsize_13 b-fon__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="xajax_RestoreAdvice('.(int) $id_advice.', '.(int) $status.')">Вернуть рекомендацию</a>';
    
    $objResponse->call("adviceRespBlock", $id_advice, $html);
    
    return $objResponse;   
}

function DeleteAdvice($id_advice) {
    $objResponse = new xajaxResponse();
    
    $id_advice = intval($id_advice);
    if (!$id_advice) {
        $objResponse->call('alert', 'Ошибка');
        return $objResponse;
    }
    
    $advice = new paid_advices();
    $info   = $advice->getAdviceById($id_advice);
    if($info['converted_id'] > 0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
        opinions::setConvertOpinion($info['converted_id'], null);
    }
    $advice->delete($id_advice);
    
    $html = 'Рекомендация удалена.';
    
    $objResponse->call("adviceRespBlockDel", $id_advice, $html);
    
    return $objResponse;   
}

function RefuseAdvice($id_advice) {
    $objResponse = new xajaxResponse();
    
    $id_advice = intval($id_advice);
    if (!$id_advice) {
        $objResponse->call('alert', 'Ошибка');
        return $objResponse;
    } 
    
    $advice = new paid_advices(); 
    $advice->refuse($id_advice); 
    
    $objResponse->script("window.location = '/users/{$_SESSION['login']}/opinions/?from=norisk&edit={$id_advice}'");
    
    return $objResponse;  
}

function RestoreAdvice($id_advice, $status) {
    $objResponse = new xajaxResponse();
    
    $id_advice = intval($id_advice);
    if (!$id_advice) {
        $objResponse->call('alert', 'Ошибка');
        return $objResponse;
    }
    
    $advice = new paid_advices(); 
    $advice->restore($id_advice, $status);
    $info   = $advice->getAdvice($id_advice);
    if($info['converted_id'] > 0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
        opinions::setConvertOpinion($info['converted_id'], true);
    }
    $objResponse->call("restoreAdvice", $id_advice);
    return $objResponse;   
}

function CalcPaidAdvice($sum, $scheme) {
    $objResponse = new xajaxResponse();
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exrates.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
    
    $exrates = new exrates();
    if(intval($sum) <= 0) {
        $objResponse->assign("sum_fm", "value", "");
        $objResponse->assign("sum_rating", "innerHTML", "");
        return $objResponse; 
    }
    $advice = new paid_advices(); 
    $EXR    = $exrates->GetAll();
    
    if($scheme == 1) {
        $sum_fm = round($sum / $EXR[13], 2);
        $FM = round(($sum * paid_advices::PAID_COMMISION), 2);
        $RT = $advice->getSBRRating($sum);
        
        $html_rt = 'и получите <strong>' . $RT . '</strong>&#160;' . ending($RT, "балл", "балла", "баллов") . ' рейтинга';
        if($sum < sbr_stages::MIN_COST_RUR_PDRD) {
            $objResponse->script("$('error_budget').show();");
        } else {
            $objResponse->script("$('error_budget').hide();");
        }
        $objResponse->assign("sum_fm", "value", $FM);
        $objResponse->assign("sum_rating", "innerHTML", $html_rt);
    } else if($scheme == 2) {
        $RUB = round($sum / paid_advices::PAID_COMMISION, 2);
        $sum_fm = round($RUB, 2);
        $RT = $advice->getSBRRating($sum);
        
        $html_rt = 'и получите <strong>' . $RT . '</strong>&#160;' . ending($RT, "балл", "балла", "баллов") . ' рейтинга';
        if($RUB < sbr_stages::MIN_COST_RUR_PDRD) {
            $objResponse->script("$('error_budget').show();");
        } else {
            $objResponse->script("$('error_budget').hide();");
        }
        $objResponse->assign("sum_rub", "value", $RUB);
        $objResponse->assign("sum_rating", "innerHTML", $html_rt);
    }
    
    return $objResponse;     
}

function getFormDeclined($id_advice, $type = 1) {
    $objResponse = new xajaxResponse();
    
    ob_start();
    define( 'IS_SITE_ADMIN', 1 );
    require_once($_SERVER['DOCUMENT_ROOT'].'/siteadmin/paid_advice/form.declined.tpl.php');
    $html = ob_get_clean();
        
    $objResponse->script("$('tr_msg_advice_{$id_advice}').setStyle('display', 'table-row');");
    $objResponse->assign("msg_advice_{$id_advice}", "innerHTML", $html);
    $objResponse->script("scrollDeclineForm($('tr_msg_advice_{$id_advice}'));");
    return $objResponse;
}

function ModDeclinedAdvice($id_advice, $msg, $type = 1) {
    $objResponse = new xajaxResponse();
    if(!(hasPermissions('users') || hasPermissions('paidadvice'))) return $objResponse;
    if (is_empty_html($msg)) {
        $objResponse->call('alert', 'Укажите причину отказа.');
        return $objResponse;
    } 
    $id_advice = intval($id_advice);
    $paid_advice = new paid_advices(); 
    $msg = stripslashes($msg);
    if($paid_advice->getAdviceStatus($id_advice) == paid_advices::STATUS_PAYED) {
        $objResponse->call('alert', 'Пользователь уже оплатил рекомендацию, вы не можете отклонить её');
        return $objResponse;
    }
    if($type == 1) {
        $paid_advice->adminDecline($id_advice, $msg);
    } else {
        $paid_advice->adminDelete($id_advice, $msg);
    }
    
    $advice['mod_msg'] = $msg;
    ob_start();
    define( 'IS_SITE_ADMIN', 1 );
    require_once($_SERVER['DOCUMENT_ROOT'].'/siteadmin/paid_advice/mod_msg.tpl.php');
    $html = ob_get_clean();
    
    $btn_del = "btn_deleted_{$id_advice}";
    $txt_del = "btn_txt_deleted_{$id_advice}";
    $btn_dec = "btn_declined_{$id_advice}";
    $txt_dec = "btn_txt_declined_{$id_advice}";
    $btn_acc = "btn_accepted_{$id_advice}";
    $txt_acc = "btn_txt_accepted_{$id_advice}";
    
    $objResponse->assign("recomend_item_{$id_advice}", "innerHTML", $html);
    $objResponse->script("$('tr_msg_advice_{$id_advice}').setStyle('display', 'none');");
    $objResponse->script("$('$btn_del').hide();
                          $('$btn_dec').hide();
                          ".($type == 1?"$('$txt_dec').show();":"$('$txt_dec').hide();")."
                          $('$btn_acc').show();
                          $('$btn_acc').set('disabled', false);  
                          $('$txt_acc').hide();
                          ".($type == 1?"$('$txt_del').hide();":"$('$txt_del').show();")."
                          $('declined_{$id_advice}').destroy();");
    
    return $objResponse;
}

function ModAcceptedAdvice($id_advice) {
    $objResponse = new xajaxResponse();
    if(!(hasPermissions('users') || hasPermissions('paidadvice'))) return $objResponse;
    $id_advice = intval($id_advice);
    $advice = new paid_advices(); 
    $advice->adminAccept($id_advice);
    
    $btn_del = "btn_deleted_{$id_advice}";
    $txt_del = "btn_txt_deleted_{$id_advice}";
    $btn_dec = "btn_declined_{$id_advice}";
    $txt_dec = "btn_txt_declined_{$id_advice}";
    $btn_acc = "btn_accepted_{$id_advice}";
    $txt_acc = "btn_txt_accepted_{$id_advice}";
    
    $objResponse->assign("recomend_item_{$id_advice}", "innerHTML", "");
    $objResponse->script("$('$btn_del').show();
                          $('$btn_dec').show();
                          $('$btn_del').set('disabled', false);
                          $('$btn_dec').set('disabled', false);
                          $('$txt_dec').hide();
                          $('$txt_del').hide();
                          $('$btn_acc').hide();
                          $('$txt_acc').show();");
    
    return $objResponse;
}


$xajax->processRequest();