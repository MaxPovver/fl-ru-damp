<?php if($is_save){?>
<div class="b-fon b-fon_width_full b-fon_padbot_17">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
        <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span><span><?=($msgtext ? $msgtext : 'Изменения были сохранены')?></span>
    </div>
</div>    
<?php }?>
<form method="POST" id="form_section" onsubmit="return false;">
    <input type="hidden" name="action" value="main"> 
    <div class="form-el">
        <label><strong>Регион</strong></label>
        <div class="form-value ">
            <?php if($seo->subdomain['id'] > 0) { ?>
            <input type="hidden" name="subdomain" value="<?= $seo->subdomain['id']?>">
            <?php }?>
            <select name="subdomain" onchange="xajax_loadMainForm(this.value)">
                <?php if($subdomains) foreach($subdomains as $key=>$subdomain) { ?>
                <option value="<?= $subdomain['id']?>" <?= ($seo->subdomain['id'] == $subdomain['id'])?'selected="selected"':''?>><?= $subdomain['name_subdomain']?></option>
                <?php } //foreach?>
            </select>
        </div>
    </div>
    <div class="form-el">
        <label><strong>META Description</strong></label>
        <div class="form-value"><textarea name="meta_description" cols="20" rows="5"><?= $seo->subdomain['meta_description']?></textarea></div>
    </div>
    <div class="form-el">
        <label><strong>META Keywords</strong> (через запятую)</label>
        <div class="form-value form-key"><div class="b-input-hint"><div id="body_1" ><textarea name="meta_keywords" cols="20" rows="5" id="kword_se"><?= $seo->subdomain['meta_keywords']?></textarea></div></div>
				</div>
    </div>
    
    <label><strong>Содержимое страницы </strong>(до динамического контента)</label>
    <div class="cl-form">
        <div class="cl-form-in">
            <textarea class="ckeditor" id="content" name="content" rows="5" cols="100"><?= $seo->subdomain['content']?></textarea>    
        </div>
    </div><!--cl-form-->
    <a class="btnr btnr-t" href="javascript:void(0)" onclick="xajax_updateContentSubdomain(getFormToArray('form_section'));"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Сохранить</span></span></span></a>
</form>