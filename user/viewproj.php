<?
$rpath = "../";
$header = "../header.php";
$footer = "../footer.html";

require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/links.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");
session_start();
//$stop_words = new stop_words( hasPermissions('users') );
$GLOBALS[LINK_INSTANCE_NAME] = new links();
$name = htmlspecialchars(trim($_GET['user']));

$user_obj = new users();
$user_obj->GetUser($name);
$uid = $user_obj->uid;
if(!$uid) {
    header("Location: /404.php"); 
    exit;
}

// Если юзер забанен, то не показываем его работу.
if ($user_obj->is_banned == 1 && !(hasPermissions('users'))) {$fpath = "../"; include(ABS_PATH."/404.php"); exit;}

$prjid = __paramInit('int', 'prjid');
if(!$prjid) {
    header("Location: /404.php"); 
    exit;
}


///////////////////////////////////////////////////////////////////////
////////////////////////stat_collector/////////////////////////////////
///////////////////////////////////////////////////////////////////////
if($uid<>get_uid(false)) {
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
  $scl = new stat_collector();
  $ref_id = __paramInit('int','f',NULL,0);
  if($ref_id == 4) $stamp = intval($_GET['stamp']);
  else $stamp = false;
  if($uid)
    $scl->LogStat($uid, (int)get_uid(false), $_SERVER['REMOTE_ADDR'], $ref_id, (int)is_emp(), $stamp);
  unset($scl);

}
///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////

$skip_blocked = ( $user_obj->uid == get_uid(false) || hasPermissions('users') ) ? false : true;

$prj_prev = (int)portfolio::GetPrjNear($user_obj->uid, $prjid, -1, $skip_blocked);
$prj_next = (int)portfolio::GetPrjNear($user_obj->uid, $prjid, 1, $skip_blocked);


$action = trim($_GET['action']);

if ($action == "delete"){
	$portf = new portfolio();
	if ($prjid) $error .= $portf->DelPortf(get_uid(false),$prjid, hasPermissions('users'));
	$pattern = "#(/users/[\w_\d]+/)viewproj\.php\?prjid=[0-9]+.*#";
	$locate_url = preg_replace($pattern, '$1', $_SERVER['REQUEST_URI']);
	header ("location: $locate_url");
	exit;
}

$proj = portfolio::GetPrj($prjid, $uid);
$width = $height = 0;
if ( !$proj || $proj['is_blocked'] == 't' && $uid != get_uid(false) && !hasPermissions('users') ) {$fpath = "../"; include(ABS_PATH."/404.php"); exit;}
else {
	$spec_text = professions::GetProfName($proj['spec']);
    if ($proj['pict']) {
        $imgTitle = $proj['name'] . ' (' . $spec_text . ') - фри-лансер ' . $proj['uname'] . ' ' . $proj['usurname'] . ' [' . $proj['login'] . ']. ';
        $str = viewattach($proj['login'], $proj['pict'], "upload", $file, -1, -1, 1048576, 0, 0, 'center', false, 1, $proj['name'], true, false, $imgTitle, $proj["wmode"]);
    }
	//elseif ($proj['link'] && !$proj['descr']) header("Location: http://" . $proj['link']);
    $pathinfo = pathinfo($proj['pict']);
    $proj['pict_ext'] = strtolower($pathinfo['extension']);
	
    $js_file   = array( 'banned.js' );
    
    //Мета-теги
    SeoTags::getInstance()->initByPortfolio($proj, $spec_text);
    $page_title = SeoTags::getInstance()->getTitle();
    $page_descr = SeoTags::getInstance()->getDescription();
    $page_keyw = SeoTags::getInstance()->getKeywords();

	$FBShare = array(
        "title"       => htmlspecialchars($proj['name'], ENT_QUOTES),
        "description" => "",
        "image"       => HTTP_PREFIX."www.free-lance.ru/images/free-lance_logo.jpg" 
    );
	$content = 'tpl.viewproj.php';
}

$css_file = array( '/css/nav.css');

$no_banner = true;
$template = 'template2.php'; 
include ("../".$template);
?>
