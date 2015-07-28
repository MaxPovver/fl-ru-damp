function search(form_id) {
    this.HINT_TEXT_SEARCH = '';  
    this.SEARCH_ID = 'search-request';
    this.SEARCH_BUTTON_ID = 'search-button';
    //this.SEARCH_EXAMPLE_ID = 'search-example';
    this.SEARCH_ADVANCED_BUTTON = 'search-advanced-button';
    this.SEARCH_ADVANCED_ID = 'advanced-search';    
    this.form = $(form_id);
    
    this.init
    =function() {
        $(this.SEARCH_ID).object = this;
        if($(this.SEARCH_ID).value.length == 0) {
            $(this.SEARCH_ID).set('value', this.HINT_TEXT_SEARCH);
        } else {
            $(this.SEARCH_ID).style.color = '#000'; 
        }
        
        $(this.SEARCH_ID).addEvents({
            focus: function() {
                if(this.value == this.object.HINT_TEXT_SEARCH) {
                    this.value = '';
                    this.style.color = '#000';    
                }
            },
            keydown: function(e) {
                if (e.event.keyCode == 13) {
                    $$('.b-input-hint__label').addClass('b-input-hint__label_hide');
                    this.object.form.submit();    
                }
                
            },
            keyup: function() {
                setLinkSearch(this.object.SEARCH_ID);
            }
        });
        
        if($(this.SEARCH_ID).get("value").length > 0) {
            setLinkSearch(this.SEARCH_ID);
        }
        
        if($(this.SEARCH_BUTTON_ID) != undefined) {
            $(this.SEARCH_BUTTON_ID).object = this;
            $(this.SEARCH_BUTTON_ID).addEvent('click', function(){
                if($(this.object.SEARCH_ID).get('value').trim() == '') {
                    $(this.object.SEARCH_ID).set('value', $('search-hint').get('value'));
                }
                $('search-action').set('value', 'search');
                $$('.b-input-hint__label').addClass('b-input-hint__label_hide');
                this.object.form.submit();    
            });
        }
        
        //$(this.SEARCH_EXAMPLE_ID).object = this;
        //$(this.SEARCH_EXAMPLE_ID).addEvent('click', function() {
        //    $(this.object.SEARCH_ID).focus();
        //    $(this.object.SEARCH_ID).value = this.innerHTML;
        //});
        
        if($(this.SEARCH_ADVANCED_BUTTON) != undefined) {
            $(this.SEARCH_ADVANCED_BUTTON).object = this;
            $(this.SEARCH_ADVANCED_BUTTON).addEvent('click', function(){
                var el = $(this.object.SEARCH_ADVANCED_ID);
                if(el.getStyle('display') == 'none') {
                    $(this.object.SEARCH_ADVANCED_BUTTON).getParent().getParent().getParent().removeClass('last');
                    el.show();
                } else {
                    $(this.object.SEARCH_ADVANCED_BUTTON).getParent().getParent().getParent().addClass('last')
                    el.hide();    
                }
                
            });
        }
    };
    
    this.addUserLimit 
    =function(limit) {
        Cookie.write('seUserLimit', limit, {duration: 356});
        window.location.href = '';
    };
};

function addUserLimit(type, limit, reload) {
    if(reload == undefined) reload = 1;
    Cookie.write('seUserLimit', limit, {duration: 356});
    if(reload == 1) window.location.href = '/search?' + type;    
}

function setLinkSearch(SEARCH_ID) {
    var value = $(SEARCH_ID).get('value');
    $$('ul.search-tabs a').each(function(elm) {
        var href = elm.get('href');
        if(href.indexOf('&search_string') > 0) {
            href = href.substr(0, href.indexOf('&search_string'));
        }
        value = value.replace(/\+/gi, "%2B");
        value = value.replace(/#/gi, "%23");
        elm.set('href', href + '&search_string=' + value + '&action=search');
    }); 
}
