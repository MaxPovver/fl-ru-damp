<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/promo.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_meta.php");

/**
 * возвращает отзывы сервису в промоблок Безопасной Сделки
 */
function getPromoFeedbacks()
{
    $objResponse = new xajaxResponse();
    
    $feedbacksFromFrl = sbr_meta::getServiceFeedbacksFromFrl();
    $feedbacksFromEmp = sbr_meta::getServiceFeedbacksFromEmp();
    
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'] . '/promo/sbr/new/tpl.feedbacks.php');
    $html = ob_get_clean();
    
    $objResponse->assign('promo-feedbacks', 'innerHTML', $html);
    $objResponse->script('PromoSBR.newFeedbacksLoaded()');
    $objResponse->script("JSScroll($('promo-feedbacks'), true)");
    
    return $objResponse;
}

/**
 * возвращает статистику в промоблок Безопасной Сделки
 */
function getPromoStats()
{
    $objResponse = new xajaxResponse();
    
    $promoStats = sbr_meta::getpromoStats();
    $roleStr = (is_emp() || !get_uid()) ? 'emp' : 'frl';
    
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'] . '/promo/sbr/new/tpl.stats.php');
    $html = ob_get_clean();
    
    $objResponse->assign('promo-stats', 'innerHTML', $html);
    
    return $objResponse;
}

$xajax->processRequest();
?>