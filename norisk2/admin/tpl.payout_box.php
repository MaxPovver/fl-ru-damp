<div class="overlay ov-out yd-overlay" id="payout_box" style="z-index:10000">
    <b class="c1"></b>
    <b class="c2"></b>
    <b class="ov-t"></b>
    <div class="ov-r">
        <div class="ov-l">
            <div class="ov-in">
                <h4>
                  Выплата <?=$GLOBALS['EXRATE_CODES'][$pmt['amt_sys']][2]?> <?=$pmt['id'] ? '№ '.$pmt['id'] : ''?> по договору <?=$sbr->getContractNum()?><br/>
                </h4>
                <div>
                  Задача: <a class="b-post__link" href="/norisk2/?site=Stage&id=<?=$stage->id?>&access=A" target="_blank"><?=$stage->getOuterNum()?></a><br/>
                  Кому: <a class="b-post__link" href="/users/<?=$sbr->data[$upfx.'login']?>/" target="_blank"><?=$sbr->data[$upfx.'uname']?> <?=$sbr->data[$upfx.'usurname']?> [<?=$sbr->data[$upfx.'login']?>]</a><br/>
                  Кошелек: <b><?=$pmt['dstacnt_nr']?></b><br/>
                  Сумма: <b><?=sbr_meta::view_cost($pmt['in_amt'], $pmt['amt_sys'], false)?></b><br/>
                  <? if($pmt['amt_sys'] == exrates::WMR) { ?>
                    <div style="vertical-align:bottom">
                      <div style="padding-top:3px;float:left" title="Лимит суммы пополнения для данного кошелька (http://www.guarantee.ru/services/users/addfunds). При установленном лимите, за один раз будет выплачиваться сумма, не превышающая его.">Лимит:</div>&nbsp;<input disabled="true" type="text"
                      id="pb_limit" value="<?=($pmt['amt_limit'] > 0 ? $pmt['amt_limit'] : '')?>" style="font-weight:bold;color:#333;width:55px"
                      onkeydown="if(event.keyCode==13)SBR.saveLimit(<?=$pmt['amt_sys']?>, <?=$stage->id?>, <?=$user_id?>, v2i($('pb_limit').value))"
                       />
                    &nbsp;
                    <a class="b-post__link b-post__link_fontsize_11 b-buttons__link_dot_0f71c8" href="javascript:;" style="text-decoration:none"
                    onclick="if($('pb_limit').disabled){$('pb_limit').disabled=false;$('pb_limit').focus();this.innerHTML='сохранить';}
                             else SBR.saveLimit(<?=$pmt['amt_sys']?>, <?=$stage->id?>, <?=$user_id?>, v2i($('pb_limit').value))
                    ">изменить</a>
                    </div>
                  <? } ?>
                  Выплачено: <b><?=sbr_meta::view_cost($pmt['out_amt'], $pmt['amt_sys'], false)?></b>
                  <div class="prc-bx">
                    <div id="payout_prc" class="prc<?=($pmt['in_amt'] <= $pmt['out_amt'] ? ' prc-c' : '')?>" style="width:<?=$pmt['out_per']?>%">&nbsp;</div>
                  </div>
                </div>
                <div style="height:95px;overflow:auto;margin-bottom:40px">
                   <? if($pmt['in_amt'] <= $pmt['out_amt']) { ?>
                     <p>Платеж завершен <?=date('d.m.Y H:i:s', strtotime($pmt['performed_dt']))?></p>
                   <? } ?>
                   <? if($pmt['errors']) { ?>
                     <p style="color:red"><?=implode('<br/>',$pmt['errors'])?></p>
                   <? } ?>
                   <? if (isset($pmt['balance']) && $pmt['balance'] < ($pmt['in_amt']-$pmt['out_amt'])) { ?>
                     <p style="color:red">Недостаточно средств на счету.</p>
                   <? } ?>
                </div>
                <div class="ov-btns">
                    <div style="text-align:right">
                      <? if (isset($pmt['balance'])) { ?>
                        <div style="float:left; line-height: 20px; <?= $pmt['balance'] < ($pmt['in_amt']-$pmt['out_amt']) ? 'color: red;' : '' ?>">
                            Баланс:  <b><?= sbr_meta::view_cost($pmt['balance'], $pmt['amt_sys'], false) ?></b>
                        </div>
                      <? } ?>
                      <? if($pmt['in_amt'] > $pmt['out_amt'] && !$stage->payouts[$user_id]['completed']) { ?>
                        <input type="button" value="<?=(
                            'Выплатить ' .
                            sbr_meta::view_cost($pmt['amt_limit'] && $pmt['amt_limit'] < $pmt['in_amt']-$pmt['out_amt'] ? $pmt['amt_limit'] : $pmt['in_amt']-$pmt['out_amt'],
                                            $pmt['amt_sys'], false))?>"
                            class="i-btn" onclick="SBR.elPayout(<?=$pmt['amt_sys']?>, this, <?=$stage->id?>, <?=$user_id?>, <?=(int)$pmt['confirmed']?>)"<?=$pmt['is_locked']=='t' ? ' disabled="true"' : ''?> />
                      <? } ?>
                      <input type="button" value="<?=($pmt['confirmed'] ? 'Отмена' : 'Закрыть')?>" class="i-btn" onclick="SBR.closePayoutPopup()" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <b class="ov-b"></b>
    <b class="c3"></b>
    <b class="c4"></b>
</div>
