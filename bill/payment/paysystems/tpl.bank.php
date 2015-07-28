<div class="b-buttons b-buttons_padbot_40 b-buttons_padbot_20_ipad <?= $bill->type_menu_block != 'bank' ? 'b-layout__txt_hide' : ''?> payment-system" id="bank_systems">
    <a href="/bill/payment/?type=alphabank" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'alphabank' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="Альфа-Банк" src="/images/bill-alfa.png" class="b-button__pic">
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=bank" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'bank' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <div class="b-button__txt b-button__txt_padlr_25 b-button__txt_color_0f71c8 b-button__txt_padtop_17 b-button__txt_padtop_5_ipad">Счет для юридических лиц и ИП</div>
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=sber" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'sber' ? 'b-button_active b-button_disabled' : "" )?>">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <div class="b-button__txt b-button__txt_padlr_25 b-button__txt_color_0f71c8 b-button__txt_padtop_17 b-button__txt_padtop_5_ipad">Квитанция для физических лиц</div>
            </span>
        </span>
    </a>

</div>