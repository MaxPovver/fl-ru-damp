<? if($sbr->status == sbr::STATUS_PROCESS && !$sbr->data['reserved_id'] && $sbr->state != 'new') {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
        <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
        Исполнитель не приступит к работе, пока вы не <a class="b-fon__link" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=reserve&id=<?= $sbr->id?>">зарезервируете деньги</a> под сделку.
	</div>
</div>	
<? } elseif($sbr->status == sbr::STATUS_PROCESS && !$sbr->data['reserved_id'] && $sbr->state == 'new') {
    $cdate = new LocalDateTime($sbr->pskb_created);
    $cdate->getWorkForDay(pskb::PERIOD_RESERVED);
    $pskb_created = $cdate->getTimestamp();
?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>
        Вам необходимо зарезервировать деньги на сделку до <?= date('d', $pskb_created)?> <?= monthtostr(date('n', $pskb_created), true)?> <?= date('Y', $pskb_created)?>. В противном случае сделка будет отменена (согласно пунктам 4.3 и 15.8 <a class="b-layout__link" href="<?= $sbr->getDocumentLink('contract') ?>">Договора</a>).
	</div>
</div>	
<? }//elseif?>

<? /*
 * 
 * <? if($sbr->scheme_type == sbr::SCHEME_LC) { ?>
        Вам необходимо <a href="/sbr/?site=reserve&id=<?= $sbr->id?>">зарезервировать деньги</a> на сделку до 16 сентября 2012, 10:09. В противном случае сделка будет отменена.
        <? } else { //if?>
 * if($stage->status == sbr_stages::STATUS_PROCESS && $sbr->data['reserved_id']) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
			<span class="b-icon b-icon_sbr_brur b-icon_margleft_-20"></span>Заказчик зарезервировал деньги на сделку, можно приступать к работе. Удачи :)
	</div>
</div>	
<? } */?>

<? if($stage->status == sbr_stages::STATUS_INARBITRAGE) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
	<span class="b-icon b-icon_sbr_avesy b-icon_margleft_-20"></span>
        Решение будет вынесено до <?= $stage->getStrOvertimeArbitrage()?>, после чего этап будет завершен. Арбитражная комиссия предложит вам урегулировать ситуацию по взаимной договоренности с другой стороной. В случае, если договоренность не будет достигнута, арбитраж примет одно из решений, указанных в пункте 9.9 <a class="b-layout__link" href="<?= $sbr->getDocumentLink('contract'); ?>">Договора</a>.
        </div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_COMPLETED && !$stage->data['emp_feedback_id']) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <? if ($sbr->status == sbr::STATUS_COMPLETED) { ?>
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>Этап не будет закрыт до тех пор, пока вы не оставите отзыв исполнителю и отзыв сервису «Безопасная Сделка».
        <? } else { ?>
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>Этап не будет закрыт до тех пор, пока вы не оставите отзыв исполнителю.
        <? } ?>
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
                Арбитраж принял решение о выплате 100% бюджета исполнителю
            <? } elseif ($frlPercent === (float)0) { ?>
                Арбитраж принял решение о возвращении вам 100% бюджета
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

