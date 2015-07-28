<?php
$g_page_id = "0|37";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

$rpath    = "../../";
include($rpath . '404.php');
exit;
$stretch_page = true;
$showMainDiv  = true;
$page_title = "Поднятие закрепленного проекта - фриланс, удаленная работа на FL.ru";
$header   = "$rpath/header.php";
$content  = 'content.php';
$footer   = "$rpath/footer.html";
$template = 'template2.php';

$uid  = get_uid();
$user = null;

if ( is_emp() ) {
	$user = new users();
    $user->GetUserByUID( $uid );
}

include( $rpath . $template );

?>
