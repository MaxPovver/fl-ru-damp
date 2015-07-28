<form id="sbr_op_form">
    <input type="hidden" id="feedback_id" value="<?= $op['id'];?>"/>
    <input type="hidden" id="stage_id" value="<?= $op['stage_id'];?>"/>
    <input type="hidden" id="login" value="<?=$login_user?>"/>
    <div class="b-username b-username_bold b-username_padbot_10">Ваша <br class="b-page__iphone">рекомендация</div>			
    <div class="b-post__voice-item b-post__voice-item_positive <?= $op['rating'] == 1 ? 'b-post__voice-item_current' : '' ;?>">
        <a class="b-post__link" href="javascript:void(0)" onclick="setVote(1)"><span class="b-post__voice b-post__voice_apositive"></span><span class="b-post__inner-link">Положительная</span></a>
        <input type="radio" value="1" name="ops_type" <?= $op['rating'] == 1 ? 'checked="checked"' : '' ;?> style="display:none"/>
    </div>
    <div class="b-post__voice-item b-post__voice-item_neutral <?= $op['rating'] == 0 ? 'b-post__voice-item_current' : '' ;?>">
        <a class="b-post__link " href="javascript:void(0)" onclick="setVote(0)"><span class="b-post__voice b-post__voice_anegative"></span><span class="b-post__inner-link">Нейтральная</span></a>
        <input type="radio" value="0" name="ops_type" <?= $op['rating'] == 0 ? 'checked="checked"' : '' ;?> style="display:none"/>
    </div>
    <div class="b-post__voice-item b-post__voice-item_negative <?= $op['rating'] == -1 ? 'b-post__voice-item_current' : '' ;?>">
        <a class="b-post__link " href="javascript:void(0)" onclick="setVote(-1)"><span class="b-post__voice b-post__voice_aneutral"></span><span class="b-post__inner-link">Отрицательная</span></a>
        <input type="radio" value="-1" name="ops_type" <?= $op['rating'] == -1 ? 'checked="checked"' : '' ;?> style="display:none"/>
    </div>
    <? if(hasPermissions("sbr")) { ?>
    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_150">
                <div class="b-layout__txt b-layout__txt_padtop_5">Название сделки</div>
            </td>
            <td class="b-layout__right">
                <div class="b-combo">
                    <div class="b-combo__input">
                        <input type="text" value="<?=html_attr($op['sbr_name'])?>" maxlength="<?=sbr::NAME_LENGTH?>" size="80" name="sbr_name" class="b-combo__input-text" id="sbr_name">
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_150">
                <div class="b-layout__txt b-layout__txt_padtop_5">Название этапа</div>
            </td>
            <td class="b-layout__right">
                <div class="b-combo">
                    <div class="b-combo__input">
                        <input type="text" value="<?=html_attr($op['stage_name'])?>" maxlength="<?=sbr::NAME_LENGTH?>" size="80" name="stage_name" class="b-combo__input-text" id="stage_name">
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <? } //if?>
	<div class="b-textarea b-textarea_padtop_10">
	   <textarea rows="5" cols="80" name="" id="sbr_op_text" class="b-textarea__textarea  b-textarea__textarea__height_50"><?= $op['descr'];?></textarea>
	</div>
	<div class="form-btn">
        <a href="javascript:void(0)" onclick="submitEditSBROp(<?= hasPermissions("sbr") ? "$('sbr_name').get('value'), $('stage_name').get('value')" : ""?>)" class="btnr btnr-t"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Сохранить</span></span></span></a>&nbsp;&nbsp;&nbsp;&nbsp; 
        <a href="javascript:void(0);" onclick="reverseForm(<?= (int)$op['id']?>);" class="lnk-dot-666">Отменить</a>
    </div>   
</form>