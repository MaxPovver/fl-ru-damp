function clearErrorBlock(obj, cls) {
    if(cls == undefined) cls = 'b-layout__middle';
    var error = $(obj).getParent('.' + cls).getElement('.errorBox');
    if(error != undefined) {
        error.dispose();
    }
}

function clearErrorPrjBlock(obj, fname) {
    if ( $(obj).getParent('.b-combo__input') != undefined ) {
        $(obj).getParent('.b-combo__input').removeClass('b-combo__input_error');
    }
    if ( $(obj).getParent('.b-textarea') != undefined ) {
        $(obj).getParent('.b-textarea').removeClass('b-textarea_error');
    }
    var errFieldName = $('errPrjField_' + (fname? fname: $(obj).name));
    if ( $(errFieldName) ) {
        $(errFieldName).dispose();
    }
}

function init_event_buttons() {
    $$(".b-button_toggle").removeEvents("click");
    $$(".b-button_toggle").removeEvents("mousedown");
    $$(".b-button_toggle").removeEvents("mouseup");
    $$(".b-button_toggle").removeEvents("mouseleave");

    $$(".b-button_toggle").addEvent("mousedown", function() {
        if(!this.hasClass("b-button_active")) {
            $$(".b-button_toggle").removeClass("b-button_active");
        } 
        this.addClass("b-button_active");
    });
    
    $('in_office').addEvent('click', function() {
        if(this.checked == true) {
            setInOffice();
        } else {
            setInProject();
        }
    });
    
    $$(".location-addbtn").addEvent("click", function() {
        var title = '<div class="b-layout__txt b-layout__txt_lineheight_13">Офис находится в</div>';
        $('block_location').getElement('.location-title').set("html", title);
        
        var content = $('block_location').getElement('.location-content');
        ComboboxManager.prepend(content, 'b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_arrow_yes b-combo__input_resize \n\
                                    b-combo__input_on_load_request_id_getcountries b-combo__input_on_click_request_id_getcities\n\
                                    drop_down_default_0 multi_drop_down_default_column_0', 'location');
        $(this).getParent().dispose();
    });
    
    $$(".paid-option").addEvent("click", function(){
        var opt = $('paid_option'); // блок с платными опциями
        var screenShot = $('screen_shot'); // блок со скриншотом
        opt.set('morph', {duration: 1000});
        screenShot.set('morph', {duration: 1000});
        var paidOptHeight = $('paid_option_inner').getSize().y; // высота блока с опциями
        
        if(opt.getSize().y === 0) {
            opt.set('morph', {
                onComplete: function(){opt.setStyle('height', '')}
            })
            opt.morph({'height': paidOptHeight});
            screenShot.morph({'top': 30});
        } else {
            opt.get('morph').$events.complete = []; // удаляем обработчик собыития onComplete
            opt.morph({'height': 0});
            screenShot.morph({'top': -110});
        }
    });
    
    $('agreement').addEvent('click', function(){
        if(this.checked == true) {
            $('f3').setProperty('readonly', 'true');
            $('currency').setProperty('readonly', 'true');
            $('priceby').setProperty('readonly', 'true');
            $('f3').setProperty('disabled', 'true');
            $('currency').setProperty('disabled', 'true');
            $('priceby').setProperty('disabled', 'true');
            
            $('f3').getParent().addClass('b-combo__input_disabled');
            $('currency').getParent().addClass('b-combo__input_disabled');
            $('priceby').getParent().addClass('b-combo__input_disabled');
            
            $$('.budget-select').addClass('budget-disabled');
            $$('.budget-pointer-road').hide();
        } else {
            $('f3').setProperty('readonly', null);
            $('currency').setProperty('readonly', null);
            $('priceby').setProperty('readonly', null);
            $('f3').removeProperty('disabled');
            $('currency').removeProperty('disabled');
            $('priceby').removeProperty('disabled');
            
            $('f3').getParent().removeClass('b-combo__input_disabled');
            $('currency').getParent().removeClass('b-combo__input_disabled');
            $('priceby').getParent().removeClass('b-combo__input_disabled');
            
            $$('.budget-select').removeClass('budget-disabled');
            $$('.budget-pointer-road').show();
        }
    });
}  

function setLogo(obj) {
    if(obj.checked == true) {
        $$('.logo-element').show();
        if($('logo_block').getElement('.logo-img') != undefined) {
            $$('.logo-add-element').hide();
        }
    } else {
        $$('.logo-element').hide();
    }
}

function deleteLogo(id) {
    $$('.logo-add-element').show();
    $('logo_block').set('html', '');
    $('prj-logoimage-block').addClass('b-button_rectangle_color_transparent');
    $('prj-logoimage-block').getElement('.b-button__txt').set('html', 'Прикрепить файл');
    xajax_deleteLogo(id);
}

function deleteLogoCompany(id) {
    $$('.logo-add-element').show();
    $('logo_block').set('html', '');
}

function addIMGLogoCompany(link, id, pictname) {
    $$('.logo-add-element').hide();
    
    var html = '<div class="b-layout__txt b-layout__txt_relative b-layout__txt_inline-block">';
    html += '<a href="javascript:void(0)" class="b-button b-button_bgcolor_fff b-button_bord_solid_3_fff b-button_admin_del b-button_right_-4 b-button_top_-6" onclick="deleteLogoCompany(' + id + ');"></a>';
    html += '<a href="' + link + '" class="b-layout__link">';
    html += '<img alt="" id="img_logo" src="' + link + '" class="b-layout__pic b-layout__pic_bord_ece9e9">';
    html += '</a></div>';
    
    $('logo_name').set('value', pictname);
    $('logo_company').set('value', id);
    $('logo_block').set('html', html);	
}

function addIMGLogo(link, id) {
    $$('.logo-add-element').hide();
    
    var html = '<div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_padleft_18">';
    html += '<div class="b-layout__txt b-layout__txt_relative b-layout__txt_inline-block">';
    html += '<a class="b-button b-button_bgcolor_fff b-button_bord_solid_3_fff b-button_admin_del b-button_right_-4 b-button_top_-6" href="javascript:void(0)" onclick="deleteLogo(' + id + ');"></a>';
	html += '<a class="b-layout__link" href="'+link+'">';					
	html += '<img class="b-layout__pic b-layout__pic_bord_ece9e9" src="' + link + '" alt="" />';
    html += '</a>'; 
	html += '</div>';						
	html += '</div>';
    
    if($('logo_id')) {
        $('logo_id').dispose();
    }
    var inp = new Element('input', {'name':'logo_id', 'value':id, 'type':'hidden', 'id':'logo_id'});
    $('frm').grab(inp);
    
    $('logo_block').set('html', html);	
    $('logo_block_link').show();
}

function uploadLogoFile(type) {
    if(type == undefined) type = 'logo';
    var form = $('frm');
    
    form.setProperty('action', '/wizard/upload.php?type='+type);
    form.setProperty('target', 'fupload');
    
    $$('#prj-logoimage-block').removeClass('b-button_rectangle_color_transparent');
    $$('#prj-logoimage-block').getElement('.b-button__txt').set('html', '<img src="/images/loader-2.gif" alt="" border="0">');
    
    form.submit();
    
    form.setProperty('action', null);
    form.setProperty('target', null);
}

function UploadLogoFileError(message) {
    $$('#prj-logoimage-block').addClass('b-button_rectangle_color_transparent');
    $$('#prj-logoimage-block').getElement('.b-button__txt').set('html', 'Прикрепить файл');
    alert(message);
}

function setInOffice() {
    $('condition_descr').set("html", "Требования, обязанности, условия");
    $('name_of_payment').set("html", "Зарплата");
    $('block_location').removeClass("b-layout_hide");
}

function setInProject() {
    $('condition_descr').set("html", "Задание");
    $('name_of_payment').set("html", "Бюджет");
    $('block_location').addClass("b-layout_hide");
}

function setTypeProject(type) {
    if(type == 1) {
        $('in_office').setProperty("checked", null);
        setInProject();
        
        $('ntop1').setProperty('price', cTopPrice);
        $('ntop2').setProperty('price', cTopPrice);
        $('ntop2').fireEvent('keyup');
        
        $('kind').setProperty('value', 'contest');
        
        $$('.project-elm').addClass('b-layout_hide');
        $$('.contest-elm').removeClass('b-layout_hide');
    } else {
        $('ntop1').setProperty('price', pTopPrice);
        $('ntop2').setProperty('price', pTopPrice);
        $('ntop2').fireEvent('keyup');
        
        $('kind').setProperty('value', 'project');
        
        $$('.project-elm').removeClass('b-layout_hide');
        $$('.contest-elm').addClass('b-layout_hide');
    }
}

var currency_data = {
    "-1":"Выберите валюту", 
    2: "Руб", 
    0:"USD", 
    1:"Евро"
};
var cost_data = {
    "-1":"Выберите из списка", 
    1: "цена за час", 
    2: "цена за день",
    3: "цена за месяц",
    4: "цена за проект"
};

var isBudgetSliderChangePrice = 0;
var category = 0;

function saveCatValue() {
    if($('category_column_id').get('value') == 0) {
        category = $('category_db_id').get('value');
        $('h_category').set('value', category);
    } else {
        $('h_subcategory').set('value', $('category_db_id').get('value'));
    }
}

function saveChangeSingleValue(name) {
    $('r_' + name).set('value', $(name + '_db_id').get('value'));
}
// --- Слайдер
function chkcost(_th) {
    _th.value=_th.value.replace(/^[0\D]+/g,'').replace(/[^\d\.]+/g,'').replace(/\..*$/,'');
    if(!($('currency_db_id').value==-1 || $('priceby_db_id').value==-1) && CheckCatsAndSubCats()) {changeBudgetSlider();}
}

function CheckCatsAndSubCats() {
    //var column   = $$(".b-combo_category").getElement('#category_column_id').get('value');
    var category = $('category_db_id').get('value');
    
    if(category == "") {
        return false;
    }
    return true;
}

function changeBudgetSlider() {
    if (!$('currency_db_id') || !$('priceby_db_id')) return; //вызов произошел до игициализации комбобоксов
    if($("f3").get('value')!='' && !($('currency_db_id').value==-1 || $('priceby_db_id').value==-1) && CheckCatsAndSubCats()) {
        var price = $("f3").get('value');
        var priceFM = getBudgetInFM(price);
        setBudgetSlider(priceFM);
    }
}

function getBudgetInFM(price) {
    switch($('currency_db_id').get('value')) {
        case '2':
            priceFM = price*fRUB;
            break;
        case '0':
            priceFM = price*fUSD;
            break;
        case '1':
            priceFM = price*fEUR;
            break;
        default:
            priceFM = price*1;
            break;
    }
    
    return priceFM.toFixed(0);
}

function getBudgetFromFM(priceFM) {
    switch($('currency_db_id').get('value')) {
        case '2':
            price = priceFM*tRUB;
            break;
        case '0':
            price = priceFM*tUSD;
            break;
        case '1':
            price = priceFM*tEUR;
            break;
        default:
            price = priceFM*1;
            break;
    }
    return price.toFixed(0);
}

function setMinAvgMaxBudgetPrice() {
    try {
        if($('currency_db_id').value==-1 || $('priceby_db_id').value==-1) {return false;}
    } catch(e) {
        return false; //вызов произошел до того как инициализовались комбобоксы 
    }
    // Перевести в текущую валюту
    var count = 1;
    var sum_min = 0;
    var sum_avg = 0;
    var sum_max = 0;
    var type = $('priceby_db_id').get('value');
    var itype = 'prj';
    var ctype;
    switch(type) {
        case '1':
            itype = 'hour';
            ctype = 1;
            break;
        case '2':
            itype = 'hour';
            ctype = 8;
            break;
        case '3':
            itype = 'hour';
            ctype = 22*8;
            break;
        case '4':
            itype = 'prj';
            ctype = 1;
            break;
    }
    
    if($('category_column_id').get('value') == 1) {
        subcat_id = $('category_db_id').get('value');
    } else {
        subcat_id = 0;
    }
    sum_min = sum_min + budget_price[itype]['min'][category][subcat_id];
    sum_avg = sum_avg + budget_price[itype]['avg'][category][subcat_id];
    sum_max = sum_max + budget_price[itype]['max'][category][subcat_id];
    sum_min = sum_min / count;
    sum_avg = sum_avg / count;
    sum_max = sum_max / count;
        
    var s_min = sum_min*ctype;
    var s_avg = sum_avg*ctype;
    var s_max = sum_max*ctype;
    $("hb-low").set("text", s_min.toFixed(0));
    $("hb-middle").set("text", s_avg.toFixed(0));
    $("hb-high").set("text", s_max.toFixed(0));
}

function setBudgetSlider(price) {
    var priceMin = $("hb-low").get("text")-0;
    var priceAvg = $("hb-middle").get("text")-0;
    var priceMax = $("hb-high").get("text")-0;
    if(price<=priceMin) {
        $("fbudget_type").set("value", 1);
        isBudgetSliderChangePrice = 0;
        $("budget-point-l").fireEvent("click");
    }
    if((price>priceMin && price<=priceAvg) || (price>=priceAvg && price<priceMax)) {
        $("fbudget_type").set("value", 2);
        isBudgetSliderChangePrice = 0;
        $("budget-point-m").fireEvent("click");
    }
    if(price>=priceMax) {
        $("fbudget_type").set("value", 3);
        isBudgetSliderChangePrice = 0;
        $("budget-point-h").fireEvent("click");
    }
}

// смена скриншота главной страницы при выборе платных опций
window.addEvent('domready', function(){
    $$('input[name=option_top]', 'input[name=option_logo]').addEvent('click', prjScreenShot);

    
    // комбинации платных опций (top, color, bold, logo)
    var decode = {
        '0000': 1,
        '0100': 2,
        '1100': 3,
        '0110': 4,
        '0101': 5,
        '1110': 6,
        '0111': 7,
        '1101': 8,
        '0010': 9,
        '1010': 10,
        '0011': 11,
        '1011': 12,
        '0001': 13,
        '1001': 14,
        '1000': 15,
        '1111': 16
    }
    
    prjScreenShot();
    
    function prjScreenShot () {
        var top, logo, code, styleIndex, screenShot, prjPointer, prjPointerText;
        
        if ($$('input[name=option_top]')[0]) {
            top = +$$('input[name=option_top]')[0].get('checked');
        } else {
            return;
        }        
        if ($$('input[name=option_logo]')[0]) {
            logo = +$$('input[name=option_logo]')[0].get('checked');
        } else {
            return;
        }
        
        code = "" + top + logo;
        styleIndex = decode[code];
        
        screenShot = $$('.b-pay-prj')[0]; // изображение скриншота
        prjPointer = $('prj_pointer'); // указатель на проект
        prjPointerText = $('prj_pointer_text'); // надпись на указателе
        if (!screenShot || !prjPointer || !prjPointerText) return;
        
        // меняем скриншот
        screenShot.set('class', '').addClass('b-pay-prj').addClass('b-pay-prj__' + styleIndex);
        // перемещаем указатель
        prjPointer.set('morph', {duration: 1000});
        if (top) { // если проект закреплен на верху, то поднимаем указатель
            prjPointer.morph({'top': 25});
        } else {
            prjPointer.morph({'top': 175});
        }
        // меняем текст указателя
        if (code === "1111") {
            prjPointerText.set('html', 'Теперь ваш проект<br /> будет заметен всем');
        } else {
            prjPointerText.set('html', 'Ваш проект будет<br />опубликован где-то<br />здесь');
        }
        
        // стилизация платных пунктов
        if (top) {
            $('option_top_count_block').removeClass('b-form_hide');
            $('option_top_pin').setStyle('display', '');
        } else {
            $('option_top_count_block').addClass('b-form_hide');
            $('option_top_pin').setStyle('display', 'none');
        }        
        
        if (logo) {
            $('option_logo_label_off').setStyle('display', 'none');
            $('option_logo_label_on').setStyle('display', '');
        } else {
            $('option_logo_label_on').setStyle('display', 'none');
            $('option_logo_label_off').setStyle('display', '');
        }

    }
    
    
})