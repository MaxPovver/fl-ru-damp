<?

if (!defined('IS_SITE_ADMIN')) {
    header ("Location: /404.php"); 
    exit;
}

$menu_item = 12;
$prj_obj = new projects;
$projects = $prj_obj->GetComplainPrjs( $num_threads, $error, $page, $sort, $search, $admin, $log_pp, $group );
if ($action && $_SESSION["rand"] != $_POST["u_token_key"]) {
    header ("Location: /403.php"); 
    exit;
}
switch ($action) {

    case 'delcomplain':
        $prj_obj->DeleteComplains(intval($_GET['pid']));
        header("Location: /siteadmin/ban-razban/?mode=$mode".($page? "&p=$page": '').($search? "&search=$search": '').($admin? "&admin=$admin": '').($sort? "&sort=$sort": '')."#p".$_GET['pid']);
        exit;
    break;
    case 'satisfycomplain':
        $prj_obj->SatisfyComplains(intval($_GET['pid']));
        header("Location: /siteadmin/ban-razban/?mode=$mode".($page? "&p=$page": '').($search? "&search=$search": '').($admin? "&admin=$admin": '').($sort? "&sort=$sort": '')."#p".$_GET['pid']);
        exit;
    break;
    case 'not_satisfycomplain':
        $prj_obj->NotSatisfyComplains(intval($_GET['pid']));
        header("Location: /siteadmin/ban-razban/?mode=$mode".($page? "&p=$page": '').($search? "&search=$search": '').($admin? "&admin=$admin": '').($sort? "&sort=$sort": '')."#p".$_GET['pid']);
        exit;
    break;

}

$css_file   = array( 'moderation.css', 'nav.css' );
include $rpath.'template.php';

?>
