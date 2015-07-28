<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");

if (!get_uid(0)) {
    header_location_exit('/404.php');
}

$bill = new billing(get_uid(0));
$bill->setPage('fail');

$error = $_SESSION['errorPs'];

if(empty($error)) {
    header("Location: /404.php");
    exit;
}

$js_file = array('billing.js');
$content = "content.php";
$header = "../../header.new.php";
$footer = "../../footer.new.html";
include ("../../template3.php");
?>