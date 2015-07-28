/**
 * Класс табов
 * 
 * @type Class
 */
var TabPanel = new Class({

    panel: null,

    initialize: function(name) 
    {
        this.panel = $$('[data-tab-panel="' + name + '"]');
        
        if (!this.panel) {
            return false;
        }
        
        var items = this.panel.getElements('[data-tab-item]')[0];
        
        if (!items.length) {
            return false;
        }        
        
        items.addEvent('click', function(){
            var content_name = this.get('data-tab-item');
            var content = $$('[data-tab-content="'+content_name+'"]');

            if (content.length) {
                $$('[data-tab-content]').addClass('g-hidden');
                content.removeClass('g-hidden');
                items.removeClass('b-menu__item_active');
                this.addClass('b-menu__item_active');
            }

            return false;
        });
    },
    
    
    setTabContent: function(tab, content)
    {
        var tab_content = this.panel.getElement('[data-tab-content="'+tab+'"]')[0];

        if (tab_content) {
            tab_content.set('html', content);
            return true;
        }
        
        return false;
    }
    
    
});