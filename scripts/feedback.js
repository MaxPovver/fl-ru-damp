var e_complete = false;

window.addEvent('domready', function() {
    try {
        if (!pagename) {
            return;
        }
    } catch(e) {
    	return;
    }
    if (pagename == 'feedback') {
		$each($$('input[name=rndnum]'),function(el) {
     		el.addEvent('keypress',function(e) {
     			if(e.key == 'enter') {
     				var id_num = $(this).get('id');
     				var num = id_num.replace(/fb-rndnum/, '');
     				if($('feedback-send'+num).hasClass('btnr-disabled')==false && (parseInt(num)+0)>0) {
     					asyncSend(num);
     				}
     				return false;
     			}
    		}.bind(el));
    	});
//		$('fb-type').getElements('li a').addEvent('click', function(){
//			$(this).getParent('.form-feedback-cat').getElements('li').removeClass('active');
//			$(this).getParent('.form-feedback-cat').getElements('input[type=radio]').setProperty('checked', 'false');
//			$(this).getParent('li').addClass('active');
//			$(this).getNext().setProperty('checked', 'true');
//			return false;
//		});
    } 
    else if (pagename == 'evaluate') {
        if ($('wish')) {
			new TextareaLimit($('wish'), MAX_WISH_CHARS);
		}
    }
});


function Send() {
	var error = '';
	$$('.form-err').removeClass('form-err');
	if (!$('fb-rndnum').value.replace(/(^\s+)|(\s+$)/g, "")) {
		$('r5').addClass('form-err');
		error = 'Пожалуйста, введите символы на картинке.';
	}
	if (!$('fb-msg').value.replace(/(^\s+)|(\s+$)/g, "")) {
		$('r4').addClass('form-err');
		error = 'Пожалуйста, опишите свою проблему.';
	}

	if (!userId) {
		var m = new RegExp("^[-^!#$%&'*+=?`{|}~.\\w]+@[-a-zA-Z0-9]+(\\.[-a-zA-Z0-9]+)+$");
		if (!m.test($('fb-mail').value)) {
			$('r3').addClass('form-err');
			error = 'Пожалуйста, укажите свое имя и email для обратной связи.';
		}
		if (!$('fb-name').value.replace(/(^\s+)|(\s+$)/g, "")) {
			$('r2').addClass('form-err');
			error = 'Пожалуйста, укажите свое имя и email для обратной связи.';
		}
	}
	if (!$('fb-type').getElement('input:checked')) {
		$('r1').addClass('form-err');
		error = 'Пожалуйста, выберите соответствующую тему вопроса.';
	}
	if (error) {
		$('err').setStyle('display', '').getElement('.fw-in').set('text', error);
        $('fb-rndnum').set('value', '');
        $('feedback-capcha').set('src','/image.php?r='+Math.random());
		return false;
	}
	var frm = new formasync();
	frm.send(document.getElementById('feed-form'));
	return false;
}


function StarsSet(type, stars) {
	var current = $('e'+type).value;
	$('stars-'+type).removeClass('vote-'+current);
	$('stars-'+type).addClass('vote-'+stars);
	$('e'+type).setProperty('value', stars);
	return false;
}

function Evaluate() {
	if (e_complete) {
		return false;
	}
	if ($('e1').value == 0 && $('e2').value == 0 && $('e3').value == 0 && $('wish').value.replace(/(^\s+)|(\s+$)/g, "") == '') {
		$('err').setStyle('display', '').getElement('.fw-in').set('text', 'Пожалуйста, поставте хотя бы одну оценку или напишите пожелание.');
		return false;
	}
	if ($('wish').value.replace(/(^\s+)|(\s+$)/g, "").length > MAX_WISH_CHARS) {
		return false;
	}
	$('e-form').set('send', {
		onRequest: function() {
			$('evaluate-success').setStyle('display', 'none');
			$('evaluate-waiting').setStyle('display', '');
			$('evaluate-send').addClass('btnr-disabled');
			$('err').setStyle('display', 'none').getElement('.fw-in').set('text', '');
		},
		onSuccess: function(html) {
			$('evaluate-waiting').setStyle('display', 'none');
			if (html.indexOf('Success:') != -1) {
				$('evaluate-success').setStyle('display', '');
				e_complete = true;
			} else {
				$('err').setStyle('display', '').getElement('.fw-in').set('text', html.substr(6));
				$('evaluate-send').removeClass('btnr-disabled');
			}
		}
	});
	$('e-form').send();
	return false;
}

function formasync() {

	this.interval = null;
	this.sended = false;
	this.setflag = false;
        this.index = 1;
	
	this.send = function(form, noflag) {
        this.clearErrors();
		if (this.sended) {
			return false;
		} else {
			this.sended = true;
			this.waiting();
		}
		var name = 'formasync-' + Math.round(Math.random() * 1000000);
        var rx = navigator.userAgent.toLowerCase().match(/msie ([0-9]+)/);
        if ( rx && parseInt(rx[1]) <= 8 ) {
            var iframe = document.createElement('<iframe name='+name+'></iframe>');
		} else {
			var iframe = document.createElement('iframe');
			iframe.name = name;
			iframe.setAttribute('name', name);
		}
		iframe.setAttribute('width', 500);
		iframe.setAttribute('height', 300);
		iframe.setAttribute('id', name);
		iframe.style.position = 'absolute';
		iframe.style.display  = 'none';
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
				self.multiError(html.substr(6));
				document.getElementsByTagName("body")[0].removeChild(iframe);
				self.sended = false;
			}
		}, 500);
		if (!noflag && !this.setflag) {
			var h = document.createElement('input');
			h.setAttribute('type', 'hidden');
			h.setAttribute('name', 'formasync');
			h.setAttribute('value', '1');
			form.appendChild(h);
			this.setflag = true;
		}
		form.setAttribute('target', name);
		form.submit();
		return false;
	}
	
	this.waiting = function() {
            this.clearErrors();
		$('feedback-success'+this.index).style.display = 'none';
//		$('feedback-waiting'+self.index).setStyle('display', '');
		$('feedback-send'+this.index).className += ' btnr-disabled';
	};
	
	this.success = function(html) {
            this.clearErrors();
//		$('feedback-waiting'+self.index).setStyle('display', 'none');
		$('feedback-send'+this.index).className = $('feedback-send'+this.index).className.replace(/\s?btnr-disabled\s?/, "");
		$('feedback-success'+this.index).style.display = '';
//		$('err'+this.index).setStyle('display', 'none').getElement('.fw-in').set('text', '');
//		$('fb-type').getElements('input[type=radio]').removeProperty('checked');
//		$('fb-type').getElements('li').removeClass('active');
		$('fb-msg'+this.index).value = '';
		
		if ( $('fb-name'+this.index) ) {
            $('fb-name'+this.index).value = '';
		}
		
		if ( $('fb-mail'+this.index) ) {
            $('fb-mail'+this.index).value = '';
		}
		
		if ( $('fb-agree'+this.index) ) {
            $('fb-agree'+this.index).checked = false;
		}
		
//		$('fb-attach'+self.index).getParent().set('html', $('fb-attach'+self.index).getParent().get('html'));
		$('fb-rndnum'+this.index).value = '';
                
        try {
            $('feedback-capcha'+this.index).src = '/image.php?num='+this.index+'_'+captchanum+'&rnd='+Math.round(Math.random() * 1000000);
        } catch(e) {
            $('feedback-capcha'+this.index).src = '/image.php?r='+Math.round(Math.random() * 1000000);
        }
		
		for (var i = 1; i < 10; i++) {
			var block = $('files_block' + i);
			if (block) {
				var p = block.parentNode;
				if (p) {
					var ls = p.getElementsByTagName('input');
					var stack = new Array();
					for (var j = 0; j < ls.length; j++) {
						if (ls[j].className.indexOf('i-file') != -1) {
						  stack.push(ls[j]);
						}						
					}					
					for (var j = 0; j < stack.length; j++) {
						var prnt = stack[j].parentNode;
						var placer = prnt.getElementsByTagName("input");
						if (placer.length > 1) {
							placer = placer[1];					
							prnt.removeChild(stack[j]);
							try {
							    var input = new Element("input", {"class":"i-file", "type":"file", "size":23, "name":"attach[]"});
							    input.inject(placer, "before");
							} catch (e) {
							    var input = document.createElement("input");
	                            input.className = "i-file";
	                            input.type      = "file";
	                            input.size      = 23;
	                            input.name      = "attach";
	                            placer.parentNode.appendChild(input);
							}
						}
					}
			   }
			}
		}
        	if (window['webim']) {
        		var s = window.location.href.replace(/&?action=[^&]*&?/, "");
        		window.location.href = s + "&action=leave";
            }
	};
    
    this.multiError = function( html ) {
        var e = html.split(';');
        var fileIndex = e[2];
        var fileErrorText = e[1];
        if ( e.length > 2 ) {
            for ( var i = 2; i < e.length; i++ ) {
                e[1] = e[1] + e[i];
            }
            e = e.slice(0,2);
        }
        var idx = e[0].split(',');
        var alt = e[1].split('~~||~~');
        for ( i = 0; i < idx.length; i++  ) {
            switch(idx[i]){
                case '2':
                    if ( i == 0 ) $('err_name_'+this.index).setStyle('display', 'block');
                    $('fb-name'+this.index).addClass('invalid');
                    break;
                case '3':
                    if ( i == 0 ) $('err_email_'+this.index).setStyle('display', 'block');
                    $('fb-mail'+this.index).addClass('invalid');
                    break;
                case '4':
                    if ( i == 0 ) $('err_msg_'+this.index).style.display = 'block';
                    $('fb-msg'+this.index).className += ' invalid';
                    break;
                case '5':
                    if ( i == 0 ) $('err_captcha_'+this.index).style.display = 'block';
                    $('fb-rndnum'+this.index).className += ' invalid';
                    break;
                case '6':
                    if (window['webim']) {
                        $('err_attach_'+this.index).style.display = 'block';
                        $('err_attach_' + this.index).style.marginTop = (parseInt(fileIndex) * 28) + 'px';
                        $$('#err_attach_'+this.index + ' strong').set('html', fileErrorText);
                    } else {
                        $('err_attach_'+this.index).style.display = 'block';
                        $('err_attach_' + this.index).style.marginTop = (parseInt(fileIndex) * 28) + 'px';
                        $$('#err_attach_'+this.index + ' strong').set('html', fileErrorText.indexOf("~~||~~") == -1 ? fileErrorText : alt[i]);
                    }
                    break;
                case '7':
                    $('err_department_'+this.index).style.display = 'block';
                    break;
            }
        }
        $('feedback-send'+this.index).className = $('feedback-send'+this.index).className.replace(/\s?btnr-disabled\s?/, "");
        $('fb-rndnum'+this.index).value = '';
		try {
            $('feedback-capcha'+this.index).src = '/image.php?num='+this.index+'_'+captchanum+'&rnd='+Math.round(Math.random() * 1000000);
		} catch(e) {
            $('feedback-capcha'+this.index).src = '/image.php?r='+Math.round(Math.random() * 1000000);
        }
    };
    
	this.error = function(html) {
		var e = html.split(';', 2);
                this.clearErrors();
                switch(e[0]){
                    case '2':
                        $('err_name_'+this.index).setStyle('display', 'block');
                        $('fb-name'+this.index).addClass('invalid');
                        break;
                    case '3':
                        $('err_email_'+this.index).setStyle('display', 'block');
                        $('fb-mail'+this.index).addClass('invalid');
                        break;
                    case '4':
                        $('err_msg_'+this.index).setStyle('display', 'block');
                        $('fb-msg'+this.index).addClass('invalid');
                        break;
                    case '5':
                        $('err_captcha_'+this.index).setStyle('display', 'block');
                        $('fb-rndnum'+this.index).addClass('invalid');
                        break;
                    case '6':
                        $$('#err_attach_'+this.index + ' strong').set('html', e[1]);
                        $('err_attach_'+this.index).setStyle('display', 'block');
                        break;
                    case '7':
                        $$('#err_department_'+this.index + ' strong').set('html', e[1]);
                        $('err_department_'+this.index).setStyle('display', 'block');
                        break;
                }

//		$('feedback-waiting'+self.index).setStyle('display', 'none');
		$('feedback-send'+this.index).removeClass('btnr-disabled');
//		$('r'+e[0]).addClass('form-err');
//		$('err'+this.index).setStyle('display', '').getElement('.fw-in').set('text', e[1]);
		$('fb-rndnum'+this.index).set('value', '');
        try {
            $('feedback-capcha'+this.index).set('src', '/image.php?num='+this.index+'_'+captchanum+'&rnd='+Math.round(Math.random() * 1000000));
        } catch(e) {
            $('feedback-capcha'+this.index).set('src', '/image.php?r='+Math.round(Math.random() * 1000000));
        }
	};

        this.clearErrors = function(){
            $('err_captcha_'+this.index).style.display = 'none';
	        $('fb-rndnum'+this.index).className = $('fb-rndnum'+this.index).className.replace(/\s?invalid\s?/, "");
	        $('err_msg_'+this.index).style.display = 'none';
	        //$('err_msg'+this.index).className = $('err_msg'+this.index).className.replace(/\s?invalid\s?/, "");
	        $('err_attach_'+this.index).style.display = 'none';
	        if ($('err_department_'+this.index)) {
	            $('err_department_'+this.index).style.display = 'none';
	        }
	        
	        if ( $('fb-name'+this.index) ) {
	            $('fb-name'+this.index).className = $('fb-name'+this.index).className.replace(/\s?invalid\s?/, "");
	        }
	        
	        if ( $('fb-mail'+this.index) ) {
	            $('fb-mail'+this.index).className = $('fb-mail'+this.index).className.replace(/\s?invalid\s?/, "");
	        }
        };
	
}

function TextareaLimit(obj, limit) {

	this.interval = null;
	this.warning = 0;
	var self = this;

	obj.onfocus = function() {
		self.interval = setInterval(function() {
			if (obj.value.length > limit) {
				self.warning = 1;
				self.warn(obj);
			} else if (this.warning) {
				self.warning = 0;
				self.ok(obj);
			}
		}, 500);
	}

	obj.onblur = function() {
		clearInterval(self.interval);
	}

	this.warn = function(obj) {
		$('warnmess').setStyle('display', 'block');
	}

	this.ok = function(obj) {
		$('warnmess').setStyle('display', 'none');
	}

}

function feedbackClearError( etype, idx ) {
    switch ( etype ) {
        case 2:
            $('err_name_'+idx).style.display = 'none';
            $('fb-name'+idx).className = String($('fb-name'+idx).className).replace(/\s?invalid\s?/, "");
            break;
        case 3:
            $('err_email_'+idx).style.display = 'none';
            if ( $('fb-email'+idx) ) {
                $('fb-email'+idx).className = String($('fb-email'+idx).className).replace(/\s?invalid\s?/, "");
            }
            break;
        case 4:
            $('err_msg_'+idx).style.display = 'none';
            if ( $('err_msg'+idx) ) {
                $('err_msg'+idx).className = String($('err_msg'+idx).className).replace(/\s?invalid\s?/, "");
            }
            break;
        case 5:
            $('err_captcha_'+idx).style.display = 'none';
            if( $('err_captcha'+idx) )
                $('err_captcha'+idx).className = String($('err_captcha'+idx).className).replace(/\s?invalid\s?/, "");
            break;
    }
}
