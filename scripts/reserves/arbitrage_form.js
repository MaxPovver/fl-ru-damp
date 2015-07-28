/**
 * Класс скриптов для обработки решения арбитра
 * 
 * @type Class
 */
var ArbitrageForm = new Class({
    
    form: null,
    formValidator: null,
    inputPrice: null,
    maxPrice: 0,
    
    init: function()
    {
        var _this = this;
       this.form = $('arbitrage_form');
       if(!this.form) return false;
       
       this.maxPrice = $('arbitrage_sum_emp').get('html');
       
        this.inputPrice = $('arbitrage_sum_frl');
        if(!this.inputPrice) return false;
        
        this.inputPrice.addEvent('keyup', function() {
            _this.checkPrice();
        });
        
       this.form.addEvent('submit', function() {
           _this.submit();
           return false;
       }); 
        
        $('arbitrage_cancel').addEvent('click', function() {
           _this.cancel(); 
        });
        
        
        Locale.use("ru-RU");
        Locale.define(Locale.getCurrent().name, 'FormValidator', {
            required:'Необходимо указать причину обращения в арбитраж.',
            maxLength:'Пожалуйста, введите до {maxLength} символов (Вы ввели {length}).'
        });
        
        this.formValidator = new Form.Validator(this.form, {
            //useTitles: true,
            serial:false,
            onElementPass: function(el) {},
            onElementFail: function(el, validator) {},
            onElementValidate: function(passed, element, validator, is_warn){}
        });
    },
    
    /**
     * Отправить отзыв
     */
    submit: function()
    {
        var is_validate = this.checkPrice() && this.formValidator.validate();
        if(is_validate) {
            xajax_reservesArbitrageApply(xajax.getFormValues(this.formValidator.element));
        }
        return false;
    },
    
    checkPrice: function()
    {
        var value = this.inputPrice.get('value');        
        var newValue = parseInt(value);
        if (isNaN(newValue) || newValue < 0) newValue = 0;
        if (newValue > this.maxPrice) newValue = this.maxPrice;        
        $('arbitrage_sum_emp').set('html', this.maxPrice - newValue);        
        if (newValue != value) {
            this.inputPrice.set('value', newValue);
            return false;
        }
        return true;
    },
    
    /**
     * Отправить отзыв
     */
    cancel: function()
    {
        var order_id = $$('[name=order_id]')[0].get('value');
        xajax_reservesArbitrageCancel(order_id);
        return false;
    }
    
});

window.addEvent('domready', function() {
    window.arbitrage_form = new ArbitrageForm();
    window.arbitrage_form.init();
});