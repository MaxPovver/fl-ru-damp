<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/PromoCodes.php");
$promoCodes = new PromoCodes();
?>
<div id="quick_mas_win_main" class="b-shadow b-shadow_center b-shadow_width_520 b-shadow_pad_15_20 b-shadow_zindex_11 b-shadow_hide" style="display:block;">
    <div class="b-fon b-fon_bg_soap">
        <div class="b-layout__title b-layout__title_padbot_5"><span class="b-icon b-icon__soap b-icon_float_left b-icon_top_4"></span>Покупка рассылки по фрилансерам
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1">с поиском и привлечением лучших исполнителей</div>
        </div>
    </div>

    <div id="quick_mas_div_main" class="b-layout">

        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">Параметры рассылки</div>


        <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_bold">Количество получателей — <span class="b-layout__txt b-layout__txt_color_6db335" id="quickmas_f_mas_u_count">0</span></div>
        <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_fontsize_11 b-layout__txt_padbot_20"><span id="quickmas_f_mas_u_count_pro_txt">из них с аккаунтом PRO</span> — <span id="quickmas_f_mas_u_count_pro">0</span></div>

        <div id="quickmas_f_mas_subcat_m" class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_bold">Выбранных разделов/специализаций — <span class="b-layout__txt b-layout__txt_color_6db335" id="quickmas_f_mas_c_count">0</span></div>
        <div id="quickmas_f_mas_subcat" class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_fontsize_11 b-layout__txt_padbot_20"></div>





        <input type="hidden" id="quick_mas_f_account_sum" value="<?= round($_SESSION['ac_sum'], 2)<0 ? 0 : round($_SESSION['ac_sum'], 2) ?>"/>
        <input type="hidden" id="quick_mas_promo_code" value=""/>

        <div class="b-layout__txt b-layout__txt_padtb_10 b-layout__txt_fontsize_15">Сумма и способ оплаты</div>

        <div id="quick_mas_div_error" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                <span id="quick_mas_div_error_txt">К сожалению, в процессе оплаты произошла ошибка, и платеж не был завершен. Попробуйте провести оплату еще раз.</span>
            </div>
        </div>

        <?=$promoCodes->render(PromoCodes::SERVICE_MASSSENDING); ?>

        <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
            Сумма к оплате: <span id="quick_mas_sum_pay"></span> руб.<br>
        </div>


        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
            <span id="quick_mas_sum_span_4">
            <span id="quick_mas_sum_span_2">Часть суммы (<span id="quick_mas_sum_span_7"></span> руб.)</span><span id="quick_mas_sum_span_3">Она</span> будет списана с личного счета, на нем 
            <span id="quick_mas_sum_account1" class="b-layout__bold">
                <?php setlocale(LC_NUMERIC,'en_US');?>
                <? if (round($_SESSION['bn_sum'] + $_SESSION['ac_sum'], 2) > 0) { ?>
                    <?= number_format(round(zin($_SESSION['ac_sum']),2), 2, ",", " "); ?>
                <? } else { ?>
                    0
                <? } ?>
            </span>
             руб.<br>
             <span id="quick_mas_sum_span_5">
            Остаток (<span id="quick_mas_sum_span_6"></span> руб.) вам нужно оплатить одним из способов:
            </span>
            </span>
            <span id="quick_mas_sum_span_1">Ее вы можете оплатить одним из способов:</span>
            <span id="quick_mas_sum_account2"></span>
        </div>


        <div id="quick_mas_block_1">
            <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> 
                <a class="b-button b-button__pm b-button__pm_card b-button_margbot_5" href="#" onClick="quickMAS_process('dolcard', 1); return false;"><span class="b-button__txt">Пластиковые<br>карты</span></a> 
                <a class="b-button b-button__pm b-button__pm_yd b-button_margbot_5" href="#" onClick="quickMAS_process('ya', 1); return false;"><span class="b-button__txt">Яндекс.Деньги</span></a> 
                <a class="b-button b-button__pm b-button__pm_wm b-button_margbot_5" href="#" onClick="quickMAS_process('webmoney', 1); return false;"><span class="b-button__txt">WebMoney</span></a> 
                <a class="b-button b-button__pm b-button__pm_sber b-button_margbot_5" data-maxprice="<?=yandex_kassa::MAX_PAYMENT_SB?>" href="#" onClick="quickMAS_process('sberbank', 1); return false;"><span class="b-button__txt">Сбербанк<br />Онлайн</span></a> 
                <a class="b-button b-button__pm b-button__pm_alfa b-button_margbot_5" data-maxprice="<?=yandex_kassa::MAX_PAYMENT_ALFA?>" href="#" onClick="quickMAS_process('alfaclick', 1); return false;"><span class="b-button__txt">Альфа Клик</span></a> 
            </div>
        </div>

        <div id="quick_mas_block_2" class="b-buttons">
            <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> <a id="quick_mas_block_2_btn" class="b-button b-button_flat b-button_flat_green" href="#" onClick="quickMAS_process('account', 1); return false;">Оплатить 3588 руб.</a> </div>
        </div>

        <div id="quick_mas_div_wait" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_<?= is_emp() ? '6db335' : 'fd6c30'?> b-layout_hide">
            <span id="quick_mas_div_wait_txt"></span> 
            <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10"><img src="/images/<?= is_emp() ? 'Green' : 'Orange'?>_timer.gif" width="80" height="20"></div>
            <span id="timer"></span>
        </div>

    </div>

    <span class="b-shadow__icon b-shadow__icon_close" onClick="$('quick_mas_overlay').setStyle('display', 'none');"></span>
</div>


<div id="quick_mas_win_main_ok" class="b-shadow b-shadow_center b-shadow_width_520 b-shadow_pad_15_20 b-shadow_zindex_11 b-shadow_bg_eeffe5 <?= $_GET['quickmas_ok'] && $_SESSION['quickmass_ok'] ? '' : 'b-shadow_hide' ?>">
    <div class="b-fon b-fon_bg_soap">
        <div class="b-layout__title b-layout__title_padbot_5"><span class="b-icon b-icon__soap b-icon_float_left b-icon_top_4"></span>Рассылка успешно проведена
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1">с поиском и привлечением лучших исполнителей</div>
        </div>
    </div>    
    <div class="b-layout__txt b-layout__txt_padbot_15">
    Количество фрилансеров в рассылке — <span class="b-layout__txt b-layout__txt_color_6db335"><?=$_SESSION['quickmas_count_u']?></span>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_11">Спасибо за покупку.<br>Желаем вам успешной работы на сайте и хороших исполнителей!</div>
    <div class="b-buttons b-buttons_padbot_10">
        <a class="b-button b-button_flat b-button_flat_green" href="" onClick="$('quick_mas_win_main_ok').addClass('b-shadow_hide'); return false;">Закрыть</a>&nbsp;&nbsp;&nbsp;
   </div>
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>
<? unset($_SESSION['quickmass_ok']); ?> 

<style type="text/css">
#quick_mas_win_main{ height:auto !important; min-width:300px !important;}
@media screen and (max-width: 600px){
#quick_mas_win_main_ok{ width:90% !important; min-width:300px !important;}
}
</style>



<div id="quick_mas_overlay" class="b-shadow__overlay b-shadow_zindex_3" style="display: none;"></div>