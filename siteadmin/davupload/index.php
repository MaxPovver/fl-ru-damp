<?
$no_banner = 1;
$rpath = "../../";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';


if (!(hasPermissions('admin'))) {
    header ("Location: /404.php"); 
    exit;
}

$mode   = __paramInit( 'string', 'mode',   null,     '' ); // раздел
$action = __paramInit( 'string', 'action', 'action', '' ); // текущее действие
$view = __paramInit( 'string', 'view', 'view', '' );     // запрос шаблона
// где находимся
if ( !in_array($mode, array('files')) ) {
    header ("Location: /404.php"); 
    exit;
}

if ( !$page ) {
    $page = 1;
}
elseif ( $page < 0 ) {
    header_location_exit( '/404.php' );
    exit;
}

$content    = '../content.php';
$js_file    = array( 'banned.js');
$css_file    = array( 'nav.css' );
$inner_page = $mode.'/content.php';
$header     = $rpath."header.php";
$footer     = $rpath."footer.html";
//$log_pp     = __paramInit( 'int', 'log_pp', 'log_pp', 20 );

define( 'IS_SITE_ADMIN', 1 );
include "$mode/index.php";
