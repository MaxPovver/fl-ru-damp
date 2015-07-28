    <div class="lnk-nr-back">
        <a href="/norisk2/">Вернуться в раздел Проекты по «Безопасным Сделкам»</a>
	</div>
	<div class="nr-prj-status">
        <? if($stage->status == sbr_stages::STATUS_COMPLETED) { ?>
            <strong class="nr-prj-cmplt">Задача завершена!</strong>
        <? }
           else if($stage->status == sbr_stages::STATUS_ARBITRAGED) { ?>
            <strong>Задача закрыта решением Арбитража</strong>
        <? } elseif($stage->status == sbr_stages::STATUS_INARBITRAGE) {
            
            if (!$stage->data['worked_time'] || $stage->data['worked_time'] === '00:00:00') { // если не было пауз во время выполнения задания
                $endDate = strtotime($stage->data['work_time'], strtotime($stage->data['first_time'])); // дата когда заканчивается срок выполнения этапа
            } else {
                //$endDate = strtotime($stage->data['work_time'], time() - strtotime($stage->data['worked_time'], 0));
                $endDate = strtotime($stage->data['work_time']) - $stage->data['worked_time_sec'];
            }
               
            if ($endDate < time()) { ?>
                <strong>Сделка просрочена на <?= ago_pub($endDate) ?></strong>
            <? } else { ?>
                <strong>На разработку осталось <?= ago_pub($endDate) ?></strong>
            <? }
                
        } else if($stage->dead_time)
           {
             if($stage->work_rem==0)
                 $dstr = 'Заканчивается сегодня';
             else if($stage->work_rem < 0)
                 $dstr = '<span class="nr-day-red">Проект просрочен на ' . ago_pub(time() + $stage->work_rem*3600*24, 'ynj') . '</span>';
             else
                 $dstr = 'До окончания ' . ago_pub(time() + $stage->work_rem*3600*24, 'ynj');
        ?>
            <strong><?=$dstr?></strong>
        <? }
           else if($stage->work_days)
           {
              if($stage->work_days > 0)
                 $dstr = 'Время выполнения ' . $stage->work_days . ' ' . ending(abs($stage->work_days), 'день', 'дня', 'дней');
              else
                 $dstr = '<span class="nr-day-red">Проект просрочен на ' . ago_pub(time() + $stage->work_days*3600*24, 'ynj') . '</span>';
        ?>
            <strong><?=$dstr?></strong>
        <? } if($stage->first_time) { ?>
            <span>Проект был открыт <?=date('j '.strtolower($MONTHA[date('n', strtotime($stage->first_time))]).' Y года в H:i', strtotime($stage->first_time))?></span>
        <? } else { ?>
            <span></span>
        <? } ?>
	</div>
    <h3 class="nr-prj-title">Этап <?=$stage->getOuterNum()?>: <a href="?site=Stage&id=<?=$stage->data['id']?>" class="inherit"><?=reformat($stage->name,40,0,1)?></a></h3>
    <div class="nr-prnt-project">
        Проект <?=$sbr->id?>: <a href="?id=<?=$sbr->id?>"><?=reformat($sbr->name,55,0,1)?></a>
        <span class="nr-prnt-doc">Номер договора: <?=$sbr->getContractNum()?></span>
    </div>
    <ul class="nr-prj-options c">
        <? if($sbr->data['reserved_id'] && !$sbr->isAdmin() && !$sbr->isAdminFinance() && ($stage->status & sbr_stages::STATUS_COMPLETED) != sbr_stages::STATUS_COMPLETED && $stage->status != sbr_stages::STATUS_INARBITRAGE) { ?>
		<li class="nr-prj-o1">
            <a href="?site=arbitrage&id=<?=$stage->id?>" class="rd21 rd21-pink"><b class="btn-lc"><b class="btn-m"><b class="btn-txt">Обратиться в Арбитраж</b></b></b></a>
		</li>
        <? } else if(($ain=$stage->status == sbr_stages::STATUS_INARBITRAGE) || $stage->status == sbr_stages::STATUS_ARBITRAGED) { ?>
        <li class="nr-prj-o1">
          <a href="javascript:;" onclick="SBR.getArbDescr(<?=$sbr->id?>, <?=$stage->id?>)" class="rd21 rd21-pink"><b class="btn-lc"><b class="btn-m"><b class="btn-txt"><?=$ain ? 'Информация Арбитража' : 'Решение Арбитража'?></b></b></b></a>
        </li>

        <? } ?>

        <? if($sbr->isEmp() && !$stage_changed && ($stage->status & sbr_stages::STATUS_COMPLETED) != sbr_stages::STATUS_COMPLETED && $stage->status != sbr_stages::STATUS_INARBITRAGE) { ?>
		<li class="nr-prj-o2">
            <a href="?site=editstage&id=<?=$stage->data['id']?>" class="rd21 rd21-grey"><b class="btn-lc"><b class="btn-m"><b class="btn-txt">Внести изменения в проект</b></b></b></a>
		</li>
        <? } ?>
		<li class="nr-prj-o3">
            <a href="?site=docs&id=<?=$sbr->id?>&sid=<?=$stage->id?>" class="rd21 rd21-grey"><b class="btn-lc"><b class="btn-m"><b class="btn-txt">Документы проекта</b></b></b></a>
		</li>
		<li>
            <a href="?site=history&id=<?=$sbr->data['id']?>" class="rd21 rd21-grey"><b class="btn-lc"><b class="btn-m"><b class="btn-txt">История проекта</b></b></b></a>
		</li>
	</ul>
	<div class="nr-tbl-inf">
		<b class="b1"></b>
		<div class="nr-tbl-inf-in">
			<table>
				<col width="185" />
				<col width="220" />
                <tr class="last<?=($ain ? ' nr-task-arb' : '')?>">
                    <td class="nr-td-budjet">Стоимость работы, в т.ч. НДС&nbsp;&nbsp;&nbsp;<b class="rd24 rd24-<?=($sbr->data['reserved_id'] ? 'grn' : 'red')?>"><b class="btn-lc"><b class="btn-m"><b class="btn-txt"><?=sbr_meta::view_cost($stage->data['cost'], $sbr->cost_sys)?></b></b></b></b></td>
                    <? if($sbr->isEmp() && !$stage_changed && ($stage->status & sbr_stages::STATUS_COMPLETED) != sbr_stages::STATUS_COMPLETED && $stage->status != sbr_stages::STATUS_INARBITRAGE && $sbr->data['reserved_id']) { ?>
                        <td class="td-expand">
                            <form action="." method="post" id="statusFrm">
                                <div class="expand-out" id="expand">
                                    <label class="e-label">Статус&nbsp;&nbsp;&nbsp;</label>
                                    <div class="expand <?=sbr_stages::$ss_classes[$stage->status][2]?>">
                                        <b class="ct"><b class="ch">&nbsp;</b><b class="cl"></b><b class="cr"></b></b>
                                        <strong onclick="ssBoxLock=1;"><?=sbr_stages::$ss_classes[$stage->status][1]?></strong>
                                        <a href="javascript:;" class="e-darr" onclick="ssBoxLock=1;$('status_box').toggleClass('e-show');document.body.onclick=function(){if(!ssBoxLock)$('status_box').removeClass('e-show');ssBoxLock=0;};">&darr;</a>
                                        <div class="e-list" onclick="ssBoxLock=1;">
                                            <div class="e-ul" id="status_box">
                                                <ul>
                                                    <? foreach(sbr_stages::$ss_classes as $id=>$stts) { 
                                                        if($id == sbr_stages::STATUS_INARBITRAGE || $id == $stage->status || $id == sbr_stages::STATUS_ARBITRAGED) continue;
                                                        if($id == sbr_stages::STATUS_COMPLETED && ($stage->frl_version != $stage->version || $sbr->frl_version != $sbr->version)) continue;
                                                    ?>
                                                      <li><a href="javascript:;" onclick="var f=document.getElementById('statusFrm');f['status'].value=<?=$id?>;f.submit()"><?=$stts[1]?></a></li>
                                                    <? } ?>
                                                </ul>
                                            </div>
                                            <b class="cb"><b class="ch"></b><b class="cl"></b><b class="cr"></b></b>
                                        </div>
                                    </div>
																		<input type="hidden" name="id" value="<?=$stage->id?>" />
																		<input type="hidden" name="site" value="<?=$site?>" />
																		<input type="hidden" name="status" value="" />
																		<input type="hidden" name="action" value="change_status" />
                                </div>
                            </form>
                        </td>
                    <? } else { ?>
                        <td >Статус&nbsp;&nbsp;&nbsp;<b class="rd24 rd24-<?=sbr_stages::$ss_classes[$stage->data['status']][0]?>"><b class="btn-lc"><b class="btn-m"><b class="btn-txt"><?=sbr_stages::$ss_classes[$stage->data['status']][1]?></b></b></b></b>
                        </td>
                    <? } ?>
                    <td class="last">
                        <? include($fpath.'tpl.stage-user.php') ?>
                    </td>
                </tr>
			</table>
		</div>
		<b class="b1"></b>
	</div>
    <div id="arb_descr_box<?=$sbr->id?>" class="arb_descr_box"></div>
