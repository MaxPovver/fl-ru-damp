<div class="b-buttons b-buttons_padbot_40 b-buttons_padbot_20_ipad <?= $bill->type_menu_block != 'terminal' ? 'b-layout__txt_hide' : ''?> payment-system" id="terminal_systems">
    <a href="/bill/payment/?type=qiwi" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'qiwi' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="QIWI" src="/images/bill-qiwi.png" class="b-button__pic">
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=svyasnoy" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'svyasnoy' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="Связной" src="/images/cvyaznoy.png" class="b-button__pic">
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=euroset" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'euroset' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="Евросеть" src="/images/euroset.png" class="b-button__pic">
            </span>
        </span>
    </a>
</div>