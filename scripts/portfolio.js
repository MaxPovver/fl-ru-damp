var portfolio = {
    editContent: function( xAjaxFunc, sId, objParams ) {
        if( objParams instanceof Element ) {
            objParams = objParams.toQueryString(); //this.formInit(objParams);
        }
        window['xajax_'+xAjaxFunc]( sId, objParams );
    },
    
    initPopup: function(cls) {
        this.popupPosition('#' + cls);
        $(cls).getElements('.cls-close_popup').addEvent('click', function() {
            $(cls).addClass('b-shadow_hide');
            //$(cls).getParent().dispose();
        });
    },
            
    initExpandLink: function(cls) {
        $(cls).getElements('.expand-link-descr').addEvent('click', function(){
            $('descr_block').getPrevious('div').setStyle('display', 'block');
            $('descr_block').getParent('.b-layout__tr').getPrevious('.b-layout__tr').setStyle('display', 'table-row');
            $('descr_block').getParent('td').getPrevious('td').getElement('div').setStyle('display', 'block');
            $('descr_block').setStyle('display', 'none');
            $('work_descr').focus();
            JSScroll($('work_descr'));
            return false;
        });
        
        $(cls).getElements('.expand-link').addEvent('click', function(){
            this.getParent('div').getPrevious('div').setStyle('display', 'block');
            this.getParent('.b-layout__tr').getPrevious('.b-layout__tr').setStyle('display', 'table-row');
            this.getParent('div').getParent('td').getPrevious('td').getElement('div').setStyle('display', 'block');
            this.getParent('div').setStyle('display', 'none');
            return false;
        });
        $(cls).getElements('.expand-link-file').addEvent('click', function() {
            this.getParent('div').getPrevious('div').setStyle('display', 'block');
            this.getParent('div').setStyle('display', 'none');
            return false;
        });
        
        
        $(cls).getElements('.toggle-type-preview').addEvent('click', function() {
            $$('.preview-work-block .b-prev__dd').addClass('b-prev__dd_hide');
            $$('.preview-work-block .b-prev__dt').removeClass('b-prev__dt_active');
            
            this.getParent().addClass('b-prev__dt_active');
            this.getParent().getNext('dd').removeClass('b-prev__dd_hide');
            
            var type = $(this).get('data-type');
            $('work_preview_type').set('value', type);
        });
    },       
            
    popupPosition: function(cls) {
        if(cls == undefined) cls = '.b-shadow_center';
        $$(cls).each(function (popup_elm) {
            var winSize = $(document).getSize();
            var scrollSize = $(document).getScroll();
            var elemSize = popup_elm.getSize();
            popup_elm.setPosition({
                y: scrollSize.y + 150, //- ( winSize.y - ( elemSize.y + 200 )  )
                x: ( winSize.x / 2 ) - (elemSize.x / 2 )
            });
        });
    },
            
    viewError : function(errors, idname) {
        $(idname).getElement('.block_errors').set('html', '');
 
        for(var name in errors) {
            if(name == 'portf_text') {
                $(idname).getElement('textarea[name=' + name + ']')
                                          .getParent()
                                          .addClass("b-textarea_error");
            } else {
                $(idname).getElement('input[name=' + name + ']')
                                          .getParent()
                                          .addClass("b-combo__input_error");
            }
            
            html  = '<div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">';
            html += '<span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span> ' + errors[name];
            html += '</div>';
            
            err = new Element('div', {'class':'b-fon b-fon_width_full b-fon_padbot_17 error_' + name, 'html': html});
            
            $(idname).getElement('.block_errors').grab(err);
        }
        
        $(idname).removeEvents('focus');
        $(idname).getElements('textarea, input').addEvent('focus', function() {
            $(idname).getElements('.b-button_rectangle_color_green').removeClass('b-button_rectangle_color_disable');
            var name = $(this).get('name');
            $$('.error_'+name).dispose();
            if( $(this).get('tag') == 'textarea' ) {
                $(this).getParent().removeClass('b-textarea_error');
            } else {
                $(this).getParent().removeClass('b-combo__input_error');
            }
        }); 
    }, 
            
    setPosition: function(from_id, after_id, action) {
        if(action == undefined) action = 'after';
        if(after_id == 0) {
            $('portfolio_info').getNext().grab($('professions_works_' + from_id), action);
        } else {
            $('professions_works_' + after_id).grab($('professions_works_' + from_id), action);
        }
    },
    
    formInit: function(form) {
        return form.toQueryString();
    }        
}
function clearHTMLText(text) {
    text = text.replace(/[\r\n]+/gi, "<br>");
    text = text.replace(/<.*?script\b([^>]*)>/gi, "");
    text = text.replace(/<(.[A-Za-z0-9_]?)\s([^>]*)>/gi, function(element, tag, attributes) {
        return '<' + tag + '>';
    });
    return text;
}

function clearHTMLTags(text) {
    text = text.replace(/<(.*?)([^>]*)>/gi, function(element, tag, attributes) {
        return '';
    });
    return text;
}

function htmlschars(text) {
   var chars = Array("<", ">", "'");
   var replacements = Array("&lt;", "&gt;", "&#039;");
   for (var i=0; i<chars.length; i++)
   {
       var re = new RegExp(chars[i], "gi");
       if(re.test(text))
       {
           text = text.replace(re, replacements[i]);
       }
   }
   return text;
}

window.addEvent('domready', function() {
    var elm = new Element('span', {id:'popup_loader'});
    $(document.body).grab(elm, 'top');
});