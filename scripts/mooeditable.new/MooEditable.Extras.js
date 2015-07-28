/*
---

name: MooEditable.Extras

description: Extends MooEditable to include more (simple) toolbar buttons.

license: MIT-style license

authors:
- Lim Chee Aun
- Ryan Mitchell

requires:
# - MooEditable
# - MooEditable.UI
# - MooEditable.UI.MenuList

provides: 
- MooEditable.Actions.formatBlock
- MooEditable.Actions.justifyleft
- MooEditable.Actions.justifyright
- MooEditable.Actions.justifycenter
- MooEditable.Actions.justifyfull
- MooEditable.Actions.removeformat
- MooEditable.Actions.insertHorizontalRule

...
*/

MooEditable.Locale.define({
    blockFormatting: 'Block Formatting',
    paragraph: 'Paragraph',
    heading1: 'Heading 1',
    heading2: 'Heading 2',
    heading3: 'Heading 3',
    alignLeft: 'Align Left',
    alignRight: 'Align Right',
    alignCenter: 'Align Center',
    alignJustify: 'Align Justify',
    removeFormatting: 'Remove Formatting',
    insertHorizontalRule: 'Insert Horizontal Rule'
});

Object.append(MooEditable.Actions, {

    formatBlock: {
        title: MooEditable.Locale.get('blockFormatting'),
        type: 'menu-custom-list',
        options: {
            list: [
            {
                text: MooEditable.Locale.get('paragraph'),
                value: 'p'
            },

            {
                text: MooEditable.Locale.get('heading1'),
                value: 'h1',
                style: 'font-size:24px; font-weight:bold;'
            },

            {
                text: MooEditable.Locale.get('heading2'),
                value: 'h2',
                style: 'font-size:18px; font-weight:bold;'
            },

            {
                text: MooEditable.Locale.get('heading3'),
                value: 'h3',
                style: 'font-size:14px; font-weight:bold;'
            }
            ]
        },
        //        states: {
        //            tags: ['p', 'h1', 'h2', 'h3']
        //        },
        states: function(el, item) {
            tags = ['p', 'h1', 'h2', 'h3'];
            
            if (!tags.contains(this.selection.getNode().tagName.toLowerCase())) {
                return;
            }
            
            try {
                item.activate(this.selection.getNode().tagName.toLowerCase());
            } catch(e) {}
        },
        command: function(menulist, name){
            var argument = '<' + name + '>';
            this.focus();
            this.selection.selectNode(this.selection.getNode());
            this.execute('formatBlock', false, argument);
            this.selection.collapse(0);
            
            if (Browser.opera) {
                nd = this.selection.getNode();
                if (/br>$/i.test(nd.get('html'))) {
                    if (nd.getLast().nodeName && nd.getLast().nodeName.toLowerCase() == 'br') {
                        nd.getLast().dispose();
                    }
                }
            }
        }
    },
	
    justifyleft:{
        title: MooEditable.Locale.get('alignLeft'),
        states: {
            css: {
                'text-align': 'left'
            }
        }
    },
	
    justifyright:{
        title: MooEditable.Locale.get('alignRight'),
        states: {
            css: {
                'text-align': 'right'
            }
        }
    },
	
    justifycenter:{
        title: MooEditable.Locale.get('alignCenter'),
        states: {
            tags: ['center'],
            css: {
                'text-align': 'center'
            }
        }
    },
	
    justifyfull:{
        title: MooEditable.Locale.get('alignJustify'),
        states: {
            css: {
                'text-align': 'justify'
            }
        }
    },
	
    removeformat: {
        title: MooEditable.Locale.get('removeFormatting')
    },
	
    insertHorizontalRule: {
        title: MooEditable.Locale.get('insertHorizontalRule'),
        states: {
            tags: ['hr']
        },
        command: function(){
            this.selection.insertContent('<hr>');
        }
    }

});


if (hljs) {
    
    MooEditable.UI.InsertCodeDialog = function(editor){
        var html =  '';
        
        html += '<div>';
        html += '<select>';
        html += '<option value=null>-- Код --</option>';
        
        $each(hljs.LANGUAGES, function(l, nm) {
            var _tx = nm.capitalize();
            if (nm.toLowerCase() == 'javascript') {
                _tx = 'JavaScript';
            }
            
            html += '<option value="{value}">{label}</option>'.substitute({'label' : _tx, 'value': nm});
        });
        
        html += '</select>';
        html += '</div><br/>';
        
        html += '<div>';
        html += '<textarea style="height: 150px;"></textarea>';
        html += '</div>';
        
        html += '<button class="dialog-button dialog-ok-button">Сохранить</button>';
        html += '<button class="dialog-button dialog-cancel-button">Отмена</button>';
        
        editor.insertCodeParagraph = function () {
            var p, p2, p3, p4, len; // вставленный параграф
            
            // тег на котором курсор
            p = editor.selection.getNode();
            // если код вставляется в BODY
            if (p.get("tag") === "body") {
                len = p.getChildren().length;
                // проверяем пусто ли окно редактирования
                // у некоторых браузеров (opera, firefox) в пустом окне уже есть тег BR
                if (len === 0 || (len === 1 && p.getChildren()[0].get("tag") === "br")) {
                    if (Browser.opera) {
                        // для оперы просто оборачиваем <br> в параграф для кода
                        p2 = new Element('p', {id: "code-insertion"}).wraps(p.getChildren()[0], 'top');
                    } else {
                        p2 = new Element('p', {id: "code-insertion"}).inject(p, 'top');
                    }
                } else { // если окно редактирования не пустое
                    // вставляем параграф на месте курсора
                    if (Browser.ie) {
                        editor.doc.execCommand('insertparagraph', false, "code-insertion");
                    } else if (Browser.chrome || Browser.safari) {
                        editor.doc.execCommand('insertparagraph', false);
                        p2 = editor.selection.getNode();
                        p3 = new Element('p', {id: 'code-insertion'}).wraps(p2);
                        p2.dispose();
                        // убираем паразитный div
                        p4 = p3.getPrevious();
                        if (p4 && p4.get("tag") === 'div') {
                            p4.dispose();
                        }
                    } else if (Browser.firefox) {
                        editor.doc.execCommand('insertparagraph', false, null);
                        p2 = editor.selection.getNode();
                        p2.set("id", "code-insertion");
                    } else if (Browser.opera) {
                        editor.doc.execCommand('insertparagraph', false, null);
                        p2 = editor.selection.getNode();
                        p2.set("id", "code-insertion");
                    }
                }
            // если курсор находится на пустом теге, то код будем вставлять в него
            } else if (p.get("tag") === "p" && p.get("text").replace("&nbsp;", "").trim().length === 0) {
                // модифицируем существующий тег
                p.set("id", "code-insertion-mod");
            // в остальных случаях, когда курсор находится внутри тега, код вставляем после него
            } else {
                if (p.get("tag") !== "p") {
                    p = p.getParents('p')[0];
                }
                new Element('p', {id: 'code-insertion'}).inject(p, "after");
            }
            
            // сохраняем контент, при этом вызывается функция cleanup
            editor.saveContent();
            
        }
        
        return new MooEditable.UI.Dialog(html, {
            'class': 'mooeditable-prompt-dialog',
            // при открытии окна редактирования кода
            onOpen: function(){
                this.editNode = false;
                this.el.setStyle('width', editor.iframe.getSize().x);
                
                setTimeout(function(ed) {
                    
                    var s = rangy.getSelection(ed.win);
                    var r = s.rangeCount ? s.getRangeAt(0) : null;
                    
                    // если не выделено ничего, то надо добавить новый тег, в который будем вставлять код
                    if (!r || !r.getNodes().length) {
                        ed.insertCodeParagraph();
                        this.el.getElement('textarea').focus();
                        return;
                    }
                    // далее если что-либо выделено
                    var nd = r.getNodes()[0];

                    // выделен ли блок с кодом
                    if (nd.nodeName.toLowerCase() !== 'p') {
                        nd = nd.parentNode;
                    }
                    var is_inn = nd.nodeName.toLowerCase() == 'p' && nd.hasClass('code');
                    
                    if (is_inn) {
                        this.editNode = nd;
                        // заполняем данные формы
                        this.el.getElement('textarea').set('value', nd.get('text'));
                        
                        var _css = nd.get('class').split(' ');
                        _css = _css.length > 1 ? _css[1] : 'null';
                        this.el.getElement('select').set('value', _css);
                    } else { // если выделен не блок с кодом, а обычный текст
                        ed.insertCodeParagraph();
                    }
                    
                    this.el.getElement('textarea').focus();
                    
                }.bind(this, editor), 10);
            },
            onClose: function() {
                this.editNode = false;
                this.el.getElement('textarea').set('value', '');
                this.el.getElement('select').set('value', 'null');
                // при отмене вставки кода удаляем элемент-заготовку для кода
                editor.doc.getElements('#code-insertion').dispose();
                // и удаляем id у тех элементов которые не уже были
                editor.doc.getElements('#code-insertion-mod').removeProperty("id");
                
                
//                hljs_render(editor.doc);
            },
            onClick: function(e){
                e.preventDefault();
                if (e.target.tagName.toLowerCase() != 'button') return;
                
                var btn = e.target;
                if (!btn) return;
                
                if (btn.hasClass('dialog-ok-button')) {
                    
                    var sl = btn.getParent().getElement('select');
                    var tx = btn.getParent().getElement('textarea');
                    
                    if (tx.get('value').trim().lenth == 0) {
                        alert('Поле не должно быть пустым.')
                        return;
                    }
                    
                    if (sl.get('value') == 'null') {
                        alert('Вы не выбрали тип кода');
                        return;
                    }
                    
                    var code = tx.get('value');
                    code = code.replace(/</g, "&lt;");
                    
                    // если блок с кодом редактируется, то есть был ранее
                    if (this.editNode) {
                        this.editNode.set('html', code );
                        
                        var old_css = this.editNode.get('class').split(' ');
                        var old_css = old_css[1];
                        
                        this.editNode.removeClass(old_css);
                        this.editNode.addClass(sl.get('value'));
                        
                        this.close();
                        return;
                    }
                    
                    editor.focus();
                    editor.fireEvent('editorKeyDown', [Event]);
                    var p1 = editor.doc.getElement("#code-insertion") || editor.doc.getElement("#code-insertion-mod");

                    p1.set('html', code);
                    p1.addClass('code');
                    p1.addClass(sl.get('value'));
                    p1.removeAttribute('id');
                    
                    // нельзя оставлять блок кода последним. Если он последний, то добавляем после него пустой параграф
                    var p2 = p1.getNext();
                    if (!p2) {
                        p2 = new Element('p');
                        p2.inject(p1, "after");
                        p2.set('html', '<br>');
                    }
                    
                    tx.set('value', '');
                    this.close();
                
                } else if (btn.hasClass('dialog-cancel-button')) {
                    this.close();
                }
            }
        });
    };
}


if(hljs) {

    var langlist = [];
    langlist.push({
        text: '-- ' + MooEditable.Locale.get('codeSelect') + ' -- ',
        value: '0'
    });
    var tags = [];
    $each(hljs.LANGUAGES, function(l, nm) {
        _tx = nm.capitalize();
        if (nm.toLowerCase() == 'javascript') {
            _tx = 'JavaScript';
        }
        langlist.push({
            text: _tx,
            value: nm
        });
        tags.push(nm);
    });

    Object.append(MooEditable.Actions, {
        codeBlock: {
            title: MooEditable.Locale.get('codeFormatting'),
            type: 'menu-custom-list',
            options: {
                list: langlist
            },
            states: function(el, item) {
                str = this.selection.getText();

                if(str.length == 0) {
                //                    item.deactivate().disable();
                } else {
                    item.enable();
                }

                if((this.selection.getNode().tagName.toLowerCase() == 'p'
                    && this.selection.getNode().hasClass('code'))
                || (this.selection.getNode().getParent('p[class*=code]')
                    && this.selection.getNode().getParent('p[class*=code]').hasClass('code'))) {

                    item.enable();
                    cls = this.selection.getNode().get('class');
                    cls = cls.replace('code ', '');

                    if(item.ul.getElement('li[class='+cls+']')) {
                        item.setLabelFromEl(item.ul.getElement('li[class='+cls+']'));
                    }
                }
            },
            command: function(menulist, name){
                var s, r, nd, ts, tsid, txt, _cb, pp, is_last, _p;
                if(name == '0') { // конвертация code в обычный текст
                    if((this.selection.getNode().tagName.toLowerCase() == 'p'
                        && this.selection.getNode().hasClass('code'))
                    || (this.selection.getNode().getParent('p[class*=code]')
                        && this.selection.getNode().getParent('p[class*=code]').hasClass('code'))) {

                        s = this.selection;
                        r = s.getRange();
                        nd = s.getNode();

                        if(Browser.Engine.trident) nd.set('class', '');
                        nd.removeAttribute('class');
                    }
                } else { // конвертация текста в code
                    
                    ts = Math.random();
                    tsid = 'el'+ts; // временный id для элемента
                    
                    s = this.selection; // выбранный текст, который станет кодом
                    
                    // проверяем, не является ли выбраный текст уже кодом
                    if((this.selection.getNode().tagName.toLowerCase() == 'p'
                        && this.selection.getNode().hasClass('code'))
                    || (this.selection.getNode().getParent('p[class*=code]')
                        && this.selection.getNode().getParent('p[class*=code]').hasClass('code'))) {

                        s.getNode().removeAttribute('class');
                        s.getNode().set('class', 'code ' + name);

                        return;
                    } 
                    
                    txt = s.getContent();
                    
                    if (!txt.trim().length) {
                        alert('Необходимо выделить текст.');
                        return false;
                    }
                    
                    txt = txt.replace( /<(H[1-6]|HR|P|DIV|ADDRESS|PRE|FORM|TABLE|LI|OL|UL|TD|CAPTION|BLOCKQUOTE|CENTER|DL|DT|DD|SCRIPT|NOSCRIPT|STYLE)[^>]*>(.*?)<\/\1>/gi, '$2<br/>' ) ;
                    // убираем в IE последний <br>
                    if (Browser.ie) {
                        txt = txt.replace(/<br\/>$/, '');
                    }
                    
                    if (txt != s.getNode().get('html')) { // если сложное выделение (более одного тега)

                        s.insertContent('<p id="'+tsid+'"><br></p>');
                        
                        _cb = $(this.doc.getElementById(tsid));
                        
                        // удаляем вложенность элементов p, которая, если не убрать, добавит по пустому тегу до и после кода
                        if (Browser.Engine.webkit || Browser.opera) {
                            var wrap = _cb.getParent();
                            _cb.replaces(wrap);
                        }
                        // в ИЕ параграфы появляются до и после кода, удаляем их
                        if (Browser.ie && Browser.version === 9) {
                            if (_cb.getNext()) _cb.getNext().dispose();
                            if (_cb.getPrevious()) _cb.getPrevious().dispose();
                        }
                        _cb.set('html', txt);
                        try {
                            s.selectNode(_cb);
                            s.collapse(1);
                        } catch(e) {}
                        
                    } else { // если выделен только один тег
                        if (s.getNode().tagName.toLowerCase() == 'p') {
                            s.getNode().set('id', tsid);
                        } else {
                            pp = new Element('p', {
                                'id' : tsid,
                                'html' : txt
                            });
                            pp.replaces(s.getNode());
                        }
                    }
                    
                    _cb = $(this.doc.getElementById(tsid));
                    
                    /*if (Browser.ie) {
                        if (_cb.previousSibling) {
                            if (_cb.previousSibling.nodeName.toLowerCase() == 'br') {
                                _cb.previousSibling.dispose();
                            } else if (_cb.previousSibling.nodeName.toLowerCase() == 'p') {
                                if (_cb.previousSibling.getLast() && _cb.previousSibling.getLast().nodeName.toLowerCase() == 'br') {
                                    _cb.previousSibling.getLast().dispose();
                                }
                            }
                        }
                        
                        if (_cb.nextSibling) {
                            if (_cb.nextSibling.nodeName.toLowerCase() == 'p') {
                                if (_cb.nextSibling.getFirst() 
                                    && _cb.nextSibling.getFirst().nodeName.toLowerCase() == 'br') {
                                    _cb.nextSibling.getFirst().dispose();
                                }
                            }
                        }
                        
                        if (_cb.childNodes[_cb.childNodes.length-1].nodeName.toLowerCase() == 'br') {
                            if (_cb.childNodes[_cb.childNodes.length-1]) {
                                $(_cb.childNodes[_cb.childNodes.length-1]).dispose();
                            }
                        }
                    }*/
                    
                    _cb.addClass('code '+name);
                    _cb.removeAttribute('id');
                    
                    // нужно ли добавлять новую строку в конце
                    /**
                     * возвращает true - если элемент последний, и надо добавить новую строку после него
                     * и false - если не последний
                     */
                    function isLast(el) {
                        var next = el.getNext();
                        if (next && next.get('text').replace(/\s/, '')) return false;
                        el = el.getParent();
                        if (el.nodeName == 'BODY' || !el) return true;
                        return isLast(el);
                    }
                    is_last = isLast(_cb);
                    //is_last = !s.getNode().nextSibling;
                    
                    if (is_last) {
                        _p = new Element('p', {'html' : '&nbsp;'});
                        try {
                            _p.inject(_cb, 'after');
                        } catch (ex) {}
                        
                        if (_p.nextSibling && _p.nextSibling.dispose) {
                            _p.nextSibling.dispose();
                        }
                    }
                    
                    return false;
                    //***********************
                    // конец функции
                    //***********************
                    
                    s = this.selection;
                    r = s.getRange();

                    if((this.selection.getNode().tagName.toLowerCase() == 'p'
                        && this.selection.getNode().hasClass('code'))
                    || (this.selection.getNode().getParent('p[class*=code]')
                        && this.selection.getNode().getParent('p[class*=code]').hasClass('code'))) {

                        s.getNode().removeAttribute('class');
                        s.getNode().set('class', 'code ' + name);

                        return;
                    } else if(s.getNode().getParent('p[class=code]')) {
                        
                    }

                    content = s.getText();
                    mode_el = (s.getNode().childNodes.length && content == s.getNode().childNodes[0].nodeValue);


                    if(mode_el) {
                        nd = s.getNode();
                        nd.set('class', 'code ' + name);

                        return;
                    }

                    el = new Element('span');
                    el.appendChild(document.createTextNode(content));
                    content = el.get('html');
                    content = content.replace(/\n/gi, '<br />');
                    content = content.replace(/\s/gi, ' ');

                    dv = new Element('div', {
                        'class': name,
                        'html': content
                    });


                    if(Browser.Engine.webkit) {
                        isBody = (s.getNode().nodeName.toLowerCase() == 'body');

                        r = s.getRange();
                        s.insertContent('');
                        s.collapse(1);

                        if(r.startContainer != r.endContainer) {
                            if(r.startOffset == 0) {
                                this.doc.execCommand('insertParagraph', true, 'p');

                                nd = s.getNode().getPrevious();
                                s.selectNode(nd);
                                s.collapse(1);
                            } else {
                                this.doc.execCommand('insertParagraph', true, 'p');
                            }
                        } else {
                            this.doc.execCommand('insertParagraph', true, 'p');

                            //                            nd = s.getNode().getPrevious();
                            //                            s.selectNode(nd);
                            s.setRange(r);
                            s.collapse(0);

                            this.doc.execCommand('insertParagraph', null, 'p');
                        }

                    } else {
                        this.doc.execCommand('insertParagraph', null, 'p');
                        s.insertContent(' ');

                        s.collapse(1);

                        dt = {
                            'class' : 'code ' + name,
                            'content': dv.get('html')
                        };
                        s.insertContent('<p class="{class}">{content}</p>'.substitute(dt));

                        return;
                    }

                    nd = s.getNode();

                    s.selectNode(nd);
                    s.collapse(1);

                    nd.set('class', 'code ' + name);
                    this.doc.execCommand('insertHTML', false, dv.get('html'));

                    return;
                }

            }
        }
    });
}