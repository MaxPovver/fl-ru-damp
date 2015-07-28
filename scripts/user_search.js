var user_search = {
    error: {
        fromIP: 'Начальный IP должен состоять из чисел от 0 до 255 или *.\nНачинаться с числа. Вместо * будут подставлены 0',
        toIP: 'Конечный IP должен состоять из чисел от 0 до 255 или *.\nНачинаться с числа. Вместо * будут подставлены 255'
    },
    
    setUlradio: function( obj, hidden_id ) {
        $(obj).getParent('.ulradio').getElements('li').removeClass('active');
        $(obj).getParent('li').addClass('active');
        $(hidden_id).set('value', $(obj).getProperty('rel'));
    },
    
    submitFilter: function() {
        var valid  = true;
        
        if ( !this.validIp('f_ip', this.error.fromIP) ) {
            return false;
        }
        
        if ( !this.validIp('t_ip', this.error.toIP) ) {
            return false;
        }
        
        return true;
    },
    
    clearFilter: function() {
        $('f_ip').set('value','');
        $('t_ip').set('value','');
        $('role').set('value','');
        $('status').set('value','');
        $('search_name').set('value','');
        $('search_phone').set('value','');
        $('search_name_exact').set('checked', false);
        $('search_phone_exact').set('checked', false);
        
        $('ulstatus').getElements('li').removeClass('active');
        $('ulrole').getElements('li').removeClass('active');
        $('ulstatus').getFirst('li').addClass('active');
        $('ulrole').getFirst('li').addClass('active');
    },
    
    setIpFilter: function(id) {
        var value = $(id).get('html');
        $('f_ip').set('value',value);
        $('t_ip').set('value',value);
        
        var a = $('a_user_search_filter');
        if (a) {
            new Fx.Scroll(window,{duration:0}).toElement($(a));
        }
    },
    
    validIp: function( id, errMsg ) {
        var regIP  = /^([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\.(\*|[1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]|)){0,3}$/;
        var ip     = this.trim(id);
        if ( ip != '' ) {
            if ( !regIP.test(ip) ) {
                alert(errMsg);
                return false;
            }
        }
        return true;
    },
    
    trim: function( id ) {
        return $(id).get('value').replace(/(^\s+)|(\s+$)/g, '');
    },
    
    stopNotifications: function( uid, role ) {
        if ( confirm('Восстановить рассылки сможет только сам пользователь в настройках своего аккаунта. Все равно продолжить?') ) {
            xajax_stopNotifications( uid, role );
        }
    },
            
    setVerification: function(uid, type) {
        xajax_setVerification( uid, type ); 
    }        
};