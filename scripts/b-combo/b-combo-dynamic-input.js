//Определение класса  CDynamicInput
/**
* @param HtmlDivElement htmlDiv
* @param Array           cssSelectors
*/
function CDynamicInput(htmlDiv, cssSelectors) {
	this.init(htmlDiv, cssSelectors);
}
/**
* @param HtmlDivElement htmlDiv
* @param Array           cssSelectors
*/
CDynamicInput.prototype.init = function(htmlDiv, cssSelectors) {
	if (this.defined(htmlDiv)) {
		this.outerDiv = htmlDiv; //первый внешний div по отношению к input
		var ls = htmlDiv.getElements(".b-combo__input-text");
		if (ls.length > 0) {
			var input = ls[0];
			this.b_input = input;
            this.b_input.self = this;
			if (!this.b_input.name) this.b_input.name = this.b_input.id;
			
			var lbl = htmlDiv.getElements("label");
			var f = false;
			this.label = 0;
			for (var i = 0; i < lbl.length ; i++) {
				if (lbl[i].hasClass("b-combo__label")) {
					this.label = lbl[i];
					f = true;
					break;
				}
			}			
			if (!f) {
				var lbl = new Element("label", {"class":"b-combo__label", "for":input.id});
				lbl.inject(input, "after");
				this.label = lbl;
			} 			
			input.self = this;
			input.addEvent("focus", this.onFocus);
			input.addEvent("blur",  this.onBlur);

			if (this.defined(cssSelectors)) {
				var s = cssSelectors.join(' ');
				this.selectors = s;
				var m = s.match(/b_combo__input_resize/g);
				if (m) {
					var w  = input.w    = parseInt(s.replace(/.*b_combo__input_width_(\d+).*/g, '$1'));
					var mw = input.maxW = parseInt(s.replace(/.*b_combo__input_max_width_(\d+).*/g, '$1'));
					if (!mw) {
						input.maxW = mw = 1000;
					}
					if (w && mw) {
						var event = 'keydown';
						if (this.checkBrowser() == 'opera') event = 'keypress';
						input.addEvent(event,  this.onKeyDown);
						this.resize(input);
					}else {
						mw = input.maxW = 10000;
					}
				}
				this.disallowNull       = 0;
				if (s.indexOf("disallow_null") != -1) {
					this.disallowNull = 1;					
				}
				if (this.selectors.indexOf(" numeric") != -1 && parseInt(input.maxLength)) {
                    input.set('min', this.checkPattern(/.*numeric_min_([\d]+).*/, 1));
                    input.set('max', this.checkPattern(/.*numeric_max_([\d]+).*/, 1));
                    input.set('storeMaxLength', parseInt(input.maxLength));
                    input.addEvent('keypress', this.onNumericInputKeyPress);
                    input.addEvent('keyup', this.onNumericInputKeyUp);
                    input.addEvent('blur', this.onNumericInputBlur);
                }
			}
		};	
		this.outerDiv.addEvent('click', function (){return false;});
		document.addEvent("click", this.close);
		this.grayText = '';
		if (this.b_input) {
			if (this.b_input.getAttribute("graytext"))  this.grayText = this.b_input.getAttribute("graytext");
			if ((this.b_input.value == '')&&(this.grayText != '')) {
				this.b_input.addClass('b-combo__input-text_color_a7');
				this.b_input.value = this.grayText;
			}
		}
	}
}

/**
 * @param id  - идентификатор элемента, который не будет закрыт
 * Закрытие всех выпадающих элементов при клике "в молоко"
 */
CDynamicInput.prototype.close = function(id) {
	var list = ComboboxManager.getList();
	for (var i = 0; i < list.length; i++) {		
		if (!id) list[i].hide();
			else if (list[i].id() != id){
				list[i].hide();
			}				
	}
}
/**
 * Выпадающего элемента при клике "в молоко"
 */
CDynamicInput.prototype.hide = function() {
	try {
		this.shadow.addClass('b-shadow_hide');
		this.outerDiv.getElement('.b-combo__input-text').removeClass('b-combo__input-text_color_a7');				
		if (this.err == 1) {			
			this.onInvalidValue();
		}else this.outerDiv.removeClass('b-combo__input_error');
	}catch(e){}
}
/**
* listener (callback)
*/
CDynamicInput.prototype.onKeyDown = function(evt) {
	if (this.self.b_input.readOnly) return;
	var reduction = 0;
	if ((evt.code == 8)||(evt.code == 46)) reduction = 1;
	this.self.resize(this, reduction);
}
/**
* listener (callback)
*/
CDynamicInput.prototype.onBlur = function() {
	if (this.self.outerDiv.hasClass("b-combo__input_disabled")) {
		return;
	}
	this.self.outerDiv.removeClass('b-combo__input_current');
	this.getNext('.b-combo__label').set('text',this.get('value'));
	if ((this.self.b_input.value == "")&&(this.self.grayText != '')) {
		this.self.b_input.addClass("b-combo__input-text_color_a7");
		this.self.b_input.value = this.self.grayText;
	}
}
/**
* listener (callback)
*/
CDynamicInput.prototype.onFocus = function() {
	this.self.close(this.self.id());
	if (this.self.outerDiv.hasClass("b-combo__input_disabled")) {
		this.self.b_input.blur(); 
		return;
	}
	this.self.outerDiv.removeClass('b-combo__input_error').addClass('b-combo__input_current');
	this.getNext('.b-combo__label').set('text',this.get('value'));
	var s = this.getNext('.b-combo__label').get('text');
	if (s != this.value) this.set('value', s);
	this.self.b_input.removeClass("b-combo__input-text_color_a7");
	if (this.self.grayText != '') {
	    if (this.self.grayText == this.self.b_input.value) {
	        this.self.b_input.value = "";
	    }
	}	
}

/**
* Устанавливает фокус и желтую рамку
*/
CDynamicInput.prototype.setFocus = function(dbg) {
	this.outerDiv.removeClass('b-combo__input_error').addClass('b-combo__input_current');
	this.b_input.getNext('.b-combo__label').set('text',this.b_input.get('value'));
	this.b_input.set('value', this.b_input.getNext('.b-combo__label').get('text'));
    try {
        this.b_input.focus();
    } catch (e) {;}
}

/**
* @param HtmlDivElement parentDiv - див в котором будет создана структура блоков shadow_*
* данная html структура используется всеми дочерними классами
*/
CDynamicInput.prototype.buildShadow = function(parentDiv) {
	var shadowConfig = new Array(
		'b-shadow b-shadow_m b-shadow_hide b-shadow_inline-block',
		'b-shadow__right',
		'b-shadow__left',
		'b-shadow__top',
		'b-shadow__bottom',
		'b-shadow__body b-shadow__body_bg_fff b-layout b-combo__body'
	);
	this.shadow      = null;
	this.shadowBody = null;
	for (var i = 0; i < shadowConfig.length; i++) {
		this.extendElementPlace =  new Element('div', {'class':shadowConfig[i]});
		if (shadowConfig[i].indexOf('b-shadow__body') != -1) this.shadowBody = this.extendElementPlace;
		this.extendElementPlace.inject(parentDiv, (i == 0)?'bottom':'top');
		if (i == 0) this.shadow = this.extendElementPlace;
		parentDiv = this.extendElementPlace;		
	}
	var shadowFooter = new Array();
	for (var i = 0; i < shadowFooter.length; i++) {
		var div = new Element('div', {'class':shadowFooter[i]});
		div.inject(this.shadow, 'bottom');
	}
}
/**
* @param HtmlInputElement obj - элемент ввода, который следует отресайзить 
* @param Bool reduction       - указывает, идет ли ресайз в сторону уменьшения
*/
CDynamicInput.prototype.resize = function(obj, reduction) {
    if (this.selectors.indexOf(" b_combo__input_resize") == -1) {
        return;
    }
	var s = obj.get('value');
	obj.getNext('.b-combo__label').set('text', s);
	var currentW = parseInt( obj.getNext('.b-combo__label').getStyle('width') );
	var avg = Math.ceil(currentW / s.length);
	if (reduction != 1) currentW += 2*avg; //чтобы первый буквы фразы не скрывались раньше времени при наборе
	//если есть тоглер, учитываем его ширину
	var togglerW = 0;
    var toggler = obj.getParent('.b-combo__input').getElement("span.b-combo__arrow");
    if (toggler) {
        togglerW = parseInt(toggler.getStyle("width"));
        if (!togglerW) {
            togglerW = 0;
        }
    }
    // проверяем длину label, и если он шире блока b-combo__input то увеличиваем его
	if ((obj.w <= currentW) && (currentW < obj.maxW) ){
		obj.getParent('.b-combo__input').setStyle('width', String(parseInt(currentW) + togglerW) + 'px');
	}
	//иначе, если label короче блока .b-combo__input устанавливаем ему его начальную ширину
	else {
		if ( currentW <= obj.w) {
			obj.getParent('.b-combo__input').setStyle('width', String(parseInt(obj.w) +  togglerW) + 'px');
		}
		if ( currentW > obj.maxW) {
			obj.getParent('.b-combo__input').setStyle('width', parseInt(obj.maxW));
		}
	}
}
/**
* @param  Mixed obj - элемент или значение
* @return true если сущность определен (не NaN не Null  и не undefined)
*/
CDynamicInput.prototype.defined = function(obj) {	
	if ((String(obj) == 'null')||(String(obj) == 'undefined')||(String(obj) == 'NaN')) {
		return false;
	}
	return  true;
}
/**
* @return String  "msie"|"firefox"|"firefox"
*/
CDynamicInput.prototype.checkBrowser = 	function(getieversion) {
	var s = navigator.userAgent.toLowerCase();
	if (s.indexOf("msie") != -1) {
		if (getieversion != 1)	return "msie";
			else{
			if (s.indexOf("7.0") != -1) return "msie7";
			if (s.indexOf("8.0") != -1) return "msie8";
			if (s.indexOf("9.0") != -1) return "msie9";
		}
	}
	if (s.indexOf("firefox") != -1) return "firefox";
	if (s.indexOf("opera") != -1) return "opera";
	if (s.indexOf("chrom") != -1) return "chrome";
	return "";
}
/**
* @param String   actionName  - идентификатор запроса  отправляемого на сервер, будет отправлен в  переменной action.
* @param Function onSuccess   - ссылка на функцию, которая обрабатывает успешныйзапрос
* @param Function onFail      - ссылка на функцию, которая обрабатывает ошибку выполнения запроса
* @param String   data        - переменные в формате name1=value1&name2=value2
* @param Bool     json        - указывает, запрашивать данные в json фщрмате или нет. По умолчанию true
*/
CDynamicInput.prototype.post = 	function(actionName, onSuccess, onFail, data, json) {
	if (String(json) == "undefined") json = true;
	if (json) {
		var req = new Request.JSON(
			{
				url: B_COMBO_AJAX_SCRIPT,
				onSuccess: onSuccess,
				onFailure: onFail
			}
		);
		req.self = this;
		var _data = "action=" + actionName + "&u_token_key=" + _TOKEN_KEY;
		if (data&&data.length > 0) _data += "&" + data;
		req.post(_data);
	} else {
		var req = new Request({
			url: B_COMBO_AJAX_SCRIPT, 
			onSuccess: onSuccess,
			onFailure: onFail
		});
		req.self = this;
		req.post("action=" + actionName + "&u_token_key=" + _TOKEN_KEY);
	}
}


/**
 * Установка позиции курсора в текстовом поле
 * **/
CDynamicInput.prototype.setCaretPosition = function (pos)  {
	this.textCursorAction = 1; //проверив этот флаг можно например не показывать выпадашку при фокусе (см. например CCalendarInput.show() )
	var input = this.b_input;		
    var doBlur = 0;
    if (input != document.activeElement) {
        doBlur = 1;
    }
	if (input.readOnly) return;	
	if (input.value == "") return;	
	if ((!pos)&&(pos !== 0)) return;
	var f = 0;
	try {f = input.setSelectionRange;}
	catch(e){;}
	if(f)	{
		input.focus();		
		try{
			input.setSelectionRange(pos,pos);
		}catch(e){
			//если находится в контейнере с style="display:none" выдает ошибку
		}
	}
	else if (input.createTextRange) {		
		var range = input.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
    if (doBlur) {
        input.blur();
    }
}

/**
 * Получение позиции курсора в текстовом поле
 * **/
CDynamicInput.prototype.getCaretPosition = function ()  {
	this.textCursorAction = 1; //проверив этот флаг можно например не показывать выпадашку при фокусе (см. например CCalendarInput.show() )
	var input = this.b_input;
    var doBlur = 0;
    if (input != document.activeElement) {
        doBlur = 1;
    }
	var pos = 0;
	// IE Support
	if (document.selection) {		
		if (input.value.length == 0) return;
		input.focus ();
		var sel = document.selection.createRange ();
		sel.moveStart ('character', -input.value.length);
		pos = sel.text.length;
	}
	// Firefox support
	else if (input.selectionStart || input.selectionStart == '0'){
		pos = input.selectionStart;		
	}
    if (doBlur) {
        input.blur();
    }
	return pos;
}

/**
 * Возвращает id главного инпута
 * @return String
 * */
CDynamicInput.prototype.id = function ()  {
    if (this.b_input) {
        if (this.b_input.id) {
            return this.b_input.id;
		}
    }
	return false;
}


/**
 * Вызывается при потере фокуса элементом если this.err != 0; 
 * При необходимости перегрузите этот метод в классе наследнике
 * */
CDynamicInput.prototype.onInvalidValue = function ()  {
	//alert('parent method');
}

/**
 * Ищет в css селекторах 
 * 	паттерн_(ЗНАЧЕНИЕ)
 * Возвращает ЗНАЧЕНИЕ или false если паттерн не найден
 * @param RegExp re - регулярное выражение 
 * @param Bool   toInt = false - если true ЗНАЧЕНИЕ будет приведено к целому числу 
 * */
CDynamicInput.prototype.checkPattern = function(re, toInt, dbg) {
	var s  = this.selectors;
	var value = s.replace(re, '$1');
	if (dbg) alert(re);
	if (dbg) alert(value);
	re = /\s/gi
	if (re.test(value)) return false;
	if (toInt) {
		value = parseInt(value);
		if (!value) return false;
	}
	return value;
}

/**
 * Устанавливает css селектор b-combo__input_disabled 
 * @param Bool flag - значение свойства disabled
 * */
CDynamicInput.prototype.setDisabled = function(flag) {
	this.outerDiv.removeClass("b-combo__input_disabled");
	if (flag == 1) {
		this.outerDiv.addClass("b-combo__input_disabled");
	}	
}

/**
 * Запрещает ввод в поле символы кроме цифр если в селекторах есть numeric
 * Если заданы пределы numeric_max_M numeric_min_N то следит за попаданием значения в эти пределы 
 * */
CDynamicInput.prototype.onNumericInputKeyPress = function (evt) {
	var max = parseInt(this.get("max"));
    var min = parseInt(this.get("min"));
    var ch = String.fromCharCode(evt.code);
    var allow = "0123456789";
    var stop = 0;
    if (allow.indexOf(ch) == -1) {
    	stop = 1;
    } else {
        if (min && max) {
            var n = parseInt(this.value + ch);
            if (n > max) {
            	if (String(n).length <= this.get("storeMaxLength")) {
                    this.set("value", max);
                    stop = 1;
                }
            } else if (n < min) {
                //this.set("value", min);
                //stop = 1;
            }            
        }
    }
    if (stop) {
        this.maxLength   = (this.value.length > 0 ?(this.value.length - 1):0);
    }
}
CDynamicInput.prototype.onNumericInputKeyUp = function(evt) {
    this.maxLength = this.get("storeMaxLength");
}
CDynamicInput.prototype.onNumericInputBlur = function () {
    var max = parseInt(this.get("max"));
    var min = parseInt(this.get("min"));
    if (max && min) {
        if (parseInt(this.value) < min || parseInt(this.value) > max) {
            this.self.outerDiv.addClass("b-combo__input_error");
        }
    }
}
/**
*
 * */
CDynamicInput.prototype.setDefaultValue = function() {
	//перезагрузить в наследнике
}

//Конец определения класса  CDynamicInput

