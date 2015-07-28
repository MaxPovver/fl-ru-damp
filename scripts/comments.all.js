/*
 * Для разделов Статьи и интервью
 * зависит от mootools-forms.js
 */

var btn_add = '/images/btn-add.png';
var btn_cancel = '/images/btn-remove3.png';
var MAX_FILE_COUNT = 10;

FileUpload = new Class({
    options: {
        url: null,
        beforeUpload: Class.empty,
        onUpload: Class.empty,
        onFailure: Class.empty,
        showPreloader: true,
        preloadImage: '/images/load_fav_btn.gif',
        debug: true
    },
    initialize: function(element, options) {
        this.element = $(element);
        if(!this.element) {
            return false;
        }
        this.setOptions(options);
        this.element.addEvents({
            'change': this._sendFile.pass(this.element, this)
        });
    },
    _sendFile: function(input) {
        _frm = new Element('form', {
            'action' : this.options.url,
            'method' : 'post',
            'enctype' : 'multipart/form-data',
            'encoding' : 'multipart/form-data'
        });
        _frm.wraps(input);

        _rnd = 'frame' + $random(1,99999);

        if($chk($(input.retrieve('frame_id')))) $(input.retrieve('frame_id')).dispose();

        fr = this._getFrame(_rnd);
        input.store('frame_id', _rnd);
        fr.store('input', input);

        fr.addEvents({
            load : this._frameLoad.pass([fr, input], this)
        });

        _frm.set('target', fr.get('id'));
        this.fireEvent('beforeUpload', [input, this]);

        if(this.options.showPreloader) {
            if(_frm.getNext()) _frm.getNext().setStyle('display', 'none');
            loader = new Element('img', {
                'class' : 'upfile-preloader',
                'src' : this.options.preloadImage
            });
            loader.inject(_frm, 'after');
        }

        _frm.submit();
        input.set('disabled', true);
    },
    _getFrame: function(id) {
        if($chk($(id))) $(id).dispose();

        fr = new IFrame({
            ulr : 'about:blank',
            'id' : id
        });
        if(!this.options.debug) {
            fr.setStyles({
                width: 1,
                height: 1,
                'position' : 'absolute',
                'left' : -100
            });
        }
        fr.inject(document.body);

        return fr;
    },
    _frameLoad: function(frame, inp) {
        frame = $(frame);inp = $(inp);

        inp.set('disabled', false);

        _doc = (frame.contentDocument || frame.contentWindow);
        if (_doc.document) _doc = _doc.document;

        resp = _doc.body.innerHTML;

        if( resp.length == 0 ) return false;

        inp.inject(inp.getParent(), 'before');
        if(inp.getNext() && inp.getNext().tagName.toLowerCase() == 'form') inp.getNext().dispose();

        if(inp.getNext('img.upfile-preloader')) {
            inp.getNext('img.upfile-preloader').dispose();
            if(inp.getNext()) inp.getNext().setStyle('display', 'inline-block');
        }

        this.fireEvent('onUpload', [resp, inp, frame]);

        if(!this.options.debug)
            (function() {frame.dispose()}).delay(100);
    }
});
FileUpload.implement(new Options);
FileUpload.implement(new Events);


Element.Events.hashchange = {
    onAdd: function(){
        var hash = self.location.hash;

        var hashchange = function(){
            if (hash == self.location.hash) return;
            else hash = self.location.hash;

            var value = (hash.indexOf('#') == 0 ? hash.substr(1) : hash);
            
            window.fireEvent('hashchange', value);
            document.fireEvent('hashchange', value);
        };

        if ("onhashchange" in window){
            window.onhashchange = hashchange;
        } else {
            hashchange.periodical(50);
        }
    }
};


function commentsAllDomready() {
    var fSubmited = false;

    // отправка формы создания/редактирования коммента
    $$('a.cl-form-submit.add-comment').addEvent('click', function() {
        f = this.getParent('form');

        if (!fSubmited) {
            fSubmited = true;
            f.submit();
        }

        return false;
    });
    $$('a.cl-form-submit.edit-comment').addEvent('click', function() {
        f = this.getParent('form');
        f.submit();

        return false;
    });
    
    // стрелочки =)
    $$('a.u-anchor').addEvent('click', function() {
        id = this.get('href').replace(/.*?#(.*?)$/, '$1').replace('_', '__');
        
        $(id).getElement('.d-anchor').set('href', this.getParent('.cl-li-in').getElement('.cl-anchor').get('href'));
        $(id).getElement('.cl-li-in').addClass('cl-li-this');

        new Fx.Scroll(window).toElement(id);
        return false;
    });
    $$('a.d-anchor').addEvent('click', function() {
        this.getParent('div.cl-li-in').removeClass('cl-li-this');
        id = this.get('href').replace(/.*?#(.*?)$/, '$1').replace('_', '__');

        new Fx.Scroll(window).toElement(id);
        return false;
    });
    // стрелочки для новых комментариев
    $$('a.b-post__arrow_up').addEvent('click', function() {
        var id = this.get('href').replace(/.*?#(.*?)$/, '$1').replace('_', '__');
        
        $(id).getElement('.b-post__arrow_bot').
            set('href', this.getParent('.cl-li').getElement('.b-post__anchor').get('href')).
            setStyle('display', '');
        $(id).getElement('.b-post__body').addClass('b-fon__body_bg_f0ffdf').addClass('b-fon__body_bordbot_dfedcf');

        JSScroll($(id), true);
        //new Fx.Scroll(window).toElement(id);
        return false;
    });
    $$('a.b-post__arrow_bot').addEvent('click', function() {
        this.getParent('li.cl-li').getElement('.b-post__body').removeClass('b-fon__body_bg_f0ffdf').removeClass('b-fon__body_bordbot_dfedcf');
        this.getParent('li.cl-li').getElement('.b-post__arrow_bot').setStyle('display', 'none');
        var id = this.get('href').replace(/.*?#(.*?)$/, '$1').replace('_', '__');

        JSScroll($(id), true);
        //new Fx.Scroll(window).toElement(id);
        return false;
    });
    
    $each($$('li.cl-li'), function(el) {
        if(el.getElements('li.cl-li').length > 3) {
            if(el.getElement('a.cl-thread-toggle'))
                el.getElement('a.cl-thread-toggle').setStyle('display', '');
        }
    });
    
    $each($$('li.cl-li.first'), function(el) {
        if(el.hasClass('cl-li-hidden-c') && el.getElements('li.cl-li').length > 1) {
            if(el.getElement('a.cl-thread-toggle'))
                el.getElement('a.cl-thread-toggle').setStyle('display', '');
        }
    });
    
    if($('comm-show-form'))
        $('comm-show-form').addEvent('click', function() {
            $$('.cl-form-cancel')[0].fireEvent('click');
            var frm = document.getElement('div.cl-form form');
            JSScroll(document.getElement('div.cl-form'));
            //new Fx.Scroll(window).toElement(document.getElement('div.cl-form'));
            return false;
        });
    $$('.cl-form-btns a.cl-form-cancel').addEvent('click', function() {
        if(CKEDITOR && CKEDITOR.instances.textarea_comments) {
            var editor = CKEDITOR.instances.textarea_comments;
            editor.destroy();
        }
        var comment = document.getElement('#cl>ul.cl-ul');
        if(comment != null) {
            this.getParent('div.cl-form').inject(document.getElement('#cl>ul.cl-ul'), 'after');
        }
        frm = document.getElement('div.cl-form form');

        if(frm.getElement('textarea') && frm.getElement('textarea').retrieve('MooEditable')) {
            frm.getElement('textarea').retrieve('MooEditable').detach();
        }

        formReset(frm);
        $$('div.cl-form .b-fon__body').setStyle('margin-left', '');
        $$('a.cl-form-submit.add-comment').setStyle('display', 'inline-block');
        $$('a.cl-form-submit.edit-comment').setStyle('display', 'none');
        
        if (frm.getElement('textarea').retrieve('MooEditable')) {
            frm.getElement('textarea').retrieve('MooEditable').attach();
        }
        if(CKEDITOR && editor) {
            CKEDITOR.replace(editor.name);
        }
        return false;
    });
    
    Comments();


    $$('.cl-form .form-files-list li input[type=image]').removeEvents('click');
    $$('.cl-form .form-files-list li input[type=image]').addEvent('click', function() {

        try {
            if(this.value == '+') {
                att = 0;
                att = $$('.cl-form-files .form-files-added input[name^=attaches]').length;

                if($$('.cl-form .form-files-list input[type=file]').length+att >= MAX_FILE_COUNT) {
                    return false;
                }

                l = this.getParent('li').clone();
                l.inject(this.getParent('ul.form-files-list'));
//                l.getElement('input[type=file]').set('value', '');
                _tmp = l.getElement('input[type=file]');
                im = new Element('input', {
                    'type' : 'file',
                    'name' : _tmp.get('name'),
                    'class' : _tmp.get('class'),
                    'size' : _tmp.get('size')
                });
                im.cloneEvents(_tmp);
                l.getElement('input[type=file]').dispose();
                im.inject(l.getElement('input[type=image]'), 'before');
                l.getElement('input[type=image]').cloneEvents(this);

                if($$('.cl-form .form-files-list input[type=file]').length >= MAX_FILE_COUNT)
                    l.getElement('input[type=image]').setStyle('display', 'none');

                this.set('src', btn_cancel);
                this.set('value', '-');
            } else {
                if($$('.cl-form .form-files-list input[type=file]').length <= 10) {
                    $$('.cl-form .form-files-list li input[type=image]').setStyle('display', 'inline-block');
                }

                this.getParent('li').dispose();
            }
        } catch (e) {
            alert(e);
        }

        return false;
    });


    if($$('div.errorBox').length) {
        ff =  $$('div.errorBox')[0];
        
        if (ff.getParent('div.cl-form')) {
            $$('div.errorBox')[0].getParent('div.cl-form').scrollIntoView();
        }
    }
}


window.addEvent('domready', function() {
    commentsAllDomready();
});



function commentsThreadState(aid, cid, type) {
    if(!type) return;
    cname = type + 'Threads';

    c = Cookie.read(cname);
    if(c) c = JSON.decode(c);
    c = $H(c);

    arr = c.get(aid);
    if(cid != 'hide' && cid != 'show') {
        if(arr) {
            arr = arr.split(',');
            if(arr.contains(cid.toString())) {
                arr.erase(cid.toString());
            } else {
                arr.push(cid.toString());
            }
            c.set(aid, arr.join(','));
        } else {
            c.set(aid, cid.toString());
        }
    } else {
        c.set(aid, cid);
    }

    Cookie.write(cname, JSON.encode(c), {duration: 30});
}

function Comments() {
    if(!$('cl')) return false;

    if($('cl').getElement('a.cl-hide-all'))
        $('cl').getElement('a.cl-hide-all').addEvent('click', function(){
            $each($$('li.cl-li.first'), function(el) {
                if(el.getElements('li.cl-li').length > 0) {
                    el.getElement('a.cl-thread-toggle').setStyle('display', '');
                }
            });
            if(SNAME) commentsThreadState(ARTICLE, 'hide', SNAME);
            $('cl').getElements('li.cl-li').addClass('cl-li-hidden-c');
            $('cl').getElements('a.cl-thread-toggle').set('text', 'Развернуть ветвь');
            $(this).removeClass('lnk-dot-666');
            $(this).addClass('lnk-dot-999');
            $('cl').getElement('a.cl-show-all').removeClass('lnk-dot-999');
            $('cl').getElement('a.cl-show-all').addClass('lnk-dot-666');
            return false;
        });

    if($('cl').getElement('a.cl-show-all'))
        $('cl').getElement('a.cl-show-all').addEvent('click', function(){
            $each($$('li.cl-li.first'), function(el) {
                if(el.getElements('li.cl-li').length < 3) {
                    el.getElement('a.cl-thread-toggle').setStyle('display', 'none');
                }
            });
            if(SNAME) commentsThreadState(ARTICLE, 'show', SNAME);
            $('cl').getElements('li.cl-li').removeClass('cl-li-hidden-c');
            $('cl').getElements('a.cl-thread-toggle').set('text', 'Свернуть ветвь');
            $(this).removeClass('lnk-dot-666');
            $(this).addClass('lnk-dot-999');
            $('cl').getElement('a.cl-hide-all').removeClass('lnk-dot-999');
            $('cl').getElement('a.cl-hide-all').addClass('lnk-dot-666');
            return false;
        });

    if($('cl').getElements('a.cl-thread-toggle'))
        $('cl').getElements('a.cl-thread-toggle').addEvent('click', function(){
            var t = $(this).getParent('li.cl-li');
            if(t.hasClass('cl-li-hidden-c')) {
                if(t.getElements('li.cl-li').length < 3) {
                    t.getElements('a.cl-thread-toggle').setStyle('display', 'none');
                }
                t.removeClass('cl-li-hidden-c');
                t.getElements('li.cl-li').removeClass('cl-li-hidden-c');
                t.getElements('a.cl-thread-toggle').set('text', 'Свернуть ветвь');
                //$(this).set('text', 'Свернуть ветвь');
                if(SNAME) commentsThreadState(ARTICLE, t.get('id').replace(/c__(\d+)$/, '$1').toInt(), SNAME);
            } else {
                t.addClass('cl-li-hidden-c');
                t.getElements('a.cl-thread-toggle').set('text', 'Развернуть ветвь');
                //$(this).set('text', 'Развернуть ветвь');
                if(SNAME) commentsThreadState(ARTICLE, t.get('id').replace(/c__(\d+)$/, '$1').toInt(), SNAME);
            }
            return false;
        });
}

function commentAddNew(el) {
    el = $(el);

    frm = document.getElement('div.cl-form form');

    if(frm.getElement('textarea') && frm.getElement('textarea').retrieve('MooEditable')) {
        frm.getElement('textarea').retrieve('MooEditable').detach();
    }
    
    if(CKEDITOR && CKEDITOR.instances.textarea_comments) {
        var editor = CKEDITOR.instances.textarea_comments;
        editor.destroy();
    }
    formReset(frm);
    $$('div.cl-form .b-fon__body').setStyle('margin-left', '30px');
    $$('a.cl-form-submit.add-comment').setStyle('display', 'inline-block');
    $$('a.cl-form-submit.edit-comment').setStyle('display', 'none');
    $$('.cl-form-cancel').setStyle('display', 'inline-block');

    if(document.getElement('ul.cl-ul div.cl-form form')) {
        $$('div.cl-form .errorBox').dispose();
    }
    
    $$('div.cl-form').inject(el.getParent('div.b-post'), 'after');
    $$('div.cl-form').setStyle('display', 'block');

    $$('div.cl-form input[name=cmtask]').set('value', 'add');
    $$('div.cl-form input[name=parent_id]').set('value', el.getParent('li.cl-li').id.replace('c__', ''));
    
    if (frm.getElement('textarea').retrieve('MooEditable')) {
        frm.getElement('textarea').retrieve('MooEditable').attach();
    }
    if(CKEDITOR && editor) {
        CKEDITOR.replace(editor.name);
    }
    return false;
}

function commentAdd(el) {
    el = $(el);

    frm = document.getElement('div.cl-form form');

    if(frm.getElement('textarea') && frm.getElement('textarea').retrieve('MooEditable')) {
        frm.getElement('textarea').retrieve('MooEditable').detach();
    }
    if(CKEDITOR && CKEDITOR.instances.textarea_comments) {
        var editor = CKEDITOR.instances.textarea_comments;
        editor.destroy();
    }
    formReset(frm);
    $$('a.cl-form-submit.add-comment').setStyle('display', 'inline-block');
    $$('a.cl-form-submit.edit-comment').setStyle('display', 'none');
    $$('.cl-form-cancel').setStyle('display', 'inline-block');

    if(document.getElement('ul.cl-ul div.cl-form form')) {
        $$('div.cl-form .errorBox').dispose();
    }

    $$('div.cl-form').inject(el.getParent('div.cl-li-in'), 'after');
    $$('div.cl-form').setStyle('display', 'block');

    $$('div.cl-form input[name=cmtask]').set('value', 'add');
    $$('div.cl-form input[name=parent_id]').set('value', el.getParent('li.cl-li').id.replace('c__', ''));
    if(CKEDITOR && editor) {
        CKEDITOR.replace(editor.name);
    }
    if (frm.getElement('textarea').retrieve('MooEditable')) {
        frm.getElement('textarea').retrieve('MooEditable').attach();
    }
    return false;
}

function commentEditNew(el, sname, id) {
    el = $(el);

    frm = document.getElement('div.cl-form form');

    if(frm.getElement('textarea') && frm.getElement('textarea').retrieve('MooEditable')) {
        frm.getElement('textarea').retrieve('MooEditable').detach();
    }
    if(CKEDITOR && CKEDITOR.instances.textarea_comments) {
        var editor = CKEDITOR.instances.textarea_comments;
        editor.destroy();
    }
    formReset(frm);
    $$('a.cl-form-submit.edit-comment').setStyle('display', 'inline-block');
    $$('a.cl-form-submit.add-comment').setStyle('display', 'none');
    $$('div.cl-form .errorBox').dispose();
    $$('.cl-form-cancel').setStyle('display', 'inline-block');
    
    $$('div.cl-form').inject(el.getParent('div.b-post'), 'after');
    $$('div.cl-form').setStyle('display', 'block');
    $$('div.cl-form textarea', 'div.cl-form inpt').set('disabled', 'true');

    //id = el.getParent('li.cl-li').id.replace('c_', '');


    xajax_GetComment(sname, id);
   
    return false;
}


function commentEdit(el, sname, id) {
    el = $(el);

    frm = document.getElement('div.cl-form form');

    if(frm.getElement('textarea') && frm.getElement('textarea').retrieve('MooEditable')) {
        frm.getElement('textarea').retrieve('MooEditable').detach();
    }
    formReset(frm);
    $$('a.cl-form-submit.edit-comment').setStyle('display', 'inline-block');
    $$('a.cl-form-submit.add-comment').setStyle('display', 'none');
    $$('div.cl-form .errorBox').dispose();
    $$('.cl-form-cancel').setStyle('display', 'inline-block');

    $$('div.cl-form').inject(el.getParent('div.cl-li-in'), 'after');
    $$('div.cl-form').setStyle('display', 'block');
    $$('div.cl-form textarea', 'div.cl-form inpt').set('disabled', 'true');

    //id = el.getParent('li.cl-li').id.replace('c_', '');


    xajax_GetComment(sname, id);

    return false;
}

function commentEditCallback(msg, attaches) {
    $$('div.cl-form textarea', 'div.cl-form inpt').set('disabled', '');
    $$('div.cl-form textarea').set('value', msg.msgtext);

    $$('div.cl-form input[name=cmtask]').set('value', 'edit');
    $$('div.cl-form input[name=parent_id]').set('value', msg.id);

    if(msg.yt) {
        toggleYoutube();
        $$('div.cl-form input[name=yt_link]').set('value', msg.yt);
    }

    has_attach = (($type(attaches) == 'object' && $H(attaches).getLength() > 0)
                    || ($type(attaches) == 'array' && attaches.length > 0));

    if(attaches && has_attach) {
        tpl = $('cl').getElement('.form-files-added.tpl').clone(false);
        tpl.inject($('cl').getElement('.cl-form-files'), 'top');
        tpl.setStyle('display', 'block');
        $each(attaches, function(file) {
            li = $('cl').getElement('.form-files-added.tpl li').clone();
            li.getElement('input').set('value', file.id);

            li.getElement('a:last-child').set('html', file.fname);
            li.getElements('img').cloneEvents($('cl').getElement('.form-files-added.tpl img'));
            li.inject(tpl);
        });
        toggleFiles();
        initFilesortBtns(tpl);

        if(attaches.length >= MAX_FILE_COUNT) {
            $$('ul.form-files-list input').set('disabled', true);
            $$('ul.form-files-list input[type=image]').setStyle('display', 'none');
        }
    }
    if(CKEDITOR && frm.getElement('textarea').get('id') == 'textarea_comments') {
        CKEDITOR.replace(frm.getElement('textarea').get('id'));
    }
    if (frm.getElement('textarea').retrieve('MooEditable')) {
        frm.getElement('textarea').retrieve('MooEditable').attach();
    }
}


function formReset(frm) {
    if(!frm) return false;
    frm.getElement('input[name=parent_id]').set('value', '');
    frm.getElement('input[name=cmtask]').set('value', 'add');
    frm.reset();
    frm.getElement('input[name=u_token_key]').set('value', _TOKEN_KEY);
    frm.getElement('textarea[name=cmsgtext]').set('value', '');
    if(frm.getElements('.errorBox'))
        frm.getElements('.errorBox').dispose();
    if($$('.form-files-list li input[value=-]').length > 0)
        $$('.form-files-list li input[value=-]').fireEvent('click');
    $$('.cl-form-files ul.form-files-added').dispose();
    $$('.cl-form-cancel').setStyle('display', 'none');
    $$('ul.form-files-list input').set('disabled', false);
    toggleFiles(true);
    toggleYoutube(true);
}

function toggleFiles(hide) {
    el = document.getElement('.cl-form .cl-form-files');
    el.setStyle('display', el.getStyle('display') == 'none' && !hide ? '' : 'none');
}

function toggleYoutube(hide) {
    el = document.getElement('.cl-form .cl-form-video');
    el.setStyle('display', el.getStyle('display') == 'none' && !hide ? '' : 'none');
}

function articlesFileInput(el) {
    el = $(el);
    new FileUpload(el, {
        url: '?task=upload',
        debug: false,
        onUpload: function(resp, inp) {
            resp = JSON.decode(resp);
            if(resp && resp.success) {
                if(!inp.getParent().getElement('input[name=attached]')) {
                    i = new Element('input', {
                        'type' : 'hidden',
                        'name' : 'attached',
                        'value' : resp.file.id
                    });
                    i.inject(inp.getParent());
                } else {
                    inp.getParent().getElement('input[name=attached]').set('value', resp.file.id);
                }
            } else {
                inp.getParent().getElement('input[name=attach]').set('value', '');
                if(inp.getParent().getElement('input[name=attached]'))
                    inp.getParent().getElement('input[name=attached]').set('value', '');
                alert(resp.errorMessage);
            }
        }
    });
}
var is_vote = 0;
function RateComment(sname, id, dir, el) {
    if(is_vote == 1) return false;
    el = $(el);
    
    if(el.getElement('img') != undefined) {
        if(el && el.getElement('img').get('src').contains('-dis')) {
            return false;
        }
    } else if(el.hasClass('b-button_disabled')) {
       return false; 
    } 
    is_vote = 1;
    xajax_RateComment(sname, id, dir);
    return false;
}

function RateCommentCallbackNew(id, rate_val) {
    if(!$chk($('rate_' + id))) {
        return false;
    }
    rval = $('rate_' + id).getElement('span');
    vl = rval.get('html').toInt() + rate_val;
    if(vl >= 0) {
        rval.removeClass('b-voting__mid_color_green');
        rval.removeClass('b-voting__mid_color_red');
    } else {
        rval.addClass('b-voting__mid_color_red');
    }
    if(vl >= 1) rval.addClass('b-voting__mid_color_green');

    rval.set('html', (vl > 0 ? '+' : '') + vl);

    lnks = $('rate_' + id).getElements('a');
    
    if(rate_val < 0) {
        if(lnks[0].hasClass('b-button_poll_nopointer')) {
            lnks[0].removeClass('b-button_poll_nopointer');
            lnks[0].removeClass('b-button_disabled');
            lnks[0].addClass('b-button_active');
        } else {
            lnks[1].addClass('b-button_poll_nopointer');
            lnks[1].addClass('b-button_disabled');
            lnks[1].removeClass('b-button_active');
        }
    }
    if(rate_val > 0) {
        if(lnks[1].hasClass('b-button_poll_nopointer')) {
            lnks[1].removeClass('b-button_poll_nopointer');
            lnks[1].removeClass('b-button_disabled');
            lnks[1].addClass('b-button_active');
        } else {
            lnks[0].addClass('b-button_poll_nopointer');
            lnks[0].addClass('b-button_disabled');
            lnks[0].removeClass('b-button_active');
        }
    }
    
    is_vote = 0;
    return false;
}

function RateCommentCallback(id, rate_val) {
    if(!$chk($('rate_' + id))) {
        return false;
    }
    rval = $('rate_' + id).getElement('span');
    vl = rval.get('html').toInt() + rate_val;
    if(vl >= 0) {
        rval.removeClass('pr-minus');
        rval.removeClass('pr-plus');
    } else {
        rval.addClass('pr-minus');
    }
    if(vl >= 1) rval.addClass('pr-plus');

    rval.set('html', (vl > 0 ? '+' : '') + vl);

    lnks = $('rate_' + id).getElements('a img');
    if(rate_val < 0) {
        if(lnks[1].get('src').contains('-dis')) {
            lnks[1].set('src', '/images/btn-urate.png');
        } else {
            lnks[0].set('src', '/images/btn-drate-dis.png');
        }
    }
    if(rate_val > 0) {
        if(lnks[0].get('src').contains('-dis')) {
            lnks[0].set('src', '/images/btn-drate.png');
        } else {
            lnks[1].set('src', '/images/btn-urate-dis.png');
        }
    }
    is_vote = 0;
    return false;
}




var btn_sort_up = '/images/arrow2-top';
var btn_sort_down = '/images/arrow2-bottom';
function initFilesortBtns(u) {
    if(!$$('.ffa-sort')) return false;
//    u = el.getParent('ul');
    l = u.getElements('li:not([class*=attach-deleted])');
    total = l.length;
    $each($$('.ffa-sort'), function(el) {
        if(el.getParent('li').hasClass('attach-deleted')) return false;
//        alert('a');
        // текущая позиция
        pos = l.indexOf(el.getParent('li'));

        up = '';
        down = '';

        if(total > 1 && pos == 0) {
            up = '-a';
        }

        if(total > 1 && (pos+1) == total) {
            up = '';
            down = '-a';
        }

        if(total == 1) {
            up = '-a';
            down = '-a';
        }

        el.getFirst().set('src', btn_sort_up + up + '.png');
        el.getLast().set('src', btn_sort_down + down + '.png');
    });
}

function moveAttach(el) {
    el = $(el);
    c = el.getParent('ul');
    // текущая позиция
    pos = c.getElements('li').indexOf(el.getParent('li'));
    // куда двигаем? =)
    type = el.getParent('span').getChildren().indexOf(el);
    // вверх
    if(type == 0 && el.getParent('li').getPrevious()) {
        el.getParent('li').inject(el.getParent('li').getPrevious(), 'before');
    }
    // вниз
    if(type == 1 && el.getParent('li').getNext()) {
        el.getParent('li').inject(el.getParent('li').getNext(), 'after');
    }
    initFilesortBtns(c);
}


function deleteAttach(el) {
    el = $(el);
    el.getParent('li').getElement('input').set('name', 'rmattaches[]');

    try {
        if($chk(CKEDITOR) && CKEDITOR.instances.txt) {
            r = new RegExp('(<img.*?id="'+el.getParent('li').getElement('input').get('value')+'".*?>)', 'g');
            txt = CKEDITOR.instances.txt.getData();

            if(r.test(txt)) {
                CKEDITOR.instances.txt.setData(txt.replace(r, ""));
            }
        }
    } catch (err) {}

    if($$('ul.form-files-list input')) {
        $$('ul.form-files-list input').set('disabled', false);
        $$('ul.form-files-list input[type=image]').setStyle('display', 'inline-block');
    }

    el.getParent('li').addClass('attach-deleted');
    el.getParent('li').setStyle('display', 'none');
    initFilesortBtns(el.getParent('ul'));
}

function NavForNewComments() {
    if(new_comments.length > 0 && $('nav_comm') != undefined) {
        $('nav_comm_count').set('html', new_comments.length);
        $('nav_comm').setStyle('display', 'block');
    } else if ( $('nav_comm') != undefined ) {
        $('nav_comm').setStyle('display', 'none');
    }
}

function navComments(scheme, pfx) {
    if(pfx == undefined) pfx = 'c_';
    if(scheme == 'next') {
        nav_position += 1;
        if(nav_position >= new_comments.length) {
            nav_position = 0;
        }
        var link = new_comments[nav_position];
    } 
    if(scheme == 'prev') {
        nav_position -= 1; 
        if(nav_position < 0) {
            nav_position = new_comments.length-1;
        } 
        var link = new_comments[nav_position];  
    }
    
    var parents = $('c__' + link).getParents('.cl-li-hidden-c');
    if(parents != undefined) {
        parents.each(function(elm) {
            elm.removeClass('cl-li-hidden-c');
        });
    }
    
    link = '#' + pfx + link;
    window.location = link;
}

function setAnchor(mode, id) {
    var el;
    // старый способ
    if (mode == 'c' && $('c__'+id)) {
        $('c__'+id).getElement('div.b-post__body').addClass('b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf');
    // новый способ, с учетом модификации тегов <a name="...."></a> (оборачивание в тег div.b-anchor)
    } else if (mode == 'c' && (el = document.getElement('a[name=c_' + id+ ']'))) {
        if (el.hasClass('b-anchor__link')) {
            var divEl = el.getParent('div.b-anchor');
            if (divEl) {
                divEl.getNext('li').getElement('div.b-post__body').addClass('b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf');
            }
        } else {
            el.getNext('li').getElement('div.b-post__body').addClass('b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf');
        }
    }
}

function setDisplayAnchor(elm) {
   $$('.b-post__anchor').removeClass('b-post__anchor_black'); 
   $(elm).addClass('b-post__anchor_black');
   $$('.b-post__body').removeClass('b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf');
   $(elm).getParent('.b-post__body').addClass('b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf');
}

function showHiddenComment(obj) {
    if(obj.get('html') == "Скрыть") {
        obj.getParent('.b-post__links').getElements('.b-post__links-item').addClass('b-post__links-item_hide');
        obj.getParent('.b-post').getElements('.b-post__txt').toggleClass('b-post__txt_hide');
        obj.getParent().removeClass('b-post__links-item_hide');
        obj.set("html", "Показать"); 
    } else {
        obj.getParent('.b-post__links').getElements('.b-post__links-item').removeClass('b-post__links-item_hide');
        obj.getParent('.b-post').getElements('.b-post__txt').toggleClass('b-post__txt_hide');
        obj.set("html", "Скрыть");
    }
    
}

function showComment(obj) {
    var p = $(obj).getParent().getNext();
    $(obj).getParent().destroy();
    p.removeClass('b-post__txt_hide');
}
