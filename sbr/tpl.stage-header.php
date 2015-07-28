<div class="b-fon b-fon_padbot_30">
    <div class="b-fon__inner b-fon__inner_bordtop_cdcdcd b-fon__inner_bordbot_cdcdcd">
        <div class="b-fon__body <?= (sbr_notification::isReaction($stage->notification) ? "b-fon__body_bg_f0ffdf" : "b-fon__body_bg_eff2f3");?> b-fon__body_bordtop_e4e7e8 b-fon__body_bordbot_e4e7e8">
            
            <? include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-header-info.php");?>
            
            <? include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/{$fpath}tpl.stage-header.php"); ?>
            
            <? include ($_SERVER['DOCUMENT_ROOT']. "/sbr/tpl.stage-header-form.php")?>
        </div>
    </div>
</div>