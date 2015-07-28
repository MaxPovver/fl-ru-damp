/**
* Зависит от:
* b-combo-dynamic-input.js
*/
//Определение класса CDropDown (будет пополняться постепенно общими методами для списков, сейчас их не могу вычленить т. к.все в значительной степени завязано на верстку)
function CDropDown(htmlDiv, cssSelectors) {
	this.init(htmlDiv, cssSelectors);
	this.DEFAULT_MAX_HEIGHT = 450;
}
CDropDown.prototype = new CDynamicInput();

CDropDown.prototype.setOrientation = function() {
    if(this.ORIENTATION == 'left') {
        var td=this.shadow.getElement('tr').getFirst('td');
        //this.shadow.setStyle('left', -td.getSize().x);
        //this.shadow.setStyle('border-left', 0);
    }
}

CDropDown.prototype.setHeight = function() {
	if (this.selectors.indexOf(" use_scroll") == -1) return;
	var h = parseInt(this.shadowBody.getStyle('height'));
	var maxH = parseInt(this.selectors.replace(/.*b_combo__input_visible_height_(\d+).*/g, '$1'));
	if (!maxH) {
		var sz = parseInt(this.selectors.replace(/.*b_combo__input_visible_items_(\d+).*/g, '$1'));
		if (sz) {
			var ls = this.columns[0].getElements('li');
			if (ls.length > 0) {
				var _h = parseInt(ls[0].getStyle('height'));
				var padT = parseInt(ls[0].getStyle('padding-top'));
				if (padT) {
					_h += padT;
				}
				var padB = parseInt(ls[0].getStyle('padding-bottom'));
				if (padB) {
					_h += padB;
				}
				if (_h) maxH = sz * _h;
			}
		}else maxH = this.DEFAULT_MAX_HEIGHT;
	}
	this.maxH = maxH;
	console.log( "maxH = " + maxH + ", h = " + h );
	if (h&&maxH) {
		if (maxH <= h) {
			this.hasScroll = 1;
			if(!this.columns[0].hasClass('b-combo__body_overflow-x_yes')) {
				this.columns[0].addClass('b-combo__body_overflow-x_yes');
			}
			if (this.checkBrowser(1) != 'msie7') {
				this.columns[0].setProperty('style', 'max-height:' + maxH + 'px');
			} else {
				this.columns[0].style.height =  maxH + 'px';
			}
		} else {
			this.columns[0].removeClass('b-combo__body_overflow-x_yes');
		}
    }
}

CDropDown.prototype.swapVisible = function() {
	this.show(this.shadow.hasClass('b-shadow_hide'));
}

CDropDown.prototype.show = function(f) {
	if(f){
            this.removeOverflowContainers();
			this.close(this.b_input.id);
			this.outerDiv.addClass('b-combo__input_current');
            try {
			    this.outerDiv.getElement('.b-combo__input-text').focus();
            } catch(e){;}
			var c = 0;
			for (var i = 0; i < this.columns.length; i++) {
				var ul = this.columns[i];
                var tag = this.outerDiv.getProperty("valueContainer");
                this.itemTag = tag = tag ? tag : "span";
                var ls = ul.getElements(tag);
                if(!ls.length) {
                    this.setDefaultValue();
                }
				if (ls.length > 0) {					
				    for (var j = 0; j < ls.length; j++) {
					    var s = ls[i].get("html");
					    s = s.replace(/&nbsp;/gi, "");
					    if (s != "") c++;
				    }
					break;
				}
			}
			if (c) {
				this.shadow.removeClass('b-shadow_hide');
				this.shadow.addClass('b-shadow_zindex_3');
			}
	}else{
		    this.resetOverflowContainers();
			this.shadow.addClass('b-shadow_hide');
			this.shadow.removeClass('b-shadow_zindex_3');
			if ((this.b_input.value != this.grayText)&&(this.grayText != '')) this.b_input.removeClass('b-combo__input-text_color_a7');			
	}
    this.setOrientation();
}
/**
* Удаляет все значения overflow:hidden у контейнеров, в которые вложена выпадашка
* нужно для совместимости с кодом тогглера фильтра в каталоге 
* см. new_site.js flt()
*/
CDropDown.prototype.removeOverflowContainers = function() {
    var o = this.outerDiv;
    if (!(this.overflowHiddenDivs instanceof Array)) {
        this.overflowHiddenDivs = new Array();
        while (o != document.getElementsByTagName('body')[0]) {
            if (o.style.overflow == "hidden") {
                this.overflowHiddenDivs.push(o);
            }
            o = o.parentNode;
        }
    }
    for (var i = 0; i < this.overflowHiddenDivs.length; i++) {
        this.overflowHiddenDivs[i].style.overflow = '';
    }
}
/**
* Восстанавливает значения overflow:hidden у контейнеров, в которые вложена выпадашка, (см removeOverflowContainers)
* нужно для совместимости с кодом тогглера фильтра в каталоге 
* см. new_site.js flt()
*/
CDropDown.prototype.resetOverflowContainers = function() {
    if (this.overflowHiddenDivs instanceof Array) {
        for (var i = 0; i < this.overflowHiddenDivs.length; i++) {
            this.overflowHiddenDivs[i].style.overflow = 'hidden';
        }
    }
}

/**
 * 
 * Конвертим если сложные ID/NAME
 * Например: 
 *      extra[0] в extra + SUFIX[0] (extra_db_id[0])
 *      extra_large[title] в extra_large[title + SUFIX] (extra_large[title_db_id])
 *      extra_large[0][title] в extra_large[0][title + SUFIX] (extra_large[0][title_db_id])
 *      
 *      
 * @param {type} id
 * @param {type} sufix
 * @returns {unresolved}
 */
CDropDown.prototype.idConv = function(id, sufix) {
    
    var REGEXP_PATTERN_ARRAY = /^([\w-]+)\[\d+\]$/g;//title[0]
    var REGEXP_PATTERN_ASOC_ARRAY = /^[\w-]+\[([\w-]+)\]$/g;//extra[title]
    var REGEXP_PATTERN_MULTI_ARRAY = /^[\w-]+\[\d+\]\[([\w-]+)\]$/g;//extra[0][title]
    var result = id + sufix;
    
    if(id.match(REGEXP_PATTERN_ARRAY) || 
       id.match(REGEXP_PATTERN_ASOC_ARRAY) || 
       id.match(REGEXP_PATTERN_MULTI_ARRAY))
    {
        result = id.replace(RegExp.$1,RegExp.$1 + sufix);
    }
    return result;
}

//Конец определения класса CDropDown

//Определение класса  CMultiLevelDropDown
/**
*  Строит html код двуколоночного (на данный момент) списка, инициализует его значениями из глобальной переменной JavaScript
*или значениями полученными ajax - запросом  в зависимости от css селекторов(см. "Руководство верстальщика")
* @param HtmlDivElement htmlDiv
* @param Array           cssSelectors
*/
function CMultiLevelDropDown(htmlDiv, cssSelectors) {
    this.initMultilevelDropDown(htmlDiv, cssSelectors);
}
CMultiLevelDropDown.prototype = new CDropDown();	//наследуемся от CDropDown
CMultiLevelDropDown.prototype.initMultilevelDropDown = function(htmlDiv, cssSelectors) {
    if (!this.defined(htmlDiv)) {
        return false;
	}
	this.init(htmlDiv, cssSelectors);
	this.b_input.autocomplete = "off";
	this.parseDefaultValues();
	this.parseExcludeValues();
	this.HOVER_CSS = 'b-combo__user_hover'; // . и .b-combo__item-inner_hover (??)
	this.FAKE_ITEM_STYLE = 'b-combo__item-inner_empty'; //стиль, по которому можно идентифицировать мнимые элементы (верстка требует не менн трех свойств в списке )
	this.MIN_COUNT_ITEMS = 3;
	this.DIVIDER = ': ';
	this.ALLOW_CREATE_VALUE = this.checkPattern(/.*allow_create_value_(\d).*/gi, 1);
    if (this.selectors.indexOf(" allow_create_value") != -1) {
        this.ALLOW_CREATE_VALUE = 1;
    }
    this.ORIENTATION = 'right';
    if(this.selectors.match(/combo__input_orientation_([a-z]+)/i)) {
       this.ORIENTATION = RegExp.$1;
    }

        //tests
        //console.log(this.idConv('exrta00-1234[110]','_db_id'));
        //console.log(this.idConv('exr-ta-1[tit-le-0]','_db_id'));
        //console.log(this.idConv('exr-ta-1[777][tit-le-0]','_db_id'));

	this.breadCrumbs = new Array();
	var id_input = $(this.idConv(this.b_input.id,"_db_id"));

	
	if (!id_input) {
		this.id_input = new Element("input", {"type":"hidden", "id": this.idConv(this.b_input.id,"_db_id"), "name": this.b_input.name?this.idConv(this.b_input.name,"_db_id") :this.idConv(this.b_input.id,"_db_id")});
		this.id_input.inject(this.outerDiv, "top");
	}else this.id_input = id_input;
	
	var column_id_input = $(this.idConv(this.b_input.id,"_column_id"));
	
	if (!column_id_input) {
		this.columnId_input = new Element("input", {"type":"hidden", "id": this.idConv(this.b_input.id,"_column_id"), "name": this.b_input.name?this.idConv(this.b_input.name,"_column_id"):this.idConv(this.b_input.id,"_column_id")});
		this.columnId_input.inject(this.outerDiv, "top");
	}else this.columnId_input = column_id_input;
	
	var s = this.selectors;
    
    this.sortType = false;//Сортировка поумолчанию по ID
    this.SORTTYPE_ABC = 'abc'; 
    this.SORTTYPE_CNT = 'cnt';
    
    if (s.indexOf(" sort_abc") != -1) {
        this.sortType = this.SORTTYPE_ABC;
    } else if(s.indexOf(" sort_cnt") != -1) {
        this.sortType = this.SORTTYPE_CNT;
    }
    
    
	this.noBlurOnEnter = 0;
	if (s.indexOf(" noblur_onenter") != -1) this.noBlurOnEnter = 1;
	this.greenArrowCss = "b-combo__item_active";
	/*if (s.indexOf(" green_arrow_off") != -1) */this.greenCss = "b-combo__item_current";
	var id = s.replace(/.*drop_down_default_(_?\d+).*/g, '$1');
	var re = new RegExp("_\\d+");
	if (re.test(id) ) {		
		id = id.replace("_", "-");
		id = parseInt(id);
	}
	if (id) this.id_input.value = id;
	if ((id === "0")||(id === 0)) this.id_input.value = id;
	
	var cid = parseInt(s.replace(/.*multi_drop_down_default_column_(\d+).*/g, '$1'));
	if (cid) this.columnId_input.value = cid;
		else this.columnId_input.value = 0; 
	
	this.buildShadow(htmlDiv.getParent('.b-combo'));
	this.buildTable();
	var s = cssSelectors.join(' ');
	this.QUANTITY_OUTPUT_RECORDS = 29;
	var numRec =  this.checkPattern(/.*quantity_output_records_(\d+).*/g);
	this.QUANTITY_OUTPUT_RECORDS = (numRec?numRec:this.QUANTITY_OUTPUT_RECORDS);
	if (s.indexOf(" show_all_records") != -1) this.QUANTITY_OUTPUT_RECORDS = 10000000;
	var initVarName = s.replace(/.*b_combo__input_init_(\w+).*/g, '$1');
    this.initVarName = initVarName;
	var data = window[initVarName];
	if(!data && window.PageOptions) {
	    data = PageOptions[initVarName];
	}
    
    if(this.columnId_input.value == 0 && id && data && typeof( data[id] ) != 'object' && !(data[id] instanceof Array) && data[id] != undefined) {
        var _el = new Element('div', {
            html: data[id].replace('&laquo;', '«').replace('&raquo;', '»') + ( this.labels[1] && String(this.labels[1].text).length ? this.DIVIDER  + this.labels[1].text : '' )
        }); 
        this.b_input.value = _el.get('text');
    }
    
    //@todo: вроде не используется
	//this.sortOff = 0;
    
	this.setEventListeners();
	var ajaxVarName = s.replace(/.*b_combo__input_on_load_request_id_([\w\d\?=&]+).*/g, '$1');
	//инициализуем единицы измерения
    this.oneResult =  ["города", "города"];
    this.lessFiveResult = ["города", "города"];
    this.greatThanFiveResult = ["городов", "городов"];
    	
	if ((ajaxVarName.indexOf("country") != -1) || (ajaxVarName.indexOf("countries") != -1) || (initVarName == 'citiesList')) {
        this.oneResult =  ["страны", "города"];
		this.lessFiveResult = ["стран", "города"];
		this.greatThanFiveResult = ["стран", "городов"];
		this.isCitiesList = 1;
    }
    if (ajaxVarName.indexOf("professionsList") != -1) {
        this.oneResult =  ["категория", "профессия"];
		this.lessFiveResult = ["категории", "профессии"];
		this.greatThanFiveResult = ["категорий", "профессий"];
		this.isCitiesList = 1;
    }

    // для истории личного счета
    if (initVarName == 'eventsList') {
        this.oneResult =  ["категории", "категории"];
        this.lessFiveResult = ["категорий", "категорий"];
        this.greatThanFiveResult = ["категорий", "категорий"];
        this.isCitiesList = 0;
    }

    var oneResult = this.b_input.getAttribute("one_result_suffix");
    if (oneResult && (oneResult.length > 0)) {
	    var words = oneResult.split(";");
	    if (words.length == 1) {
            words.push(words[0]);
		}
        this.oneResult = words;
    }
    var lessFiveResult = this.b_input.getAttribute("less_five_suffix");
    if (lessFiveResult && (lessFiveResult.length > 0)) {
        var words = oneResult.split(";");
        if (words.length == 1) {
            words.push(words[0]);
		}
        this.lessFiveResult = words;
    }
    var greatThanFiveResult = this.b_input.getAttribute("great_than_five_suffix");
    if (greatThanFiveResult && (greatThanFiveResult.length > 0)) {
	    var words = oneResult.split(";");
	    if (words.length == 1) {
            words.push(words[0]);
        }
        this.greatThanFiveResult = words;
    }
    this.ajaxOnClick = s.replace(/.*b_combo__input_on_click_request_id_(\w+).*/g, '$1');
    if (this.ajaxOnClick.test(/\s+/gi)) {
        this.ajaxOnClick = false;
    }
    this.requestLog = new Array();
    this.onchangeHandler = function(){;}
    if (this.b_input.onchange instanceof Function) {        
        this.onchangeHandler = this.id_input.onchange = this.b_input.onchange;
        this.b_input.onchange = null;
    }
	if (this.defined(data)) {
        this.read(data, 0);        
        this.selectItems();
        if (!this.requestSent) {
            this.selectRightItem();
		}
        this.show(0);
        this.b_input.blur();
	}else {
        if (!this.tryCopyColumnFromOtherComboBox()) {
		    var vars = "";
		    if (ajaxVarName.indexOf("?") != -1) {
			    var arr = ajaxVarName.split("?");
			    ajaxVarName = arr[0];
			    vars = arr[1];
		    }        
		    this.post(ajaxVarName, this.onData, this.onFailInitData, vars);
		}
	}
}
/**
* после того как данные списка загружены, устанавливает необходимые слушатели событий
*/
CMultiLevelDropDown.prototype.setEventListeners = function() {
	var toggler = this.outerDiv.getElement('.b-combo__arrow');
	if (!toggler) toggler = this.outerDiv;
	toggler.self = this;
	toggler.addEvent('click', this.onToggle);
	this.b_input.addEvent("focus", function () {
		var self = this.self;
		self.clearIfValueNotFound();
		if (self.outerDiv.hasClass("b-combo__input_disabled")) {
			self.b_input.blur(); 
			return;
			} 
		self.outerDiv.removeClass("b-combo__input_error");
		self.setHeight();
		self.show(1);
		self.checkDivider();
		} );
    this.b_input.addEvent("blur", function ()  {
        var self = this.self;
        if ( self.outerDiv.hasClass("b-combo__input_disabled") ) {
            return;
        }
        if (self.b_input.value == "" && !self.isEmpty() && (!self.ALLOW_CREATE_VALUE || self.selectors.indexOf(" disallow_null") != -1)) {
            self.err =1;
        }
    });
	this.b_input.self = this;
	this.b_input.addEvent("keyup", this.onKeyUp); 
	toggler.self = this;	
}
/**
*
*/
CMultiLevelDropDown.prototype.onKeyUp = function(evt) {
	var self   = this.self;
	if (!self) self = this;	
	if (self.outerDiv.hasClass("b-combo__input_disabled")) {
		self.b_input.blur();
		return;
	}	
    if ((evt.code == 38)||(evt.code == 40)) {
		self.onArrowsKey(evt.code);
        return;
    }    
	if (evt.code == 13) {
		self.onEnter(evt.code);
        return;
    }
	if (self.b_input.readOnly) {
	   return;
	}
	if ((this.ctrl)&&(evt.code == 65)) {		
		return;
	}
	this.prevKey = 	evt.code;
	if (evt.code == 17) {		
		this.ctrl = 0; 
		return;
	}
	if ( (evt.code == 37) ||(evt.code ==  39)||(evt.code ==  16)||(evt.code ==  35)||(evt.code ==  36)||(evt.code ==  9)) {
		return;
	}
    self.lastKey = evt.code;
	var found  = 0;
	var n = self.getNumberOfColumnForSearch();
	var v = self.b_input.value;
	v = v.toLowerCase();	
	found = self.fillColumn(v, n);
    if(found == 0 && self.id_input.value == 0) {
        self.outerDiv.addClass('b-combo__input_error');
    }
	self.show(found);
}
/**
*
*/
CMultiLevelDropDown.prototype.fillColumn = function(value, n, dbg) {
	if (n < 0)  return 0;	
	this.err = 1;
	var v = value.toLowerCase();
	var a = v.split(this.DIVIDER);
	v = a[n];	
	var re = /\s.?$/g;
	v = v.replace(re, "");
	while (v.indexOf(this.DIVIDER) != -1) v = v.replace(this.DIVIDER, "");
	var badDiv = 0;
	while (v.indexOf(":") != -1) {
		v = v.replace(":", "");
		badDiv = 1;
	}
	var found = 0;
	this.clear(n);
	if (badDiv) {
		try {
			this.clear(n + 1);
		}catch(e) {
		}
	}
	if ((v == "")&&(n == 0)&&(value != '')) {
	    this.clear(n);
	    try {
			this.clear(n + 1);
		}catch(e) {
		}
		return;
	}
	if (this.columnsCache[n] instanceof Array) {
		var arr = this.columnsCache[n];
		var countAppend = 0;
		var more = 0;
		var parentId = this.breadCrumbs[n - 1];			
		if (!parentId) parentId = 0;		
		for (var i = 0; i < arr.length; i++) {
			var currentParentId = arr[i].prid;
			if (currentParentId == parentId) {				
				var s = arr[i].text;
				if (!s) s = '';
				s = s.toLowerCase();
				if ((s.indexOf(v) === 0)) {
					if (!found) {					
						found = 1;
					}
					var append = 1;
					if (countAppend >= this.QUANTITY_OUTPUT_RECORDS) append = 0;
					//index, text, column, parentId, nocache, append, clickable
					if (append) {						
						if (!this.ALLOW_CREATE_VALUE && !countAppend) {
							this.addItem(arr[i].id, arr[i].text, n, arr[i].prid, 1, 1, 1, this.HOVER_CSS);
						}else {
							this.addItem(arr[i].id, arr[i].text, n, arr[i].prid, 1);
						}						
						countAppend++;
					}else more++;
					if (s === v) {
						this.emulateClick(arr[i].id, n);
					}else {
						for (var k = n + 1; k < this.columns.length; k++)
							this.clear(k);
					}
				}
			}
		}
					
		this.found = found;
		if (found) {				
			if (more) {
				var totalCount = more + countAppend;                
				this.addItem(-1, this.getMoreString(countAppend, totalCount, n), n, -1, 1, 1, 0, this.FAKE_ITEM_STYLE);
			}
			this.addFakeItems(n);
		}	
	}
    if (!found) {
        for (var i = 0; i < this.columns.length; i++) {
           this.clear(i);
        }
    }

	return found;
}
/**
*@param int id - идентификатор записи в базе данных
*@param int n  - номер столбца
*/
CMultiLevelDropDown.prototype.emulateClick = function(id, n) {
	var ul = this.columns[n];
	if (!ul) return;	
	var ls = ul.getElements("span");	
	var found = 0;
	for (var i = 0; i < ls.length; i++) {
		var dbid = ls[i].get("dbid");		
		if (dbid == id) {			
			ls[i].self = this;
			this.onItemClick(ls[i], 1);
			this.outerDiv.removeClass('b-combo__input_error');
			found = 1;
			break;
		}
		if (found) break;
	}
}
/**
* слушатели событий
*/
CMultiLevelDropDown.prototype.onToggle = function() {
	var self = this.self;
	if (self.outerDiv.hasClass("b-combo__input_disabled")) return;
	self.setHeight();
    self.swapVisible();
	self.checkDivider();
}


/**
 * проверка поддержки аргументов locales и options
 * @returns {Boolean}
 */
CMultiLevelDropDown.prototype.localeCompareSupportsLocales = function() 
{
  try {
    'a'.localeCompare('b', 'i');
  } catch (e) {
    return e.name === 'RangeError';
  }
  
  return false;
};



/**
*@param Object data         - многоуровневый массив ключ:значение.
*@param unsigned int  level - уровень вложенности. 
* значение может быть строкой или аналогичным объектом
*/
CMultiLevelDropDown.prototype.read = function (data, level, parentItem) {
	var length = 0;
	/*Особенность google chrome: если происходит итерация по объекту , поля при этом упорядочиваются по возрастанию по ключам, причем -1 как строка считается больше чем 0, 1  и т д*/
	var keys = new Array();

    //Первым заполняем родителя
    if (typeof data['0'] !== "undefined") {
        keys.push('0');
    }

    //Далее пункт "Все специалиации"
    if (typeof data['undefined_value'] !== "undefined") {
        keys.push('undefined_value');
    }


    if (this.sortType === this.SORTTYPE_ABC) {
    //Сортировка по алфавиту
        var _keys = new Array();
        var _loc = this.localeCompareSupportsLocales();

        for (var i in data) {
            _keys.push({index:i, value:data[i]});
        }

        switch (typeof data[Object.keys(data)[0]]) {
            case "string":
                _keys.sort(function(a, b) { 
                    return (_loc)?
                            a.value.toLowerCase().localeCompare(b.value.toLowerCase(), ['en','ru']):
                            a.value.toLowerCase().localeCompare(b.value.toLowerCase());
                });
                break;

            case "object":
                _keys.sort(function(a, b) { 
                    return (_loc)?
                            a.value[0].toLowerCase().localeCompare(b.value[0].toLowerCase(), ['en','ru']):
                            a.value[0].toLowerCase().localeCompare(b.value[0].toLowerCase());
                });            
                break;
        }

        //Далее все остальные за исключением описанных выше
        for (var i = 0; i < _keys.length; i++) {
            length++;
            var idx = _keys[i].index;

            if (idx === 'undefined_value' || idx === '0') {
                continue;
            }

            keys.push(idx);
        }
    
    } else if (this.sortType === this.SORTTYPE_CNT) {
    //Сортировка по доппараметру количество пользователей в категории
        var _keys = new Array();
        var is_top_cat = typeof data[Object.keys(data)[0]].length === 'undefined';
        
        for (var i in data) {
            _keys.push({
                index:i, //ID категории
                title:(is_top_cat)?data[i][0][0]:data[i][0], //заголовок 
                cnt: parseInt((is_top_cat)?data[i][0][1]:data[i][1]) //кол-во пользователей
            });
        }

        _keys.sort(function(a,b){return b.cnt-a.cnt;});

        //Далее все остальные за исключением описанных выше
        for (var i = 0; i < _keys.length; i++) {
            length++;
            var idx = _keys[i].index;

            if (idx === 'undefined_value' || idx === '0') {
                continue;
            }

            keys.push(idx);
        }
        
    } else {    
    //Сортирока поумолчанию по ID    
        
        
        //Далее все остальные за исключением описанных выше
        for (var i in data) {
            length++;

            if (i === 'undefined_value' || i === '0') {
                continue;
            }

            keys.push(i);
        }
    }
   
    // расставляет элементы списка в обратном порядке если в комбобоксе присутствует селектор reverse_list
    if (this.selectors.indexOf("reverse_list") !== -1) {
        keys.reverse();
    }
    
	var c = 0;
	var m = 0; //если не 0, значит записей в колонке больше чем надо показывать
	if (String(parentItem) == "undefined") parentItem = 0;
	var _pItem = parentItem;
	for (var j = 0; j < keys.length; j++) {
		var i = keys[j];
		if (typeof data[i] === 'string' || 
            (typeof data[i].length !== 'undefined' && data[i].length === 2)) {
            
            var txt = (typeof data[i].length !== 'undefined' && data[i].length === 2)?data[i][0]:data[i];
			var index = i;
			var lvl = level;
            if ((level > 0)&&(c == 0))  {
                lvl--;
                index = parentItem;
                parentItem = i;
                var num = this.columns[0].getElements("li").length;
                if (num >= this.QUANTITY_OUTPUT_RECORDS) {
                    this.addItem(index, txt, lvl, parentItem, 0, 0);
                }else {
                    this.addItem(index, txt, lvl, parentItem);
                }
            } else {
                if ((level > 0)&&(c > 0)) parentItem = _pItem;
                
                             //(index, text, column, parentId, nocache = 0, append = 1, clickable = 1)            
                if (c >= this.QUANTITY_OUTPUT_RECORDS) {
                    this.addItem(index, txt, lvl, parentItem, 0, 0);                    
                } else {
                    this.addItem(index, txt, lvl, parentItem);
                }
            }
		} else {
			this.read(data[i], level + 1, i);
		}
		if (c >= this.QUANTITY_OUTPUT_RECORDS) {		   
            if (m == 0) m = c;
        }
		c++;		
	}
	
	if (m >= this.QUANTITY_OUTPUT_RECORDS) {
		if (m < length ) {
			var n = length - m;
			//(index, text, column, parentId, nocache = 0, append = 1, clickable = 1)
            this.addItem(-1,  this.getMoreString(m, length, level), level, parentItem, 1, 1, 0, this.FAKE_ITEM_STYLE);
		}
    }    
}
/**
*@param int index              - идентификатор элемента из таблицы БД
*@param String       value     - отображаемое значение
*@param unsigned int column    - уровень вложенности. 
*@param int          parentId  - идентификатор записи-родителя в таблице БД.
*@param Bool         nocache    = false   - кешировать ли в this.columnsCache.
*@param Bool         append     = true    - добавлять ли в список.
*@param Bool         clickable  = true    - добавлять ли атрибут onclick.
*@param String       extendsSelectors  = '' если аргумент не пуст, будут добавлены в атрибут class
*/
CMultiLevelDropDown.prototype.addItem = function (index, text, column, parentId, nocache, append, clickable, extendsSelectors) {
    index = parseInt(index);
    var undefined_value = false;
    if (!index) {
        index = 0;
        undefined_value = true;
    }
    if (String(append) == "undefined")    append = 1;
	if (String(clickable) == "undefined") clickable = 1;
	if (this.labels[column] && clickable) {
        if (this.labels[column].id  == index) {
            text = this.labels[column].text;
        }
    }
    if (this.exclude[column]) {
        if (this.exclude[column].id == index) return;
    }
	if (!extendsSelectors) extendsSelectors = '';
		else extendsSelectors = ' ' + extendsSelectors;
	var ul = this.columns[column];
	if (!ul) { 		
		this.appendColumn(this.defaultColumnCss, this.row);
		ul = this.columns[column];
	}
	var td = ul.parentNode;
	td.style.display = "";
	
	if (nocache != 1) {
		if ( undefined_value ) {
            var a = [];
            a.push( {id:index, text:text, prid:parentId} );
            for (var i = 0; i < this.columnsCache[column].length; i++) {
                a.push(this.columnsCache[column][i]);
            }
            this.columnsCache[column] = a;
        } else {
            this.columnsCache[column].push({id:index, text:text, prid:parentId});
        }
	}
	
	if (append) {		
		var li = new Element('li', {'class':'b-combo__item'});
		li.inject(ul, 'bottom');
		var span = new Element('span', {'class':'b-combo__item-inner' + extendsSelectors, 'html':text});
		span.inject(li, 'top');
		if (extendsSelectors.indexOf(this.HOVER_CSS) != -1) {
			var w = this.outerDiv.getStyle("width");
			if (w) {
			    span.setProperty("style", "min-width:" + w);
			}
		}
		span.setProperty('dbid' , index);
		if (parentId) span.setProperty('dbprid' , parentId);
		span.self = this;
		if (clickable) {
			span.addEvent('click', this.onItemClick);
			span.addEvent('mouseover', this.onItemHover);
			span.setProperty("style", "cursor:pointer");
		}else {
            span.setProperty("style", "cursor:default");
        }
	}
}
/**
 *@param Bool flag определяет, вызван ли обработчик принудительно при наборе текста
* listener (callback)
*/
CMultiLevelDropDown.prototype.onItemClick = function (span, flag) {		
	var self = this.self;
	if (flag) {
		self = this;
	}else {
		span = this;
	}	
	self.outerDiv.removeClass('b-combo__input_error');
	self.err = 0;
	
	//определение номера колонки
	var li = span.getParent('li');
	var ul = li.getParent('ul');
	ul.getElements("li").removeClass(self.greenCss);
	var columnIndex = 0; //колонка по которой кликнули
	for (var j = 0; j < self.columns.length; j++) {
		if (ul == self.columns[j]) {			
			columnIndex = j;
			break;
		}
	}
	//строка для подстановки в инпут
	var s = '';	
	for (var k = 0; k < columnIndex; k++) {
		var column = self.columns[k];
		var ls = column.getElements("li");
		for (var m = 0; m < ls.length; m++) {
			if ((ls[m].className.indexOf(self.greenCss) != -1)||(ls[m].className.indexOf(self.greenArrowCss) != -1)) {
				var lsp = ls[m].getElements("span");
				sp = lsp[0];
				s += sp.get("text") + self.DIVIDER;				
				break;
			}
		}
	}	
	//очистка элементов правее кликнутого
	for (var k = columnIndex + 1; k < self.columns.length; k++) {
        self.clear(k);
	}
	//установка значения	
	self.b_input.value = s + span.get('text');
	self.resize(self.b_input);
	self.id_input.value = span.getProperty('dbid');		
	self.columnId_input.set("value", columnIndex);
    self.breadCrumbs[columnIndex] = self.id_input.value;
    for (var i = columnIndex + 1; i < self.breadCrumbs.length; i++) {
        self.breadCrumbs[i] = 0;
    }
    self.breadCrumbsToInput();
	
	ul.getElements('li').removeClass('item ' + self.greenCss);
	ul.getElements('li').removeClass('item ' + self.greenArrowCss);
	li.addClass('item ' + self.greenCss);
	//проверка, есть ли правый столбец
	var right = 0;
	var nColumn = 0; //колонка cправа от той, по которой кликнули
	for (var i = 0; i < self.columns.length; i++) {
		if (ul === self.columns[i]) {
			right = self.columns[i + 1];
			nColumn = i + 1;
			break;
		}
	}

	if (right) {
		right.getParent('.b-layout__right').removeClass('b-layout__right_hide');	
		self.clear(nColumn);
		//try get values from cache
		var id = span.getProperty('dbid');
		var L = 0;
		var more = 0;
		var hasScroll = 0;		
		for (var i = 0; i < self.columnsCache[nColumn].length; i++) {
			if (self.columnsCache[nColumn][i].prid == id) {
				if (!self.ALLOW_CREATE_VALUE && !L) {
					self.addItem(self.columnsCache[nColumn][i].id, self.columnsCache[nColumn][i].text, nColumn, self.columnsCache[nColumn][i].prid, 1, 1, 1, self.HOVER_CSS);
				}else {
					if (L < self.QUANTITY_OUTPUT_RECORDS) {
					    self.addItem(self.columnsCache[nColumn][i].id, self.columnsCache[nColumn][i].text, nColumn, self.columnsCache[nColumn][i].prid, 1);
					}
				}
				if (!hasScroll) hasScroll = self.setScrollbar(j, self.columns[nColumn]);
				L++;
				if (L > self.QUANTITY_OUTPUT_RECORDS) {
                    more++;	
				}
			}
		}
		if (L > 0) {
			li.removeClass('item ' + self.greenCss);
			li.addClass('item ' + self.greenArrowCss);
			self.b_input.value += self.DIVIDER;
			if (more) {
				//index, text, column, parentId, nocache, append, clickable, extendsSelectors
				var diff = L - more;
				self.addItem(-1, self.getMoreString(diff, L, nColumn), nColumn, -1, 1, 1, 0, self.FAKE_ITEM_STYLE);
				if (!hasScroll) hasScroll = self.setScrollbar(L, self.columns[nColumn]);				
			}
			self.addFakeItems(nColumn);
            if (!self.hideOnload) {
                self.setFocus();
            }
		}
		//cache is empty
		if (L == 0) {			
			var id = span.getProperty('dbid');
			//onclick is defined and request was not sent early
			if (self.ajaxOnClick&&self.requestLog[id] != 1 && self.requestSent != 1) {				
				self.requestLogId = id;
				self.columnId = nColumn;
				self.requestSent = 1;
				self.post(self.ajaxOnClick, self.onChildData, self.onFailChildData, "id=" + id);
			}else {				
				self.show(0);
				if (self.noBlurOnEnter == 0)self.b_input.blur();
			}
		}
		self.setScrollbar(L, right);
	}else {
		self.show(0);
		if (self.noBlurOnEnter == 0) self.b_input.blur();
	}	
	try {
        self._onChangeHandler();
    }catch (e) {
        ;
    }

    self.setOrientation();

    self.b_input.removeClass("b-combo__input-text_color_67");
	//if ((columnIndex == self.columns.length - 1)&&(!flag)) self.show(0);
	return false;
}

/**
* Строим таблицу, в которой будут располагаться колонки списка
*/
CMultiLevelDropDown.prototype.buildTable = function() {
 var p = this.extendElementPlace;
 var t = new Element('table', {'class':'b-layout__table b-layout__table_width_full', 'border':0, 'cellpadding':0, 'cellspacing':0});
 t.inject(p, 'top');
 var tb = new Element('tbody');
 tb.inject(t, 'top');
 var row = new Element('tr', {'class':'b-layout__tr'});
 row.inject(tb, 'top');
 this.row = row;
 if(this.ORIENTATION == 'left') {
     this.createColumns(row, new Array('b-layout__left b-layout__right_bordleft_cdd1d3', 'b-layout__right b-layout__right_hide'));
 } else {
     this.createColumns(row, new Array('b-layout__left', 'b-layout__right b-layout__right_bordleft_cdd1d3 b-layout__right_hide'));
 }
 
 this.defaultColumnCss = 'b-layout__right b-layout__right_bordleft_cdd1d3 b-layout__right_hide';
 
 var spans = this.outerDiv.getElements('span.b-combo__arrow');
 
 var span = 0;
 
 if (spans.length > 0) {
	span = spans[0];
 }
 
 if (span) span = span.hasClass("b-combo__arrow");
  if (!span){ 
   span = new Element("span", {"class":"b-combo__arrow"});
   span.inject(this.outerDiv, "bottom");
 } 
 
}
/**
* Создаем колонки списка
*@param HtmlRowElement parent -  ряд, в который будут добавлены столбцы списка
*@param Array classes -  массив CSS селекторов (один элемент может содержать несколько css классов в виде обычной строки)
*Судя по всему, для каждой новой колонки (в случае если она вообще понадобится) скорее всего будет свой набор селекторов, поэтому пока так.
*/
CMultiLevelDropDown.prototype.createColumns = function(parent, classes) {
	this.columns      = new Array();
	this.columnsCache = new Array();
	for (var i = 0; i < classes.length; i++) {
		this.appendColumn(classes[i], parent);		
	}
}


CMultiLevelDropDown.prototype.appendColumn = function(className, parent) {
	var cell = new Element('td', {'class':className});
	cell.inject(parent, this.ORIENTATION=='left' ? 'top' : 'bottom');
	var ul = new Element('ul', {'class':'b-combo__list'});
	ul.inject(cell, 'bottom');
	this.columns.push(ul);
	this.columnsCache.push(new Array());
	this.breadCrumbs.push(-1);
}

/**
* Создаем колонки списка
*@param HtmlRowElement parent -  ряд, в который будут добавлены столбцы списка
*@param Array classes -  массив CSS селекторов (один элемент может содержать несколько css классов в виде обычной строки)
*Судя по всему, для каждой новой колонки (в случае если она вообще понадобится) скорее всего будет свой набор селекторов, поэтому пока так.
*/
CMultiLevelDropDown.prototype.onData = function(data) {
	this.self.read(data, 0);	
	this.self.selectItems();
}
/**
*Обработка ошибки загрузки данных многоколоночного списка
*/
CMultiLevelDropDown.prototype.onFailInitData = function(data) {
}
/**
*Обработка загрузки данных столбца 
*/
CMultiLevelDropDown.prototype.onChildData = function(data) {
	var parentId = false;
	var items    = null;
	var j = 0;
	var self   = this.self;
	if (!self) self = this;
    self.requestSent = 0;
	var columnId = self.columnId;	
	if (columnId) columnId = 1;	
    if (!self.ignoreDivider) {
        self.ignoreDivider = 0;
    }
    if (!self.hideOnload) {
        self.hideOnload = 0;
    }
    var hideOnLoad = self.hideOnload;
	if (self.ignoreDivider) columnId = 0;
	if (self.checkBrowser(1) != "msie7") {
		for (var i in data) {		
			if (j == 0) parentId = data[i].parentId;
			if (j == 1) items   = data[i];
			j++;
		}
	}else {
		parentId = data[0].parentId;
		items    = data[1];		
	}
	j = 0;
	var more = 0;
	var hasScroll = 0;		
	if (parentId && items) {
		var length = 0;
		for (var i in items) {
			if (!(items[i] instanceof Object)) length++;
		}
		var countAppend = 0;
		for (var i in items) {
			if (!(items[i] instanceof Object)) {
				if (self.labels[columnId]) {					
					if (self.labels[columnId].id == i) items[i] = self.labels[columnId].text;
				}
				if (self.exclude[columnId]) {
					if (self.exclude[columnId].id == i) continue;
				}
				if (j >= self.QUANTITY_OUTPUT_RECORDS) {
					this.self.addItem(i, items[i], columnId, parentId, 0, 0);
					more++;
				} else {					
					if (!this.self.ALLOW_CREATE_VALUE && !countAppend) {												
						//index, text, column, parentId, nocache, append, clickable, extends
						this.self.addItem(i, items[i], columnId, parentId, 0, 1, 1, this.self.HOVER_CSS);
					}else {						
						
						//index, text, column, parentId, nocache, append, clickable, extends
						this.self.addItem(i, items[i], columnId, parentId);
					}
					if (!hasScroll) hasScroll = this.self.setScrollbar(j, this.self.columns[columnId]);
					countAppend++;
				}
				j++;
			}
		}		
	}
	if (j > 0) {
        if (parentId&&(columnId - 1 > -1)) {
            var ls = self.columns[columnId - 1].getElements(".b-combo__item");
            for (var i = 0; i < ls.length; i++) {
                if (ls[i].hasClass(self.greenCss)) {
				    ls[i].removeClass(self.greenCss);
				    ls[i].addClass(self.greenArrowCss);
				    break;
				}
		    }
        }
		if (self.ignoreDivider == 0 && self.hideOnload == 0) {
			self.b_input.value += self.DIVIDER;
		}
		if (more) {	
			var totalCount = countAppend + more;
			this.self.addItem(-1, this.self.getMoreString(countAppend, totalCount, columnId), columnId, parentId, 1, 1, 0, this.self.FAKE_ITEM_STYLE);
			if (!hasScroll) hasScroll = this.self.setScrollbar(j, this.self.columns[columnId]);
		}
		if (!self.hideOnload) this.self.setFocus();
	}else {		
		this.self.show(0);		
		this.self.b_input.blur();
	}
	if (parentId) this.self.requestLog[parentId] = 1;
	this.self.setScrollbar(j, this.self.columns[1]);
	if (self.ignoreDivider) self.ignoreDivider = false;
	if (self.hideOnload) {
		self.hideOnload = 0;
		self.show(0);
		if (self.labels[0]) {
		    if (self.labels[0].text&&self.b_input.value == '') self.b_input.value = self.labels[0].text;
		}
		self.b_input.blur();
	}
    self.addFakeItems(columnId);
    //выделяем активный
    var f = self.selectRightItem();
    if (!f) {
        self.onKeyUp({code:0});
        if (hideOnLoad) {
            self.show(0);
            self.b_input.blur();
            self.selectRightItem();
        }
    }
    this.self.setOrientation();
}
/**
 * @param String requestId
 * @param int    parentId
 * @param Bool   hideOnload     - если true, список не раскрывается после загрузки. По умолчанию false
 * @param String args      - Если строка не пуста, добавляется к запросу id=parentId&args. По умолчанию ''
 * @param Bool   clearTextField - если true то очищаются не только элементы списка но и текстовое поле. По умолчанию true
 * */
CMultiLevelDropDown.prototype.loadData = function(requestId, parentId, hideOnload, args, clearTextField) {
    if (String(clearTextField) == "undefined") {
         clearTextField = true;
    } else {
         if (!clearTextField) clearTextField = false;
          else clearTextField = true;
	}
	for (var i = 0; i < this.columns.length; i++) this.clear(i, clearTextField);
	this.breadCrumbs[-1] = parentId;
	if ((this.ignoreDivider != 1)&&(this.requestLog[parentId] != 1)) {
		if (hideOnload == 1) this.hideOnload = 1;
		this.requestLogId = parentId;
		this.columnId = 0;
		this.ignoreDivider = 1;			
		var data = "id=" + parentId;
		if (args&&(args instanceof String)) data += "&" + args;
		this.post(requestId, this.onChildData, this.onFailChildData, data);
	}else {
		var L = 0;
		var more = 0;
		var hasScroll = 0;
		var n = 0;
		for (var i = 0; i < this.columnsCache[n].length; i++) {			
			if (this.columnsCache[n][i].prid == parentId) {
				if (!this.ALLOW_CREATE_VALUE && !L) {
					this.addItem(this.columnsCache[n][i].id, this.columnsCache[n][i].text, n, this.columnsCache[n][i].prid, 1, 1, 1, this.HOVER_CSS);
				}else {
					if (L < this.QUANTITY_OUTPUT_RECORDS) {
                        this.addItem(this.columnsCache[n][i].id, this.columnsCache[n][i].text, n, this.columnsCache[n][i].prid, 1);
					}
				}
				if (!hasScroll) hasScroll = this.setScrollbar(L, this.columns[n]);
				L++;
				if (L > this.QUANTITY_OUTPUT_RECORDS) {
					more++;	
				}
			}
	
	
		}
		if (L > 0) {
			if (more) {
				var diff = L - more;
				//index, text, column, parentId, nocache, append, clickable, extendsSelectors
				this.addItem(-1, this.getMoreString(diff, L, n), n, -1, 1, 1, 0, this.FAKE_ITEM_STYLE);
				if (!hasScroll) hasScroll = this.setScrollbar(L, this.columns[n]);
			}
			this.addFakeItems(n);
			if (!hideOnload) this.setFocus();
			  else {
			      if (this.labels[0]) {
				      if (this.labels[0].text) this.b_input.value = this.labels[0].text;
				  }
			}
		}
	}

}
/**
*Обработка ошибки загрузки данных столбца
*/
CMultiLevelDropDown.prototype.onFailChildData = function(data) {
	if (this.self.requestLogId) this.self.requestLog[this.self.requestLogId] = 1;
}
/**
*Устанавливает стиль со скролбаром в правую колонку в том случае, если он там нужен
*@param Number L             - количество элементов в колонке
*@param HtmlUlElement right  - правый столбец (список)
*@return Bool true если скролл был добавлен
*/
CMultiLevelDropDown.prototype.setScrollbar = function(L, right) {
	if (this.hasScroll&&(this.maxH <= parseInt(right.getStyle("height")))  ) {
		right.addClass('b-combo__body_overflow-x_yes');			
		if (this.checkBrowser(1) != 'msie7') right.setProperty('style', 'max-height:' + this.maxH + 'px');
				else {
					right.style.height =  this.maxH + 'px';
				}
		return 1;
	}else {
		right.removeClass('b-combo__body_overflow-x_yes');				
	}
	return 0;
}

/**
*Анализирует значение в поле b_input  и положение курсора в нем с целью определить, по какой из колонок нужно производить поиск
*@return unsigned int номер колонки или -1 если парсинг неуспешен
*/
CMultiLevelDropDown.prototype.getNumberOfColumnForSearch = function() {
	var v = this.b_input.value;
    if (v.indexOf(this.DIVIDER) == -1) {
        return 0;
    }
	var p = this.getCaretPosition();
	var arr = v.split(this.DIVIDER);
	var dL = this.DIVIDER.length;	
	var L = arr[0].length + dL;
	for (var i = 0; i < arr.length; i++) {
		if (p < L) return i;
		L += arr[i].length;
		if (i < arr.length - 1) L += dL;
	}	
	if (p < L) return (arr.length - 1);
	return 0;
}
/**
 * Очищает столбец. Для не readOnly списков  может использоваться вместо clearSelection
 * @param int  n - номер столбца
 * @param Bool clearTextInput - если 1 то очищает и текстовое поле
 * @param Bool clearTextInput - если 1 то очищает и кеш столбца
 * */
CMultiLevelDropDown.prototype.clear = function(n, clearTextInput, clearCache) {

    this.requestLog = new Array();
    this.err = 0;
	if (clearTextInput) this.b_input.value = '';
	if (clearCache) this.columnsCache[n] = new Array();
	var ul = this.columns[n];
	if (ul) {
		var ls = ul.getElementsByTagName('li');
		for (var i = ls.length; i > -1; --i) {			
			try {
				var prnt = ls[i].parentNode;
				prnt.removeChild(ls[i]);
			}catch(e){;}
		}
		var td = ul.parentNode;
		td.style.display = "none";
	}
}
/**
 * Снимает выделение с элементов списка
 * @param Bool clearTextInput = true - если true то очищает и текстовое поле 
 * */
CMultiLevelDropDown.prototype.clearSelection = function(clearTextInput) {
    if (String(clearTextInput) == "undefined") {
        clearTextInput = 1;
    }
    if (clearTextInput) this.b_input.value = '';
    for (var n = 0; n < this.columns.length; n++) {
        var ul = this.columns[n];
        if (ul) {
            var ls = ul.getElementsByTagName('li');
            for (var i = 0; i < ls.length; i++) {            
                ls[i].removeClass(this.greenCss);
                ls[i].getElements("span")[0].removeClass(this.HOVER_CSS);
            }            
        }
    }
}
/**
*@param int keyCode  
*Обработка нажатия  клавиш "вверх" и "вниз"
*/
CMultiLevelDropDown.prototype.onArrowsKey = function(keyCode) {
	var n = this.getNumberOfColumnForUpDown();
	var ul = this.columns[n];
	if (ul) {
		this.arrowsAction = 1;
        var ls = ul.getElements(this.itemTag);
		var found = 0;
		var index = 0;
		var L = 0;
		if (keyCode == 40) {
			for (var i = 0; i < ls.length; i++) {
				var sp = ls[i];				
				if (!sp.hasClass(this.FAKE_ITEM_STYLE)) {
					if (sp.hasClass(this.HOVER_CSS)) {
						found = 1;
						index = i;
					}
					sp.removeClass(this.HOVER_CSS);
					L++;
				}
			}
			if (!found) index = -1;
			index++;
			if (index >= L) index = 0;			
			ls[index].addClass(this.HOVER_CSS);
            this.changeScroll(ls.length, ls[index], index, 1);
		}
		
		if (keyCode == 38) {
			for (var i = 0; i < ls.length; i++) {
				var sp = ls[i];				
				if (!sp.hasClass(this.FAKE_ITEM_STYLE)) {
					if (sp.hasClass(this.HOVER_CSS)) {
						found = 1;
						index = i;
					}
					sp.removeClass(this.HOVER_CSS);
					L++;
				}
			}
			if (!found) index = L;
			index--;
			if (index < 0) index = L - 1;
			ls[index].addClass(this.HOVER_CSS);
            this.changeScroll(ls.length, ls[index], index, -1);
		}
	}	
}

/**
*Прокрутка полоы скрола если он показан
* @param int length - количество элементов в списке
* @param HtmlElement item - элемент, высота которого используется в качестве шага
* @param int n номер элемента в списке
* @param int direct направление прокрутки 1 | -1
*/
CMultiLevelDropDown.prototype.changeScroll = function(length, item, n, direct) {
    if ( this.outerDiv.className.indexOf(" use_scroll") != -1 ) {
        var dY = 23; //hardcode (
        if ( (direct > 0 && n > 3 )|| (direct < 0)) {
            this.columns[0].scrollTop = (n != length - 1? n * dY : this.columns[0].scrollHeight);
        }
    }
}
/**
*Получаем номер крайнего правого открытого столбца
*@return uint
*/
CMultiLevelDropDown.prototype.getNumberOfColumnForUpDown = function() {
    for (var j = this.columns.length - 1; j > -1; j--) {
		var ul = this.columns[j];
		if (ul) {
			var ls = ul.getElements("li");
			if (ls.length) return j;
		}
	}
	return -1;
}

/**
*Обработка нажатия  клавиши "Enter"
*/
CMultiLevelDropDown.prototype.onEnter = function() {    
	var n = this.getNumberOfColumnForUpDown();	
	var ul = this.columns[n];
	if (ul) {
		var ls = ul.getElements(this.itemTag);
		var found = 0;
		var index = 0;
		var L = 0;		
		var ID = this.b_input.id;
		this.userValueFlag(0);
		for (var i = 0; i < ls.length; i++) {
			var sp = ls[i];				
			if (!sp.hasClass(this.FAKE_ITEM_STYLE)) {
				if (sp.hasClass(this.HOVER_CSS)) {                    
                    this.onItemClick(sp, 1);
                    found = 1;
				}					
			}
		}
		if (!found)	{
			if (this.ALLOW_CREATE_VALUE) {
				this.userValueFlag(1);
				if (this.b_input.value.indexOf(this.DIVIDER) == -1) {
					this.b_input.value += this.DIVIDER;					
				} else {					
					if (this.noBlurOnEnter == 0) {						
						this.b_input.blur();
					}
                    try {this._onChangeHandler();} catch(e) {;}
				}
				//this.show(0);
			}
		}
	}
	return false;
}

/**
*@param bool flag
* В зависимости от flag добавляет hidden c id = name = this,b_combo.id + "_user_value" и value = 1
* или удаляет его
*/
CMultiLevelDropDown.prototype.userValueFlag = function(f) {
	var ID = this.b_input.id;
	var userValueFlag = $(ID + '_user_value');
	if (f == 0) {		
		if (userValueFlag) {
			var p = userValueFlag.parentNode;
			p.removeChild(userValueFlag);
		}
	}
	if (f == 1) {
		this.userValueFlag(0);
		userValueFlag = new Element("input", {"type":"hidden", "name":ID + '_user_value', "id":ID + '_user_value', "value": "1"});
		userValueFlag.inject(this.outerDiv, 'top');
	}
}

/**
*@param int nColumn  
*Добавляет мнимые свойства если всего их в списке очень мало (верстка позволяет не меньше трех)
*/
CMultiLevelDropDown.prototype.addFakeItems = function(nColumn) {
	var ul = this.columns[nColumn];
	if (ul) {
		var ls = ul.getElements("li");		
        if (ls.length) for (var i = ls.length; i < this.MIN_COUNT_ITEMS; i++) {
			this.addItem(-1, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", nColumn, -1, 1, 1, 0, this.FAKE_ITEM_STYLE);
		}
	}
}
/**
 * 
 */
CMultiLevelDropDown.prototype.onInvalidValue = function ()  {
    if (this.err) {
        if (!this.ALLOW_CREATE_VALUE) {//если запрещено вводить отсебятину
            for (var i = this.columns.length - 1; i > -1; i-- ) {
                this.breadCrumbs[i] = 0;
            }
            var arr = this.b_input.value.split(this.DIVIDER);
            for (var n = arr.length - 1; n > -1; n--) {
                var found = 0;
                var ls = this.columns[n].getElements("span");
                for (var i = 0; i < ls.length; i++) {
                    var sp = ls[i];
                    if (sp.hasClass(this.HOVER_CSS)) {                    
                        arr[arr.length - 1] = sp.get("html");
                        this.b_input.removeClass('b-combo__input-text_color_a7');
                        this.b_input.value = arr.join(this.DIVIDER);
                        if (n == arr.length - 1) {
                            this.id_input.set("value", sp.get("dbid"));
                            this.columnId_input.set("value", n);
                        }
                        this.breadCrumbs[n] = sp.get("dbid");
                        found = 1;
                        break;
                    }
                }
                if (found == 0) {
                    this.breadCrumbs[n] = 0;
                }
            }
        }
        this.breadCrumbsToInput();
        if (this.b_input.value == '') {
            //this.outerDiv.addClass('b-combo__input_error');
        }
    }
}
/**
 * Обработка наведения мыши поверх элемента списка
 */
CMultiLevelDropDown.prototype.onItemHover = function ()  {		
	var span = this;
	var self = this.self;
	var li = span.parentNode;
	if (li) {
	    var ul = li.parentNode;
	    if (ul) {
            var ls = ul.getElements(self.itemTag);
		    for (var i = 0; i < ls.length; i++) {
				if (ls[i].hasClass(self.HOVER_CSS)) {
					ls[i].removeClass(self.HOVER_CSS);
					break;
				}
			}
			span.addClass(self.HOVER_CSS);
			this.arrowsAction = 0;
		}
	}
}
/**
 * @param value - текстовое знаение
 * @param id    - идентификатор значения "Все ... " в базе данных (необязательный параметр)
 */
CMultiLevelDropDown.prototype.setDefaultValue = function (value, id)  {
	for (var j = this.columns.length - 1; j > -1; j--) {
		this.clear(j);
	}
		
	var ls = this.columnsCache[0];
	for (var i = 0; i < ls.length; i++) {
		//index, text, column, parentId, nocache, append, clickable, extendsSelectors		
		if (i < this.QUANTITY_OUTPUT_RECORDS) this.addItem(ls[i].id, ls[i].text, 0, ls[i].prid, 1);
	}
	if ((ls.length - this.QUANTITY_OUTPUT_RECORDS) > 0) {
		//index, text, column, parentId, nocache, append, clickable, extendsSelectors		
		this.addItem(-1, this.getMoreString(this.QUANTITY_OUTPUT_RECORDS, ls.length, 0), 0, -1, 1, 1, 0, this.FAKE_ITEM_STYLE);
	}
	this.b_input.set("value", value);
	this.resize(this.b_input);
	if (String(Number(id)) != "NaN") {
		this.id_input.value = id;
		this.columnId_input.value = 0;
	}
}

/**
 * Обработка css селектора override_value_id_НомерКолонки_IDЗаписиВТтаблицеБазыДанных_Текстовое+Значение+Свойства
 */
CMultiLevelDropDown.prototype.parseDefaultValues = function ()  {	
	this.labels = new Object();
	var s = this.selectors;
	var prefix  = "all_value_id_";
	var prefix2 = "override_value_id_";
	var arr = s.split(/\s/gi);
	for (var i = 0; i < arr.length; i++) {
		if ((arr[i].indexOf(prefix) != -1) || (arr[i].indexOf(prefix2) != -1) ) {
			var q = arr[i];
			q = q.replace(prefix, "");
			q = q.replace(prefix2, "");
			var arr2 = q.split("_");
			if (arr2.length >= 2) {	
				this.labels[arr2[0]] = {id:arr2[1], text:String(arr2[2]).replace("+", " ")};
			}
		}
	}
}
/**
 * Обработка css селектора exclude_value_НомерКолонки_IDЗаписиВТтаблицеБазыДанных
 */
CMultiLevelDropDown.prototype.parseExcludeValues = function ()  {	
	this.exclude = new Object();
	var s = this.selectors;
	var prefix = "exclude_value_";
	var arr = s.split(/\s/gi);
	for (var i = 0; i < arr.length; i++) {
		if (arr[i].indexOf(prefix) != -1) {
			var q = arr[i];
			q = q.replace(prefix, "");
            q = q.replace('__', '_-'); // если ID с минусом (напр в профессиях есть ИД -3 -4)
			var arr2 = q.split("_");
			if (arr2.length == 2) {
				this.exclude[arr2[0]] = {id:arr2[1]};
			}
		}
	}
}

/**
 * Закрытие выпадающего элемента при клике "в молоко"
 */
CMultiLevelDropDown.prototype.hide = function() {
    var opened = !this.shadow.hasClass('b-shadow_hide');
	this.shadow.addClass('b-shadow_hide');
    if ((this.b_input.value != this.grayText)&&(this.grayText != '')) this.outerDiv.getElement('.b-combo__input-text').removeClass('b-combo__input-text_color_a7');
	if (this.err == 1) {
		this.onInvalidValue();
	};
	var s = this.b_input.value.replace(new RegExp("\\" + this.DIVIDER + "$"), "");
	if ( s != this.b_input.value ) {
        this.breadCrumbs[1] = 0;
        this.breadCrumbsToInput();
        if(opened && this.shadow.hasClass('b-shadow_hide')) {
            this.b_input.fireEvent('bcombochange');
        }
	}
	if(!s.trim()) {
    	if (this.labels[0] && this.labels[0].text) this.b_input.value = this.labels[0].text;
	} else {
    	this.b_input.value = s;
   	}
}

/**
 * Добавление при сообщении фокуса полю ввода разделитель, если это необходимо
 */
CMultiLevelDropDown.prototype.checkDivider = function() {
	if (!this.shadow.hasClass('b-shadow_hide')){
	    var s = this.b_input.value;
	    var arr = s.split(this.DIVIDER);
	    var n = arr.length;
	    var ul = this.columns[n];
	    if (ul) {
			if (ul.getElements("li").length > 0) {
                
                
                if ( this.b_input.value ==  this.columns[0].getElements("li")[0].get("text") && this.b_input.value == this.labels[0].text ) {
                    this.b_input.value = '';
                } 
                if (this.b_input.value.length && n + 1 < this.columns.length) {
                    ul = this.columns[n + 1];
                    if (ul && ul.getElements("li").length > 0) {
                        this.b_input.value += this.DIVIDER;
                    }
                }
            }
		}
	}
}

/**
 * Склонение слова "значение" /города страны поезда и прочая сущность )
 * @param   Int n
 * @param   Int columnIndex
 * @return  String
 */
CMultiLevelDropDown.prototype.getSuffix = function(n, columnIndex) {
	var argN = n = parseInt(n);
	var sN = String(n);
	var m = null;
	if (sN != "NaN") {
		if(sN.length > 1) {
			n = parseInt(sN.charAt(sN.length - 1));
			m = parseInt(sN.charAt(sN.length - 2));
		}
	}
    var cI = columnIndex;
	if ((m == 1)&&(n == 1))	{        
		if (cI > this.greatThanFiveResult.length - 1) {
            cI = this.greatThanFiveResult.length - 1;
		}
        return this.greatThanFiveResult[cI];
	}
	if (n == 1) {
        if (cI > this.oneResult.length - 1) {
            cI = this.oneResult.length - 1;
        }
        return this.oneResult[cI];
	}
	if (cI > this.greatThanFiveResult.length - 1) {
        cI = this.greatThanFiveResult.length - 1;
    }    
	return this.greatThanFiveResult[cI];
}
/**
 * Склонение слова "показано" /города страны поезда и прочая сущность)
 * Пока частный случай для списка стран и городов
 * За информативное название метода скажу спасибо
 * @param   Int n
 * @param   Int column
 * @return  String
 */
CMultiLevelDropDown.prototype.getPrefix = function(n, column) {
	var argN = n = parseInt(n);
	var sN = String(n);
	if (sN != "NaN") {
		if(sN.length > 1) {
			n = parseInt(sN.charAt(sN.length - 1));
			sN = String(n);
		}
	}
	if (sN != "NaN") {
		if ((n == 1)&& ((argN < 12)||(argN == 1))) {
			if (column == 0) {		    
                return "Показанo";
            } else {
                return "Показаны";
            }
		}else {
            if (column == 0 && (this.columnsCache[1].length > 1 || this.isCitiesList)) {
                return "Показаны";
            } else {
                return "Показанo"
            }
        }
	}
	return '';
}
/**
 * Очистка поля ввода если в списке нет такого значения
 */
CMultiLevelDropDown.prototype.clearIfValueNotFound = function() {
    var s = this.b_input.value;
    s = s.replace(/:\s$/, "");
    if (s.length == 0) return;
    var arr = s.split(this.DIVIDER);
    var allFound = true;
    var itemsExists = 0;
    for (var i = 0; i < arr.length; i++) {
        s = arr[i];
        try {
            var ul = this.columns[i];
            var ls = ul.getElements("span");
            var found = true;
            if (ls.length > 0) {
				found = false;
			}
            for (var j = 0; j < ls.length; j++) {
                itemsExists = 1;
                var q = ls[j].get("text");
                if (q == s) {
                    found = true;
                }
            }
            if (!found) {
                allFound = false;
                break;
            }
        } catch (e) {;}
    }
    if (allFound && !itemsExists) {
         allFound = 0;
    }
    if (!allFound && this.id_input.value == 0) {
        this.b_input.value = '';
    }
}
/***
 * @param int countView   - показано
 * @param int totalCount  - всего существует
 * @param int columnIndex - номер колонки
 * */
CMultiLevelDropDown.prototype.getMoreString = function(countView, totalCount, columnIndex) {
    var ul = this.columns[columnIndex];
    if (ul) {
        var ls = ul.getElements("span.b-combo__item-inner");
        for (var i = 0; i < ls.length; i++) {
            if (ls[i].get("dbid") == 0) {
                countView--; 
			    break;
            }
        }
    }
    var s = this.getPrefix(countView, columnIndex) + " " + countView + " из " + totalCount + " " + this.getSuffix(totalCount, columnIndex);
    return s;
 }
/**
* Перезагрузка данных подстроки s
* @param String s  - подстрока, для которой производится перезагрузка
* @param Number n  - номер столба, в котором происходит поиск подстроки в случае readonly списка
*/
CMultiLevelDropDown.prototype.reload = function(s, n) {
    if (parseInt(s) != s) {
        this.b_input.value = (s == undefined?'':s);
    }
    if (this.b_input.readOnly) {
        if (!n) {
            n = 0;
        }
        var ul = this.columns[n];
        var ls = ul.getElements("span.b-combo__item-inner");
        for (var i = 0; i < ls.length; i++) {
            if (ls[i].innerHTML == s || s == parseInt(ls[i].get("dbid"))) {
                ls[i].addClass(this.HOVER_CSS);
                ls[i].parentNode.addClass("item " + this.greenCss);
                this.breadCrumbs[n] = this.id_input.value = ls[i].get("dbid");
                this.columnId_input.value = n;
                if (parseInt(s) == s) {
                    this.b_input.value = ls[i].innerHTML;
                }
                this.breadCrumbsToInput();
            } else {
                ls[i].removeClass(this.HOVER_CSS);
                ls[i].parentNode.removeClass("item " + this.greenCss);
            }
        }
        if (s == undefined) {
            this.b_input.value = this.id_input.value = this.columnId_input.value = '';
            this.outerDiv.getElements("input.mlddcolumn").each(
                function (item) {
                    item.value = '';
                }
            );
        }
        return;
    }
    for (var i = this.columns.length - 1; i > -1; i--) {
        this.clear(i);
    }
    if (s != '') {
        this.hideOnload = 1;
    }
    this.onKeyUp({code:0}, this.b_input);
    this.b_input.value = s;
    this.show(0);
    this.b_input.blur();
}
/**
* Выделение элемента в списке по его id
*@param int    id        - идентификатор записи, которую надо выделить в списке
*@param String requestId - идентификатор запроса к серверу 
*@param String args - необязательный параметр (arg0=val0&arg1=val1&arg2=val2 ... )
*@param int parentId  - необязательный параметр идентификатор родительской записи (полезно например, когда надо подгрузить список городов страны и выделтить город)
*/
CMultiLevelDropDown.prototype.selectItemById = function(id, requestId, args, parentId) {
    var ls = this.columnsCache[0];    
    for (var i = 0; i < ls.length; i++) {
        if (ls[i].id == id ) {
            this.reload(ls[i].text);
            return;
        }
    }
    this.selectValueOnLoad = 1;
    this.id_input.value = id;
    this.loadData(requestId, parentId?parentId:0, 1, args);
}
/**
* Задано значение по умолчанию, выделяем в списке
*/
CMultiLevelDropDown.prototype.selectItems = function() {
    if (this.b_input.value.indexOf(this.DIVIDER) != -1) {
        if (this.columnId_input.value == 1) {
            var ls = this.b_input.value.split(this.DIVIDER);
            var countryText = ls[0];
            var cityText = ls[1];
            var ls = this.columns[0].getElements("span");
            var parentId = 0;
            for (var i = 0; i < ls.length; i++) {
                if (ls[i].get("text") == countryText) {
                    parentId = ls[i].get("dbid");
                }
            }
            if (parentId == 0) {
                 var arr = this.columnsCache[0];
                 for (var i = 0; i < arr.length; i++) {
                     if (arr[i].text == countryText) {
                         parentId = arr[i].id;
                         break;
                     }
                 }
                 if (parentId != 0) {
                     this.clear(0);
                     this.addItem(parentId, countryText, 0, 0, 1);
                 }
            }
            if (parentId != 0) {
                this.selectValueOnLoad = 1;
                this.hideOnload        = 1;
                var currId       = this.id_input.value;
                var currColumnId = this.columnId_input.value;
                var value        = this.b_input.value;
                this.emulateClick(parentId, 0);
                this.id_input.value = currId;
                this.columnId_input.value = currColumnId;
                this.b_input.value = value;
                this.resize(this.b_input);
            }
        }
    } else {
        if (this.columnId_input.value == 0 && this.id_input.value) {
            var text = this.b_input.value;
            var ls = this.columns[0].getElements("span");
            var found = 0;
            for (var i = 0; i < ls.length; i++) {
                if (ls[i].get("text") == text) {
                    found = 1;
                    ls[i].getParent("li").addClass(this.greenCss);
                    this.breadCrumbs[0] = ls[i].get("dbid");
                }
            }
            if (!found) {
                 var arr = this.columnsCache[0];
                 for (var i = 0; i < arr.length; i++) {
                     if (arr[i].text == text) {
                         found = 1;
                         this.breadCrumbs[0] = arr[i].id;
                         break;
                     }
                 }
                 if (found) {
                     this.clear(0);
                     this.addItem(this.breadCrumbs[0], text, 0, 0, 1);
                     this.columns[0].getElements("li").addClass(this.greenCss);
                 }
            }
        }
    }
    this.breadCrumbsToInput();
}
/**
*Задано значение по умолчанию, выделяем в списке (правая колонка)
*/
CMultiLevelDropDown.prototype.selectRightItem = function() {
    var columnId = 1;
    var found = 0;
    if (this.selectValueOnLoad && (this.id_input.value)) {
        var ls = this.columns[columnId].getElements("span");
        while (!ls.length) {
            columnId--;
            ls = this.columns[columnId].getElements("span");
            if (columnId < 0) {
                columnId = 0;
                break;
            }
        }
        for (var i = 0; i < ls.length; i++) {
            ls[i].removeClass(this.HOVER_CSS);
            if (ls[i].get("dbid") == this.id_input.value) {
                ls[i].getParent("li").addClass(this.greenCss);
                found = 1;
                this.breadCrumbs[columnId] = this.id_input.value;
                this.breadCrumbsToInput();
            }
        }
        if (!found) {
           this.clear(columnId);
           for (var i = 0; i < this.columnsCache[columnId].length; i++) {
               if (this.columnsCache[columnId][i].id == this.id_input.value) {
                   this.addItem(this.columnsCache[columnId][i].id, this.columnsCache[columnId][i].text, 1, this.columnsCache[columnId][i].prid, 1);
                   var ls = this.columns[columnId].getElements("span");
                   for (var i = 0; i < ls.length; i++) {  
                        if (ls[i].get("dbid") == this.id_input.value) {
                            ls[i].getParent("li").addClass(this.greenCss);
                            this.breadCrumbs[columnId] = this.id_input.value;
                            this.breadCrumbsToInput();
                            break;
                        }
                   }
                   this.addFakeItems(columnId);
                   break;
               }
           }
        }
        this.b_input.value = this.b_input.value.replace(new RegExp("\\:\\s$"), "");
        this.selectValueOnLoad = 0;
        this.hideOnload = 0;
    }
    return found;
}
/**
* Попытка скопировать в кеш данные из кеша другого CMultiLevelDropDown, если это задано селектором 
* copy_column_N_form_ID
* @return Bool true если попытка успешна
*/
CMultiLevelDropDown.prototype.tryCopyColumnFromOtherComboBox = function() {
    var s = this.selectors;
    var re = /.*\s+cut_column_(\d+)_form_(\w+)_set_parent_(\d+).*/
    var comboBoxId =  s.replace(re, '$2');
    if (comboBoxId.indexOf(" ") == -1 && comboBoxId.length) {
        var donor = ComboboxManager.getInput(comboBoxId);
        if(donor instanceof CMultiLevelDropDown) {
            var nColumn =  parseInt(s.replace(re, '$1'));
            if (nColumn) {
                if (donor.columnsCache && donor.columnsCache[nColumn]) {
                    this.columnsCache[0] = donor.columnsCache[nColumn];
                    donor.columnsCache[nColumn] = new Array();
                    var parentId =  parseInt(s.replace(re, '$3'));
                    this.breadCrumbs[-1] = -90000;
                    if (parentId) {
                        this.breadCrumbs[-1] = parentId;
                        
                        //@todo: данное решение не срабатываем
                        //так как там для выделения пункта эмулируется
                        //поиск (ввод в поле) что отфильтровывает другие элементы
                        //и визульно в выпадающем списке остается один пункт
                        //this.reload(this.b_input.value);

                        //@todo: данное решение эмулирует клик 
                        //на пункте который установлен поумолчанию
                        this.reload();
                        
                        if (this.id_input.value >= 0) {
                            var ul = this.columns[0];
                            var ls = ul.getElement("span.b-combo__item-inner[dbid=" + this.id_input.value + "]");
                            if(ls) {
                                ls.fireEvent('click');
                            }
                        }
                    }
                    return true;
                }
            }
        }
    }
    return false;
}
/**
* Копирует массив хлебных крошек в массив инпутов
*/
CMultiLevelDropDown.prototype.breadCrumbsToInput = function() {
    var id = this.idConv(this.b_input.id,"_columns");
    var css = "mlddcolumn";
    var ls = this.outerDiv.getElements("input." + css);
    for (var i = ls.length - 1; i > -1; --i) {
	    var I = ls[i];
	    var p = I.parentNode;
	    p.removeChild(ls[i]);
	}
	for (var i = 0; i < this.breadCrumbs.length; i++) {
        if (this.breadCrumbs[i] > 0) {
            this.id_input.value = this.breadCrumbs[i];
            this.columnId_input.value = i;
        }
        if ($$('[name=' + id + '[' + i + ']]').length) {
            $$('[name=' + id + '[' + i + ']]').addClass('mlddcolumn');
            continue;
        }
		var input = new Element("input", {"type":"hidden", "name": id + "[" + i + "]", "class": css, "value":(this.breadCrumbs[i] < 0 ? 0:this.breadCrumbs[i])});
        input.inject(this.outerDiv, "top");
	}

}
/**
* Проверка, есть ли элементы в списке
*/
CMultiLevelDropDown.prototype.isEmpty = function() {
    for (var n = 0; n < this.columns.length; n++) {
        var ul = this.columns[n];
        if (ul) {
            var ls = ul.getElementsByTagName('li');
            if (ls.length) {
                return false;
            }
        }
    }
    return true;
}
/**
* Выделение записи в списке
* @param String name    - текст свойства
* [@param Number n]  - номер колонки (необязательный параметр)
*/
CMultiLevelDropDown.prototype.selectItemByName = function(name, id) {
    this.reload(name, n);
}
/**
* Снимает выделение со всех записей в списке
*/
CMultiLevelDropDown.prototype.unselect = function() {
    this.reload();
}
/**
* Поддержка AddEvent('onchange')
*/
CMultiLevelDropDown.prototype._onChangeHandler = function() {
    var o = this.b_input.retrieve("events");
    if (o && o.change && o.change.keys) {
        for (var i in o.change.keys) {
            if (parseInt(i) == i) {
                if (o.change.keys [i] instanceof Function) {
                    o.change.keys [i] ({target:this.b_input});
                }
            }
        }
    }

    if(this.shadow && this.shadow.hasClass('b-shadow_hide')) {
        this.b_input.fireEvent('bcombochange');
    }

    if (this.b_input.onchange instanceof Function) {
        this.onchangeHandler = this.id_input.onchange = this.b_input.onchange;
        this.b_input.onchange = null;
    }
    this.onchangeHandler();
}
//Конец определения класса  CMultiLevelDropDown
