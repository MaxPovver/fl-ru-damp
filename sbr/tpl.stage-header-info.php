<div class="b-layout b-layout_padtop_7 <?= ( $stage->isMoreActionInHeader() || $stage->isAccessComplete() ? 'b-layout_bordbot_dedfe0' : '' ) ?>">
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                <!-- status stage -->
                <? if( $stage->isAccessComplete() )  {  ?>
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">
                    <span class="b-layout__txt b-layout__txt_color_808080"><?= $stage->getStatusName($stage->data['status'], false) ?></span>
                    &rarr; <?= $stage->getStatusName(sbr_stages::STATUS_COMPLETED, false); ?>
                </div>
                <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_color_a0763b">Нужно оставить отзыв и принять работу</div>
                <? } else if ($stage->isNewVersionStatus() && $stage_changed_for_frl) { ?>
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">
                    <span class="b-icon b-icon_top_1 b-icon_margleft_-20 <?= $stage->getStatusIco($stage->data['status'], false); ?>"></span>
                    <span class="b-layout__txt b-layout__txt_color_808080"><?= $stage->getStatusName($stage->v_data['status'], false) ?></span> 
                    &rarr; <?= $stage->getStatusName($stage->data['status'], false); ?>
                </div>
                <? } elseif ($stage->isNewVersionStatus() && $stage_changed) { //if?>
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">
                    <span class="b-layout__txt b-layout__txt_color_c7271e b-layout__txt_through"><span class="b-icon b-icon_top_1 b-icon_margleft_-20 <?= $stage->getStatusIco($stage->data['status'], false); ?>"></span><?= $stage->getStatusName($stage->v_data['status'], false); ?></span> 
                    &rarr; <?= $stage->getStatusName($stage->data['status'], false); ?>
                </div>
                <? } else {//elseif?>
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">
                    <span class="b-icon <?= $stage->getStatusIco(false, false); ?> b-icon_top_1 b-icon_margleft_-20"></span> <?= $stage->getStatusName(false, false) ?>
                </div>
                    <? 
                    if($stage->data['status'] == sbr_stages::STATUS_FROZEN && $stage->data['days_pause'] != null) {
                        $start_work_date = strtotime($stage->data['start_pause']) + ( $stage->data['days_pause']*24*60*60 );
                    ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10">Пауза на <span class="b-layout__bold"><?= $stage->data['days_pause'];?> <?= ending($stage->data['days_pause'], 'день', 'дня','дней')?></span></div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">Возобновление работ <span class="b-layout__bold"><?= date("d.m.Y", $start_work_date);?></span></div>
                    <? }//if?>
                <? }//if?>
                    
                <div class="b-layout__txt b-layout__txt_padbot_15 <?= $stage->getStatusColor(); ?>">
                <? if (in_array($stage->notification['xact_id'], $stage->active_event) && 
                       !($stage->notification['ntype'] === 'sbr_stages.COMPLETED' || 
                         $stage->notification['ntype'] === 'sbr_stages.FRL_PAID' || 
                         $stage->notification['ntype'] === 'sbr_stages.EMP_PAID' ||
                         $stage->notification['ntype'] === 'sbr_stages.DOC_RECEIVED')) { ?>
                    <a class="b-layout__link b-layout__link_dot_a0763b" href="javascript:void(0)" onclick="JSScroll($('evn_<?= $stage->notification['xact_id'] ?>'))"><?= $stage->getNotificationName(); ?></a>
                <? } else { //if?>
                    <?= $stage->getNotificationName(); ?>
                <? }//else ?>
                </div>
            </td>
            
            <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps ">
                
                <? if(!$stage->isAccessComplete())  include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/{$fpath}tpl.stage-action.php"); ?>

                
                <? if($stage->isAccessComplete()) { $sum_frl = $stage->getPayoutSum(sbr::FRL); ?>
                <div class="b-layout__txt b-layout__txt_padbot_10">
                    Версия технического задания от <span class="b-layout__bold"><?= date('d.m.Y', $stage->data['date_version_tz'][1]) ?></span>
                </div>
                <div class="b-layout__txt b-layout__txt_padbot_10">
                    Исполнитель получит <span class="b-layout__bold"><?= to_money($sum_frl)?> руб.</span>
                </div>
                <? } else {

                    switch($sbr->status) {
                        case sbr::STATUS_CANCELED:
                            ?>
                            <div class="b-layout__txt b-layout__txt_padbot_10">Отменен <span class="b-layout__bold"><?= date('d.m.Y', $stage->canceled_time) ?></span></div>
                            <div class="b-layout__txt b-layout__txt_padbot_15">Бюджет этапа <span class="b-layout__bold"><?= sbr_meta::view_cost($stage->cost, $sbr->cost_sys) ?></span></div>
                            <?
                            break;
                        case sbr::STATUS_REFUSED:
                            ?>
                            <div class="b-layout__txt b-layout__txt_padbot_10">Отменен <span class="b-layout__bold"><?= date('d.m.Y', $stage->refused_time) ?></span></div>
                            <div class="b-layout__txt b-layout__txt_padbot_15">Бюджет этапа <span class="b-layout__bold"><?= sbr_meta::view_cost($stage->cost, $sbr->cost_sys) ?></span></div>
                            <?
                            break;
                        default:
                            $type_payment = $stage->type_payment;
                            if (in_array($stage->status, array(sbr_stages::STATUS_ARBITRAGED, sbr_stages::STATUS_COMPLETED))) {
                                $sum_emp = $stage->getPayoutSum(sbr::EMP);
                                $sum_frl = $stage->getPayoutSum(sbr::FRL);

                                $psys_descr_to = '';
                                if ($sbr->scheme_type != sbr::SCHEME_LC) {
                                    $dvals = ($stage->type_payment == exrates::WMR || $stage->type_payment == exrates::YM ? array('P' => '3') : array() );
                                } else {
                                    $dvals = array('P' => pskb::$exrates_map[$stage->data['ps_frl']]);
                                    $psys_descr_to = pskb::$psys_dest[$stage->data['ps_frl']];
                                    $type_payment = exrates::BANK;
                                }
    //                            $sum_taxes = $stage->calcAllTax(sbr::FRL, $dvals);
    //                            $sum_frl   = $sum_frl_ - $sum_taxes;//$stage->getPayoutSum(sbr::FRL);
                            }
                            switch($stage->status) {
                                case sbr_stages::STATUS_ARBITRAGED:
                                    ?>
                                    <div class="b-layout__txt b-layout__txt_padbot_10">
                                        Завершен <span class="b-layout__bold"><?= date('d.m.Y', strtotime($stage->data['closed_time'])) ?></span>
                                    </div>
                                    <?php
                                    if($sbr->isEmp()) {
                                        if($sum_emp > 0) { ?>
                                        <div class="b-layout__txt b-layout__txt_padbot_15">
                                            <?
                                            // перебираем историю этапа и ищем sbr_stages.EMP_MONEY_REFUNDED
                                            foreach ($stage->history as $event) {
                                                if (array_key_exists('sbr_stages.EMP_MONEY_REFUNDED', $event)) {
                                                    $refunded = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <? if ($refunded || $stage->notification['ntype'] == 'sbr_stages.EMP_MONEY_REFUNDED') { ?>
                                            Возвращено <span class="b-layout__bold"><?= sbr_meta::view_cost($sum_emp, $stage->sbr->cost_sys) ?></span>
                                            <? } else { ?>
                                            Вам вернут <span class="b-layout__bold"><?= sbr_meta::view_cost($sum_emp, $stage->sbr->cost_sys) ?></span>
                                            <? }//else ?>
                                        </div>
                                        <? } elseif($sum_frl > 0) {?>
                                        <div class="b-layout__txt b-layout__txt_padbot_15">
                                            <span class="b-layout__bold"><?= sbr_meta::view_cost($sum_frl, $stage->sbr->cost_sys) ?></span> выплачены исполнителю
                                        </div>
                                        <? } //elseif
                                    } elseif($sbr->isFrl()) { //if @todo нет условия для просмотра админом
                                        if($sum_frl > 0) { ?>
                                        <div class="b-layout__txt b-layout__txt_padbot_15">
                                            <? if ($stage->notification['ntype'] == 'sbr_stages.MONEY_PAID') { ?>
                                            Вы получили <span class="b-layout__bold"><?= sbr_meta::view_cost($sum_frl, $stage->sbr->cost_sys) ?></span>
                                            <? } else { ?>
                                            Вы получите <span class="b-layout__bold"><?= sbr_meta::view_cost($sum_frl, $stage->sbr->cost_sys) ?></span>  <?= $psys_descr_to ?>
                                            <? }//if ?>
                                        </div>
                                        <? } elseif($sum_emp > 0) {?>
                                        <div class="b-layout__txt b-layout__txt_padbot_15">
                                            <span class="b-layout__bold"><?= sbr_meta::view_cost($sum_emp, $stage->sbr->cost_sys) ?></span> будут возвращены заказчику
                                        </div>
                                        <? } //elseif
                                    } //elseif

                                    break;
                                case sbr_stages::STATUS_COMPLETED:
                                    ?>
                                    <div class="b-layout__txt b-layout__txt_padbot_10">
                                        Завершен <span class="b-layout__bold"><?= date('d.m.Y', strtotime($stage->data['closed_time'])) ?></span>
                                    </div>
                                    <div class="b-layout__txt b-layout__txt_padbot_15">
                                    <? if ($sbr->isFrl()) {
                                        if ($stage->type_payment == exrates::FM) {
                                            $cost = $sum_frl * $sbr->exrates[$sbr->cost_sys . exrates::FM];
                                        } else {
                                            $cost = $sum_frl;
                                        }
                                        ?>
                                        Вы <?=$frl_sum_paid? "получили" : "получите"?> <span class="b-layout__bold"><?= sbr_meta::view_cost($cost, $type_payment) ?></span> <?= $psys_descr_to ?>
                                    <? } else { ?>
                                        Бюджет <span class="b-layout__bold"><?= sbr_meta::view_cost($stage->cost, $sbr->cost_sys) ?></span>
                                    <? }//else ?>
                                    </div>
                                    <?
                                    break;
                                default:
                                    ?>
                                    <!--  TZ -->
                                    <div class="b-layout__txt b-layout__txt_padbot_10">
                                    <? if ($stage->isNewVersionTZ() && $stage_changed_for_frl) { ?>
                                        <span class="b-layout__txt b-layout__txt_color_808080">
                                            Техническое задание от <span class="b-layout__bold"><?= date('d.m.Y', $stage->data['date_version_tz'][0]) ?></span>
                                        </span>
                                        &rarr; версия <span class="b-layout__bold">от <?= date('d.m.Y', $stage->data['date_version_tz'][1]) ?></span>
                                    <? } elseif ($stage->isNewVersionTZ() && $stage_changed) { $new_descr_ex = true; //if ?>
                                        <span class="b-layout__txt b-layout__txt_color_c7271e b-layout__txt_through">Техническое задание от <span class="b-layout__bold"><?= date('d.m.Y', $stage->data['date_version_tz'][0]) ?></span></span>
                                        &rarr; версия <span class="b-layout__bold">от <?= date('d.m.Y', $stage->data['date_version_tz'][1]) ?></span>
                                    <? } elseif ($stage->status != sbr_stages::STATUS_ARBITRAGED) { //if?>
                                        Версия технического задания <span class="b-layout__bold">от <?= date('d.m.Y', $stage->data['date_version_tz'][1]) ?></span>
                                    <?php }//else ?>
                                    </div>

                                    <!-- Время работы -->
                                    <div class="b-layout__txt b-layout__txt_padbot_10">
                                    <? if ($stage->isNewVersionWorkTime() && $stage_changed_for_frl && !($stage->data['status'] == sbr_stages::STATUS_FROZEN && $stage->v_data['work_time'] == $stage->data['work_time'])) { ?>
                                        <span class="b-layout__txt b-layout__txt_color_808080">
                                            <span class="b-layout__bold"><?= $stage->getStageWorkTime($stage->v_data['work_time']); ?> <?= ending($stage->getStageWorkTime($stage->v_data['work_time']), 'день', 'дня', 'дней') ?></span> на этап
                                        </span>
                                        &rarr;
                                        <? if ($stage->data['status'] == sbr_stages::STATUS_PROCESS) { ?>
                                            Старт работ <span class="b-layout__bold"><?= date('d.m.Y', strtotime($stage->data['first_time'])) ?></span>, <?= $stage->stageWorkTimeLeft(false, array(), '<span class="b-layout__bold">%s</span>');?>
                                        <? } else { ?>
                                            <span class="b-layout__bold"><?= $stage->getStageWorkTime($stage->data['work_time']); ?> <?= ending($stage->getStageWorkTime($stage->v_data['work_time']), 'день', 'дня', 'дней'); ?></span> на этап
                                        <? }//else?>
                                    <? } elseif ($stage->isNewVersionWorkTime() && $stage_changed && !($stage->data['status'] == sbr_stages::STATUS_FROZEN && $stage->v_data['work_time'] == $stage->data['work_time'])) { $new_work_time_ex = true; ?>
                                        <span class="b-layout__txt b-layout__txt_color_c7271e b-layout__txt_through"><span class="b-layout__bold"><?= $stage->getStageWorkTime($stage->v_data['work_time']); ?> <?= ending($stage->getStageWorkTime($stage->v_data['work_time']), 'день', 'дня', 'дней') ?></span> на этап</span>
                                        &rarr;
                                        <? if ($stage->data['status'] == sbr_stages::STATUS_PROCESS) { ?>
                                            Старт работ <span class="b-layout__bold"><?= date('d.m.Y', strtotime($stage->data['first_time'])) ?></span>, <?= $stage->stageWorkTimeLeft(false, array(), '<span class="b-layout__bold">%s</span>');?>
                                        <? } else { ?>
                                            <span class="b-layout__bold"><?= $stage->getStageWorkTime($stage->data['work_time']); ?> <?= ending($stage->getStageWorkTime($stage->data['work_time']), 'день', 'дня', 'дней'); ?></span> на этап
                                        <? }//if ?>
                                    <? } elseif ($stage->data['status'] == sbr_stages::STATUS_INARBITRAGE) {//if?>
                                        Заморожен <span class="b-layout__bold"><?= date('d.m.Y', strtotime($stage->arbitrage['requested'])) ?></span>,  <?= $stage->stageWorkTimeLeft(false, array(strtotime($stage->data['first_time']), strtotime($stage->arbitrage['requested'])), '<span class="b-layout__bold">%s</span>');?>
                                    <? } else { //if ?>
                                        <? if ($stage->data['status'] == sbr_stages::STATUS_PROCESS) {?>
                                            Старт работ <span class="b-layout__bold"><?= date('d.m.Y', strtotime($stage->data['first_time'])) ?></span>, <?= $stage->stageWorkTimeLeft(false, array(strtotime($stage->data['start_time_without_pause'])), '<span class="b-layout__bold">%s</span>');?>
                                        <? } else { //if?>
                                            <span class="b-layout__bold"><?= ago_pub(time() + $stage->work_rem * 3600 * 24, 'ynj') ?></span> на этап
                                        <? } //else?>
                                    <? } //else ?>
                                    </div>
                                    <?
                                    // проверяем в какой валюте будет выплата
                                    if (!$stage->payouts) {
                                        $stage->getPayouts($sbr->uid);
                                    }
                                    if ($stage->payouts) {
                                        $stagePayouts = current($stage->payouts);
                                        $creditSysFM = $stagePayouts['credit_sys'] == 1;
                                    }
                                    ?>
                                    <!-- budget -->
                                    <div class="b-layout__txt b-layout__txt_padbot_10">
                                    <? if ($stage->isNewVersionCost() && $stage_changed_for_frl) { ?>
                                        <span class="b-layout__txt b-layout__txt_color_808080">
                                            Бюджет этапа <span class="b-layout__bold"><?= sbr_meta::view_cost($stage->v_data['cost'], $sbr->v_data['cost_sys']) ?></span>
                                        </span>
                                        &rarr; Бюджет этапа <span class="b-layout__bold"><?= sbr_meta::view_cost($stage->cost, $sbr->cost_sys) ?></span>
                                    <? } elseif ($stage->isNewVersionCost() && $stage_changed) { $new_cost_ex = true; ?>
                                        <span class="b-layout__txt b-layout__txt_color_c7271e b-layout__txt_through">Бюджет этапа <span class="b-layout__bold"><?= sbr_meta::view_cost($stage->v_data['cost'], $sbr->v_data['cost_sys']) ?></span></span>
                                        &rarr; Бюджет этапа <span class="b-layout__bold"><?= sbr_meta::view_cost($stage->cost, $sbr->cost_sys) ?></span>
                                    <? } else { ?>
                                        Бюджет этапа
                                        <? if ($sbr->isEmpReservedMoney() && !$sbr->isEmp()) { ?>
                                            <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_000 open-tax" onclick="$(this).getParent('td').getElement('.b-tax').toggleClass('b-tax_hide')" href="javascript:void(0)"><?= sbr_meta::view_cost($stage->data['cost'], exrates::BANK) ?></a>
                                        <? } else { //if
                                            // выводить сумму в FM или рублях
                                            $cost = $creditSysFM ? $stage->data['cost'] * $sbr->exrates[$sbr->cost_sys . 1] : $stage->data['cost'];
                                            $costSys = $creditSysFM ? 1 : exrates::BANK;
                                            ?>
                                            <span class="b-layout__bold"><?= sbr_meta::view_cost($cost, $costSys) ?></span>
                                        <? } //else ?>
                                    <? } //if ?>
                                    </div>

                                    <?
                                    if ($sbr->isEmpReservedMoney() && !$sbr->isEmp()) {
                                        print $stage->_new_getTaxInfo();
                                    } //if
                                    break;
                            } //switch
                            break;
                    } //switch
                }
                ?>
            </td>
        </tr>
    </table>
</div>
<style type="text/css">.b-button-multi{ margin-left:20px;}</style>