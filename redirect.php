<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");


if ($_GET["login"]) {

    $login=preg_replace("|^(.+)\.free\-lance\.ru$|Ui","$1",trim($_GET["login"]));
    $login=preg_replace("|^www\.|i","",$login);
    $usr=new users();
    $usr->GetUser($login);

    if ($usr->is_pro=="t") {
        header ("Location: /users/".$usr->login);
        exit;
    }
}
header ("Location: /404.php");
exit;
?>