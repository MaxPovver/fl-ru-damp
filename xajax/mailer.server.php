<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/mailer.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mailer.php");

function setStatusSending($id, $status = 1) {
    global $session;
    
    $objResponse = new xajaxResponse();
    if (!(hasPermissions('adm') && hasPermissions('mailer'))) {
        return $objResponse;
        exit;
    }
    if($status == 1) {
        mailer::update(array('status_sending' => 3), $id);
        $objResponse->assign("status_sending", "value", 3);
    } else if($status == 3) {
        mailer::update(array('status_sending' => 1), $id);
        $objResponse->assign("status_sending", "value", 1);
    }
    
	return $objResponse;
}

function recalcRecipients($post) {
    $objResponse = new xajaxResponse();
    if ( trim($post) ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mailer.php");
        
        if ( get_magic_quotes_runtime() || get_magic_quotes_gpc() ) {
            $post = stripslashes( $post );
        }
        
        $post = iconv( 'CP1251', 'UTF-8', $post );
        $_post = json_decode( $post, true );
        foreach($_post as $k=>$v) {
            if($v['name'] == 'attachedfiles_session') continue;
            $result[$v['name']] = iconv( 'UTF-8', 'CP1251', $v['value']);
        } 
        
        $url = http_build_query($result);
        parse_str($url, $output);
        
        $mailer = new mailer();
        $filter = $mailer->loadPOST($output);
        $cnt = $mailer->getCountRecipients(array("frl", "emp"), $filter);

        if($filter['filter_emp'] > 0 && $filter['filter_frl'] > 0) {
            $sum = array_sum($cnt);
        } elseif($filter['filter_emp'] > 0) {
            $sum = $cnt[0];
        } elseif($filter['filter_frl'] > 0) {
            $sum = $cnt[1];
        } else {
            $sum = array_sum($cnt);
        }
        
        $sum = $mailer->calcSumRecipientsCount($filter, $cnt);
        
        $text = number_format($sum, 0, ",", " "). " ".ending($sum, "человек", "человека", "человек");
        $objResponse->assign("all_recipients_count", "innerHTML", $text);
        $objResponse->assign("emp_recipients_count", "innerHTML", number_format($cnt[0], 0, ",", " "));
        $objResponse->assign("frl_recipients_count", "innerHTML", number_format($cnt[1], 0, ",", " "));
        
    }
    return $objResponse;
}

function setAutoComplete($block, $check = false) {
    $objResponse = new xajaxResponse();
    if (!(hasPermissions('adm') && hasPermissions('mailer'))) {
        return $objResponse;
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Digest/DigestFactory.php");
    
    if(!in_array($block, DigestFactory::$types)) {
        return $objResponse;
    }
    
    $digestBlock = new $block();
    $digestBlock->setCheck($check);
    if(!$digestBlock->isAutoComplete()) {
        return $objResponse;
    }
    
    $auto = $digestBlock->setFieldAutoComplete();
    
    if($auto) {
        ob_start();
        $digestBlock->displayBlock();
        $html = ob_get_clean();
        
        $objResponse->assign($block . $digestBlock->getNum(), 'innerHTML', $html);
        $objResponse->call('setInitPosition');
        $objResponse->call('initNaviButton', $block . $digestBlock->getNum());
        $objResponse->call('initCheckSelect', $block . $digestBlock->getNum());
    } else {
        $objResponse->call('alert', 'Нет данных для автозаполнения');
        return $objResponse;
    }
    
    return $objResponse;
}

$xajax->processRequest();
?>
