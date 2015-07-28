<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');//Для чего тут?


session_start();
$uid = get_uid();
$stop_words = new stop_words(hasPermissions('users') );
$g_page_id = "0|25";
$name = trim($_GET['user']);
$page = trim($_GET['p']);
$template = 'template2.php';
$css_file = array( 'profile.css', 'opinions.css','/css/block/b-opinion/b-opinion.css', '/css/block/b-icon/__cont/b-icon__cont.css', '/css/nav.css', '/css/block/b-voting/b-voting.css' );
$js_file  = array( 'warning.js', 'note.js', 'status.js', 'banned.js', 'tawl.js', 
    'paid_advices.js', '/css/block/b-filter/b-filter.js', '/css/block/b-fon/b-fon.js', '/css/block/b-layout/b-layout.js', 
    'del_acc.js', 'sbr.js', 'specadd.js', 'drafts.js', 'polls.js', 'mAttach.js', 'blogs_cnt.js', 'blogs.js', 
    'opinions.js', 'calendar.js', 'projects-quick-edit.js', 'attachedfiles.js', 'projects.js');

//rus
$page_keyw = "работа, удаленная работа, поиск работы, предложение работы, портфолио фрилансеров, fl.ru";
$page_descr = "Работа. Удаленная работа. Поиск работы. Предложение работы. Портфолио фрилансеров. FL.ru";

$user = new employer();
$user->GetUser($name);
$p_user = $user;

if(strtolower($user->uid)==strtolower($uid) && is_pro(true)) { $no_adv = true; } else { $no_adv = false; }

switch ($page){
    case "rating": 
        $css_file[] = "promotion.css";
        if($user->uid == $uid) {
            $js_file[] = 'raphael-min.js';
            $js_file[] = 'svg.js';
        }
        $inner = "rating_inner.php";
        $activ_tab = 11;
        break;
        
    case "project":
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        $prj_id = intval(trim($_GET['prjid']));
        // todo: переполнение integer в БД.
        if (($prj_id < -2147483648) || ($prj_id > 2147483647)) {
        	include ABS_PATH."/404.php"; exit;
        }
        $projects = new projects();
        $uid = $user->GetUid($err);
        $prj = $projects->GetPrj($uid, $prj_id);
        if ($prj['pro_only'] == "t" && !$_SESSION['pro_last'] && $_SESSION['login'] !== $prj['login']  && !hasPermissions('projects')) header("Location: /proonly.php");
        $inner = "aboutprj_inner.php";
        $activ_tab = 0;
        $page_keyw = "требуется дизайнер, программист, требуется менеджер, фотограф,
  переводчик, автор, журналист, ищу, работа, вакансия, услуги, дизайн,
  сайт, оптимизация, хостинг, флэш, баннер, портфолио, резюме, москва,
  петербург";
        $page_descr = substr(preg_replace("'[\r\n\s]+'", " ", input_ref($prj['descr'])), 0, 150);
        $page_title = $prj['name'];
        break;
        
    case "opinions": 
        require_once($_SERVER['DOCUMENT_ROOT']."/user/opinions.action.php");
        $inner = "../opinions_inner.php"; 
        $activ_tab = 5; 
        $css_file[] = 'opinions.css'; 
        break;
    case "info": 
        $inner = "inform_inner.php"; 
        $activ_tab = 2; 
        break;
    case "all":
    	$mode = intval($_GET['mode']);
		if( !($mode > 0 && $mode <= 4)) {
			include ABS_PATH."/404.php"; exit;
		}
        $content = "all_inner.php";
        break;
        
    case "tu-orders":

        if(!(hasPermissions('users'))) 
        {
            if ($uid && !is_emp()) {
                header_location_exit("/tu-orders/");
                exit;
            } elseif ($user->uid != $uid) {
                include ABS_PATH."/404.php"; exit;
            }
        }
        
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/yii/tinyyii.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderStatus.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderFeedback.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesArbitragePopup.php');
        
        // Формируем JS внизу страницы
        define('JS_BOTTOM', true);
        $js_file[] = 'mootools-form-validator.js';
        $js_file[] = 'tservices/tservices_order.js';
        
        $page = __paramInit('int', 'page', 'page', 1);
	if($page <= 0) $page = 1;
        $on_page = 10;
        
        $tu_order_status = __paramInit('string', 's', 's', NULL);
        
        $is_owner = $user->uid == $uid;
        
        $tserviceOrderModel = new TServiceOrderModel();
        
        //Если параметры не проходят валидацию то редирект на основную по умолчанию
        if(!$tserviceOrderModel->attributes(array('status' => $tu_order_status))) 
        {
            header("Location: /users/{$user->login}/tu-orders/" , TRUE, 301);
            exit;
        }
        
        $orders_list = $tserviceOrderModel->setPage($on_page,$page)->getListForEmp($user->uid);
        $tu_orders_cnts = $tserviceOrderModel->getCounts($user->uid);
        
        //Если вообще ничего нет то 404
        if(!$tu_orders_cnts['total'])
        {
            include ABS_PATH."/404.php"; exit;
        }
        
        //Виджет для рендера статуса
        $tserviceOrderStatusWidget = new TServiceOrderStatus();
        $tserviceOrderStatusWidget->setIsOwner($is_owner);
        $tserviceOrderStatusWidget->setIsEmp(TRUE); 
        $tserviceOrderStatusWidget->is_list = true;
        $tserviceOrderStatusWidget->init();
        
        $modelMessage = new TServiceMsgModel();
        
        //Виджет формы отзывов только для инициализации и поключения скрипты.
        //Основное использование с виджете статуса но там подключать скрипт уже позно.
        //При использование виджетов в системе MVC (/tu/) этот хак не нужен. А знаешь почему?
        $tserviceOrderFeedbackWidget = new TServiceOrderFeedback();
        $tserviceOrderFeedbackWidget->init();
        
        $reservesArbitragePopup = new ReservesArbitragePopup();
        $reservesArbitragePopup->init();
        
        $inner = "tu-orders_inner.php";
        $activ_tab = 12; 
        
        //SEO
        $page_title = 'Заказы типовых услуг на FL.ru';
        //$page_descr = '';
        //$page_keyw = '';
        
        break;
    
    case "projects":
    default:
        
        //if ($_SESSION['login'] == $name) { $inner = "setup/projects_inner.php"; $_in_setup = 1;}
        //else {$inner = "projects_inner.php";}
        
        $inner = "projects_inner.php";
        $activ_tab = 1;
        
        $page = __paramInit('page', 'page', 'page', 1);
        
        break;
}
if ($_SESSION['p_ref']) unset($_SESSION['p_ref']);


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
$is_pro = payed::CheckPro($user->login);
$no_banner = !!$is_pro;


$action = trim(__paramInit('string', 'action', 'action'));

switch ($action){
    case 'activated':
        $alert_message = "Аккаунт активирован";
        break;
    case "prj_close":
        $kind = __paramInit('int', null, 'kind', 0);
		$prj_id = __paramInit('int', null, 'project_id');
        $do_close = __paramInit('bool', null, 'do_close');
		$projects = new projects();
		if ($prj_id) $error .= $projects->SwitchStatusPrj(get_uid(), $prj_id);
                    
		$location  = "/users/{$name}/projects/?kind={$kind}" . ($do_close ? '&closed=1' : '');
		header("Location: $location"); //перекидываем на текущую страницу, чтобы нельзя было повторить POST по F5
		exit;
		break;
    case "prj_delete":
        if (hasPermissions('projects') && $_SESSION["rand"] == $_POST["u_token_key"]) {
          $prj_id = (int)trim(__paramInit('int', 'prjid', 'prjid'));
          require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
          $prj = new projects();
          $prj->DeletePublicProject($prj_id,get_uid(), 1);
        }
        break;
    case "change_bn" :
        if(!hasPermissions('users')) break;
        $emp = new employer();
        $emp->boss_note = __paramInit('string',NULL,'boss_note','');
        $emp->boss_rate = __paramInit('int',NULL,'boss_rate',0);
        $emp->update($user->uid, $res);
        unset($emp);
        header("Location: /users/{$user->login}".($page ? "/{$page}/" : ''));
        exit;
        break;
    case "prj_trash":
        $location = __paramInit('string', null, 'location', '/');
		$prj_id = __paramInit('int', null, 'project_id');
        $do_remove = __paramInit('bool', null, 'do_remove');
		$projects = new projects();
		if ($prj_id) $error .= $projects->switchTrashProject(get_uid(false), $prj_id, $do_remove);

		header("Location: " . str_replace($GLOBALS['host'], '', $location)); //перекидываем на текущую страницу, чтобы нельзя было повторить POST по F5
		exit;
		break;
    case "prj_express_public":
        $location = __paramInit('string', null, 'location', '/');
		$prj_id = __paramInit('int', null, 'project_id');
		$projects = new projects();
		if ($prj_id && $user->is_pro == 't') $error .= $projects->publishedMovedToVacancy(array('uid' => $uid), $prj_id);

		header("Location: " . str_replace($GLOBALS['host'], '', $location)); //перекидываем на текущую страницу, чтобы нельзя было повторить POST по F5
		exit;
		break;

}

$header = "../header.php";
$footer = "../footer.html";

if (!$user->login)
{
    include ABS_PATH."/404.php"; exit;
}

$obj_memcache = new memBuff();
if($_SESSION['login']) {
    if($_SESSION['login']!=$user->login) {
        if(!$obj_memcache->get('user_view_'.strtolower($user->login).'_'.strtolower($_SESSION['login']))) { 
            $obj_memcache->set('user_view_'.strtolower($user->login).'_'.strtolower($_SESSION['login']), 1, 3600); 
            $user->IncHits($user->login);
        }
    }
} else {
    if(!$obj_memcache->get('user_view_'.strtolower($user->login).'_'.getRemoteIP())) { 
        $obj_memcache->set('user_view_'.strtolower($user->login).'_'.getRemoteIP(), 1, 3600); 
        $user->IncHits($user->login);
    }
}

if (($user->is_banned) && !hasPermissions('users')  )  { if (!$content) $content = "ban.php"; }
else {
    if (!$content) {
      $content = $page == 'opinions' ? 'content_new.php' : 'content.php'; // !!! проверить
      /*
        if ($_SESSION['login'] == $name) {
            $content = "content_setup.php";
            $fpath = $_SERVER['DOCUMENT_ROOT'] . "/user/employer/";
        } else {
            $content = "content.php";
        }
      */
    }
}

//Мета-теги
SeoTags::getInstance()->initByUser($user);
$page_title = SeoTags::getInstance()->getTitle();
$page_descr = SeoTags::getInstance()->getDescription();
$page_keyw = SeoTags::getInstance()->getKeywords();

$body_class = ($user->is_pro=='t' ? 'p-pro' : 'p-nopro');

if ( hasPermissions('users') ) {
    $js_file[] = 'adm_edit_content.js';
}


include ("../" . $template);