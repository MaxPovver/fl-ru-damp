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
                    <a name="s<?=$id?>"></a>
                    <div class="form nr-prj">
                        <b class="b1"></b>
                        <b class="b2"></b>
                        <div class="form-in">
                            <form action="?id=<?=$id?>" method="post" id="currentsFrm<?=$id?>">
                            <div>
                                <div class="nr-prj-in">
                                    <div class="c">
                                        <div class="nr-prj-u">
                                            <b class="b1"></b>
                                            <b class="b2"></b>
                                            <div class="nr-prj-u-in">
                                                <div class="nr-prj-un">
                                                   
                                                    <a href="/users/<?=$curr_sbr->data[$curr_sbr->apfx.'login']?>/" class="employer-name"><?=($curr_sbr->data[$curr_sbr->apfx.'uname'].' '.$curr_sbr->data[$curr_sbr->apfx.'usurname'].' ['.$curr_sbr->data[$curr_sbr->apfx.'login'].']')?></a><?=view_mark_user($curr_sbr->data, $curr_sbr->apfx);?>&nbsp;<?=$session->view_online_status($curr_sbr->data[$curr_sbr->apfx.'login'], false, '&nbsp;', $activity)?>
                                                    <? /*<div class="nr-prj-u-s">
                                                        <?=($activity ? ' Сейчас на сайте' : 'Нет на сайте')?>
                                                    </div> */ ?>
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
                                    <h4><?=sbr::$scheme_types[$curr_sbr->scheme_type][0]?></h4>
                                    <? foreach($curr_sbr->stages as $num=>$stage) { ?>
                                    <div class="form nr-budjet-details">
                                        <b class="b1"></b>
                                        <div class="form-in">
                                            <table>
                                                <col width="460" />
                                                <col width="120" />
                                                <col width="180" />
                                                <col width="140" />
                                                 <tfoot>
                                                <?= $stage->getTaxInfo(sbr::FRL) ?>
                                                </tfoot>
                                               <tbody>
                                                    <tr class="<?=($num==$curr_sbr->data['stages_cnt']-1 ? ' last' : '')?><?=($stage->status == sbr_stages::STATUS_INARBITRAGE ? ' nr-task-arb' : '')?>">
                                                        <td style="text-align:left;">
                                                            <div class="utxt">
                                                                <h5><a href="?site=Stage&id=<?=$stage->data['id']?>"><?=reformat($stage->data['name'], 35, 0, 1)?></a></h5>
                                                                <p><?=reformat(LenghtFormatEx($stage->data['descr'], 250), 42, 0, 1, 1)?></p>
                                                                <input type="hidden" name="stages[<?=$num?>][id]" value="<?=$stage->data['id']?>" />
                                                                <input type="hidden" name="stages[<?=$num?>][version]" value="<?=$stage->data['version']?>" />
                                                            </div>

                                                            <?php if ($stage->data['unread_msgs_count']): ?>
                                                            <?php $anchor_unread = ( $anchor_unread ) ? $anchor_unread : $stage->sbr->data['id'] ?>
                                                            <div class="nr-mn">
                      <a href="/norisk2/?site=Stage&id=<?=$stage->data['id']?>#c_<?=$stage->data['unread_first_id']?>" class="lnk-green"><?=$stage->data['unread_msgs_count']?> <?=ending($stage->data['unread_msgs_count'], 'новый комментарий', 'новых комментария', 'новых комментариев')?></a>
                     </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <? if($status == sbr::STATUS_CHANGED && ($stage->frl_version < $stage->version || $curr_sbr->frl_version < $curr_sbr->version) && !($stage->status == sbr_stages::STATUS_INARBITRAGE || $stage->status == sbr_stages::STATUS_ARBITRAGED)) { ?>
                                                            <td colspan="3" class="nr-ch last">
                                                                <div class="nr-ch-info">
                                                                    <div>
                                                                        Перейдите в проект, чтобы увидеть измененные условия
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
                                                </tbody>
                                            </table>
                                        </div>
                                        <b class="b1"></b>
                                    </div>
                                    <? } ?>
                                    <? if($sbr->error[$curr_sbr->data['id']]['new_version']) { ?>
                                        <div class="nr-cancel-reason c">
                                            <p>Заказчик только что изменил структуру «Безопасной Сделки» (добавил/удалил/переместил) этапы. Пожалуйста, ознакомьтесь и примите решение.</p>
                                        </div>
                                    <? } ?>
                                    <? if ($status == sbr::STATUS_NEW) { ?>
                                        <div class="nr-prj-btns c" style="position:relative">
                                            <?  if($curr_sbr->scheme_type != sbr::SCHEME_OLD) { ?>
                                                <div class="nr-warning">
                                                    <strong>Соглашаясь с данным Техническим заданием</strong>
                                                    <? if($curr_sbr->scheme_type == sbr::SCHEME_AGNT) { ?>
                                                      путем нажатия на кнопку &laquo;Согласиться&raquo;, вы заключаете Соглашение о выполнении работы и/или оказании услуги с использованием онлайн сервиса &laquo;Безопасная Сделка&raquo;. Текст Соглашения расположен на Сайте Free-lance.ru в сети Интернет по адресу: <a href="/agreement_escrow.pdf" target="_blank"><nobr><?=HTTP_PREFIX?>www.free-lance.ru/agreement_escrow.pdf</nobr></a>.<br/><br/>
                                                      Настоящим Сайт Free-lance.ru (ООО "Ваан") предлагает Оферту на заключение Договора об использовании онлайн сервиса &laquo;Безопасная Сделка&raquo;. Текст Оферты на заключение Договора об использовании онлайн сервиса &laquo;Безопасная Сделка&raquo; расположен на Сайте Free-lance.ru в сети Интернет по адресу: <a href="<?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?>" target="_blank"><nobr><?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?></nobr></a>. Нажимая на кнопку &laquo;Согласиться&raquo;, вы принимаете условия Оферты на заключение Договора об использовании онлайн сервиса &laquo;Безопасная Сделка&raquo;.
                                                    <? } else if($curr_sbr->scheme_type == sbr::SCHEME_PDRD) { ?>
                                                      путем нажатия на кнопку &laquo;Согласиться&raquo;, вы заключаете Соглашение о выполнении работы и/или оказании услуги с использованием онлайн сервиса &laquo;Безопасная Сделка&raquo;. Текст Соглашения расположен на Сайте Free-lance.ru в сети Интернет по адресу: <a href="/offer_work_free-lancer.pdf" target="_blank"><nobr><?=HTTP_PREFIX?>www.free-lance.ru/offer_work_free-lancer.pdf</nobr></a>.
                                                    <? } ?>
                                                </div>
                                                <!-- NR edited -->
                                                <br />
                                                    <div>
                                                        <ul class="form-list">
                                                            <li><label for="rez_type<?=sbr::RT_RU?>"><input type="radio" class="i-radio" name="rez_type" id="rez_type<?=sbr::RT_RU?>" onclick="<? if($isReqvsFilled) { ?>SBR.changeRezTypeFrl(<?=$curr_sbr->id?>, <?=sbr::RT_RU?>)<? } ?>" value="<?=sbr::RT_RU?>"<?=($rt_disabled && $rez_type && $rez_type != sbr::RT_RU ? ' disabled="true"' : '' )?><?=($rt_checked && $rez_type == sbr::RT_RU ? ' checked="true"' : '' )?> />&nbsp;Я подтверждаю, что являюсь резидентом Российской Федерации.</label></li>
                                                            <li>
                                                                <label for="rez_type<?=sbr::RT_UABYKZ?>">
                                                                  <input type="radio" name="rez_type" class="i-radio" id="rez_type<?=sbr::RT_UABYKZ?>" onclick="<? if($isReqvsFilled) { ?>SBR.changeRezTypeFrl(<?=$curr_sbr->id?>, <?=sbr::RT_UABYKZ?>)<? } ?>" value="<?=sbr::RT_UABYKZ?>"<?=($rt_disabled && $rez_type && $rez_type != sbr::RT_UABYKZ ? ' disabled="true"' : '' )?><?=($rt_checked && $rez_type == sbr::RT_UABYKZ ? ' checked="true"' : '' )?> />&nbsp;Я подтверждаю, что являюсь резидентом любого другого государства, кроме Российской Федерации
                                                                </label>
                                                                <div class="form fs-w form-resident-inf"<?=($rt_checked && $rez_type == sbr::RT_UABYKZ ? '' : ' style="display:none"' )?> id="norez_info<?=$curr_sbr->id?>">
                                                                    <b class="b1"></b>
                                                                    <b class="b2"></b>
                                                                    <div class="form-in">
                                                                        <p>
                                                                          <span class="red">
                                                                            <? if($curr_sbr->has_norez_overcost || ($pdrd_disabled && $curr_sbr->scheme_type==sbr::SCHEME_PDRD && $sbr->user_reqvs['rez_type']!=sbr::RT_RU)) { ?>
                                                                              <div id="ok_blocked_alert<?=$curr_sbr->id?>">
                                                                                <? if($curr_sbr->has_norez_overcost) { ?>
                                                                                  <div><strong>Бюджет задачи «Безопасной Сделки» превышает допустимый лимит</strong>, свяжитесь с работодателем для корректировки проекта.</div><br/>
                                                                                <? } ?>
                                                                                <? if($pdrd_disabled && $curr_sbr->scheme_type==sbr::SCHEME_PDRD && $sbr->user_reqvs['rez_type']!=sbr::RT_RU) { ?>
                                                                                  <div><strong>«Безопасная Сделка» по договору подряда для нерезидентов Российской Федерации временно недоступна</strong>.</div><br/>
                                                                                <? } ?>
                                                                              </div>
                                                                            <? } ?>
                                                                            Нерезиденты Российской Федерации имеют лишь один способ получения денежных средств: безналичный расчет с ограничением суммы эквивалентной пятидесяти тысячам долларов США.
                                                                          </span>
                                                                          <? if( $curr_sbr->scheme['taxes'][sbr::FRL][sbr::TAX_NP]
                                                                                 && $sbr->user_reqvs['form_type']==sbr::FT_JURI) { ?>
                                                                            <br /><br />
                                                                            Во избежание дополнительного налога (налога на прибыль), вам необходимо выслать справку в бумажном виде вместе с Актом, что вы являетесь резидентом своей страны. <a href="/docs/dokumenty_na_rezidentstvo.docx" target="_blank">Документ о Резидентстве</a>
                                                                          <? } ?>
                                                                        </p>
                                                                    </div>
                                                                    <b class="b2"></b>
                                                                    <b class="b1"></b>
                                                                </div>
                                                            </li>
                                                        </ul>

                                                        <? if(!$isReqvsFilled) { ?>
                                                        <div class="form fs-p finanse-require">
                                                            <b class="b1"></b>
                                                            <b class="b2"></b>
                                                            <div class="form-in">
                                                                 Не заполнены данные на вашей странице "Финансы", прежде чем продолжить вам необходимо <a href="/users/<?=$_SESSION['login']?>/setup/finance">заполнить все обязательные поля.</a>
                                                            </div>
                                                            <b class="b2"></b>
                                                            <b class="b1"></b>
                                                       </div>
                                                       <? } ?>
                                                        
                                                        <? if($rez_type == sbr::RT_UABYKZ && $sbr->user_reqvs['form_type'] == sbr::FT_PHYS) { ?>
                                                        <div class="form fs-p finanse-require">
                                                            <b class="b1"></b>
                                                            <b class="b2"></b>
                                                            <div class="form-in">
                                                                 Если вы являетесь физическим лицом и не являетесь резидентом РФ, для вывода средств с вашего личного счета необходимо подписать и отправить нам по почте Акт по проведенной «Безопасной Сделке» и Заявление на вывод денежных средств.
                                                            </div>
                                                            <b class="b2"></b>
                                                            <b class="b1"></b>
                                                       </div>
                                                       <? } ?>

                                                    </div>
                                                <br />
                                                <!-- NR edited -->
                                            <? } ?>
                                            <div class="btn-margin">
                                                <span class="btn-o-red">
                                                    <a href="javascript:;" onclick="$$('.rrbox-class').setStyle('display', 'none'); $('rrbox<?=$curr_sbr->id?>').style.display='block';
                                                                                   if(__okbl=$('ok_blocked_alert<?=$curr_sbr->id?>'))
                                                                                      $('rrtext<?=$curr_sbr->data['id']?>').value=__okbl.get('text').trim();
                                                                                   return false;" class="btnr btnr-red"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Отказаться</span></span></span></a>
                                                </span>
                                                <span class="btn-o-green">
                                                    <? if($curr_sbr->scheme_type != sbr::SCHEME_OLD) { ?>
                                                      <a href="javascript:;" onclick="if((_xrtc=SBR.rt_cache[<?=$curr_sbr->id?>]) && !_xrtc.ok_blocked)SBR.submitLock(document.getElementById('currentsFrm<?=$id?>'),{ok:1})" id="ok_btn<?=$curr_sbr->id?>" class="btnr btnr-green2 btnr-disabled"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Согласиться</span></span></span></a>
                                                    <? } else { ?>
                                                      <a href="javascript:;" onclick="SBR.submitLock(document.getElementById('currentsFrm<?=$id?>'),{ok:1})" class="btnr btnr-green2"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Согласиться</span></span></span></a>
                                                    <? } ?>
                                                    <input type="hidden" name="ok" value="" />
                                                </span>
                                            </div>

                                            <div class="f-overlay f-o-nr-c rrbox-class" id="rrbox<?=$curr_sbr->id?>" style="display:none;">
                                                <div class="f-overlay-in">
                                                    <h3>Укажите причину отказа</h3>
                                                    <div class="f-overlay-cnt"><textarea name="frl_refuse_reason" rows="5" cols="10" id="rrtext<?=$curr_sbr->data['id']?>"></textarea></div>
                                                    <div class="f-overlay-btns">
                                                        <input type="submit" name="refuse" value="Отказаться" class="i-btn i-bold" />
                                                        <input type="button" value="Отменить" class="i-btn" onclick="document.getElementById('rrbox<?=$curr_sbr->data['id']?>').style.display='none'; document.getElementById('rrtext<?=$curr_sbr->data['id']?>').value=''; return false;" />
                                                  </div>
                                                </div>
                                            </div>
                                        </div>
                                    <? } ?>
                                    <? if ($status == sbr::STATUS_PROCESS && !$curr_sbr->reserved_id) { ?>
                                        <div class="nr-prj-btns c" style="position:relative">
                                            <div class="btn-margin">
                                                <span class="btn-o-red">
                                                    <a href="javascript:;" onclick="$$('.rrbox-class').setStyle('display', 'none'); document.getElementById('rrbox<?=$curr_sbr->data['id']?>').style.display='block'; return false;" class="btnr btnr-red"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Отказаться от проекта</span></span></span></a>
                                                </span>
                                            </div>
                                            <div class="nr-warning">
                                                <strong>Обратите внимание &mdash; заказчик еще не зарезервировал деньги!</strong><br />
                                                Не начинайте выполнять проект до резервирования средств &mdash; так вы убережете себя от обмана.
                                            </div>
                                            <div class="f-overlay f-o-nr-c rrbox-class" id="rrbox<?=$curr_sbr->data['id']?>" style="display:none">
                                                <div class="f-overlay-in">
                                                    <h3>Укажите причину отказа</h3>
                                                    <div class="f-overlay-cnt"><textarea name="frl_refuse_reason" rows="5" cols="10" id="rrtext<?=$curr_sbr->data['id']?>"></textarea></div>
                                                    <div class="f-overlay-btns">
                                                        <input type="submit" name="refuse" value="Отказаться" class="i-btn i-bold" />
                                                        <input type="button" value="Отменить" class="i-btn" onclick="document.getElementById('rrbox<?=$curr_sbr->data['id']?>').style.display='none'; document.getElementById('rrtext<?=$curr_sbr->data['id']?>').value=''; return false;" />
                                                  </div>
                                                </div>
                                            </div>
                                        </div>
                                    <? } ?>
                                </div>
                                <input type="hidden" name="id" value="<?=$curr_sbr->data['id']?>" />
                                <input type="hidden" name="version" value="<?=$curr_sbr->data['version']?>" />
                                <input type="hidden" name="action" value="agree" />
                                </div>
                            </form>
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
