<?php 

if(!isset($buying_name)) {
    // По умолчанию
    $buying_name   = array("buying"         => "buying_buying[%s]",
                           "period"         => "buying_period[%s]",
                           "type_buy"       => "buying_type_buy[%s]",
                           "count_buy"      => "buying_count_buy[%s][%s]",
                           "sum"            => "buying_sum[%s][%s]"
                        );
}
if(!isset($buying_check)) {
    $buying_check = "buying";
}
?>
<input type="hidden" id="<?= $buying_check?>" name="<?= $buying_check?>" value="<?= ( !empty($message[$buying_check])? 1 : 0 )?>">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 <?= !empty($message[$buying_check]) ? "" :"b-fon_hide"?>">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('<?=$buying_check?>').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">Покупки</div>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    &#160;
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="<?= sprintf($buying_name['buying'], 0)?>" class="b-check__input" name="<?= sprintf($buying_name['buying'], 0)?>" type="checkbox" value="1" <?=($message[$buying_check]['buying'][0]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="<?= sprintf($buying_name['buying'], 0)?>">Не совершил ни одной покупки</label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="<?= sprintf($buying_name['buying'], 1)?>" class="b-check__input" name="<?= sprintf($buying_name['buying'], 1)?>" type="checkbox" value="1" <?=($message[$buying_check]['buying'][1]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="<?= sprintf($buying_name['buying'], 1)?>">Совершил хотя бы одну покупку</label>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">За период</div>
                </td>
                <td class="b-layout__right">
                    <span id="i_<?= $buying_name['period']?>"></span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error[$buying_check . '_period'] ? "b-combo__input_error" : "" )?>">
                            <input id="c31" class="b-combo__input-text" name="<?= sprintf($buying_name['period'], 0)?>" type="text" size="80"  
                                   value="<?= $message[$buying_check]['period'][0] ? date('d.m.Y', strtotime($message[$buying_check]['period'][0])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error[$buying_check . '_period'] ? "b-combo__input_error" : "" )?>">
                            <input id="c32" class="b-combo__input-text" name="<?= sprintf($buying_name['period'], 1)?>" type="text" size="80"  
                                   value="<?= $message[$buying_check]['period'][1] ? date('d.m.Y', strtotime($message[$buying_check]['period'][1])) : ""?>" onChange="showHideNotImportantText(this);"/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span id="buy_period_date_text" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?=($message[$buying_check]['period'][0] || $message[$buying_check]['period'][1] ? "style='display: none'" : '')?>>&#160;&#160;за всё время</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    &#160;
                </td>
                <td class="b-layout__right i-button" id="buying_types">
                    <?$cnt = count($message[$buying_check]['buy']);?>
                    <?php if( $cnt > 0 ) { ?>
                    <?php foreach($message[$buying_check]['buy'] as $key=>$val) { ?>
                    <span id="buying_type1" class="buying_type">
                        <a class="b-button b-button_margtop_7 b-button_admin_del b-button_float_right <?= ($cnt==1 ? "b-button_hide" : ""); ?>" href="javascript:void(0)" onclick="removeBuyingType(this)"></a>
                        <div class="b-select b-select_inline-block">
                            <select class="b-select__select b-select__select_width_160" <? if( ($cnt-1) == $key) {?>onchange="addBuyingType(this)"<?}//if?> name="<?= sprintf($buying_name['type_buy'], $key)?>">
                                <option value="0">Любая покупка</option>
                                <?php if($op_codes) { ?>
                                <?php foreach($op_codes as $code=>$name) { ?>
                                <option value="<?= $code?>" <?= ($code==$val['type_buy']?"selected":"")?>><?= $name?></option>
                                <?php } // foreach?>
                                <?php }//if?>
                            </select>
                        </div>
                        <span class="b-layout__txt">&#160;&#215;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_25">
                                <input id="c3882" class="b-combo__input-text" name="<?= sprintf($buying_name['count_buy'], $key, 0)?>" type="text" size="80" value="<?= $val['count_buy'][0]?>"/>
                            </div>
                        </div>
                        <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_25">
                                <input id="c3885" class="b-combo__input-text" name="<?= sprintf($buying_name['count_buy'], $key, 1)?>" type="text" size="80" value="<?= $val['count_buy'][1]?>"/>
                            </div>
                        </div>
                        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;на сумму&#160;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_50">
                                <input id="c3883" class="b-combo__input-text" name="<?= sprintf($buying_name['sum'], $key, 0)?>" type="text" size="80" value="<?= _bill($val['sum'][0])?>"/>
                            </div>
                        </div>
                        <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_50">
                                <input id="c3884" class="b-combo__input-text" name="<?= sprintf($buying_name['sum'], $key, 1)?>" type="text" size="80" value="<?= _bill($val['sum'][1])?>"/>
                            </div>
                        </div>
                        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;руб.</span>
                        <br/><br/>
                    </span>
                    <?php }//foreach?>
                    <?php } else { //if?>
                    <span id="buying_type1" class="buying_type">
                        <a class="b-button b-button_margtop_7 b-button_admin_del b-button_float_right b-button_hide" href="javascript:void(0)" onclick="removeBuyingType(this)"></a>
                        <div class="b-select b-select_inline-block">
                            <select class="b-select__select b-select__select_width_160" onchange="addBuyingType(this)" name="<?= sprintf($buying_name['type_buy'], 0)?>">
                                <option value="0">Любая покупка</option>
                                <?php if($op_codes) { ?>
                                <?php foreach($op_codes as $code=>$name) { ?>
                                <option value="<?= $code?>"><?=$name?></option>
                                <?php } // foreach?>
                                <?php }//if?>
                            </select>
                        </div>
                        <span class="b-layout__txt">&#160;&#215;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_25">
                                <input id="c3a" class="b-combo__input-text b-combo-digital-input" name="<?= sprintf($buying_name['count_buy'], 0, 0)?>" type="text" size="80" />
                            </div>
                        </div>
                        <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_25">
                                <input id="c3b" class="b-combo__input-text b-combo-digital-input" name="<?= sprintf($buying_name['count_buy'], 0, 1)?>" type="text" size="80" />
                            </div>
                        </div>
                        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;на сумму&#160;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_50">
                                <input id="c3c" class="b-combo__input-text b-combo-digital-input" name="<?= sprintf(_bill($buying_name['sum']), 0, 0)?>" type="text" size="80" />
                            </div>
                        </div>
                        <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_50">
                                <input id="c3d" class="b-combo__input-text b-combo-digital-input" name="<?= sprintf(_bill($buying_name['sum']), 0, 1)?>" type="text" size="80" />
                            </div>
                        </div>
                        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;руб.</span>
                        <br/><br/>
                    </span>
                    <?php }//else?>
                </td>
            </tr>
        </table>
    </div>
</div>