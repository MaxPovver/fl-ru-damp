<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/birthday.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
session_start();
get_uid();
if (!hasPermissions('birthday')) { header ("Location: /404.php"); exit; }

$year = __paramInit('int', 'year', 'year', 2009);
$action = __paramInit('string', 'action', 'action');
$id = __paramInit('int', 'id', 'id', NULL);
$birthday = new birthday($year);
switch($action) {
    case "add":
        $login = __paramInit('string', 'login', 'login');
        $usero = new users();
        $usero->GetUser($login);
        if(!$usero->uid) {
            $error = "Ошибка. Пользователя с логином {$login} не существует";
            break;
        }
        $usero->utype = is_emp() ? 2 : 1;
        $user['uname'] = __paramInit('string', NULL, 'name', $usero->uname);
        $user['usurname'] = __paramInit('string', NULL, 'surname', $usero->usurname);
        $user['utype'] = __paramInit('int', NULL, 'type', $usero->utype);
        if($birthday->add($usero->uid, $user)) {
            header("Location: /siteadmin/birthday/?year={$year}");
            exit;
        }
        $error = 'Ошибка.';
        break;
    case "del":
        if($birthday->del($id)) {
            header("Location: /siteadmin/birthday/?year={$year}");
            exit;
        }
        $error = 'Ошибка.';
        break;
    case "accept":
    case "unaccept":
        if($birthday->accept($id)) {
            header("Location: /siteadmin/birthday/?year={$year}");
            exit;
        }
        $error = 'Ошибка.';
        break;
		case 'close':
		case 'open':
        if($birthday->setStatus($action)) {
            header("Location: /siteadmin/birthday/?year={$year}");
            exit;
        }
        $error = 'Ошибка.';
    default:
        break;
}

	
$content = "../content.php";
$inner_page = "inner_index.php";
$header = $rpath."header.php";
$footer = $rpath."footer.html";
include ($rpath."template.php");

?>
