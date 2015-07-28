<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
session_start();
get_uid();
  
if(!hasPermissions('adm') || !hasPermissions('users')) {
  header ("Location: /404.php");
  exit;
}

$op = new opinions();

if ($_GET['ds']) $ds = strtotime($_GET['ds']);
if ($_GET['de']) $de = strtotime($_GET['de']);
if (!$ds) $ds = mktime(0, 0, 1, date('m'), date('d'), date('Y'));
if (!$de) $de = mktime(23, 59, 59, date('m'), date('d'), date('Y'));

switch ($_GET['filter']) {
    case 'pos': $rating =  1; break;
    case 'neg': $rating = -1; break;
    case 'zero': $rating = 0; break;
    default: $rating = NULL;
}

$login = __paramInit('string', 'login');

list($data, $buser) = $op->getOpinionsData($ds, $de, $rating, $login);
if (!$data) $data = array();

$content = "../content.php";

$inner_page = "inner_index.php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
