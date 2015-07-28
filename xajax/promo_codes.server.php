<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/promo.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/PromoCodes.php");

/**
 * возвращает отзывы сервису в промоблок Безопасной Сделки
 */
function checkPromoCode($popup, $code, $service_id, $type = 'id')
{
    $objResponse = new xajaxResponse();
    
    $promoCodes = new PromoCodes();
    
    $services = strpos($service_id, '|') ? explode('|', $service_id) : $service_id;

    $codeInfo = $promoCodes->check($code, $services);
    
    $classAction = $codeInfo['success'] ? 'remove' : 'add';
    
    $inputSelector = '';
            
    switch ($type) {
        case 'pro':
            $scriptSelector = "info_block = $('quick_pro_win_main').getElement('.promo_code_info');";
            $inputSelector = "code_input = $('quick_pro_win_main').getElement('.promo_code_input');";
            $scriptRecalc = "quickPRO_applyPromo();";
            break;
        
        case 'prj':
            $scriptSelector = "info_block = $('quick_pro_win_main').getElement('.promo_code_info');";
            $inputSelector = "code_input = $('quick_pro_win_main').getElement('.promo_code_input');";
            $scriptRecalc = "";
            
            $projectServices = array(
                'contest' => PromoCodes::SERVICE_CONTEST,
                'vacancy' => PromoCodes::SERVICE_VACANCY,
                'project' => PromoCodes::SERVICE_PROJECT
            );
            foreach ($projectServices as $key => $value) {
                $use_discount = (int) (is_array($codeInfo['services']) 
                        && in_array($value, $codeInfo['services']));

                $scriptRecalc .= "info_block.set('data-service-{$key}', {$use_discount});
                "; 
            }
            $scriptRecalc .= "quickPRJ_applyPromo();";
            break;
        
        case 'mas':
            $scriptSelector = "info_block = $('quick_mas_win_main').getElement('.promo_code_info');";
            $inputSelector = "code_input = $('quick_mas_win_main').getElement('.promo_code_input');";
            $scriptRecalc = "quickMAS_applyPromo();";
            break;
        
        case 'autoresponse':
            $scriptSelector = "info_block = $('quick_payment_autoresponse').getElement('.promo_code_info');";
            $inputSelector = "code_input = $('quick_payment_autoresponse').getElement('.promo_code_input');";
            $scriptRecalc = "autoresponseApplyPromo();";
            break;
        
        case 'ext':
            $scriptSelector = " var qp = window.quick_ext_payment_factory.getQuickPayment('".$popup."');
            if(qp) {
                info_block = qp.promo_code_info;
                code_input = qp.promo_code_input;
            }";
            $scriptRecalc = "qp.applyPromo();";
            break;

        default:
            $scriptSelector = " var qp = window.quick_payment_factory.getQuickPayment('".$popup."');
            if(qp) {
                info_block = qp.promo_code_info;
                code_input = qp.promo_code_input;
            }";
            $scriptRecalc = "qp.applyPromo();";
            break;
    }
    
    if ($popup == 'tservicebind') {
        $scriptSelector = "var qp = window.quick_payment_factory.getQuickPaymentById('tservicebind', '".$type."');
        if(qp) {
            info_block = qp.promo_code_info;
            code_input = qp.promo_code_input;
        }";
    }
    
     $objResponse->script("
            var info_block;
            var code_input;
            {$scriptSelector}
            {$inputSelector}
            if (code_input.get('value') == '{$code}') {
                info_block.set('text', '{$codeInfo['message']}');
                info_block.set('data-discount-percent', '{$codeInfo['discount_percent']}');
                info_block.set('data-discount-price', '{$codeInfo['discount_price']}');
                info_block.{$classAction}Class('b-layout__txt_color_c10600');
                {$scriptRecalc}
            }
        ");
    
    return $objResponse;
}

$xajax->processRequest();
?>