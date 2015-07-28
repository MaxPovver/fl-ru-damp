var iTimeoutId = null;
var option_count = new Array();
var max_option_field = 4;
var field_name_string = {
    'site': {
        'one':'Сайт', 
        'more':'сайт'
    }, 
    'email': {
        'one':'E-mail', 
        'more':'E-mail'
    },
    'phone': {
        'one':'Телефон', 
        'more':'телефон'
    },
    'icq': {
        'one':'ICQ', 
        'more':'ICQ'
    },
    'skype': {
        'one':'Skype', 
        'more':'Skype'
    },
    'jabber': {
        'one':'Jabber', 
        'more':'Jabber'
    },
    'lj': {
        'one':'LiveJournal', 
        'more':'LiveJournal'
    }
};

var month = {
    1: 'января',
    2: 'февраля',
    3: 'марта',
    4: 'апреля',
    5: 'мая',
    6: 'июня',
    7: 'июля',
    8: 'августа',
    9: 'сентября',
    10:'октября',
    11:'ноября',
    12:'декабря'
}

/**
 * @param string field     - идентификатор и по совместительству тип поля
 * @param int    showError   = 1 - показывать ли сообщение об ошибке (используется при вызове по onreypress)
 * @param int    sendRequest = 1 - для полей email, login отправлять ли запросы на сервер 
 * */
function registration_value_check(field, val, showError, sendRequest) {
    if (String(sendRequest) == "undefined") {
        sendRequest = 1;
    }else {
        if (parseInt(sendRequest) != 1) sendRequest = 0;
        else {
            sendRequest = 1;
        }
    }
    if (String(showError) == "undefined") showError = 1;
    else {
        if (parseInt(showError) != 1) showError = 0;
        else {
        	showError = 1;
        }
    }
    var f = $("reg_" + field);
    var e = $("error_" + field);
    if(e != undefined) e.addClass('b-shadow_hide').setStyle("display", "none");
    f.setProperty('title',null);
    if (f.getParent('.b-combo__input')) {
        f.getParent('.b-combo__input').removeClass('b-combo__input_error');
    }
    if(f.getParent('.b-layout__middle')) f.getParent('.b-layout__middle');

    val = f.get("value");
    val = (val==null ? fld.val : val).toString().replace(/^\s+|\s+$/g, '');
    switch(field) {
        case 'login':
            if(val.match(/^[a-zA-Z0-9]+[-a-zA-Z0-9_]{2,}$/)==null) {
                var mess;
                if (val.length === 0) {
                    mess = "Введите логин";
                } else {
                    mess = "Поле заполнено некорректно";
                }
                
                if (showError) {
                    show_error(field, mess);
                }
                
            } else if (sendRequest == 1){
                if(iTimeoutId != null) {
                    clearTimeout(iTimeoutId);
                    iTimeoutId = null;
                }
                iTimeoutId = setTimeout(function(){
                    xajax_CheckUser(val, true);
                }, 300);  
            }

            break;
        case 'email':
            if(val.match(/^[A-Za-z0-9А-Яа-я\.\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e]{1,63}@[A-Za-z0-9А-Яа-я-]{1,63}(\.[A-Za-z0-9А-Яа-я]{1,63})*\.[A-Za-zА-Яа-я]{2,15}$/)==null) {
                var mess;
                if (val.length === 0) {
                    mess = "Введите email";
                } else {
                    mess = "Поле заполнено некорректно";
                }
                if (showError) {
                    show_error(field, mess);
                }
            } /*else if ($("reg_login").get("value").length == 0) {
                xajax_GetFreeLogin(val);
            }*/
            break;
        case 'password':
            if(val.length == 0) {
                show_error(field, 'Введите пароль');
            } else if(val.length > 24) {
                if (showError) {
                    show_error(field, 'Максимальная длина пароля 24 символа');
                }
            } else if(val.length < 6) {
                if (showError) {
                    show_error(field, 'Минимальная длина пароля 6 символов');
                }
            } else if ( val.replace(/[a-zA-Z\d\!\@\#\$\%\^\&\*\(\)\_\+\-\=\;\,\.\/\?\[\]\{\}]/g, "").length != 0) {
                if (showError) {
                    show_error(field, 'Поле заполнено некорректно');
                }
            }
            break;
        case 'rndnum':
            if (val.length < 4) {
                showCaptchaError('Введите код с картинки');
            }
            break;
    }
    
    if($$('.b-combo__input_error').length == 0) {
       inputVal();
    } else {
        $('send_btn').addClass('b-button_disabled');
    }
    
    captchaBlockVisibility();
}
/**
 * меняет type для поля пароль (text/password)
 * @param string id - id элемента input для ввода пароля
 */
function show_password(id) {
    // добавил возможность задавать свой id (на случай если на странице несколько паролей)
    var v = id ? $(id) : $('reg_password');
    if (!v) return;
    
    if (Browser.ie) {
        if(v.type == 'password') {
            var inputText = new Element('input', {'class'  :'b-combo__input-text', 
                                                'value'  : v.value, 
                                                'name'   : 'password',
                                                'size'   : '80',
                                                'type'   : 'text',
                                                'id'     : 'reg_password'});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        } else {
            var inputText = new Element('input', {'class'  :'b-combo__input-text', 
                                                'value'  : v.value, 
                                                'name'   : 'password',
                                                'size'   : '80',
                                                'type'   : 'password',
                                                'id'     : 'reg_password'});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        }
        inputText.addEvent('blur', function(){
            registration_value_check('password');
        });
        inputText.addEvent('keyup', function(){
            registration_value_check('password', 0);
        });
    } else {
        if(v.getProperty('type') == 'password') {
            v.setProperty('type', 'text');
        } else {
            v.setProperty('type', 'password');
        }
    }
}

function clear_error(field) {
    var f = $(field).getParent('.b-combo__input');
    if (f != null)  {
		f.removeClass('b-combo__input_error');
	}
    $(field).setProperty('title', '');

    var e = $("error_" + field.replace("reg_", ""));
    if(e != undefined) e.addClass('b-shadow_hide').setStyle("display", "none");
    if(f != null && f.getParent('.b-layout__middle')) f.getParent('.b-layout__middle');
}

function clearCaptchaError() {
    $('captcha_error').addClass('b-shadow_hide');
    $('error_captchanum').removeClass('b-combo__input_error');
}

function add_option_field(obj, field_name, option) {
    if(option == undefined) var option = { value:'', info_for_reg:'0', error:''};
    
    if(option.value == undefined) value = '';
    else value = option.value;
    
    if(option_count[field_name] == undefined) {
        option_count[field_name] = 0;
    }
    
    
    var id    = 'm_' + field_name + '_' + option_count[field_name];
    var name  = field_name + '_' + option_count[field_name];

    switch(field_name) {
        case 'site':
            if(!$(id)) {
                var n = createFieldBlock(id, field_name, option);
                var title = new Element('div', {'class':'b-layout__txt b-layout__txt_padtop_4', 'html':field_name_string[field_name]['one']});
                n.getElement('.b-layout__left').adopt(title, title);
                if(value != '') n.getElement('input.b-combo__input-text').set('value', value);
                $(obj).getParent('tr').grab(n, 'before');
            } 
            break;
        case 'skype':
        case 'icq':
        case 'phone':
        case 'jabber':
        case 'email':
            if(!$(id)) {
                var n = createFieldBlock(id, field_name, option);
                var title = new Element('div', {'class':'b-layout__txt b-layout__txt_padtop_4', 'html':field_name_string[field_name]['one']});
                n.getElement('.b-layout__left').adopt(title);
                if(value != '') n.getElement('input.b-combo__input-text').set('value', value);
                $(obj).getParent('tr').grab(n, 'before');
            } 
            break;
        case 'lj':
            if(!$(id)) {
                var n = createFieldBlock(id, field_name, option);
                var title = new Element('div', {'class':'b-layout__txt b-layout__txt_padtop_4', 'html':field_name_string[field_name]['one']});
                n.getElement('.b-layout__left').adopt(title);
                
                var a = new Element('span', {'class':'b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_3', 'html':'&nbsp;&nbsp;.livejournal.com'});
                n.getElement('.b-combo__input').removeClass('b-combo__input_width_260').addClass('b-combo__input_width_160');
                //n.getElement('.b-layout__middle').adopt(a);
                a.inject(n.getElement('.b-layout__middle').getElement('.b-combo'), 'after');
                if(value != '') n.getElement('input.b-combo__input-text').set('value', value);
                $(obj).getParent('tr').grab(n, 'before');
            } 
            break;
        default:
            break;
    }
    
    var next = true;
    $$('tr[id^=m_' + field_name + ']').each(function(elm) {
        if($(elm).hasClass('b-layout_hide') && next) {
            $(elm).removeClass('b-layout_hide');
            $(elm).getElement('.b-combo__input-text').setProperty('disabled', null);
            next = false;
        }
    });
    
    if(option_count[field_name] <= max_option_field) {
        option_count[field_name]++;
    }
         
    if(option_count[field_name] >= max_option_field) {
        $(obj).getParent('tr').addClass('b-layout_hide');
    }
    
    if(option_count[field_name] > 0) {
        $('option_' + field_name).getElement('.b-layout__middle .b-layout__link').set('html', 'Еще один ' + field_name_string[field_name]['more']);
    }

    fix_title_option_field(obj, field_name);
}

function clear_empty_field(field_name) {
     $$('tr[id^=m_' + field_name + ']').each(function(elm) {
        var inp = $(elm).getElement('.b-combo__input-text');
        
        if(inp.get('value') == '') {
            var field_name = $(elm).getProperty('id').toString().split("_")[1];
            delete_option_field(inp, field_name);
        }
    });
     
}

 
function delete_option_field(obj, field_name) {
    field_name = field_name.split('_')[0]
    option_count[field_name]--;
    if(option_count[field_name] == 0) {
        $('option_' + field_name).getElement('.b-layout__middle .b-layout__link').set('html', field_name_string[field_name]['one']);
    }
    $(obj).getParent('tr').addClass('b-layout_hide');
    var input = $(obj).getParent('tr').getElement('.b-combo__input-text');

    if(field_name=='site') {
        input.set('value', 'http://');
    } else {
        input.set('value', '');
    }
    input.setProperty('disabled', true);
    $('option_' + field_name).removeClass('b-layout_hide');

    fix_title_option_field(obj, field_name);
}

function fix_title_option_field(obj, field_name) {
    var f = $(obj).getParent('tr').getParent('table').getElements('tr[id^=m_'+field_name+']');
    var n = false;
    f.each(function(el){
        try {
            title = el.getElement('div');
            if(n==false) {
                if(el.hasClass('b-layout_hide')==false) {
                    title.removeClass('b-layout_hide');
                    n = true;
                }
            } else {
                title.addClass('b-layout_hide');
            }
        } catch(err) {}
    });
}

function createFieldBlock(id, name, option) {
    var field_name = name.split('_')[0];
    
    var main = new Element('tr', {'class': 'b-layout__tr', 'id':id});
    var td1 = new Element('td', {'class':'b-layout__left b-layout__left_padbot_10 b-layout__left_width_110'});
    var td2 = new Element('td', {'class':'b-layout__middle b-layout__middle_padbot_10 b-layout__middle_width_270'});
    var td3 = new Element('td', {'class':'b-layout__one b-layout__one_padbot_10 b-layout__one_width_30 b-layout__one_center'});
    var td4 = new Element('td', {'class':'b-layout__right b-layout__right_padbot_10'});
    
    td2.adopt(createInputElement( field_name+'_'+id.split('_')[2] ));
    if(option.error != '') {
        var error_html = '<span class="b-form__error"></span>' + option.error;
        var error = new Element('div', {'class':'b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10', 'html':error_html});
        td2.adopt(error);
    }
    
    td3.adopt(new Element('a', {'href':'javascript:void(0)', 'class':'b-button b-button_admin_del', 'onclick':'delete_option_field(this, "' + field_name+'_'+id.split('_')[2] + '")'}));
    td4.adopt(createVisibleOptionElement(field_name+'_'+id.split('_')[2], option));
    
    main.adopt(td1, td2, td3, td4);
    
    return main;
}

function createInputElement(name) {
    var main = new Element('div', {'class':'b-combo b-combo_inline-block'});
    var div  = new Element('div', {'class':'b-combo__input b-combo__input_width_260'});
    var i = new Element('input', {'class':'b-combo__input-text b-combo__input-text_fontsize_15', 'size':'80', 'type':'text', 'value':'', 'name':name, 'id':name, 'onfocus':'clearErrorBlock(this)'});
    
    div.adopt([i, new Element('label', {'class':'b-combo__label', 'for':name})]);
    main.adopt(div);
    return main;
}

function createVisibleOptionElement(name, option) {
    if(option.info_for_reg == 'undefined') option.info_for_reg = 0;
    
    if(option.info_for_reg == 1) {
        var cls1 = 'b-eye__link_bordbot_dot_808080';
        var cls2 = 'b-eye__icon_close';
        var txt  = 'Видят только зарегистрированные';
    } else {
        var cls1 = 'b-eye__link_bordbot_dot_0f71c8';
        var cls2 = 'b-eye__icon_open';
        var txt  = 'Видят все';
    }
    
    var main = new Element('div', {'class':'b-eye b-eye_inline-block'});
    var a    = new Element('a', {'class':'b-eye__link ' + cls1 + ' b-eye-enable', 'href':'javascript:void(0)', 'onclick':'info_for_reg(this)'});
    var i    = new Element('input', {'type':'hidden', 'name':'info_for_reg[' + name + ']', 'value':option.info_for_reg});
    a.adopt([new Element('span', {'class':'b-eye__icon ' + cls2 + ' b-eye__icon_margright_5'}), new Element('span', {'class':'b-eye__txt', 'html':txt})]);
   
    main.adopt(a, i);
    return main;
}

function info_for_reg(obj) {
    var e = $(obj);
    
    if(e.hasClass('b-eye__link_bordbot_dot_808080')) {
        e.getElement('.b-eye__icon').removeClass('b-eye__icon_close');
        e.removeClass('b-eye__link_bordbot_dot_808080');
        
        e.getElement('.b-eye__icon').addClass('b-eye__icon_open');
        e.addClass('b-eye__link_bordbot_dot_0f71c8');
        
        e.getParent(".b-eye").getElement("input").set('value', 0);
        
        e.getElement('.b-eye__txt').set('html', 'Видят все');
    } else {
        e.getElement('.b-eye__icon').removeClass('b-eye__icon_open');
        e.removeClass('b-eye__link_bordbot_dot_0f71c8');
        
        e.getElement('.b-eye__icon').addClass('b-eye__icon_close');
        e.addClass('b-eye__link_bordbot_dot_808080');
        
        e.getParent(".b-eye").getElement("input").set('value', 1);
        e.getElement('.b-eye__txt').set('html', 'Видят только зарегистрированные');
    }
}

function toggleServices(service_name, obj) {
    var def  = '.services-' + service_name + '-default';
    var name = '.services-' + service_name;
    
    if(!$$(def)) return false;
    if($(obj).hasClass('b-layout__link_bordbot_dot_0f71c8')) {
        $(obj).removeClass('b-layout__link_bordbot_dot_0f71c8').addClass('b-layout__link_bordbot_dot_000');
    } else {
        $(obj).removeClass('b-layout__link_bordbot_dot_000').addClass('b-layout__link_bordbot_dot_0f71c8');
    }
    $$(def).toggleClass('b-layout_hide');
    $$(name).toggleClass('b-layout_hide');
}

function calcPRO(price, count) {
    if(count<=0) {
        $('amount_price_pro').set('html', 0);
    } else {
    	$('count_pro_name').set('html', ending(count, 'месяц', 'месяца', 'месяцев'));
        $('amount_price_pro').set('html', price*count);
    }
}


function delete_field_info(obj) {
    $(obj).getParent('tr').dispose();
}

function calcAmmountOfOption(listenerElements, htmlBlock) {
	listenerElements.each(function(elm) {
        if(elm.hasClass('scalc-click')) {
            elm.addEvent('click', function() {
                if(this.checked) {
                    var pid = $(this).getProperty('pid');
                    if($('def' + pid)) {
                        $('def' + pid).set('value', 1);
                    }
                } else {
                    var pid = $(this).getProperty('pid');
                    if($('def' + pid)) {
                        $('def' + pid).set('value', 0);
                    }
                }
                calc();
            });
        } else if(elm.hasClass('scalc-click-dis')) {
            elm.addEvent('click', function() {
                calc();
                
                var sum = htmlBlock.get('html');
                if (sum == 0) {
                    $('wizard_button').addClass('b-button_rectangle_color_disable');
                    $('wizard_button').removeClass('b-button_rectangle_color_green');
                    $('wizard_error_btn').setStyle('display', 'none');
                } else if (sum <= ac_sum) {
                    $('wizard_button').removeClass('b-button_rectangle_color_disable');
                    $('wizard_button').addClass('b-button_rectangle_color_green');
                    $('wizard_error_btn').setStyle('display', 'none');
                } else {
                    $('wizard_button').removeClass('b-button_rectangle_color_green');
                    $('wizard_button').addClass('b-button_rectangle_color_disable');
                    $('wizard_error_btn').setStyle('display', null);
                }
            });
        } else if(elm.hasClass('scalc-change')) {
            elm.addEvent('keyup', function() {
                var content = $(this).getParent('.b-check').getElement('.scalc-change-result');
                var count = parseInt($(this).get('value'), 10);
                count = count > 0? count: 0;
                var price = count * $(this).getProperty('price');
                var name  = $(this).getParent('.b-check').getElement('.scalc-change-name');
                
                name.set('html', ending(count, $(this).getProperty('change1'),$(this).getProperty('change2'),$(this).getProperty('change3')));
                content.set("html", price);
                calc();
            });
        }
    });

    function calc() {
        var result = 0;
        listenerElements.each(function(elm) {
            if(elm.getProperty('dis')) {
                var split = elm.getProperty('dis').split(',');
                for(var i = 0; i<split.length; i++) {
                    var id = split[i];
                    if(!elm.getProperty("checked")) {
                        $('pay' + id).setProperty('disab', null);
                    } else {
                        $('pay' + id).setProperty('disab', true);
                    }
                }
            }
        });
        listenerElements.each(function(elm) {
            if(elm.getProperty("checked") && !elm.getProperty('disab')) {
                // определяем на какой странице сейчас
                if ($$('input[value=upd_pay_options]').length || window.location.pathname == '/bill/') { // завершающая страница мастера
                    var proBonus = $$('input[name=pro_bonus]')[0].get('value') || 0; // бонус для каждой позиции для ПРО аккаунта (кроме самого ПРО аккаунта)
                    if (+$(elm).get('option') === 1 || $(elm).get('option') === 'top') { // если пункт закрепления на верху, то учитываем количество дней
                        var count = +$(elm).get('top_count');
                        proBonus = proBonus * count;
                    }
                    // выбран ли пункт ПРО
                    var isPro = ($$('input[op_code=15]')[0] && $$('input[op_code=15]')[0].get('checked')) || +$$('input[name=is_pro]')[0].get('value');
                    // если это чекбокс ПРО акк., то бонус не отнимаем
                    var price = $(elm).get('price');
                    var isEmp = $('isEmp').get('value');
                    // если выбран ПРО
                    if (isPro && ($(elm).get('op_code') != '15') && isEmp == 1) {
                        price = price - proBonus;
                    }
                    result = result + +price;
                } else { // страница создания проекта
                    if($(elm).hasClass('count-change')) {
                        var count = $(elm).getParent().getElement('input[type=text]');
                        var days  = parseInt(count.get('value'), 10);
                        days = days > 0? days: 0;
                        var price = count.getProperty('price') * days;
                    } else {
                        var price = $(elm).getProperty('price');
                    }
                    result = result - (-price);
                }
            }
        });
        
        htmlBlock.set('html', result);
    }
}

// показывает сообщение об ошибке для определенного поля
function show_error(field, error) {
    var inp = $('reg_' + field).getParent('.b-combo__input');
    if(inp != undefined) inp.addClass('b-combo__input_error');
    if($('error_' + field) != undefined) {
        $('error_' + field).removeClass('b-shadow_hide').setStyle('display', null);
        $('error_txt_' + field).set('html', '<span class="b-form__error"></span>' + error);
    }
}

function showCaptchaError(error) {
    $('error_txt_captchanum').set('html', '<span class="b-form__error"></span>' + error);
    $('captcha_error').removeClass('b-shadow_hide');
    $('error_captchanum').addClass('b-combo__input_error');
}

/*Очищает поля списка работ пользователя от временного текста*/
function clearGrayPortfolioTitles() {	
    var ls = $('frm').getElements(".b-combo__input-text.b-combo__input-text_color_a7");    
    for (var i = 0; i < ls.length; i++) {
		ls[i].value = '';
	}
}

/*Отправка формы авторизации на странице /wizard/registration/free-lancer?step=3/ */
function wizardLoginFormSubmit(e) {                    
    if (e.keyCode == 13 && $('auth_login').value.length > 2) {
        var btn = $(document).getElement('.sendBtnUnique');
        if(!btn.hasClass('b-button_rectangle_color_disable')) {                        	                        	
            btn.removeClass('b-button_rectangle_color_green');
            btn.addClass('b-button_rectangle_color_disable');
            $('frm-auth').submit();
        }
    }
}

function checkValueAllInputs() {
    if ($("reg_login")) {
        if (!$("reg_login").getParent("div.b-combo__input").hasClass("b-combo__input_error") ) {
            registration_value_check('login', 0, 1, 0);
        }
    } else {
        if (!$("reg_email").getParent("div.b-combo__input").hasClass("b-combo__input_error") ) {
            registration_value_check('email', 0, 1, 0);
        }
        registration_value_check('password');
        registration_value_check('rndnum');   
    }
}

function formSubmit() {
    if (!$('send_btn').hasClass('submitted') && 
        !$('send_btn').hasClass('b-button_disabled')) {
    
        checkValueAllInputs();
        if (!$('send_btn').hasClass('b-button_rectangle_color_disable')) {
            $('send_btn').addClass('submitted');
            $('form_reg').submit();
        }
    }
}


window.addEvent('domready', function(){
    // страница регистрации/авторизации
    var regBlock = $('reg-block'), // блок регистрации
        authBlock = $('auth-block'), // блок авторизации
        openAuthBlock = $('open-auth-block'),  // ссылка на открытие блока авторизации
        openRegBlock = $('open-reg-block');   // .......................... регистрации
    if (regBlock && authBlock && openAuthBlock && openRegBlock) {
        openAuthBlock.addEvent('click', function(){
            authBlock.removeClass('b-layout_hide');
            regBlock.addClass('b-layout_hide');
        })
        openRegBlock.addEvent('click', function(){
            authBlock.addClass('b-layout_hide');
            regBlock.removeClass('b-layout_hide');
        })
    }
    
    
    // страница "Личная информация"
    // закрытие информационного блока "Вы успешно зарегистрировались"
    $$('#wizard_reg_succ_close').addEvent('click', function(){
        $$('#wizard_reg_succ').dispose();
        Cookie.write('master_auth', 1, {duration:1});
    })
    $$("input.b-combo__input-text").addEvent('focus',
        function () {
            if($$('.b-combo__input_error') && 
               $$('.b-combo__input_error').length == 0 ) {
           
                inputVal();
            } else if ( $('send_btn') ) {
                $('send_btn').addClass('b-button_disabled');
            }
            
            captchaBlockVisibility();
        }
    );
    
    //Указание выбранной роли при регистрации через соцсети
    $$('input[name=role]').addEvent('change', function() {
        var role = this.value;
        var socialBtns = $$('.b-auth_btn');
        if (socialBtns.length) {
            socialBtns.each(function(el) {
                if (!el.get('data-baseurl')) {
                    el.set('data-baseurl', el.get('href'));
                }
                var baseUrl = el.get('data-baseurl');
                el.set('href', baseUrl + '&role=' + role);
            }); 
        }
    });
    
    
    //Скролим к ошибке
    var first_field = $$('.b-combo__input_error')[0];
    if(first_field) {
        var fcoord = first_field.getCoordinates();
        new Fx.Scroll(window).start(0,fcoord.top - 100);
    }
});

function inputVal()
{
    if(!(($('reg_email') && $('reg_email').get('value') == '') || 
         ($('reg_password') && $('reg_password').get('value') == '') || 
         ($('reg_rndnum') && $('reg_rndnum').get('value') == '') || 
         ($('reg_rndnum') && $('reg_rndnum').get('value').length < 4) || 
         ($('reg_login') && $('reg_login').get('value') == '')
    )) {
     
        $('send_btn').removeClass('b-button_disabled');
    }
}

function updateCaptchaImage() {
    $('rndnumimage').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random());
    return false;
}

function captchaBlockVisibility()
{
    var captcha_block = $$('[data-captcha-block]');

    if(!captcha_block.length) {
        return false;
    }
    
    if(!($('reg_email').get('value') == '' || 
         $('reg_password').get('value') == '' || 
         $('reg_password').get('value').length < 6)) {
     
        captcha_block.removeClass('g-hidden');
    }
}