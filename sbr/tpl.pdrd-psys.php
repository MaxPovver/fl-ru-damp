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
                        <input type="radio" id="rq1" class="b-radio__input b-radio__safari" name="f_rez_type" value="<?=sbr::RT_RU?>"  <?= !$sbr->user_reqvs['form_type'] ? 'filled="1"' : ''?>/>
                        <label class="b-radio__label b-radio__label_fontsize_13" for="rq1">Я подтверждаю, что являюсь резидентом Российской Федерации</label>
                    </div>
                    <div class="b-radio__item b-radio__item_padbot_5">
                        <input type="radio" id="rq2" class="b-radio__input b-radio__safari" name="f_rez_type" value="<?=sbr::RT_UABYKZ?>"  <?= !$sbr->user_reqvs['form_type'] ? 'filled="1"' : ''?>/>
                        <label class="b-radio__label b-radio__label_fontsize_13" for="rq2">Я подтверждаю, что являюсь резидентом любого другого государства,<br />кроме Российской Федерации</label>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<? } ?>
<table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
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
        <td class="b-layout__right b-layout__right_padbot_10">
            <div class="b-radio b-radio_layout_vertical b-radio_padtop_2">
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="form_type_phys" class="b-radio__input b-radio__safari" name="form_type" type="radio" onclick="setFormType(this)" value="<?= sbr::FT_PHYS?>" <?= ($sbr->user_reqvs['form_type'] == sbr::FT_PHYS ? "checked" : "")?> <?= !$isReqvsFilled[sbr::FT_PHYS] ? 'filled="1"' : ''?>/>
                    <label class="b-radio__label b-radio__label_fontsize_13" for="form_type_phys">Физическое лицо</label>
                </div>
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="form_type_juri" class="b-radio__input b-radio__safari" name="form_type" type="radio" onclick="setFormType(this);" value="<?= sbr::FT_JURI?>" <?= ($sbr->user_reqvs['form_type'] == sbr::FT_JURI ? "checked" : "")?> <?= !$isReqvsFilled[sbr::FT_JURI] ? 'filled="1"' : ''?>/>
                    <label class="b-radio__label b-radio__label_fontsize_13" for="form_type_juri">Юридическое лицо или ИП</label>
                </div>
            </div>
        </td>
    </tr>
</table>
<table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
    <tr class="b-layout__tr">
        <td class="b-layout__left b-layout__left_width_160">
            <div class="b-layout__txt b-layout__txt_relative b-layout__txt_zindex_1">Способ вывода денег
                <div class="i-shadow i-shadow_inline-block">
                    <span class="b-shadow__icon b-shadow__icon_top_-1 b-shadow__icon_valign_middle b-shadow__icon_quest"></span>
                    <div class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_top_15 b-shadow_hide">
                        <div class="b-shadow__right">
                            <div class="b-shadow__left">
                                <div class="b-shadow__top">
                                    <div class="b-shadow__bottom">
                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                            <div class="b-shadow__txt">Отметьте наиболее удобный для вас способ получения денежных средств</div>
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
        <td class="b-layout__right b-layout__right_padbot_10 b-layout__right_width_200">
            <div class="b-radio b-radio_layout_vertical b-radio_float_left  b-radio_padtop_2" id="type_payments_btn">
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="bank" class="b-radio__input b-radio__input_top_-3" name="type_payment" type="radio" value="<?=  exrates::BANK; ?>" onclick="changePaymentSys(this)" for_disable="0" checked/>
                    <label class="b-radio__label b-radio__label_fontsize_13" for="bank">Банковский перевод</label>
                </div>
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="ym" class="b-radio__input b-radio__input_top_-3" name="type_payment" type="radio" value="<?=  exrates::YM; ?>" onclick="changePaymentSys(this)" for_disable="<?= sbr::FT_JURI; ?>" <?= ($sbr->user_reqvs['form_type'] == sbr::FT_JURI) ? "disabled" : ""?>/>
                    <label class="b-radio__label b-radio__label_fontsize_13" for="ym">Яндекс.Деньги</label>
                </div>
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="wmr" class="b-radio__input b-radio__input_top_-3" name="type_payment" type="radio" value="<?=  exrates::WMR; ?>" onclick="changePaymentSys(this)" for_disable="<?= sbr::FT_JURI; ?>" <?= ($sbr->user_reqvs['form_type'] == sbr::FT_JURI) ? "disabled" : ""?>/>
                    <label class="b-radio__label b-radio__label_fontsize_13" for="wmr">Webmoney, рубли</label>
                </div>
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="fm" class="b-radio__input b-radio__input_top_-3" name="type_payment" type="radio" value="<?=  exrates::FM; ?>" onclick="changePaymentSys(this)" for_disable="<?= sbr::FT_JURI; ?>" <?= ($sbr->user_reqvs['form_type'] == sbr::FT_JURI) ? "disabled" : ""?>/>
                    <label class="b-radio__label b-radio__label_fontsize_13" for="fm">Личный счет</label>
                </div>
            </div>
        </td>
    </tr>
</table>


<div id="form_type_alert" class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padtop_20 b-layout__txt_padbot_40 b-layout__txt_padleft_20 <?= ($isReqvsFilled[$sbr->user_reqvs['form_type']] ? "b-layout__txt_hide" : "")?>">
    <span class="b-icon b-icon_margleft_-20 b-icon_sbr_rattent"></span>
    Вам не хватает данных на странице «<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 finance-open" href="javascript:void(0)">Финансы</a>». Пожалуйста, заполните все необходимые поля, иначе вы не сможете воспользоваться сервисом «Безопасная Сделка».
</div>
<div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padbot_30">После начала сделки эти настройки нельзя будет изменить.</div>
<h2 class="b-layout__title b-layout__title_padbot_15 ">Расчет гонорара</h2>

<div class="b-tax b-tax_margbot_20">
    <div class="b-tax__fon">
        <div class="b-tax__rama-t">
            <div class="b-tax__rama-b">
                <div class="b-tax__rama-l">
                    <div class="b-tax__rama-r">
                        <div class="b-tax__content">
                            <? foreach($sbr_schemes as $sch) { if(!$sch['taxes'][0]) continue;?>
                            <? // схемы ?>
                            <div style="display:none" class="sch_<?=$sch['type']?> sbr_schemes">
                                <div class="b-tax__level b-tax__level_padbot_12">
                                    <div class="b-tax__txt b-tax__txt_width_220 b-tax__txt_inline-block">Бюджет всех этапов</div>
                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="sch_<?=$sch['type']?>_f"><?=(float)$sbr->data['cost']?></div>
                                </div>
                                <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_double">
                                    <div class="b-tax__txt b-tax__txt_padleft_1 b-tax__txt_width_220 b-tax__txt_inline-block b-tax__txt_fontsize_11">Налоги и вычеты</div>
                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_fontsize_11">Сумма, руб.</div>
                                    <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11">% от бюджета проекта</div>
                                </div>

                                <? foreach($sch['taxes'][0] as $id=>$tax) { $s=$e=''; if($id==sbr::TAX_NDS) {$s='<strong>';$e='</strong>';}  ?>
                                <? // строка налога ?>
                                <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_padtop_15 taxrow-class" id="taxrow_<?=$sch['type'].'_'.$id?>"><div
                                        class="b-tax__txt b-tax__txt_width_220 b-tax__txt_inline-block"><div 
                                        class="i-shadow i-shadow_inline-block i-shadow_margleft_-16"><span
                                                class="b-shadow__icon b-shadow__icon_margright_5 b-shadow__icon_quest"></span><div
                                                class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_top_12 b-shadow_hide b-moneyinfo">
                                                <div class="b-shadow__right">
                                                    <div class="b-shadow__left">
                                                        <div class="b-shadow__top">
                                                            <div class="b-shadow__bottom">
                                                                <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                                    <div class="b-shadow__txt"><?= $tax['name'] ?></div>
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
                                        </div><?= $tax['abbr'] ?><? if ($tax['tax_id'] == 2 || $tax['tax_id'] == 3) { ?> free-lance.ru<? } ?>
                                    </div>

                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="taxsum_<?= $sch['type'] ?>_<?=$id ?>">0</div>
                                    <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11" id="taxper_<?= $sch['type'] ?>_<?= $id ?>"><?= $tax['percent']*100 ?></div>
                                </div>
                                <? // строка налога ?>
                                <? } ?>
                            </div>
                            <? // схемы ?>
                            <? } ?>

                            <? // итого ?>

                            <div class="b-tax__level b-tax__level_padtop_15" id="bank_payment_sum">
                                <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_220 b-tax__txt_inline-block">Вы получите</div>
                                <div class="b-tax__txt b-tax__txt_inline-block"><span class="b-tax__txt b-tax__txt_bold b-tax__txt_fontsize_15" id="cost_total"><?= sbr_meta::view_cost(($sbr->cost - $totalSum), $sbr->cost_sys)?></span> и <span id="rating_total">0</span> <?= ending($RT, 'балл', 'балла', 'баллов');?> рейтинга</div>
                            </div>
                            <? // итого ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padbot_10 b-layout__txt_padleft_20"><span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_oattent"></span>Внимание: Соглашаясь с данным Техническим заданием путем нажатия на кнопку «Согласиться на сделку», вы заключаете Соглашение о выполнении работы и/или оказании услуги с использованием онлайн-сервиса «Безопасная Сделка». Текст Соглашения расположен на Сайте Free-lance.ru в сети Интернет по адресу: <a class="b-layout__link " href="/agreement_escrow.pdf" target="_blank"><?=HTTP_PREFIX?>www.free-lance.ru/agreement_escrow.pdf</a>.</div>
<div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padleft_20">Настоящим Администрация Сайта предлагает Оферту на заключение Договора об использовании онлайн сервиса «Безопасная Сделка». Текст Оферты на заключение Договора об использовании онлайн сервиса «Безопасная Сделка» расположен на Сайте Free-lance.ru в сети Интернет по адресу: <a class="b-layout__link" href="<?=HTTP_PREFIX?>www.free-lance.ru/offer_work_free-lancer.pdf " target="_blank"><?=HTTP_PREFIX?>www.free-lance.ru/offer_work_free-lancer.pdf</a>. Нажимая на кнопку «Согласиться на сделку», вы принимаете условия Оферты на заключение Договора об использовании онлайн сервиса «Безопасная Сделка».</div>				 
</div>