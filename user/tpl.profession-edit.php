<div class="i-shadow  i-shadow_zindex_11">                
    <div class="b-shadow b-shadow_width_710 b-shadow_left_140" id="profession_edit_popup">
        <input type="hidden" name="prof_id" value="<?=$category['prof_id']?>" />
        <input type="hidden" name="old_portf_text" value="<?= input_ref($category['portf_text']);?>" />
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                            <div class="b-shadow__title b-shadow__title_padbot_15">
                                <?= ( $category['prof_id'] > 0 ? $category['group_name'] . ' / ' : '' );?><?= $category['prof_name']?>
                            </div>
                            <table class="b-layout__table b-layout__table_width_full">
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_90">&#160;</td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-radio b-radio_layout_horizontal">
                                            <div class="b-radio__item b-radio__item_padright_20">
                                                <input id="cat_r_pos1" class="b-radio__input" name="position" type="radio" value="1" <?= $category['ordering'] == 1 || ( $category['ordering'] == 2 && !is_pro() ) ? 'checked="checked"' : '' ?>/>
                                                <label class="b-radio__label b-radio__label_fontsize_13" for="cat_r_pos1">Первый по счету</label>
                                            </div>
                                            <div class="b-radio__item">
                                                <input id="cat_r_pos2" class="b-radio__input" name="position" type="radio" value="2" <?= ( $category['ordering'] != 1 && is_pro() ) || ( $category['ordering'] > 2 && !is_pro() ) ? 'checked="checked"' : '' ?> />
                                                <label class="b-radio__label b-radio__label_fontsize_13" for="cat_r_pos2">После
                                                    <div class="b-combo b-combo_margtop_-6 b-combo_inline-block">
                                                        <div class="b-combo__input b-combo__input_width_370 b-combo__input_multi_dropdown b-combo__input_arrow_yes b-combo__input_init_categoryList <?= $category['prev_prof_id'] ? "drop_down_default_{$category['prev_prof_id']}" : ""?> exclude_value_0_<?= $category['prof_id']?>">
                                                            <input class="b-combo__input-text" name="position_category" type="text" size="80" value="Выберите раздел" onchange="$('cat_r_pos2').set('checked', true);" one_result_suffix="категорий;профессий" less_five_suffix="категории;профессии" great_than_five_suffix="категорий;профессий"/>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_90"><div class="b-layout__txt">Уточнения к разделу</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-textarea">
                                            <textarea class="b-textarea__textarea " name="portf_text" cols="80" rows="5"><?= input_ref($category['portf_text']);?></textarea>
                                        </div>
                                        <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11">Можно использовать теги &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;</div>

                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_90"><div class="b-layout__txt b-layout__txt_lineheight_1">Ключевые слова</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-textarea">
                                            <textarea class="b-textarea__textarea " name="user_keys" cols="80" rows="5"><?= stripcslashes(implode(", ", $user_keys))?></textarea>
                                        </div>
                                        <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11">Через запятую. Например: дизайн, верстка, программирование, рерайт.</div>
                                    </td>
                                </tr>
                                <? if ($category['proftext'] == 't') { ?>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_90"><div class="b-layout__txt b-layout__txt_lineheight_1">Стоимость тысячи знаков</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-combo b-combo_inline-block b-combo_margright_5">
                                            <div class="b-combo__input b-combo__input_width_80">
                                                <input class="b-combo__input-text" name="prof_cost1000" type="text" size="80" maxlength="7" value="<?= intval($category['cost_1000'])?>" />
                                            </div>
                                        </div>
                                        <div class="b-combo b-combo_inline-block" >
                                            <div class="b-combo__input b-combo__input_width_65 b-combo__input_multi_dropdown b-combo__input_min-width_40 b-combo__input_arrow_yes b-combo__input_init_currencyList drop_down_default_<?= (int) $category['cost_type']?>">
                                                <input type="text" class="b-combo__input-text" id="prof_cost_type" name="prof_cost_type" value="" size="80" readonly="readonly">
                                            </div>
                                        </div>                   
                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_90"><div class="b-layout__txt b-layout__txt_lineheight_1">Стоимость часа работы</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-combo b-combo_inline-block b-combo_margright_5">
                                            <div class="b-combo__input b-combo__input_width_80">
                                                <input class="b-combo__input-text" name="prof_cost_hour" type="text" size="80" maxlength="7" value="<?= intval($category['cost_hour'])?>" />
                                            </div>
                                        </div>
                                        <div class="b-combo b-combo_inline-block">
                                            <div class="b-combo__input b-combo__input_width_65 b-combo__input_multi_dropdown b-combo__input_min-width_40 b-combo__input_arrow_yes b-combo__input_init_currencyList drop_down_default_<?= (int) $category['cost_type_hour']?>">
                                                <input type="text" class="b-combo__input-text" id="prof_cost_type_hour" name="prof_cost_type_hour" size="80" value="" readonly="readonly">
                                            </div>
                                        </div>                   
                                    </td>
                                </tr>
                                <? } else { //if?>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_90"><div class="b-layout__txt b-layout__txt_lineheight_1">Стоимость работ</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5 b-layout__txt_margleft_-15">от</div>
                                        <div class="b-combo b-combo_inline-block">
                                            <div class="b-combo__input b-combo__input_width_80">
                                                <input class="b-combo__input-text" name="prof_cost_from" type="text" size="80" maxlength="7" value="<?= (int) $category['cost_from']?>" />
                                            </div>
                                        </div>
                                        <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">до</div>
                                        <div class="b-combo b-combo_inline-block b-combo_margright_10">
                                            <div class="b-combo__input b-combo__input_width_80">
                                                <input class="b-combo__input-text" name="prof_cost_to" type="text" size="80" maxlength="7" value="<?= (int) $category['cost_to']?>" />
                                            </div>
                                        </div>
                                        <div class="b-combo b-combo_inline-block">
                                            <div class="b-combo__input b-combo__input_width_65 b-combo__input_multi_dropdown b-combo__input_min-width_40 b-combo__input_arrow_yes b-combo__input_init_currencyList drop_down_default_<?= (int) $category['cost_type']?> reverse_list">
                                                <input type="text" class="b-combo__input-text" id="prof_cost_type" name="prof_cost_type" value="" size="80" readonly="readonly">
                                            </div>
                                        </div>                   
                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_90"><div class="b-layout__txt b-layout__txt_lineheight_1">Сроки</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5 b-layout__txt_margleft_-15">от</div>
                                        <div class="b-combo b-combo_inline-block">
                                            <div class="b-combo__input b-combo__input_width_80">
                                                <input class="b-combo__input-text" name="prof_time_from" type="text" size="80" maxlength="7" value="<?= $category['time_from']?>" />
                                            </div>
                                        </div>
                                        <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">до</div>
                                        <div class="b-combo b-combo_inline-block b-combo_margright_10">
                                            <div class="b-combo__input b-combo__input_width_80">
                                                <input class="b-combo__input-text" name="prof_time_to" type="text" size="80" maxlength="7" value="<?= $category['time_to']?>" />
                                            </div>
                                        </div>
                                        <div class="b-combo b-combo_inline-block">
                                            <div class="b-combo__input b-combo__input_width_90 b-combo__input_resize b-combo__input_multi_dropdown b-combo__input_min-width_40 b-combo__input_arrow_yes b-combo__input_init_timeTypeList drop_down_default_<?= (int) $category['time_type']?>" reverse_list>
                                                <input type="text" class="b-combo__input-text" id="prof_time_type" name="prof_time_type" value="" size="80" readonly="readonly">
                                            </div>
                                        </div>                   
                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_90"><div class="b-layout__txt b-layout__txt_lineheight_1">Оценка часа работы</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-combo b-combo_inline-block b-combo_margright_5">
                                            <div class="b-combo__input b-combo__input_width_80">
                                                <input class="b-combo__input-text" name="prof_cost_hour" type="text" size="80" maxlength="7" value="<?= intval($category['cost_hour'])?>" />
                                            </div>
                                        </div>
                                        <div class="b-combo b-combo_inline-block " >
                                            <div class="b-combo__input b-combo__input_width_65 b-combo__input_multi_dropdown b-combo__input_min-width_40 b-combo__input_max-width_40 b-combo__input_arrow_yes b-combo__input_init_currencyList drop_down_default_<?= (int) $category['cost_type_hour']?>">
                                                <input type="text" class="b-combo__input-text" id="prof_cost_hour_type" name="prof_cost_type_hour" value="" size="80" readonly="readonly">
                                            </div>
                                        </div>                   
                                    </td>
                                </tr>
                                <? }//else?>
                                
                                <? if (is_pro()) { ?>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_90">&#160;</td>
                                    <td class="b-layout__one b-layout__one_padbot_30">
                                        <div class="b-check">
                                            <input name="on_preview_default" type="hidden" value="<?= ($category['show_preview'] == 't') ? "1" : "0" ?>"/>
                                            <input id="gr_prev" class="b-check__input" name="on_preview" type="checkbox" value="1" <?= ($category['show_preview'] == 't') ? "checked='checked'" : "" ?>/>
                                            <label for="gr_prev" class="b-check__label b-check__label_fontsize_13">Включить превью работ</label>
                                        </div>
                                    </td>
                                </tr>
                                <? } ?>
                            </table>
                            <span class="block_errors"></span>
                            <div class="b-buttons b-buttons_padleft_87">
                                <a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onclick="xajax_editProfession('<?= get_uid(false)?>', $('profession_edit_popup').toQueryString())">Сохранить изменения</a>
                                &#160;&#160;&#160;
                                <?php if($category['prof_id'] > 0 && (int) $category['is_work'] == 0) { // Удалять можно только пустые категории ?>
                                <a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="if(confirm('Удалить раздел?')) xajax_removeProfession('<?= get_uid(false)?>', {prof_id: '<?= $category['prof_id']?>'})">удалить раздел</a>
                                <?php }//if?>
                                <span class="b-buttons__txt">или</span>
                                <a class="b-buttons__link b-buttons__link_dot_0f71c8 b-buttons__link_close cls-close_popup" href="javascript:void(0)">отменить</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <span class="b-shadow__icon b-shadow__icon_close cls-close_popup"></span>
    </div>                
</div> 