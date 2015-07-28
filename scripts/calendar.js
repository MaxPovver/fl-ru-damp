// Tigra Calendar v4.0.2
// http://www.softcomplex.com/products/tigra_calendar/


var A_TCALDEF = {
	'months' : ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
	'weekdays' : ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
	'yearscroll': true,  // show year scroller
	'weekstart': 1,      // first day of week: 0-Su or 1-Mo
	'centyear'  : 70,    // 2 digit years less than 'centyear' are in 20xx, othewise in 19xx.
	'imgpath' : '/images/calendar/'   // directory with calendar images
}


function f_tcalParseDate (s_date) {

	var re_date = /^\s*(\d{1,2})\-(\d{1,2})\-(\d{2,4})\s*$/;
    var today = new Date;
	if (!re_date.exec(s_date)) {
        s_date = today.getDate() + '-' + today.getMonth() + '-' + today.getYear();
        re_date.exec(s_date);
		//return alert ("Некорректная дата: '" + s_date + "'.\nПравильно так: dd-mm-yyyy.")
    }
	var n_day = Number(RegExp.$1),
		n_month = Number(RegExp.$2),
		n_year = Number(RegExp.$3);

	if (n_year < 100)
		n_year += (n_year < this.a_tpl.centyear ? 2000 : 1900);
	if (n_month < 1 || n_month > 12) {
        n_month = today.getMonth();
		//return alert ("Invalid month value: '" + n_month + "'.\nAllowed range is 01-12.");
    }
	var d_numdays = new Date(n_year, n_month, 0);
	if (n_day > d_numdays.getDate()) {
        n_day = d_numdays.getDate();
		//return alert("Invalid day of month value: '" + n_day + "'.\nAllowed range for selected month is 01 - " + d_numdays.getDate() + ".");
    }

	return new Date (n_year, n_month - 1, n_day);
}



function f_tcalGenerDate (d_date) {
	if(this.onSuccess) this.onSuccess(); 
    return (
		(d_date.getDate() < 10 ? '0' : '') + d_date.getDate() + "-"
		+ (d_date.getMonth() < 9 ? '0' : '') + (d_date.getMonth() + 1) + "-"
		+ d_date.getFullYear()
	);
}


function tcal (a_cfg, a_tpl) {

	if (!a_tpl)
		a_tpl = A_TCALDEF;

	if (!window.A_TCALS)
		window.A_TCALS = [];
	if (!window.A_TCALSIDX)
		window.A_TCALSIDX = [];
	
	this.a_pos = { left: 0, top: 0 };
	if (a_cfg.leftOffset) this.a_pos.left = a_cfg.leftOffset;
	if (a_cfg.topOffset) this.a_pos.top = a_cfg.topOffset;
	if (a_cfg.clickEvent) this.clickEvent = a_cfg.clickEvent;
	if (a_cfg.onSuccess) this.onSuccess = a_cfg.onSuccess;
    
	this.s_id = a_cfg.id ? a_cfg.id : A_TCALS.length;
	window.A_TCALS[this.s_id] = this;
	window.A_TCALSIDX[window.A_TCALSIDX.length] = this;
	
	this.f_show = f_tcalShow;
	this.f_hide = f_tcalHide;
	this.f_toggle = f_tcalToggle;
	this.f_update = f_tcalUpdate;
	this.f_relDate = f_tcalRelDate;
	this.f_parseDate = f_tcalParseDate;
	this.f_generDate = f_tcalGenerDate;
	
	this.s_iconId = a_cfg.iconId;
	this.e_icon = f_getElement(this.s_iconId);
	this.e_icon.onclick = function(s_id) { 
		return function(event) { 
			A_TCALS[s_id].f_toggle();
			if (window.event)
				window.event.cancelBubble = true
			else if (event.stopPropagation)
				event.stopPropagation();
			return false;
		} 
	}(this.s_id);
		
	this.a_cfg = a_cfg;
	this.a_tpl = a_tpl;
}


function f_tcalShow (d_date) {

	if (!this.a_cfg.controlname)
		throw("TC: control name is not specified");
	if (this.a_cfg.formname) {
		var e_form = document.forms[this.a_cfg.formname];
		if (!e_form)
			throw("TC: form '" + this.a_cfg.formname + "' can not be found");
		this.e_input = e_form.elements[this.a_cfg.controlname];
	}
	else
		this.e_input = f_getElement(this.a_cfg.controlname);
    
	if (!this.e_input || !this.e_input.tagName || this.e_input.tagName != 'INPUT')
		throw("TC: element '" + this.a_cfg.controlname + "' does not exist in "
			+ (this.a_cfg.formname ? "form '" + this.a_cfg.controlname + "'" : 'this document'));
    this.e_input.onblur = function () {
        return f_validateInterval(this, f_tcalParseDate (this.value).getTime() );
    }
	this.e_div = f_getElement('tcal');
	if (!this.e_div) {
		this.e_div = document.createElement("DIV");
		this.e_div.id = 'tcal';
		document.body.appendChild(this.e_div);
	}
	this.e_shade = f_getElement('tcalShade');
	if (!this.e_shade) {
		this.e_shade = document.createElement("DIV");
		this.e_shade.id = 'tcalShade';
		document.body.appendChild(this.e_shade);
	}
	this.e_iframe =  f_getElement('tcalIF')
	if (b_ieFix && !this.e_iframe) {
		this.e_iframe = document.createElement("IFRAME");
		this.e_iframe.style.filter = 'alpha(opacity=0)';
		this.e_iframe.id = 'tcalIF';
		this.e_iframe.src = this.a_tpl.imgpath + 'pixel.gif';
		document.body.appendChild(this.e_iframe);
	}
	
	f_tcalHideAll();

	if (!this.f_update())
		return;

	this.e_div.style.visibility = 'visible';
	this.e_shade.style.visibility = 'visible';
	if (this.e_iframe)
		this.e_iframe.style.visibility = 'visible';

	this.b_visible = true;
	
	if (document.body.addEventListener)
		document.body.addEventListener('click', f_tcalHideOutAll, false);
	else if (document.body.attachEvent)
		document.body.attachEvent('onclick', f_tcalHideOutAll);

	if(this.a_cfg.position=='fixed') {
		$('tcal').setStyle('position', 'fixed');
		$('tcalShade').setStyle('position', 'fixed');
	}
}


function f_tcalHide (n_date) {
   f_validateInterval( this.e_input instanceof HTMLInputElement ? this.e_input : false, n_date);
	if (n_date) {
		this.e_input.value = this.f_generDate(new Date(n_date));
  if(typeof this.e_input.fireEvent == 'function')
      this.e_input.fireEvent('change');
 }

	if (!this.b_visible)
		return;

	if (this.e_iframe)
		this.e_iframe.style.visibility = 'hidden';
	if (this.e_shade)
		this.e_shade.style.visibility = 'hidden';
	this.e_div.style.visibility = 'hidden';
	
	this.b_visible = false;
	
	if (document.body.removeEventListener)
		document.body.removeEventListener('click', f_tcalHideOutAll, false);
	else if (document.body.removeEvent)
		document.body.detachEvent('onclick', f_tcalHideOutAll);
	
    if ( this.a_cfg.position == 'fixed' ) {
        $('tcal').setStyle('position', 'absolute');
        $('tcalShade').setStyle('position', 'absolute');
    }
}


function f_tcalToggle () {
	return this.b_visible ? this.f_hide() : this.f_show();
}


function f_tcalUpdate (d_date) {
    if(this.clickEvent) this.clickEvent();    
	var d_today = this.a_cfg.today ? this.f_parseDate(this.a_cfg.today) : f_tcalResetTime(new Date());
	var d_selected = this.e_input.value == ''
		? (this.a_cfg.selected ? this.f_parseDate(this.a_cfg.selected) : d_today)
		: this.f_parseDate(this.e_input.value);

	if (!d_date)
		d_date = d_selected;
	else if (typeof(d_date) == 'number')
		d_date = f_tcalResetTime(new Date(d_date));
	else if (typeof(d_date) == 'string')
		this.f_parseDate(d_date);
		
	if (!d_date) return false;

	var d_firstday = new Date(d_date);
	d_firstday.setDate(1);
	d_firstday.setDate(1 - (7 + d_firstday.getDay() - this.a_tpl.weekstart) % 7);
	
	var a_class, s_html = '<table class="ctrl"><tbody><tr>'
		+ (this.a_tpl.yearscroll ? '<td' + this.f_relDate(d_date, -1, 'y') + ' title="Предыдущий год">&nbsp;&lt;&lt;</td>' : '')
		+ '<td' + this.f_relDate(d_date, -1) + ' title="Предыдущий месяц">&nbsp;&nbsp;&lt;</td><th>'
		+ this.a_tpl.months[d_date.getMonth()] + ' ' + d_date.getFullYear()
			+ '</th><td' + this.f_relDate(d_date, 1) + ' title="Следующий месяц">&gt;&nbsp;&nbsp;</td>'
		+ (this.a_tpl.yearscroll ? '<td' + this.f_relDate(d_date, 1, 'y') + ' title="Следующий год">&gt;&gt;&nbsp;</td></td>' : '')
		+ '</tr></tbody></table><table><tbody><tr class="wd">';

	for (var i = 0; i < 7; i++)
		s_html += '<th>' + this.a_tpl.weekdays[(this.a_tpl.weekstart + i) % 7] + '</th>';
	s_html += '</tr>' ;

	var n_date, n_month, d_current = new Date(d_firstday);
	while (d_current.getMonth() == d_date.getMonth() ||
		d_current.getMonth() == d_firstday.getMonth()) {
	
		s_html +='<tr>';
		for (var n_wday = 0; n_wday < 7; n_wday++) {

			a_class = [];
			n_date = d_current.getDate();
			n_month = d_current.getMonth();

			if (d_current.getMonth() != d_date.getMonth())
				a_class[a_class.length] = 'othermonth';
			if (d_current.getDay() == 0 || d_current.getDay() == 6)
				a_class[a_class.length] = 'weekend';
			if (d_current.valueOf() == d_today.valueOf())
				a_class[a_class.length] = 'today';
			if (d_current.valueOf() == d_selected.valueOf())
				a_class[a_class.length] = 'selected';

			s_html += '<td onclick="A_TCALS[\'' + this.s_id + '\'].f_hide(' + d_current.valueOf() + ')"' + (a_class.length ? ' class="' + a_class.join(' ') + '">' : '>') + n_date + '</td>'

			d_current.setDate(++n_date);
			while (d_current.getDate() != n_date && d_current.getMonth() == n_month) {
				d_current.setHours(d_current.getHours + 1);
				d_current = f_tcalResetTime(d_current);
			}
		}
		s_html +='</tr>';
	}
	s_html +='</tbody></table>';
	
	this.e_div.innerHTML = s_html;

	var n_width  = this.e_div.offsetWidth;
	var n_height = this.e_div.offsetHeight;
	var n_top  = f_getPosition (this.e_icon, 'Top') + this.e_icon.offsetHeight + this.a_pos.top;
	var n_left = f_getPosition (this.e_icon, 'Left') - n_width + this.e_icon.offsetWidth + this.a_pos.left;
	if (n_left < 0) n_left = 0;
	
	this.e_div.style.left = n_left + 'px';
	this.e_div.style.top  = n_top + 'px';

	this.e_shade.style.width = (n_width + 8) + 'px';
	this.e_shade.style.left = (n_left - 1) + 'px';
	this.e_shade.style.top = (n_top - 1) + 'px';
	this.e_shade.innerHTML = b_ieFix
		? '<table><tbody><tr><td rowspan="2" colspan="2" width="6"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td><td width="7" height="7" style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + this.a_tpl.imgpath + 'shade_tr.png\', sizingMethod=\'scale\');"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td></tr><tr><td height="' + (n_height - 7) + '" style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + this.a_tpl.imgpath + 'shade_mr.png\', sizingMethod=\'scale\');"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td></tr><tr><td width="7" style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + this.a_tpl.imgpath + 'shade_bl.png\', sizingMethod=\'scale\');"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td><td style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + this.a_tpl.imgpath + 'shade_bm.png\', sizingMethod=\'scale\');" height="7" align="left"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td><td style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + this.a_tpl.imgpath + 'shade_br.png\', sizingMethod=\'scale\');"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td></tr><tbody></table>'
		: '<table><tbody><tr><td rowspan="2" width="6"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td><td  style="background:#fff;" rowspan="2"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td><td width="7" height="7"><img style="vertical-align:bottom;" src="' + this.a_tpl.imgpath + 'shade_tr.png"></td></tr><tr><td background="' + this.a_tpl.imgpath + 'shade_mr.png" height="' + (n_height - 7) + '"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td></tr><tr><td><img style="vertical-align:top;" src="' + this.a_tpl.imgpath + 'shade_bl.png"></td><td style="background:url(' + this.a_tpl.imgpath + 'shade_bm.png) repeat-x 0 0" height="7" align="left"><img src="' + this.a_tpl.imgpath + 'pixel.gif"></td><td><img style="vertical-align:top;" src="' + this.a_tpl.imgpath + 'shade_br.png"></td></tr><tbody></table>';
	
	if (this.e_iframe) {
		this.e_iframe.style.left = n_left + 'px';
		this.e_iframe.style.top  = n_top + 'px';
		this.e_iframe.style.width = (n_width + 6) + 'px';
		this.e_iframe.style.height = (n_height + 6) +'px';
	}
	return true;
}


function f_getPosition (e_elemRef, s_coord) {
	var n_pos = 0, n_offset,
		e_elem = e_elemRef;

	while (e_elem) {
		n_offset = e_elem["offset" + s_coord];
		n_pos += n_offset;
		e_elem = e_elem.offsetParent;
	}
	if (b_ieMac)
		n_pos += parseInt(document.body[s_coord.toLowerCase() + 'Margin']);
	else if (b_safari)
		n_pos -= n_offset;
	
	e_elem = e_elemRef;
	while (e_elem != document.body) {
		n_offset = e_elem["scroll" + s_coord];
		if (n_offset && e_elem.style.overflow == 'scroll')
			n_pos -= n_offset;
		e_elem = e_elem.parentNode;
	}
	return n_pos;
}


function f_getMouse (event) {
	var event = event || window.event;
	var x = y = 0;
	if (document.attachEvent != null) {
		x = window.event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
		y = window.event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	} else if (!document.attachEvent && document.addEventListener) {
		x = event.clientX + window.scrollX;
		y = event.clientY + window.scrollY;
	}
	return {x:x, y:y};
}


function f_tcalRelDate (d_date, d_diff, s_units) {
	var s_units = (s_units == 'y' ? 'FullYear' : 'Month');
	var d_result = new Date(d_date);
	d_result['set' + s_units](d_date['get' + s_units]() + d_diff);
	if (d_result.getDate() != d_date.getDate())
		d_result.setDate(0);
	return ' onclick="A_TCALS[\'' + this.s_id + '\'].f_update(' + d_result.valueOf() + ')"';
}


function f_tcalHideAll () {
	for (var i = 0; i < window.A_TCALSIDX.length; i++)
		window.A_TCALSIDX[i].f_hide();
}	


function f_tcalHideOutAll (event) {
	var m = f_getMouse(event);
	for (var i = 0; i < window.A_TCALSIDX.length; i++) {
		if (window.A_TCALSIDX[i].e_div) {
			var width  = window.A_TCALSIDX[i].e_div.offsetWidth;
			var height = window.A_TCALSIDX[i].e_div.offsetHeight;
			var top  = f_getPosition (window.A_TCALSIDX[i].e_div, 'Top');
			var left = f_getPosition (window.A_TCALSIDX[i].e_div, 'Left');
			if (m.x < left || m.x > (left + width) || m.y < top || m.y > (top + height))
				window.A_TCALSIDX[i].f_hide();
		}
	}
	if (window.event)
		window.event.cancelBubble = true;
	else if (event.stopPropagation) {
		event.stopPropagation();
	}
}	


function f_tcalResetTime (d_date) {
	d_date.setHours(0);
	d_date.setMinutes(0);
	d_date.setSeconds(0);
	d_date.setMilliseconds(0);
	return d_date;
}


f_getElement = document.all ?
	function (s_id) { return document.all[s_id] } :
	function (s_id) { return document.getElementById(s_id) };


var s_userAgent = navigator.userAgent.toLowerCase(),
	re_webkit = /WebKit\/(\d+)/i;
var b_mac = s_userAgent.indexOf('mac') != -1,
	b_ie5 = s_userAgent.indexOf('msie 5') != -1,
	b_ie6 = s_userAgent.indexOf('msie 6') != -1 && s_userAgent.indexOf('opera') == -1;
var b_ieFix = b_ie5 || b_ie6,
	b_ieMac  = b_mac && b_ie5,
	b_safari = b_mac && re_webkit.exec(s_userAgent) && Number(RegExp.$1) < 500;
/**
* Валидация значения календаря.
* @param  HTMLInputElement input
* @param  selected_date 
*/
function f_validateInterval(input, selected_date) {
    if (input) {
        switch (input.id) {
            case "ds":
                if ( String(selected_date) == "undefined" ) {
                    selected_date = f_tcalParseDate ($('ds').value).getTime();
                }
                if ( new Date().getTime() > selected_date ) {
                    input.getParent("div.sel-set-date").setStyle("border", "solid 1px red");
                    $('editDateButton').disabled = true;
                    return false;
                } else {
                    input.getParent("div.sel-set-date").setStyle("border", "solid 1px #B2B2B2");
                    $('editDateButton').disabled = false;
                    return true;
                }
                break;
        }
    }
}