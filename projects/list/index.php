<?php
/**
* Скрипт для вывода списка проектов, раздел "Работа" /projects/
* .htaccess RewriteRule ^projects/$ projects/list/index.php?showprojects=1 [NS,QSA]
*/

$g_page_id = "0|103";
// у раздела сделаю свои вопросы в окне помощи
if (isset($_GET['kind']) && 8 == $_GET['kind']) {
    $g_help_id = 202;
}

// первым делом запоминаем была ли попытка переключиться на антиюзера или сменить антиюзера
// иначе при подключении /classes/stdf.php очистится $_POST
// подробнее тут: #19492
$switch = (isset($_POST['action']) && 'switch' === $_POST['action']);
$change_au = (isset($_POST['action']) && 'change_au' === $_POST['action']);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_filter.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/offers_filter.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_offers.php");

$g_folders = array(0=>1, 1=>1, 2=>3, 3=>2, 4=>4);

session_start();

if($_GET['full_site_version'] == 1) {
    $show_full_site_version = 1;
    setcookie("full_site_version", "1", time()+60*60*24*30, "/");
}

$grey_main = 1;

$filter_page = 0;
$filter_apply = false;
$filter_params = array('kind'=>((isset($_GET['kind']))?intval(($_GET['kind'])):intval($_POST['kind'])));

// Развернутость / свернутость фильтра.
if (isset($_COOKIE['new_pf'.$filter_page])) {
    $filter_show = $_COOKIE['new_pf'.$filter_page];
}
else {
    $filter_show = is_emp()?0:1;
    setcookie("new_pf".$filter_page, $filter_show, time()+60*60*24*30, "/");
}

// Развернутость / свернутость фильтра скрытых проектов.
if (isset($_COOKIE['new_pf10'])) {
    $filter2_show = $_COOKIE['new_pf10'];
}
else {
    $filter2_show = 1;
    setcookie("new_pf10", $filter_show, time()+60*60*24*30, "/");
}

if (isset($_COOKIE['hidetopprjlenta']) && $_COOKIE['hidetopprjlenta']==1) {
    $hidetopprjlenta = 1;
}
else {
    $hidetopprjlenta = 0;
    setcookie("hidetopprjlenta", $hidetopprjlenta, time()+60*60*24*30, "/");
}

$isPrjOpened = isset($_COOKIE['isPrjOpened']) ? $_COOKIE['isPrjOpened'] : true;
$rpath = "";

$kind = __paramInit('int', 'kind', 'kind');

/**
 * todo: сделать проверки на допустимые значения.
 */
if (($kind < 0) || ($kind > 7)) {
     header("Location: /404.php");
     exit;
}

@$action = strip_tags(trim($_GET['action']));
if (!$action) @$action = strip_tags(trim($_POST['action']));

// определяем, был ли сброс массива POST
if (!$action && ($switch || $change_au)) {
    $action = "switch_error";
}

switch ($action) {

    // Фильтрация проектов
    case "postfilter":
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");         
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");         
        $keywords = $_POST['pf_keywords'];
        if ($keywords) {
            $keywords = preg_replace('/\s+/si', ' ', $keywords);
        }
        $prj_filter = new projects_filters();

        $f_category = isset($_POST['pf_categofy'])?$_POST['pf_categofy']:'';

        if ((int)$_POST['comboe_column_id'] === 1 && $_POST['comboe_db_id'] > 0 ) {
            $f_category[1][$_POST['comboe_db_id']] = 1;
        }
        if ((int)$_POST['comboe_column_id'] === 0 && $_POST['comboe_db_id'] > 0 ) {
            $f_category[0][$_POST['comboe_db_id']] = 1;
        }

        // Временное решение
        list($f_country, $f_city) = split(": ", $_POST['location']);
        $_POST['pf_country'] = country::getCountryId($f_country);
        $_POST['pf_city'] = city::getCityIdByCountry($f_city, (int)$_POST['pf_country']);
        if ((int)$_POST['pf_cost_from'] == 0) {
            $_POST['pf_wo_budjet'] = 1;##0028132
        }
        $prj_filter->Save(get_uid(), $_POST['pf_cost_from'], $_POST['pf_cost_to'], $_POST['pf_currency'], isset($_POST['pf_wo_budjet']), $f_category, $_POST['pf_country'], 
                $_POST['pf_city'], $_POST['pf_keywords'], isset($_POST['pf_my_specs']), $rerror, $error, 0, $filter_page, ($kind==1 || $kind==2), 
                $_POST['pf_only_sbr'], $_POST['pf_pro_only'], $_POST['pf_verify_only'], $_POST['pf_less_offers'], $_POST['pf_end_days_from'], 
                $_POST['pf_end_days_to'], false, 4, $_POST['pf_urgent_only'], $_POST['pf_block_only'], isset($_POST['hide_exec']));
        
        $location = '/projects/' . ($kind != 5 ? '?kind=' . $kind : '');
        break;

    // Деактивация фильтра проектов
    case "deletefilter":
        if ($PDA) {
            $prj_filter = new projects_filters_pda();
            $prj_filter->UpdateActiveFilter(get_uid(), false);
        } else {
            $prj_filter = new projects_filters();
            $prj_filter->DeleteFilter(get_uid());
        }
        break;

    // Активация фильтра проектов
    case "activatefilter":
        if($PDA) {
            $prj_filter = new projects_filters_pda();
            $prj_filter->UpdateActiveFilter(get_uid(), true);
        } else {
            $prj_filter = new projects_filters();
            $prj_filter->ActivateFilter(get_uid());
        }
        break;

    // Филтрация проектов (pda)
    case "postfilter_pda":
        if($PDA) {
            $prj_filter = new projects_filters_pda();
            $prj_filter->Save(get_uid(), $_POST['pf_cost_from'], $_POST['pf_cost_to'], isset($_POST['pf_my_specs']), (int)$_POST['active']);
        }
        break;
}

if (isset($location) && $location) {
    header ('Location: ' . $location);
    exit;
}

if (!$kind) $kind = 5;  //default show all
$frl_offers = new freelancer_offers();
    
if(!$_GET['newurl'] && ($kind==2 || $kind==8)) {
    $query_string = preg_replace("/kind=2/", "", $_SERVER['QUERY_STRING']);
    $query_string = preg_replace("/kind=8/", "", $query_string);
    $query_string = preg_replace("/^&/", "", $query_string);
    $query_string = preg_replace("/&$/", "", $query_string);
    header ('HTTP/1.1 301 Moved Permanently');
    header ('Location: /'.($kind==2 ? 'konkurs' : 'sdelau').'/'.($query_string ? "?{$query_string}" : ""));
    exit;
}

get_uid(false);

if ($_SESSION['p_ref']) unset($_SESSION['p_ref']);

$prfs = new professions();

$profs = $prfs->GetAllProfessions("",0, 1);

$uid = get_uid(!$action);

$page = intval($_GET['page']);
if ($page < 1) {
    $page = 1;
    $bPageDefault = true;
}

// фильтр, этот фильтр не подключаем в ПДА
if(!$PDA) {
    if($kind != 8) {
        $prj_filter = new projects_filters();
        $filter = $prj_filter->GetFilter($uid, $error, $filter_page);
        
        GaJsHelper::getInstance()->setProjectsFilterCategory($filter['categories']);
        adriver::getInstance()->setProjectsFilterCategory($filter['categories']);
        
        $filter['state'] = 0;
        $filter_apply = ($filter['active'] == "t");
        // проекты
        $prj = new new_projects();
        $prj_content = $prj->SearchDB($kind, $page, ($filter || is_emp() || hasPermissions('projects')) ? 0 : 1, $filter, false, true);
        //echo $prj_content;die;
        $prj_pos = strpos( $prj_content, '<!--data_found-->' );
    } else {
        $filter_page = $kind*10;
        $filter_params = array('kind'=>((isset($_GET['kind']))?intval(($_GET['kind'])):intval($_POST['kind'])));
        // Развернутость / свернутость фильтра.
        if(isset($_COOKIE['new_pf'.$filter_page])) {
            $filter_show = $_COOKIE['new_pf'.$filter_page];
        } else {
            $filter_show = 0;
            setcookie("new_pf".$filter_page, $filter_show, time()+60*60*24*30, "/");
        }
        $offers_filter = new offers_filter();
        $filter = $offers_filter->GetFilter($uid);
        $filter_apply = ($filter['active'] == "t");
        $filter_only_my_offs = ($filter['only_my_offs'] == "t");
    }
    
    switch ($filter_page) {
        case 1:
            $frm_action = '/proj/?p=list';
            $frm_action2 = '/proj/?p=list';
            $prmd='&amp;';
            $has_hidd = false;
            break;
        default:
            $frm_action = '/projects/';
            $frm_action2 = '/';
            $prmd='?';
    }
}

if ($prj_pos === false && !$bPageDefault) {
    include( ABS_PATH . '/404.php' );
    exit;
}

$account = new account();
if ($_SESSION['uid']) {
     $ok = $account->GetInfo($_SESSION['uid'], true);
}

stat_collector::setStamp(); // stamp

//скрытые проекты
projects_filters::initClosedProjects();

$rss_file = NULL;

switch ($kind) {
    case 0: case 1: $rss_file = "/rss/projects.xml"; break;
    case 2: $rss_file = "/rss/competition.xml"; break;
    case 4: $rss_file = "/rss/office.xml"; break;
    case 6: $rss_file = "/rss/pro.xml"; break;
    case 5: $rss_file = "/rss/all.xml"; break;
}

if (intval($_GET['page'])>1) {
    $page_title = 'Лента проектов - Страница '.intval($_GET['page']).' - фриланс, удаленная работа на FL.ru';
} else {
    $page_title = 'Фриланс, удаленная работа, проекты для фрилансеров, заказ работы';
}

if (!(intval($_GET['page']) > 1)) {
    $page_descr = 'Лучшие специалисты фрилансеры. Поиск сотрудников и заказ услуг. Удаленная работа на дому. Фриланс сайт для тех, кому нужны специалисты, нужны сотрудники удаленно, требуются фрилансеры';
}

if (!(intval($_GET['page']) > 1)) {
    $page_keyw = 'фриланс, free-lance, freelance, удаленная работа, вакансии удаленно, нужны сотрудники, найти исполнителя, онлайн фрилансер ру, требуется специалист, удаленные услуги фрилансеров';
}

// Формируем JS внизу страницы
define('JS_BOTTOM', true);

$js_file = array( 'banned.js', 'projects.js', 'attachedfiles.js', 'calendar.js', 
    '/css/block/b-pay-answer/b-pay-answer.js', 'warning.js', '/css/block/b-shadow/b-shadow.js', 
);

if (hasPermissions('projects') && $kind != 8) {
    $js_file[] = 'projects-quick-edit.js';
}

$content = "templates/main.php";

require_once($_SERVER['DOCUMENT_ROOT']."/classes/seo.php");
$seo_catalog_data = seo::getDirections(null, true);
array_push($seo_catalog_data, array('name_section_link'=>'', 'dir_name'=>'Все направления'));

// Правки для СЕО
if ($kind == 5) {
    $page_title = 'Фриланс сайт №1. Фрилансеры, вакансии удаленно, работа на дому, freelance : FL.ru';
    $page_descr = 'Работа на дому. Фриланс вакансии и проекты, в которых нужны удаленные сотрудники, онлайн фрилансеры, лучшие специалисты - дизайнеры, разработчики, программисты, копирайтеры, рерайтреы, бухгалтеры, юристы, маркетологи';
    $page_keyw = 'фриланс ру, free-lance, freelancer, удаленная работа вакансии, ищу работу на дому, вакансии удаленно, нужны сотрудники, онлайн фрилансер, требуются лучшие специалисты, программист, дизайнер, разработчик, копирайтер, рерайтер, бухгалтер, юрист, seo, художник';
}

if ($kind == 2) {
    $page_title = 'Фриланс сайт №1. Фрилансеры, онлайн конкурсы, работа на дому, freelance : FL.ru';
    $page_descr = 'Фриланс работа на дому. Лучшие конкурсы дизайнеров. Онлайн конкурсы сценариев, логотипов и слоганов. Фриланс проекты, в которых нужны идеи, варианты дизайнов, придумать слоганы, написать сценарии. Организовать конкурс для фрилансеров';
    $page_keyw = 'фриланс, free-lance, freelancer, удаленная работа вакансии, разместить конкурс, нужны идеи, придумать слоганы, конкурс сценариев, онлайн фриланс ру, организовать конкурс';
}

$main_page = true; 

include $_SERVER['DOCUMENT_ROOT'].'/template3.php';
