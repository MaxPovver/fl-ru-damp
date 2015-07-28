<?

$is_pskb_err = false;

if ($sbr->scheme_type == sbr::SCHEME_LC) {
    $is_pskb_err = $lc['state'] == pskb::STATE_ERR;
}
?>
<div class="b-menu b-menu_crumbs">
    <ul class="b-menu__list">
								<li class="b-menu__item"><a class="b-menu__link" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>">«Мои Сделки»</a>&#160;&rarr;&#160;</li>
    </ul>
</div>			
<h1 class="b-page__title b-page__title_padnull"><?= reformat2($sbr->data['name']) ?></h1>

<? include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-user.php"); ?>

<? if ($sbr->scheme_type == sbr::SCHEME_LC) { ?>
<form id="reserveForm" action="<?= !defined('PSKB_TEST_MODE') ? onlinedengi::REQUEST_URL : onlinedengi::REQUEST_TEST_URL ?>" class="<?= $is_pskb_err ? 'b-layout_hide': ''?>" method="POST">
<? } //if ?>

<? 

if ($sbr->scheme_type == sbr::SCHEME_PDRD2) { 
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wmpay.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmpay.php");
    $wm = new wmpay();
    $pm = new pmpay();    
?>
    <form method="POST" action="https://paymaster.ru/Payment/Init" id="reserveFormWM">
        <input type="hidden" name="LMI_MERCHANT_ID" value="<?=$pm->merchants[pmpay::MERCHANT_SBR]?>" />
        <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?=round($sbr->reserve_sum, 2)?>" />
        <input type="hidden" name="LMI_CURRENCY" value="RUB" />
        <input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="<?=base64_encode(iconv('CP1251', 'UTF-8', 'Оплата по договору-оферте ' . $sbr->getContractNum() . '. ' . $ndss . '. Счет #' .$account->id. ', логин ' . $sbr->getLogin() )) ?>" />
        <input type="hidden" name="LMI_PAYMENT_NO" value="<?=$pm->genPaymentNo()?>" />
        <input type="hidden" name="LMI_SIM_MODE" value="0" />
        <input type="hidden" name="PAYMENT_BILL_NO" value="<?=$account->id?>" />
        <input type="hidden" name="OPERATION_TYPE" value="<?=sbr::OP_RESERVE?>" />
        <input type="hidden" name="OPERATION_ID" value="<?=$sbr->id?>" />
    </form>
    <form name="ydpay" method="POST" action="http://money.yandex.ru/eshop.xml" id="reserveFormYM">
        <input name="scid" value="3428" type="hidden" />
        <input type="hidden" name="ShopID" value="<?=ydpay::SHOP_SBR_RESERVE?>" />
        <input type="hidden" name="Sum" value="<?=$sbr->reserve_sum?>" />
        <input type="hidden" name="CustomerNumber" value="<?=$account->id?>" />
        <input type="hidden" name="OPERATION_TYPE" value="<?=sbr::OP_RESERVE?>" />
        <input type="hidden" name="OPERATION_ID" value="<?=$sbr->id?>" />
    </form>
    
    <script type="text/javascript">
        window.addEvent('domready', function(){
            window.finance = new Finance({form_type: '<?=$sbr->user_reqvs['form_type']?>'});
        });
    </script>
    <?php
    // проверяем, заполнены ли необходимые финансовые данные? для выбранного лица (физ/юр)
    $is_filled = explode(',',preg_replace('/[}{]/', '', $sbr->user_reqvs['is_filled']));
    $isReqvsFilled[sbr::FT_PHYS] = $is_filled[sbr::FT_PHYS - 1] == 't';
    $isReqvsFilled[sbr::FT_JURI] = $is_filled[sbr::FT_JURI - 1] == 't';
    if (!$isReqvsFilled[$sbr->user_reqvs['form_type']]) {
        $noFinanceDataPDRD = true;
    }
    ?>
    <? //sbr_meta::view_finance_popup("/" . sbr::NEW_TEMPLATE_SBR . "/?site=reserve&id=$sbr_id"); ?>
    
    <form action="." method="get" id="reserveForm">
        <input type="hidden" name="site" value="<?=$site?>" />
        <input type="hidden" name="id" value="<?=$sbr->data['id']?>" />
        <input type="hidden" name="bank" value="1" />
<? }//if?>
<script type="text/javascript">
    var WMR_SYS = <?= exrates::WMR;?>;
    var YM_SYS = <?= exrates::YM;?>;
    var BANK_SYS = <?= exrates::BANK;?>;
    var FM_SYS = <?= exrates::FM;?>;
    
    var TAX_WM = <?= ($sbr->cost * 0.03);?>;
    var TAX_YM = <?= ($sbr->cost * 0.03);?>;
</script>    
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left">
                <div class="b-fon b-fon_width_72ps b-fon_padbot_30">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                        <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>Исполнитель не приступит к работе, пока вы не зарезервируете деньги под сделку.
                    </div>
                </div>	

                <table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
                <? foreach ($sbr->stages as $num => $stage) { $stage->initNotification();?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padbot_15 b-layout__left_padright_20">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15"><a class="b-layout__link" href="?site=Stage&id=<?= $stage->data['id'] ?>"><?= reformat($stage->data['name'], 35, 0, 1) ?></a></div>
                        </td>
                        <td class="b-layout__middle b-layout__middle_width_200"><div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_bold"><?= sbr_meta::view_cost($stage->data['cost'], exrates::BANK ) ?></div></td>
                        <td class="b-layout__right b-layout__right_width_250"><div class="b-layout__txt b-layout__txt_padtop_2"><span class="b-layout__bold"><?= $stage->data['work_days'] ?> <?= ending(abs($stage->data['work_days']), 'день', 'дня', 'дней') ?></span> на задачу</div></td>
                    </tr>
                <? } ?>
                </table>

                <h2 class="b-layout__title b-layout__title_padtop_50">Резервирование денег</h2>
                
                <? if ($sbr->scheme_type == sbr::SCHEME_LC) include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.pskb-psys.php"); ?>
                <? if ($sbr->scheme_type == sbr::SCHEME_PDRD2) {
                    $t = $sbr->data['cost'];
                ?>
                
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
                            <td class="b-layout__right b-layout__right_padbot_30">
                                <div class="b-radio b-radio_layout_vertical">
                                    <div class="b-radio__item b-radio__item_padbot_5">
                                        <label class="b-radio__label b-radio__label_fontsize_13" for="form_type_juri"><?= ($sbr->user_reqvs['form_type'] == sbr::FT_JURI ? "Юридическое лицо или ИП" : "Физическое лицо")?></lable> (<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 finance-open" href="javascript:void(0)">изменить</a>)
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_160">
                            <div class="b-layout__txt">Способ оплаты
                                <div class="i-shadow i-shadow_inline-block">
                                    <span class="b-shadow__icon b-shadow__icon_top_-1 b-shadow__icon_valign_middle b-shadow__icon_quest"></span>
                                    <div class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_top_15 b-shadow_hide">
                                        <div class="b-shadow__right">
                                            <div class="b-shadow__left">
                                                <div class="b-shadow__top">
                                                    <div class="b-shadow__bottom">
                                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                            <div class="b-shadow__txt">Способ перевода денег</div>
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
                            <div class="b-radio b-radio_layout_vertical b-radio_float_left" id="type_payments_btn">
                                <div class="b-radio__item b-radio__item_padbot_10">
                                    <input id="bank" class="b-radio__input" name="cost_sys" type="radio" value="<?=  exrates::BANK; ?>" onclick="changeCostSysPdrd(this)" for_disable="0" <?= $sbr->cost_sys == exrates::BANK ? "checked" : ""?>/>
                                    <label class="b-radio__label b-radio__label_fontsize_13" for="bank">Банковский перевод</lable>
                                </div>
                                <div class="b-radio__item b-radio__item_padbot_10">
                                    <input id="ym" class="b-radio__input" name="cost_sys" type="radio" value="<?=  exrates::YM; ?>" onclick="changeCostSysPdrd(this)" for_disable="<?= sbr::FT_JURI; ?>" <?= ($sbr->user_reqvs['form_type'] == sbr::FT_JURI ? "disabled" : "")?> <?= $sbr->cost_sys == exrates::YM && $sbr->user_reqvs['form_type'] == sbr::FT_PHYS ? "checked" : ""?>/>
                                    <label class="b-radio__label b-radio__label_fontsize_13" for="ym">Яндекс.Деньги</label>
                                </div>
                                <div class="b-radio__item b-radio__item_padbot_10">
                                    <input id="wmr" class="b-radio__input" name="cost_sys" type="radio" value="<?=  exrates::WMR; ?>" onclick="changeCostSysPdrd(this)" for_disable="<?= sbr::FT_JURI; ?>" <?= ($sbr->user_reqvs['form_type'] == sbr::FT_JURI ? "disabled" : "")?> <?= $sbr->cost_sys == exrates::WMR && $sbr->user_reqvs['form_type'] == sbr::FT_PHYS ? "checked" : ""?>/>
                                    <label class="b-radio__label b-radio__label_fontsize_13" for="wmr">Webmoney, рубли</label>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                
                <div id="pdrd_finance_alert" class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padtop_20 b-layout__txt_padbot_40 b-layout__txt_padleft_20 <?= ($isReqvsFilled[$sbr->user_reqvs['form_type']] ? "b-layout__txt_hide" : "")?>">
                    <span class="b-icon b-icon_margleft_-20 b-icon_sbr_rattent"></span>
                    Вам не хватает данных на странице «<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 finance-open" href="javascript:void(0)">Финансы</a>». Пожалуйста, заполните все необходимые поля, иначе вы не сможете воспользоваться сервисом «Безопасная Сделка».
                </div>
                
                <div class="b-tax b-tax_margbot_20">
                    <div class="b-tax__fon">
                        <div class="b-tax__rama-t">
                            <div class="b-tax__rama-b">
                                <div class="b-tax__rama-l">
                                    <div class="b-tax__rama-r">
                                        <div class="b-tax__content">
                                            
                                            <?/*
                                            <? // схемы ?>
                                            <div class="sch_<?=$sch['type']?> sbr_schemes">
                                                <div class="b-tax__level b-tax__level_padbot_12">
                                                    <div class="b-tax__txt b-tax__txt_width_160 b-tax__txt_inline-block">Бюджет всех этапов</div>
                                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="sch_<?=$sch['type']?>_f"><?=(float)$sbr->data['cost']?></div>
                                                </div>
                                                <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_double">
                                                    <div class="b-tax__txt b-tax__txt_padleft_1 b-tax__txt_width_160 b-tax__txt_inline-block b-tax__txt_fontsize_11">Налоги и вычеты</div>
                                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_fontsize_11">Сумма, руб.</div>
                                                    <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11">% от бюджета проекта</div>
                                                </div>

                                                <? foreach($sbr->scheme['taxes'][1] as $tax_id=>$tax) {
                                                    $t+= ($ts = round($stage->calcTax($tax),2));
                                                    if($tax['not_used'] || $ts == 0) continue;
                                                ?>
                                                <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_padtop_15 taxrow-class" id="taxrow_<?=$sch['type'].'_'.$id?>"><div
                                                        class="b-tax__txt b-tax__txt_width_160 b-tax__txt_inline-block"><div 
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

                                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="taxsum_<?= $sch['type'] ?>_<?=$id ?>"><?= number_format($ts, 2, '.', ' ');?></div>
                                                    <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11" id="taxper_<?= $sch['type'] ?>_<?= $id ?>"><?= $tax['percent']*100 ?></div>
                                                </div>
                                                <? // строка налога ?>
                                                <? } ?>
                                            </div>
                                            <? // схемы ?>
                                            */?>
                                            <? foreach($sbr_schemes as $sch) { if(!$sch['taxes'][1]) continue;?>
                                            <? // схемы ?>
                                            <div style="display:none" class="sch_<?=$sch['type']?> sbr_schemes">
                                                <div class="b-tax__level b-tax__level_padbot_12">
                                                    <div class="b-tax__txt b-tax__txt_width_160 b-tax__txt_inline-block">Бюджет всех этапов</div>
                                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="sch_<?=$sch['type']?>_f"><?=(float)$sbr->data['cost']?></div>
                                                </div>
                                                <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_double">
                                                    <div class="b-tax__txt b-tax__txt_padleft_1 b-tax__txt_width_160 b-tax__txt_inline-block b-tax__txt_fontsize_11">Налоги и вычеты</div>
                                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_fontsize_11">Сумма, руб.</div>
                                                    <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11">% от бюджета проекта</div>
                                                </div>

                                                <? foreach($sch['taxes'][1] as $id=>$tax) { $s=$e=''; if($id==sbr::TAX_NDS) {$s='<strong>';$e='</strong>';}  ?>
                                                <? // строка налога ?>
                                                <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_padtop_15 taxrow-class" id="taxrow_<?=$sch['type'].'_'.$id?>"><div
                                                        class="b-tax__txt b-tax__txt_width_160 b-tax__txt_inline-block"><div 
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
                                                <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_160 b-tax__txt_inline-block">Итого к оплате</div>
                                                <div class="b-tax__txt b-tax__txt_inline-block"><span class="b-tax__txt b-tax__txt_bold b-tax__txt_fontsize_15" id="cost_total"><?= sbr_meta::view_cost($t, exrates::BANK)?></span></div>
                                            </div>
                                            <? // итого ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    //var finance = new Finance({form_type: '<?//=$sbr->user_reqvs['form_type']?>'});
                    var taxes   = new Taxes({
                        'cost' :        '<?= $sbr->cost;?>',
                        'rating':       '<?= $RT ?>',
                        'user':         'emp',
                        'schemes_jury' : <?= $sbr_schemes_jury; ?>,
                        'schemes_phys' : <?= $sbr_schemes_phys; ?>,
                        'scheme_type':  '<?= $sbr->data['scheme_type'];?>',
                        'form_type':    '<?= $frl_reqvs['form_type']?>'
                    });
                </script>
                <? }//if?>
                <div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padtop_20 b-layout__txt_padleft_20"><span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_oattent"></span>После резервирования вернуть деньги можно будет только через арбитраж.</div>									
                <div id="finance-err" class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padleft_20 b-layout__txt_padtop_30 b-layout_hide"><span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_rattent"></span>Произошла ошибка. <span id="finance-err-txt"></span></div>
                <div class="b-buttons b-buttons_padtop_40 b-buttons_padleft_20">
                    <a href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_disabled')) <?= $sbr->scheme_type == sbr::SCHEME_PDRD2 ? "sendReservePdrd('{$sbr->data['id']}')" : "sendReserve()"?>" class="b-button b-button_flat b-button_flat_green<?= $noFinanceDataPDRD ? " b-button_disabled" : ""?>" id="send_btn">Зарезервировать деньги
                                <img width="26" height="6" alt="" src="/css/block/b-button/b-button__load.gif" class="b-button__load b-layout_hide"></a>
                    <span id="finance-btns"><span class="b-buttons__txt b-buttons__txt_padleft_10">или</span> <a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="if(confirm('Отменить сделку?')) { submitForm($('actionSbrForm'), {action: 'status_action', cancel:1}); }">отменить сделку</a>	</span>
                    <span id="finance-btns-spinn" class="b-buttons__txt b-buttons__txt_padleft_10" style="display: none;">идет подготовка к резервированию, это может занять от нескольких секунд до минуты…</span>
                </div>
                <? if ($sbr->scheme_type == sbr::SCHEME_PDRD2 && !is_release()) { ?>
                <div class="b-buttons b-buttons_padtop_40 b-buttons_padleft_20" id="buttonBankReserved">
                    <a href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_disabled')) submitForm($('commonFrm'), {action: 'test_reserve'});" class="b-button b-button_flat b-button_flat_green">Я тестю
                                <img width="26" height="6" alt="" src="/css/block/b-button/b-button__load.gif" class="b-button__load b-layout_hide"></a>
                </div>
                <? } ?>
            </td>
        </tr>
    </table>
    <? if ($sbr->scheme_type == sbr::SCHEME_LC) { ?>
    <input type="hidden" name="project" value="<?= onlinedengi::PROJECT_ID ?>" />
    <input type="hidden" name="amount" value="<?= $sbr->getReserveSum() ?>" />
    <input type="hidden" name="nick_extra" value="<?= $sbr->id ?>" />
    <input type="hidden" name="comment" value="<?= $sbr->getContractNum() ?>" />
    <? } ?>
</form>

<form action="?id=<?= $sbr->id;?>" method="post" id="actionSbrForm">
    <input type="hidden" name="cancel" value="" />
    <input type="hidden" name="id" value="<?= $sbr->id;?>" />
    <input type="hidden" name="action" value="" />
</form>

<?php if($sbr->scheme_type == sbr::SCHEME_PDRD2) {?>
<form action="." method="post" id="commonFrm">
    <input type="hidden" name="cost_sys" id="cost_sys" value="<?=$sbr->cost_sys?>">
    <input type="hidden" name="cost_sys_set" id="cost_sys_set" value="<?=$sbr->cost_sys?>">
    <input type="hidden" name="site" value="<?=$site?>" />
    <input type="hidden" name="id" value="<?= $sbr->id;?>" />
    <input type="hidden" name="action" value="" />
</form>
<? }//if?>
<div class="b-shadow b-shadow_zindex_11 b-shadow_center b-shadow_width_450" id="auth_popup" style="display:none"></div>

<?php if($sbr->scheme_type == sbr::SCHEME_LC) {?>
<div id="reserve-error-box" class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb" style="<?= $is_pskb_err ? '' : 'display: none;'?>">
    <div class="b-layout__txt b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>Ошибка.</div>
    <? if ($lc['state'] == pskb::STATE_ERR && !$lc['dol_is_failed']) { ?>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10"><?= $lc['stateReason'] ? $lc['stateReason'] : 'Ошибка обработки платежа. Средства возвращены плательщику.' ?></div>
    <? } ?>
    <? if ($lc['state'] == pskb::STATE_ERR && $lc['dol_is_failed']) { ?>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10">Платеж был отменен, либо произошла ошибка обработки платежа.</div>
    <? } ?>
    <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-fon__link" href="javascript:void(0)" onclick="this.getParent('#reserve-error-box').hide(); if ($('reserveForm')) { $('reserveForm').show(); $('reserveForm').removeClass('b-layout_hide'); }">Выбрать другой тип оплаты</a></div>
    <div class="b-layout__txt">По всем вопросам обращайтесь в <a class="b-layout__link" href="/about/feedback/">службу поддержки</a> или к <?= webim_button(2, 'консультанту', 'b-layout__link') ?>.</div>
</div>
<? } ?>