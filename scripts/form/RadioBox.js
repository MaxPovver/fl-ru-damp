/**
 * Класс элемента RadioBox
 * @type Class
 */
var ElementRadioBox = new Class({
    
    element: null,
    last_radiobox_class_param: null,
        
    initialize: function(element)
    {
        var _this = this;
        
        this.element = element;
        
        if (!this.element) {
            return false;
        }
        
        var inputs = this.element.getElements('input[type=radio]');

        if (!inputs.length) {
            return false;
        }
        
        inputs.addEvent('change', function(){
            inputs.each(function(el){
                el.getParent().removeClass('b-radio__item_checked');
             
                //вырубаем все внешнии блоки если есть такой параметр
                var show_id_param = el.get('data-show-id');
                if (show_id_param) {
                    $(show_id_param).addClass('g-hidden');
                }
            });
            
            this.getParent().addClass('b-radio__item_checked');
            
            if (_this.last_radiobox_class_param) {
                _this.element.removeClass(_this.last_radiobox_class_param);
            }
            
            var radiobox_class_param = this.get('data-radiobox-class');
            if (radiobox_class_param) {
                _this.element.addClass(radiobox_class_param);
                _this.last_radiobox_class_param = radiobox_class_param;
            }
            
            
            //включаем внешний блок если есть такой параметр
            var last_show_id_param = this.get('data-show-id');
            if (last_show_id_param) {
                $(last_show_id_param).removeClass('g-hidden');
            }
        });
    }   
});

/**
 * Спомощью фабрики создаем обьекты описанного выше класса 
 * существующие в данный момент на странице
 */
window.addEvent('domready', function() {
    if (typeof window.elements_factory !== "undefined") {
        window.elements_factory.addElements('element-radiobox','ElementRadioBox');
    }
});