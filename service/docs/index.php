<?php


$stretch_page = true;
$showMainDiv  = true;
$rpath = "../../";

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/docs_sections.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/docs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/docs_files.php';
session_start();
get_uid();
$uid = get_uid();
$login = $_SESSION['login'];
$role = $_SESSION['role'];

//==========Разбор запроса====================
$request = getServiceRequest();

$page_title = "Шаблоны документов - фриланс, удаленная работа на FL.ru";

$css_file = "help.css";
$content = "content.php";
$header = "../../header.php";
$footer = "../../footer.html";

//===========Доступные страницы===============
switch ($request['action']) {
    case 'index':
        $content = "content.php";
        $categories = docs_sections::getSections();
        // Разбиваем на блоки по 4 категории в блоке
        if (( $is_category = ( is_array($categories) && count($categories) ) )) {
            $block = $i = 0;
            foreach($categories as $cat) {
                $cat_blocks[$block][] = $cat;
                $i++;
                if($i>=4) {
                    $i=0;
                    $block++;
                }
            }
        }
        
        $last_docs = docs::getLast(10);
        break;

    case 'section':
        $content = "section.php";
        $section = docs_sections::getSection((int)$request['id']);
        $docs = docs::getDocs((int)$request['id']);
        break;

    case 'document':
        $content = "document.php";
        $doc = docs::getDoc($request['id']);
        break;

    case 'search':
        $content = "search.php";
        $text_for_search = !empty($request['text']) ? $request['text'] : false;
        if ($text_for_search) {
            $text_for_search = stripslashes($text_for_search);
            $text_for_search = preg_replace("/\"/", "", $text_for_search);
            $text_for_search = preg_replace("/'/", "", $text_for_search);
            $text_for_search = ereg_replace(" +", " ", $text_for_search);
            $text_for_search = trim($text_for_search);
            $text_for_search = htmlspecialchars($text_for_search);
            $search_results = docs::Search($text_for_search);
        }
        break;

    case 'admin':
        if(!hasPermissions('docs')){
            include($rpath . '404.php');
            exit();
        }
        $content = "admin.php";
        $js_file = array( 'mAttach2.js' );
        $error   = false;
        $error_add_file = false;
        if ($request['is_post']) {
            
            // загрузка файлов, сначала грузим файлы
            if ($_FILES['attach']) {
                $attach = $_FILES['attach'];
                $files = array();
                if (is_array($attach) && !empty($attach['name'])) {
                    foreach ($attach['name'] as $key => $v) {
                        if (!$attach['name'][$key])
                            continue;
                        $tmp = new CFile(array(
                                    'name' => $attach['name'][$key],
                                    'type' => $attach['type'][$key],
                                    'tmp_name' => $attach['tmp_name'][$key],
                                    'error' => $attach['error'][$key],
                                    'size' => $attach['size'][$key]
                        ));
                        $tmp->max_size    = 10485760;
                        $tmp->server_root = true;
                        $tmp->MoveUploadedFile('/docs/');
                        $files_attache[] = $tmp;
                        if ($tmp->error && !$tmp->id) {
                            $error_add_file = $tmp->error;
                            $error = true;
                        }
                    }
                }
            }
            
            if(count($_POST['attach_files_id']) > 0) {
                foreach($_POST['attach_files_id'] as $key=>$value) {
                    $tmp = new CFile();
                    $tmp->max_size    = 10485760;
                    $tmp->server_root = true;
                    $tmp->GetInfoById($value);
                    $files_attache[] = $tmp;
                }
            }
            
            switch($_POST['action_form']) {
                case "add":
                    if(count($files_attache) == 0) {
                        $error_add_file = "Необходимо загрузить хотя бы один файл";
                        $error = true;
                    }
                    
                    if ($error !== false) {
                        $docs = docs::getDocs();
                        $sections = docs_sections::getSections();
                    } else {
                        $docs_id = docs::Add($_POST['name'], $_POST['desc'], intval($_POST['section']));
                        
                        if($docs_id) {
                            foreach($files_attache as $file) {
                                docs_files::Add($docs_id, $file->id, $file->original_name);    
                            }
                        }
                        
                        header("Location: " . $_SERVER['REQUEST_URI']);
                    }
                    
                    break;
                case "edit":
                    $files = docs_files::getDocsFiles(intval($_POST['dosc_id_f']));
                    
                    if(count($files) == 0 && count($files_attache) == 0) {
                        $error_add_file = "Необходимо загрузить хотя бы один файл";
                        $error = true;
                    }
                    
                    if(intval($_POST['dosc_id_f']) == 0) {
                        $error_add_file = "Ошибка";
                        $error = true;
                    }
                    
                    if ($error !== false) {
                        $docs = docs::getDocs();
                        $sections = docs_sections::getSections();
                    } else {
                        docs::Update(intval($_POST['dosc_id_f']), $_POST['name'], $_POST['desc'], intval($_POST['section']));
                        
                        foreach($files_attache as $file) {
                            docs_files::Add(intval($_POST['dosc_id_f']), $file->id, $file->original_name);    
                        }
                        
                        header("Location: " . $_SERVER['REQUEST_URI']);
                    }
                    break;
            }
            
        } else {
            $docs = docs::getDocs();
            $sections = docs_sections::getSections();
        }
        if(!$docs) $docs = array();
        if(!$sections) $section = array();
        break;

    default:
         include($rpath . '404.php');
         exit();
        break;
}
//===========Доступные страницы===============
//===========Выводим контент==================
include ($rpath . "template2.php");

//===========EXT_FUNCTIONS====================
function getServiceRequest() {
    $out = $_GET;
    if (!$out['action']) {
        $out['action'] = 'index';
    }
    $out['is_post'] = $_SERVER['REQUEST_METHOD'] == 'POST';
    return $out;
}

?>
