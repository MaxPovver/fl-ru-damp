<?
// TODO: Убрать во время релиза tu, чтобы сохранить ссылки
/*
if (isset($_GET['p']) && $_GET['p'] == 'tu') {
   include('../404.php');
   exit();
}
*/

$rpath = "../";
$g_page_id = "0|24";
$header = "../header.php";
$footer = "../footer.html";
$stretch_page = true;
$showMainDiv  = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_phone.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");


session_start();
$footer_profile = true;
$uid = get_uid();
$stop_words = new stop_words( hasPermissions('users') );

$name = trim($_GET['user']);
$page = trim($_GET['p']);

$css_file = array( 'profile.css', 'opinions.css', '/css/block/b-icon/__cont/b-icon__cont.css', '/css/block/b-prev/b-prev.css', '/css/block/b-status/b-status.css', '/css/block/b-voting/b-voting.css', '/css/block/b-button/_vote/b-button_vote.css', '/css/nav.css', '/css/main.css', '/css/block/b-free-share/b-free-share.css', '/css/block/b-work/b-work.css');
$js_file  = array( 'warning.js', 'note.js', 'status.js', 'banned.js', 
    'paid_advices.js', '/css/block/b-filter/b-filter.js', '/css/block/b-fon/b-fon.js', '/css/block/b-layout/b-layout.js', 
    'del_acc.js', 'sbr.js', 'specadd.js', 'drafts.js', 'polls.js', 'mAttach.js', 'blogs_cnt.js', 'blogs.js', 
    'opinions.js', '/css/block/b-layout/b-layout.js', '/css/block/b-textarea/b-textarea.js', 'tawl_bem.js', 'user_profile.js' );


//rus
$user = new users();
if (strtolower($name) != "admin" && strtolower($name) != "anonymous") $user->GetUser($name); else {include ABS_PATH."/403.php"; exit;}
$p_user = $user;

if($user->uid === NULL) {
    include ABS_PATH."/404.php"; exit;    
}

$user_phone_block = user_phone::getInstance()->render(user_phone::PLACE_HEADER);

users::execOnFirstVisit($user);

if(strtolower($user->uid)==strtolower($uid) && is_pro(true)) { $no_adv = true; } else { $no_adv = false; }

if ($user->is_banned && !hasPermissions('users'))  { 
  $content = "ban.php";
  include ("../template.php");
  exit;
}

if ($user->login && $user->email == ''){
	require_once (ABS_PATH."/classes/login_change.php");
	$login_change = new login_change();
	$login_change->GetRowByOldLogin( $name );
	if ($login_change->new_login) header ("Location: /users/$login_change->new_login/"); 
	else include ABS_PATH."/404.php";
	exit;
}

// нельзя просматривать чужие портфолио в режиме ПРО
if ($_SESSION['i_want_pro'] && $user->uid != get_uid(0)) {
    unset($_SESSION['i_want_pro']);
    unset($_SESSION['pro_last']);
}

///////////////////////////////////////////////////////////////////////
////////////////////////stat_collector/////////////////////////////////
///////////////////////////////////////////////////////////////////////
if($user->uid<>$uid && !is_emp($user->role))
{
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
  $scl = new stat_collector();
  $ref_id = __paramInit('int','f',NULL,0);
  if($ref_id == 6 || $ref_id == 4 || $ref_id == 2 || $ref_id == 1 || $ref_id == 7) $stamp = intval($_GET['stamp']);
  else $stamp = false;
  
  if($user->uid) {
    $scl->LogStat($user->uid, (int)$uid, $_SERVER['REMOTE_ADDR'], $ref_id, (int)is_emp(), $stamp);
    
    // статистика по ключевым словам ------------
    $kw = $_GET['kw'];
    
    if ( $kw && $user->is_pro && preg_match('~/freelancers/~i', $_SERVER['HTTP_REFERER']) ) {
        $bIsEmp = ( is_emp() ) ? true : false;
        
    	$scl->wordsStatLog( $user->uid, (int)$uid, $_SERVER['REMOTE_ADDR'], $bIsEmp, $kw );
    }
    //-----------------------------------------------
  }
  unset($scl);
}
///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////


$role = $user->role;
$rpath = "../";

// Статус присутсвия.
$online_status = $user->getOnlineStatus4Profile();


if (is_emp($role)) { $fpath = "employer/"; include ($fpath."index.php"); exit;};

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
$is_pro = payed::CheckPro($user->login);
$no_banner = !!$is_pro;

$user = new freelancer();
$user->GetUser($name);

if(!$page && !$user->tabs[0] && $user->tabs[7])
{
    $page=$_GET['p']='tu';
}
elseif(!$page && !$user->tabs[0] && (!$user->tabs[3] && !hasPermissions('users')) && !$user->tabs[5] && !$user->tabs[6]) 
{
    $page=$_GET['p']='opinions';
}



//------------------------------------------------------------------------------
// Проверяем блокировку ТУ в профиле за не погашенный долг в ЛС

require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
$hide_tu_for_others = FALSE;
$_debt_info = TServiceOrderModel::model()->isDebt($user->uid);

if( $_debt_info && ($user->uid != $uid) && $_debt_info['is_blocked'] == 't')
{
  $hide_tu_for_others = TRUE;
}

//------------------------------------------------------------------------------



$action = __paramInit('string',NULL,'action');
switch ($action) {
  case "change_bn" :
    if(!hasPermissions('users')) break;
    $frl = new freelancer();
    $frl->boss_note = __paramInit('string',NULL,'boss_note','');
    $frl->boss_rate = __paramInit('int',NULL,'boss_rate',0);
    $frl->update($user->uid, $res);
    unset($frl);
    header("Location: /users/{$user->login}".($page ? "/{$page}/" : ''));
    exit;
}

switch ($page) {
     
    case "services":
        $inner = "services_inner.php";$activ_tab = 2;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $prfs = new professions();
        $profs = $prfs->GetSpecs($user->login);
        $spec_text = professions::GetProfName($user->spec);
        $page_descr = "Удаленная работа (фри-ланс). Портфолио фрилансера: ".$spec_text.". ";
        $page_keyw = "удаленная работа, фри-ланс, фрилансер, ";
        if ($profs) {
            foreach ($profs as $ikey => $prof)
            $out[] = str_replace("\"","",input_ref($prof['name']));
            $page_descr .= LenghtFormatEx(implode(", ", $out), 250, "");
            $page_keyw .= strtolower(LenghtFormatEx(implode(", ", $out), 250, ""));
        }
        break;
    case "info":
        $inner = "inform_inner.php";
        $activ_tab = 3;
        if(is_emp($user->role)) {
            $page_title = "Информация о работодателе $user->uname $user->usurname [$user->login] - фриланс, удаленная работа на FL.ru";
        } else {
            $page_title = "Информация о фрилансере $user->uname $user->usurname [$user->login] - фриланс, удаленная работа на FL.ru";
        }
        break;
    case "all":
    	$mode = intval($_GET['mode']);
        if( !($mode > 0 && $mode <= 3)) {
            include ABS_PATH."/404.php"; exit;
        }
        $content = "all_inner.php";
        break;
    case "opinions":
        require_once($_SERVER['DOCUMENT_ROOT']."/user/opinions.action.php");
        $css_file[] = 'opinions.css';
        $css_file[] = 'nav.css';
        $inner = "opinions_inner.php";
        $activ_tab = 5;
        if(is_emp($user->role)) {
            $page_title = "Отзывы о работодателе $user->uname $user->usurname [$user->login] - фриланс, удаленная работа на FL.ru";
        } else {
            $page_title = "Отзывы о фрилансере $user->uname $user->usurname [$user->login] - фриланс, удаленная работа на FL.ru";
        }
        break;
    case "rating":
        if($user->tabs[4]!=1 && !hasPermissions('users')) {
            include ABS_PATH."/404.php"; exit;
        }
        $inner = "rating_inner.php";
        $css_file[] = 'promotion.css';
        if($user->uid == $uid) {
            $js_file[] = 'raphael-min.js';
            $js_file[] = 'svg.js';
        }
        $activ_tab = 6;
        if(is_emp($user->role)) {
            $page_title = "Рейтинг работодателя $user->uname $user->usurname [$user->login] - фриланс, удаленная работа на FL.ru";
        } else {
            $page_title = "Рейтинг фрилансера $user->uname $user->usurname [$user->login] - фриланс, удаленная работа на FL.ru";
        }
        break;
    default:
        if (substr($user->tabs, 0, 1) == 0 && !(hasPermissions('users'))) {
            $activ_tab = -1;
        	break;
        }
    case "portfolio":
        if (substr($user->tabs, 0, 1) == 0 && !(hasPermissions('users'))) {
        	include ABS_PATH."/404.php";
        	exit;
        }
        
        // если хотим показать пользователю НЕПРО как будет выглядеть его страница будь он ПРО
        $iWantPro = __paramInit('bool', 'i_want_pro', null, false);
        // нельзя просматривать чужие портфолио в режиме ПРО
        if ($iWantPro && $user->uid != get_uid(0)) {
            $iWantPro = false;
            unset($_SESSION['i_want_pro']);
        }
        if ($iWantPro) {
            $is_pro = true;
            $_SESSION['i_want_pro'] = true;
            $_SESSION['pro_last'] = date('Y-m-d H:i:s', time());
            $user->is_pro = 't';
            $p_user->is_pro = 't';
            $no_adv = true;
        }
        
        if($user->login == $_SESSION['login']) {
            $js_file[] = 'uploader.js';
        }

        $inner = "tpl.portfolio.pro.php";
        $js_file[] = 'portfolio.js';
        $activ_tab = 1;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $prfs = new professions();
        $profs = $prfs->GetSpecs($user->login);
        $spec_text = professions::GetProfName($user->spec);
        $page_descr = "Удаленная работа (фри-ланс). Портфолио фрилансера: ".$spec_text.". ";
        $page_keyw = "удаленная работа, фри-ланс, фрилансер, ";
        if ($profs) {
            foreach ($profs as $ikey => $prof)
            $out[] = str_replace("\"","",input_ref($prof['name']));
            $page_descr .= LenghtFormatEx(implode(", ", $out), 250, "");
            $page_keyw .= strtolower(LenghtFormatEx(implode(", ", $out), 250, ""));
        }
        
        $specs_add = professions::GetProfsAddSpec($user->uid);
        
        //@todo: этот код ниже нужно переработать с использованием GaJsHelper
        //проверить все варианты чтобы не делать повторные запросы!
        
        $ga_profs = array_merge(array($user->spec), $specs_add);
        if (isset($ga_profs) && count($ga_profs)) {
            $dimension = array();
            $groups = array_unique(professions::GetGroupIdsByProfs($ga_profs));
            foreach ($groups as $group) {
                if ($group > 0) {
                    $dimension[] = '[g' . $group . ']';
                }
            }
            foreach ($ga_profs as $prof) {
                if ($prof > 0) {
                    $dimension[] = '[p' . $prof . ']';
                }
            }

            GaJsHelper::getInstance()->gaSet('dimension2', implode(',', $dimension));
        }
        
        break;
        
        
        
    case 'tu':
        
        if ((substr($user->tabs, 7, 1) == 0 || $hide_tu_for_others == TRUE) && !(hasPermissions('users'))) 
        {
            include ABS_PATH."/404.php"; 
            exit;
        }
        
        
        $on_page = ($user->is_pro === 't')?20:21;
        
        $js_file[] = "tservices/fineuploader.js";
        $js_file[] = "tservices/tservices.js";
        
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/yii/tinyyii.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/functions.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderDebtMessage.php');

        
        
        $page = __paramInit('int', 'page', 'page', 1);
	if($page <= 0) $page = 1;
        
        $is_owner = $user->uid == $uid;
        $is_perm = hasPermissions('tservices');
        $is_not_public = $is_owner || $is_perm; 
        
        $tservices = new tservices($user->uid);
        $data = $tservices->setPage($on_page,$page)->getShortList(!$is_not_public);
        $cnt = $tservices->getCount(!$is_not_public);

        //Виджет для рендера сообщения о блокировки
        $tserviceOrderDebtMessageWidget = new TServiceOrderDebtMessage();
        $tserviceOrderDebtMessageWidget->init($user->uid);
        
        $inner = "tu_inner.php";
        $activ_tab = 2;
        
        //SEO
        $page_title = 'Типовые услуги на FL.ru';
        //$page_descr = '';
        //$page_keyw = '';
        
        break;
}

if (!$user->login){include ABS_PATH."/404.php"; exit; }

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


if (!$content) $content = $page == 'opinions' ? 'content_new.php' : 'content.php';


//Мета-теги
SeoTags::getInstance()->initByUser($user);
$page_title = SeoTags::getInstance()->getTitle();
$page_descr = SeoTags::getInstance()->getDescription();
$page_keyw = SeoTags::getInstance()->getKeywords();


$body_class = ($user->is_pro=='t' ? 'p-pro' : 'p-nopro');

  if(empty($additional_header)) $additional_header = '';
      $con_clean_uri = '/users/';
      if(!empty($_GET['user'])) $con_clean_uri .= trim ($_GET['user']).'/';
      if(!empty($_GET['p'])) $con_clean_uri .= trim ($_GET['p']).'/';

      if($con_clean_uri) $additional_header .= '
<link rel="canonical" href="'.$GLOBALS['host'].htmlspecialchars($con_clean_uri).'"/>
';
  

if ( hasPermissions('users') ) {
    $js_file[] = 'adm_edit_content.js';
}


/*if (!is_emp() && $user->login === $_SESSION['login']) {
    $splashScreenNoSpec = true; // показать сплэш 
}*/

if($_SESSION['i_want_pro']) { $no_banner = true; }

$template = 'template2.php'; 
include ("../".$template);

?>