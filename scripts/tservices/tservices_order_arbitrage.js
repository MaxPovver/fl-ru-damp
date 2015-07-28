/**
 * Класс скриптов для обработки попапа обращения в арбитраж
 * 
 * @type Class
 */

/* @todo Переименовать в ReservesArbitrage, перенести в /scripts/reserves/  */
var OrderArbitrage = new Class({
    
    popup: null,
    form: null,
    formValidator: null,
    
    initialize: function(p)
    {
      
        if(!p) return false;
        
        var _this = this;
        this.popup = p;
        this.form = p.getElement('form');
        if(!this.form) return false;

        var message = this.form.getElement('[name="message"]');
        if(!message) return false;
        
        Locale.use("ru-RU");
        Locale.define(Locale.getCurrent().name, 'FormValidator', {
            required:'Необходимо указать причину обращения в арбитраж.',
            maxLength:'Пожалуйста, введите до {maxLength} символов (Вы ввели {length}).'
        });
        
        this.formValidator = new Form.Validator(this.form, {
            //useTitles: true,
            serial:false,
            onElementPass: function(el) {
                if(el.hasClass('ignoreValidation')) return false;
                
                if(el.type === 'textarea') el.getParent().removeClass('b-textarea_error');
                
                var error_id = $('error_' + el.get('id'));
                if(!error_id) return;
                error_id.addClass('b-shadow_hide');
            },
            onElementFail: function(el, validator) {
                var elid = el.get('id');
                var error_txt = $('error_txt_' + elid);
                
                if(error_txt)
                {
                    error_txt.set('html',this.getValidator(validator[0]).getError(el));
                    $('error_' + elid).removeClass('b-shadow_hide');
					el.getParent('.b-textarea').addClass('b-textarea_error');
                }
            },
            onElementValidate: function(passed, element, validator, is_warn){}
        });        
        
        //Клик на сообщение об ошибке - убираем ошибку
        $$(".error-message").addEvent("click", function(){
            this.addClass('b-shadow_hide');
            var input = $(this.id.replace("error_", ""));
            if(!input) return;
            var error_css = 'b-combo__input_error';
            if(input.type === 'textarea') error_css = 'b-textarea_error';
            input.getParent().removeClass(error_css);
            this.getParent().removeClass('b-textarea_error');
        }).setStyle("cursor", "pointer");
        
        
        //Клик в поле ввода с ошибкой - убираем ошибку
        $$('.b-combo__input-text, .b-textarea__textarea').addEvent('focus',function(){
            if(this.type === 'textarea') this.getParent().removeClass('b-textarea_error');
            var error_id = $('error_' + this.get('id'));
            if(!error_id) return;
            error_id.addClass('b-shadow_hide');
        });
        
        var submit_link = p.getElement('[data-order-arbitrage-submit]');
        if(submit_link) submit_link.addEvent('click', function(){
            _this.submit();
            return false;
        });
        
        var close_link = p.getElement('[data-order-arbitrage-close]');
        if(close_link) close_link.addEvent('click', function(){
            _this.close_popup();
            return false;
        });
    },
    
    /**
     * Отправить отзыв
     */
    submit: function()
    {
        var is_validate = this.formValidator.validate();
        if(is_validate) {
            xajax_reservesArbitrageNew(xajax.getFormValues(this.formValidator.element));
            //this.close_popup();
        }
        return is_validate;
    },
    
    /**
     * Закрыть папап
     */
    close_popup: function()
    {
        this.popup.addClass('b-shadow_hide');
        return true;
    },
    
    /**
     * Закрыть все открытые попапы панисания отзывов.
     * В основно для закрытия последнего.
     */        
    close_all_popup: function()
    {
        if(typeof window.order_arbitrage === "undefined") return false;
        var this_id = this.popup.get('id');
        
        for(var id in window.order_arbitrage)
        {
            var order_arbitrage = window.order_arbitrage[id];
            if(order_arbitrage.popup.hasClass('b-shadow_hide') || this_id === id) continue;
            order_arbitrage.close_popup();
        }

        return true;
    },
            
    /**
     * Обработчик события при нажатии на ссылке
     * открытия попапа
     */        
    on_open_popup: function()
    {
        this.close_all_popup();
        return false;
    }        
            
});

/**
 * Класс фабика поиска и инициализация обьектов попапов отзывов
 * 
 * @type Class
 */
var OrderArbitrageFactory = new Class({
    
    initialize: function()
    {
        var order_arbitrage_popups = $$('[data-order-arbitrage]');
        if(!order_arbitrage_popups) return false;
        window.order_arbitrage = {};
        order_arbitrage_popups.each(function(p){
            var id = p.get('id');
            window.order_arbitrage[id] = new OrderArbitrage(p);
            //Callback на клик по тому что открывает попап
            var link = p.retrieve('called_link');
            if(link) link.addEvent('click',function(){
                return window.order_arbitrage[id].on_open_popup();
            });
        });
    },
       
    /**
     * Получить обьект попапа отзыва по его ID
     */
    getOrderArbitrage: function(id)
    {
        return (typeof window.order_arbitrage[id] !== "undefined")?window.order_arbitrage[id]:false;
    }
});

window.addEvent('domready', function() {
    window.order_arbitrage_factory = new OrderArbitrageFactory();
});