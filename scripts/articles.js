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
            if(input.hasClass('b-fon')) {
                this.options.preloadImage = '/images/loader-gray.gif';    
            } else {
                this.options.preloadImage = '/images/loader-white.gif';    
            }
             loader = new Element('img', {
                'class' : 'upfile-preloader',
                'src' : this.options.preloadImage
            });
            loader.inject(_frm, 'after');
        }
        
        _frm.submit();
        input.set('disabled', true);
        // делаем кнопку ОТПРАВИТЬ НА МОДЕРАЦИЮ неактивной
        if ($('save_article')) {
            $('save_article').addClass('b-button_disabled');
        } else if ($('btn-send-articles')) {
            $('btn-send-articles').addClass('btnr-disabled');
        }
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
        // активируем кнопку ОТПРАВИТЬ НА МОДЕРАЦИЮ
        if ($('save_article')) {
            $('save_article').removeClass('b-button_disabled');
        } else if ($('btn-send-articles')) {
            $('btn-send-articles').removeClass('btnr-disabled');
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

window.addEvent('domready', function() {

    $$('.cl-form-tags a').addEvent('click', function() {

        r = /(.*?)_tag/i;
        tag = this.className.replace(r, '$1');

        switch(tag) {
            case 'question':
                tag = 'p class="q"';
                tag2 = 'p';
                break;
            case 'answer':
                tag = 'p class="a"';
                tag2 = 'p';
                break;
            default:
                tag2 = tag;
        }

        el = this.getParent('ul').getPrevious('textarea');

        if(Browser.Engine.gecko) _scrl = el.scrollTop;
        
        el.insertAroundCursor({
            before: '<' + tag + '>',
            after: '</' + tag2 + '>'
            });

        if(Browser.Engine.gecko) el.scrollTop = _scrl;

        return false;
    });
    
    /* Кнопки рейтинга статей */
    $$('.post-rate a').addEvent('click' , function() {
        if(this.href == '#') return false;
        new Request.JSON({
            url: this.href,
            onSuccess: function(resp) {
                if(resp && resp.success) {
                    if(!$chk($('rate_' + resp.id))) {
                        return false;
                    }
                    rval = $('rate_' + resp.id).getElement('span');
                    vl = rval.get('html').toInt() + resp.val;
                    if(vl >= 0) {
                        rval.removeClass('pr-minus');
                        rval.removeClass('pr-plus');
                    } else {
                        rval.addClass('pr-minus');
                    }
                    if(vl >= 1) rval.addClass('pr-plus');

                    rval.set('html', (vl > 0 ? '+' : '') + vl);

                    lnks = $('rate_' + resp.id).getElements('a img');
                    if(resp.rate_val < 0) {
                        lnks[0].set('src', '/images/btn-drate-dis.png');
                        lnks[1].set('src', '/images/btn-urate.png');
                    }
                    if(resp.rate_val > 0) {
                        lnks[0].set('src', '/images/btn-drate.png');
                        lnks[1].set('src', '/images/btn-urate-dis.png');
                    }
                    if(resp.rate_val == 0) {
                        lnks[0].set('src', '/images/btn-drate.png');
                        lnks[1].set('src', '/images/btn-urate.png');
                    }
//                    lnks[1].set('src', '/images/btn-urate-dis.png');
//                    lnks[1].inject(lnks[1].getParent(), 'after');
//                    $('rate_' + resp.id).getElements('a').dispose();
                }
            }
        }).get();
        return false;
    });

    /* Закладки (статьи) */
    $$('.post-f-fav a').addEvent('click', function() {
        if(window.retrieve('post-fav')) {
            window.retrieve('post-fav').setStyle('display', 'none');
        }
        re = /.*?([0-3])\..*/;
        cur = false;
        if(re.test(this.getElement('img').src)) {
            cur = this.getElement('img').src.replace(re, "$1");
        }

        if(this.getNext('ul.post-f-fav-sel')) {
            u = this.getNext('ul.post-f-fav-sel');
            u.setStyle('display', 'block');
            $each(u.getElements('img'), function(el, idx) {
                if(!el.src.contains('_empty')) {
                    el.src = el.src.replace(/([0-3])/i, '$1_empty');
                }
                if(idx == cur) {
                    el.src = el.src.replace('_empty', '');
                }
            });
        } else {
            u = new Element('ul', {
                'class': 'post-f-fav-sel'
            });
            u.inject(this, 'after');
            for (i = 0; i < 4; i++) {
                emp = (cur && cur == i ? '' : '_empty');
                l = new Element('li', {
                    'html': '<img style="cursor:pointer;" src="/images/ico_star_'+i+ emp + '.gif">'
                });
                l.inject(u);
            }
            u.getElements('img').addEvents({
                'mouseenter' : function() {
                    this.src = this.src.replace('_empty', '');
                },
                'mouseleave' : function() {
                    el = window.retrieve('post-fav').getPrevious('a');
                    cur = el.getElement('img').src.replace(/.*?([0-3])\..*/, "$1");
                    if(this.src.contains('star_' + cur)) {
                        return false;
                    }
                    if(!this.src.contains('_empty')) {
                        this.src = this.src.replace(/([0-3])/i, '$1_empty');
                    }
                },
                'click' : function() {
                    el = window.retrieve('post-fav').getPrevious('a');
                    tp = window.retrieve('post-fav').getElements('img').indexOf(this);
                    tp++;

                    $each(window.retrieve('post-fav').getElements('img[src!='+this.src+']'), function(_el) {
                        _el.fireEvent('mouseleave');
                    });

                    new Request.JSON({
                        url: el.href + '&page=bookmark&type='+tp,
                        onSuccess: function(resp) {
                            if(resp && resp.success) {
                                if(!$chk($('post-fav-' + resp.id) )) return;
                                mm = $('post-fav-' + resp.id).getFirst('a').getElement('img');
                                mm.src = mm.src.replace(/([0-4])/, resp.type).replace('_empty', '');
                                $('post-fav-' + resp.id).getElement('ul').setStyle('display', 'none');

                                ls = document.getElement('.fav-sort ul');
                                favSortCallback.run(0, ls.getElements('a')[(!ls.retrieve('sort_type') ? 0 : ls.retrieve('sort_type'))]);

                            }
                        }
                    }).get();
                    return false;
                }
            });
        }

        window.store('post-fav', u);
        return false;
    });
    if(document.getElement('.fav-sort')) {
        document.getElements('.fav-sort ul a').addEvent('click', favSortCallback);
        document.getElement('.fav-sort div>a').addEvent('click', function() {
            this.getNext('ul').setStyle('display', 'block');
            return false;
        });
    }
    document.addEvent('click', function(e) {
        if(window.retrieve('post-fav') && !$(e.target).getParent('ul.post-f-fav-sel')) {
            window.retrieve('post-fav').setStyle('display', 'none');
        }
        if(!$(e.target).getParent('div.fav-sort') && document.getElement('.fav-sort ul')) {
            document.getElement('.fav-sort ul').setStyle('display', 'none');
        }
    });

    initStars();


//    $$('.cl-form input[type=file]').each( function (el) {
//        articlesFileInput(el, 'comments');
//    });
    
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
    
});


/**
 * @deprecated
 */
function commentAdd(el) {
    el = $(el);
    aFormReset(document.getElement('div.cl-form form'));
    $$('a.cl-form-submit.add-comment').setStyle('display', 'inline-block');
    $$('a.cl-form-submit.edit-comment').setStyle('display', 'none');
    $$('.cl-form-cancel').setStyle('display', 'inline-block');

    if(document.getElement('ul.cl-ul div.cl-form form')) {
        $$('div.cl-form .errorBox').dispose();
    }

    $$('div.cl-form').inject(el.getParent('div.cl-li-in'), 'after');
    $$('div.cl-form').setStyle('display', 'block');

    $$('div.cl-form input[name=task]').set('value', 'add-comment');
    $$('div.cl-form input[name=reply_to]').set('value', el.getParent('li.cl-li').id.replace('c_', ''));

    return false;
}

function favSortCallback() {
    if($$('.fav-list .fav-one-edit').length > 0) {
        favCancelEdit($('favCancelEditInp'));
    }
    tp = this.getParent('ul').getElements('li').indexOf(this.getParent('li'));

    new Request.JSON({
        url : '?page=sortbm',
        onSuccess : function(resp) {
            if(!resp && !resp.success) return false;

            fl = document.getElement('.fav-list');
            fl.set('html', resp.html);
            Cookie.write('bmOrderType', resp.type, {duration: 356});
        }
    }).post({
        'type' : tp,
        'u_token_key': _TOKEN_KEY
    });

    this.getParent('ul').getPrevious('a').getElement('span').set('text', this.get('html'));
    this.getParent('ul').setStyle('display', 'none');
    this.getParent('ul').store('sort_type', tp);
    return false;
};

/**
 * @deprecated
 */
function commentEdit(el) {
    el = $(el);
    aFormReset(document.getElement('div.cl-form form'));
    $$('a.cl-form-submit.edit-comment').setStyle('display', 'inline-block');
    $$('a.cl-form-submit.add-comment').setStyle('display', 'none');
    $$('div.cl-form .errorBox').dispose();
    $$('.cl-form-cancel').setStyle('display', 'inline-block');

    $$('div.cl-form').inject(el.getParent('div.cl-li-in'), 'after');
    $$('div.cl-form').setStyle('display', 'block');
    $$('div.cl-form textarea', 'div.cl-form inpt').set('disabled', 'true');

    id = el.getParent('li.cl-li').id.replace('c_', '');

    new Request.JSON({
        url: './?page=comment&id=' + id,
        onComplete: function(resp) {
            if(resp.success) {
                $$('div.cl-form textarea', 'div.cl-form inpt').set('disabled', '');
                $$('div.cl-form textarea').set('value', resp.data.msgtext);

                $$('div.cl-form input[name=task]').set('value', 'edit-comment');
                $$('div.cl-form input[name=reply_to]').set('value', resp.data.id);

                if(resp.data.youtube_link) {
                    //toggleYoutube();
                    $$('div.cl-form input[name=yt_link]').set('value', resp.data.youtube_link);
                }

                has_attach = (($type(resp.attaches) == 'object' && $H(resp.attaches).getLength() > 0)
                                || ($type(resp.attaches) == 'array' && resp.attaches.length > 0));

                if(resp.attaches && has_attach) {
                    tpl = $('cl').getElement('.form-files-added.tpl').clone(false);
                    tpl.inject($('cl').getElement('.cl-form-files'), 'top');
                    tpl.setStyle('display', 'block');
                    $each(resp.attaches, function(file) {
                        li = $('cl').getElement('.form-files-added.tpl li').clone();
                        li.getElement('input').set('value', file.id);

                        li.getElement('a:last-child').set('html', file.fname);
                        li.getElements('img').cloneEvents($('cl').getElement('.form-files-added.tpl img'));
                        li.inject(tpl);
                    });
                    toggleFiles();
                    initFilesortBtns(tpl);
                    
                    if(resp.attaches.length >= MAX_FILE_COUNT) {
                        $$('ul.form-files-list input').set('disabled', true);
                        $$('ul.form-files-list input[type=image]').setStyle('display', 'none');
                    }
                }
            }
        }
    }).get();

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
            var r = new RegExp('(<img.*?id="'+el.getParent('li').getElement('input').get('value')+'".*?>)', 'g');
            var txt = CKEDITOR.instances.txt.getData();

            if(r.test(txt)) {
                CKEDITOR.instances.txt.setData(txt.replace(r, ""));
            }
        }
    } catch (err) {}

    if($$('ul.form-files-list input')) {
        $$('ul.form-files-list input').set('disabled', false);
        $$('ul.form-files-list input[type=image]').setStyle('display', 'inline-block');
    }
    
    var parent = el.getParent('.form-files-added.main-f');
    var sib;
    if (parent) {
        sib = parent.getSiblings('.add-photos-up');
        if (sib) sib.setStyle('display', '');
        sib = parent.getSiblings('.form-files-list.main-f');
        if (sib) sib.setStyle('display', '');
    }

    el.getParent('li').addClass('attach-deleted');
    el.getParent('li').setStyle('display', 'none');
    initFilesortBtns(el.getParent('ul'));
}

function formMoveTo(comment) {
    $$('div.cl-form').inject(comment.getFirst(), 'after');
    $$('div.cl-form').setStyle('display', 'block');

    $$('a.cl-form-submit.add-comment').setStyle('display', 'none');
    $$('a.cl-form-submit.edit-comment').setStyle('display', 'inline-block');
    $$('.cl-form-cancel').setStyle('display', 'inline-block');
}

/**
 * @deprecated
 */
function aFormReset(frm) {
    if(!frm) return false;
    frm.getElement('input[name=reply_to]').set('value', '');
    frm.getElement('input[name=task]').set('value', 'add-comment');
    frm.reset();
    if ($(frm).getElement('input[name=u_token_key]')) {
        $(frm).getElement('input[name=u_token_key]').set('value', _TOKEN_KEY);
    }
    if($$('.form-files-list li input[value=-]').length > 0)
        $$('.form-files-list li input[value=-]').fireEvent('click');
    $$('.cl-form-files ul.form-files-added').dispose();
    $$('.cl-form-cancel').setStyle('display', 'none');
    $$('ul.form-files-list input').set('disabled', false);
    toggleFiles(true);
    toggleYoutube(true);
}

function toggleFiles(hide) {
    el = document.getElement('.cl-form-files');
    el.setStyle('display', el.getStyle('display') == 'none' && !hide ? '' : 'none');
}

function toggleYoutube(hide) {
    el = document.getElement('.cl-form-video');
    el.setStyle('display', el.getStyle('display') == 'none' && !hide ? '' : 'none');
}

function deleteBookmark(id) {
    if(!confirm('Вы уверены?')) return false;

    new Request.JSON({
        url : '?page=delbm&id='+id,
        onSuccess: function(resp) {
            if(resp && resp.id) {
                rel = $('fav-' + resp.id).getParent('ul.fav-list');
                $('fav-' + resp.id).dispose();
                
                if($('post-fav-' + resp.id)) {
                    sc = $('post-fav-' + resp.id).getFirst();
                    sc.getElement('img').set('src', sc.getElement('img').src.replace(/([0-4])/, '0_empty'));
                }

                if(rel.getChildren('li').length == 2 && rel.getElement('li.no-bookmarks')) {
                    rel.getElement('li.no-bookmarks').setStyle('display', 'block');
                }
            }
        }
    }).get();

    return false;
}

function editBookmark(id) {
    ed = document.getElement('.fav-one-edit');
    ed.inject($('fav-' + id), 'after');
    ed.setStyle('display', 'block');

//    ed.getElement('textarea').set('value', $('fav-' + id).getElement('span>a').firstChild.nodeValue.trim());
    ed.getElement('textarea').set('value', $('fav-' + id).getElement('input').get('value').trim());

    $('fav-' + id).setStyle('display', 'none');
    if(ed.retrieve('el') && ed.retrieve('el') != $('fav-' + id)) ed.retrieve('el').setStyle('display', 'block');
    ed.store('el', $('fav-' + id));
    ed.store('bmid', id);

    st = $('fav-' + id).getFirst('img').src.replace(/^.*?(\d).*/, '$1');
    ed.store('selected', st);
    cs = ed.getElement('.post-f-fav-sel img[src*=star_'+st+']');
    cs.src = cs.src.replace(/(\d)_empty/, '$1');
    
    $each(ed.getElements('.post-f-fav-sel img[src!='+$('fav-' + id).getFirst('img').src+']'), function(_el) {
        _el.fireEvent('mouseleave');
    });
}

function saveBookmark(el) {
    el = $(el);
    id = el.getParent('.fav-one-edit').retrieve('bmid');
    selected = el.getParent('.fav-one-edit').retrieve('selected').toInt() + 1;
    txt = el.getParent('.fav-one-edit').getElement('textarea').get('value');

    if(txt.trim().length == 0) {
        _t = $('post-fav-' + id)
            .getParent('div.post-one')
            .getElement('div.post-txt h3 a');
        txt = _t.firstChild.nodeValue.trim();
    }
    
    if (txt.trim().length > 255) {
        alert("Превышен лимит символов (255)");
        return false;
    }

    new Request.JSON({
        url : '?page=editbm',
        onSuccess : function(resp) {
            favCancelEdit(document.getElement('.fav-one-edit-btns>input'));

            if(!resp && !resp.success) return false;

            ls = document.getElement('.fav-sort ul');
            favSortCallback.run(0, ls.getElements('a')[(!Cookie.read('bmOrderType') ? 0 : Cookie.read('bmOrderType'))]);

            if($('post-fav-' + resp.id)) {
                $('post-fav-' + resp.id).getElement('img').set('src',
                    $('post-fav-' + resp.id).getElement('img').get('src').replace(/(\d+)/, resp.type-1));
            }
        }
    }).post({
        'id' : id,
        'type' : selected,
        'title' : txt,
        'u_token_key': _TOKEN_KEY
    });
}

function favCancelEdit(el) {
    el = $(el);
    el.getParent('.fav-one-edit').setStyle('display', 'none');
    if(!el.getParent('.fav-one-edit').retrieve('el')) return;
    el.getParent('.fav-one-edit').retrieve('el').setStyle('display', 'block');
    el.getParent('.fav-one-edit').inject(document.getElement('.fav-list-tpl'));
}

function initStars() {
    document.getElements('.post-f-fav-sel img').addEvents({
        'mouseenter' : function() {
            this.src = this.src.replace('_empty', '');
        },
        'mouseleave' : function() {
            el = this.getParent('.fav-one-edit').retrieve('el');
            selected = this.getParent('.fav-one-edit').retrieve('selected');
            if(this.src.contains('star_' + selected)) {
                return false;
            }
            if(!this.src.contains('_empty')) {
                this.src = this.src.replace(/([0-3])/i,  '$1_empty');
            }
        },
        'click' : function() {
            $each(this.getParent('ul').getElements('img'), function(el) {
                if(!el.src.contains('_empty')) {
                    el.src = el.src.replace(/([0-3])/i, '$1_empty');
                }
            });
            imgs = this.getParent('.fav-one-edit').getElements('img');
            imgs[imgs.indexOf(this)].src = imgs[imgs.indexOf(this)].src.replace('_empty', '');
            this.getParent('.fav-one-edit').store('selected', imgs.indexOf(this));
        }
    });
}

function commentsThreadState(aid, cid, type) {
    switch(type) {
        case 'article':
            cname = 'articleThreads';
            break;
        case 'blogs':
            cname = 'blogsThreads';
            break;
        default:
            return false;
    }

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

    Cookie.write(cname, JSON.encode(c), {diration: 30});
}


function checkLogin(login) {
    if(login.length == 0) return false;
    if($$('.form.ai-form input[name=login]').retrieve('oldvalue') == login) {
        $$('span.login-input').setStyle('display','none');
        $$('span.login-error').setStyle('display','none');
        $$('span.login-view').setStyle('display','inline-block');
        return false;
    }
    new Request.JSON({
        url: '?task=checklogin',
        onComplete: function(resp) {
            if(resp && resp.success) {
                $$('span.login-input').setStyle('display','none');
                $$('span.login-error').setStyle('display','none');
                $$('span.login-view').setStyle('display','inline-block');
                $$('span.login-view a:first-child').set('href', '/users/' + resp.user.login);
                $$('span.login-view a:first-child').set('html', resp.user.uname + ' '
                    + resp.user.usurname
                    + ' [' + resp.user.login + ']');
                $$('.form.ai-form input[name=login]').store('oldvalue', resp.user.login);
            } else {
                $$('span.login-error').setStyle('display','inline-block');
            }
        }
    }).post({
        'login': login,
        'u_token_key': _TOKEN_KEY
    });
}

function changeLogin() {
    $$('span.login-input').setStyle('display','inline-block');
    $$('span.login-view').setStyle('display','none');
}

function toggleAddForm(noScroll, noToggle) {
    document.forms['interviewForm'].reset();
    if ($(document.forms['interviewForm']).getElement('input[name=u_token_key]')) {
        $(document.forms['interviewForm']).getElement('input[name=u_token_key]').set('value', _TOKEN_KEY);
    }
    $$('form[name=interviewForm] input, form textarea').set('disabled', '');
    if(CKEDITOR && CKEDITOR.instances.txt) {
        CKEDITOR.instances.txt.setData('');
        document.getElement('form[name=interviewForm] textarea[name=txt]').set('value', '');
//        CKEDITOR.instances.txt.updateElement();
    }
    $$('input[name^=attached]').dispose();
    $$('.add-photos .form-files-added').set('html', '');
    $$('.ap-id').set('html', '&lt;img id="0"&gt;');
    $each($$('.ap-id'), function(el) {
        if(el.getParent().tagName.toLowerCase() == 'a') {
            el.replaces(el.getParent());
        }
    });
    $$('input[type=image][value=-]').fireEvent('click');
    $$('div.form form[name=interviewForm] input[name=id]').set('value', '');
    $$('div.form form[name=interviewForm] input[name=task]').set('value', 'add');
    $$('div.cl-form .errorBox').dispose();
    changeLogin();

    $$('div.form.ai-form>h3').set('html', 'Новое интервью');
    if(noToggle) {
        disp = 'block';
    } else {
        disp = ($$('div.form.ai-form').getStyle('display') == 'block' ? 'none' : 'block');
    }
    $$('div.form.ai-form').setStyle('display', disp);
    if(!noScroll)
        new Fx.Scroll(window).toElement(document.getElement('div.form.ai-form'));
}

function editInterview(id) {
    toggleAddForm(0,1);
    $$('div.form>h3').set('html', 'Редактировать интервью');
    $$('form input:not([type=button]), form textarea').set('disabled', 'true');
    $$('div.form form input[name=id]').set('value', id);
    $$('div.form form input[name=task]').set('value', 'edit');
    new Request.JSON({
        url: '?task=get-interview&id='+id,
        onComplete: function(resp) {
            h = $H(resp.interview);
            $$('div.form form input:not([type=button]), div.form form textarea').set('disabled', '');
            try {
                $$('div.form form textarea', 'div.form form input:not([type=button]):not([type=submit])').each(function(el) {
                    if(!h.get(el.name) || el.type == 'file') return false;
                    
                    if(el.name == 'txt') {
                        CKEDITOR.instances.txt.setData(h.get(el.name));
                    } else {
                        el.set('value', h.get(el.name));
                    }
                    
                    if(el.type == 'checkbox' && h.get(el.name) == 't') {
                        el.checked = true;
                        el.value   = 1;
                    } else if (el.type == 'checkbox') {
                        el.value = 1; 
                    }

                    if(el.name == 'login') {
                        el.store('oldvalue', h.get(el.name));
                        $$('span.login-view a:first-child').set('href', '/users/' + h.get('login'));
                        $$('span.login-view a:first-child').set('html', h.get('uname') + ' '
                            + h.get('usurname')
                            + ' [' + h.get('login') + ']');
                        checkLogin(h.get('login'));
                    }
                });
            }catch(e) {
//                alert(e);
            }

            if(resp.attaches && resp.attaches.length > 0) {
                $each(resp.attaches, function(file, i) {
                    if(!$chk(file.id)) return false;
                    var li = document.getElement('.form-files-added.tpl li').clone();
                    li.getElement('input').set('value', file.id);
                    if(li.getElement('.ap-id')) {
                        li.getElement('.ap-id').set('html', li.getElement('.ap-id').get('html').replace(/(\d+)/g, file.id));

//                        li.getElement('.ap-id').set('onclick', 'insertImage(this);');

                        li.getElement('.ap-id').file_path = file.path;
                        li.getElement('.ap-id').file_fname = file.fname;

                        if(file.id) {
                            var anc = new Element('a', {
                                'href' : 'javascript:void(0)'
                            });
                            anc.addEvent('click', function() {
                                insertImage(this);
                            });
                            anc.wraps(li.getElement('.ap-id'));
                        }
                    }

                    li.getElement('a:last-child').set('html', file.fname);
                    li.getElements('img').cloneEvents(document.getElement('.form-files-added.tpl img'));

                    if(i == 0) {
                        li.inject(document.getElement('.form-files-added.main-f'));
                        document.getElement('.form-files-list.main-f').setStyle('display', 'none');
                        document.getElement('.form-files-list.main-f').getPrevious().setStyle('display', 'none');
                    } else {
                        li.inject(document.getElement('.form-files-added.add-f'));
                    }
                });
            }
        }
    }).get();
}


function initFileInput(el) {
    el = $(el);
    new FileUpload(el, {
        url: '?task=upload',
        preloadImage: '/images/load_fav_grey2.gif',
        debug: false,
        onUpload: function(resp, inp) {
            resp = JSON.decode(resp);
            if(resp && resp.success) {
                is_main = inp.getParent('.main-f');

                ap = inp.getParent().getElement('span.ap-id');
                if(ap) {
                    ap.set('html', ap.get('html').replace(/(\d+)/g, resp.file.id));

                    ap.file_fname = resp.file.fname;
                    ap.file_path = resp.file.path;

                    if(resp.file.id) {
                        anc = new Element('a', {
                            'href' : 'javascript:void(0)'
                        });
                        anc.addEvent('click', function() {
                            insertImage(this);
                        });
                        anc.wraps(ap);
                        
                        if(anc.getParent('a')) {
                            old = anc.getParent('a');
                            anc.inject(old, 'after');
                            old.dispose();
                        }
                    }
                }

                if(!inp.getParent().getElement('input[name^=attached]')) {
                    i = new Element('input', {
                        'type' : 'hidden',
                        'name' : (is_main ? 'attached[main]' : 'attached[ex][]'),
                        'value' : resp.file.id
                    });
                    i.inject(inp.getParent());
                } else {
                    inp.getParent().getElement('input[name^=attached]').set('value', resp.file.id);
                }
            } else {
                if(inp.getParent().getElement('input[name^=attach]'))
                    inp.getParent().getElements('input[name^=attach]').set('value', '');
                if(inp.getParent().getElement('input[name="attached[main]"]')) {
                    inp.getParent().getElement('input[name="attached[main]"]').dispose();
                }
                if(inp.getParent().getElement('input[name=main_foto]'))
                    inp.getParent().getElements('input[name=main_foto]').set('value', '');
                
                ap = inp.getParent().getElement('.ap-id');
                if(ap) {
                    ap.set('html', ap.get('html').replace(/(\d+)/g, "0"));

                    ap.file_fname = null;
                    ap.file_path = null;

                    if(ap.getParent().tagName.toLowerCase() == 'a') {
                        ap.replaces(ap.getParent());
                    }
                }
                alert(resp.errorMessage);
//                alert(resp.errorMessage);
            }
        }
    });
}

function saveInterview(f) {
    $$('div.errorBox').dispose();
    
    document.getElement('form[name=interviewForm] .form-btns>input').set('disabled', true);

    CKEDITOR.instances.txt.updateElement();
    
    vl = CKEDITOR.instances.txt.getData().trim();
    vl = WrapCkLinks(vl);
    document.getElement('form[name=interviewForm] textarea[name=txt]').set('value', vl);

    fstr = $(f).toQueryString();

    new Request.JSON({
        url: window.location.pathname,
        data: fstr,
        onComplete: function(resp) {
            document.getElement('form[name=interviewForm] .form-btns>input').set('disabled', false);

            if(resp.success) {
                toggleAddForm(true, false);
                if($chk(resp.newid)) {
                    document.location.href = './?id=' + resp.newid;
                } else {
                    if(resp.main_photo) {

                        if($('interview'+resp.id)) {
                            _mainphoto = document.getElement('#interview'+resp.id +' .intreview-photo img');
                            if(!_mainphoto) {
                                _mainphoto = new Element('img', {
                                    'alt' :  resp.user.fullname + ' [' + resp.user.login + ']'
                                });
                                _mainphoto.inject(document.getElement('#interview'+resp.id +' .intreview-photo'));
                            }
                        }

                        if(resp.page_view == 'view') {
                            _mainphoto = document.getElement('div.interview-avatar img');
                            if(!_mainphoto) {
                                _mainphoto = new Element('img', {
                                    'alt' :  resp.user.fullname + ' [' + resp.user.login + ']',
                                    'width' : 100,
                                    'class' : 'interview-avatar'
                                });
                                _mainphoto.inject(document.getElement('div.interview-avatar>a'));
                            }
                        }
                        
                        fname = resp.WDCPREFIX + '/'
                            + resp.main_photo.path
                            + 'sm_' + resp.main_photo.fname;

                        if(!resp.main_photo.fname)  {
                            fname = '';
                            _mainphoto.dispose();
                        } else {
                            _mainphoto.set('src', fname);
                            try {
                                //_mainphoto.removeAttribute('width');
                                //_mainphoto.removeAttribute('height');
                            } catch(e) {
//                                alert(e)
                            }
                        }
                        
                    }
                    if($('interview'+resp.id) && $('interview'+resp.id).getElement('a.freelancer-name')) {
                        $('interview'+resp.id).getElement('a.freelancer-name')
                            .set('html', resp.user.fullname + ' [' + resp.user.login + ']');
                    }
                    if ($(document).getElement('.b-crumbs__table') && $(document).getElement('.b-crumbs__table').getElements('td')[2]) {
                        $(document).getElement('.b-crumbs__table').getElements('td')[2]
                            .set('html', resp.user.fullname + ' [' + resp.user.login + ']');
                    }
                    if(resp.page_view == 'view') {
                        el = document.getElement('.interview-one .interview-body');
                        el.set('html', resp.txt);

                        el.getPrevious('h1').getElement('a')
                            .set('html', resp.user.fullname + ' [' + resp.user.login + ']');
                        el.getPrevious('h1').getElement('a')
                            .set('href', '/users/' + resp.user.login);

                        document.getElement('div.interview-avatar>a')
                            .set('href', '/users/' + resp.user.login);

                        if(document.getElement('div.interview-avatar img'))
                            document.getElement('div.interview-avatar img').getParent()
                                .set('href', '/users/' + resp.user.login);
                    }
                    window.location.hash = "page_interview";
                }
            } else {
                $each(resp.errorMessages, function(err, el) {
                   switch(el) {
                       case 'login' :
                           eel = viewError(err);
                           eel.inject(document.getElement('div.form input[name=login]'), 'after');
                           break;
                       case 'txt' :
                           eel = viewError(err);
                           eel.inject(document.getElement('div.form textarea[name=txt]').getNext(), 'after');
                           break;
                       case 'alert' :
                           alert(err);
                           break;
                       default :
                           alert(resp.errorMessages);
                   }
                });
                if($$('div.errorBox')[0]) $$('div.errorBox')[0].scrollIntoView();
            }
        }
    }).post();

    return false;
}

function viewError(msg) {
    return new Element('div', {
        'class': 'errorBox',
        'html' : '<img src="/images/ico_error.gif" alt="" width="22" height="18">' + msg
    });
}

function addArticleForm(noScroll, noToggle) {
    if (!document.forms['interviewForm']) return false;
    document.forms['interviewForm'].reset();
    if ($(document.forms['interviewForm']).getElement('input[name=u_token_key]')) {
        $(document.forms['interviewForm']).getElement('input[name=u_token_key]').set('value', _TOKEN_KEY);
    }
    $$('form[name=interviewForm] input, form[name=interviewForm] textarea').set('disabled', '');
    $$('input[name=attached]').dispose();
    $$('.add-photos .form-files-added').set('html', '');
    $$('div.form form[name=interviewForm] input[name=id]').set('value', '');
    $$('div.form form[name=interviewForm] input[name=task]').set('value', 'add-article');
    $$('div.cl-form .errorBox').dispose();
    $$('div.form.ai-form>div>h3').set('html', 'Добавить статью');
    
    $$('div.ai-form .add-f').setStyle('display', 'none');
    $$('input[name=rmlogo]', 'input[name=logo]').set('value', '');

    if(CKEDITOR.instances && CKEDITOR.instances.msgtext) CKEDITOR.instances.msgtext.setData('');
    if(CKEDITOR.instances && CKEDITOR.instances.short) CKEDITOR.instances.short.setData('');

    if(noToggle) {
        disp = 'block';
    } else {
        disp = (document.getElement('div.form.ai-form').getStyle('display') == 'block' ? 'none' : 'block');
    }
    $$('div.form.ai-form').setStyle('display', disp);
    if(!noScroll)
        new Fx.Scroll(window).toElement(document.getElement('div.form.ai-form'));
}

function editArticle(id) {
    addArticleForm(0,1);
    $$('div.ai-form>div>h3').set('html', 'Редактировать статью');
    $$('div.ai-form input:not([type=button]), div.ai-form textarea').set('disabled', 'true');
    $$('div.ai-form input[name=id]').set('value', id);
    $$('div.ai-form input[name=task]').set('value', 'edit-article');

    new Request.JSON({
        url: '?task=get-article&id='+id,
        async: false,
        onComplete: function(resp) {
            if(resp && resp.article) {
                if(CKEDITOR.instances && CKEDITOR.instances.msgtext) {
                    CKEDITOR.instances.msgtext.destroy();
                }
                
                h = $H(resp.article);
                $each($$('div.ai-form input:not([type=button]), div.ai-form textarea'), function(el) {
                   if(h.get(el.name)) {
                       el.set('value', h.get(el.name));
                   }
                });
                if(resp.btn_name_save) {
                    $('btn_name').set('html', 'Сохранить');
                }
                $$('div.ai-form input:not([type=button]), div.ai-form textarea').set('disabled', '');
                
                //$('msgtext').set('value', h.get('msgtext'));
                if (CKEDITOR) {
                    CKEDITOR.replace('msgtext');
                    CKEDITOR.instances.msgtext.setData(h.get('msgtext'));
                }
//                CKEDITOR.instances.short.setData(h.get('short'));

                if(h.get('logo') && resp.attach_url) {
                    attaches = document.getElement('div.ai-form ul.add-f');
                    attaches.setStyle('display','block');
                    attaches.getElement('li>a:last-child').set('html', resp.attach_url);
                    attaches.getElement('li>a:last-child').set('href', resp.attach_url);

                    attaches.getElement('input[name=logo]').set('value', h.get('logo'));
                }
            }
        }
    }).get();
}

function saveArticle(f) {
    if (($('save_article') && $('save_article').hasClass('b-button_disabled')) || 
        ($('btn-send-articles') && $('btn-send-articles').hasClass('btnr-disabled'))) {
        return false
    }
    $$('div.errorBox').dispose();
    f = $(f);

       var fck = CKEDITOR.instances.msgtext;
       if (!fck) return;
       var vl = fck.getData().trim();
       vl = /^(\&nbsp;|<br.*?\/>)$/i.test(vl) ? '' : vl;

       vl = WrapCkLinks(vl);
       
       document.getElement('div.js-form textarea[name=msgtext]').set('value', vl);

//       if(!document.getElement('div.js-form').hasClass('cl-form')) {
//           fck = CKEDITOR.instances.short;
//           vl = fck.getData().trim();
//           vl = /^(\&nbsp;|<br.*?\/>)$/i.test(vl) ? '' : vl;
//           document.getElement('div.js-form textarea[name=short]').set('value', vl);
//       }
       
    new Request.JSON({
        url: '/articles/',
        data: f.toQueryString(),
        async: false,
        onComplete: function(resp) {
            if(resp && resp.success) {
                try {
                    addArticleForm(true, false);
                } catch(e) {}
                if($chk(resp.newid)) {
                    document.location.href = './?id=' + resp.newid;
                } else {
                    
                    if(resp.article) {
                        
                        if($("post_" + resp.article.id) ) {
                            if($("post_" + resp.article.id).getElement('img.post-img'))
                                $("post_" + resp.article.id).getElement('img.post-img').set('src', resp.article.logo_url);
                            if($("post_" + resp.article.id).getElement('.post-body'))
                                $("post_" + resp.article.id).getElement('.post-body').set('html', resp.article.short);
                            if($("post_" + resp.article.id).getElement('.post-txt>h3>a'))
                                $("post_" + resp.article.id).getElement('.post-txt>h3>a').set('html', resp.article.title);
                            if($(document).getElement('.b-crumbs__table') && $(document).getElement('.b-crumbs__table').getElements('td')[2])
                                $(document).getElement('.b-crumbs__table').getElements('td')[2].set('html', resp.article.title);
                            new Fx.Scroll(window).toElement($("post_" + resp.article.id));
                        }

                        if(resp.page_view == 'view') {
                            var tags_html = '';
                            for(var i = 0;i<resp.article.kwords.length;i++) {
                                var name = resp.article.kwords[i]['name'];
                                var word_id = resp.article.kwords[i]['word_id'];
                                var p = (i+1 == resp.article.kwords.length ? '':', ');
                                tags_html += '<li class="b-tags__item"><a class="b-tags__link" href="?tag='+word_id+'">' + name +'</a>' + p + '</li>';
                            }
                            if(tags_html == '') {
                                document.getElement('div.b-tags span.b-tags__txt').setStyle('display', 'none');    
                            } else {
                                document.getElement('div.b-tags span.b-tags__txt').setStyle('display', 'inline');
                            }
                            document.getElement('div.interview-avatar img').set('src', resp.article.logo_url);
                            document.getElement('.interview-one div.interview-body').set('html', resp.article.msgtext);
                            document.getElement('.interview-one h1').set('html', resp.article.title);
                            document.getElement('div.b-tags ul.b-tags__list').set('html', tags_html);
                        }
                    }
                }
            } else {
                if(!resp) return;
                var is_cl = document.getElement('div.js-form').hasClass('cl-form');
                $each(resp.errorMessages, function(err, el) {
                   switch(el) {
                       case 'title' :
                           eel = viewError(err);
                           eel.inject(document.getElement('div.js-form input[name=title]').getParent(), 'after');
                           break;
                       case 'login' :
                           eel = viewError(err);
                           eel.inject(document.getElement('div.js-form input[name=login]'), 'after');
                           break;
                       case 'short' :
                           eel = viewError(err);
                           eel.inject(document.getElement('div.js-form textarea[name=short]').getParent(), 'after');
                           break;
                       case 'msgtext' :
                           eel = viewError(err);
                           eel.inject(document.getElement('div.js-form textarea[name=msgtext]').getParent(), 'after');
                           break;
                       case 'logo' :
                           eel = viewError(err);
                           if(is_cl) {
                                eel.inject(document.getElement('div.js-form .cl-form-files'));
                                break;
                           }
                           eel.inject(document.getElement('div.js-form .form-file-add'), 'after');
                           break;
                       case 'alert' :
                           alert(err);
                           break;
                   }
                });
                $('btn_name').removeClass('btnr-disabled');
                new Fx.Scroll(window).toElement($$('div.errorBox')[0]);
            }
        }
    }).post();

    return false;
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

function delArticleAttach(el) {
    el = $(el);
    el.getParent().getElement('input[name=rmlogo]').set('value', 1);
    el.getParent().getElement('input[name=logo]').set('value', '');
    el.getParent('ul').setStyle('display', 'none');
}

function WrapCkLinks(vl) {
    tmpel = new Element('div', {
        'html' : vl
    });
    $each(tmpel.getElements('a'), function(el) {
        el.set('rel', 'follow');
        el.set('target', '_blank');
        if(el.getParent() && el.getParent().tagName.toLowerCase() != 'noindex') {
            ni = new Element('noindex');
            ni.wraps(el);
        }
    });
    vl = tmpel.get('html');
    tmpel.dispose();

    return vl;
};


function delArticleForm(id) {
    if(!id) return;

    if($('post_' + id)) {
        if($('post_' + id).getElement('div.post-txt')) {
            $('del-article-form').inject($('post_' + id).getElement('div.post-txt'));
        }

        $('del-article-form').setStyle('display', '');
        $('del-article-form').getElement('input[name=id]').set('value', id);
    }
}

function delArticleFormClose() {
    $('del-article-form').setStyle('display', 'none');
    $('del-article-form').getElement('input[name=id]').set('value', '');

    frm = $('del-article-form').getElement('form');
    frm.reset();
    if ($(frm).getElement('input[name=u_token_key]')) {
        $(frm).getElement('input[name=u_token_key]').set('value', _TOKEN_KEY);
    }
}

function delArticleConfirm() {
    tx = $('del-article-form').getElement('textarea');

    if(tx.get('value').trim().length == 0) {
        alert('Необходимо указать причину отказа');
        return false;
    }

    if(!confirm('Точно удалить?')) return false;
    
    return $('del-article-form').getElement('form').submit();
}

window.addEvent('domready', function(){
    // для модераторов, принять/отклонить статью
    var $moderatorForm,
        $moderatorFormTask,
        $moderatorFormArticleID,
        $moderatorApprove,
        $moderatorDecline,
        $moderatorUndecline,
        $articlesList;
    
    $moderatorForm = $('moderator_form');
    if (!$moderatorForm) {
        return;
    }
    
    $moderatorFormTask = $('moderator_form_task');
    $moderatorFormArticleID = $('moderator_form_article_id');
    
    // для страницы статьи
    $moderatorApprove = $('moderator_approve');    
    $moderatorApprove && $moderatorApprove.addEvent('click', function(){
        approve()
    });
    $moderatorDecline = $('moderator_decline');
    $moderatorDecline && $moderatorDecline.addEvent('click', function(){
        decline();
    });
    $moderatorUndecline = $('moderator_undecline');
    $moderatorUndecline && $moderatorUndecline.addEvent('click', function(){
        undecline();
    });
    
    // для страницы списка неподтвержденных статей
    $articlesList = $('articles_list');
    $articlesList && $articlesList.addEvent('click', function(event){
        var target = event.target;
        if (!target.hasClass('moderator_approve')) {
            return;
        }
        var id = target.get('article_id');
        approve(id);
    });
    $articlesList && $articlesList.addEvent('click', function(event){
        var target = event.target;
        if (!target.hasClass('moderator_decline')) {
            return;
        }
        var id = target.get('article_id');
        decline(id);
    });
    
    function approve (id) {
        if (confirm('Вы уверены?')) {
            id && $moderatorFormArticleID.set('value', id);
            $moderatorFormTask.set('value', 'approve');
            $moderatorForm.submit();
        }
    }
    
    function decline (id) {
        if (confirm('Вы уверены?')) {
            id && $moderatorFormArticleID.set('value', id);
            $moderatorFormTask.set('value', 'decline');
            $moderatorForm.submit();
        }
    }
    
    function undecline (id) {
        if (confirm('Вы уверены?')) {
            id && $moderatorFormArticleID.set('value', id);
            $moderatorFormTask.set('value', 'undecline');
            $moderatorForm.submit();
        }
    }
});