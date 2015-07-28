<?	
$footer_registration = true;
$registration_folder = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/activate_code.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");

session_start();
$forbidden = $uid = get_uid(false);
if ($forbidden) {
    if ($_SESSION["role"][0] == 1) {
        $forbidden = 0;
    }
}
if($forbidden) {
    include $_SERVER['DOCUMENT_ROOT']."/403.php";
    exit;
}
$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.employer.php";

include ("../template2.php");
?>