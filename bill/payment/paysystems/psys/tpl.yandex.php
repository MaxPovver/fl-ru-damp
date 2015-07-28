<h2 class="b-layout__title b-layout__title_padbot_30">ќплата с помощью яндекс.ƒеньги</h2>
<table class="b-layout__table b-layout__table_width_full">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padright_20">
                <div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon_padleft_35">
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>ќбратите внимание</div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11">Ч ќплата услуг производитс€ в течение 2-3 минут.</div> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11">Ч ѕосле нажати€ на кнопку ќплатить вы будете перенаправлены на сайт яндекс.ƒеньги.</div> 
                </div>
                <form id="<?= $type_payment ?>" name="<?= $type_payment ?>"  method="post" action="<?= is_release() ? "https://money.yandex.ru/eshop.xml" : "/bill/test/ydpay.php" ?>">
                    <input class="wide" name="scid" value="2200" type="hidden" />
                    <input type="hidden" name="ShopID" value="4551" />
                    <input type="hidden" name="Sum" value="<?= $payment_sum; ?>" />
                    <input type="hidden" name="CustomerNumber" value="<?= $bill->acc['id'] ?>" />
                </form>
                <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>                 
            </td>
            <td class="b-layout__td b-layout__td_padleft_30 b-layout__td_width_270">
            </td>
        </tr>
    </tbody>
</table>