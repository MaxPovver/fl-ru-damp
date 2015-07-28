var wysiwyg_setup = {
    'defaults'   : 'toggleview | bold italic strikethrough | formatBlock | insertunorderedlist insertorderedlist | pagebreak | codeBlock ',
    'admin'      : 'toggleview | bold italic | formatBlock | insertunorderedlist insertorderedlist | createlink unlink ',
    'comments'   : 'toggleview | bold italic strikethrough | formatBlock | insertunorderedlist insertorderedlist | createlink unlink | attachimage | movie | cut | codeBlock | undo redo',
    'insertcode' : 'toggleview | bold italic strikethrough | formatBlock | insertunorderedlist insertorderedlist | createlink unlink | attachimage | movie | cut | codeBlock | undo redo'
};

//Если action присутствует в этом списке, значит сответствующей кнопке будет добавлен css класс left или right в зависимости от её расположения между сепараторами
var wysiwyg_toolbarPairButton = "insertunorderedlist,insertorderedlist,createlink,unlink,undo,redo";

function initWysiwyg() {
                        
    rangy.init();

    var blockElems = /^(H[1-6]|HR|P|DIV|ADDRESS|PRE|FORM|TABLE|LI|OL|UL|TD|CAPTION|BLOCKQUOTE|CENTER|DL|DT|DD|SCRIPT|NOSCRIPT|STYLE|IMG)$/i;



    var initBuff = function () {
        cb = new Element('div', {
            'id' : 'INSERTION_MARKER'
        });
        cb.inject(this.doc.body);

        this.selection.selectNode(cb);
        this.selection.collapse(1);

        return cb;
    }


    var cleanHtml = function(html, editor){
        
        html = html.replace(/<(DIR|FONT|SPAN|CODE|embed|object|sup)[\s\S]*?>/gi, '');
        html = html.replace(/<\/DIR>/gi, '');
        html = html.replace(/<\/FONT>/gi, '');
        html = html.replace(/<\/SPAN>/gi, '');
        html = html.replace(/<\/CODE>/gi, '');
        html = html.replace(/<\/(embed|object|sup)>/gi, '');
        
        html = html.replace(/<p.*?class=.*?code\s(.*?)['"].*?>([\s\S]*?)<\/p>/gi, '<code><$1>$2</$1></code>');
        
        html = html.replace(/<a\sname.*?>(.*?)<\/a>/gi, '$1');
        
        html = html.replace(/<PRE.*?>/gi, '<p>');
        html = html.replace(/<\/PRE>/gi, '</p>');
        //убираем переносы строки
        if (Browser.opera) {
            html = html.replace(/\n/gi, '');
        }
        
        html = html.replace(/\n/gi, '<br />');
        
        html = html.replace(/<(ol)[^>]*/gi, '<$1');
        html = html.replace(/<(ul)[^>]*/gi, '<$1');
        html = html.replace(/<([^\s>]+)[^>]!(class=.*?code)*>/gi, '<$1>');
        html = html.replace( /<(span|font)[^>]*>(.*?)(<\/\1>)?/gi, '$2' ) ;
//        html = html.replace( /<(meta|style|script)[^>]*>(.*?)(<\/\1>)?/gi, '' ) ;
        html = html.replace( /<(div)[^>]*>(.*?)<\/\1>/gi, '$2' ) ;
        html = html.replace(/<a[^>]* onclick="([^\"]*)"([^>]*)/gi, "<a href='#'");


        html = html.replace(/<STYLE.*?>.*?<\/STYLE>/gi, '');
       

        html = html.replace(/<o:p>\s*<\/o:p>/g, '');
        html = html.replace(/<o:p>[\s\S]*?<\/o:p>/g, '&nbsp;');

        // remove mso-xxx styles.
        html = html.replace(/\s*mso-[^:]+:[^;"]+;?/gi, '');

        // remove margin styles.
        html = html.replace(/\s*MARGIN: 0cm 0cm 0pt\s*;/gi, '');
        html = html.replace(/\s*MARGIN: 0cm 0cm 0pt\s*"/gi, "\"");

        html = html.replace(/\s*TEXT-INDENT: 0cm\s*;/gi, '');
        html = html.replace(/\s*TEXT-INDENT: 0cm\s*"/gi, "\"");

        html = html.replace(/\s*TEXT-ALIGN: [^\s;]+;?"/gi, "\"");

        html = html.replace(/\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"");

        html = html.replace(/\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"");

        html = html.replace(/\s*tab-stops:[^;"]*;?/gi, '');
        html = html.replace(/\s*tab-stops:[^"]*/gi, '');

        // remove FONT face attributes.
        html = html.replace(/\s*face="[^"]*"/gi, '');
        html = html.replace(/\s*face=[^ >]*/gi, '');

        html = html.replace(/\s*FONT-FAMILY:[^;"]*;?/gi, '');

        // remove class attributes
        html = html.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3");

        // remove styles.
        html = html.replace(/<(\w[^>]*) style="([^\"]*)"([^>]*)/gi, "<$1$3");

        // remove style, meta and link tags
        html = html.replace(/<STYLE[^>]*>[\s\S]*?<\/STYLE[^>]*>/gi, '');
        html = html.replace(/<(?:META|LINK)[^>]*>\s*/gi, '');

        // remove empty styles.
        html = html.replace(/\s*style="\s*"/gi, '');

        html = html.replace(/<SPAN\s*[^>]*>\s*&nbsp;\s*<\/SPAN>/gi, '&nbsp;');

        html = html.replace(/<SPAN\s*[^>]*><\/SPAN>/gi, '');

        // remove lang attributes
        html = html.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3");

        html = html.replace(/<SPAN\s*>([\s\S]*?)<\/SPAN>/gi, '$1');

        html = html.replace(/<FONT\s*>([\s\S]*?)<\/FONT>/gi, '$1');

        // remove XML elements and declarations
        html = html.replace(/<\\?\?xml[^>]*>/gi, '');

        // remove w: tags with contents.
        html = html.replace(/<w:[^>]*>[\s\S]*?<\/w:[^>]*>/gi, '');

        // remove tags with XML namespace declarations: <o:p><\/o:p>
        html = html.replace(/<\/?\w+:[^>]*>/gi, '');

        // remove comments [SF BUG-1481861].
        html = html.replace(/<\!--[\s\S]*?-->/g, '');

        html = html.replace(/<(U|I|STRIKE)>&nbsp;<\/\1>/g, '&nbsp;');

        html = html.replace(/<H\d>\s*<\/H\d>/gi, '');

        // remove "display:none" tags.
        html = html.replace(/<(\w+)[^>]*\sstyle="[^"]*DISPLAY\s?:\s?none[\s \S]*?<\/\1>/ig, '');

        // remove language tags
        html = html.replace(/<(\w[^>]*) language=([^ |>]*)([^>]*)/gi, "<$1$3");

        // remove onmouseover and onmouseout events (from MS word comments effect)
        html = html.replace(/<(\w[^>]*) onmouseover="([^\"]*)"([^>]*)/gi, "<$1$3");
        html = html.replace(/<(\w[^>]*) onmouseout="([^\"]*)"([^>]*)/gi, "<$1$3");

        // the original <Hn> tag send from word is something like this: <Hn style="margin-top:0px;margin-bottom:0px">
        html = html.replace(/<H(\d)([^>]*)>/gi, '<h$1>');

        // word likes to insert extra <font> tags, when using IE. (Wierd).
        html = html.replace(/<(H\d)><FONT[^>]*>([\s\S]*?)<\/FONT><\/\1>/gi, '<$1>$2<\/$1>');
        html = html.replace(/<(H\d)><EM>([\s\S]*?)<\/EM><\/\1>/gi, '<$1>$2<\/$1>');

        // remove "bad" tags
        html = html.replace(/<\s+[^>]*>/gi, '');

        // remove empty tags (three times, just to be sure).
        // This also removes any empty anchor
        html = html.replace(/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, '');
        html = html.replace(/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, '');
        html = html.replace(/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, '');

        // Convert <p> to <br />
        if (!editor.options.paragraphise) {
            html.replace(/<p>/gi, '<br />');
            html.replace(/<\/p>/gi, '');
        }
        html = html.replace( /<br[^>]*>/g, '<br />' ) ;
        html = html.replace( /<((ul|ol|li|p|b)[^>]*)/gi, '<$1' ) ;
        html = html.replace(/<(\w[^>]*) id=([^ |>]*)([^>]*)/gi, "<$1");
        html = html.replace( /<(table|tbody|thead)[^>]*>(.*?)(<\/\1>)?/gi, '$2' ) ;
        html = html.replace( /<(td)[^>]*>(.*?)(<\/\1>)?/gi, '$2 ' ) ;
        html = html.replace( /<(tr)[^>]*>(.*?)(<\/\1>)?/gi, '$2<br/>' ) ;
        

        html = html.replace(/<code><([^\s>]+)[^>]*>(.*?)<\/\1><\/code>/gi, '<p class="code $1">$2</p>');
        
        
//        html = html.replace(/<pre(.*?)>(.*?)<\/pre>/gi, '<p>$2</p>');
        
//        
//        html = html.replace(/<([^><]+?)([^a-z_\-]on\w*|xmlns)(\s*=\s*[^><]*)([><]*)/gi, "<$1$4");
//        html = html.replace(/<(\w[^>]*)(javascript:[^\"]*)([^>]*)/gi, "<$1$3");

        return html;
    };

    var cleanPaste = function(e) {
        var txtPastet = e.clipboardData && e.clipboardData.getData ?
        e.clipboardData.getData('text/html') : // Standard
        window.clipboardData && window.clipboardData.getData ?
        window.clipboardData.getData('Text') : // MS
        false;
        if(!!txtPastet) {
            this.selection.insertContent(cleanHtml(txtPastet, this));
            new Event(e).stop();
        }
        else { // no clipboard data available
            this.selection.insertContent('<pre id="INSERTION_MARKER">&nbsp;</pre>');
            this.txtMarked = this.doc.body.get('html');
            this.doc.body.set('html', '');
            replaceMarkerWithPastedText.delay(8, this);
        }
        return this;
    };
    

    var _cleanPaste = function() {

        doc = this.doc.body;
        s = this.selection;
        this.pasteRange = s.getRange();
        s.insertContent('<span id="INSERTION_MARKER" style="display:inline-block;"> </span>');
        this.saveContent();
        this.origHtml = doc.get('html');

//        nd = this.doc.getElementById('INSERTION_MARKER');

//        if (!nd) return;

        if (Browser.firefox) {
            doc.set('html', '');
        } else {
            s.selectNode(doc);
            alert(JSON.encode(s.getRange()))
            s.getRange().select();
//            this.setdContent('');
        }
//        s.collapse(0);

//        return;
//        this.selection.selectNode(nd);
//        this.selection.collapse(0);
return;


        (function() {
            return;
            try {
                
                doc = this.doc.body;
                s = this.selection;

                s.selectNode(doc);
                s.collapse(0);
                this.setContent('');
                s.insertContent(cleanHtml(doc.get('html'), this));
                this.saveContent();

                alert(cleanHtml(doc.get('html'), this))
//                return;

                newText = doc.get('html');
//                newText = cleanHtml(newText, this)
    //            newText = newText.replace(/^\s/, '');

                if (Browser.firefox) {
                    doc.set('html', '');
                    doc.set('html', this.origHtml);
                } else {
                    s.selectNode(doc);
                    s.insertContent(this.origHtml);
                }
                
                nd = this.doc.getElementById('INSERTION_MARKER');
                if (!nd) return;

                alert(doc.get('html'))

                s.selectNode(nd);
                s.collapse(0);
                s.insertContent(newText);
//                nd.dispose();
//                this.saveContent();

//                s.selectNode(doc);
//                s.insertContent(cleanHtml(doc.get('html'), this));
//
//                s.selectNode(nd);
//                s.setRange(r);
//                s.collapse(1);
//
//                nd.dispose();

//                s.setRange(this.pasteRange);
//                s.insertContent(newText);
//                s.collapse(0);
            } catch (e) {
                alert(e);
            }

        }).delay(1, this);

    };


    var replaceMarkerWithPastedText = function(){
        var txtPastet = this.doc.body.get('html');
        var txtPastetClean = cleanHtml(txtPastet, this);
        this.doc.body.set('html', this.txtMarked);
        var node = this.doc.body.getElementById('INSERTION_MARKER');
        this.selection.selectNode(node);
        this.selection.insertContent(txtPastetClean);

        this.doc.body.set('html', cleanHtml(this.doc.body.get('html'), this));
        return this;
    };
    
    var cleanPaste2 = function (evt, editor) {
//        evt.preventDefault();
//        return false;
        //у ИЕ проблемы с событием blur в iframe, добавляем костыль
        if (Browser.ie && editor.editorDisabled) {
            return;
        }
        function inner () {
            this.selection.collapse(1);
            
            // когда текст копируется из Word и вставляется в редактор, то у него есть класс MsoNormal
            // в этом тексте неизвестно откуда появляются переносы строк которые в cleanHtml заменяются на <br>
            // исправляем это заменяя все переносы на пробелы
            if (this.doc.body.getElements('p.MsoNormal').length) {
                var inner = this.doc.body.get('html');
                inner = inner.replace(/\n/gi, ' ');
                inner = this.doc.body.set('html', inner);                
            }
                
            _uid = Math.random();
            _uid = ['_', _uid, '_insertion_mark_'].join('');
            if (Browser.firefox) {
            	//этот участок кода только для firefox, потомучто
            	//во время вставки в нем мелькала строка _uid
            	//суть в том что вставляем не строку _uid а тег SPAN который не мелькает
                this.selection.insertContent('<span id=INSERTION_MARK></span>');
                var html = this.doc.body.get('html');
                html = html.replace('<span id="INSERTION_MARK"></span>', _uid);
                html = cleanHtml(html, this);
                this.doc.body.set('html', html);
            } else {
                this.selection.insertContent(_uid);
                this.doc.body.set('html', cleanHtml(this.doc.body.get('html'), this));
            }

//            this.doc.body.set('html', this.doc.body.get('html'));
//            this.setContent(cleanHtml(this.getContent(), this));

            if (!Browser.ie) {
                this.doc.body.set('html', this.doc.body.get('html').replace(_uid, '<span id=INSERTION_MARK></span>'));
                nod = this.doc.getElementById('INSERTION_MARK');
                nod.set('html', 'temp');

                this.selection.getSelection().removeAllRanges();
                rr = this.doc.createRange();
                rr.selectNode(nod);
                this.selection.setRange(rr);
                this.selection.collapse(1);
                nod.dispose();
            } else {
                this.doc.body.set('html', this.doc.body.get('html').replace(_uid, '<span id="INSERTION_MARK"></span>'));
                nod = this.doc.getElementById('INSERTION_MARK');
                nod.innerHTML = ' ';
                ctrl = this.doc.selection.createRange;
                this.selection.selectNode(nod);
                this.selection.collapse(1);
                $(nod).dispose();
            }

        }
        if (Browser.firefox) {
            inner.delay(0, editor);
        } else {
            inner.delay(5, editor);
        }
    };
    
    var getMousePosition = function (e) {
        e = e ? e : window.event;
        var cursor = {
            x:0, 
            y:0
        };
        if (e.pageX || e.pageY) {
            cursor.x = e.pageX;
            cursor.y = e.pageY;
        } 
        else {
            var de = document.documentElement;
            var b = document.body;
            cursor.x = e.clientX + 
            (de.scrollLeft || b.scrollLeft) - (de.clientLeft || 0);
            cursor.y = e.clientY + 
            (de.scrollTop || b.scrollTop) - (de.clientTop || 0);
        }
        return cursor;
    };

    $each($$('textarea.wysiwyg'), function(el) {
        t = new Element('div', {
            'class' : 'fe',
            'html' : '<div class="fe-in"><div class="fe-plain"></div></div>'
        });
        t.inject(el, 'after');
        el.inject(t.getElement('.fe-plain'));
        
        if(!$(el).retrieve('MooEditable')) {
            actions_setup = $(el).hasClass('wysiwyg-comments') ? wysiwyg_setup.comments : wysiwyg_setup.defaults;
            if($(el).getProperty('conf') != undefined ) {
                try {
                    if (wysiwyg_setup[$(el).getProperty('conf')]) {
                        actions_setup = wysiwyg_setup[$(el).getProperty('conf')];
                    }
                } catch (excp) {}
            }
            el.mooEditable({
                'html': '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">{BASEHREF}<style>{BASECSS} {EXTRACSS}</style>{EXTERNALCSS}<link href="/css/wysiwyg-txt.css" type="text/css" rel="stylesheet" /></head><body><p></p></body></html>',
                paragraphise: true,
                actions: actions_setup
            });
//            return;

            rel = el.retrieve('MooEditable').iframe;
            edit = el.retrieve('MooEditable');
            
            _codeEl = function(e) {
                if (!this.toolbar.getItem('insertcode')) {
                    return false;
                }
                ls = this.doc.body.getElements('[class*=code]');
                if (ls.length > 0) {
                    for (i = 0; i < ls.length; i++) {
                        if (ls[i].get('text').trim().length > 0) continue;
                        
                        ls[i].dispose();
                    }
                }
                
                nd = this.selection.getNode();
                prevent = e.key == 'backspace' || e.key == 'delete';
                is_inn = nd.nodeName.toLowerCase() == 'p' && nd.hasClass('code');
                
                if (!prevent && is_inn) {
                    e.preventDefault();
                    return false;
                }
                
                if (e.key == 'enter' && is_inn) {
                    e.preventDefault();
//                    console.log('enter');
                    return false;
                }
            };
            
            _codeElSelect = function(e) {
                if (!this.toolbar.getItem('insertcode')) {
                    return false;
                }
                nd = this.selection.getNode();
                nd = e.target;
                
                if (nd.nodeName.toLowerCase() == 'p' && nd.hasClass('code')) {
                    e.preventDefault();
                    this.selection.selectNode(nd);
                    
                    this.toolbar.getItem('insertcode').action();
                    
                    return false;
                } 
            };
            
            _codeElArr = function(e) {
                if (!this.toolbar.getItem('insertcode')) {
                    return false;
                }
                
                var keys = ['left', 'up', 'right', 'down'];
                
                if (!keys.contains(e.key)) return;
                
                var s = rangy.getSelection(this.win);
                var r = s.rangeCount ? s.getRangeAt(0) : null;
                
                if (!r || !r.getNodes().length) return;
                
                var nd = r.getNodes()[0];
                
                var nd_;
                if (['left', 'up'].contains(e.key)) {
                    if (nd && nd.nodeName === "#text") {
                        nd = nd.parentNode;
                    }
                    if (nd && nd.get && nd.get("tag") === "p" && nd.hasClass('code')) {
                    	//предыдущий элемент
                        nd_ = nd.getPrevious();
                        if (!nd_) {
                            return;
                        }
                        // надо установить курсор в конце элемента nd_
                     // для этого временно создаем элемент после nd_, ставим на него курсор и удаляем
                        var nd__ = new Element('insmarker', {'html': '|'}).inject(nd_, 'bottom');
                        s = this.selection;
                        s.selectNode(nd__);
                        s.collapse(1);
                        e.preventDefault();
                        nd__.dispose();
                    }
                    return false;
                } else if (['right', 'down'].contains(e.key)) {
                    if (nd && nd.nodeName === "#text") {
                        nd = nd.parentNode;
                    }
                    if (nd && nd.get && nd.get("tag") === "p" && nd.hasClass('code')) {
                        nd_ = nd.getNext();

                        if (!nd_) {
                        	// если после блока с кодом нет ничего, то добавляем параграф
                            var nd_ = new Element('p');
                            nd_.inject(nd, "after");
                            nd_.set('html', '<br>');
                        }
                        if (nd_) {
                            s = this.selection;
                            s.selectNode(nd_);
                            s.collapse(1);
                            e.preventDefault();
                        }
                        return false
                    }
                }

            };
            
            
            _codeElDel = function(e) {
                if (!this.toolbar.getItem('insertcode')) {
                    return false;
                }
                
                keys = ['delete', 'backspace', 'enter'];
                if (!keys.contains(e.key)) return;
                
//                console.log(e.key);
                
                if (e.key != 'enter') {
                    s = rangy.getSelection(this.win);
                    r = s.rangeCount ? s.getRangeAt(0) : null;

                    if (!r.getNodes().length) return;

                    nd = r.getNodes()[0];
                } else {
                    nd = this.selection.getNode();
                    
                    if (!nd) return;
                }
                
                is_inn = nd.nodeName.toLowerCase() == 'p' && nd.hasClass('code');
                
                if (!is_inn) {
                    return;
                }
                
                e.preventDefault();
                
                if (!Browser.chrome || e.key != 'enter') {
                    nd.set('html', '');
                }
                nd.removeAttribute('class');
                    
                this.selection.selectNode(nd);
                this.selection.collapse();
                
                return false;
            };
            
            
            edit.addEvent('editorKeyDown', _codeElDel);
            edit.addEvent('editorKeyDown', _codeElArr);
            edit.addEvent('editorKeyDown', _codeEl);
            edit.addEvent('editorKeyUp', _codeEl);
            edit.addEvent('editorMouseDown', _codeElSelect);
            edit.addEvent('editorMouseUp', _codeElSelect);
            
            _rootEl = function(e, ed) {
                fc = this.doc.body.firstChild;
                
                var _getFirstBlock = function(p) {
                    ret = null;
                    cn = p.childNodes;
                    for (i = 0; i < cn.length; i++) {
                        if (cn[i].nodeType != 1) continue;
                        if (!cn[i].nodeName.test(blockElems)) continue;
                        ret = cn[i];
                        break;
                    }
                    return ret;
                };
                 
                if (fc && fc.nodeName.toLowerCase() == '#text' ) {
                    tx = fc.nodeValue;
                    
                    fb = _getFirstBlock(this.doc.body);
                    
                    if (fb) {
                        this.selection.insertContent('<span id=INS_MARKER>INS_MARKER</span>');
                        
                        s = rangy.getSelection(this.win);
                        r = s.rangeCount ? s.getRangeAt(0) : null;
                        
                        r.setStartBefore(fc);
                        r.setEndBefore(fb);
                        
                        s.setSingleRange(r);
                        tx = s.toHtml();
                        
                        r.getNodes(false, function(nd) {
                            try {
                                nd.parentNode.removeChild(nd);
                            } catch (err) {}
                        });
                        
                        fb.set('html', tx + '<br />' + fb.get('html'));
                        this.selection.selectNode(this.doc.body.getElementById('INS_MARKER'));
                        this.doc.body.getElementById('INS_MARKER').dispose();
                        
                    } else {
                        this.selection.insertContent('<span id=INS_MARKER>INS_MARKER</span>');
                        tx = this.doc.body.get('html');
                        this.doc.body.set('html', '<p>' + tx + '</p>');
                        
                        this.selection.selectNode(this.doc.body.getElementById('INS_MARKER'));
                        this.doc.body.getElementById('INS_MARKER').dispose();
                    }
                    return;
                }
                
                if (!fc || (fc && !fc.nodeName.test(blockElems))) {
                    this.selection.insertContent('<p><br></p>');
                    if (Browser.ie) {
                        this.selection.selectNode(this.doc.body.getFirst('p'));
                    }
                    return;
                }
            };
            
            
            _rootEl2 = function(e, ed) {
                
                if (e.key == 'enter') {
                    s = this.selection;
                    if (s.getNode().hasClass('code')) {
                        e.stop();

                        s = rangy.getSelection(this.win);
                        r = s.rangeCount ? s.getRangeAt(0) : null;

                        _br = this.doc.createElement('br');
                        r.insertNode(_br);

                        if (!_br.nextSibling) {
                            _br = this.doc.createElement('br');
                            r.insertNode(_br);
                        }

                        r.selectNode(_br);
                        s.setSingleRange(r);
                        s.collapseToEnd();

                        return;
                            
                    }
                }
            };
            
            
            edit.addEvent('editorKeyDown', _rootEl);
            edit.addEvent('editorKeyDown', _rootEl2);
            edit.addEvent('editorKeyDown', function(e, ed) {
                if(e.key == 'enter' && e.control) {
                    edit.oldContent = edit.saveContent().getContent();
                    if($(this).getParent('form') != undefined) {
                        $(this).getParent('form').submit();
                    }
                    return;
                }
            });
            //edit.addEvent('editorMouseEnter', function(e, ed) {
            //    fc = this.doc.body.innerHTML;
            //    this.doc.body.innerHTML = cleanHtml(fc, ed);
            //});


            el.getParent().setStyle('position', 'relative');
            spn = new Element('div', {
                'class': 'fe-resize'
            });
            spn.inject(el, 'after');

            edit.addEvent('attach', function() {
//                el.getParent().setStyle('position', 'relative');
//                this.textarea.setStyle('clear', 'both');
//                spn = new Element('div', {
//                    'class': 'fe-resize'
//                });
//                spn.inject(el, 'after');
                if (!Browser.ie7) {
                    this.set('onmousedown', 'resizeEditor(event);');
                } else {
                    this.addEvent('mousedown', function(e) {
//                        alert(e)
                        resizeEditor();
                        return false;
                    });
                }
            }.bind(spn));

            edit.addEvent('beforeToggleView', function() {
                src = this.doc.body.get('html');
                if (this.mode == 'textarea') {
                    src = this.textarea.get('value');
                    src = src.replace(/<([^><]+?)([^a-z_\-]on\w*|xmlns)(\s*=\s*[^><]*)([><]*)/gi, "<$1$4");
                    this.textarea.set('value', src);
                }
            });

//            alert(edit.options.paragraphise)
            edit.addEvent('editorPaste', cleanPaste2.bind(edit));

            //            edit.addEvent('editorPaste', function() {
            ////                cpconst = '###' +Math.random()+ '###';
            ////                sel = edit.selection;
            ////                window.selectionBuff = sel.getRange();
            ////                s = sel;
            ////
            ////                s.selectNode(edit.doc.body);
            ////                s.collapse(0);
            ////                edit.doc.execCommand('insertHTML', false, '<pre id=clipboard></pre>');
            ////
            ////                nd = edit.doc.getElementById('clipboard');
            ////                nd.set('html', cpconst);
            ////                s.selectNode(nd);
            //
            //
            //                (function() {
            ////                    nd.setStyle('display', 'none');
            ////
            ////                    r = window.selectionBuff;
            ////                    s.setRange(r);
            ////
            ////                    tx = cleanupWord(edit.cleanup(nd.get('html').replace(cpconst, '')));
            ////
            ////                    s.insertContent(tx);
            ////                    nd.dispose();
            //                    edit.setContent(cleanupWord(edit.getContent()));
            ////                    edit.toggleView();
            //                }).delay(1);
            //            });
            edit.addEvent('editorFocus', function() {
                if($('msgtext_error')) $('msgtext_error').style.display = 'none';
                var temp;
                if (temp = $('wysiwyg-error')) temp.removeClass('b-combo__input_error');
            });
            
            var startH=0;
            var startY=0;
            var oldMouseMove=null;
            var oldMouseUp=null;
            var h_layer = null;
            
            edit.container.store('resizer', {
                'startH' : 0,
                'startY' : 0,
                'oldMouseMove' : null,
                'oldMouseUp' : null,
                'h_layer' : null
            });


            resizeEditor = function(e) {
                if ($('msgtext_error')) $('msgtext_error').style.display = 'none';
                
                if (!e) {
                    e = window.event;
                }
                
                if (e.preventDefault) {
                    e.preventDefault();
                }
                resizer = (e.target != null) ? e.target : e.srcElement;
                
                _rel = resizer.getParent('.fe-plain').getElement('.mooeditable-container')
                       .getElement('textarea.wysiwyg').retrieve('MooEditable');
//                _wrapper = _rel;
                _params = _rel.container.retrieve('resizer');
                
                h_layer =  new Element('div', {
                    'styles': {
                        'position': 'absolute',
                        'top': 0,
                        'left': -1,
                        'height' : document.getScrollSize().y,
                        'width' : document.getScrollSize().x,
//                        'background-color': '#ccc',
                        'z-index': 1000
                    }
                }).inject(document.body);
                _params.h_layer = h_layer;

                _params.startY = getMousePosition(e).y;
                _params.startH = _rel.container.getStyle('height').toInt() - _rel.toolbar.el.getSize().y;

                _params.oldMouseMove=document.onmousemove;
                _params.oldMouseUp=document.onmouseup;
                
                _rel.container.store('resizer', _params);

                document.onmousemove=resizeDrag.bind(_rel);
                document.onmouseup=resizeStop.bind(_rel);
                return false;
            };

            resizeDrag = function (e){
                if (e == null) {
                    e = window.event
                }
                _rel = this;
                
                if (e.button<=1){
                    _params = _rel.container.retrieve('resizer');
                    
                    curH = _params.startH+(getMousePosition(e).y-_params.startY);
                    //if (curH < 0) curH = 1;
                    if (curH < 70) curH = 70;  //минимальная высота текстового поля

                    try {
//                        _rel.setStyle('height', curH);
                        if (_rel.mode == 'iframe') {
                            _rel.container.getElement('iframe').setStyle('height', curH);
                        } else {
                            _rel.textarea.setStyle('height', curH);
                        }
                    } catch(er) {
                        alert([er, curH, _params.startH, e.clientY, _params.startY].join(' | '));
                    }
                    
                    return false;
                }
            };

            resizeStop = function (e) {
                _rel = this;
                _params = _rel.container.retrieve('resizer');
                _params.h_layer.dispose();
                document.onmouseup=_params.oldMouseUp;
                if (Browser.ie7) {
                    document.onmousemove=null;
                } else {
                    document.onmousemove=_params.oldMouseMove;
                }
            }
        }

    });
}

window.addEvent('domready', function(){
    initWysiwyg();
});