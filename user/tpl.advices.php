<?php
if (!$msgs) {
    $msgs = array();
}
//$counters = opinions::GetCounts($user->uid, array('norisk'));
//$cnt = intval(array_sum($counters['norisk']));

$cnt = count($msgs);    
$ac_sum = $_SESSION['ac_sum'];
$ac_sum_rub = $_SESSION['ac_sum_rub'];
?>
<div class="b-layout b-layout_padtop_20 b-fon overlay-cls">
    
    <? /*if ($_user->uid && $user->uid != $_user->uid && is_emp($_user->role) != is_emp($user->role)) { ?>
    <a class="b-button b-button_round_green b-button_float_right b-button_baseline b-button_margright_15 b-button_margtop_-3 b-button_margbot_10 advice-add-btn" onclick="adviceAddForm()" href="javascript:void(0)">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__txt">Новая рекомендация</span>
            </span>
        </span>
    </a>
    <? } */?>
		
		<div class="b-post b-post_padtop_10 b-post_padright_15 b-post_float_right b-post__txt b-post__txt_fontsize_11 b-post__txt_hide" id="manager_feedback"><span class="b-post__qwest"></span> &#160;<a class="b-post__link b-post__link_fontsize_11 b-post__link_color_4e" href="/contacts/?from=opinions">Обратиться к менеджеру за помощью</a></div>
    <h2 class="b-layout__title b-layout__title_pad_0_15_15"><?= $cnt ?> рекомендаци<?= ending($cnt, 'я', 'и', 'й')?> 
    
    <div class="b-filter">
			<div class="b-filter__body"><a class="b-filter__link b-filter__link_ie7_top_3 b-filter__link_dot_0f71c8 b-layout__link_fontsize_13" href="#"><?=$filter_string?></a></div>
			<div class="b-shadow b-shadow_marg_-32 b-filter__toggle b-filter__toggle_hide">
				<div class="b-shadow__right">
					<div class="b-shadow__left">
						<div class="b-shadow__top">
							<div class="b-shadow__bottom">
								<div class="b-shadow__body b-shadow__body_pad_15 b-shadow__body_bg_fff">
									<ul class="b-filter__list">
										<li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($period == 0 ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/<?=$html_for_filter . "&period=0#op_head" ?>'">за всё время</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($period == 0 ?"" : "b-filter__marker_hide")?>"></span></li>
										<li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($period == 1 ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/<?=$html_for_filter . "&period=1#op_head" ?>'">за последний год</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($period == 1 ?"" : "b-filter__marker_hide")?>"></span></li>
										<li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($period == 2 ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/<?=$html_for_filter . "&period=2#op_head" ?>'">за последние полгода</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($period == 2 ?"" : "b-filter__marker_hide")?>"></span></li>
										<li class="b-filter__item b-filter__item_padbot_3 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($period == 3 ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/<?=$html_for_filter . "&period=3#op_head" ?>'">за последний месяц</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($period == 3 ?"" : "b-filter__marker_hide")?>"></span></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="b-shadow__tl"></div>
				<div class="b-shadow__tr"></div>
				<div class="b-shadow__bl"></div>
				<div class="b-shadow__br"></div>
			</div>
		</div>
	</h2>
    
    <? if ($_user->uid) { ?>
    <div class="b-fon b-fon_width_full b-fon_margbot_10 b-fon_clear_both advice-status-sent" style="display: none;">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
            Рекомендация отправлена <?= is_emp($user->role) ? 'работодателю' : 'исполнителю' ?>. <a class="b-fon__link b-fon__link_fontsize_13" onclick="adviceAddForm()" href="javascript:void(0)">Отправить ещё одну</a> 
        </div>
        <span class="b-fon__close"></span>
    </div>
    
    <div class="b-fon b-fon_width_full b-fon_margbot_10 b-fon_clear_both advice-status-declined" style="display: none;">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">
            Вы отказались от рекомендации. <a class="b-fon__link b-fon__link_fontsize_13 b-fon__link_bordbot_dot_0f71c8" href="#">Вернуть рекомендацию</a> 
        </div>
        <span class="b-fon__close"></span>
    </div>
    <div class="b-fon b-fon_width_full b-fon_margbot_10 b-fon_clear_both advice-status-deleted" style="display: none;">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">
            Заявка удалена.  <a class="b-fon__link b-fon__link_fontsize_13 b-fon__link_bordbot_dot_0f71c8" href="#">Восстановить</a> 
        </div>
        <span class="b-fon__close"></span>
    </div>
    
    <? include_once(dirname(__FILE__).'/tpl.advice-add.php') ?>
    <? } ?>
    
    <? for ($i = 0; $i < count($msgs); $i++) {
        $theme = $msgs[$i];
        $next = ($is_end = ($theme != end($msgs)))? $msgs[$i+1] : array() ; 
        $is_next = ($next['sbr_id'] == $theme['sbr_id'] && $theme['is_payed'] == 0);
    ?>
    <? if($theme['sbr_id'] == $oid && $theme['is_payed'] == 0) {?>
    <div class="b-post b-post_pad_10_15_0" id="p_stage_<?=$theme['uniq_id']?>">
        <a name="p_<?=$theme['uniq_id']?>"></a>
    	<div class="b-post__body <?php if (!$is_next) { ?>b-post__body_bordbot_solid_f0  b-post__body_padbot_20<? } ?>">
    		<div class="b-post__time b-post__time_float_right">
    		  <a class="b-post__anchor b-post__anchor_margright_10" onclick="hlAnchor('p',<?= $theme['uniq_id'] ?>);" href="#p_<?= $theme['uniq_id'] ?>" title="Ссылка на эту рекомендацию"></a>
    		  <?= date("d.m.Y в H:i", strtotime($theme['posted_time'])) ?></div>
    		<div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
    		    <div id="form_container_<?= $theme['id'] ?>" class="editFormSbr" style="display:none"></div>
        		<div id="form_container_to_<?= $theme['id'] ?>" class="sbrmsgblock">
        		    <div class="b-post__voice b-post__voice_<?= $theme['sbr_rating'] == -1 ? 'negative' : ($theme['sbr_rating'] == 1 ? 'positive' : 'neutral') ?>"></div>
        			<div class="b-post__txt" id="op_message_<?=$theme['id']?>"><?= reformat($theme['descr'], 30, 0, 0, 1) ?></div>
    			</div>
    			<div class="b-post__foot b-post__foot_padtop_10">
    			Этап «<?= reformat($theme['stage_name'], 40, 0, 1); ?>», <?= ($theme['stage_status'] == 7) ? '<span class="b-post__txt b-post__txt_color_c10601 b-post__txt_fontsize_11">завершенный арбитражем</span>' : 'сданный'; ?> <?= date("d.m.Y в H:i", strtotime($theme['stage_closed'])) ?>
    			</div>
    			<?php if (($theme['fromuser_id'] == $_SESSION['uid'] && (strtotime($theme['posted_time'])+3600*24 > time())) || hasPermissions('users') && $theme['is_payed'] != 1) { ?>
    			<div class="b-post__foot b-post__foot_padtop_10">
                    <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="xajax_EditSBROpForm(<?= $theme['id'] ?>, '<?=$theme['login']?>')">Редактировать отзыв</a>
                    <?php if (hasPermissions('sbr') && $theme['is_payed'] != 1) { ?>
                    &nbsp;&nbsp;<a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="if (confirm('Удалить отзыв?')) {xajax_DeleteFeedback(<?= $theme['stage_id'] ?>, <?= $theme['id'] ?>, true)}">Удалить отзыв</a>
                    <?php }//if?>
                </div>
    			<?php }//if?>
    		</div>
    	</div>
    </div>       
    <? } else {?>
    <div class="b-post b-post_pad_10_15_0 b-post_clear_both" id="p_stage_<?=$theme['uniq_id']?>">
        <a name="p_<?=$theme['uniq_id']?>"></a>
        <div class="b-post__body b-post__body_padbot_20 <?php if ($is_end && !$is_next) { ?>b-post__body_bordbot_solid_f0<? } ?>" >
            <div class="b-post__avatar">
                <a class="b-post__link" href="/users/<?= $theme['login'] ?>/"><?= view_avatar($theme['login'], $theme['photo'], 1, 1, 'b-post__userpic') ?></a>
            </div>
            <div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
								<div class="b-post__time b-post__time_float_right">
										<a class="b-post__anchor b-post__anchor_margright_10" onclick="hlAnchor('p',<?= $theme['uniq_id'] ?>);" href="#p_<?= $theme['uniq_id'] ?>" title="Ссылка на эту рекомендацию"></a>
										<?= date("d.m.Y в H:i", strtotime($theme['posted_time'])) ?>
								</div>
                <div id="form_container_<?= $theme['id'] ?>" class="editFormSbr" style="display:none"></div>
                <div id="form_container_to_<?= $theme['id'] ?>" class="sbrmsgblock">
                    <div class="b-username b-username_padbot_10 b-username_bold"><?= view_user3($theme) ?></div>			
                    <?php if ( $theme['is_banned'] ) { ?><div style="color:#000; margin: 0 0 10px 0;" ><b>Пользователь&nbsp;забанен.</b></div><?php } ?>
                    <div id="cont_<?=$theme['id']?>" class="b-post__voice b-post__voice_<?= $theme['sbr_rating'] == -1 ? 'negative' : ($theme['sbr_rating'] == 1 ? 'positive' : 'neutral') ?>"></div>
                    <div class="b-post__txt" id="op_message_<?=$theme['id']?>"><?= reformat($theme['descr'], 30, 0, 0, 1) ?></div>
                </div>
                <?if($theme['is_payed'] == 1) { ?>
                <div class="b-post__foot b-post__foot_padtop_10"></div>
                <? } else { //if?>
                <div class="b-post__foot b-post__foot_padtop_10 <?= $is_next?"b-post__foot_padbot_5":""?>">    
                    «Безопасная Сделка» <span class="b-post__sbrm" title="«Безопасная Сделка»"></span>&#160; 
                    <? if ($uid == $theme['frl_id'] || $uid == $theme['emp_id']) { ?>
                    по проекту «<a class="b-post__link" href="<?=( sbr_meta::isNewVersionSbr($theme['scheme_type']) ? "/".sbr::NEW_TEMPLATE_SBR."/?id={$theme['sbr_id']}" : "/norisk2/?id={$theme['sbr_id']}" ) ?>"><?= reformat($theme['sbr_name'], 40, 0, 1) ?></a>»<?= !$is_next?",":""?> 
                    <? } elseif ($theme['project_id']) { ?>
                    по проекту «<a class="b-post__link" href="/projects/?pid=<?= $theme['project_id'] ?>"><?= reformat($theme['sbr_name'], 40, 0, 1) ?></a>»<?= !$is_next?",":""?>  
                    <? } else { ?>
                    по проекту «<?= reformat($theme['sbr_name'], 40, 0, 1) ?>»<?= !$is_next?",":""?>  
                    <? } ?>
                    <? if(!$is_next) { ?>
                        этап «<?= reformat($theme['stage_name'], 40, 0, 1); ?>», <?= ($theme['stage_status'] == 7) ? '<span class="b-post__txt b-post__txt_color_c10601 b-post__txt_fontsize_11">завершенный арбитражем</span>' : 'сданный'; ?> <?= date("d.m.Y в H:i", strtotime($theme['stage_closed'])) ?>
                    <? }//if?>    
                </div>  
                <div class="b-post__foot <?= $is_next?"b-post__foot_padbot_5":""?>"><?= professions::GetProfNameWP($theme['sub_category'], ' > ', 'Все разделы', 'b-post__link b-post__link_color_4e') ?></div>  
                <? } //else?>
                <? if($is_next) { ?>
                <div class="b-post__foot">Этап «<?= reformat($theme['stage_name'], 40, 0, 1); ?>», <?= ($theme['stage_status'] == 7) ? '<span class="b-post__txt b-post__txt_color_c10601 b-post__txt_fontsize_11">завершенный арбитражем</span>' : 'сданный'; ?><?= date("d.m.Y в H:i", strtotime($theme['stage_closed'])) ?></div>
                <? }//?>
                <?php if ( ( ( $theme['fromuser_id'] == $_SESSION['uid'] && (strtotime($theme['posted_time'])+3600*24 > time()) ) || hasPermissions('users') ) && $theme['is_payed'] != 1) { ?>
    			<div class="b-post__foot b-post__foot_padtop_10">
                    <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="xajax_EditSBROpForm(<?= $theme['id'] ?>, '<?=$theme['login']?>')">Редактировать отзыв</a>
                    <?php if (hasPermissions('sbr') && $theme['is_payed'] != 1) { ?>
                    &nbsp;&nbsp;<a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="if (confirm('Удалить отзыв?')) {xajax_DeleteFeedback(<?= $theme['stage_id'] ?>, <?= $theme['id'] ?>, true)}">Удалить отзыв</a>
                    <?php }//if?>
                </div>
    			<?php }//if?>
            </div>
        </div>
    </div>
    <? } $oid = $theme['sbr_id']; $_next = $is_next; } ?>
    
</div>		