<style>
@media print {
    *<.nr-a-tbl {display:none}
}
</style>
<div class="norisk-admin c">
	<div class="norisk-in">
        <form action="." method="get" id="adminFrm">
        <div>
            <table class="nr-a-tbl" cellspacing="5" style="table-layout:fixed">
                <col style="width:100px" />
                <col style="width:85px" />
                <col  />
                <col style="width:75px" />
                <col style="width:55px" />
                <col style="width:100px" />
                <col style="width:95px" />
                <thead>
                    <tr>
                        <? foreach($sbr->form_cols['all'] as $idx=>$val) { ?>
                        <th<?=($val[2] ? ' colspan="'.$val[2].'"' : '')?>>
                            <?=$val[0]?>
                            <a href="javascript:SBR.changeFormDir(<?=$idx?>,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?=($dir_col==$idx && $dir=='DESC' ? '-a' : '')?>.png" /></a> 
                            <a href="javascript:SBR.changeFormDir(<?=$idx?>,'ASC')"><img width="11" height="11" alt="v" src="/images/arrow-top<?=($dir_col==$idx && $dir=='ASC' ? '-a' : '')?>.png" /></a> 
                            <? /* if($idx==1) { ?>
                            &nbsp;<span style="font-weight:normal!important">зайти админом в проект #<input type="text" value="" size="2"/>, этап #<input type="text" value="" size="2"/>&nbsp;<input type="button" value="Go"/></span>
                            <? } */ ?>
                        </th>
                        <? } ?>
                    </tr>
                    <tr class="pd">
                        <td><div class="b-input"><input class="b-input__text" type="text" name="filter[start_time]" value="<?=html_attr($sbr_all['filter']['start_time'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()"/></div></td>
                        <td><div class="b-input"><input class="b-input__text" type="text" name="filter[sbr]" value="" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></div></td>
                        <td><div class="b-input"><input class="b-input__text" type="text" name="filter[stage]" value="<?=html_attr($sbr_all['filter']['stage'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></div></td>
                        <td><div class="b-input"><input class="b-input__text" type="text" name="filter[cost]" value="<?=html_attr($sbr_all['filter']['cost'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></div></td>
                        <td><select name="filter[cost_sys]" onchange="SBR.form.submit()">
                            <option value="0">Все</option>
                          <? foreach($EXRATE_CODES as $exc=>$exn) { ?>
                            <option value="<?=$exc?>"<?=($exc==$sbr_all['filter']['cost_sys'] ? ' selected="selected"' : '')?>><?=$exn[2]?></option>
                          <? } ?>
                        </select></td>
                        <td><div class="b-input"><input class="b-input__text" type="text" name="filter[work_time]" value="<?=html_attr($sbr_all['filter']['work_time'])?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()" /></div></td>
                        <td><select name="filter[status]" onchange="SBR.form.submit()">
                            <option value="-1">Все</option>
                            <option value="<?=sbr_adm::STATUS_RESERVED?>"<?=(sbr_adm::STATUS_RESERVED==$sbr_all['filter']['status'] ? ' selected="selected"' : '')?>>зарезервировано</option>
                          <? foreach(sbr_stages::$ss_classes as $st=>$ssc) { ?>
                            <option value="<?=$st?>"<?=(isset($sbr_all['filter']['status']) && $st==$sbr_all['filter']['status'] ? ' selected="selected"' : '')?> ><?=$ssc[1]?></option>
                          <? } ?>
                        </select></td>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="6">
                            <div class="pager">
                                <?=new_paginator($page, ceil($page_count/sbr_adm::PAGE_SIZE), 10, "%s?site=admin&mode={$mode}{$filter_prms}&dir_col={$dir_col}&dir={$dir}&page=%d%s")?>
                            </div>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <? foreach($sbr_all['data'] as $sbr_data) {
                        $sbr_data['cost_sys'] = $sbr_data['ps_emp'] ? pskb::$exrates_map[$sbr_data['ps_emp']] : $sbr_data['cost_sys']; ?>
                    <tr class="<?=(++$i%2==0 ? 'even' : 'odd')?>">
                        <td><?=($sbr_data['first_time'] ? date('d.m.Y H:i', strtotime($sbr_data['first_time'])) : ' &mdash; ')?></td>
                        <td><?=$sbr->getContractNum($sbr_data['sbr_id'], $sbr_data['scheme_type'])?></td>
                        <td><strong>#<?=sbr_stages::getOuterNum($sbr_data['sbr_id'], $sbr_data['num'])?></strong> <a href="<?= ($mode == 'all'? '/sbr/' : '' ) ?>?access=A&site=Stage&id=<?=$sbr_data['id']?>"><?=reformat($sbr_data['name'],30,0,1)?></a></td>
                        <td class="nr-a-td-sum"><?=sbr_meta::view_cost($sbr_data['cost'], NULL, false)?></td>
                        <td class="nr-a-td-val"><?= ($sbr_data['reserved_id'] || $sbr_data['scheme_type'] != sbr::SCHEME_LC ? $EXRATE_CODES[$sbr_data['cost_sys']][2] : ' &mdash; ');?></td>
                        <td><?=($sbr_data['dead_time'] ? date('d.m.Y H:i', strtotime($sbr_data['dead_time'])) : $sbr_data['work_days'].' '.ending(abs($sbr_data['work_days']), 'день', 'дня', 'дней'))?></td>
                        <td<?=($sbr_data['status']==sbr_stages::STATUS_INARBITRAGE ? ' class="stat-imp"' : '')?>><?=sbr_stages::$ss_classes[$sbr_data['status']][1]?></td>
                    </tr>
                    <? } ?>
                </tbody>
            </table>
            <input type="hidden" name="site" value="<?=$site?>" />
            <input type="hidden" name="mode" value="<?=$mode?>" />
            <input type="hidden" name="dir_col"  value="<?=$dir_col?>" />
            <input type="hidden" name="dir"  value="<?=$dir?>" />
        </div>
        </form>
	</div>
</div>
