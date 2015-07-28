<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/platipotom.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/PromoCodes.php");

if(isset($quickPRO_type) && $quickPRO_type == 'profi') {
    $quickPRO_proList = payed::getPayedPROFIList();
} else {
    $quickPRO_proList = payed::getPayedPROList( (is_emp() ? 'emp' : 'frl') );
}

$promoCodes = new PromoCodes();

$quickPRO_redirect = '';
$quickpro_ok_default = 'quickpro_ok';

switch($quickPRO_type) {
    case 'profi':
        $quickPRO_title = 'Покупка аккаунта PROFI';
        $quickPRO_ok_close_btn = 'Закрыть';    
        $quickpro_ok_default = 'quickprofi_ok';
        $quickpro_ok_title = 'Вы успешно купили аккаунт PROFI';
        $quickpro_ok_subtitle = '';
        break;
    case 'project':
        $quickPRO_title = 'Покупка аккаунта Pro для ответа на проект';
        $quickPRO_ok_close_btn = 'Закрыть и ответить на проект';
        $quickPRO_redirect = getFriendlyUrl('project', $project['id']);
        break;
    case 'promotion':
        $quickPRO_redirect = '/promotion/';
    default:
        $quickPRO_title = 'Покупка аккаунта Pro';
        $quickPRO_ok_close_btn = 'Закрыть';
        break;
}

if(!isset($_SESSION['quickbuypro_success_opcode'])) {
    $_GET[$quickpro_ok_default] = false;
}

$platipotom = new platipotom(true);
$platipotomMaxSum = (int)$platipotom->getMaxPrice(0);
?>

<div id="quick_pro_win_main" class="b-shadow b-shadow_center b-shadow_width_520 b-shadow_hide b-shadow__quick" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_15_20">
        <div class="b-fon b-fon_bg_fpro">
            <div class="b-layout__title b-layout__title_padbot_5">
                <span class="b-icon b-page__desktop b-page__ipad <?php if($quickPRO_type == 'profi') { ?>b-icon__profi<?php } else { ?>b-icon__spro b-icon__spro_<?=is_emp() ? 'e' : 'f'?> <?php } ?> b-icon_float_left b-icon_margtop_4 b-icon_margright_10"></span>
                <?=$quickPRO_title?>
                <div class="b-layout__txt b-layout__txt_padleft_70 b-layout__txt_fontsize_11 b-layout__txt_lineheight_1 b-page__desktop b-page__ipad"><?php if($quickPRO_type == 'profi') { ?>С увеличением рейтинга на 40% и скидкой 20% на все платные сервисы сайта.<?php } else { ?><?= is_emp() ? 'с выгодными скидками до 50% на дополнительные сервисы в проектах' : 'с неограниченными ответами в проектах и +20% к рейтингу'?><?php } ?>
                </div>
            </div>
        </div>
    
        <div id="quick_pro_div_main">
            <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_fontsize_15">Срок действия аккаунта</div>
    
            <div class="b-radio b-radio_padleft_20">
                <input type="hidden" id="quick_pro_f_selected_item" value=""/>
                <input type="hidden" id="quick_pro_promo_code" value=""/>
                <input type="hidden" id="quick_pro_f_account_sum" value="<?= round($_SESSION['ac_sum'], 2)<0 ? 0 : round($_SESSION['ac_sum'], 2) ?>"/>
                <?
                if(is_emp()) {
                    $s_code = 15;
                } else {
                    if (isAllowTestPro()) {
                        $s_code = 163;
                    } elseif( floor((strtotime("now")-strtotime($_SESSION['reg_date']))/86400)<7 ) {
                        $s_code = 132;
                    } else {
                        $s_code = 48;
                    }
                }
                ?>
                <? foreach($quickPRO_proList as $proItem) { ?>
                    <div class="b-radio__item b-radio__item_padbot_10 b-radio__item_inline-block b-radio__item_width_200">
                        <input type="radio" class="b-radio__input" <?= $proItem['opcode']==$s_code ? 'checked' : ''?> value="<?=$proItem['opcode']?>" name="quick_pro_f_item" id="quick_pro_f_item_<?=$proItem['opcode']?>" onClick="quickPRO_select(this);" sum="<?=$proItem['cost']?>" >
                        <label for="quick_pro_f_item_<?=$proItem['opcode']?>" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_margtop_-2">
                            <span class="b-layout__bold <?php if(isset($proItem['badge'])): ?>b-layout__bold_relative<?php endif; ?>" id="quick_pro_div_main_type_<?=$proItem['opcode']?>">
                                <? if($proItem['day']) { ?>
                                    <?= $proItem['day']?> <?= ending($proItem['day'], 'день', 'дня', 'дня')?>
                                <? } elseif ($proItem['week']) { ?>
                                    <?= $proItem['week']?> <?= ending($proItem['week'], 'неделя', 'недели', 'недель')?>
                                <? } else { ?>
                                    <? if($proItem['month']==12) { ?>
                                        1 год
                                    <? } else { ?>
                                        <?= $proItem['month']?> <?= ending($proItem['month'], 'месяц', 'месяца', 'месяцев')?>
                                    <? } ?>
                                <? } ?>
                                <?php if(isset($proItem['badge'])): ?>
                                    <span class="b-radio__label__badge b-radio__label__badge_top_-12_iphone"><?=$proItem['badge']?></span>
                                <?php endif; ?>
                            </span>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline_iphone">
                                <?php if(isset($proItem['old_cost'])): ?>
                                <strike class="b-layout__txt_color_41"><?=$proItem['old_cost']?> руб.</strike>&nbsp;
                                <?php endif; ?>
                                <?=$proItem['cost']?> руб.
                            </div>
                        </label>
                    </div>
                <? } ?>
            </div>
    
            <div class="b-layout__txt b-layout__txt_padtb_10 b-layout__txt_fontsize_15">Сумма и способ оплаты</div>
    
            <div id="quick_pro_div_error" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span id="quick_pro_div_error_txt">К сожалению, в процессе оплаты произошла ошибка, и платеж не был завершен. Попробуйте провести оплату еще раз.</span>
                </div>
            </div>
    
            <?=$promoCodes->render(PromoCodes::SERVICE_PRO); ?>
    
            <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
                Сумма к оплате: <span id="quick_pro_sum_pay"></span> руб.<br>
            </div>
    
    
            <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
                <span id="quick_pro_sum_span_4">
                <span id="quick_pro_sum_span_2">Часть суммы (<span id="quick_pro_sum_span_7"></span> руб.)</span><span id="quick_pro_sum_span_3">Она</span> будет списана с личного счета, на нем 
                <span id="quick_pro_sum_account1" class="b-layout__bold">
                    <?php setlocale(LC_NUMERIC,'en_US');?>
                    <? if (round($_SESSION['bn_sum'] + $_SESSION['ac_sum'], 2) > 0) { ?>
                        <?= number_format(round(zin($_SESSION['ac_sum']),2), 2, ",", " "); ?>
                    <? } else { ?>
                        0
                    <? } ?>
                </span>
                 руб.<br>
                 <span id="quick_pro_sum_span_5">
                Остаток (<span id="quick_pro_sum_span_6"></span> руб.) вам нужно оплатить одним из способов:
                </span>
                </span>
                <span id="quick_pro_sum_span_1">Ее вы можете оплатить одним из способов:</span>
                <span id="quick_pro_sum_account2"></span>
            </div>
    
    
            <div id="quick_pro_block_1">
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> 
                    <a class="b-button b-button__pm  b-button__pm_card b-button_margbot_5" href="#" onClick="quickPRO_process('dolcard', 1); return false;"><span class="b-button__txt">Пластиковые<br>карты</span></a> 
                    <a class="b-button b-button__pm  b-button__pm_yd b-button_margbot_5" href="#" onClick="quickPRO_process('ya', 1); return false;"><span class="b-button__txt">Яндекс.Деньги</span></a> 
                    <a class="b-button b-button__pm  b-button__pm_wm b-button_margbot_5" href="#" onClick="quickPRO_process('webmoney', 1); return false;"><span class="b-button__txt">WebMoney</span></a> 
                    <a class="b-button b-button__pm  b-button__pm_sber b-button_margbot_5" href="#" onClick="quickPRO_process('sberbank', 1); return false;"><span class="b-button__txt">Сбербанк<br />Онлайн</span></a> 
                    <a class="b-button b-button__pm  b-button__pm_alfa b-button_margbot_5" href="#" onClick="quickPRO_process('alfaclick', 1); return false;"><span class="b-button__txt">Альфа Клик</span></a> 
                    <a class="platipotom_link b-button b-button__pm b-button_margbot_5  b-button__pm_pp" data-maxprice="<?=$platipotomMaxSum?>" href="#" onClick="quickPRO_process('pp', 1); return false;"><span class="b-button__txt">Купить <br />с отсрочкой <br />оплаты</span></a> 
                    <div class="platipotom_text b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_valign_middle">Купите PRO сейчас, а оплатите потом с отсрочкой платежа до 30 дней через сервис &quot;ПлатиПотом&quot;</div>
                </div>
            </div>
    
            <div id="quick_pro_block_2" class="b-buttons">
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> <a id="quick_pro_block_2_btn" class="b-button b-button_flat b-button_flat_green" href="#" onClick="quickPRO_process('account', 1); return false;">Оплатить 3588 руб.</a> </div>
            </div>
    
            <div id="quick_pro_div_wait" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_<?= is_emp() ? '6db335' : 'fd6c30'?> b-layout_hide">
                <span id="quick_pro_div_wait_txt"></span> 
                <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10"><img src="/images/<?= is_emp() ? 'Green' : 'Orange'?>_timer.gif" width="80" height="20"></div>
                <span id="timer"></span>
            </div>
    
        </div>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>


<div id="quick_pro_win_main_ok" class="b-shadow b-shadow_center b-shadow_width_520 b-shadow_zindex_11 b-shadow_bg_eeffe5 b-shadow__quick <?= $_GET[$quickpro_ok_default] ? '' : 'b-shadow_hide' ?>" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_15_20">
        <div class="b-fon b-fon_bg_fpro">
            <div class="b-layout__title b-layout__title_padbot_5">
                <span class="b-icon b-page__desktop b-page__ipad <?php if($quickPRO_type == 'profi') { ?>b-icon__profi<?php } else { ?>b-icon__spro b-icon__spro_<?=is_emp() ? 'e' : 'f'?> <?php } ?> b-icon_float_left b-icon_margtop_4 b-icon_margright_10"></span>
                <?php if(isset($quickpro_ok_title)): ?>
                    <?= $quickpro_ok_title ?>
                <?php else: ?>
                Вы успешно купили аккаунт PRO 
                <?php endif; ?>
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1 b-page__desktop b-page__ipad">
                    <?php if(isset($quickpro_ok_subtitle)): ?>
                        <?= $quickpro_ok_subtitle ?>
                    <?php else: ?>
                        <?= is_emp() ? 'с выгодными скидками до 50% на дополнительные сервисы в проектах' : 'с неограниченными ответами в проектах и +20% к рейтингу'?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    
		<?
        $pro_last = false;
        if($_SESSION['freeze_from'] && $_SESSION['is_freezed']) {
            $pro_last = $_SESSION['payed_to'];
        } else if($_SESSION['pro_last']) {
            $pro_last = $_SESSION['pro_last'];
        }
        ?>
        <div class="b-layout__txt b-layout__txt_padbot_15">Срок действия аккаунта — <span class="b-layout__txt b-layout__txt_color_<?= is_emp() ? '6db335' : 'fd6c30'?>">до <?= date('d.m.Y H:i', strtotime($pro_last)) ?></span></div>
        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_11">
            <? if(is_emp()) { ?>
            Спасибо за покупку. <br>
            Желаем вам успешной работы на сайте и хороших исполнителей!
            <? } else { ?>
                <? if($quickPRO_type=='project') { ?>
                    <? if($project['is_blocked']=='t' || $project['closed']=='t') { ?>
                    К сожалению, выбранный вами проект уже закрыт, но вы можете посмотреть и ответить на другие проекты аналогичной тематики.<br><br>
                    Спасибо за покупку.<br>
                    Желаем вам успешной работы на сайте и множества выгодных заказов!
                    <? } else { ?>
                        Спасибо за покупку, теперь вы можете ответить на проект. <br>
                        Желаем вам успешной работы на сайте и множества выгодных заказов!
                    <? } ?>
                <? } else { ?>
                    Спасибо за покупку. <br>
                    Желаем вам успешной работы на сайте и множества выгодных заказов!
                <? } ?>
            <? } ?>
        </div>
        <div class="b-buttons b-buttons_padbot_10"> 
            <? if(is_emp()) { ?>
                <a class="b-button b-button_flat b-button_flat_green" href="#" onClick="$('quick_pro_win_main_ok').addClass('b-shadow_hide'); return false;">Закрыть</a> 
            <? } else { ?>
                <? if($quickPRO_type=='project') { ?>
                    <? if($project['is_blocked']=='t' || $project['closed']=='t') { ?>
                        <a class="b-button b-button_flat b-button_flat_green" href="/">Закрыть и посмотреть проекты</a> 
                    <? } else { ?>
                        <a class="b-button b-button_flat b-button_flat_green" href="#" onClick="$('quick_pro_win_main_ok').addClass('b-shadow_hide'); window.location.hash = '#new_offer'; return false;">Закрыть и ответить на проект</a> 
                    <? } ?>
                <? } else { ?>
                    <a class="b-button b-button_flat b-button_flat_green" href="#" onClick="$('quick_pro_win_main_ok').addClass('b-shadow_hide'); return false;">Закрыть</a> 
                <? } ?>
            <? } ?>
            <?php if($quickPRO_type != 'profi'): ?>        
            <span class="b-layout__txt b-layout__txt_fontsize_11">или <a class="b-layout__link" href="#" onClick="$('quick_pro_win_main_ok').addClass('b-shadow_hide'); quickPRO_show(); return false;">купить PRO на больший срок</a></span> 
            <?php endif; ?>
        </div>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>


<script type="text/javascript">

var quickPRO_selected = '';

function quickPRO_Reset() {
    $('quick_pro_div_main').removeClass('b-layout_hide');
    //$('quick_pro_div_main_2').addClass('b-layout_hide');
    $("quick_pro_div_wait").addClass("b-layout_hide");
    $("quick_pro_div_main").removeClass("b-layout_waiting");
    //$("quick_pro_div_wait_2").addClass("b-layout_hide");
    //$("quick_pro_div_main_2").removeClass("b-layout_waiting");
    <? if(is_emp()) { ?>
        quickPRO_select($('quick_pro_f_item_15'));
    <? } else { ?>
        <?php if(isAllowTestPro()){ ?>
            quickPRO_select($('quick_pro_f_item_163'));
        <? } elseif( floor((strtotime("now")-strtotime($_SESSION['reg_date']))/86400)<7 ) { ?>
            quickPRO_select($('quick_pro_f_item_132'));
        <? } elseif($quickPRO_type == 'profi') { ?>
            quickPRO_select($('quick_pro_f_item_164'));
        <? } else { ?>
            quickPRO_select($('quick_pro_f_item_48'));
        <? } ?>
    <? } ?>
}

function quickPRO_show() {
    quickPRO_Reset();
    <? if(is_emp()) { ?>
        quickPRO_select($('quick_pro_f_item_15'));
    <? } else { ?>
        <?php if(isAllowTestPro()){ ?>
            quickPRO_select($('quick_pro_f_item_163'));
        <? } elseif( floor((strtotime("now")-strtotime($_SESSION['reg_date']))/86400)<7 ) { ?>
            quickPRO_select($('quick_pro_f_item_132'));
        <? } elseif($quickPRO_type == 'profi') { ?>
            quickPRO_select($('quick_pro_f_item_164'));
        <? } else { ?>
            quickPRO_select($('quick_pro_f_item_48'));
        <? } ?>
    <? } ?>
    $('quick_pro_win_main').removeClass('b-shadow_hide');
}

function quickPRO_select(obj) {
    switch(obj.get('value')) {
        case 132:
            quickPRO_selected = '1d';
            break;
        case 131:
            quickPRO_selected = '1w';
            break;
        case 164:
        case 163:    
        case 15:
        case 48:
            quickPRO_selected = '1m';
            break;
        case 118:
        case 49:
            quickPRO_selected = '3m';
            break;
        case 119:
        case 50:
            quickPRO_selected = '6m';
            break;
        case 120:
        case 51:
            quickPRO_selected = '12m';
            break;
    }
    
    $('quick_pro_f_selected_item').set('value', obj.get('value'));
    
    quickPRO_onSumChanged(obj.get('sum'));
    obj.fireEvent('click');
}

function quickPRO_process(type, step) {
    var code = $('quick_pro_f_selected_item').get('value');
    var promo_code = $('quick_pro_promo_code').get('value');
    switch (type) {
        case 'account':
            xajax_quickPROPayAccount(code, '<?=$quickPRO_redirect?>', promo_code);
            break;
        case 'webmoney':
            xajax_quickPROGetYandexKassaLink(code, 'WM', '<?=$quickPRO_redirect?>', promo_code);
            break;
        case 'ya':
            xajax_quickPROGetYandexKassaLink(code, 'PC', '<?=$quickPRO_redirect?>', promo_code);
            break;
        case 'dolcard':
            xajax_quickPROGetYandexKassaLink(code, 'AC', '<?=$quickPRO_redirect?>', promo_code);
            break;
        case 'alfaclick':
            xajax_quickPROGetYandexKassaLink(code, 'AB', '<?=$quickPRO_redirect?>', promo_code);
            break;
        case 'sberbank':
            xajax_quickPROGetYandexKassaLink(code, 'SB', '<?=$quickPRO_redirect?>', promo_code);
            break;
        case 'pp':
            xajax_quickPROGetPlatipotomLink(code, '<?=$quickPRO_redirect?>', promo_code);
            break;
    }
}


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
    $("quick_pro_div_error").removeClass("b-layout_hide");
    $("quick_pro_div_wait").addClass("b-layout_hide");
    $("quick_pro_div_main").removeClass("b-layout_waiting");
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

function quickPRO_calcPlatipotom(sum)
{
    var ppl = $$('.platipotom_link');
    if (ppl) {
        pp_link = ppl[0];
        var maxSum = pp_link.get('data-maxprice');
        
        if (parseInt(maxSum) < sum) {
            ppl.addClass('b-layout_hide');
        } else {
            ppl.removeClass('b-layout_hide');
        }
    }
}

function quickPRO_initPromo()
{
    var popup = $('quick_pro_win_main');
    var promo_code_link = popup.getElements('.promo_code_link');
    var promo_code_input = popup.getElement('.promo_code_input');
    var promo_code_info = popup.getElement('.promo_code_info');
    
    if (promo_code_link && promo_code_input && promo_code_info) {
        promo_code_link.addEvent('click', function() {
            this.getParent().getNext().removeClass('b-layout_hide');
            this.getParent().addClass('b-layout_hide');
            return false;
        });

        var promoInput = $('quick_pro_promo_code');
        var promo_service = promo_code_input.get('data-service');
        var promo_old_value;
        promo_code_input.addEvent('keydown', function() {
            promo_old_value = this.get('value');
        }).addEvent('keyup', function() {
            var value = this.get('value');
            if (promo_old_value != value) {
                promoInput.set('value', value);
                return xajax_checkPromoCode(
                    'pro',
                    value,
                    promo_service,
                    'pro'
                );
            }
        });
    }
}
    
//--------------------------------------------------------------------------
    
    
function quickPRO_applyPromo() 
{
    var popup = $('quick_pro_win_main');
    var promo_code_info = popup.getElement('.promo_code_info');
    
    var code = $('quick_pro_f_selected_item').get('value');
    var selectedElem = $('quick_pro_f_item_'+code);

    var price = selectedElem.get('sum');

    var discount = parseInt(promo_code_info.get('data-discount-price'));
    
    if (discount > 0) {
        promo_code_info.set('text', "Скидка " + discount + " руб.");
        var newPrice = price - discount;
        if (newPrice < 0) newPrice = 0;
        quickPRO_changePrice(newPrice);
    } else {
        discount = parseInt(promo_code_info.get('data-discount-percent'));
        var priceDiscount = price * discount / 100;
        if (priceDiscount > 0) {
            promo_code_info.set('text', "Скидка " + priceDiscount + " руб.");
        }
        newPrice = price - priceDiscount;
        quickPRO_changePrice(newPrice);
    }
}

quickPRO_onSumChanged = function(price)
{
    var popup = $('quick_pro_win_main');
    var promo_code_info = popup.getElement('.promo_code_info');
    if (typeof promo_code_info != 'undefined' && promo_code_info && promo_code_info.get('data-discount-price')) {
        quickPRO_applyPromo();
    } else {
        quickPRO_changePrice(price);
    }
}

quickPRO_changePrice = function(price)
{
    $('quick_pro_sum_pay').set('html', price);
    var ss = parseFloat($('quick_pro_f_account_sum').get('value'));
    ss = Math.floor(ss);
    if(parseFloat(price)<=ss) {
        $('quick_pro_block_1').hide();
        $('quick_pro_block_2').show();
        $('quick_pro_sum_account2').hide();
        $('quick_pro_sum_span_1').hide();
        $('quick_pro_sum_span_4').show();
        $('quick_pro_sum_span_2').hide();
        $('quick_pro_sum_span_3').show();
        $('quick_pro_sum_span_5').hide();
        // денег хватает
    } else {
        $('quick_pro_block_2').hide();
        $('quick_pro_sum_span_1').hide();
        $('quick_pro_sum_span_4').show();
        $('quick_pro_sum_span_2').show();
        $('quick_pro_sum_span_3').hide();
        $('quick_pro_sum_span_5').show();

        if(ss==0) {
            $('quick_pro_sum_span_1').show();
            $('quick_pro_sum_span_4').hide();
            $('quick_pro_sum_span_2').hide();
            $('quick_pro_sum_span_3').hide();
            $('quick_pro_sum_span_5').hide();
            quickPRO_calcPlatipotom(parseFloat(price));
        } else {
            var s = ss-parseFloat(price);
            s = -1*s;
            s = Math.floor(s);
            if(s<11) { s = 10; }
            //$('quick_pro_div_main_2_pro').set('html', $('quick_pro_div_main_type_'+obj.get('value')).get('html'));
            //$('quick_pro_div_main_2_sum').set('html', s);
            $('quick_pro_block_1').show();
            $('quick_pro_block_2').hide();
            $('quick_pro_sum_span_6').set('html', s);
            $('quick_pro_sum_span_7').set('html', ss);
            quickPRO_calcPlatipotom(s);
        }
        
    }
    $('quick_pro_block_2_btn').set('html', 'Купить за '+price+' рублей');
}

quickPRO_init = function()
{
    $('quick_pro_win_main').getElements('.b-radio__input').addEvent('click',function(){
        this.getParent('.b-radio__item').getParent('.b-radio').getElements('.b-radio__label').removeClass('b-radio__label_color_<?= is_emp() ? "6db335" : "fd6c30"?>').getElement('.b-layout__txt').removeClass('b-layout__txt_color_<?= is_emp() ? "6db335" : "fd6c30"?>');
        if(this.getProperty('checked')==true){
            this.getNext('.b-radio__label').addClass('b-radio__label_color_<?= is_emp() ? "6db335" : "fd6c30"?>').getElement('.b-layout__txt').addClass('b-layout__txt_color_<?= is_emp() ? "6db335" : "fd6c30"?>');
        } else {
            this.getNext('.b-radio__label').removeClass('b-radio__label_color_<?= is_emp() ? "6db335" : "fd6c30"?>').getElement('.b-layout__txt').removeClass('b-layout__txt_color_<?= is_emp() ? "6db335" : "fd6c30"?>');
        }
    });
    
    var ppl = $$('.platipotom_link');
    var ppt = $$('.platipotom_text');
    if (ppl && ppt) {
        pp_link = ppl[0];
        pp_text = ppt[0];
        
        pp_text.addClass('b-layout_hide');
        
        pp_link.addEvent('mouseover', function(){
            pp_text.removeClass('b-layout_hide');
        }).addEvent('mouseout', function(){
            pp_text.addClass('b-layout_hide');
        });
    }
    
    quickPRO_initPromo();
    
    <?php if ($_GET[$quickpro_ok_default]): ?>
    window.addEvent('load', function() {
        yaCounter6051055.reachGoal('<?= is_emp() ? "r" : "f"?>pro_bill_win');
        yaCounter6051055.reachGoal('buy_<?=is_emp() ? "r" : "f"?>pro_<?=$_SESSION['quickbuypro_success_opcode']?>');
        <? unset($_SESSION['quickbuypro_success_opcode']); ?>
    });
    <?php endif; ?>
}
</script>


