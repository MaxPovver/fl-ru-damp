function toggle_yt_link() {
    if (yt_link) {
        $('yt_link').style.display = 'none';
        yt_link = false;
    } else {
        $('yt_link').style.display = 'block';
        yt_link = true;
    }
    return false;
}

function toggle_settings() {
    var a=$('settings').style;
    if(a.display=='none') a.display='block';
    else a.display='none';
}

function toggle_private() {
    if ( $('ch_is_private').get('checked') ) {
        $('label_is_private').set('html', 'Показывать только мне (скрытые от пользователей темы видны модераторам)');
    }
    else {
        $('label_is_private').set('html', 'Показывать только мне');
    }
}

function toggle_pool() {
    if ($$('.poll-line')[0].style.display != 'none') {
		$$('.poll-line').setStyle('display', 'none');
		$$('.poll-st').setStyle('display', 'none');
		$$('.poll-type').setStyle('display', 'none');
	} else {
		$$('.poll-line').setStyle('display', '');
		$$('.poll-st').setStyle('display', '');
		$$('.poll-type').setStyle('display', '');
	}
}

function toggle_attach() {var a=$('attach').style;if(a.display!='block') a.display='block';else a.display='none';}

function checkexts() {
    var val = 0;
    var grp = document.getElementById('frm')['attach[]'];
    if (typeof grp == 'undefined') {
        return allowedExt(document.getElementById('frm')['attach']);
    } else if (typeof grp.length != 'undefined') {
        for (i=0; i<grp.length; i++) {
            if (!allowedExt(grp[i].value)) return false;
        }
    } else {
        if (!allowedExt(grp.value)) return false;
    }
    if (document.getElementById('msg').value.length > blogs_max_desc_chars) {
        alert("Максимальный размер сообщения " + blogs_max_desc_chars + " символов!");
        return false;
    }
    return true;
}

function domReady( f ) {
    if ( domReady.done ) return f();

    if ( domReady.timer ) {
        domReady.ready.push( f );
    } else {
        if (window.addEventListener)
            window.addEventListener('load',isDOMReady, false);
        else if (window.attachEvent)
            window.attachEvent('onload',isDOMReady);

        domReady.ready = [ f ];
        domReady.timer = setInterval( isDOMReady, 13 );
    }
}

function isDOMReady(){
    if ( domReady.done ) return false;

    if ( document && document.getElementsByTagName && document.getElementById && document.body ) {
        clearInterval( domReady.timer );
        domReady.timer = null;

        for ( var i = 0; i < domReady.ready.length; i++ )
            domReady.ready[i]();

        domReady.ready = null;
        domReady.done = true;
    }
}

function goToAncor(name) {
    domReady(function(name) {
            return function() {
                var a = document.getElementsByTagName('A');
                for (var i = 0, len = a.length; i < len; i++) {
                    if (a[i].name == name) {
                        a[i].scrollIntoView(true);
                        break;
                    }
                }
            }
    }(name));
}

function maxChars(textarea, box, max) {
	if (typeof textarea == 'string') textarea = document.getElementById(textarea);
	if (typeof box == 'string') box = document.getElementById(box);
	if (typeof textarea != 'object' || typeof box != 'object') return false;
	textarea.onchange = textarea.onkeyup = textarea.onkeydown = function() {
		if (textarea.value.length > max) {
			box.innerHTML = 'Максимальная длина сообщения ' + max + ' символов!';
			box.style.display = 'block';
			textarea.value = textarea.value.substr(0, max + 1);
		} else {
			box.innerHTML = '&nbsp;';
			box.style.display = 'none';
		}
	}
}

/**
 * вызывается когда меняется состояние чекбокса "Запретить комментирование"
 */
function toggle_close () {
    if ($('ch_close_comments').get('checked')) {
        $('label_close_comments').set('text', 'Запретить комментирование (тема будет перенесена в раздел "Личные блоги")');
    } else {
        $('label_close_comments').set('text', 'Запретить комментирование');
    }
}
