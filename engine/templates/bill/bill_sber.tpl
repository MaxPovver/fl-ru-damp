{{include "header.tpl"}}
<script type="text/javascript">
    var IS_EMP = <?= ( is_emp()? 'true' : 'false' ) ;?>;
    billing.init();
	
    window.onload = function() {
    <? if($$alert): ?>
        <? foreach($$alert as $key=>$val): ?>
        billing.tipView({id:'<?=$key?>'}, '<?=$val?>');
        <? endforeach; ?>
    <? endif; ?>    
    }
    
	function isNumeric(obj, is_fm){
            if(typeof is_fm == 'undefined') is_fm = false;
	    billing.clearEvent(obj);
		obj.value = obj.value.replace(/\,/, '.');
		obj.value = obj.value.replace(/\s/gi, '');
		
		var str = obj.value;
		var numericExpression = /^ *(?:\d[\d ]*|\d*( \d+)*[.,]\d*) *$/; ///^[0-9]+(\.[0-9]+)?$/;
		if(str == '0' || str == 0 || str == '') {
			billing.tipView(obj, 'Пожалуйста, введите числовое значение');
		    //$$('#sum_tip').setStyle('display', 'inline');// show();
			//$$('#sum_txt').set('html', "<strong></strong>");
			//$$('#sum').addClass('invalid');
			$$('#pay').set('disabled', 1);
			return false;
		} else if(billing.isNumeric(String(str)) != false){
                        var fm = !is_fm ? rur2fm(str) : Number(str);
                        if(is_fm) str = fm2rur(str);
                        if(is_fm) $$('#sum').set('value', str);
                        if(!is_fm) $$('#fm').set('value', fm);
			billing.clearEvent(obj);
			if(str <=0) $$('#pay').set('disabled', 1);
			else $$('#pay').set('disabled', 0);
			return true;
		} else {
			billing.tipView(obj, 'Пожалуйста, введите числовое значение');
			$$('#pay').set('disabled', 1);
			return false;
		}
	}

    var etr = <?php echo EXCH_TR;?>;
    var rur2fm = function(rur){
        var fm = rur / etr;
        return fm % 2 ? fm.toFixed(2) : fm;
    }

    var fm2rur = function(fm){
        var rur = fm * etr;
        return rur % 2 ? rur.toFixed(2) : rur;
    }
    
    
    function getMoney(v) {
        el = $('sum');
        
        if (!el) {
            return;
        }
        
        el.set('value', v);
        isNumeric(el);
    }
    
    <? if (count($$alert)) { ?>
        window.addEvent('domready', function(){
            window.scrollTo(0, $('scroll_to').getPosition().y - 40)
        })
    <? } ?>
</script>

<div class="body c">
				<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
					<div class="rcol-big">
						{{include "bill/bill_menu.tpl"}}
						<div class="tabs-in bill-t-in c">
							<form method="POST" target="_blank" class="c">
							<input type="hidden" name="id" value="<?=$$bp->id?>"/>
  							<input type="hidden" name="bc" value="<?=$$bp->bank_code?>"/>
                            <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
							<h3 id="scroll_to">Оплата через банк для физических лиц</h3>
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
							<div class="bill-left-col2">

								<div class="form bill-form">
									<b class="b1"></b>
									<b class="b2"></b>
									<div class="form-in">
										<div class="form-block first">
                                                                                    <?/*<div class="form-el">
												<label class="form-label" for="">Сумма пополнения</label>
												<span class="form-input form-input2" id="fm_parent">
                                                    <input type="text" onchange="isNumeric(this,true); isGiftShow(this.value, true);" id="fm" class="i-bold" style="text-align:right" value="<?=cutz($$fm_val)?>" /> FM
												</span>
											</div>*/?>
											<div class="form-el">
												<label class="form-label" for="">Сумма пополнения</label>
												<span class="form-input form-input2" id="sum_parent">
													<input type="text" onfocus="billing.clearEvent(this);" value="<?=cutz($$bp->sum)?>" name="sum" maxlength="12" id="sum" <?=($$alert['sum']?'class="i-bold invalid"':'class="i-bold"')?> style="text-align:right" onchange="isNumeric(this);"/> руб.
												</span>
											</div>
										</div>
										<div class="form-block">
											<div class="form-el">
												<label class="form-label3" for="">ФИО</label>
												<span class="form-input" id="fio_parent">
													<input type="text" onfocus="billing.clearEvent(this);" value="<?=stripcslashes($$bp->fio)?>" id="fio" name="fio" <?=($$alert['fio']?'class="i-bold invalid"':'class="i-bold"')?>/>
												</span>
											</div>
											<div class="form-el">
												<label class="form-label3" for="">Адрес плательщика</label>
												<span class="form-input" id="address_parent">
													<textarea rows="3" cols="20" onfocus="billing.clearEvent(this);" id="address" name="address" <?=($$alert['address']?'class="invalid"':'')?>><?=stripcslashes($$bp->address)?></textarea>
												</span>
											</div>
										</div>
										<div class="form-block last">
											<div class="form-btn">
												<input type="submit" name="act" value="Создать" onClick="return billing.checkSend($('sum').value);" id="pay" <?if(!$$edit && cutz($$bp->sum)<=0):?>disabled<?endif;?> class="i-btn" />
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
                                $('sum').set('value', '<?=$need_paysum?>');
                                isNumeric($('sum'),false);
                                </script>
                                <?
                            }
                            unset($_COOKIE['need_paysum']);
                            ?>

							<div class="bill-right-col2 bill-info bill-rform">
								<p>Способ оплаты:<br /><strong>Банковский перевод для физических лиц</strong></p>
                                <p>Обращаем ваше внимание на то, что оплата квитанцией в любом отделении Сбербанка доступна только пользователям, находящимся на территории Российской Федерации.</p>
                                <p>Подробнее о данном способе пополнения в разделе "<a href="<?= HTTP_PREFIX ?>feedback.free-lance.ru/article/details/id/169" target="_blank">Помощь</a>"</p>
							</div>
							</form>
						</div>
					</div>
				</div>
			</div>
{{include "footer.tpl"}}		
