var submitFlag = 1;
var __commLastOpenedForm = null;
function __commCF(c, t, cm, om, p, a, md, adv)
{
    if (adv == undefined)
        adv = 0;
    var m = document.getElementById('idEditCommentForm_' + c);
    if (__commLastOpenedForm != m || __commLastOpenedForm.action != a)
        xajax_CreateCommentForm('idEditCommentForm_' + c, t, c, cm, om, p, a, md, adv);
}



function maxChars(textarea, box, max) {
    if (typeof textarea == 'string')
        textarea = document.getElementById(textarea);
    if (typeof box == 'string')
        box = $(box);
    if (typeof textarea != 'object' || typeof box != 'object')
        return false;
    textarea.onchange = textarea.onkeyup = textarea.onkeydown = function() {
        if (textarea.value.length > max) {
            if (box.getElement('div div div span strong')) {
                box.getElement('div div div span strong').set('html', 'Максимальная длина сообщения ' + max + ' символов!');
            } else {
                box.innerHTML = 'Максимальная длина сообщения ' + max + ' символов!';
            }
            box.style.display = 'block';
            textarea.value = textarea.value.substr(0, max + 1);
        } else {
            if (box.getElement('div div div span strong')) {
                box.getElement('div div div span strong').set('html', '&nbsp;');
            } else {
                box.innerHTML = '&nbsp;';
            }
            box.style.display = 'none';
        }
    }
}

var yt_link = false;

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
    var a = $('settings').style;
    if (a.display != 'block')
        a.display = 'block';
    else
        a.display = 'none';
}

function toggle_pool() {
    if ($$('.poll-line')[0].style.display != 'none') {
        $$('.poll-line').setStyle('display', 'none');
        $$('.poll-st').setStyle('display', 'none');
    } else {
        $$('.poll-line').setStyle('display', '');
        $$('.poll-st').setStyle('display', '');
    }
}
var toggle_box = function(el) {
    if ($(el)) {
        var a = $(el).style;
        if (a.display != 'block')
            a.display = 'block';
        else
            a.display = 'none';
    }
};
function toggle_attach() {
    var a = $('attach').style;
    if (a.display != 'block')
        a.display = 'block';
    else
        a.display = 'none';
}


window.onscroll = function() {
    var menus = $$('.b-page__title');
    var last_menu = menus[0];
    var scrolled = window.pageYOffset || document.documentElement.scrollTop;
    if ((last_menu.getSize().y + 70) > window.getSize().y) {
        var scrolledFrom = last_menu.getPosition().y + ((last_menu.getSize().y + 70) - window.getSize().y) + 300;
    } else {
        var scrolledFrom = last_menu.getPosition().y + 300;
    }
    scrolled > scrolledFrom ? $$('#upper').setStyle('visibility', 'visible') : $$('#upper').setStyle('visibility', 'hidden')
}

window.addEvent('domready', function() {
    if (typeof editor_customConfig != 'undefined' && typeof CKEDITOR != 'undefined') {
        CKEDITOR.config.customConfig = editor_customConfig;
    }
    
    if (typeof window["commentsInit"] != 'undefined') {
        window["commentsInit"]();
    } 
});