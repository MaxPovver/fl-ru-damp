function TServices_Order_Auth()
{
    TServices_Order_Auth = this; // ie ругался без этого, пока не понял.


    //--------------------------------------------------------------------------

    //Начальная инициализация
    this.init = function()
    {
        this.popup = $('tesrvices_order_auth_popup');
        if (this.popup) {
            var wrapper = $$('div.b-page__wrapper')[0];
            this.popup.inject(wrapper, 'after');
        }
    };

    //--------------------------------------------------------------------------

    this.showPopup = function()
    {
        this.popup.removeClass('b-shadow_hide');
		$$('body').setStyle('overflow','hidden');//защита от прокрутки страницы при скроле попапа
    };
    
    this.hidePopup = function() {
        this.popup.addClass('b-shadow_hide');
		$$('body').setStyle('overflow','');//снимаем защиту
    };
    
    this.showSuccess = function(message) {
        this.popup.getElement('.b-layout__table').remove();
        this.popup.getElement('.b-buttons').remove();
        this.popup.getElement('.b-layout__txt').set("html", message + '<a href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" style="margin-top:20px;" onclick="TServices_Order_Auth.popup.addClass(\'b-shadow_hide\'); return false;">Закрыть</a>');
    };


    //--------------------------------------------------------------------------


    this.checkEmail = function(showError) {
        var f = $("reg_email");
        var e = $("error_email");
        if (e != undefined)
            e.addClass('b-shadow_hide').setStyle("display", "none");
        f.setProperty('title', null);
        if (f.getParent('.b-combo__input')) {
            f.getParent('.b-combo__input').removeClass('b-combo__input_error');
        }
        if (f.getParent('.b-layout__middle'))
            f.getParent('.b-layout__middle');

        var val = f.get("value");
        val = val.toString().replace(/^\s+|\s+$/g, '');
        if (val.match(/^[A-Za-z0-9А-Яа-я\.\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e]{1,63}@[A-Za-z0-9А-Яа-я-]{1,63}(\.[A-Za-z0-9А-Яа-я]{1,63})*\.[A-Za-zрфРФ]{1,6}$/) == null) {
            var mess;
            if (val.length === 0) {
                mess = "Введите email";
            } else {
                mess = "Поле заполнено некорректно";
            }
            if (showError) {
                this.showError('email', mess);
            }
        }
    };

    this.showError = function(field, error) {
        var inp = $('reg_' + field).getParent('.b-combo__input');
        if (inp != undefined)
            inp.addClass('b-combo__input_error');
        if ($('error_' + field) != undefined) {
            $('error_' + field).removeClass('b-shadow_hide').setStyle('display', null);
            $('error_txt_' + field).set('html', '<span class="b-form__error"></span>' + error);
        }
    };

    this.submitForm = function() {
        this.checkEmail(1);
        
        var options = xajax.getFormValues($('__form_tservice'));
        var email = $("reg_email").get("value");
        var name = $("reg_name").get("value");
        var surname = $("reg_surname").get("value");
        var paytype = $$('input[name=paytype]:checked');
        options.paytype = (paytype.length)?paytype[0].get('value'):'0';
        if ($('error_email').hasClass('b-shadow_hide')) {
            xajax_tservices_order_auth(email, name, surname, options);
        }
    };
    
    

    //Запуск инициализации
    this.init();
}

window.addEvent('domready', function() {
    new TServices_Order_Auth();
});