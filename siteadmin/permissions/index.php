<?
$no_banner = 1;
$rpath = "../../";
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");

session_start();
$uid = get_uid();

if(!(hasPermissions('adm') && hasPermissions('permissions'))) {
  header ("Location: /404.php");
  exit;
}

$action = __paramInit('string', 'action', 'action');

switch($action) {
    case 'group_update':
        $name = __paramInit('string', 'name', 'name');
        $id = __paramInit('int', 'id', 'id');
        permissions::updateGroup($id, $name, $_POST['rights']);
        header('Location: ?action=group_list&msg=3');
        exit;
        break;
    case 'group_edit':
        $id = __paramInit('int', 'id', 'id');
        $inner_page = "inner_group_form.php";
        $rights = permissions::getAllRights();
        $group = permissions::getGroupInfo($id);
        break;
    case 'group_insert':
        $name = __paramInit('string', 'name', 'name');
        $id = permissions::addGroup($name, $_POST['rights']);
        header('Location: ?action=group_list&msg=2');
        exit;
        break;
    case 'group_add':
        $inner_page = "inner_group_form.php";
        $rights = permissions::getAllRights();
        break;
    case 'group_list':
        $groups = permissions::getAllGroups();
        $inner_page = "inner_group_list.php";
        break;
    case 'group_delete':
        if ($_SESSION["rand"] != $_POST["u_token_key"]) {
            header ("Location: /404.php");
            exit;
        }
        $id = __paramInit('int', 'id', 'id');
        permissions::deleteGroup($id);
        header('Location: ?action=group_list&msg=1');
        exit;
        break;
    case 'user_list':
        $groups = permissions::getAllGroups();
        $group_id = __paramInit('int', 'group_id', 'group_id', -3);
        $login = strtolower(__paramInit('string', 'login', 'login'));
        $users = permissions::getUsers($group_id, $login);
        $inner_page = "inner_user_list.php";
        break;
    case 'user_delete':
        if ($_SESSION["rand"] != $_POST["u_token_key"]) {
            header ("Location: /404.php");
            exit;
        }
        $user_id = __paramInit('int', 'uid', 'uid', 0);
        permissions::deleteUser($user_id);
        header('Location: ?action=user_list');
        exit;
        break;
    case 'user_edit':
        $user_id = __paramInit('int', 'uid', 'uid');
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $user = new users();
        $user->GetUserByUID($user_id);
        $groups = permissions::getAllGroups();
        foreach($groups as $k=>$group) {
            $g_rights = permissions::getGroupInfo($group['id']);
            $groups[$k]['rights'] = $g_rights['rights'];
        }
        $rights = permissions::getAllRights();
        $user_groups_data = permissions::getUserGroups($user_id);
        $user_groups = array();
        $user_groups_rights = array();
        foreach($user_groups_data as $user_group) {
            array_push($user_groups, $user_group['id']);
            $g_rights = permissions::getGroupInfo($user_group['id']);
            if($g_rights['rights']) {
                foreach($g_rights['rights'] as $g_right) {
                    if(!in_array($g_right, $user_groups_rights)) {
                        array_push($user_groups_rights, $g_right);
                    }
                }
            }
        }
        $user_rights_data = permissions::getUserExtraRights($user_id);
        $user_rights_allow = array();
        $user_rights_disallow = array();
        foreach($user_rights_data as $user_right) {
            if($user_right['is_allow']=='t') {
                array_push($user_rights_allow, $user_right['id']);
            } else {
                array_push($user_rights_disallow, $user_right['id']);
            }
        }
        $inner_page = "inner_user_form.php";
        break;
    case 'user_update':
        $user_id = __paramInit('int', 'uid', 'uid');
        permissions::updateUser($user_id, array($_POST['groups']), $_POST['rights_allow']);
        header('Location: /siteadmin/permissions/?action=user_list');
        exit;
        break;
    default:
        header('Location: /siteadmin/permissions/?action=group_list');
        exit;
        break;
}

$content = "../content.php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

$css_file   = array( 'moderation.css', 'new-admin.css', 'nav.css' );

include ($rpath."template.php");

?>
