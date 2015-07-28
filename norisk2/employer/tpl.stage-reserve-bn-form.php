<form action="." method="post" id="reserveFrm">
    <div class="form-h">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-h-in">
            <h3>Резервирование денег по безналичному расчету.</h3>
        </div>
    </div>
    <div class="form-in">
        <? if($sbr->reserved_id && ($sbr->reqv[sbr::FT_JURI]->payed_time && $sbr->reqv[sbr::FT_JURI]->sbr_id == $sbr->id || $sbr->reqv[sbr::FT_PHYS]->accepted_time && $sbr->reqv[sbr::FT_PHYS]->sbr_id == $sbr_id)) { ?>
            <div class="form-block first">
                <div class="form-el">
                    <p>Вы уже зарезервировали деньги.</p>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el">
                    <div class="nr-prj-btns c">
                        <span class="btn-o-green">
                            <a href="javascript:;" onclick="SBR.sendForm({action:'show_invoiced'},true)" class="btnr btnr-green2"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Выписать счет</span></span></span></a>
                        </span>
                        <input type="hidden" name="form_type" value="<?=$form_type?>" />
                    </div>
                </div>
            </div>
        <? } else { ?>
            <div class="form-block first">
                <div class="form-el">
                    <p>Заполните и проверьте правильность заполнения полей, это важно.</p>
                </div>
            </div>
            <?
            /*
            <div class="form-block">
                <div class="form-el">
                    <? if($reqv_mode == 1) { ?><strong>Основные реквизиты</strong><? } else { ?><a href="javascript:;" onclick="SBR.switchReqvMode(<?=$stage_id?>,1)" class="lnk-dot-green">Основные реквизиты</a><? } ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <? if($reqv_mode == 2) { ?><strong>Последние введенные данные</strong><? } else {?><a href="javascript:;" onclick="SBR.switchReqvMode(<?=$stage_id?>,2)" class="lnk-dot-green">Последние введенные данные</a><? } ?>
                </div>
            </div>
            */?>
            <div class="form-block form-reserv-params">
                <div class="form-el odd">
                    <input type="radio" name="form_type" <?=($form_type != sbr::FT_JURI ? ' disabled="true"' : '')?> value="<?=sbr::FT_JURI?>" id="ft<?=sbr::FT_JURI?>" <?=($form_type == sbr::FT_JURI ? ' checked="true"' : '')?> onclick="SBR.switchReqvFT(<?=sbr::FT_JURI?>,<?=sbr::FT_PHYS?>)" /><label for="ft<?=sbr::FT_JURI?>"> Юридическое лицо или ИП</label>&nbsp;&nbsp;&nbsp;
                    <input type="radio" name="form_type" <?=($form_type != sbr::FT_PHYS ? ' disabled="true"' : '')?> value="<?=sbr::FT_PHYS?>" id="ft<?=sbr::FT_PHYS?>" <?=($form_type == sbr::FT_PHYS ? ' checked="true"' : '')?> onclick="SBR.switchReqvFT(<?=sbr::FT_PHYS?>,<?=sbr::FT_JURI?>)" /><label for="ft<?=sbr::FT_PHYS?>"> Физическое лицо</label>
                </div>
            </div>
            <div class="form-block form-reserv-params" id="ft<?=sbr::FT_PHYS?>_set"<?=($sbr->user_reqvs['form_type']==sbr::FT_PHYS ? '' : ' style="display:none"')?>>
                <? $i=0; foreach(sbr_meta::$reqv_fields[sbr::FT_PHYS] as $key=>$field) { if(!$field['bill_bound']) continue; ?>
                    <div class="form-el <?=(($i++ % 2) ? 'odd' : 'even')?>">
                        <label class="form-label3"><?=$field['name']?></label>
                        <span class="form-input">
                            <input type="text" name="ft<?=sbr::FT_PHYS?>[<?=$key?>]" value="<?=$sbr->reqv[sbr::FT_PHYS]->$key?>" maxlength="<?=$field['maxlength']?>" />
                        </span>
                        <div class="tip"></div>
                        <span class="form-hint" style="width:300px;margin:3px 0 0 0"><?=$field['example']?></span>
                    </div>
                <? } ?>
                <? if($sbr->reqv[sbr::FT_PHYS]->id) { ?>
                   <input type="hidden" name="ft<?=sbr::FT_PHYS?>[id]" value="<?=$sbr->reqv[sbr::FT_PHYS]->id?>" />
                <? } ?>
                <input type="hidden" name="ft<?=sbr::FT_PHYS?>[bank_code]" value="<?=bank_payments::BC_SB?>" />
            </div>
            <div class="form-block form-reserv-params" id="ft<?=sbr::FT_JURI?>_set"<?=($sbr->user_reqvs['form_type']==sbr::FT_JURI ? '' : ' style="display:none"')?>>
                <? $i=0; foreach(sbr_meta::$reqv_fields[sbr::FT_JURI] as $key=>$field) { if(!$field['bill_bound']) continue; ?>
                    <div class="form-el <?=(($i++ % 2) ? 'odd' : 'even')?>">
                        <label class="form-label3"><?=$field['name']?></label>
                        <span class="form-input">
                            <input type="text" name="ft<?=sbr::FT_JURI?>[<?=$key?>]" value="<?=$sbr->reqv[sbr::FT_JURI]->$key?>" maxlength="<?=$field['maxlength']?>" />
                        </span>
                        <div class="tip"></div>
                        <span class="form-hint" style="width:300px;margin:3px 0 0 0"><?=$field['example']?></span>
                    </div>
                <? } ?>
                <? if($sbr->reqv[sbr::FT_JURI]->id) { ?>
                  <input type="hidden" name="ft<?=sbr::FT_JURI?>[id]" value="<?=$sbr->reqv[sbr::FT_JURI]->id?>" />
                <? } ?>
            </div>
            <div class="form-block last">
                <div class="form-el">
                    <label class="nr-res-check" style="display:none">
                        <input type="checkbox" name="save_finance" value="1" checked="true" />&nbsp;Внести изменения в основные реквизиты
                    </label>
                    <div class="nr-prj-btns c">
                        <span class="btn-o-green">
                            <a href="javascript:;" onclick="SBR.sendForm({action:'invoice'},true)" class="btnr btnr-green2"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Выписать счет</span></span></span></a>
                        </span>
                    </div>
                </div>
            </div>
        <? } ?>

    </div>
    <b class="b2"></b>
    <b class="b1"></b>
    <input type="hidden" name="action" value="" />
    <input type="hidden" name="site" value="Stage" />
    <input type="hidden" name="reqv_mode" value="<?=$reqv_mode?>" />
    <input type="hidden" name="bank" value="1" />
    <input type="hidden" name="id" value="<?=$stage_id?>" />
</form>
<form action="." method="post" id="draftFrm">
    <input type="hidden" name="site" value="Stage" />
    <input type="hidden" name="id" value="<?=$stage_id?>" />
    <input type="hidden" name="action" value="draft" />
</form>
