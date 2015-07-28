<?php
/**
 * Модерирование пользовательского контента. Потоки. Шаблон.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
?>

<div id="my_div_all">
    
<?php
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/user_content.common.php' );
$xajax->printJavascript( '/xajax/' );

if ( $checkStream ) {
    $sContentName = '';
    
    foreach ($user_content->contents as $aOne ) {
        if ( $aOne['id'] == $sContentId ) {
            $sContentName = $aOne['name'];
        }
    }
?>
    
<?php /*
<a href="javascript:void(0);" id="my_close" class="b-button b-button_rectangle_color_green b-button_float_right">
    <span class="b-button__b1">
        <span class="b-button__b2">
            <span class="b-button__txt">Закрыть поток</span>
        </span>
    </span>
</a>

<h2 class="b-layout__title b-layout__title_padbot_30"><?=$sContentName?>, поток <span id="span_num"><?=$aStream['title_num']?></span></h2>

<div class="b-menu b-menu_rubric">
    <ul class="b-menu__list b-menu__list_margleft_0">
        <li class="b-menu__item <?=( $nStatus == 0 ? 'b-menu__item_active' : '' )?> b-menu__item_margright_15 b-menu__item_fontsize_11">
            <?php if ( $nStatus == 0 ) { ?><span class="b-menu__b1"><span class="b-menu__b2 ">непроверенные</span></span><?php }
            else { ?> <a class="b-menu__link b-menu__link_color_41" href="/siteadmin/user_content/?site=stream&cid=<?=$sContentId?>&sid=<?=$sStreamId?>&status=0">непроверенные</a><?php } ?>
        </li>
        <?php if ( !in_array($sContentId, user_content::$aNoApproved) ) { ?>
        <li class="b-menu__item <?=( $nStatus == 1 ? 'b-menu__item_active' : '' )?> b-menu__item_margright_15 b-menu__item_fontsize_11">
            <?php if ( $nStatus == 1 ) { ?><span class="b-menu__b1"><span class="b-menu__b2 ">проверенные</span></span><?php }
            else { ?><a class="b-menu__link b-menu__link_color_41" href="/siteadmin/user_content/?site=stream&cid=<?=$sContentId?>&sid=<?=$sStreamId?>&status=1">проверенные</a><?php } ?>
        </li>
        <?php } ?>
        <?php if ( !in_array($sContentId, user_content::$aNoRejected) ) { ?>
        <li class="b-menu__item <?=( $nStatus == 2 ? 'b-menu__item_active' : '' )?> b-menu__item_margright_15 b-menu__item_fontsize_11">
            <?php if ( $nStatus == 2 ) { ?><span class="b-menu__b1"><span class="b-menu__b2 ">заблокированные</span></span><?php }
            else { ?><a class="b-menu__link b-menu__link_color_41" href="/siteadmin/user_content/?site=stream&cid=<?=$sContentId?>&sid=<?=$sStreamId?>&status=2">заблокированные</a><?php } ?>
        </li>
        <?php } ?>
    </ul>
</div>
 */ ?>

<?php /*if ( $nStatus == 0 ) { ?>
<div class="b-post b-post_padtop_15">
    <input type="checkbox" onclick="user_content.mass_check(this)" id="mass_check"><label for="mass_check">&nbsp;Выбрать все</label>
    <a href="javascript:void(0);" onclick="user_content.mass_submit()" class="b-button b-button_rectangle_color_green">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__txt">Одобрить выделенные</span>
            </span>
        </span>
    </a>
</div>
<?php }*/ ?>

<div id="my_div_contents"></div>

<?php /*if ( $nStatus == 0 ) { ?>
<div class="b-post b-post_padtop_15 b-post__txt_padbot_15">
    <input type="checkbox" onclick="user_content.mass_check(this)" id="mass_check2"><label for="mass_check2">&nbsp;Выбрать все</label>
    <a href="javascript:void(0);" onclick="user_content.mass_submit()" class="b-button b-button_rectangle_color_green">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__txt">Одобрить выделенные</span>
            </span>
        </span>
    </a>
</div>
<?php }*/ ?>

<script type="text/javascript">
window.addEvent('domready', function() {
    user_content.currUid    = <?=$uid?>;
    user_content.streamId   = '<?=$sStreamId?>';
    user_content.contentID  = <?=$sContentId?>;
    user_content.contentPP  = <?=user_content::CONTENTS_PER_PAGE?>;
    user_content.spinner    = new Spinner('my_div_all', {style: {'padding': 0}});
    user_content.status     = <?=$nStatus?>;
    user_content.lastID     = '2147483647'; // pg max int as string!
    user_content.getContents();
});
</script>
<?php

}
else {
/*
?>
<a href="/siteadmin/user_content/?site=choose" class="b-button b-button_rectangle_color_green b-button_float_right">
    <span class="b-button__b1">
        <span class="b-button__b2">
            <span class="b-button__txt">Повторить попытку</span>
        </span>
    </span>
</a>
*/ 
?>
<div class="b-post b-post_pad_10_15_15"><span style="color: #cc4642; font-weight: bold;">Захват потока не удался</span></div>
<?php

}

?>

</div>
