<?php
if(!defined('IN_STDF')) 
{
	header("HTTP/1.0 404 Not Found");
	exit;
}
?>
<h2 class="b-layout__title b-layout__title_fs18">
    Управление заказами с оплатой через Безопасную сделку
</h2>
<div class="b-layout b-layout__page">
    
    <?php echo $this->renderClip('navigation'); ?>
    
    <div class="norisk-admin c">
        <div class="norisk-in">
            <?php echo $content ?>
        </div>
    </div>

</div>