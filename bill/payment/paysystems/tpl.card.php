<div class="b-buttons b-buttons_padbot_40 b-buttons_padbot_20_ipad <?= $bill->type_menu_block != 'card' ? 'b-layout__txt_hide' : ''?> payment-system" id="card_systems">
    <a href="/bill/payment/?type=card" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'card' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="MasterCard" src="/images/bill-mc.png" class="b-button__pic">&#160;<img alt="Visa" src="/images/bill-visa.png" class="b-button__pic">
            </span>
        </span>
    </a>
</div>