<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.common.php");
$xajax->printJavascript('/xajax/'); 
?>
<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
    <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_center b-layout__left_padtop_80 b-layout__td_width_null_ipad">
            <span class="b-page__desktop"><img class="b-promo__pic" src="/images/promo-icons/big/3.png" alt="" /></span>
        </td>
        <td class="b-layout__td b-layout__td_width_72ps b-layout__td_width_full_ipad">

            <h1 class="b-page__title">Рассылка одобрена</h1>

            <div class="b-fon b-fon_padbot_30">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                    <div class="b-fon__txt b-fon__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>
                        Ваша заявка на рассылку была рассмотрена и одобрена модераторами сайта.
                    </div>
                    <div class="b-fon__txt">
                        Если у вас возникнут вопросы, обращайтесь в <a class="b-fon__link" href="https://feedback.fl.ru/">службу поддержки</a>.
                    </div>
                </div>
            </div>                       

            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                Текст рассылки                
            </div>
            
            <div class="b-layout__txt b-layout__txt_padbot_20"><?= $text ?></div>
            
            <a href="javascript:void(0);"
               class="b-button b-button_flat b-button_flat_green"
               data-popup="quick_payment_masssending">
                Оплатить
            </a>
            <?php echo quickPaymentPopupMasssending::getInstance()->render() ?>
        </td>							
    </tr>
</table>
