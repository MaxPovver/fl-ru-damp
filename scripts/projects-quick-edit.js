var curFBulletsBox = 2;
var popupQEditIsProcess = false;

function popupQEditPrjRefreshSubCategory(ele, without_sa)
{
   var category = ele.value;
   var div = ele.parentNode;
   var objSel = $(div.getElementsByTagName('select')[1]);
  if(typeof without_sa == 'undefined') without_sa = false;
  
  objSel.options.length = 0;
  objSel.disabled = 'disabled';
  var ft = true;
  if(!without_sa){
      if (curFBulletsBox == 2){
        objSel.options[objSel.options.length] = new Option('¬се специализации', 0, ft, ft);
        ft = false;
      } else {
        objSel.options[objSel.options.length] = new Option('¬ыберите подраздел', 0);
      }
  }
  if(category == 0) {
      objSel.set('disabled', true);
  } else {
      objSel.set('disabled', false);
  }
  
  for (i in qprj_filter_specs[category]) {
    if (qprj_filter_specs[category][i][0]) {
        objSel.options[objSel.options.length] = new Option(qprj_filter_specs[category][i][1], qprj_filter_specs[category][i][0], ft, ft);
        ft = false;
    }
  }
  if(!without_sa){
        if (curFBulletsBox == 2){
            objSel.set('value','-1');
            objSel.options[0].selected = true;
        } else {
            objSel.set('value','0');
        }
    }
    
}

function popupQEditPrjReset() {
    popupQEditPrjHideErrors();
    popupQEditIsProcess = false;
}

function popupQEditPrjUploadLogoError(error) {
    $('popup_qedit_prj_fld_err_pay').setStyle('display','block');
    $('popup_qedit_prj_fld_err_pay_txt').set('html', error);
}

function popupQEditPrjDelLogoOk() {
    $("popup_qedit_prj_logolink").set("value", "")
    $("popup_qedit_prj_use_logo").set("checked", false);
    $("popup_qedit_prj_use_logo").set("disabled", false);
    $("popup_qedit_prj_use_logo_tab").setStyle("display", "none");
    $("popup_qedit_prj_use_logo_tab2").setStyle("display", "none");
}

function popupQEditPrjUploadLogoOk(logourl) {
    $("popup_qedit_prj_use_logo_src").set("href", logourl);
    $("popup_qedit_prj_use_logo").set("checked", true);
    $("popup_qedit_prj_use_logo").set("disabled", true);
    $("popup_qedit_prj_use_logo_tab").setStyle("display", "block");
    $("popup_qedit_prj_use_logo_tab2").setStyle("display", "none");

}

function popupQEditPrjUploadLogo() {
    $('popup_qedit_prj_fld_err_pay').setStyle('display','none');
    $('popup_qedit_prj_fld_tmpaction').set('value','upload');
    var action = $('popup_qedit_prj_frm').get('action');
    $('popup_qedit_prj_frm').set('action','/projects-quick-edit-upload.php');
    $('popup_qedit_prj_frm').set('target','popup_qedit_prj_upload_logo');
    $('popup_qedit_prj_frm').submit();
    $('popup_qedit_prj_frm').set('target','');
    $('popup_qedit_prj_frm').set('action',action);
}

function popupQEditPrjDelLogo() {
    $('popup_qedit_prj_fld_tmpaction').set('value','del');
    var action = $('popup_qedit_prj_frm').get('action');
    $('popup_qedit_prj_frm').set('action','/projects-quick-edit-upload.php');
    $('popup_qedit_prj_frm').set('target','popup_qedit_prj_upload_logo');
    $('popup_qedit_prj_frm').submit();
    $('popup_qedit_prj_frm').set('target','');
    $('popup_qedit_prj_frm').set('action',action);
}

function popupQEditPrjToggleUseLogo() {
    var o = $('popup_qedit_prj_use_logo');
    if(o.get('checked')==true) { 
        $('popup_qedit_prj_use_logo_tab2').setStyle('display', 'block'); 
    } else {
        $('popup_qedit_prj_use_logo_tab2').setStyle('display', 'none');
    }
}

function popupQEditPrjToggleIsOntop() {
    var o = $('popup_qedit_prj_top_ok');
    if(o.get('checked')==true) { 
        $('popup_qedit_prj_top_ok_tab1').setStyle('display', 'block'); 
    } else {
        $('popup_qedit_prj_top_ok_tab1').setStyle('display', 'none');
    }
}

function popupQEditPrjToggleIsColor() {
    var o = $('popup_qedit_prj_is_color');
    if(o.get('checked')==true){o.getNext('.b-check__label').addClass('b-check__label_bg_fffdd7');}
    else{o.getNext('.b-check__label').removeClass('b-check__label_bg_fffdd7');};
}

function popupQEditPrjToggleIsBold() {
        var o = $('popup_qedit_prj_is_bold');
        if(o.get('checked')==true){o.getNext('.b-check__label').getChildren('span').addClass('b-check__bold');}
        else{o.getNext('.b-check__label').getChildren('span').removeClass('b-check__bold');};    
}

function popupQEditPrjHideErrors() {
    $('popup_qedit_prj_fld_err_name').setStyle('display', 'none');
    $('popup_qedit_prj_fld_err_descr').setStyle('display', 'none');
    $('popup_qedit_prj_fld_err_categories').setStyle('display', 'none');
    $('popup_qedit_prj_fld_err_location').setStyle('display', 'none');
    $('popup_qedit_prj_fld_err_pay').setStyle('display', 'none');
    $('popup_qedit_prj_fld_err_cal1').setStyle('display', 'none');
    $('popup_qedit_prj_fld_err_cal2').setStyle('display', 'none');
}

function popupQEditPrjShowError(fld) {
    $('popup_qedit_prj_fld_err_'+fld).setStyle('display', 'block');
}

function popupQEditPrjCityUpd(v){
  ct = document.getElementById("popup_qedit_prj_frm").city;
  ct.disabled = true;
  ct.options[0].innerHTML = "ѕодождите...";
  ct.value = 0;
  xajax_GetCitysByCid(v);
}

function popupQEditPrjChangeKind() {
    if($("popup_qedit_prj_fld_kind_2").get("checked")) {
        $("popup_qedit_prj_fld_location").setStyle("display", "block");
    } else {
        $("popup_qedit_prj_fld_location").setStyle("display", "none");
    }
}
/**
 * @param prj_id   - идентификатор проекта 
 * */
function popupQEditPrjShow(prj_id, event, tape) {
    if (event == undefined){
    	if($('project-item'+prj_id)) {
    		var el1 = $('project-item'+prj_id);
    		var el2 = $('popup_qedit_prj_div');
    		el2.inject(el1, 'before');
    	}
    } else {
    	var h = window.innerHeight;
        if (!h && document.documentElement && document.documentElement.clientHeight) {
            h = document.documentElement.clientHeight;
        } else if (!h) {
            h = document.getElementsByTagName('body')[0].clientHeight;
        }
        var oH = $('popup_qedit_prj_div').offsetTop;
        var oHRightBlock = 0;
        if ( tape ) {
            if ( $$( "div.b-layout__right_relative" ).length ) {
            	oHRightBlock = $$( "div.b-layout__right_relative" )[0].offsetTop;
            }
        }
        var y = ( h - parseInt($('popup_qedit_prj').getStyle("height")) ) / 2 - oH - oHRightBlock;
        y = (document.documentElement.scrollTop || document.body.scrollTop) + y;
    	$('popup_qedit_prj').setStyle("top", y + 'px');
    }
    popupQEditPrjReset();
    $('popup_qedit_prj_fld_id').set('value', prj_id);
    $('popup_qedit_prj').toggleClass('b-shadow_hide');
    xajax_quickprjedit_get_prj(prj_id);
}

function popupQEditPrjHide() {
    f_tcalHideAll();
    $('popup_qedit_prj').toggleClass('b-shadow_hide');
    popupQEditIsProcess = false;
}
/**
 * @param type - ??
 * @param pageType - тип страницы, с которой была запрошена форма быстрого редактировани€ проекта
 *                    "main" - лента на главной, "emplist" - список на странице профил€ работодател€, "project" - страница проекта в профиле работодател€
 * */
function popupQEditPrjSave(type, pageType) {
    if(popupQEditIsProcess==false) { xajax_quickprjedit_save_prj(xajax.getFormValues('popup_qedit_prj_frm'), type); }
    popupQEditIsProcess = true;

}

function popupQEditPrjDel() {
    popupQEditPrjHide();
}

function popupQEditPrjMenu(active) {
    if(active==1) {
        $('popup_qedit_prj_tab_i2').removeClass('b-menu__item_active');
        $('popup_qedit_prj_tab_i2').set('html', '<a class="b-menu__link" href="#" onClick="popupQEditPrjMenu(2); return false;">ѕлатные услуги</a>');
        $('popup_qedit_prj_tab_i1').addClass('b-menu__item_active');
        $('popup_qedit_prj_tab_i1').set('html', '<span class="b-menu__b1"><span class="b-menu__b2">ќсновные</span></span>');
        $('popup_qedit_prj_tab_payed').setStyle('display', 'none');
        $('popup_qedit_prj_tab_main').setStyle('display', 'block');
    } else {
        $('popup_qedit_prj_tab_i1').removeClass('b-menu__item_active');
        $('popup_qedit_prj_tab_i1').set('html', '<a class="b-menu__link" href="#" onClick="popupQEditPrjMenu(1); return false;">ќсновные</a>');
        $('popup_qedit_prj_tab_i2').addClass('b-menu__item_active');
        $('popup_qedit_prj_tab_i2').set('html', '<span class="b-menu__b1"><span class="b-menu__b2">ѕлатные услуги</span></span>');
        $('popup_qedit_prj_tab_payed').setStyle('display', 'block');
        $('popup_qedit_prj_tab_main').setStyle('display', 'none');
    }
}

var popup_budget_prj_id = '';
var popup_budget_prj_type = '';
var popup_budget_page_type = '';
/**
 * @param prj_id       - идентификатор проекта
 * @param price        - стоимость
 * @param currency     - валюта
 * @param costby       - за что
 * @param agreement    - true если по договоренности
 * @param name_id      - идентификатор наименовани€ проекта
 * @param from_type    - тип формы (1 - форма с главной страницы или страницы списка проектов пользовател€, 2 - форма с подробной страницы проекта)
 * @param page_type    - имеет смысл при from_type == 2. 1 - признак того, что форма отправлена со страницы списка проектов пользовател€ , а не списка проектов на главной
 * */ 
function popupShowChangeBudget(prj_id, price, currency, costby, agreement, name_id, from_type, page_type) {
    $('popup_budget_prj_name').set('html', 'ѕроект Ђ'+$('prj_name_'+name_id).get('html')+'ї');
    $('popup_budget_prj_price').set('value', price);
    var currency2 = projQuickEditCurrency[currency] || projQuickEditCurrency[0];
    $('popup_budget_prj_currency').set('value', currency2);
    $('popup_budget_prj_currency_db_id') && $('popup_budget_prj_currency_db_id').set('value', currency);
    var costby2 = projQuickEditCostby[costby] || projQuickEditCostby[1];
    $('popup_budget_prj_costby').set('value', costby2);
    $('popup_budget_prj_costby_db_id') && $('popup_budget_prj_costby_db_id').set('value', costby);
    $('popup_budget_prj_price_error').setStyle('display', 'none');
    $('popup_budget_prj_agreement').set('checked', agreement);
    popupDisableFieldsBudget();
    popup_budget_prj_id = prj_id;
    popup_budget_prj_type = from_type;
    popup_budget_page_type = page_type;
    $('popupBtnSaveBudget').addEvent('click', function() { popupSaveBudget(); return false; });
    if (from_type == 2) {
    	$('popup_budget').setStyle("width", "345px");
    }
    $('popup_budget').removeClass('b-shadow_hide');
}

function popupHideChangeBudget() {
    $('popup_budget').addClass('b-shadow_hide');
}

function popupDisableFieldsBudget() {
	if($('popup_budget_prj_agreement').get('checked')==true) {
	    $$('#popup_budget_prj_price', '#popup_budget_prj_currency','#popup_budget_prj_costby').each(function(el){
            el.getParent('.b-combo__input').addClass('b-combo__input_disabled');
            el.set('disabled', true);
        });
	} else {
	     $$('#popup_budget_prj_price', '#popup_budget_prj_currency','#popup_budget_prj_costby').each(function(el){
            el.getParent('.b-combo__input').removeClass('b-combo__input_disabled');
            el.set('disabled', false);
        });
	}
}

function popupSaveBudget() {
    var error = 0;
    var prj_id = popup_budget_prj_id;
    var type = popup_budget_prj_type;
    var page_type = popup_budget_page_type;
    if($('popup_budget_prj_agreement').get('checked')==false) {
        if(!( parseInt( $('popup_budget_prj_price').get('value') )+0 >=0 && parseInt( $('popup_budget_prj_price').get('value') )+0 == $('popup_budget_prj_price').get('value'))) {
            error = 1;
            $('popup_budget_prj_price_error').setStyle('display', 'block');
        }
    }
    if(!error) {
        var form = {};
        form.cost = $('popup_budget_prj_price').get('value');
        form.currency = $('popup_budget_prj_currency_db_id').get('value');
        form.costby = $('popup_budget_prj_costby_db_id').get('value');
        form.agreement = $('popup_budget_prj_agreement').get('checked');
        xajax_quickprjedit_save_budget(prj_id, form, type, page_type);
        
        var a = $('prj_budget_lnk_' + prj_id);
        a.onclick = function (){
            //prj_id, price, currency, type, agreement, name_id, from_type
            popupShowChangeBudget(	prj_id, 
                                    form.cost,
                                    projQuickEditCurrency[form.currency],
                                    projQuickEditCostby[form.costby],
                                    form.agreement,
                                    prj_id,
                                    type,
                                    page_type
                                );
            return false;
        }
		
        popupHideChangeBudget();
    }
}

function popup(popupid) {
	     
}

// валюта
var projQuickEditCurrency = {
    0: 'USD',
    1: '≈вро',
    2: '–уб'
};
var projQuickEditCostby = {
    1: 'за час',
    2: 'за день',
    3: 'за мес€ц',
    4: 'за проект'
};

window.addEvent('domready', function() {
    popup('popup_qedit_prj');
    popup('popup_budget');
    if($('popup_budget_prj_agreement')) {
        $('popup_budget_prj_agreement').addEvent('click',function(){ popupDisableFieldsBudget(); });
    }
    if($('popup_qedit_prj')) {
        new tcal ({ 'formname': 'popup_qedit_prj_frm', 'controlname': 'popup_qedit_prj_fld_end_date', 'iconId': 'end_date_btn', 'clickEvent': function(){  } });
        new tcal ({ 'formname': 'popup_qedit_prj_frm', 'controlname': 'popup_qedit_prj_fld_win_date', 'iconId': 'win_date_btn', 'clickEvent': function(){  } });
    }
});

