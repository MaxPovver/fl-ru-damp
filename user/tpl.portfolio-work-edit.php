<div class="i-shadow i-shadow_zindex_11">                
    <div id="portfolio_work_edit" class="b-shadow b-shadow_width_710 b-shadow_zindex_110">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                            <?php if($is_edit) { // Ид до изменений (необходимо для того чтобы понять какие разделы обновлять в интерфейсе)?>
                            <input type="hidden" name="prof_id_first" value="<?= $work['prof_id']?>">
                            <?php }//if?>
                            <input type="hidden" name="work_preview_type" id="work_preview_type" value="<?= $work['prj_prev_type']?>" />
                            <input type="hidden" name="id" value="<?= $work['id']?>">
                            <div class="b-shadow__title b-shadow__title_padbot_15"><?= ( $is_edit ? "Редактировать работу" : "Новая работа" );?></div>
                            <table class="b-layout__table b-layout__table_width_full">
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_80"><div class="b-layout__txt b-layout__txt_padtop_5">Название</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-combo">
                                            <div class="b-combo__input">
                                                <input class="b-combo__input-text" name="work_name" type="text" size="80" value="<?= $work['name']?>"/>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_width_80 b-layout__one_padbot_20">&#160;</td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-radio b-radio_layout_horizontal">
                                            <div class="b-radio__item b-radio__item_padright_20">
                                                <input id="position_first" class="b-radio__input" name="position" type="radio" value="first" />
                                                <label class="b-radio__label b-radio__label_fontsize_13" for="position_first">Первая в разделе</label>
                                            </div>
                                            <div class="b-radio__item b-radio__item_padright_20">
                                                <input id="position_last" class="b-radio__input" name="position" type="radio" value="last" <?= ( !$is_edit ? 'checked="checked"' : '' ); ?>/>
                                                <label class="b-radio__label b-radio__label_fontsize_13" for="position_last">Последняя</label>
                                            </div>
                                            <div class="b-radio__item">
                                                <input id="position_var" class="b-radio__input" name="position" type="radio" value="var" <?= ( $is_edit ? 'checked="checked"' : '' ); ?>/>
                                                <label class="b-radio__label b-radio__label_fontsize_13" for="position_var">
                                                    <div class="b-combo b-combo_margtop_-6 b-combo_inline-block">
                                                        <div class="b-combo__input b-combo__input_width_50">
                                                            <input class="b-combo__input-text" name="position_num" type="text" maxlength="4" r size="80" onchange="$('position_var').set('checked', true);" value="<?= $work['norder']?>"/>
                                                        </div>
                                                    </div>
                                                    по счёту</label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_width_80 b-layout__one_padbot_20">
                                       <div class="b-layout__txt b-layout__txt_padtop_5">Раздел</div>
                                    </td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-combo">
                                            <div class="b-combo__input b-combo__input_width_300 b-combo__input_multi_dropdown b-combo__input_arrow_yes b-combo__input_init_categoryList <?= $work['prof_id'] ? "drop_down_default_{$work['prof_id']}" : "" ?>">
                                                <input class="b-combo__input-text" id="work_category" name="work_category" type="text" size="80" value="Выберите раздел" onchange="$('cat_r_pos2').set('checked', true);"/>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_80">&#160;</td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div <?= ($work['pict'] != '' OR $work['prev_pict'] != '') ? '' : 'style="display:none"'?>>
                                            <div class="b-prev b-prev_width_208 b-prev_margright_20">
                                                <dl class="b-prev__list">
                                                    <dt class="b-prev__dt">Основной файл</dt>
                                                    <dd class="b-prev__dd">
                                                        <?if($work['pict']) {
                                                                $l_dir = substr($_SESSION['login'], 0, 2) . "/" . $_SESSION['login'];
                                                                $path = "users/$l_dir/upload/".$work['pict'];
                                                                $cfile  = new CFile($path);
                                                                $width  = $cfile->image_size['width'];
                                                                $height = $cfile->image_size['height'];
                                                        }?>
                                                        <div style="z-index:2" class="b-prev__rama b-prev__rama_height_200 <?= ( $width && $height && $cfile->image_size['type'] != 13 && $cfile->image_size['type'] != 4)  ? "b-file_hover" : ""?>" id="file_upload_block_portf">
                                                            <span class='b-file__shadow'></span>
                                                            <input type="hidden" name="old_main_file" value="<?=$work['pict']?>">
                                                            <input type="hidden" id="imain_file" name="main_file" value="<?=$work['pict']?>">
                                                            <?php 
                                                            if($work['pict']) {
                                                                if($width && $height && $cfile->image_size['type'] != 13 && $cfile->image_size['type'] != 4) {
                                                                    print view_image_file($work['pict'], $_SESSION['login'], "upload", array('max_dim' => 200, 'class' => 'b-prev__pic', 'id' => 'work_image'));
                                                                } else { ?>
                                                                <div id="work_image" class="b-layout b-layout_padtop_20 b-layout_padlr_10">
                                                                    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
                                                                            <tr class="b-layout__tr">
                                                                                <td class="b-icon-layout__icon"><i class="b-icon b-icon_attach_<?=getICOFile($cfile->getext());?>"></i></td>
                                                                                <td class="b-icon-layout__files"><div class="b-layout__txt b-layout__txt_padtop_5"><a href="<?= WDCPREFIX . '/' . $cfile->path . $cfile->name?>" class="b-icon-layout__link b-icon-layout__link_fontsize_13"><?= uploader::cutNameFile($cfile->original_name, portfolio::FILE_NAME_LENGTH_EDIT);?></a></div></td>
                                                                                <td class="b-icon-layout__size" style="padding-right:0;"><div class="b-layout__txt b-layout__txt_padtop_5">,<?= ConvertBtoMB($cfile->size); ?></div></td>
                                                                            </tr>
                                                                    </table>
                                                                      <div id="swf_params" class="b-select b-select_padtop_10 b-select_center" <?=( strtolower( preg_replace("#.*(\.[a-zA-Z0-9]*)$#", '$1', $cfile->name)) != ".swf" ? 'style="display:none"' : '') ?>>
                                                                          <label for="wmode" class="b-select__label b-select__label_inline-block b-select__label_fontsize_11">wmode: </label>
                                                                          <select id="wmode" class="b-select__select b-select__select_width_70" name="wmode">
                                                                              <option>window</option>
                                                                              <option>direct</option>
                                                                              <option>gpu</option>
                                                                          </select>
                                                                      </div>
                                                                </div>
                                                                <? }//else?>
                                                            <? }  ?>
                                                            <span id="work_main_file"></span>
                                                            <?php if($work['pict']) { ?>
                                                            <a class="b-button b-button_admin_gdel b-button_absolute b-button_top_-8 b-button_right_-8 b-button_z-index_3 qq-upload-delete" id="remove_main_file" href="javascript:void(0)" onclick="if($('work_image')) { $('work_image').dispose(); } $('imain_file').set('value', ''); $('file_upload_block_portf').removeClass('b-file_hover'); $(this).dispose();"></a>
                                                            <?php }//if?>
                                                        </div>
                                                    </dd>
                                                </dl>
                                            </div>
                                            <div class="b-prev b-prev_width_208 preview-work-block">
                                                <dl class="b-prev__list">
                                                    <dt class="b-prev__dt">Превью</dt>
                                                    <dt class="b-prev__dt <?= $work['prj_prev_type'] == 0 ? 'b-prev__dt_active' : ''?>">
                                                        <a class="b-prev__link toggle-type-preview" href="javascript:void(0)" data-type="0">графическое</a>
                                                    </dt>
                                                    <dd class="b-prev__dd <?= $work['prj_prev_type'] == 0 ? '' : 'b-prev__dd_hide'?>">
                                                        <div class="b-prev__rama b-prev__rama_height_200 b-prev__rama_shadow <?= $work['prev_pict'] ? "b-file_hover" : ""?>" id="file_upload_block_preview" style="z-index:2"> <!-- b-file_hover -->
                                                            <span class='b-file__shadow'></span>
                                                            <input type="hidden" name="old_preview_file" value="<?=$work['prev_pict']?>">
                                                            <input type="hidden" id="ipreview_file" name="preview_file" value="<?=$work['prev_pict']?>">
                                                            <?php if($work['prev_pict'])?><?php print view_image_file($work['prev_pict'], $_SESSION['login'], "upload", array('max_dim' => 200, 'class' => 'b-prev__pic', 'id' => 'preview_image'));?>
                                                            <span id="preview_overlay" style="display:none;">
                                                                <div class="b-prev__overlay b-prev__overlay_bg_black"></div>
                                                                <div class="b-layout__txt b-layout__txt_zindex_2 b-layout__txt_relative b-layout__txt_padtop_30">
                                                                    <div class="b-layout__txt b-layout__txt_padtop_30 b-layout__txt_center b-layout__txt_color_fff b-layout__txt_fontsize_11">Вы обновили основной файл</div>
                                                                    <div class="b-layout__txt b-layout__txt_center">
                                                                        <span class="b-layout__txt b-layout__txt_pad_1_3 b-layout__txt_bg_f2"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_41" href="javascript:void(0)" onclick="xajax_updatePreview( $$('#work_main_file .qq-uploader-fileID').get('file')[0], $$('#work_preview_file input[name^=IDResource]').get('value')[0]); $('work_preview_file').hide();">Обновить превью</a></span>
                                                                        &#160; <span class="b-layout__txt b-layout__txt_pad_1_3 b-layout__txt_bg_f2"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_41" href="javascript:void(0)" onclick="$('preview_overlay').hide(); $('work_preview_file').show(); $('file_upload_block_preview').addClass('b-file_hover');">Не обновлять</a></span>
                                                                    </div>
                                                                </div>
                                                            </span>
                                                            <span id="work_preview_file"></span>
                                                            <?php if($work['prev_pict']) { ?>
                                                            <a class="b-button b-button_admin_gdel b-button_absolute b-button_top_-8 b-button_right_-8 b-button_z-index_3 qq-upload-delete" href="javascript:void(0)" id="remove_preview_file" onclick="$('preview_image').dispose(); $('ipreview_file').set('value', ''); $('file_upload_block_preview').removeClass('b-file_hover'); $(this).dispose();"></a>
                                                            <?php }//if?>
                                                        </div>
                                                    </dd>
                                                    <dt class="b-prev__dt <?= $work['prj_prev_type'] == 1 ? 'b-prev__dt_active' : ''?>">
                                                        <a class="b-prev__link toggle-type-preview" href="javascript:void(0)" data-type="1">текстовое</a>
                                                    </dt>
                                                    <dd class="b-prev__dd <?= $work['prj_prev_type'] == 1 ? '' : 'b-prev__dd_hide'?>">
                                                        <div class="b-prev__rama b-prev__rama_height_200 b-prev__rama_overflow_auto">
                                                            <div class="b-prev__body" id="text_preview_descr">
                                                                <? if(trim($work['descr']) == '') { ?>
                                                                    <a href="#" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle expand-link-descr">Добавить описание</a>
                                                                <? } else { ?>
                                                                    <?= (nl2br($work['descr']));?>
                                                                <? }//if?>
                                                            </div>
                                                        </div>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                        <div class="b-layout__txt b-layout__txt_padbot_5 i-button <?= ($work['pict'] != '' OR $work['prev_pict'] != '') ? 'b-layout__txt_hide' : ''?>">
                                            <a href="javascript:void(0)" class="b-button b-button_poll_plus expand-link-file"></a>&nbsp;<a href="javascript:void(0)" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle expand-link-file">Прикрепить файл</a>
                                        </div>                    
                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_80"><div class="b-layout__txt b-layout__txt_padtop_5" <?= ( $work['link'] != '' ? '' : 'style="display:none"'); ?>>Ссылка</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div <?= ( $work['link'] != '' ? '' : 'style="display:none"'); ?>>
                                            <div class="b-combo">
                                                <div class="b-combo__input">
                                                    <input class="b-combo__input-text" name="link" type="text" size="80" value="<?= $work['link']?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-layout__txt b-layout__txt_padbot_5 i-button <?= ( $work['link'] != '' ? 'b-layout__txt_hide' : ''); ?>">
                                            <a href="#" class="b-button b-button_poll_plus expand-link"></a>&nbsp;<a href="#" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle expand-link">Поставить ссылку</a>
                                        </div>                    
                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_80"><div class="b-layout__txt b-layout__txt_padtop_5" <?= ( $work['video_link'] != '' ? '' : 'style="display:none"'); ?>>Видео</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div <?= ( $work['video_link'] != '' ? '' : 'style="display:none"'); ?>>
                                            <div class="b-combo">
                                                <div class="b-combo__input">
                                                    <input class="b-combo__input-text" name="video" type="text" size="80" value="<?= $work['video_link']?>" />
                                                </div>
                                            </div>
                                            <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11">Вставьте в поле ниже ссылку, которую вы получили на видео хостинге YouTube, RuTube или Vimeo.</div>
                                        </div>
                                        <div class="b-layout__txt b-layout__txt_padbot_5 i-button <?= ( $work['video_link'] != '' ? 'b-layout__txt_hide' : ''); ?>">
                                            <a href="#" class="b-button b-button_poll_plus expand-link"></a>&nbsp;<a href="#" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle expand-link">Добавить видео</a>
                                        </div>                    
                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_80"><div class="b-layout__txt" style="display:none;">Описание</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div <?= ( $work['descr'] != '' ? '' : 'style="display:none"'); ?>>
                                            <div class="b-textarea">
                                                <textarea class="b-textarea__textarea " name="work_descr" id="work_descr" onkeyup="var str_current = htmlschars(document.getElementById('work_descr').value); var len_current = str_current.length; if (len_current > 1500 ) { document.getElementById('work_descr').value = document.getElementById('work_descr').value.substr (0, 1500-(len_current-document.getElementById('work_descr').value.length)); } $('text_preview_descr').set('html', clearHTMLText( this.value ) ); if(len_current>1500) { len_current = 1500; } $('descr_length').set('html', len_current)" cols="80" rows="5"><?= htmlspecialchars_decode( ( $work['descr'] ) );?></textarea>
                                            </div>
                                            <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11 b-layout__txt_float_right"><span id="descr_length"><?= strlen(htmlspecialchars_decode( ( $work['descr'] ) ));?></span>/1500</div>
                                            <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11">Можно использовать теги &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;</div>
                                        </div>
                                        <div class="b-layout__txt b-layout__txt_padbot_5 i-button <?= ( $work['descr'] != '' ? 'b-layout__txt_hide' : ''); ?>" id="descr_block">
                                            <a href="#" class="b-button b-button_poll_plus expand-link"></a>&nbsp;<a href="#" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle expand-link">Добавить описание</a>
                                        </div>                    
                                    </td>
                                </tr>
                                <tr class="b-layout__tr" <?= ($work['prj_cost'] != 0 || $work['prj_time_value'] != 0 ? '' : 'style="display:none"')?>>
                                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_80"><div class="b-layout__txt b-layout__txt_padtop_5">Стоимость</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_20">
                                        <div class="b-combo b-combo_inline-block b-combo_margright_10">
                                            <div class="b-combo__input b-combo__input_width_80">
                                                <input class="b-combo__input-text" name="work_cost" type="text" maxlength="9" size="80" value="<?= (int)$work['prj_cost']?>" />
                                            </div>
                                        </div>
                                        <div class="b-combo b-combo_inline-block">
                                            <div class="b-combo__input b-combo__input b-combo__input_width_65 b-combo__input_multi_dropdown b-combo__input_min-width_40 b-combo__input_arrow_yes b-combo__input_init_currencyList drop_down_default_<?= $work['prj_cost_type'] === null ? '2' : (int)$work['prj_cost_type'] ?> reverse_list">
                                                <input type="text" class="b-combo__input-text" id="work_cost_type" name="work_cost_type" size="80" value="руб" readonly="readonly">
                                            </div>
                                        </div>                   
                                    </td>
                                </tr>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_80"><div class="b-layout__txt b-layout__txt_lineheight_1" <?= ($work['prj_cost'] != 0 || $work['prj_time_value'] != 0 ? '' : 'style="display:none"')?>>Потрачено времени</div></td>
                                    <td class="b-layout__one b-layout__one_padbot_30">
                                        <div <?= ($work['prj_cost'] != 0 || $work['prj_time_value'] != 0 ? '' : 'style="display:none"')?>>
                                            <div class="b-combo b-combo_inline-block b-combo_margright_10">
                                                <div class="b-combo__input b-combo__input_width_80">
                                                    <input class="b-combo__input-text" name="time_cost" type="text" maxlength="4" size="80" value="<?= (int)$work['prj_time_value']?>" />
                                                </div>
                                            </div>
                                            <div class="b-combo b-combo_inline-block">
                                                <div class="b-combo__input b-combo__input_width_90 b-combo__input_resize b-combo__input_multi_dropdown b-combo__input_min-width_80 b-combo__input_arrow_yes b-combo__input_init_timeTypeList drop_down_default_<?= (int) $work['prj_time_type'];?>">
                                                    <input type="text" class="b-combo__input-text" id="work_time_type" name="work_time_type" size="80" value="часов" readonly="readonly">
                                                </div>
                                            </div>                   
                                        </div>
                                        <div class="b-layout__txt b-layout__txt_padbot_5 i-button <?= ($work['prj_cost'] != 0 || $work['prj_time_value'] != 0 ? 'b-layout__txt_hide' : '')?>">
                                            <a href="#" class="b-button b-button_poll_plus expand-link"></a>&nbsp;<a href="#" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle expand-link">Добавить стоимость и сроки</a>
                                        </div>                    
                                    </td>
                                </tr>
                            </table>
                            <span class="block_errors"></span>
                            <div class="b-buttons b-buttons_padleft_78">
                                <a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_disabled')) { $(this).addClass('b-button_disabled'); xajax_editWork('<?= get_uid(false)?>', $('portfolio_work_edit').toQueryString()); }"><?= ( $is_edit ? "Редактировать работу" : "Добавить работу" );?></a>
                                <? if($is_edit) {?>
                                &#160;&#160;&#160;
                                <a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="if(confirm('Удалить работу?')) xajax_removeWork('<?= get_uid(false)?>', {id: '<?= $work['id']?>', prof_id: '<?= $work['prof_id']?>'})">удалить работу</a>
                                <? }//if?>
                                <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                                <a class="b-buttons__link b-buttons__link_dot_0f71c8 cls-close_popup" href="javascript:void(0)">отменить</a>
                            </div>
                        </div>
        <span class="b-shadow__icon b-shadow__icon_close cls-close_popup"></span>
    </div>                
</div>