<? if($_GET['quickprj_ok'] && $_SESSION['quickprj_ok']) { ?>
<div id="quick_prj_win_main_ok" class="b-shadow b-shadow_center b-shadow_width_520 b-shadow__quick  b-shadow_zindex_11 b-shadow_bg_eeffe5 <?= $_GET['quickprj_ok'] ? '' : 'b-shadow_hide' ?>" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_15_20">
       <div class="b-fon b-fon_bg_po">
          <div class="b-layout__title b-layout__title_padbot_5"><span class="b-icon b-icon__po b-icon_float_left b-icon_top_4 b-page__desktop b-page__ipad"></span>Вы успешно купили платные опции<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1 b-page__desktop b-page__ipad">с привлечением лучших исполнителей на выгодных условиях</div></div>
       </div>
       <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_11">Спасибо за покупку.<br>Желаем вам успешной работы на сайте и хороших исполнителей проекта!</div>
       <div class="b-buttons b-buttons_padbot_10">
          <a class="b-button b-button_flat b-button_flat_green" href="" onClick="$('quick_prj_win_main_ok').addClass('b-shadow_hide'); return false;">Закрыть</a>
       </div>
   </div>
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>
<? unset($_SESSION['quickprj_ok']); ?>
<script type="text/javascript">
//window.addEvent('load', function() {
    //yaCounter6051055.reachGoal('buy_project');
//});
</script>
<? } else { ?>

<iframe name="quick_pro_iframe" id="quick_pro_iframe" style="display: none;"></iframe>

<div id="quick_prj_win_main" class="b-shadow b-shadow_center b-shadow_width_520 b-shadow_hide b-shadow__quick" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_15_20">
        <div class="b-fon b-fon_bg_fpro">
            <div class="b-layout__title b-layout__title_padbot_5"><span class="b-icon b-icon__po b-icon_float_left b-icon_margtop_4 b-page__desktop b-page__ipad"></span>Покупка платных опций в проекте <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1 b-page__desktop b-page__ipad">с привлечением лучших исполнителей на выгодных условиях</div></div>
        </div>
    
        <div id="quick_pro_div_main">
            <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_fontsize_15">Выбранные вами платные опции</div>
    
            <input type="hidden" id="quick_pro_f_account_sum" value="<?= round($_SESSION['ac_sum'], 2)<0 ? 0 : round($_SESSION['ac_sum'], 2) ?>"/>
    
            <div id="quick_prj_f_d_vacancy" class="b-check b-check_padbot_10 b-check_padleft_20 b-check_inline-block b-check_width_200">
                <label class="b-check__label b-check__label_fontsize_13 b-check__label_color_6db335"><span class="b-layout__bold">Публикация вакансии</span> <div class="b-layout__txt b-layout__txt_fontsize_11 b-check__label_color_6db335 "><?=$priceVacancy ? $priceVacancy.' руб.' : ''?></div></label>
            </div>
    
            <div id="quick_prj_f_d_contest" class="b-check b-check_padbot_10 b-check_padleft_20 b-check_inline-block b-check_width_200">
                <label class="b-check__label b-check__label_fontsize_13 b-check__label_color_6db335"><span class="b-layout__bold">Публикация конкурса</span> <div class="b-layout__txt b-layout__txt_fontsize_11 b-check__label_color_6db335 "><?=$priceContest ? $priceContest.' руб.' : ''?></div></label>
            </div>
    
            <div id="quick_prj_f_d_ontop" class="b-check b-check_padbot_10 b-check_padleft_20 b-check_inline-block b-check_width_200">
                <input type="checkbox" value="" name="" class="b-check__input" id="quick_prj_f_ontop">
                <label class="b-check__label b-check__label_fontsize_13" for="quick_prj_f_ontop"><span class="b-layout__bold">Закрепление <? if($project['kind']==7) { ?>конкурса<? } elseif($project['kind']==4) { ?>вакансии<? } else {?>проекта<?} ?></span> <div class="b-layout__txt b-layout__txt_fontsize_11">на <span id="quick_prj_t_d"></span>, <span id="quick_prj_t_d_p"></span> руб.</div></label>
            </div>
    
            <div id="quick_prj_f_d_logo" class="b-check b-check_padbot_10 b-check_padleft_20 b-check_inline-block b-check_width_200">
                <input type="checkbox" value="" name="" class="b-check__input" id="quick_prj_f_logo">
                <label class="b-check__label b-check__label_fontsize_13" for="quick_prj_f_logo"><span class="b-layout__bold">Логотип и ссылка на сайт</span> <div class="b-layout__txt b-layout__txt_fontsize_11"><?=$priceLogo ? $priceLogo.' руб.' : ''?></div></label>
            </div>
    
            <div id="quick_prj_f_d_urgent" class="b-check b-check_padbot_10 b-check_padleft_20 b-check_inline-block b-check_width_200">
                <input type="checkbox" value="" name="" class="b-check__input" id="quick_prj_f_urgent">
                <label class="b-check__label b-check__label_fontsize_13" for="quick_prj_f_urgent"><span class="b-layout__bold">Опция "Срочный проект"</span> <div class="b-layout__txt b-layout__txt_fontsize_11"><?=$urgentPrice ? $urgentPrice.' руб.' : ''?></div></label>
            </div>
    
            <div id="quick_prj_f_d_hide" class="b-check b-check_padbot_10 b-check_padleft_20 b-check_inline-block b-check_width_200">
                <input type="checkbox" value="" name="" class="b-check__input" id="quick_prj_f_hide">
                <label class="b-check__label b-check__label_fontsize_13" for="quick_prj_f_hide"><span class="b-layout__bold">Опция "Скрытый проект"</span> <div class="b-layout__txt b-layout__txt_fontsize_11"><?=$hidePrice ? $hidePrice.' руб.' : ''?></div></label>
            </div>
    
            <div id="quick_prj_f_d_ontop2" class="b-check b-check_padbot_10 b-check_padleft_20 b-check_inline-block b-check_width_200">
                <input type="checkbox" value="" name="" class="b-check__input" id="quick_prj_f_ontop2">
                <label class="b-check__label b-check__label_fontsize_13" for="quick_prj_f_ontop2"><span class="b-layout__bold">Продление закрепления</span> <div class="b-layout__txt b-layout__txt_fontsize_11">на ХХ дней, 3000 руб.</div></label>
            </div>
    
            <div class="b-layout__txt b-layout__txt_padtb_10 b-layout__txt_fontsize_15">Сумма и способ оплаты</div>
    
            <div id="quick_pro_div_error" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span id="quick_pro_div_error_txt">К сожалению, в процессе оплаты произошла ошибка, и платеж не был завершен. Попробуйте провести оплату еще раз.</span>
                </div>
            </div>
    
            <?=$promoCodesForm?>
    
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
                    <a class="b-button b-button__pm b-button__pm_green b-button__pm_card b-button_margbot_5" href="#" onClick="quickPRJ_process('dolcard', 1); return false;"><span class="b-button__txt">Пластиковые<br>карты</span></a> 
                    <a class="b-button b-button__pm b-button__pm_green b-button__pm_yd b-button_margbot_5" href="#" onClick="quickPRJ_process('ya', 1); return false;"><span class="b-button__txt">Яндекс.Деньги</span></a> 
                    <a class="b-button b-button__pm b-button__pm_green b-button__pm_wm b-button_margbot_5" href="#" onClick="quickPRJ_process('webmoney', 1); return false;"><span class="b-button__txt">WebMoney</span></a> 
                    <a class="b-button b-button__pm b-button__pm_green b-button__pm_sber b-button_margbot_5" data-maxprice="<?=yandex_kassa::MAX_PAYMENT_SB?>" href="#" onClick="quickPRJ_process('sberbank', 1); return false;"><span class="b-button__txt">Сбербанк<br />Онлайн</span></a> 
                    <a class="b-button b-button__pm b-button__pm_green b-button__pm_alfa b-button_margbot_5" data-maxprice="<?=yandex_kassa::MAX_PAYMENT_ALFA?>" href="#" onClick="quickPRJ_process('alfaclick', 1); return false;"><span class="b-button__txt">Альфа Клик</span></a> 
                </div>
            </div>
    
            <div id="quick_pro_block_2" class="b-buttons">
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> <a id="quick_pro_block_2_btn" class="b-button b-button_flat b-button_flat_green" href="#" onClick="quickPRJ_process('account', 1); return false;">Оплатить 3588 руб.</a> </div>
            </div>
    
            <div id="quick_pro_div_wait" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_<?= is_emp() ? '6db335' : 'fd6c30'?> b-layout_hide">
                <span id="quick_pro_div_wait_txt"></span> 
                <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10"><img src="/images/<?= is_emp() ? 'Green' : 'Orange'?>_timer.gif" width="80" height="20"></div>
                <span id="timer"></span>
            </div>
    
        </div>

    </div>
    <span class="b-shadow__icon b-shadow__icon_close" onClick="$('quick_pro_overlay').setStyle('display', 'none');"></span>
</div>


<script type="text/javascript">

var quickPRJ_save_type = '';
var quickPRJ_select_count = 0;

var quickPRJ_prices = {
    vacancy: 0,
    top: 0,
    logo: 0, 
    contest: 0,
    urgent: 0,
    hide: 0
};

function quickPRJ_Reset() {
    $('quick_pro_div_main').removeClass('b-layout_hide');
    $("quick_pro_div_wait").addClass("b-layout_hide");
    $("quick_pro_div_main").removeClass("b-layout_waiting");
    $('quick_prj_f_d_vacancy').hide();
    $('quick_prj_f_d_contest').hide();
    $('quick_prj_f_d_ontop').hide();
    $('quick_prj_f_d_logo').hide();
    $('quick_prj_f_d_urgent').hide();
    $('quick_prj_f_d_hide').hide();
    $('quick_prj_f_d_ontop2').hide();
    $("quick_pro_div_error").addClass("b-layout_hide");
}

function clearPrices() {
    quickPRJ_prices = {
        vacancy: 0,
        top: 0,
        logo: 0, 
        contest: 0,
        urgent: 0,
        hide: 0
    };
}

function QuickPRJ_Recalc() {
        clearPrices();
        if (Public.isVacancy) {
            quickPRJ_prices.vacancy = Public.vacancyPrice;
            $('quick_prj_f_d_vacancy').show();
        }
        if ($('project_top_ok').get('checked')) {
            quickPRJ_prices.top = $('project_top_days').get('value') * Public.topDayPrice;
            $('quick_prj_t_d').set('html', $('project_top_days').get('value') + ' ' +  ending($('project_top_days').get('value'), 'день', 'дня', 'дней'));
            $('quick_prj_t_d_p').set('html', quickPRJ_prices.top);
            $('quick_prj_f_d_ontop').show();
            $('quick_prj_f_ontop').set('checked', true);
            $('quick_prj_f_ontop').fireEvent('click');
            quickPRJ_select_count--;
        }
        if ($('project_logo_ok').get('checked')) {
            quickPRJ_prices.logo = Public.logoPrice;
            $('quick_prj_f_d_logo').show();
            $('quick_prj_f_logo').set('checked', true);
            $('quick_prj_f_logo').fireEvent('click');
            quickPRJ_select_count--;
        }
        if (Public.isContest) {
            quickPRJ_prices.contest = Public.contestPrice;
            $('quick_prj_f_d_contest').show();
        }
        if ($('project_urgent').get('checked')) {
            if($('hidden_project_urgent').get('value')==0) {
                quickPRJ_prices.urgent = Public.urgentPrice;
                $('quick_prj_f_d_urgent').show();
                $('quick_prj_f_urgent').set('checked', true);
                $('quick_prj_f_urgent').fireEvent('click');
                quickPRJ_select_count--;
            }
        }
        if ($('project_hide').get('checked')) {
            if($('hidden_project_hide').get('value')==0) {
                quickPRJ_prices.hide = Public.hidePrice;
                $('quick_prj_f_d_hide').show();
                $('quick_prj_f_hide').set('checked', true);
                $('quick_prj_f_hide').fireEvent('click');
                quickPRJ_select_count--;
            }
        }
        
        var sum = quickPRJ_prices.vacancy + quickPRJ_prices.top 
                + quickPRJ_prices.logo + quickPRJ_prices.contest 
                + quickPRJ_prices.urgent + quickPRJ_prices.hide;

        quickPRJ_select(sum);
}

function quickPRJ_show() {
    quickPRJ_Reset();
    <? if($project['kind']==7) { ?>
        $('quick_prj_f_d_contest').show();
    <? } elseif($project['kind']==4) { ?>
        $('quick_prj_f_d_vacancy').show();
    <? } ?>
        quickPRJ_select_count = 0;
        clearPrices();
        if (Public.isVacancy) {
            quickPRJ_prices.vacancy = Public.vacancyPrice;
            $('quick_prj_f_d_vacancy').show();
        }
        if ($('project_top_ok') && $('project_top_ok').get('checked')) {
            quickPRJ_prices.top = $('project_top_days').get('value') * Public.topDayPrice;
            $('quick_prj_t_d').set('html', $('project_top_days').get('value') + ' ' +  ending($('project_top_days').get('value'), 'день', 'дня', 'дней'));
            $('quick_prj_t_d_p').set('html', quickPRJ_prices.top);
            $('quick_prj_f_d_ontop').show();
            $('quick_prj_f_ontop').set('checked', true);
            $('quick_prj_f_ontop').fireEvent('click');
        }
        if ($('project_logo_ok') && $('project_logo_ok').get('checked')) {
            quickPRJ_prices.logo = Public.logoPrice;
            $('quick_prj_f_d_logo').show();
            $('quick_prj_f_logo').set('checked', true);
            $('quick_prj_f_logo').fireEvent('click');
        }
        if (Public.isContest) {
            quickPRJ_prices.contest = Public.contestPrice;
            $('quick_prj_f_d_contest').show();
        }
        if ($('project_urgent') && $('project_urgent').get('checked')) {
            if($('hidden_project_urgent').get('value')==0) {
                quickPRJ_prices.urgent = Public.urgentPrice;
                $('quick_prj_f_d_urgent').show();
                $('quick_prj_f_urgent').set('checked', true);
                $('quick_prj_f_urgent').fireEvent('click');
            }
        }
        if ($('project_hide') && $('project_hide').get('checked')) {
            if($('hidden_project_hide').get('value')==0) {
                quickPRJ_prices.hide = Public.hidePrice;
                $('quick_prj_f_d_hide').show();
                $('quick_prj_f_hide').set('checked', true);
                $('quick_prj_f_hide').fireEvent('click');
            }
        }
        
        var sum = quickPRJ_prices.vacancy + quickPRJ_prices.top 
                + quickPRJ_prices.logo + quickPRJ_prices.contest 
                + quickPRJ_prices.urgent + quickPRJ_prices.hide;

        quickPRJ_select(sum);

    $('quick_prj_win_main').removeClass('b-shadow_hide');

    $('frm').set('target', 'quick_pro_iframe');
    $('is_exec_quickprj').set('value', '1');

    $('quick_pro_overlay').setStyle("display", "");
}

function quickPRJ_select(sum) {
    $('quick_pro_sum_pay').set('data-price', sum);
    quickPRJ_onSumChanged(sum);
}

/**
 * Проверяет допустимость оплаты через платежные системы
 */
function quickPRJ_checkPaymentTypes (price)
{
    var limitedPaymentTypes = $('quick_prj_win_main').getElements('[data-maxprice]');
    limitedPaymentTypes.each(function(el){
        var maxSum = parseInt(el.get('data-maxprice'));
        if (parseInt(maxSum) < price) {
            el.addClass('b-layout_hide');
        } else {
            el.removeClass('b-layout_hide');
        }
    });
}
    
function quickPRJ_process_continue() {
    var s = $('quick_pro_sum_pay').get('html');

    switch (quickPRJ_save_type) {
        case 'account':
            xajax_quickPRJPayAccount();
            break;
        case 'webmoney':
            xajax_quickPRJGetYandexKassaLink('WM');
            break;
        case 'ya':
            xajax_quickPRJGetYandexKassaLink('PC');
            break;
        case 'dolcard':
            xajax_quickPRJGetYandexKassaLink('AC');
            break;
        case 'alfaclick':
            xajax_quickPRJGetYandexKassaLink('AB');
            break;
        case 'sberbank':
            xajax_quickPRJGetYandexKassaLink('SB');
            break;
    }
    
}

function quickPRJ_process_done(url) {
    window.location = url;
}

function quickPRJ_process(type, step) {
    quickPRJ_save_type = type;
    $('frm').submit();   
}

function quickPRJ_initPromo()
{
    var popup = $('quick_prj_win_main');
    var promo_code_link = popup.getElements('.promo_code_link');
    var promo_code_input = popup.getElement('.promo_code_input');
    var promo_code_info = popup.getElement('.promo_code_info');
    
    if (promo_code_link && promo_code_input && promo_code_info) {
        promo_code_link.addEvent('click', function() {
            this.getParent().getNext().removeClass('b-layout_hide');
            this.getParent().addClass('b-layout_hide');
            return false;
        });

        var promoInput = new Element('input', {'type': 'hidden', 'name': 'promo'});
        promoInput.inject($('frm'));
        var promo_service = promo_code_input.get('data-service');
        var promo_old_value;
        promo_code_input.addEvent('keydown', function() {
            promo_old_value = this.get('value');
        }).addEvent('keyup', function() {
            var value = this.get('value');
            if (promo_old_value != value) {
                promoInput.set('value', value);
                return xajax_checkPromoCode(
                    'prj',
                    value,
                    promo_service,
                    'prj'
                );
            }
        });
    }
}

function quickPRJ_applyPromo() 
{
    var popup = $('quick_prj_win_main');
    var promo_code_info = popup.getElement('.promo_code_info');
    var newPrice = 0;
    var discount_price = parseInt(promo_code_info.get('data-discount-price'));
    var discount_percent = parseInt(promo_code_info.get('data-discount-percent'));
        
    if (isNaN(discount_price) || isNaN(discount_percent)) {
        for (i in quickPRJ_prices) {
            newPrice += quickPRJ_prices[i];
        }
        quickPRJ_changePrice(newPrice);
    } else {
        var allPrice = 0; //Сумма без скидок
        var discountSum = 0; //Сумма скидки в рублях
        var maxDiscountPrice = 0; //Максимальная скидка в рублях
        for (i in quickPRJ_prices) {
            //если есть услуга и скидка для нее, то прибавить 
            //к общей стоимости цену со скидкой
            var elemPrice = quickPRJ_prices[i];
            
            var useElem = "0";
            switch(i) {
                case 'contest':
                    useElem = promo_code_info.get('data-service-contest');
                    break;

                case 'vacancy':
                    useElem = promo_code_info.get('data-service-vacancy');
                    break;

                default:
                    useElem = promo_code_info.get('data-service-project');
                    break;
            }
                
            if (elemPrice > 0 && useElem == 1) {
                if (discount_price > 0) {
                    maxDiscountPrice += elemPrice;
                } else {
                    discountSum += elemPrice * discount_percent / 100;
                }
            }
            allPrice += elemPrice;
        }
        if (discount_price > 0) {
            discountSum = maxDiscountPrice > discount_price 
                ? discount_price 
                : maxDiscountPrice;
        }
        
        newPrice = allPrice - discountSum;
        
        if (discountSum > 0) {
            promo_code_info.set('text', "Скидка " + discountSum + " руб.");
        }
        quickPRJ_changePrice(newPrice);
    }
    
    
}

quickPRJ_onSumChanged = function(price)
{
    var popup = $('quick_prj_win_main');
    var promo_code_info = popup.getElement('.promo_code_info');
    if (typeof promo_code_info != 'undefined' && promo_code_info && promo_code_info.get('data-discount-price')) {
        quickPRJ_applyPromo();
    } else {
        quickPRJ_changePrice(price);
    }
}

quickPRJ_changePrice = function(sum)
{
    $('quick_pro_sum_pay').set('html', sum);
    //$('quick_pro_f_selected_item').set('value', sum);
    var ss = parseFloat($('quick_pro_f_account_sum').get('value'));
    ss = Math.floor(ss);
    if(parseFloat(sum)<=ss) {
        $('quick_pro_block_1').hide();
        $('quick_pro_block_2').show();
        $('quick_pro_sum_account2').hide();
        $('quick_pro_sum_span_1').hide();
        $('quick_pro_sum_span_4').show();
        $('quick_pro_sum_span_2').hide();
        $('quick_pro_sum_span_3').show();
        $('quick_pro_sum_span_5').hide();
        $('quick_pro_block_2').show();
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
            quickPRJ_checkPaymentTypes(sum);
        } else {
            var s = ss-parseFloat(sum);
            s = -1*s;
            s = Math.floor(s);
            if(s<11) { s = 10; }
            $('quick_pro_block_1').show();
            $('quick_pro_block_2').hide();
            $('quick_pro_sum_span_6').set('html', s);
            $('quick_pro_sum_span_7').set('html', ss);
            quickPRJ_checkPaymentTypes(s);
        }
    }
    $('quick_pro_block_2_btn').set('html', 'Купить за '+sum+' рублей');
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

window.addEvent('domready', 
function() {
    $('quick_prj_f_ontop').addEvent('click',function(){
        var o = $('project_top_ok').get('checked');
        if(this.getProperty('checked')==true){
            this.getNext('.b-check__label').addClass('b-check__label_color_6db335').getElement('.b-layout__txt').addClass('b-layout__txt_color_6db335');
            $('project_top_ok').set('checked', true);
            quickPRJ_select_count = quickPRJ_select_count+1;

        } else {
            if(quickPRJ_select_count==1) { this.set('checked', true); return; }
            this.getNext('.b-check__label').removeClass('b-check__label_color_6db335').getElement('.b-layout__txt').removeClass('b-layout__txt_color_6db335');
            $('project_top_ok').set('checked', false);
            quickPRJ_select_count = quickPRJ_select_count-1;
        }
        $('project_top_ok').fireEvent('change');
        if(o != $('project_top_ok').get('checked')) { QuickPRJ_Recalc(); }
    });
    $('quick_prj_f_logo').addEvent('click',function(){
        var o = $('project_logo_ok').get('checked');
        if(this.getProperty('checked')==true){
            this.getNext('.b-check__label').addClass('b-check__label_color_6db335').getElement('.b-layout__txt').addClass('b-layout__txt_color_6db335');
            $('project_logo_ok').set('checked', true);
            quickPRJ_select_count = quickPRJ_select_count+1;
        } else {
            if(quickPRJ_select_count==1) { this.set('checked', true); return; }
            this.getNext('.b-check__label').removeClass('b-check__label_color_6db335').getElement('.b-layout__txt').removeClass('b-layout__txt_color_6db335');
            $('project_logo_ok').set('checked', false);
            quickPRJ_select_count = quickPRJ_select_count-1;
        }
        $('project_logo_ok').fireEvent('change');
        if(o != $('project_logo_ok').get('checked')) { QuickPRJ_Recalc(); }
    });
    $('quick_prj_f_urgent').addEvent('click',function(){
        var o = $('project_urgent').get('checked');
        if(this.getProperty('checked')==true){
            this.getNext('.b-check__label').addClass('b-check__label_color_6db335').getElement('.b-layout__txt').addClass('b-layout__txt_color_6db335');
            $('project_urgent').set('checked', true);
            quickPRJ_select_count = quickPRJ_select_count+1;
        } else {
            if(quickPRJ_select_count==1) { this.set('checked', true); return; }
            this.getNext('.b-check__label').removeClass('b-check__label_color_6db335').getElement('.b-layout__txt').removeClass('b-layout__txt_color_6db335');
            $('project_urgent').set('checked', false);
            quickPRJ_select_count = quickPRJ_select_count-1;
        }
        $('project_urgent').fireEvent('change');
        if(o != $('project_urgent').get('checked')) { QuickPRJ_Recalc(); }
    });
    $('quick_prj_f_hide').addEvent('click',function(){
        var o = $('project_hide').get('checked');
        if(this.getProperty('checked')==true){
            this.getNext('.b-check__label').addClass('b-check__label_color_6db335').getElement('.b-layout__txt').addClass('b-layout__txt_color_6db335');
            $('project_hide').set('checked', true);
            quickPRJ_select_count = quickPRJ_select_count+1;
        } else {
            if(quickPRJ_select_count==1) { this.set('checked', true); return; }
            this.getNext('.b-check__label').removeClass('b-check__label_color_6db335').getElement('.b-layout__txt').removeClass('b-layout__txt_color_6db335');
            $('project_hide').set('checked', false);
            quickPRJ_select_count = quickPRJ_select_count-1;
        }
        $('project_hide').fireEvent('change');
        if(o != $('project_hide').get('checked')) { QuickPRJ_Recalc(); }
    });
    $('quick_prj_f_ontop2').addEvent('click',function(){
        if(this.getProperty('checked')==true){
            this.getNext('.b-check__label').addClass('b-check__label_color_6db335').getElement('.b-layout__txt').addClass('b-layout__txt_color_6db335');
        } else {
            this.getNext('.b-check__label').removeClass('b-check__label_color_6db335').getElement('.b-layout__txt').removeClass('b-layout__txt_color_6db335');
        }
    });
    
    quickPRJ_initPromo();
})

</script>

<? } ?>

<style type="text/css">
.b-check__label, input.b-check__input{ vertical-align:top !important;}
</style>

<div id="quick_pro_overlay" class="b-shadow__overlay" style="display: none;"></div>
