<?php
/**
 * Модерирование пользовательского контента. Потоки. Шаблон.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/user_content.common.php' );
$xajax->printJavascript( '/xajax/' );
?>

<h2 class="b-layout__title b-layout__title_padbot_30">Потоки на текущее время</h2>

<?php 

if ( $aStreams ) {
    foreach ( $aStreams as $sId => $aOne ) {
    ?>
<div class="b-layout__h3"><?=$aOne['name']?> <span id="queueCounts_<?=$sId?>">&nbsp;</span></div>

<div class="b-buttons_padbot_20 my_contents" id="contents_<?=$sId?>">
    <div class="b-post__txt">
            <img class="b-post__pic b-post__pic_margright_10 b-post__pic_float_left" src="/images/loading-white.gif" alt="">
            Загрузка
    </div>
</div>
    <?php
    }
    
    
    ?>
<script type="text/javascript">
window.addEvent('domready', function() {
    user_content.currUid = <?=$uid?>;
    user_content.updateStreamsTimeout = <?=user_content::MODER_CHOOSE_REFRESH?>;
    user_content.updateStreams();
});
</script>
    <?php
    
}
else {
    
?>
Нет потоков
<?php

}

?>