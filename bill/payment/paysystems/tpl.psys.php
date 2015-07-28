<div class="b-buttons b-buttons_padbot_40 b-buttons_padbot_20_ipad <?= $bill->type_menu_block != 'psys' ? 'b-layout__txt_hide' : ''?> payment-system" id="psys_systems">
    <a href="/bill/payment/?type=webmoney" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'webmoney' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img title="WebMoney" alt="WebMoney" src="/images/bill-wm.png" class="b-button__pic">
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=yandex" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'yandex' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="Yandex деньги" src="/images/bill-yad.png" class="b-button__pic">
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=qiwipurse" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'qiwipurse' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="QIWI кошелек" src="/images/bill-qp.png" class="b-button__pic">
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=webpay" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'webpay' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="Веб-кошелек" src="/images/veb-koshel.png" class="b-button__pic b-button__pic_marg_15_10">
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=okpay" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 <?= ( $bill->type_payment == 'okpay' ? 'b-button_active b-button_disabled' : "" )?>">        
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="OKPAY" src="/images/okpay.png" class="b-button__pic b-button__pic_marg_15_40">
            </span>
        </span>
    </a>
</div>