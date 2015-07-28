<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/login_change.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid();

if(!hasPermissions('adm') || !hasPermissions('changelogin')) {
  header ("Location: /404.php");
  exit;
}

$login_change = new login_change();

if ($_POST['ds']) $ds = date("Y-m-d",strtotime($_POST['ds']));
if ($_POST['de']) $de = date("Y-m-d",strtotime($_POST['de']));
if (!$ds) $ds = date("Y-m-d",mktime(0, 0, 1, date('m'), date('d'), date('Y')));
if (!$de) $de = date("Y-m-d",mktime(23, 59, 59, date('m'), date('d'), date('Y')));

switch ($_GET['filter']) {
    case 'pos': $rating =  1; break;
    case 'neg': $rating = -1; break;
    case 'zero': $rating = 0; break;
    default: $rating = NULL;
}

$old_login = trim($_POST['old_login']);
$new_login = trim($_POST['new_login']);
$save = intval($_POST['save']);

if ($old_login && $new_login && $_SESSION["rand"] == $_POST["u_token_key"]) {
    $new_login = substr(strip_tags(trim(stripslashes($new_login))),0,15);
    if (!preg_match("/^[a-zA-Z0-9]+[-a-zA-Z0-9_]{2,}$/", $new_login)) $error = "Поле заполнено некорректно";
    
    if(!$error) {
    	$login_change->new_login = $new_login;
    	$login_change->old_login = $old_login;
    	$login_change->save_old = $save;
    	$data = $login_change->Add($error);
    	if (!$error) {
    		header('Location: /siteadmin/login/'); exit; 
    	}
    }
}

$login = trim($_GET['login']);
$date  = date("Y-m-d H:i:s",$_GET['date']);
$data  = $login_change->getAllForAdmin( $login, $date, $ds, $de );

$content = "../content.php";
$css_file = array( "moderation.css", 'new-admin.css', 'nav.css' );
$inner_page = "inner_index.php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
