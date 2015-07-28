<h2 class="b-layout__title <?= ($i > 1?"b-layout__title_padtop_40":"")?> b-layout__title_padbot_15 b-layout__title_padleft_15 b-layout__title_lineheight_28" id="sbrList<?= $curr_sbr->id;?>">
    <?= reformat($curr_sbr->data['name'], 35, 0, 1) ?> <?= $curr_sbr->getContractNum() ?>
</h2>
<? 
$completed = $stpos = 0;
$stcount = sizeof($curr_sbr->stages);
foreach($curr_sbr->stages as $num=>$stage) { $stpos++;
    $stage->initNotification();
    if($stage->data['status'] == sbr::STATUS_COMPLETED) $completed++;
    include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-list.php");
}//foreach
?>
<? if($completed > 0 && $curr_sbr->data['status'] != sbr::STATUS_COMPLETED) {?>
<div class="b-layout__txt b-layout__txt_padleft_35 b-layout__txt_padtop_8"><a class="b-layout__link b-layout__link_bordbot_dot_000" href="#"><?= $completed?> <?= ending($completed, "завершенный этап", "завершенных этапа", "завершенных этапов")?></a></div>
<? }//if?>
<? include($_SERVER['DOCUMENT_ROOT']."/sbr/{$fpath}/tpl.alert.php");?>
