/**
 * 
 * @type Class
 */
var reserve_QuickPayment = new Class({
    
    initialize: function() 
    {
        var popup = window.quick_payment_factory.getQuickPayment('reserve');
        
        if (!popup) {
            return false;
        }
        
        var is_send_docs = popup.form.getElement('[name=is_reserve_send_docs]');

        if (is_send_docs) {
            $('reserve_send_docs').addEvent('click', function(){
                is_send_docs.set('value', (this.checked === true)?1:0);
            });
        }
    }
});

window.addEvent('domready', function() {
    new reserve_QuickPayment();
});