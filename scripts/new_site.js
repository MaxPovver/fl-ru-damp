window.onscroll = scrollingFunk;

function scrollingFunk(){
	var bfix = document.getElementById('b-banner_fix');
	if(bfix){fix_banner();}
   var upArrow = $$('#upper');
   if(upArrow) {arrUp();}
} 	


function arrUp() { 	 	 
  var upArrow = $$('#upper');
  var scrolledUp, wSz;
  scrolledUp = window.pageYOffset || document.documentElement.scrollTop;
  wSz = window.getSize();	 
  if((scrolledUp + 80) > wSz.y) { 	 	 
	  upArrow.setStyle('visibility','visible');
  } else { 	 	 
	  upArrow.setStyle('visibility','hidden');
  } 	 	 
} 	


function vertical_center_top() {
    $$('.b-shadow_vertical-center').each(function(popup_elm) {
        var winSize = $(window).getSize();
        var elemSize = popup_elm.getSize();
        $$('.b-shadow_vertical-center').addClass('b-shadow_center_top');
        popup_elm.setPosition({
            x: (winSize.x - elemSize.x) / 2,
            y: (winSize.y - elemSize.y) / 2
        });
    });
}

window.addEvent('domready', 
function() {
	$$('.b-file__input').addEvent('mouseover',function(){
        var button = this.getNext('.b-button');
        if (button) {
            button.addClass('b-button_hover');
        }
    });
	$$('.b-file__input').addEvent('mouseout',function(){
        var button = this.getNext('.b-button');
        if (button) {
            button.removeClass('b-button_hover');
        }
    });
    
    function togglerContacts(obj) {
            var edit = $(obj).getParent('tr').getElement('.toggler-edit');
            var view = $(obj).getParent('tr').getElement('.toggler-view');
            
            if(edit.hasClass('b-combo_hide')) {
                edit.removeClass('b-combo_hide');
                $(obj).set('text', 'cохранить');
                view.addClass('b-layout__txt_hide');
            } else {
                edit.addClass('b-combo_hide');
                $(obj).set('text', 'изменить');
                view.set('text', edit.getElement('input').get('value'));
                view.removeClass('b-layout__txt_hide');
            }
        }
        $$('.contacts-input').addEvent('focus', function() {
            if($(this).getParent('tr').getElement('.contacts-error')) {
                $(this).getParent('tr').getElement('.contacts-error').addClass('b-layout_hide');
            }
        });
        
//        $$('.contacts-input').addEvent('blur', function() {
//            togglerContacts( $(this).getParent('tr').getElement('.toggler-contacts') );
//        });
        
        $$('.toggler-contacts').addEvent('click', function() {
            togglerContacts(this);
        });
        //Не даем работать с вверификацией неавторизованным
        if ( $$(".js-verify_type_button").length  ) {
            $$(".js-verify_type_button").each(
                function (inp) {
                    inp.F = inp.onclick;
                    inp.onclick = null;
                    var evType = "click";
                    if (inp.tagName.toLowerCase() == "input") {
                        evType = "keydown";
                    }
                    inp.addEvent(evType, function (evt) {
                        if (evt.target.type == "text") {
                            if (evt.code != 13) {
                                return true;
                            }
                        }
                        if ($('remember') && !$('remember').checked) {
                            return false;
                        }
                        var F  = this.F;
                        if (!_UID) {
                            var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                            storeValue("scrollTop", scrollTop);
                            alert("Вам необходимо авторизоваться, чтобы пройти верификацию");
                            var b = $('b-button-enter');
                            if (b) {
                                b.removeEvents('mouseleave');
                                b.removeEvents('mousedown');
                                b.removeEvents('mouseup');
                                b.addClass('b-button_active');
                                b.getNext('.b-shadow').removeClass('b-shadow_hide');
                                if ($('login_form_overlay')) {
                                    $('login_form_overlay').removeClass('b-shadow_hide');
                                }
                                $("b-login__text").focus();
                           }
                           return false;
                       } else {
                           if (F instanceof Function) {
                            return F();
                           }
                           return (evType == "keydown");
                       }
                    })
                    
                }
            );
            resetScrollTop();
        }
        
        //промотать к полю ввода телефона в личном кабинете
        var re = /\/users\/[\w]+\/setup\/main\//
        if (re.test(window.location.href)) {
            resetScrollTop();
        }
        
        //Закрыть окно, если оно открыто в попапе (авторизация в копини)
        var opener_href = 0;
        try {
            if (opener && opener.location && opener.location.href) {
                opener_href = 1;
            }
        } catch(e){ ; }
        if (self.opener && !_UID && window.location.href.indexOf("/login.php") != -1 && !opener_href) {
            storeValue("external_opener_login.php", 1);
        }
        if (self.opener && _UID && window.location.href.indexOf("/siteadmin/") == -1 && !opener_href) {
            if ( getStoreValue("external_opener_login.php") == 1 ) {
                storeValue("external_opener_login.php", '');
                self.close();
            }
        }
        
    //Проинициализировать попап оплаты ПРО при его наличии на странице
    if ($('quick_pro_win_main')) {
        quickPRO_init();
    }

});

//var fpslider = null;
var ctgCI = new Array();
window.addEvent('domready', 
function() {

    if(window.kword != undefined) {	
        if(document.getElementById('search_across')) {
            var KeyWord = __key(1);
            KeyWord.bind(document.getElementById('search_across'), kword, {bodybox:"body_search_across", maxlen:120});    
        }  
    }
    
	shadow_center();
	shadow_center_top();
    window.addEvent('resize', shadow_center);
    window.addEvent('resize', shadow_center_top);
	function shadow_center() {
	    $$('.b-shadow_center').each(function(popup_elm) {
            var winSize = $(document).getSize();
            var elemSize = popup_elm.getSize();
			if(winSize.y>800){
			  popup_elm.setPosition({
				  x: (winSize.x - elemSize.x) / 2,
				  y: (winSize.y - elemSize.y) / 2
			  });
			}
			else{
			  popup_elm.setPosition({
				  x: (winSize.x - elemSize.x) / 2,
				  y: 60
			  });
				}
	    });
	}
	function shadow_center_top() {
	    $$('.b-shadow_center_top').each(function(popup_elm) {
            var winSize = $(document).getSize();
            var elemSize = popup_elm.getSize();
            popup_elm.setPosition({
                x: (winSize.x - elemSize.x) / 2,
                y: 100
            });
	    });
	}
	
    vertical_center_top();
    window.onresize=vertical_center_top;
				
				
	shadow_gorizont_center();
 window.onresize=shadow_gorizont_center;
	function shadow_gorizont_center() {
	    $$('.b-shadow_gorizont_center').each(function(popup_elm) {
            var winSize = $(document).getSize();
            var elemSize = popup_elm.getSize();
            popup_elm.setPosition({
                x: (winSize.x - elemSize.x) / 2,
                y: elemSize.y
            })
	    });
	}
	
	
    popup();
	function popup() {
	    
	    $$('.b-popup_center').each(function(popup_elm) {
	       /////////////////////////////////////////////
		// calculate height and width window       //
		/////////////////////////////////////////////                           
		   var w = 0, h = 0;
		// for opera
			var isOpera = (navigator.userAgent.indexOf("Opera") != -1);
			if(isOpera){    
			$$('html','body').setStyle('height','100%');
			w = document.body.clientWidth - parseInt(popup_elm.getStyle('width'));                        
			h = document.body.clientHeight -  parseInt(popup_elm.getStyle('height'));
		  }
		// for ie 
			var isIE = ((!isOpera)&&(navigator.appName.indexOf("Microsoft Internet Explorer") != -1));
			if(isIE){
			w = document.documentElement.clientWidth - parseInt(popup_elm.getStyle('width'));             
			h = document.documentElement.clientHeight - parseInt(popup_elm.getStyle('height')); 
			}
		// for firfox
			var isMozzila = (navigator.userAgent.toLowerCase().indexOf("gecko")!=-1)
			if(isMozzila){    
			w = window.innerWidth - parseInt(popup_elm.getStyle('width'));                                 
			h = window.innerHeight - parseInt(popup_elm.getStyle('height'));                      
		  } 
		 popup_elm.setStyle('top', h/2);
		 popup_elm.setStyle('left', w/2);  
	    });
	    
	    $$('.b-popup__close').addEvent('click',function(){
			$(this).getParent('.b-popup').setStyle('display', 'none'); 
			return false;
	     });
	    
	    $$('.b-popup').setStyle('margin-left', '0');
	    $$('.b-popup').setStyle('display', 'none');
	}
 
    $$('.b-shadow__icon_close').addEvent('click',function() {
		this.getParent('.b-shadow').addClass('b-shadow_hide');
		$$('body').setStyle('overflow','');//снимаем защиту от прокрутки страницы при скроле попапа
    })
 
    
    $$('.b-filter__body .b-filter__link').removeEvents('click');
    $$('.b-filter__body .b-filter__link').addEvent('click',function(){
		$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
                if(this.getParent('.b-filter__body').getNext('.b-filter__toggle')) {
                    this.getParent('.b-filter__body').getNext('.b-filter__toggle').removeClass('b-filter__toggle_hide');
                }
		$$('.b-filter').setStyle('z-index','0')
		this.getParent('.b-filter').setStyle('z-index','10')
		var overlay=document.createElement('div');
		overlay.className='b-filter__overlay';
		$$('.overlay-cls').grab(overlay, 'top');		
		$$('.b-filter__overlay').addEvent('click',function(){
			$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
			$$('.b-filter__overlay').dispose();
		if (Browser.ie8){
			$$('.b-filter').setStyle('overflow','visible');
			if(this.getParent('.b-filter') != undefined) {
    			this.getParent('.b-filter').setStyle('overflow','hidden');
    			this.getParent('.b-filter').setStyle('overflow','visible');
			}
		}		
			});
		return false;
    });
    //выпадающее меню "Ещё"
    $$('.main_menu_more').addEvent('click',function(){
		//$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
		this.getNext('.b-filter__toggle').removeClass('b-filter__toggle_hide');
		$$('.b-filter').setStyle('z-index','0')
		this.getParent('.b-filter').setStyle('z-index','10')
		var overlay=$(document.createElement('div'));
		overlay.className='b-filter__overlay';
	    if (this.parentNode) {
	        overlay.inject(this.parentNode, 'bottom');
	    }
		$$('.b-filter__overlay').addEvent('click',function(){
			$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
			$$('.b-filter__overlay').dispose();
		if (Browser.ie8){
			$$('.b-filter').setStyle('overflow','visible');
			if(this.getParent('.b-filter') != undefined) {
    			this.getParent('.b-filter').setStyle('overflow','hidden');
    			this.getParent('.b-filter').setStyle('overflow','visible');
			}
		}		
			});
		return false;
    });
    
    $$('.b-filter__sbr_order').addEvent('click', function() {
        $$('.b-filter__toggle').addClass('b-filter__toggle_hide');
        if (this.getParent('.b-filter__body').getNext('.b-filter__toggle')) {
            this.getParent('.b-filter__body').getNext('.b-filter__toggle').removeClass('b-filter__toggle_hide');
        }
        $$('.b-filter').setStyle('z-index', '0')
        this.getParent('.b-filter').setStyle('z-index', '10')
        var overlay = document.createElement('div');
        overlay.className = 'b-filter__overlay';
        $$(document.body).grab(overlay, 'top');
        $$('.b-filter__overlay').addEvent('click', function() {
            $$('.b-filter__toggle').addClass('b-filter__toggle_hide');
            $$('.b-filter__overlay').dispose();
            if (Browser.ie8) {
                $$('.b-filter').setStyle('overflow', 'visible');
                if (this.getParent('.b-filter') != undefined) {
                    this.getParent('.b-filter').setStyle('overflow', 'hidden');
                    this.getParent('.b-filter').setStyle('overflow', 'visible');
                }
            }
        });
        return false;
    });
    
    $$('.sbr_message_option').addEvent('click', function() {
        var order = $(this).getProperty('data-type');
        $(this).getParent('.b-shadow').addClass('b-filter__toggle_hide');
        Cookie.write('sbr_order', order == 'asc' ? 1 : 0, {duration: 356});
        window.location.reload(); // Перезагружаем страничку
    });
    
    
	if($chk($('pf_toggle'))) asynccall(initFltr);
    if($chk($('nr-tz'))) flt('nr-tz', 'fast'); /*Фильтр ТЗ в СБР*/
    if($chk($('flt-pl'))) flt('flt-pl', 'slow', 1); /*Фильтр проектов*/
    if($chk($('flt-ph'))) flt('flt-ph', 'slow'); /*Скрытые платные проекты*/
    if($chk($('flt-ds'))) flt('flt-ds', 'slow'); /*Дополнительный фильтр*/
    if($chk($('flt-cat'))) flt('flt-cat', 'slow'); /*Фильтр в каталоге*/
    if($chk($('flt-works'))) flt('flt-works', 'slow'); /*Фильтр работ в каталоге*/
    if($chk($('flt-masss'))) masssendFilter('flt-masss', 'slow'); /*Фильтр в рассылке*/
    if($chk($('masss-files'))) masssendFilter('masss-files', 'slow'); /*Фильтр в рассылке*/
    if($chk($('apf-files'))) flt('apf-files', 'slow'); /*Прикрепленные файлы в публикации проета*/
	if($chk($('flt-feedback'))) flt('flt-feedback', 'slow'); /*Прикрепленный файл в обратной связи*/
    if($('bill-history')) $$('#bill-history tr:odd').setStyle('background-color', '#F6F6F6');
    
    $$('textarea').each(function(elm){
        $(elm).addEvent('focus', function(){
            document.onkeydown = null;
        });
        $(elm).addEvent('blur', function(){
            document.onkeydown = NavigateThrough;
        });
    });
    
    $$('input').each(function(elm){
        $(elm).addEvent('focus', function(){
            document.onkeydown = null;
        });
        $(elm).addEvent('blur', function(){
            document.onkeydown = NavigateThrough;
        });
    });
    
    /*тоглер блока СБР в фильтре каталога*/
    if($chk($('flt-sbr'))){
		var flt_sbr_slider = new Fx.Slide($('flt-sbr').getElement('.flt-sbr-more'), {duration: 400});
		var flt_sbr_slider_isShw = false;
		if($('flt-masss') && $('flt-masss').getElement('.flt-cnt'))
            var flt_cat_cnt_box = $('flt-masss').getElement('.flt-cnt').getParent();
		if($('flt-cat'))
            var flt_cat_cnt_box = $('flt-cat').getElement('.flt-cnt').getParent();
        if(!vsbr) {
			  if ($('flt-sbr-mass')) {
				$('flt-sbr-mass').getParent().setStyle('position', 'absolute');
			  }
              flt_sbr_slider.hide();
        } else {
            if($('flt-cat')) {
                flt_cat_cnt_box.setStyle('height', 'auto');
                $('flt-sbr-mass').getParent().setStyle('position', 'relative');
                flt_sbr_slider_isShw = 1;
            }
        }
        $('sbr_main_check').addEvent('click', function(e) {
			if(flt_cat_cnt_box) flt_cat_cnt_box.setStyle('height', 'auto');
            if(($('sbr_main_check').checked)) {
                if ($('flt-sbr-mass')) {
					$('flt-sbr-mass').getParent().setStyle('position', 'relative');
				}
				flt_sbr_slider.slideIn();
                flt_sbr_slider_isShw = 1;
            } else {
				var ls = $('flt-sbr-mass').getElements("input");
				for (var i = 0; i < ls.length; i++) {
					if (ls[i].type == "checkbox") ls[i].checked = false;
				}
				flt_sbr_slider.slideOut();
                flt_sbr_slider_isShw = 0;
            }
        });
		if ($('flt-sbr-mass')) {
			flt_sbr_slider.addEvent('complete', function() {
				if ($('flt-sbr-mass') && !flt_sbr_slider_isShw) $('flt-sbr-mass').getParent().setStyle('position', 'absolute');
			})
		}
    }

    /*Выбор аккаунта для восстановления пароля по SMS*/
    if($chk($('lp-acc-list'))){
        $('lp-acc-list').getElements('input[type=radio]').addEvent('click', function(){
            $('lp-acc-value').set('text', this.get('value'));
        });
    }

    /*
        if($chk($('projects-list'))){
            $('projects-list').getElements('.project-full-in').slide('hide');
            $('projects-list').getElements(':nth-child(n)>h3>a').addEvent('click', function(){
                   this.getParent().getNext().getChildren().getElement('div').slide('toggle');
                   return false;
                   });
        }
    */

    initKword();

    if ( window.notification_delay ) {
        Notification.delay(window.notification_delay);
    }
    
	// закрытие блоков с крестиком
	$$( ".closable-block" ).each( function( el ) {
		el.getElements( ".cb-close" ).addEvent( "click", function() {
			el.addClass( "closable-block-hidden" );
		});
	});

	// нажатие стилизованных кнопок

	$$( ".btn, .btnr, .btnr-mb, .btnr-l" ).addEvent( "mousedown", function() {
		this.addClass( "active" );
	}).addEvent( "mouseup", function() {
		this.removeClass( "active" );
	}).addEvent( "mouseleave", function() {
		this.fireEvent( "mouseup" );
	});
	
	$$( ".b-button-multi__item" ).addEvent( "click", function() {
		if((!(this.hasClass('b-button-multi__item_disabled')))){
			if(!(this.hasClass('b-button-multi__item_active'))){
				this.getParent('.b-button-multi').getChildren('.b-button-multi__item').removeClass('b-button-multi__item_active');
				this.addClass( "b-button-multi__item_active" );
			}
		}
		//return false; // fix #0018308
	})
	
	
	$$( "a.b-button" ).addEvent( "mousedown", function() {
        if (this.hasClass('b-button_disabled')) return;
        this.addClass( "b-button_active" );
	}).addEvent( "mouseup", function() {
        if (this.hasClass('b-button_disabled')) return;
		this.removeClass( "b-button_active" );
	}).addEvent( "mouseleave", function() {
        if (this.hasClass('b-button_disabled')) return;
		this.fireEvent( "mouseup" );
	});
    // удаляем только что приделаные события у элементов с классом normal_behavior
    $$( ".b-button" ).each(function(el){
        if (el.hasClass('normal_behavior')) el.removeEvents('mousedown').removeEvents('mouseup').removeEvents('mouseleave');
    })
	
    
    init_blink_icons();
    init_get_sms();
});

function init_get_sms() {
    if($('getsms') == undefined || $('getsms') == null ) return false;
    
    $$('.sms_valid_send').addEvent('click', function() {
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
            var form = $('getsms').get('data-form');
            if( $(form) != undefined ) {
                var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                storeValue("scrollTop", scrollTop);
                $(form).submit();
            }
        }
    });
    
    if($('only_phone') != undefined)
        $('only_phone').addEvent("change", function() {
            var def = $$('input[name=def_only_phone]').get('value') == 0 ? false : true;
            var now = $(this).checked;
            showForm(def, now);
        });
        
    
    if($('finance_safety_phone') != undefined)
        $('finance_safety_phone').addEvent("change", function() {
            var def = $$('input[name=def_finance_safety_phone]').get('value') == 0 ? false : true;
            var now = $(this).checked;

            showForm(def, now);
        });
    
    function cancelSafetyChange() {
        $$('.button_first_save').addClass('b-layout__txt_hide');
        $('only_phone').checked = $$('input[name=def_only_phone]').get('value') == 0 ? false : true;
        $('finance_safety_phone').checked = $$('input[name=def_finance_safety_phone]').get('value') == 0 ? false : true;
        var SmsLimit = new CsmsLimit();
        if (!window.smslimit && window.sms_message_link_end) {
            if ( $("was_send_sms_text").getStyle("display") == "none" ) {
                $("getsms").set("text", "Получить смс с кодом (" + sms_message_link_end  + ")");
            } else {
                $("getsms").set("text", "Получить код повторно (" + sms_message_link_end  + ")");
            }
        } else {
            $("getsms").set("text", LIMIT_EXCEED_LINK_TEXT);
        }
    }
    
    $$('.first_cancel_btn').addEvent('click', cancelSafetyChange);
    
    $$('.button_first_save a.b-button').addEvent('click', function() {
        $('main_phone_form').getElement('input[name=action]').set('value', 'save_safety');
        $('main_phone_form').submit();
    });
    
    function showForm(def, now) {
        if(def == false) {
            
            if(def != now && $$('.sms_form')[0].hasClass('b-layout__txt_hide')) {
                $$('.button_first_save').removeClass('b-layout__txt_hide');
            }
        } else {
            if(def != now) {
                $('main_phone_form').getElement('input[name=action]').set('value', 'save_safety');
                $$('.button_first_save').addClass('b-layout__txt_hide');
                $$('.sms_form').removeClass('b-layout__txt_hide');
                $$('.sms_form').getElement('h3.title').set('text', 'Подтверждение действий');
                $$('.sms_form').getElement('a.sms_valid_send').set('text', 'Сохранить изменения');
                $('getsms').fireEvent('click');
            }
        }
    }
    
    $$('.sms_cancel_change').addEvent('click', function() {
        cancelSafetyChange();
        $('safety_phone_block').removeClass('b-layout__txt_hide');
        $$('.sms_form').addClass('b-layout__txt_hide');
        $$('.sms_unbind_link').removeClass('b-layout__txt_hide');
    });
    
    $$('.sms_unbind_link').addEvent('click', function() {
        cancelSafetyChange();
        $(this).addClass('b-layout__txt_hide');
        $('main_phone_form').getElement('input[name=action]').set('value', 'save_phone');
        $('safety_phone_block').addClass('b-layout__txt_hide');
        $$('.sms_form').removeClass('b-layout__txt_hide');
        $$('.sms_form').getElement('h3.title').set('text', 'Отвязать телефон');
        $('getsms').fireEvent('click');
    });
    
    $('getsms').addEvent('click', function() {
        if( $(this).hasClass('b-layout__link_bordbot_dot_80') ) {
        	if ( window.smstimeout && window.sit && !window.smslimit) {
                var t = Math.round( new Date().valueOf() / 1000 );
                var n = window.sit - t + window.smstimeout;
                if (n > 0) {
                    alert("Вы можете запросить SMS через " + n + getSuffix(n, " секунд", "у", "ы", "") );
                }
        	}
            return false;
        }
        window.document.body.style.cursor = 'wait';
        
        var code  = $(this).get('data-code');
        var field = $(this).get('data-field');// Инпут где находится наш телефон
        if(code == '' || code == undefined || field == '' || field == undefined) {
            return false;
        }
        
        var phone = $(field).get('value');
        
        if( phone.match(/^\+[0-9]{10,15}/) == null ) {
            $(field).getParent().addClass("b-combo__input_error");
            window.document.body.style.cursor = 'default';
        } else {
            
            new Request.JSON({
                url: '/xajax/users.server.php',
                onSuccess: function(resp) {
                    if(resp.success) {
                        window.smsNumber = $('mob_phone').value;
                        var msg = resp.message;
                        var SmsLimit = new CsmsLimit();
                        if (resp.count < LIMIT_SMS_TO_NUMBER) {
                            msg = 'Получить код повторно (' + resp.message + ')';
                            setTimeout(function() {
                                $('getsms').removeClass('b-layout__link_bordbot_dot_80');
                                $('getsms').addClass('b-layout__link_bordbot_dot_0f71c8');
                                if ( $('was_send_sms_text') && $$("div.sms_form").length && $$("div.sms_form")[0].hasClass("b-layout__txt_hide") ) {
                                    $('was_send_sms_text').setStyle("display", null);
                                    $('was_send_sms_text2').setStyle("display", null);
                                }
                            }, 60000);
                        } else {
                            window.smslimit = true;
                        }
                        SmsLimit.setSmsLink(msg, 0, phone, window.smslimit);
                        if(resp.c != '') {
                            $(code).set('value', resp.c);
                        }
                        window.smstimeout = Math.round( new Date().valueOf() / 1000 );
                        window.sit = 60;
                    } 
                    window.document.body.style.cursor = 'default';
                }
            }).post({
                'xjxfun': 'getsms',
                'xjxargs': ['N'+phone],
                'u_token_key': _TOKEN_KEY
            }); 
            
        }
    });
    if ( $("mob_phone") ) {
        $("mob_phone").addEvent("blur", 
            function() {
                var smsLimit = new CsmsLimit();
                smsLimit.restoreLinkTextByPhone($("mob_phone").value);
            }
        );
    }
}

    
function updateGlobalAnchor() {
    Cookie.write('pathname_anchor', window.location.pathname, {duration: 356});
    Cookie.write('global_anchor', window.location.hash, {duration: 356});
}


function initKword() {
    if(window.kword != undefined) {
        if(document.getElementById('kword_se')) {
            var KeyWord = __key(1);
            KeyWord.bind(document.getElementById('kword_se'), kword, {bodybox:"body_1", maxlen:120});
        }
    }
}
/*
function Comments(){
    $('cl').getElement('a.cl-hide-all').addEvent('click', function(){
        $('cl').getElements('li.cl-li').addClass('cl-li-hidden');
        $('cl').getElements('a.cl-thread-toggle').set('text', 'Развернуть ветвь');
        $(this).removeClass('lnk-dot-666');
        $(this).addClass('lnk-dot-999');
        $('cl').getElement('a.cl-show-all').removeClass('lnk-dot-999');
        $('cl').getElement('a.cl-show-all').addClass('lnk-dot-666');
        return false;
    });
    $('cl').getElement('a.cl-show-all').addEvent('click', function(){
        $('cl').getElements('li.cl-li').removeClass('cl-li-hidden');
        $('cl').getElements('a.cl-thread-toggle').set('text', 'Свернуть ветвь');
        $(this).removeClass('lnk-dot-666');
        $(this).addClass('lnk-dot-999');
        $('cl').getElement('a.cl-hide-all').removeClass('lnk-dot-999');
        $('cl').getElement('a.cl-hide-all').addClass('lnk-dot-666');
        return false;
    });
    $('cl').getElements('a.cl-thread-toggle').addEvent('click', function(){
        var t = $(this).getParent('li.cl-li');
        if(t.hasClass('cl-li-hidden')){
            t.removeClass('cl-li-hidden');
            t.getChildren('li.cl-li').removeClass('cl-li-hidden');
            $(this).set('text', 'Свернуть ветвь');
        }else{
            t.addClass('cl-li-hidden');
            $(this).set('text', 'Развернуть ветвь');
        }
        return false;
    });
}*/

function asynccall(func,timeout){try{window.setTimeout(func,timeout?timeout:0);}catch(e){func();}}

function initCI(ciid){var ci,cis;if(ci=document.getElementById(ciid)){ctgCI.push(cis=ci.style);cis.display='none';}}
function initCtg(gr_num) {
    gr_num=gr_num==null?-1:gr_num;
    var ci,myAccordion;
    while(ci=ctgCI.pop())ci.display='';
    myAccordion = new Fx.Accordion($('accordion'), 'a.toggler', 'ul.element', {
        opacity: false, 
        alwaysHide: true, 
        show: gr_num, 
        duration: 400,
        onActive: function(toggler, element) {
            toggler.addClass('b-catalog__link_active');
            element.setStyle('display', 'block'); 
            (function(){
                this.setStyle('overflow', 'visible'); 
            }).delay(400, element);
        },
        onBackground: function(toggler, element) {
            toggler.removeClass('b-catalog__link_active');
            element.setStyle('overflow', 'hidden'); 
        }
    });
    $$('ul.element').setStyle('display', 'block');
}

/*Старый фильтр*/
function initFltr() {
    var fpslider = new Fx.Slide('prjFilter_outer', {duration: 400});
    var fpse = fpslider.element;
    fpslider.f_isShw = fpse.getAttribute('is_showed')==1;
    fpslider.f_page  = fpse.getAttribute('page');
    fpslider.f_rnd = false;

    if(!fpslider.f_isShw)
        fpslider.hide();

    fpslider.addEvent('complete', function() {xajax_SwitchFilter((this.f_isShw = !this.f_isShw)?1:0, this.f_page);});
    $('pf_toggle').addEvent('click', function(e) { 
        e.stop();
        if(!fpslider.f_rnd) {
            fpslider.element.setStyle('display','');
            fpslider.f_rnd = true;
        }
        fpslider.toggle(); 
    });
}

/*Фильтр в рассылке*/
function masssendFilter(name, type) {
    var fbox = $(name); // ID фильтра    
    if(fbox.getElement('.flt-cnt-masssend') == undefined) return false;
    var fslider = new Fx.Slide(fbox.getElement('.flt-cnt-masssend'), {duration: type=='fast' ? 0 : 400});
    fbox.getElement(".flt-cnt-masssend").getParent().style.overflow = "hidden";
    fbox.getElement(".flt-cnt-masssend").style.overflow = "visible";
    //fbox.getElement(".flt-cnt-masssend").setStyle('margin-top', "-373px");     
    fbox.f_isShw = fbox.hasClass('flt-show'); //Проверяем наличие класса flt-show
    fbox.lnk = fbox.getElement('.flt-tgl-lnk');
    fbox.getElement('.flt-cnt-masssend').setStyle('display', 'block');
    if(fbox.f_isShw) {
        fslider.show();
    } else {
        fslider.hide();
    }
    
    fbox.lnk.addEvent('click', function(e) {
        if (document.sliderMove) {
        	return;
        }
        document.sliderMove = 1;
        if(e) e.stop();
        fbox.toggleClass('flt-show');
        fbox.toggleClass('flt-hide');
        
        if (fbox.hasClass('flt-show')) {        	
            fslider.slideIn();
        } else {        	
            fslider.slideOut();
        }
    });

    fslider.fbox = fbox;
    fslider.addEvent('complete', flt_complete);

    return fslider;
}


/*Новый фильтр*/
function flt(name /*ID фильтра*/, type, no_overflow) {
    var fbox = $(name); // ID фильтра
    if(fbox.getElement('.flt-cnt') == undefined) return false;
    var fslider = new Fx.Slide(fbox.getElement('.flt-cnt'), {duration: type=='fast' ? 0 : 400});
    fbox.f_isShw = fbox.hasClass('flt-show'); //Проверяем наличие класса flt-show
    fbox.f_isSlg = false;                     //Производится ли слайдинг (анимация) в даный момент?
    fbox.lnk = fbox.getElement('.flt-tgl-lnk');
    fbox.getElement('.flt-cnt').setStyle('display', 'block');
    if(fbox.f_isShw) {
        fslider.show();
    } else {
        fslider.hide();
    }
    
    fbox.lnk.addEvent('click', function(e) {
        if(fbox.f_isSlg) return; // Анимация ещё идет, рано кликать
        fbox.f_isSlg = true;
        if(e) e.stop();
        //fbox.getElement('.flt-cnt').getParent().setStyle('overflow', 'hidden');
        fbox.toggleClass('flt-show');
        fbox.toggleClass('flt-hide');
        if (fbox.hasClass('flt-show')) {        	
            fslider.slideIn();
        } else {        	
            fslider.slideOut();
            fbox.getElement('.flt-cnt').getParent().setStyle('overflow', 'hidden');
        }
    });

    fslider.fbox = fbox;
    fslider.addEvent('complete', flt_complete);

    return fslider;
}

function flt_complete() {
    document.sliderMove = 0;
    var otxt='Развернуть',itxt='Свернуть',fbox=this.fbox;
    fbox.f_isShw = !fbox.f_isShw;
    switch (fbox.id){
        case 'flt-pl':
        case 'flt-ph':
        case 'flt-cat':
            var d = new Date();
            d.setMonth(d.getMonth() + 1);
            document.cookie='new_pf'+fbox.getAttribute('page')+'='+(fbox.f_isShw-0)+'; expires='+d.toGMTString() + '; path=/';
            break;
        case 'flt-works':
            var d = new Date();
            d.setMonth(d.getMonth() + 1);
            document.cookie='new_pf'+fbox.getAttribute('page')+'='+(fbox.f_isShw-0)+'; expires='+d.toGMTString();
            break;    
		case 'masss-files':
            if ($('flt-masss-files')) $('flt-masss-files').getParent().setStyle('height', 'auto');
		case 'apf-files':
        case 'nr-files1':
            otxt='Прикрепленные файлы (развернуть)', itxt='Прикрепленные файлы (свернуть)';
            break;
		case 'flt-feedback':
			otxt='Прикрепить файл к сообщению (5Мб)';
			break;
		case 'nr-tz':
            var d = new Date();
            d.setMonth(d.getMonth() + 1);
            document.cookie=fbox.getAttribute('page')+'='+(fbox.f_isShw-0)+'; expires='+d.toGMTString();
            otxt='Показать', itxt='Скрыть';
            break;
		case 'flt-sbr':
			if ($('flt-sbr-mass') && !fbox.f_isShw) $('flt-sbr-mass').getParent().setStyle('position', 'absolute');
			return;
			break;
        case 'flt-masss':
            if ($('flt-cnt')) {
                var wrap = $('flt-cnt').getParent('div');
                if (wrap) {
                    wrap.setStyle('height','');
                }
            }
            break;
    }
    fbox.lnk.set('text',fbox.f_isShw?itxt:otxt);
    fbox.f_isSlg = false; // Анимация закончилась
}


function pl_toggler(arg) {
     var to = arg;
     if(to=='in') {
         $('projects-list').getElements('.project-full').slide('in');
         $('pl_toggler').set('text', 'Свернуть все проекты').set('onclick', 'pl_toggler("out");');
     }
     if(to=='out') {
         $('projects-list').getElements('.project-full').slide('out');
         $('pl_toggler').set('text', 'Развернуть все проекты').set('onclick', 'pl_toggler("in");');
     }
}

function clean(A) {
    var B=A.previousSibling;
    if(B) {
        A.onblur=function() {if(!A.value) B.style.top="5px";};
        B.style.top="-1000px"
    }
}

function acc_toggler() {
    var el_h = $('acc-change').getStyle('height').toInt();
    var el_v = $('acc-change').getStyle('visibility');
    if (el_v == 'hidden'){
        $('acc-change').setStyle('height', '0'); 
        $('acc-change').setStyle('visibility', 'visible'); 
        $('mb-account').addClass('mb-change');
        var acc_SlideOut = new Fx.Morph('acc-change', {duration: 400});
        acc_SlideOut.start({'height': [el_h+1]});
    }
    else {
        var acc_SlideIn = new Fx.Morph('acc-change', {
              duration: 400,
              onComplete:
                function() {
                    $('acc-change').setStyle('visibility', 'hidden');
                    $('acc-change').setStyle('height', el_h);
                    $('mb-account').removeClass('mb-change');
                }
              });
         acc_SlideIn.start({'height': [0]});
    }
}

function lancer_acc_exit() {
    var el = $('mb-lancer');
    el.empty();
    el.set('html', '<span><span class="mbc-fl"><label for="fl1" onclick="this.nextSibling.focus();" class="fl">Логин</label><input name="a_login" id="fl1" type="text" class="mba-str" onfocus="clean(this)" onkeydown="if(event.keyCode==13)asw_subm(\'change_au\')" /></span><span class="mbc-fl"><label for="fp1" onclick="this.nextSibling.focus();" class="fp">Пароль</label><input name="passwd" id="fp1" type="password" class="mba-str" onfocus="clean(this)" onkeydown="if(event.keyCode==13)asw_subm(\'change_au\')" /></span><span class="lnc-add-acc"><a href="javascript:asw_subm(\'change_au\')">Добавить аккаунт</a></span></span>');
    el.removeClass('mb-lancer');
    el.addClass('mb-lancer-add');
    $('acc-change').setStyle('height', 'auto');
    var el_h = $('acc-change').getStyle('height').toInt();
    $('acc-change').setStyle('height', el_h);
}

function emp_acc_exit() {
    var el = $('mb-employer');
    el.empty();
    el.set('html', '<span><span class="mbc-fl"><label for="fl1" onclick="this.nextSibling.focus();" class="fl">Логин</label><input name="a_login" id="fl1" type="text" class="mba-str" onfocus="clean(this)" onkeydown="if(event.keyCode==13)asw_subm(\'change_au\')" /></span><span class="mbc-fl"><label for="fp1" onclick="this.nextSibling.focus();" class="fp">Пароль</label><input name="passwd" id="fp1" type="password" class="mba-str" onfocus="clean(this)" onkeydown="if(event.keyCode==13)asw_subm(\'change_au\')" /></span><span class="lnc-add-acc"><a href="javascript:asw_subm(\'change_au\')">Добавить аккаунт</a></span></span>');
    el.removeClass('mb-employer');
    el.addClass('mb-employer-add');
    $('acc-change').setStyle('height', 'auto');
    var el_h = $('acc-change').getStyle('height').toInt();
    $('acc-change').setStyle('height', el_h);
}

function com_acc_exit() {
    var el = $('mb-comand');
    el.empty();
    el.set('html', '<span><span class="mbc-fl"><label for="fl3" onclick="this.nextSibling.focus();" class="fl">Логин</label><input id="fl3" type="text" class="mba-str" onfocus="clean(this)" /></span><span class="mbc-fl"><label for="fp3" onclick="this.nextSibling.focus();" class="fp">Пароль</label><input id="fp3" type="password" class="mba-str" onfocus="clean(this)" /></span><span class="lnc-add-acc"><a href="">Добавить аккаунт</a></span></span>');
    el.removeClass('mb-comand');
    el.addClass('mb-comand-add');
    $('acc-change').setStyle('height', 'auto');
    var el_h = $('acc-change').getStyle('height').toInt();
    $('acc-change').setStyle('height', el_h);
}

function asw_subm(act) {
    var asw_form = document.getElementById('asw_form');
    asw_form['action'].value=act;
    asw_form.submit();
}

function asw_subm_new() {
    var asw_form = document.getElementById('asw_form');
    asw_form.submit();
}
     
var count_scroll = 0;
var iTimeoutId   = null;

function tp_scroll(lr, sizeof) {
    if(iTimeoutId != null) {
        clearTimeout(iTimeoutId);
        iTimeoutId = null
    }
    var scroll = lr;
    var el = $('top-payed');
    var el_l = el.getStyle('margin-left').toInt();
    var el_r = el.getStyle('margin-right').toInt();
    var el_nl = el_l-228;
    var el_nr = el_l+228;
    var critical_view = sizeof-4; // 4 - сколько минимум показывать
    if (lr == 'left'){
        count_scroll += 1;
        $('tpa-right').setStyle('display', 'block');
        $('tpa-right').set('disabled', false);
        if(count_scroll >= critical_view) {
        	$('tpa-left').set('disabled', true);
        	$('tpa-left').setStyle('display', 'none');
        }
        else iTimeoutId = setTimeout(function(){$('tpa-left').set('disabled', false);$('tpa-left').setStyle('display', 'block');}, 600);
        el.morph({'margin-left': el_nl});
    }
    else {
        count_scroll -= 1;
        $('tpa-left').setStyle('display', 'block');
        $('tpa-left').set('disabled', false);
        el.morph({'margin-left': el_nr});
        if(count_scroll == 0) {
        	$('tpa-right').set('disabled', true);
        	$('tpa-right').setStyle('display', 'none');
        }
        else iTimeoutId = setTimeout(function(){$('tpa-right').set('disabled', false);$('tpa-right').setStyle('display', 'block');}, 600);
    }
}

function OpenProject(id) {
    var psty,p = document.getElementById('mp' + id);
    if(!p) return;
    psty = p.style;
    if (psty.display=='block' || (isPrjCssOpened && psty.display != 'none'))
        psty.display = 'none';
    else
        psty.display = 'block';
    openedProjects.push(psty);
}

function OpenAllProjects() {
    var ss,psty;
    if(ss = document.styleSheets.item(0)) {
        while(psty = openedProjects.pop())
            psty.display = '';
        try {ss.cssRules[0].style.display = isPrjCssOpened ? 'none' : 'block';} // Gecko
        catch(e) {ss.rules[0].style.display = isPrjCssOpened ? 'none' : 'block';} // IE
        isPrjCssOpened = !isPrjCssOpened;
        xajax_OpenAllProjects(isPrjCssOpened);
    }

    if (isPrjCssOpened) document.getElementById('pl_toggler').innerHTML = 'Свернуть все проекты';
    else document.getElementById('pl_toggler').innerHTML = 'Развернуть все проекты';
}

//Функция обновления гордов в фильтре через ajax
function FilterCityUpd(v) {
    if($("frm").pf_city != undefined) {
        ct = $("frm").pf_city;
    } else {
        ct = $("frm").city;

    }
  ct.disabled = true;
  ct.options[0].innerHTML = "Подождите...";
  ct.value = 0;
  xajax_GetCitysByCid(v);
}

//Функция обновления гордов в фильтре регионов через ajax
function RegionFilterCityUpd(v) {
    ct = $('b-select__city');
    ct.set('disabled', true);
    ct.options[0].innerHTML = "Подождите...";
    ct.value = 0;
    xajax_RFGetCitysByCid(v);
    ct.set('disabled', false);
}

//Функция обновления списка подкатегорий в зависимости от выбранной категории в фильтре
function FilterSubCategory(category,without_sa)
{
  if(typeof without_sa == 'undefined') without_sa = false;
  var objSel = $('pf_subcategory'); 
  objSel.options.length = 0;
  objSel.disabled = 'disabled';

  if(!without_sa){
      if (curFBulletsBox == 2){
        objSel.options[objSel.options.length] = new Option('Все подкатегории', 0);
      } else {
        objSel.options[objSel.options.length] = new Option('Выберите подраздел', 0);
      }
  }
  if(category == 0) {
      objSel.set('disabled', true);
  } else {
      objSel.set('disabled', false);
  }
  var ft = true;
  for (i in filter_specs[category]) {
    if (filter_specs[category][i][0]) {
        objSel.options[objSel.options.length] = new Option(filter_specs[category][i][1], filter_specs[category][i][0], ft, ft);
        ft = false;
    }
  }
   if(!without_sa){
        objSel.set('value','0');
    }
}

//Функция добавления или удаления буллета из списка выбранных специализаций в фильтре
//#0024211 - дело в том, что некоторые специальности имеют не одно "зеркало", заменил формат filter_mirror_specs с {main_prof => mirror_prof} на {main_prof =>[mirror_prof0, ..., mirror_profN ]}
//          так как ранее происходило "затирание" номера отраженной специальности при инициализации filter_mirror_specs
function FilterAddBullet(cattype, catid, title, parentid, title_full) {
    var deleteAction = ( parentid == 0 &&  title == 0);
    var curfb, data = '';
    var mirridList = filter_mirror_specs[catid];
    var mirrEx = [];  //#0024211 меняю булево значение на массив, существование зеркальных специальностей определяю по длине этого массива
    // Проверка на существование зеркальных разделов, уже имеющихся в фильтре
    var err = searchMirror(mirridList, mirrEx, cattype, title);
    if (err.length) {
        alert(err);
        return false;
    }
    //Если это добавление и не удалось найти зеркало по  cat_id - ищем в отражениях идентификатор главной специальности и повторяем поиск по нему
    if (!deleteAction) {
        for (var i in filter_mirror_specs) {
            if (!parseInt(i)) {
                continue;
            }
            var a = filter_mirror_specs[i];
            var b = [];
            for (var j = 0; j < a.length; j++) {
                if (a[j] == catid) {
                    err = searchMirror(a, b, cattype, title);
                    if (err.length) {
                        alert(err);
                        return false;
                    }
                }
            }
        }
    }
    if (!title) {
        delete filter_bullets[cattype][catid];
        for (var n = 0; n < mirrEx.length; n++) {
          delete filter_bullets[cattype][ mirrEx[n] ];
        }
    } else if (!mirrEx.length) {
      if(!filter_bullets[0][parentid]) {
          filter_bullets[cattype][catid] = {'type':cattype,'title':title,'parentid':parentid, 'title_full': title_full};
      }
      else {
          alert("Вы не можете добавить подраздел, если выбран весь раздел");
      }
    }
    var isConfirm = false;
    for (var j = 0; j <= 1; j++) {
      for (i in filter_bullets[j]) {
        curfb = filter_bullets[j][i];
        
        if (curfb['type'] == 0 || curfb['type'] == 1) {
           
          //не позволяем добавлять подкатегории при выбранной категории
          if (j==1 && filter_bullets[0][curfb['parentid']] != undefined) {
              if(isConfirm == false) {
                  if(confirm('Выбранный раздел заменит все добавленные вами ранее подразделы. Продолжить?')) {
                      delete filter_bullets[j][i];
                      isConfirm = true;
                      continue;
                  } else {
                      $('pf_category').value = 0;
                      FilterSubCategory($('pf_category').value);
                      delete filter_bullets[0][curfb['parentid']]; 
                      return false;
                  }
              } else {
                  delete filter_bullets[j][i];
                  isConfirm = true;
                  continue;
              }
          }

          data = data + '<input type="hidden" name="pf_categofy[' + j + '][' + i + ']" value="' + curfb['type'] + '">\r\n\r\n';

          //mirrored specs
          if ( j == 1 && filter_mirror_specs[i] && (filter_mirror_specs[i] instanceof Array) ) {
              for (var k = 0; k < filter_mirror_specs[i].length; k++) {
                  data = data + '<input type="hidden" name="pf_categofy[' + j + '][' + filter_mirror_specs[i][k] + ']" value="1">\r\n\r\n';
              }
          }
          
          ttl = '';
          if (curfb['title_full']) {
              ttl = ' title="' + curfb['title_full'] + '" ';
          }

          //моя специализация
          if (filter_user_specs[i] != 1) {
                if ( j == 0 ) {
                    data = data + '<li class="b-ext-filter__item b-ext-filter__item_green" ' + ttl + '><a href="javascript: void(0);" onclick="FilterAddBulletNew(0, ' + i + ',0,0);" class="b-ext-filter__spec"><span class="b-ext-filter__spec-inner"><span class="b-ext-filter__krest"></span>' + curfb['title'] + '</span></a></li>';
                } else if ( j == 1 ) {
                    data = data + '<li class="b-ext-filter__item b-ext-filter__item_light-green" ' + ttl + '><a href="javascript: void(0);" onclick="FilterAddBulletNew(1, ' + i + ',0,0);" class="b-ext-filter__spec"><span class="b-ext-filter__spec-inner"><span class="b-ext-filter__krest"></span>' + curfb['title'] + '</span></a></li>';
                }
          } else {
              if ( j == 1 ) {
                  data = data + '<li class="b-ext-filter__item b-ext-filter__item_yellow" ' + ttl + '><a href="javascript: void(0);" onclick="FilterAddBulletNew(1, ' + i + ',0,0);" class="b-ext-filter__spec"><span class="b-ext-filter__spec-inner"><span class="b-ext-filter__krest"></span>' + curfb['title'] + '</span></a></li>';
              } else {
                  data = data + '<li class="b-ext-filter__item b-ext-filter__item_green" ' + ttl + '><a href="javascript: void(0);" onclick="FilterAddBulletNew(0, ' + i + ',0,0);" class="b-ext-filter__spec"><span class="b-ext-filter__spec-inner"><span class="b-ext-filter__krest"></span>' + curfb['title'] + '</span></a></li>';
              }
          }
        }
      }
    }

    if ($('pf_specs')) {
	$('pf_specs').innerHTML = data;
	}
    if(curFBulletsBox == 2) {
        if ($('flt-cat')) $('flt-cat').getElements('div').setStyle('height', 'auto');  
      } else {
        if ($('flt-pl')) $('flt-pl').getElements('div').setStyle('height', 'auto');
    }
}


/**
 * @desc определить, есть ли элементы массива отраженных специальностей mirridList среди уже добавленных пользователем в фильтр ( filter_bullets )
 * @param Array mirridList - массив номеров отраженных специальностей
 * @param Array mirrEx     - массив, который будет заполнен номерами тех отраженных специальностей, котрые есть в filter_bullets
 * @param int cattype      - принимает значение 0 (группа специальностей) или 1 (специальность)
 * @global Array filter_bullets массив уже добавленных специальностей
 * */
function searchMirror(mirridList, mirrEx, cattype, title) {
    if ( !(mirridList instanceof Array) ) {
        mirridList= [];
    }
    for (var j = 0; j < mirridList.length; j++) {
        var mirrid = mirridList[j];
        if (cattype == 1 && filter_bullets[1][mirrid]!=undefined) {
          mirrEx.push(mirrid);
        }
        if (filter_bullets[cattype][mirrid]) {
            return 'Этот подраздел является зеркальным по отношению к тому, что уже выбран';
        }
        for(var i in filter_bullets[0]) {
            if(i>0) {
                if(filter_specs_ids[i][mirrid] && title && cattype == 1) {
                    mirrEx.push(mirrid);
                    return "Вы не можете добавить подраздел, так как он является зеркальным подразделом раздела, который вы уже выбрали";
                }
            } 
        } 
    }
    return '';
}

//Функция добавления или удаления буллета из списка выбранных специализаций в фильтре
//#0024211 - дело в том, что некоторые специальности имеют не одно "зеркало", заменил формат filter_mirror_specs с {main_prof => mirror_prof} на {main_prof =>[mirror_prof0, ..., mirror_profN ]}
//          так как ранее происходило "затирание" номера отраженной специальности при инициализации filter_mirror_specs
//Функция добавления или удаления буллета из списка выбранных специализаций в фильтре
function FilterAddBulletNew(cattype, catid, title, parentid, title_full) {
    var deleteAction = ( parentid == 0 &&  title == 0);
    var curfb, data = '';
    var mirridList = filter_mirror_specs[catid];
    var mirrEx = []; //#0024211 меняю булево значение на массив, существование зеркальных специальностей определяю по длине этого массива
    // Проверка на существование зеркальных разделов, уже имеющихся в фильтре
    var err = searchMirror(mirridList, mirrEx, cattype, title);
    if (err.length) {
        alert(err);
        return false;
    }
    //Если это добавление и не удалось найти зеркало по  cat_id - ищем в отражениях идентификатор главной специальности и повторяем поиск по нему
    if (!deleteAction) {
        for (var i in filter_mirror_specs) {
            if (!parseInt(i)) {
                continue;
            }
            var a = filter_mirror_specs[i];
            var b = [];
            for (var j = 0; j < a.length; j++) {
                if (a[j] == catid) {
                    err = searchMirror(a, b, cattype, title);
                    if (err.length) {
                        alert(err);
                        return false;
                    }
                }
            }
        }
    }
    if (!title) {
        delete filter_bullets[cattype][catid];
        for (var n = 0; n < mirrEx.length; n++) {
            delete filter_bullets[cattype][ mirrEx[n] ];
        }
    } else if (!mirrEx.length) {
        if(!filter_bullets[0][parentid]) {
            filter_bullets[cattype][catid] = {'type':cattype,'title':title,'parentid':parentid, 'title_full': title_full};
        }
        else {
            alert("Вы не можете добавить подраздел, если выбран весь раздел");
        }
    }
    var isConfirm = false;
    for (var j=0; j<=1; j++) {
        for (i in filter_bullets[j]) {
            curfb = filter_bullets[j][i];
            if (curfb['type'] == 0 || curfb['type'] == 1) {
            //не позволяем добавлять подкатегории при выбранной категории
            if (j==1 && filter_bullets[0][curfb['parentid']] != undefined) {
                if(isConfirm == false) {
                    if(confirm('Выбранный раздел заменит все добавленные вами ранее подразделы. Продолжить?')) {
                        delete filter_bullets[j][i];
                        isConfirm = true;
                        continue;
                    } else {
                        $('comboe').set("value", "Все разделы");
                        $('comboe_db_id').set("value", 0);
                        $('comboe_column_id').set("value", 0);
                        delete filter_bullets[0][curfb['parentid']]; 
                        return false;
                    }
                } else {
                    delete filter_bullets[j][i];
                    isConfirm = true;
                    continue;
                }
            }
            data = data + '<input type="hidden" name="pf_categofy[' + j + '][' + i + ']" value="' + curfb['type'] + '">\r\n\r\n';
            //mirrored specs
            if ( j == 1 && filter_mirror_specs[i] && (filter_mirror_specs[i] instanceof Array) ) {
                for (var k = 0; k < filter_mirror_specs[i].length; k++) {
                    data = data + '<input type="hidden" name="pf_categofy[' + j + '][' + filter_mirror_specs[i][k] + ']" value="1">\r\n\r\n';
                }
            }
            ttl = '';
            if (curfb['title_full']) {
                ttl = ' title="' + curfb['title_full'] + '" ';
            }

            var divider = ComboboxManager.getInput('comboe').DIVIDER;
            if (curfb['title'].indexOf(divider) != -1) {
                var arr = curfb['title'].split(divider);
                curfb['title'] = arr[arr.length - 1];
                if (curfb['type'] == 0) {
                  curfb['title'] = arr[0];
                }
            }
            //моя специализация
            if (filter_user_specs[i] != 1) {
                if ( j == 0 ) {
                    data = data + '<li class="b-ext-filter__item b-ext-filter__item_green" ' + ttl + '><a href="javascript: void(0);" onclick="FilterAddBulletNew(0, ' + i + ',0,0);" class="b-ext-filter__spec"><span class="b-ext-filter__spec-inner"><span class="b-ext-filter__krest"></span>' + curfb['title'] + '</span></a></li>';
                } else if ( j == 1 ) {
                    data = data + '<li class="b-ext-filter__item b-ext-filter__item_light-green" ' + ttl + '><a href="javascript: void(0);" onclick="FilterAddBulletNew(1, ' + i + ',0,0);" class="b-ext-filter__spec"><span class="b-ext-filter__spec-inner"><span class="b-ext-filter__krest"></span>' + curfb['title'] + '</span></a></li>';
                }
            } else {
                if ( j == 1 ) {
                    data = data + '<li class="b-ext-filter__item b-ext-filter__item_yellow" ' + ttl + '><a href="javascript: void(0);" onclick="FilterAddBulletNew(1, ' + i + ',0,0);" class="b-ext-filter__spec"><span class="b-ext-filter__spec-inner"><span class="b-ext-filter__krest"></span>' + curfb['title'] + '</span></a></li>';
                } else {
                    data = data + '<li class="b-ext-filter__item b-ext-filter__item_green" ' + ttl + '><a href="javascript: void(0);" onclick="FilterAddBulletNew(0, ' + i + ',0,0);" class="b-ext-filter__spec"><span class="b-ext-filter__spec-inner"><span class="b-ext-filter__krest"></span>' + curfb['title'] + '</span></a></li>';
                }
            }
          }
        }
    }
    if ($('pf_specs')) {
        $('pf_specs').innerHTML = data;
    }
    if(curFBulletsBox == 2) {
        if ($('flt-cat')) $('flt-cat').getElements('div').setStyle('height', 'auto');  
    } else {
        if($('flt-pl')) { $('flt-pl').getElements('div').setStyle('height', 'auto'); }
        if($('flt-pl-usr')) { $('flt-pl-usr').getElements('div').setStyle('height', 'auto'); }
    }
}


//Функция очистки полей фильтра
function FilterClearForm(f) {
    if(f == undefined) f = 'frm';
    var frm = $(f);
    if(!frm) return false;
    
    frm.getElement('#pf_cost_from').set('value', 0);
    frm.getElement('#pf_wo_budjet') && frm.getElement('#pf_wo_budjet').set('checked', true);
    frm.getElement('#pf_my_specs') && frm.getElement('#pf_my_specs').set('checked', false);
    frm.getElement('#pf_specs').set('html', '');
    frm.getElement('#comboe').set('value', 'Все разделы');
    ComboboxManager.getInput("comboe").resize(ComboboxManager.getInput("comboe").b_input, 1);
    frm.getElement('#location') && frm.getElement('#location').set('value', 'Все страны');
    frm.getElement('#location_db_id') && frm.getElement('#location_db_id').set('value', '');
    frm.getElement('#location_column_id') && frm.getElement('#location_column_id').set('value', '');
    frm.getElement('#pf_keywords').set('value', '');
    ComboboxManager.getInput("currency_text").selectItemById('2');
    frm.getElement('#pf_currency') && frm.getElement('#pf_currency').set('value', 2);
    
    return true;
}

// новая Функция очистки полей фильтра
function FilterClearFormNew(f) {
    if(f == undefined) f = 'flt-pl';
    filter_bullets = new Array();
    filter_bullets[0] = new Array();
    filter_bullets[1] = new Array();
    //$(f).getElements('div').setStyle('height', 'auto');
    try {
        $('pf_cost_from').set('value', 0);
        $('pf_cost_to').set('value', 0);
    } catch(e) {}
    $('pf_wo_budjet') && $('pf_wo_budjet').set('checked', true);
    if($('pf_my_specs')) {
        $('pf_my_specs').set('checked', false);
    }
    $('pf_specs').set('html', '');
    $('comboe').set('value', 'Все разделы');
    ComboboxManager.getInput("comboe").resize(ComboboxManager.getInput("comboe").b_input, 1);
    $('location') && $('location').set('value', 'Все страны');
    $('location_db_id') && $('location_db_id').set('value', '');
    $('location_column_id') && $('location_column_id').set('value', '');
    $('pf_keywords').set('value', '');
    $('pf_category').set('value', 0);
    $('for-less2').set('checked', false);
    $('for-pro').set('checked', false);
    $('for-urgent').set('checked', false);
    if( $('for-block') ) { $('for-block').set('checked', false); }
    $('for-ver').set('checked', false);
    ComboboxManager.getInput("currency_text").selectItemById('2');
    $('pf_currency') && $('pf_currency').set('value', 2);
    $('for-hide_exec').set('checked', false);
    // очищаем даты окончания конкурсов
    $$('input[name="pf_end_days_from"]') && $$('input[name="pf_end_days_from"]').set('value', '');
    $$('input[name="pf_end_days_to"]') && $$('input[name="pf_end_days_to"]').set('value', '');
}


/**
 * Обновление пунтка меню по необходимости
 */
function NotificationMenuItemUpdate(obj, clss)
{
    if(!obj || !obj.success) return false;
    
    var link = $$('.'+clss+' a');
    if(!link) return false;
    
    var q = link.getElement('.b-user-menu-clause-quantity')[0];
    if(q) q.destroy();

    link.set('title',obj.tip);
    if(obj.count > 0) {
        link.set('html',link.get('html') + obj.count_html);
        if(typeof obj.link !== "undefined") link.set('href',obj.link);
    }
    
    return true;
}


/**
 * Получение количества новых личных сообщений и событий сбр
 */
function Notification() {
    if ( !_UID || window.NEO ) {
        return false;
    }
    
    if(window._NEW_TEMPLATE != undefined) {
        
        new Request.JSON({
            url: '/notification.php',
            onSuccess: function(resp) {
                if (resp) {
                    if (resp.success ) {
                        if (resp.pro && resp.pro.action == 'done') {
                            if(resp.pro.role == 'FRL') {
                                var html_pro = new Element('a', {'href':  '/payed/', 
                                                                 'class': 'b-bar__link b-bar__link_underline  b-bar__link_margtop_8  b-bar__link_float_left',
                                                                 'id'   : 'b-bar__pro-btn',
                                                                 'html' : 'Купить<span class="b-icon__pro b-icon_margleft_5 b-icon__pro_f"></span>'
                                                             });
                            } else {
                                var html_pro = new Element('a', {'href':  '/payed-emp/', 
                                                                 'class': 'b-bar__link b-bar__link_underline  b-bar__link_margtop_8  b-bar__link_float_left',
                                                                 'id'   : 'b-bar__pro-btn',
                                                                 'html' : 'Купить<span class="b-icon__pro b-icon_margleft_5 b-icon__pro_e"></span>'
                                                             });
                            }
                            $('b-bar__pro-btn').destroy();
                            $('b-bar__pro').grab(html_pro);
                        }

                        // сообщения
                        NotificationMenuItemUpdate(resp.msg, 'b-user-menu-messages-clause');
                        // СБР
                        NotificationMenuItemUpdate(resp.sbr, 'b-user-menu-contracts-clause');
                        // СЧЕТ
                        NotificationMenuItemUpdate(resp.bill, 'b-user-menu-wallet-clause');
                        // проекты
                        NotificationMenuItemUpdate(resp.prj, 'b-user-menu-tasks-clause');
                        // Заказы ТУ
                        NotificationMenuItemUpdate(resp.tu, 'b-user-menu-orders-clause');
                    }
                    
                    if (resp.token && resp.token != _TOKEN_KEY) {
                        _TOKEN_KEY = resp.token;
                        U_TOKEN_KEY = resp.token;
                        CSRF_Clear();
                        CSRF(_TOKEN_KEY);
                    }
                    
                    Notification.delay(resp.delay);
                }
            }
        }).post({
            'op': 'msg|sbr|prj|bill|tu',
            'u_token_key': _TOKEN_KEY
        });
    } else {
        new Request.JSON({
            url: '/notification.php',
            onSuccess: function(resp) {
                if ( resp && resp.success ) {
                    if ( resp.msg && resp.msg.success ) {
                        if ( resp.msg.count > 0 ) {
                            var s = resp.msg.count + ending(resp.msg.count, ' новое сообщение', ' новых сообщения', ' новых сообщений');
                            $('userbar_message').set('html', s);
                            $$('.b-userbar__mess').removeClass('b-userbar__mess_hide');
                            $$('.b-userbar__icmess').addClass('b-userbar__icmess_hide');
                            $('userbar_link_msgs').addClass('b-userbar__link_green');
                        } else {
                            $('userbar_message').set('html', 'Мои контакты');
                            $$('.b-userbar__mess').addClass('b-userbar__mess_hide');
                            $$('.b-userbar__icmess').removeClass('b-userbar__icmess_hide');
                            $('userbar_link_msgs').removeClass('b-userbar__link_green');
                        }
                    }
                    if( resp.sbr && resp.sbr.success ) {
                        if( resp.sbr.count > 0 ) {
                            $$('.b-userbar__sbric').removeClass('b-userbar__sbric_hide');
                            $$('.b-userbar__icsbr').addClass('b-userbar__icsbr_hide');
                        } else {
                            $$('.b-userbar__sbric').addClass('b-userbar__sbric_hide');
                            $$('.b-userbar__icsbr').removeClass('b-userbar__icsbr_hide');
                        }
                    }
                    if(resp.token) {
                        _TOKEN_KEY = resp.token;
                        U_TOKEN_KEY = resp.token;
                        CSRF_Clear();
                        CSRF(_TOKEN_KEY);
                    }

                    if( resp.prj && resp.prj.success ) {
                        if(resp.prj.count > 0) {
                            $$('.b-userbar__prjic').removeClass('b-userbar__prjic_hide');
                            $$('.b-userbar__icprj').addClass('b-userbar__icprj_hide');
                        } else {
                            $$('.b-userbar__prjic').addClass('b-userbar__prjic_hide');
                            $$('.b-userbar__icprj').removeClass('b-userbar__icprj_hide');
                        }
                        // Для работодателей выводим количество новых сообщений, с ссылкой на последний.
                        if(resp.prj.count_msg > 0) {
                            if($('new_dialogue_messages') != undefined) $('new_dialogue_messages').destroy();
                            var b_new_messages = new Element('span', {id: 'new_dialogue_messages', 'class':'b-user__numberprj'});
                            b_new_messages.set('html', '(<a class="b-userbar__toplink" href="/projects/?pid='+resp.prj.last_emp_new_message+'" title="Есть новые сообщения">' + resp.prj.count_msg + '</a>)');
                            $('new_offers_messages').adopt(b_new_messages);
                        }
                    }
                    Notification.delay(resp.delay);
                }
            }
        }).post({
            'op': 'msg|sbr|prj',
            'u_token_key': _TOKEN_KEY
        });
    }
    return true;
}

function maxlength(itm,max) { if(itm.value.length>max)itm.value=itm.value.substr(0,max);if(!itm.onkeyup)itm.onkeyup=function(){return maxlength(itm,max);}; } 

function submitLock(form,p,nolock) {
    if(!nolock&&form.submitting===1) return false;
    if(form.onsubmit && form.onsubmit()===false) return false;
    if(p!=null) {
        for(var k in p) {
            form[k].value = p[k];
        }
    }

    form.submitting=1;
    form.submit();
    return true;
}

function submitEnter(myfield,e) {
    var keycode;
    if (window.event) keycode = window.event.keyCode;
    else if (e) keycode = e.which;
    else return true;
    
    if (keycode == 13) {
       myfield.form.submit();
       return false;
    }
    else
       return true;
}

function ending(num, v1, v2, v3) {
    var e = num % 10;
    if (((num == 0) || ((num > 5) && (num < 20))) || ((e == 0) || (e > 4))) {
        return v3;
    } else if ( e == 1 ) {
        return v1;
    } else {
        return v2;
    }
}

function getICOFile($ext) {
    var $ico = 'unknown';
    switch ($ext) {
        case "swf":
            $ico = 'swf';
            break;

        case "mp3":
            $ico = 'mp3';
            break;

        case "rar":
            $ico = 'rar';
            break;

        case "doc":
        case "docx":
            $ico = 'doc';
            break;

        case "pdf":
            $ico = 'pdf';
            break;

        case "ppt":
            $ico = 'ppt';
            break;

        case "rtf":
            $ico = 'rtf';
            break;

        case "txt":
            $ico = 'txt';
            break;

        case "xls":
        case "xlsx":
            $ico = 'xls';
            break;  

        case "zip":
            $ico = 'zip';
            break;
        case "jpg":
        case "jpeg":   
            $ico = 'jpeg';
            break;
        case "png":
            $ico = 'png';
            break; 
        case "ai":
            $ico = 'ai';
            break; 
        case "bmp":
            $ico = 'bmp';
            break; 
        case "psd":
            $ico = 'psd';
            break; 
        case "gif":
            $ico = 'gif';
            break;   
        case "flv":
            $ico = 'flv';
            break;   
        case "wav":
            $ico = 'wav';
            break;
        case "ogg":
            $ico = "ogg";
            break;
        case "3gp":
            $ico = "3gp";
            break; 
        case "wmv":
            $ico = "wmv";
            break;
        case "tiff":
            $ico = "tiff";
            break;
        case "avi":
            $ico = "avi";
            break;
        case "mkv":
            $ico = "hdv";
            break;
        case "ihd":
            $ico = "ihd";
            break;
        case "fla":
            $ico = "fla";
            break;
        default:
            $ico = 'unknown';
            break;

    }
    
    return $ico;
}

var BlinkCounter = new Array();
var IDBlinkInterval = new Array();
var MAX_BLINK  = 4;

function init_blink_icons() {
    var i = 0;
    if($('b-bar__inner') != undefined) {
        $('b-bar__inner').getElements('.b-bar__item_active').each(function(elm) {
            var eID = $(elm).getProperty('id');
            if(eID != null) {
                BlinkCounter[i]    = 0;
                clone_icons(eID);
                IDBlinkInterval[i] = setInterval("blink_icons('" + eID + "', '" + i + "')", 1000);
                i++;
            }
        });
    }
}

function clone_icons(id) {
    var size = $(id).getPosition();
    $(id).getElement('.b-bar__btn').setProperty('id', id + '_btn');
    var clone = $(id + '_btn').clone();
    clone.addClass('b-bar__btn_clone');
    clone.setProperty('id', id + '_clone');
    //$(id).addClass('b-bar__item_animate');
    //clone.setPosition({x: size.x, y : 0});
    
    $(id + '_btn').grab(clone, 'after');
}

function blink_icons(id, x) {
    if(BlinkCounter[x] == MAX_BLINK) {
        clearInterval(IDBlinkInterval[x]);
        $(id + '_clone').dispose();
        //$(id).removeClass('b-bar__item_animate');
        return;
    }
    var myElementsEffects = new Fx.Elements($(id + '_btn'),  { duration: 650 });

    myElementsEffects.start({
        '0': { 'opacity': [0,1]},
        '1': { 'opacity': [0.2, 0.3]}
    });
    BlinkCounter[x]++;
}

/**
 * прокрутка с помощью javascript
 * учитывается высота юзербара
 * el - DOM-элемент до которого нужно прокрутить страницу
 * animate - анимированая прокрутка
 */
function JSScroll (el, animate) {
    if (el) {
        el = $(el);
		var bh = $$('.b-bar')[0].getSize().y;
        var xScroll = window.getScroll().x;
        var yScroll = el.getPosition().y  - bh - 10; //  высота юзербара
        var yScroll = yScroll < 0 ? 0 : yScroll;
        if (animate) {
            new Fx.Scroll(window, {offset: {y: - bh - 10}}).toElement(el);
        } else {
            window.scrollTo(xScroll, yScroll);
        }
    }
}

// модернизируем якоря для новой главной
// чтобы при прокрутке контент не залазил под юзербар
window.addEvent('domready', function(){
    $$('a').each(function(a, index, array){
        // у якоря должен отсутствовать атрибут href и присутствовать - name
        var href = a.get('href');
        var name = a.get('name');
        if (href || !name) {
            return;
        }
        var wrapper = new Element('div', {'class':'b-anchor'});
        //wrapper.setStyle('position', 'relative');
        a.addClass('b-anchor__link');
        wrapper.wraps(a);
    })
})



function change_type_ban(type) {
    if(type == '') type = 'image';
    $$('.ban_types').hide();
    $$('.type_' + type).show();
}


// Костыль для Safari
// Если в Safari включено автозаполнение, то форма смены пользователя заполняется данными авторизованного пользователя
// Между событиями domready и load в форме смены пользователя не должно быть инпута с type="password"
if (Browser.safari) {
    window.addEvent('domready', function(){
        var passwd = $$('#asw_form').getElements('#passwd')[0];
        if (passwd) {
            passwd.set('type', 'text');
        }
    });
    window.addEvent('load', function(){
        var passwd = $$('#asw_form').getElements('#passwd')[0];
        if (passwd) {
            passwd.set('type', 'text');
        }
    });
}

function returnHelpBlock() {
    var d = new Date();
    d.setMonth(d.getMonth() - 1);
    document.cookie='close_help=0; expires=' + d.toGMTString() + '; path=/';
    
    $$('.helpsite-block').removeClass('b-fon_hide');
    $('return_help_link').addClass('b-help__txt_hide');
    $('b-bar__help').removeClass('b-bar__item_active');
}

function init_help() {
    if($('close_help')) {
        $('close_help').addEvent('click', function(){
            //$('return_help_link').removeClass('b-help__txt_hide');
            Cookie.write('close_help', 1, {duration: 356});
            $$('.helpsite-block').addClass('b-fon_hide');
            
            var btn = $('b-bar__help');
            btn.addClass('b-bar__item_active');
            var i   = BlinkCounter.length;
            BlinkCounter[i] = 0;
            clone_icons(btn.getProperty('id'));
            IDBlinkInterval[i] = setInterval("blink_icons('" + btn.getProperty('id') + "', '" + i + "')", 1000);
        });
    }
}
/**
 * Открывает и закрывает подкат в комментарии
 * */
function switchCut() {
    var p = this.parentNode.parentNode;   
    var ls = p.getElementsByTagName('div');
    var div = false;
    for (var i = 0; i < ls.length; i++) {
        if (ls[i].className == 'cat') {
            div = ls[i]; 
        }
    }
    if (!div) {
        return;
    }
    var fx = new Fx.Slide(div);    
    if (div.style.display == 'none') {
    	fx.hide();
        div.style.display = 'block';            	
    }    
    if (this.get('text') ==  'Развернуть') {
        this.set('text', 'Свернуть');
    } else {
        this.set('text', 'Развернуть');
    }    
    fx.toggle();    
}

/**
 * Проверка валидности ссылки на youtube видео
 * */
function js_video_validate(url) {
	var re = /^http:\/\//i;
    if (!(re).test(url)) url = 'http://' + url;
    var re = /^(http:\/\/youtu\.be\/([-_A-Za-z0-9]+))/i;
    if (re.test(url)) {
        return true;
    } 
    re = /^(http:\/\/(?:ru\.|www\.)?youtube\.com\/watch\?).*(v=[-_A-Za-z0-9]+)/i;
    if (re.test(url)) {
           return true;
    }
	var re = /^(http:\/\/(?:www\.)?rutube\.ru\/video\/?[-_A-Za-z0-9]+\/{0,1})/i;
    if (re.test(url)) {
        return true;            
    } 
    var re = /^(http:\/\/(?:www\.|video\.)?rutube\.ru\/(?:tracks\/)?[-_A-Za-z0-9]+(?:\.html)?)/i;
    if (re.test(url)) {
        return true;     
    }
    var re = /^(http:\/\/(?:www\.)?vimeo\.com\/[0-9]+)/i;
    if (re.test(url)) {
           return true;
    }    
    return false;    
}

function center_popup(cls) {
    if(cls == undefined) cls = '.b-shadow_center';
    $$(cls).each(function (popup_elm) {
        var winSize = $(document).getSize();
        var scrollSize = $(document).getScroll();
        var elemSize = popup_elm.getSize();
        popup_elm.setPosition({
            x: scrollSize.x,
            y: scrollSize.y - winSize.y
        });
    });
}

function shadow_popup(cls) {
    if(cls == undefined) cls = '.b-shadow_center';
    $$(cls).each(function (popup_elm) {
        var winSize = $(document).getSize();
        var elemSize = popup_elm.getSize();
        popup_elm.setPosition({
            x: (winSize.x - elemSize.x) / 2,
            y: (winSize.y - elemSize.y) / 2
        })
    });
    
    $$('.b-shadow__icon_close').addEvent('click',function() {
        if(this.getParent('.b-shadow') && this.getParent('.b-shadow').hasClass('b-filter__toggle')){
            this.getParent('.b-shadow').addClass('b-filter__toggle_hide')
        } else if(this.getParent('.b-shadow')) {
            this.getParent('.b-shadow').addClass('b-shadow_hide');
			$$('body').setStyle('overflow','');//снимаем защиту от прокрутки страницы при скроле попапа
            $$('div.b-filter__overlay').destroy();
        }
    })
}

function debug_redirectSubdomain(link, domain, subdomain, protocol) {
    if(protocol == undefined) protocol = 'http://';
    domain = domain.replace(protocol, ''); // ?prof_id
    link += '?region_filter=' + subdomain;
    window.location = protocol + domain + link;
}

function redirectSubdomain(link, domain, subdomain, protocol) {
    if(protocol == undefined) protocol = 'http://';
    var split = domain.split('.');
    if (split.length > 2 ) {
        split[0] = subdomain;
        domain = split.join('.');
    }
    window.location = protocol + domain + link;
}
    
// обработка попапа выбора пополнения счета/оплаты СБР
window.addEvent('domready', function(){
    var $chkWrap = $('bill_checkboxes_wrap');
    var $billLink = $('bill_link');
    if (!$chkWrap || !$billLink) {
        return;
    }
    var $chk = $chkWrap.getElements('input.bill_type_input');
    $chk.addEvent('change', change);
    $chk.addEvent('click', change);
    function change (){
        var href = this.get('value');
        $billLink.set('href', href);
        $billLink.removeClass('b-button_disabled');
    }
});

/**
 * форматирует строку
 * @param string string форматируемая строка
 * @param object options опции форматирования
 *      - spacing - добавлять пробелы, например {spacing: 20} бедет вставлять пробелы через каждые 20 символов
 * @returns string отформатированная строка
 */
window.reformat = function (string, options) {
    
    if (!options) {
        options = {};
    }
    
    var reg;
    
    // добавление пробелов в длинные безпробельные слова
    if (options.spacing) {
        reg = new RegExp('([^\\s]{' + (+options.spacing) + '})', 'gi');
        string = string.replace(reg, '$1 ')
    }
    
    return string;
}
/**
 * переходит к родительскому комментарию
 */
function gotoTopComment(evt) {
	if (Browser.ie && Browser.version == 8) {
 		evt.target = evt.srcElement;
    }
    //клик на ссылку Вверх в самом предложении.
    if (evt.target.parentNode.parentNode.parentNode.className == "thread-start") {
        return true;
    }
    var trg = evt.target.getParent("li.thread");
    //если клик был на ссылке "Вверх" первого коментария ветви, идем к предложению
    var skipCycle = 0;
    if ( trg.parentNode.tagName.toLowerCase() == "ul" && trg.parentNode.className == "thread-list" ) {
        var id = trg.parentNode.id.replace("comments-", "");
        trg = $("offer-" + id);
        skipCycle = 1;
    }
    //иначе ищем родителя
    var b = 0;
    while ( true && (skipCycle == 0) ) {
        trg = trg.getParent("li.thread");
        if (!trg) {
            return false;
        }
        if ( trg.parentNode.tagName.toLowerCase() == "ul" && trg.parentNode.className == "thread-list" ) {
            break;
        }
        b++;
        if (b > 1000) {
            return false;
        }
    }
    var tempId = trg.getElement("div.b-anchor").getElement("a.b-anchor__link").get("name");
    evt.target.set("href", "#" + tempId);
    setTimeout(
        function() {
            window.scrollBy(0, -32);
        },
        100
    );
    return true;
}
/**
 * @desc Отправляет методом POST u_token_key по адресу $(linkId).href
 * @param String linkId     - идентификатор ссылки
 * @param String confirmMsg - текст ссылки
**/
function addTokenToLink(linkId, confirmMsg) {
    if (confirm(confirmMsg) ) {
        var id = linkId;
        if ($(id) && $(id).href) {
            var s = '<form name="admin_unblocked_project_form" action="' + $(id).href + '" method="POST"><input type="hidden" name="u_token_key" value="' + _TOKEN_KEY + '" /></form>';
            var e = new Element("div", {html:s});
            e.inject(document.body, "bottom");
            document.forms.admin_unblocked_project_form.submit();
        }
    }
    return false;
}
/**
 * @desc Сохраняет значение в хранилище или куках если хранилище недоступно
 * @param String key     - ключ
 * @param String value - значение
**/
function storeValue(key, val) {
    if (localStorage) {
        localStorage.setItem(key, val);
    } else {
        Cookie.write(key, val);
    }
}
/**
 * @desc Считывает ранее сохраненое storeValue(key, val) значение 
 * @param String key     - ключ
 * @return String value - значение
**/
function getStoreValue(key) {
    var val;
    if (localStorage) {
        val = localStorage.getItem(key);
    } else {
        val = Cookie.read(key, val);
    }
    if (!val  || String(val) == "null") {
        val = null;
    }
    return val;
}
/**
 * @desc Добавляет к корню слова окончание в зависимости от величины числа n
 * @param n - число
 * @param root корень слова
 * @param one окончание в ед. числе
 * @param less4 окончание при величине числа от 1 до 4
 * @param more19 окончание при величине числа более 19
 * @returString
 */
 function getSuffix(n, root, one, less4, more19) {
         var m = String(n);
         if (m.length > 1) {
             m =  parseInt( m.charAt( m.length - 2 ) + m.charAt ( m.length - 1 ) );
         }
         var lex = root + less4;
         if (m > 20) {
             var r = String(n);
             var i = parseInt( r.charAt( r.length - 1 ) );
             if (i == 1) {
                 lex = root + one;
             } else {
                 if (i == 0 || i > 4) {
                    lex = root + more19;
                 }
             }
         } else if (m > 4) {
             lex = root + more19;
         } else if (m == 1) {
             lex = root + one;
         }
         return lex;
 }
/**
 * @desc Промотать окно в положение, сохраненное в переменной кук или хранилища scrollTop
 * Переменная scrollTop после этого устанавливается в ''
 */
function resetScrollTop() {
    var scrollTop = parseInt(getStoreValue("scrollTop"));
    if (!scrollTop) {
        scrollTop = 0;
    }
    if (scrollTop) {
        window.scrollTo(0, scrollTop);
        storeValue("scrollTop", '');
    }
}

function addCategoriesShadows() {
    $$('.b-cat__item').each(function(item) {
        var link = item.getElement("a.b-cat__link");
        if (link) {
            if (item.getSize().x < link.getSize().x) {
                item.addClass('b-cat__item_ellipsis');
            } else {
                item.removeClass('b-cat__item_ellipsis');
            }
        }
    });
}

window.addEvent('domready', addCategoriesShadows);
window.addEvent('resize', addCategoriesShadows);

/**
 * функции связанные с текстом ссылки отправки смс
**/
function CsmsLimit(linkId){
    this.linkId = linkId;
    if (!linkId) {
        this.linkId = "getsms";
    }
    this.exceedList = [];
}
CsmsLimit.prototype.addNumberToLimitExceedList = function () {
    var n = smsNumber.replace(/\D/g, '');
    if (n.length) {
        this.exceedList[n] = 1;
    }
}
/**
 * 
**/
CsmsLimit.prototype.numberInExceedList = function(val) {
    var n = val.replace(/\D/g, '');
    if (n.length && this.exceedList[n] == 1) {
        return true;
    }
    return false;
}
/**
 * Установить текст ссылке запроса смс и сделать ее кликабельной или нет
 * @param text - текст ссылки, если пуст, то оставляем прежний
 * @param clickable - кликабельно или нет
 * @param key       - если указан, поместить text в хранилище с этим номером
 * @param isExceed  - если true номер key будет помещен в exceedList
**/
CsmsLimit.prototype.setSmsLink = function(text, clickable, key, isExceed) {
    var id = this.linkId;
    if ( $(id) ) {
        var disable = 'b-layout__link_bordbot_dot_80';
        var enable  = 'b-layout__link_bordbot_dot_0f71c8';
        if (clickable) {
            $(id).addClass(enable).removeClass(disable);
        } else {
            $(id).addClass(disable).removeClass(enable);
        }
        if (text.length) {
            $(id).set('text', text);
        }
    }
    if (key) {
        var n = key.replace(/\D/g, '');
        if (n.length) {
            //text:timestamp[:exceed]
            var t = Math.round( new Date().valueOf() / 1000 );
            var s = text + ':' + t + (isExceed ? ':1' : '');
            storeValue(n, s);
            if (isExceed) {
                this.addNumberToLimitExceedList(n);
            }
        }
    }
}
/**
 * Восстановить текст ссылки по номеру, если такой есть в хранилище и с момента сохранения прошло менее 24 часов
 * Если не найден или найдин и прошло более суток возвращает "Получить смс с кодом (осталось LIMIT_SMS_TO_NUMBER попыток)"
 * @param phone - кликабельно или нет
**/
//localStorage.clear();
CsmsLimit.prototype.restoreLinkTextByPhone = function(phone) {
    var s = "Получить SMS с кодом (" + LIMIT_SMS_TO_NUMBER + " " + getSuffix(LIMIT_SMS_TO_NUMBER, "попыт", "ка", "ки", "ок") + ")";
    phone = phone.replace(/\D/g, '');
    var q = String( getStoreValue(phone) ).split(':');
    if (q.length > 1) {
        var time = q[1];
        if ( parseInt(time) == time ) {
            var t = Math.round( new Date().valueOf() / 1000 );
            if (t - time < 24*3600) {
                var exceed = (q[2] == 1);
                var cl = !exceed;
                if (exceed) {
                    this.addNumberToLimitExceedList(phone);
                } else {
                    if ( window.smstimeout && window.sit) {
                        var n = window.sit - t + window.smstimeout;
                        cl = (n < 0);
                    }
                }
                this.setSmsLink(q[0], cl);
                return;
            }
        }
    }
    var cl = 1;
    if ( window.smstimeout && window.sit) {
        var n = window.sit - t + window.smstimeout;
        cl = (n < 0);
    }
    this.setSmsLink(s, cl);
}

//Предотвращаем засорение адреса страницы после авторизации через Facebook
if (window.location.hash && window.location.hash == '#_=_') {
    window.location.hash = '';
}