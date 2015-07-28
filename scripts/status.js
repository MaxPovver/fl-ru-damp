var origHtmlStatus;
var statusType;
stdStatus = "Опишите здесь свое состояние. Эту надпись будут видеть все, кто зайдет на вашу страницу";
function UpdateStatus_OLD(login){
	origHtmlStatus = document.getElementById('status').innerHTML;
//	txt = statusTxt;
 txt = statusTxtSrc.data;
	radio = '';
	for (i = 1; i < statstr.length; i++){
		radio += '<input style="margin-left:0;width:15px" type="radio" id="statusType'+(i-1)+'" name="StatusType" value="'+i+'">'+
		         '<label for="statusType'+(i-1)+'">&nbsp;'+statstr[i]+'</label><br/>';
	}
	radio += '<input style="margin-left:0;width:15px" type="radio" id="statusType-1" name="StatusType" value="0">'+
	         '<label for="statusType-1">&nbsp;'+statstr[0]+'</label><br/>';
	document.getElementById('status').innerHTML='\
	<table width="100%" cellspacing="0" cellpadding="0" border="0">\
<tr>\
	<td colspan="2" style="padding-top: 0px;">'+radio+'</td>\
</tr>\
<tr>\
	<td colspan="2" style="padding-top: 4px;">\
	<textarea style="width:100%" rows="2" name="text" id="StatusText" onKeyUp="check_length(this)" onfocus="if(this.innerHTML==stdStatus)this.innerHTML=\'\';">'+((txt)?txt:stdStatus)+'</textarea></td>\
</tr>\
<tr>\
	<td><input type="button" name="reset" value="Отменить" onClick="resetform();"></td>\
	<td align="right"><input type="button" name="save" value="Сохранить" onClick="saveform(\''+login+'\')"></td>\
</tr>\
</table>';
	//document.getElementById('StatusText').value = (txt)?txt:stdStatus;
	document.getElementById('statusType'+statusType).checked=true;
}

function UpdateStatus(login){
	origHtmlStatus = document.getElementById('status').innerHTML;
	document.getElementById('status').getParent().removeClass('b-fon_bg_f0ffdf_hover').removeClass('b-layout_hover').removeClass('b-layout_inline-block').addClass('b-fon_bg_f0ffdf');
	if(document.getElementById('statusText')) {
		document.getElementById('statusText').getParent().addClass('b-layout_hide');
	}
        txt = statusTxtSrc.data;
	radio = '<div class="b-layout b-layout_padbot_10"><div class="b-select b-select_inline-block"><select id="status_type" class="b-select__select" name="StatusType">';
	for (i = 1; i < statstr.length; i++){
		radio += '<option value="'+(i-1)+'">'+statstr[i]+'</option>';
	}
	radio += '<option value="-1">Статус не выбран</option></select></div>';
	document.getElementById('status').innerHTML='\
'+radio+'&nbsp; <a href="javascript:saveform(\''+login+'\')" class="b-button b-button_flat b-button_flat_grey">Сохранить</a>&nbsp; <a href="javascript:resetform();" class="b-layout__link b-layout__link_bordbot_dot_000">Отменить</a></div>\
<div class="b-textarea"><textarea id="StatusText" rows="3" cols="20" class="b-textarea__textarea" rel="200" placeholder="'+stdStatus+'" onfocus="if(this.innerHTML==stdStatus)this.innerHTML=\'\';">'+txt+'</textarea></div>';
	//document.getElementById('StatusText').value = (txt)?txt:stdStatus;
	document.getElementById('status_type').value = statusType;
        if(typeof tawlTextareaInit == 'function') tawlTextareaInit();
}

function resetform(){
	document.getElementById('status').innerHTML = origHtmlStatus;
	document.getElementById('status').getParent().addClass('b-fon_bg_f0ffdf_hover').addClass('b-layout_hover').removeClass('b-fon_bg_f0ffdf');
	if(document.getElementById('statusText')){
		document.getElementById('statusText').getParent().removeClass('b-layout_hide');
	}
	else{
        document.getElementById('status').getParent().addClass('b-layout_inline-block')
	}
}

function saveform(login){
	txt = document.getElementById('StatusText').value;
	if (txt == stdStatus) txt = '';
	statusType = document.getElementById('status_type').value;
	document.getElementById('status').innerHTML = origHtmlStatus;
	xajax_SaveStatus(txt, statusType, login);
	document.getElementById('status').getParent().addClass('b-fon_bg_f0ffdf_hover').addClass('b-layout_hover').removeClass('b-fon_bg_f0ffdf');
	if(document.getElementById('statusText')){
		document.getElementById('statusText').getParent().removeClass('b-layout_hide');
	}
	else{
        document.getElementById('status').getParent().addClass('b-layout_inline-block')
	}
	if (txt=='') {
		document.getElementById('statusText').getParent().addClass('b-layout_hide');
        document.getElementById('status').getPrevious().dispose();
        document.getElementById('status').getParent().addClass('b-layout_inline-block');
	}
}

function check_length(message){
  var maxLen = 1500;
  var k = message.value.split("\n");
  var d = k.length*4;
  if ((message.value.length+d) > maxLen)
    {
        document.getElementById('len_err').style.display = 'block';   
        message.value = message.value.substring(0, maxLen-d);
    }else{
        document.getElementById('len_err').style.display = 'none'; 
    }
  }
