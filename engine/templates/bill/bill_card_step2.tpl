{{include "header.tpl"}}
<script type="text/javascript">
	billing.init();

	function checkFields() {
		var txt = '<strong>Поле заполнено некорректно</strong>';
		var lastname = $('LastName').value;
		var firstname = $('FirstName').value;
		var email = $('Email').value;
		var address = $('Address').value;
		var phone = $('Phone').value;
		var city = $('City').value;
		var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
        
        var error = false;
		
        if (!lastname.match(/[a-zA_Zа-яА-Я]+/)) {
            billing.tipView($('LastName'), 'Поле заполено некорректно');
            error = true;
        };
        if (!firstname.match(/[a-zA_Zа-яА-Я]+/)) {
            billing.tipView($('FirstName'), 'Поле заполено некорректно');
            error = true;
        };
		if (lastname=='' || firstname=='' || !email.match(emailExp) || address=='' || phone=='' || city==''){
			if (lastname=='') billing.tipView($('LastName'), 'Поле заполено некорректно');
			if (firstname=='') billing.tipView($('FirstName'), 'Поле заполено некорректно');
			if (!email.match(emailExp)) billing.tipView($('Email'), 'Поле заполено некорректно');
			if (address=='') billing.tipView($('Address'), 'Поле заполено некорректно');
			if (phone=='') billing.tipView($('Phone'), 'Поле заполено некорректно');
			if (city=='') billing.tipView($('City'), 'Поле заполено некорректно');
			error = true;
		} 
			
		return !error;
	}

	var etr = 1;//<?=EXCH_TR;?>;
	function isNumeric(str) {
		var numericExpression = /^ *(?:\d[\d ]*|\d*( \d+)*[.,]\d*) *$/; ///^[0-9]+([\,|\.][0-9]+)?$/;
		if(str.match(numericExpression)){
			return true;
		} else {
			return false;
		}
	}

	function infoSum(obj,is_fm) {
if(typeof is_fm == 'undefined') is_fm = false;
        obj.value = obj.value.replace(/\,/, '.');
	obj.value = obj.value.replace(/\s/gi, '');

	var val = obj.value;
		billing.clearEvent(obj);
                if(is_fm) {
                //            val = fm2rur(Number(val));
                }
		if(val == 0) {
			billing.tipView(obj, 'Пожалуйста, введите числовое значение');
			$$('#'+obj.id+'_tip').setStyle("left", "405px");
			$$('#Submit').set('disabled', 1);
		} else if(val < 150) {
		    billing.tipView(obj, 'Сумма платежа не должна быть меньше 150 рублей');
			$$('#'+obj.id+'_tip').setStyle("left", "405px");
			$$('#Submit').set('disabled', 1);  
		} else if(billing.isNumeric(String(val))) {
                         
			$$('#Submit').set('disabled', 0);
                        var nds   = Math.round(Number(val)*1800/118)/100;
                        var itogo = Math.round((val - nds)*100)/100;
                        var fm    =  Math.round(val/etr*100)/100;
			if(!is_fm) $('sum_fm').value = fm;
                        $$('#itogo').set('text', itogo);
			$$('#nds').set('text', nds);
			if(is_fm) $$('#amm').set('value', val);
			$$('#fm').set('text', fm);
			$$('#sum').set('text', val);
		    $$('#ammount').set('value', val);
		} else {
			billing.tipView(obj, 'Пожалуйста, введите числовое значение');
			$$('#'+obj.id+'_tip').setStyle("left", "405px");
			$$('#Submit').set('disabled', 1);
		}
	}
    
    var rur2fm = function(rur){
        var fm = rur / etr;
        return fm % 2 ? fm.toFixed(2) : fm;
    }

    var fm2rur = function(fm){
        var rur = fm * etr;
        return rur % 2 ? rur.toFixed(2) : rur;
    }
</script>
<div class="body c">
				<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
					<div class="rcol-big">
						{{include "bill/bill_menu.tpl"}}
						<div class="tabs-in bill-t-in c">
							<form action="<?=cardpay::URL_ORDER?>" accept-charset="UTF-8" method="post" name="form1" id="form1" onSubmit="return checkFields();">
							<input type="hidden" name="Merchant_ID" value="<?=cardpay::MERCHANT_ID?>" />
							<input type="hidden" name="OrderNumber" value="<?=$$order_id?>" />
							<input type="hidden" name="OrderAmount" id="ammount" value="<?=$$sum?>" />
							<input type="hidden" name="OrderCurrency" value="RUR" />
							<input type="hidden" name="OrderComment" value="Пополнение счета № <?=$$account->id?>" />
							<input type="hidden" name="TestMode" value="<?=cardpay::TESTMODE?>" />
							<input name=ieutf" type="hidden" value="&#9760;" />
							<h3>Пластиковые карты</h3>
							<div class="form bill-form2">

								<b class="b1"></b>
								<b class="b2"></b>
								<div class="form-in">
									<div class="form-block first last">
										<div class="form-el" id="sum_fm_parent">
											<label class="form-label" for="">Сумма пополнения:</label>
											<span class="form-input form-input2">
												<input type="text" value="" maxlength="12" id="sum_fm" class="i-bold" style="text-align:right" onchange="infoSum(this, true);" /> руб
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
<!--												<th>Ед. измер.</th>
												<th>Цена, руб.</th>-->
												<th>Сумма, руб.</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th colspan="2">Итого:</th>
												<td id="itogo"><?=($$sum-round($$sum*1800/118)/100)?></td>
											</tr>
											<tr>
												<th colspan="2">НДС:</th>
												<td id="nds"><?=(round($$sum*1800/118)/100)?></td>
											</tr>
											<tr class="bpt-sum">
												<th colspan="2">Всего к оплате:</th>
												<td><input  id="amm" type="text" onchange="infoSum(this, false);" value="<?=$$sum?>" size="6"></td>
											</tr>
										</tfoot>
										<tbody>
											<tr>
												<td>Оплата услуг сайта www.Free-lance.ru</td>
												<td id="fm"><?=(!$$norisk_id?round($$sum/EXCH_TR*100)/100:$$sum)?></td>
<!--												<td>у.е.</td>
												<td><?=EXCH_TR?></td>-->
												<td id="sum"><?=$$sum?></td>
											</tr>
										</tbody>
									</table>
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
							<div class="bill-left-col2">
								<div class="form bill-form">
									<b class="b1"></b>
									<b class="b2"></b>
									<div class="form-in">
										<div class="form-block first">
											<div class="form-el" id="FirstName_parent">
												<label class="form-label3" for="">Имя</label>
												<span class="form-input">
													<input name="FirstName" type="text" value="<?=$$user->uname?>" onfocus="billing.clearEvent(this);" id="FirstName" class="i-bold" />
												</span>
											</div>
											<div class="form-el" id="LastName_parent">
												<label class="form-label3" for="">Фамилия</label>
												<span class="form-input">
													<input name="LastName" type="text" id="LastName" onfocus="billing.clearEvent(this);" value="<?=$$user->usurname?>" class="i-bold" />
												</span>
											</div>
											<div class="form-el" id="Email_parent">
												<label class="form-label3" for="">Электронная почта</label>
												<span class="form-input">
													<input name="Email" type="text"id="Email" onfocus="billing.clearEvent(this);" value="<?=$$user->email?>" class="i-bold" />
												</span>
											</div>
											<div class="form-el" id="City_parent">
												<label class="form-label3" for="">Город</label>
												<span class="form-input">
													<input name="City" type="text" id="City" onfocus="billing.clearEvent(this);" value="<?=$$city?>" class="i-bold" />
												</span>
											</div>
											<div class="form-el" id="Address_parent">
												<label class="form-label3" for="">Адрес</label>
												<span class="form-input">
													<input name="Address" type="text" value="" id="Address" onfocus="billing.clearEvent(this);" class="i-bold" />
												</span>
											</div>
											<div class="form-el" id="Phone_parent">
												<label class="form-label3" for="">Телефон</label>
												<span class="form-input">
													<input name="Phone" type="text" id="Phone" onfocus="billing.clearEvent(this);" value="<?=$$reqv['mob_phone']?>" class="i-bold" />
												</span>
											</div>
										</div>
										<div class="form-block last">
											<div class="form-btn">
												<input type="submit" name="Submit" id="Submit" value="Добавить" disabled class="i-btn" />
											</div>
										</div>
									</div>
									<b class="b2"></b>
									<b class="b1"></b>
								</div>
							</div>

                            <?php
                            $need_paysum = (float) $_COOKIE['need_paysum'];
                            if($need_paysum>0) {
                                ?>
                                <script type="text/javascript">
                                $('sum_fm').set('value', '<?=$need_paysum?>');
                                infoSum($('sum_fm'), true);
                                </script>
                                <?
                            }
                            unset($_COOKIE['need_paysum']);
                            ?>


							<div class="bill-right-col2 bill-info bill-rform">
								<?/*<div class="warning">
									<b class="b1"></b>

									<b class="b2"></b>
									<div class="warning-in">
										<strong><span class="red">Внимание!</span> В случае, если пополнение счета осуществлялось исключительно с помощью пластиковой карты, то услуга перевода FM для вас будет недоступна.</strong>
									</div>
									<b class="b2"></b>
									<b class="b1"></b>
								</div>*/?>

								<p><strong>Период зачисления средств - от мгновенного до 7 дней.</strong></p>
								<p><a href="http://assist.ru/about/security.htm" target="_blank">Подробнее о безопасности платежей</a></p>
							</div>
						</form>
						</div>
					</div>
				</div>

			</div>
{{include "footer.tpl"}}		
