{{include "header.tpl"}}
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
$xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
    billing.init();

    var etr = 1;//<?= EXCH_TR; ?>;
    function isNumeric(str) {
        var numericExpression = /^ *(?:\d[\d ]*|\d*( \d+)*[.,]\d*) *$/; ///^[0-9]+([\,|\.][0-9]+)?$/;
        if (str.match(numericExpression)) {
            return true;
        } else {
            return false;
        }
    }

    function infoSum(obj, is_fm) {
        if (typeof is_fm == 'undefined')
            is_fm = false;
        obj.value = obj.value.replace(/\,/, '.');
        obj.value = obj.value.replace(/\s/gi, '');

        var val = obj.value;
        billing.clearEvent(obj);
        if (is_fm) {
            //            val = fm2rur(Number(val));
        }
        if (val == 0) {
            billing.tipView(obj, 'Пожалуйста, введите числовое значение');
            $$('#' + obj.id + '_tip').setStyle("left", "405px");
            $$('#Submit').set('disabled', 1);
        } else if (val < <?= is_release() ? 150 : 20 ?>) {
            billing.tipView(obj, 'Сумма платежа не должна быть меньше 150 рублей');
            $$('#' + obj.id + '_tip').setStyle("left", "405px");
            $$('#Submit').set('disabled', 1);
        } else if (billing.isNumeric(String(val))) {

            $$('#Submit').set('disabled', 0);
            var nds = Math.round(Number(val) * 1800 / 118) / 100;
            var itogo = Math.round((val - nds) * 100) / 100;
            var fm = Math.round(val / etr * 100) / 100;
            if (!is_fm)
                $('sum_fm').value = fm;
            $$('#itogo').set('text', itogo);
            $$('#nds').set('text', nds);
            if (is_fm)
                $$('#amm').set('html', val);
            $$('#fm').set('text', fm);
            $$('#sum').set('text', val);
        } else {
            billing.tipView(obj, 'Пожалуйста, введите числовое значение');
            $$('#' + obj.id + '_tip').setStyle("left", "405px");
            $$('#Submit').set('disabled', 1);
        }
    }

    var rur2fm = function(rur) {
        var fm = rur / etr;
        return fm % 2 ? fm.toFixed(2) : fm;
    }

    var fm2rur = function(fm) {
        var rur = fm * etr;
        return rur % 2 ? rur.toFixed(2) : rur;
    }
    
    function checkFields() {
        $$('input[type=submit]').set('disabled', true);
        xajax_PreparePaymentOD(<?=$$order_id?>, $('sum_fm').get('value'));
        return false;
    }
    
    function checkFieldsCallback(res, msg) {
        if (msg) {
            $$('input[type=submit]').set('disabled', false);
            alert(msg);
            return;
        }
        document.location.href = res;
        return false;
    }
</script>
<div class="body c">
				<div class="main c">
        <h1 class="b-page__title">Мой счет</h1>
        <div class="rcol-big">
            {{include "bill/bill_menu.tpl"}}
            <div class="tabs-in bill-t-in c">
                <form action="" accept-charset="UTF-8" method="post" name="form1" id="form1" onSubmit="return checkFields();">
                    <h3>Пластиковые карты</h3>
                    <div class="form bill-form2">

                        <b class="b1"></b>
                        <b class="b2"></b>
                        <div class="form-in">
                            <div class="form-block first last">
                                <div class="form-el" id="sum_fm_parent">
                                    <label class="form-label" for="">Сумма пополнения:</label>
                                    <span class="form-input form-input2">
                                        <input type="text" value="" maxlength="12" id="sum_fm" class="i-bold" style="text-align:right" onkeyup="infoSum(this, true);" onchange="infoSum(this, true);" /> руб
                                    </span>
                                </div>
                            </div>
                        </div>
                        <b class="b2"></b>
                        <b class="b1"></b>
                    </div>
                    <div class="bill-pay-tbl">
                        <b class="b1"></b>
                        <b class="b2"></b>
                        <div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Наименование услуги</th>
                                        <th>Количество</th>
                                        <th>Сумма, руб.</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Итого:</th>
                                        <td id="itogo"><?= ($$sum - round($$sum * 1800 / 118) / 100) ?></td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">НДС:</th>
                                        <td id="nds"><?= (round($$sum * 1800 / 118) / 100) ?></td>
                                    </tr>
                                    <tr class="bpt-sum">
                                        <th colspan="2">Всего к оплате:</th>
                                        <td id="amm"><?= $$sum ?></td>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <tr>
                                        <td>Оплата услуг сайта www.Free-lance.ru</td>
                                        <td id="fm"><?= (!$$norisk_id ? round($$sum / EXCH_TR * 100) / 100 : $$sum) ?></td>
                                        <td id="sum"><?= $$sum ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <b class="b2"></b>
                        <b class="b1"></b>
                    </div>
                    <div class="bill-left-col2 bill-info bill-rform">
                        <p><strong>Период зачисления средств - от мгновенного до 7 дней.</strong></p>
                    </div>
                     <input type="submit" name="Submit" id="Submit" value="Добавить" disabled class="i-btn" style="float:right" />
                    
                </form>
            </div>
        </div>
				</div>

</div>
<?php
$need_paysum = (float) $_COOKIE['need_paysum'];
if ($need_paysum > 0) {
    ?>
    <script type="text/javascript">
        $('sum_fm').set('value', '<?= $need_paysum ?>');
        infoSum($('sum_fm'), true);
    </script>
    <?
}
unset($_COOKIE['need_paysum']);
?>
{{include "footer.tpl"}}		
