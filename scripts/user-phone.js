function User_Phone()
{
    User_Phone=this; // ie ругался без этого, пока не понял.
    
    //--------------------------------------------------------------------------
    
    var popup = $('user_phone_popup');
    var form = $('main_phone_form');
    
    
    //--------------------------------------------------------------------------
    
    //Начальная инициализация
    this.init = function() 
    {
    };
    
    this.showPopup = function()
    {
        popup.removeClass('b-shadow_hide');
    };
    
    this.unbindStart = function() {
        $('buttons_step2').addClass('b-layout__txt_hide');
        $('buttons_step3').removeClass('b-layout__txt_hide');
        $('mob_phone_text').addClass('b-layout__txt_hide');
        
        var phone = $($('getsms').get('data-field')).get('value');
        $('sms_sent_ok').getElements('span')[0].set('text', phone);
        $('sms_sent_ok').removeClass('b-layout__txt_hide');

        $('smscode').set('value', '');
        $('mob_code_block').removeClass('b-layout__txt_hide');
        $('getsms').fireEvent('click');
    };
    
    this.savePhone = function(bind) {
        var code  = $('getsms').get('data-code');
        var field = $('getsms').get('data-field');
        if(code == '' || code == undefined || field == '' || field == undefined) {
            return false;
        }
        
        var phone = $(field).get('value');
        var error = 0;
        if($(code).get('value') == '') {
            $(code).getParent().addClass('b-combo__input_error');
            error = 1;
        } 
        
        if(phone.match(/^\+[0-9]{10,15}/) == null) {
            $(field).getParent().addClass('b-combo__input_error');
            error = 1;
        }
        
        if(error == 0) {
            $(code).getParent().removeClass('b-combo__input_error');
            $(field).getParent().removeClass('b-combo__input_error');
            var form = $('getsms').get('data-form');
            if( $(form) != undefined ) {
                var type = bind ? 'bind' : 'unbind';
                xajax_checkCode(phone, $(code).get('value'), type);
            }
        }
        return false;
    };
    
   
    //--------------------------------------------------------------------------
    
    
    //Запуск инициализации
    this.init();    
}

window.addEvent('domready', function() {
    new User_Phone();
});