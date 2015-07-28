<?php
/**
 * Интервью
 *
 */
$grey_articles = 1;
$g_page_id  = "0|34";
$stretch_page = true;
$showMainDiv  = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/interview.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles.php");

$mpath = dirname(__FILE__);
$rpath = realpath(dirname(__FILE__) . '/../' );

session_start();
$uid = get_uid();
if($uid) { // определяем, показывать ли вкладку НА МОДЕРАЦИИ ($articles_unpub)
    $_uid = (hasPermissions('articles')) ? null : $uid;
    $articles_unpub = (hasPermissions('articles')) ? articles::ArticlesCount(false, $_uid) : null;
}

if($_GET['id'] && !$_GET['newurl'] && !$_POST && !$_GET['task']) {
  $interview_info = interview::getInterview($uid, $_GET['id']);
  if($interview_info['id']) {
    $query_string = preg_replace("/id={$interview_info['id']}/", "", $_SERVER['QUERY_STRING']);
    $query_string = preg_replace("/^&/", "", $query_string);
    header ('HTTP/1.1 301 Moved Permanently');
    header ('Location: '.getFriendlyURL('interview', $interview_info['id']).($query_string ? "?{$query_string}" : ""));
    exit;
  }
}

$url_parts = parse_url($_SERVER['REQUEST_URI']);
if($_GET['id'] && !$_GET['task']) {
  $friendly_url = getFriendlyURL('interview', $_GET['id']);
  if(strtolower($url_parts['path'])!=$friendly_url) {
    header ('HTTP/1.1 301 Moved Permanently');
    header ('Location: '.$friendly_url);
    exit;
  }
}

$_page = __paramInit('string', null, 'page');
if (!$_page) $_page = __paramInit('string', 'page');

$id = __paramInit('int', 'id');
if(!$_page && $id) $_page = 'view';

$tab = $_page;
$task = __paramInit('string', 'task', 'task');
$year = __paramInit('int', 'yr');

$ord = __paramInit('string', 'ord');
$ord = !$ord ? 'date' : $ord;


$GET = $_GET;
foreach($GET as $k => $v) {
    switch($k) {
        case 'ord':
            $v = preg_replace('/(\W+)/si', '', $ord);
            $ord = $v;
            break;
        case 'id':
            $v = $id;
            break;
        case 'filter':
            $v = intval($v);
            $filter = $v;
            break;
    }
    $GET[$k] = $v;
}

$query = array();
if($ord != 'date') {
    $query['ord'] = $ord;
}
if($year) {
    $query['yr'] = $year;
}

$years = interview::getYears();

$page_title = "Интервью - фриланс, удаленная работа на FL.ru";
$page_descr = "Интервью - фриланс, удаленная работа на FL.ru";


//function toQueryString($array, $prefix = '&') {
//    if(!count($array)) return false;
//    $q = array();
//    foreach($array as $key => $value) {
//        $q[] = "$key=$value";
//    }
//    return $prefix . implode('&', $q);
//}

switch($task) {
    case 'checklogin':
        if(!hasPermissions('interviews')) exit();

        $login = __paramInit('string', null, 'login');
        include_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

        $result = array();
        $result['success'] = false;

        if($login) {
            $users = new users();
            $users->GetUser($login);

            if($users->uid) {
                $result['success'] = true;
                $result['user'] = array(
                    'login' => $users->login,
                    'uname' => iconv('CP1251', 'UTF-8', $users->uname),
                    'usurname' => iconv('CP1251', 'UTF-8', $users->usurname),
                );
            }
        }

        echo json_encode($result);

        exit();
        break;

    /* Создание интервью */
    case 'add':
        if(!hasPermissions('interviews')) exit();
        $result = array();
        $result['success'] = false;

        $login = __paramInit('string', null, 'login');
        $id = __paramInit('int', null, 'id');
        $is_jury = intval($_POST['is_jury'])==1?'t':'f';
        include_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

        $user = new users();
        $user->GetUser($login);

//        $txt = change_q_x(stripslashes($_POST['txt']), FALSE, false, 'b|br\s?\/?|i|p|ul|li|cut|s|h[1-6]{1}|img id="\d+"|p class="[qa]"', false, false);
        //$txt = pg_escape_string(stripslashes($_POST['txt']));

        //$txt = strip_tags($txt, '<p><b><strong><i><em><br><ul><li><ol><h1><h2><h3><h4><h5><h6><img><a><noindex>');
        $txt = iconv('UTF-8', 'CP1251', $_POST['txt']);
        $txt = __paramValue('ckedit', $txt);

        if(!$login || !$user->uid) $alert['login'] = 'Вы должны указать логин пользователя.';
        if(!$txt || $txt == '' ||is_empty_html($txt)) $alert['txt'] = 'Поле не должно быть пустым.';
        $attached = isset($_POST['attached']) ? $_POST['attached'] : array();

        $int = new interview();
        $files = new CFile();

        if(!isset($alert)) {
            /* Создание интервью */
            if(!$newid = $int->addInterview($user->uid, $txt, $attached, $is_jury)) {
                $alert['alert'] = 'Невозможно создать запись.';
            }
        }

        if(!isset($alert)) {
            $result['success'] = true;
            $result['newid'] = $newid;
        } else {
            $result['errorMessages'] = $alert;
            foreach($result['errorMessages'] as $k => $msg) {
                $result['errorMessages'][$k] = iconv('CP1251', 'UTF-8', $msg);
            }
        }

        echo json_encode($result);
        exit();
        
    /* Редактирование интервью */
    case 'edit' :
        if(!hasPermissions('interviews')) exit();
        $result = array();
        $result['success'] = false;

        $login = __paramInit('string', null, 'login');
        $id = __paramInit('int', null, 'id');
        $is_jury = intval($_POST['is_jury'])==1?'t':'f';
        include_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

        $user = new users();
        $user->GetUser($login);

//        $txt = change_q_x(stripslashes($_POST['txt']), FALSE, false, 'b|br\s?\/?|i|p|ul|li|cut|s|h[1-6]{1}|img id="\d+"|p class="[qa]"', false, false);
//        $txt = pg_escape_string(stripslashes($_POST['txt']));
//
//        $txt = strip_tags($txt, '<p><b><strong><i><em><br><ul><li><ol><h1><h2><h3><h4><h5><h6><img><a><noindex>');
        $txt = iconv('UTF-8', 'CP1251', $_POST['txt']);
        $txt = __paramValue('ckedit', $txt);

        if(!$login || !$user->uid) $alert['login'] = 'Вы должны указать логин пользователя.';
        if(!$txt || $txt == '' ||is_empty_html($txt)) $alert['txt'] = 'Поле не должно быть пустым.';
        $attached = isset($_POST['attached']) ? $_POST['attached'] : array();
        $rmfiles = isset($_POST['rmattaches']) && count($_POST['rmattaches']) ? $_POST['rmattaches'] : null;
        
        $int = new interview();
        $files = new CFile();

        if(!isset($alert)) {
            $interview = $int->getInterview($uid, $id);
            
            /* Обновление интервью */
            if(!$int->updateInterview($id, $user->uid, $txt, $attached, $is_jury)) {
                $alert['alert'] = 'Невозможно изменить запись.';
            }

            /* Удаление файлов, которые нужно удалить =) */
            if(!isset($alert) && $rmfiles) {
                foreach($rmfiles as $rf) {
                    $files->Delete($rf);
                    if($rf == $interview['main_foto']) {
                        $files->Delete(0, $interview['path'], 'sm_'.$interview['fname']);
                        $result['main_photo'] = array();
                    }
                }
            }
        }

        if(!isset($alert)) {
            $result['success'] = true;

            $int_files = $int->getInterviewFiles($id);
            $result['attaches'] = $int_files;
            $result['id'] = $id;
            $result['WDCPREFIX'] = WDCPREFIX;
            $result['user'] = array(
                'id' => $user->uid,
                'login' => $user->login,
                'fullname' => iconv('CP1251', 'UTF-8', $user->uname . ' ' .$user->usurname)
            );
            
            if($attached['main']) {
                $files->GetInfoById($attached['main']);
                $result['main_photo'] = array(
                    'id' => $files->id,
                    'path' => $files->path,
                    'fname' => $files->name
                );
            }
            $result['page_view'] = __paramInit('string', null, 'page_view');

            if($result['page_view']) {
                $int_text = $txt;
                $s = array();
                $r = array();
                if($int_files) foreach($int_files as $int_file) {
                    $url = WDCPREFIX . '/' . $int_file['path'] . $int_file['fname'];
                    $s[] = '<img id="'.$int_file['id'].'">';
                    $r[] = "<img id=\"{$int_file['id']}\" src=\"$url\">";
                }
                if(count($s) && count($r)) $int_text = str_replace($s, $r, $int_text);
                $result['txt'] = iconv('CP1251', 'UTF-8', $int_text);
            }
            
        } else {
            $result['errorMessages'] = $alert;
            foreach($result['errorMessages'] as $k => $msg) {
                $result['errorMessages'][$k] = iconv('CP1251', 'UTF-8', $msg);
            }
        }

        echo json_encode($result);
        exit();
        break;

    case 'del':
        if($_GET['token']!=$_SESSION['rand']) exit();
        if(!hasPermissions('interviews')) exit();

        $int = new interview();
        $interview = $int->getInterview($uid, $id);

        $files = new CFile();

        // удаляем основное фото
        if($interview['main_foto'] !== NULL) {
            $files->Delete($interview['main_foto']);
            // и миниатюру )
            $files->Delete(null, $interview['path'], 'sm_' . $interview['fname']);
        }
        
        // удаляем другие аттачи
        $attaches = $int->getInterviewFiles($interview['id']);
        if($attaches) foreach($attaches as $attach) {
            $files->Delete($attach['id']);
        }

        // удаляем интервью
        $int->delInterview($interview['id']);

        header('Location: ./');
        exit();
        break;

    case 'upload':
        if(!hasPermissions('interviews')) exit();

        $result = array();
        $result['success'] = false;

        $is_main = isset($_FILES['main_foto']);

        $farr = isset($_FILES['main_foto']) ? $_FILES['main_foto'] : (isset($_FILES['attach']) ? $_FILES['attach'] : null);
        $file = new CFile($farr);
        $file->allowed_ext = array("gif", "jpg", "jpeg", "png");

        $file->resize = 1;
        $file->proportional = 1;
        $file->server_root = 1;
        
        if(isset($_FILES['main_foto'])) {
            $file->max_image_size = array('width'=>800, 'height'=>1000, 'less'=>1);
        } elseif(isset($_FILES['attach'])) {
            $file->max_image_size = array('width'=>720, 'height'=>1000, 'less'=>1);
        }


        if($file->name && $file->error) {
            $alert = $file->error[0];
        } else {
            $file->MoveUploadedFile('about/interview/');
            if (!isNulArray($file->error)) {
                $alert = "Файл не удовлетворяет условиям загрузки";
            }
        }

        $fileid = $file->id;
        $filepath = $file->path;
        $filename = $file->name;
        
        if(isset($_FILES['main_foto']) && !isset($alert)) {
            $resf = $file->img_to_small('sm_' . $file->name, array('width' => 180, 'height' => 180), TRUE);
            if(!$resf) {
                $alert = $file->error[0];
            }
        }

        if(isset($alert)) {
            $result['errorMessage'] = iconv('CP1251', 'UTF-8', $alert);
        } else {
            $result['success'] = true;
            $result['file'] = array(
                'id' => $fileid,
                'path' => $filepath,
                'fname' => $filename,
                'is_main' => $is_main
            );
        }

        echo json_encode($result);

        exit();
        break;
    
    case 'get-interview' :
        if(!hasPermissions('interviews')) exit();

        $int = new interview();
        $interview = $int->getInterview($uid, $id);
        $interview['txt'] = iconv('CP1251', 'UTF-8', $interview['txt']);
        $interview['uname'] = iconv('CP1251', 'UTF-8', $interview['uname']);
        $interview['usurname'] = iconv('CP1251', 'UTF-8', $interview['usurname']);
        $interview['is_jury']  = iconv('CP1251', 'UTF-8', $interview['is_jury']);

        $resp['interview'] = $interview;
        $att = $int->getInterviewFiles($interview['id']);
        $files[] = array(
            'id' => $interview['main_foto'],
            'fname' => $interview['fname'],
            'path' => $interview['path']
        );
        $files = array_merge($files, (is_array($att) ? $att : array()) );
        $resp['attaches'] = $files;
        echo json_encode($resp);

        exit();
        break;

}

switch($_page) {
    case 'view':

        $int = new interview();
        if(!$interview = $int->getInterview($uid, $id)) {
            header ("Location: /404.php"); exit;
        }
        $int->setInterviewLVT($uid, $interview);

//        $files = $int->getInterviewFiles($interview['id'], $interview['main_foto']);

//        if($files) {
//            $s = array();
//            $r = array();
//            foreach($files as $int_file) {
//                $url = WDCPREFIX . '/' . $int_file['path'] . $int_file['fname'];
//                $s[] = '<img id="'.$int_file['id'].'">';
//                $r[] = "<img id=\"{$int_file['id']}\" src=\"$url\">";
//            }
//            if(count($s) && count($r)) $interview['txt'] = str_replace($s, $r, $interview['txt']);
//        }

        $nav = $int->getNavigation($interview, $ord);
        $navigation = array();

        if($nav[1]['pos'] == 1) {
            $navigation['next'] = $nav[1];
        } else {
            $navigation['prev'] = $nav[1];
        }
        if(isset($nav[2])) $navigation['prev'] = $nav[2];

        $content = 'content_view.php';
        $FBShare = array(
            "title"       => $interview['uname'] . ' ' . $interview['usurname'] . ' [' . $interview['login'] . ']',
            "description" => "",
            "image"       => HTTP_PREFIX."www.free-lance.ru/images/free-lance_logo.jpg"  
        );
        break;
    default:
        $interview = new interview();
        $list = $interview->getInterview($uid, null, $ord, $year, $filter);

        $content = 'content_index.php';
}


$css_file = array( '/css/main.css', '/css/ai.css' );
$css_file[] = '/css/wysiwyg.css';
$css_file[] = '/css/hljs.css';
$js_file  = array( 'mootools-forms.js', 'articles.js' );
$js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
$js_file[] = '/scripts/highlight.min.js';
$js_file[] = '/scripts/highlight.init.js';
$js_file[] = '/scripts/comments.all.js';
$js_file[] = '/scripts/banned.js';
$js_file[] = '/scripts/uploader.js';
$additional_header =
    '<script type="text/javascript" src="/scripts/mootools-forms.js"></script>' .
    '<script type="text/javascript" src="/scripts/articles.js"></script>' . 
    '<script type="text/javascript" src="/scripts/kwords.js"></script>' .
    '<script type="text/javascript" src="/kword_js.php"></script>'; 
include ($rpath."/template2.php");
