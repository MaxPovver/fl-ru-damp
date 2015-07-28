<?php

$g_page_id = "0|5";
$rpath = "../";
$grey_catalog = 1;
$activ_tab = -1;
$stretch_page = true;
$showMainDiv = true;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancers_filter.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_binds.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFrlbind.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFrlbindup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/FreelancerCatalog.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");

session_start();
stat_collector::setStamp(); // stamp

$uid = get_uid();

//------------------------------------------------------------------------------
//@todo Ссылки должны изначально передавать такие параметры вместо word
$word = __paramInit('string', 'word');
if ($word) {
    $link = "/freelancers/?action=search&search_string={$word}";
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: {$link}");
    exit(0);
}


//------------------------------------------------------------------------------

$prof_id = 0;
$prof_group_id = 0;
$prof_group_parent_id = 0;
if (isset($_GET['prof'])) {
    if (preg_match("/^[0-9]+$/", $_GET['prof'])) {
        $link = professions::GetProfLink($_GET['prof']);
        if ($link) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: /freelancers/{$link}/");
            exit(0);
        }
    } else {
        $prof_link = htmlspecialchars($_GET['prof']);
        $prof_id = professions::GetProfId($_GET['prof']);
        $prof_group_parent_id = professions::GetProfGroupParentId($_GET['prof']);

        if (!$prof_id) {
            $prof_group_id = professions::GetProfGroupId($_GET['prof']);
        }
    }

    if (!$prof_id && !$prof_group_id) {
        $prof_link = '';
        $_GET['region_filter_city'] = $_GET['region_filter_country'];
        $_GET['region_filter_country'] = $_GET['prof'];
    }
} else if (isset($_POST['prof'])) {
    if (preg_match("/^[0-9]+$/", $_GET['prof'])) {
        $prof_id = intvalPgSql(trim($_POST['prof']));
    } else {
        $prof_link = $_POST['prof'];
        $prof_id = professions::GetProfId($_POST['prof']);
    }
}

//------------------------------------------------------------------------------

if (isset($_GET['profession_db_id']) &&
        !(($prof_id > 0 && $prof_id == $_GET['profession_db_id']) ||
        ($prof_group_id > 0 && $prof_group_id == $_GET['profession_db_id']))) {

    $link = null;
    if ($_GET['profession_db_id'] > 0) {
        if ($_GET['profession_column_id'] > 0) {
            $link = professions::GetProfLink($_GET['profession_db_id']);
        } else {
            $link = professions::GetGroupLink($_GET['profession_db_id']);
        }
    }

    unset($_GET['profession_db_id'], $_GET['profession_columns'], $_GET['profession_column_id'], $_GET['profession'], $_GET['prof']);

    $query_string = stripslashes(http_build_query($_GET));
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /freelancers/" . (($link) ? "{$link}/" : "") . "?{$query_string}");
    exit(0);
}

//------------------------------------------------------------------------------

$page = intval(trim($_GET['page']));
if (!$page) {
    $page = 1;
} elseif ($page == 1) {
    $sLocation = e_url('page');
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $sLocation");
    exit(0);
} elseif ($page < 1) {
    include( ABS_PATH . '/404.php' );
    exit;
}

//------------------------------------------------------------------------------


$freelancer = new freelancer();


if (!$prof_id && !$prof_group_id) {
    $prof_type = false;
    $anchor = 0;
    $cat_menu_freelancers = true;
} elseif ($prof_group_id) {
    $prof_name = professions::GetProfGroupTitle($prof_group_id);
    $prof_type = false;
    $anchor = 0;
    $cat_menu_freelancers = true;
} else {
    if (!$prof_link) {
        $prof_link = professions::GetProfField($prof_id, 'link');
    }

    $prof_name_arr = professions::GetProfTitle($prof_id);
    $prof_name = $prof_name_arr['name'];
    $prof_title = ($prof_name_arr['title']) ? $prof_name_arr['title'] : $prof_name;
    $prof_type = professions::GetProfType($prof_id);
    $anchor = professions::GetProfessionOrigin($prof_id);
    $g_page_id = "1|" . $prof_id;
}

//Мета-теги
if ($prof_group_id) {
    SeoTags::getInstance()->initFreelancers($prof_group_id, $page, false);
} else {
    SeoTags::getInstance()->initFreelancers($prof_id, $page);
}

$page_title = SeoTags::getInstance()->getTitle();
$page_descr = SeoTags::getInstance()->getDescription();
$page_keyw = SeoTags::getInstance()->getKeywords();
$page_h1 = SeoTags::getInstance()->getH1();

//------------------------------------------------------------------------------

// Показывать только про пользователей
$is_pro = __paramInit('bool', 'is_pro', 'is_pro', false);

$action = __paramInit('string', 'action', 'action', '');

//Выборка при поиске (не менялась)
if (in_array($action, array('search', 'search_advanced'))) {

    $search_string = __paramInit('htmltext', 'search_string', 'search_string');
    $search_string = html_entity_decode(stripslashes(trim($search_string)), ENT_QUOTES);

    if (!isset($_POST['action']) && isset($_GET['action'])) {
        $_POST = $_GET;
    }

    $build = $_POST;
    unset($build['page']);
    $query_string_menu = stripslashes(http_build_query($build));

    unset($build['prof']);
    $query_string_cat = stripslashes(http_build_query($build));


//------------------------------------------------------------------------------

    $exp = __paramInit('array_int', 'exp', 'exp');
    if (count($exp) != 2) $exp = array(0, 0);

    if ($exp[0] > $exp[1] && $exp[1] != 0) {
        $a = $exp[0];
        $exp[0] = $exp[1];
        $exp[1] = $a;
    }

    $age = __paramInit('array_int', 'age', 'age');
    if (count($age) != 2) $age = array(0, 0);
    if ($age[0] > $age[1] && $age[1] != 0) {
        $a = $_POST['age'][0];
        $age[0] = $age[1];
        $age[1] = $a;
    }

    $from_cost = __paramInit('int', 'from_cost', 'from_cost');
    $to_cost = __paramInit('int', 'to_cost', 'to_cost');
    if ($from_cost > $to_cost && $to_cost != 0) {
        $a = $from_cost;
        $from_cost = $to_cost;
        $to_cost = $a;
    }


    $filter_prof = array();
    if ($prof_id > 0) {
        $filter_prof = array(1 => array($prof_id => 1));
    } elseif ($prof_group_id > 0) {
        $profs = professions::GetProfs($prof_group_id);
        $filter_prof = array();
        foreach ($profs as $prof) {
            $filter_prof[1][$prof['id']] = 1;
        }
    }

    $filter = array(
        "is_pro" => $is_pro,
        'prof' => $filter_prof
    );

    $string_professions = '';


    if ($action == "search_advanced") {
        $filter = array(
            "active" => "t",
            "prof" => $filter_prof,
            "cost_type" => intval(@$_POST['cost_type_db_id']),
            "from_cost" => $from_cost,
            "to_cost" => $to_cost,
            "curr_type" => intval(@$_POST['curr_type_db_id']),
            "exp" => is_array($_POST['exp']) ? array_map("intval", $_POST['exp']) : @$_POST['exp'],
            "exp_from" => $exp[0],
            "exp_to" => $exp[1],
            "age" => is_array($_POST['age']) ? array_map("intval", $_POST['age']) : @$_POST['age'],
            "age_from" => $age[0],
            "age_to" => $age[1],
            "country" => (int) @$_POST['location_columns'][0],
            "city" => (int) @$_POST['location_columns'][1],
            "in_office" => (bool) @$_POST['in_office'],
            "in_fav" => (bool) @$_POST['in_fav'],
            "only_free" => (bool) @$_POST['only_free'],
            "is_pro" => $is_pro,
            "is_verify" => (bool) @$_POST['is_verify'],
            "is_preview" => (bool) @$_POST['is_preview'],
            "sbr_is_positive" => (bool) @$_POST['sbr_is_positive'],
            "sbr_not_negative" => (bool) @$_POST['sbr_not_negative'],
            "opi_is_positive" => (bool) @$_POST['sbr_is_positive'],
            "opi_not_negative" => (bool) @$_POST['sbr_not_negative']
        );


        if ($filter['cost_type']) {
            $filter['cost'][] = array(
                'cost_type' => $filter['curr_type'],
                'cost_from' => $filter['from_cost'],
                'cost_to' => $filter['to_cost'],
                'type_date' => $filter['cost_type']
            );
        }

        $countryObj = new country();
        $countryCityName = $countryObj->getCountryAndCityNames($filter['country'], $filter['city']);
        $countryCityName = @$countryCityName['name'];
    }


    if (!empty($filter["prof"]) && is_array($filter["prof"][1])) {
        $raw_professions = professions::GetProfessionsTitles(array_keys($filter["prof"][1]));
        $a_professions = array();
        foreach ($raw_professions as $profession_item) {
            $a_professions[$profession_item["name"]] = '(@name_prof "' . $profession_item["name"] . '" | @additional_specs "' . $profession_item["name"] . '")';
        }

        $string_professions = '(' . join(" | ", $a_professions) . ')';
    }

    $string_query = $search_string;

    // @todo Кажется, это никогда не используется?
    $string_query .=!empty($string_professions) ? ' ' . $string_professions : '';

    $type = 'users_ext';
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_ext.php");
    $search = new searchExt($uid);
    $search->setUserLimit(FRL_PP);
    $search->addElement($type, true, FRL_PP);
    $search->searchByType($type, $string_query, $page, $filter);
    $elements = $search->getElements();
    $element = $elements[$type];


    $frls = $element->results;
    $size = $element->total;
    $works = $element->works;

    $content = "search/content.php";
}
//Обычная выборка при навигации
else {
    $direction = (int) __paramInit('bool', 'dir', 'dir', false);
    $order = __paramInit('string', 'order', 'order', 'gnr');

    $catalog = new FreelancerCatalog();
    $catalog->initSearch($prof_group_id, $prof_id, $page, $order, $direction);
    if ($catalog->isFound()) {
        $frls = $catalog->getUsers();
        $works = $catalog->getWorks();
        $pages = $catalog->getPages();
    }

    $cur_page_url = $GLOBALS['host'] . strtok($_SERVER["REQUEST_URI"], '?') . "?" .
            (($order && $order != 'gnr') ? "order=$order&" : "") .
            (($direction) ? "dir=$direction&" : "");

    //Cсылка для new_paginator()
    $sHref = "%s" . $cur_page_url . "page=%d%s";

    if ($page > 1) {
        $additional_header .= '<link rel="prev" href="' . $cur_page_url . 'page=' . ($page - 1) . '">';
    }

    if ($page < $pages) {
        $additional_header .= '<link rel="next" href="' . $cur_page_url . 'page=' . ($page + 1) . '">';
    }

    $content = "content.php";
}

//------------------------------------------------------------------------------

// Попапы закреплений фрилансеров ----------------------------------------------

$popup = __paramInit('string', 'popup');

$freelancer_binds = new freelancer_binds();
$is_spec = $prof_id > 0;
$prof_use = $is_spec ? professions::GetProfessionOrigin($prof_id) : $prof_group_id;
$allow_frl_bind = $freelancer_binds->isAllowBind($uid, $prof_use, $is_spec);
$binded_to = $freelancer_binds->getBindDateStop($uid, $prof_use, $is_spec);

if ($allow_frl_bind || $binded_to) {
    quickPaymentPopupFrlbind::getInstance()->init(array(
        'prof_id' => $prof_id,
        'prof_group_id' => $prof_group_id,
        'prof_use' => $prof_use,
        'is_spec' => $is_spec,
        'ammount' => $freelancer_binds->getPrice($prof_use, $is_spec, (bool) $binded_to, $uid),
        'date_stop' => $binded_to,
        'autoshow' => $popup == 'bind_prolong',
        'addprof' => $is_spec && $freelancer_binds->needAddProf($uid, $prof_use)
    ));
}

$is_bind_first = $freelancer_binds->isBindFirst($uid, $prof_use, $is_spec);

if ($binded_to && !$is_bind_first) {
    quickPaymentPopupFrlbindup::getInstance()->init(array(
        'prof_id' => $prof_use,
        'is_spec' => $is_spec,
        'ammount' => $freelancer_binds->getPriceUp($prof_use, $is_spec, $uid),
        'autoshow' => $popup == 'bind_up'
    ));
}

$is_binded_hide = $binded_to && !$freelancer_binds->isAllowBind($uid, $prof_use, $is_spec, false);

//------------------------------------------------------------------------------

unset($_SESSION['payed_frl_' . md5($_SERVER['REQUEST_URI'])]);

$first_pages = array();
if ($prof_group_id) {
    $first_pages = firstpage::GetAll($prof_group_id, array(), true);
} else {
    $first_pages = firstpage::GetAll(
        $prof_id? : -1, array()
    );
}

$_SESSION['payed_frl_' . md5($_SERVER['REQUEST_URI'])] = $first_pages ? : array();
$_SESSION['payed_frl_prof_id'] = $prof_group_id ? 0 : ($prof_id? : -1);
$_SESSION['payed_frl_prof_group_id'] = $prof_group_id;

if ($popup == 'firstpage_prolong') {
    $_SESSION['firstpage_popup'] = 'prolong';
} elseif ($popup == 'firstpage_up') {
    $_SESSION['firstpage_popup'] = 'up';
} else {
    $_SESSION['firstpage_popup'] = '';
}

$header = "../header.php";
$footer = "../footer.html";
$js_file[] = '/css/block/b-text/b-text.js';
$js_file[] = '/css/block/b-popup/b-popup.js';
$css_file = array('/css/block/b-icon/__cat/b-icon__cat.css', 'main.css', 'search.css', '/css/nav.css');
$js_file[] = 'search.js';
$js_file[] = 'freelancers/freelancers.js'; //@todo: Сюда переносить все inline-скрипты!
$freelancers_catalog = true;
include ("../template2.php");
