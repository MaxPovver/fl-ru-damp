<div class="b-buttons b-buttons_padbot_40 b-buttons_padbot_20_ipad <?= $bill->type_menu_block != 'mobilesys' ? 'b-layout__txt_hide' : ''?> payment-system" id="mobilesys_systems">
    <a href="/bill/payment/?type=megafon_mobile" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 b-button_margtop_10 <?= ( $bill->type_payment == 'megafon_mobile' ? 'b-button_active b-button_disabled' : "" )?>">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img title="Мегафон" alt="Мегафон" src="/images/tel/megafon.png" class="b-button__pic b-button__pic_marg_8_10">
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=beeline_mobile" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 b-button_margtop_10 <?= ( $bill->type_payment == 'beeline_mobile' ? 'b-button_active b-button_disabled' : "" )?>">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="Билайн" src="/images/tel/beeline.png" class="b-button__pic b-button__pic_marg_8_10">
            </span>
        </span>
    </a>
    <a href="/bill/payment/?type=mts_mobile" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 b-button_margtop_10 <?= ( $bill->type_payment == 'mts_mobile' ? 'b-button_active b-button_disabled' : "" )?>">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="МТС" src="/images/tel/mts.png" class="b-button__pic b-button__pic_marg_8_10">
            </span>
        </span>
    </a>
    <!--
    <a href="/bill/payment/?type=matrix_mobile" class="b-button b-button_bill b-button_bill_mid b-button_margright_5 b-button_margtop_10 <?= ( $bill->type_payment == 'matrix_mobile' ? 'b-button_active b-button_disabled' : "" )?>">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <img alt="Matrix" src="/images/tel/matrix.png" class="b-button__pic b-button__pic_marg_8_10">
            </span>
        </span>
    </a>
    -->
</div>
