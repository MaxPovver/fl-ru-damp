/////////////////////////////////////////////////////////////////////////////////
//  Locations ///////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
var locations = {

	values: [],
	
	add: function() {
		if ($('flt-cnt')) $('flt-cnt').getParent().setStyle('height', 'auto');
		// get values on page
		var bo = document.getElementById('countries');
		var co = document.getElementById('cities');
		var country = {id: bo.value, name: bo.item(bo.selectedIndex).innerHTML};
		var city = {id: co.value, name: co.item(co.selectedIndex).innerHTML};
		// check all
		if (country.id == 0 && city.id == 0) {
			alert('Необходимо выбрать страну');
			return false;
		}
		var del = [ ];
		for (var i=0; i<this.values.length; i++) {
			if ((this.values[i].country.id == country.id) && (this.values[i].city.id == city.id || this.values[i].city.id == 0)) {
				if (city.id != 0) {
					alert((this.values[i].city.id != 0)? 'Данный город уже добавлен': 'Вы не можете добавить город, если выбрана вся страна');
				} else {
					alert('Данная страна уже добавлена');
				}
				return false;
			}
			if (this.values[i].country.id == country.id && (city.id == 0 && this.values[i].city.id > 0)) {
				del[del.length] = this.values[i].city.id;
			}
		}
		if (del.length) {
			if (confirm(country.name+' уже представлена одним или несколькими городами. Так как вы хотите добавить страну полностью, то эти города будут удалены. Продолжить?')) {
				for (var i=0; i<del.length; i++) this.remove(country.id, del[i]);
			} else {
				return false;
			}
		}
		// save it
		this.values[this.values.length] = { 
			country: country,
			city: city,
			cost: 0,
			count: 0
		}
		spam.values.locations = this.values;
		// out html on page
		this.display(country, city);
		this.reset(country.id, city.id);
		professions.reset();
		// send to xajax
		spam.send();
		return true;
	},

	
	remove: function(countryId, cityId) {
		// remove from this(spam).values
		var tmp = [ ];
		for (var i=0; i<this.values.length; i++) {
			if (this.values[i].country.id != countryId || this.values[i].city.id != cityId) {
				tmp[tmp.length] = this.values[i];
			}
		}
		this.values = tmp;
		this.values.locations = locations.values;
		// cut & out html
		document.getElementById('locations-block').removeChild( document.getElementById('location-'+countryId+'-'+cityId) );
		professions.reset();
		// send to xajax
		spam.send();
		return true;
	},

	reset: function(countryId, cityId) {
		if (countryId) {
			var id = countryId+'-'+cityId;
			document.getElementById('location-count-'+id).innerHTML = '&nbsp;';
			document.getElementById('location-cost-'+id).innerHTML = 'подождите...';
		} else {
			for (var i=0; i<this.values.length; i++) {
				var id = this.values[i].country.id+'-'+this.values[i].city.id;
				document.getElementById('location-count-'+id).innerHTML = '&nbsp;';
				document.getElementById('location-cost-'+id).innerHTML = 'подождите...';
			}
		}
	},
	
	restore: function() {
		for (var i=0; i<this.values.length; i++) {
			this.display(this.values[i].country, this.values[i].city);
			this.reset(this.values[i].country.id, this.values[i].city.id);
		}
	},
	
	display: function(country, city) {
		//var suffix = (this.values.length % 2)? 'spec': 'prt';
		var suffix = (city.id != 0)? 'spec': 'prt';
		var html = '<div id="location-'+country.id+'-'+city.id+'" class="masss-'+suffix+'">\
			<span class="flt-remove"><span class="flt-spec"><span class="flt-s-in"><a href="." onclick="locations.remove('+country.id+', '+city.id+'); return false;"><img src="/images/flt-close.png" alt="" width="15" height="15" />Убрать</a></span></span></span>\
			<div class="flt-'+suffix+'">\
				<div class="flt-s-in">\
				<table>\
					<tr>\
						<th>' + country.name + ((city.id != 0)? (', '+city.name): '') + '</th>\
						<td><strong id="location-count-'+country.id+'-'+city.id+'">&nbsp;</strong></td>\
						<td class="masss-sum" id="location-cost-'+country.id+'-'+city.id+'">&nbsp;</td>\
					</tr>\
				</table>\
			</div>\
			</div>\
		</div>\
		<input type="hidden" name="locations[]" value="'+country.id+':'+city.id+'">';
		document.getElementById('locations-block').innerHTML += html;
	},
	
	recalculation: function() {
		for (var i=0; i<this.values.length; i++) {
			var id = this.values[i].country.id+'-'+this.values[i].city.id;
			document.getElementById('location-count-'+id).innerHTML = format(this.values[i].count);
			document.getElementById('location-cost-'+id).innerHTML = format(this.values[i].cost * exrate) + ' руб';
		}
	}

};


/////////////////////////////////////////////////////////////////////////////////
// professions //////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
var professions = {

	values: [],
	
	add: function() {
		// get values on page
		var bo = document.getElementById('prof_groups');
		var co = document.getElementById('profs');
		var group = {id: bo.value, name: bo.item(bo.selectedIndex).innerHTML};
		var profession = {id: co.value, name: co.item(co.selectedIndex).innerHTML};
		// check all
		if (group.id <= 0 && profession.id == 0) {
			alert('Необходимо выбрать раздел');
			return false;
		}
		var del = [ ];
		for (var i=0; i<this.values.length; i++) {
			if (this.values[i].group.id == group.id && (this.values[i].profession.id == profession.id || this.values[i].profession.id == 0)) {
				//if (profession.id) {
					alert('Раздел уже добавлен или входит в группу добавленных разделов.');
				//} else {
				//	alert('Подраздел уже добавлен для всех разделов.');
				//}
				return false;
			}
			if (this.values[i].group.id == group.id && (profession.id == 0 && this.values[i].profession.id > 0)) {
				del[del.length] = this.values[i].profession.id;
			}
		}
		if (del.length) {
			if (confirm('Раздел уже представлен одним или несколькими подразделами. Так как вы хотите добавить его полностью, то эти подразделы будут удалены. Продолжить?')) {
				for (var i=0; i<del.length; i++) this.remove(group.id, del[i]);
			} else {
				return false;
			}
		}
		// save it
		this.values[this.values.length] = { 
			group: group,
			profession: profession,
			cost: 0,
			count: 0
		}
		spam.values.professions = this.values;
		// out html on page
		this.display(group, profession);
		this.reset(group.id, profession.id);
		locations.reset();
		// send to xajax
		spam.send();
		return true;
	},

	display: function(group, profession) {
		//var suffix = (this.values.length % 2)? 'spec': 'prt';
		var suffix = (profession.id != 0)? 'spec': 'prt';
		var html = '<div id="profession-'+group.id+'-'+profession.id+'" class="masss-'+suffix+'">\
			<span class="flt-remove"><span class="flt-spec"><span class="flt-s-in"><a href="." onclick="professions.remove('+group.id+', '+profession.id+'); return false;"><img src="/images/flt-close.png" alt="" width="15" height="15" />Убрать</a></span></span></span>\
			<div class="flt-'+suffix+'">\
				<div class="flt-s-in">\
				<table>\
					<tr>\
						<th title="'+group.name+((profession.id != 0)? (', '+profession.name): '')+'">' + group.name + ((profession.id != 0)? (', '+((profession.name.length > 17)? (profession.name.substr(0, 17)+'...'): profession.name)): '') + '</th>\
						<td><strong id="profession-count-'+group.id+'-'+profession.id+'">&nbsp;</strong></td>\
						<td class="masss-sum" id="profession-cost-'+group.id+'-'+profession.id+'">&nbsp;</td>\
					</tr>\
				</table>\
			</div>\
			</div>\
			<input type="hidden" name="professions[]" value="'+group.id+':'+profession.id+'">\
		</div>';
		document.getElementById('professions-block').innerHTML += html;
	},

	remove: function(groupId, professionId) {
		// remove from this(spam).values
		var tmp = [ ];
		for (var i=0; i<this.values.length; i++) {
			if (this.values[i].group.id != groupId || this.values[i].profession.id != professionId) {
				tmp[tmp.length] = this.values[i];
			}
		}
		this.values = tmp;
		spam.values.professions = this.values;
		// cut & out html
		document.getElementById('professions-block').removeChild( document.getElementById('profession-'+groupId+'-'+professionId) );
		locations.reset();
		// send to xajax
		spam.send();
		return true;
	},

	reset: function(groupId, professionId) {
		if (groupId) {
			var id = groupId+'-'+professionId;
			document.getElementById('profession-count-'+id).innerHTML = '&nbsp;';
			document.getElementById('profession-cost-'+id).innerHTML = 'подождите...';
		} else {
			for (var i=0; i<this.values.length; i++) {
				var id = this.values[i].group.id+'-'+this.values[i].profession.id;
				document.getElementById('profession-count-'+id).innerHTML = '&nbsp;';
				document.getElementById('profession-cost-'+id).innerHTML = 'подождите...';
			}
		}
	},

	restore: function() {
		for (var i=0; i<this.values.length; i++) {
			this.display(this.values[i].group, this.values[i].profession);
			this.reset(this.values[i].group.id, this.values[i].profession.id);
		}
	},
	
	recalculation: function() {
		for (var i=0; i<this.values.length; i++) {
			var id = this.values[i].group.id+'-'+this.values[i].profession.id;
			document.getElementById('profession-count-'+id).innerHTML = format(this.values[i].count);
			document.getElementById('profession-cost-'+id).innerHTML = format(this.values[i].cost * exrate) + ' руб.';
		}
	}
	
};

/////////////////////////////////////////////////////////////////////////////////
// costs ////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

var costs = {

	values: { },
	count: 0,
	index: 0,
	max: 5,
	
	set: function(name, value) {
		var o = name.match(/(.+)\[([0-9]+)\]/);
		if (typeof this.values[o[2]] == 'undefined') {
			this.values[o[2]] = { cost_from: 0, cost_to: 0, cost_period: 'hour', cost_type: 0 }
		}
		this.values[o[2]][o[1]] = value;
		/*if (this.values[o[2]].cost_from > 0 || this.values[o[2]].cost_to > 0)*/ spam.send();
	},
		
	add: function(value) {
		if ($('flt-cnt')) $('flt-cnt').getParent().setStyle('height', 'auto');
		if (value)	this.values[this.index] = value;
		var div = document.createElement('div');
		div.id = 'cost-' + this.index;
		var html = '<span class="flt-add"><span class="flt-spec"><span class="flt-s-in" id="costs-button-'+this.index+'">';
		if (this.count >= this.max-1) {
			html += '<a href="." onclick="costs.remove('+this.index+'); return false;"><img src="/images/flt-close.png" alt="" width="15" height="15" />Убрать</a>';
		} else {
			html += '<a href="." onclick="costs.add(); return false;"><img src="/images/flt-add.png" alt="" width="15" height="15" />Добавить еще</a>';
		}
		html += '</span></span></span>\
		<span class="flt-prm8">\
			<span class="flt-prm9">\
				<select class="" name="cost_period['+this.index+']" onchange="costs.set(this.name, this.value)">\
				<option value="hour"'+((value && value.cost_period=='hour')? ' selected': '')+'>За час</option>\
				<option value="month"'+((value && value.cost_period=='month')? ' selected': '')+'>За месяц</option></select>\
				<input class="" name="cost_from['+this.index+']" type="text" maxlength="6" size="10" class="" onkeypress="return isNumKeyPressed(event)" onchange="costs.set(this.name, this.value)" value="'+(value && value.cost_from? value.cost_from: '')+'"/>\
			</span>\
			&nbsp;&mdash;&nbsp;\
			<span class="flt-prm10">\
				<input name="cost_to['+this.index+']" type="text" maxlength="6" size="10" class="" onkeypress="return isNumKeyPressed(event)" onchange="costs.set(this.name, this.value)" value="'+(value && value.cost_to? value.cost_to: '')+'"/>&nbsp;\
				<select class="" name="cost_type['+this.index+']" onchange="costs.set(this.name, this.value)">\
				<option value="2"'+((value && value.cost_type==2)? ' selected': '')+'>Руб.</option>\
				<option value="0"'+((value && value.cost_type==0)? ' selected': '')+'>USD</option>\
				<option value="1"'+((value && value.cost_type==1)? ' selected': '')+'>Euro</option>\
			</span>\
		</span>';
		if (this.index > 0) {
			document.getElementById('costs-button-'+(this.index-1)).innerHTML = '<a href="." onclick="costs.remove('+(this.index-1)+'); return false;"><img src="/images/flt-close.png" alt="" width="15" height="15" />Убрать</a>';
		}
		div.innerHTML = html;
        if (document.getElementById('costs-block')) {
            document.getElementById('costs-block').appendChild(div);
        }
		this.index++;
		this.count++;
	},
	
	remove: function(num) {
		document.getElementById('costs-block').removeChild( document.getElementById('cost-' + num) );
		if (this.count >= this.max-1) {
			if (this.index-1 == num) {
				var index = this.index - 2;
				this.index--;
			} else {
				var index = this.index-1;
			}
			document.getElementById('costs-button-'+index).innerHTML = '<a href="javascript: return false" onclick="costs.add(); return false;"><img src="/images/flt-add.png" alt="" width="15" height="15" />Добавить еще</a>';			
		}
		delete this.values[num];
		this.count--;
		spam.send();
	},
	
	restore: function() {
		for (var i=0; i<this.values.length; i++) {
			this.display(this.values[i].group, this.values[i].profession);
			this.reset(this.values[i].group.id, this.values[i].profession.id);
		}
	}

};


/////////////////////////////////////////////////////////////////////////////////
// spam /////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
var spam = {

	values: { },
	calc: { },
	busy: 0,
	
	
	set: function(name, value) {
		if (this.values[name] == value) return;
		this.values[name] = value;
		locations.reset();
		professions.reset();
		this.send();
	},
		

	send: function() {
		if (this.busy > 0) {
			this.busy = 2;
			return;
		}
		
		$('calc_waiting_users').removeClass('user-calc').removeClass('user-calc-ee1d16');
		$('calc_waiting_users').addClass('user-calc');
		$('calc_waiting_cost').removeClass('user-calc').removeClass('user-calc-ee1d16');
		$('calc_waiting_cost').addClass('user-calc');
		$('calc_done').setStyle('display', 'none');
		$('calc_waiting').setStyle('display', '');
		
		$('button_load_span').addClass('b-layout__txt_visibility_hidden').getParent('.b-button').addClass('b-button_disabled');
		$('button_load_img').setStyle('display', '');
		
		$('calc_msg').set('html', 'Пожалуйста, дождитесь окончания расчета количества получателей.');
		$('calc_msg').removeClass('b-buttons__txt_color_80').removeClass('b-buttons__txt_color_ee1d16').removeClass('b-buttons__txt_color_6db335');
		$('calc_msg').addClass('b-buttons__txt_color_80');
		
		var values = { };
		for (var key in this.values) values[key] = this.values[key];
		values.defs = [ ];
		
		values.locations = locations.values.slice();
		var bo = document.getElementById('countries');
		var co = document.getElementById('cities');
		if (bo.value) {
			values.locations[values.locations.length] = {
				country: { id: bo.value, name: bo.item(bo.selectedIndex).innerHTML },
				city: { id: co.value, name: ((co.selectedIndex >= 0)? co.item(co.selectedIndex).innerHTML: '') },
				cost: 0,
				count: 0
			}
		}
		values.defs.countries = bo.value;
		values.defs.cities = co.value;

		values.professions = professions.values.slice();
		var bo = document.getElementById('prof_groups');
		var co = document.getElementById('profs');
		if (bo.value < 0) {
			document.getElementById('users').innerHTML = '0';
			document.getElementById('costFM').innerHTML = '0';
			//document.getElementById('costRUB').innerHTML = '0';
			document.getElementById('pro-users').innerHTML = '0';
			document.getElementById('pro-costFM').innerHTML = '0';
			//document.getElementById('pro-costRUB').innerHTML = '0';
		}
		if (bo.value) {
			values.professions[values.professions.length] = {
				group: { id: bo.value, name: bo.item(bo.selectedIndex).innerHTML },
				profession: { id: co.value, name: ((co.selectedIndex >= 0)? co.item(co.selectedIndex).innerHTML: '') },
				cost: 0,
				count: 0
			}
		}
		values.defs.prof_groups = bo.value;
		values.defs.professions = co.value;
		
		values.costs = [ ];
		for (var key in costs.values) {
			var i = values.costs.length;
			values.costs[i] = { };
			for (var ind in costs.values[key]) values.costs[i][ind] = costs.values[key][ind];
		}
		
		document.getElementById('users').className += ' masss-txt-waiting';
		document.getElementById('costFM').className += ' masss-txt-waiting';
		//document.getElementById('costRUB').className += ' masss-txt-waiting';
		
		this.busy = 1;
  		xajax_Calculate(values);
		
		document.body.style.cursor = 'default';
	},
    
    sendFromSearch: function () {
        xajax_CalculateFromSearch(location.search.slice(1));
    },
	
	recalculation: function() {
	    $('calc_done').setStyle('display', '');
		$('calc_waiting').setStyle('display', 'none');
		
		$('button_load_span').removeClass('b-layout__txt_visibility_hidden').getParent('.b-button').removeClass('b-button_disabled');
		$('button_load_img').setStyle('display', 'none');
		
		$('calc_msg').set('html', 'Расчет закончен, можете отправить заявку на модерацию.');
		$('calc_msg').removeClass('b-buttons__txt_color_80').removeClass('b-buttons__txt_color_ee1d16').removeClass('b-buttons__txt_color_6db335');
		$('calc_msg').addClass('b-buttons__txt_color_6db335');
		
		document.getElementById('users').className = document.getElementById('users').className.replace(/ masss-txt-waiting/, '');
		document.getElementById('costFM').className = document.getElementById('costFM').className.replace(/ masss-txt-waiting/, '');
		//document.getElementById('costRUB').className = document.getElementById('costRUB').className.replace(/ masss-txt-waiting/, '');
		
		if (document.getElementById('prof_groups').value >= 0 || document.getElementById('prof_groups').value == -1) {
			document.getElementById('users').innerHTML = format(this.calc.count);
			document.getElementById('costFM').innerHTML = format(this.calc.cost * exrate);
			//document.getElementById('costRUB').innerHTML = format(this.calc.cost * exrate);
            document.getElementById('pro-users').innerHTML = format(this.calc.pro.count);
            document.getElementById('pro-costFM').innerHTML = format(this.calc.pro.cost * exrate);
            //document.getElementById('pro-costRUB').innerHTML = format(this.calc.pro.cost * exrate);
		}

		
		locations.recalculation();
		professions.recalculation();
		
		if (this.busy > 1) {
			this.busy = 0;
			this.send();
		}
		this.busy = 0;

		if (document.getElementById('prof_groups').value >= 0 && mysum < this.calc.cost) {
			document.getElementById('warning').innerHTML = 'На вашем счету недостаточно средств (не хватает <strong>'+format(this.calc.cost-mysum)+' руб.</strong>). Вы можете <a href="/bill/" class="lnk-dot-blue">пополнить счет</a> или изменить критерии отбора адресатов.';
			document.getElementById('warning').style.display = 'block';
		} else {
			document.getElementById('warning').style.display = 'none';
		}
		
	}
	
	
};


/////////////////////////////////////////////////////////////////////////////////
// functions ////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
function format(number) {
    var s = String(number);
    var data = s.split('.');
    s = data[0];
    var a = [];
    var j = 1;
    for (var i = s.length - 1; i > -1; i--, ++j) {
        a.push(s.charAt(i));
        if ((j % 3) == 0) a.push(' ');
    }
    a.reverse();
    var f = parseInt(data[1]);
    if ( !isNaN(f) ) {
        f = String(f);
        var p = 0;
        var last = 0;
        for (var i = f.length - 1; i > 1; i--) {
            var n = parseInt( f.charAt(i) ) + p;
            if ( n > 4 ) {
                p = 1;
            } else {
                p = 0;
            }
        }
        f = f.substring(0, 2);
        var m = Number(f) + p;
        data[1] = m;
        if (m == 100) {
            f = '';
            return (Number( data[0] ) + 1);
        }
    }    
    data[0] = a.join(""); 
    a = data.join(",");
    return a;
}

function isNumKeyPressed(e) {
	var key = (typeof e.charCode == 'undefined' ? e.keyCode : e.charCode); 
	if (e.ctrlKey || e.altKey || key < 32) return true; 
	key = String.fromCharCode(key); 
	return /[\d]/.test(key);
}

function GetCities(country, city) {
	document.getElementById('cities').options.length = 0;
	if (country > 0) {
		document.getElementById('btnAddLocation').onclick = function() { return false; }
		document.getElementById('cities').options[0] = new Option('Загрузка...', 0);
		document.getElementById('cities').disabled = true;
		if (spam.busy == 0) spam.busy = 1;
		xajax_GetCities(country, city);
	} else {
		document.getElementById('cities').options[0] = new Option('Все города', 0);
	}
}

function GetProfessions(prof_group, profession) {
	document.getElementById('profs').options.length = 0;
	document.getElementById('profs').options[0] = new Option('Все разделы', 0);
	for (group in profs) {
		if (group == prof_group) {
			for (var i=0; i<profs[group].length; i++) {
				document.getElementById('profs').options[i+1] = new Option(profs[group][i].name, profs[group][i].id);
			}
		}
	}
	if (prof_group == -1) {
		document.getElementById('profs').disabled = true;
	} else {
		document.getElementById('profs').disabled = false;
	}
	if (profession) {
		for (var i=0; i<document.getElementById('profs').options.length; i++) {
			if (document.getElementById('profs').options[i].value == profession) {
				document.getElementById('profs').selectedIndex = i;
				break;
			}
		}
	}
}

function SendIt() {
	if (document.getElementById('msg').value.replace(/\s/g, '') == '' || document.getElementById('msg').className == 'msgerror') {
		document.getElementById('msg').className = 'msgerror';
		document.getElementById('msg').value = 'Это поле обязательно к заполнению.';
		alert('Вы не написали сообщение.');
		return false;
	}
	if (spam.busy) {
		//alert('Пожалуйста, подождите. Идет расчет количества пользователей');
		$('calc_waiting_users').addClass('user-calc-ee1d16');
		$('calc_waiting_cost').addClass('user-calc-ee1d16');
		$('calc_msg').set('html', 'Пожалуйста, дождитесь окончания расчета количества получателей.');
		$('calc_msg').removeClass('b-buttons__txt_color_80').removeClass('b-buttons__txt_color_ee1d16').removeClass('b-buttons__txt_color_6db335');
		$('calc_msg').addClass('b-buttons__txt_color_ee1d16');
		return false;
	}
	if (document.getElementById('prof_groups').value < 0) {
		alert('Вы не выбрали раздел каталога.');
		return false;
	}
	/*if (mysum < spam.calc.cost) {
		alert('У вас недостаточно средств на счету.');
		return false;
	}*/
	if (spam.calc.count == 0) {
		alert('Нет пользователей для рассылки.');
		return false;
	}
	document.getElementById('frm').submit();
	
	return false;
}


/////////////////////////////////////////////////////////////////////////////////
// MultiFile ////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

function MultiFile(setting, uploaded) {

	this.setting = setting;
	
	this.interval = null;
	this.isIE = (navigator.userAgent.toLowerCase().indexOf('msie') != -1);
	this.files = [ ];
	
	var self = this;	


	this.form = document.createElement('form');
	if (this.isIE) {
        var isIE7= false;
        try {
            isIE7 = (navigator.appVersion.indexOf("MSIE 7.")==-1) ? false : true;
        } catch(e) { }
        if(isIE7) {
            // IE7
            this.form.setAttribute('encoding', 'multipart/form-data'); 
        } else {
            this.form.setAttribute('enctype', 'multipart/form-data');
        }
	} else {
    	this.form.setAttribute('enctype', 'multipart/form-data');
    }

	this.form.setAttribute('action', this.setting.backend);
	this.form.setAttribute('method', 'POST');
	this.setting.input.parentNode.replaceChild(this.form, this.setting.input);
	this.form.appendChild(this.setting.input);

	this.onchange = function() {
		if (!self.form.checkValidate || self.form.checkValidate()) {
			if (self.setting.input.value != '') {
				var p = self.setting.input.value.lastIndexOf('.');
				if (p < 1) return false;
				var ext = self.setting.input.value.substr(p + 1).toLowerCase();
				if (ext == '') return false;
				var ok = allowedExt( self.setting.input.value );
				
				if (!ok) {
					self.reset();
					return false;
				}
				if (self.files.length >= self.setting.maxfiles) {
					self.error('Максимальное количество файлов для загрузки '+self.setting.maxfiles);
					self.reset();
					return false;
				}
				self.setting.load.style.display = '';
				self.onUpload();
			}
		}
	}
	
	this.setting.input.onchange = this.onchange;

	
	// ===========================================

	this.position = function(id) {
		for (var i=0,m=this.files.length; i<m; i++) {
			if (this.files[i].id == id) return i;
		}
		return -1;
	}
	
	
	this.save = function() {
		var value = '';
		for (var i=0,m=this.files.length; i<m; i++) {
			value += this.files[i].id+',';
		}
		if (value) value = value.substr(0, value.length-1);
		var d = new Date();
		d.setSeconds(d.getSeconds() + this.setting.sessTTL);
		document.cookie = 'mass-files' + "=" + value + "; expires=" + d.toGMTString() + ";";
	}
	
	
	this.up = function(id) {
		var p2 = this.position(id);
		var p1 = p2-1;
        if (this.files[p1]) {
            this.files[p1].obj.parentNode.insertBefore(this.files[p2].obj, this.files[p1].obj);
            var tmp = this.files[p1];
            this.files[p1] = this.files[p2];
            this.files[p2] = tmp;
            this.udb(p1);
            this.udb(p2);
            this.save();
        }
	}
	
	
	this.down = function(id) {
		var p1 = this.position(id);
		var p2 = p1+1;
        if (this.files[p2]) {
            this.files[p1].obj.parentNode.insertBefore(this.files[p2].obj, this.files[p1].obj);
            var tmp = this.files[p1];
            this.files[p1] = this.files[p2];
            this.files[p2] = tmp;
            this.udb(p1);
            this.udb(p2);
            this.save();
        }
	}
	

	this.unlink = function(id) {
		var pos = this.position(id);
		this.files[pos].obj.parentNode.removeChild(this.files[pos].obj);
		var tmp = [ ];
		for (var i=0,j=0,m=this.files.length; i<m; i++) {
			if (this.files[i].id != id) tmp[j++] = this.files[i];
		}
		this.files = tmp;
		if (this.files.length) {
			this.udb(0);
			this.udb(this.files.length-1);
		}
		$('flt-masss-files').getParent().setStyle('height', 'auto');
		this.save();
		xajax_DelFile(id);
	}
	
	
	this.udb = function(pos) {
		var self = this;
		if (pos == 0) {
			this.files[pos].imgup.src = '/images/arrow2-top-a.png';
			this.files[pos].imgup.onclick = function() { return false; }
		} else {
			this.files[pos].imgup.src = '/images/arrow2-top.png';
			this.files[pos].imgup.onclick = function() { self.up(self.files[pos].id); }
		}
		if (pos == this.files.length-1) {
			this.files[pos].imgdown.src = '/images/arrow2-bottom-a.png';
			this.files[pos].imgdown.onclick = function() { return false; }
		} else {
			this.files[pos].imgdown.src = '/images/arrow2-bottom.png';
			this.files[pos].imgdown.onclick = function() { self.down(self.files[pos].id); }
		}
	}

	
	this.element = function(id, fullpath, name, filetype) {
		if (!name || name == '') {
			name = fullpath.replace(/^.*?([^\\/]+)$/, "$1");
		}
		if ((p = name.lastIndexOf('.')) > 0) {
			var b = name.substr(0, p);

		} else {
			var b = '';
			var e = '';
		}
		
        if(filetype=='docx') filetype = 'doc';
        if(filetype=='xlsx') filetype = 'xls';
        if(filetype=='jpg') filetype = 'jpeg';
        if(filetype=='mkv') filetype = 'hdv';
        if(!(filetype=='swf' || filetype=='mp3' || filetype=='rar' || filetype=='doc' || filetype=='pdf' || filetype=='ppt' || 
             filetype=='rtf' || filetype=='txt' || filetype=='xls' || filetype=='zip' || filetype=='jpeg' || filetype=='png' || 
             filetype=='ai' || filetype=='bmp' || filetype=='psd' || filetype=='gif' || filetype=='flv' || filetype=='wav' || 
             filetype=='ogg' || filetype=='wmv' || filetype=='tiff' || filetype=='avi' || filetype=='hdv' || filetype=='ihd' || filetype=='fla')
          ) {
            filetype = 'unknown';
        }
        ico = filetype;

		var pos = this.files.length;
		var self = this;

		var img1 = document.createElement('img');
		img1.src = (pos > 0)? '/images/arrow2-top.png': '/images/arrow2-top-a.png';
		img1.alt = img1.title = 'вверх';
		img1.onclick = function() {
			self.up(id);
		}

		var img2 = document.createElement('img');
		img2.src = '/images/arrow2-bottom-a.png';
		if (pos > 0) {
			this.files[pos-1].imgdown.src = '/images/arrow2-bottom.png';
		}
		img2.alt = img2.title = 'вниз';
		img2.onclick = function() {
			self.down(id);
		}

		var span = document.createElement('span');
		span.className = 'ffa-sort';
		span.appendChild(img1);
		span.appendChild(img2);

		var img3 = document.createElement('img');
		img3.src = '/images/btn-remove2.png';
		img3.alt = img3.title = 'Удалить';
		var a1 = document.createElement('a');
		a1.title = 'Удалить';
		a1.onclick = function() {
			self.unlink(id);
		}
		a1.appendChild(img3);
		
		var a2 = document.createElement('a');
		a2.href = fullpath;
		a2.target = '_blank';
		a2.className = 'mime '+ico;
		var txt = document.createTextNode(name);
		a2.appendChild(txt);
		
		var hidden = document.createElement('input');
		hidden.type = 'hidden';
		hidden.name = 'upfiles[]';
		hidden.value = id;

		var li = document.createElement('li');
		li.appendChild(span);
		li.appendChild(a1);
		li.appendChild(a2);
		li.appendChild(hidden);
		
		return {obj: li, imgup: img1, imgdown: img2};
	}
	

	this.reset = function() {
		this.setting.input.disabled = false;
		this.form.reset();
		this.setting.load.style.display = 'none';
	}
	
	
	this.onUpload = function() {
		var iframeName = 'upfile-' + Math.round(Math.random() * 1000000);

		if (this.isIE) {
            try {
    			var iframe = document.createElement('<iframe name='+iframeName+'></iframe>');
            } catch(e) {
    			var iframe = document.createElement('iframe');
    			iframe.name = iframeName;
    			iframe.setAttribute('name', iframeName);
            }
		} else {
			var iframe = document.createElement('iframe');
			iframe.name = iframeName;
			iframe.setAttribute('name', iframeName);
		}

		iframe.setAttribute('width', 1);
		iframe.setAttribute('height', 1);
		iframe.setAttribute('id', iframeName);
		iframe.style.position = 'absolute';
		iframe.style.visibility = 'hidden';
		document.getElementsByTagName("body")[0].appendChild(iframe);
		try { iframe.contentWindow.document.getElementsByTagName("body")[0].innerHTML = '&nbsp'; } catch(e) { } //opera 9.27
		this.interval = setInterval(function(self, iframe) {
			return function() {
				var html = '';
				try {
					html = iframe.contentWindow.document.getElementsByTagName("body")[0].innerHTML;
				} catch(e) {
					html = '';
				}
				if (html.indexOf('-- IBox --') != -1) {
					clearInterval(self.interval);
					self.onUploaded(html);
					document.getElementsByTagName("body")[0].removeChild(iframe);
				}
			}
		}(this, iframe), 500);
		this.form.setAttribute('target', iframeName);
		this.form.submit();
		this.setting.input.disabled = true;
	}

	
	this.onUploaded = function(answer) {
		var r = [];
		var error = false;

		if (r = answer.match(/<status>([^<]+)<\/status>/i, "$1")) var status = r[1]; else var status = 'error';
		if (r = answer.match(/<time>([^<]+)<\/time>/i, "$1")) var time = r[1]; else var time = 0;
		switch (status.toLowerCase()) {
			case 'success':
				if (r = answer.match(/<filename>([^<]+)<\/filename>/i, "$1")) var filename = r[1]; else var filename = '';
				if (r = answer.match(/<filetype>([^<]+)<\/filetype>/i, "$1")) var filetype = r[1]; else var filetype = '';
				if (r = answer.match(/<displayname>([^<]+)<\/displayname>/i, "$1")) var displayname = r[1]; else var displayname = '';
				if (r = answer.match(/<fileid>([^<]+)<\/fileid>/i, "$1")) var fileID = r[1]; else var fileID = 0;
				break;
			case 'error':
				if (r = answer.match(/<message>([^<]+)<\/message>/i, "$1")) var message = r[1]; else var message = '';
				this.error(message? message: 'Ошибка при загрузке файла.');
				error = true;
				break;
			default:
				this.error('Ошибка при загрузке файла. Попробуйте еще раз.');
				error = true;
				break;
		}
		if (!error) {
			$('flt-masss-files').getParent().setStyle('height', 'auto');
			var el = this.element(fileID, filename, displayname, filetype);
			this.files[this.files.length] = { 
				id: fileID, 
				fullpath: filename, 
				name: displayname, 
				obj: el.obj, 
				imgup: el.imgup, 
				imgdown: el.imgdown
			}
			this.setting.files.appendChild(el.obj);
			this.save();
		}
		this.reset();
	}

	
	this.error = function(text) {
		alert(text);
	}
	

	// =============================================
	
	if (uploaded) {
		$('flt-masss-files').getParent().setStyle('height', 'auto');
		for (var i=0,m=uploaded.length; i<m; i++) {
			var el = this.element(uploaded[i].id, uploaded[i].filename, uploaded[i].displayname, uploaded[i].filetype);
			this.files[this.files.length] = { 
				id: uploaded[i].id, 
				fullpath: uploaded[i].filename, 
				name: uploaded[i].displayname, 
				obj: el.obj, 
				imgup: el.imgup, 
				imgdown: el.imgdown
			}
			this.setting.files.appendChild(el.obj);
		}
		this.save();
	}

	
};
