<?
/*if (!$stop_words) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php');
    $stop_words = new stop_words(hasPermissions('communes'));
}*/
?>
<script type="text/javascript">
    function __commDT(b,t,e,m,p,o,s,f)
    {
        if(warning(1)) {
            var tc=document.getElementById('idThCnt');
            xajax_DeleteTopic(b,t,e,m,p,o,s,f,(tc?tc.innerHTML:0),<?= $commune_id;?>);
        }
    }
</script>
<div class="b-post b-post_padbot_20">
    <div class="b-post__body b-post__body_bordbot_solid_dfedcf  b-post__body_padbot_20">
        <div class="b-post__time b-post__time_float_right b-page__desktop b-page__ipad">
            <?=date("d.m.Y в H:i", $created_time) ?>
        </div>
        <div class="b-post__avatar b-post__avatar_margright_10">
            <? seo_start($ajax_view); ?>
                <a class="b-post__link" href="/users/<?=$top['user_login']?>/">
                    <?= view_avatar_info($top['user_login'], $top['user_photo'], 1, 0, 0,'b-post__userpic') ?>
                </a>
            <?= seo_end(false, $ajax_view); ?>
        </div>
        <div class="b-post__content b-post__content_margleft_60 i-button">
            <div class="b-username b-username_bold b-username_padbot_10">
                <?// seo_start($ajax_view); ?>
                <?= __commPrntUsrInfo($top, 'user_', '', '', false, $ajax_view); ?>
                <?php 
                if($top['modified_id']){
                    $alt = '';
                    $img_suf = 'a';
                    if($top['modified_id']==$top['user_id']){
                        $alt = 'внесены изменения: ';
                        $img_suf = 'u';
                    }else if($top['modified_id'] == $top['commune_author_id']){
                        $alt = 'Отредактировано создателем сообщества ';
                    }else if($top['modified_by_commune_admin']){
                        $alt = 'Отредактировано администратором сообщества ';
                    }else { 
                        $alt = 'Отредактировано модератором ';
                    }
                    if($mod & commune::MOD_MODER){
                        $alt .= ' ( '.$top['modified_login'].' : ' . $top['modified_usurname'] . ' ' . $top['modified_uname'].' ) ';
                    } 
                    $alt .= dateFormat("[d.m.Y | H:i]",$top['modified_time']);
                    ?>
                    <img src="/images/ico-e-<?= $img_suf;?>.png" alt="" title="<?= $alt;?>" style="vertical-align:top; position:relative; top:2px;"/>
                <? } 
                $user_data = commune::GetUserCommuneRel($top['commune_id'],$top['user_id']);
                if($is_moder){
                    if($top['member_is_banned'] || $top['user_is_banned'] || $user_data['is_banned']){ ?>
                        <p><strong style="background: #F2A5A5; paddong: 4px">Пользователь забанен<?php echo $user_data['is_banned'] ? ' в сообществе' : ''?></strong></p>
                    <?php } 

                } ?>
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_weight_normal b-layout__txt_lowercase b-layout__txt_padright_10">на сайте <?=ElapsedMnths(strtotime($top['reg_date']))?></span>
                <?//= seo_end(false, $ajax_view); ?>
            </div>
            <div id="topicRate_<?= $top['id'];?>">
                <?= __commPrntTopicRating($top, $mod);?>
            </div>
<?php if ($_GET['site'] != 'Topic') { ?>
            <h3 class="b-post__title b-post__title_padbot_15 <?= $actionRating == 'blur' ? 'b-post__title_color_a7a7a6' : ''; ?>">
                <?php if($top['is_private'] == 't') { ?>
                <img src="/images/icons/eye-hidden.png" alt="Скрытый пост" title="Скрытый пост">&nbsp;
                <?php }//if?>
                <?php $sTitle   = /*$top['moderator_status'] === '0' ? $stop_words->replace($top['title']) :*/ $top['title']; ?>
                <? if ($site === 'Topic') { ?>
                    <?= reformat2($sTitle, $title_max, 0, 1) ?>
                <? } else { ?>
                    <?if($top['pos']) {?><img src="/images/tp-w.gif" alt="" style=""><?}?><a class="b-post__link" href='<?=getFriendlyURL('commune', $msg_id)?><?= ($page>1?'?bp='.$page : '')?>'><?= reformat2($sTitle, $title_max, 0, 1) ?></a>
                <? } ?>
                
            </h3>
<?php } ?>
            <?// seo_start(!$hideInJS)?>
            <?//Сообщение поста?>
            <? if (intval($top["deleted_id"])) {
                $datetime = explode(" ", $top["deleted_time"]);
                $date = $datetime[0];
                $date = explode("-", $date);
                $date = join(".",  array_reverse($date) );
                $time = $datetime[1];
                $time = explode(":", $time);
                $time = $time[0] .':'.$time[1];
                $user_entity = 'модератором';
                if ($top["commune_author_id"] == $top["deleted_id"]) {
                    $user_entity = 'администратором сообщества';
                }
                if ($top["user_id"] == $top["deleted_id"]) {
                    $user_entity = 'автором темы';
                }
            ?><div class="b-post__moderator_info">
                  <span class="b-post__moderator_info_red">Удалено <?=$user_entity ?> [<?=$top["modified_login"] ?>] <?=$top["modified_uname"] ?> <?=$top["modified_usurname"] ?></span> <span class="b-post__moderator_info_gray">[<?=$date ?> | <?=$time ?>]</span>
              </div><?
               } ?>
            <div class="b-post__txt <?=(intval($top["deleted_id"])?'b-post__deleted_txt':'') ?> <?= $actionRating == 'blur' ? "b-post__txt_opacity_3" : ""?>">                
                <?php $sMessage = /*$top['moderator_status'] === '0' ? $stop_words->replace($top['msgtext']) :*/ $top['msgtext']; ?>
                <?php
                if($site != 'Topic') {
                      $sMessage = str_replace("<cut>", "[cut]", $sMessage);
                      $aMessage = explode("[cut]", $sMessage);
                      $sMessage = $aMessage[0];
                      $tiser    = $aMessage[1];
                } else {
                    $sMessage = str_replace("<cut>", "<br/>", $sMessage);
                }
                
                // закрываем теги для сообществ перенесенных из блогов
                if (in_array($top['commune_id'], array(5000, 5001))) {
                    $sMessage = close_tags2($sMessage, 'a,p,s,i,b,h1,h2,h3,h4,h5,h6');
                }
                ?>
                <?= reformat($sMessage, $msgtext_max, ($site == 'Topic' ? 0 : 1), -($top['user_is_chuck'] == 't'), 0, 25, true); if ($tiser) {?>
                <br/><?php if($_GET['site'] != 'Topic') {?><a href="<?=getFriendlyURL('commune', $msg_id)?>">Подробнее</a>                               
                         <? }else {?>
                         <div>
                        <div><a href="javascript:void(0)" class="commentspoiler">Развернуть</a></div>
                        <div style="border: solid 1px #000;background-color: #FCFCFC; padding:5px;display:none" class="cat">
                            <?=$tiser ?>
                        </div>
                        </div><script type="text/javascript">$$('a.commentspoiler').addEvent('click', switchCut);</script>
                         <? }?>
                <?}?>
            </div>
            <? include(TPL_COMMUNE_PATH.'/poll.php');?>
            <?= (($top['youtube_link']) ? ("<div style='padding-top: 20px'>" . show_video($top['id'], $top['youtube_link']) . "</div>") : "") ?>
            
            <?php if(is_array($top['attach']) && count($top['attach'])) { 
                if ($top['cnt_files'] > 1 && $_GET['site'] != 'Topic') {
                    $top['attach'] = array($top['attach'][0]);
                }
                ?>
                <div class="attachments attachments-p">
                    <?php foreach ($top['attach'] as $attach) {
                        $att_ext = CFile::getext($attach['fname']);
                        $str = '';
                        //$str = viewattachLeft($top['user_login'], $attach['fname'], 'upload', $file, commune::MSG_IMAGE_MAX_HEIGHT, commune::_MSG_IMAGE_MAX_WIDTH, commune::MSG_IMAGE_MAX_SIZE, !($attach['small'] == 't'), (int) ($attach['small'] == 't'));
                        $is_tn = (int)($attach['small'] == 't');
                        $aData = getAttachDisplayData( null, $attach['fname'], $attach['path'], commune::MSG_IMAGE_MAX_HEIGHT, commune::_MSG_IMAGE_MAX_WIDTH, commune::MSG_IMAGE_MAX_SIZE, $is_tn );
                        if ( $aData && $aData['success'] ) {
                            if ( $aData['file_mode'] || $aData['virus_flag'] || $att_ext == "swf") { // Добавил проверку на swf потому что в сообществах и блогах по swf всегда ссылка
                                $str = viewattachLeft( null, $attach['fname'], $attach['path'], $file, 0, 0, 0, 0, 0, 0, $nn );
                                //seo_start();
                                echo '<div class = "flw_offer_attach">', $str, '</div>';
                                //print seo_end();
                            } 
                            else {
                                $cur_foto_alt = $foto_alt." фото ".$attach['fname'];
                                if ( $is_tn )
                                    $str = "<div align=\"center\"><a href=\"".WDCPREFIX . '/' . $attach['path'] . $attach['fname']."\" target=\"_blank\" alt=\"".$cur_foto_alt."\" title=\"".$cur_foto_alt."\"><img src=\"".WDCPREFIX.'/'. $attach['path'] .$aData['file_name']."\" alt=\"".$cur_foto_alt."\" title=\"".$cur_foto_alt."\" width=\"{$aData['img_width']}\" height=\"{$aData['img_height']}\" /></a></div>";
                                else 
                                    $str = "<div align=\"center\"><img src=\"".WDCPREFIX. '/' . $attach['path'].$aData['file_name'] . "\" alt=\"".$cur_foto_alt."\" title=\"".$cur_foto_alt."\" width=\"{$aData['img_width']}\" height=\"{$aData['img_height']}\" /></div>";
                                print("<br/><br/>" . $str . "<br/>");
                            }
                        }
                        if ($top['cnt_files'] > 1 && $_GET['site'] != 'Topic') {
                            seo_start();
                            ?>
                            <br/><a href='<?=getFriendlyURL('commune', $msg_id)?><?= ($om ? '?om='.$om : '') ?>' ><b><?=commune::ShowMoreAttaches($top['cnt_files'])?></b></a>
                            <? print seo_end();
                        } 
                    } ?>
                </div>
            <?php } ?>
            <?//= seo_end(false, !$hideInJS)?>
           
            <div id="theme-reason-<?=$top['theme_id']?>">
                <?php if ( $top['is_blocked_s'] == 't' || $top['is_blocked_c'] == 't' ) {
                    $blocked_time = $top['is_blocked_s'] == 't' ? $top['blocked_time_s'] : $top['blocked_time_c'];
                    $moder_login  = $top['is_blocked_s'] == 't' ? $top['admin_login_s'] : $top['admin_login_c'];
                    $moder_name   = $top['is_blocked_s'] == 't' ? $top['admin_uname_s'] . ' '. $top['admin_usurname_s'] : $top['admin_uname_c'] . ' '. $top['admin_usurname_c'];
                    $moder_type   = $mod & ( commune::MOD_ADMIN | commune::MOD_MODER ) ? ($top['is_blocked_s'] == 't' ? ' администратор сайта' : ' администратор сообщества') : '';
                ?>
                <div class='b-fon b-fon_width_full b-fon_padtop_20'>
                    <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_padleft_30 b-fon__body_bg_ff6d2d">
                        <span class="b-fon__attent_white"></span>
                        <span class="b-fon__txt b-fon__txt_bold">Пост заблокирован</span><?php if ($top['is_blocked_s'] == 't') { ?><?= ': ' . str_replace("\n", "<br>", ($top['blocked_reason_s'])) ?><?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
                    
            <div id="warnreason-<?=$top['id']?>" style="display: none">&nbsp;</div>
            
            
            
            <div id="idEditCommentForm_<?= $top['id'];?>" style="margin-bottom:20px">
                <a name="errorp"></a>
                <a name="op"></a>   
                <? if(isset($alert)) { echo __commPrntCommentForm($commune_id, $om, 0, 'Edit.post', $top['id'], $msg_id, $msg_id, $_POST, $alert, 'Topic', $mod); } ?>
            </div>
        </div>
            
        <div class="b-post__foot b-post__foot_clear_both">
            <? if ($site === 'Topic') { ?>
                <? if(!commune::isBannedCommune($mod)) include(TPL_COMMUNE_PATH.'/admin_bar.php');?>
                <div class="b-free-share">
                  <?= ViewSocialButtons('commune_topic', $top['title']);?>
                </div>                                                
            <? } else { ?>
                <ul class="b-post__links b-post__links_float_right">
                    <?php if($top['title']!='')  { seo_start($ajax_view); } ?>
                        <? include(TPL_COMMUNE_PATH.'/tpl.user_bar.php');?>
                    <?php if($top['title']!='')  {
                        print seo_end(false, $ajax_view);
                    } ?>
                </ul>
                <? if(!commune::isBannedCommune($mod)) include(TPL_COMMUNE_PATH.'/admin_bar.php');?>
            <? } ?>
        </div>
        <div class="b-post__time b-page__iphone">
            <?=date("d.m.Y в H:i", $created_time) ?>
        </div>
    </div><!-- конец топика -->
</div>
<style type="text/css">.msie .b-icon__ver{ position:relative; top:2px}</style>