<?php

//$rpath = "../";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/tservices.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_auth_smail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');


function more_feedbacks($tuid, $page, $total_cnt) 
{
    $per_page = 5;
    $objResponse = &new xajaxResponse();
    
    $tservices = new tservices();
    $feedbacks = $tservices->setPage($per_page,$page)->getFeedbacks($tuid);
    
    $sHtml = Template::render(ABS_PATH . '/tu/tpl.feedbacks-items.php', array('feedbacks' => $feedbacks));

    $local_cnt = $per_page*$page;
    $objResponse->call('ap_feedbacks.setContent',$sHtml,($local_cnt >= $total_cnt));
    
    return $objResponse;
}

function tservices_order_auth($email, $name, $surname, $options) 
{
    $objResponse = &new xajaxResponse();

    $name    = substr(strip_tags(trim(stripslashes($name))),0,21); //Для регистрации
    $surname = substr(strip_tags(trim(stripslashes($surname))),0,21); //Для регистрации
    $email = substr(strip_tags(trim(stripslashes($email))),0,64); //Для регистрации и авторизации
    
    $tu_id = intval(@$options['tu_id']);

    $tservices = new tservices();
    $tService = $tservices->getCard($tu_id);
    if(!$tService) return $objResponse;
    
    if (is_email($email)) 
    {

        //Забираем только нужные нам ключи
        $options = array_intersect_key($options, array('extra' => '','is_express' => '','paytype' => ''));
        //Проверка входных параметров
        $is_valid_extra = !isset($options['extra']) || (isset($options['extra']) && (count(array_intersect(array_keys($tService['extra']), $options['extra'])) == count($options['extra'])));
        $is_valid_express = !isset($options['is_express']) || (isset($options['is_express']) && $options['is_express'] == '1' && $tService['is_express'] == 't');
        $is_valid_paytype = isset($options['paytype']) && in_array($options['paytype'], array('0','1'));
        if(!($is_valid_extra && $is_valid_express && $is_valid_paytype)) return $objResponse;

        $tservices_auth_smail = new tservices_auth_smail();
        
        $user = new users();
        $user->GetUser($email, true, true);
        //Проверяем на всякий случай там точно мыло совпало а то может логин
        $is_email = ($user->email == $email);

        //Создаем хеш для ссылки активации
        $code = TServiceOrderModel::model()->newOrderActivation(array(
            'user_id' => ($user->uid > 0)?$user->uid:NULL,
            'tu_id' => $tService['id'],
            'uname' => !empty($name)?$name:NULL,
            'usurname' => !empty($surname)?$surname:NULL,
            'email' => $email,
            'options' => $options
        ));
        
        // Пользователь найден, ведь у него есть email. А как еще проверить?
        if (($user->uid > 0) && $is_email) 
        { 
            
            if (is_emp($user->role)) 
            {
                $tservices_auth_smail->orderByOldUser($email, $tService, $code);
                $objResponse->call('TServices_Order_Auth.showSuccess', "На указанную вами почту отправлено письмо со ссылкой-подтверждением. Пожалуйста, перейдите по ней для завершения процесса заказа услуги.");
            } 
            else 
            {
                $objResponse->call('TServices_Order_Auth.showError', 'email', 'Данный e-mail принадлежит фрилансеру');
            }
            
        } 
        else 
        {
            $tservices_auth_smail->orderByNewUser($email, $tService, $code);
            $objResponse->call('TServices_Order_Auth.showSuccess', "На указанную вами почту отправлено письмо со ссылкой-подтверждением. Пожалуйста, перейдите по ней для завершения процесса заказа услуги.");
        }
        
    }
    else 
    {
        $objResponse->call('TServices_Order_Auth.showError', 'email', 'Неверно указана почта');
    }
    
    return $objResponse;
}

$xajax->processRequest();