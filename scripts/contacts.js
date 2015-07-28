function ContactsAddNewGroup() {
    xajax_AddGroup($('tab_groups_new_group_name').get('value'));
}

function ContactsUpdateGroup(id) {
    xajax_UpdateGroup(id,$('tab_edit_group_'+id).get('value'));
}

function ContactsShowTab(id) {
    if(id) {
        $('tab_edit_contact').setStyle('visibility', 'hidden');
        $$('#tabs li').removeClass('active');
        $$('.tab_content').setStyle('display', 'none');

        $(id).toggleClass('active');
        $('content_'+id).setStyle('display', 'block');

        switch(id) {
            case 'tab_groups':
                xajax_GetGroups();
                break;
            case 'tab_add_contact':
                xajax_GetGroupsForSelect(0, 'fld_add_group');
                break;
        }
    } else {
        $('tab_edit_contact').setStyle('visibility', 'hidden');
        $$('#tabs li').removeClass('active');
        $$('.tab_content').setStyle('display', 'none');
    }
}

var max_count_fields = 5;

var count_add_fields = new Array();
var count_edit_fields = new Array();

count_add_fields['email'] = 0;
count_add_fields['phone'] = 0;
count_add_fields['skype'] = 0;
count_add_fields['icq'] = 0;
count_add_fields['other'] = 0;
count_add_fields['files'] = 0;

function ContactsDelField(id, type) {
    count_add_fields[type]--;
    $(id).dispose();
}

function ContactsAddFilesField(id) {
    if((count_add_fields['files']+1)<max_count_fields) {
        count_add_fields['files']++;
        for(i=1; i<=count_add_fields['files']; i++) {
            if(!$('mailer_li_file_'+i)) { num = i; }
        }
        li = new Element('li').set('id','mailer_li_file_'+num).injectAfter(id);
        li.set('html', '<input type="file" size="30" name="file_'+num+'"> <a href="" onClick="ContactsDelField(\'mailer_li_file_'+num+'\',\'files\'); return false;"><img src="/images/btns/btn-f-remove.png" alt=""></a></li>');
    }
}

function ContactsAddField(id,type, form) {
    if((count_add_fields[type]+1)<max_count_fields) {
        count_add_fields[type]++;
        for(i=1; i<=count_add_fields[type]; i++) {
            if(!$(form+'_contact_li_'+type+'_'+i)) { num = i; }
        }
        li = new Element('li').set('id',form+'_contact_li_'+type+'_'+num).injectAfter(id);
        switch(type) {
            case 'email':
                title = 'Почта';
                break;
            case 'phone':
                title = 'Телефон';
                break;
            case 'skype':
                title = 'Skype';
                break;
            case 'icq':
                title = 'ICQ';
                break;
            case 'other':
                title = 'Другое';
                break;
        }
        li.set('html','<label for="">'+title+':</label> <input type="text" value="" name="fld_'+form+'_'+type+'_'+num+'" id="fld_'+form+'_'+type+'_'+num+'" /> &nbsp;<a href="" onClick="ContactsDelField(\''+form+'_contact_li_'+type+'_'+num+'\',\''+type+'\'); return false;"><img src="/images/btns/btn-f-remove.png" alt=""></a>');
    }
}

function ContactsEditContact(id) {
    $('tab_edit_contact').setStyle('visibility', 'visible');
    $('tab_edit_contact').toggleClass('active');
    $('content_tab_edit_contact').setStyle('display', 'block');

    count_edit_fields['email'] = 0;
    count_edit_fields['phone'] = 0;
    count_edit_fields['skype'] = 0;
    count_edit_fields['icq'] = 0;
    count_edit_fields['other'] = 0;

    for(i=1; i<=max_count_fields; i++) {
        if($('edit_contact_li_email_'+i)) { $('edit_contact_li_email_'+i).dispose(); }
        if($('edit_contact_li_phone_'+i)) { $('edit_contact_li_phone_'+i).dispose(); }
        if($('edit_contact_li_skype_'+i)) { $('edit_contact_li_skype_'+i).dispose(); }
        if($('edit_contact_li_icq_'+i)) { $('edit_contact_li_icq_'+i).dispose(); }
        if($('edit_contact_li_other_'+i)) { $('edit_contact_li_other_'+i).dispose(); }
    }

    xajax_GetContactInfo(id);

    window.location = "#tabs";
}

function ContactsSelectAll(obj) {
    if(obj.get('checked')==true) {
        $$('#frm_contacts_list .contacts_id').set('checked',true);
    } else {
        $$('#frm_contacts_list .contacts_id').set('checked',false);
    }
}

function ContactsDeleteAll() {
    var is_checked = false;
    $$('#frm_contacts_list .contacts_id').each(function(el) { if(el.get('checked')==true) { is_checked = true; } });
    if(is_checked==true) {
        if(confirm('Вы действительно хотите удалить выбранные контакты?')) {
            xajax_DeleteContacts(xajax.getFormValues('frm_contacts_list'));
        }
    } else {
        alert('Вы не выбрали контакты которые хотите удалить');
    }
}

function ContactsSelectContactsForMail() {
    var is_checked = false;
    $$('#frm_contacts_list .contacts_id').each(function(el) { if(el.get('checked')==true) { is_checked = true; } });
    if(is_checked==true) {
        xajax_AddContactsForMail(xajax.getFormValues('frm_contacts_list'));
    } else {
        alert('Вы не выбрали контакты которые хотите добавить в рассылку');
    }
}

function ContactsSelectGroupsForMail() {
    var is_checked = false;
    $$('#frm_groups_list .groups_id').each(function(el) { if(el.get('checked')==true) { is_checked = true; } });
    if(is_checked==true) {
        xajax_AddContactsByGroupsForMail(xajax.getFormValues('frm_groups_list'));
    } else {
        alert('Вы не выбрали группы контактов которые хотите добавить в рассылку');
    }
}

function ContactsShowMailerDialog() {
    if($('ov-groups').getStyle('display')=='block') { 
        $('ov-groups').setStyle('display','none'); 
    } else { 
        xajax_GetGroupsForMailerDialog();
        $('ov-groups').setStyle('display','block'); 
    }
}

function ContactsSelectGroupForMail(obj_id, group_id) {
    obj = $(obj_id);
    if(!obj.hasClass('active')) {
        $('w_groups_id_'+group_id).set('checked',true);
        xajax_MailerToggleContacts(group_id,'check');
    } else {
        xajax_MailerToggleContacts(group_id,'uncheck');
        $('w_groups_id_'+group_id).set('checked',false);
    }
    obj.toggleClass('active');
}

function ContactsSelectAllForMail(obj) {
    if(obj.get('checked')==true) {
        $$('#w_contacts .w_contacts').each(function(el) { el.set('checked',true); });
    } else {
        $$('#w_contacts .w_contacts').each(function(el) { el.set('checked',false); });
    }
}

function ContactsSaveContactsMailer() {
    xajax_SaveContactsMailer(xajax.getFormValues('w_contacts'));
}

function toggleMailerFiles() {
    if($('mailer_files').getStyle('display')=='none' || $('mailer_files').getStyle('display')=='') {
        $('mailer_files').setStyle('display','');
    } else {
        $('mailer_files').setStyle('display','none');
    }
}

function ContactsCheckMailForm() {
    subject = $('fld_mailer_subject').get('value');
    subject = subject.replace(/(^\s+)|(\s+$)/g, "");
    if($('fld_mailer_contacts_id').get('value')=='') {
        alert('Вы должны выбрать получателей');
        return false;
    } else {
        return true;
    }
    if(subject=='') {
        alert('Тема email не может быть пустой');
        return false;
    } else {
        return true;
    }
}
