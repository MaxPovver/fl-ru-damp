var banned = {

    buffer: {},
    reasons: {},
    banUid: '', 
    banNoSend: 0, // 1 - не отправлять уведомления о банах
    reload: 0, // 1 - после действия перезагрузить страницу
    context: {}, // контекст действия для лога
    zero: false, // буффер обмена
    isMSIEless9: ((navigator.userAgent.indexOf("MSIE 8.0") != -1)||(navigator.userAgent.indexOf("MSIE 7.0") != -1)),
    zeroLeft: 0,
    
    // TODO: сделать глобальной или сделать готовый плагин для выбора даты
    updateDays: function( idDays, idMonths, idYears ) {
        var aDaysNum = new Array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
        var oDays    = $(idDays);
        var oMonths  = $(idMonths);
        var oYears   = $(idYears);
        
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
    },
    
    zeroHide: function() {
        $$('div[id^=ZeroClipboardDiv_]').each( function(obj) {
            obj.store('objGetStyleLeft', obj.getStyle('left'));
            obj.setStyle('left', '-999px' );
        });
    },
    
    zeroShow: function() {
        $$('div[id^=ZeroClipboardDiv_]').each( function(obj,index) {
            obj.setStyle('left', obj.retrieve('objGetStyleLeft'));
        });
    },
    
    addContext: function( uid, code, link, name ) {
        this.context[uid] = { 
            uid: uid,
            link: link,
            code: code,
            name: name
        };
    }, 
    
	reasonHTML: function(uniqId, buttonName) {
		return '<div style="width: 100%; margin-top: 10px">\
			<b>Причина:</b>\
			<br />\
			<div><textarea id="bfrm_'+uniqId+'" name="bfrm_'+uniqId+'" style="width: 100%; height: 40px"></textarea></div>\
			<br />\
			<div style="text-align: right">\
                <input type="button" value="Отмена" onclick="banned.commit(\''+uniqId+'\', (banned.buffer[\''+uniqId+'\'].action = \'close\'))">\
                <input type="button" value="'+buttonName+'" onclick="banned.commit(\''+uniqId+'\', document.getElementById(\'bfrm_'+uniqId+'\').value)">\
			</div>\
			</div>\
		';
	},
	
	selectReasonHTML: function( uniqId, title, button ) {
        return '<div class="b-fon b-fon_clear_both b-fon_padtop_10 b-fon_bg_fcc b-fon_width_full">\
        	<b class="b-fon__b1"></b>\
        	<b class="b-fon__b2"></b>\
        	<div class="b-fon__body b-fon__body_pad_5_10">\
        		<h4 class="b-layout__h4 b-layout__h4_bold">' + title + '</h4>\
        		<div id="bfrm_div_sel_'+uniqId+'" class="b-select b-select_padbot_10">\
        			<select id="bfrm_sel_'+uniqId+'" name="bfrm_sel_'+uniqId+'" class="b-select__select b-select__select_width_full" disabled="disabled">\
        				<option>Подождите...</option>\
        			</select>\
        		</div>\
        		<div class="b-textarea b-textarea_margbot_10">\
        			<textarea id="bfrm_'+uniqId+'" name="bfrm_'+uniqId+'" rows="5" cols="20" class="b-textarea__textarea" disabled="disabled"></textarea>\
        		</div>\
        		<div class="b-buttons">\
        			<input type="button" id="bfrm_btn_'+uniqId+'" disabled onclick="banned.commit(\''+uniqId+'\', document.getElementById(\'bfrm_'+uniqId+'\').value)" value="'+ button +'" class="">&nbsp;\
        			<a href="#" onclick="banned.commit(\''+uniqId+'\', (banned.buffer[\''+uniqId+'\'].action = \'close\')); return false;" class="b-buttons__link b-buttons__link_dot_666">Отменить</a>\
        		</div>\
        	</div>\
        	<b class="b-fon__b2"></b>\
        	<b class="b-fon__b1"></b>\
        </div>\
        ';
	},
	
	adjustReasonHTML: function( uniqId ) {
        var field        = $('bfrm_sel_' + uniqId);
        var dim = field.getParent().getParent().getSize();
        var borderLeft   = parseInt(field.getStyle('border-left-width'));
        var borderRight  = parseInt(field.getStyle('border-right-width'));
        var paddingLeft  = parseInt(field.getStyle('padding-left'));
        var paddingRight = parseInt(field.getStyle('padding-right'));
        var marginLeft   = parseInt(field.getStyle('margin-left'));
        var marginRight  = parseInt(field.getStyle('margin-right'));
        if (/Safari/.test(navigator.userAgent)) {
            var styleWidth   = dim.x - borderLeft - borderRight - marginLeft - marginRight - 20;
        }
        else {
            var styleWidth   = dim.x - borderLeft - borderRight - paddingLeft - paddingRight - marginLeft - marginRight - 20;
        }
        
        field.setStyle('width', styleWidth );
        
        field        = $('bfrm_'+uniqId);
        borderLeft   = parseInt(field.getStyle('border-left-width'));
        borderRight  = parseInt(field.getStyle('border-right-width'));
        paddingLeft  = parseInt(field.getStyle('padding-left'));
        paddingRight = parseInt(field.getStyle('padding-right'));
        marginLeft   = parseInt(field.getStyle('margin-left'));
        marginRight  = parseInt(field.getStyle('margin-right'));
        styleWidth   = dim.x - borderLeft - borderRight - paddingLeft - paddingRight - marginLeft - marginRight - 20;
        
        field.setStyle('width', styleWidth );
	},
	
	setReason: function( uniqId ) {
	    var reasonId = $('bfrm_sel_'+uniqId).get('value');
        var selIdx   = $('bfrm_sel_'+uniqId).selectedIndex;
        var selText  = $('bfrm_sel_'+uniqId).options[selIdx].text;
        var actId    = ( typeof(banned.buffer[uniqId].act_id) != 'undefined' ) ? banned.buffer[uniqId].act_id : '';
        
	    if ( reasonId != '' ) {
	        if ( actId ) {
                if ( this.buffer[uniqId].reasonId[actId] == '' ) {
                    this.buffer[uniqId].customReason[actId] = $('bfrm_' + uniqId ).get( 'value' );
                }
	        }
	        else {
	            if ( this.buffer[uniqId].reasonId == '' ) {
                    this.buffer[uniqId].customReason = $('bfrm_' + uniqId ).get( 'value' );
                }
	        }
	        
            $('bfrm_' + uniqId).set( 'readonly', true );
            
            if ( typeof this.reasons[reasonId] != 'undefined' ) {
                $('bfrm_' + uniqId ).set( 'value', this.reasons[reasonId] );
            }
            else {
                xajax_getAdminActionReasonText( uniqId, reasonId );
            }
	    }
	    else {
	        if ( actId ) {
                $('bfrm_' + uniqId ).set( 'value', this.buffer[uniqId].customReason[actId] );
	        }
	        else {
	            $('bfrm_' + uniqId ).set( 'value', this.buffer[uniqId].customReason );
	        }
	        $('bfrm_' + uniqId).set( 'readonly', false );
	    }
	    
	    if ( actId ) {
    	    this.buffer[uniqId].reasonId[actId]   = reasonId;
    	    this.buffer[uniqId].reasonName[actId] = selText;
	    }
	    else {
	        this.buffer[uniqId].reasonId   = reasonId;
    	    this.buffer[uniqId].reasonName = selText;
	    }
	},

    setReasonStream: function( uniqId ) {
        var reasonId = $('bfrm_sel_stream_'+uniqId).get('value');
        var selIdx   = $('bfrm_sel_stream_'+uniqId).selectedIndex;
        var selText  = $('bfrm_sel_stream_'+uniqId).options[selIdx].text;
        var actId    = ( typeof(banned.buffer[uniqId].act_id) != 'undefined' ) ? banned.buffer[uniqId].act_id : '';
        
        if ( reasonId != '' ) {
            xajax_getAdminActionReasonTextStream( uniqId, reasonId );
        }
        else {
            $('bfrm_stream_' + uniqId ).set( 'value', '' );
        }
    },

    setDelReason: function( uniqId ) {
        var reasonId = $('bfrm_sel_'+uniqId).get('value');
        var selIdx   = $('bfrm_sel_'+uniqId).selectedIndex;
        var selText  = $('bfrm_sel_'+uniqId).options[selIdx].text;
        var actId    = ( typeof(banned.buffer[uniqId].act_id) != 'undefined' ) ? banned.buffer[uniqId].act_id : '';
        
        if ( reasonId != '' ) {
            xajax_getAdminActionReasonTextDel( uniqId, reasonId );
        }
        else {
            $('bfrm_' + uniqId ).set( 'value', '' );
        }
    },
	
	saveReason: function( uniqId ) {
	    var reasonId = $('bfrm_sel_'+uniqId).get('value');
        var selIdx   = $('bfrm_sel_'+uniqId).selectedIndex;
        var selText  = $('bfrm_sel_'+uniqId).options[selIdx].text;
        var actId    = ( typeof(this.buffer[uniqId].act_id) != 'undefined' ) ? this.buffer[uniqId].act_id : '';
        
        if ( actId ) {
    	    this.buffer[uniqId].reasonId[actId]   = reasonId;
    	    this.buffer[uniqId].reasonName[actId] = selText;
    	    
    	    if ( reasonId == '' ) {
    	        this.buffer[uniqId].customReason[actId] = $('bfrm_' + uniqId ).get( 'value' );
    	    }
	    }
	    else {
	        this.buffer[uniqId].reasonId   = reasonId;
    	    this.buffer[uniqId].reasonName = selText;
    	    
    	    if ( reasonId == '' ) {
    	        this.buffer[uniqId].customReason = $('bfrm_' + uniqId ).get( 'value' );
    	    }
	    }
	},
    
    warnUser: function( userId, warnId, draw_func, contextId, edit, streamType ) {
	    var div       = document.getElementById('ov-notice');
	    var uniqId    = 'warnUser' + userId;
	    var context   = ( typeof(this.context[contextId]) != 'undefined' && this.context[contextId] != null ) ? this.context[contextId] : '';
        
        this.buffer[uniqId] = { 
            action: 'warnUser', 
            divObj: div, 
            divHTML: div.innerHTML,
            userId: userId, 
            warnId: warnId, 
            draw_func: draw_func, 
            context: context,
            customReason: new Array(),
            reasonId: new Array(),
            reasonName: new Array()
        };
        
        if ( this.zero ) this.zeroHide();

        if(contextId=='moder') {
            $('warn_div_stream').getParent().setStyle('display', '');
            $('warn_delreason_title').setStyle('display', '');
        }

        $$("#ov-notice").toggleClass('b-shadow_hide');
        
        if ( $('ban_btn') ) {
            $('ban_btn').addClass('b-button_rectangle_color_green').removeClass('b-button_rectangle_color_disable');
        }
        
        xajax_setUserWarnForm( userId, warnId, edit, contextId, streamType );
    },
    
    warnUserNew: function( userId, warnId, draw_func, contextId, edit ) {
	    var div       = document.getElementById('ov-notice');
	    var uniqId    = 'warnUser' + userId;
	    var context   = ( typeof(this.context[contextId]) != 'undefined' && this.context[contextId] != null ) ? this.context[contextId] : '';
        
        this.buffer[uniqId] = { 
            action: 'warnUser', 
            divObj: div, 
            divHTML: div.innerHTML,
            userId: userId, 
            warnId: warnId, 
            draw_func: draw_func, 
            context: context,
            customReason: new Array(),
            reasonId: new Array(),
            reasonName: new Array()
        };
        
        if ( this.zero ) this.zeroHide();
        
        $$("div[id^='ov-notice']").setStyle('display', 'none');
        xajax_setUserWarnFormNew( userId, warnId, edit );
    },
    
	blockedThread: function(topicId) {
        var uniqId = "thread_"+topicId;
        var div = document.getElementById('thread-reason-'+topicId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedThread', 
            divObj: div, 
            divHTML: div.innerHTML,
            topicId: topicId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Заблокировать топик', 'Заблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 7, uniqId );
	},
	
	unblockedThread: function(topicId) {
		var uniqId = "thread_"+topicId;
        var div = document.getElementById('thread-reason-'+topicId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedThread', 
            divObj: div, 
            divHTML: div.innerHTML,
            divShow: true,
            topicId: topicId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Разблокировать топик', 'Разблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 8, uniqId );
	},

        unBlocked: function(uniqId){
            xajax_unBlocked(uniqId);
        },

	blockedProject: function(projectId) {
        var uniqId = "project_"+projectId;
        var div = document.getElementById('project-reason-'+projectId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedProject', 
            divObj: div, 
            divHTML: div.innerHTML,
            projectId: projectId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML( uniqId, 'Причина блокировки проекта', 'Заблокировать' );
        div.style.display = 'block';
        
        if (this.isMSIEless9) {
			var innerId = 'bfrm_div_sel_' + uniqId;
			var div = document.getElementById(innerId);			
			var parent = div.parentNode;			
			var links = parent.getElementsByTagName("a");						
			if (links.length > 0) {
				links[0].self = this;
				links[0].onclick = function() {					
					this.self.commit(uniqId, (this.self.buffer[uniqId].action = 'close')); return false;					
				}
			}
		}
        
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 9, uniqId );
	},

	blockedProjectWithComplains: function(projectId) {
        var uniqId = "project_"+projectId;
        var div = document.getElementById('project-reason-'+projectId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedProjectWithComplains', 
            divObj: div, 
            divHTML: div.innerHTML,
            projectId: projectId,
            customReason: '',
            reasonId: ''
        };
        
        div.innerHTML = this.selectReasonHTML( uniqId, 'Причина блокировки проекта', 'Заблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 9, uniqId );
	},

	unblockedProject: function(projectId) {
		var uniqId = "project_"+projectId;
        var div = document.getElementById('project-reason-'+projectId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedProject', 
            divObj: div, 
            divHTML: div.innerHTML,
            divShow: true,
            projectId: projectId,
            customReason: '',
            reasonId: ''
        };
        
        div.innerHTML = this.selectReasonHTML( uniqId, 'Причина разблокировки проекта', 'Разблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 10, uniqId );
	},
    
	blockedCommune: function(communeId) {
        var uniqId = "commune_"+communeId;
        var div = document.getElementById('commune-reason-'+communeId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedCommune', 
            divObj: div, 
            divHTML: div.innerHTML,
            communeId: communeId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Заблокировать сообщество', 'Заблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 11, uniqId );
	},
	
	unblockedCommune: function(communeId) {
		var uniqId = "commune_"+communeId;
        var div = document.getElementById('commune-reason-'+communeId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedCommune', 
            divObj: div, 
            divHTML: div.innerHTML,
            communeId: communeId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Разблокировать сообщество', 'Разблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 12, uniqId );
	},
	
	blockedCommuneTheme: function(communeId, themeId, msgId) {
        var uniqId = "theme_"+themeId;
        var div = document.getElementById('theme-reason-'+themeId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedCommuneTheme', 
            divObj: div, 
            divHTML: div.innerHTML,
            communeId: communeId,
            themeId: themeId,
            msgId: msgId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Заблокировать сообщение', 'Заблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 15, uniqId );
	},
	
	unblockedCommuneTheme: function(communeId, themeId, msgId) {
		var uniqId = "theme_"+themeId;
        var div = document.getElementById('theme-reason-'+themeId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedCommuneTheme', 
            divObj: div, 
            divHTML: div.innerHTML,
            divShow: true,
            communeId: communeId,
            themeId: themeId,
            msgId: msgId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Разблокировать сообщение', 'Разблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 16, uniqId );
	},
	
	blockedFreelanceOffer: function(offerId) {
        var uniqId = "freelance_offer_"+offerId;
        var div = document.getElementById('freelance-offer-block-'+offerId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedFreelanceOffer', 
            divObj: div, 
            divHTML: div.innerHTML,
            offerId: offerId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Заблокировать предложение', 'Заблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 13, uniqId );
	},
	
	unblockedFreelanceOffer: function(offerId) {
		var uniqId = "freelance_offer_"+offerId;
        var div = document.getElementById('freelance-offer-block-'+offerId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedFreelanceOffer', 
            divObj: div, 
            divHTML: div.innerHTML,
            offerId: offerId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Разблокировать предложение', 'Разблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 14, uniqId );
	},
    
    blockedProjectOffer: function(offerId, userId, projectId) {
        var uniqId = "project_offer_"+offerId;
        var div = document.getElementById('project-offer-block-'+offerId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedProjectOffer', 
            divObj: div, 
            divHTML: div.innerHTML,
            offerId: offerId,
            userId: userId,
            projectId: projectId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Заблокировать предложение', 'Заблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 27, uniqId );
	},
    
    unblockedProjectOffer: function(offerId, userId, projectId) {
		var uniqId = "project_offer_"+offerId;
        var div = document.getElementById('project-offer-block-'+offerId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedProjectOffer', 
            divObj: div, 
            divHTML: div.innerHTML,
            offerId: offerId,
            userId: userId,
            projectId: projectId,
            divShow: true,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Разблокировать предложение', 'Разблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 28, uniqId );
	},
	
	userBanToggle: function() {
	    var bOn = $('ban_none').get('checked');
        $('ban_to_date').set( 'disabled', bOn );
        $('ban_forever').set( 'disabled', bOn );
        
        if ( bOn ) {
            $('ban_day').set( 'disabled', bOn );
            $('ban_month').set( 'disabled', bOn );
            $('ban_year').set( 'disabled', bOn );
        }
        else {
            this.userBanToToggle();
        }
	},
	
	userBanToToggle: function() {
	    var bOn = $('ban_forever').get('checked');
        $('ban_day').set( 'disabled', bOn );
        $('ban_month').set( 'disabled', bOn );
        $('ban_year').set( 'disabled', bOn );
	},
	
	userBanNone: function(uniqId) {
	    this.saveReason( uniqId );
	    this.buffer[uniqId].act_id = 1;
	    xajax_getAdminActionReasons( 4, uniqId, this.buffer[uniqId].reasonId[1] );
	},
	
	userBanSite: function(uniqId) {
	    this.saveReason( uniqId );
	    this.buffer[uniqId].act_id = 3;
	    xajax_getAdminActionReasons( 3, uniqId, this.buffer[uniqId].reasonId[3] );
	},
	
	userBanBlog: function(uniqId) {
	    this.saveReason( uniqId );
	    this.buffer[uniqId].act_id = 5;
	    xajax_getAdminActionReasons( 5, uniqId, this.buffer[uniqId].reasonId[5] );
	},
	
	userBan: function(userId, contextId, edit, streamType) {
        var uniqId  = "userban_"+userId;
        var div     = document.getElementById('ov-notice22');
        var context = ( typeof(this.context[contextId]) != 'undefined' && this.context[contextId] != null ) ? this.context[contextId] : '';
        this.buffer[uniqId] = { 
            action: 'userBan', 
            divObj: div, 
            divHTML: div.innerHTML,
            userId: userId,
            context: context,
            customReason: new Array(),
            reasonId: new Array(),
            reasonName: new Array()
        };
        
        if ( this.zero ) this.zeroHide();
        
        $('ban_btn').set( 'disabled', false );
        $('ban_btn').set( 'value', 'Сохранить' );
        
        $$("div[id^='ov-notice']").setStyle('display', 'none');
        xajax_setUserBanForm( userId, edit, contextId, streamType );
	},
    
    blockedPortfolio: function( portfolioId ) {
        var uniqId = "portfolio_"+portfolioId;
        var div = document.getElementById('portfolio-block-'+portfolioId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedPortfolio', 
            divObj: div, 
            divHTML: div.innerHTML,
            portfolioId: portfolioId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Заблокировать работу', 'Заблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 29, uniqId );
	},
    
    unblockedPortfolio: function( portfolioId ) {
		var uniqId = "portfolio_"+portfolioId;
        var div = document.getElementById('portfolio-block-'+portfolioId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedPortfolio', 
            divObj: div, 
            divHTML: div.innerHTML,
            portfolioId: portfolioId,
            divShow: true,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Разблокировать работу', 'Разблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 30, uniqId );
	},
    
    blockedDialogue: function( dialogueId ) {
        var uniqId = "dialogue_"+dialogueId;
        var div = document.getElementById('dialogue-block-'+dialogueId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedDialogue', 
            divObj: div, 
            divHTML: div.innerHTML,
            dialogueId: dialogueId,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Заблокировать комментарий', 'Заблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 31, uniqId );
	},
    
    unblockedDialogue: function( dialogueId ) {
		var uniqId = "dialogue_"+dialogueId;
        var div = document.getElementById('dialogue-block-'+dialogueId);
        if (typeof this.buffer[uniqId] != 'undefined' && this.buffer[uniqId] != null) {
            this.commit(uniqId, (this.buffer[uniqId].action = 'close'));
            return;
        }
        this.buffer[uniqId] = { 
            action: 'blockedDialogue', 
            divObj: div, 
            divHTML: div.innerHTML,
            dialogueId: dialogueId,
            divShow: true,
            customReason: '',
            reasonId: ''
        };
        div.innerHTML = this.selectReasonHTML(uniqId, 'Разблокировать комментарий', 'Разблокировать' );
        div.style.display = 'block';
        this.adjustReasonHTML( uniqId );
        
        xajax_getAdminActionReasons( 32, uniqId );
	},

    delReason: function( sId, userId, sDrawFunc, objParams ) {
        var uniqId  = "delreason_"+sId;
        var div     = document.getElementById('ov-notice22-r');
        
        this.buffer[uniqId] = { 
            action: 'delReason', 
            divObj: div, 
            divHTML: div.innerHTML,
            sId: sId,
            userId: userId,
            sDrawFunc: sDrawFunc,
            objParams: objParams,
            customReason: new Array(),
            reasonId: new Array(),
            reasonName: new Array()
        };
        
        $$("div[id^='ov-notice']").setStyle('display', 'none');
        xajax_setDelReasonForm( sId, sDrawFunc, JSON.encode(objParams) );
    },
    
	commit: function(uniqId, reason, reasonStream) {
    /*
     * !!! после правок в этой функции проверьте все case из switch
     */
		if($('ban_btn') != undefined || $('ban_btn') != null) {
	        if ($('ban_btn').hasClass('b-button_rectangle_color_disable') ) {
	            return false;
	        }
	        else {
	            $('ban_btn').removeClass('b-button_rectangle_color_green');
	            $('ban_btn').addClass('b-button_rectangle_color_disable');
	        }
		}
		
        if (reason || this.buffer[uniqId].action == 'userBan' ) {
            if (typeof(reason) == "string") {
                reason = reason.replace(/&/g, "&amp;");
                reason = reason.replace(/"/g, "&quot;");
                reason = reason.replace(/'/g, "&#039;");
                reason = reason.replace(/</g, "&lt;");
                reason = reason.replace(/>/g, "&gt;");
            }

            if (this.buffer[uniqId].divHTML || this.isMSIEless9) {
                if (this.buffer[uniqId].action == 'close') {
                    this.buffer[uniqId].divObj.innerHTML = this.buffer[uniqId].divHTML;
                    if ( !this.buffer[uniqId].divShow )
                        this.buffer[uniqId].divObj.style.display = 'none';
                } else {
                    if ( this.buffer[uniqId].action != 'userBan' && this.buffer[uniqId].action != 'warnUser' && this.buffer[uniqId].action != 'delReason')
                        this.buffer[uniqId].divObj.innerHTML = '<div style="width: 100%; text-align: center"><img src="/images/load_fav_btn.gif" width="24" height="24" border="0"></div>';
                }
            }

            var is_usercontent_stream = false;
            if(typeof(banned.context.moder) != 'undefined') {
                if(typeof(reasonStream)=='undefined') reasonStream = '';
                is_usercontent_stream = true;
                reasonStream = ''+reasonStream+'';
                if (typeof(reasonReason) == "string") {
                    reasonStream = reasonStream.replace(/&/g, "&amp;");
                    reasonStream = reasonStream.replace(/"/g, "&quot;");
                    reasonStream = reasonStream.replace(/'/g, "&#039;");
                    reasonStream = reasonStream.replace(/</g, "&lt;");
                    reasonStream = reasonStream.replace(/>/g, "&gt;");
                }
            }

			switch (this.buffer[uniqId].action) {
			    case 'userBan':
                    $('ban_btn').set( 'disabled', true );
                    $('ban_btn').set( 'value', 'Подождите' );
			        if ( reason.replace(/(^\s+)|(\s+$)/g, "") == '' && this.buffer[uniqId].act_id > 1 ) {
			            alert('Необходимо указать причину!');
                        $('ban_btn').set( 'disabled', false );
                        $('ban_btn').set( 'value', 'Сохранить' );
                        return false;
			        } else {
                        if(is_usercontent_stream==true) {
                            if(reasonStream.replace(/(^\s+)|(\s+$)/g, "") == '') {
                                alert('Необходимо указать причину!');
                                $('ban_btn').set( 'disabled', false );
                                $('ban_btn').set( 'value', 'Сохранить' );
                                return false;
                            } else {
                                $(this.buffer[uniqId].streamId).contentWindow.user_content.resolveReason = reasonStream;
                            }
                        }
                    }

                    var date = '';
                    if ( $('ban_to_date').get('checked') ) {
                        date = $('ban_year').get('value') + '-' + $('ban_month').get('value') + '-' + $('ban_day').get('value');
                    }
                    var noticeSbrPartners = $('notice_sbr_partners') && $('notice_sbr_partners').get('checked');
                    if ( this.buffer[uniqId].context.uid == 'moder' ) {
                        $(this.buffer[uniqId].streamId).contentWindow.xajax_updateUserBan( JSON.encode([this.buffer[uniqId].userId]), this.buffer[uniqId].act_id, reason, this.buffer[uniqId].reasonId[this.buffer[uniqId].act_id], date, this.banNoSend, JSON.encode(this.buffer[uniqId].context), noticeSbrPartners );
                    }
                    else {
                        xajax_updateUserBan( JSON.encode([this.buffer[uniqId].userId]), this.buffer[uniqId].act_id, reason, this.buffer[uniqId].reasonId[this.buffer[uniqId].act_id], date, this.banNoSend, JSON.encode(this.buffer[uniqId].context), noticeSbrPartners );
                    }
                    break;
                case 'delReason':
                    var sDrawFunc = this.buffer[uniqId].sDrawFunc;
                    if ( sDrawFunc == 'stream0' || sDrawFunc == 'stream1' || sDrawFunc == 'stream2' || sDrawFunc == 'delLetter' ) {
                        var stream_id = this.buffer[uniqId].objParams.stream_id;
                        $(stream_id).contentWindow.xajax_setDeleted( this.buffer[uniqId].sId, this.buffer[uniqId].userId, reason, this.buffer[uniqId].sDrawFunc, JSON.encode(this.buffer[uniqId].objParams) );
                    }
                    else {
                        xajax_setDeleted( this.buffer[uniqId].sId, this.buffer[uniqId].userId, reason, this.buffer[uniqId].sDrawFunc, JSON.encode(this.buffer[uniqId].objParams) );
                    }
                    break;
                case 'blockedCommuneTheme': 
                    xajax_BlockedCommuneTheme(this.buffer[uniqId].communeId, this.buffer[uniqId].themeId, this.buffer[uniqId].msgId, reason, this.buffer[uniqId].reasonId, this.buffer[uniqId].reasonName);
                    break;
                case 'blockedFreelanceOffer': 
                    xajax_BlockedFreelanceOffer(this.buffer[uniqId].offerId, reason, this.buffer[uniqId].reasonId, this.buffer[uniqId].reasonName);
                    break;
                case 'blockedProjectOffer': 
                    xajax_BlockedProjectOffer(this.buffer[uniqId].offerId, this.buffer[uniqId].userId, this.buffer[uniqId].projectId, reason, this.buffer[uniqId].reasonId, this.buffer[uniqId].reasonName);
                    break;
                case 'blockedCommune': 
                    xajax_BlockedCommune(this.buffer[uniqId].communeId, reason, this.buffer[uniqId].reasonId, this.buffer[uniqId].reasonName);
                    break;
                case 'blockedThread': 
                    xajax_BlockedThread(this.buffer[uniqId].topicId, reason, this.buffer[uniqId].reasonId, this.buffer[uniqId].reasonName);
                    break;
                case 'blockedProject': 
                    xajax_BlockedProject( this.buffer[uniqId].projectId, reason, this.buffer[uniqId].reasonId, this.buffer[uniqId].reasonName ); 
                    break;
                case 'blockedProjectWithComplains': 
                    xajax_BlockedProjectWithComplain(this.buffer[uniqId].projectId, reason, this.buffer[uniqId].reasonId, this.buffer[uniqId].reasonName ); 
                    break;
                case 'warnUser' :
                    $('warn_btn').set( 'disabled', true );
                    $('warn_close').set( 'disabled', true );
                    $('warn_btn').set( 'value', 'Подождите' );
                    if ( reason.replace(/(^\s+)|(\s+$)/g, "") == '' && this.buffer[uniqId].act_id > 1 ) {
                        alert('Необходимо указать причину!');
                        $('ban_btn').set( 'disabled', false );
                        $('ban_btn').set( 'value', 'Сохранить' );
                        return false;
                    } else {
                        if(is_usercontent_stream==true) {
                            if(reasonStream.replace(/(^\s+)|(\s+$)/g, "") == '') {
                                alert('Необходимо указать причину!');
                                $('ban_btn').set( 'disabled', false );
                                $('ban_btn').set( 'value', 'Сохранить' );
                                return false;
                            } else {
                                $(this.buffer[uniqId].streamId).contentWindow.user_content.resolveReason = reasonStream;
                            }
                        }
                    }
                    if ( this.buffer[uniqId].context.uid == 'moder' ) {
                        $(this.buffer[uniqId].streamId).contentWindow.xajax_updateUserWarn( JSON.encode([this.buffer[uniqId].userId]), this.buffer[uniqId].act_id, this.buffer[uniqId].warnId, this.buffer[uniqId].reasonId[this.buffer[uniqId].act_id], this.buffer[uniqId].reasonName, reason, this.buffer[uniqId].draw_func, JSON.encode(this.buffer[uniqId].context) );
                    }
                    else {
                        xajax_updateUserWarn( JSON.encode([this.buffer[uniqId].userId]), this.buffer[uniqId].act_id, this.buffer[uniqId].warnId, this.buffer[uniqId].reasonId[this.buffer[uniqId].act_id], this.buffer[uniqId].reasonName, reason, this.buffer[uniqId].draw_func, JSON.encode(this.buffer[uniqId].context) );
                    }
                    break;
                case 'blockedPortfolio': 
                    xajax_BlockedPortfolio( this.buffer[uniqId].portfolioId, reason, this.buffer[uniqId].reasonId, this.buffer[uniqId].reasonName ); 
                    break;
                case 'blockedDialogue': 
                    xajax_BlockedDialogue( this.buffer[uniqId].dialogueId, reason, this.buffer[uniqId].reasonId, this.buffer[uniqId].reasonName ); 
                    break;
                case 'close':
                    if ( this.zero ) this.zeroShow();
                    break;
            }
            
            if ( this.buffer[uniqId].action != 'userBan' && this.buffer[uniqId].action != 'delReason' )
                delete this.buffer[uniqId];
        } else {
            alert('Необходимо указать причину!');
            return false;
       }
    }

};


window.addEvent('domready', function(){
   var autoContext = $$('[data-banned-uid]');
   if (autoContext.length) {
       autoContext.each(function(el) {
           var uid = el.get('data-banned-uid');
           var code = el.get('data-banned-code');
           var link = el.get('data-banned-link');
           var name = el.get('data-banned-name');
           banned.addContext(uid, code, link, name);
       });
   }
});