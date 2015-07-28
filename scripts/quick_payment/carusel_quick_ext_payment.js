/**
 * Класс расширенного функционала для попапа быстрой оплаты размещения в карусели
 * В имя класса добавляется назание специфического попапа 
 * - это используется для автоматической инициализацией фабрикой
 * 
 * @type Class
 */
var carusel_QuickExtPayment = new Class({
    
    //Обязательно наследуемся от базового класса
    Extends: QuickExtPayment,
    
    initialize: function(p) 
    {
        //Обязательно передаем в родительский класс
        //Это вызов родительского initialize()
        if (!this.parent(p)) {
            return false;
        }
        
        //TODO
        var _this = this;

        var hours_element = this.form.getElement('[data-hours-el]');
        var hours_element_txt = this.form.getElement('[data-hours-txt]');

        $('el-carusel-num').addEvent('change', function(){
            var value = parseInt(this.value);
            if(isNaN(value) || value <= 0) value = 1;
            
            _this.setPrice(value * _this.price_value);
            
            if (value > 1) {
                hours_element_txt.addClass('b-layout__txt_hide');
                hours_element.removeClass('b-layout_hide');
            } else {
                hours_element.addClass('b-layout_hide');
                hours_element_txt.removeClass('b-layout__txt_hide');
            }
        });

    }
});