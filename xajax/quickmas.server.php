<?php

/**
 * Услуга массовой рассылки предложения о проекте фрилансерам
 * Оплата услуги.
 */


require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/quickmas.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/masssending.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/country.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/city.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/project_exrates.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/yandex_kassa.php");

session_start();

function quickMASSetCats($frm) {
    $objResponse = new xajaxResponse();

    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/country.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/city.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/professions.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/project_exrates.php';

    if($frm) {
        global $DB;
        $frm = preg_replace("/,$/", "", $frm);
        $acats = explode(",", $frm);
        $cats_data = array();
        foreach($acats as $v) {
            $v = preg_replace("/^mass_cat_span_/", "", $v);
            $c = explode("_", $v);
            if($c[0]==0) { continue; }
            if($c[1]==0) {
                $sql = "SELECT prof_group FROM professions WHERE id=?i";
                $p = $DB->val($sql, $c[0]);
                $cats_data[] = $p.":".$c[0];
            } else {
                $cats_data[] = $c[0].":0";
            }
        }
        $html = '';
        if($cats_data) {
            $count = 0;
            foreach($cats_data as $v) {
                $count++;
                if(count($cats_data)<=3 || (count($cats_data)>=4 && $count<3)) {
                    $c = explode(":", $v);
                    $sql = "SELECT name FROM prof_group WHERE id=?i";
                    $p = $DB->val($sql, $c[0]);
                    $html .= $p;
                    if($c[1]) {
                        $html .=" - ".professions::GetProfName($c[1])."<br>";
                    } else {
                        $html .= "<br>";
                    }
                }
            }
            if(count($cats_data)>=4 && $count>2) {
                $html .= "и еще ".($count-2)." ".ending(($count-2), 'другой', 'других', 'других');
            }
            $objResponse->script("$('quickmas_f_mas_c_count').set('html', '".count($cats_data)."');");
            $objResponse->script("$('quickmas_f_mas_subcat_m').show();");
        } else {
            $objResponse->script("$('quickmas_f_mas_subcat_m').hide();");
        }
        $objResponse->assign("quickmas_f_mas_subcat", "innerHTML", $html);
    }

    return $objResponse;
}


//------------------------------------------------------------------------------


function quickMASPayAccount($frm, $promo_code) 
{
    $objResponse = new xajaxResponse();

    if (is_emp()) {
        global $DB;

        $masssending = new masssending();
        
        //@todo: не используются?
        //$countries = country::GetCountries(TRUE);
        //$prof_groups = professions::GetAllGroupsLite(TRUE);
        //$professions = professions::GetProfList();
        //$exrates = project_exrates::GetAll();

        $uid = get_uid(false);
        
        //$params['msg'] = stripslashes($frm['msg']);
        $host = str_replace('www.n.fl.ru', 'n.fl.ru', $GLOBALS['host']);
        $params['msg'] = "Здравствуйте!\n\nПриглашаю ознакомиться с проектом '".change_q_x(stripslashes($frm['title']), FALSE, FALSE, 'b|i|p|ul|li|s|h[1-6]{1}', FALSE, FALSE)."' ".$host.$frm['link']." \n\n\n".LenghtFormatEx(change_q_x(stripslashes($frm['msg']), FALSE, FALSE, 'b|i|p|ul|li|s|h[1-6]{1}', FALSE, FALSE),300);
        $params['max_users'] = intval($frm['max_users']);
        $params['max_cost'] = intval($frm['max_cost']);

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
        if ($calc = $masssending->Add($uid, $params)) {
            $masssending->ClearTempFiles(session_id());
            
            //Допустимо использование промокодов
            $masssending->billing->setPromoCodes('SERVICE_MASSSENDING', $promo_code);
            
            $billReserveId = $masssending->Accept($calc['massid'], false);
            
            if ($billReserveId && 
                $masssending->billing->isAllowPayFromAccount()) {
                $complete = $masssending->billing->buyOrder($billReserveId);
                
                if ($complete) {
                    $_SESSION['quickmas_count_u'] = $calc['count'];
                    $_SESSION['quickmass_ok'] = 1;
                    $objResponse->script("window.location = '?quickmas_ok=1';");
                }
            }
        }
    }

    return $objResponse;
}


//------------------------------------------------------------------------------


function quickMASGetYandexKassaLink($frm, $payment, $promo_code) 
{
    $objResponse = new xajaxResponse();

    if(is_emp()) {
        global $DB, $host;        

        $masssending = new masssending();
        
        //@todo: не используюутся?
        //$countries = country::GetCountries(TRUE);
        //$prof_groups = professions::GetAllGroupsLite(TRUE);
        //$professions = professions::GetProfList();
        //$exrates = project_exrates::GetAll();

        $uid = get_uid(false);

        //$params['msg'] = stripslashes($frm['msg']);
        $params['msg'] = "Здравствуйте!\n\nПриглашаю ознакомиться с проектом '".change_q_x(stripslashes($frm['title']), FALSE, FALSE, 'b|i|p|ul|li|s|h[1-6]{1}', FALSE, FALSE)."' ".str_replace('www.n.fl.ru', 'n.fl.ru',$host).$frm['link']." \n\n\n".LenghtFormatEx(change_q_x(stripslashes($frm['msg']), FALSE, FALSE, 'b|i|p|ul|li|s|h[1-6]{1}', FALSE, FALSE),300);
        $params['max_users'] = intval($frm['max_users']);
        $params['max_cost'] = intval($frm['max_cost']);

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
        if ($calc = $masssending->Add($uid, $params)) {
            $masssending->ClearTempFiles(session_id());
            
            //Допустимо использование промокодов
            $masssending->billing->setPromoCodes('SERVICE_MASSSENDING', $promo_code);
            
            $billReserveId = $masssending->Accept($calc['massid'], false);
            
            if ($billReserveId) {
                
                $_SESSION['quickmas_is_begin'] = 1;
                $_SESSION['quickmas_count_u'] = $calc['count'];
                $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];                
                
                $sum = $masssending->billing->getRealPayedSum();
                
                $yandex_kassa = new yandex_kassa();
                $html_form = $yandex_kassa->render(
                        $sum, 
                        $masssending->billing->account->id, 
                        $payment, 
                        $billReserveId); 
                
                $html_form = preg_replace('/^[^\/]+\/\*!?/', '', $html_form);
                $html_form = preg_replace('/\*\/[^\/]+$/', '', $html_form);

                $objResponse->script('$("quick_mas_div_wait").set("html", \''.$html_form.'\');');
                $objResponse->script("$('quick_mas_div_wait').getElements('form')[0].submit();");                
            }
        }
    }

    return $objResponse;
}


//------------------------------------------------------------------------------


function quickMASCheckOrder() 
{
    $objResponse = new xajaxResponse();
    $is_ok = 0;
    if(isset($_SESSION['quickmas_is_success'])) {
        if($_SESSION['quickmas_is_success']=='y') {
            $is_ok = 1;
        }
    }
    if($is_ok==1) {
        $_SESSION['quickmass_ok'] = 1;
        $objResponse->script("window.location = '?quickmas_ok=1';");
    } else {
        $objResponse->script('$("quick_mas_div_error").removeClass("b-layout_hide");');
        $objResponse->script('$("quick_mas_div_wait").addClass("b-layout_hide");');
        $objResponse->script('$("quick_mas_div_main").removeClass("b-layout_waiting");');
    }
    unset($_SESSION['quickmas_is_success']);
    unset($_SESSION['quickmas_is_begin']);
    return $objResponse;
}

$xajax->processRequest();