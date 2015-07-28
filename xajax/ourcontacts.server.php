<?
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/ourcontacts.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/contacts.php");

/**
* Получить информацию о контакте
*
* @param    integer $id     идентификатор контакта
*/
function GetContactInfo($id) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $c = contacts::getContactInfo($id);
        $objResponse->assign('fld_edit_id', 'value', $c['id']);
        $objResponse->assign('fld_edit_name', 'value', $c['name']);
        $objResponse->assign('fld_edit_surname', 'value', $c['surname']);
        $objResponse->assign('fld_edit_company', 'value', $c['company']);
        $objResponse->assign('fld_edit_note', 'value', $c['note']);
        $objResponse->script("xajax_GetGroupsForSelect({$c['group_id']}, 'fld_edit_group');");
        if($c['emails']) {
            $objResponse->assign('fld_edit_email', 'value', $c['emails'][0]);
            for($i=1; $i<count($c['emails']); $i++) {
                $objResponse->script("ContactsAddField('edit_contact_li_email','email','edit');");
                $objResponse->assign('fld_edit_email_'.$i, 'value', $c['emails'][$i]);
            }
        }
        if($c['phones']) {
            $objResponse->assign('fld_edit_phone', 'value', $c['phones'][0]);
            for($i=1; $i<count($c['phones']); $i++) {
                $objResponse->script("ContactsAddField('edit_contact_li_phone','phone','edit');");
                $objResponse->assign('fld_edit_phone_'.$i, 'value', $c['phones'][$i]);
            }
        }
        if($c['skypes']) {
            $objResponse->assign('fld_edit_skype', 'value', $c['skypes'][0]);
            for($i=1; $i<count($c['skypes']); $i++) {
                $objResponse->script("ContactsAddField('edit_contact_li_skype','skype','edit');");
                $objResponse->assign('fld_edit_skype_'.$i, 'value', $c['skypes'][$i]);
            }
        }
        if($c['icqs']) {
            $objResponse->assign('fld_edit_icq', 'value', $c['icqs'][0]);
            for($i=1; $i<count($c['icqs']); $i++) {
                $objResponse->script("ContactsAddField('edit_contact_li_icq','icq','edit');");
                $objResponse->assign('fld_edit_icq_'.$i, 'value', $c['icqs'][$i]);
            }
        }
        if($c['others']) {
            $objResponse->assign('fld_edit_other', 'value', $c['others'][0]);
            for($i=1; $i<count($c['others']); $i++) {
                $objResponse->script("ContactsAddField('edit_contact_li_other','other','edit');");
                $objResponse->assign('fld_edit_other_'.$i, 'value', $c['others'][$i]);
            }
        }
    }
    return $objResponse;
}

/**
* Добавление контакта
*
* @param    array   $frm    Данные контакта
*/
function AddContact($frm) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $error = 0;
        $name = trim(strip_tags(stripslashes($frm['fld_add_name'])));
        $surname = trim(strip_tags(stripslashes($frm['fld_add_surname'])));
        $company = trim(strip_tags(stripslashes($frm['fld_add_company'])));
        $note = trim(strip_tags(stripslashes($frm['fld_add_note'])));
        $group = intval($frm['fld_add_group']);

        if($name=='' || strlen($name)>250) {
            $error = 1;
            $objResponse->script("alert('Имя не может быть пустым и должно быть менее 250 символов');");
        }

        if($surname=='' || strlen($surname)>250) {
            $error = 1;
            $objResponse->script("alert('Фамилия не может быть пустым и должно быть менее 250 символов');");
        }

        if($group<1) {
            $error = 1;
            $objResponse->script("alert('Вы не выбрали группу');");
        }


        $emails = array();
        $frm['fld_add_email'] = trim(strip_tags(stripslashes($frm['fld_add_email'])));
        if($frm['fld_add_email']) array_push($emails, $frm['fld_add_email']);
        for($i=1; $i<5; $i++) {
            $frm['fld_add_email_'.$i] = trim(strip_tags(stripslashes($frm['fld_add_email_'.$i])));
            if($frm['fld_add_email_'.$i]) array_push($emails, $frm['fld_add_email_'.$i]);
        }
        reset($emails);
        if(!$emails) {
            $error = 1;
            $objResponse->script("alert('Email не может быть пустым');");
        }
        foreach($emails as $email) {
            if(!is_email($email)) {
                $error = 1;
                $objResponse->script("alert('Неправильно введен email');");
            }
        }    

        $phones = array();
        $frm['fld_add_phone'] = trim(strip_tags(stripslashes($frm['fld_add_phone'])));
        if($frm['fld_add_phone']) array_push($phones, $frm['fld_add_phone']);
        for($i=1; $i<5; $i++) {
            $frm['fld_add_phone_'.$i] = trim(strip_tags(stripslashes($frm['fld_add_phone_'.$i])));
            if($frm['fld_add_phone_'.$i]) array_push($phones, $frm['fld_add_phone_'.$i]);
        }    

        $skypes = array();
        $frm['fld_add_skype'] = trim(strip_tags(stripslashes($frm['fld_add_skype'])));
        if($frm['fld_add_skype']) array_push($skypes, $frm['fld_add_skype']);
        for($i=1; $i<5; $i++) {
            $frm['fld_add_skype_'.$i] = trim(strip_tags(stripslashes($frm['fld_add_skype_'.$i])));
            if($frm['fld_add_skype_'.$i]) array_push($skypes, $frm['fld_add_skype_'.$i]);
        }    

        $icqs = array();
        $frm['fld_add_icq'] = trim(strip_tags(stripslashes($frm['fld_add_icq'])));
        if($frm['fld_add_icq']) array_push($icqs, $frm['fld_add_icq']);
        for($i=1; $i<5; $i++) {
            $frm['fld_add_icq_'.$i] = trim(strip_tags(stripslashes($frm['fld_add_icq_'.$i])));
            if($frm['fld_add_icq_'.$i]) array_push($icqs, $frm['fld_add_icq_'.$i]);
        }    

        $others = array();
        $frm['fld_add_other'] = trim(strip_tags(stripslashes($frm['fld_add_other'])));
        if($frm['fld_add_other']) array_push($others, $frm['fld_add_other']);
        for($i=1; $i<5; $i++) {
            $frm['fld_add_other_'.$i] = trim(strip_tags(stripslashes($frm['fld_add_other_'.$i])));
            if($frm['fld_add_other_'.$i]) array_push($others, $frm['fld_add_other_'.$i]);
        }    

        if(!$error) {
            $contact['name'] = $name;
            $contact['surname'] = $surname;
            $contact['company'] = $company;
            $contact['group'] = $group;
            $contact['note'] = $note;
            $contact['emails'] = $emails;
            $contact['phones'] = $phones;
            $contact['skypes'] = $skypes;
            $contact['icqs'] = $icqs;
            $contact['others'] = $others;
            contacts::addContact($contact);
            $objResponse->script("alert('Контакт успешно добавлен'); window.location='/siteadmin/contacts';");
        }
    }
	return $objResponse;
}

/**
* Редактирование контакта
*
* @param    array   $frm    Данные контакта
*/
function EditContact($frm) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $error = 0;
        $name = trim(strip_tags(stripslashes($frm['fld_edit_name'])));
        $surname = trim(strip_tags(stripslashes($frm['fld_edit_surname'])));
        $company = trim(strip_tags(stripslashes($frm['fld_edit_company'])));
        $note = trim(strip_tags(stripslashes($frm['fld_edit_note'])));
        $group = intval($frm['fld_edit_group']);

        if($name=='' || strlen($name)>250) {
            $error = 1;
            $objResponse->script("alert('Имя не может быть пустым и должно быть менее 250 символов');");
        }

        if($surname=='' || strlen($surname)>250) {
            $error = 1;
            $objResponse->script("alert('Фамилия не может быть пустым и должно быть менее 250 символов');");
        }

        if($group<1) {
            $error = 1;
            $objResponse->script("alert('Вы не выбралю группу');");
        }


        $emails = array();
        $frm['fld_edit_email'] = trim(strip_tags(stripslashes($frm['fld_edit_email'])));
        if($frm['fld_edit_email']) array_push($emails, $frm['fld_edit_email']);
        for($i=1; $i<5; $i++) {
            $frm['fld_edit_email_'.$i] = trim(strip_tags(stripslashes($frm['fld_edit_email_'.$i])));
            if($frm['fld_edit_email_'.$i]) array_push($emails, $frm['fld_edit_email_'.$i]);
        }
        reset($emails);
        if(!$emails) {
            $error = 1;
            $objResponse->script("alert('Email не может быть пустым');");
        }
        foreach($emails as $email) {
            if(!is_email($email)) {
                $error = 1;
                $objResponse->script("alert('Неправильно введен email');");
            }
        }    

        $phones = array();
        $frm['fld_edit_phone'] = trim(strip_tags(stripslashes($frm['fld_edit_phone'])));
        if($frm['fld_edit_phone']) array_push($phones, $frm['fld_edit_phone']);
        for($i=1; $i<5; $i++) {
            $frm['fld_edit_phone_'.$i] = trim(strip_tags(stripslashes($frm['fld_edit_phone_'.$i])));
            if($frm['fld_edit_phone_'.$i]) array_push($phones, $frm['fld_edit_phone_'.$i]);
        }    

        $skypes = array();
        $frm['fld_edit_skype'] = trim(strip_tags(stripslashes($frm['fld_edit_skype'])));
        if($frm['fld_edit_skype']) array_push($skypes, $frm['fld_edit_skype']);
        for($i=1; $i<5; $i++) {
            $frm['fld_edit_skype_'.$i] = trim(strip_tags(stripslashes($frm['fld_edit_skype_'.$i])));
            if($frm['fld_edit_skype_'.$i]) array_push($skypes, $frm['fld_edit_skype_'.$i]);
        }    

        $icqs = array();
        $frm['fld_edit_icq'] = trim(strip_tags(stripslashes($frm['fld_edit_icq'])));
        if($frm['fld_edit_icq']) array_push($icqs, $frm['fld_edit_icq']);
        for($i=1; $i<5; $i++) {
            $frm['fld_edit_icq_'.$i] = trim(strip_tags(stripslashes($frm['fld_edit_icq_'.$i])));
            if($frm['fld_edit_icq_'.$i]) array_push($icqs, $frm['fld_edit_icq_'.$i]);
        }    

        $others = array();
        $frm['fld_edit_other'] = trim(strip_tags(stripslashes($frm['fld_edit_other'])));
        if($frm['fld_edit_other']) array_push($others, $frm['fld_edit_other']);
        for($i=1; $i<5; $i++) {
            $frm['fld_edit_other_'.$i] = trim(strip_tags(stripslashes($frm['fld_edit_other_'.$i])));
            if($frm['fld_edit_other_'.$i]) array_push($others, $frm['fld_edit_other_'.$i]);
        }    

        if(!$error) {
            $contact['id'] = $frm['fld_edit_id'];
            $contact['name'] = $name;
            $contact['surname'] = $surname;
            $contact['company'] = $company;
            $contact['group'] = $group;
            $contact['note'] = $note;
            $contact['emails'] = $emails;
            $contact['phones'] = $phones;
            $contact['skypes'] = $skypes;
            $contact['icqs'] = $icqs;
            $contact['others'] = $others;
            contacts::editContact($contact);
            $objResponse->script("alert('Контакт успешно сохранен'); window.location='/siteadmin/contacts';");
        }
    }
	return $objResponse;
}

/**
* Удаление контакта
*
* @param    integer $contact_id идентификатор контакта
*/
function DeleteContact($contact_id) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        contacts::deleteContact($contact_id);
        $objResponse->script("alert('Контакт успешно удален'); window.location = '/siteadmin/contacts';");
    }
    return $objResponse;
}

/**
* Удаление контактов
*
* @param    integer $contacts_id идентификаторы контактов для удаления
*/
function DeleteContacts($contacts_id) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $contacts_id = $contacts_id['all_contacts'];
        if($contacts_id) {
            foreach($contacts_id as $k=>$v) {
                if(is_array($v)) { unset($contacts_id[$k]); }
            }
        }
        foreach($contacts_id as $k=>$v) {
            contacts::deleteContact($v);
        }
        $objResponse->script("alert('Контакты успешно удалены'); window.location = '/siteadmin/contacts';");
    }
    return $objResponse;
}

/**
* Добавление контактов в рассылку
*
* @param    integer $contacts_id идентификаторы контактов
*/
function AddContactsForMail($contacts_id) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $contacts_id = $contacts_id['all_contacts'];
        if($contacts_id) {
            foreach($contacts_id as $k=>$v) {
                if(is_array($v)) { unset($contacts_id[$k]); }
            }
        }
        if(!isset($_SESSION['mailer_contacts'])) { 
            $_SESSION['mailer_contacts'] = array(); 
            $contacts = array();
        } else {
            $contacts = $_SESSION['mailer_contacts'];
        }
        $new_contacts = array();
        foreach($contacts_id as $k=>$v) {
            $new_contacts[$v] = $v;
        }
        $_SESSION['mailer_contacts'] = $contacts+$new_contacts;
        $str_ids = '';
        foreach($_SESSION['mailer_contacts'] as $v) {
            $str_ids .= $v.',';
            $objResponse->assign('all_contacts_'.$v, 'checked', false);
            $objResponse->assign('w_contacts_id_'.$v, 'checked', true);                        
        }
        $str_ids = preg_replace("/,$/", "", $str_ids);
        $objResponse->assign('frm_fld_count_contacts', 'innerHTML', count($_SESSION['mailer_contacts']));
        $objResponse->assign('fld_mailer_contacts_id', 'value', $str_ids);
        $objResponse->script("alert('Контакты успешно добавлены в рассылку');");
    }
    return $objResponse;
}

/**
* Сохранение контактов для рассылки
*
* @param    array $data идентификаторы контактов и групп
*/
function SaveContactsMailer($data) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $groups_id = $data['w_groups_id'];
        $contacts_id = $data['w_contacts_id'];
        if($groups_id) {
            foreach($groups_id as $k=>$v) {
                if(is_array($v)) { unset($groups_id[$k]); }
            }
        }
        if($contacts_id) {
            foreach($contacts_id as $k=>$v) {
                if(is_array($v)) { unset($contacts_id[$k]); }
            }
        }
        $_SESSION['mailer_contacts'] = $contacts_id;
        $_SESSION['mailer_groups'] = $groups_id;

        $str_ids = '';
        if($_SESSION['mailer_contacts']) {
            foreach($_SESSION['mailer_contacts'] as $v) {
                $str_ids .= $v.',';
            }
        }
        $str_ids = preg_replace("/,$/", "", $str_ids);
        $objResponse->assign('frm_fld_count_contacts', 'innerHTML', count($_SESSION['mailer_contacts']));
        $objResponse->assign('fld_mailer_contacts_id', 'value', $str_ids);
    }
    return $objResponse;
}

/**
* Добавление контактов в рассылку по группе
*
* @param    integer $groups_id идентификаторы групп
*/
function AddContactsByGroupsForMail($groups_id) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $groups_id = $groups_id['all_groups'];
        if($groups_id) {
            foreach($groups_id as $k=>$v) {
                if(is_array($v)) { unset($groups_id[$k]); }
            }
        }
        if(!isset($_SESSION['mailer_contacts'])) { 
            $_SESSION['mailer_contacts'] = array(); 
            $contacts = array();
        } else {
            $contacts = $_SESSION['mailer_contacts'];
        }
        if(!isset($_SESSION['mailer_groups'])) { 
            $_SESSION['mailer_groups'] = array(); 
            $groups = array();
        } else {
            $groups = $_SESSION['mailer_groups'];
        }

        $new_contacts = array();
        $contacts_id = array();
        $new_groups = array();
        $groups_ids = array();
        foreach($groups_id as $k=>$v) {
            $gr_contacts = contacts::getContacts($v);
            if($gr_contacts) {
                foreach($gr_contacts as $c) {
                    $contacts_id[$c['id']] = $c['id'];
                }
            }
            $objResponse->assign('all_groups_'.$v, 'checked', false);
            $groups_ids[$v] = $v;
        }
        if($contacts_id) {
            foreach($contacts_id as $k=>$v) {
                $new_contacts[$v] = $v;
            }
        }
        if($groups_ids) {
            foreach($groups_id as $k=>$v) {
                $new_groups[$v] = $v;
            }
        }
        $_SESSION['mailer_contacts'] = $contacts+$new_contacts;
        $_SESSION['mailer_groups'] = $groups+$new_groups;

        $str_ids = '';
        foreach($_SESSION['mailer_contacts'] as $v) {
            $str_ids .= $v.',';
            $objResponse->assign('w_contacts_id_'.$v, 'checked', true);
        }
        $str_ids = preg_replace("/,$/", "", $str_ids);
        $objResponse->assign('frm_fld_count_contacts', 'innerHTML', count($_SESSION['mailer_contacts']));
        $objResponse->assign('fld_mailer_contacts_id', 'value', $str_ids);
        $objResponse->script("alert('Контакты из выбранных групп успешно добавлены в рассылку');");
    }
    return $objResponse;
}

/**
* Получить список групп
*/
function GetGroups() {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $html = '';
        $n = 1;
        $groups = contacts::getGroups();
        if($groups) {
            foreach($groups as $group) {
                $contacts = contacts::getContacts($group['id']);
                $html .= "<li>\n";
                $html .= '<span class="mc-g-del"><input type="button" value="Удалить" class="i-btn" onClick="xajax_DeleteGroup('.$group['id'].');" />&nbsp; <a href="javascript:void(0);" onclick="$(this).getParent(\'li\').removeClass(\'li-d\');" class="lnk-dot-666">Отменить</a>&nbsp; </span>'."\n";
                $html .= "<span class='mc-g-o'>".count($contacts)."&nbsp;&nbsp; <a href='' onClick=\"xajax_GetGroupTitle({$group['id']}); $(this).getParent('li').addClass('li-e'); return false;\"><img src='/images/ico-e-u.png' alt='Редактировать'></a>&nbsp;&nbsp; <a href='' onclick=\"".(!count($contacts)?"\$(this).getParent('li').addClass('li-d');":"")." return false;\"><img src='/images/btn-remove2.png' alt='Удалить'></a>&nbsp;&nbsp;</span>\n";
                $html .= "<span class='i-chk'><input type='checkbox' id='all_groups_{$group['id']}' name='all_groups[]' value='{$group['id']}' class='groups_id'></span>\n";
                $html .= "<span class='num'>{$n}.</span>\n";
                $html .= "<label for=''>{$group['title']}</label>\n";
                $html .= '<span class="edit"><input type="text" class="i-txt" value="" id="tab_edit_group_'.$group['id'].'"/> <input type="button" value="Сохранить" class="i-btn" onClick="ContactsUpdateGroup('.$group['id'].'); $(this).getParent(\'li\').removeClass(\'li-e\');" />&nbsp; <a href="javascript:void(0);" onclick="$(this).getParent(\'li\').removeClass(\'li-e\');" class="lnk-dot-666">Отменить</a></span>'."\n";
                $html .= "</li>\n";
                $n++;
            }
            $objResponse->assign('ul_group_list', 'innerHTML', $html);
            $objResponse->script('$("btn_group_list_emailer").setStyle("display","block");');
        }
    }
	return $objResponse;
}

/**
* Получить список групп для диалога рассылки
*/
function GetGroupsForMailerDialog() {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $html = '';
        $html_fld = '';
        if(!isset($_SESSION['mailer_groups'])) { $_SESSION['mailer_groups'] = array(); }
        $groups = contacts::getGroups();
        if($groups) {
            foreach($groups as $group) {
                $html .= '<a href="" class="g1'.(in_array($group['id'],$_SESSION['mailer_groups'])?' active':'').'" id="grm_'.$group['id'].'" onClick="ContactsSelectGroupForMail(\'grm_'.$group['id'].'\','.$group['id'].'); return false;">'.$group['title'].'</a> ';
                $html_fld .= '<input type="checkbox" id="w_groups_id_'.$group['id'].'" name="w_groups_id[]" value="'.$group['id'].'" style="display:none;" '.(in_array($group['id'],$_SESSION['mailer_groups'])?'checked':'').'>'."\n";
            }
            $html = preg_replace("/ $/","",$html);
            $html = $html.$html_fld;
            $objResponse->assign('dialog_groups_list', 'innerHTML', $html);
        }
    }
	return $objResponse;
}

/**
* Ставит/убирает выделение контактов для рассылки
*
* @param    integer $group_id   илентификатор группы
* @param    string  $action     действие check/uncheck
*/
function MailerToggleContacts($group_id, $action) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $contacts = contacts::getContacts($group_id);
        if($contacts) {
            switch($action) {
                case 'check':
                    foreach($contacts as $c) {
                        $objResponse->assign('w_contacts_id_'.$c['id'], 'checked', true);
                    }
                    break;
                case 'uncheck':
                    foreach($contacts as $c) {
                        $objResponse->assign('w_contacts_id_'.$c['id'], 'checked', false);
                    }
                    break;
            }
        }
    }
    return $objResponse;
}

/**
* Получить список групп в виде SELECT
*
* @param    integer $selected_group_id  ID выбранной группы
* @param    string  $element_id         ID <SELECT> для которого надо получить группы
*/
function GetGroupsForSelect($selected_group_id=0, $element_id) {
	session_start();
	$objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $html = '';
        $groups = contacts::getGroups();
        if($groups) {
            $objResponse->remove($element_id);
            $objResponse->insertAfter($element_id.'_label', 'select', $element_id);
            $objResponse->assign($element_id, 'name', $element_id);
            foreach($groups as $group) {
                $objResponse->create("$element_id", "option", $element_id.'_o_'.$group['id']);
                $objResponse->assign($element_id.'_o_'.$group['id'], "value", $group['id']);
                $objResponse->assign($element_id.'_o_'.$group['id'], "innerHTML", $group['title']);
                if($group['id']==$selected_group_id) {
                    $objResponse->assign($element_id.'_o_'.$group['id'], "selected", true);
                }
            }

        }
    }
	return $objResponse;
}

/**
* Удаление группы
*
* @param    integer идентификатор группы
*/
function DeleteGroup($group_id) {
    session_start();
    $objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        contacts::deleteGroup($group_id);
        $objResponse->script("xajax_GetGroups();");
        $objResponse->script("alert('Группа успешно удалена');");
    }
    return $objResponse;
}

/**
* Добавление новой группы
*
* @param    string  $title  название группы
*/
function AddGroup($title) {
    session_start();
    $objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $title = trim(strip_tags(stripslashes($title)));
        if($title=='' || strlen($title)>50) {
            $objResponse->script("alert('Название группы не может быть пустым и должно быть менее 50 символов');");
        } else {
            contacts::addGroup($title);
            $objResponse->script("xajax_GetGroups();");
            $objResponse->assign("tab_groups_new_group_name", "value", "");
            $objResponse->script("alert('Группа успешно добавлена');");
        }
    }
    return $objResponse;
}

/**
* Получить название группы
*
* @param    integer $group_id   идентификатор группы
*/
function GetGroupTitle($group_id) {
    session_start();
    $objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $group = contacts::getGroup($group_id);
        $objResponse->assign('tab_edit_group_'.$group_id, 'value', $group['title']);
    }
    return $objResponse;
}

/**
* Изменение группы
*
* @param    integer $id     идентификатор группы
* @param    string  $title  название группы
*/
function UpdateGroup($id, $title) {
    session_start();
    $objResponse = new xajaxResponse();
    if(hasPermissions('ourcontacts')) {
        $title = trim(strip_tags(stripslashes($title)));
        if($title=='' || strlen($title)>50) {
            $objResponse->script("alert('Название группы не может быть пустым и должно быть менее 50 символов');");
        } else {
            contacts::updateGroup($id,$title);
            $objResponse->script("xajax_GetGroups();");
            $objResponse->script("alert('Группа успешно сохранена');");
        }
    }
    return $objResponse;
}

$xajax->processRequest();

?>


