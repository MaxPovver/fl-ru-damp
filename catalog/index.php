<?php

if ( !empty($_GET['subdomain']) && (!isset($_COOKIE['cdastop']) || !isset($_COOKIE[session_name()])) ) {
    require_once($_SERVER['DOCUMENT_ROOT']."/classes/memBuff2.php");
    $mem = new memBuff;
    if ( !empty($_GET['cdakey']) ) {
        $data = $mem->get(CROSSDOMAINAUTH_KEY_NAME . $_GET['cdakey']);
        if ( empty($_COOKIE['cdastop']) && !empty($data['sess']) && !empty($data['time']) && (mktime() - $data['time'] <= 120) ) {
            session_id($data['sess']);
        }
        setcookie('cdastop', 1, 0, '/');
        $mem->delete(CROSSDOMAINAUTH_KEY_NAME . $_GET['cdakey']);
    } else {
        mt_srand();
        $key = md5(uniqid($_SERVER['HTTP_HOST'], true));
        $back = HTTP_PREFIX . $_GET['subdomain'] . '.' . preg_replace('~^'.HTTP_PREFIX.'(?:www\.)?~', '', $GLOBALS['host']) . '/';
        if ( !empty($_GET['direction']) ) {
            $back .= 'catalog/' . $_GET['direction'] . '/';
        }
        if ( !empty($_GET['cat']) ) {
            $back .= $_GET['cat'] . '/';
        }
        if ( !empty($_GET['dir']) ) {
            $back .= $_GET['dir'] . '.html';
        }
        $data = array(
            'back' => $back,
            'time' => mktime()
        );
        $mem->set(CROSSDOMAINAUTH_KEY_NAME . $key, $data, 120);
        $redirectUri = "{$GLOBALS['host']}/crossauth.php?cdakey={$key}";
        //header("Location: {$GLOBALS['host']}/crossauth.php?cdakey={$key}");
        //exit;
    }
}

require_once($_SERVER['DOCUMENT_ROOT']."/classes/seo.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/country.php");

$g_page_id = "0|1";

session_start();
//$_SESSION['subdomain'] = 

$content_type = '';

$uid = get_uid();
$subdomain = $_GET['subdomain']; //@todo автоматом в эту переменную надо передавать субдомен
if($subdomain == null) $subdomain = 'all';

$seo = new seo($subdomain);

if(isset($_GET['subdomain']) && !$seo->subdomain['id']) {
    header("Location: {$GLOBALS['host']}/404.php");
    exit;
}

$direct_link = $direct_id = __paramInit('string', 'direction');
if ($direct_link) {
    $direct = $seo->getDirectionByLink($direct_link);
    $direct_id = null;
    if ($direct['id']) {
        $direct_id = $direct['id'];
    }
    if(isset($_GET['direction']) && $direct_id==null) {
        header("Location: {$GLOBALS['host']}/404.php");
        exit;
    }
}


if(isset($_GET['dir'])) {
    //if(is_numeric($_GET['dir'])) {
    //    $section_id = intval($_GET['dir']);
    //    $section_content = seo::getSectionById($section_id);
    //} else {
        $section_name = $_GET['dir'];
        $section_content = $seo->getSectionByName($section_name, true, $direct_id, $_GET['cat']);
    //}
    if(!$section_content['id'] || $section_content['direct_id']!=$direct_id || $seo->subdomain['id']!=$section_content['subdomain_id']) {
        header("Location: /404.php");
        exit();
    }
    //$seo = new seo((int)$section_content['subdomain_id']);
    $page_keyw  = $section_content['meta_keywords'];
    $page_descr = $section_content['meta_description'];

    $content_type = 'article';

    $dinamic_content = $seo->getDinamicContent($page_keyw, $seo->subdomain['name_subdomain']);
    $dinamic_content_articles = $seo->getDinamicContentArticles($page_keyw);
} elseif (!$subdomain && $direct_link) {
    $section_direction = $direct;
} else {
    $section_direction = $direct;
    $seo = new seo($subdomain);
}

$directions = $seo->getDirections($direct_id);
$res   = $seo->getSections(true, $direct_id);
$sections = array();
if($res) {
    foreach ($res as $row) {
        $sections[$row['direct_id']][] = $row;
    }
}

$cat_info = $seo->getSectionByName($_GET['cat'], false, $direct_id);
if($cat_info) {
    $catid = $cat_info['id'];
    $content_type = $content_type ? $content_type : 'dir';
} else {
    $catid = 0;
    $content_type = $content_type ? $content_type : 'direction';
}

$subdomains = $seo->getSubdomains();

$rpath = "../";

$countries = $seo->getCountries();
if((empty($_GET['subdomain']) && empty($_GET['direction'])) || (!empty($_GET['subdomain']) && empty($_GET['direction']))) {
    if(empty($_GET['subdomain'])) {
        $seo = new seo('all');
    } else {
        $seo = new seo($_GET['subdomain']);
    }
    $tmp_directions = $seo->getDirections();
    foreach($tmp_directions as $direction) {
        $directions[$direction['id']] = $direction;
    }
    $sections = $seo->getSectionsForMain();
    $content = "content-main.php";
} else {
    $content = "content.php";
}

switch($content_type) {
    case 'article':
        $page_title = $section_content['name_section'];
        break;
    case 'dir':
        $page_title = $cat_info['name_section'];
        break;
    case 'direction':
        $page_title = $section_direction['dir_name'];
        break;
}

$header = $rpath."header.php";
$footer = $rpath."footer.html";
$css_file[] = "seo.css";
$js_file = array( 'seo.js' );

$additional_header  = "<BASE href='{$host}'>";
$additional_header .= "<link rel='shortcut icon' href='".HTTP_PREFIX."www.free-lance.ru/favicon.ico' />";
$additional_header .= "<!-- {$_SERVER["REQUEST_URI"]} -->";
if ( !empty($redirectUri) ) {
    $additional_header .= "<script type='text/javascript'> if (navigator.cookieEnabled) location.href = '{$redirectUri}'; </script>";
}

if(isset($section_content) && $section_content['is_draft'] == 't' && !hasPermissions('seo')) {
    include ABS_PATH."/404.php"; exit;
}

include ($rpath."/template2.php");
?>