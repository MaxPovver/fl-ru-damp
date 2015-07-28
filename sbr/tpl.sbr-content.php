<? if (!$sbr_currents) {?>
<div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_padtop_8">
    <?=($count_sbr && ( $filter == '' || $filter == 'complete' ) ? '': sbr::$name_filter[$filter])?>
</div>
<?php if(!$count_sbr && $count_old_sbr > 0 && $filter == '') { ?>
<div class="b-layout__txt_padtop_20 b-layout__txt_padleft_20 b-layout__txt_fontsize_22">
    <a href="?site=archive" class="b-layout__link b-layout__link_bordbot_dot_0f71c8"><?= $count_old_sbr; ?> <?= ending($count_old_sbr, "сделка, завершенная", "сделки, завершенные", "сделок, завершенных")?> в старом интерфейсе (перенесено в "Архив")</a>
</div>
<?php }//if?>
<? } else {?> 

    <? // проверяем есть ли хоть одна сделка в состоянии ЗАРЕЗЕРВИРОВАТЬ ДЕНЬГИ
    $needReserveSbrExists = false;
    if ($filter === 'disable') {
        foreach ($sbr_currents as $curSBR) {
            if (!$curSBR->reserved_id && $curSBR->status == sbr::STATUS_PROCESS && $curSBR->state != 'new') {
                $needReserveSbrExists = true;
                break;
            }
        }
    }
    if ($needReserveSbrExists && $sbr->isEmp()) { ?>
        <div class="b-fon b-fon_padbot_10 b-fon_padtop_15">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                <div class="b-fon__txt b-fon__txt_linheight_18 b-fon__txt_pad_10_10 b-layout_overflow_hidden">
                    <div class="b-layout__txt_float_left">
                        <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25 b-layout__txt_absolute" style="margin:1px 0 0 -23px;"></span>
                        Выберите сделку, для которой хотите зарезервировать деньги.
                    </div>
                </div>
            </div>
        </div>
    <? } ?>

<? foreach($sbr_currents as $id=>$curr_sbr) { $i++; ?>
    <?php if($curr_sbr->data['status'] == sbr::STATUS_NEW) { ?>
        <? include ($_SERVER['DOCUMENT_ROOT']."/sbr/{$fpath}/tpl.new.php");?>
    <?php } else {//if?>
        <? include ($_SERVER['DOCUMENT_ROOT']."/sbr/tpl.sbr.php");?>
    <?php }//else?>
<? }?>
<? }?>