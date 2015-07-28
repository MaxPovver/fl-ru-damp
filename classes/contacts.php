<?php
/**
 * Подключаем файл с основными функциями системы
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
/**
 * Класс для работы с базой контактов в админке
 */
class contacts {

    const CONTACTS_PER_PAGE = 10; 

    /**
    * Получить список групп контактов
    *
    * @return   array   список групп контактов
    */
    function getGroups() {
        global $DB;
        $sql = "SELECT * FROM contacts_groups ORDER BY id";
        $groups = $DB->rows($sql);
        return ($groups?$groups:null);
    }

    /**
    * Удаление контакта
    *
    * @param    integer $contact_id     идентификатор контакта
    */
    function deleteContact($contact_id) {
        global $DB;
        $sql = "DELETE FROM contacts_fields WHERE contact_id=?i";
        $DB->query($sql, $contact_id);
        $sql = "DELETE FROM contacts WHERE id=?i";
        $DB->query($sql, $contact_id);
    }
    
    /**
    * Получить список контактов
    *
    * @param    array   $filter     Данные фильтра
    * @param    integer $page       Номер страницы
    * @return   array               список контактов
    */
    function getListContacts($filter=false, $page=0) {
        global $DB;
        if($page) {
            $limit = self::CONTACTS_PER_PAGE;
            $offset = $limit * ($page - 1);
            $limit_str = "LIMIT $limit OFFSET $offset";
        }
        $sorttype = "ASC";
        if($filter['sorttype']) {
            $sorttype = $filter['sorttype'];
        }
		if($filter['query']) {
            $query = $filter['query'];
            $filter_str = " WHERE c.name ILIKE ('%".pg_escape_string($query)."%') OR c.surname ILIKE ('%".pg_escape_string($query)."%') OR c.note ILIKE ('%".pg_escape_string($query)."%') OR c.company ILIKE ('%".pg_escape_string($query)."%') ";
            $sql = "SELECT DISTINCT contact_id FROM contacts_fields WHERE value ILIKE ('%".pg_escape_string($query)."%')";
            $fields = $DB->rows($sql);
            if($fields) {
                    $contact_ids_str = '';
                    foreach($fields as $field) {
                        $contact_ids_str .= $field['contact_id'].',';
                    }
                    $contact_ids_str = preg_replace("/,$/", "", $contact_ids_str);
                    if($contact_ids_str) {
                        $filter_str = $filter_str."OR c.id IN ({$contact_ids_str}) ";
                    }
            }
        }
        $order_field = "ORDER BY c.name ASC";
        if($filter['sort']) {
            switch ($filter['sort']) {
                case 'name':
                    $order_field = "ORDER BY c.name ".$sorttype;
                    break;
                case 'surname':
                    $order_field = "ORDER BY c.surname ".$sorttype;
                    break;
                case 'company':
                    $order_field = "ORDER BY c.company ".$sorttype;
                    break;
                case 'group':
                    $order_field = "ORDER BY g.title ".$sorttype;
                    break;
            }
        }
        $contacts = array();
        $sql = "SELECT c.*, g.title as group_title FROM contacts as c INNER JOIN contacts_groups as g ON g.id=c.group_id {$filter_str} {$order_field} {$limit_str}";
        $contacts = $DB->rows($sql);
        if($contacts) {
            if($filter['sort']=='email' || $filter['sort']=='skype' || $filter['sort']=='icq' || $filter['sort']=='phone') {
                switch($filter['sort']) {
                    case 'email':
                        $type = 1;
                        break;
                    case 'phone':
                        $type = 2;
                        break;
                    case 'skype':
                        $type = 3;
                        break;
                    case 'icq':
                        $type = 4;
                        break;
                }
                $f_c_id = "0,";
				foreach($contacts as $v) {
                    $f_c_id .= $v['id'].',';
                }
                $f_c_id = preg_replace("/,$/","",$f_c_id);
                if(!$filter['sorttype']) {
                    $sorttype = 'asc';
                } else {
                    $sorttype = $filter['sorttype'];
                }
                $a_f_ids = array();
                $ac_f_ids = array();
                array_push($a_f_ids, 0);
                $sql = "SELECT id, contact_id FROM contacts_fields WHERE type={$type} AND contact_id IN ({$f_c_id}) ORDER BY id ASC";
                $t = $DB->rows($sql);
				if($t) {
                    foreach($t as $v) {
                        if(!in_array($v['contact_id'], $ac_f_ids)) {
                            array_push($a_f_ids, $v['id']);
                        }
                        array_push($ac_f_ids, $v['contact_id']);
                    }
                }
                $s_f_ids = '';
                foreach($a_f_ids as $v) {
                    $s_f_ids .= $v.',';
                }
                $s_f_ids = preg_replace("/,$/","",$s_f_ids);
                $sql = "SELECT contact_id FROM contacts_fields WHERE id IN ({$s_f_ids}) ORDER BY value {$sorttype}";
                $fields = $DB->rows($sql);
                if($fields) {
                    $source_contacts = $contacts;
                    $contacts = array();
                    foreach($fields as $field) {
                        $s_contact = self::search_in_array($field['contact_id'], $source_contacts);
                        array_push($contacts,$s_contact);
                    }
                    if($source_contacts) {
                        foreach($source_contacts as $v) {
                            array_push($contacts,$v);
                        }
                    }
                }
            }
            foreach($contacts as $key=>$contact) {
                $contacts[$key]['emails'] = array();
                $contacts[$key]['phones'] = array();
                $contacts[$key]['skypes'] = array();
                $contacts[$key]['icqs'] = array();
                $contacts[$key]['others'] = array();
                $sql = "SELECT * FROM contacts_fields WHERE contact_id=?i ORDER BY id ASC";
                $fields = $DB->rows($sql, $contact['id']);
                if($fields) {
                    foreach($fields as $field) {
                        switch($field['type']) {
                            case 1:
                                // Email
                                array_push($contacts[$key]['emails'], $field['value']);
                                break;
                            case 2:
                                // Телефон
                                array_push($contacts[$key]['phones'], $field['value']);
                                break;
                            case 3:
                                // Skype
                                array_push($contacts[$key]['skypes'], $field['value']);
                                break;
                            case 4:
                                // ICQ
                                array_push($contacts[$key]['icqs'], $field['value']);
                                break;
                            case 5:
                                // Другое
                                array_push($contacts[$key]['others'], $field['value']);
                                break;
                        }
                    }
                }
            }
        }
        return $contacts;
    }

    /**
    * Поиск в массиве контактов
    *
    * @param    integer $id     идентификатор контакто который ищем
    * @param    array   $source массив в котором надо искать
    * @return   array           массив с информацией о контакте
    */
    function search_in_array($id, &$source) {
        foreach($source as $k=>$v) {
            if($v['id']==$id) {
                $c = $v;
                unset($source[$k]);
            }
        }
        return $c;
    }

    /**
    * Получить информацию о контакте
    *
    * @param    integer $id         ID контакта
    * @return   array               список контактов
    */
    function getContactInfo($id) {
        global $DB;
        $contact = array();
        $sql = "SELECT c.* FROM contacts as c WHERE id=?i";
        $contact = $DB->row($sql, $id);
        $contact['emails'] = array();
        $contact['phones'] = array();
        $contact['skypes'] = array();
        $contact['icqs'] = array();
        $contact['others'] = array();
        $sql = "SELECT * FROM contacts_fields WHERE contact_id=?i ORDER BY id ASC";
        $fields = $DB->rows($sql, $contact['id']);
        if($fields) {
            foreach($fields as $field) {
                switch($field['type']) {
                    case 1:
                       // Email
                       array_push($contact['emails'], $field['value']);
                       break;
                    case 2:
                       // Телефон
                       array_push($contact['phones'], $field['value']);
                       break;
                    case 3:
                       // Skype
                       array_push($contact['skypes'], $field['value']);
                       break;
                    case 4:
                       // ICQ
                       array_push($contact['icqs'], $field['value']);
                       break;
                    case 5:
                       // Другое
                       array_push($contact['others'], $field['value']);
                       break;
                }
            }
        }
        return $contact;
    }

    /**
    * Добавить контакт
    *
    * @param   array   $contact данные контакта
    */
    function addContact($contact) {
        global $DB;
        $sql = "INSERT INTO contacts(name, surname, company, note, group_id) VALUES(?, ?, ?, ?, ?i) RETURNING id;";
        $contact_id = $DB->val($sql, pg_escape_string($contact['name']), pg_escape_string($contact['surname']), pg_escape_string($contact['company']), pg_escape_string($contact['note']), $contact['group']);
		if($contact_id) {
            $sql = 'INSERT INTO contacts_fields(value, type, contact_id, is_main) VALUES';
            foreach($contact['emails'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',1,{$contact_id},".(!$k?'true':'false')."),";
            }
            foreach($contact['phones'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',2,{$contact_id},".(!$k?'true':'false')."),";
            }
            foreach($contact['skypes'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',3,{$contact_id},".(!$k?'true':'false')."),";
            }
            foreach($contact['icqs'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',4,{$contact_id},".(!$k?'true':'false')."),";
            }
            foreach($contact['others'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',5,{$contact_id},".(!$k?'true':'false')."),";
            }
            $sql = preg_replace("/,$/",";",$sql);
            $DB->query($sql);
        }
    }

    /**
    * Редактирование контакта
    *
    * @param   array   $contact данные контакта
    */
    function editContact($contact) {
        global $DB;
        $sql = "UPDATE contacts SET name=?, surname=?, company=?, note=?, group_id=?i WHERE id=?i";
        $contact_id = $DB->val($sql, pg_escape_string($contact['name']), pg_escape_string($contact['surname']), pg_escape_string($contact['company']), pg_escape_string($contact['note']), $contact['group'], $contact['id']);
		if($contact_id) {
            $sql = "DELETE FROM contacts_fields WHERE contact_id={$contact_id};\n";
            $sql .= 'INSERT INTO contacts_fields(value, type, contact_id, is_main) VALUES';
            foreach($contact['emails'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',1,{$contact_id},".(!$k?'true':'false')."),";
            }
            foreach($contact['phones'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',2,{$contact_id},".(!$k?'true':'false')."),";
            }
            foreach($contact['skypes'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',3,{$contact_id},".(!$k?'true':'false')."),";
            }
            foreach($contact['icqs'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',4,{$contact_id},".(!$k?'true':'false')."),";
            }
            foreach($contact['others'] as $k=>$v) {
                $sql .= "('".pg_escape_string($v)."',5,{$contact_id},".(!$k?'true':'false')."),";
            }
            $sql = preg_replace("/,$/",";",$sql);
            $DB->query($sql);
        }
    }

    /**
    * Получить список контактов в группе
    *
    * @param    ineger  $group_id   ID группы
    * @return   array               список контактов
    */
    function getContacts($group_id=0) {
        global $DB;
        if($group_id) {
            $group_sql = "WHERE group_id={$group_id}";
        }
        $sql = "SELECT * FROM contacts {$group_sql} ORDER BY id";
        $contacts = $DB->rows($sql);
        return ($contacts?$contacts:NULL);
    }

    /**
    * Удаление группы
    *
    * @param    integer $group_id   идентификатор группы
    */
    function deleteGroup($group_id) {
        global $DB;
        $sql = "DELETE FROM contacts_groups WHERE id=?i";
        $DB->query($sql, $group_id);
    }

    /**
    * Добавление группы
    *
    * @param    string $title   название группы
    */
    function addGroup($title) {
        global $DB;
        $sql = "INSERT INTO contacts_groups(title) VALUES (?)";
        $DB->query($sql, pg_escape_string($title));
    }

    /**
    * Получить информацию о группе
    *
    * @param    integer $group_id   идентификатор группы
    */
    function getGroup($group_id) {
        global $DB;
        $sql = "SELECT * FROM contacts_groups WHERE id=?i";
        return $DB->row($sql, $group_id);
    }

    /**
    * Добавить новую рассылку
    *
    * @param    string  $subject        тема email
    * @param    string  $message        текст email
    * @param    array   $attaches       прикрепленные файлы
    * @param    string  $contact_ids    идентификаторы получателей
    */
    function AddMail($subject, $message, $attaches, $contact_ids) {
        global $DB;
        $files = '';
        $fs = array();
        if($attaches['file']) {
            $f = new CFile($attaches['file']);
            $f->max_size = 2097152;
            $dir = get_login(get_uid(false));
            $f_name = $f->MoveUploadedFile($dir."/upload");
            array_push($fs,$f_name);
        }
        for($i=1; $i<=5; $i++) {
            if($attaches['file_'.$i]) {
                $f = new CFile($attaches['file_'.$i]);
                $f->max_size = 2097152;
                $dir = get_login(get_uid(false));
                $f_name = $f->MoveUploadedFile($dir."/upload");
                array_push($fs,$f_name);
            }
        }
        if($fs) {
            foreach($fs as $v) {
                $files .= $v.',';
            }
            $files = preg_replace("/,$/", "", $files);
        }
        $sql = "INSERT INTO contacts_mails(subject,message,attaches,contact_ids,user_id) VALUES(?, ?, ?,? , ?i)";
        $DB->query($sql, pg_escape_string($subject), pg_escape_string(nl2br($message)), $files, $contact_ids, get_uid(false));
    }

    /**
    * Получает списоок рассылок
    *
    * @return   array   список рассылок
    */
    function GetMails() {
        global $DB;
        $mails = false;
        $sql = "SELECT * FROM contacts_mails";
        return $DB->rows($sql);
    }

    /**
    * Удаление рассылки
    *
    * @param    integer $id идентификатор рассылки
    */
    function DeleteMail($id) {
        global $DB;
        $sql = "SELECT attaches,user_id FROM contacts_mails WHERE id=?i";
        $mail = $DB->row($sql, $id);
        $user = new users();
        $user->GetUser($user->GetField($mail['user_id'], $ee, 'login'));
        $m_files = preg_split("/,/",$mail['attaches']);
        if($m_files) {
            foreach($m_files as $a) {
        	    $f = new CFile();
                $f->Delete(0, $user->login.'/', $a);
		    }
        }
        $sql = "DELETE FROM contacts_mails WHERE id=?i";
        $DB->query($sql, $id);
    }

    /**
    * Изменение группы
    *
    * @param    integer $id      идентификатор группы
    * @param    string  $title   название группы
    */
    function updateGroup($id,$title) {
        global $DB;
        $sql = "UPDATE contacts_groups SET title=? WHERE id=?i";
        $DB->query($sql, pg_escape_string($title), $id);
    }

}
?>
