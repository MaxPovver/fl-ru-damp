<div id="quick_acc_win_main" class="b-shadow b-shadow_center b-shadow_width_520 b-shadow_zindex_11 b-shadow_hide b-shadow__quick" style="display:block;">
   <div class="b-shadow__body b-shadow__body_pad_15_20">
        <div class="b-fon b-fon_bg_pp">
            <div class="b-layout__title b-layout__title_padbot_5"><span class="b-icon b-icon__pp b-icon_float_left b-icon_top_4 b-page__desktop b-page__ipad"></span>Погашение задолженности<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1 b-page__desktop b-page__ipad">на личном счете</div></div>
        </div>
    
        <div id="quick_acc_div_main">
    
            <input type="hidden" id="quick_acc_f_account_sum" value="<?= round($_SESSION['ac_sum'], 2)<0 ? 0 : round($_SESSION['ac_sum'], 2) ?>"/>
    
            <div class="b-layout__txt b-layout__txt_padtb_10 b-layout__txt_fontsize_15">Сумма и способ оплаты</div>
    
            <div id="quick_acc_div_error" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span id="quick_acc_div_error_txt">К сожалению, в процессе оплаты произошла ошибка, и платеж не был завершен. Попробуйте провести оплату еще раз.</span>
                </div>
            </div>
    
    
            <?
            $quick_acc_sum = floor($_SESSION['ac_sum']);
            if($quick_acc_sum>-10) { $quick_acc_sum=-10; }
            $quick_acc_sum  = $quick_acc_sum*-1;
            ?>
            <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
                Сумма к оплате: <span id="quick_acc_sum_pay"><?=$quick_acc_sum?></span> руб.<br>
            </div>
    
    
            <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
                <span id="quick_acc_sum_span_1">Ее вы можете оплатить одним из способов:</span>
                <span id="quick_acc_sum_account2"></span>
            </div>
    
    
            <div id="quick_acc_block_1">
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> 
                    <a class="b-button b-button__pm  b-button__pm_card b-button_margbot_5" href="#" onClick="quickACC_process('dolcard', 1); return false;"><span class="b-button__txt">Пластиковые<br>карты</span></a> 
                    <a class="b-button b-button__pm  b-button__pm_yd b-button_margbot_5" href="#" onClick="quickACC_process('ya', 1); return false;"><span class="b-button__txt">Яндекс.Деньги</span></a> 
                    <a class="b-button b-button__pm  b-button__pm_wm b-button_margbot_5" href="#" onClick="quickACC_process('webmoney', 1); return false;"><span class="b-button__txt">WebMoney</span></a> 
                    <?php if ($quick_acc_sum <= yandex_kassa::MAX_PAYMENT_SB): ?> 
                        <a class="b-button b-button__pm  b-button__pm_sber b-button_margbot_5" href="#" onClick="quickACC_process('sberbank', 1); return false;"><span class="b-button__txt">Сбербанк<br />Онлайн</span></a> 
                    <?php endif; ?>
                    <?php if ($quick_acc_sum <= yandex_kassa::MAX_PAYMENT_ALFA): ?> 
                        <a class="b-button b-button__pm  b-button__pm_alfa b-button_margbot_5" href="#" onClick="quickACC_process('alfaclick', 1); return false;"><span class="b-button__txt">Альфа Клик</span></a> 
                    <?php endif; ?>
                </div>
            </div>
    
    
            <div id="quick_acc_div_wait" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_<?= is_emp() ? '6db335' : 'fd6c30'?> b-layout_hide">
                <span id="quick_acc_div_wait_txt"></span> 
                <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10"><img src="/images/<?= is_emp() ? 'Green' : 'Orange'?>_timer.gif" width="80" height="20"></div>
                <span id="timer"></span>
            </div>
    
        </div>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>


<div id="quick_acc_win_main_ok" class="b-shadow b-shadow_center b-shadow_width_520 b-shadow_zindex_11 b-shadow_bg_eeffe5 b-shadow__quick <?= $_GET['quickacc_ok'] ? '' : 'b-shadow_hide' ?>" style="display:block;">
   <div class="b-shadow__body b-shadow__body_pad_15_20">
    <div class="b-fon b-fon_bg_pp">
        <div class="b-layout__title b-layout__title_padbot_5"><span class="b-icon b-icon__pp b-icon_float_left b-icon_top_4 b-page__desktop b-page__ipad"></span>Задолженность успешно погашена<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1 b-page__desktop b-page__ipad">на личном счете</div></div>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_20">Спасибо за оплату. <br>Желаем вам успешной работы на сайте и множества выгодных заказов!</div>
    <div class="b-buttons b-buttons_padbot_10">
        <a class="b-button b-button_flat b-button_flat_green" href="" onClick="$('quick_acc_win_main_ok').addClass('b-shadow_hide'); return false;">Закрыть</a>&nbsp;&nbsp;&nbsp;
   </div>
   </div>
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>


<script type="text/javascript">

function quickACC_Reset() {
    $('quick_acc_div_main').removeClass('b-layout_hide');
    $("quick_acc_div_wait").addClass("b-layout_hide");
    $("quick_acc_div_main").removeClass("b-layout_waiting");
    $("quick_acc_div_error").addClass("b-layout_hide");
}

function quickACC_show() {
    quickACC_Reset();
    $('quick_acc_win_main').removeClass('b-shadow_hide');
}

function quickACC_select(obj) {
    var sum = 0;

    $('quick_acc_sum_pay').set('html', sum);

    var ss = parseFloat($('quick_acc_f_account_sum').get('value'));
    ss = Math.floor(ss);
    if(parseFloat(sum)<=ss) {
        $('quick_acc_block_1').hide();
        $('quick_acc_block_2').show();
        $('quick_acc_sum_account2').hide();
        $('quick_acc_sum_span_1').hide();
        $('quick_acc_sum_span_4').show();
        $('quick_acc_sum_span_2').hide();
        $('quick_acc_sum_span_3').show();
        $('quick_acc_sum_span_5').hide();
        // денег хватает
    } else {
        $('quick_acc_block_2').hide();
        $('quick_acc_sum_span_1').hide();
        $('quick_acc_sum_span_4').show();
        $('quick_acc_sum_span_2').show();
        $('quick_acc_sum_span_3').hide();
        $('quick_acc_sum_span_5').show();

        if(ss==0) {
            $('quick_acc_sum_span_1').show();
            $('quick_acc_sum_span_4').hide();
            $('quick_acc_sum_span_2').hide();
            $('quick_acc_sum_span_3').hide();
            $('quick_acc_sum_span_5').hide();
        } else {
            var s = ss-parseFloat(sum);
            s = -1*s;
            s = Math.floor(s);
            if(s<11) { s = 10; }
            $('quick_acc_block_1').show();
            $('quick_acc_block_2').hide();
            $('quick_acc_sum_span_6').set('html', s);
            $('quick_acc_sum_span_7').set('html', ss);
        }
    }
    $('quick_acc_block_2_btn').set('html', 'Купить за '+sum+' рублей');
    if(obj!=null) {
        obj.fireEvent('click');
    }
}

<?php 
require_once(ABS_PATH . '/classes/yandex_kassa_helper.php');
if(yandex_kassa_helper::isAllowKassa()) { ?>
function quickACC_process(type, step) {
    switch(type) {
        case 'webmoney':
            xajax_quickACCGetYandexKassaLink($('quick_acc_sum_pay').get('html'), 'WM');
            break;
        case 'ya':
            xajax_quickACCGetYandexKassaLink($('quick_acc_sum_pay').get('html'), 'PC');
            break;
        case 'dolcard':
            xajax_quickACCGetYandexKassaLink($('quick_acc_sum_pay').get('html'), 'AC');
            break;
        case 'alfaclick':
            xajax_quickACCGetYandexKassaLink($('quick_acc_sum_pay').get('html'), 'AB');
            break;
        case 'sberbank':
            xajax_quickACCGetYandexKassaLink($('quick_acc_sum_pay').get('html'), 'SB');
            break;
    }
}
<?php } else { ?>
function quickACC_process(type, step) {
    switch(type) {
        case 'webmoney':
            xajax_quickACCGetWebmoneyLink($('quick_acc_sum_pay').get('html'));
            break;
        case 'ya':
            xajax_quickACCGetYdLink($('quick_acc_sum_pay').get('html'));
            break;
        case 'dolcard':
            xajax_quickACCGetDOLCardLink($('quick_acc_sum_pay').get('html'));
            break;
    }
}
<?php } ?>


var limit = 1200; // в секундах
var timeout_id = null;

function resetTimer() {
    limit = 1200;
    clearTimeout(timeout_id);
}

function processTimer(id){

  if (limit > 0) {
    timeout_id = setTimeout("processTimer("+id+")",1000);
    limit--;
  } else {
    $("quick_acc_div_error").removeClass("b-layout_hide");
    $("quick_acc_div_wait").addClass("b-layout_hide");
    $("quick_acc_div_main").removeClass("b-layout_waiting");
  }

  var limit_div = parseInt(limit/60); // минуты
  var limit_mod = limit - limit_div*60; // секунды
  
  // строка с оставшимся временем
  limit_str = "&nbsp;&nbsp;";
  if (limit_div < 10) limit_str = limit_str + "0";
  limit_str = limit_str + limit_div + ":";
  if (limit_mod < 10) limit_str = limit_str + "0";
  limit_str = limit_str + limit_mod + "&nbsp;&nbsp;";      
  
  // вывод времени
  el_timer = document.getElementById("timer");
  if (el_timer) el_timer.innerHTML = limit_str;
}

</script>

<style type="text/css">
.b-check__label, input.b-check__input{ vertical-align:top !important;}
</style>

