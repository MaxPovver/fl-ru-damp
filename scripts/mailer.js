window.addEvent('domready', 
    function() {
        
        initDigitalInput();
        
        $$('.show-filter').addEvent('click',function(){
            this.getParent('.b-layout__txt').getParent('.b-layout__txt').getNext('.b-layout__inner').toggleClass('b-layout__inner_hide');
            this.getPrevious('.b-layout__ygol').toggleClass('b-layout__ygol_hide');
            return false;
            })
        $$('.show-settings a').addEvent('click',function(){
            this.getParent('.b-layout__txt').getNext('.b-fon').toggleClass('b-fon_hide');
            this.getParent('.b-layout__txt').toggleClass('b-layout__txt_hide');
            return false;
            })
        $$('.close-block').addEvent('click',function(){
            clearSubfilter($(this).getParent('.b-fon-subfilter'));
            this.getParent('.b-fon').toggleClass('b-fon_hide');
            this.getParent('.b-fon').getPrevious('.show-settings').toggleClass('b-layout__txt_hide');
            return false;
            });
        
        setEventFilterElements('filter_employer', 'emp_check1');
        setEventFilterElements('filter_freelancer', 'frl_check2');
    }
);
    
function clearSubfilter(elm) {
    elm.getElements('input, select').each(function(elm) {
        if(elm.type == 'checkbox') {
            elm.checked = null;
        }
        if(elm.type == 'text') {
            elm.value = '';
        }
        if(elm.get('tag') == 'select') {
            elm.options[0].selected = true;
        }
    });
    elm.getElements('span[id$=_date_text]').setStyle('display', 'inline-block');
}    
    
function initDigitalInput() {
    $$('.b-combo-digital-input').addEvent('keyup', function(){
        digitalInput(this);
    });

    $$('.b-combo-digital-input').addEvent('keydown', function(){
        digitalInput(this);
    });
    
    $$('.b-combo-digital-input').addEvent('blur', function(){
        digitalInput(this);
    });    
}    
    
function digitalInput(obj) {
    obj.value = obj.value.replace(',', '.');
    obj.value = obj.value.replace(/[^0-9.]/, '');
    var val   = obj.value.split('.');
    if(val.length > 1) {
        var value = '';
        for(i=0;i<val.length-1;i++) {
            value += val[i]; 
        }
        value += '.' + val[val.length-1];
        obj.value = value;
    }
}    


function setEventFilterElements(filter_name, check_name) {
    $$('#' + filter_name + ' input').each( function( el ) {
        if(el.get("type") == "checkbox") {
            el.addEvent("click", function(){
                if(this.checked) {
                    $(check_name).set("checked", true);
                }
            });
        } 
        if(el.get("type") == "text") {
            el.addEvent("change", function() {
                if(this.value != "") {
                    $(check_name).set("checked", true);
                }
            });
        }
    }); 
    
    $$('#' + filter_name + ' select').each( function( el ) {
        el.addEvent("change", function() {
            $(check_name).set("checked", true);
        });
    });
}

var count_type_buying = 0;
function addBuyingType(obj) {
    count_type_buying += 1;
    
    var def    = obj.getParent("span").clone();
    var parent = obj.getParent("td");
    
    def.getElements('input').each(function(el){
        var name = el.get("name");
        name = name.replace(/\[\d+?\]\[/, "[" + count_type_buying + "][");
        el.set("name", name); 
    });

    def.getElements('select').each(function(el) {
        el.options[0].selected = true;
        var name = el.get("name");
        name = name.replace(/\[\d+?\]/, "[" + count_type_buying + "]");
        el.set("name", name);
    });
    
    parent.grab(def);
    updateEventBuyingSelect(parent);
    
    initDigitalInput();
}

function updateEventBuyingSelect(obj) {
    var selects = obj.getElements("select");
    for(var i =0; i < selects.length; i++) {
        if(i == selects.length-1) {
            selects[i].setProperty("onchange", "addBuyingType(this);");
        } else {
            selects[i].setProperty("onchange", "");
        }
    }
    
    if(obj.getElements(".buying_type").length > 1) {
        obj.getElements("a.b-button_admin_del").removeClass('b-button_hide');
    } else {
        obj.getElements("a.b-button_admin_del").addClass('b-button_hide');
    }
}

function removeBuyingType(obj) {
    var parent = obj.getParent("td");
    if(parent.getElements(".buying_type").length > 1) {
        obj.getParent("span").dispose();
        updateEventBuyingSelect(parent);
    }
}

function updateCitys(v) {
    ct = $("pf_city");
    ct.disabled = true;
    ct.options[0].innerHTML = "Подождите...";
    ct.value = 0;
    xajax_GetCitysByCid(v, {'name' : 'city', 'class' : 'b-select__select b-select__select_width_300', 'id' : 'pf_city'});
}

function selectRegularType(val, sregtype) {
    if(sregtype[val] != undefined) {
        sHtml = "";
        for(var i=0; i < sregtype[val].length; i++) {
            sHtml += '<option value=' + ( i - (-1) ) + ' >' + sregtype[val][i] + '</option>';
        }
        
        $('type_send_regular').set('html', sHtml);
        $('type_send_regular').options[0].selected = true;
        $('repeat_type').removeClass('b-layout_hide');
        $('date_sending').removeClass('b-combo_inline-block').addClass('b-combo_hide');
        $('str_date_sending').removeClass('b-layout__txt_inline-block').addClass('b-layout_hide');
    } else {
        $('repeat_type').addClass('b-layout_hide');
        $('date_sending').removeClass('b-combo_hide').addClass('b-combo_inline-block');
        $('str_date_sending').removeClass('b-layout_hide').addClass('b-layout__txt_inline-block');
    }
}


function calcRecpient() {
    var form = new Array();
    for(i=0;i<$('create_form').elements.length;i++) {
        if($('create_form').elements[i].type == 'checkbox') {
            if($('create_form').elements[i].checked == true) {
                var value = 1;
            } else {
                var value = 0;
            }
        } else {
            var value = $('create_form').elements[i].value;
        }
        
        form[form.length] = {'name': $('create_form').elements[i].name, 'value': value};
    }
    json_form = JSON.encode(form);
    
    xajax_recalcRecipients(json_form);
}

function setPlaceholderWysiwyg(obj) {
    if(CKEDITOR && CKEDITOR.instances.main_message) {
        var editor = CKEDITOR.instances.main_message;
        editor.insertText( obj.title );
    } else {
        var edt = $('main_message').retrieve('MooEditable');
        if(edt) {
            edt.selection.insertContent(obj.title);
        }
    }
}

function showHideNotImportantText(obj) {
    var obj_td, n;
    var obj_input = obj.b_input;

    obj_td = obj_input.getParent().getParent().getParent();
    n = 0;

    obj_td.getElements('input[class=b-combo__input-text]').each(function(elm) {
        if(elm.get('value')!='') n = 1;
    });

    if(n==1) {
        obj_td.getElements('span[id$=_date_text]').setStyle('display', 'none');
    } else { 
        obj_td.getElements('span[id$=_date_text]').setStyle('display', 'inline-block');
    }
}

function clearMainFilter() {
    var filter = $('filter_form');
    
    filter.getElements('input').each(function(el) {
        switch(el.type) {
            case 'text':
                el.set('value', '');
                break;
            case 'checkbox':
                el.checked = false;
                break;
            case 'hidden':
                if(el.name == 'from_eng_format' || el.name == 'to_eng_format') {
                    el.set('value', '');
                }
                break;
        }
    });
    
    filter.getElements('select').each(function(el) {
        el.selectedIndex = 0;
    });
}


function preview()
{
    $('draft').set('value', '1'); 
    $('preview').set('value', '1'); 
    $('create_form').submit();
}