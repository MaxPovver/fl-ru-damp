var adm_edit_content = {
    WDCPREFIX: '',
    userLogin: '',
    buffer: null, 
    reasons: {}, 
    disabled: false,
    
    onSubmit: function( xAjaxFunc ) {
        if ( xAjaxFunc == 'admEditPrjOffers' ) {
            if ( $('files') ) {
                var iframes = $$("iframe[name^='upfile-']");

                if ( iframes && iframes.length > 0 ) {
                    alert('Пожалуйста, дождитесь загрузки файлов.');
                    return false;
                }

                var files = '';

                for ( var i=0, c=iboxes.boxes.length; i<c; i++ ) {
                    if (iboxes.boxes[i].fileID) files += iboxes.boxes[i].fileID + '/';
                }
                
                $('files').set('value', files);
                
                if (!document.getElementById('adm_edit_descr').value.replace(/[\s\r\n]+/, '') && !files) {
                    alert('Необходимо загрузить работы или написать текст предложения.');
                    return false;
                }
            }
        }
        else if ( xAjaxFunc == 'admEditCommunity' && typeof(adm_edit_ckeditor) != 'undefined' ) {
            adm_edit_ckeditor.updateElement();
        }
        
        return true;
    },
    
    editContent: function( xAjaxFunc, sId, nEdit, sDrawFunc, objParams ) {
        if ( nEdit == 0 ) {
            if (typeof this.buffer != 'undefined' && this.buffer != null) {
                this.cancel();
                return;
            }
            
            this.buffer = {
                func: xAjaxFunc,
                sId: sId,
                drawFunc: sDrawFunc,
                objParams: objParams,
                customReason: '',
                reasonId: ''
            };
            
            window['xajax_'+xAjaxFunc]( sId, nEdit, null, sDrawFunc, JSON.encode(objParams) );
        }
        else {
            if (typeof this.buffer != 'undefined' && this.buffer != null) {
                if ( this.onSubmit(xAjaxFunc) ) {
                    if ( sDrawFunc == 'stream0' || sDrawFunc == 'stream1' || sDrawFunc == 'stream2' ) {
                        $(objParams.stream_id).contentWindow['xajax_'+xAjaxFunc]( sId, nEdit, xajax.getFormValues('adm_edit_frm'), sDrawFunc, JSON.encode(objParams) );
                    }
                    else {
                        window['xajax_'+xAjaxFunc]( sId, nEdit, xajax.getFormValues('adm_edit_frm'), sDrawFunc, JSON.encode(objParams) );
                    }
                }
            }
            else {
                this.cancel();
            }
        }
    },
    
    edit: function() {
        $('adm_edit_text').setStyle('height', '70px');
        $('adm_edit_text').setStyle('min-height', '70px');
        $$("div[id^='ov-notice']").setStyle('display', 'none');
        $('ov-notice33').removeClass('b-shadow_center_top');
        $('ov-notice33').setStyle('top', '100px');
        $('ov-notice33').setStyle('position', 'fixed');
        $('ov-notice33').setStyle('display', '');
        // нельзя жать попап по ширине - Работы вылазят за рамки попапа
        /*if ( window.location.href.indexOf('/siteadmin/user_content/?site=frames') == -1 ) {
        	$('ov-notice33').setStyle('width', '624px');
        }*/
        this.setMouseWheelHandler($('ov-notice33'));
        //shadow_popup();
        this.hideAllErrors();
        $('ov-notice33').removeClass('b-shadow_hide');
    },
    
    setMouseWheelHandler: function(div) {
        if ( !div.get("mosewheel") ) {
            div.addEvent("mousewheel",
                function (evt) {
	            	var h = window.innerHeight;
	                if (!h && document.documentElement && document.documentElement.clientHeight) {
	                    h = document.documentElement.clientHeight;
	                } else if (!h) {
	                    h = document.getElementsByTagName('body')[0].clientHeight;
	                }
                    var divH = parseInt( div.getStyle("height") ) + parseInt( div.getStyle("padding") );
                    var y = parseInt( div.getStyle("top") );
                    if ( divH + y < h && y >= 0 ) {
            	        return;
                    }
                    var dY = 15;
                    var newY = y; 
                    if ( evt.wheel == -1 ) {
                    	if ( y + divH > h ) {
                    		newY = y - dY;
                    	}
                        if ( y + divH < h ) {
                        	newY = h - divH;
                        }
                        div.setStyle("top", newY + "px");
                    } else if ( evt.wheel == 1 ) {
                        var top = 0;
                        if ( window.location.href.indexOf('/siteadmin/user_content/?site=frames') == -1 ) {
                            top = 32;
                        }
                    	if ( y < top ) {
                    		newY = y + dY;
                    	}
                        if ( y > top ) {
                        	newY = top;
                        }
                        div.setStyle("top", newY + "px");
                    }
                }
            );
        }
    },
    
    save: function() {
        if (typeof this.buffer != 'undefined' && this.buffer != null && !this.disabled) {
            this.disabled = true;
            this.button();
            this.editContent( this.buffer.func, this.buffer.sId, 1, this.buffer.drawFunc, this.buffer.objParams );
        }
        else {
            this.cancel();
        }
    },
    
    cancel: function() {
        this.buffer = null;
        $('ov-notice33').addClass('b-shadow_hide');
        $('ov-notice33').setStyle('display', 'none');
        this.disabled = false;
        this.button();
    },
    
    button: function() {
        $('adm_edit_btn').addClass('b-button_disabled');
    },
    
    setReason: function() {
	    var reasonId = $('adm_edit_sel').get('value');
        
	    if ( reasonId != '' ) {
            if ( this.buffer.reasonId == '' ) {
                this.buffer.customReason = $('adm_edit_text').get('value');
            }
	        
            $('adm_edit_text').set('readonly', true);
            
            if ( typeof this.reasons[reasonId] != 'undefined' ) {
                $('adm_edit_text').set('value', this.reasons[reasonId]);
            }
            else {
                xajax_getAdmEditReasonText(reasonId);
            }
	    }
	    else {
            $('adm_edit_text').set('value', this.buffer.customReason);
	        $('adm_edit_text').set('readonly', false);
	    }
        
        this.buffer.reasonId = reasonId;
	},
    
    hideError: function( errorId ) {
        $('div_adm_edit_err_'+errorId).setStyle('display', 'none');
    },
    
    showError: function( errorId ) {
        $('div_adm_edit_err_'+errorId).setStyle('display', 'block');
    },
    
    hideAllErrors: function( errorId ) {
        $$("div[id^='div_adm_edit_err_']").setStyle('display', 'none');
    },
    
    editMenuItems: new Array(),
    
    editMenu: function( active ) {
        var max = this.editMenuItems.length;
        $$("li[id^='adm_edit_tab_i']").removeClass('b-menu__item_active');
        $('adm_edit_tab_i'+active).addClass('b-menu__item_active');
        $$("div[id^='adm_edit_tab_div']").setStyle('display', 'none');
        $('div_adm_reason').setStyle('display', 'none');
        if (active > 0 && active < max) $('adm_edit_tab_div'+active).setStyle('display', 'block');
        $('adm_edit_tab_i'+max).set('html', '<a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu('+ max +'); return false;">Причина редактирования</a>');
        
        for ( var i = 1; i < max; i++ ) {
            if ( active == i ) {
                $('adm_edit_tab_i'+i).set('html', '<span class="b-menu__b1"><span class="b-menu__b2">'+ this.editMenuItems[i] +'</span></span>');
            }
            else {
                $('adm_edit_tab_i'+i).set('html', '<a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu('+ i +'); return false;">'+ this.editMenuItems[i] +'</a>');
            }
        }

        if ( active == max ) {
            $('adm_edit_tab_i'+max).set('html', '<span class="b-menu__b1"><span class="b-menu__b2">Причина редактирования</span></span>');
            $('div_adm_reason').setStyle('display', 'block');
        }
        vertical_center_top();
        $('ov-notice33').removeClass('b-shadow_center_top');
    },

    /* ПРЕДЛОЖЕНИЯ ПО ПРОЕКТАМ  */
    
    works_ids:   new Array(),
    works_names: new Array(),
    works_prevs: new Array(),
    works_picts: new Array(),
    works_links: new Array(),
    
    prjOfferAddWork: function( num, pict, prev ) {
        for( var i = 1; i <= 3; i++ ) {
            var work_id = $('ps_work_'+ i +'_id');

            if ( work_id.value == '' ) {
                var work       = $('td_pic_'+ i);
                work.className = 'pic';
                work_id.value  = num;
                
                if (num == 0) { /* загружен */
                    if (prev != '') { /* есть превью */
                        work.innerHTML = '<a href="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + pict + '" target="_blank" class="blue" title=""  style="text-decoration:none"><div align="left"><img src="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + prev + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:adm_edit_content.prjOfferClearWork('+ i +', 0);"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
                    }
                    else {
                        work.innerHTML = '<div align="left" style="font-size:100%;"><a href="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + pict + '" target="_blank" class="blue" title="">' + pict + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:adm_edit_content.prjOfferClearWork('+ i +', 0);"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
                    }
                    $('ps_work_'+ i +'_pict').set('value', pict);
                    $('ps_work_'+ i +'_prev_pict').set('value', prev);
                    $('ps_work_'+ i +'_link').set('value', '');
                    $('ps_work_'+ i +'_name').set('value', '');
                }
                else { /* из портфолио */
                    if (this.works_prevs[num] != '') { /* есть превью */
                        if (this.works_prevs[num] == undefined) {this.works_prevs[num] = prev};
                        work.innerHTML = '<a href="/users/' + this.userLogin + '/viewproj.php?prjid=' + num + '" target="_blank" class="blue" title="' + this.works_names[num] + '"><div align="left"><img src="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + this.works_prevs[num] + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:adm_edit_content.prjOfferClearWork('+ i +', ' + num + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
                    }
                    else { /* превью нема */
                        work.innerHTML = '<div align="left" style="font-size:100%;"><a href="/users/' + this.userLogin + '/viewproj.php?prjid=' + num + '" target="_blank" class="blue" title="' + this.works_names[num] + '" class="blue">' + this.works_picts[num] + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:adm_edit_content.prjOfferClearWork('+ i +', ' + num + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
                    }
                    
                    $('ps_work_'+ i +'_pict').set('value', this.works_picts[num]);
                    $('ps_work_'+ i +'_prev_pict').set('value', this.works_prevs[num]);
                    $('ps_work_'+ i +'_link').set('value', this.works_links[num]);
                    $('ps_work_'+ i +'_name').set('value', this.works_names[num]);
                }
                
                if ( i > 1 ) { 
                    $('td_pic_sort_'+(i-1)).set('html', '<img id="ico_right_'+  (i-1) + i +'" src="/images/ico_right.gif" alt="" width="9" height="9" border="0" onclick="adm_edit_content.prjOfferWorkPos(' + num + ", '"+  (i-1) + i +"');\" /><br />" + '<img id="ico_left_'+  (i-1) + i +'" src="/images/ico_left.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" onclick="adm_edit_content.prjOfferWorkPos(' + num + ", '"+  (i-1) + i +"');\">");
                }
                
                break;
            }
        }
        
        if ( i == 3 ) {
            $$("div[id^='adm_edit_work_ctrl']").setStyle('display', 'none');
            $$("div[id^='adm_edit_work_msg']").setStyle('display', 'block');
        }
    },
    
    prjOfferClearWork: function( num, id ) {
        var nClear = num;
        for( var i = num; i < 3; i++ ) {
            var work2_id   = $('ps_work_'+ (i+1) +'_id');
            
            if ( work2_id.value != '' ) {
                nClear++;
                var work2_pict = $('ps_work_'+ (i+1) +'_pict');
                var work2_name = $('ps_work_'+ (i+1) +'_name');
                var work2_prev = $('ps_work_'+ (i+1) +'_prev_pict');

                if (work2_id.value == 0) {
                    $('td_pic_'+ i).set('html', '<a href="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work2_pict.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:adm_edit_content.prjOfferClearWork('+ i +', ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>');
                }
                else {
                    $('td_pic_'+ i).set('html', '<a href="/users/' + this.userLogin + '/viewproj.php?prjid=' + work2_id.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:adm_edit_content.prjOfferClearWork('+ i +', ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>');
                }

                $('ps_work_'+ i +'_id').set('value', work2_id.value);
                $('ps_work_'+ i +'_pict').set('value', work2_pict.value);
                $('ps_work_'+ i +'_prev_pict').set('value', work2_prev.value);
                $('ps_work_'+ i +'_link').set('value', $('ps_work_'+ (i+1) +'_link').get('value'));
                $('ps_work_'+ i +'_name').set('value', work2_name.value);
            }
        }
        
        $('td_pic_'+ nClear).set('html', '<div class="pic_blank_cnt">&nbsp;</div><div style="width:200px; margin-top:6px; font-size:100%;">&nbsp;</div>');
		$('td_pic_'+ nClear).removeClass('pic').addClass('pic_blank');
		$('ps_work_'+ nClear +'_id').set('value', '');
		$('ps_work_'+ nClear +'_pict').set('value', '');
		$('ps_work_'+ nClear +'_prev_pict').set('value', '');
		$('ps_work_'+ nClear +'_link').set('value', '');
		$('ps_work_'+ nClear +'_name').set('value', '');
        
        for ( i = 3; i > 1; i-- ) {
            if ( $('ps_work_'+ i +'_id').get('value') == '' ) {
                $('td_pic_sort_'+(i-1)).set('html', '<img id="ico_right_'+  (i-1) + i +'" src="/images/ico_right0.gif" alt="" width="9" height="9" border="0" /><br /><img id="ico_left_'+  (i-1) + i +'" src="/images/ico_left0.gif" alt="" width="9" height="9" border="0" style="margin-top:2px;" />');
            }
        }
        
        $$("div[id^='adm_edit_work_ctrl']").setStyle('display', 'block');
        $$("div[id^='adm_edit_work_msg']").setStyle('display', 'none');
    },
    
    prjOfferWorkPos: function(id, num) {
        var n               = num.split('');
        var work1           = $('td_pic_'+n[0]);
        var work2           = $('td_pic_'+n[1]);
        var work1_id        = $('ps_work_'+n[0]+'_id');
        var work2_id        = $('ps_work_'+n[1]+'_id');
        var work1_pict      = $('ps_work_'+n[0]+'_pict');
        var work2_pict      = $('ps_work_'+n[1]+'_pict');
        var work1_prev      = $('ps_work_'+n[0]+'_prev_pict');
        var work2_prev      = $('ps_work_'+n[1]+'_prev_pict');
        var work1_link      = $('ps_work_'+n[0]+'_link');
        var work2_link      = $('ps_work_'+n[1]+'_link');
        var work1_name      = $('ps_work_'+n[0]+'_name');
        var work2_name      = $('ps_work_'+n[1]+'_name');
        var work_id_value   = work1_id.value;
		var work_pict_value = work1_pict.value;
		var work_prev_value = work1_prev.value;
		var work_link_value = work1_link.value;
		var work_name_value = work1_name.value;
        
		if ( work2_id.value == 0 ) { /* загружен */
			if ( work2_prev.value != '' ) { // превью есть
				work1.innerHTML  = '<a href="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work2_pict.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else {
				work1.innerHTML  = '<div align="left" style="font-size:100%;"><a href="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work2_pict.value + '" target="_blank" class="blue" title="' + work2_name.value + '">' + work2_pict.value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
		}
		else {
			if ( work2_prev.value != '' ) { /* превью есть */
				work1.innerHTML  = '<a href="/users/' + this.userLogin + '/viewproj.php?prjid=' + work2_id.value + '" target="_blank" class="blue" title="' + work2_name.value + '"><div align="left"><img src="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work2_prev.value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else {
				work1.innerHTML  = '<div align="left" style="font-size:100%;"><a href="/users/' + this.userLogin + '/viewproj.php?prjid=' + work2_id.value + '" target="_blank" class="blue" title="' + work2_name.value + '">' + work2_pict.value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(1, ' + work2_id.value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
		}

		work1_id.value   = work2_id.value;
		work1_pict.value = work2_pict.value;
		work1_prev.value = work2_prev.value;
		work1_link.value = work2_link.value;
		work1_name.value = work2_name.value;

		if ( work_id_value == 0 ) { /* загружен */
			if (work_prev_value != '') { /* превью есть */
				work2.innerHTML  = '<a href="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work_pict_value + '" target="_blank" class="blue" title="' + work_name_value + '"><div align="left"><img src="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work_prev_value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else {
				work2.innerHTML  = '<div align="left" style="font-size:100%;"><a href="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work_pict_value + '" target="_blank" class="blue" title="' + work_name_value + '">' + work_pict_value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
		}
		else {
			if (work_prev_value != '') { /* превью есть */
				work2.innerHTML  = '<a href="/users/' + this.userLogin + '/viewproj.php?prjid=' + work_id_value + '" target="_blank" class="blue" title="' + work_name_value + '"><div align="left"><img src="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/' + work_prev_value + '" alt="" border="0"></div></a><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
			else {
				work2.innerHTML  = '<div align="left" style="font-size:100%;"><a href="/users/' + this.userLogin + '/viewproj.php?prjid=' + work_id_value + '" target="_blank" class="blue" title="' + work_name_value + '">' + work_pict_value + '</a></div><div style="margin-top:6px; font-size:100%;"><a href="javascript:clear_work(2, ' + work_id_value + ');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0" style="margin-right: 6px" />Удалить</a></div>';
			}
		}
        
		work2_id.value   = work_id_value;
		work2_pict.value = work_pict_value;
		work2_prev.value = work_prev_value;
		work2_link.value = work_link_value;
		work2_name.value = work_name_value;
    },

    prjOfferLoadWorks: function() {
        xajax_admEditPrjOffersLoadWorks($('adm_edit_prof').get('value'), $('user_id').get('value'));
    },
    
    /* РАБОТЫ В ПОРТФОЛИО */
    
    portfolioAddFile: function( type, filename, fileid ) {
        var html = '<a href="' + this.WDCPREFIX + '/users/' + this.userLogin + '/upload/'+ filename +'" class="blue" target="_blank">Посмотреть загруженный файл</a>';
        
        if ( type == 'prev_pict' ) {
            html = html + '&nbsp;&nbsp;<input type="checkbox" class="b-check__input" id="adm_edit_del_prev" name="del_prev" value="1"><label class="b-check__label" for="adm_edit_del_prev">Удалить файл</label>';
        }
        
        $(type).set('value', filename);
        $('span_'+type).set('html', html);
        $('span_'+type).setStyle('visibility', 'visible');
    },
    
    /* ИЗМЕНЕНИЯ В ПРОФИЛЕ И КАРУСЕЛЬ */
    
    profileAddFile: function( type, filename, fileid ) {
        $('new_val').set('value', filename);
        var dir = type == 'resume_file' ? 'resume' : (type == 'photo' || type == 'carusellogo' ? 'foto' : 'logo');
        var html = '<a href="' + this.WDCPREFIX + '/users/' + this.userLogin + '/' + dir + '/' + filename +'" class="blue" target="_blank">Посмотреть загруженный файл</a>';
        
        if ( type == 'carusellogo' ) {
            html = html + '&nbsp;&nbsp;<input type="checkbox" class="b-check__input" id="adm_edit_del_prev" name="del_prev" value="1"><label class="b-check__label" for="adm_edit_del_prev">Удалить файл</label>';
        }
        
        $('span_new_val').set('html', html);
    },
    
    /* ПРОЕКТЫ */
    
    prjSubCategory: function( ele ) {
        var category = ele.value;
        var div      = ele.parentNode;
        var objSel   = $(div.getElementsByTagName('select')[1]);

        objSel.options.length = 0;
        objSel.disabled       = 'disabled';

        objSel.options[objSel.options.length] = new Option('Все специализации', 0, true, true);

        if ( category == 0 ) {
            objSel.set('disabled', true);
        } 
        else {
            objSel.set('disabled', false);
        }

        for (i in this.prj_specs[category]) {
            if (this.prj_specs[category][i][0]) { 
                objSel.options[objSel.options.length] = new Option(this.prj_specs[category][i][1], this.prj_specs[category][i][0], false, false);
            }
        }

        objSel.set('value','0');
    },
    
    prjChangeKind: function() {
        if ( $("adm_edit_kind4").get("checked") ) {
            $("adm_edit_location").setStyle("display", "block");
        } 
        else {
            $("adm_edit_location").setStyle("display", "none");
        }
    },
    
    prjCityUpd: function( v ) {
        var div = $('frm_city');
        var ct = $(div.getElementsByTagName('select')[0]);
        ct.disabled = true;
        ct.options[0].innerHTML = "Подождите...";
        ct.value = 0;
        xajax_GetCitysByCid(v);
    },
    
    prjAgreement: function() {
        if ( $('adm_edit_agreement').get('checked') ) {
            $('adm_edit_cost').set('disabled', true);
            $('adm_edit_currency').set('disabled', true);
            $('adm_edit_priceby').set('disabled', true);
        }
        else {
            $('adm_edit_cost').set('disabled', false);
            $('adm_edit_currency').set('disabled', false);
            $('adm_edit_priceby').set('disabled', false);
        }
    },
    
    prjToggleLogo: function() {
        if ( $("adm_edit_logo_ok").get("checked") ) {
            $("adm_edit_location").setStyle("display", "block");
        } 
        else {
            $("adm_edit_location").setStyle("display", "none");
        }
    }, 
    
    prjAddLogo: function( fid, logourl ) {
        $('adm_edit_logo_id').set('value', fid);
        $('adm_edit_span_logo').setStyle('visibility', 'visible');
        $('adm_edit_span_logo').getFirst('a').set('href', logourl);
    }
};