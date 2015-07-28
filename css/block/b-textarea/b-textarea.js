/**
чтобы textarea не увеличивалась автоматически по мере набора текста надо добавить стиль b-taxtarea_noresize к тегу textarea


---
script: mootools.resizableTextarea.js
description: Resizable (as in webkit) textarea for MooTools
license: MIT-style license
authors:
- Sergii Kashcheiev
requires:
- core/1.2.4: Events
- core/1.2.4: Options
provides: [resizableTextarea]
...
*/
var resizableTextarea = new Class({
Version: "1.1",
Implements: [Options],
options: {
handler: ".b-textarea__handler",
modifiers: {x: true, y: true},
size: {x:[50, 500], y:[50, 500]},
onResizeClass: "resize",
onStart: function(current) {},
onEnd: function(current) {},
onResize: function(current) {}
},
initialize: function(holder, options) {
this.holder = holder;
this.setOptions(options);
this.holder.each(function(el, i) {
el.textarea = el.getElement("textarea");
el.textarea.addEvent("focus", 
function () {
    var grayText = this.getAttribute("graytext");
    if (grayText) {
        if (this.value == grayText) {
        	this.removeClass("b-textarea__textarea_color_a7");
        	this.value = '';
        }
    }
}
);
el.textarea.addEvent("blur", 
function () {
            var grayText = this.getAttribute("graytext");
            if (grayText) {
		        if (this.value == '') {
		        	this.addClass("b-textarea__textarea_color_a7");
		        	this.value = grayText;
		        }
		    }
		}
);
if (!el.textarea.getAttribute("graytext")) el.textarea.setAttribute("graytext", "");
if ((el.textarea.value == "")&&(el.textarea.getAttribute("graytext") != "")) {
	el.textarea.addClass("b-textarea__textarea_color_a7");
	el.textarea.value = el.textarea.getAttribute("graytext");
}
el.textarea.width = el.textarea.getWidth();
el.textarea.height = el.textarea.getHeight();
if(this.options.modifiers.x) {
if(this.options.size.x[0] > this.options.size.x[1]) {
this.options.size.x[0] = this.options.size.x[1];
}
if(el.textarea.width < this.options.size.x[0]) {
el.textarea.setStyle("width", this.options.size.x[0]);
el.textarea.width = this.options.size.x[0];
}
else if(el.textarea.width > this.options.size.x[1]) {
el.textarea.setStyle("width", this.options.size.x[1]);
el.textarea.width = this.options.size.x[1];
}
}
if(this.options.modifiers.y) {
if(this.options.size.y[0] > this.options.size.y[1]) {
this.options.size.y[0] = this.options.size.y[1];
}
if(el.textarea.height < this.options.size.y[0]) {
el.textarea.setStyle("height", this.options.size.y[0]);
el.textarea.height = this.options.size.y[0];
}
else if(el.textarea.height > this.options.size.y[1]) {
el.textarea.setStyle("height", this.options.size.y[1]);
el.textarea.height = this.options.size.y[1];
}
}
el.handler = el.getElement(this.options.handler);
if(el.handler == null) {
el.handler = new Element("span", {
"class": "b-textarea__handler"
});
el.handler.inject(el.textarea, "after");
}
el.textarea.setStyles({"resize": "none"});
el.handler.left = el.textarea.width - el.handler.getPosition(el).x;
el.handler.top = el.textarea.height - el.handler.getPosition(el).y;
el.handler.pressed = false;
el.handler.addEvent("mousedown", function(e) {
if (!(document.uniqueID && document.compatMode && !window.XMLHttpRequest)) {
document.onselectstart = function() { return false; }
document.onmousedown = function() { return false; }
}
if (Browser.Engine.trident) { el.handler.setCapture() }
else {
document.addEvent("mousemove", function(e) { el.handler.fireEvent("mousemove", e) });
document.addEvent("mouseup", function() { el.handler.fireEvent("mouseup") });
}
el.handler.pressed = true;
el.handler.x = e.page.x - el.handler.getPosition().x - el.handler.left;
el.handler.y = e.page.y - el.handler.getPosition().y - el.handler.top;
el.addClass(this.options.onResizeClass);
this.options.onStart(el);
}.bind(this));
el.handler.addEvent("mouseup", function() {
if (!(document.uniqueID && document.compatMode && !window.XMLHttpRequest)) {
document.onmousedown = null;
document.onselectstart = null;
}
if (Browser.Engine.trident) { el.handler.releaseCapture(); }
else {
document.removeEvent("mousemove", function(e) { el.handler.fireEvent("mousemove", e) });
document.removeEvent("mouseup", function() { el.handler.fireEvent("mousemove") });
}
el.handler.pressed = false;
el.removeClass(this.options.onResizeClass);
this.options.onEnd(el);
}.bind(this));
el.handler.addEvent("mousemove", function(e) {
if(el.handler.pressed) {
if(this.options.modifiers.x) {
el.textarea.newWidth = e.page.x - el.getPosition().x - el.handler.x;
if(el.textarea.newWidth < this.options.size.x[1] && el.textarea.newWidth > this.options.size.x[0])
el.textarea.newWidth = el.textarea.newWidth;
else if(el.textarea.newWidth <= this.options.size.x[0])
el.textarea.newWidth = this.options.size.x[0];
else el.textarea.newWidth = this.options.size.x[1];
el.textarea.setStyle("width", el.textarea.newWidth);
el.handler.setStyle("left", el.textarea.newWidth - el.handler.left - el.getStyle("border-left-width").toInt());
}
if(this.options.modifiers.y) {
	el.textarea.newHeight = e.page.y - el.getPosition().y - el.handler.y;
	if(el.textarea.newHeight<=el.textarea.minimumHeight) {
		this.options.size.y[0] = el.textarea.minimumHeight;
	}
	if(el.textarea.newHeight < this.options.size.y[1] && el.textarea.newHeight > this.options.size.y[0])
		el.textarea.newHeight = el.textarea.newHeight;
	else if(el.textarea.newHeight <= this.options.size.y[0])
		el.textarea.newHeight = this.options.size.y[0];
	else el.textarea.newHeight = this.options.size.y[1];
	el.textarea.setStyle("height", el.textarea.newHeight);
	el.handler.setStyle("top", el.textarea.newHeight - el.handler.top - el.getStyle("border-top-width").toInt());
}
this.options.onResize(el);
}
}.bind(this));
}.bind(this));
}
});



/*
---
description: DynamicTextarea

license: MIT-style

authors:
- Amadeus Demarzi (http://enmassellc.com/)

requires:
 core/1.3: [Core/Class, Core/Element, Core/Element.Event, Core/Element.Style, Core/Element.Dimensions]

provides: [DynamicTextarea]
...
*/

(function(){

// Prevent the plugin from overwriting existing variables
if (this.DynamicTextarea) return;

var DynamicTextarea = this.DynamicTextarea = new Class({

	Implements: [Options, Events],

	options: {
		value: '',
		minRows: 1,
		delay: true,
		lineHeight: null,
		offset: 0,
		padding: 0

		// AVAILABLE EVENTS
		// onCustomLineHeight: (function) - custom ways of determining lineHeight if necessary

		// onInit: (function)

		// onFocus: (function)
		// onBlur: (function)

		// onKeyPress: (function)
		// onResize: (function)

		// onEnable: (function)
		// onDisable: (function)

		// onClean: (function)
	},

	textarea: null,

	initialize: function(textarea,options) {
		this.textarea = textarea;
		if (!this.textarea) return;

		this.setOptions(options);

		this.parentEl = new Element('div',{
			styles:{
				padding:0,
				margin:0,
				border:0,
				height:'auto',
				width:'auto'
			}
		})
			.inject(this.textarea,'after')
			.adopt(this.textarea);

		// Prebind common methods
		['focus','delayCheck','blur','scrollFix','checkSize','clean','disable','enable','getLineHeight']
			.each(function(method){
				this[method] = this[method].bind(this);
			},this);

		// Firefox and Opera handle scroll heights differently than all other browsers
		if (window.Browser.firefox || window.Browser.opera) {
			this.options.offset =
				parseInt(this.textarea.getStyle('padding-top'),10) +
				parseInt(this.textarea.getStyle('padding-bottom'),10) +
				parseInt(this.textarea.getStyle('border-bottom-width'),10) +
				parseInt(this.textarea.getStyle('border-top-width'),10);
		} else {
			this.options.offset =
				parseInt(this.textarea.getStyle('border-bottom-width'),10) +
				parseInt(this.textarea.getStyle('border-top-width'),10);
			this.options.padding =
				parseInt(this.textarea.getStyle('padding-top'),10) +
				parseInt(this.textarea.getStyle('padding-bottom'),10);
		}

		// Disable browser resize handles, set appropriate styles
		this.textarea.set({
			'rows': 1,
			'styles': {
				'resize': 'none',
				'-moz-resize': 'none',
				'-webkit-resize': 'none',
				'position': 'relative',
				'display': 'block',
				'overflow': 'hidden',
				'height': 'auto'
			}
		});

		this.getLineHeight();
		this.fireEvent('customLineHeight');

		// Set the height of the textarea, based on content
		this.checkSize(true);
		this.textarea.addEvent('focus',this.focus);
		this.fireEvent('init',[textarea,options]);
        
        // для принудительной подгонки размеров поля ввода под контент надо вызвать $(textarea).fireEvent('checkSizeForced')
		this.textarea.addEvent('checkSizeForced', function(){
            this.checkSize(true);
        }.bind(this));
	},

	// This is the only crossbrowser method to determine ACTUAL lineHeight in a textarea (that I am aware of)
	getLineHeight: function(){
		var backupValue = this.textarea.value;
		this.textarea.value = 'M';
		this.options.lineHeight = this.textarea.getScrollSize().y - this.options.padding;
		this.textarea.value = backupValue;
		this.textarea.setStyle('height', this.options.lineHeight * this.options.minRows);
	},

	// Stops a small scroll jump on some browsers
	scrollFix: function(){
		this.textarea.scrollTo(0,0);
	},

	// Add interactive events, and fire focus event
	focus: function(){
		this.textarea.addEvents({
			'keydown': this.delayCheck,
			'keypress': this.delayCheck,
			'blur': this.blur
			//'scroll': this.scrollFix
		});
		return this.fireEvent('focus');
	},

	// Clean out extraneaous events, and fire blur event
	blur: function(){
		this.textarea.removeEvents({
			'keydown': this.delayCheck,
			'keypress': this.delayCheck,
			'blur': this.blur
			//'scroll': this.scrollFix
		});
		return this.fireEvent('blur');
	},

	// Delay checkSize because text hasn't been injected into the textarea yet
	delayCheck: function(){
		if (this.options.delay === true)
			this.options.delay = this.checkSize.delay(1);
	},

	// Determine if it needs to be resized or not, and resize if necessary
	checkSize: function(forced) {
		var oldValue = this.options.value,
			modifiedParent = false;

		this.options.value = this.textarea.value;
		this.options.delay = false;

		if (this.options.value === oldValue && forced!==true)
			return this.options.delay = true;

		if (!oldValue || this.options.value.length < oldValue.length || forced) {
			modifiedParent = true;
			this.parentEl.setStyle('height',this.parentEl.getSize().y);
			this.textarea.setStyle('height', this.options.minRows * this.options.lineHeight);
		}

		var tempHeight = this.textarea.getScrollSize().y,
			offsetHeight = this.textarea.offsetHeight,
			cssHeight = tempHeight - this.options.padding,
			scrollHeight = tempHeight + this.options.offset;
	    if (cssHeight < 100) {
             cssHeight = 100;
        }

		var clientHeight = document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;
                if ( cssHeight < clientHeight - 70 ) {
                    if ( (scrollHeight !== offsetHeight && cssHeight > this.options.minRows * this.options.lineHeight ) || cssHeight == this.options.minRows * this.options.lineHeight ){
                            this.textarea.setStyle('height',cssHeight);
                            var txt_handler = this.textarea.getParent('.b-textarea') && this.textarea.getParent('.b-textarea').getElement('.b-textarea__handler');
                            if(txt_handler != undefined) {
                                txt_handler.setStyle('top', cssHeight-16);
                            }
                            this.textarea.minimumHeight = cssHeight;
                            this.fireEvent('resize');
                    }//else {alert("scrollHeight = " + scrollHeight +", offsetHeight=" + offsetHeight + ", cssHeight = " + cssHeight + ", this.options.minRows * this.options.lineHeight = " + (this.options.minRows * this.options.lineHeight) );}
                    this.textarea.setStyle('overflow','hidden');
                } else if(cssHeight > clientHeight-70) {
                    cssHeight = clientHeight;
                    if (scrollHeight !== offsetHeight && cssHeight > this.options.minRows * this.options.lineHeight){
                        this.textarea.setStyle('height',cssHeight);
                        var txt_handler = this.textarea.getParent('.b-textarea') && this.textarea.getParent('.b-textarea').getElement('.b-textarea__handler');
                        if(txt_handler != undefined) {
                            txt_handler.setStyle('top', cssHeight-16);//alert(cssHeight-16);
                        }
                        this.textarea.minimumHeight = cssHeight;
                        this.fireEvent('resize');
                    }
                     this.textarea.setStyle('overflow','auto');
                } else {
                    this.textarea.setStyle('overflow','auto');
                }

		if(modifiedParent) this.parentEl.setStyle('height','auto');

		this.options.delay = true;
		if (forced !== true)
			return this.fireEvent('keyPress');
	},

	// Clean out this textarea's event handlers
	clean: function(){
		this.textarea.removeEvents({
			'focus': this.focus,
			'keydown': this.delayCheck,
			'keypress': this.delayCheck,
			'blur': this.blur
			//'scroll': this.scrollFix
		});
		return this.fireEvent('clean');
	},

	// Disable the textarea
	disable: function(){
		this.textarea.blur();
		this.clean();
		this.textarea.set(this.options.disabled,true);
		return this.fireEvent('disable');
	},

	// Enables the textarea
	enable: function(){
		this.textarea.addEvents({
			'focus': this.focus
			//'scroll': this.scrollFix
		});
		this.textarea.set(this.options.disabled,false);
		return this.fireEvent('enable');
	}
});

})();




function initBtextarea() {
var textarea = new resizableTextarea($$(".b-textarea"), {
	handler: ".b-textarea__handler",
	modifiers: {x: false, y: true},
	size: {y:[100, 30000]},
	onResize: function(current) {}
  })
  
// Default Textarea, takes no options
$$('.b-textarea__textarea:not(.b-textarea_noresize)').each(function(el) {
	new DynamicTextarea(el);
});

    
    $$('.b-textarea__textarea').addEvent('focus', makeTextareaCurrent);
    $$('.b-textarea__textarea').addEvent('blur', makeTextareaNotCurrent);
};


window.addEvent('domready', function() {
	
initBtextarea();

    


});


window.makeTextareaCurrent = function () {
    this.getParent('.b-textarea').addClass('b-textarea_current');
};
window.makeTextareaNotCurrent = function () {
    this.getParent('.b-textarea').removeClass('b-textarea_current');
};


