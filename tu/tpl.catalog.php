<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}
?>
<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/tservices.common.php");
    $xajax->printJavascript('/xajax/');
?>
<?php echo $controller->renderOutput; ?>