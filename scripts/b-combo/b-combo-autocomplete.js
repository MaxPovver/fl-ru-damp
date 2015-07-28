/**
* Зависит от:
* b-combo-dynamic-input.js
* 
*/
//Определение класса  CAutocompleteInput
/**
*  Строит html код поля ввода с подсказками
* @param HtmlDivElement htmlDiv
* @param Array           cssSelectors
*/
function CAutocompleteInput(htmlDiv, cssSelectors) {
	this.init(htmlDiv, cssSelectors);
    this.companyCss = "b-combo__user b-combo__txt b-combo__txt_fontsize_13";
	this.outerDiv.addClass("b-combo__input_overflow_hidden");
	this.firstSectionText = 'Ваши последние контакты';
	this.secondSectionText = 'Остальные пользователи';
	this.countMeasure = 'человек';
	this.countMeasure1 = '';
	this.countMeasure2 = '';
	var s = this.b_input.getAttribute("first_section_text");
	if (s && s.length > 0) this.firstSectionText = s;
	
	var s = this.b_input.getAttribute("second_section_text");
	if (s && s.length > 0) this.secondSectionText = s;
	
	var s = this.b_input.getAttribute("count_measure");
	if (s && s.length > 0) this.countMeasure = s;
	
	var s = this.b_input.getAttribute("count_measure_1");
	if (s && s.length > 0) this.countMeasure1 = s;
	
	var s = this.b_input.getAttribute("count_measure_2");
	if (s && s.length > 0) this.countMeasure2 = s;
	
	this.b_input.autocomplete = "off";
	this.HOVER_CSS = 'b-combo__user_hover'; // . и .b-combo__item-inner_hover
	this.id_input = new Element("input", {"type":"hidden", "id": this.b_input.id + "_db_id", "name": this.b_input.name?this.b_input.name + "_db_id":this.b_input.id + "_db_id"});
	this.id_input.inject(this.outerDiv, "top");	
	this.section_input = new Element("input", {"type":"hidden", "id": this.b_input.id + "_section", "name": this.b_input.name?this.b_input.name + "_section":this.b_input.id + "_section"});
	this.section_input.inject(this.outerDiv, "top");	
	var ls  = this.outerDiv.getElements(".b-combo__arrow-user");
	var ls2 = this.outerDiv.getElements(".b-combo__arrow");
    if (!ls.length && !ls2.length) {
        var sp = new Element("span", {"class":"b-combo__arrow-user"});
	    sp.inject(this.outerDiv, "bottom");
	}
    var togglerW = 0;
    var toggler = this.outerDiv.getElement("span.b-combo__arrow-user");
    if (toggler) {
        togglerW = parseInt(toggler.getStyle("width"));
        if (!togglerW) {
            togglerW = 0;
        }
    }
    this.togglerW  = togglerW;
	this._onchangeHandler = function(){;}
	if (this.b_input.onchange instanceof Function) {		
		this._onchangeHandler = this.id_input.onchange = this.b_input.onchange;
		this.b_input.onchange = this.onKeyUp;
	}
	var s = this.selectors;
	this.allowCreateValue = 0;
	if (s.indexOf(" allow_create_value") != -1) this.allowCreateValue = 1;
	this.b_input.set("title", "Начните вводить имя и выберите значение из списка ниже");
	this.buildShadow(htmlDiv.getParent('.b-combo'));
	this.shadow.addClass('b-shadow_zindex_2');
	this.columns = new Array();
	this.columns.push(this.extendElementPlace);
	this.cache  = {list:new Object(), counters:new Object(), flatList:new Array()};        //кеш списков для подстрок (используется при загрузки нескольких записей для вводимой подстроки)
	this.cacheRecords = {list:{}};        //кеш записей, загруженых функцией loadRecord
	this.extendElementPlace.addClass('b-combo__users');
	this.extendElementPlace.removeClass('b-layout');
	
	this.scope = 0;                                 //определяет, где искать пользовательей, 0 - в СБР/контактах и общем списке пользователей, 1 - в СБР/контактах, 2 - в общем списке пользователей
	if (s.indexOf(" search_in_sbr") != -1) this.scope = 1;
	if (s.indexOf(" search_in_userlist") != -1) this.scope = 2;
	this.userType = 0;                              //определяет, каких пользовательей искать, 0 - искать и фриленсеров и работодателей, 1 - искать только фриленсеров, 2 - искать только работодателей
	if (s.indexOf(" get_only_freelancers") != -1) this.userType = 1;
	if (s.indexOf(" get_only_employer") != -1)   this.userType = 2;
	this.startSymbol = parseInt(s.replace(/.*b_combo__input_quantity_symbols_(\d+).*/g, '$1'))? parseInt(s.replace(/.*b_combo__input_quantity_symbols_(\d+).*/g, '$1')):3;
	this.itemsInRequest = parseInt(s.replace(/.*b_combo__input_items_(\d+).*/g, '$1'))? parseInt(s.replace(/.*b_combo__input_items_(\d+).*/g, '$1')):5;
	this.useCache = 1;
	this.method = s.replace(/.*b_combo__input_request_id_(\w+).*/g, '$1');
	if (this.method.indexOf(" ") != -1) this.method = "getuserlist";
	var id = s.replace(/.*drop_down_default_(_?\d+).*/g, '$1');
	var re = new RegExp("_\\d+");
	if (re.test(id) ) {
		id = id.replace("_", "-");
		id = parseInt(id);
	}
	if (!id&&(id !== "0")&&(id !== 0)) {
	    id = parseInt(this.b_input.value);
        if (id) {
            this.b_input.value = '';
		}
	}
	if (id) {
		this.id_input.value = id;
		this.post("get_user_info", this.onUserInfo, this.onFailUserInfo, "uid=" + id);
	}
	if ((id === "0")||(id === 0)) {
		this.id_input.value = id;
		this.post("get_user_info", this.onUserInfo, this.onFailUserInfo, "uid=" + id);
	}
	if (s.indexOf("drop_down_not_use_cache") != -1) this.useCache = 0;
	//this.useCache = 0;
	this.setEventListeners();
	var tail = Math.random();
	this.moreUsersId    = "muid_" + this.b_input.id + "_" + tail;
	while ($(this.moreUsersId)) {
		tail = Math.random();
		this.moreUsersId    = "muid_" + this.b_input.id + "_" + tail;
	}
	this.moreContactsId = "mcid_" + this.b_input.id + "_" + tail;
	this.usersLblId     = "ulid_" + this.b_input.id + "_" + tail;
	this.contactsLblId  = "clid_" + this.b_input.id + "_" + tail;
	this.hideResult = 1;
	if (!id) {	    
	    this.onKeyUp({code:0}, this.b_input);
	}
}
CAutocompleteInput.prototype = new CDropDown();	//наследуемся от CDropDown
/**
* после того как данные списка загружены, устанавливает необходимые слушатели событий
*/
CAutocompleteInput.prototype.setEventListeners = function() {
	//если есть стрелка - вешаем на стрелку, иначе на сам контейнер с полем ввода
	var toggler = this.outerDiv.getElement('.b-combo__arrow-user');
	if (!toggler) toggler = this.outerDiv;
	toggler.self = this;
	toggler.addEvent('click', this.onToggle);
	this.b_input.self = this;
	this.b_input.addEvent("keyup", this.onKeyUp);
	this.b_input.addEvent("focus", this.on_focus);
	this.label.self = this;
	this.label.addEvent("click", this.hideLabel);
}

/**
 * listener
 * */
CAutocompleteInput.prototype.hideLabel = function() {
	var self = this.self;
	if (!self) self = this;
	if (self.outerDiv.hasClass("b-combo__input_disabled")) {
		return false;
	}
	self.label.removeClass("b-combo__label_show");
	self.setInputVisible(1);
	try {
	    self.b_input.focus();
    } catch(e){;}
    return false;
}

CAutocompleteInput.prototype.on_focus = function() {	
	var self = this.self;
    self.focused = 1;	
	if (self.outerDiv.hasClass("b-combo__input_disabled")) {self.b_input.blur(); return;}
	self.setHeight();
	var flag = self.columns[0].getElements("div.b-combo__user").length && self.shadow.hasClass('b-shadow_hide');
	self.show(flag);	
}

/**
* Делает следующий элемент активным
* Если активного нет делает активным первый  или последний в зависимоти от нажатой клавиши
* надо допилить, когда появится отдельный стиль для активного элемента
*@param n  - код клавиши
*/
CAutocompleteInput.prototype.setActive = function(n) {
	var ls = this.columns[0].getElements("div.b-combo__user");
	var found = 0;
	var i = 0;
	for (i = 0; i < ls.length; i++) {
		if (ls[i].hasClass(this.HOVER_CSS)) {
			ls[i].removeClass(this.HOVER_CSS);
			found = 1;
			break;
		}
	}
	if (found) {
		if (n == 38) i--;
		if (i < 0)   i = ls.length - 1;
		if (n == 40) i++;
		if (i >= ls.length) i = 0;
	}else {
		if (n == 38) i = ls.length - 1;
		if (n == 40) i = 0;
	}
	ls[i].addClass(this.HOVER_CSS);
	this.itemHighlightFromMouse = false;
}
/**
 * 
 * */
CAutocompleteInput.prototype.onEnter = function() {
	this.requestAbort = 1;
	var ls = this.columns[0].getElements("div.b-combo__user");
	var found = 0;
	var i = 0;
	for (i = 0; i < ls.length; i++) {
		if (ls[i].hasClass(this.HOVER_CSS)) {
			found = 1;
			break;
		}
	}

	if (found) {
        //search section
        var l2 = this.columns[0].getElements("div");
        var section = -1; //не удалось определить
        for (var j = 0; j < l2.length; j++) {
            if (l2[j].id.indexOf("clid_") == 0) {
                section = 0;
            }
            if (l2[j].id.indexOf("ulid_") == 0) {
                section = 1;
            }
            if (l2[j] == ls[i]) {
                break;
            }
        }
        //end search section
		this.createValueError = 0;
	    this.err = 0;
		this.b_input.value  = ls[i].get('val');
	    this.id_input.value = ls[i].get('dbid');
	    this.section_input.value = section;
		this.show(0);
        if (this.selectors.indexOf(" b_combo__input_resize") != -1) {
            this.resize(this.label);
        }
		this.b_input.blur();
	}
}
/**
* listener (callback)
*/
CAutocompleteInput.prototype.onBlur = function() {
	this.self.outerDiv.removeClass("b-combo__input_error");
    this.self.focused = 0;
	if (this.self.err || this.self.createValueError) {
		this.self.outerDiv.addClass("b-combo__input_error");
	}
	if ((this.self.b_input.value == "")&&(this.self.grayText != '')) {
		this.self.outerDiv.removeClass("b-combo__input_error");
		this.self.b_input.addClass("b-combo__input-text_color_a7");
		this.self.b_input.value = this.self.grayText;
	}
	if ( this.self.b_input.value == this.self.grayText)  {
        this.self.b_input.addClass("b-combo__input-text_color_a7");
    }
	if (this.self.outerDiv.hasClass("b-combo__input_disabled")) {
		return;
	}
	this.self.outerDiv.removeClass('b-combo__input_current');
	//this.getNext('.b-combo__label').set('text',this.get('value'));
}

/**
* @param HtmlInputElement obj - элемент ввода, который следует отресайзить 
* @param Bool reduction       - указывает, идет ли ресайз в сторону уменьшения
*/
CAutocompleteInput.prototype.resize = function(obj, reduction) {
    if (this.selectors.indexOf(" b_combo__input_resize") == -1) {
        return;
    }
    var b_combo__label_show = 0;
    if ( this.label.hasClass("b-combo__label_show") ) {
        this.label.removeClass("b-combo__label_show");
        b_combo__label_show = 1;
    }
	var s = obj.get('value');
	if (s) this.label.set('html', s);
	 else s = this.label.get("text");
	var currentW = parseInt( this.label.getStyle('width') );
	var avg = Math.ceil(currentW / s.length);
	if (reduction != 1) currentW += avg; //чтобы первый буквы фразы не скрывались раньше времени при наборе
	// проверяем длину label, и если он шире блока b-combo__input то увеличиваем его			
	if ((obj.w <= currentW) && (currentW < obj.maxW) ){
		obj.getParent('.b-combo__input').setStyle('width', currentW + this.togglerW + 'px');	}
		//иначе, если label короче блока .b-combo__input устанавливаем ему его начальную ширину
	else {
		if ( currentW <= obj.w) {
			obj.getParent('.b-combo__input').setStyle('width', String(parseInt(obj.w) + this.togglerW) + 'px');
		}
		if ( currentW > obj.maxW) {
			obj.getParent('.b-combo__input').setStyle('width', parseInt(obj.maxW) + 'px');
		}
	}
    if (b_combo__label_show) {
        this.label.addClass("b-combo__label_show");
    }
}
/**
 * Снятие выделения с элементов списка при вводе текста
 */
CAutocompleteInput.prototype.clearSelection = function() {		
	var ls = this.columns[0].getElements("div.b-combo__user").removeClass(this.HOVER_CSS);		
}
/**
 *listener
 */
CAutocompleteInput.prototype.onKeyUp = function(evt, input) {
	if (!input) input = this;
	var self = input.self;
    self.requestAbort = 0;
	input.readOnly = false;	
	if (self.ctrl == 1 && evt.code != 86){
		self.ctrl = 0;
		return;
	}	
	if (evt.code == 17) {
		self.ctrl = 0;
		return;
	}
	self.cutBadSymbols();
	if ((evt.code == 38)||(evt.code == 40)) {
		if (self.columns[0].getElements("div.b-combo__user").length > 0) {
			self.show(1);
			self.setActive(evt.code);
		}
		return;
	}
	if (evt.code == 13) {
		self.onEnter();
		return;
	}	
	if (evt.code == 46){
		return;
	}
	self.clearSelection();
	var v = input.value;
	if ((v.length >= self.startSymbol)||((self.hideResult)&&(v.length == 0))||(v == '')) {
		if (self.highlightExistValue()) {
			self.createValueError = 0;
			return;
		}else {
			self.id_input.value = null;
		}
		if (!self.allowCreateValue) {
		    self.createValueError = 1;		    
		}
		if (self.useCache) {
			var data = self.cache.list[self.hash()];
			var j = 0;
			if (data instanceof Array) {
				if (data.length > 0) {
					self.createStructure();
					var contacts = 0;
					for (var i in data) {
						var o = data[i];
						if (o.id) {
							self.addItem(o.id, o.name, o.login, o.link, o.img, o.isContact, o.role, 1, o.css, o.address);
							if (o.isContact) contacts++;
							j++;
						}
					}
					var data = self.cache.counters[self.hash()];
					if (!data) {
						self.show(0);
						return;
					}
					self.addMoreString(data.moreContacts, 1, contacts);
					self.addMoreString(data.moreUsers, 0, j - contacts);
					if (!contacts) self.removeContactsDiv();
				}else self.columns[0].innerHTML = '';
				if (j) {
					self.outerDiv.removeClass("b-combo__input_error");
					self.err = 0;
				}
                if (!self.reloadProcess) {
                    self.show(j);
                }
				self.setHeight();
                if (self.reloadProcess) {
                    self.setDisabled(0);
                    self.createValueError = self.err = 0;
                    self.show(0);
                    self.searchValueInDisplayList();
                    self.b_input.removeClass("b-combo__input_error");
                    self.outerDiv.removeClass("b-combo__input_current");
                    self.b_input.blur();
                    self.reloadProcess = 0;
                }
				return;
			}else {
                self.createStructure();
                var contacts = 0;
                for (var i in self.cache.flatList) {
                    var o = self.cache.flatList[i];
                    if (o.id) {
                        if (o.name.indexOf(v) != -1 || o.login.indexOf(v) != -1 || String(o.name + " [" + o.login + "]").indexOf(v) != -1) {
                            self.addItem(o.id, o.name, o.login, o.link, o.img, o.isContact, o.role, 1, o.css, o.address);
                            if (o.isContact) {contacts++;}
                            j++;
                        }
                    }
                }
                self.addMoreString(contacts - 1, 1, contacts);
				self.addMoreString(j - contacts, 0, j - contacts);
				if (!contacts) self.removeContactsDiv();
                if (j > 0) {
                    self.setHeight();
					if (self.reloadProcess) {
						self.setDisabled(0);
						self.createValueError = self.err = 0;
						self.searchValueInDisplayList();
						self.b_input.removeClass("b-combo__input_error");
						self.outerDiv.removeClass("b-combo__input_current");
						self.b_input.blur();
						self.reloadProcess = 0;
					}
					self.show(1);
					if ( self.highlightExistValue() ) {
                     return;
				    }
                } else {
                    self.show(0);
                }
                if (!self.allowCreateValue && v.trim() != '') {
					self.err = 1;
                }
            }
		}
		if (self.send == 1) {
            return;
        }
		
		if (!self.method) {
			throw new Exception(0, "Request Id Required");
			return;
		}		
		if (!self.send) {
		    self.send = 1;
		    self.storeValue = input.value;		
		    var word = input.value.replace("[", "");
		    word = word.replace("]", "");
            self.post(self.method, self.onData, self.onFailData, "word=" + word + "&limit=" + self.itemsInRequest + "&userType=" + self.userType + "&scope=" + self.scope);
		}
	} else {		
		var ls = self.columns[0].getElements("div.b-combo__user");
		self.show(ls.length);
	}
}
/**
* после того как данные списка загружены, устанавливает необходимые слушатели событий
*/
CAutocompleteInput.prototype.onToggle = function() {
	// проверка высоты выпадающего окна (первая колонка) и если оно больше допустимой заданой, то добавляем к нему скролл
	//скорее всего уйдет в класс - родитель
	var self = this.self;	
	self.setHeight();
	self.hideLabel();
	var flag = self.columns[0].getElements("div.b-combo__user").length && self.shadow.hasClass('b-shadow_hide');
	if (flag) self.b_input.blur();	
}
/**
* подсветка записи при полном совпадении ввода (Имя Фамилия Логин)
*/
CAutocompleteInput.prototype.highlightExistValue = function() {
	var v = this.b_input.value.toLowerCase();
	var ls = this.columns[0].getElements("div.b-combo__user");
	var found = 0;
	var i = 0;
	for (i = 0; i < ls.length; i++) {
        if (ls[i]) {
            if (String(ls[i].get("login")).toLowerCase() == v || String(ls[i].get("val")).toLowerCase() == v) {
			    found = 1;			
                break;
             }
		}
	}
	if (found) {
		this.columns[0].getElements("div.b-combo__user").removeClass(this.HOVER_CSS);
		ls[i].addClass(this.HOVER_CSS);
		this.itemHighlightFromMouse = false;
	}
	return found;
}
/**
*@param int index           - идентификатор элемента из БД
*@param String name         - имя пользователя
*@param String login        - ник пользователя
*@param String link         - ссылка на профиль пользователя
*@param String img          - линк на изображение
*@param Bool   isContact     - определяет, добавить элемент в список контактов или список пользователей.
*@param String   role        - определяет, работодатель или фриленсер charAt(0)
*@param Bool   nocache       - определяет, добавить элемент в кеш класса или нет.
*@param String extendCss     - дополнительные css для div.b-combo__user
*@param String address       - адрес компании
*/
CAutocompleteInput.prototype.addItem = function (index, name, login, link, img, isContact, role, nocache, extendCss, address) {
	var text = name;
	var div = this.users;
    if (!extendCss) {
        extendCss = '';
    } else {
        extendCss = ' ' + extendCss;
    }
    if (!address) {
        address = '';
    }
    if (!login) {
        login = '';
    }
	if (isContact == 1) {		
		div = this.contacts;
	}
	if (nocache != 1) {
		if (!this.cache.list[this.hash(1)]) this.cache.list[this.hash(1)] = new Array();
		this.cache.flatList[index] =  this.cache.list[this.hash(1)][index] = {id:index, name:name, login:login, link:link, img:img, isContact:isContact, role:role, css:extendCss, address:address};
	}
	var d = new Element('div', {'class':'b-combo__user' + extendCss});
	d.addEvent("click", function () {return false;});
	d.inject(div, 'after');
	
	if (img.length > 0) {
		var img = new Element('img', {'class':'b-username__avatar', "style":"width:16px;height:16px",  "src":img});
		img.inject(d, 'top');
	}
	var color = "fd6c30";
	if (!role) role = '';
	if (role.charAt(0) == 1) color = "6db335";
	var openBlackSpan = '<span class="b-combo__username" style="color:#000 !important">';
	var openColorSpan = '<span class="b-combo__userlogin b-combo__userlogin_color_' + color + '" style="color:' + color + '">';
	d.innerHTML += openBlackSpan + this.highlight(text) + "</span>";	
	if (login.length > 0) d.innerHTML +=  ' [' + openColorSpan + this.highlight(login) + '</span>]';
    this.resizeShadow(d.innerHTML, 0, 0);
    if (address.length > 0) {
        d.innerHTML +=  ' <div class="b-combo__txt b-combo__txt_adr">' + address + '<div class="b-combo__shad"></div></div>';
    }
	d.self = this;
	d.set("dbid", index);
	d.set("val", text);
    if (login) {
        d.set("login", login);
    }
	d.addEvent('click', this.onItemClick);
	d.addEvent('mouseover', this.onItemOver);
	if (isContact == 1) this.contacts = d;
		else this.users = d;
}
/**
 *@param Bool ajax - если true используется сохраненое значение подстроки, иначе - значение input
 * */
CAutocompleteInput.prototype.hash = function (ajax) {
	var s = this.storeValue;
	if (ajax != 1)	s = this.b_input.value;	
	var q = '';
	for (var i = 0; i < s.length; i++) {
		var c = s.charCodeAt(i);
		q += String(c);
	}
	return q;
}
/**
 * Подсветка совпадения во фразе
 * */
CAutocompleteInput.prototype.highlight = function (text) {
	var arr = String(this.b_input.value).split(/\s+/gi);
    var cleanArr = new Array();
    for (var i = 0; i < arr.length; i++) {
        var found = 0;
        for (var j = 0; j < cleanArr.length; j++) {
            if (cleanArr[j] == arr[i]) {
                found = 1;
                break;
            }
        }
        if (!found) {
            cleanArr.push(arr[i]);
        }
    }
    arr = cleanArr;
	for (var i = 0; i < arr.length; i++) {
        var s = String(arr[i]);
        s = s.replace(/\[/g, "");
        s = s.replace(/\]/g, "");
        s = s.replace(/\-/g, "\\-");
        s = s.replace(/\+/g, "\\+");
        if (s.length > 0) {
            var cyr = this.latToCyr(s);
            if (cyr.length > 0) {
			    text = text.replace(new RegExp("(" + cyr + ")", "gi"), '@%&@$1@&%@');
            }
            var lat = this.cyrToLat(s);
            if (lat.length > 0) {
               text = text.replace(new RegExp("(" + lat + ")", "gi"), '@%&@$1@&%@');
            }
            text = text.replace(new RegExp("(" + s + ")", "gi"), '@%&@$1@&%@');
        }
    }
	while (text.indexOf("@%&@") != -1) {
		text = text.replace("@%&@", '<em class="b-username__bold">');
	}
	
	while (text.indexOf("@&%@") != -1) {
		text = text.replace("@&%@", '</em>');
	}
	
	return text;
}

/**
* listener (callback)
*/
CAutocompleteInput.prototype.onItemClick = function () {
	this.self.itemHighlightFromMouse = 0;
	this.self.onEnter();
}
/**
* 
*/
CAutocompleteInput.prototype.onData = function(data) {
	this.self.send = 0;
	var self = this.self;
    if (self.requestAbort) {
        return;
    }
	var counters = data.counters;
	var list = data.list;
	if (list&&list.length > 0) {
        if ( !self.shadow.hasClass("b-shadow_hide")) {
            self.outerDiv.removeClass("b-combo__input_error");
            self.err = 0;
        }
		self.createStructure();
		var contacts = 0;
		for (var i = 0; i < list.length; i++) {
			var o = list[i];
			/*
			 * <pre>Array
(
    [0] => Array
        (
            [uid] => 237991
            [uname] => landfp
            [usurname] => landfp
            [login] => land_fp
            [photo] => 
            [path] => 
            [role] => 1
        )

    [1] => Array
        (
            [uid] => 62853
            [uname] => voland
            [usurname] => freak
            [login] => voland-freak
            [photo] => 
            [path] => 
            [role] => 0
        )

    [2] => Array
        (
            [uid] => 237971
            [uname] => landf
            [usurname] => landf
            [login] => land_f
            [photo] => f_4f7aedf03b54e.png
            [path] => users/la/land_f/foto/
            [role] => 1
        )
)			 * 
			 * */
			var img = '';
			if (o.photo.length > 0) {
				var img = data.dav + '/' + o.path + '/' + o.photo;  
				if (o.photo == "/images/temp/small-pic.gif") img = o.photo;
			}
            var css = self.companyCss;
            if (!o.isCompany) {
                css = '';
            }
            
			try {
				self.cacheRecords[o.uid] = {
					found:1,
					record: {
						uid:o.uid, 
						uname:o.uname, 
						usurname:o.usurname, 
						login:o.login, 
						photo:o.photo,
						path:o.path,
						isContacts:o.isContacts,
						role: o.role,
						address:o.address
					}
					,			dav : data.dav
				};
			} catch(e) {/*console.log(o)*/;}
			self.addItem(o.uid, o.uname + ' ' + o.usurname, o.login, 0, img, o.isContacts, o.role, 0, css, o.address);
			if (o.isContacts) contacts++;
		}
		if (!self.cache.counters[self.hash(1)]) {
			self.cache.counters[self.hash(1)] = {moreContacts:counters.moreContacts, moreUsers:counters.moreUsers};
		}
		self.addMoreString(counters.moreContacts, 1, contacts);
		self.addMoreString(counters.moreUsers, 0, (list.length - contacts));
		if (!contacts) self.removeContactsDiv();		
		self.setHeight();
		if (!self.hideResult && self.focused) {
			self.show(1);
		}else {
			self.hideResult = 0;
		}
        var currValue = self.b_input.value;
        var currSection = 0;
        self.columns[0].getElements("div").each(
            function (item) {
                if (item.id == self.contactsLblId) {
                    currSection = 1;
                }
                if ( self.hasUniqueValueInList(item) ) {
                    self.id_input.set("value", item.get("dbid"));
                    self.section_input.set("value", currSection);
                    self.showLabel(item);
                    self.show(0);
                }
            }
        );
	} else { // с сервера пришел пустой лист
        var currValue = self.b_input.value;
        var hideItems = 1;
        self.columns[0].getElements("div.b-combo__user").each(
            function (item) {
                if ( String(item.get("val") + "[" + item.get("login") + "]").indexOf(currValue) != -1 ) {
                    hideItems = 0;
                }
            }
        );
        if ( hideItems ) {
            self.columns[0].innerHTML = '';
            self.show(0);
        }
        self.cache.list[self.hash(1)] = new Array();
        self.hideResult = 0;
    }
	if (self.storeValue != self.b_input.value) {
		self.onKeyUp({code:0}, self.b_input);
	}
	if (self.reloadProcess) {
        self.setDisabled(0);
        self.searchValueInDisplayList();
        self.reloadProcess = 0;
    }
}
/**
 * @desc  
 * @param HtmlDivElement item - элемент верстки списка результатов
 * Html cтруктура представления результатов поиска
 * */
CAutocompleteInput.prototype.hasUniqueValueInList = function(item) {
    var currValue = this.b_input.value;
    var exItems = 0;
    this.columns[0].getElements("div.b-combo__user").each(
        function (item) {
            if ( String(item.get("val") + "[" + item.get("login") + "]").indexOf(currValue) != -1 ) {
                exItems++;
            }
        }
    );
    //------------------------------------
	return (exItems < 2) && (
        item.hasClass("b-combo__user") && item.get("val") + "[" + item.get("login") + "]" == currValue
        || item.get("login") == currValue
        || item.get("val") == currValue
    );
}
/**
 * Html cтруктура представления результатов поиска
 * */
CAutocompleteInput.prototype.createStructure = function() {	
	this.columns[0].innerHTML = '<div class="b-combo__txt" ' + ( (this.firstSectionText.length == 1 && this.firstSectionText.charCodeAt(0) == 160) ? ' style="display:none" ': '') + 'id="' + this.contactsLblId + '">' + this.firstSectionText + '</div><div class="b-combo__txt b-combo__txt_padbot_20" id="' + this.moreContactsId + '">И еще человек</div><div class="b-combo__txt" ' + ( (this.secondSectionText.length == 1 && this.secondSectionText.charCodeAt(0) == 160) ? ' style="display:none" ' : '') + 'id="' + this.usersLblId + '">' + this.secondSectionText +'</div><div class="b-combo__txt" id="' + this.moreUsersId + '">И еще человек</div>';
	this.contacts = this.columns[0].getElements("div")[0];
	this.users    = this.columns[0].getElements("div")[2];
}
/**
* Добавляет строчку вида "И еще quanity контактов"
*@param Number quantity
*@param Bool   isContact     - добавлять строку в div со списком контактов или нет
*@param Number quantityAdded - сколько добавлено записей в блок, определенный isContact 
*/
CAutocompleteInput.prototype.addMoreString = function(quantity, isContact, quantityAdded) {
	var div = 0;
	if (isContact == 1) {
		div = $(this.moreContactsId);
	}else {
		var ls = this.extendElementPlace.getElements('.b-combo__txt');
		div =  $(this.moreUsersId);
	}
	
    if (parseInt(quantity) > 0) {
        var total = quantity + quantityAdded;
        var measure = this.countMeasure;
        if (isContact && this.countMeasure1.length) {
            measure = this.countMeasure1;
        }else if(!isContact && this.countMeasure2.length) {
            measure = this.countMeasure2;
        }
        if ( total >= 1000 ) {
            if (div) div.set("html", this.getPrefix(quantityAdded) + " " + quantityAdded + " из нескольких тысяч " + measure);
        } else {
            if (div) div.set("html", this.getPrefix(quantityAdded) + " " + quantityAdded + " из " + this.splitNum(total) + " " + measure);
        }
	}else {		
		var parent = div.parentNode;
		parent.removeChild(div);
	}
	
	if (quantityAdded == 0 ) {
		var id = this.usersLblId;
		if(isContact)  {
			id = this.contactsLblId;
		}
		var div = $(id);
		var parent = div.parentNode;
		parent.removeChild(div);
	}
}

/**
* Разбивает число по порядкам
* например 1000000 на 1 000 000 
*@param Number n
*@return String 
*/
CAutocompleteInput.prototype.splitNum = function(n) {
	var s = String(n);
	var a = new Array();
	var j = 1;
	for (var i = s.length - 1; i > -1; i--, ++j) {
		a.push(s.charAt(i));
		if ((j % 3) == 0) a.push(' ');
	}
	a.reverse();
	return a.join("");
}

/**
* Удаляет div'ы содержащие результаты поиска среди контактов пользователя 
*/
CAutocompleteInput.prototype.removeContactsDiv = function() {
	var div = this.columns[0];
	var ls = div.getElements("div");
	var rm = new Array();
	var i = 0;
	for (i = 0; i < ls.length; i++) {
		rm.push(ls[i]);
		if (ls[i].className.indexOf("b-combo__txt_padbot_20") != -1) {
			break;
		}
	}
	if (i == 1) {
		for (var j = 0; j < rm.length; j++) {
			try{
				div.removeChild(rm[j]);
			} catch(e){;}
		}
		ls[i + 1].set("html", "Пользователи");
	}
}
/**
*Обработка ошибки загрузки данных списка пользователя
*/
CAutocompleteInput.prototype.onFailData = function(data) {
	this.self.columns[0].innerHTML = '';
	this.self.send = 0;
}
/**
 * override CDropDown.show
 * */
CAutocompleteInput.prototype.show = function(f) {
	if(f){
			this.close(this.b_input.id);
			this.outerDiv.addClass('b-combo__input_current');
			try {
				this.b_input.removeClass('b-combo__input-text_color_a7');
                this.b_input.focus();
            } catch(e) {;}
			var c = 0;					
			if (this.shadow.getElements("div.b-combo__user").length > 0) {
				c++;				
			}			
            this.shadow.addClass("b-shadow_zindex_3");
            this.outerDiv.addClass("b-shadow_zindex_4");
			if (c) this.shadow.removeClass('b-shadow_hide');
	}else{
            this.shadow.addClass('b-shadow_hide');
            this.shadow.removeClass("b-shadow_zindex_3");
            this.outerDiv.removeClass("b-shadow_zindex_4");
			var ls = this.columns[0].getElements("div.b-combo__user");
			var found = 0;
			var i = 0;
            var loginFound = 0;
			for (i = 0; i < ls.length; i++) {
                var login = ls[i].get('login');
                if (!login) {
                    login = '';
                }
                if (login.length > 0) {
                    var plainText = ls[i].get("text");
                    var ptArr = plainText.split('[');
                    plainText = ptArr[1];
                    var cLogin = '';
                    if (plainText) {
                        ptArr = plainText.split(']');
                        cLogin = ptArr[0];
                    }
                    if (cLogin.toLowerCase() == this.b_input.value.toLowerCase()) {
						this.id_input.set("value", ls[i].get('dbid'));
                        loginFound = 1;
                    }
                }
				if ((ls[i].hasClass(this.HOVER_CSS)&&(this.itemHighlightFromMouse == false)) || loginFound) {
					found = 1;
					break;
				}
			}
			
			if (found) {
                this.showLabel(ls[i]);
			}
	}
}
/**
 * @param mixed span  - htmlSpanElement или объект события, в зависимости от того, как вызывается метод
 * @param Bool  flag  - true когда метод вызван принудительно
 * */
CAutocompleteInput.prototype.onItemOver = function(span, flag) {
	if (!flag) span = this;
	var self = span.self;
	var ls = self.shadow.getElements("div.b-combo__user");
	if (ls.length > 0) {
		for (var i = 0; i < ls.length; i++) {
			var dv = ls[i];
			dv.removeClass(self.HOVER_CSS);
		}
		span.addClass(self.HOVER_CSS);
		if (!flag) self.itemHighlightFromMouse = true;
	}
}
/**
 * Выпадающего элемента при клике "в молоко"
 */
CAutocompleteInput.prototype.hide = function() {
    if (this.outerDiv.hasClass("b-combo__input_disabled")) {
         return;
    }
	try {
		this.show(0);
		if (this.err == 1 || this.createValueError) {
			this.onInvalidValue();
		}else this.outerDiv.removeClass('b-combo__input_error');
	}catch(e){}
}
/**
 * Вырезает лишниe символы
 */
CAutocompleteInput.prototype.cutBadSymbols = function() {
	var lat = "abcdefghijklmnopqrstuvwxyz";
	var cyr = "абвгдеёжзийклмнопрстуфхцчшщъыьэюя";
	cyr += cyr.toUpperCase();
	lat += lat.toUpperCase();
	var allow = "№[]0123456789 _-," + lat + cyr;
	var q = '';
	var s = this.b_input.value;
	for (var i = 0; i < s.length; i++) {
		if (allow.indexOf(s.charAt(i)) != -1) q += s.charAt(i);
	}

    if (s != q) {
        var pos = this.getCaretPosition();
        if (pos > (q.length)) pos = q.length;
        this.b_input.value = q;
        this.setCaretPosition(pos);
    }
}
/**
* 
*/
CAutocompleteInput.prototype.onKeyDown = function(evt) {		
    if (evt.code == 8) {
		return;
	}
	this.self.requestAbort = 0;
	if ( evt.code == 17) {
		this.self.ctrl = 1;
		return;
	}
	if (this.self.ctrl == 1) return;
	if (evt.code == 46){
		return;
	}	
	if (evt.code != 13) {
		this.self.requestAbort = 0;
	} 
	var shift  = [61, 59, 220, 18, 192, 222, 191, 190]; //коды клавиш, которые печатают символы в сочетании с Shift
	for (var i = 48; i < 58; i++) {
	    if (i != 51) {
		   shift.push(i);
		}//48-57
	}
	var numlock = [106, 107, 110, 111, 192, 59, 222, 191, 190, 188, 190];
	for (var i = 0; i < numlock.length; i++) {
		if (evt.event.keyCode == numlock[i]) {
			this.self.b_input.readOnly = true;
			this.self.show(1);
			break;
		}
	}	
	if (evt.event.shiftKey == 1) {
		for (var i = 0; i < shift.length; i++) {
			if (evt.event.keyCode == shift[i]) {
				this.self.b_input.readOnly = true;
				this.self.show(1);
				break;
			}
		}
	}	
	this.self.cutBadSymbols();	
	if ((evt.code == 38)||(evt.code == 40)) return;
	var reduction = 0;
	if ((evt.code == 8)||(evt.code == 46)) reduction = 1;
	this.self.resize(this, reduction);
}
/**
* Обработка данных значения по умолчанию
*/
CAutocompleteInput.prototype.onUserInfo = function(data) {
	var self = this.self;
	if (!self) {
		self = this;
	}
	if (data.found) {
		self.cacheRecords[data.record.uid] = data;
	    var o = data.record;
	    var img = '';
        if (o.photo) {
            img = data.dav + '/' + o.path + '/' + o.photo;
        }
		if (o.photo == "/images/temp/small-pic.gif") { img = o.photo; }
        self.storeValue = self.b_input.value = o.uname + " " + o.usurname;
        if (o.login) {
            self.storeValue = self.b_input.value += " [" + o.login + "]";
        }
	    self.label.w = self.b_input.w;
        self.label.maxW = 10000;
        self.resize(self.label);
        var color = "fd6c30";
        var role = String(o.role);
        if (role.charAt(0) == '1') color = '6db335';
        var html = '';
        if (img) {
            html = '<img class="b-username__avatar" style="width: 16px; height: 16px;" src="' + img + '">';
        }
        var brackets = '';
        if (o.login && o.login.length > 0) brackets  = ' [<span style="color:#' + color + '" class="b-combo__userlogin b-combo__userlogin_color_' + color + '">' + o.login + '</span>]';
        html += '<span style="color:#000 !important" class="b-combo__username">' + o.uname + ' ' + o.usurname + '</span>' + brackets;
        self.label.set("html", html);
        self.setInputVisible(0)
        self.label.addClass("b-combo__label_show");
        if (self.selectors.indexOf(" b_combo__input_resize") != -1) {
             self.outerDiv.setStyle('width', self.label.clientWidth + 10);
        }
        self.createStructure();
        var css = self.companyCss;
        if (!o.isCompany) {
            css = '';
        }
        self.addItem(o.uid, o.uname + ' ' + o.usurname, o.login, 0, img, o.isContacts, o.role, 0, css, o.address);
        if (o.isContacts) {
            self.section_input.value = 0;
        } else {
           self.section_input.value = 1;
        }
        self.addMoreString(0, 1, 0);
		self.addMoreString(0, 0, 0);
		var ls = self.shadow.getElements(".b-combo__user");
		self.itemHighlightFromMouse = false;
		ls[0].addClass(self.HOVER_CSS);
        html = ls[0].getElements("span")[0].get("html").replace(/<em[^>]*>/gi, "");
        html = html.replace(/<\/em>/gi, "");
        ls[0].getElements("span")[0].set("html", html);
        self.id_input.set("value", o.uid);
        self.show(0);
    }   
}
/**
* здесь может быть обработка ошибкиданных значения по умолчанию
*/
CAutocompleteInput.prototype.onFailUserInfo = function(evt) {

}
/**
* 
*/
CAutocompleteInput.prototype.onchangeHandler = function(evt) {
	if (this.prevIdValue != this.id_input.value) {
		this.prevIdValue = this.id_input.value;
		this._onchangeHandler();
	}
}
/**
* Склонение слова "Показан(-ы)"
*@param Number quantity
*@param Bool   n          
*/
CAutocompleteInput.prototype.getPrefix = function(n) {
    var argN = n = parseInt(n);
	var sN = String(n);
	var m = null;
	if (sN != "NaN") {
		if(sN.length > 1) {
			n = parseInt(sN.charAt(sN.length - 1));
			m = parseInt(sN.charAt(sN.length - 2));
		}
	}
	if ((m == 1)&&(n == 1))	{		
        return "Показаны";
	}
	if (n == 1) {
        return "Показан";
	}
	return "Показаны";
}
/**
 * Очистка текстового поля
 */
CAutocompleteInput.prototype.clear = function() {
    this.columns[0].innerHTML = '';
    this.id_input.value = '';
    this.b_input.value = '';
    this.label.removeClass("b-combo__label_show");
	this.setInputVisible(1);
    this.resize(this.b_input, 1);
    this.reloadProcess = 1;
    this.onKeyUp({code:0}, this.b_input);
    this.b_input.blur();
    //this.show(0);
    this.outerDiv.removeClass("b-combo__input_error");
}
/**
 * Перезагрузка данных подстроки s
 * @param String s - подстрока
 * 
 */
CAutocompleteInput.prototype.reload = function(s) {    
	this.clear();
	this.reloadProcess = 1;
	this.hideResult    = 1;
    this.b_input.value = s;
    this.setDisabled(1);
    this.onKeyUp({code:0}, this.b_input);
}
/**
 * Ищет среди добавленных в список соответствие div.val и b_input.value.
 * Если найдено, добавляет свойству HOVER_CSS  и подставляет вместо текста метку label
 * */
CAutocompleteInput.prototype.searchValueInDisplayList = function() {
    var ls = this.columns[0].getElements("div.b-combo__user");
    var found = 0;
    var i = 0;
    for (i = 0; i < ls.length; i++) {
        if ((ls[i].get("val") == this.b_input.value)) {
            found = 1;
            break;
        }
    }
    if (found) {
        ls[i].addClass(this.HOVER_CSS);
        this.showLabel(ls[i]);
    }
}
/**
 *Копирует html из элемента списка в label  и отображает label, скрывая input text
 *@param item    - элемент выпадающей части списка
 * */
CAutocompleteInput.prototype.showLabel = function(item) {
    this.outerDiv.removeClass("b-combo__input_error");
    this.createValueError = 0;
    this.label.set("html", item.get("html"));
    var addr = this.label.getElement("div.b-combo__txt.b-combo__txt_adr");
    if (addr) {
        var prnt = addr.parentNode;
        prnt.removeChild(addr);
    }
    this.label.w = this.b_input.w;
    if (this.selectors.indexOf(" b_combo__input_resize") != -1) {
	    this.resize(this.label);
	}
    var html = item.get("html").replace(/<em[^>]*>/gi, "");
    html = html.replace(/<\/em>/gi, "");
    this.label.set("html", html);
    var addr = this.label.getElement("div.b-combo__txt.b-combo__txt_adr");
    if (addr) {
        var prnt = addr.parentNode;
        prnt.removeChild(addr);
    }
    this.b_input.set("value", item.get("val"));
    this.setInputVisible(0);
    var cW = parseInt( this.label.getStyle('width') );
    this.label.addClass("b-combo__label_show");
    this.onchangeHandler();
}
/**
 *@param int    id           - идентификатор записи в таблице базы данных
 *@param String requestId    - идентификатор запроса на сервер
 *@param String args         - еще параметры в виде var0=val0&var1=val1...
 * */
 CAutocompleteInput.prototype.loadRecord = function(id, requestId, args) {
    if (!parseInt(id)) {
		return;
	}
	if (this.cacheRecords[id] instanceof Object) {
		this.onUserInfo(this.cacheRecords[id]);
		return;
	}
    this.id_input.value = id;
    var param = "uid=" + id;
    if (args) {
        param += "&" + args;
    }
    this.post(requestId, this.onUserInfo, this.onFailUserInfo, param);
 }
/**
* @param String str        - HTML для проверки длины
* @param Bool   reduction  - указывает, идет ли ресайз в сторону уменьшения
* @param Int    dw         - если не ноль, суммируется с label.width
*/
CAutocompleteInput.prototype.resizeShadow = function(str, reduction, dw) {    
    if (!dw) {
        dw = 0;
    }
    var s = this.selectors;
    this.label.w = parseInt(s.replace(/.*b_combo__input_width_(\d+).*/g, '$1'));
    this.label.maxW = parseInt(s.replace(/.*b_combo__input_max_width_(\d+).*/g, '$1'));
    if (!this.label.maxW) {
        this.label.maxW = 1000;
    }
    if (!this.label.w) {
        this.label.w = 0;
    }
    var label = new Element("nowrap", {style:"position:absolute; top:0px; left:0px"});
    document.body.appendChild(label);
    var oldW      = parseInt( this.shadow.getStyle('width') );
    label.set('html', str);
    var currentW = parseInt( label.getStyle('width') ) + this.shadow.getElement('div.b-shadow__left').getStyle('padding-left').toInt() - this.shadow.getElement('div.b-shadow__body').getStyle('margin-left').toInt() + this.togglerW + dw; 
    // проверяем длину label, и если он шире блока b-combo__shadow, то увеличиваем его            
    if ((this.label.w <= currentW) && (currentW < this.label.maxW) ){
        this.shadow.setStyle('width', currentW);
    }
    //иначе, если label короче блока .b-combo__input устанавливаем ему его начальную ширину
    else {
        if ( currentW <= this.label.w) {
           this.shadow.setStyle('min-width', parseInt(this.label.w));
        }
        if ( currentW > this.label.maxW) {
           this.shadow.setStyle('width', parseInt(this.label.maxW));
        }
    }
    //был более широкий элемент -  ресазим для него
    if ( parseInt( this.shadow.getStyle('width') ) < oldW && oldW < this.label.maxW) {
        this.shadow.setStyle('width', oldW);
    }
    document.body.removeChild(label);
}
/**
 * Транскрипция кирилицы в латиницу
 * @param  String s
 * @return String
 * */
CAutocompleteInput.prototype.cyrToLat = function(s) {
    s = s.replace(new RegExp("кс", "g"), "x");
    s = s.replace(new RegExp("Кс", "g"), "X");
    s = s.replace(new RegExp("КС", "g"), "X");
    var cyr = "абвгдеёжзийклмнопрстуфхцчшщъыьэюя";
    var lat = "abvgdeejziyklmnoprstufhccccciceua";
    cyr += cyr.toUpperCase();
    lat += lat.toUpperCase();
    var result = '';
    "абвгдеёжзийклмнопрстуфхцчшщъыьэюя"
    for (var i = 0; i < s.length; i++) {
        var ch = s.charAt(i);
        var j = cyr.indexOf(ch);
        var ch2 = '';
        if (j != -1) {
            ch2 = lat[j];
        }
        if (ch == "ч") {
            ch2 = "ch";
        }
        if (ch == "Ч") {
            ch2 = "Ch";
        }
        if (ch == "ч") {
            ch2 = "ch";
        }
        if (ch == "Ш") {
            ch2 = "Sh";
        }
        if (ch == "ш") {
            ch2 = "sh";
        }
        if (ch == "Щ") {
            ch2 = "Shch";
        }
        if (ch == "щ") {
            ch2 = "shch";
        }
        if (ch == "ъ" || ch == "ь" || ch == "Ь" || ch == "Ъ") {
            ch2 = "";
        }
        if (ch == "Ю") {
            ch2 = "Yu";
        }
        if (ch == "ю") {
            ch2 = "yu";
        }
        if (ch == "Я") {
            ch2 = "Ya";
        }
        if (ch == "я") {
            ch2 = "ya";
        }
        result += ch2;
    }
    return result;
}
/**
 * Транскрипция латиницы в кирилицу
 * @param  String s
 * @return String
 * */
CAutocompleteInput.prototype.latToCyr = function(s) {
    s = s.replace(new RegExp("shch", "g"), "щ");
    s = s.replace(new RegExp("Shch", "g"), "Щ");
    s = s.replace(new RegExp("SHCH", "g"), "Щ");
    
    s = s.replace(new RegExp("sh", "g"), "ш");
    s = s.replace(new RegExp("Sh", "g"), "Ш");
    s = s.replace(new RegExp("SH", "g"), "Ш");
    
    s = s.replace(new RegExp("ch", "g"), "ч");
    s = s.replace(new RegExp("Ch", "g"), "Ч");
    s = s.replace(new RegExp("CH", "g"), "Ч");
    
    s = s.replace(new RegExp("yu", "g"), "ю");
    s = s.replace(new RegExp("Yu", "g"), "Ю");
    s = s.replace(new RegExp("YU", "g"), "Ю");
    
    s = s.replace(new RegExp("ya", "g"), "я");
    s = s.replace(new RegExp("Ya", "g"), "Я");
    s = s.replace(new RegExp("Ya", "g"), "Я");
    
    var cyr = "абвгдеёжзийклмнопрстуфхцчшщъыьэюя";
    var lat = "abvgdeejziyklmnoprstufhccccciceua";
    cyr += cyr.toUpperCase();
    lat += lat.toUpperCase();
    var result = '';
    for (var i = 0; i < s.length; i++) {
        var ch = s.charAt(i);
        var j = lat.indexOf(ch);
        var ch2 = '';
        if (j != -1) {
            ch2 = cyr[j];
        }
        if (ch == "x") {
            ch2 = "кс";
        }
        if (ch == "X") {
            ch2 = "Кс";
        }
        result += ch2;
    }
    return result;
}
/**
 * Скрыть/показать поле ввода
 * @param bool f  - показать или скрыть поле
 * */
CAutocompleteInput.prototype.setInputVisible = function(f) {
    if (!f) {
        this.b_input.setStyle("display", "none");
        this.outerDiv.setStyle("border-top", "1px solid #888C8F").setStyle("height", "27px").setStyle("border-bottom", "1px solid  #A2A6A8").setStyle("border-left", "1px solid  #BDC0C1").setStyle("border-right", "1px solid  #BDC0C1");
    } else {
        this.b_input.setStyle("display", "block");
        this.outerDiv.setStyle("border", null);
    }
}
//Конец определения класса  CAutocompleteInput
