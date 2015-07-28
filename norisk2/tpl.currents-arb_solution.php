<div class="overlay ov-out nr-task-overlay">
	<b class="c1"></b>
	<b class="c2"></b>
	<b class="ov-t"></b>
	<div class="ov-r">
		<div class="ov-l">
			<div class="ov-in">
				<div class="nr-arb-full-info">
                    <?
                    if ($stage->arbitrage['by_consent'] === 't') { 
                        $introText = "Арбитраж закрыл этап";
                    } else {
                        $introText = "Арбитраж вынес решение по этапу";
                    }
                    ?>
					<h3><?= $introText ?> &laquo;<a href="?site=Stage&id=<?=$stage->id?>"><?=reformat($stage->name, 33, 0, 1)?></a>&raquo;</h3>
                    
   					<div class="nr-arb-why">
   						<a href="javascript:void(0);" onclick="$(this).getParent('.nr-arb-why').toggleClass('nr-arb-why-show');" class="lnk-dot-666">Причина подачи в Арбитраж</a>
   						<div class="nr-arb-why-in">
   							<p class="d"><?=date('j '.strtolower($GLOBALS['MONTHA'][date('n', strtotime($stage->arbitrage['requested']))]).' Y, H:i', strtotime($stage->arbitrage['requested']))?></p>
   							<p><?=reformat($stage->arbitrage['descr'], 40, 0, 0, 1)?></p>
   							<? if($stage->arbitrage['attach']) { ?>
   							<ul class="added-files-list c">
                                <? foreach($stage->arbitrage['attach'] as $id=>$a) { if($a['is_deleted']=='t') continue; ?>
                                  <li class="<?=CFile::getext($a['name'])?>"><a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" target="_blank"><?=($a['orig_name'] ? $a['orig_name'] : $a['name'])?></a>, <span><?=ConvertBtoMB($a['size'])?></span></li>
                                <? } ?>
   							</ul>
                            <? } ?>
   						</div>
   					</div>
                    <p><?=date('j '.strtolower($GLOBALS['MONTHA'][date('n', strtotime($stage->arbitrage['resolved']))]).' Y, H:i', strtotime($stage->arbitrage['resolved']))?></p>
					<div class="form fs-dg nr-form-cause">
						<b class="b1"></b>
						<b class="b2"></b>
						<div class="form-in">
                            <p><?=reformat($stage->arbitrage['descr_arb'], 40, 0, 0, 1)?></p>
						</div>
						<b class="b2"></b>
						<b class="b1"></b>
					</div>
					<table class="nr-arb-solution">
						<col />
						<col />
						<col width="50" />
						<tr>
                            <th><a href="/users/<?=$emp->login?>/" class="employer-name"><?=$emp->uname?> <?=$emp->usurname?> [<?=$emp->login?>]</a></th>
                            <td><strong><?=sbr_meta::view_cost($stage->getPayoutSum(sbr::EMP), $stage->sbr->cost_sys, false)?></strong></td>
                            <td><?=100*(1-$stage->arbitrage['frl_percent'])?>%</td>
						</tr>
						<tr>
                            <th><a href="/users/<?=$frl->login?>/" class="freelancer-name"><?=$frl->uname?> <?=$frl->usurname?> [<?=$frl->login?>]</a></th>
                            <td><strong><?=sbr_meta::view_cost($stage->getPayoutSum(sbr::FRL), $stage->sbr->cost_sys, false)?></strong></td>
                            <td><?=100*$stage->arbitrage['frl_percent']?>%</td>
						</tr>
					</table>
                    <? if($show_pay_info && !$stage->sbr->isAdmin()) { ?>
                        <div class="form fs-g2 nr-arb-bgrn">
                            <b class="b1"></b>
                            <b class="b2"></b>
                            <div class="form-in">
                                Для получения денег перейдите в задачу и заполните форму.
                            </div>
                            <b class="b2"></b>
                            <b class="b1"></b>
                        </div>
                    <? } ?>
                    <p>Если у вас есть вопросы, пожалуйста, обратитесь в <a href="/help/?all">Службу поддержки</a> или к <a href="/users/norisk/">менеджеру «Безопасной Сделки»</a></p>
					<div class="nr-arb-fi-close">
						<a href="javascript:void(0);" class="btn btn-grey" onclick="$(this).getParent('div.overlay').setStyle('display', 'none'); return false;"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Закрыть</span></span></span></a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<b class="ov-b"></b>
	<b class="c3"></b>
	<b class="c4"></b>
</div>
