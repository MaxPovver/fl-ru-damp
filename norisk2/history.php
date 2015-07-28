<script type="text/javascript">
var SBR;
window.addEvent('domready', function() { SBR = new Sbr('historyFrm'); } );
</script>
<div class="tabs-in nr-tabs-in2 c">
	<div class="lnk-nr-back">
        <a href="/norisk2/">Вернуться в раздел Проекты по «Безопасным Сделкам»</a>
	</div>
	<div class="nr-prnt-project">
        История проекта: <strong class="nr-ico">#<?=$sbr->data['id']?></strong> <a href="/norisk2/?id=<?=$sbr->data['id']?>"><?=reformat($sbr->data['name'],50,0,1)?></a>
	</div>
    <form action=".#page" method="get" id="historyFrm">
    <div>
        <input type="hidden" name="site" value="<?=$site?>" />
        <input type="hidden" name="id" value="<?=$sbr->data['id']?>" />
        <input type="hidden" name="dir" value="<?=$dir?>" />
        <input type="hidden" name="dir_col" value="<?=$dir_col?>" />
        <div class="nr-history-tbl">
            <table class="tbl-type1" cellspacing="0">
                <col width="110" />
                <col width="450" />
                <col width="200" />
                <thead>
                    <tr>
                        <th>
                            Дата
                            <a href="javascript:SBR.changeFormDir(0,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?=($dir=='DESC' ? '-a' : '')?>.png" /></a>
                            <a href="javascript:SBR.changeFormDir(0,'ASC')"><img width="11" height="11" alt="^" src="/images/arrow-top<?=($dir=='ASC' ? '-a' : '')?>.png" /></a>
                        </th>
                        <th>
                            Событие
                        </th>
                        <th>
                            Этап
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <select class="nr-h-sel1" name="filter[ev_date]" onchange="document.getElementById('historyFrm').submit()">
                               <option value="">Все</option>
                             <? foreach($sbr_history['options']['ev_date'] as $ev_date=>$x) { ?>
                               <option value="<?=$ev_date?>"<?=($ev_date==$sbr_history['filter']['ev_date'] ? ' selected="selected"' : '')?> ><?=$ev_date?></option>
                             <? } ?>
                            </select>
                        </td>
                        <td>
                            <select class="nr-h-sel2" name="filter[ev_code]" onchange="document.getElementById('historyFrm').submit()">
                               <option value="">Все</option>
                             <? foreach($sbr_history['options']['ev_code'] as $ev_code=>$ev_name) { ?>
                               <option value="<?=$ev_code?>"<?=($ev_code==$sbr_history['filter']['ev_code'] ? ' selected="selected"' : '')?>><?=$ev_name?></option>
                             <? } ?>
                            </select>
                        </td>
                        <td>
                            <select class="nr-h-sel3" name="filter[stage_id]" onchange="document.getElementById('historyFrm').submit()">
                               <option value="">Все</option>
                             <? if($sbr_history['options']['stage_id']) foreach($sbr_history['options']['stage_id'] as $id=>$name) { ?>
                               <option value="<?=$id?>"<?=($id==$sbr_history['filter']['stage_id'] ? ' selected="selected"' : '')?>><?=$name?></option>
                             <? } ?>
                            </select>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <? $i=0; foreach($sbr_history['events'] as $id=>$ev) { ?>
                    <tr class="<?=((++$i % 2) ? 'odd' : 'even')?>">
                        <td><?=date('d.m.Y H:i', strtotime($ev['ev_time']))?></td>
                        <td><strong><?=$ev['ev_name'].($ev['note'] ? ' ('.trim($ev['note']).')' : '')?></strong></td>
                        <td><span <?=($ev['stage_name'] ? ' class="nr-h-sub"' : '')?>><a href="<?=($ev['stage_name'] ? "?site=Stage&id={$ev['own_id']}" : "/norisk2/?id={$ev['sbr_id']}")?>"><?=($ev['stage_name'] ? reformat($ev['stage_name'],40,0,1) : 'Весь проект')?></a></span></td>
                    </tr>
                    <? } ?>
                </tbody>
            </table>
        </div>
     </div>
    </form>
</div>
