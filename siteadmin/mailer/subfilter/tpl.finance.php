<?php 

if(!isset($finance_name)) {
    // По умолчанию
    $finance_name   = array("money"          => "finance_money",
                            "spend"          => "finance_spend[%s]",
                            "deposit"        => "finance_deposit[%s]",
                            "method_deposit" => "finance_method_deposit[%s]"
                        );
}
if(!isset($finance_check)) {
    $finance_check = "finance";
}
?>
<input type="hidden" id="<?= $finance_check?>" name="<?= $finance_check?>" value="<?= (!empty($message[$finance_check]) ? 1 : 0 )?>">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 <?= !empty($message[$finance_check]) ? "" : "b-fon_hide"; ?>">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('<?=$finance_check?>').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">Финансы</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">Денег на счету</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135 ">
                            <input id="c3888" class="b-combo__input-text b-combo-digital-input" name="<?= $finance_name['money']?>" type="text" size="80"  value="<?= _bill($message[$finance_check]['money'])?>" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;руб.</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Последнее<br />списание</div>
                </td>
                <td class="b-layout__right">
                    <span id="i_<?= $finance_name['spend']?>"></span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error[$finance_check . '_spend'] ? "b-combo__input_error" : "" )?>">
                            <input id="c34" class="b-combo__input-text" name="<?= sprintf($finance_name['spend'], 0)?>" type="text" size="80"  
                                   value="<?= $message[$finance_check]['spend'][0] ? date('d.m.Y', strtotime($message[$finance_check]['spend'][0])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error[$finance_check . '_spend'] ? "b-combo__input_error" : "" )?>">
                            <input id="c36" class="b-combo__input-text" name="<?= sprintf($finance_name['spend'], 1)?>" type="text" size="80"  
                                   value="<?= $message[$finance_check]['spend'][1] ? date('d.m.Y', strtotime($message[$finance_check]['spend'][1])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span id="fin_lastout_date_text" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?=($message[$finance_check]['spend'][0] || $message[$finance_check]['spend'][1] ? "style='display: none'" : '')?>>&#160;&#160;не важно</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Последнее<br />пополнение</div>
                </td>
                <td class="b-layout__right">
                    <span id="i_<?= $finance_name['deposit']?>"></span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error[$finance_check . '_deposit'] ? "b-combo__input_error" : "" )?>">
                            <input id="c3b1" class="b-combo__input-text" name="<?= sprintf($finance_name['deposit'], 0)?>" type="text" size="80" readonly 
                                   value="<?= $message[$finance_check]['deposit'][0] ? date('d.m.Y', strtotime($message[$finance_check]['deposit'][0])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error[$finance_check . '_deposit'] ? "b-combo__input_error" : "" )?>">
                            <input id="c13" class="b-combo__input-text" name="<?= sprintf($finance_name['deposit'], 1)?>" type="text" size="80"  
                                   value="<?= $message[$finance_check]['deposit'][1] ? date('d.m.Y', strtotime($message[$finance_check]['deposit'][1])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span id="fin_lastin_date_text" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?=($message[$finance_check]['deposit'][0] || $message[$finance_check]['deposit'][1] ? "style='display: none'" : '')?>>&#160;&#160;не важно</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Способ<br />пополнения счёта</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="<?= sprintf($finance_name['method_deposit'], 0)?>" class="b-check__input" name="<?= sprintf($finance_name['method_deposit'], 0)?>" type="checkbox" value="1" <?=($message[$finance_check]['method_deposit'][0]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="<?= sprintf($finance_name['method_deposit'], 0)?>">Яндекс.Деньги</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="<?= sprintf($finance_name['method_deposit'], 1)?>" class="b-check__input" name="<?= sprintf($finance_name['method_deposit'], 1)?>" type="checkbox" value="1" <?=($message[$finance_check]['method_deposit'][1]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="<?= sprintf($finance_name['method_deposit'], 1)?>">Webmoney</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="<?= sprintf($finance_name['method_deposit'], 2)?>" class="b-check__input" name="<?= sprintf($finance_name['method_deposit'], 2)?>" type="checkbox" value="1" <?=($message[$finance_check]['method_deposit'][2]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="<?= sprintf($finance_name['method_deposit'], 2)?>">СМС</label>
                    </div>
                    <div class="b-check">
                        <input id="<?= sprintf($finance_name['method_deposit'], 3)?>" class="b-check__input" name="<?= sprintf($finance_name['method_deposit'], 3)?>" type="checkbox" value="1" <?=($message[$finance_check]['method_deposit'][3]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="<?= sprintf($finance_name['method_deposit'], 3)?>">Банковский перевод</label>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>