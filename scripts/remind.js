
var roleList = {
    1:'Фрилансер',
    2:'Работодатель'
};

/**
 * Восстановление пароля
 * 
 * @type Class
 */
var Remind = new Class({

    initialize: function(id)
    {
        var form = $(id);

        if(!form)  {
            return false;
        }        
        
        Locale.use("ru-RU");
        Locale.define(Locale.getCurrent().name, 'FormValidator', {
            required: 'Обязательно для заполнения',
            minLength: 'Пожалуйста, введите от {minLength} символов'
        });        

        window.remindFormValidator = new Form.Validator(form, {
            //useTitles: true,
            serial:false,
            onElementPass: this.onElementPass,
            onElementFail: this.onElementFail
        });
        
        $$('[data-validators]').getLast().addEvent('keyup', function (){
            window.remindFormValidator.validate();
            tougleSubmitButton();
        });
        
        $('remind_email').addEvent('keyup', function () {
            if (this.get('value').length > 0) {
                $$('[data-captcha-block]').removeClass('g-hidden');
            }
        });
        
        var role = ComboboxManager.getInput("role");
        if (role) {
            role.b_input.addEvent('bcombochange', function() {
                window.remindFormValidator.validate();
                tougleSubmitButton();                
            });
        }       
    },
    
    onElementPass: function(el)
    {
        var id = el.get('id');

        if (!id) {
            return false;
        }

        var error = $(id + '_error');
        
        if (!error) {
            return false;
        }
        
        error.addClass('b-shadow_hide');
        el.getParent().removeClass('b-combo__input_error');
    },
    
    onElementFail: function(el, validator) 
    {
        var id = el.get('id');

        if (!id) {
            return false;
        }

        var error = $(id + '_error');
        var error_txt = $(id + '_error_txt');

        if (!error || !error_txt) {
            return false;
        }

        error_txt.set('html',this.getValidator(validator[0]).getError(el));
        error.removeClass('b-shadow_hide');
        el.getParent().addClass('b-combo__input_error');
   }
           
});

window.addEvent('domready', function() {
    new Remind('email_remind');
});


//------------------------------------------------------------------------------
// Перерабатываем функции ниже
//------------------------------------------------------------------------------

function tougleSubmitButton()
{
   var remind_button_email = $('remind_button_email');
   if($$('.b-layout__txt_error:not(.b-shadow_hide)').length) {
       remind_button_email.addClass('b-button_disabled');
   } else {
       remind_button_email.removeClass('b-button_disabled');
   }
}

function RemindByEmail() 
{
    if ($('remind_button_email').hasClass('b-button_disabled')) {
        //tougleSubmitButton();
        return false;
    }
    
    if (!window.remindFormValidator.validate()) {
        //tougleSubmitButton();
        return false;
    }


	$("remind_captcha_error").addClass("b-shadow_hide");    
	$("remind_email_error").addClass("b-shadow_hide");    
    $('remind_button_email').addClass('b-button_disabled');
    var roleInput = $('role_db_id');
    var role = roleInput.get('value') > 0?roleInput.get('value') : 0;
    xajax_RemindByEmail( 
        $('remind_email').get('value'), 
        $('remind_captcha').get('value'),  
        $('captchanum').get('value'),
        role
    );
        
    return false;
}

function RemindChangeSMSLogin(login) {
    document.getElementById('remind_user_login').innerHTML = 'free 2+<span id="lp-acc-value">'+login+'</span>';
}

function toggleRemindMethod() {
    $('sms_remind').toggleClass('b-txt_hide');
    $('email_remind').toggleClass('b-txt_hide');
}


 