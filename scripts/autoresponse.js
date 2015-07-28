var currencyList = {0:"USD", 1:"Евро", 2:"Руб"};

function autoresponseShowPayModal(id)
{
    $('autoresponse_hidden_id').set('value', id); 

    var $budget = $('filter_budget_budget').get('value') < 1?'любой':$('filter_budget_budget').get('value');
    var $total = $('el-total').get('value');
    var $sum = $total * autoresponse_price;

    if ($budget != 'любой') {
        $budget = new String($budget) + ' ' + $('filter_budget_currency').get("value");
        $budget += ' ' + $('filter_budget_priceby').get("value")
    }
    
    $('quickar_total').set('html', $total);
    $('quickar_budget').set('html', $budget);
    $('quickar_category').set('html', $$('input[name=filter_category]')[0].get('value'));
    $('quick_ar_sum_pay').set('data-sum', $sum);
   
    autoresponseApplyPromo();
    
    $('quick_payment_autoresponse').removeClass('b-shadow_hide');
    
}

function autoresponseChangePrice($sum)
{
    var blockPayNone = $('pay_none');
    var blockPayPart = $('pay_part');
    var blockPayFull = $('pay_full');
    
    var $ac_sum = parseFloat($('ac_sum').get('text'));
    var $pay_sum = Math.ceil($sum - $ac_sum);
    
    if ($pay_sum > 0) {
        if ($pay_sum < 10) $pay_sum = 10;
        $('payments').removeClass("b-layout_hide");
        $('payment_account').addClass("b-layout_hide");
        blockPayNone.addClass("b-layout_hide");
        if ($ac_sum > 0) {
            blockPayPart.removeClass("b-layout_hide");
            blockPayFull.addClass("b-layout_hide");
        } else {
            blockPayPart.addClass("b-layout_hide");
            blockPayFull.removeClass("b-layout_hide");
        }
        autoresponseCalcPlatipotom($pay_sum);
    } else {
        blockPayNone.removeClass("b-layout_hide");
        blockPayPart.addClass("b-layout_hide");
        blockPayFull.addClass("b-layout_hide");
        $('payments').addClass("b-layout_hide");
        $('payment_account').removeClass("b-layout_hide");            
    }
    
    $('quick_pro_sum_part').set('html', $pay_sum);
    $('quick_ar_sum_pay').set('html', $sum);
    $('quick_ar_sum_pay_acc').set('html', $sum);
    
}

function autoresponseInitPromo()
{
    var promo_code_link = $('quick_payment_autoresponse').getElements('.promo_code_link');
    var promo_code_input = $('quick_payment_autoresponse').getElement('.promo_code_input');
    var promo_code_info = $('quick_payment_autoresponse').getElement('.promo_code_info');

    if (promo_code_link && promo_code_input && promo_code_info) {
        promo_code_link.addEvent('click', function() {
            this.getParent().getNext().removeClass('b-layout_hide');
            this.getParent().addClass('b-layout_hide');
            return false;
        });
        
        var promoInput = new Element('input', {'type': 'hidden', 'name': 'promo'});
        promoInput.inject($('quick_payment_autoresponse').getElement('form'));
        var promo_service = promo_code_input.get('data-service');
        var promo_old_value;
        promo_code_input.addEvent('keydown', function() {
            promo_old_value = this.get('value');
        }).addEvent('keyup', function() {
            var value = this.get('value');
            if (promo_old_value != value) {
                promoInput.set('value', value);
                return xajax_checkPromoCode(
                    'autoresponse',
                    value,
                    promo_service,
                    'autoresponse'
                );
            }
        });
    }
}

function autoresponseApplyPromo()
{
    var promo_code_info = $('quick_payment_autoresponse').getElement('.promo_code_info');
    var price = parseInt($('quick_ar_sum_pay').get('data-sum'));

    var discount = promo_code_info 
        ? parseInt(promo_code_info.get('data-discount-price'))
        : NaN;
    if (isNaN(discount)) {
        autoresponseChangePrice(price);
    } else if (discount > 0) {
        promo_code_info.set('text', "Скидка " + discount + " руб.");
        var newPrice = price - discount;
        if (newPrice < 0) newPrice = 0;
        autoresponseChangePrice(newPrice);
    } else {
        discount = parseInt(promo_code_info.get('data-discount-percent'));
        var priceDiscount = price * discount / 100;
        if (priceDiscount > 0) {
            promo_code_info.set('text', "Скидка " + priceDiscount + " руб.");
        }
        newPrice = price - priceDiscount;
        autoresponseChangePrice(newPrice);
    }
}

/**
 * Проверяет допустимость оплаты через систему ПлатиПотом
 */
function autoresponseCalcPlatipotom(price)
{
    var platipotom_link = $('quick_payment_autoresponse').getElement('.platipotom_link');
    if (platipotom_link) {
        var maxSum = platipotom_link.get('data-maxprice');
        if (parseInt(maxSum) < price) {
            platipotom_link.addClass('b-layout_hide');
        } else {
            platipotom_link.removeClass('b-layout_hide');
        }
    }
}

window.addEvent('domready', function() {
    var $arQuantity = $('el-total');
    var $arBudget = $('filter_budget_budget');
    var $arSubmitPrice = $('ar-submit-price');
    var $arSaveBtn = $('ar-save-btn');
    var $arDescr = $('el-descr');
    var $arDescrError = $('el-descr-error');
    var $arDescrErrorText = $('el-descr-error-text');

    var $arTotal = $('el-total');
    var $arTotalError = $('el-total-error');
    var $arTotalErrorText = $('el-total-error-text');

    var $arCategoryError = $('el-filter_category-error');
    var $arCategoryErrorText = $('el-filter_category-error-text');

    var platipotom_link = $('quick_payment_autoresponse').getElement('.platipotom_link');
    var platipotom_text = $('quick_payment_autoresponse').getElement('.platipotom_text');
    if (platipotom_link && platipotom_text) {
        platipotom_text.addClass('b-layout_hide');
        platipotom_link.addEvent('mouseover', function(){
            platipotom_text.removeClass('b-layout_hide');
        }).addEvent('mouseout', function(){
            platipotom_text.addClass('b-layout_hide');
        });
    }
    
    autoresponseInitPromo();

    $arSaveBtn && $arSaveBtn.addEvent('click', saveAutoresponse);

    function validateAutoresponse() {
        var ok = true;

        $arDescr.getParent('.b-textarea').removeClass('b-textarea_error');
        $arDescrError.addClass('b-layout_hide');

        var descrLength = $arDescr.get('value').trim().length;
        if (descrLength === 0 || descrLength > 1000) {
            ok = false;
            scrollTo || (scrollTo = $arDescr);
            $arDescrError.removeClass('b-layout_hide');
            $arDescr.getParent('.b-textarea').addClass('b-textarea_error');
            $arDescrErrorText.set('text', descrLength === 0 ? 'Необходимо ввести текст ответа' : 'Текст ответа не должен превышать 1000 символов');
        }

        $arTotal.getParent('.b-combo__input').removeClass('b-combo__input_error');
        $arTotalError.addClass('b-layout_hide');

        var arTotalValue = $arTotal.get('value').trim();
        if (Number(arTotalValue) !== parseInt(Number(arTotalValue)) || arTotalValue < 1) {
            ok = false;
            $arTotalError.removeClass('b-layout_hide');
            $arTotal.getParent('.b-combo__input').addClass('b-combo__input_error');
            $arTotalErrorText.set('text', 'Неверное значение');
        }

        $$('input[name=filter_category_columns[0]]')[0].getParent('.b-combo__input').removeClass('b-combo__input_error');
        $arCategoryError.addClass('b-layout_hide');

        var arCategoryValue = $$('input[name=filter_category_columns[0]]')[0].get('value')[0];
        if (parseInt(Number(arCategoryValue)) < 1) {
            ok = false;
            $arCategoryError.removeClass('b-layout_hide');
            $$('input[name=filter_category_columns[0]]')[0].getParent('.b-combo__input').addClass('b-combo__input_error');
            $arCategoryErrorText.set('text', 'Необходимо выбрать хотя бы одну специализацию');
        }

        return ok;
    }

    function saveAutoresponse(id, sum) {
        if (!validateAutoresponse()) {
            return;
        }

        $('frm').set('target', 'quick_ar_iframe');
        $('frm').submit();
    }

    function autoresponseQuantityChanged() {
        var quantity = $arQuantity.get('value').trim();

        if (quantity !== quantity.replace(/\D*/gi, '')) {
            quantity = quantity.replace(/\D*/gi, '')
            $arQuantity.set('value', quantity);
        }

        // Максимальное количество возможных автоответов для заказа
        if (quantity > 1000) {
            quantity = 1000;
            $arQuantity.set('value', quantity);   
        }

        var price = quantity.toInt() * autoresponse_price;
        $arSubmitPrice.set('html', price);
    }

    if ($arQuantity) {
        $arQuantity.addEvent('change', autoresponseQuantityChanged);
        $arQuantity.addEvent('input', autoresponseQuantityChanged);
        $arQuantity.addEvent('keyup', autoresponseQuantityChanged);
    }

    function autoresponseBudgetChanged() {
        var budget = $arBudget.get('value').trim();

        if (budget !== budget.replace(/\D*/gi, '')) {
            budget = budget.replace(/\D*/gi, '')
            $arBudget.set('value', budget);
        }

        // Максимальное количество возможных автоответов для заказа
        if (budget < 0) {
            budget = 0;
            $arBudget.set('value', budget);   
        }
    }

    if ($arBudget) {
        $arBudget.addEvent('change', autoresponseBudgetChanged);
        $arBudget.addEvent('input', autoresponseBudgetChanged);
        $arBudget.addEvent('keyup', autoresponseBudgetChanged);
    }

});
