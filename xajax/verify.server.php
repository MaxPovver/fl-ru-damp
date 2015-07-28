<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/verify.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/verify.php");

function addSubscribe() {
    $objResponse = new xajaxResponse();
    
    if(!get_uid(false)) {
        return $objResponse;
    }
    
    if(!verify::isSubscribeUser()) {
        verify::addSubscribeUser();
        
        $count = verify::getCountSubscribe();
        $objResponse->assign('count_subscribe', 'innerHTML', verify::converNumbersTemplate($count));
        $objResponse->assign('count_subscribe_text', 'innerHTML', ending($count, 'пользователь', 'пользователя', 'пользователей'));
        $objResponse->script("$('button_send').dispose(); $('send_success').removeClass('b-fon_hide');");
    } else {
        return $objResponse;
    }
    
    return $objResponse;
}

$xajax->processRequest();