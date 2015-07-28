<?
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/b_combo_box_request_handler.php";
$handler = new CBComboRequestHandler();
echo $handler->processRequest();
return;