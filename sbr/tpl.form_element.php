<?php

    $rezItmClass = '';
    $isVisible = false;
    
    if (isset($tbl['rez_type_new']) && !empty($tbl['rez_type_new'])):
        $rezItmClass = 'rez--itm' . implode(' rez--itm', $tbl['rez_type_new']);
        $isVisible = in_array($rez_type, $tbl['rez_type_new']);
    else:
        $rezItmClass = ($tbl['rez_type'])?"rez--itm{$tbl['rez_type']}":'';
    endif;
    
    $isVisible = !($tbl['rez_type'] && !($tbl['rez_type'] & $rez_type)) || $isVisible;
    
    $isStatic = isset($setting['static']) && $setting['static'] == true;
    
?>

<span class="<?=$rezItmClass?>" <?=(($isVisible) ? '' : ' style="display:none"')?>>
<? if($tbl_caption != '') { ?>        
<h3 class="b-layout__h3 b-layout__h3_padtop_17">
    <?= $tbl_caption;?>
</h3>
<? }//if?>
<span class="<?= $setting['abbr_block']?>">
<?php 
$pos=0; 
foreach($tbl as $key=>$field) { 
    if (!$field['name']) continue; 
    if ($key=='idcard_ser') continue; //Серию паспорта показываем при его номере
    $pos++;
    
    $addit_html = "";
    switch($field['type']) {
        case 'small':
        case 'number':
            $length_lable   = 21;
            $td_width = 200;
            $selector_input = "b-combo__input " . sbr_meta::getSelector('field', $setting['field']);
            break;
        case 'date':
            $length_lable   = 17;
            $td_width = 200;
            $selector_input = "b-combo__input b-combo__input_calendar b-combo__input_arrow_yes date_format_use_dot use_past_date no_set_date_on_load year_min_limit_1900 ".sbr_meta::getSelector('field', $setting['field']);
            $addit_html     = '<span class="b-combo__arrow-date"></span>';
            break;
        default:
        case 'default':
            $length_lable   = 88;
            $td_width = 400;
            $selector_input = "b-combo__input " . sbr_meta::getSelector('field', $setting['field']);
            break;
    }
    
    if ($key == 'idcard') {
        $td_width = 120;
    }

    $bDisabled = $setting['disabled'] && (empty($setting['disabled_fields']) || in_array($key, $setting['disabled_fields']));
    $disabledClass = !empty($bDisabled) ? ' b-combo__input_disabled' : '';

    $labelClass = strlen($field['name']) <= 28 ? 'b-layout__txt_padtop_5' : 'b-layout__txt_lineheight_1';
    $exampleClass = strlen($field['example']) <= 50 ? 'b-layout__txt_padtop_5' : 'b-layout__txt_lineheight_1';
    
    $rezItmClass = '';
    $isVisible = false;
    
    if (isset($field['rez_type_new']) && !empty($field['rez_type_new'])):
        $rezItmClass = 'rez--itm' . implode(' rez--itm', $field['rez_type_new']);
        $isVisible = in_array($rez_type, $field['rez_type_new']);
    else:
        $rezItmClass = ($field['rez_type'])?"rez--itm{$field['rez_type']}":'';
        $isVisible = $field['rez_type'] == 0 || $field['rez_type'] == $rez_type;
    endif;
    
?>    
    <table class="b-layout__table b-layout__table_width_full <?=$rezItmClass?>" <?=(($isVisible) ? '' : ' style="display:none"')?>>
        <tr class="b-layout__tr">
        <?php if ($field['type'] == 'check') { ?>
            <td class="b-layout__td b-layout__td_width_200 b-layout__td_padbot_15 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">&nbsp;</td>
            <td class="b-layout__td b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                <div class="b-radio b-radio_layout_horizontal">
                    <div class="b-radio__item">
                        <input id="i<?=$field['idname']?>" class="b-check__input" name="ft<?=$form_type?>[<?=$key?>]" type="checkbox" value="1"<?=$reqvs[$form_type][$key]?' checked="checked"':'' ?> />
                        <label class="b-radio__label b-radio__label_fontsize_13" for="i<?=$field['idname']?>"><?= $field['name'] ?></label>
                    </div>
                </div>
            </td>            
        <?php } else { ?>
            <td class="b-layout__td b-layout__td_width_200 b-layout__td_padbot_15 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                <div class="b-layout__txt b-layout__txt <?=$labelClass?> label-<?=$key?>"><?= $field['name'] ?></div>
            </td>
            <?php if ($key == 'idcard') { ?>
            <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                <?php if($isStatic): ?>
                <div class="b-layout__txt b-layout__txt_italic <?=$labelClass?>">
                    <?=html_attr($reqvs[$form_type]['idcard_ser'])?>
                </div>
                <?php else: ?>
                
                <div class="b-combo<?=($field['rez_required'] ? ' rez--req'.$field['rez_required'] : '').$disabledClass?>">
                    <div class="<?= $selector_input?>">
                        <input <?=empty($bDisabled)? '': 'disabled'?> 
                            type="text" 
                            id="i_1_idcard_ser" 
                            title="<?=  strip_tags($field['example'])?>"
                            value="<?=html_attr($reqvs[$form_type]['idcard_ser'])?>" 
                            size="80" 
                            class="b-combo__input-text"
                            name="ft<?=$form_type?>[idcard_ser]">
                        <?= $addit_html;?>
                    </div>
                </div>
                <?php endif; ?>
                </td>
                <?php } ?>
            
            <td class="b-layout__td b-layout__td_width_<?=$td_width?> b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">

                <?php if (!$setting['options'] || !in_array($field['pos'], array_keys($setting['options']))) { ?>
                <?php if($isStatic): ?>
                    <div class="b-layout__txt b-layout__txt_italic <?=$labelClass?>">
                        <?=html_attr($reqvs[$form_type][$key])?>
                    </div>
                <?php else: ?>
                    <div class="b-combo<?=($field['rez_required'] ? ' rez--req'.$field['rez_required'] : '').$disabledClass?>">
                        <div class="<?= $selector_input?>">
                            <input <?=empty($bDisabled)? '': 'disabled'?> 
                                type="text" 
                                id="i<?=$field['idname']?>" 
                                title="<?=  strip_tags($field['example'])?>"
                                placeholder="<?= in_array($field['pos'], array_keys($setting['subdescr'])) && $setting['subdescr'][$field['pos']] != '' ? $setting['subdescr'][$field['pos']] : ''?>"
                                value="<?=html_attr($reqvs[$form_type][$key])?>" 
                                size="80" 
                                class="b-combo__input-text"
                                onfocus="<?= $setting['field'] == 'phone'? "savePhoneChage(this);" : ""?>" 
                                onblur="if(this.value.length == 0) {<?= $setting['field'] == 'phone'? "savePhoneChage(this);" : ""?>}"
                                name="ft<?=$form_type?>[<?=$key?>]">
                            <?= $addit_html;?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php } else { ?>
                    <?php if($isStatic): ?>
                    
                    <?php else: ?>
                    <div class="b-combo b-select<?=($field['rez_required'] ? ' rez--req'.$field['rez_required'] : '')?>">
                        <select id="i<?=$field['idname']?>" name="ft<?=$form_type?>[<?=$key?>]" class="b-select__select b-select__select_width_400 select-<?=$key?>">
                            <?php foreach ($setting['options'][$field['pos']] as $value => $text) { ?>
                            <option value="<?=$value?>"<?=$reqvs[$form_type][$key] == $value ? ' selected="selected"':''?>><?=$text?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <?php endif; ?>
                <?php } ?>
                
                
            </td>
            <td class="b-layout__td b-layout__td_padbot_15 b-layout__td_width_full_iphone">
                <?php if($field['example'] && !$isStatic): ?>
                    <div class="b-layout__txt <?=$exampleClass?> b-layout__txt_fontsize_11 example-<?=$key?>">
                        <?=(!in_array($field['pos'], $setting['notexample'])?'Например: ':'').$field['example']?>
                    </div>
                <?php endif; ?>
            </td>
        <?php } ?>
        </tr>
    </table>
<?php }//foreach?>
</span>

</span>