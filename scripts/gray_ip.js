var gray_ip = {
    error: {
        login:  'Пользователь не найден',
        noIP:   'Укажите хотя бы один IP адрес',
        newIP:  'Вы ввели IP в недопустимом формате',
        fromIP: 'Начальный IP должен состоять из чисел от 0 до 255 или *.\nНачинаться с числа. Вместо * будут подставлены 0',
        toIP: 'Конечный IP должен состоять из чисел от 0 до 255 или *.\nНачинаться с числа. Вместо * будут подставлены 255'
    },
    
    bOne: false,
    adminId: 0, // UID админа
    
    clearEditAll: function() {
        $$('div[id^="edit_ip_"]').set('html','');
    },
    
    clearEdit: function(num) {
        $$('div#edit_ip_'+num).set('html','');
    },
    
    submitEdit: function(num) {
        if ( this.validateForm('txt_edit_'+num, 'uid_edit_'+num) ) {
            xajax_setPrimaryIp( this.trim('uid_edit_'+num), this.trim('log_edit_'+num), this.adminId, this.trim('txt_edit_'+num) );
        }
    },
    
    clearAdd: function() {
        $$('#frm_gray_ip_add button').set( 'disabled', true );
        $('add_ip').set('value','');
        $('add_login').set('value','');
        $('add_uid').set('value','');
        this.changeLogin();
    },
    
    submitAdd: function() {
        if ( this.validateForm('add_ip', 'add_uid') ) {
            xajax_addPrimaryIp( this.trim('add_uid'), this.trim('add_login'), this.adminId, this.trim('add_ip') );
        }
    },
    
    validateForm: function( idIp, idUid ) {
        var ipT = this.trim( idIp );
        
        if ( !ipT ) {
            alert(this.error.noIP);
            return false;
        }
        
        var ipA   = ipT.split("\n");
        var aPart = new Array;
        
        for ( i = 0; i < ipA.length; i++ ) {
            ipA[i] = ipA[i].replace(/(^\s+)|(\s+$)/g, '');
            aPart  = ipA[i].split('.');
            
            if ( aPart.length == 4 ) {
                for ( j = 0; j < aPart.length; j++ ) {
                    if ( !this.validIpPart(aPart[j], this.error.newIP) ) {
                        return false;
                    }
                }
            }
            else {
                alert(this.error.newIP);
                return false;
            }
        }
        
        if ( !this.trim(idUid) ) {
            alert(gray_ip.error.login);
            return false;
        }
        
        return true;
    },
    
    checkLogin: function( login ) {
        if ( login.length == 0 ) {
            return false;
        }
        
        new Request.JSON({
            url: '?task=checklogin',
            onComplete: function(resp) {
                if(resp && resp.success) {
                    $$('span.login-view a:first-child').set('href', '/users/' + resp.user.login);
                    $$('input[name="add_uid"]').set('value', resp.user.uid);
                    $$('span.login-view a:first-child').set('html', resp.user.uname + ' '
                        + resp.user.usurname
                        + ' [' + resp.user.login + ']');
                    $$('#frm_gray_ip_add button').set( 'disabled', false );
                    $$('span.login-input').setStyle('display','none');
                    $$('span.login-view').setStyle('display','inline-block');
                } else {
                    alert(gray_ip.error.login);
                    $$('#frm_gray_ip_add button').set( 'disabled', true );
                }
            }
        }).post({
            'login': login,
            'u_token_key': _TOKEN_KEY
        });
    },
    
    changeLogin: function() {
        $$('span.login-view').setStyle('display','none');
        $$('span.login-input').setStyle('display','inline-block');
    },
    
    checkUsers: function( checked ) {
        Array.each( $$("input[id^='chk_users']"), function(obj,index) {
            obj.checked = checked;
        });
        
        Array.each( $$("input[id^='chk_prim']"), function(obj,index) {
            obj.checked = checked;
        });
    },
    
    checkSecondaryUsers: function( pid, checked ) {
        Array.each( $$("td[id^='td_sec_"+pid+"_'] input[id^='chk_users']"), function(obj,index) {
            obj.checked = checked;
        });
        
        if ( this.bOne ) {
            $('chk_all').checked = checked;
        }
    },
    
    MassBanSecondaryUser: function( pid ) {
        adminLogOverlayClose();
        
        var users = this.getUsersInner("td[id^='td_sec_"+pid+"_'] input[id^='chk_users']");
        
        if ( users ) {
            $('ban_btn').set( 'disabled', false );
            $('ban_btn').set( 'value', 'Сохранить' );
            
            var div = document.getElementById('ov-notice22');
            var context = ( typeof(banned.context['gray_ip']) != 'undefined' && banned.context['gray_ip'] != null ) ? banned.context['gray_ip'] : '';
            
            banned.buffer['userban_0'] = { 
                users: users,
                action: 'userBan', 
                divObj: div, 
                divHTML: div.innerHTML,
                context: context,
                customReason: new Array(),
                reasonId: new Array(),
                reasonName: new Array()
            };
            
            xajax_setUserMassBanForm();
        }
    },
    
    MassDelSecondaryUser: function( pid, ip ) {
        adminLogOverlayClose();
        
        var users = this.getUsersInner("td[id^='td_sec_"+pid+"_'] input[id^='chk_users']");
        
        if ( users ) {
            if ( confirm('Прекратить отслеживать выбранных пользователей для IP '+ ip +'?') ) {
                this.checkUsers( false );
                
                for ( i=0; i < users.length; i++ ) {
                    $('chk_users'+users[i]).checked = true;
                }
                
                $('task').set('value','mass_sdel');
                $('frm_gray_ip').submit();
            }
        }
    },
    
    getUsers: function() {
        return this.getUsersInner("input[id^='chk_users']");
    },
    
    getUsersInner: function( selector ) {
        var cnt    = 0;
        var users = new Array();
        
        Array.each( $$(selector), function(obj,index) {
            if ( obj.checked ) {
                users[cnt]=obj.value;
                cnt++;
            }
        });
        
        if ( !users.length ) {
            alert('Необходимо выбрать хотя бы одного пользователя');
            return false;
        }
        
        return users;
    },
    
    submitDel: function(num) {
        var users = this.getUsers();
        
        if ( users ) {
            $('frm_gray_ip').submit();
        }
    },
    
    clearFilter: function() {
        $('f_ip').set('value','');
        $('t_ip').set('value','');
        $('search_name').set('value','');
        
        $('adm').selectedIndex = 0;
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
    
    validIpPart: function( ip, errMsg ) {
        var regIP  = /^([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
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
    }
};

function grayIpDomready() {
    $$('.toggle a').addEvent('click', function() {
        this.getParent('h4').getNext('.slideBlock').toggleClass('filtr-hide');
        return false;
    });
    
    $('add_login').addEvent('focus', function() {
        $$('#frm_gray_ip_add button').set( 'disabled', true );
    });
    
    $('add_login').addEvent('blur', function() {
        gray_ip.checkLogin(gray_ip.trim('add_login'));
    });
}

window.addEvent('domready', function() {
    grayIpDomready();
});