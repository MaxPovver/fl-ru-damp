<?php

define('USERS_ON_PAGE', 50);  // кол-во пользователей на страницу

if (!defined('IS_SITE_ADMIN')) {
    header ("Location: /404.php"); 
    exit;
}

$users  = new users;
$banned = $users->GetBannedUsers($nums, $error, $page, $sort, $ft, $search, $admin);

$css_file   = array( 'moderation.css', 'new-admin.css', 'nav.css' );
include $rpath.'template.php';

?>