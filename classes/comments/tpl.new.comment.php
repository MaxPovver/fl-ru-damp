<? if(($is_new || $data['is_new']) && $data['author_login'] != $_SESSION['login']) { ?>
    <script type="text/javascript">
        new_comments[new_comments.length] = <?=$data['id']?>;
    </script>
    <a name="unread"></a>
<? } ?>
<?php

if($data['author_is_banned']) {
    $show_banned_text = false;
    if($data['is_permission']) {
        $show_banned_text = true; 
    } else {
        $data['msgtext'] = 'Ответ от заблокированного пользователя';
    }
}

$author_user = array('login'    => $data['author_login'], 
                     'uname'    => $data['author_uname'], 
                     'usurname' => $data['author_usurname'],
                     'role'     => $data['author_role'],
                     'is_pro'   => $data['author_is_pro'],
                     'is_pro_test'   => $data['author_is_pro_test'],
                     'is_team'  => $data['author_is_team'],
                     'is_verify' => $data['author_is_verify'],
                     'is_profi' => $data['author_is_profi']
                );
if($this->enableAutoModeration && !$this->auto_mod->isImmunity($data['author_uid'], array('is_team' => $data['author_is_team'], 'login' => $data['author_login']), $msg['id']) ) {
    $actionRating = $this->auto_mod->actionByRate($data['rating'], $this->auto_mod->getScale('comment'));
}
?>
    
<div class="b-post <?php if($data['level']){if($data['level']<15) {print("b-post_padleft_" . ( $data['level'] * 35 ));}else{ print('b-post_padleft_490');}}?> b-fon b-fon_padbot_10">
    <div class="b-post__body b-post__body_relative b-post__body_pad_10_10_15  b-post__body_marglr_-10 <?= ( $data['is_new'] ? 'b-fon__body_bg_f0ffdf' : '');?>">
        <div class="b-post__time b-post__time_float_right b-post__time_float_none_iphone">
                <a href="#c_<?=$data['id']?>" title="Ссылка на этот комментарий" id="link_anchor_<?=$data['id']?>" class="b-post__anchor b-post__anchor_margright_10" onclick="setDisplayAnchor(this)"></a>
                <? if($data['modified'] && $data['modified'] == $data['author']) { ?>
                    <img src="/images/ico-e-u.png" alt="Отредактировано пользователем" title="Внесены изменения <?=date('d.m.Y в H:i', strtotime($data['modified_time']))?>" />
                    <? } ?>
                <? if($data['modified'] && $data['modified'] != $data['author'] ) {
                    $moduser = ($data['is_permission']) ? " ({$data['mod_login']} : {$data['mod_uname']} {$data['mod_usurname']})" : "";
                    ?>
                    <img src="/images/ico-e-a.png" alt="<?=$data['access']['update']?>" title="<?=$data['access']['update']?> <?=$moduser?>: <?=date('d.m.Y в H:i', strtotime($data['modified_time']))?>" />
                <? } //if ?>
                <?=date('d.m.Y в H:i', strtotime($data['created_time']))?>
        </div>
        <div class="b-post__avatar b-post__avatar_margright_10"> <a href="/users/<?=$data['author_login']?>" class="b-post__link"><?=view_avatar_info($data['author_login'], $data['author_photo'], 1)?></a> </div>
        <div class="b-post__content b-post__content_margleft_60">
            <div class="b-username b-username_bold b-username_padbot_10"> <?=view_user3($author_user)?> <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_weight_normal b-layout__txt_lowercase b-layout__txt_padright_10">на сайте <?=ElapsedMnths(strtotime($data['author_reg_date']))?></span></div>
            <?php if($this->enableRating && $data['deleted'] === NULL && $author_user['is_team']!='t') { $rating_class = $msg['rating'] < 0 ? 'b-voting__mid_color_red' : ($msg['rating'] >= 1 ? 'b-voting__mid_color_green' : '') ;?>
            <div class="b-voting b-voting_float_right" id="rate_<?= $data['id'] ?>"> 
                <?php if($data['author'] == get_uid(false) || $this->_options['readonly'] || $this->_options['deny_vote']) { ?>
                    <a href="javascript:void(0)" class="b-button b-button_poll_plus b-button_poll_nopointer b-button_disabled b-voiting__right"></a> 
                    <a href="javascript:void(0)" class="b-button b-button_poll_minus b-button_poll_nopointer b-button_disabled b-voiting__left"></a>
                <?php } else {?>
                    <a href="javascript:void(0)" onclick="RateComment('<?= $this->_sname ?>', <?= $data['id'] ?>, 1, this)" class="b-button b-button_poll_plus normal_behavior <?= ( $data['user_rating']  == 1 || !get_uid(false)) ? 'b-button_poll_nopointer b-button_disabled' : 'b-button_active' ?> b-voiting__right"></a> 
                    <a href="javascript:void(0)" onclick="RateComment('<?= $this->_sname ?>', <?= $data['id'] ?>, -1, this)" class="b-button b-button_poll_minus normal_behavior <?= ( $data['user_rating']  == -1 || !get_uid(false)) ? 'b-button_poll_nopointer b-button_disabled' : 'b-button_active' ?> b-voiting__left"></a>
                <?php }?>
                <span class="b-voting__mid <?= $rating_class?>"><?= ($data['rating'] > 0 ? '+' : ''); ?><?= (int)$data['rating']; ?></span>
            </div>
            <?php }//if?>
            <?php if($actionRating == 'hide') { ?>
                <div class="b-post__txt b-post__txt_padbot_20 b-post__txt_color_a7a7a6">Комментарий скрыт. <a class="b-post__link b-post__link_dot_a7a7a6" href="javascript:void(0)" onClick="showComment(this)">Показать</a></div>
            <?php } ?>
            <div class="b-post__txt <?= ( ( $data['deleted'] !== NULL && $data['is_permission'] ) || $actionRating == 'blur' || $actionRating == 'hide' ? "b-post__txt_color_a7a7a6" : "" )?> <?= $msg['hiddenRating'] || $actionRating == 'hide' ?"b-post__txt_hide":""?> <?=$data['deleted'] !== NULL?"b-post__txt_color_b1":""?>">
                <?
                $sMsgText = ( $this->enableWysiwyg ) ? wysiwygLinkEncode($data['msgtext']) : $data['msgtext'];
                $sMsgText = ( $this->enableWysiwyg ) ? wysiwygLinkDecode($sMsgText) : $sMsgText;
                //$sMsgText = str_replace("<cut>", "[cut]", $sMsgText);
                $sMsgText = reformat( $sMsgText, $wordlength, 0, 0, 1, 25, $this->enableWysiwyg);
                $sMsgText = preg_replace("/(<code {1,}.*style {0,})=/imsU", "$1&#61;", $sMsgText);
                $sMsgText = preg_replace("/(onmouseover|onclick|ondblclick|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup {0,})=/imsU", "$1&#61;", $sMsgText);
                //$aMsgText = explode("[cut]", $sMsgText);
                //$sMsgText = $aMsgText[0];
                //$sTiser   = $aMsgText[1];  
                ?>
                <? if($data['deleted'] === NULL) { ?>
                    <?= $sMsgText?>
                    <? if ($sTiser) {?><br/>
                    <div>
                        <div><a href="javascript:void(0)" class="commentspoiler">Развернуть</a></div>
                        <div style="border: solid 1px #000;background-color: #FCFCFC; padding:5px;display:none" class="cat">
                            <?=$sTiser ?>
                        </div>
                    </div><script type="text/javascript">$$('a.commentspoiler').addEvent('click', switchCut);</script>
                    <?}?>
                    <? if($data['yt'] !== NULL) { ?>
                    <div class="added-video">
                        <?= show_video($data['id'], $data['yt']) ?>
                    </div>
                    <? } ?>

                    <? global $foto_alt; ?>
                    <? if($data['attach']) { ?>
                        <? 
                        if(isset($data['attach']['id'])) $data['attach'] = array($data['attach']);
                        foreach ( $data['attach'] as $attach ) {
                        $maxwidth = $this->_options['maxwidth']?$this->_options['maxwidth']-($data['level']*$this->_options['minus_level_width']):600;
                        echo '<div class = "flw_offer_attach b-page__desktop b-page__ipad">', viewattachLeft(null, $attach['fname'], $attach['path'], $file, 1000, $maxwidth, 307200, 0, $attach['small'] == 't' || $attach['small'] === null?1:0, 0, 0, "{$foto_alt} фото {$attach['fname']}"), '</div>';
                        echo '<div class = "b-page__iphone"><a class="b-layout__link" target="_blank" href="',WDCPREFIX,'/',$attach['path'],$attach['fname'],'">',$attach['fname'],'</a></div>';
                        }
                        ?>
                    <? } ?>
                    <? } elseif($data['is_permission']) {?>
                    <span class="b-post__txt b-post__txt_color_c10601">Удалено:</span> <?=$sMsgText?>
                        <?php if(get_uid(false) != $data['author']){ ?>
                            <br/>Удалил <?=$data['mod_login_del'] ?>: (<?=$data['mod_uname_del'] ?> <?=$data['mod_usurname_del'] ?>)
                        <?php }?>
                        <?php if (trim($data['reason'])) {?>
                            <div style="color:#ff0000">Причина: <?=$data['reason'] ?></div>
                        <?php }?>
                    <? } else { ?>
                        <?= ($data['deleted'] == $data['author'])?'Комментарий удален автором':$data['access']['delete'];?> <?=date('[d.m.Y в H:i]', strtotime($data['deleted_time']));?>
                        <?php if (trim($data['reason']) && ( $data['is_permission'] || get_uid(false) == $data['author'])) {?>
                            <div style="color:#ff0000">Причина: <?=$data['reason'] ?></div>
                        <?php }?>
                    <? } ?>
            </div>
            <?php if($msg['hiddenRating']) { ?>
            <div class="b-post__txt b-post__txt_color_a7a7a6">Комментарий скрыт</div>
            <?php } ?>
            <?php if($uid && $data['deleted'] === NULL && (!$data['author_is_banned'] || $data['is_permission'])) { ?>
            <div class="b-post__foot b-post__foot_clear_both">
                <ul class="b-post__links b-post__links_padtop_5">
                    <?php if($msg['hiddenRating']) { ?>
                    <li class="b-post__links-item b-post__links-item_padright_10"><a class="b-post__link b-post__link_dot_0f71c8 b-post__link_toggler" href="javascript:void(0)" onClick="showHiddenComment(this)">Показать</a></li>
                    <?php }//if?>
                    <? if (!$this->_options['readonly']) {?>
                    <li class="b-post__links-item b-post__links-item_padright_10 <?= $msg['hiddenRating']?"b-post__links-item_hide":""?>"><a class="b-post__link b-post__link_dot_0f71c8" href="javascript:void(0)" onclick="commentAddNew(this, SNAME)">Ответить</a></li>
                    <?} ?>
                    <?php if(!$this->_options['readonly'] && ($uid == $data['author'] || $data['is_permission'])) { ?>
                        <?php if(($data['is_permission'] >= 1 && $data['is_permission'] < 4) || $data['author'] == get_uid(false)){?>
                        <li class="b-post__links-item b-post__links-item_padright_10 <?= $msg['hiddenRating']?"b-post__links-item_hide":""?>"><a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="commentEditNew(this, SNAME, <?=$data['id']?>)">Редактировать</a></li>
                        <?php }?>
                        <li class="b-post__links-item b-post__links-item_padright_10 <?= $msg['hiddenRating']?"b-post__links-item_hide":""?>"><a class="b-post__link b-post__link_dot_c10601" href="?<?= url($_GET, array('cmtask' => 'delete', 'cmid' => $data['id'], 'token'=> $_SESSION['rand']))?>" onclick="return (confirm('Вы уверены?'));">Удалить</a></li>
                    <?php }//if?>
                    <?/* <li class="b-post__links-item b-post__links-item_padright_10"><a class="b-post__link b-post__link_dot_c10601" href="#2">Сделать предупреждение</a> — 21</li> */?>
                    <li class="b-post__links-item b-post__links-item_padright_10 <?= $msg['hiddenRating']?"b-post__links-item_hide":""?>"><a href="javascript:void(0)" class="cl-thread-toggle b-post__link b-post__link_dot_0f71c8" style="display:none;"><?=$is_hidden ? 'Развернуть ' : 'Свернуть '?> ветвь</a></li>
                </ul>
            </div>
            <?php } elseif ( $data['is_permission'] && (!$data['mod_access'] || $data['is_permission'] <= $data['mod_access']) ) {
                // дополнительное условие для возможности восстановления комментария
                // если комментарий удалил автор комментария, то никто больше не может его восстановить
                if ($data['author'] != $data['mod_uid_del']) { ?>
                    <div class="b-post__foot">
                        <ul class="b-post__links b-post__links_padtop_5">
                            <li class="b-post__links-item b-post__links-item_padright_10"><a class="b-post__link b-post__link_dot_c10601" href="?<?= url($_GET, array('cmtask' => 'restore', 'cmid' => $data['id'], 'token'=> $_SESSION['rand']))?>" onclick="return (confirm('Вы уверены?'));">Восстановить</a></li>
                        </ul>
                    </div>
                <? } ?>
            <?php } else {//elseif?>
            <div class="b-post__foot">
                <ul class="b-post__links b-post__links_padtop_5">
                    <li class="b-post__links-item b-post__links-item_padright_10 <?= $msg['hiddenRating']?"b-post__links-item_hide":""?>"><a href="javascript:void(0)" class="cl-thread-toggle b-post__link b-post__link_dot_0f71c8" style="display:none;"><?=$is_hidden ? 'Развернуть ' : 'Свернуть '?> ветвь</a></li>
                </ul>
            </div>
            <?php }?>
        </div>
        <div class="b-post__arrows">
            <? if($data['level']) { ?>
            <a href="#c_<?=$data['parent_id']?>" class="b-post__arrow b-post__arrow_up"></a>
            <? } ?>
            <a href="#c_3" class="b-post__arrow b-post__arrow_bot" style="display:none"></a>
        </div>
    </div>
</div>