/**
 * Класс настроек профиля пользователя
 * @type Class
 */
var CUser_Setup = new Class({
    
    initialize: function(){},

    showSpec: function(){
        var ch2 = $('ch2');
        if(!ch2.get('checked')) {
            ch2.setProperty('checked', true);
            xajax_togglePrj(1);
        }
        $('filter_body_p').setStyle('display','');
        $('head_filter').setStyle('display','');
        $('ch2-a').setStyle('display','none');
        return false;
    }
});

//--------------------------------------------------------------------------

window.addEvent('domready', function() {
    window.User_Setup = new CUser_Setup();
});