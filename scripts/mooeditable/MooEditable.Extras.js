/*
---

script: MooEditable.Extras.js

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

MooEditable.lang.set({
	blockFormatting: 'Block Formatting',
	codeFormatting: 'Code Formatting',
	paragraph: 'Paragraph',
	heading1: 'Heading 1',
	heading2: 'Heading 2',
	heading3: 'Heading 3',
	alignLeft: 'Align Left',
	alignRight: 'Align Right',
	alignCenter: 'Align Center',
	alignJustify: 'Align Justify',
	removeFormatting: 'Remove Formatting',
	insertHorizontalRule: 'Insert Horizontal Rule',
 codeSelect: 'Code'
});

MooEditable.Actions.extend({

	formatBlock: {
		title: MooEditable.lang.get('blockFormatting'),
		type: 'menu-custom-list',
		options: {
			list: [
				{text: MooEditable.lang.get('paragraph'), value: 'p'},
				{text: MooEditable.lang.get('heading1'), value: 'h4', style: 'font-size:24px; font-weight:bold;'},
				{text: MooEditable.lang.get('heading2'), value: 'h5', style: 'font-size:18px; font-weight:bold;'},
				{text: MooEditable.lang.get('heading3'), value: 'h6', style: 'font-size:14px; font-weight:bold;'}
			]
		},
		states: {
			tags: ['p', 'h4', 'h5', 'h6']
		},
		command: function(menulist, name){
			var argument = '<' + name + '>';
			this.focus();
			this.execute('formatBlock', false, argument);
		}
	},
	
	justifyleft:{
		title: MooEditable.lang.get('alignLeft'),
		states: {
			css: {'text-align': 'left'}
		}
	},
	
	justifyright:{
		title: MooEditable.lang.get('alignRight'),
		states: {
			css: {'text-align': 'right'}
		}
	},
	
	justifycenter:{
		title: MooEditable.lang.get('alignCenter'),
		states: {
			tags: ['center'],
			css: {'text-align': 'center'}
		}
	},
	
	justifyfull:{
		title: MooEditable.lang.get('alignJustify'),
		states: {
			css: {'text-align': 'justify'}
		}
	},
	
	removeformat: {
		title: MooEditable.lang.get('removeFormatting')
	},
	
	insertHorizontalRule: {
		title: MooEditable.lang.get('insertHorizontalRule'),
		states: {
			tags: ['hr']
		},
		command: function(){
			this.selection.insertContent('<hr>');
		}
	}

});


if(hljs) {

    langlist = [];
        langlist.push({
            text: '-- ' + MooEditable.lang.get('codeSelect') + ' -- ',
            value: '0'
        });
    tags = [];
    $each(hljs.LANGUAGES, function(l, nm) {
        langlist.push({
            text: nm.capitalize(),
            value: nm
        });
        tags.push(nm);
    });

    MooEditable.Actions.extend({
        codeBlock: {
            title: MooEditable.lang.get('codeFormatting'),
            type: 'menu-custom-list',
            options: {
                list: langlist
            },
            states: function(el, item) {
                str = this.selection.getText();
                
                if(str.length == 0) {
                    item.deactivate().disable();
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
                if(name == '0') {
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
                } else {

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
                            
                            nd = s.getNode().getPrevious();
                            s.selectNode(nd);
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