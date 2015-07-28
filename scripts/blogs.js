
var order_now = "date";

var fav_orders = new Array;
fav_orders['date'] = "дате";
fav_orders['priority'] = "важности";
fav_orders['abc'] = "алфавиту";

function FavPriority(thread_id, priority)
{
	if (document.getElementById('favpriority' + thread_id))
	{
		document.getElementById('favpriority' + thread_id).value = priority;
	}
/*
	document.getElementById('favpic' + thread_id + '-0').src = '/images/ico_star_0_empty.gif';
	document.getElementById('favpic' + thread_id + '-1').src = '/images/ico_star_1_empty.gif';
	document.getElementById('favpic' + thread_id + '-2').src = '/images/ico_star_2_empty.gif';
	document.getElementById('favpic' + thread_id + '-3').src = '/images/ico_star_3_empty.gif';
	document.getElementById('favpic' + thread_id + '-' + priority).src = '/images/ico_star_' + priority + '.gif';
	document.getElementById('favstar' + thread_id).src = '/images/ico_star_' + priority + '.gif';
*/
}

var currentLayer = 0;
var currentOpen = false;

function ShowFavFloat(thread_id)
{
	if (currentLayer && !currentOpen)
	{
		HideFavFloat(0, 0);
	}

	currentLayer = thread_id;
	currentOpen = true;

	if (document.getElementById('FavFloat' + thread_id).innerHTML == "")
	{
		var outHTML = '';

		outHTML += '<DIV style="border:#A7A6AA 1px solid; background:#FFFFFF; position:absolute;">';
		outHTML += '<IMG id="showstar' + thread_id + '-0" alt="" src="/images/bookmarks/bsg.png"  border="0" vspace="1" onClick="xajax_AddFavBlog(' + thread_id + ', 0, 0, \'add\', order_now); HideFavFloat(' + thread_id + ', 0);" style="cursor:hand;"><BR>';
		outHTML += '<IMG id="showstar' + thread_id + '-1" alt="" src="/images/bookmarks/bsgr.png"  border="0" vspace="1" onClick="xajax_AddFavBlog(' + thread_id + ', 1, 0, \'add\', order_now); HideFavFloat(' + thread_id + ', 1);" style="cursor:hand;"><BR>';
		outHTML += '<IMG id="showstar' + thread_id + '-2" alt="" src="/images/bookmarks/bsy.png"  border="0" vspace="1" onClick="xajax_AddFavBlog(' + thread_id + ', 2, 0, \'add\', order_now); HideFavFloat(' + thread_id + ', 2);" style="cursor:hand;"><BR>';
		outHTML += '<IMG id="showstar' + thread_id + '-3" alt="" src="/images/bookmarks/bsr.png" border="0" vspace="1" onClick="xajax_AddFavBlog(' + thread_id + ', 3, 0, \'add\', order_now); HideFavFloat(' + thread_id + ', 3);" style="cursor:hand;">';
		outHTML += '</DIV>';

		document.getElementById('FavFloat' + thread_id).innerHTML = outHTML;
	}
	else
	{
		document.getElementById('FavFloat' + thread_id).style.display = 'block';
	}

	var show_star = 0;

	// blogs main
	if (document.getElementById('favpriority' + thread_id))
	{
		var show_star = document.getElementById('favpriority' + thread_id).value;
	}
	// blogs inner
	else if (document.getElementById('favpriority'))
	{
		var show_star = document.getElementById('favpriority').innerHTML;
	}

	document.getElementById('showstar' + thread_id + '-0').src = '/images/bookmarks/bsg.png';
	document.getElementById('showstar' + thread_id + '-1').src = '/images/bookmarks/bsgr.png';
	document.getElementById('showstar' + thread_id + '-2').src = '/images/bookmarks/bsy.png';
	document.getElementById('showstar' + thread_id + '-3').src = '/images/bookmarks/bsr.png';
	//document.getElementById('showstar' + thread_id + '-' + show_star).src = '/images/ico_star_' + show_star + '.gif';

	return true;
}

function HideFavFloat(thread_id, priority)
{
	if (!thread_id && !currentOpen && currentLayer)
	{
		document.getElementById('FavFloat' + currentLayer).style.display = 'none';
		currentLayer = 0;
	}
	else if (thread_id)
	{
		document.getElementById('FavFloat' + thread_id).style.display = 'none';
	}

	// blogs inner
	if (document.getElementById('favpriority'))
	{
		document.getElementById('favpriority').innerHTML = priority;
	}

	currentOpen = false;

	return true;
}


var currentOrder = false;
var currentOrderStr = 0;

function ShowFavOrderFloat()
{
	currentOrder = true;

	var outHTML = '';

	outHTML += '<DIV style="background:#DFDFDF; position:absolute; margin:-14 0px 0px -3px; padding:2px 2px 2px 4px; font-size:10px; width:70px;">';
	outHTML += '<A href="#" onclick="xajax_AddFavBlog(0, 0, 0, \'\', \'date\'); HideFavOrderFloat(0); return false;" style="color:#5C5C5C">дате</A><BR>';
	outHTML += '<A href="#" onclick="xajax_AddFavBlog(0, 0, 0, \'\', \'priority\'); HideFavOrderFloat(1); return false;" style="color:#5C5C5C">важности</A><BR>';
	outHTML += '<A href="#" onclick="xajax_AddFavBlog(0, 0, 0, \'\', \'abc\'); HideFavOrderFloat(2); return false;" style="color:#5C5C5C">алфавиту</A>';
	outHTML += '</DIV>';

	document.getElementById('fav_order_float').innerHTML = outHTML;
	document.getElementById('fav_order_float').style.display = 'block';

	return true;
}

function HideFavOrderFloat(order)
{
	var order_str = '';

	if (order == 0)	{order_now = "date";}
	if (order == 1)	{order_now = "priority";}
	if (order == 2)	{order_now = "abc";}

	if (!currentOrder) 
	{
		var fav_order = document.getElementById('fav_order');

		if (fav_order)
		{
			fav_order.innerHTML = fav_orders[order_now];
			fav_order.innerHTML += ' <IMG alt="" src="/images/ico_fav_arrow.gif" width="5" height="3" border="0" align="absmiddle" style="margin:4px 0px 0px 2px;">';
			fav_order.innerHTML += '<DIV id="fav_order_float" style="displa:none;"></DIV>';
		}

		var fav_order_float = document.getElementById('fav_order_float');

		if (fav_order_float)
		{
			fav_order_float.innerHTML = '';
			fav_order_float.style.display = 'none';
		}
	}

	currentOrder = false;
	currentOrderStr = order;

	return true;
}


function resetBlogForm() {
    _frm = $('frm');
    if (!_frm) {
        return 1;
    }
    //tid = _frm.getElement('input[name=thread_id]').get('value');
    
    if (!_frm.retrieve('oldtr')) {
        _frm.store('oldtr', 1);
    }
    
    var _u_token_key_val = '';
    if(_frm.getElement('input[name=u_token_key]')) {
    	_u_token_key_val = _frm.getElement('input[name=u_token_key]').get('value');
    }

    _frm.reset();
    _frm.getElements('input[type=text], textarea').set('value', '');
    _frm.getElements('input:checked').set('checked', false);
    _frm.getElement('input[id=fmultiple0]').set('checked', true);
    _frm.getElements('input[name=page],input[name=thread],input[name=thread_id]').dispose();
    if(_frm.getElement('div.apf-addedfiles') != undefined) {
        _frm.getElement('div.apf-addedfiles').getAllNext().dispose();
        _frm.getElement('div.apf-addedfiles').dispose();
    }
    _frm.getElements('select').each(function(el) {
        el.selectedIndex = 0;
    });
    
    polls = _frm.getElements('tr.poll-line');
    if (polls.length > 1) {
        polls.erase(polls.getLast());
        polls.dispose();
    }
    _frm.getElements('tr.poll-st, tr.poll-type, tr.poll-line, div#attach, div#settings').hide();
    
    _frm.getElement('div h2').set('html', 'Создать новое сообщение:');
       
   
	polls = _frm.getElements('tr.poll-line');
	if (polls.length > 0) {
		
		var inputs = polls[0].getElements("input.poll-answer");
		if (inputs.length > 0) {
			var input = inputs[0];
			var td = input.parentNode;
			td.innerHTML =  '';
			var input = new Element("input");
			input.set("class", "poll-answer");
			input.set("name", "answers[]");
			input.set("type", "text");
			input.inject(td, "top");
		}
		
		var ls = polls[0].getElements("td");
		if (ls.length > 0) {
			var ls2 = ls[0].getElements("span");
			if (ls2.length > 0) 
				ls2[0].set("html", "1");				
		}
	}		
	var table = $$(".blog-form-tbl")[0];
	if ( table && table.getElements) {
        var links = table.getElements("a");
        for (var i = 0; i < links.length; i++) {
            var s = links[i].get("html");
            s = s.replace("Редактировать опрос", "Добавить опрос");
            links[i].set("html", s);
        }
	}
	
	$("draft_time_save").set("style", "display:none");

    if(_frm.getElement('input[name=u_token_key]')) {
    	_frm.getElement('input[name=u_token_key]').set('value', _u_token_key_val);
    }

    if ($('draft_post_id')) {
        $('draft_post_id').set('value', '');
    }
    
    if ($('draft_id')) {
        $('draft_id').set('value', '');
    }
    
    _frm.getElement('input[name=action]').set('value', 'new_tr');

    if($('fcategory') && gr_value ) { $('fcategory').set('value', gr_value); }
    
    DraftInit(3);
    
    return 1;
}

function restoreBlogForm(el) {
    _frm = $('frm');
    if (!_frm) {
        return 1;
    }
    
    if (_frm.retrieve('oldtr')) {
        if ($(el).get('href'))
            document.location.href = $(el).get('href').replace(/(\#.*?)$/gi, '');
    }
    
    return 1;
}
