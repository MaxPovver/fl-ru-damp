window.addEvent('domready', 
    function() {
        init_work_time_add();
        init_document_button();
        init_multi_button();
        init_shadow_btn();
        init_help();
        init_fileinfo(); // эту функцию перенес в sbr.js (она нужна на странице финансов)
        if($('toggler-tz')) {
            $('toggler-tz').addEvent('click',function(){
                if(this.getParent('.b-post').getElement('.b-post__body').hasClass('b-post__body_height_100')){
                    this.getParent('.b-post').getElement('.b-post__body').removeClass('b-post__body_height_100');
                    this.getParent('.b-post').getElement('.b-post__weaken').addClass('b-post__weaken_hide');
                    this.set('text', 'Свернуть задание');
                } else{
                    this.getParent('.b-post').getElement('.b-post__body').addClass('b-post__body_height_100');
                    this.getParent('.b-post').getElement('.b-post__weaken').removeClass('b-post__weaken_hide');
                    this.set('text', 'Развернуть задание');
                    }
                return false;
            });
        }
        
        if($$('.b-estimate__link').length > 0) {
            $$('.b-estimate__link').addEvent('click',function(){
                if(!this.getParent('.b-estimate__item').hasClass('b-estimate__item_active')){
                    this.getParent('.b-estimate').getElements('.b-estimate__item').removeClass('b-estimate__item_active');
                    this.getParent('.b-estimate__item').addClass('b-estimate__item_active');
                    return false;
                }
            });
        }
        
        var reg = /#(o|a|s|p|n|c)_(\d*)/;
        var arr = null;

        if (arr = reg.exec(window.location.hash)) {
            hlAnchorScroll(arr[1],arr[2]);
        }
    }
);
    
window.addEvent('load', 
    function() {   
        var reg = /#(o|a|s|p|n|c)_(\d*)/;
        var arr = null;
        if (arr = reg.exec(window.location.hash)) {
            hlAnchorScroll(arr[1],arr[2]);
        }
    }
);
    
    
var listPauseDays = new Object();    
for(var i = 1;i<31;i++) {
    listPauseDays[i] = i + ending(i, ' день', ' дня', ' дней');
}  

function changePauseDays(is_toggle) {
    if(is_toggle == undefined) is_toggle = 0;
    
    if(is_toggle == 0) {
        var cpd = ComboboxManager.getInput("count_pause_days");
        var tmp = new Date();
        tmp.setDate( tmp.getDate() + parseInt( cpd.breadCrumbs[0] ) );

        var dt = ComboboxManager.getInput("pause_date");
        var day = tmp.getDate();
        var m   = tmp.getMonth();
        var y   = tmp.getFullYear();
        dt.setDate(String(y) + "-" + dt.nToStr(++m) + "-" + dt.nToStr(day), false);
    } else {
        var now = new Date();
        var tmp = new Date($('pause_date_eng_format').get('value'));
        
        var day = Math.ceil( (tmp.getTime() - now.getTime() ) / (1000*60*60*24) );
        
        var dt  = ComboboxManager.getInput("pause_date");
        var cpd = ComboboxManager.getInput("count_pause_days");
        if(day > 30) {
            day = 30;
            var tmp = new Date();
            tmp.setDate( tmp.getDate() + 30 );
            var d = tmp.getDate();
            var m = tmp.getMonth();
            var y = tmp.getFullYear();
            dt.setDate(String(y) + "-" + dt.nToStr(++m) + "-" + dt.nToStr(d), false);
        }
        cpd.selectItemById(day);
        cpd.setDefaultValue(day + ending(day, ' день', ' дня', ' дней'));
        cpd.selectItems();
        $(cpd.shadow).set('class', $(cpd.shadow).get('class') + ' b-shadow_hide'); // Для IE
    }
}

function init_work_time_add() {
    if($('work_time_add') == false || $('work_time_add') == undefined) return;
    $('work_time_add').addEvent('change', function() {
        changedAddDaysStage(this);
    });
    $('work_time_add').addEvent('keyup', function() {
        changedAddDaysStage(this);
    });
}

function changedAddDaysStage(obj) {
    var val = $(obj).get('value');
    var sval = val.replace(/\D+/, '');
    if(sval != val) {
        $(obj).set('value', sval);
    }
    var days = parseInt( $(obj).get('value')).toFixed(0);
    var year = $(obj).get('data-year');
    var month = $(obj).get('data-month') - 1;
    var day = $(obj).get('data-day');
    var time = (new Date(year, month, day)).getTime();

    if(days == undefined || days == null || days == 'NaN') days = 0;
    if(days == 0) {
        $('btn_changed_stage').set('html', 'Внести изменения');
        $('label_descr_work_time_add').addClass('b-layout__txt_hide');
    } else {
        $('btn_changed_stage').set('html', 'Я согласен изменить срок аккредитива');
        $('label_descr_work_time_add').removeClass('b-layout__txt_hide');
    }
    var label = ending(days, '&#160;день', '&#160;дня', '&#160;дней');
    $('str_label_work_time_add').set('html', label);
    
    $('stage_end_date').set('text', getNextDateStage(days, time));
}

function getNextDateStage(days, timestamp) {
    if(days == undefined || days == null || days == 'NaN') days = 0;
    var d = new Date(timestamp);
    
    var nextDate   = parseInt( d.getTime() ) + parseInt( (days * 3600 * 24) * 1000 );
    d.setTime(nextDate);
    var month = ( d.getMonth() + 1 );
    month = month < 10 ? '0' + month : month;
    var day = d.getDate() < 10 ? '0' + d.getDate() : d.getDate();
    var date = day + '.' + month + '.' + d.getFullYear() + ' г.';
    return date;
}

function hlAnchorScroll(mode, id) {
    if(mode == 'n') {
        var IDName = "evn_" + id; 
        JSScroll($(IDName));
    }
}
   
function init_shadow_btn() {
    $$('.b-shadow__icon_quest').removeEvents('click');
    $$('.b-shadow__icon_close:not([id="help_popup_close"])').removeEvents('click');
    
    if($$('.b-shadow__icon_quest').length > 0) {
        $$('.b-shadow__icon_close:not([id="help_popup_close"])').addEvent('click',function(event) {
            event.stop();
            if(this.getParent('.b-moneyinfo').hasClass('b-filter__toggle')){
                this.getParent('.b-moneyinfo').addClass('b-filter__toggle_hide')
            } else {
                this.getParent('.b-moneyinfo').addClass('b-shadow_hide');
                $$('div.b-filter__overlay').destroy();
            }
        });
        $$('.b-moneyinfo').addEvent('click', function(event){
            event.stop();
        });
        // квадрат с вопросиком
        // если добавить класс b-shadow__icon_quest_no_event, то событие не добавится к нему
        $$('.b-shadow__icon_quest:not(.b-shadow__icon_quest_no_event)').addEvent('click',function(event){
            event.stop();
            $$('.b-shadow').addClass('b-shadow_hide');
            this.getParent('.i-shadow').getChildren('.b-shadow').removeClass('b-shadow_hide');
        });
        
        $(document.body).addEvent('click', function() {
            $$('.b-moneyinfo').addClass('b-shadow_hide');
            $$('div.b-filter__overlay').destroy();
        });
    }
}   
   
function init_multi_button() {
    if($('b-button-more')) {
        $('b-button-more').addEvent('click', function(event) {
            event.stop();
            $('b-button-more').getParent().toggleClass('b-button-multi__item_active'); // Конфликт со скриптом b-page.js
            $('b-button-more').getParent().getElement('.b-shadow').toggleClass('b-shadow_hide');
        });

        $('b-button-more').getParent().getElement('.b-shadow').addEvent('click', function(event) {
            event.stop();
        });

        $(document.body).addEvent('click', function() {
            $$('.b-button-multi__item').removeClass('b-button-multi__item_active');
            var elm = $('b-button-more').getParent().getElement('.b-shadow');
            if(elm) {
                if(! elm.hasClass('b-shadow_hide')) {
                    $('b-button-more').getParent().removeClass('b-button-multi__item_active');
                    elm.addClass('b-shadow_hide');
                }
            }
        });
    }
}    

    
function init_document_button() {
    if($('document_show') && $('document_links')) {
        $('document_show').addEvent('click', function(event) {
            event.stop();
            $('document_links').toggleClass('b-shadow_hide');
        });

        $('document_links').addEvent('click', function(event) {
            //event.stop();
        });

        $(document.body).addEvent('click', function() {
            if(!$('document_links').hasClass('b-shadow_hide')) {
                $('document_links').addClass('b-shadow_hide');
            }
        });
    }
}    

function toggle_currents(obj) {
    var ttl = $(obj).get('html');
    
    if(ttl == 'Свернуть завершенные сделки') {
        $(obj).set('html', 'Развернуть завершенные сделки');
        $('loads_currents_sbr').hide();
    } else {
        $(obj).set('html', 'Свернуть завершенные сделки');
        $('loads_currents_sbr').show();
    }
}
 
function toggle_tz() {
    if($$('.sbr-old-tz')[0].hasClass('b-post__txt_hide')) {
        $('toggle-tz-link').set('html', 'Посмотреть новое');
        $$('.sbr-old-tz').removeClass('b-post__txt_hide');
        $$('.sbr-tz').addClass('b-post__txt_hide');
        if($('new_attach')) $('new_attach').hide();
        if($('old_attach')) $('old_attach').show();
    } else {
        $('toggle-tz-link').set('html', 'Посмотреть старое');
        $$('.sbr-old-tz').addClass('b-post__txt_hide');
        $$('.sbr-tz').removeClass('b-post__txt_hide');
        if($('new_attach')) $('new_attach').show();
        if($('old_attach')) $('old_attach').hide();
    }
} 


function hlAnchor(mode, id) {
    if(mode == 'n') {
        var IDName = "event_" + id; 
    }
    
    if($(IDName) != undefined) {
        $(IDName).getParent().getElements('.b-post_bg_f0f4f5').each(function(elm) {
            elm.removeClass('b-post_bg_f0f4f5');   
        }); 
        $(IDName).getParent().getElements('.b-post__anchor_black').each(function(elm) {
            elm.removeClass('b-post__anchor_black');   
        }); 
        $(IDName).getElement('.b-post__anchor').addClass('b-post__anchor_black');
        $(IDName).addClass('b-post_bg_f0f4f5');
    }
    
    if(mode == 'a' && $$('#ops_answer_'+id)) $('ops_answer_'+id).addClass('ops-one-this');
    if(mode == 's' && $$('#ops_stage_'+id)) $('ops_stage_'+id).addClass('ops-one-this');
}

function getMasterStep(stage_id) {
    $$('.master-stage').hide();
    var stage = $('master-stage-' + stage_id);
    if(stage) {
        stage.show();
    }
}

function MStage(option) {
    var active = null;
    var stages = new Array();
    var view_size = 954;
    var sbr_agree = false;
    var stages_agree = []; // просмотренные исполнителем этапы
    var agree_cnt = 0;
    var master = $('master-list');
    var tsize = 0;
    var lsize = new Array();
    var lisize = new Array();
    
    this.initHScroll = function(position) {
        master.getElements('li').each(function(elm) {
            tsize = tsize + $(elm).getSize().x;
            lsize.push(tsize);
            lisize.push($(elm).getSize().x);
        });
        if(tsize - (-10) > 1201) { 
            $('master-list').setStyle('width', tsize - (-10));
        }
        this.myFx = new Fx.Scroll(master.getParent(), {
            onComplete: function() {
                var scr = master.getParent().getScroll();
                if(scr.x > 1) {
                    $('shadow-left').setStyle('display', 'block');
                } else {
                    $('shadow-left').setStyle('display', 'none');
                }
            }
        });
        
        if(position == undefined) {
            this.myFx.start(0,0);
        } else {
            this.scroll(position);
        }
    }
    
    this.scroll = function(pos) {
        var nsize = lsize[pos+1];
        if(nsize != undefined && lsize[lsize.length-1] > view_size) { // Скролить не надо если все объекты видны
            if(view_size < nsize) {
                var x =  nsize - view_size;
                var scr = master.getParent().getScroll();
                var dif = scr.x - x; // Выйсняем надо крутить назад или нет
                if(dif > lisize[pos]/2) x = view_size / 3;
                //if(scr.x <= x || dif > lisize[pos]/2) {
                if(lisize[pos-1] < 80 && master.getElements('li')[pos-2] != undefined) {
                    this.myFx.toElement(master.getElements('li')[pos-2], 'x');
                } else {
                    this.myFx.toElement(master.getElements('li')[pos-1], 'x');
                }
                    //this.myFx.start(x, 0);
                //}
            } else if(view_size >= nsize) {
                if(pos > 0) {
                    if(lisize[pos-1] < 80 && master.getElements('li')[pos-2] != undefined) {
                        this.myFx.toElement(master.getElements('li')[pos-2], 'x');
                    } else {
                        this.myFx.toElement(master.getElements('li')[pos-1], 'x');
                    }
                } else {
                    this.myFx.toElement(master.getElements('li')[pos], 'x');
                }
            }
        }
    }
    
    this.draw = function(id, e) {
        var step = stages.length;
        if(id == 'last') {
            for(var i = 0; i < stages.length; i++) {
                this.redrawStage(stages[i], true);
            }
            this.redrawStage(id, false);
            this.agreeStage(step);
            xajax_checkSbr(stages[step-1]);
            this.loadStage(id);
        } else { 
            for(var i = 0; i < stages.length; i++) {
                if(i <= step || sbr_agree == true || stages_agree[i]) {
                    this.redrawStage(stages[i], true);
                }
                if(stages[i] == id) {
                    step = i;
                    this.setActive(id);
                    if(e.preventDefault) {
                        e.preventDefault();
                    } else {
                        e.returnValue = false;
                    }
                    //e.preventDefault();
                    this.scroll(i+1); // учитываем первый текст как шаг
                    this.redrawStage(id, false);
                    this.loadStage(id);
                    if($('step-' + id).getParent().getNext('span.b-master__icon-e') != undefined) {
                        this.redrawStage(stages[i+1] != undefined ? stages[i+1] : 'last', true);
                    } else {
                        this.agreeStage(i);
                        xajax_checkSbr(stages[i-1]);
                    }
                }
            }
            
            if(sbr_agree == true) {
                this.redrawStage('last', true);
            }
        }
    }
    
    this.loadStage = function(stage_id) {
        $$('.master-stage').hide();
        $('master-stage-' + stage_id).show();
    }
    
    this.agreeStage = function(pos) {
        stages_agree[pos] = true;
        if(pos > 0) {
            if($('step-' + stages[pos-1]).getParent().getNext('span.b-master__icon-e') == undefined) {
                var next_stage = stages[pos+1] != undefined ? stages[pos+1] : 0;
                if(pos == stages.length) {
                    next_stage = -1;
                }
                agree_cnt++;
                xajax_agreeStage(stages[pos-1],  next_stage);
                if(agree_cnt == stages.length) {
                    this.setSbrAgree(true);
                }
            } else if(pos != stages.length) {
                this.redrawStage(stages[pos+1] != undefined ? stages[pos+1] : 'last', true);
            }
        } else {
            this.redrawStage(stages[pos+1] != undefined ? stages[pos+1] : 'last', true);
        }
    }
    
    this.completeStage = function(stage_id) {
        var stage = $('step-' + stage_id);
        var ok    = new Element('span', {'class' : 'b-master__icon-e b-master__icon-e_ok', 'style': 'display:none'});
        stage.getParent().grab(ok, 'after');
    }
    
    this.redrawStage = function(id, link) {
        var element = $('step-' + id);
        if(element.getElement('a')) {
            var name_stage = element.getElement('a').get('html');
        } else {
            var name_stage = element.get('html');
        }
        name_stage = name_stage.replace('<', '&lt;');
        name_stage = name_stage.replace('>', '&gt;');
        name_stage = name_stage.replace('"', '&quot;');
        if(id == 'last') name_stage = 'Условия вашей работы<br />и расчет гонорара';
        
        if(link == true) {
            var a = new Element('a', {'class' : 'b-master__link', 'href' : 'javascript:void(0)', 'onclick' : 'mstage.draw(\'' + id + '\', event)', 'html' : name_stage});
            element.set('html', '');
            element.grab(a);
            element.getParent('li').removeClass('b-master__item_current');
        } else {
            element.getParent('li').addClass('b-master__item_current');
            element.set('html', name_stage);
        }
    }
    
    this.setActive = function(act) {
        active = act;
    }
    this.getActive = function() {
        return active;
    }
    this.setStage = function(stage_id) {
        stages.push(stage_id);
    }
    this.getStages = function() {
        return stages;
    }
    this.setSbrAgree = function(agree) {
        sbr_agree = agree;
    }
    this.getSbrAgree = function() {
        return sbr_agree;
    } 
}

function setFormType(obj) {
    if($(obj).getProperty('filled') == 1) {
        $('form_type_alert').removeClass('b-layout__txt_hide');
        $('agree_btn').addClass('b-button_disabled');
    } else {
        $('form_type_alert').addClass('b-layout__txt_hide');
        $('agree_btn').removeClass('b-button_disabled');
    }
    
    var form_type = $(obj).get('value');
    taxes.options.form_type =  form_type;
    if(finance) finance.options.form_type = form_type;
    taxes.recalc();
    $('type_payments_btn').getElements('input[type=radio]').each(function(el){
        var for_disable =  el.getProperty('for_disable').split('|');
        el.set('disabled', false);
        for(var i = 0; i < for_disable.length;i++) {
            if(for_disable[i] == form_type) {
                el.set('checked', false);
                el.set('disabled', true);
                break;
            }
        }
        
        if(el.get('checked') == true) changePaymentSys(el);
        
    });
    
    $$('.b-tax-info').addClass('b-tax__level_hide');
    $$('.tax-type-' + form_type).removeClass('b-tax__level_hide');
}

function changePaymentSys(obj) {
    var sys = $(obj).get('value');
    
    if(sys == WMR_SYS || sys == YM_SYS) {
        taxes.setShemesTax(13, [0.03, 0.03]);
    } else {
        taxes.setShemesTax(13, undefined);
    }
    
    taxes.recalc(sys);
}

function sendReservePdrd(sbr_id) {
    var sys     = $('cost_sys').get('value');
    var sys_set = $('cost_sys_set').get('value');
    if(sys != sys_set) {
        xajax_updCostSys(sbr_id, sys);
    } else {
        submitReservePdrd();
    }
}

function submitReservePdrd() {
    var sys = $('cost_sys_set').get('value');
    if(sys == WMR_SYS) {
        submitForm($('reserveFormWM'));
    } else if(sys == YM_SYS) {
        submitForm($('reserveFormYM')); 
    } else {
        submitForm($('reserveForm'));
    }
}

function changeCostSysPdrd(obj) {
    var sys = $(obj).get('value');
    $('cost_sys').set('value', sys);
}

function frlRefuse(sbr_id) {
    //console.debug(sbr_id);
    $('rrbox' + sbr_id).setStyle('display', 'block');
    $('rrtext' + sbr_id).set('value', '');
}

function removeDraftSbr(sbr_id) {
    var draft = $('draftsbr_' + sbr_id);
    var fadeOut = new Fx.Morph(draft, {
        onComplete: function(){
            draft.dispose();
        }
    });
    fadeOut.start({
        'height': 0,
        'padding-bottom': 0
    });
}

function changeFrlRezType(emp_rez_type) {
    var frl_id = $('frl_db_id').get('value');
    if(frl_id > 0) {
        xajax_checkFrlRezType(frl_id, emp_rez_type);
    }
}

function toggle_arb() {
    $$('.b-button-multi__item').removeClass('b-button-multi__item_active');
    if( $('b-button-more')) {
        var elm = $('b-button-more').getParent().getElement('.b-shadow');
    }
    if(elm) {
        if(! elm.hasClass('b-shadow_hide')) {
            $('b-button-more').getParent().removeClass('b-button-multi__item_active');
            elm.addClass('b-shadow_hide');
        }
    }
    
    
    JSScroll($('arbitrage_form'));
    $('arbitrage_form').toggleClass('b-shadow_hide');
    
}
function check_arb(check) {
    if(check) { 
        $('send_arbitrage').removeClass('b-button_disabled'); 
    } else {
        $('send_arbitrage').addClass('b-button_disabled');  
    }
    
}

function _new_checkWMDoc() {
    xajax_checkWMDoc();
    if($('act_error')) $('act_error').set('html', '');
}

function _new_clearCheckWMDoc() {
    if($('wmdoc_alert')) $('wmdoc_alert').dispose();
    $('submit_btn').removeClass('b-button_rectangle_color_disable');
    //$('act_error').set('html', '');
}

function submitForm(form, param) {
    if(form.submitting===1) return false;
    if(form.onsubmit && form.onsubmit()===false) return false;
    if(param!=null) {
        for(var k in param) {
            form[k].value = param[k];
        }
    }
    form.submitting=1;
    form.submit();
}

function changeCostSys(sys, cost, sum) {
    $$('.b-tax-wm_ym').dispose();
    $$('.tax-cost').addClass('b-tax_hide');
    $('budget_stage').set('html', BUDGET);
    $('tax_ammount').set('html', sum);
    
    if(sys == SYS_WMR) {
        var descr = 'Комиссия Webmoney';
        var text  = 'Комиссия за вывод в  Webmoney';
    } else if(sys == SYS_YM) {
        var descr = 'Комиссия Яндекс.Деньги';
        var text  = 'Комиссия за вывод в Яндекс.Деньги';
    } else if(sys == SYS_FM) {
        $('budget_stage').set('html', BUDGET_FM);
        $('tax_ammount').set('html', COST_FM);
        $$('.tax-cost-' + sys).removeClass('b-tax_hide');
        return false;
    } else {
        $$('.tax-cost-all').removeClass('b-tax_hide');
        return false;
    }
    $$('.tax-cost-all').removeClass('b-tax_hide');
    
    html  = '   <div class="b-tax__txt b-tax__txt_width_160 b-tax__txt_inline-block">';
    html += '       <div class="i-shadow i-shadow_inline-block i-shadow_margleft_-16">';
    html += '           <span class="b-shadow__icon b-shadow__icon_quest"></span>';
    html += '           <div class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_top_15 b-shadow_hide b-moneyinfo">';
    html += '               <div class="b-shadow__right">';
    html += '                   <div class="b-shadow__left">'
    html += '                       <div class="b-shadow__top">';
    html += '                           <div class="b-shadow__bottom">';
    html += '                               <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">';
    html += '                                   <div class="b-shadow__txt">' + descr + '</div>';
    html += '                               </div>';
    html += '                           </div>';
    html += '                       </div>';
    html += '                   </div>';
    html += '               </div>';
    html += '               <div class="b-shadow__tl"></div>';
    html += '               <div class="b-shadow__tr"></div>';
    html += '               <div class="b-shadow__bl"></div>';
    html += '               <div class="b-shadow__br"></div>';
    html += '               <span class="b-shadow__icon b-shadow__icon_close"></span>';
    html += '               <span class="b-shadow__icon b-shadow__icon_nosik"></span>';
    html += '           </div>';
    html += '       </div>';
    html += '       ' + descr;
    html += '   </div>';
    html += '   <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="tax_wmr_ym">&minus; ' + cost + ' руб.</div>';
    html += '   <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11">3</div';
    
    
    var elm = new Element('div', {'class':'b-tax__level b-tax__level_padbot_12 b-tax__level_padtop_15 b-tax-payment b-tax-wm_ym', 'html': html});
    
    $('tax_sum').grab(elm, 'before');
    init_shadow_btn();
    
}

function setExecUser(login) {
    var combouser = ComboboxManager.getInput("frl");
    var users = combouser.columns[0].getElements("div.b-combo__user");
    
    for(var i = 0;i < users.length; i++) {
        var l = users[i].getElement('.b-combo__userlogin').get('text');
        if(l == login) {
            users[i].addClass(combouser.HOVER_CSS); 
            combouser.itemHighlightFromMouse = 0;
            combouser.onEnter();
            break;
        }
    }
}

var Taxes = new Class({
    Implements: [Events, Options],
    
    options: {
        'scheme_type':      0,
        'form_type':        1,
        'user':             'frl',
        'cost':             0,
        'rating':           0,
        'schemes_jury':     [],
        'schemes_phys':     [],
        'schemes':          [] //схемы расчета налогов
    },
    
    initialize: function(options) {
        this.setOptions(options);
        this.recalc();  
    },
    
    setShemesTax: function(id, taxes) {
        this.options.schemes_phys[1][id] = taxes;
        this.options.schemes_phys[2][id] = taxes;
        this.options.schemes_phys[4][id] = taxes;
        this.options.schemes_phys[5][id] = taxes;
    },
    
    /**
     * @param sys - в какой валюте вывести итоговую сумму
     * по умолчанию - рубли
     * если sys = 1 - FM
     */
    recalc: function(sys) {
        $$('.sbr_schemes').hide();
        $$('.taxrow-class').addClass('b-tax__level_hide'); // Открывать будем посчитанные
        var cost_sys = " руб.";
        var cost = null, 
        cost_total = this.options.cost,
        SCHEMES = ( this.options.form_type == 1 ? this.options.schemes_phys : this.options.schemes_jury ),
        STYPE = this.options.scheme_type;
        // если аккредитив, то эта функция не нужна
        if (STYPE == 1) {
            return;
        }
        tax = SCHEMES[STYPE]['t'][0];
        
        $$('.sch_' + STYPE).show();
        cost_total = isNaN(cost_total) ? 0 : cost_total;
        
        cost_sum = cost_total;
        cost_tax = cost_total;
        cost =  cost_total;
        
        for(k in SCHEMES[STYPE]) {
            if(SCHEMES[STYPE][k] == undefined) continue;
            if (!$('taxrow_' + STYPE + '_' + k)) continue;
            if(k == 17 || k == 16 || k == 12 || k == 7) continue;
            tx = mny(cost_total * SCHEMES[STYPE][k][0]);
            cost_tax -= tx; 
        }
        
        for(k in SCHEMES[STYPE]) {
            if(SCHEMES[STYPE][k] == undefined) continue;
            if (!$('taxrow_' + STYPE + '_' + k)) continue;
            if(k == 't') continue;
            tx = mny(cost_total * SCHEMES[STYPE][k][0]);
            if(k == 3) {
                var cost = cost - tx;
            }
            if(k == 13) {
                tx = mny(cost * SCHEMES[STYPE][k][0]);
            }
            if(k == 17 || k == 16 || k == 12 || k == 7) { //НДФЛ
                tx = mny(cost_tax * SCHEMES[STYPE][k][1]);
                tx = Math.floor(tx);
            }
            cost_sum -= tx; 
            $('taxsum_' + STYPE + '_' + k).set('html', fmt(mny(tx)) + cost_sys);
            $('taxper_' + STYPE + '_' + k).set('html', mny(SCHEMES[STYPE][k][1]*100));
            $('taxrow_' + STYPE + '_' + k).removeClass('b-tax__level_hide');
        }
        
        if(this.options.user != 'frl') {
            cost_sum = tax * cost_total;
        }
        
        $('sch_' + STYPE + '_f').set('html', fmt(mny(cost_total)) + cost_sys);
        // перерасчет в FM
        if (sys == FM_SYS) {
            cost_sys = " руб.";
            cost_sum = cost_sum ;
        }
        if($('cost_total'))
            $('cost_total').set('html', v2f(cost_sum) + cost_sys);
        if($('rating_total'))
            $('rating_total').set('html', this.options.rating);
    }
});


function view_sbr_popup(id_popup) {
    $(id_popup).getElement('.b-shadow').removeClass('b-shadow_hide');
    var overlay = new Element('div', {'class': 'b-shadow__overlay b-shadow__overlay_bg_black', 'id':'b-shadow_sbr__overlay'});
    $(id_popup).grab(overlay, 'after');
}

function sbr_check_num_only( evt ) {
    evt = ( evt ) ? evt : ( ( window.event ) ? event : null );        
    if( ( evt.keyCode < 48 || evt.keyCode > 57 ) ) {
        evt.CancelBubble = true;
        evt.returnValue = false;
        return false;
    }

    return true;                
}

function sbr_check_enter_sms_code() {
    if($('sbr_sms_code').get('value').length>=1 && $('sbr_feedback_text').get('value').length>=1 && $('ops_type').get('value')!='') {
        $('sbr_btn').removeClass('b-button_disabled');
        $('sbr_btn').addClass('b-button_flat_green');
    } else {
        $('sbr_btn').removeClass('b-button_flat_green');
        $('sbr_btn').addClass('b-button_disabled');

    }
}

window.addEvent('domready', function(){
    var $agree = $('sbr_create_agree_emp');
    if (!$agree) {
        return;
    }
    var $submitForm = $('submit_form');

    var $rq1 = $('rq1');
    var $rq2 = $('rq2');
    if ($rq1 && $rq2) {
        $rq1.addEvent('change', rqChange);
        $rq2.addEvent('change', rqChange);
    }

    function rqChange () {
        sbrRezTypeSelected = $rq1.get('checked') || $rq2.get('checked');
    }

    $agree.addEvent('change', agreeChanged);
    $agree.addEvent('click', agreeChanged);
    
    function agreeChanged () {
        if (sbrRezTypeSelected && $agree.get('checked')) {
            $submitForm.removeClass('b-button_disabled');
        } else {
            $submitForm.addClass('b-button_disabled');
        }
    }
});


window.addEvent('domready', function(){
    var $agree = $('sbr_agree_frl');
    if (!$agree) {
        return;
    }
    var $agreeBtn = $('send_btn') || $('agree_btn');
    $agree.addEvent('change', agreeChanged);
    $agree.addEvent('click', agreeChanged);
    
    function agreeChanged () {
        if ($agree.get('checked') && !sbrDisableButton) {
            $agreeBtn.removeClass('b-button_disabled');
        } else {
            $agreeBtn.addClass('b-button_disabled');
        }
    }
});


window.addEvent('domready', function(){
    if ($('stage_add_comment') !== null) {
        CKEDITOR.on('instanceReady', function(e) {
            CKEDITOR.instances['ckeditor_comments'].on("beforeCommandExec", function(event) {
                if (event.data.name === 'entersubmit') {
                    for (var instanceName in CKEDITOR.instances) {
                        CKEDITOR.instances[instanceName].updateElement();
                    }
                }
            });
        });
    }

    var $addCommentBtn = $('stage_add_comment');
    if (!$addCommentBtn) {
        return;
    }
    
    var editor;
    
    $addCommentBtn.addEvent('click', addComment);
    $('ckeditor_comments').addEvent('focus', function(){333});
    
    function addComment() {
        var textLength = 0, filesCount = 0;
        try {
            // длина введенного текста
            textLength = CKEDITOR.instances['ckeditor_comments'].getData().length;
            // количество загруженных вайлов
            filesCount = stageCommentAttachedfiles.container.getElements('input[name="attaches[]"]').length;
        } catch(e){};
        
        if (!textLength && !filesCount) { // если не введено сообщение и не загружен ни один файл
            //$('cke_ckeditor_comments') && $('cke_ckeditor_comments').addClass('b-combo__input_error');
            try {
                CKEDITOR.instances['ckeditor_comments'].fire('show_error_frame');
            } catch(e){};
            
            return;
        }
        
        // отправляем комментарий
        var sbrID = this.get('sbr_id');
        var formID = 'msg_form' + sbrID;
        for (var instanceName in CKEDITOR.instances) {
            CKEDITOR.instances[instanceName].updateElement();
        }
        submitForm(document.getElementById(formID));
    }
});


/**
 * проверка количества символов в формах ввода отзывов
 * если больше чем надо, то кнопка отправки формы деактивируется
 */
window.addEvent('domready', function(){
    var $completeFrm = $('completeFrm');
    if (!$completeFrm) {
        return;
    }
    
    var $submitBtn = $completeFrm.getElement('#submit_btn'), // кнопка отправки отзыва
        $feedback = $completeFrm.getElement('textarea[name="feedback[descr]"]'), // поле для ввода отзыва
        $sbrFeedback = $completeFrm.getElement('textarea[name="sbr_feedback[descr]"]'); // поле для ввода отзыва сервису
        
    if (!$submitBtn || !$feedback) {
        return;
    }
    
    // поле для отзыва партнеру
    $feedback.addEvent('tawl_overlimit', checkForm);
    $feedback.addEvent('tawl_underlimit', checkForm);
    
    // поле для ввода отзыва о сервисе
    if ($sbrFeedback) {
        $sbrFeedback.addEvent('tawl_overlimit', checkForm);
        $sbrFeedback.addEvent('tawl_underlimit', checkForm);
    }
    
    // проверяем длину отзывов
    function checkForm () {
        var feedbackOverLimit = $feedback.get('value').length > $feedback.get('rel');
        if ($sbrFeedback) { // это поле есть только на последнем этапе
            var sbrFeedbackOverLimit = $sbrFeedback.get('value').length > $sbrFeedback.get('rel');
        }
        if (feedbackOverLimit || sbrFeedbackOverLimit) {
            $submitBtn.addClass('b-button_disabled');
        } else {
            $submitBtn.removeClass('b-button_disabled');
        }
    }
});


/**
 * контролируем возможность менять реквизиты исполнителя в зависимости от суммы бюджета
 */
window.addEvent('domready', function(){
    var $physRadio, // радиокнопка физическое лицо
        $juriRadio, // радиокнопка юридическое лицо
        $resRadio, // радиокнопка резидент РФ
        $notResRadio; // радиокнопка НЕ резидент РФ

    if (typeof(_SBR) === 'undefined') {
        return;
    }

    $physRadio = $('form_type_phys');
    $juriRadio = $('form_type_juri');
    $resRadio = $('rq1');
    $notResRadio = $('rq2');

    $physRadio && $physRadio.addEvent('change', setPhys);
    $juriRadio && $juriRadio.addEvent('change', setJuri);
    $resRadio && $resRadio.addEvent('change', setRes);
    $notResRadio && $notResRadio.addEvent('change', setNotRes);

    function setPhys () {
        _SBR.frlFormType = 1;
        checkReqvs();
    }
    function setJuri () {
        _SBR.frlFormType = 2;
        checkReqvs();
    }
    function setRes () {
        _SBR.frlNotRes = 0;
        checkReqvs();
    }
    function setNotRes () {
        _SBR.frlNotRes = 1;
        checkReqvs();
    }

    function checkReqvs () {
        if (_SBR.empFormType == 1 && _SBR.empNotRes && _SBR.maxcostPhys < _SBR.cost && _SBR.cost < _SBR.maxcost) {
            $juriRadio.set('checked', true);
            disableTypeBlock();
        } else if (_SBR.empFormType == 1 && !_SBR.empNotRes && _SBR.maxcost < _SBR.cost) {
            $resRadio.set('checked', true);
            disableResBlock();
        // заказчик резидент РФ и физическое лицо, а бюджет нажодится в пределах 5000$ - 50000$
        } else if (_SBR.empFormType == 1 && !_SBR.empNotRes && _SBR.maxcostPhys < _SBR.cost && _SBR.cost < _SBR.maxcost) {
            if (_SBR.frlFormType == 1) { // исполнитель физическое лицо
                $resRadio.set('checked', true);
                disableResBlock(); // нельзя редактировать резидентство
            } else { // исполнитель юридическое лицо
                enableResBlock();
            }
            if (_SBR.frlNotRes) { // исполнитель не резидент РФ
                $juriRadio.set('checked', true);
                disableTypeBlock(); // нельзя редактировать физ/юр
            } else { // исполнитель резидент РФ
                enableTypeBlock(); // можно редактировать физ/юр
            }
        }
    }

    function disableResBlock () {
        $resRadio.set('disabled', true);
        $notResRadio.set('disabled', true);
    }
    function enableResBlock () {
        $resRadio.set('disabled', false);
        $notResRadio.set('disabled', false);
    }
    function disableTypeBlock () {
        $physRadio.set('disabled', true);
        $juriRadio.set('disabled', true);
    }
    function enableTypeBlock () {
        $physRadio.set('disabled', false);
        $juriRadio.set('disabled', false);
    }

    checkReqvs();

});