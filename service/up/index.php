<?php
$g_page_id = "0|36";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
$rpath    = "../../";
include($rpath . '404.php');
exit;
$stretch_page = true;
$showMainDiv  = true;
$page_title = "Поднятие проекта - фриланс, удаленная работа на FL.ru";
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
$up_price = array ( 'kon' => new_projects::getPriceByCode( ( is_emp() && is_pro() ? new_projects::OPCODE_KON_UP : new_projects::OPCODE_KON_UP_NOPRO )),
                    'prj' => new_projects::getPriceByCode( ( is_emp() && is_pro() ? new_projects::OPCODE_UP : new_projects::OPCODE_UP_NOPRO )) );
include( $rpath . $template );

?>
