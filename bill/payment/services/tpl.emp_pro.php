<?php
$type = $pro_type[$service['op_code']];
?>
<div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_relative b-layout_margbot_10">
    <span class="b-page__desktop b-page__ipad"><span class="b-icon b-icon__spro b-icon__spro_e b-icon_absolute b-icon_left_10" title="PRO"></span></span>
    <span class="b-layout__txt b-layout__txt_float_right b-layout__txt_fontsize_15 b-layout__txt_color_fd6c30 b-layout__txt_padtop_2 b-layout__txt_padleft_10"><?= to_money($service['ammount'])?> руб.</span>
    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padleft_70 b-layout__txt_padleft_null_iphone b-layout__txt_padtop_2">ѕрофессиональный аккаунт на <?= $type['is_test'] ? '1 неделю' : ($type['month'] . ' ' . ending($type['month'], 'мес€ц', 'мес€ца', 'мес€цев'))?></div>
</div>