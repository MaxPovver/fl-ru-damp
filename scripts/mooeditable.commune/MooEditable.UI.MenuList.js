/*
---

name: MooEditable.UI.MenuList

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
		onAction: function(){},
		*/
		title: '',
		name: '',
		'class': '',
		list: []
	},

	initialize: function(options, el){
		this.setOptions(options);
		this.name = this.options.name;
  this.editor = el;
		this.render();
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
			styles: {'height' : '21px'},
			events: {
				change: self.change.bind(self)
			}
		});
		
		this.disabled = false;

		// add hover effect for IE
		if (Browser.ie) this.el.addEvents({
			mouseenter: function(e){this.addClass('hover');},
			mouseleave: function(e){this.removeClass('hover');}
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
		this.fireEvent('action', [this].concat(Array.from(arguments)));
	},
	
	enable: function(){
		if (!this.disabled) return;
		this.disabled = false;
		this.el.set('disabled', false).removeClass('disabled1').set({
			disabled: false//, opacity: 1
		});
            this._enable();
		return this;
	},
	
	disable: function(){
		if (this.disabled) return;
		this.disabled = true;
		this.el.set('disabled', true).addClass('disabled1').set({
			disabled: true//, opacity: 0.5
		});
            setTimeout(this._disable.bind(this), 10);
		return this;
	},
	
	activate: function(value){
		if (this.disabled) return;
		var index = 0;
		if (value) this.options.list.each(function(item, i){
			if (item.value == value) index = i;
		});
		this.el.selectedIndex = index;
            this._enable();
		return this;
	},
	
	deactivate: function(){
		this.el.selectedIndex = 0;
		this.el.removeClass('onActive');
//            setTimeout(this._disable.bind(this), 10);
		return this;
	},
        
        _disable: function() {
            _item = this.el;
            if (this.el._overlay) {
                return;
            }
            
            _bg = '#ffffff';
            p = this.el.getParents();
            for (i = 0; i < p.length; i++) {
                if (p[i].getStyle('background-color').length && p[i].getStyle('background-color') != 'transparent') {
                    _bg = p[i].getStyle('background-color');
                    break;
                }
            };
            
            _over = new Element('div', {
                'styles' : {
                    'class' : 'tb-btn-over',
                    'position' : 'absolute',
                    'width' :   _item.getSize().x,
                    'height' :   _item.getSize().y+3,
                    'left' :   _item.getPosition().x,
                    'top' :   _item.getPosition().y,
                    'background-color'  :  _bg,
                    'z-index'   :   110,
                    'opacity'   : .6
                }
            });
            _over.inject(document.body);
            this.el._overlay = _over;
        },
        
        _enable: function() {
            if (this.el._overlay) {
                this.el._overlay.dispose();
                this.el._overlay = null;
            }
        }
	
});


MooEditable.UI.MenuCustomList = new Class({

    Extends: MooEditable.UI.MenuList,

    render: function(ee) {
        this.el = new Element('div', {
            'class': 'b-buttons b-buttons_inline-block i-shadow'
        });

        this.timeoutID = null;

        this.el.addEvent('mouseenter', function() {
            this.el.addClass('hover');
//            this.el.range = this.editor.selection.getRange();

            if(this.timeoutID) {
                $clear(this.timeoutID);
            }
        }.bind(this));
        
        var unfocus = function() {
            this.el.removeClass('focus');  
            this.el.getElement('.b-button').removeClass('b-button_active');  
			this.el.getElement('.b-shadow').addClass('b-shadow_hide');

        };

        this.el.addEvent('mouseleave', function() {
            this.el.removeClass('hover');
            this.timeoutID = unfocus.delay(400, this);

            //if(Browser.Engine.trident) this.editor.selection.setRange(this.el.range);
        }.bind(this));

        this.label = new Element('div', {
            'class': 'b-button b-button_small b-button_z-index_3',
            'html': '<span class="b-button__b1"><span class="b-button__b2"><span class="b-button__arrow-small"></span><span class="b-button__txt b-button__txt_fontsize_11">Заголовок 1</span></span></span>'
        });
        this.label.addEvent('click', function(lbl) {
            if(this.disabled) return;
            //if(Browser.ie) this.editor.selection.setRange(this.el.range);
            this.el.addClass('focus');
//            this.el.range = this.editor.selection.getRange();

            if(this.el.getElement('.b-button').hasClass('b-button_active')) {
                this.el.getElement('.b-button').removeClass('b-button_active');        
				this.el.getElement('.b-shadow').addClass('b-shadow_hide');

            } else {
                this.el.getElement('.b-button').addClass('b-button_active');
        		this.el.getElement('.b-shadow').removeClass('b-shadow_hide');
            }
        }.bind(this, this.label));
        this.label.addEvent('mousedown', function(lbl) {
            this.el.range = this.editor.selection.getRange();
            //return false;
        }.bind(this));

        this.label.inject(this.el);

        this.el.addEvent('change', this.change.bind(this))

        items = this.options.list;
        this.ul = new Element('div', {
            'class': 'b-shadow b-shadow_m b-shadow_zindex_2 b-shadow_top_22 b-shadow_left_2 b-shadow_hide',
            'html': '	<div class="b-shadow__right"><div class="b-shadow__left"><div class="b-shadow__top"><div class="b-shadow__bottom"><div class="b-shadow__body b-shadow__body_pad_10 b-shadow__body_bg_fff"></div></div></div></div></div><div class="b-shadow__tl"></div><div class="b-shadow__tr"></div><div class="b-shadow__bl"></div><div class="b-shadow__br"></div>'
			});
        for(i = 0; i < items.length; i++){
            el = new Element('div', {
                'html' : '<span class="b-shadow__icon item_list b-shadow__icon_galka"></span>'+items[i].text,
                'class' : 'b-shadow__txt b-shadow__txt_padbot_5 cursor_default '+items[i].value
            });
            el.addEvent('click', function(elm) {
                if (!this.el.range) {
                    this.el.range = this.editor.selection.getRange();
                }
                
                this.editor.selection.setRange(this.el.range);
                this.el.fireEvent('change', [elm, this]);
            }.bind(this, el));
            //el.addEvent('mousedown', function(elm) {
            //    return false;
            //}.bind(this, el));
            el.inject(this.ul.getElement('.b-shadow__body'));
        }
        this.ul.inject(this.el);
        this.setLabelFromEl(this.ul.getElement('.b-shadow__body').getFirst());

        this.addEvent('onRender', this.onRender);

        return this;
    },

    change: function(el) {
        this.el.removeClass('focus');
        var c = el.get('class');
        c = c.replace('b-shadow__txt b-shadow__txt_padbot_5 cursor_default ', '');
        this.action(c);
        this.el.getElement('.b-button').removeClass('b-button_active');
        this.el.getElement('.b-shadow').addClass('b-shadow_hide');

        this.setLabelFromEl(el);
    },

    setLabelFromEl: function(el) {
       // this.label.getElement('.b-button__txt>span').set('class', el.get('class'));    	
        this.label.getElement('.b-button__txt').set('html', el.get('html'));
        if (this.label.getElement('.item_list')) {        	
        	this.label.setStyle("min-width", "119px");        	
            this.label.getElement('.item_list').removeClass('b-shadow__icon_galka');
        }
        if (el.getElement('.item_list')) {
        	el.getParent(".b-shadow__bottom").getElements('.item_list').removeClass('b-shadow__icon_galka');
        	var className = el.getAttribute("class");
        	if (!className) {
                className = '';
        	}
        	if (className.charAt(className.length - 1) != '0') {
                el.getElement('.item_list').addClass('b-shadow__icon_galka');
            }
    	}
    },

    activate: function(value){
        if (this.disabled) return;
        el = this.ul.getElement('.b-shadow__body').getElement('div[class='+value+']');
        this.setLabelFromEl(el);
        return this;
    },

    deactivate: function(){
        //this.setLabelFromEl(this.ul.getElement('.b-shadow__body').getFirst());
        return this;
    }

});