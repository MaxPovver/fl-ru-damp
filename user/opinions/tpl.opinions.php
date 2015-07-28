<div class="b-layout b-layout_padtop_20 b-fon overlay-cls">
<?php 
        //https://beta.free-lance.ru/mantis/view.php?id=29146#c88101
        if(false): 
?>
    <div class="b-post b-post_padtop_10 b-post_padright_15 b-post_float_right b-post__txt b-post__txt_fontsize_11 b-post__txt_hide" id="manager_feedback">
        <span class="b-post__qwest"></span> &#160;
        <a class="b-post__link b-post__link_fontsize_11 b-post__link_color_4e" href="/contacts/?from=opinions">
            Обратиться к менеджеру за помощью
        </a>
    </div>
    <h2 class="b-layout__title b-layout__title_pad_0_15_15">
        <?= $count?>&#160;<?= ending($count, "мнение", "мнения", "мнений")?> 
        &#160;&#160;&#160;
        <div class="b-filter">
            <div class="b-filter__body"><a class="b-filter__link b-filter__link_ie7_top_3 b-filter__link_dot_0f71c8 b-layout__link_fontsize_13" href="#"><?=$filter_type_user?></a></div>
            <div class="b-shadow b-shadow_marg_-32 b-filter__toggle b-filter__toggle_hide">
                <div class="b-shadow__right">
                    <div class="b-shadow__left">
                        <div class="b-shadow__top">
                            <div class="b-shadow__bottom">
                                <div class="b-shadow__body b-shadow__body_pad_15 b-shadow__body_bg_fff">
                                    <ul class="b-filter__list">
                                        <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($ops_type == 'users' ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/?from=users&sort=<?= $sort?>&period=<?= $period?>#op_head'">всех пользователей</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($ops_type == 'users' ?"" : "b-filter__marker_hide")?>"></span></li>
                                        <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($ops_type == 'emp' ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/?from=emp&sort=<?= $sort?>&period=<?= $period?>#op_head'">работодателей</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($ops_type == 'emp' ?"" : "b-filter__marker_hide")?>"></span></li>
                                        <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($ops_type == 'frl' ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/?from=frl&sort=<?= $sort?>&period=<?= $period?>#op_head'">фри-лансеров</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($ops_type == 'frl' ?"" : "b-filter__marker_hide")?>"></span></li>
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
        &#160;&#160;&#160;
        <div class="b-filter">
            <div class="b-filter__body"><a class="b-filter__link b-filter__link_ie7_top_3 b-filter__link_dot_0f71c8 b-layout__link_fontsize_13" href="#"><?=$filter_string?></a></div>
            <div class="b-shadow b-shadow_marg_-32 b-filter__toggle b-filter__toggle_hide">
                <div class="b-shadow__right">
                    <div class="b-shadow__left">
                        <div class="b-shadow__top">
                            <div class="b-shadow__bottom">
                                <div class="b-shadow__body b-shadow__body_pad_15 b-shadow__body_bg_fff">
                                    <ul class="b-filter__list">
                                        <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($period == 0 ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/<?=$html_for_filter . "&period=0#op_head"; ?>'" >за всё время</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($period == 0 ?"" : "b-filter__marker_hide")?>"></span></li>
                                        <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($period == 1 ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/<?=$html_for_filter . "&period=1#op_head"; ?>'" >за последний год</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($period == 1 ?"" : "b-filter__marker_hide")?>"></span></li>
                                        <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($period == 2 ?"b-filter__link_no" : "")?>" onclick="window.location = '/users/<?=$user->login?>/opinions/<?=$html_for_filter . "&period=2#op_head"; ?>'" >за последние полгода</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($period == 2 ?"" : "b-filter__marker_hide")?>"></span></li>
                                        <li class="b-filter__item b-filter__item_padbot_3 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15"><a class="b-filter__link <?= ($period == 3 ?"b-filter__link_no" : "")?>"  onclick="window.location = '/users/<?=$user->login?>/opinions/<?=$html_for_filter . "&period=3#op_head"; ?>'" >за последний месяц</a><span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka <?= ($period == 3 ?"" : "b-filter__marker_hide")?>"></span></li>
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
<?php
    endif;
?>
    <?php if (is_array($msgs) && count($msgs)) { $aUser = get_object_vars($user); ?>
        <?php foreach($msgs as $opinion) {
            $opcomm = opinions::getCommentOpinionById(array($opinion['id']));
            $cls_rating = ( $opinion['rating'] == 1 ? "b-button_poll_plus" : ($opinion['rating'] == 0 ? " b-button_poll_multi" : "b-button_poll_minus") );
        ?>
        <div class="b-post" id="opinion_<?= $opinion['id']?>">
            <a name="o_<?=$opinion['id']?>"></a>
            <div class="b-post__body b-post__body_pad_10_15_20" >
                <div class="b-post__avatar"> 
                    <a class="b-post__link" href="/users/<?=$opinion['login']?>/"><?= view_avatar($opinion['login'], $opinion['photo'], 1, 1, 'b-post__userpic') ?></a>
                </div>
                <div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
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
                        <?php if($opinion['touser_id'] == $_SESSION['uid'] && $showPostFootBlock) {?>
																									<div class="b-post__foot b-post__foot_padtop_10 " id="opinion_buttons_<?= $opinion['id']?>" <?= empty($opcomm[$opinion['id']]) ? "" : "style='display:none'"?>>
                            <a class="b-post__link b-post__link_dot_0f71c8" 
                                
                                id="opinion_btn_add_comment_<?= $opinion['id']?>" href="#" 
                                onclick="if(!this.disabled) { $(this).getParent('.b-post__foot').hide(); this.disabled = true; xajax_AddOpComentForm('<?= $opinion['id']?>', '<?=$ops_type?>'); return false; }">
                                Добавить комментарий</a>  &#160;&#160; 
                            <?php 
                            /**
                             * @deprecated #0019740  
                             */
                            /*if($opinion['rating'] == 1 && is_emp($opinion['role']) != is_emp($user->role) && $opinion['is_converted'] != 't') {?>
                            <a class="b-post__link" href="/users/<?=$aUser['login']?>/opinions/?from=norisk&opinion=<?= $opinion['id'];?>">Перевести мнение в рекомендацию</a> &#160;&#160;
                            <a target="_blank" href="/help/?q=1002#convert" class="b-post__link b-post__link_color_4e"><span class="b-post__qwest"></span></a> &#160;<a target="_blank" href="/help/?q=1002#convert" class="b-post__link b-post__link_color_4e">Что это такое?</a>
                            <?php }//*/?>
																									</div>
                            <div class="b-post" id="comment_<?= $opinion['id']?>"></div>
                        <?php } //if?>
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
    <?/*
    <div class="b-post" >
        <div class="b-post__body b-post__body_pad_10_15_20" >
            <div class="b-post__avatar"> <a class="b-post__link" href="/users/UPshifter/"><img src="http://betadav.free-lance.ru/users/UPshifter/foto/sm_f_4a43cc568eee5.gif" alt="UPshifter" width="50" height="50" class="b-post__userpic" /></a> </div>
            <div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
                <div class="b-post__time b-post__time_float_right"> <a class="b-post__anchor b-post__anchor_margright_10"  href="#" title="Ссылка на эту рекомендацию"></a> 18.03.2010 в 18:21 </div>
                <div class="b-username b-username_padbot_5 b-username_bold"><a class="b-username__link b-username__link_color_000" href="/users/UPshifter">Апшифтер Самойлович</a> <span class="b-username__login b-username__login_color_6db335">[<a class="b-username__link b-username__link_color_6db335" href="/users/UPshifter">UPshifter</a>]</span></div>
                <a class="b-button b-button_poll_plus b-button_active" href="#"></a>
                <div class="b-post__txt b-post__txt_padtop_5">Надежный и порядочный человек.<br />Было приятно с ним работать.</div>
                <div class="b-post__foot b-post__foot_padtop_10 "><a class="b-post__link b-post__link_dot_0f71c8" href="#">Добавить комментарий</a> &#160;&#160; <a class="b-post__link" href="#">Перевести мнение в рекомендацию</a> &#160;&#160; <a target="_blank" href="#" class="b-post__link b-post__link_color_4e"><span class="b-post__qwest"></span></a> &#160;<a target="_blank" href="#" class="b-post__link b-post__link_color_4e">Что это такое?</a></div>
            </div>
        </div>
    </div>

    <div class="b-post b-post_bg_f0f4f5 b-post_bordbot_solid_dfe3e4" >
        <div class="b-post__body b-post__body_pad_10_15_20" >
            <div class="b-post__avatar"> <a class="b-post__link" href="/users/UPshifter/"><img src="http://betadav.free-lance.ru/users/UPshifter/foto/sm_f_4a43cc568eee5.gif" alt="UPshifter" width="50" height="50" class="b-post__userpic" /></a> </div>
            <div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
                <div class="b-post__time b-post__time_float_right"> <a class="b-post__anchor b-post__anchor_margright_10"  href="#" title="Ссылка на эту рекомендацию"></a> 18.03.2010 в 18:21 </div>
                <div class="b-username b-username_padbot_5 b-username_bold"><a class="b-username__link b-username__link_color_000" href="/users/UPshifter">Апшифтер Самойлович</a> <span class="b-username__login b-username__login_color_6db335">[<a class="b-username__link b-username__link_color_6db335" href="/users/UPshifter">UPshifter</a>]</span></div>
                <a class="b-button b-button_poll_minus b-button_active" href="#"></a>
                <div class="b-post__txt b-post__txt_padtop_5">Надежный и порядочный человек.<br />Было приятно с ним работать.</div>
            </div>
        </div>
    </div>

    <div class="b-post" >
        <div class="b-post__body b-post__body_pad_10_15_20_30" >
            <div class="b-post__avatar"> <a class="b-post__link" href="/users/UPshifter/"><img src="http://betadav.free-lance.ru/users/UPshifter/foto/sm_f_4a43cc568eee5.gif" alt="UPshifter" width="50" height="50" class="b-post__userpic" /></a> </div>
            <div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
                <div class="b-post__time b-post__time_float_right"> <a class="b-post__anchor b-post__anchor_margright_10"  href="#" title="Ссылка на эту рекомендацию"></a> 18.03.2010 в 18:21 </div>
                <div class="b-username b-username_padbot_5 b-username_bold"><a class="b-username__link b-username__link_color_000" href="/users/UPshifter">Апшифтер Самойлович</a> <span class="b-username__login b-username__login_color_6db335">[<a class="b-username__link b-username__link_color_6db335" href="/users/UPshifter">UPshifter</a>]</span></div>
                <div class="b-post__txt b-post__txt_padtop_5">Надежный и порядочный человек.<br />Было приятно с ним работать.</div>
                <div class="b-post__foot b-post__foot_padtop_10 "><a class="b-post__link b-post__link_dot_c10601" href="#">Редактировать</a> &#160;&#160; <a class="b-post__link b-post__link_dot_c10601" href="#">Удалить</a></div>
            </div>
        </div>
    </div>

    <div class="b-post" >
        <div class="b-post__body b-post__body_pad_10_15_20" >
            <div class="b-post__avatar"> <a class="b-post__link" href="/users/UPshifter/"><img src="http://betadav.free-lance.ru/users/UPshifter/foto/sm_f_4a43cc568eee5.gif" alt="UPshifter" width="50" height="50" class="b-post__userpic" /></a> </div>
            <div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
                <div class="b-post__time b-post__time_float_right"> <a class="b-post__anchor b-post__anchor_margright_10"  href="#" title="Ссылка на эту рекомендацию"></a> 18.03.2010 в 18:21 </div>
                <div class="b-username b-username_padbot_5 b-username_bold"><a class="b-username__link b-username__link_color_000" href="/users/UPshifter">Апшифтер Самойлович</a> <span class="b-username__login b-username__login_color_6db335">[<a class="b-username__link b-username__link_color_6db335" href="/users/UPshifter">UPshifter</a>]</span></div>
                <a class="b-button b-button_poll_minus b-button_active" href="#"></a>
                <div class="b-post__txt b-post__txt_padtop_5">Надежный и порядочный человек.<br />Было приятно с ним работать.</div>
                <div class="b-post__foot b-post__foot_padtop_10 "><a class="b-post__link" href="#">Перевести мнение в рекомендацию</a></div>
            </div>
        </div>
    </div>

    <div class="b-post" >
        <div class="b-post__body b-post__body_pad_10_15_20_30" >
            <div class="b-post__avatar"> <a class="b-post__link" href="/users/UPshifter/"><img src="http://betadav.free-lance.ru/users/UPshifter/foto/sm_f_4a43cc568eee5.gif" alt="UPshifter" width="50" height="50" class="b-post__userpic" /></a> </div>
            <div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
                <div class="b-post__time b-post__time_float_right"> <a class="b-post__anchor b-post__anchor_margright_10"  href="#" title="Ссылка на эту рекомендацию"></a> 18.03.2010 в 18:21 </div>
                <div class="b-username b-username_padbot_5 b-username_bold"><a class="b-username__link b-username__link_color_000" href="/users/UPshifter">Апшифтер Самойлович</a> <span class="b-username__login b-username__login_color_6db335">[<a class="b-username__link b-username__link_color_6db335" href="/users/UPshifter">UPshifter</a>]</span></div>
                <div class="b-post__txt b-post__txt_padtop_5">Надежный и порядочный человек.<br />Было приятно с ним работать.</div>
                <div class="b-post__foot b-post__foot_padtop_10 "><a class="b-post__link b-post__link_dot_c10601" href="#">Редактировать</a> &#160;&#160; <a class="b-post__link b-post__link_dot_c10601" href="#">Удалить</a></div>
            </div>
        </div>
    </div>
    <?php } else {//if?>
    <div id="no_messages" style="font-size:12px">
        <br /><br />
        <table width="100%" cellspacing="0" cellpadding="0" >
            <tbody>
                <tr>
                    <td>&nbsp;</td>
                    <td style="padding-bottom: 10px;">Мнений нет</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>
    </div>*/?>
    <?php }//else?>
</div> 