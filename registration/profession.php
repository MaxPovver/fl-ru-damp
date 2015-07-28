<?php

$hide_banner_top = true;
$g_page_id = "0|993";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

// страницу могут смотреть только зарегистрированые фрилансеры
$uid = get_uid(false);

if (!$uid || is_emp()) {
    include $_SERVER['DOCUMENT_ROOT'] . "/403.php";
    exit;
}

$profession_id = __paramInit('int',NULL,'profession',NULL);

if ($profession_id !== NULL) {
    
    $redirect_to = '/projects/';
    
    if ($profession_id > 0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_filter.php");

        //Создаем фильтр для проектов
        $f_category[1][$profession_id] = 1;

        $prj_filter = new projects_filters();
        $prj_filter->Save(
                $uid, 
                '', 
                '', 
                2, 
                true, 
                $f_category, 
                '', 
                '', 
                '', 
                false, 
                $rerror, 
                $error, 
                0, 
                0, 
                false, 
                false, 
                false, 
                false, 
                false, 
                false, 
                false, 
                false, 
                4, 
                false, 
                false);

        setcookie("new_pf0", 1, time()+60*60*24*30, "/");
        
        //Сохраняем как основную специализацию
        $or_spec=professions::GetProfessionOrigin($profession_id);
        $frl = new freelancer;
        $frl->spec = $profession_id;
        $frl->spec_orig = $or_spec;
        professions::setLastModifiedSpec($uid, $profession_id);
        $frl->Update($uid, $error);
        $_SESSION['specs'] = $frl->GetAllSpecs($uid);
    }
    
    //Если есть редирект то он приоритетней
    if ($_SESSION['ref_uri']) {
        $redirect_to = urldecode($_SESSION['ref_uri']);
    }
    

    $_user_action = (isset($_REQUEST['user_action']) && $_REQUEST['user_action'])?substr(htmlspecialchars($_REQUEST['user_action']), 0, 25):'';
    $_user_action = trim($_user_action);
    
    switch($_user_action) {
        case 'tu':
            if (isset($_SESSION['tu_ref_uri'])) {
                $redirect_to = HTTP_PFX . $_SERVER["HTTP_HOST"] . urldecode($_SESSION['tu_ref_uri']);
            }
            break;
            
        case 'new_tu':
            $redirect_to = HTTP_PFX . $_SERVER["HTTP_HOST"] . '/users/' . $_SESSION['login'] . '/tu/new/';
            break;
        
        case 'promo_verification':
            $redirect_to = '/promo/verification/';
            break;
        
        case 'buypro':
            $redirect_to = '/payed/';
            break;
        
    }
    
    
    header("Location: {$redirect_to}");
    exit;
}

//Если есть другой редирект то выставляем этот флаг
$is_other_redirect = isset($_SESSION['ref_uri']) || isset($_REQUEST['user_action']);

//Получить список профессий с указанной сортировкой
$professions_data = professions::GetProfessionsAndGroup('gname, name');

$stretch_page = true;
$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.profession.php";

include("../template3.php");