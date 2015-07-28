<div id="filter_freelancer" class="b-layout__inner b-layout__inner_bordtop_c6 b-layout__inner_bordbot_c6 b-layout__inner_margbot_30 b-layout__inner_padtb_20 <?= ($message['filter_frl'] ? "":"b-layout__inner_hide")?>">
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">Аккаунт</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_10">
                        <input id="check_frl_pro" class="b-check__input" name="ftype_account[0]" type="checkbox" value="1" <?=($message['ftype_account'][0]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check_frl_pro">Профессиональный <img src="/images/icons/f-pro.png" alt=""  /></label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="check_frl_norm" class="b-check__input" name="ftype_account[1]" type="checkbox" value="1" <?=($message['ftype_account'][1]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check_frl_norm">Начальный</label>
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
                        <input id="check_frl_profile_full" class="b-check__input" name="ftype_profile[0]" type="checkbox" value="1" <?=($message['ftype_profile'][0]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check_frl_profile_full">Заполнен</label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="check_frl_profile_empty" class="b-check__input" name="ftype_profile[1]" type="checkbox" value="1" <?=($message['ftype_profile'][1]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check_frl_profile_empty">Пустой</label>
                    </div>
                </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">Портфолио</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_10">
                        <input id="check_frl_porfolio1" class="b-check__input" name="ftype_portfolio[0]" type="checkbox" value="1" <?=($message['ftype_portfolio'][0]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check_frl_porfolio1">Есть хотя бы одна работа</label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="check_frl_porfolio2" class="b-check__input" name="ftype_portfolio[1]" type="checkbox" value="1" <?=($message['ftype_portfolio'][1]==1?"checked":"")?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check_frl_porfolio2">Нет ни одной работы</label>
                    </div>
                </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Зарегистрирован</div>
            </td>
            <td class="b-layout__right">
                <table class="b-layout__table">
                    <tr>
                        <td class="b-layout__td b-layout__td_padbot_10">
                            <input id="fregdate_interval" class="b-check__input" name="fregdate_interval" type="checkbox" value="1" <?=($message['fregdate_interval']?"checked":"")?>/>
                            <label class="b-check__label b-check__label_fontsize_13" for="fregdate_interval">На сайте меньше двух месяцев</label>                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span id="i_fregdate"></span>
                            <div class="b-combo b-combo_inline-block">
                                <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['fregdate'] ? "b-combo__input_error" : "" )?>">
                                    <input id="fdate_from" class="b-combo__input-text" name="ffrom_regdate" type="text" size="80" value="<?= $message['ffrom_regdate'] ? date('d.m.Y', strtotime($message['ffrom_regdate'])) : '';?>" onChange="showHideNotImportantText(this);"/>
                                    <span class="b-combo__arrow-date"></span>
                                </div>
                            </div>
                            <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                            <div class="b-combo b-combo_inline-block">
                                <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['fregdate'] ? "b-combo__input_error" : "" )?>">
                                    <input id="fdate_to" class="b-combo__input-text" name="fto_regdate" type="text" size="80" value="<?= $message['fto_regdate'] ? date('d.m.Y', strtotime($message['fto_regdate'])) : '';?>" onChange="showHideNotImportantText(this);"/>
                                    <span class="b-combo__arrow-date"></span>
                                </div>
                            </div>
                            <span id="frl_reg_date_text" class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5 b-layout__txt_fontsize_11" <?=($message['ffrom_regdate'] || $message['fto_regdate'] ? "style='display: none'" : '')?>>&#160;&#160;не важно</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <span id="i_flastvisit"></span>
            <td class="b-layout__left b-layout__left_width_130 b-layout__left_valign_middle">
                <div class="b-layout__txt">Последний визит</div>
            </td>
            <td class="b-layout__right">
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['flastvisit'] ? "b-combo__input_error" : "" )?>">
                        <input id="ffrom_lastvisit" class="b-combo__input-text" name="ffrom_lastvisit" type="text" size="80"  value="<?= $message['ffrom_lastvisit'] ? date('d.m.Y', strtotime($message['ffrom_lastvisit'])) : ''?>" onChange="showHideNotImportantText(this);"/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date <?= ( $mailer->error['flastvisit'] ? "b-combo__input_error" : "" )?>">
                        <input id="fto_lastvisit" class="b-combo__input-text" name="fto_lastvisit" type="text" size="80"  value="<?= $message['fto_lastvisit'] ? date('d.m.Y', strtotime($message['fto_lastvisit'])) : ''?>" onChange="showHideNotImportantText(this);"/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span id="frl_lastvisit_date_text" class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5 b-layout__txt_fontsize_11" <?=($message['ffrom_lastvisit'] || $message['fto_lastvisit'] ? "style='display: none'" : '')?>>&#160;&#160;не важно</span>
            </td>
        </tr>
    </table>
    
    <? $finance_check  = "ffinance"; ?>
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message[$finance_check])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('<?= $finance_check?>').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('<?= $finance_check?>').set('value', 1);">Финансы</a>
    </div>
    <?php 
    $finance_name   = array("money"          => "ffinance_money",
                            "spend"          => "ffinance_spend[%s]",
                            "deposit"        => "ffinance_deposit[%s]",
                            "method_deposit" => "ffinance_method_deposit[%s]"
                      );
    ?>
	<? include("subfilter/tpl.finance.php"); ?>			
	<? unset($finance_name, $finance_check); ?>			
		
    <? $buying_check = "fbuying"; ?>
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message[$buying_check])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('<?= $buying_check?>').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('<?= $buying_check?>').set('value', 1);">Покупки</a>
    </div>
	<?php
    $buying_name   = array("buying"         => "fbuying_buying[%s]",
                           "period"         => "fbuying_period[%s]",
                           "type_buy"       => "fbuying_type_buy[%s]",
                           "count_buy"      => "fbuying_count_buy[%s][%s]",
                           "sum"            => "fbuying_sum[%s][%s]"
                        );
    $op_codes = mailer::$buying_freelance;
    ?>
    <? include("subfilter/tpl.buying.php"); ?>
    <? unset($buying_name, $buying_check, $op_codes); ?>			
			
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message['fproject'])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('fproject').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('fproject').set('value', 1);">Проекты</a>
    </div>
	<? include("subfilter/tpl.fproject.php"); ?>			
    
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message['fspec'])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('fspec').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('fspec').set('value', 1);">Специализация</a>
    </div>
	<? include("subfilter/tpl.specs.php"); ?>			
				
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message['fblog'])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('fblog').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('fblog').set('value', 1);">Активность в блогах</a>
    </div>
	<? include("subfilter/tpl.blogs.php"); ?>			
				
    <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_margleft_130 i-button show-settings <?= !empty($message['flocation'])?"b-layout__txt_hide":""?>">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('flocation').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('flocation').set('value', 1);">География</a>
    </div>
	<? include("subfilter/tpl.location.php"); ?>
    
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Пол</div>
            </td>
            <td class="b-layout__right">
                <div class="b-check b-check_padbot_10 b-check_padtop_4">
                    <input id="check_frl_sex1" class="b-check__input" name="ftype_sex[0]" type="checkbox" value="1" <?=($message['ftype_sex'][0]==1?"checked":"")?>/>
                    <label class="b-check__label b-check__label_fontsize_13" for="check_frl_sex1">Мужской</label>
                </div>
                <div class="b-check">
                    <input id="check_frl_sex2" class="b-check__input" name="ftype_sex[1]" type="checkbox" value="1" <?=($message['ftype_sex'][1]==1?"checked":"")?>/>
                    <label class="b-check__label b-check__label_fontsize_13" for="check_frl_sex2">Женский</label>
                </div>
            </td>
        </tr>
    </table>
</div><!-- b-layout__inner -->