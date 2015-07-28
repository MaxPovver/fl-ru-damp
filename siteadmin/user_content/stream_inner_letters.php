<?php
/**
 * Модерирование пользовательского контента. Диалог в личной переписке. Шаблон.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
?>
<div id="my_div_all">

<?php
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/user_content.common.php' );
$xajax->printJavascript( '/xajax/' );
?>

<?php if( $sStreamId ) { /*?>
<a href="javascript:void(0);" id="my_close" class="b-button b-button_rectangle_color_green b-button_float_right">
    <span class="b-button__b1">
        <span class="b-button__b2">
            <span class="b-button__txt">Закрыть поток</span>
        </span>
    </span>
</a>
*/ ?>

<div class="b-layout__txt"><a class="b-layout__link" href="/siteadmin/user_content/?site=stream&cid=1&sid=<?=$aStream['stream_id']?>">Личные сообщения, поток <?=$aStream['title_num']?></a> &rarr;</div>
<?php } ?>

<h2 class="b-layout__title">Переписка</h2>

<?php
if ( $oFromUser->login && $oToUser->login ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
    
    $stop_words = new stop_words( true );
    
?>
<script type="text/javascript">
    banned.addContext( 'user_from', 2, '', 'Личное сообщение для <?=$oToUser->uname?> <?=$oToUser->usurname?> [<?=$oToUser->login?>]' );
    banned.addContext( 'user_to', 2, '', 'Личное сообщение для <?=$oFromUser->uname?> <?=$oFromUser->usurname?> [<?=$oFromUser->login?>]' );
</script>
    
<table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
    <tr class="b-layout__tr">
        <td class="b-layout__left b-layout__left_padbot_10"><div class="b-username  b-username_fontsize_11"><a class="b-username__link b-username__link_color_41" href="/users/<?=$oFromUser->login?>" target="_blakn"><?=$oFromUser->uname?> <?=$oFromUser->usurname?></a> <a class="b-username__link b-username__link_color_41" href="/users/<?=$oFromUser->login?>" target="_blakn">[<?=$oFromUser->login?>]</a>&nbsp;с&nbsp;</div></td>
        <td class="b-layout__right b-layout__right_padbot_10"><div class="b-username  b-username_fontsize_11"><a class="b-username__link b-username__link_color_41" href="/users/<?=$oToUser->login?>" target="_blakn"><?=$oToUser->uname?> <?=$oToUser->usurname?></a> <a class="b-username__link b-username__link_color_41" href="/users/<?=$oToUser->login?>" target="_blakn">[<?=$oToUser->login?>]</a></div></td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__left">
            <a class="b-button b-button_margright_20 b-button_mini" href="#" onclick="banned.warnUser(<?=$nFromId?>, 0, '', 'user_from', 0); return false;"><span class="b-button__icon b-button__icon_att"></span></a>
            <a class="b-button b-button_margright_20 b-button_mini" href="#" onclick="banned.userBan(<?=$nFromId?>, 'user_from', 0);"><span class="b-button__icon b-button__icon_krest"></span></a>
        </td>
        <td class="b-layout__right">
            <a class="b-button b-button_margright_20 b-button_mini" href="#" onclick="banned.warnUser(<?=$nToId?>, 0, '', 'user_to', 0); return false;"><span class="b-button__icon b-button__icon_att"></span></a>
            <a class="b-button b-button_margright_20 b-button_mini" href="#" onclick="banned.userBan(<?=$nToId?>, 'user_to', 0);"><span class="b-button__icon b-button__icon_krest"></span></a>
        </td>
    </tr>
</table>

<div id="my_div_contents"></div>

<script type="text/javascript">
window.addEvent('domready', function() {
    user_content.currUid        = <?=$uid?>;
    user_content.streamId       = '<?=$sStreamId?>';
    user_content.contentID      = <?=$sContentId?>;
    user_content.contentPP      = <?=user_content::CONTENTS_PER_PAGE?>;
    user_content.getLettersFid  = <?=$nFromId?>;
    user_content.getLettersTid  = <?=$nToId?>;
    user_content.getLettersMid  = <?=$nMsgId?>;
    user_content.spinner        = new Spinner('my_div_all', {style: {'padding': 0}});
    user_content.status         = 0;
    <?php 
    if( $sStreamId ) { 
    ?>
    user_content.scrollFunction = 'getLetters';
    user_content.getLetters();
    //$('my_close').addEvent('click', function(){user_content.releaseStream();});
    <?php 
    } 
    else {
    ?>
    user_content.scrollFunction = 'getBlockedLetters';
    user_content.getBlockedLetters();
    <?php
    }
    ?>
});
</script>
<?php

}
else {
    
?>
<div class="b-post b-post_padtop_15">
    <div class="b-post__txt">Нет диалога</div>
</div>
<?php
    
}
?>

</div>