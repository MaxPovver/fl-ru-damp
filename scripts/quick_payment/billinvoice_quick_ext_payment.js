/**
 * Класс обработки попапа пополнения счета безналом BillInvoice
 * @type Class
 */
var billinvoice_QuickExtPayment = new Class({
    
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
        //var _this = this;
    }
});