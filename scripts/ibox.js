function IBox(elmPARENT, backend, fname, values) {

	this.ICO = {
		jpg:  '/images/ico_jpeg.gif',
		jpeg: '/images/ico_jpeg.gif',
		gif:  '/images/ico_gif.gif',
		png:  '/images/ico_png.gif',
		swf:  '/images/ico_swf.gif',
		zip:  '/images/ico_zip.gif',
		rar:  '/images/rar_32.png',
		xls:  '/images/ico_xls.gif',
		xml:  '/images/ico_unknown.gif',
		xsd:  '/images/ico_unknown.gif',
		xlsx: '/images/ico_xls.gif',
		doc:  '/images/ico_doc.gif',
		docx: '/images/ico_doc.gif',
		txt:  '/images/ico_txt.gif',
		rtf:  '/images/ico_rtf.gif',
		pdf:  '/images/ico_pdf.gif',
		psd:  '/images/ico_psd.gif',
		mp3:  '/images/ico_mp3.gif',
		flv:  '/images/flv_32.png',
		ogg:  '/images/ogg_32.png',
		'3gp': '/images/3gp_32.png',  
		wav:  '/images/wav_32.png',   
		bmp:  '/images/ico_bmp.gif',   
	}
	this.errorICO = '/images/ico_closed.gif';

	this.elmPARENT = elmPARENT;
	this.backend = backend;
	this.fname = fname;
	this.values = values;
	this.isIE = (navigator.userAgent.toLowerCase().indexOf('msie') != -1);
	this.iframeName = '';
	this.time = 0;
	this.fileID = '';

	this.elmFULL = document.createElement('div');
	this.elmMAIN = document.createElement('div');
	this.elmINFO = document.createElement('div');
	this.elmMAIN.className = 'ibox';
	this.elmINFO.className = 'ibox-info';
	this.elmINFO.innerHTML = '&nbsp;';
	this.elmFULL.appendChild(this.elmMAIN);
	this.elmFULL.appendChild(this.elmINFO);
	this.elmPARENT.appendChild(this.elmFULL);

	
	this.select = function() {
		this.fileID = '';
		this.elmMAIN.innerHTML = '';
		this.elmINFO.innerHTML = '';
		this.elmBLOCKER = document.createElement('div');
		this.elmBLOCKER.className = 'ibox-blocker';
		this.elmCONTENT = document.createElement('div');
		this.elmCONTENT.className = 'ibox-cnt-select';
		this.elmFILE = document.createElement('input');
		this.elmFILE.type = 'file';
		this.elmFILE.name = this.fname;
		this.elmFILE.className = 'ibox-file';
		this.elmFILE.size = 2;
		this.elmFILE.onmouseover = function(ibox) {
			return function() { ibox.elmFAKE.src = '/images/imgbox-add-hover.gif' }
		}(this);
		this.elmFILE.onmouseout = function(ibox) {
			return function() { ibox.elmFAKE.src = '/images/imgbox-add.gif' }
		}(this);
		this.elmFILE.onchange = function(ibox) {
			return function() {	
				if (ibox.elmFILE.value) {
					var p = ibox.elmFILE.value.lastIndexOf('.');					
					if (ibox.ICO[ibox.elmFILE.value.substr(p + 1).toLowerCase()]) {
						ibox.loading();
						ibox.onUpload();
					} else {
						ibox.error('Выбранный тип файла запрещен к загрузке.');
					}
				}
			}
		}(this);
		this.elmFILE.onmousedown = function(event) {
			(event || window.event).cancelBubble = true;
		};
		this.elmFAKE = document.createElement('img');
		this.elmFAKE.src = '/images/imgbox-add.gif';
		this.elmFAKE.className = 'ibox-fake';

    	this.elmFORM = document.createElement('form');
    	this.elmFORM = document.createElement('form');
		if (this.isIE) {
            var isIE7= false;
            try {
                isIE7 = (navigator.appVersion.indexOf("MSIE 7.")==-1) ? false : true;
            } catch(e) { }
            if(isIE7) { 
                // IE7
                this.elmFORM.setAttribute('encoding', 'multipart/form-data'); 
            } else {
                this.elmFORM.setAttribute('enctype', 'multipart/form-data');
            }

		} else {
    		this.elmFORM.setAttribute('enctype', 'multipart/form-data');
        }


		this.elmFORM.setAttribute('action', this.backend);
		this.elmFORM.setAttribute('method', 'POST');
		if (this.values) {
			for (name in this.values) {
				var input = document.createElement('input');
				input.setAttribute('name', name);
				input.setAttribute('type', 'hidden');
				input.value = this.values[name];
				this.elmFORM.appendChild(input);
			}
		}
		this.elmFORM.appendChild(this.elmFILE);
		this.elmCONTENT.appendChild(this.elmFORM);
		this.elmCONTENT.appendChild(this.elmFAKE);
		this.elmMAIN.appendChild(this.elmBLOCKER);
		this.elmMAIN.appendChild(this.elmCONTENT);
	}
	

	this.file = function(filename, displayname, time, fileID) {
		var ico = this.errorICO;
		var p = -1;
		if (!displayname) displayname = filename;
		displayname = displayname.replace(/^.*?([^\\/]+)$/, "$1");
		if ((p = displayname.lastIndexOf('.')) > 0) {
			var name = displayname.substr(0, p);
			var ext  = displayname.substr(p + 1);
			if (this.ICO[ext]) ico = this.ICO[ext];
		} else {
			var name = displayname;
			var ext  = '';
		}
		if (name.length > 22) {
			var disp = name.substr(0, 16) + '...' + name.substr(20, 3) + '.' + ext;
		} else {
			var disp = name + '.' + ext;
		}
		this.elmMAIN.innerHTML = '';
		this.elmINFO.innerHTML = '';
		var div = document.createElement('div');
		div.style.background = 'url(' + ico + ') bottom center no-repeat';
		var span = document.createElement('span');
		var a = document.createElement('a');
		a.href = filename;
		a.target = '_blank';
		a.innerHTML = disp;
		a.onmousedown = function(event) {
			(event || window.event).cancelBubble = true;
		};
		span.appendChild(a);
		this.elmCONTENT = document.createElement('div');
		this.elmCONTENT.appendChild(div);
		this.elmCONTENT.appendChild(span);
		this.elmCONTENT.className = 'ibox-cnt-file';
		this.elmMAIN.appendChild(this.elmCONTENT);
		this.fileID = fileID;
		if (time) this.info(time);
	}
	

    this.image = function(filename, image, time, fileID) {
		this.elmMAIN.innerHTML = '';
		this.elmINFO.innerHTML = '';
		this.elmCONTENT = document.createElement('div');
		this.elmCONTENT.innerHTML = '&nbsp;';
        this.elmCONTENT.className = 'ibox-cnt-image';
        this.elmCONTENT.style.background = 'url(' + image + ') center no-repeat';
        this.elmMAIN.appendChild(this.elmCONTENT);
		this.fileID = fileID;
        this.info(time);
    }

	
	this.loading = function() {
		var content = document.createElement('div');
		content.innerHTML = '&nbsp;';
		content.className = 'ibox-cnt-loading';
		content.style.background = 'url(/images/load_fav_btn_gray.gif) center no-repeat';
		this.elmFAKE.style.display = 'none';
		this.elmMAIN.appendChild(content);
	}
	

	this.info = function(time) {
		/*if (time) var d = new Date(time * 1000); else var d = new Date();
		var day = d.getDate();
		var month = d.getMonth() + 1;
		var year = d.getYear();
		var hour = d.getHours();
		var minutes = d.getMinutes();
		var date = (day>9? day: '0'+day) + '.' + (month>9? month: '0'+month) + '.' + (year>1900? year: year+1900);
		var time = (hour>9? hour: '0'+hour) + ':' + (minutes>9? minutes: '0'+minutes);*/
		var IMG = document.createElement('img');
		IMG.setAttribute('src', '/images/del_box.gif');
		var A1 = document.createElement('a');
		A1.setAttribute('href', '.');
		var A2 = document.createElement('a');
		A2.setAttribute('href', '.');
		A2.innerHTML = 'Удалить';
		A1.onclick = A2.onclick = function(ibox) {
			return function() {
				if (confirm('Удалить работу?'))	ibox.select();
				return false;
			}
		}(this);
		var TABLE = document.createElement('table');
		var TBODY = document.createElement('tbody')
		var TR = document.createElement('tr');
		TABLE.appendChild(TBODY);
		TBODY.appendChild(TR);
		var TD1 = document.createElement('td');
		var TD2 = document.createElement('td');
		var TD3 = document.createElement('td');
		TD1.innerHTML = time; //'Добавлено ' + date + ' в ' + time;
		TD1.className = 'date';
		A1.appendChild(IMG);
		//TD2.appendChild(A1);
		TD3.appendChild(A2);
		TR.appendChild(TD1);
		TR.appendChild(TD2);
		TR.appendChild(TD3);
		this.elmINFO.innerHTML = '';
		this.elmINFO.appendChild(TABLE);
	}
	
	
	this.error = function(message) {
		alert(message);
	}

	
	this.onUpload = function() {
	    var random = Math.round(Math.random() * 1000000);
		this.iframeName = 'upfile-' + random;
		if (this.isIE) {
            try {
    			var iframe = document.createElement('<iframe name='+this.iframeName+'></iframe>');
            } catch(e) {
    			var iframe = document.createElement('iframe');
    			iframe.name = this.iframeName;
    			iframe.setAttribute('name', this.iframeName);
            }
		} else {
			var iframe = document.createElement('iframe');
			iframe.name = this.iframeName;
			iframe.setAttribute('name', this.iframeName);
		}
		iframe.setAttribute('width', 1);
		iframe.setAttribute('height', 1);
		iframe.setAttribute('id', this.iframeName);
		iframe.style.position = 'absolute';
		iframe.style.visibility = 'hidden';
		document.getElementsByTagName("body")[0].appendChild(iframe);
		try { iframe.contentWindow.document.getElementsByTagName("body")[0].innerHTML = '&nbsp;'; } catch(e) { } //for opera 9.27
		
		var start_date = new Date();
        window['start_time_' + random] = start_date.getTime();
		
		this.interval = setInterval(function(ibox, iframe, random) {
			return function() {
				var html = '';
				try {
					html = iframe.contentWindow.document.getElementsByTagName("body")[0].innerHTML;
				} catch(e) {
					html = '';
				}
				
                var current_date = new Date();
                var current_time = current_date.getTime();
                var secs_elapsed = Math.round( (current_time - window['start_time_' + random]) / 1000 );
                
				if ( html.indexOf('-- IBox --') != -1 || html.indexOf('ERROR') != -1 || secs_elapsed >= time_limit ) {
				    clearInterval(ibox.interval);
    				if (html.indexOf('-- IBox --') != -1) {
    					ibox.onUploaded(html);
    				}
    				else {
    				    ibox.error('Недопустимый размер файла');
    				    ibox.select();
    				}
    				
    				document.getElementsByTagName("body")[0].removeChild(iframe);
				}
			}
		}(this, iframe, random), 500);
		this.elmFORM.setAttribute('target', this.iframeName);
		this.elmFORM.submit();
	}
	
	
	this.onUploaded = function(answer) {
		var r = [];
		if (r = answer.match(/<status>([^<]+)<\/status>/i, "$1")) var status = r[1]; else var status = 'error';
		if (r = answer.match(/<time>([^<]+)<\/time>/i, "$1")) var time = r[1]; else var time = 0;
		switch (status.toLowerCase()) {
			case 'error':
				if (r = answer.match(/<message>([^<]+)<\/message>/i, "$1")) var message = r[1]; else var message = '';
				this.error(message? message: 'Ошибка при загрузке файла.');
				this.select();
				break;
			case 'success':
				if (r = answer.match(/<filename>([^<]+)<\/filename>/i, "$1")) var filename = r[1]; else var filename = '';
				if (r = answer.match(/<displayname>([^<]+)<\/displayname>/i, "$1")) var displayname = r[1]; else var displayname = '';
				if (r = answer.match(/<preview>([^<]+)<\/preview>/i, "$1")) var preview = r[1]; else var preview = '';
				if (r = answer.match(/<fileid>([^<]+)<\/fileid>/i, "$1")) var fileID = r[1]; else var fileID = '';
				if (preview) this.image(filename, preview, time, fileID); else this.file(filename, displayname, time, fileID);
				break;
		}
	}

	
};



function IBoxes(backend, fname, values) {

	this.backend = backend;
	this.fname = fname;
	this.onUploaded = null;
	this.values = values;
	
	this.boxes = [];
	this.coor  = [];
	
	this.ddMouseX = -1;
	this.ddMouseY = -1;
	this.ddCloneBox = null;
	this.ddCloneWidth  = 0;
	this.ddCloneHeight = 0;
	this.ddSourceNumber = -1;
	this.ddCrossNumber  = -1;
	this.ddDestNumber   = -1;
	this.ddDisableSelection = false;
    this.ddClientWidth  = 0;
    this.ddClientHeight = 0;

	
	this.select = function(elmPARENT) {
		var c = this.boxes.length;
		this.boxes[c] = new IBox(elmPARENT, this.backend, this.fname, this.values);
		if (this.onUploaded) this.boxes[c].onUploaded = this.onUploaded;
		this.boxes[c].select();
		this.boxes[c].elmMAIN.onmousedown = this.fnBoxMousedown(this, c);
	}

	
	this.file = function(elmPARENT, filename, displayname, time, fileID) {
		var c = this.boxes.length;
		this.boxes[c] = new IBox(elmPARENT, this.backend, this.fname, this.values);
		if (this.onUploaded) this.boxes[c].onUploaded = this.onUploaded;
		this.boxes[c].file(filename, displayname, time, fileID);
		this.boxes[c].elmMAIN.onmousedown = this.fnBoxMousedown(this, c);
	}

	
	this.image = function(elmPARENT, filename, preview, time, fileID) {
		var c = this.boxes.length;
		this.boxes[c] = new IBox(elmPARENT, this.backend, this.fname, this.values);
		if (this.onUploaded) this.boxes[c].onUploaded = this.onUploaded;
		this.boxes[c].image(filename, preview, time, fileID);
		this.boxes[c].elmMAIN.onmousedown = this.fnBoxMousedown(this, c);
	}

	
    this.swap = function(num1, num2) {
        var p = this.boxes[num2].elmFULL.parentNode;
        this.boxes[num1].elmFULL.parentNode.appendChild(this.boxes[num2].elmFULL);
        p.appendChild(this.boxes[num1].elmFULL);
		p = this.boxes[num1];
		this.boxes[num1] = this.boxes[num2];
		this.boxes[num2] = p;
        this.boxes[num1].elmMAIN.onmousedown = this.fnBoxMousedown(this, num1);
        this.boxes[num2].elmMAIN.onmousedown = this.fnBoxMousedown(this, num2);
	}
	
	
	// Private
    // --------------------------------------------
	
	this.pos = function(obj) {
		if (obj.getBoundingClientRect) {
			var box = obj.getBoundingClientRect()
			var body = document.body
			var docElem = document.documentElement
			var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
			var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft
			var clientTop = docElem.clientTop || body.clientTop || 0
			var clientLeft = docElem.clientLeft || body.clientLeft || 0
			var top  = box.top +  scrollTop - clientTop
			var left = box.left + scrollLeft - clientLeft
			return { x: Math.round(left), y: Math.round(top) }
		} else {
			var r = { x: 0, y: 0 };
			while (obj) {
				r.x += obj.offsetLeft;
				r.y += obj.offsetTop;
				obj = obj.offsetParent;
			}
			return r;
		}
	}

	
	this.mouse = function(event) {
        var x = y = 0;
        if (document.attachEvent != null) {
            x = window.event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
            y = window.event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
        } else if (!document.attachEvent && document.addEventListener) {
            x = event.clientX + window.scrollX;
            y = event.clientY + window.scrollY;
        }
        return {x:x, y:y};
	}

	
	this.square = function(rect1, rect2) {
		var cross = {};
		cross.x1 = Math.max(rect1.x, rect2.x);
		cross.x2 = Math.min(rect1.x + rect1.width, rect2.x + rect2.width);
		if (cross.x1 > cross.x2) return 0;
		cross.y1 = Math.max(rect1.y, rect2.y);
		cross.y2 = Math.min(rect1.y + rect1.height, rect2.y + rect2.height);
		if (cross.y1 > cross.y2) return 0;
		return (cross.x2 - cross.x1) * (cross.y2 - cross.y1);
	}

	
	this.addListener = function(obj, type, fn) {
        if (obj.addEventListener) {
            obj.addEventListener(type, fn, false);
		} else if (obj.attachEvent) {
			obj.attachEvent("on" + type, fn);
		} else {
			obj["on" + type] = fn;
		}
	}

	
	this.removeListener = function(obj, type, fn) {
		if (obj.removeEventListener) {
			obj.removeEventListener(type, fn, false);
		} else if (obj.detachEvent) {
			obj.detachEvent("on" + type, obj[type + fn]);
		} else {
			obj["on" + type] = null;
		}
	}

	
	this.removeSelection = function(){
		if (window.getSelection) {
			window.getSelection().removeAllRanges();
		} else if (document.selection && document.selection.clear) {
			document.selection.clear();
		}
	}


	// Events
    // --------------------------------------------

	this.fnBoxMousedown = function(iBoxes, num) {
		return function(event) {
			var mouse = iBoxes.mouse(event || window.event);
			var n = num;
			var cap = -1;
			for (var i = 0, c = iBoxes.boxes.length; i < c; i++) {
				var r = iBoxes.pos(iBoxes.boxes[i].elmMAIN);
				iBoxes.coor[i] = { x: r.x, y: r.y, width: iBoxes.boxes[i].elmMAIN.offsetWidth, height: iBoxes.boxes[i].elmMAIN.offsetHeight }
				if (
					mouse.x >= iBoxes.coor[i].x && mouse.x <= iBoxes.coor[i].x + iBoxes.coor[i].width &&
					mouse.y >= iBoxes.coor[i].y && mouse.y <= iBoxes.coor[i].y + iBoxes.coor[i].height
				) {
					cap = i;
				}
			}
			if (!(
				mouse.x >= iBoxes.coor[n].x && mouse.x <= iBoxes.coor[n].x + iBoxes.coor[n].width &&
				mouse.y >= iBoxes.coor[n].y && mouse.y <= iBoxes.coor[n].y + iBoxes.coor[n].height
			)) {
				n = cap;
			}
			if (n >= 0) {
				iBoxes.ddDisableSelection = true;
                document.body.style.khtmlUserSelect = 'none';
                document.body.style.mozUserSelect = 'none';
                document.body.style.userSelect = 'none';
				iBoxes.ddClientWidth  = document.body.clientWidth;
                // opera hack
                iBoxes.ddClientHeight = (document.documentElement.clientHeight > document.body.clientHeight)? document.documentElement.clientHeight: document.body.clientHeight;
                iBoxes.ddMouseX = mouse.x;
				iBoxes.ddMouseY = mouse.y;
				iBoxes.ddSourceNumber = n;
				iBoxes.ddCloneBox = iBoxes.boxes[n].elmMAIN.cloneNode(true);
				iBoxes.ddCloneBox.style.position = 'absolute';
				iBoxes.ddCloneBox.style.left = iBoxes.coor[n].x + 'px';
				iBoxes.ddCloneBox.style.top  = iBoxes.coor[n].y + 'px';
				iBoxes.ddCloneBox.style.zIndex = '100';
				iBoxes.ddCloneBox.style.opacity = '0.6';
                // Hack for IE 7 and less versions
                var e = iBoxes.ddCloneBox.getElementsByTagName("*");
                for (var i = 0, len = e.length; i < len; i++) {
                    if (e[i].className == 'ibox-file' || e[i].className == 'ibox-blocker') {
                        e[i].style.visibility = 'hidden';
                    }
                }
                // End hack
				iBoxes.ddCloneBox.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=60)';
				iBoxes.ddCloneWidth = iBoxes.boxes[n].elmMAIN.offsetWidth;
				iBoxes.ddCloneHeight = iBoxes.boxes[n].elmMAIN.offsetHeight;
				document.body.appendChild(iBoxes.ddCloneBox);
			}
		}
	}
	

	this.addListener(document.body, "mousemove", function(iBoxes) {
		return function(event) {
			if (iBoxes.ddCloneBox) {
				iBoxes.removeSelection();
				var mouse = iBoxes.mouse(event || window.event);
				var nX = parseInt(iBoxes.ddCloneBox.style.left) + mouse.x - iBoxes.ddMouseX;
                var nY = parseInt(iBoxes.ddCloneBox.style.top) + mouse.y - iBoxes.ddMouseY;
                if (nX + iBoxes.ddCloneWidth > iBoxes.ddClientWidth) nX = parseInt(iBoxes.ddCloneBox.style.left);
                if (nY + iBoxes.ddCloneHeight > iBoxes.ddClientHeight) nY = parseInt(iBoxes.ddCloneBox.style.top);
                iBoxes.ddCloneBox.style.left = nX + 'px';
				iBoxes.ddCloneBox.style.top  = nY + 'px';
				iBoxes.ddMouseX = mouse.x;
				iBoxes.ddMouseY = mouse.y;
				var cloneCoor = { 
					x: nX, 
					y: nY, 
					width: iBoxes.ddCloneWidth, 
					height: iBoxes.ddCloneHeight
				};
				var crossNumber = -1;
				var crossSquare =  0;
				for (var i=0, len=iBoxes.boxes.length; i<len; i++) {
					var r = iBoxes.pos(iBoxes.boxes[i].elmMAIN);
					if (i != iBoxes.ddSourceNumber) {
						var c = iBoxes.square(cloneCoor, {x: r.x, y: r.y, width: iBoxes.boxes[i].elmMAIN.offsetWidth, height: iBoxes.boxes[i].elmMAIN.offsetHeight});
						if (c > crossSquare) {
							crossSquare = c;
							crossNumber = i;
						}
					}
				}
				if (crossNumber != iBoxes.ddCrossNumber) {
					if (iBoxes.ddCrossNumber >= 0) {
						iBoxes.boxes[iBoxes.ddCrossNumber].elmMAIN.className = "ibox";
					}
					if (crossNumber >= 0) {
						iBoxes.boxes[crossNumber].elmMAIN.className = "ibox-dd";
					}
					iBoxes.ddCrossNumber = crossNumber;
				}
			}
		}
	}(this));
	

	this.addListener(document.body, "mouseup", function(iBoxes) {
		return function(event) {
			if (iBoxes.ddCloneBox) {
				iBoxes.removeSelection();
				iBoxes.ddDisableSelection = false;
                document.body.style.khtmlUserSelect = '';
                document.body.style.mozUserSelect = '';
                document.body.style.userSelect = '';
				if (iBoxes.ddCrossNumber >= 0) {
					iBoxes.boxes[iBoxes.ddCrossNumber].elmMAIN.className = "ibox";
					iBoxes.swap(iBoxes.ddSourceNumber, iBoxes.ddCrossNumber);
				}
				iBoxes.ddCrossNumber = -1;
				iBoxes.ddSourceNumber = -1;
				document.body.removeChild(iBoxes.ddCloneBox);
				iBoxes.ddCloneBox = null;
			}
		}
	}(this));

	
};
