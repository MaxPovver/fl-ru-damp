<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<div class="admin">
    <h1>Администрирование</h1>
    <div class="lm-col">
        <?php include( $rpath."/siteadmin/leftmenu.php" ) ?>
    </div>
    <div class="r-col">
        <div class="ban-razban">
            <?php include( $inner_page ) ?>
        </div>
    </div>
</div>