<?
define( 'IS_SITE_ADMIN', 1 );
ob_start();
$no_banner = 1;
	$rpath = "../../";
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
	session_start();
	get_uid();
	
	if (!hasPermissions('ouraccounts'))
		{header ("Location: /404.php"); exit;}



$action = $_POST['action'];
$users  = new users();


if($action) {
    switch($action) {
        case "addteam":
            $users->GetUser($_POST['login']);
            
            if($users->uid && $users->ignore_in_stats == 'f') {
                $users->ignore_in_stats = 't';
                $user_update = new users();
                $user_update->ignore_in_stats = 't';
                $error = $user_update->Update($users->uid, $error);
                
                if($error) $error_login = $error;    
            } else {
                if($users->ignore_in_stats == 't') {
                    $error_login = "ѕользователь с логином ".$users->login." уже находитс€ в списке";
                } else {
                    $error_login = "ѕользовател€ с логином ".$_POST['login']." не существует";
                }
            }
            break;
        case "delteam":
            $users->GetUser($_POST['login']);
            
            if($users->uid && $users->ignore_in_stats == 't') {
                $users->ignore_in_stats = 'f';
                $user_update = new users();
                $user_update->ignore_in_stats = 'f';
                $error = $user_update->Update($users->uid, $error);
                
                if($error) $error_login = $error;    
            } else {
                if($users->ignore_in_stats == 'f') {
                    $error_login = "ѕользовател€ с логином ".$_POST['login']." нет в списке";
                } else {
                    $error_login = "ѕользовател€ с логином ".$_POST['login']." не существует";
                }
            }
            
            break;    
    }
}

$users_team = $users->GetUsers("ignore_in_stats = 't'", "login ASC");


$css_file = array('moderation.css','new-admin.css','nav.css');
$content = "../content.php";
$inner_page = "inner_index.php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

ob_end_flush();