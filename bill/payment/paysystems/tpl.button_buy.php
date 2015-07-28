<? if( $bill->payed_sum['acc'] > 0 ) { ?>
    <div class="b-layout__txt b-layout__txt_padtop_15">
        C личного счета будет списано <span class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_color_fd6c30 b-layout__txt_bold"><?= to_money($bill->payed_sum['acc'], 2) ?> руб.</span>
    </div>
<?php }//if?>
<? if ( $bill->payed_sum['ref'] > 0 ) {?>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padtop_10">
    Оставшаяся после оплаты сумма <span class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_color_fd6c30 b-layout__txt_bold"><span class="payed_account_sum"><?=to_money($bill->payed_sum['ref'], 2) ?></span> руб.</span>  будет возвращена вам на счет.
</div>
<? }//if?>
<div class="b-buttons b-buttons_padtop_20">
    <a href="javascript:void(0)" class="b-button <?= $disabled ? "b-button_rectangle_color_disable" : ""?> b-button_rectangle_color_green prepare-payment" data-payment="<?= $type_payment ?>" data-checked="<?= $checked?>">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__txt">Оплатить <span class="b-button__colored b-button__colored_fd6c30"><?= to_money( $bill->payed_sum['pay']) ?> руб.</span></span>
            </span>
        </span>
    </a>
</div>