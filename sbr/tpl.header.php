<div class="b-menu b-menu_line b-menu_padbot_20 b-menu_overflow_visible">
<?php /*
    <ul class="b-menu__list b-menu__right-list" style="float:right;">
        <li class="b-menu__item b-menu__item_padtop_3"><a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=calc" class="b-menu__link"><span class="b-icon b-icon_float_left b-icon_margtop_4 b-icon_sbr_calc"></span>Калькулятор БС</a></li>
        <li class="b-menu__item b-menu__item_padtop_3"><a href="javascript:void(0)" id="document_show" class="b-menu__link b-menu__link_bordbot_dot_0f71c8"><span class="b-icon b-icon_float_left b-icon_margtop_4 b-icon_sbr_doc"></span>Документы</a>
            <div class="i-shadow">								
                <div id="document_links" class="b-shadow b-shadow_m b-shadow_right_0 b-shadow_top_5 b-shadow_width_260 b-shadow_hide">
                    <div class="b-shadow__right">
                        <div class="b-shadow__left">
                            <div class="b-shadow__top">
                                <div class="b-shadow__bottom">
                                    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                        <div class="b-shadow__txt"><a class="b-shadow__link" target="_blank" href="/offer_work_employer.pdf">Договор подряда</a>, PDF, 176кб</div>
                                        <div class="b-shadow__txt"><a class="b-shadow__link" target="_blank" href="/offer_lc.pdf">Аккредитив</a>, PDF, 213кб</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="b-shadow__icon b-shadow__icon_nosik b-shadow__icon_right_50"></span>
                </div>
            </div>							
        </li>
    </ul>
*/ ?>
    <ul class="b-menu__list b-menu__list_padleft_10">
        <li class="b-menu__item <?= ($filter == '' && $site != 'drafts' ? 'b-menu__item_active':'')?>">
            <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/" class="b-menu__link">
                <span class="b-menu__b1">Все сделки</span>
            </a>
        </li>
        <li class="b-menu__item <?= $filter == 'disable' || $filter == 'disable_emp' ? 'b-menu__item_active' : ''?>">
            <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?filter=disable" class="b-menu__link">
                <span class="b-menu__b1"><span class="b-icon b-icon_sbr_stime"></span>На согласовании</span>
            </a>
        </li>
        <li class="b-menu__item <?= $filter == 'enable' ? 'b-menu__item_active' : ''?>">
            <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?filter=enable" class="b-menu__link">
                <span class="b-menu__b1"><span class="b-icon b-icon_sbr_bplay"></span>В работе</span>
            </a>
        </li>
        <li class="b-menu__item <?= $filter == 'cancel' ? 'b-menu__item_active' : ''?>">
            <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?filter=cancel" class="b-menu__link">
                <span class="b-menu__b1"><span class="b-icon b-icon_sbr_rdel"></span>Отмененные</span>
            </a>
        </li>
        <li class="b-menu__item <?= $filter == 'complete' ? 'b-menu__item_active' : ''?>">
            <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?filter=complete" class="b-menu__link">
                <span class="b-menu__b1"><span class="b-icon b-icon_sbr_gok"></span>Завершенные</span>
            </a>
        </li>
        <? if($sbr->isEmp() && $sbr->draftExists()) { ?>
        <li class="b-menu__item b-menu__item_last <?= $site == 'drafts' ? 'b-menu__item_active' : ''?>">
            <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=drafts" class="b-menu__link">
                <span class="b-menu__b1"><span class="b-icon b-icon_margtop_2 b-icon_sbr_edit"></span>Черновики</span>
            </a>
        </li>
        <? }//if?>
        <? if($count_old_sbr > 0) { ?>
        <li class="b-menu__item b-menu__item_last <?= $site == 'archive' ? 'b-menu__item_active' : ''?>">
            <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=archive" class="b-menu__link">
                <span class="b-menu__b1">Архив</span>
            </a>
        </li>
        <? }//if?>
    </ul>
</div>