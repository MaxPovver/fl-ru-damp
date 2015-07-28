<? if($sbr->status == sbr::STATUS_PROCESS && !$sbr->data['reserved_id']) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
			<span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>Не начинайте работу, пока заказчик не зарезервирует деньги под эту сделку!
	</div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_PROCESS && $sbr->data['reserved_id']) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
			<span class="b-icon b-icon_sbr_brur b-icon_margleft_-20"></span>Заказчик зарезервировал деньги на сделку, можно приступать к работе.
	</div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_COMPLETED && $stage->notification['ntype'] == 'sbr_stages.FRL_FEEDBACK') {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>
        Чтобы получить заработанные деньги, вы должны выслать <a class="b-fon__link b-fon__link_bordbot_dot_0f71c8" href="javascript:void(0);" onclick="JSScroll($('head_docs'));">подписанные документы</a>.
	</div>
</div>
<? } ?>

<? if($stage->status == sbr_stages::STATUS_INARBITRAGE) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
        <span class="b-icon b-icon_sbr_avesy b-icon_margleft_-20"></span>
        Решение будет вынесено до <?= $stage->getStrOvertimeArbitrage()?>, после чего этап будет завершен. Арбитражная комиссия предложит вам урегулировать ситуацию по взаимной договоренности с другой стороной. В случае, если договоренность не будет достигнута, арбитраж примет одно из решений, указанных в пункте 9.9 <a class="b-layout__link" href="<?= $sbr->getDocumentLink('contract'); ?>">Договора</a>.
        </div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_ARBITRAGED) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
			<span class="b-icon b-icon_sbr_aok b-icon_margleft_-20"></span>
            <?
            $frlPercent = (float)$stage->arbitrage['frl_percent'];
            $byConsent = $stage->arbitrage['by_consent'] === 't';
            $byAward   = $stage->isByAward();
            if ($frlPercent === (float)1) { ?>
                Арбитраж принял решение о выплате вам 100% бюджета.
            <? } elseif ($frlPercent === (float)0) { ?>
                Арбитраж принял решение о возвращении 100% бюджета заказчику.
            <? } else { ?>
                Арбитраж завершил этот этап. 
                <? if($byAward) {?>
                По решению арбитража бюджет был разделен.
                <? } elseif ($byConsent) { ?>
                По соглашению сторон бюджет был разделен.
                <? }?>
            <? } ?>
	</div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_COMPLETED && !$stage->data['frl_feedback_id'] && $stage->sbr->scheme_type == sbr::SCHEME_LC) { 
    // $completed_time -- время завершения сделки берется из файла tpl.stage-history-event.php -- чтобы лишний раз не выбирать его
    $cdate = new LocalDateTime($completed_time);
    $cdate->getWorkForDay(pskb::PERIOD_FRL_EXEC);
    $pskb_created = $cdate->getTimestamp();
    $overtime_completed = strtotime($completed_time . ' + ' . pskb::PERIOD_FRL_EXEC . 'day');?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>
        Чтобы получить заработанные деньги, вам необходимо нажать кнопку «Завершить этап» до <?= date('d', $overtime_completed)?> <?= monthtostr(date('n', $overtime_completed), true)?> <?= date('Y', $overtime_completed)?>.
	</div>
</div>	
<? }//if?>
