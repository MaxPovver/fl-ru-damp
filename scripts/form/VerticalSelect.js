/**
 * Класс элемента VerticalSelect
 * @type Class
 */
var ElementVerticalSelect = new Class({
    
    element: null,
    
    last_optgroup_block: null,
    last_optgroup_link: null,
    last_option_label: null,
    bar_size: null,
    
    initialize: function(element)
    {
        var _this = this;
        
        this.element = element;
        
        if (!this.element) {
            return false;
        }

        var inputs = this.element.getElements('input[type=radio]');
        
        if (!inputs.length) {
            return false;
        }

        inputs.addEvent('change', function(){
            
            if (_this.last_option_label) {
                _this.last_option_label.removeClass('active');
            }
            
            var label = this.getNext();
            
            if (label) {
                _this.last_option_label = label.addClass('active');
            }
        });
        
        
        var links = this.element.getElements('[data-optgroup-link]');
        if (links.length) {
            links.addEvent('click', function(){
                var idx = this.get('data-optgroup-link');
                
                if (_this.last_optgroup_link) {
                    _this.last_optgroup_link.removeClass('active');
                }
                
                _this.last_optgroup_link = this.addClass('active');
                
                if (_this.last_optgroup_block) {
                    _this.last_optgroup_block.addClass('g-hidden');
                }

                var block = _this.element.getElement('[data-optgroup-block="'+idx+'"]');
                if (block) {
                    _this.last_optgroup_block = block.removeClass('g-hidden');
                }
                
                _this.element.getElement('[name="category"]').set('value', idx);
                
                return false;
            });
            
            this.element.getElement('[data-optgroup-link="2"]').fireEvent('click');
            
        } else {
            
            var sel = this.element.getElement('select');
            if (sel) {
                sel.addEvent('change', function(){
                    
                    var idx = this.get('value');
                    
                    if (_this.last_optgroup_link) {
                        _this.last_optgroup_link.removeClass('active');
                    }

                    _this.last_optgroup_link = this.addClass('active');

                    if (_this.last_optgroup_block) {
                        _this.last_optgroup_block.addClass('g-hidden');
                    }

                    var block = _this.element.getElement('[data-optgroup-block="'+idx+'"]');
                    if (block) {
                        _this.last_optgroup_block = block.removeClass('g-hidden');
                    }

                    _this.element.getElement('[name="category"]').set('value', idx);

                    return false;
                });
                
                sel.fireEvent('change');
            }
            
        }
        
        this.last_option_label = this.element.getElement('label.active');

        this.stickOptGroup();
   },
    
    
   stickOptGroup: function()
   {
        var _this = this;
        
        var option_content = this.element.getElement('[data-option-content]');
        
        if (!option_content) {
            return false;
        }

        var option_layout = option_content.getParent();
        
        if (!option_layout) {
            return false;
        }
        

        var bar = $$('.b-bar');
        this.bar_size = (bar)?bar[0].getSize():{y:0};
        if (this.bar_size.y > 0) {
            this.bar_size.y -= 2;
        }       
        
        var footer_top = $('i-footer').getCoordinates().top;
        var start_coords = option_content.getCoordinates();

        window.addEvent('scroll', function() {
            
            var scrollTop = window.getScrollTop();
            var coord = option_content.getCoordinates();
            var layout_size = option_layout.getSize();


            if (footer_top > 0 && footer_top < (scrollTop + coord.height + 100)) {
                option_content.setStyles({
                    'position':'absolute',
                    'top':footer_top - coord.height - 100,
                    //'z-index':'9',
                     'width':layout_size.x//,
                    //'height':sticky_size.y,
                    //'overflow':'visible'
                });
           } else if ((scrollTop > start_coords.top - _this.bar_size.y - 20)) {
                option_content.setStyles({
                    'position':'fixed',
                    'top':_this.bar_size.y + 20,
                    'z-index':'9',
                    'width':layout_size.x,
                    //'height':sticky_size.y,
                    'overflow':'visible'
                });
            } else if((scrollTop + _this.bar_size.y) <= start_coords.top) {
                option_content.setStyles({
                    'top':0,
                    'position':'static',
                    'width':'100%',
                    //'height':'auto',
                    'overflow':'visible'
                });
            }
            
            
        }).fireEvent('scroll');
        
        
        window.addEvent('resize', function(){
            
            var position = option_content.style.position;
            if (position === 'fixed' || position === 'absolute') {
                var layout_size = option_layout.getSize();
                option_content.setStyles({
                    //'top':_this.bar_size.y,
                    'width':layout_size.x
                });
            }
            
            window.fireEvent('scroll');
            
        }).fireEvent('resize');     
        
        return true;
    }
    
});

/**
 * Спомощью фабрики создаем обьекты описанного выше класса 
 * существующие в данный момент на странице
 */
window.addEvent('domready', function() {
    if (typeof window.elements_factory !== "undefined") {
        window.elements_factory.addElements('element-vertical-select','ElementVerticalSelect');
    }
});