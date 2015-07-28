<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
	$rpath = "../../";
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
	session_start();
	get_uid();
	
	if (!(hasPermissions('adm') && (hasPermissions('stats') || hasPermissions('tmppayments')) ))
		{header ("Location: /404.php"); exit;}
	
$content = "../content.php";


$inner_page = trim($_GET['page']);
if ( !in_array($inner_page, array('charts', 'charts2', 'charts3', 'charts4', 'geo')) ) {
    $inner_page = "index";
}

if($inner_page=='index') {
    switch($_GET['t']) {
        case 'd':
            $inner_page = $inner_page.'_d';
            break;
        case 'g':
            $inner_page = $inner_page.'_g';
            break;
        case 'p':
            $inner_page = $inner_page.'_p';
            break;
        case 'c':
            $inner_page = $inner_page.'_c';
            break;
        case 'u':
            $inner_page = $inner_page.'_u';
            break;
        case 'v':
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Verification.php");
            $inner_page = $inner_page.'_v';
            break;
        default:
            $inner_page = $inner_page.'_d';
            break;
    }
}

$inner_page = "inner_".$inner_page.".php";
$css_file = array(
    'moderation.css',
    'new-admin.css',
    'nav.css');

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
