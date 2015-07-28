function Formasync() {

	this.interval = null;
	this.sended = false;

	this.send = function(form, noflag) {
		if (this.sended) {
			return false;
		} else {
			this.sended = true;
			this.waiting();
		}
		var name = 'formasync-' + Math.round(Math.random() * 1000000);
		if ((navigator.userAgent.toLowerCase().indexOf('msie') != -1)) {
			var iframe = document.createElement('<iframe name='+name+'></iframe>');
		} else {
			var iframe = document.createElement('iframe');
			iframe.name = name;
			iframe.setAttribute('name', name);
		}
		iframe.setAttribute('width', 700);
		iframe.setAttribute('height', 200);
		iframe.setAttribute('id', name);
		iframe.style.position = 'absolute';
		//iframe.style.visibility = 'hidden';
		document.getElementsByTagName("body")[0].appendChild(iframe);
		try {iframe.contentWindow.document.getElementsByTagName("body")[0].innerHTML = '&nbsp';} catch(e) { } //opera 9.27
		var self = this;
		this.interval = setInterval(function() {
			var html = '';
			try {
				html = iframe.contentWindow.document.getElementsByTagName("body")[0].innerHTML;
			} catch(e) {
				html = '';
			}
			if (html.indexOf('Success:') != -1) {
				clearInterval(self.interval);
				self.success(html.substr(8));
				document.getElementsByTagName("body")[0].removeChild(iframe);
				self.sended = false;
			} else if (html.indexOf('Error:') != -1) {
				clearInterval(self.interval);
				self.error(html.substr(6));
				document.getElementsByTagName("body")[0].removeChild(iframe);
				self.sended = false;
			}
		}, 500);
		if (!noflag) {
			var h = document.createElement('input');
			h.setAttribute('type', 'hidden');
			h.setAttribute('name', 'formasync');
			h.setAttribute('value', '1');
			form.appendChild(h);
		}
		form.setAttribute('target', name);
		form.submit();
		return false;
	}

}

function CommentForm() {

	this.id = '';
	this.op = '';

	this.open = function(id) {
		new Element('img', {
			'src': '/images/load-line.gif'
		}).inject('edit-'+id, 'bottom');
		$('edit-'+id).setStyle('display', 'block');
		var p = id.split('-');
		xajax_EditComment(p[0], p[1]);
		return false;
	}

	this.close = function(id) {
		$('edit-'+id).getElements('form').destroy();
		$('edit-'+id).setStyle('display', 'none');
		return false;
	}

	this.toggle = function(id) {
		if ($('edit-'+id).getStyle('display') == 'none') {
			this.open(id);
		} else {
			this.close(id);
		}
		return false;
	}

	this.save = function(id) {
		if (this.id) {
			return false;
		}
		this.id = id;
		this.op = 'save';
		this.send($('form-'+id));
		return false;
	}

	this.del = function(id) {
		if (confirm('Уверены?')) {
			$('dels').set('value', $('item-'+id).getElement('input[type=checkbox]').value).set('name', 'dels');
			this.id = id;
			this.op = 'del';
			this.send($('dels-frm'));
		}
		return false;
	}

	this.restore = function(id) {
		if (confirm('Уверены?')) {
			$('dels').set('value', $('item-'+id).getElement('input[type=checkbox]').value).set('name', 'restore');
			this.id = id;
			this.op = 'restore';
			this.send($('dels-frm'));
		}
		return false;
	}

	this.dels = function() {
		var val = '';
		$$('.m-co-checkbox').each(function(item) {
			if (item.checked) {
				val += ';'+item.value;
			}
		});
		if (val) {
			if (confirm('Уверены?')) {
				$('dels').set('value', val.substr(1)).set('name', 'dels');
				$('dels-frm').submit();
			}
		} else {
			alert('Вам нужно выбрать хотя бы один комментарий');
		}
		return false;
	}

	this.waiting = function() {
		if (this.op == 'save') {
			$('edit-'+this.id).getElements('input[type=button]').setProperty('disabled', 'true');
			$('edit-'+this.id).getElements('a').setProperty('disabled', 'true');
		} else if (this.op == 'del' || this.op == 'restore') {
			new Element('img', {
				'src': '/images/load-line.gif'
			}).inject('edit-'+this.id, 'bottom');
			$('edit-'+this.id).setStyle('display', 'block');
		}
	}

	this.success = function(html) {
		if (this.op == 'save') {
			this.close(this.id);
			var r = new RegExp("<eval>([\\s\\S]+)</eval>", "m");
			var found = r.exec(html);
			$('comment-'+this.id).set('html', html.replace(r, ""));
			if (found) {
				eval(found[1]);
			}
		} else if (this.op == 'del') {
			$('comment-'+this.id).addClass('m-co-t-delete');
			$('delbtn-'+this.id).addClass('m-co-o-edit');
			$('delbtn-'+this.id)
				.getElement('a')
				.removeClass('lnk-dot-red')
				.addClass('lnk-dot-666')
				.set('text', 'Восстановить');
			$('item-'+this.id).getElement('.m-co-checkbox').setProperty('disabled', 'true');
			$('delbtn-'+this.id).getElement('a').onclick = function(id) {
				return function() {
					return comm.restore(id);
				}
			}(this.id);
			$('edit-'+this.id).set('html', '').setStyle('display', 'none');
		} else if (this.op == 'restore') {
			$('comment-'+this.id).removeClass('m-co-t-delete');
			$('delbtn-'+this.id).removeClass('m-co-o-edit');
			$('delbtn-'+this.id)
				.getElement('a')
				.removeClass('lnk-dot-666')
				.addClass('lnk-dot-red')
				.set('text', 'Удалить');
			$('item-'+this.id).getElement('.m-co-checkbox').removeProperty('disabled');
			$('delbtn-'+this.id).getElement('a').onclick = function(id) {
				return function() {
					return comm.del(id);
				}
			}(this.id);
			$('edit-'+this.id).set('html', '').setStyle('display', 'none');
		}
		this.op = '';
		this.id = '';
	}

	this.error = function(html) {
		if (this.op == 'save') {
			$('edit-'+this.id).getElements('input[type=button]').removeProperty('disabled');
			$('edit-'+this.id).getElements('a').removeProperty('disabled');
		} else if (this.op == 'restore' || this.op == 'del') {
			$('edit-'+this.id).set('html', '').setStyle('display', 'none');
		}
		this.id = '';
		this.op = '';
		alert(html);
	}

}

CommentForm.prototype = new Formasync();
var comm = new CommentForm();



var MAX_FILE_COUNT = 20;
var btn_cancel = '/images/btn-remove3.png';

var FilesList = function() {

	try {
		if(this.value == '+') {
			att = 0;
			att = $$('.cl-form-files .form-files-added input[name^=attaches]').length;

			if($$('.cl-form .form-files-list input[type=file]').length+att >= MAX_FILE_COUNT) {
				return false;
			}

			l = this.getParent('li').clone();
			l.inject(this.getParent('ul.form-files-list'));
			//                l.getElement('input[type=file]').set('value', '');
			_tmp = l.getElement('input[type=file]');
			im = new Element('input', {
				'type' : 'file',
				'name' : _tmp.get('name'),
				'class' : _tmp.get('class'),
				'size' : _tmp.get('size')
			});
			im.cloneEvents(_tmp);
			l.getElement('input[type=file]').dispose();
			im.inject(l.getElement('input[type=image]'), 'before');
			l.getElement('input[type=image]').cloneEvents(this);

			if($$('.cl-form .form-files-list input[type=file]').length >= MAX_FILE_COUNT)
				l.getElement('input[type=image]').setStyle('display', 'none');

			this.set('src', btn_cancel);
			this.set('value', '-');
		} else {
			if($$('.cl-form .form-files-list input[type=file]').length <= 10) {
				$$('.cl-form .form-files-list li input[type=image]').setStyle('display', 'inline-block');
			}

			this.getParent('li').dispose();
		}
	} catch (e) {
		alert(e);
	}

	return false;
};

function deleteAttach(el) {
    el = $(el);
    el.getParent('li').getElement('input').set('name', 'rmattaches[]');
    el.getParent('li').addClass('attach-deleted');
    el.getParent('li').setStyle('display', 'none');
}


function toggleFiles(id, hide) {
    el = $('form-'+id).getElement('.cl-form-files');
    el.setStyle('display', el.getStyle('display') == 'none' && !hide ? '' : 'none');
}

function toggleYoutube(id, hide) {
    el = $('form-'+id).getElement('.cl-form-video');
    el.setStyle('display', el.getStyle('display') == 'none' && !hide ? '' : 'none');
}

function warnMax() {
	/*if (confirm('У пользователя максимальное количество предупреждений. Забанить?')) {
		alert('Забанили');
	}*/
	alert('У пользователя максимальное количество предупреждений.');
	return false;
}
