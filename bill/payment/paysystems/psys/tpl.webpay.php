<h2 class="b-layout__title b-layout__title_padbot_30">Оплата через Веб-кошелек</h2>
<table class="b-layout__table b-layout__table_width_full">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padright_20">
                <div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon_padleft_35">
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>Обратите внимание</div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11">— Оплата услуг производится в течение 2-3 минут.</div> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11">— Платежи суммой более 15 000 не производятся.</div> 
                </div> 
                <form action="<?= is_release() ? "http://www.onlinedengi.ru/wmpaycheck.php" : "/bill/test/webpay.php"?>" method="post" id="<?= $type_payment ?>" name="<?= $type_payment ?>">
                    <input type="hidden" name="project" value="3097"  />
                    <input type="hidden" name="mode_type" value="204"  />
                    <input type="hidden" name="nickname" value="<?= $bill->user['login']?>"  />
                    <input type="hidden" name="nick_extra" value="<?= $bill->user['login']?>"  />
                    <input type="hidden" name="amount" value="<?= $payment_sum; ?>" />
                </form>
                <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>                  
            </td>
            <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_width_270">
            </td>
        </tr>
    </tbody></table>