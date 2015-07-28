<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");

$rpath = "../";

session_start();
$uid = get_uid();

$name = $_GET['user'];
$mode = intval($_GET['mode']);
$user = new users();
$user->GetUser($name);
$role = $user->role;
$is_emp = 0;

if(substr($role, 0, 1)  == '1')  {
  $user = new employer();
  $is_emp = 1;
}
else
  $user = new freelancer();

if( !($mode > 0 && $mode <= 4) || ($mode==4)!=(!!$is_emp) ) {
  header ("Location: /404.php");
  exit;
}

$user->GetUser($name);

$header = "../header.php";
$footer = "../footer.html";
$content = "all_inner.php";
$js_file = array( 'note.js' );
include("../template.php");
?>
