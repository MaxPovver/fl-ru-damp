function TServices_Order()
{
    TServices_Order=this; // ie ругался без этого, пока не понял.
    
    
    //--------------------------------------------------------------------------
    
    
    //Начальная инициализация
    this.init = function() 
    {
        if (typeof xajax !== "undefined") {
            xajax.callback.global.onComplete = this.onCompleteAjax;
        }
        
    };


    //--------------------------------------------------------------------------
    
    /**
     * Запускается приуспешном выполнении 
     * любого аякса на старице
     * 
     * @param {type} args
     * @returns {undefined}
     */
    this.onCompleteAjax = function(args)
    {
        
        //Исключаем функции после которых 
        //не нужно обновлять историю заказа
        var exclude_funcs = [
            'tservicesOrdersCheckMessages',
            'tservicesOrdersNewMessage',
            'getOrderHistory'
        ];

        if (exclude_funcs.indexOf(args.functionName.xjxfun) < 0) {
            TServices_Order.updateOrderHistory();
        }
    };


    //--------------------------------------------------------------------------
    
    
    this.updateOrderHistory = function()
    {
        if (!$('history') || (typeof _ORDERID === "undefined")) {
            return false;
        }
        
        xajax_getOrderHistory(_ORDERID);
        return true;
    };

    //--------------------------------------------------------------------------
    
    
    this.showAcceptPopup = function(idx)
    {
        $$('.__tservices_orders_status_popup_hide').addClass('b-shadow_hide');
        $('tservices_orders_status_popup_' + idx).removeClass('b-shadow_hide');
        return false;
    };
    
    
    //--------------------------------------------------------------------------
    
    
    this.closeAcceptPopup = function(idx)
    {
        $('tservices_orders_status_popup_' + idx).addClass('b-shadow_hide');
        return false;
    };
    
    
    //--------------------------------------------------------------------------
    
    this.setOrderStatus = function(elem, status)
    {
        //if(status == 'close' && !confirm('Тут будет попап с указание отзыва!')) return FALSE;
        
        if(status == 'close')
        {
            $('tservices_orders_feedback_popup').removeClass('b-shadow_hide');
            return false;
        }
        
        
        var form = new Element('form', {'action':elem.get('data-url'),'method':'post'});
        var idx = new Element('input', {'type':'hidden', 'value':status,'name':'status'});
        var token = new Element('input', {'type':'hidden','value':_TOKEN_KEY,'name':'u_token_key'});
        
        form.adopt(idx,token);
        form.setStyle('display','none').inject($(document.body), 'bottom');
        form.submit();
    };
    
   
    //--------------------------------------------------------------------------
    
    this.changePriceAndDays = function(order_id)
    {
        var newPrice = parseInt($('tu_edit_budjet_price').value);
        var newDays = parseInt($('tu_edit_budjet_days').value);
        var paytype = $$('#tu_edit_budjet input[name=paytype]:checked');
        paytype = (paytype.length)?paytype[0].get('value'):'0';
        
        var success = true;
        if (newPrice < 300) {
            $('tu_edit_budjet_price').set('value', 300);
            success = false;            
        }
        if (newDays < 1) {
            $('tu_edit_budjet_days').set('value', 1);
            success = false;            
        }
        if (newDays > 730) {
            $('tu_edit_budjet_days').set('value', 730);
            success = false;            
        }
        
        if (success) {
            xajax_tservicesOrdersSetPrice(order_id, newPrice, newDays, paytype);
            $('tu_edit_budjet').toggleClass('b-shadow_hide');
        }        
    };
    
    
    //--------------------------------------------------------------------------
    
    
    this.hideBeforeStatus = function(order_id)
    {
        var order_message_id = 'tservices_order_message_' + order_id;
        var order_message = $(order_message_id);
        if(order_message) order_message.dispose();
    };
    
    
    this.showBeforeStatus = function(order_id, html)
    {
        var order_status = $('tservices_order_status_' + order_id);
        if(!order_status) return false;
        
        var order_message_id = 'tservices_order_message_' + order_id;
        var order_message = $(order_message_id);
        if(!order_message) 
            order_message = new Element('div', {id:order_message_id})
                                .inject(order_status, 'before');
        
        order_message.set('html',html);
    };
    
    
    //--------------------------------------------------------------------------
    
    
    //Запуск инициализации
    this.init();    
}

window.addEvent('domready', function() {
    new TServices_Order();
});