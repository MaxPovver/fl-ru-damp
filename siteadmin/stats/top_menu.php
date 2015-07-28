<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<?php if($mIndex) {?> По дням <?php } else { //if?><a href="/siteadmin/stats/">По дням</a><?php }//else?> | 
<?php if($mFull) {?> Общая <?php } else { //if?><a href="?t=g">Общая</a><?php }//else?> |
<?php if($mPro) {?> PRO <?php } else { //if?><a href="?t=p">PRO</a><?php }//else?> |
<?php if($mCountry) {?> Страны, города и возраст <?php } else { //if?><a href="?t=c">Страны, города и возраст</a><?php }//else?> |
<?php if($mUser) {?>Активные пользователи<?php } else { //if?><a href="?t=u">Активные пользователи</a><?php }//else?> |
<?php if($mVerify) {?>Верификация<?php } else { //if?><a href="?t=v">Верификация</a><?php }//else?>