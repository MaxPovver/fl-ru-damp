/**
* Зависит от:
* b-combo-dynamic-input.js
* b-combo-multidropdown.js
* b-combo-calendar.js
*/
var B_COMBO_AJAX_SCRIPT = '/b_combo_ajax.php';
var CComboboxManager = new function() {        
		/*для добавления нового типа комбобокса достаточно добавить в конфиг соответствующее поле, где
		 ключ     - css селектор, определяющий в html верстке тип тип комбобокса (все "-" в селекторе меняются на "_"), 
		 значение - JavaScript класс, реализующий свойства и функции комбобокса нового типа
		*/
		var FactoryConfig = {
			b_combo__input_multi_dropdown : 'CMultiLevelDropDown', 
			b_combo__input_calendar       : 'CCalendarInput',
			b_combo__input_dropdown	      : 'CAutocompleteInput',
			b_combo__input_phone_countries_dropdown : 'CPhoneCodesCountries'
		}
		var BASIS_COMBO_CLASS = 'b-combo__input';                       //наличие этого css селектора однозначно определяет, обрабатывать ли элемент как комбобокс (см. Руководство верстальщика)
		var basisTemplate = '<div class="b-combo__input"> \
								<input class="b-combo__input-text" type="text" /> \
								<label class="b-combo__label" ></label> \
							</div>';
		//private: 
		var list = new Array();	//массив комбобоксов, все созданные на странице комбобоксы помещаются в него
		var instance; 			//для реализации singleton
        // Конструктор
        function CComboboxManager() {
                if ( !document.instance ) {
                        document.instance = instance = this;                        
						window.addEvent("domready", initInputs);
				}
                else return instance;                 
        } 		
		//public:		
		//type может принимать значение css селектора (селекторов), определяющих тип  и свойства комбобокса
		//(см. руководство верстальщика)
		/**
		*  @param     DOMNode parentDOMNode
		*  @param     String   type
		*  @param     String   id = undefined установить атрибут id создаваемому input
		*  @return  CDynamicInput   //или его наследников
		**/
        CComboboxManager.prototype.append = function(parentDOMNode, type, id) {
			return injectElement(parentDOMNode, type, 'bottom', id);			
		};
		
		/**
		*  @param     DOMNode parentDOMNode
		*  @param     String   type
		*  @param     String   id = undefined установить атрибут id создаваемому input
		*  @return  CDynamicInput   //или его наследников
		**/
        CComboboxManager.prototype.prepend = function(parentDOMNode, type, id) {
			return injectElement(parentDOMNode, type, 'top', id);
		};

		/**
		*@param     String id - идентификатор элемента input. 
		* Удалаяет combobox в том случае, если input размещен в div с class="b-combo__input" который в свою очередь 
		*вложен в div с class = "b-combo" 
		**/
        CComboboxManager.prototype.remove = function(id) {
			var o   = $(id);
			var div = o.getParent(".b-combo__input");
			if (div) {
				for (var i = 0; list.length; i++) {
					if (list[i].outerDiv === div) {
						list.splice(i, 1);
						break;
					}
				}
			}
			var div = div.getParent('.b-combo');
			if (div) {
				div.dispose();
			}
		}
		
		/**
		* Возвращает массив комбооксов
		**/
        CComboboxManager.prototype.getList = function() {
			return list;
		}
		
		/**
		* Возвращает определенный комбобокс
		* @param id - id инпута, комбобокс которого нужно получить
		**/
        CComboboxManager.prototype.getInput = function(id) {
			for  (var i = 0; i < list.length; i++) {
				if (list[i].b_input.id == id) return list[i];
			}
			return false;
		}
		
		/**
		* Очищает содержимое выпадаемой части 
		*/
        CComboboxManager.prototype.setDefaultValue = function(id, value, tableId) {
			for (var i = 0; i < list.length; i++) {
				if (list[i].id() == id) {
					list[i].setDefaultValue(value, tableId);
					break;
				}
			}
		}
		
		CComboboxManager.prototype.createCombobox = function (div) {
			var ls = getListCssSelectors(div);			
			for (var i = 0; i < ls.length; i++) {
				if (String(FactoryConfig[ls[i]]) != "undefined") {
					return new FactoryConfig[ls[i]](div, ls);					
				}
			}			
			return new CDynamicInput(div, ls);			
		}
        
        CComboboxManager.prototype.initCombobox = function(ls) {		
			for (var i = 0; i < ls.length; i++) {
				list.push(createCombobox(ls[i]));
			}
        }

        CComboboxManager.prototype.initInputs = function() {
            list = new Array();		
	        initInputs();
        }
		
		//private:
		function initInputs() {
			var ls = $$('.' + BASIS_COMBO_CLASS);			
			for (var i = 0; i < ls.length; i++) {
				list.push(createCombobox(ls[i]));
			}
		}

		function createCombobox(div) {
			var ls = getListCssSelectors(div);			
			for (var i = 0; i < ls.length; i++) {
				if (window[FactoryConfig[ls[i]]] instanceof Function) {
					return new window[FactoryConfig[ls[i]]](div, ls);
				}
			}			
			return new CDynamicInput(div, ls);			
		}
		
		function getListCssSelectors(HtmlDivElement) {
			var s = HtmlDivElement.getProperty('class');
			s = s.replace(/\-/gi, "_");
			return s.split(new RegExp('\\s+', 'gi'));			
		}
				
		function injectElement(parentDOMNode, type, place, id) {
			var tpl = basisTemplate;
			type = type.replace(new RegExp('\\b' + BASIS_COMBO_CLASS + '\\b', 'gi'), '');
			//tpl  = tpl.replace('{extend}', ' ' + type);				
			var div = new Element('div', {'class': 'b-combo'});
			//div.set('html', tpl);
			div.inject(parentDOMNode, place);
			var b_div = new Element('div', {'class': BASIS_COMBO_CLASS});
			b_div.inject(div, 'top');
			b_div.addClass(type);
			var i = new Element('input', {'class': 'b-combo__input-text', 'type':'text'});
			i.inject(b_div, 'top');
			if (id) i.setProperty('id', id);
			var l = new Element('label', {'class': 'b-combo__label'});
			l.inject(i, 'after');			
			list.push(createCombobox(b_div));
			return list[list.length - 1];
		}     
		
        return CComboboxManager;
}

var ComboboxManager = new CComboboxManager();

//=======================================================
//тестируем append / prepend / remove
function prepend() {
	d = $('container');
	ComboboxManager.prepend(d, 'b-combo__input_width_100 b-combo__input_max-width_400 b-combo__input_resize');
}

function append() {
	d = $('container');
	ComboboxManager.append(d, 'b-combo__input_width_100 b-combo__input_max-width_400 b-combo__input_resize');
}

function remove() {
	ComboboxManager.remove('c1');
}

//например этой переменной будем инициализировать меню с подменю 
//(в соответствующем div элементе добавляем b_combo__input_init_specdata)
/*
* Пример перемеенной JavaScript инициализующей список  из трех колонок
*/
var threeData = {1:"Австралия",  2:"Босния", 
				 3: {0:"Россия", //первый элемент в случае вложенного объекта определяет элемент родительского меню
                                                  //То есть id России = 3, parentId России = 0 так как в примере справочника континентов нет 
					  31:{     //31 - это идентификатор города Москвы.
						   3:"Москва", // первый элемент в случае вложенного объекта определяет элемент родительского 
                                                               // меню его ключ - это parentId, в данном случае id России
						   311:"Ул.Брянцева",
						   312:"Ул.Семашко",
						   313:"Ул.Сталина",
						   314:"Ул.Ленина"
					   }, 
					   32:"Санкт-Петербург", 
					   33: "Волгоград",
					   34: "Самара",
					   35: "Саратов"
					},
				4: {  0: "Турция",
					  41: "Стамбул",
					  42: "Анкара"
					}
};
