<?php


$sbr_taxes = $ps_total_sum = $sbr_taxes_sum = array();

foreach ($sbr->scheme['taxes'][$sbr->isEmp() ? sbr::EMP : sbr::FRL] as $tid => $tax) {
    foreach (pskb::$psys[$sbr->isEmp() ? pskb::USER_EMP : pskb::USER_FRL] as $k => $v) {
        $t_sum = sbr_meta::calcAnyTax($tid, $sbr->scheme['id'], $sbr->cost, array('P' => pskb::$exrates_map[$k]));
        if ($t_sum == 0) continue;
        $sbr_taxes[$k][] = $tid;
        if (!isset($ps_total_sum[$k])) {
            $ps_total_sum[$k] = $sbr->cost;
        }
        if ($sbr->isEmp()) {
            $ps_total_sum[$k] += $t_sum;
        } else {
            $ps_total_sum[$k] -= $t_sum;
        }
        $sbr_taxes_sum[$tid][$k] = $t_sum;
    }
}
            
?>

<? if (!$sbr->user_reqvs['rez_type']) { ?>
<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_160">
                <div class="b-layout__txt">Резидентство</div>
            </td>
            <td class="b-layout__right b-layout__right_padbot_30">
                <div class="b-radio b-radio_layout_vertical b-radio_padtop_2">
                    <div class="b-radio__item b-radio__item_padbot_5">
                        <input type="radio" id="rq1" class="b-radio__input b-radio__safari" name="f_rez_type" value="<?=sbr::RT_RU?>"  <?= !$sbr->user_reqvs['form_type'] ? 'filled="1"' : ''?> />
                        <label class="b-radio__label b-radio__label_fontsize_13" for="rq1">Я подтверждаю, что являюсь резидентом Российской Федерации</label>
                    </div>
                    <div class="b-radio__item b-radio__item_padbot_5">
                        <input type="radio" id="rq2" class="b-radio__input b-radio__safari" name="f_rez_type" value="<?=sbr::RT_UABYKZ?>"  <?= !$sbr->user_reqvs['form_type'] ? 'filled="1"' : ''?> />
                        <label class="b-radio__label b-radio__label_fontsize_13" for="rq2">Я подтверждаю, что являюсь резидентом любого другого государства,<br />кроме Российской Федерации</label>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<? } ?>
<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_160">
                <div class="b-layout__txt">Форма организации
                    <div class="i-shadow i-shadow_inline-block">
                        <span class="b-shadow__icon b-shadow__icon_top_-1 b-shadow__icon_valign_middle b-shadow__icon_quest"></span>
                        <div class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_top_15 b-shadow_hide b-shadow_zindex_2">
                            <div class="b-shadow__right">
                                <div class="b-shadow__left">
                                    <div class="b-shadow__top">
                                        <div class="b-shadow__bottom">
                                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                <div class="b-shadow__txt">Укажите форму организации: юридическое лицо (также в случае, если вы являетесь индивидуальным предпринимателем) или физическое лицо</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="b-shadow__tl"></div>
                            <div class="b-shadow__tr"></div>
                            <div class="b-shadow__bl"></div>
                            <div class="b-shadow__br"></div>
                            <span class="b-shadow__icon b-shadow__icon_close"></span>
                            <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                        </div>
                    </div>
                </div>
            </td>
            <td class="b-layout__middle b-layout__middle_width_200 b-layout__middle_padbot_30">
                <div class="b-radio b-radio_layout_vertical b-radio_padtop_2">
                    <div class="b-radio__item b-radio__item_padbot_5">
                        <input id="form_type_phys" class="b-radio__input b-radio__safari" name="form_type" onclick=" if($('method_any_text')) $('method_any_text').show();" type="radio" value="<?= sbr::FT_PHYS ?>" <?= ($sbr->user_reqvs['form_type'] == sbr::FT_PHYS || !$sbr->user_reqvs['form_type'] ? "checked" : "") ?> <?= !$isReqvsFilled[sbr::FT_PHYS] ? 'filled="1"' : '' ?> />
                        <label class="b-radio__label b-radio__label_fontsize_13" for="form_type_phys">Физическое лицо</label>
                    </div>
                    <div class="b-radio__item b-radio__item_padbot_5">
                        <input id="form_type_juri" class="b-radio__input b-radio__safari" name="form_type" onclick=" if($('method_any_text')) $('method_any_text').hide();" type="radio" value="<?= sbr::FT_JURI ?>" <?= ($sbr->user_reqvs['form_type'] == sbr::FT_JURI ? "checked" : "") ?> <?= !$isReqvsFilled[sbr::FT_JURI] ? 'filled="1"' : '' ?> />
                        <label class="b-radio__label b-radio__label_fontsize_13" for="form_type_juri">Юридическое лицо или ИП</label>
                    </div>
                </div>
            </td>
            <td class="b-layout__right" rowspan="2">
                <? if ($sbr->isFrl() && !$sbr->is_diff_method) { ?>
                    <?php if($sbr->is_only_ww) {?>
                    <div class="b-fon b-fon_float_left finance-min-alert1">
                        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                            <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>
                            Проведение сделок и вывод сумм возможен только при наличии идентифицированного Веб-кошелька.<br>
                            Для идентификации кошелька:<br>
                            1. Перейдите по <a class="b-layout__link" href="https://webpay.pscb.ru/login/auth" target="_blank">ссылке</a> для авторизации или регистрации кошелька на ваш номер телефона.<br>
                            2. В кошельке перейдите на <a class="b-layout__link" href="https://webpay.pscb.ru/UserProfile/identeficationWays" target="_blank">страницу идентификации</a>.<br>
                            3. Выберите способ (например, упрощенная идентификация) и пройдите идентификацию.
                            <div id="ya_pay" style="display:none"><br/><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>Чтобы получить деньги, вам необходимо принять новое <a href="http://money.yandex.ru/offer.xml?from=llim" class="b-fon__link" target="_blank">соглашение об использовании</a> сервиса &laquo;Яндекс.Деньги&raquo;.</div>
                            <div class="b-layout__bold">Обратите внимание, в этапах сделки с бюджетом менее 15 000 рублей выплата возможна только на Веб-кошелек ПСКБ.</div>
                        </div>
                    </div>
                    <?php } else { //if?>
                    <div class="b-fon b-fon_float_left finance-min-alert1">
                        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                            <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>
                            Проведение сделок и вывод сумм возможен только при наличии идентифицированного Веб-кошелька.<br>
                            Для идентификации кошелька:<br>
                            1. Перейдите по <a class="b-layout__link" href="https://webpay.pscb.ru/login/auth" target="_blank">ссылке</a> для авторизации или регистрации кошелька на ваш номер телефона.<br>
                            2. В кошельке перейдите на <a class="b-layout__link" href="https://webpay.pscb.ru/UserProfile/identeficationWays" target="_blank">страницу идентификации</a>.<br>
                            3. Выберите способ (например, упрощенная идентификация) и пройдите идентификацию.
                            
                            <div class="finance-min-alert1-more">
                            <br>                         
                            Для вывода денежных средств в размере более 15 000 рублей вы должны быть идентифицированы в платежной системе, в противном случае  
                                <div class="i-shadow i-shadow_inline-block">
                                    <span class="b-shadow__icon b-shadow__icon_top_-1 b-shadow__icon_valign_middle b-shadow__icon_quest"></span>
                                    <div class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_top_15 b-shadow_zindex_2 b-shadow_hide">
                                        <div class="b-shadow__right">
                                            <div class="b-shadow__left">
                                                <div class="b-shadow__top">
                                                    <div class="b-shadow__bottom">
                                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                            <div class="b-shadow__txt">По техническим или любого другого рода причинам</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-shadow__tl"></div>
                                        <div class="b-shadow__tr"></div>
                                        <div class="b-shadow__bl"></div>
                                        <div class="b-shadow__br"></div>
                                        <span class="b-shadow__icon b-shadow__icon_close"></span>
                                        <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                                    </div>
                                </div> оплата будет произведена по безналичному расчету или на <a href="<?= HTTP_PREFIX ?>feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/" class="b-fon__link">Веб-кошелек</a>.   
                            </div>
                        </div>
                    </div>
                    <? } ?>
                <? } ?>
                
                <? if ($sbr->isEmp()) { ?>
                <div id="card_pay" class="b-fon b-fon_float_left finance-min-alert1" style="display:none">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                        <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>Резервирование банковской картой доступно<br/>при условии ее привязки к Веб-кошельку ПСКБ.<br>Подробнее <a class="b-fon__link" href="http://feedback.fl.ru/topic/397423-privyazka-bankovskoj-kartyi-k-veb-koshelku/" target="_blank">о привязке карты</a>.
                        <br><br><span class="b-layout__bold">Также ознакомьтесь, пожалуйста, с <a class="b-fon__link" target="_blank" href="http://feedback.fl.ru/topic/397466-ogranicheniya-pri-rezervirovanii-v-bezopasnoj-sdelke/">лимитами</a><br>на резервирование денег банковской картой.</span>
                    </div>
                </div>
                <? }//if?>
            </td>            
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_160">
                <div class="b-layout__txt b-layout__txt_relative b-layout__txt_zindex_1">Способ <?=($sbr->isFrl() ? 'вывода' : 'ввода') ?> денег
                    <div class="i-shadow i-shadow_inline-block">
                    <span class="b-shadow__icon b-shadow__icon_top_-1 b-shadow__icon_valign_middle b-shadow__icon_quest"></span>
                    <div class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_top_15 b-shadow_hide">
                        <div class="b-shadow__right">
                            <div class="b-shadow__left">
                                <div class="b-shadow__top">
                                    <div class="b-shadow__bottom">
                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                            <div class="b-shadow__txt">Отметьте наиболее удобный для вас способ <?=($sbr->isFrl() ? 'получения' : 'резервирования') ?> денежных средств</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-shadow__tl"></div>
                        <div class="b-shadow__tr"></div>
                        <div class="b-shadow__bl"></div>
                        <div class="b-shadow__br"></div>
                        <span class="b-shadow__icon b-shadow__icon_close"></span>
                        <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                    </div>
                    </div>
                </div>
            </td>
            <td class="b-layout__middle b-layout__middle_width_200 b-layout__middle_padbot_30">
                <div class="b-radio b-radio_layout_vertical b-radio_padtop_2">
                    <? foreach ($paysystems['list'] as $id => $name) { ?>
                        <div class="b-radio__item b-radio__item_padbot_5">
                            <input class="b-radio__input b-radio__safari" type="radio" value="<?= $id ?>" name="mode_type" onchange="exrates_changes(this.value);" onclick=" <?= ($id==onlinedengi::CARD) ? "if($('card_pay')) $('card_pay').setStyle('display', 'block'); " : "if($('card_pay')) $('card_pay').setStyle('display', 'none'); "?> <?=(($id==onlinedengi::YD)? "if($('ya_pay')) $('ya_pay').setStyle('display', 'block')": "if($('ya_pay')) $('ya_pay').setStyle('display', 'none')")?>" id="mt<?= $id ?>" 
                                   <? if (in_array($id, $paysystems['hidden'][$sbr->user_reqvs['form_type'] ? $sbr->user_reqvs['form_type'] : sbr::FT_PHYS])) { ?>disabled='disabled'<? } ?> />
                            <label for="mt<?= $id ?>" class="b-radio__label b-radio__label_fontsize_13"><?= $name ?></label>
                        </div>
                    <? } ?>
                </div>
            </td>
        </tr>
    </tbody>
</table>

<? if($sbr->isFrl() && $sbr->is_diff_method) { 
    $pay_all = array_merge($sbr->stage_payout_ww, $sbr->stage_payout_other);
    $one_ww = (count($sbr->stage_payout_ww) == 1); 
    $one_other = (count($sbr->stage_payout_other) == 1); ?>
    <div class="b-fon b-fon_padbot_30">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
            <div class="b-fon__txt b-fon__txt_linheight_18 ">
                <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25 b-icon_top_1"></span>
                <div id="method_any_text" class="b-fon__txt b-fon__txt_padbot_5 b-fon__txt_linheight_18" <?= ($sbr->user_reqvs['form_type'] != sbr::FT_PHYS ? 'style="display:none"' : "");?>>
                    <?= $one_ww ? "Бюджет этапа" : "Бюджеты этапов" ?> <?= implode(count($sbr->stage_payout_ww) == 2 ? " и " : " , ", $sbr->stage_payout_ww); ?> меньше 15 000 руб., поэтому <?= $one_ww ? "гонорар будет перечислен" : "гонорары по ним будут перечислены"?> на Веб-кошелек. Гонорар за <?= $one_other ? "этап" : "этапы"; ?> <?= implode(count($sbr->stage_payout_other) == 2 ? " и " : ", ", $sbr->stage_payout_other); ?> будет перечислен на <span class="other_payout_method">банковский счет</span>.
                </div>  
                <div id="method_ww_text" class="b-fon__txt b-fon__txt_padbot_5 b-fon__txt_linheight_18" style="display:none">
                    Бюджеты этапов <?= implode(count($pay_all) == 2 ? " и " : " , ", $pay_all); ?> меньше 15 000 руб., поэтому гонорары по ним будут перечислены на Веб-кошелек
                </div>
                <div class="b-fon__txt b-fon__txt_linheight_18 ">
                    Проведение сделок и вывод сумм возможен только при наличии идентифицированного Веб-кошелька.<br>
                    Для идентификации кошелька:<br>
                    1. Перейдите по <a class="b-layout__link" href="https://webpay.pscb.ru/login/auth" target="_blank">ссылке</a> для авторизации или регистрации кошелька на ваш номер телефона.<br>
                    2. В кошельке перейдите на <a class="b-layout__link" href="https://webpay.pscb.ru/UserProfile/identeficationWays" target="_blank">страницу идентификации</a>.<br>
                    3. Выберите способ (например, упрощенная идентификация) и пройдите идентификацию.
                    <div class="b-layout__bold">Обратите внимание, в этапах сделки с бюджетом менее 15 000 рублей выплата возможна только на Веб-кошелек ПСКБ.</div>
                </div>
            </div>
        </div>
    </div>
<? } //?>

<div class="b-layout__inner b-layout__inner_bordtop_ced4d8 b-layout__inner_bordbot_ced4d8 b-layout__inner_margbot_30 b-layout__inner_margtop_10 b-layout__inner_relative b-layout__inner_width_72ps" id="finance_form">         
    <div class="b-layout__inner b-layout__inner_padtb_20 b-layout__inner_bordtop_ed b-layout__inner_bordbot_ed"> 
        <?
        /*<div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padbot_10 b-layout__txt_padleft_20 finance-alert">
            <span class="b-icon b-icon_top_1 b-icon_margleft_-20 b-icon_sbr_oattent"></span>Для проведения сделки будут использованы ваши данные со страницы  «<a href="/users/<?= $sbr->login ?>/setup/finance/" class="b-layout__link b-layout__link_bordbot_dot_0f71c8">Финансы</a>». 
            Пожалуйста, проверьте актуальность указанных данных.
        </div>
        <div id="form_type_alert" class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padbot_15 b-layout__txt_padleft_20 b-layout_hide no-finance-alert">
            <span class="b-icon b-icon_top_1 b-icon_margleft_-20 b-icon_sbr_rattent"></span>Вам не хватает данных на странице «<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="/users/<?= $sbr->login ?>/setup/finance/">Финансы</a>». 
            Пожалуйста, заполните всё необходимое, иначе вы не сможете воспользоваться сервисом «Сделка Без Риска».
        </div>*/
        ?>
        <div id="inline_reqvs_alert" class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padbot_30 b-layout__txt_padleft_20">
            <span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_oattent"></span>Внимательно заполните все поля ниже. После начала сделки эти настройки нельзя будет изменить.</div>
        
        
        <div id="inline_reqvs">
            <?
            if ($sbr->user_reqvs['rez_type']) {
                $reqvs_fields = pskb::getReqvsFields($sbr->user_reqvs['rez_type']);
            } else {
                $reqvs_fields = pskb::$reqvs_fields_both;
            }
            $reqv_1 = array();
            foreach ($reqvs_fields as $k => $v) {
                if (!array_key_exists($k, $sbr->user_reqvs[1])) continue;
                if (in_array('all', $v[2]) || in_array(sbr::FT_PHYS, $v[2])) {
                    $reqv_1[$k] = $sbr->user_reqvs[1][$k];
                }
            }
            $reqv_2 = array();
            foreach ($reqvs_fields as $k => $v) {
                if (!array_key_exists($k, $sbr->user_reqvs[2])) continue;
                if (in_array('all', $v[2]) || in_array(sbr::FT_JURI, $v[2])) {
                    $reqv_2[$k] = $sbr->user_reqvs[2][$k];
                }
            }
            ?>
            <? if (!sbr_meta::$reqv_fields) {
                sbr_meta::getReqvFields();
            } ?>
            <? foreach ($reqv_1 as $f_name => $f_value) { ?>
            <? if (!isset($reqvs_fields[$f_name])) continue;
            $example = sbr_meta::$reqv_fields[1][$f_name]['example'];
            $maxlength = $sbr->user_reqvs['rez_type']==2 && $f_name=='bank_rs' ? 25 : sbr_meta::$reqv_fields[1][$f_name]['maxlength'];
            $disabled = false;
            /*if($_SESSION['is_verify'] == 't' && in_array($f_name, array('fio', 'birthday', 'idcard_name', 'idcard', 'idcard_from', 'idcard_to', 'idcard_by', 'mob_phone'))) {
                $disabled = true;
            }*/
            if ( $sbr->user_reqvs["is_activate_mob"] == "t" && in_array($f_name, array('mob_phone')) ) {
                $disabled = true;
            }
            ?>
            <table cellspacing="0" cellpadding="0" border="0" 
                   class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20  b-layout_hide reqvs-fields reqvs-1-<?= implode(' reqvs-1-', $reqvs_fields[$f_name][1]) ?>">
                <tbody>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_160">
                            <div class="b-layout__txt b-layout__txt_padtop_5"><?= $reqvs_fields[$f_name][0] ?></div>
                        </td>
                        <td class="b-layout__right ">
                            <div class="b-combo b-input-hint">
                                <label onclick="$(this).getNext().getElement('input').focus()" class="b-input-hint__label b-input-hint__label_overflow_hidden <?= html_attr($f_value) != '' ? "b-input-hint__label_hide" : ""?>" for="i_1_<?= $f_name ?>" id="example-<?= $f_name ?>"><?= $example ?></label>
                                <div class="b-combo__input b-combo__input_width_<?= $f_name == 'fio' ? 400 : 190 ?> <?= $disabled ? "b-combo__input_disabled" : ""?>">
                                    <? if($disabled) { ?>
                                    <input type="text" value="<?= $f_value ?>" id="i_1_<?= $f_name ?>_dis" maxlength="<?= $maxlength; ?>" size="80" name="reqvs_1_<?= $f_name ?>_dis" <?= $disabled ? "disabled" : ""?> class="b-combo__input-text <?= $f_name == 'mob_phone' ? 'b-combo__input-text_fontsize_18' : '' ?>"/>
                                    <input type="hidden" value="<?= $f_value ?>" id="i_1_<?= $f_name ?>" maxlength="<?= $maxlength; ?>" size="80" name="reqvs_1_<?= $f_name ?>"/>
                                    <? } else {//if ?>
                                    <input type="text" value="<?= $f_value ?>" id="i_1_<?= $f_name ?>" maxlength="<?= $maxlength; ?>" size="80" name="reqvs_1_<?= $f_name ?>" <?= $disabled ? "disabled" : ""?> class="b-combo__input-text <?= $f_name == 'mob_phone' ? 'b-combo__input-text_fontsize_18' : '' ?>" onfocus="$(this).getParent('td').getElement('.finance-error').addClass('b-layout_hide')" />
                                    <? }//else?>
                                </div>
                                
                            </div>
                            <? if ($f_name == 'mob_phone' && $sbr->isFrl()) { ?>
                            <div class="b-layout__txt b-layout__txt_fontsize_11">После завершения сделки на этот номер будут высылаться СМС с кодом подтверждения.<br>Только введя этот код, вы сможете получить гонорар. С кодом в международном формате +[код страны][код оператора][телефонный номер]. Например +79031234567, +380912345678</div>
                            <? } else if($f_name == 'mob_phone' && $sbr->isEmp()) {?>
                            <div class="b-layout__txt b-layout__txt_fontsize_11">С кодом в международном формате +[код страны][код оператора][телефонный номер]. Например +79031234567, +380912345678</div>
                            <? } ?>
                            <div class="b-layout__txt b-layout_hide b-layout__txt_color_c10600 b-layout__txt_padleft_20 finance-error">
                                <span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_rattent"></span><span class="finance-error-text"></span>
                            </div>
                            <? if($f_name == 'bank_ks') { ?>
                                <div class="b-layout__txt b-layout__txt_fontsize_11">
                                    Содержит <?= $maxlength; ?> символов. Обратите внимание: к/с начинается на 30111810
                                </div>
                            <? } //if?>
                        </td>
                    </tr>
                </tbody>
            </table>  
            <? } ?>
        
            <? foreach ($reqv_2 as $f_name => $f_value) { ?>
            <? if (!isset($reqvs_fields[$f_name])) continue;
            $example = sbr_meta::$reqv_fields[2][$f_name]['example'];
            $maxlength = $sbr->user_reqvs['rez_type']==2 && $f_name=='bank_rs' ? 25 : sbr_meta::$reqv_fields[2][$f_name]['maxlength'];
            $disabled = false;
            if($_SESSION['is_verify'] == 't' && in_array($f_name, array('fio', 'birthday', 'idcard_name', 'idcard', 'idcard_from', 'idcard_to', 'idcard_by', 'mob_phone'))) {
                $disabled = true;
            }
            ?>
            <table cellspacing="0" cellpadding="0" border="0" 
                   class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20  b-layout_hide reqvs-fields reqvs-2-<?= implode(' reqvs-2-', $reqvs_fields[$f_name][1]) ?>">
                <tbody>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_160">
                            <div class="b-layout__txt b-layout__txt_padtop_5"><?= $f_name == 'fio' ? 'ФИО представителя' : $reqvs_fields[$f_name][0] ?></div>
                        </td>
                        <td class="b-layout__right ">
                            <div class="b-combo b-input-hint">
                                <label onclick="$(this).getNext().getElement('input').focus()" class="b-input-hint__label b-input-hint__label_overflow_hidden <?= html_attr($f_value) != '' ? "b-input-hint__label_hide" : ""?>" for="i_2_<?= $f_name ?>" id="example-<?= $f_name ?>"><?= ( $f_name != 'bank_rf_bik' ? $example : '123456789' ) ?></label>
                                <div class="b-combo__input b-combo__input_width_<?= $f_name == 'full_name' || $f_name == 'address_jry' || $f_name == 'address_fct' ? 400 : 190 ?> <?= $disabled ? "b-combo__input_disabled" : ""?>">
                                    <? if($disabled) { ?>
                                    <input type="text" value="<?= $f_value ?>" id="i_2_<?= $f_name ?>_dis" maxlength="<?= $maxlength; ?>" size="80" name="reqvs_2_<?= $f_name ?>_dis" class="b-combo__input-text <?= $f_name == 'mob_phone' ? 'b-combo__input-text_fontsize_18' : '' ?>" <?= $disabled ? "disabled" : ""?> />
                                    <input type="hidden" value="<?= $f_value ?>" id="i_2_<?= $f_name ?>" maxlength="<?= $maxlength; ?>" size="80" name="reqvs_2_<?= $f_name ?>"/>
                                    <? } else{ ?>
                                    <input type="text" value="<?= $f_value ?>" id="i_2_<?= $f_name ?>" maxlength="<?= $maxlength; ?>" size="80" name="reqvs_2_<?= $f_name ?>" class="b-combo__input-text <?= $f_name == 'mob_phone' ? 'b-combo__input-text_fontsize_18' : '' ?>" <?= $disabled ? "disabled" : ""?> onfocus="$(this).getParent('td').getElement('.finance-error').addClass('b-layout_hide')" />
                                    <? }//if?>
                                </div>
                                
                            </div>
                            <? if ($f_name == 'mob_phone' && $sbr->isFrl()) { ?>
                            <div class="b-layout__txt b-layout__txt_fontsize_11">После завершения сделки на этот номер будут высылаться СМС с кодом подтверждения.<br>Только введя этот код вы сможете получить гонорар.</div>
                            <? } ?>
                            <div class="b-layout__txt b-layout_hide b-layout__txt_color_c10600 b-layout__txt_padleft_20 finance-error">
                                <span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_rattent"></span><span class="finance-error-text"></span>
                            </div>
                            <? if($f_name == 'bank_ks') { ?>
                                <div class="b-layout__txt b-layout__txt_fontsize_11">
                                    Содержит <?= $maxlength; ?> символов. Обратите внимание: к/с начинается на 30111810
                                </div>
                            <? } //if?>
                            <? if($f_name == 'bank_rf_bik') { ?>
                                <div class="b-layout__txt b-layout__txt_fontsize_11">
                                    <?= $example;?>
                                </div>
                            <? } //if?>
                        </td>
                    </tr>
                </tbody>
            </table>  
            <? } ?>

            <!--<a class="b-button b-button_margleft_-2 b-button_rectangle_color_transparent " id="finance-update-btn"
               href="javascript:void(0)" 
               onclick="setReqvs(this);">
                <span class="b-button__b1">
                    <span class="b-button__b2">
                        <span class="b-button__txt">Изменить реквизиты</span>
                    </span>
                </span>
            </a>-->
        </div>
        <? if(!$sbr->is_diff_method) { ?>
        <div class="b-layout__nosik b-layout__nosik_1" id="finance-noss"></div>
        <? }//if?>
    </div>
</div>
<?php 

/*
<div id="webwallet-note" class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padbot_30 b-layout__txt_padleft_20 b-layout_hide">
            <span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_oattent"></span>Обратите внимание:
<?php if($sbr->isFrl() && $sbr->is_only_ww) {?>
Если размер гонорара за этап составляет сумму до 15 000 рублей включительно, денежные средства будут выплачены на <a class="b-layout__link" href="<?= HTTP_PREFIX ?>feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/">Веб-кошелек</a>.
<?php } ?>
Для неперсонифицированных Веб-кошельков действует ограничение на переводы за календарный месяц &ndash; 40 000 рублей. 
Если суммарный приход средств на ваш счет превысит данную сумму, вам будет необходимо <a class="b-layout__link" href="<?= HTTP_PREFIX ?>feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/">пройти идентификацию в системе Веб-кошелек</a>.
</div>
*/ 

if($sbr->isFrl() && $sbr->is_diff_method) {
    foreach($sbr->stages as $_stage) {
        foreach($sbr_taxes as $ps => $sbr_tax) {
            $dvals = array('P' => ( $_stage->cost <= pskb::WW_ONLY_SUM && $sbr->user_reqvs['form_type'] == sbr::FT_PHYS ? exrates::WEBM : pskb::$exrates_map[$ps] ) );
            print $_stage->viewTaxesInfoMaster($dvals, $ps);
        }
    }
    
} else {
    foreach($sbr_taxes as $ps => $sbr_tax) { ?>

<div class="b-tax b-tax_margbot_20 b-layout_hide finance-taxrows" id="taxrow_ps_<?= $ps ?>">
    <div class="b-tax__fon">
        <div class="b-tax__rama-t">
            <div class="b-tax__rama-b">
                <div class="b-tax__rama-l">
                    <div class="b-tax__rama-r">
                        <div class="b-tax__content">
                            <div>
                                <div class="b-tax__level b-tax__level_padbot_12">
                                    <div class="b-tax__txt b-tax__txt_width_220 b-tax__txt_inline-block">Бюджет всех этапов</div>
                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="sch_<?= $sbr->scheme["type"] ?>_f"><?= sbr_meta::view_cost($sbr->data['cost'], exrates::BANK) ?></div>
                                </div>
                                <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_double">
                                    <div class="b-tax__txt b-tax__txt_padleft_1 b-tax__txt_width_220 b-tax__txt_inline-block b-tax__txt_fontsize_11">Налоги и вычеты</div>
                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_fontsize_11">Сумма, руб.</div>
                                    <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11">% от бюджета проекта</div>
                                </div>
                                <? foreach($sbr->scheme['taxes'][$sbr->isEmp() ? sbr::EMP : sbr::FRL] as $id=>$tax) { if (!in_array($id, $sbr_tax)) continue;  ?>
                                <? // строка налога ?>
                                <div class="b-tax__level <?= ($id==sbr::TAX_NDS ? "b-tax__level_bordtop_9ea599" : "");?> b-tax__level_padbot_12 b-tax__level_padtop_15">
                                    <div class="b-tax__txt b-tax__txt_width_220 b-tax__txt_inline-block">
                                        <?= $tax['name'] ?>
                                    </div>
                                        <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="taxsum_<?= $sch['type'] ?>_<?=$id ?>"><?= sbr_meta::view_cost($sbr_taxes_sum[$id][$ps]) ?></div>
                                        <div class="b-tax__txt b-tax__txt_width_150 b-tax__txt_inline-block b-tax__txt_fontsize_11" id="taxper_<?= $sch['type'] ?>_<?= $id ?>">
                                        <?php if($id==sbr::TAX_NDS) { ?>
                                            <?= $tax['percent']*100 ?>% от бюджета + налоги
                                        <?php } else {//if?>
                                            <?= $tax['percent']*100 ?>
                                        <?php }//else?>
                                    </div>
                                </div>
                                <? // строка налога ?>
                                <? } ?>
                            </div>

                            <? if ($sbr->isEmp()) { ?>
                            <div class="b-tax__level b-tax__level_padtop_15">
                                <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_220 b-tax__txt_inline-block">Итого к оплате</div>
                                <div class="b-tax__txt b-tax__txt_inline-block"><span class="b-tax__txt b-tax__txt_bold b-tax__txt_fontsize_15"><?= sbr_meta::view_cost($ps_total_sum[$ps], exrates::BANK) ?></span> </div>
                            </div>
                            <? } ?>

                            <? if ($sbr->isFrl()) { ?>
                            <div class="b-tax__level b-tax__level_padtop_15">
                                <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_220 b-tax__txt_inline-block">Вы получите</div>
                                <div class="b-tax__txt b-tax__txt_inline-block"><span class="b-tax__txt b-tax__txt_bold b-tax__txt_fontsize_15"><?= sbr_meta::view_cost($ps_total_sum[$ps], exrates::BANK)?></span> и <span><?= $RT ?></span> <?= ending($RT, 'балл', 'балла', 'баллов');?> рейтинга</div>
                            </div>
                            <? } ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>	
<? } ?>
<? }//if?>
    
<?php if($sbr->isFrl()) { ?>
<div class="b-tax b-tax_margbot_20 b-layout_hide finance-taxrows" id="taxrow_ps_0">
    <div class="b-tax__fon">
        <div class="b-tax__rama-t">
            <div class="b-tax__rama-b">
                <div class="b-tax__rama-l">
                    <div class="b-tax__rama-r">
                        <div class="b-tax__content">
                            <div>
                                <div class="b-tax__level b-tax__level_padbot_12">
                                    <div class="b-tax__txt b-tax__txt_width_220 b-tax__txt_inline-block">Бюджет всех этапов</div>
                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="sch_<?= $sbr->scheme["type"] ?>_f"><?= sbr_meta::view_cost($sbr->data['cost'], exrates::BANK) ?></div>
                                </div>
                            </div>
                            <div class="b-tax__level b-tax__level_padtop_15">
                                <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_220 b-tax__txt_inline-block">Вы получите</div>
                                <div class="b-tax__txt b-tax__txt_inline-block"><span class="b-tax__txt b-tax__txt_bold b-tax__txt_fontsize_15"><?= sbr_meta::view_cost($sbr->cost, exrates::BANK)?></span> и <span><?= $RT ?></span> <?= ending($RT, 'балл', 'балла', 'баллов');?> рейтинга</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>	
<?php }//if?>

<script>
    var psysDisabled = <?= json_encode($paysystems['disabled']) ?>;
    var psysHidden = <?= json_encode($paysystems['hidden']) ?>;
    var taxesMap = <?= json_encode($sbr_taxes) ?>;
//    alert(taxesMap['263']);
    var exrates_changes = function(exrate) {
        var exrates_name = 'банковский счет';
        switch(exrate) {
            <? foreach(pskb::$exrates_map as $k=>$v) { ?>
            case '<?= $k;?>':
                exrates_name = '<?= sbr_meta::view_type_payment($v);?>';    
                break;
            <? }//foreach?>
        }
        
        if(exrate == '<?= pskb::WW?>' && $('method_any_text')) {
            $('method_any_text').hide();
            //$('method_ww_text').show();
        } else if($('method_any_text')) {
            $('method_any_text').show();
            //$('method_ww_text').hide();
        }
        
        if($$('.other_payout_method')) {
            $$('.other_payout_method').set('html', exrates_name);
        }
    };
    
    var setReqvs = function(btn) {
        if (!$('inline_reqvs')) {
            return false;
        }
        
        finance_spinn_show();
        
//        var btn = $('finance-update-btn');
//        if (btn.hasClass('b-button_disabled')) {
//            return false;
//        }
//        btn.addClass('b-button_disabled');
        
        var params = {};
        var ft = document.getElement('input[name=form_type]:checked').get('value');
        if (!ft) {
            return false;
        }
        params['form_type'] = ft;
        
        if ($$('input[name=f_rez_type]').length) {
            var rez = document.getElement('input[name=f_rez_type]:checked').get('value');
            params['rez_type'] = rez;
        }
        
        $$('#inline_reqvs input').each(function(el) {
            if (el.getParent('table').hasClass('b-layout_hide')) {
                return;
            }
            if (el.get('name').contains('reqvs_' + ft + '_')) {
                params[el.get('name').replace('reqvs_' + ft + '_', '')] = el.get('value');
                
            }
        });
        console.log(params);
        xajax_setReqvs(<?= (int)$sbr->data['id'] ?>, params);
        return false;
    };
    
    var finance_err_set = function(params, ft) {
        if (!params) {
            return false;
        }
        finance_spinn_hide();
        JSScroll($('finance_form'));
        
        Object.each(params, function(mess, fname) {
//            console.log('input[name=reqvs_' + ft +'_' + fname +']');
            var el = document.getElement('input[name=reqvs_' + ft +'_' + fname +']');
            if (!el) return;
            
            // обводим инпут красным
            el.getParent('.b-combo__input').addClass('b-combo__input_error');
            // добавляем текст ошибки
            var td = el.getParent('td');
            if (!td) return;
            var error = td.getElement('.finance-error');
            if (!error) return;
            error.removeClass('b-layout_hide');
            error.getElement('.finance-error-text').set('text', mess);
        });
    };
    
    var finance_check = function(evt, scroll_to) {
        var rez, ft, mt;
        
        $$('.reqvs-fields').addClass('b-layout_hide');
        $$('.finance-alert').removeClass('b-layout_hide');
        $$('.no-finance-alert').addClass('b-layout_hide');
        //$$('.finance-min-alert1, .finance-min-alert2').addClass('b-layout_hide');
        $$('.finance-min-alert1-more').addClass('b-layout_hide');
        
        $$('input[name=mode_type]').set('disabled', false);
        
        if ($$('input[name=f_rez_type]').length) {
            if (!$$('input[name=f_rez_type]:checked').length) {
                $$('input[name=f_rez_type]:not(:disabled)')[0].set('checked', true);
            }
            rez = document.getElement('input[name=f_rez_type]:checked').get('value');
        }
        
        
        if ($$('input[name=form_type]').length) {
            if (!$$('input[name=form_type]:not(:disabled):checked').length) {
                $$('input[name=form_type]:not(:disabled)')[0].set('checked', true);
            }
            ft = document.getElement('input[name=form_type]:checked').get('value');
        }
        
        psysHidden[ft].each(function(val) {
            if (!$('mt' + val)) return;
            $('mt' + val).set('disabled', true);
        });
        //$$('.finance-min-alert' + ft).removeClass('b-layout_hide');
        $$('.finance-min-alert' + ft + '-more').removeClass('b-layout_hide');
        
        // переносим номер телефона из бывшего лица (физ/юр) в новое
        var prevFormType = ft == 1 ? 2 : 1; // предыдущий тип лица
        var prevPhone = $$('input[name=reqvs_' + prevFormType + '_mob_phone]')[0].get('value'); // телефон предыдущего лица
        $$('input[name=reqvs_' + ft + '_mob_phone]')[0].set('value', prevPhone);
        
        if ($$('input[name=mode_type]').length) {
            if (!$$('input[name=mode_type]:not(:disabled):checked').length) {
                $$('input[name=mode_type]:not(:disabled)')[0].set('checked', true);
            }
            mt = document.getElement('input[name=mode_type]:checked').get('value');
        
            if ($('finance-noss')) {
                $('finance-noss').set('class', 'b-layout__nosik b-layout__nosik_' + ($$('input[name=mode_type]').indexOf(document.getElement('input[name=mode_type]:checked')) + 1));
            }
        }
        
        $$('.finance-taxrows').addClass('b-layout_hide');
        if (taxesMap[mt]) {
            <? if($sbr->is_diff_method) { ?>
            if(ft == 1) {
                $$('.taxrow_WW').removeClass('b-layout_hide');
                $$('.taxrow_ps_WW_' + mt).removeClass('b-layout_hide');
            } else {
                $$('.taxrow_ps_' + mt).removeClass('b-layout_hide');
            }
            <? } else { //if?>
            $('taxrow_ps_' + mt).removeClass('b-layout_hide');
            <? }//else?>
        } else if($('taxrow_ps_0')) {
            $('taxrow_ps_0').removeClass('b-layout_hide');
        }
        
        $$('.reqvs-' + ft + '-all').removeClass('b-layout_hide');
        $$('.reqvs-' + ft + '-' + mt).removeClass('b-layout_hide');
        
        // скрываем все поля не относящиеся для этого типа резидентства
        if (rez) {
            var rezStr = rez == 1 ? 'not_rezident' : 'rezident';
            $$('.reqvs-' + ft + '-' + rezStr).addClass('b-layout_hide');
        }
        
//        if ($('send_btn')) $('send_btn').removeClass('b-button_rectangle_color_disable');
//        if ($('agree_btn')) $('agree_btn').removeClass('b-button_rectangle_color_disable');
        errs = 0;
        if (psysDisabled[ft].contains(parseInt(mt))) {
            $$('.finance-alert').addClass('b-layout_hide');
            $$('.no-finance-alert').removeClass('b-layout_hide');
            errs++;
//            if ($('send_btn')) $('send_btn').addClass('b-button_rectangle_color_disable');
//            if ($('agree_btn')) $('agree_btn').addClass('b-button_rectangle_color_disable');
        }
//        console.log(errs == 0);
        if (errs > 0) {
            finance_spinn_hide();
        }
        if (errs > 0 && scroll_to) {
            JSScroll($('finance_form'));
        }
        return errs == 0;
    };
    
    // синхронизация телефонов между типами лиц при их редактировании
    window.addEvent('domready', function(){
        $$('input[name$=_mob_phone]').addEvent('change', function(){
            var phone = $(this).get('value');
            $$('input[name$=_mob_phone]').set('value', phone);
        })
    });
    
    var finance_spinn_show = function() {
        if (!$('send_btn')) {
            return false;
        }
        if ($('finance-err')) {
            $('finance-err').addClass('b-layout_hide');
        }
        $('send_btn').addClass('b-button_rectangle_color_disable');
        $('send_btn').getElement('.b-button__txt').addClass('b-button__txt_hide');
        $('send_btn').getElement('img.b-button__load').removeClass('b-layout_hide');
        
        if ($('finance-btns'))
            $('finance-btns').hide();
        if ($('finance-btns-spinn'))
            $('finance-btns-spinn').setStyle('display', '');
    };
    
    var finance_spinn_hide = function() {
        if (!$('send_btn')) {
            return false;
        }
        if($('sbr_agree_frl') && $('sbr_agree_frl').checked == true) {
            $('send_btn').removeClass('b-button_rectangle_color_disable');
        }
        $('send_btn').getElement('.b-button__txt').removeClass('b-button__txt_hide');
        $('send_btn').getElement('img.b-button__load').addClass('b-layout_hide');
        
        if ($('finance-btns'))
            $('finance-btns').setStyle('display', '');
        if ($('finance-btns-spinn'))
            $('finance-btns-spinn').hide();
    };
    
    $$('input[name=form_type]').addEvent('change', finance_check);
    $$('input[name=mode_type]').addEvent('click', function() {
        finance_check(); 
    });
    $$('input[name=f_rez_type]').addEvent('change', finance_check);
    
    window.addEvent('domready', function() {
        finance_check();
//        $$('input[name=form_type]:checked').fireEvent('change');
    });
</script>
<? if ($sbr->isEmp()) { ?>
<script>
    
    var finance_add_fld = function(fname, val) {
        if (!$('reserveForm')) {
            return false;
        }
        el = $('reserveForm').getElement('input[name=' + fname + ']');
        if (!el) {
            var el = new Element('input', {
                'type': 'hidden',
                'name': fname,
                'value': val
            });
            el.inject($('reserveForm'));
        } else {
            el.set('value', val);
        }
    };
    
    var finance_send_frm = function() {
        if (!$('reserveForm')) {
            return false;
        }
        var _frm = new Element('form', {
            'method': 'post',
            'action': $('reserveForm').get('action')
        });
        var reqv = ['mode_type', 'project', 'amount', 'nick_extra', 'comment', 'source', 'order_id', 'nickname', 'u_token_key'];
        var elems = [];
        $$('#reserveForm input[type=hidden], #reserveForm input[type=text], #reserveForm input[type=radio]:checked').each(function(el) {
            if (reqv.contains(el.get('name'))) {
                var inp = new Element('input', {
                    'type': 'hidden',
                    'name': el.get('name'),
                    'value': el.get('value')
                })
                inp.inject(_frm);
            }
        });
        try {
            _frm.inject(document.body);
            _frm.submit();
        } catch(e) {}
    };
    
    var finance_raise_err = function(msg) {
        if (!$('finance-err')) {
            return false;
        }
        $('finance-err').getElement('#finance-err-txt').set('html', msg);
        $('finance-err').removeClass('b-layout_hide');
        
        finance_spinn_hide();
    };
    
    var finance_prepare = function() {
        mt = document.getElement('input[name=mode_type]:checked');
        if (!mt) {
            return false;
        }
        mt = mt.get('value');
        
        finance_spinn_show();
        xajax_preparePayment(<?= (int)$sbr->data['id'] ?>, mt);
    };
    
    var finance_check_lc = function (sbr_id, delay) {
        if (delay) {
            xajax_checkPayment.delay(2000, this, sbr_id);
        } else {
            xajax_checkPayment(sbr_id, 1);
        }
    };
        
    window.addEvent('domready', function() {
        if ($('send_btn')) {
            $('send_btn').set('onclick', "if(!$(this).hasClass('b-button_rectangle_color_disable')) setReqvs();");
        }
    });
</script>
<? } else { ?>
<script>
    
    var finance_add_fld = function(fname, val) {
        var frm = $('currentsFrm<?= $sbr->data['id'] ?>');
        if (!frm) {
            return false;
        }
        el = frm.getElement('input[name=' + fname + ']');
        if (!el) {
            var el = new Element('input', {
                'type': 'hidden',
                'name': fname,
                'value': val
            });
            el.inject(frm);
        } else {
            el.set('value', val);
        }
    };
    
    var finance_prepare = function() {
        if (!$('currentsFrm<?= $sbr->data['id'] ?>')) {
            return false;
        }
        $('currentsFrm<?= $sbr->data['id'] ?>').submit();
    };
    
    window.addEvent('domready', function() {
        if ($('agree_btn')) {
            $('agree_btn').set('id', 'send_btn');
            $('send_btn').set('onclick', "if(!$(this).hasClass('b-button_rectangle_color_disable')) {finance_add_fld('ok', '1'); setReqvs();}");
        }
    });
</script>
<? } ?>

<? if ($sbr->isEmp())  include_once $_SERVER['DOCUMENT_ROOT'] . '/sbr/employer/tpl.pskb-cards.php'; ?>