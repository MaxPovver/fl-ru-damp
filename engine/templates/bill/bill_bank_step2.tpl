{{include "header.tpl"}}
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.common.php");
$xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
    function CityUpd(v){
		ct = document.getElementById("main_form").city_id;
		ct.disabled = true;
		ct.options[0].innerHTML = "Подождите...";
		ct.value = 0;
		
		var selIdx  = $('country_id').selectedIndex;
        var selText = $('country_id').options[selIdx].text;
        $('reqv_country').set('value', selText);
        $('reqv_city').set('value', '');
        
        if (v==1) {
            $('reqv_inn').addClass('form-imp');
        }
        else {
            $('reqv_inn').removeClass('form-imp');
        }
		
		xajax_GetCitysByCid(v, {name:'city_id',id:'city_id',onchange:"CitySet()"});
	}
	
	function CitySet() {
	    var selIdx  = $('city_id').selectedIndex;
        var selText = $('city_id').options[selIdx].text;
        $('reqv_city').set('value', selText);
	}

    window.onload = function() {
        <?php if($$error) { 
            $sFirstError = '';
            foreach($$error as $id=>$txt) {
                $sFirstError = !$sFirstError ? $id : $sFirstError;
    		?>
    		tipView2({id:'<?=$id?>'}, 1, '<strong><?=$txt?></strong>', 1);
    		<?php
            }
            ?>
            window.location = '#<?=$sFirstError?>_tip';
            <?php 
            } 
            ?>
    }
	function chButton() {
		if(isNumeric($$('#ammount').value) == true) $$('#pay').set('disabled', 0);
		else $$('#pay').set('disabled', 1);
		
		if($$('#ammount').value<=0) $$('#pay').set('disabled', 1);
	}
	
	function tipView2(tip, act, text, error) {
		$$('#'+tip.id).removeClass('invalid'); //.addClass('i-bold');
		$$('#'+tip.id+'_txt').set('html',text);
		if(act==1) $$('#'+tip.id+'_tip').setStyle('display', 'block');//show();
		else $$('#'+tip.id+'_tip').setStyle('display', 'none');
		
		if(error == 1) $$('#'+tip.id).addClass('invalid');
	}
	
	function tipView3(id, act, text, error) {
		$$('#'+id).removeClass('invalid'); //.addClass('i-bold');
		$$('#'+id+'_txt').set('html',text);
		if(act==1) $$('#'+id+'_tip').setStyle('display', 'block');//show();
		else $$('#'+id+'_tip').setStyle('display', 'none');
		
		if(error == 1) $$('#'+id).addClass('invalid');
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

function tipView(id, act, txt) {
	$$('#'+id+'_tip').destroy(); //remove();
	
	var tpl = '<div class="tip-in"><div class="tip-txt"><div class="tip-txt-in"><span class="middled" id="'+id+'_txt"><strong>'+txt+'</strong></span></div></div></div>';
	
	var idDiv = id+'_tip';
	var div = new Element('div', {'id':idDiv, 'class':'tip', 'styles':{'left':'405px'}});
	div.set('html', tpl);
	
	if(act == 1) {
		$$('#'+id+'_parent').adopt(div);
	} else {
		$$('#'+id+'_tip').destroy();
	}
}

function checkSum() {
   var val = $('ammount2').value;
   
   if(!isNumeric(val)) {
       return false;
   } else if(val <= 0) {
       return false;
   }
   
   return true;
}

function infoSum(obj,is_fm) {
if(typeof is_fm == 'undefined') is_fm = false;
	obj.value = obj.value.replace(/\,/, '.');
	obj.value = obj.value.replace(/\s/gi, '');
	
	var val = obj.value;
	if(val == 0) {
		tipView('sum_a', 1, 'Пожалуйста, введите числовое значение, больше нуля');
		$$('#sum_a').addClass('invalid');
		$$('.www').set('disabled', 1);
		$$('#vps').set('disabled', 1);
		$$('#ammount').set('value', val);
		$$('#ammount2').set('value', val);
	} else if(isNumeric(String(val))) {
                if(is_fm) {
                    //val = fm2rur(val);
                }
                if(val % 2) Number(val).toFixed(2);
		//val = value.replace(/ /, '');
		tipView('sum_a', 0, 'Пожалуйста, введите числовое значение');
		$$('#sum_a').removeClass('invalid');
		$$('.www').set('disabled', 0);
		$$('#vps').set('disabled', 0);
		var nds   = Math.round(Number(val)*1800/118)/100;
		var itogo = Math.round((val - nds)*100)/100;
		var fm    =  Math.round(val/etr*100)/100;

                if(!is_fm) $('sum_a').value = fm;
		$$('#itogo').set('text',itogo);
		$$('#nds').set('text',nds);
		if(is_fm) $$('#amm').set('value',val);
		$$('#fm').set('text',fm);
		$$('#sum').set('text',val);
		$$('#ammount').set('value', val);
		$$('#ammount2').set('value', val);
	} else {
		tipView('sum_a', 1, 'Пожалуйста, введите числовое значение');
		$$('#sum_a').addClass('invalid');
		$$('.www').set('disabled', 1);
		$$('#vps').set('disabled', 1);
		$$('#ammount').set('value', val);
		$$('#ammount2').set('value', val);
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
    var IS_EMP = <?= ( is_emp()? 'true' : 'false' ) ;?>;
    function getMoney(v) {
        el = $('amm');
        
        if (!el) {
            return;
        }
        
        el.set('value', v);
        infoSum(el, false);
    }
 
</script>
<div class="body c">
				<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
					<div class="rcol-big">
						{{include "bill/bill_menu.tpl"}}
						<div class="tabs-in bill-t-in c">
							<h3>Банковский перевод <span class="bill-num"><span>Б-<?=$$account->id?>-<?=($$billNum+1)?></span></span></h3>
                            <? if (
                                    (time() > strtotime('2012-12-27 00:00:00') && strtotime('2012-12-27 14:00:00') > time()) || // это дата для тестирования
                                    (time() > strtotime('2012-12-29 00:00:00') && strtotime('2013-01-08 23:59:59') > time()) // это дата боевая
                            ) { ?>
                            <div class="b-fon b-fon_padbot_30">
                                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                                    <span class="b-fon__attent_red"></span>Внимание! В связи с новогодними выходными пополнение по безналичному расчету не производится. Данные операции с денежными средствами возобновятся 9 января 2013 г.
                                </div>
                            </div>
                            <? } ?>
							<div class="form bill-form2">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="form-in">
									<div class="form-block first last">
										<div class="form-el" id="sum_a_parent">
											<label class="form-label" for="">Сумма пополнения:</label>
											<span class="form-input form-input2">
                                                                                            <input value="<?=round($$sum)?>" style="text-align: right" class="i-bold" id="sum_a" maxlength="12" type="text" onchange="infoSum(this, true);"> Руб
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
												<td id="itogo"><?=($$sum-round($$sum*18/118, 2))?></td>
											</tr>
											<tr>
												<th colspan="2">НДС:</th>
												<td id="nds"><?=round($$sum*18/118, 2)?></td>
											</tr>
											<tr class="bpt-sum">
												<th colspan="2">Всего к оплате:</th>
												<td><input  id="amm" type="text" onchange="infoSum(this, false);" value="<?=$$sum?>" size="6"></td>
											</tr>
										</tfoot>
										<tbody>
											<tr>
												<td>Оплата услуг сайта <a href="http://www.free-lance.ru">www.free-lance.ru</a></td>
												<td id="fm"><?=(!$$norisk_id?round($$sum):$$sum)?></td>
<!--												<td><?=(($$norisk_id)?"руб.":"у.е.")?></td>
												<td><?=(($$norisk_id)?1:EXCH_TR)?></td>-->
												<td id="sum"><?=$$sum?></td>
											</tr>
										</tbody>
									</table>
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
							<div class="form bill-form bill-bank">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="form-in">
									<? if ($$reqvByUid): ?>
									<form action="/bill/transfer/" method="post" name="payment" id="payment" target="_blank">
									<input type="hidden" name="sum" id="ammount2" value="<?=$$sum?>">
									<input type="hidden" name="noriskId" value="<?=$$norisk_id?>">
									<? foreach ($$reqvByUid as $ikey => $value): $$reqvs->BindRequest($value); ?>
									<? if ($$edit_mode && $$reqvs->id == $$eid) { $reqvkey = $ikey; }?>
									<input type="hidden" name="id[]" value="<?=$$reqvs->id?>">
									<a name="reqv<?=$$reqvs->id?>"></a>
									<div class="form-block c first">
										<h4><?=$$reqvs->org_name?> (<?=$$reqvs->full_name?>)</h4>
										<div class="bill-bank-col">
											<div class="form-el">
												<label class="form-label3" for="">Страна:</label>
												<span class="form-input-value">
													<?=$$reqvs->country?>
												</span>
											</div>
											<div class="form-el">
												<label class="form-label3" for="">Город:</label>
												<span class="form-input-value">
													<?=$$reqvs->city?>
												</span>
											</div>
											<div class="form-el">
												<label class="form-label3" for="">Почтовый адрес:</label>
												<span class="form-input-value">
													<?=$$reqvs->address?>
												</span>
											</div>
											<div class="form-el">
												<label class="form-label3" for="">Юридический адрес:</label>
												<span class="form-input-value">
													 <?=$$reqvs->address_jry?>
												</span>
											</div>
											<div class="form-el">
												<label class="form-label3" for="">ИНН:</label>
												<span class="form-input-value">
													<?=$$reqvs->inn?>
												</span>
											</div>
											<div class="form-el">
												<label class="form-label3" for="">КПП:</label>
												<span class="form-input-value">
													<?=$$reqvs->kpp?>
												</span>
											</div>
										</div>
										<div class="bill-bank-col">
											<div class="form-el">
												<label class="form-label3" for="">Телефон:</label>
												<span class="form-input-value">
													<?=$$reqvs->phone?>
												</span>
											</div>
											<div class="form-el">
												<label class="form-label3" for="">Факс:</label>
												<span class="form-input-value">
													<?=$$reqvs->fax?>
												</span>
											</div>
											<div class="form-el">
												<label class="form-label3" for="">E-mail:</label>
												<span class="form-input-value">
													<?=$$reqvs->email?>
												</span>
											</div>
										</div>
									</div>
									<div class="form-block last">
										<div class="form-btn">
											<input type="submit" value="Выписать счет" onClick="return checkSum();" name="send_id[<?=$$reqvs->id?>]" class="www i-btn" id="vps"/>&nbsp;&nbsp;
											<input type="button" value="Изменить" onClick="document.location.href = '/<?=$$name_page?>/bank/edit/<?=$$reqvs->id?>/<?=$$sum?>#edit';" class="i-btn" />&nbsp;&nbsp;
											<input type="button" value="Удалить" onClick="if(confirm('Выполнить действие?')) document.location.href = '/<?=$$name_page?>/bank/delete/<?=$$reqvs->id?>/';"class="i-btn" />&nbsp;&nbsp;
										</div>
									</div>
									<? endforeach; ?>  
									</form>
									<? endif; ?>
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
							<form method="POST" id="main_form">
								<input type="hidden" name="sum" id="ammount" value="<?=stripslashes(htmlspecialchars($$sum))?>">
								<input type="hidden" name="noriskId" value="<?=stripslashes(htmlspecialchars($$norisk_id))?>">
								<input type="hidden" name="country" id="reqv_country" value="<?=stripslashes(($$reqv->country))?>">
								<input type="hidden" name="city" id="reqv_city" value="<?=stripslashes(($$reqv->city))?>">
							<div class="bill-left-col2">
								<a name="edit"></a> 
								<div class="form bill-form">
									<b class="b1"></b>
									<b class="b2"></b>
									<div class="form-in">
            							<div class="form-block first">
											<div class="form-el">
                                                <a name="org_name_tip"></a>
												<label class="form-label3" for="">Название организации</label>
												<span class="form-input form-imp">
													<input type="text" value="<?=stripslashes(($$reqv->org_name))?>"  name="org_name"  id="org_name" onfocus="tipView2(this, 1, '<em>Без кавычек и обозначения формы собственности</em>')" onBlur="tipView2(this);"/>
												</span>
												<div class="tip" id="org_name_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="org_name_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="phone_tip"></a>
												<label class="form-label3" for="">Телефон</label>
												<span class="form-input form-imp">
													<input type="text" value="<?=stripslashes(($$reqv->phone))?>" name="phone" id="phone" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="phone_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="phone_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="fax_tip"></a>
												<label class="form-label3" for="">Факс</label>
												<span class="form-input">
													<input type="text" value="<?=stripslashes($$reqv->fax)?>" name="fax" id="fax" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="fax_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="fax_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="email_tip"></a>
												<label class="form-label3" for="">Электронная почта</label>
												<span class="form-input form-imp">
													<input type="text" value="<?=stripslashes(($$reqv->email))?>" name="email"  id="email" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="email_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="email_txt"></span>
															</div>
														</div>
													</div>
												</div>	
											</div>
											
											<div class="form-el">
                                                <a name="country_tip"></a>
												<label class="form-label3" for="">Страна</label>
												<span class="form-select form-imp">
												    <?php $country_id = null; ?>
													<select name="country_id" id="country_id" onChange="CityUpd(this.value)" onFocus="tipView3('country');" <?if (!$$countries) print("disabled")?> >
                                                        <option value="">Не выбрано</option>
														<? if($$countries) foreach ($$countries as $id => $name): ?>
                                                            <?php $sSelected = ($name == $$reqv->country) ? ' selected' : ''; ?>
                                                            <?php if ( $name == $$reqv->country ) { $country_id = $id; } ?>
															<option value="<?=$id?>" <?=$sSelected?>><?=$name?></option>
														<? endforeach; ?>
													</select>
												</span>
												
												<div class="tip" id="country_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="country_txt"></span>
															</div>
														</div>
													</div>
												</div>	
												
											</div>
											
											<div class="form-el">
                                                <a name="city_tip"></a>
												<label class="form-label3" for="">Город<?=$$reqv->city?></label>
												<span class="form-select form-imp" id="frm_city">
												    <?php if ( $country_id ) { $cities = city::GetCities($country_id); } ?>
													<select name="city_id" id="city_id" onchange="CitySet()" onFocus="tipView3('city');" <?php if ( !$cities ) print("disabled")?>>
                                                        <option value="">Не выбрано</option>
														<? if( $cities ) foreach ( $cities as $cityid => $city): ?>
															<option value="<?=$cityid?>" <? if ($city == $$reqv->city) print(" selected") ?>><?=$city?></option>
														<? endforeach; ?>
													</select>
												</span>
												
												<div class="tip" id="city_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="city_txt"></span>
															</div>
														</div>
													</div>
												</div>	
												
											</div>
											<div class="form-el">
                                                <a name="index_tip"></a>
												<label class="form-label3" for="">Почтовый индекс</label>
												<span class="form-input form-imp">
													<input type="text" value="<?=stripslashes(($$reqv->index))?>" name="index" id="index" onFocus="tipView2(this, 1, '<em>111033</em>')" onblur="tipView2(this)"  />
												</span>
												<div class="tip" id="index_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="index_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="address_tip"></a>
												<label class="form-label3" for="">Почтовый адрес</label>
												<span class="form-input form-imp">
													<input type="text" value="<?=stripslashes(($$reqv->address))?>" name="address" id="address" onfocus="tipView2(this, 1, '<strong>На этот адрес будут высланы все документы</strong><em>Пример: ул. Самокатная, 1, стр. 21</em>');" onblur="tipView2(this)"/>
												</span>
												<div class="tip" id="address_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="address_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="address_grz_tip"></a>
												<label class="form-label3" for="">Адрес грузополучателя</label>
												<span class="form-input">
													<input type="text" value="<?=stripslashes(($$reqv->address_grz))?>"  name="address_grz" id="address_grz" onfocus="tipView2(this, 1, '<em>Москва, 111033, ул. Самокатная, 1, стр.21</em>');" onblur="tipView2(this)"/>
												</span>
												<div class="tip" id="address_grz_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="address_grz_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="inn_tip"></a>
												<label class="form-label3" for="">ИНН</label>
												<span id="reqv_inn" class="form-input<?php if ($country_id==1) { ?> form-imp<?php } ?>">
													<input type="text" value="<?=stripslashes(($$reqv->inn))?>" name="inn"  id="inn" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="inn_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="inn_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="kpp_tip"></a>
												<label class="form-label3" for="">КПП</label>
												<span class="form-input">
													<input type="text" value="<?=stripslashes(($$reqv->kpp))?>" name="kpp" id="kpp" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="kpp_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="kpp_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="full_name_tip"></a>
												<label class="form-label3" for="">Полное название организации</label>
												<span class="form-input form-imp">
													<input type="text" value="<?=stripslashes(($$reqv->full_name))?>"  name="full_name" id="full_name" onfocus="tipView2(this, 1, '<em>Укажите форму собственности аббревиатурой</em>');" onblur="tipView2(this);"/>
												</span>
												<div class="tip" id="full_name_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="full_name_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="fio_tip"></a>
												<label class="form-label3" for="">Представитель</label>
												<span class="form-input">
													<input type="text"  name="fio" value="<?=stripslashes(($$reqv->fio))?>" id="fio" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="fio_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="fio_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="address_jry_tip"></a>
												<label class="form-label3" for="">Юридический адрес</label>
												<span class="form-input form-imp">
													<input type="text" value="<?=stripslashes(($$reqv->address_jry))?>" name="address_jry" id="address_jry" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="address_jry_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="address_jry_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="bank_rs_tip"></a>
												<label class="form-label3" for="">Расчетный счет</label>
												<span class="form-input">
													<input type="text" value="<?=stripslashes(($$reqv->bank_rs))?>" name="bank_rs"  id="bank_rs" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="bank_rs_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="bank_rs_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="bank_name_tip"></a>
												<label class="form-label3" for="">Название банка</label>
												<span class="form-input">
													<input type="text" value="<?=stripslashes(($$reqv->bank_name))?>" name="bank_name" id="bank_name" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="bank_name_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="bank_name_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="bank_city_tip"></a>
												<label class="form-label3" for="">Город банка</label>
												<span class="form-input">
													<input type="text" value="<?=stripslashes(($$reqv->bank_city))?>" name="bank_city" id="bank_city" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="bank_city_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="bank_city_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-el">
                                                <a name="bank_ks_tip"></a>
												<label class="form-label3" for="">Корреспондентский счет</label>
												<span class="form-input">
													<input type="text" value="<?=stripslashes(($$reqv->bank_ks))?>" name="bank_ks" id="bank_ks" onFocus="tipView2(this);"/>
												</span>
												<div class="tip" id="bank_ks_tip" style="display:none">
													<div class="tip-in">
														<div class="tip-txt">
															<div class="tip-txt-in">
																<span class="middled" id="bank_ks_txt"></span>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="form-block last">
											<div class="form-btn">
												<? if (!$$edit_mode): ?>
													<input type="submit" name="send" value="Создать" id="Submit" class="i-btn" />
												<? else: ?>
													<input type="hidden" name="id" value="<?=stripslashes(($$reqv->id))?>">
													<input type="submit" name="update" value="Изменить" id="Submit" class="i-btn" />
												<? endif; ?>
											</div>
										</div>
									</div>
									<b class="b2"></b>
									<b class="b1"></b>
								</div>
							</div>
							<div class="bill-right-col2 bill-info bill-rform">
								<p>Способ оплаты:<br /><strong>Банковский перевод для юридических лиц и ИП</strong></p>
								<p>Оплата банковским переводом на счет ООО &laquo;ВААН&raquo;.<br />Для организаций существует возможность выставить счет и оплатить его в банке.</p>
							</div>
						</form>
						</div>
					</div>
				</div>
			</div>

            <?php
            $need_paysum = (float) $_COOKIE['need_paysum'];
            if($need_paysum>0) {
                ?>
                <script type="text/javascript">
                $('sum_a').set('value', '<?=$need_paysum?>');
                infoSum($('sum_a'), true);
                </script>
                <?
            }
            unset($_COOKIE['need_paysum']);
            ?>


{{include "footer.tpl"}}
