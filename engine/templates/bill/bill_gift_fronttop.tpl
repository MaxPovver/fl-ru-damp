{{include "header.tpl"}}
<? $transaction_id = $$account->start_transaction($$uid, $$tr_id); ?>
<?=$$xajax->printJavascript('/xajax/');?>
<script type="text/javascript">

	billing.init();

	window.onload = function(){
 	<? if($$error): ?>
 		<? foreach($$error as $key=>$val): ?>
 		billing.tipView({id:'<?=$key?>'}, '<?=$val?>');
 		<? endforeach; ?>
	<? endif; ?>	
 	};
	
	function loginCheck(obj) {
 		var myLogin = '<?=$_SESSION['login']?>';
	    billing.clearEvent(obj); 
 		
 		if(myLogin == obj.value) {
 		    billing.tipView(obj, 'Вы не можете сделать подарок самому себе');
 			return false;	   
 		}
 		
 		if(billing.isNull(obj.value) == true) {
 			billing.tipView(obj, 'Данное поле является обязательным');
 			return false;	
 		}
 		
 		xajax_CheckUserType(obj.value, 1);
 	}
	<? if (count($$error)) { ?>
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
							<h3 id="scroll_to" class="bill-gifts-h3">Подарите место наверху главной страницы</h3>
							
							<form action="." method="post" name="frm" id="frm" >
							<input type="hidden" id="usertype" name="usertype" value="frl">
							<input type="hidden" name="transaction_id" value="<?=$transaction_id?>">
                            <input type="hidden" value="<?=$_SESSION["rand"] ?>" name="u_token_key" />
							<div class="bill-left-col2">
								<div class="form bill-form">
									<b class="b1"></b>
									<b class="b2"></b>
									<div class="form-in">
										<div class="form-block first">
											<div class="form-el">
												<label class="form-label" for="">Логин получателя:</label>
												<span class="form-input" id="login_parent">
													<input type="text" value="<?=htmlspecialchars($$login)?>"  id="login" name="login" class="i-bold" onblur="loginCheck(this); " />
												</span>
											</div>
										</div>
										<div class="form-block">
											<div class="form-el">
												<span class="form-hint fhr"></span>
												<label for="" class="form-label2">Поздравительная надпись</label>
												<span class="form-txt" id="descr_parent">
													<textarea rows="5" cols="40" id="descr" name="msg" onBlur="billing.isMaxLen(this);" onKeyUp="billing.isMaxLen(this);"><?=htmlspecialchars(stripslashes($$msg));?></textarea>
												</span>
												<span class="form-hint">Вы набрали <span id="count_length"><?=(strlen($$msg))?> <?=ending(strlen($$msg), "символ", "символа", "символов")?></span>. Разрешено не более 300</span>
											</div>
										</div>
										<div class="form-block">
											<div class="form-el">
												<label class="form-label" for="">Итого к оплате:</label>
												<span>
													<span id="">3</span> FM
												</span>
											</div>
										</div>
										<div class="form-block last">
											<div class="form-btn">
												<input type="submit" name="act" value="Подарить" onClick="return billing.checkSend(1);" class="i-btn" />
											</div>
										</div>
									</div>
									<b class="b2"></b>
									<b class="b1"></b>
								</div>
							</div>
							<div class="bill-right-col2 bill-info">
								Сделайте сюрприз своим друзьям и знакомым &mdash; отправьте им подарок.
							</div>
							</form>
						</div>
					</div>
				</div>
			</div>
{{include "footer.tpl"}}										