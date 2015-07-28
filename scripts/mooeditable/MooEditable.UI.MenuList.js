/*
---

script: MooEditable.UI.MenuList.js

description: UI Class to create a menu list (select) element.

license: MIT-style license

authors:
- Lim Chee Aun

requires:
# - MooEditable
# - MooEditable.UI

provides: [MooEditable.UI.MenuList]

...
*/

MooEditable.UI.MenuList = new Class({

    Implements: [Events, Options],

    options: {
        /*
        onAction: $empty,
        */
        title: '',
        name: '',
        'class': '',
        list: []
    },

    initialize: function(options, toolbar){
        this.setOptions(options);
        this.name = this.options.name;
        this.render();
        this.toolbar = toolbar;
        this.editor = this.toolbar.editor;
    },
	
    toElement: function(){
        return this.el;
    },
	
    render: function(){
        var self = this;
        var html = '';
        this.options.list.each(function(item){
            html += '<option value="{value}" style="{style}">{text}</option>'.substitute(item);
        });
        this.el = new Element('select', {
            'class': self.options['class'],
            title: self.options.title,
            html: html,
            styles: {
                'height' : '21px'
            },
            events: {
                change: self.change.bind(self)
            }
        });
		
        this.disabled = false;

        // add hover effect for IE
        if (Browser.Engine.trident) this.el.addEvents({
            mouseenter: function(e){
                this.addClass('hover');
            },
            mouseleave: function(e){
                this.removeClass('hover');
            }
        });
		
        return this;
    },
	
    change: function(e){
        e.preventDefault();
        if (this.disabled) return;
        var name = e.target.value;
        this.action(name);
    },
	
    action: function(){
        this.fireEvent('action', [this].concat($A(arguments)));
    },
	
    enable: function(){
        if (!this.disabled) return;
        this.disabled = false;
        this.el.set('disabled', false).removeClass('disabled').set({
            disabled: false,
            opacity: 1
        });
        return this;
    },
	
    disable: function(){
        if (this.disabled) return;
        this.disabled = true;
        this.el.set('disabled', true).addClass('disabled').set({
            disabled: true,
            opacity: 0.4
        });
        return this;
    },
	
    activate: function(value){
        if (this.disabled) return;
        var index = 0;
        if (value) this.options.list.each(function(item, i){
            if (item.value == value) index = i;
        });
        this.el.selectedIndex = index;
        return this;
    },
	
    deactivate: function(){
        this.el.selectedIndex = 0;
        this.el.removeClass('onActive');
        return this;
    }
	
});


MooEditable.UI.MenuCustomList = new Class({

    Extends: MooEditable.UI.MenuList,

    render: function(ee) {
        this.el = new Element('div', {
            'class': 'formatBlock-item toolbar-item'
        });

        this.timeoutID = null;


        this.el.addEvent('mouseenter', function() {
            this.el.range = this.editor.selection.getRange();
            
            if(this.timeoutID) {
                $clear(this.timeoutID);
            }
        }.bind(this));

        this.el.addEvent('mouseleave', function() {
            this.timeoutID = this.el.removeClass.delay(400, this.el, 'formatBlock-item-focus');

            if(Browser.Engine.trident) this.editor.selection.setRange(this.el.range);
        }.bind(this));


        this.label = new Element('span', {
            'class': 'f',
            'html': '<span class="l"><span class="h4">Заголовок 1</span></span><span class="r"></span>'
        });
        this.label.addEvent('click', function(lbl) {
            if(this.disabled) return;

//            this.el.range = this.editor.selection.getRange();
            
            if(this.el.hasClass('formatBlock-item-focus')) {
                this.el.removeClass('formatBlock-item-focus');
            } else {
                this.el.addClass('formatBlock-item-focus');
            }
        }.bind(this, this.label));

        this.label.inject(this.el);

        this.el.addEvent('change', this.change.bind(this))

        items = this.options.list;
        this.ul = new Element('ul');
        for(i = 0; i < items.length; i++){
            el = new Element('li', {
                'html' : items[i].text,
                'class' : items[i].value
            });
            el.addEvent('click', function(elm) {
                this.editor.selection.setRange(this.el.range);
                this.el.fireEvent('change', [elm, this]);
            }.bind(this, el));
            el.inject(this.ul);
        }
        this.ul.inject(this.el);
        this.setLabelFromEl(this.ul.getFirst());

        this.addEvent('onRender', this.onRender);

        return this;
    },

    change: function(el) {
        this.action(el.get('class'));
        this.el.removeClass('formatBlock-item-focus');
        
        this.setLabelFromEl(el);
    },

    setLabelFromEl: function(el) {
        this.label.getElement('span>span').set('class', el.get('class'));
        this.label.getElement('span>span').set('html', el.get('html'));
    },

    activate: function(value){
        if (this.disabled) return;
        el = this.ul.getElement('li[class='+value+']');
        this.setLabelFromEl(el);
        return this;
    },

    deactivate: function(){
        this.setLabelFromEl(this.ul.getFirst());
        return this;
    }

});