<h2 class="b-layout__title b-layout__title_padbot_20 b-layout__title_padtop_65">История этапа &#160;&#160;&#160;
    
     <div class="b-filter">
        <div class="b-filter__body">
            <a class="b-txt__lnk b-txt__lnk_dot_0f71c8 b-txt__lnk_inline-block b-txt__lnk_fs_15 b-filter__sbr_order" href="javascript:void(0)">Последние сообщения <?= $stage->orders == 'DESC' ? 'сверху' : 'снизу'?></a> <span class="b-filter__arrow b-filter__arrow_0f71c8 b-filter__arrow_margtop_6"></span>
        </div>
        <div class="b-shadow b-shadow_marg_-5_-11 b-filter__toggle b-filter__toggle_hide">
                            <div class="b-shadow__body b-shadow__body_pad_10 b-shadow__body_bg_fff b-shadow_overflow_hidden">
                                <ul class="b-filter__list">
                                    <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_15 sbr_message_option" data-type="asc">
                                        <a class="b-txt__lnk b-txt__lnk_fs_15 b-txt__lnk_dot_0f71c8 b-txt__lnk_inline-block" href="javascript:void(0)">Последние сообщения снизу</a> <?= $stage->orders == 'ASC' ? '<span class="b-filter__marker b-filter__marker_top_6 b-filter__marker_galka"></span>' : ''?>
                                    </li>
                                    <li class="b-filter__item b-filter__item_lineheight_15 sbr_message_option" data-type="descr">
                                        <a class="b-txt__lnk b-txt__lnk_fs_15 b-txt__lnk_dot_0f71c8 b-txt__lnk_inline-block" href="javascript:void(0)">Последние сообщения сверху</a> <?= $stage->orders == 'DESC' ? '<span class="b-filter__marker b-filter__marker_top_6 b-filter__marker_galka"></span>' : ''?>
                                    </li>
                                </ul>
                            </div>
        </div>
    </div>

</h2>
<? 
if($stage->status != sbr_stages::STATUS_ARBITRAGED && $sbr->status != sbr::STATUS_CANCELED && $sbr->status != sbr::STATUS_REFUSED && $sbr->reserved_id && $stage->orders == 'DESC') {
    include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-comment.php");
}

$update_event = array();
$update_event_sbr = array();
$cost_sys = $sbr->cost_sys;
$tz_time  = strtotime($stage->data['created']);
$work_time = $stage->data['int_work_time'];
$frl_arb   = false;
$is_worktime_modified = sbr_stages::isModifiedWorkTime($stage->history);

$frl_version_started_work = 1;
if($stage->orders == 'DESC') {
    $started_worked = 0;
    foreach($stage->history as $xact => $history) { $current = current($history); 
        if($current['abbr'] == 'sbr_stages.STARTED_WORK') $started_worked++;
    }
    
    $reverse_history = array_reverse($stage->history);
    $changed = array('sbr_stages.TZ_MODIFIED', 'sbr_stages.WORKTIME_MODIFIED', 'sbr_stages.COST_MODIFIED', 'sbr_stages.STATUS_MODIFIED');
    
    foreach($reverse_history as $xact => $history) { $current = current($history);
        if(in_array($current['abbr'], $changed)) {
            $frl_version_started_work++;
        }
        if($current['abbr'] == 'sbr_stages.AGREE') break;
    }
}

if ($sbr->isEmp() && $stage->data['tagCust'] == 1) {
    // ищем запись о завершении этапа
    $eventOffset = 0;
    foreach ($stage->history as $histKey => $hist) {
        $eventOffset++;
        // добавляем новую запись: справочная информация о документах по сделке
        if ($hist['sbr_stages.COMPLETED']) {
            $newEvent = array(
                'sbr_stages.DOCS_NOTE' => array(
                    'own_role'  => '3',
                    'abbr'      => 'sbr_stages.DOCS_NOTE',
                    'xtime'     => $hist['sbr_stages.COMPLETED']['xtime']
                )
            );
            
            $arr1 = array_slice($stage->history, 0, $eventOffset, true);
            $arr1['_' . $histKey] = $newEvent;
            $arr2 = array_slice($stage->history, $eventOffset, NULL, true);
            $stage->history = $arr1 + $arr2;
            
            break;
        }
    }
}

$started_work_view = 0;
foreach($stage->history as $xact => $history) { $current = current($history);
    if( empty(sbr_notification::$history[$current['abbr']]) ) continue; // Если нет названия в истории то не выводим.
    if($reopen && $current['abbr'] == 'sbr.OPEN') $current['abbr'] = 'sbr.REOPEN';
    if($current['abbr'] == 'sbr_stages.STARTED_WORK') $started_work_view++;
    if($current['abbr'] == 'sbr_stages.EMP_MONEY_REFUNDED' && $sbr->isFrl()) continue;
    if($current['abbr'] == 'sbr_stages.MONEY_PAID' && $sbr->isEmp()) continue;
    if($current['abbr'] == 'sbr_stages.DOC_RECEIVED') continue;
    if($current['abbr'] == 'sbr_stages.FRL_FEEDBACK') {
        $feedback = $stage->getFeedback($stage->data['frl_feedback_id']);
        $uniq_id = $feedback['id'] * 2 + 1; // @see sbr_meta::getUserFeedbacks();
        $current['additional']  = '<a class="b-post__link b-post__link_color_a0763b" href="/users/'.$stage->sbr->data['emp_login'].'/opinions/#p_'.$uniq_id.'" target="_blank">';
        $current['additional'] .= ( $feedback['rating'] == 1 ? 'положительный ' : ($feedback['rating'] == 0 ? 'нейтральный' : 'отрицательный' ) ) . ' отзыв';
        $current['additional'] .= '</a>';
    }
    if($current['abbr'] == 'sbr_stages.EMP_FEEDBACK') {
        $feedback = $stage->getFeedback($stage->data['emp_feedback_id']);
        $uniq_id = $feedback['id'] * 2 + 1;
        $current['additional']  = '<a class="b-post__link b-post__link_color_a0763b" href="/users/'.$stage->sbr->data['frl_login'].'/opinions/#p_'.$uniq_id.'" target="_blank">';
        $current['additional'] .= ( $feedback['rating'] == 1 ? 'положительный' : ($feedback['rating'] == 0 ? 'нейтральный' : 'отрицательный' ) ) . ' отзыв';
        $current['additional'] .= '</a>';
    }
    
    if($current['abbr'] == 'sbr_stages.FRL_ARB') {
        $frl_arb = true;
    } elseif($current['abbr'] == 'sbr_stages.EMP_ARB') {
        $frl_arb = false;
    }
    
    //if($sbr->isFrl() && ($current['abbr'] == 'sbr_stages.ADD_DOC' || $current['abbr'] == 'sbr.ADD_DOC') && $current['foronly_role'] == 2) continue;
    //if($sbr->isEmp() && ($current['abbr'] == 'sbr_stages.ADD_DOC' || $current['abbr'] == 'sbr.ADD_DOC') && $current['foronly_role'] == 1) continue;
    
    if($current['abbr'] == 'sbr_stages.ADD_DOC' || $current['abbr'] == 'sbr.ADD_DOC') {
        $doc = $sbr->getDocs($current['src_id']);
        if(!$doc) continue;
    }
   if($current['abbr'] == 'sbr_stages.EMP_MONEY_REFUNDED') {
       $type_payment = $stage->sbr->scheme_type == sbr::SCHEME_LC ? pskb::$exrates_map[$stage->sbr->data['ps_emp']] : ( $stage->sbr->cost_sys == exrates::FM ? $stage->sbr->cost_sys : null );
       if($stage->data['lc_state'] == pskb::PAYOUT_ERR) {
           $type_payment = exrates::WEBM; // Если происходит ошибка выплаты деньги всегда идут на веб-кошелек
       }
       $current['additional'] = 'На ' . sbr_meta::view_type_payment($type_payment, ($type_payment == exrates::CARD ? 'вашу ' : 'ваш ')) . ' были переведены ' . sbr_meta::view_cost($stage->getPayoutSum(sbr::EMP), $stage->sbr->cost_sys) . ' Зачисление денежных средств произведено ' . date('d.m.Y в H:i', strtotime($stage->data['lc_date'])) . ' согласно пункту 6.7 <a class="b-layout__link" href="' . $sbr->getDocumentLink('contract') . '">Договора</a>.';
   }
   if($current['abbr'] == 'sbr_stages.MONEY_PAID') {
       $type_payment = $stage->sbr->scheme_type == sbr::SCHEME_LC ? pskb::$exrates_map[$stage->data['ps_frl']] : ( $stage->type_payment == exrates::FM ? $stage->type_payment : null );
       if($stage->data['lc_state'] == pskb::PAYOUT_ERR) {
           $type_payment = exrates::WEBM; // Если происходит ошибка выплаты деньги всегда идут на веб-кошелек
       }
       $current['additional'] = 'На ' . sbr_meta::view_type_payment($type_payment, ($type_payment == exrates::CARD ? 'вашу ' : 'ваш ') ) . ' были переведены ' . sbr_meta::view_cost($stage->getPayoutSum(sbr::FRL, $type_payment ), $stage->type_payment == exrates::FM ? $stage->type_payment : $stage->sbr->cost_sys) . ' Зачисление денежных средств произведено '. date('d.m.Y в H:i', strtotime($stage->data['lc_date'])) . ' согласно пункту 6.7 <a class="b-layout__link" href="' . $sbr->getDocumentLink('contract') . '">Договора</a>.';
   }
   
   if($current['abbr'] == 'sbr_stages.STATUS_MODIFIED' && $current['new_val'] == sbr_stages::STATUS_COMPLETED) {
       $current['abbr'] .= '_OK';
   }
   
   if($current['abbr'] == 'sbr_stages.REFUSE' && $current['new_val'] == '') {
       $current['additional'] =  $sbr->isEmp() ? " и предпочел не указывать причину" : " и предпочли не указывать причину";
   }
   
   if($current['abbr'] == 'sbr_stages.OVERTIME') {
       $cdate = new LocalDateTime($current['xtime']);
       $cdate->getWorkForDay(pskb::PERIOD_EXP);
       $overtime = strtotime($current['xtime'] . " + " . $cdate->getCountDays() . "day");
       $current['additional'] = date('d', $overtime). " " . monthtostr(date('n', $overtime), true) . " " . date('Y', $overtime);
   }
   
   if($current['abbr'] == 'sbr.AGREE' && ( $sbr->isFrl() || $sbr->isAdmin() || $sbr->isAdminFinance() ) ) {
       $type_payment = $stage->sbr->scheme_type == sbr::SCHEME_LC ? pskb::$exrates_map[$stage->data['ps_frl']] : $stage->type_payment ;
       if ($stage->data['ps_frl'] == pskb::WW) {
           $current['additional'] = '. Способ получения гонорара — <a class="b-post__link" href="https://webpay.pscb.ru/login/auth">Веб-кошелек</a> (№ ' . $sbr->data['numPerf'] . ')';
       } else {
           $current['additional'] = ', выбрав вывод ' . sbr_meta::view_type_payment($type_payment, 'на ');
       }
   }
   
    if($current['abbr'] == 'sbr.RESERVE' && ( $sbr->isEmp() || $sbr->isAdmin() || $sbr->isAdminFinance() )) {
       $type_payment = $stage->sbr->scheme_type == sbr::SCHEME_LC ? pskb::$exrates_map[$stage->sbr->data['ps_emp']] : $stage->sbr->cost_sys;
       $current['additional'] = ' через ' . sbr_meta::view_type_payment($type_payment);
   }
   
    if ($current['abbr'] == 'sbr_stages.ARB_RESOLVED') {
        // пункт договора в зависимости от решения арбитража
        if ($stage->isByConsent() && $stage->arbitrage['frl_percent'] == 0) { // возврат 100% по договоренности
            $current['additional'] = '9.5.2';
        } elseif (!$stage->isByConsent() && $stage->arbitrage['frl_percent'] == 0) { // возврат 100% решением арбитража
            $current['additional'] = '9.9.3';
        } elseif (!$stage->isByConsent() && $stage->arbitrage['frl_percent'] == 1) { // выплата 100% решением арбитража
            $current['additional'] = '9.9.1';
        } elseif ($stage->isByAward()) { // разделение по решению арбитража
            $current['additional'] = '9.9.2';
        } else { // остается только выплата 100% или разделение по договоренности
            $current['additional'] = '9.5.1';
        }
    }
?>

<?
// должно ли событие подсветиться зеленым? учитывается также источник события, то есть не подсвечиваются события которые исходят от просматривающего пользователя
$greenEvent = in_array($current['xact_id'], $stage->active_event) && !(($sbr->isFrl() && $current['own_role'] == 1) || ($sbr->isEmp() && $current['own_role'] == 2));
// @todo логика верстки не самодостаточна. данные дивы нужны для выделения, хотя можно было сверстать все в одном диве + добавить 1 класс для измненения цвета фона
if ($greenEvent) { ?>
<div class="b-fon b-fon_marg_-15_-10 b-fon_padbot_30">
    <div class="b-fon__body b-fon__body_pad_5_10_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf" id="event_react_<?= $current['xact_id']?>">
<? }//if?>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0" id="evn_<?= $xact?>">
            <tr class="b-layout__tr">
                <td class="b-layout__left <? if (!$greenEvent) { ?>b-layout__left_padbot_30<? }//if?> b-layout__left_width_72ps">
                    <div class="b-post">
                        <div class="b-post__body">
                            <div class="b-post__avatar b-post__avatar_padtop_4 b-post__avatar_margright_10 absolute">
                                <? if ($current['abbr'] == "sbr_stages.COMMENT") { // аватарка для комментариея
                                    if ($current['foronly_role'] == 1) { // фрилансер ?>
                                        <a class="b-post__link" href="/users/<?= $sbr->data['frl_login']?>/"><?=view_avatar($sbr->data['frl_login'], $sbr->data['frl_photo'], 1, 1, $cls="b-post__userpic")?></a>
                                    <? } else { // работодатель ?>
                                        <a class="b-post__link" href="/users/<?= $sbr->data['emp_login']?>/"><?=view_avatar($sbr->data['emp_login'], $sbr->data['emp_photo'], 1, 1, $cls="b-post__userpic")?></a>
                                    <? }
                                } else if($current['abbr'] == "sbr_stages.OVERTIME") { ?>
                                    <a class="b-post__link" href="#"><img class="b-post__userpic" src="/images/temp/g-rur.png" width="50" height="50" alt="" /></a>  
                                <? } elseif($current['own_role'] == 1) { ?>
                                    <a class="b-post__link" href="/users/<?= $sbr->data['frl_login']?>/"><?=view_avatar($sbr->data['frl_login'], $sbr->data['frl_photo'], 1, 1, $cls="b-post__userpic")?></a>
                                <? } elseif($current['own_role'] == 2 || $current['own_role'] == 0) { //if?>
                                    <a class="b-post__link" href="/users/<?= $sbr->data['emp_login']?>/"><?=view_avatar($sbr->data['emp_login'], $sbr->data['emp_photo'], 1, 1, $cls="b-post__userpic")?></a>
                                <? } elseif($current['own_role'] == 3 && $current['abbr'] != 'sbr_stages.EMP_MONEY_REFUNDED' && $current['abbr'] != 'sbr_stages.MONEY_PAID') {?>
                                    <? if($stage->status == sbr_stages::STATUS_ARBITRAGED || $stage->status == sbr_stages::STATUS_INARBITRAGE) {?>
                                    <a class="b-post__link" href="#"><img class="b-post__userpic" src="/images/temp/arbitration.png" width="50" height="50" alt="" /></a>
                                    <? } else { ?>
                                    <img class="b-post__userpic" src="/images/temp/g-rur.png" width="50" height="50" />
                                    <? } ?>
                                <? } elseif( ($current['abbr'] == 'sbr_stages.EMP_MONEY_REFUNDED' || $current['abbr'] == 'sbr_stages.MONEY_PAID') && $stage->status == sbr_stages::STATUS_ARBITRAGED) {?>
                                    <a class="b-post__link" href="#"><img class="b-post__userpic" src="/images/temp/b-rur.png" width="50" height="50" alt="" /></a>
                                <? } elseif( ($current['abbr'] == 'sbr_stages.EMP_MONEY_REFUNDED' || $current['abbr'] == 'sbr_stages.MONEY_PAID') && $stage->status != sbr_stages::STATUS_ARBITRAGED) {?> 
                                    <a class="b-post__link" href="#"><img class="b-post__userpic" src="/images/temp/g-rur.png" width="50" height="50" alt="" /></a>
                                <? } else {//else?>
                                    <img class="b-post__userpic" src="/images/temp/g-rur.png" width="50" height="50" alt="" />
                                <? }?>
                            </div>
                            <div class="b-post__content b-post__content_margleft_65 min-height_70">
                                <? if($current['abbr'] == 'sbr_stages.COMMENT') { $pfx = $current['foronly_role'] == 1 ? 'frl_' : 'emp_';?>
                                <div class="b-username b-username_relative b-username_bold b-username_padbot_10">
                                    <?= $session->view_online_status($sbr->data[$pfx . 'login'], false, '&nbsp;', $activity) ?><a href="/users/<?= $sbr->data[$pfx . 'login']?>/" class="b-username__link <?= $pfx == 'emp_' ? "b-username__link_color_6db335":"b-username__link_color_f2922a"?>"><?= $sbr->data[$pfx . 'uname']?>&nbsp;<?=$sbr->data[$pfx . 'usurname']?></a>
                                    <span class="b-username__login <?= $pfx == 'emp_' ? "b-username__login_color_6db335" : "b-username__login_color_f2922a"?>">[<a href="/users/<?= $sbr->data[$pfx . 'login']?>/" class="b-username__link b-username__link_color_6db335"><?= $sbr->data[$pfx . 'login']?></a>]</span><? $data = $sbr->data; ?> <?= view_mark_user_div($data[$pfx . "is_pro"] === 't', $pfx === "emp_", $data[$pfx . "is_team"] === 't', "") ?><?= $sbr->data[$pfx . 'is_verify'] == 't' ? view_verify() : '';?>
                                </div>
                                <div class="b-post__txt b-post__txt_fontsize_15">
                                <? 
                                $current['msg'] = preg_replace("/(<code {1,}.*style {0,})=/imsU", "$1&#61;", $current['msg']);
                                $current['msg'] = preg_replace("/(onmouseover|onclick|ondblclick|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup {0,})=/imsU", "$1&#61;", $current['msg']);
                                ?> 
                                <?= reformat2($current['msg'], 40);?>
                                </div>
                                <? if($current['src_id'] > 0) { $attach = $stage->getMsgAttach($current['own_id']);?>
                                <table class="b-layout__table b-layout__table_margtop_10" cellpadding="0" cellspacing="0" border="0">
                                    <? foreach($attach as $src) {?>
                                    <tr class="b-layout__tr">
                                        <td class="b-layout__middle b-layout__middle_padbot_5"><div class="b-layout__txt"><i class="b-icon b-icon_attach_<?=getICOFile(CFile::getext($src['name']))?>"></i> <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?= $src['path'] . $src['name'] ?>" target="_blank"><?= $src['orig_name']?></a>, <?= ConvertBtoMB($src['size'])?></div></td>
                                        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5"><div class="b-layout__txt"><a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?= $src['path'] . $src['name'] ?>" target="_blank">Скачать</a></div></td>
                                    </tr>
                                    <? }//foreach?>
                                </table>
                                <? }//if 
                                if( ($current['estatus'] != 't' && $sbr->isEmp()) || ($current['fstatus'] != 't' && !$sbr->isEmp()) ) {
                                    $is_run_comment = true;
                                }
                                } elseif($current['abbr'] == 'sbr_stages.ARB_COMMENT') {//if?>
                                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_bold">Менеджер арбитража</div>
                                <div class="b-post__txt b-post__txt_fontsize_15"><?= reformat2($current['msg'], 40);?></div>
                                <? if($current['src_id'] > 0) { $attach = $stage->getMsgAttach($current['own_id']);?>
                                <table class="b-layout__table b-layout__table_margtop_10" cellpadding="0" cellspacing="0" border="0">
                                    <? foreach($attach as $src) {?>
                                    <tr class="b-layout__tr">
                                        <td class="b-layout__middle b-layout__middle_padbot_5"><div class="b-layout__txt"><i class="b-icon b-icon_attach_<?=getICOFile(CFile::getext($src['name']))?>"></i> <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?= $src['path'] . $src['name'] ?>" target="_blank"><?= $src['orig_name']?></a>, <?= ConvertBtoMB($src['size'])?></div></td>
                                        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5"><div class="b-layout__txt"><a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?= $src['path'] . $src['name'] ?>" target="_blank">Скачать</a></div></td>
                                    </tr>
                                    <? }//foreach?>
                                </table>
                                <? }//if
                                if( ($current['estatus'] != 't' && $sbr->isEmp()) || ($current['fstatus'] != 't' && !$sbr->isEmp()) ) {
                                    $is_run_comment = true;
                                }
                                } else {?>
                                <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_color_a0763b">
                                    <?
                                    if ($sbr->isNewContract() && in_array($current['abbr'], array('sbr_stages.STATUS_MODIFIED_OK', 'sbr_stages.FRL_FEEDBACK', 'sbr_stages.EMP_FEEDBACK'))) {
                                        $current_abbr = $current['abbr'] . '_NEW_CONTRACT';
                                    } else {
                                        $current_abbr = $current['abbr'];
                                    }
                                    $historyName = sbr_notification::getHistoryName($current_abbr, ( $sbr->isEmp() ? 0 : ( $sbr->isFrl() ? 1 : 2 ) ), $current['additional']);
                                    // для определенных типов уведомлений подставляем ссылку на договор
                                    if (in_array($current['abbr'], array('sbr.COST_SYS_MODIFIED', 'sbr_stages.COST_MODIFIED', 'sbr_stages.TZ_MODIFIED', 'sbr_stages.STATUS_MODIFIED', 'sbr_stages.STATUS_MODIFIED_OK', 'sbr_stages.WORKTIME_MODIFIED', 'sbr_stages.EMP_ARB', 'sbr_stages.FRL_ARB', 'sbr_stages.ARB_RESOLVED'))) {
                                        $contractDocLink = $sbr->getDocumentLink('contract');
                                        $historyName = str_replace('link_offer_lc', $contractDocLink, $historyName);
                                    }
                                    ?>
                                    <?= $historyName ?>
                                </div>
                                <? }//else?>
                                <? if($current['abbr'] == 'sbr_stages.REFUSE' && $current['new_val'] != '') { ?>
                                    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15"><?= reformat($current['new_val'], 45);?></div>
                                <? }//if?>
                                <? include ($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.stage-history-event.php'); ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="b-layout__right">
                    <div class="b-post__time b-post__time_float_right">
                        <a class="b-post__anchor b-post__anchor_margright_10" href="#n_<?=$xact?>" onclick="setTimeout('JSScroll($(\'evn_<?= $xact?>\'));', 100);"></a><?= date('d.m.Y в H:i', strtotime($current['xtime'])); ?>
                    </div>
                    <?php if(in_array($current['xact_id'], $stage->active_event) && $sbr->isAdmin()) { ?>
                    <div class="b-post_clear_both b-post_float_right b-post_padtop_10" id="adm_react_link_<?= $current['xact_id'];?>">
                        <a href="javascript:void(0)" onclick="if(confirm('Вы действительно хотите завершить данное событие?')) xajax_aCompleteEvent('<?=$current['xact_id']; ?>')" class="b-layout__link b-layout__link_color_ee1d16 b-layout__link_bordbot_dot_ee1d16">Завершить</a>
                    </div>
                    <?php }?>
                </td>
            </tr>
        </table>
<? if ($greenEvent) {?>
    </div>
</div>
<? }//if?>
<? } //foreach

if(count($update_event) > 0) { 
    sbr_notification::setNotificationCompleted($update_event, $stage->data['sbr_id'], $stage->id);
}
if(count($update_event_sbr) > 0) {
    sbr_notification::setNotificationCompleted($update_event_sbr, $stage->data['sbr_id'], $stage->data['sbr_id']);
}
if($is_run_comment == true) {
    sbr_notification::setNotificationCommentViewCompleted($stage->data['sbr_id'], $stage->id);
}
?>
