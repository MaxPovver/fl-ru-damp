<input type="hidden" id="emassend" name="emassend" value="<?= ( !empty($message['emassend'])? 1 : 0 )?>">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 <?= !empty($message['emassend'])? "" :"b-fon_hide"; ?>">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('emassend').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">Рассылка</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">Специализация</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="massend_spec" class="b-select__select b-select__select_width_300">
                            <option value="0">Любая</option>
                            <?php if( $specs ) { ?>
                            <?php foreach($specs as $key=>$value) { ?>
                            <option value="<?= $value['id'];?>" <?= ($value['id'] == $message['emassend']['spec']?"selected":"")?>><?=$value['name'];?></option>
                            <?php }//foreach?>
                            <?php }//if?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full  b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">Получателей</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3ed" class="b-combo__input-text b-combo-digital-input" name="massend_recipient[0]" type="text" size="80"  value="<?= $message['emassend']['recipient'][0]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3de" class="b-combo__input-text b-combo-digital-input" name="massend_recipient[1]" type="text" size="80"  value="<?= $message['emassend']['recipient'][1]?>"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?= (mailer::checkEmptyRange($message, 'emassend', 'recipient') ? "style='display:none'" : ""); ?>>&#160;&#160;любое количество</span>
                </td>
            </tr>
        </table>
    </div>
</div>