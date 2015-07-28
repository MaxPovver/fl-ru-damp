/**
* Зависит от:
* b-combo-dynamic-input.js
*/
//Начало определения класса CCalendarInput
/**
* @param HtmlDivElement htmlDiv
* @param Array           cssSelectors
*/
function CCalendarInput(htmlDiv, cssSelectors) {
    this.initialization = true; // если true - значит происходит процесс инициализации календаря
	this.init(htmlDiv, cssSelectors);
	this.textCursorAction = 0;
	if (this.outerDiv.hasClass('b-combo__input_error')) {
	    this.forceError = 1;
	}
	if (!this.outerDiv.hasClass("b-combo__input_arrow_date_yes")) 
		this.outerDiv.addClass("b-combo__input_arrow_date_yes");	
	this.FORMAT_ENG = 'eng';
	this.FORMAT_RUS = 'rus';
	this.FORMAT_USE_DOT = '.';
	this.FORMAT_USE_SLASH = '/';
	this.FORMAT_USE_TEXT = ' ';
	this.FORMAT_USE_DASH = '-';
	this.format = this.FORMAT_RUS;
	this.formatDivider = '.';
	this.readUserFormat();
	
    // Если название элемента массив
    if(this.b_input.name.indexOf('[') != -1) {
        var b_input_name = this.b_input.name.replace(']', '');
        b_input_name = b_input_name + "_eng_format]";
    } else {
        var b_input_name = this.b_input.name ? this.b_input.name + "_eng_format" : this.b_input.id + "_eng_format";
    }
    
	this.e_date = new Element("input", {"type":"hidden", "id": this.b_input.id + "_eng_format", "name": b_input_name});
	this.e_date.inject(this.outerDiv, "top");
	this.grid = new Array();
	this.buildShadow(htmlDiv.getParent('.b-combo'));
	this.extendElementPlace.addClass('b-calendar');
	this.extendElementPlace.removeClass('b-layout');
	this.buildTable();
	this.setEventListeners();
	this.yearLabel = this.extendElementPlace.getElement('b-calendar__currentmonth');
	this.months = new Array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
	this.months2 = new Array('', 'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь');
	this.qDay  = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	this.parseDateLimit();
	this.LIM = 42;	
	this.usePast = 1;
    this._onchangeHandler = function(){;}
    if (this.b_input.onchange instanceof Function) {		
        this._onchangeHandler = this.e_date.onchange = this.b_input.onchange;
        this.b_input.onchange = null;
    }
    if (this.selectors.indexOf('use_past_date') == -1) {
        this.usePast = 0;
    }
    this.cacheCurrentDate();
	var userdata = 0;
	var v = this.b_input.value;
	
	this.disallowNull = 0;
	if (this.selectors.indexOf(" disallow_null") != -1) this.disallowNull = 1;
	this.setCurrentDateOnNull = 0;
	if (this.selectors.indexOf(" set_current_date_on_null") != -1) this.setCurrentDateOnNull = 1;
	
	if (this.b_input.value) {
		userdata = this.onKeyUp({code:0}, this, 1);		
		if (userdata == 0) {
			this.b_input.value = v;			
			this.hide();			
			this.b_input.blur();
            this.initialization = false;
			return;
		}
	}	
	if (this.selectors.indexOf('date_use_server_time') == -1) {
		var dt= new Date();
		var day = dt.getDate();
		var m   = dt.getMonth();
		var y   = dt.getFullYear();
		this.setDate(  String(y) + "-" + this.nToStr(++m) + "-" + this.nToStr(day), true  );	
		this.hide();
		this.b_input.blur();
	}else {
		this.post("getdate", this.onDate, this.onFailServerDate, "", 0);
	}
    this.initialization = false;
}
CCalendarInput.prototype = new CDynamicInput();
/**
* после того как данные списка загружены, устанавливает необходимые слушатели событий
*/
CCalendarInput.prototype.setEventListeners = function() {
	//если есть стрелка - вешаем на стрелку, также показываем календарь при фокусе в поле ввода
	var toggler = this.outerDiv.getElement('.b-combo__arrow-date');
	if (!toggler) toggler = this.outerDiv;
	toggler.self = this;
	toggler.addEvent('click', this.onToggle);
	this.b_input.self = this;
	this.b_input.addEvent("focus", this.on_focus);
	this.outerDiv.addEvent('click', this.returnFalse);
	var prev = this.shadow.getElement(".b-calendar__prevmonth").getElements("a")[0];
	prev.self = this;
	prev.addEvent("click", this.onPrevMonth);
	var next = this.shadow.getElement(".b-calendar__nextmonth").getElements("a")[0];
	next.self = this;
	next.addEvent("click", this.onNextMonth);
	this.b_input.addEvent("keyup", this.onKeyUp);
	this.b_input.addEvent("keyup", this.onKeyDown);
}

/**
 * Сохраняет текущую локальную дату, либо переданную в eDate дату как точку, относительно 
 * которой будет устанавливаться дотупность выбора дней календаря
 * @param eDate - дата в формате YYYY-mm-dd или пустое значение
 * **/
CCalendarInput.prototype.cacheCurrentDate = function (eDate) {
	//if (!this.usePast) {
		var re = /[0-9]{4}\-[0-9]{2}\-[0-9]{2}/gi
		var dt  = new Date();
		var day = dt.getDate();
		var m   = dt.getMonth() + 1;
		var y   = dt.getFullYear();
		if (re.test(eDate)) {
			var a = eDate.split("-");
			day = this.toNumber(a[2]);
			m   = this.toNumber(a[1]);
			y   = this.toNumber(a[0]);
		}
		this.cacheDate = {y:y, m:m, d:day};
	//}
}


/**
*Строит таблицу в которой будут элементы календаря
*/
CCalendarInput.prototype.buildTable = function() {
 var p = this.extendElementPlace;
 var t = new Element('table', {'class':'b-calendar__title', 'border':0, 'cellpadding':0, 'cellspacing':0});
 t.inject(p, 'top');
 
 var th = new Element('thead', {'class': 'b-calendar__head'});
 th.inject(t, 'top');
 var row = new Element('tr', {'class':'b-calendar__navigate'});
 row.inject(th, 'top');
 var arr = new Array('b-calendar__prevmonth', 'b-calendar__currentmonth', 'b-calendar__nextmonth');
 var attr = {'class': 'b-calendar__link', 'href':'#'};
 for (var i = 0; i < arr.length; i++){
	var td = new Element('td', {'class':arr[i]});
	td.inject(row, 'bottom');
	if (i == 1) td.setProperty('colspan', 5)	
		else {
			var a = new Element('a', attr);
			a.inject(td, 'bottom');
	}
 } 
 var tb = new Element('tbody', {'class':'b-calendar__body'});
 tb.inject(t, 'bottom'); 
 var days = new Array('Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
 this.createRow(days, tb, 'b-calendar__nameday', 0, 1, 'th');
  
 var t  = new Element('table', {'class':'b-calendar__month', 'border':0, 'cellpadding':0, 'cellspacing':0});
 t.inject(p, 'bottom');
 var tb = new Element('tbody', {'class':'b-calendar__body'});
 tb.inject(t, 'bottom');
 
 days = new Array('01', '31', '31', '25', '25', '25', '25'); 
 this.createRow(days, tb, 'b-calendar__day', 1);
 this.createRow(days, tb, 'b-calendar__day', 1);
 this.createRow(days, tb, 'b-calendar__day', 1);
 this.createRow(days, tb, 'b-calendar__day', 1);
 this.createRow(days, tb, 'b-calendar__day', 1);
 this.createRow(days, tb, 'b-calendar__day', 1);
}
/**
*@param Array items              -  массив элементов, которые будут помещены в ячейки ряда
*@param HtmlTBodyElement parent  -  куда добавлять ряды
*@param String css               -  имя css класса, добавляемого к ячейкам
*@param Bool addToGrid           -  добавлять ли ячейку в массив с которым работаем при установке даты
*@param Bool weekend             -  добавлять ли css селектор выходного дня
*@param String type = 'td'       -  тип ячейки - "th" | "td"
*/
CCalendarInput.prototype.createRow = function(items, parent, css, addToGrid, weekend, type) {
	if (!type) type = 'td';
	var row = new Element('tr', {'class':'b-calendar__week'});
	row.inject(parent, 'bottom');
	for (var i = 0; i < items.length; i++) {
		var cell = new Element(type, {'class':css});
		cell.inject(row, 'bottom');
		if (type =='th') cell.set('text', items[i]);
			else {
			var a = new Element('a', {'class':'b-calendar__link', 'href':'#'});
			a.inject(cell, 'top');
			a.set('text', items[i]);
		}
		if ((weekend == 1)&&(i > 4)) {
			cell.addClass('b-calendar__nameday_weekend');
		}
		if (addToGrid) {
			this.grid.push(cell);
		}
	}
}

/**
* listener
*/
CCalendarInput.prototype.onToggle = function() {
	var self = this.self;
	if (self.outerDiv.hasClass("b-combo__input_disabled")) return;
	self.show(self.shadow.hasClass('b-shadow_hide'));
}

/**
* listener
*/
CCalendarInput.prototype.show = function(flag) {
	if (this.textCursorAction) {
		this.textCursorAction = 0;
		return;
	}
	if(flag){
			this.close();
            this.shadow.setProperty('style', 'z-index:30');
			this.outerDiv.addClass('b-combo__input_current');
			this.shadow.removeClass('b-shadow_hide');			
			this.outerDiv.removeClass('b-combo__input_error');
            try {
                this.b_input.focus();
            }catch (e) {;}
	}else{
		    this.shadow.setProperty('style', 'z-index:100');
		    this.shadow.addClass('b-shadow_hide');
			this.outerDiv.getElement('.b-combo__input-text').removeClass('b-combo__input-text_color_a7');		
	}
}

/**
*@param Number n
*@return String 
*/
CCalendarInput.prototype.nToStr =  function (n) {
	var s = String(n);
	if (n < 10) {
		s = "0" + s.replace(/0/gi, '');
	}
	return s;
}
/**
*@param String eDate  - строка в английском формате yyyy-mm-dd
*@param Bool   setFocus  - устанавливать ли фокус
*/
CCalendarInput.prototype.setDate = function(eDate, setFocus) {
	if (!setFocus) {
        setFocus = false;
	} else {
		setFocus = true;
	}
    if (String(eDate) == "undefined") {
        this.e_date.set("value", '');
        this.b_input.set("value", '');
        return;
	}
	var a = eDate.split("-");
	this.setInputValue(a, setFocus);
	this.e_date.set("value", eDate);
	this.sMonthText    = this.months[this.toNumber(a[1])];
	this.sYearText     = a[0];
	this.sDay          = a[2];
	this.shadow.getElement(".b-calendar__currentmonth").set("text", this.months2[this.toNumber(a[1])] + ' ' + this.sYearText);

	var month = this.toNumber(a[1]) - 1;
	var dt = new Date(a[0], month, this.toNumber(a[2]));
	dt = new Date(a[0], month, 1);
	var offset = dt.getDay();
	
	if (offset == 0) offset = 7;
	offset--;
	
	if (this.leapYear(a[0])) this.qDay[1] = 29;
		else this.qDay[1] = 28;
	var limit = this.qDay[month] + offset;
	if (limit > this.LIM) limit = this.LIM;
	var j = 1;
	var activeFound = false;
	var activeDay   = 32;
	var last = 0;

	//Предыдущий месяц
	var idx = month - 1;
	if (idx < 0) idx = 11;
	var pqd = this.qDay[idx] - offset + 1;
	//Следующий месяц
	var nm = 1;
	for (var i = 0; i < this.LIM; i++ ) {
		//var s = "m_n" + String(i);
		//var mc:CViewDay = calendar.getChildByName(s) as CViewDay;	
		var mc = this.grid[i];
		mc.removeClass("b-calendar__day_current");
		mc.removeClass("b-calendar__day_last");
		mc.removeClass("b-calendar__day_weekend");
		mc.removeClass("b-calendar__day_future");
		mc.removeClass("b-calendar__day_nextmonth");
		mc.removeClass("b-calendar__day_prevmonth");		
		
		mc.getElements('a')[0].self = this;
		mc.getElements('a')[0].addEvent('click', this.returnFalse);
		if ((i >= offset) && (i < limit )) {
			//mc.setText(String(j));
			mc.getElements('a')[0].set("text", j);
			if ( (j == this.toNumber(a[2])) 
			     //&& (this.cacheDate.m == this.toNumber(a[1]))
			     //&& (this.cacheDate.y == this.toNumber(a[0]))  
			   ) {
					//активировали
					//mc.m_a_text.visible = activeFound = mc.isActive = mc.activeMc.visible = true;
					activeFound = 1;
					activeDay   = j;
					mc.addClass("b-calendar__day_current");
					mc.removeClass("b-calendar__day_future");
				}
				else {
					//отметили как неактивный
					//mc.m_a_text.visible = mc.isActive = mc.activeMc.visible = false;
					mc.removeClass("b-calendar__day_current");
				}			
			last = i;
			var css = this.getCssByDate(eDate, j);			
			if (!this.usePast) {							
				if (css == "b-calendar__day_future") {
					//alert(eDate + ", j = " + j + ", i = " + i);
					if (!mc.hasClass("b-calendar__day_current")) mc.addClass(css);
					mc.getElements('a')[0].addEvent('click', this.onSelectDate);
					this.setCursor(mc, "pointer");
				}
				 else {
					 mc.addClass(css);
					 mc.getElements('a')[0].removeEvent('click', this.onSelectDate);
					 this.setCursor(mc, "default");
				 }
		    } else {
				if (css == "b-calendar__day_future") {
					if (!mc.hasClass("b-calendar__day_current")) mc.addClass("b-calendar__day_future");
					mc.getElements('a')[0].addEvent('click', this.onSelectDate);
					this.setCursor(mc, "pointer");		
				}else {
					 mc.addClass(css);
					 mc.getElements('a')[0].removeEvent('click', this.onSelectDate);
					 this.setCursor(mc, "default");
				 }		
			}
			j++;
		}else {
			mc.getElements('a')[0].removeEvent('click', this.onSelectDate);
			this.setCursor(mc, "default");
		}		
		
		var prevMonthIndex = this.toNumber(a[1]) - 1;
		if (prevMonthIndex < 1) prevMonthIndex = 12;
		prevMonthIndex = this.nToStr(prevMonthIndex);
		var ePrevMonthDate = a[0] + "-" + prevMonthIndex + "-" + a[2];
		
		if (i < offset) {
			//прошедший месяц
			//mc.setText(String(pqd), prevColor, false);
			mc.getElements('a')[0].set("text", pqd);
			mc.addClass('b-calendar__day_prevmonth');
			if ( (this.getCssByDate(ePrevMonthDate, pqd) == "b-calendar__day_future")) {
				mc.getElements('a')[0].addEvent('click', this.onSelectDate);
				mc.addClass('b-calendar__day_future');
				this.setCursor(mc, "pointer");
			}else {
				this.setCursor(mc, "default");
			}
			pqd++;
		}
		
		var nextMonthIndex = this.toNumber(a[1]) + 1;
        var nextYear = a[0];
        if (nextMonthIndex > 12) {
            nextMonthIndex = 1;
            nextYear++;
        }
		nextMonthIndex = this.nToStr(nextMonthIndex);
        var eNextMonthDate = nextYear + "-" + nextMonthIndex + "-" + a[2];
		if (i >= limit) {
			//будущий (прошедший) месяц
			//mc.setText(String(nm), prevColor, false);			
			mc.addClass('b-calendar__day_nextmonth');
			mc.getElements('a')[0].set("text", nm);			
			if ((this.getCssByDate(eNextMonthDate, nm) == "b-calendar__day_future")) {
				mc.getElements('a')[0].addEvent('click', this.onSelectDate);
				mc.addClass('b-calendar__day_future');
				this.setCursor(mc, "pointer");
			}else {
				this.setCursor(mc, "default");
			}
			nm++;
		}		
	}
	if (!activeFound) {
		//сделали активным last  и записали его в label
		var mc = this.grid[last];
		mc.addClass("b-calendar__day_current");
		//this.b_input.set("value", a[2] + "." + a[1] + "." + a[0]);
		this.setInputValue(a, setFocus);
		this.e_date.set("value", a[0] + "-" + a[1] + "-" + a[2]);		
	}
	if ((this.selectors.indexOf('no_set_date_on_load') != -1)&&(this.firstLoad != 1)) {
		this.firstLoad = 1;
		this.b_input.value = '';
		this.e_date.value  = '';
	}
	this.monthSelect = 0;
	this.outerDiv.removeClass('b-combo__input_error');
}
/**
 * Возвращает b-calendar__day_future или b-calendar__day_last в тех случаях, когда eDate позже текущей
 * @param String eDate - дата в формате YYYY-mm-dd
 * @param Number day   - день месяца (если передан, подставляется вместо dd)
 * @return String
 * */
 CCalendarInput.prototype.getCssByDate = function(eDate, day) {
     var c = {y:this.cacheDate.y, m:this.cacheDate.m, d:this.cacheDate.d};
     if (this.usePast) {
         c = {y:this.MIN_YEAR_LIMIT , m:this.MIN_MONTH, d:this.MIN_DAY};
     } else {
         var minDate = Date.parse(this.MIN_YEAR_LIMIT + "-" + this.nToStr(this.MIN_MONTH) + "-" + this.nToStr(this.MIN_DAY));
         var cDate = Date.parse(c.y + "-" + this.nToStr(c.m) + "-" + this.nToStr(c.d));
         if (minDate > cDate) {
             c = {y:this.MIN_YEAR_LIMIT , m:this.MIN_MONTH, d:this.MIN_DAY};
         }
     }
     var future = 'b-calendar__day_future';
     var last   = 'b-calendar__day_last';
     var a = eDate.split("-");
     var d = this.toNumber(a[2]);
     var m = this.toNumber(a[1]);
     var y = this.toNumber(a[0]);
     if (this.toNumber(day) > 0) d = this.toNumber(day);
     
     if (y == this.MAX_YEAR_LIMIT) {
        if ( m > this.MAX_MONTH ) {
            return last;
        }
        else if (m == this.MAX_MONTH) {
             if (d > this.MAX_DAY) {
                 return last;
             }
        }
     }
     if (y > c.y && y <= this.MAX_YEAR_LIMIT) return future;
      else if (y > this.MAX_YEAR_LIMIT) {
         return last;
     }
     if (y < c.y) return last;
     if (y == c.y) {
         if (m > c.m) return future;
         if (m < c.m) {
             return last;
         }
         if (m == c.m) {
             if (d >= c.d) return future;
             if (d < c.d) {
                 return last;
             }
         }
     }
 }
/**
* listener обработка клика на числе в сетке календаря
*/
CCalendarInput.prototype.onSelectDate = function() {
	var self = this.self;
	var n = this.get('text');
	var ar = self.months;
	var m = '';
	for (var j = 0; j < ar.length; j++ ) {
		if (ar[j] == self.sMonthText) {
			var parent = this.parentNode;
			if (parent.className.indexOf("b-calendar__day_nextmonth") != -1) {
				j++;
				if (j > 12) {
					j = 1;
					self.sYearText = Number(self.sYearText) + 1;
				}
			}
			
			if (parent.className.indexOf("b-calendar__day_prevmonth") != -1) {
				j--;
				if (j == 0) {
					j = 12;
					self.sYearText = Number(self.sYearText) - 1;
					if (self.sYearText < self.MIN_YEAR_LIMIT) {
						j = 1;
						self.sYearText++;
						return;
					}
				}
			}
			m = self.nToStr(j);
			break;
		}
	}	
	self.b_input.set("value", self.nToStr(n) + "." + m + "." + self.sYearText);	
	self.setDate(self.sYearText + "-" + m + "-" + self.nToStr(n), true);
    // в IE в момент закрытия календаря срабатывает событие focus и календарь открывается снова
    // по-этому в IE закрываем календарь с задержкой
    if (Browser.ie) {
        setTimeout(function(){
            self.shadow.addClass('b-shadow_hide');
        }, 10)
    } else {
        self.shadow.addClass('b-shadow_hide');
    }
	self.outerDiv.getElement('.b-combo__input-text').removeClass('b-combo__input-text_color_a7');
    self.b_input.blur();
	self.err = 0;
	self.onchangeHandler();
	return false;
}
/**
* заглушка перехода к # 
*/
CCalendarInput.prototype.returnFalse = function() {
	return false;
}
/**
* listener
*/
CCalendarInput.prototype.onPrevMonth = function() {
	var self = this.self;	
	self.err = 0;	
	var storedYear = self.sYearText;
	if (self.leapYear(self.sYearText)) self.qDay[1] = 29;
		else self.qDay[1] = 28;
	var s = self.sMonthText;
	var j = 0;
    var CURR_MAX_DAY = 31;
	for (j = 0; j < self.months.length; j++ ) {
		if (self.months[j] == s) {
			if (j == 1) j = 13;
			j--;
			self.sMonthText = self.months[j];
			break;
		}
	}
	if (j == 12) {
		var y = Number(self.sYearText);
		y--;
		if (y < self.MIN_YEAR_LIMIT) {
			self.sMonthText = s;
			return false;
		}
		self.sYearText = String(y);
	}
	if (!self.usePast) {
		if (   
		          (
		              (j < self.toNumber(self.cacheDate.m))
		           && (self.toNumber(self.sYearText) == self.cacheDate.y)
		          )
		       || (self.toNumber(self.sYearText) < self.cacheDate.y)
		   ) {
			self.sMonthText = s;
			self.sYearText = storedYear;
			return false;
		}
		if 
		(
		      (j == self.toNumber(self.cacheDate.m))
		   && (self.toNumber(self.sYearText) == self.cacheDate.y)
		) {
			if ( self.toNumber(self.sDay) <  self.cacheDate.d)
			    self.sDay = self.nToStr(self.cacheDate.d);
		}
	}
	
	
	if (self.sYearText == self.MIN_YEAR_LIMIT) {		
		if (j < self.MIN_MONTH) {
			self.sMonthText = s;
			self.sYearText = storedYear;
			return false;
		}
		if (j == self.MIN_MONTH) {
			if ( self.toNumber(self.sDay) <  self.MIN_DAY) {
				self.sDay = self.nToStr(self.MIN_DAY);
			}
		}
	}
    var storedDay = self.toNumber(self.sDay);
    // Была ли дата сброшена на меньшую?
    if (typeof self.sDay_descent != 'undefined' && self.sDay_descent && storedDay > 27) {
        self.sDay = self.sDay_old;
        self.sDay_descent = false;
    }
    if (storedDay > 27) { // Надо проверить на кол-во дней в месяце
        CURR_MAX_DAY = 33 - new Date(self.sYearText, j-1, 33).getDate();
        if (storedDay > CURR_MAX_DAY) {
            self.sDay_old = self.sDay;
            self.sDay = self.nToStr(CURR_MAX_DAY);
            // Запоминаем, что сбросили день на меньший.
            self.sDay_descent = true;
        }
    }
	self.monthSelect = 1;
	s = self.sYearText + "-" + self.nToStr(j) + "-" + self.sDay;
	self.setDate(s, true);
    self.onchangeHandler();
	return false;
}
/**
* listener
*/
CCalendarInput.prototype.onNextMonth = function() {
    var self = this.self;
    self.err = 0;
    var storedYear = self.sYearText;
    var storedMonth = self.sMonthText;
    var CURR_MAX_DAY = 31;
    var i = 0;
    for (i = 0; i < self.months.length; i++ ) {
        if (self.months[i] == storedMonth) {
            break;
        }
    }
    var iStoredMonth = i;
    var storedDay = parseInt(self.sDay);
    if (self.leapYear(self.sYearText)) self.qDay[1] = 29;
        else self.qDay[1] = 28;
    var s = self.sMonthText;
    var j = 0;
    for (j = 0; j < self.months.length; j++ ) {
        if (self.months[j] == s) {
            if (j == 12) j = 0;
            j++;
            self.sMonthText = self.months[j];
            break;
        }
    }
    var y = Number(self.sYearText);
    if (j == 1) {
        y++;
        if (y > self.MAX_YEAR_LIMIT) {
            self.sYearText = storedYear;
            self.sMonthText = s;
            return false;
        }
        self.sYearText = String(y);
    } else if ( y == self.MAX_YEAR_LIMIT ) {
        if (j > self.MAX_MONTH) {
            self.sYearText = storedYear;
            self.sMonthText = s;
            return false;
        } else if (j == self.MAX_MONTH ) {
            if (storedDay > self.MAX_DAY) {
                self.sDay = self.nToStr(self.MAX_DAY);
            }
        }
    }
    // Была ли дата сброшена на меньшую?
    if (typeof self.sDay_descent != 'undefined' && self.sDay_descent && storedDay > 27) {
        self.sDay = self.sDay_old;
        self.sDay_descent = false;
    }
    if (storedDay > 27) { // Надо проверить на кол-во дней в месяце
        CURR_MAX_DAY = 33 - new Date(self.sYearText, j-1, 33).getDate();
        if (storedDay > CURR_MAX_DAY) {
            self.sDay_old = self.sDay;
            self.sDay = self.nToStr(CURR_MAX_DAY);
            // Запоминаем, что сбросили день на меньший. Если в следующем есть нужное кол-во дней, восстановим.
            self.sDay_descent = true;
        }
    }
    s = self.sYearText + "-" + self.nToStr(j) + "-" + self.sDay;
    self.monthSelect = 1;    
    self.setDate(s, true);
    self.onchangeHandler();
    return false;
}
/**
* скорее всего заменю на pareseInt
*@param String s
*@return Number n
*/
CCalendarInput.prototype.toNumber = function (s) {
	s = String(s);
	if (s.length != 2) return Number(s);
	var i = Number(s.charAt(0));
	if (i == 0) return Number(s.charAt(1));
	return Number(s);
}
/**
*Високосный ли год 
*@param  int year - год в формате ГГГГ
*@return Bool true если год високосный иначе false
*/
CCalendarInput.prototype.leapYear =  function (year) {
	year = Number(year);
	var r = false;
	var y = year;
	if (y % 4 == 0) {
		if (y % 100 == 0){
			if (y % 400 == 0) return true;
			return false;
		}			
		return true;
	}
	return false;
}
/**
*Читает css селекторы и инициализует свойства format  и formatDivider
*/
CCalendarInput.prototype.readUserFormat = function() {
	var formats = new Array('date_format_rus', 'date_format_eng', 'date_format_use_dot', 'date_format_use_slash', 'date_format_use_text', 'date_format_use_dash');
	for (var i = 0; i < 2; i++) {
		if (this.selectors.indexOf(formats[i]) != -1) {
			switch (i) {
				case 0:
					this.format = this.FORMAT_RUS;
					break;
				case 1:
					this.format = this.FORMAT_ENG;
					break;
			}			
			break; //cycle
		}
	}
    
	for (var i = 2; i < formats.length; i++) {
		if (this.selectors.indexOf(formats[i]) != -1) {
			switch (i) {
				case 2:
					this.formatDivider = this.FORMAT_USE_DOT;
					break;
				case 3:
					this.formatDivider = this.FORMAT_USE_SLASH;
					break;
				case 4:
					this.formatDivider = this.FORMAT_USE_TEXT;
					break;	
				case 5:
					this.formatDivider = this.FORMAT_USE_DASH;
					break;		
			}			
			break; //cycle
		}
	}
}
/**
* Устанавливает значение в сответствии с текущим форматом календаря
*@param Array arrEngDate    - массив ['YYYY', 'mm', 'dd']
*@param Bool  setFocus      - устанавливать ли фокус после установки даты По умолчанию false
*/
CCalendarInput.prototype.setInputValue = function (arrEngDate, setFocus) {
    if (!setFocus) {
        setFocus = false;
    } else {
        setFocus = true;
	}
	var a = new Array();
	for (var i = 0; i < arrEngDate.length; i++) a.push(arrEngDate[i]);	
	this.e_date.set("value",arrEngDate.join("-"));
	if ( (this.toNumber(a[2]) < this.toNumber(a[0]))&&(this.format == this.FORMAT_ENG) )  {
		a = a.reverse();
	}	
    this.readUserFormat();
	if (this.formatDivider == this.FORMAT_USE_TEXT) {
		a[1] = this.months[this.toNumber(a[1])];
		a[2] = this.toNumber(a[2]);
	}
	if (!this.firstRun && !this.initialization) {			
		var p = 0;	
		//if (this.monthSelect != 1) p = this.getCaretPosition();
		this.b_input.set("value", a[2] + this.formatDivider + a[1] + this.formatDivider + a[0]);
        try {
		    if (setFocus) {
                this.b_input.focus();
            }
	    }catch (e) {;}
		//if (this.monthSelect != 1) this.setCaretPosition(p);
	}else {
		this.b_input.set("value", a[2] + this.formatDivider + a[1] + this.formatDivider + a[0]);
		this.firstRun = 0;
	}
}


CCalendarInput.prototype.onKeyDown = function (evt) {
	if (evt.code == 17) this.ctrl = 1;
}

/**
* listener
*/
CCalendarInput.prototype.onKeyUp = function (evt, _self) {	
	if ((this.ctrl)&&(evt.code == 65)) {
		return;
	}	
	if (evt.code == 17) {
		this.ctrl = 0;
		return;
	}
	if ( (evt.code == 37) ||(evt.code ==  39)||(evt.code ==  16)||(evt.code ==  35)||(evt.code ==  36) ) {
		return;
	}	
	var self   = this.self;
	var input  = this;
	if (!self) {
		self  = _self;
		self.firstRun = 1;
		input = this.b_input;
	} else 	if (input.readOnly) return;
    self.sDay_old = self.sDay; // Нужно для правильной работы проверки дат в 
    self.sDay_descent = false; // ... onNextMonth() и onPrevMonth()		
	var div    = self.outerDiv;
	var err    = 1;
	var date   = 0;
	var iMonth = 0;
	var dividers = new Array('.', '/', '-', ' ');
	var formats = new Array('rus', 'eng');
	var cacheF = self.format;
	var cacheFD = self.formatDivider;
	var eDate = 0;
	for (var j = 0; j < formats.length; j++) {
		self.format = formats[j];
		for (var k = 0; k < dividers.length; k++) {
				self.formatDivider = dividers[k];
				if (self.formatDivider != self.FORMAT_USE_TEXT) {
					var pattern = new RegExp("[\\d]{2}\\" + self.formatDivider + "[\\d]{2}\\" + self.formatDivider + "[\\d]{4}");
					if (self.format == self.FORMAT_ENG) pattern = new RegExp("[\\d]{4}\\" + self.formatDivider + "[\\d]{2}\\" + self.formatDivider + "[\\d]{2}");
					if (input.value.test(pattern)) {		
						div.removeClass('b-combo__input_error');						
						err = 0;						
						date = input.value.split(self.formatDivider);
						if (parseInt(date[2]) > parseInt(date[0])) date = date.reverse();
						eDate = self.nToStr(date[0]) + '-' + date[1] + '-' + self.nToStr(date[2]);						
					}
				}else {								
					date = input.value.split(new RegExp('\\s+', 'gi'));
					if (date.length == 3) {						
						for (var i = 1; i < self.months.length; i++) {
							if ( self.months[i] == date[1]) {
								var day = self.toNumber(date[0]);								
								if (!day) date[0] = self.sDay;
								var y = self.toNumber(date[2]);				
								if (!y&&(date[2] != '')) date[2] = self.sYearText;
								iMonth = i;
								err = 0;								
								break;
							}
						}	
						if (parseInt(date[2]) > parseInt(date[0])) {
							date = date.reverse();						
						}						
						if ((self.usePast != 1)&&(date[0] < self.cacheDate.y)) {
							err = 1;
						}
					}
					if (!err) {	
						if (!date[0]) err = 1;
						if (parseInt(date[0]) > self.MAX_YEAR_LIMIT) date[0] = self.sYearText;
						var max = self.qDay[iMonth - 1];
						var d = date[2];						
						if (d > max) d = max;
						eDate = parseInt(date[0]) + '-' + self.nToStr(iMonth) + '-' + self.nToStr(d);
					}
					else {
						//div.addClass('b-combo__input_error');	
					}	
				}
				if (err == 0) {
					break;
				}
		}
		
		if (err == 0) {
			break;
		}
	}		
	if (err == 1) {
        self.format = self.FORMAT_RUS;		
        err = self.setDateAsMonthAndYear(dividers, formats);
		if (!err) return err;		
		self.format = self.FORMAT_RUS;
		err = self.setDateAsDayAndMonth(dividers, formats);
		if (!err) return err;
		self.format = self.FORMAT_RUS;
		err = self.setDateAsMonth();
		if (!err) return err;
		err = self.setDateAsYear();
		if (!err) return err;
		err = self.setDateAsDay();		
		if (!err) return err;		
        
		self.format = cacheF;
		self.formatDivider =  cacheFD;
		
	}else {
		if (self.validDate(eDate)) {
	    if (
				(self.usePast)
			 || (self.getCssByDate(eDate) == "b-calendar__day_future")
		   ) {
			   self.setDate(eDate, true);
			   if (evt.code != 0) {
                   self.onchangeHandler();
			   }
		   }
		   else {
			   err = 1;
		   }
		}
		else {
			err = 1;		
		}
	}	
	self.err = err;	
	return err;
}

/**
 * Парсит значение в инпуте, если его можно трактовать как значение числа изменяет число в текущей дате 
 * */
CCalendarInput.prototype.setDateAsDay = function() {	
    var input  = this.b_input;
	var div    = this.outerDiv;
	var err    = 1;
	var d = this.toNumber(input.value);	
	if (!d) return err;
	
	var iMonth = 0;
	for (var i = 1; i < this.months.length; i++) {
		if ( this.months[i] == this.sMonthText ) {
			iMonth = i;			
			break;
		}
	}
	
	var eDate = this.sYearText + "-" + this.nToStr(iMonth) + "-" + this.nToStr(d);
	if (this.validDate(eDate)) {
		if (
			    (this.getCssByDate(eDate) == "b-calendar__day_future")
			 || this.usePast
		   ) {		   
			   var cache  = this.b_input.value;
			   var p = this.getCaretPosition();
			   this.setDate(eDate, true);
			   this.b_input.value  = cache;
			   this.setCaretPosition(p);
			   err = 0;
		     }
	}
	this.err = err;
	return err;
}

/**
 * Парсит значение в инпуте, если его можно трактовать как значение года изменяет год в текущей дате 
 * */
CCalendarInput.prototype.setDateAsYear = function() {
    var input  = this.b_input;
	var div    = this.outerDiv;
	var err    = 1;
	var y = parseInt(input.value);
	if (!y) {
		return 0;
		//y = parseInt(this.sYearText);
		//if (!y)	y = this.cacheDate.y;
	}
	
	var iMonth = 0;
	for (var i = 1; i < this.months.length; i++) {
		if ( this.months[i] == this.sMonthText ) {
			iMonth = i;			
			break;
		}
	}
	
	var eDate = y + "-" + this.nToStr(iMonth) + "-" + this.sDay;
	if (this.validDate(eDate)) {
		if (
			    (this.getCssByDate(eDate) == "b-calendar__day_future")
			 || this.usePast
		   ) {
			var cache  = this.b_input.value;
			var p = this.getCaretPosition();
			this.setDate(eDate, true);
			this.b_input.value  = cache;			
			this.setCaretPosition(p);
			err = 0;
		}
	}
	this.err = err;
	return err;
}

/**
 * Парсит значение в инпуте, если найдено наименование месяца изменяет месяц в текущей дате 
 * */
CCalendarInput.prototype.setDateAsMonth = function() {
	var input  = this.b_input;
	if (this.strContentMixedDividers(input.value)) return 1;
	var div    = this.outerDiv;
	var err    = 1;
	for (var i = 1; i < this.months.length; i++) {
		if ( this.months[i] == input.value) {
			iMonth = i;
			err = 0;
			break;
		}
	}	
    if (err) {
		for (var i = 1; i < this.months2.length; i++) {
			if ( this.months2[i] == input.value ) {
				iMonth = i;
				err = 0;
				break;
			}
		}
	}
	if (err) {}//div.addClass('b-combo__input_error');
	else {		
		var eDate = this.sYearText + '-' + this.nToStr(iMonth) + "-" + this.nToStr(this.sDay);
		if (
			(this.usePast)
		 || (this.getCssByDate(eDate) == "b-calendar__day_future")
	   ) {
		  //div.removeClass('b-combo__input_error');
		  var cache = this.b_input.value;
		  var p = this.getCaretPosition();
		  this.setDate(eDate, true);
		  this.b_input.value =  cache;
		  this.setCaretPosition(p);
		  this.format = this.FORMAT_RUS;
		  this.formatDivider = this.FORMAT_USE_TEXT;
	   }
	   else {
		   err = 1;
	   }
		
	}
	this.err = err;
	return err;
}

/**
 * при вводе пользователем дня и месяца устанвливает данные месяц и год в календаре.
 * Число ставится первое
 * */
CCalendarInput.prototype.setDateAsDayAndMonth = function(dividers, formats) {
	var input  = this.b_input;
	var div    = this.outerDiv;
	var err    = 1;
	var date   = 0;
	var iMonth = 0;
	for (var j = 0; j < formats.length; j++) {
		this.format = formats[j];
		for (var k = 0; k < dividers.length; k++) {
				this.formatDivider = dividers[k];
				if (this.formatDivider != this.FORMAT_USE_TEXT) {
					var pattern = new RegExp("[\\d]{2}\\" + this.formatDivider + "[\\d]{2}");
					if (this.format == this.FORMAT_ENG) pattern = new RegExp("[\\d]{2}\\" + this.formatDivider + "[\\d]{2}");
                    if ((!pattern.test(input.value))||(this.strContentMixedDividers(input.value))) {
						//div.addClass('b-combo__input_error');
					}else {
						//div.removeClass('b-combo__input_error');
						err = 0;
						date = input.value.split(this.formatDivider);
						
						var max = this.qDay[this.toNumber[date[1]] - 1];
						if (this.toNumber(date[0]) > max) date[0] = max;
												
						var eDate = this.sYearText + '-' + date[1] + '-' + date[0];
						if (this.validDate(eDate)) {
							if (
								    (this.usePast)
								 || (this.getCssByDate(eDate) == "b-calendar__day_future")
							   ) {
								   var cache  = this.b_input.value;
								   var p = this.getCaretPosition();
						           this.setDate(eDate, true);
						           this.b_input.value  = cache;
						           this.setCaretPosition(p);
							   }
							   else {
								   err = 1;
							   }
						}
						else {
						    err = 1;
						}
					}
				}else {
					date = input.value.split(new RegExp('\\s+', 'gi'));
					if (date.length == 2) {
						if ( parseInt(date[0]) && !parseInt(date[1]) ) {
						    for (var i = 1; i < this.months.length; i++) {
									if ( this.months[i] == date[1]) {
										iMonth = i;
										var max = this.qDay[i - 1];
										if (date[0] > max) date[0] = max;
										err = 0;
										break;
									}
								}								
						}
						//date = date.reverse(); !!
					}
					if (err) {}//div.addClass('b-combo__input_error');
						else {
							//div.removeClass('b-combo__input_error');
							var eDate = this.sYearText + '-' + this.nToStr(iMonth) + "-" + this.nToStr(date[0]);							
							var p = this.getCaretPosition();
							this.setDate(eDate, true);
							this.b_input.value =  date[0] + " " + date[1] + " ";
							this.setCaretPosition(p);
						}	
				}
				
				if (err == 0) {
					break;
				}
		}
		if (err == 0) {
			break;
		}
	}
	this.err = err;
	return err;
}

/**
 * при вводе пользователем месяца и года устанвливает данные месяц и год в календаре.
 * Число ставится первое
 * */
CCalendarInput.prototype.setDateAsMonthAndYear = function(dividers, formats) {	
	var input  = this.b_input;
	var div    = this.outerDiv;
	var err    = 1;
	var date   = 0;
	var iMonth = 0;
	for (var j = 0; j < formats.length; j++) {
		this.format = formats[j];
		for (var k = 0; k < dividers.length; k++) {
				this.formatDivider = dividers[k];
				if (this.formatDivider != this.FORMAT_USE_TEXT) {
					var pattern = new RegExp("[0-9]{2}\\" + this.formatDivider + "[0-9]{4}");
					if (this.format == this.FORMAT_ENG) {
						pattern = new RegExp("[0-9]{4}\\" + this.formatDivider + "[0-9]{2}");
					}
                    if (!pattern.test(input.value) || this.strContentMixedDividers(input.value)) {
						//div.addClass('b-combo__input_error');
					}else {						
						//div.removeClass('b-combo__input_error');						
						err = 0;						
						date = input.value.split(this.formatDivider);
						if (parseInt(date[1]) > parseInt(date[0])) date = date.reverse();
						var eDate = this.nToStr(date[0]) + '-' + date[1] + '-01';
						if (this.validDate(eDate)) {
							if (
								    (this.usePast)
								 || (this.getCssByDate(eDate) == "b-calendar__day_future")
							   ) {
								   var cache  = this.b_input.value;
								   var p = this.getCaretPosition();
						           this.setDate(eDate, true);
						           this.b_input.value  = cache;
						           this.setCaretPosition(p);
							   }
							   else {
								   err = 1;
							   }
						}
						else {
						    err = 1;
						}
					}
				}else {
					date = input.value.split(new RegExp('\\s+', 'gi'));
					var v =  date[0];
					var y =  date[1];
					if (date.length == 2) {
						if ( (parseInt(date[0]) > 999) || (parseInt(date[1]) > 999) ) {
						    if ( parseInt(date[0]) > 999) {
								v = date[1];
								y = date[0];
							}
								for (var i = 1; i < this.months.length; i++) {
									if ( this.months[i] == v) {
										iMonth = i;
										err = 0;
										break;
									}
								}
								
						}
					}
					if (err) {}//div.addClass('b-combo__input_error');
						else {
							//div.removeClass('b-combo__input_error');
							var eDate = y + '-' + this.nToStr(iMonth) + '-01';
							var cache = this.b_input.value;
							var p = this.getCaretPosition();
							this.setDate(eDate, true);
							this.b_input.value =  cache;
							this.setCaretPosition(p);
						}	
				}
				
				if (err == 0) {
					break;
				}
		}
		if (err == 0) {
			break;
		}
	}
	this.err = err;
	return err;
}

/**
* загрузка даты с сервера
*/
CCalendarInput.prototype.onDate = function(eDate) {
    this.self.cacheCurrentDate(eDate);
    this.self.setDate(eDate, true);
    this.self.hide();
	this.self.b_input.blur();
}
/**
* ошибка загрузки даты с сервера
*/
CCalendarInput.prototype.onFailServerDate = function(err) {
	var dt  = new Date();
	var day = dt.getDate();
	var m   = dt.getMonth();
	var y   = dt.getFullYear();
	this.disp = false;	
	this.self.setDate(  String(y) + "-" + this.self.nToStr(++m) + "-" + this.self.nToStr(day), true );
}

/**
 * Проверка величины значений года месяца и дня
 * @param String eDate         -  дата в формате YYYY-mm-dd
 * @param Bool   ignoreLimits  -  указывает, нужно ли игнорировать интервалы дат, заданные в 
 *  this.MIN_YEAR_LIMIT  = 2000;
 *	this.MIN_MONTH       = 1;
 *	this.MIN_DAY         = 1;	
 *	this.MAX_YEAR_LIMIT  = 2050;
 *	this.MAX_MONTH       = 12;
 *	this.MAX_DAY         = 31;
 * если ignoreLimits == true проверяется только валидность значения месяца и числа
 * @return Bool  true если числовые значения допустимы и false в противном случае
 * */
CCalendarInput.prototype.validDate = function(eDate, ignoreLimits, dbg) {
	if (dbg) alert("validDate: edATE = " + eDate);
    var a = eDate.split("-");
    var d = this.toNumber(a[2]);
    var m = this.toNumber(a[1]);
    var y = this.toNumber(a[0]);
    var err = false;
    if (!ignoreLimits) {		
		if ((y < this.MIN_YEAR_LIMIT) || (y > this.MAX_YEAR_LIMIT)) err = true;
		if (y == this.MIN_YEAR_LIMIT) {
			if (m < this.MIN_MONTH ) err = true;
			if (m == this.MIN_MONTH) {
				if (d < this.MIN_DAY) err = true;
			}
		}
		
		if (y == this.MAX_YEAR_LIMIT) {
			if (m > this.MAX_MONTH ) err = true;
			if (m == this.MAX_MONTH) {
				if (d > this.MAX_DAY) err = true;
			}
		}
	}
    if ((m < 1)||(m > 12)) err = true;
    
    if (this.leapYear(y)) this.qDay[1] = 29;
		else this.qDay[1] = 28;
	var top = this.qDay[m - 1];
    if ((d < 1)||(d > top)) err = true;
    return (!err);
}
/**
 * Устанавливает ссылке в ячейке стиль курсора
 * @param HtmlCellElement cell  -  Html элемент td
 * @param String cursorView  -  значение свойства cursor
 * */
CCalendarInput.prototype.setCursor = function(cell, cursorView) {
    var ls= cell.getElements("a");
    if (ls.length == 1) {
		ls[0].setProperty("style", "cursor:" + cursorView);
	}
}

/**
 * Устанавливает границы ввода дат 
 * анализируются css селекторы:
 *  year_max_limit_ЗНАЧЕНИЕ - задает максимально возможный год
 *  year_min_limit_ЗНАЧЕНИЕ - задает минимально возможный год
 *  year_month_max_limit_ЗНАЧЕНИЕ_ГОД_ЗНАЧЕНИЕ_МЕСЯЦ         - задает максимально  возможные год и месяц
 *  year_month_min_limit_ЗНАЧЕНИЕ_ГОД_ЗНАЧЕНИЕ_МЕСЯЦ         - задает минимально   возможные год и месяц
 *  date_min_limit_ЗНАЧЕНИЕ_ГОД_ЗНАЧЕНИЕ_МЕСЯЦ_ЗНАЧЕНИЕ_ДЕНЬ - задает минимально   возможную дату
 *  date_max_limit_ЗНАЧЕНИЕ_ГОД_ЗНАЧЕНИЕ_МЕСЯЦ_ЗНАЧЕНИЕ_ДЕНЬ - задает максиимально возможную дату
 * */
CCalendarInput.prototype.parseDateLimit = function() {
	this.MIN_YEAR_LIMIT  = 2000;
	this.MIN_MONTH       = 1;
	this.MIN_DAY         = 1;	
	this.MAX_YEAR_LIMIT  = 3000;
	this.MAX_MONTH       = 12;
	this.MAX_DAY         = 31;
	//------------------------------------------------------------------
    if (!this.parseMinDate()) {
		if (!this.parseMinYearAndMonth())
			this.parseMinYear();
	}
	
	if (!this.parseMaxDate()) {
		if (!this.parseMaxYearAndMonth())
			this.parseMaxYear();
	}
}

/**
 * Устанавливает нижнюю границу ввода дат 
 * анализируется css селектор:
 * date_min_limit_ЗНАЧЕНИЕ_ГОД_ЗНАЧЕНИЕ_МЕСЯЦ_ЗНАЧЕНИЕ_ДЕНЬ - задает минимально  возможную дату
 * @return Bool 
 * */
CCalendarInput.prototype.parseMinDate = function() {	
	var p  = /.*date_min_limit_([0-9]{4}_[0-9]{2}_[0-9]{2}).*/g
	var date = this.checkPattern(p);	
	if (!date) return false;
	
	date = date.replace(/_/g, "-");
	if (this.validDate(date, 1)) {
		var a = date.split("-"); 
		this.MIN_YEAR_LIMIT = a[0];
		this.MIN_MONTH      = this.toNumber(a[1]);
		this.MIN_DAY        = this.toNumber(a[2]);		
		return true;
	}
	return false;
}

/**
 * Устанавливает верхюю границу ввода дат 
 * анализируется css селектор:
 * date_max_limit_ЗНАЧЕНИЕ_ГОД_ЗНАЧЕНИЕ_МЕСЯЦ_ЗНАЧЕНИЕ_ДЕНЬ - задает минимально возможную дату
 * @return Bool 
 * */
CCalendarInput.prototype.parseMaxDate = function() {	
	var p  = /.*date_max_limit_([0-9]{4}_[0-9]{2}_[0-9]{2}).*/g
	var date = this.checkPattern(p);	
	if (!date) return false;
	
	date = date.replace(/_/g, "-");
	if (this.validDate(date, 1)) {
		var a = date.split("-"); 
		this.MAX_YEAR_LIMIT = a[0];
		this.MAX_MONTH      = this.toNumber(a[1]);
		this.MAX_DAY        = this.toNumber(a[2]);
		return true;
	}
	return false;
}



/**
 * Устанавливает нижние границы месяца и года
 * анализируется css селектор:
 * date_min_limit_ЗНАЧЕНИЕ_ГОД_ЗНАЧЕНИЕ_МЕСЯЦ_ЗНАЧЕНИЕ- задает минимально возможные год и месяц
 * @return Bool  - false если не удалось распарсить селектор  и true в противном случае
 * */
CCalendarInput.prototype.parseMinYearAndMonth = function() {
	var p  = /.*year_month_min_limit_([0-9]{4}_[0-9]{2})\s?.*/g
	var date = this.checkPattern(p, 0);	
	if (!date) return false;	
	date = date.replace(/_/g, "-") + "-" + this.nToStr(this.MIN_DAY);
	if (this.validDate(date, 1)) {
		var a = date.split("-"); 
		this.MIN_YEAR_LIMIT = a[0];
		this.MIN_MONTH      = this.toNumber(a[1]);		
		return true;
	}
	return false;
}


/**
 * Устанавливает верхние границы месяца и года
 * анализируется css селектор:
 *  year_month_max_limit_ЗНАЧЕНИЕ_ГОД_ЗНАЧЕНИЕ_МЕСЯЦ - задает максимально возможные год и месяц
 * @return Bool  - false если не удалось распарсить селектор  и true в противном случае
 * */
CCalendarInput.prototype.parseMaxYearAndMonth = function() {
	var p  = /.*year_month_max_limit_([0-9]{4}_[0-9]{2})\s?.*/g
	var date = this.checkPattern(p);	
	if (!date) return false;	
	date = date.replace(/_/g, "-");
	var a = date.split("-");
	this.qDay[2] = 28;
	if (this.leapYear(a[0])) {
		this.qDay[2] = 29;
	}
	this.MAX_DAY =  this.qDay[this.toNumber(a[1])];
	date = date + "-" + this.nToStr(this.MAX_DAY);
	if (this.validDate(date, 1)) {
		var a = date.split("-"); 
		this.MAX_YEAR_LIMIT = a[0];
		this.MAX_MONTH      = this.toNumber(a[1]);		
		return true;
	}
	return false;
}

/**
 * Устанавливает нижнюю границу года
 * анализируется css селектор:
 *  year_min_limit_ЗНАЧЕНИЕ     - задает минимально возможный год
 * @return Bool  - false если не удалось распарсить селектор  и true в противном случае
 * */
CCalendarInput.prototype.parseMinYear = function() {
	var p  = /.*year_min_limit_([0-9]{4}).*/g
	var year = this.checkPattern(p, 1);	
	if (!year) return false;
	if (this.validDate(year + "-" + this.nToStr(this.MIN_MONTH) + "-" + this.nToStr(this.MIN_DAY), 1)) {
		this.MIN_YEAR_LIMIT = year;
		return true;
	}
	return false;
}

/**
 * Устанавливает верхнюю границу года
 * анализируется css селектор:
 *  year_max_limit_ЗНАЧЕНИЕ     - задает максимально возможный год
 * @return Bool                 - false если не удалось распарсить селектор  и true в противном случае
 * */
CCalendarInput.prototype.parseMaxYear = function() {
	var p  = /.*year_max_limit_([0-9]{4}).*/g
	var year = this.checkPattern(p, 1);	
	if (!year) return false;
	if (year < this.MIN_YEAR_LIMIT) return false;
	if (this.validDate(year + "-" + this.nToStr(this.MAX_MONTH) + "-" + this.nToStr(this.MAX_DAY), 1)) {
		this.MAX_YEAR_LIMIT = year;
		return true;
	}
	return false;
}

/**
 * не работает с текстовым форматом
 * @param  String s
 * @return Bool                 - true если строка содержит символы помимо разделителя и цифр
 * */
CCalendarInput.prototype.strContentMixedDividers = function(s) {
	var re = /[0-9]\s?/gi
	s = s.replace(re, "");
	re = new RegExp("\\" + this.formatDivider, "gi");
	s = s.replace(re, "");
	if (s.length > 0) {
		return true;
	}
	return 0;
}
/**
 * 
 */
CCalendarInput.prototype.onInvalidValue = function ()  {	
	var s = this.b_input.value;
	if (!this.disallowNull||(this.disallowNull&&(s.replace(/\s/gi, "") != ''))) {
        if (s.replace(/\s/gi, "") == '') {
            this.e_date.value = '';
            this.onchangeHandler();
            return;
        }
		var iM = 0;
		for (var i = 1; i < this.months.length; i++) {
			if (this.months[i] == this.sMonthText) {			
				iM = i;
				break;
			}
		}	
		if (iM > 0) {
			var date = this.sYearText + "-" + this.nToStr(iM) + "-" +  this.nToStr(this.sDay);		
			if ((this.getCssByDate(date) == "b-calendar__day_future")||(this.usePast)) {
				this.err = 0;
				this.setDate(date, true);
				this.outerDiv.removeClass('b-combo__input_error');
				this.b_input.blur();
				this.hide();
				return;
			} else {
				this.outerDiv.addClass('b-combo__input_error');
			}
		}
	}else if (this.disallowNull&&(s.replace(/\s/gi, "") == '')){
		if (this.setCurrentDateOnNull) {
			var edate = this.cacheDate.y + "-" + this.cacheDate.m + "-" + this.cacheDate.d;
			this.setDate(edate, true);
			this.err = 0;
			this.outerDiv.removeClass('b-combo__input_error');
			this.b_input.blur();
			this.hide();
			return;
		}else {
		    this.err = 1;
		}
	}
	if (this.err)	this.outerDiv.addClass('b-combo__input_error');
	else this.outerDiv.removeClass('b-combo__input_error');
}

/**
 * Сокрытие выпадающего элемента при клике "в молоко"
 */
CCalendarInput.prototype.hide = function() {	
	try {
		this.shadow.addClass('b-shadow_hide');
		this.shadow.setProperty('style', 'z-index:100');
		this.outerDiv.getElement('.b-combo__input-text').removeClass('b-combo__input-text_color_a7');				
		if (this.err == 1) {			
			this.onInvalidValue();
		}else {
			if (!this.forceError){
				this.outerDiv.removeClass('b-combo__input_error');
			}else {
				this.outerDiv.addClass('b-combo__input_error');
				this.forceError = 0;
			}
		}
	}catch(e){}
}
/**
 * 
 */
CCalendarInput.prototype.on_focus = function() {
	if (this.self.textCursorAction) {
		this.self.textCursorAction = 0;
		return;
	}		
	if (this.self.outerDiv.hasClass("b-combo__input_disabled")) 
		return; 
	var self =  this.self;	
	self.close(self.id());
	self.outerDiv.addClass('b-combo__input_current');
	self.outerDiv.removeClass('b-combo__input_error');
	self.shadow.setProperty('style', 'z-index:30');
	self.shadow.removeClass('b-shadow_hide');	
}

/**
* 
*/
CCalendarInput.prototype.onchangeHandler = function(evt) {
	if (this.prevDateValue != this.e_date.value) {
		this.prevDateValue = this.e_date.value;
		this._onchangeHandler();
	}
}
