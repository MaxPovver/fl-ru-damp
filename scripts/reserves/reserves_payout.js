/**
 * Класс обработки событий всплывающего окна 
 * выплаты средств по резерву
 * 
 * @type Class
 */
var ReservesPayout = new Class({
    
    popup: null,
    form: null,
    wait_screen: null,
    error_screen: null,
    
    initialize: function(p)
    {
        if(!p) return false;
        
        var _this = this;
        this.popup = p;
        this.form = p.getElement('form');    
        this.wait_screen = p.getElement('[data-reserves-payout-wait-screen]');
        this.error_screen = p.getElement('[data-reserves-payout-error-screen]');
        
        var link_types = p.getElements('[data-reserves-payout-type]');
        
        if(link_types.length)
        {
            link_types.addEvent('click', function() {
                var type = this.get('data-reserves-payout-type');
                if (!type) return false;
                var wait_msg = this.get('data-reserves-payout-wait');
                if(wait_msg) _this.show_wait(wait_msg);
                _this.process(type);
                return false;
            });
        }        
    },
            
    process: function(type)
    {
        var data = xajax.getFormValues(this.form);
        return xajax_reservesPayoutProcess(type,data);
    },
            
    show_wait: function(msg)
    {
        if(!this.wait_screen) return false;
        this.hide_error();
        if(msg != 'true') 
            this.wait_screen
                .getElement('[data-reserves-payout-wait-msg]')
                .set('html',msg);
        this.wait_screen.getParent().addClass('b-layout_waiting');
        this.wait_screen.removeClass('b-layout_hide');
        return true;
    },        
    
    hide_wait: function()
    {
        if(!this.wait_screen) return false;
        this.wait_screen.getParent().removeClass('b-layout_waiting');
        this.wait_screen.addClass('b-layout_hide');
        return true;
    },            
            
    close_popup: function()
    {
        this.hide_wait();
        this.popup.addClass('b-shadow_hide');
        return true;
    },
    
    show_error: function(msg)
    {
        if(!this.error_screen) return false;
        this.hide_wait();
        if(msg.length) 
            this.error_screen
                .getElement('[data-reserves-payout-error-msg]')
                .set('html',msg);
        this.error_screen.removeClass('b-layout_hide');
        return true;
    },
    
    hide_error: function()
    {
        if(!this.error_screen) return false;
        this.error_screen.addClass('b-layout_hide');
        return true;
    }
    
});

var ReservesPayoutFactory = new Class({
    initialize: function()
    {
        var popups = $$('[data-reserves-payout]');
        if(!popups) return false;
        window.reserves_payouts = {};
        popups.each(function(p){
            var id = p.get('id');
            window.reserves_payouts[id] = new ReservesPayout(p);
        });
    },
            
    /**
     * Получить обьект попапа отзыва по его ID
     */
    getReservesPayout: function(id)
    {
        return (typeof window.reserves_payouts[id] !== "undefined")?window.reserves_payouts[id]:false;
    }
});


window.addEvent('domready', function() {
    window.reserves_payout_factory = new ReservesPayoutFactory();
});