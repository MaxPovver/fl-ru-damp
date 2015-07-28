<style type="text/css">
@media print {
    *<.nr-a-tbl {display:none}
}
</style>
<script type="text/javascript">
window.addEvent('domready', function() {
    if($(document.body).getElement('.container')) {
        var size = $(document.body).getElement('.container').getScrollSize();
        $('expose').setStyles({
            'width': size.x + 'px',
            'height': size.y + 'px'
        });
    }
});
<?php if($sbr->isAdmin()) {?>
Sbr.prototype.elPayout
=function(type, itm, sid, uid, cfm) {
    itm.disabled=true;
    var prc = document.getElementById('payout_prc');
    prc.className += ' prc-a';
    xajax_elPayout(type, sid, uid, cfm);
}


Sbr.prototype.saveLimit
=function(type,sid,uid,limit) {
    xajax_saveLimit(type,sid,uid,limit);
}

Sbr.prototype.openPayoutPopup
=function(type,sid,uid,html,cmpl,spcmpl) {
    var server=(html==null);
    var pspb,spb=document.getElementById('sp_btn'+sid+'_'+uid);
    if(server) {
        xajax_openPayoutPopup(type,sid,uid);
        return;
    }
    document.getElementById('payout_box').innerHTML = html;
    document.getElementById('expose').style.display='block';
    var bp,b=document.getElementById(type+'_btn'+sid+'_'+uid);
    if(b) {
        bp = b.parentNode;
        if(cmpl) bp.innerHTML='';
        if(spcmpl) bp.innerHTML=spcmpl;
    }
}
Sbr.prototype.closePayoutPopup
=function() {
    document.getElementById('expose').style.display='none';
    document.getElementById('payout_box').innerHTML = '';
}
<?php } //if?>
</script>
<div class="norisk-admin c">
	<div class="norisk-in">
	<? if ($errors) foreach ($errors as $err) { ?>
	<div style="color:red;font: 12px;"><?=$err?></div>
	<? } ?>
        <form action="." method="get" id="adminFrm">
        <div>
            <table class="nr-a-tbl" cellspacing="0" cellpadding="0" style="table-layout:fixed">
                <col style="width:115px" />
                <col style="width:80px" />
                <col style="width:81px" />
                <col  />
                <col style="width:75px" />
                <col style="width:55px" />
                <col style="width:155px" />
                <col style="width:188px" />
                <thead>
                    <tr>
                        <td colspan="8">
                            Остатки: <b><?=sbr_meta::view_cost($yd_balance, exrates::YM, false) ?></b>
                        </td>
                    </tr>
                    <tr>
                        <? foreach($sbr->form_cols[$mode] as $idx=>$val) { ?>
                        <th<?=($val[2] ? ' colspan="'.$val[2].'"' : '')?>>
                            <?=$val[0]?>
                            <a href="javascript:SBR.changeFormDir(<?=$idx?>,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?=($dir_col==$idx && $dir=='DESC' ? '-a' : '')?>.png" /></a> 
                            <a href="javascript:SBR.changeFormDir(<?=$idx?>,'ASC')"><img width="11" height="11" alt="v" src="/images/arrow-top<?=($dir_col==$idx && $dir=='ASC' ? '-a' : '')?>.png" /></a> 
                        </th>
                        <? } ?>
                    </tr>
                    <tr class="pd">
                        <td><input type="text" name="filter[requested]" value="<?=html_attr($sbr_payouts['filter']['requested'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></td>
                        <td><input type="text" name="filter[sbr]" value="<?=html_attr($sbr_payouts['filter']['sbr'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></td>
                        <td><input type="text" name="filter[stage]" value="<?=html_attr($sbr_payouts['filter']['stage'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></td>
                        <td><input type="text" name="filter[user]" value="<?=html_attr($sbr_payouts['filter']['user'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></td>
                        <td><input type="text" name="filter[sum]" value="<?=html_attr($sbr_payouts['filter']['sum'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></td>
                        <td>
                          <select name="filter[sys]" onchange="SBR.form.submit()">
                            <option value="0">Все</option>
                            <? foreach($EXRATE_CODES as $exc=>$exn) { ?>
                              <option value="<?=$exc?>"<?=($exc==$sbr_payouts['filter']['sys'] ? ' selected="selected"' : '')?> ><?=$exn[2]?></option>
                            <? } ?>
                          </select>
                        </td>
                        <td><input type="text" name="filter[account_num]" value="<?=html_attr($sbr_payouts['filter']['account_num'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></td>
                        <td>
                          <select name="filter[completed]" onchange="SBR.form.submit()">
                            <option value="0">Все</option>
                            <option value="1"<?=(1==$sbr_payouts['filter']['completed'] ? ' selected="selected"' : '')?>>Выплачены</option>
                            <option value="2"<?=(2==$sbr_payouts['filter']['completed'] ? ' selected="selected"' : '')?>>Не выплачены</option>
                          </select>
                        </td>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <div class="pager">
                                <?=new_paginator($page, ceil($page_count/sbr_adm::PAGE_SIZE), 10, "%s?site=admin&mode={$mode}{$filter_prms}&dir_col={$dir_col}&dir={$dir}&page=%d%s")?>
                            </div>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <? foreach($sbr_payouts['data'] as $sp) {
                        $payment_id = $sp['payment_id'] ? $sp['payment_id'] : $sp['wmpaymaster_id'];?>
                    <tr class="<?=(++$i%2==0 ? 'even' : 'odd').($sp['is_arbitrage']=='t' ? ' l-arb' : '')?>">
                        <td class="nr-a-o-date"><?=date('d.m.Y H:i', strtotime($sp['requested']))?></td>
                        <td><?=$sbr->getContractNum($sp['sbr_id'], $sp['scheme_type'])?></td>
                        <td class="nr-a-o-num"><?=($sp['is_arbitrage']=='t' ? '<b>А</b>&nbsp;&nbsp;' : '')?><a href="/sbr/?access=A&site=Stage&id=<?=$sp['stage_id']?>">#<?=sbr_stages::getOuterNum($sp['sbr_id'], $sp['stage_num'])?></a></td>
                        <td><a href="/users/<?=$sp['login']?>/" class="nr-a-lnk-<?=(is_emp($sp['role']) ? 'emp' : 'frl')?>"><?=($sp['uname'].' '.$sp['usurname'].' ['.$sp['login'].']')?></a></td>
                        <td class="nr-a-td-sum"><?=sbr_meta::view_cost($sp['credit_sum'], NULL, false)?></td>
                        <td class="nr-a-td-val">
                          <?=$EXRATE_CODES[$sp['credit_sys']][2]?>
                        </td>
                        <td><a href="/users/<?=$sp['login']?>/setup/finance/?sid=<?=$sp['stage_id']?>"><?=($sp['account_num'] ? $sp['account_num'] : 'Не задан')?></a></td>
                        <td class="nr-a-td-btn">
                        
                          <? if(!$sp['completed'] && ($sp['credit_sys']!=exrates::YM  || $sp['yd_completed']=='t')
                                                  && ($sp['credit_sys']!=exrates::WMR || $sp['wm_completed']=='t' || ($sp['is_arbitrage']=='t' && is_emp($sp['role'])) ))
                             {
                          ?>
                            <? if($sp['credit_sys']==exrates::WMR && $sp['is_arbitrage']=='t' && is_emp($sp['role']) && $payment_id) {?>
                                <? if($sp['is_refund'] == null) {?>
                                <span>
                                    <input type="button" id="wm_btn_refund<?=$sp['stage_id'].'_'.$sp['user_id']?>" value="Возврат" class="i-btn" onclick="SBR.sendForm({action:'refund', user_id: <?=$sp['user_id']?>, payment_id:<?= $payment_id?>, stage_id:<?=$sp['stage_id']?>});">
                                </span>
                                <? } elseif($sp['is_refund'] == 't') {?>
                                Деньги возвращены (<?=date('d.m.Y H:i', strtotime($sp['completed']))?>)
                                <? } else {?>
                                В процессе возврата
                                <? }?>
                            <?php } else if($sbr->isAdmin() && ! ( is_emp($sp['role']) && ($sp['credit_sys'] == exrates::YM || $sp['credit_sys'] == exrates::WMR  ) ) ) {?>
                            <span><input type="button" id="sp_btn<?=$sp['stage_id'].'_'.$sp['user_id']?>" value="Выплатить" class="i-btn" onclick="SBR.sendForm({action:'payout',user_id:<?=$sp['user_id']?>,stage_id:<?=$sp['stage_id']?>})"<?=($sp['account_num'] ? '' : ' disabled="disabled" title="Необходимо определить номер счета/кошелька"')?> /></span>
                            <?php }//if?>
                          <? } else if($sp['completed']) { ?>
                            <?=date('d.m.Y H:i', strtotime($sp['completed']))?>&nbsp;
                            <? if ($sp['credit_sys']!=exrates::YM && $sp['credit_sys']!=exrates::WMR && $sbr->isAdmin()) { ?>
                              <a href="javascript:;" onclick="if(window.confirm('Отменить выплату?'))SBR.sendForm({action:'unpayout',user_id:<?=$sp['user_id']?>,stage_id:<?=$sp['stage_id']?>})" title="Отменить выплату"><img src="/images/basket.png" width="11" heigth="11" alt="x" /></a>
                            <? } ?>
                          <? } ?>
                          <? if ($sp['credit_sys']==exrates::YM && !$sp['completed'] && $sbr->isAdmin()) { ?>
                            <span><input type="button" id="yd_btn<?=$sp['stage_id'].'_'.$sp['user_id']?>" value="Выплатить ЯД" class="i-btn" onclick="SBR.openPayoutPopup(<?=exrates::YM?>, <?=$sp['stage_id']?>,<?=$sp['user_id']?>)"
                            <?=(!$sp['account_num'] ? ' disabled="disabled"' : '')?>
                              title="<?=(!$sp['account_num'] ? 'Необходимо определить номер кошелька' : 'Открыть терминал выплат в ЯД')?>"/>
                            </span>
                          <? } ?>
                          <? if ($sp['credit_sys']==exrates::WMR && !$sp['completed'] && $sbr->isAdmin()) { ?>
                            <span><input type="button" id="wm_btn<?=$sp['stage_id'].'_'.$sp['user_id']?>" value="Выплатить WMR" class="i-btn" onclick="SBR.openPayoutPopup(<?=exrates::WMR?>, <?=$sp['stage_id']?>,<?=$sp['user_id']?>)"
                            <?=(!$sp['account_num'] ? ' disabled="disabled"' : '')?>
                              title="<?=(!$sp['account_num'] ? 'Необходимо определить номер кошелька' : 'Открыть терминал выплат в WMR')?>"/>
                            </span>
                          <? } ?>
                        </td>
                    </tr>
                    <? } ?>
                </tbody>
            </table>
            <? if(DEBUG) { ?>
            <input type="hidden" name="debug" value="0"/>
            <? }//if?>
            <input type="hidden" name="payment_id" value="">
            <input type="hidden" name="user_id" value=""/>
            <input type="hidden" name="stage_id" value=""/>
            <input type="hidden" name="site" value="<?=$site?>"/>
            <input type="hidden" name="mode" value="<?=$mode?>"/>
            <input type="hidden" name="dir_col"  value="<?=$dir_col?>"/>
            <input type="hidden" name="dir"  value="<?=$dir?>"/>
            <input type="hidden" name="action" value=""/>
        </div>
        </form>
	</div>
    <div id="payout_box"></div>
    <div id="expose" style="width: 1365px; height: 1352px; display:none"></div>
</div>
