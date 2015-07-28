/**
 * @example
 * window.onload = function() {
 *    var KeyWord = new __key();  
 *    KeyWord.bind(document.getElementById('se'), words);   
 * }
 *
 * где document.getElementById('se') - элемент куда вводим ключевые значения
 * words -> массив слов для поиска
 */
String.prototype.trim = function() {
    a = this.replace(/^\s+/, '');
    return a.replace(/\s+$/, '');
};
    
function offsetPosition( element ) {
    var offsetLeft = 0, offsetTop = 0;
    do { 
        offsetLeft += element.offsetLeft
        offsetTop  += element.offsetTop;
    }
    while ( element = element.offsetParent );
    return [ offsetLeft, offsetTop];
}

function setCaretPosition(ctrl, pos) {
    var ua = navigator.userAgent.toLowerCase();
	if(ctrl.setSelectionRange && ua.indexOf("firefox") != -1) {
	    ctrl.setSelectionRange(pos-1,pos);
        var ev = document.createEvent ('KeyEvents');
        ev.initKeyEvent('keypress', true, true, window, false, false, false, false, 0, ctrl.value.charCodeAt(pos-1));
        ctrl.dispatchEvent(ev);
        ctrl.setSelectionRange(pos,pos);
        ctrl.focus();
	} else if(ctrl.setSelectionRange) {
	    ctrl.focus();
		ctrl.setSelectionRange(pos,pos);
	} else if (ctrl.createTextRange) {
	    ctrl.focus();
		var range = ctrl.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
}

function __key(gid) {
    if(gid == undefined) gid = 1;
    
    var KeyWord = new Object();
    
    KeyWord.bind = function(obj, words, setting) {
        this.search_el    = obj;
        this.iTimeoutId   = null;
        this.debug = 0;
    	this.keys  = new Array();
    	this.boxList = new Array();
    	this.defPosList = 0;
    	this.defWord    = "";
    	
    	
    	
        obj.onkeydown = function(e) {
            if (window.event) keycode = window.event.keyCode;
            else if (e) keycode = e.keyCode;
            if(keycode == 40) {
                KeyWord.defPosList += 1;     
                if(KeyWord.defPosList > KeyWord.maxList) KeyWord.defPosList = 0;
                
                if(KeyWord.defPosList != 0) { 
                    KeyWord.getActiveLink(KeyWord.defPosList-1, 0);   
                } else { 
                    KeyWord.getActiveLink(KeyWord.maxList, 0);  
                } 
                KeyWord.getActiveLink(KeyWord.defPosList, 1);
                return false;
            } else if(keycode == 38) {
                KeyWord.defPosList -= 1; 
                
                if(KeyWord.defPosList < 0) KeyWord.defPosList = KeyWord.maxList; 
               
                if(KeyWord.defPosList != KeyWord.maxList) {
                    KeyWord.getActiveLink(KeyWord.defPosList+1, 0);
                } else {
                    KeyWord.getActiveLink(0, 0);
                }
                KeyWord.getActiveLink(KeyWord.defPosList, 1);
                return false;  
            } else if(keycode == 13) {
                if(KeyWord.boxList[KeyWord.defPosList] != undefined) KeyWord.boxList[KeyWord.defPosList].onclick();
                //e.stopPropagation();
                
                return false;  
            }
        };
        
        if(setting == undefined) setting = {};
        obj.onkeyup = function(e) {
            if (window.event) keycode = window.event.keyCode;
            else if (e) keycode = e.keyCode;
                
            if(this.value == "") KeyWord.clearBox();
           
            if(keycode != 40 && keycode != 38 && keycode != 39 && keycode != 13 && keycode != 37) {
                KeyWord.parse_word(this);  
            } 
            
            if(setting.maxlen != undefined) {
                if(this.value.length > setting.maxlen) { 
                    alert('Вы превысили максимальное количество символов'); 
                    this.value = this.value.substr(0, setting.maxlen); 
                }    
            }
            return false;   
        }; 
        obj.onkeypress = function(e) {
            if (!e && window.event) e=window.event;
            if(e && e.keyCode == 13)
                return false;
        };
        
    	if(words == undefined) this.words = null;
    	else this.words = words;
    	
    	if(setting.minlen == undefined) this.minlen = 1;
    	else this.minlen = setting.minlen;
    	
    	if(setting.maxres == undefined) this.maxres = 10;
    	else this.maxres = setting.maxres;
    	
    	this.maxList    = this.maxres;
    	
    	if(setting.bodybox == undefined) this.bodybox = -1;
    	else this.bodybox = setting.bodybox;
    };
    
    KeyWord.getActiveLink = function(pos, act) {
        if(KeyWord.boxList[pos] != undefined)
        if(act == 1) {
            KeyWord.boxList[pos].onview();
            
            if(KeyWord.boxList[pos].style != undefined) {
                KeyWord.boxList[pos].style.color = "black"; 
                KeyWord.boxList[pos].style.fontWeight = "bold"; 
                KeyWord.boxList[pos].style.borderBottom = "1px black solid";   
            }        
        } else {
            if(KeyWord.boxList[pos].style != undefined) {
                KeyWord.boxList[pos].style.color = "#666";  
                KeyWord.boxList[pos].style.fontWeight = "normal"; 
                KeyWord.boxList[pos].style.borderBottom = "1px #666 solid";  
            }
        }
    };
    
    KeyWord.parse_word = function(obj) {
        if(obj.value.trim().length >= 1) {
    	   if(this.iTimeoutId != null) {
               clearTimeout(this.iTimeoutId);
    		   this.iTimeoutId = null;
    	   }
    	   this.iTimeoutId = setTimeout(function(){ KeyWord.parseKeyInput(obj);	}, 200);
        }      
    };
    
    KeyWord.getSearchIndex = function(obj) {
        var a = obj.value.split(',');
        if(a.length == 0) a[0] = obj.value;
        var search_index = a.length-1;
        
        if(this.keys.length > 0) {
            for(i=0;i<this.keys.length;i++) {
                if(a[i] != undefined) {
                    if(this.keys[i].trim() != a[i].trim() && search_index != i) {
                        search_index = i; 
                        break;
                    }
                }
            }
            return search_index;
        } 
        
        return search_index;
    };
    
    KeyWord.parseKeyInput = function(obj) {
        var a = obj.value.split(',');
        if(a.length == 0) a[0] = obj.value;
        var search_index = this.getSearchIndex(obj);
        
        this.search(a[search_index].trim(), search_index);
        this.keys = a;
    };
    
    KeyWord.search = function(str, ind) {
        if(ind == undefined) ind = -1;
        if(this.box != undefined) {
            this.clearBox();var vis = 0;
        }
        
        if(str.length < this.minlen) return false;
        
        var pattern = new RegExp("^"+str+".*", "i");
        var m  = new Array();
        for(i=0,j=1;  i < this.words.length; i++) {
            if(pattern.exec(this.words[i]) && this.words[i] != str) {
                m[j] = this.words[i].trim();
                j++;
            } 
        }
        
        if(document.getElementById("se_box"+gid) == undefined) {
            var box = document.createElement("div");
            this.box = box;
            this.box.className = 'b-input-hint__bottom';
            this.box.setAttribute("id", "se_box"+gid);
            this.search_el.onblur = function() {
                KeyWord.clearBox();     
            }
            
            this.box.onmouseout = function() {
               KeyWord.search_el.onblur = function() {
                   KeyWord.clearBox();     
               }
            }
            
            this.box.onmouseover = function() {
                KeyWord.search_el.onblur = null;
            }
            if(this.bodybox == -1) {
                document.body.appendChild(this.box);
            } else {
                $(this.bodybox).grab(this.box);
                //document.getElementById(this.bodybox).appendChild(this.box);
            }
        }
        // Заглушка для нулевого элемента
        this.boxList[0] = {innerHTML: str, lng:str.length, onview:function(){KeyWord.viewKey(str, ind);}, onclick:function(){KeyWord.clearBox()}};
        str = str.toLowerCase();
        for(var k=1;k<j;k++) {
            if(k>this.maxres) break;
            
            var hint   = document.createElement("div");
			hint.className = 'b-input-hint__item';
            var text = document.createTextNode(m[k]);
            var list = document.createElement("a");
            list.className = 'b-input-hint__link';
            list.setAttribute("id", "alist_"+k+"_"+gid);
            list.setAttribute("href", "javascript:void(0)");
            list.innerHTML = m[k].toLowerCase().replace(str, '<em class="b-input-hint__em">'+str+'</em>');
            list.lng     = text.length;
            list.onclick = function() {
                var h = this.id.split("_");
                KeyWord.addKey(m[h[1]], ind);
            }
            
            list.onview = function() {
                var h = this.id.split("_");
                KeyWord.viewKey(m[h[1]], ind);
            }
            
            this.boxList[k] = list;
			hint.appendChild(list);
            this.box.appendChild(hint);
            vis = 1;
        }
        
        this.maxList = k-1;
        
        if(vis == 1) {
            this.box.style.display = "block";
        } else {
            this.clearBox();
        }
    };
    
    KeyWord.clearBox = function() {
        if(KeyWord.box != undefined) {
            KeyWord.box.innerHTML     = "";
            KeyWord.box.style.display = "none";
        }
        KeyWord.defPosList = 0;
        KeyWord.defWord = "";
        KeyWord.boxList = new Array();
    };
    
    KeyWord.viewKey = function(str, pos) {
        for(i=0;i<this.keys.length;i++) this.keys[i] = this.keys[i].trim();
        if(pos >= 0) {
            this.keys[pos] = str.trim();
        } else {
            this.keys[this.keys.length-1] = str;
        }
        _str = this.keys.join(", ");
        
        try {
            _tmp = new Element('div', {'html': _str});
            _str = _tmp.childNodes[0].nodeValue.trim();
        } catch(e) {}
        
        this.search_el.value = _str; 
        if(navigator.userAgent.toLowerCase().indexOf("firefox") != -1) this.search_el.scrollTop = 5000;
        //if(this.keys.length > 1 && pos != this.keys.length-1)
        setCaretPosition(this.search_el, this.getPosCaret(str, pos));  
    };
    
    KeyWord.getPosCaret = function(str, pos) {
        var lng = 0;
        if(pos>0) {
            var deflng = pos*2;
            lng += deflng;
        }
        
        for(i=0;i<pos; i++) {
            lng += this.keys[i].trim().length;
        }
        
        lng += str.length;
       
        
        return lng;
    };
    
    KeyWord.addKey = function(str, pos) {
        this.viewKey(str, pos);
        this.clearBox();     
    };
    
    return KeyWord;
}
