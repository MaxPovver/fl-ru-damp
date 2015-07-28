<?
sbr_meta::getReqvFields();
$sbr->getInvoiceReqv($form_type, $reqv_mode);
?>
<div class="b-menu b-menu_crumbs">
    <ul class="b-menu__list">
								<li class="b-menu__item"><a class="b-menu__link" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>">«Мои Сделки»</a>&#160;&rarr;&#160;</li>
    </ul>
</div>			
<h1 class="b-page__title b-page__title_padnull"><?= reformat2($sbr->data['name']) ?></h1>

<? include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-user.php"); ?>

<? if ($sbr->scheme_type == sbr::SCHEME_PDRD2) { ?>
    <form action="." method="post" id="reserveFrm">
        <input type="hidden" name="site" value="<?=$site?>" />
        <input type="hidden" name="id" value="<?=$stage->data['id']?>" />
        <input type="hidden" name="bank" value="1" />
<? }//if?>
    
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_72ps">
                <div class="b-fon b-fon_width_full b-fon_padbot_30">
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
                
                <? if ($sbr->scheme_type == sbr::SCHEME_PDRD2) {
                    $t = $sbr->data['cost'];
                ?>
                <table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_160">
                            <div class="b-layout__txt">Способ оплаты</div>
                        </td>
                        <td class="b-layout__right b-layout__right_padbot_10 b-layout__right_width_200">
                            <div class="b-radio b-radio_layout_vertical b-radio_float_left" id="type_payments_btn">
                                <div class="b-radio__item b-radio__item_padbot_10">
                                    <label class="b-radio__label b-radio__label_fontsize_13" for="bank"><strong>Банковский перевод</strong></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="form_type"  value="<?= $form_type?>" id="ft<?=sbr::FT_PHYS?>"/>
                
                <div class="form-block form-reserv-params" id="ft<?=sbr::FT_PHYS?>_set"<?=($sbr->user_reqvs['form_type']==sbr::FT_PHYS ? '' : ' style="display:none"')?>>
                    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                        <tbody>
                    <? $i=0; foreach(sbr_meta::$reqv_fields[sbr::FT_PHYS] as $key=>$field) { if(!$field['bill_bound']) continue; ?>
                        <tr class="b-layout__tr">
                            <td class="b-layout__left b-layout__left_width_160">
                                <div class="b-layout__txt b-layout__txt_padtop_5">
                                    <?= $field['name']?> 
                                    <?php if($setting['name_descr'][$field['pos']]) {?>
                                        <span class="b-layout__txt b-layout__txt_padright_5 b-layout__txt_float_right"><?= $setting['name_descr'][$field['pos']]?></span>
                                    <?php }//if?>
                                </div>
                            </td>
                            <td class="b-layout__right ">
                                <div class="b-combo b-input-hint">
                                    <div class="b-combo__input b-combo__input_width_400 ">            
                                        <input type="text" id="i<?=$field['idname']?>" title="<?=$field['example']?>" class="b-combo__input-text  b-combo__input_nohintblur" name="ft<?=sbr::FT_PHYS?>[<?=$key?>]" size="80" value="<?=$sbr->reqv[sbr::FT_PHYS]->$key?>" maxlength="<?=$field['maxlength']?>" >
                                    </div> 
                                </div>
                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10">
                                    <?=$field['example']?>
                                </div>
                             </td>
                        </tr>
                    <? } ?>
                        </tbody>
                    </table>
                    <? if($sbr->reqv[sbr::FT_PHYS]->id) { ?>
                    <input type="hidden" name="ft<?=sbr::FT_PHYS?>[id]" value="<?=$sbr->reqv[sbr::FT_PHYS]->id?>" />
                    <? } ?>
                    <input type="hidden" name="ft<?=sbr::FT_PHYS?>[bank_code]" value="<?=bank_payments::BC_SB?>" />
                </div>
                <div class="form-block form-reserv-params" id="ft<?=sbr::FT_JURI?>_set"<?=($sbr->user_reqvs['form_type']==sbr::FT_JURI ? '' : ' style="display:none"')?>>
                    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                        <tbody>
                    <? $i=0; foreach(sbr_meta::$reqv_fields[sbr::FT_JURI] as $key=>$field) { if(!$field['bill_bound']) continue; ?>
                        <tr class="b-layout__tr">
                            <td class="b-layout__left b-layout__left_width_160">
                                <div class="b-layout__txt b-layout__txt_padtop_5">
                                    <?= $field['name']?> 
                                    <?php if($setting['name_descr'][$field['pos']]) {?>
                                        <span class="b-layout__txt b-layout__txt_padright_5 b-layout__txt_float_right"><?= $setting['name_descr'][$field['pos']]?></span>
                                    <?php }//if?>
                                </div>
                            </td>
                            <td class="b-layout__right ">
                                <div class="b-combo b-input-hint">
                                    <div class="b-combo__input b-combo__input_width_400 ">            
                                        <input type="text" id="i<?=$field['idname']?>" title="<?=$field['example']?>" class="b-combo__input-text  b-combo__input_nohintblur" name="ft<?=sbr::FT_JURI?>[<?=$key?>]" size="80" value="<?=$sbr->reqv[sbr::FT_JURI]->$key?>" maxlength="<?=$field['maxlength']?>" >
                                    </div> 
                                </div>
                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10">
                                    <?=$field['example']?>
                                </div>
                             </td>
                        </tr>
                    <? } ?>
                        </tbody>
                    </table>
                    <? if($sbr->reqv[sbr::FT_JURI]->id) { ?>
                    <input type="hidden" name="ft<?=sbr::FT_JURI?>[id]" value="<?=$sbr->reqv[sbr::FT_JURI]->id?>" />
                    <? } ?>
                </div>
                
                <? }//if?>

                <div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padtop_20 b-layout__txt_padleft_20"><span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_oattent"></span>После резервирования вернуть деньги можно будет только через арбитраж.</div>									
                <div id="finance-err" class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padleft_20 b-layout__txt_padtop_30 b-layout_hide"><span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_rattent"></span>Произошла ошибка. <span id="finance-err-txt"></span></div>
                <div class="b-buttons b-buttons_padtop_40 b-buttons_padleft_20">
                    <a href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_disabled')) submitForm($('reserveFrm'), {action:'invoice'});" class="b-button b-button_flat b-button_flat_green" id="send_btn">Выписать счет
                                <img width="26" height="6" alt="" src="/css/block/b-button/b-button__load.gif" class="b-button__load b-layout_hide"></a>
                    <span id="finance-btns"><span class="b-buttons__txt b-buttons__txt_padleft_10">или</span> <a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="if(confirm('Отменить сделку?')) { submitForm($('actionSbrForm'), {action: 'status_action', cancel:1}); }">отменить сделку</a>	</span>
                    <span id="finance-btns-spinn" class="b-buttons__txt b-buttons__txt_padleft_10" style="display: none;">идет подготовка к резервированию, это может занять от нескольких секунд до минуты…</span>
                </div>
            </td>
            <td class="b-layout__right"></td>
        </tr>
    </table>
   
    <input type="hidden" name="action" value="" />
    <input type="hidden" name="site" value="reserve" />
    <input type="hidden" name="reqv_mode" value="<?=$reqv_mode?>" />
    <input type="hidden" name="bank" value="1" />
    <input type="hidden" name="id" value="<?=$sbr->data['id']?>" />
    
</form>

<form action="?id=<?= $sbr->id;?>" method="post" id="actionSbrForm">
    <input type="hidden" name="cancel" value="" />
    <input type="hidden" name="id" value="<?= $sbr->id;?>" />
    <input type="hidden" name="action" value="" />
</form>

<?php if($sbr->scheme_type == sbr::SCHEME_PDRD2) {?>
<form action="." method="post" id="commonFrm">
    <input type="hidden" name="site" value="<?=$site?>" />
    <input type="hidden" name="id" value="<?= $sbr->id;?>" />
    <input type="hidden" name="action" value="" />
</form>
<? }//if?>
    
<div id="reserve-error-box" class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb" style="display:none;">
    <div class="b-layout__txt b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>Ошибка.</div>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10">Платеж был отменен, либо произошла ошибка обработки платежа.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-fon__link" href="javascript:void(0)" onclick="this.getParent('#reserve-error-box').hide(); if ($('reserveForm')) { $('reserveForm').show(); $('reserveForm').removeClass('b-layout_hide'); }">Выбрать другой тип оплаты</a></div>
    <div class="b-layout__txt">По всем вопросам обращайтесь в <a class="b-layout__link" href="/about/feedback/">службу поддержки</a> или к <?= webim_button(2, 'консультанту', 'b-layout__link') ?>.</div>
</div>