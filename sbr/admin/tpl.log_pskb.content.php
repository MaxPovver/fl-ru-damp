<? if($content) { ?>

<a class="b-layout__link b-layout__link_fontsize_15 b-layout__link_float_right b-layout__link_dot_c10600" 
   title="Данные метод очистит только дублирующие данные для текущего аккредитива, при этом оставит в таблице первое вхождение дубля и последнее." 
   href="javascript:void(0)" 
   onclick="if(confirm('Очистить лог от дублей?')) { xajax_aClearCloneLogPSKB('<?= $lc_id;?>', '<?= $query; ?>', '<?= $logname;?>'); }">Очистить дублирующие логи</a>
<br/>
<br/>
<table class="nr-a-opinions" cellspacing="0" style="width: 100%">
    <thead>
        <tr>
            <th>#</th>
            <th>URL</th>
            <th>Название</th>
            <th>Дата запроса</th>
            <th>Параметры</th>
            <th>Ответ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($content as $log) { $data = unserialize($log['log']); $_url = parse_url($data['request_url']);?>
        <tr class="<?= (++$i % 2 == 0 ? 'even' : 'odd') ?>">
            <td><?= $i?>.</td>
            <td title="<?= $data['request_url']?>">
                <?= $data['request_url']?>
                <?= $data['request_url'] == '' ? "---" : "" ?>
                <div id="log_pskb_param_<?= $log['id']?>" class="i-shadow_center b-shadow_hide" style="z-index:10000">																						
                    <div class="b-shadow b-shadow_width_950 b-shadow_zindex_11">
                        <div class="b-shadow__right">
                            <div class="b-shadow__left">
                                <div class="b-shadow__top">
                                    <div class="b-shadow__bottom">
                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                            <strong>Параметры запроса</strong>
                                            <pre><?= var_export($data['param']); ?></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-shadow__tl"></div>
                        <div class="b-shadow__tr"></div>
                        <div class="b-shadow__bl"></div>
                        <div class="b-shadow__br"></div>
                        <div class="b-shadow__icon b-shadow__icon_close" onclick="$('log_pskb_param_<?= $log['id']?>').addClass('b-shadow_hide');"></div>
                    </div>
                </div>
                
                <div id="log_pskb_response_<?= $log['id']?>" class="i-shadow_center b-shadow_hide" style="z-index:10000">																						
                    <div class="b-shadow b-shadow_width_950 b-shadow_zindex_11">
                        <div class="b-shadow__right">
                            <div class="b-shadow__left">
                                <div class="b-shadow__top">
                                    <div class="b-shadow__bottom">
                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                            <strong>Ответ</strong><br/>
                                            <? if(is_string($data['response'])) {?>
                                            <textarea cols="140" onclick="$(this).select()"><?= $data['response'];?></textarea><br/>
                                            <? }//if?>
                                            <? if(is_array($data['response'])) { ?>
                                            <pre><?= var_export($data['response']); ?></pre>
                                            <? } else {//if ?>
                                            <pre><?= var_export(json_decode($data['response'],1)); ?></pre>
                                            <? }//else?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-shadow__tl"></div>
                        <div class="b-shadow__tr"></div>
                        <div class="b-shadow__bl"></div>
                        <div class="b-shadow__br"></div>
                        <div class="b-shadow__icon b-shadow__icon_close" onclick="$('log_pskb_response_<?= $log['id']?>').addClass('b-shadow_hide');"></div>
                    </div>
                </div>
            </td>
            <td><?= $log['logname']?></td>
            <td><?= date('d.m.Y H:i:s', strtotime($log['date_created'])); ?></td>
            <td>
                <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="$('log_pskb_param_<?= $log['id']?>').toggleClass('b-shadow_hide');" title="Посмотреть параметры запроса">Параметры</a>
            </td>
            <td><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="$('log_pskb_response_<?= $log['id']?>').toggleClass('b-shadow_hide');" title="Посмотреть ответ">Ответ</a></td>
        </tr>
        <?php } ?>  
    </tbody>
</table>
<? } else { ?>
<div style="padding:10px">
Логи по данному аккредитиву не найдены.
</div>
<? } ?>
