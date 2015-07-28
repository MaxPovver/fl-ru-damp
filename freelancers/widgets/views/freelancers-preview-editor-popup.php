<?php

/**
 * Шаблон выбора работы/услуги
 */

?>
<form action="" method="post">
    <div class="b-layout" data-tab-panel="<?=$tab_panel_name?>">
        <div class="b-menu b-menu_line b-menu_padbot_20">
            <ul class="b-menu__list">
                <?php foreach($tabs as $key => $value): ?>
                <li data-tab-item="<?= $key ?>" class="b-menu__item <?php if(isset($value['active']) && $value['active']): ?>b-menu__item_active<?php endif; ?>">
                    <a class="b-menu__link" href="javascript:void(0);" title="<?= $value['title'] ?>">
                        <span class="b-menu__b1"><?= $value['title'] ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php foreach($tabs as $key => $value): ?>
        <div class="b-layout<?php if(!isset($value['active']) || !$value['active']): ?> g-hidden<?php endif; ?>" data-tab-content="<?= $key ?>">
    <?php
            include('freelancers-preview-editor-popup-tab-content.php');
    ?>
        </div>
        <?php endforeach; ?>
    </div>
    <input type="hidden" name="pos" value="" />
    <input type="hidden" name="group" value="<?=$group_id?>" />
    <input type="hidden" name="prof" value="<?=$prof_id?>" />
    <input type="hidden" name="hash" value="<?=$hash?>" />
</form>