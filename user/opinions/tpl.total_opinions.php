<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderFeedbackModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_feedback.php');


if (!$msgs) {
    $msgs = array();
}
$cnt = count($msgs);    
$ac_sum = $_SESSION['ac_sum'];
$ac_sum_rub = $_SESSION['ac_sum_rub'];

$aUser = get_object_vars($user);
?>


		
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
    
    <? } ?>
    
    
<?php 
    $is_user_admin = hasPermissions('users');
    $is_tservices_adm = hasPermissions('tservices');
    $is_emp = is_emp($user->role);
    
    for ($i = 0; $i < count($msgs); $i++) {
        $theme = $msgs[$i];
        $next = ($is_end = ($theme != end($msgs)))? $msgs[$i+1] : array() ; 
        $is_next = ($next['sbr_id'] == $theme['sbr_id'] && $theme['is_payed'] == 0);
        
        $uniq_id_sufix = ($theme['opinion_type'] > 1)?"-{$theme['opinion_type']}":'';
        
        $is_hidden = $theme['opinion_type'] == 3 && $theme['hidden'] == "t";

      if(false && $theme['sbr_id'] == $oid && $theme['is_payed'] == 0) {
          
      //Получается в эту часть вообще никогда не заходим
      //Зачем так делать? Зечем она нужна?
?>
    <div class="b-post " id="p_stage_<?=$theme['uniq_id']?>">
        <a name="p_<?=$theme['uniq_id']?>"></a>
        <div class="b-post__body b-post__body_pad_10_15_20">
            <div class="b-post__avatar">
                <a class="b-post__link" href="/users/<?= $theme['login'] ?>/"><?= view_avatar($theme['login'], $theme['photo'], 1, 1, 'b-post__userpic') ?></a>
            </div>
            <div class="b-post__content b-post__content_margleft_60">
                <div class="b-post__time b-post__time_float_right">
                    <a class="b-post__anchor b-post__anchor_margright_10" onclick="hlAnchor('p',<?= $theme['uniq_id'] ?>);" href="#p_<?= $theme['uniq_id'] ?>" title="Ссылка на этот отзыв"></a>
                    <?= date("d.m.Y в H:i", strtotime($theme['posted_time'])) ?>
                </div>
                <div id="form_container_<?= $theme['id'] ?>" class="editFormSbr" style="display:none"></div>
                <div id="form_container_to_<?= $theme['id'] ?>" class="sbrmsgblock">
                    <div class="b-username b-username_padbot_5 b-username_bold"><?= view_user3($theme) ?></div>			
                    <?php if ( $theme['is_banned'] ) { ?><div style="color:#000; margin: 0 0 10px 0;" ><b>Пользователь&nbsp;забанен.</b></div><?php } ?>
                    <div class="b-fon b-fon_fontsize_11 b-fon_inline-block">
                        <div class="b-fon__body  b-fon__body_padleft_20 b-fon__body_bg_fff9bf b-fon__body_fontsize_11">
                            <span id="cont_<?= $theme['id'] ?>" class="b-button b-button_poll_<?= $theme['sbr_rating'] == -1 ? 'minus' : ($theme['sbr_rating'] == 1 ? 'plus' : 'multi') ?> b-button_active b-button_marg_-2_2_0_-15"></span> 
                            <?if($theme['is_payed'] == 1) { ?>
                                подтвержденная рекомендация по итогам сотрудничества
                            <? } else { ?>
                                по итогам сделки <span class="b-icon b-icon_top_3 b-icon_sbr_shield"></span>&nbsp; 
                                <? if ($uid == $theme['frl_id'] || $uid == $theme['emp_id']) { // отзывы смотрит участнк сделк ?>
                                    «<a class="b-layout__link" href="<?=( sbr_meta::isNewVersionSbr($theme['scheme_type']) ? "/".sbr::NEW_TEMPLATE_SBR."/?id={$theme['sbr_id']}" : "/norisk2/?id={$theme['sbr_id']}" ) ?>"><?= reformat($theme['sbr_name'], 40, 0, 1) ?></a>» &nbsp;
                                <? } elseif ($theme['project_id']) { ?>
                                    «<a class="b-post__link" href="/projects/<?= $theme['project_id'] ?>"><?= reformat($theme['sbr_name'], 40, 0, 1) ?></a>»<?= !$is_next?",":""?>
                                <? } else { ?>
                                     «<?= reformat($theme['sbr_name'], 40, 0, 1) ?>»<?= !$is_next?",":""?> 
                                <? } ?>
                                <? if(!$is_next) { ?>
                                    этап «<?= reformat($theme['stage_name'], 40, 0, 1); ?>», <?= ($theme['stage_status'] == 7) ? '<span class="b-post__txt b-post__txt_color_c10601 b-post__txt_fontsize_11">завершенный арбитражем</span>' : 'сданный'; ?> <?= date("d.m.Y в H:i", strtotime($theme['stage_closed'])) ?>
                                <? } ?>
                            <? } ?>      
                        </div>
                    </div>
                    <div class="b-post__txt b-post__txt_padtop_5" id="op_message_<?=$theme['id']?>"><?= reformat($theme['descr'], 30, 0, 0, 1) ?></div>
                <?php if ( ( ( $theme['fromuser_id'] == $_SESSION['uid'] && (strtotime($theme['posted_time'])+3600*24 > time()) ) || hasPermissions('users') ) && $theme['is_payed'] != 1) { ?>
    			<div class="b-post__foot b-post__foot_padtop_10">
                    <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="xajax_EditSBROpForm(<?= $theme['id'] ?>, '<?=$theme['login']?>')">Редактировать отзыв</a>
                    <?php if (hasPermissions('sbr') && $theme['is_payed'] != 1) { ?>
                    &nbsp;&nbsp;<a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="if (confirm('Удалить отзыв?')) {xajax_DeleteFeedback(<?= $theme['stage_id'] ?>, <?= $theme['id'] ?>, true)}">Удалить отзыв</a>
                    <?php }//if?>
                </div>
    			<?php }//if?>
                    <? if ($theme['touser_id'] == $_SESSION['uid']) { ?>
                        <div class="b-post__foot b-post__foot_padtop_10 <?= empty($theme['comm_id']) ? "" : "b-post__foot-empty"?>" id="feedback_buttons_<?= $theme['id']?>">
                            <a  class="b-post__link b-post__link_dot_0f71c8" id="feedback_opinion_btn_add_comment_<?= $theme['id']?>" href="javascript:void(0)" 
                                onclick="if(!this.disabled) { $(this).getParent('.b-post__foot').hide(); this.disabled = true; xajax_AddOpComentForm('<?= $theme['id']?>', '<?=$ops_type?>', true); return false; }">
                                Добавить комментарий</a>  &#160;&#160; 
                        </div>
                            <div class="b-post" id="feedback_comment_<?= $theme['id']?>" <?//= $showPostFootBlock ? '' : 'style="display:none"' ?>></div>
                    <? } ?>
                </div>
            </div>
        </div>
    </div>       
<? 

} else {
    
?>
    <div class="b-post" id="p_stage_<?=$theme['uniq_id']?>">
        <a name="p_<?=$theme['uniq_id']?>"></a>
        <div class="b-post__body b-post__body_pad_10_15_20">
            <div class="b-post__avatar">
                <a class="b-post__link" href="/users/<?= $theme['login'] ?>/"><?= view_avatar($theme['login'], $theme['photo'], 1, 1, 'b-post__userpic') ?></a>
            </div>
            <div class="b-post__content b-post__content_margleft_60">
                <div class="b-post__time b-post__time_float_right">
                    <a class="b-post__anchor b-post__anchor_margright_10" onclick="hlAnchor('p',<?= $theme['uniq_id'] ?>);" href="#p_<?= $theme['uniq_id'] ?>" title="Ссылка на этот отзыв"></a>
                    <?= date("d.m.Y в H:i", strtotime($theme['posted_time'])) ?>
                </div>
                <div id="form_container_<?= $theme['id'] . $uniq_id_sufix ?>" class="editFormSbr" style="display:none"></div>
                    <div id="form_container_to_<?= $theme['id'] . $uniq_id_sufix ?>" class="sbrmsgblock">
                        <div class="b-username b-username_padbot_5 b-username_bold<?php if($is_hidden) { ?> b-username_color_a7a7a6<?php } ?>"><?= view_user3($theme) ?></div>			
                        <?php if ( $theme['is_banned'] ) { ?><div style="color:#000; margin: 0 0 10px 0;" ><b>Пользователь&nbsp;забанен.</b></div><?php } ?>
                        <div class="b-fon b-fon_fontsize_11 b-fon_inline-block">
                            <div class="b-fon__body  b-fon__body_padleft_20 b-fon__body_bg_fff9bf b-fon__body_fontsize_11<?php if($is_hidden) { ?> b-post__txt_color_a7a7a6<?php } ?>">
                                <span id="cont_<?= $theme['id'] . $uniq_id_sufix ?>" class="b-button b-button_poll_<?= $theme['sbr_rating'] == -1 ? 'minus' : ($theme['sbr_rating'] == 1 ? 'plus' : 'multi') ?> b-button_active b-button_marg_-2_2_0_-15"></span> 
                            <?php if($theme['opinion_type'] == 2){ ?>
                                <?php if($is_emp){ ?>за заказ<?php }else{ ?>за выполнение заказа<?php } ?> 
                                    <?php if($theme['type'] == 0): ?>
                                    <a href="<?php echo tservices_helper::card_link($theme['project_id'], $theme['sbr_name']) ?>">
                                            <?= reformat($theme['sbr_name'], 40, 0, 1) ?>
                                    </a> 
                                    <?php else: ?>
                                        <b><?= reformat(htmlspecialchars($theme['sbr_name']), 40, 0, 1) ?></b>
                                    <?php endif; ?>
                                на сумму <?php echo tservices_helper::cost_format($theme['num'], true, false, false) ?>
                            <?php } elseif($theme['opinion_type'] == 3) { ?>
                                <?php if($is_emp){ ?>за проект<?php }else{ ?>за выполнение проекта<?php } ?>
                                <?php if($theme['kind'] == 9): ?>
                                    &laquo;<?= reformat($theme['sbr_name'], 40, 0, 1) ?>&raquo;
                                <?php else: ?>
                                    <a href="/projects/<?php echo $theme['project_id'] ?>"><?= reformat($theme['sbr_name'], 40, 0, 1) ?></a>
                                <?php endif; ?>
                            <?php } else { ?>
                                <?if($theme['is_payed'] == 1) { ?>
                                    подтвержденная рекомендация по итогам сотрудничества
                                <? } else { ?>
                                    по итогам сделки <span class="b-icon b-icon_top_3 b-icon_sbr_shield"></span>&nbsp; 
                                    <? if ($uid == $theme['frl_id'] || $uid == $theme['emp_id']) { // отзывы смотрит участнк сделк ?>
                                        «<a id='sbr_name_<?=$theme['id']?>' class="b-layout__link" href="<?=( sbr_meta::isNewVersionSbr($theme['scheme_type']) ? "/".sbr::NEW_TEMPLATE_SBR."/?id={$theme['sbr_id']}" : "/norisk2/?id={$theme['sbr_id']}" ) ?>"><?= reformat($theme['sbr_name'], 40, 0, 1) ?></a>» &nbsp;
                                    <? } elseif ($theme['project_id']) { ?>
                                        «<a id='sbr_name_<?=$theme['id']?>' class="b-post__link" href="/projects/<?= $theme['project_id'] ?>"><?= reformat($theme['sbr_name'], 40, 0, 1) ?></a>»<?= !$is_next?",":""?>
                                    <? } else { ?>
                                         «<span id='sbr_name_<?=$theme['id']?>'><?= reformat($theme['sbr_name'], 40, 0, 1) ?></span>»<?= !$is_next?",":""?> 
                                    <? } ?>
                                    этап «<span id='stage_name_<?=$theme['id']?>'><?= reformat($theme['stage_name'], 40, 0, 1); ?></span>», <?= ($theme['stage_status'] == 7) ? '<span class="b-post__txt b-post__txt_color_c10601 b-post__txt_fontsize_11">завершенный арбитражем</span>' : 'сданный'; ?> <?= date("d.m.Y в H:i", strtotime($theme['stage_closed'])) ?>
                                <? } ?>
                            <?php } ?>
                            </div>
                        </div>
                        <div class="b-post__txt b-post__txt_padtop_5<?php if($is_hidden) { ?> b-post__txt_color_a7a7a6<?php } ?>" id="op_message_<?=$theme['id'] . $uniq_id_sufix?>">
                            <?= reformat($theme['descr'], 30, 0, 0, 1) ?>
                        </div>
                        <?php 
                            if($theme['opinion_type'] == 2){
                                
                                $is_allow_edit = FALSE;
                                $is_owner = ($theme['touser_id'] == $uid);
                                if($is_owner) {
                                    $is_allow_edit = ($theme['sbr_rating'] < 0) || TServiceOrderFeedbackModel::isAllowFeedback($theme['posted_time']);
                                }
                                
                                $is_allow_edit = $is_tservices_adm || $is_allow_edit;
                                $is_allow_delete = $is_tservices_adm || $is_owner;
                        ?>
                            <?php if($is_allow_edit || $is_allow_delete){ ?>
                        <div class="b-post__foot b-post__foot_padtop_10">
                            <?php if($is_allow_edit){ ?>
                            <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="xajax_tservicesOrdersEditFeedback(<?= $theme['id'] ?>);">
                                Редактировать отзыв
                            </a>
                            &nbsp;&nbsp;
                            <?php } ?>
                            <?php if($is_allow_delete){ ?>
                            <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="if (confirm('Удалить отзыв?')) {xajax_tservicesOrdersDeleteFeedback(<?= $theme['id'] ?>);}">
                                Удалить отзыв
                            </a>
                            <?php }//if ?>
                        </div>
                            <?php }//if ?>
                        
                        <?php if(false){ ?>
                        <div class="b-post__foot b-post__foot_padtop_10 <?= empty($theme['comm_id']) ? "" : "b-post__foot-empty"?>" id="feedback_buttons_<?= $theme['id']?>">
                            <a class="b-post__link b-post__link_dot_0f71c8" id="feedback_opinion_btn_add_comment_<?= $theme['id']?>" href="javascript:void(0)" 
                               onclick="if(!this.disabled) { $(this).hide(); this.disabled = true; xajax_AddOpComentForm('<?= $theme['id']?>', '<?=$ops_type?>', true); return false; }">
                                Добавить комментарий
                            </a>  &#160;&#160; 
                            <div class="b-post" id="feedback_comment_<?= $theme['id']?>" <?//= $showPostFootBlock ? '' : 'style="display:none"' ?>></div>
                        </div>
                        <?php } ?>
                        <?php 
                        }
                        elseif($theme['opinion_type'] == 3)
                        { 
                            
                                $is_allow_edit = FALSE;
                                $is_owner = ($theme['touser_id'] == $uid);
                                if($is_owner) {
                                    $is_allow_edit = ($theme['sbr_rating'] < 0) || projects_feedback::isAllowFeedback($theme['posted_time']);
                                }
                                
                                $is_allow_edit = $is_tservices_adm || $is_allow_edit;
                                $is_allow_delete = $is_tservices_adm || $is_owner;                        
                        ?>
                        <?php if($is_allow_edit || $is_allow_delete){ ?>
                        <div class="b-post__foot b-post__foot_padtop_10">
                            <?php if($is_allow_edit){ ?>
                            <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="xajax_projectEditFeedback(<?= $theme['id'] ?>);">
                                Редактировать отзыв
                            </a>
                            &nbsp;&nbsp;
                            <?php } ?>
                            <?php if($is_allow_delete){ ?>
                            <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="if (confirm('Удалить отзыв?')) {xajax_projectDeleteFeedback(<?= $theme['id'] ?>);}">
                                Удалить отзыв
                            </a>
                            <?php }//if ?>
                        </div>
                            <?php }//if ?>
                        <?php
                        }
                        else
                        {
                        ?>
                        <?php if ( ( ( $theme['fromuser_id'] == $_SESSION['uid'] && (strtotime($theme['posted_time'])+3600*24 > time()) ) || hasPermissions('users') ) && $theme['is_payed'] != 1) { ?>
                        <div class="b-post__foot b-post__foot_padtop_10">
                        <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="xajax_EditSBROpForm(<?= $theme['id'] ?>, '<?=$theme['login']?>')">Редактировать отзыв</a>
                        <?php if (hasPermissions('sbr') && $theme['is_payed'] != 1) { ?>
                        &nbsp;&nbsp;<a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="if (confirm('Удалить отзыв?')) {xajax_DeleteFeedback(<?= $theme['stage_id'] ?>, <?= $theme['id'] ?>, true)}">Удалить отзыв</a>
                        <?php }//if?>
                        </div>
                        <?php }//if?>
                        <? if (($theme['touser_id'] == $_SESSION['uid'] || hasPermissions('users')) && $theme['is_payed'] != 1) { ?>
                            <div class="b-post__foot b-post__foot_padtop_10 <?= empty($theme['comm_id']) ? "" : "b-post__foot-empty"?>" id="feedback_buttons_<?= $theme['id']?>">
                                <a  class="b-post__link b-post__link_dot_0f71c8" id="feedback_opinion_btn_add_comment_<?= $theme['id']?>" href="javascript:void(0)" 
                                    onclick="if(!this.disabled) { $(this).getParent('.b-post__foot').hide(); this.disabled = true; xajax_AddOpComentForm('<?= $theme['id']?>', '<?=$ops_type?>', true); return false; }">
                                    Добавить комментарий</a>  &#160;&#160; 
                            </div>
                                <div class="b-post" id="feedback_comment_<?= $theme['id']?>" <?//= $showPostFootBlock ? '' : 'style="display:none"' ?>></div>
                        <? } ?>
                       <?php }//endif ?>
                    </div>
                    
                <?php if ($is_hidden && !$is_owner) {?>
                    <div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_padbot_10">
                        Данный отзыв пока скрыт и виден только вам и заказчику.<br>
                        Как только вы приобретете аккаунт PRO, все ваши скрытые отзывы станут видны всем.
                    </div>
                    <a class="b-button b-button_flat b-button_flat_green" href="/payed/">Купить PRO и опубликовать все скрытые отзывы</a>
                <?php } ?>
            </div>
        </div>
    </div>


    <? }
    
    if (!empty($theme['comm_id']) && $theme['is_payed'] != 1) {
        $comment = array(
            'comment'       => $theme['comm_text'],
            'id'            => $theme['comm_id'],
            'date_create'   => $theme['comm_date_create'],
            'user_id'       => $theme['comm_user_id'],
            'feedback_id'   => $theme['id'],
        ) ?>
        <div class="b-post" id="feedback_comment_cont_<?= $theme['id'] . $uniq_id_sufix?>">
            <a name="c_<?= $theme['id'] . $uniq_id_sufix?>"></a>
            <?
            $isFeedback = true;
            include ($_SERVER['DOCUMENT_ROOT']."/user/opinions/comment.tpl.php");
            unset($isFeedback);
            ?>
        </div>
    <? } elseif (($theme['touser_id'] == $_SESSION['uid'] || hasPermissions('users')) && $theme['is_payed'] != 1) {//if?>
        <div class="b-post" id="feedback_comment_cont_<?= $theme['id']?>">
            <a name="c_<?= $theme['id']?>"></a>
        </div>
    <? }
    
    $oid = $theme['sbr_id']; 
    
    
    //$_next = $is_next; 

} //endfor
    
?>


    <? if (is_array($msgs2) && count($msgs2)) { $aUser = get_object_vars($user); ?>
        <? foreach($msgs2 as $opinion) {
            $opcomm = opinions::getCommentOpinionById(array($opinion['id']));
            $cls_rating = ( $opinion['rating'] == 1 ? "b-button_poll_plus" : ($opinion['rating'] == 0 ? " b-button_poll_multi" : "b-button_poll_minus") );
        ?>
        <div class="b-post" id="opinion_<?= $opinion['id']?>">
            <a name="o_<?=$opinion['id']?>"></a>
            <div class="b-post__body b-post__body_pad_10_15_20" >
                <div class="b-post__avatar"> 
                    <a class="b-post__link" href="/users/<?=$opinion['login']?>/"><?= view_avatar($opinion['login'], $opinion['photo'], 1, 1, 'b-post__userpic') ?></a>
                </div>
                <div class="b-post__content b-post__content_margleft_60">
                    <div class="b-post__time b-post__time_float_right"> 
                        <a class="b-post__anchor b-post__anchor_margright_10" onclick="hlAnchor('o',<?= $opinion['id'] ?>);" href="#o_<?= $opinion['id'] ?>" title="Ссылка на это мнение"></a> <?= date('d.m.Y в H:i', strtotime($opinion['post_time']))?> 
                    </div>
                    <div class="b-username b-username_padbot_5 b-username_bold">
                        <?= view_user3($opinion)?>
                    </div>
                    <span class="b-button <?= $cls_rating?> b-button_active"></span>
                    <div class="b-post__txt b-post__txt_padtop_5">
                        <?= reformat($opinion['msgtext'], 40)?>
                    </div>
                        <?php 
                              $showPostFootBlock =(($opinion['rating'] == 1 && is_emp($opinion['role']) != is_emp($user->role) && $opinion['is_converted'] != 't') || (empty($opcomm[$opinion['id']])));
                        ?>
                        <?php if($opinion['touser_id'] == $_SESSION['uid']) {?>
							<div class="b-post__foot b-post__foot_padtop_10 " id="opinion_buttons_<?= $opinion['id']?>" <?= empty($opcomm[$opinion['id']]) ? "" : "style='display:none'"?>>
                                <a class="b-post__link b-post__link_dot_0f71c8" 
                                   id="opinion_btn_add_comment_<?= $opinion['id']?>" href="#" 
                                   onclick="if(!this.disabled) { $(this).getParent('.b-post__foot').hide(); this.disabled = true; xajax_AddOpComentForm('<?= $opinion['id']?>', '<?=$ops_type?>'); return false; }">
                                Добавить комментарий</a>  &#160;&#160; 
							</div>
                            <div class="b-post" id="comment_<?= $opinion['id']?>" <?= $showPostFootBlock ? '' : 'style="display:none"' ?>></div>
                        <? } ?>
                        <?php if($is_user_admin): ?>    
                        <a class="b-post__link b-post__link_dot_c10601" 
                           href="javascript:void(0)" 
                           onclick="if (confirm('Вы действительно хотите удалить отзыв?')) xajax_DeleteOpinion(<?= $opinion['id'] ?>,'<?= (is_emp($opinion['role'])?'emp':'frl')?>'); return false;">
                            Удалить отзыв</a>
                        <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if(!empty($opcomm[$opinion['id']])) { $comment = $opcomm[$opinion['id']];?>
            <div class="b-post" id="comment_cont_<?= $opinion['id']?>">
                <a name="c_<?= $opinion['id']?>"></a>
                <? include ($_SERVER['DOCUMENT_ROOT']."/user/opinions/comment.tpl.php");?>
            </div>
        <?php } elseif($opinion['touser_id'] == $_SESSION['uid']) {//if?>
            <div class="b-post" id="comment_cont_<?= $opinion['id']?>">
                <a name="c_<?= $opinion['id']?>"></a>
            </div>
        <?php }//elseif?>
        <?php }//foreach?>
    
    <?php } ?>
    
