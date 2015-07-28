function initWysiwyg() {


    var cleanHtml = function(html, editor){

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
            html.replace(/<\\p>/gi, '');
        }

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
            this.selection.insertContent('<span id="INSERTION_MARKER">&nbsp;</span>');
            this.txtMarked = this.doc.body.get('html');
            this.doc.body.set('html', '');
            replaceMarkerWithPastedText.delay(5, this);
        }
        return this;
    };

    var replaceMarkerWithPastedText = function(){
        var txtPastet = this.doc.body.get('html');
        var txtPastetClean = cleanHtml(txtPastet, this);
        this.doc.body.set('html', this.txtMarked);
        var node = this.doc.body.getElementById('INSERTION_MARKER');
        this.selection.selectNode(node);
        this.selection.insertContent(txtPastetClean);
        return this;
    };

    $each($$('textarea.wysiwyg'), function(el) {
        t = new Element('div', {
            'class' : 'fe',
            'html' : '<div class="fe-in"><div class="fe-plain"></div></div>'
        });
        t.inject(el, 'after');
        el.inject(t.getElement('.fe-plain'));
        
        if(!$(el).retrieve('MooEditable')) {
            el.mooEditable({
                'html': '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">{BASEHREF}<style>{BASECSS} {EXTRACSS}</style>{EXTERNALCSS}<link href="/css/wysiwyg-txt.css" type="text/css" rel="stylesheet" /></head><body></body></html>',
                paragraphise: false,
                actions: 'toggleview | bold italic underline strikethrough | formatBlock | insertunorderedlist insertorderedlist | createlink unlink | urlimage urlvideo | pagebreak | codeBlock | undo redo'
            });
//            return;

            el.getParent().setStyle('position', 'relative');
            spn = new Element('div', {
                'class': 'fe-resize'
            });
            spn.inject(el, 'after');

            rel = el.retrieve('MooEditable').iframe;
            edit = el.retrieve('MooEditable');


//            alert(edit.options.paragraphise)
            edit.addEvent('editorPaste', cleanPaste.bind(edit));

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
            });
            
            var startH=0;
            var startY=0;
            var oldMouseMove=null;
            var oldMouseUp=null;
            var h_layer = null;

            spn.set('onmousedown', 'resizeEditor(event)');

            resizeEditor = function(e) {
                if (e == null) {
                    e = window.event
                }
                
                if (e.preventDefault) {
                    e.preventDefault();
                }
                resizer = (e.target != null) ? e.target : e.srcElement;


                h_layer =  new Element('div', {
                    'styles': {
                        'position': 'absolute',
                        'top': 0,
                        'left': -100,
                        'height' : document.getScrollSize().y,
                        'width' : document.getScrollSize().x
                    }
                }).inject(document.body);

                startY=e.clientY;
                startH=rel.getStyle('height').toInt();

                oldMouseMove=document.onmousemove;
                oldMouseUp=document.onmouseup;

                document.onmousemove=resizeDrag;
                document.onmouseup=resizeStop;
                return false;
            };

            resizeDrag = function (e){
                if (e == null) {
                    e = window.event
                }
                if (e.button<=1){
                    curH=(startH+(e.clientY-startY));
                    //                    if (curH<minH) curH=minH;
                    rel.setStyle('height', curH);
                    return false;
                }
            };

            resizeStop = function (e) {
                h_layer.dispose();
                document.onmousemove=oldMouseMove;
                document.onmouseup=oldMouseUp;
            }

        }

    });
}

window.addEvent('domready', function(){
    initWysiwyg();
});