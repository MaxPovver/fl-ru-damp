<input type="hidden" id="fblog" name="fblog" value="<?= ( !empty($message['fblog'])? 1 : 0 )?>">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 <?= !empty($message['fblog'])? "" :"b-fon_hide"; ?>">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('fblog').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">Активность в блогах</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">За период</div>
                </td>
                <td class="b-layout__right">
                     <span id="i_fblog_period"></span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['fblog_period'] ? "b-combo__input_error" : "" )?>">
                            <input id="blog_period_0" class="b-combo__input-text" name="fblog_period[0]" type="text" size="80"  
                                   value="<?= $message['fblog']['period'][0] ? date('d.m.Y', strtotime($message['fblog']['period'][0])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['fblog_period'] ? "b-combo__input_error" : "" )?>">
                            <input id="blog_period_1" class="b-combo__input-text" name="fblog_period[1]" type="text" size="80"   
                                   value="<?= $message['fblog']['period'][1] ? date('d.m.Y', strtotime($message['fblog']['period'][1])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span id="blog_perid_date_text" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?=($message['fblog']['period'][0] || $message['fblog']['period'][1] ? "style='display: none'" : '')?>>&#160;&#160;за всё время</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">Написал постов</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="fblog_post[0]" type="text" size="80" value="<?= $message['fblog']['post'][0]?>" />
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text b-combo-digital-input" name="fblog_post[1]" type="text" size="80" value="<?= $message['fblog']['post'][1]?>" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'fblog', 'post') ? "style='display:none'" : ""); ?>>&#160;&#160;любое количество</span>
                </td>
            </tr>
        </table>
    </div>
</div>