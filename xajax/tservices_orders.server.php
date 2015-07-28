<?php

//$rpath = "../";

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/yii/tinyyii.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/xajax/tservices_orders.common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_smail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderFeedbackModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderStatus.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderHistory.php');
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php";
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_order_history.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/validation.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/OrderStatusIndicator.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/memBuff2.php');


session_start();
$uid = get_uid(FALSE);
$orderModel = TServiceOrderModel::model();


//------------------------------------------------------------------------------

/**
 * Удаление отзыва по заказу ТУ
 * 
 * @param type $feedback_id
 * @return \xajaxResponse
 */
function tservicesOrdersDeleteFeedback($feedback_id)
{
    $uid = get_uid(false);
    $objResponse = &new xajaxResponse();

    $feedback_id = intval($feedback_id);
    
    $orderFeedbackModel = new TServiceOrderFeedbackModel();
    $data = $orderFeedbackModel->getFeedback($feedback_id);
    if(!$data) return $objResponse;      
    
    $is_adm = hasPermissions('tservices');
    $is_owner = ($data['user_id'] == $uid);
    
    if(!$is_owner && !$is_adm) return $objResponse; 
    
    $orderFeedbackModel->attributes(array('modified_id' => $uid));
    $ret = $orderFeedbackModel->deleteFeedback($feedback_id);
    if(!$ret) return $objResponse;
    
    //Очистить кеш кол-ва новых событий
    $anti_prefix = (is_emp())?'frl':'emp';
    TServiceOrderModel::model()->clearCountEvent($data["{$anti_prefix}_id"]);
    
    //TODO: можно пересчитать кол-во и обновить на старице или обновить всю страницу
    
    $objResponse->script("$('p_stage_{$feedback_id}-2').dispose();");
    $objResponse->script("$('feedback_comment_cont_{$feedback_id}-2').dispose();");;

    $objResponse->script("window.location.reload()");
    
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * Обновление отзыва по зказу ТУ
 * 
 * @param type $params
 * @return \xajaxResponse
 */
function tservicesOrdersUpdateFeedback($params)
{
    $uid = get_uid(false);
    $objResponse = &new xajaxResponse();
    
    $feedback_id = @$params['feedback_id'];
    $feedback_id = intval($feedback_id);
    $feedback = @$params['feedback'];
    
    $orderFeedbackModel = new TServiceOrderFeedbackModel();
    $data = $orderFeedbackModel->getFeedback($feedback_id);
    if(!$data) return $objResponse;     
    
    $is_adm = hasPermissions('tservices');
    $is_owner = ($data['user_id'] == $uid);
    $is_editable = (($data['rating'] < 0) || TServiceOrderFeedbackModel::isAllowFeedback($data['posted_time']));

    if(!($is_owner && $is_editable) && !$is_adm) return $objResponse; 

    $is_valid = $orderFeedbackModel->attributes(array(
        'feedback' => $feedback,
        'modified_id' => $uid
    ));
    if(!$is_valid) return $objResponse;
    
    $ret = $orderFeedbackModel->updateFeedback($feedback_id);
    if(!$ret) return $objResponse;
    
    $data = $orderFeedbackModel->attributes();
    
    $ele_id = 'form_container_' . $feedback_id . '-2';
    $text_id = 'op_message_' . $feedback_id . '-2';
    
    $objResponse->script("$('$text_id').setStyle('display', 'block');");
    $objResponse->script("$('$ele_id').setStyle('display', 'none');");
    $objResponse->script("$$('.sbrmsgblock').setStyle('display', 'block');");
    $objResponse->assign($text_id, "innerHTML",  '<p>'.reformat($data['feedback'], 30).'</p>');
    $objResponse->assign($ele_id, "innerHTML",  '');

    return $objResponse;    
}


//------------------------------------------------------------------------------


/**
 * Редактирование отзыва по заказу ТУ
 * 
 * @param type $feedback_id
 * @return \xajaxResponse
 */
function tservicesOrdersEditFeedback($feedback_id)
{    
    $uid = get_uid(false);
    
    $objResponse = &new xajaxResponse();

    $orderFeedbackModel = new TServiceOrderFeedbackModel();
    $data = $orderFeedbackModel->getFeedback($feedback_id);
    if(!$data) return $objResponse; 
    
    $is_adm = hasPermissions('tservices');
    $is_owner = ($data['user_id'] == $uid);
    $is_editable = (($data['rating'] < 0) || TServiceOrderFeedbackModel::isAllowFeedback($data['posted_time']));
    
    if (!($is_owner && $is_editable) && !$is_adm)  {
        return $objResponse; 
    }
    
    //$data['hash'] = md5(TServiceOrderModel::SOLT . $feedback_id);
    $content = Template::render(ABS_PATH . '/tu/tpl.order-feedback-form.php',$data);
    
    $ele_id = 'form_container_'.$feedback_id.'-2';
    $objResponse->script("$$('.editFormSbr').set('html', '&nbsp;').setStyle('display', 'none');");
    $objResponse->script("$$('.sbrmsgblock').setStyle('display', 'block');");
    $objResponse->script("$('form_container_to_{$feedback_id}-2').setStyle('display', 'none');");
    $objResponse->script("$('$ele_id').setStyle('display', 'block');");
    $objResponse->assign($ele_id, "innerHTML", $content);
    
    return $objResponse;      
}


//------------------------------------------------------------------------------


/**
 * Новый отзыв по заказу ТУ
 * 
 * @global type $uid
 * @global type $orderModel
 * @param type $params
 * @return \xajaxResponse
 */
function tservicesOrdersNewFeedback($params)
{
    
    $uid = get_uid(false);
    $orderModel = TServiceOrderModel::model();
    
    $objResponse = &new xajaxResponse();
    
    $order_id = intval(@$params['oid']);
    $hash = @$params['hash'];
    $test_hash = md5(TServiceOrderModel::SOLT . $order_id);
    
    if($uid <= 0 || $hash !== $test_hash) return $objResponse;
    
    $memebuff = new memBuff();
    if ($memebuff->get('feedback_process_' . $order_id)) return $objResponse;
    $memebuff->set('feedback_process_' . $order_id, true);
    
    $is_emp = is_emp();
    $prefix = ($is_emp)?'emp':'frl';
    $sufix = ($is_emp)?'frl':'emp';
    
    $allow_status = array(
        TServiceOrderModel::STATUS_ACCEPT,
        TServiceOrderModel::STATUS_FIX,
        TServiceOrderModel::STATUS_EMPCLOSE,
        TServiceOrderModel::STATUS_FRLCLOSE
    );
    
    $feedback = @$params['feedback'];
    $is_feedback = !empty($feedback);
    $fbtype = @$params['fbtype'];
    
    $orderData = $orderModel->getCard($order_id, $uid);    
    //Если не существует или статус не подходящий
    if(!$orderData || !in_array($orderData['status'], $allow_status)) {
        $memebuff->delete('feedback_process_' . $order_id);
        return $objResponse;
    }
    //Если есть отзыв и он не удален
    if(!empty($orderData[$prefix . '_feedback'])) {
        $memebuff->delete('feedback_process_' . $order_id);
        return $objResponse;
    }
    
    $order_id = $orderData['id'];
    $status = $orderData['status'];  
    
    if ($orderModel->isDisallowFeedback()) {
        $memebuff->delete('feedback_process_' . $order_id);
        return $objResponse;
    }
    
    //Меняем статус при необходимости и тем самым закрываем заказ
    if($status != TServiceOrderModel::STATUS_EMPCLOSE)
    {
        try
        {
            $new_status = $orderModel->changeStatus($order_id, 'close', $is_emp, $fbtype);
        }
        catch(Exception $e)
        {
            $sHtml = tservices_helper::getMessage($e->getMessage(),'error');
            $objResponse->call('TServices_Order.showBeforeStatus', $order_id, $sHtml);
            $memebuff->delete('feedback_process_' . $order_id);
            return $objResponse;
        }
        
        $orderData['status'] = $new_status;
    }
    

    //Сохраняем отзыв если он есть
    if($is_feedback)
    {
        $orderFeedbackModel = new TServiceOrderFeedbackModel();
        $is_valid = $orderFeedbackModel->attributes(array(
            'feedback' => $feedback,
            'rating' => $fbtype,
            'is_emp' => $is_emp,
            'user_id' => $uid
        ));
        
        //Тут обрабатывать ошибки при валидации
        if(!$is_valid || !$orderFeedbackModel->addFeedback($order_id)) {
            $memebuff->delete('feedback_process_' . $order_id);
            return $objResponse;
        }
        
        $attributes = $orderFeedbackModel->attributes();   
        $orderData[$prefix . '_feedback'] = $attributes['feedback'];
        $orderData[$prefix . '_rating'] = $attributes['rating'];
        
        //Сохранить действие в историю
        $history = new tservices_order_history($order_id);
        $history->saveFeedback($is_emp, $fbtype);
        
        //Чистим кеш кол-во новых сообщений юзера после написания комментария 
        $orderModel->clearCountEvent($orderData["{$sufix}_id"]);
        
        /*
        if ($status == TServiceOrderModel::STATUS_EMPCLOSE && $is_emp && $fbtype < 0) 
        {
            $orderModel->cancelTax($order_id);
        }
         */
    }    
    

        
    //Уведомление на почту 
    $tservices_smail = new tservices_smail();
    $tservices_smail->attributes(array('order' => $orderData, 'is_emp' => $is_emp));
    $tservices_smail->closeOrderAndFeedback($status);

    $tserviceOrderStatusWidget = new TServiceOrderStatus();
    $tserviceOrderStatusWidget->setIsEmp($is_emp); 
    $tserviceOrderStatusWidget->setOrder($orderData);
    $tserviceOrderStatusWidget->init();
    
    
    ob_start();
    $tserviceOrderStatusWidget->run();
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    $objResponse->assign('tservices_order_status_'.$order_id,'innerHTML',$sHtml);
    
    //если фрилансер закрывает заказ или оставляет отзыв
    //то обновляем ему информацию о состоянии счета
    if(!$is_emp)
    {
        //не показываем отрицательную сумму
        //$balance = ($_SESSION['ac_sum'] > 0)?number_format(round(zin($_SESSION['ac_sum']),2), 2, ",", " ").' руб.':'Мои услуги';
        //$balance = '<span class="b-bar__icon b-bar__icon_fm"></span>' . $balance;
        
        //для новой шапки
        $balance = number_format(round(zin($_SESSION['ac_sum']),2), 2, ",", " ").' Р';
        $objResponse->script("$$('.b-user-menu-wallet-clause a').set('html', '".$balance."');");
    }
    
    //Обновляем события так как у mootools нет Live 
    //а Delegation не работает.
    $objResponse->script('
        Bar_Ext.popuper();
        window.order_feedback_factory = new OrderFeedbackFactory();
    ');
    
    $memebuff->delete('feedback_process_' . $order_id);
    return $objResponse;
}



//------------------------------------------------------------------------------


/**
 * Новое сообщение в заказе ТУ
 * 
 * @global type $orderModel
 * @param type $order_id
 * @param type $message
 * @param type $sess
 * @return \xajaxResponse
 */
function tservicesOrdersNewMessage($order_id, $message, $sess)
{
    global $orderModel;
    
    $uid = get_uid(false);
    
    $objResponse = &new xajaxResponse();
   
    $orderModel->attributes(array('is_adm' => hasPermissions('tservices')));
    
    $orderData = $orderModel->getCard($order_id, $uid);
    
    //Если не существует или статус не подходящий
    if(!$orderData) return $objResponse;
    
    $author_id = $uid;
    $reciever_id = is_emp() ? $orderData['frl_id'] : $orderData['emp_id'];
    //@todo: Зачем делаем reformat? это только функция ворматирования!
    //@todo: где валидация на кол-во симолов?
    //@todo: "reformat(htmlspecialchars(strip_tags(" это для кучи? 
    //если есть теги то нужно показвать их а не вырезать
    $text = reformat(htmlspecialchars(strip_tags($message)), 30);
    
    $modelMsg = TServiceMsgModel::model();
    $mes_id = $modelMsg->add($order_id, $uid, $reciever_id, $text);
    if(!$mes_id) return $objResponse;
    
    TServiceOrderModel::touchOrder($order_id, is_emp());
    
    $modelMsg->addAttached($sess, $mes_id);
    $message = $modelMsg->getCard($mes_id);
    if(!$message) return $objResponse;
    
    $attachedfiles = new attachedfiles();
    $sess = $attachedfiles->getSession();
    $objResponse->script("TServices_Order_Messages.updateAttachSession('{$sess}');");
    
    $sHtml = Template::render(ABS_PATH . '/tu/tpl.order-msg.php', array(
        'messages' => array($message), 
        'order' => $orderData
    ));
    
    $mail = new pmail;
    
    if($uid != $orderData['frl_id']) {
        $mail->NewTserviceMessage($uid, $orderData['frl_id'], $orderData, stripslashes($text));
    }
    
    if($uid != $orderData['emp_id']) {
        $mail->NewTserviceMessage($uid, $orderData['emp_id'], $orderData, stripslashes($text));
    }
        
    
    $objResponse->script("$('messages').getElements('div.b-layout').removeClass('b-layout_margbot_10')"
            . ".removeClass('b-fon')"
            . ".removeClass('b-fon_bg_e4faeb')"
            . ".removeClass('b-fon_pad_10')"
            . ".addClass('b-layout_margbot_20');");
    $objResponse->prepend('messages', 'innerHTML', $sHtml);
    
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * 
 * Получить последнии сообщения по заказу ТУ
 * 
 * @global type $orderModel
 * @param type $order_id
 * @return \xajaxResponse
 */
function tservicesOrdersCheckMessages($order_id)
{
    global $orderModel;
    
    $uid = get_uid(false);
    
    $objResponse = &new xajaxResponse();
   
    $orderData = $orderModel->getCard($order_id, $uid);
    if(!$orderData) return $objResponse;
    
    $modelMsg = TServiceMsgModel::model();
    $messages = $modelMsg->getListNew($order_id);
    
    if (count($messages)) {
        $modelMsg->markAsRead($order_id, $uid);
        
        $sHtml = Template::render(ABS_PATH . '/tu/tpl.order-msg.php', array(
            'messages' => $messages, 
            'order' => $orderData
        ));
        
        $objResponse->script("$('messages').getElements('div.b-layout').removeClass('b-layout_margbot_10')"
            . ".removeClass('b-fon')"
            . ".removeClass('b-fon_bg_e4faeb')"
            . ".removeClass('b-fon_pad_10')"
            . ".addClass('b-layout_margbot_20');");
        $objResponse->prepend('messages', 'innerHTML', $sHtml);
    }
    
    $objResponse->script("TServices_Order_Messages.checkMessages.delay(40000);");
    
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * Редактирование стоимости и сроков заказа ТУ
 * 
 * @param type $order_id
 * @param type $price
 * @param type $days
 * 
 * @return \xajaxResponse
 */
function tservicesOrdersSetPrice($order_id, $price, $days, $paytype) 
{
    $objResponse = &new xajaxResponse();
    
    $uid = get_uid(false);
    
    $price = intval($price);
    $days = intval($days);
    $paytype = intval($paytype);
    
    //Валидация входных параметров
    $validator = new validation();
    $valid = $validator->is_natural_no_zero($price) && $validator->greater_than_equal_to($price, 300);
    $valid = $valid && $validator->is_natural_no_zero($days) && $validator->numeric_interval($days, 1, 730);
    $valid = $valid && in_array($valid, array(TServiceOrderModel::PAYTYPE_DEFAULT, TServiceOrderModel::PAYTYPE_RESERVE));
    if(!$valid) return $objResponse;

    //Получение заказа
    $orderModel = TServiceOrderModel::model();
    $order_id = intval($order_id);
    $old_order = $orderModel->getCard($order_id, $uid);
    if(!$old_order) return $objResponse; 
    
    //Валидация возможности изменений
    $is_new_status = $old_order['status'] == TServiceOrderModel::STATUS_NEW;
    $is_owner = $old_order['emp_id'] == $uid;
    $is_reserve_accepted = isset($old_order['reserve_data']);
    if(!($is_new_status && $is_owner && !$is_reserve_accepted)) return $objResponse;
    
    //Проверка возможности смены типа оплаты
    $is_reserve = tservices_helper::isOrderReserve($paytype);
    if($is_reserve && !tservices_helper::isAllowOrderReserve($old_order['category_id'])) return $objResponse;
    if(!$is_reserve) $paytype = TServiceOrderModel::PAYTYPE_DEFAULT;
    
    $data = array(
        'order_price' => $price,
        'order_days' => $days,
        'pay_type' => $paytype
    );

    //Меняем
    if ($orderModel->edit($order_id, $data, $old_order['tax'])) 
    {
        $order = $old_order;
        $order['order_price'] = $price;
        $order['order_days'] = $days;
        $order['pay_type'] = $paytype;

        //Сохранить действие в историю
        $history = new tservices_order_history($order_id);
        $history->save($order, $old_order);

        //Уведомление на почту 
        $tservices_smail = new tservices_smail();
        $tservices_smail->changeOrder2($order, $old_order);
        
        
        //Обновляем интерфейс цен и сроков
        $objResponse->script("$('tu-container-price').set('html', '" . tservices_helper::cost_format($price) . "');");
        $objResponse->script("$('tu-container-days').set('html', '" . tservices_helper::days_format($days) . "');");
        $objResponse->script("$('tu_edit_budjet_price').set('value', '" . $price . "');");
        $objResponse->script("$('tu_edit_budjet_days').set('value', '" . $days . "');");
        
        
        //Обновляем сообщение статуса, т.к. вторая сторона тоже могла его изменить
        $tserviceOrderStatusWidget = new TServiceOrderStatus();
        $tserviceOrderStatusWidget->setIsEmp(true); 
        $tserviceOrderStatusWidget->setOrder($order);
        $tserviceOrderStatusWidget->init();

        ob_start();
        $tserviceOrderStatusWidget->run();
        $statusHtml = ob_get_contents();
        ob_end_clean();

        $objResponse->assign('tservices_order_status_'.$order_id,'innerHTML',$statusHtml);            
        $objResponse->script("
            $('tu-container-price').getParent()
            .removeClass('b-layout__link_bordbot_dot_".($is_reserve?'000':'ee1d16')."')
            .addClass('b-layout__link_bordbot_dot_".($is_reserve?'ee1d16':'000')."');
            $('tu-container-price').getPrevious('span').set('html','".($is_reserve?'Бюджет:':'Стоимость:')."');    
        ");
    }

    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * Запрос на обновление истории заказа
 * 
 * @param type $order_id
 * @return \xajaxResponse
 */
function getOrderHistory($order_id)
{
    $objResponse = &new xajaxResponse();
    
    $uid = get_uid(false);
    $order_id = intval($order_id);
    
    $orderModel = TServiceOrderModel::model();
    $orderModel->attributes(array('is_adm' => hasPermissions('tservices')));
    $order = $orderModel->getCard($order_id, $uid);
    
    if (!$order) {
        return $objResponse;
    }
    
    $tserviceOrderHistoryWidget = new TServiceOrderHistory();
    $tserviceOrderHistoryWidget->setOrderId($order_id);
    $tserviceOrderHistoryWidget->init();

    ob_start();
    $tserviceOrderHistoryWidget->run();
    $historyHtml = ob_get_contents();
    ob_end_clean();
    $objResponse->assign('history','innerHTML',$historyHtml);
    
    //Заодно обновляем индикатор статуса  
    $orderStatusIndicator = new OrderStatusIndicator();
    $orderStatusIndicator->order = $order;
    $html = $orderStatusIndicator->getAjaxRender();
    $objResponse->assign('order_status_indicator','innerHTML',$html);
    
    return $objResponse;
}


//------------------------------------------------------------------------------




$xajax->processRequest();