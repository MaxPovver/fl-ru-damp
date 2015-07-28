<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/wizard.common.php");
$xajax->printJavascript('/xajax/'); 
?>
<div class="b-layout">
    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <h1 class="b-page__title b-page__title_padbot_30"><?= $wizard->name;?></h1>
    </div>

    <div class="b-master b-master_emp b-master_clear_both b-master_padbot_30">
        <ul class="b-master__list">
            <?php foreach($wizard->steps as $pos=>$step) { $active = $step->isActive($pos); $disable = $step->isDisable(); ?>
            <li class="b-master__item
                    <?= $pos == count($wizard->steps) ? "b-master__item_last" : "" ?>
                    <?= $pos == 1 ? "b-master__item_first" : "" ?> 
                    <?= $active ? "b-master__item_current" : "" ?> 
                    <?= $disable ? "b-master__item_disabled" : "" ?>" >
                <div class="b-master__left">
                    <div class="b-master__right">
                        <div class="b-master__txt b-master__txt_padtop_25">
                            <?php if(!$active && !$disable) { ?><a class="b-master__link" href="?step=<?= (int) $pos?>"><?php }//if?>
                            <span class="b-master__icon-e b-master__icon-e_<?= $pos?>"></span><?= $step->name?>
                            <?php if(!$active && !$disable) { ?></a><?php }//if?>
                            <?php if($step->isCompleted()) { ?>
                            <span class="b-master__icon-e b-master__icon-e_ok"></span>
                            <?php }//if?>
                        </div>
                    </div>
                </div>
            </li>
            <?php }//foreach?>
        </ul>
    </div>
    
    <?= $wizard->getLastStep()->render(); ?>
		
</div>