<div id="filter_employer" class="b-layout__inner b-layout__inner_bordtop_c6 b-layout__inner_bordbot_c6 b-layout__inner_margbot_30 b-layout__inner_padtb_20 <?= ($message['filter_emp'] ? "":"b-layout__inner_hide")?>">
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Аккаунт</div>
            </td>
            <td class="b-layout__right">
                <div class="b-check b-check_padbot_10">
                    <input id="check_emp_pro" class="b-check__input" name="etype_account[0]" type="checkbox" value="1" <?=($message['etype_account'][0]==1?"checked":"")?>/>
                    <label class="b-check__label b-check__label_fontsize_13" for="check_emp_pro">Профессиональный <img src="/images/icons/e-pro.png" alt=""  /></label>
                </div>
                <div class="b-check b-check_padbot_20">
                    <input id="check_emp_norm" class="b-check__input" name="etype_account[1]" type="checkbox" value="1" <?=($message['etype_account'][1]==1?"checked":"")?>/>
                    <label class="b-check__label b-check__label_fontsize_13" for="check_emp_norm">Начальный</label>
                </div>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Профиль</div>
            </td>
            <td class="b-layout__right">
                <div class="b-check b-check_padbot_10">
                    <input id="check_emp_profile_full" class="b-check__input" name="etype_profile[0]" type="checkbox" value="1" <?=($message['etype_profile'][0]==1?"checked":"")?>/>
                    <label class="b-check__label b-check__label_fontsize_13" for="check_emp_profile_full">Заполнен</label>
                </div>
                <div class="b-check b-check_padbot_20">
                    <input id="check_emp_profile_norm" class="b-check__input" name="etype_profile[1]" type="checkbox" value="1" <?=($message['etype_profile'][1]==1?"checked":"")?>/>
                    <label class="b-check__label b-check__label_fontsize_13" for="check_emp_profile_norm">Пустой</label>
                </div>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130 b-layout__left_valign_middle">
                <div class="b-layout__txt">Зарегистрирован</div>
            </td>
            <td class="b-layout__right">
                <span id="i_eregdate"></span>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['eregdate'] ? "b-combo__input_error" : "" )?>">
                        <input id="date_from" class="b-combo__input-text" name="efrom_regdate" type="text" size="80" value="<?= $message['efrom_regdate'] ? date('d.m.Y', strtotime($message['efrom_regdate'])) : ""?>" onChange="showHideNotImportantText(this);"/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['eregdate'] ? "b-combo__input_error" : "" )?>">
                        <input id="date_to" class="b-combo__input-text" name="eto_regdate" type="text" size="80"  value="<?= $message['eto_regdate'] ? date('d.m.Y', strtotime($message['eto_regdate'])) : ""?>" onChange="showHideNotImportantText(this);"/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span id="emp_reg_date_text" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?=($message['efrom_regdate'] || $message['eto_regdate'] ? "style='display: none'" : '')?>>&#160;&#160;не важно</span>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130 b-layout__left_valign_middle">
                <div class="b-layout__txt">Последний визит</div>
            </td>
            <td class="b-layout__right">
                <span id="i_lastvisit"></span>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['elastvisit'] ? "b-combo__input_error" : "" )?>">
                        <input id="efrom_lastvisit" class="b-combo__input-text" name="efrom_lastvisit" type="text" size="80"  value="<?= $message['efrom_lastvisit'] ? date('d.m.Y', strtotime($message['efrom_lastvisit'])) : ""?>" onChange="showHideNotImportantText(this);"/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['elastvisit'] ? "b-combo__input_error" : "" )?>">
                        <input id="eto_lastvisit" class="b-combo__input-text" name="eto_lastvisit" type="text" size="80"  value="<?= $message['eto_lastvisit'] ? date('d.m.Y', strtotime($message['eto_lastvisit'])) : ""?>" onChange="showHideNotImportantText(this);"/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span id="emp_lastvisit_date_text" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" <?=($message['efrom_lastvisit'] || $message['eto_lastvisit'] ? "style='display: none'" : '')?>>&#160;&#160;не важно</span>
            </td>
        </tr>
    </table>
    <? $finance_check  = "efinance"; ?>
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message[$finance_check])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('<?= $finance_check?>').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('<?= $finance_check?>').set('value', 1);">Финансы</a>
    </div>
    <?php 
    $finance_name   = array("money"          => "efinance_money",
                            "spend"          => "efinance_spend[%s]",
                            "deposit"        => "efinance_deposit[%s]",
                            "method_deposit" => "efinance_method_deposit[%s]"
                      );
    ?>
    <? include("subfilter/tpl.finance.php"); ?>
    <? unset($finance_name, $finance_check); ?>
    
    <? $buying_check = "ebuying"; ?>
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message[$buying_check])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('<?= $buying_check;?>').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('<?= $buying_check;?>').set('value', 1);">Покупки</a>
    </div>
    <?php
    $buying_name   = array("buying"         => "ebuying_buying[%s]",
                           "period"         => "ebuying_period[%s]",
                           "type_buy"       => "ebuying_type_buy[%s]",
                           "count_buy"      => "ebuying_count_buy[%s][%s]",
                           "sum"            => "ebuying_sum[%s][%s]"
                        );
    $op_codes = mailer::$buying_employer;
    ?>
    <? include("subfilter/tpl.buying.php"); ?>
    <? unset($buying_name, $buying_check, $op_codes); ?>
    
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message['eproject'])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('eproject').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('eproject').set('value', 1);">Проекты</a>
    </div>
    <? include("subfilter/tpl.eproject.php"); ?>
    
    <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message['emassend'])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('emassend').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('emassend').set('value', 1);">Рассылка</a>
    </div>
	<? include("subfilter/tpl.massend.php"); ?>			
				
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Пол</div>
            </td>
            <td class="b-layout__right">
                <div class="b-check b-check_padbot_10 b-check_padtop_4">
                    <input id="check_emp_sex0" class="b-check__input" name="etype_sex[0]" type="checkbox" value="1" <?=($message['etype_sex'][0]==1?"checked":"")?>/>
                    <label class="b-check__label b-check__label_fontsize_13" for="check_emp_sex0">Мужской</label>
                </div>
                <div class="b-check">
                    <input id="check_emp_sex1" class="b-check__input" name="etype_sex[1]" type="checkbox" value="1" <?=($message['etype_sex'][1]==1?"checked":"")?>/>
                    <label class="b-check__label b-check__label_fontsize_13" for="check_emp_sex1">Женский</label>
                </div>
            </td>
        </tr>
    </table>
</div><!-- b-layout__inner -->