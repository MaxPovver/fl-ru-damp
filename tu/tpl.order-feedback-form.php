<form id="sbr_op_form">
    <input type="hidden" name="feedback_id" value="<?= $id ?>"/>
    <?php if(isset($hash)){ ?>
    <input type="hidden" name="hash" value="<?php echo $hash ?>" />
    <?php } ?>
    <div class="b-username b-username_bold b-username_padbot_10">Ваш отзыв</div>
    <div class="b-textarea b-textarea_padtop_10">
        <textarea rows="5" cols="80" name="feedback" id="sbr_op_text" class="b-textarea__textarea  b-textarea__textarea__height_50"><?= $feedback ?></textarea>
    </div>
    <div class="b-buttons b-buttons_padtop_10">
        <a href="javascript:void(0);" onclick="submitTservicesOrdersFeedback(this);" class="b-button b-button_flat b-button_flat_green">Сохранить</a>
        &nbsp;&nbsp;&nbsp;&nbsp; 
        <a href="javascript:void(0);" onclick="reverseForm('<?= $id ?>-2');" class="b-buttons__link">Отменить</a>
    </div>
    <input type="hidden" name="u_token_key" value="<?=@$_SESSION['rand']?>" />
</form>