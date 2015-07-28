<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
session_start();
get_uid();
  
if (!(hasPermissions('adm') && hasPermissions('ratinglog'))) {
    header ("Location: /404.php"); exit; 
}

$action = __paramInit('string', 'action', 'action');
$page   = __paramInit('int', 'page', 'page');
$rating = new rating();

switch ($action) {
    case 'search_user':
        $limit = 100;
        $count = 0;
        if( $page < 0 || $page == null) $page = 1;
        $login = __paramInit('string', 'login_user', 'login_user');
        $filter = __paramInit('int', 'filter_factor', 'filter_factor');
        if(($filter < 0 && $filter > 30) || $filter===null) $filter = false;
        $rlog = $rating->getRatingLog($login==''?false:$login, $filter==-1||$filter===null?false:$filter, $limit, $page, $count);
        if($login!='') {
            $href_pager[] = "login_user={$login}";     
        }
        if($filter!=-1) {
            $href_pager[] = "filter_factor={$filter}";       
        }
        $href_pager[] = "action=search_user";
        $href = implode("&", $href_pager);
        if($href!= "") $href = "&".$href;
        $pages = ceil($count/$limit);
        $not_search = true;
        
        $verificationTime = $rlog[0]['ver_data_ff'] ? strtotime($rlog[0]['ver_data_ff']) : ($rlog[0]['ver_data_wm'] ? strtodata($rlog[0]['ver_data_wm']) : null);
        
        break;
    default:
        break;
}

$content    = '../content22.php';
$inner_page = 'tpl.index.php';
$css_file   = array( 'moderation.css', 'new-admin.css', 'nav.css' );

include ($rpath . "template2.php");
?>
