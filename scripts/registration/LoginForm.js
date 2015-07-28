/**
 * Авторизация
 * 
 * @type Class
 */
var LoginForm = new Class({

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

        new Form.Validator(form, {
            //useTitles: true,
            serial:false,
            onElementPass: this.onElementPass,
            onElementFail: this.onElementFail
        });
        
        
        //Скролим к ошибке
        var first_field = $$('.b-combo__input_error')[0];
        if(first_field) {
            var fcoord = first_field.getCoordinates();
            new Fx.Scroll(window).start(0,fcoord.top - 100);
        }
    },
    
    onElementPass: function(el)
    {
        var id = el.get('id');

        if (!id) {
            return false;
        }

        var error = $(id + '-error');
        
        if (!error) {
            return false;
        }
        
        error.addClass('b-layout_hide');
        el.getParent().removeClass('b-combo__input_error');
    },
    
    onElementFail: function(el, validator) 
    {
        var id = el.get('id');

        console.log(id);

        if (!id) {
            return false;
        }

        var error = $(id + '-error');
        var error_txt = $(id + '-error-text');

        if (!error || !error_txt) {
            return false;
        }

        error_txt.set('html',this.getValidator(validator[0]).getError(el));
        error.removeClass('b-layout_hide');
        el.getParent().addClass('b-combo__input_error');
   }
           
});

window.addEvent('domready', function() {
    new LoginForm('login-form');
});