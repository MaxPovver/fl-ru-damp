<?php
if(!defined('IN_STDF')) 
{
	header("HTTP/1.0 404 Not Found");
	exit;
}
?>
<div class="admin">
    <h1>Администрирование</h1>
    <div class="lm-col">
        <?php include(ABS_PATH . "/siteadmin/leftmenu.php") ?>
    </div>
    <div class="r-col">
        <div class="ban-razban">
            <?php echo $content ?>
        </div>
    </div>
</div>