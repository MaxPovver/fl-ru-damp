<div id="favs_item<?= $key ?>" class="b-menu__list">
    <div id="fav_edit_buttons_<?= $key ?>" class="b-buttons b-buttons_float_right i-shadow">
        <a id="favs_edit_fav<?= $key ?>" style="display:none" href="javascript:void(0)" class="b-button b-button_m_edit"></a>
        <a id="favs_delete_fav<?= $key ?>" style="display:none" href="javascript:void(0)" class="b-button b-button_m_delete"></a>
        <div id="favs_editor<?= $key ?>" class="b-shadow b-shadow_width_230 b-shadow_left_-90 b-shadow_top_20 b-shadow_hide">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                <form action="">
                                    <div class="b-textarea b-textarea_margbot_10">
                                        <textarea id="favtext<?= $key ?>" class="b-textarea__textarea b-textarea__textarea__height_70" name="" cols="" rows=""><?= ($fav['title'] ? stripslashes(reformat2($fav['title'], 18, 1, 1)) : '<без темы>') ?></textarea>
                                    </div>
                                    <a id="fav_editor_submit<?= $key ?>" href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green b-button_block">Изменить название</a>															
                                </form>
                            </div>
            <span id="favs_close_editor<?= $key ?>" class="b-shadow__icon b-shadow__icon_close"></span>
            <span class="b-shadow__icon b-shadow__icon_nosik"></span>								
        </div>
    </div>
    <a id="favs_fav_name<?= $key ?>" class="b-menu__link" href="<?=getFriendlyUrl('commune', $key)?><?= ($om ? '?om='.$om : '') ?>"><?= ($fav['title'] ? stripslashes(reformat2($fav['title'], 18, 1, 1)) : '<без темы>') ?></a>
</div>
<div id="favs_fav_deleted<?= $key ?>" style="display:none" class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_5 b-fon__body_fontsize_11 b-fon__body_bg_ffeeeb">
        Удалена. 
        <a id="favs_recover_fav<?= $key ?>" class="b-fon__link" href="javascript:void(0)">Восстановить</a>
    </div>
</div>