<?
$g_page_id = "0|7";
$rpath = "../";
$grey_search = 1;
$activ_tab = -1;
$stretch_page = true;
$showMainDiv  = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/search.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
session_start();
stat_collector::setStamp(); // stamp
$uid    = get_uid();
$type   = __paramInit('string', 'type', NULL);
// для фрилансеров по-умолчанию - поиск проектов
if ($type === null) {
    $type = (is_emp() || !get_uid(0))? 'users': 'projects';
}
$page   = intval($_GET['page']);
$action = __paramInit('string', 'action', 'action');
$pss    = urldecode(__paramInit('htmltext','search_string','search_string'));
//if ( strtolower(mb_detect_encoding( trim( str_repeat( preg_replace("~\d+~", "", $pss), 3 )), array("utf-8", "windows-1251") )) == "utf-8" ) {
if (__paramInit('string', 'encode', 'encode', 'cp1251') === 'utf8') {
    $pss = mb_convert_encoding($pss, "windows-1251", "UTF-8");
}
/*if(trim($_GET['search_string']) == "" && $_GET['search_hint'] != "") {
    $_GET['search_string'] = $_GET['search_hint'];
    $pss    = __paramInit('htmltext','search_hint','search_hint');
}*/
$_GET['search_string'] = stripslashes($_GET['search_string']);
if(!isset($_POST['action']) && isset($_GET['action'])) {
    $_POST = $_GET;
}
$build = $_GET;
unset($build['page']);

$query_string = stripslashes(http_build_query($build));
unset($build['type']);
$query_string_menu = stripslashes(http_build_query($build));
$userLimit = intval(isset($_COOKIE['seUserLimit'])?$_COOKIE['seUserLimit']:0);
// $_SESSION['search_elms'] -- ассоциирован с базовыми индексами (ключами) элементов поиска.
// Элементы принимают значения количества найденных документов в предыдущем поиске, либо пустая строка '' -- категория отключена пользователем.
/*if(!isset($_SESSION['search_elms']))
    $_SESSION['search_elms'] = array('projects'=>0, 'users'=>0, 'works'=>0, 'messages'=>0, 'commune'=>0, 'blogs'=>0, 'articles'=>0, 'notes'=>0);*/
if(!isset($_SESSION['search_elms'][$type])) {
    unset($_SESSION['search_elms'], $_SESSION['search_string'], $_SESSION['search_advanced']);
}
if($_GET['action'] != 'search_advanced') {
    unset($_SESSION['search_advanced']);
}
if(isset($_GET['search_string'])) {
    $_SESSION['search_string'] = base64_encode($_GET['search_string']);
}
$search_tabs = array('works'    => array("name" => "Работы", "search" => "works"),
                     'messages' => array("name" => "Личные сообщения", "search" => "messages"),  
                     'commune'  => array("name" => "Сообщества", "search" => "commune"),
                     'blogs'    => array("name" => "Блоги", "search" => "blogs"),
                  //   'articles' => array("name" => "Статьи и интервью", "search" => "articles"),
                     'notes'    => array("name" => "Личные заметки", "search" => "notes"));
//#0026462 Убираем поиск по статьям и магазинам
if (in_array($type, array('articles'))) {
    header_location_exit('/404.php');
}
if(BLOGS_CLOSED == true) {
    unset($search_tabs['blogs']);
    if($type == 'blogs') $type = 'commune';
}
switch($type) {
    default:
        $user_limit_array = array(20,50,100);
        break;
    case "works":
        $user_limit_array = array(24,54,102);
        break;
}

if(!$user_limit_array[$userLimit]) $set_usr_limit = current($user_limit_array);
else $set_usr_limit = $user_limit_array[$userLimit];

$_SESSION['search_tab_active'] = strtolower($type);     
$_SESSION['string_professions'] = '';
switch($type) {
    default:
    case "users_test": // #0016532
        header_location_exit('/404.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
        if(!isset($_SESSION['search_elms']['users_test'])) {
            $_SESSION['search_elms'] = array('users_test' => 0); 
        }
        
        if($_POST['exp'][0] > $_POST['exp'][1] && $_POST['exp'][1] != 0) {
            $a = $_POST['exp'][0];
            $_POST['exp'][0] = $_POST['exp'][1];
            $_POST['exp'][1] = $a;
        }
        
        if($_POST['age'][0] > $_POST['age'][1] && $_POST['age'][1] != 0) {
            $a = $_POST['age'][0];
            $_POST['age'][0] = $_POST['age'][1];
            $_POST['age'][1] = $a;
        }
        
        if(is_array($_POST['from_cost'])) {
            foreach($_POST['from_cost'] as $key=>$val) {
                if($val > $_POST['to_cost'][$key] && $_POST['to_cost'][$key] != 0) {
                    $a = $_POST['from_cost'][$key];
                    $_POST['from_cost'][$key] = $_POST['to_cost'][$key];
                    $_POST['to_cost'][$key] = $a;
                }
            }
        }
        
        if($_POST['action'] == "search_advanced") {
            $filter = array("active"       => "t",
                            "categories"   => $_POST['pf_categofy'],
                            "prof"         => $_POST['pf_categofy'],
                            "kwords"       => $_POST['kword'],
                            //"kword"        => $_POST['kword'],
                            "cost_type"    => is_array($_POST['cost_type']) ? array_map("intval", $_POST['cost_type']) : $_POST['cost_type'],
                            "from_cost"    => is_array($_POST['from_cost']) ? array_map("intval", $_POST['from_cost']) : $_POST['from_cost'],
                            "to_cost"      => is_array($_POST['to_cost']) ? array_map("intval", $_POST['to_cost']) : $_POST['to_cost'],
                            "curr_type"    => is_array($_POST['curr_type']) ? array_map("intval", $_POST['curr_type']) : $_POST['curr_type'],
                            "exp"          => is_array($_POST['exp']) ? array_map("intval", $_POST['exp']) : $_POST['exp'],
                            "exp_from"     => (int)$_POST['exp'][0],
                            "exp_to"       => (int)$_POST['exp'][1],
                            "login"        => htmlspecialchars($_POST['login']),
                            "age"          => is_array($_POST['age']) ? array_map("intval", $_POST['age']) : $_POST['age'],
                            "age_from"     => (int)$_POST['age'][0],
                            "age_to"       => (int)$_POST['age'][1],
                            "country"      => (int)$_POST['pf_country'],
                            "city"         => (int)$_POST['pf_city'],
                            "in_office"    => $_POST['in_office'],
                            "in_fav"       => $_POST['in_fav'],
                            "only_free"    => $_POST['only_free'],
                            "is_pro"       => $_POST['is_pro'],
                            "is_verify"       => $_POST['is_verify'],
                            "sbr_is_positive"  => $_POST['sbr_is_positive'],
                            "is_preview"   => $_POST['is_preview'],
                            "sbr_not_negative"  => $_POST['sbr_not_negative'],
                            "opi_is_positive"  => $_POST['opi_is_positive'],
                            "opi_not_negative"  => $_POST['opi_not_negative'],
                            "success_sbr"  => $_POST['success_sbr']);
                       
            
                         
            $_SESSION['search_advanced'][$type] = $filter;
        } elseif($_POST['action'] == "search") {
            unset($_SESSION['search_advanced'][$type]);
        }
        
        if(!$filter) {
            $filter = $_SESSION['search_advanced'][$type];
        }
        
        if($filter['kwords']) {
            $key_word = stripslashes(urldecode($filter['kwords']));
            if($key_word) {
                $word = kwords::getKeys($key_word);
                $filter['orig_kwords'] = 1;
                $_SESSION['search_advanced'][$type]['orig_kwords'] = 1;
                if($word) {
                    $filter['kword'] = $word;
                    $_SESSION['search_advanced'][$type]['kword'] = $filter['kword'];
                }
            }
        }
        
        if($filter['cost_type']) {
            foreach($filter['cost_type'] as $key=>$value) {
                $cFilter[] = array("cost_type" => $filter['curr_type'][$key],
                                   "cost_from" => $filter['from_cost'][$key],
                                   "cost_to"   => $filter['to_cost'][$key],
                                   "type_date" => $value);
            }
            $filter['cost'] = $cFilter;
            $_SESSION['search_advanced'][$type]['cost'] = $filter['cost'];
        }
        
        $gFilter = $filter['categories']; 
        break;
    case "users":
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/professions.php");
        if(!isset($_SESSION['search_elms']['users'])) {
            $_SESSION['search_elms'] = array('users' => 0); 
        }
        
        if($_POST['exp'][0] > $_POST['exp'][1] && $_POST['exp'][1] != 0) {
            $a = $_POST['exp'][0];
            $_POST['exp'][0] = $_POST['exp'][1];
            $_POST['exp'][1] = $a;
        }
        
        if($_POST['age'][0] > $_POST['age'][1] && $_POST['age'][1] != 0) {
            $a = $_POST['age'][0];
            $_POST['age'][0] = $_POST['age'][1];
            $_POST['age'][1] = $a;
        }
        
        if(is_array($_POST['from_cost'])) {
            foreach($_POST['from_cost'] as $key=>$val) {
                if($val > $_POST['to_cost'][$key] && $_POST['to_cost'][$key] != 0) {
                    $a = $_POST['from_cost'][$key];
                    $_POST['from_cost'][$key] = $_POST['to_cost'][$key];
                    $_POST['to_cost'][$key] = $a;
                }
            }
        }
        
        if($_POST['action'] == "search_advanced") {
            $filter = array("active"       => "t",
                            "categories"   => $_POST['pf_categofy'],
                            "prof"         => $_POST['pf_categofy'],
                            "kwords"       => $_POST['kword'],
                            //"kword"        => $_POST['kword'],
                            "cost_type"    => is_array($_POST['cost_type']) ? array_map("intval", $_POST['cost_type']) : $_POST['cost_type'],
                            "from_cost"    => is_array($_POST['from_cost']) ? array_map("intval", $_POST['from_cost']) : $_POST['from_cost'],
                            "to_cost"      => is_array($_POST['to_cost']) ? array_map("intval", $_POST['to_cost']) : $_POST['to_cost'],
                            "curr_type"    => is_array($_POST['curr_type']) ? array_map("intval", $_POST['curr_type']) : $_POST['curr_type'],
                            "exp"          => is_array($_POST['exp']) ? array_map("intval", $_POST['exp']) : $_POST['exp'],
                            "exp_from"     => (int)$_POST['exp'][0],
                            "exp_to"       => (int)$_POST['exp'][1],
                            "login"        => htmlspecialchars($_POST['login']),
                            "age"          => is_array($_POST['age']) ? array_map("intval", $_POST['age']) : $_POST['age'],
                            "age_from"     => (int)$_POST['age'][0],
                            "age_to"       => (int)$_POST['age'][1],
                            "country"      => (int)$_POST['pf_country'],
                            "city"         => (int)$_POST['pf_city'],
                            "in_office"    => $_POST['in_office'],
                            "in_fav"       => $_POST['in_fav'],
                            "only_free"    => $_POST['only_free'],
                            "is_pro"       => $_POST['is_pro'],
                            "is_verify"       => $_POST['is_verify'],
                            "sbr_is_positive"  => $_POST['sbr_is_positive'],
                            "is_preview"   => $_POST['is_preview'],
                            "sbr_not_negative"  => $_POST['sbr_not_negative'],
                            "opi_is_positive"  => $_POST['opi_is_positive'],
                            "opi_not_negative"  => $_POST['opi_not_negative'],
                            "success_sbr"  => $_POST['success_sbr']);
                       
            
                         
            $_SESSION['search_advanced'][$type] = $filter;
        } elseif($_POST['action'] == "search") {
            unset($_SESSION['search_advanced'][$type]);
        }
        
        if(!$filter) {
            $filter = $_SESSION['search_advanced'][$type];
        }
        
        if($filter['kwords']) {
            $key_word = stripslashes(urldecode($filter['kwords']));
            if($key_word) {
                $word = kwords::getKeys($key_word);
                $filter['orig_kwords'] = 1;
                $_SESSION['search_advanced'][$type]['orig_kwords'] = 1;
                if($word) {
                    $filter['kword'] = $word;
                    $_SESSION['search_advanced'][$type]['kword'] = $filter['kword'];
                }
            }
        }
        
        if($filter['cost_type']) {
            foreach($filter['cost_type'] as $key=>$value) {
                $cFilter[] = array("cost_type" => $filter['curr_type'][$key],
                                   "cost_from" => $filter['from_cost'][$key],
                                   "cost_to"   => $filter['to_cost'][$key],
                                   "type_date" => $value);
            }
            $filter['cost'] = $cFilter;
            $_SESSION['search_advanced'][$type]['cost'] = $filter['cost'];
        }
        
        $gFilter = $filter['categories'];
        if ($filter["prof"][1] && is_array($filter["prof"][1])) {
            $raw_professions = professions::GetProfessionsTitles(array_keys($filter["prof"][1]));
            $a_professions = array();
            foreach ($raw_professions as $profession_item) {
                $a_professions[$profession_item["name"]] = '(@name_prof "' . $profession_item["name"] . '" | @additional_specs "' . $profession_item["name"] . '")';
            }
            $_SESSION['string_professions'] = join(" ", $a_professions);
        }

        break;
    case "projects":
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/professions.php");
        if(get_uid()) $user_specs = professions::GetProfessionsByUser($_SESSION['uid'], false, true);
        
        if(!isset($_SESSION['search_elms']['projects'])) {
            $_SESSION['search_elms'] = array('projects' => 0); 
        }
        
        if($_POST['pf_cost_from'] > $_POST['pf_cost_to'] && $_POST['pf_cost_to'] != 0) {
            $a = $_POST['pf_cost_from'];
            $_POST['pf_cost_from'] = $_POST['pf_cost_to'];
            $_POST['pf_cost_to'] = $a;
        }
        
        if($_POST['action'] == "search_advanced") {
            $_POST['pf_country'] = intval($_POST['pf_country']);
            $_POST['pf_city'] = intval($_POST['pf_city']);
            $filter = array("active"      => "t",
                            "cost_from"   => $_POST['pf_cost_from'],
                            "cost_to"     => $_POST['pf_cost_to'],
                            "currency"    => $_POST['pf_currency'],
                            "wo_cost"     => $_POST['pf_wo_budjet']==1?'t':'f',
                            "only_sbr"    => $_POST['pf_only_sbr']==1?'t':'f',
                            "category"    => $_POST['pf_category'],
                            "my_specs"    => $_POST['pf_my_specs']==1?'t':'f',
                            "categories"  => $_POST['pf_categofy'],
                            "country"     => (int)$_POST['pf_country'],
                            "city"        => (int)$_POST['pf_city']);

            if($filter['my_specs'] == 't') {
                $filter['user_specs'] = $user_specs;
            }                       
            $_SESSION['search_advanced'][$type] = $filter;
        } elseif($_POST['action'] == "search") {
            unset($_SESSION['search_advanced'][$type]);
        }
        
        // вывод по-умолчанию последних N (в зависимости от выбранного значения) проектов #0019045
        $top_projects = null;
        $top_projects_cnt = null;
        if (get_uid(0) && !is_emp() && !isset($_POST['search_string'])) {
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/projects.php");
            
            $prj = new new_projects();
            $prj->page_size = $set_usr_limit;
            $top_projects = $prj->getLastProjects(5, null, $set_usr_limit);
            $top_projects_cnt = count($top_projects);
        }
        
        break;
    case "works":
    case "messages":
    case "commune":
    //case "blogs":
    case "notes":
    //case "articles":
        
        $search_tabs[$type]['active'] = true;
        
        if(!isset($_SESSION['search_elms']['notes'])) {
            $_SESSION['search_elms'] = array('works'=>0, 'messages'=>0, 'commune'=>0, /*'blogs'=>0, 'articles'=>0,*/ 'notes'=>0); 
        }
        
        /*
        if(BLOGS_CLOSED == true) {
            unset($_SESSION['search_elms']['blogs']);
        }*/
        
        break;
            
}

if(!$type) 
    $_SESSION['search_string'] = '';
$_SESSION['search_string'] = isset($_POST['search_string']) || $action=='search' ? base64_encode(html_entity_decode(stripslashes(trim($pss)), ENT_QUOTES)) : $_SESSION['search_string'];

if (isset($_POST['search_elms']) && is_array($_POST['search_elms'])) {
    $search_elms = array();
    foreach ($_POST['search_elms'] as $search_elm => $total) {
        if (in_array($search_elm, array('projects', 'users', 'works', 'messages', 'commune', 'notes'))) {
            $search_elms[$search_elm] = abs(intval($total));
        }
    }
    $_SESSION['search_elms'] = $search_elms;
}

$search_string = trim(base64_decode($_SESSION['search_string']).' '.$_SESSION['string_professions']);
$search = new search($uid);
$search->setUserLimit($set_usr_limit);
foreach($_SESSION['search_elms'] as $key=>$total) {
    $search->addElement($key, true, $set_usr_limit);
}

if($type == 'all')
    $action = 'search';
    
if ( !$page ) {
    $page = 1;
    $bPageDefault = true;
}
       
if($_POST['action']) $action = $_POST['action'];

if(isset($_SESSION['search_advanced'][$type]) && $action == 'view') {
    $action = 'view_advanced';
}
$is_search = ($search_string != ''||$_SESSION['search_advanced'][$type]);
switch ($action) {
    case "search_advanced": 
        $search->search($search_string, $page, $_SESSION['search_advanced'][$type]);
        break;
    case 'search' : 
        $search->search($search_string, $page); 
        break;
    case 'view_advanced':
        $search->search($search_string, $page, $_SESSION['search_advanced'][$type]);
        break;
    case 'view'   :
        $nCount   = $_SESSION['search_elms'][$type];
        $nPerPage = $_SESSION['search_limit'][$type];
        $nPerPage = ( intval($nPerPage) ) ? $nPerPage : 5;
        $nPages   = ceil( $nCount / $nPerPage );
        
        if ( 
            ($nCount == 0 || $nCount - 1 < ($page - 1) * $nPerPage) && !$bPageDefault 
            || $nPages == 1 && !$bPageDefault 
        ) {
        	include( ABS_PATH . '/404.php' );
            exit;
        }
        
        $search->search($search_string, $page, isset($_SESSION['search_advanced'])?$_SESSION['search_advanced']:"");
    break;
    default:
        break;
}
$elements = $search->getElements();
// Заполняем сессию количеством найденных документов.
foreach ($elements as $key=>$elm) {
    $_SESSION['search_elms'][$key]  = ($elm->isActive() ? $elm->total : $_SESSION['search_elms'][$key]);
    $_SESSION['search_limit'][$key] = ($elm->isActive() ? $elm->getProperty('limit') : $_SESSION['search_limit'][$key]);
}

$element = $elements[$type]; 


if(isset($_SESSION['search_elms'][$type]) && isset($_POST['search_string']) && !isset($_GET['only_tab'])) {
    if($_SESSION['search_elms'][$type] == 0) {
        foreach($_SESSION['search_elms'] as $name=>$count) {
            if($count>0) {
                header("Location: /search/?type={$name}&{$query_string_menu}&only_tab=1");
                exit;
            }
        }    
    }
}

if($search_string) {
    $search_input_hint = kwords::getRandomSearchHint($type);
} else {
    $search_input_hint = kwords::getRandomSearchHint($type);
}

$page_title = "Поиск - фриланс, удаленная работа на FL.ru";
$is_use_new_mootools = true;
$content = "content.php";
$css_file = array('search.css', 'nav.css','/css/block/b-menu/_tabs/b-menu_tabs.css','/css/block/b-search/b-search.css','/css/block/b-input-hint/b-input-hint.css' );

$js_file[] = 'search.js';
if($type && $type != 'users') {
    $js_file[] = '/kword_search_js.php?type=' . $type;
}
$content_bgcolor = '#ffffff';
$header  = "../header.php";
$footer  = "../footer.html";
include ("../template2.php");
?>
