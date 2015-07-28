var messages_spam = {
    openSpam: new Array(),
    
    onCalendarSetDate: function( ctrlId ) {
        var prefix = ctrlId.substr(5);
        var parts  = $(ctrlId).get('value').split('-');
        parts[0]   = parts[0].replace(/(^0+)/g, '');
        this._setDateSelects(prefix, parts[0], parts[1], parts[2]);
    },
    
    onSelectSetDate: function( prefix ) {
        var d = $(prefix + '_d').get('value');
        var m = $(prefix + '_m').get('value');
        var y = $(prefix + '_y').get('value');
        if ( d && m && y ) {
            $('fake_'+prefix).set('value',d+'-'+m+'-'+y);
        }
    },
    
    clearFilter: function( dateD, dateM, dateY ) {
        var tdDay = dateD.length > 1 ? dateD : '0'+dateD;
        $('fake_s_from').set('value', tdDay+'-'+dateM+'-'+dateY);
        $('fake_c_from').set('value', tdDay+'-'+dateM+'-'+dateY);
        this._setDateSelects('s_to', dateD, dateM, dateY);
        this._setDateSelects('c_to', dateD, dateM, dateY);
        this._setDateSelects('s_from', '', '', '');
        this._setDateSelects('c_from', '', '', '');
        $('spamer_ex').set('checked', false);
        $('user_ex').set('checked', false);
        $('fake_s_to').set('value', '');
        $('fake_c_to').set('value', '');
        $('spamer').set('value', '');
        $('user').set('value', '');
        $('kwd').set('value', '');
    },
    
    _setDateSelects: function( prefix, dateD, dateM, dateY ) {
        $(prefix + '_m').set('value', dateM);
        $(prefix + '_y').set('value', dateY);
        UpdateDays(prefix);
        $(prefix + '_d').set('value', dateD);
    },
    
    checkDateFilter: function() {
        var s = this._checkDatePeriod('s','спама');
        var c = this._checkDatePeriod('c','жалобы');
        return ( s && c );
    },
    
    _checkDatePeriod: function( prefix, word ) {
        var oDaysF    = $(prefix + '_from_d');
        var oMonthsF  = $(prefix + '_from_m');
        var oYearsF   = $(prefix + '_from_y');
        var oDaysT    = $(prefix + '_to_d');
        var oMonthsT  = $(prefix + '_to_m');
        var oYearsT   = $(prefix + '_to_y');
        
        if ( oDaysF && oMonthsF && oYearsF && oDaysT && oMonthsT && oYearsT ) {
            if ( 
                !(oDaysF.get('value') && oMonthsF.get('value') && oYearsF.get('value'))
                && !(!oDaysF.get('value') && !oMonthsF.get('value') && !oYearsF.get('value'))
            ) {
                alert('Укажите начальную дату ' + word);
                return false;
            }
            
            if ( 
                !(oDaysT.get('value') && oMonthsT.get('value') && oYearsT.get('value'))
                && !(!oDaysT.get('value') && !oMonthsT.get('value') && !oYearsT.get('value'))
            ) {
                alert('Укажите конечную дату ' + word);
                return false;
            }
            
            if ( 
                oDaysF.get('value') && oMonthsF.get('value') && oYearsF.get('value') 
                && oDaysT.get('value') && oMonthsT.get('value') && oYearsT.get('value') 
            ) {
                var fDate = Date.UTC( oYearsF.get('value'), oMonthsF.get('value'), oDaysF.get('value') );
                var tDate = Date.UTC( oYearsT.get('value'), oMonthsT.get('value'), oDaysT.get('value') );
                
                if ( tDate < fDate ) {
                    alert('Конечная дата ' + word + ' должна быть больше начальной');
                    return false;
                }
            }
        }
        
        return true;
    },
    
    getSpamComplaints: function( id, md5 ) {
        if ( typeof(this.openSpam[id+md5]) != 'undefined' ) {
            if ( this.openSpam[id+md5].opened == true ) {
                this.openSpam[id+md5].opened = false;
                $( 'div_all_compliants_'+id+md5 ).setStyle('display','none');
                $('div_compliant_'+id+md5).setStyle('display','');
            }
            else {
                this.openSpam[id+md5].opened = true;
                $('div_compliant_'+id+md5).setStyle('display','none');
                $('div_all_compliants_'+id+md5).setStyle('display','');
            }
        }
        else {
            new Request.JSON({
                url: '/xajax/contacts.server.php',
                onSuccess: function(resp) {
                    if ( resp && resp.success ) {
                        var html = '';
                        
                        for ( i = 0; i < resp.data.length; i++ ) {
                            html += '<div class="compliant-item">\
                                <div class="form fs-o">\
                                    <b class="b1"></b>\
                                    <b class="b2"></b>\
                                    <div class="form-in">\
                                        <span class="compliant-autor">'+ (resp.data[i].text ? 'Комментарий от' : 'Пожаловался' ) + '&nbsp;\
                                        <a href="/users/' + resp.data[i].login + '">' + resp.data[i].name + ' ' + resp.data[i].surname + ' ['+ resp.data[i].login + ']</a> ' + resp.data[i].date + ' в ' + resp.data[i].time + '</span>' 
                                        + ( resp.data[i].text ? '<p>'+ resp.data[i].text +'</p>' : '' )
                                    + ' </div>\
                                    <b class="b2"></b>\
                                    <b class="b1"></b>\
                                </div>\
                            </div>';
                        }
                        
                        messages_spam.openSpam[id+md5] = {opened: true};
                        $('div_all_compliants_'+id+md5).set('html',html);
                        $('div_all_compliants_'+id+md5).setStyle('display','');
                        $('div_compliant_'+id+md5).setStyle('display','none');
                    }
                    else {
                        alert('Ошибка получения данных');
                    }
                }
            }).post({
               'xjxfun': 'getSpamComplaints',
               'xjxargs': ['N' + id, 'S' + md5],
               'u_token_key': _TOKEN_KEY
            });
        }
    }
};