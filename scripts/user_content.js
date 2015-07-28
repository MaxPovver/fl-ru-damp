var user_content = {
    spinner: null,
    currUid: 0,
    
    setUserWarns: function( user_id, warns ) {
        Array.each( $(document.body).getElements('iframe'), function(obj, index) {
            if ( warns > 0 ) {
                obj.contentWindow.$$('span[id^="warn_'+ user_id +'"]').set('html', warns);
            }
            else {
                obj.contentWindow.$$('span[id^="warn_'+ user_id +'"]').getParent().getParent().addClass('user-notice');
                obj.contentWindow.$$('span[id^="warn_'+ user_id +'"]').getParent().getParent().set('html', 'Предупреждений нет');
            }
        });
    },
    
    getUserWarns: function( user_id ) {
        xajax_getUserWarns( user_id, 'admin', 'streams' );
    },
    
    tabMenuItems: new Array(),

    tabMenu: function( content_id, stream_id, active ) {
        if ( active > 0 ) {
            if ( !$('a_mass_'+stream_id).hasClass('b-button_disabled') ) {
                $('a_mass_'+stream_id).addClass('b-button_disabled');
            }
            
            $('a_mass_'+stream_id).onclick = function() { return false; };
            $('check_'+stream_id).set('disabled', true);
            $('check_'+stream_id).set('checked', false);
        }
        else {
            if ( $('a_mass_'+stream_id).hasClass('b-button_disabled') ) {
                $('a_mass_'+stream_id).removeClass('b-button_disabled');
            }
            
            $('a_mass_'+stream_id).onclick = function(idx){return function(){$(idx).contentWindow.user_content.mass_submit();}}(stream_id);
            $('a_mass_'+stream_id).set('disabled', false);
            $('check_'+stream_id).set('disabled', false);
        }
        
        var max = this.tabMenuItems[stream_id].length;
        $$("li[id^='stream_" + stream_id + "_tab_i']").removeClass('b-menu__item_active');
        $('stream_' + stream_id + '_tab_i'+active).addClass('b-menu__item_active');
        
        for ( var i = 0; i < max; i++ ) {
            if ( this.tabMenuItems[stream_id][i] == '' ) {
                continue;
            }
            
            if ( active == i ) {
                $('stream_' + stream_id + '_tab_i'+i).set('html', '<span class="b-menu__b1"><span class="b-menu__b2">'+ this.tabMenuItems[stream_id][i] +'</span></span>');
            }
            else {
                $('stream_' + stream_id + '_tab_i'+i).set('html', '<a class="b-menu__link" href="javascript:void(0);" onClick="user_content.tabMenu('+ content_id +', \''+ stream_id +'\', '+ i +'); return false;">'+ this.tabMenuItems[stream_id][i] +'</a>');
            }
        }

        $(stream_id).set('src', '/siteadmin/user_content/?site=stream&cid=' + content_id + '&sid=' + stream_id + '&status=' + active);
    },
    
    blockedLogin: '',
    blockedLoginEx: '',
    blockedFrom: '',
    blockedTo: '',

    unblock: function( sid, from_id ) {
        var isSent = $('is_sent_'+sid).get('value');
        this.spinner.show();
        xajax_unblock( sid, from_id, isSent );
    },
    
    mass_check: function( obj, stream_id ) {
        var bChecked = obj.checked;
        $(stream_id).contentWindow.$$('input[id^="mass_sid_"]').set('checked', bChecked);
    },
    
    mass_submit: function() {
        var cnt    = 0;
        var sid    = new Array();
        var uid    = new Array();
        var isSent = new Array();
        var currId = '';
        var height = 0;
        
        Array.each( $$("input[id^='mass_sid_']"), function(obj, index) {
            if ( obj.checked ) {
                sid[cnt]    = obj.get('value');
                uid[cnt]    = $('uid_'+sid[cnt]).get('value');
                isSent[cnt] = $('is_sent_'+sid[cnt]).get('value');
                currId      = obj.get('id').replace('mass_sid_', '');
                height      = height + $('my_div_content_'+currId).getCoordinates()['height'] + 20;
                cnt++;
            }
        });

        if ( cnt == 0 ) {
            alert('Необходимо выбрать хотя бы одну запись');
            parent.$('check_'+this.streamId).set('checked', false);
            return false;
        }

        if ( confirm('Вы действительно хотите утвердить выделенные записи?') ) { 
            this.spinner.show();
            var nextId = $('my_div_content_'+currId).getNext();
            
            this.scrollPosition = nextId ? nextId.getPosition()['y'] - height : cnt == this.contentCnt ? 0 : $('my_div_content_'+currId).getPosition()['y'] - height;
            
            if ( this.status == 0 ) {
                $('my_div_contents').grab(this.getWait());
            }
            
            xajax_massApproveContent( this.contentID, this.streamId, JSON.encode(sid), JSON.encode(uid), this.contentCnt, this.status, JSON.encode(isSent) );
        }
    },
    
    getBlockedLetters: function() {
        $('my_div_contents').grab(this.getWait());
        this.spinner.show();
        xajax_getBlockedLetters( this.getLettersFid, this.getLettersTid, this.getLettersPage );
    },
    
    updateLetter: function( sid, from_id, action ) {
        this.spinner.show();
        xajax_updateLetter( sid, from_id, action );
    },
    
    approveLetter: function( sid, from_id ) {
        this.spinner.show();
        xajax_approveLetter( sid, from_id );
    },
    
    clearBlockedFilter: function() {
        var date    = new Date();
        $('login').set('value', '');
        $('login_ex').set('checked', false);
        ComboboxManager.getInput('date_from').setDate(date.toISOString().substr(0,10));
        ComboboxManager.getInput('date_to').setDate(date.toISOString().substr(0,10));
    },
    
    setBlockedFilter: function() {
        var bValid          = false;
        this.blockedLogin   = $('login').get('value');
        this.blockedLoginEx = $('login_ex').get('checked') ? 'ex' : '';
        this.blockedFrom    = $('date_from_eng_format').get('value');
        this.blockedTo      = $('date_to_eng_format').get('value');
        
        if ( this.blockedFrom && this.blockedTo && this.blockedFrom <= this.blockedTo ) {
            bValid    = true;
        }
        
        if ( bValid ) {
            $('my_div_contents').set('html', '');
            this.lastID = '2147483647';
            this.getBlocked();
        }
        else {
            alert('Укажите корректные начальную и конечную даты');
        }
    },

    getBlocked: function() {
        $('my_div_contents').grab(this.getWait());
        this.spinner.show();
        xajax_getBlocked( this.contentID, this.blockedLogin, this.blockedLoginEx, this.blockedFrom, this.blockedTo, this.lastID );
    },
    
    getLettersPage: 1,
    getLettersFid: 0,
    getLettersTid: 0,
    getLettersMid: 0,
    
    getLetters: function() {
        $('my_div_contents').grab(this.getWait());
        this.spinner.show();
        xajax_getLetters( this.streamId, this.getLettersFid, this.getLettersTid, this.getLettersMid, this.getLettersPage );
    },
    
    delLetter: function( from_id, sid ) {
        this.spinner.show();
        xajax_delLetter( this.streamId, from_id, sid );
    },
    
    status: 0,
    lastID: 0,
    contentID: 0,
    contentPP: 0,
    contentCnt: 0,
    streamId: 0,
    scrollPosition: 0,
    scrollFunction: 'getContents',
    scrollWindow: window,
    scrollContent: '',
    playSoundOn: true,
    playSoundFlag: false,
    playSoundObj: null,
    playSoundUrl: '/siteadmin/user_content/sounds/std_snd.wav',
    
    addSoundControl: function( streamId ) {
        $('sound-control-'+streamId).set('title', 'Выключить звук');
        $('sound-control-'+streamId).setStyles({display: 'block', width: 18, height: 18, margin: 0, background: 'url(/webim/images/sound-off.gif) no-repeat 0 0'});
        $('sound-control-'+streamId).onclick = function(streamId){return function(){user_content.switchSound(streamId);}}(streamId);
    },
    
    switchSound: function( streamId ) {
        var playSoundOn = $(streamId).contentWindow.user_content.playSoundOn;
        var title = playSoundOn ? 'Включить звук' : 'Выключить звук';
        var icon  = playSoundOn ? 'sound-on.gif' : 'sound-off.gif';
        $(streamId).contentWindow.user_content.playSoundOn = !playSoundOn;
        $('sound-control-'+streamId).set('title', title);
        $('sound-control-'+streamId).setStyle('background', 'url(/webim/images/'+ icon +') no-repeat 0 0');
    },
    
    playSound: function() {
        if ( this.playSoundOn && this.playSoundFlag ) {
            this.playSoundFlag = false;
            var playSoundObj;
            
            if ( this.playSoundObj ) {
                document.body.removeChild( this.playSoundObj );
            }
            
            if ( navigator.userAgent.indexOf("MSIE") > -1 ) {
                playSoundObj = document.createElement( "bgsound" );
                playSoundObj.setAttribute( 'id', 'webim-sound-object' );
                playSoundObj.setAttribute( 'name', 'webim-sound-object' );
                playSoundObj.setAttribute( 'loop', '0' );
            } 
            else {
                playSoundObj = document.createElement( "div" );
                playSoundObj.setAttribute( 'id', 'webim-sound-object' );
            }
            
            document.body.appendChild( playSoundObj );
            this.playSoundObj = playSoundObj;
            
            var ot = "application/x-mplayer2";
            var userAgent = navigator.userAgent.toLowerCase();
            if (navigator.mimeTypes && userAgent.indexOf("windows") == -1) {
                var plugin = navigator.mimeTypes["audio/mpeg"].pt;
                if (plugin || userAgent.indexOf("opera") >= 0) {
                    ot = "audio/mpeg";
                }
            }
            
            var obj = $("webim-sound-object");
            
            if ( navigator.userAgent.indexOf("MSIE") > -1 ) {
                obj.src = this.playSoundUrl;
            }
            else {
                if ( !!document.createElement('audio').canPlayType('audio/wav') ) {
                    obj.innerHTML = '<audio autoplay><source src="' + this.playSoundUrl + '"></audio>';
                }
                else {
                    obj.innerHTML = '<embed type="' + ot + '" src="' + this.playSoundUrl + '" loop="0" autostart="1" width="0" height="0">';
                }
            }
        }
    },
    
    addContext: function( sid ) {
        if ( ( typeof(parent.banned) != 'undefined' && parent.banned != null ) ) {
            parent.banned.addContext(
                'moder', 
                $('ccode_'+sid).get('value'), 
                $('curl_'+sid).get('value'), 
                $('ctitle_'+sid).get('value')
            );
        }
    },
    
    afterScroll: function() {
        $(user_content.scrollWindow).scrollTo(0, this.scrollPosition);
        $(user_content.scrollWindow).addEvent('scroll', user_content.onScroll);
    },
    
    onScroll: function() {
        var h = user_content.scrollContent == '' ? $(document.body).clientHeight : $(user_content.scrollContent).clientHeight;
        var w = typeof(user_content.scrollWindow) == 'string' ? $(user_content.scrollWindow).clientHeight : $(user_content.scrollWindow).innerHeight;
        var c = Math.ceil(h/100*10);
        if ( h - w <= $(user_content.scrollWindow).getScroll()['y'] + c ) {
            user_content.scrollPosition = $(user_content.scrollWindow).getScroll()['y'];
            $(user_content.scrollWindow).removeEvent("scroll", user_content.onScroll);
            user_content[user_content.scrollFunction]();
        }
    },
    
    resolveSid: '', 
    resolveUid: 0, 
    resolveReason: '',
    
    resolveAndBan: function( sid, action, uid ) {
        this.resolveSid = sid;
        this.resolveUid = uid;

        xajax_resolveAndBan( this.contentID, this.streamId, sid, action, uid );
    },
    
    resolveContent: function( sid, action, uid ) {
        this.spinner.show();

        if ( this.status == 0 ) {
            $('my_div_contents').grab(this.getWait());
        }
        
        var isSent = $('is_sent_'+sid).get('value');
        
        xajax_resolveContent( this.contentID, this.streamId, sid, action, uid, this.contentCnt, this.status, isSent, this.resolveReason );
    },
    
    chooseContent: function() {
        new Request.JSON({
            url: '/xajax/user_content.server.php',
            onSuccess: function(resp) {
                if ( resp && resp.success ) {
                    user_content.getContents();
                }
                else if( resp && resp.div ) {
                    $('my_div_all').set('html', resp.div);
                }
            },
            onError: function(text, error) {
                parent.window.location.reload(true);
            },
            onFailure: function(xhr) {
                parent.window.location.reload(true);
            }
        }).post({
           'xjxfun': 'chooseContent',
           'xjxargs': ['N' + this.contentID, 'S'+this.streamId],
           'u_token_key': _TOKEN_KEY
        });;
    },
    
    getContents: function() {
        $('my_div_contents').grab(this.getWait());
        this.spinner.show();
        xajax_getContents(this.contentID, this.streamId, this.status, this.lastID, this.contentCnt);
    },
    
    releaseStream: function( contentID, streamId ) {
        if ( confirm('Вы действительно хотите закрыть поток?') ) {
            this.spinner.show();
            xajax_releaseStream(contentID, streamId);
        }
    },
    
    getWait: function() {
        $$("div[id^='my_div_wait']").destroy();
        var wait = new Element('div', {id: 'my_div_wait', 'class': 'b-post b-post_pad_10_15_15'}).grab(
            new Element('div', {'class': 'b-post__body'}).grab(
                new Element('div', {'class': 'b-post__txt', 
                    'html': '<img class="b-post__pic b-post__pic_margright_10 b-post__pic_float_left" src="/images/loading-white.gif" alt="" />Загрузка новых записей'
                })
            )
        );
        
        return wait;
    },
    
    updateStreamsTimeout: 5,
    updateStreamsTimeoutId: null,
    framesWnd: null,
    
    chooseStream: function( content_id, stream_id ) {
        /* занять или перехватить поток */
        var a = $(stream_id);
        var admin_id = a.retrieve('admin_id');

        if ( !admin_id || confirm('Перехватить поток?') ) {
            $(stream_id).onclick = null;

            if ( !this.framesWnd || this.framesWnd.closed ) {
                //var url = '/siteadmin/user_content/?site=frames&mode=choose&cid=' + content_id + '&sid=' + stream_id;
                var url = '/siteadmin/user_content/?site=frames';
                Cookie.write('my_streams_content_id', content_id);
                Cookie.write('my_streams_stream_id', stream_id);
                this.framesWnd = window.open( url, 'FRMS', 'location=no,scrollbars=yes,resizable=yes' );
                this.framesWnd.focus();
                this.framesWnd.opener = window;
            }
            else {
                this.framesWnd.focus();
                this.framesWnd.xajax_chooseStream( content_id, stream_id, 0 );
            }
        }
    },
    
    updateStreams: function() {
        new Request.JSON({
            url: '/xajax/user_content.server.php',
            onSuccess: function(resp) {
                if ( resp && resp.success ) {
                    var stream = null, a = null, span = null, color = '', title = '';
                    var chosen = new Array();

                    for ( var content_id in resp.update ) {
                        $('contents_'+content_id).empty();
                        chosen[content_id] = 0;
                        
                        if ( resp.update[content_id].length > 0 ) {
                            for ( var i = 0; i < resp.update[content_id].length; i++ ) {
                                stream = resp.update[content_id][i];
                                color = stream.admin_id ? (stream.admin_id == user_content.currUid ? 'disabled b-button_flat_green' : 'flat_red') : 'flat_green';
                                title = stream.admin_id ? (stream.admin_id == user_content.currUid ? 'Занят вами' : stream.admin_name) + (resp.streams && typeof(resp.streams[stream.stream_id]) != 'undefined' ? ' ('+resp.streams[stream.stream_id]+')' : '') : 'Занять';
                                a = new Element('a', {'id': stream.stream_id, 'href': 'javascript:void(0);', 'class': 'b-button b-button_margbot_10 b-button_margright_10 b-button_flat b-button_'+color});
                                span = new Element('span', {'html': (i+1)+'. ' + title, 'class': 'b-button__txt b-button__txt_minwidth_110 b-button__txt_block b-button__txt_align_left'});
                                a.grab(new Element('span', {'class': 'b-button__b1'}).grab(new Element('span', {'class': 'b-button__b2'}).grab(span)));
                                
                                if ( color != 'disable' ) {
                                    a.onclick = function(p1, p2){return function(){user_content.chooseStream(p1, p2);}}(content_id, stream.stream_id);
                                    a.store('admin_id', stream.admin_id);
                                }
                                
                                if ( stream.admin_id ) {
                                    chosen[content_id]++;
                                }
                                
                                $('contents_'+content_id).grab(a);
                            }
                        }
                        else {
                            $('contents_'+content_id).set('html', '<span>В данное время нет ни одного потока ни у одной смены</span>');
                        }
                    }
                    
                    if ( resp.queue ) {
                        for ( var content_id in resp.queue ) {
                            if ( content_id != 'update' && $('queueCounts_'+content_id) && !chosen[content_id] ) {
                                $('queueCounts_'+content_id).set('html', ' (в очереди - ' + resp.queue[content_id] + ')');
                            }
                            else if ( content_id != 'update' && $('queueCounts_'+content_id) ) {
                                $('queueCounts_'+content_id).set('html', '');
                            }
                        }
                    }

                    $$('div.my_contents a.b-button').addEvent('mousedown', function() {
                        this.addClass('b-button_active');
                    }).addEvent('mouseup', function() {
                        this.removeClass('b-button_active');
                    }).addEvent('mouseleave', function() {
                        this.fireEvent('mouseup');
                    });
                    
                    user_content.updateStreamsTimeoutId = setTimeout('user_content.updateStreams()', user_content.updateStreamsTimeout*1000);
                }
                else {
                    window.location = '/siteadmin/user_content/?site=choose';
                }
            },
            onError: function(text, error) {
                window.location.reload(true);
            },
            onFailure: function(xhr) {
                window.location.reload(true);
            }
        }).post({
           'xjxfun': 'updateStreamsForUser',
           'xjxargs': ['N' + this.currUid],
           'u_token_key': _TOKEN_KEY
        });
    },
    
    /*
     * Количество потоков в сменах
     */
     
    submitShiftsContents: function() {
        var regex = /^[\d]+$/;
        var valid = true;
        
        $$('#form_streams input.b-combo__input-text').each(function(item, index){
            item.getParent().removeClass('b-combo__input_error');
            var val = item.get('value');
            
            if ( !regex.test(val) || parseInt(val) < 1 || parseInt(val) > 32767 ) {
                item.getParent().addClass('b-combo__input_error');
                valid = false;
            }
        });
        
        if ( !valid ) {
            alert('Кол-во потоков должно быть целым числом от 1 до 32767');
        }
        else {
            $('form_streams').submit();
        }
        
        return valid;
    },
    
    upShiftsContents: function(obj) {
        this.changeShiftsContents(obj, 1);
    },
    
    downShiftsContents: function(obj) {
        this.changeShiftsContents(obj, -1);
    },
    
    changeShiftsContents: function( obj, diff ) {
        var input = obj.getParent().getFirst('div').getFirst('div').getFirst('input');
        
        if ( input ) {
            var val   = input.get('value');
            var regex = /^[\d]+$/;
            
            if ( regex.test(val) ) {
                var intval = parseInt( val );
                if ( (diff > 0 && intval < 32767) || (diff < 0 && intval > 1) ) {
                    input.set('value', (intval + diff));
                }
            }
            else {
                alert('Кол-во потоков должно быть целым числом от 1 до 32767');
            }
        }
    },
    
    /*
     * Смены
     */

    shiftCnt: 0,
    
    addShift: function() {
        this.shiftCnt++;
        var block = new Element('div', {'class': 'b-layout__txt b-layout__txt_padbot_15 i-button my-shift', 'id': 'div_add'+this.shiftCnt});
        block.grab(new Element('span', {'html': this.shiftCnt})).grab(new Element('span', {'html': ' смена работает с '}));
        ComboboxManager.append(block, '');
        block.getFirst('div').addClass('b-combo_inline-block b-combo_margtop_-5');
        block.getFirst('div').getFirst('div').removeClass('b-combo__input_current').addClass('b-combo__input_width_35');
        block.getFirst('div').getFirst('div').getFirst('input').set('name', 'add_from[]');
        block.grab(new Element('span', {'html': '&#160;&#160;до&#160;&#160;'}));
        ComboboxManager.append(block, '');
        block.getLast('div').addClass('b-combo_inline-block b-combo_margtop_-5');
        block.getLast('div').getFirst('div').removeClass('b-combo__input_current').addClass('b-combo__input_width_35');
        block.getLast('div').getFirst('div').getFirst('input').set('name', 'add_to[]');
        block.grab(new Element('span', {'html': '&#160;&#160;'}));
        var a = new Element('a', {'href': 'javascript:void(0);', 'class': 'b-button b-button_margtop_-5 b-button_admin_del'});
        a.onclick = function(idx){return function(){user_content.delShift(idx);}}('div_add'+this.shiftCnt);
        block.getLast('span').grab(a);
        $('div_shifts').grab(block);
        $('add_shift_cnt').set('html', this.shiftCnt+1);
    },

    delShift: function(idx) {
        $(idx).destroy();
        $$('.my-shift').each(function(item, index){
            item.getFirst('span').set('html', index+1);
        });
        this.shiftCnt--;
        $('add_shift_cnt').set('html', this.shiftCnt+1);
    },

    delShiftEx: function(id) {
        $('ex_id'+id).destroy();
        $('form_shifts').grab(new Element('input', {'type': 'hidden', 'name': 'del_id[]', 'value': id}));
        this.delShift('div_ex_'+id);
    },

    submitShifts: function() {
        var regex = /^(([0-1][0-9])|([2][0-3])):([0-5][0-9])$/;
        var valid = true;
        var exist = false;

        $$('#form_shifts input.b-combo__input-text').each(function(item, index){
            exist = true;
            item.getParent().removeClass('b-combo__input_error');
            
            if ( !regex.test(item.get('value')) ) {
                item.getParent().addClass('b-combo__input_error');
                valid = false;
            }
        });

        if ( !valid ) {
            alert('Не все смены указаны корректно');
        }
        else {
            if ( !exist ) {
                if ( !confirm('Отсутствие смен делает модерирование не возможным.\nВсе равно продолжить?') ) {
                    return false;
                }
            }
            
            $('form_shifts').submit();
        }
        
        return valid;
    },
    
    
    /*
     * Проекты
     */
    
    make_vacancy: function(id)
    {
        this.spinner.show();
        xajax_makeVacancy(this.streamId, id);
    }
    
};