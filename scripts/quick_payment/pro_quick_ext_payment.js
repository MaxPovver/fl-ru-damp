/**
 * Класс обработки попапа покупки PRO
 * @type Class
 */
var pro_QuickExtPayment = new Class({
    
    //Обязательно наследуемся от базового класса
    Extends: QuickExtPayment,
    
    popup_id: '',
    
    last_visible: null,
    
    title_element: null,
    
    initialize: function(p) 
    {
        //Обязательно передаем в родительский класс
        //Это вызов родительского initialize()
        if (!this.parent(p) || 
            !this.form) {
        
            return false;
        }
        
        var _this = this;
        p.addEvent('showpopup', function(link) {
            _this.onShowPopup(link);
        });
        
        this.popup_id = this.popup.get('id');
        
        //Делаем видимым пункт поумолчанию
        this.last_visible = this.form.getElement('input[type=radio]:checked');
        if (this.last_visible) {
            this.last_visible.getParent().show();
            this.changeTitlePart();
            this.changeOption();
        }
    },
            
    onShowPopup: function(link)
    {
        var param = link.get('data-popup-params');
        if (!param || this.isWait()) {
            return false;
        }
        
        var current_visible = this.form.getElement('input[value='+param+']');
        if (current_visible) {
            this.last_visible.getParent().hide();
            current_visible.set('checked', true).getParent().show();
            this.last_visible = current_visible;
            this.changeTitlePart();
            this.changeOption();
        }
    },
    
    changeTitlePart: function()
    {
        if (!this.title_element) {
            this.title_element = this.popup.getElement('[data-quick-payment-title]');
        }
        
        if (!this.title_element || 
            !this.last_visible) {
        
            return false;
        }
        
        var value = this.last_visible.get('value');
        var template = $(this.popup_id + 'Type' + value);

        if (template) {
            this.title_element.set('html', template.get('html'));
        }
        
        return true;
    },
    
    changeOption: function()
    {
        var price = this.last_visible.get('data-quick-payment-price');
        this.setPrice(price);
    }        
});