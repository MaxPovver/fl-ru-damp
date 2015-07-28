/**
 * Базовый класс обработки событий попап окна быстрой оплаты.
 * Это развитие quick_payment.js и уход от добавленной там специфики.
 * @todo: Доработки только для расширения общего функционала.
 * 
 * @type Class
 */
var QuickExtPayment = new Class({
 
    //Implements: Animal,
    //Extends: Animal,
    
    popup_name: '',
    popup: null,
    form: null,
    
    wait_screen: null,
    error_screen: null,
    success_screen: null,
    
    price_element: null,
    price_value: null,
    
    paynone_element: null,
    paypart_element: null,
    payfull_element: null,
    
    accsum_element: null,
    partsum_element: null,
    
    accsum_value: null,
    
    payment_list_element: null,
    payment_account_element: null,
    payment_account_price: null,
    
    payment_platipotom_link: null,
    payment_platipotom_text: null,
    
    promo_code_link: null,
    promo_code_input: null,
    promo_code_info: null,
    
    form_elements_error: null,
    
    initialize: function(p)
    {
        if(!p) return false;
        
        var _this = this;
        this.popup = p;
        this.popup_name = p.get('data-quick-ext-payment');
        this.form = p.getElement('form');
        this.wait_screen = p.getElement('[data-quick-payment-wait-screen]');
        this.error_screen = p.getElement('[data-quick-payment-error-screen]');
        this.success_screen = p.getElement('[data-quick-payment-success-screen]');
        this.price_element = p.getElement('[data-quick-payment-price]');
        
        this.paynone_element = p.getElement('[data-quick-payment-paynone]');
        this.paypart_element = p.getElement('[data-quick-payment-paypart]');
        this.payfull_element = p.getElement('[data-quick-payment-payfull]');
        
        this.accsum_element = p.getElement('[data-quick-payment-accsum]');
        this.partsum_element = p.getElement('[data-quick-payment-partsum]');
    
        this.payment_list_element = p.getElements('[data-quick-payment-list]');
        this.payment_account_element = p.getElement('[data-quick-payment-account]');
        this.payment_account_price = p.getElement('[data-quick-payment-account-price]');
        
        this.payment_platipotom_link = p.getElement('.platipotom_link');
        this.payment_platipotom_text = p.getElement('.platipotom_text');
        
        if (this.payment_platipotom_link && this.payment_platipotom_text) {
            this.payment_platipotom_text.addClass('b-layout_hide');
            this.payment_platipotom_link.addEvent('mouseover', function(){
                _this.payment_platipotom_text.removeClass('b-layout_hide');
            }).addEvent('mouseout', function(){
                _this.payment_platipotom_text.addClass('b-layout_hide');
            });
        }
        
        if (this.accsum_element) {
            this.accsum_value = this.accsum_element.get('data-quick-payment-accsum');
        }
        
        if (this.price_element) {
            this.price_value = this.price_element.get('data-quick-payment-price');
        }
        
        var link_types = p.getElements('[data-quick-payment-type]');

        if (link_types.length) {
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
        if (close) {
            close.addEvent('click', function() {
                _this.close_popup();
                return false;
            });
        }
        
        this.promo_code_link = p.getElements('.promo_code_link');
        this.promo_code_input = p.getElement('.promo_code_input');
        this.promo_code_info = p.getElement('.promo_code_info');
        
        if (this.promo_code_link && this.promo_code_input && this.promo_code_info) {
            this.initPromo();
        }        
        
        vertical_center_top();
        
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    process: function(type)
    {
        var data = xajax.getFormValues(this.form);
        return xajax_quickPaymentProcess(this.popup_name,type,data);
    },
    
    
    //--------------------------------------------------------------------------
    
    isWait: function()
    {
        if (!this.wait_screen) {
            return false;
        }
        
        return !this.wait_screen.hasClass('b-layout_hide');
    },
    
    //--------------------------------------------------------------------------
            
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
    
    //--------------------------------------------------------------------------
    
    hide_wait: function()
    {
        if(!this.wait_screen) return false;
        this.wait_screen.getParent().removeClass('b-layout_waiting');
        this.wait_screen.addClass('b-layout_hide');
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    close_popup: function()
    {
        this.hide_wait();
        this.hide_success();
        this.hide_error();
        this.popup.addClass('b-shadow_hide');
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    show_error: function(msg)
    {
        if(!this.error_screen) return false;
        this.hide_wait();
        if(msg.length) 
            this.error_screen
                .getElement('[data-quick-payment-error-msg]')
                .set('html',msg);
        this.error_screen.removeClass('b-layout_hide');
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    hide_error: function()
    {
        this.hideElementsError();
        
        if(!this.error_screen) return false;
        this.error_screen.addClass('b-layout_hide');
        return true;
    },
    
    //--------------------------------------------------------------------------
    
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
    
    //--------------------------------------------------------------------------
    
    hide_success: function()
    {
        if(!this.success_screen) return false;
        this.success_screen.getParent().removeClass('b-layout_waiting');
        this.success_screen.addClass('b-layout_hide');
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    hideElementsError: function()
    {
        if (this.form_elements_error) {
            this.form_elements_error.each(function(e){
                e.block.addClass('b-layout_hide');
                e.text.set('html','');
            });
            
            return true;
        }
        
        return false;
    },
    
    //--------------------------------------------------------------------------
    
    showElementsError: function(params)
    {
        var errors = this.getJsonFromUrl(params);

        if (Object.keys(errors).length) {
           this.form_elements_error = [];
           for (var key in errors) {
               
               if(!$('el-' + key + '-error')) {
                   continue;
               }
               
               var e = {
                    text: $('el-' + key + '-error-text').set('html', errors[key]),
                    block: $('el-' + key + '-error').removeClass('b-layout_hide')
               };
               this.form_elements_error.push(e);
           }
           
           this.hide_wait();
           this.hide_success();
           
           return true;
        }
        
        return false;
    },
    
    //--------------------------------------------------------------------------
    
    getJsonFromUrl: function(query) 
    {
        var result = {};

        query.split("&").forEach(function(part) {
            var item = part.split("=");
            result[item[0]] = item[1];
        });
        
        return result;
    },
            
    //--------------------------------------------------------------------------
            
    sendPaymentForm: function(html)
    {
        var hideDiv = new Element('div')
                .setStyle('display','none')
                .set('html', html)
                .inject(this.popup, 'bottom');

        var form = hideDiv.getElement('form');
        
        if (form) {
            form.submit();
        }
    },
    
    //--------------------------------------------------------------------------            
    
    setPrice: function(price)
    {
        if(price < 0) return false;
        
        this.price_element.set('data-price', price);
        
        if (this.promo_code_info) {
            this.applyPromo();
        } else {
            this.changePrice(price);
        }
    },
    
    //--------------------------------------------------------------------------            
    
    changePrice: function(price)
    {
        if(price < 0) return false;
        
        var priceFormat = this.view_cost_format(price);
        this.price_element.set('text', priceFormat);

        var pay_sum = price - this.accsum_value;
        pay_sum = Math.ceil( (pay_sum > 0 && pay_sum < 10)?10:pay_sum );
            
        if (this.accsum_value > 0) {
            
            this.payment_account_price.set('text', priceFormat);
            
            if (pay_sum > 0) {
                this.paynone_element.addClass("b-layout__txt_hide");
                this.paypart_element.removeClass("b-layout__txt_hide");
                this.partsum_element.set('text', this.view_cost_format(pay_sum));
                
                this.payment_account_element.addClass("b-layout_hide");
                this.payment_list_element.removeClass("b-layout_hide");
            } else {
                this.paypart_element.addClass("b-layout__txt_hide");
                this.paynone_element.removeClass("b-layout__txt_hide");
                
                this.payment_list_element.addClass("b-layout_hide");
                this.payment_account_element.removeClass("b-layout_hide");
            }
        }
        
        this.checkPaymentTypes(pay_sum);
        
        return true;
    },
    
    //--------------------------------------------------------------------------    
    
    view_cost_format: function(price)
    {
        return this.number_format(price, 2, ',', ' ').replace(',00', ''); 
    },
    
    //--------------------------------------------------------------------------
    
    /**
     * Форматирование числа
     * Format a number with grouped thousands
     * 
     * @param {type} number
     * @param {type} decimals
     * @param {type} dec_point
     * @param {type} thousands_sep
     * @returns {Number|String}
     */
    number_format: function(number, decimals, dec_point, thousands_sep) 
    {	
        // 
        // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +	 bugfix by: Michael White (http://crestidg.com)

        var i, j, kw, kd, km;

        // input sanitation & defaults
        if (isNaN(decimals = Math.abs(decimals))) {
            decimals = 2;
        }
        if (dec_point == undefined) {
            dec_point = ",";
        }
        if (thousands_sep == undefined) {
            thousands_sep = ".";
        }

        i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

        if ((j = i.length) > 3) {
            j = j % 3;
        } else {
            j = 0;
        }

        km = (j ? i.substr(0, j) + thousands_sep : "");
        kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
        //kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).slice(2) : "");
        kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");

        return km + kw + kd;
    },        
            
    
    //--------------------------------------------------------------------------

    /**
     * Проверяет допустимость оплаты через платежные системы
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
    },
    
    
    //--------------------------------------------------------------------------

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
                    'ext'
                );
            }
        });
    },
    
    
    //--------------------------------------------------------------------------
    
    
    applyPromo: function() 
    {
        var price = parseInt(this.price_element.get('data-price'));
        if (isNaN(price)) {
            price = parseInt(this.price_element.get('data-quick-payment-price'));
        }

        var discount = parseInt(this.promo_code_info.get('data-discount-price'));
        if (discount > 0) {
            this.promo_code_info.set('text', "Скидка " + this.number_format(discount) + " руб.");
            var newPrice = price - discount;
            if (newPrice < 0) newPrice = 0;
            this.changePrice(newPrice);
        } else {
            discount = parseInt(this.promo_code_info.get('data-discount-percent'));
            var priceDiscount = price * discount / 100;
            if (priceDiscount > 0) {
                this.promo_code_info.set('text', "Скидка " + this.number_format(priceDiscount) + " руб.");
            }
            newPrice = price - priceDiscount;
            this.changePrice(newPrice);
        }
    }
    
    
    
    
    
    
    
    /*
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
    } */
    
});


var QuickExtPaymentFactory = new Class({
    
    initialize: function()
    {
        var popups = $$('[data-quick-ext-payment]');
        
        if (!popups) {
            return false;
        }
        
        window.quick_ext_payments = {};
        popups.each(function(p){
            var name = p.get('data-quick-ext-payment');
            var class_name = name + '_QuickExtPayment';
            if (typeof window[class_name] !== "undefined") {
                window.quick_ext_payments[name] = new window[class_name](p);
            }
        });
    },
     
    setQuickPayment: function(name)
    {
        var popup = $$('[data-quick-ext-payment="'+name+'"]').getLast();
        
        if (!popup) {
            return false;
        }
        
        var class_name = name + '_QuickExtPayment';
        if (typeof window[class_name] !== "undefined") {
            window.quick_ext_payments[name] = new window[class_name](popup);
        }
    },        
            
    getQuickPayment: function(name)
    {
        return (typeof window.quick_ext_payments[name] !== "undefined")?window.quick_ext_payments[name]:false;
    }
});


window.addEvent('domready', function() {
    window.quick_ext_payment_factory = new QuickExtPaymentFactory();
});