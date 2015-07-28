$extend(Selectors.Pseudo, {
    visible: function() {
        if (this.getStyle('visibility') != 'hidden' && this.isVisible() && this.isDisplayed()) {
            return this;
        }
    }
});

function TServices_Order_Messages()
{
    TServices_Order_Messages=this; // ie ругался без этого, пока не понял.
    
    //--------------------------------------------------------------------------
    
    var is_debug = false;
    
    var block = $('form-block');
    
    var form,
        btnSend, 
        message, 
        orderInput, 
        formValidator;

    var is_sending = 0;
    var orderid = 0;
    
    //--------------------------------------------------------------------------
    

    
    //--------------------------------------------------------------------------
    
    //Начальная инициализация
    this.init = function() 
    {
        if (block) {
            form = block.getElement('form#message-form');
        }
        
        if (!form) return this.log('Not found some elements.','error');
        
        btnSend = form.getElement('a.b-button_flat_green');
        message = form.getElement('textarea');
        orderInput = form.getElement('[name=orderid]');
    
        attachedFiles.init('attachedfiles', 
            TU_ORDER_MSG_SESS,
            new Array(), 
            TU_ORDER_MSG_MAX_FILES,
            TU_ORDER_MSG_MAX_FILE_SIZE,
            TU_ORDER_MSG_EXT,
            'tservice_message',
            TU_ORDER_MSG_KEY
        );
        
        btnSend.addEvent('click', function(event){
            TServices_Order_Messages.submit();
            return false;
        });
        
        if (orderInput) {
            orderid = orderInput.value;
        }
        
        message.addEvent('keydown', function(event){
            if (event.key == 'enter' && event.control) {
                TServices_Order_Messages.submit();
            }
        });
        
        Locale.use("ru-RU");
        Locale.define(Locale.getCurrent().name, 'FormValidator', {});

        formValidator = new Form.Validator(form, {
            //useTitles: true,
            serial:false,
            onElementPass: function(el) {},
            onElementFail: function(el, validator) {},
            onElementValidate: function(passed, element, validator, is_warn){}
        });
        
        if (block.hasClass('autoscroll')) {
            var myFx = new Fx.Scroll(window, {
                duration: 300,
                wait: false,
                offset: {
                    x: 0,
                    y: -80
                }
            }).toElement('form-block');
            message.focus();
        }
        
    };
    
    //--------------------------------------------------------------------------
    
    
    this.submit = function()
    {
        var is_validate = formValidator.validate() || attachedFiles.count > 0;
        if ( is_validate && !is_sending ) { 
            is_sending = 1;
            btnSend.addClass("b-button_disabled");
            btnSend.set('html','Подождите');
            
            // for Opera
            sending_interval = setTimeout( function() { 
                clearTimeout(sending_interval);
                var uploadSesion = form.getElement('input[name=attachedfiles_session]');
                xajax_tservicesOrdersNewMessage(orderid, message.value, uploadSesion.value);
            }, 10);
            return false;
        } else {
            return false;
        }

    };
    
    
    //--------------------------------------------------------------------------

    this.log = function(message, level) 
    {
        "use strict";
        
        if(!is_debug) return false;
        
        if (window.console) {
            if (!level || level === 'info') {
                window.console.log(message);
            }
            else
            {
                if (window.console[level]) {
                    window.console[level](message);
                }
                else {
                    window.console.log('<' + level + '> ' + message);
                }
            }
        }
        
        return false;
    };
    
    this.updateAttachSession = function(sess) {
        message.set('value', null);
        
        var input = form.getElement("input[name='attachedfiles_session']");
        input.set('value', sess);
        
        btnSend.removeClass("b-button_disabled");
        btnSend.set('html','Отправить сообщение');
        is_sending = 0;
        
        var fileRows = $$('div.b-fon__item');
        for (var i = 0; i < fileRows.length; i++) {
            if (fileRows[i].id.lastIndexOf('attachedfile_', 0) === 0) {
                var idrr = fileRows[i].id;
                $(idrr).addClass('b-layout__txt_hide');
            }
        }    
        
        attachedFiles.count = 0;
        attachedFiles.changeClasses();
        
    };
    
    
    this.checkMessages = function() {
        xajax_tservicesOrdersCheckMessages(orderid);
    };
    
    this.duplicateLinks = function() {
        var dupLinks = $$('a[data-duplicate]');
        dupLinks.each(function(el, i){
            var linkId = el.get('data-duplicate');
            var span = new Element('span', {html: '&#160; или &#160;'});
            var anchor = el.get('text').replace(/^\s+|\s+$/g, '');
            if (linkId > 1) {
                anchor = anchor.substring(0, 1).toLowerCase() + anchor.substring(1);
            }
            var a = new Element('a', {
                href: el.get('href'),
                onclick: el.get('onclick'),
                text: anchor
            });
            if (el.get('data-popup')) {
                a.set('data-popup', el.get('data-popup'));
                Bar_Ext.bindPopup(a);
            }
            span.inject(block.getElement('.b-buttons'), 'bottom');
            a.inject(block.getElement('.b-buttons'), 'bottom');
        });
    };
    
    //--------------------------------------------------------------------------
    
    
    //Запуск инициализации
    this.init();    
    this.checkMessages.delay(40000);
    this.duplicateLinks();
    
}

window.addEvent('domready', function() {
    new TServices_Order_Messages();
});