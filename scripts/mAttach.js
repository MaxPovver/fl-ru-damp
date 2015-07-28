
function mAttach(sourceObj, max) {

    this.objs  = [sourceObj];
    this.max   = max;
    this.count = 1;

    this.plus = function() {
		var button = document.createElement('INPUT');
		button.type = 'button';
		button.value = '+';
		button.onclick = function(self) { return function() { self.add(); } }(this);
		return button;
	}
	
	this.minus = function(num) {
		var button = document.createElement('INPUT');
		button.type = 'button';
		button.value = '-';
		button.onclick = function(self, num) { return function() { self.remove(num); } }(this, num);
		return button;
	}
    
    this.findClass = function(obj, className) {
        for (var i=0; i<obj.childNodes.length; i++) {
            if (obj.childNodes[i].className == className) return obj.childNodes[i];
            if (obj.childNodes[i].tagName) var s = this.findClass(obj.childNodes[i], className);
            if (s) return s;
        }
        return false;
    }
    
   	this.add = function() {
	if ($('flt-cnt'))$('flt-cnt').getParent().setStyle('height', 'auto');
		if (this.max && this.count >= this.max) return false;
		var num = this.objs.length-1;
        this.objs[num+1] = this.objs[num].parentNode.appendChild(this.objs[num].cloneNode(true));
        this.objs[num+1].innerHTML = this.objs[num].innerHTML;
        var tmp = this.findClass(this.objs[num], "addButton");
        tmp.replaceChild(this.minus(num), tmp.childNodes[0]);
        var tmp = this.findClass(this.objs[num+1], "addButton");
        tmp.replaceChild((this.max && this.count+1 >= this.max)? this.minus(num+1): this.plus(), tmp.childNodes[0]);
        this.count++;
	}

	this.remove = function(num) {
	if ($('flt-cnt'))$('flt-cnt').getParent().setStyle('height', 'auto');
		if (this.count == 1) return false;
		this.objs[num].parentNode.removeChild(this.objs[num]);
		if (this.max && this.count >= this.max) {
            var c = this.objs.length - 1;
            var tmp = this.findClass(this.objs[(num == c)? (--this.objs.length - 1): c], "addButton");
            tmp.replaceChild(this.plus(), tmp.childNodes[0]);
        }
        this.count--;
	}    
    
    var tmp = this.findClass(this.objs[0], "addButton");
    tmp.replaceChild(this.plus(), tmp.childNodes[0]);
    
};