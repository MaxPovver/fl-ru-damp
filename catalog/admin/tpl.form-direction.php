<?php if($is_save){?>
<div class="b-fon b-fon_width_full b-fon_padbot_17">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
        <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span><?=($msgtext ? $msgtext : 'Изменения были сохранены')?>
    </div>
</div>    
<?php }?>
<form method="POST" id="form_section" onsubmit="return false;">
    <input type="hidden" name="action" value="direction_save">
    <? if ($form_data['id']) { ?>
    <input type="hidden" name="id" value="<?= $form_data['id'] ?>">
    <? } ?>
    <div class="form-el">
        <label><strong>Название направления</strong></label>
        <div class="form-value "><input type="text" name="name_section" id="name_section" class="i-txt" value="<?= $form_data['dir_name']?>" onblur="xajax_setTranslit(this.value)"></div>
    </div>
    <div class="form-el">
        <label><strong>Название ссылки</strong></label>
        <div class="form-value "><input type="text" name="name_section_link" id="name_section_link" class="i-txt" value="<?= $form_data['name_section_link']?>"></div>
    </div>
    <div class="form-el">
        <label><strong>META Description</strong></label>
        <div class="form-value"><textarea name="meta_description" cols="20" rows="5"><?= $form_data['meta_description']?></textarea></div>
    </div>
    <div class="form-el">
        <label><strong>META Keywords</strong> (через запятую)</label>
        <div class="form-value form-key"><div class="b-input-hint"><div id="body_1" ><textarea name="meta_keywords" cols="20" rows="5" id="kword_se"><?= $form_data['meta_keywords']?></textarea></div></div>
				</div>
    </div>
    
    <label><strong>Содержимое страницы </strong></label>
    <div class="cl-form">
        <div class="cl-form-in">
            <textarea class="ckeditor" id="content" name="content" rows="5" cols="100"><?= $form_data['page_content']?></textarea>    
        </div>
    </div><!--cl-form-->
    <a class="btnr btnr-t" href="javascript:void(0)" onclick="xajax_saveDirectForm(getFormToArray('form_section'));"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Сохранить</span></span></span></a>
</form>