var sliderObject1 = $('slider1');
var knobObject1 = $('knob1');

if (sliderObject1 && knobObject1) {
    var SliderObject1 = new Slider(sliderObject1, knobObject1, {
        range: [0, 100],
        snap: true,
        steps: 100,
        offset: 0,
        wheel: true,
        mode: 'horizontal',
        onChange: function (step) {
            u = Math.floor($('mass_f_users').get('value') * (step / 100));
            $('mass_find_count').set('html', u);
            p = Math.floor($('mass_f_cost').get('value') / $('mass_f_users').get('value') * u);
            $('mass_find_cost').set('html', p);
        },
        onTick: function (pos) {
            this.knob.setStyle('left', pos);
        },
        onComplete: function (step) {
            u = Math.floor($('mass_f_users').get('value') * (step / 100));
            p = Math.floor($('mass_f_cost').get('value') / $('mass_f_users').get('value') * u);
            $('mass_max_users').set('value', u);
            $('mass_max_cost').set('value', p);
            $('mass_find_cost').set('html', p);
        }
    });


    SliderObject1.set(100);

    window.addEvent('domready', function () {
        mass_spam.send();
    });
}

function quickMAS_Reset() {
    $('quick_mas_div_main').removeClass('b-layout_hide');
    $("quick_mas_div_wait").addClass("b-layout_hide");
    $("quick_mas_div_main").removeClass("b-layout_waiting");
    $("quick_mas_div_error").addClass("b-layout_hide");
    $("quickmas_f_mas_subcat").set('html', '');
}

function quickMAS_show() {
    quickMAS_Reset();
    quickMAS_select(null);
    $('quick_mas_win_main').removeClass('b-shadow_hide');

    $('quick_mas_overlay').setStyle("display", "");
}

var quick_mas_sum_1 = 0;
var quick_mas_sum_2 = 0;
var quick_mas_sum_3 = 0;

/**
 * ѕровер€ет допустимость оплаты через платежные системы
 */
function quickMAS_checkPaymentTypes(price)
{
    var limitedPaymentTypes = $('quick_mas_win_main').getElements('[data-maxprice]');
    limitedPaymentTypes.each(function (el) {
        var maxSum = parseInt(el.get('data-maxprice'));
        if (parseInt(maxSum) < price) {
            el.addClass('b-layout_hide');
        } else {
            el.removeClass('b-layout_hide');
        }
    });
}

function quickMAS_select(obj) {

    $('quickmas_f_mas_u_count').set('html', $('mass_find_count').get('html'));
    c_cats = $('mass_f_cats').get('value');


    if (c_cats) {
        xajax_quickMASSetCats($('mass_f_cats').get('value'));
    }

    sum = $('mass_find_cost').get('html');

    $('quick_mas_sum_pay').set('data-price', sum);

    quickMAS_onSumChanged(sum);

    if (obj != null) {
        obj.fireEvent('click');
    }
    quickMAS_checkPaymentTypes(sum);
}

function quickMAS_process(type, step) {
    var promo_code = $('quick_mas_promo_code').get('value');
    switch (type) {
        case 'webmoney':
            xajax_quickMASGetYandexKassaLink(xajax.getFormValues('mass_frm'), 'WM', promo_code);
            break;
        case 'ya':
            xajax_quickMASGetYandexKassaLink(xajax.getFormValues('mass_frm'), 'PC', promo_code);
            break;
        case 'account':
            xajax_quickMASPayAccount(xajax.getFormValues('mass_frm'), promo_code);
            break;
        case 'dolcard':
            xajax_quickMASGetYandexKassaLink(xajax.getFormValues('mass_frm'), 'AC', promo_code);
            break;
        case 'alfaclick':
            xajax_quickMASGetYandexKassaLink(xajax.getFormValues('mass_frm'), 'AB', promo_code);
            break;
        case 'sberbank':
            xajax_quickMASGetYandexKassaLink(xajax.getFormValues('mass_frm'), 'SB', promo_code);
            break;
    }
}

function quickMAS_onSumChanged(price)
{
    var popup = $('quick_mas_win_main');
    var promo_code_info = popup.getElement('.promo_code_info');
    if (typeof promo_code_info !== 'undefined' && promo_code_info && promo_code_info.get('data-discount-price')) {
        quickMAS_applyPromo();
    } else {
        quickMAS_changePrice(price);
    }
}

function quickMAS_initPromo()
{
    var popup = $('quick_mas_win_main');
    
    if (!popup) {
        return;
    }
    
    var promo_code_link = popup.getElements('.promo_code_link');
    var promo_code_input = popup.getElement('.promo_code_input');
    var promo_code_info = popup.getElement('.promo_code_info');

    if (promo_code_link && promo_code_input && promo_code_info) {
        promo_code_link.addEvent('click', function () {
            this.getParent().getNext().removeClass('b-layout_hide');
            this.getParent().addClass('b-layout_hide');
            return false;
        });

        var promoInput = $('quick_mas_promo_code');
        var promo_service = promo_code_input.get('data-service');
        var promo_old_value;
        promo_code_input.addEvent('keydown', function () {
            promo_old_value = this.get('value');
        }).addEvent('keyup', function () {
            var value = this.get('value');
            if (promo_old_value != value) {
                promoInput.set('value', value);
                return xajax_checkPromoCode(
                        'mas',
                        value,
                        promo_service,
                        'mas'
                        );
            }
        });
    }
}

function quickMAS_applyPromo()
{
    var popup = $('quick_mas_win_main');
    var promo_code_info = popup.getElement('.promo_code_info');
    var price = $('quick_mas_sum_pay').get('data-price');
    var discount = parseInt(promo_code_info.get('data-discount-price'));

    if (isNaN(discount)) {
        quickMAS_changePrice(price);
    } else if (discount > 0) {
        promo_code_info.set('text', "—кидка " + discount + " руб.");
        var newPrice = price - discount;
        if (newPrice < 0)
            newPrice = 0;
        quickMAS_changePrice(newPrice);
    } else {
        discount = parseInt(promo_code_info.get('data-discount-percent'));
        var priceDiscount = price * discount / 100;
        if (priceDiscount > 0) {
            promo_code_info.set('text', "—кидка " + priceDiscount + " руб.");
        }
        newPrice = price - priceDiscount;
        quickMAS_changePrice(newPrice);
    }
}

quickMAS_changePrice = function (sum)
{
    $('quick_mas_sum_pay').set('html', sum);
    var ss = parseFloat($('quick_mas_f_account_sum').get('value'));
    ss = Math.floor(ss);
    if (parseFloat(sum) <= ss) {
        $('quick_mas_block_1').hide();
        $('quick_mas_block_2').show();
        $('quick_mas_sum_account2').hide();
        $('quick_mas_sum_span_1').hide();
        $('quick_mas_sum_span_4').show();
        $('quick_mas_sum_span_2').hide();
        $('quick_mas_sum_span_3').show();
        $('quick_mas_sum_span_5').hide();
        // денег хватает
    } else {
        $('quick_mas_block_2').hide();
        $('quick_mas_sum_span_1').hide();
        $('quick_mas_sum_span_4').show();
        $('quick_mas_sum_span_2').show();
        $('quick_mas_sum_span_3').hide();
        $('quick_mas_sum_span_5').show();

        if (ss == 0) {
            $('quick_mas_sum_span_1').show();
            $('quick_mas_sum_span_4').hide();
            $('quick_mas_sum_span_2').hide();
            $('quick_mas_sum_span_3').hide();
            $('quick_mas_sum_span_5').hide();
        } else {
            var s = ss - parseFloat(sum);
            s = -1 * s;
            s = Math.floor(s);
            if (s < 11) {
                s = 10;
            }
            $('quick_mas_block_1').show();
            $('quick_mas_block_2').hide();
            $('quick_mas_sum_span_6').set('html', s);
            $('quick_mas_sum_span_7').set('html', ss);
        }
    }
    $('quick_mas_block_2_btn').set('html', ' упить за ' + sum + ' ' + ending(sum, 'рубль', 'рубл€', 'рублей'));
}


var limit = 1200; // в секундах
var timeout_id = null;

function resetTimer() {
    limit = 1200;
    clearTimeout(timeout_id);
}

function processTimer(id) {

    if (limit > 0) {
        timeout_id = setTimeout("processTimer(" + id + ")", 1000);
        limit--;
    } else {
        $("quick_mas_div_error").removeClass("b-layout_hide");
        $("quick_mas_div_wait").addClass("b-layout_hide");
        $("quick_mas_div_main").removeClass("b-layout_waiting");
    }

    var limit_div = parseInt(limit / 60); // минуты
    var limit_mod = limit - limit_div * 60; // секунды

    // строка с оставшимс€ временем
    limit_str = "&nbsp;&nbsp;";
    if (limit_div < 10)
        limit_str = limit_str + "0";
    limit_str = limit_str + limit_div + ":";
    if (limit_mod < 10)
        limit_str = limit_str + "0";
    limit_str = limit_str + limit_mod + "&nbsp;&nbsp;";

    // вывод времени
    el_timer = document.getElementById("timer");
    if (el_timer)
        el_timer.innerHTML = limit_str;
}

window.addEvent('domready', function () {
    quickMAS_initPromo();
});