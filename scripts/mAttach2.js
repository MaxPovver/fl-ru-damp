function mAttach2(sourceObj, max, params) {
    this.objs  = [sourceObj];
    this.max   = max;
    this.count = 1;
    this.params={};
    /**
     * p - css селектор элемента выполняющего роль кнопки +
     * m - css селектор элемента выполняющего роль кнопки -	
     * Добавил обработку двух параметров:
     * onRemove Function (block)- вызывается после того, как поле добавлено. Не вызывается, если кол-во полей уже равно максимально допустимому. Передает в функцию ссылку на block (контейнер в котором находятся инпут, кнопки добавитьт / удалить)
     * onAdd    Function (input)- вызывается до того, как поле удалено.  Не вызывается, если кол-во полей уже равно минимально допустимому.  Передает ссылку на инпут input.
     * */
    if(params!=null)
        this.params=params;

    this.plus = function() {
        if(this.params['bt']=='a') {
    		var button = document.createElement('A');
            button.href= 'javascript: void(0);';
        } else {
    		var button = document.createElement('INPUT');
    		button.type = 'button';
        }
        if(!this.params['nv'])
            button.innerHTML = '+';
        if(this.params['p']!=undefined)
            button.className = this.params['p'];
  		button.onclick = function(self) { return function() { self.add(); } }(this);
		return button;
	}
	
	this.minus = function(num) {
        if(this.params['bt']=='a') {
    		var button = document.createElement('A');
		    button.href = 'javascript: void(0);';
        } else {
    		var button = document.createElement('INPUT');
		    button.type = 'button';
        }
        if(!this.params['nv'])
            button.value = '-';
        if(this.params['m']!=undefined)
            button.className = this.params['m'];
  		button.onclick = function(self, num) { return function() { self.remove(num); } }(this, num);
		return button;
	}
    
   	this.add = function() {
        if(this.flt && this.flt.parentNode) {
            if (!this.flt.parentNode.style) {
                this.flt.parentNode.style = {};
            }
            this.flt.parentNode.style.height = 'auto';
        }
		if (this.max!=null && this.count >= this.max) return false;
		var num = this.objs.length-1;
        this.objs[num+1] = this.objs[num].parentNode.appendChild(this.objs[num].cloneNode(true));
        this.objs[num+1].innerHTML=this.objs[num+1].innerHTML;
        this.objs[num].replaceChild(this.minus(num), this.objs[num].lastChild);
        this.objs[num+1].replaceChild((this.max!=null && this.count+1 >= this.max)? this.minus(num+1): this.plus(), this.objs[num+1].lastChild);
        this.count++;
        var ls = this.objs[num+1].getElementsByTagName("input");
        for (var i = 0; i < ls.length; i++) {
            if (ls[i].type == "file") {
                if (this.params['onAdd'] instanceof Function) {
                    this.params['onAdd'](ls[i]);
                }
            }
        }
    }

	this.remove = function(num) {
        if(this.flt)
            this.flt.parentNode.style.height = 'auto';
		if (this.count == 1) return false;
        if (this.params['onRemove'] instanceof Function) {
            this.params['onRemove'](this.objs[num]);
        }
		this.objs[num].parentNode.removeChild(this.objs[num]);
		if (this.max!=null && this.count >= this.max) {
            var c = this.objs.length - 1;
            var tmp = this.objs[(num == c)? (--this.objs.length - 1): c];
            tmp.replaceChild(this.plus(), tmp.lastChild);
        }
        this.count--;
    }

    this.init = function() {
        var fcls = this.params['f'] ? this.params['f'] : '.flt-cnt';
        this.flt = $(this.objs[0]);
        if ( this.flt.getParent ) {
            this.flt = this.flt.getParent(fcls);
        }
	    if(this.max>this.count) {
	       this.objs[0].appendChild(this.plus());
	    }
	    if(this.max===0)this.objs[0].style.display='none';
    }

    this.incMax = function(cnt) {
        if(this.max==null) return;
        if(this.flt)
            this.flt.parentNode.setStyle('height', 'auto');
        if(cnt==null || cnt<=0) cnt = 1;
        if(this.max!==0) {
            var tmp = this.objs[this.max - 1];
            if(this.max==1)
                tmp.appendChild(this.plus());
            else if(this.objs.length == this.max)
                tmp.replaceChild(this.plus(), tmp.lastChild);
        }
        else
            this.objs[0].style.display='block';
        this.max += cnt;
    }


    this.init();
    
    
};
