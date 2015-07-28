var Opauth = new Class({
    
    submitted: false,
    
    initialize: function()
    {
        var _this = this;
        this.form = $('form-opauth');
        
        
        if (this.form) {
            this.submitBtn = $('opauth-save-btn');
            this.submitBtn.addEvent('click', function(){
                _this.formSubmit();
                return false;
            });
            
            this.inputLogin = $('reg_login');
            if (this.inputLogin) {
                this.inputLogin.addEvent('blur', function(){
                    _this.registration_value_check('login');
                }).addEvent('keyup', function(){
                    _this.registration_value_check('login', this.get('value'), 0);
                }).addEvent('focus', function() {
                    $('error_login').addClass('b-shadow_hide');
                });
            }
            
            this.inputEmail = $('reg_email');
            if (this.inputEmail) {
                this.inputEmail.addEvent('blur', function(){
                    _this.registration_value_check('email');
                }).addEvent('focus', function() {
                    $('error_email').addClass('b-shadow_hide');
                });
            }
        }        
    },
    
    formSubmit: function() {
        if (!this.submitted) {
            this.checkValueAllInputs();
            if(!this.submitBtn.hasClass('b-button_rectangle_color_disable')) {
                this.submitBtn.addClass('submitted');
                iTimeoutId = null;
                this.submitted = true;
                this.form.submit();
            }
        }
    },
    
    checkValueAllInputs: function() {
        if (this.inputLogin && !this.inputLogin.getParent("div.b-combo__input").hasClass("b-combo__input_error") ) {
            this.registration_value_check('login', 0, 1, 0);
        }

        if (this.inputEmail && !this.inputEmail.getParent("div.b-combo__input").hasClass("b-combo__input_error") ) {
            this.registration_value_check('email', 0, 1, 0);
        }
    },
    
    registration_value_check: function(field, val, showError, sendRequest) {
        if (String(sendRequest) == "undefined") {
            sendRequest = 1;
        } else {
            sendRequest = parseInt(sendRequest) != 1 ? 0 : 1;
        }
        if (String(showError) == "undefined") showError = 1;
        else {
            if (parseInt(showError) != 1) showError = 0;
            else {
                showError = 1;
            }
        }
        var f = $("reg_" + field);
        var e = $("error_" + field);
        if(e != undefined) e.addClass('b-shadow_hide').setStyle("display", "none");
        f.setProperty('title',null);
        if (f.getParent('.b-combo__input')) {
            f.getParent('.b-combo__input').removeClass('b-combo__input_error');
        }
        
        val = f.get("value");
        val = (val==null ? fld.val : val).toString().replace(/^\s+|\s+$/g, '');
        
        switch(field) {
            case 'login':
                if(val.match(/^[a-zA-Z0-9]+[-a-zA-Z0-9_]{2,}$/)==null) {
                    var mess;
                    if (val.length === 0) {
                        mess = "Введите логин";
                    } else {
                        mess = "Поле заполнено некорректно";
                    }
                    if (showError) {
                        this.show_error(field, mess);
                    }
                } else if (sendRequest == 1){
                    if(iTimeoutId != null) {
                        clearTimeout(iTimeoutId);
                        iTimeoutId = null;
                    }
                    iTimeoutId = setTimeout(function(){
                        xajax_CheckUser(val, true);
                    }, 300);  
                }
                break;
            case 'email':
                if(val.match(/^[A-Za-z0-9А-Яа-я\.\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e]{1,63}@[A-Za-z0-9А-Яа-я-]{1,63}(\.[A-Za-z0-9А-Яа-я]{1,63})*\.[A-Za-zА-Яа-я]{2,15}$/)==null) {
                    var mess;
                    if (val.length === 0) {
                        mess = "Введите email";
                    } else {
                        mess = "Поле заполнено некорректно";
                    }
                    if (showError) {
                        this.show_error(field, mess);
                    }
                }
                break;
        }
        if($$('.b-combo__input_error').length == 0) {
            this.submitBtn.removeClass('b-button_rectangle_color_disable');
        } else {
            this.submitBtn.addClass('b-button_rectangle_color_disable');
        }
    },
    
    show_error: function(field, error)
    {
        show_error(field, error);
    }
    
});

function show_error(field, error) {
    if ($('opauth-save-btn') && $('opauth-save-btn').hasClass('submitted')) {
        return false;
    }
    var inp = $('reg_' + field).getParent('.b-combo__input');
    if(inp != undefined) inp.addClass('b-combo__input_error');
    if($('error_' + field) != undefined) {
        $('error_' + field).removeClass('b-shadow_hide').setStyle('display', null);
        $('error_txt_' + field).set('html', '<span class="b-form__error"></span>' + error);
    }
}
    

var roleList = {
    1:'Фрилансер',
    2:'Работодатель'
};    
    
window.addEvent('domready', function() {
    new Opauth();
    CSRF(_TOKEN_KEY);
});
