
window.addEvent('domready', function() {
    $$('.js-editor').each(function(el) {
        new Editor(el);
//        new jsEdit(el);
    });
});

var Editor = new Class({
    
    Implements: [Events, Options],
    
    blockEls    : /^(H[1-6]|HR|P|DIV|ADDRESS|PRE|FORM|TABLE|LI|OL|UL|TD|CAPTION|BLOCKQUOTE|CENTER|DL|DT|DD|SCRIPT|NOSCRIPT|STYLE)$/i,
    
    options     : {
        'config'        :   'toggle|spacer|bold|italic|underline|strikethrough|spacer|insertunorderedlist|insertorderedlist',
    
        'defaults'      : {

            'bold'  : {
                'label' : 'Жирный',
                'css'   : '{action}-item remove-active',
                'match' : {
                    'tags'   : ['b', 'strong'],
                    'styles' : /font-weight\s*?:\s*?bold/i
                }
            },

            'italic'  : {
                'label' : 'Курсив',
                'css'   : '{action}-item remove-active',
                'match' : {
                    'tags'   : ['i', 'em'],
                    'styles' : /font-style\s*?:\s*?italic/i
                }
            },

            'underline'  : {
                'label' : 'Подчеркнутый',
                'css'   : '{action}-item remove-active',
                'match' : {
                    'tags'   : ['u'],
                    'styles' : /text-decoration\s*?:\s*?underline/i
                }
            },

            'strikethrough'  : {
                'label' : 'Зачеркнутый',
                'css'   : '{action}-item remove-active',
                'match' : {
                    'tags'   : ['s', 'strike'],
                    'styles' : /text-decoration\s*?:\s*?line-through/i
                }
            },

            'insertunorderedlist'  : {
                'label' : 'Маркированный список',
                'css'   : '{action}-item remove-active',
                'match' : {
                    'tags'   : ['ul']
                },
                'onComplete' : function(btn, action) {
                    btn.editor.activateControls.delay(1, btn.editor);
                }
            },

            'insertorderedlist'  : {
                'label' : 'Нумерованный список',
                'css'   : '{action}-item remove-active',
                'match' : {
                    'tags'   : ['ol']
                },
                'onComplete' : function(btn, action) {
                    btn.editor.activateControls.delay(1, btn.editor);
                }
            },

            'toggle' : {
                'label'    : 'HTML-код',
                'css'   : 'toggleview-item',
                'onAction' : function(btn) {
                    if (btn.editor.textarea.getStyle('display') == 'none') {
                        btn.editor.hideEditor();
                    } else {
                        btn.editor.showEditor();
                    }
                }
            },

            'spacer' : {
                'type' : 'spacer'
            }
            
        }
    },
           
    'textarea'  : null,
    'editor'    : null,
    'container' : null,
    'panel'     : null,
    
    'focused'   : false,
    'inside'    : false,
    
    'timer'     : null,
    
    initialize: function(el, options) {
        this.setOptions(options);
        
        this.textarea = el;
        this.editor = this.getEditor();
        this.panel = this.getControls();
    },
    
    getEditor: function() {
        if (this.editor) {
            return this.editor;
        }
        
        this.textarea.setStyle('display', 'none');
        editor = new Element('div', {
            'class'     : 'jsedit b-post__txt',
            'tabindex'  : '0',
//            'html'      : '<p>'+String.fromCharCode('8203')+'</p>',
            'html'      : '<p><br></p>',
            'styles'    : {
                'padding'   : '5px 0 0 5px',
                'overflow'  : 'auto',
                'border'    : '1px solid #ccc',
                'height'    : '150px',
                'text-align': 'left',
                'background-color' : '#fff'
            }
        });
        editor.setProperty('contentEditable', 'true');
        editor.inject(this.textarea, 'before');
        
        editor.addEvent('keydown', function(evt) {
            
            if (evt.key == 'enter') {
                s = rangy.getSelection();
                r = s.rangeCount ? s.getRangeAt(0) : null;
                
               
                cur = r.startContainer;
                if (cur.nodeType == 3 && cur.parentNode.nodeName.toLowerCase() == 'li') {
                    cur = cur.parentNode;
                }
                
                if (cur.nodeName.toLowerCase() == 'li' && !cur.get('text').trim().length) {
                    evt.preventDefault();
                    
                    cmd = 'insertorderedlist';
                    if (cur.parentNode.nodeName.toLowerCase() == 'ul') {
                        cmd = 'insertunorderedlist';
                    }
                    
                    document.execCommand(cmd, false, false);
                    
                    if (Browser.firefox) {
                        document.execCommand('insertparagraph', false, false);
                    }
                    
                    return false;
                }
                
//                document.execCommand('insertparagraph', null, null);
//                this.insertNode.attempt(document.createElement('br'), this);
            }
        }.bind(this));
        
//        editor.addEvent('keydown', function(evt) {
//            if (evt.key == 'enter') {
//                evt.preventDefault();
//                document.execCommand('insertparagraph', null, null);
////                this.insertNode.attempt(document.createElement('br'), this);
//            }
//        }.bind(this));

        editor.addListener('paste', function() {
            alert('a')
        });
        
        editor.addEvent('keyup', this.checkRoot.bind(this));
        editor.addEvent('keydown', this.checkRoot.bind(this));
        editor.addEvent('mousedown', this.checkRoot.bind(this));
        
        editor.addEvent('keyup', this.activateControls.bind(this));
//        editor.addEvent('keydown', this.activateControls.bind(this));
        editor.addEvent('mousedown', this.activateControls.bind(this));
        editor.addEvent('mouseup', this.activateControls.bind(this));
        
//        editor.addEventListener('DOMSubtreeModified', function() {
//            if (this.timer) return;
//            
//            _id = Math.random();
//            _id = new String(_id).replace('.', '');
//            nd = new Element('marker', {
//                'id' : _id
//            });
//            this.insertNode(nd);
//            
//            this.updateValue();
//            this.updateContent();
//            
//            this.editor.focus();
//            r = rangy.createRange();
//            r.selectNode($(_id));
//            s = rangy.getSelection();
//            s.setSingleRange(r);
//            
//            
//            this.insertNode();
//            
//            $(_id).dispose();
//            
//        }.bind(this), true);
        
        this.container = new Element('div', {'class'     : 'fe-plain'});
        this.container.wraps(editor);
        this.container.wraps(this.textarea);
        
        fe_in = new Element('div', {'class'     : 'fe-in'});
        fe_in.wraps(this.container);
        
        fe = new Element('div', {'class'     : 'fe'});
        fe.wraps(fe_in);
        
        editor.addEvent('focus', function() {
            this.focused = true;
        }.bind(this));
        
        this.container.addEvent('mouseenter', function(evt) {
            this.inside = true;
        }.bind(this));
        
        this.container.addEvent('mouseleave', function(evt) {
            this.inside = false;
        }.bind(this));
        
        document.addEvent('click', function(evt) {
            if (!this.inside) {
                this.focused = false;
            }
        }.bind(this));
        
        return editor;
    },
    
    getControls: function() {
        if (this.panel) {
            return this.panel;
        }
        
        return new Editor.Panel(this);
    },
    
    getValue: function() {
        return this.cleanup(this.textarea.get('value'));
    },
    
    getContent: function() {
        return this.cleanup(this.editor.get('html'));
    },
    
    updateValue: function() {
        this.textarea.set('value', this.getContent());
    },
    
    updateContent: function(txt) {
        this.editor.set('html', this.getValue());
    },
    
    getRange: function() {
        s = rangy.getSelection();
        r = s.rangeCount ? s.getRangeAt(0) : null;
        
        return r;
    },
    
    activateControls: function(evt) {
//        this.touchTimer();
        
        this.panel.container.getElements('.remove-active').removeClass('onActive');
        
        var selected = [];
        
        if (!this.editor.focused) {
            this.editor.focus();
        }
        
        s = rangy.getSelection();
        r = s.rangeCount ? s.getRangeAt(0) : null;
        
        start = r.startContainer.parentNode ? r.startContainer.parentNode : r.startContainer;
        end   = r.endContainer.parentNode ? r.endContainer.parentNode : r.endContainer;
        
        if (start.nodeType == 3 && end.nodeType == 3) {
            return;
        }
        
        var isChild = function(n1, n2) {
            list1 = n1.getParents();
            
            for (i = 0; i < list1.length; i++ ) {
                if (list1[i] != n2) continue;
                if (i == 100) break;
                return true;
            }
            
            return false;
        };
        
        if (!isChild(start, end)) {
            selected.push(start);
        }

        prnts = start.getParents();
        for (i = 0; i < prnts.length; i++) {
            if (prnts[i].hasClass('jsedit')) break;
            if (selected.contains(prnts[i])) continue;
            
            selected.push(prnts[i]);
        }
        
        this.panel.check(selected);
        
    },
    
    checkRoot: function() {
        if (!this.editor.get('html').trim().length) {
            this.insertNode(new Element('p'));
        }
    },
    
    insertNode: function(nd) {
        this.touchTimer();
        
        s = rangy.getSelection();
        r = s.rangeCount ? s.getRangeAt(0) : null;
        
        if (nd) {
            r.insertNode(nd);
            r.collapseAfter(nd);
        }
        
        nd2 = document.createTextNode(String.fromCharCode('8203'));
        r.insertNode(nd2);
        
        r.selectNode(nd2);
        s.setSingleRange(r);
        s.collapseToStart();
    },
    
    surroundContents: function(node) {
        r = this.getRange();
        r.surroundContents(node);
    },
    
    cleanup: function(txt) {
        
        txt = txt.replace(/<(\w+)\s.*?style=(["'])(.*?)\2.*?>(.*?)<\/\1>/gi, function(matches, tag, undefined, style, txt) {
            styles = style.split(';');
            
            skip_tags  = [];  
            add_tags   = [];  

            switch (true) {
                case style.test(/font-weight\s?:\s?normal/i):
                    skip_tags.combine('b|strong'.split('|'));
                    break;
                case style.test(/font-style\s?:\s?normal/i):
                    skip_tags.combine('i|em'.split('|'));
                    break;
                case style.test(/text-decoration\s?:\s?none/i):
                    skip_tags.combine('s|strike|u'.split('|'));
                    break;
                case style.test(/font-weight\s?:\s?bold/i):
                    add_tags.combine(['b']);
                    break;
                case style.test(/font-style\s?:\s?italic/i):
                    add_tags.combine(['i']);
                    break;
                case style.test(/text-decoration\s?:\s?underline/i):
                    add_tags.combine(['u']);

                    if(tag.test(/s|strike/i)) {
                        skip_tags.combine([tag]);
                    }
                    break;
                case style.test(/text-decoration\s?:\s?line-through/i):
                    add_tags.combine(['s']);

                    if(tag.test(/u/i)) {
                        skip_tags.combine([tag]);
                    }
                    break;
            }
            
//            alert(skip_tags)
//            alert(add_tags)
            
            out = [];
            add_tags.combine([tag]);
            
//            alert(add_tags);
            
            for (i = 0; i < add_tags.length; i++) {
                if (skip_tags.contains(add_tags[i])) continue;
                
                out.push(add_tags[i]);
            }
            
            out.reverse();
            txt = '<' + out.join('><') + '>' + txt;
            out.reverse();
            txt += '</' + out.join('></') + '>';
            
            return txt;
        });
        
        txt = txt.replace(/<\s*(\w+).*?>/gi, function(all, match) {
//            alert(match)
            if (match == 'marker') {
                return all;
            }
            
            return '<' + match + '>';
        });
        
        txt = txt.replace(/<(span)>(.*)<\/\1>/gi, '$2');
        
        

        txt = txt.replace(/\u1680/gi, '');
        txt = txt.replace(/\u180E/gi, '');
        txt = txt.replace(/\u205F/gi, '');
        txt = txt.replace(/\u200B/gi, '');
        txt = txt.replace(/\u200C/gi, '');
        
        return txt;
    },
    
    hideEditor: function() {
        this.updateValue();
        this.panel.container.removeClass('disabled');
        this.editor.setStyle('display', 'none');
        this.textarea.setStyle('display', '');
    },
    
    showEditor: function() {
        this.updateContent();
        this.panel.container.addClass('disabled');
        this.editor.setStyle('display', '');
        this.textarea.setStyle('display', 'none');
    },
    
    touchTimer: function() {
        this.timer = setTimeout(function() {
            clearTimeout(this.timer);
            this.timer = null;
        }.bind(this), 10);
    }
});

Editor.Panel = new Class({
    Implements: [Events, Options],
    
    'editor'  : null,
    'container' : null,
    'elements'  : {},
    
    initialize: function(editor, options) {
        this.setOptions(options);
        this.editor = editor;
        

        var config = this.editor.options.config;
        config = config.split('|');
        
        this.container = new Element('div', {
            'class'     : 'mooeditable-ui-toolbar  c '
        });
        this.container.inject(this.editor.container, 'top');
        
        clear = new Element('div', {
            'style'     : 'clear: both;'
        });
        clear.inject(this.container, 'after');
        
        config.each(function(act) {

            var control = this.editor.options.defaults[act];
            
            if (control != undefined) {
                var clsName = (control['type'] == undefined ? 'button' : control['type']).capitalize();

                this.elements[act] = new Editor[clsName](this, act, control);
            }
        }.bind(this));
        
        return this;
    },
    
    check: function(selected) {
        if(!selected.length) return;
        
        Object.each(this.elements, function (el) {
            if (el.match) el.match(selected);
        });
    }
});

Editor.Button = new Class({
    Implements: [Events, Options],
    
    'options' : {
        'match' : {
            'tags' : [],
            'styles' : null
        },
        'onAction'  : function(btn, act) {
            var nativecode = act;
            
//            try {
//                document.execCommand("styleWithCSS", 0, false);
//            } catch (e) {
//                try {
//                    document.execCommand("useCSS", 0, true);
//                } catch (e) {
//                    try {
//                        document.execCommand('styleWithCSS', false, false);
//                    }
//                    catch (e) {
//                    }
//                }
//            }
//            document.execCommand(nativecode, null, null);
            document.execCommand("StyleWithCSS", true, true);
            document.execCommand(nativecode, false, null);

            return true;
        },
        'onComplete' : Class.empty
    },
    
    'panel' : null,
    'element' : null,
    'editor'  : null,
    
    initialize: function(panel, act, options) {
        this.setOptions(options);
        
        if (typeOf(this.options.match.tags) == 'function') {
            this.options.match.tags = this.options.match.tags(act);
        }
        
        if (typeOf(this.options.match.styles) == 'function') {
            this.options.match.styles = this.options.match.styles(act);
        }
        
        this.panel = panel;
        this.editor = this.panel.editor;
        
        var _em = new Element('em', {
            'html' : !this.options.label ? act : this.options.label
        });
        _em.inject(this.panel.container);

        var el = new Element('button', {
//            'html'  : !this.options.label ? act : this.options.label,
//            'href'      : 'javascript:void(0)',
            'styles'    : {
                'background-color' : 'transparent',
                'border' : 0,
                'width'  : 25,
                'height'  : 25
            },
            'class'     : 'toolbar-item ' + (this.options.css ? this.options.css.substitute({'action' : act}) : ''),
            'events'    : {
                'click' : function(evt) {
                    evt.preventDefault();
//                    if (!this.editor.focused) {
//                        this.editor.editor.focus();
//                    }
                    
                    this.action.delay(5, this, act);
                    
                }.bind(this)
            }
        });
        
        el.inject(this.panel.container);
        
        el.wraps(_em);
        this.element = el;
        
        return this;
    },
    
    action: function(act) {
        
        this.editor.touchTimer();
        
//        this.editor.activateControls();
        this.fireEvent('onAction', [this, act]);
        this.fireEvent('onComplete', [this, act]);

        if (!this.element.hasClass('onActive')) {
            this.activate();
        } else {
            this.deactivate();
        }
        
        return false;
    },
    
    activate: function() {
        this.element.addClass('onActive');
        this.editor.editor.focus();
    },
    
    deactivate: function() {
        this.element.blur();
        this.element.removeClass('onActive');
        this.editor.editor.focus();
    },
    
    match: function(elements) {
        var matched = false;
        
        for (i = 0; i < elements.length; i++) {
            if (!this.options.match.tags.contains(elements[i].tagName.toLowerCase())) continue;
            
            matched = true;
        }
        
        if (matched) {
            this.activate();
        }
//        alert(this.options.match.tags)
        
        return matched;
    }
});

Editor.Spacer = new Class({
    
    initialize: function(panel) {
        sp = new Element('span', {
            'class'     : 'toolbar-separator'
        });
        sp.inject(panel.container);
    }
});

Editor.Selectbox = new Class({
    Implements: [Events, Options]
});


