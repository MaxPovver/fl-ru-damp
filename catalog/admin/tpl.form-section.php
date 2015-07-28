<?php $countries = $seo->getCountries(); ?>
<form method="POST" id="form_section" action="/" onsubmit="return false;">
    <?php if($is_edit) { ?>
    <input type="hidden" name="id" value="<?= $section_id?>">
    <input type="hidden" id="old_parent" name="old_parent" value="<?= (int)$parent_section?>">
    <input type="hidden" name="old_position" value="<?= (int)$form_section['pos_num']?>">
    <input type="hidden" name="bind" value="<?= (int)$form_section['bind']?>">
    <input type="hidden" id="old_direction" name="old_direction" value="<?= (int)$direct_id?>">
    <?php } //if?>
    <? if ($direct_id) { ?>
    <input type="hidden" name="direction" value="<?= (int)$direct_id?>">
    <? } ?>
    <input type="hidden" name="is_subcategory" value="<?=($is_subcategory ? '1' : '0')?>">
                <? if($is_edit) { ?>
                <div class="form-el">
                  <label><strong>Направление</strong></label>
                  <div class="form-value ">
                    <select id="s_direction" name="direction" <?= $parent_section ? 'disabled': '' ?> onChange="if(this.value != $('old_direction').get('value')) { $('new_position').set('disabled', true); } else { $('new_position').set('disabled', false); }">
<!--                        <option value="0">Не выбрано</option>-->
                        <?php if($directions) foreach($directions as $row) { ?>
                        <option value="<?= $row['id']?>" <?= ($form_section['direct_id'] == $row['id'])?'selected="selected"':''?>><?= $row['dir_name']?></option>
                        <?php } //foreach?>
                    </select>
                  </div>
                </div>
                <? } ?>
    
                <?php if($parent_section) { ?>
                <div class="form-el">
                  <label><strong>Регион</strong></label>
                  <div class="form-value ">
                    <?php if($seo->subdomain['id'] > 0) { ?>
                    <input type="hidden" name="subdomain" value="<?= $seo->subdomain['id']?>">
                    <?php }?>
                    <select name="subdomain" <?= ($seo->subdomain['id'] > 0)?"disabled":""?>>
                      <option value="-1" <?=(($subdomain_id == -1)?'selected="selected"':'')?>>Все</option>
                      <?php
                      foreach($countries as $country) {
                        $country_options = "<option value=''>{$country['country_name']}</option>";
                        foreach($subdomains as $key=>$subdomain) {
                          if($subdomain['country_id']!=$country['id']) continue;
                          $country_options .= "<option value='{$subdomain['id']}' ".(($subdomain_id == $subdomain['id'])?'selected="selected"':'').">&nbsp;&nbsp;{$subdomain['name_subdomain']}</option>";
                        }
                        $subdomain_options .= $country_options;
                      }
                      ?>
                      <?=$subdomain_options?>

                    </select>
                  </div>
                </div>
                <?php } //if?>
                <?php if($parent_section) { ?>
                <div class="form-el">
                  <label><strong>Раздел</strong></label>
                  <div class="form-group">
                    <div class="form-value form-select">
                      <select style="float:left" name="parent" onChange="if($('old_parent')) { if(this.value != $('old_parent').get('value')) { $('new_position').set('disabled', true); } } xajax_getPositions(this.value, '<?=$form_section['direct_id']?>');">
                        <?php if($sections) foreach($sections as $section) { if($parent_section == $section['id']) $parent_pos_num = $section['pos_num'];?>
                        <option value="<?= $section['id']?>" <?= ($parent_section == $section['id']?'selected="selected"':'');?>><?=$section['name_section']?></option>
                        <?php } //foreach ?>
                      </select> 
											<span style="float:left; padding-top:2px;">&nbsp;&nbsp;<input type="checkbox" name="is_draft" value="<?= ($form_section['is_draft'] == 't'?1:0)?>" <?= ($form_section['is_draft'] == 't'?'checked="checked"':'')?> onclick="if(this.checked) {this.value = 1;} else { this.value = 0}"> В черновик</span>
                        
                    </div>
                  </div>
                </div>
                <?php } //if?>
    
                <div class="form-el">
                  <label><strong>Название <?=$parent_section?'под':''?>раздела</strong></label>
                  <div class="form-group">
                    <div class="form-value "><input type="text" name="name_section" id="name_section" class="i-txt" value="<?= $form_section['name_section']?>" onblur="xajax_setTranslit(this.value)"></div>
                    <div class="form-value form-select">
                        <select name="new_position" id="new_position" <?= ($disabled_position?"disabled":"")?>>
                            <?php for($p=1;$p<=$positions;$p++) { if($form_section['pos_num'] == $p) { $selected='selected'; $is_selected=true; } else { $selected=''; } ?>
                            <option value="<?=$p?>" <?=$selected?>><?= ($parent_pos_num ? $parent_pos_num."." : "")?><?=$p?></option>
                            <?php }//for?>
                            <?php if($positions) { ?>
                              <option value="-1" <?=($is_selected==true ? '' : 'selected')?>>Последний</options>
                            <?php } else { ?>
                              <option value="1"><?= ($parent_pos_num ? $parent_pos_num."." : "")?>1</option>
                            <?php } ?>
                        </select>
                    </div>
                  </div>  
                </div>
                <div class="form-el">
                  <label><strong>Название ссылки <?=$parent_section?'под':''?>раздела</strong></label>
                  <div class="form-value "><input type="text" name="name_section_link" id="name_section_link" class="i-txt" value="<?= $form_section['name_section_link']?>"></div>
                </div>
                
                <div class="form-el">
                  <label><strong>META Description</strong></label>
                  <div class="form-value"><textarea name="meta_description" cols="20" rows="5"><?= $form_section['meta_description']?></textarea></div>
                </div>
                <div class="form-el">
                  <label><strong>META Keywords</strong> (через запятую)</label>
                  <div class="form-value form-key"><div class="b-input-hint"><div id="body_1" ><textarea name="meta_keywords" cols="20" rows="5" id="kword_se"><?= $form_section['meta_keywords']?></textarea></div></div></div>
                </div>
                
                <label><strong>Содержимое страницы </strong>(до динамического контента)</label>
                <div class="cl-form">
                  <div class="cl-form-in">
                    <textarea class="ckeditor" id="content_before" name="content_before" rows="5" cols="100"><?= $form_section['content_before']?></textarea>    
                  </div>
                </div><!--cl-form-->
    
                <label><strong>Содержимое страницы </strong>(после динамического контента)</label>
                <div class="cl-form">
                  <div class="cl-form-in">
                    <textarea class="ckeditor" id="content_after" name="content_after" rows="5" cols="100"><?= $form_section['content_after']?></textarea> 
                  </div>
                </div><!--cl-form-->
                <a class="btnr btnr-t" href="javascript:void(0)" onclick="xajax_createSection(getFormToArray('form_section'), '<?= $is_edit?"update":"create"?>');"><span class="btn-lc"><span class="btn-m"><span class="btn-txt"><?= $is_edit?"Изменить":"Создать"?> <?=($is_subcategory ? 'подраздел' : 'раздел')?></span></span></span></a>
</form>