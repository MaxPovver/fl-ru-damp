<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");

if (!get_uid(0)) {
    header_location_exit('/404.php');
}

$bill = new billing(get_uid(0));
$bill->setPage('success');

$js_file = array('billing.js');
$content = "content.php";
$header = "../../header.new.php";
$footer = "../../footer.new.html";
include ("../../template3.php");
?>