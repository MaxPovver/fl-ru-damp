<?
$g_page_id = "0|9";
$g_help_id = 101;
$rpath = "../";
$stretch_page = true;
$showMainDiv  = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");

session_start();
$uid = get_uid();

if(!$uid) { header('Location: /403.php'); exit; }

$p = strtolower(__paramInit('string', 'p', 'p'));
$action = __paramInit('string', 'draft_frm_action', 'draft_frm_action');

if(!empty($p) && !in_array($p, array('projects', 'contacts', 'blogs', 'communes', 'contacts'))) {
    header('Location: /drafts');
    exit;
}

switch($action) {
    case 'delete':
        $drafts = $_POST['del_draft'];
        switch($p) {
            case 'projects':
                $type = 1;
                break;
            case 'contacts':
                $type = 2;
                break;
            case 'blogs':
                $type = 3;
                break;
            case 'communes':
                $type = 4;
                break;
            default:
                $type = 0;
                break;
        }
        if($drafts && $type) {
            foreach($drafts as $draft) {
                drafts::DeleteDraft($draft, $uid, $type, true);
            }
        }
        header('Location: /drafts/?p='.$p);
        exit;
        break;
}

if ( empty($p) ) {
    $c = drafts::GetCounts($uid);
    if ( !empty($c['projects']) && is_emp() ) {
        $p = 'projects';
    } else if ( !empty($c['contacts']) ) {
        $p = 'contacts';
    } else if ( !empty($c['blogs']) ) {
        $p = 'blogs';
    } else if ( !empty($c['communes']) ) {
        $p = 'communes';
    } else if ( is_emp() ) {
        $p = 'projects';
    } else {
        $p = 'contacts';
    }
}
if(BLOGS_CLOSED == true && $p == 'blogs') {
    $p = 'communes';
}
switch($p) {
    case 'projects':
        $drafts = drafts::getUserDrafts($uid, 1);
        $content = "content_projects.php";
        break;
    case 'contacts':
        $drafts = drafts::getUserDrafts($uid, 2);
        $content = "content_contacts.php";
        break;
    case 'blogs':
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
        $drafts = drafts::getUserDrafts($uid, 3);
        $u = new users();
        $u->GetUserByUID($uid);
        if ( $u->ban_where ) {
            $is_ban = $u->ban_where;
            $ban = $u->GetBan($uid, $u->ban_where);
        }
        $content = "content_blogs.php";
        break;
    case 'communes':
        $drafts = drafts::getUserDrafts($uid, 4);
        $content = "content_communes.php";
        break;
}

$header = "../header.php";
$footer = "../footer.html";
$css_file = "drafts.css";
$js_file = array( 'drafts.js' );

include ("../template2.php");

?>
