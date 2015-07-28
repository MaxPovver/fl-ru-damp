origHtml = new Array();
function UpdateNote(num,version,tw){
	origHtml[num] = document.getElementById('notetd'+num).innerHTML;
	document.getElementById('notetd'+num).innerHTML='\
	<table width="100%" cellspacing="0" cellpadding="0" border="0">\
<tr>\
	<td style="padding-bottom:5px;border:0"><div class="b-textarea"><textarea class="b-textarea__textarea b-textarea__textarea_height_70" rows="5" name="text" id="text'+num+'" onkeyup="(checknote(this))"></textarea></div></td>\
</tr>\
<tr>\
	<td style="border:0"><button class="b-button b-button_flat b-button_flat_grey b-button_float_right" type="button" name="reset" onClick="resetformN('+num+');">Отменить</button><button class="b-button b-button_flat b-button_flat_grey" type="button" name="save" onClick="saveformN(\''+act[num]+'\','+num+','+version+')">Сохранить</button</td>\
</tr>\
</table>';
	document.getElementById('text'+num).value = src[num].replace(/(&lt;)/ig,'<').replace(/(&gt;)/ig,'>');
	document.getElementById('text'+num).focus();
}

function checknote(message) {
    var maxLen = 200;
    if ((message.value.length) > maxLen)
    {
        alert('Максимальный размер заметки 200 символов!');
        message.value = message.value.substring(0, maxLen);
    }
}

function resetformN(num){
	document.getElementById('notetd'+num).innerHTML = origHtml[num];
}

function saveformN(actl, num,version){
	txt[num] = document.getElementById('text'+num).value;
	src[num] = document.getElementById('text'+num).value;
	if (txt[num].length > 200) {
        alert('Максимальный размер заметки 200 символов!');
        return false;
    }
    document.getElementById('notetd'+num).innerHTML = origHtml[num];
	xajax_FormSave(login[num], txt[num], actl, rl[num], num, version);
}

function headerNoteForm() {
    if ( headerNote.replace(/(^\s+)|(\s+$)/g, "") != '' ) {
        $('header_note').set('value',headerNote.replace(/(&lt;)/ig,'<').replace(/(&gt;)/ig,'>') ).setStyle('color','#444');
    }
    else {
        $('header_note').set('value','Текст этой заметки будет виден только вам.').setStyle('color','#999');
        headerNoteFocus = function(){$('header_note').setStyle('color','#444').set('value','');$('header_note').removeEvent('focus',headerNoteFocus);};
        $('header_note').addEvent( 'focus', headerNoteFocus);
    }
    
    $('zametka').addClass('b-layout_hide');
    $('zametka_fmr').removeClass('b-layout_hide');
}

function headerNoteText() {
    $('zametka').removeClass('b-layout_hide');
    $('zametka_fmr').addClass('b-layout_hide');
    $('header_note').set('value', '');
}
