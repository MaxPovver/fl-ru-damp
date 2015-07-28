<?php 
// кэшируем версии сделок
$stageVersionsCache = array();

foreach($history as $key=>$event) {
    if(isset($stage->group_history[$xact][$key])) {
        $event['group'] = $stage->group_history[$xact][$key];
    }
    switch($event['abbr']) {
        case 'sbr_stages.PAUSE_OVER':
            ?>
            <div class="b-post__txt b-post__txt_color_a0763b">Этап на паузе &rarr; Этап в работе</div>
            <?
            if( ($event['estatus'] != 't' && $sbr->isEmp()) || ($event['fstatus'] != 't' && !$sbr->isEmp()) ) {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr_stages.PAUSE_RESET':
            if( ($event['estatus'] != 't' && $sbr->isEmp()) || ($event['fstatus'] != 't' && !$sbr->isEmp()) ) {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr.OPEN':
            $reopen = true;
            break;
        case 'sbr.AGREE':
            if($sbr->scheme_type == sbr::SCHEME_LC && $sbr->isFrl()) {
                $data = $sbr->data;
                if($stage->cost <= pskb::WW_ONLY_SUM && $stage->data['tagPerf'] == pskb::PHYS) {
                    $data['ps_frl'] = pskb::WW;
                }
                if ($data['ps_frl'] == pskb::WW) { ?>
                    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">C информацией о «Веб-кошельке» и доступных вам возможностях вы можете ознакомиться <noindex><a rel="nofollow" class="b-post__link" href="https://feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/">здесь</a></noindex>.</div>
                <? } else {
                    $reqv = pskb::getPayedReqvs($data, 'frl');
                    foreach($reqv as $name=>$acc) {
                        if ($acc == '') {
                            continue;
                        } ?>
                        <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b"><?= $name; ?>&nbsp;&nbsp;&nbsp;&nbsp;<?= $acc; ?></div>
                    <? } ?>
                    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Дополнительно вам доступен <a class="b-post__link" href="https://webpay.pscb.ru/login/auth">«Веб-кошелек»</a> (№ <?= $sbr->data['numPerf'] ?>), который в дальнейшем может быть использован для получения денежных средств. С информацией о «Веб-кошельке» и доступных вам возможностях вы можете ознакомиться <noindex><a rel="nofollow" class="b-post__link" href="https://feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/">здесь</a></noindex>.</div>
                <? }
            }
            if( ($event['estatus'] != 't' && $sbr->isEmp())) {
                $update_event_sbr[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr.RESERVE':
            if($sbr->scheme_type == sbr::SCHEME_LC && $sbr->isEmp()) {
                $reqv = pskb::getPayedReqvs($sbr->data, 'emp');
                foreach($reqv as $name=>$acc) { if($acc == '') continue;
                    ?><div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b"><?= $name; ?>&nbsp;&nbsp;&nbsp;&nbsp;<?= $acc; ?></div><?
                } ?>
                <? if ($sbr->data['ps_emp'] != pskb::WW && $sbr->data['ps_emp'] != onlinedengi::BANK_YL) { ?>
                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Дополнительно вам доступен <a class="b-post__link" href="https://webpay.pscb.ru/login/auth">«Веб-кошелек»</a> (№ <?= $sbr->data['numCust'] ?>), который в дальнейшем может быть использован в случае возврата средств. С информацией о «Веб-кошельке» и доступных вам возможностях вы можете ознакомиться <noindex><a rel="nofollow" class="b-post__link" href="https://feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/">здесь</a></noindex>.</div>
                <? } ?>
            <? }
            if( ($event['estatus'] != 't' && $sbr->isEmp()) || ($event['fstatus'] != 't' && !$sbr->isEmp()) ) {
                $update_event_sbr[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr.COMPLETED':
            if( ($event['estatus'] != 't' && $sbr->isEmp()) || ($event['fstatus'] != 't' && !$sbr->isEmp()) ) {
                $update_event_sbr[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr_stages.EMP_ARB':
            if(!$sbr->isEmp() && $event['fstatus'] != 't') {
                $update_event[$event['abbr']] = $event['abbr'];
            }
        case 'sbr_stages.FRL_ARB':
            ?>
            <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_15">
                <?= reformat($event['history_descr'], 30);?>
            </div>
            <div class="b-post__txt b-post__txt_color_a0763b">Этап в работе &rarr; Этап в арбитраже</div>
            <div class="b-post__txt b-post__txt_color_a0763b">
                Старт работ <?= date('d.m.Y', strtotime($stage->data['first_time']))?>, <?= $stage->stageWorkTimeLeft(abs($work_time), array(strtotime($stage->data['first_time']), strtotime($event['xtime'])));?>
                 &rarr; 
                Заморожен  <?= date('d.m.Y', strtotime($event['xtime']))?>
            </div>
            <?
            if($sbr->isEmp() && $event['estatus'] != 't') {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr_stages.COMPLETED':
            $completed_time = $event['xtime'];
            ?>
            <div class="b-post__txt b-post__txt_color_a0763b">
            Старт работ <?= date('d.m.Y', strtotime($stage->data['start_time'] ? $stage->data['start_time'] : $stage->data['first_time'] ))?>
            &rarr;
            Завершен <?= date('d.m.Y', strtotime($event['xtime']))?>
            </div>
            <?
            break;
        case 'sbr_stages.STARTED_WORK':
            if($started_work != 1 && $work_modified == false || ($started_work_view == $started_worked && $stage->orders == 'DESC')) {
                $first_version = $stage->getVersion($frl_version_started_work, $stage->data);
                $work_time = $is_worktime_modified ? intval($first_version['work_time']) : intval($first_version['int_work_time']);
            }
            $started_work = 1;
            ?>
            <div class="b-post__txt b-post__txt_color_a0763b">
            <?= abs($work_time); ?> <?= ending(abs($work_time), 'день', 'дня', 'дней')?> на этап 
                &rarr; 
            Старт работ <span class="b-layout__bold"><?= date('d.m.Y', strtotime($event['xtime']))?></span><?/*, <?= $stage->stageWorkTimeLeft(abs($work_time), array(strtotime($event['xtime']), strtotime($event['xtime'])), '<span class="b-layout__bold">%s</span>');?>*/?>
            </div>    
            <?
            if($sbr->isEmp() && $event['estatus'] != 't') {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            if($sbr->isFrl() && $event['fstatus'] != 't') {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr.CANCEL':
            if($sbr->isFrl() && $event['abbr'] == 'sbr.CANCEL' && $event['fstatus'] != 't') {
                $update_event_sbr[$event['abbr']] = $event['abbr'];
            }
        case 'sbr.REFUSE':
            ?>
            <? if($event['new_val'] != '') {?>
            <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_15">
                <?= reformat(stripslashes($event['new_val']), 45)?>
            </div>  
            <? }//if?>
            <div class="b-post__txt b-post__txt_color_a0763b">Этап не начат &rarr; Этап отменен</div>
            <div class="b-post__txt b-post__txt_color_a0763b"><?=$work_time?> <?= ending($work_time, 'день', 'дня', 'дней');?> на этап &rarr; Отменен <?=date('d.m.Y', strtotime($event['xtime']));?></div>
            <?
            if($sbr->isEmp() && $event['abbr'] == 'sbr.REFUSE' && $event['estatus'] != 't') {
                $update_event_sbr[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr.SCHEME_MODIFIED':
            ?>
            <div class="b-post__txt b-post__txt_color_a0763b">Тип договора «<strong><?= sbr_meta::getNameScheme($event['old_val']);?></strong>» &rarr; Тип договора «<strong><?= sbr_meta::getNameScheme($event['new_val']);?></strong>»</div> 
            <?
            if($sbr->isEmp() && $event['estatus'] != 't') {
                $update_event_sbr[$event['abbr']] = $event['abbr'];
            }
            if($sbr->isFrl() && $event['fstatus'] != 't') {
                $update_event_sbr[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr.COST_SYS_MODIFIED': 
            $cost_sys = $event['new_val'];
            ?>
            <div class="b-post__txt b-post__txt_color_a0763b">Валюта проекта <?= $GLOBALS['EXRATE_CODES'][$event['old_val']][1]?> &rarr; Валюта проекта <?= $GLOBALS['EXRATE_CODES'][$event['new_val']][1]?></div>   
            <?
            break;
        case 'sbr_stages.COST_MODIFIED':
            ?>
            <div class="b-post__txt b-post__txt_color_a0763b">Бюджет этапа <?= sbr_meta::view_cost($event['old_val'], $cost_sys)?> &rarr; Бюджет этапа <?= sbr_meta::view_cost($event['new_val'], $cost_sys)?></div>   
            <?
            break;
        //case 'sbr_stages.REFUSE':
        case 'sbr_stages.AGREE':
            $changedData = sbr_meta::getChangedDataForFreelancer($event['id'], $stage->data['sbr_id'], $stage->data['id']);
            $changedData = array_reverse($changedData, true);
            foreach($changedData as $src_type => $cdata) {
                switch($cdata['src_type_id']) {
                    case 6: // sbr_stages.STATUS_MODIFIED 
                        ?>
                        <div class="b-post__txt <?= isset($changedData[23]) || isset($changedData[8]) ? "": "b-post__txt_padbot_5" ?> b-post__txt_color_a0763b"><?= $stage->getStatusName($cdata['old_val'], false)?> &rarr; <?= $stage->getStatusName($cdata['new_val'], false)?></div>
                        <?
                        if(isset($changedData[23]) && $changedData[23]['new_val'] != null) {
                            $grp = $changedData[23];
                            $start_work_date = strtotime($grp['xtime']) + 86400 * $grp['new_val'];
                            ?>
                            <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Пауза на <?= $grp['new_val']?> <?= ending($grp['new_val'], 'день', 'дня', 'дней')?> &rarr;&nbsp;Возобновление работ <?= date("d.m.Y", $start_work_date);?></div>
                            <?
                        }
                        
                        if(isset($changedData[8]) && $changedData[8]['new_val'] != null) {
                            $grp = $changedData[8];
                            $newVersion = $grp['version'];
                            if ($newVersion == $stage->data['version']) {
                                $newData = $stage->data;
                                $stageVersionsCache[$newVersion] = $newData;
                            } elseif ($newVersion == $stage->v_data['frl_version']) {
                                $newData = $stage->v_data;
                                $stageVersionsCache[$newVersion] = $newData;
                            } elseif (isset($stageVersionsCache[$newVersion])) {
                                $newData = $stageVersionsCache[$newVersion];
                            } else {
                                $newData = $stage->getVersion($newVersion, $stage->data);
                                $stageVersionsCache[$newVersion] = $newData;
                            }
                            $work_time = (int) $newData['work_days'];
                            ?>
                            <div class="b-post__txt b-post__txt_color_a0763b">
                                <?= abs($work_time) . ' ' . ending(abs($work_time), 'день', 'дня', 'дней') ?> на этап &rarr; Возобновление работ <?= date('d.m.Y', strtotime($grp['new_val'])); ?>
                            </div>
                            <?
                        }
                        break;
                    case 9: // sbr_stages.WORKTIME_MODIFIED
                        ?>
                        <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b"><?= (int)$cdata['old_val']. ' ' . ending((int)$cdata['old_val'], 'день', 'дня', 'дней')?> на этап &rarr; <?= (int)$cdata['new_val']. ' ' . ending((int)$cdata['new_val'], 'день', 'дня', 'дней')?> на этап</div>
                        <?
                        break;
                        break;
                    case 4: // sbr_stages.TZ_MODIFIED
                        $newVersion = $cdata['version'];
                        if ($newVersion == $stage->data['version']) {
                            $newData = $stage->data;
                            $stageVersionsCache[$newVersion] = $newData;
                        } elseif ($newVersion == $stage->v_data['frl_version']) {
                            $newData = $stage->v_data;
                            $stageVersionsCache[$newVersion] = $newData;
                        } elseif (isset($stageVersionsCache[$newVersion])) {
                            $newData = $stageVersionsCache[$newVersion];
                        } else {
                            $newData = $stage->getVersion($newVersion, $stage->data);
                            $stageVersionsCache[$newVersion] = $newData;
                        }
                        ?>
                        <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Техническое задание от <a class="b-post__link b-post__link_color_a0763b" href="javascript:void(0)" onclick="$('popup_old_tz_<?= $cdata['id']; ?>').toggleClass('b-shadow_hide');"><?= date('d.m.Y', $newData['date_version_tz'][0])?></a> &rarr; Техническое задание от <a class="b-post__link b-post__link_color_a0763b" href="javascript:void(0)" onclick="$('popup_new_tz_<?= $cdata['id']; ?>').toggleClass('b-shadow_hide');"><?= date('d.m.Y', $newData['date_version_tz'][1])?></a></div>    
                        <?
                        break;
                }
            }
            
            break;
        case 'sbr_stages.TZ_MODIFIED':
            
            // находим данные для новой версии ТЗ
            // возможно нужная версия ТЗ уже где-то есть, и в базу лезть не надо
            $newVersion = $event['version'];
            if ($newVersion == $stage->data['version']) {
                $newData = $stage->data;
            } elseif ($newVersion == $stage->v_data['frl_version']) {
                $newData = $stage->v_data;
            } elseif (isset($stageVersionsCache[$newVersion])) {
                $newData = $stageVersionsCache[$newVersion];
            } else {
                $newData = $stage->getVersion($newVersion, $stage->data);
                $stageVersionsCache[$newVersion] = $newData;
            }
            $newAttach = $newData['attach'];
            
            // находим данные для старой версии ТЗ
            $oldVersion = $newVersion - 1;
            if ($oldVersion == $stage->v_data['frl_version']) {
                $oldData = $stage->v_data;
            } elseif (isset($stageVersionsCache[$oldVersion])) {
                $oldData = $stageVersionsCache[$oldVersion];
            } else {
                $oldData = $stage->getVersion($oldVersion, $stage->data);
                $stageVersionsCache[$oldVersion] = $oldData;
            }
            $oldAttach = $oldData['attach'];
            ?>
            <div id="popup_new_tz_<?= $event['id'] ?>" class="b-shadow b-shadow_center_top b-shadow_zindex_11 b-shadow_width_950 b-shadow_hide">
                <div class="b-shadow__right">
                    <div class="b-shadow__left">
                        <div class="b-shadow__top">
                            <div class="b-shadow__bottom">
                                <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">

                                    <h2 class="b-layout__title">Техническое задание от <?= date('d.m.Y [H:i]', strtotime($event['xtime']))?></h2>
                                    <div class="b-layout__txt b-layout__txt_overflow_auto b-layout__txt_max-height_250 h_400 overflow_auto">
                                        <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_15">
                                            <?= reformat($newData['descr'], 70, 0, 0, 1)?>
                                        </div>
                                        <?php if (is_array($newAttach) && $newAttach && $newAttach = array_filter($newAttach, create_function('$a', 'return $a["is_deleted"] === "f";'))) { ?>
                                        <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_15 b-layout__txt_bold">Вложения</div>
                                        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                                            <tbody>
                                                <? foreach ($newAttach as $doc) { ?>
                                                    <tr class="b-layout__tr">
                                                        <td class="b-layout__left b-layout__left_padright_10 b-layout__left_padbot_5">
                                                            <div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_fontsize_11"><?= date('d.m.Y', strtotime($doc['sign_time'] ? $doc['sign_time'] : $doc['publ_time'])); ?></div>
                                                        </td>
                                                        <td class="b-layout__middle b-layout__middle_padbot_5">
                                                            <div class="b-layout__txt"><i class="b-icon b-icon_attach_<?= getICOFile(($doc['ftype']));?>"></i> 
                                                                <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['path'] . $doc['name']?>" target="_blank"><?= $doc['orig_name'] ? $doc['orig_name'] : $doc['name']?></a>, <?= ConvertBtoMB($doc['size'])?>
                                                            </div>
                                                        </td>
                                                        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
                                                            <div class="b-layout__txt">
                                                                <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['path'] . $doc['name']?>" target="_blank">Скачать</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <? } ?>
                                                <?/*} else { $doc = current($stage->getAttach($event['new_val'], null)); if($doc)?>
                                                    <tr class="b-layout__tr">
                                                        <td class="b-layout__left b-layout__left_padright_10 b-layout__left_padbot_5">
                                                            <div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_fontsize_11"><?= date('d.m.Y', strtotime($doc['sign_time'] ? $doc['sign_time'] : $doc['publ_time'])); ?></div>
                                                        </td>
                                                        <td class="b-layout__middle b-layout__middle_padbot_5">
                                                            <div class="b-layout__txt"><i class="b-icon b-icon_attach_<?= getICOFile(($doc['ftype']));?>"></i> 
                                                                <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['path'] . $doc['name']?>" target="_blank"><?= $doc['orig_name'] ? $doc['orig_name'] : $doc['name']?></a>, <?= ConvertBtoMB($doc['size'])?>
                                                            </div>
                                                        </td>
                                                        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
                                                            <div class="b-layout__txt">
                                                                <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['path'] . $doc['name']?>" target="_blank">Скачать</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                 <? } */?>
                                            </tbody>
                                        </table>
                                        <?php }//if?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-shadow__tl"></div>
                <div class="b-shadow__tr"></div>
                <div class="b-shadow__bl"></div>
                <div class="b-shadow__br"></div>
                <span class="b-shadow__icon b-shadow__icon_close"></span>
            </div>

            <div class="b-shadow b-shadow_center b-shadow_zindex_3 b-shadow_width_950 b-shadow_hide" id="popup_old_tz_<?= $event['id']; ?>">
                <div class="b-shadow__right">
                    <div class="b-shadow__left">
                        <div class="b-shadow__top">
                            <div class="b-shadow__bottom">
                                <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">

                                    <h2 class="b-layout__title">Техническое задание от <?= date('d.m.Y [H:i]', $tz_time)?></h2>
                                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_15 b-layout__txt_overflow_auto b-layout__txt_max-height_250">
                                        <?= reformat($oldData['descr'], 70, 0, 0, 1); ?>
                                    </div>
                                    <?php if (is_array($oldAttach) && $oldAttach && $oldAttach = array_filter($oldAttach, create_function('$a', 'return $a["is_deleted"] === "f";'))) { ?>
                                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_15 b-layout__txt_bold">Вложения</div>
                                    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                                        <tbody>
                                            <? foreach ($oldAttach as $doc) { ?>
                                                <tr class="b-layout__tr">
                                                    <td class="b-layout__left b-layout__left_padright_10 b-layout__left_padbot_5">
                                                        <div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_fontsize_11"><?= date('d.m.Y', strtotime($doc['sign_time'] ? $doc['sign_time'] : $doc['publ_time'])); ?></div>
                                                    </td>
                                                    <td class="b-layout__middle b-layout__middle_padbot_5">
                                                        <div class="b-layout__txt"><i class="b-icon b-icon_attach_<?= getICOFile(($doc['ftype']));?>"></i> 
                                                            <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['path'] . $doc['name']?>" target="_blank"><?= $doc['orig_name'] ? $doc['orig_name'] : $doc['name']?></a>, <?= ConvertBtoMB($doc['size'])?>
                                                        </div>
                                                    </td>
                                                    <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
                                                        <div class="b-layout__txt">
                                                            <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['path'] . $doc['name']?>" target="_blank">Скачать</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <?/*} else { $doc = current($stage->getAttach($event['old_val'], null)); if($doc) ?>
                                                <tr class="b-layout__tr">
                                                    <td class="b-layout__left b-layout__left_padright_10 b-layout__left_padbot_5">
                                                        <div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_fontsize_11"><?= date('d.m.Y', strtotime($doc['sign_time'] ? $doc['sign_time'] : $doc['publ_time'])); ?></div>
                                                    </td>
                                                    <td class="b-layout__middle b-layout__middle_padbot_5">
                                                        <div class="b-layout__txt"><i class="b-icon b-icon_attach_<?= getICOFile(($doc['ftype']));?>"></i> 
                                                            <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['path'] . $doc['name']?>" target="_blank"><?= $doc['orig_name'] ? $doc['orig_name'] : $doc['name']?></a>, <?= ConvertBtoMB($doc['size'])?>
                                                        </div>
                                                    </td>
                                                    <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
                                                        <div class="b-layout__txt">
                                                            <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['path'] . $doc['name']?>" target="_blank">Скачать</a>
                                                        </div>
                                                    </td>
                                                </tr> 
                                             <? } */?>
                                        </tbody>
                                    </table>
                                    <?php }//if?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-shadow__tl"></div>
                <div class="b-shadow__tr"></div>
                <div class="b-shadow__bl"></div>
                <div class="b-shadow__br"></div>
                <span class="b-shadow__icon b-shadow__icon_close"></span>
            </div>
            
            <div class="b-post__txt b-post__txt_color_a0763b">Техническое задание от <a class="b-post__link b-post__link_color_a0763b" href="javascript:void(0)" onclick="$('popup_old_tz_<?= $event['id']; ?>').toggleClass('b-shadow_hide');"><?= date('d.m.Y', $tz_time)?></a> &rarr; Техническое задание от <a class="b-post__link b-post__link_color_a0763b" href="javascript:void(0)" onclick="$('popup_new_tz_<?= $event['id']; ?>').toggleClass('b-shadow_hide');"><?= date('d.m.Y', strtotime($event['xtime']))?></a></div>
            <?
            $tz_time = strtotime($event['xtime']); 
            break;
        case 'sbr_stages.STATUS_MODIFIED':
            if($event['col']  != 'status') {
                if(is_array($event['group']) && !empty($event['group'])) {
                    $group = $event;
                    foreach($event['group'] as $grp) {
                        if($grp['col'] == 'status') {
                            $event = $grp;
                            break;
                        }
                    }
                    $event['group'][] = $group;
                } else {
                    break;
                }
            } 
            ?>
            <div class="b-post__txt b-post__txt_color_a0763b"><?= $stage->getStatusName($event['old_val'], false)?> &rarr; <?= $stage->getStatusName($event['new_val'], false)?></div>
            <?
            if(is_array($event['group']) && !empty($event['group'])) {
                foreach($event['group']  as $grp) {
                    if($grp['col'] == 'days_pause' && $grp['new_val'] > 0) {
                        $start_work_date = strtotime($event['xtime']) + ( $grp['new_val']*24*60*60 );
                        ?>
                        <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Пауза на <?= $grp['new_val']?> <?= ending($grp['new_val'], 'день', 'дня', 'дней')?> &rarr;&nbsp;Возобновление работ <?= date("d.m.Y", $start_work_date);?></div>    
                        <?
                    }
                }
            }
            break;
        case 'sbr_stages.WORKTIME_MODIFIED':
            ?>
            <? if($event['col'] == 'start_time' && $event['new_val'] != NULL) { ?>
            <div class="b-post__txt b-post__txt_color_a0763b">
                <?= abs($work_time). ' ' . ending(abs($work_time), 'день', 'дня', 'дней')?> на этап &rarr; Возобновление работ <?= date('d.m.Y', strtotime($event['new_val']));?><?/*, <?= $stage->stageWorkTimeLeft(abs($work_time), array(strtotime($event['new_val']), strtotime($event['xtime'])));?>*/?>
            </div>
            <? } elseif($event['col'] == 'work_time') { //if
                $old_time = $stage->getStageWorkTime($event['old_val']); 
                $work_time = $stage->getStageWorkTime($event['new_val']);
                $work_modified = true;
            ?>
            <div class="b-post__txt b-post__txt_color_a0763b"><?= $old_time. ' ' . ending($old_time, 'день', 'дня', 'дней')?> на этап &rarr; <?= $work_time. ' ' . ending($work_time, 'день', 'дня', 'дней')?> на этап</div>
            <? }//else?>
            <?
            break;
        case 'sbr_stages.ADD_DOC':
        case 'sbr.ADD_DOC':
            $files = $docs = array();
            $doc = $sbr->getDocs($event['src_id']);
            if(is_array($doc)) $docs[] = current($doc);
            if($event['group']) {
                foreach($event['group'] as $gr_file) {
                    $doc = $sbr->getDocs($gr_file['src_id']);
                    if(is_array($doc)) $docs[] = current($doc);
                }
            } 
            if(count($docs) <= 0) break;
            foreach($docs as $doc) {
                $file = new CFile($doc['file_id']);
                $file->original_name = $doc['name'];
                $files[] = $file;
            }
            ?>
            <table class="b-layout__table  b-layout__table_margtop_10" cellpadding="0" cellspacing="0" border="0">
            <?php foreach($files as $file) { ?>
                <tr class="b-layout__tr">
                    <td class="b-layout__middle b-layout__middle_padbot_5"><div class="b-layout__txt"><i class="b-icon b-icon_attach_<?=getICOFile($file->getext())?>"></i> <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$file->path . $file->name?>" target="_blank"><?= $file->original_name?></a>, <?= ConvertBtoMB($file->size)?></div></td>
                    <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5"><div class="b-layout__txt"><a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$file->path . $file->name?>" target="_blank">Скачать</a></div></td>
                </tr>
            <?php } //if?>
            </table>
            <?
            break;
        case 'sbr_stages.ARB_CANCELED':
            $i_work_time = $work_time < 0 ? 0 : $work_time;
            ?>
            <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Этап в арбитраже &rarr; Этап в работе</div>
            <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b"><?= $i_work_time. ' ' . ending($i_work_time, 'день', 'дня', 'дней')?> на этап &rarr; Cтарт работ <span class="b-layout__bold"><?= date('d.m.Y', strtotime($stage->data['start_time']))?></span>, <?= $stage->stageWorkTimeLeft($i_work_time, array(strtotime($stage->data['start_time']), strtotime($event['xtime'])), '<span class="b-layout__bold">%s</span>');?></div> 
            <?
            $update_event[$event['abbr']] = $event['abbr'];
            break;
        case 'sbr_stages.ARB_RESOLVED':
            $stage->tmp_doc_arb = $stage->sbr->getLastPublishedDocByType(sbr::DOCS_TYPE_ARB_REP, $stage->id);
            ?>
            <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_15"><?= reformat($stage->arbitrage['descr_arb'], 40, 0, 0, 1)?></div>
            <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b"><span class="b-post__bold">Заказчику вернуть <?=100*(1-$stage->arbitrage['frl_percent'])?>%</span> бюджета проекта, <?=sbr_meta::view_cost($stage->getPayoutSum(sbr::EMP), $stage->sbr->cost_sys)?></div>
            <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b"><span class="b-post__bold">Исполнителю заплатить <?=100*$stage->arbitrage['frl_percent']?>%</span> бюджета проекта, <?=sbr_meta::view_cost($stage->getPayoutSum(sbr::FRL), $stage->sbr->cost_sys)?></div>
            <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Решение: <?= $stage->arbitrage['result'] == '' ? 'Расторжение договора' : reformat(str_replace(array('e%', 'f%'), array((100 * (1 - $stage->arbitrage['frl_percent'])) . "%", ( 100 * $stage->arbitrage['frl_percent']) . "%" ), $stage->arbitrage['result']), 40, 0, 0, 1)?></div>
            <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Этап в арбитраже &rarr; Этап завершен арбитражем</div>
            <?
            if( ($event['estatus'] != 't' && $sbr->isEmp()) || ($event['fstatus'] != 't' && !$sbr->isEmp()) ) {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr_stages.EMP_FEEDBACK':
            if(!$sbr->isEmp() && $event['fstatus'] != 't') {
                $update_event[$event['abbr']] = $event['abbr'];
            }
        case 'sbr_stages.FRL_FEEDBACK':
            ?>
            <span class="b-button b-button_margtop_1 b-button_padright_5 b-button_float_left <?= sbr_meta::getAdviceICO((int)$feedback['rating'])?> b-button_active"></span><br/>
            <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_15"><?= reformat($feedback['descr'], 40, 0, 0, 1)?></div>   
            <?
            if($sbr->isEmp() && $event['abbr'] == 'sbr_stages.FRL_FEEDBACK' && $event['estatus'] != 't') {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr_stages.MONEY_PAID':
            if($stage->status == sbr_stages::STATUS_ARBITRAGED) {
                ?><div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Сожалеем, что пришлось обращаться в арбитраж. Надеемся, следующая ваша сделка будет удачнее.</div><?
            } else {
                ?><div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Спасибо, что воспользовались «Безопасной Сделкой»!</div><?
            }
            if(!$sbr->isEmp() && $event['fstatus'] != 't') {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr_stages.EMP_MONEY_REFUNDED':
            ?>
            <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">Сожалеем, что пришлось обращаться в арбитраж. Надеемся, следующая ваша сделка будет удачнее.</div>    
            <?
            if($sbr->isEmp() && $event['estatus'] != 't') {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr_stages.OVERTIME':
//            $cdate = new LocalDateTime($event['xtime']);
//            $cdate->getWorkForDay(pskb::PERIOD_EXP);
//            $overtime = strtotime($event['xtime'] . " + " . $cdate->getCountDays(). "day");
            $overtime = strtotime("-2 day", strtotime($sbr->data['dateEndLC']));
            while(date('w', $overtime)==0 || date('w', $overtime)==6) {
                $overtime = strtotime("-1 day", $overtime);
            }
            $str_overtime = date('d', $overtime). " " . monthtostr(date('n', $overtime), true) . " " . date('Y', $overtime);
            if($sbr->isEmp()) {
                ?>
                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_padleft_10 b-post__txt_indent_-10 b-post__txt_color_a0763b">&bull; Если вы довольны выполненной работой, примите ее, нажав &laquo;Принять работу&raquo; наверху страницы.</div>
                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_padleft_10 b-post__txt_indent_-10 b-post__txt_color_a0763b">&bull; Если исполнитель еще не закончил, добавьте время на работу.</div>
                <?php if(time() <= $overtime) { ?>
                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_padleft_10 b-post__txt_indent_-10 b-post__txt_color_a0763b">&bull; Если вы не довольны работой с исполнителем и нет возможности увеличить время на работу, <a class="b-layout__link" href="javascript:void(0)" onclick="toggle_arb();" >обратитесь в арбитраж</a>.</div>
                <?php }//if?>
                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_padleft_10 b-post__txt_indent_-10 b-post__txt_color_a0763b">Если до <?= $str_overtime;?> вы не решите, как поступить, деньги будут возвращены вам, а исполнитель не получит гонорар.</div>
                <?
            } else {
                $contractDocLink = $sbr->getDocumentLink('contract');
                ?>
                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_padleft_10 b-post__txt_indent_-10 b-post__txt_color_a0763b">&bull; Если вы выполнили работу, свяжитесь с заказчиком, чтобы он принял проект.</div>
                <?php if(time() <= $overtime) { ?>
                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_padleft_10 b-post__txt_indent_-10 b-post__txt_color_a0763b">&bull; Если заказчик не выходит на связь, <a class="b-layout__link" href="javascript:void(0)" onclick="toggle_arb();">обратитесь в арбитраж</a>.</div>
                <?php }//if?>
                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_padleft_10 b-post__txt_indent_-10 b-post__txt_color_a0763b">&bull; Если до <?= $str_overtime;?>, заказчик не примет работу (и ни одна из сторон не обратится в Арбитраж), то согласно пункту 9.2 <a class="b-layout__link" href="<?= $contractDocLink ?>">Договора</a> ему будут возвращены деньги, и вы не получите гонорар.</div>
                <?
            }
            if( ($event['estatus'] != 't' && $sbr->isEmp()) || ($event['fstatus'] != 't' && !$sbr->isEmp()) ) {
                $update_event[$event['abbr']] = $event['abbr'];
            }
            break;
        case 'sbr_stages.DOCS_NOTE':
            ?>
            <div class="b-post__txt b-post__txt_color_a0763b" style="margin-top: -8px;">
                Закрывающие документы (Акт, счет-фактура) формируются и загружаются в сделку в начале следующего месяца.
                Для получения закрывающих документов укажите, пожалуйста, почтовый адрес компании на вкладке <a class="b-layout__link" href="/users/<?= $_SESSION['login'] ?>/setup/finance/">Финансы</a> вашего аккаунта.
                На указанный адрес вам будут высланы закрывающие документы на сумму вознаграждения Общества (ООО "Ваан").
                Для того чтобы получить оригинал Акта на полную сумму сделки, обратитесь ко второму участнику сделки.
            </div>
            <?
            break;
    }
} //foreach
?>