<?
require_once("classes/config.php");
require_once("classes/payed.php");
require_once("classes/pay_place.php");
require_once("classes/firstpage.php");
require_once("classes/commune.php");
require_once("classes/log.php");

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/qiwipay.php");
    $qiwipay = new qiwipay();
    $qiwipay->checkBillsStatus($error);

?>