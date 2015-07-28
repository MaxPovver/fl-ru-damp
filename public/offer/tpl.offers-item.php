<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
$team = new teams();
$stop_words = new stop_words(hasPermissions('projects') );
stat_collector::setStamp();
?>
<?php 
foreach($f_offers as $i=>$offer) { 
    unset($user_ago);
    if($offer['birthday'] !== NULL && $offer['birthday'] > "1910-01-01") {
        $user_ago = ElapsedYears(strtotime($offer['birthday']));
    }
    $info_for_reg = @unserialize($offer['info_for_reg']);
    $offer['sf'] = abs($offer['se']) + abs($offer['sg']) + abs($offer['sl']);
    $offer['ef'] = abs($offer['e_plus']) + abs($offer['e_null']) + abs($offer['e_minus']);
    if(get_uid(false)) $offer['is_fav'] = $team->teamsIsInFavorites($_SESSION['uid'], $offer['uid']);
    
    $sTitle = htmlspecialchars( $offer['title'] );
    $sTitle = $offer['moderator_status'] === '0' && $offer['is_pro'] != 't' ? $stop_words->replace($sTitle) : $sTitle;
    $sTitle = reformat( $sTitle, 35, 0, 1 );
    $sDescr = htmlspecialchars( $offer['descr'] );
    $sDescr = $offer['moderator_status'] === '0' && $offer['is_pro'] != 't' ? $stop_words->replace($sDescr) : $sDescr;
    $sDescr = reformat( $sDescr, 50 );
?><a name="o_<?=$offer['id']?>">&nbsp;</a>
<div class="b-freelancer b-freelancer_bordbot_f0 b-freelancer_padbot_30 b-freelancer_padtop_20"> 
    
	<span class="b-freelancer__date b-freelancer__date_float_right b-freelancer__date_padtop_10 b-freelancer__date_padleft_10"><?= date("d.m.Y в H:i", strtotime($offer['post_date']))?></span>
    <h2 class="b-freelancer__h2 b-freelancer__h2_padbot_20"><? if($offer['is_closed'] == 't') {?><img src="/images/ico_closed.gif" align="absmiddle"> <?}//if?><a class="b-freelancer__link" href="/users/<?=$offer['login']?>/?f=<?=  stat_collector::REFID_FRL_OFFERS?>&stamp=<?= $_SESSION['stamp'] ?>"><?= $sTitle?></a></h2>
	<p class="b-freelancer__p"><?= $sDescr?></p>
	
	<div class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10" id="freelance-offer-reason-<?=$offer['id']?>" style="<?=$offer['is_blocked'] == 'f' ?"display:none":""?>">
			<b class="b-fon__b1"></b>
			<b class="b-fon__b2"></b>
			<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
				<span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20"><span class="b-fon__txt_bold">Предложение заблокировано</span>. <span id="freelance-offer-reason-txt-<?=$offer['id']?>"><?= reformat( $offer['reason'], 24, 0, 0, 1, 24 ); ?></span> <a class="b-fon__link" href="https://feedback.fl.ru/">Служба поддержки</a> </div>
			</div>
			<b class="b-fon__b2"></b>
			<b class="b-fon__b1"></b>
	</div>
	
	<div class="b-layout b-layout_padtop_20">
		<table class="b-layout_table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
			<tr class="b-layout__tr">
				<td class="b-layout__left">
          <div class="b-username b-username_bold b-username_padbot_10"><?= view_user3($offer, "?f=".stat_collector::REFID_FRL_OFFERS."&stamp={$_SESSION['stamp']}");?></div>
					<div class="b-freelancer__txt b-freelancer__txt_fontsize_11 b-freelancer__txt_padbot_5">
						<div class="b-freelancer__txt b-freelancer__txt_valign_top b-freelancer__txt_inline-block b-freelancer__txt_width_120 b-freelancer__txt_bordbot_dot_e5"> <span class="b-freelancer__txt b-freelancer__txt_top_3 b-freelancer__txt_bg_fff">Специализация</span> </div>
						<div class="b-freelancer__txt b-freelancer__txt_inline-block b-freelancer__txt_width_320 b-freelancer__txt_top_3">
						<? projects::getSpecsStr($offer['id'],' / ', ', ');?>
						<?= $offer['cat_name']?><?= $offer['profname']!="Нет специализации"?" &rarr; <a class='b-freelancer__link' href='/freelancers/{$offer['link']}/'>{$offer['profname']}</a>":""?></div>
					</div>
					<div class="b-freelancer__txt b-freelancer__txt_fontsize_11 b-freelancer__txt_padbot_5">
						<div class="b-freelancer__txt b-freelancer__txt_valign_top b-freelancer__txt_inline-block b-freelancer__txt_width_120 b-freelancer__txt_bordbot_dot_e5"> <span class="b-freelancer__txt b-freelancer__txt_top_3 b-freelancer__txt_bg_fff">На сайте</span> </div>
						<div class="b-freelancer__txt b-freelancer__txt_inline-block b-freelancer__txt_width_320 b-freelancer__txt_top_3"><?= ElapsedMnths(strtotime($offer['reg_date']))?></div>
					</div>
					<?php if ($offer['country'] && !($info_for_reg['country'] && !get_uid(false))) {  ?>
					<div class="b-freelancer__txt b-freelancer__txt_fontsize_11 b-freelancer__txt_padbot_5">
						<div class="b-freelancer__txt b-freelancer__txt_valign_top b-freelancer__txt_inline-block b-freelancer__txt_width_120 b-freelancer__txt_bordbot_dot_e5"> <span class="b-freelancer__txt b-freelancer__txt_top_3 b-freelancer__txt_bg_fff">Местонахождение</span> </div>
						<div class="b-freelancer__txt b-freelancer__txt_inline-block b-freelancer__txt_width_320 b-freelancer__txt_top_3"><?= country::GetCountryName($offer['country']);?><?if ($offer['city'] && !($info_for_reg['city'] && !get_uid(false))) { print(", ".city::GetCityName($offer['city'])); }?></div>
					</div>
					<?php }//if?>
					<?php if($user_ago && !($info_for_reg['birthday'] && !get_uid(false))) {?>
					<div class="b-freelancer__txt b-freelancer__txt_fontsize_11 b-freelancer__txt_padbot_5">
						<div class="b-freelancer__txt b-freelancer__txt_valign_top b-freelancer__txt_inline-block b-freelancer__txt_width_120 b-freelancer__txt_bordbot_dot_e5"> <span class="b-freelancer__txt b-freelancer__txt_top_3 b-freelancer__txt_bg_fff">Возраст</span> </div>
						<div class="b-freelancer__txt b-freelancer__txt_inline-block b-freelancer__txt_width_320 b-freelancer__txt_top_3"><?= view_exp($user_ago)?></div>
					</div>
					<?php }//if?>
			    </td>
				<td class="b-layout__right b-layout__right_width_240"><div class="b-freelancer__txt b-freelancer__txt_fontsize_11 b-freelancer__txt_padbot_10">
					<!--
						<div class="b-freelancer__txt b-freelancer__txt_float_right">Отношение <span class="b-freelancer__txt ">-96</span></div>
					-->
						<div class="b-freelancer__txt">Рейтинг <span class="b-freelancer__txt <?=($offer['rating']<0?"b-freelancer__txt_color_c10600":"b-freelancer__txt_color_6db335")?>"><?= ($offer['rating']<0?"&minus;":"") . abs(rating::round($offer['rating']))?></span></div>
					</div>
					<div class="b-freelancer__txt b-freelancer__txt_fontsize_11 b-layout">
						<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
							<tr class="b-layout__tr">
								<td class="b-layout__left b-layout__left_padbot_5">
									<a class="b-freelancer__link" href="/users/<?=$offer['login']?>/opinions/?from=norisk#op_head" target="_blank"><?=$offer['sf']?> <?=ending($offer['sf'], "рекомендация", "рекомендации", "рекомендаций")?></a>&#160; 
								</td>
								<td class="b-layout__one b-layout__one_width_30 b-layout__one_right">
									<a class="b-freelancer__link b-freelancer__link_color_6db335 b-freelancer__link_decoration_no" href="/users/<?=$offer['login']?>/opinions/?from=norisk&sort=1#op_head" target="_blank">+</a><a class="b-freelancer__link b-freelancer__link_color_6db335" href="/users/<?=$offer['login']?>/opinions/?from=norisk&sort=1#op_head" target="_blank"><?=(int)$offer['sg']?></a>&#160; 
								</td>
								<td class="b-layout__one b-layout__one_width_30 b-layout__one_right">
									<a class="b-freelancer__link b-freelancer__link_color_414141" href="/users/<?=$offer['login']?>/opinions/?from=norisk&sort=2#op_head" target="_blank"><?=(int)$offer['se']?></a>&#160; 
								</td>
								<td class="b-layout__one b-layout__one_width_30 b-layout__one_right">
									<a class="b-freelancer__link b-freelancer__link_color_c10600 b-freelancer__link_decoration_no" href="/users/<?=$offer['login']?>/opinions/?from=norisk&sort=3#op_head" target="_blank">&minus;</a><a class="b-freelancer__link b-freelancer__link_color_c10600" href="/users/<?=$offer['login']?>/opinions/?from=norisk&sort=3#op_head" target="_blank"><?=abs((int)$offer['sl'])?></a> 
								</td>
							</tr>
							<tr class="b-layout__tr">
								<td class="b-layout__left b-layout__left_padbot_5">
								<a class="b-freelancer__link" href="/users/<?=$offer['login']?>/opinions/?from=users#op_head" target="_blank"><?=$offer['ef']?> <?=ending($offer['ef'], "мнение", "мнения", "мнений")?> <?=ending($offer['ef'], "пользователя", "пользователей", "пользователей")?></a>&#160; 
								</td>
								<td class="b-layout__one b-layout__one_width_30 b-layout__one_right">
								<a class="b-freelancer__link b-freelancer__link_color_6db335 b-freelancer__link_decoration_no" href="/users/<?=$offer['login']?>/opinions/?from=users&sort=1#op_head" target="_blank">+</a><a class="b-freelancer__link b-freelancer__link_color_6db335" href="/users/<?=$offer['login']?>/opinions/?from=users&sort=1#op_head" target="_blank"><?=(int)$offer['e_plus']?></a>&#160; 
								</td>
								<td class="b-layout__one b-layout__one_width_30 b-layout__one_right">
								<a class="b-freelancer__link b-freelancer__link_color_414141" href="/users/<?=$offer['login']?>/opinions/?from=users&sort=2#op_head" target="_blank"><?=(int)$offer['e_null']?></a>&#160; 
								</td>
								<td class="b-layout__one b-layout__one_width_30 b-layout__one_right">
								<a class="b-freelancer__link b-freelancer__link_color_c10600 b-freelancer__link_decoration_no" href="/users/<?=$offer['login']?>/opinions/?from=users&sort=3#op_head" target="_blank">&minus;</a><a class="b-freelancer__link b-freelancer__link_color_c10600" href="/users/<?=$offer['login']?>/opinions/?from=users&sort=3#op_head" target="_blank"><?=abs((int)$offer['e_minus'])?></a> 
								</td>
							</tr>
						</table>
						<div class="b-freelancer__txt b-freelancer__txt_padbot_5">
							<div class="b-freelancer__sbr"></div>
							&#160;<?=(int)$offer['success_cnt']?> <?= ending((int)$offer['success_cnt'], "Безопасная Сделка", "Безопасные Сделки", "Безопасных Сделок")?></div>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div class="b-buttons b-buttons_padtop_10">
	<?php if($_SESSION['login'] != $offer['login'] && get_uid(false) && !$hidden_block_button) {?>
    <a class="b-button b-button_rectangle_color_transparent" href="/contacts/?from=<?=$offer['login']?>" target="_blank">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__icon b-button__icon_mess"></span><span class="b-button__txt b-button__txt_float_left">Обсудить проект</span>
            </span>
        </span>
    </a>
    <a class="b-button b-button_rectangle_color_transparent fav_action_<?=$offer['login']?>" onclick="xajax_<?=$offer['is_fav']?"DelInTeam":"AddInTeam"?>(<?=$_SESSION['uid']?>, '<?=$offer['login']?>', null, true); return false" href="#">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__icon b-button__icon_star"></span>
                <span class="b-button__txt b-button__txt_float_left fav_title_<?=$offer['login']?>"><?=$offer['is_fav']?"Убрать из избранных":"В избранное"?></span>
            </span>
        </span>
    </a>
    
    <?php if ( !hasPermissions('projects') ) { // админу не нужно жаловаться, он должен банить! ?>
        <? if ( !$frl_offers->ComplainExists($offer['id'], $_SESSION['uid']) ) { ?>
        <a onclick="complainPopup(<?=$offer['id']?>);" id="offer_complain_<?=$offer['id']?>" href="javascript:void(0);" class="b-buttons__link b-buttons__link_dot_c10601 b-buttons__link_margleft_10">Пожаловаться</a>    
        <?php } else { ?>
        <a href="javascript:void(0)" class="b-buttons__link b-buttons__link_color_c10601 b-buttons__link_margleft_10">Ваша жалоба на рассмотрении</a>
        <?php } ?>
    <?php } ?>
    
    <?php }//if?>
    <?php if(($_SESSION['uid'] == $offer['uid'] || hasPermissions('projects')) && !$hidden_block_button) { ?>
		<a class="b-buttons__link b-buttons__link_color_c10601 b-buttons__link_margleft_10" href="/public/offer/?action=edit&fid=<?=$offer['id']?><?= $page>1?"&page={$page}":""?>">Редактировать</a>    
		<?php if(hasPermissions('projects') && $_SESSION['uid'] != $offer['uid']) { $page_uri = $page>1?"&page={$page}":"";?>
		<script type="text/javascript">
        banned.addContext( 'freelance-offer-block-<?=$offer['id']?>', 3, '<?=$GLOBALS['host']?>/kind=8#offer<?=$offer['id']?>', "<?=htmlspecialchars($offer['title'])?>" );
        </script>
		<span id="freelance-offer-button-<?=$offer['id']?>"><a class="b-buttons__link b-buttons__link_dot_c10601 b-buttons__link_margleft_10" href="javascript:void(0);" onclick="banned.<?=($offer['is_blocked']=='t'? 'unblockedFreelanceOffer': 'blockedFreelanceOffer')?>(<?=$offer['id']?>)"><?= $offer['is_blocked']=='f'?"Заблокировать":"Разблокировать"; ?></a>  </span> 
		<?php if ( $offer['warn'] < 3 && !$offer['is_banned'] && !$offer['ban_where']) { ?>
		<span class='warnlink-<?= $offer['uid']?>'><a class="b-buttons__link b-buttons__link_dot_c10601 b-buttons__link_margleft_10" href="#" onclick='banned.warnUserNew(<?=$offer['uid']?>, 0, "frl_offers", "freelance-offer-block-<?=$offer['id']?>", 0); return false;'>Сделать предупреждение</a>
		<div class="b-buttons__txt">— <span class='warncount-<?= $offer['uid']?>'><?= (int)$offer['warn']?></span></div></span>
		<?php } else { $sBanTitle = (!$offer['is_banned'] && !$offer['ban_where']) ? 'Забанить!' : 'Разбанить';?>
		<span class='warnlink-<?= $offer['uid']?>'><a class="b-buttons__link b-buttons__link_dot_c10601 b-buttons__link_margleft_10" href="#" onclick='banned.userBan(<?=$offer['uid']?>, "freelance-offer-block-<?=$offer['id']?>", 0); return false;'><?= $sBanTitle?></a></span>
		<?php }//?>
		
		
		<div id="freelance-offer-block-<?= $offer['id'] ?>">&nbsp;</div>
		<?php } elseif($_SESSION['uid'] == $offer['uid']) {?>
		<a class="b-buttons__link b-buttons__link_color_c10601 b-buttons__link_margleft_10" onclick="return warning(2)" href="/public/offer/?action=close&fid=<?=$offer['id']?>">Снять с публикации</a>
		<?php }//elseif?>
    <?php }//if?>
	</div>
</div>
<?php } //foreach?>
    <? print new_paginator($page, $pages, 4, "%s?page=%d%s")?>
<?php
if ( hasPermissions('projects') ) {
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.new.php' );
}
?>