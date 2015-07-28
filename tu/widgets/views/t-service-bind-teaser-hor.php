<div class="b-post b-post_margbot_20">
    <div class="b-pay-tu b-pay-tu_hor">
        <a class="b-pay-tu__close" href="#"></a>
        <table class="b-layout__table b-layout__table_width_full">
            <tbody><tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_valign_mid b-layout__td_width_100 b-layout__td_center"><div class="b-pay-tu__pin"></div></td>
                <td class="b-layout__td b-layout__td_padleft_20">
                    <?php if (isset($subtitle)): ?>
                        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15">
                            <?=  strip_tags($subtitle)?>
                        </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_6db335 b-layout__txt_fontsize_15 b-layout__txt_padbot_10">
                        <?=$title?> за <?=view_cost_format($price, true, false, false)?> на 7 дней до <?=$date?></div>
                    <a class="b-button b-button_flat b-button_flat_green"<?php if (isset($popup_id)): ?>
                   data-popup="<?=$popup_id?>"
               <?php endif; ?>href="<?=$href?>"><?=$btn_text?></a>
                </td>
            </tr></tbody>
        </table>
    </div>
</div>

<?php
if (@$popup) {
    echo $popup;
}
