<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
$uid = get_uid(false);

if(!$uid) header("Location: /404.php");
$rpath = "./";
if (!$fpath) $fpath = "";

$alert = $_GET['alert'];


switch($alert) {
    case "blog":
        $id         = intval($_GET['id']);
        $u_token_key = __paramInit('string', null, 'u_token_key');
        $back_gr = __paramInit('string', 'back_gr', 'back_gr');
        $back = $back_gr !== null ? "&back_gr=".$back_gr : "";
        $alert      = "blog";
        $text_descr = "Вы действительно хотите удалить сообщение?";
        $form_uri   = "/blogs/viewgroup.php?id={$id}{$back}&action=delete&ord=new&u_token_key={$u_token_key}";
        $back_uri   = $_SESSION['back_uri'];
        break;
    case "comment":
        $id         = intval($_GET['id']);
        $tr         = intval($_GET['tr']);
        $u_token_key = __paramInit('string', null, 'u_token_key');
        $alert      = "comment";
        $text_descr = "Вы действительно хотите удалить комментарий?";
        $form_uri   = "/blogs/view.php?tr={$tr}&ord=new&id={$id}&action=delete&ord=new&u_token_key={$u_token_key}";
        $back_uri   = $_SESSION['back_uri'];
        break;  
    default:
        header("Location: /404.php");      
}

if(isset($_POST['ok'])) {
    header("Location: $form_uri");
    die();
} elseif(isset($_POST['cancel'])) {
    header("Location: $back_uri");
    die(); 
}

$header = ABS_PATH."/header.php";
$footer = ABS_PATH."/footer.html";
$content = ABS_PATH."/alert_inner.php";
include("template2.php");
?>