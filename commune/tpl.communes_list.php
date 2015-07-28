<?php
//$page = isset($page) ? $page : 1;
$groupCommCnt = isset($groupCommCnt) ? $groupCommCnt : 0;
$comm_is_ajax = false;

if (is_array($communes) && count($communes)) {
    // начало нумерации сообществ для своей сортировки
    $comm_limit          = commune::MAX_ON_PAGE;
    $comm_start_position = ($page - 1) * $comm_limit;
    $i = 0;
    foreach ($communes as $comm) {
        $i++;
        // заголовок
        $comm_url = getFriendlyURL('commune_commune', $comm['id']);
        $comm_name = "<a href='".$comm_url."' class='b-post__link'>" . ($search !== NULL ? highlight(reformat2($comm['name'], 25, 1, 1), $search, 20) : reformat2($comm['name'], 25, 1, 1)) . "</a>";
        $comm_descr = ($search !== NULL ? highlight(reformat2($comm['descr'], 25, 1), $search) : reformat2($comm['descr'], 25, 1));
        // Сколько участников.
        $mAcceptedCnt = $comm['a_count'] - $comm['w_count'] + 1; // +1 -- создатель
        $mCnt = $mAcceptedCnt . ' участник' . getSymbolicName($mAcceptedCnt, 'man');
        ?>
        <div class="b-post b-post_padbot_20">
            <div class="b-post__body b-post__body_bordbot_solid_f0  b-post__body_padbot_30 b-layout">
                <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                    <tr class="b-layout__tr">
                    <? /*
                        <? if ( $sub_om == commune::OM_CM_JOINED_MY ) { ?>
                        <div class="form c-my-sort">
                            <b class="b1"></b>
                            <b class="b2"></b>
                            <div class="form-in">
                                <a onclick="xajax_CommuneMove(<?= $comm['id'] ?>, '>', <?= $group_id?$group_id:0 ?>, 10, <?= $page ?>);" href="javascript:void(0);" class="b-sqr b-sqr-t1"><em></em></a>
                                <a onclick="$('commune_set_order_<?= $comm['id'] ?>').setStyle('display', '');" href="javascript:void(0);" class="b-sqr b-sqr-t2"><em><?= $comm_start_position + $i ?></em></a>
                                <a onclick="xajax_CommuneMove(<?= $comm['id'] ?>, '<', <?= $group_id?$group_id:0 ?>, 10, <?= $page ?>);" href="javascript:void(0);" class="b-sqr b-sqr-t3"><em></em></a>
                            </div>
                            <b class="b2"></b>
                            <b class="b1"></b>
                        </div>
                        <? } ?>
																					*/ ?>
																								
                        <td class="b-layout__left b-layout__left_width_220">
                            <? // картинка сообщества
                            seo_start();
                            $img_file = $comm['image'];
                            $img_src = WDCPREFIX."/users/" . $comm['author_login'] . "/upload/" . $img_file;
                            if ($img_file) { ?>
                                <a href='/commune/?id=<?= $comm['id'] ?>'>
                                    <img class="b-post__pic" src="<?= $img_src ?>" alt=''/>
                                </a>
                            <? } else { ?>
                                &nbsp;
                            <? } ?>
                            <?= seo_end();?>
                        </td>
                        <td class="b-layout__right">
                            <div class="b-post__content i-button">
                                <? // блок голосования ?>
                                <? if($comm['id']!=5100) { ?>
                                <div id="idCommRating_<?= $comm['id'] ?>" class="b-voting b-voting_float_right">
                                        <script type="text/javascript">var lockRating<?=$comm['id']?>=0;</script>
                                        <? // блок голосования
                                        $rate_uid = get_uid(false);
                                        $rate_rating = $comm['yeas'] - $comm['noes'];
                                        if ($rate_uid) {
                                            $rate_vote = commune::GetUserVote($comm['id'], $rate_uid);
                                        }
                                        $rate_p_onClick  = ''; $rate_m_onClick = '';                                        
                                        $rate_p_href = " href='javascript:void(0)'";
                                        $rate_p_alt = ''; $rate_m_alt = '';
                                        $rate_onclick = '';
                                        if ($rate_uid && $comm['author_uid'] != $rate_uid && $comm['current_user_join_status'] == commune::JOIN_STATUS_ACCEPTED) {
                                            $rate_onclick = "xajax_Vote('idCommRating_','{$comm['id']}', '{$rate_uid}', document.getElementById('idCommRatingValue_{$comm['id']}').innerHTML";
                                        }
                                        if ($rate_onclick) {
                                            if ($rate_vote != 1) {
                                                $rate_p_onClick = " onclick=\"try { if(!lockRating{$comm['id']}) {$rate_onclick},  1); lockRating{$comm['id']}=1; } catch(e) { }\"";
                                                $rate_p_alt = " alt='+'";
                                            }
                                            if ($rate_vote != -1) {
                                                $rate_m_onClick = " onclick=\"try { if(!lockRating{$comm['id']}) {$rate_onclick}, -1); lockRating{$comm['id']}=1; } catch(e) { }\"";
                                                $rate_m_alt = " alt='-'";
                                            }
                                        }
                                        ?>
                                        <?php $classname = $rate_rating < 0 ? '_color_red' : ($rate_rating >= 1 ? '_color_green' : '') ;?>
                                        <? if($rate_onclick && $rate_vote != 1) { ?>
                                        <a class="b-button b-button_poll_plus normal_behavior b-button_active b-voiting__right"<?=$rate_p_href.$rate_p_alt.$rate_p_onClick?>></a>
                                        <? } else { ?>
                                        <a class="b-button b-button_poll_plus normal_behavior b-button_poll_nopointer b-voiting__right"></a>
                                        <? } ?>
                                        <? if($rate_onclick && $rate_vote != -1) { ?>
                                        <a class="b-button b-button_poll_minus normal_behavior b-button_active b-voiting__left"<?=$rate_p_href.$rate_m_alt.$rate_m_onClick?>></a>
                                        <? } else { ?><a class="b-button b-button_poll_minus normal_behavior b-button_poll_nopointer b-voiting__left"></a><? } ?>
                                        <span class="b-voting__mid b-voting__mid<?=$classname?>" id="idCommRatingValue_<?=$comm['id']?>"><?= ($rate_rating > 0 ? '+' : ($rate_rating < 0 ? '&minus;' : '')) . abs(intval($rate_rating))?></span>                    
                                </div>
                                <? } ?>
                                <? // название сообщества ?>
                                <h3 class="b-post__title b-post__title_padbot_15"><?= $comm_name ?></h3>
                                <? // описание сообщества ?>
                                <div class="b-post__txt b-post__txt_padbot_20"><?= $comm_descr ?></div>
                                <? // инфа о сообществе ?>
                                <div class="b-post__foot">
                                    <? // количество участников и постов ?>
                                    <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_11">
                                        <a class="b-post__link b-post__link_fontsize_11 b-post__link_float_right" href="<?= $comm_url ?>">
                                            <?
                                            $themesCount = commune::getCommuneThemesCount($comm['id']);
                                            if (hasPermissions('communes')) {
                                                $themes_count = $themesCount['count'];
                                            } elseif ($comm['author_id'] == $uid || $comm['is_moderator'] === 't') {
                                                $themes_count = $themesCount['count'] - $themesCount['admin_hidden_count'];
                                            } else {
                                                $themes_count = $themesCount['count'] - $themesCount['hidden_count'];
                                            }
                                            $for_admin = ($comm['author_id'] == uid || hasPermissions('communes')) ? true : false;
                                            ?>
                                            <?= $themes_count.' '.ending($themes_count, 'пост', 'поста', 'постов') ?>
                                        </a>
                                        <?= $mAcceptedCnt.' '.ending($mAcceptedCnt, 'участник', 'участника', 'участников') ?>
                                    </div>
                                    <? // дата создания ?>
                                    <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_11">Создано 
                                        <?= __commPrntAgeEx($comm) ?>
                                    </div>
                                    <? // создатель ?>
                                    <div class="b-post__txt b-post__txt_padbot_30 b-post__txt_fontsize_11">Создатель 
                                        <span class="b-username b-username_bold b-username_fontsize_11">
                                            <?//= __commPrntUsrInfo($comm, 'author_', '', '', false) ?>
                                            <?
                                            $info_pfx = 'author_';
                                            $info_cls = '';
                                            $info_sty = '';
                                            $info_hyp = false;
                                            $info_ajax_view = false;
                                            
                                            $info_is_emp = is_emp($comm[$info_pfx . 'role']);
                                            $info_login = $comm[$info_pfx . 'login'];
                                            $info_uname = $comm[$info_pfx . 'uname'];
                                            $info_usurname = $comm[$info_pfx . 'usurname'];
                                            if ($info_sty) {
                                                $info_sty = " style='$info_sty'";
                                            } elseif ($info_is_emp) {
                                                $info_sty = " style='color:green'";
                                            }
                                            if (!$info_cls) $info_cls = ($info_is_emp ? 'b-username__login_color_6db335' : 'b-username__login_color_fd6c30');
                                            if ($info_hyp) {
                                                $info_uname = hyphen_words($comm['dsp_uname']? $comm['dsp_uname']: $info_uname);
                                                $info_usurname = hyphen_words($comm['dsp_usurname']? $comm['dsp_usurname']: $info_usurname);
                                            }
                                            /*!!!is_team!!!*/
                                            if(@$comm[$info_pfx.'is_profi'] == 't') {
                                                $info_pro = view_profi();
                                            } else {
                                                $info_pro = ($comm[$info_pfx.'is_pro']=='t'?($info_is_emp?view_pro_emp():view_pro2(($comm[$info_pfx.'is_pro_test']=='t')?true:false)):""); 
                                            }
                                            
                                            $is_team = view_team_fl();
                                            if($comm[$info_pfx . 'is_verify'] == 't') {
                                                $info_pro .= view_verify();
                                                $is_team  .= view_verify();
                                            }
                                            $seo_text = "<a class=\"b-username__link\"{$info_sty} href=\"/users/{$info_login}\" title=\"{$info_login}\">[".($comm['dsp_login']? $comm['dsp_login']: $info_login)."]</a>";
                                            ?>
                                                <a class='b-username__link' href='/users/<?= $info_login ?>'><?= $info_uname ?> <?= $info_usurname ?></a>&nbsp;
                                                <span class='b-username__login <?= $info_cls ?>'>
                                                    <?= $info_ajax_view ? $seo_text : seo_end($seo_text) ?>
                                                </span>
                                                <?= $comm[$info_pfx . 'is_team'] == 't' ? $is_team : $info_pro ?>
                                                
                                        </span>
                                    </div>
                                    <? // информация о блокировке сообщества
                                    if($comm['is_blocked'] == 't') {
                                        echo __commPrntBlockedBlock($comm['blocked_reason'], $comm['blocked_time'], $comm['admin_login'], "{$comm['admin_name']} {$comm['admin_uname']}");
                                    } ?>
                                    <? // вступить в сообщество ?>
                                    <? if ($uid = get_uid(false)) { ?>
                                        <span id="commSubscrButton_<?= $comm['id'] ?>">
                                            <?//= __commPrntSubmitButton($comm, $uid, null, 'green') ?>
                                            <? // подписка на рассылку
                                            $subs_mode = 'green';
                                            $subs_a_style = 'b-button b-button_flat b-button_flat_grey b-button_margbot_10_ipad';
                                            $subs_span_style = 'b-button__txt';
                                            if (!$subs_mode) { ?>&nbsp;&nbsp;<? }
                                            if ($comm['is_banned'] === 't' || ($comm['current_user_join_status'] != commune::JOIN_STATUS_ACCEPTED && $comm['author_uid'] != $uid)) {
                                                // если пользователь забанен или еще не вступил в сообщество
                                            } else {
                                                if (commune::isCommuneSubscribed($comm['id'],$uid)) {
                                                    $subs_onclick = "xajax_SubscribeCommune(".$comm['id'].",false,'$subs_mode'); return false;"; ?>
                                                    <a href="javascript:void(0)" onclick="<?= $subs_onclick ?>" class="<?= $subs_a_style ?>">Отписаться</a>
                                                <? } else { 
                                                    $subs_onclick = "xajax_SubscribeCommune(".$comm['id'].",true,'$subs_mode'); return false";
                                                    if ($subs_mode == 'green') { ?>
                                                        <a href="javascript:void(0)" onclick="<?= $subs_onclick ?>" class="<?= $subs_a_style ?>">Подписаться</a>
                                                    <? } else { ?>   
                                                        <a href="javascript:void(0)" onclick="<?= $subs_onclick ?>" class="<?= $subs_a_style ?>">Подписаться</a>
                                                    <? } ?>
                                                <? }
                                            } ?>
                                        </span>
                                        <? // __commPrntJoinButton($comm, $uid, null, 1) // кнопка ВСУПИТЬ В СООБЩЕСТВО ?>
                                        <? // кнопка ВСТУПИТЬ/ВЫЙТИ из сообщества
                                        $join_async = 1;
                                        $join_a_style = "b-button b-button_flat b-button_flat_grey b-button_margbot_10_ipad";
                                        $join_span_style = "b-button__txt";
                                        if ($comm['author_uid']==$uid || !get_uid(false)) {
                                            // если пользователь = автор сообщества или неавторизован
                                        } elseif ($comm['is_banned'] === 't') { ?>
                                            <div class="b-fon b-fon_width_full b-fon_padtop_20">
                                                <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_padleft_30 b-fon__body_bg_ff6d2d">
                                                    <span class="b-fon__attent_white"></span>
                                                    <span class="b-fon__txt b-fon__txt_bold">Вы заблокированы в этом сообществе</span>
                                                </div>
                                            </div>                                            
                                        <? } elseif ($comm['current_user_join_status'] == commune::JOIN_STATUS_NOT || $comm['current_user_join_status'] == commune::JOIN_STATUS_DELETED) { ?>
                                            <a href="javascript:void(0)" onclick="xajax_JoinCommune(<?= $comm["id"] ?>, <?= $join_async ?>); return false;" class="<?= $join_a_style ?>">Вступить в сообщество</a>
                                        <? } elseif ($comm['current_user_join_status'] == commune::JOIN_STATUS_ASKED) { ?>
                                            <a href="javascript:void(0)" onclick="xajax_OutCommune(<?= $comm["id"] ?>, <?= $join_async ?>); return false;" class="<?= $join_a_style ?>">Отозвать заявку</a>
                                        <? } elseif ($comm['current_user_join_status'] == commune::JOIN_STATUS_ACCEPTED) { ?>
                                            <a href="javascript:void(0)" onclick="xajax_OutCommune(<?= $comm["id"] ?>, <?= $join_async ?>); return false;" class="<?= $join_a_style ?>">Выйти из сообщества</a>
                                        <? } ?>
                                    <? } ?>
                                </div>
                                <? if ( $sub_om == commune::OM_CM_JOINED_MY ) { ?>
                                <div id="commune_set_order_<?= $comm['id'] ?>" class="overlay ov-out ov-commune-sort" style="display: none;">
                                    <b class="c1"></b>
                                    <b class="c2"></b>
                                    <b class="ov-t"></b>
                                    <div class="ov-r">
                                        <div class="ov-l">
                                            <div class="ov-in">
                                                <label>Позиция</label> <input type="text" id="position_time_<?= $comm['id'] ?>" name="position_time_<?= $comm['id'] ?>" size="3">&nbsp;
                                                <button onclick="xajax_CommuneSetPosition(<?= $comm['id'] ?>, <?= $comm_start_position+$i ?>, $('position_time_<?= $comm['id'] ?>').get('value'), <?= $groupCommCnt ?>, <?= $group_id?$group_id:0 ?>, 10, <?= $page ?>);">Применить</button>&nbsp;
                                                <a href="javascript:void(0);" onclick="$(this).getParent('.overlay').setStyle('display', 'none');" class="lnk-dot-666">Отменить</a>
                                            </div>
                                        </div>
                                    </div>
                                    <b class="ov-b"></b>
                                    <b class="c3"></b>
                                    <b class="c4"></b>
                                </div>            
                                <? } ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <? }
} ?>