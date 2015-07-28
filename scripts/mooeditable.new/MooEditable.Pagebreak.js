/*
---

name: MooEditable.Pagebreak

description: Extends MooEditable with pagebreak plugin

license: MIT-style license

authors:
- Ryan Mitchell

requires:
# - MooEditable
# - MooEditable.UI
# - MooEditable.Actions

provides: [MooEditable.Actions.pagebreak]

usage: |
  Add the following tags in your html
  <link rel="stylesheet" href="MooEditable.css">
  <link rel="stylesheet" href="MooEditable.Pagebreak.css">
  <script src="mootools.js"></script>
  <script src="MooEditable.js"></script>
  <script src="MooEditable.Pagebreak.js"></script>

  <script>
  window.addEvent('domready', function(){
    var mooeditable = $('textarea-1').mooEditable({
      actions: 'bold italic underline strikethrough | pagebreak | toggleview',
      externalCSS: '../../Assets/MooEditable/Editable.css'
    });
  });
  </script>

...
*/

//MooEditable.Actions.Settings.pagebreak = {
//	imageFile: '../../Assets/MooEditable/Other/pagebreak.gif'
//};
//
//MooEditable.Locale.define('pageBreak', 'Page break');
//
//MooEditable.Actions.pagebreak = {
//	title: MooEditable.Locale.get('pageBreak'),
//	command: function(){
//		this.selection.insertContent('<img class="mooeditable-visual-aid mooeditable-pagebreak">');
//	},
//	events: {
//		attach: function(editor){
//			if (Browser.ie){
//				// addListener instead of addEvent, because controlselect is a native event in IE
//				editor.doc.addListener('controlselect', function(e){
//					var el = e.target || e.srcElement;
//					if (el.tagName.toLowerCase() != 'img') return;
//					if (!document.id(el).hasClass('mooeditable-pagebreak')) return;
//					if (e.preventDefault){
//						e.preventDefault();
//					} else {
//						e.returnValue = false;
//					}
//				});
//			}
//		},
//		editorMouseDown: function(e, editor){
//			var el = e.target;
//			var isSmiley = (el.tagName.toLowerCase() == 'img') && $(el).hasClass('mooeditable-pagebreak');
//			Function.attempt(function(){
//				editor.doc.execCommand('enableObjectResizing', false, !isSmiley);
//			});
//		},
//		beforeToggleView: function(){ // code to run when switching from iframe to textarea
//			if (this.mode == 'iframe'){
//				var s = this.getContent().replace(/<img([^>]*)class="mooeditable-visual-aid mooeditable-pagebreak"([^>]*)>/gi, '<!-- page break -->');
//				this.setContent(s);
//			} else {
//				var s = this.textarea.get('value').replace(/<!-- page break -->/gi, '<img class="mooeditable-visual-aid mooeditable-pagebreak">');
//				this.textarea.set('value', s);
//			}
//		},
//		render: function(){
//			this.options.extraCSS = 'img.mooeditable-pagebreak { display:block; width:100%; height:16px; background: url('
//				+ MooEditable.Actions.Settings.pagebreak.imageFile + ') repeat-x; }'
//				+ this.options.extraCSS;
//		}
//	}
//};


MooEditable.Actions.Settings.pagebreak = {
    imageFile: '../../Assets/MooEditable/Other/pagebreak.gif'
};

MooEditable.Locale.define('pageBreak', 'Page break');

MooEditable.Actions.pagebreak = {
    title: MooEditable.Locale.get('pageBreak'),
    command: function(btn){
        this.focus();
//        pb_set = /<hr([^>]*)class="mooeditable-pagebreak"([^>]*)>/gi.test(this.doc.body.get('html'));
        pb_set = this.doc.body.getElement('hr.mooeditable-pagebreak');
        if (!pb_set) {
            this.execute('inserthorizontalrule');

            _hr = this.doc.body.getElement('hr');
            _hr.set('class', 'mooeditable-pagebreak');

            btn.activate();
        } else {
            s = this.selection;
            _hr = this.doc.body.getElement('hr.mooeditable-pagebreak');
            s.selectNode(_hr);
            
            if (Browser.opera) {
                _hr.dispose();
            } else {
                this.execute('delete');
            }
            btn.deactivate();
        }
        
//        s = this.selection;
//        r1 = s.getRange();
//        
//        s.selectNode(this.doc.body);
//        s.collapse(0);
//        
//        r2 = s.getRange();
//        r1.setEndAfter(r2.endContainer);
//        s.setRange(r1);
//        
//        t = s.getContent();
    },
    states: function(el, item) {
        str = this.doc.body.get('html');

        if(/<hr([^>]*)class="mooeditable-pagebreak"([^>]*)>/gi.test(str)) {
            item.activate();
        } else {
            item.deactivate();
        }
    },
    events: {
        attach: function(editor){
            if (Browser.ie){
                // addListener instead of addEvent, because controlselect is a native event in IE
                editor.doc.addListener('controlselect', function(e){
                    var el = e.target || e.srcElement;
                    if (el.tagName.toLowerCase() != 'img') return;
                    if (!document.id(el).hasClass('mooeditable-pagebreak')) return;
                    if (e.preventDefault){
                        e.preventDefault();
                    } else {
                        e.returnValue = false;
                    }
                });
            }
        },
        
        beforeToggleView: function(){ // code to run when switching from iframe to textarea
            if (this.mode == 'iframe'){
                s = this.doc.body.get('html').replace(/<hr([^>]*)class="mooeditable-pagebreak"([^>]*)>/gi, '<cut>');
                this.setContent(s);
            } else {
                s = this.textarea.get('value').replace(/<\!-- -W-EDITOR-CUT- -->/gi, '<hr class="mooeditable-pagebreak">');
                s = this.textarea.get('value').replace(/<cut>/gi, '<hr class="mooeditable-pagebreak">');
                s = s.replace(/<\/cut>/gi, '');
                this.textarea.set('value', s);
            }
        },
        
        saveContent: function() {
            if (this.mode == 'iframe' && /<cut>/gi.test(this.doc.body.get('html'))) {
                s = this.doc.body.get('html').replace(/<cut>/gi, '<hr class="mooeditable-pagebreak">');
                s = s.replace(/<\/cut>/gi, '');
                this.setContent(s);
            }
            if (this.mode == 'iframe' && /<\!-- -W-EDITOR-CUT- -->/gi.test(this.doc.body.get('html'))) {
                s = this.doc.body.get('html').replace(/<\!-- -W-EDITOR-CUT- -->/gi, '<hr class="mooeditable-pagebreak">');
                s = s.replace(/<\/cut>/gi, '');
                this.setContent(s);
            }
        },
        
        render: function(){
            this.options.extraCSS = 'img.mooeditable-pagebreak { display:block; width:100%; height:16px; background: url('
            + MooEditable.Actions.Settings.pagebreak.imageFile + ') repeat-x; }'
            + this.options.extraCSS;
        
            this.options.extraCSS = this.options.extraCSS 
                + '.pagebreak-item:focus, .pagebreak-item:active, .pagebreak-item.onActive {background-position: -2px -150px !important;}'; 
            this.options.extraCSS = this.options.extraCSS 
                + 'hr.mooeditable-pagebreak {border: 1px dashed #ccc;}'; 
        }
    }
};
