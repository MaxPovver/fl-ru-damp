<input type="hidden" id="fproject" name="fproject" value="<?= ( !empty($message['fproject'])? 1 : 0 )?>">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 <?= !empty($message['fproject'])? "" :"b-fon_hide"; ?>">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('fproject').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">Ответы на проекты</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">За период</div>
                </td>
                <td class="b-layout__right">
                     <span id="i_fproject_period"></span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['fproject_period'] ? "b-combo__input_error" : "" )?>">
                            <input id="c3" class="b-combo__input-text" name="fproject_period[0]" type="text" size="80" readonly 
                                   value="<?= $message['fproject']['period'][0] ? date('d.m.Y', strtotime($message['fproject']['period'][0])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['fproject_period'] ? "b-combo__input_error" : "" )?>">
                            <input id="c39" class="b-combo__input-text" name="fproject_period[1]" type="text" size="80" readonly 
                                   value="<?= $message['fproject']['period'][1] ? date('d.m.Y', strtotime($message['fproject']['period'][1])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span id="fprj_perid_date_text" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?=($message['fproject']['period'][0] || $message['fproject']['period'][1] ? "style='display: none'" : '')?>>&#160;&#160;за всё время</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">Кол-во ответов</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="fproject_count-0" class="b-combo__input-text b-combo-digital-input" name="fproject_count[0]" type="text" size="80" value="<?= $message['fproject']['count'][0]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="fproject_count-1" class="b-combo__input-text b-combo-digital-input" name="fproject_count[1]" type="text" size="80" value="<?= $message['fproject']['count'][1]?>" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'fproject', 'count') ? "style='display:none'" : ""); ?>>&#160;&#160;любое количество</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Предпочитает<br />проекты</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="fproject_type0" class="b-check__input" name="fproject_type[0]" type="checkbox" value="1" <?=($message['fproject']['type_project'][0]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="fproject_type0">Высокого класса</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="fproject_type1" class="b-check__input" name="fproject_type[1]" type="checkbox" value="1" <?=($message['fproject']['type_project'][1]==1?"checked":"")?> />
                        <label class="b-check__label b-check__label_fontsize_13" for="fproject_type1">Среднего</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="fproject_type2" class="b-check__input" name="fproject_type[2]" type="checkbox" value="1" <?=($message['fproject']['type_project'][2]==1?"checked":"")?> />
                        <label class="b-check__label b-check__label_fontsize_13" for="fproject_type2">Низкого</label>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>