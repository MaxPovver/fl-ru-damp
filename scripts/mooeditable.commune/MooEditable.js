/*
---

name: MooEditable

description: Class for creating a WYSIWYG editor, for contentEditable-capable browsers.

license: MIT-style license

authors:
- Lim Chee Aun
- Radovan Lozej
- Ryan Mitchell
- Olivier Refalo
- T.J. Leahy

requires:
- Core/Class.Extras
- Core/Element.Event
- Core/Element.Dimensions

inspiration:
- Code inspired by Stefan's work [Safari Supports Content Editing!](http://www.xs4all.nl/~hhijdra/stefan/ContentEditable.html) from [safari gets contentEditable](http://walkah.net/blog/walkah/safari-gets-contenteditable)
- Main reference from Peter-Paul Koch's [execCommand compatibility](http://www.quirksmode.org/dom/execCommand.html)
- Some ideas and code inspired by [TinyMCE](http://tinymce.moxiecode.com/)
- Some functions inspired by Inviz's [Most tiny wysiwyg you ever seen](http://forum.mootools.net/viewtopic.php?id=746), [mooWyg (Most tiny WYSIWYG 2.0)](http://forum.mootools.net/viewtopic.php?id=5740)
- Some regex from Cameron Adams's [widgEditor](http://widgeditor.googlecode.com/)
- Some code from Juan M Martinez's [jwysiwyg](http://jwysiwyg.googlecode.com/)
- Some reference from MoxieForge's [PunyMCE](http://punymce.googlecode.com/)
- IE support referring Robert Bredlau's [Rich Text Editing](http://www.rbredlau.com/drupal/node/6)

provides: [MooEditable, MooEditable.Selection, MooEditable.UI, MooEditable.Actions]

...
*/

(function(){

    var blockEls = /^(H[1-6]|HR|P|DIV|ADDRESS|PRE|FORM|TABLE|LI|OL|UL|TD|CAPTION|BLOCKQUOTE|CENTER|DL|DT|DD|SCRIPT|NOSCRIPT|STYLE)$/i;
    var urlRegex = /^(https?|ftp|rmtp|mms):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i;
    var protectRegex = /<(script|noscript|style)[\u0000-\uFFFF]*?<\/(script|noscript|style)>/g;

    this.MooEditable = new Class({

        Implements: [Events, Options],

        options: {
            toolbar: true,
            cleanup: true,
            paragraphise: true,
            xhtml : true,
            semantics : true,
            actions: 'bold italic underline strikethrough | insertunorderedlist insertorderedlist indent outdent | undo redo | createlink unlink | urlimage | toggleview',
            handleSubmit: true,
            handleLabel: true,
            disabled: false,
            baseCSS: Browser.opera?'html{ height: 100%; cursor: text; } body{ font-family: sans-serif; height:90%; min-width:100px}':'html{ height: 100%; cursor: text; } body{ font-family: sans-serif; }',
            extraCSS: '',
            externalCSS: '',
            html: '<!DOCTYPE html><html><head><meta charset="UTF-8">{BASEHREF}<style>{BASECSS} {EXTRACSS}</style>{EXTERNALCSS}</head><body></body></html>',
            rootElement: 'p',
            baseURL: '',
            dimensions: null,
            errorCSS: 'iframe_error'
        },

        initialize: function(el, options){
            this.setOptions(options);
            this.textarea = document.id(el);
            this.textarea.store('MooEditable', this);
            this.actions = this.options.actions.clean().split(' ');
            this.keys = {};
            this.dialogs = {};
            this.protectedElements = [];
            var itemGroupCounter = 0;  //считаем действия в группе, то есть не разделенные сепаратором
            var i = 0;                 //массив для хранения action которым надо добавить признак левой кнопки
            this.leftButtons  = new Array();         //массив для хранения action которым надо добавить признак левой кнопки
            this.rightButtons = new Array();         //массив для хранения action которым надо добавить признак правой кнопки
            this.actions.each(function(action){
                if (action != "|" && this.actions[i + 1] != "|" && (this.actions[i + 2] == "|" || i + 2 == this.actions.length) && wysiwyg_toolbarPairButton.indexOf(action) != -1) {
                	this.leftButtons.push(action);
                }
                if (action != "|" && (this.actions[i + 1] == "|"  || i + 1 >= this.actions.length) && this.actions[i - 1] != "|" && wysiwyg_toolbarPairButton.indexOf(action) != -1) {
                	this.rightButtons.push(action);
                }
                var act = MooEditable.Actions[action];
                i++;
                if (!act) return;
                if (act.options){
                    var key = act.options.shortcut;
                    if (key) this.keys[key] = action;
                }
                if (act.dialogs){
                    Object.each(act.dialogs, function(dialog, name){
                        dialog = dialog.attempt(this);
                        dialog.name = action + ':' + name;
                        if (typeOf(this.dialogs[action]) != 'object') this.dialogs[action] = {};
                        this.dialogs[action][name] = dialog;
                    }, this);
                }
                if (act.events){
                    Object.each(act.events, function(fn, event){
                        this.addEvent(event, fn);
                    }, this);
                }                
            }.bind(this));
            this.render();
        },
	
        toElement: function(){
            return this.textarea;
        },
        
        clearError: function() {
            return this.iframe.removeClass(this.options.errorCSS);
        },
	
        render: function(){
            var self = this;
		
            // Dimensions
            var dimensions = this.options.dimensions || this.textarea.getSize();
		
            // Build the container
            this.container = new Element('div', {
                id: (this.textarea.id) ? this.textarea.id + '-mooeditable-container' : null,
                'class': 'mooeditable-container',
                styles: {
                   // width: dimensions.x
                }
            });

            // Override all textarea styles
            this.textarea.addClass('mooeditable-textarea').setStyle('height', dimensions.y);
		
            this.toolbar = new MooEditable.UI.Toolbar({
                onItemAction: function(){
                    var args = Array.from(arguments);
                    var item = args[0];
                    self.action(item.name, args);
                }
            }, this);
            this.attach.delay(1, this);
		
            // Update the event for textarea's corresponding labels
            if (this.options.handleLabel && this.textarea.id) $$('label[for="'+this.textarea.id+'"]').addEvent('click', function(e){
                if (self.mode != 'iframe') return;
                e.preventDefault();
                self.focus();
            });

            // Update & cleanup content before submit
            if (this.options.handleSubmit){
                this.form = this.textarea.getParent('form');
                if (!this.form) return;
                this.form.addEvent('submit', function(){
                    if (self.mode == 'iframe') self.saveContent();
                });
            }
            
            this.fireEvent('render', this);
        },

        attach: function(){
            var self = this;
		
            // Dimensions
            var dimensions = this.options.dimensions || this.textarea.getSize();
		
            if (this.iframe) {
                this.iframe.dispose();
            }
            var error_class = '';
            if(this.textarea.hasClass('wysiwyg-error')) {
                error_class = ' ' + this.options.errorCSS; //b-combo__input_error';
            }
            // Build the iframe
            this.iframe = new IFrame({
                'class': 'mooeditable-iframe' + error_class,
                frameBorder: 0,
                src: 'javascript:""', // Workaround for HTTPs warning in IE6/7
                styles: {
                    height: dimensions.y
                }
            });

            // Assign view mode
            this.mode = 'iframe';
		
            // Editor iframe state
            this.editorDisabled = false;

            // Put textarea inside container
            this.container.wraps(this.textarea);

            this.textarea.setStyle('display', 'none');
		
            this.iframe.setStyle('display', '').inject(this.textarea, 'before');
		
            Object.each(this.dialogs, function(action, name){
                Object.each(action, function(dialog){
                    document.id(dialog).inject(self.iframe, 'before');
                    var range;
                    dialog.addEvents({
                        open: function(){
                            range = self.selection.getRange();
                            self.editorDisabled = true;
                            self.toolbar.disable(name);
                            self.fireEvent('dialogOpen', this);
                        },
                        close: function(){
                            self.toolbar.enable();
                            self.editorDisabled = false;
                            self.focus();
                            if (range) self.selection.setRange(range);
                            self.fireEvent('dialogClose', this);
                        }
                    });
                });
            });

            // contentWindow and document references
            this.win = this.iframe.contentWindow;
            this.doc = this.win.document;
		
            // Deal with weird quirks on Gecko
            if (Browser.firefox) this.doc.designMode = 'On';

            // Build the content of iframe
            var docHTML = this.options.html.substitute({
                BASECSS: this.options.baseCSS,
                EXTRACSS: this.options.extraCSS,
                EXTERNALCSS: (this.options.externalCSS) ? '<link rel="stylesheet" href="' + this.options.externalCSS + '">': '',
                BASEHREF: (this.options.baseURL) ? '<base href="' + this.options.baseURL + '" />': ''
            });
            this.doc.open();
            this.doc.write(docHTML);
            this.doc.close();

            // Turn on Design Mode
            // IE fired load event twice if designMode is set
            (Browser.ie) ? this.doc.body.contentEditable = true : this.doc.designMode = 'On';

            // Mootoolize window, document and body
            Object.append(this.win, new Window);
            Object.append(this.doc, new Document);
            if (Browser.Element){
                var winElement = this.win.Element.prototype;
                for (var method in Element){ // methods from Element generics
                    if (!method.test(/^[A-Z]|\$|prototype|mooEditable/)){
                        winElement[method] = Element.prototype[method];
                    }
                }
            } else {
                document.id(this.doc.body);
            }
		
            this.setContent(this.textarea.get('value'));

            // Bind all events
            this.doc.addEvents({
                mouseup: this.editorMouseUp.bind(this),
                mousedown: this.editorMouseDown.bind(this),
                mouseover: this.editorMouseOver.bind(this),
                mouseout: this.editorMouseOut.bind(this),
                mouseenter: this.editorMouseEnter.bind(this),
                mouseleave: this.editorMouseLeave.bind(this),
                contextmenu: this.editorContextMenu.bind(this),
                click: this.editorClick.bind(this),
                dblclick: this.editorDoubleClick.bind(this),
                keypress: this.editorKeyPress.bind(this),
                keyup: this.editorKeyUp.bind(this),
                keydown: this.editorKeyDown.bind(this),
                focus: this.editorFocus.bind(this),
                blur: this.editorBlur.bind(this)
            });
            this.win.addEvents({
                focus: this.editorFocus.bind(this),
                blur: this.editorBlur.bind(this)
            });
            ['cut', 'copy', 'paste'].each(function(event){
                self.doc.body.addListener(event, self['editor' + event.capitalize()].bind(self));
            });
            this.textarea.addEvent('keypress', this.textarea.retrieve('mooeditable:textareaKeyListener', this.keyListener.bind(this)));
		
            // Fix window focus event not firing on Firefox 2
            if (Browser.firefox2) this.doc.addEvent('focus', function(){
                self.win.fireEvent('focus').focus();
            });
            // IE9 is also not firing focus event
            if (this.doc.addEventListener) this.doc.addEventListener('focus', function(){
                self.win.fireEvent('focus');
            }, true);

            // styleWithCSS, not supported in IE and Opera
            if (!Browser.ie && !Browser.opera){
                var styleCSS = function(){
                    self.execute('styleWithCSS', false, false);
                    self.doc.removeEvent('focus', styleCSS);
                };
                this.win.addEvent('focus', styleCSS);
            }

            if (this.options.toolbar){
                document.id(this.toolbar).inject(this.container, 'top');
                this.toolbar.render(this.actions, this.leftButtons, this.rightButtons);
            }
		
            if (this.options.disabled) this.disable();

            this.selection = new MooEditable.Selection(this.win);
		
            this.oldContent = this.getContent();
		
            this.fireEvent('attach', this);
            
            var editor = this;
            // обработчик нажатия на иконку видео в редакторе
            this.videoClickEvent = function () {
                // в этой функции this - это DOM элемент
                var url = this.get('video_url');
                // открываем диалог
                var dialog = editor.dialogs.movie.prompt.open();
                var input = dialog.el.getElement('.dialog-input');
                input.set('value', url);
                // помечаем картинку как редактируемую
                editor.doc.body.getElements('img.wysiwyg_video').removeClass('edit_url')
                this.addClass('edit_url');
            };
            this.addVideoClickEventListener = function () {
                this.doc.getElements('img.wysiwyg_video').addEvent('click', this.videoClickEvent);
            };
            this.addVideoClickEventListener();
		
            return this;
        },
	
        detach: function(){
            this.saveContent();
            this.textarea.setStyle('display', '').removeClass('mooeditable-textarea').inject(this.container, 'before');
            this.textarea.removeEvent('keypress', this.textarea.retrieve('mooeditable:textareaKeyListener'));
            this.container.dispose();
            this.fireEvent('detach', this);
            return this;
        },
	
        enable: function(){
            this.editorDisabled = false;
            this.toolbar.enable();
            return this;
        },
	
        disable: function(){
            this.editorDisabled = true;
            this.toolbar.disable();
            return this;
        },
	
        editorFocus: function(e){
            // ? ?? ???????? ? ???????? blur ? iframe, ????????? ???????
            if (Browser.ie && (Browser.version == 7 || Browser.version == 8)) {
                this.editorDisabled = false;
            }
            if (Browser.ie && Browser.version == 9) {
                this.noFocus = false;
            }
            this.clearError();
            this.oldContent = '';
            this.fireEvent('editorFocus', [e, this]);
        },
	
        editorBlur: function(e){
            this.oldContent = this.saveContent().getContent();
            // ? ?? ???????? ? ???????? blur ? iframe, ????????? ???????
            if (Browser.ie && (Browser.version == 7 || Browser.version == 8)) {
                this.editorDisabled = true;
            }
            if (Browser.ie && Browser.version == 9) {
                this.noFocus = true;
            }
            this.fireEvent('editorBlur', [e, this]);
        },
	
        editorMouseUp: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
		
            if (this.options.toolbar) this.checkStates();
		
            this.fireEvent('editorMouseUp', [e, this]);
        },
	
        editorMouseDown: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
		
            this.fireEvent('editorMouseDown', [e, this]);
        },
	
        editorMouseOver: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
		
            this.fireEvent('editorMouseOver', [e, this]);
        },
	
        editorMouseOut: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
		
            this.fireEvent('editorMouseOut', [e, this]);
        },
	
        editorMouseEnter: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
		
            if (this.oldContent && this.getContent() != this.oldContent){
                this.focus();
                this.fireEvent('editorPaste', [e, this]);
            }
		
            this.fireEvent('editorMouseEnter', [e, this]);
        },
	
        editorMouseLeave: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
		
            this.fireEvent('editorMouseLeave', [e, this]);
        },
	
        editorContextMenu: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
		
            this.fireEvent('editorContextMenu', [e, this]);
        },
	
        editorClick: function(e){
            // make images selectable and draggable in Safari
            if (Browser.safari || Browser.chrome){
                var el = e.target;
                if (Element.get(el, 'tag') == 'img'){
			
                    // safari doesnt like dragging locally linked images
                    if (this.options.baseURL){
                        if (el.getProperty('src').indexOf('http://') == -1){
                            el.setProperty('src', this.options.baseURL + el.getProperty('src'));
                        }
                    }
			
                    this.selection.selectNode(el);
                    this.checkStates();
                }
            }
		
            this.fireEvent('editorClick', [e, this]);
        },
	
        editorDoubleClick: function(e){
            this.fireEvent('editorDoubleClick', [e, this]);
        },
	
        editorKeyPress: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
            if (Browser.opera){
                var ctrlmeta = e.control || e.meta;
                if ((ctrlmeta && e.key == 'v') || (e.shift && e.code == 45)){
                    this.fireEvent('editorPaste', [e, this]);
                }
            }
            if (Browser.Engine.webkit) {
                if (e.code == 9) {
                    e.stop();
                    return;
                }
            }
            if (this.noFocus){
                e.stop();
                return;
            }
		
            this.keyListener(e);
		
            this.fireEvent('editorKeyPress', [e, this]);
        },
	
        editorKeyUp: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
            if (this.noFocus){
                e.stop();
                return;
            }
		
            var c = e.code;
            // 33-36 = pageup, pagedown, end, home; 45 = insert
            if (this.options.toolbar && (/^enter|left|up|right|down|delete|backspace$/i.test(e.key) || (c >= 33 && c <= 36) || c == 45 || e.meta || e.control)){
                if (Browser.ie6){ // Delay for less cpu usage when you are typing
                    clearTimeout(this.checkStatesDelay);
                    this.checkStatesDelay = this.checkStates.delay(500, this);
                } else {
                    this.checkStates();
                }
            }
		
            this.fireEvent('editorKeyUp', [e, this]);
        },
	
        editorKeyDown: function(e){
            if ((e.code == 37 || e.code == 39) && (/*this.prevKey == 224 ||*/ e.alt || e.meta) && !Browser.ie) {
                if (this.selection.getRange().collapsed) {
                    if (e.code == 37) {
                        var top = this.selection.getRange().getBoundingClientRect().top;                    
                        var r = this.selection.getRange();
                        var node = r.startContainer;
                        if (r.startOffset > 0) {
                            r.setStart(node, 0);
                            r.setEnd(node, 0);
                        }
                        this.selection.setRange(r);
                    }
                    if (e.code == 39) {
                        var top = this.selection.getRange().getBoundingClientRect().top;                    
                        var r = this.selection.getRange();
                        var node = r.startContainer;
                        if (node.textContent && node.textContent.length) {
                            try {
                                r.setStart(node, node.textContent.length);
                                r.setEnd(node, node.textContent.length);
                                this.selection.setRange(r);
                            } catch(e) {
                                ;
                            }
                        }
                    }
                }
                
                return false;
            }
            if (this.editorDisabled){
                e.stop();
                return;
            }
            if (this.noFocus){
                e.stop();
                return;
            }
		
            if (e.key == 'enter'){
                if (this.options.paragraphise){
                    if (e.shift && (Browser.safari || Browser.chrome)){
                        var s = this.selection;
                        var r = s.getRange();
					
                        // Insert BR element
                        var br = this.doc.createElement('br');
                        r.insertNode(br);
					
                        // Place caret after BR
                        r.setStartAfter(br);
                        r.setEndAfter(br);
                        s.setRange(r);
					
                        // Could not place caret after BR then insert an nbsp entity and move the caret
                        if (s.getSelection().focusNode == br.previousSibling){
                            var nbsp = this.doc.createTextNode('\u00a0');
                            var p = br.parentNode;
                            var ns = br.nextSibling;
                            (ns) ? p.insertBefore(nbsp, ns) : p.appendChild(nbsp);
                            s.selectNode(nbsp);
                            s.collapse(1);
                        }
					
                        // Scroll to new position, scrollIntoView can't be used due to bug: http://bugs.webkit.org/show_bug.cgi?id=16117
                        this.win.scrollTo(0, Element.getOffsets(s.getRange().startContainer).y);
					
                        e.preventDefault();
                    } else if (Browser.firefox || Browser.safari || Browser.chrome){
                        var node = this.selection.getNode();
                        var isBlock = Element.getParents(node).include(node).some(function(el){
                            return el.nodeName.test(blockEls);
                        });
                        if (!isBlock) {
                            this.execute('insertparagraph');
                        } else {
                            // когда добавляется новый параграф, то align наследуется от предыдущего
                            // и если в параграфе картинка выровнена по центру, то следующий параграф будет тоже выровнен по центру
                            // без возможности изменить
                            // исправляем это
                            var align = node.get('align');
                            node.set('align', '');
                            setTimeout(function(){
                                node.set('align', align);
                            }, 1)
                        }
                    }
                } else {
                    if (Browser.ie){
                        var r = this.selection.getRange();
                        var node = this.selection.getNode();
                        if (r && node.get('tag') != 'li'){
                            this.selection.insertContent('<br>');
                            this.selection.collapse(false);
                        }
                        e.preventDefault();
                    }
                }
            }
		
            if (Browser.opera){
                var ctrlmeta = e.control || e.meta;
                if (ctrlmeta && e.key == 'x'){
                    this.fireEvent('editorCut', [e, this]);
                } else if (ctrlmeta && e.key == 'c'){
                    this.fireEvent('editorCopy', [e, this]);
                } else if ((ctrlmeta && e.key == 'v') || (e.shift && e.code == 45)){
                    // ?????? ???? ????? ?????????? ??? ?????????? ??????? (keyup)
                    //this.fireEvent('editorPaste', [e, this]); 
                }
            }
		
            this.fireEvent('editorKeyDown', [e, this]);
        },
	
        editorCut: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
		
            this.fireEvent('editorCut', [e, this]);
        },
	
        editorCopy: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
		
            this.fireEvent('editorCopy', [e, this]);
        },
	
        editorPaste: function(e){
            if (this.editorDisabled){
                e.stop();
                return;
            }
            if (this.noFocus){
                e.stop();
                return;
            }
		
            this.fireEvent('editorPaste', [e, this]);
        },
	
        keyListener: function(e){
            var key = (Browser.Platform.mac) ? e.meta : e.control;
            if (!key || !this.keys[e.key]) return;
            e.preventDefault();
            var item = this.toolbar.getItem(this.keys[e.key]);
            item.action(e);
        },

        focus: function(){
            (this.mode == 'iframe' ? this.win : this.textarea).focus();
            this.fireEvent('focus', this);
            return this;
        },

        action: function(command, args){
            var action = MooEditable.Actions[command];
            if (action.command && typeOf(action.command) == 'function'){
                action.command.apply(this, args);
            } else {
                this.focus();
                this.execute(command, false, args);
                if (this.mode == 'iframe') this.checkStates();
            }
        },

        execute: function(command, param1, param2){
            if (this.busy) return;
            this.busy = true;
            this.doc.execCommand(command, param1, param2);
            this.saveContent();
            this.busy = false;
            return false;
        },
        
        toggleView: function(){
            this.fireEvent('beforeToggleView', this);
            if (this.mode == 'textarea'){
                this.mode = 'iframe';
                this.iframe.setStyle('display', '');
                this.setContent(this.textarea.value);
                this.textarea.setStyle('display', 'none');
                this.addVideoClickEventListener();
            } else {
                this.saveContent();
                this.mode = 'textarea';
                this.textarea.setStyle('display', '');
                this.iframe.setStyle('display', 'none');
            }
            this.fireEvent('toggleView', this);
            this.focus.delay(10, this);
            return this;
        },

        getContent: function(){
            var protect = this.protectedElements;
            if (!this.doc.body) {
                return '';
            }
            var html = this.doc.body.get('html').replace(/<!-- mooeditable:protect:([0-9]+) -->/g, function(a, b){
                return protect[b.toInt()];
            });
            return this.cleanup(this.ensureRootElement(html));
        },

        setContent: function(content){
            var protect = this.protectedElements;
            content = content.replace(protectRegex, function(a){
                protect.push(a);
                return '<!-- mooeditable:protect:' + (protect.length-1) + ' -->';
            });
            this.doc.body.set('html', this.ensureRootElement(content));
            this.fireEvent('saveContent', this);
            return this;
        },

        saveContent: function(){
            if (this.mode == 'iframe'){
                this.textarea.set('value', this.getContent());
            }
            return this;
        },
	
        ensureRootElement: function(val) {
            return val;
            if (val.length == 0) return val;
            if (this.options.rootElement){
                tmp = new Element('div', {
                    'html' : val.trim()
                });
                
                if (!tmp.childNodes.length) {
                    return val;
                }
                
                if (!tmp.firstChild.nodeName.test(blockEls)) {
                    repl = new Element(this.options.rootElement, {
                        'html': tmp.get('html')
                    });
                    tmp.set('html', '');
                    repl.inject(tmp);
                }
                
                
                val = tmp.get('html').replace(/\n\n/g, '');
                
                tmp.dispose();
            }
            
            return val;
        },
 
//        _ensureRootElement: function(val){
//            if (this.options.rootElement){
//                var el = new Element('div', {
//                    html: val.trim()
//                    });
//                var start = -1;
//                var create = false;
//                var html = '';
//                var length = el.childNodes.length;
//                for (var i=0; i<length; i++){
//                    var childNode = el.childNodes[i];
//                    var nodeName = childNode.nodeName;
//                    if (!nodeName.test(blockEls) && nodeName !== '#comment'){
//                        if (nodeName === '#text'){
//                            if (childNode.nodeValue.trim()){
//                                if (start < 0) start = i;
//                                html += childNode.nodeValue;
//                            }
//                        } else {
//                            if (start < 0) start = i;
//                            html += new Element('div').adopt($(childNode).clone()).get('html');
//                        }
//                    } else {
//                        create = true;
//                    }
//                    if (i == (length-1)) create = true;
//                    if (start >= 0 && create){
//                        var newel = new Element(this.options.rootElement, {
//                            html: html
//                        });
//                        el.replaceChild(newel, el.childNodes[start]);
//                        for (var k=start+1; k<i; k++){
//                            el.removeChild(el.childNodes[k]);
//                            length--;
//                            i--;
//                            k--;
//                        }
//                        start = -1;
//                        create = false;
//                        html = '';
//                    }
//                }
//                val = el.get('html').replace(/\n\n/g, '');
//            }
//            return val;
//        },        
        
        checkStates: function(){
            var element = this.selection.getNode();
            if (!element) return;
            if (typeOf(element) != 'element') return;
		
            this.actions.each(function(action){
                var item = this.toolbar.getItem(action);
                if (!item) return;
                item.deactivate();

                var states = MooEditable.Actions[action]['states'];
                if (!states) return;
			
                // custom checkState
                if (typeOf(states) == 'function'){
                    states.attempt([document.id(element), item], this);
                    return;
                }
			
                try{
                    if (this.doc.queryCommandState(action)){
                        item.activate();
                        return;
                    }
                } catch(e){}
			
                if (states.tags){
                    var el = element;
                    do {
                        var tag = el.tagName.toLowerCase();
                        if (states.tags.contains(tag)){
//                            item.activate(tag);
                            break;
                        }
                    }
                    while ((el = Element.getParent(el)) != null);
                }

                if (states.css){
                    var el = element;
                    do {
                        var found = false;
                        for (var prop in states.css){
                            var css = states.css[prop];
                            if (el.style[prop.camelCase()].contains(css)){
                                item.activate(css);
                                found = true;
                            }
                        }
                        if (found || el.tagName.test(blockEls)) break;
                    }
                    while ((el = Element.getParent(el)) != null);
                }
            }.bind(this));
        },

        cleanup: function(source){
            if (!this.options.cleanup) return source.trim();
		
            do {
                var oSource = source;
			
                // replace base URL references: ie localize links
                if (this.options.baseURL){
                    source = source.replace('="' + this.options.baseURL, '="');
                }
                
                source = source.replace(/<(embed|object|sup).*?>/gi, '');
                source = source.replace(/<\/(embed|object|sup)>/gi, '');
                
                source = source.replace(/<noindex>/gi, "");
                source = source.replace(/<\/noindex>/gi, "");
                source = source.replace(/<div>(.+?)<\/div>/ig, '<p>$1</p>');
//                source = source.replace(/<div>/gi, "");
//                source = source.replace(/<\/div>/gi, "<br />");

                // Webkit cleanup
                source = source.replace(/<br class\="webkit-block-placeholder">/gi, "<br />");
                source = source.replace(/<span class="Apple-style-span">(.*)<\/span>/gi, '$1');
                // tab ??????? ??????
                source = source.replace(/<span class="Apple-tab-span"[^>]*>(.*)<\/span>/gi, '&#160;&#160;&#160;&#160;$1');
                //source = source.replace(/^        /gi, '&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;');
                //source = source.replace(/^&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/gi, '&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;');
                source = source.replace(/ class="Apple-style-span"/gi, '');
                source = source.replace(/<span style="">/gi, '');
                source = source.replace(/<(\w[^>]*) id\s?=\s?""/gi, "<$1");

                // Remove padded paragraphs
                source = source.replace(/<p>\s*<br ?\/?>\s*<\/p>/gi, '<p>\u00a0</p>');
                source = source.replace(/<p>(&nbsp;|\s)*<\/p>/gi, '<p>\u00a0</p>');
                if (!this.options.semantics){
                    source = source.replace(/\s*<br ?\/?>\s*<\/p>/gi, '</p>');
                }

                // Replace improper BRs (only if XHTML : true)
                if (this.options.xhtml){
                    source = source.replace(/<br>/gi, "<br />");
                }

                if (this.options.semantics){
                    //remove divs from <li>
                    if (Browser.ie){
                        source = source.replace(/<li>\s*<div>(.+?)<\/div><\/li>/g, '<li>$1</li>');
                    }
                    //remove stupid apple divs
                    if (Browser.safari || Browser.chrome){
                        source = source.replace(/^([\w\s]+.*?)<div>/i, '<p>$1</p><div>');
                        source = source.replace(/<div>(.+?)<\/div>/ig, '<p>$1</p>');
                    }

                    //<p> tags around a list will get moved to after the list
                    if (!Browser.ie){
                        //not working properly in safari?
                        source = source.replace(/<p>[\s\n]*(<(?:ul|ol)>.*?<\/(?:ul|ol)>)(.*?)<\/p>/ig, '$1<p>$2</p>');
                        source = source.replace(/<\/(ol|ul)>\s*(?!<(?:p|ol|ul|img).*?>)((?:<[^>]*>)?\w.*)$/g, '</$1><p>$2</p>');
                    }

                    source = source.replace(/<br[^>]*><\/p>/g, '</p>'); // remove <br>'s that end a paragraph here.
                    source = source.replace(/<p>\s*(<img[^>]+>)\s*<\/p>/ig, '$1\n'); // if a <p> only contains <img>, remove the <p> tags

                    //format the source
                    source = source.replace(/<p([^>]*)>(.*?)<\/p>(?!\n)/g, '<p$1>$2</p>\n'); // break after paragraphs
                    source = source.replace(/<\/(ul|ol|p)>(?!\n)/g, '</$1>\n'); // break after </p></ol></ul> tags
                    source = source.replace(/><li>/g, '>\n\t<li>'); // break and indent <li>
                    source = source.replace(/([^\n])<\/(ol|ul)>/g, '$1\n</$2>'); //break before </ol></ul> tags
                    source = source.replace(/([^\n])<img/ig, '$1\n<img'); // move images to their own line
                    source = source.replace(/^\s*$/g, ''); // delete empty lines in the source code (not working in opera)
                }

                // Remove leading and trailing BRs
                source = source.replace(/<br ?\/?>$/gi, '');
                source = source.replace(/^<br ?\/?>/gi, '');

                // Remove useless BRs
                if (this.options.paragraphise) source = source.replace(/(h[1-6]|p|div|address|pre|li|ol|ul|blockquote|center|dl|dt|dd)><br ?\/?>/gi, '$1>');

                // Remove BRs right before the end of blocks
                source = source.replace(/<br ?\/?>\s*<\/(h1|h2|h3|h4|h5|h6|li|p)/gi, '</$1');

                // Semantic conversion
                source = source.replace(/<span style="font-weight: bold;\s?">(.*)<\/span>/gi, '<strong>$1</strong>');
                source = source.replace(/<span style="font-style: italic;\s?">(.*)<\/span>/gi, '<em>$1</em>');
                source = source.replace(/<b\b[^>]*>(.*?)<\/b[^>]*>/gi, '<strong>$1</strong>');
                source = source.replace(/<i\b[^>]*>(.*?)<\/i[^>]*>/gi, '<em>$1</em>');
//                source = source.replace(/<u\b[^>]*>(.*?)<\/u[^>]*>/gi, '<span style="text-decoration: underline;">$1</span>');
                source = source.replace(/<span style="text-decoration\s?:\s?underline;\s?">(.*)<\/span>/gi, '<u>$1</u>');
                source = source.replace(/<strong><span style="font-weight: normal;">(.*)<\/span><\/strong>/gi, '$1');
                source = source.replace(/<em><span style="font-weight: normal;">(.*)<\/span><\/em>/gi, '$1');
                source = source.replace(/<span style="text-decoration: underline;"><span style="font-weight: normal;">(.*)<\/span><\/span>/gi, '$1');
                source = source.replace(/<strong style="font-weight: normal;">(.*)<\/strong>/gi, '$1');
                source = source.replace(/<em style="font-weight: normal;">(.*)<\/em>/gi, '$1');
                
                if (Browser.safari || Browser.chrome) {
                    //var patt = /<(em|u|strike|strong|span)\s+style="(font-weight:\s?bold;\s?|font-style:\s?italic;\s?|text-decoration:\s?underline;\s?|text-decoration:\s?line-through;\s?|text-decoration:\s?underline\s?line-through;\s?)+">(.*)/gi;
                    var patt = /<(em|u|strike|strong|span)\s+style="([^"]+)">(.*)/gi;
                    function replacer(str, tag, mod, text){
                        var open = 1;
                        var index = 0;
                        var patt = new RegExp('</?' + tag, 'ig')
                        while (open !== 0) {
                            index = text.indexOf(tag, index + 1);
                            if (index === -1) {
                                return str;
                            }
                            if (text[index + tag.length] === '>') {
                                if (text[index - 1] == '/') {
                                    open--
                                } else {
                                    open++;
                                }
                            }        
                        }    
                        var b = mod.indexOf('bold');
                        var i = mod.indexOf('italic');
                        var u = mod.indexOf('underline');
                        var t = mod.indexOf('line-through');
                        var output = '';
                        if (tag !== 'span') output += '<' + tag + '>';
                        if (b !== -1) output += '<strong>';
                        if (i !== -1) output += '<em>';
                        if (u !== -1) output += '<u>';
                        if (t !== -1) output += '<strike>';
                        output += text.substring(0, index - 2);
                        if (t !== -1) output += '</strike>';
                        if (u !== -1) output += '</u>';
                        if (i !== -1) output += '</em>';
                        if (b !== -1) output += '</strong>';
                        if (tag !== 'span') output += '</' + tag + '>';
                        output += text.substring(index + tag.length + 1, text.length);
                        return output;
                    }
                    source = source.replace(patt, replacer);
                }
                
                if (Browser.ie) {
                    // ??????? ???? ?????? ?? ???????? ????, ?????? IE ????????? ??? ???? ????? url ??? email
                    source = source.replace(/(<p\sclass=.*?code[^>]*>.*?)<a[^>]*>(.*?)<\/a>(.*?<\/p>)/gi, '$1$2$3');
                }
                
                if (Browser.firefox) {
                    // ?????????? ?????? ?? ??????? ???????????? ????? ???? ??? firefox ?????????? ??????? href
                    source = source.replace(/(<a href=\")(?:\.\.\/)+(users[^\"]+)/, '$1' + window.location.protocol + '//' + window.location.host + '/$2');
                }
                
                // ???? ????????? html-??? ???? <a href="http://beta.free-lance.ru/">http://beta.free-lance.ru</a>
                // ?? ?? ????? ??????????????? ? ?????????
                // &lt;a href=&quot;http://beta.free-lance.ru/&quot;&gt;http://beta.free-lance.ru&lt;/a&gt;
                // ?????????? ???
                source = source.replace(/&lt;a([^<>]*?href=)(?:&quot;|\")([^<>]*?)(?:&quot;|\")([^<>]*?)&gt;([^<>]*?)&lt;\/a&gt;/ig, '<a$1"$2"$3>$4</a>');

                // Replace uppercase element names with lowercase
                source = source.replace(/<[^> ]*/g, function(match){
                    return match.toLowerCase();
                });

                // Replace uppercase attribute names with lowercase
                source = source.replace(/<[^>]*>/g, function(match){
                    match = match.replace(/ [^=]+=/g, function(match2){
                        return match2.toLowerCase();
                    });
                    return match;
                });

                // Put quotes around unquoted attributes
                source = source.replace(/<[^!][^>]*>/g, function(match){
                    match = match.replace(/( [^=]+=)([^"][^ >]*)/g, "$1\"$2\"");
                    return match;
                });

                //make img tags xhtml compatible <img>,<img></img> -> <img/>
                if (this.options.xhtml){
                    source = source.replace(/<img([^>]+)(\s*[^\/])>(<\/img>)*/gi, '<img$1$2 />');
                }
			
                //remove double <p> tags and empty <p> tags
                source = source.replace(/<p>(?:\s*)<p>/g, '<p>');
                source = source.replace(/<\/p>\s*<\/p>/g, '</p>');
			
                // Replace <br>s inside <pre> automatically added by some browsers
                source = source.replace(/<pre[^>]*>.*?<\/pre>/gi, function(match){
                    return match.replace(/<br ?\/?>/gi, '\n');
                });
                
                source = source.replace(/<p\sclass[^>]*>.*?<\/p>/gi, function(match){
                    return match.replace('\n', '<br/>');
                });
                
                source = source.replace(/<hr([^>]*)class="mooeditable-pagebreak"([^>]*)>/gi, '<cut>');

                // Final trim
                source = source.trim();
            }
            while (source != oSource);

            return source;
        }

    });

    MooEditable.Selection = new Class({

        initialize: function(win){
            this.win = win;
        },

        getSelection: function(){
            this.win.focus();
            return (this.win.getSelection) ? this.win.getSelection() : this.win.document.selection;
        },

        getRange: function(){
            var s = this.getSelection();

            if (!s) return null;

            try {
                return s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : null);
            } catch(e) {
                // IE bug when used in frameset
                return this.doc.body.createTextRange();
            }
        },

        setRange: function(range){
            if (range.select){
                Function.attempt(function(){
                    range.select();
                });
            } else {
                var s = this.getSelection();
                if (s.addRange){
                    s.removeAllRanges();
                    s.addRange(range);
                }
            }
        },

        selectNode: function(node, collapse){
            var r = this.getRange();
            var s = this.getSelection();

            if (r.moveToElementText){
                Function.attempt(function(){
                    r.moveToElementText(node);
                    r.select();
                });
            } else if (s.addRange){
                collapse ? r.selectNodeContents(node) : r.selectNode(node);
                s.removeAllRanges();
                s.addRange(r);
            } else {
                s.setBaseAndExtent(node, 0, node, 1);
            }

            return node;
        },

        isCollapsed: function(){
            var r = this.getRange();
            if (r.item) return false;
            return r.boundingWidth == 0 || this.getSelection().isCollapsed;
        },

        collapse: function(toStart){
            var r = this.getRange();
            var s = this.getSelection();

            if (r.select){
                r.collapse(toStart);
                r.select();
            } else {
                toStart ? s.collapseToStart() : s.collapseToEnd();
            }
        },

        getContent: function(){
            var r = this.getRange();
            var body = new Element('body');

            if (this.isCollapsed()) return '';

            if (r.cloneContents){
                body.appendChild(r.cloneContents());
            } else if (r.item != undefined || r.htmlText != undefined){
                body.set('html', r.item ? r.item(0).outerHTML : r.htmlText);
            } else {
                body.set('html', r.toString());
            }

            var content = body.get('html');
            return content;
        },

        getText : function(){
            var r = this.getRange();
            var s = this.getSelection();
            return this.isCollapsed() ? '' : r.text || (s.toString ? s.toString() : '');
        },

        getNode: function(){
            var r = this.getRange();

            if (!Browser.ie || Browser.version >= 9){
                var el = null;

                if (r){
                    el = r.commonAncestorContainer;

                    // Handle selection a image or other control like element such as anchors
                    if (!r.collapsed)
                        if (r.startContainer == r.endContainer)
                            if (r.startOffset - r.endOffset < 2)
                                if (r.startContainer.hasChildNodes())
                                    el = r.startContainer.childNodes[r.startOffset];

                    while (typeOf(el) != 'element') el = el.parentNode;
                }

                return document.id(el);
            }
		
            return document.id(r.item ? r.item(0) : r.parentElement());
        },

        insertContent: function(content){
            if (Browser.ie){
                var r = this.getRange();
                if (r.pasteHTML){
                    r.pasteHTML(content);
                    r.collapse(false);
                    r.select();
                } else if (r.insertNode){
                    r.deleteContents();
                    if (r.createContextualFragment){
                        r.insertNode(r.createContextualFragment(content));
                    } else {
                        var doc = this.win.document;
                        var fragment = doc.createDocumentFragment();
                        var temp = doc.createElement('div');
                        fragment.appendChild(temp);
                        temp.outerHTML = content;
                        r.insertNode(fragment);
                    }
                }
            } else {
                this.win.document.execCommand('insertHTML', false, content);
            }
        }

    });

    // Avoiding Locale dependency
    // Wrapper functions to be used internally and for plugins, defaults to en-US
    var phrases = {};
    MooEditable.Locale = {
	
        define: function(key, value){
            if (typeOf(window.Locale) != 'null') return Locale.define('en-US', 'MooEditable', key, value);
            if (typeOf(key) == 'object') Object.merge(phrases, key);
            else phrases[key] = value;
        },
	
        get: function(key){
            if (typeOf(window.Locale) != 'null') {
                return Locale.get('MooEditable.' + key);
            }
            return key ? phrases[key] : '';
        }
	
    };

    MooEditable.Locale.define({
        ok: 'OK',
        cancel: 'Cancel',
        bold: 'Bold',
        italic: 'Italic',
        underline: 'Underline',
        strikethrough: 'Strikethrough',
        unorderedList: 'Unordered List',
        orderedList: 'Ordered List',
        indent: 'Indent',
        outdent: 'Outdent',
        undo: 'Undo',
        redo: 'Redo',
        removeHyperlink: 'Remove Hyperlink',
        addHyperlink: 'Add Hyperlink',
        selectTextHyperlink: 'Please select the text you wish to hyperlink.',
        enterURL: 'Enter URL',
        enterImageURL: 'Enter image URL',
        addImage: 'Add Image',
        toggleView: 'Toggle View',
        cut       : 'Подкат'
    });

    MooEditable.UI = {};

    MooEditable.UI.Toolbar= new Class({

        Implements: [Events, Options],

        options: {
            /*
		onItemAction: function(){},
		*/
            'class': ''
        },
    
        initialize: function(options, edit){
            this.setOptions(options);
            this.el = new Element('div', {
                'class': 'mooeditable-ui-toolbar ' + this.options['class'] + ' c '
                });
            this.items = {};
            this.content = null;
            this.editor = edit;
        },
	
        toElement: function(){
            return this.el;
        },
	
        render: function(actions, leftButtons, rightButtons){
            if (this.content){
                this.el.adopt(this.content);
            } else {
                this.content = actions.map(function(action){
                	var left  = leftButtons.join(",");
                    var right = rightButtons.join(",");
                    var css = "";
                    if (left.indexOf(action) != -1) {
                        css = "b-button__left";
                    } else if (right.indexOf(action) != -1) {
                        css = "b-button__right";
                    }
                    return (action == '|') ? this.addSeparator() : this.addItem(action, css);
                }.bind(this));                
            }
            return this;
        },
	
        addItem: function(action, extendCss){
            if (!extendCss) {
                extendCss = "";
            }else {
            	extendCss = " " + extendCss;
            }
            var self = this;
            var act = MooEditable.Actions[action];
            if (!act) return;
            var type = act.type || 'button';
            var options = act.options || {};
            var item = new MooEditable.UI[type.camelCase().capitalize()](Object.append(options, {
                name: action,
                'class': action + '-item b-button b-button_mini' + extendCss,
                title: act.title,
                onAction: self.itemAction.bind(self)
            }), self.editor);
            this.items[action] = item;
            document.id(item).inject(this.el);
            
//            if(item.name == 'codeBlock') item.deactivate().disable();
            
            return item;
        },
	
        getItem: function(action){
            return this.items[action];
        },
	
        addSeparator: function(){
            return new Element('span', {
                'class': 'toolbar-separator'
            }).inject(this.el);
        },
	
        itemAction: function(){
            this.fireEvent('itemAction', arguments);
        },

        disable: function(except){
            Object.each(this.items, function(item){
                (item.name == except) ? item.activate() : item.deactivate().disable();
            });
            return this;
        },

        enable: function(){
            Object.each(this.items, function(item){
                item.enable();
            });
            return this;
        },
	
        show: function(){
            this.el.setStyle('display', '');
            return this;
        },
	
        hide: function(){
            this.el.setStyle('display', 'none');
            return this;
        }
	
    });

    MooEditable.UI.Button = new Class({

        Implements: [Events, Options],

        options: {
            /*
		onAction: function(){},
		*/
            title: '',
            name: '',
            text: 'Button',
            'class': '',
            shortcut: '',
            mode: 'icon'
        },

        initialize: function(options, edit){
            this.setOptions(options);
            this.name = this.options.name;
            this.editor = edit;
            this._state = false;
            this.render();
        },
	
        toElement: function(){
            return this.el;
        },
	
        render: function(){
            var self = this;
            var key = (Browser.Platform.mac) ? 'Cmd' : 'Ctrl';
            var shortcut = (this.options.shortcut) ? ' ( ' + key + '+' + this.options.shortcut.toUpperCase() + ' )' : '';
            var text = this.options.title || name;
            var title = text + shortcut;
            this.el = new Element('a', {
                'class': self.options['class'],
                'href': 'javascript:void(0)',
                title: title,
                html: '<em class="b-button__icon">' + text + '</em>',
                events: {
                    click: self.click.bind(self),
                    mousedown: function(e){
                        e.preventDefault();
                    }
                }
            });
            if (this.options.mode != 'icon') this.el.addClass('mooeditable-ui-button-' + this.options.mode);
		
            this.active = false;
            this.disabled = false;

            // add hover effect for IE
//            if (Browser.ie) 
            this.el.addEvents({
                mouseenter: function(e){
                    this.addClass('hover');
                },
                mouseleave: function(e){
                    this.removeClass('hover');
                }
            });            
		
            return this;
        },
	
        click: function(e){
            e.preventDefault();
            if (this.disabled) return;
            this.action(e);
            this.editor.fireEvent('menuButtonClicked', this);
        },
	
        action: function(){
            if (this.name === 'underline' || this.name === 'strikethrough') { // ??????????? ??????????? ?????? 0015886
                this._state = true;
            } else {
                this._state = !this._state;
            }
            this.fireEvent('action', [this].concat(Array.from(arguments)));
        },
	
        enable: function(){
            if (this.active) this.el.removeClass('b-button_active');
            if (!this.disabled) return;
            this.disabled = false;
            this.el.removeClass('disabled1').set({
                disabled: false//, opacity: 1
            });
            this._enable();
            return this;
        },
	
        disable: function(){
            if (this.disabled) return;
            this.disabled = true;
            this.el.addClass('disabled1').set({
                disabled: true//, opacity: 0.5
            });
            setTimeout(this._disable.bind(this), 10);
            
            return this;
        },
	
        activate: function(){
            if (this.disabled) return;
            this.active = true;
            this.el.addClass('b-button_active');
            return this;
        },
	
        deactivate: function(){
            this.active = false;
            this.el.removeClass('b-button_active');
            return this;
        },
        
        setLabel: function(str) {
            this.el.getElement('em').set('html', str);
        },
        
        resetLabel: function() {
            this.el.getElement('em').set('html', this.options.title);
        },
        
        _disable: function() {
            if (this.el._overlay) {
                return;
            }
            
            _bg = '#ffffff';
            p = $(this.el).getParents();
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
                    'width' :   this.el.getSize().x,
                    'height' :   this.el.getSize().y+3,
                    'left' :   this.el.getPosition().x,
                    'top' :   this.el.getPosition().y,
                    'background-color'  :  _bg,
                    'z-index'   :   110,
                    'opacity'   : .5
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

    MooEditable.UI.Dialog = new Class({

        Implements: [Events, Options],

        options:{
            /*
		onOpen: function(){},
		onClose: function(){},
		*/
            'class': '',
            contentClass: 'overlay '
        },

        initialize: function(html, options){
            this.setOptions(options);
            this.html = html;
		
            var self = this;
            this.el = new Element('div', {
                'class': ' i-shadow ' + self.options['class'],
                html: '<div class="b-shadow b-shadow_m b-shadow_zindex_2 b-shadow_left_10 ' + self.options.contentClass + '">' + 
                      '<div class="b-shadow__right"><div class="b-shadow__left"><div class="b-shadow__top"><div class="b-shadow__bottom">' +
                      '<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10" style="padding-bottom:20px">' +
                      html +
                      '</div>' +
                      '</div></div></div></div><div class="b-shadow__tl"></div><div class="b-shadow__tr"></div><div class="b-shadow__bl"></div><div class="b-shadow__br"></div><div class="b-shadow__icon b-shadow__icon_close"></div>' +
                      '</div>',
                styles: {
                    'display': 'none'
                },
                events: {
                    click: self.click.bind(self)
                }
            });
        },
	
        toElement: function(){
            return this.el;
        },
/*
 * Для возможности использования в диалогах <input type="file"/> отключил fireEvent для текстовых полей
* */
        click: function(e){
            if (e.target.hasClass('b-shadow__icon_close')) {
                this.close();
            }
                
            if (arguments[0].target.type != 'file') {
                this.fireEvent('click', arguments);
            }
            return this;
        },
	
        open: function(){
            this.el.setStyle('display', '');
            this.fireEvent('open', this);
            return this;
        },
	
        close: function(){
            this.el.setStyle('display', 'none');
            this.fireEvent('close', this);
            return this;
        }

    });
    
    MooEditable.UI.Dialog2 = new Class({

        Implements: [Events, Options],

        options:{
            'class': '',
            contentClass: 'overlay '
        },

        initialize: function(html, options){
            this.setOptions(options);
            this.html = html;
		
            var self = this;
            this.el = new Element('div', {
                'class': ' i-shadow ' + self.options['class'],
                html:   '<div class="b-shadow b-shadow_width_380 b-shadow_zindex_2" style="left:10px">' +
                            '<div class="b-shadow__right">' +
                                '<div class="b-shadow__left">' +
                                    '<div class="b-shadow__top">' +
                                        '<div class="b-shadow__bottom">' +
                                            '<div class="b-shadow__body b-shadow__body_bg_d4d5d7">' +
                                                html +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="b-shadow__tl"></div>' +
                            '<div class="b-shadow__tr"></div>' +
                            '<div class="b-shadow__bl"></div>' +
                            '<div class="b-shadow__br"></div>' +
                        '</div>',
                styles: {
                    'display': 'none'
                },
                events: {
                    click: self.click.bind(self)
                }
            });
        },
	
        toElement: function(){
            return this.el;
        },
/*
 * Для возможности использования в диалогах <input type="file"/> отключил fireEvent для текстовых полей
* */
        click: function(e){
            if (e.target.hasClass('b-shadow__icon_close')) {
                this.close();
            }
                
            if (arguments[0].target.type != 'file') {
                this.fireEvent('click', arguments);
            }
            return this;
        },
	
        open: function(){
            this.el.setStyle('display', '');
            this.fireEvent('open', this);
            return this;
        },
	
        close: function(){
            this.el.setStyle('display', 'none');
            this.fireEvent('close', this);
            return this;
        }

    });

    MooEditable.UI.DialogNew = new Class({

        Implements: [Events, Options],

        options:{
            /*
		onOpen: function(){},
		onClose: function(){},
		*/
            'class': '',
            contentClass: 'overlay ov-out'
        },

        initialize: function(html, options){
            this.setOptions(options);
            this.html = html;
            
            _html = '';
//            _html += '<div class="b-shadow b-shadow_inline-block">';
            _html += '<div class="b-shadow__right">';
            _html += '<div class="b-shadow__left">';
            _html += '<div class="b-shadow__top">';
            _html += '<div class="b-shadow__bottom">';
            _html += '<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">';
            _html += html;
            _html += '</div>';
            _html += '</div>';
            _html += '</div>';
            _html += '</div>';
            _html += '</div>';
            _html += '<div class="b-shadow__tl"></div>';
            _html += '<div class="b-shadow__tr"></div>';
            _html += '<div class="b-shadow__bl"></div>';
            _html += '<div class="b-shadow__br"></div>';
            _html += '<div class="b-shadow__icon b-shadow__icon_nosik"></div>';
//            _html += '</div>';
		
            var self = this;
            this.el = new Element('div', {
                'class' : 'b-shadow b-shadow_inline-block',
                html: _html,
                styles: {
                    'position': 'absolute',
                    'display': 'none',
//                    'margin-top': 2,
                    'margin' : 2,
                    'z-index': 1000
                },
                events: {
                    click: self.click.bind(self)
                }
            });
        },
	
        toElement: function(){
            return this.el;
        },
	
        click: function(){
            this.fireEvent('click', arguments);
            return this;
        },
	
        open: function(){
            this.el.setStyle('display', '');
            this.fireEvent('open', this);
            return this;
        },
	
        close: function(){
            this.el.setStyle('display', 'none');
            this.fireEvent('close', this);
            return this;
        }

    });

    MooEditable.UI.AlertDialog = function(alertText){
        if (!alertText) return;
        var html = alertText + ' <button class="dialog-ok-button">' + MooEditable.Locale.get('ok') + '</button>';
        return new MooEditable.UI.Dialog(html, {
            'class': 'mooeditable-alert-dialog',
            onOpen: function(){
                var button = this.el.getElement('.dialog-ok-button');
                (function(){
                    button.focus();
                }).delay(10);
            },
            onClick: function(e){
                e.preventDefault();
                if (e.target.tagName.toLowerCase() != 'button') return;
                if (document.id(e.target).hasClass('dialog-ok-button')) this.close();
            }
        });
    };

    MooEditable.UI.PromptDialog = function(questionText, answerText, fn){
        if (!questionText) return;
        var html =  '<div class="b-combo b-combo_inline-block b-combo_top_4">' +
                        '<div class="b-combo__input b-combo__input_width_280">' +
                            '<input class="b-combo__input-text b-combo__input-text_color_a7 dialog-input" type="text" value="'+questionText+'" size="80" name="" />' +
                        '</div>' +
                    '</div>' +
                    '<a class="b-button b-button_rectangle_color_transparent dialog_ok_button" href="javascript:void(0)">' +
                        '<span class="b-button__b1">' +
                            '<span class="b-button__b2">' +
                                '<span class="b-button__txt">Добавить</span>' +
                            '</span>' +
                        '</span>' +
                    '</a>';

        return new MooEditable.UI.Dialog(html, {
            'class': 'mooeditable-prompt-dialog',
            onOpen: function(){
                var input = this.el.getElement('.dialog-input');
                (function(){
                	var v = $$('div.cl-form input[name=yt_link]').get('value');                	
                    input.focus();
                    input.select();
                    if (js_video_validate(v)) {
                        input.set("value", v);
                    }
                    input.store('default-text', input.get('value'))
                }).delay(10);
            },
            onClick: function(e){
                e.preventDefault();
                var action;
                var target = e.target;
                var tag = target.tagName.toLowerCase();
                var input = this.el.getElement('.dialog-input');
                if (tag === 'span') {
                    if (target.getParent('a').hasClass('dialog_ok_button')) {
                        var answer = input.get('value');
                        input.set('value', answerText);
                        this.close();
                        if (fn) fn.attempt(answer, this);
                    }
                } else if (tag === 'input') {
                    if (target.hasClass('dialog-input')) {
                        input.set('value', '');
                        input.removeClass('b-combo__input-text_color_a7');
                    }
                }
            }
        });
    };
    
    MooEditable.UI.UploadImageDialog = function(questionText, answerText, fn) {
        if (!questionText) return;
        
        var html = 'Выберите изображение <form name="uploadImageForm" action=".", enctype="multipart/form-data" id="uploadImageForm" target="uploadImageIframe" method="POST"> <input type="hidden" value="' + _TOKEN_KEY + '" name="u_token_key" /> <input type="hidden" id="wysiwygUploadImageAction" value="wysiwygUploadImage" name="..." /> <input type="file" class="text dialog-input" name="wysiwyg_uploadimage" id="wysiwyg_uploadimage"/></form><div>&nbsp;</div>' 
        + '<div style="float:right"><button class="dialog-button dialog-ok-button">' + MooEditable.Locale.get('ok') + '</button>'
        + '<button class="dialog-button dialog-cancel-button">' + MooEditable.Locale.get('cancel') + '</button></div><div style="float:none">&nbsp;</div>';
    
        return new MooEditable.UI.Dialog(html, {
            'class': 'mooeditable-prompt-dialog',
            onOpen: function(){        	
                $('wysiwygUploadImageAction').name = 'action';
                var input = this.el.getElement('.dialog-input');
                (function(){
                    input.focus();
                    input.select();
                }).delay(10);
            },
            onClick: function(e){
                e.preventDefault();
                if (e.target.tagName.toLowerCase() != 'button') return;
                var button = document.id(e.target);
                var input = this.el.getElement('.dialog-input');
                if (button.hasClass('dialog-cancel-button')){
                    input.set('value', "");
                    this.close();
                } else if (button.hasClass('dialog-ok-button')){
                    this.close();                    
                    document.uploadIframe = false;
                    if (Browser.ie) {                        
                        document.uploadIframe = document.createElement('iframe');
                        document.uploadIframe.setAttribute('name', 'uploadImageIframe');
                        document.uploadIframe.setAttribute("style", "display:none");
                    } else {
                        document.uploadIframe = new Element("iframe", {name:"uploadImageIframe", style:"display:none"});
                    }
                    document.getElementsByTagName('body')[0].appendChild(document.uploadIframe);
                    document.uploadImageDialog = this;
                    document.fn = fn;                    
                    document.uploadImageComplete = function() {
                        var data = document.uploadIframe.contentWindow.document.getElementsByTagName("body")[0].innerHTML;
                            var fn = document.fn;
                            var url = data.substring(data.indexOf('url=') + 4, data.length);                            
                            if (fn && (data.indexOf('url=') != -1)) {
                                var url = data.match(/url=([^\&]*)/)[1];
                                var width = data.match(/width=([^\&]*)/)[1];
                                var height = data.match(/height=([^\&]*)/)[1];
                                fn.attempt([url, width, height], document.uploadImageDialog);
                            } else if (data.indexOf('msg=') != -1) {
                                var msg = data.substring(data.indexOf('msg=') + 4, data.length);
                                alert(msg);
                            }
                            document.uploadImageDialog.close();
                            var fileInput = $('wysiwyg_uploadimage');
                            var parentNode = fileInput.parentNode;
                            parentNode.removeChild(fileInput);
                            var fileInput = new Element("input", {type:"file", id:"wysiwyg_uploadimage", name:"wysiwyg_uploadimage", "class":"text dialog-input"});
                            fileInput.inject(parentNode, 'top');
                    };
                    
                    if (Browser.firefox) {
                        document.uploadIframe.contentWindow.onload =  function() {
                            document.uploadImageComplete();
                            document.getElementsByTagName("body")[0].removeChild(document.uploadIframe);
                        };
                        document.uploadIframe.contentWindow.onerror = function() {                            
                        }
                    } else if (document.uploadIframe) {
                        document.timeLimit = 30;
                        document.counter   = 0;
                        document.rc = setInterval(
                            function(){
                                var data = '';
                                try {
                                    var data = document.uploadIframe.contentWindow.document.getElementsByTagName("body")[0].innerHTML;
                                } catch(e) {;}
                                if (data.indexOf("status=") != -1 || document.counter / 2 > document.timeLimit){
                                    if (data.indexOf("status=") != -1) {
                                        document.uploadImageComplete();
                                    }
                                    clearInterval(document.rc);
                                    document.getElementsByTagName("body")[0].removeChild(document.uploadIframe);
                                }
                                document.counter++;
                            }, 500
                        );
                    }
                    document.uploadImageForm.submit();
                    $('wysiwygUploadImageAction').name = '...';
                }
            }
        });
    };
    
    MooEditable.UI.ImagePropsDialog = function(editor, text, fn) {

        var html =  '<div class="b-layout b-layout_pad_10">' +
                        '<input type="hidden" id="wysiwygUploadImageAction" value="wysiwygUploadImage" name="..." />' +
                        '<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">' +
                            '<tr class="b-layout__tr">' +
                                '<td class="b-layout__one b-layout__one_width_130" rowspan="3">' +
                                    '<img id="imagePreview" class="b-layout__pic b-layout__pic_center" />' +
                                '</td>' +
                                '<td class="b-layout__one b-layout__one_padleft_20">' +
                                    '<div class="b-layout__txt b-layout__txt_padtop_4">Ширина</div>' +
                                '</td>' +
                                '<td class="b-layout__one" style="height:35px">' +
                                    '<div class="b-combo b-combo_inline-block">' +
                                        '<div class="b-combo__input b-combo__input_width_50">' +
                                            '<input id="imagePropsWidth" class="b-combo__input-text" name="" type="text" size="80" value="">' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_4">px</div>' +
                                '</td>' +
                            '</tr>' +
                            '<tr class="b-layout__tr" style="height:35px">' +
                                '<td class="b-layout__one b-layout__one_padleft_20">' +
                                    '<div class="b-layout__txt b-layout__txt_padtop_4">Высота</div>' +
                                '</td>' +
                                '<td class="b-layout__one">' +
                                    '<div class="b-combo b-combo_inline-block">' +
                                        '<div class="b-combo__input b-combo__input_width_50">' +
                                            '<input id="imagePropsHeight" class="b-combo__input-text" name="" type="text" size="80" value="">' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_4">px</div>' +
                                '</td>' +
                            '</tr>' +
                            '<tr class="b-layout__tr">' +
                                '<td class="b-layout__one b-layout__one_padleft_20">' +
                                    '<div class="b-layout__txt b-layout__txt_padtop_4">Выравнивание&nbsp;</div>' +
                                '</td>' +
                                '<td class="b-layout__one" id="imagePropsAlignParent">' +
                                '</td>' +
                            '</tr>' +
                        '</table>' +
                    '</div>' +
                    '<div class="b-buttons b-buttons_bg_fff b-buttons_pad_10">' +
                        '<a class="dialog-button dialog-ok-button b-button b-button_rectangle_color_green" href="javascript:void(0)">' +
                            '<span class="b-button__b1">' +
                                '<span class="b-button__b2 b-button__b2_padlr_5">' +
                                    '<span class="b-button__txt">Сохранить</span>' +
                                '</span>' +
                            '</span>' +
                        '</a>' +
                        '<span class="b-buttons__txt">&nbsp;&nbsp;или&nbsp;&nbsp;</span>' +
                        '<a class="dialog-button dialog-cancel-button b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)">закрыть не сохраняя</a>' +
                    '</div>';
        
        // размеры картинки
        var imageNode, imageParent, aspectRatio;
        
        var aligns = {
            1: 'Влево',
            2: 'Вправо',
            3: 'По центру'
        };
        var attrToAlign = {
            'left': 1,
            'right': 2,
            'center': 3
        };
        var alignToAttr = {
            1: 'left',
            2: 'right',
            3: 'center'
        };
        window.aligns = aligns;
        
        var dialog2 = new MooEditable.UI.Dialog2(html, {
            'class': 'mooeditable-prompt-dialog',
            onOpen: function(){
                var node = editor.selection.getNode();
                if (node.get('tag') !== 'img') {
                    node = editor.doc.body.getElement('#new_image');
                    node && node.set('id', '');
                }
                if (!node || node.get('tag') !== 'img') {
                    var range = rangy.getSelection(editor.win);
                    if (range.rangeCount === 1) {
                        var nodes = range.getRangeAt(0).getNodes();
                        for (var i in nodes) {
                            if (!nodes.hasOwnProperty(i)) return;
                            if (nodes[i].get('tag') === 'img') {
                                node = nodes[i];
                                break;
                            }
                        }
                    }
                }
                if (node && node.get('tag') == 'img') {
                    imageNode = node;
                    
                    var imageWidth = parseInt(node.get('width')) || node.naturalWidth;
                    var imageHeight = parseInt(node.get('height')) || node.naturalHeight;
                    if (!imageHeight || !imageWidth) {
                        aspectRatio = false;
                    } else {
                        aspectRatio = imageHeight / imageWidth;;
                    }
                    
                    $('imagePropsWidth').value = imageWidth;
                    $('imagePropsHeight').value = imageHeight;
                    
                    var previewWidth, previewHeight, k;
                    if (imageHeight > imageWidth) {
                        previewHeight = 120;
                        k = imageHeight / previewHeight;
                        previewWidth = (imageWidth / k).toFixed();
                    } else {
                        previewWidth = 120;
                        k = imageWidth / previewWidth;
                        previewHeight = (imageHeight / k).toFixed();
                    }
                    $('imagePreview').set('src', node.get('src'));
                    $('imagePreview').set('width', previewWidth);
                    $('imagePreview').set('height', previewHeight);
                    
                    // выравнивание задается не для картинки а для родительского параграфа
                    var parent = node.getParent();
                    if (parent.get('tag') !== 'p') {
                        var parent = new Element('p', {align: 'left'}).wraps(node);
                        align = 1;
                    } else {
                        var alignAttr = parent.get('align') || 'left';
                        var align = attrToAlign[alignAttr];
                    }
                    imageParent = parent;
                    
                    $('imagePropsAlign').set('value', aligns[align]);
                    $('imagePropsAlign_db_id').set('value', align);
                } else {
                    this.close();
                }
            },
            onClick: function(e) {
                e.preventDefault();
                var button = e.target;
                if (!$(button).hasClass('dialog-button')) {
                    button = button.getParent('a');
                    if (!button || !button.hasClass('dialog-button')) {
                        return;
                    }
                }
                //var button = document.id(e.target);                
                if (button.hasClass('dialog-cancel-button')){                    
                    this.close();
                } else if (button.hasClass('dialog-ok-button')) {
                	this.close();
                	/*var node = editor.selection.getNode();
                    if (node.get('tag') !== 'img') {
                        node = editor.doc.body.getElement('#new_image');
                    }*/
                	var node = imageNode;
                    
                	if (node.get('tag') != 'img' && editor.selection.getContent().indexOf("<img") != 0) {                		
                        return;
                	}
                	var w = $('imagePropsWidth').value; 
                    var h = $('imagePropsHeight').value;
                    if ( (!parseInt(w) || !parseInt(h)) && (w.length > 0 || h.length > 0) ) {
                    	alert('Поля "Ширина" и "Высота" должны содержать целые значения');
                    	return;
                    }
                    
                    if (w.length > 0) {
                        node.set("width", w);
                    } else {
                    	node.set("width", '');
                    }
                    if (h.length > 0) {
                     	node.set("height", h);
                    }else {
                    	node.set("height", '');
                    }
                    
                    var alignValue = $('imagePropsAlign_db_id').value || 1;
                    var align = alignToAttr[alignValue];
                    imageParent.set("align", align);
                    imageNode.set('align', '');
                }
            }
        });
        
        
        var inputHeight = dialog2.el.getElement('#imagePropsHeight');
        var inputWidth = dialog2.el.getElement('#imagePropsWidth');
        inputHeight.addEvent('change', onHeightChange);
        inputWidth.addEvent('change', onWidthChange);

        function onHeightChange () {
            var height = parseInt(inputHeight.get('value'), 10);
            if (isNaN(height) || !aspectRatio) {
                return;
            }
            var width = Math.ceil(height / aspectRatio);
            inputHeight.set('value', height);
            inputWidth.set('value', width);
        }

        function onWidthChange () {
            var width = parseInt(inputWidth.get('value'), 10);
            if (isNaN(width) || !aspectRatio) {
                return;
            }
            var height = Math.ceil(width * aspectRatio);
            inputHeight.set('value', height);
            inputWidth.set('value', width);
        }
        
        var imagePropsAlignParent = dialog2.el.getElement('#imagePropsAlignParent');
        
        ComboboxManager.append(imagePropsAlignParent, 'b-combo__input b-combo__input_width_45 b-combo__input_multi_dropdown b-combo__input_min-width_40 b-combo__input_max-width_40 b-combo__input_arrow_yes b-combo__input_init_aligns', 'imagePropsAlign');
        
        return dialog2;
    };
    
    MooEditable.Actions = {

        bold: {
            title: MooEditable.Locale.get('bold'),
            options: {
                shortcut: 'b'
            },
            states: {
                tags: ['b', 'strong'],
                css: {
                    'font-weight': 'bold'
                }
            },
            events: {
                beforeToggleView: function(){
                    if(Browser.firefox){
                        var value = this.textarea.get('value');
                        var newValue = value.replace(/<strong([^>]*)>/gi, '<b$1>').replace(/<\/strong>/gi, '</b>');
                        if (value != newValue) this.textarea.set('value', newValue);
                    }
                },
                attach: function(){
                    if(Browser.firefox){
                        var value = this.textarea.get('value');
                        var newValue = value.replace(/<strong([^>]*)>/gi, '<b$1>').replace(/<\/strong>/gi, '</b>');
                        if (value != newValue){
                            this.textarea.set('value', newValue);
                            this.setContent(newValue);
                        }
                    }
                }
            }
        },
	
        italic: {
            title: MooEditable.Locale.get('italic'),
            options: {
                shortcut: 'i'
            },
            states: {
                tags: ['i', 'em'],
                css: {
                    'font-style': 'italic'
                }
            },
            events: {
                beforeToggleView: function(){
                    if (Browser.firefox){
                        var value = this.textarea.get('value');
                        var newValue = value.replace(/<embed([^>]*)>/gi, '<tmpembed$1>')
                        .replace(/<em([^>]*)>/gi, '<i$1>')
                        .replace(/<tmpembed([^>]*)>/gi, '<embed$1>')
                        .replace(/<\/em>/gi, '</i>');
                        if (value != newValue) this.textarea.set('value', newValue);
                    }
                },
                attach: function(){
                    if (Browser.firefox){
                        var value = this.textarea.get('value');
                        var newValue = value.replace(/<embed([^>]*)>/gi, '<tmpembed$1>')
                        .replace(/<em([^>]*)>/gi, '<i$1>')
                        .replace(/<tmpembed([^>]*)>/gi, '<embed$1>')
                        .replace(/<\/em>/gi, '</i>');
                        if (value != newValue){
                            this.textarea.set('value', newValue);
                            this.setContent(newValue);
                        }
                    }
                }
            }
        },
	
        underline: {
            title: MooEditable.Locale.get('underline'),
            options: {
                shortcut: 'u'
            },
            states: {
                tags: ['u'],
                css: {
                    'text-decoration': 'underline'
                }
            }
        },
	
        strikethrough: {
            title: MooEditable.Locale.get('strikethrough'),
            options: {
                shortcut: 's'
            },
            states: {
                tags: ['s', 'strike'],
                css: {
                    'text-decoration': 'line-through'
                }
            },
            events: {
                editorKeyDown: function() {
                    this.checkStates.delay(10, this);
                },
                menuButtonClicked: function(btn) {
                    if (btn.name === 'strikethrough' && !btn._state) {
                        btn.deactivate();
                    }
                }
            }
        },
	
        insertunorderedlist: {
            title: MooEditable.Locale.get('unorderedList'),
            states: {
                tags: ['ul']
            }
        },
	
        insertorderedlist: {
            title: MooEditable.Locale.get('orderedList'),
            states: {
                tags: ['ol']
            }
        },
	
        indent: {
            title: MooEditable.Locale.get('indent'),
            states: {
                tags: ['blockquote']
            }
        },
	
        outdent: {
            title: MooEditable.Locale.get('outdent')
        },
	
        undo: {
            title: MooEditable.Locale.get('undo'),
            options: {
                shortcut: 'z'
            }
        },
	
        redo: {
            title: MooEditable.Locale.get('redo'),
            options: {
                shortcut: 'y'
            }
        },
	
        unlink: {
            title: MooEditable.Locale.get('removeHyperlink')
        },

        createlink: {
            title: MooEditable.Locale.get('addHyperlink'),
            options: {
                shortcut: 'l'
            },
            states: {
                tags: ['a']
            },
            dialogs: {
                alert: MooEditable.UI.AlertDialog.pass(MooEditable.Locale.get('selectTextHyperlink')),
                prompt: function(editor){
                    return MooEditable.UI.PromptDialog(MooEditable.Locale.get('enterURL'), 'http://', function(url){
                        var regExp = /^(https?:\/\/)?([\da-zа-я\.-]+)\.(([a-z\.]{2,6})|рф).*$/;
                        if (!regExp.test(url.toLowerCase()))
                        {
                            alert("Некорректный URL")
                            return;
                        }
                        if (url.indexOf(".рф") != -1) {
						    url = url.trim();
						    var http = "http://";
						    if (url.indexOf("https://") == 0) {
								http = "https://";
							}
							url = url.replace(http, "");
							var lim = url.indexOf("/");
							var domain = url.substring(0, lim);							
							var tail = url.replace(domain, "");
							if (lim == -1) {
								domain = url;
								tail = '';
							}
							domain = punycode.toASCII(domain);
							url = http + domain + tail;
						}
                        editor.execute('createlink', false, url.trim());
                    });
                }
            },
            command: function(){
                var selection = this.selection;
                var dialogs = this.dialogs.createlink;
                if (selection.isCollapsed()){
                    var node = selection.getNode();
                    if (node.get('tag') == 'a' && node.get('href')){
                        selection.selectNode(node);
                        var prompt = dialogs.prompt;
                        prompt.el.getElement('.dialog-input').set('value', node.get('href'));
                        prompt.open();
                    } else {
                        dialogs.alert.open();
                    }
                } else {
                    var text = selection.getText();
                    var prompt = dialogs.prompt;
                    if (urlRegex.test(text)) prompt.el.getElement('.dialog-input').set('value', text);
                    prompt.open();
                }
            }
        },

        urlimage: {
            title: MooEditable.Locale.get('addImage'),
            options: {
                shortcut: 'm'
            },
            dialogs: {
                prompt: function(editor){
                    return MooEditable.UI.PromptDialog(MooEditable.Locale.get('enterImageURL'), 'http://', function(url){
                       editor.execute('insertimage', false, url.trim());
                    });
                }
            },
            command: function(){
                this.dialogs.urlimage.prompt.open();
            }
        },
        
        attachimage: {
            title: MooEditable.Locale.get('addImage'),
            options: {
                shortcut: 'm'
            },
            dialogs: {
                uploader: function(editor){
                    return MooEditable.UI.UploadImageDialog(MooEditable.Locale.get('enterImageURL'), 'http://', function(url, width, height){
                        var img = '<img id="new_image" src="' + url + '"';
                        img += ' width="' + width + '" ';
                        img += ' height="' + height + '" ';
                        img += ' style="padding:10px" ';
                        img += '/>';
                        var s = editor.getContent();
                        if (s.indexOf('<p>') == -1) {
                            editor.selection.insertContent('<p align="left">&nbsp;' + img + '&nbsp;</p>');
                        } else {
                            editor.selection.insertContent(img);
                        }
                        setTimeout(function(){
                            editor.dialogs.attachimage.imageProperties.open()
                        }, 1);
                    });
                },
                imageProperties: function(editor){
                    return MooEditable.UI.ImagePropsDialog(editor, '', function(){;});
                }
            },
            command: function(){
            	var node = this.selection.getNode();
				if (node.get('tag') == 'img') {										
					this.dialogs.attachimage.imageProperties.open();
					return;					
				}else {
                    var s = this.selection.getContent();
                    if (s.indexOf('<img') == 0) {
                        this.dialogs.attachimage.imageProperties.open();
                        return;
                    }
                }
                this.dialogs.attachimage.uploader.open();
            }
        },
        
        cut: {
            title: MooEditable.Locale.get('cut'),
            options: {
                shortcut: 'j'
            },
            dialogs: {
                alert: MooEditable.UI.AlertDialog.pass(MooEditable.Locale.get('selectTextHyperlink'))
            },
            command: function(e){                
                var range = this.selection.getRange();                
                var el = this.doc.createElement('cutplace');
                try {
                    range.insertNode(el);
                }catch(e) {;}
                
                var content = this.getContent();
                content = content.replace(/\[cut\]/g, '');
                this.setContent(content);               
                
                var node = this.selection.getNode();
                var el = node.getElementsByTagName('cutplace')[0];                
                if (this.win.document.createRange && !Browser.ie) {
                    range.selectNode(el);                    
                    this.selection.setRange(range);                    
                } else {
                    var rng = node.createTextRange();
                    rng.moveToElementText(el);
                    rng.select();
                }
                try {
                	el.parentNode.removeChild(el);
                } catch(e){;}
               	var s = "[cut]";
            	this.selection.insertContent(s);            	
        	    this.saveContent();        	    
            }
        },
        
       movie: {
            title: MooEditable.Locale.get('addVideo'),
            options: {
                //shortcut: 'w'
            },            
            dialogs: {
                prompt: function(editor){
                    return MooEditable.UI.PromptDialog(MooEditable.Locale.get('enterVideoURL'), 'Добавить адрес видео', function(url){
                        var videoImg, editedImg;
                        
                        // проверяем, редактирование или добавление новой ссылки
                        editedImg = editor.doc.body.getElement('img.wysiwyg_video.edit_url');
                        if (editedImg) {
                            editedImg.removeClass('edit_url');
                            // заменяем ссылку на новую
                            editedImg.set('video_url', url);
                        } else {
                            // добавляем новое видео
                            // при добавлении видео в редакторе, добавляем просто картинку и сохраняем ссылку
                            editor.selection.insertContent('<p><img class="wysiwyg_video" id="wysiwyg_video" src="/images/video.png" video_url="' + url + '">&nbsp;</p>');
                            // находим только что вставленый DOM элемент
                            videoImg = editor.doc.body.getElement('#wysiwyg_video');
                            videoImg.set('id', null);
                            // обработчик события при клике
                            videoImg.addEvent('click', editor.videoClickEvent);
                        }
                        /*if ($('yt_link')) {
                            $('yt_link').set('value', url.trim());
                        } else if($('youtube_link')) {
                        	$('youtube_link').set('value', url.trim());
                            $('yt_box').setStyle('display', 'block');
                            $('add_yt_box').setStyle('display', 'none');
                            JSScroll($('yt_box'), true);
                        }*/
                    });
                }
            },
            command: function(){
                var dialog = this.dialogs.movie.prompt.open();
                this.doc.body.getElements('img.wysiwyg_video').removeClass('edit_url');
            }
        },
        
       toggleview: {
            title: MooEditable.Locale.get('toggleView'),
            command: function(){
                (this.mode == 'textarea') ? this.toolbar.enable() : this.toolbar.disable('toggleview');
                this.toggleView();
            }
        },

       insertcode: {
            title: MooEditable.Locale.get('insertcode'),
            options: {
//                shortcut: 'l'
            },
            states: function(el, item) {
//                console.log('el ' + el.hasClass('code'));
                item.resetLabel();
                
                if (el.hasClass('code')) {
//                    item.setLabel('???????? ???');
                    if (el.get('text').trim().length == 0) {
                        el.dispose();
                        return false;
                    }
                    
                    this.selection.selectNode(el);
                    this.lastCodeEl = this.selection.getNode();
                    return false;
                }
                
                this.lastCodeEl = null;
            },
            
        dialogs: {
                alert: MooEditable.UI.AlertDialog.pass(MooEditable.Locale.get('selectTextHyperlink'))
                ,
                prompt: function(editor){
//                    return MooEditable.UI.PromptDialog(MooEditable.Locale.get('enterURL'), 'http://', function(url){
//                        var regExp = /^(https?:\/\/)?([\da-z?-??\.-]+)\.(([a-z\.]{2,6})|??).*$/;
//                        if (!regExp.test(url.toLowerCase()))
//                        {
//                            alert("???????????? ??????")
//                            return;
//                        }
//                        editor.execute('createlink', false, url.trim());
//                    });
                    return MooEditable.UI.InsertCodeDialog(editor);
                }
            },
        command: function(){
                var selection = this.selection;
                var dialogs = this.dialogs.insertcode;
//                if (selection.isCollapsed()){
//                    var node = selection.getNode();
////                    if (node.get('tag') == 'a' && node.get('href')){
//                        selection.selectNode(node);
                        var prompt = dialogs.prompt;
//                        prompt.el.getElement('.dialog-input').set('value', node.get('href'));
//                        prompt.open();
////                    } else {
////                        dialogs.alert.open();
////                    }
//                } else {
//                    var text = selection.getText();
//                    var prompt = dialogs.prompt;
//                    if (urlRegex.test(text)) prompt.el.getElement('.dialog-input').set('value', text);
                    prompt.open();
//                }
            }
        }

    };

    MooEditable.Actions.Settings = {};

    Element.Properties.mooeditable = {

        get: function(){
            return this.retrieve('MooEditable');
        }

    };

    Element.implement({

        mooEditable: function(options){
            var mooeditable = this.get('mooeditable');
            if (!mooeditable) mooeditable = new MooEditable(this, options);
            return mooeditable;
        }

    });

})();
