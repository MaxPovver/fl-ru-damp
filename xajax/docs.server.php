<?

$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/docs.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/docs.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/docs_files.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/docs_sections.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");

function AddSection($name) {
    //debugger(__FILE__, __LINE__, $name);
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $name = trim($name);
        if(!$name) {
            $objResponse->script("alert('Вы должны указать имя раздела');");
            return $objResponse;
        }
        $error = false;
        if (!$error = docs_sections::Add($name)) {
            $sections = docs_sections::getSections();
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_sections.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $sel_html = '<select id="$id_1" onclick="$(\'$id_2\').set(\'value\',this.value)">';
            $option_doc = '';
            foreach ($sections as $sec){
                $sel_html .= '<option value="'.$sec['id'].'">'.$sec['name'].'</option>';
                $option_doc .=  '<option value="'.$sec['id'].'">'.$sec['name'].'</option>';
            }
            $sel_html .= '</select>';
            $objResponse->assign('sel_s1_parent', 'innerHTML', strtr($sel_html,array('$id_1' => 'sel_s2','$id_2' => 'sel_s1')));
            $objResponse->assign('sel_s2_parent', 'innerHTML', strtr($sel_html,array('$id_1' => 'sel_s1','$id_2' => 'sel_s2')));
            $objResponse->assign('admin_sections', 'innerHTML', $html);
            $objResponse->assign('frm_section', 'innerHTML', $option_doc);
        } else {
            $objResponse->assign('admin_sections', 'innerHTML', $error);
        }
        
        $objResponse->script("$('docs-group-new').addClass('dgn-hide')");
        $objResponse->script("$('new_section_name').set('value','')");
        //$objResponse->script("$('frm_section').innerHTML($option_doc)");
        $objResponse->script("hideSectionEdit();");
    }
    return $objResponse;
}

function UpdateSection($id, $name) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $name = trim($name);
        if(!$name) {
            $objResponse->script("alert('Вы должны указать имя раздела');");
            return $objResponse;
        }
        $error = false;
        if (!$error = docs_sections::Update($id, $name)) {
            $id = null;
            $sections = docs_sections::getSections();
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_sections.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();

            $sel_html = '<select id="$id_1" onclick="$(\'$id_2\').set(\'value\',this.value)">';
            $option_doc = '';
            foreach ($sections as $sec){
                $sel_html .= '<option value="'.$sec['id'].'">'.$sec['name'].'</option>';
                $option_doc .=  '<option value="'.$sec['id'].'">'.$sec['name'].'</option>';
            }
            $sel_html .= '</select>';
            $objResponse->assign('sel_s1_parent', 'innerHTML', strtr($sel_html,array('$id_1' => 'sel_s2','$id_2' => 'sel_s1')));
            $objResponse->assign('sel_s2_parent', 'innerHTML', strtr($sel_html,array('$id_1' => 'sel_s1','$id_2' => 'sel_s2')));
            $objResponse->assign('admin_sections', 'innerHTML', $html);
            $objResponse->assign('frm_section', 'innerHTML', $option_doc);
        } else {
            $objResponse->assign('admin_sections', 'innerHTML', $error);
        }
        $objResponse->script("$('docs-group-new').addClass('dgn-hide')");
        $objResponse->script("$('new_section_name').set('value','')");
        $objResponse->script("hideSectionEdit();");
    }
    return $objResponse;
}

function SectionMoveTo($id, $direction = 'up') {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $error = false;
        if ($direction == 'up') {
            $error = docs_sections::MoveUp($id);
        } else {
            $error = docs_sections::MoveDown($id);
        }
        if (!$error) {
            $id = null;
            $sections = docs_sections::getSections();
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_sections.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('admin_sections', 'innerHTML', $html);
        } else {
            $objResponse->assign('admin_sections', 'innerHTML', $error);
        }
    }
    return $objResponse;
}

function AddDoc($name, $desc, $section_id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $name = trim($name);
        if(!$name) {
            $objResponse->script("alert('Вы должны указать имя документа');");
            return $objResponse;
        }
        $error = false;
        if (!$error = docs::Add($name, $desc, $section_id)) {
            $docs = docs::getDocs();
            $sections = docs_sections::getSections();
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_docs.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('admin_docs', 'innerHTML', $html);
        } else {
            $objResponse->assign('admin_docs', 'innerHTML', $error);
        }
    }
    return $objResponse;
}

function UpdateDoc($name, $desc, $section_id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $name = trim($name);
        if(!$name) {
            $objResponse->script("alert('Вы должны указать имя документа');");
            return $objResponse;
        }
        $error = false;
        if (!$error = docs::Update($name, $desc, $section_id)) {
            $docs = docs::getDocs();
            $sections = docs_sections::getSections();
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_docs.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('admin_docs', 'innerHTML', $html);
        } else {
            $objResponse->assign('admin_docs', 'innerHTML', $error);
        }
    }
    return $objResponse;
}

function EditDocFormPrepare($doc_id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $error = false;
        if ($doc = docs::getDoc($doc_id)) {
            $objResponse->assign('dosc_id_f', 'value', $doc_id);
            $objResponse->assign('frm_name', 'value', trim($doc['name']));
            $objResponse->assign('frm_desc', 'value', $doc['desc']);
            $objResponse->assign('doc_save_btn', 'value', 'Сохранить');
            $objResponse->assign('action_form', 'value', 'edit');
            $objResponse->script('$("attach_files_box").destroy();');
            $objResponse->script('$("docs_files_error").destroy();');
            $objResponse->script('$(\'frm_section\').set(\'value\',' . $doc['docs_sections_id'] . ');');
            if ($files = $doc['attach']) {
                $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_docs_uploaded_files.php';
                ob_start();
                include($page);
                $html = ob_get_contents();
                ob_end_clean();
                $objResponse->assign('form_files_added', 'innerHTML', $html);
            } else {
                $objResponse->assign('form_files_added', 'innerHTML', $error);
            }
        }
        $objResponse->script('showAddDocsForm(false, true);');
        $objResponse->script('JSScroll("docs_admin")');
    }
    return $objResponse;
}

function FileMoveTo($id, $direction = 'up') {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        if ($direction == 'up') {
            $doc_id = docs_files::MoveUp($id);
        } else {
            $doc_id = docs_files::MoveDown($id);
        }
        if ($files = docs_files::getDocsFiles($doc_id)) {
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_docs_uploaded_files.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('form_files_added', 'innerHTML', $html);
        } else {
            $objResponse->assign('form_files_added', 'innerHTML', 'Не возожно получить список файлов');
        }
    }
    return $objResponse;
}

function DeleteFile($id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        if (!($error = docs_files::Delete($id))) {
            $objResponse->script('$("file_'.$id.'").destroy()');
        } else {
            $objResponse->assign('form_files_added', 'innerHTML', 'Ошибка при удалении файла');
        }
    }
    return $objResponse;    
}

function DeleteEditFile($id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        if($id == 0) {
            $objResponse->script('$("file_'.$id.'").destroy();');
            return $objResponse;         
        } else {
            $objResponse->script('$("attach_files_'.$id.'").destroy();');
            $objResponse->script('$("file_'.$id.'").destroy();');
            
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/CFile.php";
            
            $tmp = new CFile();
            $tmp->GetInfoById($id);
            $tmp->Delete($id);
        }
    }
    return $objResponse; 
}

function RefreshUploadedFiles($doc_id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $error = false;
        if ($files = docs_files::getDocsFiles($doc_id)) {
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_docs_uploaded_files.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('form_files_added', 'innerHTML', $html);
        } else {
            $objResponse->assign('form_files_added', 'innerHTML', 'Не возожно получить список файлов');
        }
    }
    return $objResponse;
}

function DeleteSection($section_id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $error = false;
        if (!$error = docs_sections::Delete($section_id)) {
            $sections = docs_sections::getSections();
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_sections.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $sel_html = '<select id="$id_1" onclick="$(\'$id_2\').set(\'value\',this.value)">';
            $option_doc = '';
            foreach ($sections as $sec){
                $sel_html .= '<option value="'.$sec['id'].'">'.$sec['name'].'</option>';
                $option_doc .=  '<option value="'.$sec['id'].'">'.$sec['name'].'</option>';
            }
            $sel_html .= '</select>';
            $objResponse->assign('sel_s1_parent', 'innerHTML', strtr($sel_html,array('$id_1' => 'sel_s2','$id_2' => 'sel_s1')));
            $objResponse->assign('sel_s2_parent', 'innerHTML', strtr($sel_html,array('$id_1' => 'sel_s1','$id_2' => 'sel_s2')));
            $objResponse->assign('frm_section', 'innerHTML', $option_doc);
            $objResponse->assign('admin_sections', 'innerHTML', $html);
        } else {
            $objResponse->assign('admin_sections', 'innerHTML', $error);
        }
    }
    return $objResponse;
}

function DeleteSections($ids) {
    $ids = array_map('intval', explode(':', trim($ids,':')));
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $error = false;
        if (!$error = docs_sections::DeleteList($ids)) {
            $sections = docs_sections::getSections();
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_sections.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $sel_html = '<select id="$id_1" onclick="$(\'$id_2\').set(\'value\',this.value)">';
            $option_doc = '';
            foreach ($sections as $sec){
                $sel_html .= '<option value="'.$sec['id'].'">'.$sec['name'].'</option>';
                $option_doc .=  '<option value="'.$sec['id'].'">'.$sec['name'].'</option>';
            }
            $sel_html .= '</select>';
            $objResponse->assign('sel_s1_parent', 'innerHTML', strtr($sel_html,array('$id_1' => 'sel_s2','$id_2' => 'sel_s1')));
            $objResponse->assign('sel_s2_parent', 'innerHTML', strtr($sel_html,array('$id_1' => 'sel_s1','$id_2' => 'sel_s2')));
            $objResponse->assign('frm_section', 'innerHTML', $option_doc);
            $objResponse->assign('admin_sections', 'innerHTML', $html);
        } else {
            $objResponse->assign('admin_sections', 'innerHTML', $error);
        }
    }
    return $objResponse;
}


function MoveDocs($docs,$section) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $error = false;
        if (!$error = docs::Move($docs, $section)) {
            $docs = docs::getDocs();
            $sections = docs_sections::getSections();
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_docs.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('admin_docs', 'innerHTML', $html);

            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_sections.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('admin_sections', 'innerHTML', $html);
            
        } else {
            $objResponse->assign('admin_docs', 'innerHTML', $error);
        }
    }
    return $objResponse;
}

/**
 * Удаляет документы
 *
 * @param unknown_type $doc_id
 * @param unknown_type $doc_section_id
 * @return unknown
 */
function DeleteDoc($doc_id, $doc_section_id=false) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $error = false;
        if (!$error = docs::Delete($doc_id)) {
            $docs = docs::getDocs();
            $sections = docs_sections::getSections();
            $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_docs.php';
            ob_start();
            include($page);
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('admin_docs', 'innerHTML', $html);
            if($doc_section_id) {
                $cnt  = (int)count(docs::getDocs($doc_section_id));
                $objResponse->assign('count_docs_'.$doc_section_id, 'innerHTML', $cnt);
                if($cnt == 0) {
                    $objResponse->script('$("del_block_sec_'.$doc_section_id.'").setStyle("display", "inline");');
                }
            } else {
                $objResponse->script('document.location.href = "/service/docs/admin/"');    
            }
        } else {
            $objResponse->assign('admin_docs', 'innerHTML', $error);
        }
    }
    return $objResponse;
}

function DeleteDocHTML($doc_id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $doc = docs::getDoc($doc_id);
        $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_docs_delete_line.php';
        ob_start();
        include($page);
        $html = ob_get_contents();
        ob_end_clean();
        $html = str_replace("\n", "", $html);
        $objResponse->script('$("doc_line_'.$doc_id.'").set("html", "'.addslashes($html).'")');
        $objResponse->script("$('doc_line_$doc_id').addClass('orng')");
    }
    return $objResponse;
}

function GetDocHTML($doc_id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $doc = docs::getDoc($doc_id);
        $sec = docs_sections::getSection($doc['docs_sections_id']);
        $doc['section_name'] = $sec['name'];
        $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_docs_line.php';
        ob_start();
        include($page);
        $html = ob_get_contents();
        ob_end_clean();
        $html = str_replace("\n", "", $html);
        $objResponse->script('$("doc_line_'.$doc_id.'").set("html", "'.addslashes($html).'")');
        $objResponse->script("$('doc_line_$doc_id').removeClass('orng')");
    }
    return $objResponse;
}

function DeleteSectionHTML($id, $num) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $sections = docs_sections::getSections();
        $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_sections.php';
        ob_start();
        include($page);
        $html = ob_get_contents();
        ob_end_clean();
        $objResponse->assign('admin_sections', 'innerHTML', $html);
    }
    return $objResponse;
}

function GetSectionHTML($id, $num) {
    $id = null;
    $objResponse = new xajaxResponse();
    if(hasPermissions('docs')) {
        $sections = docs_sections::getSections();
        $page = $_SERVER['DOCUMENT_ROOT'] . '/service/docs/admin_sections.php';
        ob_start();
        include($page);
        $html = ob_get_contents();
        ob_end_clean();
        $objResponse->assign('admin_sections', 'innerHTML', $html);
    }
    return $objResponse;
}

$xajax->processRequest();
?>