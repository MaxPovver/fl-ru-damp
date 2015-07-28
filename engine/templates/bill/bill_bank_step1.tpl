{{include "header.tpl"}}
<script type="text/javascript">
	function isNumeric(str){
		var numericExpression = /^[0-9]+(\.[0-9]+)?$/;
		if(str.match(numericExpression)){
			$$('#sum_tip').setStyle("display", "none"); //hide();
			$$('#ammount').removeClass('invalid');
			return true;
		}else{
			$$('#sum_tip').setStyle("display", "block"); //show();
			$$('#sum_txt').html("<strong>Пожалуйста, введите числовое значение</strong>");
			$$('#ammount').addClass('invalid');
			return false;
		}
	}
	function chButton() {
		if(isNumeric($$('#ammount').value) == true) $$('#pay').set('disabled', 0);
		else $$('#pay').set('disabled', 1);
		
		if($$('#ammount').value<=0) $$('#pay').set('disabled', 1);
	}
</script>
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
		<div class="rcol-big">
			{{include "bill/bill_menu.tpl"}}
			<div class="tabs-in bill-t-in c">
				<form method="POST">
					<input type="hidden" name="id" value="<?=$$bp->id?>"/>
					<input type="hidden" name="bc" value="<?=$$bp->bank_code?>"/>
					<h3>Банковский перевод</h3>
					<div class="form bill-form">
						<b class="b1"></b>
						<b class="b2"></b>
						<div class="form-in">
							<div class="form-block first">
								<div class="form-el">
									<label class="form-label" for="">Сумма</label>
									<span class="form-input form-input2">
										<input type="text" value="" name="sum" id="ammount" onkeyup="chButton();" onBlur="chButton();" class="i-bold" style="text-align:right"/> руб.
									</span>
									<div class="tip" id="sum_tip" style="display:none">
										<div class="tip-in">
											<div class="tip-txt" id="sum_txt"></div>
										</div>
									</div>
								</div>
								<div class="form-block last">
									<div class="form-btn">
										<input type="submit" name="act" value="Добавить" id="pay" disabled class="i-btn" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
{{include "footer.tpl"}}