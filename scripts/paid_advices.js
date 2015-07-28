
window.addEvent('domready', function() {
    $$('a.advice-new').addEvent('click', newAdvice);
    
    if ($('link_work')) {
        var delayId = 0;
        $('link_work').addEvent('keyup', function() {
            if ($(this).get('value').length) {
                delayId = setTimeout(checkForm, 400);
            } else {
                checkForm();
            }
        });
        $('link_work').addEvent('keydown', function() {
            clearTimeout(delayId);
            delayId = null;
        });
    }
});


function adviceAddForm() {
    $('advice_text').set('value', '');
    
    $$('.advice-add-form').removeClass("b-textarea_hidden");
    $$('.advice-add-btn').hide();
    
    $$('.advice-status-sent').hide();
    
    $('manager_feedback').removeClass('b-post__txt_hide');
    
    return false;
}

function adviceAddFormClose() {
    $('advice_text').set('value', '');
    $$('.advice-add-form').addClass("b-textarea_hidden");
    $$('.advice-add-btn').show();
    
    $('manager_feedback').addClass('b-post__txt_hide');
    
    return false;
}


function newAdvice() {
    el = $(this);
    
    if (el.retrieve('lock')) {
        return;
    }
    
    el.store('lock', 1);
    
    f = el.getParent('.b-post').getElement('form');
    if (!f) {
        return false;
    }
    
    userto = f.getElement('input[name=user_to]').get('value');
    usmsg = f.getElement('textarea').get('value');
    
    xajax_NewAdvice(userto, usmsg);
}

function adviceRespBlock(advice, html) {
    var elm = new Element('div#block_advice_' + advice);
    elm.addClass('b-fon b-fon_width_full b-fon_margbot_10');
    elm.set('html', '<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">' + html + '</div><span class="b-fon__close" onclick="if(confirm(\'Вы уверены, что хотите удалить отзыв?\')) { xajax_DeleteAdvice(' + advice + '); $(this).getParent().destroy(); }"></span>');
    $('new_advice_' + advice).grab(elm, 'before');
    $('new_advice_' + advice).hide();
}

function adviceRespBlockDel(advice, html) {
    var elm = new Element('div#block_advice_'+advice);
    elm.addClass('b-fon b-fon_width_full b-fon_margbot_10');
    elm.set('html', '<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">' + html + '</div><span class="b-fon__close" onclick="$(this).getParent().destroy();"></span>');
    
    $('new_advice_' + advice).grab(elm, 'after');
    $('new_advice_' + advice).hide();
}

function restoreAdvice(advice) {
    $('block_advice_' + advice).destroy();  
    $('new_advice_' + advice).show();            
}

function newAdviceResp(err) {
    if (!err) {
        adviceAddFormClose();
        $$('.advice-status-sent').show();
    } else {
        alert(err);
    }
    
    $(el).eliminate('lock');
}

var iTimeoutId = null;

function calcSum(sum, scheme, min_cost, edit) {
    if(edit == undefined) edit = 1;
    var txt = new String();
    var num_format = /[^0-9,.]/;
    txt = sum.toString();
    sum = txt.replace(',', '.');
    if(scheme == 1) {
        $('sum_rub').set('value', sum);    
    } else {
        $('sum_fm').set('value', sum);  
    }
    if(iTimeoutId != null) {
        clearTimeout(iTimeoutId);
        iTimeoutId = null;
    }
    iTimeoutId = setTimeout(function() {
        checkForm(edit);
        if(num_format.exec(sum) != null) {
            $('error_budget_format').show();
            $('sum_rating').hide();
            return false;
        }
        
        if(sum < min_cost && scheme == 1) {
            $('error_budget').show();
            $('sum_fm').set('value', '');
            $('sum_rating').hide();
            return false;
        }
        if(sum <= 1 && scheme == 2) {
            $('error_budget').show();
            $('sum_rub').set('value', '');
            $('sum_rating').hide();
            return false;
        }
        $('error_budget').hide();
        $('error_budget_format').hide();
        $('sum_rating').show();
        xajax_CalcPaidAdvice(sum, scheme);   
    }, 400);
}

function select_upload_file(type) {
    $('attachedfiles_file_' + type).click();
}

function select_upload_file_ff3(obj) {
    obj.getParent().getElement('input').removeClass('b-file__input');
}

function select_file(obj, type) {
    var fname = obj.value;
    var fname = fname.substr(fname.lastIndexOf("\\", fname) + 1, fname.length);
    var ext   = getICOFile(fname.substr(fname.lastIndexOf('.', fname) + 1, fname.length).toLowerCase());
    var cls = 'b-icon_attach_'+ext;
    if(fname.length > 40 ) {
        fname = fname.substr(0, 18) + '...' + fname.substr(fname.length-18, 18);
    }
    $('fname_' + type).set('text', fname);
    $('fname_' + type).setProperty('href', 'javascript:void(0)');
    $('upload_txt_' + type).getElement('i').destroy();
    var i = new Element("i");
    i.addClass('b-icon').addClass(cls).addClass('b-icon_top_5').addClass('b-icon_ie7_top_1');
    $('upload_txt_' + type).grab(i, 'top');//removeClass().addClass(cls);
    $('upload_txt_' + type).setStyle('display', 'inline');
    $('upload_link_' + type).set('text', 'выбрать другой');
    if(type == 3) {
        select_link(2);
    }
}

function select_link(scheme) {
    if($('reverse_block') == undefined) return false;
    if(scheme == 1) {
        $('reverse_block').grab($('select_link'), 'before');
        $('reverse_block').grab($('upload_link_3_block'), 'after');
        
        $('attachedfiles_file_3').removeClass('b-file__input').addClass('b-file__input');
        $('upload_link_3').set('text', 'Загрузить');
        $('upload_txt_3').setStyle('display', 'none');
        $('select_link').hide();
        $('input_link').setStyle('display', 'inline');
        document.getElementById('link_work').focus();
    } else {
        $('reverse_block').grab($('select_link'), 'after');
        $('reverse_block').grab($('upload_link_3_block'), 'before'); 
        
        $('select_link').setStyle('display', 'inline');
        $('input_link').hide();
        
        $('link_work').set('value', '');
    }
}

function checkForm(isEdit) {
    if(isEdit == undefined) isEdit = 1;
    if(isEdit == 1 && $('save_launcher')) $('save_launcher').hide();
    var fnct = function(){
        $('add_mod').set('value', 1); 
        $('form_advice').submit(); 
    }
    var num_format = /[^0-9,.]/;
    
    if($('sum_rub').get('value') != '' && 
       num_format.exec($('sum_rub').get('value')) == null && num_format.exec($('sum_fm').get('value')) == null &&
       ($('attachedfiles_file_1').get('value') != '' || $('uploaded_1') != undefined) && 
       ($('attachedfiles_file_2').get('value') != '' || $('uploaded_2') != undefined) && 
       ($('attachedfiles_file_3').get('value') != '' || $('uploaded_3') != undefined || $('link_work').get('value') != '') &&
       $('isReqvsFilled').get('value') == 0) {
       
        $('btn_send_moderate').removeClass('b-button_rectangle_color_disable');
        document.getElementById('btn_send_moderate').onclick = fnct;
    } else {
        $('btn_send_moderate').removeClass('b-button_rectangle_color_disable');
        $('btn_send_moderate').addClass('b-button_rectangle_color_disable'); 
        document.getElementById('btn_send_moderate').onclick = "";
    }
}

function adminClearFilter(dateD, dateM, dateY) {
    $('form_filter').getElements('.i-txt').each(function(elm) {
       elm.value = '';
    });
    
    var input = $('fday');
    if ( input ) {
        input.set('value', '1');
    }
    
    var input = $('fyear');
    if ( input ) {
        input.set('value', dateY);
    }
    
    var input = $('tday');
    if ( input ) {
        input.set('value', dateD);
    }
    
    var input = $('tyear');
    if ( input ) {
        input.set('value', dateY);
    }
    
    $('mod_status').selectedIndex = 0;
    $('paid_status').selectedIndex = 0;
    
    $('tmnth').selectedIndex = dateM - 1;
    $('fmnth').selectedIndex = dateM - 1;
}

function scrollDeclineForm(elm) {
    var myFx = new Fx.Scroll(window).toElement(elm);
    elm.getElement('textarea').focus();
}