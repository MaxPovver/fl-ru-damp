var comment = {

	
	opened: {},
	delID: 0,
	
		
	add: function() {
		var text = document.getElementById('comment-text').value.replace(/\s+/g, '');
		if (!text) {
			alert('Комментарий не может быть пустым.');
			return false;
		}
		if (this.opened) {
			document.getElementById('comment-text').disabled = true;
			document.getElementById('comment-reset').disabled = true;
			document.getElementById('comment-submit').disabled = true;
			xajax_CreateComment(this.opened.offerID, document.getElementById('comment-text').value, this.opened.replyID, this.opened.level);
		}
	},
	
		
	added: function(commentID, html) {
		var replyID = this.opened.replyID;
		var offerID = this.opened.offerID;
		this.reset();
		if (replyID) {
			document.getElementById('thread-'+replyID).innerHTML += ('<ul>'+html+'</ul>');
		} else if (document.getElementById('comments-'+offerID)) {
			document.getElementById('comments-'+offerID).innerHTML += html;
   document.getElementById('comments-'+offerID).style.display = '';
		} else {
			document.getElementById('offer-'+offerID).innerHTML += ('<ul class="thread-list" id="comments-'+offerID+'">'+html+'</ul>');
		}
		this.counter(offerID, 1);
		if (document.getElementById('to-'+offerID).innerHTML.search(/Развернуть/i) != -1) commentsTree(pid, offerID);
		//goAncor('c-comment-'+commentID);
	},
	
	
	counter: function(offerID, divCount) {
		if (document.getElementById('to-'+offerID)) {
			var text = document.getElementById('to-'+offerID).innerHTML;
			var msgCount = parseInt(document.getElementById('co-'+offerID).innerHTML) + divCount;
		} else {
			var text = 'Свернуть ветвь';
			var msgCount = divCount;
		}
		if (msgCount) {
			var b = document.createElement('b');
			var a = document.createElement('a');
			a.href = '.';
			a.id = 'to-' + offerID;
			a.onclick = function() { commentsTree(pid, offerID); return false; }
			a.innerHTML = text;
			var span = document.createElement('span');
			span.innerHTML = ' (<span id="co-'+offerID+'">' + msgCount + '</span>)';
			b.appendChild(a);
			b.appendChild(span);
			document.getElementById('commtree-'+offerID).innerHTML = '&nbsp;';
			document.getElementById('commtree-'+offerID).appendChild(b);
		} else {
			document.getElementById('commtree-'+offerID).innerHTML = '&nbsp;';
		}
	},
	
	
	change: function() {
		var text = document.getElementById('comment-text').value.replace(/\s+/g, '');
		if (!text) {
			alert('Комментарий не может быть пустым.');
			return false;
		}
		if (this.opened && this.opened.commentID) {
			document.getElementById('comment-text').disabled = true;
			document.getElementById('comment-reset').disabled = true;
			document.getElementById('comment-submit').disabled = true;
			xajax_ChangeComment(this.opened.commentID, document.getElementById('comment-text').value);
		}
	},
	
	
	changed: function(html, original, modified) {
		var commentID = this.opened.commentID;
		var restoreHTML = this.opened.restoreHTML;
		this.reset();
		document.getElementById('comment-change-'+commentID).innerHTML = restoreHTML;
		document.getElementById('comment-msg-'+commentID).innerHTML = html;
		document.getElementById('comment-msg-original-'+commentID).innerHTML = original;
		document.getElementById('comment-modified-'+commentID).innerHTML = modified;
		gotoAncor('comment-'+commentID);
	},
	
	
	del: function(commentID) {
		if (confirm('Удалить комментарий?')) {
			this.delID = commentID;
			xajax_DeleteComment(commentID);
		}
	},
	
		
	deleted: function(html) {
		document.getElementById('comment-'+this.delID).className += ' comment-deleted';
		var options = document.getElementById('comment-options-'+this.delID);
		options.innerHTML = html;
	},
	
		
	restore: function(commentID) {
		if (confirm('Восстановить комментарий?')) {
			this.delID = commentID;
			xajax_RestoreComment(commentID);
		}
	},
	
		
	restored: function(html) {
		var newClass = document.getElementById('comment-'+this.delID).className.replace(/\s*comment\-deleted\s*/, '');
		document.getElementById('comment-'+this.delID).className = newClass;
		var options = document.getElementById('comment-options-'+this.delID);
		options.innerHTML = html;
	},

	
	form: function(offerID, replyID, commentID, level) {
		var openedID = replyID? 'comment-'+replyID: 'comments-place-'+offerID;
		if ( this.opened && openedID == this.opened.openedID && replyID == this.opened.replyID ) {
			this.reset();
			return false;
		}
		this.reset();
		
		var title   = null;
		var onclick = null;
		
		if ( commentID ) {
		    title   = 'Отмена x';
		    onclick = function(comment) { return function() { comment.reset(); return false; } }(this);
		}
		else {
		    title = 'Стереть x';
		    onclick = function(comment) { return function() { $('comment-text').set('value', ''); return false; } }(this);
		}
		
		var reset = new Element( 'input#comment-reset', {
    		    type: 'reset',
    		    value: title,
    		    events: {
    		        click: onclick
    		    }
    		}
		);
		
		if ( commentID ) {
		    title   = 'Сохранить';
		    onclick = function(comment) { return function() { comment.change(); return false; } }(this);
		}
		else {
		    title = 'Отправить';
		    onclick = function(comment) { return function() { comment.add(); return false; } }(this);
		}
		
		var submit = new Element( 'input#comment-submit', {
    		    type: 'submit',
    		    value: title,
    		    events: {
    		        click: onclick
    		    }
    		}
		);
		
		var div = new Element( 'div' );
		
		var warn = new Element( 'span#comment-warn', {
    		    html: '&nbsp;'
    		}
		);
		
		div.grab( warn );
		div.grab( reset );
		div.grab( submit );
		
		if (commentID) {
			var comm = $('comment-change-'+commentID);
			var msg = $('comment-msg-original-'+commentID).get('html');
			var restoreHTML = comm.get('html');
			msg = msg.replace(/&amp;/g, '&');  
			msg = msg.replace(/&lt;/g, '<');  
			msg = msg.replace(/&gt;/g, '>');
			msg = msg.replace(/&nbsp; &nbsp;/g, '  ');
			msg = msg.replace(/&nbsp;/g, ' ');
			msg = msg.replace(/<a\s[^>]*title="?([^"\s]+)"?[^>]*>[^<]+<\/a>/gi, "$1");
			msg = msg.replace(/<\/?noindex>/ig, '');
			msg = msg.replace(/<br\s?\/?>/ig, "\n");
			comm.innerHTML = '';
		}
		
		var comment = new Element( 'textarea#comment-text', {
    		    name: 'comment-text',
    		    value: msg ? msg : ''
    		}
		);
		
		var box = new Element('div#comment-area', {
		    'class': 'thread-comment' + (replyID? ' thread-inner-comment': '')
		});

        var html_d = '';
        var class_d = '';
        if(contest_is_pro==1) {
        	class_d = 'b-layout__txt b-layout__txt_block b-layout__txt_color_6db335 b-layout__txt_padtop_5 b-layout__txt_padbot_5';
        	html_d = '<span class="b-icon b-icon_sbr_allow"></span>Вы можете оставлять свои контакты, так как являетесь владельцем аккаунта <span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_3"></span>';
        } else {
        	class_d = 'b-layout__txt b-layout__txt_block b-layout__txt_color_c10600 b-layout__txt_padtop_5 b-layout__txt_padbot_5';
        	html_d = '<span class="b-icon b-icon_sbr_forb"></span>Обмен контактами запрещен. Чтобы оставить свои контакты, <a class="b-layout__link" href="/'+(this.is_emp ? 'payed-emp' : 'payed')+'/">купите</a> '+this.pro_html;
        }

        var tizer = new Element('span', {
            'class': class_d, 
            'html' : html_d
        });
		
		box.grab( comment );
     //   box.grab(tizer);
		box.grab( div );
		
		$( openedID ).grab( box );
		
		this.opened = { openedID: openedID, offerID: offerID, replyID: replyID, commentID: (commentID? commentID: null), restoreHTML: (restoreHTML? restoreHTML: null), level: level };
	},

	
	reset: function() {
		if (this.opened.openedID) {
			$('comment-area').dispose();
			if (this.opened.commentID) $('comment-change-'+this.opened.commentID).set('html', this.opened.restoreHTML);
			this.opened = {};
		}
	}
	
	
};



function Boxes(parentObj, files, max) {
   
	this.parentObj = parentObj;
	this.max = max;
	this.count = 0;
	this.TABLE = null;
	this.files = files;
	this.fCounter = 0;
	this.path = '';
	this.WDCPERFIX;
		
	this.arrow = function(img, num1, num2) {
		var DIV = document.createElement('div');
		DIV.className = 'iboxes-swaper';
		var A = document.createElement('a');
		A.href = '.';
		A.onclick = function() { iboxes.swap(num1, num2); return false; }
		var IMG = document.createElement('img');
		IMG.src = img;
		A.appendChild(IMG);
		DIV.appendChild(A);
		return DIV;
	}

	this.add = function() {
		if (this.count >= this.max) {
			alert("Максимальное количество работы для загрузки - " + this.max);
			return false;
		}
		if (!this.count) {
			this.TABLE = document.createElement('TABLE');
			this.TABLE.setAttribute('cellpadding', '0');
			this.TABLE.setAttribute('cellspacing', '0');
			this.TABLE.setAttribute('border', '0');
			this.TABLE.className = 'iboxes-table';
			this.parentObj.appendChild(this.TABLE);
			this.TABLE = this.TABLE.appendChild(document.createElement('tbody'));
		}
			
		var TR = document.createElement('TR');
	
		var TD = document.createElement('td');
		TD.className = 'iboxes-box';
		TR.appendChild(TD);
		if (this.fCounter < this.files.length) {
			var path = this.WDCPERFIX + '/users/'+this.files[this.fCounter].dir+'/upload/';
			if (this.files[this.fCounter].preview) {
				iboxes.image(TD, path+this.files[this.fCounter].filename, path+this.files[this.fCounter].preview, this.files[this.fCounter].time, this.files[this.fCounter].fileID);
			} else {
				iboxes.file(TD, path+this.files[this.fCounter].filename, this.files[this.fCounter].displayname, this.files[this.fCounter].time, this.files[this.fCounter].fileID);
			}
			++this.fCounter;
		} else {
			iboxes.select(TD);
		}
		
		var TD = document.createElement('td');
		TD.className = 'iboxes-switcher';
		TD.appendChild(this.arrow('/images/to-right.png', this.count, this.count+1))
		TD.appendChild(this.arrow('/images/to-left.png', this.count, this.count+1));
		TR.appendChild(TD);
		
		var TD = document.createElement('td');
		TD.className = 'iboxes-box';
		TR.appendChild(TD);
		if (this.fCounter < this.files.length) {
			var path = this.WDCPERFIX + '/users/'+this.files[this.fCounter].dir+'/upload/';
			if (this.files[this.fCounter].preview) {
				iboxes.image(TD, path+this.files[this.fCounter].filename, path+this.files[this.fCounter].preview, this.files[this.fCounter].time, this.files[this.fCounter].fileID);
			} else {
				iboxes.file(TD, path+this.files[this.fCounter].filename, this.files[this.fCounter].displayname, this.files[this.fCounter].time, this.files[this.fCounter].fileID);
			}
			++this.fCounter;
		} else {
			iboxes.select(TD);
		}
		
		var TD = document.createElement('td');
		TD.className = 'iboxes-switcher';
		TD.appendChild(this.arrow('/images/to-right.png', this.count+1, this.count+2))
		TD.appendChild(this.arrow('/images/to-left.png', this.count+1, this.count+2));
		TR.appendChild(TD);
		
		var TD = document.createElement('td');
		TD.className = 'iboxes-box';
		TR.appendChild(TD);
		if (this.fCounter < this.files.length) {
			var path = this.WDCPERFIX + '/users/'+this.files[this.fCounter].dir+'/upload/';
			if (this.files[this.fCounter].preview) {
				iboxes.image(TD, path+this.files[this.fCounter].filename, path+this.files[this.fCounter].preview, this.files[this.fCounter].time, this.files[this.fCounter].fileID);
			} else {
				iboxes.file(TD, path+this.files[this.fCounter].filename, this.files[this.fCounter].displayname, this.files[this.fCounter].time, this.files[this.fCounter].fileID);
			}
			++this.fCounter;
		} else {
			iboxes.select(TD);
		}
			
		this.TABLE.appendChild(TR);
		this.count += 3;
			
		if (this.fCounter < this.files.length) this.add()
	}

}



var candidate = {
	
	add: function(offerID) {
		if (confirm('Добавить пользователя в кандидаты?')) {
			document.getElementById('select-'+offerID).style.display = 'none';
			document.getElementById('selected-'+offerID).style.display = '';
			document.getElementById('stat-candidates').innerHTML = parseInt(document.getElementById('stat-candidates').innerHTML) + 1;
			xajax_Candidate(offerID);
		}
	},
	
	added: function(uid, login) {
		candidates[candidates.length] = { uid: uid, login: login }
	},

	del: function(offerID) {
		if (confirm('Исключить пользователя из кандидатов?')) {
			document.getElementById('select-'+offerID).style.display = '';
			document.getElementById('selected-'+offerID).style.display = 'none';
			document.getElementById('stat-candidates').innerHTML = parseInt(document.getElementById('stat-candidates').innerHTML) - 1;
			xajax_Candidate(offerID);
		}
	},
	
	deleted: function(uid, login) {
		var t = [];
		for (var i=0; i<candidates.length; i++) {
			if (candidates[i].uid != uid) t[t.length] = candidates[i];
		}
		candidates = t;
	},
		
	block: function(userID) {
		xajax_UserBlocked(userID, pid);
	}
	
};

function setCookie(name, value, props) {
    props = props || {}
    var exp = props.expires;
    if ( typeof exp == "number" && exp ) {
        var d = new Date();
        d.setTime(d.getTime() + exp*1000);
        exp = props.expires = d;
    }
    if(exp && exp.toUTCString) { props.expires = exp.toUTCString() }
    value = encodeURIComponent(value);
    var updatedCookie = name + "=" + value;
    for ( var propName in props ) {
        updatedCookie += "; " + propName;
        var propValue = props[propName];
        if ( propValue !== true ) { updatedCookie += "=" + propValue }
    }
    document.cookie = updatedCookie;
}


function getCookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset)
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}


function commentsTree(projectID, commentID, dsp) {
    if(dsp == undefined) dsp = -1;
    
	var obj = document.getElementById('comments-'+commentID);
	var cookie = getCookie('contestCM');
	
	if(dsp == 1) {
        obj.style.display = 'block';               
    } else if(dsp == 0) {
        obj.style.display = 'none';
    }
	
    try {
        cookie = JSON.decode(cookie);
    } catch(e) { 
        // nothing
    }
    if ( !cookie ) {
        cookie = {};
    }
    if (cookie[projectID]) {
		var i = 0;
		var prj = []; 
		for (var k in cookie[projectID]) {
			if (parseInt(cookie[projectID][k]) > 0) prj[i++] = parseInt(cookie[projectID][k]);
		}
	} else {
		var prj = [];
	}
	if (obj.style.display && obj.style.display == 'none') {
		for (var i=0,c=prj.length; i<c; i++) {
			if (prj[i] == commentID) {
				prj[i] = 0;
				break;
			}
		}
		obj.style.display = '';
		document.getElementById('to-'+commentID).innerHTML = 'Свернуть ветвь';
	} else {
		var has = false;
		for (var i=0,c=prj.length; i<c; i++) {
			if (prj[i] == commentID) {
				var has = true;
				break;
			}
		}
		if (!has) prj[prj.length] = commentID;
		obj.style.display = 'none';
		document.getElementById('to-'+commentID).innerHTML = 'Развернуть ветвь';
	}
	cookie[projectID] = [];
	var j = 0;
	for (var i=0; i<prj.length; i++) {
		if (prj[i] > 0) cookie[projectID][j++] = prj[i];
	}
    var expire = new Date();
    expire.setFullYear(expire.getFullYear() + 1);
	setCookie('contestCM', JSON.encode(cookie), {'expires': expire});
	
}


function goAncor(name) {
	DOMReady.add(function() {
		var a = document.getElementsByTagName('A');
		for (var i = 0, len = a.length; i < len; i++) {
			if (a[i].name == name) {
				a[i].scrollIntoView(true);
				break;
			}
		}
	});
}
	
	
function sendOffer() {
	var files = '';
	for (var i=0,c=iboxes.boxes.length; i<c; i++) {
		if (iboxes.boxes[i].fileID) files += iboxes.boxes[i].fileID + '/';
	}
	document.getElementById('files').value = files;
	if (!document.getElementById('comment-box').value.replace(/[\s\r\n]+/, '') && !files) {
		alert('Чтобы разместить предложение, необходимо загрузить работы или написать свое предложение.');
		return false;
	}
	
	var iframes = $$("iframe[name^='upfile-']");
	if ( iframes && iframes.length > 0 ) {
	    alert('Пожалуйста, дождитесь загрузки файлов.');
	    return false;
	}
	
	$('offer_submit').set('disabled', true);
    $('offer_reset').set('disabled', true);
	
	return true;
}



function checkDate(year, month, day) {
	var daysOfMonth = [31, ((year % 4 == 0 && (year % 100 != 0 || year % 400 == 0))? 29: 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	var d = new Date();
	if (year < d.getFullYear() || year > (d.getFullYear() + 1)) return false;
	if (month < 1 || month > 12) return false;
	if (day < 1 || day > daysOfMonth[month - 1]) return false;
	return true;
}


function sendDates() {
	var de,ds = document.getElementById('ds').value;
	if (!(ds = ds.match(/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})$/)) || !checkDate(ds[3], ds[2], ds[1])) {
		alert('Неправильная дата окончания конкурса');
		return false;
	}
	de = document.getElementById('de').value;
	if (!(de = de.match(/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})$/)) || !checkDate(de[3], de[2], de[1])) {
		alert('Неправильная дата объявления победителя');
		return false;
	}
	var dds,dde,d=new Date();
	d = new Date(d.getFullYear(), d.getMonth(), d.getDate());
	dds = new Date(ds[3], ds[2]-1, ds[1]);
	if (dds <= d) {
		alert('Дата окончания конкурса не может находиться в прошлом');
		return false;
	}
	dde = new Date(de[3], de[2]-1, de[1]);
	if (dde <= dds) {
		alert('Дата определения победителя должна быть больше даты окончания конкурса');
		return false;
	}
	return true;
}


function setWinners(candidates) {

	if (!candidates) {
		alert('Для того, чтобы выбрать победителей Вам нужно определиться с кандидатами');
		return false;
	}
	
    var form = document.createElement('form');
    form.setAttribute('enctype', 'multipart/form-data');
	var div0 = document.createElement('div');
	var div1 = document.createElement('div');
	var div2 = document.createElement('div');
	var div3 = document.createElement('div');
	var h = document.createElement('h2');
	var p1 = document.createElement('p');
	var p2 = document.createElement('p');
	var input1 = document.createElement('input');
	var input2 = document.createElement('input');
	var input3 = document.createElement('input');
	var input4 = document.createElement('input');
	
	form.method = 'post';
	form.action = '/projects/index.php?pid='+pid+'&action=winners';
	form.id = 'winnerform';
	form.onsubmit = function() {
		if (document.getElementById('win-1').selectedIndex <= 0) {
			alert('Вам нужно выбрать победителя');
			return false;
		}
		if (document.getElementById('win-3').selectedIndex > 0 && document.getElementById('win-2').selectedIndex <= 0) {
			alert('Вы не указали второе место');
			return false;
		}
		$('winnerform').set('action', $('winnerform').get('action')+'&win-1='+$('win-1').get('value')+'&win-2='+$('win-2').get('value')+'&win-3='+$('win-3').get('value'));
		return true;
	}
	div0.id = 'opacity-layer';
	div1.id = 'set-winner';
	div1.className = 'set-winner';
	div2.className = 'set-winner-form';
	div3.className = 'set-winner-options';
	p1.className = 'sw-btns';
	p2.className = 'sw-important';
	h.innerHTML = 'Определите победителей';
	input1.type = 'submit';
	input1.value = 'Выбрать победителя';
	input2.type = 'button';
	input2.value = 'Отменить x';
	input3.name = 'u_token_key';
	input3.type = 'hidden';
	input3.value = _TOKEN_KEY;
	input4.name = 'action';
	input4.type = 'hidden';
	input4.value = 'winners';
	
	// багафикс по #0005557
	var pageSize = getPageSize();
	div0.style.height = document.body.scrollHeight+'px';
	div1.style.top    = (div1.style.top - (-pageSize.body)) + 'px';
	
	input2.onclick = function() {
		document.getElementById('opacity-layer').parentNode.removeChild(document.getElementById('opacity-layer'));
        document.getElementById('set-winner').parentNode.removeChild(document.getElementById('set-winner'));
	}
	p1.appendChild(input1);
	p1.innerHTML += '&nbsp;&nbsp;&nbsp;';
	p1.appendChild(input2);
	form.appendChild(input3);
	form.appendChild(input4);
	p2.innerHTML = '<strong>Внимание!</strong> После определения победителей конкурс<br />будет автоматически закрыт';

	
	for (var i=1; i<=3; i++) {
		var divx = document.createElement('div');
		var labelx = document.createElement('label');
		var selectx = document.createElement('select');
		labelx.innerHTML = i + '-' + ((i == 3)? 'е': 'ое') + ' место';
		selectx.id = 'win-' + i;
		selectx.setAttribute('name', 'win-'+i);
		selectx.options[0] = new Option('<выберите победителя>', 0);
		for (var j=0; j<candidates.length; j++) {
			selectx.options[j+1] = new Option(candidates[j].login, candidates[j].uid);
		}
		selectx.onchange = function() {
			var v = [
				document.getElementById('win-1').options[document.getElementById('win-1').selectedIndex].value,
				document.getElementById('win-2').options[document.getElementById('win-2').selectedIndex].value,
				document.getElementById('win-3').options[document.getElementById('win-3').selectedIndex].value
			];
			document.getElementById('win-1').options.length = 0;
			document.getElementById('win-2').options.length = 0;
			document.getElementById('win-3').options.length = 0;
			for (var i=1; i<=3; i++) {
				document.getElementById('win-'+i).options[0] = new Option('<выберите победителя>', 0);
				for (var j=0; j<candidates.length; j++) {
					var add = true;
					for (var c=0; c<3; c++) {
						if ((i-1 != c) && (candidates[j].uid == v[c])) {
							add = false;
							break;
						}
					}
					if (add) {
						document.getElementById('win-'+i).options[document.getElementById('win-'+i).options.length] = new Option(candidates[j].login, candidates[j].uid);
					}
				}
				for (var c=0; c<document.getElementById('win-'+i).options.length; c++) {
					if (document.getElementById('win-'+i).options[c].value == v[i-1]) {
						document.getElementById('win-'+i).selectedIndex = c;
						break;
					}
				}
			}
		}
		divx.appendChild(labelx);
		divx.appendChild(selectx);
		div3.appendChild(divx);
	}
	
	div2.appendChild(h);
	div2.appendChild(div3);
	div2.appendChild(p1);
	div2.appendChild(p2);
	form.appendChild(div2);
	div1.appendChild(form);
	
	$$('body').grab(div0);
	$$('body').grab(div1);

	$('win-1').onchange = winnerformOnChange;
	$('win-2').onchange = winnerformOnChange;
	$('win-2').disabled = true;
    $('win-3').onchange = winnerformOnChange;
    $('win-3').disabled = true;
    document.sourceList = new Array();
    for (var i = 0; i < $('win-1').options.length; i++) {
    	document.sourceList.push({value:$('win-1').options[i].value, text:$('win-1').options[i].text});
    }
    function winnerformOnChange () {
    	var sourceList = document.sourceList;
        var ids = ['win-1', 'win-2', 'win-3'];
        var id = this.id;
        var index = parseInt(id.replace(/[\D]/ig, ""));        
        var uid = parseInt($(id).value);
        if (uid) {
	        for (var i = 0; i < ids.length; i++) {
	            if (ids[i] != id) {
	                var ls = $(ids[i]).options;
	                for (var j = 0; j < ls.length; j++) {
	                    if (ls[j].value == uid) {
	                        ls[j] = null;
	                        break;
	                    }
	                }
	                if (ls.length > 1) {
	                    var k = parseInt(ids[i].replace(/[\D]/ig, ""));
	                    if (k - index == 1) {
	                        $(ids[i]).disabled = false;
	                    }
	                } else {
	                	$(ids[i]).disabled = true;
	                }
	            }
	        }
        } else {
            for (var i = 0; i < ids.length; i++) {
            	$(ids[i]).options.length = 0;
            	for (var j = 0; j < sourceList.length; j++) {
                    $(ids[i]).options[j] = new Option(sourceList[j].text, sourceList[j].value);                    
                }
                if (i == 0) {
                    $(ids[i]).disabled = false;
                } else {
                    $(ids[i]).disabled = true;
                }
            }
        }
    }
}


function resetOffer() {
	for (var i=0,c=iboxes.boxes.length; i<c; i++) iboxes.boxes[i].select();
	document.getElementById('comment-box').value = '';
	$('add-offer').style.display = 'none';
	var count_offers = 0;
	$('contest-comments').getElement('ul').getChildren('li').each(function(el) {
		if(el.getStyle('display')!='none') count_offers++;
	});
	if (count_offers == 0) {
			$('contest-comments').style.display = 'none';
	}	
}
	
	
function deleteOffer(id, is_my_offer) {
	if (confirm('Уверены, что хотите удалить предложение?')) {
		document.getElementById('offer-'+id).style.display = 'none';
		var count_offers = 0;
		$('contest-comments').getElement('ul').getChildren('li').each(function(el) {
			if(el.getStyle('display')!='none') count_offers++;
		});
		if(count_offers==0) {
			$('contest-comments').setStyle('display', 'none');
			$('contest-comments-treelink').setStyle('display', 'none');
			$('contest-answer-header').set('html', 'Нет предложений');
			$('contest-add-button').setStyle('display', 'block');
		} else {
			if(is_my_offer==1) $('contest-add-button').setStyle('display', 'block');
		}
		xajax_DelOffer(id);
	}
}
	
function removeOffer(prj_id, offer_id) {
	if (confirm('Уверены, что хотите удалить предложение?')) {
        $('offer-'+offer_id).addClass('comment-deleted');
        var infoDiv = $('offer-'+offer_id).getElement("div.moderator_info");
        var info = new Element("div", {"class":"suggest-comment-txt", style:"color:red", text:"Предложение удалено вами"});
        info.inject(infoDiv, "top");
        $('rr_lnk_'+offer_id).set('html', 'Восстановить');
        //$('rr_lnk_'+offer_id).set('onclick', 'restoreOffer('+prj_id+','+offer_id+'); return false;');
        var link = $('rr_lnk_'+offer_id);
        link. onclick = function () {
        	restoreOffer(prj_id, offer_id); 
        	return false;
        }
		xajax_RemoveOffer(prj_id, offer_id);
	}
}

function restoreOffer(prj_id, offer_id) {
	if (confirm('Уверены, что хотите восстановить предложение?')) {
        $('offer-'+offer_id).removeClass('comment-deleted');
        var info = $('offer-'+offer_id).getElement("div.moderator_info");
        var firstChild = info.getElement("div.suggest-comment-txt");
        info.removeChild(firstChild);
        $('rr_lnk_'+offer_id).set('html', 'Удалить');
        //$('rr_lnk_'+offer_id).set('onclick', 'removeOffer('+prj_id+','+offer_id+'); return false;');
        var link = $('rr_lnk_'+offer_id);
        link. onclick = function () {
        	removeOffer(prj_id, offer_id); 
        	return false;
        }
		xajax_RestoreOffer(prj_id, offer_id);
	}
}

function ShowHide(id) {
	if (document.getElementById(id).style.display == '') {
		document.getElementById(id).style.display = 'none';
	} else {
		document.getElementById(id).style.display = '';
	}
}


function checkMaxChars(textareaId, warningBoxId, maxChars) {
	if (document.getElementById(textareaId).value.length > maxChars) {
		document.getElementById(warningBoxId).innerHTML = 'Максимальная длина сообщения ' + maxChars + ' символов!';
		document.getElementById(warningBoxId).style.backgroundImage = 'url(/images/ico_error.gif)';
		document.getElementById(textareaId).value = document.getElementById(textareaId).value.substr(0, maxChars);
	} else {
		document.getElementById(warningBoxId).innerHTML = '';
		document.getElementById(warningBoxId).style.backgroundImage = '';
	}
}


var DOMReady = {
	
	isReady: false,
	timer: null,
	jobs: [],
		
	add: function(func) {
		if (this.isReady || this.isDOMReady()) return func();
		if (this.timer) {
			this.jobs.push(func);
		} else {
			if (window.addEventListener) {
				window.addEventListener('load', function(DOMReady) { DOMReady.isDOMReady() }(this), false);
			} else if (window.attachEvent) {
				window.attachEvent('onload', function(DOMReady) { DOMReady.isDOMReady() }(this));
			}
			this.jobs = [ func ];
			this.timer = setInterval(function(DOMReady) { DOMReady.isDOMReady() }(this), 13);
		}
	},
		
	isDOMReady: function() {
		if (this.isReady) return true;
		if (document && document.getElementsByTagName && document.getElementById && document.body) {
			if (this.timer) {
				clearInterval(this.timer);
				this.timer = null;
			}
			for (var i = 0; i < this.jobs.length; i++) this.jobs[i]();
			this.jobs = [];
			this.isReady = true;
			return true;
		}
		return false;
	}
	
};


function serialize (mixed_value) {
    var _getType = function (inp) {
        var type = typeof inp, match;
        var key;
        if (type == 'object' && !inp) {
            return 'null';
        }
        if (type == "object") {
            if (!inp.constructor) {
                return 'object';
            }
            var cons = inp.constructor.toString();
            match = cons.match(/(\w+)\(/);
            if (match) {
                cons = match[1].toLowerCase();
            }
            var types = ["boolean", "number", "string", "array"];
            for (key in types) {
                if (cons == types[key]) {
                    type = types[key];
                    break;
                }
            }
        }
        return type;
    };
    var type = _getType(mixed_value);
    var val, ktype = '';
    
    switch (type) {
        case "function": 
            val = ""; 
            break;
        case "boolean":
            val = "b:" + (mixed_value ? "1" : "0");
            break;
        case "number":
            val = (Math.round(mixed_value) == mixed_value ? "i" : "d") + ":" + mixed_value;
            break;
        case "string":
            val = "s:" + encodeURIComponent(mixed_value).replace(/%../g, 'x').length + ":\"" + mixed_value + "\"";
            break;
        case "array":
        case "object":
            val = "a";
            /*
            if (type == "object") {
                var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
                if (objname == undefined) {
                    return;
                }
                objname[1] = this.serialize(objname[1]);
                val = "O" + objname[1].substring(1, objname[1].length - 1);
            }
            */
            var count = 0;
            var vals = "";
            var okey;
            var key;
            for (key in mixed_value) {
                ktype = _getType(mixed_value[key]);
                if (ktype == "function") { 
                    continue; 
                }
				if (type == "array" && ktype != "number") {
					continue;
				}
                
                okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
				vals += this.serialize(okey) +
                        this.serialize(mixed_value[key]);
                count++;
            }
            val += ":" + count + ":{" + vals + "}";
            break;
        case "undefined": // Fall-through
        default: // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
            val = "N";
            break;
    }
    if (type != "object" && type != "array") {
        val += ";";
    }
    return val;
}


function unserialize (data) {
    var error = function (type, msg, filename, line){throw new this.window[type](msg, filename, line);};
    var read_until = function (data, offset, stopchr){
        var buf = [];
        var chr = data.slice(offset, offset + 1);
        var i = 2;
        while (chr != stopchr) {
            if ((i+offset) > data.length) {
                error('Error', 'Invalid');
            }
            buf.push(chr);
            chr = data.slice(offset + (i - 1),offset + i);
            i += 1;
        }
        return [buf.length, buf.join('')];
    };
    var read_chrs = function (data, offset, length){
        var buf;
 
        buf = [];
        for (var i = 0;i < length;i++){
            var chr = data.slice(offset + (i - 1),offset + i);
            buf.push(chr);
        }
        return [buf.length, buf.join('')];
    };
    var _unserialize = function (data, offset){
        var readdata;
        var readData;
        var chrs = 0;
        var ccount;
        var stringlength;
        var keyandchrs;
        var keys;
 
        if (!offset) {offset = 0;}
        var dtype = (data.slice(offset, offset + 1)).toLowerCase();
 
        var dataoffset = offset + 2;
        var typeconvert = new Function('x', 'return x');
 
        switch (dtype){
            case 'i':
                typeconvert = function (x) {return parseInt(x, 10);};
                readData = read_until(data, dataoffset, ';');
                chrs = readData[0];
                readdata = readData[1];
                dataoffset += chrs + 1;
            break;
            case 'b':
                typeconvert = function (x) {return parseInt(x, 10) !== 0;};
                readData = read_until(data, dataoffset, ';');
                chrs = readData[0];
                readdata = readData[1];
                dataoffset += chrs + 1;
            break;
            case 'd':
                typeconvert = function (x) {return parseFloat(x);};
                readData = read_until(data, dataoffset, ';');
                chrs = readData[0];
                readdata = readData[1];
                dataoffset += chrs + 1;
            break;
            case 'n':
                readdata = null;
            break;
            case 's':
                ccount = read_until(data, dataoffset, ':');
                chrs = ccount[0];
                stringlength = ccount[1];
                dataoffset += chrs + 2;
 
                readData = read_chrs(data, dataoffset+1, parseInt(stringlength, 10));
                chrs = readData[0];
                readdata = readData[1];
                dataoffset += chrs + 2;
                if (chrs != parseInt(stringlength, 10) && chrs != readdata.length){
                    error('SyntaxError', 'String length mismatch');
                }
            break;
            case 'a':
                readdata = {};
 
                keyandchrs = read_until(data, dataoffset, ':');
                chrs = keyandchrs[0];
                keys = keyandchrs[1];
                dataoffset += chrs + 2;
 
                for (var i = 0; i < parseInt(keys, 10); i++){
                    var kprops = _unserialize(data, dataoffset);
                    var kchrs = kprops[1];
                    var key = kprops[2];
                    dataoffset += kchrs;
 
                    var vprops = _unserialize(data, dataoffset);
                    var vchrs = vprops[1];
                    var value = vprops[2];
                    dataoffset += vchrs;
 
                    readdata[key] = value;
                }
 
                dataoffset += 1;
            break;
            default:
                error('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype);
            break;
        }
        return [dtype, dataoffset - offset, typeconvert(readdata)];
    };
    
    return _unserialize((data+''), 0)[2];
}

var getPageSize = function() {
    var xScroll, yScroll;
    if (window.scrollMaxX || window.scrollMaxY) {  
        xScroll = window.innerWidth  + window.scrollMaxX;
        yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight >= document.body.offsetHeight){ // all but Explorer Mac
        xScroll = document.body.scrollWidth;
        yScroll = document.body.scrollHeight;
    }
    var windowWidth, windowHeight;
    if (self.innerHeight) { // all except Explorer
        windowWidth = self.innerWidth;
        windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
        windowWidth = document.documentElement.clientWidth;
        windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
        windowWidth = document.body.clientWidth;
        windowHeight = document.body.clientHeight;
    }
    pageHeight = Math.max(windowHeight, yScroll || 0);
    pageWidth = Math.max(windowWidth, xScroll || 0);
    bodyTop   = self.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
    return { page: [pageWidth, pageHeight], window: [windowWidth, windowHeight], body: [bodyTop] };
};

window.addEvent('domready', function() {
    if (typeof iboxes_pid != "undefined") {
        iboxes = new IBoxes('/projects/upload.php', 'ps_attach', {action: 'add_pic', pid: iboxes_pid, u_token_key: _TOKEN_KEY});
    }
    
    if (typeof boxes_params != undefined && typeof files != "undefined") {
        boxes = new Boxes(document.getElementById('ca-iboxes'), files, 15);
        boxes.path = boxes_params.path;
        boxes.WDCPERFIX = boxes_params.WDCPERFIX;
        if (boxes_params.isAdd) {
            boxes.add();
        }
    }
    
    if (typeof comment_params != "undefined") {
        comment.is_emp = comment_params.is_emp;
        comment.pro_html = comment_params.pro_html;
    }
});