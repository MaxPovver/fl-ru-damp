window.addEvent('domready', function(e) {
    var reg = /#(o|a|s|p|n|c)_(\d*)/;
    var arr = null;
    
    if (arr = reg.exec(window.location.hash)) {
        hlAnchor(arr[1],arr[2]);
    }
    
    opinionsFormBtns();
});

function hlAnchor(mode, id) {
    $$('.ops-one').removeClass('ops-one-this');
    $$('.ops-answer').removeClass('ops-one-this');
    $$('.ops-nr-stage').removeClass('ops-one-this');
    
    if(mode == 'o') {
        var IDName = "opinion_" + id; 
    }
    if(mode == 'c') {
        var IDName = "comment_" + id;
    }
    if(mode == 'n') {
        var IDName = "new_advice_" + id;
    }
    if(mode == 'p') {
        var IDName = "p_stage_" + id;
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

// No risk opinions functions

function setStar(section, id, value) {
    if($(section+'_stars_'+id).getAttribute('rel') != 'edit') return false;
    $(section+'_stars_'+id).setAttribute('class','stars-vote vote-'+value+' stars-vote-a');
    $('message-edit').getElement('input[name='+section+'_rate]').set('value', value);
}

function feedbackEditForm(stage_id, id, login, msg, p_rate, n_rate, a_rate, err) {
    if(!msg) {
        closeForm();
        if($chk($('rating'+id))) {
            $('p_stars_'+id).addClass('stars-vote-a').setAttribute('rel','edit');
            $('n_stars_'+id).addClass('stars-vote-a').setAttribute('rel','edit');
            $('a_stars_'+id).addClass('stars-vote-a').setAttribute('rel','edit');
        }
        $('message'+id).setStyle('display', 'none');
        c = $('message-tpl').clone();
        c.id = 'message-edit';
        c.setStyle('display', '');
        c.inject($('message'+id), 'after');
//        if($('rating'+id).getElement('table')) {
//            $('rating'+id).setStyle('display', 'none');
//            c = $('rating-tpl').clone();
//            c.id = 'rating-edit';
//            c.setStyle('display', '');
//            c.inject($('rating'+id), 'after');
//            $('rating'+id).getElement('table').clone().inject(c);
//            $('rating-edit').getElements('img').setStyle('cursor', 'pointer');
//            $('rating-edit').getElements('img').addEvent('click', setVars);
//        }
        xajax_getFeedback(stage_id, id, login);
        return;
    }
    
    $('message-edit').getElement('textarea').set('value', msg);
    $('message-edit').getElement('input[name=id]').set('value', id);
    $('message-edit').getElement('input[name=stage_id]').set('value', stage_id);
    $('message-edit').getElement('input[name=p_rate]').set('value', p_rate);
    $('message-edit').getElement('input[name=n_rate]').set('value', n_rate);
    $('message-edit').getElement('input[name=a_rate]').set('value', a_rate);
    $('message-edit').getElement('input[name=login]').set('value', login);
}

function setVars() {
    val = this.getAllPrevious().length;
    this.set('src', '/images/i_star_o.gif');
    this.getAllPrevious().set('src', '/images/i_star_o.gif');
    this.getAllNext().set('src', '/images/i_star_g.gif');
    
    fieldname = this.className;
    $('message-edit').getElement('input[name='+fieldname+']').set('value', val+1);
}

function closeForm() {
    $$('.stars-vote').removeClass('stars-vote-a');
    var votes = $$('.stars-vote');
    for (var i = 0; i < votes.length; i++){
        votes[i].setAttribute('rel','view');
    }
    if($chk($('message-edit'))) {
        $('message-edit').getPrevious().setStyle('display', '');
        $('message-edit').dispose();
        if($chk($('rating-edit'))) {
            $('rating-edit').getPrevious().setStyle('display', '');
            $('rating-edit').dispose();
        }
    }
}

function saveRating(err, stage_id, id, mesg, p_rate, n_rate, a_rate) {
    if(!stage_id) {
        frm = $('message-edit');
        frm.getElement('.errorBox').setStyle('display', 'none');
        frm.getElement('.errorBox span').set('html', '');
        id = frm.getElement('input[name=id]').get('value');
        stage_id = frm.getElement('input[name=stage_id]').get('value');
        if($chk($('rating'+id))) {
            p_rate = frm.getElement('input[name=p_rate]').get('value');
            n_rate = frm.getElement('input[name=n_rate]').get('value');
            a_rate = frm.getElement('input[name=a_rate]').get('value');
        }
        login = frm.getElement('input[name=login]').get('value');
        mesg = frm.getElement('textarea').get('value');
        xajax_editFeedback(stage_id, id, p_rate, n_rate, a_rate, mesg, login);
        return;
    }
    if(!err) {
        $('message'+id).set('html', mesg);
        if($chk($('rating-edit'))) {
            $('rating-edit').getElement('table').clone().replaces($('rating'+id).getElement('table'));
            $('rating'+id).getElements('img').removeEvents('click');
            $('rating'+id).getElements('img').setStyle('cursor', '');
        }
        closeForm();
    } else {
        $('message-edit').getElement('.errorBox').setStyle('display', '');
        $('message-edit').getElement('.errorBox span').set('html', err);
    }
}

function check_length(message) {
    var maxLen = 100000;
    if (message.value.length > maxLen)
    {
        alert('Слишком длинный текст!\n Максимальная длина текста - 100 000 символов.');
        message.value = message.value.substring(0, maxLen);
    }
}

// Other opinions functions

function setRating( obj, op_id ) {
    if($('error_msg') != undefined) $('error_msg').set('html', '');
    if ( typeof op_id == 'undefined' ) op_id = false;
    $(obj).getParent('.ops-type').getElements('li').removeClass('active');
    $(obj).getParent('li').addClass('active');
    var eid = op_id ? 'rating_edit_' + op_id : 'rating_add';
    $(eid).set('value', $(obj).getProperty('rel'));
}

function opinionCheckMaxLengthUpdater(obj) {
    if (!$(obj)) return;
    var text       = $(obj).get('value');
    
    $('opinion_max_length').set('html',  text.trim().length);
    
    if ( text.length > opinion_max_length ) {
        $(obj).getParent('.b-textarea').addClass('b-textarea_error');
        $('opinion_max_length').getParent().addClass('b-layout__txt_color_c10600');
        //opinionMaxLengthError( eid, max_length );
    } else if($(obj).getParent('.b-textarea').hasClass('b-textarea_error')) {
        $(obj).getParent('.b-textarea').removeClass('b-textarea_error');
        $('opinion_max_length').getParent().removeClass('b-layout__txt_color_c10600');
    }
}


function opinionCheckMaxLength( type, eid ) {
    if (!$(eid)) return;
    
    var text       = $(eid).get('value');
    var max_length = ( type == 'opinion' ) ? opinion_max_length : comment_max_length;
    
    if ( text.trim().length > max_length ) {
        //$(eid).set( 'value', text.substr( 0, max_length ) );
        opinionMaxLengthError( eid, max_length );
        
        var $submitBtn = $('opinion-comment-form-submit-btn');
        $submitBtn && $submitBtn.addClass('b-button_disabled');
    } else if ( text.trim().length === 0 ) {
        var $submitBtn = $('opinion-comment-form-submit-btn');
        $submitBtn && $submitBtn.addClass('b-button_disabled');
    } else {
        var $submitBtn = $('opinion-comment-form-submit-btn');
        $submitBtn && $submitBtn.removeClass('b-button_disabled');
    }
}

function opinionMaxLengthError( eid, max_length ) {
    //$('error_' + eid).set('html', opinion_error_limit.replace("???", max_length) );
}

function opinionCheckMaxLengthStart( type, eid ) {
    window['opinion_timer_' + eid] = window.setInterval( 'opinionCheckMaxLength(' + "'"+ type +"', " + "'"+ eid +"'" + ');',10 );
}

function opinionCheckMaxLengthStop( eid ) {
    window['opinion_timer_' + eid] = window.clearInterval( window['opinion_timer_' + eid] );
}

function opinionSubmitAddForm( sid, uid, from ) {
    var msg   = $('msg').get('value').trim();
    var error = false;
    
    if ( msg.length == 0 ) {
        opinionFormError( 'error_msg' );
        return false;
    }
    
    if($('rating_add').get('value') == "") {
        opinionFormError( 'error_msg', opinion_error_rating);
        return false;
    }
    
    if(!error) {
        $('btn-send-opinions').setProperty('onclick', 'return false;');
        xajax_AddOpinion( sid, uid, msg, $('rating_add').get('value'), 10, from );
    }
    
    return false;
}

function opinionSubmitEditForm( op_id, from ) {
    var msg = $('edit_msg_' + op_id).get('value').trim();
    
    if ( msg.length == 0 ) {
        opinionFormError( 'error_edit_msg_' + op_id );
    }
    else {
        xajax_EditOpinion( op_id, msg, $('rating_edit_' + op_id).get('value'), 1, from ); 
    }
    
    return false;
}

function opinionFormError( eid, text ) {
    if ($$('a.btnr')) {
        $$('a.btnr')[0].blur();
    }
    if(text == undefined) text = opinion_error_empty;
    $( eid ).set('html', text );
}

function opinionCommentSubmitForm( op_id, msg_id, from, isFeedback ) {
    var prefix = isFeedback ? 'feedback_' : '';
    var msg = $(prefix + 'edit_comm_' + op_id).get('value').trim();
    
    if ( msg.trim().length == 0 ) {
        opinionCommentFormError( op_id, isFeedback );
    } else {
        xajax_EditOpinionComm( op_id, msg_id, msg, from, isFeedback );
    }
    
    return false
}

function opinionCommentFormError( op_id, isFeedback ) {
    var el = $('error_edit_comm_' + op_id);
    el && el.set('html', comment_error_empty );
}



function submitProjectFeedback(element)
{
    var form = $(element).getParent('form');
    if(!form) return false;
    xajax_projectUpdateFeedback(xajax.getFormValues(form));
    return true;
}


function submitTservicesOrdersFeedback(element)
{
    var form = $(element).getParent('form');
    if(!form) return false;
    xajax_tservicesOrdersUpdateFeedback(xajax.getFormValues(form));
    return true;
}


function submitEditSBROp(s, n){
    if(s == undefined) s = null;
    if(n == undefined) n = null;
    var feedback_id = $('feedback_id').value;
    var login = $('login').value;
    var stage_id = $('stage_id').value;
    var votes = $$('#sbr_op_form input[type=radio]');
    var vote = null;
    for(var i = 0; i < votes.length; i++){
        if(votes[i].checked) vote = votes[i].value;
    }
    var msg = $('sbr_op_text').value;
    xajax_editFeedbackNew(feedback_id, msg, login, stage_id, vote, s, n);
}

function setVote(val){
    var votes = $$('#sbr_op_form input[type=radio]');
    for(var i = 0; i < votes.length; i++){
        if(votes[i].value == val){
            votes[i].checked = true;
            votes[i].getParent().addClass('b-post__voice-item_current');
        }else{
            votes[i].checked = false;
            votes[i].getParent().removeClass('b-post__voice-item_current');
        }
    }
}


function opinionChConuters(mid, pid) {
    var m,p;
    if(mid && (m=$(mid))) {
        m.innerHTML = parseInt(m.innerHTML) - 1;
    }
    if(pid && (p=$(pid))) {
        p.innerHTML = parseInt(p.innerHTML) + 1;
    }
}


function showOpinionsForm() {
    el = $('form_container');
    if (!el) return;
    el.getElement('.ops-add-full').toggleClass('ops-add-show');  
    
    opinionCheckMaxLengthStart('opinion', 'msg'); 
    
    if (el.getElement('.ops-add-full').hasClass('ops-add-show') && $('no_messages')) {
        $('no_messages').style.display = 'none' 
    } else if ($('no_messages')) { 
        $('no_messages').style.display = 'block'
    }
    
    $$('.ops-frm-toggler').hide();
    
    if ($$('.ops-frm-toggler').length > 1) {
        new Fx.Scroll(window).toElement(document.getElement('a[name=op_head]'));
    }
}

function hideOpinionsForm() {
    el = $('form_container');
    if (!el) return;
    
    el.getElement('.ops-add-full').toggleClass('ops-add-show');
    $$('.ops-frm-toggler').show();
    
    opinionCheckMaxLengthStop('msg');
    opinionsFormBtns();
}

function opinionCancelForm(opinion, comment, isFeedback) {
    var prefix = isFeedback ? 'feedback_' : '';
    if(comment == 0) {
        $(prefix + 'comment_' + opinion).removeChild($(prefix + 'ed_comm_form_' + opinion)); 
        $(prefix + 'opinion_btn_add_comment_' + opinion).setProperty('disabled', '');
        $(prefix + 'opinion_btn_add_comment_' + opinion).show();
    } else {
        $(prefix + 'comment_' + opinion).removeChild($(prefix + 'ed_comm_form_' + opinion));
        $(prefix + 'opinion_btn_edit_comment_' + opinion).setProperty('disabled', '');
        $(prefix + 'comment_content_' + opinion).setStyle('display', 'block');
    }
}

function opinionsFormBtns() {
    return false; // @deprecated
    el = $('form_container');
    if (!el) return;
    
    if (!$('opFormBtn2') && $$('#messages_container div.ops-one').length > 5 && el.getElement('.ops-add-in>a.btn')) {
        wr = new Element('div', {'id' : 'opFormBtn2', 'class' : 'ops-add ops-add-in'});

        btn2 = el.getElement('.ops-add-in>a.btn');
        btn2 = btn2.clone().inject($('messages_container'), 'after');

        wr.wraps(btn2);
    }
    
    if ($$('#messages_container div.ops-one').length > 5) {
        $$('#opFormBtn2 .ops-frm-toggler').show();
    } else {
        $$('#opFormBtn2 .ops-frm-toggler').hide();
    }
}

function reverseForm(id) {
    $('form_container_' + id).set('html', '');
    $('form_container_to_' + id).setStyle('display', 'block');
}

function opinionCloseAllForms () {
    $$('div[id^="feedback_ed_comm_form_"]').each(function(el){
        var id = el.get('id').slice(22);
        opinionCancelForm(id, el.get('comment'), true);
    });
    $$('div[id^="ed_comm_form_"]').each(function(el){
        var id = el.get('id').slice(13);
        opinionCancelForm(id, el.get('comment'));
    });
}