<?php

$g_page_id = "0|5";
$rpath = "../";
$grey_catalog = 1;
$activ_tab = -1;
$stretch_page = true;
$showMainDiv  = true;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/yii/tinyyii.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_seo.php");
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

session_start();
stat_collector::setStamp(); // stamp

$uid = get_uid();



//------------------------------------------------------------------------------

//@todo Ссылки должны изначально передавать такие параметры вместо word
$word = __paramInit('string', 'word');
if ($word) {
    $search_string = urlencode($word);
    $link = "/freelancers/?action=search&search_string={$search_string}";
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: {$link}");
    exit(0);
}


$prof_id = 0;
$prof_group_id = 0;
$prof_group_parent_id = 0;
if (isset($_GET['prof'])) 
{
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
} 
else if (isset($_POST['prof'])) 
{
    if (preg_match("/^[0-9]+$/", $_GET['prof'])) {
        $prof_id = intvalPgSql(trim($_POST['prof']));
    } else {
        $prof_link = $_POST['prof'];
        $prof_id = professions::GetProfId($_POST['prof']);
    }
}



if(isset($_GET['profession_db_id']) && 
   !(($prof_id > 0 && $prof_id == $_GET['profession_db_id']) || 
   ($prof_group_id > 0 && $prof_group_id == $_GET['profession_db_id']))) {
    
   $link = null;
   if($_GET['profession_db_id'] > 0){
       if($_GET['profession_column_id'] > 0){
           $link = professions::GetProfLink($_GET['profession_db_id']);
       }else{
           $link = professions::GetGroupLink($_GET['profession_db_id']);
       }
   }
   
   unset($_GET['profession_db_id'], 
         $_GET['profession_columns'], 
         $_GET['profession_column_id'], 
         $_GET['profession'], 
         $_GET['prof']);

   $query_string = stripslashes(http_build_query($_GET));
   header("HTTP/1.1 301 Moved Permanently");
   header("Location: /freelancers/".(($link)?"{$link}/":"")."?{$query_string}");
   exit(0);
}



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
        'ammount' => $freelancer_binds->getPrice($prof_use, $is_spec, (bool)$binded_to, $uid),
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
        'ammount' => round($freelancer_binds->getPriceUp($prof_use, $is_spec, $uid), 2),
        'autoshow' => $popup == 'bind_up'
    ));
}

$is_binded_hide = $binded_to && !$freelancer_binds->isAllowBind($uid, $prof_use, $is_spec, false);

//------------------------------------------------------------------------------


$page = intval(trim($_GET['page']));
if (!$page) 
{
    $page = 1;
    $bPageDefault = true;
} 
elseif ($page == 1) 
{
    $sLocation = e_url('page');
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $sLocation");
    exit(0);
} 
elseif ($page < 1) 
{
    include( ABS_PATH . '/404.php' );
    exit;
}

//------------------------------------------------------------------------------


GaJsHelper::getInstance()->setFrlCategories(array($prof_group_id,$prof_group_parent_id), $prof_id);
adriver::getInstance()->setFrlCategories(array($prof_group_id,$prof_group_parent_id), $prof_id);

//------------------------------------------------------------------------------

$section        = intval( $_GET['section'] );
$freelancer     = new freelancer();
$freelancer_seo = new freelancer_seo( $section );

if (!$prof_id && !$prof_group_id)
{
  // Подсчитываем количество избранных. Переписать в отдельный метод.
//  $fav_count = 0;
  $prof_name = "Все фри-лансеры";
  $prof_type = false;
  $prof_group_name = '';
  $prof_descr = '';
  $prof_descr_text = '';
  $page_title = "Удаленная работа%prepositional_cityname%. Фрилансеры%cityname%. Проекты от работодателей%cityname%.";
  $page_keyw = "фрилансер, free-lance, freelance, нужны сотрудники удаленно, онлайн фрилансер ру, найти специалиста, сайт фрилансеров, ищу сотрудника, фриланс дизайнер, бухгалтер на дому, юрист удаленно, программист, разработчик, seo оптимизатор, копирайт, рерайт";
  $page_descr = "Лучшие специалисты для тех, кому нужны сотрудники удаленно. Удаленная работа на дому. Онлайн фрилансеры: дизайнеры, копирайтеры, рерайтеры, разработчики, программисты, seo, бухгалтеры, юристы, художники";

  //list($avg_price_hour, $avg_price_project, $avg_price_month) = professions::GetAvgPrices($prof_id);
  //$avg_price_hour = $avg_price_project = $avg_price_month = null;
  
  $anchor = 0;
  
  $cat_menu_freelancers = true;
}
elseif ($prof_group_id)
{
    $prof_name = professions::GetProfGroupTitle($prof_group_id);
    $prof_type = false;
    $prof_group_name = '';
    $prof_descr = '';
    $prof_descr_text = '';
    $page_title = $prof_name .". Удаленная работа%prepositional_cityname%. Фрилансеры%cityname%. Проекты от работодателей%cityname%.";
    $page_keyw = $prof_name . ", фри-лансер, удаленная работа, поиск работы, предложение работы, портфолио фри-лансеров, разработка сайтов, программирование, переводы, тексты, дизайн, арт, реклама, маркетинг, прочее, fl.ru";
    $page_descr = $prof_name . ",Фри-лансер. Удаленная работа. Поиск работы. Предложение работы. Портфолио фри-лансеров. Разработка сайтов, Программирование, Переводы, Тексты, Дизайн, Арт, Реклама, Маркетинг, Прочее. FL.ru";

    //list($avg_price_hour, $avg_price_project, $avg_price_month) = professions::GetAvgPrices($prof_id);
    //$avg_price_hour = $avg_price_project = $avg_price_month = null;

    $anchor = 0;

    $cat_menu_freelancers = true;
} 
else
{
  // Подсчитываем количество избранных. Переписать в отдельный метод.
    //  $fav_count = 0;
  if( !$prof_link ) 
  {
      $prof_link = professions::GetProfField($prof_id, 'link');
  }

  $prof_name_arr = professions::GetProfTitle($prof_id);
  $prof_name = $prof_name_arr['name'];
  $prof_title = ($prof_name_arr['title'])?$prof_name_arr['title']:$prof_name;
  $prof_type = professions::GetProfType($prof_id);
  //$prof_group_name = professions::GetProfGroupName($prof_id);
  $prof_descr = professions::GetProfField($prof_id, 'descr');
  $prof_descr_text = professions::GetProfField($prof_id, 'descr_text');
  $anchor = professions::GetProfessionOrigin($prof_id);
  $g_page_id = "1|" . $prof_id;
  
    if ( $page == 1 ) 
    {
        if ( empty($prof_name_arr['title']) ) 
        {
            $page_title = $prof_title . " - фриланс, удаленная работа на FL.ru";
            $page_keyw  = $prof_title . ", Поиск работы, Предложение работы, Портфолио фри-лансеров, FL.ru";
        } 
        else 
        {
            $page_title = $page_keyw = $prof_name_arr['title'] . " - фриланс, удаленная работа%prepositional_cityname% на FL.ru";
            $page_keyw  = $prof_name_arr['title'] . ", Поиск работы, Предложение работы, Портфолио фри-лансеров, FL.ru";
        }
    }
    else 
    {
        $page_title = $prof_name . ' - Страница ' . $page . ' - фриланс, удаленная работа%prepositional_cityname% на FL.ru';
        $page_keyw  = $prof_name . ', Страница ' . $page . ', FL.ru';
    }
    
    
    if ( $page == 1 ) 
    {
        $page_descr = $prof_title . " Удаленная работа. Поиск работы. Предложение работы. Портфолио фри-лансеров. FL.ru";
    }
    else 
    {
        $page_descr = $prof_name . '. Страница ' . $page . ' - фриланс, удаленная работа%prepositional_cityname% на FL.ru';
    }
    
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
$show_all_freelancers = __paramInit('string', 'show', 'show', 'all');
$show_all_freelancers = ($show_all_freelancers == 'all');

$action = __paramInit('string', 'action', 'action', '');

//Выборка при поиске
if(in_array($action, array('search','search_advanced')))
{
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_ext.php");
    
    
    $type = 'users_ext';
    $search_string   = __paramInit('htmltext','search_string','search_string');
    $search_string = html_entity_decode(stripslashes(trim($search_string)), ENT_QUOTES);

    $_GET['search_string'] = stripslashes($_GET['search_string']);
    
    if(!isset($_POST['action']) && isset($_GET['action'])) 
    {
        $_POST = $_GET;
    }
    
    

    $build = $_POST;
    unset($build['page']);
    $query_string_menu = stripslashes(http_build_query($build));
    
    unset($build['prof']);
    $query_string_cat = stripslashes(http_build_query($build));

    
//------------------------------------------------------------------------------
    
    
    if ($_POST['exp'][0] > $_POST['exp'][1] && $_POST['exp'][1] != 0) 
    {
        $a = $_POST['exp'][0];
        $_POST['exp'][0] = $_POST['exp'][1];
        $_POST['exp'][1] = $a;
    }

    if ($_POST['age'][0] > $_POST['age'][1] && $_POST['age'][1] != 0) 
    {
        $a = $_POST['age'][0];
        $_POST['age'][0] = $_POST['age'][1];
        $_POST['age'][1] = $a;
    }
    
    
    if (@$_POST['from_cost'] > @$_POST['to_cost'] && @$_POST['to_cost'] != 0) {
        $a = $_POST['from_cost'];
        $_POST['from_cost'] = $_POST['to_cost'];
        $_POST['to_cost'] = $a;
    }
    
    
    $filter_prof = '';
    if ($prof_id > 0) {
        $filter_prof = array(1 => array($prof_id => 1));
    } elseif ($prof_group_id > 0) {
        $profs = professions::GetProfs($prof_group_id);
        $filter_prof = array(0 => array($prof_group_id => 1));
        foreach ($profs as $prof) {
            $filter_prof[1][$prof['id']] = 1;
        }
    }
    
    $filter = array(
        "is_pro" => $is_pro,
        'prof' => $filter_prof
    );

    $string_professions = '';
    
    
    if($action == "search_advanced") 
    {
        $filter = array(
            "active" => "t",
            "prof" => $filter_prof,
            "cost_type" => intval(@$_POST['cost_type_db_id']),
            "from_cost" => intval(@$_POST['from_cost']),
            "to_cost" => intval(@$_POST['to_cost']),
            "curr_type" => intval(@$_POST['curr_type_db_id']),
            "exp" => is_array($_POST['exp']) ? array_map("intval", $_POST['exp']) : @$_POST['exp'],
            "exp_from" => (int) @$_POST['exp'][0],
            "exp_to" => (int) @$_POST['exp'][1],
            "age" => is_array($_POST['age']) ? array_map("intval", $_POST['age']) : @$_POST['age'],
            "age_from" => (int) @$_POST['age'][0],
            "age_to" => (int) @$_POST['age'][1],
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
    
    
    if (!empty($filter["prof"]) && is_array($filter["prof"][1])) 
    {
        $raw_professions = professions::GetProfessionsTitles(array_keys($filter["prof"][1]));
        $a_professions = array();
        foreach ($raw_professions as $profession_item) {
            $a_professions[$profession_item["name"]] = '(@name_prof "' . $profession_item["name"] . '" | @additional_specs "' . $profession_item["name"] . '")';
        }

        $string_professions = '(' . join(" | ", $a_professions) . ')';
    }

    $string_query = $search_string;
    
    // @todo Кажется, это никогда не используется?
    $string_query .= !empty($string_professions) ? ' ' . $string_professions : '';
    
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
else
{

    // Показываем только ПРО пользователей
    if (!$show_all_freelancers) 
    {
        freelancer::SetFuTable('fu_pro');
    }

    
//------------------------------------------------------------------------------
    
    
    $ff = NULL;

    if ($prof_group_id)
    {
        if (is_null($ff))
        {
            $ff = array();
        }
        
        $ff['prof'][0] = array($prof_group_id => 1);
        $ff['prof'][1] = array();
    }
    
    
//------------------------------------------------------------------------------
    
    
    $frl_pp = intval(trim($_GET['pp']));
    if (!$frl_pp) $frl_pp = FRL_PP;

    if (isset($_POST['direction'])) 
    {
        $direction = intval($_POST['dir']);
    } 
    else 
    {
        $direction = intval($_GET['dir']);
    }
    
    if ($direction != 1) 
    {
        $direction = 0;
    }
    
    
    $order = __paramInit('string', 'order', 'order', 'gnr');
    switch ($order) 
    {
        case "sbr":
            $orderby = "sbr";
            $str_rating = "Рекомендации";
            break;
        case "ops":
            $orderby = "opinions";
            $str_rating = "Мнения";
            break;
        case "pph":
            $orderby = "cost_hour";
            $str_rating = "Цена за час";
            break;
        case "ppp":
            $orderby = "cost_proj";
            $str_rating = "Цена за проект";
            break;
        case "pp1":
            $orderby = "cost_1000";
            $str_rating = "Цена за 1000 знаков";
            break;
        case "ppm":
            $orderby = "cost_month";
            $str_rating = "Цена за месяц";
            break;
        case "gnr":
        default:
            $order = 'gnr';
            $orderby = "general";
            $str_rating = "Рейтинг";
            break;
    }

    $fav_show = intval($_SESSION['fs']);
    if ($fav_show != 1) 
    {
        $fav_show = 0;
    }
    
    if (isset($_GET['fs'])) 
    {
        $fav_show = intval($_GET['fs']);
        if ($fav_show != 1) 
        {
            $fav_show = 0;
        }
        $_SESSION['fs'] = $fav_show;
    }
    
    if ($section) 
    {
        $frls = $freelancer_seo->fseoGetCatalog($count_frl_catalog, $size, $works, $frl_pp, ($page - 1) * $frl_pp, $orderby, $direction);
    } 
    else 
    {
        $frls = $freelancer->getCatalog($prof_id, $uid, $count_frl_catalog, $size, $works, $frl_pp, ($page - 1) * $frl_pp, $orderby, $direction, $fav_show, $ff);
    }

    $pages = ceil($count_frl_catalog / $frl_pp);
    
    $cur_page_url = $GLOBALS['host'] . strtok($_SERVER["REQUEST_URI"],'?') . "?".
            ($hhf_prm ? str_replace('&','',$hhf_prm).'&' : '').
            (($order && $order!='gnr')?"order=$order&":"").
            (($direction)?"dir=$direction&":"").
            (($show_all_freelancers)?"show=all&":"").                        
            (($key_word)?'word='.str_replace('%','%%', urlencode(stripslashes($key_word))).'&':'');
    
    //Cсылка для new_paginator()
    $sHref = "%s".$cur_page_url."page=%d%s";

    if ($page > 1) 
    {
        $additional_header .= '<link rel="prev" href="'.$cur_page_url . 'page=' . ($page - 1) . '">';
    }
    
    if ($page < $pages) 
    {
        $additional_header .= '<link rel="next" href="'.$cur_page_url . 'page=' .  ($page + 1) . '">';
    }

    $content = "content.php";
}

//------------------------------------------------------------------------------


if($f_city_id) {
  $city_info = city::getCity($f_city_id);
  $page_title = preg_replace("/%cityname%/", ' '.$city_info['city_name'], $page_title);
  $page_title = preg_replace("/%prepositional_cityname%/", ($city_info['prepositional_city_name'] ? ' в '.$city_info['prepositional_city_name'] : ' '.$city_info['city_name']), $page_title);
} else {
  $page_title = preg_replace("/%cityname%/", '', $page_title);
  $page_title = preg_replace("/%prepositional_cityname%/", '', $page_title);
}


//------------------------------------------------------------------------------

//require_once("metadata.inc.php");


//if ($page < 20) $buffer_on = true;

/*
$additional_header = '<script type="text/javascript" src="/scripts/kwords.js"></script>' .
                     '<script type="text/javascript" src="/kword_js.php"></script>';	
*/

//------------------------------------------------------------------------------

require_once(ABS_PATH . '/freelancers/widgets/FreelancersTServicesWidget.php');
//Инициализация виджета плитки ТУ вместо портфолио
$freelancersTServicesWidget = new FreelancersTServicesWidget();

//------------------------------------------------------------------------------

//Популярные услуги из этой же категории
require_once(ABS_PATH . '/tu/widgets/TServicesPopular.php');
$tservicesPopular = new TServicesPopular();
$tservicesPopular->setOptions(array(
    'prof_group_id' => $prof_group_id,
    'prof_id' => $prof_id,
    'limit' => 9,
    'title' => 'Услуги фрилансеров',
    'title_css' => 'b-layout__title_padtop_10',
));
$tservicesPopular->init();

//------------------------------------------------------------------------------


if ($uid > 0 && !is_emp() && !in_array($action, array('search','search_advanced')) && is_pro()) {
    require_once(ABS_PATH . '/freelancers/widgets/FreelancersPreviewEditorPopup.php');
    $freelancersPreviewEditorPopup = FreelancersPreviewEditorPopup::getInstance(array(
        'group_id' => $prof_group_id,
        'prof_id' => $prof_id
    ));
}

//------------------------------------------------------------------------------

$header = "../header.php";
$footer = "../footer.html";
$js_file[] = '/css/block/b-text/b-text.js';
$js_file[] = '/css/block/b-popup/b-popup.js';
$css_file = array( '/css/block/b-icon/__cat/b-icon__cat.css', '/css/block/b-search/b-search.css', 'main.css', '/css/nav.css' );
$js_file[] = 'search.js';
$js_file[] = 'freelancers/freelancers.js';//@todo: Сюда переносить все inline-скрипты!
$freelancers_catalog = true;
include ("../template2.php");