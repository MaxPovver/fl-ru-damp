var bAdminLogComments = false; // не используется
var bAdminLogStat     = false;
var aAdminLogProjName = new Array();

function setReasonBold( sid ) {
    $('is_bold_'+sid).set( 'disabled', true );
    xajax_setReasonBold( sid, $('is_bold_'+sid).get('checked') ? 't' : 'f' );
}

function adminLogDomready() {
    $$('.admin-lenta table a').addEvent('click',function(event){event.stopPropagation();});
    UpdateDays( 'from' );
    UpdateDays( 'to' );
}

window.addEvent('domready', function() {
    adminLogDomready();
});

function switchTimeInputsEnable(s) {
    if (s == 'time') {
    	$('shifts_list') .disabled = true;
    	$('timeFrom')    .disabled = false;
        $('timeTo')      .disabled = false;
    }
    if (s == 'shift') {
    	$('shifts_list') .disabled = false;
    	$('timeFrom')    .disabled = true;
        $('timeTo')      .disabled = true;
        setTimeInterval();
    }
}

function setTimeInterval() {
    setTimeIntervalFromSelect();
    $('timeFrom').value = document.timeFrom;
    $('timeTo').value = document.timeTo;
}

function setTime(time, prefix, msg) {
    var re = /[0-9]{2}:[0-9]{2}/;
    if (!msg) {
        msg = "Необходимо выбрать смену или указать период";
    }
    if (re.test(time)) {
        var arr = time.split(":");
        $(prefix + "_h").value = arr[0];
        $(prefix + "_i").value = arr[1];
        return true;
    } else {
        if (msg) {
            alert(msg);
        }
    }
    return false;
}

function setTimeIntervalFromSelect() {
    for (var i = 0; i < $('shifts_list').options.length; i++) {
        if ($('shifts_list').options[i].value == $('shifts_list').value) {
        	var timeFrom = document.timeFrom = $('shifts_list').options[i].getAttribute("time_from");
            var timeTo   = document.timeTo   =  $('shifts_list').options[i].getAttribute("time_to");
            var r = (setTime(timeFrom, "from") && setTime(timeTo, "to"));
            return r;
        }
    }
    return false;
}

function setTimeIntervalFromTextInput() {
	var a = setTime($('timeFrom').value, "from", "Некорректно указано время начала смены");
    var b = setTime($('timeTo').value, "to", "Некорректно указано время окончания смены");
    return (a && b);
}

function checkDateFilter() {
    if ($('shift').checked) {
        if ( !setTimeIntervalFromSelect() ) {
            return false; 
        }
    }else if ($('time').checked) {
        if ( !setTimeIntervalFromTextInput() ) {
            return false;
        }
    } else {
        alert("Необходимо выбрать смену или указать инервал");
        return false;
    }
    
    var oDaysF    = $('from_d');
    var oMonthsF  = $('from_m');
    var oYearsF   = $('from_y');
    var oDaysT    = $('to_d');
    var oMonthsT  = $('to_m');
    var oYearsT   = $('to_y');
    
    if ( oDaysF && oMonthsF && oYearsF && oDaysT && oMonthsT && oYearsT ) {
        if ( 
            !(oDaysF.get('value') && oMonthsF.get('value') && oYearsF.get('value'))
            && !(!oDaysF.get('value') && !oMonthsF.get('value') && !oYearsF.get('value'))
        ) {
            alert('Укажите начальную дату');
            return false;
        }
        
        if ( 
            !(oDaysT.get('value') && oMonthsT.get('value') && oYearsT.get('value'))
            && !(!oDaysT.get('value') && !oMonthsT.get('value') && !oYearsT.get('value'))
        ) {
            alert('Укажите конечную дату');
            return false;
        }
        
        var nStampF = Date.UTC( parseInt(oYearsF.get('value')), parseInt(oMonthsF.get('value')) - 1, parseInt(oDaysF.get('value')) );
        var nStampT = Date.UTC( parseInt(oYearsT.get('value')), parseInt(oMonthsT.get('value')) - 1, parseInt(oDaysT.get('value')) );
        
        if ( nStampF > nStampT ) {
            alert('Конечная дата не может быть меньше начальной');
            return false;
        }
    }
    
    return true;
}

function UpdateDays( id ) {
    var aDaysNum = new Array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
    var oDays    = $(id + '_d');
    var oMonths  = $(id + '_m');
    var oYears   = $(id + '_y');
    
    if ( oDays && oMonths && oYears ) {
        if ( oMonths.get('value') == 2 ) {
            if ( oYears.get('value') % 400==0 || ( oYears.get('value') % 100!=0 && oYears.get('value') % 4==0 ) ) {
               aDaysNum[1] = 29;
            }
        }
        
        var nDayBak  = parseInt(oDays.get('value')); // Запоминаем выбранный день
        var nLastDay = parseInt(oDays.getLast('option').get('value'));
        
        nDiff = nLastDay - aDaysNum[ oMonths.get('value')-1 ];
        
        if ( nDiff > 0 ) {
            for ( i=0; i < nDiff; i++ ) {
                oDays.getLast('option').destroy();
            }
        }
        else {
            nDiff *= -1;
            for ( i=0; i < nDiff; i++ ) {
                nVal = parseInt( nLastDay ) + i + 1;
                opt = new Element('option', {value: nVal, html: nVal});
                opt.inject(oDays);
            }
        }
        
        if ( parseInt(oDays.getLast('option').get('value')) < nDayBak ) {
            oDays.getLast('option').set('selected', 'selected');
        }
    }
    
    return false;
}

function adminLogValidateStat() {
    return checkDateFilter();
}

function _adminLogGetChecked( id, error ) {
    var cnt  = 0;
    var aChk = new Array();
    
    Array.each( $$("input[id^='"+ id +"']"), function(obj, index) {
        if ( obj.checked ) {
            aChk[cnt] = obj.value;
            cnt++;
        }
    });
    
    if ( !aChk.length ) {
        alert( error );
        return false;
    }
    
    return aChk;
}

function adminLogClearFilter( dateD, dateM, dateY ) {
    $('from_d').set('value', '');
    $('to_d').set('value', (bAdminLogStat ? '' : dateD) );
    
    var input = $('search');
    if ( input ) {
        input.set('value', '');
    }
    
    var input = $('search_name');
    if ( input ) {
        input.set('value', '');
    }
    
    var input = $('category');
    if ( input ) {
        input.selectedIndex = 0;
        adminLogSubCatFilter( 0, 0 );
    }
    
    var input = $('from_h');
    if ( input ) {
        input.set('value', '');
    }
    
    var input = $('to_h');
    if ( input ) {
        input.set('value', '');
    }
    
    var input = $('from_i');
    if ( input ) {
        input.set('value', '');
    }
    
    var input = $('to_i');
    if ( input ) {
        input.set('value', '');
    }
    
    $$('input[id^="chk_time"]').set('checked', false);
    $$('input[id^="chk_act"]').set('checked', false);
    
    $('from_m').selectedIndex = 0;
    $('from_y').selectedIndex = 0;
    $('to_m').selectedIndex   = ( bAdminLogStat ) ? 0 : dateM - 1;
    $('to_y').selectedIndex   = 0;
    
    var input = $('act');
    if ( input ) {
        input.selectedIndex    = 0;
    }
    
    var input = $('adm');
    if ( input ) {
        input.selectedIndex    = 0;
    }
}

function adminLogSubCatFilter( category, sub_category ) {
    var objSel = $('sub_category');
    objSel.set( 'disabled', true );
    objSel.options.length = 0;
    
    objSel.options[objSel.options.length] = new Option( 'Все подкатегории', 0, true );
    
    var ft = false;
    
    for ( i in filter_specs[category] ) {
        if ( filter_specs[category][i][0] ) {
            ft = (filter_specs[category][i][0] == sub_category) ? true : false;
            objSel.options[objSel.options.length] = new Option( filter_specs[category][i][1], filter_specs[category][i][0], false, ft );
        }
    }
    
    if ( category != 0 ) {
        objSel.set( 'disabled', false );
    }
}

function adminLogOverlayClose() {
    $$("div[id^='ov-notice']").setStyle('display', 'none');
}

function adminLogGetProjBlock( obj_id, name_idx, last_act, src_id, edit ) {
    adminLogOverlayClose();
    
    var sName = aAdminLogProjName[name_idx];
    
    if ( sName.length > 50 ) {
        sName = sName.substr(0, 50)+'...';
    }
    
    $('block_num').set( 'html', '#'+obj_id );
    $('block_name').set( 'title', aAdminLogProjName[name_idx] );
    $('block_name').set( 'html', sName );
    $('adminLogSetProjBlock').set( 'disabled', false );
    $('adminLogSetProjBlock').set( 'value', 'Сохранить' );
    
    xajax_setPrjBlockForm( obj_id, last_act, src_id, edit );
}

function adminLogSetProjBlock() {
    var reason = _adminLogReplace( $('bfrm_0').get('value') );
    
    if ( reason != '' ) {
        $('adminLogSetProjBlock').set( 'disabled', true );
        $('adminLogSetProjBlock').set( 'value', 'Подождите' );
        
        xajax_updatePrjBlock( banned.buffer[0].objectId, banned.buffer[0].act_id, banned.buffer[0].srcId, reason, banned.buffer[0].reasonId[banned.buffer[0].act_id] );
    } 
    else {
        alert('Необходимо указать причину!');
        return false;
   }
}

function adminLogGetOfferBlock( obj_id, name_idx, last_act, src_id, edit ) {
    adminLogOverlayClose();
    
    var sName = aAdminLogProjName[name_idx];
    
    if ( sName.length > 50 ) {
        sName = sName.substr(0, 50)+'...';
    }
    
    $('block_num').set( 'html', '#'+obj_id );
    $('block_name').set( 'title', aAdminLogProjName[name_idx] );
    $('block_name').set( 'html', sName );
    $('adminLogSetOfferBlock').set( 'disabled', false );
    $('adminLogSetOfferBlock').set( 'value', 'Сохранить' );
    
    xajax_setOfferBlockForm( obj_id, last_act, src_id, edit );
}

function adminLogSetOfferBlock() {
    var reason = _adminLogReplace( $('bfrm_0').get('value') );
    
    if ( reason != '' ) {
        $('adminLogSetOfferBlock').set( 'disabled', true );
        $('adminLogSetOfferBlock').set( 'value', 'Подождите' );
        
        xajax_updateOfferBlock( banned.buffer[0].objectId, banned.buffer[0].act_id, banned.buffer[0].srcId, reason, banned.buffer[0].reasonId[banned.buffer[0].act_id] );
    } 
    else {
        alert('Необходимо указать причину!');
        return false;
   }
}

function adminLogMassMoneyBlock( act ) {
    var users = _adminLogGetChecked( 'chk_users', 'Необходимо выбрать хотя бы одного пользователя' );
    
    if ( users ) {
        if ( confirm('Вы уверены что хотите заблокировать деньги?') ) {
            xajax_updateMoneyBlock( JSON.encode(users), act );
        }
    }
}

function adminLogMassActivate( reload ) {
    var users = _adminLogGetChecked( 'chk_users', 'Необходимо выбрать хотя бы одного пользователя' );
    
    if ( users ) {
        if (confirm('Вы уверены, что хотите активировать пользователей?')) {
            xajax_activateUser( JSON.encode(users), reload );
        }
    }
}

function adminLogCheckUsers( checked ) {
    Array.each( $$("input[id^='chk_users']"), function(obj,index) {
        obj.checked = checked;
    });
}

function _adminLogReplace( reason ) {
    reason = reason.replace(/&/g, "&amp;");
    reason = reason.replace(/"/g, "&quot;");
    reason = reason.replace(/'/g, "&#039;");
    reason = reason.replace(/</g, "&lt;");
    reason = reason.replace(/>/g, "&gt;");
    
    return reason;
}

function adjustUserWarnsHTML() {
    var dim = $('t_user_warns').getSize();
    
    if ( dim.y > 350 ) {
        $('d_user_warns').setStyle('height', 350 );
        $('d_user_warns').setStyle('overflow', 'auto' );
    }
}

function adjustLastTenHTML() {
    var dim = $('t_last_ten').getSize();
    
    if ( dim.y > 350 ) {
        $('d_last_ten').setStyle('height', 350 );
        $('d_last_ten').setStyle('overflow', 'auto' );
    }
}

function adminLogWarnMax() {
    alert('У пользователя максимальное количество предупреждений.');
    return false;
}

function getMassWarnUser() {
    adminLogOverlayClose();
    
    var users = _adminLogGetChecked( 'chk_users', 'Необходимо выбрать хотя бы одного пользователя' );
    
    if ( users ) {
        $('adminLogSetUserWarn').set( 'disabled', false );
        $('adminLogSetUserWarn').set( 'value', 'Сохранить' );
        $('bfrm_0').set('value','');
        $('bfrm_sel_0').selectedIndex = 0;
        banned.buffer[0] = { users: users, customReason: '', reasonId: '' };
        $('ov-notice6').setStyle('display', '');
    }
}

function setMassWarnUser() {
    var users = _adminLogGetChecked( 'chk_users', 'Необходимо выбрать хотя бы одного пользователя' );
    
    if ( users ) {
        $('adminLogSetUserWarn').set( 'disabled', true );
        $('adminLogSetUserWarn').set( 'value', 'Подождите' );
        var context = { uid: 'admin', link: '', code: -1, name: '' };
        xajax_updateUserWarn( JSON.encode(banned.buffer[0].users), 1, 0, banned.buffer[0].reasonId, '', $('bfrm_0').get('value'), 'user_search', '', JSON.encode(context) );
    }
}

function getMassBanUser( contextId ) {
    adminLogOverlayClose();
    
    var users = _adminLogGetChecked( 'chk_users', 'Необходимо выбрать хотя бы одного пользователя' );
    
    if ( users ) {
        $('ban_btn').set( 'disabled', false );
        $('ban_btn').set( 'value', 'Сохранить' );
        
        var div = document.getElementById('ov-notice22');
        var context = ( typeof(banned.context[contextId]) != 'undefined' && banned.context[contextId] != null ) ? banned.context[contextId] : '';
        
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
}

function setMassBanUser() {
    var users = _adminLogGetChecked( 'chk_users', 'Необходимо выбрать хотя бы одного пользователя' );
    
    if ( users ) {
        $('ban_btn').set( 'disabled', true );
        $('ban_btn').set( 'value', 'Подождите' );
        
        var reason = _adminLogReplace( $('bfrm_userban_0').get('value') );
        
        if ( reason.replace(/(^\s+)|(\s+$)/g, "") == '' ) {
            alert('Необходимо указать причину!');
            $('ban_btn').set( 'disabled', false );
            $('ban_btn').set( 'value', 'Сохранить' );
            return false;
        }
        
        var date    = '';
        
        if ( $('ban_to_date').get('checked') ) {
            date = $('ban_year').get('value') + '-' + $('ban_month').get('value') + '-' + $('ban_day').get('value');
        }
        
        xajax_updateUserBan( JSON.encode(banned.buffer['userban_0'].users), banned.buffer['userban_0'].act_id, reason, banned.buffer['userban_0'].reasonId[banned.buffer['userban_0'].act_id], date, banned.banNoSend, JSON.encode(banned.buffer['userban_0'].context) );
    }
}

function setSafetyPhoneForm( uid ) {
    $('safety_phone_show'+uid).setStyle('display', 'none');
    $('safety_phone_edit'+uid).setStyle('display', '');
}

function unsetSafetyPhoneForm( uid ) {
    var checed = ($('safety_only_phone_show'+uid).getStyle('display') == 'none') ? false : true;
    $('safety_phone'+uid).set('value', $('safety_phone_hidden'+uid).get('value'));
    $('safety_only_phone'+uid).set('checked', checed);
    $('safety_phone_show'+uid).setStyle('display', '');
    $('safety_phone_edit'+uid).setStyle('display', 'none');
}

function updateSafetyPhone( uid ) {
    var phone   = $('safety_phone'+uid).get('value');
    var only    = $('safety_only_phone'+uid).get('checked') ? 't' : 'f';
    var finance = $('safety_mob_phone'+uid).get('checked') ? 't' : 'f';
    var regex = /^\+\d{7,}$/;
    
    //if ( phone != '' && !regex.test(phone) ) {
    //    alert('Вы ввели телефон в недопустимом формате');
    //    return false;
    //}
    
    if ( phone.length > 30 ) {
        alert('Номер телефона должен быть меньше 30 цифр');
        return false;
    }
    
    if ( phone == '' && only == 't' ) {
        alert('Не оставляйте "Только по SMS" при пустом телефоне');
        return false;
    }
    
    xajax_updateSafetyPhone( uid, phone, only, finance );
    
    return false;
}

function setSafetyIpForm( uid ) {
    $('safety_ip_show'+uid).setStyle('display', 'none');
    $('safety_ip_edit'+uid).setStyle('display', '');
}

function unsetSafetyIpForm( uid ) {
    $('safety_ip'+uid).set('value', $('safety_ip_value'+uid).get('html'));
    $('safety_ip_show'+uid).setStyle('display', '');
    $('safety_ip_edit'+uid).setStyle('display', 'none');
}

function updateSafetyIp( uid ) {
    var ip = $('safety_ip'+uid).get('value');
    
    xajax_updateSafetyIp( uid, ip );
    
    return false;
}

////////////////////////

function setEmailForm( uid ) {
    $('email_show'+uid).setStyle('display', 'none');
    $('email_edit'+uid).setStyle('display', '');
}

function unsetEmailForm( uid ) {
    $('email'+uid).set('value', $('email_value'+uid).get('html'));
    $('email_show'+uid).setStyle('display', '');
    $('email_edit'+uid).setStyle('display', 'none');
}

function updateEmail( uid ) {
    var email = $('email'+uid).get('value');
    
    if ( email.match(/^[A-z0-9_\\.-]+[@][A-z0-9_-]+([.][A-z0-9_-]+)*[.][A-z]{2,4}$/)==null) {
        alert('Вы ввели email в недопустимом формате');
        return false;
    }
    else {
        xajax_updateEmail( uid, email );
    }
    
    return false;
}

////////////////////////

function upPopValue( uid ) {
    xajax_updatePop( uid, parseInt($('pop_input_'+uid).get('value')) + 1 );
}

function downPopValue( uid ) {
    xajax_updatePop( uid, parseInt($('pop_input_'+uid).get('value')) - 1 );
}

function setPopForm( uid ) {
    $('pop_show'+uid).setStyle('display', 'none');
    $('pop_edit'+uid).setStyle('display', '');
    $('pop_input_'+uid).set( 'disabled', false );
}

function unsetPopForm( uid ) {
    $('pop_input_'+uid).set('value', $('pop'+uid).get('html'));
    $('pop_show'+uid).setStyle('display', '');
    $('pop_edit'+uid).setStyle('display', 'none');
}

function updatePop( uid ) {
    var pop = $('pop_input_'+uid).get('value');
    
    if ( pop.match(/^(-)?[0-9]+$/)==null) {
        alert('Вы ввели отношение в недопустимом формате');
        return false;
    }
    else {
        $('pop_input_'+uid).set( 'disabled', true );
        xajax_updatePop( uid, pop );
    }
    
    return false;
}

window.addEvent('domready', function() {
    $$("input[id^='pop_input_']").addEvent('keydown',function(e) {
        if ( e.key == 'enter' ) {
            updatePop( this.id.replace('pop_input_', '') );
        }
    });
});