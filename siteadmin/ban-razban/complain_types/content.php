<?
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

?>

<h3>Управление списком жалоб</h3>
			
<div class="b-layout b-layout_padtop_10 b-layout_padbot_10">
    <p class="b-layout b-layout__txt_fontsize_15">
        <? if ($moder) { ?>
        <b>Жалобы модератору</b>
        <a href="?mode=complain_types&moder=0" class="b-layout b-layout__link b-layout__link_bordbot_dot_000 b-layout_marglr_30">Жалобы работодателю</a>
        <? } else { ?>
        <a href="?mode=complain_types&moder=1" class="b-layout b-layout__link b-layout__link_bordbot_dot_000 b-layout_marglr_30">Жалобы модератору</a>
        <b>Жалобы работодателю</b>
        <? } ?>
    </p>
</div>
<? $complainsExists = $complainTypes && is_array($complainTypes) && count($complainTypes); ?>
<form method="post" id="complain_types_form">
    <div id="complain_types">
        <input type="hidden" name="action" value="save" />
        <input type="hidden" name="u_token_key" value="<?= $_SESSION['rand'] ?>" />
        <input type="hidden" name="moder" value="<?= $moder ?>" />
        <? if ($complainsExists) { ?>
            <? foreach($complainTypes as $cType) { ?>

                <div class="b-layout b-layout_pad_20 b-layout_bord_e6 b-layout_margbot_10 b-layout__table_margtop_10 complain-type">
                    <input type="hidden" name="id[]" value="<?= $cType['id'] ?>" />
                    <input type="hidden" name="del[]" value="0" />
                    <a href="javascript:void(0)" class="b-button b-button_admin_del b-button_float_right b-button_margtop_5 del_complain_type"></a>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_245">
                            <input class="b-combo__input-text" name="name[]" type="text" size="80" value="<?= $cType['name'] ?>" />
                        </div>
                    </div>
                    &#160;&#160;&#160;&#160;&#160;<span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_4">Сделать &nbsp;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_45">
                            <input class="b-combo__input-text" name="pos[]" type="text" size="80" value="<?= $cType['pos'] ?>" maxlength="2" />
                        </div>
                    </div>&#160;
                    <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_4">в списке</span>
                    &#160;&#160;&#160;&#160;&#160;
                    <div class="b-check b-check_inline-block b-check_padtop_6">
                        <input class="b-check__input" name="textarea_checkbox[]" type="checkbox" value="1" <?= $cType['textarea'] ? 'checked' : '' ?> />
                        <input name="textarea[]" type="hidden" value="<?= $cType['textarea'] ?>" />
                        <label for="b-check1" class="b-check__label b-check__label_fontsize_13">Добавить описание</label>
                    </div>
                    <div class="b-check b-check_inline-block b-check_padtop_6">
                        <input class="b-check__input" name="required_checkbox[]" type="checkbox" value="1" <?= $cType['required'] ? 'checked' : '' ?> />
                        <input name="required[]" type="hidden" value="<?= $cType['required'] ?>" />
                        <label for="b-check1" class="b-check__label b-check__label_fontsize_13">Описание обязательно</label>
                    </div>
                </div>

            <? } ?>
        <? } ?>
    </div>
</form>
<div id="no_complains" <?= $complainsExists ? 'style="display:none;"' : '' ?>>
    <p style="text-align: center; font-size: 20px; font-weight: bold;">Пусто</p>
</div>

<div id="complain_type_template" style="display:none;">
    <div class="b-layout b-layout_pad_20 b-layout_bord_e6 b-layout_margbot_10 b-layout__table_margtop_10 complain-type">
        <input type="hidden" name="id[]" value="" />
        <input type="hidden" name="del[]" value="0" />
        <a href="javascript:void(0)" class="b-button b-button_admin_del b-button_float_right b-button_margtop_5 del_complain_type"></a>
        <div class="b-combo b-combo_inline-block">
            <div class="b-combo__input b-combo__input_width_245">
                <input class="b-combo__input-text" name="name[]" type="text" size="80" value="" />
            </div>
        </div>
        &#160;&#160;&#160;&#160;&#160;<span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_4">Сделать &nbsp;</span>
        <div class="b-combo b-combo_inline-block">
            <div class="b-combo__input b-combo__input_width_45">
                <input class="b-combo__input-text" name="pos[]" type="text" size="80" value="" />
            </div>
        </div>&#160;
        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_4">в списке</span>
        &#160;&#160;&#160;&#160;&#160;
        <div class="b-check b-check_inline-block b-check_padtop_6">
            <input class="b-check__input" name="textarea_checkbox[]" type="checkbox" value="1" />
            <input name="textarea[]" type="hidden" value="" />
            <label for="b-check1" class="b-check__label b-check__label_fontsize_13">Добавить описание</label>
        </div>
        <div class="b-check b-check_inline-block b-check_padtop_6">
            <input class="b-check__input" name="required_checkbox[]" type="checkbox" value="1" />
            <input name="required[]" type="hidden" value="" />
            <label for="b-check1" class="b-check__label b-check__label_fontsize_13">Описание обязательно</label>
        </div>
    </div>
</div>

<p class="b-layout b-layout_padtop_15">
    <a id="add_complain_type" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_13" href="javascript:void(0)">Добавить жалобу</a>&nbsp;&nbsp;&nbsp;&nbsp;
    <a id="save_complain_types" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_13" href="javascript:void(0)">Сохранить</a>
</p>


