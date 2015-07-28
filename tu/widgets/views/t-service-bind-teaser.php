<div class="<?=$main_div_class?>">
    <div class="b-pay-tu">
        <a class="b-pay-tu__close" href="javascript:void(0);"></a>
        <div class="b-pay-tu__inner">
            <div class="b-pay-tu__pin"></div>
            <?php if (isset($subtitle)): ?>
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">
                    <?=$subtitle?>
                </div>
            <?php endif; ?>
            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_6db335"><?=$title?></div>
            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_20 b-layout__txt_color_6db335">
                за <?=view_cost_format($price, true, false, false)?>
            </div>
            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_color_6db335">
                на 7 дней до <?=$date?>
            </div>
            <a class="b-button b-button_flat b-button_flat_green"<?php if (isset($popup_id)): ?>
                   data-popup="<?=$popup_id?>"
               <?php endif; ?>href="<?=$href?>"><?=$btn_text?></a>
        </div>
    </div>
</div>
<?php
if (@$popup) {
    echo $popup;
}
