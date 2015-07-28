var billing = new Object();

billing.init = function(sett) {
	/**/
	
	if(sett == undefined) sett = {};
	
	this.errorInputClassName = 'invalid'; // Если в поле была ошибка. название класса которая это отображает
	this.clearInputClassName = 'i-bold';
	
	if(sett.maxLen == undefined) this.maxLen = 300;
	else this.maxLen = sett.maxLen;
	
	if(sett.form_id == undefined) this.form = 'frm';
	else this.form = sett.form_id;
	
	if(sett.button == undefined) this.button = 'send';
	else this.button = sett.button; 
};

function validateQiwi() {
    var valid = true;
    var rur   = '';
    var fm    = '';
    if ($('qiwi_rur_edit')) {
        rur   = $('qiwi_rur_edit').value;
    }
    if ($('qiwi_fm_edit')) {
        fm    = $('qiwi_fm_edit').value;
    }
    var phone = $('phone').value;
    var reg   = /^\d{10}$/;
    
    billing.clearEvent($('phone'));
    if ($('qiwi_fm_edit')) {
        billing.clearEvent($('qiwi_fm_edit'));
    }
    billing.clearEvent($('qiwi_rur_edit'));
    
    if (!reg.test(phone)) {
        billing.tipView2($('phone'), 'Введите номер телефона в федеральном формате без "8" и без "+7"', '<b>Пример:</b> 9161234567');
        valid = false;
    }
    
    if ( !billing.isNumeric(String(fm)) && $('qiwi_fm_edit')) {
        billing.tipView($('qiwi_fm_edit'), 'Пожалуйста, введите числовое значение');
        valid = false;
    }
    
    if ( !billing.isNumeric(String(rur)) ) {
        billing.tipView($('qiwi_rur_edit'), 'Пожалуйста, введите числовое значение');
        valid = false;
    }
    else {
        var sum  = parseFloat(rur);
        var txt2 = 'Введите сумму от ' + min_sum + ' до ' + max_sum;
        
        if ( sum > max_sum ) {
            billing.tipView2($('qiwi_rur_edit'), 'Слишком большая сумма', txt2);
            valid = false;
        }
        
        if ( sum < min_sum ) {
            billing.tipView2($('qiwi_rur_edit'), 'Минимальная сумма &mdash; ' + min_sum + ' руб.', txt2);
            valid = false;
        }
    }
    
    
    return valid;
}

billing.isNumeric = function(str, c) {
	if(c == undefined) {
		var numericExpression = /^ *(?:\d[\d ]*|\d*( \d+)*[.,]\d*) *$/; ///^[0-9]+[\,\.\s]*([0-9]+)?$/;
	} else {
		var numericExpression = /^[0-9]+?$/;
	}
	if(str.match(numericExpression)){
		return true;
	} else {
		return false;
	}	
};

billing.isNull = function(val, num) {
	if(val.length == 0) return true;
	
	if(num != undefined && val == 0) return true;
	
	return false;
};

billing.isMaxLen = function(obj) {
	this.clearEvent(obj);
	var r = this.maxLen-obj.value.length;
	
    var num = obj.value.length;
    var e   = num % 10;
    
    if (((num == 0) || ((num > 5) && (num < 20))) || ((e == 0) || (e > 4))) {
        result = num + ' символов';
    } else if (e == 1) {
        result = num + ' символ';
    } else {
        result = num + ' символа';
    }
        
	$$('#count_length').set('text', result);
	if(obj.value.length > this.maxLen) {
 		this.tipView(obj, 'Вы ввели слишком длинный текст'); 
 	}
 		
};

billing.tipView = function(obj, text) {
	if(text == undefined)   text = '';
	
	this.text_tip   = text;
	
	this.setErrorMode(obj);
};

billing.tipView2 = function( obj, text1, text2 ) {
    if ( text1 == undefined ) text1 = '';
    if ( text2 == undefined ) text2 = '';
    
    this.clearEvent(obj);
    var tpl = '<div class="tip-in"><div class="tip-txt"><div class="tip-txt-in">'
        + '<span class="middled" id="' + obj.id + '_txt"><strong>' + text1 + '</strong><em>' + text2 + '</em></span>'
        + '</div></div></div>';
    
    var idDiv = obj.id+'_tip';
    var div = new Element('div', {'id':idDiv, 'class':'tip'});
    div.set("html", tpl);
    
    $$('#'+obj.id).addClass(this.errorInputClassName);
    $$('#'+obj.id+'_parent').adopt(div);
};

/* Начальное состояние */
billing.clearEvent = function(obj) {
	$$('#'+obj.id).removeClass(this.errorInputClassName);
	$$('#'+obj.id+'_tip').destroy();
};

billing.clearEvents = function(obj) {
	$$('#'+obj.id1).removeClass(this.errorInputClassName);
	$$('#'+obj.id1+'_tip').destroy();
	$$('#'+obj.id2).removeClass(this.errorInputClassName);
	$$('#'+obj.id2+'_tip').destroy();		
};

billing.setErrorMode = function(obj) {
	this.clearEvent(obj);
	var tpl = '<div class="tip-in"><div class="tip-txt"><div class="tip-txt-in"><span class="middled" id="'+obj.id+'_txt"><strong>'+this.text_tip+'</strong></span></div></div></div>';
	var idDiv = obj.id+'_tip';
	var div = new Element('div', {'id':idDiv, 'class':'tip'});
	div.set("html", tpl);

	$$('#'+obj.id+'_parent').adopt(div);
	$$('#'+obj.id).addClass(this.errorInputClassName);
};

billing.checkSend = function(p) {
	if ( $('paysumfm') ) {
		var err = false;
		var obj = $('paysumfm');
		if(this.isNumeric(obj.value) == false || obj.value == '') {
			this.tipView(obj, 'Пожалуйста, введите числовое значение');
			err = true;
		}
		var obj = $('paysum');
		if(this.isNumeric(obj.value) == false || obj.value == '') {
			this.tipView(obj, 'Пожалуйста, введите числовое значение');
			err = true;
		}
		if (err) {
			return false;
		}
	}
	if(p < 0.01) {
		this.popup_info('Ошибки ввода. Исправьте их и повторите отправку.');
		return false;
	}

    if($$('.tip').length == 0 || $$('.tip').get('id') == "" || $$('.tip').get('id') == 'err_safety_phone') return true;
	else {
		this.popup_info('Ошибки ввода. Исправьте их и повторите отправку.'); //alert('Ошибки ввода. Исправьте их и повторите отправку.'); 
		return false;
	}
	
	return true;
};

billing.getPageSize = function() {
    var xScroll, yScroll;
    if (window.scrollMaxX || window.scrollMaxY) {  
        xScroll = window.innerWidth  + window.scrollMaxX;
        yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight >= document.body.offsetHeight){ // all but Explorer Mac
        xScroll = document.body.scrollWidth;
        yScroll = document.body.scrollHeight;
    }
    var windowWidth, windowHeight;
    if (self.innerHeight) { // all except Explorer
        windowWidth = self.innerWidth;
        windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
        windowWidth = document.documentElement.clientWidth;
        windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
        windowWidth = document.body.clientWidth;
        windowHeight = document.body.clientHeight;
    }
    pageHeight = Math.max(windowHeight, yScroll || 0);
    pageWidth = Math.max(windowWidth, xScroll || 0);
    return { page: [pageWidth, pageHeight], window: [windowWidth, windowHeight] };
};

billing.popup_info_timer = false;
billing.popup_info_pos = function() {
    if(!$("info_popup")) return false;
    var topScr = this.getBodyScrollTop();
    
    var pSize= this.getPageSize()['window'];
    var el = $("info_popup");
    el.set("styles", {"top": (topScr-100)+"px", "left": (pSize[0])/3+'px'} );
    
    
    var animate = function() {
        el.setStyle("top", (el.getStyle("top").toInt()+6)+"px");
        if(el.getStyle("top").toInt() > topScr+250) {
            timer = $clear(timer);
        }
    }
    
    var timer = animate.periodical(1);
    
   
    el.setStyle("display", "inline");//show();
};
billing.popup_info_close = function() {
    clearTimeout(this.popup_info_timer);
    $$('#info_popup').setStyle("display", "none");
    $$('.i-btn').set('disabled', 0);
};

billing.popup_info = function(text) {
    $$('.i-btn').set('disabled', 1);
    if(!text) {
        text = 'Ошибка ввода';
    }
    var style = 'font-size:80%; border:#7d0000 solid 2px;height:auto;width:auto;overflow:auto; position: absolute;display:block;left:-1000px;top:-1000px;text-align:center;padding:20px;background-color:#ffc5c5;z-index: 1000;';
    if(!$("info_popup")) {
        var div = new Element('div', {
		    'title'   : 'Кликните по сообщению, чтобы скрыть его',
		    'style'   : style,
		    'id'      : 'info_popup',
		    'onclick' : 'billing.popup_info_close();',
		    'text'    : text
		});
        
        $(document.body).adopt(div);//.set('html', '<div title="Кликните по сообщению, чтобы скрыть его" id="info_popup" style="'+style+'" onclick="billing.popup_info_close();">'+text+'</div>');
    } else {
        $$("#info_popup").set("html", text);
    }
    
    var topScr = this.getBodyScrollTop();
    this.popup_info_pos();
    var el = $("info_popup");
    
    el.setStyle("dislay", "inline");
    /*
    el.animate({"top": "+=350px"}, 1500);
    //el.fadeIn(1000);
    this.popup_info_timer = setTimeout('$$("#info_popup").fadeOut("fast", function() {billing.popup_info_close();}); ', 3000);
    $(document).bind("scroll", (function() {this.popup_info_pos();}));*/
    
    this.popup_info_timer = setTimeout(function() { billing.popup_info_close();}, 3000);
};

billing.getBodyScrollTop = function() {
    return self.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
};


billing.setSum
=function(sum, fmsum) {
    if(sum==null)
        sum = v2f(fmsum*this.exch);
    if(fmsum==null) {
        if(v2f(this.fmsum*this.exch)!=sum)
            fmsum = v2f(sum/this.exch);
        else
            fmsum = this.fmsum;
    }
    this.sum = sum;
    this.fmsum = fmsum;
};

billing.changeSum
=function(val) {
    val = v2f(val==null ? this.bx_sum.value : val);
    this.setSum(val, null);
    if (this.bx_fmsum)
        this.bx_fmsum.value = f2v(this.fmsum);
    if (this.bx_sum)
        this.bx_sum.value = f2v(this.sum);
    //this.changeFmSum()
};

billing.changeFmSum
=function(val) {
    val = v2f(val==null ? this.bx_fmsum.value : val);
    this.setSum(null, val);
    this.bx_fmsum.value = f2v(this.fmsum);
    this.bx_sum.value = f2v(this.sum);
};


billing.cur2FM
=function(val, obj) {
	this.clearEvent(obj);
 	if(!this.bx_sum) {
        this.bx_sum = $('paysum');
        this.bx_fmsum = $('paysumfm');
    }
	if(val == 1) {
	    this.changeFmSum();
	} else {
	    this.changeSum();
	}
	
	$('ammount').value = this.sum;
	if(this.isNumeric(obj.value) == false) {
 		this.tipView(obj, 'Пожалуйста, введите числовое значение');
 		return false;
 	} 
 	
	if(this.isNull($('ammount').value, 1) == true) {
		this.tipView(obj, 'Пожалуйста, введите числовое значение');
 		return false;
	} 
	
	this.clearEvents({id1:"paysum", id2:"paysumfm"});
};


function f2v(s) { return (s<=0 ? '' : mny(s)); }
function v2f(s) { var f=parseFloat(s.toString().replace(/,/g,'.').replace(/\s/g,'')); return (isNaN(f)||f<0 ? 0 : mny(f)); }
function mny(s,cl) { return Math[(cl?'ceil':'round')](s*100)/100; }

window.addEvent('domready', 
    function(){
        $$('.tlf_place a').addEvent('click',function(){
            if($(this).getParent().hasClass('tlf_place_ru')) {
                $('phone_code').set('html', '77');
            } else {
                $('phone_code').set('html', '7');
            }
            
            this.getParent('.form-el').getElements('.tlf_place').toggleClass('global_hide');
            //this.getParent('.form-el').getElements('.tlf-input').toggleClass('global_hide');
            return false;
        });
        
        var mask  = '0000000000';
        $$('.tlf-input input').each(function(elm){
            var hint = new Element('label', {'for':'phone', 'id' : 'phone_hint', 'html': ''});
            hint.setStyles({
                'position'     : 'absolute',
                'color'        : '#b2b4b5',
                'padding-top'  : navigator.userAgent.toLowerCase().indexOf("firefox") != -1 ? '1px' : '2px',   
                'padding-left' : '5px'
            });
            var hide = new Element('span', {'id' : 'hint_hide_simbol'});
            var vis  = new Element('span', {'id' : 'hint_vis_simbol', 'html' : mask});
            hide.setStyle('color', 'white');
            hide.setStyle('visibility', 'hidden');
            hint.grab(hide).grab(vis);
            $(elm).getParent().grab(hint, 'top');
        });
        
        if ($('phone')) {
            aHint(document.getElementById('phone'));
        }
    }
);
    
String.prototype.replace_push = function(k, push) {
    if(push == undefined) push = ' ';
    
    if(this[k] != undefined && this[k] != push) {
        var p = this.substr(0, k);
        var n = this.substr(k, this.length);
        return p + ' ' + n;
    } else {
        return this;
    }
}
function aHint(obj) {
    var mask  = '0000000000';
    //obj.value = obj.value.replace_push(3);
    //obj.value = obj.value.replace_push(7);
    //obj.value = obj.value.replace_push(10);
    // if(obj.value.length > 13) obj.value = obj.value.substr(0, 13);
    var len   = obj.value.length;
    
    var hide_simbol = mask.substr(0, len);
    var vis_simbol  = mask.substr(len, mask.length);
    
    $('hint_hide_simbol').set('html', hide_simbol);
    $('hint_vis_simbol').set('html', vis_simbol);
}
