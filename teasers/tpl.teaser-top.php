<?
$prjID = __paramInit('int', 'prj_id', null, null);
$link = $prjID ? '/public/?step=1&public=' . $prjID : '/service/top/';
?>
<td class="b-layout__td b-layout__td_width_70 b-layout__td_center b-layout__td_width_null_ipad"><span class="b-page__desktop"><img class="b-layout__pic" src="/images/promo-icons/small/5.png" alt=""  /></span></td>
<td class="b-layout__td"><h3 class="b-layout__h3 b-layout__h3_padbot_5"><a class="b-layout__link b-layout__link_bold" href="/service/top/">«акрепление проекта</a></h3>
<div class="b-layout__txt">„тобы проект был замечен как можно большим количеством пользователей, его можно закрепить. «акрепленный проект находитс€ наверху ленты проектов на главной странице.</div>
<div class="b-buttons b-buttons_padtop_10 b-page__iphone"><a href="<?= $link ?>" class="b-button b-button_flat b-button_flat_green b-button_height_auto">«акрепить этот проект</a></div>
</td>
<td class="b-layout__td b-layout__td_width_270 b-layout__td_center b-layout__td_valign_mid  b-layout__td_width_null_iphone b-layout__td_pad_10 b-layout__td_pad_null_iphone"><a href="<?= $link ?>" class="b-button b-button_flat b-button_flat_green b-button_height_auto b-page__desktop b-page__ipad">«акрепить этот проект</a></td>