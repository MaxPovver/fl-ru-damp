<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once(ABS_PATH . '/classes/DocGen/DocGenReserves.php');

$order_id = __paramInit('int', 'order_id', null, 759);
$user_id = __paramInit('int', 'uid', null, 239549);

$orderModel = new TServiceOrderModel();
$order = $orderModel->getCard($order_id, $user_id);

$doc = new DocGenReserves($order);
echo $doc->generateSpecification();