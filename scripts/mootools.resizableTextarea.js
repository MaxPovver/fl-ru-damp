/*
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
		handler: ".handler",
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
					"class": "handler"
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
				else	{
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
				else	{
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