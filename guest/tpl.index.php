<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}
?>
<?php echo $controller->renderOutput; ?>