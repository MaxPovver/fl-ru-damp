<?php
define( 'IS_SITE_ADMIN', 1 );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );

session_start();
get_uid();

if ( !hasPermissions('suspiciousip') ) {
    header_location_exit('/404.php');	
}

define('RECORDS_ON_PAGE', 100);

$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
if ($page < 1) $page = 1;

$no_banner  = 1;
$rpath      = "../../";
$header     = $rpath."header.php";
$content    = '../content2.php';
$inner_page = "inner_index.php";
$footer     = $rpath."footer.html";
$aRecords   = users::GetSuspiciousIPs( $nTotal, RECORDS_ON_PAGE, ($page - 1) * RECORDS_ON_PAGE );
$css_file   = array( 'moderation.css', 'new-admin.css', 'nav.css' );

include ( $rpath . "template2.php" );

?>
