function TServices_Catalog()
{
    TServices_Catalog=this; // ie ругался без этого, пока не понял.
    
    
    //--------------------------------------------------------------------------
    

    this.init = function() 
    {
    };
    
    //--------------------------------------------------------------------------
    
    
    this.clearFilterForm = function(el)
    {
        var form = el.getParent('form');
        form.getElements('input[type=checkbox]').set('checked', false);
        form.getElement('#location_id').set('value', 'Все страны');
        form.getElement('#category_id').set('value', 'Все категории');
        form.getElement('#keywords').set('value', '');
        form.getElements('.mlddcolumn').set('value', 0);
        form.getElement('#location_id_db_id').set('value', 0);
        form.getElement('#category_id_db_id').set('value', 0);
    };
    
    
    //--------------------------------------------------------------------------
    


    this.orderNow = function(elem)
    {
        var form = new Element('form', {'action':elem.get('data-url'),'method':'post'});
        var idx = new Element('input', {'type':'hidden', 'value':1,'name':'popup'});
        var token = new Element('input', {'type':'hidden','value':_TOKEN_KEY,'name':'u_token_key'});
        
        form.adopt(idx,token);
        form.setStyle('display','none').inject($(document.body), 'bottom');
        form.submit();
        
        return false;
    };
    
    this.changeFilterOrder = function(newOrder) {
        var form = $('frm');
        form.getElement('#order').set('value', newOrder);
        form.submit();
    };
    
    //--------------------------------------------------------------------------
    
    //Запуск
    this.init();    
}

window.addEvent('domready', function() {
    new TServices_Catalog();
    
    var hash = window.location.hash;
    if (hash === '#tu_filter') 
    {
        var el = $('b_ext_filter');
        if (el) 
        {
            var bh = $$('.b-bar')[0].getSize().y;
            var xScroll = window.getScroll().x;
            var yScroll = el.getPosition().y - bh - 10;
            var yScroll = yScroll < 0 ? 0 : yScroll;
            window.scrollTo(xScroll, yScroll);
	}
    }
    
    $$('.tu-order-link').addEvent('click', function() {
        
    });
});