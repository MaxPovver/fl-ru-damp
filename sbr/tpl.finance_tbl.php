<span class="<?=($tbl['rez_type'] ? 'rez--itm'.$tbl['rez_type'] : '')?>" <?=($tbl['rez_type'] && !($tbl['rez_type'] & $rez_type) ? ' style="display:none"' : '')?>>
<?php if($setting['fon']) { ?>
<div class="b-fon b-fon_padbot_30"><div class="b-fon__body b-fon__body_pad_15 b-fon__body_bg_ffebbf">
<?php }//if?>
        
<? if($tbl_caption != '') { ?>        
<h3 class="b-layout__h3">
    <? if($setting['caption_expand']) {?>
        <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_0f71c8 finance-block block-<?= $setting['abbr_block']?>" href="javascript:void(0)"><?= $tbl_caption;?></a> <span class="b-layout__arrow"></span> &#160; 
        <span class="b-layouyt__txt b-layouyt__txt_weight_normal b-layout__txt_fontsize_11"><?= $setting['caption_descr']?></span>
    <? } else {//if?>
        <?= $tbl_caption;?>
    <? }//if?>
</h3>
<? }//if?>
<span class="<?= $setting['abbr_block']?>">
<?php $pos=0; foreach($tbl as $key=>$field) { if (!$field['name']) continue; $pos++;?>    
    <?php if($tbl_subheader['pos'] == $pos) {?>
    <h4 class="b-layout__h4">
        <?= $tbl_subheader['title']?>:
    </h4>
    <?php }//if?>
    <table class="b-layout__table <?= sbr_meta::getSelector('table', $setting['table'])?> <?=($field['rez_type'] ? 'rez--itm'.$field['rez_type'] : '')?>" <?=($field['rez_type'] && !($field['rez_type'] & $rez_type) ? ' style="display:none"' : '')?>>
        <tbody>

            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padright_10 <?= sbr_meta::getSelector('table', $setting['table'], 1)?>">
                    <div class="b-layout__txt b-layout__txt_padtop_5 b-page__desktop b-page__ipad">
                        <?= $field['name']?> 
                        <?php if($setting['name_descr'][$field['pos']]) {?>
                            <span class="b-layout__txt b-layout__txt_padright_5 b-layout__txt_float_right"><?= $setting['name_descr'][$field['pos']]?></span>
                        <?php }//if?>
                    </div>
                </td>
                <td class="b-layout__td ">
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-page__iphone">
                        <?= $field['name']?> 
                        <?php if($setting['name_descr'][$field['pos']]) {?>
                            <span class="b-layout__txt"><?= $setting['name_descr'][$field['pos']]?></span>
                        <?php }//if?>
                    </div>
                    
                    <?php if($setting['field'] == 'phone') { ?>
                    <div class="i-shadow">
                        <div class="b-shadow b-shadow_m b-shadow_top_0 b-shadow_left_310 b-shadow_hide auth_mob_alert">
                                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                                <div class="b-layout__txt b-layout__txt_padright_15 b-layout__txt_color_c4271f" id="auth_mob_alert_content">Необходимо активировать телефон</div>
                                            </div>
                            <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_20 b-shadow__icon_left_5"></span>
                        </div>                                
                    </div> 
                    <?php }//if?>
                    
                    <div class="b-combo <?= $setting['combo_css']?> <?=($field['rez_required'] ? ' rez--req'.$field['rez_required'] : '')?> <? /* ($field['rez_required'] && ($field['rez_required'] & $rez_type) ? ' form-imp' : '') */?>">
                        <?php 
                        $addit_html = "";
                        switch($field['type']) {
                            case 'small':
                            case 'number':
                                $length_lable   = 21;
                                $selector_input = "b-combo__input b-combo__input_width_170 " . sbr_meta::getSelector('field', $setting['field']);
                                break;
                            case 'date':
                                $length_lable   = 17;
                                $selector_input = "b-combo__input b-combo__input_width_170 b-combo__input_calendar b-combo__input_arrow_yes date_format_use_dot use_past_date no_set_date_on_load year_min_limit_1900 ".sbr_meta::getSelector('field', $setting['field']);
                                $addit_html     = '<span class="b-combo__arrow-date"></span>';
                                break;
                            default:
                            case 'default':
                                $length_lable   = 59;
                                $selector_input = "b-combo__input b-combo__input_width_400 b-combo__input_width_280_iphone " . sbr_meta::getSelector('field', $setting['field']);
                                break;
                        }
                        
                        $bDisabled = $setting['disabled'] && (empty($setting['disabled_fields']) || in_array($key, $setting['disabled_fields']));
                        
                        if ( !empty($bDisabled) ) {
                            $selector_input .= ' b-combo__input_disabled';
                        }
                        ?>
                        <?php /*<label class="b-input-hint__label b-input-hint__label_overflow_hidden b-input-hint__label_width_397 <?= html_attr($reqvs[$form_type][$key]) != '' ? "b-input-hint__label_hide" : ""?>" for="i<?=$field['idname']?>" id="example-<?=$field['idname']?>"></label>*/ ?>
                        <div class="<?= $selector_input?>">
                            <input <?=empty($bDisabled)? '': 'disabled'?> type="text" id="i<?=$field['idname']?>" title="<?=$field['example']?>" class="b-combo__input-text  b-combo__input_nohintblur <?= sbr_meta::getSelector('field', $setting['field'], 1)?>" name="ft<?=$form_type?>[<?=$key?>]" size="80" value="<?=html_attr($reqvs[$form_type][$key])?>" maxlength="<?= $reqvs['rez_type']==2 && $key=='bank_rs' ? 25 : $field['maxlength']?>" 
                                                                                    onfocus="<?= $setting['field'] == 'phone'? "savePhoneChage(this);" : ""?>" 
                                                                                    onblur="if(this.value.length == 0) <?= $setting['field'] == 'phone'? "savePhoneChage(this);" : ""?>"
                                                                                    placeholder="<?= strlen($field['example']) <= $length_lable && in_array($field['pos'], array_keys($setting['subdescr'])) && $setting['subdescr'][$field['pos']] != '' ? $field['example'] : ''?>">
                            <?= $addit_html;?>
                        </div>
                    </div> &#160;&#160;
                        <?php if(($setting['field'] == 'phone' && !$setting['auth'] && $reqvs[$form_type][$key] != '' && !$bDisabled)||($setting['field'] == 'phone' && $setting['auth'])) { ?>
                        <div class="b-layout b-layout_inline-block b-layout_padtop_10_iphone b-layout_block_iphone">
                            <?php if($setting['field'] == 'phone' && !$setting['auth'] && $reqvs[$form_type][$key] != '' && !$bDisabled) { ?>
                            <span class="c_sms_main">
                                <a href="javascript:void(0)"class="b-button b-button_flat b-button_flat_grey" data-phone="<?=html_attr($reqvs[$form_type][$key])?>">Активировать</a>
                            </span>
                            <?php } elseif($setting['field'] == 'phone' && $setting['auth']) {//if?>
                            <div class="b-layout__txt b-layout__txt_padtop_7 b-layout__txt_nowrap b-layout__txt_inline-block"><span class="b-icon b-icon_sbr_gok b-icon_top_2"></span>Активирован <a href="/users/<?=$_SESSION["login"] ?>/setup/main/" target="_blank" >Изменить номер телефона</a></div>
                            <?php }//elseif?>
                        </div>
                        <?php }//elseif?>
                    
                    <div class="b-layout__txt b-layout__txt_fontsize_11">
                        <? if(in_array($field['pos'], array_keys($setting['subdescr'])) && $setting['subdescr'][$field['pos']] != '') { ?>
                            <?= $setting['subdescr'][$field['pos']]?>
                        <? } elseif($field['example'] && $setting['field'] != 'phone') { //if?>
                            Например:  <?= $field['example']; ?>
                        <? }//else?>
                        <?= $tbl_header ?>
                    </div>
                    <?= ($setting['file'][$field['pos']] ? $setting['file'][$field['pos']] : "" ) ?>
                </td>
            </tr>
        </tbody>
    </table>
<?php }//foreach?>
    <?php if($setting['abbr_block'] == 'docs') { ?>
				<div class="b-check b-check_padleft_185 b-check_padbot_15">
					<input type="checkbox" value="1" name="is_agree_view_doc" id="is_agree_view_doc" class="b-check__input" <?= $reqvs['is_agree_view_sbr'] == 't' ? 'checked="checked"' : ''?>>
					<label for="is_agree_view_doc" class="b-check__label b-check__label_fontsize_13">&nbsp;Разрешаю использовать мои паспортные данные в документах сервиса &laquo;Безопасная Сделка&raquo;</label>
				</div>
    <?php }//if?>
</span>
<?php if($setting['fon']) { ?>        
</div></div>
<?php }//if?>
</span>