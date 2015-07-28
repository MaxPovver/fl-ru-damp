/**
 * Класс расширенного функционала для попапа
 * 
 * @type Class
 */
var freelancersPreviewEditorPopup = new Class({
    
    //Обязательно наследуемся от базового класса
    Extends: CPopup,
    
    tab_panel: null,
    page_cache: [],
    current_label: null,
    save_btn: null,
    
    initialize: function(p) 
    {
        //Обязательно передаем в родительский класс
        //Это вызов родительского initialize()
        if (!this.parent(p)) {
            return false;
        }
        
        var _this = this;
        
        this.tab_panel = new TabPanel(this.popup_name + 'Tab');
        
        //Обработка события нажания на кнопку сохранить
        this.save_btn = p.getElement('[data-popup-save]');
        if (this.save_btn) {
            this.save_btn.addEvent('click', function() {
                if (!_this.save_btn.hasClass('b-button_disabled')) {
                    _this.show_wait('');
                    _this.saveProcess();
                }
                return false;
            });
        }
        
        this.initPager();
        this.initRadioCheck();
        this.initLinks();
    },
    
    
    //--------------------------------------------------------------------------
    
    saveProcess: function()
    {
        var data = xajax.getFormValues(this.form);
        return xajax_FPEP_saveProcess(data);
    },    
            
      
    //--------------------------------------------------------------------------
            
    initLinks: function()
    {
        var _this = this;
        
        _this.popup.addEvent('showpopup', function(link) {
            
            var pos = link.get('data-preview-pos');
            if (!pos || _this.isWait()) {
                return false;
            }
            
            var pos_element = _this.form.getElement('input[name=pos]');
            if (pos_element) {
                pos_element.set('value', pos);
            }
        });
    },
            
    //--------------------------------------------------------------------------
            
    initRadioCheck: function()
    {
        var _this = this;
        
        var inputs = this.popup.getElements('input[type=radio]');
        if (inputs) {
            inputs.addEvent('change', function(){
                if (_this.current_label) {
                    _this.current_label.removeClass('active');
                }
                
                _this.current_label = this.getParent();
                _this.current_label.addClass('active');
                
                if (_this.save_btn) {
                    _this.save_btn.removeClass('b-button_disabled');
                }
            });
        }        
    },
     
     
    //--------------------------------------------------------------------------        
            
            
    initPager: function()
    {
        var _this = this;
        
        if (this.form) {
            var page_links = this.form.getElements('.b-pager__link');
            if (page_links) {
                page_links.addEvent('click', function(){
                    
                    var href = this.get('href');
                    var params = href.parseQueryString();
                    
                    if (typeof _this.page_cache[href] === "undefined") {
                        _this.show_wait('');
                        xajax_FPEP_getTab(params);
                    } else {
                        _this.showTabContent(params['tab'], _this.page_cache[href]);
                    }
                    
                    return false;
                });
            }
        }        
    },
            

    //--------------------------------------------------------------------------
    

    showTabContent: function(tab, content, param)
    {
        this.page_cache[param] = content;
        this.tab_panel.setTabContent(tab, content);
        this.initPager();
        this.initRadioCheck();
        this.hide_wait();
    }
});