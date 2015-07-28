<?
$opID = $isFeedback ? $comment['feedback_id'] : $comment['opinion_id'];
$prefix = $isFeedback ? 'feedback_' : '';
?>
<div class="b-post__body b-post__body_pad_10_15_20" id="<?= $prefix ?>comment_content_<?= $opID ?>">
    <div class="b-post__body b-post__body_padleft_15 b-post__body_bordleft_fd6c30">
        <div class="b-post__avatar">
            <a class="b-post__link" href="/users/<?=$aUser['login']?>/"><?= view_avatar($aUser['login'], $aUser['photo'], 1, 1, 'b-post__userpic') ?></a>
        </div>
        <div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
            <div class="b-post__time b-post__time_float_right"> 
                <a class="b-post__anchor b-post__anchor_margright_10"  onclick="hlAnchor('c', <?= $opID ?>);" href="#c_<?= $opID ?>" title="Ссылка на эту рекомендацию"></a> <?= date('d.m.Y в H:i', strtotime($comment['date_create']))?> 
            </div>
            <div class="b-username b-username_padbot_5 b-username_bold">
                <?= view_user3($aUser)?>
            </div>
            <div class="b-post__txt b-post__txt_padtop_5" id="<?= $prefix ?>comment_text_<?= $opID ?>">
                <?= reformat($comment['comment'], 40)?>
            </div>
            <?php if($comment['user_id'] == $_SESSION['uid'] || hasPermissions('users')) {?>
            <div class="b-post__foot b-post__foot_padtop_10 ">
                <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" id="<?= $prefix ?>opinion_btn_edit_comment_<?= $opID ?>" onclick="if(!this.disabled) { this.disabled = true; xajax_AddOpComentForm('<?= $opID ?>', '<?=$ops_type?>' <?= $isFeedback ? ', true' : '' ?>); } return false;">Редактировать</a> &#160;&#160; 
                <a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0)" onclick="if (confirm('Вы действительно хотите удалить комментарий?')) xajax_DeleteOpinionComm('<?= $opID ?>', '<?=$comment['id']?>', '<?=$ops_type?>' <?= $isFeedback ? ', true' : '' ?>); return false;">Удалить</a>
            </div>
            <?php }//if?>
        </div>
    </div>
</div>