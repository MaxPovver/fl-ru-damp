window.addEvent('domready', function() {
    $$('.abuse-project-link').addEvent('click', function(){
        $('abuse_project_popup').toggleClass('b-shadow_hide');
    });
    
    $$('.abuse-btn-send').addEvent('click', function() {
        if( !$(this).hasClass('b-button_disabled')) {
            xajax_SendComplain($('project_id_abuse').get('value'), $('prj_abuse_id').get('value'), $('prj_abuse_msg').get('value'), $('abuse_resource_file').get('value'));
        }
        $(this).addClass('b-button_rectangle_color_disable');
    });
    
    var required = false; // если true - текст ввести обязательно
    
    $$('.abuse-cause-link').addEvent('click', function() {
        var cause  = $(this).getProperty('data-cause');
        var name   = $(this).get('text');
        var writed = $(this).getProperty('data-textarea');
        required = +(writed && $(this).getProperty('data-required'));
        var parent = $(this).getParent();

        if(writed != '1' && !confirm('Вы уверены, что хотите отправить жалобу?')) {
            return false;
        }

        $('form_abuse').hide();
        $$('.abuse-cause-link').each(function(el) {
            if(!$(el).hasClass('abuse-checked'))
                $(el).removeClass('b-layout__txt_hide');
        });
        $$('.abuse-check-name').each(function(el) {
            if(!$(el).hasClass('abuse-checked'))
                $(el).set('text', null);
        });
        $$('.abuse-check').each(function(el) {
            if(!$(el).hasClass('abuse-checked'))
                $(el).addClass('b-layout__txt_hide');
        });
        $$('.abuse-cause-block').each(function(el){
            if(!$(el).hasClass('abuse-checked'))
                $(el).removeClass('b-layout__txt_color_71');
        });

        $(this).addClass('b-layout__txt_hide');
        $('prj_abuse_id').set('value', cause);
        parent.getElement('.abuse-check-name').set('text', name);
        parent.getElement('.abuse-check').removeClass('b-layout__txt_hide');
        parent.addClass('b-layout__txt_color_71');
        
        $('abuse-cause-error').addClass('b-layout__txt_hide');

        if(writed == '1') {
            $('form_abuse').show();
            $(this).grab($('form_abuse'), 'after');
        } else {
            xajax_SendComplain($('project_id_abuse').get('value'), cause, name, '');
        }
        
        checkMsgLength();
    });
    
    
    $$('.abuse-employer-project-link').addEvent('click', function(){
        $('abuse_employer_project_popup').toggleClass('b-shadow_hide');
        $('abuse_moderator_project_popup').addClass('b-shadow_hide');
    });
    $$('.abuse-moderator-project-link').addEvent('click', function(){
        $('abuse_moderator_project_popup').toggleClass('b-shadow_hide');
        $('abuse_employer_project_popup').addClass('b-shadow_hide');
    });
    
    // проверяет длину введенного текста
    // но только для жалобы ДРУГОЕ
    function checkMsgLength ($button) {
        if (!required) {
            $$('#form_abuse .abuse-btn-send').removeClass('b-button_disabled');
            return;
        }
        var msgLength = $('prj_abuse_msg').get('value').trim().length;
        if (msgLength === 0) {
            $$('#form_abuse .abuse-btn-send').addClass('b-button_disabled');
        } else {
            $$('#form_abuse .abuse-btn-send').removeClass('b-button_disabled');
        }
    }
    
    if($('prj_abuse_msg')) {
        $('prj_abuse_msg').addEvent('change', checkMsgLength);
        $('prj_abuse_msg').addEvent('input', checkMsgLength);
        $('prj_abuse_msg').addEvent('keyup', checkMsgLength);
    }
});


/**
 * Сбросить выбранный раздел жалобы
 */
function abuseResetSelection()
{
    $('form_abuse').hide();
    $$('.abuse-cause-link').each(function(el) {
        if(!$(el).hasClass('abuse-checked'))
            $(el).removeClass('b-layout__txt_hide');
    });
    $$('.abuse-check-name').each(function(el) {
        if(!$(el).hasClass('abuse-checked'))
            $(el).set('text', null);
    });
    $$('.abuse-check').each(function(el) {
        if(!$(el).hasClass('abuse-checked'))
            $(el).addClass('b-layout__txt_hide');
    });
    $$('.abuse-cause-block').each(function(el){
        if(!$(el).hasClass('abuse-checked'))
            $(el).removeClass('b-layout__txt_color_71');
    });
}