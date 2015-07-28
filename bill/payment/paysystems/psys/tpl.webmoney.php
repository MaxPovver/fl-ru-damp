<h2 class="b-layout__title b-layout__title_padbot_30">ќплата с помощью WebMoney</h2>
<table class="b-layout__table b-layout__table_width_full">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padright_20">
                <div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon_padleft_35">
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>ќбратите внимание</div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11">Ч ќплата услуг производитс€ в течение 2-3 минут.</div> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11">Ч ѕосле нажати€ на кнопку ќплатить вы будете перенаправлены в платежную систему WebMoney.</div>
                </div>
                <form id="<?= $type_payment ?>" name="<?= $type_payment ?>" method="post" action="<?= is_release() ? "https://paymaster.ru/Payment/Init" : "/bill/test/wmpay.php" ?>">
                    <input type="hidden" name="LMI_MERCHANT_ID" value="<?= $bill->pm->merchants[pmpay::MERCHANT_BILL] ?>" />
                    <input type="hidden" name="LMI_PAYMENT_AMOUNT" id="ammount" value="<?= $payment_sum ?>" />
                    <input type="hidden" name="LMI_CURRENCY" value="RUB" />
                    <input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="<?= base64_encode(iconv('CP1251', 'UTF-8', "ќплата за услуги сайта www.free-lance.ru, в том числе Ќƒ— - 18%. —чет #" . $bill->acc['id'] . ", логин " . $bill->user['login'])) ?>" />
                    <input type="hidden" name="LMI_PAYMENT_NO" value="<?= $bill->pm->genPaymentNo() ?>" />
                    <input type="hidden" name="PAYMENT_BILL_NO" value="<?= $bill->acc['id'] ?>" />
                    <input type="hidden" name="LMI_SIM_MODE" value="0" />
                </form>
                <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>                    
            </td>
            <td class="b-layout__td b-layout__td_padleft_30 b-layout__td_width_270">
            </td>
        </tr>
    </tbody>
</table>