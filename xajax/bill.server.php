<?php


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");


/**
 * Удаление файла счета пользователя
 * 
 * @param type $invoice_id
 * @return \xajaxResponse
 */
function removeBillInvoice($invoice_id)
{
    $objResponse = &new xajaxResponse();
    
    $uid = get_uid(false);
    if ($uid <= 0) {
        return $objResponse;
    }

    require_once(ABS_PATH . '/bill/models/BillInvoicesModel.php');
    $billInvoicesModel = new BillInvoicesModel();
    $file_id = $billInvoicesModel->getInvoiceFileId($invoice_id, $uid);
    
    if($file_id) {
        $file = new CFile();
        $file->Delete($file_id);
        
        $objResponse->script("
            $('bill_invoice_remove').addClass('b-layout_hide').empty();
            $('bill_invoice_create').removeClass('b-layout_hide');
        ");
    }
    
    return $objResponse;
}


function ShowBillComms($bill_id, $uid = 0, $mode = 1){
	global $session;
	session_start();
    $objResponse = new xajaxResponse();
    
    if ($uid && !hasPermissions('payments')) {
        return $objResponse;
    } elseif (!$uid) {
        $uid = $_SESSION['uid'];
    }
	
    $account = new account();
    if ($account->checkOperationOwner((int)$bill_id, (int)$uid)) {
        $info = $account->GetHistoryInfo($bill_id, $uid, $mode);
    }
	
	if (isset($info) && $info){
		$objResponse->assign("bil".$bill_id,"innerHTML", ($mode==2 ? '<br/>' : '' ).$info);
	}
	return $objResponse;
}

function ShowBillText($bill_id){
	global $session, $DB;
	session_start();
    $uid = get_uid(false);
	$objResponse = new xajaxResponse();
        $info = false;
		if ($bill_id) {
                $row = $DB->row("
					SELECT ao.*, b.op_name 
					FROM account_operations ao 
					INNER JOIN op_codes b ON (ao.op_code = b.id) 
                    INNER JOIN account a ON a.id = ao.billing_id 
					WHERE ao.id = ?i AND a.uid = ?i", $bill_id, $uid);
                if($row) {
        	        if(!empty($row['op_name'])){
                            $info = account::GetHistoryText($row);
                        }
        	    }
		}

	if ($info){
	    $info = str_replace( '%username%', $_SESSION['login'], $info );
		$objResponse->assign("bil".$bill_id,"innerHTML", ($br?"<br>":'').$info);
	}
	return $objResponse;
}

/*function CheckUser($login) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/opinions.php';
    $login = stripslashes(trim(change_q_x($login, true)));
    $user = new users();
    $user->GetUser($login);
    
    $res      = get_object_vars($user);
    if((int)$res['uid'] <= 0) {
	   $objResponse->script("billing.tipView({id:'login'}, 'Нет такого пользователя');");
       return $objResponse;
	}
    
    if((int)$res['uid'] == $_SESSION['uid']) {
       $objResponse->script("billing.tipView({id:'login'}, 'Вы не можете перевести деньги самому себе');");
       return $objResponse;
    }
    $sbr_info = sbr_meta::getUserInfo($res['uid']);
    $ocnt     = opinions::GetCounts($res['uid'], array('norisk', 'emp', 'all'));
    
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'].'/engine/templates/user_info.tpl');
    $html = ob_get_clean();
    
    $objResponse->assign("get_user_info", "innerHTML", $html);
    
	return $objResponse;
}
 */

/*
function CheckUserType($login, $alert=false){
        $objResponse = new xajaxResponse();
        global $session, $DB;
        
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        
	$check_user = new users();
	$role = $check_user->GetRole($login, $error);
    
	
	// если нет role то и пользователя нет
	if($role == "") {
	   $objResponse->script("billing.tipView({id:'login'}, 'Нет такого пользователя');"); 

	   return $objResponse;   
	}
	
	if (substr($role, 0, 1)  != '0')	$user_type = "emp";
	else					$user_type = "frl";
   
	$objResponse->assign("usertype", "value", $user_type);
	
	if($user_type == 'emp' && !$alert) {
		$objResponse->script('$("pay").set("html", 10); ');
	} else if(!$alert) {
		$objResponse->script('$("pay").set("html", 19); ');
	}
	
	if($alert) {
	    if($user_type == 'emp') {
	       $objResponse->script("billing.tipView({id:'login'}, 'Пользователь не является фрилансером');");
	    }
	}
        $objResponse->script('monthCheck($("month"));');
	return $objResponse;
}
*/

/*
function changeCalendarMonth($month, $year) {
	global $session;
	session_start();
	if (!$uid) $uid = $_SESSION['uid'];
	
	$account = new account();

	if($month == date('m') && $year == date('Y')) {
		$day = date('d');
	} else {
		$day = -1;
	}
	
	$month_name = array(1=>"Январь", 2=>"Февраль", 3=>"Март", 4=>"Апрель", 5=>"Май", 6=>"Июнь", 7=>"Июль", 8=>"Август", 9=>"Сентябрь", 10=>"Октябрь", 11=>"Ноябрь", 12=>"Декабрь");
	$name_page = 'bill';
	$monthDay = date('t', mktime(0,0,0,$month, 1, $year));
	$xajax = true;
	
	$month = $month<10?"0".(int)$month:$month;
	
	$calendar = $account->getDateBillOperation($month, (int)$uid, false, $year);
	
	ob_start();
	include_once($_SERVER['DOCUMENT_ROOT']."/engine/templates/bill/bill_history_calendar.tpl");
	$data = ob_get_contents();
	ob_get_clean();
	
	$objResponse = new xajaxResponse();
    //$objResponse->script("alert($year.$month);");  
	$objResponse->assign("calendar_content", "innerHTML", $data);
	
	return $objResponse;
}
*/

/*
function BlockOperation($opid){
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    $acc = new account();
    $acc->Blocked((int)$_SESSION['uid'], $opid);
    $objResponse->assign("lock_$opid", "innerHTML", 'Разблокировать');
    $objResponse->script("$('lock_$opid').setAttribute('onclick','xajax_UnBlockOperation($opid); return false;');");
    return $objResponse;
}
*/

/*
function UnBlockOperation($opid){
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    $acc = new account();
    $acc->unBlocked((int)$_SESSION['uid'], $opid);
    $objResponse->assign("lock_$opid", "innerHTML", 'Заблокировать');
    $objResponse->script("$('lock_$opid').setAttribute('onclick','xajax_BlockOperation($opid); return false;');");
    return $objResponse;
}
*/

/*
function PreparePaymentOD($order_id, $amount) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    
    $order_id = intval($order_id);
    $amount = floatval($amount);
    $uid = get_uid(0);
    if (!$uid || !$order_id || !$amount) {
        return $objResponse;
    }
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi_cards.php");
    $account = new onlinedengi_cards();
    $url = $account->getRedirectUrl($order_id, $amount);
    
    if (!$url) {
        $objResponse->call("checkFieldsCallback", null, 'Ошибка обработки запроса.');
        return $objResponse;
    }
    
    $objResponse->call("checkFieldsCallback", $url);
    
    return $objResponse;
}
*/

/*
function addService($service) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }
    
    $billing = new billing($uid);
    $billing->clearOrders();
    if( $billing->create($service['opcode'], $service['auto'])) {
        $objResponse->call('forwardList');
    } else {
        $objResponse->call('alert', 'Возникла ошибка при добавлении услуги.');
    }
    
    return $objResponse;
}
*/

/*
function updateOrder($service) {
    global $DB;
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    if(!$uid || !$service['order_id']) {
        return $objResponse;
    }
    $billing = new billing($uid);
    $info    = $billing->findOrders($DB->parse("AND id = ?i", $service['order_id']));
    $info    = !empty($info) ? current($info) : null;
    $options = $billing->prepareOperationCode($service['opcode']);
    // @todo плохая логика вынести отсюда, внутрь класса
    if($info['option'] == 'top' && in_array($info['op_code'], array(53, 86)) ) {
        $options['ammount']  = is_pro() ? 750 : 1500;
        $options['comment'] .= 'закрепление наверху на '.$service['count'].' '.  getTermination($service['count'], array(0 => 'день', 1 => 'дня', 2=> 'дней'));
        if ($billing->pro_exists_in_list_service) {
            $options['pro_ammount']  = 750;
        }
    }
    if(!empty($options)) {
        if($service['count'] <= 0) $service['count'] = 1;
        $options['op_code']  = $service['opcode'];
        $options['op_count'] = $service['count'];
        $options['ammount']  = $options['ammount']*$service['count'];
        $options['pro_ammount']  = intval($options['pro_ammount'])*$service['count'];
        $billing->update($service['order_id'], $options, true);
    }
    return $objResponse;
}
*/

/*
function clearOrdersServices() {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }
    
    $billing = new billing($uid);
    $billing->clearOrders();
    
    $objResponse->call("forwardMain");
    
    return $objResponse;
}
*/

/*
function removeOrder($service) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    if(!$uid || !$service['order_id']) {
        return $objResponse;
    }
    
    $billing = new billing($uid);
    $billing->remove($service['order_id'], true);
    $billing->getCountListServices();
    
    if($billing->count <= 0) {
        $objResponse->call('forwardMain');
    }

    if( !empty($billing->eventRemove) ) {
        foreach($billing->eventRemove['ids'] as $id) {
            $objResponse->script("$$('div[data-name={$billing->eventRemove['service']}_{$id}]').dispose();");
        }
    }
//    if ( in_array($service["opcode"], billing::$pro_op_codes) && is_emp()) {//#0024779 - пересчитываем сумму заказа, когда пользователь удаляет pro
//        $billing->getOrders();
//        $a = $billing->list_service;
//        if ( !is_array($a) ) {
//            $a = array();
//        }
//        $objResponse->script("recalcTotalWithotPro([" . join(",", array_keys($a)) . "]); orders.calcServiceSum(); ");
//    }
    return $objResponse;
}
*/

/*
function updateAutoProlong($service) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    if(!$uid || !$service['order_id']) {
        return $objResponse;
    }
    $billing = new billing($uid);

    // Если нет активного кошелька и мы включаем автопродление показываем попап с привязкой
    // при этом само автопродление включается
//    if( !WalletTypes::isWalletActive($uid)  ) {
//        if($service['auto'] > 0) {
//            $objResponse->call("toggleWalletPopup");
//        }
//        $objResponse->script("$('select_wallet{$service['order_id']}').toggleClass('b-layout__txt_hide')");
//    } else {
//        if($service['auto'] > 0) {
//            $objResponse->script("$('wallet_info{$service['order_id']}').removeClass('b-layout__txt_hide')");
//        } else {
//            $objResponse->script("$('wallet_info{$service['order_id']}').addClass('b-layout__txt_hide')");
//        }
//    }

    $options = $billing->prepareOperationCode($service['opcode']);
    if(!empty($options)) {
        if($options['service'] == 'pro') {
            $billing->setUpdateAuto($options['service'], ( $service['auto'] > 0 ? true : false ) );
        } else {
            $billing->setUpdateAuto($options['service'], ( $service['auto'] > 0 ? true : false ), $service['order_id']);
        }
    }
    
    return $objResponse;
}
*/

/*
function updateProAuto($type) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
    $objResponse = new xajaxResponse();

    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }
    $billing = new billing($uid);

    // Изменяем авто продление PRO, если нужно
    if ($type == 'on') {
        if( !WalletTypes::isWalletActive($uid)  ) {
            $objResponse->call("toggleWalletPopup");
            $objResponse->script("$('wallet_info_pro').addClass('b-layout__txt_hide')");
            $objResponse->script("$('select_wallet_pro').removeClass('b-layout__txt_hide')");
        } else {
            $objResponse->script("$('wallet_info_pro').removeClass('b-layout__txt_hide')");
            $objResponse->script("$('select_wallet_pro').addClass('b-layout__txt_hide')");
        }
        $billing->obj_user->setPROAutoProlong('on', $uid);
    }
    if ($type == 'off') {
        $objResponse->script("$('wallet_info_pro').addClass('b-layout__txt_hide')");
        $objResponse->script("$('select_wallet_pro').addClass('b-layout__txt_hide')");
        $billing->obj_user->setPROAutoProlong('off', $uid);
    }

    return $objResponse;
}
*/

/*
function preparePaymentServices($method) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }
    $method = __paramValue('string', $method);
    $bill = new billing($uid);
    $bill->not_init_pm = true;
    $bill->setPaymentMethod($method);
    
    if(in_array($bill->type_payment, array('megafon_mobile', 'beeline_mobile', 'mts_mobile', 'matrix_mobile', 'qiwipurse', 'sber', 'bank')) || $bill->type_menu_block == 'terminal') {
        $objResponse->script("$('{$method}').submit();");
        return $objResponse;
    }
    $ok  = $bill->preparePayments($bill->getTotalAmmountOrders());
    
    if($ok) {
        $objResponse->script("$('{$method}').submit();");
    }
    
    return $objResponse;
}
*/

/*
function cancelReservedOrders($id) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }
    
    $bill = new billing($uid);
    
    if($bill->checkStatusReserve($id) != billing::RESERVE_STATUS) {
        $objResponse->call('alert', 'Данный список услуг уже обработан.');
    } else {
        $success = $bill->setReserveStatus($id, billing::RESERVE_CANCEL_STATUS);
        
        if($success) {
            $bill->updateOrderListStatus($id, billing::STATUS_NEW);
            $objResponse->call("forwardList");
        } 
    }
    
    return $objResponse;
}
*/

function ShowReserveOrders($id) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }
    
    $bill = new billing($uid);
    $info = $bill->getOrderInfo($id, 'comment');
    $info = array_map(create_function('$array', 'return $array["comment"];' ), $info);
    
    if ($info){
        $info = implode(", ", $info);
        $info .= ' &#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_80 b-layout__link_inline-block b-layout__link_lineheight_1" onclick="xajax_ShowReserveText('.$id.');" href="javascript:void(0);">Скрыть</a>';
		$objResponse->assign("res".$id,"innerHTML", $info);
	}
    
    return $objResponse;
}
function ShowReserveText($reserve_id){
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    global $session, $DB;
    session_start();
    $uid = get_uid(false);
    $objResponse = new xajaxResponse();
    $info = false;
    if ($reserve_id) {
        $sql = "
            WITH bill_queue_ammount AS (
                SELECT SUM(CASE WHEN pro_ammount > 0 THEN pro_ammount ELSE ammount END) as ammount, reserve_id FROM bill_queue
                WHERE reserve_id = ?i
                GROUP BY reserve_id
            )
            SELECT 'Список заказов №'|| id ||' на сумму ' || round(bq.ammount, 2) || ' руб' as op_name, id, " . billing::RESERVE_OP_CODE . " as op_code
            FROM bill_reserve
            INNER JOIN bill_queue_ammount as bq ON bq.reserve_id = bill_reserve.id
            WHERE id = ?i AND uid = ?i";
        $row = $DB->row($sql, $reserve_id, $reserve_id, $uid);
        if ($row && !empty($row['op_name'])) {
            $info = account::GetHistoryText($row);
        }
    }

    if ($info){
        $objResponse->assign("res" . $reserve_id, "innerHTML", $info);
    }
    return $objResponse;
}

/*
function walletActivate($type, $path = null) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
    $objResponse = new xajaxResponse();

    $uid = get_uid(false);
    if(!$uid || !WalletTypes::isValidType($type)) {
        return $objResponse;
    }

    $wallet = WalletTypes::initWalletByType($uid, $type);
    if($wallet->getAccessToken() != '') { // Значит можно просто активировать услугу, но сначало нужно проверить работает ли токен
        // Токен рабочий активируем иначе посылаем пользователя активироваться
        if($wallet->api->checkToken()) {
            if( $wallet->setActiveWallet($type, $uid) ) {

                if(name_page($path) == 'bill') {
                    $objResponse->call("toggleWalletPopup");
                    $objResponse->script("window.location.reload()");
                } else{
                    $objResponse->call("toggleWalletPopup");
                    ob_start();
                    $service['auto'] = 't';
                    include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.info_wallet.php");
                    $content = str_replace(array("\r", "\n"), "", ob_get_clean());

                    ob_start();
                    include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_wallet.php");
                    $right = str_replace(array("\r", "\n"), "", ob_get_clean());

                    $objResponse->script("$$('.walletSelect').addClass('b-layout__txt_hide');");
                    $objResponse->script("$$('.walletInfo').set('html', '{$content}');");
                    $objResponse->script("$$('.walletRightBlock').set('html', '{$right}');");
                }
            }

            return $objResponse;
        }
    }

    $auth_uri = $wallet->authorize();
    if($wallet->api->error) {
        $_SESSION['errorCards'] = array('ErrorMessage' => $auth_uri['errorMessage']);
        $objResponse->script("window.location = '/bill/fail_card/';");
        return $objResponse;
    }
    $parse    = parse_url($auth_uri);
    parse_str($parse['query'], $query);

    $objResponse->script("$('walletForm').set('html', '');");

    foreach($query as $name=>$value) {
        $objResponse->script("
            var inp = new Element('input', {'type':'hidden', 'name': '{$name}', 'value' :'{$value}'});
            $('walletForm').grab(inp);
        ");
    }

    // Только методом GET
    if($type == WalletTypes::WALLET_ALPHA) {
        $objResponse->script("$('walletForm').set('method', 'GET')");
    }

    //$objResponse->call("alert", $auth_uri);
    $objResponse->script("
        $('walletForm').set('action', '{$auth_uri}');
        $('walletForm').submit();
    ");


    return $objResponse;
}
*/

/*
function walletRevoke($type) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
    $objResponse = new xajaxResponse();

    $uid = get_uid(false);
    if(!$uid || !WalletTypes::isValidType($type)) {
        return $objResponse;
    }

    $wallet = WalletTypes::initWalletByType($uid, $type);
    if($wallet) {
        $wallet->data['access_token'] = null;
        $wallet->saveWallet();
        $objResponse->script("$$('.walletSelect').removeClass('b-layout__txt_hide');");
        $objResponse->script("$$('.walletInfo').addClass('b-layout__txt_hide');");
        $objResponse->script("$('walletInfo{$type}').dispose();");
        $objResponse->script("$('wallet{$type}').set('checked', false)");

        $objResponse->call("toggleWalletPopup");

        ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_wallet.php");
        $right = str_replace(array("\r", "\n"), "", ob_get_clean());

        $objResponse->script("$$('.walletRightBlock').set('html', '{$right}');");

    } else {
        $objResponse->call("alert", "Ошибка удаления кошелька");
    }

    return $objResponse;
}
*/

$xajax->processRequest();