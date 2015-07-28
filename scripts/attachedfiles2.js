

var attachedFiles2 = new Class({
    Implements: [Options, Events],
    
    options: {
        'session':      null,
        'files':        [],
        'skipTypes':    '',
        'type':         '', 
        'uid':          0,
        'count':        0,
        'action_delete': 'delete',
        'fileHandler':  '/attachedfiles2.php',
        'hiddenName':   'attaches[]',           //формат имени скрытых полей с идентификаторами файлов
        
        'selectors':    {
            'template':         '.attachedfiles-tpl',
            'error':            '.attachedfiles_error',
            'errortxt':         '.attachedfiles_errortxt',
            'uploadingfile':    '.attachedfiles_uploadingfile',
            'sendbutton':       '.attachedfiles_button',
            'deletingfile':     '.attachedfiles_deletingfile',
            'file':             '.b-file__input',
            
            'uploadedfileRow':  '.attachedfiles_template',
            'uploadedfileIcon': 'i.b-icon',
            'uploadedfileName': 'a.b-icon-layout__link',
            'uploadedfileDel':  '.b-button_admin_del'
        },
        
        'onComplete':   Class.empty,
        'onDelete':     Class.empty,
        'onError':      Class.empty
    },
    
    initialize: function(el, options, attach_session) {
//        el = document.getElement('.' + el);
        if (!el) {
            return false;
        }
        if(!attach_session) attach_session = '';
        this.setOptions(options);
        this.tpl = document.getElement(this.options.selectors.template);
        
        if (!this.tpl) {
            //console.log('Templates not found!')
            return false;
        }
        
        this.id = Number.random(1, 999999) * Number.random(1, 999999);
        
        //console.log(this.id);
        
        this.container = el;
        this.container.set('id', this.id);
        this.container.set('html', this.tpl.get('html'));
        this.container.getElement('input[name^=attachedfiles_session]').set('value', attach_session);
        
        this.el = this.container.getElement('input[type=file]');
        if(this.el) this.el.addEvent('change', this.upload.bind(this));
        
        var files = [];
        if (this.options.files && this.options.files.length != 0) {
            files = this.options.files;
        }
        if (files.length) {
            files.each(function(el) {
                this.addFile(el.id, el.type, el.orig_name, el.path, el.tsize, el.name);
            }.bind(this));
        }
        
        if (!$('attachedfiles_target' + this.id)) {
            iframe = new Element('iframe', {
                'src':         'about:blank',
                'id':          'attachedfiles_target' + this.id,
                'name':        'attachedfiles_target' + this.id,
                'scrolling':   'no',
                'frameborder': '0',
                'styles':   {
                    'width'      : '1px',
                    'height'     : '1px',
                    'visibility' : 'hidden'
                }
            });
            iframe.inject(document.body);
        }
        
        this.container.getElements('.load-spinner').each(function(elm) {
            if(elm.getParent('.b-fon__body_bg_fff')) {
                elm.setProperty('src', '/images/loader-white.gif');
            } else if(elm.getParent('.b-fon__body_bg_f0ffdf')) {
                elm.setProperty('src', '/images/loading-green.gif');
            } else if(elm.getParent('.b-fon__body')) {
                elm.setProperty('src', '/images/load_fav_btn.gif');
            } else {
                elm.setProperty('src', '/images/loader-white.gif');
            }
        }, this);
        
        this.container.store('instance', this);
    },
    
    upload: function() {
        this.form = new Element('form', {
            'action':   this.options.fileHandler,
            'enctype':  'multipart/form-data',
            'method':   'post',
            'target':   'attachedfiles_target' + this.id
        });        
        this.form.wraps(this.container);
        
        new Element('input', {
            'type':     'hidden',
            'name':     'attachedfiles_action',
            'value':    'add'
        }).inject(this.form);
        
        new Element('input', {
            'type':     'hidden',
            'name':     'attachedfiles_formid',
            'value':    this.id
        }).inject(this.form);
        
        new Element('input', {
            'type':     'hidden',
            'name':     'u_token_key',
            'value':    _TOKEN_KEY
        }).inject(this.form);
        
        this.container.getElement(this.options.selectors.error).hide();
        this.container.getElement(this.options.selectors.uploadingfile).show();
        
        this.container.getElement(this.options.selectors.file).setStyle('right', '-9999px');
        this.container.getElement(this.options.selectors.sendbutton).addClass('b-button_disabled');
        
        this.form.submit();
        
        this.container.inject(this.form, 'before');
        this.form.reset();
        this.form.dispose();
        
        try {
            // очищаем информацию о загруженном файле, чтобы можно было повторно загрузить этот же файл
            // если этого не сделать, то при попытки загрузить один и тот же файл второй раз подряд не наступит события change
            this.container.getElement('input[type="file"]').set('value', '');
        } catch(e){};
    },
    
    resetFields: function() {
//        this.el.reset();
    },
    
    raiseError: function(msg) {
        //console.log('error')
        this.container.getElement(this.options.selectors.errortxt).set('html', msg);
        this.container.getElement(this.options.selectors.error).show();
        this.container.getElement(this.options.selectors.file).setStyle('right', '0px');
        this.container.getElement(this.options.selectors.sendbutton).removeClass('b-button_disabled');
    },
    
    addFile: function(id, type, name, path, size, fname) {

        var filerow_tpl = this.container.getElement(this.options.selectors.uploadedfileRow);
        var filerow = filerow_tpl.clone().inject(filerow_tpl.getParent());
        filerow.setStyle('display', '');
        
        filerow.getElement(this.options.selectors.uploadedfileIcon)
            .set('class', 'b-icon b-icon_attach_unknown b-icon_attach_' + type);
        
        var filename = filerow.getElement(this.options.selectors.uploadedfileName)
            .set('html', name)
            .set('target', '_blank')
            .set('href', [___WDCPREFIX, '/', path, fname].join(''));
        
        filename.getParent().set('html', filename.getParent().get('html') + ', ' + size);
        
        new Element('input', {
            'type':     'hidden',
            'name':     this.options.hiddenName,
            'value':    id,
            'class':    'f'+id
        }).inject(this.container, 'top');
        
        filerow.addClass('f'+id);
        
        var del = filerow.getElement(this.options.selectors.uploadedfileDel);
        if(del) del.addEvent('click', this.del.bind(this, id));
    },
    
    complete: function(resp) {
        this.resetFields();
        this.container.getElement(this.options.selectors.uploadingfile).hide();

        if (resp.error) {
            this.raiseError(resp.error);
            this.fireEvent('error', [this, resp]);
            return;
        }
        if(resp.attach_session) { 
            this.container.getElement('input[name^=attachedfiles_session]').set('value', resp.attach_session);
        }
        this.addFile(resp.id, resp.type, resp.orig_name, resp.path, resp.tsize, resp.name);
        
        this.container.getElement(this.options.selectors.file).setStyle('right', '0px');
        this.container.getElement(this.options.selectors.sendbutton).removeClass('b-button_disabled');
        this.fireEvent('complete', [this, resp]);
    }, 
    
    del: function(id) {
        //console.log(id);
        this.form = new Element('form', {
            'action':   this.options.fileHandler,
            'enctype':  'multipart/form-data',
            'method':   'post',
            'target':   'attachedfiles_target' + this.id
        });        
        this.form.wraps(this.container);
        
        new Element('input', {
            'type':     'hidden',
            'name':     'attachedfiles_action',
            'value':    this.options.action_delete
        }).inject(this.form);
        
        new Element('input', {
            'type':     'hidden',
            'name':     'attachedfiles_delete',
            'value':    id
        }).inject(this.form);
        
        new Element('input', {
            'type':     'hidden',
            'name':     'attachedfiles_formid',
            'value':    this.id
        }).inject(this.form);
        
        new Element('input', {
            'type':     'hidden',
            'name':     'u_token_key',
            'value':    _TOKEN_KEY
        }).inject(this.form);
        
        this.container.getElement(this.options.selectors.error).hide();
        
        var del = this.container.getElement(this.options.selectors.deletingfile);
        del.inject(this.container.getElement(this.options.selectors.uploadedfileRow + '.f' + id), 'after');
        this.container.getElement(this.options.selectors.uploadedfileRow + '.f' + id).hide();
        del.show();
        
        this.form.submit();
        
        this.container.inject(this.form, 'before');
        this.form.reset();
        this.form.dispose();
        
        this.fireEvent('delete', [this]);
    }, 
    
    deleted: function(fileid) {
        this.container.getElement(this.options.selectors.deletingfile).hide();
        $$('.f'+fileid).dispose();
    }
});

attachedFiles2.upload_done = function(fid, resp) {
    var el = $(new String(fid));
    
    if (!el) return;
    if (!el.retrieve('instance')) return;
    
    var self = el.retrieve('instance');
    self.complete(resp);
};

attachedFiles2.del_done = function(fid, fileid) {
    var el = $(new String(fid));
    
    if (!el) return;
    if (!el.retrieve('instance')) return;
    
    var self = el.retrieve('instance');
    self.deleted(fileid);
};

