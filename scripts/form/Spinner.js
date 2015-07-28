/**
 * Класс элемента Zend_Form_Element_Spinner
 * @type Class
 */
var ElementSpinner = new Class({
    
    element: null,
    name: null,
    element_input: null,
    btn_plus: null,
    btn_minus: null,
    element_suffix: null,
    
    max_value: 0,
    min_value: 0,
    current_value: 0,
    step: 1,
    
    initialize: function(element)
    {
        var _this = this;
        
        this.element = element;
        this.name = element.get('data-element-spinner-name');
        this.element_input = element.getElement('input');
        this.btn_plus = element.getElement('[data-element-spinner-plus]');
        this.btn_minus = element.getElement('[data-element-spinner-minus]');
        this.element_suffix = $('el-' + this.name + '-suffix');

        if (this.element_input) {
            //Предельные значения
            this.max_value = this.element_input.get('max');
            this.min_value = this.element_input.get('min');
            this.current_value = this.element_input.get('value');
            
            //Миняем подпить если она есть
            if (this.element_suffix) {
                var suffix = this.element_suffix.get('data-element-spinner-suffix');
                suffix = suffix && suffix.split(',');
                var is_suffix = suffix && suffix.length === 3;
                
                if (is_suffix) {
                    this.element_input.addEvent('change', function(){
                        _this.element_suffix.set('text', _this.plural(
                                _this.current_value, 
                                suffix[0], 
                                suffix[1], 
                                suffix[2]));
                    });
                }
            }
            
            //Контроль значения после изменений
            this.element_input.addEvent('change', function(){
                var value = parseInt(this.value);

                if (isNaN(value)) {
                    this.value = _this.current_value;
                    return false; 
                }
                
                return true;
            });
            
            //Констроль воода символов
            this.element_input
                    .addEvent('keydown', this.isAllowSymbol)
                    .addEvent('keyup', function(e){
                
                        var value = parseInt(this.value);
                        
                        if (_this.isAllowSymbol(e) && !isNaN(value)) {
                           if (_this.max_value < value || 
                               _this.min_value > value) {
                              this.value = _this.current_value;
                              return false; 
                           } else {
                              _this.current_value = this.value;
                           }
                           
                           this.fireEvent('change');
                        } 
                        
                        return true;
                    });
        }

        //Инкеремент
        if (this.btn_plus) {
            this.btn_plus.addEvent('click', function(){
                if (_this.current_value < _this.max_value) {
                    _this.current_value++;
                    _this.element_input
                            .set('value', _this.current_value)
                            .fireEvent('change');
                }
            });
        }
        
        //Декремент
        if (this.btn_minus) {
            this.btn_minus.addEvent('click', function(){
                if (_this.current_value > _this.min_value) {
                    _this.current_value--;
                    _this.element_input
                            .set('value', _this.current_value)
                            .fireEvent('change');
                }                
            });
        }
    },   
      
    
    /**
     * Позволяем вводить только разрешенные символы
     */        
    isAllowSymbol: function(e)
    {           
        var event = e.event;

        // Allow: backspace, delete, tab, escape, and enter
        if (e.code == 46 || e.code == 8 || e.code == 9 || e.code == 27 || e.code == 13 ||
        // Allow: Ctrl+A
        (e.code == 65 && (event.ctrlKey === true || event.metaKey === true)) ||
        // Allow: Ctrl+C, Ctrl+V, Ctrl+R, etc.
        (event.charCode == 0 && (event.ctrlKey === true || event.metaKey === true)) ||
        // Allow: home, end, left, right
        (e.code >= 35 && e.code <= 39) || 
        // Digits        
        e.key.test(/[\d-]/)
        ) {
            return true;
        }

        return false;        
    },    
        
        
            
    /**
    * Возвращает правильный вариант 
    * окончания существительного для числа
    * 
    * @param {type} a
    * @param {type} str1
    * @param {type} str2
    * @param {type} str3
    * @returns {unresolved}
    */
    plural: function(a, str1, str3, str2)
    {
        var number = a;
        var p1 = number%10;
        var p2 = number%100;
        
        if(number == 0) return str2;
        if(p1==1 && !(p2>=11 && p2<=19)) return str1;
        if(p1>=2 && p1<=4 && !(p2>=11 && p2<=19)) return str3;
        
        return str2;
    }
});

/**
 * Спомощью фабрики создаем обьекты описанного выше класса 
 * существующие в данный момнт на странице
 */
window.addEvent('domready', function() {
    if (typeof window.elements_factory !== "undefined") {
        window.elements_factory.addElements('element-spinner-name','ElementSpinner');
    }
});