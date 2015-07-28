<?

if (!defined('IS_SITE_ADMIN')) {
    header ("Location: /404.php"); 
    exit;
}

$comm_obj = new commune;
$communes = $comm_obj->GetBlockedCommunes($nums, $error, $page, $sort, $search, $admin);

if ($action && $_POST["u_token_key"] != $_SESSION["rand"]) {
    header ("Location: /404.php"); 
    exit;
}

switch ($action) {

    case 'unblocked':
        $comm_obj->UnBlocked(intval($_GET['comm']));
        header("Location: /siteadmin/ban-razban/?mode=$mode".($page? "&p=$page": '').($search? "&search=$search": '').($admin? "&admin=$admin": '').($sort? "&sort=$sort": ''));
        exit;
    break;
    
}

$css_file    = array( 'nav.css', 'moderation.css' );
include $rpath.'template.php';

?>