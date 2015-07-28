<?php if(!$print) { ?>
<div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_float_right">
    <a href="/bill/payment/print/?type=qiwi" target="_blank" class="b-layout__link">Распечатать страницу</a>
</div>
<?php }//?>
<h2 class="b-layout__title b-layout__title_padbot_30">Оплата через QIWI</h2>
<table class="b-layout__table b-layout__table_width_full">
    <tbody>
        <tr class="b-layout__tr">
            <?php if(!$print) { ?>
            <td class="b-layout__td b-layout__td_padright_20 b-layout__td_center b-layout__td_width_120">
                <img class="b-layout__pic" width="140" height="99" alt="" src="/images/bill-qiwi-big.png">
            </td>
            <?php }//if?>
            <td class="b-layout__td b-layout__td_padleft_30">

                <h3 class="b-layout__h3">Инструкция</h3>
                <div class="b-fon b-fon_padbot_20">
                    <b class="b-fon__b1"></b>
                    <b class="b-fon__b2"></b>
                    <div class="b-fon__body b-fon__body_pad_10_20">
                        <div class="b-layout__txt b-layout__txt_padbot_5">1. Найдите в своем городе любой автомат ОСМП.</div>
                        <div class="b-layout__txt b-layout__txt_padbot_5">2. Зайдите в раздел «Электронная коммерция».</div>
                        <div class="b-layout__txt b-layout__txt_padbot_5">3. Выберите среди предложенных компаний (нажмите на значок Free-lance.ru на дисплее автомата). После этого на экране автомата появится виртуальная клавиатура.</div>
                        <div class="b-layout__txt b-layout__txt_padbot_5">4. Введите ваш логин (который вы используете для входа на сайт) с помощью клавиатуры.</div>
                        <div class="b-layout__txt b-layout__txt_padbot_5">5. Внесите необходимое количество денег в терминал.</div>
                        <div class="b-layout__txt b-layout__txt_padbot_5">6. После этого вам будет предложено подтвердить платеж – проверьте точность введенных вами данных (логин, сумма) и подтвердите платеж.</div>
                        <div class="b-layout__txt b-layout__txt_padbot_5">7. Деньги будут автоматически перечислены на счет и списаны в оплату услуг.</div>
                        <div class="b-layout__txt">8. Автомат выдаст вам чек об оплате. Обязательно возьмите и сохраните чек об оплате до тех пор, пока не проверите наличие денег на вашем личном счете.</div>
                    </div>
                    <b class="b-fon__b2"></b>
                    <b class="b-fon__b1"></b>
                </div> 
                
                <div class="b-layout__txt b-layout__txt_padbot_15">Обращаем ваше внимание на то, что оплата с помощью терминалов QIWI доступна только пользователям, находящимся на территории Российской Федерации.</div>
                    
                <div class="b-fon b-fon_bg_fcc b-fon_padbot_20">
                    <b class="b-fon__b1"></b>
                    <b class="b-fon__b2"></b>
                    <div class="b-fon__body b-fon__body_pad_5"><strong>Внимание:</strong> мы не осуществляем возврат денежных средств при оплате через терминалы.</div>
                    <b class="b-fon__b2"></b>
                    <b class="b-fon__b1"></b>
                </div>
                <h3 class="b-layout__h3">Что делать, если деньги не были перечислены на счет?</h3>
                <div class="b-layout__txt">В случае, если зачисление суммы не произошло, напишите нам в <a class="b-layout__link" href="/about/feedback/">Службу поддержки</a> и пришлите копию чека об оплате (скан).</div>
                <?php if(!$print) { ?>
                <form method="POST" action="<?= ( is_release() ? "/bill/payment/?type={$type_payment}" : "/bill/test/osmp.php" )?>" id="<?= $type_payment ?>" name="<?= $type_payment ?>">
                    <input type="hidden" name="action" value="osmp" />
                    <input type="hidden" name="sum" value="<?= $payment_sum ?>" />
                </form>
                <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>
                <?php }//if?>
            </td>
        </tr>
    </tbody>
</table>
<?php if($print) { ?>
<script type="text/javascript">window.print();</script> 
<?php }//if?>