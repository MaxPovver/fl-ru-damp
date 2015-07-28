<?php 
$no_banner = 1;
$enter=true;
$header = "../../header.php";
$footer = "../../footer.html";
$page_title = "Фрилансер. Работодатель. Удаленная работа. Поиск работы. Предложение работы. Портфолио фрилансеров. FL.ru";
$page_keyw = "фрилансер, работодатель, удаленная работа, поиск работы, предложение работы, портфолио фрилансеров, разработка сайтов, программирование, переводы, тексты, дизайн, арт, реклама, маркетинг, прочее, fl.ru";
$page_descr = "Фрилансер. Работодатель.Удаленная работа. Поиск работы. Предложение работы. Портфолио фрилансеров. Разработка сайтов, Программирование, Переводы, Тексты, Дизайн, Арт, Реклама, Маркетинг, Прочее. FL.ru";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_offers.php");
session_start();
$uid = get_uid();

$error_page = "../../404.php";
$template_page = "../../template2.php";
include("../../403.php");
exit; 
if(is_emp() && !hasPermissions('projects')) {
    $content = "tpl.error-offers.php";
    include($template_page);
    exit;
}
    
if(!$_SESSION['uid']) {
    include("../../fbd.php");
    exit;    
}

$frl_offers = new freelancer_offers();
$categories = professions::GetAllGroupsLite();
$categories_specs = professions::GetAllProfessions((intval($project['category'])?intval($project['category']):$categories[0]['id']));
      
$action  = __paramInit('string', 'action', 'action');
$fid     = intval($_GET['fid']);
if($action == 'edit' && intval($_GET['fid']) <= 0) {
    include($error_page);
    exit; 
}
$content = "tpl.public-offers.php";
$js_file = array( 'tawl.js' );

if($action) {
    if($_POST['action']) {
        if(trim($_POST['title']) == "") {
            $error['title'] = true;
        }
        if(trim($_POST['descr']) == "") {
            $error['descr'] = true;
        }

        if($_POST['categories'] == 0) {
            $error['categories'] = true;
        }
           
        if( strlen_real($_POST['descr']) > freelancer_offers::MAX_SIZE_DESCRIPTION ) {
            $error['descr_max'] = true;
        }
         
        if( strlen($_POST['title']) > freelancer_offers::MAX_SIZE_TITLE ) {
            $error['title_max'] = true;
        }
    }
    
    if($action != "create" && $action != "update" && $fid) {
        $offer = $frl_offers->getOfferById($fid);
        if(!$offer) {
            include($error_page);
            exit;
        }
    }
   
    switch($action) {
        case "create":
            if(isset($error)) {
                $categories_specs = professions::GetAllProfessions($_POST['categories']);
                break;
            }
            $create = array("user_id"        => (int)$_SESSION['uid'],
                            "title"          => $_POST['title'],
                            "descr"          => $_POST['descr'],
                            "category_id"    => (int)$_POST['categories'],
                            "subcategory_id" => (int)$_POST['subcategories']
                            );
            
            $id_offer = $frl_offers->Create($create);
            if($id_offer > 0) {
                //$_SESSION['bill.GET']['addinfo'] = "<a href='/?kind=8#o_{$id_offer}'>Перейти к оплаченному предложению</a>";
                header("Location: /public/offer/offer_published.php?offer_id=$id_offer");
            }
            break;
        case "update":
            if(!$fid) $fid = intval($_POST['fid']);
            $is_edit = true;
            if(isset($error)) {
                $categories_specs = professions::GetAllProfessions($_POST['categories']);
                break;
            }
            $update = array("title"          => $_POST['title'], 
                            "descr"          => $_POST['descr'], 
                            "category_id"    => (int)$_POST['categories'], 
                            "subcategory_id" => (int)$_POST['subcategories']);
            
            $frl_offers->Update(intval($_POST['fid']), $update);
            if(isset($_POST['page'])) $page = intval($_POST['page']);
            $page_uri = "";
            if($page<0) $page = 0;
            if($page>0) $page_uri = "&page={$page}";
            
            $back = __paramInit('string', 'red', 'red', '');
            $back = $back ? $back : '/projects/?kind=8'.$page_uri.'#o_'.$fid;
            header("Location: {$back}");
            break;
        case "edit":
            $is_edit = true;
            $categories_specs = professions::GetAllProfessions($offer['category_id']);
            break;
        case "delete":
            $frl_offers->Delete($fid);
            $success_text = "Предложение удалено";
            $content = "tpl.success-offers.php";
            break; 
        case "open":  
            $update = array("is_closed" => 'f');
        case "close":
            if(!$update) $update = array("is_closed" => 't');
            $frl_offers->Update($fid, $update);
            $success_text = "Предложение снято с публикации";
            $content = "tpl.success-offers.php";
            break;  
        case "unblock": 
            $update = array("is_blocked" => 'f');
            $success_text = "Предложение разблокировано";
        case "block":
            if(!hasPermissions('projects')) {
                include($error_page);
                exit;
            }
            if(!$update) $update = array("is_blocked" => 't');
            if(!$success_text) $success_text = "Предложение заблокировано";
            $frl_offers->Update($fid, $update);
            
            $content = "tpl.success-offers.php";
            break;   
    }
}
    
$css_file[] = "nav.css";

include ($template_page);
  



?>
