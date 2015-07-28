/**
 *  ласс обработки событий попап окна быстрой оплаты
 * 
 * @todo: это общий класс здесь недолжно быть никакой специфики, но теперь уже позно класс испорчен
 * см quick_ext_payment.js это обновленна€ верси€ от которой нужно наследоватьс€
 * 
 * @type Class
 */
var QuickPayment = new Class({
 
    //Implements: Animal,
    //Extends: Animal,
    
    popup_name: '',
    popup: null,
    form: null,
    wait_screen: null,
    error_screen: null,
    success_screen: null,
    
    promo_code_link: null,
    promo_code_input: null,
    promo_code_info: null,    
    
    initialize: function(p)
    {
        if(!p) return false;
        
        var _this = this;
        this.popup = p;
        this.popup_name = p.get('data-quick-payment');
        this.form = p.getElement('form');
        this.wait_screen = p.getElement('[data-quick-payment-wait-screen]');
        this.error_screen = p.getElement('[data-quick-payment-error-screen]');
        this.success_screen = p.getElement('[data-quick-payment-success-screen]');

        this.promo_code_link = p.getElements('.promo_code_link');
        this.promo_code_input = p.getElement('.promo_code_input');
        this.promo_code_info = p.getElement('.promo_code_info');
        
        if (this.popup_name == 'frlbind') {
            this.prepareFrlbind();
        } else if (this.popup_name == 'frlbindup') {
            this.prepareFrlbindup();
        } else if (p.hasClass('quick_payment_tservicebind')) {
            this.prepareTservicebind();
        } else if (p.hasClass('quick_payment_tservicebindup')) {
            this.prepareTservicebindup();
        } else if (this.popup_name == 'account') {
            this.prepareAccount();
        } else if (this.popup_name == 'masssending') {
            this.prepareMasssending();
        }
        
        var link_types = p.getElements('[data-quick-payment-type]');
        
        if(link_types.length)
        {
            link_types.addEvent('click', function() {
                var type = this.get('data-quick-payment-type');
                if (!type) return false;
                var wait_msg = this.get('data-quick-payment-wait');
                if(wait_msg) _this.show_wait(wait_msg);
                _this.process(type);
                return false;
            });
        }
        
        var close = p.getElements('[data-quick-payment-close]');
        if (close)
        {
            close.addEvent('click', function() {
                _this.close_popup();
                return false;
            });
        }
        
        var platipotom_link = this.popup.getElement('.platipotom_link');
        var platipotom_text = this.popup.getElement('.platipotom_text');
        if (platipotom_link && platipotom_text) {
            platipotom_text.addClass('b-layout_hide');
            platipotom_link.addEvent('mouseover', function(){
                platipotom_text.removeClass('b-layout_hide');
            }).addEvent('mouseout', function(){
                platipotom_text.addClass('b-layout_hide');
            });
        }
        
        if (this.promo_code_link && this.promo_code_input && this.promo_code_info) {
            this.initPromo();
        }
        
        vertical_center_top();
    },
    
    process: function(type)
    {
        var pay_account_btn = this.popup.getElement('[data-quick-payment-type="account"]');
        if (pay_account_btn) {
            pay_account_btn.addClass('b-button_disabled');
        }
        var data = xajax.getFormValues(this.form);
        return xajax_quickPaymentProcess(this.popup_name,type,data);
    },
            
    show_wait: function(msg)
    {
        if(!this.wait_screen) return false;
        this.hide_error();
        if(msg != 'true') 
            this.wait_screen
                .getElement('[data-quick-payment-wait-msg]')
                .set('html',msg);
        this.wait_screen.getParent().addClass('b-layout_waiting');
        this.wait_screen.removeClass('b-layout_hide');
        return true;
    },        
    
    hide_wait: function()
    {
        if(!this.wait_screen) return false;
        this.wait_screen.getParent().removeClass('b-layout_waiting');
        this.wait_screen.addClass('b-layout_hide');
        return true;
    },
            
    close_popup: function()
    {
        this.hide_wait();
        this.hide_success();
        this.popup.addClass('b-shadow_hide');
        return true;
    },
    
    show_error: function(msg)
    {
        if(!this.error_screen) return false;
        this.hide_wait();
        if(msg.length) 
            this.error_screen
                .getElement('[data-quick-payment-error-msg]')
                .set('html',msg);
        this.error_screen.removeClass('b-layout_hide');
        
        var pay_account_btn = this.popup.getElement('[data-quick-payment-type="account"]');
        if (pay_account_btn) {
            pay_account_btn.removeClass('b-button_disabled');
        }
        return true;
    },
    
    hide_error: function()
    {
        if(!this.error_screen) return false;
        this.error_screen.addClass('b-layout_hide');
        return true;
    },
    
    show_success: function(msg)
    {
        if(!this.success_screen) return false;
        this.hide_wait();
        if(msg.length) 
            this.success_screen
                .getElement('[data-quick-payment-success-msg]')
                .set('html',msg);
        this.success_screen.getParent().addClass('b-layout_waiting');
        this.success_screen.removeClass('b-layout_hide');
        return true;
    },
    
    hide_success: function()
    {
        if(!this.success_screen) return false;
        this.success_screen.getParent().removeClass('b-layout_waiting');
        this.success_screen.addClass('b-layout_hide');
        return true;
    },
    
    initPromo: function()
    {
        this.promo_code_link.addEvent('click', function() {
            this.getParent().getNext().removeClass('b-layout_hide');
            this.getParent().addClass('b-layout_hide');
            return false;
        });
        
        var promoInput = new Element('input', {'type': 'hidden', 'name': 'promo'});
        promoInput.inject(this.form);
        var promo_service = this.promo_code_input.get('data-service');
        var _this = this;
        var promo_old_value;
        this.promo_code_input.addEvent('keydown', function() {
            promo_old_value = this.get('value');
        }).addEvent('keyup', function() {
            var value = this.get('value');
            if (promo_old_value != value) {
                promoInput.set('value', value);
                return xajax_checkPromoCode(
                    _this.popup_name,
                    value,
                    promo_service,
                    _this.popup.get('id')
                );
            }
        });
    },
    
    applyPromo: function() 
    {
        var price = parseInt(this.popup.getElement('.quick_sum_pay').get('data-sum'));
       
        if (typeof this.promo_code_info == 'undefined' || !this.promo_code_info) {
            this.updatePayButtons(price);
        } else {
            var discount = parseInt(this.promo_code_info.get('data-discount-price'));
            if (isNaN(discount)) {
                this.updatePayButtons(price);
            } else if (discount > 0) {
                this.promo_code_info.set('text', "—кидка " + discount + " руб.");
                var newPrice = price - discount;
                if (newPrice < 0) newPrice = 0;
                this.updatePayButtons(newPrice);
            } else {
                discount = parseInt(this.promo_code_info.get('data-discount-percent'));
                var priceDiscount = price * discount / 100;
                if (priceDiscount > 0) {
                    this.promo_code_info.set('text', "—кидка " + priceDiscount + " руб.");
                }
                newPrice = price - priceDiscount;
                this.updatePayButtons(newPrice);
            }
        }
    },
    
    prepareFrlbind: function()
    {
        this.blockPayNone = this.popup.getElements('.pay_none')[0];
        this.blockPayPart = this.popup.getElements('.pay_part')[0];
        this.blockPayFull = this.popup.getElements('.pay_full')[0];
        this.ac_sum = parseFloat(this.popup.getElements('.ac_sum')[0].get('text'));
        
        var maxWeeks = 99;
        var price = parseInt($('frlbind_ammount').get('value'));
        var stop_date = $('frlbind_date').get('text');
        
        var weekInput = $('input-weeks');
        var _this = this;
        weekInput.addEvent('change', function() {
            var weeks = parseInt(weekInput.get('value'));
            if (weeks != weekInput.get('value') || isNaN(weeks) || weeks < 1) {
                weeks = 1;
                weekInput.set('value', weeks).fireEvent('change');
            } else if (weeks > maxWeeks) {
                weeks = maxWeeks;
                weekInput.set('value', weeks).fireEvent('change');
            }
            
            $('frlbind_weeks').set('text', getSuffix(weeks, 'недел', '€', 'и', 'ь'));

            var endDate = new Date();
            
            if (stop_date !== '') {
                endDate.setTime(Date.parse(stop_date));
            }

            var numberOfDaysToAdd = weeks * 7;
            endDate.setDate(endDate.getDate() + numberOfDaysToAdd);

            var d = endDate.getDate();
            if (d < 10)
                d = '0' + d;
            var m = endDate.getMonth() + 1;
            if (m < 10)
                m = '0' + m;
            var y = endDate.getFullYear();
            $('frlbind_date').set('text', d + '.' + m + '.' + y);

            
            var sum = weeks * price;
            _this.onChangeSum(sum);
        });
        weekInput.set('value', 1).fireEvent('change');

        var more = this.popup.getElements('.b-button_poll_plus');
        more.addEvent('click', function() {
            var weeks = parseInt(weekInput.get('value'));
            if (weeks < maxWeeks) {
                weekInput.set('value', weeks + 1).fireEvent('change');
            }
        });

        var less = this.popup.getElements('.b-button_poll_minus');
        less.addEvent('click', function() {
            var weeks = parseInt(weekInput.get('value'));
            if (weeks > 1) {
                weekInput.set('value', weeks - 1).fireEvent('change');
            }
        });
    },
    
    prepareFrlbindup: function() {
        this.blockPayNone = this.popup.getElements('.pay_none')[0];
        this.blockPayPart = this.popup.getElements('.pay_part')[0];
        this.blockPayFull = this.popup.getElements('.pay_full')[0];
        this.ac_sum = parseFloat(this.popup.getElements('.ac_sum')[0].get('text'));
        
        var sum = parseInt(this.popup.getElements('.quick_sum_pay')[0].get('text'));
        
        var buffer = parseInt($('buffer_sum').get('text'));
        
        if (buffer >= sum) {
           this.popup.getElements('.payments')[0].addClass("b-layout_hide");
           this.popup.getElements('.payment_account')[0].addClass("b-layout_hide"); 
           this.blockPayNone.addClass("b-layout_hide");
           this.blockPayPart.addClass("b-layout_hide");
           this.blockPayFull.addClass("b-layout_hide");
           $('pay_buffer').removeClass('b-layout_hide');
           this.popup.getElements('.payment_buffer')[0].removeClass('b-layout_hide');
        } else {
           $('pay_buffer').addClass('b-layout_hide');
           this.popup.getElements('.payment_buffer')[0].addClass('b-layout_hide');
           this.onChangeSum(sum);
        }
    },
    
    prepareTservicebind: function()
    {
        this.popup.inject(document.body, 'bottom');
        
        this.form.getElements('.input-redirect')[0].set('value', window.location.href);
        this.blockPayNone = this.popup.getElements('.pay_none')[0];
        this.blockPayPart = this.popup.getElements('.pay_part')[0];
        this.blockPayFull = this.popup.getElements('.pay_full')[0];
        this.ac_sum = parseFloat(this.popup.getElements('.ac_sum')[0].get('text'));
        
        var dateBlock = this.popup.getElements('.tservicebind_date')[0];
        
        var maxWeeks = 99;
        var price = parseInt(this.popup.getElements('.input-ammount').get('value'));
        var stop_date = dateBlock.get('text');
        
        var weekInput = this.popup.getElements('.input-weeks')[0];
        var weeksText = this.popup.getElements('.tservicebind_weeks')[0];
        var _this = this;
        weekInput.addEvent('change', function() {
            var weeks = parseInt(weekInput.get('value'));
            if (weeks != weekInput.get('value') || isNaN(weeks) || weeks < 1) {
                weeks = 1;
                weekInput.set('value', weeks).fireEvent('change');
            } else if (weeks > maxWeeks) {
                weeks = maxWeeks;
                weekInput.set('value', weeks).fireEvent('change');
            }
            
            weeksText.set('text', getSuffix(weeks, 'недел', '€', 'и', 'ь'));

            var endDate = new Date();
            
            if (stop_date !== '') {
                endDate.setTime(Date.parse(stop_date));
            }

            var numberOfDaysToAdd = weeks * 7;
            endDate.setDate(endDate.getDate() + numberOfDaysToAdd);

            var d = endDate.getDate();
            if (d < 10)
                d = '0' + d;
            var m = endDate.getMonth() + 1;
            if (m < 10)
                m = '0' + m;
            var y = endDate.getFullYear();
            dateBlock.set('text', d + '.' + m + '.' + y);

            
            var sum = weeks * price;
            _this.onChangeSum(sum);
        });
        weekInput.set('value', 1).fireEvent('change');

        var more = this.popup.getElements('.b-button_poll_plus');
        more.addEvent('click', function() {
            var weeks = parseInt(weekInput.get('value'));
            if (weeks < maxWeeks) {
                weekInput.set('value', weeks + 1).fireEvent('change');
            }
        });

        var less = this.popup.getElements('.b-button_poll_minus');
        less.addEvent('click', function() {
            var weeks = parseInt(weekInput.get('value'));
            if (weeks > 1) {
                weekInput.set('value', weeks - 1).fireEvent('change');
            }
        });

    },
    
    prepareTservicebindup: function() {
        this.blockPayNone = this.popup.getElements('.pay_none')[0];
        this.blockPayPart = this.popup.getElements('.pay_part')[0];
        this.blockPayFull = this.popup.getElements('.pay_full')[0];
        this.ac_sum = parseFloat(this.popup.getElements('.ac_sum')[0].get('text'));
        
        var sum = parseInt(this.popup.getElements('.quick_sum_pay')[0].get('text'));
        
        var buffer = parseInt($('buffer_sum').get('text'));
        
        if (buffer >= sum) {
           this.popup.getElements('.payments')[0].addClass("b-layout_hide");
           this.popup.getElements('.payment_account')[0].addClass("b-layout_hide"); 
           this.blockPayNone.addClass("b-layout_hide");
           this.blockPayPart.addClass("b-layout_hide");
           this.blockPayFull.addClass("b-layout_hide");
           $('pay_buffer').removeClass('b-layout_hide');
           this.popup.getElements('.payment_buffer')[0].removeClass('b-layout_hide');
        } else {
           $('pay_buffer').addClass('b-layout_hide');
           this.popup.getElements('.payment_buffer')[0].addClass('b-layout_hide');
           this.onChangeSum(sum);
        }
    },
    
    prepareAccount: function() {
        var priceInput = $('account_price');
        var minLimit = priceInput.get('data-minimum');
        var maxLimit = priceInput.get('data-maximum');
        var _this = this;
        var old;
        priceInput.addEvent('keydown', function(){
            old = priceInput.get('value');
        }).addEvent('keyup', function(){
            var price = parseInt(priceInput.get('value'));
            if (price && price !== old) {
                if (isNaN(price)) price = minLimit;
                if (price > maxLimit) price = maxLimit;
                if (price !== priceInput.get('value')) {
                    priceInput.set('value', price);
                }
                _this.checkPaymentTypes(price);
            }
        }).addEvent('change', function(){
            var price = parseInt(priceInput.get('value'));
            if (isNaN(price) || price < minLimit) price = minLimit;
            if (price !== priceInput.get('value')) {
                priceInput.set('value', price);
            }
            _this.checkPaymentTypes(price);
        });
    },
    
    prepareMasssending: function() {
        this.blockPayNone = this.popup.getElements('.pay_none')[0];
        this.blockPayPart = this.popup.getElements('.pay_part')[0];
        this.blockPayFull = this.popup.getElements('.pay_full')[0];
        this.ac_sum = parseFloat(this.popup.getElements('.ac_sum')[0].get('text'));
        var sum = parseInt(this.popup.getElements('.quick_sum_pay')[0].get('text'));
        this.onChangeSum(sum);
    },
    
    updatePayButtons: function($sum)
    {
        var $pay_sum = $sum - this.ac_sum;
        
        if ($pay_sum > 0) {
            if ($pay_sum < 10) $pay_sum = 10;
            this.popup.getElements('.payments')[0].removeClass("b-layout_hide");
            this.popup.getElements('.payment_account')[0].addClass("b-layout_hide");
            this.blockPayNone.addClass("b-layout_hide");
            if (this.ac_sum > 0) {
                this.blockPayPart.removeClass("b-layout_hide");
                this.blockPayFull.addClass("b-layout_hide");
            } else {
                this.blockPayPart.addClass("b-layout_hide");
                this.blockPayFull.removeClass("b-layout_hide");
            }
            this.checkPaymentTypes($pay_sum);
        } else {
            this.blockPayNone.removeClass("b-layout_hide");
            this.blockPayPart.addClass("b-layout_hide");
            this.blockPayFull.addClass("b-layout_hide");
            this.popup.getElements('.payments')[0].addClass("b-layout_hide");
            this.popup.getElements('.payment_account')[0].removeClass("b-layout_hide");
        }
        this.popup.getElements('.quick_sum_pay')[0].set('html', $sum);
        this.popup.getElements('.quick_sum_part')[0].set('html', Math.ceil($pay_sum));
        this.popup.getElements('.quick_sum_pay_acc')[0].set('html', $sum);
    },
    
    onChangeSum: function($sum)
    {
        this.popup.getElements('.quick_sum_pay')[0].set('data-sum', $sum);
        this.applyPromo();
    },
    
    /**
     * ѕровер€ет допустимость оплаты через платежные системы
     */
    checkPaymentTypes: function(price)
    {
        
        var limitedPaymentTypes = this.popup.getElements('[data-maxprice]');
        limitedPaymentTypes.each(function(el){
            var maxSum = parseInt(el.get('data-maxprice'));
            if (parseInt(maxSum) < price) {
                el.addClass('b-layout_hide');
            } else {
                el.removeClass('b-layout_hide');
            }
        });
    }
});


var QuickPaymentFactory = new Class({
    initialize: function()
    {
        var popups = $$('[data-quick-payment]');
        if(!popups) return false;
        window.quick_payments = {};
        popups.each(function(p){
            var name = p.get('data-quick-payment');
            
            if (typeof window.quick_payments[name] == "undefined") {
                window.quick_payments[name] = new Object();
            }
            
            var id = p.get('id');
            window.quick_payments[name][id] = new QuickPayment(p);
        });
    },
            
    getQuickPayment: function(name)
    {
        var popup = false;
        var popupGroup = (typeof window.quick_payments[name] !== "undefined")?window.quick_payments[name]:false;
        if (popupGroup) {
            for (var key in popupGroup) {
                popup = popupGroup[key];
                if(typeof(popup) !== 'function') {
                    break;
                }
            }
        }
        return popup;
    },
    
    getQuickPaymentById: function(name, id)
    {
        return (typeof window.quick_payments[name] !== "undefined" 
            && typeof window.quick_payments[name][id] !== "undefined")?window.quick_payments[name][id]:false;
    }
});


window.addEvent('domready', function() {
    window.quick_payment_factory = new QuickPaymentFactory();
});