/**
 * Класс скриптов для обработки попапа отзыва для заказа
 * 
 * @type Class
 */
var OrderFeedback = new Class({
    
    //Implements: Animal,
    //Extends: Animal,
    
    popup: null,
    form: null,
    formValidator: null,
    is_set_fbtype: false,
    only_close: true,
    
    BTN_TXT_ONLY_CLOSE: 'Закрыть заказ',
    BTN_TXT_WITH_FEEDBACK: 'Оставить отзыв и закрыть заказ',
    
    FEEDBACK_SUBMIT_LABEL: '.__tservices_orders_feedback_submit_label',
    
    submit_button: null,
    wait_screen: null,
    feedback: null,
    
    initialize: function(p)
    {
        if(!p) return false;
        
        var _this = this;
        this.popup = p;
        this.form = p.getElement('form');
        if(!this.form) return false;

        this.feedback = this.form.getElement('[name="feedback"]');
        if(!this.feedback) { 
            return false;
        }
        
        this.wait_screen = p.getElement('[data-popup-wait-screen]');
        var close = p.getElements('[data-popup-close]');
        if (close) {
            close.addEvent('click', function() {
                _this.close_popup();
                return false;
            });
        }     

        var submit_label = p.getElement(this.FEEDBACK_SUBMIT_LABEL);
        
        if(!this.feedback.get('data-order-feedback-is-close') && submit_label)
        {
            var feedbackTouch = function(){
                if((this.get('value').length > 0 && !_this.only_close) || 
                   (this.get('value').length == 0 && _this.only_close)) return true;
        
                _this.only_close = !_this.only_close;
                submit_label.set('html',(_this.only_close)?_this.BTN_TXT_ONLY_CLOSE:_this.BTN_TXT_WITH_FEEDBACK);

                return true;
            };
            this.feedback.addEvents({keyup:feedbackTouch,mouseup:feedbackTouch});
        }
        
        
        Locale.use("ru-RU");
        Locale.define(Locale.getCurrent().name, 'FormValidator', {});
        
        this.formValidator = new Form.Validator(this.form, {
            //useTitles: true,
            serial:false,
            onElementPass: function(el) {},
            onElementFail: function(el, validator) {},
            onElementValidate: function(passed, element, validator, is_warn){}
        });        
        
        this.formValidator.add('fbtype',{
            errorMsg:Form.Validator.getMsg.pass('required'),
            test: function(element, props){
                var is_feedback = (_this.feedback.get('value').length > 0);
                _this.is_set_fbtype = _this.is_set_fbtype || element.get('checked');
                if(is_feedback) return _this.is_set_fbtype;
                return true;
            }        
        });
        
        this.submit_button = p.getElement('[data-order-feedback-submit]');
        if (this.submit_button) { 
            this.submit_button.addEvent('click', function(){
                _this.submit();
                return false;
            });
        }
        
        var close_link = p.getElement('[data-order-feedback-close]');
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
            this.show_wait('');
            xajax_tservicesOrdersNewFeedback(xajax.getFormValues(this.formValidator.element));
            this.close_popup();
        }
        return is_validate;
    },
    
    /**
     * Кнопка отправки не активна
     */
    disableSubmit: function() 
    {
        if (this.submit_button) {
            this.submit_button.addClass('b-button_disabled');
            return true;
        }
        
        return false;
    },
    
    /**
     * Кнопка отпрауи активна
     */
    enableSubmit: function() 
    {
        if (this.submit_button) {
            this.submit_button.removeClass('b-button_disabled');
            return true;
        }
        
        return false;
    },
    
    
    
    /**
     * Закрыть папап
     */
    close_popup: function()
    {
        this.popup.addClass('b-shadow_hide');
        this.hide_wait();
        this.feedback.set('value','').fireEvent('keyup');
        return true;
    },
    
    /**
     * Закрыть все открытые попапы панисания отзывов.
     * В основно для закрытия последнего.
     */        
    close_all_popup: function()
    {
        if(typeof window.order_feedbacks === "undefined") return false;
        var this_id = this.popup.get('id');
        
        for(var id in window.order_feedbacks)
        {
            var order_feedback = window.order_feedbacks[id];
            if(order_feedback.popup.hasClass('b-shadow_hide') || this_id === id) continue;
            order_feedback.close_popup();
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
    },
    
            
    //--------------------------------------------------------------------------
    
    
    /**
     * Показать процесс загрузки
     */
    show_wait: function(msg)
    {
        this.disableSubmit();
        
        if (!this.wait_screen) {
            return false;
        }

        if (msg != 'true') { 
            this.wait_screen
                .getElement('[data-popup-wait-msg]')
                .set('html',msg);
        }
        
        this.wait_screen.getParent().addClass('b-layout_waiting');
        this.wait_screen.removeClass('b-layout_hide');
        
        return true;
    },        
    
    //--------------------------------------------------------------------------
    
    /**
     * Скрыть процесс загрузки
     */
    hide_wait: function()
    {
        this.enableSubmit();
        
        if (!this.wait_screen) {
            return false;
        }
        
        this.wait_screen.getParent().removeClass('b-layout_waiting');
        this.wait_screen.addClass('b-layout_hide');
        
        return true;
    }            
});

/**
 * Класс фабика поиска и инициализация обьектов попапов отзывов
 * 
 * @type Class
 */
var OrderFeedbackFactory = new Class({
    
    initialize: function()
    {
        var order_feedback_popups = $$('[data-order-feedback]');
        if(!order_feedback_popups) return false;
        window.order_feedbacks = {};
        order_feedback_popups.each(function(p){
            var id = p.get('id');
            window.order_feedbacks[id] = new OrderFeedback(p);
            //Callback на клик по тому что открывает попап
            var link = p.retrieve('called_link');
            if(link) link.addEvent('click',function(){
                return window.order_feedbacks[id].on_open_popup();
            });
        });
        
        window.fireEvent('resize');
    },
       
    /**
     * Получить обьект попапа отзыва по его ID
     */
    getOrderFeedback: function(id)
    {
        return (typeof window.order_feedback[id] !== "undefined")?window.order_feedback[id]:false;
    }
});

window.addEvent('domready', function() {
    window.order_feedback_factory = new OrderFeedbackFactory();
});