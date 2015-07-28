var order_now = "date";

var allow_add = true;

var fav_orders = new Array;
fav_orders['date'] = "дате";
fav_orders['priority'] = "важности";
fav_orders['abc'] = "алфавиту";

function getStarByPR(pr){
            switch(Number(pr)){
            case 0:
                return '/images/bookmarks/bsg.png';
                break;
            case 1:
                return '/images/bookmarks/bsgr.png';
                break;
            case 2:
                return '/images/bookmarks/bsy.png';
                break;
            case 3:
                return '/images/bookmarks/bsr.png';
                break;
            default:
                return '/images/bookmarks/bsw.png';
                break;
        }
}

function getEmptyStarByPR( pr ) {
    switch ( Number(pr) ) {
        case 0:
            return '/images/ico_star_0_empty.gif';
            break;
        case 1:
            return '/images/ico_star_1_empty.gif';
            break;
        case 2:
            return '/images/ico_star_2_empty.gif';
            break;
        case 3:
            return '/images/ico_star_3_empty.gif';
            break;
        default:
            return '/images/ico_star_0_empty.gif';
            break;
    }
}

function FavPriority(msg_id, priority) {
	if (document.getElementById('favpriority' + msg_id)) {
		document.getElementById('favpriority' + msg_id).value = priority;
	}
	

	document.getElementById('favpic' + msg_id + '-0').src = getEmptyStarByPR(0);
	document.getElementById('favpic' + msg_id + '-1').src = getEmptyStarByPR(1);
	document.getElementById('favpic' + msg_id + '-2').src = getEmptyStarByPR(2);
	document.getElementById('favpic' + msg_id + '-3').src = getEmptyStarByPR(3);
	
	document.getElementById('favpic' + msg_id + '-' + priority).src = getStarByPR( priority );
	
	var fs=document.getElementById('favstar' + msg_id);
	if ( fs ) fs.src = getStarByPR(priority);

}


function FavPriorityLenta(msg_id, priority, pfx) {
    if(pfx == undefined) pfx = 'CM';
	if (document.getElementById('favpriority' + pfx + msg_id)) {
		document.getElementById('favpriority' + pfx + msg_id).value = priority;
		document.getElementById('curfavstar' + pfx + msg_id).src = document.getElementById('favpic' + pfx + msg_id + '-' + priority).src;
	}
	

//	document.getElementById('favpic' + pfx + msg_id + '-0').src = '/images/ico_star_0_empty.gif';
//	document.getElementById('favpic' + pfx + msg_id + '-1').src = '/images/ico_star_1_empty.gif';
//	document.getElementById('favpic' + pfx + msg_id + '-2').src = '/images/ico_star_2_empty.gif';
//	document.getElementById('favpic' + pfx + msg_id + '-3').src = '/images/ico_star_3_empty.gif';
//	document.getElementById('favpic' + pfx + msg_id + '-' + priority).src = '/images/ico_star_' + priority + '.gif';
	//document.getElementById('favstarCM' + msg_id).src = '/images/ico_star_' + priority + '.gif';

}


var currentLayer = 0;
var currentOpen = false;

function ShowFavFloatLenta(msg_id, user, pfx) {
	if (currentLayer && !currentOpen) {
		HideFavFloat(0, 0);
	}

	currentLayer = msg_id;
	currentOpen = true;
	
	var _msg = pfx + msg_id;
	
//	if (document.getElementById('FavFloat' + msg_id).innerHTML == "") {
	    
	    var outHTML = '';
	    
//	outHTML += '<ul class="post-f-fav-sel">';
        outHTML += '<li><a href="javascript:void(0)"><img id="showstar' + _msg + '-0" src="'+getStarByPR(0)+'" alt="" onClick="xajax_Lenta_AddFav(\''+ msg_id +'\', \''+pfx+'\', ' + user + ', 0, 0); HideFavFloat(' + msg_id + ', 0);"></a></li>';
    	outHTML += '<li><a href="javascript:void(0)"><img id="showstar' + _msg + '-1" src="'+getStarByPR(1)+'" alt="" onClick="xajax_Lenta_AddFav(\''+ msg_id +'\', \''+pfx+'\', ' + user + ', 0, 1); HideFavFloat(' + msg_id + ', 1);"></a></li>';
    	outHTML += '<li><a href="javascript:void(0)"><img id="showstar' + _msg + '-2" src="'+getStarByPR(2)+'" alt="" onClick="xajax_Lenta_AddFav(\''+ msg_id +'\', \''+pfx+'\', ' + user + ', 0, 2); HideFavFloat(' + msg_id + ', 2);"></a></li>';
    	outHTML += '<li><a href="javascript:void(0)"><img id="showstar' + _msg + '-3" src="'+getStarByPR(3)+'" alt="" onClick="xajax_Lenta_AddFav(\''+ msg_id +'\', \''+pfx+'\', ' + user + ', 0, 3); HideFavFloat(' + msg_id + ', 3);"></a></li>';
//        outHTML += '</ul>';
    		
        document.getElementById('FavFloat' + msg_id).innerHTML = outHTML;
//    } else {
    	document.getElementById('FavFloat' + msg_id).style.display = 'block';
//    }

	var show_star = '';
	
	//lenta
	if (document.getElementById('favpriority' + _msg)) {
		var show_star = document.getElementById('favpriority' + _msg).value;
	}
    var stars = new Array();
    stars[0] = 'bsg.png';
    stars[1] = 'bsgr.png';
    stars[2] = 'bsy.png';
    stars[3] = 'bsr.png';
	document.getElementById('showstar' + _msg + '-0').src = getStarByPR(0);
	document.getElementById('showstar' + _msg + '-1').src = getStarByPR(1);
	document.getElementById('showstar' + _msg + '-2').src = getStarByPR(2);
	document.getElementById('showstar' + _msg + '-3').src = getStarByPR(3);
//	if(show_star != '') document.getElementById('showstar' + _msg + '-' + show_star).src = '/images/bookmarks/' + getStarByPR(show_star);

	return true;    
}


function ShowFavFloat(msg_id, user, om, lenta) {
    if(lenta == undefined) lenta = -1;
    
	if (currentLayer && !currentOpen) {
		HideFavFloat(0, 0);
	}

	currentLayer = msg_id;
	currentOpen = true;
	
	
//	if (document.getElementById('FavFloat' + msg_id).innerHTML == "") {
        var outHTML = '';
    		
//    	outHTML += '<ul class="post-f-fav-sel">';
    	outHTML += '<li><a href="javascript:void(0)"><img id="showstar' + msg_id + '-0" src="'+getStarByPR(0)+'" alt="" onClick="xajax_AddFav(\'favstar'+ msg_id +'\', \'fav' + msg_id + '\', ' + msg_id + ', ' + user + ', ' + om + ', 0, 0); HideFavFloat(' + msg_id + ', 0);"></a></li>';
    	outHTML += '<li><a href="javascript:void(0)"><img id="showstar' + msg_id + '-1" src="'+getStarByPR(1)+'" alt="" onClick="xajax_AddFav(\'favstar'+ msg_id +'\', \'fav' + msg_id + '\', ' + msg_id + ', ' + user + ', ' + om + ', 0, 1); HideFavFloat(' + msg_id + ', 1);"></a></li>';
    	outHTML += '<li><a href="javascript:void(0)"><img id="showstar' + msg_id + '-2" src="'+getStarByPR(2)+'" alt="" onClick="xajax_AddFav(\'favstar'+ msg_id +'\', \'fav' + msg_id + '\', ' + msg_id + ', ' + user + ', ' + om + ', 0, 2); HideFavFloat(' + msg_id + ', 2);"></a></li>';
    	outHTML += '<li><a href="javascript:void(0)"><img id="showstar' + msg_id + '-3" src="'+getStarByPR(3)+'" alt="" onClick="xajax_AddFav(\'favstar'+ msg_id +'\', \'fav' + msg_id + '\', ' + msg_id + ', ' + user + ', ' + om + ', 0, 3); HideFavFloat(' + msg_id + ', 3);"></a></li>';
//        outHTML += '</ul>';
    		
    	document.getElementById('FavFloat' + msg_id).innerHTML = outHTML;
//    } else {
    	document.getElementById('FavFloat' + msg_id).style.display = 'block';
//    }

	var show_star = '';

	// blogs main
	if (document.getElementById('favpriority' + msg_id)) {
		var show_star = document.getElementById('favpriority' + msg_id).value;
	}

	document.getElementById('showstar' + msg_id + '-0').src = getStarByPR(0);
	document.getElementById('showstar' + msg_id + '-1').src = getStarByPR(1);
	document.getElementById('showstar' + msg_id + '-2').src = getStarByPR(2);
	document.getElementById('showstar' + msg_id + '-3').src = getStarByPR(3);
//	document.getElementById('showstar' + msg_id + '-' + show_star).src = '/images/ico_star_' + show_star + '.gif';

	return true;
}

var currentOrder = false;
var currentOrderStr = 0;

function ShowFavOrderFloat()
{
	currentOrder = true;

	var outHTML = '';

	outHTML += '<ul style="width:90px"><li><a href="javascript:void(0)" onclick="xajax_SortFav(\'date\', 0); HideFavOrderFloat(0); return false;">дате</a></li>';
	outHTML += '<li><a href="javascript:void(0)" onclick="xajax_SortFav(\'priority\', 1); HideFavOrderFloat(1); return false;">важности</a></li>';
	outHTML += '<li><a href="javascript:void(0)" onclick="xajax_SortFav(\'abc\', 2); HideFavOrderFloat(2); return false;">алфавиту</a></li></ul>';

												
	document.getElementById('fav_order_float').innerHTML = outHTML;
	document.getElementById('fav_order_float').style.display = 'block';

	return true;
}

function ShowFavOrderFloatLenta()
{
	currentOrder = true;

	var outHTML = '';

	outHTML += '<ul style="width:90px"><li><a href="javascript:void(0)" onclick="xajax_Lenta_SortFav(\'date\', 0); HideFavOrderFloatLenta(0); return false;">дате</a></li>';
	outHTML += '<li><a href="javascript:void(0)" onclick="xajax_Lenta_SortFav(\'priority\', 1); HideFavOrderFloatLenta(1); return false;">важности</a></li>';
	outHTML += '<li><a href="javascript:void(0)" onclick="xajax_Lenta_SortFav(\'abc\', 2); HideFavOrderFloatLenta(2); return false;">алфавиту</a></li></ul>';

												
	document.getElementById('fav_order_float').innerHTML = outHTML;
	document.getElementById('fav_order_float').style.display = 'block';

	return true;
}

function HideFavFloat(msg_id, priority) {
	if (!msg_id && !currentOpen && currentLayer)
	{
		document.getElementById('FavFloat' + currentLayer).style.display = 'none';
		currentLayer = 0;
	}
	else if (msg_id)
	{
		document.getElementById('FavFloat' + msg_id).style.display = 'none';
	}

	// blogs inner
	if (document.getElementById('favpriority'))
	{
		document.getElementById('favpriority').innerHTML = priority;
	}

	currentOpen = false;

	return true;
}

function HideFavOrderFloat(order)
{
	var order_str = '';

	if (order == 0)	{order_now = "date";}
	if (order == 1)	{order_now = "priority";}
	if (order == 2)	{order_now = "abc";}
	if (!currentOrder)  {
		var fav_order = document.getElementById('fav_order');

		if (fav_order) {
			fav_order.innerHTML = '<div id="fav_order_float" style="display:none;position:absolute;top:2px;z-index:10"></div>';
			fav_order.innerHTML += '<a href="javascript:void(0)" onclick="ShowFavOrderFloat()">'+fav_orders[order_now]+'&nbsp;<img src="/images/ico_fav_arrow.gif" alt="" /></a>';
		}

		var fav_order_float = document.getElementById('fav_order_float');

		if (fav_order_float) {
			fav_order_float.innerHTML = '';
			fav_order_float.style.display = 'none';
		}
	}

	currentOrder = false;
	currentOrderStr = order;

	return true;
}

function HideFavOrderFloatLenta(order)
{
	var order_str = '';

	if (order == 0)	{order_now = "date";}
	if (order == 1)	{order_now = "priority";}
	if (order == 2)	{order_now = "abc";}
	if (!currentOrder)  {
		var fav_order = document.getElementById('fav_order');

		if (fav_order) {
			fav_order.innerHTML = '<div id="fav_order_float" style="display:none;position:absolute;top:2px;z-index:10"></div>';
			fav_order.innerHTML += '<a href="javascript:void(0)" onclick="ShowFavOrderFloatLenta()">'+fav_orders[order_now]+'&nbsp;<img src="/images/ico_fav_arrow.gif" alt="" /></a>';
		}

		var fav_order_float = document.getElementById('fav_order_float');

		if (fav_order_float) {
			fav_order_float.innerHTML = '';
			fav_order_float.style.display = 'none';
		}
	}

	currentOrder = false;
	currentOrderStr = order;

	return true;
}

function CommuneAddCategory() {
    if(!allow_add) return false;
    allow_add = false;
    xajax_AddCategory(xajax.getFormValues('commune_form_add_category'));
}

function CommuneCancelAddCategory() {
    allwo_add = true;
    $('commune_fld_add_category_name').set('value','');
    $('commune_fld_add_category_only_for_admin').set('checked',false);
    $('clp-crt').toggleClass('clp-crt-hide');
}

function CommuneEditCategory(id, comm_id) {
    //$('comm_span_cmd_'+id).setStyle('display','none');
    //$('comm_a_name_'+id).setStyle('display','none');
    //$('comm_div_edit_'+id).setStyle('display','block');
    xajax_EditCategory(id, comm_id);
}

function CommuneCancelEditCategory(id) {
    $('comm_span_cmd_'+id).setStyle('display','block');
    $('comm_a_name_'+id).setStyle('display','block');
    $('comm_div_edit_'+id).setStyle('display','none');
}

function CommuneUpdateCategory(id) {
    xajax_UpdateCategory(xajax.getFormValues('comm_form_edit_'+id));
}

function setRoleUser(id, member, is_moderator, is_manager) {
    xajax_setRoleUser(id, member, is_moderator, is_manager);        
}

function memberNoteForm(id) {
    if ($('ne1' + id).style.display != 'none') {
        $('ne1' + id).setStyle('display', 'none');
        $('ne2' + id).setStyle('display', '');
        $('ne3' + id).setStyle('display', 'none');
    } else {
        $('ne1' + id).setStyle('display', '');
        $('ne2' + id).setStyle('display', 'none');
        $('ne3' + id).setStyle('display', '');
    }
};

(function () {
    // **********************
    // Звездочки возле топика
    // **********************
    // реакция на добавление топика в закладки
    function favStar () {
        var param = this.id.match(/^fav_star_(\d+)_(\d+)_(\d+)_(\d+)$/);
        var msg_id = param[1]; // id сообщения
        var user_id = param[2]; // id пользователя
        var om = param[3];
        var fav = param[4]; // в закладках или нет
        var add = fav > 0 ? 0 : 1; // 1 - добавить в закладки, 0 - удалить из закладок
        xajax_AddFav('favstar' + msg_id, 'fav' + msg_id, msg_id, user_id, om, fav, add);
        if (fav > 0) {
            unlightStar($(this), msg_id, user_id, om, add);
            //$(this).set('class', 'b-post__star b-post__star_white');
            //$(this).set('id', 'fav_star_' + msg_id + '_' + user_id + '_' + om + '_' + add);
        } else {
            lightStar($(this), msg_id, user_id, om, add);
            //$(this).set('class', 'b-post__star b-post__star_yellow');
            //$(this).set('id', 'fav_star_' + msg_id + '_' + user_id + '_' + om + '_' + add);
        }
        var fav_count = $$('ul#favBlock li').length;
        // если удаляем последнюю закладку
        if (fav_count == 1 && fav == 1) {
            $('no_favs').setStyle('display', '');
            $('fav_order_menu').setStyle('display', 'none');
            $('favBlock').setStyle('display', 'none');
            $('favs_edit_edit').setStyle('display', 'none');
        };
        // если добавляем первую закладку
        if (fav_count == 0 && fav == 0) {
            if($('no_favs') != undefined) $('no_favs').setStyle('display', 'none');
            if($('fav_order_menu') != undefined) $('fav_order_menu').setStyle('display', '');
            if($('favBlock') != undefined) $('favBlock').setStyle('display', '');
            if($('favs_edit_edit') != undefined) $('favs_edit_edit').setStyle('display', '');
        };
    }
    
    /**
     * Зажечь звезду
     */
    function unlightStar (el, msg_id, user_id, om) {
        el.set('class', 'b-post__star b-post__star_white');
        el.set('id', 'fav_star_' + msg_id + '_' + user_id + '_' + om + '_0');
    }
    /**
     * Погасить звезду
     */
    function lightStar (el, msg_id, user_id, om) {
        el.set('class', 'b-post__star b-post__star_yellow');
        el.set('id', 'fav_star_' + msg_id + '_' + user_id + '_' + om + '_1');
    }
    
    // ***************************
    // ЗАКЛАДКИ
    // ***************************
    
    // режим редактирования закладок
    var isFavsEditMode = false;
    
    // включить режим редактирования
    function favsEditMode () {
        // скрыть кнопку РЕДАКТИРОВАТЬ и показать СОХРАНИТЬ и ОТМЕНА
        $('favs_edit_edit').setStyle('display', 'none');
        $('favs_edit_save').setStyle('display', '');
        // кнопки редактирования для каждой закладки
        $$('a[id^=favs_edit_fav]').setStyle('display', '');
        $$('a[id^=favs_delete_fav]').setStyle('display', '');
        isFavsEditMode = true;
    }
    // редактировать закладку
    function editFav () {
        $$('div[id^=favs_editor]').addClass('b-shadow_hide');
        $(this).getSiblings('div[id^=favs_editor]').removeClass('b-shadow_hide');
    }
    // закрыть редактор закладки
    function closeFavEditor () {
        $(this).getParent('div[id^=favs_editor]').addClass('b-shadow_hide');
    }
    // применить изменения в редакторе
    function favEditorSubmit () {
        var parentDiv = $(this).getParent('div[id^=favs_item]');
        var name = parentDiv.getElement('textarea[id^=favtext]').value;
        // проверка длины текста
        if (name.length > 128) {
            alert('Слишком длинное название закладки!');
            return false;
        }
        //item_name.set('text', name);
        var id = this.id.match(/fav_editor_submit(\d+)/)[1];
        // закрыть редактор
        $(this).getParent('div[id^=favs_editor]').addClass('b-shadow_hide');
        xajax_EditFav(id, 1, name, 'update');
    }
    // удалить закладку
    function deleteFav () {
        if (!confirm('Удалить закладку?')) return;
        var parentDiv = $(this).getParent('div[id^=favs_item]');
        var id = this.id.match(/favs_delete_fav(\d+)/)[1];
        var user_id = $('favs_user_id').get('value');
        var om = $('favs_om').get('value');
        xajax_AddFav('favstar' + id, 'fav' + id, id, user_id, om, 1);
        var star = $$('span[id^=fav_star_' + id + ']')[0];
        if (star) unlightStar(star, id, user_id, om);
    }
    // восстановить удаленную закладку
    function favRecover () {
        var id = this.id.match(/favs_recover_fav(\d+)/)[1];
        favChanges[id] = {};
        $('favs_item' + id).setStyle('display', '');
        $('favs_fav_deleted' + id).setStyle('display', 'none');
    }    
    // выйти из режима редактирования
    function favsEditCancel () {
        $('favs_edit_edit').setStyle('display', '');
        $('favs_edit_save').setStyle('display', 'none');
        // возвращаем удаленные разделы
        $$('div[id^=favs_fav_deleted]').setStyle('display', 'none');
        $$('div[id^=favs_item]').setStyle('display', '');
        // скрыть кнопки редактирования
        $$('a[id^=favs_edit_fav]').setStyle('display', 'none');
        $$('a[id^=favs_delete_fav]').setStyle('display', 'none');
        // восстанавливаем старые названия разделов (а также в редакторе)
        for(var id in favChanges) {
            change = favChanges[id];
            if (change.oldName) {
                $('favs_fav_name' + id).set('text', change.oldName);
                $('favtext' + id).set('text', change.oldName);
            }
        }
        favChanges = {};
        isFavsEditMode = false;
    }
    // закончить редактирование
    function favsEditFinish () {
        $('favs_edit_edit').setStyle('display', '');
        $('favs_edit_save').setStyle('display', 'none');
        // скрыть кнопки редактирования
        $$('a[id^=favs_edit_fav]').setStyle('display', 'none');
        $$('a[id^=favs_delete_fav]').setStyle('display', 'none');
        isFavsEditMode = false;
    }
    
    // сортировка
    function favsSortAbc () {
        var cid = null;
        if($(this).get('data-cid') != '') cid = $(this).get('data-cid');
        xajax_SortFav('abc', 2, cid);
        HideFavOrderFloat2(2)
    }
    function favsSortDate () {
        var cid = null;
        if($(this).get('data-cid') != '') cid = $(this).get('data-cid');
        xajax_SortFav('date', 0, cid);
        HideFavOrderFloat2(0);
    }
    function HideFavOrderFloat2 (order) {
        if (!currentOrder) {
            if (!$('fav_order_menu')) return;
            if (order == 0) {
                $$('li[id^=favs_abc_sorted]').set('style', 'display:none !important');
                $$('li[id^=favs_date_sorted]').set('style', '');
            } else {
                $$('li[id^=favs_date_sorted]').set('style', 'display:none !important');
                $$('li[id^=favs_abc_sorted]').set('style', '');
            }
        }
        return true;
    }
    
    // ***************************
    // РАЗДЕЛЫ
    // ***************************
    
    // изменения разделов во время редактирования
    var categoryChanges = {};
    var allowAddCategory = true;
    var addCategoryDefaultName = 'Название';
    // начать редактирование разделов
    function categoriesEdit () {
        // показать кнопки редактирования для каждого раздела
        $$('div[id^=comm_span_cmd_]').setStyle('display', '');
        // скрыть количество постов в разделах
        $$('div[id^=categories_themes_count]').setStyle('display', 'none');
        $('categories_themes_count_all').setStyle('display', 'none');
        // показать кнопки СОХРАНИТЬ и ОТМЕНА
        $('categories_edit_save').setStyle('display', '');
        $('categories_edit_cancel').setStyle('display', '');
        // скрыть кнопки РЕДАКТИРОВАТЬ и ДОБАВИТЬ
        $('categories_edit_edit').setStyle('display', 'none');
        $('categories_edit_add').setStyle('display', 'none');
        return false;
    }
    
    // добавить новый раздел
    function addCategory () {
        $('add_category_block').removeClass('b-shadow_hide');
        $('add_category_block').setStyle('display', '');
        if ( !$( "categories_edit_edit" ) || $( "categories_edit_edit" ).getStyle("display") == "none" ) {
            $('add_category_block').setStyle('left', '0px');
        } else {
            $('add_category_block').setStyle('left', null);
        }
        var ls = $('add_category_block').getElements('textarea');
        if (ls.length > 0) {
            ls[0].setProperty("style", "display:none");
        }
        return false;
    }
    
    function addCategorySubmit () {
        if(!allowAddCategory) return false;
        var textField = $('commune_fld_add_category_name');
        // проверка на длину символов до отправки
        var catName = textField.get('value');
        if (catName.length > 30 || catName.length == 0) {
            alert('Название раздела не может быть пустым и должно содержать не более 30 символов');
            return;
        }
        
        allowAddCategory = false;
        //xajax.call('AddCategory', {parameter: [xajax.getFormValues('commune_form_add_category')], onComplete: communeObj.initCategories});
        xajax_AddCategory(xajax.getFormValues('commune_form_add_category'));
        $('add_category_block').addClass('b-shadow_hide');
        textField.set('value', addCategoryDefaultName); // очищаем поле для ввода
    }
    
    // закончить добавление нового раздела
    function closeAddCategory () {
        $('add_category_block').addClass('b-shadow_hide');
        //$('add_category_block').setStyle('display', 'none');
        return false;
    }
    
    // очистить текст
    function clearText () {
        if ($(this).get('value') === addCategoryDefaultName) $(this).set('value', '');
    }
    
    // при нажатии на кнопку РЕДАКТИРОВАТЬ РАЗДЕЛ
    function editCategory () {
        $$('div[id^=category_editor]').addClass('b-shadow_hide');
        $(this).getParent().getElement('div[id^=category_editor]').removeClass('b-shadow_hide');
        var id = $(this).getParent().getElement('textarea').id;
        id = id.replace("commune_fld_edit_category_name_", "");
        $(this).getParent().getElement('textarea').value = $('category_name' + id).get('text');
    }
    
    // закрыть окно редактирования раздела
    function categoryEditClose () {
        $(this).getParent('div[id^=category_editor]').addClass('b-shadow_hide');        
    }
    
    // подтвержение изменений
    function categoryEditSubmit () {
        // основной родительский li
        var mainParentLi = $(this).getParent('li[id^=category_item]');
        // новое название раздела
        var newName = mainParentLi.getElement('textarea[id^=commune_fld_edit_category_name_]').value;
        // проверка длины нового названия
        var allow = "1234567890\t\n !.\"',.!@%^#$;:-<>";
        var str   = "abcdefghijklmnopqrstuvwxyzабвгдеёжзийклмнопрстуфхцчшщъыьэюя";
        allow += str + str.toUpperCase();
        var tS = "";
        for (var i = 0; i < newName.length; i++) {
            var ch = newName.charAt(i);
            if (allow.indexOf(ch) != -1) tS += ch;
        }
        if (tS.length > 30 || tS.length == 0) {        	
            alert('Название раздела не может быть пустым и должно содержать не более 30 символов');
            return;
        }
        var oldName = mainParentLi.getElement('a[id^=category_name]').get('text');
        mainParentLi.getElement('a[id^=category_name]').set('text', tS);
        var id = mainParentLi.getElement('div[id^=comm_span_cmd_]').id.match(/comm_span_cmd_(\d+)/)[1];
        // сохраняем изменения
        if (!categoryChanges[id]) {
            categoryChanges[id] = {};
            categoryChanges[id].oldName = oldName;
        }
        categoryChanges[id].action = 'edit';
        // скрываем окно редактирования
        mainParentLi.getElement('div[id^=category_editor]').addClass('b-shadow_hide');
    }
    
    // при нажатии на кнопку УДАЛИТЬ РАЗДЕЛ
    function delCategory () {
        if (confirm('Вы действительно хотите удалить раздел?')) {
            var id = this.id.match(/category_del_button(\d+)/)[1];
            categoryChanges[id] = {
                action: 'delete'
            }
            $('category_deleted' + id).setStyle('display', '');
            $('category_item' + id).setStyle('display', 'none');
        }
    }
    
    // восстановление удаленного раздела
    function categoryRecover () {
        var id = this.id.match(/category_recover(\d+)/)[1];
        categoryChanges[id] = {};
        $('category_deleted' + id).setStyle('display', 'none');
        $('category_item' + id).setStyle('display', '');
    }
    
    // сохраняем изменения
    function categoriesEditSave () {
        var change; // изменения для отдельно взятого пункта
        for(var id in categoryChanges) {
            change = categoryChanges[id];
            switch (change.action) {
                case 'edit':
                    //xajax.call('UpdateCategory', {parameter:[xajax.getFormValues('comm_form_edit_'+id)], onComplete: communeObj.initCategories});
                    xajax_UpdateCategory(xajax.getFormValues('comm_form_edit_'+id)); //communeObj.initCategories
                    break
                case 'delete':
                    var commune_id = $(document).getElement('input[name=commune_id]').value;
                    var om = $(document).getElement('input[name=om]').value;
                    xajax_DeleteCategory(id, commune_id, om);
                    break
            }            
        }
        // скрываем кнопки редактирования и показываем число постов в разделах
        $$('div[id^=comm_span_cmd_]').setStyle('display', 'none');
        $$('div[id^=categries_members_count]').setStyle('display', '');
        // показываем количество постов в разделах
        $$('div[id^=categories_themes_count]').setStyle('display', '');
        $('categories_themes_count_all').setStyle('display', '');
        // убираем кнопки СОХРАНИТЬ и ОТМЕНА
        $('categories_edit_save').setStyle('display', 'none');
        $('categories_edit_cancel').setStyle('display', 'none');
        // показываем кнопки РЕДАКТИРОВАТЬ и ДОБАВИТЬ
        $('categories_edit_edit').setStyle('display', '');
        $('categories_edit_add').setStyle('display', '');
        // стираем все записи об изменениях
        categoryChanges = {};
    }
    
    // отмена всех изменений
    function categoriesEditCancel () {
        // скрываем все блоки ВОССТАНОВИТЬ
        $$('li[id^=category_deleted]').setStyle('display', 'none');
        $$('li[id^=category_item]').setStyle('display', '');
        // восстанавливаем старые названия разделов (а также в редакторе)
        var change;
        for(var id in categoryChanges) {
            change = categoryChanges[id];
            if (change.oldName) {
                $('category_name' + id).set('text', change.oldName);
                $('commune_fld_edit_category_name_' + id).set('text', change.oldName);
            }
        }
        // скрываем кнопки редактирования и показываем число постов в разделах
        $$('div[id^=comm_span_cmd_]').setStyle('display', 'none');
        $$('div[id^=categries_members_count]').setStyle('display', '');
        // показываем количество постов в разделах
        $$('div[id^=categories_themes_count]').setStyle('display', '');
        $('categories_themes_count_all').setStyle('display', '');
        // убираем кнопки СОХРАНИТЬ и ОТМЕНА
        $('categories_edit_save').setStyle('display', 'none');
        $('categories_edit_cancel').setStyle('display', 'none');
        // показываем кнопки РЕДАКТИРОВАТЬ и ДОБАВИТЬ
        $('categories_edit_edit').setStyle('display', '');
        $('categories_edit_add').setStyle('display', '');
            
        // стираем все записи об изменениях
        categoryChanges = {};
    }
    
    // подключение событий
    window.addEvent('domready', function() {
        // показать кнопки редактирования разделов
        if ($('categories_edit')) $('categories_edit').addEvent('click', categoriesEdit);
        // добавить новый раздел
        if ($('add_category')) $('add_category').addEvent('click', addCategory);
        // подтвердить добавление категории
        if ($('category_add_submit')) $('category_add_submit').addEvent('click', addCategorySubmit);
        // сохранить все изменения с разделами
        if ($('categories_edit_save')) $('categories_edit_save').addEvent('click', categoriesEditSave);
        // отменить все изменения с разделами
        if ($('categories_edit_cancel')) $('categories_edit_cancel').addEvent('click', categoriesEditCancel);
        // закрыть окно добавления раздела
        if ($('close_add_category')) $('close_add_category').addEvent('click', closeAddCategory);
        // очистка текста в поле для ввода при создании раздела
        if ($('commune_fld_add_category_name')) $('commune_fld_add_category_name').addEvent('focus', clearText);
        
        // добавление поста в закладки
        $$('span[id^=fav_star_]').addEvent('click', favStar);
        
        // включить режим редактирования
        if ($('favs_edit_edit_btn')) $('favs_edit_edit_btn').addEvent('click', favsEditMode);
        // закончить редактирование
        if ($('favs_edit_save_btn')) $('favs_edit_save_btn').addEvent('click', favsEditFinish);
        // сортировка
        if ($('favs_sort_abc')) $('favs_sort_abc').addEvent('click', favsSortAbc);
        if ($('favs_sort_date')) $('favs_sort_date').addEvent('click', favsSortDate);
        communeObj.initFavs();
        communeObj.initComments();
    })
    
    communeObj = {
        // РАЗДЕЛЫ *************************
        initCategories: function () {
            var temp;
            allowAddCategory = true;
            // кнопка РЕДАКТИРОВАТЬ РАЗДЕЛ
            $$('a[id^=category_edit_button]').addEvent('click', editCategory);
            // кнопка УДАЛИТЬ РАЗДЕЛ
            $$('a[id^=category_del_button]').addEvent('click', delCategory);
            // закрыть окно редактирования раздела
            $$('span[id^=category_edit_close]').addEvent('click', categoryEditClose);
            // подтвердить редактирование раздела
            $$('a[id^=category_edit_submit]').addEvent('click', categoryEditSubmit);
            // восстановить удаленный раздел
            $$('a[id^=category_recover]').addEvent('click', categoryRecover);
            // надпись в поле для ввода названия нового раздела
            if (temp = $('commune_fld_add_category_name')) temp.set('value', addCategoryDefaultName);
            // показать или скрыть кнопку РЕДАКТИРОВАТЬ
            if ($$('li[id^=category_item]').length > 0) {
                if (temp = $('categories_edit_edit')) temp.setStyle('display', 'inline');
            } else {
                if (temp = $('categories_edit_edit')) temp.setStyle('display', 'none');
            }
        },
        // ЗАКЛАДКИ *************************
        initFavs: function () {
            var temp;
            // кнопка РЕДАКТИРОВАТЬ ЗАКЛАДКУ
            $$('a[id^=favs_edit_fav]').addEvent('click', editFav);
            // кнопка УДАЛИТЬ ЗАКЛАДКУ
            $$('a[id^=favs_delete_fav]').addEvent('click', deleteFav);
            // кнопка ЗАКРЫТЬ РЕДАКТОР
            $$('span[id^=favs_close_editor]').addEvent('click', closeFavEditor);
            // кнопка ИЗМЕНИТЬ ЗАКЛАДКУ
            $$('a[id^=fav_editor_submit]').addEvent('click', favEditorSubmit);
            // кнопка восстановить закладку
            $$('a[id^=favs_recover_fav]').addEvent('click', favRecover);
            if (isFavsEditMode) {
                favsEditMode();
            }
            // если нет закладок
            if ($$('div[id^=favs_item]').length === 0) {
                if (temp = $('no_favs')) temp.setStyle('display', '');
                if (temp = $('fav_order_menu')) temp.setStyle('display', 'none');
                if (temp = $('favBlock')) temp.setStyle('display', 'none');
                if (temp = $('favs_edit_edit')) temp.setStyle('display', 'none');
            }
        },
        
        // КОММЕНТАРИИ ******************************
        initComments: function () {
            // проверяем, что в наличии имеются комментарии
            if ($$('.comment-list').length !== 1) return;
            // подсвечиваем непрочитанные комменты
            $$('.comment-list')[0].getElements('li.cl-li').each(function(li){
                if (li.getChildren('a[name=unread]').length) {
                    li.getChildren('div.b-post')[0].getChildren('div.b-post__body')[0].addClass('b-fon__body_bg_f0ffdf');
                }
            })
            
        }
    }
    
    
})();
/**
* Отметить пост удаленным
* @param String postId              идентификатор блока
* @param String moderatorLogin      логин удалившего
* @param String moderatorName       имя удалившего
* @param String moderatorSurname    фамилия удалившего
* @param String date                дата удаления
* @param String time                время удаления
**/
function commune_markPostAsDeleted (postId, moderatorLogin, moderatorName, moderatorSurname, date, time ) {
    var modInfo = new Element('div', {'class':'b-post__moderator_info', html:'<span class="b-post__moderator_info_red">Удалнено модератором [' + moderatorLogin + '] ' + moderatorName + ' ' + moderatorSurname + '</span> <span class="b-post__moderator_info_gray">[' + date + ' | ' + time + ']</span>'});
    var o = $(postId).getElement('div.b-post__txt');
    o.addClass('b-post__deleted_txt');
    modInfo.inject(o, 'before');
    $(postId).getElements("a.b-post__link").each(
        function (a) {
            if (a.get("text").trim() == "Удалить") {
                a.set("onclick", a.get("onclick").replace("__commDT", "xajax_restoreDeletedPost"));
                a.set("text", "Восстановить");
            }
        }
    );

    $(postId).getElement("div.b-post__content").getElements("div").each(
        function (d) {
            if (String(d.get("id")).indexOf("poll-") == 0) {
                d.addClass('b-post__deleted_txt');
            }
        }
    );
}
/**
* Восстановить удаленный пост
* @param String divId               идентификатор блока
* @param String msgId               идентификатор записи в базе данных
* @param String uid                 идентификатор пользователя
* @param String mod                 режим
* @param String page                номер страницы
* @param String om                  нужен для прохода по страницам, возвратам, переходам
* @param String site                $site==NULL|'Commune' -- топик выводится на странице сообщества (/commune/),
*                                   $site=='Topic' -- на странице комментариев (/commune/?site=Topic),
*                                   $site=='Lenta' -- в ленте (/lenta/).
* @param String isFav               находится в закладка пользователя $user_id или нет.
**/
function commune_RestoreMessage(divId, msgId, uid, mod, page, om, site, isFav) {
    var o = $(divId).getElement('div.b-post__txt');
    o.removeClass('b-post__deleted_txt');
    $(divId).getElement("div.b-post__moderator_info").dispose();
    $(divId).getElements("a.b-post__link").each(
        function (a) {
            if (a.get("text").trim() == "Восстановить") {
                a.set("onclick", a.get("onclick").replace("xajax_restoreDeletedPost", "__commDT"));
                a.set("text", "Удалить");
            }
        }
    );
    $(divId).getElement("div.b-post__content").getElements("div").each(
        function (d) {
            if (String(d.get("id")).indexOf("poll-") == 0) {
                d.removeClass('b-post__deleted_txt');
            }
        }
    );
}

window.addEvent('domready', function(){
    communeObj.initCategories();
    
    if (typeof commune_config !=="undefined") {    
        poll.init('Commune', document.getElementById(commune_config.poll_id), commune_config.poll_max, commune_config.session);
        if (document.getElementById('question')) maxChars('question', 'polls_error', commune_config.question_max_char);

        if (commune_config.new_post_id && commune_config.new_post_om) {
            if($('new_post_msg') != undefined) {
                $('new_post_msg').addEvent('click', function() { 
                    xajax_commPrntCommentForm(commune_config.new_post_id, commune_config.new_post_om);
                });
            }
        }
    }
    
    if (typeof commune_attached !== "undefined") {
        attachedFiles.initComm(
            'attachedfiles', 
            commune_attached.sess,
            commune_attached.list,
            commune_attached.max_files,
            commune_attached.max_file_size,
            commune_attached.disallowed,
            'commune',
            commune_attached.uid
        );
    }
});