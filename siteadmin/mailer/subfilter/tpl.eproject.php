<input type="hidden" id="eproject" name="eproject" value="<?= ( !empty($message['eproject'])? 1 : 0 )?>">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 <?= !empty($message['eproject'])? "" :"b-fon_hide"; ?>">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('eproject').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">Проекты</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">За период</div>
                </td>
                <td class="b-layout__right">
                    <span id="i_eproject_period"></span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['eproject_period'] ? "b-combo__input_error" : "" )?>">
                            <input id="c311" class="b-combo__input-text" name="eproject_period[0]" type="text" size="80" 
                                   value="<?= $message['eproject']['period'][0] ? date('d.m.Y', strtotime($message['eproject']['period'][0])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['eproject_period'] ? "b-combo__input_error" : "" )?>">
                            <input id="c35" class="b-combo__input-text" name="eproject_period[1]" type="text" size="80" 
                                   value="<?= $message['eproject']['period'][1] ? date('d.m.Y', strtotime($message['eproject']['period'][1])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span id="eprj_period_date_text" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?=($message['eproject']['period'][0] || $message['eproject']['period'][1] ? "style='display: none'" : '')?>>&#160;&#160;за всё время</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Создал проектов<br />любого типа</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_created[0]" type="text" size="80" value="<?= $message['eproject']['created'][0]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_created[1]" type="text" size="80" value="<?= $message['eproject']['created'][1]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'eproject', 'created') ? "style='display:none'" : ""); ?>>&#160;&#160;любое количество</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Фри-ланс</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_freelance[0]" type="text" size="80" value="<?= $message['eproject']['freelance'][0]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_freelance[1]" type="text" size="80" value="<?= $message['eproject']['frelance'][1]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'eproject', 'freelance') ? "style='display:none'" : ""); ?>>&#160;&#160;любое количество</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">Только для <img src="/images/icons/f-pro.png" alt="" /></div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_only_pro[0]" type="text" size="80" value="<?= $message['eproject']['only_pro'][0]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_only_pro[1]" type="text" size="80" value="<?= $message['eproject']['only_pro'][1]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'eproject', 'only_pro') ? "style='display:none'" : ""); ?>>&#160;&#160;любое количество</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">В офис</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_in_office[0]" type="text" size="80" value="<?= $message['eproject']['in_office'][0]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_in_office[1]" type="text" size="80" value="<?= $message['eproject']['in_office'][1]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'eproject', 'in_office') ? "style='display:none'" : ""); ?>>&#160;&#160;любое количество</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">Конкурсы</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_konkurs[0]" type="text" size="80" value="<?= $message['eproject']['konkurs'][0]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_konkurs[1]" type="text" size="80" value="<?= $message['eproject']['konkurs'][1]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'eproject', 'konkurs') ? "style='display:none'" : ""); ?>>&#160;&#160;любое количество</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Бюджет каждого<br />проекта</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_budget[0]" type="text" size="80" value="<?= $message['eproject']['budget'][0]?>" />
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_budget[1]" type="text" size="80" value="<?= $message['eproject']['budget'][1]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'eproject', 'budget') ? "style='display:none'" : ""); ?>>&#160;&#160;любой</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Сум. бюджет<br />всех проектов</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_sum_budget[0]" type="text" size="80" value="<?= $message['eproject']['sum_budget'][0]?>" />
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_sum_budget[1]" type="text" size="80" value="<?= $message['eproject']['sum_budget'][1]?>" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'eproject', 'sum_budget') ? "style='display:none'" : ""); ?>>&#160;&#160;любой</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Среднее кол-во<br />отв. на проекты</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_avg_answer[0]" type="text" size="80" value="<?= $message['eproject']['avg_answer'][0]?>" />
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="eproject_avg_answer[1]" type="text" size="80" value="<?= $message['eproject']['avg_answer'][1]?>" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'eproject', 'avg_answer') ? "style='display:none'" : ""); ?>>&#160;&#160;любое количество</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Расчитаны на<br />исполнителей</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="eproject_executor0" class="b-check__input" name="eproject_executor[0]" type="checkbox" value="1" <?=($message['eproject']['executor'][0]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="eproject_executor0">Высокого класса</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="eproject_executor1" class="b-check__input" name="eproject_executor[1]" type="checkbox" value="1" <?=($message['eproject']['executor'][1]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="eproject_executor1">Среднего</label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="eproject_executor2" class="b-check__input" name="eproject_executor[2]" type="checkbox" value="1" <?=($message['eproject']['executor'][2]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="eproject_executor2">Низкого</label>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">Специализации</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="eproject_spec" class="b-select__select b-select__select_width_300">
                            <option value="0">Любые</option>
                            <?php if( $specs ) { ?>
                            <?php foreach($specs as $key=>$value) { ?>
                            <option value="<?= $value['id'];?>" <?= ($value['id'] == $message['eproject']['spec']?"selected":"")?>><?=$value['name'];?></option>
                            <?php }//foreach?>
                            <?php }//if?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>