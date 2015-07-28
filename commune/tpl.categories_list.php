<li class="b-menu__item b-menu__item_padbot_10">
    <div class="b-menu__b1">
        <div id="categories_themes_count_all" class="b-menu__number b-menu__number_fontsize_11"><?= $themes_count . ending($themes_count, ' пост', ' поста', ' постов') ?></div>
    </div>
    <? if ($curr_cat == 0) { ?>
        <a class="b-menu__link b-menu__link_color_000" href="<?= getFriendlyUrl('commune_commune', $comm['id']) . '?om=' . $om ?>">Все вместе</a>
    <? } else { ?>
        <a class="b-menu__link" href="<?= getFriendlyUrl('commune_commune', $comm['id']) . '?om=' . $om ?>">Все разделы</a>
    <? } ?>
</li>
<? if($categories) {
    foreach($categories as $category) { 
        $categoryThemesCount = $communeThemesCounts['categories'][$category['id']];
        if ($for_admin) {
            $categoryThemes = (int)$categoryThemesCount['count'];
        } elseif ($for_commune_admin) {
            $categoryThemes = (int)($categoryThemesCount['count'] - $categoryThemesCount['admin_hidden_count']);
        } else {
            $categoryThemes = (int)($categoryThemesCount['count'] - $categoryThemesCount['hidden_count']);
        }
        ?>
        <li id="category_item<?= $category['id']; ?>" class="b-menu__item b-menu__item_padbot_10">
            <? if($for_admin || $for_commune_admin) { ?>
                <div id="comm_span_cmd_<?= $category['id'] ?>" class="b-buttons b-buttons_float_right b-buttons_bg_fff i-shadow" style="display:none">
                    <a id="category_edit_button<?= $category['id'] ?>" href="javascript:void(0)" class="b-button b-button_m_edit"></a>
                    <a id="category_del_button<?= $category['id'] ?>" href="javascript:void(0)" class="b-button b-button_m_delete"></a>
                    <div id="category_editor<?= $category['id'] ?>" class="b-shadow b-shadow_width_230 b-shadow_left_-90 b-shadow_top_20 b-shadow_hide">
                        <div class="b-shadow__right">
                            <div class="b-shadow__left">
                                <div class="b-shadow__top">
                                    <div class="b-shadow__bottom">
                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                            <form id="comm_form_edit_<?= $category['id'] ?>" action="">
                                                <input type="hidden" name="om" value="<?= $om ?>" />
                                                <input type="hidden" name="commune_id" value="<?= $comm['id'] ?>" />
                                                <input type="hidden" name="category_id" value="<?= $category['id'] ?>" />
                                                <div class="b-textarea b-textarea_margbot_10">
                                                    <textarea rel="<?= commune::MAX_CATEGORY_NAME_SIZE ?>" id="commune_fld_edit_category_name_<?= $category['id'] ?>" name="commune_fld_edit_category_name" class="b-textarea__textarea b-textarea__textarea__height_70" cols="" rows=""><?= hyphen_words($category['name']) ?></textarea>
                                                </div>
                                                <div class="b-check b-check_padbot_10">
                                                    <input id="commune_fld_add_category_only_for_admin" name="commune_fld_edit_category_only_for_admin" class="b-check__input" type="checkbox" value="1" <?= $category["is_only_for_admin"] === 't' ? ' checked' : '' ?> />
                                                    <label for="b-check1" class="b-check__label b-check__label_fontsize_13">Публикации только<br>администрации</label>
                                                </div>
                                                <a id="category_edit_submit<?= $category['id'] ?>" href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green b-button_block">Изменить название</a>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span id="category_edit_close<?= $category['id'] ?>" class="b-shadow__icon b-shadow__icon_close"></span>
                        <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                    </div>
                </div>
            <? } ?>
            <div class="b-menu__b1">
                <div id="categories_themes_count<?= $category['id'] ?>" class="b-menu__number b-menu__number_fontsize_11"><?= $categoryThemes . ending($categoryThemes, ' пост', ' поста', ' постов') ?></div>
            </div>
            <? if ( $category['id'] == $curr_cat && $page == 1 ) { ?>
                <?= hyphen_words($category['name']) ?>
                <a id="category_name<?= $category['id'] ?>" style="color: #000;" class="b-menu__link" href="<? getFriendlyUrl('commune_commune', $comm['id']) . '?om=' . $om . '&cat=' . $category['id'] ?>"><?= hyphen_words($category['name']) ?></a>
            <? } else { ?>
                <a id="category_name<?= $category['id'] ?>" class="b-menu__link" href="<?= getFriendlyUrl('commune_commune', $comm['id']) . '?om=' . $om . '&cat=' . $category['id'] ?>"><?= hyphen_words($category['name']) ?></a>
            <? } ?>
        </li>
        <li id="category_deleted<?= $category['id'] ?>" class="b-menu__item b-menu__item_padbot_10 i-button" style="display:none">
            <div class="b-fon b-fon_width_full">
                <div class="b-fon__body b-fon__body_pad_5 b-fon__body_fontsize_11 b-fon__body_bg_ffeeeb">Удален. <a id="category_recover<?= $category['id'] ?>" class="b-fon__link" href="javascript:void(0)">Восстановить</a></div>
            </div>
        </li>
    <? }
} ?>