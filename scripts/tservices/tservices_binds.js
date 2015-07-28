/**
 * Класс скриптов для работы с закреплениями услуг
 * 
 * @type Class
 */

var TservicesBinds = new Class({
    
    initialize: function()
    {
        var link_close = $$('.b-pay-tu__close')[0];
        
        if (!link_close) {
            return false;
        }
        
        var short_block = $('show_tu_bind_block');
        var div_class = 'b-layout__tu-cols';
        
        var teaser_block = link_close.getParent('div.'+div_class);
        
        if (teaser_block === null) {
            div_class = 'i-pic';
            teaser_block = link_close.getParent('div.'+div_class);
        }
        
        if (teaser_block === null) {
            div_class = 'b-post';
            teaser_block = link_close.getParent('div.'+div_class);
        }
        
        
        var lastTservice = teaser_block.getParent().getElements('.'+div_class+'.b-layout_hide')[0];
        
        link_close.addEvent('click',function(){
            teaser_block.addClass('b-layout_hide');
            if ($('tservices_tile')) {
                teaser_block.inject('tservices_tile', 'before');
            }
            short_block.removeClass('b-layout_hide');
            if (lastTservice) lastTservice.removeClass('b-layout_hide');
            Cookie.write('hide_tservices_teaser', true, {duration: 30});
            return false;
        });
            
        short_block.getElements('a.b-layout__link')[0].addEvent('click',function(){
            teaser_block.removeClass('b-layout_hide');
            if ($('tservices_tile')) {
                teaser_block.inject('tservices_tile', 'top');
            }
            short_block.addClass('b-layout_hide');
            if (lastTservice) lastTservice.addClass('b-layout_hide');
            Cookie.dispose('hide_tservices_teaser');
            return false;
        });
        
        if (Cookie.read('hide_tservices_teaser')) {
            link_close.fireEvent('click');
        }
        
        if ($('b_ext_filter') && div_class != 'b-post') {
           short_block.inject('b_ext_filter', 'after'); 
        }
    }
            
});

window.addEvent('domready', function() {
    
    window.tservices_binds = new TservicesBinds();
});