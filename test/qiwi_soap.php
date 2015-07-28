<?
/*Тестируем киви соап*/
require_once("../classes/stdf.php");
require_once("../classes/exchrates.php");
require_once("../classes/memBuff.php");
require_once("../classes/payment_keys.php");
require_once("../classes/users.php");
require_once("../classes/account.php");
require_once("../classes/qiwipay_soap.php");

$qiwipay = new qiwipay_soap();
$result = $qiwipay->getBillList();

var_dump($result);

       

?>