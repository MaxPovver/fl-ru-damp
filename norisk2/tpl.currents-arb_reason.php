<div class="overlay ov-out nr-task-overlay">
    <b class="c1"></b>
    <b class="c2"></b>
    <b class="ov-t"></b>
    <div class="ov-r">
        <div class="ov-l">
            <div class="ov-in">
                <div class="nr-arb-full-info">
                    <h3>Этап &laquo;<a href="?site=Stage&id=<?=$stage->id?>"><?=reformat($stage->name,33, 0, 1)?></a>&raquo; отправлен на рассмотрение в арбитражную комиссию Free-lance.ru</h3>
                    <p><?=date('j '.strtolower($GLOBALS['MONTHA'][date('n', strtotime($stage->arbitrage['requested']))]).' Y, H:i', strtotime($stage->arbitrage['requested']))?></p>
                    <p><a href="/users/<?=$stage->arbitrage['login']?>/" class="<?=is_emp($stage->arbitrage['role']) ? 'employer' : 'freelancer'?>-name"><?=$stage->arbitrage['uname']?> <?=$stage->arbitrage['usurname']?> [<?=$stage->arbitrage['login']?>]</a> обратился с причиной:</p>
                    <div class="form fs-dg nr-form-cause">
                        <b class="b1"></b>
                        <b class="b2"></b>
                        <div class="form-in">
                            <p><?=reformat($stage->arbitrage['descr'], 48, 0, 1, 1)?></p>
                            <? if($stage->arbitrage['attach']) { ?>
                            <ul class="list-files">
                                <? foreach($stage->arbitrage['attach'] as $id=>$a) { if($a['is_deleted']=='t') continue; ?>
                                <li><a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" target="_blank"><?=($a['orig_name'] ? $a['orig_name'] : $a['name'])?></a>, <span><?=ConvertBtoMB($a['size'])?></span></li>
                                <? } ?>
                            </ul>
                            <? } ?>
                        </div>
                        <b class="b2"></b>
                        <b class="b1"></b>
                    </div>
                    <p class="nr-arb-imp"><strong>Арбитражая Комиссия свяжется с вами в ближайшее время.</strong></p>
                    <p>Если у вас есть вопросы &mdash; обратитесь, пожалуйста, к <a href="/users/norisk/">Менеджеру «Безопасной Сделки»</a> или в <a href="/help/?all">Службу поддержки</a></p>
                    <div class="nr-arb-fi-close">
                        <a href="javascript:void(0);" class="btn btn-grey" onclick="$(this).getParent('div.overlay').setStyle('display', 'none'); if(SBR.bx_arb_descr)SBR.bx_arb_descr.style.display='none'; return false;"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Закрыть</span></span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <b class="ov-b"></b>
    <b class="c3"></b>
    <b class="c4"></b>
</div>
