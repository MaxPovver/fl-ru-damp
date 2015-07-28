<? // !!! объединить в шаблоны ?>
<?php $anchor_unread = 0; ?>
<script type="text/javascript">
var SBR = new Sbr();
</script>
<div class="tabs-in nr-tabs-in">
    <? 
       foreach($sbr_currents as $status=>$sbrs) {
           $is_hidden = !($anchor && array_key_exists($anchor, $sbrs)) && $_COOKIE['sbr_ss'.$status]==1;
           $ids_sbr = array_keys($sbrs);
           foreach($is_hidden_newmsg as $val) {
               if(array_search($val['id'], $ids_sbr) !== false) {
                   $is_hidden = false;
                   break;
               }
           }
    ?>
        <div class="nr-list-items" id="<?=$sbrss_classes[$status][0]?>">
            <div class="flt-bar">
                <a href="javascript:void(0);" class="flt-tgl-lnk" id="sssw<?=$status?>" onclick="SBR.hideStatusBox(this,<?=$status?>)"><?=($is_hidden ? 'Показать' : 'Скрыть')?></a>
                <h3><?=$sbrss_classes[$status][1]?> (<?=count($sbrs)?>)</h3>
            </div>
            <div id="ssbox<?=$status?>"<?=($is_hidden ? ' style="display:none"' : '')?>>
                <!-- Проект -->
                <? foreach($sbrs as $id=>$curr_sbr) { ?>
                    <a name="s<?=$id?>" id="s<?=$id?>"></a>
                    <div class="form nr-prj">
                        <b class="b1"></b>
                        <b class="b2"></b>
                        <div class="form-in">
                            <div class="nr-prj-in">
                                <div class="c">
                                    <div class="nr-prj-u">
                                        <b class="b1"></b>
                                        <b class="b2"></b>
                                        <div class="nr-prj-u-in">
                                            <div class="nr-prj-un">
                                                
                                                <a href="/users/<?=$curr_sbr->data[$curr_sbr->apfx.'login']?>/" class="freelancer-name"><?=($curr_sbr->data[$curr_sbr->apfx.'uname'].' '.$curr_sbr->data[$curr_sbr->apfx.'usurname'].' ['.$curr_sbr->data[$curr_sbr->apfx.'login'].']')?></a><?=view_mark_user($curr_sbr->data, $curr_sbr->apfx)?>&nbsp;<?=$session->view_online_status($curr_sbr->data[$curr_sbr->apfx.'login'], false, '&nbsp;', $activity)?>
                                                <?/*<div class="nr-prj-u-s">
                                                    <?=($activity ? ' Сейчас на сайте' : 'Нет на сайте')?>
                                                </div>*/ ?>
                                            </div>
                                        </div>
                                        <b class="b2"></b>
                                        <b class="b1"></b>
                                    </div>
                                    <h4 class="nr-prj-title"><a href="?id=<?=$id?>" class="inherit"><?=reformat($curr_sbr->data['name'], 35, 0, 1)?></a></h4>
                                </div>
                                <div class="nr-prj-created">
                                    <strong>#<?=$id?></strong> Проект создан: <?=$MONTHS[date('n', $t = strtotime($curr_sbr->data['posted']))].date(' j, Y', $t)?>
                                </div>
                                <div class="nr-tbl-inf nr-tbl-inf-list">
                                    <b class="b1"></b>
                                    <div class="nr-tbl-inf-in">
                                        <table>
                                            <col width="460" />
                                            <col width="120" />
                                            <col width="180" />
                                            <col width="140" />
                                            <? foreach($curr_sbr->stages as $num=>$stage) { ?>
                                            <tr class="<?=($num==$curr_sbr->data['stages_cnt']-1 ? ' last' : '')?><?=($stage->status == sbr_stages::STATUS_INARBITRAGE ? ' nr-task-arb' : '')?>">
                                                <td style="text-align:left">
                                                    <div class="utxt">
                                                        <h5><a href="?site=Stage&id=<?=$stage->data['id']?>"><?=reformat($stage->data['name'], 35, 0, 1)?></a></h5>
                                                        <p><?=reformat(LenghtFormatEx($stage->data['descr'], 250), 42, 0, 1, 1)?></p>
                                                    </div>
                                                    
                                                    <?php if ($stage->data['unread_msgs_count']): ?>
                                                    <?php $anchor_unread = ( $anchor_unread ) ? $anchor_unread : $stage->sbr->data['id'] ?>
                                                    <div class="nr-mn">
														<a href="/norisk2/?site=Stage&id=<?=$stage->data['id']?>#c_<?=$stage->data['unread_first_id']?>" class="lnk-green"><?=$stage->data['unread_msgs_count']?> <?=ending($stage->data['unread_msgs_count'], 'новый комментарий', 'новых комментария', 'новых комментариев')?></a>
													</div>
                                                    <?php endif; ?>
                                                </td>
                                                <? if(($status == sbr::STATUS_PROCESS || $status == sbr::STATUS_CHANGED) && $stage->status!=sbr_stages::STATUS_INARBITRAGE && ($stage->frl_version > $stage->version || $curr_sbr->frl_version > $curr_sbr->version)) { ?>
                                                <td colspan="3" class="nr-ch">
                                                    <div class="nr-ch-info">
                                                        <div>
                                                            Исполнитель отказался от новых условий<?=(trim($stage->data['frl_refuse_reason']) ? ' (причина в задаче)' : ' без указания причины')?>.
                                                        </div>
                                                    </div>
                                                </td>
                                                <? } else { ?>
                                                    <td class="nr-td-budjet" title="<?=$EXRATE_CODES[$curr_sbr->cost_sys][0]?>"><b class="rd24 rd24-<?=($curr_sbr->data['reserved_id'] ? 'grn' : 'red')?>"><b class="btn-lc"><b class="btn-m"><b class="btn-txt"><?=sbr_meta::view_cost($stage->data['cost'], $curr_sbr->cost_sys)?></b></b></b></b></td>
                                                    <? if($stage->status == sbr_stages::STATUS_COMPLETED || $stage->status == sbr_stages::STATUS_ARBITRAGED) { ?>
                                                      <td><?=$stage->data['work_days']?> <?=ending(abs($stage->data['work_days']), 'день', 'дня', 'дней')?></td>
                                                    <? } else { ?>
                                                      <td<?=($stage->data['work_rem'] < 0 ? ' class="nr-day-red"' : '')?>><?=$stage->data['work_rem']?> <?=ending(abs($stage->data['work_rem']), 'день', 'дня', 'дней')?></td>
                                                    <? } ?>
                                                    <td class="last">
                                                        <b class="rd24 rd24-<?=sbr_stages::$ss_classes[$stage->data['status']][0]?>"><b class="btn-lc"><b class="btn-m"><b class="btn-txt"><?=sbr_stages::$ss_classes[$stage->data['status']][1]?></b></b></b></b>
                                                        <? if(($ain=$stage->status == sbr_stages::STATUS_INARBITRAGE) || $stage->status == sbr_stages::STATUS_ARBITRAGED) { ?>
                                                          <span class="lnk-arb"><a href="javascript:;" onclick="SBR.getArbDescr(<?=$curr_sbr->id?>, <?=$stage->id?>)"><?=$ain ? 'Информация' : 'Решение Арбитража'?></a></span>
                                                        <? } ?>
                                                    </td>
                                                <? } ?>
                                            </tr>
                                            <? } ?>
                                        </table>
                                    </div>
                                    <b class="b1"></b>
                                </div>
                                <?=$curr_sbr->view_sign_alert(); ?>
                                <form action="?id=<?=$id?>" method="post" id="currentsFrm<?=$id?>">
                                    <? if(!$curr_sbr->reserved_id && ($curr_sbr->status == sbr::STATUS_PROCESS || $curr_sbr->status == sbr::STATUS_CHANGED)) { ?>
                                        <div class="nr-cancel-reason c" style="background: #FFCCCC">
                                            <p>Исполнитель приступит к работе только <b>после резервирования денежных средств</b>. Пожалуйста, перейдите в задачу и произведите резервирование денежных средств.</p>
                                        </div>
                                    <? } ?>

                                    <? /* if($sbr->error[$curr_sbr->data['id']]['canceled']) { ?>
                                        <div class="nr-cancel-reason c">
                                            <p>К сожалению вы не можете отменить проект, исполнитель только что принял условия и ожидает резервирования денег.<br />Для резерваци перейдите в задачу проекта, если вы настаиваете на отказе от проекта, обратитесь в Арбитраж (зайдите в задачу).</p>
                                        </div>
                                    <? } */ ?>
                                    <? if($status == sbr::STATUS_NEW || (!$curr_sbr->reserved_id && $status != sbr::STATUS_CANCELED && $status != sbr::STATUS_REFUSED && $status != sbr::STATUS_COMPLETED)) { ?>
                                                <div class="nr-prj-btns c">
                                                    <div class="btn-margin">
                                                        <span class="btn-o-red">
                                                            <a href="javascript:;" onclick="SBR.submitLock(document.getElementById('currentsFrm<?=$id?>'),{cancel:1})" class="btnr btnr-red"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Отменить</span></span></span></a>
                                                            <input type="hidden" name="cancel" value="" />
                                                        </span>
                                                    </div>
                                                </div>
                                    <? } ?>
                                    <? if($status == sbr::STATUS_REFUSED || $status == sbr::STATUS_CANCELED) { ?>
                                        <div class="nr-cancel-reason c">
                                            <strong>
                                                <?=($status == sbr::STATUS_CANCELED ? 'Вы отменили сделку' : ($curr_sbr->data['frl_refuse_reason'] ? reformat($curr_sbr->data['frl_refuse_reason'], 79, 0, 1, 1) : 'Причина отказа не указана.'))?>
                                            </strong>
                                        </div>
                                        <div class="nr-prj-btns c">
                                            <div class="btn-margin">
                                                <span class="btn-o-green">
                                                    <a href="/norisk2/?site=edit&id=<?=$id?>" class="btnr btnr-green2"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Изменить условия</span></span></span></a>
                                                    <input type="hidden" name="resend" value="" />
                                                </span>
                                            </div>
                                        </div>
                                    <? } ?>
                                    <input type="hidden" name="id" value="<?=$curr_sbr->data['id']?>" />
                                    <input type="hidden" name="action" value="status_action" />
                                </form>
                            </div>
                        </div>
                        <b class="b2"></b>
                        <b class="b1"></b>
                    </div>
                    <div id="arb_descr_box<?=$curr_sbr->id?>" class="arb_descr_box"></div>
                    <!-- конец Проект -->
                <? } ?>
            </div>
        </div>
    <? } ?>
</div>
<? if($anchor) { ?>
<script type="text/javascript">
go_anchor('s<?=$anchor?>',true);
</script>
<?
}
elseif ( $anchor_unread ) {
?>
<script type="text/javascript">
go_anchor('s<?=$anchor_unread?>',true);
</script>
<?
}
?>
