var Digest = new Class({
    Implements: [Options],
    
    options: {
        'is_wysiwyg' : false,
        'is_create'  : false,
        'is_add_fld' : false,
        'main'       : true,
        'num'        : 0,
        'element'    : 1,
        'block'      : '',
        'name'       : '',
        'action'     : ''
    },
    
    initialize: function(options) {
        this.setOptions(options);
        
        if(this.options.name) {
            this.options.block = $$('.' + this.options.name)[this.options.num];
        }
        
        if(this.options.block) {
            this.options.action = this.options.block.getElement('.block_create_action a'); 
            if(this.options.main) {
                this.options.action.addEvent('click', this.create.bind(this));
            } else {
                this.options.action.addEvent('click', this.removeBlock); 
            }
        }
        
        if(this.options.is_create == true) {
            this.options.action.removeClass('b-layout__txt_hide');
        } else {
            this.options.action.addClass('b-layout__txt_hide');
        }
        
        if(this.options.is_add_fld == true) {
            this.options.block.getElement('.block_add_fld a').addEvent('click', function(){
                var clone = this.getParent('.b-fon__body').getElement('tr').clone();
                clone.getElement('td').set('html', '');
                clone.getElement('.b-input-hint__label').destroy();
                this.getParent('.b-fon__body').getElement('table').grab(clone);
                var inp = clone.getElement('.b-combo__input input');
                new CDynamicInput(clone.getElement('.b-combo__input'));
                inp.set('value', '').removeClass('b-combo__input-text_color_a7');
                clone.getElement('.b-button_admin_del').removeClass('b-button_hide');
                clone.getElement('.b-button_admin_del').addEvent('click', function(){
                    this.getParent('tr').destroy();
                });
            });
            this.options.block.getElements('.b-button_admin_del').addEvent('click', function(){
                this.getParent('tr').destroy();
            });
        }
    },
    
    create: function() {
        if(this.options.is_wysiwyg) {
            var editor = CKEDITOR.instances[this.options.block.getElement('textarea').get('id')];
            editor.destroy();
        }
        var clone = this.options.block.clone();
        this.options.block.grab(clone, 'after');
        clone.getElement('.ClassMain').set('value', '0');
        clone.getElement('input[type=checkbox]').set('checked', false);
        clone.getElements('.b-layout__tr input').set('value', '');
        clone.getElement('.b-layout__tr textarea').set('value', '');
        clone.getElement('.upButton').cloneEvents(this.options.block.getElement('.upButton'));
        clone.getElement('.downButton').cloneEvents(this.options.block.getElement('.downButton'));
        clone.getElement('.b-check__label').set('html', 'Дополнительный блок');
        clone.getElement('.block_create_action a').removeEvents('click');
        clone.getElement('.block_create_action a').addEvent('click', this.removeBlock);
        clone.getElement('.block_create_action a').addClass('b-layout__link_dot_c10600');
        clone.getElement('.block_create_action a').set('html', 'Удалить блок');
        setInitPosition();
        
        if(this.options.is_wysiwyg && editor) {
            this.options.element++;
            var split  = editor.name.split('_');
            split[split.length-1] = this.options.element;
            idText = split.join('_');
            clone.getElement('.b-layout__tr textarea').set('id', idText);
            
            CKEDITOR.replace(editor.name);
            CKEDITOR.replace(idText);
        }
    },
    
    removeBlock: function() {
        if(this.getParent('span').getElement('textarea')) {
            var editor = CKEDITOR.instances[this.getParent('span').getElement('textarea').get('id')];
            editor.destroy();
        }
        this.getParent('span').destroy();
        setInitPosition();
    }
});


function initNaviButton(elm) {
    if(elm == undefined) {
        var content = $(document.body);
    } else {
        var content = $(elm);
    }
    
    content.getElements('.upButton').addEvent('click', function() {
        if(this.getParent('span').getPrevious('span.BlockList')) {
            if(this.getParent('span').getElement('textarea')) {
                var editor = CKEDITOR.instances[this.getParent('span').getElement('textarea').get('id')];
                editor.destroy();
            }
            this.getParent('span').getPrevious('span.BlockList').grab(this.getParent('span'), 'before');
            setInitPosition();
            if(editor) {
                CKEDITOR.replace(editor.name);
            }
        }
    });
    
    content.getElements('.downButton').addEvent('click', function() {
        if(this.getParent('span').getNext('span.BlockList')) {
            if(this.getParent('span').getElement('textarea')) {
                var editor = CKEDITOR.instances[this.getParent('span').getElement('textarea').get('id')];
                editor.destroy();
            }
            this.getParent('span').getNext('span.BlockList').grab(this.getParent('span'), 'after');
            setInitPosition();
            if(editor) {
                CKEDITOR.replace(editor.name);
            }
        }
    });
}

function initCheckSelect(elm) {
    if(elm == undefined) {
        var content = $(document.body);
    } else {
        var content = $(elm);
    }
    
    content.getElements('.check_select_block').addEvent('click', function() {
        if($('error_blocks_select')) $('error_blocks_select').destroy();
    });
}

function setInitPosition() {
    var i = 1;
    $$('input[name^=position]').each(function(elm){
        //console.debug(elm.name, elm.value, i);
        elm.value = i;
        i++;
    });
}

function checkRecipients() {
    if($('chk_frl').checked && $('chk_emp').checked) {
        $('count_recipient').set('html', ALL_CNT);
    } else if($('chk_frl').checked) {
        $('count_recipient').set('html', FRL_CNT);
    } else if($('chk_emp').checked) {
        $('count_recipient').set('html', EMP_CNT);
    } else {
        $('count_recipient').set('html', 0);
    }
}