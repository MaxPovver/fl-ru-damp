{{include "header.tpl"}}
<? $transaction_id = $$account->start_transaction($$uid, $$tr_id); ?>
<script type="text/javascript">
	billing.init();
	window.onload = function() {
	<? if($$alert): ?>
        <? foreach($$alert as $key=>$val): ?>
        billing.tipView({id:'<?=$key?>'}, '<?=$val?>');
        <? endforeach; ?>
    <? endif; ?>    
	}
	function rndBlur(obj) {

	}
    <? if (count($$alert)) { ?>
        window.addEvent('domready', function(){
            window.scrollTo(0, $('scroll_to').getPosition().y - 40)
        })
    <? } ?>
</script>
<style>.tip{left: 270px; top:55px}</style>
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
		<div class="rcol-big">
			{{include "bill/bill_menu.tpl"}}
			<div class="tabs-in bill-t-in c">
				<h3 id="scroll_to">Подтвердите перевод</h3>
					<div class="form bill-form-tc2">
						<form action="." method="post" name="frm" id="frm">
						<input type="hidden" name="sum" value="<?=$$sum?>">
						<input type="hidden" name="login" value="<?=$$login?>">
						<input type="hidden" name="action" value="sendm">
						<input type="hidden" name="msg" value="<?=stripslashes($$msg)?>">
						<input type="hidden" name="transaction_id" value="<?=$transaction_id?>">
						<b class="b1"></b>
						<b class="b2"></b>
						<div class="form-in">
							<div class="form-block first">
								<div class="form-el">
									<label class="form-label" for="">Сумма:</label>
									<span class="form-input">
										<?=$$sum?> FM
									</span>
								</div>
								<div class="form-el">
									<label class="form-label" for="">Получатель:</label>
									<span class="form-input b-username" style="width:280px">
          <? $res = get_object_vars($$user);
             $sbr_info = $$sbr_info;
             $ocnt = $$ocnt;
             include (ABS_PATH . "/engine/templates/user_info.tpl"); ?>
									</span>
								</div>
							</div>
							<div class="form-block">
								<div class="form-el">
									<label class="form-label3" for="">Комментарий:</label>
									<span class="form-input-value2">
									<? if ($$msg) print(reformat(stripslashes($$msg), 23));
									   else print("Сообщения нет"); ?>
									</span>
								</div>
							</div>
							<div class="form-block">
								<div class="form-el" id="sum_parent">
									<label class="form-label3" for="">Введите код с картинки:</label>
									<span class="form-input form-captcha">
										<div class="c" style="margin-bottom:5px"><img src="/image.php?r=<?=time()?>" alt="" id="rndnumimage" onClick="$('rndnumimage').set('src','/image.php?r='+Math.random());" /> <a href="#" onClick="$('rndnumimage').set('src','/image.php?r='+Math.random()); return false;">Обновить код</a></div>
										<input type="text" id="sum" name="rndnum" value="" size="5" onfocus="billing.clearEvent(this);" onkeydown="if(event.keyCode==13)document.getElementById('frm').btns.focus();" />
										<input type="hidden" value="<?=$_SESSION["rand"] ?>" name="u_token_key" />
									</span>
								</div>
							</div>
							<div class="form-block last">
								<div class="form-btn">
									<input type="submit" name="last_action"  value="&laquo; Назад" onClick="" class="i-btn i-norm" /> <input type="submit" name="btns" value="Перевести" class="i-btn i-bold" />
								</div>
							</div>
						</div>
						<b class="b2"></b>
						<b class="b1"></b>
						</form>
					</div>
			</div>
		</div>
	</div>
</div>
{{include "footer.tpl"}}
